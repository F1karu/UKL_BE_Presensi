<?php  
namespace App\Http\Controllers;  

use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller 
{          
    public function __invoke(Request $request)     
    {         
                 
        $validator = Validator::make($request->all(), [             
            'username' => 'required',             
            'password' => 'required'         
        ]);          

           
        if ($validator->fails()) {             
            return response()->json($validator->errors(), 422);         
        }          

        $credentials = $request->only('username', 'password');          

        try {
            if (!$token = Auth::guard('api')->attempt($credentials)) {             
                return response()->json([                 
                    'status' => 'error',                 
                    'message' => 'Username atau Password Anda salah'             
                ], 401);         
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }

        $user = Auth::guard('api')->user();

        if ($user->role === 'Siswa') {
            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil.'
            ], 200);
        }
     
        return response()->json([             
            'status' => 'success',             
            'message' => 'Login berhasil',                
            'token' => $token,            
        ], 200);     
    } 
}
