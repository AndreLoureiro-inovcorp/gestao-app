<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'entities',
            'contacts',
            'articles',
            'proposals',
            'proposal_articles',
            'client_orders',
            'client_order_articles',
            'supplier_orders',
            'supplier_order_articles',
            'supplier_invoices',
            'calendar_events',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('tenant_id')
                        ->after('id')
                        ->constrained()
                        ->onDelete('cascade');
                });
            }
        }

        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->onDelete('set null');
            });
        }

        $uniqueConstraints = [
            'entities' => ['number', 'tax_number'],
            'proposals' => ['number'],
            'client_orders' => ['number'],
            'supplier_orders' => ['number'],
            'articles' => ['number'],
        ];

        foreach ($uniqueConstraints as $table => $fields) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($fields) {
                    foreach ($fields as $field) {
                        $table->unique(['tenant_id', $field]);
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $uniqueConstraints = [
            'entities' => ['number', 'tax_number'],
            'proposals' => ['number'],
            'client_orders' => ['number'],
            'supplier_orders' => ['number'],
            'articles' => ['number'],
        ];

        foreach ($uniqueConstraints as $table => $fields) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($fields) {
                    foreach ($fields as $field) {
                        $table->dropUnique(['tenant_id', $field]);
                    }
                });
            }
        }

        $tables = [
            'entities',
            'contacts',
            'articles',
            'proposals',
            'proposal_articles',
            'client_orders',
            'client_order_articles',
            'supplier_orders',
            'supplier_order_articles',
            'supplier_invoices',
            'calendar_events',
            'activity_log',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
