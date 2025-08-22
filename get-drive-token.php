<?php

require __DIR__ . '/vendor/autoload.php';

$client = new \Google_Client();
$client->setClientId('391274858397-717abcalqkvj0hqn6t0dqb6rfrgr6q8v.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://127.0.0.1:8000/callback');
$client->setAccessType('offline');
$client->setApprovalPrompt('force');
$client->addScope('https://www.googleapis.com/auth/drive');

// === Paste your code here ===
$authCode = '4/0AVMBsJjScQ5GiOt2Apnj9SJtlVCAihb_l0zhXkBpu1WuRkJO1TY5TzVVL-GXIkLYohkQ6w';

try {
    $client->authenticate($authCode);
    $token = $client->getAccessToken();

    echo "✅ Access Token and Refresh Token:\n\n";
    print_r($token);

    // Optionally save to a file
    file_put_contents('token.json', json_encode($token));

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
