<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Closure;
use Fyre\Auth\Auth;
use Fyre\Error\Exceptions\NotFoundException;
use Fyre\Middleware\Middleware;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

/**
 * UnauthenticatedMiddleware
 */
class UnauthenticatedMiddleware extends Middleware
{
    protected Auth $auth;

    /**
     * New UnauthenticatedMiddleware constructor.
     *
     * @param Auth $auth The Auth.
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @param Closure $next The next handler.
     * @return ClientResponse The ClientResponse.
     *
     * @throws NotFoundException is the user is authenticated and the request accepts JSON.
     */
    public function handle(ServerRequest $request, Closure $next): ClientResponse
    {
        if (!$this->auth->isLoggedIn()) {
            return $next($request);
        }

        throw new NotFoundException();
    }
}
