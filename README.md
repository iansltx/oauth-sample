# Don't Fear The OAuth - Sample Project

A trivial sample project exhibiting a server-side OAuth 2 integration

To set up, run `docker-compose up --build`. This will build the app and database, including a user
with credentials `superuser:super-secret`. The application is served at `localhost:80`. To clear the
database/close down the app, run `docker-compose down`.

To see the Password Grant in action, run `php scripts/password-grand.php` in a local terminal (doesn't
require Docker if you have a relatively recent version of PHP installed). Feed it requested scopes
(e.g. me.name, me.hash) at the command line.
