<div id="lead-saved-filter-sidebar" class="lead-saved-filter-sidebar">
    <div class="header d-flex align-items-center justify-content-between p-2">
        <span><label>Saved Filters</label></span>
        <a href="javascript:void(0)" class="closebtn" onclick="closeSavedFiltersNav()"><i class="fas fa-times"></i></a>
    </div>
    <div class="lead-saved-filters d-flex row m-0 mt-2 justify-content-center">
        @if(!empty($lead_filters))
        @foreach($lead_filters as $filter)
        <div class="filter d-flex align-items-center py-1 filter_id_{{$filter->id}} w-100">
            <div class="title d-flex">
                <label>{{$filter->name}}</label>
            </div>
            <button class="btn btn-success btn-sm mr-2 apply" type="button"
                onclick="applySavedFilterConfirm('{{$filter->id}}', '{{$filter->name}}', '{{$filter->conditions}}')"><i
                    class="fas fa-check"></i></button>
            <button class="btn btn-danger btn-sm closebtn mr-1" type="button"
                onclick="deleteSavedFilterConfirm('{{$filter->id}}', '{{$filter->name}}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endforeach
        @endif
        <div class="no-filter-found m-2 p-2 {{is_null($lead_filters) ? '': 'd-none'}}">No Filter found</div>
    </div>
</div>