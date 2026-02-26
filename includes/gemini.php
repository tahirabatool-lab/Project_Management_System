<?php
// includes/gemini.php

function generateProjectRequirements($projectTitle)
{
    $apiKey = GEMINI_API_KEY;

    // Use gemini-2.5-flash model which is latest and fully supported
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . urlencode($apiKey);

    $prompt = "Generate simple university level software project requirements for the project titled: {$projectTitle}.
Use bullet points.
Easy language.
8 to 12 requirements.";

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    error_log("=== GEMINI API REQUEST ===");
    error_log("URL: " . $url);
    error_log("Project Title: " . $projectTitle);
    error_log("Request Payload: " . json_encode($data));

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);

    error_log("HTTP Code: " . $httpCode);
    error_log("Response: " . substr($response, 0, 1000));

    // Check for cURL errors
    if ($response === false || !empty($curlError)) {
        error_log('CURL Error: ' . $curlError);
        return ['error' => 'Network error: ' . $curlError];
    }

    // Check HTTP status code
    if ($httpCode !== 200) {
        error_log("Gemini API HTTP Error: $httpCode. Full Response: $response");
        
        // Try to parse error details from response
        $errorData = json_decode($response, true);
        $errorMsg = $errorData['error']['message'] ?? "API Error ($httpCode): Failed to generate requirements. Check API key and quota.";
        
        return ['error' => $errorMsg];
    }

    // Decode and validate JSON response
    $result = json_decode($response, true);
    
    if ($result === null) {
        error_log('JSON Decode Error: ' . json_last_error_msg() . '. Response: ' . substr($response, 0, 500));
        return ['error' => 'Invalid JSON response from API'];
    }

    // Extract text from nested Gemini response structure
    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        error_log('Unexpected API response structure: ' . json_encode($result));
        return ['error' => 'Unexpected API response structure'];
    }

    $requirements = $result['candidates'][0]['content']['parts'][0]['text'];
    
    error_log("=== SUCCESS ===");
    error_log("Generated requirements length: " . strlen($requirements));
    
    return ['success' => true, 'requirements' => $requirements];
}
