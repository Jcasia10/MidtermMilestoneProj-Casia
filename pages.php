<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$db = Database::connection();
$page = $_GET['page'] ?? 'home';
$error = $error ?? null;

function render_auth_page(string $page, ?string $error): never {
    $pageTitle = $page === 'login' ? 'Welcome back' : 'Join the table';
    require __DIR__ . '/header.php';
    ?>
    <section class="auth-wrap">
        <p class="eyebrow">Barangay Bakehouse</p>
        <h1><?= $page === 'login' ? 'Come on in.' : 'Pull up a chair.' ?></h1>
        <div class="auth-card">
            <?php if ($error): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <?php if ($page === 'register'): ?><div class="field"><label for="name">Your name</label><input class="form-control" id="name" name="name" required maxlength="80"></div><?php endif; ?>
                <div class="field"><label for="email">Email</label><input class="form-control" type="email" id="email" name="email" required></div>
                <div class="field"><label for="password">Password</label><input class="form-control" type="password" id="password" name="password" required minlength="8"></div>
                <button class="button" type="submit"><?= $page === 'login' ? 'Log in' : 'Create account' ?></button>
            </form>
        </div>
        <p class="auth-link"><?= $page === 'login' ? 'New to the club? <a href="index.php?page=register">Create an account</a>' : 'Already a member? <a href="index.php?page=login">Log in</a>' ?></p>
    </section>
    <?php require __DIR__ . '/footer.php';
    exit;
}

function load_categories(PDO $db): array {
    return $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}

