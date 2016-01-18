<?php
/**
 * edit_entry.php
 * Interface d'édition d'une réservation
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2010-04-07 15:38:14 $.
 *
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 *
 * @author      Bouteillier Nicolas <bouteillier.nicolas@kaizendo.fr>
 * @copyright	Copyright 2015 Bouteillier Nicolas
 *
 * @link		http://www.gnu.org/licenses/licenses.html
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
include 'include/admin.inc.php';

use Grr\Event\EditEntryForm;
use Grr\Event\EditEntryEvent;

$grr_script_name = 'edit_entry.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    settype($id, 'integer');
} else {
    $id = null;
}
/* si id != null c'est une entry qui existe déjà, donc on est dans le cas d'une modification */
$period = isset($_GET['period']) ? $_GET['period'] : null;
if (isset($period)) {
    settype($period, 'integer');
}
if (isset($period)) {
    $end_period = $period;
}
$edit_type = isset($_GET['edit_type']) ? $_GET['edit_type'] : null;
if (!isset($edit_type)) {
    $edit_type = '';
}
$page = verif_page();
if (isset($_GET['hour'])) {
    $hour = $_GET['hour'];
    settype($hour, 'integer');
    if ($hour < 10) {
        $hour = '0'.$hour;
    }
} else {
    $hour = null;
}
if (isset($_GET['minute'])) {
    $minute = $_GET['minute'];
    settype($minute, 'integer');
    if ($minute < 10) {
        $minute = '0'.$minute;
    }
} else {
    $minute = null;
}
$rep_num_weeks = '';
global $twentyfourhour_format;
if (!isset($day) || !isset($month) || !isset($year)) {
    $day = date('d');
    $month = date('m');
    $year = date('Y');
}
if (isset($id)) {
    if ($info = mrbsGetEntryInfo($id)) {
        $area = mrbsGetRoomArea($info['room_id']);
        $room = $info['room_id'];
        $id_site = mrbsGetAreaSite($area);
    } else {
        $area = -1;
        $room = -1;
    }
} else {
    Definition_ressource_domaine_site();
}
if (@file_exists('language/lang_subst_'.$area.'.'.$locale)) {
    include 'language/lang_subst_'.$area.'.'.$locale;
}
get_planning_area_values($area);
$affiche_mess_asterisque = false;
$type_affichage_reser = grr_sql_query1('SELECT type_affichage_reser FROM '.TABLE_PREFIX."_room WHERE id='".$room."'");
$delais_option_reservation = grr_sql_query1('SELECT delais_option_reservation FROM '.TABLE_PREFIX."_room WHERE id='".$room."'");
$qui_peut_reserver_pour = grr_sql_query1('SELECT qui_peut_reserver_pour FROM '.TABLE_PREFIX."_room WHERE id='".$room."'");
$back = '';
if (isset($_SERVER['HTTP_REFERER'])) {
    /* TODO TEST ET SECURISER ÇA */
    $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
}
$longueur_liste_ressources_max = Settings::get('longueur_liste_ressources_max');
if ($longueur_liste_ressources_max == '') {
    $longueur_liste_ressources_max = 20;
}
if (check_begin_end_bookings($day, $month, $year)) {
    if ((Settings::get('authentification_obli') == 0) && (getUserName() == '')) {
        $type_session = 'no_session';
    } else {
        $type_session = 'with_session';
    }
    /* TODO PASSER À TWIG */
    showNoBookings($day, $month, $year, $back);
    exit();
}
if ((authGetUserLevel(getUserName(), -1) < 2) && (auth_visiteur(getUserName(), $room) == 0)) {
    /* TODO passer à twig */
    showAccessDenied($back);
    exit();
}
if (authUserAccesArea(getUserName(), $area) == 0) {
    /* TODO passer à twig */
    showAccessDenied($back);
    exit();
}
if (isset($id) && ($id != 0)) {
    $compt = 0;
} else {
    $compt = 1;
}
if (UserRoomMaxBooking(getUserName(), $room, $compt) == 0) {
/* TODO passer à twig */
    showAccessDeniedMaxBookings($day, $month, $year, $room, $back);
    exit();
}
$etype = 0;
if (isset($id)) {
    $sql = 'SELECT name, beneficiaire, description, start_time, end_time, type, room_id, entry_type, repeat_id, option_reservation, jours, create_by, beneficiaire_ext, statut_entry, clef, courrier FROM '.TABLE_PREFIX."_entry WHERE id=$id";
    $res = grr_sql_query($sql);
    if (!$res) {
        fatal_error(1, grr_sql_error());
    }
    if (grr_sql_count($res) != 1) {
        fatal_error(1, get_vocab('entryid').$id.get_vocab('not_found'));
    }
    $row = grr_sql_row($res, 0);
    grr_sql_free($res);
    $breve_description = $row[0];
    $beneficiaire = $row[1];
    $beneficiaire_ext = $row[12];
    $tab_benef = donne_nom_email($beneficiaire_ext);
    $create_by = $row[11];
    $description = $row[2];
    $statut_entry = $row[13];
    $start_day = strftime('%d', $row[3]);
    $start_month = strftime('%m', $row[3]);
    $start_year = strftime('%Y', $row[3]);
    $start_hour = strftime('%H', $row[3]);
    $start_min = strftime('%M', $row[3]);
    $end_day = strftime('%d', $row[4]);
    $end_month = strftime('%m', $row[4]);
    $end_year = strftime('%Y', $row[4]);
    $end_hour = strftime('%H', $row[4]);
    $end_min = strftime('%M', $row[4]);
    $duration = $row[4] - $row[3];
    $etype = $row[5];
    $room_id = $row[6];
    $entry_type = $row[7];
    $rep_id = $row[8];
    $option_reservation = $row[9];
    $jours_c = $row[10];
    $clef = $row[14];
    $courrier = $row[15];
    $modif_option_reservation = 'n';
    if ($entry_type >= 1) {
        $sql = 'SELECT rep_type, start_time, end_date, rep_opt, rep_num_weeks, end_time, type, name, beneficiaire, description
		FROM '.TABLE_PREFIX."_repeat WHERE id='".protect_data_sql($rep_id)."'";
        $res = grr_sql_query($sql);
        if (!$res) {
            fatal_error(1, grr_sql_error());
        }
        if (grr_sql_count($res) != 1) {
            fatal_error(1, get_vocab('repeat_id').$rep_id.get_vocab('not_found'));
        }
        $row = grr_sql_row($res, 0);
        grr_sql_free($res);
        $rep_type = $row[0];
        if ($rep_type == 2) {
            $rep_num_weeks = $row[4];
        }
        if ($edit_type == 'series') {
            $start_day = (int) strftime('%d', $row[1]);
            $start_month = (int) strftime('%m', $row[1]);
            $start_year = (int) strftime('%Y', $row[1]);
            $start_hour = (int) strftime('%H', $row[1]);
            $start_min = (int) strftime('%M', $row[1]);
            $duration = $row[5] - $row[1];
            $end_day = (int) strftime('%d', $row[5]);
            $end_month = (int) strftime('%m', $row[5]);
            $end_year = (int) strftime('%Y', $row[5]);
            $end_hour = (int) strftime('%H', $row[5]);
            $end_min = (int) strftime('%M', $row[5]);
            $rep_end_day = (int) strftime('%d', $row[2]);
            $rep_end_month = (int) strftime('%m', $row[2]);
            $rep_end_year = (int) strftime('%Y', $row[2]);
            $type = $row[6];
            $breve_description = $row[7];
            $beneficiaire = $row[8];
            $description = $row[9];
            if ($rep_type == 2) {
                $rep_day[0] = $row[3][0] != '0';
                $rep_day[1] = $row[3][1] != '0';
                $rep_day[2] = $row[3][2] != '0';
                $rep_day[3] = $row[3][3] != '0';
                $rep_day[4] = $row[3][4] != '0';
                $rep_day[5] = $row[3][5] != '0';
                $rep_day[6] = $row[3][6] != '0';
            } else {
                $rep_day = array(0, 0, 0, 0, 0, 0, 0);
            }
        } else {
            $rep_end_date = utf8_encode(strftime($dformat, $row[2]));
            $rep_opt = $row[3];
            $start_time = $row[1];
            $end_time = $row[5];
        }
    } else {
        $flag_periodicite = 'y';
        $rep_id = 0;
        $rep_type = 0;
        $rep_end_day = $day;
        $rep_end_month = $month;
        $rep_end_year = $year;
        $rep_day = array(0, 0, 0, 0, 0, 0, 0);
        $rep_jour = 0;
    }
} else {
    if ($enable_periods == 'y') {
        $duration = 60;
    } else {
        $duree_par_defaut_reservation_area = grr_sql_query1('SELECT duree_par_defaut_reservation_area FROM '.TABLE_PREFIX."_area WHERE id='".$area."'");
        if ($duree_par_defaut_reservation_area == 0) {
            $duree_par_defaut_reservation_area = $resolution;
        }
        $duration = $duree_par_defaut_reservation_area;
    }
    $edit_type = 'series';
    if (Settings::get('remplissage_description_breve') == '2') {
        $breve_description = $_SESSION['prenom'].' '.$_SESSION['nom'];
    } else {
        $breve_description = '';
    }
    $beneficiaire = getUserName();
    $tab_benef['nom'] = '';
    $tab_benef['email'] = '';
    $create_by = getUserName();
    $description = '';
    $start_day = $day;
    $start_month = $month;
    $start_year = $year;
    $start_hour = $hour;
    (isset($minute)) ? $start_min = $minute : $start_min = '00';
    if ($enable_periods == 'y') {
        $end_day = $day;
        $end_month = $month;
        $end_year = $year;
        $end_hour = $hour;
        (isset($minute)) ? $end_min = $minute : $end_min = '00';
    } else {
        $now = mktime($hour, $minute, 0, $month, $day, $year);
        $fin = $now + $duree_par_defaut_reservation_area;
        $end_day = date('d', $fin);
        $end_month = date('m', $fin);
        $end_year = date('Y', $fin);
        $end_hour = date('H', $fin);
        $end_min = date('i', $fin);
    }
    $type = '';
    $room_id = $room;
    $id = 0;
    $rep_id = 0;
    $rep_type = 0;
    $rep_end_day = $day;
    $rep_end_month = $month;
    $rep_end_year = $year;
    $rep_day = array(0, 0, 0, 0, 0, 0, 0);
    $rep_jour = 0;
    $option_reservation = -1;
    $modif_option_reservation = 'y';
}
/* get user champs_libre */
$tplArrayEditEntry['userChampsLibre'] = mysqli_result(grr_sql_query('SELECT champs_libre FROM '.TABLE_PREFIX.'_utilisateurs WHERE login="'.getUserName().'"'), 0);

