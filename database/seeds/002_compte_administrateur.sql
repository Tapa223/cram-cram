-- Compte administrateur initial.
-- Le mot de passe provisoire est stocké uniquement sous forme hachée (bcrypt) et
-- communiqué séparément. Le flag doit_changer_mdp impose de le remplacer dès la
-- première connexion. INSERT IGNORE : un compte existant avec cette adresse n'est
-- jamais modifié.
INSERT IGNORE INTO admin_users (nom, email, mot_de_passe_hash, role, actif, doit_changer_mdp)
VALUES ('Administrateur CRAM-CRAM', 'admin@cramcram.org', '$2y$12$mVoEHnkAmrIxEysSA.7Gr.4eUYppkD0/YUsvGXtjDnYahMBMPEsJu', 'admin', 1, 1);
