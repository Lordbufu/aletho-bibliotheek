<?php
namespace App\Core;

use App\Core\Input\Request;
use App\Core\ServiceContainer;
use App\Core\Auth\UserPermissions;
use App\Core\Database\{Database, Installer};

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
    protected string $schemaDir;
    protected string $lockFile;

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
        // Hardcoded services:
        $this->services->register('database', fn() => new Database($this->dbConfig));
        $this->services->register('request', fn() => new Request(array_merge($_GET, $_POST)));
        $this->services->register('perm', fn() => new UserPermissions());

        // Load defined intances in 'services.php'
        $bindings = require __DIR__ . '/service/services.php';

        foreach($bindings as $key => $factory) {
            $this->services->register($key, $factory);
        }
    }

    /**
     * Boots the app.
     *
     * @return bool  true if everything initialized OK
     * @throws \Throwable  on any initialization error
     */
    public static function run(): bool {
        // silently install or verify schema; will throw on error
        if(self::$instance->installSchema()) {
            self::$instance->get('logger')->warning("Installer triggered: tables created or skipped where necessary.", 'installer');
        }
        // else {
        //     self::$instance->get('logger')->warning("Installer skipped: all required tables already exist.", 'installer');
        // }

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

    /**
     * Render a view file from app/views/ with optional data.
     *
     * @param string $view      View filename (e.g., 'main.view.php')
     * @param array  $data      Associative array of data for the view
     * @return void
     */
    public static function view(string $view, array $data = []): void {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View not found: {$view}";
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
    }

    /**
     * Redirect to a different URL and exit.
     *
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    public static function redirect(string $url, int $statusCode = 302): void {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}
