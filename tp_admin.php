<?php
/*
Admin-Funktionen zur Bearbeitung von Terminen
Jean-Pierre Kousz
*/

include ('functions.php');


add_action('admin_menu', 'show_menu_tp');

// Festlegen des Menueintrages in Dashboard
function show_menu_tp() {
	add_menu_page('termine', __("Date survey","terminplaner"),'read','tp_admin', 'tp_show_user_ui',null,4);
}


// Handler für Shortcodes User Interface
function tp_show_user_ui() {
	if (!current_user_can('read'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
    $_SERVER["REQUEST_URI"] = tp_trim_request_url($_SERVER["REQUEST_URI"]);

	$lc_action = "";
	if(isset($_POST['action']) and $_POST['action'] != ""){
		$lc_action = $_POST['action'];
	} elseif(isset($_GET['action']) and $_GET['action'] != "") {
		$lc_action = $_GET['action'];
	}
	
	switch($lc_action){

		case "add_termin":
			tp_add_termin();
		break;
	
		case "show_tp":
			tp_show_adm_termin();
		break;

		case "edit_tp":
			tp_edit_adm_termin();
		break;

		case "edit_tp_save":
			tp_edit_adm_termin_save();
		break;

		case "tp_add_termin_save":
			tp_add_termin_save();
		break;

		case "tp_add_datum_save":
			tp_add_datum_save();
		break;
	
		case "delete_termin":
			tp_delete_adm_termin();
		break;

		case "delete_datum":
			tp_delete_adm_datum();
		break;

		case "delete_termin_do":
			tp_delete_adm_termin_do();
		break;

		default:
			tp_show_termine();
		break;
	}

}


function tp_show_termine(){
	global $wpdb;
	$tp_table_name = $wpdb->prefix . "tp_termine";
	
	$tp_termine = $wpdb->get_results("SELECT * FROM $tp_table_name ORDER BY tp_id DESC");

	echo '<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>'.__("Date survey","terminplaner").' > '.__("Overview","terminplaner").' &nbsp;<a href="'.$_SERVER["REQUEST_URI"].'&action=add_termin" class="add-new-h2">'.__("add survey","terminplaner").'</a></h2><p>&nbsp;</p>
	<table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead><tr>
	<th width="30px" align="right">ID</th>
	<th width="120px">'.__("Period of time","terminplaner").'</th>
	<th width="140px">'.__("Creator","terminplaner").'</th>
	<th>'.__("Event description","terminplaner").'</th>
	</tr></thead>';
	
	foreach ( $tp_termine as $termin ) 
	{
		$terminvonbis="";
		$tp_table_name = $wpdb->prefix . "tp_daten";
	    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_daten_tp_id=".$termin->tp_id." ORDER BY tp_t_datum,tp_t_zeit";
		$tp_daten = $wpdb->get_results($lc_sql);

		$ln_t_count=0;
		foreach ( $tp_daten as $tp_datum ){
			$tp_einzel_datum = tp_date_atom_to_dt($tp_datum->tp_t_datum);
			if($ln_t_count == 0){
				$terminvonbis = $tp_einzel_datum;
			}
		$ln_t_count++;
		} 
		$terminvonbis .= " - ".$tp_einzel_datum;

		echo "<tr><td align='right'>".$termin->tp_id."</td><td>".$terminvonbis."</td><td><b>".$termin->tp_ersteller."</b><br><a href='".$_SERVER["REQUEST_URI"]."&action=edit_tp&tp_id=".$termin->tp_id."'>".__("Edit","terminplaner")."</a>&nbsp;&nbsp;<a href='".$_SERVER["REQUEST_URI"]."&action=delete_termin&tp_key=".$termin->tp_key."'>".__("Delete","terminplaner")."</a><br></td><td><b><a href='".$_SERVER["REQUEST_URI"]."&action=show_tp&tp_key=".$termin->tp_key."'>".$termin->tp_bez."</a></b><br>".$termin->tp_beschreibung."<br>Link: <a href='".get_home_url()."/". __("datesurvey","terminplaner")."?action=show&key=".$termin->tp_key."' target='_blank'>".get_home_url()."/". __("datesurvey","terminplaner")."?action=show&key=".$termin->tp_key."</td></tr>";
	}
	
	echo '</table></div>';

}


// Status eines Termines ausgeben
function tp_show_adm_termin(){
	global $wpdb;

	tp_show_dashboard_head(__("date detail","terminplaner"));
	$_GET['tp_key'] = esc_html($_GET['tp_key']);

	tp_show_status($_GET['tp_key']);	

	return;
}


// Termin bearbeiten
function tp_edit_adm_termin(){
	global $wpdb;

	tp_show_dashboard_head(__("date modify","terminplaner"));

//	$_GET['tp_key'] = esc_html($_GET['tp_key']);
	$tp_table_name = $wpdb->prefix . "tp_termine";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_id=".$_GET['tp_id'];
	$tp_termin = $wpdb->get_row($lc_sql);

	$tp_form_1 = WP_PLUGIN_DIR."/terminplaner/forms/form_termin_edit.frm";
	$tp_html = file_get_contents($tp_form_1);

	$tp_html = form_replace($tp_html,"edit_tp_save",$tp_termin->tp_key,$tp_termin);
	echo $tp_html;
}


function tp_edit_adm_termin_save(){
	global $wpdb;
	
	tp_show_dashboard_head("event");

	$_POST['tp_bez'] = esc_html($_POST['tp_bez']);
	$_POST['tp_beschreibung'] = esc_html($_POST['tp_beschreibung']);
	$_POST['tp_ersteller'] = esc_html($_POST['tp_ersteller']);
	$_POST['tp_mail'] = esc_html($_POST['tp_mail']);
	$_POST['tp_id'] = esc_html($_POST['tp_id']);
	
	$tp_table_name = $wpdb->prefix . "tp_termine";
	$la_insertdata = array();
	$la_insertdata['tp_bez'] = $_POST['tp_bez'];
	$la_insertdata['tp_beschreibung'] = $_POST['tp_beschreibung'];
	$la_insertdata['tp_ersteller'] = $_POST['tp_ersteller'];
	$la_insertdata['tp_mail'] = $_POST['tp_mail'];
	
	$la_where['tp_id'] = $_POST['tp_id'];

	$wpdb->update($tp_table_name, $la_insertdata,$la_where);

	tp_show_status($_POST['tp_key']);	

}

// Formular für neuen Termin ausgeben
function tp_add_termin(){
	tp_show_dashboard_head("new event");
	
	$tp_action_mode = "tp_add_termin_save";
	$tp_key = zufallsstring(10);
	$termin = new stdClass;

	if ( is_user_logged_in() ) {
		global $current_user;
		get_currentuserinfo();
	      	$termin->tp_ersteller = $current_user->user_firstname." ".$current_user->user_lastname;
	      	$termin->tp_mail = $current_user->user_email;
	} else {
      	$tp_username = "";
	}


	$tp_form_1 = WP_PLUGIN_DIR."/terminplaner/forms/form_termin_t1.frm";
	$tp_form_2 = WP_PLUGIN_DIR."/terminplaner/forms/form_termin_t2.frm";

	$tp_html = file_get_contents($tp_form_1).file_get_contents($tp_form_2);

	$tp_html = form_replace($tp_html,$tp_action_mode,$tp_key,$termin);
	echo $tp_html;

	return;	
}


// Speichern eines neuen Termines mit dem ersten Datum
function tp_add_termin_save(){
	global $wpdb;

	tp_show_dashboard_head($_POST['tp_bez']);

	if($_POST['tp_bez'] == "" OR $_POST['tp_beschreibung'] == "" OR $_POST['tp_ersteller'] == "" OR $_POST['tp_mail'] == "" OR $_POST['tp_t_datum'] == "" OR $_POST['tp_t_zeit'] == ""){
		echo __("Mandatory informations are missing.","terminplaner")."<br><a href='javascript:history.back()'>".__("Back to input!","terminplaner")."</a>";
		return;
	}

	$_POST['tp_bez'] = esc_html($_POST['tp_bez']);
	$_POST['tp_beschreibung'] = esc_html($_POST['tp_beschreibung']);
	$_POST['tp_ersteller'] = esc_html($_POST['tp_ersteller']);
	$_POST['tp_mail'] = esc_html($_POST['tp_mail']);
	if(isset($_POST['tp_id'])){
		$_POST['tp_id'] = esc_html($_POST['tp_id']);
	}
	$_POST['tp_t_datum'] = esc_html($_POST['tp_t_datum']);
	$_POST['tp_t_zeit'] = esc_html($_POST['tp_t_zeit']);

	$tp_table_name = $wpdb->prefix . "tp_termine";
	$la_insertdata = array();
	$la_insertdata['tp_key'] = $_POST['tp_key'];
	$la_insertdata['tp_bez'] = $_POST['tp_bez'];
	$la_insertdata['tp_beschreibung'] = $_POST['tp_beschreibung'];
	$la_insertdata['tp_ersteller'] = $_POST['tp_ersteller'];
	$la_insertdata['tp_mail'] = $_POST['tp_mail'];

	$wpdb->insert($tp_table_name, $la_insertdata);
	$li_insert_id = $wpdb->insert_id;

	$tp_table_name = $wpdb->prefix . "tp_daten";
	$la_insertdata = array();
	$la_insertdata['tp_daten_tp_id'] = $li_insert_id;
	$la_insertdata['tp_t_datum'] = tp_date_dt_to_atom($_POST['tp_t_datum']);
	$la_insertdata['tp_t_zeit'] = $_POST['tp_t_zeit'];
	$wpdb->insert($tp_table_name, $la_insertdata);

	$tp_table_name = $wpdb->prefix . "tp_termine";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_id='".$li_insert_id."'";
	$tp_termin = $wpdb->get_row($lc_sql);

	$tp_form_1 = WP_PLUGIN_DIR."/terminplaner/forms/form_termin_t1ro.frm";
	$tp_html = file_get_contents($tp_form_1);

	$tp_html = form_replace($tp_html,tp_add_datum_save,$termin->tp_key,$tp_termin);
	echo $tp_html;

	// Anzeige der bestehenden der Daten für den Termin
	$tp_table_name = $wpdb->prefix . "tp_daten";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_daten_tp_id=".$li_insert_id;
	$tp_daten = $wpdb->get_results($lc_sql);
	$tp_d_count = 0;
	foreach ( $tp_daten as $tp_datum ){
		$tp_d_count++;	
		echo "<tr><td>".$tp_d_count.". ".__("Date","terminplaner")."</td><td>".tp_date_atom_to_dt($tp_datum->tp_t_datum)."</td>";
		echo "<td>$tp_datum->tp_t_zeit</td><td align='right'></td></tr>";
	}

	$tp_form_2 = WP_PLUGIN_DIR."/terminplaner/forms/form_termin_t2.frm";
	$tp_html = file_get_contents($tp_form_2);
	$tp_html = form_replace($tp_html,"","","");
	echo $tp_html;
}


function tp_add_datum_save($tp_id=null){
	global $wpdb;

	if($tp_id!==null){
		$_POST['tp_id'] = $tp_id;
	}
	echo '<div class="wrap">';

	$_POST['tp_id'] = esc_html($_POST['tp_id']);
	$_POST['tp_t_datum'] = esc_html($_POST['tp_t_datum']);
	$_POST['tp_t_zeit'] = esc_html($_POST['tp_t_zeit']);

	$tp_table_name = $wpdb->prefix . "tp_termine";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_id='".$_POST['tp_id']."'";
	$tp_termin = $wpdb->get_row($lc_sql);
	
	tp_show_dashboard_head($tp_termin->tp_bez);

	if(!isset($tp_id)){
		if($_POST['tp_t_datum'] == "" OR $_POST['tp_t_zeit'] == ""){
			echo __("Mandatory informations are missing.","terminplaner").".<br><a href='javascript:history.back()'>".__("back","terminplaner")."</a>".__("to input!","terminplaner");
			return;
		}

		$tp_table_name = $wpdb->prefix . "tp_daten";
		$la_insertdata = array();
		$la_insertdata['tp_daten_tp_id'] = $_POST['tp_id'];
		$la_insertdata['tp_t_datum'] = tp_date_dt_to_atom($_POST['tp_t_datum']);
		$la_insertdata['tp_t_zeit'] = $_POST['tp_t_zeit'];
		$wpdb->insert($tp_table_name, $la_insertdata);
	}


	$tp_form_1 = WP_PLUGIN_DIR."/terminplaner/forms/form_termin_t1ro.frm";
	$tp_html = file_get_contents($tp_form_1);

	$tp_html = form_replace($tp_html,"tp_add_datum_save",$tp_termin->tp_key,$tp_termin);
	echo $tp_html;

	// Anzeige der bestehenden der Daten für den Termin
	$tp_table_name = $wpdb->prefix . "tp_daten";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_daten_tp_id=".$_POST['tp_id']." ORDER BY tp_t_datum,tp_t_zeit";
	$tp_daten = $wpdb->get_results($lc_sql);
	$tp_d_count = 0;
	$tp_delete_sign = WP_PLUGIN_URL."/terminplaner/forms/delete.jpg";
	foreach ( $tp_daten as $tp_datum ){
		$tp_d_count++;	
		echo "<tr><td>".$tp_d_count.". ".__("Date","terminplaner")."</td><td>".tp_date_atom_to_dt($tp_datum->tp_t_datum)."</td>";
		echo "<td>$tp_datum->tp_t_zeit</td><td><a href='".$_SERVER["REQUEST_URI"]."&action=delete_datum&tp_id=".$tp_termin->tp_id."&tp_term_id=".$tp_datum->tp_term_id."'><img src='".$tp_delete_sign."'></td></tr>";
	}

	$tp_form_2 = WP_PLUGIN_DIR."/terminplaner/forms/form_termin_t2.frm";
	$tp_html = file_get_contents($tp_form_2);
	$tp_html = form_replace($tp_html,"","","");
	echo $tp_html;
	
	echo "<p><a href='".$_SERVER["REQUEST_URI"]."'>".__("Close","terminplaner")."!</a></p>";
}


// Einzelnes Datum löschen
function tp_delete_adm_datum(){
	global $wpdb;
	$_GET['tp_term_id'] = esc_html($_GET['tp_term_id']);

	$tp_table_daten = $wpdb->prefix . "tp_daten";
	$wpdb->query("delete from ".$tp_table_daten." where tp_term_id=".$_GET['tp_term_id']);
	tp_add_datum_save($_GET['tp_id']);
}


// Bestehender Termin löschen
function tp_delete_adm_termin(){
	global $wpdb;

	tp_show_dashboard_head(__("detail"));

	$_GET['tp_term_id'] = esc_html($_GET['tp_term_id']);
	$tp_id = tp_show_status($_GET['tp_key'],"");	

	echo "<a href='".$_SERVER["REQUEST_URI"]."&action=delete_termin_do&tp_id=".$tp_id."'>".__("delete")."</a>";
	return;
}

function tp_delete_adm_termin_do(){
	global $wpdb;

	tp_show_dashboard_head(__("delete"));
	
	// Löschen der Stati

	$_GET['tp_id'] = esc_html($_GET['tp_id']);
	$tp_table_termine = $wpdb->prefix . "tp_termine";
	$tp_table_daten = $wpdb->prefix . "tp_daten";
		$tp_table_teilnehmer = $wpdb->prefix . "tp_teilnehmer";
	$tp_table_status = $wpdb->prefix . "tp_term_status";
    $lc_sql = "SELECT * FROM $tp_table_teilnehmer WHERE tp_tln_tp_id=".$_GET['tp_id'];
	$tp_teilnehmende = $wpdb->get_results($lc_sql);
	foreach ( $tp_teilnehmende as $tp_teilnehmer ){
    	$lc_sql = "SELECT * FROM $tp_table_status WHERE tp_teilnehmer_tp_tln_id=".$tp_teilnehmer->tp_tln_id;
		$tp_stati = $wpdb->get_results($lc_sql);
		foreach ( $tp_stati as $tp_status ){
			$wpdb->query("delete from ".$tp_table_status." where tp_tstat_id=".$tp_status->tp_tstat_id);
		}
		$wpdb->query("delete from ".$tp_table_teilnehmer." where tp_tln_id=".$tp_teilnehmer->tp_tln_id);
	}
	$wpdb->query("delete from ".$tp_table_daten." where tp_daten_tp_id=".$_GET['tp_id']);
	$wpdb->query("delete from ".$tp_table_termine." where tp_id=".$_GET['tp_id']);

	echo "<p>".__("Event deleted!")."<br><a href='".$_SERVER["REQUEST_URI"]."'>".__("continue")."</a></p>";

}

?>
