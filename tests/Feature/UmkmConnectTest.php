<?php

namespace Tests\Feature;

use App\Jobs\SendBroadcastEmailJob;
use App\Jobs\SendEmailReceiptJob;
use App\Livewire\CustomerIndex;
use App\Livewire\TransactionCreate;
use App\Mail\BroadcastPromoMail;
use App\Mail\DigitalReceiptMail;
use App\Models\Broadcast;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class UmkmConnectTest extends TestCase
{
    use RefreshDatabase;

    private Store $store1;
    private Store $store2;
    private User $owner1;
    private User $cashier1;
    private User $owner2;
    private Service $service1;
    private Service $service2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store1 = Store::create([
            'name' => 'CleanShoe Studio',
            'business_type' => 'Cuci Sepatu & Tas',
            'address' => 'Jakarta Selatan',
            'sender_name' => 'CleanShoe Studio',
            'sender_email' => 'halo@cleanshoe.test',
            'point_value_idr' => 100,
            'vip_points_threshold' => 1000,
        ]);

        $this->store2 = Store::create([
            'name' => 'Pangkas Rapi',
            'business_type' => 'Barbershop',
            'address' => 'Bandung',
            'sender_name' => 'Pangkas Rapi',
            'sender_email' => 'halo@pangkas.test',
            'point_value_idr' => 100,
            'vip_points_threshold' => 1000,
        ]);

        $this->owner1 = User::create([
            'store_id' => $this->store1->id,
            'name' => 'Owner Satu',
            'email' => 'owner1@test.com',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->cashier1 = User::create([
            'store_id' => $this->store1->id,
            'name' => 'Kasir Satu',
            'email' => 'kasir1@test.com',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->owner2 = User::create([
            'store_id' => $this->store2->id,
            'name' => 'Owner Dua',
            'email' => 'owner2@test.com',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->service1 = Service::create([
            'store_id' => $this->store1->id,
            'name' => 'Deep Clean Sepatu',
            'price' => 75000,
            'points_reward' => 25,
            'is_active' => true,
        ]);

        $this->service2 = Service::create([
            'store_id' => $this->store2->id,
            'name' => 'Haircut Store 2',
            'price' => 50000,
            'points_reward' => 15,
            'is_active' => true,
        ]);
    }

    public function test_rbac_middleware_restricts_cashier_from_owner_routes(): void
    {
        $this->actingAs($this->cashier1);

        $this->get('/dashboard')->assertOk();
        $this->get('/customers')->assertOk();
        $this->get('/transactions/create')->assertOk();
        $this->get('/transactions')->assertOk();

        $this->get('/broadcasts')->assertForbidden();
        $this->get('/reports')->assertForbidden();
        $this->get('/services')->assertForbidden();
        $this->get('/staff')->assertForbidden();
        $this->get('/settings')->assertForbidden();

        $this->actingAs($this->owner1);
        $this->get('/broadcasts')->assertOk();
        $this->get('/reports')->assertOk();
        $this->get('/services')->assertOk();
        $this->get('/staff')->assertOk();
        $this->get('/settings')->assertOk();
    }

    public function test_multi_tenant_isolation_prevents_cross_store_access(): void
    {
        $custStore1 = Customer::create([
            'store_id' => $this->store1->id,
            'name' => 'Pelanggan Toko 1',
            'whatsapp_number' => '628111111111',
            'email' => 'c1@test.com',
            'total_points' => 100,
        ]);

        $custStore2 = Customer::create([
            'store_id' => $this->store2->id,
            'name' => 'Pelanggan Toko 2',
            'whatsapp_number' => '628222222222',
            'email' => 'c2@test.com',
            'total_points' => 200,
        ]);

        $this->actingAs($this->owner1);

        $this->assertCount(1, Customer::all());
        $this->assertTrue(Customer::first()->is($custStore1));

        $this->get("/customers/{$custStore1->id}")->assertOk();
        $this->get("/customers/{$custStore2->id}")->assertNotFound();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::create([
            'store_id' => $this->store1->id,
            'name' => 'Kasir Nonaktif',
            'email' => 'nonaktif@test.com',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $inactive->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_new_store_registration_creates_store_and_owner(): void
    {
        $response = $this->post('/register', [
            'store_name' => 'Laundry Kilat Jaya',
            'business_type' => 'Laundry Kiloan & Satuan',
            'name' => 'Surya Pemilik',
            'email' => 'surya@laundry.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('stores', ['name' => 'Laundry Kilat Jaya']);
        $this->assertDatabaseHas('users', [
            'email' => 'surya@laundry.test',
            'role' => 'owner',
        ]);
    }

    public function test_customer_index_livewire_registers_and_searches_customers(): void
    {
        $this->actingAs($this->cashier1);

        Livewire::test(CustomerIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Raka Wijaya')
            ->set('whatsapp_number', '081234567890')
            ->set('email', 'raka@test.com')
            ->call('saveCustomer')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'store_id' => $this->store1->id,
            'name' => 'Raka Wijaya',
            'whatsapp_number' => '6281234567890',
            'email' => 'raka@test.com',
        ]);

        Livewire::test(CustomerIndex::class)
            ->set('search', 'Raka')
            ->assertSee('Raka Wijaya');
    }

    public function test_transaction_create_calculates_points_discount_and_dispatches_receipt_job(): void
    {
        Bus::fake([SendEmailReceiptJob::class]);

        $customer = Customer::create([
            'store_id' => $this->store1->id,
            'name' => 'Budi Loyal',
            'whatsapp_number' => '6281200001111',
            'email' => 'budi@test.com',
            'total_points' => 200,
        ]);

        $this->actingAs($this->cashier1);

        Livewire::test(TransactionCreate::class)
            ->call('selectCustomer', $customer->id)
            ->call('addService', $this->service1->id)
            ->set('redeemPoints', true)
            ->set('pointsToUse', 150)
            ->call('checkout')
            ->assertHasNoErrors();

        $tx = Transaction::withoutGlobalScopes()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($tx);
        $this->assertEquals(75000.00, (float) $tx->subtotal);
        $this->assertEquals(150, $tx->points_used);
        $this->assertEquals(15000.00, (float) $tx->discount_amount);
        $this->assertEquals(60000.00, (float) $tx->total_paid);
        $this->assertEquals(25, $tx->points_earned);

        $customer->refresh();
        // 200 - 150 + 25 = 75
        $this->assertEquals(75, $customer->total_points);
        $this->assertNotNull($customer->last_visit_at);

        Bus::assertDispatched(SendEmailReceiptJob::class, function (SendEmailReceiptJob $job) use ($tx): bool {
            return $job->transactionId === $tx->id;
        });
    }

    public function test_send_email_receipt_job_sends_digital_receipt_mail(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'store_id' => $this->store1->id,
            'name' => 'Siti Receipt',
            'whatsapp_number' => '6281200002222',
            'email' => 'siti@test.com',
            'total_points' => 50,
        ]);

        $tx = Transaction::create([
            'store_id' => $this->store1->id,
            'customer_id' => $customer->id,
            'user_id' => $this->cashier1->id,
            'invoice_code' => 'INV-TEST-001',
            'service_detail' => [
                ['service_id' => $this->service1->id, 'name' => 'Deep Clean Sepatu', 'price' => 75000, 'qty' => 1, 'points_reward' => 25, 'subtotal' => 75000],
            ],
            'subtotal' => 75000,
            'points_used' => 0,
            'discount_amount' => 0,
            'total_paid' => 75000,
            'points_earned' => 25,
        ]);

        (new SendEmailReceiptJob($tx->id))->handle(app(\App\Services\StoreMailer::class));

        Mail::assertSent(DigitalReceiptMail::class, function (DigitalReceiptMail $mail) use ($customer): bool {
            return $mail->hasTo($customer->email);
        });

        $this->assertNotNull($tx->fresh()->receipt_sent_at);
    }

    public function test_broadcast_segmentation_and_job_sends_only_to_matching_store_segment(): void
    {
        Mail::fake();

        $vipCustomer = Customer::create([
            'store_id' => $this->store1->id,
            'name' => 'VIP One',
            'whatsapp_number' => '6281110001',
            'email' => 'vip1@test.com',
            'total_points' => 1200,
            'last_visit_at' => now()->subDays(2),
        ]);

        $passiveCustomer = Customer::create([
            'store_id' => $this->store1->id,
            'name' => 'Passive One',
            'whatsapp_number' => '6281110002',
            'email' => 'passive1@test.com',
            'total_points' => 50,
            'last_visit_at' => now()->subDays(45),
        ]);

        // Store 2 VIP customer should NEVER receive Store 1 broadcast
        Customer::create([
            'store_id' => $this->store2->id,
            'name' => 'VIP Store 2',
            'whatsapp_number' => '6282220001',
            'email' => 'vip2@test.com',
            'total_points' => 2000,
            'last_visit_at' => now()->subDays(2),
        ]);

        $broadcast = Broadcast::create([
            'store_id' => $this->store1->id,
            'subject' => 'Kangen Kamu!',
            'message' => 'Ayo mampir lagi ada diskon spesial untuk pelanggan setia kami.',
            'target_segment' => Broadcast::SEGMENT_INACTIVE_30_DAYS,
            'status' => 'pending',
        ]);

        (new SendBroadcastEmailJob($broadcast->id))->handle(app(\App\Services\StoreMailer::class));

        Mail::assertSent(BroadcastPromoMail::class, 1);
        Mail::assertSent(BroadcastPromoMail::class, fn ($mail) => $mail->hasTo($passiveCustomer->email));
        Mail::assertNotSent(BroadcastPromoMail::class, fn ($mail) => $mail->hasTo($vipCustomer->email));

        $broadcast->refresh();
        $this->assertEquals('sent', $broadcast->status);
        $this->assertEquals(1, $broadcast->recipients_count);
    }

    public function test_scheduled_broadcast_command_dispatches_due_campaigns(): void
    {
        Bus::fake([SendBroadcastEmailJob::class]);

        $due = Broadcast::create([
            'store_id' => $this->store1->id,
            'subject' => 'Due Campaign',
            'message' => 'Promo siap kirim sekarang juga untuk semua pelanggan.',
            'target_segment' => Broadcast::SEGMENT_ALL,
            'scheduled_at' => now()->subMinute(),
            'status' => 'pending',
        ]);

        $future = Broadcast::create([
            'store_id' => $this->store1->id,
            'subject' => 'Future Campaign',
            'message' => 'Promo masa depan yang belum waktunya dikirim.',
            'target_segment' => Broadcast::SEGMENT_ALL,
            'scheduled_at' => now()->addHour(),
            'status' => 'pending',
        ]);

        $this->artisan('broadcasts:process-scheduled')->assertExitCode(0);

        Bus::assertDispatched(SendBroadcastEmailJob::class, fn ($job) => $job->broadcastId === $due->id);
        Bus::assertNotDispatched(SendBroadcastEmailJob::class, fn ($job) => $job->broadcastId === $future->id);
    }

    public function test_owner_can_manage_services_staff_settings_broadcasts_and_reports(): void
    {
        Bus::fake([SendBroadcastEmailJob::class, SendEmailReceiptJob::class]);

        $customer = Customer::create([
            'store_id' => $this->store1->id,
            'name' => 'Pelanggan Analitik',
            'whatsapp_number' => '6281234567899',
            'email' => 'analitik@test.com',
            'total_points' => 1100,
            'last_visit_at' => now()->subDay(),
        ]);

        $tx = Transaction::create([
            'store_id' => $this->store1->id,
            'customer_id' => $customer->id,
            'user_id' => $this->cashier1->id,
            'invoice_code' => 'INV-RPT-001',
            'service_detail' => [
                ['service_id' => $this->service1->id, 'name' => 'Deep Clean Sepatu', 'price' => 75000, 'qty' => 2, 'points_reward' => 25, 'subtotal' => 150000],
            ],
            'subtotal' => 150000,
            'points_used' => 0,
            'discount_amount' => 0,
            'total_paid' => 150000,
            'points_earned' => 50,
        ]);

        $this->actingAs($this->owner1);

        // Services CRUD
        $this->post('/services', [
            'name' => 'Express Wash',
            'price' => 35000,
            'points_reward' => 10,
        ])->assertRedirect('/services');
        $this->assertDatabaseHas('services', ['store_id' => $this->store1->id, 'name' => 'Express Wash']);

        // Staff creation & toggle
        $this->post('/staff', [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@test.com',
            'role' => 'cashier',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/staff');
        $newStaff = User::withoutGlobalScopes()->where('email', 'kasirbaru@test.com')->firstOrFail();
        $this->patch("/staff/{$newStaff->id}/toggle-status")->assertRedirect('/staff');
        $this->assertFalse($newStaff->fresh()->is_active);

        // Store settings update
        $this->put('/settings', [
            'name' => 'CleanShoe Studio Updated',
            'business_type' => 'Cuci Sepatu & Tas',
            'address' => 'Jakarta',
            'sender_name' => 'CleanShoe',
            'sender_email' => 'noreply@cleanshoe.test',
            'point_value_idr' => 200,
            'vip_points_threshold' => 1500,
        ])->assertRedirect('/settings');
        $this->assertEquals('CleanShoe Studio Updated', $this->store1->fresh()->name);

        // Broadcast immediate dispatch
        $this->post('/broadcasts', [
            'subject' => 'Promo Weekend',
            'message' => 'Diskon 20 persen khusus akhir pekan ini untuk member setia.',
            'target_segment' => Broadcast::SEGMENT_ALL,
        ])->assertRedirect('/broadcasts');
        Bus::assertDispatched(SendBroadcastEmailJob::class);

        // Reports daily, weekly, monthly
        $this->get('/reports?period=daily')->assertOk()->assertSee('Pelanggan Analitik');
        $this->get('/reports?period=weekly')->assertOk();
        $this->get('/reports?period=monthly')->assertOk();

        // Transaction detail & resend receipt
        $this->get("/transactions/{$tx->id}")->assertOk()->assertSee('INV-RPT-001');
        $this->post("/transactions/{$tx->id}/resend-receipt")->assertRedirect();
        Bus::assertDispatched(SendEmailReceiptJob::class);
    }

    public function test_database_seeder_populates_multi_tenant_demo_data(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'owner@umkm.com', 'role' => 'owner']);
        $this->assertDatabaseHas('users', ['email' => 'kasir@umkm.com', 'role' => 'cashier']);
        $this->assertDatabaseHas('users', ['email' => 'owner2@umkm.com', 'role' => 'owner']);
        $this->assertGreaterThanOrEqual(2, Store::count());
    }
}
