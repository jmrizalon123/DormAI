<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $contract->contract_number }}</title>
    <style>
        body { font-family: Georgia, serif; max-width: 720px; margin: 2rem auto; line-height: 1.5; color: #111; }
        h1 { font-size: 1.25rem; margin-bottom: 0.25rem; }
        .meta { color: #555; font-size: 0.9rem; margin-bottom: 1.5rem; }
        @media print { body { margin: 1rem; } }
    </style>
</head>
<body>
    <h1>Rental agreement {{ $contract->contract_number }}</h1>
    <p class="meta">{{ $contract->tenant->fullName() }} · {{ $contract->property->name }} · Room {{ $contract->room->room_number }} · {{ $contract->bedSpace->label }}</p>
    <div>{!! nl2br(e($contract->terms)) !!}</div>
    <script>window.onload = function () { window.print(); };</script>
</body>
</html>
