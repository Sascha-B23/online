<div class="dynamicTable_html_layout" style="width:900px; display: none; position:relative;">
	<!-- <div class="loading_icon" style="position:absolute; top:0px; left:0px; width:1007px; height:100%; background-color:#ffffff; border:1px solid red; z-index:9999;"> -->
		<img src="<?=$this->iconHttpRoot;?>loading.gif" class="loading_icon" />
		<img src="<?=$this->iconHttpRoot;?>filter_normal.png" class="filter_icon_normal" />
		<img src="<?=$this->iconHttpRoot;?>filter_active.png" class="filter_icon_active" />
		<img src="<?=$this->iconHttpRoot;?>order_asc.png" class="order_asc" />
		<img src="<?=$this->iconHttpRoot;?>order_desc.png" class="order_desc" />
	<!-- </div> -->
	<!-- Default-Aufbau Dynamic Table -->
	<table cellspacing="0" cellpadding="0" border="0" class="dynamicTable_tableInfo">
		<tbody>
			<tr>
				<td style="width: 40px;">
					<img class="icon" src="" alt="" />
				</td>
				<td>
					<strong class="headline"></strong>
				</td>
				<td style="width: 75px;">
					<span class="entries">0</span> Einträge
				</td>
				<td style="width: 80px;">
					Einträge / Seite
				</td>
				<td style="width: 20px;"><input class="entriesperpage" type="text" /></td>
				<td style="width: 25px;"><img class="entriesperpagesubmit" src="<?=$this->iconHttpRoot;?>pfeil_right.png" alt="" /></td>
				<td style="width: 15px;">&#160;</td>
				<td style="width: 30px;">Seite</td>
				<td style="width: 65px;">
					<input class="page_input" type="text" /> / <span class="page_count">0</span>
				</td>
				<td style="width: 40px;">Suche</td>
				<td style="width: 150px;">
					<input class="search_input" type="text" /> 
				</td>
				<td style="width: 25px;">
					<img class="search_button" src="<?=$this->iconHttpRoot;?>pfeil_right.png" alt="Suche starten" title="Suche starten" />
				</td>
				<td style="width: 50px;">
					<img class="search_clear" src="<?=$this->iconHttpRoot;?>close.png" alt="Suche zurücksetzen" title="Suche zurücksetzen" />
				</td>
				<td style="width:25px;">
					<div class="column_visibility_wrapper"></div>
					<img class="column_visibility_button" src="<?=$this->iconHttpRoot;?>columns_display_state.png" alt="Spalten ein-/ausblenden" title="Spalten ein-/ausblenden" />
				</td>
				<td style="width:25px;">
					<div class="config_wrapper"></div>
					<img class="config_button" src="<?=$this->iconHttpRoot;?>filter.png" alt="Filtereinstellungen" title="Filtereinstellungen" />
				</td>
				<td style="width: 25px;">
					<div class="button_maximize"></div>
					<input type="hidden" class="table_minimized_state" value="" />
				</td>
				<td style="width: 25px;">
					<div class="button_minimize"></div>
				</td>
			</tr>
		</tbody>
	</table>

	<table class="dynamicTable_mainTable" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td></td>
			</tr>
		</tbody>
	</table>

	<!-- Default-Aufbau Contextmenu Table -->
	<table class="dynamicTable_contextMenu" border="0" cellpadding="0" cellspacing="0">
		<tr class="NONE">
			<td colspan="2"></td>
		</tr>
		<tr class="SORT_AZ">
			<td colspan="2">
				<a class="filterwrapper" href="#">
					<span class="filterlabel"></span>
					<input type="hidden" class="filterelement" value="" />
				</a>
			</td>
		</tr>
		<tr class="SORT_ZA">
			<td colspan="2">
				<a class="filterwrapper" href="#">
					<span class="filterlabel"></span>
					<input type="hidden" class="filterelement" value="" />
				</a>
			</td>
		</tr>
		<tr class="FILTER_CHECKBOX">
			<td colspan="2">
				<div class="filterwrapper">
					<ul>
						<li><input type="checkbox" class="filterelement" /> <span class="filterlabel"></span></li>
					</ul>
				</div>
			</td>
		</tr>
		<tr class="FILTER_FROM">
			<td><span class="filterlabel"></span></td>
			<td><input class="filterelement" type="text" /></td>
		</tr>
		<tr class="FILTER_TO">
			<td><span class="filterlabel"></span></td>
			<td><input class="filterelement" type="text" /></td>
		</tr>
		<tr class="FILTER_TEXTSEARCH">
			<td><span class="filterlabel"></span></td>
			<td><input class="filterelement" type="text" /></td>
		</tr>
		<tr class="SUBMIT_BUTTON">
			<td colspan="2"></td>
		</tr>
		<tr class="SUBMIT_BUTTON_LIST">
			<td colspan="2">
				<div class="filterwrapper"></div>
			</td>
		</tr>
		<tr class="SHOW_LIGHTBOX_BUTTON">
			<td colspan="2"></td>
		</tr>
	</table>
</div>