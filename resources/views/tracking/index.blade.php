@extends('layouts.app')

@section('title', 'Lacak Pesanan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <div class="text-5xl mb-4">📦</div>
            <h1 class="text-2xl font-bold mb-2">Lacak Pesanan</h1>
            <p class="text-text-secondary">Masukkan kode pesanan untuk melihat status</p>
        </div>

        <form action="{{ route('tracking.search') }}" method="POST" class="card">
            @csrf
            <div class="mb-4">
                <label for="code" class="block text-sm font-medium mb-2">Kode Pesanan</label>
                <input 
                    type="text" 
                    name="code" 
                    id="code"
                    placeholder="Contoh: WRG-20260119-0001"
                    class="input-field"
                    required
                >
            </div>
            <button type="submit" class="btn-primary w-full">
                Lacak Pesanan
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-text-secondary text-sm">Belum punya pesanan?</p>
            <a href="{{ route('shop.index') }}" class="text-primary hover:underline font-medium">
                Mulai Belanja →
            </a>
        </div>
    </div>
</div>
@endsection
