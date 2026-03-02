{{-- Reusable Premium Input Field --}}
{{-- Usage: @include('leads.partials.form-premium', ['name' => 'field_name', 'label' => 'Field Label']) --}}
@php
    $id = $id ?? $name;
    $placeholder = $placeholder ?? "Enter amount";
    $colClass = $colClass ?? "col-6";
@endphp

<div class="{{ $colClass }}">
    <strong>{{ $label }}:</strong>
    <div class="input-group">
        <span class="input-group-text rounded-right-0">$</span>
        {!!
            Form::number($name, null, [
                "id" => $id,
                "placeholder" => $placeholder,
                "class" => "form-control rounded-left-0",
                "step" => "any",
                "aria-label" => "Dollar amount",
            ])
        !!}
    </div>
</div>
