<?php
/**
 * HELMS System Admin module - methods from the class diagram.
 * Plain functions (no classes); names match FINAL CLASS.drawio.
 */

const SA_ROLE_LABELS = [
    'IndividualCustomer'     => 'Individual Customer',
    'CustomerServiceOfficer' => 'Customer Service Officer',
    'CustomerServiceManager' => 'Customer Service Manager',
    'FinanceOfficer'         => 'Finance Officer',
    'FinanceManager'         => 'Finance Manager',
    'TransportManager'       => 'Transport Manager',
    'Driver'                 => 'Driver',
    'WarehouseOfficer'       => 'Warehouse Officer',
    'WarehouseManager'       => 'Warehouse Manager',
    'ManagingDirector'       => 'Managing Director',
    'SystemAdmin'            => 'System Administrator',
];

/** Throws unless $userId belongs to a System Administrator. */
function sa_require_admin(PDO $pdo, string $userId): void
{
    $stmt = $pdo->prepare(
        "SELECT u.userId FROM User u
         INNER JOIN SystemAdmin sa ON sa.userId = u.userId
         WHERE u.userId = :userId LIMIT 1"
    );
    $stmt->execute(['userId' => $userId]);

    if (!$stmt->fetch()) {
        throw new RuntimeException('Access denied. System Administrator access is required.');
    }
}

// SystemAdmin.viewUser()
// Lists users, optionally filtered by role ('ALL' = no filter) and a search term.
function viewUser(PDO $pdo, string $roleFilter = 'ALL', string $search = ''): array
{
    $sql = "SELECT userId, username, role FROM User WHERE 1 = 1";
    $params = [];

    if ($roleFilter !== 'ALL') {
        $sql .= " AND role = :role";
        $params['role'] = $roleFilter;
    }

    if ($search !== '') {
        $sql .= " AND (userId LIKE :userId OR username LIKE :username OR role LIKE :searchRole)";
        $term = '%' . $search . '%';
        $params['userId'] = $term;
        $params['username'] = $term;
        $params['searchRole'] = $term;
    }

    $sql .= " ORDER BY userId ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// SystemAdmin.deactivateUser()  (D)
// Permanently removes an Individual Customer account that has no parcels.
// Throws RuntimeException with a user-friendly message when not allowed.
function deactivateUser(PDO $pdo, string $currentAdminId, string $targetUserId): void
{
    $targetUserId = trim($targetUserId);

    if ($targetUserId === '') {
        throw new RuntimeException('Invalid user selected.');
    }
    if ($targetUserId === $currentAdminId) {
        throw new RuntimeException('You cannot delete your own account while you are logged in.');
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "SELECT userId, username, role FROM User
             WHERE userId = :userId LIMIT 1 FOR UPDATE"
        );
        $stmt->execute(['userId' => $targetUserId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target) {
            throw new RuntimeException('The selected user could not be found.');
        }
        if ($target['role'] === 'SystemAdmin') {
            throw new RuntimeException(
                'System Administrator accounts are protected and cannot be deleted.'
            );
        }
        if ($target['role'] !== 'IndividualCustomer') {
            $roleName = SA_ROLE_LABELS[$target['role']] ?? $target['role'];
            throw new RuntimeException(
                'This ' . $roleName . ' account cannot be deleted from this page. ' .
                'Only Individual Customer accounts without parcel records can be deleted.'
            );
        }

        $stmt = $pdo->prepare(
            "SELECT c.userId FROM Customer c
             INNER JOIN IndividualCustomer ic ON ic.userId = c.userId
             WHERE c.userId = :userId LIMIT 1 FOR UPDATE"
        );
        $stmt->execute(['userId' => $targetUserId]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException(
                'The customer profile could not be found. No records were deleted.'
            );
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Parcel WHERE customerId = :customerId");
        $stmt->execute(['customerId' => $targetUserId]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException(
                'This customer cannot be deleted because they have existing parcel records.'
            );
        }

        // Child records first, then the User record.
        $steps = [
            ['IndividualCustomer', 'Unable to delete the individual customer profile.'],
            ['Customer',           'Unable to delete the customer profile.'],
            ['User',               'Unable to delete the user account.'],
        ];
        foreach ($steps as [$table, $failMessage]) {
            $stmt = $pdo->prepare("DELETE FROM $table WHERE userId = :userId");
            $stmt->execute(['userId' => $targetUserId]);
            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException($failMessage);
            }
        }

        $pdo->commit();

    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('deactivateUser failed for ' . $targetUserId . ': ' . $e->getMessage());

        $sqlState = (string)($e->errorInfo[0] ?? $e->getCode());
        throw new RuntimeException(
            $sqlState === '23000'
                ? 'This account is still linked to other system records, so it cannot be deleted. Nothing was changed.'
                : 'The account could not be deleted because of a system problem. Nothing was changed. Please try again later.'
        );

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Unexpected deactivateUser error: ' . $e->getMessage());
        throw new RuntimeException('Something went wrong while deleting the account. Nothing was changed.');
    }
}
