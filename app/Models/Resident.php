<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;
    protected $fillable = [
        'telegram_id',
        'username',
        'full_name',
        'phone'
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
