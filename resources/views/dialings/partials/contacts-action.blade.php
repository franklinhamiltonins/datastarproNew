@if (count($businessContacts) > 0)
    <div class="d-flex flex-column prospect-container">
        @foreach ($businessContacts as $value)
            @php
                $disableButton = \Carbon\Carbon::parse($value["calling_disable_time_in_dialing"])->gt(\Carbon\Carbon::now());
            @endphp

            @if ($value["c_phone"])
                <div class="flex flex-nowrap contact-info-list">
                    <a
                        href="javascript:void(0)"
                        class="w-50"
                        onclick="
                            sendToProspects({
                                lead_id: '{{ $row->id }}',
                                contact_id: {{ $value["id"] }},
                                lead_url: '/leads/edit/{{ base64_encode($row->id) }}',
                                backpage_url: window.location.href,
                                page_type: '{{ $pageType }}',
                            })
                        "
                    >
                        <div
                            class="text-xs lh-1 p-1 badge-primary justify-content-center align-items-center contact-info-list-badge flex-column rounded-sm"
                        >
                            <div><strong>{{ $value["c_title"] }}</strong></div>
                            <div>{{ $value["c_full_name"] }}</div>
                        </div>
                    </a>

                    <div
                        class="d-flex w-50"
                        id="{{ $value["id"] }}"
                        onclick="
                            handlecallInitiation({
                                lead_id: '{{ $row->id }}',
                                contact_id: {{ $value["id"] }},
                                lead_url: '/leads/edit/{{ base64_encode($row->id) }}',
                                backpage_url: window.location.href,
                                page_type: '{{ $pageType }}',
                                dialing_id: {{ $dialingId }},
                            })
                        "
                    >
                        <button
                            {{ $disableButton ? "disabled" : "" }}
                            class="btn p-1 btn-success btn-sm w-100 flex align-items-center justify-content-center contact-info-list-btn"
                            data-agent-id="{{ $agentId }}"
                            data-lead-id="{{ $row->id }}"
                            data-dialing-id="{{ $dialingId }}"
                            data-contact-id="{{ $value["id"] }}"
                        >
                            <div class="small d-flex align-items-center justify-content-center">
                                <i class="fa fa-phone-alt"></i>
                                <span class="ml-1">{{ $value["c_phone"] }}</span>
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
