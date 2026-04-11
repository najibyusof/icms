@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-red-950">{{ $jsu->title }}</h1>
                <p class="text-sm text-red-900/70">
                    {{ $jsu->course?->code }} - {{ $jsu->course?->name }} | {{ $jsu->academic_session }} |
                    {{ ucfirst($jsu->exam_type) }}
                </p>
            </div>
            <div class="flex gap-2">
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-900">Status:
                    {{ ucfirst($jsu->status) }}</span>
                <a href="{{ route('jsu.manage.index') }}"
                    class="rounded-lg border border-red-300 px-3 py-1.5 text-sm font-semibold text-red-900 hover:bg-red-50">Back</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-700">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-red-200 bg-white/80 p-4 lg:col-span-2">
                <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-red-900">Blueprint Entries</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-red-100 text-sm">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Q#</th>
                                <th class="px-3 py-2 text-left">CLO</th>
                                <th class="px-3 py-2 text-left">Bloom</th>
                                <th class="px-3 py-2 text-left">Marks</th>
                                <th class="px-3 py-2 text-left">Weight</th>
                                <th class="px-3 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100">
                            @forelse ($jsu->blueprints as $bp)
                                <tr>
                                    <td class="px-3 py-2">{{ $bp->question_no }}</td>
                                    <td class="px-3 py-2">{{ $bp->clo?->clo_no ? 'CLO ' . $bp->clo->clo_no : '-' }}</td>
                                    <td class="px-3 py-2">L{{ $bp->bloom_level }} -
                                        {{ $bloomLevels[$bp->bloom_level] ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ number_format($bp->marks, 2) }}</td>
                                    <td class="px-3 py-2">
                                        {{ $bp->weight_percentage !== null ? number_format($bp->weight_percentage, 2) . '%' : '-' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @can('update', $jsu)
                                            <form method="POST"
                                                action="{{ route('jsu.manage.blueprints.destroy', [$jsu, $bp]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-md border border-rose-300 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-red-900/70">No blueprint rows yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('update', $jsu)
                    <form method="POST" action="{{ route('jsu.manage.blueprints.store', $jsu) }}"
                        class="mt-4 grid gap-3 rounded-xl border border-red-200 p-3 md:grid-cols-6">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-red-900">Q#</label>
                            <input type="number" name="question_no" min="1"
                                class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-sm" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-red-900">CLO</label>
                            <select name="clo_id" class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-sm">
                                <option value="">None</option>
                                @foreach ($clos as $clo)
                                    <option value="{{ $clo->id }}">CLO {{ $clo->clo_no }} -
                                        {{ \Illuminate\Support\Str::limit($clo->statement, 45) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-red-900">Bloom</label>
                            <select name="bloom_level" class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-sm"
                                required>
                                @foreach ($bloomLevels as $level => $label)
                                    <option value="{{ $level }}">L{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-red-900">Marks</label>
                            <input type="number" name="marks" min="0" step="0.01"
                                class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-sm" required>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full rounded-lg bg-red-900 px-3 py-2 text-xs font-semibold text-white hover:bg-red-800">Save
                                Row</button>
                        </div>
                    </form>
                @endcan
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-red-200 bg-white/80 p-4">
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-red-900">Difficulty Check</h2>
                    <div class="space-y-2 text-sm">
                        @foreach ($distribution as $group => $item)
                            <div
                                class="rounded-lg border px-3 py-2 {{ $item['within_tolerance'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                                <div class="font-semibold uppercase">{{ $group }}</div>
                                <div>Target: {{ $item['target_pct'] }}%</div>
                                <div>Actual: {{ $item['actual_pct'] }}%</div>
                            </div>
                        @endforeach
                        <p class="text-xs text-red-900/70">Tolerance: ±{{ $tolerance }}%</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-red-200 bg-white/80 p-4">
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-red-900">Workflow Actions</h2>
                    <div class="space-y-2">
                        @can('submit', $jsu)
                            <form method="POST" action="{{ route('jsu.manage.submit', $jsu) }}">@csrf
                                <button type="submit"
                                    class="w-full rounded-lg bg-red-900 px-3 py-2 text-sm font-semibold text-white hover:bg-red-800">Submit
                                    for Approval</button>
                            </form>
                        @endcan

                        @can('approve', $jsu)
                            <form method="POST" action="{{ route('jsu.manage.approve', $jsu) }}" class="space-y-2">@csrf
                                <input name="comment" class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-sm"
                                    placeholder="Approval comment (optional)">
                                <button type="submit"
                                    class="w-full rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">Approve
                                    Step</button>
                            </form>

                            <form method="POST" action="{{ route('jsu.manage.reject', $jsu) }}" class="space-y-2">@csrf
                                <textarea name="reason" rows="2" class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-sm"
                                    placeholder="Rejection reason" required></textarea>
                                <button type="submit"
                                    class="w-full rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Reject</button>
                            </form>
                        @endcan

                        @can('activate', $jsu)
                            <form method="POST" action="{{ route('jsu.manage.activate', $jsu) }}">@csrf
                                <button type="submit"
                                    class="w-full rounded-lg border border-sky-300 bg-sky-50 px-3 py-2 text-sm font-semibold text-sky-800 hover:bg-sky-100">Activate
                                    JSU</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
