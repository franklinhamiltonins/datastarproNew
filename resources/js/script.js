document.addEventListener( 'DOMContentLoaded', () => {
        const topScrollbarContainer = document.querySelector( '.top-scrollbar-container' );
        const topScrollbarSpacer = document.querySelector( '.top-scrollbar-spacer' );
        const tableContentContainer = document.querySelector( '.table-container' );
        const dataTable = document.querySelector( 'dataTable' );
        const tableWidth = dataTable.scrollWidth;
        topScrollbarSpacer.style.width = tableWidth + 'px';
        console.log(topScrollbarContainer);
        alert('hello');
    } );