if (isset($_GET['Err'])) {
    $Err = $_GET['Err'];
}
if ($enable_periods == 'y') {
    toPeriodString($start_min, $duration, $dur_units);
} else {
    toTimeString($duration, $dur_units, true);
}
if (!getWritable($beneficiaire, getUserName(), $id)) {
    showAccessDenied($back);
    exit;
}
$nb_areas = 0;
$sql = 'SELECT id, area_name FROM '.TABLE_PREFIX.'_area';
$res = grr_sql_query($sql);
$allareas_id = array();
if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); ++$i) {
        array_push($allareas_id, $row[0]);
        if (authUserAccesArea(getUserName(), $row[0]) == 1) {
            ++$nb_areas;
        }
    }
}
$use_select2 = 'y';
print_header($day, $month, $year, 'with_session', false);

/* intégration de twig, premier passage, probablement à refactoriser pour éviter les variables assignées deux fois */
$tplArrayEditEntry['area'] = $area;
$tplArrayEditEntry['id'] = $id;
$tplArrayEditEntry['room'] = $room;
$tplArrayEditEntry['etype'] = $etype;
$tplArrayEditEntry['beneficiaire'] = $beneficiaire;
$tplArrayEditEntry['edit_type'] = $edit_type;

/* settings */
$tplArrayEditEntry['settings']['joursCyclesActif'] = Settings::get('jours_cycles_actif');
$tplArrayEditEntry['settings']['remplissageDescriptionBreve'] = Settings::get('remplissage_description_breve');
/* je ne suis pas sûr que show_courrier serve à ça, mais comme les champs pour le courrier ne sont pas soumis à un if, et que ça me paraît cohérent pour le moment, je vais utiliser show_courrier pour cacher les champs courrier */
$tplArrayEditEntry['settings']['showCourrier'] = Settings::get('show_courrier');

/* vocable */
$tplArrayEditEntry['vocab']['you_have_not_entered'] = get_vocab('you_have_not_entered');
$tplArrayEditEntry['vocab']['deux_points'] = get_vocab('deux_points');
$tplArrayEditEntry['vocab']['nom_beneficiaire'] = get_vocab('nom beneficiaire');
$tplArrayEditEntry['vocab']['brief_description'] = get_vocab('brief_description');
$tplArrayEditEntry['vocab']['required'] = get_vocab('required');
$tplArrayEditEntry['vocab']['is_not_numeric'] = get_vocab('is_not_numeric');
$tplArrayEditEntry['vocab']['choose_a_type'] = get_vocab('choose_a_type');
$tplArrayEditEntry['vocab']['choose_a_day'] = get_vocab('choose_a_day');
$tplArrayEditEntry['vocab']['addentry'] = get_vocab('addentry');
$tplArrayEditEntry['vocab']['editseries'] = get_vocab('editseries');
$tplArrayEditEntry['vocab']['copyentry'] = get_vocab('copyentry');
$tplArrayEditEntry['vocab']['editentry'] = get_vocab('editentry');
$tplArrayEditEntry['vocab']['namebooker'] = get_vocab('namebooker');
$tplArrayEditEntry['vocab']['fulldescription'] = get_vocab('fulldescription');
$tplArrayEditEntry['vocab']['reservations_moderees'] = get_vocab('reservations_moderees');
$tplArrayEditEntry['vocab']['fulldescription'] = get_vocab('fulldescription');
$tplArrayEditEntry['vocab']['date'] = get_vocab('date');
$tplArrayEditEntry['vocab']['definir_par_defaut'] = get_vocab('definir par defaut');
$tplArrayEditEntry['vocab']['nom_beneficiaire'] = strip_tags(get_vocab('nom_beneficiaire'));
$tplArrayEditEntry['vocab']['email_beneficiaire'] = get_vocab('email beneficiaire');
$tplArrayEditEntry['vocab']['no_compatibility_with_repeat_type'] = get_vocab('no_compatibility_with_repeat_type');

foreach ($allareas_id as $idtmp) {
    //$overload_fields = mrbsOverloadGetFieldslist($idtmp);
    $tplArrayEditEntry['overloadFields'][] = mrbsOverloadGetFieldslist($idtmp);
}

/* $A est le titre de la page */
if ($id == 0) {
    $A = get_vocab('addentry');
    $tplArrayEditEntry['titrePage'] = get_vocab('addentry');
} else {
    if ($edit_type == 'series') {
        $A = get_vocab('editseries');
        $tplArrayEditEntry['titrePage'] = get_vocab('editseries');
    } else {
        if (isset($_GET['copier'])) {
            $A = get_vocab('copyentry');
            $tplArrayEditEntry['titrePage'] = get_vocab('copyentry');
        } else {
            $A = get_vocab('editentry');
            $tplArrayEditEntry['titrePage'] = get_vocab('editentry');
        }
    }
}
$B = get_vocab('namebooker');
$tplArrayEditEntry['nameBooker'] = get_vocab('namebooker');
if (Settings::get('remplissage_description_breve') == '1') {
    $B .= ' *';
    $tplArrayEditEntry['nameBooker'] .= ' *';
    $affiche_mess_asterisque = true;
}
$B .= get_vocab('deux_points');
$tplArrayEditEntry['nameBooker'] .= get_vocab('deux_points');

$C = htmlspecialchars($breve_description);
$tplArrayEditEntry['breveDescription'] = htmlspecialchars(strip_tags($breve_description));

$D = get_vocab('fulldescription');
$E = htmlspecialchars($description);
$tplArrayEditEntry['description'] = htmlspecialchars(strip_tags($description));

$F = get_vocab('date').get_vocab('deux_points');


$sql = 'SELECT area_id, capacity, room_name FROM '.TABLE_PREFIX."_room WHERE id=$room_id";
$res = grr_sql_query($sql);
$row = grr_sql_row($res, 0);
$area_id = $row[0];
$tplArrayEditEntry['capacity'] = $row[1];
$tplArrayEditEntry['roomName'] = $row[2];

