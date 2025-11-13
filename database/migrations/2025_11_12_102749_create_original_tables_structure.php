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
        // Drop existing simple usuario table
        Schema::dropIfExists('usuario');
        
        // Create proper usuario table based on tablas.sql
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('username', 150)->unique();
            $table->char('password_hash', 60);
            $table->string('device_id', 255)->default('');
            $table->timestamps();
            
            $table->index('device_id', 'idx_usuario_device_id');
        });

        // Create all_notifications table
        Schema::create('all_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 255);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('package_name', 255);
            $table->string('app_name', 100);
            $table->string('title', 500);
            $table->string('text', 1000);
            $table->text('big_text')->nullable();
            $table->string('sub_text', 500)->nullable();
            $table->bigInteger('timestamp');
            $table->boolean('is_payment_app')->default(false);
            $table->string('category', 50)->default('other');
            $table->boolean('synced')->default(false);
            $table->timestamps();
            
            $table->index('device_id', 'idx_device_id');
            $table->index('package_name', 'idx_package_name');
            $table->index('timestamp', 'idx_timestamp');
            $table->index('category', 'idx_category');
            $table->index('is_payment_app', 'idx_payment_app');
            $table->index('synced', 'idx_synced');
            $table->index('created_at', 'idx_created_at');
            $table->index('user_id', 'idx_all_notifications_user_id');
            
            $table->foreign('user_id')->references('id')->on('usuario')->onDelete('set null')->onUpdate('cascade');
        });

        // Create payment_notifications table
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 255);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('app', 100);
            $table->string('package_name', 255)->nullable();
            $table->string('title', 500);
            $table->string('text', 1000)->nullable();
            $table->text('big_text')->nullable();
            $table->string('sub_text', 500)->nullable();
            $table->text('original_message')->nullable();
            $table->bigInteger('timestamp');
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('sender', 255)->nullable();
            $table->string('confidence_level', 50)->default('MEDIUM');
            $table->text('raw_notification_text')->nullable();
            $table->boolean('migrated')->default(false);
            $table->timestamps();
            
            $table->index('device_id', 'idx_device_id');
            $table->index('app', 'idx_app');
            $table->index('timestamp', 'idx_timestamp');
            $table->index('confidence_level', 'idx_confidence');
            $table->index('migrated', 'idx_migrated');
            $table->index('created_at', 'idx_created_at');
            $table->index('user_id', 'idx_payment_notifications_user_id');
            
            $table->foreign('user_id')->references('id')->on('usuario')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('all_notifications');
        Schema::dropIfExists('payment_notifications');
        Schema::dropIfExists('usuario');
    }
};
