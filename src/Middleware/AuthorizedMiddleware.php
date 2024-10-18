<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Fyre\Auth\Access;
use Fyre\Middleware\Middleware;
use Fyre\Middleware\RequestHandler;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

/**
 * AuthorizedMiddleware
 */
class AuthorizedMiddleware extends Middleware
{
    /**
     * Process a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @param RequestHandler $handler The RequestHandler.
     * @param mixed ...$args The arguments for the access rule.
     * @return ClientResponse The ClientResponse.
     */
    public function process(ServerRequest $request, RequestHandler $handler, mixed ...$args): ClientResponse
    {
        Access::authorize(...$args);

        return $handler->handle($request);
    }
}
