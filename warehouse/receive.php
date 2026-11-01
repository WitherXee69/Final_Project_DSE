<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/id_generator.php';
require_once __DIR__ . '/_layout.php';

$error = '';

$customers = [];
$receivers = [];
$warehouses = [];

$categories = [
    'HOUSEHOLD',
    'DOCUMENT',
    'COMMERCIAL',
    'PERSONAL',
    'ELECTRONICS'
];

$serviceTypes = [
    'STANDARD',
    'EXPRESS'
];

$intakeMethods = [
    'WALK_IN',
    'PICKUP'
];

try {

    $customerQuery = $pdo->query(
        "SELECT
            c.userId,
            COALESCE(
                ic.name,
                cc.companyName,
                CONCAT('Customer #', c.userId)
            ) AS customerName
         FROM Customer c
         LEFT JOIN IndividualCustomer ic
            ON ic.userId = c.userId
         LEFT JOIN CorporateCustomer cc
            ON cc.userId = c.userId
         ORDER BY customerName"
    );

    $customers = $customerQuery->fetchAll();


    $receiverQuery = $pdo->query(
        "SELECT
            receiverId,
            receiverName,
            receiverPhone,
            zoneCode
         FROM Receiver
         ORDER BY receiverName"
    );

    $receivers = $receiverQuery->fetchAll();


    $warehouseQuery = $pdo->query(
        "SELECT
            warehouseId,
            warehouseName,
            zoneCode,
            status
         FROM Warehouse
         ORDER BY warehouseName"
    );

    $warehouses = $warehouseQuery->fetchAll();

} catch (PDOException $e) {

    error_log('Warehouse intake option error: ' . $e->getMessage());

    $error = 'Unable to load the intake options. Please try again later.';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {

    $customerId = trim($_POST['customerId'] ?? '');
    $receiverId = trim($_POST['receiverId'] ?? '');
    $warehouseId = trim($_POST['warehouseId'] ?? '');

    $description = trim($_POST['description'] ?? '');
    $parcelCategory = $_POST['parcelCategory'] ?? '';
    $serviceType = $_POST['serviceType'] ?? '';
    $intakeMethod = $_POST['intakeMethod'] ?? '';

    $declaredWeightInput = trim($_POST['declaredWeight'] ?? '');
    $dimensions = trim($_POST['dimensions'] ?? '');

    $isFragile = isset($_POST['isFragile']) ? 1 : 0;


    // Required relationship validation.
    if ($customerId === '' || $receiverId === '' || $warehouseId === '') {

        $error = 'Select a customer, receiver and warehouse.';

    } elseif ($description === '') {

        $error = 'Enter a parcel description.';

    } elseif (strlen($description) > 255) {

        $error = 'The parcel description cannot exceed 255 characters.';

    } elseif (!in_array($parcelCategory, $categories, true)) {

        $error = 'Select a valid parcel category.';

    } elseif (!in_array($serviceType, $serviceTypes, true)) {

        $error = 'Select a valid delivery service.';

    } elseif (!in_array($intakeMethod, $intakeMethods, true)) {

        $error = 'Select a valid intake method.';

    } elseif ($dimensions !== '' && strlen($dimensions) > 50) {

        $error = 'Dimensions cannot exceed 50 characters.';

    } elseif (
        $declaredWeightInput !== '' &&
        (
            !is_numeric($declaredWeightInput) ||
            (float) $declaredWeightInput < 0 ||
            (float) $declaredWeightInput > 99999999.99
        )
    ) {

        $error = 'Enter a valid declared weight.';

    } else {

        // Convert an empty weight field into SQL NULL.
        $declaredWeight = $declaredWeightInput === ''
            ? null
            : (float) $declaredWeightInput;


        try {

            // Verify that the selected records actually exist.
            $customerCheck = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM Customer
                 WHERE userId = :customerId"
            );

            $customerCheck->execute([
                'customerId' => $customerId
            ]);


            $receiverCheck = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM Receiver
                 WHERE receiverId = :receiverId"
            );

            $receiverCheck->execute([
                'receiverId' => $receiverId
            ]);


            $warehouseCheck = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM Warehouse
                 WHERE warehouseId = :warehouseId"
            );

            $warehouseCheck->execute([
                'warehouseId' => $warehouseId
            ]);


            if (
                (int) $customerCheck->fetchColumn() === 0 ||
                (int) $receiverCheck->fetchColumn() === 0 ||
                (int) $warehouseCheck->fetchColumn() === 0
            ) {

                $error = 'One or more selected records are no longer available. Please refresh the page.';

            } else {

                // Generate the parcel ID and insert the parcel together.
                $pdo->beginTransaction();

                $year = date('Y');

                $parcelId = generateNextId(
                    $pdo,
                    'PARCEL_' . $year,
                    'HEN-' . $year . '-',
                    6
                );


                $insert = $pdo->prepare(
                    "INSERT INTO Parcel (
                        parcelId,
                        description,
                        parcelCategory,
                        isFragile,
                        serviceType,
                        intakeMethod,
                        declaredWeight,
                        confirmedWeight,
                        dimensions,
                        detailsConfirmed,
                        parcelReceivedDate,
                        storageLocationCode,
                        status,
                        holdExpiresAt,
                        failureReason,
                        customerId,
                        receiverId,
                        csoId,
                        pickupId,
                        warehouseId
                    ) VALUES (
                        :parcelId,
                        :description,
                        :parcelCategory,
                        :isFragile,
                        :serviceType,
                        :intakeMethod,
                        :declaredWeight,
                        NULL,
                        :dimensions,
                        0,
                        CURDATE(),
                        NULL,
                        'RECEIVED_AT_WAREHOUSE',
                        NULL,
                        NULL,
                        :customerId,
                        :receiverId,
                        NULL,
                        NULL,
                        :warehouseId
                    )"
                );


                $insert->execute([
                    'parcelId' => $parcelId,
                    'description' => $description,
                    'parcelCategory' => $parcelCategory,
                    'isFragile' => $isFragile,
                    'serviceType' => $serviceType,
                    'intakeMethod' => $intakeMethod,
                    'declaredWeight' => $declaredWeight,
                    'dimensions' => $dimensions !== '' ? $dimensions : null,
                    'customerId' => $customerId,
                    'receiverId' => $receiverId,
                    'warehouseId' => $warehouseId
                ]);


                $pdo->commit();

                header(
                    'Location: view.php?id=' . urlencode($parcelId) . '&created=1'
                );

                exit;
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('Warehouse parcel registration error: ' . $e->getMessage());

            $error = 'Unable to register the parcel. Please check the information and try again.';
        }
    }
}


