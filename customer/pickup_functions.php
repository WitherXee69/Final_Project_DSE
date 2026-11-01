<?php
/**
 * HELMS - Pickup business logic for the customer portal (Create / Read / Update / Cancel)
 *
 * Functions named after the class diagram methods:
 *   Customer.requestPickup()       -> requestPickup()
 *   Pickup.recordParcelDetails()   -> recordParcelDetails()   (saves the Parcel + Receiver rows)
 *   Pickup.updatePickup()          -> updatePickup()
 *   Customer/Pickup.cancelPickup() -> cancelPickup()          (deletes the pickup, its parcel and receiver)
 * Helpers (pickup_list, pickup_get, pickup_details, ...) have no matching diagram method.
 * Cost estimates (viewCostEstimate / CostEstimate.calculate) are not implemented yet.
 *
 * Tables used: Pickup, Parcel, Receiver (and CostEstimate rows only to clean up when a pickup is deleted).
 * Every function takes the project's $pdo (config/database.php).
 */

const PICKUP_WINDOWS = ['09:00-12:00', '13:00-16:00', '16:00-18:00'];

/* ---------- Pickup status vocabulary (Pickup.status is free text in the shared database) ---------- */
const PK_REQUESTED = 'REQUESTED';   // customer asked, waiting for customer service
const PK_APPROVED  = 'APPROVED';    // approved by customer service, driver not yet assigned
const PK_SCHEDULED = 'SCHEDULED';   // driver assigned
const PK_COMPLETED = 'COMPLETED';   // parcel collected
const PK_REJECTED  = 'REJECTED';
const PK_FAILED    = 'FAILED';
const PK_CANCELLED = 'CANCELLED';
const PK_ALL = [PK_REQUESTED, PK_APPROVED, PK_SCHEDULED, PK_COMPLETED, PK_REJECTED, PK_FAILED, PK_CANCELLED];

/** Other spellings teammates' code may write, mapped to the canonical status above */
const PK_SYNONYMS = ['ASSIGNED' => PK_SCHEDULED, 'PICKED_UP' => PK_COMPLETED, 'SUCCESSFUL' => PK_COMPLETED];

/** Canonical upper-case status for any stored value ('Completed' -> 'COMPLETED') */
function pickup_status(?string $s): string {
    $s = strtoupper(trim((string)$s));
    return PK_SYNONYMS[$s] ?? $s;
}

function pickup_label(?string $s): string {
    $s = pickup_status($s);
    return [
        PK_REQUESTED => 'Requested', PK_APPROVED => 'Approved', PK_SCHEDULED => 'Driver assigned',
        PK_COMPLETED => 'Collected', PK_REJECTED => 'Rejected', PK_FAILED => 'Failed', PK_CANCELLED => 'Cancelled',
    ][$s] ?? ($s === '' ? 'Unknown' : ucfirst(strtolower($s)));
}

function pickup_can_edit(array $p): bool   { return pickup_status($p['status'] ?? '') === PK_REQUESTED; }
function pickup_can_cancel(array $p): bool { return in_array(pickup_status($p['status'] ?? ''), [PK_REQUESTED, PK_APPROVED], true); }

/** True if the text looks like a pickup id, e.g. PCK-2026-000051 */
function pickup_valid_id(string $id): bool { return (bool)preg_match('/^PCK-\d{4}-\d{6}$/', $id); }

/* ---------- Choice lists shown on the form ---------- */
const PARCEL_CATEGORIES = [
    'DOCUMENT' => 'Document', 'SMALL_PACKAGE' => 'Small package', 'MEDIUM_PACKAGE' => 'Medium package',
    'LARGE_PACKAGE' => 'Large package', 'BULKY_ITEM' => 'Bulky item',
];
const SERVICE_LABELS = ['STANDARD' => 'Standard', 'EXPRESS' => 'Express', 'SAME_DAY' => 'Same day'];
const ZONE_LABELS = [
    'KDY-01' => 'Kandy', 'GLE-01' => 'Galle', 'NGB-01' => 'Negombo',
    'MTR-01' => 'Matara', 'NWE-01' => 'Nuwara Eliya', 'JFN-01' => 'Jaffna',
];

