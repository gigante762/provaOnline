<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cant_create_exams_from_post()
    {
        $userStudent = \App\Models\User::factory()->create();
        
        $this->actingAs($userStudent)
        ->post(route('exams.store'), [
            'title' => 'Exame '.rand(0,1000),
            'content' => 'some content',
            'open_at' => now(),
            'close_at' => now()->addDay(rand(1,20)),
            'minutes' => rand(3,10),
        ])->assertForbidden();
    }

    public function test_teacher_can_create_exams_from_post()
    {
        $userTeacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $this->actingAs($userTeacher)
        ->post(route('exams.store'), [
            'title' => 'Exame '.rand(0,1000),
            'content' => 'some content',
            'open_at' => now(),
            'close_at' => now()->addDay(rand(1,20)),
            'minutes' => rand(3,10),
        ])->assertOk();

    }

    /**
     * @depens test_teacher_can_create_exams
     */
    public function test_create_exam_from_user()   
    {
        $user = \App\Models\User::factory()->create();

        \App\Models\Exam::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertDatabaseCount('exams', 1);
    }
}
