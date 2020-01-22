<?php

$accessTokenResponse = json_decode(file_get_contents(
    'http://localhost/oauth/token',
    false,
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => 'machine-to-machine',
                'client_secret' => 'super-secret-client-secret-string'
            ])
        ]
    ])
));

echo "=== GOT ACCESS TOKEN ===\n\n";

print_r($accessTokenResponse);

$timeResponse = json_decode(file_get_contents(
    'http://localhost/api/time',
    false,
    stream_context_create([
        'http' => [
            'header' => 'Authorization: Bearer ' . $accessTokenResponse->access_token,
        ]
    ])
));

echo "\n=== GOT CURRENT TIME ===\n\n";

print_r($timeResponse);

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

/** @noinspection PhpUnreachableStatementInspection */
$userInfoResponse = json_decode(file_get_contents(
    'http://localhost/api/me',
    false,
    stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'header' => 'Authorization: Bearer ' . $accessTokenResponse->access_token,
        ]
    ])
));

echo "\n=== USER INFO ERROR ===\n\n";
print_r($userInfoResponse);
