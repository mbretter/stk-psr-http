<?php

namespace StkTest\Psr\Http\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use Stk\Psr\Http\Middleware\Range;
use Zend\Diactoros\ServerRequestFactory;

class RangeTest extends TestCase
{
    /** @var Range */
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

    /** @var null|stdClass */
    protected $range;

    protected function setUp(): void
    {
        $this->request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => '192.168.200.5',
            'HTTP_HOST'   => 'foo.com',
            'HTTP_RANGE'  => 'items=1-10'
        ]);

        $this->body = $this->createMock(StreamInterface::class);

        $this->responseHandler = $this->createMock(RequestHandlerInterface::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->response->method('getBody')->willReturn($this->body);
        $this->requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->requestHandler->method('handle')->will($this->returnCallback(function ($request) {
            /** @var ServerRequestInterface $request */
            $this->range = $request->getAttribute('range');

            return $this->response;
        }));

        $this->middleware = new Range();
    }

    public function testProcess()
    {
        $this->middleware->process($this->request, $this->requestHandler);
        $this->assertEquals((object)['start' => 1, 'stop' => 10], $this->range);
    }

    public function testProcessWithoutRangeHeader()
    {
        $request = $this->request->withoutHeader('Range');
        $this->middleware->process($request, $this->requestHandler);
        $this->assertNull($this->range);
    }

    public function testProcessWithMalformedRangeHeader()
    {
        $request = $this->request->withHeader('Range', 'sdfsdsdf=122x-2s');
        $this->middleware->process($request, $this->requestHandler);
        $this->assertNull($this->range);
    }

    public function testInvoke()
    {
        $range = null;
        $this->middleware->__invoke($this->request, $this->response, function ($req, $resp) use (&$range) {
            /** @var ServerRequestInterface $req */
            $range = $req->getAttribute('range');

            return $resp;
        });
        $this->assertEquals((object)['start' => 1, 'stop' => 10], $range);
    }

    public function testInvokeWithoutNext()
    {
        $resp = $this->middleware->__invoke($this->request, $this->response, null);
        $this->assertSame($resp, $this->response);
    }

}