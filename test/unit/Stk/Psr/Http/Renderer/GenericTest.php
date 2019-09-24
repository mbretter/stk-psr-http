<?php

namespace StkTest\Psr\Http\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use Stk\Psr\Http\Renderer\Generic;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

class GenericTest extends TestCase
{
    /** @var Generic */
    protected $renderer;

    /** @var ServerRequestInterface */
    protected $request;

    /** @var MockObject|ResponseInterface */
    protected $response;

    protected function setUp(): void
    {
        $this->request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.200.5',
            'HTTP_HOST'   => 'foo.com'
        ]);

        $this->response = new Response();

        $this->renderer = new Generic();
    }

    public function testInvoke()
    {
        $response = $this->renderer->__invoke($this->request, $this->response, 'something');
        $this->assertEquals('something', $response->getBody());
    }

}