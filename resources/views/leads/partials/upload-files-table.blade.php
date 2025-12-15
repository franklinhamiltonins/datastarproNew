<div class="card card-secondary mt-0">
    <div class="card-header">
        <h3 class="card-title ">Uploaded Files</h3>
    </div>
    <div class="card-body filesUploadedTable p-2 p-lg-3">
        <div class="row">
            <div
                class="col-lg-12 margin-tb d-flex flex-wrap justify-content-between table-top-sec mb-3">
                <div class="custom_search_page d-flex align-items-center justify-content-between">
                    <div id="custom_length_menu">
                        <label class="d-flex align-items-center justify-content-between mb-0">Show
                            <select id="customPageLength"
                                class="form-control form-control-sm mx-1 px-0 bg-transparent"
                                aria-controls="leads_datatable">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            entries
                        </label>
                    </div>
                </div>
                <div id="leads_datatable_filter" class="dataTables_filter search-sec mb-0">
                    <label
                        class="d-flex align-items-center justify-content-end mb-0 position-relative"><input
                            type="search" id="customSearchBox" placeholder="Search for Entries"
                            aria-controls="leads_datatable" class="form-control" val="">
                        <i class="fas fa-search position-absolute"></i>
                    </label>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table class="table table-bordered table-hover table-sm table-striped" id="files_datatable">
                <thead class="text-nowrap">
                    <tr>
                        <th scope="col">No</th>
                        <th></th>
                        <th>Name <span class="arrow"></span></th>
                        <th>Description <span class="arrow"></span></th>
                        <th>Date <span class="arrow"></span></th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" defer></script>
<script >
    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    jQuery(document).ready(function() {
        var table = jQuery('#files_datatable').DataTable({
            // dom: 'lBfrtip',
            processing: true,
            oLanguage: {
                sProcessing: `{!! trim(preg_replace('/\s+/', ' ', view('partials.datatable_loader')->render())) !!}`
            },
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ url('leads/leads-files') }}",
                type: 'POST',
                data: function(d) {
                    d.leadId = '{{$lead->id}}'; //send the lead id
                }
            },
            columns: [
                //set table columns
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    "targets": [0],
                    "searchable": false,
                    "orderable": false,
                },
                {
                    data: 'id',
                    name: 'id',
                    'visible': false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },

                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],

            order: [
                [4, 'desc']
            ],
            dom: 'rt<"bottom"ip><"clear">',
        });

        $('#customPageLength').on('change', function() {
            var length = $(this).val();
            table.page.len(length).draw();
        });

        function debounce(func, wait) {
            var timeout;
            return function() {
                var context = this,
                    args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    timeout = null;
                    func.apply(context, args);
                }, wait);
            };
        }

        $('#customSearchBox').on('keyup', debounce(function(event) {
            $(event.target).siblings('i.fas.fa-search.position-absolute').remove();
            if (!event.target.value) {
                $(event.target).after('<i class="fas fa-search position-absolute"></i>');
            }
            if (event.key === "Enter") {
                table.search(this.value).draw();
            } else {
                table.search(this.value).draw();
            }
        }, 500)); // 500ms debounce interval

        $('#customSearchBox').on('input', debounce(function(event) {
            if (!event.target.value) {
                console.log('contact search cross clicked');
                $(event.target).blur(); // to remove cursiour from search field.

                $(event.target).siblings('i.fas.fa-search.position-absolute')
                    .remove(); // remove search icon and the append
                $(event.target).after('<i class="fas fa-search position-absolute"></i>');
                table.search(event.target.value).draw(); // drow the table
            }
        }, 500));
    });


</script>
@endpush