function render_recipe_list(PDO $db, string $page, array $categories): never {
    $search = trim($_GET['q'] ?? '');
    $category = (int) ($_GET['category'] ?? 0);
    $join = $page === 'favorites' ? 'JOIN favorites f ON f.recipe_id = r.id AND f.user_id = ?' : '';
    $params = [current_user()['id']];
    if ($page === 'favorites') { $params[] = current_user()['id']; }
    $conditions = [];
    if ($search) { $conditions[] = '(r.title LIKE ? OR r.description LIKE ?)'; $params[] = '%' . $search . '%'; $params[] = '%' . $search . '%'; }
    if ($category) { $conditions[] = 'r.category_id = ?'; $params[] = $category; }
    $sql = 'SELECT r.*, c.name category_name, u.name author, CASE WHEN fav.user_id IS NULL THEN 0 ELSE 1 END is_favorite FROM recipes r LEFT JOIN favorites fav ON fav.recipe_id = r.id AND fav.user_id = ? ' . $join . ' JOIN users u ON u.id = r.user_id JOIN categories c ON c.id = r.category_id ' . ($conditions ? 'WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY r.created_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();
    $pageTitle = $page === 'favorites' ? 'Saved recipes' : 'Explore recipes';
    require __DIR__ . '/header.php';
    ?>
    <?php if ($page === 'home'): ?><section class="hero"><div class="hero-copy"><p class="eyebrow">Welcome to the neighborhood bakehouse</p><h1>Good things rise here.</h1><p>Recipes shared from one home kitchen to another. Find a new favorite, then leave a little love for the baker who made it.</p><div class="hero-actions"><a class="button" href="#recipes">Browse the recipes <span>↓</span></a><a class="text-button hero-link" href="index.php?page=create">Share your bake <span>→</span></a></div></div><div class="hero-feature"><div class="hero-feature-art"><span class="hero-stamp">BAKED<br>WITH<br>CARE</span><span class="hero-doodle">✦</span></div><div class="hero-feature-caption"><span>From our kitchen</span><strong>For the love of baking.</strong></div></div></section><?php else: ?><section class="hero hero-simple"><div class="hero-copy"><p class="eyebrow">Your recipe shelf</p><h1>Saved for later.</h1><p>The bakes you are keeping close.</p></div></section><?php endif; ?>
    <form class="filters" method="get"><input type="hidden" name="page" value="<?= e($page) ?>"><input name="q" value="<?= e($search) ?>" placeholder="Search cakes, cookies, pandesal..."><select name="category"><option value="0">All categories</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= $category === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select><button class="button" type="submit">Find a bake</button></form>
    <div class="section-heading" id="recipes"><div><p class="eyebrow">The community table</p><h2><?= $search || $category ? 'Your results' : 'Fresh from the oven' ?></h2></div><a class="button secondary small" href="index.php?page=create">+ Share recipe</a></div>
    <section class="recipe-grid"><?php foreach ($recipes as $recipe): ?><article class="recipe-card"><?php if ($recipe['image_path']): ?><img class="recipe-card-image" src="<?= e($recipe['image_path']) ?>" alt="<?= e($recipe['title']) ?>"><?php endif; ?><p class="tag"><?= e($recipe['category_name']) ?> · <?= e($recipe['difficulty']) ?></p><h3><a href="index.php?page=recipe&id=<?= $recipe['id'] ?>"><?= e($recipe['title']) ?></a></h3><p class="desc"><?= e($recipe['description']) ?></p><div class="card-bottom"><span class="meta">by <?= e($recipe['author']) ?></span><button class="favorite-button <?= $recipe['is_favorite'] ? 'saved' : '' ?>" data-favorite="<?= $recipe['id'] ?>" data-csrf="<?= e(csrf_token()) ?>" type="button" aria-label="<?= $recipe['is_favorite'] ? 'Remove from favorites' : 'Add to favorites' ?>"><?= $recipe['is_favorite'] ? '♥ Saved' : '♡ Favorite' ?></button></div></article><?php endforeach; ?><?php if (!$recipes): ?><div class="empty">No bakes here yet. Try another search or be the first to share one.</div><?php endif; ?></section>
    <?php require __DIR__ . '/footer.php';
    exit;
}

function render_comment_editor(PDO $db): never {
    $stmt = $db->prepare('SELECT c.id, c.recipe_id, c.body, r.title FROM comments c JOIN recipes r ON r.id = c.recipe_id WHERE c.user_id = ? ORDER BY c.created_at DESC');
    $stmt->execute([current_user()['id']]);
    $comments = $stmt->fetchAll();
    $pageTitle = 'Your notes';
    require __DIR__ . '/header.php';
    ?>
    <section class="form-wrap"><div class="form-intro"><p class="eyebrow">Your voice in the kitchen</p><h1>Your notes.</h1><p class="note">Update the feedback you have left for your neighbors.</p></div><?php if (!$comments): ?><div class="empty">You have not left a note yet. Visit a recipe to share one.</div><?php endif; ?><?php foreach ($comments as $comment): ?><div class="auth-card" style="margin-bottom:16px"><p class="tag"><a href="index.php?page=recipe&id=<?= $comment['recipe_id'] ?>"><?= e($comment['title']) ?></a></p><form method="post" action="index.php?action=edit-comment"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="comment_id" value="<?= $comment['id'] ?>"><input type="hidden" name="recipe_id" value="<?= $comment['recipe_id'] ?>"><textarea class="form-control" name="body" maxlength="1000" required><?= e($comment['body']) ?></textarea><div class="form-actions"><button class="button small">Save note</button></div></form></div><?php endforeach; ?></section>
    <?php require __DIR__ . '/footer.php';
    exit;
}

function render_recipe(PDO $db): never {
    $stmt = $db->prepare('SELECT r.*, c.name category_name, u.name author FROM recipes r JOIN categories c ON c.id = r.category_id JOIN users u ON u.id = r.user_id WHERE r.id = ?');
    $stmt->execute([(int) $_GET['id']]);
    $recipe = $stmt->fetch();
    if (!$recipe) { http_response_code(404); exit('Recipe not found'); }
    $stmt = $db->prepare('SELECT item FROM ingredients WHERE recipe_id = ? ORDER BY sort_order'); $stmt->execute([$recipe['id']]); $ingredients = $stmt->fetchAll();
    $stmt = $db->prepare('SELECT c.*, u.name author FROM comments c JOIN users u ON u.id = c.user_id WHERE c.recipe_id = ? ORDER BY c.created_at DESC'); $stmt->execute([$recipe['id']]); $comments = $stmt->fetchAll();
    $check = $db->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?'); $check->execute([current_user()['id'], $recipe['id']]); $saved = (bool) $check->fetch();
    $owner = (int) $recipe['user_id'] === (int) current_user()['id']; $pageTitle = $recipe['title']; require __DIR__ . '/header.php';
    ?>
    <section class="recipe-layout"><article><div class="recipe-header"><?php if ($recipe['image_path']): ?><img class="recipe-hero-image" src="<?= e($recipe['image_path']) ?>" alt="<?= e($recipe['title']) ?>"><?php endif; ?><p class="eyebrow"><?= e($recipe['category_name']) ?> · <?= e($recipe['difficulty']) ?></p><h1><?= e($recipe['title']) ?></h1><p class="desc"><?= e($recipe['description']) ?></p><div class="meta-line"><span>Shared by <?= e($recipe['author']) ?></span><span><?= $recipe['updated_at'] ? 'Updated ' . e(date('M j, Y', strtotime($recipe['updated_at']))) : 'Posted ' . e(date('M j, Y', strtotime($recipe['created_at']))) ?></span></div><?php if ($owner): ?><div class="actions"><a class="button secondary small" href="index.php?page=edit&id=<?= $recipe['id'] ?>">Edit recipe</a><form class="inline-form" method="post" action="index.php?action=delete-recipe&id=<?= $recipe['id'] ?>" onsubmit="return confirm('Remove this recipe?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button class="button small">Remove</button></form></div><?php endif; ?></div>
    <div class="content-block"><h2>Ingredients</h2><ul class="ingredients"><?php foreach ($ingredients as $ingredient): ?><li><?= e($ingredient['item']) ?></li><?php endforeach; ?></ul></div><div class="content-block"><h2>How to make it</h2><p class="steps"><?= e($recipe['steps']) ?></p></div>
    <div class="content-block"><h2>From the kitchen</h2><form method="post" action="index.php?page=comment"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>"><textarea class="form-control" name="body" maxlength="1000" required placeholder="What did you think? Any tips for the next baker?"></textarea><div class="form-actions"><button class="button small">Leave a note</button></div></form><?php foreach ($comments as $comment): ?><div class="comment"><div class="comment-head"><span><?= e($comment['author']) ?></span><small><?= $comment['updated_at'] ? 'edited' : date('M j, Y', strtotime($comment['created_at'])) ?></small></div><p><?= e($comment['body']) ?></p><?php if ((int) $comment['user_id'] === (int) current_user()['id']): ?><form method="post" class="inline-form" action="index.php?action=delete-comment&id=<?= $comment['id'] ?>&recipe_id=<?= $recipe['id'] ?>"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button class="button secondary small">Delete note</button></form><?php endif; ?></div><?php endforeach; ?></div></article>
    <aside class="recipe-aside"><h3>Keep this one close.</h3><p class="note">Save it to your personal favorites shelf.</p><button class="button favorite-detail <?= $saved ? 'saved' : '' ?>" data-favorite="<?= $recipe['id'] ?>" data-csrf="<?= e(csrf_token()) ?>" type="button"><?= $saved ? '♥ Saved to favorites' : '♡ Add to favorites' ?></button></aside></section>
    <?php require __DIR__ . '/footer.php'; exit;
}

function render_recipe_form(PDO $db, array $categories, string $page, ?string $error): never {
    $editing = $page === 'edit'; $recipe = ['title'=>'','description'=>'','category_id'=>'','steps'=>'','difficulty'=>'Easy','bake_minutes'=>30]; $ingredients = [''];
    if ($editing) { $stmt = $db->prepare('SELECT * FROM recipes WHERE id = ? AND user_id = ?'); $stmt->execute([(int) $_GET['id'], current_user()['id']]); $recipe = $stmt->fetch() ?: exit('Recipe not found'); $stmt = $db->prepare('SELECT item FROM ingredients WHERE recipe_id = ? ORDER BY sort_order'); $stmt->execute([$recipe['id']]); $ingredients = array_column($stmt->fetchAll(), 'item'); }
    $pageTitle = $editing ? 'Edit recipe' : 'Share a recipe'; require __DIR__ . '/header.php';
    ?>
    <section class="form-wrap"><div class="form-intro"><p class="eyebrow"><?= $editing ? 'Tune the recipe' : 'Put it on the table' ?></p><h1><?= $editing ? 'Make it even better.' : 'Share your signature bake.' ?></h1><p class="note">Everyone starts somewhere. Clear steps and measured ingredients help your neighbor bake with confidence.</p></div><?php if ($error): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="form-grid"><div class="field"><label for="title">Recipe title</label><input class="form-control" id="title" name="title" maxlength="140" required value="<?= e($recipe['title']) ?>"></div><div class="field"><label for="category_id">Category</label><select class="form-control" id="category_id" name="category_id" required><option value="">Choose one</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= (int) $recipe['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></div></div><div class="field"><label for="description">Short description</label><input class="form-control" id="description" name="description" maxlength="255" required value="<?= e($recipe['description']) ?>" placeholder="The one-line reason people should make this."></div><div class="form-grid"><div class="field"><label for="difficulty">Difficulty</label><select class="form-control" id="difficulty" name="difficulty"><option <?= $recipe['difficulty'] === 'Easy' ? 'selected' : '' ?>>Easy</option><option <?= $recipe['difficulty'] === 'Confident' ? 'selected' : '' ?>>Confident</option><option <?= $recipe['difficulty'] === 'Showstopper' ? 'selected' : '' ?>>Showstopper</option></select></div><div class="field"><label for="bake_minutes">Bake time in minutes</label><input class="form-control" type="number" id="bake_minutes" name="bake_minutes" min="1" max="1440" required value="<?= e($recipe['bake_minutes']) ?>"></div></div><div class="field"><label>Ingredients</label><div data-ingredients><?php foreach ($ingredients as $ingredient): ?><div class="ingredient-row"><input class="form-control" name="ingredients[]" maxlength="180" required value="<?= e($ingredient) ?>" placeholder="e.g. 2 cups flour"><button type="button" class="remove-ingredient" aria-label="Remove ingredient">×</button></div><?php endforeach; ?></div><button type="button" class="button secondary small" data-add-ingredient>+ Add ingredient</button></div><div class="field"><label for="steps">Cooking steps</label><textarea class="form-control" id="steps" name="steps" maxlength="5000" required placeholder="One step per line..."><?= e($recipe['steps']) ?></textarea></div><div class="form-actions"><button class="button" type="submit"><?= $editing ? 'Save changes' : 'Publish recipe' ?></button><a class="button secondary" href="index.php">Cancel</a></div></form></section>
    <?php require __DIR__ . '/footer.php'; exit;
}
