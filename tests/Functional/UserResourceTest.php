<?php

namespace App\Tests\Functional;

use App\Factory\UserFactory;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    use HasBrowser;
    use resetDatabase;
    public function testCreateUser()
    {
        $this->browser()
            ->post('/api/users', [
                'json' => [
                    'email' => 'test@test.com',
                    'password' => 'password',
                    'username' => 'test',
                ]
            ])
            ->assertStatus(201)
            ->post('/login', [
                'json' => [
                    'email' => 'test@test.com',
                    'password' => 'password',
                ]
            ])
            ->assertSuccessful();


    }
    public function testPatchToUpdateUser(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                ],
                //el tipo especifico que necesita las peticiones si queremos hacer una operacion patch
                'headers' => ['Content-Type' => 'application/merge-patch+json']
            ])
            ->assertStatus(200);
    }

}