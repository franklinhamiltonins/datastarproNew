document.addEventListener( 'DOMContentLoaded', () => {
    const topScrollbarContainer = document.querySelector( '.top-scrollbar-container' );
    const topScrollbarSpacer = document.querySelector( '.top-scrollbar-spacer' );
    const dataTable = document.querySelector( 'dataTable' );
    const tableWidth = dataTable.scrollWidth;
    topScrollbarSpacer.style.width = tableWidth + 'px';
});