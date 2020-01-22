<?php if (!$_GET) {
    setcookie('oauthState', $state = base64_encode(random_bytes(18)));
    header('Location: /oauth/authorize?' . http_build_query([
        'client_id' => 'third-party-service',
        'response_type' => 'code',
        'scope' => 'me.name',
        'state' => $state
    ]));
    return;
} ?>
<!doctype html>
<html lang="en">
<body>
<h3>Query String Parameters</h3>
<p><?= nl2br(print_r($_GET, true)) ?><p>
<p><?= $_GET['state'] === $_COOKIE['oauthState'] ? 'State matched!' : 'STATE MISMATCH' ?></p>

<?php if ($_GET['code']) {
    $accessTokenResponse = json_decode(file_get_contents(
        'http://localhost/oauth/token',
        false,
        stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'grant_type' => 'authorization_code',
                    'client_id' => 'third-party-service',
                    'client_secret' => 'another-super-secret-string',
                    'code' => $_GET['code'],
                    'redirect_uri' => 'http://localhost/server-side-auth-code.php'
                ])
            ]
        ])
    ));
} ?>

<h3>Access Token Response</h3>
<p><?= nl2br(print_r($accessTokenResponse, true)) ?></p>

<?php $userInfoResponse = json_decode(file_get_contents(
    'http://localhost/api/me',
    false,
    stream_context_create([
        'http' => [
            'header' => 'Authorization: Bearer ' . $accessTokenResponse->access_token,
        ]
    ])
)); ?>

<h3>User Info Response</h3>
<p><?= nl2br(print_r($userInfoResponse, true)) ?></p>

</body></html>
