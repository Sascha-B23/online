<?php
/***************************************************************************
 * Text-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class TextElement extends FormElement {
		
	protected $dynamicContent = null;
	protected $buttons = Array();
	protected $additionalElemnts = Array();
	protected $placeholder ="";
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function TextElement($id, $name, $value, $required=false, $error="", $readonly=false, $dynamicContent=null, $buttons=Array(), $additionalElemnts=Array(), $placeholder="" ){
		parent::__construct($id, $name, $value, $required, $error, $readonly);
		$this->dynamicContent=$dynamicContent;
		$this->buttons=$buttons;
		$this->additionalElemnts = $additionalElemnts;
		$this->placeholder = $placeholder;
		for( $a=0; $a<count($this->buttons); $a++){
			$this->width-=(int)$this->buttons[$a]["width"];
		}
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		global $DOMAIN_HTTP_ROOT;
		?>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="left" valign="middle">
					<input type="text" id="<?=$this->id;?>" name="<?=$this->id;?>" value="<?=$this->value;?>" class="TextForm" style="width: <?=$this->width;?>px;" <?if($this->readonly)echo "disabled";?> <?if ($this->placeholder!=''){?>placeholder="<?=$this->placeholder;?>"<?}?>>
					<?	foreach ($this->additionalElemnts as $element)
						{
							$element->PrintElement();
						}
					?>
				</td>
			<?	for( $a=0; $a<count($this->buttons); $a++){
					?><td align="left" valign="middle"><a href="<?=$this->buttons[$a]["href"];?>"><img src="<?=$DOMAIN_HTTP_ROOT.$this->buttons[$a]["pic"];?>" width="<?=$this->buttons[$a]["width"];?>px" alt="<?=$this->buttons[$a]["help"];?>" title="<?=$this->buttons[$a]["help"];?>" border="0"></a></td><?
				}?>
			</tr>
		</table>
		<?
		// Inhalt dynamisch laden?
		if( $this->dynamicContent!=null ){ 
			$this->dynamicContent->PrintElement();
		}
	}
		
	/***************************************************************************
	* Gibt den Text als Integer-Wert zurück
	* @return bool/int Gibt im Erfolgsfall den Integer oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function GetInteger($string){
		return HelperLib::ConvertStringToInteger($string);
	}
	
	/***************************************************************************
	* Gibt den Text als Integer-Wert zurück
	* @return bool/int Gibt im Erfolgsfall den Integer oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function GetFloat($string){
		return HelperLib::ConvertStringToFloat($string);
	}
	
} // TextElement

/***************************************************************************
 * Passwort-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class PwdElement extends FormElement {
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		?>
		<input type="password" id="<?=$this->id;?>" name="<?=$this->id;?>" value="<?=$this->value;?>" class="TextForm" style="width: <?=$this->width;?>px;" <?if($this->readonly)echo "disabled";?>>
		<?
	}
	
} // TextElement

/***************************************************************************
 * Passwort-Element mit Passwortgenerierung
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class PwdElement2 extends FormElement {
	
	/***************************************************************************
	 * Readonly
	 * @var bool
	 * @access protected
	 **************************************************************************/
	protected $showPWD = true;
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function PwdElement2($id, $name, $value, $required=false, $error="", $readonly=false, $showPWD=true ){
		$this->showPWD=$showPWD;
		parent::__construct($id, $name, $value, $required, $error, $readonly);
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		?>
		<input type="password" id="<?=$this->id;?>" name="<?=$this->id;?>" value="<?=$this->value;?>" class="TextForm" style="width: <?=$this->width;?>px;" <?if($this->readonly)echo "disabled";?> onChange="document.getElementById('<?=$this->id;?>_genpwd').style.visibility = 'hidden';"><br>
		<a href="javascript:document.forms.FM_FORM.<?=$this->id;?>_gen.value='true'; document.forms.FM_FORM.submit();">Passwort generieren</a>&#160;&#160;&#160;<span id="<?=$this->id;?>_genpwd" name="<?=$this->id;?>_genpwd"><?if($this->showPWD)echo $this->value;?></span>
		<input type="hidden" id="<?=$this->id;?>_gen" name="<?=$this->id;?>_gen" value="">
		<?
	}
	
} // TextElement

