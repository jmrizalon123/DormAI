@extends('layouts.app')
@section('page-title', 'Edit tenant')
@section('content')
    @include('organization.tenants._form', ['tenant' => $tenant, 'action' => route('organization.tenants.update', $tenant), 'method' => 'PUT', 'cancelUrl' => route('organization.tenants.show', $tenant)])
@endsection
