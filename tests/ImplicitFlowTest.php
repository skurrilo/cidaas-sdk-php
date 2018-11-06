<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Cidaas\OAuth2\Cidaas;
use Cidaas\OAuth2\Token\AccessToken;

$provider = new Cidaas([
    'baseUrl' => 'cidaas-base-url',
    'clientId' => 'YOUR CIDAAS CLIENT ID', // The client ID assigned to you by the provider
    'clientSecret' => 'YOUR CIDAAS CLIENT SECRET', // The client password assigned to you by the provider
    'redirectUri' => 'http://localhost:8080',
]);

print_r($provider->getAuthorizationUrl(["response_type" => 'token']));
print_r("\n");

echo "Copy Paste the above URL in the browser and login and Enter the Access Token : ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);

$accessToken2 = new AccessToken(["access_token" => trim($line)]);
$resourceOwner = $provider->getResourceOwner($accessToken2);

print_r($resourceOwner);
