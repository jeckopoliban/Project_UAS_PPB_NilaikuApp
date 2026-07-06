<?php
function sendRequest($method, $url, $token = null, $body = null) {
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        $content = json_encode($body);
    } else {
        $content = null;
    }
    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'ignore_errors' => true,
            'content' => $content,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];
    $ctx = stream_context_create($opts);
    return file_get_contents($url, false, $ctx);
}

$base = 'https://uas-ppb-c050424006-backend.test/api';
$noorBody = ['email' => 'noor@mail.com', 'password' => 'noor123'];
$zacklyBody = ['email' => 'zackly@mail.com', 'password' => 'zackly123'];

$noorRaw = sendRequest('POST', $base . '/login', null, $noorBody);
echo "NOOR LOGIN RAW:\n";
echo $noorRaw . "\n\n";
$noor = json_decode($noorRaw, true);
$tokenA = $noor['data']['token'] ?? null;
if ($tokenA) {
    $noorTakRaw = sendRequest('GET', $base . '/tahun-akademik', $tokenA);
    echo "NOOR TAHUN AKADEMIK RAW:\n";
    echo $noorTakRaw . "\n\n";
} else {
    echo "Noor token not found\n\n";
}

$zacklyRaw = sendRequest('POST', $base . '/login', null, $zacklyBody);
echo "ZACKLY LOGIN RAW:\n";
echo $zacklyRaw . "\n\n";
$zackly = json_decode($zacklyRaw, true);
$tokenB = $zackly['data']['token'] ?? null;
if ($tokenB) {
    $zacklyTakRaw = sendRequest('GET', $base . '/tahun-akademik', $tokenB);
    echo "ZACKLY TAHUN AKADEMIK RAW:\n";
    echo $zacklyTakRaw . "\n";
} else {
    echo "Zackly token not found\n";
}