/***************************************************************************
 * PLZ und Ort-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class ZIPCityElement extends FormElement 
{
	protected $placeholderZip ="";
	protected $placeholderStreet ="";
	
	public function ZIPCityElement($id, $name, $value, $required=false, $error="", $readonly=false, $placeholderZip="", $placeholderStreet="" )
	{
		$this->placeholderZip = $placeholderZip;
		$this->placeholderStreet = $placeholderStreet;
		parent::__construct($id, $name, $value, $required, $error, $readonly);
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		?>
		<input type="text" id="<?=$this->id;?>_zip" name="<?=$this->id;?>_zip" value="<?=$this->value["zip"];?>" class="TextForm" style="width: <?=floor($this->width*0.3);?>px;" <?if($this->readonly)echo "disabled";?> <?if ($this->placeholderZip!=''){?>placeholder="<?=$this->placeholderZip;?>"<?}?>>&#160;/&#160;<input type="text" id="<?=$this->id;?>_city" name="<?=$this->id;?>_city" value="<?=$this->value["city"];?>" class="TextForm" style="width: <?=floor($this->width*0.67);?>px;" <?if($this->readonly)echo "disabled";?> <?if ($this->placeholderStreet!=''){?>placeholder="<?=$this->placeholderStreet;?>"<?}?>>
		<?
	}
	
} // ZIPCityElement

/**
 * TextArea-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TextAreaElement extends FormElement {
		
	/**
	 * Use MCE
	 * @var bool
	 */
	protected $useMCE = true;

	/**
	 * Konstruktor
	 * @param string	$name	Name des Elements
	 * @param string	$value	Wert des Elements
	 * @param bool	$required	Pflichtfeld
	 * @access public
	 */
	public function TextAreaElement($id, $name, $value, $required=false, $error="", $readonly=false, $useMCE=false ){
		$this->useMCE=$useMCE;
		parent::__construct($id, $name, $value, $required, $error, $readonly);
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		if ($this->useMCE && $this->readonly)
		{
			?><div style="width: <?=$this->width;?>px; height: <?=$this->height;?>px; border: solid #BCBCBC 1px; background-color: #E5E5E5 ;overflow: scroll;"><?=$this->value;?></div><?
			?><textarea id="<?=$this->id;?>" name="<?=$this->id;?>" style="visibility: hidden;"><?=$this->value;?></textarea><?
		}
		else
		{
			?><textarea id="<?=$this->id;?>" name="<?=$this->id;?>" <?if ($this->useMCE){?>class="mceSimple"<?}else{?>class="TextForm"<?}?>  style="width: <?=$this->width;?>px; height: <?=$this->height;?>px;" <? if($this->readonly)echo "disabled";?>><?=$this->value;?></textarea><?
		}
	}
	
} // TextAreaElement

