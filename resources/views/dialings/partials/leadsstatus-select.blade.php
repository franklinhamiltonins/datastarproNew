<select class="form-control leadselectstatus" name="status" onchange="updateLeadStatus('{{ $row->id }}', this.value)">
	@foreach($statusOptions as $statusOption)
	<option value="{{ $statusOption }}" {{ $selectedStatus == $statusOption ? 'selected' : '' }}>
		{{ $statusOption }}
	</option>
	@endforeach
</select>