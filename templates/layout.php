<!doctype html>
<html lang="en">
    <head>
        <title><?= $title ?? 'OAuth 2 Sample App' ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    </head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <style>body { margin-top: 2em; }</style>
    <body>
        <div class="container-fluid">
            <?php if (isset($user)): ?>
                <div style="float: right"><a href="/logout">Log Out</a></div>
            <?php endif; ?>
            <?= $content ?? '' ?>
        </div>
    </body>
</html>
