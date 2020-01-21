<?php

namespace App\Middleware;

use App;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;

class IsAuthenticated implements MiddlewareInterface
{
    protected App\Session $session;

    public function __construct(App\Session $session)
    {
        $this->session = $session;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!$this->session->isLoggedIn()) {
            return new Response(
                StatusCodeInterface::STATUS_FOUND,
                new Headers([
                    'Location' => '/login?dest=' .
                        urlencode($request->getUri()->getPath() . '?' . $request->getUri()->getQuery())
                ])
            );
        }

        return $handler->handle($request);
    }
}
