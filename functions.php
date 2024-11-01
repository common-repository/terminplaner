<?php
/* 
Hilfs-Funktionen zur Bearbeitung von Terminen
Jean-Pierre Kousz
*/


// Erstellen der Terminplanungsseite
function tp_create_page() {
   global $wpdb;
   $postarr = array(
      'post_status'=> 'publish',
      'post_title' => __("date survey","terminplaner"),
      'post_name'  => $wpdb->escape(__('datesurvey','terminplaner')),
      'post_content' => '[tp_show]',
      'post_type'  => 'page',
      'comment_status' => 'closed',
   );
   if ($int_post_id = wp_insert_post($postarr)) {
      update_option('tp_page', $int_post_id);
   }
}


// Löschen der Terminplanungsseite
function tp_delete_page() {
   global $wpdb;
   $tp_page_id = get_option('tp_page' );
   if ($tp_page_id)
      wp_delete_post($tp_page_id, true);
}



// Datum umwandeln
function tp_date_dt_to_atom($datum_dt){
	return substr($datum_dt,6,4).substr($datum_dt,3,2).substr($datum_dt,0,2);
}


function tp_date_atom_to_dt($datum_dt){
	return substr($datum_dt,8,2).".".substr($datum_dt,5,2).".".substr($datum_dt,0,4);
}


// Request URL anpassen
function tp_trim_request_url($ls_request_url){
//	return $ls_request_url;
	$ln_pos = strpos($ls_request_url,"action")-1;
	if($ln_pos > 0){
		$ls_request_url = substr($ls_request_url,0,$ln_pos);
	}
	return $ls_request_url;
}



function tp_html_entity_decode_deep($var) 
{
        if(is_array($var)) {
                foreach($var as $k => $v) {
                        $var[$k] = tp_html_entity_decode_deep($v);
                }
        } else {
                $var = html_entity_decode($var, ENT_QUOTES, "UTF-8");
        }
        return $var;
}


//Generieren eines Zufallsstrings
function zufallsstring($laenge){
	$zeichen = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$zufalls_string = '';
	$anzahl_zeichen = strlen($zeichen);
	for($i=0;$i<$laenge;$i++){
		$zufalls_string .= $zeichen[mt_rand(0, $anzahl_zeichen - 1)];
	}
	return $zufalls_string;
}


// Anzeige des Dashboard Titel
function tp_show_dashboard_head($tp_title){
	echo '<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>'. __("Date survey","terminplaner")." > " .$tp_title.'</h2><p>&nbsp;</p>';
}



// Ersetzen der Vorgaben im Formular
function form_replace($tp_form_str,$tp_action_mode,$tp_key,$termin){
	$tp_form_str = str_replace("%action_mode%",(isset($tp_action_mode)) ? $tp_action_mode : "",$tp_form_str);
	$tp_form_str = str_replace("%tp_id%",(isset($termin->tp_id)) ? $termin->tp_id : "",$tp_form_str);
	$tp_form_str = str_replace("%tp_key%",(isset($termin->tp_key)) ? $termin->tp_key : $tp_key,$tp_form_str);
	$tp_form_str = str_replace("%tp_bez%",(isset($termin->tp_bez)) ? $termin->tp_bez : "",$tp_form_str);
	$tp_form_str = str_replace("%tp_beschreibung%",(isset($termin->tp_beschreibung)) ? $termin->tp_beschreibung : "",$tp_form_str);
	$tp_form_str = str_replace("%tp_ersteller%",(isset($termin->tp_ersteller)) ? $termin->tp_ersteller : "",$tp_form_str);
	$tp_form_str = str_replace("%tp_mail%",(isset($termin->tp_mail)) ? $termin->tp_mail : "",$tp_form_str);
	$tp_form_str = str_replace("%tp_t_datum%",(isset($datum->tp_t_datum)) ? $datum->tp_t_datum : "",$tp_form_str);
	$tp_form_str = str_replace("%tp_t_zeit%",(isset($datum->tp_t_zeit)) ? $datum->tp_t_zeit : "",$tp_form_str);
	$tp_form_str = str_replace("#EVENTNAME#",__("Event name","terminplaner"),$tp_form_str);
	$tp_form_str = str_replace("#EVENTDESCRIPTION#",__("Event description","terminplaner"),$tp_form_str);
	$tp_form_str = str_replace("#CREATORNAME#",__("Creator name","terminplaner"),$tp_form_str);
	$tp_form_str = str_replace("#CREATORMAIL#",__("Creator e-mail","terminplaner"),$tp_form_str);
	$tp_form_str = str_replace("#DATE#",__("Date","terminplaner"),$tp_form_str);
	$tp_form_str = str_replace("#TIME#",__("Time","terminplaner"),$tp_form_str);
	$tp_form_str = str_replace("#ADDDATE#",__("add date","terminplaner"),$tp_form_str);
	$tp_form_str = str_replace("#SAVEBUTTON#",__("Save","terminplaner"),$tp_form_str);
	return $tp_form_str;
}


