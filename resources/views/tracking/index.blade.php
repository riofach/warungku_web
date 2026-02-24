@extends('layouts.app')

@section('title', 'Lacak Pesanan')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-lg">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">Lacak Pesanan</h1>
        <p class="text-text-secondary">Masukkan kode pesanan untuk melihat status terkini</p>
    </div>

    {{-- Error Message --}}
    @if(session('error'))
        <div class="bg-error/10 border border-error text-error px-4 py-3 rounded-lg flex items-start gap-2 mb-4">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Search Form --}}
    <form action="{{ route('tracking.search') }}" method="POST" class="card p-6">
        @csrf
        <div class="mb-5">
            <label for="code" class="block text-sm font-medium mb-2">
                Kode Pesanan
                <span class="text-error">*</span>
            </label>
            <input
                type="text"
                name="code"
                id="code"
                placeholder="Contoh: WRG-20260115-0001"
                value="{{ old('code', session('searched_code', '')) }}"
                class="input-field font-mono @error('code') border-error @enderror"
                required
                autofocus
            >
            @error('code')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-text-tertiary text-xs mt-2">
                Format kode: <span class="font-mono font-medium">WRG-YYYYMMDD-XXXX</span>
            </p>
        </div>

        <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Lacak Pesanan
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-text-secondary text-sm">Belum punya pesanan?</p>
        <a href="{{ route('home') }}" class="text-primary hover:underline font-medium">
            Mulai Belanja →
        </a>
    </div>
</div>
@endsection
