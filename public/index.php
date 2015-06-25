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

$maxImageSize = 2000 * 2000;

$manipulators = [
    new League\Glide\Api\Manipulator\Orientation(),
    new League\Glide\Api\Manipulator\Rectangle(),
    new Liftopia\Manipulator\FillCanvasSize($maxImageSize),
    new League\Glide\Api\Manipulator\Brightness(),
    new League\Glide\Api\Manipulator\Contrast(),
    new League\Glide\Api\Manipulator\Gamma(),
    new League\Glide\Api\Manipulator\Sharpen(),
    new League\Glide\Api\Manipulator\Filter(),
    new League\Glide\Api\Manipulator\Blur(),
    new League\Glide\Api\Manipulator\Pixelate(),
    new League\Glide\Api\Manipulator\Output(),
];

$imageManager = new Intervention\Image\ImageManager();

$api = new League\Glide\Api\Api($imageManager, $manipulators);

$server = new League\Glide\Server($source, $cache, $api);
$server->setBaseUrl('/img/');

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
