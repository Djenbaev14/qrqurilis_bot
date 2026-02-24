<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'application_id',
        'status_id',
        'changed_at',
        'comment',
    ];
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
