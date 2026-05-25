<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ads')
            ->where('ad_type', 'code')
            ->where('code', 'like', '%Promote your local brand%')
            ->delete();
    }

    public function down(): void
    {
        // Placeholder ad intentionally removed
    }
};
