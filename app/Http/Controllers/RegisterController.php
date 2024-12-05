<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;


class RegisterController extends Controller
{
    
    public function __invoke(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'nama'      => 'required|max:255',
            'username'  => 'required|max:255|unique:users',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'required|'
        ]);

        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        
        $user = User::create([
            'nama'      => $request->nama,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password), 
            'role'      => $request->role,
        ]);

        $user->makeHidden(['email', 'created_at', 'updated_at']);

        if (!request()->bearerToken()) {
            return Response::json([
                'status' => 'error',
                'message' => 'Token is required.'
            ], 401);
        }

        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'Pengguna berhasil ditambahkan',
                'data'    => $user,  
            ], 201);
        }

        return response()->json([
            'success' => false,
        ], 409);
    }

    public function update(Request $request, $id)
    {
        
        $request->validate([
            'nama'     => 'sometimes|required',
            'username' => 'sometimes|required|unique:users,username,' . $id,
            'email'    => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|min:8|confirmed',
            'role'     => 'sometimes|required'
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->makeHidden(['email', 'email_verified_at', 'created_at', 'updated_at']);

        $user->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna berhasil diubah',
            'data'    => $user
        ]);
    }
}
