<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Model\ScrapCounty;
use App\Model\ScrapCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

// use Log;
use DB;

use Illuminate\Validation\ValidationException;


class BotController extends Controller
{

	/**
	 * Import county and city from CSV file
	 * @param object $request
	 * @return bool true
	 */
	public function import_scrap(Request $request)
	{
		// return 'import_scrap';
		//validate file existance
		$this->validate($request, [
			'file' => 'required',
		]);

		//collect success/errors
		$dataSuccess = collect();
		$dataErrors = collect();
		$csvLeads = collect();
		$createdLeads = array();

		$niceNames = [
			'search_keyword' => 'Search Keyword',
			'city' => 'City',
			'state' => 'State',
			'state_code' => 'State Code',
			'county' => 'County',
		];

		//check if file exist and is readable
		if (!file_exists($request->file) || !is_readable($request->file)) {
			toastr()->error('Invalid file !');
			return redirect()->back();
		}

		// get the file extension in order to validate
		$extension = $request->file('file')->getClientOriginalExtension();
		// dd($extension);
		$name = $request->file('file')->getClientOriginalName();
		if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") { // if the extension matches, proceed

			//get data from csv file
			$fileData = self::readDataFromCsvForScrap($request->file, $extension);
			// dd($fileData);
			//if the fileData returns error, abort import
			if (isset($fileData['errors'])) {
				toastr()->error($fileData['errors']);
				return redirect()->back();
			}


			//created leads
			$created = 0;
			//updated rows (except heading)
			$updated = 1;
			//loop trough rows
			foreach ($fileData as $key => $data) {
				$updated++;  //increment updated rows

				//format fields that do not have the required DB format
				// $alldata =  self::format_csv_data($data, $updated, $niceNames); //returns data and errors
				$alldata =  self::format_csv_data($data, $updated, $niceNames); //returns data and errors

				$data = $alldata['data']; //get data

				//if there are errors , store them
				if (count($alldata['errors']) > 0) {
					$dataErrors->push($alldata['errors']);
				}
				// dd($data);
				$scraps = ScrapCity::storeCountyAndCity($data);
				// dd($scraps);
				$contact = false;

				// messages variable to use in blade
				$dataSuccess ? $messages['success'] = $dataSuccess : '';
				$dataErrors ? $messages['failures'] = $dataErrors : '';

				toastr()->success($created . ' City created and ' . $updated . ' rows processed!', 'Import Success!');
			}
			return redirect()->back()->withErrors('messages',  $messages);
			return redirect()->back();
		} else { //if the file exension doesn't match the required
			// toastr()->error('The file must be a file of type: csv, xlsx, xls.');
			// session(['error' => 'The file must be a file of type: csv, xlsx, xls.']);
			// return redirect()->back();
			// dd('error');
			// return redirect()->back()->withErrors('The file must be a file of type: csv, xlsx, xls.');
			return redirect()->back()->with('warning', 'Login is not successful.');
		}
	}

	/**
	 * Read data from csv file
	 * @param object $csvFile
	 * @return array $csvData
	 */
	private static function readDataFromCsvForScrap($csvFile, $extension)
	{

		//store file
		$fileName = Carbon::now()->format('mdYHisu');
		//if the file is xlsx or xls , convert it to csv
		if ($extension == "xlsx" || $extension == "xls") {
			if ($extension == "xlsx") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			} else if ($extension == "xls") {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
			}

			$reader->setReadDataOnly(true);


			$path = '../storage/app/public/uploads/' . $fileName . '.csv';
			$excel = $reader->load($csvFile);
			// dd($excel);
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
			// $writer->setUseBOM(true);
			// $writer->setOutputEncoding('UTF-8');
			$writer->setUseBOM(false);
			$writer->setOutputEncoding('UTF-8');
			$writer->setEnclosureRequired(false);
			$writer->save($path);

			$csvFile =  $path;
		} else {

			$file = Storage::putFileAs('public/uploads', $csvFile, $fileName . '.csv');
		}

		$delimiter = ',';
		$header = null;
		$csvData = array();
		//the required columns
		$requiredColumns = array(
			0 => "Search Keyword",
			1 => "City",
			2 => "State",
			3 => "State Code",
			4 => "County",

		);
		//read data and add it to array
		if (($handle = fopen($csvFile, 'r')) !== false) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {

				if (!$header) {
					$header = $row;
					//loop trough required columns and if one of them is missing in csv, send error
					foreach ($requiredColumns as $req) {

						if (!in_array($req, $header)) {

							$ColumnError =  'Column ' . $req . ' is missing. File was not imported';
							return array('errors' => $ColumnError);
						}
					}
				} else {
					if (count($header) > count($row)) {
						$csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), "")), 'UTF-8', 'UTF-8');
					} else if (count($header) < count($row)) {
						$csvData[] =  mb_convert_encoding(array_combine($header, array_slice($row, 0, count($header))), 'UTF-8', 'UTF-8');
					} else {
						$csvData[] = mb_convert_encoding(array_combine($header, $row), 'UTF-8', 'UTF-8');
					}
				}
			}
			fclose($handle);
		}
		return $csvData;
	}

	private static function storeCountyAndCity($data)
	{
		// dd($data);
		//store into scrap county
		if ($data['County']) {
			$scrapCounty = ScrapCounty::updateOrCreate([
				'name'   => $data['County'],
			], [
				'name'     => $data['County'],
				'status' => 1,
			]);
			// dd($scrapCounty->id);

			if ($scrapCounty) {
				//store into scrap city
				$scrapCity = ScrapCity::updateOrCreate([
					'search_keyword'   => $data['Search Keyword'],
					'city' => $data['City'],
					'state' => $data['State'],
					'state_code' => $data['State Code'],
					'county_id' => $scrapCounty->id
				], [
					'search_keyword'   => $data['Search Keyword'],
					'city' => $data['City'],
					'state' => $data['State'],
					'state_code' => $data['State Code'],
					'county_id' => $scrapCounty->id,
					'status' => 1
				]);

				return $scrapCity->id;
			}
		}
	}


	private static function format_csv_data($data, $updated, $niceNames)
	{
		$dataErrors = collect();
		foreach ($data as $key => $r) { // loop trough row cells
			echo $key;
			if ($key) {
				if ($key !== 'County' || $key !== 'county') {
					$dataErrors->push(
						array(
							"row" => $updated,
							"attribute" => $key,
							"errors" => "Invalid '" . $key . "'  value: " . $data[$key] . " - was not imported ",
							"values" => $data[$key],

						)
					);
				}
				$data[$key] = $r;
			}
		}

		return array('data' => $data, 'errors' => $dataErrors);
	}

	//view to index
	public function botSettings(Request $request)
	{
		$vars = array();
		$cities = ScrapCity::with('scrapCounty')->orderBy('city')->paginate(30);
		// dd($cities);
		return view('bot.index', compact('cities'))->with('i', ($request->input('page', 1) - 1) * 10);
	}

	//view to settings import
	public function botImport(Request $request)
	{
		$vars = array();
		return view('bot.settings', compact($vars));
	}
}
