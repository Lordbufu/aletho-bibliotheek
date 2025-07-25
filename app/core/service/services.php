<?php

use App\Core\Database\Database;
use App\Core\Services\{LoggerService, MailTemplateService, MailerService};

return [
    'database'             => fn(array $config) => new Database($config),
    'logger'               => fn() => new LoggerService(),
    'request'              => fn() => new \App\Request(array_merge($_GET, $_POST)),
    'mailTemplateService'  => fn() => new MailTemplateService(),
    'mailerService'        => fn() => new MailerService(/* config? */),
];