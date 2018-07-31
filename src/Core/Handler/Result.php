<?php

namespace Cpa\TDS\Core\Handler;

use Cpa\TDS\Core\Response;

class Result
{

    /**
     * @var string
     */
    protected $response = '';

    /**
     * @var string
     */
    protected $criteria = '';

    /**
     * @var array
     */
    protected $tokens = array();

    /**
     * @var array
     */
    protected $cookies = array();

    /**
     * @param Response $response
     * @param string $criteria
     * @param array $tokens
     * @param array $cookies
     */
    public function __construct(Response $response, $criteria = '', $tokens = array(), $cookies = array())
    {
        $this->response = $response;
        $this->criteria = (string)$criteria;
        $this->tokens = (array)$tokens;
        $this->cookies = (array)$cookies;
    }

    /**
     * @return string
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function criteria()
    {
        return $this->criteria;
    }

    /**
     * @return array
     */
    public function tokens()
    {
        return $this->tokens;
    }

    /**
     * @return array
     */
    public function cookies()
    {
        return $this->cookies;
    }
}