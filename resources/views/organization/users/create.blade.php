@extends('layouts.app')

@section('title', 'Add team member — '.config('app.name'))
@section('page-title', 'Add team member')

@section('content')
    @include('organization.users._form', [
        'action' => route('organization.users.store'),
        'method' => 'POST',
        'user' => null,
        'roles' => $roles,
    ])
@endsection