/** [code => label] of delivery zones: the known zones plus any zone already used by a receiver */
function pickup_zones(PDO $pdo): array {
    $codes = $pdo->query("SELECT DISTINCT zoneCode FROM Receiver WHERE zoneCode IS NOT NULL AND zoneCode <> ''")->fetchAll(PDO::FETCH_COLUMN);
    $codes = array_unique(array_merge(array_keys(ZONE_LABELS), $codes));
    sort($codes);
    $zones = [];
    foreach ($codes as $c) $zones[$c] = (ZONE_LABELS[$c] ?? $c) . ' (' . $c . ')';
    return $zones;
}

/** [code => label] of delivery services (Parcel.serviceType) */
function pickup_services(PDO $pdo): array {
    return SERVICE_LABELS;
}

/* ============================================================================================
 *  Validation
 * ============================================================================================ */

/** Trim and normalise the posted form into the shape the rest of the code expects */
function pickup_clean(array $d): array {
    $dims = [];
    foreach (['dimL', 'dimW', 'dimH'] as $k) $dims[$k] = trim((string)($d[$k] ?? ''));
    return [
        'pickupAddress'       => trim((string)($d['pickupAddress'] ?? '')),
        'pickupDate'          => trim((string)($d['pickupDate'] ?? '')),
        'preferredTimeWindow' => trim((string)($d['preferredTimeWindow'] ?? '')),
        'description'         => trim((string)($d['description'] ?? '')),
        'parcelCategory'      => trim((string)($d['parcelCategory'] ?? '')),
        'serviceType'         => trim((string)($d['serviceType'] ?? '')),
        'isFragile'           => !empty($d['isFragile']) ? 1 : 0,
        'declaredWeight'      => trim((string)($d['declaredWeight'] ?? '')),
        'dimL' => $dims['dimL'], 'dimW' => $dims['dimW'], 'dimH' => $dims['dimH'],
        'receiverName'        => trim((string)($d['receiverName'] ?? '')),
        'receiverPhone'       => trim((string)($d['receiverPhone'] ?? '')),
        'receiverAddress'     => trim((string)($d['receiverAddress'] ?? '')),
        'zoneCode'            => trim((string)($d['zoneCode'] ?? '')),
    ];
}

/** Character count that also works if the PHP mbstring extension is switched off */
function pickup_len(string $s): int { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); }

/** @return string[] error messages; empty = valid.  $d must come from pickup_clean() */
function pickup_validate(PDO $pdo, array $d): array {
    $err = [];

    // --- pickup
    $n = pickup_len($d['pickupAddress']);
    if ($n < 10 || $n > 255) $err[] = 'Pickup address must be between 10 and 255 characters.';
    $dt = DateTime::createFromFormat('Y-m-d', $d['pickupDate']);
    if (!$dt || $dt->format('Y-m-d') !== $d['pickupDate']) $err[] = 'Please choose a valid pickup date.';
    elseif ($dt < new DateTime('today'))                   $err[] = 'Pickup date cannot be in the past.';
    if (!in_array($d['preferredTimeWindow'], PICKUP_WINDOWS, true)) $err[] = 'Please choose a preferred time window.';

    // --- parcel
    $n = pickup_len($d['description']);
    if ($n < 3 || $n > 255) $err[] = 'Parcel description must be between 3 and 255 characters.';
    if (!isset(PARCEL_CATEGORIES[$d['parcelCategory']])) $err[] = 'Please choose a parcel category.';
    if (!isset(pickup_services($pdo)[$d['serviceType']])) $err[] = 'Please choose a delivery service.';
    if (!is_numeric($d['declaredWeight']) || (float)$d['declaredWeight'] <= 0 || (float)$d['declaredWeight'] > 1000)
        $err[] = 'Parcel weight must be a number above 0 and up to 1000 kg.';
    $dimsGiven = array_filter([$d['dimL'], $d['dimW'], $d['dimH']], fn($v) => $v !== '');
    if ($dimsGiven) {
        $ok = count($dimsGiven) === 3;
        foreach ($dimsGiven as $v) if (!is_numeric($v) || (float)$v <= 0 || (float)$v > 1000) $ok = false;
        if (!$ok) $err[] = 'Enter all three dimensions (length, width, height in cm) as positive numbers, or leave all blank.';
    }

    // --- receiver
    $n = pickup_len($d['receiverName']);
    if ($n < 2 || $n > 150) $err[] = 'Receiver name must be between 2 and 150 characters.';
    if (!preg_match('/^\+?[0-9][0-9 \-]{6,18}$/', $d['receiverPhone'])) $err[] = 'Enter a valid receiver phone number (7 to 20 digits).';
    $n = pickup_len($d['receiverAddress']);
    if ($n < 10 || $n > 255) $err[] = 'Receiver address must be between 10 and 255 characters.';
    $zones = pickup_zones($pdo);
    if ($zones && !isset($zones[$d['zoneCode']])) $err[] = 'Please choose the receiver\'s delivery zone.';

    return $err;
}

