<?php


namespace Pitwch\RestAPIWrapperProffix\HttpClient;

/**
 * Class Options
 *
 * @package Pitwch\RestAPIWrapperProffix\HttpClient
 */
class Options
{

    const VERSION = 'V4';

    const TIMEOUT = 15;

    const PX_API_PREFIX = '/pxapi/';

    /**
     * User agent string
     */
    const USER_AGENT = 'php-wrapper-proffix-restapi';

    /**
     * Login endpoint
     */
    const LOGIN_ENDPOINT = 'PRO/Login';

    /**
     * Endpoints that do not require login
     */
    const NO_LOGIN = array('PRO/Info', 'PRO/Datenbank');

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var SessionCache|null
     */
    private $sessionCache = null;

    /**
     * Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return isset($this->options['version']) ? $this->options['version'] : self::VERSION;
    }

    /**
     * @return bool
     */
    public function verifySsl()
    {
        return isset($this->options['verify_ssl']) ? (bool)$this->options['verify_ssl'] : true;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return isset($this->options['timeout']) ? (int)$this->options['timeout'] : self::TIMEOUT;
    }

    /**
     * @return string
     */
    public function apiPrefix()
    {
        return isset($this->options['api_prefix']) ? $this->options['api_prefix'] : self::PX_API_PREFIX;
    }

    /**
     * @return string
     */
    public function getLoginEndpoint()
    {
        return isset($this->options['login_endpoint']) ? $this->options['login_endpoint'] : self::LOGIN_ENDPOINT;

    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return isset($this->options['key']) ? $this->options['key'] : '';

    }

    /**
     * @return string
     */
    public function userAgent()
    {
        return isset($this->options['user_agent']) ? $this->options['user_agent'] : self::USER_AGENT;
    }

    public function getFollowRedirects()
    {
        return isset($this->options['follow_redirects']) ? (bool)$this->options['follow_redirects'] : false;
    }

    /**
     * Check if session caching is enabled
     *
     * @return bool
     */
    public function isSessionCachingEnabled()
    {
        return isset($this->options['enable_session_caching']) ? (bool)$this->options['enable_session_caching'] : true;
    }

    /**
     * Set the session cache instance
     *
     * @param SessionCache $sessionCache
     */
    public function setSessionCache(SessionCache $sessionCache)
    {
        $this->sessionCache = $sessionCache;
    }

    /**
     * Get the session cache instance
     *
     * @return SessionCache|null
     */
    public function getSessionCache()
    {
        return $this->sessionCache;
    }
}
