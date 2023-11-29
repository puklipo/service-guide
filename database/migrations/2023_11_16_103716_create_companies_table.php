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
        Schema::create('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary()->comment('法人番号');
            $table->string('name')->comment('法人名称');
            $table->string('name_kana')->comment('法人名称かな');
            $table->string('area')->comment('法人住所（市区町村）');
            $table->string('address')->comment('法人住所（番地以降）');
            $table->string('tel')->comment('法人電話番号');
            $table->string('url')->comment('法人URL');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
