<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'start_time', 'end_time', 'start_break_hour', 'end_break_hour'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
