<?php 
	if(!is_user_logged_in()) {die('request faild.');}
	include_once 'simple_custom_vote-vars.php';
?>
<?php 

	if(isset($_GET['id'])) {
		global $wpdb;
		$_result  = $wpdb->get_results(
			'SELECT type_id FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.' WHERE id ='.$_GET['id']
		);
		$wpdb->get_results(
			'DELETE FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.' WHERE id ='.$_GET['id']
		);
		$wpdb->get_results(
			'DELETE FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME.' WHERE type_id ='.$_result[0]->type_id
		);
		include_once 'simple_custom_vote-setting-page.php';
	}
	else {
		$simple_custom_vote_util->_('不正なリクエストです');
		die;
	}
?>