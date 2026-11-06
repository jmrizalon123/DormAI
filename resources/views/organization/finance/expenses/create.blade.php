@extends('layouts.app')
@section('title', 'Record expense')
@section('page-title', 'Record expense')
@section('content')
    <div class="da-panel p-4">
        <form method="POST" action="{{ route('organization.expenses.store') }}">
            @csrf
            @include('organization.finance.expenses._form')
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="submit_now" value="1" id="submit_now" @checked(old('submit_now'))>
                <label class="form-check-label" for="submit_now">Submit for approval after saving</label>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Save expense</button>
                <a href="{{ route('organization.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
