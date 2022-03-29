<?php

namespace App\Jobs;

use App\Models\Classroom;
use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Str;

class AssingExamToClassroom implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $exam, $classroom;

    public function __construct(Exam $exam, Classroom $classroom)
    {
        $this->exam = $exam;
        $this->classroom = $classroom;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // it could send an email for eache user
        
        foreach ($this->classroom->students as $student) {
            $exam = $student->examsAvailables()->attach($this->exam->id, ['uuid' => Str::uuid()]);
        }
    }
}
