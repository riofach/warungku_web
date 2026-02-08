@extends('layouts.app')

@section('title', 'Warung Tutup')

@section('content')
<div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Warung Sedang Tutup</h1>
    
    <p class="text-gray-600 mb-8 max-w-md">
        Maaf, kami sedang tidak beroperasi saat ini. Silakan kembali lagi pada jam operasional kami:
        <span class="block font-medium text-gray-900 mt-2">
            08:00 - 21:00 WIB
        </span>
    </p>
    
    <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
        Kembali ke Beranda
    </a>
</div>
@endsection
