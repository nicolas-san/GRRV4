<?php

use Grr\Event\EditEntryForm;

$dispatcher->addListener('editentry.form_before', function (EditEntryForm $event) {
    //echo $event->getTest();
    /* add a field */
    //return "ici";
});

$dispatcher->addListener('editentry.form_inside_plugin_area', function (EditEntryForm $event) {
    //echo $event->getTest();
    /* add a field */
    /* je met les data à afficher dans une var, pour préparer twig sur edit_entry */
    //$display = '<div>';
    //$display .=  '<input type="text" name="repas" value="Ici le repas">';


    //$display = '</div>';
    //return $display;
});