/***************************************************************************
 * Dropdown-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class DropdownElement extends FormElement {
	
	protected $options = Array();
	protected $dynamicContent = null;
	protected $buttons = Array();
	protected $script = "";
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param mixed	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function DropdownElement($id, $name, $value, $required=false, $error="", $options=Array(), $multiselect=false, $dynamicContent=null, $buttons=Array(), $readonly=false, $script = ""){
		parent::__construct($id, $name, $value, $required, $error, $readonly);
		$this->options=$options;
		$this->multiselect=$multiselect;
		$this->height = $this->multiselect===true ? 75 : 0;
		$this->dynamicContent = $dynamicContent;
		$this->buttons = $buttons;
		$this->script = trim($script);
		for( $a=0; $a<count($this->buttons); $a++){
			$this->width-=(int)$this->buttons[$a]["width"];
		}
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		global $DOMAIN_HTTP_ROOT;
		?>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="left" valign="middle">
					<select id="<?=$this->id;?><?if($this->multiselect)echo "[]";?>" name="<?=$this->id;?><?if($this->multiselect)echo "[]";?>" class="SelectForm" style="width: <?=$this->width;?>px; <?if($this->height>0){?>height:<?=$this->height;?>px;<?}?>" <?if($this->multiselect)echo "multiple=\"multiple\"";?> <?if($this->readonly)echo " disabled";?>>
					<?	for($a=0; $a<count($this->options); $a++){?>
							<option value="<?=$this->options[$a]["value"];?>" <? if( $this->multiselect && in_array($this->options[$a]["value"], $this->value) || (!$this->multiselect && $this->options[$a]["value"]==$this->value) )echo "selected";?>><?=$this->options[$a]["name"];?></option>
					<? 	}?>
					</select>
				</td>
				<? if(!$this->multiselect){
				for( $a=0; $a<count($this->buttons); $a++){
					?><td align="left" valign="middle"><a href="<?=$this->buttons[$a]["href"];?>"><img src="<?=$DOMAIN_HTTP_ROOT.$this->buttons[$a]["pic"];?>" width="<?=$this->buttons[$a]["width"];?>px" alt="<?=$this->buttons[$a]["help"];?>" title="<?=$this->buttons[$a]["help"];?>" border="0"></a></td><?
				}}?>
			</tr>
		<? 	if($this->multiselect && count($this->buttons)>0){ ?>
				<tr>
					<td align="right">
				<?	for( $a=0; $a<count($this->buttons); $a++){?>
						<a href="<?=$this->buttons[$a]["href"];?>"><img src="<?=$DOMAIN_HTTP_ROOT.$this->buttons[$a]["pic"];?>" width="<?=$this->buttons[$a]["width"];?>px" alt="<?=$this->buttons[$a]["help"];?>" title="<?=$this->buttons[$a]["help"];?>" border="0"></a>
				<?	}?>
					</td>
				</tr>
		<? 	}?>
		</table>
	<?	// Inhalt dynamisch laden?
		if( $this->dynamicContent!=null ){ 
			$this->dynamicContent->PrintElement();
		}
		if ($this->script!=""){
			?><script type="text/javascript"><?
			echo $this->script;
			?></script><?
		}
	}
	
} // DropdownElement

/***************************************************************************
 * Text und Dropdown-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class TextAndDropdownElement extends DropdownElement {

	protected $valueText="";

	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function TextAndDropdownElement($id, $name, $valueText, $valueDropdown, $required=false, $error="", $options=Array(), $multiselect=false, $dynamicContent=null, $buttons=Array(), $readonly=false ){
		$this->valueText=$valueText;
		parent::__construct($id, $name, $valueDropdown, $required, $error, $options, $multiselect, $dynamicContent, $buttons, $readonly);
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		global $DOMAIN_HTTP_ROOT;
		?>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="left" valign="middle">
					<input type="text" id="<?=$this->id;?>" name="<?=$this->id;?>" value="<?=$this->valueText;?>" class="TextForm" style="width: <?=$this->width*0.6;?>px;" <?if($this->readonly)echo "disabled";?>>&#160;
				</td>
				<td align="left" valign="middle">
					<select id="<?=$this->id;?>_dd<?if($this->multiselect)echo "[]";?>" name="<?=$this->id;?>_dd<?if($this->multiselect)echo "[]";?>" class="SelectForm" style="width: <?=$this->width*0.4;?>px;" <?if($this->multiselect)echo "multiple=\"multiple\"";?> <?if($this->readonly)echo " disabled";?>>
					<?	for($a=0; $a<count($this->options); $a++){?>
							<option value="<?=$this->options[$a]["value"];?>" <? if( $this->multiselect && in_array($this->options[$a]["value"], $this->value) || !$this->multiselect && $this->options[$a]["value"]==$this->value )echo "selected";?>><?=$this->options[$a]["name"];?></option>
					<? 	}?>
					</select>
				</td>
				<?
				for( $a=0; $a<count($this->buttons); $a++){
					?><td align="left" valign="middle"><a href="<?=$this->buttons[$a]["href"];?>"><img src="<?=$DOMAIN_HTTP_ROOT.$this->buttons[$a]["pic"];?>" width="<?=$this->buttons[$a]["width"];?>px" alt="<?=$this->buttons[$a]["help"];?>" title="<?=$this->buttons[$a]["help"];?>" border="0"></a></td><?
				}?>
			</tr>
		</table>
	<?	// Inhalt dynamisch laden?
		if( $this->dynamicContent!=null ){ 
			$this->dynamicContent->PrintElement();
		}
	}
	
} // TextAndDropdownElement



/***************************************************************************
 * Dropdown-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class RadioButtonElement extends DropdownElement {
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		for($a=0; $a<count($this->options); $a++){
			?><input type="radio" id="<?=$this->id;?>" name="<?=$this->id;?>" class="RadioForm" value="<?=$this->options[$a]["value"];?>" <? if($this->options[$a]["value"]==$this->value)echo "checked";?> <?if($this->readonly)echo "disabled";?> <?=$this->options[$a]["script"];?> /> <?=$this->options[$a]["name"];?>&#160;&#160;&#160;<?
	 	}
		?><br><?
	}
	
} // RadioButtonElement

/***************************************************************************
 * Checkbox-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class CheckboxElement extends FormElement {
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		?><input type="checkbox" id="<?=$this->id;?>" name="<?=$this->id;?>" class="CeckboxForm" <? if($this->value=="on")echo "checked";?> /><?
	}
	
} // CheckboxElement

/***************************************************************************
 * Datum-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class DateElement extends FormElement {
			
	protected $dynamicContent = null;
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function DateElement($id, $name, $value, $required=false, $error="", $dynamicContent=null, $readonly=false ){
		parent::__construct($id, $name, $value, $required, $error, $readonly);
		$this->dynamicContent=$dynamicContent;
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		global $DOMAIN_HTTP_ROOT;
		?>
		<table>
			<tr>
				<td align="left" valign="middle"><input type="text" id="<?=$this->id;?>" name="<?=$this->id;?>" value="<?=$this->value;?>" class="TextForm" style="width: <?=$this->width-30;?>px;" <?if($this->readonly)echo "disabled";?>></td>
				<td align="left" valign="middle"><?if(!$this->readonly){?><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/gui/calendar.png" style="cursor:pointer;cursor:hand;"  onClick="popUpCalendar(this, $('<?=$this->id;?>'), 'dd.mm.yyyy');"><?}?></td>
			</tr>
		</table>
		<?
		// Inhalt dynamisch laden?
		if( $this->dynamicContent!=null ){ 
			$this->dynamicContent->PrintElement();
		}
	}
	
	/**
	 * Wandelt den übergebene String in einen Timestamp
	 * @return bool/int Gibt im Erfolgsfall den Timestamp oder im Fehlerfall false zurück
	 */
	static public function GetTimeStamp($dateString)
	{
		return HelperLib::ConvertStringToTimeStamp($dateString);
	}
	
} // DateElement

