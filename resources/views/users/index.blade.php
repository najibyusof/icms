@extends('layouts.app')

@section('content')

    {{-- ── Page header ── --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-red-950">User Management</h1>
            <p class="mt-0.5 text-sm text-red-700">Create and manage system user accounts.</p>
        </div>
        @can('create', App\Models\User::class)
            <button type="button" onclick="userModal.openCreate()"
                class="flex items-center gap-2 rounded-xl bg-red-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add User
            </button>
        @endcan
    </div>

    {{-- ── Flash message ── --}}
    @if (session('success'))
        <div id="flash"
            class="mb-4 flex items-center justify-between rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-sm">
            <span>{{ session('success') }}</span>
            <button onclick="document.getElementById('flash').remove()"
                class="ml-4 text-green-600 hover:text-green-900">&times;</button>
        </div>
    @endif

    {{-- ── Filters ── --}}
    <div class="mb-5 rounded-2xl border border-red-200/70 bg-white/75 p-4 backdrop-blur shadow-sm">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-semibold text-red-800 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email or staff ID…"
                    class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-red-900 placeholder-red-300 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
            </div>

            <div class="min-w-36">
                <label class="block text-xs font-semibold text-red-800 mb-1">Role</label>
                <select name="role"
                    class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-40">
                <label class="block text-xs font-semibold text-red-800 mb-1">Faculty</label>
                <input type="text" name="faculty" value="{{ request('faculty') }}" placeholder="Filter by faculty…"
                    class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-red-900 placeholder-red-300 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                    class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 transition">
                    Filter
                </button>
                <a href="{{ route('users.index') }}"
                    class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    {{-- ── Users table ── --}}
    <div class="rounded-2xl border border-red-200/70 bg-white/75 backdrop-blur shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-red-100">
            <thead class="bg-red-50/80">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Staff ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Faculty</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Role</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-red-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-red-50">
                @forelse($users as $u)
                    <tr class="hover:bg-red-50/40 transition">
                        <td class="px-4 py-3 text-sm font-medium text-red-950">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-sm text-red-700">{{ $u->email }}</td>
                        <td class="px-4 py-3 text-sm text-red-600 font-mono">{{ $u->staff_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-red-700">{{ $u->faculty ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php $roleName = $u->roles->first()?->name; @endphp
                            @if ($roleName)
                                <span
                                    class="inline-flex items-center rounded-lg bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
                                    {{ \App\Support\CanonicalRoleName::normalize($roleName) }}
                                </span>
                            @else
                                <span class="text-sm text-red-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $u)
                                    @php
                                        $editData = $u->only(['id', 'name', 'email', 'staff_id', 'faculty']);
                                        $editData['role'] = \App\Support\CanonicalRoleName::normalize($u->roles->first()?->name ?? '');
                                    @endphp
                                    <button type="button" data-user='@json($editData)'
                                        onclick="userModal.openEdit(JSON.parse(this.dataset.user))"
                                        class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50 transition">
                                        Edit
                                    </button>
                                @endcan

                                @can('delete', $u)
                                    @if ($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $u) }}"
                                            onsubmit="return confirm('Delete {{ addslashes($u->name) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-rose-400 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-red-400">
                            No users found. Try adjusting your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-t border-red-100 bg-red-50/50 px-4 py-3 text-sm text-red-700">
                <span>
                    Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
                </span>
                <div class="flex items-center gap-1">
                    @if ($users->onFirstPage())
                        <span class="cursor-not-allowed rounded-lg border border-red-200 px-3 py-1.5 text-red-300">←
                            Prev</span>
                    @else
                        <a href="{{ $users->appends(request()->query())->previousPageUrl() }}"
                            class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-800 hover:bg-red-100 transition">←
                            Prev</a>
                    @endif

                    @if ($users->hasMorePages())
                        <a href="{{ $users->appends(request()->query())->nextPageUrl() }}"
                            class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-800 hover:bg-red-100 transition">Next
                            →</a>
                    @else
                        <span class="cursor-not-allowed rounded-lg border border-red-200 px-3 py-1.5 text-red-300">Next
                            →</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════
     MODAL — Create / Edit user
══════════════════════════════════════════════════════ --}}
    <div id="userModalOverlay"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-red-950/40 backdrop-blur-sm"
        onclick="if(event.target===this)userModal.close()">

        <div class="relative w-full max-w-lg mx-4 rounded-2xl border border-red-200 bg-white shadow-2xl">

            {{-- Modal header --}}
            <div class="flex items-center justify-between border-b border-red-100 px-6 py-4">
                <h2 id="modalTitle" class="text-base font-bold text-red-950">Add User</h2>
                <button type="button" onclick="userModal.close()" class="text-red-400 hover:text-red-700 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form id="userForm" method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="list-inside list-disc space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div>
                        <label for="f_name" class="block text-xs font-semibold text-red-800 mb-1">Full Name <span
                                class="text-rose-500">*</span></label>
                        <input type="text" id="f_name" name="name" required autocomplete="off"
                            value="{{ old('name') }}"
                            class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                    </div>

                    <div>
                        <label for="f_email" class="block text-xs font-semibold text-red-800 mb-1">Email <span
                                class="text-rose-500">*</span></label>
                        <input type="email" id="f_email" name="email" required autocomplete="off"
                            value="{{ old('email') }}"
                            class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                    </div>

                    <div>
                        <label for="f_staff_id" class="block text-xs font-semibold text-red-800 mb-1">Staff ID</label>
                        <input type="text" id="f_staff_id" name="staff_id" autocomplete="off"
                            value="{{ old('staff_id') }}"
                            class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                    </div>

                    <div>
                        <label for="f_faculty" class="block text-xs font-semibold text-red-800 mb-1">Faculty</label>
                        <input type="text" id="f_faculty" name="faculty" autocomplete="off"
                            value="{{ old('faculty') }}"
                            class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="f_role" class="block text-xs font-semibold text-red-800 mb-1">Role <span
                                class="text-rose-500">*</span></label>
                        <select id="f_role" name="role" required
                            class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                            <option value="">— Select role —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="f_password" class="block text-xs font-semibold text-red-800 mb-1">
                            Password <span id="pwHint" class="font-normal text-red-400">(required)</span>
                        </label>
                        <input type="password" id="f_password" name="password" autocomplete="new-password"
                            class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                    </div>

                    <div>
                        <label for="f_password_confirmation" class="block text-xs font-semibold text-red-800 mb-1">Confirm
                            Password</label>
                        <input type="password" id="f_password_confirmation" name="password_confirmation"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm text-red-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-red-100">
                    <button type="button" onclick="userModal.close()"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-red-900 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800 transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Auto-open on edit route or validation-error re-open --}}
    @if (isset($editUser))
        @php
            $editUserData = $editUser->only(['id', 'name', 'email', 'staff_id', 'faculty']);
            $editUserData['role'] = \App\Support\CanonicalRoleName::normalize($editUser->roles->first()?->name ?? '');
        @endphp
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                userModal.openEdit(@json($editUserData));
            });
        </script>
    @elseif($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                userModal.openCreate(true);
            });
        </script>
    @endif

    <script>
        const userModal = {
            overlay: document.getElementById('userModalOverlay'),
            form: document.getElementById('userForm'),
            title: document.getElementById('modalTitle'),
            method: document.getElementById('formMethod'),
            pwHint: document.getElementById('pwHint'),

            open() {
                this.overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            },

            close() {
                this.overlay.classList.add('hidden');
                document.body.style.overflow = '';
            },

            openCreate(keepValues) {
                this.title.textContent = 'Add User';
                this.form.action = '{{ route('users.store') }}';
                this.method.value = 'POST';
                this.pwHint.textContent = '(required)';

                if (!keepValues) {
                    this.form.querySelectorAll('input:not([name="_method"]):not([name="_token"]), select').forEach(
                        function(el) {
                            if (el.type === 'hidden') return;
                            el.value = '';
                        });
                }
                this.open();
            },

            openEdit(user) {
                this.title.textContent = 'Edit User';
                this.form.action = '/users/' + user.id;
                this.method.value = 'PUT';
                this.pwHint.textContent = '(leave blank to keep current)';

                document.getElementById('f_name').value = user.name ?? '';
                document.getElementById('f_email').value = user.email ?? '';
                document.getElementById('f_staff_id').value = user.staff_id ?? '';
                document.getElementById('f_faculty').value = user.faculty ?? '';
                document.getElementById('f_role').value = user.role ?? '';
                document.getElementById('f_password').value = '';
                document.getElementById('f_password_confirmation').value = '';

                this.open();
            }
        };
    </script>

@endsection
