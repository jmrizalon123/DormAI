@extends('layouts.app')
@section('page-title', 'Room '.$room->room_number)
@section('page-subtitle', $room->property->name.' · '.$room->building->name.' · '.$room->floor->name)
@section('page-actions')
    <a href="{{ route('organization.rooms.edit', $room) }}" class="btn btn-outline-secondary btn-sm">Edit room</a>
@endsection
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Bed spaces</div><div class="fs-4 fw-semibold">{{ $bedSummary['total'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Available</div><div class="fs-4 fw-semibold">{{ $bedSummary['available'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Occupied</div><div class="fs-4 fw-semibold">{{ $bedSummary['occupied'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Room status</div><div><span class="badge text-bg-{{ $room->status->badgeClass() }}">{{ $room->status->label() }}</span></div></div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center"><span class="fw-medium">Bed spaces</span></div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light"><tr><x-da-table-num-th /><th>Label</th><th>Rate</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($room->bedSpaces as $bed)
                                <tr>
                                    <x-da-table-num-td :row="$loop->index" />
                                    <td>{{ $bed->label }}</td>
                                    <td>₱{{ number_format($bed->monthly_rate ?? $room->monthly_rate, 2) }}</td>
                                    <td><span class="badge text-bg-{{ $bed->status->badgeClass() }}">{{ $bed->status->label() }}</span></td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('organization.rooms.bed-spaces.update', [$room, $bed]) }}" class="d-inline-flex gap-1 align-items-center">@csrf @method('PUT')
                                            <input type="hidden" name="label" value="{{ $bed->label }}">
                                            <select name="status" class="form-select form-select-sm" data-da-submit-on-change>@foreach(App\Enums\BedSpaceStatus::cases() as $s)<option value="{{ $s->value }}" @selected($bed->status === $s)>{{ $s->label() }}</option>@endforeach</select>
                                        </form>
                                        @if($bed->status !== App\Enums\BedSpaceStatus::Occupied)
                                            <form method="POST" action="{{ route('organization.rooms.bed-spaces.destroy', [$room, $bed]) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">×</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($room->bedSpaces->count() < $room->capacity)
                    <div class="card-footer bg-white">
                        <form method="POST" action="{{ route('organization.rooms.bed-spaces.store', $room) }}" class="row g-2">@csrf
                            <div class="col-md-3"><input name="label" class="form-control form-control-sm" placeholder="Label" required></div>
                            <div class="col-md-3"><input name="monthly_rate" type="number" step="0.01" class="form-control form-control-sm" placeholder="Rate"></div>
                            <div class="col-md-3"><select name="status" class="form-select form-select-sm">@foreach(App\Enums\BedSpaceStatus::cases() as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach</select></div>
                            <div class="col-md-3"><button class="btn btn-sm btn-primary w-100">Add bed</button></div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-medium">Room photos</div>
                <div class="card-body">
                    @foreach($room->images as $image)
                        <img src="{{ $image->url() }}" class="img-fluid rounded border mb-2" alt="">
                        <form method="POST" action="{{ route('organization.rooms.images.destroy', [$room, $image]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mb-3">Remove</button></form>
                    @endforeach
                    <form method="POST" action="{{ route('organization.rooms.images.store', $room) }}" enctype="multipart/form-data">@csrf
                        <input type="file" name="image" class="form-control mb-2" accept="image/*" required>
                        <button class="btn btn-sm btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
