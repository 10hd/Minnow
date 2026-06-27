<?php
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request) {
    case '':
    case '/':
        require __DIR__ . '/home.php';
        exit();

    case '/library':
        require __DIR__ . '/library.php';
        exit();

    case '/about':
        require __DIR__ . '/about.php';
        exit();

    case '/player':
        require __DIR__ . '/player.php';
        exit();

    case '/search':
        require __DIR__ . '/search.php';
        exit();

    default:
        http_response_code(404);
        require __DIR__ . '/404.html';
}
?>
