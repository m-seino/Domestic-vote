<?php 
	include_once 'domestic_vote-vars.php';
?>
<?php 

	if(isset($_GET['id'])) {
		global $wpdb;
		$_result  = $wpdb->get_results(
			'SELECT type_id FROM '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME.' WHERE id ='.$_GET['id']
		);
		$wpdb->get_results(
			'DELETE FROM '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME.' WHERE id ='.$_GET['id']
		);
		$wpdb->get_results(
			'DELETE FROM '.DOMESTIC_VOTE_PLUGIN_COUNT_TABLE_NAME.' WHERE type_id ='.$_result[0]->type_id
		);
		include_once 'domestic_vote-setting-page.php';
	}
	else {
		$DU->_('不正なリクエストです');
		die;
	}
?>