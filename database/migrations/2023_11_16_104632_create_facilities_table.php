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
        Schema::create('facilities', function (Blueprint $table) {
            $table->string('id')->primary()->comment('WAM NO');
            $table->string('name')->comment('事業所名称');
            $table->string('name_kana')->comment('事業所名称かな');
            $table->string('no')->comment('事業所番号');
            $table->string('address')->comment('事業所住所（番地以降）');
            $table->string('tel')->comment('事業所電話番号');
            $table->string('url')->comment('事業所URL');
            $table->foreignId('pref_id')->constrained();
            $table->foreignId('area_id')->constrained();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('service_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
