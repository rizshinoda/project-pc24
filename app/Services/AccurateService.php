<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AccurateService
{
    /**
     * Access Token dari session Laravel.
     */
    protected function accessToken(): string
    {
        $token = session('accurate_access_token');

        if (!$token) {
            throw new RuntimeException(
                'Accurate belum terhubung. Silakan connect Accurate terlebih dahulu.'
            );
        }

        return $token;
    }

    /**
     * Host database Accurate hasil dari open-db.
     */
    protected function host(): string
    {
        $host = session('accurate_host');

        if (!$host) {
            throw new RuntimeException(
                'Host Accurate belum tersedia. Jalankan open-db terlebih dahulu.'
            );
        }

        return rtrim($host, '/');
    }

    /**
     * Session ID database Accurate hasil dari open-db.
     */
    protected function sessionId(): string
    {
        $sessionId = session('accurate_session');

        if (!$sessionId) {
            throw new RuntimeException(
                'Session Accurate belum tersedia. Jalankan open-db terlebih dahulu.'
            );
        }

        return $sessionId;
    }

    /**
     * Membuat HTTP client untuk API Accurate.
     */
    protected function request()
    {
        return Http::withToken($this->accessToken())
            ->withHeaders([
                'X-Session-ID' => $this->sessionId(),
            ]);
    }

    /**
     * Mengambil daftar customer dari Accurate.
     */
    public function customerList(array $params = []): Response
    {
        return $this->request()
            ->get(
                $this->host() . '/accurate/api/customer/list.do',
                $params
            );
    }

    /**
     * Membuat customer baru di Accurate.
     */
    public function customerSave(array $data): Response
    {
        return $this->request()
            ->asForm()
            ->post(
                $this->host() . '/accurate/api/customer/save.do',
                $data
            );
    }

    public function findCustomer(string $keyword): ?array
    {
        $response = $this->customerList([
            'fields' => 'id,name,no',
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val' => $keyword,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Gagal mengambil customer Accurate: ' . $response->body()
            );
        }

        $result = $response->json();

        if (!($result['s'] ?? false)) {
            throw new RuntimeException(
                $result['d'][0] ?? 'Gagal mencari customer Accurate.'
            );
        }

        return $result['d'][0] ?? null;
    }

    public function findOrCreateCustomer(
        string $customerName,
        string $siteName
    ): array {
        $name = trim($customerName . ' - ' . $siteName);

        // 1. Cari customer terlebih dahulu
        $customer = $this->findCustomer($name);

        if ($customer) {
            return [
                'id' => $customer['id'],
                'name' => $customer['name'] ?? $name,
                'created' => false,
            ];
        }

        // 2. Jika tidak ditemukan, buat customer baru
        $response = $this->customerSave([
            'name' => $name,
            'transDate' => now()->format('d/m/Y'),
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Gagal membuat customer Accurate: ' . $response->body()
            );
        }

        $result = $response->json();

        if (!($result['s'] ?? false)) {
            throw new RuntimeException(
                $result['d'][0] ?? 'Gagal membuat customer Accurate.'
            );
        }

        $data = $result['r'] ?? [];

        if (empty($data['id'])) {
            throw new RuntimeException(
                'Customer berhasil dibuat tetapi ID Accurate tidak ditemukan.'
            );
        }

        return [
            'id' => $data['id'],
            'name' => $data['name'] ?? $name,
            'created' => true,
        ];
    }
}
