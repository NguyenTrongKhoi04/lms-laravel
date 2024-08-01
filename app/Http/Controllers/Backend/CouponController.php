<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CouponController extends Controller
{
    public function AdminAllCoupon()
    {
        $coupon = Coupon::latest()->get();
        return view('admin.backend.coupon.coupon_all', compact('coupon'));
    } // End Method 

    public function AdminAddCoupon()
    {
        return view('admin.backend.coupon.coupon_add');
    } // End Method 

    public function AdminStoreCoupon(Request $request)
    {
        Coupon::insert([
            'coupon_name' => strtoupper($request->coupon_name),
            'coupon_discount' => $request->coupon_discount,
            'coupon_discount' => $request->coupon_discount,
            'coupon_validity' => $request->coupon_validity,
            'created_at' => Carbon::now()
        ]);

        $notification = [
            'message' => 'Coupon Added Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.all.coupon')->with($notification);
    } // End Method

    public function AdminEditCoupon($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.backend.coupon.coupon_edit', compact('coupon'));
    } // End Method

    public function AdminUpdateCoupon(Request $request)
    {
        $id = $request->id;

        Coupon::find($id)->update([
            'coupon_name' => strtoupper($request->coupon_name),
            'coupon_discount' => $request->coupon_discount,
            'coupon_discount' => $request->coupon_discount,
            'coupon_validity' => $request->coupon_validity,
            'updated_at' => Carbon::now()
        ]);

        $notification = [
            'message' => 'Coupon Updated Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.all.coupon')->with($notification);
    } // End Method

    public function AdminDeleteCoupon($id)
    {
        Coupon::find($id)->delete();

        $notification = [
            'message' => 'Coupon Deleted Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    } // End Method
}
