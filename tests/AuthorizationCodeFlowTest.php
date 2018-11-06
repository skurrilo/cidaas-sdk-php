<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cidaas\OAuth2\Cidaas;

$provider = new Cidaas([
    'baseUrl' => 'cidaas-base-url',
    'clientId' => 'YOUR CIDAAS CLIENT ID', // The client ID assigned to you by the provider
    'clientSecret' => 'YOUR CIDAAS CLIENT SECRET', // The client password assigned to you by the provider
    'redirectUri' => 'http://localhost:8080',
]);

print_r($provider->getAuthorizationUrl(["response_type" => 'code']));
print_r("\n");

echo "Copy Paste the above URL in the browser and login and Enter the Code : ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);

$accessToken = $provider->getAccessToken('authorization_code', [
    'code' => trim($line),
]);

print_r($accessToken->getToken());
print_r("\n");
print_r($accessToken->getRefreshToken());
print_r("\n");

$resourceOwner = $provider->getResourceOwner($accessToken);

print_r($resourceOwner);
print_r("\n");

$refrehToken = $provider->getAccessToken('refresh_token', [
    'refresh_token' => trim($accessToken->getRefreshToken()),
]);

print_r($refrehToken->getToken());
print_r("\n");
