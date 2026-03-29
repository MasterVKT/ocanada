<?php
$ctx = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
$body = @file_get_contents('http://127.0.0.1:8081/login', false, $ctx);
echo $body === false ? 'FAIL' : 'OK';
