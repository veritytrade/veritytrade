<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('state');
            }
        });

        DB::table('users')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $updates = [];

                foreach (['phone', 'address', 'state', 'city'] as $field) {
                    $value = $row->{$field} ?? null;
                    if ($value === null || $value === '') {
                        continue;
                    }

                    try {
                        Crypt::decryptString($value);
                        continue;
                    } catch (\Throwable $e) {
                        $updates[$field] = Crypt::encryptString((string) $value);
                    }
                }

                if (!empty($updates)) {
                    DB::table('users')->where('id', $row->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('users', 'state')) {
                $table->dropColumn('state');
            }
        });
    }
};

