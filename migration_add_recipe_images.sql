USE bakehouse;

ALTER TABLE recipes
    ADD COLUMN image_path VARCHAR(255) NULL AFTER steps;
