<?php

/**
 * login.php
 * interface de connexion
 * Ce script fait partie de l'application GRR
 * Dernière modification : $Date: 2009-12-16 14:52:31 $.
 *
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @author    Marc-Henri PAMISEUX <marcori@users.sourceforge.net>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @copyright Copyright 2008 Marc-Henri PAMISEUX
 *
 * @link      http://www.gnu.org/licenses/licenses.html
 *
 * @version   $Id: login.php,v 1.10 2009-12-16 14:52:31 grr Exp $
 * @filesource
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
include 'include/connect.inc.php';
include 'include/config.inc.php';
include 'include/misc.inc.php';
include 'include/functions.inc.php';
include "include/$dbsys.inc.php";
include 'include/init.php';
// Settings
require_once './include/settings.class.php';
//Chargement des valeurs de la table settingS
if (!Settings::load()) {
    die('Erreur chargement settings');
}
// Paramètres langage
include 'include/language.inc.php';
// Session related functions
require_once './include/session.inc.php';
// Vérification du numéro de version et renvoi automatique vers la page de mise à jour
if (verif_version()) {
    header('Location: ./admin/admin_maj.php');
    exit();
}
// User wants to be authentified
if (isset($_POST['login']) && isset($_POST['password'])) {
    // Détruit toutes les variables de session au cas où une session existait auparavant
    $_SESSION = array();
    $result = grr_opensession($_POST['login'], unslashes($_POST['password']));
    // On écrit les données de session et ferme la session
    session_write_close();
    if ($result == '2') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= ' '.get_vocab('wrong_pwd');
    } elseif ($result == '3') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= '<br />'.get_vocab('importation_impossible');
    } elseif ($result == '4') {
        //$message = get_vocab("importation_impossible");
        $message = get_vocab('echec_connexion_GRR');
        $message .= ' '.get_vocab('causes_possibles');
        $message .= '<br />- '.get_vocab('wrong_pwd');
        $message .= '<br />- '.get_vocab('echec_authentification_ldap');
    } elseif ($result == '5') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= '<br />'.get_vocab('connexion_a_grr_non_autorisee');
    } elseif ($result == '6') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= '<br />'.get_vocab('connexion_a_grr_non_autorisee');
        $message .= '<br />'.get_vocab('format identifiant incorrect');
    } elseif ($result == '7') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= '<br />'.get_vocab('connexion_a_grr_non_autorisee');
        $message .= '<br />'.get_vocab('echec_authentification_ldap');
        $message .= '<br />'.get_vocab('ldap_chemin_invalide');
    } elseif ($result == '8') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= '<br />'.get_vocab('connexion_a_grr_non_autorisee');
        $message .= '<br />'.get_vocab('echec_authentification_ldap');
        $message .= '<br />'.get_vocab('ldap_recherche_identifiant_aucun_resultats');
    } elseif ($result == '9') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= '<br />'.get_vocab('connexion_a_grr_non_autorisee');
        $message .= '<br />'.get_vocab('echec_authentification_ldap');
        $message .= '<br />'.get_vocab('ldap_doublon_identifiant');
    } elseif ($result == '10') {
        $message = get_vocab('echec_connexion_GRR');
        $message .= '<br />'.get_vocab('connexion_a_grr_non_autorisee');
        $message .= '<br />'.get_vocab('echec_authentification_imap');
    } else {
        if (isset($_POST['url'])) {
            $url = rawurldecode($_POST['url']);
            header('Location: '.$url);
            die();
        } else {
            header('Location: ./'.htmlspecialchars_decode(page_accueil()).'');
            die();
        }
    }
}
$tplArray = [];
// Dans le cas d'une démo, on met à jour la base une fois par jour.
MajMysqlModeDemo();
//si on a interdit l'acces a la page login
if ((Settings::get('Url_cacher_page_login') != '') && ((!isset($sso_super_admin)) || ($sso_super_admin == false)) && (!isset($_GET['local']))) {
    header('Location: ./index.php');
}
echo begin_page(get_vocab('mrbs').get_vocab('deux_points').Settings::get('company'), 'no_session');

/*<!--<script type="text/javascript" src="js/functions.js" ></script>
<div class="center">-->*/

    $nom_picture = './images/'.Settings::get('logo');
    if ((Settings::get('logo') != '') && (@file_exists($nom_picture))) {
        $tplArray['pathLogo'] = $nom_picture;
        /*echo "<a href=\"javascript:history.back()\"><img src=\"".$nom_picture."\" alt=\"logo\" /></a>\n";"";*/
    } else {
        $tplArray['pathLogo'] = false;
    }
    $tplArray['settings']['title'] = Settings::get('title_home_page');
    $tplArray['settings']['company'] = Settings::get('company');
    $tplArray['settings']['message'] = Settings::get('message_home_page');
    $tplArray['settings']['disableLogin'] = Settings::get('disable_login');

    $tplArray['vocab']['msg_login3'] = get_vocab('msg_login3');
    /*<h1>
        <?php
        echo Settings::get("title_home_page");
            </h1>
    <h2>

        echo Settings::get("company");

    </h2>
    <br />
    <p>
    */
        //echo Settings::get("message_home_page");
        /*if ((Settings::get("disable_login")) == 'yes') {
            echo "<br /><br /><span class='avertissement'>" . get_vocab("msg_login3") . "</span>";
        }*/

    /*</p>*/
    /*<form action="login.php" method='post' style="width: 100%; margin-top: 24px; margin-bottom: 48px;">*/
        /*<?php*/
