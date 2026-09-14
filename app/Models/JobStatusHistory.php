<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'job_status_history';

    protected $fillable = ['job_card_id', 'from_status', 'to_status', 'user_id', 'notes'];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
