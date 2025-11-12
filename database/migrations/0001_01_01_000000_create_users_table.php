<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
//---------------------------------------------------------------

    Schema::create('provinces', function (Blueprint $table) {
        $table->id('province_id');
        $table->string('ten_tinh')->nullable();
        $table->integer('ma_tinh')->nullable();
    });


    Schema::create('provinces_report', function (Blueprint $table) {

        $table->id('province_report_id');

        $table->unsignedBigInteger('province_id')->nullable();
        $table->foreign('province_id')->references('province_id')->on('provinces')->onDelete('cascade');
        $table->year('nam_dieu_tra')->nullable();
         
        $table->integer('tong_dan_so')->nullable();
        $table->integer('dan_so_tu_15_den_25_tuoi')->nullable();
        $table->integer('dan_so_tu_15_den_35_tuoi')->nullable();
        $table->integer('dan_so_tu_15_den_60_tuoi')->nullable();
        $table->integer('gioi_tinh_nam')->nullable();
        $table->integer('gioi_tinh_nu')->nullable();
        $table->integer('gioi_tinh_nu_tu_15_den_25_tuoi')->nullable();
        $table->integer('gioi_tinh_nu_tu_15_den_35_tuoi')->nullable();
        $table->integer('gioi_tinh_nu_tu_15_den_60_tuoi')->nullable();

        $table->integer('dan_toc')->nullable();
        $table->integer('dan_toc_tu_15_den_25_tuoi')->nullable();
        $table->integer('dan_toc_tu_15_den_35_tuoi')->nullable();
        $table->integer('dan_toc_tu_15_den_60_tuoi')->nullable();

        $table->integer('nu_dan_toc')->nullable();
        $table->integer('nu_dan_toc_tu_15_den_25_tuoi')->nullable();
        $table->integer('nu_dan_toc_tu_15_den_35_tuoi')->nullable();
        $table->integer('nu_dan_toc_tu_15_den_60_tuoi')->nullable();

        $table->integer('Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->integer('Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->integer('Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->integer('Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->timestamps();
    });

    Schema::create('wards', function (Blueprint $table) {
        $table->id('ward_id');
        
        $table->unsignedBigInteger('province_id')->nullable();

        $table->foreign('province_id')->references('province_id')->on('provinces')->onDelete('cascade');

        $table->string('ten_phuong')->nullable();
        $table->integer('ma_phuong')->nullable();
    });

    Schema::create('wards_report', function (Blueprint $table) {
        $table->id('ward_report_id');

        $table->unsignedBigInteger('province_report_id')->nullable();
        $table->unsignedBigInteger('ward_id')->nullable();
        $table->unsignedBigInteger('province_id')->nullable();

        $table->foreign('ward_id')->references('ward_id')->on('wards')->onDelete('cascade');
        $table->foreign('province_id')->references('province_id')->on('provinces')->onDelete('cascade');
        $table->foreign('province_report_id')->references('province_report_id')->on('provinces_report')->onDelete('cascade');
        $table->year('nam_dieu_tra')->nullable();

        $table->integer('tong_dan_so')->nullable();
        $table->integer('dan_so_tu_15_den_25_tuoi')->nullable();
        $table->integer('dan_so_tu_15_den_35_tuoi')->nullable();
        $table->integer('dan_so_tu_15_den_60_tuoi')->nullable();
        $table->integer('gioi_tinh_nam')->nullable();
        $table->integer('gioi_tinh_nu')->nullable();
        $table->integer('gioi_tinh_nu_tu_15_den_25_tuoi')->nullable();
        $table->integer('gioi_tinh_nu_tu_15_den_35_tuoi')->nullable();
        $table->integer('gioi_tinh_nu_tu_15_den_60_tuoi')->nullable();

        $table->integer('dan_toc')->nullable();
        $table->integer('dan_toc_tu_15_den_25_tuoi')->nullable();
        $table->integer('dan_toc_tu_15_den_35_tuoi')->nullable();
        $table->integer('dan_toc_tu_15_den_60_tuoi')->nullable();

        $table->integer('nu_dan_toc')->nullable();
        $table->integer('nu_dan_toc_tu_15_den_25_tuoi')->nullable();
        $table->integer('nu_dan_toc_tu_15_den_35_tuoi')->nullable();
        $table->integer('nu_dan_toc_tu_15_den_60_tuoi')->nullable();

        $table->integer('Ds_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Ds_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Ds_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Ds_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Ds_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Ds_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Dt_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Dt_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Dt_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Dt_nu_mu_chu_md_1_do_tuoi_15_den_25_cht_lop_3')->nullable();
        $table->integer('Dt_nu_mu_chu_md_1_do_tuoi_15_den_35_cht_lop_3')->nullable();
        $table->integer('Dt_nu_mu_chu_md_1_do_tuoi_15_den_60_cht_lop_3')->nullable();

        $table->integer('Ds_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Ds_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Ds_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->integer('Ds_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Ds_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Ds_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->integer('Dt_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Dt_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Dt_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->integer('Dt_nu_mu_chu_md_2_do_tuoi_15_den_25_cht_lop_5')->nullable();
        $table->integer('Dt_nu_mu_chu_md_2_do_tuoi_15_den_35_cht_lop_5')->nullable();
        $table->integer('Dt_nu_mu_chu_md_2_do_tuoi_15_den_60_cht_lop_5')->nullable();

        $table->timestamps(); // created_at, updated_at
    
    });
//---------------------------------------------------------------
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            //---------------------------------------------------------------
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();

            $table->foreign('ward_id')->references('ward_id')->on('wards')->onDelete('cascade');
            $table->foreign('province_id')->references('province_id')->on('provinces')->onDelete('cascade');
            //---------------------------------------------------------------
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Thêm cột role
            $table->string('role');

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('wards_report');
        Schema::dropIfExists('wards');

        Schema::dropIfExists('provinces_report');
        Schema::dropIfExists('provinces');
    }
};
