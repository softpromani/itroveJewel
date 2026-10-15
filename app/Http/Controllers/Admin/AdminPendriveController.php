<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PendriveRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminPendriveController extends Controller
{
    /**
     * Register a new USB Pendrive Hardware Serial Number for a client.
     */
    public function store(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'serial_number' => ['required', 'string', 'min:3', 'max:100'],
            'pendrive_model' => ['nullable', 'string', 'max:150'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $cleanSerial = trim($validated['serial_number']);

        // Check if this serial number is already registered to another active client
        $existing = PendriveRegistration::where('serial_number', $cleanSerial)
            ->where('status', 'active')
            ->where('client_id', '!=', $client->id)
            ->with('client')
            ->first();

        if ($existing) {
            return back()->withInput()->withErrors([
                'serial_number' => "This Pendrive Serial ({$cleanSerial}) is already registered and active for client '{$existing->client->client_name}' ({$existing->client->shop_name}). Each pendrive serial can only be locked to one client.",
            ]);
        }

        // Check if already registered for this client
        $currentForClient = PendriveRegistration::where('client_id', $client->id)
            ->where('serial_number', $cleanSerial)
            ->first();

        if ($currentForClient) {
            $currentForClient->update([
                'pendrive_model' => $validated['pendrive_model'] ?? $currentForClient->pendrive_model,
                'label' => $validated['label'] ?? $currentForClient->label,
                'status' => 'active',
                'registered_at' => now(),
            ]);

            return redirect()->route('admin.clients.show', $client)
                ->with('success', "USB Serial '{$cleanSerial}' has been re-activated for {$client->shop_name}.");
        }

        PendriveRegistration::create([
            'client_id' => $client->id,
            'serial_number' => $cleanSerial,
            'pendrive_model' => $validated['pendrive_model'] ?? 'Standard USB Flash Drive',
            'label' => $validated['label'] ?? 'Primary USB License',
            'status' => 'active',
            'registered_at' => now(),
            'registered_by' => 'Admin',
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "USB Pendrive Serial '{$cleanSerial}' successfully bound and registered for {$client->shop_name}! The software on this pendrive will now verify and unlock.");
    }

    /**
     * Toggle status between active and revoked.
     */
    public function toggleStatus(PendriveRegistration $pendrive): RedirectResponse
    {
        $newStatus = $pendrive->status === 'active' ? 'revoked' : 'active';
        $pendrive->update(['status' => $newStatus]);

        $msg = $newStatus === 'active'
            ? "USB Serial '{$pendrive->serial_number}' is now ACTIVE and can verify on client computers."
            : "USB Serial '{$pendrive->serial_number}' has been REVOKED. Client software will be locked out immediately.";

        return back()->with('success', $msg);
    }

    /**
     * Delete a registered pendrive.
     */
    public function destroy(PendriveRegistration $pendrive): RedirectResponse
    {
        $client = $pendrive->client;
        $serial = $pendrive->serial_number;
        $pendrive->delete();

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "USB Serial '{$serial}' was deleted from {$client->shop_name}.");
    }
}
