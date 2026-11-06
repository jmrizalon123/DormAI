@extends('layouts.app')
@section('title', 'Add property')
@section('page-title', 'Add property')
@section('page-subtitle', 'Use the tabs to add basics, amenities, and marketing details that attract tenants.')
@section('content')
    @include('organization.properties._form', [
        'action' => route('organization.properties.store'),
        'cancelUrl' => route('organization.properties.index'),
    ])
@endsection
