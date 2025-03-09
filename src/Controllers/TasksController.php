<?php

namespace App\Controllers;

class TasksController
{
    private $accessToken;
    private $userInfo = null;

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();
    }

    private function getAccessToken()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.baubuddy.de/index.php/login",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"username\":\"365\", \"password\":\"1\"}",
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic QVBJX0V4cGxvcmVyOjEyMzQ1NmlzQUxhbWVQYXNz",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        if ($err) {
            error_log("Auth cURL Error: " . $err);

            throw new \Exception("Auth cURL Error: " . $err);
        }

        if ($httpCode != 200) {
            error_log("Auth API returned non-200 status code: " . $httpCode . " Response: " . $response);

            throw new \Exception("Auth API returned status code: " . $httpCode);
        }

        $data = json_decode($response, true);

        if (!isset($data["oauth"]["access_token"])) {
            error_log("Auth API response does not contain access_token: " . print_r($data, true));

            throw new \Exception("Auth API response does not contain access_token");
        }

        if (isset($data["userInfo"])) {
            $this->userInfo = $data["userInfo"];
        }

        return $data["oauth"]["access_token"];
    }

    public function getTasks()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.baubuddy.de/dev/index.php/v1/tasks/select",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->accessToken,
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($err) {
            error_log("cURL Error: " . $err);

            return ["error" => "API Error: " . $err];
        }

        if ($httpCode != 200) {
            error_log("API returned non-200 status code: " . $httpCode . " Response: " . $response);

            return ["error" => "API returned status code: " . $httpCode];
        }

        $tasks = json_decode($response, true);

        return [
            "tasks" => $tasks,
            "userInfo" => $this->userInfo
        ];
    }
}
