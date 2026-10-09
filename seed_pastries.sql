USE bakehouse;

SET @seed_user = (SELECT id FROM users ORDER BY id LIMIT 1);
SET @pastries = (SELECT id FROM categories WHERE name = 'Pastries');
SET @cakes = (SELECT id FROM categories WHERE name = 'Cakes');
SET @cookies = (SELECT id FROM categories WHERE name = 'Cookies');
SET @filipino = (SELECT id FROM categories WHERE name = 'Filipino Favorites');

START TRANSACTION;

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @pastries, 'Cinnamon Morning Knots', 'Soft, buttery knots glazed with cinnamon sugar for slow weekend mornings.', 'Mix the dough until smooth and let it rise until doubled.\nRoll out the dough, brush with butter, and scatter over cinnamon sugar.\nCut into strips, tie into knots, and rest for 20 minutes.\nBake until golden and finish with a light glaze.', 'Easy', 24
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Cinnamon Morning Knots');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Cinnamon Morning Knots' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '3 cups all-purpose flour' item, 0 sort_order UNION ALL SELECT '2 tbsp brown sugar', 1 UNION ALL SELECT '1 tsp instant yeast', 2 UNION ALL SELECT '1 cup warm milk', 3 UNION ALL SELECT '4 tbsp softened butter', 4 UNION ALL SELECT '1 tsp cinnamon', 5) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @pastries, 'Strawberry Cream Danish', 'Flaky pastry pockets filled with vanilla cream and bright strawberry jam.', 'Thaw the puff pastry until flexible but still cool.\nCut into squares and fold the corners toward the center.\nFill with cream and strawberry jam.\nBake until puffed and golden, then dust with sugar.', 'Confident', 22
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Strawberry Cream Danish');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Strawberry Cream Danish' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '1 sheet puff pastry' item, 0 sort_order UNION ALL SELECT '1/2 cup strawberry jam', 1 UNION ALL SELECT '1/2 cup pastry cream', 2 UNION ALL SELECT '1 egg for brushing', 3 UNION ALL SELECT '2 tbsp icing sugar', 4) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @cakes, 'Lemon Olive Oil Loaf', 'A tender citrus loaf with a bright lemon crumb and a simple glaze.', 'Whisk eggs, sugar, lemon zest, and olive oil until glossy.\nFold in flour, baking powder, salt, and lemon juice.\nPour into a lined loaf tin.\nBake until the center springs back, then glaze while warm.', 'Easy', 48
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Lemon Olive Oil Loaf');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Lemon Olive Oil Loaf' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '2 cups all-purpose flour' item, 0 sort_order UNION ALL SELECT '3 eggs', 1 UNION ALL SELECT '3/4 cup olive oil', 2 UNION ALL SELECT '3/4 cup sugar', 3 UNION ALL SELECT '2 lemons', 4 UNION ALL SELECT '1 tsp baking powder', 5) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @cookies, 'Salted Chocolate Rye Cookies', 'Deep chocolate cookies with crisp edges, chewy centers, and a pinch of sea salt.', 'Cream the butter and sugars until pale.\nBeat in the egg and vanilla, then fold in the dry ingredients and chocolate.\nChill the dough for at least 30 minutes.\nScoop, sprinkle with salt, and bake until the edges set.', 'Easy', 12
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Salted Chocolate Rye Cookies');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Salted Chocolate Rye Cookies' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '1 cup rye flour' item, 0 sort_order UNION ALL SELECT '1/2 cup cocoa powder', 1 UNION ALL SELECT '1 cup dark chocolate', 2 UNION ALL SELECT '1/2 cup butter', 3 UNION ALL SELECT '3/4 cup brown sugar', 4 UNION ALL SELECT '1 egg', 5) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @pastries, 'Apple Cardamom Galette', 'Rustic apple pastry with warm cardamom and crisp, buttery edges.', 'Roll the pastry into a rough circle.\nArrange thin apple slices in the center and sprinkle with sugar and cardamom.\nFold the edges over the fruit and brush with egg.\nBake until deeply golden and bubbling.', 'Confident', 38
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Apple Cardamom Galette');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Apple Cardamom Galette' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '1 pie pastry round' item, 0 sort_order UNION ALL SELECT '3 crisp apples', 1 UNION ALL SELECT '1/3 cup brown sugar', 2 UNION ALL SELECT '1 tsp ground cardamom', 3 UNION ALL SELECT '1 tbsp lemon juice', 4) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @filipino, 'Ube Cheese Pandesal', 'Pillowy Filipino bread rolls with ube filling and a salty cheese center.', 'Make a soft yeast dough and let it rise until doubled.\nDivide the dough and fill each piece with ube halaya and cheese.\nSeal, roll in breadcrumbs, and proof until puffy.\nBake until lightly golden.', 'Confident', 18
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Ube Cheese Pandesal');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Ube Cheese Pandesal' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '3 cups bread flour' item, 0 sort_order UNION ALL SELECT '1 cup warm milk', 1 UNION ALL SELECT '1/2 cup ube halaya', 2 UNION ALL SELECT '1 cup cubed cheddar', 3 UNION ALL SELECT '1/4 cup breadcrumbs', 4 UNION ALL SELECT '2 tsp instant yeast', 5) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @pastries, 'Brown Butter Pear Tart', 'A delicate pear tart layered over nutty brown butter frangipane.', 'Blind bake the pastry shell until lightly golden.\nWhisk the brown butter filling and spread it in the shell.\nArrange pear slices in overlapping rows.\nBake until the pears soften and the filling puffs.', 'Showstopper', 45
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Brown Butter Pear Tart');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Brown Butter Pear Tart' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '1 tart pastry shell' item, 0 sort_order UNION ALL SELECT '3 ripe pears', 1 UNION ALL SELECT '1/2 cup butter', 2 UNION ALL SELECT '1/2 cup almond flour', 3 UNION ALL SELECT '1/3 cup sugar', 4) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @cakes, 'Honey Roasted Carrot Cake', 'A moist spiced cake topped with tangy cream cheese and toasted walnuts.', 'Roast the carrots until tender, then mash them.\nWhisk the wet ingredients and fold in the spiced dry ingredients.\nStir in carrots and walnuts, then bake until set.\nCool fully before spreading with frosting.', 'Confident', 42
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Honey Roasted Carrot Cake');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Honey Roasted Carrot Cake' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '2 cups grated carrots' item, 0 sort_order UNION ALL SELECT '1/2 cup honey', 1 UNION ALL SELECT '2 eggs', 2 UNION ALL SELECT '1 1/2 cups flour', 3 UNION ALL SELECT '1 tsp cinnamon', 4 UNION ALL SELECT '1/2 cup chopped walnuts', 5) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @cookies, 'Pistachio Thumbprint Biscuits', 'Buttery pistachio biscuits with a jewel of raspberry jam in the middle.', 'Cream butter and sugar until fluffy.\nMix in flour and ground pistachios to form a soft dough.\nRoll into balls, press a small well into each, and fill with jam.\nBake until pale gold.', 'Easy', 15
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Pistachio Thumbprint Biscuits');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Pistachio Thumbprint Biscuits' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '1 cup shelled pistachios' item, 0 sort_order UNION ALL SELECT '1 cup butter', 1 UNION ALL SELECT '1/2 cup sugar', 2 UNION ALL SELECT '1 1/2 cups flour', 3 UNION ALL SELECT '1/2 cup raspberry jam', 4) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

