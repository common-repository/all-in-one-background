<?php
/*
Plugin Name: All in one Background
Plugin URI: http://www.1efthander.com/category/plug-in/all-in-one-background
Description: <strong>All in one Background</strong>(Custom Background Images & Colors) : 포스트 / 페이지 등에 배경이미지와 색상을 지정할 수 있습니다. <strong>Features</strong> : 랜덤 슬라이드 메인 배경이미지, 특정일자의 배경이미지, 배경 패턴을 추가할 예정입니다.
Version: 0.9.1
Author: 1eftHander
Author URI: http://www.1efthander.com
*/

//Wordpress Tested up to : 3.4.2

define('AIOB_VERSION', '0.9.0');
define('CRLF', "\n");

//add_meta_boxes라는 hook에  add_aiob_meta_box 함수 등록. add_meta_boxes : Runs when "edit post" page loads. (3.0+)
add_action('add_meta_boxes', 'add_aiob_meta_box');

//admin_enqueue_scripts라는 hook에 load_css_js 함수 등록 
//js, css파일을 따로 등록했을 경우 반드시 처리해야 함
add_action('admin_enqueue_scripts', 'load_css_js');

//포스트 / 페이지 저장할 때마다 실행
add_action('save_post', 'save_aiob_meta_box');

//포스트 / 페이지 css
add_action('wp_head', 'add_aiob_css', 99999);

//ajax
add_action('wp_ajax_nopriv_select_aiob_media', 'select_aiob_media');
add_action('wp_ajax_select_aiob_media', 'select_aiob_media');

//post type별 add meta box
function add_aiob_meta_box()
{
	if ( function_exists('add_meta_box') )
	{
		add_meta_box(
			  'AIOB'
			, __('All in one Background')
			, 'aiob_meta_box'
			, null			//post|page|link|custom_post_type|null(current)
			, 'normal'		//advanced|normal|side
			, 'high'		//default|high|core|low
		);
	}
}

//aiob & farbtastic load
function load_css_js()
{
	wp_register_style('aiob', plugins_url('/all-in-one-background.css', __FILE__), false, AIOB_VERSION);
	wp_enqueue_style('aiob');

	wp_register_script('aiob', plugins_url('/all-in-one-background.js', __FILE__), false, AIOB_VERSION);
	wp_enqueue_script('aiob');
		
	wp_enqueue_script('farbtastic');
	wp_enqueue_style('farbtastic');
}

//aiob meta box save
function save_aiob_meta_box($post_id)
{
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

	update_post_meta($post_id, '_aiob_background',   $_REQUEST['_aiob_background']);
	update_post_meta($post_id, '_aiob_attachment',   $_REQUEST['_aiob_attachment']);
	update_post_meta($post_id, '_aiob_repeat',       $_REQUEST['_aiob_repeat']);
	update_post_meta($post_id, '_aiob_color_picker', $_REQUEST['_aiob_color_picker']);
	update_post_meta($post_id, '_aiob_image_id',    $_REQUEST['_aiob_image_id']);
	update_post_meta($post_id, '_aiob_image_url',    $_REQUEST['_aiob_image_url']);
}