/** 'LxWxH' text stored in Parcel.dimensions (or null) */
function pickup_dimensions(array $d): ?string {
    if ($d['dimL'] === '' || $d['dimW'] === '' || $d['dimH'] === '') return null;
    $f = fn($v) => rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.');
    return $f($d['dimL']) . 'x' . $f($d['dimW']) . 'x' . $f($d['dimH']);
}

/** Split a stored 'LxWxH' back into the three form fields */
function pickup_split_dimensions(?string $s): array {
    if ($s && preg_match('/^(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)$/', $s, $m))
        return ['dimL' => $m[1], 'dimW' => $m[2], 'dimH' => $m[3]];
    return ['dimL' => '', 'dimW' => '', 'dimH' => ''];
}

/* ============================================================================================
 *  IDs and transactions
 * ============================================================================================ */

/** Next id such as PCK-2026-000052. Call inside a transaction ($prefix e.g. 'PCK-2026-', 'RCV-', 'EST-'). */
function id_next(PDO $pdo, string $table, string $column, string $prefix): string {
    $pos = strlen($prefix) + 1;
    $st  = $pdo->prepare("SELECT MAX(CAST(SUBSTRING($column, $pos) AS UNSIGNED)) FROM $table WHERE $column LIKE ? FOR UPDATE");
    $st->execute([$prefix . '%']);
    return $prefix . str_pad((string)((int)$st->fetchColumn() + 1), 6, '0', STR_PAD_LEFT);
}

