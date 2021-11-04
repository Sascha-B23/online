<div id="menu" style="width:100%; height:33px; background-color:#CC0033">
	<?if (isset($_SESSION["currentUser"])){?>
	<!-- Menü -->
	<script type="text/javascript">
		var checkerle = 0; 
		var a; 
		var um_hidden_1 = false; 
		var um_hidden_2 = false; 
		var um_hidden_3 = false; 
		var um_hidden_4 = false; 
	</script>
	<?}?>
	<?if($MENU_SHOW != false){
		include($DOMAIN_FILE_SYSTEM_ROOT."shared/menu/menu_content_".$LANG.".php5");?>
		<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<td>
					<span style="position:relative; top:8px; left:14px;">
						<?
							$count=count($mainmenu);
							if($count < 4) $count = 4;
						?>
						<?for($i=1; $i<$count+1; $i++){?>
							<? /*
							<span id="mm_<?=$i?>" onmouseover="oldinnerHTML=this.innerHTML;this.innerHTML='&lt;&#160;<?=$mainmenu[$i]["name"]?>&#160;&gt;'" onmouseout="this.innerHTML='&#160;&#160;&#160;<?=$mainmenu[$i]["name"]?>&#160;&#160;&#160;';" onclick="newinnerHTML=this.innerHTML;oldmout=this.onmouseout;this.onmouseout='';this.innerHTML='&lt;&#160;<?=$mainmenu[$i]["name"]?>&#160;&gt;';if(window.mover_show_submenu) mover_show_submenu(<?=$i?>);">&#160;&#160;&#160;<?=$mainmenu[$i]["name"]?>&#160;&#160;&#160;</span>
							*/ 
							$mouseoverpic = explode(".", $mainmenu[$i]["img"]);
							$mouseoverpic = $mouseoverpic[0]."_over.".$mouseoverpic[1]
							?>
							<?if($mainmenu[$i]["name"] != ""){?>
								<?if( $mainmenu[$i]["id"] == $HM ){?>
									<script type="text/javascript">checkerle = <?=$i?>;</script>
									<?if($mainmenu[$i]["link"] != ""){?>
										<span id="mm_<?=$i?>"><a href="<?=$mainmenu[$i]["link"]?>?<?=SID?>"><img id="img_mm_<?=$i?>" onmouseover="if(window.hideAllSubmenues)hideAllSubmenues();" src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mouseoverpic?>" alt="<?=$mainmenu[$i]["name"]?>" style="position:relative; top:-8px;" /></a></span>
									<?}else{?>
										<span id="mm_<?=$i?>"><img id="img_mm_<?=$i?>" src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mouseoverpic?>" alt="<?=$mainmenu[$i]["name"]?>" style="position:relative; top:-8px;" onmouseover="if(window.mover_show_submenu)mover_show_submenu(<?=$i?>);" onclick="if(window.mover_show_submenu)mover_show_submenu(<?=$i?>);"/></span>
									<?}?>
								<?}else{?>
									<?if($mainmenu[$i]["link"] != ""){?>
										<span id="mm_<?=$i?>"><a href="<?=$mainmenu[$i]["link"]?>?<?=SID?>"><img id="img_mm_<?=$i?>" src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mainmenu[$i]["img"]?>" alt="<?=$mainmenu[$i]["name"]?>" style="position:relative; top:-8px;" onmouseover="this.src='<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mouseoverpic?>'; if(window.hideAllSubmenues)hideAllSubmenues();" onmouseout="this.src='<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mainmenu[$i]["img"]?>'"/></a></span>
									<?}else{?>
										<span id="mm_<?=$i?>"><img id="img_mm_<?=$i?>" src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mainmenu[$i]["img"]?>" alt="<?=$mainmenu[$i]["name"]?>" style="position:relative; top:-8px;" onmouseover="this.src='<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mouseoverpic?>';if(window.mover_show_submenu) mover_show_submenu(<?=$i?>);"/></span>
									<?}?>
								<?}?>
							<?}else{?>
								<img src="<?=$DOMAIN_HTTP_ROOT?>pics/blind.gif" id="img_mm_<?=$i?>" style="width:1px; height:1px;" alt="" />
							<?}?>
							<img src="<?=$DOMAIN_HTTP_ROOT?>pics/blind.gif" style="width:40px; height:5px;" alt="" />
						<?}?>
					</span>
				</td>
				<?
					//Browserabfrage
					$u_agent = $_SERVER['HTTP_USER_AGENT']; 
					$ub = ''; 
					if(preg_match('/MSIE/i',$u_agent)){ 
						$ub = "ie"; 
					}elseif(preg_match('/Firefox/i',$u_agent)){ 
						$ub = "firefox"; 
					}elseif(preg_match('/Safari/i',$u_agent) || preg_match('/Chrome/i',$u_agent)){ 
						$ub = "webkit"; 
					}elseif(preg_match('/Opera/i',$u_agent)){ 
						$ub = "opera"; 
					}else{
						$ub = "ns";
					}
				?>
				<td style="width: 100px;">
					<span <?if($ub == "webkit" || $ub == "ns" || $ub == "ie"){?>style="position:relative; top:-2px;"<?}?>>
						<a href="<?=$DOMAIN_HTTP_ROOT?>de/logout.php5?<?=SID?>" style="text-decoration:none; color:#ffffff;"><img src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/hm_menu_logout.gif" onmouseover="this.src='<?=$DOMAIN_HTTP_ROOT?>pics/menu/hm_menu_logout_over.gif'" onmouseout="this.src='<?=$DOMAIN_HTTP_ROOT?>pics/menu/hm_menu_logout.gif'" alt="" /></a>
					</span>
				</td>
			</tr>
		</table>
			
		<?for($i=1; $i<$count+1;$i++){?>
			<?if(count($submenu[$i]) != 0){?>
				<div id="um_<?=$i?>" style="z-index:999; position:absolute; visibility:hidden;" onmouseover="window.clearTimeout(a); if(window.mover_show_submenu) mover_show_submenu(<?=$i?>);" onmouseout="a = window.setTimeout('mout_hide_submenu(<?=$i?>)', 1);">
					
					<table border="0" cellpadding="0" cellspacing="0">
					<?for($lauf=1; $lauf<count($submenu[$i])+1; $lauf++){?>
						<?if($lauf > 1){?>
						<tr style="height:1px;">
							<td width="14" style="background:url(<?=$DOMAIN_HTTP_ROOT?>pics/menu/dd_hg_line_left.png);background-repeat:repeat-y;"></td>
							<td bgcolor="#ffffff">
								<table border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
									<tr bgcolor="#ffffff">
										<td width="14" bgcolor="#ffffff"></td>
										<td bgcolor="#ffffff"></td>
										<td width="16" bgcolor="#ffffff"></td>
									</tr>
								</table>
							</td>
							<td width="16" style="background:url(<?=$DOMAIN_HTTP_ROOT?>pics/menu/dd_hg_line_right.png);background-repeat:repeat-y;"></td>
						</tr>
						<?}?>
						<tr style="height:30px;">
							<td width="14" style="background:url(<?=$DOMAIN_HTTP_ROOT?>pics/menu/dd_hg_leftSide.png);background-repeat:repeat-y;"></td>
							<?
								$mouseoverpic=explode(".", $submenu[$i][$lauf]["img"]);
								$mouseoverpic = $mouseoverpic[0]."_over.".$mouseoverpic[1];
							?>
							<td bgcolor="#E5E5E5">
								<?if($submenu[$i][$lauf]["subID"] == $UM && $mainmenu[$i]["id"] == $HM){?>
									<a href="<?=$submenu[$i][$lauf]["link"]."?".SID?>"><img src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mouseoverpic?>" alt="<?=$submenu[$i][$lauf]["name"]?>" /></a>
								<?}else{?>
									<a href="<?=$submenu[$i][$lauf]["link"]."?".SID?>"><img src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$submenu[$i][$lauf]["img"]?>" alt="<?=$submenu[$i][$lauf]["name"]?>" onmouseover="this.src='<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mouseoverpic?>'" onmouseout="this.src='<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$submenu[$i][$lauf]["img"]?>'" /></a>
								<?}?>
							</td>
							<td width="16" style="background:url(<?=$DOMAIN_HTTP_ROOT?>pics/menu/dd_hg_rightSide.png);background-repeat:repeat-y;"></td>
						</tr>
					<?}?>
						<tr style="height:9px; line-height:0;">
							<td width="14"><img src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/dd_hg_bottom_left.png" width="14" alt="" /></td>
							<td style="background:url(<?=$DOMAIN_HTTP_ROOT?>pics/menu/dd_hg_bottom_middle.png); background-repeat: repeat-x;"></td>
							<td width="16"><img src="<?=$DOMAIN_HTTP_ROOT?>pics/menu/dd_hg_bottom_right.png" width="16" alt="" /></td>
						</tr>
					</table>
				</div>
			<?}else{?>
				<div id="um_<?=$i?>" style="z-index:999; position:absolute; visibility:hidden;" onmouseover="window.clearTimeout(a); if(window.mover_show_submenu) mover_show_submenu(<?=$i?>);" onmouseout="a = window.setTimeout('mout_hide_submenu(<?=$i?>)', 1);">
					<script type="text/javascript">um_hidden_<?=$i?> = true;</script>
				</div>
			<?}?>
		<?}?>
		<script type="text/javascript">
			//////////////////////////////
			// Browserweiche
			var ie=false; var version="0";
			if( typeof( window.innerWidth ) == 'number' ){
				ie=false; //Non-IE
			} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ){
				ie=true; version="6+"; //IE 6+ in 'standards compliant mode'
			} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ){
				ie=true; version="4&8k"; //IE 4 and IE 8 in 'compatibility mode' compatible
			}
			// Browserweiche Ende
			//////////////////////////////
			
			function getX(obj){
			   return( obj.offsetParent==null ? obj.offsetLeft : obj.offsetLeft+getX(obj.offsetParent) );
			}
			function getY(obj){
			   return( obj.offsetParent==null ? obj.offsetTop : obj.offsetTop+getY(obj.offsetParent) );
			}
			
			function calcMenueLayout(){
				// Dynamisches auslesen der Dropdownleisten Position
				<?if($ub == "ie"){?>
					menu_add=209;
					if( ie==true && version=="6+" )
					{
						var mm1left = getX(document.getElementById("mm_1"));
						var mm1top = getY(document.getElementById("mm_1"));
					}
					else if( ie==true && version=="4&8k" )
					{
						var mm1left = getX(document.getElementById("mm_1"))+0;
						var mm1top = getY(document.getElementById("mm_1"))+20;
					}
					else if( ie==true && version=="7" )
					{
						var mm1left = getX(document.getElementById("mm_1"))+20;
						var mm1top = getY(document.getElementById("mm_1"))+20;
						menu_add = 204;
					}
					else
					{
						var mm1left = getX(document.getElementById("mm_1"))+20;
						var mm1top = getY(document.getElementById("mm_1"))-8;
						menu_add = 204;
					}
					
					document.getElementById("um_1").style.top = mm1top+"px";
					document.getElementById("um_2").style.top = mm1top+"px";
					document.getElementById("um_3").style.top = mm1top+"px";
					document.getElementById("um_4").style.top = mm1top+"px";
					
					var menu_left = -10 + mm1left;
					document.getElementById("um_1").style.left = menu_left + "px";
					if( um_hidden_1 != true ){ menu_left = menu_left + menu_add; }
					document.getElementById("um_2").style.left = menu_left + "px";
					if( um_hidden_2 != true ){ menu_left = menu_left + menu_add; }
					document.getElementById("um_3").style.left = menu_left + "px";
					if( um_hidden_3 != true ){ menu_left = menu_left + menu_add; }
					document.getElementById("um_4").style.left = menu_left + "px";
					if( um_hidden_4 != true ){ menu_left = menu_left + menu_add; }
					
				<?}else if($ub == "firefox"){?>
					var mm1left = getX(document.getElementById("mm_1"))+20;
					var mm1top = getY(document.getElementById("mm_1"))+3;
					
					document.getElementById("um_1").style.top = mm1top+"px";
					document.getElementById("um_2").style.top = mm1top+"px";
					document.getElementById("um_3").style.top = mm1top+"px";
					document.getElementById("um_4").style.top = mm1top+"px";
					
					var menu_left = -10 + mm1left;
					document.getElementById("um_1").style.left = menu_left + "px";
					if( um_hidden_1 != true ){ menu_left = menu_left + 204 }
					document.getElementById("um_2").style.left = menu_left + "px";
					if( um_hidden_2 != true ){ menu_left = menu_left + 204 }
					document.getElementById("um_3").style.left = menu_left + "px";
					if( um_hidden_3 != true ){ menu_left = menu_left + 204 }
					document.getElementById("um_4").style.left = menu_left + "px";
					if( um_hidden_4 != true ){ menu_left = menu_left + 204 }
					
				<?}else if($ub == "webkit"){?>
					var mm1left = getX(document.getElementById("mm_1"))+16;
					var mm1top = getY(document.getElementById("mm_1"));
					// Abfrage wegen Bug nach Aktualisieren (nur Webkit-Browser)
					if(mm1top > 100){ mm1top = mm1top-19; }else{ mm1top = mm1top+23; } 
					
					document.getElementById("um_1").style.top = mm1top+"px";
					document.getElementById("um_2").style.top = mm1top+"px";
					document.getElementById("um_3").style.top = mm1top+"px";
					document.getElementById("um_4").style.top = mm1top+"px";
					
					
					var menu_left = -10 + mm1left;
					document.getElementById("um_1").style.left = menu_left + "px";
					if( um_hidden_1 != true ){ menu_left = menu_left + 208 }
					document.getElementById("um_2").style.left = menu_left + "px";
					if( um_hidden_2 != true ){ menu_left = menu_left + 208 }
					document.getElementById("um_3").style.left = menu_left + "px";
					if( um_hidden_3 != true ){ menu_left = menu_left + 208 }
					document.getElementById("um_4").style.left = menu_left + "px";
					if( um_hidden_4 != true ){ menu_left = menu_left + 208 }
					
					
				<?}else if($ub == "opera"){?>
					var mm1left = getX(document.getElementById("mm_1"))+14;
					var mm1top = getY(document.getElementById("mm_1"))+63;
					if(mm1top > 100){ mm1top = mm1top-30; }else{ mm1top = mm1top; } 
					
					document.getElementById("um_1").style.top = mm1top+"px";
					document.getElementById("um_2").style.top = mm1top+"px";
					document.getElementById("um_3").style.top = mm1top+"px";
					document.getElementById("um_4").style.top = mm1top+"px";
					
					var menu_left = -10 + mm1left;
					document.getElementById("um_1").style.left = menu_left + "px";
					if( um_hidden_1 != true ){ menu_left = menu_left + 210 }
					document.getElementById("um_2").style.left = menu_left + "px";
					if( um_hidden_2 != true ){ menu_left = menu_left + 210 }
					document.getElementById("um_3").style.left = menu_left + "px";
					if( um_hidden_3 != true ){ menu_left = menu_left + 210}
					document.getElementById("um_4").style.left = menu_left + "px";
					if( um_hidden_4 != true ){ menu_left = menu_left + 210 }
					
				<?}else if($ub == "ns"){?>
					var mm1left = getX(document.getElementById("mm_1"))+14;
					var mm1top = getY(document.getElementById("mm_1"))+3;
					
					if(mm1top < 100){ mm1top = mm1top; }else{ mm1top = mm1top-19; }
					
					document.getElementById("um_1").style.top = mm1top+"px";
					document.getElementById("um_2").style.top = mm1top+"px";
					document.getElementById("um_3").style.top = mm1top+"px";
					document.getElementById("um_4").style.top = mm1top+"px";
					
					var menu_left = -10 + mm1left;
					document.getElementById("um_1").style.left = menu_left + "px";
					if( um_hidden_1 != true ){ menu_left = menu_left + 210 }
					document.getElementById("um_2").style.left = menu_left + "px";
					if( um_hidden_2 != true ){ menu_left = menu_left + 210 }
					document.getElementById("um_3").style.left = menu_left + "px";
					if( um_hidden_3 != true ){ menu_left = menu_left + 210 }
					document.getElementById("um_4").style.left = menu_left + "px";
					if( um_hidden_4 != true ){ menu_left = menu_left + 210 }
					
				<?}?>
			}
			calcMenueLayout();
			
			// Dropdown Menüs verstecken
			var oldmout, oldinnerHTML, newinnerHTML;
			function mout_hide_submenu(submenu){
				//if(submenu == 3) return false;
				mover_show_submenu(1);
				document.getElementById("um_1").style.visibility = "hidden";
				document.getElementById("um_"+submenu).style.visibility = "hidden";
			}
			
			function hideAllSubmenues(){
				document.getElementById("um_1").style.visibility = "hidden";
				document.getElementById("um_2").style.visibility = "hidden";
				document.getElementById("um_3").style.visibility = "hidden";
				document.getElementById("um_4").style.visibility = "hidden";
			}
			
			// Dropdown Menü anzeigen
			function mover_show_submenu(submenu){
				if(document.getElementById("um_"+submenu) == null) return false;
			
				if(checkerle != 1 && submenu != 1)
					document.getElementById("img_mm_1").src = "<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mainmenu[1]["img"]?>";
				if(checkerle != 2 && submenu != 2)
					document.getElementById("img_mm_2").src = "<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mainmenu[2]["img"]?>";
				if(checkerle != 3 && submenu != 3)
					document.getElementById("img_mm_3").src = "<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mainmenu[3]["img"]?>";
				if(checkerle != 4 && submenu != 4)
					document.getElementById("img_mm_4").src = "<?=$DOMAIN_HTTP_ROOT?>pics/menu/<?=$mainmenu[4]["img"]?>";
				
				document.getElementById("um_1").style.visibility = "hidden";
				document.getElementById("um_2").style.visibility = "hidden";
				document.getElementById("um_3").style.visibility = "hidden";
				document.getElementById("um_4").style.visibility = "hidden";
				
				document.getElementById("um_"+submenu).style.visibility = "visible";
			}
		</script>
	<?}else{?>
		<script type="text/javascript">
			function calcMenueLayout()
			{
			}
		</script>
	<?}?>
</div>