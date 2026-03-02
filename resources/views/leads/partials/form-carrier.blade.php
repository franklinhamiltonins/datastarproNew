{{-- Reusable Carrier Select Field with "Other" Option --}}
{{-- Usage: @include('leads.partials.form-carrier', ['name' => 'field_name', 'label' => 'Field Label', 'carriers' => $carriers, 'selected' => $lead->field_name]) --}}
@php
    $id = $id ?? $name;
    $carrierId = $carrierId ?? $name . "_other";
    $selectedVal = $selected ?? "";
    $isOther = ! empty($selectedVal) && ! $carriers->contains("name", $selectedVal);
@endphp

<div class="form-group col mb-0">
    <strong>{{ $label }}:</strong>
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        class="form-control input selectboxcarrier"
        placeholder="Select Carrier"
        onchange="toggleOtherInput(this, '{{ $carrierId }}')"
    >
        <option value="" {{ $selectedVal == "" ? "selected" : "" }}>Select Carrier</option>
        @foreach ($carriers as $carrier)
            <option value="{{ $carrier->name }}" {{ $selectedVal == $carrier->name ? "selected" : "" }}>
                {{ $carrier->name }}
            </option>
        @endforeach

        <option value="other" {{ $isOther ? "selected" : "" }}>Others</option>
    </select>
    <div id="{{ $carrierId }}" class="mt-2 otherInput" {!! $isOther ? "" : 'style="display:none"' !!}>
        <input
            placeholder="Enter {{ strtolower($label) }}"
            class="form-control"
            name="{{ $name }}_other"
            type="text"
            value="{{ $isOther ? $selectedVal : "" }}"
        />
    </div>
</div>
