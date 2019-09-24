<?php

namespace Stk\Psr\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonRest implements MiddlewareInterface
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

        $response = $next($request, $response);

        return $this->handle($response);
    }

    /**
     * {@inheritDoc}
     *
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $this->handle($response);
    }

    protected function handle(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('Content-Type', 'application/json')
            ->withHeader('Expires', 'Tue, 10 Jul 1997 01:00:00 GMT')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Pragma', 'no-cache');
    }
}