/** Run $fn inside a transaction. Retries if two customers grab the same new id at once. */
function pickup_tx(PDO $pdo, callable $fn, bool $retry = true) {
    for ($try = 1; ; $try++) {
        try {
            $pdo->beginTransaction();
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($retry && $e->getCode() === '23000' && $try < 3) continue;   // 23000 = duplicate key
            throw $e;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}

/* ============================================================================================
 *  CREATE   (Customer.requestPickup, Pickup.recordParcelDetails)
 * ============================================================================================ */

/**
 * Pickup.recordParcelDetails(): saves the parcel and its receiver for a pickup.
 * Creates them if the pickup has none yet, otherwise updates them. Returns the parcelId.
 * Must be called inside a transaction (see pickup_tx).
 */
function recordParcelDetails(PDO $pdo, string $pickupId, string $customerId, array $d): string {
    $st = $pdo->prepare("SELECT parcelId, receiverId FROM Parcel WHERE pickupId = ? ORDER BY parcelId LIMIT 1 FOR UPDATE");
    $st->execute([$pickupId]);
    $parcel = $st->fetch();

    // Receiver: edit in place unless another parcel shares it, then make a new one
    $receiverId = $parcel['receiverId'] ?? null;
    $shared = false;
    if ($receiverId) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM Parcel WHERE receiverId = ? AND parcelId <> ?");
        $st->execute([$receiverId, $parcel['parcelId']]);
        $shared = (int)$st->fetchColumn() > 0;
    }
    if ($receiverId && !$shared) {
        $pdo->prepare("UPDATE Receiver SET receiverName = ?, receiverPhone = ?, receiverAddress = ?, zoneCode = ? WHERE receiverId = ?")
            ->execute([$d['receiverName'], $d['receiverPhone'], $d['receiverAddress'], $d['zoneCode'] ?: null, $receiverId]);
    } else {
        $receiverId = id_next($pdo, 'Receiver', 'receiverId', 'RCV-');
        $pdo->prepare("INSERT INTO Receiver (receiverId, receiverName, receiverPhone, receiverAddress, zoneCode) VALUES (?,?,?,?,?)")
            ->execute([$receiverId, $d['receiverName'], $d['receiverPhone'], $d['receiverAddress'], $d['zoneCode'] ?: null]);
    }

    if ($parcel) {
        $pdo->prepare("UPDATE Parcel SET description = ?, parcelCategory = ?, isFragile = ?, serviceType = ?, declaredWeight = ?,
                                         dimensions = ?, receiverId = ? WHERE parcelId = ?")
            ->execute([$d['description'], $d['parcelCategory'], $d['isFragile'], $d['serviceType'], round((float)$d['declaredWeight'], 2),
                       pickup_dimensions($d), $receiverId, $parcel['parcelId']]);
        return $parcel['parcelId'];
    }
    $parcelId = id_next($pdo, 'Parcel', 'parcelId', 'HEN-' . date('Y') . '-');
    $pdo->prepare("INSERT INTO Parcel (parcelId, description, parcelCategory, isFragile, serviceType, intakeMethod, declaredWeight,
                                       dimensions, detailsConfirmed, status, customerId, receiverId, pickupId)
                   VALUES (?,?,?,?,?,'PICKUP',?,?,0,'PENDING_PICKUP',?,?,?)")
        ->execute([$parcelId, $d['description'], $d['parcelCategory'], $d['isFragile'], $d['serviceType'],
                   round((float)$d['declaredWeight'], 2), pickup_dimensions($d), $customerId, $receiverId, $pickupId]);
    return $parcelId;
}

/**
 * Customer.requestPickup(): creates the pickup and records its parcel and receiver.
 * $d must come from pickup_clean() and pass pickup_validate(). Returns the new pickupId.
 */
function requestPickup(PDO $pdo, string $customerId, array $d): string {
    return pickup_tx($pdo, function () use ($pdo, $customerId, $d) {
        $pickupId = id_next($pdo, 'Pickup', 'pickupId', 'PCK-' . date('Y') . '-');
        $pdo->prepare("INSERT INTO Pickup (pickupId, requestedAt, pickupDate, preferredTimeWindow, pickupAddress, status, customerId)
                       VALUES (?, NOW(), ?, ?, ?, ?, ?)")
            ->execute([$pickupId, $d['pickupDate'], $d['preferredTimeWindow'], $d['pickupAddress'], PK_REQUESTED, $customerId]);

        recordParcelDetails($pdo, $pickupId, $customerId, $d);
        return $pickupId;
    });
}

/* ============================================================================================
 *  READ
 * ============================================================================================ */

/** Canonical status => number of pickups, for the dashboard and filter tabs */
function pickup_counts(PDO $pdo, string $customerId): array {
    $st = $pdo->prepare("SELECT status, COUNT(*) c FROM Pickup WHERE customerId = ? GROUP BY status");
    $st->execute([$customerId]);
    $out = [];
    foreach ($st->fetchAll(PDO::FETCH_KEY_PAIR) as $status => $n) {
        $k = pickup_status($status);
        $out[$k] = ($out[$k] ?? 0) + (int)$n;
    }
    return $out;
}

/** This customer's pickups with parcel / receiver summary; optional status filter and search */
function pickup_list(PDO $pdo, string $customerId, string $status = '', string $q = ''): array {
    $firstParcel = "(SELECT pc.parcelId FROM Parcel pc WHERE pc.pickupId = p.pickupId ORDER BY pc.parcelId LIMIT 1)";
    $sql = "SELECT p.*,
              (SELECT description  FROM Parcel WHERE parcelId = $firstParcel) AS parcelDescription,
              (SELECT serviceType  FROM Parcel WHERE parcelId = $firstParcel) AS parcelService,
              (SELECT r.receiverName FROM Parcel pc JOIN Receiver r ON r.receiverId = pc.receiverId WHERE pc.parcelId = $firstParcel) AS receiverName
            FROM Pickup p WHERE p.customerId = ?";
    $params = [$customerId];

    $status = strtoupper($status);
    if (in_array($status, PK_ALL, true)) {
        $names = [$status];
        foreach (PK_SYNONYMS as $alias => $canon) if ($canon === $status) $names[] = $alias;
        $sql .= " AND p.status IN (" . implode(',', array_fill(0, count($names), '?')) . ")";
        array_push($params, ...$names);
    }
    $q = trim($q);
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= " AND (p.pickupAddress LIKE ? OR p.pickupId LIKE ? OR EXISTS (
                    SELECT 1 FROM Parcel pc LEFT JOIN Receiver r ON r.receiverId = pc.receiverId
                    WHERE pc.pickupId = p.pickupId AND (pc.description LIKE ? OR r.receiverName LIKE ?)))";
        array_push($params, $like, $like, $like, $like);
    }
    $st = $pdo->prepare($sql . " ORDER BY p.requestedAt DESC, p.pickupId DESC");
    $st->execute($params);
    return $st->fetchAll();
}

