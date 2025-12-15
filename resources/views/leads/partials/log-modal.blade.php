{{-- log Modal --}}
<div class="modal fade" id="logModal" data-source="" style="display: none;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-0">
            <div class="modal-header p-2 p-lg-3 align-items-center">
                <h5 class="modal-title">Log for: {{$lead->name}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-2 p-lg-3">

                <div class="row">
                    <div class="col-12">
                        <div id="logsView" class="logsview">
                            <div class="">
                                @if(!count($logs) < 1 ) @foreach ($logs as $log) <p>

                                    User <span class="text-info">{{$log->users?$log->users->name : ''}}</span> performed
                                    action "<span class="text-danger">{{$log->action}}</span>" on date:
                                    {{$log->created_at}}
                                    </p>
                                    @endforeach

                                    @else
                                    <p>No actions.</p>
                                    @endif

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer justify-content-start p-2 p-lg-3">
                <button type="button" class="btn btn-sm px-3 py-2 m-0 btn-outline-secondary" data-dismiss="modal">Close</button>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>