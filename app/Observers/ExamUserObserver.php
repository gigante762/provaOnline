<?php

namespace App\Observers;
use App\Models\ExamUser;

use Illuminate\Support\Str;

class ExamUserObserver
{
    /**
     * Handle the ExamUser "created" event.
     *
     * @param  \App\Models\ExamUser  $examUser
     * @return void
     */
    public function created(ExamUser $examUser)
    {
        $examUser->uuid = Str::uuid();
    }
}
