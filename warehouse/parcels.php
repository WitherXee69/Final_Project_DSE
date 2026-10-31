<?php
require_once __DIR__ . '/_layout.php';

$q = trim($_GET['q'] ?? '');
$parcels = wh_parcels();
if ($q !== '') {
    $parcels = array_filter($parcels, function ($p) use ($q) {
        foreach (['parcelId', 'description', 'status', 'storageLocationCode'] as $k) {
            if (stripos((string)$p[$k], $q) !== false) {
                return true;
            }
        }

        return false;
    });
}
wh_header('Parcel register', 'parcels');
?>
<section class="wh-panel">
    <div class="wh-panel-head">
        <div>
            <h2>Warehouse parcels</h2>
            <p class="wh-muted">Search by parcel ID, description, status or storage location.</p>
        </div>
        <a class="wh-btn" href="receive.php">＋ Receive parcel</a>
    </div>
    <form class="wh-search" method="get">
        <input type="search" name="q" value="<?= wh_h($q) ?>" placeholder="Search parcels...">
        <button class="wh-btn secondary">Search</button>
        <a class="wh-btn secondary" href="parcels.php">Clear</a>
    </form>
    <div class="wh-table-wrap">
        <table class="wh-table">
            <thead>
                <tr>
                    <th>Parcel ID</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Weight</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$parcels): ?>
                    <tr><td colspan="7" class="wh-empty">No parcels match your search.</td></tr>
                <?php endif; ?>
                <?php foreach ($parcels as $p): ?>
                    <tr>
                        <td><strong><?= wh_h($p['parcelId']) ?></strong></td>
                        <td><?= wh_h($p['description']) ?></td>
                        <td><?= wh_h($p['parcelCategory']) ?></td>
                        <td><?= wh_h($p['confirmedWeight'] ?: $p['declaredWeight']) ?> kg</td>
                        <td><?= wh_h($p['storageLocationCode'] ?: '—') ?></td>
                        <td>
                            <span class="wh-badge <?= wh_status_class($p['status']) ?>">
                                <?= wh_h($p['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="wh-actions">
                                <a class="text-link" href="view.php?id=<?= urlencode($p['parcelId']) ?>">View</a>
                                <a class="text-link" href="edit.php?id=<?= urlencode($p['parcelId']) ?>">Edit</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php wh_footer(); ?>
