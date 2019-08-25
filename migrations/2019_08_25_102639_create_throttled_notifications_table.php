<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThrottledNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('throttled_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('payload');
            $table->dateTime('sent_at')->nullable();
            $table->uuid('notification_id');
            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')
                ->on('notifications');
        });
    }

    public function down()
    {
        Schema::dropIfExists('throttled_notifications');
    }
}
