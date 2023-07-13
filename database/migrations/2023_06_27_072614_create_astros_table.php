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
        Schema::create('astros', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('index')->index()->comment('索引');
            $table->integer('date')->comment('日期');
            $table->string('constellation', 3)->comment('星座');

            $table->string('overall_desc')->nullable()->comment('综合运势');
            $table->string('romance_desc')->nullable()->comment('爱情运势');
            $table->string('workjob_desc')->nullable()->comment('事业运势');
            $table->string('wealth_desc')->nullable()->comment('财富运势');
            $table->string('health_desc')->nullable()->comment('健康运势');

            $table->string('lucky_astro', 10)->nullable()->comment('速配星座');
            $table->string('alert_astro', 10)->nullable()->comment('提防星座');
            $table->string('lucky_color', 10)->nullable()->comment('幸运颜色');
            $table->string('lucky_number', 10)->nullable()->comment('幸运数字');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astros');
    }
};
