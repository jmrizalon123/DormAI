<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center py-5">
        <div class="row align-items-center g-5 w-100">
            <div class="col-lg-7">
                <p class="text-uppercase text-muted small mb-2">Philippines · SaaS</p>
                <h1 class="display-5 fw-semibold">{{ config('app.name') }}</h1>
                <p class="lead text-muted mb-4">
                    AI-driven dormitory and boarding house management — properties, tenants, billing, and analytics in one platform.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Sign in</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary">Register organization</a>
                    @endauth
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 text-uppercase text-muted">Phase 1 foundation</h2>
                        <ul class="mb-0 ps-3">
                            <li>Multi-tenant organizations</li>
                            <li>Roles & permissions</li>
                            <li>Secure authentication</li>
                            <li>Platform approval workflow</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>
