<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AttendanceController extends Controller
{
    public function hadir(Request $request)
    {

        if (!request()->bearerToken()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is required.'
            ], 401);
        }

        
        $request->validate([
            'user_id' => 'required|exists:users,id', 
            'date'    => 'required|date', 
            'time'    => 'required|date_format:H:i:s', 
            'status'  => 'required|in:hadir,izin,sakit', 
        ]);

        
        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'date'    => $request->date,
            'time'    => $request->time,
            'status'  => $request->status,
        ]);

        $attendance->makeHidden(['created_at', 'updated_at']);

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi berhasil dicatat',
            'data'    => $attendance
        ], 201);
    }

    public function history($user_id)
    {
        $attendances = Attendance::where('user_id', $user_id)->get();

        if (!request()->bearerToken()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is required.'
            ], 401);
        }

        if ($attendances->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance records found for this user.'
            ], 404);
        }

        $attendances->makeHidden(['created_at', 'updated_at']);

        return response()->json([
            'status' => 'success',
            'data'    => $attendances
        ], 200);
    }

    public function summary($user_id)
{
    
    $attendanceRecords = Attendance::where('user_id', $user_id)->get();

    
    $attendanceGroupedByMonth = $attendanceRecords->groupBy(function($date) {
        return Carbon::parse($date->date)->format('m-Y'); // Group by MM-YYYY
    });

    $summary = [];

    foreach ($attendanceGroupedByMonth as $monthYear => $records) {

        $hadir = $records->where('status', 'hadir')->count();
        $izin = $records->where('status', 'izin')->count();
        $sakit = $records->where('status', 'sakit')->count();

        $summary[] = [
            'month' => $monthYear,
            'attendance_summary' => [
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
            ],
        ];
    }

    
    
    if (!request()->bearerToken()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token is required.'
        ], 401);
    }
    
    
    return response()->json([
        'status' => 'success',
        'data' => [
            'user_id' => $user_id,
            'attendance_summary_by_month' => $summary
            ]
        ], 200);
    }
    
    //     public function summary($user_id, $monthYear)
    // {
    //     // Ambil data presensi untuk pengguna pada bulan dan tahun tertentu
    //     $attendanceSummary = Attendance::where('user_id', $user_id)
    //         ->whereMonth('date', Carbon::parse($monthYear)->month)
    //         ->whereYear('date', Carbon::parse($monthYear)->year)
    //         ->get();
    
    //     // Hitung jumlah kehadiran berdasarkan status
    //     $hadir = $attendanceSummary->where('status', 'hadir')->count();
    //     $izin = $attendanceSummary->where('status', 'izin')->count();
    //     $sakit = $attendanceSummary->where('status', 'sakit')->count();
    
    //     // Menyusun hasil rekap
    //     $summary = [
    //         'user_id' => $user_id,
    //         'month' => $monthYear,
    //         'attendance_summary' => [
    //             'hadir' => $hadir,
    //             'izin' => $izin,
    //             'sakit' => $sakit,
    //         ],
    //     ];
    
    //     // Mengembalikan response JSON
    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $summary
    //     ], 200);
    // }
    
public function analysis(Request $request)
{
    if (!request()->bearerToken()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token is required.'
        ], 401);
    }

    
    $validated = $request->validate([
        'start_date' => 'required|date_format:Y-m-d',
        'end_date'   => 'required|date_format:Y-m-d',
        'group_by'   => 'required|in:Siswa,Karyawan',
    ]);

    
    $startDate = Carbon::parse($validated['start_date']);
    $endDate = Carbon::parse($validated['end_date']);

    $users = User::where('role', $validated['group_by'])->get();

    $totalUsers = $users->count();
    $totalHadir = 0;
    $totalIzin = 0;
    $totalSakit = 0;
    $totalAlpha = 0;

    
    foreach ($users as $user) {
        
        $attendanceRecords = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        
        $totalHadir += $attendanceRecords->where('status', 'hadir')->count();
        $totalIzin += $attendanceRecords->where('status', 'izin')->count();
        $totalSakit += $attendanceRecords->where('status', 'sakit')->count();
        $totalAlpha += $attendanceRecords->where('status', 'alpha')->count();
    }

    
    $totalAttendance = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;
    $hadirPercentage = $totalAttendance > 0 ? round(($totalHadir / $totalAttendance) * 100, 2) : 0;
    $izinPercentage = $totalAttendance > 0 ? round(($totalIzin / $totalAttendance) * 100, 2) : 0;
    $sakitPercentage = $totalAttendance > 0 ? round(($totalSakit / $totalAttendance) * 100, 2) : 0;
    $alphaPercentage = $totalAttendance > 0 ? round(($totalAlpha / $totalAttendance) * 100, 2) : 0;

    
    $groupedAnalysis = [
        'group' => $validated['group_by'], 
        'total_users' => $totalUsers,
        'attendance_rate' => [
            'hadir_percentage' => $hadirPercentage,
            'izin_percentage' => $izinPercentage,
            'sakit_percentage' => $sakitPercentage,
            'alpha_percentage' => $alphaPercentage,
        ],
        'total_attendance' => [
            'hadir' => $totalHadir,
            'izin' => $totalIzin,
            'sakit' => $totalSakit,
            'alpha' => $totalAlpha,
        ],
    ];

    
    return response()->json([
        'status' => 'success',
        'data' => [
            'analysis_period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'grouped_analysis' => [$groupedAnalysis], 
        ]
    ], 200);
}


}



