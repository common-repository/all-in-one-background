(function($) {
	$(document).ready(function() {
		background_options();

		$("#aiob_color_picker").focus(function() {
			$(this).siblings("#color_picker").show();
			return false;
		}).blur(function() {
			$(this).siblings("#color_picker").hide();
			return false;
		});
		
		select_color_picker();

		$("input[name=_aiob_color_clear]").click(function() {
			$("input[name=_aiob_color_picker]").val("#");
			$("input[name=_aiob_color_picker]").removeAttr("style");
		});

		//Media Uploader
		var isMediaUpload = false;

		$("#aiob_image_add").click(function() {
			tb_show("Add Media", "media-upload.php?type=image&amp;TB_iframe=1");
			isMediaUpload = true;
			return false;
		});

		window.original_send_to_editor = window.send_to_editor;
		window.send_to_editor = function(html) {
			if (isMediaUpload)
			{
				var imgUrl = $("img", html).attr("src");
				var imgClass = $("img", html).attr("class");
				var imgId = parseInt(imgClass.replace(/\D/g, ""), 10);
				
				$("input[name=_aiob_image_url]").val(imgUrl);
				$("input[name=_aiob_image_id]").val(imgId);
				isMediaUpload = false;
				tb_remove();
				
				thumbnail_image(true);
			}
			else
			{
				window.original_send_to_editor(html);
			}
		}
		
		if ($("input[name=_aiob_image_url]").val() == "")
		{
			thumbnail_image(false);
		}
	});
	
	function thumbnail_image(val)
	{
		if (val)
		{
			var imgId = $("input[name=_aiob_image_id]").val();
			ajaxCall(imgId);
			$("#attachment_info").show(); //.slideDown("fast");
		}
		else
		{
			$("#attachment_info").hide(); //.slideUp("fast");
		}
	}

	function ajaxCall(val)
	{
		jQuery.post(
			ajaxurl,
			{
				action : "select_aiob_media",
				postId : val
			},
            function(response) {
				$("#aiob_thumnail").html(response.img_tag);
				$("#aiob_file_name").text(response.file_name);
				$("#aiob_file_type").text(response.file_type);
				$("#aiob_upload_date").text(response.upload_date);
				$("#aiob_dimensions").html(response.media_dims);
            }
        );
		return false;
	}

	function select_color_picker()
	{
		var $ = jQuery;
		$("#color_picker").each(function() {
			var $this = $(this),
				$input = $this.siblings("input[name=_aiob_color_picker]");
			
			// Make sure the value is displayed
			if (!$input.val())
				$input.val("#");

			$this.farbtastic($input);
		});
	}

	function background_options()
	{
		if ("enabled" != $("input[name=_aiob_background]:checked").val())
		{
			$("#aiob_background_options").hide();
		}

		$("input[name=_aiob_background]").click(function() {
			if ("enabled" == $(this).val())
			{
				$("#aiob_background_options").slideDown("fast");
			} 
			else
			{
				$("#aiob_background_options").slideUp("fast");
			}
		});

		if ("custom" != $("input[name=_aiob_posotion]:checked").val())
		{
			$("input[name=_aiob_posotion_custom_x]").attr("disabled", "disabled");
			$("input[name=_aiob_posotion_custom_y]").attr("disabled", "disabled");
		}

		$("input[name=_aiob_posotion]").click(function() {
			if ("custom" == $(this).val())
			{
				$(this).siblings(".a-iob-posotion-custom").removeAttr("disabled");
				$(this).siblings(".a-iob-posotion-custom").attr("maxlength", "6");
			} 
			else
			{
				$("input[name=_aiob_posotion_custom_x]").attr("disabled", "disabled");
				$("input[name=_aiob_posotion_custom_y]").attr("disabled", "disabled");
			}
		});
		
		$("input[name=_aiob_color_picker]").attr("maxlength", "7");
		
		$("input[name=_aiob_image_clear]").click(function() {
			$("input[name=_aiob_image_url]").attr("value", "");
			$("input[name=_aiob_image_id]").attr("value", "");
			thumbnail_image(false);
		});
	}
})(jQuery);
