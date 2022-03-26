<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /* Criar uma situração simples */

        $teacher = \App\Models\User::factory()
        ->has(\App\Models\Exam::factory())
        ->has(\App\Models\Classroom::factory())
        ->create(['role'=>'teacher']);

        $exam = $teacher->exams()->first();

        $classroom = $teacher->classrooms()->first();

        $studentsEmails = [];
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;

        $classroom->assingStudents($studentsEmails);
        
        $teacher->applyExamToClassroom($exam, $classroom);
    }
}
