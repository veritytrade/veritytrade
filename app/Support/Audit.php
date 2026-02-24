<?php

namespace App\Support;

use App\Models\AuditLog;

class Audit
{
    public static function log(string $action, string $table, $rowId = null, $before = null, $after = null): void
    {
        AuditLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'table_name' => $table,
            'row_id' => $rowId !== null ? (string) $rowId : null,
            'before_json' => $before,
            'after_json' => $after,
        ]);
    }
}
