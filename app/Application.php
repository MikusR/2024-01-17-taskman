<?php

declare(strict_types=1);

namespace App;

use App\Controllers\TaskController;
use Doctrine\DBAL\Exception;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use FastRoute;


class Application
{


    public function run(): void
    {
        //Initialize twig
        $loader = new FilesystemLoader(__DIR__ . '/Views');
        $twig   = new Environment($loader, []);
        $twig->addGlobal('errors', false);
        //Router
        $dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [TaskController::class, 'index']);
            $r->addRoute('POST', '/', [TaskController::class, 'add']);
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
                try {
                    $response = (new $controller())->{$method}(...array_values($vars));
                } catch (Exception $e) {
                    var_dump($e);
                    die;
                }


                switch (true) {
                    case $response instanceof ViewResponse:
                        echo $twig->render($response->getViewName() . '.twig', $response->getData());
                        break;
                    case $response instanceof RedirectResponse:
                        header('Location: ' . $response->getLocation());
                        break;
                }
                break;
        }
    }


}