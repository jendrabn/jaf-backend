<?php

namespace Tests\Feature\Admin;

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FlashSaleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['backoffice.access', 'flash_sales.view', 'flash_sales.create', 'flash_sales.edit', 'flash_sales.delete', 'flash_sales.mass_delete'] as $permission) {
            Permission::findOrCreate($permission);
        }

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo([
            'backoffice.access',
            'flash_sales.view',
            'flash_sales.create',
            'flash_sales.edit',
            'flash_sales.delete',
            'flash_sales.mass_delete',
        ]);

        $this->actingAs($this->admin);

        ProductCategory::factory()->create();
        ProductBrand::factory()->create();
    }

    #[Test]
    public function it_can_create_a_flash_sale_with_products(): void
    {
        $product = Product::factory()->create();

        $payload = [
            'name' => 'Midnight Flash Sale',
            'description' => 'Limited time deal',
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'is_active' => 1,
            'products' => [
                [
                    'product_id' => $product->id,
                    'flash_price' => '125000.00',
                    'stock_flash' => 50,
                    'max_qty_per_user' => 2,
                ],
            ],
        ];

        $response = $this->post(route('admin.flash-sales.store'), $payload);

        $response->assertRedirect(route('admin.flash-sales.index'));
        $this->assertDatabaseHas('flash_sales', [
            'name' => 'Midnight Flash Sale',
            'is_active' => true,
        ]);

        $flashSale = FlashSale::first();

        $this->assertDatabaseHas('flash_sale_products', [
            'flash_sale_id' => $flashSale->id,
            'product_id' => $product->id,
            'flash_price' => '125000.00',
            'stock_flash' => 50,
            'max_qty_per_user' => 2,
        ]);
    }

    #[Test]
    public function it_can_mass_delete_flash_sales(): void
    {
        $flashSales = FlashSale::factory()->count(2)->create();

        $response = $this->deleteJson(route('admin.flash-sales.massDestroy'), [
            'ids' => $flashSales->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Flash sales deleted successfully.']);

        $this->assertDatabaseCount('flash_sales', 0);
    }

    #[Test]
    public function it_validates_overlapping_schedules(): void
    {
        FlashSale::factory()->create([
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
        ]);

        $product = Product::factory()->create();

        $payload = [
            'name' => 'Overlap Event',
            'description' => 'Should fail',
            'start_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(2)->subHour()->format('Y-m-d H:i:s'),
            'is_active' => 1,
            'products' => [
                [
                    'product_id' => $product->id,
                    'flash_price' => '99000.00',
                    'stock_flash' => 25,
                    'max_qty_per_user' => 1,
                ],
            ],
        ];

        $response = $this->from(route('admin.flash-sales.create'))
            ->post(route('admin.flash-sales.store'), $payload);

        $response->assertSessionHasErrors(['start_at', 'end_at']);
        $this->assertDatabaseCount('flash_sales', 1);
    }
}
