<?php
/**
 * Created by PhpStorm.
 * User: vimalprakash
 * Date: 19/10/17
 * Time: 4:58 PM
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cidaas\OAuth2\Cidaas;

$provider = new Cidaas([
    'baseUrl' => 'cidaas-base-url',
    'clientId' => 'YOUR CIDAAS CLIENT ID', // The client ID assigned to you by the provider
    'clientSecret' => 'YOUR CIDAAS CLIENT SECRET', // The client password assigned to you by the provider
]);

$parsedInfo = [
    "access_token" => "eyJhbGciOiJSUzI1NiIsImtpZCI6IjA5YTU3NDY1LTkxNjgtNDcwMy1hNDQ0LWE2OTJjZWVjMWJlMyJ9.eyJzaWQiOiJkZDAxOTcxYi1jMTk5LTQyMmItODJkNC1kOTUwNjFjODkzNTMiLCJzdWIiOiIwZWQxZTQxMS0zYjc3LTRjODktODk1Ny0zN2JjZjhjZmQzNGMiLCJpc3ViIjoiZWZjOWI2NzktMGEyNi00MDM2LTk1NmUtYThkYWUzN2M4YjM0IiwiYXVkIjoiZTBlMWE1MzAtNzNkYS00YjE4LTlkNzYtNTU5MzM4MTJkMjdmIiwiaWF0IjoxNTQxNTMxMTAxLCJhdXRoX3RpbWUiOjE1NDE1MzExMDEsImlzcyI6Imh0dHBzOi8vdmltc2luZGlhLWZyZWUuY2lkYWFzLmRlIiwianRpIjoiNDRkNTMxNWItZjk4ZS00MDc1LTgzOGUtOTQ2OTg5YjNlNjBiIiwibm9uY2UiOiIxNTQxNTMxMDg5OTQ0Iiwic2NvcGVzIjpbIm9wZW5pZCIsInByb2ZpbGUiLCJlbWFpbCIsInBob25lIiwiYWRkcmVzcyIsIm9mZmxpbmVfYWNjZXNzIiwiaWRlbnRpdGllcyIsInJvbGVzIiwiZ3JvdXBzIl0sInJvbGVzIjpbIlVTRVIiXSwiZ3JvdXBzIjpbeyJncm91cElkIjoiQ0lEQUFTX0FETUlOUyIsInJvbGVzIjpbIkFETUlOIl19XSwiZXhwIjoxNTQxNjE3NTAxfQ.aNPIUTxtsPdJvUUQIs4_FZZIhGim0-YGSXEsuT4f5vcaD_phlPGs-jMwaaEFz-Cp7P96-OdvqfEb8MCZnzEckqrQXqGq1MVldePYGa746d0HdM93tfbpLj4HQH0uV10Qq_TjGkEw32JVCsSyt7vaMjLFaZ50V6hEh5iD1rv74D4",
    "headers" => [
        "x-forwarded-for" => "192.168.2.1",
        "user-agent" => "Chrome",
    ],
    "requestURL" => "/test",

];
$result = $provider->validateToken($parsedInfo, null, null);

print_r($result);
print_r("\n");

$tokenExpired = $provider->isTokenExpired($parsedInfo["access_token"]);

if ($tokenExpired == true) {
    print_r("In valid token");
    print_r("\n");
} else {
    print_r("Valid token");
    print_r("\n");
}