/***************************************************************************
 * Datum und Uhrzeit-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class TimeElement extends FormElement {
	
	protected $dynamicContent = null;
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function DateAndTimeElement($id, $name, $value, $required=false, $error="", $dynamicContent=null ){
		parent::__construct($id, $name, $value, $required, $error);
		$this->dynamicContent=$dynamicContent;
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		global $DOMAIN_HTTP_ROOT;
		?>
		<table>
			<tr>
				<td align="left" valign="middle"><input type="text" id="<?=$this->id;?>" name="<?=$this->id;?>" value="<?=$this->value;?>" class="TextForm" style="width: <?=60;?>px;" <?if($this->readonly)echo "disabled";?>></td>
			</tr>
		</table>
		<?
		// Inhalt dynamisch laden?
		if( $this->dynamicContent!=null ){ 
			$this->dynamicContent->PrintElement();
		}
	}
	
	/***************************************************************************
	* Wandelt den übergebene String in einen Timestamp
	* @return bool/int Gibt im Erfolgsfall den Timestamp oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function GetTimeStamp($timeString){
		// Zeit im Format hh:mm
		$dateParts=explode(":", trim($timeString));
		if( count($dateParts)==2 ){
			if( ((int)$dateParts[0])>=0 && ((int)$dateParts[0])<24 ){
				if( ((int)$dateParts[1])>=0 && ((int)$dateParts[1])<=59 ){
					return (int)((int)$dateParts[0]*60*60+(int)$dateParts[1]*60);
				}
			}
		}
		return false;
	}
	
	/***************************************************************************
	* Wandelt den übergebene Timestamp in einen String im Format hh:mm um
	* @return bool/string Gibt im Erfolgsfall den String oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function GetString($timestamp){
		if( !is_numeric($timestamp) || $timestamp!=(int)$timestamp )return false;
		$hours=(int)($timestamp/60/60);
		$min= ($timestamp % (60*60))/60;
		return $hours.":".($min<10 ? "0" : "").$min;
	}
	
} // TimeElement

/***************************************************************************
 * Datum und Uhrzeit-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class DateAndTimeElement extends FormElement {
			
	protected $dynamicContent = null;
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function DateAndTimeElement($id, $name, $value, $required=false, $error="", $dynamicContent=null ){
		parent::__construct($id, $name, $value, $required, $error);
		$this->dynamicContent=$dynamicContent;
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		global $DOMAIN_HTTP_ROOT;
		?>
		<table>
			<tr>
				<td align="left" valign="middle"><input type="text" id="<?=$this->id;?>" name="<?=$this->id;?>" value="<?=$this->value["date"];?>" class="TextForm" style="width: <?=$this->width-150;?>px;" <?if($this->readonly)echo "disabled";?>></td>
				<td align="left" valign="middle"><img src="<?=$DOMAIN_HTTP_ROOT;?>pics/gui/calendar.png" style="cursor:pointer;cursor:hand;"  onClick="popUpCalendar(this, $('<?=$this->id;?>'), 'dd.mm.yyyy');"></td>
				<td align="left" valign="middle"><input type="text" id="<?=$this->id;?>_clock" name="<?=$this->id;?>_clock" value="<?=$this->value["time"];?>" class="TextForm" style="width: <?=60;?>px;" <?if($this->readonly)echo "disabled";?>></td>
			</tr>
		</table>
		<?
		// Inhalt dynamisch laden?
		if( $this->dynamicContent!=null ){ 
			$this->dynamicContent->PrintElement();
		}
	}
	
	/***************************************************************************
	* Wandelt den übergebene String in einen Timestamp
	* @return bool/int Gibt im Erfolgsfall den Timestamp oder im Fehlerfall false zurück
	* @access public
	***************************************************************************/
	static public function GetTimeStamp($dateString, $timeString){
		// Datum
		$time=DateElement::GetTimeStamp($dateString);
		if( $time===false )return false;
		// Uhrzeit
		$dateParts=explode(":", trim($timeString));
		if( count($dateParts)==2 ){
			if( ((int)$dateParts[0])>=0 && ((int)$dateParts[0])<24 ){
				if( ((int)$dateParts[1])>=0 && ((int)$dateParts[1])<=59 ){
					return ((int)((int)$time+(int)$dateParts[0]*60*60+(int)$dateParts[1]*60));
				}
			}
		}
		return false;
	}
	
} // DateAndTimeElement


