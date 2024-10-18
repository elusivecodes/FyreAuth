<?php
declare(strict_types=1);

namespace Fyre\Auth\Authenticators;

use Fyre\Auth\Authenticator;
use Fyre\Auth\Identity;
use Fyre\Entity\Entity;
use Fyre\Server\ServerRequest;
use Fyre\Session\Session;

/**
 * SessionAuthenticator
 */
class SessionAuthenticator extends Authenticator
{
    protected static array $defaults = [
        'sessionKey' => 'auth',
        'sessionField' => 'id',
    ];

    /**
     * Authenticate a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @return Entity|null The authenticated user.
     */
    public function authenticate(ServerRequest $request): Entity|null
    {
        $id = Session::get($this->config['sessionKey']);

        if (!$id) {
            return null;
        }

        $Model = Identity::getModel();

        return $Model->find()
            ->where([
                $Model->aliasField($this->config['sessionField']) => $id,
            ])
            ->first();
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
        $sessionKey = $this->config['sessionKey'];

        if (!Session::has($sessionKey)) {
            Session::refresh();

            $id = $user->get($this->config['sessionField']);

            Session::set($sessionKey, $id);
        }
    }

    /**
     * Logout the current user.
     *
     * @return static The Auth.
     */
    public function logout(): void
    {
        Session::delete($this->config['sessionKey']);
        Session::refresh(true);
    }
}
