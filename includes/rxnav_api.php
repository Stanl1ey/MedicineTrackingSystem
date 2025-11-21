<?php
function getDrugInfo($medicine_name) {
    $url = "https://rxnav.nlm.nih.gov/REST/drugs.json?name=" . urlencode($medicine_name);
    
    // First try with cURL
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'MedicineTracker/1.0',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if (!$curl_error && $http_code === 200 && $response) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return ['success' => true, 'data' => $data];
            }
        }
    }
    
    // Fallback to file_get_contents if cURL fails
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'MedicineTracker/1.0',
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return ['success' => true, 'data' => $data];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Unable to connect to RxNav API'];
}

function extractDrugSuggestions($api_data) {
    $suggestions = [];
    
    if (!isset($api_data['drugGroup'])) {
        return $suggestions;
    }
    
    if (isset($api_data['drugGroup']['conceptGroup'])) {
        $conceptGroups = $api_data['drugGroup']['conceptGroup'];
        
        if (!is_array($conceptGroups)) {
            return $suggestions;
        }
        
        foreach ($conceptGroups as $group) {
            if (isset($group['conceptProperties']) && is_array($group['conceptProperties'])) {
                foreach ($group['conceptProperties'] as $drug) {
                    if (isset($drug['name'])) {
                        $suggestions[] = [
                            'name' => $drug['name'],
                            'rxcui' => $drug['rxcui'] ?? '',
                            'synonym' => $drug['synonym'] ?? '',
                            'source' => $group['tty'] ?? 'Unknown'
                        ];
                    }
                }
            }
        }
    }
    
    return $suggestions;
}

// Mock data for testing when API is unavailable
function getMockDrugData($searchTerm) {
    $mockDrugs = [
        'aspirin' => [
            ['name' => 'Aspirin', 'rxcui' => '1191', 'synonym' => 'Ecotrin', 'source' => 'SBD'],
            ['name' => 'Aspirin 81mg', 'rxcui' => '123456', 'synonym' => 'Low Dose Aspirin', 'source' => 'SBD']
        ],
        'ibuprofen' => [
            ['name' => 'Ibuprofen', 'rxcui' => '5640', 'synonym' => 'Advil, Motrin', 'source' => 'SBD'],
            ['name' => 'Ibuprofen 200mg', 'rxcui' => '123457', 'synonym' => '', 'source' => 'SBD']
        ],
        'metformin' => [
            ['name' => 'Metformin', 'rxcui' => '6809', 'synonym' => 'Glucophage', 'source' => 'SBD'],
            ['name' => 'Metformin HCl 500mg', 'rxcui' => '123458', 'synonym' => '', 'source' => 'SBD']
        ],
        'ba' => [
            ['name' => 'Bactrim', 'rxcui' => '1655056', 'synonym' => 'Sulfamethoxazole/Trimethoprim', 'source' => 'SBD'],
            ['name' => 'Baclofen', 'rxcui' => '1202', 'synonym' => 'Lioresal', 'source' => 'SBD']
        ]
    ];
    
    $searchLower = strtolower($searchTerm);
    foreach ($mockDrugs as $drug => $variants) {
        if (strpos($drug, $searchLower) !== false) {
            return $variants;
        }
    }
    
    return [];
}
?>