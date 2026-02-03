<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Vonage\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use Hash;


use App\Model\User;
use App\Model\ActivityReportFile;
use App\Model\ActivityReportAor;
use App\Model\ActivityReport;
use App\Model\MailerLeadTracker;
use App\Model\DailyCallReportLog;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Contact;
use App\Model\LeadSource;

trait ActivityReportTrait
{
    protected function activityListQuery($requestData,$aor=false,$manager_id=0)
    {
        $formatted_date = $this->getFormatedDate($requestData);
        if($requestData['view_type'] == 1){
            $query = ActivityReport::query();
            if($aor){
                $query = $query->with(['agent','aor','leads']);
            }
            else{
                $query = $query->with(['agent','leads']);
            }
        }
        else{
            $query = ActivityReport::join('users', 'users.id', '=', 'activity_reports.user_id')
            ->leftjoin("leads","activity_reports.community_id","=","leads.id")
            ->groupBy("user_id")
            ->select(
                'user_id',
                'users.name as agent_name',
                "leads.name as community_name",
                DB::raw("COUNT(*) as total_lead"),
                DB::raw("SUM(appointments) as total_appointments"),
                DB::raw("SUM(policies) as total_policies"),
                DB::raw("SUM(expiry_policies_premium) as total_expiry_policies_premium"),
            );
        }
        
        if(!empty($formatted_date["from"]) && !empty($formatted_date["to"])){
            $query->whereBetween('date', [$formatted_date["from"],$formatted_date["to"]]);
        }
        if (!empty($requestData['agent'])) {
            $query->where('user_id', $requestData['agent']);
        }
        elseif($manager_id > 0){
            $accountIds = $this->getAllAccountIdsForManager($manager_id);

            if (!empty($accountIds)) {
                $query->whereIn('user_id', $accountIds);
            }
        }

        return $query;
    }

    protected function generateMailLeadTrackerData($requestData,$manager_id=0)
    {
        $formatted_date = $this->getFormatedDate($requestData);
        if($requestData['view_type'] == 1){
            $query = MailerLeadTracker::query()
            ->with(['leadSource', 'agent']);

        }
        else{
            $query = MailerLeadTracker::join('users', 'users.id', '=', 'mailer_leads_tracker.user_id')
            ->groupBy("user_id")
            ->select(
                'user_id',
                'users.name as agent_name',
                DB::raw("COUNT(*) as total_lead")
            );
        }

        if (!empty($requestData['lead_source'])) {
            $query->where('lead_source', $requestData['lead_source']);
        }

        if (!empty($requestData['agent'])) {
            $query->where('user_id', $requestData['agent']);
        }
        else{
            $accountIds = $this->getAllAccountIdsForManager($manager_id);

            if (!empty($accountIds)) {
                $query->whereIn('user_id', $accountIds);
            }
        }

        if(!empty($formatted_date["from"]) && !empty($formatted_date["to"])){
            $query->whereBetween('mailer_leads_tracker.date', [date("Y-m-d",strtotime($formatted_date["from"])),date("Y-m-d",strtotime($formatted_date["to"]))]);
        }

        return $query;
    }

    protected function generateDailyReportData($agent = null, $from = null, $to = null,$manager_id=0)
    {
        $usersQuery = User::select('id', 'name', 'bigoceanuser_id')
            ->whereNotNull('bigoceanuser_id');

        if (!empty($agent)) {
            $usersQuery->where("id", $agent);
        }
        else{
            $accountIds = $this->getAllAccountIdsForManager($manager_id);

            if (!empty($accountIds)) {
                $usersQuery->whereIn('id', $accountIds);
            }
        }

        $users = $usersQuery->get();

        return $users->map(function ($user) use ($from, $to) {
            $dcrQuery = DailyCallReportLog::where('user_franklin_id', $user->bigoceanuser_id);
            $mailerQuery = MailerLeadTracker::where('user_id', $user->id);
            $activityQuery = ActivityReport::where('user_id', $user->id);

            if ($from && $to) {
                $dcrQuery->whereBetween('call_begin', [$from, $to]);
                $mailerQuery->whereBetween('created_at', [$from, $to]);
                $activityQuery->whereBetween('date', [date("Y-m-d", strtotime($from)), date("Y-m-d", strtotime($to))]);
            }

            $outboundCalls = (clone $dcrQuery)->where('call_type', 'Outbound')->count();

            $leadSources_arr = ['Facebook', 'Mailer', 'SMS', 'Email', '611 Transfer', '611 Referral Email'];
            $leadCounts = [];

            foreach ($leadSources_arr as $source) {
                $source_ids = LeadSource::where('status', 1)
                    ->where("name", "like", "%" . $source . "%")
                    ->pluck("id")
                    ->toArray();

                $leadCounts[$source] = (clone $mailerQuery)->whereIn('lead_source', $source_ids)->count();
            }

            $appointments = (clone $activityQuery)->sum(DB::raw('IFNULL(appointments, 0)'));
            $policies = (clone $activityQuery)->sum(DB::raw('IFNULL(policies, 0)'));
            $expiryPremium = (clone $activityQuery)->sum(DB::raw('IFNULL(expiry_policies_premium, 0)'));

            $activityIds = (clone $activityQuery)->pluck('id');
            $aorDetails = ActivityReportAor::whereIn('activity_report_id', $activityIds)->get();

            // $aorCsv = $aorDetails->pluck('aor')->unique()->filter()->implode(', ');
            $aorSum = $aorDetails->sum(function ($row) {
                return is_numeric($row->aor) ? $row->aor : 0;
            });
            $aorMonthCsv = $aorDetails->pluck('aor_effective_date')->filter()->map(function ($date) {
                try {
                    return Carbon::parse($date)->format('F');
                } catch (\Exception $e) {
                    return null;
                }
            })->filter()->unique()->implode(', ');

            $aorPremiumSum = $aorDetails->sum(function ($row) {
                return is_numeric($row->expiring_aor_premium) ? $row->expiring_aor_premium : 0;
            });

            return [
                'producer_name' => $user->name,
                'outbound_calls' => $outboundCalls,
                'facebook' => $leadCounts['Facebook'] ?? 0,
                'mailer' => $leadCounts['Mailer'] ?? 0,
                'sms' => $leadCounts['SMS'] ?? 0,
                'email' => $leadCounts['Email'] ?? 0,
                'transfer_611' => $leadCounts['611 Transfer'] ?? 0,
                'referal_611' => $leadCounts['611 Referral Email'] ?? 0,
                'appointments' => $appointments,
                'policies' => $policies,
                'expiry_premium' => $expiryPremium,
                'aor' => $aorSum,
                'aor_effective_month' => $aorMonthCsv,
                'aor_premium' => $aorPremiumSum,
            ];
        });
    }

