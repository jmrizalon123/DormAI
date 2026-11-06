<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user?->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user?->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach (App\Enums\UserStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $user?->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $user?->roles->first()?->name) === $role)>{{ str($role)->headline() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ $user ? 'New password (optional)' : 'Password' }}</label>
                    <input type="password" name="password" class="form-control" @unless($user) required @endunless>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" class="form-control" @unless($user) required @endunless>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('organization.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
