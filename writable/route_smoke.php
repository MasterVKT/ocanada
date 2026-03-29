<?php
declare(strict_types=1);

$base = 'http://127.0.0.1:8081';

function request(string $url, string $method = 'GET', array $data = [], string $cookie = ''): array
{
    $headers = [
        'User-Agent: OCanadaSmoke/1.0',
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
            'timeout' => 10,
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

function firstLocation(array $headers): string
{
    foreach ($headers as $line) {
        if (stripos($line, 'Location:') === 0) {
            return trim(substr($line, strlen('Location:')));
        }
    }

    return '';
}

function scanRoutes(string $base, array $routes, string $cookie = ''): array
{
    $issues = [];

    foreach ($routes as $route) {
        $res = request($base . $route, 'GET', [], $cookie);
        $status = $res['status'];

        if ($status >= 500 || $status === 0) {
            $issues[] = [
                'route' => $route,
                'status' => $status,
                'location' => firstLocation($res['headers']),
            ];
        }
    }

    return $issues;
}

$publicRoutes = [
    '/',
    '/login',
    '/auth/forgot-password',
    '/kiosque',
    '/kiosque/search',
    '/visitor/index',
    '/visitor/history',
    '/visitor/statistics',
];

$adminRoutes = [
    '/admin/dashboard',
    '/admin/employees',
    '/admin/presences/index',
    '/admin/presences/history',
    '/admin/presences/statistics',
    '/admin/leaves',
    '/admin/planning',
    '/admin/planning/shifts',
    '/admin/visitors',
    '/admin/visitors/statistics',
    '/admin/documents',
    '/admin/rapports',
    '/admin/finance',
    '/admin/audit',
    '/admin/configuration',
    '/shared/realtime',
    '/notifications',
    '/profile',
];

$report = [
    'publicIssues' => scanRoutes($base, $publicRoutes),
    'login' => [
        'attempted' => false,
        'success' => false,
        'status' => 0,
        'location' => '',
        'error' => '',
    ],
    'adminIssues' => [],
];

$loginPage = request($base . '/login');
$cookie = mergeCookies('', $loginPage['setCookies']);

if ($loginPage['status'] >= 200 && $loginPage['status'] < 400) {
    $report['login']['attempted'] = true;

    $tokenName = '';
    $tokenValue = '';
    if (preg_match('/<input[^>]+type="hidden"[^>]+name="([^"]+)"[^>]+value="([^"]*)"/i', $loginPage['body'], $m)) {
        $tokenName = $m[1];
        $tokenValue = html_entity_decode($m[2], ENT_QUOTES);
    }

    $email = getenv('OCANADA_ADMIN_EMAIL') ?: 'admin@ocanada.local';
    $password = getenv('OCANADA_ADMIN_PASSWORD') ?: 'ChangeMe123';

    $payload = [
        'email' => $email,
        'password' => $password,
    ];

    if ($tokenName !== '') {
        $payload[$tokenName] = $tokenValue;
    }

    $loginResult = request($base . '/auth/attempt-login', 'POST', $payload, $cookie);
    $cookie = mergeCookies($cookie, $loginResult['setCookies']);

    $location = firstLocation($loginResult['headers']);
    $report['login']['status'] = $loginResult['status'];
    $report['login']['location'] = $location;

    $dashboard = request($base . '/admin/dashboard', 'GET', [], $cookie);
    if ($dashboard['status'] === 200) {
        $report['login']['success'] = true;
        $report['adminIssues'] = scanRoutes($base, $adminRoutes, $cookie);
    } else {
        $report['login']['error'] = 'Login did not grant admin dashboard access (status ' . $dashboard['status'] . ').';
    }
} else {
    $report['login']['error'] = 'Cannot open /login page.';
}

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
