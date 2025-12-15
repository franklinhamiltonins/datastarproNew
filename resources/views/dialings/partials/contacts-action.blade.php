@if (count($business_contacts) > 0)
<div class="d-flex flex-column prospect-container">
    @foreach($business_contacts as $key => $value)

    @php
    $disableButton = \Carbon\Carbon::parse($value['calling_disable_time_in_dialing'])->gt(\Carbon\Carbon::now());
    @endphp

    @if ($value['c_phone'])
    <div class="flex flex-nowrap contact-info-list">
        <a href="javascript:void(0)" class="w-50"
            onclick="sendToProspects({lead_id: '{{$row->id}}',contact_id: {{$value['id']}},lead_url: '/leads/edit/{{base64_encode($row->id)}}',backpage_url: window.location.href,page_type: '{{$page_type}}'})">
            <div
                class="text-xs lh-1 p-1 badge-primary justify-content-center align-items-center contact-info-list-badge flex-column rounded-sm">
                <div><strong>{{$value['c_title']}}</strong></div>
                <div>{{$value['c_full_name']}}</div>
            </div>
        </a>

        <div class="d-flex w-50" id="{{$value['id']}}"
            onclick="handlecallInitiation({lead_id: '{{$row->id}}',contact_id: {{$value['id']}},lead_url: '/leads/edit/{{base64_encode($row->id)}}',backpage_url: window.location.href,page_type: '{{$page_type}}',dialing_id:{{$agentlist_id}}})">

            <button {{ $disableButton ? 'disabled' : '' }}
                class="btn p-1 btn-success btn-sm w-100 flex align-items-center justify-content-center contact-info-list-btn "
                data-agent-id="{{$agent_id}}" data-lead-id="{{$row->id}}" data-dialing-id="{{$agentlist_id}}"
                data-contact-id="{{$value['id']}}">
                <div class="small d-flex align-items-center justify-content-center">
                    <i class="fa fa-phone-alt"></i>
                    <span class="ml-1">{{$value['c_phone']}}</span>
                </div>
            </button>
        </div>
    </div>
    @endif

    @endforeach
</div>


@else
<span>No Contact</span>
@endif