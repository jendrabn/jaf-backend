<?php

namespace Tests\Feature\Admin;

use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TaxManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('backoffice.access');

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('backoffice.access');

        $this->actingAs($this->user);
    }

    #[Test]
    public function it_can_create_a_tax_via_ajax(): void
    {
        $response = $this->postJson(route('admin.taxes.store'), [
            'name' => 'Value Added Tax',
            'rate' => 10,
        ]);

        $response->assertCreated()
            ->assertJson(['message' => 'created']);

        $this->assertDatabaseHas('taxes', [
            'name' => 'Value Added Tax',
            'rate' => 10.00,
        ]);
    }

    #[Test]
    public function it_can_update_a_tax_via_ajax(): void
    {
        $tax = Tax::factory()->create([
            'name' => 'VAT 10',
            'rate' => 10,
        ]);

        $response = $this->putJson(route('admin.taxes.update', $tax), [
            'name' => 'VAT 5',
            'rate' => 5.5,
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'updated']);

        $this->assertDatabaseHas('taxes', [
            'id' => $tax->id,
            'name' => 'VAT 5',
            'rate' => 5.50,
        ]);
    }

    #[Test]
    public function it_can_delete_a_tax_via_ajax(): void
    {
        $tax = Tax::factory()->create();

        $response = $this->deleteJson(route('admin.taxes.destroy', $tax));

        $response->assertOk()
            ->assertJson(['message' => 'deleted']);

        $this->assertDatabaseMissing('taxes', [
            'id' => $tax->id,
        ]);
    }

    #[Test]
    public function it_can_mass_delete_taxes(): void
    {
        $taxes = Tax::factory()->count(3)->create();

        $response = $this->deleteJson(route('admin.taxes.massDestroy'), [
            'ids' => $taxes->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'deleted']);

        $this->assertDatabaseCount('taxes', 0);
    }

    #[Test]
    public function it_validates_unique_name_on_update(): void
    {
        [$firstTax, $secondTax] = Tax::factory()->count(2)->create();

        $response = $this->putJson(route('admin.taxes.update', $firstTax), [
            'name' => $secondTax->name,
            'rate' => $firstTax->rate,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }
}

