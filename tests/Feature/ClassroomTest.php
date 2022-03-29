<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_index_view()
    {
        $userStudent = \App\Models\User::factory()->create();
        
        $this->actingAs($userStudent)
        ->get(route('classrooms.index'))->assertSuccessful();
    }

    public function test_students_cant_create_classrooms()
    {
        $userStudent = \App\Models\User::factory()->create();
        
        $this->actingAs($userStudent)
        ->post(route('classrooms.store'), [
            'name' => 'Class test name',
        ])->assertForbidden();
    }

    public function test_teacher_can_create_classrooms()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $this->actingAs($userTeacher)
        ->post(route('classrooms.store'), [
            'name' => 'Class test name',
        ]);

        $this->assertDatabaseHas('classrooms',[
            'name' => 'Class test name',
            'user_id' => $userTeacher->id
        ]);

    }

    public function test_assing_students()
    {
        $teacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $teacher->id
        ]);

        $this->actingAs($teacher);
        
        $student = \App\Models\User::factory()->create();

        $this->assertEquals(0,  $classroom->students()->count());

        $this->post(route('classrooms.assingstudent',$classroom->id),[
            'emails' => $student->email
        ]);

        $this->assertDatabaseHas('classroom_user',[
            'user_id' => $student->id,
            'classroom_id' => $classroom->id
        ]);

        $this->assertEquals(1, $classroom->students()->count());

        $studentsEmails = [];
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        
        $this->post(route('classrooms.assingstudent',$classroom->id),[
            'emails' => $studentsEmails
        ]);

        $this->assertEquals(4, $classroom->students()->count());
    }


    /**
     * @depens test_assing_students_method
     */
    public function test_unassing_students_method()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);
        
        $studentEmail = \App\Models\User::factory()->create()->email;

        $classroom->assingStudents($studentEmail);
        $this->assertEquals(1, $classroom->students()->count());
        
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
            'emails' => $studentEmail
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
            'emails' => $studentEmail
        ]);

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
            'emails' => $studentEmail
        ])->assertForbidden();

        $this->assertEquals(1, $classroom->students()->count());
    }

    public function test_teacher_unassing_students_to_a_own_classroom()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $classroom = \App\Models\Classroom::factory()->create([
            'user_id' => $userTeacher->id
        ]);

        $student = \App\Models\User::factory()->create();
        $studentEmail = $student->email;

        $classroom->assingStudents($studentEmail);


        $this->actingAs($userTeacher)
        ->delete(route('classrooms.unassingstudent',$classroom->id),[
            'emails' => $studentEmail
        ]);

        $this->assertDatabaseMissing('classroom_user',[
            'user_id' => $student->id,
            'classroom_id' => $classroom->id
        ]);

        $this->assertEquals(0, $classroom->students()->count());


        $studentsEmails = [];
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;
        $studentsEmails[] = \App\Models\User::factory()->create()->email;

        $classroom->assingStudents($studentsEmails);

        $this->assertEquals(3, $classroom->students()->count());


        $this->actingAs($userTeacher)
        ->delete(route('classrooms.unassingstudent',$classroom->id),[
            'emails' => $studentsEmails
        ]);

        $this->assertEquals(0, $classroom->students()->count());


    }
}
