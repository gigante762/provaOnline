<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cant_create_classroms()
    {
        $userStudent = \App\Models\User::factory()->create();
        
        $this->actingAs($userStudent)
        ->post(route('classrooms.store'), [
            'name' => 'Class test name',
            'user_id' => $userStudent->id,
        ])->assertForbidden();
    }

    public function test_teacher_can_create_classroms()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $this->actingAs($userTeacher)
        ->post(route('classrooms.store'), [
            'name' => 'Class test name',
            'user_id' => $userTeacher->id,
        ])->assertOk();

    }

    /**
     * @depens test_teacher_can_create_classroms
     */
    public function test_create_classroom_from_user()
    {
        $user = \App\Models\User::factory()->create();

        \App\Models\Classroom::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertDatabaseCount('classrooms', 1);
    }


    public function test_teacher_can_assing_students_to_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);
        
        $studentsEmails = [];
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        
        $classroom->assingStudents($studentsEmails);

        $this->assertEquals(3, $classroom->students()->count());
    } 

    /**
     * @depens test_teacher_can_assing_students_to_classroom
     */
    public function test_teacher_can_remove_students_from_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);
        
        $studentEmail = \App\Models\User::factory()->create()->email;
        
        $classroom->unAssingStudents($studentEmail);

        $this->assertEquals(0, $classroom->students()->count());
    } 
}
