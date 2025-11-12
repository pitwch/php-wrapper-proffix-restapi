<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;

use Pitwch\RestAPIWrapperProffix\HttpClient\Request;
use Pitwch\RestAPIWrapperProffix\HttpClient\Response;


/**
 * Class HttpClientException
 *
 * @package Pitwch\RestAPIWrapperProffix\HttpClient
 */
class HttpClientException extends \Exception
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private ?Response $response;

    /**
     * @var array|null
     */
    private ?array $fieldErrors;


    /**
     * HttpClientException constructor.
     *
     * @param string   $message
     * @param int      $code
     * @param Request  $request
     * @param Response $response
     * @param array|null $fieldErrors
     */
    public function __construct($message, $code, Request $request, ?Response $response, ?array $fieldErrors = null)
    {
        parent::__construct($message, $code);

        $this->request  = $request;
        $this->response = $response;
        $this->fieldErrors = $fieldErrors;
    }


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get field-level validation errors
     *
     * @return array|null Array of field errors or null if none exist
     */
    public function getFieldErrors(): ?array
    {
        return $this->fieldErrors;
    }

    /**
     * Check if there are field-level validation errors
     *
     * @return bool
     */
    public function hasFieldErrors(): bool
    {
        return !empty($this->fieldErrors);
    }

    /**
     * Get a detailed error message including field-level errors
     *
     * @return string
     */
    public function getDetailedMessage(): string
    {
        $message = $this->getMessage();
        
        if ($this->hasFieldErrors()) {
            $message .= "\nField errors:";
            foreach ($this->fieldErrors as $fieldError) {
                $fieldName = $fieldError['Name'] ?? 'Unknown';
                $fieldMessage = $fieldError['Message'] ?? 'No message';
                $reason = $fieldError['Reason'] ?? '';
                
                $message .= "\n  - {$fieldName}: {$fieldMessage}";
                if (!empty($reason)) {
                    $message .= " (Reason: {$reason})";
                }
            }
        }
        
        return $message;
    }
}
