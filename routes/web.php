<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\CourseController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\QuestionController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\WishListController;
use App\Http\Controllers\Backend\SettingController;

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


// TODO Route Public
//? route '/login' có sẵn do dùng thư viện laravel beeze
Route::get('/', [UserController::class, 'Index'])->name('index');
Route::get('/admin/login', [AdminController::class, 'AdminLogin'])->name('admin.login');
Route::get('/become/instructor', [AdminController::class, 'BecomeInstructor'])->name('become.instructor');
Route::post('/instructor/register', [AdminController::class, 'InstructorRegister'])->name('instructor.register');
Route::get('/instructor/login', [InstructorController::class, 'InstructorLogin'])->name('instructor.login');

Route::controller(IndexController::class)->group(function () {
    Route::get('/course/details/{id}/{slug}', 'CourseDetails');
    Route::get('/category/{id}/{slug}', 'CategoryCourse');
    Route::get('/subcategory/{id}/{slug}', 'SubCategoryCourse');
    Route::get('/instructor/details/{id}', 'InstructorDetails')->name('instructor.details');
});

Route::post('/add-to-wishlist/{course_id}', [WishListController::class, 'AddToWishList']);

// cart
Route::controller(CartController::class)->group(function () {
    Route::post('/cart/data/store/{id}', 'AddToCart');
    Route::get('/cart/data/', 'CartData');
    Route::get('/course/mini/cart/', 'AddMiniCart');
    Route::get('/minicart/course/remove/{rowId}', 'CartRemove');
    Route::get('/mycart', 'MyCart')->name('mycart');
    Route::get('/get-cart-course', 'GetCartCourse');
    Route::post('/coupon-apply', 'CouponApply');
    Route::get('/coupon-calculation', 'CouponCalculation');
    Route::get('/coupon-remove', 'CouponRemove');
    Route::post('/buy/data/store/{id}', 'BuyToCart');

    // checkout
    Route::get('/checkout', 'CheckoutCreate')->name('checkout');
    Route::post('/payment', [CartController::class, 'Payment'])->name('payment');
});


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
    // Wishlist
    Route::controller(WishListController::class)->group(function () {
        Route::get('user/wishlist', 'AllWishlist')->name('user.wishlist');
        Route::get('/get-wishlist-course/', 'GetWishlistCourse');
        Route::get('/wishlist-remove/{id}', 'RemoveWishlist');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('/my/course', 'MyCourse')->name('my.course');
        Route::get('/course/view/{course_id}', 'CourseView')->name('course.view');
    });

    // User Question All Route 
    Route::controller(QuestionController::class)->group(function () {
        Route::post('/user/question', 'UserQuestion')->name('user.question');
    });
});
require __DIR__ . '/auth.php';

// TODO Route Admin
Route::middleware(['auth', 'roles:admin'])->group(function () {
    // dashboard
    Route::controller(AdminController::class)->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', 'AdminDashboard')->name('admin.dashboard');
            Route::get('/logout', 'AdminLogout')->name('admin.logout');
            Route::get('/profile', 'AdminProfile')->name('admin.profile');
            Route::post('/profile/store', 'AdminProfileStore')->name('admin.profile.store');
            Route::get('/change/password', 'AdminChangePassword')->name('admin.change.password');
            Route::post('/password/update', 'AdminPasswordUpdate')->name('admin.password.update');
            // course
            Route::get('/all/course', 'AdminAllCourse')->name('admin.all.course');
            Route::post('/update/course/stauts', 'UpdateCourseStatus')->name('update.course.stauts');
            Route::get('/course/details/{id}', 'AdminCourseDetails')->name('admin.course.details');
        });

        Route::get('/all/instructor', 'AllInstructor')->name('all.instructor');
        Route::post('/update/user/status', 'UpdateUserStatus')->name('update.user.status');
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

    // Coupon 
    Route::controller(CouponController::class)->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/all/coupon', 'AdminAllCoupon')->name('admin.all.coupon');
            Route::get('/add/coupon', 'AdminAddCoupon')->name('admin.add.coupon');
            Route::post('/store/coupon', 'AdminStoreCoupon')->name('admin.store.coupon');
            Route::get('/edit/coupon/{id}', 'AdminEditCoupon')->name('admin.edit.coupon');
            Route::post('/update/coupon', 'AdminUpdateCoupon')->name('admin.update.coupon');
            Route::get('/delete/coupon/{id}', 'AdminDeleteCoupon')->name('admin.delete.coupon');
        });
    });

    // Setting
    Route::controller(SettingController::class)->group(function () {
        Route::get('/smtp/setting', 'SmtpSetting')->name('smtp.setting');
        Route::post('/update/smtp', 'SmtpUpdate')->name('update.smtp'); //! shouldn't update smtp
    });

    // Order
    Route::controller(OrderController::class)->group(function () {
        Route::get('/admin/pending/order', 'AdminPendingOrder')->name('admin.pending.order');
        Route::get('/admin/order/details/{id}', 'AdminOrderDetails')->name('admin.order.details');
        Route::get('/pending-confirm/{id}', 'PendingToConfirm')->name('pending-confirm');

        Route::get('/admin/confirm/order', 'AdminConfirmOrder')->name('admin.confirm.order');
    });
});

