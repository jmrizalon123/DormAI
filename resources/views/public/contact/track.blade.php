@extends('layouts.marketplace')

@section('title', 'Track contact message — '.config('app.name'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="da-panel p-4">
                    <h1 class="h5 fw-bold mb-2">Track your message</h1>
                    <p class="text-muted small mb-4">Enter the reference from your confirmation and the same email you used when contacting us.</p>
                    <form method="GET" id="contactTrackForm" data-da-no-confirm>
                        <div class="mb-3">
                            <label class="form-label" for="trackReference">Reference</label>
                            <input type="text" id="trackReference" class="form-control" placeholder="CNT-XXXXXXXX" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="trackEmail">Email</label>
                            <input type="email" id="trackEmail" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">View conversation</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('contactTrackForm');
            if (!form) return;
            var baseUrl = @json(url('/contact/track'));
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var ref = document.getElementById('trackReference').value.trim();
                var email = document.getElementById('trackEmail').value.trim();
                var url = baseUrl + '/' + encodeURIComponent(ref) + '?email=' + encodeURIComponent(email);
                window.location.href = url;
            });
        })();
    </script>
@endpush
