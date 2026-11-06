<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\OrganizationStatus;
use App\Enums\TenantStatus;
use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Http\Responses\LoginResponse;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    public function boot(): void
    {
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::query()->where('email', $request->input('email'))->first();

            if ($user === null || ! Hash::check((string) $request->input('password'), $user->password)) {
                return null;
            }

            if ($user->status !== UserStatus::Active) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Your account is not active. Please contact support.'],
                ]);
            }

            if ($user->isSuperAdmin()) {
                return $user;
            }

            $organization = $user->organization;

            if ($organization === null) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Your account is not linked to an organization.'],
                ]);
            }

            if ($organization->status !== OrganizationStatus::Active) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Your organization is pending approval or is not active.'],
                ]);
            }

            if ($user->account_type === UserAccountType::Tenant) {
                $tenant = Tenant::query()
                    ->where('user_id', $user->id)
                    ->where('organization_id', $user->organization_id)
                    ->first();

                if ($tenant === null) {
                    throw ValidationException::withMessages([
                        Fortify::username() => ['Your tenant profile could not be found.'],
                    ]);
                }

                if ($tenant->status !== TenantStatus::Active) {
                    throw ValidationException::withMessages([
                        Fortify::username() => ['Your tenant profile is not active.'],
                    ]);
                }
            }

            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input(Fortify::username());

            return Limit::perMinute(5)->by(strtolower($email).'|'.$request->ip());
        });
    }
}
