<?php
declare(strict_types=1);

namespace Fyre\Auth\Authenticators;

use Fyre\Auth\Authenticator;
use Fyre\Auth\Identity;
use Fyre\Entity\Entity;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;

use function count;
use function hash_hmac;
use function json_decode;
use function json_encode;
use function password_hash;

use const PASSWORD_DEFAULT;

/**
 * CookieAuthenticator
 */
class CookieAuthenticator extends Authenticator
{
    protected static array $defaults = [
        'cookieName' => 'auth',
        'cookieOptions' => [
            'httpOnly' => true,
        ],
        'identifierField' => 'email',
        'passwordField' => 'password',
        'salt' => null,
    ];

    protected bool|null $sendCookie = null;

    /**
     * Authenticate a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @return Entity|null The authenticated user.
     */
    public function authenticate(ServerRequest $request): Entity|null
    {
        $cookie = $request->getCookie($this->config['cookieName']);

        if (!$cookie) {
            return null;
        }

        $data = json_decode($cookie, true);

        if (!$data || count($data) !== 2) {
            $this->logout();

            return null;
        }

        [$identifier, $tokenHash] = $data;

        $user = Identity::identify($identifier);

        if (!$user) {
            $this->logout();

            return null;
        }

        $token = $this->createToken($user);

        if (!password_verify($token, $tokenHash)) {
            $this->logout();

            return null;
        }

        return $user;
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
        if ($this->sendCookie === false) {
            return $response->deleteCookie($this->config['cookieName'], $this->config['cookieOptions']);
        }

        if ($user && $this->sendCookie === true) {
            $identifier = $user->get($this->config['identifierField']);

            $token = $this->createToken($user);
            $tokenHash = password_hash($token, PASSWORD_DEFAULT);

            $value = json_encode([$identifier, $tokenHash]);

            return $response->setCookie($this->config['cookieName'], $value, $this->config['cookieOptions']);
        }

        return $response;
    }

    /**
     * Login as a user.
     *
     * @param Entity $user The user.
     * @param bool $rememberMe Whether to remember the user.
     * @return static The Auth.
     */
    public function login(Entity $user, bool $rememberMe = false): void
    {
        if ($rememberMe) {
            $this->sendCookie = true;
        }
    }

    /**
     * Logout the current user.
     *
     * @return static The Auth.
     */
    public function logout(): void
    {
        $this->sendCookie = false;
    }

    /**
     * Create a token for a user.
     *
     * @param Entity $user The user.
     * @return string The token.
     */
    protected function createToken(Entity $user): string
    {
        $identifier = $user->get($this->config['identifierField']);
        $password = $user->get($this->config['passwordField']);

        $value = $identifier.$password;

        if (!$this->config['salt']) {
            return $value;
        }

        $hash = hash_hmac('sha1', $value, $this->config['salt']);

        return $value.$hash;
    }
}
