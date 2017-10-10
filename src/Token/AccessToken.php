<?php
/**
 * Created by PhpStorm.
 * User: vimalprakash
 * Date: 05/10/17
 * Time: 12:52 PM
 */
namespace  Cidaas\OAuth2\Token;

use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

class AccessToken implements JsonSerializable
{
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var int
     */
    protected $expires;

    /**
     * @var string
     */
    protected $refreshToken;

    /**
     * @var string
     */
    protected $resourceOwnerId;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * Constructs an access token.
     *
     * @param array $options An array of options returned by the service provider
     *     in the access token request. The `access_token` option is required.
     * @throws InvalidArgumentException if `access_token` is not provided in `$options`.
     */
    public function __construct(array $options = [])
    {
        if (empty($options['access_token'])) {
            throw new InvalidArgumentException('Required option not passed: "access_token"');
        }

        $this->accessToken = $options['access_token'];

        if (!empty($options['resource_owner_id'])) {
            $this->resourceOwnerId = $options['resource_owner_id'];
        }

        if (!empty($options['refresh_token'])) {
            $this->refreshToken = $options['refresh_token'];
        }

        // We need to know when the token expires. Show preference to
        // 'expires_in' since it is defined in RFC6749 Section 5.1.
        // Defer to 'expires' if it is provided instead.
        if (isset($options['expires_in'])) {
            if (!is_numeric($options['expires_in'])) {
                throw new \InvalidArgumentException('expires_in value must be an integer');
            }

            $this->expires = $options['expires_in'] != 0 ? time() + $options['expires_in'] : 0;
        } elseif (!empty($options['expires'])) {
            // Some providers supply the seconds until expiration rather than
            // the exact timestamp. Take a best guess at which we received.
            $expires = $options['expires'];

            if (!$this->isExpirationTimestamp($expires)) {
                $expires += time();
            }

            $this->expires = $expires;
        }

        // Capture any additional values that might exist in the token but are
        // not part of the standard response. Vendors will sometimes pass
        // additional user data this way.
        $this->values = array_diff_key($options, array_flip([
            'access_token',
            'resource_owner_id',
            'refresh_token',
            'expires_in',
            'expires',
        ]));
    }

    /**
     * Check if a value is an expiration timestamp or second value.
     *
     * @param integer $value
     * @return bool
     */
    protected function isExpirationTimestamp($value)
    {
        // If the given value is larger than the original OAuth 2 draft date,
        // assume that it is meant to be a (possible expired) timestamp.
        $oauth2InceptionDate = 1349067600; // 2012-10-01
        return ($value > $oauth2InceptionDate);
    }

    /**
     * Returns the access token string of this instance.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->accessToken;
    }

    /**
     * Returns the refresh token, if defined.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Returns the expiration timestamp, if defined.
     *
     * @return integer|null
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Returns the resource owner identifier, if defined.
     *
     * @return string|null
     */
    public function getResourceOwnerId()
    {
        return $this->resourceOwnerId;
    }

    /**
     * Checks if this token has expired.
     *
     * @return boolean true if the token has expired, false otherwise.
     * @throws RuntimeException if 'expires' is not set on the token.
     */
    public function hasExpired()
    {
        $expires = $this->getExpires();

        if (empty($expires)) {
            throw new RuntimeException('"expires" is not set on the token');
        }

        return $expires < time();
    }

    /**
     * Returns additional vendor values stored in the token.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Returns the token key.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getToken();
    }

    /**
     * Returns an array of parameters to serialize when this is serialized with
     * json_encode().
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $parameters = $this->values;

        if ($this->accessToken) {
            $parameters['access_token'] = $this->accessToken;
        }

        if ($this->refreshToken) {
            $parameters['refresh_token'] = $this->refreshToken;
        }

        if ($this->expires) {
            $parameters['expires'] = $this->expires;
        }

        if ($this->resourceOwnerId) {
            $parameters['resource_owner_id'] = $this->resourceOwnerId;
        }

        return $parameters;
    }
}