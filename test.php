<?php

function postPermissionCheck($data) {
    $url = 'http://localhost:8080/permissions/check';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status_code, $response, $err];
}

// Define test cases
$tests = [
    [['user_id' => 2, 'path' => '/1/justa.pdf', 'operation' => 'r'],
     ['status_code' => 200, 'permitted' => true]],
    [['user_id' => 2, 'path' => '/1/justa.pdf', 'operation' => 'rw'],
     ['status_code' => 200, 'permitted' => true]],
    [['user_id' => 1, 'path' => '/2/secrets.txt', 'operation' => 'r'],
     ['status_code' => 200, 'permitted' => true]],
    [['user_id' => 1, 'path' => '/2/secrets.txt', 'operation' => 'rw'],
     ['status_code' => 200, 'permitted' => false]],
    [['user_id' => 3, 'path' => '/1/justa.pdf', 'operation' => 'r'],
     ['status_code' => 200, 'permitted' => false]],
     [['user_id' => 2, 'path' => '/1/photos/fold/smth.png', 'operation' => 'rw'],
     ['status_code' => 200, 'permitted' => true]],
];

foreach ($tests as $i => $test) {
    list($test, $expected) = $test;
    list($status_code, $response, $err) = postPermissionCheck($test);
    echo "Test #" . ($i + 1) . ": ";
    if ($err) {
        echo "cURL error: $err\n";
        continue;
    }
    // Try to decode JSON response
    $json = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Invalid JSON response: $response\n";
        continue;
    }

    if ($status_code === $expected["status_code"]) {
        // Check if the response matches the expected result
        if ($status_code === 200) {
            $result = isset($json['permitted']) ? $json['permitted'] : null;
            if ($result === $expected['permitted']) {
                echo "PASSED\n";
            } else {
                $expectedStr = $expected['permitted'] ? 'true' : 'false';
                $resultStr = $result ? 'true' : 'false';
                echo "FAILED (Expected: {$expectedStr}, Got: $resultStr) with response: $response\n";
            }
        } else {
            echo "PASSED\n";
        }
    } else {
        echo "FAILED (Expected status code: {$expected['status_code']}, Got: $status_code) with response: $response\n";
    }
}