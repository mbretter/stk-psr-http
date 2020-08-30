<?php

namespace StkTest\Psr\Http\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stk\Psr\Http\Middleware\AcceptLanguage;
use Stk\Service\AcceptLanguageInterface;
use Zend\Diactoros\ServerRequestFactory;

class AcceptLanguageTest extends TestCase
{
    protected AcceptLanguage $middleware;

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

    protected ?string $language;

    protected array $languages;

    protected function setUp(): void
    {
        $this->request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR'          => '192.168.200.5',
            'HTTP_HOST'            => 'foo.com',
            'HTTP_ACCEPT_LANGUAGE' => 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'
        ]);

        $this->body = $this->createMock(StreamInterface::class);

        $this->responseHandler = $this->createMock(RequestHandlerInterface::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->response->method('getBody')->willReturn($this->body);
        $this->requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->requestHandler->method('handle')->will($this->returnCallback(function ($request) {
            /** @var ServerRequestInterface $request */
            $this->language  = $request->getAttribute('language');
            $this->languages = $request->getAttribute('languages');

            return $this->response;
        }));

        $this->middleware = new AcceptLanguage();
    }

    public function testProcess()
    {
        $this->middleware->process($this->request, $this->requestHandler);
        $this->assertEquals('fr-CH', $this->language);
        $this->assertEquals([
            ['fr-CH', 1],
            ['fr', 0.9],
            ['en', 0.8],
            ['de', 0.7],
            ['*', 0.5],
        ], $this->languages);
    }

    public function testProcessSimple()
    {
        $request = $this->request->withHeader('Accept-Language', 'de');
        $this->middleware->process($request, $this->requestHandler);
        $this->assertEquals('de', $this->language);
        $this->assertEquals([
            ['de', 1]
        ], $this->languages);
    }

    public function testProcessWithoutRangeHeader()
    {
        $request = $this->request->withoutHeader('Accept-Language');
        $this->middleware->process($request, $this->requestHandler);
        $this->assertCount(0, $this->languages);
        $this->assertNull($this->language);
    }

    public function testProcessWithMalformedAcceptLanguageHeader()
    {
        $request = $this->request->withHeader('Accept-Language', '');
        $this->middleware->process($request, $this->requestHandler);
        $this->assertCount(0, $this->languages);
        $this->assertNull($this->language);
    }

    public function testInvoke()
    {
        $language  = null;
        $languages = null;
        $this->middleware->__invoke($this->request, $this->response,
            function ($req, $resp) use (&$language, &$languages) {
                /** @var ServerRequestInterface $req */
                $language  = $req->getAttribute('language');
                $languages = $req->getAttribute('languages');

                return $resp;
            });
        $this->assertEquals('fr-CH', $language);
        $this->assertEquals([
            ['fr-CH', 1],
            ['fr', 0.9],
            ['en', 0.8],
            ['de', 0.7],
            ['*', 0.5],
        ], $languages);
    }

    public function testWithService()
    {
        $service = new AcceptLanguageService();

        $middleware = new AcceptLanguage($service);
        $middleware->process($this->request, $this->requestHandler);
        $this->assertEquals('fr-CH', $service->language);
        $this->assertEquals([
            ['fr-CH', 1],
            ['fr', 0.9],
            ['en', 0.8],
            ['de', 0.7],
            ['*', 0.5],
        ], $service->languages);
    }

    public function testInvokeWithoutNext()
    {
        $resp = $this->middleware->__invoke($this->request, $this->response, null);
        $this->assertSame($resp, $this->response);
    }

}

class AcceptLanguageService implements AcceptLanguageInterface
{
    public ?string $language = null;

    public array $languages = [];

    public function setAcceptLanguage(string $language = null)
    {
        $this->language = $language;
    }

    public function setAcceptLanguages(array $languages = [])
    {
        $this->languages = $languages;
    }
}