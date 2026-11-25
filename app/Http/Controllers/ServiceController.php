<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('services.index', [
            'services' => $services,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'points_reward' => ['required', 'integer', 'min:0'],
        ], [
            'name.required' => 'Nama layanan wajib diisi.',
            'price.required' => 'Harga layanan wajib diisi.',
            'points_reward.required' => 'Reward poin wajib diisi.',
        ]);

        Service::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'points_reward' => $validated['points_reward'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('services.index')
            ->with('success', "Layanan \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'points_reward' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'points_reward' => $validated['points_reward'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('services.index')
            ->with('success', "Layanan \"{$service->name}\" berhasil diperbarui.");
    }

    public function destroy(Service $service): RedirectResponse
    {
        $name = $service->name;
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', "Layanan \"{$name}\" berhasil dihapus.");
    }
}
