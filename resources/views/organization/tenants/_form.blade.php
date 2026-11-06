<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ $action }}">@csrf @if(!empty($method)) @method($method) @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">First name *</label><input name="first_name" class="form-control" value="{{ old('first_name', $tenant->first_name ?? '') }}" required></div>
            <div class="col-md-4"><label class="form-label">Middle name</label><input name="middle_name" class="form-control" value="{{ old('middle_name', $tenant->middle_name ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Last name *</label><input name="last_name" class="form-control" value="{{ old('last_name', $tenant->last_name ?? '') }}" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $tenant->email ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $tenant->phone ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Date of birth</label><input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($tenant->date_of_birth ?? null)?->format('Y-m-d')) }}"></div>
            <div class="col-md-4"><label class="form-label">Gender</label><input name="gender" class="form-control" value="{{ old('gender', $tenant->gender ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(App\Enums\TenantStatus::cases() as $s)<option value="{{ $s->value }}" @selected(old('status', $tenant?->status?->value ?? 'prospect') === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label">Address</label><input name="address_line1" class="form-control" value="{{ old('address_line1', $tenant->address_line1 ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">City</label><input name="city" class="form-control" value="{{ old('city', $tenant->city ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">Province</label><input name="province" class="form-control" value="{{ old('province', $tenant->province ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">School / work</label><input name="school_or_work" class="form-control" value="{{ old('school_or_work', $tenant->school_or_work ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label">Valid ID type</label><input name="valid_id_type" class="form-control" value="{{ old('valid_id_type', $tenant->valid_id_type ?? '') }}"></div>
            <div class="col-md-3"><label class="form-label">Valid ID number</label><input name="valid_id_number" class="form-control" value="{{ old('valid_id_number', $tenant->valid_id_number ?? '') }}"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $tenant->notes ?? '') }}</textarea></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Save</button><a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Cancel</a></div>
    </form>
</div></div>
