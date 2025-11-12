<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Auth;



class WardsExport implements WithHeadings, FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents
{

	protected $namDieuTra;
    protected $id;

	public function __construct($namDieuTra, $id)
	{
        $this->id = $id;
		$this->namDieuTra = $namDieuTra;
	}

    public function collection()
    {
        
        $ward = DB::select("
        
            SELECT w.ten_phuong, p.ten_tinh, wr.tren_18_tuoi, wr.gioi_tinh_nam, wr.gioi_tinh_nu, wr.dan_toc_kinh, wr.dan_toc_khac, wr.khong_theo_ton_giao, wr.co_theo_ton_giao,
                wr.co_thuoc_dien_uu_tien, wr.ma_truong, wr.bac_tot_nghiep_thpt, wr.bac_tot_nghiep_thcs, wr.bac_tot_nghiep_th, wr.bac_tot_nghiep_mn, wr.khong_co_bac_tot_nghiep, wr.da_tot_nghiep, wr.bac_tn_nghe_dai_hoc, wr.bac_tn_nghe_cao_dang, wr.bac_tn_nghe_khac, wr.khong_co_bac_tn_nghe, wr.da_tot_nghiep_nghe,
                wr.khuyet_tat_van_dong, wr.khuyet_tat_nghe_noi, wr.khuyet_tat_nhin, wr.khuyet_tat_than_kinh, wr.khuyet_tat_tri_tue, wr.khuyet_tat_hoc_tap, wr.tu_ky, wr.khuyet_tat_khac, wr.co_chung_nhan_khuyet_tat, wr.khuyet_tat_co_kha_nang_hoc_tap, wr.hoan_canh_dac_biet, wr.quan_he_voi_chu_ho    
            FROM wards_report wr
            JOIN wards w 
                ON wr.ward_id = w.ward_id 
            JOIN provinces p
                ON w.province_id = p.province_id
            WHERE wr.province_id = ? AND wr.nam_dieu_tra = ?
        ", [$this->id->province_id, $this->namDieuTra]);

        $total = DB::select("
            SELECT '' as temp,
                   '' as temp2,
                SUM(tren_18_tuoi) as tren_18_tuoi,
                SUM(gioi_tinh_nam) as nam,
                SUM(gioi_tinh_nu) as nu,
                SUM(dan_toc_kinh) as dan_toc_kinh,
                SUM(dan_toc_khac) as dan_toc_khac,
                SUM(khong_theo_ton_giao) as khong_theo_ton_giao,
                SUM(co_theo_ton_giao) as co_theo_ton_giao,
                SUM(co_thuoc_dien_uu_tien) as co_thuoc_dien_uu_tien,
                SUM(ma_truong) as ma_truong,
                SUM(bac_tot_nghiep_thpt) as bac_tot_nghiep_thpt,
                SUM(bac_tot_nghiep_thcs) as bac_tot_nghiep_thcs,
                SUM(bac_tot_nghiep_th) as bac_tot_nghiep_th,
                SUM(bac_tot_nghiep_mn) as bac_tot_nghiep_mn,
                SUM(khong_co_bac_tot_nghiep) as khong_co_bac_tot_nghiep,
                SUM(da_tot_nghiep) as da_tot_nghiep,
                SUM(bac_tn_nghe_dai_hoc) as bac_tn_nghe_dai_hoc,
                SUM(bac_tn_nghe_cao_dang) as bac_tn_nghe_cao_dang,
                SUM(bac_tn_nghe_khac) as bac_tn_nghe_khac,
                SUM(khong_co_bac_tn_nghe) as khong_co_bac_tn_nghe,
                SUM(da_tot_nghiep_nghe) as da_tot_nghiep_nghe,
                SUM(khuyet_tat_van_dong) as khuyet_tat_van_dong,
                SUM(khuyet_tat_nghe_noi) as khuyet_tat_nghe_noi,
                SUM(khuyet_tat_nhin) as khuyet_tat_nhin,
                SUM(khuyet_tat_than_kinh) as khuyet_tat_than_kinh,
                SUM(khuyet_tat_tri_tue) as khuyet_tat_tri_tue,
                SUM(khuyet_tat_hoc_tap) as khuyet_tat_hoc_tap,
                SUM(tu_ky) as tu_ky,
                SUM(khuyet_tat_khac) as khuyet_tat_khac,
                SUM(co_chung_nhan_khuyet_tat) as co_chung_nhan_khuyet_tat,
                SUM(khuyet_tat_co_kha_nang_hoc_tap) as khuyet_tat_co_kha_nang_hoc_tap,
                SUM(hoan_canh_dac_biet) as hoan_canh_dac_biet,
                SUM(quan_he_voi_chu_ho) as quan_he_voi_chu_ho
            FROM wards_report
            WHERE province_id = ? AND nam_dieu_tra = ?   
        ", [$this->id->province_id, $this->namDieuTra]);


        $wardsCollection = collect($ward);
        $wardsCollection->push($total[0]);
        // dd($wardsCollection);
        return $wardsCollection;
    }

    public function headings(): array
    {
        return [

            [
                "Tên phường",
                "Thuộc tỉnh",
                "Trên 18 tuổi",
                "Nam",
                "Nữ",
                "Dân tộc kinh",
                "Dân tộc khác",
                "Không theo tôn giáo",
                "Có theo tôn giáo",
                "Thuộc diện ưu tiên",
                "Số lượng mã trường",
                "Bậc tốt nghiệp thpt",
                "Bậc tốt nghiệp thcs",
                "Bậc tốt nghiệp th",
                "Bậc tốt nghiệp mn",
                "Không có bậc tốt nghiệp",
                "Đã tốt nghiệp",
                "Bậc tốt nghiệp nghề đại học",
                "Bậc tốt nghiệp nghề cao đẳng",
                "Bậc tốt nghiệp nghề khác",
                "Không có bậc tốt nghiệp nghề",
                "Đã tốt nghiệp nghề",
                "Khuyết tật vận động",
                "Khuyết tật nghe nói",
                "Khuyết tật nhìn",
                "Khuyết tất thần kinh",
                "Khuyết tật trí tuệ",
                "Khuyết tật học tập",
                "Tự kỷ",
                "Khuyết tật khác",
                "Có chứng nhận khuyết tật",
                "Khuyết tật có khả năng học tập",
                "Hoàn cảnh đặc biệt",
                "Quan hệ với chủ họ",
            ]
        ];
    }

    public function startCell(): string
    {
        return 'C7';
    }

    public function registerEvents(): array
    {
        
        return [
            AfterSheet::class => function(AfterSheet $event) {
                

                $event->sheet->mergeCells('B1:M3');
                $event->sheet->setCellValue('B1', 'TỔNG HỢP DỮ LIỆU CỦA CÁC PHƯỜNG NĂM '. $this->namDieuTra);

                $event->sheet->mergeCells('B5:B7');
                $event->sheet->setCellValue('B5', 'STT');

                $event->sheet->mergeCells('C5:D5');
                $event->sheet->setCellValue('C5', 'Tên đơn vị');

                $event->sheet->setCellValue('E5', 'Dân số trên 18 tuổi');

                $event->sheet->mergeCells('F5:I5');
                $event->sheet->setCellValue('F5', 'Tổng dân số');

                $event->sheet->mergeCells('F6:G6');
                $event->sheet->setCellValue('F6', 'Giới tính');

                $event->sheet->mergeCells('H6:I6');
                $event->sheet->setCellValue('H6', 'Dân tộc');

                $event->sheet->mergeCells('J5:K5');
                $event->sheet->setCellValue('J5', 'Số lượng theo tôn giáo');

                $event->sheet->setCellValue('L5', 'Diện ưu tiên');

                $event->sheet->setCellValue('M5', 'Mã trường');

                $event->sheet->mergeCells('N5:S5');
                $event->sheet->setCellValue('N5', 'Bậc tốt nghiệp');

                $event->sheet->mergeCells('T5:X5');
                $event->sheet->setCellValue('T5', 'Bậc tốt nghiệp nghề');

                $event->sheet->mergeCells('Y5:AH5');
                $event->sheet->setCellValue('Y5', 'Khuyết tật');

                $event->sheet->setCellValue('AI5', 'Hoàn cảnh');

                $event->sheet->setCellValue('AJ5', 'Quan hệ');

                $event->sheet->mergeCells('B13:E13');
                $event->sheet->setCellValue('B13', 'Người lập bảng: '. $this->id->name );

                $event->sheet->mergeCells('K13:M13');
                $event->sheet->setCellValue('K13', 'Ngày' . " " .'Tháng' . " " . 'Năm: ' . $this->namDieuTra );

                $event->sheet->mergeCells('K14:M14');
                $event->sheet->setCellValue('K14', 'TM. ỦY BAN NHAN DÂN' );

                $event->sheet->mergeCells('K15:M15');
                $event->sheet->setCellValue('K15', 'CHỦ TỊCH' );

                $event->sheet->mergeCells('K16:M16');
                $event->sheet->setCellValue('K16', '(ký tên và đóng dấu)' );


 
                $styleArray = [
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',  
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];

                $cols = [
                    'C' => 'Tên phường',
                    'D' => 'Thuộc tỉnh',
                    'E' => 'Trên 18 tuổi',
                    'J' => 'Không theo tôn giáo',
                    'K' => 'Có theo tôn giáo',
                    'L' => 'Thuộc diện ưu tiên',
                    'M' => 'Số lượng mã trường',
                    'N' => 'Bậc tốt nghiệp thpt',
                    'O' => 'Bậc tốt nghiệp thcs',
                    'P' => 'Bậc tốt nghiệp th',
                    'Q' => 'Bậc tốt nghiệp mn',
                    'R' => 'Không có bậc tốt nghiệp',
                    'S' => 'Đã tốt nghiệp',
                    'T' => 'Bậc tốt nghiệp nghề đại học',
                    'U' => 'Bậc tốt nghiệp nghề cao đẳng',
                    'V' => 'Bậc tốt nghiệp nghề khác',
                    'W' => 'Không có bậc tốt nghiệp nghề',
                    'X' => 'Đã tốt nghiệp nghề',
                    'Y' => 'Khuyết tật vân động',
                    'Z' => 'Khuyết tật nghe nói',
                    'AA' => 'Khuyết tật nhìn',
                    'AB' => 'Khuyết tật thần kinh',
                    'AC' => 'Khuyết tật trí tuệ',
                    'AD' => 'Khuyết tật học tập',
                    'AE' => 'Tự kỷ',
                    'AF' => 'Khuyết tật khác',
                    'AG' => 'Có chứng nhạn khuyết tật',
                    'AH' => 'Khuyết tật có khả năng học tập',
                    'AI' => 'Hoàn cảnh đặc biệt',
                    'AJ' => 'Quan hệ với chủ họ'

                ];
                foreach ($cols as $col => $text) {
                    $event->sheet->mergeCells("{$col}6:{$col}7");
                    $event->sheet->setCellValue("{$col}6", $text);
                    $event->sheet->getStyle("{$col}6:{$col}7")->applyFromArray($styleArray);
                }

                $event->sheet->getStyle('B1:M3')->applyFromArray($styleArray);
                $event->sheet->getStyle('B5:B7')->applyFromArray($styleArray);
                $event->sheet->getStyle('C5:D5')->applyFromArray($styleArray);
                $event->sheet->getStyle('E5')->applyFromArray($styleArray);
                $event->sheet->getStyle('F5:I5')->applyFromArray($styleArray);
                $event->sheet->getStyle('F6:G6')->applyFromArray($styleArray);
                $event->sheet->getStyle('H6:I6')->applyFromArray($styleArray);
                $event->sheet->getStyle('J5:K5')->applyFromArray($styleArray);
                $event->sheet->getStyle('L5')->applyFromArray($styleArray);
                $event->sheet->getStyle('M5')->applyFromArray($styleArray);
                $event->sheet->getStyle('N5:S5')->applyFromArray($styleArray);
                $event->sheet->getStyle('T5:X5')->applyFromArray($styleArray);
                $event->sheet->getStyle('Y5:AH5')->applyFromArray($styleArray);
                $event->sheet->getStyle('AI5')->applyFromArray($styleArray);
                $event->sheet->getStyle('AJ5')->applyFromArray($styleArray);
                $event->sheet->getStyle('B13:D13')->applyFromArray($styleArray);
                $event->sheet->getStyle('K13:M13')->applyFromArray($styleArray);
                $event->sheet->getStyle('K14:M14')->applyFromArray($styleArray);
                $event->sheet->getStyle('K15:M15')->applyFromArray($styleArray);
                $event->sheet->getStyle('K16:M16')->applyFromArray($styleArray);



                $highestRow = $event->sheet->getHighestRow();
                // dd($highestRow);

                for ($row = 1; $row < $highestRow - 11; $row++){
                    $event->sheet->setCellValue('B'. $row + 7, $row);
                }

                $dataRange = 'B5:AJ' . $highestRow - 5;


                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];

                $event->sheet->getStyle($dataRange)->applyFromArray($dataStyle);
            },
        ];
    }
}
