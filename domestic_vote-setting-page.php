<?php 
	include_once 'domestic_vote-vars.php';
?>
<div class="wrap">
	<h2><?php $DU->_($CA::$plugin_name); ?> <?php $DU->_('設定ページ'); ?> <a href="<?php echo $DU->thisPluginUrl('add'); ?>" class="add-new-h2" ><?php $DU->_('投票項目を追加'); ?></a></h2>
	<link rel="stylesheet" type="text/css" href="<?php echo ($plugins_url.'/'.$CA::$plugin_fix.'/css/'.$CA::$plugin_fix.'.css'); ?>">

	<?php 
		global $wpdb;
		$type_data = $wpdb->get_results(
			'SELECT id,name FROM '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME.' ORDER BY id desc '
		);
	?>
	<table class="wp-list-table widefat fixed domestic-vote-table">
		<thead>
			<tr>
				<th width="20%"><?php $DU->_('投票項目名'); ?></th>
				<th width="70%"><?php $DU->_('投票用ショートコード'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($type_data as $key => $value): ?>
			<tr>
				<td>
					<?php $DU->_($value->name); ?>
					<div class="row-actions">
						<span class="edit">
							<a href="<?php echo $DU->thisPluginUrl('info',$value->id); ?>"><?php $DU->_('詳細'); ?></a>
						</span>
						|
						<span class="edit">
							<a href="<?php echo $DU->thisPluginUrl('edit',$value->id); ?>"><?php $DU->_('編集'); ?></a>
						</span>
						|
						<span class="delete">
							<a href="<?php echo $DU->thisPluginUrl('delete',$value->id); ?>"><?php $DU->_('削除'); ?></a>
						</span>
					</div>
				</td>
				<td>
					[dvote type_id="<?php echo($value->id); ?>" post_id="{<?php $DU->_('投稿のID'); ?>}" html="{<?php $DU->_('aタグ内のHTML'); ?>}" class="{<?php $DU->_('（任意）aタグに付与するclass名'); ?>}"]
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>
<div class="wrap">
	<h2><?php $DU->_('得票数一覧'); ?></h2>
	<table>
		<thead>
			
		</thead>
	</table>
</div>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.1/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo ($plugins_url.'/'.$CA::$plugin_fix.'/js/'.$CA::$plugin_fix.'.js'); ?>"></script>
<script type="text/javascript">
$(function(){
	// initial data read.
	$.ajax({
		type: "POST",
		url: ajaxurl,
		dataType: 'json',
		data: {
			action: 'domestic_vote_read'
		}
	})
	.done(function(data){
		var d = document;
		var _obj = {};
		console.log(data);

		$('.domestic-vote-progress').fadeOut('fast');
	});
});
</script>
<div class="domestic-vote-progress domestic-vote-progress-cover">
</div>
<div class="domestic-vote-progress domestic-vote-progress-message-wrap">
	<span class="domestic-vote-progress-message">
	<?php $DU->_('just a moment please. ( ˘ω˘ )'); ?>
	</span>
</div>