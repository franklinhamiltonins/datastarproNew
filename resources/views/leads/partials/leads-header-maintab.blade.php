<ul class="nav nav-tabs nav-justified" id="pills-tab" role="tablist">
    <li class="nav-item">
        <a
            class="nav-link active upperpaneltab_leads"
            id="pills-Lead-tab"
            data-bs-toggle="pill"
            href="#pills-Lead"
            role="tab"
            aria-controls="pills-Lead"
            aria-selected="true"
        >
            Lead
        </a>
    </li>
    <li class="nav-item">
        <a
            class="nav-link upperpaneltab_leads"
            id="pills-client-insurance-info-tab"
            data-bs-toggle="pill"
            href="#pills-client-insurance-info"
            role="tab"
            aria-controls="pills-client-insurance-info"
            aria-selected="true"
        >
            Client Insurance Info
        </a>
    </li>
    <li class="nav-item">
        <a
            class="nav-link upperpaneltab_leads"
            id="pills-lead-actions-tab"
            data-bs-toggle="pill"
            href="#pills-lead-actions"
            role="tab"
            aria-controls="pills-lead-actions"
            aria-selected="true"
        >
            Lead Actions
        </a>
    </li>
    <li class="nav-item">
        <a
            class="nav-link upperpaneltab_leads"
            id="pills-lead-campaigns-tab"
            data-bs-toggle="pill"
            href="#pills-lead-campaigns"
            role="tab"
            aria-controls="pills-lead-campaigns"
            aria-selected="true"
        >
            Lead Campaigns
        </a>
    </li>
    @can("lead-file-list")
        <li class="nav-item">
            <a
                class="nav-link t upperpaneltab_leads"
                id="pills-File-tab"
                data-bs-toggle="pill"
                href="#pills-File"
                role="tab"
                aria-controls="pills-File"
                aria-selected="false"
            >
                Uploaded Files
            </a>
        </li>
    @endcan
</ul>
