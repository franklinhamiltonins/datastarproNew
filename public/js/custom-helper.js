function restrictInput(input, maxLength) {
    let value = input.value;

    // Remove any non-numeric characters
    value = value.replace(/[^0-9]/g, '');

    // Restrict to maxLength digits
    if (value.length > maxLength) {
        value = value.slice(0, maxLength);
    }

    // Set the sanitized value back to the input field
    input.value = value;
}

function assign_value_numberformat(value) {
    if(value == ''){
        return "N/A";
    }
    else{
        return "$"+formatUSNumberJs(value);
    }
}

function htmlEntries(str) {
    if (!str) return '-';
    const temp = document.createElement('textarea');
    temp.innerHTML = str;
    return temp.value;
}

function formatUSNumberJs(number, decimals = 2) {
    // Convert to a number if it's a valid numeric string
    if (typeof number === 'string' && !isNaN(parseFloat(number))) {
        number = parseFloat(number);
    }

    // Check if it's a valid number after conversion
    if (typeof number !== 'number' || isNaN(number)) {
        console.warn('Invalid number input:', number);
        return '0.00'; // Return a default formatted value for invalid input
    }

    // Format the valid number to US style
    return number.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

function currencyValue(value) {
    // console.log(value);
    if (!value) {
        return "$0.00";
    }
    else{
        return "$"+formatUSNumberJs(value);
    }
}

async function checkFilterValue() {
    const dateType = document.getElementById("filter_date_range").value;

    if(dateType){
        let error = 0;
        if(dateType == "custom"){
            const sdate = document.getElementById("custom_from").value;
            const edate = document.getElementById("custom_to").value;

            if(sdate == ""){
                toastr.error("From is required with Custom Range");
                error++;
            }
            if(edate == ""){
                toastr.error("To is required with Custom Range");
                error++;
            }
        }
        else if(dateType == "custom_days") {
            const custom_days = document.getElementById("custom_days").value;

            if(custom_days == ""){
                toastr.error("Last number of days is required with Custom Days");
                error++;
            }
        }

        if(error > 0){
            return false;
        }
        // console.log(error);return false;
    }

    return true;
}

function dataDownLoad(downLoadUrl,dataRequest) {
    $.ajax({
        url: downLoadUrl,
        method: 'POST',
        data: dataRequest,
        beforeSend: function() {
            $('.loader-area').append(`
                <div class="ajax-loader-wrapper">
                    <div class="loader-bg position-absolute">
                        <figure class="loader-img">
                            <img src="/images/logo.png" alt="">
                        </figure>
                    </div>
                </div>
            `);
        },
        success: function(response) {
            if(response.status){
                toastr.success(response.message);
            }
            else{
                toastr.error(response.message);
            }
        },
        error: function(err) {
            console.error(err);
            alert("Failed to fetch report.");
        },
        complete: function() {
            $('.ajax-loader-wrapper').remove();
        }
    });
}

function releaseDownloadButton() {
    document.getElementById("download-btn")?.classList.remove("noactivityClass");
}

function disableDownloadButton() {
    document.getElementById("download-btn")?.classList.add("noactivityClass");
}

function formatDateMDY(dateStr) {
    if (!dateStr || dateStr.trim() === '') {
        return 'N/A';
    }

    const date = new Date(dateStr);
    if (isNaN(date)) {
        return 'N/A';
    }

    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const year = date.getFullYear();

    return `${month}/${day}/${year}`;
}

function restrictInteger() {
    const inputs = document.querySelectorAll(".integer-only");

    inputs.forEach(input => {

        // Block any non-digit key
        input.addEventListener("keydown", function (e) {
            const allowedKeys = [
                "Backspace", "Delete", "Tab", "Escape", "Enter",
                "ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown",
                "Home", "End"
            ];

            // allow control keys
            if (allowedKeys.includes(e.key)) return;

            // allow only digits 0–9
            if (!/^\d$/.test(e.key)) {
                e.preventDefault();
            }
        });

        // Clean the input on any change (covers paste, mobile, etc.)
        input.addEventListener("input", function () {
            let cleaned = this.value.replace(/\D+/g, ""); // remove non-digits

            // apply maxlength
            const max = this.getAttribute("maxlength");
            if (max) {
                cleaned = cleaned.slice(0, max);
            }

            this.value = cleaned;
        });
    });
}

document.querySelectorAll('.inputSelectionBox').forEach(function(input) {
    input.addEventListener('change', function() {
        disableDownloadButton();
    });
});

document.addEventListener("DOMContentLoaded", function () {
    restrictInteger();
});

$(document).on('change', '#customPageLength', function () {
    let length = $(this).val();
    table.page.len(length).draw();
});


$(document).on('keyup', '#customSearchBox', debounce(function (event) {

    $(this).siblings('i.fas.fa-search.position-absolute').remove();

    if (!this.value) {
        $(this).after('<i class="fas fa-search position-absolute"></i>');
    }

    // console.log(this.value);
    // console.log(table);

    // Enter OR normal search – both do same
    table.search(this.value).draw();

}, 500));


$(document).on('input', '#customSearchBox', debounce(function (event) {

    if (!this.value) {
        $(this).siblings('i.fas.fa-search.position-absolute').remove();
        $(this).after('<i class="fas fa-search position-absolute"></i>');
        table.search("").draw();
    }

}, 500));


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

function rebindSearchAndLengthEvents() {
    setTimeout(() => {
        $('#customPageLength').trigger('change');
        $('#customSearchBox').trigger('input');
    }, 100);
}

async function loadSearchArea(type,tableName) {
    let lengthMenu = `
        <div id="custom_length_menu">
            <label class="d-flex align-items-center justify-content-between mb-0">
                Show
                <select id="customPageLength"
                    class="form-control form-control-sm mx-1 px-0 bg-transparent"
                    aria-controls="${tableName}">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                entries
            </label>
        </div>
    `;

    let searchMenu = `
        <div id="${tableName}_filter"
            class="dataTables_filter search-sec mb-0 mr-2">
            <label class="d-flex align-items-center justify-content-end mb-0 position-relative">
                <input type="search" id="customSearchBox" placeholder="Search for Entries"
                    aria-controls="${tableName}" class="form-control" val="">
                <i class="fas fa-search position-absolute"></i>
            </label>
        </div>
    `;

    // INSERT BOTH
    $("#pageLengthAreaMenu").html(lengthMenu);
    $("#searchAreaMenu").html(searchMenu);
}



