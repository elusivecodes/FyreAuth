<?php
declare(strict_types=1);

namespace Fyre\Auth\Authenticators;

use Fyre\Auth\Auth;
use Fyre\Auth\Authenticator;
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
     * New Authenticator constructor.
     *
     * @param Auth $auth The Auth.
     * @param array $options Options for the handler.
     */
    public function __construct(
        Auth $auth,
        protected Session $session,
        array $options = []
    ) {
        parent::__construct($auth, $options);
    }

    /**
     * Authenticate a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @return Entity|null The authenticated user.
     */
    public function authenticate(ServerRequest $request): Entity|null
    {
        $id = $this->session->get($this->config['sessionKey']);

        if (!$id) {
            return null;
        }

        $Model = $this->auth->identifier()->getModel();

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

        if (!$this->session->has($sessionKey)) {
            $this->session->refresh();

            $id = $user->get($this->config['sessionField']);

            $this->session->set($sessionKey, $id);
        }
    }

    /**
     * Logout the current user.
     *
     * @return static The Auth.
     */
    public function logout(): void
    {
        $this->session->delete($this->config['sessionKey']);
        $this->session->refresh(true);
    }
}
