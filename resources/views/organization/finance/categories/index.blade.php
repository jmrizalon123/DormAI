@extends('layouts.app')
@section('title', 'Expense categories')
@section('page-title', 'Expense categories')
@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="da-panel p-4">
                <h2 class="h6 mb-3">Add category</h2>
                <form method="POST" action="{{ route('organization.expense-categories.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
                    <button type="submit" class="btn btn-primary btn-sm">Add category</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="da-panel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><x-da-table-num-th /><th>Name</th><th>Expenses</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <x-da-table-num-td :paginator="$categories" :row="$loop->index" />
                                    <td>
                                        <div class="fw-semibold">{{ $category->name }}</div>
                                        @if($category->description)<div class="small text-muted">{{ $category->description }}</div>@endif
                                    </td>
                                    <td>{{ $category->expenses_count }}</td>
                                    <td><span class="badge text-bg-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('organization.expense-categories.update', $category) }}" class="d-inline-flex gap-1 align-items-center">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="name" value="{{ $category->name }}">
                                            <input type="hidden" name="description" value="{{ $category->description }}">
                                            <input type="hidden" name="is_active" value="{{ $category->is_active ? 0 : 1 }}">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $category->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                        @if($category->expenses_count === 0)
                                            <form method="POST" action="{{ route('organization.expense-categories.destroy', $category) }}" class="d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-4">No categories yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-da-pagination :paginator="$categories" />
            </div>
        </div>
    </div>
@endsection
