@extends('layouts.app')

@section('title', 'Edit Group')

@section('content')
    @include('group::partials.form', [
        'group' => $group,
        'programmes' => $programmes,
        'coordinators' => \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'email']),
        'mode' => 'edit',
        'action' => route('groups.update', $group),
        'submitLabel' => 'Update group',
        'cancelUrl' => route('groups.show', $group),
    ])
@endsection
