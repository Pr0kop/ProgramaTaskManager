<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Factory;

use App\Domain\User\Entity\User;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

final class UserFactoryTest extends TestCase
{
    private UserFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new UserFactory();
    }

    private function validData(): array
    {
        return [
            'id'       => 1,
            'name'     => 'John Doe',
            'username' => 'johndoe',
            'email'    => 'john@example.com',
            'phone'    => '123-456-789',
            'website'  => 'johndoe.com',
        ];
    }

    // --- Instance ---

    public function testReturnsUserInstance(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token123');

        $this->assertInstanceOf(User::class, $user);
    }

    // --- Fields ---

    public function testSetsNameCorrectly(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token123');

        $this->assertSame('John Doe', $user->getName());
    }

    public function testSetsUsernameCorrectly(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token123');

        $this->assertSame('johndoe', $user->getUsername());
    }

    public function testSetsEmailCorrectly(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token123');

        $this->assertSame('john@example.com', $user->getEmail()->value);
    }

    public function testSetsExternalIdCorrectly(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token123');

        $this->assertSame(1, $user->getExternalId());
    }

    public function testSetsPhoneCorrectly(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token123');

        $this->assertSame('123-456-789', $user->getPhone());
    }

    public function testSetsWebsiteCorrectly(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token123');

        $this->assertSame('johndoe.com', $user->getWebsite());
    }

    public function testSetsHashedPassword(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed_pw', 'token123');

        $this->assertSame('hashed_pw', $user->getPassword());
    }

    public function testSetsApiToken(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'my_token');

        $this->assertSame('my_token', $user->getApiToken());
    }

    // --- Role ---

    public function testDefaultRoleIsMember(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token');

        $this->assertSame(UserRole::Member, $user->getRole());
    }

    public function testAdminRoleIsSetWhenProvided(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token', UserRole::Admin);

        $this->assertSame(UserRole::Admin, $user->getRole());
        $this->assertTrue($user->isAdmin());
    }

    public function testMemberRoleIsNotAdmin(): void
    {
        $user = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token', UserRole::Member);

        $this->assertFalse($user->isAdmin());
    }

    // --- UUID ---

    public function testGeneratesUniqueIds(): void
    {
        $user1 = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token1');
        $user2 = $this->factory->createFromJsonPlaceholder($this->validData(), 'hashed', 'token2');

        $this->assertNotSame($user1->getId()->value, $user2->getId()->value);
    }

    // --- Optional fields ---

    public function testPhoneIsNullableWhenMissing(): void
    {
        $data = $this->validData();
        unset($data['phone']);

        $user = $this->factory->createFromJsonPlaceholder($data, 'hashed', 'token');

        $this->assertNull($user->getPhone());
    }

    public function testWebsiteIsNullableWhenMissing(): void
    {
        $data = $this->validData();
        unset($data['website']);

        $user = $this->factory->createFromJsonPlaceholder($data, 'hashed', 'token');

        $this->assertNull($user->getWebsite());
    }

    // --- Invalid data ---

    public function testThrowsOnEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = $this->validData();
        $data['name'] = '   ';

        $this->factory->createFromJsonPlaceholder($data, 'hashed', 'token');
    }

    public function testThrowsOnEmptyUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = $this->validData();
        $data['username'] = '';

        $this->factory->createFromJsonPlaceholder($data, 'hashed', 'token');
    }

    public function testThrowsOnInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = $this->validData();
        $data['email'] = 'not-an-email';

        $this->factory->createFromJsonPlaceholder($data, 'hashed', 'token');
    }

    public function testThrowsOnEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = $this->validData();
        $data['email'] = '';

        $this->factory->createFromJsonPlaceholder($data, 'hashed', 'token');
    }
}
