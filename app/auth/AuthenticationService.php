<?php

namespace App\Auth;

use App\Auth\PasswordValidator;

class AuthenticationService {
    protected $db;
    protected $logger;
    protected $session;
    protected $permissionsMap;

    public function __construct(PDO $db, $logger, $session) {
        $this->db      = $db;
        $this->logger  = $logger;
        $this->session = $session;
        $this->permissionsMap = include BASE_PATH . '/ext/config/permissions.php';

        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(string $email, string $password): bool {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'role' => $user['role'],
                'office_id' => $user['office_id']
            ];

            return true;
        }

        return false;
    }

    public function logout(): void {
        session_destroy();
    }

    public function can(string $permission): bool {
        $role = $_SESSION['user']['role'] ?? 'guest';

        return in_array($permission, $this->permissionsMap[$role] ?? []);
    }

    public function currentUser() {
        return $_SESSION['user'] ?? ['role' => 'guest'];
    }

    public function resetOwnPassword(int $userId, string $currentPassword, string $newPassword): bool {
        if (!$this->can('manage_account')) {
            throw new \Exception('UNAUTHORIZED');
        }

        // Validate password strength
        if (!PasswordValidator::isValid($newPassword)) {
            throw new \Exception('WEAK_PASSWORD');
        }

        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $storedHash = $stmt->fetchColumn();

        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            throw new \Exception('INVALID_CREDENTIALS');
        }

        if ($currentPassword === $newPassword) {
            throw new \Exception('PASSWORD_UNCHANGED');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $success = $update->execute([$hash, $userId]);

        if ($success) {
            $this->logger->info("User {$userId} changed their password");
            session_regenerate_id(true);
        }

        return $success;
    }

    public function resetUserPassword(int $targetUserId, string $newPassword): bool {
        if (!$this->can('manage_accounts')) {
            throw new \Exception('UNAUTHORIZED');
        }

        if (!PasswordValidator::isValid($newPassword)) {
            throw new \Exception('WEAK_PASSWORD');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $success = $update->execute([$hash, $targetUserId]);

        if ($success) {
            $adminId = $_SESSION['user']['id'] ?? 'unknown';
            $this->logger->info("Admin {$adminId} reset password for user {$targetUserId}");
            // Optional: notify affected user
        }

        return $success;
    }
}