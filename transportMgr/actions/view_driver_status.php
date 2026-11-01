<?php

function viewDriverStatus($pdo, $transportMgrId)
{
    $stmt = $pdo->prepare("
        SELECT
            d.userId,
            u.username,
            d.licenseNumber,
            d.contactNumber,
            d.status,
            tm.region,
            tm.officeNumber
        FROM Driver d
        INNER JOIN User u ON d.userId = u.userId
        INNER JOIN TransportManager tm ON d.transportMgrId = tm.userId
        WHERE d.transportMgrId = ?
        ORDER BY u.username ASC
    ");

    $stmt->execute([$transportMgrId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}