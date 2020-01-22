<?php

use App\Middleware\IsAuthenticated;
use App\Repositories\AccessTokenRepository;
use App\Repositories\ClientRepository;
use App\Repositories\RefreshTokenRepository;
use App\Repositories\ScopeRepository;
use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Pimple\Container;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

return function (Container $container, array $env) {
    $container['authServer'] = function (Container $container) use ($env): AuthorizationServer {
        $server = new AuthorizationServer(
            $container['clientRepo'],
            $container['accessTokenRepo'],
            $container['scopeRepo'],
            new CryptKey(__DIR__ . '/private.key'),
            $env['APP_SECRET']
        );

        $passwordGrant = new PasswordGrant($container['userRepo'], $container['refreshTokenRepo']);
        $passwordGrant->setRefreshTokenTTL(new DateInterval('P1M'));
        $server->enableGrantType($passwordGrant, new DateInterval('PT1H'));

        $refreshGrant = new RefreshTokenGrant($container['refreshTokenRepo']);
        $refreshGrant->setRefreshTokenTTL(new DateInterval('P1M'));
        $server->enableGrantType($refreshGrant, new DateInterval('PT1H'));

        $server->enableGrantType(new ClientCredentialsGrant(), new DateInterval('PT1H'));

        return $server;
    };
    $container['resourceServer'] = function (Container $container): ResourceServer {
        return new ResourceServer($container['accessTokenRepo'], new CryptKey(__DIR__ . '/public.key'));
    };

    $container['clientRepo'] = fn (Container $container): ClientRepositoryInterface => new ClientRepository();
    $container['accessTokenRepo'] = fn (Container $c): AccessTokenRepository => new AccessTokenRepository($c['db']);
    $container['refreshTokenRepo'] = fn (Container $c): RefreshTokenRepository => new RefreshTokenRepository($c['db']);
    $container['scopeRepo'] = fn (): ScopeRepositoryInterface => new ScopeRepository();

    $container['userRepo'] = fn (Container $container): UserRepository => new UserRepository($container['db']);
    $container['session'] = fn (Container $container): App\Session => new App\Session($container['userRepo']);

    $container['middleware.isAuthenticated'] = fn (Container $c) => new IsAuthenticated($c['session']);
    $container['middleware.isApiAuthenticated'] = function (Container $c) {
        return new class ($c['resourceServer']) extends ResourceServerMiddleware implements MiddlewareInterface
        {
            public function process(ServerRequestInterface $request, RequestHandler $handler): ResponseInterface {
                return $this->__invoke($request, new \Slim\Psr7\Response(), fn ($req, $res) => $handler->handle($req));
            }
        };
    };

    $container['view'] = fn () => new App\View(__DIR__ . '/../templates');

    $container['db'] = function () use ($env): ExtendedPdoInterface {
        return new ExtendedPdo('mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
            $env['DB_USER'], $env['DB_PASSWORD']);
    };
};
