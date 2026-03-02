{{-- Reusable Insurance Section Partial --}}
{{-- Usage: @include('leads.partials.insurance-section', ['title' => 'Section Title', 'type' => 'gl', 'carriers' => $carriers, 'selected' => $lead->gl, ...]) --}}
@php
    $sectionId = $type ?? "section";
    $isOther = ! empty($selected) && ! $carriers->contains("name", $selected);
@endphp

<div class="mb-2 form-group">
    <strong>{{ $title }}:</strong>
    <div class="form-row">
        <div class="form-group col mb-0">
            <select
                name="{{ $type }}"
                class="form-control input selectboxcarrier"
                placeholder="Select Carrier"
                onchange="toggleOtherInput(this, '{{ $type }}_div')"
            >
                <option value="" {{ $selected == "" ? "selected" : "" }}>Select Carrier</option>
                @foreach ($carriers as $carrier)
                    <option value="{{ $carrier->name }}" {{ $selected == $carrier->name ? "selected" : "" }}>
                        {{ $carrier->name }}
                    </option>
                @endforeach

                <option value="other" {{ $isOther ? "selected" : "" }}>Others</option>
            </select>
        </div>
        <div class="form-group col mb-0">
            {!! Form::select($type . "_ren_month", $months ?? [], $renewalMonth ?? "", ["class" => "form-control multiple"]) !!}
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col mb-0">
            <div id="{{ $type }}_div" class="mt-2 otherInput" {!! $isOther ? "" : 'style="display:none"' !!}>
                <input
                    placeholder="{{ $title }} Carrier"
                    class="form-control"
                    name="{{ $type }}_other"
                    type="text"
                    value="{{ $isOther ? $selected : "" }}"
                />
            </div>
        </div>
    </div>
</div>

@if (isset($expiryPremium) || isset($policyDate))
    <div class="mb-2">
        <div class="form-row">
            @if (isset($expiryPremium))
                <div class="form-group col mb-0">
                    <strong>{{ $title }} Expiring Premium:</strong>
                    <div class="input-group">
                        <span class="input-group-text rounded-right-0">$</span>
                        {!!
                            Form::number($type . "_expiry_premium", null, [
                                "placeholder" => "Enter amount",
                                "class" => "form-control rounded-left-0",
                                "step" => "any",
                            ])
                        !!}
                    </div>
                </div>
            @endif

            @if (isset($policyDate))
                <div class="form-group col mb-0">
                    <strong>{{ $title }} Policy Renewal Date:</strong>
                    {!! Form::date($type . "_policy_renewal_date", null, ["class" => "form-control"]) !!}
                </div>
            @endif
        </div>
    </div>
@endif

@if (isset($rating) || isset($exclusions) || isset($claimsMade))
    <div class="mb-2">
        <div class="form-row">
            @if (isset($rating))
                <div class="form-group col mb-0">
                    <strong>{{ $title }} Rating:</strong>
                    {!! Form::select($type . "_rating", ["" => "Select Rating"], $rating ?? "", ["class" => "form-control"]) !!}
                </div>
            @endif

            @if (isset($exclusions))
                <div class="form-group col mb-0">
                    <strong>Exclusions:</strong>
                    {!! Form::select($type . "_exclusions", ["" => "Select Exclusions"], $exclusions ?? "", ["class" => "form-control"]) !!}
                </div>
            @endif

            @if (isset($claimsMade))
                <div class="form-group col mb-0">
                    <strong>Claims Made:</strong>
                    {!! Form::select($type . "_claims_made", ["No" => "No", "Yes" => "Yes"], $claimsMade ?? "", ["class" => "form-control"]) !!}
                </div>
            @endif
        </div>
    </div>
@endif

@if (isset($otherExclusions) || isset($pricePerUnit))
    <div class="mb-2">
        <div class="form-row">
            @if (isset($otherExclusions))
                <div class="form-group col mb-0">
                    <strong>Other Exclusions:</strong>
                    <input
                        placeholder="Other Exclusions"
                        class="form-control"
                        name="{{ $type }}_other_exclusions"
                        type="text"
                        value="{{ $otherExclusions ?? "" }}"
                    />
                </div>
            @endif

            @if (isset($pricePerUnit))
                <div class="form-group col mb-0">
                    <strong>Price Per Unit:</strong>
                    <input
                        placeholder="Price Per Unit"
                        class="form-control"
                        name="{{ $type }}_price_per_unit"
                        type="text"
                        value="{{ $pricePerUnit ?? "" }}"
                    />
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Workers Compensation specific fields --}}
@if (isset($employeeCount) || isset($employeePayroll))
    <div class="mb-2">
        <div class="form-row">
            @if (isset($employeeCount))
                <div class="form-group col mb-0">
                    <strong>Employee Count:</strong>
                    {!!
                        Form::number($type . "_employee_count", null, [
                            "placeholder" => "Enter Count",
                            "class" => "form-control",
                        ])
                    !!}
                </div>
            @endif

            @if (isset($employeePayroll))
                <div class="form-group col mb-0">
                    <strong>Employee Payroll:</strong>
                    <div class="input-group">
                        <span class="input-group-text rounded-right-0">$</span>
                        {!!
                            Form::number($type . "_employee_payroll", null, [
                                "placeholder" => "Enter Payroll",
                                "class" => "form-control rounded-left-0",
                            ])
                        !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Crime Insurance specific fields --}}
@if (isset($employeeTheft) || isset($operatingReserves) || isset($pendingLitigation))
    <div class="mb-2">
        <div class="form-row">
            @if (isset($employeeTheft))
                <div class="form-group col mb-0">
                    <strong>Employee Theft:</strong>
                    {!!
                        Form::number($type . "_employee_theft", null, [
                            "placeholder" => "Enter Theft",
                            "class" => "form-control",
                        ])
                    !!}
                </div>
            @endif

            @if (isset($operatingReserves))
                <div class="form-group col mb-0">
                    <strong>Operating Reserves:</strong>
                    {!!
                        Form::number($type . "_operating_reserves", null, [
                            "placeholder" => "Enter Reserves",
                            "class" => "form-control",
                        ])
                    !!}
                </div>
            @endif

            @if (isset($pendingLitigation))
                <div class="form-group col mb-0">
                    <strong>Pending Litigation:</strong>
                    {!!
                        Form::select($type . "_pending_litigation", ["No" => "No", "Yes" => "Yes"], $pendingLitigation ?? "", [
                            "class" => "form-control",
                        ])
                    !!}
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Flood specific fields --}}
@if (isset($elevationCertificate) || isset($lomaLetter))
    <div class="mb-2">
        <div class="form-row">
            @if (isset($elevationCertificate))
                <div class="form-group col mb-0">
                    <strong>Elevation Certificate:</strong>
                    {!!
                        Form::select($type . "_elevation_certificate", ["No" => "No", "Yes" => "Yes"], $elevationCertificate ?? "", [
                            "class" => "form-control",
                        ])
                    !!}
                </div>
            @endif

            @if (isset($lomaLetter))
                <div class="form-group col mb-0">
                    <strong>Loma Letter:</strong>
                    {!!
                        Form::select($type . "_loma_letter", ["No" => "No", "Yes" => "Yes"], $lomaLetter ?? "", [
                            "class" => "form-control",
                        ])
                    !!}
                </div>
            @endif
        </div>
    </div>
@endif
