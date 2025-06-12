<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Closure;
use Fyre\Auth\Auth;
use Fyre\Error\Exceptions\ForbiddenException;
use Fyre\Middleware\Middleware;
use Fyre\Server\ClientResponse;
use Fyre\Server\RedirectResponse;
use Fyre\Server\ServerRequest;

/**
 * AuthorizedMiddleware
 */
class AuthorizedMiddleware extends Middleware
{
    /**
     * New AuthorizedMiddleware constructor.
     *
     * @param Auth $auth The Auth.
     */
    public function __construct(
        protected Auth $auth
    ) {}

    /**
     * Handle a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @param Closure $next The next handler.
     * @param mixed ...$args The arguments for the access rule.
     * @return ClientResponse The ClientResponse.
     *
     * @throws ForbiddenException is the user is not authorized.
     */
    public function handle(ServerRequest $request, Closure $next, mixed ...$args): ClientResponse
    {
        if ($this->auth->access()->allows(...$args)) {
            return $next($request);
        }

        if (
            !$this->auth->isLoggedIn() &&
            $request->negotiate('content', ['text/html', 'application/json']) !== 'application/json'
        ) {
            $redirect = $this->auth->getLoginUrl($request->getUri());

            return new RedirectResponse($redirect);
        }

        throw new ForbiddenException();
    }
}
