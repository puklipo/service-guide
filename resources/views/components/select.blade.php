@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }} {{ $attributes->class(['select']) }}
>{{ $slot }}</select>
