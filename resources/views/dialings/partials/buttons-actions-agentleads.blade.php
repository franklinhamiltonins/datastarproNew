@if($is_admin)
<a class="btn btn-sm btn-info action-btn assign_lead_to_agent" data-toggle="modal" data-target="#assign_lead_to_agentmodal" data-current="{{ $row->id }}" data-assigned_agents="{{ $row->agent_ids }}" title="Assign Lead to Agent" href="{{ route('dialings.show', base64_encode($row->id)) }}"><i class="fa fa-tags"></i></a>
<!-- <a class="btn btn-sm btn-info action-btn assign_lead_to_agent" href="javascript:void()" title="Assign Lead to Agent" href="{{ route('dialings.show', $row->id) }}"><i class="fa fa-tags"></i></a> -->

@endif