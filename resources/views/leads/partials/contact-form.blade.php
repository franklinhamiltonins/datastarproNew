{{-- Contact Form Partial --}}
<div class="form-row">
    <div class="form-group col mb-2">
        <label class="form-label small mb-1 font-weight-normal">
            First Name<sup class="mandatoryClass">*</sup>:
        </label>
        {!! Form::text('c_first_name', null, [
            'placeholder' => 'First Name',
            'class' => 'form-control px-2'
        ]) !!}
    </div>
    <div class="form-group col mb-2">
        <label class="form-label small mb-1 font-weight-normal">
            Last Name<sup class="mandatoryClass">*</sup>:
        </label>
        {!! Form::text('c_last_name', null, [
            'placeholder' => 'Last Name',
            'class' => 'form-control px-2'
        ]) !!}
    </div>
</div>

<div class="form-group mb-2">
    <label class="form-label small mb-1 font-weight-normal">Title:</label>
    {!! Form::select('c_title',
        !empty($contact) ? array_merge([$contact->c_title => $contact->c_title], $contactsTitle) : $contactsTitle,
        !empty($contact) ? $contact->c_title : [],
        ['class' => 'form-control multiple contactTitle px-2', 'onchange' => 'getSetOtherVal(this)']
    ) !!}
    <div id="contactTitleOther" class="mt-2 otherInput" style="display:none; text-transform: lowercase;">
        <input placeholder="Other Contact Title" class="form-control capitalize"
            name="contact-title" type="text">
    </div>
</div>

<div class="form-group mb-2">
    <label class="form-label small mb-1 font-weight-normal">
        Address 1<sup class="mandatoryClass">*</sup>:
    </label>
    {!! Form::text('c_address1', null, [
        'placeholder' => 'Address - must start with a number',
        'class' => 'form-control px-2',
        'pattern' => '^\d[0-9a-zA-Z\s\/#,._-:]*$',
        'title' => 'Chars allowed: # . - _ ,'
    ]) !!}
</div>

<div class="form-group mb-2">
    <label class="form-label small mb-1 font-weight-normal">Address 2:</label>
    {!! Form::text('c_address2', null, [
        'placeholder' => 'Address2',
        'class' => 'form-control px-2'
    ]) !!}
</div>

<div class="form-row">
    <div class="form-group col mb-2">
        <label class="form-label small mb-1 font-weight-normal">City:</label>
        {!! Form::text('c_city', null, ['placeholder' => 'City', 'class' => 'form-control px-2']) !!}
    </div>
    <div class="form-group col mb-2">
        <label class="form-label small mb-1 font-weight-normal">State:</label>
        {!! Form::select('c_state', $states, !empty($contact) ? $contact->c_state : [],
            ['class' => 'form-control multiple USstates px-2']
        ) !!}
    </div>
</div>

<div class="form-group mb-2">
    <label class="form-label small mb-1 font-weight-normal">Zip:</label>
    {!! Form::text('c_zip', null, [
        'placeholder' => 'Zip - 5 digits',
        'class' => 'form-control px-2',
        'maxlength' => '5'
    ]) !!}
</div>

<div class="form-group mb-2">
    <label class="form-label small mb-1 font-weight-normal">County:</label>
    {!! Form::select('c_county',
        !empty($contact) ? array_merge([$contact->c_county => $contact->c_county], $counties) : $counties,
        !empty($contact) ? $contact->c_county : [],
        ['class' => 'form-control multiple floridaCountiesContact px-2', 'onchange' => 'getSetOtherVal(this)']
    ) !!}
    <div id="countyOtherContact" class="mt-2 otherInput" style="display:none; text-transform: lowercase;">
        <input placeholder="Other County" class="form-control capitalize px-2"
            name="county-other-contact" type="text">
    </div>
</div>

<div class="form-row">
    <div class="form-group col mb-2">
        <label class="form-label small mb-1 font-weight-normal">Phone:</label>
        {!! Form::text('c_phone', null, [
            'placeholder' => 'Phone',
            'class' => 'form-control px-2',
            'step' => 'any',
            'maxlength' => '16'
        ]) !!}
    </div>
    <div class="form-group col mb-2">
        <label class="form-label small mb-1 font-weight-normal">Email:</label>
        {!! Form::email('c_email', null, [
            'placeholder' => 'Email Address',
            'class' => 'form-control px-2',
            'step' => 'any'
        ]) !!}
    </div>
</div>

<div class="form-group mb-2">
    <label class="form-label small mb-1 font-weight-normal">Status:</label>
    <select class="commonClass form-control leadscontactstatus contact-info-list-select px-2" name="c_status">
        @foreach($statusOptions as $keyStatus => $statusOption)
            @if(!empty($statusOption->false_status) && $statusOption->false_status == 1)
                <option value="{{ $statusOption->id }}"
                    {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
                    {{ $statusOption->name }}
                </option>
            @endif
        @endforeach

        <optgroup label="Prospecting">
            @foreach($statusOptions as $keyStatus => $statusOption)
                @if($statusOption->false_status != 1 && $statusOption->display_in_pipedrive == null)
                    <option value="{{ $statusOption->id }}"
                        {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
                        {{ $statusOption->name }}
                    </option>
                @endif
            @endforeach
        </optgroup>

        <optgroup label="Pipeline">
            @foreach($statusOptions as $keyStatus => $statusOption)
                @if($statusOption->false_status != 1 && $statusOption->display_in_pipedrive != null)
                    <option value="{{ $statusOption->id }}"
                        {{ !empty($contact) && $contact->c_status == $statusOption->id ? 'selected' : '' }}>
                        {{ $statusOption->name }}
                    </option>
                @endif
            @endforeach
        </optgroup>
    </select>
</div>

<div class="form-group mb-2">
    <label class="form-label small mb-1 font-weight-normal">Assign Agent:</label>
    <select class="commonClass form-control leadscontactassignagent contact-info-list-select px-2"
        name="c_agent_id">
        @if(count($agentList) > 1)
            <option value="0" selected>Select Agent</option>
        @endif
        @foreach($agentList as $keyagent => $agentvalue)
            <option value="{{ $keyagent }}"
                {{ !empty($contact) && $contact->c_agent_id == $keyagent ? 'selected' : '' }}>
                {{ $agentvalue }}
            </option>
        @endforeach
    </select>
</div>

@push('scripts')
<script>
    // Strip non-digits from phone input
    function phoneFormat(input) {
        return input.replace(/\D/g, '');
    }

    // Initialize phone input on page load
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInputs = document.querySelectorAll('input[name="c_phone"]');

        phoneInputs.forEach(function(input) {
            input.addEventListener('keyup', function() {
                this.value = phoneFormat(this.value);
            });
        });
    });
</script>
@endpush
