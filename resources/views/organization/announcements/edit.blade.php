@extends('layouts.app')
@section('title', 'Edit announcement')
@section('page-title', 'Edit announcement')
@section('content')
    @include('organization.announcements._form', ['action' => route('organization.announcements.update', $announcement), 'announcement' => $announcement, 'method' => 'PUT'])
@endsection
