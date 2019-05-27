<?php

namespace Stk\Psr\Http\Renderer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Json
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $data
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $data)
    {
        if (is_array($data) && !count($data) && !$response->hasHeader('Content-Range')) {
            $response = $response->withHeader('Content-Range', "items 0-0/0");
        }

        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')
            ->withHeader('Expires', 'Tue, 10 Jul 1997 01:00:00 GMT')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Pragma', 'no-cache');
    }
}