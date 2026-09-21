<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 20)->unique();
                $table->string('name', 100);
                $table->integer('capacity');
                $table->string('location', 150);
                $table->string('floor', 50);
                $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available')->index();
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->text('facilities')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('user_name', 100)->nullable();
                $table->string('user_dept', 100)->nullable();
                $table->unsignedInteger('room_id');
                $table->string('title', 150);
                $table->date('date');
                $table->time('start_time');
                $table->time('end_time');
                $table->text('purpose')->nullable();
                $table->string('activity_type', 50)->default('internal_divisi');
                $table->integer('attendees_count')->default(1);
                $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('confirmed');
                $table->enum('attendance_status', ['scheduled', 'checked_in', 'checked_out', 'no_show'])->nullable();
                $table->dateTime('check_in_at')->nullable();
                $table->dateTime('check_out_at')->nullable();
                $table->dateTime('no_show_at')->nullable();
                $table->enum('attendance_updated_by', ['user', 'system'])->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();
                $table->index(['room_id', 'date', 'status']);
                $table->index(['date', 'status']);
                $table->index(['user_id', 'status']);
                $table->index(['status', 'attendance_status', 'date', 'start_time', 'end_time']);
            });
        }

        if (! Schema::hasTable('room_displays')) {
            Schema::create('room_displays', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('room_id');
                $table->string('display_name', 100);
                $table->string('display_token', 64)->unique();
                $table->timestamp('last_active')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('recipient_user_id');
                $table->unsignedInteger('booking_id');
                $table->string('type', 50)->default('booking_pending');
                $table->string('title', 150);
                $table->string('message');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['recipient_user_id', 'booking_id', 'type'], 'notification_recipient_booking_type_unique');
                $table->index(['recipient_user_id', 'is_read', 'created_at']);
                $table->foreign('recipient_user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('room_displays');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('rooms');
    }
};
