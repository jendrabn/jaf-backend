<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardRecentDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate('backoffice.access');
        Permission::findOrCreate('dashboard.view');
        Role::findOrCreate(User::ROLE_ADMIN);
        Role::findOrCreate(User::ROLE_USER);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(User::ROLE_ADMIN);
        $this->admin->givePermissionTo(['backoffice.access', 'dashboard.view']);
    }

    #[Test]
    public function it_displays_recent_entities_on_dashboard(): void
    {
        $customer = User::factory()->create(['name' => 'Customer One']);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'total_price' => 125000,
            'shipping_cost' => 10000,
            'status' => Order::STATUS_COMPLETED,
        ]);

        $message = ContactMessage::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $log = AuditLog::query()->create([
            'description' => 'admin:logged_in',
            'event' => 'login',
            'user_id' => $this->admin->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.home'));

        $response->assertOk()
            ->assertViewHas('recent_orders', function ($orders) use ($order) {
                return $orders->contains('id', $order->id);
            })
            ->assertViewHas('recent_contact_messages', function ($messages) use ($message) {
                return $messages->contains('id', $message->id);
            })
            ->assertViewHas('recent_audit_logs', function ($logs) use ($log) {
                return $logs->contains('id', $log->id);
            });

        $response->assertSee("#{$order->id}")
            ->assertSee('Jane Doe')
            ->assertSee('admin:logged_in');
    }
}
