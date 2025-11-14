<?php

return [
    'host'          => $_ENV['MAIL_HOST'] ?? $_SERVER['MAIL_HOST'] ?? 'localhost',
    'port'          => $_ENV['MAIL_PORT'] ?? $_SERVER['MAIL_PORT'] ?? '587',
    'username'      => $_ENV['MAIL_USER'] ?? $_SERVER['MAIL_USER'] ?? 'user',
    'password'      => $_ENV['MAIL_PASS'] ?? $_SERVER['MAIL_PASS'] ?? 'pass',
    'from_email'    => 'noreply.bibliotheek@aletho.nl',
    'from_name'     => 'Aletho Bibliotheek',
];