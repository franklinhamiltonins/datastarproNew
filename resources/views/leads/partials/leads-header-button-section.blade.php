<div class="row m-0">
    <div class="col-lg-12 margin-tb mb-3">
        <div class="d-flex justify-content-between">
            <div class="left-content row d-flex align-items-center">
                <a
                    class="btn btn-info btn-sm px-2 mb-3 mb-md-0"
                    href="{{ route("leads.index", ["id" => $lead->id]) }}"
                    onclick="changeBackButtonLink(event)"
                >
                    <i class="fas fa-arrow-circle-left"></i>
                    Back
                </a>
            </div>
            <div class="actions">
                <button
                    class="btn btn-sm btn-secondary mb-0"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#logModal"
                >
                    <i class="fa fa-comment-dots"></i>
                    <span class="d-none d-lg-inline">Lead Log</span>
                </button>
                @can("lead-action")
                    <button
                        class="btn btn-sm btn-warning action-btn m-0"
                        data-bs-toggle="modal"
                        data-bs-target="#userLeadActions"
                    >
                        <i class="fas fa-mouse-pointer"></i>
                        <span class="d-none d-lg-inline">Add Lead Actions</span>
                    </button>
                @endcan

                @if ($editMode)
                    @can("lead-list")
                        <a
                            class="btn btn-sm btn-info action-btn m-0"
                            href="{{ route("leads.show", base64_encode($lead->id)) }}"
                        >
                            <i class="fa fa-eye"></i>
                            <span class="d-none d-lg-inline">View Lead</span>
                        </a>
                    @endcan
                @else
                    @can("lead-edit")
                        <a
                            class="btn btn-success btn-sm action-btn m-0"
                            href="{{ route("leads.edit", base64_encode($lead->id)) }}"
                        >
                            <i class="fa fa-edit"></i>
                            <span class="d-none d-lg-inline">Edit Business</span>
                        </a>
                    @endcan
                @endif
                @if ($editMode)
                    @can("lead-delete")
                        {!!
                            Form::open([
                                "method" => "DELETE",
                                "route" => ["leads.destroy", $lead->id],
                                "style" => "display:inline",
                                "class" => ["leadForm-" . $lead->id],
                            ])
                        !!}
                        <a
                            href="#"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteModal"
                            onclick="setModal(this, '{{ $lead->id }}')"
                            class="btn btn-sm btn-danger deletebtn action-btn m-0"
                        >
                            <i class="fa fa-trash"></i>
                            <span class="d-none d-lg-inline">Delete Lead</span>
                        </a>
                        {!! Form::close() !!}
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>
