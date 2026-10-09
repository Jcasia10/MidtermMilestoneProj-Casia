<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Bakehouse';
$user = current_user();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Bakehouse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php" aria-label="Bakehouse home"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 48 48" role="img"><path d="M13 24v13h22V24M10 25h28a5 5 0 0 0 .4-10A9 9 0 0 0 22 9a8 8 0 0 0-7.6 5.5A5.5 5.5 0 0 0 10 25Z"/><path d="M10 37h28"/></svg></span><span>Bakehouse<small>barangay recipe club</small></span></a>
    <?php if ($user): ?>
        <nav class="nav"><a href="index.php">Explore</a><a href="index.php?page=create">Share a recipe</a><a href="index.php?page=favorites">Saved</a><a href="index.php?page=comment-edit">Your notes</a><span class="user-chip">Hi, <?= e($user['name']) ?></span><a class="nav-quiet" href="index.php?action=logout">Log out</a></nav>
    <?php endif; ?>
</header>
<main class="page-shell">
<?php if ($flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
