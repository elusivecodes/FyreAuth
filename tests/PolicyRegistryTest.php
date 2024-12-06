<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\PolicyRegistry;
use Fyre\Container\Container;
use Fyre\Utility\Inflector;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Model\OtherModel;
use Tests\Mock\Model\PostsModel;
use Tests\Mock\Policy\PostPolicy;

final class PolicyRegistryTest extends TestCase
{
    protected PolicyRegistry $policyRegistry;

    public function testGetNamespaces(): void
    {
        $this->assertSame(
            [
                'Tests\Mock\Policy\\',
            ],
            $this->policyRegistry->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->policyRegistry->hasNamespace('Tests\Mock\Policy')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->policyRegistry->hasNamespace('Tests\Invalid')
        );
    }

    public function testMap(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->map('Other', PostPolicy::class)
        );

        $policy = $this->policyRegistry->use('Other');

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testMapClassName(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->map(OtherModel::class, PostPolicy::class)
        );

        $policy = $this->policyRegistry->use(OtherModel::class);

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->removeNamespace('Tests\Mock\Policy')
        );

        $this->assertFalse(
            $this->policyRegistry->hasNamespace('Tests\Mock\Policy')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->removeNamespace('Tests\Invalid')
        );
    }

    public function testUse(): void
    {
        $policy = $this->policyRegistry->use('Posts');

        $this->assertInstanceOf(PostPolicy::class, $policy);

        $this->assertSame(
            $this->policyRegistry->use('Posts'),
            $policy
        );
    }

    public function testUseClassName(): void
    {
        $policy = $this->policyRegistry->use(PostsModel::class);

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testUseInvalid(): void
    {
        $policy = $this->policyRegistry->use('Invalid');

        $this->assertNull($policy);
    }

    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Inflector::class);
        $container->singleton(PolicyRegistry::class);

        $this->policyRegistry = $container->use(PolicyRegistry::class);

        $this->policyRegistry->addNamespace('Tests\Mock\Policy');
    }
}