// Retain submitted values if validation fails.
$old = $_POST;

wh_header('Receive parcel', 'receive');

?>

<section class="wh-panel">

    <div class="wh-panel-head">

        <div>
            <h2>Record a parcel arriving at the warehouse</h2>

            <p class="wh-muted">
                Enter the parcel details supplied at intake.
                Confirmed weight and storage location can be recorded during processing.
            </p>
        </div>

    </div>


    <?php if ($error !== ''): ?>

        <p class="wh-error">
            <?= wh_h($error) ?>
        </p>

    <?php endif; ?>


    <?php if (
        empty($customers) ||
        empty($receivers) ||
        empty($warehouses)
    ): ?>

        <p class="wh-error">
            The receiving form requires at least one customer,
            receiver and warehouse record in the database.
            Please register the missing records first.
        </p>

    <?php else: ?>

        <form class="wh-form" method="post">

            <div class="wh-field">

                <label for="customerId">
                    Customer *
                </label>

                <select id="customerId" name="customerId" required>

                    <option value="">
                        Select customer
                    </option>

                    <?php foreach ($customers as $customer): ?>

                        <option
                            value="<?= wh_h($customer['userId']) ?>"
                            <?= ($old['customerId'] ?? '') === $customer['userId'] ? 'selected' : '' ?>
                        >
                            <?= wh_h($customer['customerName']) ?>
                            (<?= wh_h($customer['userId']) ?>)
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="wh-field">

                <label for="receiverId">
                    Receiver *
                </label>

                <select id="receiverId" name="receiverId" required>

                    <option value="">
                        Select receiver
                    </option>

                    <?php foreach ($receivers as $receiver): ?>

                        <option
                            value="<?= wh_h($receiver['receiverId']) ?>"
                            <?= ($old['receiverId'] ?? '') === $receiver['receiverId'] ? 'selected' : '' ?>
                        >
                            <?= wh_h($receiver['receiverName']) ?>
                            (<?= wh_h($receiver['receiverId']) ?>)
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="wh-field">

                <label for="warehouseId">
                    Warehouse *
                </label>

                <select id="warehouseId" name="warehouseId" required>

                    <option value="">
                        Select warehouse
                    </option>

                    <?php foreach ($warehouses as $warehouse): ?>

                        <option
                            value="<?= wh_h($warehouse['warehouseId']) ?>"
                            <?= ($old['warehouseId'] ?? '') === $warehouse['warehouseId'] ? 'selected' : '' ?>
                        >
                            <?= wh_h($warehouse['warehouseName']) ?>
                            (<?= wh_h($warehouse['warehouseId']) ?>)
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="wh-field full">

                <label for="description">
                    Parcel description *
                </label>

                <input
                    id="description"
                    name="description"
                    type="text"
                    maxlength="255"
                    required
                    placeholder="Brief description of contents"
                    value="<?= wh_h($old['description'] ?? '') ?>"
                >

            </div>


            <div class="wh-field">

                <label for="parcelCategory">
                    Parcel category *
                </label>

                <select
                    id="parcelCategory"
                    name="parcelCategory"
                    required
                >

                    <option value="">
                        Select category
                    </option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= wh_h($category) ?>"
                            <?= ($old['parcelCategory'] ?? '') === $category ? 'selected' : '' ?>
                        >
                            <?= wh_h(ucwords(strtolower($category))) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="wh-field">

                <label for="serviceType">
                    Delivery service *
                </label>

                <select
                    id="serviceType"
                    name="serviceType"
                    required
                >

                    <?php foreach ($serviceTypes as $service): ?>

                        <option
                            value="<?= wh_h($service) ?>"
                            <?= ($old['serviceType'] ?? 'STANDARD') === $service ? 'selected' : '' ?>
                        >
                            <?= wh_h(ucwords(strtolower($service))) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="wh-field">

                <label for="intakeMethod">
                    Intake method *
                </label>

                <select
                    id="intakeMethod"
                    name="intakeMethod"
                    required
                >

                    <?php foreach ($intakeMethods as $method): ?>

                        <option
                            value="<?= wh_h($method) ?>"
                            <?= ($old['intakeMethod'] ?? 'WALK_IN') === $method ? 'selected' : '' ?>
                        >
                            <?= $method === 'WALK_IN' ? 'Walk-in' : 'Pickup' ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="wh-field">

                <label for="declaredWeight">
                    Declared weight (kg)
                </label>

                <input
                    id="declaredWeight"
                    name="declaredWeight"
                    type="number"
                    min="0"
                    max="99999999.99"
                    step="0.01"
                    placeholder="0.00"
                    value="<?= wh_h($old['declaredWeight'] ?? '') ?>"
                >

            </div>


            <div class="wh-field">

                <label for="dimensions">
                    Dimensions
                </label>

                <input
                    id="dimensions"
                    name="dimensions"
                    type="text"
                    maxlength="50"
                    placeholder="L x W x H cm"
                    value="<?= wh_h($old['dimensions'] ?? '') ?>"
                >

            </div>


            <div class="wh-field">

                <label class="wh-check">

                    <input
                        type="checkbox"
                        name="isFragile"
                        value="1"
                        <?= isset($old['isFragile']) ? 'checked' : '' ?>
                    >

                    Fragile parcel

                </label>

                <small>
                    Handle with additional care where required.
                </small>

            </div>


            <div class="wh-form-actions">

                <button class="wh-btn" type="submit">
                    Save received parcel
                </button>

                <a class="wh-btn secondary" href="parcels.php">
                    Cancel
                </a>

            </div>

        </form>

    <?php endif; ?>

</section>

<?php wh_footer(); ?>