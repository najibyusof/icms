@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl rounded-3xl border border-red-200 bg-white/95 p-8 shadow-xl shadow-red-200/60">
        <h1 class="text-3xl font-extrabold text-red-950">Sign In</h1>
        <p class="mt-3 text-sm text-red-900/70">
            Authentication can be handled by internal auth or external SSO. Plug your identity provider into the Integration
            module.
        </p>

        <div class="mt-8 rounded-2xl bg-red-50 p-5 text-sm text-red-900">
            SSO endpoint scaffolding is available at <strong>/integration/sso/validate-token</strong>.
        </div>
    </section>
@endsection
