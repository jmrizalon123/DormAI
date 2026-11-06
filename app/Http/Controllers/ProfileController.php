<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user !== null && $user->isTenant()) {
            return redirect()->route('portal.profile.edit');
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }
}
