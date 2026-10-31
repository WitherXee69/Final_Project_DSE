<?php

const PICKUP_WINDOWS = ['08:00 - 10:00', '10:00 - 12:00', '12:00 - 14:00', '14:00 - 16:00', '16:00 - 18:00'];

/
const PK_REQUESTED = 'Requested';
const PK_APPROVED  = 'Approved';
const PK_REJECTED  = 'Rejected';
const PK_CANCELLED = 'Cancelled';
const PK_ALL = [PK_REQUESTED, PK_APPROVED, PK_REJECTED, PK_CANCELLED, 'Successful', 'Failed'];


function pickup_validate(array $d): array {
    $err  = [];
    $addr = trim($d['pickupAddress'] ?? '');
    if (mb_strlen($addr) < 10)  $err[] = 'Pickup address must be at least 10 characters.';
    if (mb_strlen($addr) > 255) $err[] = 'Pickup address must be 255 characters or fewer.';

    $date = $d['pickupDate'] ?? '';
    $dt   = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dt || $dt->format('Y-m-d') !== $date)  $err[] = 'Please choose a valid pickup date.';
    elseif ($dt < new DateTime('today'))         $err[] = 'Pickup date cannot be in the past.';

    if (!in_array($d['preferredTimeWindow'] ?? '', PICKUP_WINDOWS, true))
        $err[] = 'Please choose a preferred time window.';
    return $err;
}


function pickup_create(PDO $pdo, int $customerId, array $d): int {
    $st = $pdo->prepare(
        "INSERT INTO Pickup (requestedAt, pickupDate, preferredTimeWindow, pickupAddress, status, customerId)
         VALUES (NOW(), ?, ?, ?, ?, ?)"
    );
    $st->execute([$d['pickupDate'], $d['preferredTimeWindow'], trim($d['pickupAddress']), PK_REQUESTED, $customerId]);
    return (int)$pdo->lastInsertId();
}


function pickup_list(PDO $pdo, int $customerId, string $status = '', string $q = ''): array {
    $sql = "SELECT * FROM Pickup WHERE customerId = ?";
    $p   = [$customerId];
    if ($status !== '' && in_array($status, PK_ALL, true)) { $sql .= " AND status = ?"; $p[] = $status; }
    $q = trim($q);
    if ($q !== '') {
        $sql .= " AND (pickupAddress LIKE ? OR pickupId = ?)";
        $p[] = '%' . $q . '%';
        $p[] = ctype_digit(ltrim($q, '#')) ? (int)ltrim($q, '#') : 0;
    }
    $st = $pdo->prepare($sql . " ORDER BY requestedAt DESC, pickupId DESC");
    $st->execute($p);
    return $st->fetchAll();
}


function pickup_get(PDO $pdo, int $pickupId, int $customerId): ?array {
    $st = $pdo->prepare("SELECT * FROM Pickup WHERE pickupId = ? AND customerId = ?");
    $st->execute([$pickupId, $customerId]);
    return $st->fetch() ?: null;
}


function pickup_update(PDO $pdo, int $pickupId, int $customerId, array $d): bool {
    $st = $pdo->prepare(
        "UPDATE Pickup SET pickupDate = ?, preferredTimeWindow = ?, pickupAddress = ?
         WHERE pickupId = ? AND customerId = ? AND status = ?"
    );
    $st->execute([$d['pickupDate'], $d['preferredTimeWindow'], trim($d['pickupAddress']),
                  $pickupId, $customerId, PK_REQUESTED]);
    return $st->rowCount() > 0;
}


function pickup_cancel(PDO $pdo, int $pickupId, int $customerId): bool {
    $st = $pdo->prepare(
        "UPDATE Pickup SET status = ?, cancelledAt = NOW()
         WHERE pickupId = ? AND customerId = ? AND status IN (?, ?)"
    );
    $st->execute([PK_CANCELLED, $pickupId, $customerId, PK_REQUESTED, PK_APPROVED]);
    return $st->rowCount() > 0;
}

function pickup_can_edit(array $p): bool   { return ($p['status'] ?? '') === PK_REQUESTED; }
function pickup_can_cancel(array $p): bool { return in_array($p['status'] ?? '', [PK_REQUESTED, PK_APPROVED], true); }


function pickup_counts(PDO $pdo, int $customerId): array {
    $st = $pdo->prepare("SELECT status, COUNT(*) c FROM Pickup WHERE customerId = ? GROUP BY status");
    $st->execute([$customerId]);
    return $st->fetchAll(PDO::FETCH_KEY_PAIR);
}


function pickup_next(PDO $pdo, int $customerId): ?array {
    $st = $pdo->prepare(
        "SELECT * FROM Pickup WHERE customerId = ? AND status IN (?, ?) AND pickupDate >= CURDATE()
         ORDER BY pickupDate ASC, pickupId ASC LIMIT 1"
    );
    $st->execute([$customerId, PK_REQUESTED, PK_APPROVED]);
    return $st->fetch() ?: null;
}
