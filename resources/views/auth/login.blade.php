@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl rounded-3xl border border-red-200 bg-white/95 p-8 shadow-xl shadow-red-200/60">
        <h1 class="text-3xl font-extrabold text-red-950">Sign In</h1>
        <p class="mt-3 text-sm text-red-900/70">Login with your email or staff ID and password.</p>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="login" class="block text-sm font-semibold text-red-900">Email or Staff ID</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
                @error('login')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-red-900">Password</label>
                <input id="password" name="password" type="password" required
                    class="mt-2 w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-red-950 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-200" />
                @error('password')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-red-900/80">
                <input type="checkbox" name="remember" value="1"
                    class="rounded border-red-300 text-red-700 focus:ring-red-300" />
                Remember me
            </label>

            <button type="submit"
                class="w-full rounded-xl bg-red-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                Login
            </button>
        </form>

        @if (config('services.sso.enabled'))
            <div class="mt-5 flex items-center gap-4">
                <div class="h-px flex-1 bg-red-200"></div>
                <span class="text-xs font-semibold uppercase tracking-[0.24em] text-red-900/50">or</span>
                <div class="h-px flex-1 bg-red-200"></div>
            </div>

            <a href="{{ route('integration.sso.redirect') }}"
                class="mt-5 flex w-full items-center justify-center rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-950 transition hover:border-red-400 hover:bg-red-100">
                Login with SSO
            </a>
        @endif

        <div class="mt-6 flex items-center justify-between text-sm">
            <a href="{{ route('password.request') }}"
                class="font-semibold text-red-900 underline decoration-red-300 underline-offset-4">Forgot password?</a>

            <span class="text-red-900/60">Use staff credentials assigned by admin.</span>
        </div>
    </section>
@endsection
