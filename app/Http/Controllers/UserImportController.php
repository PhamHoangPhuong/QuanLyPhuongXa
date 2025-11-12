<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Jobs\ProcessImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class UserImportController extends Controller
{
    public function importQueue(Request $request)
    {
        $file = $request->file('file');

        if(!$file){
            return response()->json(['message' => 'Yêu cầu gửi file'], 400);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        $validExtensions = ['xlsx', 'xls', 'csv'];

        if (!in_array($extension, $validExtensions)) {
            return response()->json(['message' => 'File không đúng định dạng Excel'], 400);
        }

        $path = $file->store('imports');

        $now = now();


        $id = Auth::guard('api')->user()->ward_id;
        // dd($id);

        $sessionCode = (string) Str::uuid();

        DB::statement("INSERT INTO import_sessions (session_code, ward_id, status, result, total_success, total_fail, queued_at) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $sessionCode,
            $id,
            'Đang chờ xử lý',
            null,
            0,
            0,
            $now,
        ]);

        // Lấy session vừa tạo để truyền vào job
        $sessionRecord = DB::select("SELECT * FROM import_sessions WHERE session_code = ? LIMIT 1", [$sessionCode]);
        if (!$sessionRecord) {
            return response()->json(['message' => 'Không thể tạo phiên import'], 500);
        }
        $session = $sessionRecord[0];

        
        
        ProcessImportJob::dispatch($session->id, $path, $id);
    
        return response()->json([
            'message'      => 'File đã được đẩy vào queue',
            'session_codes' => $session->session_code,
        
        ]);
    }

    public function delete($session_id)
    {
        $deletedUserImport = DB::delete("
            DELETE FROM usersimport WHERE session_id = ?
        ", [$session_id]);

        $deletedImportSessions = DB::delete("
            DELETE FROM import_sessions WHERE id = ?
        ", [$session_id]);

        $response = [];

        if ($deletedUserImport > 0) {
            $response[] = "Đã xóa $deletedUserImport dòng từ usersimport";
        } else {
            $response[] = "Không tìm thấy bản ghi nào trong usersimport";
        }

        if ($deletedImportSessions > 0) {
            $response[] = "Đã xóa $deletedImportSessions dòng từ import_sessions";
        } else {
            $response[] = "Không tìm thấy bản ghi nào trong import_sessions";
        }

        return response()->json([
            'messages' => $response,
        ]);
    }

    public function getResult($sessionCode)
    {
        $sessionRecord = DB::select("SELECT * FROM import_sessions WHERE session_code = ? LIMIT 1", [$sessionCode]);

        if (!$sessionRecord) {
            return response()->json(['message' => 'Không tìm thấy phiên import'], 404);
        }

        $session = $sessionRecord[0];

        return response()->json([
            'status'        => $session->status,
            'result'        => $session->result,
            'total_success' => $session->total_success,
            'total_fail'    => $session->total_fail,
            'queued_at'     => $session->queued_at,
            'executed_at'   => $session->executed_at,
        ]);
    }
}