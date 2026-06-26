USE nepal_tours;

-- Safe to run on an existing database. It only updates package image paths.
-- Also normalizes the old "cultural" category slug into the single public "culture" category.
UPDATE tours
SET category = 'culture'
WHERE category = 'cultural';

UPDATE tours
SET image = 'assets/images/Everest/photo-1544735716-87fa59a45b4e.jpg'
WHERE title = 'Everest Base Camp Trek';

UPDATE tours
SET image = 'assets/images/Everest/photo-1545918458-8394c4e4b21f.jpg'
WHERE title = 'Everest Panorama Trek';

UPDATE tours
SET image = 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Manaslu%20Circuit%20Trek%20-%20Mountain%20View.jpg?width=1200'
WHERE title = 'Manaslu Circuit Trek';

UPDATE tours
SET image = 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Janaki%20Temple%20Janakpur-Janakpur030315%20MG%2036680059.jpg?width=1200'
WHERE title = 'Janakpur Mithila Culture Tour';

UPDATE tours
SET image = 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Before%20sunrise%20over%20the%20Himalayas%20-%20Flickr%20-%20Nasir%20Khan%20Saikat.jpg?width=1200'
WHERE title = 'Nagarkot Sunrise Weekend';

UPDATE tours
SET image = 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Bandipur%20%E2%80%93%20Balabazar%20-%2001.jpg?width=1200'
WHERE title = 'Bandipur Weekend Heritage Escape';
