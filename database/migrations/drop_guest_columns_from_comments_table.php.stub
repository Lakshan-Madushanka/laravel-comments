<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // First we need to drop indexes if exists

            $indexes = [
                'comments_guest_name_index',
                'comments_guest_email_index',
                'comments_ip_address_index'
            ];

            foreach ($indexes as $index) {
                if ($this->checkIndexExists($index)) {
                    $table->dropIndex($index);
                }
            }

            // Dropping columns if exists

            $columns = [
                'guest_name',
                'guest_email',
                'ip_address',
            ];

            foreach ($columns as $column) {
                if ($this->checkColumnExists($column)) {
                    $table->dropColumn($columns);
                }
            }
        });
    }

    public function checkIndexExists(string $index, string $table = 'comments'): bool
    {
        return Schema::hasIndex($table, $index);
    }

    public function checkColumnExists(string $column, string $table = 'comments'): bool
    {
        return Schema::hasColumn($table, $column);
    }
};
