<?php

namespace Stk\Psr\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NoContent implements MiddlewareInterface
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

        $response = $next($request, $response);

        return $this->handle($response);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $this->handle($response);
    }

    protected function handle(ResponseInterface $response): ResponseInterface
    {
        if ($response->getStatusCode() == 200 && $response->getBody()->getSize() == 0) {
            $response = $response->withStatus(204);
        }

        return $response;
    }
}
