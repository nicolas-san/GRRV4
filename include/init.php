<?php

/**
 * init.php
 * Load des vendors, et fait les initialisations nécessaires
 *
 * Ce script fait partie de l'application GRR
 *
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @author    Bouteillier Nicolas <contact@kaizendo.fr>
 * @copyright Copyright 2015 Bouteillier Nicolas
 *
 * @link      http://www.gnu.org/licenses/licenses.html
 *
 * This file is part of GRR.
 *
 * GRR is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GRR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GRR; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
if (@file_exists('../include/connect.inc.php')) {
    $racine = '../';
} else {
    $racine = './';
}
/* load vendors */
require_once $racine . 'vendor/autoload.php';

/**
 * symfony autoloader
 */
//require_once $racine . 'vendor/symfony/class-loader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

//global $loader;
$autoloader = new UniversalClassLoader();

// enregistrez les espaces de noms et préfixes ici (voir ci-dessous)
//$loader->registerNamespace('Grr\Event', $racine . 'src/Grr/Event');
//$loader->registerNamespace('Grr', $racine . 'src/Grr/Event');
$autoloader->registerNamespace('Grr', $racine . 'src');
// vous pouvez rechercher dans l'« include_path » en dernier recours.

//$loader->useIncludePath(true);
$autoloader->register();
/*
 * Twig init
 */
global $twig;
$loader = new Twig_Loader_Filesystem([ $racine.'src/Grr/Resources/Templates/'.$template.'/views/', $racine.'src/Plugins/' ]);
/*
 * debug true, and profiler, only for dev env, todo : manage env dev or prod
 */
/*$twig = new Twig_Environment($loader, array(
    'cache' => $racine.'app/cache/',
    'debug' => false,
));*/
$twig = new Twig_Environment($loader, array(
    'cache' => false,
    'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());

/*
 * event dispatcher init
 */
use Symfony\Component\EventDispatcher\EventDispatcher;

global $dispatcher;
$dispatcher = new EventDispatcher();

/* ical export class */

/* init of YAML component */
use Symfony\Component\Yaml\Parser;

global $yaml;
$yaml = new Parser();

/**
 * je charge les plugins en dernier, pour qu'ils aient accès à tous les composants
 */
include_once 'plugins.php';

