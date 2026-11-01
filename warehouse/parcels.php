<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/_layout.php';

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$warehouseId = trim($_GET['warehouse'] ?? '');

$sql = "
    SELECT
        p.parcelId,
        p.description,
        p.parcelCategory,
        p.declaredWeight,
        p.confirmedWeight,
        p.storageLocationCode,
        p.status,
        p.warehouseId,
        w.warehouseName
    FROM Parcel p
    LEFT JOIN Warehouse w
        ON p.warehouseId = w.warehouseId
    WHERE 1 = 1
";

$params = [];

if ($q !== '') {
    $sql .= "
        AND (
            CAST(p.parcelId AS CHAR) LIKE :search
            OR p.description LIKE :search
            OR p.storageLocationCode LIKE :search
        )
    ";

    $params['search'] = "%$q%";
}

if ($status !== '') {
    $sql .= " AND p.status = :status";
    $params['status'] = $status;
}

if ($warehouseId !== '') {
    $sql .= " AND p.warehouseId = :warehouseId";
    $params['warehouseId'] = $warehouseId;
}

$sql .= " ORDER BY p.parcelId DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$parcels = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT warehouseId, warehouseName
    FROM Warehouse
    ORDER BY warehouseName
");
$stmt->execute();
$warehouses = $stmt->fetchAll();

$statuses = [
    'PENDING_PICKUP',
    'RECEIVED_AT_WAREHOUSE',
    'DETAILS_CONFIRMED',
    'PACKED',
    'AWAITING_PAYMENT',
    'READY_FOR_DISPATCH',
    'OUT_FOR_DELIVERY',
    'DELIVERED',
    'RETURNED_TO_WAREHOUSE',
    'RETURN_TO_SENDER',
    'UNCLAIMED',
    'RELEASED_TO_SENDER'
];

wh_header('Parcel register', 'parcels');
?>

<section class="wh-panel">

    <div class="wh-panel-head">
        <div>
            <h2>Warehouse parcels</h2>
            <p class="wh-muted">
                Search by parcel ID, description or storage location.
            </p>
        </div>

        <a class="wh-btn" href="receive.php">
            ＋ Receive parcel
        </a>
    </div>

    <form class="wh-search" method="get">

        <input
            type="search"
            name="q"
            value="<?= wh_h($q) ?>"
            placeholder="Search parcels..."
        >

        <select name="status">
            <option value="">All statuses</option>

            <?php foreach ($statuses as $s): ?>
                <option
                    value="<?= wh_h($s) ?>"
                    <?= $status === $s ? 'selected' : '' ?>
                >
                    <?= wh_h(str_replace('_', ' ', $s)) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <select name="warehouse">
            <option value="">All warehouses</option>

            <?php foreach ($warehouses as $w): ?>
                <option
                    value="<?= wh_h($w['warehouseId']) ?>"
                    <?= $warehouseId == $w['warehouseId'] ? 'selected' : '' ?>
                >
                    <?= wh_h($w['warehouseName']) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <button type="submit" class="wh-btn secondary">
            Search
        </button>

        <a class="wh-btn secondary" href="parcels.php">
            Clear
        </a>

    </form>

    <div class="wh-table-wrap">

        <table class="wh-table">

            <thead>
                <tr>
                    <th>Parcel ID</th>
                    <th>Description</th>
                    <th>Warehouse</th>
                    <th>Category</th>
                    <th>Weight</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (empty($parcels)): ?>

                    <tr>
                        <td colspan="8" class="wh-empty">
                            No parcels match your search.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($parcels as $p): ?>

                        <tr>

                            <td>
                                <strong>
                                    <?= wh_h($p['parcelId']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= wh_h($p['description']) ?>
                            </td>

                            <td>
                                <?= wh_h($p['warehouseName'] ?? 'Not assigned') ?>
                            </td>

                            <td>
                                <?= wh_h($p['parcelCategory']) ?>
                            </td>

                            <td>
                                <?= wh_h(
                                    $p['confirmedWeight'] ?: $p['declaredWeight']
                                ) ?>
                                kg
                            </td>

                            <td>
                                <?= wh_h(
                                    $p['storageLocationCode'] ?: '—'
                                ) ?>
                            </td>

                            <td>
                                <span class="wh-badge <?= wh_status_class($p['status']) ?>">
                                    <?= wh_h(
                                        str_replace('_', ' ', $p['status'])
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <div class="wh-actions">

                                    <a
                                        class="text-link"
                                        href="view.php?id=<?= urlencode($p['parcelId']) ?>"
                                    >
                                        View
                                    </a>

                                    <a
                                        class="text-link"
                                        href="edit.php?id=<?= urlencode($p['parcelId']) ?>"
                                    >
                                        Edit
                                    </a>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>

<?php wh_footer(); ?>