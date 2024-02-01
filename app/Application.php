<?php

declare(strict_types=1);

namespace App;

use App\Controllers\TaskController;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use FastRoute;
use Dotenv;


class Application
{


    public function run(): void
    {
        //Setup dotenv
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
        $dotenv->safeLoad();
        //Set configuration parameters
        date_default_timezone_set($_ENV['TIMEZONE']);

        //Initialize twig
        $loader            = new FilesystemLoader(__DIR__.'/Views');
        $twig              = new Environment($loader, []);
        $_SESSION['error'] = [];

        //Router
        $dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [TaskController::class, 'index']);
            $r->addRoute('POST', '/', [TaskController::class, 'add']);
            $r->addRoute('POST', '/search', [TaskController::class, 'search']);
            $r->addRoute('GET', '/search', [TaskController::class, 'showSearch']);

            $r->addRoute('GET', '/task/{id:\d+}/edit', [TaskController::class, 'edit']);
            $r->addRoute('POST', '/task/{id:\d+}', [TaskController::class, 'update']);
            $r->addRoute('GET', '/task/{id:\d+}', [TaskController::class, 'show']);
            $r->addRoute('POST', '/task/{id:\d+}/delete', [TaskController::class, 'delete']);
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri        = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars    = $routeInfo[2];

                [$controller, $method] = $handler;

                $response = (new $controller())->{$method}(...array_values($vars));

                $twig->addGlobal('error', $_SESSION['error']);
                $twig->addGlobal('nameError', $_SESSION['nameError']);

                switch (true) {
                    case $response instanceof ViewResponse:
                        try {
                            echo $twig->render($response->getViewName().'.twig', $response->getData());
                        } catch (Error $e) {
                            echo "<h2>There is an error with this application</h2>";
                            echo "<p>{$e->getMessage()}</p>";
                        }
                        break;
                    case $response instanceof RedirectResponse:
                        header('Location: '.$response->getLocation());
                        break;
                }
                break;
        }
    }


}