// TODO Route Instructor
Route::middleware(['auth', 'roles:instructor'])->group(function () {
    Route::controller(InstructorController::class)->group(function () {
        Route::prefix('instructor')->group(function () {
            Route::get('/dashboard', 'InstructorDashboard')->name('instructor.dashboard');
            Route::get('/logout', 'InstructorLogout')->name('instructor.logout');
            Route::get('/profile', 'InstructorProfile')->name('instructor.profile');
            Route::post('/profile/store', 'InstructorProfileStore')->name('instructor.profile.store');
            Route::get('/change/password', 'InstructorChangePassword')->name('instructor.change.password');
            Route::post('/password/update', 'InstructorPasswordUpdate')->name('instructor.password.update');
        });

        Route::get('/all/course', 'AllCourse')->name('all.course');
    });

    // course
    Route::controller(CourseController::class)->group(function () {
        Route::get('/all/course', 'AllCourse')->name('all.course');
        Route::get('/add/course', 'AddCourse')->name('add.course');

        Route::get('/subcategory/ajax/{category_id}', 'GetSubCategory');
        Route::post('/store/course', 'StoreCourse')->name('store.course');

        Route::get('/edit/course/{id}', 'EditCourse')->name('edit.course');
        Route::post('/update/course', 'UpdateCourse')->name('update.course');
        Route::post('/update/course/image', 'UpdateCourseImage')->name('update.course.image');
        Route::post('/update/course/video', 'UpdateCourseVideo')->name('update.course.video');
        Route::post('/update/course/goal', 'UpdateCourseGoal')->name('update.course.goal');
        Route::get('/delete/course/{id}', 'DeleteCourse')->name('delete.course');

        Route::get('/add/course/lecture/{id}', 'AddCourseLecture')->name('add.course.lecture');
        Route::post('/add/course/section/', 'AddCourseSection')->name('add.course.section');

        Route::post('/save-lecture/', 'SaveLecture')->name('save-lecture');
        Route::get('/edit/lecture/{id}', 'EditLecture')->name('edit.lecture');
        Route::post('/update/course/lecture', 'UpdateCourseLecture')->name('update.course.lecture');
        Route::get('/delete/lecture/{id}', 'DeleteLecture')->name('delete.lecture');
        Route::post('/delete/section/{id}', 'DeleteSection')->name('delete.section');
    });

    // Order
    Route::controller(OrderController::class)->group(function () {
        Route::get('/instructor/all/order', 'InstructorAllOrder')->name('instructor.all.order');
        Route::get('/instructor/order/details/{payment_id}', 'InstructorOrderDetails')->name('instructor.order.details');
        Route::get('/instructor/order/invoice/{payment_id}', 'InstructorOrderInvoice')->name('instructor.order.invoice');
    });

    // Question
    Route::controller(QuestionController::class)->group(function () {
        Route::get('/instructor/all/question', 'InstructorAllQuestion')->name('instructor.all.question');
        Route::get('/question/details/{id}', 'QuestionDetails')->name('question.details');
        Route::post('/instructor/replay', 'InstructorReplay')->name('instructor.replay');
    });
});
