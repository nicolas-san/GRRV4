<?php
/**
 * @author Bouteillier Nicolas <contact@kaizendo.fr>
 * Date: 17/09/15
 *
 * This file load the plugins
 */

/* todo séparer plugin admin et front ? */
if (@file_exists('../include/connect.inc.php')) {
    $racine = '../';
} else {
    $racine = './';
}
include_once $racine."src/Plugins/Acme/acme.php";
include_once $racine."src/Plugins/kzdHookCreateEntry/kzdHookCreateEntry.php";