/***************************************************************************
 * File-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class FileElement extends FormElement {
		
	protected $allowedFileTypes = Array();
	protected $maxFileSize = 0;
	
	/**
	 * Show file creation time (upload date)
	 * @var bool 
	 */
	protected $showCreationDate = false;
	
	/**
	 * Show delete option
	 * @var bool 
	 */
	protected $canDeleteFile = true;
	
	/**
	 * Show upload option
	 * @var bool 
	 */
	protected $showUploadButton = true;
	
	/**
	 * Show upload option
	 * @var bool 
	 */
	protected $showFileUpload = true;
	
	/**
	 * Files to be listed
	 * @var File[] 
	 */
	protected $fileList = Array();
		
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function FileElement($id, $name, $value, $required=false, $error="", $fileSemantic=FM_FILE_SEMANTIC_UNKNOWN, $fileList=Array(), $canDeleteFile=true, $showUploadButton=true, $showCreationDate=false, $showFileUpload=true  ){
		global $fileManager;
		$allowedFileTypes=$fileManager->GetFileTypesForFileSemantic($fileSemantic);
		$maxFileSize=$fileManager->GetMaxFilesizeForFileSemantic($fileSemantic);
		$this->canDeleteFile = $canDeleteFile;
		$this->showUploadButton = $showUploadButton;
		$this->showFileUpload = $showFileUpload;
		if (!$this->showFileUpload) $this->showUploadButton=false;
		$addToName="";
		if ($this->showFileUpload) 
		{
			$fileTypes="";
			for ($a=0; $a<count($allowedFileTypes); $a++)
			{
				if($fileTypes!="")$fileTypes.=", ";
				$fileTypes.=trim(strtoupper($allowedFileTypes[$a]));
			}
			if ($fileTypes!="") $addToName.=$fileTypes;
			if ($maxFileSize>0)
			{
				if ($addToName!="") $addToName.="; ";
				$addToName.="max. ".($maxFileSize/1024/1024)."MB";
			}
			if ($addToName!="") $addToName=" (".$addToName.")";
		}
		parent::__construct($id, $name.$addToName, $value, $required, $error);
		$this->allowedFileTypes=$allowedFileTypes;
		$this->maxFileSize=$maxFileSize;
		$this->fileList=$fileList;
		$this->showCreationDate = $showCreationDate;
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		global $DOMAIN_HTTP_ROOT;
		if ($this->showFileUpload){?><input type="file" id="<?=$this->id;?>" name="<?=$this->id;?>" class="fileForm"/><?}?><?if($this->showUploadButton){?><input type="button" value="Upload" id="btn_<?=$this->id;?>" name="btn_<?=$this->id;?>" class="formButton" onClick="javascript:document.forms.FM_FORM.sendData.value=true; document.forms.FM_FORM.forwardToListView.value=false; document.forms.FM_FORM.submit();"><?}?><input type="hidden" id="deleteFile_<?=$this->id;?>" name="deleteFile_<?=$this->id;?>" value=""/><?
		if ($this->showFileUpload){?><br /><?}
		if (count($this->fileList)>0)
		{
			?><table border="0" width="100%"><?
			for($a=0; $a<count($this->fileList); $a++){
			?>	<tr>
				<?	if ($this->showCreationDate){?>
						<td valign="top" width="60px">
							<?=date("d.m.Y", $this->fileList[$a]->GetCreationTime());?>
						</td>
				<?	}?>
					<td valign="top">
						<a href="<?=$DOMAIN_HTTP_ROOT;?>templates/download_file.php5?<?=SID;?>&code=<?=$_SESSION['fileDownloadManager']->AddDownloadFile($this->fileList[$a])?>&timestamp=<?=time();?>"><?=$this->fileList[$a]->GetFileName();?></a><br>
					</td>
				<?	if($this->canDeleteFile){?>
						<td valign="top" width="60px">
							<a href="javascript:javascript:showDialogWindow(263, 163, 'url(<?=$DOMAIN_HTTP_ROOT;?>pics/dialog/dialog_mitte_links.gif)', 'Warnhinweis', 'Möchten Sie die Datei wirklich löschen?', 'Ja, Datei löschen', 'javascript:document.forms.FM_FORM.forwardToListView.value=false; document.forms.FM_FORM.deleteFile_<?=$this->id;?>.value=<?=$this->fileList[$a]->GetPKey();?>; document.forms.FM_FORM.submit();', '', '', '', '');">[Löschen]</a>
						</td>
				<?	}?>
				</tr><?
			}
			?></table><?
		}
	}
		
	/***************************************************************************
	* Erzeugt ein FileElement aus den übergebene Daten und gibt dieses zurück
	* @return Object/int Gibt im Erfolgsfall das FileElement und im Fehlerfall 	-1: Keine Datei angegeben
	*												-2: Datei ist zu groß (php.ini)
	*												-3: Datei konnte nicht komplett übertragen werden
	*												-4: Datei konnte nicht übertragen werden (unbekannter Fehler)
	*												-5: Dateiendung nicht erlaubt
	* @access public
	***************************************************************************/
	static public function GetFileElement($db, $uploadFileData, $fileSemantic){
		// Datei ausgewählt?
		if( isset($uploadFileData) && $uploadFileData["error"]!=UPLOAD_ERR_NO_FILE ){
			// Datei auf Server angekommen?
			if( is_uploaded_file($uploadFileData["tmp_name"]) && $uploadFileData["error"]==UPLOAD_ERR_OK ){
				global $fileManager;
				$pathInfo = pathinfo($uploadFileData["name"]);
				$fileObject=$fileManager->CreateFromFile($db, $uploadFileData["tmp_name"], $fileSemantic, $pathInfo["basename"], $pathInfo["extension"]);
				@unlink($uploadFileData["tmp_name"]);
				if( is_object($fileObject) && get_class($fileObject)=="File" )return $fileObject;
				return $fileObject-10;
			}else{
				if( $uploadFileData["error"]==UPLOAD_ERR_INI_SIZE )return -2;
				if( $uploadFileData["error"]==UPLOAD_ERR_PARTIAL )return -3;
				if( $uploadFileData["error"]==UPLOAD_ERR_NO_FILE )return -1;
				return -4;
			}
		}
		return -1;	
	}
	
	/***************************************************************************
	* Gibt für den übergebene Fehlercode einen entsprechenden Fehlertext zurück
	* @param int 	Fehlercode
	* @return string 	Fehlertext
	* @access public
	***************************************************************************/
	static public function GetErrorText($errorCode){
		switch($errorCode){
			case -1:
				return "Bitte laden Sie die zugehörige Datei hoch.";
			case -2:
			case -17:
				return "Die angegebene Datei ist zu groß.";
			case -18:
				return "Die angegebene Datei ist leer (Dateigröße: 0 Byte)";
			case -3:
				return "Bei der Übertragung ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.";
			case -4:
				return "Die Datei konnte nicht übertragen werden. Bitte versuchen Sie es erneut.";
			case -5:
			case -13:
			case -14:
				return "Der Dateityp ist nicht erlaubt.";
		}
		return "Es ist ein allgemeiner Fehler beim Hochladen und Speichern aufgetreten. Fehlercode: ".$errorCode."";
	}
	
} // FileElement

