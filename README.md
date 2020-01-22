# Don't Fear The OAuth - Sample Project

A trivial sample project exhibiting a server-side OAuth 2 integration

To set up, run `docker-compose up --build`. This will build the app and database, including a user
with credentials `superuser:super-secret`. The application is served at `localhost:80`. To clear the
database/close down the app, run `docker-compose down`.

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
