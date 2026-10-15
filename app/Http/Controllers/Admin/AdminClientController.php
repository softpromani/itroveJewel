<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PendriveRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminClientController extends Controller
{
    /**
     * Display the Admin Dashboard with client metrics and listing.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $statusFilter = $request->input('status', 'all');

        $query = Client::with(['pendriveRegistrations'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('pendriveRegistrations', function ($pQuery) use ($search) {
                        $pQuery->where('serial_number', 'like', "%{$search}%")
                            ->orWhere('pendrive_model', 'like', "%{$search}%");
                    });
            });
        }

        if ($statusFilter !== 'all' && in_array($statusFilter, ['active', 'suspended', 'inactive'])) {
            $query->where('status', $statusFilter);
        }

        $clients = $query->paginate(15)->withQueryString();

        // Metrics for summary cards
        $metrics = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('status', 'active')->count(),
            'total_pendrives' => PendriveRegistration::count(),
            'active_pendrives' => PendriveRegistration::where('status', 'active')->count(),
        ];

        return view('admin.clients.index', [
            'clients' => $clients,
            'metrics' => $metrics,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Show form to create a new client.
     */
    public function create(): View
    {
        return view('admin.clients.create');
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:150'],
            'shop_name' => ['required', 'string', 'max:200'],
            'mobile_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150', Rule::unique(Client::class, 'email')],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $client = Client::create([
            ...$validated,
            'status' => 'active',
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Client '{$client->client_name}' created successfully! Now you can register their USB pendrive serial number.");
    }

    /**
     * Display client details and their assigned pendrive registrations.
     */
    public function show(Client $client): View
    {
        $client->load(['pendriveRegistrations' => function ($query) {
            $query->latest();
        }]);

        return view('admin.clients.show', [
            'client' => $client,
        ]);
    }

    /**
     * Show the form for editing the client.
     */
    public function edit(Client $client): View
    {
        return view('admin.clients.edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the client in storage.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:150'],
            'shop_name' => ['required', 'string', 'max:200'],
            'mobile_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150', Rule::unique(Client::class, 'email')->ignore($client->id)],
            'status' => ['required', 'in:active,suspended,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client details updated successfully.');
    }

    /**
     * Delete the client and their registered pendrives.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $name = $client->client_name;
        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', "Client '{$name}' and all associated pendrive registrations have been removed.");
    }
}
