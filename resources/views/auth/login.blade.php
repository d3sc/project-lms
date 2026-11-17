<x-guest-layout>
    @if (session('status'))
        <div class="alert-success mb-4">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-error mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="form-label">Kata Sandi</label>
            <input type="password" id="password" name="password" class="form-input" required autocomplete="current-password">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="form-checkbox">
                <span class="text-sm text-slate-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:underline">Lupa kata sandi?</a>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full">Masuk</button>
    </form>
</x-guest-layout>