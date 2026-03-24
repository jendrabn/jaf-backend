<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\Coupon;
use Database\Seeders\ProductBrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class ApplyCouponPostTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ProductCategorySeeder::class,
            ProductBrandSeeder::class,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_apply_coupon()
    {
        $response = $this->postJson('/api/apply_coupon');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_apply_coupon()
    {
        $user = $this->createUser();
        $cart = Cart::factory()->for($user)->for($this->createProduct())->create();
        $coupon = Coupon::factory()->create([
            'code' => 'WELCOME10',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/apply_coupon', [
            'code' => $coupon->code,
            'cart_ids' => [$cart->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Coupon applied successfully')
            ->assertJsonPath('data.id', $coupon->id);

        $this->assertSame($coupon->id, session('applied_coupon'));
    }

    #[Test]
    public function returns_error_for_invalid_coupon_code()
    {
        $user = $this->createUser();
        $cart = Cart::factory()->for($user)->for($this->createProduct())->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/apply_coupon', [
            'code' => 'INVALID',
            'cart_ids' => [$cart->id],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid coupon code');
    }
}
