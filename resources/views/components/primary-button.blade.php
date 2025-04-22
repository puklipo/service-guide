<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-sm']) }}>
    {{ $slot }}
</button>