/***************************************************************************
 * BlankElement-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class BlankElement extends FormElement {
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function BlankElement(){
		parent::__construct("", "", "");
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		?>&#160;<?
	}
	
} // BlankElement

/***************************************************************************
 * HLineElement-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class HLineElement extends FormElement {
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function HLineElement(){
		parent::__construct("", "", "");
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		?><hr style="height:1px; width:100%;"><?
	}
	
} // HLineElement

/***************************************************************************
 * List-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class ListElement extends FormElement {

	private $listDataObject = null;

	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function ListElement($id, $name, $value, $required=false, $error="", $readonly=false, $listDataObject){
		$this->listDataObject=$listDataObject;
		$this->fullWidthNeeded=true;
		parent::__construct($id, $name, $value, $required, $error, $readonly);
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		$list1 = new NCASList( $this->listDataObject, "FM_FORM" );
		$list1->PrintData();
	}
	
} // ListElement


/**
  * List-Element
  * 
  * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
  *
  * @since    	PHP 5.0
  * @version		1.0
  * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
  */
class DynamicListElement extends FormElement 
{

	/**
	 * @var DynamicTable 
	 */
	private $listDataObject = null;

	/**
	 * Konstruktor
	 * @param string	$name	Name des Elements
	 * @param string	$value	Wert des Elements
	 * @param bool	$required	Pflichtfeld
	 */
	public function DynamicListElement($id, $name, $value, $required=false, $error="", $readonly=false, DynamicTable $listDataObject)
	{
		$this->listDataObject = $listDataObject;
		$this->fullWidthNeeded = true;
		parent::__construct($id, $name, $value, $required, $error, $readonly);
	}
	
