<?php

namespace Stk\Psr\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stk\Service\AcceptLanguageInterface;

/**
 * catches Accept-Language header from the request
 * parse and set an attribute respectively
 */
class AcceptLanguage implements MiddlewareInterface
{
    protected ?AcceptLanguageInterface $service = null;

    public function __construct(AcceptLanguageInterface $service = null)
    {
        $this->service = $service;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null): ResponseInterface
    {
        if ($next === null) {
            return $response;
        }

        $request = $this->handle($request);

        return $next($request, $response);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->handle($request);

        return $handler->handle($request);
    }

    protected function handle(ServerRequestInterface $request): ServerRequestInterface
    {
        $hdr = $request->getHeaderLine('Accept-Language');

        $language  = null;
        $languages = [];
        if (strlen($hdr)) {
            foreach (explode(',', $hdr) as $langQuality) {
                $lq    = explode(';', $langQuality, 2);
                $lq[0] = trim($lq[0]);
                if (count($lq) === 1) {
                    $lq[1] = 1.0;
                } else {
                    $lq[1] = (float) substr($lq[1], strpos($lq[1], '=') + 1);
                }

                $languages[] = $lq;
            }

            if (count($languages) > 0) {
                uasort($languages, fn($a, $b) => $b[1] <=> $a[1]);
                $languages = array_values($languages);
                $language = $languages[0][0];
            }
        }

        if ($this->service !== null) {
            $this->service->setAcceptLanguage($language);
            $this->service->setAcceptLanguages($languages);
        }

        return $request->withAttribute('language', $language)->withAttribute('languages', $languages);
    }

}
