<?php

use App\Models\User;
use Fig\Http\Message\StatusCodeInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Slim\App;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;
use Slim\Interfaces\RouteCollectorProxyInterface;

return function(App $app) {
    // OAUTH

    $app->post('/oauth/token', function (Request $request, Response $response) {
        try {
            return $this->get('authServer')->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    });

    $app->get('/oauth/authorize', function (Request $request, Response $response) {
        try {
            /** @var AuthorizationServer $server */
            $server = $this->get('authServer');
            $authRequest = $server->validateAuthorizationRequest($request);
            /** @var User $user */
            $authRequest->setUser($user = $this->get('session')->getUser());

            if ($this->get('clientRepo')->wasApproved($authRequest)) {
                $authRequest->setAuthorizationApproved(true);
                return $server->completeAuthorizationRequest($authRequest, $response);
            }

            return $this->get('view')->render($response, 'consent', [
                'authRequest' => $authRequest,
                'requestedScopes' => $this->get('scopeRepo')->listRequestedScopes($authRequest),
                'user' => $this->get('session')->getUser()
            ]);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    })->add('middleware.isAuthenticated');

    $app->post('/oauth/authorize', function (Request $request, Response $response) {
        try {
            /** @var AuthorizationServer $server */
            $server = $this->get('authServer');
            $authRequest = $server->validateAuthorizationRequest($request);
            /** @var User $user */
            $authRequest->setUser($user = $this->get('session')->getUser());

            if ($request->getParsedBodyParam('consent') === 'approve') {
                $authRequest->setAuthorizationApproved(true);
                $this->get('clientRepo')->recordApproval($authRequest);
            } else {
                $authRequest->setAuthorizationApproved(false);
            }

            return $server->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    })->add('middleware.isAuthenticated');

    // LOGGED-IN API

    $app->group('/api', function (RouteCollectorProxyInterface $group) {
        $group->get('/me', function (Request $request, Response $response) {
            if (!$request->getAttribute('oauth_user_id')) {
                return $response->withStatus(StatusCodeInterface::STATUS_FORBIDDEN)
                    ->withJson(['message' => 'This endpoint requires a user to be logged in.']);
            }

            /** @var User $user */
            $user = $this->get('userRepo')->getById($request->getAttribute('oauth_user_id'));
            $scopes = $request->getAttribute('oauth_scopes');

            return $response->withJson(['id' => $user->getId()] +
                (in_array('me.name', $scopes) ?
                    ['first_name' => $user->getFirstName(), 'last_name' => $user->getLastName()] : []) +
                (in_array('me.hash', $scopes) ?
                    ['hash' => $user->getHash()] : []));
        });

        $group->get('/time', function (Request $request, Response $response) {
            return $response->withJson([
                'time' => gmdate('c'),
                'client_id' => $request->getAttribute('oauth_client_id')
            ]);
        });
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
