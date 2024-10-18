<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\PolicyRegistry;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Model\OtherModel;
use Tests\Mock\Model\PostsModel;
use Tests\Mock\Policy\PostPolicy;

final class PolicyRegistryTest extends TestCase
{
    public function testGetNamespaces(): void
    {
        $this->assertSame(
            [
                '\Tests\Mock\Policy\\',
            ],
            PolicyRegistry::getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            PolicyRegistry::hasNamespace('Tests\Mock\Policy')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            PolicyRegistry::hasNamespace('Tests\Invalid')
        );
    }

    public function testMap(): void
    {
        PolicyRegistry::map('Other', PostPolicy::class);

        $policy = PolicyRegistry::use('Other');

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testMapClassName(): void
    {
        PolicyRegistry::map(OtherModel::class, PostPolicy::class);

        $policy = PolicyRegistry::use(OtherModel::class);

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testRemoveNamespace(): void
    {
        $this->assertTrue(
            PolicyRegistry::removeNamespace('Tests\Mock\Policy')
        );

        $this->assertFalse(
            PolicyRegistry::hasNamespace('Tests\Mock\Policy')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertFalse(
            PolicyRegistry::removeNamespace('Tests\Invalid')
        );
    }

    public function testUse(): void
    {
        $policy = PolicyRegistry::use('Posts');

        $this->assertInstanceOf(PostPolicy::class, $policy);

        $this->assertSame(
            PolicyRegistry::use('Posts'),
            $policy
        );
    }

    public function testUseClassName(): void
    {
        $policy = PolicyRegistry::use(PostsModel::class);

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testUseInvalid(): void
    {
        $policy = PolicyRegistry::use('Invalid');

        $this->assertNull($policy);
    }

    protected function setUp(): void
    {
        PolicyRegistry::clear();
        PolicyRegistry::addNamespace('Tests\Mock\Policy');
    }
}
