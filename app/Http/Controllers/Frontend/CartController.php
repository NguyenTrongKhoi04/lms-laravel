<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\Orderconfirm;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\SubCategory;
use App\Models\Course;
use App\Models\Course_goal;
use App\Models\CourseSection;
use App\Models\CourseLecture;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Stripe;

class CartController extends Controller
{
    public function AddToCart(Request $request, $id)
    {
        $course = Course::find($id);
        $instructor = User::find($request->instructor)->toArray();
        $cartItem = Cart::search(function ($cartItem, $rowId) use ($id) {
            return $cartItem->id === $id;
        });

        if ($cartItem->isNotEmpty()) {
            return response()->json(['error' => 'Course is already in your cart']);
        }

        if ($course->discount_price == NULL) {

            Cart::add([
                'id' => $id,
                'name' => $request->course_name,
                'qty' => 1,
                'price' => $course->selling_price,
                'weight' => 1,
                'options' => [
                    'image' => $course->course_image,
                    'slug' => $request->course_name_slug,
                    'instructor' => $request->instructor,
                    'instructor_info' => $instructor,
                ],
            ]);
        } else {
            Cart::add([
                'id' => $id,
                'name' => $request->course_name,
                'qty' => 1,
                'price' => $course->discount_price,
                'weight' => 1,
                'options' => [
                    'image' => $course->course_image,
                    'slug' => $request->course_name_slug,
                    'instructor' => $request->instructor,
                    'instructor_info' => $instructor,
                ],
            ]);
        }

        return response()->json(['success' => 'Successfully Added on Your Cart']);
    } // End Method

    public function CartData()
    {
        $carts = Cart::content();
        $cartTotal = Cart::total();
        $cartQty = Cart::count();

        return response()->json(array(
            'carts' => $carts,
            'cartTotal' => $cartTotal,
            'cartQty' => $cartQty,
        ));
    } // End Method 

    public function AddMiniCart()
    {
        $carts = Cart::content();
        $cartTotal = Cart::total();
        $cartQty = Cart::count();

        return response()->json(array(
            'carts' => $carts,
            'cartTotal' => $cartTotal,
            'cartQty' => $cartQty,
        ));
    } // End Method 

    public function CartRemove($rowId)
    {
        Cart::remove($rowId);

        //calculator this cart with coupon
        if (Session::has('coupon')) {
            $coupon_name = Session::get('coupon')['coupon_name'];
            $coupon = Coupon::where('coupon_name', $coupon_name)->first();

            Session::put('coupon', [
                'coupon_name' => $coupon->coupon_name,
                'coupon_discount' => $coupon->coupon_discount,
                'discount_amount' => round(Cart::total() * $coupon->coupon_discount / 100),
                'total_amount' => round(Cart::total() - Cart::total() * $coupon->coupon_discount / 100)
            ]);
        }

        return response()->json(['success' => 'Successfully Remove on Your Cart']);
    } // End Method 

    public function MyCart()
    {
        return view('frontend.mycart.view_mycart');
    } // End Method

    public function GetCartCourse()
    {
        $carts = Cart::content();
        $cartTotal = Cart::total();
        $cartQty = Cart::count();

        return response()->json(array(
            'carts' => $carts,
            'cartTotal' => $cartTotal,
            'cartQty' => $cartQty,
        ));
    } // End Method

    /**
     * =============================================================================
     * * Coupon
     * =============================================================================
     */
    public function CouponApply(Request $request)
    {
        $coupon = Coupon::where('coupon_name', $request->coupon_name)->where('coupon_validity', '>=', Carbon::now()->format('Y-m-d'))->first();

        if ($coupon) {
            Session::put('coupon', [
                'coupon_name' => $coupon->coupon_name,
                'coupon_discount' => $coupon->coupon_discount,
                'discount_amount' => round(Cart::total() * $coupon->coupon_discount / 100),
                'total_amount' => round(Cart::total() - Cart::total() * $coupon->coupon_discount / 100)
            ]);

            return response()->json([
                'validity' => true,
                'success' => 'Coupon Applied Successfully'
            ]);
        } else {
            return response()->json(['error' => 'Invaild Coupon']);
        }
    } // End Method 

