@extends('layouts.app')
@section('title', 'New announcement')
@section('page-title', 'New announcement')
@section('content')
    @include('organization.announcements._form', ['action' => route('organization.announcements.store')])
@endsection
