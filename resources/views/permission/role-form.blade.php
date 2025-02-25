@extends('layouts.app')

@section('title')
    edit hak akses
@endsection

@section('content')
<div class="container">
    <form action="{{ route('roles.update', ['role' => $role->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="text" name="name" value="{{ $role->name }}" class="form-control mb-3">
        <button type="button" class="btn btn-secondary mb-3" id="checkAll">Centang Semua</button>
        @foreach ($permissions as $group => $groupPermissions)
            <h3>Grup {{ ucfirst($group) }}</h3>
            <div class="d-flex flex-wrap gap-4">
            @foreach ($groupPermissions as $permission)
                <div class="form-check ms-2">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                    class="form-check-input" id="perm_{{ $permission->id }}"
                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                        {{ ucfirst($permission->alias) }}
                    </label>
                </div>
                @endforeach
            </div>
            <hr>
        @endforeach
        <button type="submit" class="btn btn-primary mt-2 mb-3">Perbarui Hak Akses</button>
    </form>
</div>

<script>
    document.getElementById('checkAll').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    });
</script>
@endsection