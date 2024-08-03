<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function SmtpSetting()
    {
        $smpt = SmtpSetting::find(1);
        return view('admin.backend.setting.smpt_update', compact('smpt'));
    } // End Method 

    public function SmtpUpdate(Request $request)
    {
        $id = $request->id;
        SmtpSetting::find($id)->update([
            'mailer' =>  $request->mailer,
            'host' =>  $request->host,
            'port' =>  $request->port,
            'username' =>  $request->username,
            'password' =>  $request->password,
            'encryption' =>  $request->encryption,
            'from_address' =>  $request->from_address,
        ]);

        $notification = [
            'message' => "Smtp Setting Updated Successfully",
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    } // End Method
}
