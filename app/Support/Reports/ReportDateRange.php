<?php

namespace App\Support\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;

final class ReportDateRange
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->string('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return new self($from, $to);
    }
}
