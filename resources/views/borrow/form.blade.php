@extends('layouts.app')
@section('content')
@forelse ($data as $item)
    <p>{{ $item->user->name }}</p>
@empty
    <p>gada data</p>
@endforelse
@endsection