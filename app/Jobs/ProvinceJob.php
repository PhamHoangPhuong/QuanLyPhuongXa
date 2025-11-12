<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;

class ProvinceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    protected $namDieuTra;

    public function __construct($namDieuTra)
    {
        $this->namDieuTra = $namDieuTra;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $id = Auth::guard('api')->user();
        DB::update("
            UPDATE provinces_report
            SET province_id = ?, nam_dieu_tra = ?
        ", [$id->province_id, $namDieuTra]);

        $rows = DB::select("
            SELECT SUM(tren_18_tuoi) as tren_18_tuoi FROM wards_report
        ");

        $countOver18 = $rows[0]->tren_18_tuoi;

        $tong_gioi_tinh_nam = DB::select("
            SELECT SUM(gioi_tinh_nam) as nam FROM wards_report
        ");

        $countNam = $tong_gioi_tinh_nam[0]->nam;

        $tong_gioi_tinh_nu = DB::select("
                SELECT SUM(gioi_tinh_nu) as nu FROM wards_report
        ");

        $countNu = $tong_gioi_tinh_nu[0]->nu;

        //---------------------------------------------------
        $tong_dan_toc_kinh = DB::select("
            SELECT SUM(dan_toc_kinh) as dan_toc_kinh FROM wards_report
        ");

        $countDanTocKinh = $tong_dan_toc_kinh[0]->dan_toc_kinh;

        $tong_dan_toc_khac = DB::select("
            SELECT SUM(dan_toc_khac) as dan_toc_khac FROM wards_report
        ");

        $countDanTocKhac = $tong_dan_toc_khac[0]->dan_toc_khac;

        $tong_dan_toc_kinh = DB::select("
            SELECT SUM(dan_toc_kinh) as dan_toc_kinh FROM wards_report
        ");

        $countDanTocKinh = $tong_dan_toc_kinh[0]->dan_toc_kinh;

        $tong_khong_theo_ton_giao = DB::select("
            SELECT SUM(khong_theo_ton_giao) as khong_theo_ton_giao FROM wards_report
        ");

        $countTongKhongTheoTonGiao = $tong_khong_theo_ton_giao[0]->khong_theo_ton_giao;

        $tong_co_theo_ton_giao = DB::select("
            SELECT SUM(co_theo_ton_giao) as co_theo_ton_giao FROM wards_report
        ");

        $countTongCoTheoTonGiao = $tong_co_theo_ton_giao[0]->co_theo_ton_giao;

        //---------------------------------------------------
        $tong_co_thuoc_dien_uu_tien = DB::select("
            SELECT SUM(co_thuoc_dien_uu_tien) as co_thuoc_dien_uu_tien FROM wards_report
        ");

        $countCoThuocDienUuTien = $tong_co_thuoc_dien_uu_tien[0]->co_thuoc_dien_uu_tien;

        $tong_ma_truong = DB::select("
            SELECT SUM(ma_truong) as ma_truong FROM wards_report
        ");

        $countMaTruong = $tong_ma_truong[0]->ma_truong;
        //---------------------------------------------------

        $tong_bac_tot_nghiep_thpt = DB::select("
            SELECT SUM(bac_tot_nghiep_thpt) as bac_tot_nghiep_thpt FROM wards_report
        ");

        $countBacTotNghiepTHPT = $tong_bac_tot_nghiep_thpt[0]->bac_tot_nghiep_thpt;

        $tong_bac_tot_nghiep_thcs = DB::select("
            SELECT SUM(bac_tot_nghiep_thcs) as bac_tot_nghiep_thcs FROM wards_report
        ");

        $countBacTotNghiepTHCS = $tong_bac_tot_nghiep_thcs[0]->bac_tot_nghiep_thcs;

        $tong_bac_tot_nghiep_th = DB::select("
            SELECT SUM(bac_tot_nghiep_th) as bac_tot_nghiep_th FROM wards_report
        ");

        $countBacTotNghiepTH = $tong_bac_tot_nghiep_th[0]->bac_tot_nghiep_th;

        $tong_bac_tot_nghiep_mn = DB::select("
            SELECT SUM(bac_tot_nghiep_mn) as bac_tot_nghiep_mn FROM wards_report
        ");

        $countBacTotNghiepMN = $tong_bac_tot_nghiep_mn[0]->bac_tot_nghiep_mn;

        $tong_khong_co_bac_tot_nghiep = DB::select("
            SELECT SUM(khong_co_bac_tot_nghiep) as khong_co_bac_tot_nghiep FROM wards_report
        ");

        $countKhongCoBacTotNghiep = $tong_khong_co_bac_tot_nghiep[0]->khong_co_bac_tot_nghiep;
        
        //---------------------------------------------------
        $tong_da_tot_nghiep = DB::select("
            SELECT SUM(da_tot_nghiep) as da_tot_nghiep FROM wards_report
        ");

        $countDaTotNghiep = $tong_da_tot_nghiep[0]->da_tot_nghiep;

        $tong_bac_tn_nghe_dai_hoc = DB::select("
            SELECT SUM(bac_tn_nghe_dai_hoc) as bac_tn_nghe_dai_hoc FROM wards_report
        ");

        $countBacTnNgheDaiHoc = $tong_bac_tn_nghe_dai_hoc[0]->bac_tn_nghe_dai_hoc;

        $tong_bac_tn_nghe_cao_dang = DB::select("
            SELECT SUM(bac_tn_nghe_cao_dang) as bac_tn_nghe_cao_dang FROM wards_report
        ");

        $countBacTnNgheCaoDang = $tong_bac_tn_nghe_cao_dang[0]->bac_tn_nghe_cao_dang;

        $tong_bac_tn_nghe_khac = DB::select("
            SELECT SUM(bac_tn_nghe_khac) as bac_tn_nghe_khac FROM wards_report
        ");

        $countBacTnNgheKhac = $tong_bac_tn_nghe_khac[0]->bac_tn_nghe_khac;

        $tong_khong_co_bac_tn_nghe = DB::select("
            SELECT SUM(khong_co_bac_tn_nghe) as khong_co_bac_tn_nghe FROM wards_report
        ");

        $countKhongCoBacTnNghe = $tong_khong_co_bac_tn_nghe[0]->khong_co_bac_tn_nghe;

        $tong_da_tot_nghiep_nghe = DB::select("
            SELECT SUM(da_tot_nghiep_nghe) as da_tot_nghiep_nghe FROM wards_report
        ");

        $countDaTotNghiepNghe = $tong_da_tot_nghiep_nghe[0]->da_tot_nghiep_nghe;
        //---------------------------------------------------
        $tong_khuyet_tat_van_dong = DB::select("
            SELECT SUM(khuyet_tat_van_dong) as khuyet_tat_van_dong FROM wards_report
        ");

        $countKhuyetTatVanDong = $tong_khuyet_tat_van_dong[0]->khuyet_tat_van_dong;

        $tong_khuyet_tat_nghe_noi = DB::select("
            SELECT SUM(khuyet_tat_nghe_noi) as khuyet_tat_nghe_noi FROM wards_report
        ");

        $countKhuyetTatNgheNoi = $tong_khuyet_tat_nghe_noi[0]->khuyet_tat_nghe_noi;

        $tong_khuyet_tat_nhin = DB::select("
            SELECT SUM(khuyet_tat_nhin) as khuyet_tat_nhin FROM wards_report
        ");

        $countKhuyetTatNhin = $tong_khuyet_tat_nhin[0]->khuyet_tat_nhin;

        $tong_khuyet_tat_than_kinh = DB::select("
            SELECT SUM(khuyet_tat_than_kinh) as khuyet_tat_than_kinh FROM wards_report
        ");

        $countKhuyetTatThanKinh = $tong_khuyet_tat_than_kinh[0]->khuyet_tat_than_kinh;

        $tong_khuyet_tat_tri_tue = DB::select("
            SELECT SUM(khuyet_tat_tri_tue) as khuyet_tat_tri_tue FROM wards_report
        ");

        $countKhuyetTatTriTue = $tong_khuyet_tat_tri_tue[0]->khuyet_tat_tri_tue;

        $tong_khuyet_tat_hoc_tap = DB::select("
            SELECT SUM(khuyet_tat_hoc_tap) as khuyet_tat_hoc_tap FROM wards_report
        ");

        $countKhuyetTatHocTap = $tong_khuyet_tat_hoc_tap[0]->khuyet_tat_hoc_tap;

        $tong_tu_ky = DB::select("
            SELECT SUM(tu_ky) as tu_ky FROM wards_report
        ");

        $countTuKy = $tong_tu_ky[0]->tu_ky;

        $tong_khuyet_tat_khac = DB::select("
            SELECT SUM(khuyet_tat_khac) as khuyet_tat_khac FROM wards_report
        ");

        $countKhuyetTatKhac = $tong_khuyet_tat_khac[0]->khuyet_tat_khac;

        $tong_co_chung_nhan_khuyet_tat = DB::select("
            SELECT SUM(co_chung_nhan_khuyet_tat) as co_chung_nhan_khuyet_tat FROM wards_report
        ");

        $countCoChungNhanKhuyetTat = $tong_co_chung_nhan_khuyet_tat[0]->co_chung_nhan_khuyet_tat;

        $tong_khuyet_tat_co_kha_nang_hoc_tap = DB::select("
            SELECT SUM(khuyet_tat_co_kha_nang_hoc_tap) as khuyet_tat_co_kha_nang_hoc_tap FROM wards_report
        ");

        $countKhuyetTatCoKhaNangHocTap = $tong_khuyet_tat_co_kha_nang_hoc_tap[0]->khuyet_tat_co_kha_nang_hoc_tap;

        $tong_hoan_canh_dac_biet = DB::select("
            SELECT SUM(hoan_canh_dac_biet) as hoan_canh_dac_biet FROM wards_report
        ");

        $countHoanCanhDacBiet = $tong_hoan_canh_dac_biet[0]->hoan_canh_dac_biet;

        $tong_quan_he_voi_chu_ho = DB::select("
            SELECT SUM(quan_he_voi_chu_ho) as quan_he_voi_chu_ho FROM wards_report
        ");

        $countQuanHeVoiChuHo = $tong_quan_he_voi_chu_ho[0]->quan_he_voi_chu_ho;

        DB::insert("
            INSERT INTO provinces_report (
                tren_18_tuoi, gioi_tinh_nam, gioi_tinh_nu, dan_toc_kinh, dan_toc_khac, khong_theo_ton_giao, co_theo_ton_giao, co_thuoc_dien_uu_tien, ma_truong,
                bac_tot_nghiep_thpt, bac_tot_nghiep_thcs, bac_tot_nghiep_th, bac_tot_nghiep_mn, khong_co_bac_tot_nghiep, da_tot_nghiep, bac_tn_nghe_dai_hoc, bac_tn_nghe_cao_dang, bac_tn_nghe_khac, khong_co_bac_tn_nghe, da_tot_nghiep_nghe,
                khuyet_tat_van_dong, khuyet_tat_nghe_noi, khuyet_tat_nhin, khuyet_tat_than_kinh, khuyet_tat_tri_tue, khuyet_tat_hoc_tap, tu_ky, khuyet_tat_khac, co_chung_nhan_khuyet_tat, khuyet_tat_co_kha_nang_hoc_tap, hoan_canh_dac_biet, quan_he_voi_chu_ho
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $countOver18,
            $countNam,
            $countNu,
            $countDanTocKinh,
            $countDanTocKhac,
            $countTongKhongTheoTonGiao,
            $countTongCoTheoTonGiao,
            $countCoThuocDienUuTien,
            $countMaTruong,
            $countBacTotNghiepTHPT,
            $countBacTotNghiepTHCS,
            $countBacTotNghiepTH,
            $countBacTotNghiepMN,
            $countKhongCoBacTotNghiep,
            $countDaTotNghiep,
            $countBacTnNgheDaiHoc,
            $countBacTnNgheCaoDang,
            $countBacTnNgheKhac,
            $countKhongCoBacTnNghe,
            $countDaTotNghiepNghe,
            $countKhuyetTatVanDong,
            $countKhuyetTatNgheNoi,
            $countKhuyetTatNhin,
            $countKhuyetTatThanKinh,
            $countKhuyetTatTriTue,
            $countKhuyetTatHocTap,
            $countTuKy,
            $countKhuyetTatKhac,
            $countCoChungNhanKhuyetTat,
            $countKhuyetTatCoKhaNangHocTap,
            $countHoanCanhDacBiet,
            $countQuanHeVoiChuHo
        ]);
    }
}
