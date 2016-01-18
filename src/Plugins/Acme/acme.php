<?php

use Grr\Event\EditEntryForm;

//global $dispatcher;

$dispatcher->addListener('editentry.form_before', function (EditEntryForm $event) {
    //echo $event->getTest();
    /* add a field */
    //echo 'Form modifié pour la gestion des repas';
});

$dispatcher->addListener('editentry.form_inside_plugin_area', function (EditEntryForm $event) {
    //echo $event->getTest();
    /* add a field */
    //echo '<input type="text" name="repas" value="Ici le repas">';
});