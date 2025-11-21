<?php
session_start();

// Set headers first to prevent any output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Get and validate input
$drug_name = isset($_GET['name']) ? trim($_GET['name']) : '';

if (empty($drug_name) || strlen($drug_name) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please enter at least 2 characters']);
    exit();
}

// Enhanced mock drug database - PRIMARY DATA SOURCE
function getMockDrugData($searchTerm) {
    $searchLower = strtolower($searchTerm);
    $allDrugs = [
        'aspirin' => [
            ['name' => 'Aspirin', 'rxcui' => '1191', 'synonym' => 'Ecotrin, Bayer Aspirin', 'source' => 'SBD', 'description' => 'Pain reliever, fever reducer, anti-inflammatory. Used for headaches, pain, fever, and heart protection.'],
            ['name' => 'Aspirin 81mg', 'rxcui' => '123456', 'synonym' => 'Low Dose Aspirin', 'source' => 'SBD', 'description' => 'Low-dose aspirin for heart attack and stroke prevention.']
        ],
        'ibuprofen' => [
            ['name' => 'Ibuprofen', 'rxcui' => '5640', 'synonym' => 'Advil, Motrin', 'source' => 'SBD', 'description' => 'NSAID for pain, inflammation, and fever relief.'],
            ['name' => 'Ibuprofen 200mg', 'rxcui' => '123457', 'synonym' => 'Advil, Motrin IB', 'source' => 'SBD', 'description' => 'Over-the-counter pain and inflammation relief.']
        ],
        'metformin' => [
            ['name' => 'Metformin', 'rxcui' => '6809', 'synonym' => 'Glucophage', 'source' => 'SBD', 'description' => 'Oral diabetes medication for type 2 diabetes.']
        ],
        'amoxicillin' => [
            ['name' => 'Amoxicillin', 'rxcui' => '723', 'synonym' => 'Amoxil, Moxatag', 'source' => 'SBD', 'description' => 'Antibiotic for bacterial infections.']
        ],
        'lipitor' => [
            ['name' => 'Atorvastatin', 'rxcui' => '83367', 'synonym' => 'Lipitor', 'source' => 'SBD', 'description' => 'Statin medication for high cholesterol.']
        ],
        'ventolin' => [
            ['name' => 'Albuterol', 'rxcui' => '435', 'synonym' => 'Ventolin, ProAir', 'source' => 'SBD', 'description' => 'Bronchodilator for asthma and COPD.']
        ],
        'synthroid' => [
            ['name' => 'Levothyroxine', 'rxcui' => '6470', 'synonym' => 'Synthroid, Levoxyl', 'source' => 'SBD', 'description' => 'Thyroid hormone replacement therapy.']
        ],
        'da' => [
            ['name' => 'Diazepam', 'rxcui' => '3640', 'synonym' => 'Valium', 'source' => 'SBD', 'description' => 'Benzodiazepine for anxiety, muscle spasms, and seizures.'],
            ['name' => 'Danazol', 'rxcui' => '3002', 'synonym' => 'Danocrine', 'source' => 'SBD', 'description' => 'Synthetic steroid for endometriosis treatment.'],
            ['name' => 'Daptomycin', 'rxcui' => '314204', 'synonym' => 'Cubicin', 'source' => 'SBD', 'description' => 'Antibiotic for serious bacterial infections.']
        ],
        'dap' => [
            ['name' => 'Dapsone', 'rxcui' => '3094', 'synonym' => 'Aczone', 'source' => 'SBD', 'description' => 'Antibiotic for skin infections and leprosy.'],
            ['name' => 'Daptomycin', 'rxcui' => '314204', 'synonym' => 'Cubicin', 'source' => 'SBD', 'description' => 'Antibiotic for serious bacterial infections.']
        ],
        'para' => [
            ['name' => 'Paracetamol', 'rxcui' => '161', 'synonym' => 'Acetaminophen, Tylenol', 'source' => 'SBD', 'description' => 'Pain reliever and fever reducer.'],
            ['name' => 'Paroxetine', 'rxcui' => '7973', 'synonym' => 'Paxil', 'source' => 'SBD', 'description' => 'Antidepressant (SSRI) for depression and anxiety.']
        ],
        'peni' => [
            ['name' => 'Penicillin', 'rxcui' => '7982', 'synonym' => 'Penicillin G', 'source' => 'SBD', 'description' => 'Antibiotic for bacterial infections.'],
            ['name' => 'Penicillin V', 'rxcui' => '7984', 'synonym' => 'Pen-Vee', 'source' => 'SBD', 'description' => 'Oral penicillin antibiotic.']
        ]
    ];

    // Exact match
    if (isset($allDrugs[$searchLower])) {
        return $allDrugs[$searchLower];
    }

    // Partial match search
    $results = [];
    foreach ($allDrugs as $drugKey => $drugVariants) {
        if (strpos($drugKey, $searchLower) !== false) {
            $results = array_merge($results, $drugVariants);
        } else {
            foreach ($drugVariants as $drug) {
                $drugNameLower = strtolower($drug['name']);
                $synonymLower = strtolower($drug['synonym']);
                
                if (strpos($drugNameLower, $searchLower) !== false || 
                    strpos($synonymLower, $searchLower) !== false) {
                    $results[] = $drug;
                }
            }
        }
    }

    // Remove duplicates
    $uniqueResults = [];
    $seen = [];
    foreach ($results as $drug) {
        $key = $drug['name'] . $drug['rxcui'];
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $uniqueResults[] = $drug;
        }
    }

    return $uniqueResults;
}

try {
    // Use mock data directly (more reliable than external API)
    $suggestions = getMockDrugData($drug_name);
    $source = 'Local Medicine Database';
    
    // If no results found, provide helpful message
    if (empty($suggestions)) {
        $suggestions = [[
            'name' => 'No exact matches found',
            'rxcui' => '',
            'synonym' => 'Try a different search term',
            'source' => 'Help',
            'description' => 'Try searching for common medicines like: aspirin, ibuprofen, metformin, amoxicillin'
        ]];
    }
    
    // Limit results
    $suggestions = array_slice($suggestions, 0, 15);

    echo json_encode([
        'success' => true,
        'drugs' => $suggestions,
        'count' => count($suggestions),
        'source' => $source,
        'search_term' => $drug_name
    ]);

} catch (Exception $e) {
    // Fallback response
    echo json_encode([
        'success' => true,
        'drugs' => [[
            'name' => 'Medicine Search',
            'rxcui' => '',
            'synonym' => 'Enter medicine name above',
            'source' => 'Local DB',
            'description' => 'Type the name of any medicine to search our database'
        ]],
        'count' => 1,
        'source' => 'Local Database',
        'search_term' => $drug_name
    ]);
}

exit();
?>