<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LoadExcelsController extends Controller
{
    public function load(Request $request)
    {

        $file = $request->file('file');
        if (!$file) {
            return response()->json(['message' => 'Yêu cầu gửi file'], 400);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $validExtensions = ['xlsx', 'xls', 'csv'];
        if (!in_array($extension, $validExtensions)) {
            return response()->json(['message' => 'File không đúng định dạng Excel'], 400);
        }


        $fullPath = $file->getPathname();


        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();


        $wards = DB::select("
            SELECT 
                wr.nam_dieu_tra,
                w.ten_phuong,
                p.ten_tinh,
                SUM(wr.tren_18_tuoi) AS tong_tren_18_tuoi,
                SUM(wr.gioi_tinh_nam) AS tong_gioi_tinh_nam,
                SUM(wr.gioi_tinh_nu) AS tong_gioi_tinh_nu,
                SUM(wr.dan_toc_kinh) AS tong_dan_toc_kinh,
                SUM(wr.dan_toc_khac) AS tong_dan_toc_khac,
                SUM(wr.khong_theo_ton_giao) AS tong_khong_theo_ton_giao,
                SUM(wr.co_theo_ton_giao) AS tong_co_theo_ton_giao,
                SUM(wr.co_thuoc_dien_uu_tien) AS tong_co_thuoc_dien_uu_tien,
                MAX(wr.ma_truong) AS ma_truong_dai_dien,
                SUM(wr.bac_tot_nghiep_thpt) AS tong_bac_tot_nghiep_thpt,
                SUM(wr.bac_tot_nghiep_thcs) AS tong_bac_tot_nghiep_thcs,
                SUM(wr.bac_tot_nghiep_th) AS tong_bac_tot_nghiep_th,
                SUM(wr.bac_tot_nghiep_mn) AS tong_bac_tot_nghiep_mn,
                SUM(wr.khong_co_bac_tot_nghiep) AS tong_khong_co_bac_tot_nghiep,
                SUM(wr.da_tot_nghiep) AS tong_da_tot_nghiep,
                SUM(wr.bac_tn_nghe_dai_hoc) AS tong_bac_tn_nghe_dai_hoc,
                SUM(wr.bac_tn_nghe_cao_dang) AS tong_bac_tn_nghe_cao_dang,
                SUM(wr.bac_tn_nghe_khac) AS tong_bac_tn_nghe_khac,
                SUM(wr.khong_co_bac_tn_nghe) AS tong_khong_co_bac_tn_nghe,
                SUM(wr.da_tot_nghiep_nghe) AS tong_da_tot_nghiep_nghe,
                SUM(wr.khuyet_tat_van_dong) AS tong_khuyet_tat_van_dong,
                SUM(wr.khuyet_tat_nghe_noi) AS tong_khuyet_tat_nghe_noi,
                SUM(wr.khuyet_tat_nhin) AS tong_khuyet_tat_nhin,
                SUM(wr.khuyet_tat_than_kinh) AS tong_khuyet_tat_than_kinh,
                SUM(wr.khuyet_tat_tri_tue) AS tong_khuyet_tat_tri_tue,
                SUM(wr.khuyet_tat_hoc_tap) AS tong_khuyet_tat_hoc_tap,
                SUM(wr.tu_ky) AS tong_tu_ky,
                SUM(wr.khuyet_tat_khac) AS tong_khuyet_tat_khac,
                SUM(wr.co_chung_nhan_khuyet_tat) AS tong_co_chung_nhan_khuyet_tat,
                SUM(wr.khuyet_tat_co_kha_nang_hoc_tap) AS tong_khuyet_tat_co_kha_nang_hoc_tap,
                SUM(wr.hoan_canh_dac_biet) AS tong_hoan_canh_dac_biet,
                SUM(wr.quan_he_voi_chu_ho) AS tong_quan_he_voi_chu_ho
            FROM wards_report wr
            JOIN wards w ON wr.ward_id = w.ward_id
            JOIN provinces p ON w.province_id = p.province_id
            GROUP BY wr.nam_dieu_tra, w.ten_phuong, p.ten_tinh
            ORDER BY wr.nam_dieu_tra, w.ten_phuong;
            
        ");
        // dd($wards);


        // $Ward = array_map(fn($row) => [
        //     'nam' => $row
        // ], $wards);
        // dd($Ward);

        $highestRow = $sheet->getHighestRow();


        $startRow = 7; 
        $yearColumnMap = [
            2025 => 'E',
            2024 => 'F',
            2023 => 'G',
            2022 => 'H',
            2021 => 'I',
            2020 => 'J',
            2019 => 'K',
        ];

        foreach ($wards as $ward) {
            $wardArray = (array)$ward;
            $year = (int)$wardArray['nam_dieu_tra'];

    
            $col = $yearColumnMap[$year];

            $row = $startRow;

            
            foreach ($wardArray as $key => $value) {
                $sheet->setCellValue($col . $row, $value ?? '');
                $row++;
            }
        }


        $writer = new Xlsx($spreadsheet);
        $fileName = 'PCGD.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}