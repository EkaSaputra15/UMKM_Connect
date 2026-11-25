<?php

namespace App\Livewire;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pelanggan CRM - UMKM Connect')]
class CustomerIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $segment = 'all';

    public bool $showFormModal = false;
    public ?int $editingCustomerId = null;

    public string $name = '';
    public string $whatsapp_number = '';
    public string $email = '';
    public string $birth_date = '';

    public ?int $selectedCustomerId = null;

    public function mount(): void
    {
        if (request()->boolean('create')) {
            $this->openCreateModal();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSegment(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $this->editingCustomerId = $customer->id;
        $this->name = $customer->name;
        $this->whatsapp_number = $customer->whatsapp_number;
        $this->email = (string) ($customer->email ?? '');
        $this->birth_date = $customer->birth_date?->format('Y-m-d') ?? '';
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function saveCustomer(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'min:9', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.min' => 'Nomor WhatsApp minimal 9 digit.',
            'email.email' => 'Format email tidak valid.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
        ]);

        $normalizedWa = Customer::normalizeWhatsapp($validated['whatsapp_number']);

        $payload = [
            'name' => trim($validated['name']),
            'whatsapp_number' => $normalizedWa,
            'email' => $validated['email'] !== '' ? trim($validated['email']) : null,
            'birth_date' => $validated['birth_date'] !== '' ? $validated['birth_date'] : null,
        ];

        if ($this->editingCustomerId) {
            $customer = Customer::findOrFail($this->editingCustomerId);
            $customer->update($payload);
            session()->flash('success', "Data pelanggan {$customer->name} berhasil diperbarui.");
        } else {
            $customer = Customer::create($payload);
            $this->selectedCustomerId = $customer->id;
            session()->flash('success', "Pelanggan baru {$customer->name} berhasil didaftarkan.");
        }

        $this->closeModal();
    }

    public function deleteCustomer(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        if ($customer->transactions()->exists()) {
            session()->flash('error', "Pelanggan {$customer->name} memiliki riwayat transaksi sehingga tidak dapat dihapus.");

            return;
        }

        $name = $customer->name;
        $customer->delete();
        session()->flash('success', "Pelanggan {$name} berhasil dihapus.");
    }

    public function selectRow(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
    }

    private function resetForm(): void
    {
        $this->editingCustomerId = null;
        $this->name = '';
        $this->whatsapp_number = '';
        $this->email = '';
        $this->birth_date = '';
        $this->resetValidation();
    }

    public function render(): View
    {
        $store = auth()->user()->store;
        $vipThreshold = $store?->vip_points_threshold ?? 1000;
        $search = trim($this->search);

        $customers = Customer::query()
            ->with('store:id,vip_points_threshold')
            ->withCount('transactions')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('whatsapp_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($this->segment === 'active', function ($query): void {
                $cutoff = now()->subDays(30);
                $query->where(function ($sub) use ($cutoff): void {
                    $sub->where('last_visit_at', '>=', $cutoff)
                        ->orWhere(function ($inner) use ($cutoff): void {
                            $inner->whereNull('last_visit_at')->where('created_at', '>=', $cutoff);
                        });
                });
            })
            ->when($this->segment === 'inactive_30_days', fn ($q) => $q->segment('inactive_30_days', $vipThreshold))
            ->when($this->segment === 'vip', fn ($q) => $q->segment('vip', $vipThreshold))
            ->orderByDesc('last_visit_at')
            ->orderByDesc('id')
            ->paginate(12);

        return view('livewire.customer-index', [
            'customers' => $customers,
            'vipThreshold' => $vipThreshold,
        ]);
    }
}
