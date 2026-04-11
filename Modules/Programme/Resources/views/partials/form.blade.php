@php
    $isEdit = $mode === 'edit';
    $statusLabel = $programme->status ? str($programme->status)->headline() : 'Draft';
@endphp

<div class="ams-shell">
    <section
        class="relative overflow-hidden rounded-4xl border border-white/70 bg-[linear-gradient(135deg,rgba(95,15,19,0.96),rgba(159,33,42,0.82))] px-6 py-7 text-white shadow-[0_28px_80px_-34px_rgba(47,6,6,0.7)] sm:px-8">
        <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-3">
                <span
                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-red-50/90">
                    Programme Workspace
                </span>
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $isEdit ? 'Refine programme strategy and governance' : 'Build a premium programme record' }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-red-50/80 sm:text-base">
                        Capture academic identity, leadership ownership, and accreditation context in one structured
                        workflow.
                    </p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Lifecycle</p>
                    <p class="mt-2 text-xl font-semibold">{{ $statusLabel }}</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Duration</p>
                    <p class="mt-2 text-xl font-semibold">
                        {{ old('duration_semesters', $programme->duration_semesters ?: 8) }} Sem</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Ownership</p>
                    <p class="mt-2 text-sm font-semibold">
                        {{ $programme->programmeChair?->name ?? 'Chair assignment pending' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="ams-card p-3 sm:p-4">
        <div class="flex flex-wrap gap-2" data-tabs>
            <button type="button" class="ams-tab ams-tab-active" data-tab-target="programme-overview">Overview</button>
            <button type="button" class="ams-tab" data-tab-target="programme-governance">Governance</button>
            <button type="button" class="ams-tab" data-tab-target="programme-readiness">Readiness</button>
        </div>
    </section>

    <form action="{{ $action }}" method="POST" class="ams-shell">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                <div id="programme-overview" data-tab-panel class="ams-card p-6 sm:p-8">
                    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="ams-stat-label">Academic Identity</p>
                            <h2 class="mt-2 text-2xl font-semibold text-red-950">Programme profile</h2>
                        </div>
                        <p class="max-w-xl text-sm text-red-700">Define the qualification code, market-facing title, and
                            academic level used across downstream curriculum workflows.</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="space-y-2 text-sm font-medium text-red-900">
                            <span>Programme code <span class="text-red-600">*</span></span>
                            <input type="text" name="code" value="{{ old('code', $programme->code) }}"
                                class="ams-field @error('code') border-red-400 ring-4 ring-red-100 @enderror"
                                placeholder="e.g. CS101" required>
                            @error('code')
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-2 text-sm font-medium text-red-900">
                            <span>Programme level <span class="text-red-600">*</span></span>
                            <select name="level"
                                class="ams-select @error('level') border-red-400 ring-4 ring-red-100 @enderror"
                                required>
                                <option value="">Select level</option>
                                @foreach (['Diploma', 'Bachelor', 'Master', 'PhD'] as $level)
                                    <option value="{{ $level }}" @selected(old('level', $programme->level) === $level)>{{ $level }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level')
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-2 text-sm font-medium text-red-900 md:col-span-2">
                            <span>Programme name <span class="text-red-600">*</span></span>
                            <input type="text" name="name" value="{{ old('name', $programme->name) }}"
                                class="ams-field @error('name') border-red-400 ring-4 ring-red-100 @enderror"
                                placeholder="e.g. Bachelor of Computer Science" required>
                            @error('name')
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-2 text-sm font-medium text-red-900 md:col-span-2">
                            <span>Strategic description</span>
                            <textarea name="description" class="ams-textarea @error('description') border-red-400 ring-4 ring-red-100 @enderror"
                                placeholder="Summarise the programme mission, graduate direction, and academic proposition.">{{ old('description', $programme->description) }}</textarea>
                            @error('description')
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </div>

                <div id="programme-governance" data-tab-panel class="hidden ams-card p-6 sm:p-8">
                    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="ams-stat-label">Governance</p>
                            <h2 class="mt-2 text-2xl font-semibold text-red-950">Oversight and compliance</h2>
                        </div>
                        <p class="max-w-xl text-sm text-red-700">Assign accountable owners and capture accreditation
                            references before routing the programme into approval workflows.</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="space-y-2 text-sm font-medium text-red-900">
                            <span>Accreditation body</span>
                            <input type="text" name="accreditation_body"
                                value="{{ old('accreditation_body', $programme->accreditation_body) }}"
                                class="ams-field @error('accreditation_body') border-red-400 ring-4 ring-red-100 @enderror"
                                placeholder="e.g. Malaysian Qualifications Agency">
                            @error('accreditation_body')
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-2 text-sm font-medium text-red-900">
                            <span>Programme chair</span>
                            <select name="programme_chair_id"
                                class="ams-select @error('programme_chair_id') border-red-400 ring-4 ring-red-100 @enderror">
                                <option value="">Select programme chair</option>
                                @foreach ($chairs as $chair)
                                    <option value="{{ $chair->id }}" @selected((string) old('programme_chair_id', $programme->programme_chair_id) === (string) $chair->id)>
                                        {{ $chair->name }} ({{ $chair->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('programme_chair_id')
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </div>

                <div id="programme-readiness" data-tab-panel class="hidden ams-card p-6 sm:p-8">
                    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="ams-stat-label">Operational Readiness</p>
                            <h2 class="mt-2 text-2xl font-semibold text-red-950">Delivery settings</h2>
                        </div>
                        <p class="max-w-xl text-sm text-red-700">Prepare the programme for scheduling, quality review,
                            and activation with a clear duration and publication state.</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="space-y-2 text-sm font-medium text-red-900">
                            <span>Duration in semesters <span class="text-red-600">*</span></span>
                            <input type="number" name="duration_semesters" min="1" max="20"
                                value="{{ old('duration_semesters', $programme->duration_semesters ?: 8) }}"
                                class="ams-field @error('duration_semesters') border-red-400 ring-4 ring-red-100 @enderror"
                                required>
                            @error('duration_semesters')
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label
                            class="flex items-center justify-between rounded-3xl border border-red-100 bg-red-50/70 px-5 py-4 text-sm text-red-900">
                            <div>
                                <span class="block font-semibold">Active programme</span>
                                <span class="mt-1 block text-red-700">Controls whether the programme is available for
                                    operational use.</span>
                            </div>
                            <input type="checkbox" name="is_active" value="1"
                                class="h-5 w-5 rounded border-red-300 text-red-900 focus:ring-red-300"
                                @checked(old('is_active', $programme->is_active ?? true))>
                        </label>
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="ams-card p-6">
                    <p class="ams-stat-label">Review Summary</p>
                    <div class="mt-4 space-y-4">
                        <div class="rounded-3xl border border-red-100 bg-red-50/70 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-700">Current status
                            </p>
                            <p class="mt-2 text-lg font-semibold text-red-950">{{ $statusLabel }}</p>
                        </div>
                        <div class="rounded-3xl border border-red-100 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-700">Form coverage</p>
                            <div class="mt-3 space-y-3 text-sm text-red-800">
                                <div class="flex items-center justify-between">
                                    <span>Identity</span>
                                    <span>{{ old('code', $programme->code) ? 'Ready' : 'Pending' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Governance</span>
                                    <span>{{ old('programme_chair_id', $programme->programme_chair_id) || old('accreditation_body', $programme->accreditation_body) ? 'In progress' : 'Pending' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Operational setup</span>
                                    <span>{{ old('duration_semesters', $programme->duration_semesters) ? 'Ready' : 'Pending' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ams-card p-6">
                    <p class="ams-stat-label">Actions</p>
                    <div class="mt-4 flex flex-col gap-3">
                        <button type="submit" class="ams-button-primary w-full justify-center">
                            {{ $submitLabel }}
                        </button>
                        <a href="{{ $cancelUrl }}" class="ams-button-secondary w-full justify-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </aside>
        </section>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-tabs]').forEach((tabSet) => {
                const buttons = tabSet.querySelectorAll('[data-tab-target]');
                const panels = document.querySelectorAll('[data-tab-panel]');

                buttons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const target = button.dataset.tabTarget;

                        buttons.forEach((candidate) => {
                            candidate.classList.remove('ams-tab-active');
                        });

                        panels.forEach((panel) => {
                            panel.classList.toggle('hidden', panel.id !== target);
                        });

                        button.classList.add('ams-tab-active');
                    });
                });
            });
        });
    </script>
@endpush
