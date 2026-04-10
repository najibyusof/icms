@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-2xl rounded-3xl border border-red-200 bg-white p-8 text-center shadow-lg shadow-red-100">
        <h1 class="text-3xl font-extrabold text-red-950">ICMS Ready</h1>
        <p class="mt-3 text-red-900/70">
            The modular Academic Management System has been initialized.
        </p>
        <a href="{{ route('dashboard') }}"
            class="mt-6 inline-flex rounded-xl bg-red-700 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">
            Open Dashboard
        </a>
    </section>
@endsection
