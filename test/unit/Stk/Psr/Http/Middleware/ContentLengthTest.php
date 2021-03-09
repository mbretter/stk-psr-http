<?php

namespace StkTest\Psr\Http\Middleware;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stk\Psr\Http\Middleware\ContentLength;

class ContentLengthTest extends TestCase
{
    /** @var ContentLength */
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

        $this->middleware = new ContentLength();
    }

    public function testProcess()
    {
        $this->body->method('getSize')->willReturn(100);
        $this->response->expects($this->once())->method('withHeader')->with('Content-Length', 100)
            ->willReturn($this->response);
        $this->middleware->process($this->request, $this->requestHandler);
    }

    public function testProcessEmpty()
    {
        $this->body->method('getSize')->willReturn(null);
        $this->response->expects($this->never())->method('withHeader')->willReturn($this->response);
        $this->middleware->process($this->request, $this->requestHandler);
    }

    public function testProcessWithExistingHeader()
    {
        $this->body->method('getSize')->willReturn(100);
        $this->response->expects($this->never())->method('withHeader')->willReturn($this->response);
        $this->response->expects($this->once())->method('hasHeader')->with('Content-Length')->willReturn(true);
        $this->middleware->process($this->request, $this->requestHandler);
    }

    public function testInvoke()
    {
        $this->body->method('getSize')->willReturn(100);
        $this->response->expects($this->once())->method('withHeader')->with('Content-Length', 100)
            ->willReturn($this->response);
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