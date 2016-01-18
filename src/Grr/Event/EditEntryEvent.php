<?php
/**
 * EditEntry.php
 *
 * Évènements du script qui gère l'affichage du formulaire edit entry
 *
 * Ce script fait partie de l'application GRR
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
namespace Grr\Event;

final class EditEntryEvent
{
    /**
     * L'évènement editEntry.form.before est lancé juste avant la balise form
     *
     * Le « listener » de l'évènement reçoit une instance de
     * Grr\Event\EditEntryForm
     *
     * @var string
     */
    const EDITENTRY_FORM_BEFORE = 'editentry.form_before';
    const EDITENTRY_FORM_AFTER = 'editentry.form_after';
    const EDITENTRY_FORM_INSIDE_START = 'editentry.form_inside_start';
    const EDITENTRY_FORM_INSIDE_END = 'editentry.form_inside_stop';
    const EDITENTRY_FORM_INSIDE_PLUGIN_AREA = 'editentry.form_inside_plugin_area';
}