INSERT INTO recipes (user_id, category_id, title, description, steps, difficulty, bake_minutes)
SELECT @seed_user, @filipino, 'Calamansi Meringue Pie', 'A bright calamansi curd pie under a cloud of toasted meringue.', 'Bake the pastry shell until crisp.\nCook the calamansi curd until thick and glossy.\nPour the curd into the shell and chill until set.\nTop with meringue and toast until golden.', 'Showstopper', 20
WHERE NOT EXISTS (SELECT 1 FROM recipes WHERE title = 'Calamansi Meringue Pie');
SET @recipe_id = (SELECT id FROM recipes WHERE title = 'Calamansi Meringue Pie' LIMIT 1);
INSERT INTO ingredients (recipe_id, item, sort_order) SELECT @recipe_id, item, sort_order FROM (SELECT '1 baked pie crust' item, 0 sort_order UNION ALL SELECT '1/2 cup calamansi juice', 1 UNION ALL SELECT '4 egg yolks', 2 UNION ALL SELECT '1 cup sugar', 3 UNION ALL SELECT '4 egg whites', 4 UNION ALL SELECT '1/4 tsp cream of tartar', 5) seed WHERE NOT EXISTS (SELECT 1 FROM ingredients WHERE recipe_id = @recipe_id);

COMMIT;
