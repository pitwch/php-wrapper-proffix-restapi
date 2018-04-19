<?php

namespace RestAPIWrapperProffix;

use Httpful\Request as PHPHttpful;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use RestAPIWrapperProffix\Exception\HttpException;

/**
 * RestAPIWrapperProffix wrapper class.
 */
class RestAPIWrapperProffix
{
    /**
     * RestAPIWrapperProffix api configuration.
     * @var array
     */
    protected static $config = array();

    /**
     * Monolog logger object for basic logging.
     */
    protected static $logger;

    /**
     * Constructor for Wrapper.
     */
    public function __construct($api_user, $api_password, $api_database, $api_url, $api_modules, $api_key = '', $logpath = '', $log = true)
    {
        $configerror = [];

        if (empty(self::$config)) {
            $logpath = empty($logpath) ? __DIR__ . '/../../log/logs.log' : $logpath;

            self::$config = array(
                'api_user' => $api_user,
                'api_password' => $api_password,
                'api_database' => $api_database,
                'api_modules' => $api_modules,
                'log_path' => $logpath,
                'api_url' => $api_url,
                'api_key' => $api_key,
                'api_log' => $log
            );
        }

        if (empty(self::$logger)) {
            // create a log channel
            self::$logger = new Logger('ProffixRestAPIWrapperLogger');
            self::$logger->pushHandler(new StreamHandler(self::$config['log_path']));
        }
    }

    public function pxErrorHandler($response)
    {
        $error = json_decode($response);

        return $error->Message;
    }


    /**
     * GET - Request to PROFFIX REST-API with Auto-Login / Logout
     * @param $endpoint
     * @return mixed
     * @throws HttpException
     */
    public function Get($endpoint,$filter = '')
    {

        $endpoint = self::$config['api_url'] . $endpoint;
        $user = self::$config['api_user'];
        $filterquery = urldecode($filter);

        $pxsessionid = $this->login();

        $response = PHPHttpful::get($endpoint.$filterquery)
            ->addHeader('PxSessionId', $pxsessionid)
            ->expectsJson()
            ->send();


        if ($response->code != 200) {
            $this->logout($pxsessionid);
            if (self::$config['api_log']) {
                self::$logger->info('GET - Request failed: ', ['context' => array(
                    'pxSessionId' => $pxsessionid,
                    'Endpoint' => $endpoint,
                    'Benutzer' => $user,
                    'Message' => $response->body,
                    "Status" => $response->code
                )]);
            }
            return $response;
        } else
            $this->logout($pxsessionid);

        return $response->body;

    }


    /**
     * Login for PROFFIX REST-API
     * @return mixed
     * @throws HttpException
     */
    public function login()
    {
        $body = $this->getLoginJson();
        $url = self::$config['api_url'];
        $endpoint = $url . "PRO/Login";
        $user = self::$config['api_user'];

        $response = PHPHttpful::post($endpoint)
            ->sendsJson()
            ->body(json_encode($body))
            ->send();

        //check url is valid and accessable
        $status = $response->code;
        if ($status != 201) {
            throw new HttpException($this->getHttpStatusMessage($status), $status);
        }
        if ($status == 201) {
            $header = $response->headers->toArray();
            $pxsessionid = $header['pxsessionid'];
            if (self::$config['api_log']) {
                self::$logger->info('Login successful: ', ['context' => array(
                    'pxSessionId' => $pxsessionid,
                    'Endpoint' => $endpoint,
                    'Benutzer' => $user
                )]);
            }
            return $pxsessionid;
        }
        return $response;
    }


    /**
     * Create Login Body as JSON from Config
     * @return array
     */
    private function getLoginJson()
    {
        $api_user = self::$config['api_user'];
        $api_password = self::$config['api_password'];
        $api_database = self::$config['api_database'];
        $api_modules = self::$config['api_modules'];

        $loginJson = Array("Benutzer" => $api_user,
            "Passwort" => $api_password,
            "Datenbank" => Array("Name" => $api_database),
            "Module" => explode(",", $api_modules)
        );

        return $loginJson;
    }

