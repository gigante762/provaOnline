<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = ['title','user_id','content','open_at','close_at'];

    protected $casts = [
        'open_at' => 'date',
        'close_at' => 'date'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
