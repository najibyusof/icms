@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-red-950">Notification Settings</h1>
                <p class="text-sm text-red-900/70">Enable or disable each channel by workflow state.</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-900 hover:bg-red-50">Back</a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-700">
                <ul class="list-disc pl-6 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('notifications.settings.save') }}"
            class="overflow-hidden rounded-2xl border border-red-200 bg-white/80">
            @csrf

            <table class="min-w-full divide-y divide-red-100 text-sm">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">State</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Database</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Mail</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Telegram</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Push (FCM)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-100">
                    @foreach (['submitted', 'approved', 'rejected'] as $state)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-red-950">{{ ucfirst($state) }}</td>
                            @foreach (['database', 'mail', 'telegram', 'push'] as $channel)
                                <td class="px-4 py-3">
                                    <input type="hidden" name="channels[{{ $state }}][{{ $channel }}]"
                                        value="0">
                                    <input type="checkbox" name="channels[{{ $state }}][{{ $channel }}]"
                                        value="1" @checked(old("channels.{$state}.{$channel}", $matrix[$state][$channel] ?? true))
                                        class="h-4 w-4 rounded border-red-300 text-red-700 focus:ring-red-500">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex justify-end border-t border-red-100 px-4 py-4">
                <button type="submit"
                    class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Save
                    Settings</button>
            </div>
        </form>
    </div>
@endsection
