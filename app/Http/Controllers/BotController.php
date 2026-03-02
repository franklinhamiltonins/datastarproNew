<?php

namespace App\Http\Controllers;

use App\Model\ScrapCity;
use App\Traits\MessageConstantsTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BotController extends Controller
{
    use MessageConstantsTrait;

    /**
     * Import county and city from CSV file
     *
     * @param  object  $request
     * @return bool true
     */
    public function importScrap(Request $request)
    {
        // validate file existance
        $this->validate($request, [
            'file' => 'required',
        ]);

        // collect success/errors
        $dataSuccess = collect();
        $dataErrors = collect();

        $niceNames = [
            'search_keyword' => self::SEARCH_KEYWORD,
            'city' => 'City',
            'state' => 'State',
            'state_code' => self::STATE_CODE,
            'county' => 'County',
        ];

        // check if file exist and is readable
        if (! file_exists($request->file) || ! is_readable($request->file)) {
            toastr()->error('Invalid file !');

            return redirect()->back();
        }

        // get the file extension in order to validate
        $extension = $request->file('file')->getClientOriginalExtension();
        // if the extension matches, proceed
        if ($extension == 'xlsx' || $extension == 'xls' || $extension == 'csv') {

            // get data from csv file
            $fileData = self::readDataFromCsvForScrap($request->file, $extension);
            // if the fileData returns error, abort import
            if (isset($fileData['errors'])) {
                toastr()->error($fileData['errors']);

                return redirect()->back();
            }

            // created leads
            $created = 0;
            // updated rows (except heading)
            $updated = 1;
            // loop trough rows
            foreach ($fileData as $data) {
                $updated++;  // increment updated rows

                // format fields that do not have the required DB format
                $alldata = self::formatCsvData($data, $updated, $niceNames); // returns data and errors

                $data = $alldata['data']; // get data

                // if there are errors , store them
                if (count($alldata['errors']) > 0) {
                    $dataErrors->push($alldata['errors']);
                }
                ScrapCity::storeCountyAndCity($data);

                // messages variable to use in blade
                $dataSuccess ? $messages['success'] = $dataSuccess : '';
                $dataErrors ? $messages['failures'] = $dataErrors : '';

                toastr()->success($created.' City created and '.$updated.' rows processed!', 'Import Success!');
            }

            return redirect()->back()->withErrors('messages', $messages);
        } else {
            return redirect()->back()->with('warning', 'The file must be a file of type: csv, xlsx, xls.');
        }
    }

    /**
     * Read data from csv file
     *
     * @param  object  $csvFile
     * @return array $csvData
     */
    private static function readDataFromCsvForScrap($csvFile, $extension)
    {

        // store file
        $fileName = Carbon::now()->format('mdYHisu');
        // if the file is xlsx or xls , convert it to csv
        if ($extension == 'xlsx' || $extension == 'xls') {
            if ($extension == 'xlsx') {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            } elseif ($extension == 'xls') {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
            }

            $reader->setReadDataOnly(true);

            $path = '../storage/app/public/uploads/'.$fileName.'.csv';
            $excel = $reader->load($csvFile);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($excel);
            $writer->setUseBOM(false);
            $writer->setOutputEncoding('UTF-8');
            $writer->setEnclosureRequired(false);
            $writer->save($path);

            $csvFile = $path;
        } else {

            Storage::putFileAs('public/uploads', $csvFile, $fileName.'.csv');
        }

        $delimiter = ',';
        $header = null;
        $csvData = [];
        // the required columns
        $requiredColumns = [
            0 => self::SEARCH_KEYWORD,
            1 => 'City',
            2 => 'State',
            3 => self::STATE_CODE,
            4 => 'County',

        ];
        // read data and add it to array
        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {

                if (! $header) {
                    $header = $row;
                    // loop trough required columns and if one of them is missing in csv, send error
                    foreach ($requiredColumns as $req) {

                        if (! in_array($req, $header)) {

                            $ColumnError = 'Column '.$req.' is missing. File was not imported';

                            return ['errors' => $ColumnError];
                        }
                    }
                } else {
                    if (count($header) > count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_pad($row, count($header), '')), 'UTF-8', 'UTF-8');
                    } elseif (count($header) < count($row)) {
                        $csvData[] = mb_convert_encoding(array_combine($header, array_slice($row, 0, count($header))), 'UTF-8', 'UTF-8');
                    } else {
                        $csvData[] = mb_convert_encoding(array_combine($header, $row), 'UTF-8', 'UTF-8');
                    }
                }
            }
            fclose($handle);
        }

        return $csvData;
    }

    private static function formatCsvData($data, $updated, $niceNames)
    {
        $dataErrors = collect();
        // loop trough row cells
        foreach ($data as $key => $r) {
            if ($key) {
                if ($key !== 'County' || $key !== 'county') {
                    $dataErrors->push(
                        [
                            'row' => $updated,
                            'attribute' => $key,
                            'errors' => "Invalid '".$key."'  value: ".$data[$key].' - was not imported ',
                            'values' => $data[$key],
                        ]
                    );
                }
                $data[$key] = $r;
            }
        }

        return ['data' => $data, 'errors' => $dataErrors];
    }

    // view to index
    public function botSettings(Request $request)
    {
        $cities = ScrapCity::with('scrapCounty')->orderBy('city')->paginate(30);

        return view('bot.index', compact('cities'))->with('i', ($request->input('page', 1) - 1) * 10);
    }

    // view to settings import
    public function botImport()
    {
        return view('bot.settings');
    }
}
