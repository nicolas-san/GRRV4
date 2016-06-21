GRR
===================

** Développement arrêté, normalement fonctionnelle mais en gros chantier, cette version pour l'instant est à l'arrêt et cherche éventuellement un dév **
# Pour la tester :
- cloner le repos
- lancer un ./composer.phar install
- visiter la homepage pour lancer l'install
- il faut immédiatement après l'install aller dans l'admin et configurer un domaine et une ressource

** Notes sur la version 4 **
 - la version 4 est une version de transition, le front est géré avec Twig, et les nouveaux vendors sont gérés avec composer.
 - une compatibilité maximale est prévue entre la V3 et la V4, les versions suivantes ne le seront probablement pas

**Requière :**

> PHP 5.4+
> Mysql 5.4+

http://grr.devome.com/

GRR est un outil de gestion et de réservation de ressources. **GRR** est une adaptation d'une application **MRBS**.

----------

Installation
-------------

Pour obtenir une description complète de la procédure d'installation, veuillez vous reporter au fichier "**INSTALL.txt**".

Pour une installation simplifiée, décompressez simplement cette archive sur un serveur, et indiquez l'adresse où se trouvent les fichiers extraits dans un navigateur (ex: http://www.monsite.fr/grr).

>Préalables pour l'installation automatisée :
>disposer d'un espace FTP sur un serveur, pour y transférer les fichiers
>disposer d'une base de données MySQL (adresse du serveur MySQL, login, mot de passe)

Licence
-------------
**GRR** est publié sous les termes de la **GNU General Public Licence**, dont le contenu est disponible dans le fichier "**LICENSE**", en anglais et dans le fichiers "**licence_fr.html**" en français. **GRR** est gratuit, vous pouvez le copier, le distribuer, et le modifier, à condition que chaque partie de **GRR** réutilisée ou modifiée reste sous licence **GNU GPL**. Par ailleurs et dans un soucis d'efficacité, merci de rester en contact avec le développeur de **GRR** pour éventuellement intégrer vos contributions à une distribution ultérieure.

Enfin, **GRR** est livré en l'état sans aucune garantie. Les auteurs de cet outil ne pourront en aucun cas être tenus pour responsables d'éventuels bugs.


Remarques concernant la sécurité
-------------------

La sécurisation de **GRR** est dépendante de celle du serveur. Nous vous recommandons d'utiliser un serveur Apache ou Nginx sous Linux, en utilisant le protocole **https** (transferts de données cryptées), et en veillant à toujours utiliser les dernières versions des logiciels impliqués (notamment **Apache/Nginx** et **PHP**).

L'EQUIPE DE DEVELOPPEMENT DE GRR NE SAURAIT EN AUCUN CAS ETRE TENUE POUR RESPONSABLE EN CAS D'INTRUSION EXTERIEURE LIEE A UNE FAIBLESSE DE GRR OU DE SON SUPPORT SERVEUR.
