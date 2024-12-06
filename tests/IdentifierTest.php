<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Mock\Entity\User;
use Tests\Mock\Model\UsersModel;

final class IdentifierTest extends TestCase
{
    use ConnectionTrait;

    public function testAttempt(): void
    {
        $user = $this->identifier->attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $user
        );
    }

    public function testAttemptInvalidPassword(): void
    {
        $user = $this->identifier->attempt('test@test.com', 'invalid');

        $this->assertNull($user);
    }

    public function testAttemptInvalidUsername(): void
    {
        $user = $this->identifier->attempt('invalid@test.com', 'any');

        $this->assertNull($user);
    }

    public function testAttemptRehash(): void
    {
        $user = $this->identifier->identify('test@test.com');

        $user->password = password_hash('test', PASSWORD_ARGON2I);

        $this->identifier->getModel()->save($user);

        $user = $this->identifier->attempt('test@test.com', 'test');

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $user = $this->identifier->identify('test@test.com');

        $this->assertFalse(
            password_needs_rehash($user->password, PASSWORD_DEFAULT)
        );
    }

    public function testGetIdentifierFields(): void
    {
        $this->assertSame(
            ['username', 'email'],
            $this->identifier->getIdentifierFields()
        );
    }

    public function testGetModel(): void
    {
        $Model = $this->identifier->getModel();

        $this->assertInstanceOf(
            UsersModel::class,
            $Model
        );
    }

    public function testGetPasswordField(): void
    {
        $this->assertSame(
            'password',
            $this->identifier->getPasswordField()
        );
    }

    public function testIdentify(): void
    {
        $user = $this->identifier->identify('test@test.com');

        $this->assertInstanceOf(
            User::class,
            $user
        );
    }

    public function testIdentifyInvalid(): void
    {
        $user = $this->identifier->identify('invalid');

        $this->assertNull($user);
    }
}
