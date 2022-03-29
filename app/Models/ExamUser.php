<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamUser extends Pivot
{
    use HasFactory;

    protected $table = 'exam_user';


    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
