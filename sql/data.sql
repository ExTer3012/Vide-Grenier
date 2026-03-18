-- data.sql — Données initiales de la base de données
-- Vide Grenier en Ligne
-- Idempotent : peut être exécuté plusieurs fois sans dupliquer

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Utilisateurs
-- Les mots de passe sont hashés avec Argon2id
-- Compte admin : admin@admin.fr / password
-- Compte user  : john.doe@gmail.com / password
INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_admin`)
VALUES
    (1, 'John Doe',  'john.doe@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$placeholder_john',  0),
    (2, 'Admin',     'admin@admin.fr',     '$argon2id$v=19$m=65536,t=4,p=1$placeholder_admin', 1)
ON DUPLICATE KEY UPDATE
                     `username` = VALUES(`username`),
                     `is_admin` = VALUES(`is_admin`);

-- Articles
INSERT INTO `articles` (`id`, `name`, `description`, `published_date`, `user_id`, `views`, `picture`)
VALUES
    (1,  'Mappemonde à gratter',    'Carte du monde à gratter. Neuve dans son emballage d\'origine',                                         '2018-05-28', 1, 4,  '1.jpeg'),
    (2,  'Guide Berlin',            'Guide de voyage Lonely Planet. Petit format, pas de plan détachable',                                    '2018-05-28', 1, 29, '2.jpeg'),
    (3,  'Jeu Harry Potter',        'Harry Potter et la coupe de feu. Jeu Nintendo Gamecube. Complet, très bon état',                         '2018-05-28', 1, 65, '3.jpeg'),
    (4,  'Peinture Cheval',         'Tableau numéro d\'art cheval. Fait main',                                                                '2018-05-28', 1, 7,  '4.jpeg'),
    (5,  'Cluedo Games of Thrones', 'Jeu de société Cluedo Games of Thrones en très bon état.',                                              '2018-05-28', 1, 8,  '5.jpeg'),
    (6,  'Jeu de boules',           'Mini boules de pétanque. Boîte contenant 6 boules, un cochonnet et une ficelle. En bon état.',           '2018-05-28', 1, 8,  '6.jpeg'),
    (7,  'Livre',                   'La déclaration de Gemma Malley. Lu une fois. Très bon état',                                             '2018-05-28', 2, 9,  '7.jpeg'),
    (8,  'Puzzle Harry Potter',     'Puzzle 1000 pièces Fantastic Beast - Neuf, dans son emballage d\'origine',                               '2018-05-28', 1, 14, '8.jpeg'),
    (9,  'Cadre New York',          'Cadre en toile plastifiée, Taxis New York 100x50.',                                                      '2018-05-28', 1, 9,  '9.jpeg'),
    (10, 'Calculatrice',            'Calculatrice Casio, erreur de modèle.',                                                                  '2018-05-28', 1, 11, '10.jpeg'),
    (11, 'Djembé',                  'Djembé en bois peu servi.',                                                                              '2018-05-28', 2, 15, '11.jpeg'),
    (12, 'Pull de noel',            'Pull de noel Coca Cola thème du ski, taille xs hommes. Jamais porté car trop petit.',                    '2018-05-28', 1, 13, '12.jpeg'),
    (13, 'Taie d\'oreiller',        'Taie d\'oreiller h&m. Très bon état. Dimensions : 47x47cm',                                             '2018-05-28', 1, 13, '13.jpeg'),
    (14, 'Beau Livre',              'The Grand Tour des éditions taschen. Dans sa boîte cartonnée. Grand format : 41x30x7 - 8 kilos.',        '2018-05-28', 1, 17, '14.jpeg'),
    (15, 'Mules Minelli',           'Jamais portées. Trop petites, étroites.',                                                                '2018-05-28', 1, 15, '15.jpeg'),
    (16, 'Bougie',                  'Bougie Bath&Body Works. 3 Mèches Pure White Cotton.',                                                    '2018-05-28', 1, 16, '16.jpeg'),
    (17, 'Figurines Harry Potter',  'Minifigures harry potter de dumbledore avec le sachet.',                                                  '2018-05-28', 1, 17, '17.jpeg'),
    (18, 'Peluche R2D2',            'Presque jamais utilisée.',                                                                               '2018-05-28', 1, 18, '18.jpeg'),
    (19, 'Carte pokemon',           'État passable. Carte Lugia',                                                                             '2018-05-28', 1, 23, '19.jpeg'),
    (20, 'Moules Muffin',           '9 moules à muffins',                                                                                     '2018-05-28', 2, 21, '20.jpeg'),
    (21, 'Meuble',                  'Beau meuble en fer forgé',                                                                               '2018-05-28', 1, 22, '21.jpeg'),
    (22, 'Montre femme',            'Montre femme bracelet doré réglable, cadran marbre noir et blanc. Jamais portée.',                       '2018-05-28', 1, 22, '22.jpeg'),
    (23, 'Produit beauté',          'Eau micellaire démaquillante Yves Rocher. Neuf jamais ouvert',                                           '2018-05-28', 1, 24, '23.jpeg'),
    (24, 'Sac noir',                'Petite sacoche noire simple neuve. Jamais portée',                                                       '2018-05-28', 1, 24, '24.jpeg'),
    (25, 'Boite airpods',           'Boite airpods seule (écouteur cassé).',                                                                  '2018-05-28', 1, 26, '25.jpeg'),
    (26, 'VHS Destination Finale',  'Cassette vidéo horreur bon état VHS.',                                                                   '2018-05-28', 1, 27, '26.jpeg'),
    (27, 'Porte-clés',              'Porte-clés fétiche arumbaya vu dans une BD Tintin',                                                      '2018-05-28', 1, 27, '27.jpeg'),
    (28, 'Manteau Desigual',        'Neuf. Taille 42 mais taille petit (correspond à un S).',                                                 '2018-05-28', 1, 28, '28.jpeg'),
    (29, 'Cintres',                 'Cintres en aluminium, parfaits pour pantalons et jupes. Plus de 80 disponibles.',                        '2018-05-28', 1, 30, '29.jpeg'),
    (30, 'Chaise de bureau',        'Chaise de bureau rose pour enfant.',                                                                     '2018-05-28', 1, 30, '30.jpeg'),
    (31, 'Tapis enfant',            'Tapis épaisseur moquette. Cadre cousu. Dessous caoutchouc. 1,20m x 80cm.',                              '2018-05-28', 1, 33, '31.jpeg')
ON DUPLICATE KEY UPDATE
    `views` = VALUES(`views`);