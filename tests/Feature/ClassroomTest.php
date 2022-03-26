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


    public function test_teacher_can_assing_student_to_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);
        
        $classroom->assingStudents([\App\Models\User::factory()->create()->email]);

        $this->assertEquals(1, $classroom->students()->count());
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

    
    public function test_teacher_cant_assing_students_to_a_non_own_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);

        $userTeacher2 = \App\Models\User::factory()->create(['role' => 'teacher']);

        $studentEmail = \App\Models\User::factory()->create()->email;

        $this->actingAs($userTeacher2)
        ->post(route('classrooms.assingstudent',$classroom->id),[
            'student_email' => $studentEmail
        ])->assertForbidden();

        $this->assertEquals(0, $classroom->students()->count());
    }

    public function test_teacher_assing_students_to_a_own_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);

        $studentEmail = \App\Models\User::factory()->create()->email;

        $this->actingAs($userTeacher)
        ->post(route('classrooms.assingstudent', $classroom->id),[
            'student_email' => $studentEmail
        ])->assertOk();

        $this->assertEquals(1, $classroom->students()->count());
    }

    /**
     * @depens test_teacher_assing_students_to_a_own_classroom
     */
    public function test_teacher_cant_unassing_students_to_a_non_own_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);

        $studentEmail = \App\Models\User::factory()->create()->email;

        $classroom->assingStudents([$studentEmail]);

        $userTeacher2 = \App\Models\User::factory()->create(['role' => 'teacher']);

        $this->actingAs($userTeacher2)
        ->delete(route('classrooms.unassingstudent',$classroom->id),[
            'student_email' => $studentEmail
        ])->assertForbidden();

        $this->assertEquals(1, $classroom->students()->count());
    }

    public function test_teacher_unassing_students_to_a_own_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);

        $studentEmail = \App\Models\User::factory()->create()->email;

        $classroom->assingStudents([$studentEmail]);


        $this->actingAs($userTeacher)
        ->delete(route('classrooms.unassingstudent',$classroom->id),[
            'student_email' => $studentEmail
        ])->assertOk();

        $this->assertEquals(0, $classroom->students()->count());
    }
}
