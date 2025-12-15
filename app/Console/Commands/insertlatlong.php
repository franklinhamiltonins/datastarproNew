<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\LeadsModel\Lead;
use App\Model\Addressdata;
use Illuminate\Support\Facades\Log;

class insertlatlong extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'check:latlong';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Insert latitutde and longitude of the adresses';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$leads = Lead::whereNotNull('address1')->whereNull('deleted_at')
			// ->where(function ($query) {
			// 	$query->whereNotNull('latitude')
			// 		->orWhereNotNull('longitude')
			// 		->orWhere('latitude', '!=', '')
			// 		->orWhere('longitude', '!=', '');
			// })
			->get();
		// dd($leads);
		$count = 0;
		if ($leads->count()) {
			$bar = $this->output->createProgressBar($leads->count());
			$updated = 0;
			foreach ($leads as $lead) {
				if ($lead->address1 && $lead->address1) {
					$address_text = ($lead->address1 && $lead->address2) ? $lead->address1 . ' ' . $lead->address2 : ($lead->address1 ? $lead->address1 : $lead->address2);
					$address_text = $lead->city ? $address_text . ' ' . $lead->city . ',' : $address_text;
					$address_text = $lead->state ? $address_text . ' ' . $lead->state . ',' : $address_text;
					$address_text = $lead->zip ? $address_text . ' ' . $lead->zip . ',' : $address_text;
					$lat_long = $this->getLatLngFromGoogle($address_text);
					$edit_lead = Lead::find($lead->id);
					if ($edit_lead) {
						$lead_update = [];
						$lead_update['latitude'] = $lat_long['lat'];
						$lead_update['longitude'] = $lat_long['long'];
						try {
							$updated++;
							$edit_lead->update($lead_update);
							$count++;
							$bar->advance();
							Log::channel('latlong')->info('Lead ID ' . $lead->id . ' with latitude : ' . $lat_long['lat'] . ' and longitide : ' . $lat_long['long'] . ' updated successfully.');
						} catch (\Exception $e) {
							Log::channel('latlong')->error('Error updating Lead ID ' . $lead->id . ': ' . $e->getMessage());
						}
					} else {
						Log::channel('latlong')->error('Lead Id  ' . $lead->id . ':  not found.');
					}
				}
			}
			$bar->finish();
			$this->info($count . ' addresses have been updated with latitude and longitude');
		}
	}

	public function getLatLngFromGoogle($address)
	{
		$data['lat'] = null;
		$data['long'] = null;
		$address_data = Addressdata::where('address', $address)->first();
		if ($address_data) {
			$data['lat'] = $address_data->latitude;
			$data['long'] = $address_data->longitude;
			return $data;
		}
		//Google Map API URL
		$API_KEY = env('GOOGLE_MAP_API_KEY');
		$url = "https://maps.google.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $API_KEY;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($response);

		if ($result && $result->status == "OK") {
			$data['lat'] = $result->results[0]->geometry->location->lat;
			$data['long'] = $result->results[0]->geometry->location->lng;
			Addressdata::create(['address' => $address, 'latitude' => $data['lat'], 'longitude' => $data['long']]);
		}
		return $data;
	}
}
