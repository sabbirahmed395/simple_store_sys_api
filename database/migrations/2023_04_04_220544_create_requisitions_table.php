<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("requisition_for");
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'issued'])->default('pending');
            $table->timestamps();
        });

        Schema::create('requisition_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisition_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('quantity')->default(1);
        });

        Schema::create('requisition_issue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisition_detail_id');
            $table->unsignedBigInteger('stock_id');
            $table->unsignedBigInteger('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requisitions');
    }
};
