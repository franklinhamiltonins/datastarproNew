<div class="members_{{ $lead_id }}">
	@if(!empty($members))
	<ul>
		@foreach($members as $member)
		<li class="member-item color-{{ $loop->index % 4 }}">
			<span class="member-title">{{ $member['c_title'] }}</span>
			<span class="member-name">{{ $member['c_full_name'] }}</span>
		</li>
		@endforeach
	</ul>
	@else
	<p>No contacts available</p>
	@endif
</div>
<style>
	.member-item {
		display: flex;
		margin-bottom: 5px;
	}

	.member-title,
	.member-name {
		margin-right: 10px;
	}

	.color-0 {
		background-color: #f4f6f9;
		color: black;
	}

	.color-1 {
		background-color: #f4f6f9;
		color: black;
	}

	.color-2 {
		background-color: #f4f6f9;
		color: black;
	}

	.color-3 {
		background-color: #f4f6f9;
		color: black;
	}

	/* Add more colors if needed */
</style>