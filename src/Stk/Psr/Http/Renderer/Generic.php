<?php

namespace Stk\Psr\Http\Renderer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Generic
{

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param $string
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $string)
    {
        $response->getBody()->write($string);

        return $response;
    }
}
