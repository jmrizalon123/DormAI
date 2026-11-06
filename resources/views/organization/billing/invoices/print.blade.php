<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>{{ $invoice->invoice_number }}</title>
<style>body{font-family:system-ui,sans-serif;max-width:720px;margin:2rem auto;color:#111} table{width:100%;border-collapse:collapse} th,td{padding:.5rem;border-bottom:1px solid #e2e8f0;text-align:left} .text-end{text-align:right}</style></head>
<body>
<h1>Invoice {{ $invoice->invoice_number }}</h1>
<p>{{ $invoice->organization->name }}<br>Bill to: {{ $invoice->tenant->fullName() }}<br>Due: {{ $invoice->due_date->format('M j, Y') }}</p>
<table><thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
<tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td class="text-end">₱{{ number_format($item->amount, 2) }}</td></tr>@endforeach</tbody>
<tfoot><tr><th>Balance due</th><th class="text-end">₱{{ number_format($invoice->balance, 2) }}</th></tr></tfoot></table>
<script>window.onload=function(){window.print()}</script>
</body></html>
