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

/* Set SESSION `_flash` data */
if (!function_exists('setFlash')) {
    function setFlash($data) {
        foreach ($data as $key => $value) {
            $_SESSION['_flash'][$key] = $value;
        }
        return true;
    }

}

