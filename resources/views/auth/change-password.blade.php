@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl rounded-3xl border border-red-200 bg-white/95 p-8 shadow-xl shadow-red-200/60">
        <h1 class="text-3xl font-extrabold text-red-950">Change Password</h1>
        <p class="mt-3 text-sm text-red-900/70">Use a minimum of 8 characters and include at least one number.</p>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}" class="mt-8 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-sm font-semibold text-red-900">Current Password</label>
                <input id="current_password" name="current_password" type="password" required
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
                @error('current_password')
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
                <label for="password_confirmation" class="block text-sm font-semibold text-red-900">Confirm New
                    Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-red-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                Update Password
            </button>
        </form>
    </section>
@endsection
