<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ProvincesExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ProvinceController extends Controller
{

    public function getProvince(Request $request)
    {
        $tenTinh = $request->input('ten_tinh');
        $maTinh  = $request->input('ma_tinh');

        if (!$tenTinh || !$maTinh) {
            return response()->json(['message' => 'Vui lòng nhập đủ ten_tinh và ma_tinh!'], 400);
        }

        DB::insert("
            INSERT INTO provinces(ten_tinh, ma_tinh) VALUES(?, ?)
        ", [$tenTinh, $maTinh]);

        $provinces = DB::select("
            SELECT ten_tinh, ma_tinh FROM provinces
        ");

        return response()->json([
            'message'   => 'Import dữ liệu thành công!',
            'provinces' => $provinces
        ]);
    }

    public function province()
    {
        //---------------------------------------------------

        $id = Auth::guard('api')->user()->province_id;
        // dd($id);
        $tinhList = DB::select("SELECT ward_id, nam_dieu_tra FROM wards_report
        WHERE province_id = ?
        ", [$id]);
        // dd($tinhList);
        // dd($phuongList);
        $tinhIdList = array_map(fn($row) => [
            'nam_dieu_tra' => $row->nam_dieu_tra,
        ], $tinhList);

        foreach ($tinhIdList as $tinhList) {

            $namDieuTra = $tinhList['nam_dieu_tra'];

            $exists = DB::selectOne("
                SELECT *
                FROM provinces_report 
                WHERE province_id = ? AND nam_dieu_tra = ? 
            ", [$id, $namDieuTra]);

            if ($exists) {
                continue;
            }

            $row = DB::selectOne("
                SELECT 
                    SUM(tong_dan_so) AS tong_dan_so,
                    SUM(dan_so_tu_15_den_25_tuoi) AS dan_so_tu_15_den_25_tuoi,
                    SUM(dan_so_tu_15_den_35_tuoi) AS dan_so_tu_15_den_35_tuoi,
                    SUM(dan_so_tu_15_den_60_tuoi) AS dan_so_tu_15_den_60_tuoi,


                    SUM(gioi_tinh_nu) AS gioi_tinh_nu,
                    SUM(gioi_tinh_nu_tu_15_den_25_tuoi) AS gioi_tinh_nu_tu_15_den_25_tuoi,
                    SUM(gioi_tinh_nu_tu_15_den_35_tuoi) AS gioi_tinh_nu_tu_15_den_35_tuoi,
                    SUM(gioi_tinh_nu_tu_15_den_60_tuoi) AS gioi_tinh_nu_tu_15_den_60_tuoi,

                    SUM(gioi_tinh_nam) as gioi_tinh_nam,


                    SUM(dan_toc) AS dan_toc,
                    SUM(dan_toc_tu_15_den_25_tuoi) AS dan_toc_tu_15_den_25_tuoi,
                    SUM(dan_toc_tu_15_den_35_tuoi) AS dan_toc_tu_15_den_35_tuoi,
                    SUM(dan_toc_tu_15_den_60_tuoi) AS dan_toc_tu_15_den_60_tuoi,


                    SUM(nu_dan_toc) AS nu_dan_toc,
                    SUM(nu_dan_toc_tu_15_den_25_tuoi) AS nu_dan_toc_tu_15_den_25_tuoi,
                    SUM(nu_dan_toc_tu_15_den_35_tuoi) AS nu_dan_toc_tu_15_den_35_tuoi,
                    SUM(nu_dan_toc_tu_15_den_60_tuoi) AS nu_dan_toc_tu_15_den_60_tuoi,


                    SUM(Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3) AS Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                    SUM(Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3) AS Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                    SUM(Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3) AS Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,


                    SUM(Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3) AS Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                    SUM(Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3) AS Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                    SUM(Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3) AS Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,


                    SUM(Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3) AS Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                    SUM(Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3) AS Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                    SUM(Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3) AS Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,


                    SUM(Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3) AS Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                    SUM(Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3) AS Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                    SUM(Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3) AS Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,


                    SUM(Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5) AS Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                    SUM(Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5) AS Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                    SUM(Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5) AS Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,


                    SUM(Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5) AS Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                    SUM(Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5) AS Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                    SUM(Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5) AS Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,


                    SUM(Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5) AS Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                    SUM(Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5) AS Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                    SUM(Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5) AS Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,


                    SUM(Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5) AS Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                    SUM(Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5) AS Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                    SUM(Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5) AS Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5

                FROM wards_report
                WHERE nam_dieu_tra = ? AND province_id = ?
            ", [$namDieuTra, $id]);

            
            // --------------------------------------------------------------------------------------
            // Dân số
            $countTotalPopulation = $row->tong_dan_so;

            // Từ 15 - 25 tuổi
            $countBetween15To25 = $row->dan_so_tu_15_den_25_tuoi;

            // Từ 15 - 35 tuổi
            $countBetween15To35 = $row->dan_so_tu_15_den_35_tuoi;

            // Từ 15 - 60 tuổi
            $countBetween15To60 = $row->dan_so_tu_15_den_60_tuoi;
            
            // --------------------------------------------------------------------------------------

            // Giới tính
            $gioi_tinh_nam = $row->gioi_tinh_nam;
            $gioi_tinh_nu = $row->gioi_tinh_nu;

            // Giới tính nữ từ 15 đến 25 tuổi
            $gioi_tinh_nu_tu_15_den_25_tuoi = $row->gioi_tinh_nu_tu_15_den_25_tuoi;

            // Giới tính nữ từ 15 đến 35 tuổi
            $gioi_tinh_nu_tu_15_den_35_tuoi = $row->gioi_tinh_nu_tu_15_den_35_tuoi;

            // Giới tính nữ từ 15 đến 60 tuổi
            $gioi_tinh_nu_tu_15_den_60_tuoi = $row->gioi_tinh_nu_tu_15_den_60_tuoi;

            // --------------------------------------------------------------------------------------
            // Tổng số dân tộc
            $countTongSoDanToc = $row->dan_toc;

            // Tổng số dân tộc từ 15 đến 25 tuổi
            $countTongSoDanTocTu15Den25 = $row->dan_toc_tu_15_den_25_tuoi;

            // Tổng số dân tộc từ 15 đến 35 tuổi
            $countTongSoDanTocTu15Den35 = $row->dan_toc_tu_15_den_35_tuoi;

            // Tổng số dân tộc từ 15 đến 60 tuổi
            $countTongSoDanTocTu15Den60 = $row->dan_toc_tu_15_den_60_tuoi;

            // --------------------------------------------------------------------------------------
            // Tổng số nữ dân tộc
            $countTongSoNuDanToc = $row->nu_dan_toc;

            // Tổng số nữ dân tộc từ 15 đến 25 tuổi
            $countTongSoNuDanTocTu15Den25 = $row->nu_dan_toc_tu_15_den_25_tuoi;

            // Tổng số nữ dân tộc từ 15 đến 35 tuổi
            $countTongSoNuDanTocTu15Den35 = $row->nu_dan_toc_tu_15_den_35_tuoi;

            // Tổng số nữ dân tộc từ 15 đến 60 tuổi
            $countTongSoNuDanTocTu15Den60 = $row->nu_dan_toc_tu_15_den_60_tuoi;

            // 1 --------------------------------------------------------------------------------------

            // Tổng dân số mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $row->Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3;

            // Tổng dân số mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $row->Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

            // Tổng dân số mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $row->Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

            // 2 --------------------------------------------------------------------------------------

            // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $row->Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3;

            // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $row->Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

            // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $row->Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

            // 3 --------------------------------------------------------------------------------------

            // Tổng số dân tộc mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $row->Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

            // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $row->Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

            // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $row->Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

            // 4 --------------------------------------------------------------------------------------

            // Tổng số dân tộc nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $row->Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

            // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $row->Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

            // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $row->Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

            // 5 --------------------------------------------------------------------------------------

            // Tổng dân số mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $row->Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5;

            // Tổng dân số mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $row->Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

            // Tổng dân số mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $row->Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

            // 6 --------------------------------------------------------------------------------------

            // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $row->Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5;

            // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $row->Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

            // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $row->Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

            // 7 --------------------------------------------------------------------------------------

            // Tổng số dân tộc mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $row->Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

            // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $row->Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

            // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $row->Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

            // 8 --------------------------------------------------------------------------------------

            // Tổng số dân tộc nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $row->Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

            // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $row->Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

            // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
            $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $row->Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

            

            DB::insert("
                INSERT INTO provinces_report (
                    province_id, nam_dieu_tra , tong_dan_so, dan_so_tu_15_den_25_tuoi, dan_so_tu_15_den_35_tuoi, dan_so_tu_15_den_60_tuoi, gioi_tinh_nam, gioi_tinh_nu, gioi_tinh_nu_tu_15_den_25_tuoi, gioi_tinh_nu_tu_15_den_35_tuoi, gioi_tinh_nu_tu_15_den_60_tuoi,
            dan_toc, dan_toc_tu_15_den_25_tuoi, dan_toc_tu_15_den_35_tuoi, dan_toc_tu_15_den_60_tuoi, nu_dan_toc, nu_dan_toc_tu_15_den_25_tuoi, nu_dan_toc_tu_15_den_35_tuoi, nu_dan_toc_tu_15_den_60_tuoi,

                Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                

                Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

                Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

                Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,
                
                Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5

                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $id, 
                $namDieuTra,
                $countTotalPopulation,
                $countBetween15To25,
                $countBetween15To35,
                $countBetween15To60,
                $gioi_tinh_nam,
                $gioi_tinh_nu,
                $gioi_tinh_nu_tu_15_den_25_tuoi,
                $gioi_tinh_nu_tu_15_den_35_tuoi,
                $gioi_tinh_nu_tu_15_den_60_tuoi,
                $countTongSoDanToc,
                $countTongSoDanTocTu15Den25,
                $countTongSoDanTocTu15Den35,
                $countTongSoDanTocTu15Den60,
                $countTongSoNuDanToc,
                $countTongSoNuDanTocTu15Den25,
                $countTongSoNuDanTocTu15Den35,
                $countTongSoNuDanTocTu15Den60,

                $count_ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                $count_ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                $count_ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                $count_dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                $count_dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                $count_dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,


                $count_ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                $count_ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                $count_ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

                $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

                $count_dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                $count_dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                $count_dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

                $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,
            ]);
        }
        return response()->json(['message' => 'Import dữ liệu thành công!']);
    }

    public function export()
    {
        return Excel::download(new ProvincesExport, 'provinces.xlsx');
    }

    public function index(){
        $result = DB::select("SELECT * FROM provinces_report");

        return response()->json([
            'province' => $result
        ]);
    }
}