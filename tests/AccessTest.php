<?php
declare(strict_types=1);

namespace Tests;

use Exception;
use Fyre\Entity\Entity;
use Fyre\Error\Exceptions\ForbiddenException;
use PHPUnit\Framework\TestCase;

final class AccessTest extends TestCase
{
    use ConnectionTrait;

    public function testAfter(): void
    {
        $this->login();

        $ranAfter = false;
        $this->access->after(function(Entity|null $user, string $rule, bool|null $result) use (&$ranAfter): bool {
            $ranAfter = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
            $this->assertNull($result);

            return true;
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertTrue($ranAfter);
    }

    public function testAfterFail(): void
    {
        $this->login();

        $ranAfter = false;
        $this->access->after(function(Entity|null $user, string $rule, bool|null $result) use (&$ranAfter): bool {
            $ranAfter = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
            $this->assertNull($result);

            return false;
        });

        $this->assertFalse(
            $this->access->allows('test')
        );

        $this->assertTrue($ranAfter);
    }

    public function testAfterMultiple(): void
    {
        $this->login();

        $ranAfter = 0;
        $this->access->after(function(Entity|null $user, string $rule, bool|null $result) use (&$ranAfter): void {
            $ranAfter++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
            $this->assertNull($result);
        });
        $this->access->after(function(Entity|null $user, string $rule, bool|null $result) use (&$ranAfter): bool {
            $ranAfter++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
            $this->assertNull($result);

            return true;
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertSame(
            2,
            $ranAfter
        );
    }

    public function testAfterMultipleResult(): void
    {
        $this->login();

        $ranAfter = 0;
        $this->access->after(function(Entity|null $user, string $rule, bool|null $result) use (&$ranAfter): bool {
            $ranAfter++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
            $this->assertNull($result);

            return true;
        });
        $this->access->after(function(Entity|null $user, string $rule, bool|null $result) use (&$ranAfter): bool {
            $ranAfter++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
            $this->assertTrue($result);

            return false;
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertSame(
            2,
            $ranAfter
        );
    }

    public function testAfterResult(): void
    {
        $this->login();

        $ranAfter = false;
        $this->access->after(function(Entity|null $user, string $rule, bool|null $result) use (&$ranAfter): bool {
            $ranAfter = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
            $this->assertTrue($result);

            return false;
        });

        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertTrue($ranAfter);
        $this->assertTrue($ran);
    }

    public function testAllows(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertTrue($ran);
    }

    public function testAllowsArguments(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(Entity|null $user, string $value) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame('test', $value);

            return true;
        });

        $this->assertTrue(
            $this->access->allows('test', 'test')
        );

        $this->assertTrue($ran);
    }

    public function testAllowsFail(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });

        $this->assertFalse(
            $this->access->allows('test')
        );

        $this->assertTrue($ran);
    }

    public function testAny(): void
    {
        $this->login();

        $ran = 0;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });
        $this->access->define('test2', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $this->assertTrue(
            $this->access->any(['test', 'test2'])
        );

        $this->assertSame(1, $ran);
    }

    public function testAnyArguments(): void
    {
        $this->login();

        $ran = 0;
        $this->access->define('test', function(Entity|null $user, string $value) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame('test', $value);

            return true;
        });
        $this->access->define('test2', function(Entity|null $user, string $value) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame('test', $value);

            return true;
        });

        $this->assertTrue(
            $this->access->any(['test', 'test2'], 'test')
        );

