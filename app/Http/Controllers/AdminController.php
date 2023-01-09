<?php

namespace App\Http\Controllers;

// use Storage;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //change password page
    public function changePasswordPage()
    {
        return view('admin.account.changePassword');
    }

    //change password
    public function changePassword(Request $request)
    {
        // dd($request->all());
        /*
        1. all field must be fill
        2. new password and confirm password length must be greater than 6 and less than 10
        3. new password and confirm password must be the same
        4. old password must be the same with db password
          if old password and new password were the same show message('This is your old password!')
        5. change password
        */
        $this->passwordValitationCheck($request);
        $user = User::select('password')->where('id', Auth::user()->id)->first();
        $dbHashValue = $user->password; //hash value
        if ((Hash::check($request->oldPassword, $dbHashValue)) && (Hash::check($request->newPassword, $dbHashValue))) {
            return back()->with(['samePsw' => 'This is your old password!']);
        } elseif (Hash::check($request->oldPassword, $dbHashValue)) {
            $data = ['password' => Hash::make($request->newPassword)];
            User::where('id', Auth::user()->id)->update($data);
            // Auth::logout();
            return back()->with(['changeSuccess' => 'Password changed..']);
            // return redirect()->route('auth#loginPage');
        }
        return back()->with(['notMatch' => 'The old password do not match. Try Again!']);

        // $hashValue = Hash::make('code lab');
        // if (Hash::check(' lab', $hashValue)) {
        //     dd('correct');
        // } else {
        //     dd('incorrect');
        // }
        // dd($dbPassword);

        // dd($user->toArray());
        // dd('changed psw');
    }

    //direct details page
    public function details()
    {
        return view('admin.account.details');
    }

    //direct edit profile page
    public function edit()
    {
        return view('admin.account.edit');
    }

    //update account info
    public function update($id, Request $request)

    {
        $this->accountValidationCheck($request);
        $data = $this->getUserData($request);
        //for image
        if (request()->hasFile('image')) {
            //1 old image | check and delete | store
            $dbImage = User::where('id', $id)->first();
            $dbImage = $dbImage->image;

            if ($dbImage != null) {
                Storage::delete('public/' . $dbImage);
            }
            // dd($dbImage);
            $fileName = uniqid() . $request->file('image')->getClientOriginalName();
            // dd($fileName);
            $request->file('image')->storeAs('public', $fileName);
            $data['image'] = $fileName;
        }

        User::where('id', Auth::user()->id)->update($data);
        return redirect()->route('admin#details')->with(['UpdateSuccess' => 'Admin Account Updated..']);
    }

    //get user data
    private function getUserData($request)
    {
        return [
            'name' => $request->name,
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'updated_at' => Carbon::now()
        ];
    }

    //account validation Check
    private function accountValidationCheck($request)
    {
        Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'gender' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'image' => 'mimes:jpg,png,jpeg'
        ])->validate();
    }

    //password Valitation check
    private function passwordValitationCheck($request)
    {
        Validator::make($request->all(), [
            'oldPassword' => 'required|min:6|max:10',
            'newPassword' => 'required|min:6|max:10',
            'confirmPassword' => 'required|min:6|max:10|same:newPassword'
        ], [
            'oldPassword.required' => 'Old psw lo ak pr tl'
        ])->validate();
    }

    //
}