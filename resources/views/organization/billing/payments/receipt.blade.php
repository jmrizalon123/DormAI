<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Receipt {{ $payment->payment_number }}</title>
<style>body{font-family:system-ui,sans-serif;max-width:480px;margin:2rem auto} h1{font-size:1.25rem}</style></head>
<body>
<h1>Official receipt</h1>
<p><strong>{{ $payment->payment_number }}</strong><br>{{ $payment->organization->name }}<br>Received from: {{ $payment->tenant->fullName() }}</p>
<p>Amount: <strong>₱{{ number_format($payment->amount, 2) }}</strong><br>Method: {{ $payment->payment_method->label() }}<br>Date: {{ $payment->paid_at->format('M j, Y') }}</p>
@if($payment->allocations->isNotEmpty())<p>Applied to:@foreach($payment->allocations as $a)<br>— {{ $a->invoice->invoice_number }}: ₱{{ number_format($a->amount, 2) }}@endforeach</p>@endif
<script>window.onload=function(){window.print()}</script>
</body></html>
