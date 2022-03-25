<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cant_create_exams()
    {
        $userStudent = \App\Models\User::factory()->create();
        
        $this->actingAs($userStudent)
        ->post(route('classrooms.store'), [
            'name' => 'Class test name',
            'user_id' => $userStudent->id,
        ])->assertForbidden();
    }

    public function test_teacher_can_create_classrooms()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $this->actingAs($userTeacher)
        ->post(route('classrooms.store'), [
            'name' => 'Class test name',
            'user_id' => $userTeacher->id,
        ])->assertOk();

    }

    

    public function test_create_classroom_from_user()   
    {
        $user = \App\Models\User::factory()->create();

        \App\Models\Classroom::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertDatabaseCount('classrooms', 1);
    }

    public function test_create_exam_from_user()   
    {
        $user = \App\Models\User::factory()->create();

        \App\Models\Exam::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertDatabaseCount('exams', 1);
    }

    
}