    public function CouponCalculation()
    {
        // session()->flush();
        // dd(session()->get('coupon'), Cart::content());
        if (Session::has('coupon')) {
            return response()->json(array(
                'subtotal' => Cart::total(),
                'coupon_name' => session()->get('coupon')['coupon_name'],
                'coupon_discount' => session()->get('coupon')['coupon_discount'],
                'discount_amount' => session()->get('coupon')['discount_amount'],
                'total_amount' => session()->get('coupon')['total_amount'],
            ));
        } else {
            return response()->json(array(
                'total' => Cart::total(),
            ));
        }
    } // End Method 

    public function CouponRemove()
    {
        Session::forget('coupon');
        return response()->json(['Success' => 'Coupon Remove Successfully']);
    } // End Method

    public function CheckoutCreate()
    {
        if (Auth::check()) {
            if (Cart::total() > 0) {
                $carts = Cart::content();
                $cartTotal = Cart::total();
                $cartQty = Cart::count();

                return view('frontend.checkout.checkout_view', compact('carts', 'cartTotal', 'cartQty'));
            }

            $notification = array(
                'message' => 'Add At list One Course',
                'alert-type' => 'error'
            );
            return redirect()->to('/')->with($notification);
        }

        $notification = [
            'message' => 'You Need to Login',
            'alert-type' => 'error'
        ];

        return redirect()->back()->with($notification);
    } // End Method

    public function Payment(Request $request)
    {
        if (Session::has('coupon')) {
            $total_amount = Session::get('coupon')['total_amount'];
        } else {
            $total_amount = round(Cart::total());
        }

        $data = array();
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['phone'] = $request->phone;
        $data['address'] = $request->address;
        $data['course_title'] = $request->course_title;
        $cartTotal = Cart::total();
        $carts = Cart::content();

        if ($request->cash_delivery == 'stripe') {
            return view('frontend.payment.stripe', compact('data', 'cartTotal', 'carts'));
        } elseif ($request->cash_delivery == 'handcash') {
            do {
                $invoice_no = 'EOS' . mt_rand(10000000, 99999999);
            } while (Payment::where('invoice_no', $invoice_no)->exists());

            // Add Payment To DB
            $data = new Payment();
            $data->name = $request->name;
            $data->email = $request->email;
            $data->phone = $request->phone;
            $data->address = $request->address;
            $data->cash_delivery = $request->cash_delivery;
            $data->total_amount = $total_amount;
            $data->payment_type = 'Direct Payment';
            $data->invoice_no = $invoice_no;
            $data->order_date = Carbon::now()->format('d F Y');
            $data->order_month = Carbon::now()->format('F');
            $data->order_year = Carbon::now()->format('F');
            $data->status = 'pending';
            $data->save();

            // Add Orders to DB
            foreach ($request->course_title as $key => $course_title) {

                $existingOrder = Order::where('user_id', Auth::user()->id)->where('course_id', $request->course_id[$key])->exists();

                if ($existingOrder) {
                    $notification = [
                        "message" => 'You have already ' . Course::whereId($request->course_id)->value('course_name') . 'in this course',
                        "alert-type" => "error"
                    ];
                    return redirect()->back()->with($notification);
                }

                $order = new Order();
                $order->payment_id = $data->id;
                $order->user_id = Auth::user()->id;
                $order->course_id = $request->course_id[$key];
                $order->instructor_id = $request->instructor_id[$key];
                $order->course_title = $course_title;
                $order->price = $request->price[$key];
                $order->save();
            }

            $request->session()->forget(['cart', 'coupon']);

            $paymentId = $data->id;
            $sendmail = Payment::find($paymentId);
            $data = [
                'invoice_no' => $sendmail->invoice_no,
                'amount' => $total_amount,
                'name' => $sendmail->name,
                'email' => $sendmail->email,
            ];

            Mail::to($request->email)->send(new Orderconfirm($data));

            $notification = [
                'message' => 'Cash Payment Submit Successfully',
                'alert-type' => 'success'
            ];
            return redirect()->route('index')->with($notification);
        } // End Elseif 
    } // End Method

