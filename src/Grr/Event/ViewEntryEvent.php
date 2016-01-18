<?php
/**
 * @author Bouteillier Nicolas <contact@kaizendo.fr>
 *
 */
namespace Grr\Event;

final class ViewEntryEvent
{
    /**
     * L'évènement viewEntry.end est lancé à la fin de view_entry
     *
     * Le « listener » de l'évènement reçoit une instance de
     * Grr\Event\ViewEntry
     *
     * @var string
     */
    const VIEWENTRY_END = 'viewentry.end';

}