<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Middleware\Middleware;
use Fyre\Middleware\RequestHandler;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

/**
 * AuthMiddleware
 */
class AuthMiddleware extends Middleware
{
    /**
     * Process a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @param RequestHandler $handler The RequestHandler.
     * @return ClientResponse The ClientResponse.
     */
    public function process(ServerRequest $request, RequestHandler $handler): ClientResponse
    {
        $auth = Auth::instance();

        $authenticators = $auth->authenticators();

        foreach ($authenticators as $authenticator) {
            $user = $authenticator->authenticate($request);

            if (!$user) {
                continue;
            }

            $auth->login($user);
            break;
        }

        $response = $handler->handle($request);

        $user = $auth->user();
        foreach ($authenticators as $authenticator) {
            $response = $authenticator->beforeResponse($response, $user);
        }

        return $response;
    }
}
