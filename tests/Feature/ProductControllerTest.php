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
use App\Models\Product;

class ProductControllerTest extends TestCase
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
      
      $this->factory = Product::factory()->make()->toArray();
    }

    protected function setupPermissions()
    {
      Permission::create(['name' => 'products.index']);
      Permission::create(['name' => 'products.store']);
      Permission::create(['name' => 'products.update']);
      Permission::create(['name' => 'products.destroy']);

      Role::create(['name' => 'Admin'])->givePermissionTo(Permission::all());

      $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }
    public function testGETAllProducts()
    {
        $this->withoutExceptionHandling();
       $this->withHeaders([
               'Authorization' => 'Bearer '.$this->adminToken,
           ])
           ->json(
               'GET',
               '/api/product'
           )
           ->assertStatus(200)
           ->assertHeader('Content-Type', 'application/json');
    }

    public function testGETOneProduct()
    {
        $product = Product::factory()->create();
        $this->withHeaders([
               'Authorization' => 'Bearer '.$this->adminToken,
           ])
           ->json(
               'GET',
               '/api/product/' . $product->id
           )
           ->assertStatus(200)
           ->assertHeader('Content-Type', 'application/json');
    }

    public function testPOSTProduct()
    {
        $factory = $this->factory;

        $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->adminToken,
            ])
            ->json(
                'POST',
                '/api/product',
                $factory
            )
            ->assertStatus(201)
            ->assertHeader('Content-Type', 'application/json');

        $json = json_decode($response->getContent());
        $factory['id'] = $json->id;

        $response->assertJson($factory);

        $this->assertDatabaseHas('products', [
            'product_name' => $factory['product_name'],
            'quantity'     => $factory['quantity'],
            'unit_price'   => $factory['unit_price'],
            'currency'     => $factory['currency'],
        ]);
    }

    public function testPUTProduct()
    {
        $this->withoutExceptionHandling();

        $factory = $this->factory;
        $product = Product::factory()->create();
        $factory['id'] = $product->id;

        $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->adminToken,
            ])
            ->json(
                'PUT',
                '/api/product/'.$product->id,
                $factory
            )
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson($factory);

        $this->assertDatabaseHas('products', [
            'product_name' => $factory['product_name'],
            'quantity'     => $factory['quantity'],
            'unit_price'   => $factory['unit_price'],
            'currency'     => $factory['currency'],
        ]);
    }

    public function testDELETEProduct()
    {
        $this->withoutExceptionHandling();

        $product = Product::factory()->create();

        $this->withHeaders([
                'Authorization' => 'Bearer '.$this->adminToken,
            ])
            ->json(
                'DELETE',
                '/api/product/'.$product->id
            )
            ->assertStatus(200);

        $this->assertDatabaseMissing('products', [ 'id' => $product->id ]);
    }
}