$moderate = grr_sql_query1('SELECT moderate FROM '.TABLE_PREFIX."_room WHERE id='".$room_id."'");

$tplArrayEditEntry['areaId'] = $row[0];
$tplArrayEditEntry['moderate'] = $moderate;

/*echo '<h2>'.$A.'</h2>'.PHP_EOL;*/
/*if ($moderate) {
    echo '<h3><span class="texte_ress_moderee">'.$vocab['reservations_moderees'].'</span></h3>'.PHP_EOL;
}*/

/**
* Avant le formulaire
 */
// crée le EntryFormEvent et le répartit
global $id_site;
$event = new EditEntryForm($id_site, $area_id, $tplArrayEditEntry, $id);
$dispatcher->dispatch(EditEntryEvent::EDITENTRY_FORM_BEFORE, $event);

/*echo '<form class="form-inline" id="main" action="edit_entry_handler.php" method="get">'.PHP_EOL;*/

if ($enable_periods == 'y') {
    $sql = 'SELECT id, area_name FROM '.TABLE_PREFIX."_area WHERE id='".$area."' ORDER BY area_name";
} else {
    $sql = 'SELECT id, area_name FROM '.TABLE_PREFIX."_area WHERE enable_periods != 'y' ORDER BY area_name";
}
$res = grr_sql_query($sql);
if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        if (authUserAccesArea(getUserName(), $row[0]) == 1) {
            /*echo "AREA<br>";
            var_dump($row);*/
            $tplArrayEditEntry['areasIds'][$i]['id'] = $row[0];
            $tplArrayEditEntry['areasIds'][$i]['name'] = $row[1];
            /*print '      case "'.$row[0]."\":\n";*/
            $sql2 = 'SELECT id, room_name FROM '.TABLE_PREFIX."_room WHERE area_id='".$row[0]."'";
            $tab_rooms_noaccess = verif_acces_ressource(getUserName(), 'all');
            foreach ($tab_rooms_noaccess as $key) {
                $sql2 .= " AND id != $key ";
            }
            $sql2 .= ' ORDER BY room_name';
            $res2 = grr_sql_query($sql2);
            if ($res2) {
                $len = grr_sql_count($res2);
                $tplArrayEditEntry['areasIds'][$i]['roomsObjSize'] = min($longueur_liste_ressources_max, $len);
                /*print 'roomsObj.size='.min($longueur_liste_ressources_max, $len).";\n";*/
                for ($j = 0; ($row2 = grr_sql_row($res2, $j)); $j++) {
                    $tplArrayEditEntry['areasIds'][$i]['roomsObjOption'][$j] = $row2;
                    //print "roomsObj.options[$j] = new Option(\"".str_replace('"', '\\"', $row2[1]).'",'.$row2[0].")\n";
                }
                //print "roomsObj.options[0].selected = true\n";
            }
            //print "break\n";
        }
    }
}

/*echo '<div id="error"></div>';
echo '<table class="table-bordered EditEntryTable"><tr>'.PHP_EOL;
echo '<td style="width:50%; vertical-align:top; padding-left:15px; padding-top:5px; padding-bottom:5px;">'.PHP_EOL;
echo '<table class="table-header">'.PHP_EOL;*/

if (((authGetUserLevel(getUserName(), -1, 'room') >= $qui_peut_reserver_pour) || (authGetUserLevel(getUserName(), $area, 'area') >= $qui_peut_reserver_pour)) && (($id == 0) || (($id != 0) && (authGetUserLevel(getUserName(), $room) > 2)))) {
    $flag_qui_peut_reserver_pour = 'yes';
    $tplArrayEditEntry['flagQuiPeutReserverPour'] = 'yes';
    $tplArrayEditEntry['vocab']['reservation_au_nom_de'] = get_vocab('reservation au nom de');
    $tplArrayEditEntry['vocab']['personne_exterieure'] = get_vocab('personne exterieure');

/*    echo '<tr>'.PHP_EOL;
    echo '<td class="E">'.PHP_EOL;
    echo '<b>'.ucfirst(trim(get_vocab('reservation au nom de'))).get_vocab('deux_points').'</b>'.PHP_EOL;
    echo '</td>'.PHP_EOL;
    echo '</tr>'.PHP_EOL;
    echo '<tr>'.PHP_EOL;
    echo '<td class="CL">'.PHP_EOL;
    echo '<div class="col-xs-6">'.PHP_EOL;
    echo '<select size="1" class="form-control" name="beneficiaire" id="beneficiaire" onchange="setdefault(\'beneficiaire_default\',\'\');check_4();insertProfilBeneficiaire();">'.PHP_EOL;
    echo '<option value="" >'.get_vocab('personne exterieure').'</option>'.PHP_EOL;*/


    $sql = 'SELECT DISTINCT login, nom, prenom, tel FROM '.TABLE_PREFIX."_utilisateurs WHERE (etat!='inactif' and statut!='visiteur' ) OR (login='".$beneficiaire."') ORDER BY nom, prenom";
    $res = grr_sql_query($sql);
    if ($res) {
        for ($i = 0; ($row = grr_sql_row($res, $i)); ++$i) {
            $tplArrayEditEntry['options'][$i]['value'] = $row[0];
            //echo '<option value="'.$row[0].'" ';
            if ($id == 0 && isset($_COOKIE['beneficiaire_default'])) {
                //$tplArrayEditEntry['options'][$i]['cookie'] = $_COOKIE['beneficiaire_default'];
                $cookie = $_COOKIE['beneficiaire_default'];
            } else {
                //$tplArrayEditEntry['options'][$i]['cookie'] = '';
                $cookie = '';
            }
            if ((!$cookie && strtolower($beneficiaire) == strtolower($row[0])) || ($cookie && $cookie == $row[0])) {
                //echo ' selected="selected" ';
                $tplArrayEditEntry['options'][$i]['selected'] = true;
            }
            //echo '>'.$row[1].' '.$row[2].'</option>'.PHP_EOL;
            if($row[3] != "") {
                $tel = ' ('.$row[3].')';
            } else {
                $tel = "";
            }
            $tplArrayEditEntry['options'][$i]['text'] = $row[1].' '.$row[2].$tel;
        }
    }
    $test = grr_sql_query1('SELECT login FROM '.TABLE_PREFIX."_utilisateurs WHERE login='".$beneficiaire."'");
    if (($test == -1) && ($beneficiaire != '')) {
        //echo '<option value="-1" selected="selected" >'.get_vocab('utilisateur_inconnu').$beneficiaire.')</option>'.PHP_EOL;
        $tplArrayEditEntry['optionBeneficiaireInconnu'] = $beneficiaire;
        $tplArrayEditEntry['vocab']['utilisateur_inconnu'] = get_vocab('utilisateur_inconnu');

    } else {
        $tplArrayEditEntry['optionBeneficiaireInconnu'] = false;
    }
    /*
    echo '</select>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
    echo '<input type="button" class="btn btn-primary" value="'.get_vocab('definir par defaut').'" onclick="setdefault(\'beneficiaire_default\',document.getElementById(\'main\').beneficiaire.options[document.getElementById(\'main\').beneficiaire.options.selectedIndex].value)" />'.PHP_EOL;
    echo '<div id="div_profilBeneficiaire">'.PHP_EOL;
    echo '</div>'.PHP_EOL;*/
    if (isset($statut_beneficiaire)) {
        $tplArrayEditEntry['statusBeneficiaire'] = $statut_beneficiaire;
    } else {
        $tplArrayEditEntry['statusBeneficiaire'] = false;
    }
    /*if (isset($statut_beneficiaire)) {
        echo $statut_beneficiaire;
    }*/
    /*echo '</td></tr>'.PHP_EOL;*/
    $tplArrayEditEntry['tabBenef']['nom'] = strip_tags($tab_benef['nom']);
    $tplArrayEditEntry['tabBenef']['email'] = htmlspecialchars($tab_benef['email']);

    /* if ($tab_benef['nom'] != '') {
        echo '<tr id="menu4"><td>'.PHP_EOL;
    } else {
        echo '<tr style="display:none" id="menu4"><td>'.PHP_EOL;
    }*/
    /*    echo '<div class="form-group">'.PHP_EOL;
    echo '    <div class="input-group">'.PHP_EOL;
    echo '      <div class="input-group-addon"><span class="glyphicon glyphicon-user"></span></div>'.PHP_EOL;
    echo '      <input class="form-control" type="text" name="benef_ext_nom" value="'.htmlspecialchars($tab_benef['nom']).'" placeholder="'.get_vocab('nom beneficiaire').'">'.PHP_EOL;
    echo '    </div>'.PHP_EOL;
    echo '  </div>'.PHP_EOL;*/
    $affiche_mess_asterisque = true;
    $tplArrayEditEntry['settings']['automaticMail'] = Settings::get('automatic_mail');
    /* if (Settings::get('automatic_mail') == 'yes') {
        echo '<div class="form-group">'.PHP_EOL;
        echo '    <div class="input-group">'.PHP_EOL;
        echo '      <div class="input-group-addon"><span class="glyphicon glyphicon-envelope" ></span></div>'.PHP_EOL;
        echo '      <input class="form-control" type="email" name="benef_ext_email" value="'.htmlspecialchars($tab_benef['email']).'" placeholder="'.get_vocab('email beneficiaire').'">'.PHP_EOL;
        echo '    </div>'.PHP_EOL;
        echo '  </div>'.PHP_EOL;
    }
    echo "</td></tr>\n";*/
} else {
    $flag_qui_peut_reserver_pour = 'no';
    $tplArrayEditEntry['flagQuiPeutReserverPour'] = 'no';
}
/*echo '<tr><td class="E">'.PHP_EOL;
echo '<b>'.$B.'</b>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;
echo '<tr><td class="CL">'.PHP_EOL;
echo '<input id="name" class="form-control" name="name" size="80" value="'.$C.'" />'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;
echo '<tr><td class="E">'.PHP_EOL;
echo '<b>'.$D.'</b>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;
echo '<tr><td class="TL">'.PHP_EOL;
echo '<textarea name="description" class="form-control" rows="4" cols="82">'.$E.'</textarea>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;
echo '<tr><td>'.PHP_EOL;
echo '<div id="div_champs_add">'.PHP_EOL;
echo '</div>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;*/

