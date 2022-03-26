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

Route::resource('classrooms', ClassRoomController::class);

Route::post('/classrooms/{classroom}/assingstudent', [ClassRoomController::class, 'assingStudent'])
->name('classrooms.assingstudent')->can('update', 'classroom');

Route::delete('/classrooms/{classroom}/assingstudent', [ClassRoomController::class, 'unassingStudent'])
->name('classrooms.unassingstudent')->can('update', 'classroom');

Route::resource('exams', ExamController::class);
