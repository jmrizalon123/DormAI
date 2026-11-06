@extends('layouts.app')

@section('title', 'Platform dashboard — '.config('app.name'))

@section('page-title', 'Platform dashboard')
@section('page-subtitle', 'SaaS administration and organization health')

@section('content')
    <div class="da-hero mb-4">
        <h2 class="h4">Platform overview</h2>
        <p>Track onboarded organizations and approval pipeline.</p>
    </div>

    <div class="row g-3 g-xl-4">
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card label="Organizations" :value="$stats['organizations_total']" icon="bi-buildings" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card label="Pending approval" :value="$stats['organizations_pending']" icon="bi-hourglass-split" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card label="Active" :value="$stats['organizations_active']" icon="bi-check-circle" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card label="Organization users" :value="$stats['users_total']" icon="bi-person-badge" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-ui.stat-card label="New contact messages" :value="$stats['contact_pending']" icon="bi-envelope-exclamation" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('platform.contact-messages.index') }}" class="text-decoration-none">
                <x-ui.stat-card label="Open contact threads" :value="$stats['contact_open']" icon="bi-chat-dots" />
            </a>
        </div>
    </div>
@endsection
