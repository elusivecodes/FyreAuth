<?php
declare(strict_types=1);

namespace Tests\Mock\Policy;

use Tests\Mock\Entity\Post;
use Tests\Mock\Entity\User;

class PostPolicy
{
    public function create(User|null $user): bool
    {
        return (bool) $user;
    }

    public function update(User|null $user, Post $post): bool
    {
        return $user && $user->id === $post->id;
    }
}
