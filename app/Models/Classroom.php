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
    public function assingStudents($studentsEmails)
    {
        $emails = (gettype($studentsEmails) == 'string') ? [$studentsEmails] : $studentsEmails;
        $students = \App\Models\User::whereIn('email', $emails)->get();
        $this->students()->attach($students->pluck('id')->all());

    }

    /**
     * Receive an email and remove this students from the classroom
     */
    public function unAssingStudents($studentsEmails)
    {
        $emails = (gettype($studentsEmails) == 'string') ? [$studentsEmails] : $studentsEmails;
        $students = \App\Models\User::whereIn('email', $emails)->get();
        $this->students()->detach($students->pluck('id')->all());
          
    }
}
