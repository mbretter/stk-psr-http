<?php

namespace StkTest\Psr\Http\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stk\Psr\Http\Middleware\NoContent;
use Zend\Diactoros\ServerRequestFactory;

class NoContentTest extends TestCase
{
    /** @var NoContent */
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

        $this->middleware = new NoContent();
    }

    public function testProcess()
    {
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->expects($this->once())->method('withStatus')->with(204)->willReturnSelf();
        $this->middleware->process($this->request, $this->requestHandler);
    }

    public function testProcessWithErrorCode()
    {
        $this->response->method('getStatusCode')->willReturn(400);
        $this->response->expects($this->never())->method('withStatus')->with(204)->willReturnSelf();
        $this->middleware->process($this->request, $this->requestHandler);
    }

    public function testInvoke()
    {
        $this->response->method('getStatusCode')->willReturn(200);
        $this->response->expects($this->once())->method('withStatus')->with(204)->willReturnSelf();
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