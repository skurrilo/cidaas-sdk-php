<?php
/**
 * Created by PhpStorm.
 * User: vimalprakash
 * Date: 09/10/17
 * Time: 12:19 AM
 */

namespace Cidaas\OAuth2;


use GuzzleHttp\Message\ResponseInterface;

class CidaasIdentityProviderException extends IdentityProviderException
{
    /**
     * @param  ResponseInterface $response
     * @param  string|null $message
     * @return IdentityProviderException
     */
    public static function fromResponse(ResponseInterface $response, $message = null)
    {
        return new static($message, $response->getStatusCode(), (string) $response->getBody());
    }
}