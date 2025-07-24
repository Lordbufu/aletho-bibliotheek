<?php
namespace App\Core;

use App\Core\Database\{Database, Installer};
use App\Core\Services\MailTemplateService;

class App {
    private static App $instance;
    private ServiceContainer $services;
    private array $dbConfig;

    protected string   $schemaDir;
    protected string   $lockFile;

    public function __construct(array  $dbConfig, string $schemaDir, string $lockFile) {
        self::$instance     = $this;

        $this->dbConfig     = $dbConfig;
        $this->schemaDir    = $schemaDir;
        $this->lockFile     = $lockFile;

        $this->services  = new ServiceContainer();
        $this->registerServices();
    }

    public static function get(string $service): mixed {
        return self::$instance->services->get($service);
    }

    private function registerServices(): void {
        $bindings = require __DIR__ . '/service/services.php';

        foreach ($bindings as $name => $factory) {
            // database krijgt de config-array mee
            if ($name === 'database') {
                $this->services->register($name, fn() => $factory($this->dbConfig));
            } else {
                // request, mailTemplateService, etc.
                $this->services->register($name, $factory);
            }
        }
    }

    /**
     * Entry point for your application.
     */
    public function run(): void {
        $this->installSchema();
        // ← here you’d dispatch routes, controllers, etc.
        echo "👍 App ready to go.\n";

        echo $this->mailTemplateTest();
    }

    /**
     * One–time schema installation via Installer.
     */
    protected function installSchema(): void {
        $pdo       = App::get('database')->pdo();
        $installer = new Installer($pdo, $this->schemaDir, $this->lockFile);

        $missing = $installer->getMissingTables();

        if (is_array($missing) && !empty($missing)) {
            $installer->installTables($missing);
            echo "🚀 Database schema installed successfully.\n";
        } else {
            echo "✅ All tables are already present.\n";
        }
    }

    protected function mailTemplateTest() {
        $segments = [
            'header_string' => 'Hallo :user_name,',
            'body_text_1' => 'Je boek: <strong>:item_title</strong> verloopt binnenkort.',
            'body_text_2' => 'Je hebt tot: <strong>:due_date</strong> om het terug te brengen.',
            'action_intro' => 'Je kunt het boek verlengen als je wilt.',
            'footer_outro' => 'Met vriendelijke groet, <br> Het Bibliotheek team',
            'footer_notes' => 'Let op: Wij zijn gesloten tijdens de zomervakantie.'
        ];

        $tokens = [
            ':user_name'    => 'Bu',
            ':item_title'   => 'De Avonturen van Sherlock Holmes',
            ':due_date'     => '15 juli 2025',
            ':action_link'  => 'https://example.com/verlengen'
        ];

        $html = App::get('mailTemplateService')->renderTemplate($segments, $tokens);

        return $html; // Just renders to screen for debugging
    }
}
