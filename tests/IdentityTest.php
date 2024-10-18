<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\Identity;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Entity\User;
use Tests\Mock\Model\UsersModel;

final class IdentityTest extends TestCase
{
    use ConnectionTrait;

    public function testAttempt(): void
    {
        $user = Identity::attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $user
        );
    }

    public function testAttemptInvalidPassword(): void
    {
        $user = Identity::attempt('test@test.com', 'invalid');

        $this->assertNull($user);
    }

    public function testAttemptInvalidUsername(): void
    {
        $user = Identity::attempt('invalid@test.com', 'any');

        $this->assertNull($user);
    }

    public function testAttemptRehash(): void
    {
        $user = Identity::identify('test@test.com');

        $user->password = password_hash('test', PASSWORD_ARGON2I);

        Identity::getModel()->save($user);

        $user = Identity::attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $user = Identity::identify('test@test.com');

        $this->assertFalse(
            password_needs_rehash($user->password, PASSWORD_DEFAULT)
        );
    }

    public function testGetIdentifierFields(): void
    {
        $this->assertSame(
            ['username', 'email'],
            Identity::getIdentifierFields()
        );
    }

    public function testGetModel(): void
    {
        $Model = Identity::getModel();

        $this->assertInstanceOf(
            UsersModel::class,
            $Model
        );
    }

    public function testGetPasswordField(): void
    {
        $this->assertSame(
            'password',
            Identity::getPasswordField()
        );
    }

    public function testIdentify(): void
    {
        $user = Identity::identify('test@test.com');

        $this->assertInstanceOf(
            User::class,
            $user
        );
    }

    public function testIdentifyInvalid(): void
    {
        $user = Identity::identify('invalid');

        $this->assertNull($user);
    }
}
