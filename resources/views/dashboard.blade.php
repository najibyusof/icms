@extends('layouts.app')

@section('content')
    <section class="rounded-3xl border border-red-200/60 bg-white/90 p-8 shadow-xl shadow-red-100/40 backdrop-blur">
        <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p
                    class="inline-flex rounded-full bg-red-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-red-700">
                    Enterprise Blueprint
                </p>
                <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-red-950 md:text-5xl">
                    Academic Management System
                </h1>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-red-900/75 md:text-base">
                    Modular Laravel 12 architecture with RBAC, workflow approvals, and queue-first notification delivery.
                </p>
            </div>
            <div class="rounded-2xl bg-red-950 px-6 py-4 text-right text-red-100 shadow-lg shadow-red-900/40">
                <p class="text-xs uppercase tracking-[0.2em]">Queue Driver</p>
                <p class="mt-1 text-xl font-bold">Database (Windows Ready)</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($modules as $module)
                <article
                    class="group rounded-2xl border border-red-200 bg-linear-to-br from-white via-red-50/40 to-white p-5 transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-red-200/60">
                    <h2 class="text-lg font-bold text-red-900">{{ $module }}</h2>
                    <p class="mt-2 text-sm text-red-900/70">Controller → Service → Model with policies, requests, and queued
                        events.</p>
                    <div
                        class="mt-4 h-1 w-16 rounded-full bg-red-300 transition-all duration-300 group-hover:w-full group-hover:bg-red-600">
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
