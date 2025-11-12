<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $wardId;
    protected $namDieuTra;

    public function __construct($wardId, $namDieuTra)
    {
        $this->wardId = $wardId;
        $this->namDieuTra = $namDieuTra;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $wardId = $this->wardId;
        $namDieuTra = $this->namDieuTra;

        $result = DB::selectOne("
        SELECT
            -- Năm điều tra
            MAX(nam_dieu_tra) AS nam_dieu_tra,

            --------------------------------------------------------------------------------------
            -- Tổng dân số
            SUM(CASE WHEN tt IS NOT NULL AND tt <> '' THEN 1 ELSE 0 END) AS tong_dan_so,

            -- Đếm số người trên 15 đến 25 tuổi
            SUM(CASE WHEN nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) AS dan_so_tu_15_den_25_tuoi,

            -- Đếm số người trên 15 đến 35 tuổi
            SUM(CASE WHEN nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) AS dan_so_tu_15_den_35_tuoi,

            -- Đếm số người trên 15 đến 60 tuổi
            SUM(CASE WHEN nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) AS dan_so_tu_15_den_60_tuoi,

            --------------------------------------------------------------------------------------
            -- Giới tính
            SUM(CASE WHEN gioi_tinh = 0 THEN 1 ELSE 0 END) AS gioi_tinh_nam,
            SUM(CASE WHEN gioi_tinh = 1 THEN 1 ELSE 0 END) AS gioi_tinh_nu,

            -- Đếm số giới tính nữ trên 15 đến 25 tuổi
            SUM(CASE WHEN gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) AS gioi_tinh_nu_tu_15_den_25_tuoi,

            -- Đếm số giới tính nữ trên 15 đến 35 tuổi
            SUM(CASE WHEN gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) AS gioi_tinh_nu_tu_15_den_35_tuoi,

            -- Đếm số giới tính nữ trên 15 đến 60 tuổi
            SUM(CASE WHEN gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) AS gioi_tinh_nu_tu_15_den_60_tuoi,
            
            --------------------------------------------------------------------------------------
            -- Tổng số dân tộc
            SUM(CASE WHEN LOWER(dan_toc) <> 'kinh' OR dan_toc IS NULL THEN 0 ELSE 1 END) AS dan_toc,

            -- Tổng số dân tộc từ 15 đến 25 tuổi
            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END)
            AS dan_toc_tu_15_den_25_tuoi,

            -- Tổng số dân tộc từ 15 đến 35 tuổi
            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS dan_toc_tu_15_den_35_tuoi,

            -- Tổng số dân tộc từ 15 đến 60 tuổi
            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS dan_toc_tu_15_den_60_tuoi,

            --------------------------------------------------------------------------------------
            -- Tổng số nữ dân tộc 
            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND gioi_tinh = 1 THEN 1 ELSE 0 END) 
            AS nu_dan_toc,

            -- Tổng số nữ dân tộc từ 15 đến 25 tuổi 
            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS nu_dan_toc_tu_15_den_25_tuoi,

            -- Tổng số nữ dân tộc từ 15 đến 35 tuổi
            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS nu_dan_toc_tu_15_den_35_tuoi,

            -- Tổng số nữ dân tộc từ 15 đến 60 tuổi
            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS nu_dan_toc_tu_15_den_60_tuoi,
            
            --------------------------------------------------------------------------------------
            -- Tổng dân số mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,

            SUM(CASE WHEN hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,

            SUM(CASE WHEN hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

            --------------------------------------------------------------------------------------
            -- Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,

            SUM(CASE WHEN hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,

            SUM(CASE WHEN hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

            --------------------------------------------------------------------------------------
            -- Tổng số dân tộc mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

            --------------------------------------------------------------------------------------
            -- Tổng số dân tộc nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 3 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,




            --------------------------------------------------------------------------------------
            -- Tổng dân số mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,

            SUM(CASE WHEN hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,

            SUM(CASE WHEN hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

            --------------------------------------------------------------------------------------
            -- Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,

            SUM(CASE WHEN hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,

            SUM(CASE WHEN hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

            --------------------------------------------------------------------------------------
            -- Tổng số dân tộc mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

            --------------------------------------------------------------------------------------
            -- Tổng số dân tộc nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 25 THEN 1 ELSE 0 END) 
            AS Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 35 THEN 1 ELSE 0 END) 
            AS Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,

            SUM(CASE WHEN (LOWER(dan_toc) = 'kinh' OR dan_toc IS NULL) AND hoc_xong_lop < 5 AND hoc_xong_lop IS NOT NULL AND gioi_tinh = 1 AND nam_dieu_tra - nam_sinh BETWEEN 15 AND 60 THEN 1 ELSE 0 END) 
            AS Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5

        FROM usersimport
        WHERE ward_id = ? AND nam_dieu_tra = ?
    ", [$wardId, $namDieuTra]);
    // dd($result);

        // Năm điều tra
        $countNamDieuTra = $result->nam_dieu_tra;

        // --------------------------------------------------------------------------------------
        // Dân số
        $countTotalPopulation = $result->tong_dan_so;

        // Từ 15 - 25 tuổi
        $countBetween15To25 = $result->dan_so_tu_15_den_25_tuoi;

        // Từ 15 - 35 tuổi
        $countBetween15To35 = $result->dan_so_tu_15_den_35_tuoi;

        // Từ 15 - 60 tuổi
        $countBetween15To60 = $result->dan_so_tu_15_den_60_tuoi;
        
        // --------------------------------------------------------------------------------------

        // Giới tính
        $gioi_tinh_nam = $result->gioi_tinh_nam;
        $gioi_tinh_nu = $result->gioi_tinh_nu;

        // Giới tính nữ từ 15 đến 25 tuổi
        $gioi_tinh_nu_tu_15_den_25_tuoi = $result->gioi_tinh_nu_tu_15_den_25_tuoi;

        // Giới tính nữ từ 15 đến 35 tuổi
        $gioi_tinh_nu_tu_15_den_35_tuoi = $result->gioi_tinh_nu_tu_15_den_35_tuoi;

        // Giới tính nữ từ 15 đến 60 tuổi
        $gioi_tinh_nu_tu_15_den_60_tuoi = $result->gioi_tinh_nu_tu_15_den_60_tuoi;

        // --------------------------------------------------------------------------------------
        // Tổng số dân tộc
        $countTongSoDanToc = $result->dan_toc;

        // Tổng số dân tộc từ 15 đến 25 tuổi
        $countTongSoDanTocTu15Den25 = $result->dan_toc_tu_15_den_25_tuoi;

        // Tổng số dân tộc từ 15 đến 35 tuổi
        $countTongSoDanTocTu15Den35 = $result->dan_toc_tu_15_den_35_tuoi;

        // Tổng số dân tộc từ 15 đến 60 tuổi
        $countTongSoDanTocTu15Den60 = $result->dan_toc_tu_15_den_60_tuoi;

        // --------------------------------------------------------------------------------------
        // Tổng số nữ dân tộc
        $countTongSoNuDanToc = $result->nu_dan_toc;

        // Tổng số nữ dân tộc từ 15 đến 25 tuổi
        $countTongSoNuDanTocTu15Den25 = $result->nu_dan_toc_tu_15_den_25_tuoi;

        // Tổng số nữ dân tộc từ 15 đến 35 tuổi
        $countTongSoNuDanTocTu15Den35 = $result->nu_dan_toc_tu_15_den_35_tuoi;

        // Tổng số nữ dân tộc từ 15 đến 60 tuổi
        $countTongSoNuDanTocTu15Den60 = $result->nu_dan_toc_tu_15_den_60_tuoi;

        // 1 --------------------------------------------------------------------------------------

        // Tổng dân số mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $result->Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3;

        // Tổng dân số mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $result->Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

        // Tổng dân số mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $result->Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

        // 2 --------------------------------------------------------------------------------------

        // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $result->Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3;

        // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $result->Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

        // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $result->Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

        // 3 --------------------------------------------------------------------------------------

        // Tổng số dân tộc mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $result->Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

        // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $result->Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

        // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $result->Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

        // 4 --------------------------------------------------------------------------------------

        // Tổng số dân tộc nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = $result->Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

        // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = $result->Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3;

        // Tổng dân số nữ mù chữ ở mức độ 1 chưa hoàn thành lớp 3 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = $result->Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3;

        // 5 --------------------------------------------------------------------------------------

        // Tổng dân số mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $result->Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5;

        // Tổng dân số mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $result->Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

        // Tổng dân số mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $result->Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

        // 6 --------------------------------------------------------------------------------------

        // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $result->Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5;

        // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $result->Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

        // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $result->Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

        // 7 --------------------------------------------------------------------------------------

        // Tổng số dân tộc mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $result->Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

        // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $result->Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

        // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $result->Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

        // 8 --------------------------------------------------------------------------------------

        // Tổng số dân tộc nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = $result->Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;

        // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = $result->Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5;

        // Tổng dân số nữ mù chữ ở mức độ 2 chưa hoàn thành lớp 5 độ tuổi từ 15 đến 25, từ 15 đến 35, từ 15 đến 60
        $count_dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = $result->Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5;


        
        
        

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

        $exists = DB::selectOne("
            SELECT ward_id, nam_dieu_tra 
            FROM wards_report 
            WHERE ward_id = ? AND nam_dieu_tra = ?
            LIMIT 1
        ", [$wardId, $namDieuTra]);

        if ($exists){

        DB::update("
            UPDATE wards_report SET 
                tong_dan_so = ?,
                dan_so_tu_15_den_25_tuoi = ?,
                dan_so_tu_15_den_35_tuoi = ?,
                dan_so_tu_15_den_60_tuoi = ?,
                gioi_tinh_nam = ?, 
                gioi_tinh_nu = ?,
                gioi_tinh_nu_tu_15_den_25_tuoi = ?, 
                gioi_tinh_nu_tu_15_den_35_tuoi = ?, 
                gioi_tinh_nu_tu_15_den_60_tuoi = ?,
                dan_toc = ?,
                dan_toc_tu_15_den_25_tuoi = ?,
                dan_toc_tu_15_den_35_tuoi = ?,
                dan_toc_tu_15_den_60_tuoi = ?,
                nu_dan_toc = ?,
                nu_dan_toc_tu_15_den_25_tuoi = ?,
                nu_dan_toc_tu_15_den_35_tuoi = ?,
                nu_dan_toc_tu_15_den_60_tuoi = ?,

                Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = ?,
                Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = ?,
                Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = ?,

                Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = ?,
                Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = ?,
                Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = ?,

                Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = ?,
                Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = ?,
                Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = ?,

                Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3 = ?,
                Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3 = ?,
                Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3 = ?,

                

                Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = ?,
                Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = ?,
                Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = ?,

                Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = ?,
                Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = ?,
                Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = ?,

                Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = ?,
                Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = ?,
                Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = ?,
                
                Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5 = ?,
                Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5 = ?,
                Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5 = ?


            WHERE ward_id = ? AND nam_dieu_tra = ?
        ", [
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

            $wardId,
            $countNamDieuTra
        ]);
    } else{

        DB::insert("
            INSERT INTO wards_report (ward_id, nam_dieu_tra, tong_dan_so, dan_so_tu_15_den_25_tuoi, dan_so_tu_15_den_35_tuoi, dan_so_tu_15_den_60_tuoi, gioi_tinh_nam, gioi_tinh_nu, gioi_tinh_nu_tu_15_den_25_tuoi, gioi_tinh_nu_tu_15_den_35_tuoi, gioi_tinh_nu_tu_15_den_60_tuoi,
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
            Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,

            created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $wardId,
            $countNamDieuTra,
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
            now(),
            now()
            ]);
        }
    }
}
