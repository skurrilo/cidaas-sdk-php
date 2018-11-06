<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cidaas\OAuth2\Cidaas;
use Cidaas\OAuth2\Token\AccessToken;

$provider = new Cidaas([
    'baseUrl' => 'cidaas-base-url',
    'clientId' => 'YOUR CIDAAS CLIENT ID', // The client ID assigned to you by the provider
    'clientSecret' => 'YOUR CIDAAS CLIENT SECRET', // The client password assigned to you by the provider
]);

$accessToken = $provider->getAccessToken('client_credentials');

print_r($accessToken->getToken());
print_r("\n");

$accessToken2 = new AccessToken(["access_token" => $accessToken->getToken()]);

$parsedInfo = [
    "access_token" => $accessToken2->getToken(),
    "headers" => [
        "x-forwarded-for" => "192.168.2.1",
        "user-agent" => "Chrome",
    ],
    "requestURL" => "/test",

];

$tokenValid = $provider->validateToken($parsedInfo);
if ($tokenValid["status_code"] == 200) {
    print_r("valid token");
} else {
    print_r("not valid token");
}
print_r("\n");

$userinfo = $provider->getUserInfoById($accessToken2, "0ed1e411-3b77-4c89-8957-37bcf8cfd34c");
print_r($userinfo);
print_r("\n");
