<?php

namespace App\Traits;

trait MessageConstantsTrait
{
    // SMTP related messages
    public const SMTP_NOT_CONFIGURED = 'You do not have SMTP configuration. Please set it up before attempting to download.';

    // Download report
    public const REPORT_GENERATING = 'Report is being generated. You will receive it via email shortly.';

    public const PUBLIC_CSV = 'public/csv';

    public const SEARCH_KEYWORD = 'Search Keyword';

    public const STATE_CODE = 'State Code';

    public static function publicPathCsv()
    {
        return storage_path('app/public/csv');
    }
}
