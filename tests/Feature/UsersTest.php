<?php

namespace Tests\Feature;

use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $routeBaseName = "users";

    protected function getIndexUrl()
    {
        return route("users.index");
    }

    protected function getCreateUrl()
    {
        return route("users.store");
    }

    protected function getUpdateUrl($id)
    {
        return route("users.update", ['user' => $id]);
    }

    protected function getDestroyUrl($id)
    {
        return route("users.destroy", ['user' => $id]);
    }

    public function testCheckIfUsersArePaginated()
    {
        $indexUrl = $this->getIndexUrl();

        $this->actAsRandomUser();

        $this->generateUsers(30);

        $this->getJson($indexUrl)
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $this->getJson("{$indexUrl}?limit=25")
            ->assertJsonCount(25, 'data');

        $appUrl = config("app.url");

        $this->getJson("{$indexUrl}?limit=30")
            ->assertJsonCount(10, 'data')
            ->assertJsonFragment([
                "meta" => [
                    "current_page" => 1,
                    "from" => 1,
                    "last_page" => 4,
                    "path" => "{$appUrl}/api/users",
                    "per_page" => 10,
                    "to" => 10,
                    "total" => 31
                ]
            ]);

        $this->getJson('/api/users?page=2')
            ->assertJsonFragment([
                "meta" => [
                    "current_page" => 2,
                    "from" => 11,
                    "last_page" => 4,
                    "path" => "{$appUrl}/api/users",
                    "per_page" => 10,
                    "to" => 20,
                    "total" => 31,
                ]
            ]);
    }

    public function testAnonymouseUserForbidden()
    {
        $user = factory(User::class)->create();

        $this->getJson($this->getIndexUrl())
            ->assertStatus(401);

        $this->postJson($this->getCreateUrl())
            ->assertStatus(401);

        $this->putJson($this->getUpdateUrl($user->id))
            ->assertStatus(401);

        $this->deleteJson($this->getDestroyUrl($user->id))
            ->assertStatus(401);
    }

    public function testUserCanBeCreatedByAdminOnly()
    {
        $user = factory(User::class)->make();

        $this->actAsRandomUser();

        $this->postJson($this->getCreateUrl(), [
            'name' => 'name#1#test',
            'email' => 'email@gmail.com',
            'permissions' => User::PERMISSIONS_USER,
            'password' => 'secret'
        ])
            ->assertStatus(403);

        $this->actAsRandomAdmin();

        $this->postJson($this->getCreateUrl(), [
            'name' => $user->name,
            'password' => 'secret',
            'permissions' => $user->permissions,
            'email' => $user->email,
        ])
            ->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => $user->name,
            'permissions' => $user->permissions,
            'email' => $user->email,
        ]);
    }

    public function testUserCanBeUpdatedByOwnerOrAdmin()
    {
        $user = factory(User::class)->create();

        $this->actAsRandomUser();

        $newParams = [
            'name' => "User #1",
            'email' => "user_1@gmail.com",
        ];

        $this->putJson($this->getUpdateUrl($user->id), $newParams)
            ->assertStatus(403);

        $this->actingAs($user);

        $this->putJson($this->getUpdateUrl($user->id), $newParams)
            ->assertStatus(203);

        $updatedUser = User::find($user->id);

        $this->assertSame($updatedUser->name, $newParams['name']);
        $this->assertSame($updatedUser->email, $newParams['email']);

        $newParams1 = [
            'name' => "User #2",
            'email' => "user_2@gmail.com",
        ];

        $this->actAsRandomAdmin();

        $this->putJson($this->getUpdateUrl($user->id), $newParams1)
            ->assertStatus(203);

        $updatedUser = User::find($user->id);

        $this->assertSame($updatedUser->name, $newParams1['name']);
        $this->assertSame($updatedUser->email, $newParams1['email']);
    }

    public function testAnonymousAUserCannotDeleteUser()
    {
        $user = factory(User::class)->create();

        $this->actAsRandomUser();

        $this->deleteJson($this->getDestroyUrl($user->id))
            ->assertStatus(403);
    }

    public function testAdminCanDeleteUser()
    {
        $user = factory(User::class)->create();

        $this->actAsRandomAdmin();

        $this->deleteJson($this->getDestroyUrl($user->id))
            ->assertNoContent()
            ->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function testUserCanBeDestroyeddByAdminOnly()
    {
        $user = factory(User::class)->create();

        $this->actAsRandomUser();

        $this->deleteJson($this->getDestroyUrl($user->id))
            ->assertStatus(403);
    }

    public function testUserCanCreateTransaction()
    {
        $this->actAsRandomUser();

        $url = route("store_user_transaction", ['user' => auth()->id()]);

        $this->postJson($url, [
            'amount' => 100,
            'type' => Transaction::TYPE_DEBIT
        ])->assertStatus(201)
            ->assertJsonFragment(['debit_sum' => 100]);

        $this->postJson($url, [
            'amount' => 110,
            'type' => Transaction::TYPE_DEBIT
        ])->assertStatus(201)
            ->assertJsonFragment(['debit_sum' => 210]);

        $this->postJson($url, [
            'amount' => 110,
            'type' => Transaction::TYPE_CREDIT
        ])->assertStatus(201)
            ->assertJsonFragment(['debit_sum' => 210]);
    }

    public function testStoreUserValidationsWorks()
    {
        $this->actAsRandomAdmin();

        $this->postJson($this->getCreateUrl(), [])->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ],
                'email' => [
                    'The email field is required.'
                ],
                'password' => [
                    'The password field is required.'
                ],
                'permissions' => [
                    'The permissions field is required.'
                ]
            ]
        ])
            ->assertStatus(422);

        factory(User::class)->create(['name' => "user1", 'email' => 'test111@email.com']);

        $this->postJson($this->getCreateUrl(), [
            'name' => 'user1',
            'email' => 'test111@email.com',
            'permissions' => User::PERMISSIONS_USER,
            'password' => 'secret'
        ])->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name has already been taken.'
                ],
                'email' => [
                    'The email has already been taken.'
                ],
            ]
        ])
            ->assertStatus(422);

        $this->postJson($this->getCreateUrl(), [
            'name' => 'user111',
            'email' => 'text',
            'permissions' => "text1",
            'password' => 'secret'
        ])->assertJsonFragment([
            'errors' => [
                'permissions' => [
                    'The selected permissions is invalid.'
                ],
                'email' => [
                    'The email must be a valid email address.'
                ],
            ]
        ])
            ->assertStatus(422);
    }

    public function testUpdateUserValidationsWorks()
    {
        $this->actAsRandomAdmin();

        factory(User::class)->create(['name' => "user1", 'email' => 'test111@email.com']);

        $user = factory(User::class)->create(['name' => "user2", 'email' => 'test22@email.com']);

        $this->putJson($this->getUpdateUrl($user->id), [
            'name' => 'user1',
            'email' => 'test111@email.com',
        ])->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name has already been taken.'
                ],
                'email' => [
                    'The email has already been taken.'
                ],
            ]
        ])
            ->assertStatus(422);

        $this->putJson($this->getUpdateUrl($user->id), [
            'name' => 'user2',
            'email' => 'test22@email.com',
        ])->assertStatus(203);
    }

    public function testStoreUserTransactionValidationsWorks()
    {
        $this->actAsRandomUser();

        $url = route("store_user_transaction", ['user' => auth()->id()]);

        $this->postJson($url, [
        ])
            ->assertJsonFragment([
                'errors' => [
                    'amount' => [
                        'The amount field is required.'
                    ],
                    'type' => [
                        'The type field is required.'
                    ],
                ]
            ])
            ->assertStatus(422);

        $this->postJson($url, [
            'amount' => "asd",
            'type' => 'pam'
        ])->assertJsonFragment([
            'errors' => [
                'amount' => [
                    'The amount must be an integer.'
                ],
                'type' => [
                    'The selected type is invalid.'
                ],
            ]
        ])
            ->assertStatus(422);

        $this->postJson($url, [
            'amount' => -30,
            'type' => Transaction::TYPE_DEBIT
        ])->assertJsonFragment([
                'errors' => [
                    'amount' => [
                        'The amount must be at least 0.'
                    ],
                ]
            ])
            ->assertStatus(422);
    }

    protected function generateUsers($usersCount = 10)
    {
        $faker = $this->faker;
        factory(User::class, $usersCount)->create()->each(function ($user) use ($faker) {
            $transactionsCount = $faker->numberBetween(10, 30);
            for ($i = 0; $i < $transactionsCount; $i++) {
                $user->transactions()->save(factory(Transaction::class)->make([
                    'user_id' => $user->id
                ]));
            }
        });
    }
}
