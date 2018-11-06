<?php
/**
 * Created by PhpStorm.
 * User: vimalprakash
 * Date: 09/10/17
 * Time: 12:12 AM
 */

namespace Cidaas\OAuth2;

use Cidaas\OAuth2\Helpers\ArrayAccessorTrait;

class CidaasResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected $response;

    public function __construct(array $response = [])
    {
        $id = "";
        if (isset($response["identities"])) {
            $id = $response["identities"][0]["identityId"];
        }

        $this->response = [
            "id" => $id,
            "provider" => $this->getValueByKey($response, 'provider'),
            "ssoId" => $this->getValueByKey($response, 'sub'),
            "username" => $this->getValueByKey($response, 'username'),
            "email" => $this->getValueByKey($response, 'email'),
            "mobile" => $this->getValueByKey($response, 'mobile_number'),
            "firstname" => $this->getValueByKey($response, 'given_name'),
            "lastname" => $this->getValueByKey($response, 'family_name'),
            "emailVerified" => $this->getValueByKey($response, 'email_verified'),
            "mobileNoVerified" => $this->getValueByKey($response, 'mobile_number_verified'),
            "currentLocale" => $this->getValueByKey($response, 'locale'),
            "userStatus" => $this->getValueByKey($response, 'user_status'),
            "identityJRString" => $this->getValueByKey($response, 'rawJSON'),
            "customFields" => $this->getValueByKey($response, 'customFields'),
            "roles" => $this->getValueByKey($response, 'roles'),
            "photoURL" => $this->getValueByKey($response, 'picture'),
            "groupInfo" => $this->getValueByKey($response, 'groups'),
            "displayName" => $this->getValueByKey($response, 'name'),
            "lastLoggedTime" => $this->getValueByKey($response, 'last_accessed_at'),
            "lastUsedSocialIdentity" => $this->getValueByKey($response, 'last_used_identity_id'),
            "groups" => $this->getValueByKey($response, 'groups'),
            "middle_name" => $this->getValueByKey($response, 'middle_name'),
            "nickname" => $this->getValueByKey($response, 'nickname'),
            "profile" => $this->getValueByKey($response, 'profile'),
            "birthdate" => $this->getValueByKey($response, 'birthdate'),
            "address" => $this->getValueByKey($response, 'address'),
            "identityCustomFields" => $this->getValueByKey($response, 'identityCustomFields'),
            "providerUserId" => $this->getValueByKey($response, 'providerUserId'),
            "updated_at" => $this->getValueByKey($response, 'updated_at'),
            "identities" => $this->getValueByKey($response, 'identities'),
        ];
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }
    public function getUserId()
    {
        return $this->getValueByKey($this->response, 'ssoId');
    }
    public function getProvider()
    {
        return $this->getValueByKey($this->response, 'provider');
    }
    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'email');
    }
    public function getName()
    {
        return $this->getValueByKey($this->response, 'displayName');
    }
    public function getPictureUrl()
    {
        return $this->getValueByKey($this->response, 'photoURL');
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->response;
    }
}