/*echo '<tr><td class="E"><br>'.PHP_EOL;
echo '<b>'.get_vocab('status_clef').get_vocab('deux_points').'</b>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;
echo '<tr><td class="CL">'.PHP_EOL;
echo '<input name="keys" type="checkbox" value="y" ';*/

/* todo déplacer vocab avec ceux plus haut */
$tplArrayEditEntry['vocab']['msg_clef'] = get_vocab('msg_clef');
$tplArrayEditEntry['vocab']['status_clef'] = get_vocab('status_clef');
$tplArrayEditEntry['vocab']['status_courrier'] = get_vocab('status_courrier');
$tplArrayEditEntry['vocab']['msg_courrier'] = get_vocab('msg_courrier');
$tplArrayEditEntry['vocab']['period'] = get_vocab('period');
$tplArrayEditEntry['vocab']['time'] = get_vocab('time');
$tplArrayEditEntry['vocab']['unit'] = strip_tags(get_vocab('unit'));
$tplArrayEditEntry['vocab']['all_day'] = get_vocab('all_day');
$tplArrayEditEntry['vocab']['fin_reservation'] = get_vocab('fin_reservation');

if (isset($clef) && $clef == 1) {
    //echo 'checked';
    $tplArrayEditEntry['clef'] = true;
} else {
    $tplArrayEditEntry['clef'] = false;
}
/*echo ' > '.get_vocab('msg_clef');
echo '</td></tr>'.PHP_EOL;*/
/*echo '<tr><td class="E"><br>'.PHP_EOL;
echo '<b>'.get_vocab('status_courrier').get_vocab('deux_points').'</b>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;
echo '<tr><td class="CL">'.PHP_EOL;
echo '<input name="courrier" type="checkbox" value="y" ';*/
if (isset($courrier) && $courrier == 1) {
    //echo 'checked';
    $tplArrayEditEntry['courrier'] = true;
} else {
    $tplArrayEditEntry['courrier'] = false;
}

/*echo ' > '.get_vocab('msg_courrier');
echo '</td></tr>'.PHP_EOL;*/

/*echo '<tr><td class="E">'.PHP_EOL;
echo '<b>'.$F.'</b>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;
echo '<tr><td class="CL">'.PHP_EOL;*/

/*echo '<div class="form-group">'.PHP_EOL;*/

/* todo faire un helper twig pour les select renvoyés ici */
$tplArrayEditEntry['rawSelectDate'] = jQuery_DatePicker('start', true);
$tplArrayEditEntry['typeDate'] = "start";

if ($enable_periods == 'y') {
    $tplArrayEditEntry['enablePeriod'] = true;
    $tplArrayEditEntry['period'] = $period;
    $tplArrayEditEntry['periodStartMin'] = $start_min;

/*    echo '<b>'.get_vocab('period').'</b>'.PHP_EOL;

    echo '<select name="period">'.PHP_EOL;*/
    foreach ($periods_name as $p_num => $p_val) {
        $tplArrayEditEntry['periods'][$p_num] = $p_val;
/*        echo '<option value="'.$p_num.'"';
        if ((isset($period) && $period == $p_num) || $p_num == $start_min) {
            echo ' selected="selected"';
        }
        echo '>'.$p_val.'</option>'.PHP_EOL;
   */
   }
/*    echo '</select>'.PHP_EOL;*/
} else {
$tplArrayEditEntry['enablePeriod'] = false;
    /*echo '<b>'.get_vocab('time').' : </b>';*/
    if (isset($_GET['id'])) {

        //$tplArrayEditEntry['EnvGetId'] = $_GET['id'];
        //$tplArrayEditEntry['dureeParDefautReservationArea'] = $duration;
        $duree_par_defaut_reservation_area = $duration;
        $tplArrayEditEntry['timePicker'] = jQuery_TimePicker('start_', $start_hour, $start_min, $duree_par_defaut_reservation_area, true);
        $tplArrayEditEntry['timePickerEnd'] = jQuery_TimePicker('end_', $end_hour, $end_min, $duree_par_defaut_reservation_area, true);
    } else {
        //$tplArrayEditEntry['EnvGetId'] = false;

        $tplArrayEditEntry['timePicker'] = jQuery_TimePicker('start_', $hour, $minute, $duree_par_defaut_reservation_area, true);
        $tplArrayEditEntry['timePickerEnd'] = jQuery_TimePicker('end_', $hour, $minute, $duree_par_defaut_reservation_area, true);
    }
    if (!$twentyfourhour_format) {
        $tplArrayEditEntry['24hFormat'] = true;
        if ($start_hour < 12) {
            $tplArrayEditEntry['ampm'] = 'am';
        } else {
            $tplArrayEditEntry['ampm'] = 'pm';
        }
        $tplArrayEditEntry['amTime'] =  date('a', mktime(1, 0, 0, 1, 1, 1970));
        $tplArrayEditEntry['pmTime'] =  date('a', mktime(13, 0, 0, 1, 1, 1970));
        /*$checked = ($start_hour < 12) ? 'checked="checked"' : '';
        echo '<input name="ampm" type="radio" value="am" '.$checked.' />'.date('a', mktime(1, 0, 0, 1, 1, 1970));
        $checked = ($start_hour >= 12) ? 'checked="checked"' : '';
        echo '<input name="ampm" type="radio" value="pm" '.$checked.' />'.date('a', mktime(13, 0, 0, 1, 1, 1970));*/
    } else {
        $tplArrayEditEntry['24hFormat'] = false;

    }

}

