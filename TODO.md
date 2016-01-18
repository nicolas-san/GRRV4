# Todos
* refaire install.md avec explications pour composer
* faire un package avec les vendors et/ou
* refaire l'installer

# Futur Ajout
* Mise en place de la generation de pdf avancé - Partiel
* Envois d'une lettre en pdf avec le mail de confirmation
* Sauvegarde de la lettre sur le serveur

# Refacto idées
* Gestion lib externes avec composer
* IMPORTANT sécuriser toutes les entrées utilisateurs avec strip_tags, possibilité actuellement d'XSS dans la bdd 
* refacto begin_page pour l'intégrer à print_header puisque les deux préparent les infos pour afficher le début de page
* dé dupliquer le code des fonctions make_site_*
* il faudrait voir pour au moins, dédupliquer le code de week et week_all, puis refaire les events en fonction
* idem pour month et month_all
* prévoir un système de surcharge de paramètres globaux, spécifiques au template, un php d'init ou config du template
* prévoir un système de surcharge d'un template, comme les child de wp
* prévoir dans le template, une surcharge des élements de langues

# Warnings
* Les vars sont passées en GET à edit_entry_handler, avec les plugins risque de dépasser la taille max du _GET, il faudrait passer en POST
* tester les events sans passer le tpl, et faire les get et set tpl, peut être event dispatcher à accès aux vars en cours directement ?