<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// TODO: php artisan make:controller []
// $controllers = ['AdminController', 'InstructorController', "UserController"];

// foreach ($controllers as $controller) {
//     Artisan::call('make:controller', ['name' => $controller]);
// }

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [UserController::class, 'Index'])->name('index');




Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// TODO Route Admin
Route::middleware(['auth', 'roles:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');
    Route::get('/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/profile/store', [AdminController::class, 'AdminProfileStore'])->name('admin.profile.store');
    Route::get('/change/password', [AdminController::class, 'AdminChangePassword'])->name('admin.change.password');
    Route::post('/password/update', [AdminController::class, 'AdminPasswordUpdate'])->name('admin.password.update');
});
Route::get('/admin/login', [AdminController::class, 'AdminLogin'])->name('admin.login');

// TODO Route Instructor
Route::middleware((['auth', 'roles:instructor']))->prefix('instructor')->group(function () {
    Route::get('/dashboard', [InstructorController::class, 'InstructorDashboard'])->name('instructor.dashboard');
    Route::get('/logout', [InstructorController::class, 'InstructorLogout'])->name('instructor.logout');
    Route::get('/profile', [InstructorController::class, 'InstructorProfile'])->name('instructor.profile');
    Route::post('/profile/store', [InstructorController::class, 'InstructorProfileStore'])->name('instructor.profile.store');
    Route::get('/change/password', [InstructorController::class, 'InstructorChangePassword'])->name('instructor.change.password');
    Route::post('/password/update', [InstructorController::class, 'InstructorPasswordUpdate'])->name('instructor.password.update');
});
Route::get('/instructor/login', [InstructorController::class, 'InstructorLogin'])->name('instructor.login');
