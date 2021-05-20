<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;
    protected $factory;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function setUp(): void
    {
      parent::setUp();
        \Artisan::call('migrate',['-vvv' => true]);
        \Artisan::call('passport:install',['-vvv' => true]);

      $this->setupPermissions();

      $admin = User::factory()->create();
      $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
      $admin->assignRole('Admin');
      $user = [
        'email' => $admin->email,
        'password' => 'password',
      ];
      auth()->attempt($user) ? $this->adminToken = auth()->user()->createToken('Personal Access Token')->accessToken : '';
      
      $this->factory = User::factory()->make()->toArray();
    }

    protected function setupPermissions()
    {
      Permission::create(['name' => 'users.index']);
      Permission::create(['name' => 'users.store']);
      Permission::create(['name' => 'users.update']);
      Permission::create(['name' => 'users.destroy']);

      Role::create(['name' => 'Admin'])->givePermissionTo(Permission::all());

      $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }
    public function testGETAll()
    {
       $this->withHeaders([
               'Authorization' => 'Bearer '.$this->adminToken,
           ])
           ->json(
               'GET',
               '/api/user'
           )
           ->assertStatus(200)
           ->assertHeader('Content-Type', 'application/json');
    }

    public function testGETOne()
    {
        $user = User::factory()->create();
        $this->withHeaders([
               'Authorization' => 'Bearer '.$this->adminToken,
           ])
           ->json(
               'GET',
               '/api/user/' . $user->id
           )
           ->assertStatus(200)
           ->assertHeader('Content-Type', 'application/json');
    }

    public function testPOST()
    {
        $factory = $this->factory;
        $factory['password'] = 'secret';

        $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->adminToken,
            ])
            ->json(
                'POST',
                '/api/user',
                $factory
            )
            ->assertStatus(201)
            ->assertHeader('Content-Type', 'application/json');

        $json = json_decode($response->getContent());
        $factory['id'] = $json->id;

        unset($factory['password']);
        unset($factory['email_verified_at']);

        $response->assertJson($factory);

        $this->assertDatabaseHas('users', [
            'name' => $factory['name'],
            'email' => $factory['email'],
        ]);
    }

    public function testPUT()
    {
        $this->withoutExceptionHandling();

        $factory = $this->factory;
        $user = User::factory()->create();
        $factory['id'] = $user->id;

        $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->adminToken,
            ])
            ->json(
                'PUT',
                '/api/user/'.$user->id,
                $factory
            )
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson($factory);

        $this->assertDatabaseHas('users', [
            'name' => $factory['name'],
            'email' => $factory['email'],
        ]);
    }

    public function testDELETE()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $this->withHeaders([
                'Authorization' => 'Bearer '.$this->adminToken,
            ])
            ->json(
                'DELETE',
                '/api/user/'.$user->id
            )
            ->assertStatus(200);

        $this->assertDatabaseMissing('users', [ 'id' => $user->id ]);
    }
}
