<?php

namespace App\Services;

class ChaparShipment
{
    protected static string $baseUrl = 'http://api.krch.ir/v1/';
    protected static string $authToken = '';

    protected static function request(string $endpoint, array $postFields): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::$baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'APP-AUTH: ' . self::$authToken,
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new \Exception('Curl Error: ' . $error);
        }

        curl_close($curl);

        return json_decode($response, true) ?? [];
    }

    public static function bulk(string $jsonInput): array
    {
        return self::request('bulk_import', ['input' => $jsonInput]);
    }

    public static function quote(array $orderData): array
    {
        $inputJson = json_encode(['order' => $orderData], JSON_UNESCAPED_UNICODE);
        return self::request('get_quote', ['input' => $inputJson]);
    }

    public static function agent(): array
    {
        return self::request('fetch_agent', []);
    }
}
