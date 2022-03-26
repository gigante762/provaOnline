<?php

namespace App\Models;

use App\Jobs\AssingExamToClassroom;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function examsAvailables()
    {
        return $this->belongsToMany(Exam::class)
        ->withPivot('answers', 'opened_at', 'closed_at', 'uuid');
    }

    public function scopeExamsOpeneds($query)
    {
        return $query->whereDate(now(),'>=','open_at');
    }

    public function applyExamToClassroom(Exam $exam, Classroom $classroom)
    {
        AssingExamToClassroom::dispatch($exam, $classroom);
    }

    
    
}
