<?php

namespace Tests\Feature;

use App\Jobs\AssingExamToClassroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
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

        $this->assertDatabaseCount('exams', 0);
    }

    public function test_teacher_can_create_exams_from_post()
    {
        $teacher = \App\Models\User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
        ->post(route('exams.store'), [
            'title' => 'Exame to test 51',
            'content' => 'some content',
            'open_at' => now(),
            'close_at' => now()->addDay(rand(1,20)),
            'minutes' => rand(3,10),
        ]);

        $this->assertDatabaseHas('exams',[
            'user_id' => $teacher->id,
            'title' => 'Exame to test 51',
            'content' => 'some content',
        ]);

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
        $student = \App\Models\User::factory()->create();
        $studentsEmails[] =  $student->email;

        $classroom->assingStudents($studentsEmails);
        
        $teacher->applyExamToClassroom($exam, $classroom);

        $this->assertDatabaseHas('exam_user', [
            'user_id'  => $student->id,
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
        $student2 = \App\Models\User::factory()->create();
        $classroom->assingStudents($student->email);
        $teacher->applyExamToClassroom($exam, $classroom);


        // use the uuid instead
        $examToDo = $student->examsAvailables()->first();

        $this->withExceptionHandling();
        
        $this->actingAs($student)
        ->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertSuccessful();

        $this->actingAs($student2)
        ->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertForbidden();
    }

    public function test_block_exam_acess_when_is_not_the_date()
    {
        $teacher = \App\Models\User::factory()
        ->has(\App\Models\Classroom::factory())
        ->create(['role'=>'teacher']);

        $exam = \App\Models\Exam::factory()->create([
            'user_id' => $teacher->id,
            'open_at' => '2022-03-29',
            'close_at' => '2022-03-31',
        ]);


        $classroom = $teacher->classrooms()->first();
        $student = \App\Models\User::factory()->create();
        $classroom->assingStudents($student->email);
        $teacher->applyExamToClassroom($exam, $classroom);
        // use the uuid instead
        $examToDo = $student->examsAvailables()->first();

        $this->actingAs($student);

        Carbon::setTestNow('2022-03-28');
        $this->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertForbidden();

        Carbon::setTestNow('2022-03-29');
        $this->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertSuccessful();

        Carbon::setTestNow('2022-03-30');
        $this->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertSuccessful();

        Carbon::setTestNow('2022-03-31');
        $this->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertSuccessful();

        Carbon::setTestNow('2022-04-01');
        $this->get(route('exams.show', $examToDo->pivot->uuid))
        ->assertForbidden();

    }


}