/*echo '</div>'.PHP_EOL;
echo '</td></tr>'.PHP_EOL;*/
$tplArrayEditEntry['typeAffichageReser'] = $type_affichage_reser;
if ($type_affichage_reser == 0) {
    //$tplArrayEditEntry['typeAffichageReser'] = false;
    $tplArrayEditEntry['spinner']['duration'] = $duration;
    /*    echo '<tr><td class="E">'.PHP_EOL;
    echo '<b>'.get_vocab('duration').'</b>'.PHP_EOL;
    echo '</td></tr>'.PHP_EOL;
    echo '<tr><td class="CL">'.PHP_EOL;
    echo '<div class="form-group">'.PHP_EOL;*/

    //spinner($duration);
    //echo '<select class="form-control" name="dur_units" size="1">'.PHP_EOL;
    if ($enable_periods == 'y') {
        $units = array('periods', 'days');
    } else {
        $duree_max_resa_area = grr_sql_query1('SELECT duree_max_resa_area FROM '.TABLE_PREFIX."_area WHERE id='".$area."'");
        if ($duree_max_resa_area < 0) {
            $units = array('minutes', 'hours', 'days', 'weeks');
        } elseif ($duree_max_resa_area < 60) {
            $units = array('minutes');
        } elseif ($duree_max_resa_area < 60 * 24) {
            $units = array('minutes', 'hours');
        } elseif ($duree_max_resa_area < 60 * 24 * 7) {
            $units = array('minutes', 'hours', 'days');
        } else {
            $units = array('minutes', 'hours', 'days', 'weeks');
        }
    }
    $tplArrayEditEntry['units'] = $units;
    $tplArrayEditEntry['durUnits'] = $dur_units;

    /*    while (list(, $unit) = each($units)) {
        echo '<option value="'.$unit.'"';
        if ($dur_units ==  get_vocab($unit)) {
            echo ' selected="selected"';
        }
        echo '>'.get_vocab($unit).'</option>'.PHP_EOL;
    }
    echo '</select>'.PHP_EOL;*/

    $fin_jour = $eveningends;
    $minute = $resolution / 60;
    $minute_restante = $minute % 60;
    $heure_ajout = ($minute - $minute_restante) / 60;
    if ($minute_restante < 10) {
        $minute_restante = '0'.$minute_restante;
    }
    $heure_finale = round($fin_jour + $heure_ajout, 0);
    if ($heure_finale > 24) {
        $heure_finale_restante = $heure_finale % 24;
        $nb_jour = ($heure_finale - $heure_finale_restante) / 24;
        $heure_finale = $nb_jour.' '.$vocab['days'].' + '.$heure_finale_restante;
    }
    $af_fin_jour = $heure_finale.' H '.$minute_restante;

    /*echo '<input name="all_day" type="checkbox" value="yes" />'.get_vocab('all_day');*/
    /*if ($enable_periods != 'y') {
        echo ' ('.$morningstarts.' H - '.$af_fin_jour.')';
    }*/
    $tplArrayEditEntry['morningStarts'] = $morningstarts;
    $tplArrayEditEntry['afFinJour'] = $af_fin_jour;

    /*    echo '</div>'.PHP_EOL;
    echo '</td></tr>'.PHP_EOL;*/


} else {


        //$tplArrayEditEntry['typeAffichageReser'] = true;
    /*    echo '<tr><td class="E"><b>'.get_vocab('fin_reservation').get_vocab('deux_points').'</b></td></tr>'.PHP_EOL;
        echo '<tr><td class="CL" >'.PHP_EOL;

        echo '<div class="form-group">'.PHP_EOL;*/
    $tplArrayEditEntry['rawSelectDateEnd'] = jQuery_DatePicker('end', true);

    if ($enable_periods == 'y') {
        if (isset($end_period)) {
            $tplArrayEditEntry['endPeriod'] = $end_period;
        } else {
            $tplArrayEditEntry['endPeriod'] = false;
        }
        $tplArrayEditEntry['endMin'] = $end_min;

    /*        echo '<b>'.get_vocab('period').'</b>';
            echo "<td class=\"CL\">\n";
            echo '<select class="form-control" name="end_period">';*/
    /*        foreach ($periods_name as $p_num => $p_val) {
                echo '<option value="'.$p_num.'"';
                if ((isset($end_period) && $end_period == $p_num) || ($p_num + 1) == $end_min) {
                    echo ' selected="selected"';
                }
                echo ">$p_val</option>\n";
            }
            echo '</select>'.PHP_EOL;*/

    } else {

    /*        echo '<b>'.get_vocab('time').' : </b>';
            if (isset($_GET['id'])) {
                jQuery_TimePicker('end_', $end_hour, $end_min, $duree_par_defaut_reservation_area);
            } else {
                jQuery_TimePicker('end_', '', '', $duree_par_defaut_reservation_area);
            }
            if (!$twentyfourhour_format) {
                $checked = ($end_hour < 12) ? 'checked="checked"' : '';
                echo "<input name=\"ampm\" type=\"radio\" value=\"am\" $checked />".date('a', mktime(1, 0, 0, 1, 1, 1970));
                $checked = ($end_hour >= 12) ? 'checked="checked"' : '';
                echo "<input name=\"ampm\" type=\"radio\" value=\"pm\" $checked />".date('a', mktime(13, 0, 0, 1, 1, 1970));
            }*/
    }
    /*    echo '</div>'.PHP_EOL;
        echo '</td></tr>'.PHP_EOL;*/
}

if (($delais_option_reservation > 0) && (($modif_option_reservation == 'y') || ((($modif_option_reservation == 'n') && ($option_reservation != -1))))) {
    $tplArrayEditEntry['delaisEtModifOk'] = true;
    $tplArrayEditEntry['opitonReservation'] = $option_reservation;
    $day = date('d');
    $month = date('m');
    $year = date('Y');

    $tplArrayEditEntry['vocab']['reservation_a_confirmer_au_plus_tard_le'] = get_vocab('reservation_a_confirmer_au_plus_tard_le');
    $tplArrayEditEntry['vocab']['confirmer'] = get_vocab('confirmer');
    $tplArrayEditEntry['vocab']['confirmer'] = get_vocab('confirmer');
    $tplArrayEditEntry['vocab']['confirmer_reservation'] = get_vocab('confirmer reservation');
    $tplArrayEditEntry['vocab']['Reservation_confirmee'] = get_vocab('Reservation confirmee');


    /*echo '<tr><td class="E"><br><div class="col-xs-12"><div class="alert alert-danger" role="alert"><b>'.get_vocab('reservation_a_confirmer_au_plus_tard_le').'</div>'.PHP_EOL;*/

/* todo refacto, supprimer code inutilisé grâce a twig, ex: $aff_options */
    if ($modif_option_reservation == 'y') {
        $tplArrayEditEntry['modifiable'] = true;
        /*echo '<select class="form-control" name="option_reservation" size="1">'.PHP_EOL;*/
        $k = 0;
        $selected = 'n';
        $tplArrayEditEntry['selected'] = false;
        $aff_options = '';
        while ($k < $delais_option_reservation + 1) {
            $day_courant = $day + $k;
            $date_courante = mktime(0, 0, 0, $month, $day_courant, $year);
            $tplArrayEditEntry['optionResa'][$k]['dateCourante'] = $date_courante;
            $aff_date_courante = time_date_string_jma($date_courante, $dformat);
            /* j'essaye sans utiliser time_date_string_jma pour voir si on peut s'en passer */
            $tplArrayEditEntry['optionResa'][$k]['affDateCourante'] = strftime($dformat, $date_courante);

            $aff_options .= '<option value = "'.$date_courante.'" ';
            if ($option_reservation == $date_courante) {
                $aff_options .= ' selected="selected" ';
                $selected = 'y';
                $tplArrayEditEntry['selected'] = true;
            }
            $aff_options .= '>'.$aff_date_courante."</option>\n";
            ++$k;
        }
        /*echo '<option value = "-1">'.get_vocab('Reservation confirmee')."</option>\n";
        */if (($selected == 'n') and ($option_reservation != -1)) {
            //echo '<option value = "'.$option_reservation.'" selected="selected">'.time_date_string_jma($option_reservation, $dformat)."</option>\n";
            $tplArrayEditEntry['selectedNo'] = strftime($dformat, $option_reservation);
        }/*
        echo $aff_options;
        echo '</select>';*/


    } else {
        $tplArrayEditEntry['selectedNo'] = strftime($dformat, $option_reservation);
    /*  echo '<input type="hidden" name="option_reservation" value="'.$option_reservation.'" /> <b>'.
        time_date_string_jma($option_reservation, $dformat)."</b>\n";
        echo '<br /><input type="checkbox" name="confirm_reservation" value="y" />'.get_vocab('confirmer reservation')."\n";*/
    }
    /*echo '<br /><div class="alert alert-danger" role="alert">'.get_vocab('avertissement_reservation_a_confirmer').'</b></div>'.PHP_EOL;
    echo "</div></td></tr>\n";*/
} else {
    $tplArrayEditEntry['delaisEtModifOk'] = false;
}

