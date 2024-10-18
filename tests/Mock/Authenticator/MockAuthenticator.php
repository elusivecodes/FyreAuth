<?php
declare(strict_types=1);

namespace Tests\Mock\Authenticator;

use Fyre\Auth\Authenticator;
use Fyre\Auth\Identity;
use Fyre\Entity\Entity;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

/**
 * MockAuthenticator
 */
class MockAuthenticator extends Authenticator
{
    /**
     * Authenticate a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @return Entity|null The authenticated user.
     */
    public function authenticate(ServerRequest $request): Entity|null
    {
        return Identity::identify('test@test.com');
    }

    /**
     * Update the ClientResponse before sending to client.
     *
     * @param ClientResponse $response The ClientResponse.
     * @param Entity|null The authenticated user.
     * @return ClientResponse The ClientResponse.
     */
    public function beforeResponse(ClientResponse $response, Entity|null $user = null): ClientResponse
    {
        return $response->setHeader('Authenticated', 'test');
    }
}
