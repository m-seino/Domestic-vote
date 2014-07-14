<?php
/*
Plugin Name: Simple Custom Vote
Plugin URI: https://github.com/m-seino/simple-custom-vote
Description: 
Version: 1.0
Author: Maiko Seino
Author URI: http://incr.jp
License: GPL2
*/
class SimpleCustomvoteUtil {
	public function thisPluginUrl($mode = null , $id = null) {
		$baseUrl = admin_url().'options-general.php?page='.SimpleCustomvoteControler::$plugin_fix.'/'.SimpleCustomvoteControler::$plugin_fix.'.php';

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
			SimpleCustomvoteUtil::_($val);
		}
	}
}
class SimpleCustomvoteValidator {
	public static $VALIDATE_TYPE_REQUIRE = 1;
	public static $VALIDATE_TYPE_NUMBER  = 2;
	public function validate($type = array(),$target, $before = '',$sepalate = '',$after ='',$disp_name = '') {
		if(count($_POST) == 0) {
			return;
		}
		$err_message = array();
		if (in_array(SimpleCustomvoteValidator::$VALIDATE_TYPE_REQUIRE, $type)) {
			$target = trim($target);
			if( is_null($target) || $target == '' ) {
				$err_message[] = ($disp_name == '' ? '' : $disp_name.'は').'必ず入力してください。';
			}
		}
		if (in_array(SimpleCustomvoteValidator::$VALIDATE_TYPE_NUMBER, $type)) {
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
class SimpleCustomvoteControler {

	public static $plugin_name = 'Simple Custom Vote';
	public static $plugin_fix = 'simple_custom_vote';
	public static $table_name = 'simple_custom_vote_type';
	public static $sub_table_name = 'simple_custom_vote_popularcount';

	public function SimpleCustomvoteControler() {

		global $wpdb;
		define('SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME', $wpdb->prefix . SimpleCustomvoteControler::$table_name);
		define('SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME', $wpdb->prefix . SimpleCustomvoteControler::$sub_table_name);

		add_action('admin_menu', 'simple_custom_vote_menu');
		function simple_custom_vote_menu() {
			add_options_page(__(SimpleCustomvoteControler::$plugin_name), __(SimpleCustomvoteControler::$plugin_name), 8, __FILE__, 'simple_custom_vote_options');
		}
		function simple_custom_vote_options() {
			if(isset($_GET['mode']) && $_GET['mode'] == 'add') {
				include_once 'simple_custom_vote-data-add.php';
			}
			else if(isset($_GET['mode']) && $_GET['mode'] == 'edit') {
				include_once 'simple_custom_vote-data-edit.php';
			}
			else if(isset($_GET['mode']) && $_GET['mode'] == 'info') {
				include_once 'simple_custom_vote-data-info.php';
			}
			else if(isset($_GET['mode']) && $_GET['mode'] == 'delete') {
				include_once 'simple_custom_vote-data-delete.php';
			}
			else {
				include_once 'simple_custom_vote-setting-page.php';
			}
		}
		function simple_custom_vote_regist_ajax_actions() {
			add_action( 'wp_ajax_simple_custom_vote_countup', 'simple_custom_vote_countup_callback' );
			add_action( 'wp_ajax_nopriv_simple_custom_vote_countup', 'simple_custom_vote_countup_callback' );
			function simple_custom_vote_countup_callback() {
				// Validation
				if(!isset($_POST['type_id']) || empty($_POST['type_id'])) {
					echo __('エラー：リクエストパラメータ type_id がありません。');
				}
				if(!isset($_POST['post_id']) || empty($_POST['post_id'])) {
					echo __('エラー：リクエストパラメータ post_id がありません。');
				}
				global $wpdb;

				$_query = 'SELECT sum(count) as count FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME;
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
							'INSERT INTO '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME.' (type_id, post_id, count, unique_id) VALUES (%d,%d,1,%d)',
				    		$_POST['type_id'],
				    		$_POST['post_id'],
				    		$_POST['unique_id'] == '' ? '0' : $_POST['unique_id']
						)
					);
					echo "1";
				}
				else {
					$_count = $vote_record[0]->count;

					if(($_POST['unique_id'] == '') || ($_POST['unique_id'] != '' && $_POST['allow_duplicate_count'] == 'true')) {
						$result = $wpdb->query(
							$wpdb->prepare(
								'UPDATE '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME.' SET count = %d '.$_where,
								++$_count,
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
			add_action( 'wp_ajax_simple_custom_vote_read', 'simple_custom_vote_read_callback' );
			add_action( 'wp_ajax_nopriv_simple_custom_vote_read', 'simple_custom_vote_read_callback' );
			function simple_custom_vote_read_callback() {
				global $wpdb;
				$_arr = array();

				$_arr['columns_id'] = array();
				$type_data = $wpdb->get_results(
					'SELECT id,name FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.' ORDER BY id asc '
				);

				if(count($type_data) == 0) {
					echo "{}";
					die;
				}

				$_sub_querys = array();
				$_columns_str = '';
				$_where_arr = array();
				// 投票項目の取得
				foreach ($type_data as $key => $value) {
					$_columns_str .= 'count_'.$value->id.',';
					$_arr['columns_id'][] = $value->id;
					$_arr['columns_name'][] = $value->name;
					$_where_arr[] = 'count_'.$value->id.' IS NOT NULL';

					$_sub_querys[] = '
					(
						SELECT 
							post.ID as ID'.$value->id.', 
							SUM( count.count ) AS count_'.$value->id.'
						FROM 
							'.$wpdb->prefix.'posts AS post
						LEFT JOIN 
							'.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME.' AS count 
						ON post.ID = count.post_id
						WHERE count.type_id = '.$value->id.'
						GROUP BY post.id
					) as sub'.$value->id.'
					';
				}

				// 投票内訳のクエリを生成
				$_stash = array();
				$_cnt =  count($_sub_querys);

				$i = 0;
				for (; $i < $_cnt; $i++) { 
					$_stash[] = $_sub_querys[$i];
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


				$_join_query = array();
				$_sub_query_name = 'sub'.$_arr['columns_id'][$i-1];
				foreach ($_arr['columns_id'] as $key => $value) {
					$_join_query[] = 'post.ID = '.$_sub_query_name.'.ID'.$value;
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
					'.implode(' OR ', $_join_query).'
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
		simple_custom_vote_regist_ajax_actions();

		function simple_custom_vote_init_database(){
			global $wpdb;
			$table_name = $wpdb->prefix . SimpleCustomvoteControler::$table_name;
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "
					CREATE TABLE " . $table_name . " (
					id int(20) NOT NULL AUTO_INCREMENT,
					name varchar(100) NOT NULL,
					UNIQUE KEY id (id)
				);";
				dbDelta($sql);

				// サンプル用データ
				$wpdb->query(
					$wpdb->prepare('INSERT INTO '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.'(name) VALUES (%s);','sample_1')
				);
				$wpdb->query(
					$wpdb->prepare('INSERT INTO '.SIMPLE_CUSTOM_VOTE_PLUGIN_TABLE_NAME.'(name) VALUES (%s);','sample_2')
				);
			}
			$sub_table_name = $wpdb->prefix . SimpleCustomvoteControler::$sub_table_name;
			if($wpdb->get_var("SHOW TABLES LIKE '$sub_table_name'") != $sub_table_name) {
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "
					CREATE TABLE " . $sub_table_name . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					type_id bigint(20) NOT NULL,
					post_id bigint(20) NOT NULL,
					unique_id varchar(100) ,
					count bigint(20) NOT NULL,
					UNIQUE KEY id (id)
				);";
				dbDelta($sql);
			}

		}
		simple_custom_vote_init_database();

		function simple_custom_vote_shortcode($atts) {
			extract(shortcode_atts(array(
				'type_id' => '',
				'post_id' => '',
				'unique_id' => 'null',
				'html' => '',
				'show_view_count' => false,
				'allow_duplicate_count' => false,
				'on_duplicate_html' => '',
				'callback' => '',
				'id' => '',
				'class' => ''
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
			if($allow_duplicate_count == 'true' && $unique_id == '') {
				echo __('ショートコードエラー ： allow_duplicate_countはunique_idの指定が必要です。');
				return;
			}

			$_tag = '<a href="#" {{id}} data-type_id="{{type_id}}" data-post_id="{{post_id}}" data-allow_duplicate_count="{{allow_duplicate_count}}" data-unique_id="{{unique_id}}" data-callback="{{callback}}" class="simple_custom_vote_voting {{class}}">{{show_view_count}}{{html}}</a>';

			if ($id != '') {
				$id = 'id="'.$id.'"';
			}

			$_tag = str_replace('{{type_id}}', $type_id, $_tag);
			$_tag = str_replace('{{post_id}}', $post_id, $_tag);
			$_tag = str_replace('{{allow_duplicate_count}}', $allow_duplicate_count, $_tag);
			$_tag = str_replace('{{unique_id}}', $unique_id, $_tag);
			$_tag = str_replace('{{html}}', $html, $_tag);
			$_tag = str_replace('{{class}}', $class, $_tag);
			$_tag = str_replace('{{id}}', $id, $_tag);
			$_tag = str_replace('{{callback}}', $callback, $_tag);


			$_query = 'SELECT SUM( count ) AS count FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME.' WHERE type_id = '.$type_id.' AND post_id = '.$post_id.' GROUP BY post_id';
			global $wpdb;
			$_result = $wpdb->get_results( $_query );

			$_count = isset($_result[0]) ? $_result[0]->count : 0;

			if($show_view_count == 'true') {
				$_tag = str_replace('{{show_view_count}}', '<span class="scvote_count">'.$_count.'</span>', $_tag);
			}
			else {
				$_tag = str_replace('{{show_view_count}}', '', $_tag);
			}

			$_tag = str_replace('{{count}}', is_null($_count) ? '0' : $_count , $_tag);

			return $_tag;
		}
		add_shortcode('scvote', 'simple_custom_vote_shortcode');

// FIXME
		function simple_custom_vote_insert_script() {
			$_admin_ajax_url = admin_url('admin-ajax.php');
echo<<<EOL
<script type="text/javascript">
jQuery(function( $ ) {
	$('.simple_custom_vote_voting').on('click',function(e){
		var _self = $(this);
		var _callback = $(this).data('callback');
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: '$_admin_ajax_url',
			datatype : 'text',
			data: {
				'action': 'simple_custom_vote_countup',
				'type_id' : $(this).data('type_id'),
				'post_id' : $(this).data('post_id'),
				'allow_duplicate_count' : $(this).data('allow_duplicate_count'),
				'unique_id' : $(this).data('unique_id'),
			}
		})
		.done(function( data ) {
			_self.find('.scvote_count').text(data);
			eval(_callback);
		});
	});
});
</script>
EOL;
		}
		//add_action( 'wp_footer', 'simple_custom_vote_insert_script');

		function simple_custom_vote_admin_scripts() {
			wp_enqueue_script( 'wp_enqueue_scripts', plugins_url( '/js/'.SimpleCustomvoteControler::$plugin_fix.'.js' , __FILE__ ), array( 'jquery' ), '20140714', true );
		}
		function my_styles() {
			wp_enqueue_style( 'simple-custom-vote-style', plugins_url( '/css/'.SimpleCustomvoteControler::$plugin_fix.'.css' , __FILE__ ), array( SimpleCustomvoteControler::$plugin_fix ) ,false);
		}

		add_action( 'admin_enqueue_scripts' , 'simple_custom_vote_admin_scripts');
		add_action( 'wp_enqueue_scripts' , 'simple_custom_vote_insert_script');
		add_action( 'wp_enqueue_scripts' , 'my_styles');
	}

	public static function getVoteCount($post_id, $type_id = null) {
		global $wpdb;
		$_query = 'SELECT sum(count) as count FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME;
		$_vote_data = null;
		if (is_null($type_id)) {
			$_vote_data = $wpdb->get_results(
				$wpdb->prepare(
					$_query.' WHERE post_id = %d '
				,$post_id )
			);
		}
		else {
			$_vote_data = $wpdb->get_results(
				$wpdb->prepare(
					$_query.' WHERE post_id = %d AND type_id = %d'
				,$post_id ,$type_id)
			);
		}

		if(!is_null($_vote_data) && count($_vote_data) > 0) {
			return $_vote_data[0]->count;
		}
		else {
			throw new Exception("Error", 1);
		}
	}

	public static function isExistVoteByUniqueId($post_id, $unique_id, $type_id = null) {
		if( !isset($post_id) || empty($post_id) || is_null($post_id) ||
			!isset($unique_id) || empty($unique_id) || is_null($unique_id) ) {
			echo __('関数エラー ： 引数の指定に誤りがあります。未設定・空文字・NULL値の可能性があります。');
		}

		global $wpdb;
		$_vote_data = null;
		$_query = 'SELECT id FROM '.SIMPLE_CUSTOM_VOTE_PLUGIN_COUNT_TABLE_NAME;
		if (is_null($type_id)) {
			$_vote_data = $wpdb->get_results(
				$wpdb->prepare(
					$_query.' WHERE post_id = %d AND unique_id = %d'
				,$post_id ,$unique_id)
			);
		}
		else {
			$_vote_data = $wpdb->get_results(
				$wpdb->prepare(
					$_query.' WHERE post_id = %d AND unique_id = %d AND type_id = %d'
				,$post_id ,$unique_id, $type_id)
			);
		}
		if(!is_null($_vote_data) && count($_vote_data) > 0) {
			return true;
		}
		else {
			return false;
		}
	}

}

$simple_custom_vote_controler = new SimpleCustomvoteControler();
$simple_custom_vote_util = new SimpleCustomvoteUtil();
$simple_custom_vote_validator = new SimpleCustomvoteValidator();
