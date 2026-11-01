
<?php
/**
 * HELMS - Public parcel tracking lookup
 * Returns parcel status and receiver-visible tracking events as JSON.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/database.php';

function respond($success, $message = '', $data = null)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

$trackingId = strtoupper(trim($_POST['id'] ?? ''));

if ($trackingId === '') {
    respond(false, 'Please enter a tracking number.');
}

if (!preg_match('/^HEN-[0-9]{4}-[0-9]{6}$/', $trackingId)) {
    respond(false, 'Please enter a valid tracking number, such as HEN-2026-000123.');
}

try {
    // Get parcel status.
    $parcelQuery = $pdo->prepare("
        SELECT parcelId, status
        FROM Parcel
        WHERE parcelId = :parcelId
        LIMIT 1
    ");

    $parcelQuery->execute([
        ':parcelId' => $trackingId
    ]);

    $parcel = $parcelQuery->fetch();

    if (!$parcel) {
        respond(false, 'We could not find a parcel with that tracking number.');
    }

    // Only events explicitly marked as visible to the receiver are shown.
    $eventQuery = $pdo->prepare("
        SELECT
            eventType,
            occurredAt,
            locationDescription
        FROM TrackingEvent
        WHERE parcelId = :parcelId
          AND isVisibleToReceiver = 1
        ORDER BY occurredAt ASC
    ");

    $eventQuery->execute([
        ':parcelId' => $trackingId
    ]);

    $events = $eventQuery->fetchAll();

    respond(true, 'Tracking information found.', [
        'parcelId' => $parcel['parcelId'],
        'status' => $parcel['status'],
        'events' => $events
    ]);

} catch (PDOException $e) {
    // Log technical details on the server, not in the public response.
    error_log('HELMS tracking lookup failed: ' . $e->getMessage());

    respond(false, 'Tracking is temporarily unavailable. Please try again later.');
}
