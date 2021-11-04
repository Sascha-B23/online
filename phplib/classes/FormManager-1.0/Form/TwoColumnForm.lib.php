<?php
/**
 * Form-Klasse für zweispaltige Ausgabe
 * 
 * @author   	Stephan Walleczek <s.walleczek@stollvongati.com>
 *
 * @since    	PHP 5.0
 * @version		1.0
 * @copyright 	Copyright (c) 2010 Stoll von Gáti GmbH www.stollvongati.com
 */
class TwoColumnForm extends Form 
{	
	/**
	 * PrintData()
	 * Ausgabe der Daten in HTML mit Design
	 */
	protected function PrintContent()
	{
		?>	<input type="hidden" name="sendData" id="sendData" value="<?=($this->defaultSendData ? "true" : "false");?>">
			<table border="0" cellpadding="0" cellspacing="0" width="919px">
				<tr  height="1px">
					<td width="29"></td>
					<td width="416"></td>
					<td width="29"></td>
					<td width="416"></td>
					<td width="29"></td>
				</tr>
			<?	// Allgemeine Fehlerausgabe
				$errors = $this->formData->GetErrors();
				if( count($errors)>0 ){ ?>
					<tr>
						<td>&#160;</td>
						<td colspan="3" align="center"><font class="errorText">Es sind Fehler aufgetreten. Bitte überprüfen Sie Ihre Eingaben.</font></td>
						<td>&#160;</td>
					</tr>
					<tr>
						<td colspan="5">&#160;</td>
					</tr>
				<?	if( is_array($errors["misc"]) && count($errors["misc"])>0 ){ 
						for($a=0; $a<count($errors["misc"]); $a++){ ?>
							<tr>
								<td>&#160;</td>
								<td colspan="3" align="center"><font class="errorText"><?=$errors["misc"][$a];?></font></td>
								<td>&#160;</td>
							</tr>
					<?	}?>
						<tr>
							<td colspan="5">&#160;</td>
						</tr>							
				<?	}?>
			<?	}?>					
			<?	// Eingabeelemente
				$elements=$this->formData->GetElements();
				$numLines=ceil(count($elements)/2);
				for($a=0; $a<$numLines; $a++)
				{ 
					$elem1=$elements[$a*2+0];
					$elem2=$elements[$a*2+1];
					if( $elem1!="" && $elem1->FullWidthNeeded() ){?>
						<tr>
							<td width="29">&#160;</td>
							<td width="861" valign="top" colspan="3">
								<?if (trim($elem1->GetName())!=''){?><strong><?=$elem1->GetName();?><?if($elem1->IsRequired())echo "*";?></strong><br /><?}?>
								<?=$elem1->PrintElement();?>
								<? if($elem1->HasError()){ ?>
									<font class="errorText"><?=$elem1->GetError();?>&#160;</font>
								<? }?>
							</td>
							<td width="29">&#160;</td>
						</tr>
				<?	}else{?>
						<tr>
							<td width="29">&#160;</td>
							<td width="416" valign="top">
							<? 	if( $elem1!="" ){?>
									<strong><?=$elem1->GetName();?><?if($elem1->IsRequired())echo "*";?></strong><br />
									<?=$elem1->PrintElement();?>
									<? if($elem1->HasError()){ ?>
										<font class="errorText"><?=$elem1->GetError();?>&#160;</font>
									<? }?>
							<?	}else{?>
									&#160;
							<?	}?>
							</td>
							<td width="29">&#160;</td>
							<td width="416" valign="top">
							<? 	if( $elem2!="" ){?>
									<strong><?=$elem2->GetName();?><?if($elem2->IsRequired())echo "*";?></strong><br />
									<?=$elem2->PrintElement();?>
									<? if($elem2->HasError()){ ?>
										<font class="errorText"><?=$elem2->GetError();?>&#160;</font>
									<? }?>
							<?	}else{?>
									&#160;
							<?	}?>
							</td>
							<td width="29">&#160;</td>
						</tr>
				<?	}?>
					<tr>
						<td colspan="5">&#160;</td>
					</tr>	
			<?	} ?>
			</table>
		<?
	}
}
?>