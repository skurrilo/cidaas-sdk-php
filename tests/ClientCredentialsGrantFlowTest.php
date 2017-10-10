<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cidaas\OAuth2\Cidaas;
use Cidaas\OAuth2\Token\AccessToken;



$provider = new Cidaas([
    'baseUrl'                 => 'yourcidaasbaseurl',
    'clientId'                => 'xxxx',    // The client ID assigned to you by the provider
    'clientSecret'            => 'yyyy',   // The client password assigned to you by the provider
]);


$accessToken = $provider->getAccessToken('client_credentials');

print_r($accessToken->getToken());
print_r("\n");


$accessToken2 = new AccessToken(["access_token" => $accessToken->getToken()]);

$tokenValid = $provider->validateToken($accessToken2->getToken());
if($tokenValid){
    print_r("valid token");
}else{
    print_r("not valid token");
}
print_r("\n");


$userinfo = $provider->getUserInfoById($accessToken2,"ff8829d5-7bc6-4158-9757-20077ecc627f");
print_r($userinfo);
print_r("\n");
