<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Error\Exceptions\NotFoundException;
use Fyre\Http\Uri;
use Fyre\Middleware\Middleware;
use Fyre\Middleware\RequestHandler;
use Fyre\Server\ClientResponse;
use Fyre\Server\RedirectResponse;
use Fyre\Server\ServerRequest;

/**
 * UnauthenticatedMiddleware
 */
class UnauthenticatedMiddleware extends Middleware
{
    protected string|Uri $redirect;

    /**
     * New UnauthenticatedMiddleware constructor.
     *
     * @param string|Uri $redirect The URI to redirect to.
     */
    public function __construct(string|Uri $redirect = '/')
    {
        $this->redirect = $redirect;
    }

    /**
     * Process a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @param RequestHandler $handler The RequestHandler.
     * @return ClientResponse The ClientResponse.
     *
     * @throws NotFoundException is the user is authenticated and the request accepts JSON.
     */
    public function process(ServerRequest $request, RequestHandler $handler): ClientResponse
    {
        if (!Auth::instance()->isLoggedIn()) {
            return $handler->handle($request);
        }

        $contentType = $request->negotiate('content', ['text/html', 'application/json']);

        if ($contentType === 'application/json') {
            throw new NotFoundException();
        }

        return new RedirectResponse($this->redirect);
    }
}
