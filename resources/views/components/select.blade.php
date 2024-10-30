@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }} {{ $attributes->class(['border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-none block flex-auto dark:bg-gray-800 disabled:opacity-30']) }}
>{{ $slot }}</select>
