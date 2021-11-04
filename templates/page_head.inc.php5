<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?=$lm->GetString('SYSTEM', 'ID_COMPANY_NAME');?> - <?=$lm->GetString('SYSTEM', 'ID_APPLICATION_NAME');?></title>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
		<meta name="author" content="<?=$lm->GetString('SYSTEM', 'ID_COMPANY_NAME');?>" />
		<meta name="copyright" content="(c)<?=date("Y");?> <?=$lm->GetString('SYSTEM', 'ID_COMPANY_NAME');?>" />
		<meta name="robots" content="noindex" />
		<meta name="robots" content="nofollow" />
		<link rel="shortcut icon" href="<?=$SHARED_HTTP_ROOT?>favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" type="text/css" href="<?=$SHARED_HTTP_ROOT?>css/content.css" />
		<link rel='stylesheet' type='text/css' href='<?=$SHARED_HTTP_ROOT?>css/popcalendar.css' />
	<?	if (isset($_SESSION["currentUser"])){?>
			<? /* Mootools einbinden */?>
			<script src="<?=$SHARED_HTTP_ROOT?>scripts/mootools-core-1.4.5-full-compat.js" type="text/javascript"></script>
			<script src="<?=$SHARED_HTTP_ROOT?>scripts/mootools-more-1.4.0.1.js" type="text/javascript"></script>
			<script src="<?=$SHARED_HTTP_ROOT?>scripts/clientcide.js" type="text/javascript"></script>
			
			<?=$dynamicTableManager->GetReqirementHtmlString();?>
			<? /* Kalender einbinden */?>
			<?//if($MENU_SHOW != false){?>
				<script language='javascript' src='<?=$SHARED_HTTP_ROOT?>scripts/calendar.js'></script>
				<script language='javascript' src='<?=$SHARED_HTTP_ROOT?>scripts/popcalendar.js.php5?root=<?=urlencode($SHARED_HTTP_ROOT);?>'></script>
				<script language='javascript' src='<?=$SHARED_HTTP_ROOT?>scripts/popupcalendar_setup.js.php5'></script>
			<?//}?>
			<script type="text/javascript">
				Clientcide.setAssetLocation('<?=$SHARED_HTTP_ROOT?>/scripts/assets');
				window.onresize = function(){
					setContentHeight();
					calcMenueLayout();
				}
			</script>
			<script src="<?=$SHARED_HTTP_ROOT?>scripts/tiny_mce_3.5.8/tiny_mce.js" type="text/javascript"></script>
			<script type="text/javascript">
				tinyMCE.init({
						language : "de", 
						mode : "textareas", 
						theme : "advanced", 
						plugins : "table, paste",
						editor_selector : "mceSimple",
						theme_advanced_buttons1 : "bold,italic,underline,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,|,forecolor",        
						theme_advanced_buttons2 : "tablecontrols",
						theme_advanced_buttons3 : "",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_resizing : false,
						table_styles : "",
						table_cell_styles : "",
						table_row_styles : "",
						//paste_text_sticky: true,
						//paste_text_sticky_default: true,
						paste_postprocess : function(pl, o)
						{
							var ed = pl.editor, dom = ed.dom;
							
							tinymce.each(dom.select('*', o.node), function(el) 
							{   
								// Remove all tags which are not <p>, <br>, <table> ...
								if (el.tagName.toLowerCase() != "p" && 
									el.tagName.toLowerCase() != "br" && 
									el.tagName.toLowerCase() != "table"  && 
									el.tagName.toLowerCase() != "tr" && 
									el.tagName.toLowerCase() != "td" && 
									el.tagName.toLowerCase() != "th" && 
									el.tagName.toLowerCase() != "strong" && 
									el.tagName.toLowerCase() != "b"  && 
									el.tagName.toLowerCase() != "i") 
								{   
									dom.remove(el, 1); // 1 = KeepChildren
									console.log(el.tagName);
								}
								// Remove all attributes with the following names...
								dom.setAttrib(el, 'style', '');
								dom.setAttrib(el, 'cellspacing', '');
								dom.setAttrib(el, 'cellpadding', '');
								dom.setAttrib(el, 'border', '');
								// Set id 'wstable' to all table-tags to get a unique table layout
								if (el.tagName.toLowerCase() == "table")
								{
									dom.setAttrib(el, 'id', 'wstable');
								}
							});
						},
						valid_children : "-table[style|border|cellspacing|cellpadding],-tr[style],-td[div|p|style]",
						forced_root_block: false
				});
			</script>
			
			<script type="text/javascript" src="<?=$SHARED_HTTP_ROOT?>scripts/slimbox_with_iframe/lightbox.js"></script>
			<link id="LightboxCss" rel="stylesheet" media="screen" type="text/css" href="<?=$SHARED_HTTP_ROOT?>scripts/slimbox_with_iframe/slimbox.css" />
			<script type="text/javascript">
				var MOOTOOLS_LIGHTBOX = null;
				
				window.addEvent('domready', function() {
					MOOTOOLS_LIGHTBOX = new Lightbox();
				});
			</script>
			<?include("multiselect.js.php5");?>
			<?include("contextmenu.js.php5");?>
			<?include("listManagerURLBuilder.js.php5");?>
	<?	}?>
	</head>