	/**
	 * Gibt das Element aus
	 */
	public function PrintElement()
	{
		$list1 = new KimList($_SESSION["dynamicTableManager"], $this->listDataObject);
		$list1->PrintData();
	}
	
} // DynamicListElement


/***************************************************************************
 * CustomHTMLElement-Element
 * 
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 ***************************************************************************/
class CustomHTMLElement extends FormElement {
	
	private $htmlCode="";
	
	/***************************************************************************
	* Konstruktor
	* @param string	$name	Name des Elements
	* @param string	$value	Wert des Elements
	* @param bool	$required	Pflichtfeld
	* @access public
	***************************************************************************/
	public function CustomHTMLElement($htmlCode, $name=""){
		$this->htmlCode=$htmlCode;
		parent::__construct("", $name, "");
	}
	
	/***************************************************************************
	* Gibt das Element aus
	* @access public
	***************************************************************************/
	public function PrintElement(){
		echo $this->htmlCode;
	}
	
} // CustomHTMLElement


/**
 * Ampel-Element
 *
 * @author   	Stephan Walleczekl <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2020 Stoll von Gáti GmbH www.stollvongati.com
 */
class AmpelElement extends FormElement {

	protected $realisiert = false;

	/**
	 * Konstruktor
	 * @param string	$name	Name des Elements
	 * @param string	$value	Wert des Elements
	 * @param bool	$required	Pflichtfeld
	 * @access public
	 */
	public function AmpelElement($id, $name, $value, $required=false, $error="", $readonly=false, $realisiert=false){
		$this->realisiert = $realisiert;
		parent::__construct($id, $name, $value, $required, $error, $readonly);
	}

