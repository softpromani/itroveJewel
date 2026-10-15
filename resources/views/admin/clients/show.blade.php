@extends('admin.layout')

@section('title', $client->shop_name . ' - Details & USB Pendrives')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Top Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-2 text-xs text-slate-400">
            <a href="{{ route('admin.clients.index') }}" class="hover:text-amber-300 transition">Clients</a>
            <span>&rsaquo;</span>
            <span class="text-slate-200 font-semibold">{{ $client->shop_name }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.clients.edit', $client) }}" 
                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit Client</span>
            </a>

            <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" 
                onsubmit="return confirm('Are you sure you want to delete client {{ $client->shop_name }} and all their USB registrations?');">
                @csrf
                @method('DELETE')
                <button type="submit" 
                    class="px-3.5 py-2 rounded-xl bg-red-950/60 hover:bg-red-900/80 text-red-300 border border-red-500/30 text-xs font-semibold transition">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Client Header Card -->
    <div class="glass-panel gold-border rounded-3xl p-6 sm:p-8 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-400 to-yellow-300 flex items-center justify-center font-bold text-slate-950 text-2xl font-serif shadow-lg shadow-amber-500/20">
                    {{ strtoupper(substr($client->shop_name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-wide">{{ $client->shop_name }}</h1>
                    <p class="text-xs text-slate-400">Owner: <span class="text-slate-200 font-semibold">{{ $client->client_name }}</span></p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <span class="text-[11px] font-mono-code px-3 py-1 rounded-full border {{ $client->status === 'active' ? 'bg-emerald-950/70 border-emerald-500/40 text-emerald-300' : 'bg-red-950/70 border-red-500/40 text-red-300' }}">
                    ACCOUNT: {{ strtoupper($client->status) }}
                </span>
                <span class="text-xs text-slate-500">Member since {{ $client->created_at->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                <span class="text-slate-500 text-[10px] uppercase font-bold block">Mobile Contact</span>
                <span class="text-slate-200 font-mono-code text-sm mt-0.5 block">{{ $client->mobile_number }}</span>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                <span class="text-slate-500 text-[10px] uppercase font-bold block">Email Address</span>
                <span class="text-slate-200 text-sm mt-0.5 block truncate">{{ $client->email }}</span>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                <span class="text-slate-500 text-[10px] uppercase font-bold block">Authorized USBs</span>
                <span class="text-amber-300 font-bold text-sm mt-0.5 block">
                    {{ $client->pendriveRegistrations->where('status', 'active')->count() }} Active / {{ $client->pendriveRegistrations->count() }} Total
                </span>
            </div>
        </div>

        @if($client->notes)
            <div class="p-3.5 rounded-xl bg-slate-900/40 border border-slate-800/80 text-xs text-slate-400">
                <strong class="text-slate-300">Notes:</strong> {{ $client->notes }}
            </div>
        @endif
    </div>

    <!-- Step 2: Register New Pendrive Card -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-amber-500/30 space-y-4">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">
                +
            </div>
            <div>
                <h2 class="text-base font-bold text-white tracking-wide">Register New USB Pendrive Serial</h2>
                <p class="text-xs text-slate-400">
                    Connect the client's USB drive to your computer, check its physical hardware serial number, and register it below.
                </p>
            </div>
        </div>

        <form action="{{ route('admin.clients.pendrives.store', $client) }}" method="POST" class="space-y-4 pt-2">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Serial Number -->
                <div>
                    <label for="serial_number" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Physical Hardware Serial Number <span class="text-amber-400">*</span>
                    </label>
                    <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number') }}" required 
                        placeholder="e.g. KRN240203943 or 070732A1521F3841"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 font-mono-code placeholder-slate-500">
                </div>

                <!-- Pendrive Model -->
                <div>
                    <label for="pendrive_model" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Pendrive Brand / Model <span class="text-slate-500">(Optional)</span>
                    </label>
                    <input type="text" id="pendrive_model" name="pendrive_model" value="{{ old('pendrive_model') }}" 
                        placeholder="e.g. SanDisk Ultra Flair 64GB"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 placeholder-slate-500">
                </div>

                <!-- Label -->
                <div>
                    <label for="label" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Label / Billing Counter <span class="text-slate-500">(Optional)</span>
                    </label>
                    <input type="text" id="label" name="label" value="{{ old('label') }}" 
                        placeholder="e.g. Main Billing Counter #1"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 placeholder-slate-500">
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <span class="text-[11px] text-slate-400">
                    &bull; The software will query our server API during installation and lock only if this serial matches.
                </span>

                <button type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs tracking-wider shadow-lg shadow-amber-500/20 transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>AUTHORIZE & BIND PENDRIVE</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Assigned Pendrives Listing -->
    <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden space-y-4 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-white tracking-wide">Registered USB Pendrives</h2>
                <p class="text-xs text-slate-400">Authorized storage hardware permitted to execute ItroveJewel for this client.</p>
            </div>
            <span class="text-xs font-mono-code text-amber-400 bg-amber-950/60 border border-amber-500/30 px-3 py-1 rounded-full">
                {{ $client->pendriveRegistrations->count() }} Registered
            </span>
        </div>

        @if($client->pendriveRegistrations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/90 border-b border-slate-800 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                        <tr>
                            <th class="py-3 px-3">Physical Hardware Serial</th>
                            <th class="py-3 px-3">Model / Label</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Server Verifications</th>
                            <th class="py-3 px-3">Registered At</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($client->pendriveRegistrations as $pendrive)
                            <tr class="hover:bg-slate-900/40 transition">
                                <!-- Serial Number -->
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <span class="font-mono-code font-bold text-sm text-white select-all">
                                            {{ $pendrive->serial_number }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Model & Label -->
                                <td class="py-3.5 px-3">
                                    <span class="font-medium text-slate-200 block">{{ $pendrive->pendrive_model ?: 'Standard USB Drive' }}</span>
                                    <span class="text-[11px] text-slate-500 block">{{ $pendrive->label ?: 'Default Counter' }}</span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-3">
                                    @if($pendrive->status === 'active')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950/70 text-emerald-300 border border-emerald-500/40">
                                            ACTIVE &bull; UNLOCKED
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-950/70 text-red-300 border border-red-500/40">
                                            REVOKED / LOCKED
                                        </span>
                                    @endif
                                </td>

                                <!-- Verification Metrics -->
                                <td class="py-3.5 px-3">
                                    <div class="space-y-0.5">
                                        <span class="text-slate-300 font-semibold text-[11px]">
                                            {{ $pendrive->verification_count }} times verified
                                        </span>
                                        <p class="text-[10px] text-slate-500">
                                            Last: {{ $pendrive->last_verified_at ? $pendrive->last_verified_at->diffForHumans() : 'Never checked yet' }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Registered Date -->
                                <td class="py-3.5 px-3 text-slate-400 font-mono-code text-[11px]">
                                    {{ $pendrive->registered_at ? $pendrive->registered_at->format('Y-m-d') : $pendrive->created_at->format('Y-m-d') }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-3 text-right space-x-2">
                                    <form action="{{ route('admin.pendrives.toggle', $pendrive) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $pendrive->status === 'active' ? 'bg-amber-950/60 hover:bg-amber-900/80 text-amber-300 border border-amber-500/30' : 'bg-emerald-950/60 hover:bg-emerald-900/80 text-emerald-300 border border-emerald-500/30' }}">
                                            {{ $pendrive->status === 'active' ? 'Revoke Access' : 'Activate USB' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.pendrives.destroy', $pendrive) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Remove serial {{ $pendrive->serial_number }} from this client?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-red-950 text-slate-400 hover:text-red-300 text-xs transition">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-500 border border-dashed border-slate-800 rounded-2xl">
                <svg class="w-8 h-8 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs font-semibold text-slate-400">No USB pendrives registered yet</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Use the form above to add the client's USB hardware serial number.</p>
            </div>
        @endif
    </div>

</div>
@endsection