$tplArrayEditEntry['nbAreas'] = $nb_areas;

/* todo rassembler vocab*/
$tplArrayEditEntry['vocab']['match_area'] = get_vocab('match_area');
$tplArrayEditEntry['vocab']['rooms'] = get_vocab('rooms');
$tplArrayEditEntry['vocab']['ctrl_click'] = get_vocab('ctrl_click');
/*echo '<tr ';
if ($nb_areas == 1) {
    echo 'style="display:none" ';
}
echo '><td class="E"><b>'.get_vocab('match_area').get_vocab('deux_points')."</b></td></tr>\n";
echo '<tr ';
if ($nb_areas == 1) {
    echo 'style="display:none" ';
}
echo "><td class=\"CL\" style=\"vertical-align:top;\" >\n";
echo '<div class="col-xs-3"><select class="form-control" id="areas" name="areas" onchange="changeRooms(this.form);insertChampsAdd();insertTypes()" >';*/
if ($enable_periods == 'y') {
    $sql = 'SELECT id, area_name FROM '.TABLE_PREFIX."_area WHERE id='".$area."' ORDER BY area_name";
} else {
    $sql = 'SELECT id, area_name FROM '.TABLE_PREFIX."_area WHERE enable_periods != 'y' ORDER BY area_name";
}
$res = grr_sql_query($sql);
if ($res) {
    $incrementForValidArea = 0;
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        if (authUserAccesArea(getUserName(), $row[0]) == 1) {
            $selected = '';
            $tplArrayEditEntry['areasAuth'][$incrementForValidArea]['0'] = $row[0];
            $tplArrayEditEntry['areasAuth'][$incrementForValidArea]['1'] = $row[1];
            if ($row[0] == $area) {
                //$selected = 'selected="selected"';
                $tplArrayEditEntry['areasAuth'][$incrementForValidArea]['selected'] = true;
            } else {
                $tplArrayEditEntry['areasAuth'][$incrementForValidArea]['selected'] = false;
            }
            //print '<option '.$selected.' value="'.$row[0].'">'.$row[1].'</option>'.PHP_EOL;
            $incrementForValidArea++;
        }
    }
}
//echo '</select>',PHP_EOL,'</div>',PHP_EOL,'</td>',PHP_EOL,'</tr>',PHP_EOL;

/*echo '<!-- ************* Ressources edition ***************** -->',PHP_EOL;
echo '<tr><td class="E"><b>'.get_vocab('rooms').get_vocab('deux_points')."</b></td></tr>\n";*/
$sql = 'SELECT id, room_name, description, capacity FROM '.TABLE_PREFIX."_room WHERE area_id=$area_id ";
$tab_rooms_noaccess = verif_acces_ressource(getUserName(), 'all');
foreach ($tab_rooms_noaccess as $key) {
    $sql .= " and id != $key ";
}
$sql .= ' ORDER BY order_display,room_name';
$res = grr_sql_query($sql);
$len = grr_sql_count($res);

$tplArrayEditEntry['longeurListeRessourcesMax'] = min($longueur_liste_ressources_max, $len);
/*echo '<tr><td class="CL" style="vertical-align:top;"><table border="0"><tr><td><select name="rooms[]" size="'.min($longueur_liste_ressources_max, $len).'" multiple="multiple">';*/
//Sélection de la "room" dans l'"area"
if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); ++$i) {
        /*var_dump($row);echo "<br>";*/
        $tplArrayEditEntry['rooms'][$i]['0'] = $row[0];
        $tplArrayEditEntry['rooms'][$i]['1'] = $row[1];
        $tplArrayEditEntry['rooms'][$i]['capacity'] = $row[3];
        $tplArrayEditEntry['rooms'][$i]['desc'] = $row[2];
        $selected = '';
        if ($row[0] == $room_id) {
            //$selected = 'selected="selected"';
            $tplArrayEditEntry['rooms'][$i]['selected'] = true;
        } else {
            $tplArrayEditEntry['rooms'][$i]['selected'] = false;
        }
        /*echo '<option ',$selected,' value="',$row[0],'">',$row[1],'</option>',PHP_EOL;*/
    }
}
/*echo '</select>',PHP_EOL,'</div>',PHP_EOL,'</td>',PHP_EOL,'<td>',get_vocab('ctrl_click'),'</td>',PHP_EOL,'</tr>',PHP_EOL,'</table>',PHP_EOL;
echo '</td>',PHP_EOL,'</tr>',PHP_EOL;
echo '<tr>',PHP_EOL,'<td>',PHP_EOL,'<div id="div_types">',PHP_EOL;
echo '</div>',PHP_EOL,'</td>',PHP_EOL,'</tr>',PHP_EOL;
echo '<tr>',PHP_EOL,'<td class="E">',PHP_EOL;*/

/*<script type="text/javascript" >
	insertChampsAdd();
	insertTypes();
	insertProfilBeneficiaire();
</script>*/

if ($affiche_mess_asterisque) {
    $tplArrayEditEntry['afficheMessAsterisque'] = get_vocab('required');
    //get_vocab('required');
} else {
    $tplArrayEditEntry['afficheMessAsterisque'] = false;
}
/*echo '</td></tr>',PHP_EOL;
echo '</table>',PHP_EOL;
echo '</td>',PHP_EOL;
echo '<td style="vertical-align:top;">',PHP_EOL;
echo '<table class="table-header">',PHP_EOL;*/

$sql = 'SELECT id FROM '.TABLE_PREFIX.'_area;';
$res = grr_sql_query($sql);
/*echo '<!-- ************* Periodic edition ***************** -->',PHP_EOL;*/
$weeklist = array('unused','every week','week 1/2','week 1/3','week 1/4','week 1/5');
$monthlist = array('firstofmonth','secondofmonth','thirdofmonth','fouthofmonth','fiveofmonth','lastofmonth');

