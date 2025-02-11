@extends('layouts.app')
@section('content')
<div class="container">
    <form action="{{ route('roles.update', ['role' => $role->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="text" name="name" value="{{ $role->name }}" class="form-control">
        @foreach ($permissions as $group => $groupPermissions)
            <h5>{{ ucfirst($group) }}</h5>
            @foreach ($groupPermissions as $permission)
                <div class="form-check">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                        class="form-check-input" id="perm_{{ $permission->id }}"
                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                        {{ ucfirst($permission->name) }}
                    </label>
                </div>
            @endforeach
            <hr>
        @endforeach
        <button type="submit" class="btn btn-primary mt-2">Update Permissions</button>
    </form>
</div>
@endsection