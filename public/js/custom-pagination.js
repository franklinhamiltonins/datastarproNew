// public/js/pagination.js

function renderPagination(currentPage, lastPage, containerId, fetchFunctionName,pagelength=0) {

    if(pagelength == 1){
        $(`#${containerId}`).html('');
        return;
    }
    let paginationHTML = `<nav><ul class="pagination justify-content-end">`;

    if (currentPage > 1) {
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
    }

    for (let i = 1; i <= lastPage; i++) {
        paginationHTML += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>`;
    }

    if (currentPage < lastPage) {
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
    }

    paginationHTML += `</ul></nav>`;



    $(`#${containerId}`).html(paginationHTML);

    // Bind pagination click
    $(`#${containerId} .page-link`).on('click', function (e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (page) {
            fetchFunctionName(page);
        }
    });
}
