<?php
declare(strict_types=1);

namespace Fyre\Auth\Authenticators;

use Fyre\Auth\Authenticator;
use Fyre\Auth\Identity;
use Fyre\Entity\Entity;
use Fyre\Server\ServerRequest;

use function str_starts_with;
use function strlen;
use function substr;

/**
 * TokenAuthenticator
 */
class TokenAuthenticator extends Authenticator
{
    protected static array $defaults = [
        'tokenHeader' => 'Authorization',
        'tokenHeaderPrefix' => 'Bearer',
        'tokenQuery' => null,
        'tokenField' => 'token',
    ];

    /**
     * Authenticate a ServerRequest.
     *
     * @param ServerRequest $request The ServerRequest.
     * @return Entity|null The authenticated user.
     */
    public function authenticate(ServerRequest $request): Entity|null
    {
        $headerToken = $this->config['tokenHeader'] ?
            $request->getHeaderValue($this->config['tokenHeader']) :
            null;

        if ($headerToken && $this->config['tokenHeaderPrefix'] && str_starts_with($headerToken, $this->config['tokenHeaderPrefix'].' ')) {
            $headerToken = substr($headerToken, strlen($this->config['tokenHeaderPrefix']) + 1);
        }

        $queryToken = $this->config['tokenQuery'] ?
            $request->getQuery($this->config['tokenQuery']) :
            null;

        $token = $headerToken ?? $queryToken;

        if (!$token) {
            return null;
        }

        $Model = Identity::getModel();

        return $Model->find()
            ->where([
                $Model->aliasField($this->config['tokenField']) => $token,
            ])
            ->first();
    }
}
