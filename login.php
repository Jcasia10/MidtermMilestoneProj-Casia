<?php
require_once __DIR__ . '/auth.php';
$pageTitle = 'Welcome back';
require __DIR__ . '/header.php';
?>
<section class="account-page">
    <div class="account-intro"><p class="eyebrow">Barangay Bakehouse / member access</p><h1>Come on in.</h1><p>Keep your favorite bakes close and add your own recipe to the table.</p></div>
    <div class="account-card">
        <?php if (!empty($error)): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label for="email">Email</label><input class="form-control" type="email" id="email" name="email" required autocomplete="email"></div>
            <div class="field"><label for="password">Password</label><input class="form-control" type="password" id="password" name="password" required autocomplete="current-password"></div>
            <button class="button button-wide" type="submit">Log in to Bakehouse <span>→</span></button>
        </form>
        <p class="account-link">New to the club? <a href="index.php?page=register">Create an account</a></p>
    </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
