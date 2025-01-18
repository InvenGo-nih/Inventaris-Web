@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'class="form-control"']) }}>
