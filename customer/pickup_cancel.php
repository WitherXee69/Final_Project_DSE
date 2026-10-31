<?php
// CANCEL: POST only (soft delete - sets status = Cancelled)
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/pickup_functions.php';
$uid = cust_id();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: pickups.php'); exit; }
cust_check_csrf();
$id = (int)($_POST['id'] ?? 0);
if (pickup_cancel($pdo, $id, $uid)) cust_flash('success', "Pickup #$id has been cancelled.");
else cust_flash('error', 'This pickup can no longer be cancelled.');
header('Location: pickups.php');
