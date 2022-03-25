<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['name','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Receive an email's array and add these students into the classroom
     */
    public function assingStudents(array $studentsEmail)
    {

        $students = \App\Models\User::whereIn('email', $studentsEmail)->get();
        

        $this->students()->attach($students);
        
    }
}
