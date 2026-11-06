@php($template = $contractTemplate ?? null)
<form method="POST" action="{{ $template ? route('organization.contract-templates.update', $template) : route('organization.contract-templates.store') }}" class="card border-0 shadow-sm">
    @csrf
    @if($template) @method('PUT') @endif
    <div class="card-body row g-3">
        <div class="col-md-8"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $template?->name) }}" required></div>
        <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default" @checked(old('is_default', $template?->is_default))><label class="form-check-label" for="is_default">Default template</label></div></div>
        <div class="col-12">
            <label class="form-label">Body</label>
            <textarea name="body" class="form-control" rows="12" required>{{ old('body', $template?->body) }}</textarea>
            <div class="form-text">Placeholders: @{{organization_name}}, @{{tenant_name}}, @{{property_name}}, @{{room_number}}, @{{bed_label}}, @{{monthly_rate}}, @{{security_deposit}}, @{{advance_payment}}, @{{start_date}}, @{{end_date}}</div>
        </div>
        <div class="col-12"><button class="btn btn-primary">Save</button></div>
    </div>
</form>
