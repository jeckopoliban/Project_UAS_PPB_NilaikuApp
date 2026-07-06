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
$login = sendRequest('POST', $base . '/login', null, ['email' => 'zackly@mail.com', 'password' => 'zackly123']);
echo "LOGIN RAW:\n";
echo $login . "\n\n";
$loginData = json_decode($login, true);
$token = $loginData['data']['token'] ?? null;
if (!$token) {
    echo "No token\n";
    exit(1);
}
$raw = sendRequest('GET', $base . '/komponen-nilai?mata_kuliah_id=1127', $token, null);
echo "KOMPONEN NILAI RAW:\n";
echo $raw . "\n";
