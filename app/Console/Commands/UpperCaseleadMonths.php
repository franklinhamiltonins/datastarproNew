<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Lead;
use Illuminate\Console\Command;

class UpperCaseleadMonths extends Command
{
    protected $signature = 'command:UpperCaseleadMonths';
    protected $description = 'Command to update renewal_month names from lowercase to uppercase';

    public function handle():void
    {
        $leads = Lead::limit(10)->get();

        foreach ($leads as $lead) {
            if (! $lead || empty($lead->renewal_month)) {
                continue;
            }

            $month = strtolower(trim($lead->renewal_month));

            $updatedMonth = $this->getProperCaseMonth($month);

            if ($updatedMonth !== $lead->renewal_month) {
                $lead->update(['renewal_month' => $updatedMonth]);
            }
        }
    }

    // Convert lowercase month name to proper case (January, February, etc.)
    private function getProperCaseMonth($month)
    {
        $map = [
            'january'   => 'January',
            'february'  => 'February',
            'march'     => 'March',
            'april'     => 'April',
            'may'       => 'May',
            'june'      => 'June',
            'july'      => 'July',
            'august'    => 'August',
            'september' => 'September',
            'october'   => 'October',
            'november'  => 'November',
            'december'  => 'December',
        ];

        return $map[$month] ?? $month; // Fallback keeps logic same
    }
}
