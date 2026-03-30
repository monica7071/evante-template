<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoteApiClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ai.remote_api_base_url', ''), '/');
        $this->apiKey  = config('ai.remote_api_key', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl);
    }

    public function getProjects(): array
    {
        $response = $this->get('/projects');

        return [
            'projects' => $response['data'] ?? [],
            'total'    => $response['total'] ?? 0,
        ];
    }

    public function searchRooms(array $filters): array
    {
        $query = array_filter([
            'project_id' => $filters['project_id'] ?? null,
            'bedrooms'   => $filters['bedrooms'] ?? null,
            'min_price'  => $filters['min_price'] ?? null,
            'max_price'  => $filters['max_price'] ?? null,
            'floor'      => $filters['floor'] ?? null,
            'unit_type'  => $filters['unit_type'] ?? null,
        ], fn ($v) => $v !== null);

        $response = $this->get('/rooms', $query);

        return [
            'rooms' => $response['data'] ?? [],
            'total' => $response['total'] ?? 0,
        ];
    }

    public function getRoomDetail(string $unitCode): array
    {
        $response = $this->get("/rooms/{$unitCode}");

        if (! ($response['success'] ?? false)) {
            return ['error' => $response['message'] ?? "ไม่พบห้อง '{$unitCode}'"];
        }

        return $response['data'] ?? [];
    }

    public function bookAppointment(array $input): array
    {
        $response = $this->post('/appointments', [
            'unit_code'        => $input['unit_code'],
            'appointment_date' => $input['appointment_date'],
            'appointment_time' => $input['appointment_time'],
            'visitor_name'     => $input['visitor_name'],
            'visitor_phone'    => $input['visitor_phone'],
        ]);

        if (! ($response['success'] ?? false)) {
            return ['error' => $response['message'] ?? 'ไม่สามารถจองนัดหมายได้'];
        }

        return [
            'success'          => true,
            'message'          => $response['message'] ?? 'จองนัดหมายเรียบร้อยแล้ว',
            'unit_code'        => $input['unit_code'],
            'appointment_date' => $input['appointment_date'],
            'appointment_time' => $input['appointment_time'],
            'visitor_name'     => $input['visitor_name'],
            'visitor_phone'    => $input['visitor_phone'],
        ];
    }

    public function cancelAppointment(string $unitCode): array
    {
        $response = $this->post("/appointments/{$unitCode}/cancel");

        if (! ($response['success'] ?? false)) {
            return ['error' => $response['message'] ?? 'ไม่สามารถยกเลิกนัดหมายได้'];
        }

        return ['success' => true, 'message' => 'ยกเลิกนัดหมายเรียบร้อยแล้ว', 'unit_code' => $unitCode];
    }

    // ── HTTP helpers ──────────────────────────────────────────────────────────

    private function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl . $path;

        Log::info("RemoteApiClient GET {$url}", $query);

        $response = Http::timeout(15)
            ->withHeaders(['X-API-Key' => $this->apiKey])
            ->get($url, $query);

        if ($response->failed()) {
            Log::error("RemoteApiClient GET failed: {$response->status()}", [
                'url'  => $url,
                'body' => $response->body(),
            ]);
            return ['success' => false, 'message' => "API error: HTTP {$response->status()}"];
        }

        return $response->json() ?? [];
    }

    private function post(string $path, array $data = []): array
    {
        $url = $this->baseUrl . $path;

        Log::info("RemoteApiClient POST {$url}", $data);

        $response = Http::timeout(15)
            ->withHeaders(['X-API-Key' => $this->apiKey])
            ->post($url, $data);

        if ($response->failed()) {
            Log::error("RemoteApiClient POST failed: {$response->status()}", [
                'url'  => $url,
                'body' => $response->body(),
            ]);
            return ['success' => false, 'message' => $response->json('message') ?? "API error: HTTP {$response->status()}"];
        }

        return $response->json() ?? [];
    }
}
