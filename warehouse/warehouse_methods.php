<?php
/**
 * HELMS Warehouse module - methods from the class diagram.
 *
 * Plain functions (no classes). Each function name matches the method name in
 * FINAL CLASS.drawio. The owning class is noted above each function.
 *
 * Every function takes the PDO connection first and throws RuntimeException
 * with a user-friendly message when validation / business rules fail.
 */

require_once __DIR__ . '/../config/id_generator.php';
require_once __DIR__ . '/_layout.php';

const WH_CATEGORIES    = ['HOUSEHOLD', 'DOCUMENT', 'COMMERCIAL', 'PERSONAL', 'ELECTRONICS'];
const WH_SERVICE_TYPES = ['STANDARD', 'EXPRESS'];
const WH_INTAKE_METHODS = ['WALK_IN', 'PICKUP'];


/* ---------------------------------------------------------------------
 * Internal helpers (not in the diagram)
 * ------------------------------------------------------------------- */

/** Validate the editable parcel fields shared by receive and edit. */
function wh_validate_parcel_fields(array $d): array
{
    $description = trim((string)($d['description'] ?? ''));
    $category    = (string)($d['parcelCategory'] ?? '');
    $serviceType = (string)($d['serviceType'] ?? '');
    $intake      = (string)($d['intakeMethod'] ?? '');
    $weight      = trim((string)($d['declaredWeight'] ?? ''));
    $dimensions  = trim((string)($d['dimensions'] ?? ''));

    if ($description === '') {
        throw new RuntimeException('Enter a parcel description.');
    }
    if (strlen($description) > 255) {
        throw new RuntimeException('The parcel description cannot exceed 255 characters.');
    }
    if (!in_array($category, WH_CATEGORIES, true)) {
        throw new RuntimeException('Select a valid parcel category.');
    }
    if (!in_array($serviceType, WH_SERVICE_TYPES, true)) {
        throw new RuntimeException('Select a valid delivery service.');
    }
    if (!in_array($intake, WH_INTAKE_METHODS, true)) {
        throw new RuntimeException('Select a valid intake method.');
    }
    if (strlen($dimensions) > 50) {
        throw new RuntimeException('Dimensions cannot exceed 50 characters.');
    }
    if ($weight !== '' && (!is_numeric($weight) || (float)$weight < 0 || (float)$weight > 99999999.99)) {
        throw new RuntimeException('Enter a valid declared weight.');
    }

    return [
        'description'    => $description,
        'parcelCategory' => $category,
        'serviceType'    => $serviceType,
        'intakeMethod'   => $intake,
        'declaredWeight' => $weight === '' ? null : (float)$weight,
        'dimensions'     => $dimensions === '' ? null : $dimensions,
        'isFragile'      => !empty($d['isFragile']) ? 1 : 0,
    ];
}

/** Lock and return a parcel row inside an open transaction. */
function wh_lock_parcel(PDO $pdo, string $parcelId): array
{
    $stmt = $pdo->prepare("SELECT * FROM Parcel WHERE parcelId = :id FOR UPDATE");
    $stmt->execute(['id' => $parcelId]);
    $parcel = $stmt->fetch();

    if (!$parcel) {
        throw new RuntimeException('Parcel not found.');
    }
    return $parcel;
}

