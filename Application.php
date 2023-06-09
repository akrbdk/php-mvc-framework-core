<?php

namespace Akrbdk\PhpMvcCore;

use Akrbdk\PhpMvcCore\Db\Database;
use Exception;

class Application
{
    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'afterRequest';
    public static string $ROOT_DIR;
    public static Application $app;
    public string $layout = 'main';
    public string $userClass;
    public View $view;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public ?Controller $controller = null;
    public Database $db;
    public ?UserModel $user;
    protected array $eventListeners = [];

    public function __construct($rootPath, array $config)
    {
        $this->userClass = $config['userClass'];

        self::$ROOT_DIR = $rootPath;
        self::$app = $this;

        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->view = new View();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config['db']);

        $primaryValue = $this->session->get('user');
        if ($primaryValue) {
            $userClass = new $this->userClass();
            $primaryKey = $userClass->primaryKey();
            $this->user = $userClass->findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }
    }

    public static function isGuest(): bool
    {
        return !self::$app->user;
    }

    /**
     * @return Controller
     */
    public function getController(): Controller
    {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function run(): void
    {
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);

        try {
            echo $this->router->resolve();
        } catch (Exception $e) {
            Application::$app->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $e
            ]);
        }

        $this->triggerEvent(self::EVENT_AFTER_REQUEST);
    }

    public function triggerEvent($eventName): void
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }

    public function login(UserModel $user): bool
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        return true;
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->unset('user');
    }

    public function on($eventName, $callback): void
    {
        $this->eventListeners[$eventName][] = $callback;
    }
}
