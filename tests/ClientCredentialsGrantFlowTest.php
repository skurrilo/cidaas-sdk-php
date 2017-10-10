<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cidaas\OAuth2\Cidaas;
use Cidaas\OAuth2\Token\AccessToken;



$provider = new Cidaas([
    'baseUrl'                 => 'https://apis-cidaas.test.carbookplus.com',
    'clientId'                => '58b03a8f2f6e4bbc84f9160e122338fc',    // The client ID assigned to you by the provider
    'clientSecret'            => '4017295703147628411',   // The client password assigned to you by the provider
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


$userinfo = $provider->getUserInfoById($accessToken2,"da81b6a0-7e47-4984-9dc6-d706e75143fd");
print_r($userinfo);
print_r("\n");
