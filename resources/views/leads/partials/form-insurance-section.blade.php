{{-- Reusable Insurance Section Partial --}}
{{-- Usage: @include('leads.partials.form-insurance-section', ['title' => 'Section Title', 'type' => 'gl']) --}}
@php
    $sectionId = $type ?? 'section';
@endphp

<div class="mb-2 form-group">
    <strong>{{ $title }}:</strong>
    <div class="form-row">
        <div class="form-group col mb-0">
            <select name="{{ $type }}" class="form-control input selectboxcarrier"
                    placeholder="Select Carrier" onchange="toggleOtherInput(this, '{{ $type }}_div')">
                <option value="">Select Carrier</option>
                @foreach($carriers as $carrier)
                    <option value="{{ $carrier->name }}">{{ $carrier->name }}</option>
                @endforeach
                <option value="other">Others</option>
            </select>
        </div>
        <div class="form-group col mb-0">
            {!! Form::select($type . '_ren_month', $months, null, ['class' => 'form-control multiple']) !!}
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col mb-0">
            <div id="{{ $type }}_div" class="mt-2 otherInput" style="display:none">
                <input placeholder="{{ $title }} Carrier" class="form-control"
                       name="{{ $type }}_other" type="text">
            </div>
        </div>
    </div>
</div>
