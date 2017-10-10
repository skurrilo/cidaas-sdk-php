<?php
/**
 * Created by PhpStorm.
 * User: vimalprakash
 * Date: 09/10/17
 * Time: 12:13 AM
 */

namespace Cidaas\OAuth2;

/**
 * Classes implementing `ResourceOwnerInterface` may be used to represent
 * the resource owner authenticated with a service provider.
 */
interface ResourceOwnerInterface
{
    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray();
}