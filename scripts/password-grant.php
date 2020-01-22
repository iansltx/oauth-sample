<?php

$accessTokenResponse = json_decode(file_get_contents(
    'http://localhost/oauth/token',
    false,
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'grant_type' => 'password',
                'client_id' => 'first-party',
                'username' => 'superuser',
                'password' => 'super-secret',
                'scope' => implode(' ', array_slice($argv, 1))
            ])
        ]
    ])
));

echo "=== GOT ACCESS TOKEN ===\n\n";

print_r($accessTokenResponse);

$userInfoResponse = json_decode(file_get_contents(
    'http://localhost/api/me',
    false,
    stream_context_create([
        'http' => [
            'header' => 'Authorization: Bearer ' . $accessTokenResponse->access_token,
        ]
    ])
));

echo "\n=== GOT USER PROFILE INFO ===\n\n";

print_r($userInfoResponse);

function splitJWT($jwt)
{
    return array_map(function (string $encoded) {
        return json_decode(/* base64url-encoded */ base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded)));
    }, array_slice(explode('.', $jwt), 0, 2));
}

[$header, $payload] = splitJWT($accessTokenResponse->access_token);

echo "\n== SHOWING JWT HEADER ==\n\n";

print_r($header);

echo "\n== SHOWING JWT PAYLOAD ==\n\n";

print_r($payload);
