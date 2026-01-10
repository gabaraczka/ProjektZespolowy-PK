<?php

namespace Tests;

use App\Model\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User(
            email: 'test@example.com',
            password: 'hashedPassword123',
            id: 1,
            created_at: '2024-01-10 10:00:00'
        );
    }

    public function testUserCanBeCreated(): void
    {
        $this->assertEquals('test@example.com', $this->user->getEmail());
        $this->assertEquals('hashedPassword123', $this->user->getPassword());
    }

    public function testUserIdCanBeSet(): void
    {
        $this->user->setId(5);
        $this->assertEquals(5, $this->user->getId());
    }

    public function testUserPasswordCanBeUpdated(): void
    {
        $newPassword = 'newHashedPassword456';
        $this->user->setPassword($newPassword);
        
        $this->assertEquals($newPassword, $this->user->getPassword());
    }

    public function testUserCreatedAtIsReturned(): void
    {
        $this->assertNotNull($this->user->getCreatedAt());
        $this->assertEquals('2024-01-10 10:00:00', $this->user->getCreatedAt());
    }
}