// Anzeigen der Verfügbarkeiten für einen Termin
function tp_show_status($tp_key,$tp_mode=null){
	global $wpdb;
	
	// Abfragen des Termines
	$tp_table_name = $wpdb->prefix . "tp_termine";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_key='".$tp_key."'";
	$tp_termin = $wpdb->get_row($lc_sql);
	
	if($tp_termin==""){
		echo "<b>". __("Wrong key!","terminplaner")."<p>&nbsp</p><p>&nbsp</p><p>&nbsp</p><p>&nbsp</p></b>";
		return;
	}

	// Ausgabe der TP Styles
	$tp_css = WP_PLUGIN_DIR."/terminplaner/forms/tp_styles.css";
	echo "<style type='text/css'><!--".file_get_contents($tp_css)."--></style>";
	
	echo "<p><b>".$tp_termin->tp_bez."</b><br>";
	echo __("Created by:","terminplaner")." <a href='mailto:".$tp_termin->tp_mail."'>".$tp_termin->tp_ersteller."</a></p>";
	echo "<hr size='1'>";
	echo "<p>".$tp_termin->tp_beschreibung."<br>";
	echo "<hr size='1'></p>";
	
	
	
	// Abfragen der Daten des Termines
	$tp_table_name = $wpdb->prefix . "tp_daten";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_daten_tp_id=".$tp_termin->tp_id." ORDER BY tp_t_datum,tp_t_zeit";
	$tp_daten = $wpdb->get_results($lc_sql);


	// Falls dazufügen, Formtag einfügen
	if($tp_mode == "add"){
		echo "<form method='POST'>";
		echo "<input type='hidden' name='action' value='tp_add_save'>";
		echo "<input type='hidden' name='tp_tln_tp_id' value='".$tp_termin->tp_id."'>";
	}
	
	// Anzeige des Kopfes mit den einzelnen Terminen
	$ln_t_count=0;
	echo "<table class='tp_table'>";
	echo "<tr><td class='tp_gray'>". __("participant / dates","terminplaner")."</td>";
	foreach ( $tp_daten as $tp_datum ){
//		$tp_einzel_datum = substr(tp_date_atom_to_dt($tp_datum->tp_t_datum),0,6);
		$tp_einzel_datum = date_i18n(get_option('date_format') ,strtotime($tp_datum->tp_t_datum));
		echo "<td align='center' class='tp_gray'>".$tp_einzel_datum."<br>".$tp_datum->tp_t_zeit."</td>";
		$tp_r_count[$ln_t_count] = 0;
		$tp_r_array[$ln_t_count] = $tp_datum->tp_term_id;
		$ln_t_count++;
	} 
	echo "</tr>";
	
	// Abfragen der Teilnehmer
	$tp_table_name = $wpdb->prefix . "tp_teilnehmer";
	$tp_table_status = $wpdb->prefix . "tp_term_status";
    $lc_sql = "SELECT * FROM $tp_table_name WHERE tp_tln_tp_id=".$tp_termin->tp_id;
	$tp_teilnehmende = $wpdb->get_results($lc_sql);
	foreach ( $tp_teilnehmende as $tp_teilnehmer ){
		echo "<tr>";
		echo "<td class='tp_lgray'>".$tp_teilnehmer->tp_tln_name."</td>";
		for($ln_count=0;$ln_count<$ln_t_count;$ln_count++){
    		$lc_sql = "SELECT * FROM $tp_table_status WHERE tp_teilnehmer_tp_tln_id=".$tp_teilnehmer->tp_tln_id." and tp_daten_tp_term_id=".$tp_r_array[$ln_count];
			$tp_status = $wpdb->get_row($lc_sql);

			$tp_switch_choise = "";
			if(isset($tp_status->tp_tpstat_choice)){
				$tp_switch_choise = $tp_status->tp_tpstat_choice;
				if($tp_status->tp_tpstat_choice == "j" or $tp_status->tp_tpstat_choice == "v"){
					$tp_r_count[$ln_count]++;
				}
			}
			switch($tp_switch_choise){
				case "j":
					echo "<td class='tp_green'>";
				break;
				case "v":
					echo "<td class='tp_yellow'>";
				break;
				default:
					echo "<td class='tp_red'>";
				break;
			}

			echo "</td>";
		}
		echo "</tr>";
	} 


	// Anzeige des Forms für neue Teilnehmende


	// Anzeige der möglichen Teilnehmern
	echo "<tr>";
	echo "<td class='tp_lgray'>&nbsp;</td>";
	for($ln_count=0;$ln_count<$ln_t_count;$ln_count++){
		echo "<td align='center' bgcolor='#f0f0f0'>".$tp_r_count[$ln_count]."</td>";
	}
	echo "</tr>";
	
	
	// Abfrage, ob User eingeloggt, Daten in Formular übernehmen
	if ( is_user_logged_in() ) {
		global $current_user;
		get_currentuserinfo();
	      	$tp_username = $current_user->user_firstname." ".$current_user->user_lastname;
	} else {
      	$tp_username = "";
	}
	
	if($tp_mode == "add"){
		echo "<tr><td bgcolor='#e0e0e0'><input class='tp_input' type='text' size='20' maxlength='45' name='tp_tln_name' value='".$tp_username."'>";
		for($ln_count=0;$ln_count<$ln_t_count;$ln_count++){
			$tp_select_name="option_".$ln_count;
			echo "<td align='center' bgcolor='#e0e0e0'><select class='tp_select' name='".$tp_select_name."'><option value=''>". __("no","terminplaner")."</option><option value='j'>". __("yes","terminplaner")."</option><option value='v'>". __("mb","terminplaner")."</option></select></td>";
		}
		echo "</tr>";
	}

	echo "</table><br>";

	if($tp_mode == "add"){
		echo "<input type='submit' value=' ". __("submit","terminplaner")." '></form><p>&nbsp;</p>";
	}
	
	return $tp_termin->tp_id;
}


?>
