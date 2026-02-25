<?php

namespace App\Model\LeadsModel\LeadTrait;

use App\Model\Carrier;
use App\Model\Rating;

/**
 * Trait for relationship methods in Lead model.
 * Contains all carrier, rating, and other relationship definitions.
 */
trait LeadRelationshipsTraitTwo
{
    // ==================== Carrier Relationships ====================

    /**
     * Get the property carrier associated with this lead.
     */
    public function propertyCarrier()
    {
        return $this->belongsTo(Carrier::class, 'ins_prop_carrier');
    }

    /**
     * Get the general liability carrier.
     */
    public function glCarrier()
    {
        return $this->belongsTo(Carrier::class, 'general_liability');
    }

    /**
     * Get the crime insurance carrier.
     */
    public function ciCarrier()
    {
        return $this->belongsTo(Carrier::class, 'crime_insurance');
    }

    /**
     * Get the directors & officers carrier.
     */
    public function doCarrier()
    {
        return $this->belongsTo(Carrier::class, 'directors_officers');
    }

    /**
     * Get the umbrella carrier.
     */
    public function umbrellaCarrier()
    {
        return $this->belongsTo(Carrier::class, 'umbrella');
    }

    /**
     * Get the workers compensation carrier.
     */
    public function wcCarrier()
    {
        return $this->belongsTo(Carrier::class, 'workers_compensation');
    }

    /**
     * Get the flood carrier.
     */
    public function floodCarrier()
    {
        return $this->belongsTo(Carrier::class, 'flood');
    }

    /**
     * Get the difference in condition carrier.
     */
    public function dcCarrier()
    {
        return $this->belongsTo(Carrier::class, 'difference_in_condition');
    }

    /**
     * Get the x-wind carrier.
     */
    public function xwindCarrier()
    {
        return $this->belongsTo(Carrier::class, 'x_wind');
    }

    /**
     * Get the equipment breakdown carrier.
     */
    public function ebCarrier()
    {
        return $this->belongsTo(Carrier::class, 'equipment_breakdown');
    }

    /**
     * Get the commercial automobiles carrier.
     */
    public function caCarrier()
    {
        return $this->belongsTo(Carrier::class, 'commercial_automobiles');
    }

    /**
     * Get the marina carrier.
     */
    public function marinaCarrier()
    {
        return $this->belongsTo(Carrier::class, 'marina');
    }

    // ==================== Rating Relationships ====================

    /**
     * Get the property rating.
     */
    public function propertyRating()
    {
        return $this->belongsTo(Rating::class, 'rating');
    }

    /**
     * Get the general liability rating.
     */
    public function generaLiablityRating()
    {
        return $this->belongsTo(Rating::class, 'gl_rating');
    }

    /**
     * Get the crime insurance rating.
     */
    public function crimeInsuranceRating()
    {
        return $this->belongsTo(Rating::class, 'ci_rating');
    }

    /**
     * Get the directors & officers rating.
     */
    public function directorOfficerRating()
    {
        return $this->belongsTo(Rating::class, 'do_rating');
    }

    /**
     * Get the umbrella rating.
     */
    public function uRating()
    {
        return $this->belongsTo(Rating::class, 'umbrella_rating');
    }

    /**
     * Get the workers compensation rating.
     */
    public function workerCompansestionRating()
    {
        return $this->belongsTo(Rating::class, 'wc_rating');
    }

    /**
     * Get the flood rating.
     */
    public function fRating()
    {
        return $this->belongsTo(Rating::class, 'flood_rating');
    }
}
