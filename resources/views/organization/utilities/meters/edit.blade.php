@extends('layouts.app')
@section('title', 'Edit meter')
@section('page-title', 'Edit meter')
@section('content')
    <div class="da-panel p-4">
        <form method="POST" action="{{ route('organization.utility-meters.update', $meter) }}">@csrf @method('PUT') @include('organization.utilities.meters._form', ['meter' => $meter])<div class="mt-4"><button class="btn btn-primary">Update</button></div></form>
    </div>
@endsection
