<?php

namespace StkTest\Psr\Http\Middleware;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stk\Psr\Http\Middleware\JsonRest;

class JsonRestTest extends TestCase
{
    /** @var JsonRest */
    protected $middleware;

    /** @var ServerRequestInterface */
    protected $request;

    /** @var MockObject|RequestHandlerInterface */
    protected $requestHandler;

    /** @var MockObject|ResponseInterface */
    protected $responseHandler;

    /** @var MockObject|ResponseInterface */
    protected $response;

    /** @var MockObject|StreamInterface */
    protected $body;

    protected function setUp(): void
    {
        $this->request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.200.5',
            'HTTP_HOST'   => 'foo.com'
        ]);

        $this->body = $this->createMock(StreamInterface::class);

        $this->responseHandler = $this->createMock(RequestHandlerInterface::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->response->method('getBody')->willReturn($this->body);
        $this->requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->requestHandler->method('handle')->willReturn($this->response);

        $this->middleware = new JsonRest();
    }

    public function testProcess()
    {
        $this->response->expects($this->exactly(4))->method('withHeader')->withConsecutive(
            ['Content-Type', 'application/json'],
            ['Expires', 'Tue, 10 Jul 1997 01:00:00 GMT'],
            ['Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0'],
            ['Pragma', 'no-cache']
        )->willReturn($this->response);
        $this->middleware->process($this->request, $this->requestHandler);
    }

    public function testInvoke()
    {
        $this->response->expects($this->exactly(4))->method('withHeader')->withConsecutive(
            ['Content-Type', 'application/json'],
            ['Expires', 'Tue, 10 Jul 1997 01:00:00 GMT'],
            ['Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0'],
            ['Pragma', 'no-cache']
        )->willReturn($this->response);
        $this->middleware->__invoke($this->request, $this->response, function ($req, $resp) {
            return $resp;
        });
    }

    public function testInvokeWithoutNext()
    {
        $resp = $this->middleware->__invoke($this->request, $this->response, null);
        $this->assertSame($resp, $this->response);
    }

}