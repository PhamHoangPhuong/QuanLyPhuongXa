<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsersImport implements WithMultipleSheets
{
	protected $sessionId;
	public $failCount = 0;

	public function __construct($sessionId = null)
	{
		$this->sessionId = $sessionId;
	}

	public function sheets(): array
	{
		return [
			new FirstSheetImport($this->sessionId, $this) // This will handle the first sheet
			// Add other sheet import classes here if you have more sheets
		];
	}
}

class FirstSheetImport implements ToCollection, WithStartRow
{
	/**
	 * @param array $row
	 *
	 * @return \Illuminate\Database\Eloquent\Model|null
	 */

	protected $sessionId;
	protected $parentImport;

	public function __construct($sessionId = null, UsersImport $parentImport)
	{
		$this->sessionId = $sessionId;
		$this->parentImport = $parentImport;
	}


	public function collection(Collection $rows)
	{
		$checkboxFields = [
			'tai_mu_chu_muc',
			'khuyet_tat_van_dong',
			'khuyet_tat_nghe_noi',
			'khuyet_tat_nhin',
			'khuyet_tat_than_kinh',
			'khuyet_tat_tri_tue',
			'khuyet_tat_hoc_tap',
			'tu_ky',
			'khuyet_tat_khac',
			'co_chung_nhan_khuyet_tat',
			'kha_nang_hoc_tap'
		];


		$columnMap = [
			'tt',
			'ho_dem',
			'ten',
			'ngay',
			'thang',
			'nam_sinh',
			'gioi_tinh',
			'dan_toc',
			'ton_giao',
			'dien_uu_tien',
			'ho_dem_cua_chu_ho',
			'ten_cua_chu_ho',
			'dia_chi',
			'so_phieu',
			'dien_cu_tru',
			'tinh_trang_cu_tru',
			'khoi_hoc',
			'lop_hoc',
			'quan_huyen_dang_hoc',
			'ten_truong_dang_hoc',
			'ma_truong',
			'bac_tot_nghiep',
			'bo_tuc',
			'nam_tot_nghiep',
			'bac_tn_nghe',
			'so_bang_tot_nghiep',
			'nam_tn_nghe',
			'hoc_xong_lop',
			'hoc_xong_nam',
			'bo_hoc_lop',
			'bo_hoc_nam',
			'dang_hoc_lop',
			'hoan_thanh_lop',
			'tai_mu_chu_muc',
			'khuyet_tat_van_dong',
			'khuyet_tat_nghe_noi',
			'khuyet_tat_nhin',
			'khuyet_tat_than_kinh',
			'khuyet_tat_tri_tue',
			'khuyet_tat_hoc_tap',
			'tu_ky',
			'khuyet_tat_khac',
			'co_chung_nhan_khuyet_tat',
			'kha_nang_hoc_tap',
			'hoan_canh_dac_biet',
			'chi_tiet_hoan_canh',
			'quan_he_voi_chu_ho',
			'ho_ten_cha_me',
			'dien_thoai',
			'ghi_chu',
		];
		$year = null;
		foreach ($rows as $indexRow => $row) {

			if($indexRow == 1) {
				$year = $row[0];
				
			}
			if($indexRow < 4) {
				continue;
			}


			// if($indexRow == 0) {
			// 	$year = $row[0];
				
			// }
			// if($indexRow < 3) {
			// 	continue;
			// }
			$data = [];



			foreach ($columnMap as $index => $dbKey) {
				$value = $row[$index + 1] ?? null;
				
				$normalized = strtolower(trim((string) $value));

				if ($dbKey === 'gioi_tinh') {
					$data[$dbKey] = $normalized === 'x' ? 0 : 1;
				} elseif (in_array($dbKey, $checkboxFields)) {
					$data[$dbKey] = $normalized === 'x' ? 1 : 0;
				} else {
					$data[$dbKey] = $value;
				}
			}

			if (!empty($data['dia_chi']) && str_contains($data['dia_chi'], 'Xuyên Đông')) {
				$data['tinh_thanh'] = 'Thành phố Long Xuyên, tỉnh An Giang';
			}

			$data['nam_dieu_tra'] = $year;
			// $data['dia_chi'] = 'Mỹ Long';

			// $data['dia_chi'] = 'Mỹ Bình';


			if (!empty(array_filter($data))) {
				$data['session_id'] = $this->sessionId;

				$columns = array_keys($data);
				$placeholders = implode(',', array_fill(0, count($columns), '?'));
				$columnsSql = implode(',', array_map(fn($col) => "`$col`", $columns));
				$values = array_values($data);

				$sql = "INSERT INTO usersimport ($columnsSql) VALUES ($placeholders)";
				DB::statement($sql, $values);

			} else {

				$this->parentImport->failCount++;
			}
		}
	}

	public function chunkSize(): int
	{
		return 1000;
	}

	//không cần phải mapping kiểu 1 => mà là đếm theo từ 0,1,2,3,.. trong excel chỉ có headingrow thì mới mapping theo kiểu 1 =>
	public function startRow(): int
	{
		return 1;
	}
}