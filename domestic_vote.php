<?php
/*
Plugin Name: Domestic vote
Plugin URI: https://github.com/m-seino/Domestic-vote
Description: 
Version: 0.1
Author: Maiko Seino
Author URI: http://incr.jp
License: GPLv2
*/
require_once( ABSPATH . "wp-includes/pluggable.php" );
class DomesticvoteUtil {
	public function thisPluginUrl($mode = null , $id = null) {
		$baseUrl = admin_url().'options-general.php?page='.DomesticvoteControler::$plugin_fix.'/'.DomesticvoteControler::$plugin_fix.'.php';

		if(!is_null($mode)) {
			$baseUrl = $baseUrl.'&mode='.$mode;
		}
		if(!is_null($id)) {
			$baseUrl = $baseUrl.'&id='.$id;
		}
		return $baseUrl;
	}
	public function _echo($str) {
		echo (__($str));
	}
	public function _($str) {
		echo htmlspecialchars(__($str));
	}
	public function iz($val) {
		if(isset($val)) {
			DomesticvoteUtil::_($val);
		}
	}
}
class DomesticvoteValidator {
	public static $VALIDATE_TYPE_REQUIRE = 1;
	public static $VALIDATE_TYPE_NUMBER  = 2;
	public function validate($type = array(),$target, $before = '',$sepalate = '',$after ='',$disp_name = '') {
		if(count($_POST) == 0) {
			return;
		}
		$err_message = array();
		if (in_array(DomesticvoteValidator::$VALIDATE_TYPE_REQUIRE, $type)) {
			$target = trim($target);
			if( is_null($target) || $target == '' ) {
				$err_message[] = ($disp_name == '' ? '' : $disp_name.'は').'必ず入力してください。';
			}
		}
		if (in_array(DomesticvoteValidator::$VALIDATE_TYPE_NUMBER, $type)) {
			if (!preg_match("/^[0-9]+$/", $target)) {
				$err_message[] = ($disp_name == '' ? '' : $disp_name.'は').'数値で入力してください。';
			}
		}
		if(count($err_message) == 0) {
			return;
		}
		else {
			return $before.implode($sepalate, $err_message).$after ;
		}
	}
}
class DomesticvoteControler {

	public static $plugin_name = 'Domestic vote';
	public static $plugin_fix = 'domestic_vote';
	public static $table_name = 'domestic_vote_type';
	public static $sub_table_name = 'domestic_vote_popularcount';

