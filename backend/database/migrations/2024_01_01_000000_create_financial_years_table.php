<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialYearsTable extends Migration
{
    public function up()
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('parent_year')->nullable();
            $table->unsignedBigInteger('previous_year')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0:Inactive | 1:Active | 2:Closed | 3:No data Imp…');
            $table->timestamp('closed_on')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_year')->references('id')->on('financial_years')->nullOnDelete();
            $table->foreign('previous_year')->references('id')->on('financial_years')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_years');
    }
}
