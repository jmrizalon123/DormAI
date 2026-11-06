@php
    $daFlashes = collect();

    if ($message = session('success')) {
        $daFlashes->push(['type' => 'success', 'message' => $message]);
    }

    if ($message = session('error')) {
        $daFlashes->push(['type' => 'danger', 'message' => $message]);
    }

    if (session('status') === 'registration-submitted') {
        $daFlashes->push([
            'type' => 'info',
            'message' => 'Registration submitted. Verify your email, then sign in after a platform administrator approves your organization.',
        ]);
    }

    if (session('status') === 'verification-link-sent') {
        $daFlashes->push([
            'type' => 'success',
            'message' => 'A new verification link has been sent to your email address.',
        ]);
    }

    if ($errors->any()) {
        if ($errors->count() === 1) {
            $daFlashes->push(['type' => 'danger', 'message' => $errors->first()]);
        } else {
            $daFlashes->push([
                'type' => 'danger',
                'message' => 'Please fix the '.$errors->count().' highlighted errors in the form.',
            ]);
        }
    }
@endphp

@if ($daFlashes->isNotEmpty())
    <script type="application/json" id="daFlashMessages">@json($daFlashes->values())</script>
@endif
