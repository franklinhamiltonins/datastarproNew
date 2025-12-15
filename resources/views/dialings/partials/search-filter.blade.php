<div class="fields-container">
    <div class="row or_section">
        <div class="col-md-6 mb-3">
            <strong class="text-info mb-2 d-block">Marked Own Timestamp :</strong>
            <div class="row">
                <div class="col-6">
                    <input type="text" value="{{$est_timenow_minus1day}}"  placeholder="Min Time" class="form-control" name="min_time" id="min_time">
                </div>
                <div class="col-6">
                    <input type="text" value="{{$est_timenow}}" placeholder="Max Time" class="form-control " name="max_time" id="max_time">
                </div>
            </div>
        </div>
        @can('agent-create')
            <div class="col-md-6 mb-3">
                <strong class="text-info mb-2 d-block">Agent :</strong>
                <div class="row">
                    <div class="col-6">
                        {!! Form::select("agent_list", $agent_users, [], array('class' => 'form-control ml-0','id'=>'agent_list')) !!}
                    </div>
                </div>
            </div>
        @endcan
    </div>
    <br>
</div>
<div class="dropdown-divider mb-2 mt-2"></div>
<div class="row flex-wrap align-items-center justify-content-center justify-content-md-between">
    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-start">
        <button type="button" class="btn btn-outline-primary btn-sm mr-3" id="btnFiterSubmitSearch" onclick="reset_table()">Reset</button>
        <button type="button" class="btn btn-outline-primary btn-sm mr-3" id="btnFiterresetSearch" onclick="filter_table()">Filter</button>
    </div>
</div>
