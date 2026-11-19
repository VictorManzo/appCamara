<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Guarda las fotos de productos en public/fotos: la foto (máx. 1600 px) y una miniatura
 * de 420 px para listas y catálogo. Siempre se re-codifican a JPEG con GD, así nunca se
 * guarda tal cual un archivo subido.
 */
class Fotos
{
    public static function dir(): string
    {
        $d = FCPATH . 'fotos' . DIRECTORY_SEPARATOR;
        if (! is_dir($d)) {
            mkdir($d, 0755, true);
        }
        return $d;
    }

    /** @return array{foto:string,mini:string} */
    public static function guardarDesdeArchivo(string $ruta): array
    {
        $info = @getimagesize($ruta);
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new RuntimeException('El archivo no es una foto válida (JPG, PNG o WebP).');
        }
        if ($info[0] * $info[1] > 40_000_000) {
            throw new RuntimeException('La foto es demasiado grande.');
        }
        $base = bin2hex(random_bytes(12));
        $foto = $base . '.jpg';
        $mini = $base . '_m.jpg';
        $dir  = self::dir();

        $img = \Config\Services::image('gd', null, false)->withFile($ruta)->reorient(true);
        if (max($info[0], $info[1]) > 1600) {
            $img->resize(1600, 1600, true, 'auto');
        }
        $img->convert(IMAGETYPE_JPEG)->save($dir . $foto, 86);

        $m = \Config\Services::image('gd', null, false)->withFile($dir . $foto);
        if (max($m->getWidth(), $m->getHeight()) > 420) {
            $m->resize(420, 420, true, 'auto');
        }
        $m->save($dir . $mini, 78);

        return ['foto' => $foto, 'mini' => $mini];
    }

    public static function borrar(?string ...$archivos): void
    {
        foreach ($archivos as $a) {
            if ($a && preg_match('/^[a-f0-9]{24}(_m)?\.jpg$/', $a) && is_file(self::dir() . $a)) {
                @unlink(self::dir() . $a);
            }
        }
    }
}
