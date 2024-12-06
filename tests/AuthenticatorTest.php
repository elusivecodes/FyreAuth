<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\Authenticators\CookieAuthenticator;
use Fyre\Auth\Authenticators\SessionAuthenticator;
use Fyre\Auth\Authenticators\TokenAuthenticator;
use Fyre\Middleware\MiddlewareQueue;
use Fyre\Middleware\RequestHandler;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;
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
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $user = $this->identifier->identify('test@test.com');

        $tokenHash = password_hash('test@test.com'.$user->password, PASSWORD_DEFAULT);
        $auth = json_encode(['test@test.com', $tokenHash]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'globals' => [
                    'cookie' => [
                        'auth' => $auth,
                    ],
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
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

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
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

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
        $authenticator = $this->container->build(CookieAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

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
        $authenticator = $this->container->build(SessionAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->session->set('auth', 1);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testSessionAuthenticatorLogin(): void
    {
        $authenticator = $this->container->build(SessionAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $this->auth->attempt('test@test.com', 'test');

        $this->assertTrue($this->auth->isLoggedIn());

        $this->assertSame(
            1,
            $this->session->get('auth')
        );
    }

    public function testSessionAuthenticatorLogout(): void
    {
        $authenticator = $this->container->build(SessionAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $this->auth->attempt('test@test.com', 'test');
        $this->auth->logout();

        $this->assertFalse($this->auth->isLoggedIn());

        $this->assertNull(
            $this->session->get('auth')
        );
    }

    public function testTokenAuthenticator(): void
    {
        $authenticator = $this->container->build(TokenAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'globals' => [
                    'server' => [
                        'HTTP_AUTHORIZATION' => 'Bearer Ew7tqx8kH6QsNe8SS0tVT0BX2LIRVQyl',
                    ],
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