/** One pickup (ownership enforced), or null */
function pickup_get(PDO $pdo, string $pickupId, string $customerId): ?array {
    if (!pickup_valid_id($pickupId)) return null;
    $st = $pdo->prepare("SELECT * FROM Pickup WHERE pickupId = ? AND customerId = ?");
    $st->execute([$pickupId, $customerId]);
    return $st->fetch() ?: null;
}

/** Pickup plus ['parcel' => row|null, 'receiver' => row|null] */
function pickup_details(PDO $pdo, string $pickupId, string $customerId): ?array {
    $p = pickup_get($pdo, $pickupId, $customerId);
    if (!$p) return null;

    $st = $pdo->prepare("SELECT * FROM Parcel WHERE pickupId = ? ORDER BY parcelId LIMIT 1");
    $st->execute([$pickupId]);
    $p['parcel'] = $st->fetch() ?: null;

    $p['receiver'] = null;
    if ($p['parcel']) {
        $st = $pdo->prepare("SELECT * FROM Receiver WHERE receiverId = ?");
        $st->execute([$p['parcel']['receiverId']]);
        $p['receiver'] = $st->fetch() ?: null;
    }

    return $p;
}

/** Values to pre-fill the edit form from a pickup_details() result */
function pickup_form_values(array $p): array {
    $pc = $p['parcel'] ?? [];
    $rc = $p['receiver'] ?? [];
    return array_merge([
        'pickupAddress' => $p['pickupAddress'], 'pickupDate' => $p['pickupDate'], 'preferredTimeWindow' => $p['preferredTimeWindow'],
        'description' => $pc['description'] ?? '', 'parcelCategory' => $pc['parcelCategory'] ?? '', 'serviceType' => $pc['serviceType'] ?? '',
        'isFragile' => !empty($pc['isFragile']) ? 1 : 0, 'declaredWeight' => $pc['declaredWeight'] ?? '',
        'receiverName' => $rc['receiverName'] ?? '', 'receiverPhone' => $rc['receiverPhone'] ?? '',
        'receiverAddress' => $rc['receiverAddress'] ?? '', 'zoneCode' => $rc['zoneCode'] ?? '',
    ], pickup_split_dimensions($pc['dimensions'] ?? null));
}

