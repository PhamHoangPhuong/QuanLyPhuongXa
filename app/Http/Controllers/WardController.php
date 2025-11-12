<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\WardsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProcessImportJob;
use Illuminate\Support\Facades\Auth;

class WardController extends Controller
{

    public function getWard(Request $request, $province_id)
    {
        $tenPhuong = $request->input('ten_phuong');
        $maPhuong  = $request->input('ma_phuong');

        if (!$tenPhuong || !$maPhuong) {
            return response()->json([
                'message' => 'Vui lòng nhập đủ ten_phuong và ma_phuong!'
            ], 400);
        }

        $province = DB::select("SELECT province_id FROM provinces WHERE province_id = ?", [$province_id]);
        if (empty($province)) {
            return response()->json([
                'message' => "Province với ID {$province_id} không tồn tại!"
            ], 404);
        }

        DB::insert("
            INSERT INTO wards(ten_phuong, ma_phuong, province_id)
            VALUES(?, ?, ?)
        ", [$tenPhuong, $maPhuong, $province_id]);

        $wards = DB::select("
            SELECT ten_phuong, ma_phuong, province_id
            FROM wards
            WHERE province_id = ?
        ", [$province_id]);

        return response()->json([
            'message' => 'Import dữ liệu thành công!',
            'wards'   => $wards
        ]);
    }

    public function sumTotalWard()
    {

        $phuongList = DB::select("SELECT distinct ward_id, nam_dieu_tra FROM usersimport");
        // dd($phuongList);
        $phuongIdList = array_map(fn($row) => [
            'ward_id' => $row->ward_id,
            'nam_dieu_tra' => $row->nam_dieu_tra,
        ], $phuongList);

        // dd($phuongIdList);

        foreach ($phuongIdList as $phuongId) {

            $wardId = $phuongId['ward_id'];
            $namDieuTra = $phuongId['nam_dieu_tra'];

            $tong_nam_dieu_tra = DB::selectOne("
                SELECT distinct nam_dieu_tra
                FROM usersimport
                WHERE ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countNamDieuTra = $tong_nam_dieu_tra->nam_dieu_tra;

            // dd(gettype($countNamDieuTra));

            // Đếm số người trên 18 tuổi
            $rows = DB::select("
                SELECT COUNT(nam_sinh) AS tren_18_tuoi
                FROM usersimport
                WHERE YEAR(CURDATE()) - nam_sinh > 18 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countOver18 = $rows[0]->tren_18_tuoi;

            //---------------------------------------------------------------
            //Giới tính
            $tong_gioi_tinh = DB::select("
                SELECT gioi_tinh, COUNT(*) AS so_luong
                FROM usersimport
                WHERE ward_id = ? AND nam_dieu_tra = ?
                GROUP BY gioi_tinh
            ", [$wardId, $namDieuTra]);

            $gioi_tinh_nam = 0;
            $gioi_tinh_nu = 0;

            foreach ($tong_gioi_tinh as $row) {
                if ($row->gioi_tinh == 0) {
                    $gioi_tinh_nam = $row->so_luong;
                } elseif ($row->gioi_tinh == 1) {
                    $gioi_tinh_nu = $row->so_luong;
                }
            }

            //---------------------------------------------------------------
            //Kinh
            $tongKinhRow = DB::selectOne("
                SELECT COUNT(*) AS tong_kinh
                FROM usersimport
                WHERE LOWER(dan_toc) = 'kinh' AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $tongKinh = $tongKinhRow->tong_kinh;

            //Kinh khác
            $tongKinhKhacRow = DB::selectOne("
                SELECT COUNT(*) AS tong_kinh_khac
                FROM usersimport
                WHERE (LOWER(dan_toc) != 'kinh' OR dan_toc IS NULL) AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $tongKinhKhac = $tongKinhKhacRow->tong_kinh_khac;

            //Không tôn giáo
            $khongTonGiaoRow = DB::selectOne("
                SELECT COUNT(*) AS khong_ton_giao
                FROM usersimport
                WHERE LOWER(TRIM(ton_giao)) = 'không' AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);
            $countKhongTonGiao = $khongTonGiaoRow->khong_ton_giao;

            //Có tôn giáo
            $coTonGiaoRow = DB::selectOne("
                SELECT COUNT(*) AS co_ton_giao
                FROM usersimport
                WHERE LOWER(TRIM(ton_giao)) = 'có' AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);
            $countCoTonGiao = $coTonGiaoRow->co_ton_giao;
            //---------------------------------------------------------------
            //Ưu tiên
            $thuocDienUuTien = DB::selectOne("
                SELECT COUNT(*) as so_luong
                FROM usersimport
                WHERE (dien_uu_tien IS NOT NULL OR dien_uu_tien = '') AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countThuocDienUuTien = $thuocDienUuTien->so_luong;

            //Mã trường
            $coMaTruong = DB::selectOne("
                SELECT COUNT(*) as so_luong
                FROM usersimport
                WHERE (ma_truong IS NOT NULL OR ma_truong = '') AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoMaTruong = $coMaTruong->so_luong;

            //Bậc tốt nghiệp
            $thuoc_bac_tot_nghiep = DB::select("
                SELECT bac_tot_nghiep, COUNT(*) AS so_luong
                FROM usersimport
                WHERE ward_id = ? AND nam_dieu_tra = ?
                GROUP BY bac_tot_nghiep
            ", [$wardId, $namDieuTra]);


            $bacTotNghiepTHPT = 0;
            $bacTotNghiepTHCS = 0;
            $bacTotNghiepTH = 0;
            $bacTotNghiepMN = 0;
            $khongCoBacTotNghiep = 0;

            
            foreach ($thuoc_bac_tot_nghiep as $row){
                
                if ($row->bac_tot_nghiep == 'THPT'){
                    $bacTotNghiepTHPT = $row->so_luong;

                } elseif($row->bac_tot_nghiep == 'THCS'){
                    $bacTotNghiepTHCS = $row->so_luong;

                } elseif($row->bac_tot_nghiep == 'TH'){
                    $bacTotNghiepTH = $row->so_luong;

                } elseif($row->bac_tot_nghiep == 'MN'){
                    $bacTotNghiepMN = $row->so_luong;

                } elseif(is_null($row->bac_tot_nghiep) || empty($row->bac_tot_nghiep)){
                    $khongCoBacTotNghiep += $row->so_luong;
                }
            }

            //Đã tốt nghiệp
            $daTotNghiep = DB::selectOne("
                SELECT COUNT(*) as so_luong
                FROM usersimport
                WHERE (nam_tot_nghiep IS NOT NULL OR nam_tot_nghiep = '') AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countDaTotNghiep = $daTotNghiep->so_luong;

            //---------------------------------------------------------------
            //Bậc tn nghề đại học
            $tongDaiHoc = DB::selectOne("
                SELECT COUNT(*) AS tong_dai_hoc
                FROM usersimport
                WHERE LOWER(bac_tn_nghe) = 'Đại Học' AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countTongDaiHoc = $tongDaiHoc->tong_dai_hoc;

            //Bậc tn nghề cao đẳng
            $tongCaoDang = DB::selectOne("
                SELECT COUNT(*) AS tong_cao_dang
                FROM usersimport
                WHERE LOWER(bac_tn_nghe) = 'Cao Đẳng' AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countTongCaoDang = $tongCaoDang->tong_cao_dang;

            //Bậc tn nghề khác
            $tongBacTnKhac = DB::selectOne("
                SELECT COUNT(*) AS tong_bac_tn_khac
                FROM usersimport
                WHERE LOWER(bac_tn_nghe) != 'Đại Học' AND LOWER(bac_tn_nghe) != 'Cao Đẳng' AND bac_tn_nghe IS NOT NULL AND ward_id = ? 
                AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countTongBacTnKhac = $tongBacTnKhac->tong_bac_tn_khac;

            //Không có bậc tn nghề
            $khongCoBacTnNghe = DB::selectOne("
                SELECT COUNT(*) AS khong_co_bac_tn_nghe 
                FROM usersimport
                WHERE bac_tn_nghe IS NULL AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countKhongCoBacTnNghe = $khongCoBacTnNghe->khong_co_bac_tn_nghe;

            //Đã tốt nghiệp nghề
            $daTotNghiepNghe = DB::selectOne("
                SELECT COUNT(*) as so_luong
                FROM usersimport
                WHERE (nam_tn_nghe IS NOT NULL OR nam_tn_nghe = '') AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countDaTotNghiepNghe = $daTotNghiepNghe->so_luong;

            //---------------------------------------------------------------

            //Vận Động
            $coKhuyetTatVanDong = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_van_dong
                FROM usersimport
                WHERE khuyet_tat_van_dong = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoKhuyetTatVanDong = $coKhuyetTatVanDong->khuyet_tat_van_dong;


            //Nghe Nói
            $coKhuyetTatNgheNoi = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_nghe_noi
                FROM usersimport
                WHERE khuyet_tat_nghe_noi = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoKhuyetTatNgheNoi = $coKhuyetTatNgheNoi->khuyet_tat_nghe_noi;


            //Nhìn
            $coKhuyetTatNhin = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_nhin
                FROM usersimport
                WHERE khuyet_tat_nhin = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoKhuyetTatNhin = $coKhuyetTatNhin->khuyet_tat_nhin;

            //Thần kinh
            $coKhuyetTatThanKinh = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_than_kinh
                FROM usersimport
                WHERE khuyet_tat_than_kinh = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoKhuyetTatThanKinh = $coKhuyetTatThanKinh->khuyet_tat_than_kinh;

            //Trí tuệ
            $coKhuyetTatTriTue = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_tri_tue
                FROM usersimport
                WHERE khuyet_tat_tri_tue = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoKhuyetTatTriTue = $coKhuyetTatTriTue->khuyet_tat_tri_tue;

            //Học tập
            $coKhuyetTatHocTap = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_hoc_tap
                FROM usersimport
                WHERE khuyet_tat_hoc_tap = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoKhuyetTatHocTap = $coKhuyetTatHocTap->khuyet_tat_hoc_tap;

            //Tự kỷ
            $coTuKy = DB::selectOne("
                SELECT COUNT(*) AS tu_ky
                FROM usersimport
                WHERE tu_ky = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoTuKy = $coTuKy->tu_ky;

            //Khuyết tật khác
            $coKhuyetTatKhac = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_khac
                FROM usersimport
                WHERE khuyet_tat_khac = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoKhuyetTatKhac = $coKhuyetTatKhac->khuyet_tat_khac;

            //Chứng nhận khuyết tật
            $coChungNhanKhuyetTat = DB::selectOne("
                SELECT COUNT(*) AS co_chung_nhan_khuyet_tat
                FROM usersimport
                WHERE co_chung_nhan_khuyet_tat = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoChungNhanKhuyetTat = $coChungNhanKhuyetTat->co_chung_nhan_khuyet_tat;

            //Khả năng học tập
            $khuyetTatCoKhaNangHocTap = DB::selectOne("
                SELECT COUNT(*) AS khuyet_tat_co_kha_nang_hoc_tap
                FROM usersimport
                WHERE kha_nang_hoc_tap = 1 AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countKhuyetTatCoKhaNangHocTap = $khuyetTatCoKhaNangHocTap->khuyet_tat_co_kha_nang_hoc_tap;
            //---------------------------------------------------------------

            //Hoàn cảnh đặc biệt
            $coHoanCanhDacBiet = DB::selectOne("
                SELECT COUNT(*) AS hoan_canh_dac_biet
                FROM usersimport
                WHERE hoan_canh_dac_biet IS NOT NULL AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoHoanCanhDacBiet = $coHoanCanhDacBiet->hoan_canh_dac_biet;

            $coQuanHeVoiChuHo = DB::selectOne("
                SELECT COUNT(*) AS quan_he_voi_chu_ho
                FROM usersimport
                WHERE quan_he_voi_chu_ho IS NOT NULL AND ward_id = ? AND nam_dieu_tra = ?
            ", [$wardId, $namDieuTra]);

            $countCoQuanHeVoiChuHo = $coQuanHeVoiChuHo->quan_he_voi_chu_ho;


            // $dataToInsert = [
            //     'province_id' => 1,
            //     'ten_phuong' => 'Xuyên Đông',
            //     'tren_18_tuoi' => $countOver18,
            //     'nam' => $gioi_tinh_nam,
            //     'nu' => $gioi_tinh_nu,
            //     'tong_kinh' => $tongKinh,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ];

            DB::insert("
            INSERT INTO wards_report (ward_id, nam_dieu_tra, tren_18_tuoi, gioi_tinh_nam, gioi_tinh_nu, dan_toc_kinh, dan_toc_khac, khong_theo_ton_giao, co_theo_ton_giao, co_thuoc_dien_uu_tien, ma_truong,
            bac_tot_nghiep_thpt, bac_tot_nghiep_thcs, bac_tot_nghiep_th, bac_tot_nghiep_mn, khong_co_bac_tot_nghiep, da_tot_nghiep, da_tot_nghiep_nghe, 
            bac_tn_nghe_dai_hoc, bac_tn_nghe_cao_dang, bac_tn_nghe_khac, khong_co_bac_tn_nghe,
            khuyet_tat_van_dong, khuyet_tat_nghe_noi, khuyet_tat_nhin, khuyet_tat_than_kinh, khuyet_tat_tri_tue, khuyet_tat_hoc_tap, 
            tu_ky, khuyet_tat_khac, co_chung_nhan_khuyet_tat, khuyet_tat_co_kha_nang_hoc_tap, hoan_canh_dac_biet, quan_he_voi_chu_ho, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $wardId,
            $countNamDieuTra,
            $countOver18,
            $gioi_tinh_nam,
            $gioi_tinh_nu,
            $tongKinh,
            $tongKinhKhac,
            $countKhongTonGiao,  
            $countCoTonGiao,
            $countThuocDienUuTien,
            $countCoMaTruong,
            $bacTotNghiepTHPT,
            $bacTotNghiepTHCS,
            $bacTotNghiepTH,
            $bacTotNghiepMN,
            $khongCoBacTotNghiep,
            $countDaTotNghiep,
            $countDaTotNghiepNghe,
            $countTongDaiHoc,
            $countTongCaoDang,
            $countTongBacTnKhac,
            $countKhongCoBacTnNghe,
            $countCoKhuyetTatVanDong,
            $countCoKhuyetTatNgheNoi,
            $countCoKhuyetTatNhin,
            $countCoKhuyetTatThanKinh,
            $countCoKhuyetTatTriTue,
            $countCoKhuyetTatHocTap,
            $countCoTuKy,
            $countCoKhuyetTatKhac,
            $countCoChungNhanKhuyetTat,
            $countKhuyetTatCoKhaNangHocTap,
            $countCoHoanCanhDacBiet,
            $countCoQuanHeVoiChuHo,
            now(),
            now()
            ]);
        }    
        return response()->json([
            'message' => 'Import thành công',
        ]);
    }

    public function update(Request $request, $wardId, $namDieuTra)
    {

        $deletedUserImport = DB::delete("
            DELETE FROM usersimport WHERE ward_id = ?
        ", [$wardId]);

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

        
        
        ProcessImportJob::dispatchSync($session->id, $path, $id);

        $tong_nam_dieu_tra = DB::selectOne("
            SELECT distinct nam_dieu_tra
            FROM usersimport
            WHERE ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);
        // dd($tong_nam_dieu_tra);

        $countNamDieuTra = $tong_nam_dieu_tra->nam_dieu_tra;

        // dd(gettype($countNamDieuTra));

        // Đếm số người trên 18 tuổi
        $rows = DB::select("
            SELECT COUNT(nam_sinh) AS tren_18_tuoi
            FROM usersimport
            WHERE YEAR(CURDATE()) - nam_sinh > 18 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countOver18 = $rows[0]->tren_18_tuoi;

        //---------------------------------------------------------------
        //Giới tính
        $tong_gioi_tinh = DB::select("
            SELECT gioi_tinh, COUNT(*) AS so_luong
            FROM usersimport
            WHERE ward_id = ? AND nam_dieu_tra = ?
            GROUP BY gioi_tinh
        ", [$wardId, $namDieuTra]);

        $gioi_tinh_nam = 0;
        $gioi_tinh_nu = 0;

        foreach ($tong_gioi_tinh as $row) {
            if ($row->gioi_tinh == 0) {
                $gioi_tinh_nam = $row->so_luong;
            } elseif ($row->gioi_tinh == 1) {
                $gioi_tinh_nu = $row->so_luong;
            }
        }

        //---------------------------------------------------------------
        //Kinh
        $tongKinhRow = DB::selectOne("
            SELECT COUNT(*) AS tong_kinh
            FROM usersimport
            WHERE LOWER(dan_toc) = 'kinh' AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $tongKinh = $tongKinhRow->tong_kinh;

        //Kinh khác
        $tongKinhKhacRow = DB::selectOne("
            SELECT COUNT(*) AS tong_kinh_khac
            FROM usersimport
            WHERE (LOWER(dan_toc) != 'kinh' OR dan_toc IS NULL) AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $tongKinhKhac = $tongKinhKhacRow->tong_kinh_khac;

        //Không tôn giáo
        $khongTonGiaoRow = DB::selectOne("
            SELECT COUNT(*) AS khong_ton_giao
            FROM usersimport
            WHERE LOWER(TRIM(ton_giao)) = 'không' AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);
        $countKhongTonGiao = $khongTonGiaoRow->khong_ton_giao;

        //Có tôn giáo
        $coTonGiaoRow = DB::selectOne("
            SELECT COUNT(*) AS co_ton_giao
            FROM usersimport
            WHERE LOWER(TRIM(ton_giao)) = 'có' AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);
        $countCoTonGiao = $coTonGiaoRow->co_ton_giao;
        //---------------------------------------------------------------
        //Ưu tiên
        $thuocDienUuTien = DB::selectOne("
            SELECT COUNT(*) as so_luong
            FROM usersimport
            WHERE (dien_uu_tien IS NOT NULL OR dien_uu_tien = '') AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countThuocDienUuTien = $thuocDienUuTien->so_luong;

        //Mã trường
        $coMaTruong = DB::selectOne("
            SELECT COUNT(*) as so_luong
            FROM usersimport
            WHERE (ma_truong IS NOT NULL OR ma_truong = '') AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoMaTruong = $coMaTruong->so_luong;

        //Bậc tốt nghiệp
        $thuoc_bac_tot_nghiep = DB::select("
            SELECT bac_tot_nghiep, COUNT(*) AS so_luong
            FROM usersimport
            WHERE ward_id = ? AND nam_dieu_tra = ?
            GROUP BY bac_tot_nghiep
        ", [$wardId, $namDieuTra]);


        $bacTotNghiepTHPT = 0;
        $bacTotNghiepTHCS = 0;
        $bacTotNghiepTH = 0;
        $bacTotNghiepMN = 0;
        $khongCoBacTotNghiep = 0;

        
        foreach ($thuoc_bac_tot_nghiep as $row){
            
            if ($row->bac_tot_nghiep == 'THPT'){
                $bacTotNghiepTHPT = $row->so_luong;

            } elseif($row->bac_tot_nghiep == 'THCS'){
                $bacTotNghiepTHCS = $row->so_luong;

            } elseif($row->bac_tot_nghiep == 'TH'){
                $bacTotNghiepTH = $row->so_luong;

            } elseif($row->bac_tot_nghiep == 'MN'){
                $bacTotNghiepMN = $row->so_luong;

            } elseif(is_null($row->bac_tot_nghiep) || empty($row->bac_tot_nghiep)){
                $khongCoBacTotNghiep += $row->so_luong;
            }
        }

        //Đã tốt nghiệp
        $daTotNghiep = DB::selectOne("
            SELECT COUNT(*) as so_luong
            FROM usersimport
            WHERE (nam_tot_nghiep IS NOT NULL OR nam_tot_nghiep = '') AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countDaTotNghiep = $daTotNghiep->so_luong;

        //---------------------------------------------------------------
        //Bậc tn nghề đại học
        $tongDaiHoc = DB::selectOne("
            SELECT COUNT(*) AS tong_dai_hoc
            FROM usersimport
            WHERE LOWER(bac_tn_nghe) = 'Đại Học' AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countTongDaiHoc = $tongDaiHoc->tong_dai_hoc;

        //Bậc tn nghề cao đẳng
        $tongCaoDang = DB::selectOne("
            SELECT COUNT(*) AS tong_cao_dang
            FROM usersimport
            WHERE LOWER(bac_tn_nghe) = 'Cao Đẳng' AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countTongCaoDang = $tongCaoDang->tong_cao_dang;

        //Bậc tn nghề khác
        $tongBacTnKhac = DB::selectOne("
            SELECT COUNT(*) AS tong_bac_tn_khac
            FROM usersimport
            WHERE LOWER(bac_tn_nghe) != 'Đại Học' AND LOWER(bac_tn_nghe) != 'Cao Đẳng' AND bac_tn_nghe IS NOT NULL AND ward_id = ? 
            AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countTongBacTnKhac = $tongBacTnKhac->tong_bac_tn_khac;

        //Không có bậc tn nghề
        $khongCoBacTnNghe = DB::selectOne("
            SELECT COUNT(*) AS khong_co_bac_tn_nghe 
            FROM usersimport
            WHERE bac_tn_nghe IS NULL AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countKhongCoBacTnNghe = $khongCoBacTnNghe->khong_co_bac_tn_nghe;

        //Đã tốt nghiệp nghề
        $daTotNghiepNghe = DB::selectOne("
            SELECT COUNT(*) as so_luong
            FROM usersimport
            WHERE (nam_tn_nghe IS NOT NULL OR nam_tn_nghe = '') AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countDaTotNghiepNghe = $daTotNghiepNghe->so_luong;

        //---------------------------------------------------------------

        //Vận Động
        $coKhuyetTatVanDong = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_van_dong
            FROM usersimport
            WHERE khuyet_tat_van_dong = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoKhuyetTatVanDong = $coKhuyetTatVanDong->khuyet_tat_van_dong;


        //Nghe Nói
        $coKhuyetTatNgheNoi = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_nghe_noi
            FROM usersimport
            WHERE khuyet_tat_nghe_noi = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoKhuyetTatNgheNoi = $coKhuyetTatNgheNoi->khuyet_tat_nghe_noi;


        //Nhìn
        $coKhuyetTatNhin = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_nhin
            FROM usersimport
            WHERE khuyet_tat_nhin = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoKhuyetTatNhin = $coKhuyetTatNhin->khuyet_tat_nhin;

        //Thần kinh
        $coKhuyetTatThanKinh = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_than_kinh
            FROM usersimport
            WHERE khuyet_tat_than_kinh = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoKhuyetTatThanKinh = $coKhuyetTatThanKinh->khuyet_tat_than_kinh;

        //Trí tuệ
        $coKhuyetTatTriTue = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_tri_tue
            FROM usersimport
            WHERE khuyet_tat_tri_tue = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoKhuyetTatTriTue = $coKhuyetTatTriTue->khuyet_tat_tri_tue;

        //Học tập
        $coKhuyetTatHocTap = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_hoc_tap
            FROM usersimport
            WHERE khuyet_tat_hoc_tap = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoKhuyetTatHocTap = $coKhuyetTatHocTap->khuyet_tat_hoc_tap;

        //Tự kỷ
        $coTuKy = DB::selectOne("
            SELECT COUNT(*) AS tu_ky
            FROM usersimport
            WHERE tu_ky = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoTuKy = $coTuKy->tu_ky;

        //Khuyết tật khác
        $coKhuyetTatKhac = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_khac
            FROM usersimport
            WHERE khuyet_tat_khac = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoKhuyetTatKhac = $coKhuyetTatKhac->khuyet_tat_khac;

        //Chứng nhận khuyết tật
        $coChungNhanKhuyetTat = DB::selectOne("
            SELECT COUNT(*) AS co_chung_nhan_khuyet_tat
            FROM usersimport
            WHERE co_chung_nhan_khuyet_tat = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoChungNhanKhuyetTat = $coChungNhanKhuyetTat->co_chung_nhan_khuyet_tat;

        //Khả năng học tập
        $khuyetTatCoKhaNangHocTap = DB::selectOne("
            SELECT COUNT(*) AS khuyet_tat_co_kha_nang_hoc_tap
            FROM usersimport
            WHERE kha_nang_hoc_tap = 1 AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countKhuyetTatCoKhaNangHocTap = $khuyetTatCoKhaNangHocTap->khuyet_tat_co_kha_nang_hoc_tap;
        //---------------------------------------------------------------

        //Hoàn cảnh đặc biệt
        $coHoanCanhDacBiet = DB::selectOne("
            SELECT COUNT(*) AS hoan_canh_dac_biet
            FROM usersimport
            WHERE hoan_canh_dac_biet IS NOT NULL AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoHoanCanhDacBiet = $coHoanCanhDacBiet->hoan_canh_dac_biet;

        $coQuanHeVoiChuHo = DB::selectOne("
            SELECT COUNT(*) AS quan_he_voi_chu_ho
            FROM usersimport
            WHERE quan_he_voi_chu_ho IS NOT NULL AND ward_id = ? AND nam_dieu_tra = ?
        ", [$wardId, $namDieuTra]);

        $countCoQuanHeVoiChuHo = $coQuanHeVoiChuHo->quan_he_voi_chu_ho;


        // $dataToInsert = [
        //     'province_id' => 1,
        //     'ten_phuong' => 'Xuyên Đông',
        //     'tren_18_tuoi' => $countOver18,
        //     'nam' => $gioi_tinh_nam,
        //     'nu' => $gioi_tinh_nu,
        //     'tong_kinh' => $tongKinh,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ];

        DB::update("
        UPDATE wards_report SET 
            tren_18_tuoi = ?, 
            gioi_tinh_nam = ?, 
            gioi_tinh_nu = ?, 
            dan_toc_kinh = ?, 
            dan_toc_khac = ?, 
            khong_theo_ton_giao = ?, 
            co_theo_ton_giao = ?, 
            co_thuoc_dien_uu_tien = ?, 
            ma_truong = ?, 
            bac_tot_nghiep_thpt = ?, 
            bac_tot_nghiep_thcs = ?, 
            bac_tot_nghiep_th = ?, 
            bac_tot_nghiep_mn = ?, 
            khong_co_bac_tot_nghiep = ?, 
            da_tot_nghiep = ?, 
            bac_tn_nghe_dai_hoc = ?, 
            bac_tn_nghe_cao_dang = ?, 
            bac_tn_nghe_khac = ?, 
            khong_co_bac_tn_nghe = ?, 
            da_tot_nghiep_nghe = ?, 
            khuyet_tat_van_dong = ?, 
            khuyet_tat_nghe_noi = ?, 
            khuyet_tat_nhin = ?, 
            khuyet_tat_than_kinh = ?, 
            khuyet_tat_tri_tue = ?, 
            khuyet_tat_hoc_tap = ?, 
            tu_ky = ?, 
            khuyet_tat_khac = ?, 
            co_chung_nhan_khuyet_tat = ?, 
            khuyet_tat_co_kha_nang_hoc_tap = ?, 
            hoan_canh_dac_biet = ?, 
            quan_he_voi_chu_ho = ? 
        WHERE ward_id = ? AND nam_dieu_tra = ?
    ", [
        $countOver18,
        $gioi_tinh_nam,
        $gioi_tinh_nu,
        $tongKinh,
        $tongKinhKhac,
        $countKhongTonGiao,
        $countCoTonGiao,
        $countThuocDienUuTien,
        $countCoMaTruong,
        $bacTotNghiepTHPT,
        $bacTotNghiepTHCS,
        $bacTotNghiepTH,
        $bacTotNghiepMN,
        $khongCoBacTotNghiep,
        $countDaTotNghiep,
        $countTongDaiHoc,
        $countTongCaoDang,
        $countTongBacTnKhac,
        $countKhongCoBacTnNghe,
        $countDaTotNghiepNghe,
        $countCoKhuyetTatVanDong,
        $countCoKhuyetTatNgheNoi,
        $countCoKhuyetTatNhin,
        $countCoKhuyetTatThanKinh,
        $countCoKhuyetTatTriTue,
        $countCoKhuyetTatHocTap,
        $countCoTuKy,
        $countCoKhuyetTatKhac,
        $countCoChungNhanKhuyetTat,
        $countKhuyetTatCoKhaNangHocTap,
        $countCoHoanCanhDacBiet,
        $countCoQuanHeVoiChuHo,
        $wardId,
        $countNamDieuTra
    ]);
 
        return response()->json([
            'message' => 'Cập nhật thành công',
        ]);
    }

    public function export(Request $request)
    {
        $namDieuTra = $request->input('nam_dieu_tra');
        $id = Auth::guard('api')->user();
        // dd($namDieuTra);
        return Excel::download(new WardsExport($namDieuTra, $id), 'wards.xlsx');
    }

    public function index(){
        $result = DB::select("SELECT * FROM wards_report");

        return response()->json([
            'ward' => $result
        ]);
    }
}