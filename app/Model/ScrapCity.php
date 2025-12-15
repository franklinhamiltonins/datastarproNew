<?php


namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  App\Model\LeadsModel\Lead;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapCity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'search_keyword', 'city', 'state', 'state_code', 'county_id', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function scrapCounty()
    {
        // return $this->belongsTo(ScrapCounty::class, 'scrap_county');
        return $this->belongsTo(ScrapCounty::class, 'county_id');
    }

    public static function storeCountyAndCity($data)
    {
        // dd($data);
        // return;

        //store into scrap county
        if ($data['County']) {
            $scrapCounty = ScrapCounty::updateOrCreate(
                [
                    'name'   => $data['County'],
                ],
                [
                    'name'     => $data['County'],
                    'status' => 1,
                ]
            );
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

                return true;
            }
        }
    }
}
