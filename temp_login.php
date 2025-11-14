<?php
$projectBase = __DIR__ . '/DKMN_BE';
require $projectBase . '/vendor/autoload.php';
$app = require $projectBase . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/api/nguoi-dung/dang-nhap', 'POST', [
    'email' => 'admin@dkmn.com',
    'password' => '123456',
]);
$response = $kernel->handle($request);
printf("%s\n%s\n", $response->getStatusCode(), $response->getContent());
$kernel->terminate($request, $response);
