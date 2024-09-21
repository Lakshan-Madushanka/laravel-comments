<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
         $table->dropIndex('comments_guest_name_index');
         $table->dropIndex('comments_guest_email_index');
         $table->dropIndex('comments_ip_address_index');

         $table->dropColumn(['guest_name', 'guest_email', 'ip_address']);
        });
    }
};
