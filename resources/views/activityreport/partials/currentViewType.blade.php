<div class="px-3 pt-3 pb-1">
    <div class="row">
        <div class="col-lg-12 margin-tb d-flex flex-wrap justify-content-between table-top-sec">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-sm btn-outline-primary active" id="log-view-btn">
                    <input type="radio" name="viewType" value="log" autocomplete="off" checked> Log Wise View
                </label>
                <label class="btn btn-sm btn-outline-primary" id="consolidated-view-btn">
                    <input type="radio" name="viewType" value="consolidated" autocomplete="off"> Consolidated View
                </label>
            </div>
        </div>
    </div>
</div>
<script >
    // currentViewType -> 1 = log , 2 = consolidated
    var currentViewType = 1;

    document.getElementById('log-view-btn').addEventListener('click', function() {
        if (currentViewType !== 1) {
            currentViewType = 1;
            loadAreaWiseSection();
        }
    });

    document.getElementById('consolidated-view-btn').addEventListener('click', function() {
        if (currentViewType !== 2) {
            currentViewType = 2;
            loadAreaWiseSection();
        }
    });

    function displaySection() {
        document.getElementById("logWiseView").classList.toggle("displayNoneClass", currentViewType != 1);
        document.getElementById("consolidatedView").classList.toggle("displayNoneClass", currentViewType == 1);

        // if(currentViewType != 1){
        //     document.getElementById("pagination-wrapper").innerHTML = "";
        // }
    }

    async function loadAreaWiseSection(response) {
        await initDataTable(response);
        releaseDownloadButton();
        displaySection();
    }
</script>