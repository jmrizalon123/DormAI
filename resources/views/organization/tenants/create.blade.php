@extends('layouts.app')
@section('page-title', 'Register tenant')
@section('content')
    @include('organization.tenants._form', ['action' => route('organization.tenants.store'), 'cancelUrl' => route('organization.tenants.index')])
@endsection
