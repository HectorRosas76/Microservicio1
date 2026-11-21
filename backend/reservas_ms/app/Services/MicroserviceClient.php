<?php

namespace App\Services;

use Exception;

/**
 * Cliente HTTP mínimo con curl (viene activado en XAMPP, no hay que instalar nada).
 * Sirve para que reservas_ms consulte a clientes_ms y vehiculos_ms.
 */
class MicroserviceClient
{
    private string $baseUrl;
    private string $name;

    function __construct(string $baseUrl, string $name)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->name = $name;
    }

    /** GET. Devuelve el JSON como array, [] si responde 204, o null si responde 404. */
    function get(string $path): ?array
    {
        return $this->request('GET', $path);
    }

    /** PUT con cuerpo JSON. Devuelve el JSON como array, o null si responde 404. */
    function put(string $path, array $body): ?array
    {
        return $this->request('PUT', $path, $body);
    }

    private function request(string $method, string $path, ?array $body = null): ?array
    {
        $ch = curl_init($this->baseUrl . '/' . ltrim($path, '/'));
        $headers = ['Accept: application/json'];
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 5,
        ];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        $options[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($raw === false) {
            // el microservicio está apagado o no responde
            throw new Exception("El servicio de {$this->name} no está disponible", 503);
        }
        if ($code === 404) {
            return null;
        }
        if ($code === 204) {
            return [];
        }
        if ($code >= 400) {
            throw new Exception("El servicio de {$this->name} respondió con error ($code)", 502);
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
