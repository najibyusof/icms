@extends('layouts.app')

@section('content')
    @include('programme::partials.form', [
        'programme' => new \Modules\Programme\Models\Programme(['is_active' => true, 'status' => 'draft']),
        'chairs' => $chairs,
        'mode' => 'create',
        'action' => route('programmes.store'),
        'submitLabel' => 'Create programme',
        'cancelUrl' => route('programmes.index'),
    ])
@endsection
