<?php

use App\Core\Database\Database;
use App\Core\Services\MailTemplateService;
use App\Core\Services\MailerService;

return [

    // Core services
    'database' => fn(array $config) => new Database($config),

    // request: haalt $_GET + $_POST één keer en filtert via Request
    'request' => function() {
        return new Request(array_merge($_GET, $_POST));
    },

    // Mail system
    'mailTemplateService' => fn() => new MailTemplateService(),
    'mailerService'       => fn() => new MailerService(/* config */),

    // Logging, Auth, etc. can follow here
];