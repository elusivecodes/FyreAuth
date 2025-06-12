<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Closure;
use Fyre\Auth\Auth;
use Fyre\Middleware\Middleware;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

/**
 * AuthMiddleware
 */
class AuthMiddleware extends Middleware
{
    /**
     * New AuthenticatedMiddleware constructor.
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
     * @return ClientResponse The ClientResponse.
     */
    public function handle(ServerRequest $request, Closure $next): ClientResponse
    {
        $authenticators = $this->auth->authenticators();

        foreach ($authenticators as $authenticator) {
            $user = $authenticator->authenticate($request);

            if (!$user) {
                continue;
            }

            $this->auth->login($user);
            break;
        }

        $response = $next($request);

        $user = $this->auth->user();
        foreach ($authenticators as $authenticator) {
            $response = $authenticator->beforeResponse($response, $user);
        }

        return $response;
    }
}
