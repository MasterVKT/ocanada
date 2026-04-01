<?php

declare(strict_types=1);

$base = 'http://127.0.0.1:8081';

function request(string $url, string $method = 'GET', array $data = [], string $cookie = ''): array
{
    $headers = [
        'User-Agent: OCanadaPostSmoke/1.0',
        'Accept: text/html,application/json;q=0.9,*/*;q=0.8',
    ];

    if ($cookie !== '') {
        $headers[] = 'Cookie: ' . $cookie;
    }

    $content = '';
    if ($method === 'POST') {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $content = http_build_query($data);
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => $content,
            'ignore_errors' => true,
            'timeout' => 15,
        ],
    ]);

    $body = @file_get_contents($url, false, $ctx);
    $responseHeaders = $http_response_header ?? [];

    $status = 0;
    if (!empty($responseHeaders) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $m)) {
        $status = (int) $m[1];
    }

    $setCookies = [];
    foreach ($responseHeaders as $line) {
        if (stripos($line, 'Set-Cookie:') === 0) {
            $setCookies[] = trim(substr($line, strlen('Set-Cookie:')));
        }
    }

    return [
        'status' => $status,
        'body' => $body === false ? '' : $body,
        'headers' => $responseHeaders,
        'setCookies' => $setCookies,
    ];
}

function mergeCookies(string $existing, array $setCookies): string
{
    $jar = [];

    if ($existing !== '') {
        foreach (explode(';', $existing) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '' || strpos($chunk, '=') === false) {
                continue;
            }
            [$k, $v] = explode('=', $chunk, 2);
            $jar[$k] = $v;
        }
    }

    foreach ($setCookies as $setCookie) {
        $pair = trim(explode(';', $setCookie, 2)[0]);
        if (strpos($pair, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $pair, 2);
        $jar[$k] = $v;
    }

    $parts = [];
    foreach ($jar as $k => $v) {
        $parts[] = $k . '=' . $v;
    }

    return implode('; ', $parts);
}

function extractCsrf(string $html): array
{
    if (preg_match('/<input[^>]+type="hidden"[^>]+name="([^"]+)"[^>]+value="([^"]*)"/i', $html, $m)) {
        return [$m[1], html_entity_decode($m[2], ENT_QUOTES)];
    }

    return ['', ''];
}

function firstLocation(array $headers): string
{
    foreach ($headers as $line) {
        if (stripos($line, 'Location:') === 0) {
            return trim(substr($line, strlen('Location:')));
        }
    }

    return '';
}

function login(string $base, string $email, string $password, string $dashboardPath): array
{
    $cookie = '';
    $loginPage = request($base . '/login');
    $cookie = mergeCookies($cookie, $loginPage['setCookies']);

    [$tokenName, $tokenValue] = extractCsrf($loginPage['body']);

    $payload = [
        'email' => $email,
        'password' => $password,
    ];
    if ($tokenName !== '') {
        $payload[$tokenName] = $tokenValue;
    }

    $attempt = request($base . '/auth/attempt-login', 'POST', $payload, $cookie);
    $cookie = mergeCookies($cookie, $attempt['setCookies']);

    $check = request($base . $dashboardPath, 'GET', [], $cookie);

    return [
        'ok' => $check['status'] === 200,
        'cookie' => $cookie,
        'status' => $attempt['status'],
        'location' => firstLocation($attempt['headers']),
    ];
}

$report = [
    'admin' => ['login' => false, 'approveProbe' => 'skipped'],
    'agent' => ['login' => false, 'storeStatus' => 0, 'checkoutStatus' => 0],
    'employe' => ['login' => false, 'calculateStatus' => 0],
    'issues' => [],
];

$adminEmail = getenv('OCANADA_ADMIN_EMAIL') ?: 'admin@ocanada.local';
$adminPassword = getenv('OCANADA_ADMIN_PASSWORD') ?: 'Admin123!';
$agentEmail = getenv('OCANADA_AGENT_EMAIL') ?: 'agent@ocanada.local';
$agentPassword = getenv('OCANADA_AGENT_PASSWORD') ?: 'Agent123!';
$employeeEmail = getenv('OCANADA_EMPLOYEE_EMAIL') ?: 'employe@ocanada.local';
$employeePassword = getenv('OCANADA_EMPLOYEE_PASSWORD') ?: 'Employe123!';

$admin = login($base, $adminEmail, $adminPassword, '/admin/dashboard');
$report['admin']['login'] = $admin['ok'];
if (!$admin['ok']) {
    $report['issues'][] = 'Admin login failed';
}

$agent = login($base, $agentEmail, $agentPassword, '/agent/dashboard');

