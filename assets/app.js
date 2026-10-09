document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-favorite]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            const response = await fetch('index.php?action=favorite', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'},
                body: new URLSearchParams({recipe_id: button.dataset.favorite, csrf: button.dataset.csrf})
            });
            const result = await response.json();
            if (result.ok) {
                button.classList.toggle('saved', result.saved);
                button.textContent = button.classList.contains('favorite-detail')
                    ? (result.saved ? '♥ Saved to favorites' : '♡ Add to favorites')
                    : (result.saved ? '♥ Saved' : '♡ Favorite');
                button.setAttribute('aria-label', result.saved ? 'Remove from favorites' : 'Add to favorites');
            }
            button.disabled = false;
        });
    });

    const ingredientList = document.querySelector('[data-ingredients]');
    const addIngredient = document.querySelector('[data-add-ingredient]');
    if (ingredientList && addIngredient) {
        addIngredient.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'ingredient-row';
            row.innerHTML = '<input class="form-control" name="ingredients[]" required maxlength="180" placeholder="e.g. 2 cups flour"><button type="button" class="remove-ingredient" aria-label="Remove ingredient">×</button>';
            ingredientList.appendChild(row);
        });
        ingredientList.addEventListener('click', (event) => {
            if (event.target.matches('.remove-ingredient') && ingredientList.children.length > 1) event.target.parentElement.remove();
        });
    }

});
