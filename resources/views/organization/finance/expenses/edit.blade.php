@extends('layouts.app')
@section('title', 'Edit expense')
@section('page-title', 'Edit expense')
@section('content')
    <div class="da-panel p-4">
        <form method="POST" action="{{ route('organization.expenses.update', $expense) }}">
            @csrf
            @method('PUT')
            @include('organization.finance.expenses._form')
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Update expense</button>
                <a href="{{ route('organization.expenses.show', $expense) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
