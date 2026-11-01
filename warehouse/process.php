
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/_layout.php';

$id = strtoupper(trim($_GET['id'] ?? ''));
$error = '';
$success = isset($_GET['saved']);

$parcel = null;

/**
 * Load the parcel from the database.
 */
function wh_load_process_parcel(PDO $pdo, string $parcelId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT *
         FROM Parcel
         WHERE parcelId = :parcelId
         LIMIT 1"
    );

    $stmt->execute(['parcelId' => $parcelId]);

    $parcel = $stmt->fetch();

    return $parcel ?: null;
}

if ($id === '') {
    $error = 'A parcel ID is required.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    try {
        $pdo->beginTransaction();

        // Lock this parcel while its processing action is being performed.
        $stmt = $pdo->prepare(
            "SELECT *
             FROM Parcel
             WHERE parcelId = :parcelId
             FOR UPDATE"
        );

        $stmt->execute(['parcelId' => $id]);
        $parcel = $stmt->fetch();

        if (!$parcel) {
            throw new RuntimeException('Parcel not found.');
        }

        $status = wh_normalize_status($parcel['status']);

        if ($action === 'confirm') {

            if (!in_array($status, [
                'RECEIVED_AT_WAREHOUSE',
                'DETAILS_CONFIRMED'
            ], true)) {
                throw new RuntimeException(
                    'Parcel details cannot be confirmed at its current status.'
                );
            }

            $weight = trim($_POST['confirmedWeight'] ?? '');

            if (
                $weight === '' ||
                !is_numeric($weight) ||
                (float)$weight <= 0
            ) {
                throw new RuntimeException(
                    'Enter a valid confirmed weight greater than zero.'
                );
            }

            $stmt = $pdo->prepare(
                "UPDATE Parcel
                 SET confirmedWeight = :weight,
                     detailsConfirmed = 1,
                     status = 'DETAILS_CONFIRMED'
                 WHERE parcelId = :parcelId"
            );

            $stmt->execute([
                'weight' => (float)$weight,
                'parcelId' => $id
            ]);

        } elseif ($action === 'location') {

            if (!in_array($status, [
                'RECEIVED_AT_WAREHOUSE',
                'DETAILS_CONFIRMED'
            ], true)) {
                throw new RuntimeException(
                    'Storage location cannot be changed at the parcel\'s current status.'
                );
            }

            $location = strtoupper(
                trim($_POST['storageLocationCode'] ?? '')
            );

            if ($location === '') {
                throw new RuntimeException(
                    'Enter a storage location code.'
                );
            }

            if (strlen($location) > 50) {
                throw new RuntimeException(
                    'Storage location code must not exceed 50 characters.'
                );
            }

            // Assigning a location does not automatically mean the parcel is packed.
            $stmt = $pdo->prepare(
                "UPDATE Parcel
                 SET storageLocationCode = :location
                 WHERE parcelId = :parcelId"
            );

            $stmt->execute([
                'location' => $location,
                'parcelId' => $id
            ]);

        } elseif ($action === 'packed') {

            $hasConfirmedDetails = (bool)$parcel['detailsConfirmed'];
            $hasLocation = trim(
                (string)($parcel['storageLocationCode'] ?? '')
            ) !== '';

            if ($status !== 'DETAILS_CONFIRMED') {
                throw new RuntimeException(
                    'Parcel details must be confirmed before packing.'
                );
            }

            if (!$hasConfirmedDetails) {
                throw new RuntimeException(
                    'Confirm parcel details before marking it packed.'
                );
            }

            if (!$hasLocation) {
                throw new RuntimeException(
                    'Assign a storage location before marking it packed.'
                );
            }

            $stmt = $pdo->prepare(
                "UPDATE Parcel
                 SET status = 'PACKED'
                 WHERE parcelId = :parcelId"
            );

            $stmt->execute(['parcelId' => $id]);

        } elseif ($action === 'release') {

            if ($status !== 'PACKED') {
                throw new RuntimeException(
                    'Only packed parcels can be released to the sender.'
                );
            }

            $stmt = $pdo->prepare(
                "UPDATE Parcel
                 SET status = 'RELEASED_TO_SENDER'
                 WHERE parcelId = :parcelId"
            );

            $stmt->execute(['parcelId' => $id]);

        } else {
            throw new RuntimeException('Invalid processing action.');
        }

        $pdo->commit();

        header(
            'Location: process.php?id=' .
            urlencode($id) .
            '&saved=1'
        );
        exit;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = $e->getMessage();
    }
}

// Load the latest database values for displaying the page.
if ($id !== '' && $parcel === null) {
    try {
        $parcel = wh_load_process_parcel($pdo, $id);
    } catch (PDOException $e) {
        $error = 'Unable to load parcel information from the database.';
    }
}

wh_header('Warehouse processing', 'parcels');
?>

