<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Mock\Authenticator\MockAuthenticator;
use Tests\Mock\Entity\User;

final class AuthTest extends TestCase
{
    use ConnectionTrait;

    public function testAttempt(): void
    {
        $user = $this->auth->attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testAttemptInvalidPassword(): void
    {
        $user = $this->auth->attempt('test@test.com', 'invalid');

        $this->assertNull($user);
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testAttemptInvalidUsername(): void
    {
        $user = $this->auth->attempt('invalid@test.com', 'any');

        $this->assertNull($user);
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testAuthenticator(): void
    {
        $authenticator = new MockAuthenticator();

        $this->auth->addAuthenticator($authenticator);

        $this->assertSame(
            $authenticator,
            $this->auth->authenticator(MockAuthenticator::class)
        );
    }

    public function testAuthenticatorInvalid(): void
    {
        $this->assertNull(
            $this->auth->authenticator('invalid')
        );
    }

    public function testAuthenticatorKey(): void
    {
        $authenticator = new MockAuthenticator();

        $this->auth->addAuthenticator($authenticator, 'mock');

        $this->assertSame(
            $authenticator,
            $this->auth->authenticator('mock')
        );
    }

    public function testAuthenticators(): void
    {
        $authenticator = new MockAuthenticator();

        $this->auth->addAuthenticator($authenticator, 'mock');

        $this->assertSame(
            [
                'mock' => $authenticator,
            ],
            $this->auth->authenticators()
        );
    }

    public function testIsLoggedIn(): void
    {
        $this->login();

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testIsLoggedInFalse(): void
    {
        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testLogout(): void
    {
        $this->login();
        $this->auth->logout();

        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testUser(): void
    {
        $this->login();

        $this->assertInstanceOf(
            User::class,
            $this->auth->user()
        );
    }

    public function testUserNull(): void
    {
        $this->assertNull(
            $this->auth->user()
        );
    }
}