	public function getMenuKey($menu_position) {
		global $wp_post_types;
		foreach ($wp_post_types as $key => $value) {
			if($value->menu_position == $menu_position) {
				return $key;
			}
		}
		return $menu_position;
	}
	public function DomesticvoteControler() {

		// ログインして無ければリクエストは受け付けない
		if(!is_user_logged_in()) {die('request faild.');}

		global $wpdb;
		define('DOMESTIC_VOTE_PLUGIN_TABLE_NAME', $wpdb->prefix . DomesticvoteControler::$table_name);
		define('DOMESTIC_VOTE_PLUGIN_COUNT_TABLE_NAME', $wpdb->prefix . DomesticvoteControler::$sub_table_name);

		add_action('admin_menu', 'domestic_vote_menu');
		function domestic_vote_menu() {
			add_options_page(__(DomesticvoteControler::$plugin_name), __(DomesticvoteControler::$plugin_name), 8, __FILE__, 'domestic_vote_options');
		}
		function domestic_vote_options() {
			if(isset($_GET['mode']) && $_GET['mode'] == 'add') {
				include_once 'domestic_vote-data-add.php';
			}
			else if(isset($_GET['mode']) && $_GET['mode'] == 'edit') {
				include_once 'domestic_vote-data-edit.php';
			}
			else if(isset($_GET['mode']) && $_GET['mode'] == 'info') {
				include_once 'domestic_vote-data-info.php';
			}
			else if(isset($_GET['mode']) && $_GET['mode'] == 'delete') {
				include_once 'domestic_vote-data-delete.php';
			}
			else {
				include_once 'domestic_vote-setting-page.php';
			}
		}
		function domestic_vote_regist_ajax_actions() {
			add_action( 'wp_ajax_domestic_vote_countup', 'domestic_vote_countup_callback' );
			function domestic_vote_countup_callback() {
				// Validation
				if(!isset($_POST['type_id']) || empty($_POST['type_id'])) {
					echo __('エラー：リクエストパラメータ type_id がありません。');
				}
				if(!isset($_POST['post_id']) || empty($_POST['post_id'])) {
					echo __('エラー：リクエストパラメータ post_id がありません。');
				}
				global $wpdb;

				$_query = 'SELECT sum(count) as count FROM '.DOMESTIC_VOTE_PLUGIN_COUNT_TABLE_NAME;
				$_where = ' WHERE type_id = %s AND post_id = %s ';

				$_prepare = '';
				if (!empty($_POST['unique_id'])) {
					$_where .= 'AND unique_id = %s';
			    	$_prepare = $wpdb->prepare(
			    		$_query.$_where,
			    		$_POST['type_id'],
			    		$_POST['post_id'],
			    		$_POST['unique_id']
			    	);
				}
				else {
			    	$_prepare = $wpdb->prepare(
			    		$_query.$_where,
			    		$_POST['type_id'],
			    		$_POST['post_id']
			    	);
				}

				$vote_record = $wpdb->get_results($_prepare);
				$result = '';
				if(empty($vote_record[0]->count)) {
					$result = $wpdb->query(
						$wpdb->prepare(
							'INSERT INTO '.DOMESTIC_VOTE_PLUGIN_COUNT_TABLE_NAME.' (type_id, post_id, count, unique_id) VALUES (%d,%d,1,%d)',
				    		$_POST['type_id'],
				    		$_POST['post_id'],
				    		$_POST['unique_id'] == '' ? '0' : $_POST['unique_id']
						)
					);
					echo "1";
				}
				else {
					$_count = $vote_record[0]->count + 1;

					if(($_POST['unique_id'] == '') || ($_POST['unique_id'] != '' && $_POST['allow_duplicate_count'] == 'true')) {
						$result = $wpdb->query(
							$wpdb->prepare(
								'UPDATE '.DOMESTIC_VOTE_PLUGIN_COUNT_TABLE_NAME.' SET count = %d '.$_where,
								$_count,
								$_POST['type_id'],
								$_POST['post_id'],
								$_POST['unique_id'] == '' ? '0' : $_POST['unique_id']
							)
						);
					}
					echo $_count;
				}
				die;
			}
			add_action( 'wp_ajax_domestic_vote_read', 'domestic_vote_read_callback' );
			function domestic_vote_read_callback() {
				global $wpdb;
				$_arr = array();

				$_arr['columns_id'] = array();
				$type_data = $wpdb->get_results(
					'SELECT id,name FROM '.DOMESTIC_VOTE_PLUGIN_TABLE_NAME.' ORDER BY id asc '
				);

				$_subQuerys = array();
				$_columns_str = '';
				$_where_arr = array();
				// 投票項目の取得
				foreach ($type_data as $key => $value) {
					$_columns_str .= 'count_'.$value->id.',';
					$_arr['columns_id'][] = $value->id;
					$_arr['columns_name'][] = $value->name;
					$_where_arr[] = 'count_'.$value->id.' IS NOT NULL';

					$_subQuerys[] = '
					(
						SELECT 
							post.ID as ID'.$value->id.', 
							SUM( count.count ) AS count_'.$value->id.'
						FROM 
							'.$wpdb->prefix.'posts AS post
						LEFT JOIN 
							'.DOMESTIC_VOTE_PLUGIN_COUNT_TABLE_NAME.' AS count 
						ON post.ID = count.post_id
						WHERE count.type_id = '.$value->id.'
						GROUP BY post.id
					) as sub'.$value->id.'
					';
				}

				// 投票内訳のクエリを生成
				$_stash = array();
				$_cnt =  count($_subQuerys);

				$i = 0;
				for (; $i < $_cnt; $i++) { 
					$_stash[] = $_subQuerys[$i];
					if(count($_stash) == 2) {
						$_temp = '';
						$_temp2 = '';
						$_temp = implode(' LEFT JOIN ', $_stash);
						$_temp .= '
							 ON sub'.$_arr['columns_id'][$i].'.ID'.$_arr['columns_id'][$i].
							 ' =  sub'.$_arr['columns_id'][$i-1].
							 '.ID'.$_arr['columns_id'][$i-1].' ';

						$_temp2 = implode(' RIGHT JOIN ', $_stash);
						$_temp2 .= '
							 ON sub'.$_arr['columns_id'][$i].'.ID'.$_arr['columns_id'][$i].
							 ' =  sub'.$_arr['columns_id'][$i-1].
							 '.ID'.$_arr['columns_id'][$i-1].' ';
						$_temp = 'SELECT * FROM ((SELECT * FROM '.$_temp.') UNION (SELECT * FROM '.$_temp2.')) as uni';

						$_temp = '('.$_temp.') as sub'.$_arr['columns_id'][$i];
						$_stash = array($_temp);
					}
				}


				$_joinQuery = array();
				$_subQueryName = 'sub'.$_arr['columns_id'][$i-1];
				foreach ($_arr['columns_id'] as $key => $value) {
					$_joinQuery[] = 'post.ID = '.$_subQueryName.'.ID'.$value;
				}
				$_query = '
				SELECT
					'.$_columns_str.'
					post.ID,
					post.post_title
				FROM
					'.$wpdb->prefix.'posts as post
				LEFT JOIN
					'.$_temp.'
				ON
					'.implode(' OR ', $_joinQuery).'
				WHERE 
					'.implode(' OR ', $_where_arr).'
				';

				$_arr['data'] = $type_data = $wpdb->get_results(
					$_query
				);
				echo json_encode($_arr);
				die;
			}
		}
		domestic_vote_regist_ajax_actions();

		function domestic_vote_init_database(){
			global $wpdb;
			$table_name = $wpdb->prefix . DomesticvoteControler::$table_name;
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "
					CREATE TABLE " . $table_name . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					name varchar(100) NOT NULL,
					UNIQUE KEY id (id)
				);";
				dbDelta($sql);
			}
			$sub_table_name = $wpdb->prefix . DomesticvoteControler::$sub_table_name;
			if($wpdb->get_var("SHOW TABLES LIKE '$sub_table_name'") != $sub_table_name) {
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "
					CREATE TABLE " . $sub_table_name . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					type_id bigint(20) NOT NULL,
					post_id bigint(20) NOT NULL,
					unique_id varcher(100) ,
					count bigint(20) NOT NULL,
					UNIQUE KEY id (id)
				);";
				dbDelta($sql);
			}
		}
		domestic_vote_init_database();

		function domestic_vote_shortcode($atts) {
			extract(shortcode_atts(array(
				'type_id' => '',
				'post_id' => '',
				'unique_id' => 'null',
				'html' => '',
				'show_view_count' => false,
				'allow_duplicate_count' => false,
				'class' => null
			), $atts));


			if($type_id == '') {
				echo __('ショートコードエラー ： type_idの指定がありません。');
				return;
			}
			if($post_id == '') {
				echo __('ショートコードエラー ： post_idの指定がありません。');
				return;
			}
			if($html == '') {
				echo __('ショートコードエラー ： htmlの指定がありません。');
				return;
			}

			$_tag = '<a href="#" data-type_id="{{type_id}}" data-post_id="{{post_id}}" data-allow_duplicate_count="{{allow_duplicate_count}}" data-unique_id="{{unique_id}}" class="domestic_vote_voting {{class}}">{{show_view_count}}{{html}}</a>';

			$_tag = str_replace('{{type_id}}', $type_id, $_tag);
			$_tag = str_replace('{{post_id}}', $post_id, $_tag);
			$_tag = str_replace('{{allow_duplicate_count}}', $allow_duplicate_count, $_tag);
			$_tag = str_replace('{{unique_id}}', $unique_id, $_tag);
			$_tag = str_replace('{{html}}', $html, $_tag);
			$_tag = str_replace('{{class}}', $class, $_tag);

			if($show_view_count) {
				$_query = 'SELECT SUM( count ) AS count FROM '.DOMESTIC_VOTE_PLUGIN_COUNT_TABLE_NAME.' WHERE type_id = '.$type_id.' AND post_id = '.$post_id.' GROUP BY post_id';
				global $wpdb;
				$_result = $wpdb->get_results( $_query );
				$_tag = str_replace('{{show_view_count}}', '<span class="dvote_count">'.$_result[0]->count.'</span>', $_tag);
			}
			else {
				$_tag = str_replace('{{show_view_count}}', '', $_tag);
			}

			return $_tag;
		}
		add_shortcode('dvote', 'domestic_vote_shortcode');

		function domestic_vote_insert_script() {
echo<<<EOL
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.1/jquery.min.js"></script>
<script type="text/javascript">
$(function(){
	// reset
	$('.domestic_vote_voting').on('click',function(e){
		var _self = $(this);
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			datatype : 'text',
			data: {
				'action': 'domestic_vote_countup',
				'type_id' : $(this).data('type_id'),
				'post_id' : $(this).data('post_id'),
				'allow_duplicate_count' : $(this).data('allow_duplicate_count'),
				'unique_id' : $(this).data('unique_id'),
			}
		})
		.done(function( data ) {
			_self.find('.dvote_count').text(data);
			console.log(data);
		});
	});
});
</script>
EOL;
		}
		add_action( 'wp_footer', 'domestic_vote_insert_script');

	}
}

$CA = new DomesticvoteControler();
$DU = new DomesticvoteUtil();
$DV = new DomesticvoteValidator();

?>

<?php //echo do_shortcode('[dvote type_id="1" post_id="1" class="icon_good" html="<span class=\'good\'></span>役に立った"]'); ?>

