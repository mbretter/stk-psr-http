<?php

namespace Stk\Psr\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * catches Range header from the request
 * parse and set an attribute with start and stop value
 */
class Range
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
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
        $range = $request->getHeaderLine('Range');

        $r = null;
        if (preg_match('/items=(\d+)-(\d+)/', $range, $matches)) {
            $r        = new stdClass();
            $r->start = (int)$matches[1];
            $r->stop  = (int)$matches[2];
        }

        return $request->withAttribute('range', $r);
    }
}
