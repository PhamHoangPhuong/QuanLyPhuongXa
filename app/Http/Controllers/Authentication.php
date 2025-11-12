<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class Authentication extends Controller
{
    public function verifyToken(string $token)
    {
        try {
            // Nếu token hợp lệ → trả về payload dạng mảng
            $payload = JWTAuth::setToken($token)->getPayload();
            $secretKey = config('jwt.secret'); //<- php artisan config:clear
            //dd($secretKey)
            if (empty($secretKey)) {
                return false; // không có secret key
            }
            return $payload->toArray();
        } catch (TokenExpiredException $e) {
            return false; // Token hết hạn
        } catch (TokenInvalidException $e) {
            return false; // Token sai
        } catch (JWTException $e) {
            return false; // Không có token hoặc lỗi parse
        }
    }
}
