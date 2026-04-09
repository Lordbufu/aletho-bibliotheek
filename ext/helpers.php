<?php
/** Function to set viewpaths more cleanly. */
if (!function_exists('viewPath')) {
    function viewPath($string) {
        return  __DIR__ . "/views/" . $string;
    }
}

/** Dump and Die data on screen for debug reasons. */
if (!function_exists('dd')) {
    function dd($data) {
        echo '<pre style="background:#222;color:#0f0;padding:0.625rem;font-size:0.875rem;line-height:1.4;">';
        if (is_array($data) || is_object($data)) {
            print_r($data);
        } else {
            var_dump($data);
        }
        echo '</pre>';
        die;
    }
}

/** Calculate due date for setting the correct end_date (might get removed later) */
if(!function_exists('calculateDueDate')) {
    function calculateDueDate(?string $startDate, int $days): string {
        $dt = $startDate ? new \DateTimeImmutable($startDate) : new \DateTimeImmutable('now');
        return $dt->add(new \DateInterval("P{$days}D"))->format('Y-m-d');
    }
}

/** Set custom SESSION flash data, supports arrays of types and message.
 *      @param string $bucket           -> One of: global, inline, inlinePop, form, single, multi, js
 *      @param string|array $type       -> e.g. success, failure, warning, info
 *      @param string|array $message    -> Message string or array (for form data)
 */
if (!function_exists('setFlash')) {
    function setFlash(string $bucket, $type, $message): void {
        $buckets = [
            'global'    => '_flashGlobal',
            'inline'    => '_flashInline',
            'inlinePop' => '_flashInlinePop',
            'form'      => '_flashForm',
            'single'    => '_flashSingle',
            'multi'     => '_flashMulti',
            'js'        => '_flashJs'
        ];

        if (!isset($buckets[$bucket])) {
            throw new InvalidArgumentException("Unknown flash bucket: {$bucket}");
        }

        $_SESSION[$buckets[$bucket]] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

/** Check if user is a guest */
if(!function_exists('checkGuestRole')) {
    function checkGuestRole() {
        return ($_SESSION['user']['role'] ?? null) === 'Guest';
    }
}

/** Check if user is a global admin (currently used to seperate global and office admins) */
if (!function_exists('isGlobalAdmin')) {
    function isGlobalAdmin(): bool {
        return ($_SESSION['user']['role'] ?? null) === 'global_admin';
    }
}

/** Check if user is a office admin (currently unused) */
if (!function_exists('isOfficeAdmin')) {
    function isOfficeAdmin(): bool {
        return ($_SESSION['user']['role'] ?? null) === 'office_admin';
    }
}

/** Check if user can edit (is either a global or office admin) */
if (!function_exists('canEdit')) {
    function canEdit(): bool {
        return in_array($_SESSION['user']['role'], ['office_admin', 'global_admin']);
    }
}