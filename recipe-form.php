<?php
require_once __DIR__ . '/auth.php';
require_login();
$editing = $page === 'edit';
$recipe = ['title' => '', 'description' => '', 'category_id' => '', 'steps' => '', 'difficulty' => 'Easy', 'bake_minutes' => 30];
$ingredients = [''];
if ($editing) {
    $stmt = $db->prepare('SELECT * FROM recipes WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) $_GET['id'], current_user()['id']]);
    $recipe = $stmt->fetch() ?: exit('Recipe not found');
    $stmt = $db->prepare('SELECT item FROM ingredients WHERE recipe_id = ? ORDER BY sort_order');
    $stmt->execute([$recipe['id']]);
    $ingredients = array_column($stmt->fetchAll(), 'item');
}
$pageTitle = $editing ? 'Edit recipe' : 'Share a recipe';
require __DIR__ . '/header.php';
?>
<section class="recipe-form-page">
    <div class="form-heading"><p class="eyebrow"><?= $editing ? 'Recipe notes / revision' : 'Recipe notes / new entry' ?></p><h1><?= $editing ? 'Make it even better.' : 'Share your signature bake.' ?></h1><p>Write it the way you would pass it across the counter: clear, generous, and easy to follow.</p></div>
    <form class="recipe-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <?php if (!empty($error)): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?>
        <div class="form-section"><div class="section-number">01</div><div class="section-body"><h2>The introduction</h2><div class="form-grid"><div class="field"><label for="title">Recipe title</label><input class="form-control" id="title" name="title" maxlength="140" required value="<?= e($recipe['title']) ?>" placeholder="Ube crinkle cookies"></div><div class="field"><label for="category_id">Category</label><select class="form-control" id="category_id" name="category_id" required><option value="">Choose one</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= (int) $recipe['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></div></div><div class="field"><label for="description">Short description</label><input class="form-control" id="description" name="description" maxlength="255" required value="<?= e($recipe['description']) ?>" placeholder="A soft, purple cookie with crisp edges."></div></div></div>
        <div class="form-section"><div class="section-number">02</div><div class="section-body"><h2>The practical bits</h2><div class="form-grid"><div class="field"><label for="difficulty">Difficulty</label><select class="form-control" id="difficulty" name="difficulty"><option <?= $recipe['difficulty'] === 'Easy' ? 'selected' : '' ?>>Easy</option><option <?= $recipe['difficulty'] === 'Confident' ? 'selected' : '' ?>>Confident</option><option <?= $recipe['difficulty'] === 'Showstopper' ? 'selected' : '' ?>>Showstopper</option></select></div><div class="field"><label for="bake_minutes">Bake time in minutes</label><input class="form-control" type="number" id="bake_minutes" name="bake_minutes" min="1" max="1440" required value="<?= e($recipe['bake_minutes']) ?>"></div></div><div class="field image-field"><label for="recipe_image">Recipe picture <span>(optional)</span></label><input class="form-control" type="file" id="recipe_image" name="recipe_image" accept="image/jpeg,image/png,image/webp"><small class="field-hint">JPG, PNG, or WebP up to 5 MB.</small><?php if (!empty($recipe['image_path'])): ?><img class="current-recipe-image" src="<?= e($recipe['image_path']) ?>" alt="Current recipe picture"><?php endif; ?></div></div></div>
        <div class="form-section"><div class="section-number">03</div><div class="section-body"><h2>What you need</h2><div class="ingredient-list" data-ingredients><?php foreach ($ingredients as $ingredient): ?><div class="ingredient-row"><input class="form-control" name="ingredients[]" maxlength="180" required value="<?= e($ingredient) ?>" placeholder="2 cups flour"><button type="button" class="remove-ingredient" aria-label="Remove ingredient">×</button></div><?php endforeach; ?></div><button type="button" class="text-button" data-add-ingredient>+ Add another ingredient</button></div></div>
        <div class="form-section"><div class="section-number">04</div><div class="section-body"><h2>How it comes together</h2><div class="field"><label for="steps">Cooking steps</label><textarea class="form-control" id="steps" name="steps" maxlength="5000" required placeholder="One step per line..."><?= e($recipe['steps']) ?></textarea></div></div></div>
        <div class="form-submit"><button class="button" type="submit"><?= $editing ? 'Save changes' : 'Publish recipe' ?> <span>→</span></button><a class="text-button" href="index.php">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/footer.php'; ?>
