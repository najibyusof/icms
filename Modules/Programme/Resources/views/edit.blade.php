@extends('layouts.app')

@section('content')
    @include('programme::partials.form', [
        'programme' => $programme,
        'chairs' => $chairs,
        'mode' => 'edit',
        'action' => route('programmes.update', $programme),
        'submitLabel' => 'Update programme',
        'cancelUrl' => route('programmes.show', $programme),
    ])
@endsection