    protected function getFormatedDate($requestData)
    {
        $from = $to = null;
        if (isset($requestData['date_range']) && $requestData['date_range']) {
            $now = Carbon::now();
        
            switch ($requestData['date_range']) {
                case 'yesterday':
                    $from = $now->copy()->subDay()->startOfDay();
                    $to = $now->copy()->subDay()->endOfDay();
                    break;
                case 'last_7_days':
                    $from = $now->copy()->subDays(6)->startOfDay();
                    $to = $now->endOfDay();
                    break;
                case 'last_30_days':
                    $from = $now->copy()->subDays(29)->startOfDay();
                    $to = $now->endOfDay();
                    break;
                case 'custom':
                    if (isset($requestData['from_date']) && $requestData['from_date'] && 
                        isset($requestData['to_date']) && $requestData['to_date']) {
                        $from = Carbon::parse($requestData['from_date'])->startOfDay();
                        $to = Carbon::parse($requestData['to_date'])->endOfDay();
                    }
                    break;
                case 'custom_days':
                    if (isset($requestData['custom_days']) && is_numeric($requestData['custom_days'])) {
                        $from = $now->copy()->subDays($requestData['custom_days'] - 1)->startOfDay();
                        $to = $now->endOfDay();
                    }
                    break;
            }
        }

        return [
            "from" => $from,
            "to" => $to,
        ];
    }

    protected function safeDate($date)
    {
        try {
            if (!$date || $date == "0000-00-00") {
                return '';
            }
            return Carbon::parse($date)->format('m/d/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function safeNumberFormat($num, $decimals = 2) {
        $clean = preg_replace('/[^0-9.\-]/', '', $num);
        return number_format((float)(is_numeric($clean) ? $clean : 0), $decimals);
    }



    protected function formatAorDetails($activityReports)
    {
        $formatted = [];

        foreach ($activityReports as $report) {
            $item = [
                'id' => $report->id,
                'date' => $this->safeDate($report->date),
                'agent_name' => optional($report->agent)->name,
                'appointments' => $report->appointments,
                'policies' => $report->policies,
                'expiry_policies_premium' => $this->safeNumberFormat($report->expiry_policies_premium,2),
                'community_name' => $report->community_name,
                'aor_breakdown' => $report->aor_breakdown,
            ];

            $aors = $report->aor->take(5); // Take only first 5 AOR records
            // echo "<pre>";print_r($aors);exit;

            foreach ($aors as $index => $aor) {
                $i = $index + 1;

                $item["aor{$i}"] = $aor->aor ?? '';
                $item["aor_community_name{$i}"] = $aor->aor_community_name ?? '';
                $item["aor_effective_date{$i}"] =  $this->safeDate($aor->aor_effective_date);
                $item["expiring_aor_premium{$i}"] = $this->safeNumberFormat($aor->expiring_aor_premium,2);
            }

            // Fill missing AORs with N/A if less than 5
            for ($i = count($aors) + 1; $i <= 5; $i++) {
                $item["aor{$i}"] = 'N/A';
                $item["aor_community_name{$i}"] = '';
                $item["aor_effective_date{$i}"] = '';
                $item["expiring_aor_premium{$i}"] = '0';
            }

            $formatted[] = $item;
        }

        return $formatted;
    }


    
}
