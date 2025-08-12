<?php

/*  App expected user data:
        Name            - for login and welcome messages
        Password        - for user validation
        Email           - for admin email notifications
        canEdit         - for Permission authentication
        canEditOffice   - for Permission authentication
 */
namespace App\Core\Auth;

use App\Core\Database\Database;

class AuthService {
    private const SESSION_KEY = 'user';

    // assume App::get('database') returns this
    public function __construct(private Database $db) {}

    /**
     * Evaluate database data for editing permissions.
     */
    protected function canEdit($userData): bool {
        if($userData['is_global_admin'] || $userData['is_office_admin']) {
            return TRUE;
        }

        return FALSE;
    }

    /** 
     * Is someone logged in? 
     */
    public function check(): bool {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public function user(): ?array {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public function login(string $name, string $password): bool {
        // Temp test data
        // $row1 = ['name' => 'user', 'password' => '#redacted#', 'email' => null, 'is_loaner' => 1, 'is_office_admin' => 0, 'is_global_admin' => 0, 'created_at' => '01-01-1979', 'updated_at' => '08-08-2025', 'active' => 1];
        // $row1 = ['name' => 'office_admin', 'password' => '#redacted#', 'email' => 'bibliotheek@aletho.nl', 'is_loaner' => 0, 'is_office_admin' => 1, 'is_global_admin' => 0, 'office_id' => 1, 'created_at' => '01-01-1979', 'updated_at' => '08-08-2025', 'active' => 1];
        $row1 = ['name' => 'globaleAdmin', 'password' => '#redacted#', 'email' => null, 'is_loaner' => 0, 'is_office_admin' => 0, 'is_global_admin' => 1, 'created_at' => '01-01-1979', 'updated_at' => '08-08-2025', 'active' => 1];

        // 1. fetch the entire DB row by username (uncommneted for testing reasons)
        //$row = $this->db->query('users', 'name', $name)->fetch();
        // if (! $row || ! password_verify($password, $row['password'])) {
        //     return false;
        // }

        if (! $row1 ) {
            return false;
        }

        // 2. build a lightweight session payload
        $_SESSION[self::SESSION_KEY] = [
            'name'    => $row1['name'],
            'email'   => $row1['email'],
            'canEdit' => (bool) $this->canEdit($row1),
        ];

        // debugData($_SESSION);

        // 3. Add office_id if user has a office (needs db query cause linking tables)
        if(isset($row1['office_id'])) {
            $_SESSION[self::SESSION_KEY]['office_id'] = $row1['office_id'];
        }

        // debugData($_SESSION);

        return true;
    }

    /** (likely to move to session manager later on)
     * Destroy the session slice for this user.
     */
    public function logout(): void {
        unset($_SESSION[self::SESSION_KEY]);
    }
}