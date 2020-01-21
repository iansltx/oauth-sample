<?php

use App\Middleware\IsAuthenticated;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use Pimple\Container;
use App\Repositories\UserRepository;

return function (Container $container, array $env) {
    $container['userRepo'] = fn (Container $container): UserRepository => new UserRepository($container['db']);
    $container['session'] = fn (Container $container): App\Session => new App\Session($container['userRepo']);
    $container['middleware.isAuthenticated'] = fn (Container $container) => new IsAuthenticated($container['session']);
    $container['view'] = fn () => new App\View(__DIR__ . '/../templates');

    $container['db'] = function () use ($env): ExtendedPdoInterface {
        return new ExtendedPdo('mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
            $env['DB_USER'], $env['DB_PASSWORD']);
    };
};