    /**
     * Logout for PROFFIX REST-API
     * @param $pxsessionid
     * @return \Httpful\Response
     * @throws HttpException
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    public function logout($pxsessionid)
    {
        $url = self::$config['api_url'];
        $endpoint = $url . "PRO/Login";
        $user = self::$config['api_user'];

        $response = PHPHttpful::delete($endpoint)
            ->addHeader('PxSessionId', $pxsessionid)
            ->send();

        //check url is valid and accessable
        $status = $response->code;
        if ($status != 204) {
            if (self::$config['api_log']) {
                self::$logger->error('Logout failed: ', ['context' => array(
                    'pxSessionId' => $pxsessionid,
                    'Endpoint' => $endpoint,
                    'Benutzer' => $user,
                    'Message' => $response->body,
                    "Status" => $response->code
                )]);
            }
            throw new HttpException($this->getHttpStatusMessage($status), $status);
        }
        if ($status == 204) {
            if (self::$config['api_log']) {
                self::$logger->info('Logout successful: ', ['context' => array(
                    'pxSessionId' => $pxsessionid,
                    'Endpoint' => $endpoint,
                    'Benutzer' => $user
                )]);
            }
            return $response;
        }
        return $response;
    }

    /**
     * POST - Request to PROFFIX REST-API with Auto-Login / Logout
     * @param $endpoint
     * @return mixed
     * @throws HttpException
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    public function Create($endpoint,$post)
    {

        $endpoint = self::$config['api_url'] . $endpoint;
        $user = self::$config['api_user'];


        $pxsessionid = $this->login();

        $response = PHPHttpful::post($endpoint)
            ->addHeader('PxSessionId', $pxsessionid)
            ->sendsJson()
            ->body($post)
            ->send();


        if ($response->code != 200) {
            $this->logout($pxsessionid);
            if (self::$config['api_log']) {
                self::$logger->error('POST - Request failed: ', ['context' => array(
                    'pxSessionId' => $pxsessionid,
                    'Endpoint' => $endpoint,
                    'Benutzer' => $user,
                    'Message' => $response->body,
                    "Status" => $response->code
                )]);
            }
            echo $response;
        } else
            $this->logout($pxsessionid);
        if (self::$config['api_log']) {
            self::$logger->info('POST - Request successful: ', ['context' => array(
                'pxSessionId' => $pxsessionid,
                'Endpoint' => $endpoint,
                'Benutzer' => $user,
                'Message' => $response->body,
                "Status" => $response->code
            )]);
        }
        return $response->body;

    }

    /**
     * PUT - Request to PROFFIX REST-API with Auto-Login / Logout
     * @param $endpoint
     * @return mixed
     * @throws HttpException
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    public function Update($endpoint)
    {

        $endpoint = self::$config['api_url'] . $endpoint;
        $user = self::$config['api_user'];


        $pxsessionid = $this->login();

        $response = PHPHttpful::put($endpoint)
            ->addHeader('PxSessionId', $pxsessionid)
            ->sendsJson()
            ->send();


        if ($response->code != 200) {
            $this->logout($pxsessionid);
            if (self::$config['api_log']) {
                self::$logger->error('PUT - Request failed: ', ['context' => array(
                    'pxSessionId' => $pxsessionid,
                    'Endpoint' => $endpoint,
                    'Benutzer' => $user,
                    'Message' => $response->body,
                    "Status" => $response->code
                )]);
            }
            echo $response;
        } else
            $this->logout($pxsessionid);
        if (self::$config['api_log']) {
            self::$logger->info('PUT - Request successful: ', ['context' => array(
                'pxSessionId' => $pxsessionid,
                'Endpoint' => $endpoint,
                'Benutzer' => $user,
                'Message' => $response->body,
                "Status" => $response->code
            )]);
        }

        return $response->body;

    }

    /**
     * Query Info Endpoint PROFFIX REST-API
     * @param string $key
     * @return mixed
     */
    public function GetInfo($key = '')
    {
        $missing = 'API-Key missing';


        if (empty($key)) {
            $apikey = self::$config['api_key'];

        } else
            $apikey = $key;


        if (!empty($apikey)) {
            if (self::$config['api_log']) {
                self::$logger->error('Info Request failed', ['context' => array(
                    'Endpoint' => 'PRO/Info',
                    'Message' => $missing
                )]);
            }

            $endpoint = self::$config['api_url'] . "PRO/Info?Key=" . $apikey;


            $response = PHPHttpful::get($endpoint)
                ->expectsJson()
                ->send();


            if ($response->code != 200) {
                if (self::$config['api_log']) {
                    self::$logger->error('Info Request failed: ', ['context' => array(
                        'Endpoint' => $endpoint,
                        'Message' => $response->body,
                        "Status" => $response->code
                    )]);
                }
                echo($response);
            } else
                if (self::$config['api_log']) {
                    self::$logger->info('Info - Request successful: ', ['context' => array(
                        'Version' => $response->body->Version,
                        'NeuesteVersion' => $response->body->NeuesteVersion,
                        'ServerZeit' => $response->body->ServerZeit,
                        'Endpoint' => $endpoint,
                        'Message' => $response->body,
                        "Status" => $response->code
                    )]);
                }

            return $response->body;

        } else
            echo $missing;
    }

    /**
     * Get available Databases from PROFFIX REST-API
     * @param string $key
     * @return mixed
     */
    public function GetDatabases($key = '')
    {
        $missing = 'API-Key missing';


        if (empty($key)) {
            $apikey = self::$config['api_key'];

        } else
            $apikey = $key;


        if (!empty($apikey)) {
            if (self::$config['api_log']) {
                self::$logger->error('Info Request failed', ['context' => array(
                    'Endpoint' => 'PRO/Datenbank',
                    'Message' => $missing
                )]);
            }

            $endpoint = self::$config['api_url'] . "PRO/Datenbank?Key=" . $apikey;


            $response = PHPHttpful::get($endpoint)
                ->expectsJson()
                ->send();


            if ($response->code != 200) {
                if (self::$config['api_log']) {
                    self::$logger->error('Info Request failed: ', ['context' => array(
                        'Endpoint' => $endpoint,
                        'Message' => $response->body,
                        "Status" => $response->code
                    )]);
                }
                echo($response);
            } else
                if (self::$config['api_log']) {
                    self::$logger->info('Info - Request successful: ', ['context' => array(
                        'Endpoint' => $endpoint,
                        'Message' => $response->body,
                        "Status" => $response->code
                    )]);
                }

            return $response->body;

        } else
            echo $missing;
    }

}
