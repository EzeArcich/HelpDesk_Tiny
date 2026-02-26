<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->string('type');
            $table->json('meta')->nullable();
            $table->timestamp('created_at');
        });

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'pgsql', 'sqlsrv'], true)) {
            DB::statement("ALTER TABLE ticket_activities ADD CONSTRAINT ticket_activities_type_check CHECK (type IN ('created', 'assigned', 'status_changed', 'commented', 'tagged'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
