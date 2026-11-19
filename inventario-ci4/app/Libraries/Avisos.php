<?php

namespace App\Libraries;

use App\Models\AjusteModel;

/**
 * Avisos gratuitos de pedidos nuevos: Telegram (bot propio) y correo del hosting.
 * WhatsApp no se envía desde el servidor (eso requiere la API de pago); en su lugar el
 * cliente recibe un enlace wa.me con el pedido ya escrito.
 */
class Avisos
{
    public static function config(): array
    {
        $a = new AjusteModel();
        return [
            'email'    => $a->leer('aviso_email'),
            'whatsapp' => $a->leer('aviso_whatsapp'),
            'tgToken'  => $a->leer('aviso_tg_token'),
            'tgChat'   => $a->leer('aviso_tg_chat'),
        ];
    }

    /** @return array{email:?bool,telegram:?bool} null = no configurado */
    public static function enviar(string $asunto, string $texto): array
    {
        $c = self::config();
        return [
            'email'    => $c['email'] !== '' ? self::correo($c['email'], $asunto, $texto) : null,
            'telegram' => ($c['tgToken'] !== '' && $c['tgChat'] !== '') ? self::telegram($c['tgToken'], $c['tgChat'], $texto) : null,
        ];
    }

    public static function correo(string $para, string $asunto, string $texto): bool
    {
        try {
            $host = parse_url(base_url(), PHP_URL_HOST) ?: 'localhost';
            $host = preg_replace('/^www\./', '', $host);
            $email = \Config\Services::email();
            $email->initialize(['mailType' => 'text', 'charset' => 'UTF-8', 'protocol' => 'mail']);
            $email->setFrom('pedidos@' . $host, 'Catálogo Juguetería PETUS');
            $email->setTo($para);
            $email->setSubject($asunto);
            $email->setMessage($texto);
            $ok = $email->send(false);
            if (! $ok) {
                log_message('error', 'Correo de pedido no enviado: ' . strip_tags($email->printDebugger(['headers'])));
            }
            return $ok;
        } catch (\Throwable $e) {
            log_message('error', 'Correo de pedido: ' . $e->getMessage());
            return false;
        }
    }

    public static function telegram(string $token, string $chat, string $texto): bool
    {
        if (! preg_match('/^\d+:[A-Za-z0-9_-]{20,}$/', $token)) {
            return false;
        }
        try {
            $r = \Config\Services::curlrequest(['timeout' => 10, 'http_errors' => false])
                ->post('https://api.telegram.org/bot' . $token . '/sendMessage', ['form_params' => [
                    'chat_id' => $chat, 'text' => mb_substr($texto, 0, 4000), 'disable_web_page_preview' => 'true',
                ]]);
            $j = json_decode($r->getBody(), true);
            if (empty($j['ok'])) {
                log_message('error', 'Telegram: ' . $r->getBody());
            }
            return ! empty($j['ok']);
        } catch (\Throwable $e) {
            log_message('error', 'Telegram: ' . $e->getMessage());
            return false;
        }
    }

    /** Busca el último chat que le escribió al bot (para no tener que buscar el número a mano). */
    public static function detectarChat(string $token): ?array
    {
        if (! preg_match('/^\d+:[A-Za-z0-9_-]{20,}$/', $token)) {
            return null;
        }
        try {
            $r = \Config\Services::curlrequest(['timeout' => 10, 'http_errors' => false])
                ->get('https://api.telegram.org/bot' . $token . '/getUpdates');
            $j = json_decode($r->getBody(), true);
            foreach (array_reverse($j['result'] ?? []) as $u) {
                $chat = $u['message']['chat'] ?? $u['my_chat_member']['chat'] ?? null;
                if ($chat && isset($chat['id'])) {
                    return ['id' => (string) $chat['id'], 'nombre' => trim(($chat['first_name'] ?? '') . ' ' . ($chat['last_name'] ?? '')) ?: ($chat['title'] ?? '')];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Telegram getUpdates: ' . $e->getMessage());
        }
        return null;
    }

    /** Enlace wa.me para que el cliente te mande el pedido por WhatsApp. */
    public static function enlaceWhatsApp(string $texto): ?string
    {
        $num = preg_replace('/\D+/', '', self::config()['whatsapp']);
        if (strlen($num) === 10) {
            $num = '52' . $num; // número de México sin lada de país
        }
        return strlen($num) >= 11 ? 'https://wa.me/' . $num . '?text=' . rawurlencode($texto) : null;
    }
}
