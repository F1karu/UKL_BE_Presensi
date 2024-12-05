<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        
        if (!request()->bearerToken()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is required.'
            ], 401);
        }

        $users = User::all();

        $users->makeHidden(['email', 'email_verified_at', 'created_at', 'updated_at']);

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    /**
     * Get user by ID.
     */
    public function show($id)
    {
        // Cek apakah token ada dalam request
        if (!request()->bearerToken()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is required.'
            ], 401);
        }

        $user = User::find($id);

        $user->makeHidden(['email', 'email_verified_at', 'created_at', 'updated_at']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);

    }
    public function delete($id)
    {
        $delete = User::where('id', $id)->delete();

        if ($delete) {
            return response()->json(['status' => true, 'message' => 'Sukses menghapus user']);
        } else {
            return response()->json(['status' => false, 'message' => 'Gagal menghapus user']);
        }

        if (!request()->bearerToken()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is required.'
            ], 401);
        }

    }
}
