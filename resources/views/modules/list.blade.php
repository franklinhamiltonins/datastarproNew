@extends('layouts.app')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <nav aria-label="breadcrumb" class="page-path">
                    <ol class="breadcrumb">
                        @foreach ($breadcrumbs as $breadcrumb)
                        <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                        </li>
                        @endforeach
                    </ol>
                </nav>


                <div class="card p-2 p-lg-3 overflow-auto">
                    <div class="d-flex justify-content-between mb-4">
                        <div class="moduleSearchContainer">
                            <!-- Search Bar -->
                            @if($showSearchBox)
                            <div class="form-group mb-0" id="{{$moduleName}}_search">
                                <form action="" method="GET" id="{{$moduleName}}_searchForm"
                                    class="mb-0 moduleSearchForm d-flex">
                                    <div class="form-group mb-0">
                                        <input type="text" id="searchInput" name="search" class="form-control"
                                            placeholder="Search..." value="{{ $searchKeyword }}">
                                    </div>
                                    <button type="submit" class="btn btn-primary"
                                        id="{{$moduleName}}_submit">Search</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        <div class="d-flex modulePerPageContainer align-items-center">
                            <!-- Items per page dropdown -->
                            @if($showPerPage)
                            <div class="form-group d-flex mb-0 align-items-center" id="{{$moduleName}}_perPage">
                                <label for="perPage" class="mb-0">Items per page:</label>
                                <select id="{{$moduleName}}_selectPerPage" class="form-control moduleSelectPerPage">
                                    <option class="modulePerPageOption" value="5" {{ $perPage == 5 ? 'selected' : '' }}>
                                        5</option>
                                    <option class="modulePerPageOption" value="10"
                                        {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option class="modulePerPageOption" value="25"
                                        {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option class="modulePerPageOption" value="50"
                                        {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option class="modulePerPageOption" value="100"
                                        {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                            @endif
                            <a href="javascript:void(0)" class="btn btn-sm btn-info action-btn p-1" id="moduleRefresh">
                                <img src="{{asset('/images/module/refresh-icon.png')}}" alt="refresh" class="white-img">
                            </a>
                        </div>
                    </div>
                    <!-- Table -->
                    <!-- @foreach($hideColumns as $key => $hideColumn)
                    <input type="hidden" id="hideColumn_{{$key}}" value="{{ $hideColumn }}" />
                    @endforeach
                    <input type="hidden" id="totalLengthHideColumn" value="{{ count($hideColumns) }}" /> -->
                    <div id="{{$moduleName}}_container" class="moduleTableContainer">
                        <div id="moduleTableProcessing" class="dataTables_processing" style="display: none;"><img
                                src="{{asset('/images/module/loader.gif')}}" alt="Loader"></div>
                        <table class="moduleTable rounded" id="{{$moduleName}}_table">
                            <thead id="{{$moduleName}}_thead" class="moduleHeadContainer">
                                <tr id="{{$moduleName}}_head_tr" class="moduleHeadTrContainer">
                                    @foreach($tableHeaders as $header)
                                    <th class="modulethead @if($header['columnName'] == $sortColumn) selected @endif"
                                        data-sort-order="{{ $sortDirection }}"
                                        data-sort-column="{{ $header['columnName'] }}">{{ $header['niceName'] }}</th>
                                    @endforeach
                                    <input type="hidden" id="showActionLinkId" value="{{ $showActionLink }}" />
                                    @if($showActionLink)
                                    <th class="modulethead">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="{{$moduleName}}_tbody" class="modultTbody">
                            </tbody>


                        </table>
                        <div id="paginationLinksContainer" class="pagination-links justify-content-center d-flex mt-4">

                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</section>

@include('modules.delete-modal')

<!-- JavaScript code here -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var moduleName = '{{ $moduleName }}';
    // var showActionLink = '{{ $showActionLink }}';
    var showActionLink = document.getElementById('showActionLinkId').value;
    var currentPage = '{{ $currentPage}}';
    var perPage = '{{ $perPage}}';
    var sortDirection = '{{ $sortDirection}}';
    var sortColumn = '{{ $sortColumn}}';
    var currentPage = '{{ $currentPage}}';
    var apiEndpoint = '{{ $apiEndpoint}}';
    var baseUrl = '/' + moduleName + '/list/' + apiEndpoint; // Define base URL

    var loadingSpinner = document.getElementById('moduleTableProcessing');

    var requestData = {
        sortColumn: '',
        sortOrder: '',
        perPage: '',
        keyword: '',
        currentPage: ''
    };
    loadModuleData(); // Load initial module data

    // function for pagination links
    document.getElementById('paginationLinksContainer').addEventListener('click', function(event) {
        event.preventDefault();
        if (event.target && event.target.matches('.paginationLinks')) {
            var pageUrl = event.target.getAttribute('href');
            var pageNumber = pageUrl.match(/page=(\d+)/)[1];
            requestData.currentPage = pageNumber;
            loadModuleData();
        }
    });
    document.getElementById('moduleRefresh').addEventListener('click', function(event) {

        loadModuleData();

    });




    // function for sorting
    document.querySelector('#{{$moduleName}}_thead').addEventListener('click', function(event) {
        if (event.target.tagName === 'TH') {
            var columnName = event.target.getAttribute('data-sort-column').trim();
            var sortOrder = event.target.getAttribute('data-sort-order') || 'asc';
            if (sortOrder === 'asc') {
                event.target.setAttribute('data-sort-order', 'desc');
            } else {
                event.target.setAttribute('data-sort-order', 'asc');
            }
            document.querySelectorAll('.modulethead').forEach(function(header) {
                header.classList.remove('selected');
            });
            requestData.sortColumn = columnName;
            requestData.sortOrder = sortOrder;

            // Add 'selected' class to the clicked header
            event.target.classList.add('selected');
            loadModuleData(); // Reload data with updated sorting
        }
    });

    // function for perpage results
    document.getElementById('{{$moduleName}}_selectPerPage').addEventListener('change', function(event) {
        var selectedValue = event.target.value; // Extract the selected value
        requestData.perPage = event.target.value;
        loadModuleData(selectedValue); // Pass the selected value to the AJAX function
    });

    document.getElementById('{{$moduleName}}_searchForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var searchTerm = document.getElementById('searchInput').value;
        requestData.keyword = searchTerm;
        loadModuleData(searchTerm);
    });



    // Function to load module data
    function loadModuleData(url = '') {
        loadingSpinner.style.display = 'flex';
        var fullUrl = url ? baseUrl + url.substring(url.indexOf('?')) : baseUrl; // Construct full URL
        var xhr = new XMLHttpRequest();
        xhr.open('POST', fullUrl);
        xhr.setRequestHeader('Content-Type', 'application/json'); // Set request header

        // Get CSRF token from the meta tag
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        xhr.setRequestHeader('X-CSRF-Token', csrfToken); // Set CSRF token header
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                loadingSpinner.style.display = 'none';
                if (xhr.status === 200) {

                    let response = JSON.parse(xhr.responseText);

                    // // work on the hidden column- START
                    // let hideColumns = [];
                    // for (let i = 0; i < document.getElementById('totalLengthHideColumn').value; i++) {
                    //     let dataInHidden = document.getElementById(`hideColumn_${i}`).value;
                    //     hideColumns.push(dataInHidden);
                    // }

                    // let data = response.response.data.map(row => {
                    //     let filteredRow = {};
                    //     for (const column of Object.keys(row)) {
                    //         if (!hideColumns.includes(column)) {
                    //             filteredRow[column] = row[column];
                    //         }
                    //     }
                    //     return filteredRow;
                    // });
                    // // work on the hidden column- END

                    generateBodyData(response.response.data)
                    // generateBodyData(data)
                    updatePaginationLinks(response.response.links, requestData.currentPage);
                } else {
                    console.error('Error: ' + xhr.status);
                }
            }
        };
        xhr.send(JSON.stringify(requestData));
    }



    function generateBodyData(data) {
        var bodyHtml = '';
        if (data.length === 0) {
            var colspan = document.querySelectorAll('.moduleHeadTrContainer th').length;

            bodyHtml += '<tr class="tbodyrow noRecordsFound"><td colspan="' + colspan +
                '" class="tbodycolumns">No records found</td></tr>';
        } else {
            data.forEach(function(item) {
                bodyHtml += '<tr class="tbodyrow">';
                for (const [key, value] of Object.entries(item)) {
                    bodyHtml += '<td class="tbodycolumns">' + value + '</td>';

                }
                // bodyHtml += '<td class="tbodycolumns">' + generateActionLinks(item) + '</td>';
                if (showActionLink) {
                    bodyHtml += '<td class="tbodycolumns">' + generateActionLinks(item) + '</td>';
                }

                bodyHtml += '</tr>';
            });
        }


        document.getElementById('{{$moduleName}}_tbody').innerHTML = bodyHtml;
    }

    function generateActionLinks(item) {
        var actionsHtml = '';

        let eyeicon = "{{asset('/images/module/eye-slick.png ')}}";
        actionsHtml += '<a class="btn btn-sm btn-info action-btn p-1 white-img" title="View ' + moduleName +
            '" href="' + generateActionUrl('view', item.id) + '"><img src="' + eyeicon +
            '" alt="View" style="width: 18px;"></a>';

        let editicon = "{{asset('/images/module/edit-slick.png ')}}";
        actionsHtml += '<a class="btn btn-sm btn-success action-btn p-1 white-img" title="Edit ' + moduleName +
            '" href="' + generateActionUrl('edit', item.id) + '"><img src="' + editicon +
            '" alt="edit" style="width: 18px;"></a>';

        let deleteicon = "{{asset('/images/module/delete-slick.png ')}}";
        actionsHtml +=
            '<a href="#" title="Delete Dialing List" data-bs-toggle="modal" data-bs-target="#moduledeleteModal" onclick="setModal(this, \'' +
            item.id + '\', \'' + moduleName +
            '\')" class="btn btn-sm btn-danger deletebtn action-btn p-1 white-img">';
        actionsHtml += '<img src="' + deleteicon + '" alt="Delete" style="width: 18px;">';
        actionsHtml += '</a>';


        return actionsHtml;
    }

    function generateActionUrl(action, itemId) {

        switch (action) {
            case 'edit':
                return '/' + moduleName + '/edit/' + itemId;
            case 'view':
                return '/' + moduleName + '/view/' + itemId;
            case 'delete':
                return '/' + moduleName + '/delete/' + itemId;
            default:
                return '#';
        }
    }

    function updatePaginationLinks(links, currentPage) {
        var paginationLinksContainer = document.getElementById('paginationLinksContainer');
        paginationLinksContainer.innerHTML = '';
        if (links.length === 0) {
            paginationLinksContainer.parentNode.removeChild(paginationLinksContainer);
        } else {
            links.forEach(function(link) {
                var linkHtml = '<a class="paginationLinks' + (link.label == currentPage ? ' selected' :
                    '') + '" href="' + link.url + '">' + link.label + '</a>';
                paginationLinksContainer.insertAdjacentHTML('beforeend', linkHtml);
            });


        }
    }
});
</script>
@endsection