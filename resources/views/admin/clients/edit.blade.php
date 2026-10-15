@extends('admin.layout')

@section('title', 'Edit Client - ' . $client->shop_name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center space-x-2 text-xs text-slate-400">
        <a href="{{ route('admin.clients.index') }}" class="hover:text-amber-300 transition">Clients</a>
        <span>&rsaquo;</span>
        <a href="{{ route('admin.clients.show', $client) }}" class="hover:text-amber-300 transition">{{ $client->shop_name }}</a>
        <span>&rsaquo;</span>
        <span class="text-slate-200">Edit</span>
    </div>

    <!-- Title -->
    <div>
        <h1 class="text-2xl font-bold text-white tracking-wide">Edit Client Profile</h1>
        <p class="text-xs text-slate-400 mt-1">
            Update store name, contact details, or suspend client account status.
        </p>
    </div>

    <!-- Form Card -->
    <div class="glass-panel gold-border rounded-3xl p-6 sm:p-8 space-y-6">
        <form action="{{ route('admin.clients.update', $client) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Client & Shop Name Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="shop_name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Shop / Store Name <span class="text-amber-400">*</span>
                    </label>
                    <input type="text" id="shop_name" name="shop_name" value="{{ old('shop_name', $client->shop_name) }}" required 
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition">
                </div>

                <div>
                    <label for="client_name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Client / Owner Full Name <span class="text-amber-400">*</span>
                    </label>
                    <input type="text" id="client_name" name="client_name" value="{{ old('client_name', $client->client_name) }}" required 
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition">
                </div>
            </div>

            <!-- Contact Information Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="mobile_number" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Mobile Number <span class="text-amber-400">*</span>
                    </label>
                    <input type="text" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $client->mobile_number) }}" required 
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 font-mono-code transition">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Email Address <span class="text-amber-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $client->email) }}" required 
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition">
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Account Status <span class="text-amber-400">*</span>
                    </label>
                    <select id="status" name="status" required 
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition">
                        <option value="active" {{ old('status', $client->status) === 'active' ? 'selected' : '' }}>Active (Authorized)</option>
                        <option value="suspended" {{ old('status', $client->status) === 'suspended' ? 'selected' : '' }}>Suspended (Locked Out)</option>
                        <option value="inactive" {{ old('status', $client->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Additional Notes -->
            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Notes / Remarks
                </label>
                <textarea id="notes" name="notes" rows="3" 
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition">{{ old('notes', $client->notes) }}</textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-2 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.clients.show', $client) }}" 
                    class="px-5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 hover:bg-slate-800 text-slate-300 font-semibold text-xs transition">
                    Cancel
                </a>

                <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 font-bold text-xs tracking-wider shadow-lg shadow-amber-500/20 transition">
                    UPDATE CLIENT
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
