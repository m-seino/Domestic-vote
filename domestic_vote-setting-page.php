<?php 
	include_once 'domestic_vote-vars.php';
?>
<div class="wrap">
	<h2><?php $DU->_($CA::$plugin_name); ?><?php $DU->_('設定ページ'); ?> <a href="<?php echo $DU->thisPluginUrl('add'); ?>" class="add-new-h2" ><?php $DU->_('Add'); ?></a></h2>
	<link rel="stylesheet" type="text/css" href="<?php echo ($plugins_url.'/'.$CA::$plugin_fix.'/css/'.$CA::$plugin_fix.'.css'); ?>">

	<?php 
		global $wpdb;
		$type_data = $wpdb->get_results(
			'SELECT id,name FROM '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME
		);
	?>
	<table class="wp-list-table widefat fixed domestic-vote-table">
		<thead>
			<tr>
				<th width="20%"><?php $DU->_('Name'); ?></th>
				<th width="70%"><?php $DU->_('Short code'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($type_data as $key => $value): ?>
			<tr>
				<td>
					<?php $DU->_($value->name); ?>
					<div class="row-actions">
						<span class="edit">
							<a href="<?php echo $DU->thisPluginUrl('edit',$value->id); ?>"><?php $DU->_('詳細'); ?></a>
						</span>
						|
						<span class="edit">
							<a href="<?php echo $DU->thisPluginUrl('edit',$value->id); ?>"><?php $DU->_('編集'); ?></a>
						</span>
						|
						<span class="delete">
							<a href="<?php echo $DU->thisPluginUrl('edit',$value->id); ?>"><?php $DU->_('削除'); ?></a>
						</span>
					</div>
				</td>
				<td>
					[dvote type_id="<?php echo($value->id); ?>" post_id="{<?php echo __('投稿のID'); ?>}" html="{<?php echo __('aタグ内のHTML'); ?>}" class="{<?php echo __('（任意）aタグに付与するclass名'); ?>}"]
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
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
		for (var i = data.length - 1; i >= 0; i--) {
			var chk = d.getElementById(
				data[i].role_name+'-'
				+data[i].disable_menu_key
				+(data[i].disable_submenu_key !== '' ? '-'+data[i].disable_submenu_key : '-' )
				+(data[i].remove_cap !== '' ? '-'+data[i].remove_cap : '' )
			)
			if(!chk){continue;}
			$(chk).attr('checked','checked');
			_obj['domestic-vote-submenu-row-'+data[i].disable_menu_key] = true;
			_obj['domestic-vote-cap-row-'+data[i].disable_menu_key] = true;
		};
		for (var key in _obj) {
			$('.'+key).show();
		};
		$('.domestic-vote-progress').fadeOut('fast');
	});

	// register
	$('#domestic-vote-submit-regist').on('click',function(){
		var checked = (function($chks){
			var s = [];
			$chks.each(function(){
				s.push($(this).val());
			});
			return s;
		})($('.domestic_vote_check:checked'));
		$.ajax({
			type: "POST",
			url: ajaxurl,
			beforeSend : function(){
				$('.domestic-vote-progress').fadeIn('slow');
			},
			data: {
				action: 'domestic_vote_regist',
				values: checked
			}
		})
		.done(function( data ) {
			$('.domestic-vote-progress').fadeOut('fast');
		});
	});

	// reset
	$('#domestic-vote-submit-reset').on('click',function(){
		if (confirm('<?php $DU->_("設定をリセットしてもよろしいですか?"); ?>')) {
			$.ajax({
				type: "POST",
				url: ajaxurl,
				beforeSend : function(){
					$('.domestic-vote-progress').fadeIn('slow');
				},
				data: {
					action: 'domestic_vote_reset',
				}
			})
			.done(function( data ) {
				$('.domestic-vote-progress').fadeOut('fast');

				var checked = (function($chks){
					$chks.each(function(){
						$(this)[0].checked = false;
					})
				})($('.domestic_vote_check:checked'));
			});
		};
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