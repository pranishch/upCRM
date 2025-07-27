<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallbacksTable extends Migration
{
    public function up()
    {
        Schema::create('callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('added_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->boolean('is_completed')->default(false);
            
            $table->index(['created_by', 'created_at']);
            $table->index(['manager_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('callbacks');
    }
};