@extends('layouts.app')
@section('pagetitle', 'Agent Activity Form')
@push('breadcrumbs')
<li class="breadcrumb-item active">Agent Activity</li>
<li class="breadcrumb-item active">Tracker</li>
@endpush
@section('content')
<section class="content">
    <div class="container-fluid dashboard-sec">
        <div class="mt-2 card card-secondary">
            <div class="row">
                <div class="col-12">
                    <div class="card-body lead-update p-0">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Please fill the form to report your numbers daily</h6>
                        </div>
                        <form id="agent_activity_reports">
                            @csrf
                            <div class="form-row p-3">
                                <!-- <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Email:</strong>
                                    <input placeholder="Valid Email" class="form-control capitalize" name="valid-email" type="email">
                                    <small class="text-xs muted">This form is collecting email</small>
                                </div> -->
                                <input type="hidden" name="created_by" value="{{$agentId}}">
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Agent<sup class="mandatoryClass">*</sup>:</strong>
                                    <select class="form-control" name="user_id" id="user_id">
                                        @if($isAdminUser)
                                            <option value="">Select Agent</option>
                                        @endif
                                        @foreach($agentUsers as $agent)
                                            <option value="{{$agent['id']}}">{{$agent["displayname"]}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Date<sup class="mandatoryClass">*</sup>:</strong>
                                    <input id="dateInput" class="form-control" name="date" type="date" >
                                    <!-- <p id="formattedDate" style="margin-top: 10px;"></p> -->
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Appointments:</strong>
                                    <input placeholder="Appointments" class="form-control integer-only" name="appointments" type="text" maxlength="3">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Policies:</strong>
                                    <input placeholder="Policies" class="form-control integer-only" name="policies" type="text" maxlength="3">
                                </div>
                                <div class="form-group col-12 col-md-6 col-lg-4 mb-2">
                                    <strong>Expiring Policy Premium:</strong>
                                    <div class="input-group">
                                        <span class="input-group-text rounded-right-0">$</span>
                                        <input placeholder="Expiring Policy Premium " class="form-control rounded-left-0" step="any" aria-label="Dollar amount (with dot and two decimal places" id="premium" maxlength="10" oninput="restrictInput(this, 10)" name="expiry_policies_premium" type="number">
                                    </div>
                                </div>
                                <!-- <div class="form-group col-12 col-md-6 col-lg-4 mb-2 position-relative"> -->
                                <div class="form-group col-12 mb-4">
                                    <strong>Community Name:</strong>
                                    <input type="hidden" name="community_id" id="community_id" value="0">
                                    <!-- <textarea name="community_name" placeholder="Community Name" class="form-control" rows="4"></textarea> -->
                                    <div class="position-relative">
                                        <input
                                            type="text"
                                            class="form-control"
                                            placeholder="Community Name"
                                            name="community_name"
                                            id="community_name"
                                            autocomplete="off"
                                        />
                                        <div
                                            id="suggestions_comm"
                                            class="list-group bg-white position-absolute left-0 top-100 right-0 shadow-sm"
                                            style="display: none;max-height: 292px;"
                                        ></div>
                                    </div>
                                </div>

                                <div id="aor-container" class="col-12 mb-2">
                                    <div class="aor-group-outer border pb-2 pt-4 px-3 rounded mt-2 mb-4 position-relative">
                                        <!-- AOR Group Template -->
                                         <span class="position-absolute aor-group-head bg-secondary px-3 py-1 rounded-lg text-sm">AOR Group</span>
                                        <div class="aor-group row" data-index="0">
                                            <div class="form-group col-12 col-md-6 col-lg-3 mb-2">
                                                <strong>AOR:</strong>
                                                <input placeholder="AOR" class="form-control integer-only" name="aor[0][aor]" type="text" maxlength="3">
                                            </div>
                                            <div class="form-group col-12 col-md-6 col-lg-3 mb-2">
                                                <strong>AOR (Community Name):</strong>
                                                <input placeholder="AOR (Community Name)" class="form-control " name="aor[0][aor_community_name]" type="text">
                                            </div>
                                            <div class="form-group col-12 col-md-6 col-lg-3 mb-2">
                                                <strong>AOR MONTH Effective Date:</strong>
                                                <input placeholder="AOR MONTH Effective Date" class="form-control " name="aor[0][aor_effective_date]" type="date">
                                            </div>
                                            <div class="form-group col-12 col-md-6 col-lg-3 mb-2">
                                                <strong>Expiring AOR Premium:</strong>
                                                <div class="input-group">
                                                    <span class="input-group-text rounded-right-0">$</span>
                                                    <input placeholder="Expiring AOR Premium " class="form-control rounded-left-0" step="any" aria-label="Dollar amount (with dot and two decimal places" id="premium" maxlength="10" oninput="restrictInput(this, 10)" name="aor[0][expiring_aor_premium]" type="number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 mb-3">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="addAORGroup()">Add Another AOR</button>
                                </div>

                                <div class="form-group col-12 mb-2">
                                    <strong>AOR Break Down:</strong>
                                    <textarea name="aor_breakdown" id="" placeholder="AOR Break Down" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between px-3 pb-3">
                                <button type="button" class="btn btn-info btn-sm" onclick="document.getElementById('fileInput').click()">
                                    <i class="fas fa-file-upload"></i> Add File
                                </button>

                                <!-- Hidden file input -->
                                <input type="file" id="fileInput" name="signed_aor_doc[]" multiple style="display: none;" onchange="handleFiles(this.files)">
                            </div>  

                            <!-- File preview area -->
                            <div id="fileList" class="px-3"></div>

                            <div class="d-flex justify-content-between px-3 pb-3">
                                <button type="submit" class="btn btn-info btn-sm" >
                                    <i class="fas fa-file-alt"></i> Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
@endpush
@push('scripts')
<script src="{{ asset('js/custom-helper.js') }}"></script>
<script>
    // Get today's date in YYYY-MM-DD format
    const input = document.getElementById("dateInput");
    const today = new Date().toISOString().split("T")[0];
    input.setAttribute("max", today);
    input.value = today;

    let debounceComTimeout;

    let selectedFiles = [];

    function handleFiles(files) {
        selectedFiles = Array.from(files); // Store files in array
        renderFileList();
    }

    function renderFileList() {
        const fileList = document.getElementById("fileList");
        fileList.innerHTML = "";

        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement("div");
            fileItem.className = "d-flex align-items-center justify-content-between border rounded p-2 mb-2 bg-light";

            fileItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    <span>${file.name}</span>
                </div>
                <button class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;

            fileList.appendChild(fileItem);
        });
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        renderFileList();
    }

    $('#agent_activity_reports').on('submit', function(e) {
        e.preventDefault();

        if(document.getElementById("user_id").value == ""){
            toastr.error("Agent is required");
            return;
        }
        if(document.getElementById("dateInput").value == ""){
            toastr.error("Date is required");
            return;
        }

        // Prepare form data
        let formData = new FormData(this);

        $.ajax({
            url: '{{ route("agentreport.saveAgentActivity") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('body').append(`{!! trim(preg_replace('/\s+/', ' ', view('partials.formSubmission_loader')->render())) !!}`);
            },
            success: function(response) {
                // alert('Agent activity report saved successfully.');
                if(response.status){
                    toastr.success(response.message);
                    $('#agent_activity_reports')[0].reset(); // reset form
                    if(response.redirection){
                        setTimeout(function() {
                            window.location.href = '{{ route("agentreport.activityReport") }}';
                        }, 1500); // 1500 milliseconds = 1.5 seconds
                    }
                    else{
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    }
                }
                else{
                    toastr.error(response.message);
                }
                
            },
            error: function(xhr) {
                console.error(xhr);
                alert('Something went wrong.');
            },
            complete: function() {
                $('.ajax-loader-wrapper').remove();
            }
        });
    });

    let aorIndex = 1;
    const maxAOR = 5;

    function addAORGroup() {
        const container = document.getElementById('aor-container');
        const currentCount = container.querySelectorAll('.aor-group').length;

        if (currentCount >= maxAOR) {
            toastr.error("You can only add up to 5 AOR Group.");
            return;
        }

        const template = container.querySelector('.aor-group-outer');
        const clone = template.cloneNode(true);
        clone.dataset.index = aorIndex;

        const inputs = clone.querySelectorAll('input');
        inputs.forEach(input => {
            const baseName = input.name.replace(/\[\d+\]/g, '');
            input.name = `aor[${aorIndex}]${baseName.slice(3)}`;
            input.value = '';
        });

        // Add remove button if not present
        removeExistingRemoveButton(clone);
        const removeBtn = document.createElement("div");
        removeBtn.className = "position-absolute remove-btn";
        removeBtn.innerHTML = `<button type="button" class="btn btn-sm btn-danger" onclick="removeAORGroup(this)"><i class="fas fa-trash"></i></button>`;
        clone.appendChild(removeBtn);

        container.appendChild(clone);

        restrictInteger();
        aorIndex++;
    }

    function removeAORGroup(btn) {
        const container = document.getElementById('aor-container');
        const groups = container.querySelectorAll('.aor-group-outer');
        if (groups.length > 1) {
            const groupOuter = btn.closest('.aor-group-outer');
            if (groupOuter) {
                container.removeChild(groupOuter);
            }
        }
        //  else {
        //     toastr.warning("At least one AOR group is required.");
        // }
    }

    function removeExistingRemoveButton(group) {
        const existing = group.querySelector('button.btn-danger');
        if (existing) {
            existing.closest('div.col-12').remove();
        }
    }

    $('#community_name').on('input', function () {
        clearTimeout(debounceComTimeout);

        const keyword = $(this).val().trim();

        $('#community_id').val(0);

        debounceComTimeout = setTimeout(() => {
            if (keyword.length > 2) {
                $.ajax({
                    url: '/community/search',
                    method: 'POST',
                    data: { keyword },
                    headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        if (Array.isArray(data) && data.length > 0) {
                            const suggestions = data.map(lead => `
                                <a href="javascript:void(0)" 
                                    class="list-group-item list-group-item-action p-2 small community-suggestion"
                                    data-id="${lead.id}" 
                                    data-name="${lead.name}">
                                    <strong class="text-primary">${lead.name}</strong>
                                    <br>
                                    <small class="text-muted">
                                        ${`${lead.address1 ?? ""}, ${lead.city ?? ""}, ${lead.state ?? ""} ${lead.zip ?? ""}`.trim()}
                                    </small>
                                </a>
                            `).join('');

                            $('#suggestions_comm').html(suggestions).show();
                        } else {
                            $('#suggestions_comm').hide();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("AJAX Error:", textStatus, errorThrown);
                    }
                });
            } else {
                $('#suggestions_comm').hide();
            }
        }, 300);
    });

    $(document).on('click', '.community-suggestion', function () {
        const name = $(this).data('name');
        const id = $(this).data('id');

        $('#community_name').val(name);
        $('#community_id').val(id);
        $('#suggestions_comm').hide();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#community_name, #suggestions_comm').length) {
            $('#suggestions_comm').hide();
        }
    });

</script>
@endpush