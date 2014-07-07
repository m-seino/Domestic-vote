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
				$wpdb->prepare('INSERT INTO '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME.'(name) VALUES (%s);',$_POST['name'])
			);
			$mode = 'add';
			$add_item_name = $_POST['name'];
			$_POST = array();
		}
		else {
			$mode = 'error';
		}
	}
?>
<div class="wrap">
	<h1><?php $DU->_($CA::$plugin_name); ?> <?php $DU->_('データ登録'); ?> </h1>
	<link rel="stylesheet" type="text/css" href="<?php echo ($plugins_url.'/'.$CA::$plugin_fix.'/css/'.$CA::$plugin_fix.'.css'); ?>">
	<form method="post">
	<?php if ($mode == 'add'): ?>
		<div id="message" class="updated"><p><?php $DU->_('【'.$add_item_name.'】を登録しました'); ?></p></div>
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
					<?php 
						$DU->_echo($valid['name']);
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" name="submit" id="domestic-vote-submit-regist" class="button button-primary" value="<?php $DU->_('Add'); ?>">
		<a href="<?php echo $DU->thisPluginUrl(); ?>" class="button button-delete" ><?php $DU->_('Cancel'); ?></a>
	</p>
	</form>
</div>