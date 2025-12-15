@foreach($lead->contacts as $contact)

    @if($filter_contact_by == ""  || ($filter_contact_by >= 0 && $contact->added_by_scrap_apis == $filter_contact_by))
        @if (!empty($contact->c_phone))

            @can('leads-edit')
                <a href="{{ url('/leads/edit/'.base64_encode($lead->id).'?contact_id='.$contact->id.'&contact_phone='.$contact->c_phone) }}" target="_blank" style="color: #6e8cad;">

            @endcan
                    {{ $contact->c_phone }}
            @can('leads-edit')
                </a>
            @endcan
                <br/>
        @endif
    @endif

@endforeach


