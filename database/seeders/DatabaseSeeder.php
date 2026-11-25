<?php

namespace Database\Seeders;

use App\Models\Broadcast;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // TENANT 1: CleanShoe & Care Studio
        // ==========================================
        $store1 = Store::create([
            'name' => 'CleanShoe & Care Studio',
            'business_type' => 'Cuci Sepatu & Tas',
            'address' => 'Jl. Kemang Raya No. 45, Jakarta Selatan',
            'sender_name' => 'CleanShoe & Care Studio',
            'sender_email' => 'halo@cleanshoe.id',
            'point_value_idr' => 100,
            'vip_points_threshold' => 1000,
        ]);

        $owner1 = User::create([
            'store_id' => $store1->id,
            'name' => 'Andi Pratama (Owner)',
            'email' => 'owner@umkm.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $cashier1 = User::create([
            'store_id' => $store1->id,
            'name' => 'Rina Maharani',
            'email' => 'kasir@umkm.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $cashier2 = User::create([
            'store_id' => $store1->id,
            'name' => 'Dimas Saputra',
            'email' => 'dimas@umkm.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $srv1 = Service::create([
            'store_id' => $store1->id,
            'name' => 'Fast Clean Sepatu',
            'price' => 45000,
            'points_reward' => 15,
            'is_active' => true,
        ]);

        $srv2 = Service::create([
            'store_id' => $store1->id,
            'name' => 'Deep Clean Canvas & Suede',
            'price' => 75000,
            'points_reward' => 25,
            'is_active' => true,
        ]);

        $srv3 = Service::create([
            'store_id' => $store1->id,
            'name' => 'Unyellowing & Repaint Midsole',
            'price' => 120000,
            'points_reward' => 40,
            'is_active' => true,
        ]);

        $srv4 = Service::create([
            'store_id' => $store1->id,
            'name' => 'Bag Care Leather Premium',
            'price' => 150000,
            'points_reward' => 50,
            'is_active' => true,
        ]);

        $srv5 = Service::create([
            'store_id' => $store1->id,
            'name' => 'Water Repellent Coating',
            'price' => 35000,
            'points_reward' => 10,
            'is_active' => true,
        ]);

        $budi = Customer::create([
            'store_id' => $store1->id,
            'name' => 'Budi Santoso',
            'whatsapp_number' => '6281234560001',
            'email' => 'budi.santoso@example.com',
            'birth_date' => '1998-08-17',
            'total_points' => 1250,
            'last_visit_at' => now()->subHours(3),
        ]);

        $siti = Customer::create([
            'store_id' => $store1->id,
            'name' => 'Siti Rahma',
            'whatsapp_number' => '6285798760002',
            'email' => 'siti.rahma@example.com',
            'birth_date' => '2000-02-14',
            'total_points' => 420,
            'last_visit_at' => now()->subDays(2),
        ]);

        $ahmad = Customer::create([
            'store_id' => $store1->id,
            'name' => 'Ahmad Fauzi',
            'whatsapp_number' => '6281311110003',
            'email' => 'ahmad.fauzi@example.com',
            'birth_date' => '1997-11-22',
            'total_points' => 160,
            'last_visit_at' => now()->subDays(42),
        ]);

        $dewi = Customer::create([
            'store_id' => $store1->id,
            'name' => 'Dewi Lestari',
            'whatsapp_number' => '6281922220004',
            'email' => 'dewi.lestari@example.com',
            'birth_date' => '1995-05-09',
            'total_points' => 1080,
            'last_visit_at' => now()->subDays(1),
        ]);

        $reza = Customer::create([
            'store_id' => $store1->id,
            'name' => 'Reza Gunawan',
            'whatsapp_number' => '6281533330005',
            'email' => 'reza.gunawan@example.com',
            'birth_date' => null,
            'total_points' => 75,
            'last_visit_at' => now()->subDays(35),
        ]);

        $nadia = Customer::create([
            'store_id' => $store1->id,
            'name' => 'Nadia Putri',
            'whatsapp_number' => '6281744440006',
            'email' => null,
            'birth_date' => '2001-09-12',
            'total_points' => 310,
            'last_visit_at' => now()->subDays(4),
        ]);

        $sampleTransactions = [
            [
                'customer' => $budi,
                'user' => $cashier1,
                'invoice_code' => 'INV-1-20261125-001',
                'items' => [
                    ['service_id' => $srv3->id, 'name' => $srv3->name, 'price' => 120000, 'qty' => 1, 'points_reward' => 40, 'subtotal' => 120000],
                    ['service_id' => $srv5->id, 'name' => $srv5->name, 'price' => 35000, 'qty' => 1, 'points_reward' => 10, 'subtotal' => 35000],
                ],
                'subtotal' => 155000,
                'points_used' => 0,
                'discount_amount' => 0,
                'total_paid' => 155000,
                'points_earned' => 50,
                'at' => now()->subHours(3),
            ],
            [
                'customer' => $dewi,
                'user' => $cashier1,
                'invoice_code' => 'INV-1-20261124-002',
                'items' => [
                    ['service_id' => $srv4->id, 'name' => $srv4->name, 'price' => 150000, 'qty' => 2, 'points_reward' => 50, 'subtotal' => 300000],
                ],
                'subtotal' => 300000,
                'points_used' => 200,
                'discount_amount' => 20000,
                'total_paid' => 280000,
                'points_earned' => 100,
                'at' => now()->subDays(1),
            ],
            [
                'customer' => $siti,
                'user' => $cashier2,
                'invoice_code' => 'INV-1-20261123-003',
                'items' => [
                    ['service_id' => $srv2->id, 'name' => $srv2->name, 'price' => 75000, 'qty' => 2, 'points_reward' => 25, 'subtotal' => 150000],
                ],
                'subtotal' => 150000,
                'points_used' => 0,
                'discount_amount' => 0,
                'total_paid' => 150000,
                'points_earned' => 50,
                'at' => now()->subDays(2),
            ],
            [
                'customer' => $nadia,
                'user' => $cashier1,
                'invoice_code' => 'INV-1-20261121-004',
                'items' => [
                    ['service_id' => $srv1->id, 'name' => $srv1->name, 'price' => 45000, 'qty' => 2, 'points_reward' => 15, 'subtotal' => 90000],
                ],
                'subtotal' => 90000,
                'points_used' => 0,
                'discount_amount' => 0,
                'total_paid' => 90000,
                'points_earned' => 30,
                'at' => now()->subDays(4),
            ],
            [
                'customer' => $budi,
                'user' => $owner1,
                'invoice_code' => 'INV-1-20261120-005',
                'items' => [
                    ['service_id' => $srv4->id, 'name' => $srv4->name, 'price' => 150000, 'qty' => 1, 'points_reward' => 50, 'subtotal' => 150000],
                ],
                'subtotal' => 150000,
                'points_used' => 100,
                'discount_amount' => 10000,
                'total_paid' => 140000,
                'points_earned' => 50,
                'at' => now()->subDays(5),
            ],
            [
                'customer' => $ahmad,
                'user' => $cashier2,
                'invoice_code' => 'INV-1-20261014-006',
                'items' => [
                    ['service_id' => $srv2->id, 'name' => $srv2->name, 'price' => 75000, 'qty' => 1, 'points_reward' => 25, 'subtotal' => 75000],
                ],
                'subtotal' => 75000,
                'points_used' => 0,
                'discount_amount' => 0,
                'total_paid' => 75000,
                'points_earned' => 25,
                'at' => now()->subDays(42),
            ],
        ];

        foreach ($sampleTransactions as $row) {
            Transaction::create([
                'store_id' => $store1->id,
                'customer_id' => $row['customer']->id,
                'user_id' => $row['user']->id,
                'invoice_code' => $row['invoice_code'],
                'service_detail' => $row['items'],
                'subtotal' => $row['subtotal'],
                'points_used' => $row['points_used'],
                'discount_amount' => $row['discount_amount'],
                'total_paid' => $row['total_paid'],
                'points_earned' => $row['points_earned'],
                'receipt_sent_at' => $row['customer']->email ? $row['at'] : null,
                'created_at' => $row['at'],
                'updated_at' => $row['at'],
            ]);
        }

        Broadcast::create([
            'store_id' => $store1->id,
            'subject' => 'Promo Khusus Member VIP - Diskon Coating 50%!',
            'message' => "Terima kasih telah menjadi member VIP CleanShoe & Care Studio.\n\nNikmati diskon 50% untuk Water Repellent Coating pada kunjungan berikutnya!",
            'target_segment' => Broadcast::SEGMENT_VIP,
            'sent_at' => now()->subDays(3),
            'recipients_count' => 2,
            'status' => 'sent',
        ]);

        // ==========================================
        // TENANT 2: Pangkas Rapi Barbershop (Tenant Isolation Verification)
        // ==========================================
        $store2 = Store::create([
            'name' => 'Pangkas Rapi Barbershop',
            'business_type' => 'Barbershop & Salon',
            'address' => 'Jl. Dago No. 18, Bandung',
            'sender_name' => 'Pangkas Rapi Barbershop',
            'sender_email' => 'halo@pangkasrapi.id',
            'point_value_idr' => 100,
            'vip_points_threshold' => 1000,
        ]);

        User::create([
            'store_id' => $store2->id,
            'name' => 'Fajar Nugraha',
            'email' => 'owner2@umkm.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        Service::create([
            'store_id' => $store2->id,
            'name' => 'Gentleman Haircut & Wash',
            'price' => 60000,
            'points_reward' => 20,
            'is_active' => true,
        ]);

        Customer::create([
            'store_id' => $store2->id,
            'name' => 'Pelanggan Toko Bandung',
            'whatsapp_number' => '6281999990001',
            'email' => 'bandung.cust@example.com',
            'total_points' => 200,
            'last_visit_at' => now()->subDay(),
        ]);
    }
}