	/***************************************************************************
	 * Gibt das Element aus
	 * @access public
	 ***************************************************************************/
	public function PrintElement()
	{
		$showFolgeeinsparung = false;
		?>
		<table>
			<tr>
				<td>&#160;</td>
				<td colspan="<?if($this->realisiert){?>6<?}else{?>3<?}?>" <?if($this->realisiert){?>style="border-right: solid 1px #989898;"<?}?>>Ersteinsparung</td>
				<td colspan="<?if($this->realisiert){?>6<?}else{?>3<?}?>" style="<?if($this->realisiert){?>border-right: solid 1px #989898;<?}?><?if(!$showFolgeeinsparung){?> display: none;<?}?>" data-columntype="folgeeinsparung_<?=$this->id;?>">Folgeeinsparung</td>
				<td rowspan="<?=(2+count(Kuerzungsbetrag::GetEinsparungsTypen()));?>" valign="top"><input type="button" value="<?=($showFolgeeinsparung ? "-" : "+");?>" onClick="$$('td[data-columntype=folgeeinsparung_<?=$this->id;?>]').setStyle('display', this.dataset.currentstate); this.dataset.currentstate = this.dataset.currentstate=='' ? 'none' : ''; this.value = this.dataset.currentstate=='' ? '+' : '-';" data-currentstate="<?=($showFolgeeinsparung ? "none" : "");?>" ></td>
			</tr>
			<tr>
				<td>&#160;</td>
				<td>Grün</td>
				<?if($this->realisiert){?><td align="center" style="border-right: solid 1px #989898;">r</td><?}?>
				<td>Gelb</td>
				<?if($this->realisiert){?><td align="center" style="border-right: solid 1px #989898;">r</td><?}?>
				<td>Rot</td>
				<?if($this->realisiert){?><td align="center" style="border-right: solid 1px #989898;">r</td><?}?>
				<td data-columntype="folgeeinsparung_<?=$this->id;?>" <?if(!$showFolgeeinsparung){?>style="display: none;"<?}?>>Grün</td>
				<?if($this->realisiert){?><td align="center" style="border-right: solid 1px #989898;<?if(!$showFolgeeinsparung){?> display: none;<?}?>" data-columntype="folgeeinsparung_<?=$this->id;?>">r</td><?}?>
				<td data-columntype="folgeeinsparung_<?=$this->id;?>" <?if(!$showFolgeeinsparung){?>style="display: none;"<?}?>>Gelb</td>
				<?if($this->realisiert){?><td align="center" style="border-right: solid 1px #989898;<?if(!$showFolgeeinsparung){?> display: none;<?}?>" data-columntype="folgeeinsparung_<?=$this->id;?>">r</td><?}?>
				<td data-columntype="folgeeinsparung_<?=$this->id;?>" <?if(!$showFolgeeinsparung){?>style="display: none;"<?}?>>Rot</td>
				<?if($this->realisiert){?><td align="center" style="border-right: solid 1px #989898;<?if(!$showFolgeeinsparung){?> display: none;<?}?>" data-columntype="folgeeinsparung_<?=$this->id;?>">r</td><?}?>
			</tr>
			<? 	foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType){
				if ($einsparungType==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT)
				{?>
					<tr>
					<td>Lösungsvorschlag</td>
					<? 	foreach(Kuerzungsbetrag::GetTypes() as $type){
							foreach(Kuerzungsbetrag::GetRatings() as $rating){ ?>
								<td <?if($type==Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG){?>data-columntype="folgeeinsparung_<?=$this->id;?>" <?if(!$showFolgeeinsparung){?>style="display: none;"<?}?><?}?>>
									<?if($rating==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN){?><div class="TextForm" style="width: 50px; text-align: right;"><?=$this->GetVorschlag($type);?></div><?}?>
								</td>
								<?if($this->realisiert){?>
									<td style="border-right: solid 1px #989898; <?if($type==Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG && !$showFolgeeinsparung){?>display: none;<?}?>" <?if($type==Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG){?>data-columntype="folgeeinsparung_<?=$this->id;?>"<?}?>></td>
								<?}
							}
					}?>
			<?	}
				?>
				<tr>
					<td><?=Kuerzungsbetrag::GetEinsparungsTypName($einsparungType);?></td>
				<? 	foreach(Kuerzungsbetrag::GetTypes() as $type){
					 	foreach(Kuerzungsbetrag::GetRatings() as $rating){ ?>
							<td <?if($type==Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG){?>data-columntype="folgeeinsparung_<?=$this->id;?>" <?if(!$showFolgeeinsparung){?>style="display: none;"<?}?><?}?>>
								<?if(in_array($einsparungType, array(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD)) || $rating==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN){?><input type="text" id="<?=$this->id;?>_<?=$einsparungType;?>_<?=$type;?>_<?=$rating;?>" name="<?=$this->id;?>_<?=$einsparungType;?>_<?=$type;?>_<?=$rating;?>" value="<?=$this->value[$einsparungType][$type][$rating];?>" class="TextForm" style="width: 50px; text-align: right; <?if(isset($this->error[$einsparungType][$type][$rating])){?>border: 1px solid #db2209;<?}?>" <?if($this->readonly)echo "disabled";?> placeholder="0,00"><?}?>
							</td>
							<?if($this->realisiert){?>
								<td style="border-right: solid 1px #989898; <?if($type==Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG && !$showFolgeeinsparung){?>display: none;<?}?>" <?if($type==Kuerzungsbetrag::KUERZUNGSBETRAG_TYPE_FOLGEEINSPARUNG){?>data-columntype="folgeeinsparung_<?=$this->id;?>"<?}?>>
									<?if(in_array($einsparungType, array(Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG, Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_ENTGEGENKOMMEN_STANDARD)) || $rating==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRUEN){?><input type="checkbox" id="<?=$this->id;?>_<?=$einsparungType;?>_<?=$type;?>_<?=$rating;?>_r" name="<?=$this->id;?>_<?=$einsparungType;?>_<?=$type;?>_<?=$rating;?>_r" class="CeckboxForm" <? if($this->value["r"][$einsparungType][$type][$rating]=="on")echo "checked";?> /><?}?>
								</td>
							<?}?>
				<?		}
					}?>
				</tr>
			<?	}?>
		</table>
		<?
	}

	/**
	 * Berechnet den Lösungsvorschlag
	 * @param $type
	 * @return bool
	 */
	protected function GetVorschlag($type)
	{
		$total = 0.0;
		foreach(Kuerzungsbetrag::GetEinsparungsTypen() as $einsparungType)
		{
			if ($einsparungType==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_VERMIETERANGEBOT) continue;
			$einsparungTypeSumme = 0.0;
			foreach(Kuerzungsbetrag::GetRatings() as $rating)
			{
				if ($rating==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_ROT || $rating==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSTUFUNG_GRAU) continue;
				$value = TextElement::GetFloat($this->value[$einsparungType][$type][$rating]);
				$einsparungTypeSumme+=$value;
			}
			if ($einsparungType==Kuerzungsbetrag::KUERZUNGSBETRAG_EINSPARUNGSTYP_MAXIMALFORDERUNG) $total+=$einsparungTypeSumme;
			else $total-=$einsparungTypeSumme;
		}
		return HelperLib::ConvertFloatToLocalizedString($total);
	}

	/**
	 * Gibt den Fehlertext des Elements zurück
	 * @return string Fehlertext
	 */
	public function GetError()
	{
		return "Bitte überprüfen Sie ihre Eingabe bei den rot markierten Feldern";
	}

} // TextAreaElement