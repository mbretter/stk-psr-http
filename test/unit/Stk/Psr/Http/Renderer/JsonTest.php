<?php

namespace StkTest\Psr\Http\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stk\Psr\Http\Renderer\Json;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response;

class JsonTest extends TestCase
{
    /** @var Json */
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

        $this->renderer = new Json();
    }

    public function testInvoke()
    {
        $response = $this->renderer->__invoke($this->request, $this->response, ['foo' => 'bar']);
        $this->assertEquals('{"foo":"bar"}', (string) $response->getBody());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testInvokeWithEmptyArray()
    {
        $response = $this->renderer->__invoke($this->request, $this->response, []);
        $this->assertEquals('[]', (string) $response->getBody());
        $this->assertEquals('items 0-0/0', $response->getHeaderLine('Content-Range'));
    }

    public function testInvokeWithEmptyArrayWithRangeHeader()
    {
        $response = $this->response->withHeader('Content-Range', 'items 1-1/1');
        $response = $this->renderer->__invoke($this->request, $response, []);
        $this->assertEquals('[]', (string) $response->getBody());
        $this->assertEquals('items 1-1/1', $response->getHeaderLine('Content-Range'));
    }
}