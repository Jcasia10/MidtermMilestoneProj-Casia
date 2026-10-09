# Bakehouse

A plain PHP/PDO recipe sharing site themed around barangay baking.

## Run locally

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin and import `schema.sql`. It creates the `bakehouse` database and the six required tables.
3. If you imported an older version of the database, run `migration_add_recipe_images.sql` in phpMyAdmin once.
4. To populate the catalog with sample pastries, import `seed_pastries.sql`.
5. Confirm the connection values in `db.php` match the local MySQL account. The default XAMPP account is `root` with an empty password.
6. Visit `http://localhost/MidtermMilestoneProj-Casia/`.

Members can register, sign in, publish recipes with any number of ingredients and an optional picture, search and filter, comment, save recipes without reloading, and edit or remove their own content.
