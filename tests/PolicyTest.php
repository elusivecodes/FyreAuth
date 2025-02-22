<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Error\Exceptions\ForbiddenException;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Model\PostsModel;

final class PolicyTest extends TestCase
{
    use ConnectionTrait;

    public function testPolicyCreateAlias(): void
    {
        $this->expectNotToPerformAssertions();

        $this->login();

        $this->access->authorize('create', 'Posts');
    }

    public function testPolicyCreateAliasFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->access->authorize('create', 'Posts');
    }

    public function testPolicyCreateClassName(): void
    {
        $this->expectNotToPerformAssertions();

        $this->login();

        $this->access->authorize('create', PostsModel::class);
    }

    public function testPolicyCreateClassNameFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->access->authorize('create', PostsModel::class);
    }

    public function testPolicyCreateModel(): void
    {
        $this->expectNotToPerformAssertions();

        $this->login();

        $Posts = $this->modelRegistry->use('Posts');

        $this->access->authorize('create', $Posts);
    }

    public function testPolicyCreateModelFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $Posts = $this->modelRegistry->use('Posts');

        $this->access->authorize('create', $Posts);
    }

    public function testPolicyUpdateAlias(): void
    {
        $this->expectNotToPerformAssertions();

        $this->login();

        $this->access->authorize('update', 'Posts', 1);
    }

    public function testPolicyUpdateAliasFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->access->authorize('update', 'Posts', 1);
    }

    public function testPolicyUpdateClassName(): void
    {
        $this->expectNotToPerformAssertions();

        $this->login();

        $this->access->authorize('update', PostsModel::class, 1);
    }

    public function testPolicyUpdateClassNameFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->access->authorize('update', PostsModel::class, 1);
    }

    public function testPolicyUpdateEntity(): void
    {
        $this->expectNotToPerformAssertions();

        $this->login();

        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->get(1);

        $this->access->authorize('update', $post);
    }

    public function testPolicyUpdateEntityFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->get(1);

        $this->access->authorize('update', $post);
    }

    public function testPolicyUpdateModel(): void
    {
        $this->expectNotToPerformAssertions();

        $this->login();

        $Posts = $this->modelRegistry->use('Posts');

        $this->access->authorize('update', $Posts, 1);
    }

    public function testPolicyUpdateModelFail(): void
    {
        $this->expectException(ForbiddenException::class);

        $Posts = $this->modelRegistry->use('Posts');

        $this->access->authorize('update', $Posts, 1);
    }
}