/** Run $work inside a transaction on a locked parcel. */
function wh_with_locked_parcel(PDO $pdo, string $parcelId, callable $work): void
{
    try {
        $pdo->beginTransaction();
        $parcel = wh_lock_parcel($pdo, $parcelId);
        $work($parcel);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}


/* ---------------------------------------------------------------------
 * Class: Parcel
 * ------------------------------------------------------------------- */

// Parcel.setStatus()
function setStatus(PDO $pdo, string $parcelId, string $status): void
{
    if (!array_key_exists($status, wh_status_options())) {
        throw new RuntimeException('Invalid parcel status.');
    }
    $stmt = $pdo->prepare("UPDATE Parcel SET status = :status WHERE parcelId = :id");
    $stmt->execute(['status' => $status, 'id' => $parcelId]);
}

// Parcel.markReceivedByWarehouse()
function markReceivedByWarehouse(PDO $pdo, string $parcelId): void
{
    setStatus($pdo, $parcelId, 'RECEIVED_AT_WAREHOUSE');
}

// Parcel.markReleasedToSender()
function markReleasedToSender(PDO $pdo, string $parcelId): void
{
    setStatus($pdo, $parcelId, 'RELEASED_TO_SENDER');
}


/* ---------------------------------------------------------------------
 * Class: WarehouseOfficer
 * ------------------------------------------------------------------- */

// WarehouseOfficer.receiveParcel()
// Creates (C) a parcel record at intake. Returns the new parcelId.
function receiveParcel(PDO $pdo, array $data): string
{
    $customerId  = trim((string)($data['customerId'] ?? ''));
    $receiverId  = trim((string)($data['receiverId'] ?? ''));
    $warehouseId = trim((string)($data['warehouseId'] ?? ''));

    if ($customerId === '' || $receiverId === '' || $warehouseId === '') {
        throw new RuntimeException('Select a customer, receiver and warehouse.');
    }

    $fields = wh_validate_parcel_fields($data);

    // Verify the selected related records exist.
    $checks = [
        ['Customer',  'userId',      $customerId],
        ['Receiver',  'receiverId',  $receiverId],
        ['Warehouse', 'warehouseId', $warehouseId],
    ];
    foreach ($checks as [$table, $column, $value]) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = :v");
        $stmt->execute(['v' => $value]);
        if ((int)$stmt->fetchColumn() === 0) {
            throw new RuntimeException(
                'One or more selected records are no longer available. Please refresh the page.'
            );
        }
    }

    try {
        $pdo->beginTransaction();

        $year     = date('Y');
        $parcelId = generateNextId($pdo, 'PARCEL_' . $year, 'HEN-' . $year . '-', 6);

        $stmt = $pdo->prepare(
            "INSERT INTO Parcel (
                parcelId, description, parcelCategory, isFragile, serviceType,
                intakeMethod, declaredWeight, confirmedWeight, dimensions,
                detailsConfirmed, parcelReceivedDate, storageLocationCode, status,
                holdExpiresAt, failureReason, customerId, receiverId, csoId,
                pickupId, warehouseId
            ) VALUES (
                :parcelId, :description, :parcelCategory, :isFragile, :serviceType,
                :intakeMethod, :declaredWeight, NULL, :dimensions,
                0, CURDATE(), NULL, 'RECEIVED_AT_WAREHOUSE',
                NULL, NULL, :customerId, :receiverId, NULL,
                NULL, :warehouseId
            )"
        );
        $stmt->execute($fields + [
            'parcelId'    => $parcelId,
            'customerId'  => $customerId,
            'receiverId'  => $receiverId,
            'warehouseId' => $warehouseId,
        ]);

        $pdo->commit();
        return $parcelId;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('receiveParcel failed: ' . $e->getMessage());
        throw new RuntimeException(
            'Unable to register the parcel. Please check the information and try again.'
        );
    }
}

// WarehouseOfficer.viewParcelDetails()  (R)
function viewParcelDetails(PDO $pdo, string $parcelId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT
            p.*,
            COALESCE(NULLIF(ic.name, ''), NULLIF(cc.companyName, ''), c.userId) AS customerName,
            c.contactNumber AS customerPhone,
            c.email         AS customerEmail,
            c.address       AS customerAddress,
            r.receiverName,
            r.receiverPhone,
            r.receiverAddress,
            r.zoneCode      AS receiverZone,
            w.warehouseName,
            w.zoneCode      AS warehouseZone
         FROM Parcel p
         LEFT JOIN Customer c            ON p.customerId = c.userId
         LEFT JOIN IndividualCustomer ic ON c.userId = ic.userId
         LEFT JOIN CorporateCustomer cc  ON c.userId = cc.userId
         LEFT JOIN Receiver r            ON p.receiverId = r.receiverId
         LEFT JOIN Warehouse w           ON p.warehouseId = w.warehouseId
         WHERE p.parcelId = :parcelId
         LIMIT 1"
    );
    $stmt->execute(['parcelId' => $parcelId]);

    return $stmt->fetch() ?: null;
}

// WarehouseOfficer.viewParcelLocation()  (R)
function viewParcelLocation(PDO $pdo, string $parcelId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT p.parcelId, p.storageLocationCode, p.warehouseId,
                w.warehouseName, w.zoneCode
         FROM Parcel p
         LEFT JOIN Warehouse w ON p.warehouseId = w.warehouseId
         WHERE p.parcelId = :parcelId
         LIMIT 1"
    );
    $stmt->execute(['parcelId' => $parcelId]);

    return $stmt->fetch() ?: null;
}

// WarehouseOfficer.updateParcelDetails()  (U)
function updateParcelDetails(PDO $pdo, string $parcelId, array $data): void
{
    $fields = wh_validate_parcel_fields($data);

    try {
        $stmt = $pdo->prepare(
            "UPDATE Parcel SET
                description    = :description,
                parcelCategory = :parcelCategory,
                isFragile      = :isFragile,
                serviceType    = :serviceType,
                intakeMethod   = :intakeMethod,
                declaredWeight = :declaredWeight,
                dimensions     = :dimensions
             WHERE parcelId = :parcelId"
        );
        $stmt->execute($fields + ['parcelId' => $parcelId]);
    } catch (PDOException $e) {
        error_log('updateParcelDetails failed: ' . $e->getMessage());
        throw new RuntimeException('Unable to save parcel changes. Please try again.');
    }
}

