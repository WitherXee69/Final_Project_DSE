<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/warehouse_methods.php';

$id = strtoupper(trim($_GET['id'] ?? ''));
$error = '';
$success = isset($_GET['saved']);
$parcel = null;

if ($id === '') {
    $error = 'A parcel ID is required.';

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'confirm':
                confirmParcelDetails($pdo, $id, $_POST['confirmedWeight'] ?? '');
                break;

            case 'location':
                assignStorageLocation($pdo, $id, $_POST['storageLocationCode'] ?? '');
                break;

            case 'packed':
                markParcelPacked($pdo, $id);
                break;

            case 'release':
                releaseParcelToSender($pdo, $id);
                break;

            default:
                throw new RuntimeException('Invalid processing action.');
        }

        header('Location: process.php?id=' . urlencode($id) . '&saved=1');
        exit;

    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    } catch (PDOException $e) {
        error_log('Warehouse processing error: ' . $e->getMessage());
        $error = 'Unable to save the processing action. Please try again.';
    }
}

if ($id !== '' && $parcel === null) {
    try {
        $parcel = viewParcelDetails($pdo, $id);
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

            <form method="post" class="wh-panel">

                <h2>1. Confirm parcel details</h2>

                <p class="wh-muted">
                    Record the measured weight and confirm the supplied details.
                </p>
                <div class="wh-field wh-field-spaced">
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

            <form method="post" class="wh-panel">

                <h2>2. Assign storage location</h2>

                <p class="wh-muted">
                    Enter the warehouse shelf or rack location code.
                </p>
                <div class="wh-field wh-field-spaced">
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
