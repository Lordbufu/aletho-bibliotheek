<?php
namespace App\Core;

use App\Core\ServiceContainer;
use App\Core\Database\{Database, Installer};
use App\Core\Services\{MailTemplateService, MailerService, LoggerService};

/**
 * @method static \App\Core\Database\Database database()
 * @method static \App\Core\Services\LoggerService logger()
 * @method static \App\Request request()
 * @method static \App\Core\Services\MailTemplateService mailTemplateService()
 * @method static \App\Core\Services\MailerService mailerService()
 */
class App {
    private static App $instance;
    private ServiceContainer $services;
    private array $dbConfig;
    protected string   $schemaDir;
    protected string   $lockFile;

    /**
     * Make the constructor private to enforce boot().
     */
    private function __construct(array  $dbConfig, string $schemaDir, string $lockFile) {
        self::$instance     = $this;
        $this->dbConfig     = $dbConfig;
        $this->schemaDir    = $schemaDir;
        $this->lockFile     = $lockFile;
        $this->services  = new ServiceContainer();
        $this->registerServices();
    }

    /**
     * Boot the application (call once).
     */
    public static function boot(array $dbConfig, string $schemaDir, string $lockFile): void {
        if(isset(self::$instance)) {
            throw new RuntimeException('App::boot() already called');
        }

        self::$instance = new self($dbConfig, $schemaDir, $lockFile);
    }

    /**
     * Magic proxy for App::serviceName() → container->get('serviceName')
     */
    public static function __callStatic(string $name, array $arguments) : mixed {
        return self::$instance->services->get($name);
    }

    /**
     * Get a service instance from the container.
     */
    public static function get(string $service): mixed {
        return self::$instance->services->get($service);
    }

    /**
     * Load your factories from services.php & register them.
     */
    private function registerServices(): void {
        $bindings = require __DIR__ . '/service/services.php';

        foreach($bindings as $key => $factory) {
            // special‐case database so it receives $dbConfig
            if($key === 'database') {
                $this->services->register($key, fn() => $factory($this->dbConfig));
            } else {
                $this->services->register($key, $factory);
            }
        }
    }

    /**
     * Boots the app.
     *
     * @return bool  true if everything initialized OK
     * @throws \Throwable  on any initialization error
     */
    public function run(): bool {
        // silently install or verify schema; will throw on error
        if($this->installSchema()) {
            $this->get('logger')->warning("Installer triggered: tables created or skipped where necessary.", 'installer');
        } else {
            $this->get('logger')->warning("Installer skipped: all required tables already exist.", 'installer');
        }

        echo $this->mailTemplateTest();         // temp test case.
        // at this point you know your DB is good, so hand off
        // to routing / controller logic
        return true;
    }

    /**
     * One–time schema installation via Installer.
     *
     * @return bool  true if we just installed, false if already installed
     * @throws \RuntimeException on any install error
     */
    protected function installSchema(): bool {
        $installer = new Installer(App::get('database')->pdo(), $this->schemaDir, $this->lockFile);

        if(!$missing = $installer->getMissingTables()) {                        // assign and check the result of getMissingTables()
            return false;
        }

        $installer->installTables($missing);                                    // installTables() throws on error
        return true;
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
