<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $duplicates = DB::table('persons')
                ->selectRaw('LOWER(email) as normalized_email, MIN(id) as keeper_id')
                ->whereNotNull('email')
                ->groupByRaw('LOWER(email)')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $duplicate) {
                $duplicateIds = DB::table('persons')
                    ->whereRaw('LOWER(email) = ?', [$duplicate->normalized_email])
                    ->where('id', '!=', $duplicate->keeper_id)
                    ->pluck('id');

                foreach ($duplicateIds as $duplicateId) {
                    $this->retargetPersonReferences((int) $duplicateId, (int) $duplicate->keeper_id);
                    DB::table('persons')->where('id', $duplicateId)->delete();
                }
            }
        });

        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasIndex('persons', 'persons_email_index')) {
                $table->dropIndex('persons_email_index');
            }

            if (!Schema::hasIndex('persons', 'persons_email_unique')) {
                $table->unique('email', 'persons_email_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasIndex('persons', 'persons_email_unique')) {
                $table->dropUnique('persons_email_unique');
            }

            if (!Schema::hasIndex('persons', 'persons_email_index')) {
                $table->index('email', 'persons_email_index');
            }
        });
    }

    private function retargetPersonReferences(int $fromPersonId, int $toPersonId): void
    {
        $updates = [
            'club_members' => ['person_id'],
            'digital_cards' => ['person_id'],
            'events' => ['created_by', 'validated_by'],
            'notifications' => ['person_id'],
            'request' => ['requested_by', 'validated_by'],
            'tickets' => ['person_id', 'generated_by', 'scanned_by'],
        ];

        foreach ($updates as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->where($column, $fromPersonId)
                    ->update([$column => $toPersonId]);
            }
        }

        if (
            Schema::hasTable('personal_access_tokens') &&
            Schema::hasColumn('personal_access_tokens', 'tokenable_type') &&
            Schema::hasColumn('personal_access_tokens', 'tokenable_id')
        ) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\Person')
                ->where('tokenable_id', $fromPersonId)
                ->update(['tokenable_id' => $toPersonId]);
        }
    }
};
