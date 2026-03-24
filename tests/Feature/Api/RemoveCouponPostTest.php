<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class RemoveCouponPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_remove_coupon()
    {
        $response = $this->postJson('/api/remove_coupon');

        $response->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function can_remove_coupon_from_session()
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);
        session(['applied_coupon' => 123]);

        $response = $this->postJson('/api/remove_coupon');

        $response->assertOk()
            ->assertJsonPath('message', 'Coupon removed successfully');

        $this->assertNull(session('applied_coupon'));
    }
}
