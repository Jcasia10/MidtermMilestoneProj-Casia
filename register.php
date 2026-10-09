<?php
require_once __DIR__ . '/auth.php';
$pageTitle = 'Join the table';
require __DIR__ . '/header.php';
?>
<section class="account-page">
    <div class="account-intro"><p class="eyebrow">Barangay Bakehouse / new member</p><h1>Pull up a chair.</h1><p>Join a neighborhood recipe shelf built around warm ovens and generous hands.</p></div>
    <div class="account-card">
        <?php if (!empty($error)): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label for="name">Your name</label><input class="form-control" id="name" name="name" required maxlength="80" autocomplete="name"></div>
            <div class="field"><label for="email">Email</label><input class="form-control" type="email" id="email" name="email" required autocomplete="email"></div>
            <div class="field"><label for="password">Password</label><input class="form-control" type="password" id="password" name="password" required minlength="8" autocomplete="new-password"><small class="field-hint">At least 8 characters.</small></div>
            <button class="button button-wide" type="submit">Create my account <span>→</span></button>
        </form>
        <p class="account-link">Already a member? <a href="index.php?page=login">Log in</a></p>
    </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
