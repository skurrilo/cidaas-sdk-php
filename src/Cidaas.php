<?php

namespace  Cidaas\OAuth2;

use Cidaas\OAuth2\Helpers\AbstractGrant;
use Cidaas\OAuth2\Helpers\ArrayAccessorTrait;
use Cidaas\OAuth2\Helpers\QueryBuilderTrait;
use Cidaas\OAuth2\Helpers\RequiredParameterTrait;
use Cidaas\OAuth2\Token\AccessToken;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use UnexpectedValueException;

class Cidaas {

    use ArrayAccessorTrait;
    use QueryBuilderTrait;
    use RequiredParameterTrait;

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;

            protected $baseUrl;

            /**
            * @var string HTTP method used to fetch access tokens.
            */
            const METHOD_GET = 'GET';
     
         /**
          * @var string HTTP method used to fetch access tokens.
          */
         const METHOD_POST = 'POST';
     
         /**
          * @var string
          */
         protected $clientId;
     
         /**
          * @var string
          */
         protected $clientSecret;
     
         /**
          * @var string
          */
         protected $redirectUri;
     
         /**
          * @var string
          */
         protected $state;




    public function __construct(array $options = [], array $collaborators = [])
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }

    }

    protected function domain()
    {
        if (empty($this->baseUrl)) {
            throw new \RuntimeException('Cidaas base url is not specified');
        }

        return $this->baseUrl;
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->domain() . '/oauth2-login/oauth2/authz';
    }

    public function getManagerUserInfo()
    {
        return $this->domain() . '/oauth2-usermanagement/oauth2/user';
    }


    public function getValidateTokenUrl()
    {
        return $this->domain() . '/oauth2-login/oauth2/checktoken';
    }

    public function getBaseAccessTokenUrl(array $params = [])
    {
        return $this->domain() . '/oauth2-login/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->domain() . '/oauth2-usermanagement/oauth2/userinfo';
    }

    public function getTokenInfoUrl()
    {
        return $this->domain() . '/token/userinfobytoken';
    }

    public function getDefaultScopes()
    {
        return ['openid','profile', 'email'];
    }


    /**
     * Returns the method to use when requesting an access token.
     *
     * @return string HTTP method
     */
    protected function getAccessTokenMethod()
    {
        return self::METHOD_POST;
    }


    /**
     * Builds the access token URL's query string.
     *
     * @param  array $params Query parameters
     * @return string Query string
     */
    protected function getAccessTokenQuery(array $params)
    {
        return $this->buildQueryString($params);
    }


    /**
     * Returns the request body for requesting an access token.
     *
     * @param  array $params
     * @return string
     */
    protected function getAccessTokenBody(array $params)
    {
        return $this->buildQueryString($params);
    }

    /**
     * Returns the full URL to use when requesting an access token.
     *
     * @param array $params Query parameters
     * @return string
     */
    protected function getAccessTokenUrl(array $params)
    {
        $url = $this->getBaseAccessTokenUrl($params);

        if ($this->getAccessTokenMethod() === self::METHOD_GET) {
            $query = $this->getAccessTokenQuery($params);
            return $this->appendQuery($url, $query);
        }

        return $url;
    }

    public function getAuthorizationUrl(array $options = [])
    {
        $base   = $this->getBaseAuthorizationUrl();
        $params = $this->getAuthorizationParameters($options);
        $query  = $this->getAuthorizationQuery($params);

        return $this->appendQuery($base, $query);
    }

    function milliseconds() {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }

    protected function getRandomState($length = 32)
    {
        // Converting bytes to hex will always double length. Hence, we can reduce
        // the amount of bytes by half to produce the correct length.
        return $this->milliseconds();
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function getAuthorizationQuery(array $params)
    {
        return $this->buildQueryString($params);
    }

    protected function buildQueryString(array $params)
    {
        return http_build_query($params, null, '&', \PHP_QUERY_RFC3986);
    }

    protected function appendQuery($url, $query)
    {
        $query = trim($query, '?&');

        if ($query) {
            $glue = strstr($url, '?') === false ? '?' : '&';
            return $url . $glue . $query;
        }

        return $url;
    }

    protected function getAuthorizationParameters(array $options){

        if (empty($options['state'])) {
            $options['state'] = $this->getRandomState();
        }

        if (empty($options['scope'])) {
            $options['scope'] = $this->getDefaultScopes();
        }

        if (empty($options['response_type'])) {
            $options['response_type'] = 'code';
        }

        $options += [
            'approval_prompt' => 'auto'
        ];

        if (is_array($options['scope'])) {
            $separator = $this->getScopeSeparator();
            $options['scope'] = implode($separator, $options['scope']);
        }

        // Store the state as it may need to be accessed later on.
        $this->state = $options['state'];

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->redirectUri;
        }

        $options['client_id'] = $this->clientId;

        return $options;
    }

    public function getHttpClient()
    {
        $client = new Client();
        return $client;
    }

    public function getResourceOwner(AccessToken $token)
    {
        $response = $this->fetchResourceOwnerDetails($token);

        return $this->createResourceOwner($response, $token);
    }

    public function getResponse(RequestInterface $request)
    {
        return $this->getHttpClient()->send($request);
    }

    public function getParsedResponse(RequestInterface $request)
    {
        try {

            $response = $this->getResponse($request);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        $parsed = $this->parseResponse($response);

        $this->checkResponse($response, $parsed);

        return $parsed;
    }


    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            return CidaasIdentityProviderException::fromResponse(
                $response,
                $data['error'] ?: $response->getReasonPhrase()
            );
        }
        return $response;
    }


    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        
        $url = $this->getResourceOwnerDetailsUrl($token);

        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

        return $this->getParsedResponse($request);
    }

    public function getAuthenticatedRequest($method, $url, $token, array $options = [])
    {
        return $this->createRequest($method, $url, $token, $options);
    }

    protected function createRequest($method, $url, $token, array $options)
    {
        $defaults = [
            'headers' => $this->getHeaders($token),
        ];

        $options = array_merge_recursive($defaults, $options);
        
        $factory = $this->getHttpClient();

        return $factory->createRequest($method, $url, $options);
    }

    public function getHeaders($token = null)
    {
        if ($token) {
            return array_merge(
                $this->getDefaultHeaders(),
                $this->getAuthorizationHeaders($token)
            );
        }

        return $this->getDefaultHeaders();
    }

    protected function getDefaultHeaders()
    {
        return [];
    }

    protected function getAuthorizationHeaders($token = null)
    {
        return ["access_token"=>$token];
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new CidaasResourceOwner($response);
    }


    protected function parseResponse(ResponseInterface $response)
    {
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);

        if (strpos($type, 'urlencoded') !== false) {
            parse_str($content, $parsed);
            return $parsed;
        }

        // Attempt to parse the string as JSON regardless of content type,
        // since some providers use non-standard content types. Only throw an
        // exception if the JSON could not be parsed when it was expected to.
        try {
            return $this->parseJson($content);
        } catch (UnexpectedValueException $e) {
            if (strpos($type, 'json') !== false) {
                throw $e;
            }

            if ($response->getStatusCode() == 500) {
                throw new UnexpectedValueException(
                    'An OAuth server error was encountered that did not contain a JSON body',
                    0,
                    $e
                );
            }

            return $content;
        }
    }

    /**
     * Attempts to parse a JSON response.
     *
     * @param  string $content JSON content from response body
     * @return array Parsed JSON data
     * @throws UnexpectedValueException if the content could not be parsed
     */
    protected function parseJson($content)
    {
        $content = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException(sprintf(
                "Failed to parse JSON response: %s"
            ));
        }

        return $content;
    }


    /**
     * Returns the content type header of a response.
     *
     * @param  ResponseInterface $response
     * @return string Semi-colon separated join of content-type headers.
     */
    protected function getContentType(ResponseInterface $response)
    {
        return join(';', (array) $response->getHeader('content-type'));
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  mixed $grant
     * @param  array $options
     * @return AccessToken
     */
    public function getAccessToken($grant, array $options = [])
    {


        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
        ];

        $params   = $this->prepareRequestParameters($grant,$params, $options);
        $request  = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared);

        return $token;
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param  array $response
     * @return AccessToken
     */
    protected function createAccessToken(array $response)
    {
        return new AccessToken($response);
    }


    /**
     * Returns the key used in the access token response to identify the resource owner.
     *
     * @return string|null Resource owner identifier key
     */
    protected function getAccessTokenResourceOwnerId()
    {
        return static::ACCESS_TOKEN_RESOURCE_OWNER_ID;
    }

    /**
     * Prepares an parsed access token response for a grant.
     *
     * Custom mapping of expiration, etc should be done here. Always call the
     * parent method when overloading this method.
     *
     * @param  mixed $result
     * @return array
     */
    protected function prepareAccessTokenResponse(array $result)
    {
        if ($this->getAccessTokenResourceOwnerId() !== null) {
            $result['resource_owner_id'] = $this->getValueByKey(
                $result,
                $this->getAccessTokenResourceOwnerId()
            );
        }
        return $result;
    }

    /**
     * Builds request options used for requesting an access token.
     *
     * @param  array $params
     * @return array
     */
    protected function getAccessTokenOptions(array $params)
    {
        $options = ['headers' => ['content-type' => 'application/x-www-form-urlencoded']];

        if ($this->getAccessTokenMethod() === self::METHOD_POST) {
            $options['body'] = $this->getAccessTokenBody($params);
        }

        return $options;
    }

    /**
     * Returns a PSR-7 request instance that is not authenticated.
     *
     * @param  string $method
     * @param  string $url
     * @param  array $options
     * @return RequestInterface
     */
    public function getRequest($method, $url, array $options = [])
    {
        return $this->createRequest($method, $url, null, $options);
    }

    /**
     * Returns a prepared request for requesting an access token.
     *
     * @param array $params Query string parameters
     * @return RequestInterface
     */
    protected function getAccessTokenRequest(array $params)
    {
        $method  = $this->getAccessTokenMethod();
        $url     = $this->getAccessTokenUrl($params);
        $options = $this->getAccessTokenOptions($params);

        return $this->getRequest($method, $url, $options);
    }

    /**
     * Returns a list of all required request parameters.
     *
     * @return array
     */
     protected function getRequiredRequestParameters(){
         return [];
     }

    /**
     * Prepares an access token request's parameters by checking that all
     * required parameters are set, then merging with any given defaults.
     *
     * @param  array $defaults
     * @param  array $options
     * @return array
     */
    public function prepareRequestParameters($grant,array $defaults, array $options)
    {
        $defaults['grant_type'] = $grant;

        $required = $this->getRequiredRequestParameters();
        $provided = array_merge($defaults, $options);

        $this->checkRequiredParameters($required, $provided);

        return $provided;
    }


    public function getUserInfoById(AccessToken $token,$user_id){
        $client = $this->getHttpClient();

        $result = $client->get($this->getManagerUserInfo()."/".$user_id,[
            "headers"=>[
                "Content-Type" => "application/json",
                "access_token"=>$token->getToken()
            ]
        ]);

        return $this->createResourceOwner($this->parseResponse($result), $token);

    }


    public function isTokenExpired($access_token){
        $client = $this->getHttpClient();

        $result = $client->get($this->getValidateTokenUrl(),[
            "headers"=>[
                "Content-Type" => "application/json",
                "access_token"=>$access_token
            ]
        ]);

        if($result->getBody()->getContents() == "true"){
            return false;
        }

        return true;
    }

    public  function  validateToken($parsedInfo=[],$roles=[],$scopes=[]){
        $access_token_key = "access_token";
        if(!isset($parsedInfo[$access_token_key])){
            return [
                "error"=>"Access denied for this resource",
                "status_code"=>401,
                "message" => "Access token cannot be null"
            ];
        }

        if(!isset($parsedInfo["headers"])){
            return [
                "error"=>"Access denied for this resource",
                "status_code"=>401,
                "message" => "Headers cannot be null"
            ];
        }

        $headers = $parsedInfo["headers"];


        $ipAddress = "";

        if(isset($headers["x-forwarded-for"])){
            $ips = explode(" ",$headers["x-forwarded-for"]);
            if(sizeof($ips)>0){
                $ipAddress = $ips[0];
            }

        }


        $host = "";
        if(isset($headers["X-Forwarded-Host"])){
            $host = $headers["X-Forwarded-Host"];
        }

        $acceptLanguage = "";

        if(isset($headers["Accept-Language"])){
            $acceptLanguage = $headers["Accept-Language"];
        }

        $userAgent = "";

        if(isset($headers["user-agent"])){
            $userAgent = $headers["user-agent"];
        }

        $referrer = "";

        if(isset($headers["referrer"])){
            $referrer = $headers["referrer"];
        }


        $dataToSend = [
            "accessToken"=>$parsedInfo[$access_token_key],
            "userId"=>null,
            "clientId"=>null,
            "referrer"=>$referrer,
            "ipAddress"=>$ipAddress,
            "host"=>$host,
            "acceptLanguage"=>$acceptLanguage,
            "userAgent"=>$userAgent,
            "requestURL"=>isset($parsedInfo["requestURL"])?$parsedInfo["requestURL"]:"",
            "success"=>false,
            "requestedScopes"=>"",
            "requestedRoles"=>"",
            "createdTime"=>date_create('now')->format('Y-m-d\TH:i:sO'),
            "requestInfo"=>$headers
        ];



        if($roles!=null){
            $dataToSend["requestedRoles"] =  implode(",",$roles);
        }

        if($scopes!=null){
            $dataToSend["requestedScopes"] =  implode(" ",$scopes);
        }


        $client = $this->getHttpClient();

        $result = $client->post($this->getTokenInfoUrl(),[
            "json"=>$dataToSend,
            "headers"=>[
                "Content-Type" => "application/json",
                "access_token"=>$parsedInfo[$access_token_key]
            ]
        ]);

        if($result->getStatusCode() == 200) {
            $token_check_response = json_decode($result->getBody()->getContents());

            return [
                "data"=>$token_check_response,
                "status_code"=>200
            ];

        }

        return [
            "error"=>"Access denied for this resource",
            "status_code"=>401
        ];
    }

}

