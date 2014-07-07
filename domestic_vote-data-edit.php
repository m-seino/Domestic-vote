<?php 
	include_once 'domestic_vote-vars.php';
?>
<?php 
	$valid = array();
	$mode = '';
	if($_POST) {
		$valid['name'] = $DV->validate(array(
			$DV::$VALIDATE_TYPE_REQUIRE
		),$_POST['name'],'<p>','<br>','</p>','名前');
		if(strlen(implode('', $valid)) == 0 ){
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare('UPDATE '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME.' SET name = %s WHERE id = %s;',$_POST['name'],$_POST['id'])
			);
			$mode = 'add';
			$add_item_name = $_POST['name'];
			$_POST = array();
			setFormData();
		}
		else {
			$mode = 'error';
		}
	}
	else {
		setFormData();
	}

	function setFormData () {
		if(isset($_GET['id'])) {
			global $wpdb;
			$type_data = $wpdb->get_results(
				'SELECT id,name FROM '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME.' WHERE id ='.$_GET['id']
			);
			foreach ($type_data[0] as $key => $value) {
				$_POST[$key] = $value;
			}
		}
		else {
			die('不正なリクエストです');
		}
	}
?>
<div class="wrap">
	<h1><?php $DU->_($CA::$plugin_name); ?> <?php $DU->_('データ編集'); ?> </h1>
	<link rel="stylesheet" type="text/css" href="<?php echo ($plugins_url.'/'.$CA::$plugin_fix.'/css/'.$CA::$plugin_fix.'.css'); ?>">
	<form method="post">
	<?php if ($mode == 'add'): ?>
		<div id="message" class="updated"><p><?php $DU->_('【'.$add_item_name.'】を編集しました'); ?></p></div>
	<?php endif; ?>
	<?php if ($mode == 'error'): ?>
		<div id="message" class="error"><p><?php $DU->_('入力内容に不備があります'); ?></p></div>
	<?php endif; ?>
	<table class="wp-list-table widefat fixed">
		<tbody>
			<tr>
				<th width="100px;">
					<?php $DU->_('Name'); ?> 
				</th>
				<td>
					<input name="name" value="<?php $DU->iz($_POST['name']); ?>" />
					<?php $DU->_echo($valid['name']); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">

		<input type="hidden" name="id" value="<?php $DU->iz($_POST['id']); ?>" />
		<input type="submit" name="submit" id="domestic-vote-submit-regist" class="button button-primary" value="<?php $DU->_('Edit'); ?>">
		<a href="<?php echo $DU->thisPluginUrl(); ?>" class="button button-delete" ><?php $DU->_('Cancel'); ?></a>
	</p>
	</form>
</div>