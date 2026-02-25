<!-- Reusable Yes/No Select Field -->
<!-- Usage: include 'leads partials form-yes-no', 
['name' => 'field_name', 'label' => 'Field Label', 'selected' => field name ?? '']) -->
@php
    $id = $id ?? $name;
    $class = $class ?? 'form-control';
    $required = $required ?? false;
    $selected = $selected ?? 'No';
@endphp

<div class="form-group col mb-0">
    <strong>{{ $label }}{{ $required ? ' *' : '' }}:</strong>
    {!! Form::select($name, [
        'No' => 'No',
        'Yes' => 'Yes',
    ], $selected, array(
        'class' => $class,
        'id' => $id,
        'placeholder' => $placeholder ?? ''
    )) !!}
</div>
