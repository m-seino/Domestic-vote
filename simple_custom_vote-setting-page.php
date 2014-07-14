<?php 
	include_once 'simple_custom_vote-vars.php';
?>
<div class="wrap">
	<h2><?php $simple_custom_vote_util->_($simple_custom_vote_controler::$plugin_name); ?> <?php $simple_custom_vote_util->_('設定ページ'); ?> <a href="<?php echo $simple_custom_vote_util->thisPluginUrl('add'); ?>" class="add-new-h2" ><?php $simple_custom_vote_util->_('投票項目を追加'); ?></a></h2>
	<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( $simple_custom_vote_controler::$plugin_fix.'.css', __FILE__ ); ?>">

	<?php 
		global $wpdb;
		$type_data = $wpdb->get_results(
			'SELECT id,name FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.' ORDER BY id desc '
		);
	?>
	<table class="wp-list-table widefat fixed simple_custom-vote-table">
		<thead>
			<tr>
				<th width="20%"><?php $simple_custom_vote_util->_('投票項目名'); ?></th>
				<th width="70%"><?php $simple_custom_vote_util->_('投票リンク用ショートコード'); ?> 
					<span><a href="https://github.com/m-seino/simple-custom-vote" target="_blank">[<?php $simple_custom_vote_util->_('ショートコードヘルプ'); ?>]</a></span>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php if (count($type_data) == 0): ?>
				
			<?php endif ?>
			<?php foreach ($type_data as $key => $value): ?>
			<tr>
				<td>
					<?php $simple_custom_vote_util->_($value->name); ?>
					<div class="row-actions">
						<span class="edit">
							<a href="<?php echo $simple_custom_vote_util->thisPluginUrl('info',$value->id); ?>"><?php $simple_custom_vote_util->_('詳細'); ?></a>
						</span>
						|
						<span class="edit">
							<a href="<?php echo $simple_custom_vote_util->thisPluginUrl('edit',$value->id); ?>"><?php $simple_custom_vote_util->_('編集'); ?></a>
						</span>
						|
						<span class="delete">
							<a href="<?php echo $simple_custom_vote_util->thisPluginUrl('delete',$value->id); ?>" class="scvote_delete" ><?php $simple_custom_vote_util->_('削除'); ?></a>
						</span>
					</div>
				</td>
				<td>
					[scvote type_id="<?php echo($value->id); ?>" post_id="{<?php $simple_custom_vote_util->_('投稿のID'); ?>}" html="{<?php $simple_custom_vote_util->_('aタグ内のHTML'); ?>}"]
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>
<div class="wrap">
	<h2><?php $simple_custom_vote_util->_('得票数一覧'); ?></h2>
	<p class="simple_custom-vote-progress-message">
		<?php $simple_custom_vote_util->_('データ取得中'); ?>
	</p>
	<table id="info_table" class="tablesorter wp-list-table widefat fixed simple_custom-vote-table">
		<thead id="info_table_head">
			<tr>
				<th width="30%">項目タイトル</th>
			</tr>
		</thead>
		<tbody id="info_table_body">
		</tbody>
	</table>
</div>
<script type="text/javascript">
jQuery(function( $ ) {
	$.ajax({
		type: "POST",
		url: ajaxurl,
		dataType: 'json',
		data: {
			action: 'simple_custom_vote_read'
		},
		beforeSend : function() {
			$('#info_table').hide();
		}
	})
	.done(function(data){
		var d = document;
		var _obj = {};

		if(!data.columns_name) {
			$('.simple_custom-vote-progress-message').text('<?php $simple_custom_vote_util->_("投票項目がありません"); ?>');
			return;
		}

		$.each(data.columns_name,function(){
			$('#info_table_head tr').append('<th><a href="#">'+this+'</a></th>');
		})
		$.each(data.data,function(){
			var $_tr = $('<tr></tr>');
			$_tr.append('<td>'+this.post_title+'</td>');
			for (var key in this) {
				if(key.indexOf('count_') != -1) {
					$_tr.append('<td>'+(this[key] === null ? '0' : this[key])+'</td>');
				}
			}
			$('#info_table_body').append($_tr);
		})

		$('.simple_custom-vote-progress-message').fadeOut('fast',function(){
			$('#info_table').fadeIn('fast');
			$('#info_table').tablesorter();
		});
	});

	$('.scvote_delete').on('click',function(e){
		if(!window.confirm("<?php $simple_custom_vote_util->_('本当に削除しますか？');  ?>")){
			e.preventDefault();
		}
	})
});

</script>