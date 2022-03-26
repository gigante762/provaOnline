<?php

use App\Http\Controllers\{
    ClassRoomController,
    ExamController
};

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::resource('classrooms', ClassRoomController::class);

Route::prefix('/classrooms')->group(function(){
    Route::post('/', [ClassRoomController::class,'store'])->name('classrooms.store')->can('create','App\Models\Classroom');
    Route::get('/', [ClassRoomController::class,'index'])->name('classrooms.index');
    Route::get('/{classroom}', [ClassRoomController::class,'show'])->name('classrooms.show');
    Route::delete('/{classroom}', [ClassRoomController::class,'delete'])->name('classrooms.delete');
    
    Route::post('/{classroom}/assingstudent', [ClassRoomController::class, 'assingStudent'])
    ->name('classrooms.assingstudent')->can('update', 'classroom');

    Route::delete('/classrooms/{classroom}/assingstudent', [ClassRoomController::class, 'unassingStudent'])
    ->name('classrooms.unassingstudent')->can('update', 'classroom');
});





//Route::resource('exams', ExamController::class);

Route::prefix('/exams')->group(function(){
    Route::post('/',[ExamController::class,'store'])->name('exams.store')->can('create','App\Models\Exam');
    Route::get('/{exam}',[ExamController::class,'show'])->name('exams.show')->can('viewExam','exam');
});
