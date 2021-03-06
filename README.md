# Don't Fear The OAuth - Sample Project

A trivial sample project exhibiting a server-side OAuth 2 integration

## Setup

To set up, run `docker-compose up --build`. This will build the app and database, including a user
with credentials `superuser:super-secret`. The application is served at `localhost:80`. To clear the
database/close down the app, run `docker-compose down`.

## Grants in Action

> Browser-based flows (implicit, PKCE) require a modern web browser, as I'm using async/await, fetch,
> arrow functions, and other modern JS/HTML5 features, as well as the Web Crypto API to grab random
> data and calculate hashes. Recent versions of Firefox or Chrome have been tested with these examples.

To see the Password Grant in action, run `php scripts/password-grand.php` in a local terminal (doesn't
require Docker if you have a relatively recent version of PHP installed). Feed it requested scopes
(e.g. me.name, me.hash) at the command line. To see the Client Credentials Grant in action, run
`php scripts/client-credentials-grant.php`.

To see the Implicit Grant in action, navigate to `http://localhost/spa-implicit.php`. You'll be
redirected to an app authorization page, or to a login page if you haven't yet logged into the app.
Approving or denying the authorization request will redirect you back to the SPA page, which will
show either access token information and user information (pulled via an API endpoint) if approved,
or auth errors if denied. This app stores a random `state` value in local storage before redirecting
to the authorization server, and checks that value when it receives a redirect back.

To see the Authorization Code grant in action on a server-side scenario (using the client secret),
navigate to `http://localhost/server-side-auth-code.php`. You'll be redirected to the same app
authorization/login flow as for the implicit grant, but when you get redirected back the server-side
will handle redeeming the resulting auth code for an access token. The `state` value is stored
cross-request in a cookie.

To see the Authorization Code grant used in a client-side application via PKCE, navigate to
`http://localhost/spa-pkce.php`. You'll be redirected to log in and authorize after a few seconds,
and when you're redirected back your browser will redeem its auth code for an access token, then
use that access token to pull user info, completely client-side. Both `state` and `code_verifier`
fields required for this flow are stored using localStorage.
