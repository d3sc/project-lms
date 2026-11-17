@extends('layouts.app')

@section('title', 'Profil')

@section('header')
    <h1 class="page-title">Profil</h1>
    <p class="page-subtitle">Perbarui nama, email, dan kata sandi akun Anda.</p>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card max-w-2xl">
        <div class="card-header">
            <p class="text-sm font-medium text-slate-900">Informasi Akun</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('patch')

                <div>
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="btn-primary">Simpan</button>
                    @if (session('status') === 'profile-updated')
                        <span class="text-sm text-emerald-600">Tersimpan.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card max-w-2xl">
        <div class="card-header">
            <p class="text-sm font-medium text-slate-900">Ubah Kata Sandi</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('put')

                <div>
                    <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" autocomplete="current-password">
                    @foreach ($errors->updatePassword->get('current_password') as $message)
                        <p class="form-error">{{ $message }}</p>
                    @endforeach
                </div>

                <div>
                    <label for="password" class="form-label">Kata Sandi Baru</label>
                    <input type="password" id="password" name="password" class="form-input" autocomplete="new-password">
                    @foreach ($errors->updatePassword->get('password') as $message)
                        <p class="form-error">{{ $message }}</p>
                    @endforeach
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
                    @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                        <p class="form-error">{{ $message }}</p>
                    @endforeach
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="btn-primary">Perbarui Kata Sandi</button>
                    @if (session('status') === 'password-updated')
                        <span class="text-sm text-emerald-600">Kata sandi diperbarui.</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection