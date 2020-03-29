<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotifiablesTable extends Migration
{
    public function up(): void
    {
        Schema::create('notifiables', static function (Blueprint $table): void {
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifiables');
    }
}
