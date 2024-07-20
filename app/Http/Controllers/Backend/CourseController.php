<?php

namespace App\Http\Controllers\Backend;

use App\Models\Course;
use App\Models\Course_goal;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use PHPUnit\Framework\Constraint\Count;

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

    public function StoreCourse(Request $request)
    {
        // dd($request->toArray());
        $request->validate([
            'video' => ['required', 'mimes:mp4', 'max:10000']
        ]);

        $image = $request->file('course_image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        // dd($image->getClientOriginalExtension(), $name_gen);
        Image::make($image)->resize(370, 246)->save('upload/course/thambnail/' . $name_gen);
        $save_url = 'upload/course/thambnail/' . $name_gen;

        $video = $request->file('video');
        $videoName = time() . '.' . $video->getClientOriginalExtension();
        $video->move(public_path('upload/course/video/'), $videoName);
        $save_video = 'upload/course/video/' . $videoName;


        $course_id = Course::insertGetId([
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'instructor_id' => Auth::user()->id,
            'course_title' => $request->course_title,
            'course_name' => $request->course_name,
            'course_name_slug' => trim(strtolower(str_replace(' ', '-', $request->course_name))),
            'description' => $request->description,
            'video' => $save_video,

            'label' => $request->label,
            'duration' => $request->duration,
            'resources' => $request->resources,
            'certificate' => $request->certificate,
            'selling_price' => $request->selling_price,
            'discount_price' => $request->discount_price,
            'prerequisites' => $request->prerequisites,

            'bestseller' => $request->bestseller,
            'featured' => $request->featured,
            'highestrated' => $request->highestrated,
            'status' => 1,
            'course_image' => $save_url,
            'created_at' => Carbon::now(),
        ]);

        $goles = Count($request->course_goals);
        if ($goles != NULL) {
            for ($i = 0; $i < $goles; $i++) {
                $gcount = new Course_goal();
                $gcount->course_id = $course_id;
                $gcount->goal_name = $request->course_goals[$i];
                $gcount->save();
            }
        }

        $notification = [
            'message' => 'Course Inserted Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.course')->with($notification);
    } // End Method

    public function EditCourse($id)
    {
        $course = Course::find($id);
        $categories = Category::latest()->get();
        $subcategories = Subcategory::latest()->get();

        return view('instructor.course.edit_course', compact('course', 'categories', 'subcategories'));
    }

    public function UpdateCourse($id)
    {
    }
}
