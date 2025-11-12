<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function signIn(Request $request)
    {
        $validation = $request->validate([
            'name' => ['required', 'string', 'min:6', 'max:20'],
            'email'      => ['required', 'string', 'email', 'max:50'],
            'password'   => ['required', 'string', 'min:6', 'max:20'],
        ], [
            'name.required' => 'Yêu cầu bạn nhập tên người dùng',
            'name.min' => 'Tên người dùng phải có ít nhất 6 ký tự',
            'name.max' => 'Tên người dùng chỉ có tối đa 20 ký tự',
            'email.required' => 'Yêu cầu bạn nhập email',
            'email.email'    => 'Sai định dạng email',
            'email.max'      => 'Email chỉ có tối đa 50 ký tự',
            'password.required' => 'Yêu cầu bạn nhập mật khẩu',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.max'      => 'Mật khẩu chỉ có tối đa 20 ký tự'
        ]);

        $email = strtolower(trim($validation['email']));
        $username = trim($validation['name']);
        $password = $validation['password'];

        $user = DB::selectOne(
            'SELECT * FROM users WHERE email = ? OR name = ? LIMIT 1',
            [$email, $username]
        );

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại trong hệ thống'
            ], 401);
        }

        if ($user->name !== $username) {
            return response()->json([
                'status' => false,
                'message' => 'Tên người dùng không tồn tại'
            ], 401);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        $credentials = [
            'email'   => $validation['email'],
            'name'   => $validation['name'],
            'password'   => $validation['password'] 
        ];

        
        $token = Auth::guard('api')->attempt($credentials);

        if(!$token){
            return response()->json([
                'Status' => 'Error',
                'Message' => 'Token failed'
            ]);
        }
        // dd($request->all());
        return response()->json([
            'status'       => true,
            'message'      => 'Đăng nhập thành công',
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 2440,
        ]);
    }
}