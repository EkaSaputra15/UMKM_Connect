<?php

namespace App\Livewire;

use App\Jobs\SendEmailReceiptJob;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kasir & Transaksi Baru - UMKM Connect')]
class TransactionCreate extends Component
{
    public string $customerSearch = '';
    public ?int $selectedCustomerId = null;

    public bool $showQuickCustomerForm = false;
    public string $newCustomerName = '';
    public string $newCustomerWhatsapp = '';
    public string $newCustomerEmail = '';
    public string $newCustomerBirthDate = '';

    public string $serviceSearch = '';
    /** @var array<int, array{id:int, name:string, price:float, points_reward:int, qty:int}> */
    public array $cart = [];

    public bool $redeemPoints = false;
    public int $pointsToUse = 0;

    public ?int $completedTransactionId = null;
    public ?string $completedInvoiceCode = null;
    public bool $completedReceiptQueued = false;

    public function mount(): void
    {
        $preselectedId = request()->integer('customer_id');
        if ($preselectedId > 0 && Customer::whereKey($preselectedId)->exists()) {
            $this->selectedCustomerId = $preselectedId;
        }
    }

    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        if (! $this->selectedCustomerId) {
            return null;
        }

        return Customer::with('store:id,point_value_idr,vip_points_threshold')
            ->find($this->selectedCustomerId);
    }

    #[Computed]
    public function pointValueIdr(): int
    {
        return max(1, (int) (auth()->user()->store?->point_value_idr ?? 100));
    }

    #[Computed]
    public function subtotal(): float
    {
        $total = 0.0;
        foreach ($this->cart as $item) {
            $total += ((float) $item['price']) * ((int) $item['qty']);
        }

        return $total;
    }

    #[Computed]
    public function maxRedeemablePoints(): int
    {
        $customer = $this->selectedCustomer;
        if (! $customer || $customer->total_points <= 0 || $this->subtotal <= 0) {
            return 0;
        }

        $maxBySubtotal = (int) floor($this->subtotal / $this->pointValueIdr);

        return max(0, min($customer->total_points, $maxBySubtotal));
    }

    #[Computed]
    public function effectivePointsUsed(): int
    {
        if (! $this->redeemPoints || ! $this->selectedCustomer) {
            return 0;
        }

        return max(0, min($this->pointsToUse, $this->maxRedeemablePoints));
    }

    #[Computed]
    public function discountAmount(): float
    {
        return min($this->subtotal, (float) ($this->effectivePointsUsed * $this->pointValueIdr));
    }

    #[Computed]
    public function totalPaid(): float
    {
        return max(0.0, $this->subtotal - $this->discountAmount);
    }

    #[Computed]
    public function pointsEarned(): int
    {
        $earned = 0;
        foreach ($this->cart as $item) {
            $earned += ((int) $item['points_reward']) * ((int) $item['qty']);
        }

        return $earned;
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);
        $this->selectedCustomerId = $customer->id;
        $this->customerSearch = '';
        $this->redeemPoints = false;
        $this->pointsToUse = 0;
        $this->showQuickCustomerForm = false;
    }

    public function clearCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->redeemPoints = false;
        $this->pointsToUse = 0;
    }

    public function toggleQuickCustomerForm(): void
    {
        $this->showQuickCustomerForm = ! $this->showQuickCustomerForm;
        $this->resetValidation(['newCustomerName', 'newCustomerWhatsapp', 'newCustomerEmail', 'newCustomerBirthDate']);
    }

    public function createQuickCustomer(): void
    {
        $validated = $this->validate([
            'newCustomerName' => ['required', 'string', 'max:255'],
            'newCustomerWhatsapp' => ['required', 'string', 'min:9', 'max:20'],
            'newCustomerEmail' => ['nullable', 'email', 'max:255'],
            'newCustomerBirthDate' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'newCustomerName.required' => 'Nama pelanggan wajib diisi.',
            'newCustomerWhatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'newCustomerEmail.email' => 'Format email tidak valid.',
        ]);

        $customer = Customer::create([
            'name' => trim($validated['newCustomerName']),
            'whatsapp_number' => Customer::normalizeWhatsapp($validated['newCustomerWhatsapp']),
            'email' => $validated['newCustomerEmail'] !== '' ? trim($validated['newCustomerEmail']) : null,
            'birth_date' => $validated['newCustomerBirthDate'] !== '' ? $validated['newCustomerBirthDate'] : null,
        ]);

        $this->selectedCustomerId = $customer->id;
        $this->showQuickCustomerForm = false;
        $this->newCustomerName = '';
        $this->newCustomerWhatsapp = '';
        $this->newCustomerEmail = '';
        $this->newCustomerBirthDate = '';
        $this->customerSearch = '';
    }

    public function addService(int $serviceId): void
    {
        $service = Service::where('is_active', true)->findOrFail($serviceId);

        if (isset($this->cart[$service->id])) {
            $this->cart[$service->id]['qty']++;
        } else {
            $this->cart[$service->id] = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'points_reward' => (int) $service->points_reward,
                'qty' => 1,
            ];
        }

        $this->syncPointsToUse();
    }

    public function incrementQty(int $serviceId): void
    {
        if (isset($this->cart[$serviceId])) {
            $this->cart[$serviceId]['qty']++;
            $this->syncPointsToUse();
        }
    }

    public function decrementQty(int $serviceId): void
    {
        if (! isset($this->cart[$serviceId])) {
            return;
        }

        if ($this->cart[$serviceId]['qty'] <= 1) {
            unset($this->cart[$serviceId]);
        } else {
            $this->cart[$serviceId]['qty']--;
        }

        $this->syncPointsToUse();
    }

    public function removeService(int $serviceId): void
    {
        unset($this->cart[$serviceId]);
        $this->syncPointsToUse();
    }

    public function updatedRedeemPoints(bool $value): void
    {
        if ($value) {
            $this->pointsToUse = $this->maxRedeemablePoints;
        } else {
            $this->pointsToUse = 0;
        }
    }

    public function updatedPointsToUse(): void
    {
        if ($this->pointsToUse < 0) {
            $this->pointsToUse = 0;
        } elseif ($this->pointsToUse > $this->maxRedeemablePoints) {
            $this->pointsToUse = $this->maxRedeemablePoints;
        }
    }

    private function syncPointsToUse(): void
    {
        if (! $this->redeemPoints) {
            $this->pointsToUse = 0;

            return;
        }

        if ($this->pointsToUse > $this->maxRedeemablePoints || $this->pointsToUse === 0) {
            $this->pointsToUse = $this->maxRedeemablePoints;
        }
    }

    public function checkout(): void
    {
        if (! $this->selectedCustomerId) {
            $this->addError('checkout', 'Pilih atau daftarkan pelanggan terlebih dahulu sebelum memproses transaksi.');

            return;
        }

        if (empty($this->cart)) {
            $this->addError('checkout', 'Pilih minimal satu layanan ke dalam keranjang transaksi.');

            return;
        }

        $user = auth()->user();
        $store = $user->store;
        $pointValueIdr = max(1, (int) ($store?->point_value_idr ?? 100));

        $result = DB::transaction(function () use ($user, $pointValueIdr): array {
            $customer = Customer::query()
                ->where('store_id', $user->store_id)
                ->lockForUpdate()
                ->findOrFail($this->selectedCustomerId);

            $serviceIds = array_keys($this->cart);
            $dbServices = Service::query()
                ->where('store_id', $user->store_id)
                ->where('is_active', true)
                ->whereIn('id', $serviceIds)
                ->get()
                ->keyBy('id');

            $serviceDetails = [];
            $subtotal = 0.0;
            $pointsEarned = 0;

            foreach ($this->cart as $serviceId => $item) {
                $dbService = $dbServices->get($serviceId);
                if (! $dbService) {
                    continue;
                }

                $qty = max(1, (int) $item['qty']);
                $price = (float) $dbService->price;
                $lineSubtotal = $price * $qty;
                $linePoints = ((int) $dbService->points_reward) * $qty;

                $serviceDetails[] = [
                    'service_id' => $dbService->id,
                    'name' => $dbService->name,
                    'price' => $price,
                    'qty' => $qty,
                    'points_reward' => (int) $dbService->points_reward,
                    'subtotal' => $lineSubtotal,
                ];

                $subtotal += $lineSubtotal;
                $pointsEarned += $linePoints;
            }

            if (empty($serviceDetails)) {
                throw new \RuntimeException('Layanan yang dipilih sudah tidak tersedia.');
            }

            $pointsUsed = 0;
            $discountAmount = 0.0;

            if ($this->redeemPoints && $customer->total_points > 0 && $subtotal > 0) {
                $maxBySubtotal = (int) floor($subtotal / $pointValueIdr);
                $maxAllowed = max(0, min($customer->total_points, $maxBySubtotal));
                $pointsUsed = max(0, min((int) $this->pointsToUse, $maxAllowed));
                $discountAmount = min($subtotal, (float) ($pointsUsed * $pointValueIdr));
            }

            $totalPaid = max(0.0, $subtotal - $discountAmount);

            $transaction = Transaction::create([
                'store_id' => $user->store_id,
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'invoice_code' => Transaction::generateInvoiceCode($user->store_id),
                'service_detail' => $serviceDetails,
                'subtotal' => $subtotal,
                'points_used' => $pointsUsed,
                'discount_amount' => $discountAmount,
                'total_paid' => $totalPaid,
                'points_earned' => $pointsEarned,
            ]);

            $newBalance = max(0, ($customer->total_points - $pointsUsed) + $pointsEarned);

            $customer->forceFill([
                'total_points' => $newBalance,
                'last_visit_at' => now(),
            ])->save();

            return [
                'transaction' => $transaction,
                'hasEmail' => ! empty($customer->email),
            ];
        });

        /** @var Transaction $transaction */
        $transaction = $result['transaction'];

        if ($result['hasEmail']) {
            SendEmailReceiptJob::dispatch($transaction->id);
        }

        $this->completedTransactionId = $transaction->id;
        $this->completedInvoiceCode = $transaction->invoice_code;
        $this->completedReceiptQueued = $result['hasEmail'];

        $this->cart = [];
        $this->redeemPoints = false;
        $this->pointsToUse = 0;
        unset($this->selectedCustomer);
    }

    public function startNewTransaction(): void
    {
        $this->completedTransactionId = null;
        $this->completedInvoiceCode = null;
        $this->completedReceiptQueued = false;
        $this->selectedCustomerId = null;
        $this->cart = [];
        $this->redeemPoints = false;
        $this->pointsToUse = 0;
        $this->customerSearch = '';
        $this->serviceSearch = '';
    }

    public function render(): View
    {
        $customerResults = collect();
        if (! $this->selectedCustomerId) {
            $q = trim($this->customerSearch);
            $customerResults = Customer::query()
                ->when($q !== '', function ($query) use ($q): void {
                    $query->where(function ($sub) use ($q): void {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('whatsapp_number', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('last_visit_at')
                ->limit(8)
                ->get();
        }

        $services = Service::query()
            ->where('is_active', true)
            ->when(trim($this->serviceSearch) !== '', function ($query): void {
                $query->where('name', 'like', '%' . trim($this->serviceSearch) . '%');
            })
            ->orderBy('name')
            ->get();

        return view('livewire.transaction-create', [
            'customerResults' => $customerResults,
            'services' => $services,
        ]);
    }
}
