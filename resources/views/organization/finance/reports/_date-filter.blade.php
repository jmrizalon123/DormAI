<div class="da-filter-bar mb-4">
    <form method="GET" class="row g-3 da-filter-row align-items-end">
        <div class="col-12 col-md-4"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}"></div>
        <div class="col-12 col-md-4"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}"></div>
        <div class="col-12 col-md-4 da-filter-submit"><button type="submit" class="btn btn-primary w-100">Update period</button></div>
    </form>
    @include('organization.finance.reports._export-actions')
</div>
