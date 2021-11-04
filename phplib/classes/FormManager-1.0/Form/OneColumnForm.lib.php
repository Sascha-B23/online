<?php
/**
 * Form-Klasse für einspaltige Ausgabe
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class OneColumnForm extends Form 
{
	/**
	 * Tabellenbreite
	 * @var string
	 */
	protected $tableWidth = "512px";
	
	/**
	 * Constructor
	 */
	public function OneColumnForm($jumpBackURL, &$formData, $templateToUse="template_1row_edit.inc.php5", $noOutput=false )
	{
		parent::__construct($jumpBackURL, $formData, $templateToUse, $noOutput);
	}
	
	/**
	 * Set the width of the tabel
	 * @param string $width 
	 */
	public function SetTableWidth($width)
	{
		$this->tableWidth = $width;
	}
	
	/**
	 * PrintData()
	 * Ausgabe der Daten in HTML mit Design
	 */
	protected function PrintContent()
	{
		?>	<input type="hidden" name="sendData" id="sendData" value="<?=($this->defaultSendData ? "true" : "false");?>">
			<table border="0" cellpadding="0" cellspacing="0" width="<?=$this->tableWidth;?>" bgcolor="#ffffff">
			<?	// Allgemeine Fehlerausgabe
				$errors = $this->formData->GetErrors();
				if( count($errors)>0 ){ ?>
					<tr>
						<td>&#160;</td>
						<td align="center"><font class="errorText">Es sind Fehler aufgetreten. Bitte überprüfen Sie Ihre Eingaben.</font></td>
					</tr>
					<tr>
						<td colspan="2">&#160;</td>
					</tr>
				<?	if( is_array($errors["misc"]) && count($errors["misc"])>0 ){ 
						for($a=0; $a<count($errors["misc"]); $a++){ ?>
							<tr>
								<td>&#160;</td>
								<td align="center"><font class="errorText"><?=$errors["misc"][$a];?></font></td>
							</tr>
					<?	}?>
						<tr>
							<td colspan="2">&#160;</td>
						</tr>
				<?	}?>
			<?	}?>					
			<?	$elements=$this->formData->GetElements();
				for($a=0; $a<count($elements); $a++){ ?>
				<?	if($elements[$a]->FullWidthNeeded()){?>
					<tr>
						<td valign="top" colspan="2"><?=$elements[$a]->GetName();?><?if($elements[$a]->IsRequired())echo "*";?><br /><?=$elements[$a]->PrintElement();?>&#160;</td>
					</tr>
				<?	}else{ ?>
					<tr>
						<td valign="top"><?=$elements[$a]->GetName();?><?if($elements[$a]->IsRequired())echo "*";?>&#160;</td>
						<td valign="top"><?=$elements[$a]->PrintElement();?>&#160;</td>
					</tr>
				<?	}?>
					<? if($elements[$a]->HasAdvice()){ ?>
						<tr>
							<td>&#160;</td>
							<td class="adviceText"><?=$elements[$a]->GetAdvice();?>&#160;</td>
						</tr>						
					<? }?>
					<? if($elements[$a]->HasError()){ ?>
						<tr>
							<td>&#160;</td>
							<td class="errorText"><?=$elements[$a]->GetError();?>&#160;</td>
						</tr>						
					<? }?>
					<tr>
						<td>&#160;</td>
						<td>&#160;</td>
					</tr>
			<?	} ?>
			</table> <?
	}
}
?>