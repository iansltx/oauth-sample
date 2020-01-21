<?php

use Fig\Http\Message\StatusCodeInterface;
use Slim\App;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;

return function(App $app) {
    // LOGGED-IN USER ROUTE

    $app->get('/', function(Request $request, Response $response) {
        return $this->get('view')->render($response, 'home', ['user' => $this->get('session')->getUser()]);
    })->add('middleware.isAuthenticated');

    // LOGIN + LOGOUT

    $app->get('/login', function(Request $request, Response $response) {
        return $this->get('view')->render($response, 'login', ['wasLoggedOut' => $request->getQueryParam('loggedOut')]);
    });

    $app->post('/login', function(Request $request, Response $response) {
        if ($user = $this->get('userRepo')->getByUsernameAndPassword(
            $request->getParsedBodyParam('username'),
            $request->getParsedBodyParam('password')
        )) {
            $this->get('session')->setUser($user);
            return $response->withStatus(StatusCodeInterface::STATUS_FOUND)
                ->withHeader('Location', urldecode($request->getQueryParam('dest')) ?: '/');
        }

        return $this->get('view')->render($response, 'login', ['error' => 'Your credentials did not match.']);
    });

    $app->get('/logout', function (Request $request, Response $response) {
        $this->get('session')->logOut();

        return $response->withStatus(StatusCodeInterface::STATUS_FOUND)
            ->withHeader('Location', '/login?loggedOut=true');
    })->add('middleware.isAuthenticated');
};
