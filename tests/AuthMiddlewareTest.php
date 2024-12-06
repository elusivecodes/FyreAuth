<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Entity\Entity;
use Fyre\Error\Exceptions\ForbiddenException;
use Fyre\Error\Exceptions\NotFoundException;
use Fyre\Middleware\MiddlewareQueue;
use Fyre\Middleware\RequestHandler;
use Fyre\Server\ClientResponse;
use Fyre\Server\ServerRequest;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Authenticator\MockAuthenticator;

final class AuthMiddlewareTest extends TestCase
{
    use ConnectionTrait;

    public function testAuthenticatedMiddleware(): void
    {
        $this->login();

        $queue = new MiddlewareQueue([
            'authenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );
    }

    public function testAuthenticatedMiddlewareFail(): void
    {
        $queue = new MiddlewareQueue([
            'authenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            '/login',
            $response->getHeaderValue('Location')
        );
    }

    public function testAuthMiddleware(): void
    {
        $authenticator = $this->container->build(MockAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            'test',
            $response->getHeaderValue('Authenticated')
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testAuthorizedMiddleware(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $queue = new MiddlewareQueue([
            'authorized:test',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    public function testAuthorizedMiddlewareArguments(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(Entity|null $user, string $value) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame('test', $value);

            return true;
        });

        $queue = new MiddlewareQueue([
            'authorized:test,test',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    public function testAuthorizedMiddlewareFail(): void
    {
        $this->login();

        $this->expectException(ForbiddenException::class);

        $this->access->define('test', function(Entity|null $user): bool {
            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });

        $queue = new MiddlewareQueue([
            'authorized:test',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);
    }

    public function testUnauthenticatedMiddleware(): void
    {
        $queue = new MiddlewareQueue([
            'unauthenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertNull(
            $response->getHeaderValue('Location')
        );
    }

    public function testUnauthenticatedMiddlewareFail(): void
    {
        $this->expectException(NotFoundException::class);

        $this->login();

        $queue = new MiddlewareQueue([
            'unauthenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);
    }

    public function testUnauthenticatedMiddlewareFailJson(): void
    {
        $this->expectException(NotFoundException::class);

        $this->login();

        $queue = new MiddlewareQueue([
            'unauthenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'globals' => [
                    'server' => [
                        'HTTP_ACCEPT' => 'application/json;q=0.9,text/plain',
                    ],
                ],
            ],
        ]);

        $handler->handle($request);
    }
}
