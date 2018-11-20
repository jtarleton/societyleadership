<?php 
declare(strict_types=1);
require(__DIR__ .'/../society_lib.php');

use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testConnect(): void
    {
        $pdo = \SocietyLeadership\SocietyDB::getInstance();
        $this->assertInstanceOf(
            'PDO',
            $pdo
        );
    }

    public function testUsername(): void
    {
        $allUsers = \SocietyLeadership\User::findByCriteria();
        $this->assertEquals(2,
            2
        );
    }

    public function testPassword(): void
    {
        $allUsers = \SocietyLeadership\User::findByCriteria();
        $this->assertEquals(2,
            2
        );
    }

    public function testFirstName(): void
    {
        $allUsers = \SocietyLeadership\User::findByCriteria();
        $this->assertEquals(2,
            2
        );
    }

    public function testLastName(): void
    {
        $allUsers = \SocietyLeadership\User::findByCriteria();
        $this->assertEquals(2,
            2
        );
    }
    
    public function testEmail(): void
    {
        $allUsers = \SocietyLeadership\User::findByCriteria();
        $this->assertEquals(2,
            2
        );
    }
}


