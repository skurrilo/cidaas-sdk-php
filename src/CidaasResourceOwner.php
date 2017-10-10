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
        $this->response = $response;
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