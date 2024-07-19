<?php

namespace App\Http\Controllers\Backend;

use App\Models\Course;
use App\Models\Course_goal;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function AllCourse()
    {
        $id = Auth::user()->id;
        $courses = Course::where('instructor_id', $id)->latest()->get();
        return view('instructor.course.all_course', compact('courses'));
    } // End Method

    public function AddCourse()
    {
        $categories = Category::all();
        return view('instructor.course.add_course', compact('categories'));
    } // End Method

    public function GetSubCategory($category_id)
    {
        $subcat = Category::find($category_id)->subcategories;
        // $subcat = SubCategory::where('category_id', $category_id)->orderBy('subcategory_name', 'ASC')->get();
        return json_encode($subcat);
    } // End Method
}