$tplArray['vocab']['authentification_locale'] = get_vocab('authentification_locale');

        if ((isset($message)) && (Settings::get('disable_login')) != 'yes') {
            $tplArray['messageWarning'] = $message;
            //echo("<p><span class='avertissement'>" . $message . "</span></p>");
        } else {
            $tplArray['messageWarning'] = false;
        }
        if ((Settings::get('sso_statut') == 'cas_visiteur') || (Settings::get('sso_statut') == 'cas_utilisateur')) {
            $tplArray['authLocale'] = true;
            $tplArray['vocab']['authentification_CAS'] = get_vocab('authentification_CAS');

            /*echo "<p><span style=\"font-size:1.4em\"><a href=\"./index.php\">".get_vocab("authentification_CAS")."</a></span></p>";
            echo "<p><b>".get_vocab("authentification_locale")."</b></p>";*/
        } else {
            $tplArray['authLocale'] = false;
        }
        if ((Settings::get('sso_statut') == 'lemon_visiteur') || (Settings::get('sso_statut') == 'lemon_utilisateur')) {
            $tplArray['lemon'] = true;
            $tplArray['vocab']['authentification_lemon'] = get_vocab('authentification_lemon');

            /*echo "<p><span style=\"font-size:1.4em\"><a href=\"./index.php\">".get_vocab("authentification_lemon")."</a></span></p>";
            echo "<p><b>".get_vocab("authentification_locale")."</b></p>";*/
        } else {
            $tplArray['lemon'] = false;
        }
        if (Settings::get('sso_statut') == 'lcs') {
            $tplArray['lcs'] = true;
            $tplArray['lcsLink'] = LCS_PAGE_AUTHENTIF;
            $tplArray['vocab']['authentification_lcs'] = get_vocab('authentification_lcs');
            /*echo "<p><span style=\"font-size:1.4em\"><a href=\"".LCS_PAGE_AUTHENTIF."\">".get_vocab("authentification_lcs")."</a></span></p>";
            echo "<p><b>".get_vocab("authentification_locale")."</b></p>";*/
        } else {
            $tplArray['lcs'] = false;
        }
        if ((Settings::get('sso_statut') == 'lasso_visiteur') || (Settings::get('sso_statut') == 'lasso_utilisateur')) {
            $tplArray['lasso'] = true;
            $tplArray['vocab']['authentification_lasso'] = get_vocab('authentification_lasso');
            /*echo "<p><span style=\"font-size:1.4em\"><a href=\"./index.php\">".get_vocab("authentification_lasso")."</a></span></p>";
            echo "<p><b>".get_vocab("authentification_locale")."</b></p>";*/
        } else {
            $tplArray['lasso'] = false;
        }
        if ((Settings::get('sso_statut') == 'http_visiteur') || (Settings::get('sso_statut') == 'http_utilisateur')) {
            $tplArray['http'] = true;
            $tplArray['vocab']['authentification_http'] = get_vocab('authentification_http');
            /*echo "<p><span style=\"font-size:1.4em\"><a href=\"./index.php\">".get_vocab("authentification_http")."</a></span></p>";
            echo "<p><b>".get_vocab("authentification_locale")."</b></p>";*/
        } else {
            $tplArray['http'] = false;
        }

        $tplArray['vocab']['identification'] = get_vocab('identification');
        $tplArray['vocab']['login'] = get_vocab('login');
        $tplArray['vocab']['pwd'] = get_vocab('pwd');
        $tplArray['vocab']['OK'] = get_vocab('OK');
        $tplArray['vocab']['mrbs'] = strip_tags(get_vocab('mrbs'));
        $tplArray['vocab']['grr_version'] = strip_tags(get_vocab('grr_version'));
        $tplArray['vocab']['msg_login1'] = strip_tags(get_vocab('msg_login1'));

        /*<fieldset style="padding-top: 8px; padding-bottom: 8px; width: 40%; margin-left: auto; margin-right: auto;">
            <legend class="fontcolor3" style="font-variant: small-caps;"> echo get_vocab("identification"); </legend>
            <table style="width: 100%; border: 0;" cellpadding="5" cellspacing="0">
                <tr>
                    <td style="text-align: right; width: 40%; font-variant: small-caps;"> echo get_vocab("login"); </td>
                    <td style="text-align: center; width: 60%;"><input type="text" id="login" name="login" /></td>
                </tr>
                <tr>
                    <td style="text-align: right; width: 40%; font-variant: small-caps;"> echo get_vocab("pwd"); </td>
                    <td style="text-align: center; width: 60%;"><input type="password" name="password" /></td>
                </tr>
            </table>*/

            if (isset($_GET['url'])) {
                $tplArray['url'] = rawurlencode(strip_tags($_GET['url']));
                /*echo "<input type=\"hidden\" name=\"url\" value=\"".$url."\" />\n";*/
            } else {
                $tplArray['url'] = false;
            }

            /*<input type="submit" name="submit" value=" echo get_vocab("OK"); " style="font-variant: small-caps;" />
        </fieldset>
    </form>
    <script type="text/javascript">
        document.getElementById('login').focus();
    </script>*/

    if (Settings::get('webmaster_email') != '') {
        $lien = affiche_lien_contact('contact_administrateur', 'identifiant:non', 'seulement_si_email');
        if ($lien != '') {
            $tplArray['webmasterMail'] = $lien;
        } else {
            $tplArray['webmasterMail'] = false;
        }
    } else {
        $tplArray['webmasterMail'] = false;
    }
    /*echo "<a href=\"javascript:history.back()\">Précedent";
    echo " - <b>".Settings::get("company")."</b></a>";*/

    /*<br />
    <br />*/
    $tplArray['version'] = Settings::get('version');

    //$grr_devel_url = "http://grr.devome.com/";
    /*echo "<br /><p class=\"small\"><a href=\"".$grr_devel_url."\">".get_vocab("mrbs")."</a> - ".get_vocab("grr_version").affiche_version();*/
    /*$email = explode('@',$grr_devel_email);
    $person = $email[0];
    $domain = $email[1];*/
    /*echo "<br />".get_vocab("msg_login1")."<a href=\"".$grr_devel_url."\">".$grr_devel_url."</a>";*/
echo $twig->render('login.html.twig', $tplArray);
