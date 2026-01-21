<?php

use Vasoft\Joke\Core\Response\HtmlResponse;
use Vasoft\Joke\Core\Response\ResponseStatus;
use Vasoft\Joke\Core\Routing\Router;

/**
 * @var Router $router
 */
$router->get(
    '/{*}',
    static fn(string $path) => new HtmlResponse()->setStatus(ResponseStatus::NOT_FOUND)->setBody('Not found ' . $path)
);
