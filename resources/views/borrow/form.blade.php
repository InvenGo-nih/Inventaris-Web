@forelse ($inventaris as $item)
    <p>{{ $item->name }}</p>
@empty
    <p>gada data</p>
@endforelse