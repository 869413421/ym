<?php


namespace Core\Http;


class Response
{
    private $swooleResponse;

    private $body;

    private function __construct(\Swoole\Http\Response $response)
    {
        $this->swooleResponse = $response;
        $this->swooleResponse->header('Content-Type', 'text/plan;charset=utf8');
    }

    public static function init(\Swoole\Http\Response $response)
    {
        return new self($response);
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * 设置响应头
     * @param $key
     * @param $value
     */
    public function setHeader($key, $value)
    {
        $this->swooleResponse->header($key, $value);
    }

    /**
     * 设置响应码
     * @param int $httpCode
     */
    public function withHttpStatus(int $httpCode)
    {
        $this->swooleResponse->status($httpCode);
    }

    /**
     * 重定向
     * @param $url
     * @param int $httpCode
     */
    public function redirect($url, $httpCode = 302)
    {
        $this->withHttpStatus($httpCode);
        $this->setHeader('Location', $url);
    }

    /**
     * 响应到浏览器
     */
    public function end()
    {
        if (is_array($this->body) || is_object($this->body))
        {
            $this->setHeader('Content-Type', 'application/json');
            $this->body = json_encode($this->body, JSON_UNESCAPED_UNICODE);
        }
        $this->swooleResponse->write($this->body);
        $this->swooleResponse->end();
    }
}