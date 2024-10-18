<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Error\Exceptions\UnauthorizedException;
use Fyre\Middleware\Middleware;
use Fyre\Middleware\RequestHandler;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

/**
 * AuthenticatedMiddleware
 */
class AuthenticatedMiddleware extends Middleware
{
    /**
     * Process a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @param RequestHandler $handler The RequestHandler.
     * @return ClientResponse The ClientResponse.
     *
     * @throws UnauthorizedException is the user is not authenticated.
     */
    public function process(ServerRequest $request, RequestHandler $handler): ClientResponse
    {
        if (!Auth::instance()->isLoggedIn()) {
            throw new UnauthorizedException();
        }

        return $handler->handle($request);
    }
}
