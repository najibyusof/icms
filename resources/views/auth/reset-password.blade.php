@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl rounded-3xl border border-red-200 bg-white/95 p-8 shadow-xl shadow-red-200/60">
        <h1 class="text-3xl font-extrabold text-red-950">Reset Password</h1>
        <p class="mt-3 text-sm text-red-900/70">Create a new password for your account.</p>

        <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-semibold text-red-900">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
                @error('email')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-red-900">New Password</label>
                <input id="password" name="password" type="password" required
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
                @error('password')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-red-900">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-red-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                Reset Password
            </button>
        </form>
    </section>
@endsection
