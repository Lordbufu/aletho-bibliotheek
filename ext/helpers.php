<?php

/* Function to set viewpaths more cleanly. */
if (!function_exists('viewPath')) {
    function viewPath($string) {
        return  __DIR__ . "/views/" . $string;
    }
}

/* Needed a logger to log before the logging service is active. */
if (!function_exists('handleBootFailure')) {
    function handleBootFailure(array $errors): void {
        error_log("App failed to boot with " . count($errors) . " errors: " . implode('; ', $errors));

        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, "Boot failed:\n" . implode("\n", $errors) . "\n");
        } else {
            http_response_code(500);
            include BASE_PATH . '/ext/views/errors/500.php';
        }
    }
}

/* Dump and Die data on screen for debug reasons. */
if (!function_exists('dd')) {
    function dd($data) {
        echo '<pre style="background:#222;color:#0f0;padding:10px;font-size:14px;line-height:1.4;">';
        if (is_array($data) || is_object($data)) {
            print_r($data);
        } else {
            var_dump($data);
        }
        echo '</pre>';
        die;
    }
}

/** Set custom SESSION flash data, supports arrays of types and message.
 *      @param string $bucket           -> One of: global, inline, form
 *      @param string|array $type       -> e.g. success, failure, warning
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
            'type'    => $type,
            'message' => $message,
        ];
    }
}