<section class="wh-panel">

    <?php if (!$parcel): ?>

        <p class="wh-empty">
            <?= wh_h($error ?: 'Parcel not found.') ?>
        </p>

        <div class="wh-actions">
            <a class="wh-btn secondary" href="parcels.php">
                Back to register
            </a>
        </div>

    <?php else: ?>

        <?php
        $status = wh_normalize_status($parcel['status']);

        $canProcess = in_array($status, [
            'RECEIVED_AT_WAREHOUSE',
            'DETAILS_CONFIRMED'
        ], true);

        $detailsConfirmed = (bool)$parcel['detailsConfirmed'];

        $hasLocation = trim(
            (string)($parcel['storageLocationCode'] ?? '')
        ) !== '';
        ?>

        <div class="wh-panel-head">
            <div>
                <h2>
                    <?= wh_h($parcel['parcelId']) ?>
                    ·
                    <?= wh_h($parcel['description']) ?>
                </h2>

                <p class="wh-muted">
                    Follow the warehouse handling steps defined for the parcel.
                </p>
            </div>

            <span class="wh-badge <?= wh_status_class($status) ?>">
                <?= wh_h(wh_status_label($status)) ?>
            </span>
        </div>

        <?php if ($success): ?>
            <div class="wh-demo-notice">
                Parcel processing action successfully saved to the database.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="wh-error">
                <?= wh_h($error) ?>
            </p>
        <?php endif; ?>

        <div class="wh-form">

            <!-- Step 1: Confirm parcel details -->
            <form method="post" class="wh-panel">

                <h2>1. Confirm parcel details</h2>

                <p class="wh-muted">
                    Record the measured weight and confirm the supplied details.
                </p>

                <div class="wh-field" style="margin-top:12px">
                    <label for="confirmedWeight">
                        Confirmed weight (kg)
                    </label>

                    <input
                        type="number"
                        id="confirmedWeight"
                        name="confirmedWeight"
                        min="0.01"
                        step="0.01"
                        required
                        value="<?= wh_h($parcel['confirmedWeight'] ?? '') ?>"
                        <?= !$canProcess ? 'disabled' : '' ?>
                    >
                </div>

                <div class="wh-form-actions">
                    <button
                        class="wh-btn"
                        type="submit"
                        name="action"
                        value="confirm"
                        <?= !$canProcess ? 'disabled' : '' ?>
                    >
                        Confirm details
                    </button>
                </div>

                <?php if ($detailsConfirmed): ?>
                    <p class="wh-muted">
                        Parcel details have been confirmed.
                    </p>
                <?php endif; ?>

            </form>

            <!-- Step 2: Assign storage location -->
            <form method="post" class="wh-panel">

                <h2>2. Assign storage location</h2>

                <p class="wh-muted">
                    Enter the warehouse shelf or rack location code.
                </p>

                <div class="wh-field" style="margin-top:12px">
                    <label for="storageLocationCode">
                        Storage location code
                    </label>

                    <input
                        type="text"
                        id="storageLocationCode"
                        name="storageLocationCode"
                        maxlength="50"
                        value="<?= wh_h($parcel['storageLocationCode'] ?? '') ?>"
                        placeholder="A-01-03"
                        required
                        <?= !$canProcess ? 'disabled' : '' ?>
                    >
                </div>

                <div class="wh-form-actions">
                    <button
                        class="wh-btn"
                        type="submit"
                        name="action"
                        value="location"
                        <?= !$canProcess ? 'disabled' : '' ?>
                    >
                        Assign location
                    </button>
                </div>

                <?php if ($hasLocation): ?>
                    <p class="wh-muted">
                        Current location:
                        <?= wh_h($parcel['storageLocationCode']) ?>
                    </p>
                <?php endif; ?>

            </form>

            <!-- Step 3: Mark parcel packed -->
            <form method="post" class="wh-panel">

                <h2>3. Mark parcel packed</h2>

                <p class="wh-muted">
                    Available after parcel details are confirmed and a storage
                    location is assigned.
                </p>

                <div class="wh-form-actions">
                    <button
                        class="wh-btn"
                        type="submit"
                        name="action"
                        value="packed"
                        <?= (
                            $status !== 'DETAILS_CONFIRMED' ||
                            !$detailsConfirmed ||
                            !$hasLocation
                        ) ? 'disabled' : '' ?>
                    >
                        Mark as packed
                    </button>
                </div>

            </form>

            <!-- Step 4: Release to sender -->
            <form method="post" class="wh-panel">

                <h2>4. Release to sender</h2>

                <p class="wh-muted">
                    Release a packed parcel when the sender collects it.
                </p>

                <div class="wh-form-actions">
                    <button
                        class="wh-btn"
                        type="submit"
                        name="action"
                        value="release"
                        <?= $status !== 'PACKED' ? 'disabled' : '' ?>
                    >
                        Release to sender
                    </button>
                </div>

            </form>

        </div>

        <div class="wh-actions">
            <a
                class="wh-btn secondary"
                href="view.php?id=<?= urlencode($id) ?>"
            >
                View parcel details
            </a>

            <a class="wh-btn secondary" href="parcels.php">
                Back to register
            </a>
        </div>

    <?php endif; ?>

</section>

<?php wh_footer(); ?>
