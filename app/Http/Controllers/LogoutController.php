<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LogoutController extends Controller
{
    
    public function __invoke(Request $request)
    {
        
        if (!$request->bearerToken()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is required.'
            ], 401);
        }

        try {
            
            $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

            if ($removeToken) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logout Berhasil!',
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to logout. Please try again.',
            ], 500);
        }
    }
}
