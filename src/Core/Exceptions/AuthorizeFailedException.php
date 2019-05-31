<?php


namespace EasyPdd\Core\Exceptions;


class AuthorizeFailedException extends RuntimeException
{
    /**
     * Response body.
     *
     * @var array
     */
    public $body;
    /**
     * Constructor.
     *
     * @param string $message
     * @param array  $body
     */
    public function __construct($message, $body)
    {
        parent::__construct($message, -1);
        $this->body = $body;
    }
}