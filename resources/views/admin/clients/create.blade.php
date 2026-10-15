@extends('admin.layout')

@section('title', 'Register New Client')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center space-x-2 text-xs text-slate-400">
        <a href="{{ route('admin.clients.index') }}" class="hover:text-amber-300 transition">Clients</a>
        <span>&rsaquo;</span>
        <span class="text-slate-200">Register New Client</span>
    </div>

    <!-- Title & Description -->
    <div>
        <h1 class="text-2xl font-bold text-white tracking-wide">Register New Jewel Client</h1>
        <p class="text-xs text-slate-400 mt-1">
            Step 1: Enter the client's shop and contact information. Once saved, you will proceed to register their physical USB Pendrive serial number.
        </p>
    </div>

    <!-- Workflow Timeline Card -->
    <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
        <div class="flex items-center space-x-2.5 text-amber-300 font-semibold">
            <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs shrink-0">1</span>
            <span>Add Client Details</span>
        </div>
        <div class="flex items-center space-x-2.5 text-slate-400">
            <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-xs shrink-0">2</span>
            <span>Register Pendrive Serial</span>
        </div>
        <div class="flex items-center space-x-2.5 text-slate-400">
            <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-xs shrink-0">3</span>
            <span>Handover USB to Client</span>
        </div>
    </div>

    <!-- Form Card -->
    <div class="glass-panel gold-border rounded-3xl p-6 sm:p-8 space-y-6">
        <form action="{{ route('admin.clients.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Client & Shop Name Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="shop_name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Shop / Jewellery Store Name <span class="text-amber-400">*</span>
                    </label>
                    <input type="text" id="shop_name" name="shop_name" value="{{ old('shop_name') }}" required 
                        placeholder="e.g. Royal Diamond & Jewel Palace"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition placeholder-slate-500">
                </div>

                <div>
                    <label for="client_name" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Client / Owner Full Name <span class="text-amber-400">*</span>
                    </label>
                    <input type="text" id="client_name" name="client_name" value="{{ old('client_name') }}" required 
                        placeholder="e.g. Rajesh Mehta"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition placeholder-slate-500">
                </div>
            </div>

            <!-- Contact Information Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="mobile_number" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Mobile Number <span class="text-amber-400">*</span>
                    </label>
                    <input type="text" id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}" required 
                        placeholder="e.g. +91 9876543210"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition placeholder-slate-500 font-mono-code">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Email Address <span class="text-amber-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                        placeholder="e.g. rajesh@royaljewels.com"
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition placeholder-slate-500">
                </div>
            </div>

            <!-- Additional Notes -->
            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Notes / Store Location / Remarks <span class="text-slate-500">(Optional)</span>
                </label>
                <textarea id="notes" name="notes" rows="3" 
                    placeholder="e.g. Zaveri Bazaar Main Branch, Counter 1 license requested..."
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-sm text-white focus:outline-none focus:border-amber-400 transition placeholder-slate-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-2 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.clients.index') }}" 
                    class="px-5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 hover:bg-slate-800 text-slate-300 font-semibold text-xs tracking-wide transition">
                    Cancel
                </a>

                <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-300 hover:from-amber-400 hover:to-yellow-200 text-slate-950 font-bold text-xs tracking-wider shadow-lg shadow-amber-500/20 transition flex items-center space-x-2">
                    <span>SAVE & PROCEED TO PENDRIVE &rsaquo;</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
