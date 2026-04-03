<?php

namespace App\Libs\Context;

/** User context object, storing all user specific properties */
final class UserContext {
    public int      $id;
    public string   $username;
    public string   $email;
    public string   $passwordHash;
    public string   $role;          // 'user', 'office_admin', 'global_admin'
    public ?int     $officeId;      // added a null option
    public bool     $active;
}