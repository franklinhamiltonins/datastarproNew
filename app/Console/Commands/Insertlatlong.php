<?php

namespace App\Console\Commands;

use App\Model\LeadsModel\Lead;
use App\Services\GetLangLongGoogleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Insertlatlong extends Command
{
    protected $signature = 'check:latlong';
    protected $description = 'Insert latitude and longitude of the addresses';

    public function handle()
    {
        $leads = $this->getLeadsToProcess();

        if ($leads->isEmpty()) {
            return $this->info("No leads found for processing.");
        }

        $progress = $this->output->createProgressBar($leads->count());
        $updatedCount = 0;

        foreach ($leads as $lead) {
            $address = $this->buildFullAddress($lead);

            if (!$address) {
                Log::channel('latlong')->error("Lead ID {$lead->id}: Invalid address.");
                continue;
            }

            $latLong = $this->getLatLngFromGoogle($address);
            if (!$latLong) {
                Log::channel('latlong')->error("Lead ID {$lead->id}: Failed to fetch coordinates.");
                continue;
            }

            if ($this->updateLeadLatLong($lead, $latLong)) {
                $updatedCount++;
                $progress->advance();
            }
        }

        $progress->finish();
        $this->info("\n{$updatedCount} addresses updated with latitude and longitude.");
    }

    private function getLeadsToProcess()
    {
        return Lead::whereNotNull('address1')
            ->limit(10)
            ->get();
    }

    private function buildFullAddress($lead)
    {
        $parts = array_filter([
            $lead->address1,
            $lead->address2,
            $lead->city,
            $lead->state,
            $lead->zip
        ]);

        return $parts ? implode(' ', $parts) : null;
    }

    private function updateLeadLatLong($lead, $latLong)
    {
        try {
            $lead->update([
                'latitude' => $latLong['lat'],
                'longitude' => $latLong['long'],
            ]);

            Log::channel('latlong')->info(
                "Lead ID {$lead->id} updated: lat {$latLong['lat']} long {$latLong['long']}"
            );

            return true;
        } catch (\Exception $e) {
            Log::channel('latlong')->error(
                "Error updating Lead ID {$lead->id}: " . $e->getMessage()
            );

            return false;
        }
    }

    private function getLatLngFromGoogle($address)
    {
        return (new GetLangLongGoogleService)
            ->getLatLngFromGoogleService($address);
    }
}
