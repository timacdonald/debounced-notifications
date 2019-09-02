<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThrottledNotificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('throttled_notifications', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->longText('payload');
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delayed_until')->nullable();
            $table->string('reserved_key')->nullable();
            $table->uuid('notification_id');
            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')
                ->on('notifications');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('throttled_notifications');
    }
}
