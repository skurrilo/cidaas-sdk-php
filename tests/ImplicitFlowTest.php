<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Cidaas\OAuth2\Cidaas;
use Cidaas\OAuth2\Token\AccessToken;

$provider = new Cidaas([
    'baseUrl'                 => 'https://demo.cidaas.de',
    'clientId'                => 'a2fc98a3854b462997df8dd2d8a0dc6e',    // The client ID assigned to you by the provider
    'clientSecret'            => '2790397476058938975',   // The client password assigned to you by the provider
    'redirectUri'             => 'https://demo.cidaas.de/user-ui/html/welcome.html'
]);

print_r($provider->getAuthorizationUrl(["response_type"=>'token']));
print_r("\n");

echo "Copy Paste the above URL in the browser and login and Enter the Access Token : ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);

$accessToken2 = new AccessToken(["access_token" => trim($line)]);
$resourceOwner = $provider->getResourceOwner($accessToken2);

print_r($resourceOwner);
