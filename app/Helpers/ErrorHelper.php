<?php

namespace App\Helpers;

use Throwable;

class ErrorHelper
{
    public static function safeDatabaseError(): array
    {
        return [
            'conexion' => config('database.default'),
            'host' => config('database.connections.pgsql.host'),
            'puerto' => config('database.connections.pgsql.port'),
            'base_datos' => config('database.connections.pgsql.database'),
        ];
    }

    public static function safeMessage(Throwable $exception): string
    {
        if (config('app.debug')) {
            return $exception->getMessage();
        }

        return 'Ocurrio un error interno controlado.';
    }
}
