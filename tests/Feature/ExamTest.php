<?php

namespace Tests\Feature;

use App\Jobs\AssingExamToClassroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
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
     * @depens test_teacher_can_create_exams_from_post
     */
    public function test_create_exam_from_user()   
    {
        $user = \App\Models\User::factory()
        ->has(\App\Models\Exam::factory()->count(3))
        ->create();

        $this->assertEquals(3, $user->exams()->count());
        $this->assertDatabaseCount('exams', 3);
    }

    public function test_dispatch_assing_exam_to_classroom_job()   
    {
        Bus::fake();

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

        Bus::assertDispatched(AssingExamToClassroom::class);

    }

    public function test_students_received_exam_to_do()   
    {
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

        $studentExemple = \App\Models\User::where('email', $studentsEmails[0])->first();

        $this->assertDatabaseHas('exam_user', [
            'user_id'  => $studentExemple->id,
            'exam_id' => $exam->id,
        ]);

        $this->assertDatabaseCount('exam_user', 3);
    }

    public function test_student_exam_have_uuid()
    {
        $teacher = \App\Models\User::factory()
        ->has(\App\Models\Exam::factory())
        ->has(\App\Models\Classroom::factory())
        ->create(['role'=>'teacher']);

        $exam = $teacher->exams()->first();

        $classroom = $teacher->classrooms()->first();
     
        $student = \App\Models\User::factory()->create();
        
        $classroom->assingStudents($student->email);
        
        $teacher->applyExamToClassroom($exam, $classroom);

        $this->assertDatabaseHas('exam_user', [
            'user_id'  => $student->id,
            'exam_id' => $exam->id,
        ]);

        $this->assertEquals(1, $student->examsAvailables()->count());

        $this->assertNotEmpty($student->examsAvailables()->first()->pivot->uuid, 'exam_user uuid is null');
    }

    /**
     * @depends test_student_exam_have_uuid
     */
    public function test_student_can_view_their_own_exam()
    {

        $teacher = \App\Models\User::factory()
        ->has(\App\Models\Exam::factory())
        ->has(\App\Models\Classroom::factory())
        ->create(['role'=>'teacher']);

        $exam = $teacher->exams()->first();
        $classroom = $teacher->classrooms()->first();
        $student = \App\Models\User::factory()->create();
        $classroom->assingStudents($student->email);
        $teacher->applyExamToClassroom($exam, $classroom);


        // use the uuid instead
        $examToDo = $student->examsAvailables()->first();

        $this->actingAs($student)
        ->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertOk();



    }

    public function test_student_cant_view_their_non_own_exam()
    {
        $teacher = \App\Models\User::factory()
        ->has(\App\Models\Exam::factory())
        ->has(\App\Models\Classroom::factory())
        ->create(['role'=>'teacher']);

        $exam = $teacher->exams()->first();
        $classroom = $teacher->classrooms()->first();
        $student = \App\Models\User::factory()->create();
        $classroom->assingStudents($student->email);
        $teacher->applyExamToClassroom($exam, $classroom);


        $examToDo = $student->examsAvailables()->first();

        $student2 = \App\Models\User::factory()->create();

        $this->actingAs($student2)
        ->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertForbidden();

    }
}