    public function BuyToCart(Request $request, $id)
    {
        $course = Course::find($id);
        $cartItem = Cart::search(function ($cartItem, $rowId) use ($id) {
        });

        if ($cartItem->isNotEmpty()) {
            return response()->json(['error' => 'Course is already in your cart']);
        }

        Cart::add([
            'id' => $id,
            'name' => $request->course_name,
            'qty' => 1,
            'price' => $course->discount_price ?? $course->selling_price,
            'weight' => 1,
            'options' => [
                'image' => $course->course_image,
                'slug' => $request->course_name_slug,
                'instructor' => $request->instructor,
            ],
        ]);

        return response()->json(['success' => 'Successfully Added on Your Cart']);
    } // End Method

    public function StripeOrder(Request $request)
    {
        if (Session::has('coupon')) {
            $total_amount = Session::get('coupon')['total_amount'];
        } else {
            $total_amount = round(Cart::total());
        }

        \Stripe\Stripe::setApiKey('sk_test_51IUTWzALc6pn5BvMjaRW9STAvY4pLiq1dNViHoh5KtqJc9Bx7d4WKlCcEdHOJdg3gCcC2F19cDxUmCBJekGSZXte00RN2Fc4vm');

        $token = $_POST['stripeToken'];

        $charge = \Stripe\Charge::create([
            'amount' => $total_amount * 100,
            'currency' => 'usd',
            'description' => 'Lms',
            'source' => $token,
            'metadata' => ['order_id' => '3434'],
        ]);

        $order_id = Payment::insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'total_amount' => $total_amount,
            'payment_type' => 'Stripe',
            'invoice_no' => 'EOS' . mt_rand(10000000, 99999999),
            'order_date' => Carbon::now()->format('d F Y'),
            'order_month' => Carbon::now()->format('F'),
            'order_year' => Carbon::now()->format('Y'),
            'status' => 'pending',
            'created_at' => Carbon::now(),
        ]);

        $carts = Cart::content();
        foreach ($carts as $cart) {
            Order::insert([
                'payment_id' => $order_id,
                'user_id' => Auth::user()->id,
                'course_id' => $cart->id,
                'instructor_id' => $cart->options->instructor,
                'course_title' => $cart->options->name,
                'price' => $cart->price,
            ]);
        } // end foreach 

        if (Session::has('coupon')) {
            Session::forget('coupon');
        }
        Cart::destroy();

        $notification = array(
            'message' => 'Stripe Payment Submit Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('index')->with($notification);
    } // End Method 

    public function InsCouponApply(Request $request)
    {
        $coupon = Coupon::where('coupon_name', $request->coupon_name)->where('coupon_validity', '>=', Carbon::now()->format('Y-m-d'))->first();

        if ($coupon) {
            if ($coupon->course_id == $request->course_id && $coupon->instructor_id == $request->instructor_id) {

                Session::put('coupon', [
                    'coupon_name' => $coupon->coupon_name,
                    'coupon_discount' => $coupon->coupon_discount,
                    'discount_amount' => round(Cart::total() * $coupon->coupon_discount / 100),
                    'total_amount' => round(Cart::total() - Cart::total() * $coupon->coupon_discount / 100)
                ]);

                return response()->json(array(
                    'validity' => true,
                    'success' => 'Coupon Applied Successfully'
                ));
            } else {
                return response()->json(['error' => 'Coupon Criteria Not Met for this course and instructor']);
            }
        } else {
            return response()->json(['error' => 'Invalid Coupon']);
        }
    } // End Method 

}
