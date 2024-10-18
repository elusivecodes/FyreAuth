<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\Authenticators\CookieAuthenticator;
use Fyre\Auth\Authenticators\SessionAuthenticator;
use Fyre\Auth\Authenticators\TokenAuthenticator;
use Fyre\Auth\Identity;
use Fyre\Middleware\MiddlewareQueue;
use Fyre\Middleware\RequestHandler;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;
use Fyre\Session\Session;
use PHPUnit\Framework\TestCase;

use function json_decode;
use function json_encode;
use function password_hash;
use function password_verify;

use const PASSWORD_DEFAULT;

final class AuthenticatorTest extends TestCase
{
    use ConnectionTrait;

    public function testCookieAuthenticator(): void
    {
        $this->auth->addAuthenticator(new CookieAuthenticator());

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = new RequestHandler($queue);

        $user = Identity::identify('test@test.com');

        $tokenHash = password_hash('test@test.com'.$user->password, PASSWORD_DEFAULT);
        $auth = json_encode(['test@test.com', $tokenHash]);

        $request = new ServerRequest([
            'globals' => [
                'cookie' => [
                    'auth' => $auth,
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testCookieAuthenticatorLogin(): void
    {
        $this->auth->addAuthenticator(new CookieAuthenticator());

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = new RequestHandler($queue);

        $request = new ServerRequest();

        $this->auth->attempt('test@test.com', 'test');

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());

        $user = $this->auth->user();

        $cookie = $response->getCookie('auth');

        $this->assertNull($cookie);
    }

    public function testCookieAuthenticatorLoginRemember(): void
    {
        $this->auth->addAuthenticator(new CookieAuthenticator());

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = new RequestHandler($queue);

        $request = new ServerRequest();

        $this->auth->attempt('test@test.com', 'test', true);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());

        $user = $this->auth->user();

        $cookie = $response->getCookie('auth');

        $data = json_decode($cookie->getValue(), true);

        $this->assertCount(2, $data);

        [$identifier, $tokenHash] = $data;

        $token = 'test@test.com'.$user->password;

        $this->assertTrue(
            password_verify($token, $tokenHash)
        );

        $this->assertSame('auth', $cookie->getName());
        $this->assertFalse($cookie->isExpired());
    }

    public function testCookieAuthenticatorLogout(): void
    {
        $this->auth->addAuthenticator(new CookieAuthenticator());

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = new RequestHandler($queue);

        $request = new ServerRequest();

        $this->auth->attempt('test@test.com', 'test', true);
        $this->auth->logout();

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertFalse($this->auth->isLoggedIn());

        $cookie = $response->getCookie('auth');

        $this->assertSame('', $cookie->getValue());
        $this->assertTrue($cookie->isExpired());
    }

    public function testSessionAuthenticator(): void
    {
        $this->auth->addAuthenticator(new SessionAuthenticator());

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = new RequestHandler($queue);

        $request = new ServerRequest();

        Session::set('auth', 1);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testSessionAuthenticatorLogin(): void
    {
        $this->auth->addAuthenticator(new SessionAuthenticator());

        $this->auth->attempt('test@test.com', 'test');

        $this->assertTrue($this->auth->isLoggedIn());

        $this->assertSame(
            1,
            Session::get('auth')
        );
    }

    public function testSessionAuthenticatorLogout(): void
    {
        $this->auth->addAuthenticator(new SessionAuthenticator());

        $this->auth->attempt('test@test.com', 'test');
        $this->auth->logout();

        $this->assertFalse($this->auth->isLoggedIn());

        $this->assertNull(
            Session::get('auth')
        );
    }

    public function testTokenAuthenticator(): void
    {
        $this->auth->addAuthenticator(new TokenAuthenticator());

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = new RequestHandler($queue);

        $request = new ServerRequest([
            'globals' => [
                'server' => [
                    'HTTP_AUTHORIZATION' => 'Bearer Ew7tqx8kH6QsNe8SS0tVT0BX2LIRVQyl',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }
}
