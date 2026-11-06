@extends('layouts.app')
@section('title', 'Add utility meter')
@section('page-title', 'Add utility meter')
@section('content')
    <div class="da-panel p-4">
        <form method="POST" action="{{ route('organization.utility-meters.store') }}">@csrf @include('organization.utilities.meters._form')<div class="mt-4"><button class="btn btn-primary">Save meter</button></div></form>
    </div>
@endsection
