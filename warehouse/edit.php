<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/warehouse_methods.php';

$id = trim($_GET['id'] ?? '');
$error = '';
$parcel = null;

$categories     = WH_CATEGORIES;
$serviceTypes   = WH_SERVICE_TYPES;
$intakeMethods  = ['PICKUP', 'WALK_IN'];

if ($id !== '') {
    try {
        $parcel = viewParcelDetails($pdo, $id);
    } catch (PDOException $e) {
        error_log('Warehouse edit retrieval error: ' . $e->getMessage());
        $error = 'Unable to retrieve parcel information.';
    }
}

if (!$parcel) {
    wh_header('Edit parcel', 'parcels');
    ?>

    <section class="wh-panel">
        <p class="wh-empty">
            <?= wh_h($error ?: 'Parcel not found.') ?>
            <a class="text-link" href="parcels.php">
                Return to parcel register
            </a>.
        </p>
    </section>

    <?php
    wh_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        updateParcelDetails($pdo, $id, $_POST);
        header('Location: view.php?id=' . urlencode($id) . '&updated=1');
        exit;
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    // Preserve submitted values when validation fails.
    $parcel['description']    = trim($_POST['description'] ?? '');
    $parcel['parcelCategory'] = $_POST['parcelCategory'] ?? '';
    $parcel['isFragile']      = isset($_POST['isFragile']) ? 1 : 0;
    $parcel['serviceType']    = $_POST['serviceType'] ?? '';
    $parcel['intakeMethod']   = $_POST['intakeMethod'] ?? '';
    $parcel['declaredWeight'] = trim($_POST['declaredWeight'] ?? '');
    $parcel['dimensions']     = trim($_POST['dimensions'] ?? '');
}

wh_header('Edit parcel details', 'parcels');

?>

<section class="wh-panel">

    <div class="wh-panel-head">
        <div>
            <h2>Update <?= wh_h($parcel['parcelId']) ?></h2>

            <p class="wh-muted">
                Parcel ID is the record identifier and cannot be changed.
            </p>
        </div>
    </div>

    <?php if ($error): ?>
        <p class="wh-error"><?= wh_h($error) ?></p>
    <?php endif; ?>

    <form class="wh-form" method="post">

        <div class="wh-field full">
            <label>Description *</label>

            <input
                type="text"
                name="description"
                maxlength="255"
                required
                value="<?= wh_h($parcel['description']) ?>"
            >
        </div>

        <div class="wh-field">
            <label>Category</label>

            <select name="parcelCategory" required>

                <?php foreach ($categories as $value): ?>

                    <option
                        value="<?= wh_h($value) ?>"
                        <?= $parcel['parcelCategory'] === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= wh_h(ucwords(strtolower($value))) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="wh-field">
            <label>Service type</label>

            <select name="serviceType" required>

                <?php foreach ($serviceTypes as $value): ?>

                    <option
                        value="<?= wh_h($value) ?>"
                        <?= $parcel['serviceType'] === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= wh_h(ucwords(strtolower($value))) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="wh-field">
            <label>Intake method</label>

            <select name="intakeMethod" required>

                <?php foreach ($intakeMethods as $value): ?>

                    <option
                        value="<?= wh_h($value) ?>"
                        <?= $parcel['intakeMethod'] === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= $value === 'WALK_IN'
                            ? 'Walk-in'
                            : 'Pickup' ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="wh-field">
            <label>Declared weight (kg)</label>

            <input
                type="number"
                min="0"
                step="0.01"
                name="declaredWeight"
                value="<?= wh_h($parcel['declaredWeight']) ?>"
            >
        </div>

        <div class="wh-field">
            <label>Dimensions</label>

            <input
                type="text"
                maxlength="50"
                name="dimensions"
                placeholder="Example: 40x30x25"
                value="<?= wh_h($parcel['dimensions']) ?>"
            >
        </div>

        <div class="wh-field">
            <label class="wh-check">

                <input
                    type="checkbox"
                    name="isFragile"
                    <?= $parcel['isFragile'] ? 'checked' : '' ?>
                >

                Fragile parcel

            </label>
        </div>

        <div class="wh-field full">
            <small>
                Confirmed weight, storage location and parcel status
                are managed through warehouse processing.
            </small>
        </div>

        <div class="wh-form-actions">

            <button class="wh-btn" type="submit">
                Save changes
            </button>

            <a
                class="wh-btn secondary"
                href="view.php?id=<?= urlencode($id) ?>"
            >
                Cancel
            </a>

        </div>

    </form>

</section>

<?php wh_footer(); ?>
