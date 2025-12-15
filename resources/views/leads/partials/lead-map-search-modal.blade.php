<style>
    #mapsearch.modal.fade.show {
        padding-right: 0 !important;
    }

    #mapsearch .modal-dialog {
        max-width: 98%;
    }

    #mapsearch .modal-dialog .modal-header {
        padding: 0.5rem !important;
    }

    #mapsearch .modal-dialog .modal-body {
        padding: 0 !important;
    }

    #mapsearch .modal-dialog .modal-footer {
        padding: 0.5rem !important;
    }

    #map-canvas {
        height: 77vh;
        margin: 0px;
        padding: 0px
    }

    #map-canvas-overlay {
        height: 100%;
        z-index: 999;
        position: absolute;
        width: 100%;
        display: block;
    }

    .controls {
        margin-top: 16px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    .gmnoprint.gm-style-mtc-bbw {
        display: none;
    }

    .pac-container {
        font-family: Roboto;
    }

    #type-selector {
        color: #fff;
        background-color: #4d90fe;
        padding: 5px 11px 0px 11px;
    }

    #type-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
    }

    #target {
        width: 345px;
    }
</style>
<div class="modal fade" id="mapsearch" data-source="" style="display: none;" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ">
            <div class="modal-header p-2 p-lg-3 align-items-center">
                <h5 class="modal-title">Map Search</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-2 p-lg-3">
                <div id="map-canvas"></div>
            </div>
            <div class="modal-footer justify-content-between p-2 p-lg-3">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="map_marker_confirm_button" disabled>Search</button>
            </div>
        </div>
    </div>
</div>