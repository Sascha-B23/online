<?php
/**
 * ListData-Implementierung für den Bereicht Standortvergleich Prozessstatus 
 * 
 * @access   	public
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */ 

error_reporting(E_ERROR | E_PARSE);

$versionDir = "html2ps_v2043";
require_once(dirname(__FILE__).'/'.$versionDir.'/config.inc.php');
require_once(HTML2PS_DIR.'pipeline.factory.class.php');

//error_reporting(E_ALL);
//ini_set("display_errors","1");
ini_alter("pcre.backtrack_limit",10000000);
ini_alter("pcre.recursion_limit",10000000);
ini_alter("memory_limit","1024M");
ini_alter("max_execution_time","1000");

parse_config_file(HTML2PS_DIR.'html2ps.config');

class MyDestinationMemory extends Destination {

	protected $pdfData=null;

	function MyDestinationMemory() {
	}

	function process($tmp_filename, $content_type) {
		$this->pdfData = file_get_contents($tmp_filename);
	}
	
	function GetData(){
		return $this->pdfData;
	}
}

class MyFetcherMemory extends Fetcher {
	var $base_path;
	var $content;
	var $allowedReplaceFiles = Array("jpeg", "jpg", "gif", "png");

	function MyFetcherMemory($content, $base_path) {
		$this->content = $content;
		$this->base_path = $base_path;
	}

	function get_data($url) {
		global $SHARED_FILE_SYSTEM_ROOT;
		if (!$url) {
			return new FetchedDataURL($this->content, array(), "");
		} else {
			// BasePath-Pfade auf lokale Pfade umbiegen und dann einlesen (behebt Probleme bei SSL-Verbindungen)
			$urlParts=explode(".", $url);
			if( in_array( strtolower($urlParts[count($urlParts)-1]), $this->allowedReplaceFiles) )$url=str_replace($this->base_path, $SHARED_FILE_SYSTEM_ROOT, $url);
			//echo $url."<br />";
			return new FetchedDataURL(@file_get_contents($url), array(), "");
		}
	}

	function get_base_url() {
		return $this->base_path;
	}
}

/**
 * 
 * INFO: Use XDEBUG_PROFILE as GET-Param to profile via XDEBUG
 * @global type $SHARED_HTTP_ROOT
 * @global type $g_config
 * @param string $html	HTML to convert to PDF
 * @param string $paper_format	PDF target format (A4, A3 ...)
 * @param int $width_in_px	(source page with in pixel)
 * @param bool $querformat	
 * @param string $base_path
 * @param bool $smartpagebreak
 * @param bool $mergin
 * @param string $header_html
 * @param string $footer_html
 * @return string 
 */
function convert_to_pdf($html, $paper_format="A4", $width_in_px=1024, $querformat=false, $base_path='', $smartpagebreak = false, $mergin = false, $header_html = "", $footer_html = "" ) {
	$pipeline = PipelineFactory::create_default_pipeline('', // Attempt to auto-detect encoding
													   '');
	// HTTP_ROOT als BasePath verwenden
	global $SHARED_HTTP_ROOT;
	if( trim($base_path)=='' )$base_path=$SHARED_HTTP_ROOT;
	// Override HTML source 
	// @TODO: default http fetcher will return null on incorrect images 
	// Bug submitted by 'imatronix' (tufat.com forum).
	$pipeline->fetchers[0] = new MyFetcherMemory($html, $base_path);

	// Override destination to local file
	$pipeline->destination = new MyDestinationMemory();

	$baseurl = '';

	$media =& Media::predefined($paper_format);

	// true = Querformat 
	// false = Hochformat
	$media->set_landscape($querformat);
	
	$media->set_margins($mergin==false ? array('left' => 5, 'right' => 5, 'top' => 5, 'bottom' => 5) : $mergin);
							
	$media->set_pixels($width_in_px);

	global $g_config;
	$g_config = array(
					'cssmedia'     => 'screen',
					'scalepoints'  => '1',
					'renderimages' => true,
					'renderlinks'  => true,
					'renderfields' => true,
					'renderforms'  => false,
					'mode'         => 'html',
					'encoding'     => 'utf-8',
					'debugbox'     => false,
					'pdfversion'    => '1.4',
					'draw_page_border' => false,
					//'method' => 'pdflib',
					//'html2xhtml'  => false,
					//'smartpagebreak' => $smartpagebreak,
					);
	// Header & Footer ?
	if ($header_html!="" || $footer_html!="" )
	{
		$filter = new PreTreeFilterHeaderFooter($header_html, $footer_html);
		$pipeline->pre_tree_filters[] = $filter;
		$pipeline->pre_tree_filters[] = new PreTreeFilterHTML2PSFields();
	}
	
	$pipeline->configure($g_config);
	//$pipeline->output_driver = new OutputDriverPDFLIB16($GLOBALS['g_config']['pdfversion']);
	
	//$startTime = time();
	$pipeline->process($baseurl, $media);
	//echo "Fn process_batch() total time: ".(time()-$startTime)."s<br />";
	//echo $html;
	//exit;
	
	return $pipeline->destination->GetData();
}

?>