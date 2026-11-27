@extends('layouts.app')

@section('title')
    edit hak akses
@endsection

@section('content')
    <form action="{{ $role->exists ? route('roles.update', ['role' => $role->id]) : route('roles.store') }}" method="POST">
        @csrf
        @if ($role->exists)
            @method('PUT')
        @endif
        <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control mb-3" required>
        <button type="button" class="btn btn-secondary mb-3" id="checkAll">Pilih Semua</button>
        <button type="button" class="btn btn-secondary mb-3" id="closeAll">Hapus Semua</button>
        @foreach ($permissions as $group => $groupPermissions)
            <h3>Grup {{ ucfirst($group) }}</h3>
            <div class="d-flex flex-wrap gap-4">
                @foreach ($groupPermissions as $permission)
                    <div class="form-check ms-2">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                            id="perm_{{ $permission->id }}"
                            {{ $role->exists && $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                            {{ ucfirst($permission->alias) }}
                        </label>
                    </div>
                @endforeach
            </div>
            <hr>
        @endforeach
        <button type="submit" class="btn btn-primary mt-2">{{ $role->exists ? 'Perbarui Hak Akses' : 'Simpan Jabatan' }}</button>
    </form>

    <script>
        document.getElementById('checkAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });
        document.getElementById('closeAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    </script>
@endsection
