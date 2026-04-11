@php
    $isEdit = $mode === 'edit';
    $activeValue = old('is_active', $group->is_active ?? true);
@endphp

<div class="ams-shell">
    <section
        class="relative overflow-hidden rounded-4xl border border-white/70 bg-[linear-gradient(135deg,rgba(47,6,6,0.96),rgba(159,33,42,0.84))] px-6 py-7 text-white shadow-[0_28px_80px_-34px_rgba(47,6,6,0.7)] sm:px-8">
        <div class="absolute right-0 top-0 h-44 w-44 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-3">
                <span
                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-red-50/90">
                    Cohort Operations
                </span>
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $isEdit ? 'Refine group setup and ownership' : 'Create a new academic group' }}</h1>
                    <p class="mt-2 text-sm text-red-50/80 sm:text-base">Capture programme alignment, intake cycle,
                        semester placement, and coordinator ownership in one streamlined form.</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Lifecycle</p>
                    <p class="mt-2 text-lg font-semibold">{{ $activeValue ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Semester</p>
                    <p class="mt-2 text-lg font-semibold">{{ old('semester', $group->semester ?? 1) }}</p>
                </div>
            </div>
        </div>
    </section>

    <form action="{{ $action }}" method="POST" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <section class="ams-card p-6 sm:p-8">
            <div class="mb-6">
                <p class="ams-stat-label">Group Definition</p>
                <h2 class="mt-2 text-2xl font-semibold text-red-950">Core cohort details</h2>
                <p class="mt-2 text-sm text-red-700">Set the academic context and ownership required for member and
                    course assignment workflows.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="space-y-2 text-sm font-medium text-red-900 md:col-span-2">
                    <span>Programme <span class="text-red-600">*</span></span>
                    <select name="programme_id"
                        class="ams-select @error('programme_id') border-red-400 ring-4 ring-red-100 @enderror" required>
                        <option value="">Select programme</option>
                        @foreach ($programmes as $programme)
                            <option value="{{ $programme->id }}" @selected((string) old('programme_id', $group->programme_id) === (string) $programme->id)>
                                {{ $programme->code }} - {{ $programme->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('programme_id')
                        <span class="text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </label>

                <label class="space-y-2 text-sm font-medium text-red-900 md:col-span-2">
                    <span>Group name <span class="text-red-600">*</span></span>
                    <input type="text" name="name" value="{{ old('name', $group->name) }}"
                        class="ams-field @error('name') border-red-400 ring-4 ring-red-100 @enderror"
                        placeholder="e.g. CS 2026 Intake A" required>
                    @error('name')
                        <span class="text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </label>

                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Intake year <span class="text-red-600">*</span></span>
                    <input type="number" name="intake_year" min="2000" max="2100"
                        value="{{ old('intake_year', $group->intake_year ?: now()->year) }}"
                        class="ams-field @error('intake_year') border-red-400 ring-4 ring-red-100 @enderror" required>
                    @error('intake_year')
                        <span class="text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </label>

                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Semester <span class="text-red-600">*</span></span>
                    <input type="number" name="semester" min="1" max="14"
                        value="{{ old('semester', $group->semester ?: 1) }}"
                        class="ams-field @error('semester') border-red-400 ring-4 ring-red-100 @enderror" required>
                    @error('semester')
                        <span class="text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </label>

                <label class="space-y-2 text-sm font-medium text-red-900 md:col-span-2">
                    <span>Coordinator (optional)</span>
                    <select name="coordinator_id"
                        class="ams-select @error('coordinator_id') border-red-400 ring-4 ring-red-100 @enderror">
                        <option value="">Select coordinator</option>
                        @foreach ($coordinators as $coordinator)
                            <option value="{{ $coordinator->id }}" @selected((string) old('coordinator_id', $group->coordinator_id) === (string) $coordinator->id)>
                                {{ $coordinator->name }} ({{ $coordinator->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('coordinator_id')
                        <span class="text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </label>

                <label
                    class="flex items-center justify-between rounded-3xl border border-red-100 bg-red-50/70 px-5 py-4 text-sm text-red-900 md:col-span-2">
                    <div>
                        <span class="block font-semibold">Active group</span>
                        <span class="mt-1 block text-red-700">Controls whether this cohort is selectable in operational
                            workflows.</span>
                    </div>
                    <input type="checkbox" name="is_active" value="1"
                        class="h-5 w-5 rounded border-red-300 text-red-900 focus:ring-red-300"
                        @checked($activeValue)>
                </label>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="ams-card p-6">
                <p class="ams-stat-label">Form Summary</p>
                <div class="mt-4 space-y-3 text-sm text-red-800">
                    <div class="rounded-2xl border border-red-100 bg-red-50/70 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.16em] text-red-600">Programme</p>
                        <p class="mt-1 font-semibold text-red-950">
                            {{ old('programme_id', $group->programme_id) ? 'Selected' : 'Pending' }}</p>
                    </div>
                    <div class="rounded-2xl border border-red-100 bg-red-50/70 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.16em] text-red-600">Coordinator</p>
                        <p class="mt-1 font-semibold text-red-950">
                            {{ old('coordinator_id', $group->coordinator_id) ? 'Assigned' : 'Pending' }}</p>
                    </div>
                </div>
            </section>

            <section class="ams-card p-6">
                <p class="ams-stat-label">Actions</p>
                <div class="mt-4 flex flex-col gap-3">
                    <button type="submit"
                        class="ams-button-primary w-full justify-center">{{ $submitLabel }}</button>
                    <a href="{{ $cancelUrl }}" class="ams-button-secondary w-full justify-center">Cancel</a>
                </div>
            </section>
        </aside>
    </form>
</div>
