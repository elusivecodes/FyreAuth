<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Closure;
use Fyre\Auth\Auth;
use Fyre\Error\Exceptions\UnauthorizedException;
use Fyre\Middleware\Middleware;
use Fyre\Server\ClientResponse;
use Fyre\Server\RedirectResponse;
use Fyre\Server\ServerRequest;

/**
 * AuthenticatedMiddleware
 */
class AuthenticatedMiddleware extends Middleware
{
    protected Auth $auth;

    /**
     * New AuthenticatedMiddleware constructor.
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
     * @throws UnauthorizedException is the user is not authenticated.
     */
    public function handle(ServerRequest $request, Closure $next): ClientResponse
    {
        if ($this->auth->isLoggedIn()) {
            return $next($request);
        }

        if ($request->negotiate('content', ['text/html', 'application/json']) === 'application/json') {
            throw new UnauthorizedException();
        }

        $redirect = $this->auth->getLoginUrl($request->getUri());

        return new RedirectResponse($redirect);
    }
}