// WarehouseOfficer.confirmParcelDetails()  (U)  - also Parcel.confirmDetails()
function confirmParcelDetails(PDO $pdo, string $parcelId, $confirmedWeight): void
{
    $weight = trim((string)$confirmedWeight);
    if ($weight === '' || !is_numeric($weight) || (float)$weight <= 0) {
        throw new RuntimeException('Enter a valid confirmed weight greater than zero.');
    }

    wh_with_locked_parcel($pdo, $parcelId, function (array $parcel) use ($pdo, $parcelId, $weight) {
        $status = wh_normalize_status($parcel['status']);
        if (!in_array($status, ['RECEIVED_AT_WAREHOUSE', 'DETAILS_CONFIRMED'], true)) {
            throw new RuntimeException('Parcel details cannot be confirmed at its current status.');
        }

        $stmt = $pdo->prepare(
            "UPDATE Parcel
             SET confirmedWeight = :weight,
                 detailsConfirmed = 1,
                 status = 'DETAILS_CONFIRMED'
             WHERE parcelId = :parcelId"
        );
        $stmt->execute(['weight' => (float)$weight, 'parcelId' => $parcelId]);
    });
}

// WarehouseOfficer.assignStorageLocation()  (U)  - also Parcel.assignStorageLocation()
function assignStorageLocation(PDO $pdo, string $parcelId, string $storageLocationCode): void
{
    $location = strtoupper(trim($storageLocationCode));
    if ($location === '') {
        throw new RuntimeException('Enter a storage location code.');
    }
    if (strlen($location) > 50) {
        throw new RuntimeException('Storage location code must not exceed 50 characters.');
    }

    wh_with_locked_parcel($pdo, $parcelId, function (array $parcel) use ($pdo, $parcelId, $location) {
        $status = wh_normalize_status($parcel['status']);
        if (!in_array($status, ['RECEIVED_AT_WAREHOUSE', 'DETAILS_CONFIRMED'], true)) {
            throw new RuntimeException(
                "Storage location cannot be changed at the parcel's current status."
            );
        }

        $stmt = $pdo->prepare(
            "UPDATE Parcel SET storageLocationCode = :location WHERE parcelId = :parcelId"
        );
        $stmt->execute(['location' => $location, 'parcelId' => $parcelId]);
    });
}

// WarehouseOfficer.markParcelPacked()  (U)
function markParcelPacked(PDO $pdo, string $parcelId): void
{
    wh_with_locked_parcel($pdo, $parcelId, function (array $parcel) use ($pdo, $parcelId) {
        if (wh_normalize_status($parcel['status']) !== 'DETAILS_CONFIRMED') {
            throw new RuntimeException('Parcel details must be confirmed before packing.');
        }
        if (!(bool)$parcel['detailsConfirmed']) {
            throw new RuntimeException('Confirm parcel details before marking it packed.');
        }
        if (trim((string)($parcel['storageLocationCode'] ?? '')) === '') {
            throw new RuntimeException('Assign a storage location before marking it packed.');
        }

        setStatus($pdo, $parcelId, 'PACKED');
    });
}

// WarehouseOfficer.releaseParcelToSender()  (U)
function releaseParcelToSender(PDO $pdo, string $parcelId): void
{
    wh_with_locked_parcel($pdo, $parcelId, function (array $parcel) use ($pdo, $parcelId) {
        if (wh_normalize_status($parcel['status']) !== 'PACKED') {
            throw new RuntimeException('Only packed parcels can be released to the sender.');
        }

        markReleasedToSender($pdo, $parcelId);
    });
}


/* ---------------------------------------------------------------------
 * Class: WarehouseManager
 * ------------------------------------------------------------------- */

// WarehouseManager.generateStorageReport()  (R)
// Builds the StorageReport data (parcelsStored, unclaimedParcels,
// capacityUtilisation) for the manager's assigned warehouse.
function generateStorageReport(PDO $pdo, string $managerUserId): array
{
    $stmt = $pdo->prepare(
        "SELECT w.warehouseId, w.warehouseName, w.zoneCode, w.capacity, w.currentLoad
         FROM WarehouseManager wm
         INNER JOIN Warehouse w ON w.warehouseId = wm.warehouseId
         WHERE wm.userId = :userId
         LIMIT 1"
    );
    $stmt->execute(['userId' => $managerUserId]);
    $warehouse = $stmt->fetch();

    if (!$warehouse) {
        throw new RuntimeException('No warehouse is assigned to this manager.');
    }

    $stmt = $pdo->prepare(
        "SELECT status, COUNT(*) AS total
         FROM Parcel
         WHERE warehouseId = :warehouseId
         GROUP BY status
         ORDER BY status"
    );
    $stmt->execute(['warehouseId' => $warehouse['warehouseId']]);
    $byStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $capacity = (int)$warehouse['capacity'];
    $load     = (int)$warehouse['currentLoad'];

    return [
        'generatedAt'         => date('Y-m-d H:i:s'),
        'warehouse'           => $warehouse,
        'parcelsStored'       => $load,
        'unclaimedParcels'    => (int)($byStatus['UNCLAIMED'] ?? 0),
        'capacityUtilisation' => $capacity > 0 ? round($load / $capacity * 100, 1) : 0.0,
        'byStatus'            => $byStatus,
    ];
}
