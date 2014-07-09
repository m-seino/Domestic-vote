<?php 
	if(!is_user_logged_in()) {die('request faild.');}
	include_once 'domestic_vote-vars.php';
?>
<?php 
	$valid = array();
	$mode = '';
	if($_POST) {
		$valid['name'] = $domestic_vote_validator->validate(array(
			$domestic_vote_validator::$VALIDATE_TYPE_REQUIRE
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
	<h1><?php $domestic_vote_util->_($domestic_vote_controler::$plugin_name); ?> <?php $domestic_vote_util->_('データ登録'); ?> </h1>
	<link rel="stylesheet" type="text/css" href="<?php echo ($plugins_url.'/'.$domestic_vote_controler::$plugin_fix.'/css/'.$domestic_vote_controler::$plugin_fix.'.css'); ?>">
	<form method="post">
	<?php if ($mode == 'add'): ?>
		<div id="message" class="updated"><p><?php $domestic_vote_util->_('【'.$add_item_name.'】を登録しました'); ?></p></div>
	<?php endif; ?>
	<?php if ($mode == 'error'): ?>
		<div id="message" class="error"><p><?php $domestic_vote_util->_('入力内容に不備があります'); ?></p></div>
	<?php endif; ?>
	<table class="wp-list-table widefat fixed">
		<tbody>
			<tr>
				<th width="100px;">
					<?php $domestic_vote_util->_('Name'); ?> 
				</th>
				<td>
					<input name="name" value="<?php $domestic_vote_util->iz($_POST['name']); ?>" />
					<?php 
						$domestic_vote_util->_echo($valid['name']);
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" name="submit" id="domestic-vote-submit-regist" class="button button-primary" value="<?php $domestic_vote_util->_('追加'); ?>">
		<a href="<?php echo $domestic_vote_util->thisPluginUrl(); ?>" class="button button-delete" ><?php $domestic_vote_util->_('戻る'); ?></a>
	</p>
	</form>
</div>