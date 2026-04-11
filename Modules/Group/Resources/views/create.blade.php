@extends('layouts.app')

@section('title', 'Create Group')

@section('content')
    @include('group::partials.form', [
        'group' => new \Modules\Group\Models\AcademicGroup(['is_active' => true]),
        'programmes' => $programmes,
        'coordinators' => \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'email']),
        'mode' => 'create',
        'action' => route('groups.store'),
        'submitLabel' => 'Create group',
        'cancelUrl' => route('groups.index'),
    ])
@endsection
