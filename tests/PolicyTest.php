<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\Access;
use Fyre\Error\Exceptions\ForbiddenException;
use Fyre\ORM\ModelRegistry;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Model\PostsModel;

final class PolicyTest extends TestCase
{
    use ConnectionTrait;

    /**
     * @doesNotPerformAssertions
     */
    public function testPolicyCreateAlias(): void
    {
        $this->login();

        Access::authorize('create', 'Posts');
    }

    public function testPolicyCreateAliasFail(): void
    {
        $this->expectException(ForbiddenException::class);

        Access::authorize('create', 'Posts');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPolicyCreateClassName(): void
    {
        $this->login();

        Access::authorize('create', PostsModel::class);
    }

    public function testPolicyCreateClassNameFail(): void
    {
        $this->expectException(ForbiddenException::class);

        Access::authorize('create', PostsModel::class);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPolicyCreateModel(): void
    {
        $this->login();

        $Posts = ModelRegistry::use('Posts');

        Access::authorize('create', $Posts);
    }

    public function testPolicyCreateModelFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $Posts = ModelRegistry::use('Posts');

        Access::authorize('create', $Posts);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPolicyUpdateAlias(): void
    {
        $this->login();

        Access::authorize('update', 'Posts', 1);
    }

    public function testPolicyUpdateAliasFail(): void
    {
        $this->expectException(ForbiddenException::class);

        Access::authorize('update', 'Posts', 1);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPolicyUpdateClassName(): void
    {
        $this->login();

        Access::authorize('update', PostsModel::class, 1);
    }

    public function testPolicyUpdateClassNameFail(): void
    {
        $this->expectException(ForbiddenException::class);

        Access::authorize('update', PostsModel::class, 1);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPolicyUpdateEntity(): void
    {
        $this->login();

        $Posts = ModelRegistry::use('Posts');

        $post = $Posts->get(1);

        Access::authorize('update', $post);
    }

    public function testPolicyUpdateEntityFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $Posts = ModelRegistry::use('Posts');

        $post = $Posts->get(1);

        Access::authorize('update', $post);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPolicyUpdateModel(): void
    {
        $this->login();

        $Posts = ModelRegistry::use('Posts');

        Access::authorize('update', $Posts, 1);
    }

    public function testPolicyUpdateModelFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $Posts = ModelRegistry::use('Posts');

        Access::authorize('update', $Posts, 1);
    }
}
