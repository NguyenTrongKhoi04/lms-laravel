<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Course_goal;
use App\Models\Category;
use App\Models\CourseSection;
use App\Models\CourseLecture;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class IndexController extends Controller
{
    public function CourseDetails($id, $slug)
    {
        $course = Course::find($id);
        $goals = Course_goal::where('course_id', $id)->orderBy('id', 'DESC')->get();

        $ins_id = $course->instructor_id;
        $instructorCourses = Course::where('instructor_id', $ins_id)->orderBy('id', "DESC")->get();
        // Course::with(['category', 'subcategory', 'user'])->find($id); //Eager Loading

        $categories = Category::latest()->get();
        $cat_id = $course->category_id;
        $relatedCourses = Course::where('category_id', $cat_id)->where('id', '!=', $id)->orderBy('id', 'DESC')->limit(3)->get();

        return view('frontend.course.course_details', compact('course', 'goals', 'instructorCourses', 'categories', 'relatedCourses'));
    } // End Method
}