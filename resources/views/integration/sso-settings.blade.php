@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-red-950">SSO Settings</h1>
                <p class="text-sm text-red-900/70">Manage the default role and external-to-local role mappings used during
                    SSO login.</p>
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

        @php
            $roleMap = $settings['role_map'] ?? [];
            $rows = old('external_roles')
                ? collect(old('external_roles'))
                    ->map(fn($value, $i) => ['external' => $value, 'local' => old('local_roles.' . $i)])
                    ->all()
                : collect($roleMap)
                    ->map(fn($local, $external) => ['external' => $external, 'local' => $local])
                    ->values()
                    ->all();
            if ($rows === []) {
                $rows = [['external' => '', 'local' => '']];
            }
        @endphp

        <form method="POST" action="{{ route('integration.sso.settings.save') }}"
            class="space-y-5 rounded-2xl border border-red-200 bg-white/80 p-6">
            @csrf

            <div class="max-w-md">
                <label class="mb-1 block text-sm font-semibold text-red-900">Default Role</label>
                <select name="default_role" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                    @foreach ($availableRoles as $role)
                        <option value="{{ $role }}" @selected(old('default_role', $settings['default_role'] ?? 'Lecturer') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-red-200">
                <div class="flex items-center justify-between border-b border-red-100 bg-red-50 px-4 py-3">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-red-900">Role Mapping</h2>
                    <button type="button" onclick="addRoleRow()"
                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-900 hover:bg-red-100">Add
                        Mapping</button>
                </div>
                <div id="roleMapRows" class="space-y-3 p-4">
                    @foreach ($rows as $index => $row)
                        <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto] role-map-row">
                            <input type="text" name="external_roles[]" value="{{ $row['external'] }}"
                                placeholder="external role e.g. lecturer"
                                class="rounded-lg border border-red-200 px-3 py-2 text-sm">
                            <select name="local_roles[]" class="rounded-lg border border-red-200 px-3 py-2 text-sm">
                                <option value="">Select local role</option>
                                @foreach ($availableRoles as $role)
                                    <option value="{{ $role }}" @selected($row['local'] === $role)>{{ $role }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" onclick="removeRoleRow(this)"
                                class="rounded-lg border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Save SSO
                    Settings</button>
            </div>
        </form>
    </div>

    <template id="roleMapRowTemplate">
        <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto] role-map-row">
            <input type="text" name="external_roles[]" placeholder="external role e.g. lecturer"
                class="rounded-lg border border-red-200 px-3 py-2 text-sm">
            <select name="local_roles[]" class="rounded-lg border border-red-200 px-3 py-2 text-sm">
                <option value="">Select local role</option>
                @foreach ($availableRoles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
            <button type="button" onclick="removeRoleRow(this)"
                class="rounded-lg border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
        </div>
    </template>

    <script>
        function addRoleRow() {
            const template = document.getElementById('roleMapRowTemplate');
            const container = document.getElementById('roleMapRows');
            container.appendChild(template.content.cloneNode(true));
        }

        function removeRoleRow(button) {
            const row = button.closest('.role-map-row');
            const container = document.getElementById('roleMapRows');

            if (container.querySelectorAll('.role-map-row').length === 1) {
                row.querySelector('input').value = '';
                row.querySelector('select').value = '';
                return;
            }

            row.remove();
        }
    </script>
@endsection
