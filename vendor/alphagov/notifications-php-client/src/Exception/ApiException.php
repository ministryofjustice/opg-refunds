<?php
namespace Alphagov\Notifications\Exception;

use Psr\Http\Message\ResponseInterface;

class ApiException extends NotifyException {

    /**
     * @var ResponseInterface
     */
    private $response;


    public function __construct($message, $code, ResponseInterface $response) {

        $this->response = $response;

        parent::__construct( $message, $code );

    }

    /**
     * Returns the full response the lead to the exception.
     *
     * @return ResponseInterface
     */
    public function getResponse(){
        return $this->response;
    }

}
