<?php

namespace App\Controllers;

use App\Libraries\Fotos;
use App\Models\AjusteModel;
use App\Models\ProductoModel;
use ZipArchive;

/**
 * Importa el archivo "inventario-petus.zip" que descarga la app de claude.ai
 * (botón "Exportar para mi sitio"). Se puede subir desde esta página o dejarlo en
 * writable/import/ con el administrador de archivos del hosting si es muy grande.
 * Volver a importar actualiza los productos en lugar de duplicarlos.
 */
class Importar extends BaseController
{
    public function index()
    {
        return view('importar', ['resultado' => session()->getFlashdata('resultado'), 'error' => session()->getFlashdata('error'),
            'enCarpeta' => is_file($this->rutaCarpeta())]);
    }

    public function procesar()
    {
        @set_time_limit(600);
        $zipRuta = null;
        $f = $this->request->getFile('archivo');
        if ($f && $f->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $f->isValid()) {
                return redirect()->back()->with('error', 'El archivo no se pudo subir (' . $f->getErrorString() . '). Si pesa mucho, súbelo a writable/import/ con el administrador de archivos y usa el segundo botón.');
            }
            $zipRuta = $f->getTempName();
        } elseif ($this->request->getPost('desde_carpeta') && is_file($this->rutaCarpeta())) {
            $zipRuta = $this->rutaCarpeta();
        }
        if (! $zipRuta) {
            return redirect()->back()->with('error', 'Elige el archivo inventario-petus.zip.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipRuta) !== true) {
            return redirect()->back()->with('error', 'No pude abrir el ZIP. Asegúrate de que sea el archivo que descargó la app.');
        }
        $datos = json_decode((string) $zip->getFromName('productos.json'), true);
        if (! is_array($datos) || ! isset($datos['productos'])) {
            $zip->close();
            return redirect()->back()->with('error', 'El ZIP no trae productos.json.');
        }

        $m = new ProductoModel();
        $nuevos = 0; $actualizados = 0; $fotos = 0; $tmp = WRITEPATH . 'import/foto.tmp';
        @mkdir(WRITEPATH . 'import', 0755, true);

        foreach ($datos['productos'] as $p) {
            $origen = substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($p['id'] ?? '')), 0, 40);
            if ($origen === '') {
                continue;
            }
            $fila = [
                'origen_id'     => $origen,
                'nombre'        => mb_substr(trim((string) ($p['nombre'] ?? '')), 0, 200),
                'serie'         => mb_substr((string) ($p['serie'] ?? ''), 0, 150),
                'numero'        => mb_substr((string) ($p['numero'] ?? ''), 0, 40),
                'color'         => mb_substr((string) ($p['color'] ?? ''), 0, 200),
                'anio'          => mb_substr((string) ($p['anio'] ?? ''), 0, 10),
                'cantidad'      => max(0, (int) ($p['cantidad'] ?? 0)),
                'precio'        => isset($p['precio']) && (float) $p['precio'] > 0 ? (float) $p['precio'] : null,
                'precio_antes'  => isset($p['precioAntes']) && (float) $p['precioAntes'] > 0 ? (float) $p['precioAntes'] : null,
                'dueno'         => ($p['dueno'] ?? 'V') === 'L' ? 'L' : 'V',
                'notas'         => mb_substr((string) ($p['notas'] ?? ''), 0, 2000),
                'es_hot_wheels' => ! empty($p['esHotWheels']) ? 1 : 0,
                'vendidos'      => max(0, (int) ($p['vendidos'] ?? 0)),
            ];
            $fila['pendiente'] = $fila['nombre'] === '' ? 1 : 0;
            if (! empty($p['creado'])) {
                $fila['created_at'] = date('Y-m-d H:i:s', (int) ($p['creado'] / 1000));
            }

            $existente = $m->where('origen_id', $origen)->first();
            // foto: solo si es nueva o el producto aún no tiene
            $archivoFoto = (string) ($p['archivo'] ?? '');
            if ($archivoFoto !== '' && (! $existente || ! $existente['foto'])) {
                $bin = $zip->getFromName($archivoFoto);
                if ($bin !== false) {
                    file_put_contents($tmp, $bin);
                    try {
                        $fila += Fotos::guardarDesdeArchivo($tmp);
                        $fotos++;
                    } catch (\Throwable $e) {
                        log_message('warning', 'Foto no importada de ' . $origen . ': ' . $e->getMessage());
                    }
                }
            }
            if ($existente) {
                $m->update($existente['id'], $fila);
                $actualizados++;
            } else {
                $m->insert($fila);
                $nuevos++;
            }
        }
        @unlink($tmp);
        $zip->close();
        if (isset($datos['contacto'])) {
            (new AjusteModel())->guardar('contacto', mb_substr((string) $datos['contacto'], 0, 300));
        }
        if ($zipRuta === $this->rutaCarpeta()) {
            @unlink($zipRuta);
        }
        return redirect()->to(site_url('importar'))->with('resultado', "Listo: {$nuevos} productos nuevos, {$actualizados} actualizados y {$fotos} fotos.");
    }

    private function rutaCarpeta(): string
    {
        return WRITEPATH . 'import/inventario-petus.zip';
    }
}
