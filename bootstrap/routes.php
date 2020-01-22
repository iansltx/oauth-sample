<?php

use App\Models\User;
use Fig\Http\Message\StatusCodeInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Slim\App;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;

return function(App $app) {
    // OAUTH

    $app->post('/oauth/token', function (Request $request, Response $response) {
        try {
            return $this->get('authServer')->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    });

    // LOGGED-IN API

    $app->get('/api/me', function (Request $request, Response $response) {
        /** @var User $user */
        $user = $this->get('userRepo')->getById($request->getAttribute('oauth_user_id'));
        $scopes = $request->getAttribute('oauth_scopes');

        return $response->withJson(['id' => $user->getId()] +
            (in_array('me.name', $scopes) ?
                ['first_name' => $user->getFirstName(), 'last_name' => $user->getLastName()] : []) +
            (in_array('me.hash', $scopes) ?
                ['hash' => $user->getHash()] : []));
    })->add('middleware.isApiAuthenticated');

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