if (!$agent['ok']) {
    $fallbacks = [
        ['agent.demo@ocanada.local', 'Agent123!'],
        ['agent@ocanada.local', 'password'],
        ['agent.demo@ocanada.local', 'password'],
    ];

    foreach ($fallbacks as [$fallbackEmail, $fallbackPassword]) {
        $agent = login($base, $fallbackEmail, $fallbackPassword, '/agent/dashboard');
        if ($agent['ok']) {
            break;
        }
    }
}

$report['agent']['login'] = $agent['ok'];
if (!$agent['ok']) {
    $report['issues'][] = 'Agent login failed';
}

$employee = login($base, $employeeEmail, $employeePassword, '/employe/dashboard');
$report['employe']['login'] = $employee['ok'];
if (!$employee['ok']) {
    $report['issues'][] = 'Employe login failed';
}

if ($agent['ok']) {
    $registerPage = request($base . '/agent/visitors/register', 'GET', [], $agent['cookie']);
    [$tokenName, $tokenValue] = extractCsrf($registerPage['body']);

    $payload = [
        'nom' => 'Probe',
        'prenom' => 'Visitor',
        'email' => 'probe.visitor+' . date('His') . '@ocanada.local',
        'telephone' => '+237690000999',
        'motif' => 'Reunion',
        'personne_a_voir' => 'Agent Demo',
    ];

    if ($tokenName !== '') {
        $payload[$tokenName] = $tokenValue;
    }

    $store = request($base . '/agent/visitors/store', 'POST', $payload, $agent['cookie']);
    $report['agent']['storeStatus'] = $store['status'];

    $storeJson = json_decode($store['body'], true);
    $visitorId = (int) ($storeJson['visiteurId'] ?? 0);

    if ($store['status'] !== 200 || !is_array($storeJson) || ($storeJson['success'] ?? false) !== true || $visitorId <= 0) {
        $report['issues'][] = 'Agent visitor store failed';
    } else {
        $checkoutPayload = [];
        if ($tokenName !== '' && isset($storeJson['csrfToken'])) {
            $checkoutPayload[$tokenName] = (string) $storeJson['csrfToken'];
        }

        $checkout = request($base . '/agent/visitors/' . $visitorId . '/checkout', 'POST', $checkoutPayload, $agent['cookie']);
        $report['agent']['checkoutStatus'] = $checkout['status'];
        $checkoutJson = json_decode($checkout['body'], true);

        if ($checkout['status'] !== 200 || !is_array($checkoutJson) || ($checkoutJson['success'] ?? false) !== true) {
            $report['issues'][] = 'Agent visitor checkout failed';
        }
    }
}

if ($employee['ok']) {
    $leavePage = request($base . '/employe/leaves/create', 'GET', [], $employee['cookie']);
    [$tokenName, $tokenValue] = extractCsrf($leavePage['body']);

    $payload = [
        'date_debut' => date('Y-m-d', strtotime('+2 days')),
        'date_fin' => date('Y-m-d', strtotime('+5 days')),
    ];
    if ($tokenName !== '') {
        $payload[$tokenName] = $tokenValue;
    }

    $calc = request($base . '/employe/leaves/calculate-working-days', 'POST', $payload, $employee['cookie']);
    $report['employe']['calculateStatus'] = $calc['status'];
    $calcJson = json_decode($calc['body'], true);

    if ($calc['status'] !== 200 || !is_array($calcJson) || ($calcJson['success'] ?? false) !== true) {
        $report['issues'][] = 'Employe working-days calculation failed';
    }
}

if ($admin['ok']) {
    $leavesPage = request($base . '/admin/leaves', 'GET', [], $admin['cookie']);
    $leaveId = 0;
    if (preg_match('/\/admin\/leaves\/(\d+)/', $leavesPage['body'], $m)) {
        $leaveId = (int) $m[1];
    }

    if ($leaveId > 0) {
        $leaveDetail = request($base . '/admin/leaves/' . $leaveId, 'GET', [], $admin['cookie']);
        [$tokenName, $tokenValue] = extractCsrf($leaveDetail['body']);

        $payload = ['commentary' => 'Probe approval check'];
        if ($tokenName !== '') {
            $payload[$tokenName] = $tokenValue;
        }

        $approve = request($base . '/admin/leaves/' . $leaveId . '/approve', 'POST', $payload, $admin['cookie']);
        $approveJson = json_decode($approve['body'], true);
        $report['admin']['approveProbe'] = [
            'leaveId' => $leaveId,
            'status' => $approve['status'],
            'success' => is_array($approveJson) ? (bool) ($approveJson['success'] ?? false) : false,
        ];

        if ($approve['status'] >= 500) {
            $report['issues'][] = 'Admin leave approve returned server error';
        }
    }
}

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