//meta box layer
function aiob_meta_box()
{
	$meta = get_post_meta(get_the_ID());
	
	$attachment_id;
	$attach_info;
	$file_name;
	$file_type;
	$upload_date;
	$media_dims;
	$img_tag;
	if ( isset($meta['_aiob_image_id'][0]) && $meta['_aiob_image_id'][0] != '')
	{
		$attachment_id = $meta['_aiob_image_id'][0];
		$attach_info   = get_post($attachment_id);
		$file_name      = esc_html(basename($attach_info->guid));
		$file_type      = $attach_info->post_mime_type;
		$upload_date   = mysql2date( get_option('date_format'), $attach_info->post_date );
	
		$attach_meta = wp_get_attachment_metadata($attach_info->ID);
		if ( is_array($attach_meta)
				&& array_key_exists('width', $attach_meta)
				&& array_key_exists('height', $attach_meta) )
		{
			$media_dims .= "<span id='media-dims-$attach_info->ID'>{$attach_meta['width']}&nbsp;&times;&nbsp;{$attach_meta['height']}</span> ";
		}
		$media_dims = apply_filters('media_meta', $media_dims, $attach_info);
		
		$img_tag = '<img src="' . wp_get_attachment_thumb_url($attachment_id) .'" />';
	}
	
	echo CRLF . '<div id="aiob_background_header">';
	echo CRLF . '	<span class="a-iob-meta-box-header">Background</span>';
	echo CRLF . '	<div class="a-iob-meta-box-input">';
	echo CRLF . '		<label><input type="radio" name="_aiob_background" value="enabled"' . checked($meta['_aiob_background'][0], 'enabled', false) . ' />enabled</label>';
	echo CRLF . '		<label><input type="radio" name="_aiob_background" value="disabled"' . checked($meta['_aiob_background'][0], 'disabled', false) . ' />disabled</label>';
	echo CRLF . '	</div>';
	echo CRLF . '</div>';
	echo CRLF . '<div id="aiob_background_options">';
	echo CRLF . '	<div id="aiob_attachment" class="a-iob-background-options_line">';
	echo CRLF . '		<span class="a-iob-meta-box-header">Attachment</span>';
	echo CRLF . '		<div class="a-iob-meta-box-input">';
	echo CRLF . '			<label><input type="radio" name="_aiob_attachment" value="fixed"' . checked($meta['_aiob_attachment'][0], 'fixed', false) . ' />fixed</label>';
	echo CRLF . '			<label><input type="radio" name="_aiob_attachment" value="scroll"' . checked($meta['_aiob_attachment'][0], 'scroll', false) . ' />scroll</label>';
	echo CRLF . '		</div>';
	echo CRLF . '	</div>';
	echo CRLF . '	<div id="aiob_repeat" class="a-iob-background-options_line">';
	echo CRLF . '		<span class="a-iob-meta-box-header">Repeat</span>';
	echo CRLF . '		<div class="a-iob-meta-box-input">';
	echo CRLF . '			<label><input type="radio" name="_aiob_repeat" value="no-repeat"' . checked($meta['_aiob_repeat'][0], 'no-repeat', false) . ' />no-repeat</label>';
	echo CRLF . '			<label><input type="radio" name="_aiob_repeat" value="repeat"' . checked($meta['_aiob_repeat'][0], 'repeat', false) . ' />repeat</label>';
	echo CRLF . '			<label><input type="radio" name="_aiob_repeat" value="repeat-x"' . checked($meta['_aiob_repeat'][0], 'repeat-x', false) . ' />repeat-x</label>';
	echo CRLF . '			<label><input type="radio" name="_aiob_repeat" value="repeat-y"' . checked($meta['_aiob_repeat'][0], 'repeat-y', false) . ' />repeat-y</label>';
	echo CRLF . '		</div>';
	echo CRLF . '	</div>';
	echo CRLF . '	<div id="aiob_color" class="a-iob-background-options_line">';
	echo CRLF . '		<span class="a-iob-meta-box-header">Color</span>';
	echo CRLF . '		<div class="a-iob-meta-box-input">';
	echo CRLF . '			<input type="text" name="_aiob_color_picker" class="a-iob-color-picker" id="aiob_color_picker" value="' . $meta['_aiob_color_picker'][0] . '" />';
	echo CRLF . '			<input type="button" name="_aiob_color_clear" class="button" value="Clear" />';
	echo CRLF . '			<div id="color_picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>';
	echo CRLF . '		</div>';
	echo CRLF . '	</div>';
	echo CRLF . '	<div id="aiob_image" class="a-iob-background-options">';
	echo CRLF . '		<span class="a-iob-meta-box-header">Image</span>';
	echo CRLF . '		<div class="a-iob-meta-box-input">'; // wp-media-buttons
	echo CRLF . '			<input type="button" name="_aiob_image_add" id="aiob_image_add" class="button" value="Upload/Insert" />';
	echo CRLF . '			<input type="button" name="_aiob_image_clear" class="button" value="Clear" /><br />';
	echo CRLF . '			<input type="hidden" name="_aiob_image_id" value="' . $attachment_id . '" />';
	echo CRLF . '			<div id="attachment_info">';
	echo CRLF . '				<input type="text" name="_aiob_image_url" id="aiob_image_url" class="a-iob-image-url" value="' . $meta['_aiob_image_url'][0] . '" />';
	echo CRLF . '				<table>';
	echo CRLF . '					<tr valign="top">';
	echo CRLF . '						<td width="200">';
	echo CRLF . '							<p style="padding: 4px; border: 1px dashed #CCC; width: 150px; height: 150px;"><span id="aiob_thumnail">' . $img_tag . '</span></p>';
	echo CRLF . '						</td>';
	echo CRLF . '						<td>';
	echo CRLF . '							<p><strong>File name:</strong> <span id="aiob_file_name">' . $file_name . '</span></p>';
	echo CRLF . '							<p><strong>File type:</strong> <span id="aiob_file_type">' . $file_type . '</span></p>';
	echo CRLF . '							<p><strong>Upload date:</strong> <span id="aiob_upload_date">' . $upload_date . '</span></p>';
	echo CRLF . '							<p><strong>Dimensions:</strong> <span id="aiob_dimensions">'  . $media_dims . '</span></p>';
	echo CRLF . '						</td>';
	echo CRLF . '					</tr>';
	echo CRLF . '				</table>';
	echo CRLF . '			</div>';
	echo CRLF . '		</div>';
	echo CRLF . '	</div>';
	echo CRLF . '</div>';
	echo CRLF;
	echo CRLF;
}

