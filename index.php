<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/pages.php';

if (!empty($_GET['action'])) {
    handle_action($db, $_GET['action']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = handle_post($db, $page);
}

if (!$page || ($page === 'home' && !current_user())) {
    $page = current_user() ? 'home' : 'login';
}

$memberPages = ['home', 'recipe', 'create', 'edit', 'favorites', 'comment', 'comment-edit'];
if (in_array($page, $memberPages, true)) {
    require_login();
}

if ($page === 'login' || $page === 'register') {
    require __DIR__ . '/' . $page . '.php';
    exit;
}

$categories = load_categories($db);

if ($page === 'home' || $page === 'favorites') {
    render_recipe_list($db, $page, $categories);
}

if ($page === 'comment-edit') {
    render_comment_editor($db);
}

if ($page === 'recipe') {
    render_recipe($db);
}

if (in_array($page, ['create', 'edit'], true)) {
    require __DIR__ . '/recipe-form.php';
    exit;
}

http_response_code(404);
exit('Page not found');
