@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl rounded-3xl border border-red-200 bg-white/95 p-8 shadow-xl shadow-red-200/60">
        <h1 class="text-3xl font-extrabold text-red-950">Forgot Password</h1>
        <p class="mt-3 text-sm text-red-900/70">Enter your email and we will send you a secure reset link.</p>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold text-red-900">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
                @error('email')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-red-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                Send Reset Link
            </button>
        </form>

        <p class="mt-6 text-sm text-red-900/70">
            Remembered your password?
            <a href="{{ route('login') }}"
                class="font-semibold text-red-900 underline decoration-red-300 underline-offset-4">Back to sign in</a>
        </p>
    </section>
@endsection
