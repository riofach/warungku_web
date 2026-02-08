@extends('layouts.app')

@section('title', 'Pembayaran QRIS')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg text-center p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Pembayaran QRIS</h1>
        
        <div class="mb-6">
            <p class="text-gray-500">Kode Pesanan:</p>
            <p class="text-2xl font-mono font-bold text-primary-600">{{ $code }}</p>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 text-left">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Halaman ini adalah <strong>Placeholder Sementara</strong>.<br>
                        Desain QRIS Payment lengkap akan dikerjakan di <strong>Story 11.4</strong>.
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">
            &larr; Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
