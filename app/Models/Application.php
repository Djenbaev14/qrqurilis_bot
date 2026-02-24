<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    protected $fillable = [
        'resident_id',
        'address',
        'message',
        'status_id',
        'company_id',
        'region_id',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function photos()
    {
        return $this->hasMany(ApplicationPhoto::class);
    }
    public function histories()
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }
}
