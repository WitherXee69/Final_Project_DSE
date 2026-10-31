<?php
require_once __DIR__ . '/_layout.php';

$id = $_GET['id'] ?? '';
$p = wh_find($id);
$error = '';

if (!$p) {
    wh_header('Warehouse processing', 'parcels');
    ?>
    <section class="wh-panel">
        <p class="wh-empty">Parcel not found.</p>
    </section>
    <?php
    wh_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'confirm') {
        $weight = trim($_POST['confirmedWeight'] ?? '');
        if ($weight === '' || !is_numeric($weight) || (float)$weight <= 0) {
            $error = 'Enter a valid confirmed weight.';
        } else {
            $p['confirmedWeight'] = $weight;
            $p['detailsConfirmed'] = true;
            if ($p['status'] === 'Pending Confirmation') {
                $p['status'] = 'Received at Warehouse';
            }
        }
    } elseif ($action === 'location') {
        $loc = strtoupper(trim($_POST['storageLocationCode'] ?? ''));
        if ($loc === '') {
            $error = 'Enter a storage location code.';
        } else {
            $p['storageLocationCode'] = $loc;
            $p['status'] = 'Stored';
        }
    } elseif ($action === 'packed') {
        if (!$p['detailsConfirmed']) {
            $error = 'Confirm parcel details before marking it packed.';
        } elseif ($p['storageLocationCode'] === '') {
            $error = 'Assign a storage location before marking it packed.';
        } else {
            $p['status'] = 'Packed';
        }
    } elseif ($action === 'release') {
        if ($p['status'] !== 'Packed') {
            $error = 'Only packed parcels can be released to the sender.';
        } else {
            $p['status'] = 'Released to Sender';
        }
    }

    if (!$error) {
        wh_save($p);
        header('Location: process.php?id=' . urlencode($id) . '&saved=1');
        exit;
    }
}

wh_header('Warehouse processing', 'parcels');
?>
<section class="wh-panel">
    <div class="wh-panel-head">
        <div>
            <h2><?= wh_h($p['parcelId']) ?> · <?= wh_h($p['description']) ?></h2>
            <p class="wh-muted">Follow the warehouse handling steps defined for the parcel.</p>
        </div>
        <span class="wh-badge <?= wh_status_class($p['status']) ?>"><?= wh_h($p['status']) ?></span>
    </div>
    <?php if (isset($_GET['saved'])): ?>
        <div class="wh-demo-notice">The action was saved to the temporary demo session.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color:#b91c1c;margin-bottom:12px;font-size:.85rem"><?= wh_h($error) ?></p>
    <?php endif; ?>
    <div class="wh-form">
        <form method="post" class="wh-panel">
            <h2>1. Confirm parcel details</h2>
            <p class="wh-muted">Record the measured weight and confirm the supplied details.</p>
            <div class="wh-field" style="margin-top:12px">
                <label>Confirmed weight (kg)</label>
                <input type="number" name="confirmedWeight" min=".01" step=".01" value="<?= wh_h($p['confirmedWeight']) ?>">
            </div>
            <div class="wh-form-actions">
                <button class="wh-btn" name="action" value="confirm">Confirm details</button>
            </div>
        </form>
        <form method="post" class="wh-panel">
            <h2>2. Assign storage location</h2>
            <p class="wh-muted">Enter the warehouse shelf / rack location code.</p>
            <div class="wh-field" style="margin-top:12px">
                <label>Storage location code</label>
                <input name="storageLocationCode" value="<?= wh_h($p['storageLocationCode']) ?>" placeholder="A-01-03">
            </div>
            <div class="wh-form-actions">
                <button class="wh-btn" name="action" value="location">Assign location</button>
            </div>
        </form>
        <form method="post" class="wh-panel">
            <h2>3. Mark parcel packed</h2>
            <p class="wh-muted">Available after details are confirmed and a location is assigned.</p>
            <div class="wh-form-actions">
                <button class="wh-btn" name="action" value="packed">Mark as packed</button>
            </div>
        </form>
        <form method="post" class="wh-panel">
            <h2>4. Release to sender</h2>
            <p class="wh-muted">Release a packed parcel when the sender collects it.</p>
            <div class="wh-form-actions">
                <button class="wh-btn" name="action" value="release">Release to sender</button>
            </div>
        </form>
    </div>
    <div class="wh-actions">
        <a class="wh-btn secondary" href="view.php?id=<?= urlencode($id) ?>">View parcel details</a>
        <a class="wh-btn secondary" href="parcels.php">Back to register</a>
    </div>
</section>
<?php wh_footer(); ?>
