<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$db = Database::connection();
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

function redirect_to(string $url): never {
    header('Location: ' . $url);
    exit;
}

function save_recipe_image(?string $oldPath = null): ?string {
    if (empty($_FILES['recipe_image']['name'])) {
        return $oldPath;
    }
    if ($_FILES['recipe_image']['error'] !== UPLOAD_ERR_OK || $_FILES['recipe_image']['size'] > 5 * 1024 * 1024) {
        return $oldPath;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['recipe_image']['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        return $oldPath;
    }
    $directory = __DIR__ . '/uploads/recipes';
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (move_uploaded_file($_FILES['recipe_image']['tmp_name'], $directory . '/' . $filename)) {
        return 'uploads/recipes/' . $filename;
    }
    return $oldPath;
}

function handle_action(PDO $db, ?string $action): void {
    if ($action === 'logout') {
        session_destroy();
        redirect_to('index.php?page=login');
    }

    if ($action === 'favorite') {
        require_login();
        verify_csrf();
        header('Content-Type: application/json');
        $recipeId = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
        $check = $db->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?');
        $check->execute([current_user()['id'], $recipeId]);
        if ($check->fetch()) {
            $db->prepare('DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?')->execute([current_user()['id'], $recipeId]);
            echo json_encode(['ok' => true, 'saved' => false]);
        } else {
            $db->prepare('INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)')->execute([current_user()['id'], $recipeId]);
            echo json_encode(['ok' => true, 'saved' => true]);
        }
        exit;
    }

    if ($action === 'delete-recipe') {
        require_login();
        verify_csrf();
        $db->prepare('DELETE FROM recipes WHERE id = ? AND user_id = ?')->execute([(int) $_GET['id'], current_user()['id']]);
        flash('Recipe removed.');
        redirect_to('index.php');
    }

    if ($action === 'delete-comment') {
        require_login();
        verify_csrf();
        $recipeId = (int) $_GET['recipe_id'];
        $db->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?')->execute([(int) $_GET['id'], current_user()['id']]);
        flash('Comment removed.');
        redirect_to('index.php?page=recipe&id=' . $recipeId);
    }

    if ($action === 'edit-comment') {
        require_login();
        verify_csrf();
        $recipeId = (int) $_POST['recipe_id'];
        $body = trim($_POST['body'] ?? '');
        if ($body && strlen($body) <= 1000) {
            $db->prepare('UPDATE comments SET body = ? WHERE id = ? AND user_id = ?')->execute([$body, (int) $_POST['comment_id'], current_user()['id']]);
            flash('Comment updated.');
        }
        redirect_to('index.php?page=recipe&id=' . $recipeId);
    }
}

function handle_post(PDO $db, string $page): ?string {
    verify_csrf();

    if ($page === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            return 'Use a name, a valid email, and a password of at least 8 characters.';
        }
        try {
            $stmt = $db->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash('Account created. Welcome to the Bakehouse.');
            redirect_to('index.php?page=login');
        } catch (PDOException $exception) {
            return 'That email is already part of the Bakehouse.';
        }
    }

    if ($page === 'login') {
        $stmt = $db->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
        $stmt->execute([strtolower(trim($_POST['email'] ?? ''))]);
        $found = $stmt->fetch();
        if ($found && password_verify($_POST['password'] ?? '', $found['password_hash'])) {
            unset($found['password_hash']);
            session_regenerate_id(true);
            $_SESSION['user'] = $found;
            redirect_to('index.php');
        }
        return 'Those details did not match an account.';
    }

    if (in_array($page, ['create', 'edit'], true)) {
        require_login();
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = (int) ($_POST['category_id'] ?? 0);
        $steps = trim($_POST['steps'] ?? '');
        $difficulty = $_POST['difficulty'] ?? 'Easy';
        $minutes = (int) ($_POST['bake_minutes'] ?? 0);
        $ingredients = array_values(array_filter(array_map('trim', $_POST['ingredients'] ?? [])));
        if (!$title || !$description || !$category || !$steps || !$ingredients || $minutes < 1 || !in_array($difficulty, ['Easy', 'Confident', 'Showstopper'], true)) {
            return 'Please complete every recipe field and add at least one ingredient.';
        }
        if ($page === 'create') {
            $imagePath = save_recipe_image();
            $stmt = $db->prepare('INSERT INTO recipes (user_id, category_id, title, description, steps, image_path, difficulty, bake_minutes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([current_user()['id'], $category, $title, $description, $steps, $imagePath, $difficulty, $minutes]);
            $recipeId = (int) $db->lastInsertId();
            flash('Your recipe is on the table.');
        } else {
            $recipeId = (int) $_GET['id'];
            $existing = $db->prepare('SELECT image_path FROM recipes WHERE id = ? AND user_id = ?');
            $existing->execute([$recipeId, current_user()['id']]);
            $imagePath = save_recipe_image($existing->fetchColumn() ?: null);
            $stmt = $db->prepare('UPDATE recipes SET category_id = ?, title = ?, description = ?, steps = ?, image_path = ?, difficulty = ?, bake_minutes = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$category, $title, $description, $steps, $imagePath, $difficulty, $minutes, $recipeId, current_user()['id']]);
            $db->prepare('DELETE FROM ingredients WHERE recipe_id = ?')->execute([$recipeId]);
            flash('Recipe updated.');
        }
        $ingredientStmt = $db->prepare('INSERT INTO ingredients (recipe_id, item, sort_order) VALUES (?, ?, ?)');
        foreach ($ingredients as $order => $item) {
            $ingredientStmt->execute([$recipeId, $item, $order]);
        }
        redirect_to('index.php?page=recipe&id=' . $recipeId);
    }

    if ($page === 'comment') {
        require_login();
        $recipeId = (int) $_POST['recipe_id'];
        $body = trim($_POST['body'] ?? '');
        if ($body && strlen($body) <= 1000) {
            $db->prepare('INSERT INTO comments (recipe_id, user_id, body) VALUES (?, ?, ?)')->execute([$recipeId, current_user()['id'], $body]);
            flash('Your note was added.');
        }
        redirect_to('index.php?page=recipe&id=' . $recipeId);
    }

    return null;
}
