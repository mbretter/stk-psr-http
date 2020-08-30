<?php

namespace Stk\Psr\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * catches Accept-Language header from the request
 * parse and set an attribute respectively
 */
class AcceptLanguage implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next): ResponseInterface
    {
        if (!$next) {
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
                    $lq[1] = 1;
                } else {
                    $lq[1] = (float) substr($lq[1], strpos($lq[1], '=') + 1);
                }

                $languages[] = $lq;
            }

            if (count($languages) > 0) {
                uasort($languages, fn($a, $b) => $a[0] <=> $a[1]);
                $language = $languages[0][0];
            }
        }

        return $request->withAttribute('language', $language)->withAttribute('languages', $languages);
    }
}
