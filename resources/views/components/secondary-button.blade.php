<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline btn-secondary btn-sm']) }}>
    {{ $slot }}
</button>
