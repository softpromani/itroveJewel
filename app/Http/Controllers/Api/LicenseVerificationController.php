<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendriveRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseVerificationController extends Controller
{
    /**
     * Verify a USB Hardware Serial Number from the central ItroveJewel server.
     */
    public function verifyUsb(Request $request): JsonResponse
    {
        $request->validate([
            'serial_number' => ['required', 'string', 'min:3', 'max:120'],
            'model' => ['nullable', 'string', 'max:150'],
        ]);

        $serial = trim($request->input('serial_number'));

        // Look up registered active pendrive and verify the client is also active
        $registration = PendriveRegistration::with('client')
            ->where('serial_number', $serial)
            ->first();

        // 1. Serial not found in system
        if (! $registration) {
            return response()->json([
                'success' => false,
                'authorized' => false,
                'status' => 'NOT_REGISTERED',
                'message' => "USB Hardware Serial [{$serial}] is not registered in the ItroveJewel central system. Please contact the administrator to register this pendrive.",
            ], 403);
        }

        // 2. Pendrive registration revoked
        if ($registration->status !== 'active') {
            return response()->json([
                'success' => false,
                'authorized' => false,
                'status' => 'REVOKED',
                'message' => "USB Hardware Serial [{$serial}] has been REVOKED by the administrator. Access is disabled.",
            ], 403);
        }

        // 3. Client account suspended or inactive
        $client = $registration->client;
        if (! $client || $client->status !== 'active') {
            return response()->json([
                'success' => false,
                'authorized' => false,
                'status' => 'CLIENT_INACTIVE',
                'message' => "The client account for {$client?->shop_name} is suspended. Please contact the administrator.",
            ], 403);
        }

        // Record successful verification
        $registration->increment('verification_count');
        $registration->update([
            'last_verified_at' => now(),
            'pendrive_model' => $request->input('model', $registration->pendrive_model),
        ]);

        // Cryptographic server authorization seal
        $appKey = config('app.key');
        $serverToken = hash_hmac('sha256', "SERVER_APPROVED::{$serial}::{$client->id}::{$client->email}", $appKey);

        return response()->json([
            'success' => true,
            'authorized' => true,
            'status' => 'ACTIVE',
            'message' => "USB Hardware Serial successfully verified for {$client->shop_name}.",
            'client' => [
                'id' => $client->id,
                'client_name' => $client->client_name,
                'shop_name' => $client->shop_name,
                'mobile_number' => $client->mobile_number,
                'email' => $client->email,
            ],
            'pendrive' => [
                'serial_number' => $registration->serial_number,
                'pendrive_model' => $registration->pendrive_model,
                'label' => $registration->label,
                'registered_at' => $registration->registered_at?->toISOString(),
                'verification_count' => $registration->verification_count,
            ],
            'server_token' => $serverToken,
            'verified_at' => now()->toISOString(),
        ]);
    }
}