        $this->assertSame(1, $ran);
    }

    public function testAnyFail(): void
    {
        $this->login();

        $ran = 0;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });
        $this->access->define('test2', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });

        $this->assertFalse(
            $this->access->any(['test', 'test2'])
        );

        $this->assertSame(2, $ran);
    }

    public function testAnyFirstFail(): void
    {
        $this->login();

        $ran = 0;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });
        $this->access->define('test2', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $this->assertTrue(
            $this->access->any(['test', 'test2'])
        );

        $this->assertSame(2, $ran);
    }

    public function testAuthorize(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $this->access->authorize('test');

        $this->assertTrue($ran);
    }

    public function testAuthorizeFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertNull($user);

            return false;
        });

        $this->access->authorize('test');

        $this->assertTrue($ran);
    }

    public function testBefore(): void
    {
        $this->login();

        $ranBefore = false;
        $this->access->before(function(Entity|null $user, string $rule) use (&$ranBefore): bool {
            $ranBefore = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');

            return true;
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertTrue($ranBefore);
    }

    public function testBeforeFail(): void
    {
        $this->login();

        $ranBefore = false;
        $this->access->before(function(Entity|null $user, string $rule) use (&$ranBefore): bool {
            $ranBefore = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');

            return false;
        });

        $this->assertFalse(
            $this->access->allows('test')
        );

        $this->assertTrue($ranBefore);
    }

    public function testBeforeMultiple(): void
    {
        $this->login();

        $ranBefore = 0;
        $this->access->before(function(Entity|null $user, string $rule) use (&$ranBefore): void {
            $ranBefore++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');
        });
        $this->access->before(function(Entity|null $user, string $rule) use (&$ranBefore): bool {
            $ranBefore++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');

            return true;
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertSame(
            2,
            $ranBefore
        );
    }

    public function testBeforeMultipleFail(): void
    {
        $this->login();

        $ranBefore = false;
        $this->access->before(function(Entity|null $user, string $rule) use (&$ranBefore): bool {
            $ranBefore = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');

            return false;
        });
        $this->access->before(function(Entity|null $user, string $rule): void {
            throw new Exception();
        });

        $this->assertFalse(
            $this->access->allows('test')
        );

        $this->assertTrue(
            $ranBefore
        );
    }

    public function testBeforeMultipleResult(): void
    {
        $this->login();

        $ranBefore = false;
        $this->access->before(function(Entity|null $user, string $rule) use (&$ranBefore): bool {
            $ranBefore = true;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame($rule, 'test');

            return true;
        });
        $this->access->before(function(Entity|null $user, string $rule): void {
            throw new Exception();
        });

        $this->assertTrue(
            $this->access->allows('test')
        );

        $this->assertTrue(
            $ranBefore
        );
    }

    public function testDenies(): void
    {
        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertNull($user);

            return false;
        });

        $this->assertTrue(
            $this->access->denies('test')
        );

        $this->assertTrue($ran);
    }

    public function testDeniesArguments(): void
    {
        $ran = false;
        $this->access->define('test', function(Entity|null $user, string $value) use (&$ran): bool {
            $ran = true;

            $this->assertNull($user);
            $this->assertSame('test', $value);

            return false;
        });

        $this->assertTrue(
            $this->access->denies('test', 'test')
        );

        $this->assertTrue($ran);
    }

    public function testDeniesFail(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $this->assertFalse(
            $this->access->denies('test')
        );

        $this->assertTrue($ran);
    }

    public function testNone(): void
    {
        $this->login();

        $ran = 0;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });
        $this->access->define('test2', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });

        $this->assertTrue(
            $this->access->none(['test', 'test2'])
        );

        $this->assertSame(2, $ran);
    }

    public function testNoneArguments(): void
    {
        $this->login();

        $ran = 0;
        $this->access->define('test', function(Entity|null $user, string $value) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame('test', $value);

            return false;
        });
        $this->access->define('test2', function(Entity|null $user, string $value) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);
            $this->assertSame('test', $value);

            return false;
        });

        $this->assertTrue(
            $this->access->none(['test', 'test2'], 'test')
        );

        $this->assertSame(2, $ran);
    }

    public function testNoneFail(): void
    {
        $this->login();

        $ran = 0;
        $this->access->define('test', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return false;
        });
        $this->access->define('test2', function(Entity|null $user) use (&$ran): bool {
            $ran++;

            $this->assertInstanceOf(Entity::class, $user);

            return true;
        });

        $this->assertFalse(
            $this->access->none(['test', 'test2'])
        );

        $this->assertSame(2, $ran);
    }

    public function testOrder(): void
    {
        $results = [];

        $this->access->define('test', function() use (&$results): bool {
            $results[] = 1;

            return true;
        });

        $this->access->before(function() use (&$results): void {
            $results[] = 2;
        });

        $this->access->before(function() use (&$results): void {
            $results[] = 3;
        });

        $this->access->after(function() use (&$results): void {
            $results[] = 4;
        });

        $this->access->after(function() use (&$results): void {
            $results[] = 5;
        });

        $this->access->allows('test');

        $this->assertSame(
            [2, 3, 1, 4, 5],
            $results
        );
    }
}