$tplArrayEditEntry['editType'] = $edit_type;
if (($edit_type == 'series') || (isset($flag_periodicite))) {

    /* todo rassembler les vocab */
    $tplArrayEditEntry['vocab']['click_here_for_series_open'] = get_vocab('click_here_for_series_open');
    $tplArrayEditEntry['vocab']['click_here_for_series_close'] = get_vocab('click_here_for_series_close');
    $tplArrayEditEntry['vocab']['rep_type'] = get_vocab('rep_type');
    $tplArrayEditEntry['vocab']['every_week'] = get_vocab('every week');



    $tplArrayEditEntry['showPeriodicite'] = true;

    /*echo '<tr>',PHP_EOL,'<td id="ouvrir" style="cursor: inherit" align="center" class="fontcolor4">',PHP_EOL,
    '<span class="fontcolor1 btn btn-primary"><b><a href="javascript:clicMenu(1);check_5()">',get_vocab('click_here_for_series_open'),'</a></b></span>',PHP_EOL,
    '</td>',PHP_EOL,'</tr>',PHP_EOL,'<tr>',PHP_EOL,'<td style="display:none; cursor: inherit white;" id="fermer" align="center" class="fontcolor4">',PHP_EOL,
    '<span class="btn btn-primary fontcolor1 white"><b><a href="javascript:clicMenu(1);check_5()">',get_vocab('click_here_for_series_close'),'</a></b></span>',PHP_EOL,
    '</td>',PHP_EOL,'</tr>',PHP_EOL;
    echo '<tr>',PHP_EOL,'<td>',PHP_EOL,'<table id="menu1" style="display:none;">',PHP_EOL,'<tr>',PHP_EOL,
    '<td class="F"><b>',get_vocab('rep_type'),'</b></td>',PHP_EOL,'</tr>',PHP_EOL,'<tr>',PHP_EOL,'<td class="CL">',PHP_EOL;
    echo '<table class="table" >',PHP_EOL;*/
    if (Settings::get('jours_cycles_actif') == 'Oui') {
        $max = 8;
    } else {
        $max = 7;
    }
    for ($i = 0; $i < $max; $i++) {
        if ($i == 6 && Settings::get('jours_cycles_actif') == 'Non') {
            $i++;
        }
        if ($i != 5) {
            //echo '<tr>',PHP_EOL,'<td>',PHP_EOL,'<input name="rep_type" type="radio" value="',$i,'"';
            if ($i == $rep_type) {
                //echo $i;
                $tplArrayEditEntry['repetitions'][$i]['checked'] = true;
                //echo ' checked="checked"';
            } else {
                $tplArrayEditEntry['repetitions'][$i]['checked'] = false;
            }
            if (($i == 3) && ($rep_type == 5) && $tplArrayEditEntry['repetitions'][$i]['checked'] == false) {
                //echo ' checked="checked"';
                $tplArrayEditEntry['repetitions'][$i]['checked'] = true;
            } /*else {
                $tplArrayEditEntry['repetitions'][$i]['checked'] = false;
            }*/
            //echo ' onclick="check_1()" />',PHP_EOL,'</td>',PHP_EOL,'<td>',PHP_EOL;

            if (($i != 2) && ($i != 3)) {
                $tplArrayEditEntry['repetitions'][$i]['vocab'] = get_vocab("rep_type_$i");
                //echo get_vocab("rep_type_$i");
            } else {
                $tplArrayEditEntry['repetitions'][$i]['vocab'] = false;
            }

            //echo PHP_EOL;
            if ($i == '2') {
                $tplArrayEditEntry['repetitions'][$i]['type'] = 2;

                /*echo '<select class="form-control" name="rep_num_weeks" size="1" onfocus="check_2()" onclick="check_2()">',PHP_EOL;
                echo '<option value="1" >',get_vocab('every week'),'</option>',PHP_EOL;*/
                for ($weekit = 2; $weekit < 6; ++$weekit) {
                    $tplArrayEditEntry['repetitions'][$i]['weeks'][$weekit]['selected'] = true;
                    //echo '<option value="',$weekit,'"';
                    if ($rep_num_weeks == $weekit) {
                        $tplArrayEditEntry['repetitions'][$i]['weeks'][$weekit]['selected'] = true;
                        //echo ' selected="selected"';
                    } else {
                        $tplArrayEditEntry['repetitions'][$i]['weeks'][$weekit]['selected'] = false;
                    }
                    //echo '>',get_vocab($weeklist[$weekit]),'</option>',PHP_EOL;
                    $tplArrayEditEntry['repetitions'][$i]['weeks'][$weekit]['vocab'] = get_vocab($weeklist[$weekit]);

                }
                //echo '</select>',PHP_EOL;

            } elseif ($i == '3') {

                $tplArrayEditEntry['repetitions'][$i]['type'] = 3;

                $monthrep3 = '';
                $monthrep5 = '';
                if ($rep_type == 3) {
                    $monthrep3 = ' selected="selected" ';
                    $tplArrayEditEntry['repetitions'][$i]['selected'] = 3;
                }
                if ($rep_type == 5) {
                    $monthrep5 = ' selected="selected" ';
                    $tplArrayEditEntry['repetitions'][$i]['selected'] = 5;
                }
                $tplArrayEditEntry['vocab']['rep_type_3'] = get_vocab('rep_type_3');
                $tplArrayEditEntry['vocab']['rep_type_5'] = get_vocab('rep_type_5');

                /*echo '<select class="form-control" name="rep_month" size="1" onfocus="check_3()" onclick="check_3()">'.PHP_EOL;
                echo "<option value=\"3\" $monthrep3>".get_vocab('rep_type_3')."</option>\n";
                echo "<option value=\"5\" $monthrep5>".get_vocab('rep_type_5')."</option>\n";
                echo "</select>\n";*/

            } elseif ($i == '7') {

                $tplArrayEditEntry['repetitions'][$i]['type'] = 7;

                /*echo '<select class="form-control" name="rep_month_abs1" size="1" onfocus="check_7()" onclick="check_7()">'.PHP_EOL;*/
                for ($weekit = 0; $weekit < 6; $weekit++) {
                    $tplArrayEditEntry['repetitions'][$i]['weeks7'][$weekit]['vocab'] = get_vocab($monthlist[$weekit]);
                    /*echo '<option value="'.$weekit.'"';
                    echo '>'.get_vocab($monthlist[$weekit])."</option>\n";*/
                }
                /*echo '</select>'.PHP_EOL;*/
                /*echo '<select class="form-control" name="rep_month_abs2" size="1" onfocus="check_8()" onclick="check_8()">'.PHP_EOL;*/
                for ($weekit = 1; $weekit < 8; ++$weekit) {
                    $tplArrayEditEntry['repetitions'][$i]['weeksDayName'][$weekit]['vocab'] = day_name($weekit);
                    /*echo '<option value="'.$weekit.'"';
                    echo '>'.day_name($weekit)."</option>\n";*/
                }
                /*echo "</select>\n";
                echo get_vocab('ofmonth');*/

            }

            //echo "</td></tr>\n";
        }
    }
/* todo rassembler les vocab */
$tplArrayEditEntry['vocab']['ofmonth'] = get_vocab('ofmonth');
$tplArrayEditEntry['vocab']['rep_end_date'] = get_vocab('rep_end_date');
$tplArrayEditEntry['vocab']['rep_rep_day'] = get_vocab('rep_rep_day');

/*    echo "</table>\n\n";
    echo "<!-- ***** Fin de périodidité ***** -->\n";*/
    /*echo '</td></tr>';
    echo '<tr><td class="F"><b>'.get_vocab('rep_end_date')."</b></td></tr>\n";
    echo '<tr><td class="CL">';*/
    $tplArrayEditEntry['rawDatePickerRepEnd'] = jQuery_DatePicker('rep_end', true);
    /*echo "</td></tr></table>\n";
    echo "<table style=\"display:none\" id=\"menu2\" width=\"100%\">\n";
    echo '<tr><td class="F"><b>'.get_vocab('rep_rep_day')."</b></td></tr>\n";
    echo '<tr><td class="CL">';*/
    for ($i = 0; $i < 7; $i++) {
        $wday = ($i + $weekstarts) % 7;
        $tplArrayEditEntry['repDays'][$i]['wday'] = $wday;
        //echo "<input name=\"rep_day[$wday]\" type=\"checkbox\"";
        if ($rep_day[$wday]) {
            //echo ' checked="checked"';
            $tplArrayEditEntry['repDays'][$i]['checked'] = true;
        } else {
            $tplArrayEditEntry['repDays'][$i]['checked'] = false;
        }
        $tplArrayEditEntry['repDays'][$i]['vocab'] = day_name($wday);
        //echo ' onclick="check_1()" />'.day_name($wday)."\n";
    }
    /*echo "</td></tr>\n</table>\n";
    echo "<table style=\"display:none\" id=\"menuP\" width=\"100%\">\n";
    echo "<tr><td class=\"F\"><b>Jours/Cycle</b></td></tr>\n";
    echo '<tr><td class="CL">';*/
    for ($i = 1; $i < (Settings::get('nombre_jours_Jours/Cycles') + 1); ++$i) {
        $wday = $i;

        //echo "<input type=\"radio\" name=\"rep_jour_\" value=\"$wday\"";
        if (isset($jours_c)) {
            if ($i == $jours_c) {
                $tplArrayEditEntry['repRadioJours'][$wday]['checked'] = true;
                //echo ' checked="checked"';
            }
        }
        $tplArrayEditEntry['vocab']['rep_type_6'] = get_vocab('rep_type_6');
        //echo ' onclick="check_1()" />',get_vocab('rep_type_6'),' ',$wday,PHP_EOL;
    }
    /*echo '</td>',PHP_EOL,'</tr>',PHP_EOL,'</table>',PHP_EOL,'</td>',PHP_EOL,'</tr>',PHP_EOL;*/


} else {
    $tplArrayEditEntry['showPeriodicite'] = false;

    $tplArrayEditEntry['vocab']['periodicite_associe'] = get_vocab('periodicite_associe');
    $tplArrayEditEntry['vocab']['rep_type'] = get_vocab('rep_type');
    $tplArrayEditEntry['vocab']['rep_rep_day'] = get_vocab('rep_rep_day');
    $tplArrayEditEntry['vocab']['rep_rep_days'] = get_vocab('rep_rep_days');
    $tplArrayEditEntry['vocab']['date'] = get_vocab('date');
    $tplArrayEditEntry['vocab']['duration'] = get_vocab('duration');
    $tplArrayEditEntry['vocab']['rep_end_date'] = get_vocab('rep_end_date');

    /*echo '<tr><td class="E"><b>'.get_vocab('periodicite_associe').get_vocab('deux_points')."</b></td></tr>\n";*/
    if ($rep_type == 2) {
        $tplArrayEditEntry['pasPeriodique']['vocab'] = get_vocab($weeklist[$rep_num_weeks]);
        $affiche_period = get_vocab($weeklist[$rep_num_weeks]);
    } else {
        $tplArrayEditEntry['pasPeriodique']['vocab'] = get_vocab('rep_type_'.$rep_type);
        $affiche_period = get_vocab('rep_type_'.$rep_type);
    }
    //echo '<tr><td class="E"><b>'.get_vocab('rep_type').'</b> '.$affiche_period.'</td></tr>'."\n";
    if ($rep_type != 0) {
        //$tplArrayEditEntry['pasPeriodique']['repTypeNot0'] = true;
        $opt = '';
        if ($rep_type == 2) {
            $nb = 0;
            for ($i = 0; $i < 7; ++$i) {
                $wday = ($i + $weekstarts) % 7;
                if ($rep_opt[$wday]) {
                    if ($opt != '') {
                        $opt .= ', ';
                    }
                    $opt .= day_name($wday);
                    ++$nb;
                }
            }
        }
        if ($rep_type == 6) {
            $nb = 1;
            $opt .= get_vocab('jour_cycle').' '.$jours_c;
        }
        if ($opt) {
            $tplArrayEditEntry['pasPeriode']['opt'] = $opt;
            $tplArrayEditEntry['pasPeriode']['nb'] = $nb;

            /*if ($nb == 1) {

                echo '<tr><td class="E"><b>'.get_vocab('rep_rep_day').'</b> '.$opt.'</td></tr>'."\n";
            } else {
                echo '<tr><td class="E"><b>'.get_vocab('rep_rep_days').'</b> '.$opt.'</td></tr>'."\n";
            }*/
        } else {
            $tplArrayEditEntry['pasPeriode']['opt'] = false;
        }
        if ($enable_periods == 'y') {
            list($start_period, $start_date) = period_date_string($start_time);
        } else {
            $start_date = time_date_string($start_time, $dformat);
        }
        $duration = $end_time - $start_time;
        if ($enable_periods == 'y') {
            toPeriodString($start_period, $duration, $dur_units);
        } else {
            toTimeString($duration, $dur_units, true);
        }
        $tplArrayEditEntry['pasPeriode']['startDate'] = $start_date;
        $tplArrayEditEntry['pasPeriode']['duration'] = $duration;
        $tplArrayEditEntry['pasPeriode']['durUnits'] = $dur_units;
        $tplArrayEditEntry['pasPeriode']['repEndDate'] = $rep_end_date;

        /*echo '<tr><td class="E"><b>'.get_vocab('date').get_vocab('deux_points').'</b> '.$start_date.'</td></tr>'."\n";
        echo '<tr><td class="E"><b>'.get_vocab('duration').'</b> '.$duration.' '.$dur_units.'</td></tr>'."\n";
        echo '<tr><td class="E"><b>'.get_vocab('rep_end_date').'</b> '.$rep_end_date.'</td></tr>'."\n";*/
    }
}
/*    echo '</table>',PHP_EOL;
    echo '</td>',PHP_EOL,'</tr>',PHP_EOL,'</table>',PHP_EOL;*/

        $tplArrayEditEntry['vocab']['cancel'] = get_vocab('cancel');
        $tplArrayEditEntry['vocab']['save'] = get_vocab('save');
        $tplArrayEditEntry['vocab']['cancel'] = get_vocab('cancel');


        $tplArrayEditEntry['cancelPageLink'] = $page.'.php?year='.$year.'&month='.$month.'&day='.$day.'&area='.$area.'&room='.$room;
        $tplArrayEditEntry['repId'] = $rep_id;
        $tplArrayEditEntry['page'] = $page;
        $tplArrayEditEntry['roomId'] = $room_id;



	/*<div id="fixe">
		<input type="button" class="btn btn-primary" value="<?php echo get_vocab('cancel')?>" onclick="window.location.href='<?php echo $page.'.php?year='.$year.'&amp;month='.$month.'&amp;day='.$day.'&amp;area='.$area.'&amp;room='.$room; ?>'" />
		<input type="button" class="btn btn-primary" value="<?php echo get_vocab('save')?>" onclick="Save_entry();validate_and_submit();" />
		<input type="hidden" name="rep_id"    value="<?php echo $rep_id?>" />
		<input type="hidden" name="edit_type" value="<?php echo $edit_type?>" />
		<input type="hidden" name="page" value="<?php echo $page?>" />
		<input type="hidden" name="room_back" value="<?php echo $room_id?>" />*/

        if ($flag_qui_peut_reserver_pour == 'no') {
            $tplArrayEditEntry['inputHiddenBeneficiaire'] = true;
            //echo '<input type="hidden" name="beneficiaire" value="'.$beneficiaire.'" />'.PHP_EOL;
        }
        if (!isset($statut_entry)) {
            $statut_entry = '-';
        }

        $tplArrayEditEntry['statusEntry'] = $statut_entry;
        $tplArrayEditEntry['createBy'] = $create_by;

        /*echo '<input type="hidden" name="statut_entry" value="'.$statut_entry.'" />'.PHP_EOL;
        echo '<input type="hidden" name="create_by" value="'.$create_by.'" />'.PHP_EOL;*/
        if ($id != 0) {
            if (isset($_GET['copier'])) {
                $id = null;
                $tplArrayEditEntry['idForHidden'] = false;
            } else {
                $tplArrayEditEntry['idForHidden'] = $id;
                //echo '<input type="hidden" name="id" value="'.$id.'" />'.PHP_EOL;
            }
        } else {
            $tplArrayEditEntry['idForHidden'] = false;
        }
        /* todo vérif si nécessaire */
        $tplArrayEditEntry['typeAffichageReser'] = $type_affichage_reser;
            //echo '<input type="hidden" name="type_affichage_reser" value="'.$type_affichage_reser.'" />'.PHP_EOL;
        /*
		</div>
	</form>
	<script type="text/javascript">
		insertProfilBeneficiaire();
		insertChampsAdd();
		insertTypes()
	</script>
	<script type="text/javascript">
		$('#areas').on('change', function(){
			$('.multiselect').multiselect('destroy');
			$('.multiselect').multiselect();
		});
		$(document).ready(function() {
			$('.multiselect').multiselect();
			$("#select2").select2();
		});
	</script>
	<script type="text/javascript" >
		document.getElementById('main').name.focus();
		*/
        if (isset($cookie) && $cookie) {
            $tplArrayEditEntry['cookie'] = true;
            //echo 'check_4();';
        } else {
            $tplArrayEditEntry['cookie'] = false;
        }
        if (($id != '') && (!isset($flag_periodicite))) {
            $tplArrayEditEntry['idNotVideEtPeriodicite'] = true;
            //echo "clicMenu('1'); check_5();\n";
        } else {
            $tplArrayEditEntry['idNotVideEtPeriodicite'] = false;
        }
        if (isset($Err) && $Err == 'yes') {
            $tplArrayEditEntry['err'] = true;
            //echo "timeoutID = window.setTimeout(\"Load_entry();check_5();\",500);\n";
        } else {
            $tplArrayEditEntry['err'] = false;
        }

    /*	</script>*/
    /**
     * Ici l'espace dédié aux plugins de edit_entry
     */
    /**
     * je dispatch l'event
     */
    /* si ça reste à false, c'est qu'il n'y a pas de plugin */
    $tplArrayEditEntry['plugins'] = false;
    // crée le EntryFormEvent et le répartit
    /*echo "<pre>";
    var_dump($id_site, $area_id, $id);
    echo "</pre>";*/
    $event = new EditEntryForm($id_site, $area_id, $tplArrayEditEntry, $id);
    $dispatcher->dispatch(EditEntryEvent::EDITENTRY_FORM_INSIDE_PLUGIN_AREA, $event);
    /* mise à jour du tableau du template avec le retour des events */
    $tplArrayEditEntry = $event->getTpl();
    //var_dump($tplArrayEditEntry['plugins']);
	echo $twig->render('editEntry.html.twig', $tplArrayEditEntry);
	   // include 'include/trailer.inc.php';
    //include 'footer.php';


