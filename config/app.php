<?php

return [
    'env'   => getenv('APP_ENV'),
    'debug' => filer_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
    'url'   => 'https://localhost',
];