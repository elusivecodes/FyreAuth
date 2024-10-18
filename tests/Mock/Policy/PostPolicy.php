<?php
declare(strict_types=1);

namespace Tests\Mock\Policy;

use Fyre\Auth\Policy;
use Fyre\Entity\Entity;

/**
 * PostPolicy
 */
class PostPolicy extends Policy
{
    public function create(Entity|null $user): bool
    {
        return (bool) $user;
    }

    public function update(Entity|null $user, Entity $post): bool
    {
        return $user && $user->id === $post->id;
    }
}
