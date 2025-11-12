<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;

class InsertExcel extends Controller
{
    public function insert(Request $request)
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

        $namDieuTra = $request->input('nam_dieu_tra');
        $id = Auth::guard('api')->user();


        $wards = DB::select("
            SELECT * FROM wards
            JOIN wards_report ON wards_report.ward_id = wards.ward_id
            WHERE nam_dieu_tra = ?
        ", [$namDieuTra]);
        // dd($wards);


        // $Ward = array_map(fn($row) => [
        //     'nam' => $row
        // ], $wards);
        // dd($Ward);

        //không nên đặt sau foreach vì nó sẽ chỉ lấy một row nên đặt trước foreach
        $startRow = $sheet->getHighestRow() - 69; 
        $currentRow = $startRow;

        $index = 1;

        

        foreach ($wards as $ward){

            $col = [
                'B' => $ward->ten_phuong,
                'C' => $ward->tong_dan_so,
                'D' => $ward->gioi_tinh_nu,
                'E' => $ward->dan_toc,
                'F' => $ward->nu_dan_toc,


                'G' => $ward->dan_so_tu_15_den_25_tuoi,
                'H' => $ward->gioi_tinh_nu_tu_15_den_25_tuoi,
                'I' => $ward->dan_toc_tu_15_den_25_tuoi,
                'J' => $ward->nu_dan_toc_tu_15_den_25_tuoi,

                'K' => $ward->Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                'M' => $ward->Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                'O' => $ward->Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,
                'Q' => $ward->Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,

                'S' => $ward->Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                'U' => $ward->Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                'W' => $ward->Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,
                'Y' => $ward->Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5,

                'AA' => $ward->dan_so_tu_15_den_35_tuoi,
                'AB' => $ward->gioi_tinh_nu_tu_15_den_35_tuoi,
                'AC' => $ward->dan_toc_tu_15_den_35_tuoi,
                'AD' => $ward->nu_dan_toc_tu_15_den_35_tuoi,

                'AE' => $ward->Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                'AG' => $ward->Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                'AI' => $ward->Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3,
                'AK' => $ward->Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3,

                'AM' => $ward->Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                'AO' => $ward->Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                'AQ' => $ward->Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,
                'AS' => $ward->Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5,


                'AU' => $ward->dan_so_tu_15_den_60_tuoi,
                'AV' => $ward->gioi_tinh_nu_tu_15_den_60_tuoi,
                'AW' => $ward->dan_toc_tu_15_den_60_tuoi,
                'AX' => $ward->nu_dan_toc_tu_15_den_60_tuoi,

                'AY' => $ward->Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,
                'BA' => $ward->Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,
                'BC' => $ward->Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,
                'BE' => $ward->Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3,

                'BG' => $ward->Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,
                'BI' => $ward->Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,
                'BK' => $ward->Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,
                'BM' => $ward->Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5,
                
            ];

            
            
            $rowData = $sheet->rangeToArray("C{$currentRow}:BN{$currentRow}", null, $calculateFormulas = true, $formatData = true, $returnCellRef = true);

            $hasData = false;
            foreach ($rowData[$currentRow] as $cellValue) {
                if (!empty($cellValue)) {
                    $hasData = true;
                    break;
                }
            }


            if ($hasData) {
                $sheet->insertNewRowBefore($currentRow, 1);
            }

            foreach ($col as $colIndex => $cols){
                $sheet->setCellValue("{$colIndex}{$currentRow}", $cols);
            }

            $columns = [
                'L' => "=(K{$currentRow}/G{$currentRow})*100",
                'N' => "=(M{$currentRow}/H{$currentRow})*100",
                'P' => "=(O{$currentRow}/I{$currentRow})*100",
                'R' => "=(Q{$currentRow}/J{$currentRow})*100",

                'T' => "=(S{$currentRow}/G{$currentRow})*100",
                'V' => "=(U{$currentRow}/H{$currentRow})*100",
                'X' => "=(W{$currentRow}/I{$currentRow})*100",
                'Z' => "=(Y{$currentRow}/J{$currentRow})*100",

                'AF' => "=(AE{$currentRow}/AA{$currentRow})*100",
                'AH' => "=(AG{$currentRow}/AB{$currentRow})*100",
                'AJ' => "=(AI{$currentRow}/AC{$currentRow})*100",
                'AL' => "=(AK{$currentRow}/AD{$currentRow})*100",

                'AN' => "=(AM{$currentRow}/AA{$currentRow})*100",
                'AP' => "=(AO{$currentRow}/AB{$currentRow})*100",
                'AR' => "=(AQ{$currentRow}/AC{$currentRow})*100",
                'AT' => "=(AS{$currentRow}/AD{$currentRow})*100",

                'AZ' => "=(AY{$currentRow}/AA{$currentRow})*100",
                'BB' => "=(BA{$currentRow}/AB{$currentRow})*100",
                'BD' => "=(BC{$currentRow}/AC{$currentRow})*100",
                'BF' => "=(BE{$currentRow}/AD{$currentRow})*100",

                'BH' => "=(BG{$currentRow}/AA{$currentRow})*100",
                'BJ' => "=(BI{$currentRow}/AB{$currentRow})*100",
                'BL' => "=(BK{$currentRow}/AC{$currentRow})*100",
                'BN' => "=(BM{$currentRow}/AD{$currentRow})*100",
            ];

            foreach ($columns as $col => $formula) {
                $sheet->setCellValue("{$col}{$currentRow}", $formula);
            }

            $sheet->setCellValue("A{$currentRow}", $index);
            $index++;


            $currentRow++;
        }

        $sumRow = $currentRow; 

        $sheet->setCellValue("C{$sumRow}", "=SUM(C{$startRow}:C".($currentRow-1).")");
        $sheet->setCellValue("D{$sumRow}", "=SUM(D{$startRow}:D".($currentRow-1).")");
        $sheet->setCellValue("E{$sumRow}", "=SUM(E{$startRow}:E".($currentRow-1).")");
        $sheet->setCellValue("F{$sumRow}", "=SUM(F{$startRow}:F".($currentRow-1).")");


        $sheet->setCellValue("G{$sumRow}", "=SUM(G{$startRow}:G".($currentRow-1).")");
        $sheet->setCellValue("H{$sumRow}", "=SUM(H{$startRow}:H".($currentRow-1).")");
        $sheet->setCellValue("I{$sumRow}", "=SUM(I{$startRow}:I".($currentRow-1).")");
        $sheet->setCellValue("J{$sumRow}", "=SUM(J{$startRow}:J".($currentRow-1).")");


        $sheet->setCellValue("K{$sumRow}", "=SUM(K{$startRow}:K".($currentRow-1).")");
        $sheet->setCellValue("L{$sumRow}", "=SUM(L{$startRow}:L".($currentRow-1).")");
        $sheet->setCellValue("M{$sumRow}", "=SUM(M{$startRow}:M".($currentRow-1).")");
        $sheet->setCellValue("N{$sumRow}", "=SUM(N{$startRow}:N".($currentRow-1).")");


        $sheet->setCellValue("O{$sumRow}", "=SUM(O{$startRow}:O".($currentRow-1).")");
        $sheet->setCellValue("P{$sumRow}", "=SUM(P{$startRow}:P".($currentRow-1).")");
        $sheet->setCellValue("Q{$sumRow}", "=SUM(Q{$startRow}:Q".($currentRow-1).")");
        $sheet->setCellValue("R{$sumRow}", "=SUM(R{$startRow}:R".($currentRow-1).")");


        $sheet->setCellValue("S{$sumRow}", "=SUM(S{$startRow}:S".($currentRow-1).")");
        $sheet->setCellValue("T{$sumRow}", "=SUM(T{$startRow}:T".($currentRow-1).")");
        $sheet->setCellValue("U{$sumRow}", "=SUM(U{$startRow}:U".($currentRow-1).")");
        $sheet->setCellValue("V{$sumRow}", "=SUM(V{$startRow}:V".($currentRow-1).")");


        $sheet->setCellValue("W{$sumRow}", "=SUM(W{$startRow}:W".($currentRow-1).")");
        $sheet->setCellValue("X{$sumRow}", "=SUM(X{$startRow}:X".($currentRow-1).")");
        $sheet->setCellValue("Y{$sumRow}", "=SUM(Y{$startRow}:Y".($currentRow-1).")");


        $sheet->setCellValue("Z{$sumRow}", "=SUM(Z{$startRow}:Z".($currentRow-1).")");
        $sheet->setCellValue("AA{$sumRow}", "=SUM(AA{$startRow}:AA".($currentRow-1).")");
        $sheet->setCellValue("AB{$sumRow}", "=SUM(AB{$startRow}:AB".($currentRow-1).")");


        $sheet->setCellValue("AC{$sumRow}", "=SUM(AC{$startRow}:AC".($currentRow-1).")");
        $sheet->setCellValue("AD{$sumRow}", "=SUM(AD{$startRow}:AD".($currentRow-1).")");
        $sheet->setCellValue("AE{$sumRow}", "=SUM(AE{$startRow}:AE".($currentRow-1).")");


        $sheet->setCellValue("AF{$sumRow}", "=SUM(AF{$startRow}:AF".($currentRow-1).")");
        $sheet->setCellValue("AG{$sumRow}", "=SUM(AG{$startRow}:AG".($currentRow-1).")");
        $sheet->setCellValue("AH{$sumRow}", "=SUM(AH{$startRow}:AH".($currentRow-1).")");


        $sheet->setCellValue("AI{$sumRow}", "=SUM(AI{$startRow}:AI".($currentRow-1).")");
        $sheet->setCellValue("AJ{$sumRow}", "=SUM(AJ{$startRow}:AJ".($currentRow-1).")");
        $sheet->setCellValue("AK{$sumRow}", "=SUM(AK{$startRow}:AK".($currentRow-1).")");


        $sheet->setCellValue("AL{$sumRow}", "=SUM(AL{$startRow}:AL".($currentRow-1).")");
        $sheet->setCellValue("AM{$sumRow}", "=SUM(AM{$startRow}:AM".($currentRow-1).")");
        $sheet->setCellValue("AN{$sumRow}", "=SUM(AN{$startRow}:AN".($currentRow-1).")");


        $sheet->setCellValue("AO{$sumRow}", "=SUM(AO{$startRow}:AO".($currentRow-1).")");
        $sheet->setCellValue("AP{$sumRow}", "=SUM(AP{$startRow}:AP".($currentRow-1).")");
        $sheet->setCellValue("AQ{$sumRow}", "=SUM(AQ{$startRow}:AQ".($currentRow-1).")");


        $sheet->setCellValue("AR{$sumRow}", "=SUM(AR{$startRow}:AR".($currentRow-1).")");
        $sheet->setCellValue("AS{$sumRow}", "=SUM(AS{$startRow}:AS".($currentRow-1).")");
        $sheet->setCellValue("AT{$sumRow}", "=SUM(AT{$startRow}:AT".($currentRow-1).")");


        $sheet->setCellValue("AU{$sumRow}", "=SUM(AU{$startRow}:AU".($currentRow-1).")");
        $sheet->setCellValue("AV{$sumRow}", "=SUM(AV{$startRow}:AV".($currentRow-1).")");
        $sheet->setCellValue("AW{$sumRow}", "=SUM(AW{$startRow}:AW".($currentRow-1).")");


        $sheet->setCellValue("AX{$sumRow}", "=SUM(AX{$startRow}:AX".($currentRow-1).")");
        $sheet->setCellValue("AY{$sumRow}", "=SUM(AY{$startRow}:AY".($currentRow-1).")");
        $sheet->setCellValue("AZ{$sumRow}", "=SUM(AZ{$startRow}:AZ".($currentRow-1).")");


        $sheet->setCellValue("BA{$sumRow}", "=SUM(BA{$startRow}:BA".($currentRow-1).")");
        $sheet->setCellValue("BB{$sumRow}", "=SUM(BB{$startRow}:BB".($currentRow-1).")");
        $sheet->setCellValue("BC{$sumRow}", "=SUM(BC{$startRow}:BC".($currentRow-1).")");


        $sheet->setCellValue("BD{$sumRow}", "=SUM(BD{$startRow}:BD".($currentRow-1).")");
        $sheet->setCellValue("BE{$sumRow}", "=SUM(BE{$startRow}:BE".($currentRow-1).")");
        $sheet->setCellValue("BF{$sumRow}", "=SUM(BF{$startRow}:BF".($currentRow-1).")");


        $sheet->setCellValue("BG{$sumRow}", "=SUM(BG{$startRow}:BG".($currentRow-1).")");
        $sheet->setCellValue("BH{$sumRow}", "=SUM(BH{$startRow}:BH".($currentRow-1).")");
        $sheet->setCellValue("BI{$sumRow}", "=SUM(BI{$startRow}:BI".($currentRow-1).")");


        $sheet->setCellValue("BJ{$sumRow}", "=SUM(BJ{$startRow}:BJ".($currentRow-1).")");
        $sheet->setCellValue("BK{$sumRow}", "=SUM(BK{$startRow}:BK".($currentRow-1).")");
        $sheet->setCellValue("BL{$sumRow}", "=SUM(BL{$startRow}:BL".($currentRow-1).")");


        $sheet->setCellValue("BM{$sumRow}", "=SUM(BM{$startRow}:BM".($currentRow-1).")");
        $sheet->setCellValue("BN{$sumRow}", "=SUM(BN{$startRow}:BN".($currentRow-1).")");


        $Row1 = $currentRow + 2;
        $Row2 = $currentRow + 1;

        $sheet->setCellValue("A{$Row1}", 'NGƯỜI LẬP BẢNG ' . ":" . $id->name);
        $sheet->setCellValue("AB{$Row2}", 'NĂM ' . ":" . $namDieuTra);
    

        $writer = new Xlsx($spreadsheet);
        $fileName = 'TongHop.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}