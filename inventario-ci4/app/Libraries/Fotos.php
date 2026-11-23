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

        return ['foto' => $foto, 'mini' => $mini, 'huella' => self::huella($dir . $mini)];
    }

    /**
     * dHash de 64 bits: la imagen se reduce a 9×8 en grises y cada bit dice si un píxel es
     * más claro que el de su derecha. Fotos del mismo producto dan huellas parecidas
     * (pocos bits distintos). El navegador calcula la misma huella en panel.js.
     */
    public static function huella(string $ruta): ?string
    {
        $src = @imagecreatefromstring((string) @file_get_contents($ruta));
        if (! $src) {
            return null;
        }
        $t = imagecreatetruecolor(9, 8);
        imagecopyresampled($t, $src, 0, 0, 0, 0, 9, 8, imagesx($src), imagesy($src));
        $bits = '';
        for ($y = 0; $y < 8; $y++) {
            $fila = [];
            for ($x = 0; $x < 9; $x++) {
                $c = imagecolorat($t, $x, $y);
                $fila[] = 0.299 * (($c >> 16) & 255) + 0.587 * (($c >> 8) & 255) + 0.114 * ($c & 255);
            }
            for ($x = 0; $x < 8; $x++) {
                $bits .= $fila[$x] < $fila[$x + 1] ? '1' : '0';
            }
        }
        imagedestroy($t);
        imagedestroy($src);
        $hex = '';
        foreach (str_split($bits, 4) as $n) {
            $hex .= dechex(bindec($n));
        }
        return $hex;
    }

    public static function huellaDeArchivo(?string $archivo): ?string
    {
        return ($archivo && preg_match('/^[a-f0-9]{24}(_m)?\.jpg$/', $archivo) && is_file(self::dir() . $archivo)) ? self::huella(self::dir() . $archivo) : null;
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
