<?php
/**
 * Trillboards Partner API - PHP SDK
 *
 * Lightweight PHP client for Trillboards Partner API.
 * Designed for server-to-server integrations (Tier 1).
 *
 * Usage:
 *   $client = new TrillboardsPartner('trb_partner_xxx');
 *   $config = $client->getVastConfig();
 *   // ... play ad ...
 *   $client->reportImpressions([...]);
 */

class TrillboardsPartner {
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.trillboards.com',
        int $timeout = 30
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Get VAST configuration.
     * Cache this response for 1 hour to reduce API calls.
     *
     * @param string|null $deviceId Optional device ID for device-specific config
     * @return array API response with VAST tag URL template
     * @throws Exception On API error
     */
    public function getVastConfig(?string $deviceId = null): array {
        $query = $deviceId ? "?device_id=" . urlencode($deviceId) : "";
        return $this->request('GET', "/v1/partner/vast/config{$query}");
    }

    /**
     * Report batch impressions with cryptographic proofs.
     *
     * @param array $impressions Array of impression objects
     * @return array API response with proofs
     * @throws Exception On API error
     */
    public function reportImpressions(array $impressions): array {
        return $this->request('POST', '/v1/partner/tracking/batch', [
            'impressions' => $impressions
        ]);
    }

    /**
     * Register a new device.
     *
     * @param string $deviceId Unique device identifier
     * @param array $data Optional device data (name, location, etc.)
     * @return array API response with device details
     * @throws Exception On API error
     */
    public function registerDevice(string $deviceId, array $data = []): array {
        return $this->request('POST', '/v1/partner/device', array_merge(
            ['device_id' => $deviceId],
            $data
        ));
    }

    /**
     * Get partner info and stats.
     *
     * @return array API response with partner details
     * @throws Exception On API error
     */
    public function getInfo(): array {
        return $this->request('GET', '/v1/partner/info');
    }

    /**
     * Rotate API key with 24-hour grace period.
     * WARNING: Save the new key immediately - it's only shown once.
     *
     * @return array API response with new API key
     * @throws Exception On API error
     */
    public function rotateApiKey(): array {
        return $this->request('POST', '/v1/partner/api-key/rotate');
    }

    /**
     * Make HTTP request to Trillboards API.
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array|null $body Request body for POST/PUT
     * @return array Decoded JSON response
     * @throws Exception On HTTP or API error
     */
    private function request(string $method, string $endpoint, ?array $body = null): array {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("HTTP request failed: {$error}");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }

        if ($httpCode >= 400) {
            $message = $data['message'] ?? $data['error']['message'] ?? 'Unknown error';
            throw new Exception("API error ({$httpCode}): {$message}");
        }

        return $data;
    }
}

// ==========================================
// Quick Start Example
// ==========================================

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0])) {
    $apiKey = getenv('TRILLBOARDS_API_KEY');
    if (!$apiKey) {
        echo "Error: Set TRILLBOARDS_API_KEY environment variable\n";
        exit(1);
    }

    $client = new TrillboardsPartner($apiKey);

    echo "1. Fetching VAST configuration...\n";
    $config = $client->getVastConfig();
    echo "   VAST URL: " . ($config['data']['vast_tag_template'] ?? 'N/A') . "\n\n";

    echo "2. Your player fetches VAST directly from Google\n";
    echo "   (Trillboards not in critical ad serving path)\n\n";

    echo "3. Reporting impression after ad completion...\n";
    $result = $client->reportImpressions([
        [
            'device_id' => 'demo-device-001',
            'ad_id' => 'ima_demo_ad_123',
            'event' => 'complete',
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'duration_ms' => 15000
        ]
    ]);
    $processed = $result['data']['processed'] ?? 0;
    $proofs = count($result['data']['proofs'] ?? []);
    echo "   Processed: {$processed}\n";
    echo "   Proofs: {$proofs} Ed25519 signatures\n\n";

    echo "Integration complete! Your screens are now monetized.\n";
}
