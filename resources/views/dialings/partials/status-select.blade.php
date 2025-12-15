@if ($is_admin)
<select class="form-control selectstatus @if($row->referral_marker) {{'disabledClass'}} @endif" name="status" onchange="updateStatus('{{ $row->id }}', this.value)">
	@foreach($statusOptions as $statusOption)
	<option value="{{ $statusOption }}" {{ $selectedStatus == $statusOption ? 'selected' : '' }}>
		{{ $statusOption }}
	</option>
	@endforeach
</select>
@else
{{ $row->status }}
@endif