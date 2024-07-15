<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\CategoryController;

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



// TODO Route User
Route::get('/dashboard', function () {
    return view('frontend.dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/profile', 'UserProfile')->name('user.profile');
        Route::post('/profile/update', 'UserProfileUpdate')->name('user.profile.update');
        Route::get('/logout', 'UserLogout')->name('user.logout');
        Route::get('/change/password', 'UserChangePassword')->name('user.change.password');
        Route::post('/password/update', 'UserPasswordUpdate')->name('user.password.update');
    });
});

require __DIR__ . '/auth.php';

// TODO Route Admin
Route::middleware(['auth', 'roles:admin'])->group(function () {
    // dashboard
    Route::prefix('admin')->controller(AdminController::class)->group(function () {
        Route::get('/dashboard', 'AdminDashboard')->name('admin.dashboard');
        Route::get('/logout', 'AdminLogout')->name('admin.logout');
        Route::get('/profile', 'AdminProfile')->name('admin.profile');
        Route::post('/profile/store', 'AdminProfileStore')->name('admin.profile.store');
        Route::get('/change/password', 'AdminChangePassword')->name('admin.change.password');
        Route::post('/password/update', 'AdminPasswordUpdate')->name('admin.password.update');
    });

    // Category
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/all/category', 'AllCategory')->name('all.category');
        Route::get('/add/category', 'AddCategory')->name('add.category');
        Route::post('/store/category', 'StoreCategory')->name('store.category');
        Route::get('/edit/category/{id}', 'EditCategory')->name('edit.category');
        Route::post('/update/category', 'UpdateCategory')->name('update.category');
        Route::get('/delete/category/{id}', 'DeleteCategory')->name('delete.category');
        Route::get('/all/subcategory', 'AllSubCategory')->name('all.subcategory');

        // SubCategory
        Route::get('/all/category', 'AllCategory')->name('all.category');
        Route::get('/add/subcategory', 'AddSubCategory')->name('add.subcategory');
        Route::post('/store/subcategory', 'StoreSubCategory')->name('store.subcategory');
        Route::get('/edit/subcategory/{id}', 'EditSubCategory')->name('edit.subcategory');
        Route::post('/update/subcategory', 'UpdateSubCategory')->name('update.subcategory');
        Route::get('/delete/subcategory/{id}', 'DeleteSubCategory')->name('delete.subcategory');
    });
});
Route::get('/admin/login', [AdminController::class, 'AdminLogin'])->name('admin.login');

// TODO Route Instructor
Route::middleware(['auth', 'roles:instructor'])->group(function () {
    Route::prefix('instructor')->controller(InstructorController::class)->group(function () {
        Route::get('/dashboard', 'InstructorDashboard')->name('instructor.dashboard');
        Route::get('/logout', 'InstructorLogout')->name('instructor.logout');
        Route::get('/profile', 'InstructorProfile')->name('instructor.profile');
        Route::post('/profile/store', 'InstructorProfileStore')->name('instructor.profile.store');
        Route::get('/change/password', 'InstructorChangePassword')->name('instructor.change.password');
        Route::post('/password/update', 'InstructorPasswordUpdate')->name('instructor.password.update');
    });
});
Route::get('/instructor/login', [InstructorController::class, 'InstructorLogin'])->name('instructor.login');