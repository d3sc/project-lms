@extends('layouts.app')

@section('title', 'Pengguna')

@section('header')
    <h1 class="page-title">Pengguna</h1>
    <p class="page-subtitle">Kelola akun admin dan siswa. Tidak ada pendaftaran publik.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.users.create') }}" class="btn-primary">Tambah Pengguna</a>
@endsection

@section('content')
<div class="card overflow-hidden">
    <div class="card-body p-0">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th class="hidden sm:table-cell">Email</th>
                    <th>Peran</th>
                    <th class="hidden sm:table-cell">Status</th>
                    <th class="w-40 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700">
                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-slate-900">{{ $user->name }}</p>
                                    <p class="truncate text-xs text-slate-500 sm:hidden">{{ $user->email }}</p>
                                    @if ($user->is(auth()->user()))
                                        <span class="text-xs text-indigo-600">Akun Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell text-slate-600">{{ $user->email }}</td>
                        <td>
                            <span class="{{ $user->isAdmin() ? 'badge-indigo' : 'badge-slate' }}">
                                {{ $user->isAdmin() ? 'Admin' : 'Siswa' }}
                            </span>
                        </td>
                        <td class="hidden sm:table-cell">
                            <span class="{{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-ghost btn-sm">Lihat</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-ghost btn-sm">Edit</a>
                                @unless ($user->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                          onsubmit="return confirm('Hapus pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-sm text-rose-600 hover:text-rose-700">Hapus</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-500">
                            <p class="font-medium">Belum ada pengguna</p>
                            <a href="{{ route('admin.users.create') }}" class="mt-1 inline-block text-xs text-indigo-600 hover:underline">Tambah pengguna pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
