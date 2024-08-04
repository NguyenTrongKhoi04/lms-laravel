<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Question;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function AdminPendingOrder()
    {
        $payment = Payment::where('status', 'pending')->orderBy('id', 'DESC')->get();
        return view('admin.backend.orders.pending_orders', compact('payment'));
    } // End Method

    public function AdminOrderDetails($payment_id)
    {
        $payment = Payment::where('id', $payment_id)->first();
        $orderItem = Order::where('payment_id', $payment_id)->orderBy('id', 'DESC')->get();

        return view('admin.backend.orders.admin_order_details', compact('payment', 'orderItem'));
    } // End Method

    public function PendingToConfirm($payment_id)
    {
        Payment::find($payment_id)->update(['status' => 'confirm']);

        $notification = [
            'message' => 'Order Confrim Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.confirm.order')->with($notification);
    } // End Method

    public function AdminConfirmOrder()
    {
        $payment = Payment::where('status', 'confirm')->orderBy('id', 'DESC')->get();
        return view('admin.backend.orders.confirm_orders', compact('payment'));
    } // End Method

    public function InstructorAllOrder()
    {
        $id = Auth::user()->id;
        $orderItem1 = Order::where('instructor_id', $id)->orderBy('id', 'desc')->get();

        $latestOrderItem = Order::where('instructor_id', $id)->select('payment_id', \DB::raw('MAX(id) as max_id'))->groupBy('payment_id');

        $orderItem = Order::joinSub($latestOrderItem, 'latest_order', function ($join) {
            $join->on('orders.id', '=', 'latest_order.max_id');
        })->orderBy('latest_order.max_id', 'DESC')->get();

        // dd($orderItem1->toArray(), $orderItem->toArray());

        return view('instructor.orders.all_orders', compact('orderItem'));
    } // End Method 

    public function InstructorOrderDetails($payment_id)
    {
        $payment = Payment::find($payment_id)->first();
        $orderItem = Order::where('payment_id', $payment_id)->orderBy('id', 'DESC')->get();

        return view('instructor.orders.instructor_order_details', compact('payment', 'orderItem'));
    } // End Method

    public function InstructorOrderInvoice($payment_id)
    {
        $payment = Payment::where('id', $payment_id)->first();
        $orderItem = Order::where('payment_id', $payment_id)->orderBy('id', 'DESC')->get();

        $pdf = Pdf::loadView('instructor.orders.order_pdf', compact('payment', 'orderItem'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);

        return $pdf->download('invoice.pdf');
    } // End Method 

    public function MyCourse()
    {
        $id = Auth::user()->id;

        // Xử lý trường hợp mua 1 khóa học nhiều lần
        // Lấy id của đơn hàng mua gần nhât
        $latestOrders = Order::where('user_id', $id)->select('course_id', \DB::raw('MAX(id) as max_id'))->groupBy('course_id');

        // lấy các thông tin còn lại dựa vào id của đơn hàng mới nhất
        $myCourse = Order::joinSub($latestOrders, 'latest_order', function ($join) {
            $join->on('orders.id', '=', 'latest_order.max_id');
        })->orderBy('latest_order.max_id', 'DESC')->get();

        // dd($myCourse->toArray());
        return view('frontend.mycourse.my_all_course', compact('myCourse'));
    } // End Method

    public function CourseView($course_id)
    {
        $id = Auth::user()->id;

        $course = Order::where('course_id', $course_id)->where('user_id', $id)->first();
        $section = CourseSection::where('course_id', $course_id)->orderBy('id', 'asc')->get();
        $allquestion = Question::latest()->get();

        return view('frontend.mycourse.course_view', compact('course', 'section', 'allquestion'));
    } // End Method 

}
