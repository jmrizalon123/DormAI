@extends('layouts.app')
@section('title', 'Edit '.$property->name)
@section('page-title', 'Edit property')
@section('page-subtitle', $property->name.' · Update listing, location, amenities, and photos.')
@section('content')
    @include('organization.properties._form', [
        'property' => $property,
        'action' => route('organization.properties.update', $property),
        'method' => 'PUT',
        'cancelUrl' => route('organization.properties.show', $property),
    ])
@endsection
