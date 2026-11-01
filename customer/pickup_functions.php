<?php

const PICKUP_WINDOWS = ['09:00-12:00', '13:00-16:00', '16:00-18:00'];

// Must match Pickup.status ENUM in sql/HELMS.sql
const PK_REQUESTED = 'REQUESTED';
const PK_APPROVED  = 'APPROVED';
const PK_REJECTED  = 'REJECTED';
const PK_ASSIGNED  = 'ASSIGNED';
const PK_PICKED_UP = 'PICKED_UP';
const PK_FAILED    = 'FAILED';
const PK_CANCELLED = 'CANCELLED';
const PK_ALL = [PK_REQUESTED, PK_APPROVED, PK_ASSIGNED, PK_PICKED_UP, PK_REJECTED, PK_FAILED, PK_CANCELLED];

/** Friendly text for a status value */
function pickup_label(?string $s): string {
    return [
        PK_REQUESTED => 'Requested', PK_APPROVED => 'Approved', PK_ASSIGNED => 'Driver assigned',
        PK_PICKED_UP => 'Picked up',  PK_REJECTED => 'Rejected', PK_FAILED => 'Failed',
        PK_CANCELLED => 'Cancelled',
    ][$s ?? ''] ?? (string)$s;
}

function pickup_valid_id(string $id): bool {
    return (bool)preg_match('/^PCK-\d{4}-\d{6}$/', $id);
}

/** Character count that also works if the PHP mbstring extension is switched off */
function pickup_len(string $s): int {
    return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
}

/** @return string[] error messages; empty = valid */
function pickup_validate(array $d): array {
    $err  = [];
    $addr = trim($d['pickupAddress'] ?? '');
    if (pickup_len($addr) < 10)  $err[] = 'Pickup address must be at least 10 characters.';
    if (pickup_len($addr) > 255) $err[] = 'Pickup address must be 255 characters or fewer.';

    $date = $d['pickupDate'] ?? '';
    $dt   = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dt || $dt->format('Y-m-d') !== $date)  $err[] = 'Please choose a valid pickup date.';
    elseif ($dt < new DateTime('today'))         $err[] = 'Pickup date cannot be in the past.';

    if (!in_array($d['preferredTimeWindow'] ?? '', PICKUP_WINDOWS, true))
        $err[] = 'Please choose a preferred time window.';
    return $err;
}

/** Next id for this year: PCK-YYYY-000001, PCK-YYYY-000002 ... */
function pickup_next_id(PDO $pdo): string {
    $year = date('Y');
    $st = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(pickupId, 10) AS UNSIGNED)) FROM Pickup WHERE pickupId LIKE ?");
    $st->execute(['PCK-' . $year . '-%']);
    return sprintf('PCK-%s-%06d', $year, (int)$st->fetchColumn() + 1);
}

/** CREATE - returns the new pickupId. Retries if two requests pick the same number at once. */
function pickup_create(PDO $pdo, string $customerId, array $d): string {
    $sql = "INSERT INTO Pickup (pickupId, requestedAt, pickupDate, preferredTimeWindow, pickupAddress, status, customerId)
            VALUES (?, NOW(), ?, ?, ?, ?, ?)";
    for ($try = 0; $try < 3; $try++) {
        $id = pickup_next_id($pdo);
        try {
            $pdo->prepare($sql)->execute([$id, $d['pickupDate'], $d['preferredTimeWindow'],
                                          trim($d['pickupAddress']), PK_REQUESTED, $customerId]);
            return $id;
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000' || $try === 2) throw $e;   // 23000 = duplicate key
        }
    }
    throw new RuntimeException('Could not create pickup.');
}

/** READ list - this customer's pickups; optional status filter and search (id or address) */
function pickup_list(PDO $pdo, string $customerId, string $status = '', string $q = ''): array {
    $sql = "SELECT * FROM Pickup WHERE customerId = ?";
    $p   = [$customerId];
    if ($status !== '' && in_array($status, PK_ALL, true)) { $sql .= " AND status = ?"; $p[] = $status; }
    $q = trim($q);
    if ($q !== '') {
        $sql .= " AND (pickupAddress LIKE ? OR pickupId LIKE ?)";
        $p[] = '%' . $q . '%';
        $p[] = '%' . $q . '%';
    }
    $st = $pdo->prepare($sql . " ORDER BY requestedAt DESC, pickupId DESC");
    $st->execute($p);
    return $st->fetchAll();
}

/** READ one - ownership enforced */
function pickup_get(PDO $pdo, string $pickupId, string $customerId): ?array {
    if (!pickup_valid_id($pickupId)) return null;
    $st = $pdo->prepare("SELECT * FROM Pickup WHERE pickupId = ? AND customerId = ?");
    $st->execute([$pickupId, $customerId]);
    return $st->fetch() ?: null;
}

/** UPDATE - only while still REQUESTED (not yet approved by staff) */
function updatePickup(PDO $pdo, string $pickupId, string $customerId, array $d): bool {
    $st = $pdo->prepare(
        "UPDATE Pickup SET pickupDate = ?, preferredTimeWindow = ?, pickupAddress = ?
         WHERE pickupId = ? AND customerId = ? AND status = ?"
    );
    $st->execute([$d['pickupDate'], $d['preferredTimeWindow'], trim($d['pickupAddress']),
                  $pickupId, $customerId, PK_REQUESTED]);
    return $st->rowCount() > 0;
}

/** CANCEL - permanently deletes the request; allowed while REQUESTED or APPROVED */
function cancelPickup(PDO $pdo, string $pickupId, string $customerId): bool {
    try {
        $st = $pdo->prepare(
            "DELETE FROM Pickup
             WHERE pickupId = ? AND customerId = ? AND status IN (?, ?)"
        );
        $st->execute([$pickupId, $customerId, PK_REQUESTED, PK_APPROVED]);
        return $st->rowCount() > 0;
    } catch (PDOException $e) {
        // 23000 = another table (e.g. CostEstimate, Parcel) still points at this pickup
        if ($e->getCode() === '23000') return false;
        throw $e;
    }
}

function pickup_can_edit(array $p): bool   { return ($p['status'] ?? '') === PK_REQUESTED; }
function pickup_can_cancel(array $p): bool { return in_array($p['status'] ?? '', [PK_REQUESTED, PK_APPROVED], true); }

/** [status => count] for dashboard and filter tabs */
function pickup_counts(PDO $pdo, string $customerId): array {
    $st = $pdo->prepare("SELECT status, COUNT(*) c FROM Pickup WHERE customerId = ? GROUP BY status");
    $st->execute([$customerId]);
    return $st->fetchAll(PDO::FETCH_KEY_PAIR);
}

/** Soonest upcoming pickup that is still live, or null */
function pickup_next(PDO $pdo, string $customerId): ?array {
    $st = $pdo->prepare(
        "SELECT * FROM Pickup WHERE customerId = ? AND status IN (?, ?, ?) AND pickupDate >= CURDATE()
         ORDER BY pickupDate ASC, pickupId ASC LIMIT 1"
    );
    $st->execute([$customerId, PK_REQUESTED, PK_APPROVED, PK_ASSIGNED]);
    return $st->fetch() ?: null;
}
