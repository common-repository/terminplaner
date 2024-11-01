<?php
/* 
User-Funktionen zur Bearbeitung von Terminen
Jean-Pierre Kousz
*/


// Festllegen des Shortcodes in der Terminplanungsseite
function tp_shortcode_show(){

	$lc_action = "";
	if(isset($_POST['action']) and $_POST['action'] != ""){
		$lc_action = $_POST['action'];
	} elseif(isset($_GET['action']) and $_GET['action'] != "") {
		$lc_action = $_GET['action'];
	}
	
	switch($lc_action){
	
		case "tp_add_save":
			tp_add_save();
			return;
		break;


		case "show":
			return tp_show_termin($atts=null);
		break;

	
		default:
			return tp_show_error($atts);
		break;
	}

}


// Fehlermeldung, wenn der Key-Parameter fehlt
function tp_show_error(){
	echo __("Required parameters are missing!","terminplaner")."<p>&nbsp</p><p>&nbsp</p><p>&nbsp</p><p>&nbsp</p>";
	return;
}


// Anzeige des Termines mit Eingabemöglichkeit der Verfügbarkeit
function tp_show_termin($atts){
	global $wpdb;
	tp_show_status($_GET['key'],"add");	
	return;
}


// Speichern der angegebenen Verfügbarkeit
function tp_add_save(){
	global $wpdb;
	$tp_table_name = $wpdb->prefix . "tp_termine";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_id=".$_POST['tp_tln_tp_id'];
    $lc_sql = esc_html($lc_sql);
	$tp_termin = $wpdb->get_row($lc_sql);

	if($tp_termin == ""){
		echo "<b>". __("No date entry is found!","terminplaner")."</b>";
		return;
	}
	
	if($_POST['tp_tln_name'] == ""){
		echo "<b>". __("There was no name entered!","terminplaner")."</b>";
		return;
	}

	$tp_table_name = $wpdb->prefix . "tp_teilnehmer";
	$_POST['tp_tln_tp_id'] = esc_html($_POST['tp_tln_tp_id']);
	$_POST['tp_tln_name'] = esc_html($_POST['tp_tln_name']);
	
	$la_insertdata['tp_tln_tp_id'] = $_POST['tp_tln_tp_id'];	
	$la_insertdata['tp_tln_name'] = htmlentities(stripslashes ($_POST['tp_tln_name']), ENT_QUOTES, "UTF-8");	

	$wpdb->insert($tp_table_name, $la_insertdata);
	$li_insert_id = $wpdb->insert_id;

	// Abfrage der Daten für den Termin
	$tp_table_name = $wpdb->prefix . "tp_daten";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_daten_tp_id=".$_POST['tp_tln_tp_id']." ORDER BY tp_t_datum,tp_t_zeit";
	$tp_daten = $wpdb->get_results($lc_sql);
	$la_insertdata = array();
	$la_insertdata['tp_teilnehmer_tp_tln_id'] = $li_insert_id;
	$ln_t_count=0;
	$tp_table_name = $wpdb->prefix . "tp_term_status";

	
	foreach ( $tp_daten as $tp_datum ){
		$tp_r_array[$ln_t_count] = $tp_datum->tp_term_id;

			$tp_opt_name = "option_".$ln_t_count;

			$tp_option = esc_html($_POST[$tp_opt_name]);
			switch($_POST[$tp_opt_name]){
				case "j":
				case "v":
						$la_insertdata['tp_daten_tp_term_id'] = $tp_datum->tp_term_id;
						$la_insertdata['tp_tpstat_choice'] = $tp_option;
						$wpdb->insert($tp_table_name, $la_insertdata);
				break;
			}
		$ln_t_count++;
	} 
	tp_show_status($tp_termin->tp_key,"none");	

	echo "<b>". __("Your availability has been added to this event!","terminplaner")."</b><br>". __("Thank you!","terminplaner")."<p>";

   	$tp_admin_mail = get_option('admin_email');
   	str_replace('\r','',$tp_admin_mail);
   	str_replace('\n','',$tp_admin_mail);
    $lc_mail_message = __("Hi","terminplaner")."\r\n\r\n".$_POST['tp_tln_name']." ". __("has recorded his availability!","terminplaner")."\r\n\r\n". __("Kind regards","terminplaner")."\r\n";
    	
    $lc_headers = 'From: '. __("webserver","terminplaner").' <'.$tp_admin_mail.'>';
	wp_mail($tp_termin->tp_mail, __("date survey:","terminplaner")." ".$tp_termin->tp_bez, $lc_mail_message, $lc_headers);
	return;
}

?>
