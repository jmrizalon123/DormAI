@php $expense = $expense ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Category</label><select name="expense_category_id" class="form-select" required>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('expense_category_id', $expense?->expense_category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Property (optional)</label><select name="property_id" class="form-select"><option value="">Organization-wide</option>@foreach($properties as $property)<option value="{{ $property->id }}" @selected(old('property_id', $expense?->property_id)==$property->id)>{{ $property->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Expense date</label><input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div>
    <div class="col-md-4"><label class="form-label">Amount (₱)</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $expense?->amount) }}" required></div>
    <div class="col-md-4"><label class="form-label">Reference #</label><input type="text" name="reference_number" class="form-control" value="{{ old('reference_number', $expense?->reference_number) }}"></div>
    <div class="col-md-6"><label class="form-label">Vendor / payee</label><input type="text" name="vendor" class="form-control" value="{{ old('vendor', $expense?->vendor) }}"></div>
    <div class="col-md-6"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{ old('description', $expense?->description) }}" required></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $expense?->notes) }}</textarea></div>
</div>
