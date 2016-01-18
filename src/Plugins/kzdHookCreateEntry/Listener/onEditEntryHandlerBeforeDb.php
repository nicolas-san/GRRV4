<?php

use Grr\Event\EditEntryHandlerForCreate;

$dispatcher->addListener('editentryhandler.beforedb', function (EditEntryHandlerForCreate $event) {

    /* config from config.yml */
    global $configValuesHookCreateEntry;

    /* get the data from the event */
    $data = $event->getData();

    /* get the champs_libre field associated with the current user */
    $champsLibre = mysqli_result(grr_sql_query('SELECT champs_libre FROM '.TABLE_PREFIX.'_utilisateurs WHERE login="'.$data['create_by'].'"'), 0);

    $userInfo = explode('@',$champsLibre);
    /* first case is the domain associated with the user, I use it to override the fields for mrbsCreate* functions */
    /*echo "<pre>BEFORE";
    var_dump($data);
    echo "<hr>";
    var_dump($userInfo);
    echo "</pre>";*/

    if ($data['entry_moderate'] == 1) {
        /* the entry is moderate */
        //echo "<br>MODERATE";
        //echo $userInfo[0] ." -- ". $data['area'];
        if ($userInfo[0] == $data['area']) {
            /* this user is not moderate for this ressource */
            $data['entry_moderate'] = 0;
            $data['send_mail_moderate'] = 0;
            //echo "<br>dans le if chuipascensé moderé<br>";
        }
    }

    /* set back the event */
    $event->setData($data);

    /*echo "<pre>AFTER";
    var_dump($data);
    echo "<hr>";
    var_dump($userInfo);
    echo "</pre>";

    die();*/

});