<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use PDO;

class PostgreSqlConnectionService
{
    public function testConnection(): object
    {
        DB::connection('pgsql')->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return DB::connection('pgsql')->selectOne('select version() as version');
    }

    public function safeConnectionMetadata(): array
    {
        return [
            'conexion' => config('database.default'),
            'host' => config('database.connections.pgsql.host'),
            'puerto' => config('database.connections.pgsql.port'),
            'base_datos' => config('database.connections.pgsql.database'),
        ];
    }
}