/** Soonest upcoming pickup that is still live, or null */
function pickup_next(PDO $pdo, string $customerId): ?array {
    $st = $pdo->prepare(
        "SELECT * FROM Pickup WHERE customerId = ? AND UPPER(status) IN ('REQUESTED','APPROVED','SCHEDULED','ASSIGNED') AND pickupDate >= CURDATE()
         ORDER BY pickupDate ASC, pickupId ASC LIMIT 1"
    );
    $st->execute([$customerId]);
    return $st->fetch() ?: null;
}

/* ============================================================================================
 *  UPDATE   (Pickup.updatePickup) - only while the pickup is still REQUESTED
 * ============================================================================================ */

/** Pickup.updatePickup(): edit a pickup, its parcel and receiver - only while REQUESTED.
 *  Returns false when the pickup is missing or no longer editable. */
function updatePickup(PDO $pdo, string $pickupId, string $customerId, array $d): bool {
    return pickup_tx($pdo, function () use ($pdo, $pickupId, $customerId, $d) {
        $st = $pdo->prepare("SELECT status FROM Pickup WHERE pickupId = ? AND customerId = ? FOR UPDATE");
        $st->execute([$pickupId, $customerId]);
        $row = $st->fetch();
        if (!$row || pickup_status($row['status']) !== PK_REQUESTED) return false;

        $pdo->prepare("UPDATE Pickup SET pickupDate = ?, preferredTimeWindow = ?, pickupAddress = ? WHERE pickupId = ?")
            ->execute([$d['pickupDate'], $d['preferredTimeWindow'], $d['pickupAddress'], $pickupId]);

        recordParcelDetails($pdo, $pickupId, $customerId, $d);
        return true;
    });
}

/* ============================================================================================
 *  CANCEL = DELETE   (Customer.cancelPickup / Pickup.cancelPickup)
 * ============================================================================================ */

/**
 * Permanently deletes a pickup together with its parcel and receiver.
 * Allowed only for the owner while the status is REQUESTED or APPROVED.
 * Returns false when it is not allowed, or when another table (tracking, delivery ...) still points at the parcel.
 */
function cancelPickup(PDO $pdo, string $pickupId, string $customerId): bool {
    try {
        return pickup_tx($pdo, function () use ($pdo, $pickupId, $customerId) {
            $st = $pdo->prepare("SELECT status FROM Pickup WHERE pickupId = ? AND customerId = ? FOR UPDATE");
            $st->execute([$pickupId, $customerId]);
            $row = $st->fetch();
            if (!$row || !in_array(pickup_status($row['status']), [PK_REQUESTED, PK_APPROVED], true)) return false;

            // leftover estimate rows from earlier test data would block the delete, so remove them first
            $pdo->prepare("DELETE FROM CostEstimatePricingRule WHERE estimateId IN (SELECT estimateId FROM CostEstimate WHERE pickupId = ?)")->execute([$pickupId]);
            $pdo->prepare("DELETE FROM CostEstimate WHERE pickupId = ?")->execute([$pickupId]);

            // parcels + their receivers (a receiver is removed only if no other parcel uses it)
            $st = $pdo->prepare("SELECT parcelId, receiverId FROM Parcel WHERE pickupId = ?");
            $st->execute([$pickupId]);
            foreach ($st->fetchAll() as $pc) {
                $pdo->prepare("DELETE FROM ParcelPricingRule WHERE parcelId = ?")->execute([$pc['parcelId']]);
                $pdo->prepare("DELETE FROM Parcel WHERE parcelId = ?")->execute([$pc['parcelId']]);
                $pdo->prepare("DELETE FROM Receiver WHERE receiverId = ? AND NOT EXISTS (SELECT 1 FROM Parcel WHERE receiverId = ?)")
                    ->execute([$pc['receiverId'], $pc['receiverId']]);
            }

            $del = $pdo->prepare("DELETE FROM Pickup WHERE pickupId = ? AND customerId = ?");
            $del->execute([$pickupId, $customerId]);
            return $del->rowCount() > 0;
        }, false);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') return false;   // still referenced elsewhere: leave everything untouched (rolled back)
        throw $e;
    }
}
