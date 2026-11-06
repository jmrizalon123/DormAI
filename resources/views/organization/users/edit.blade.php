@extends('layouts.app')

@section('title', 'Edit team member — '.config('app.name'))
@section('page-title', 'Edit team member')

@section('content')
    @include('organization.users._form', [
        'action' => route('organization.users.update', $user),
        'method' => 'PUT',
        'user' => $user,
        'roles' => $roles,
    ])
@endsection
