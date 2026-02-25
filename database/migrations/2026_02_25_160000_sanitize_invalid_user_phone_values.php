<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::withTrashed()->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $phone = (string) ($user->phone ?? '');
                if ($phone === '') {
                    continue;
                }

                $clean = preg_replace('/[^\d+]/', '', $phone);
                if ($clean === '' || !preg_match('/^\+?[0-9]{6,20}$/', $clean)) {
                    $user->phone = null;
                    $user->save();
                    continue;
                }

                if ($clean !== $phone) {
                    $user->phone = $clean;
                    $user->save();
                }
            }
        });
    }

    public function down(): void
    {
        // no-op: data normalization
    }
};

