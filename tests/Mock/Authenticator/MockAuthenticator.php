<?php
declare(strict_types=1);

namespace Tests\Mock\Authenticator;

use Fyre\Auth\Authenticator;
use Fyre\Entity\Entity;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

class MockAuthenticator extends Authenticator
{
    public function authenticate(ServerRequest $request): Entity|null
    {
        return $this->auth->identifier()->identify('test@test.com');
    }

    public function beforeResponse(ClientResponse $response, Entity|null $user = null): ClientResponse
    {
        return $response->setHeader('Authenticated', 'test');
    }
}
