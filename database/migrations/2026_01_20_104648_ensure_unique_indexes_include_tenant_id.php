<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $this->handleMysql();
        } elseif ($driver === 'sqlite') {
            $this->handleSqlite();
        }
    }

    /**
     * Handle MySQL database.
     */
    private function handleMysql(): void
    {
        $permissionIndexes = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'permissions' 
            AND CONSTRAINT_TYPE = 'UNIQUE'
            AND CONSTRAINT_NAME LIKE '%name%guard%'
            AND CONSTRAINT_NAME NOT LIKE '%tenant%'
        ");

        foreach ($permissionIndexes as $index) {
            DB::statement("ALTER TABLE permissions DROP INDEX {$index->CONSTRAINT_NAME}");
        }

        $hasPermissionIndex = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'permissions' 
            AND CONSTRAINT_NAME LIKE '%tenant%name%guard%'
        ");

        if (empty($hasPermissionIndex)) {
            DB::statement('
                ALTER TABLE permissions 
                ADD UNIQUE INDEX permissions_name_guard_tenant_unique (name, guard_name, tenant_id)
            ');
        }

        $roleIndexes = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'roles' 
            AND CONSTRAINT_TYPE = 'UNIQUE'
            AND CONSTRAINT_NAME LIKE '%name%guard%'
            AND CONSTRAINT_NAME NOT LIKE '%tenant%'
        ");

        foreach ($roleIndexes as $index) {
            DB::statement("ALTER TABLE roles DROP INDEX {$index->CONSTRAINT_NAME}");
        }

        $hasRoleIndex = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'roles' 
            AND CONSTRAINT_NAME LIKE '%tenant%name%guard%'
        ");

        if (empty($hasRoleIndex)) {
            DB::statement('
                ALTER TABLE roles 
                ADD UNIQUE INDEX roles_name_guard_tenant_unique (name, guard_name, tenant_id)
            ');
        }
    }

    /**
     * Handle SQLite database.
     */
    private function handleSqlite(): void
    {
        $permissionIndexes = DB::select("
            SELECT name FROM sqlite_master 
            WHERE type = 'index' 
            AND tbl_name = 'permissions' 
            AND name LIKE '%name%guard%'
            AND name NOT LIKE '%tenant%'
        ");

        foreach ($permissionIndexes as $index) {
            try {
                DB::statement("DROP INDEX IF EXISTS {$index->name}");
            } catch (\Exception $e) {
            }
        }

        try {
            DB::statement('
                CREATE UNIQUE INDEX IF NOT EXISTS permissions_name_guard_tenant_unique 
                ON permissions (name, guard_name, tenant_id)
            ');
        } catch (\Exception $e) {
        }

        $roleIndexes = DB::select("
            SELECT name FROM sqlite_master 
            WHERE type = 'index' 
            AND tbl_name = 'roles' 
            AND name LIKE '%name%guard%'
            AND name NOT LIKE '%tenant%'
        ");

        foreach ($roleIndexes as $index) {
            try {
                DB::statement("DROP INDEX IF EXISTS {$index->name}");
            } catch (\Exception $e) {
            }
        }

        try {
            DB::statement('
                CREATE UNIQUE INDEX IF NOT EXISTS roles_name_guard_tenant_unique 
                ON roles (name, guard_name, tenant_id)
            ');
        } catch (\Exception $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
