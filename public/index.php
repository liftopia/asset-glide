<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\Route\Http\Exception\NotFoundException;

// Set image source
$source = new League\Flysystem\Filesystem(
    new League\Flysystem\Adapter\Local(__DIR__ . '/../data/source')
);

// Set image cache
$cache = new League\Flysystem\Filesystem(
    new League\Flysystem\Adapter\Local(__DIR__ . '/../data/cache')
);

$server = League\Glide\ServerFactory::create([
    'base_url' => '/img/',
    'cache' => $cache,
    'max_image_size' => 2000 * 2000,
    'source' => $source,
]);

$container = new League\Container\Container;
$container->add('Symfony\Component\HttpFoundation\Request', function () {
    return Symfony\Component\HttpFoundation\Request::createFromGlobals();
});

$router = new League\Route\RouteCollection($container);
$router->get('/img/{path}', function (Request $request) use ($server) {
    try {
        $response = $server->getImageResponse($request);
    } catch (League\Glide\Http\NotFoundException $e) {
        throw new NotFoundException($e->getMessage());
    }

    return $response;
});

$router->get(404, function(Request $request, Response $response) {
    $response->setStatusCode(404);
    return $response;
});

$dispatcher = $router->getDispatcher();
$request = Request::createFromGlobals();

try {
    $response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
} catch (NotFoundException $e) {
    $response = $dispatcher->dispatch('GET', 404);
}

// Output response from router
$response->send();