function add_aiob_css()
{
	$meta = get_post_meta(get_the_ID());
	$display = true;

	if ( is_archive() )           $display = false; //archive, calendar
	if ( is_post_type_archive() ) $display = false;
	if ( is_attachment() )        $display = false;
	if ( is_author() )            $display = false; //author
	if ( is_category() )          $display = false; //category
	if ( is_tag() )               $display = false; //tag
	if ( is_tax() )               $display = false;
	if ( is_comments_popup() )    $display = false;
	if ( is_date() )              $display = false; //calendar
	if ( is_day() )               $display = false; //calendar
	if ( is_feed() )              $display = false;
	if ( is_comment_feed() )      $display = false;
	if ( is_front_page() )        $display = false; //home
	if ( is_home() )              $display = false; //home
	if ( is_month() )             $display = false; //calendar
	//if ( is_page() )              $display = true; //page
	if ( is_paged() )             $display = false;
	//if ( is_preview() )           $display = false; //preview Changes
	if ( is_robots() )            $display = false;
	if ( is_search() )            $display = false; //search
	//if ( is_single() )            $display = true; //post
	//if ( is_singular())           $display = false; //post, page
	if ( is_time() )              $display = false;
	if ( is_trackback() )         $display = false;
	if ( is_year() )              $display = false; //year
	if ( is_404() )               $display = false; //404
	
	if ( !isset($meta['_aiob_background'][0]) )         $display = false;
	if ( ($meta['_aiob_background'][0] == 'disabled') ) $display = false;
	
	if ( $display )
	{
		echo CRLF . '<style type="text/css">';
		echo CRLF . '	body {';
		
		if ( isset($meta['_aiob_image_url'][0]) && $meta['_aiob_image_url'][0] != '')
			echo CRLF . '		background-image: url(\'' . $meta['_aiob_image_url'][0] . '\');';
		
		if ( isset($meta['_aiob_color_picker'][0]) && $meta['_aiob_color_picker'][0] != '#')
			echo CRLF . '		background-color: ' . $meta['_aiob_color_picker'][0] . ';';

		echo CRLF . '		background-attachment: ' . $meta['_aiob_attachment'][0] . ';';
		echo CRLF . '		background-repeat: ' . $meta['_aiob_repeat'][0] . ';';
		echo CRLF . '	}';
		echo CRLF . '</style>';
		echo CRLF;
		echo CRLF;
	}
}

function select_aiob_media()
{
	$attachment_id = $_REQUEST['postId'];
	$attach_info   = get_post($attachment_id);
	$file_name     = esc_html(basename($attach_info->guid));
	$file_type     = $attach_info->post_mime_type;
	$upload_date   = mysql2date( get_option('date_format'), $attach_info->post_date );
	
	$attach_meta = wp_get_attachment_metadata($attach_info->ID);
	if ( is_array($attach_meta)
			&& array_key_exists('width', $attach_meta)
			&& array_key_exists('height', $attach_meta) )
	{
		$media_dims .= "<span id='media-dims-$attach_info->ID'>{$attach_meta['width']}&nbsp;&times;&nbsp;{$attach_meta['height']}</span> ";
	}
	$media_dims = apply_filters('media_meta', $media_dims, $attach_info);
	
	$img_tag = '<img src="' . wp_get_attachment_thumb_url($attachment_id) .'" />';
	
	$response = json_encode(array('file_name' => $file_name, 'file_type' => $file_type, 'upload_date' => $upload_date, 'media_dims' => $media_dims, 'img_tag' => $img_tag));
	
	header("Content-Type: application/json");
	
	echo $response;
	
	exit;
}
?>
