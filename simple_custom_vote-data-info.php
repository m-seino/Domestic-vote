<?php 
	include_once 'simple_custom_vote-vars.php';
?>
<?php 
	if(isset($_GET['id'])) {
		global $wpdb;
		$_votedata = '';
		// 投票項目のデータを取得
		$type_data = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id,name FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.' WHERE id = %d'
			,$_GET['id'])
		);
		foreach ($type_data[0] as $key => $value) {
			$_votedata[$key] = $value;
		}

		// 投票項目に紐づくデータを取得
		
		$vote_data = $wpdb->get_results(
			$wpdb->prepare(
			  'SELECT post.ID , post.post_title ,Sum(count.count) as count FROM '.$wpdb->prefix.'posts as post RIGHT JOIN '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME.' as count ON post.ID = count.post_id WHERE count.type_id = %d GROUP BY post.id ORDER BY count desc'
			,$_GET['id'])
		);
	}
	else {
		die('不正なリクエストです');
	}
?>
<div class="wrap">
	<h1><?php $simple_custom_vote_util->iz($_votedata['name']); ?> <?php $simple_custom_vote_util->_('投票数情報'); ?> </h1>
	<link rel="stylesheet" type="text/css" href="<?php echo ($plugins_url.'/'.$simple_custom_vote_controler::$plugin_fix.'/css/'.$simple_custom_vote_controler::$plugin_fix.'.css'); ?>">
	<form method="post">
	<table class="wp-list-table widefat fixed">
		<thead>
			<th width="80%">
				<?php $simple_custom_vote_util->_('投稿タイトル'); ?> 
			</th>
			<th width="20%">
				<?php $simple_custom_vote_util->_('得票数'); ?> 
			</th>
		</thead>
		<tbody>
			<?php foreach ($vote_data as $key => $value): ?>
			<tr>
				<td>
					<a href="<?php echo get_permalink( $value->ID );?>" target="_blank">
					<?php $simple_custom_vote_util->_($value->post_title); ?> 
					</a>
				</td>
				<td>
					<?php $simple_custom_vote_util->_($value->count); ?> 
				</td>
			</tr>
			<?php endforeach ?>
			<?php if (count($vote_data) == 0): ?>
				<td colspan="2">
					<?php $simple_custom_vote_util->_('投票データがありません'); ?> 
				</td>
			<?php endif ?>
		</tbody>
	</table>
	<p class="submit">
		<a href="<?php echo $simple_custom_vote_util->thisPluginUrl(); ?>" class="button button-delete" ><?php $simple_custom_vote_util->_('戻る'); ?></a>
	</p>
	</form>
</div>