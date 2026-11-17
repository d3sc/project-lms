@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('header')
    <h1 class="page-title">Tambah Pengguna</h1>
    <p class="page-subtitle">Buat akun untuk admin atau siswa. Kata sandi minimal 8 karakter.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
@endsection

@section('content')
<div class="card max-w-2xl">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert-error mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required autofocus>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required>
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-input" autocomplete="new-password" required>
                @error('password') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password" required>
                @error('password_confirmation') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="form-label">Peran</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Siswa</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="form-checkbox" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" class="form-label mb-0">Aktif</label>
            </div>
            @error('is_active') <p class="form-error">{{ $message }}</p> @enderror

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection