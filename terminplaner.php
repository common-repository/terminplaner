<?php
/*
Plugin Name: Terminplanner
Plugin URI: http://www.jpk.ch/terminplaner
Description: Dieses Plugin ermöglicht die Koordination von Terminen
Version: 1.3.2
Author: Jean-Pierre Kousz
Author URI: http://www.jpk.ch/
*/

define('DEFAULT_TP_PAGE_NAME', 'Terminplaner');
define('TP_DB_VERSION', 1);

load_plugin_textdomain( 'terminplaner', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
require_once ('tp_admin.php');
require_once ('tp_user.php');

global $wpdb;

register_activation_hook(__FILE__,'tp_activate');
register_deactivation_hook(__FILE__,'tp_deactivate');

add_shortcode( 'tp_show', 'tp_shortcode_show' );


/* 
Aktivieren des Plugins
Hier werden die Datenbanken erstellt und die bereits notwendigen
Einträge in die Tabellen vorgenommen
*/
function tp_activate() {
	global $wpdb;
	$tp_table_name = $wpdb->prefix."tp_termine";

	$sql = "CREATE TABLE IF NOT EXISTS ". $tp_table_name ." (
  		tp_id INT NOT NULL AUTO_INCREMENT,
  		tp_bez VARCHAR(45),
  		tp_beschreibung VARCHAR(500),
  		tp_ersteller VARCHAR(45),
  		tp_mail VARCHAR(45),
  		tp_key VARCHAR(15),
  		tp_timestamp TIMESTAMP,
  		PRIMARY KEY(tp_id)); 
  		";


	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	$tp_table_name = $wpdb->prefix."tp_daten";

	$sql = "CREATE TABLE IF NOT EXISTS ".$tp_table_name." (
  		tp_term_id INT NOT NULL AUTO_INCREMENT ,
  		tp_daten_tp_id INT,
  		tp_t_datum DATE,
  		tp_t_zeit VARCHAR(6),
  		PRIMARY KEY(tp_term_id))";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);


	$tp_table_name = $wpdb->prefix."tp_teilnehmer";

	$sql = "CREATE TABLE IF NOT EXISTS ".$tp_table_name." (
  		tp_tln_id INT NOT NULL AUTO_INCREMENT ,
  		tp_tln_tp_id INT ,
  		tp_tln_name VARCHAR(45),
  		tp_tln_mail VARCHAR(45),
  		tp_tln_bemerkung VARCHAR(45),
  		tp_tln_timestamp TIMESTAMP,
  		PRIMARY KEY(tp_tln_id))";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);


	$tp_table_name = $wpdb->prefix."tp_term_status";

	$sql = "CREATE TABLE IF NOT EXISTS ".$tp_table_name." (
  		tp_tstat_id INT NOT NULL AUTO_INCREMENT ,
  		tp_daten_tp_term_id INT NOT NULL,
  		tp_teilnehmer_tp_tln_id INT NOT NULL,
  		tp_tpstat_choice VARCHAR(2),
  		PRIMARY KEY(tp_tstat_id))";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	update_option('tp_db_version', TP_DB_VERSION);

	// Terminseite erzeugen, wenn nötig
   	$tp_page_id = get_option('tp_page');

   	if ($tp_page_id != "" ) {
      query_posts("page_id=$tp_page_id");
      $count = 0;
      while(have_posts()) { the_post();
         $count++;
      }
      if ($count == 0) tp_create_page(); 
    } else {
        tp_create_page(); 
	}

	
}

// Deinstallieren des PlugIn
function tp_deactivate()
{
	global $wpdb;
	tp_delete_page();
	delete_option('tp_page');

}

?>
