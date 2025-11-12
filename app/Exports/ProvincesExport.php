<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;


class ProvincesExport implements WithHeadings, FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = DB::select("SELECT * FROM provinces_report");
        $provincesCollection = collect($data);
        // dd($provincesCollection);
        return $provincesCollection;
    }

    public function headings(): array
    {
        return [
            "Province Id",
            "Tên phường",
            "Thuộc tỉnh",
            "Mã số đơn vị hành chính",
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
            "Khuyết tất khác",
            "Có chứng nhận khuyết tật",
            "Khuyết tật có khả năng học tập",
            "Hoàn cảnh đặc biệt",
            "Quan hệ với chủ họ",
            "Ngày tạo",
            "Ngày cập nhật"
        ];
    }
}
