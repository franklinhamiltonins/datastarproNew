<div class="form-group col-md-4">
    <strong>Agent</strong>
    <select class="form-control inputSelectionBox" id="filter_agent">
        @if($isAdminUser)
            <option value="">All</option>
        @endif
        @foreach($agentUsers as $agent)
            <option value="{{ $agent['id'] }}">{{ $agent['displayname'] }}</option>
        @endforeach
    </select>
</div>

<div class="form-group col-md-4">
    <strong>Date Range</strong>
    <select class="form-control inputSelectionBox" id="filter_date_range">
        <option value="yesterday">Yesterday</option>
        <option selected value="last_7_days">Last 7 Days</option>
        <option value="last_30_days">Last 30 Days</option>
        <option value="custom">Custom Range</option>
        <option value="custom_days">Custom Days</option>
    </select>
</div>

<div class="form-group col-md-4 custom-date-range d-none">
    <strong>From</strong>
    <input type="date" class="form-control inputSelectionBox" id="custom_from">
</div>

<div class="form-group col-md-4 custom-date-range d-none">
    <strong>To</strong>
    <input type="date" class="form-control inputSelectionBox" id="custom_to">
</div>

<div class="form-group col-md-4 custom-days-input d-none">
    <strong>Last number of days</strong>
    <input type="number" min="1"  max="365" class="form-control inputSelectionBox" id="custom_days" placeholder="Enter number of days">
</div>