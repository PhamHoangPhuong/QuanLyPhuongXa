<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersImportTable extends Migration
{
    public function up()
    {
        Schema::create('usersimport', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_id')->nullable();

            $table->unsignedBigInteger('ward_id')->nullable();

            $table->year('nam_dieu_tra')->nullable();
            $table->string('tt')->nullable();
            $table->string('ho_dem')->nullable();
            $table->string('ten')->nullable();
            $table->string('ngay')->nullable();    
            $table->string('thang')->nullable();   
            $table->string('nam_sinh')->nullable();
            $table->tinyInteger('gioi_tinh')->default(0);
            $table->string('dan_toc')->nullable();
            $table->string('ton_giao')->nullable();
            $table->string('dien_uu_tien')->nullable();

            $table->string('ho_dem_cua_chu_ho')->nullable();
            $table->string('ten_cua_chu_ho')->nullable();
            $table->string('dia_chi')->nullable();
            $table->string('tinh_thanh')->nullable();
            $table->string('so_phieu')->nullable();
            $table->string('dien_cu_tru')->nullable();
            $table->string('tinh_trang_cu_tru')->nullable();
            $table->string('khoi_hoc')->nullable();
            $table->string('lop_hoc')->nullable();

            $table->string('quan_huyen_dang_hoc')->nullable();
            $table->string('ten_truong_dang_hoc')->nullable();
            $table->string('ma_truong')->nullable();
            $table->string('bac_tot_nghiep')->nullable();
            $table->string('bo_tuc')->nullable();
            $table->string('nam_tot_nghiep')->nullable();
            $table->string('bac_tn_nghe')->nullable();
            $table->string('so_bang_tot_nghiep')->nullable();
            $table->string('nam_tn_nghe')->nullable();
            $table->string('hoc_xong_lop')->nullable();
            $table->string('hoc_xong_nam')->nullable();
            $table->string('bo_hoc_lop')->nullable();
            $table->string('bo_hoc_nam')->nullable(); 

            $table->string('dang_hoc_lop')->nullable();
            $table->string('hoan_thanh_lop')->nullable();
            // enum
            $table->tinyInteger('tai_mu_chu_muc')->default(0);
            $table->tinyInteger('khuyet_tat_van_dong')->default(0);
            $table->tinyInteger('khuyet_tat_nghe_noi')->default(0);
            $table->tinyInteger('khuyet_tat_nhin')->default(0);
            $table->tinyInteger('khuyet_tat_than_kinh')->default(0);
            $table->tinyInteger('khuyet_tat_tri_tue')->default(0);
            $table->tinyInteger('khuyet_tat_hoc_tap')->default(0);
            $table->tinyInteger('tu_ky')->default(0);
            $table->tinyInteger('khuyet_tat_khac')->default(0);
            $table->tinyInteger('co_chung_nhan_khuyet_tat')->default(0);
            $table->tinyInteger('kha_nang_hoc_tap')->default(0);

            $table->string('hoan_canh_dac_biet')->nullable();
            $table->string('chi_tiet_hoan_canh')->nullable();
            $table->string('quan_he_voi_chu_ho')->nullable();
            $table->string('ho_ten_cha_me')->nullable();
            $table->string('dien_thoai')->nullable();
            $table->string('ghi_chu')->nullable();


            $table->timestamps(); // created_at, updated_at

            $table->foreign('session_id')->references('id')->on('import_sessions')->onDelete('cascade');
            $table->foreign('ward_id')->references('ward_id')->on('wards')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usersimport');
    }
}