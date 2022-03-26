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
     * @param String|Array $studentsEmail
     */
    public function assingStudents($studentsEmail)
    {
        if (gettype($studentsEmail) == 'array')
        {
            $students = \App\Models\User::whereIn('email', $studentsEmail)->get();
        }
        else
        {
            $students = \App\Models\User::where('email', $studentsEmail)->first();
        }

        $this->students()->attach($students);
        
    }

    /**
     * Receive an email and remove this students from the classroom
     */
    public function unAssingStudents(string $studentsEmail)
    {
        $student = \App\Models\User::where('email', $studentsEmail)->first();
        
        $this->students()->detach($student);   
    }
}
