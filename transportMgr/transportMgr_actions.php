<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?portal=staff');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Get the logged-in Transport Manager ID.
$transportMgrId = $_SESSION['user_id'];

// Load the Transport Manager action functions.
require_once __DIR__ . '/actions/register_driver.php';
require_once __DIR__ . '/actions/update_driver.php';
require_once __DIR__ . '/actions/delete_driver.php';
require_once __DIR__ . '/actions/view_driver_status.php';

function redirect_dashboard($message, $type = 'success')
{
    $_SESSION['transportMgr_message'] = $message;
    $_SESSION['transportMgr_message_type'] = $type;

    header('Location: transport_manager_dashboard.php');
    exit;
}

function verify_csrf()
{
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['transportMgr_csrf_token']) ||
        !hash_equals(
            $_SESSION['transportMgr_csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        http_response_code(403);
        exit('Invalid security token.');
    }
}

function get_posted_id($field)
{
    $id = trim($_POST[$field] ?? '');

    if ($id === '' || strlen($id) > 20) {
        return false;
    }

    return $id;
}

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transport_manager_dashboard.php');
    exit;
}

verify_csrf();

$action = $_POST['action'] ?? '';

try {

    // Register a new driver.
    if ($action === 'register_driver') {

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $licenseNumber = $_POST['licenseNumber'] ?? '';
        $contactNumber = $_POST['contactNumber'] ?? '';
        $status = $_POST['status'] ?? '';

        $message = registerDriver(
            $pdo,
            $username,
            $password,
            $licenseNumber,
            $contactNumber,
            $status,
            $transportMgrId
        );

        redirect_dashboard($message);
    }

    // Update an existing driver.
    if ($action === 'update_driver') {

        $driverId = get_posted_id('driverId');

        if ($driverId === false) {
            redirect_dashboard('Invalid driver ID.', 'error');
        }

        $licenseNumber = $_POST['licenseNumber'] ?? '';
        $contactNumber = $_POST['contactNumber'] ?? '';
        $status = $_POST['status'] ?? '';

        $message = updateDriver(
            $pdo,
            $driverId,
            $licenseNumber,
            $contactNumber,
            $status,
            $transportMgrId
        );

        redirect_dashboard($message);
    }

    // Delete an existing driver.
    if ($action === 'delete_driver') {

        $driverId = get_posted_id('driverId');

        if ($driverId === false) {
            redirect_dashboard('Invalid driver ID.', 'error');
        }

        $message = deleteDriver(
            $pdo,
            $driverId,
            $transportMgrId
        );

        redirect_dashboard($message);
    }

    redirect_dashboard('Unknown Transport Manager action.', 'error');

} catch (InvalidArgumentException | RuntimeException $e) {

    redirect_dashboard($e->getMessage(), 'error');

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Transport Manager action error: ' . $e->getMessage());

    redirect_dashboard(
        'The requested action could not be completed.',
        'error'
    );
}

?>