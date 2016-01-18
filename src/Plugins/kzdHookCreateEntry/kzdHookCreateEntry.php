<?php
/**
 * kzdHookCreateEntry.php
 *
 * Ce script fait partie du plugin kzdHookCreateEntry
 *
 * @author    Bouteillier Nicolas <contact@kaizendo.fr>
 * @copyright Copyright 2015 Bouteillier Nicolas
 *
 * @link      http://www.gnu.org/licenses/licenses.html
 *
 * This file is part of GRR.
 *
 * GRR is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

include_once($racine.'src/Plugins/kzdHookCreateEntry/Listener/onEditEntryHandlerBeforeDb.php');


/* parse config file */
use Symfony\Component\Yaml\Exception\ParseException;
global $yaml, $configValuesHookCreateEntry;

try {
    $configValuesHookCreateEntry = $yaml->parse(file_get_contents($racine.'src/Plugins/kzdHookCreateEntry/Config/config.yaml'));
} catch (ParseException $e) {
    printf("Unable to parse the YAML string: %s", $e->getMessage());
}
