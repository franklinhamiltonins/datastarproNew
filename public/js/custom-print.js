document.addEventListener('DOMContentLoaded', function () {

    const printBtn = document.getElementById('printBtn');
    if (!printBtn) return;

    printBtn.addEventListener('click', function () {
        const printSection = document.getElementById('printSection');
        if (!printSection) return;

        const printStyles = `
            .modal-dialog {
                width: 90%;
                max-width: 793px;
                height: auto;
                margin: auto;
            }

            .modal-content {
                height: auto;
                border-radius: 10px;
                aspect-ratio: 1 / 1.414; 
                padding: 20px; /* Add some padding for A4 look */
                box-sizing: border-box;
                background-color: white; 
            }

            .modal-body {
                overflow-y: auto;
                height: auto;
                max-height: calc(100% - 120px);
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.5;
                margin: 0;
                padding: 10px;
            }
            .card-body .form-group strong, .card-body .form-group .small {
                font-size: 10px;
            }
            .a4-style {
                width: 793px; 
                max-width: 100%;
                height: auto;
                aspect-ratio: 1 / 1.414; 
                background-color: white; 
                padding: 20px;
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.5;
                box-sizing: border-box;
            }

            .a4-style-header,
            .a4-style-footer {
                text-align: center;
                font-weight: bold;
                font-size: 16px;
                margin-bottom: 10px;
            }

            .a4-style-body {
                overflow-y: auto;
                height: auto;
                margin: 0;
                padding: 15px 10px 10px;
            }
            .print_gap{padding-bottom:20px}
            .form-row {
                display: flex;
                flex-wrap: wrap; /* Allow wrapping for smaller screens */
                margin-left: -10px; /* Compensate for inner column padding */
                margin-right: -10px;
            }

            .form-group {
                padding-left: 10px;
                padding-right: 10px;
                margin-bottom: 0.5rem; /* Add consistent spacing between rows */
            }

            /* Responsive column layout */
            .form-group.col-12 {
                flex: 0 0 100%; /* Take full width */
                max-width: 100%;
            }

            .form-group.col-md-6 {
                flex: 0 0 50%; /* Half-width for medium screens */
                max-width: 50%;
            }
            .form-group.col-md-4 {
                flex: 0 0 33.333%; /* Half-width for medium screens */
                max-width: 33.333%;
            }
            .form-group.col-md-3 {
                flex: 0 0 25%; /* Half-width for medium screens */
                max-width: 25%;
            }

            .form-group.col-lg-4 {
                flex: 0 0 33.333%; /* 1/3 width for large screens */
                max-width: 33.333%;
            }

            .form-group.col-lg-3 {
                flex: 0 0 25%; /* 1/4 width for large screens */
                max-width: 25%;
            }

            .form-group.col-lg-5 {
                flex: 0 0 41.666%; /* Slightly larger for columns needing more space */
                max-width: 41.666%;
            }

            /* Improve padding and borders for the section container */
            .p-2 {
                padding: 10px;
            }

            .mt-4 {
                margin-top: 1.5rem;
            }

            .pt-3 {
                padding-top: 1rem;
            }

            .pb-0 {
                padding-bottom: 0;
            }
            .py-1 {
                padding-bottom : 0.25rem!important;
                padding-top : 0.25rem!important;
            }
            .px-0 {
                padding-left : 0 !important;
                padding-right : 0 !important;
            }

            strong {
                font-weight: 600;
            }

            .form-group span {
                display: inline-block;
                word-wrap: break-word;
            }

            .mx-2 {
                margin-left: 10px;
                margin-right: 10px;
            }

            .rounded {
                border-radius: 5px;
            }

            .border {
                border: 1px solid #ddd;
            }

            .position-relative {
                position: relative;
            }

            .font-weight-bold {
                font-weight: bold;
            }

            .bg-white {
                background-color: #fff;
            }

            .d-inline-block {
                display: inline-block;
            }

            .z-5 {
                z-index: 5;
            }

            .px-3 {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .p-0 {
                padding: 0;
            }

            .mb-1 {
                margin-bottom: 0.25rem;
            }

            .section-head {
                font-weight: bold;
                background-color: #fff;
                padding: 0 5px;
                position: absolute;
                top: -10px;
                left: 10px; 
                z-index: 5;
            }

            .small {
                font-size: 0.875rem;
                color: #6c757d;
            }
            .longtextarea {
                display: inline !important; 
                word-wrap: break-word !important;
                white-space: normal !important;
            }

        `;

        const printWindow = window.open('about:blank', '_blank');
        
        printWindow.document.open('text/html', 'replace');

        const clonedSection = printSection.cloneNode(true);
        clonedSection.querySelectorAll('script').forEach(el => el.remove());

        /* Remove toastr containers */
        clonedSection.querySelectorAll('.toast, .toast-container').forEach(el => el.remove());

        /* Remove any inline JS handlers */
        clonedSection.querySelectorAll('*').forEach(el => {
            [...el.attributes].forEach(attr => {
                if (attr.name.startsWith('on')) {
                    el.removeAttribute(attr.name);
                }
            });
        });

        printWindow.document.write(`<!DOCTYPE html>
        <html>
        <head>
            <title>Print Lead Preview</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                ${printStyles.replace(/\/\/.*$/gm, '')}
            </style>
        </head>
        <body class="a4-style">
            ${clonedSection.innerHTML}
        </body>
        </html>
        `);

        printWindow.document.close();

        printWindow.addEventListener('load', () => {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });
    });

});