<?php

namespace App\Libraries;

/**
 * Aplica las migraciones nuevas solas (pedidos, jornadas…) en sitios que ya estaban
 * instalados, para no tener que entrar por consola.
 */
class Esquema
{
    private static bool $listo = false;

    public static function asegurar(): void
    {
        if (self::$listo) {
            return;
        }
        self::$listo = true;
        try {
            $db = db_connect();
            if ($db->tableExists('usuarios') && (! $db->tableExists('pedidos') || ! $db->tableExists('jornadas'))) {
                service('migrations')->setNamespace('App')->latest();
            }
        } catch (\Throwable $e) {
            log_message('error', 'Migraciones: ' . $e->getMessage());
        }
    }
}
