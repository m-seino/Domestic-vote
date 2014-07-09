<?php 
	include_once 'simple_custom_vote-vars.php';
?>
<?php 
	$valid = array();
	$mode = '';
	if($_POST) {
		$valid['name'] = $simple_custom_vote_validator->validate(array(
			$simple_custom_vote_validator::$VALIDATE_TYPE_REQUIRE
		),$_POST['name'],'<p>','<br>','</p>','名前');
		if(strlen(implode('', $valid)) == 0 ){
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare('INSERT INTO '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.'(name) VALUES (%s);',$_POST['name'])
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
	<h1><?php $simple_custom_vote_util->_($simple_custom_vote_controler::$plugin_name); ?> <?php $simple_custom_vote_util->_('データ登録'); ?> </h1>
	<link rel="stylesheet" type="text/css" href="<?php echo ($plugins_url.'/'.$simple_custom_vote_controler::$plugin_fix.'/css/'.$simple_custom_vote_controler::$plugin_fix.'.css'); ?>">
	<form method="post">
	<?php if ($mode == 'add'): ?>
		<div id="message" class="updated"><p><?php $simple_custom_vote_util->_('【'.$add_item_name.'】を登録しました'); ?></p></div>
	<?php endif; ?>
	<?php if ($mode == 'error'): ?>
		<div id="message" class="error"><p><?php $simple_custom_vote_util->_('入力内容に不備があります'); ?></p></div>
	<?php endif; ?>
	<table class="wp-list-table widefat fixed">
		<tbody>
			<tr>
				<th width="100px;">
					<?php $simple_custom_vote_util->_('Name'); ?> 
				</th>
				<td>
					<input name="name" value="<?php $simple_custom_vote_util->iz($_POST['name']); ?>" />
					<?php 
						$simple_custom_vote_util->_echo($valid['name']);
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" name="submit" id="simple_custom-vote-submit-regist" class="button button-primary" value="<?php $simple_custom_vote_util->_('Add'); ?>">
		<a href="<?php echo $simple_custom_vote_util->thisPluginUrl(); ?>" class="button button-delete" ><?php $simple_custom_vote_util->_('Cancel'); ?></a>
	</p>
	</form>
</div>