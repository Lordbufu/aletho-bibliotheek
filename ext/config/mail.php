<?php

return [
    'host'          => getenv('MAIL_HOST'),
    'port'          => getenv('MAIL_PORT'),
    'username'      => getenv('MAIL_USER'),
    'password'      => getenv('MAIL_PASS'),
    'from_email'    => 'noreply.bibliotheek@aletho.nl',
    'from_name'     => 'Aletho Bibliotheek',
];