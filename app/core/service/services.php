<?php

use App\Core\Database\Database;
use App\Core\Services\{LoggerService, MailTemplateService, MailerService};

return [
    'logger'               => fn() => new LoggerService(),
    'mailTemplateService'  => fn() => new MailTemplateService(),
    'mailerService'        => fn() => new MailerService(/* config? */),
];