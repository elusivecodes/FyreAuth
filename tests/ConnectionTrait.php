<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Auth\Access;
use Fyre\Auth\Auth;
use Fyre\Auth\Identity;
use Fyre\Auth\Middleware\AuthenticatedMiddleware;
use Fyre\Auth\Middleware\AuthMiddleware;
use Fyre\Auth\Middleware\AuthorizedMiddleware;
use Fyre\Auth\Middleware\UnauthenticatedMiddleware;
use Fyre\Auth\PolicyRegistry;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\Entity\EntityLocator;
use Fyre\Middleware\MiddlewareRegistry;
use Fyre\ORM\ModelRegistry;

use function getenv;
use function password_hash;

use const PASSWORD_DEFAULT;

trait ConnectionTrait
{
    protected Auth $auth;

    protected function login(): void
    {
        $user = Identity::getModel()
            ->find()
            ->where(['Users.id' => 1])
            ->first();

        $this->auth->login($user);
    }

    public static function setUpBeforeClass(): void
    {
        ConnectionManager::clear();
        ConnectionManager::setConfig('default', [
            'className' => MysqlConnection::class,
            'host' => getenv('MYSQL_HOST'),
            'username' => getenv('MYSQL_USERNAME'),
            'password' => getenv('MYSQL_PASSWORD'),
            'database' => getenv('MYSQL_DATABASE'),
            'port' => getenv('MYSQL_PORT'),
            'collation' => 'utf8mb4_unicode_ci',
            'charset' => 'utf8mb4',
            'compress' => true,
            'persist' => false,
        ]);

        $connection = ConnectionManager::use();

        $connection->query('DROP TABLE IF EXISTS posts');
        $connection->query('DROP TABLE IF EXISTS users');

        $connection->query(<<<'EOT'
            CREATE TABLE posts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT(10) UNSIGNED NOT NULL,
                content VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $connection->query(<<<'EOT'
            CREATE TABLE users (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                email VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                password VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                token VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);
    }

    public static function tearDownAfterClass(): void
    {
        $connection = ConnectionManager::use();
        $connection->query('DROP TABLE IF EXISTS posts');
        $connection->query('DROP TABLE IF EXISTS users');
    }

    protected function setUp(): void
    {
        EntityLocator::clear();
        EntityLocator::addNamespace('Tests\Mock\Entity');

        ModelRegistry::clear();
        ModelRegistry::addNamespace('Tests\Mock\Model');

        MiddlewareRegistry::clear();
        MiddlewareRegistry::map('auth', AuthMiddleware::class);
        MiddlewareRegistry::map('authenticated', AuthenticatedMiddleware::class);
        MiddlewareRegistry::map('authorized', AuthorizedMiddleware::class);
        MiddlewareRegistry::map('unauthenticated', UnauthenticatedMiddleware::class);

        PolicyRegistry::clear();
        PolicyRegistry::addNamespace('Tests\Mock\Policy');

        Access::clear();

        $_SESSION = [];

        $this->auth = new Auth();
        Auth::setInstance($this->auth);

        $Users = ModelRegistry::use('Users');

        Identity::setIdentifierFields(['username', 'email']);
        Identity::setPasswordField('password');
        Identity::setModel($Users);

        $user = $Users->newEntity([
            'username' => 'test',
            'email' => 'test@test.com',
            'password' => password_hash('test', PASSWORD_DEFAULT),
            'token' => 'Ew7tqx8kH6QsNe8SS0tVT0BX2LIRVQyl',
        ]);

        $Users->save($user);

        $Posts = ModelRegistry::use('Posts');

        $posts = $Posts->newEntities([
            [
                'user_id' => 1,
                'content' => 'test 1',
            ],
            [
                'user_id' => 2,
                'content' => 'test 2',
            ],
        ]);

        $Posts->saveMany($posts);
    }

    protected function tearDown(): void
    {
        $connection = ConnectionManager::use();
        $connection->query('TRUNCATE posts');
        $connection->query('TRUNCATE users');
    }
}
