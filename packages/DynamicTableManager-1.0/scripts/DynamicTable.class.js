var DynamicTable = new Class({	
	Implements: [Options, Events],
	Binds: ["SetElement", "GetContextMenuClassByType", "GetJSONVars", "GenerateTable", "RequestData", "RegisterFilter", "GetJSONInformationVars", "GetJSONHeaderVars"],

	element: null, 
	options: {
		viewID: null,
		jsonRequestFile: "",
		cssClasses: {
			htmlLayoutWrapper: ".dynamicTable_html_layout",
			filterIconNormal: ".filter_icon_normal",
			filterIconActive: ".filter_icon_active",
			loadingIcon: ".loading_icon",
			sortAsc: ".order_asc",
			sortDesc: ".order_desc",
			loadingIcon: ".loading_icon",
			informationTable: {
				tableClass: ".dynamicTable_tableInfo",	// CSS Class
				content: {					
					icon: ".icon",
					headline: ".headline",
					entries: ".entries",
					entriesperpage: ".entriesperpage",
					entriesperpagesubmit: ".entriesperpagesubmit",
					page_input: ".page_input",
					page_count: ".page_count",
					search_input: ".search_input",
					search_button: ".search_button",
					search_clear: ".search_clear",
					button_minimize: ".button_minimize",
					button_maximize: ".button_maximize",
					table_minimized_state: ".table_minimized_state",
					column_visibility_wrapper: ".column_visibility_wrapper",
					column_visibility_button: ".column_visibility_button",
					config_wrapper: ".config_wrapper",
					config_button: ".config_button"
				}
			},
			mainTable: {
				tableClass: ".dynamicTable_mainTable",	// CSS Class
				thead: {
					mainCSSTag: "thead", 	// CSS Tag Or Class
					rowCSSTag: "th"			// CSS Tag Or Class
				},
				tbody: {
					mainCSSTag: "tbody",	// CSS Tag Or Class
					rowCSSTag: "td"			// CSS Tag Or Class
				}
			},
			contextMenu: {
				tableClass: ".dynamicTable_contextMenu",
				typeClasses: [
					{ type: FilterTypes.TYPE_NONE, 					value: ".NONE" },
					{ type: FilterTypes.TYPE_SORT_AZ, 				value: ".SORT_AZ" },
					{ type: FilterTypes.TYPE_SORT_ZA, 				value: ".SORT_ZA" },
					{ type: FilterTypes.TYPE_FILTER_CHECKBOX,		value: ".FILTER_CHECKBOX" },
					{ type: FilterTypes.TYPE_FILTER_FROM, 			value: ".FILTER_FROM" },
					{ type: FilterTypes.TYPE_FILTER_TO, 			value: ".FILTER_TO" },
					{ type: FilterTypes.TYPE_FILTER_TEXTSEARCH,		value: ".FILTER_TEXTSEARCH" },
					{ type: FilterTypes.TYPE_SUBMIT_BUTTON, 		value: ".SUBMIT_BUTTON"},
					{ type: FilterTypes.TYPE_SUBMIT_BUTTON_LIST, 	value: ".SUBMIT_BUTTON_LIST"},
					{ type: FilterTypes.TYPE_SHOW_LIGHTBOX_BUTTON, 	value: ".SHOW_LIGHTBOX_BUTTON"}
				],
				contentClasses: {
					hint: "hint",
					label: ".filterlabel",
					element: ".filterelement",
					wrapper: ".filterwrapper" // Special for FilterTypes.TYPE_FILTER_CHECKBOX and FilterTypes.TYPE_SORT_AZ / ZA
				}
			}
		}
	},
	
	filters: {},
	information_elements: {},
	table: null,
	request: null,
	optimized_mode: null,
	tableData: {},
	
	onsuccesscallback: null,
	loading_icon_data: {},
	
	initialize: function (element, options)
	{
		this.SetElement(element);
		this.setOptions(options);
		
		this.RequestData({
			type: DynamicTableRequestTypes.TYPE_INITIAL
		});
		
		this.GenerateTable({
			header:{},
			request_information: {},
			information:{
				uniqueViewId:"",
				headline: "Loading",
				icon: ""
			},
			content:{}
		});
		this.ShowLoading();
	},
	
	/**
	 * Injects the loading layer to the element
	 */
	ShowLoading: function()
	{
		var img = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.loadingIcon)[0].clone(true, false);		
		if (typeof(this.information_elements["icon"]) != "undefined")
		{
			this.loading_icon_data["old_icon_src"] = this.information_elements["icon"].src;
			this.loading_icon_data["old_icon_class"] = this.information_elements["icon"].get("class");
			
			this.information_elements["icon"].src = img.src;
			this.information_elements["icon"].set("class", img.get("class"));
		}
		else
		{
			this.loading_icon_data["img"] = img;
			
			img.inject(this.element, "top");
		}
		if (this.table!= null) this.table.morph({opacity: 0.5});
		/*
		var table = this.element.getElement(this.options.cssClasses.mainTable.tableClass);
		
		if (table == null) table = this.element;
		
		var height = table.getSize().y;
		
		img.setPosition(table.getPosition());
		img.style.width = table.getSize().x+"px";
		img.style.height = ((height <= 50) ? 50 : height)+"px";
		*/
	},
	
	HideLoading: function()
	{
		if (this.table!= null) this.table.morph({opacity: 1.0});
		if (typeof(this.loading_icon_data["old_icon_src"]) != "undefined")
		{
			this.information_elements["icon"].src = this.loading_icon_data["old_icon_src"];
			this.information_elements["icon"].set("class", this.loading_icon_data["old_icon_class"]);
		}
		else
		{
			this.loading_icon_data["img"].destroy();
		}
		this.loading_icon_data = {};
	},
	
	/**
	 * Function to set the element by id or object
	 * @access 	public
	 * @param	String|Object	id_or_object	The id or object
	 * @return 	void
	 */
	SetElement: function (id_or_object) 
	{
		switch (String(typeof(id_or_object)).toUpperCase())
		{
			case "STRING":
				if (id_or_object != "")
				{
					this.element = $(id_or_object)
				}
				break;
			case "OBJECT":
				this.element = id_or_object;
				break;
			default:
				this.element = null;
				break;
		}
	},
	
	/**
	 * Function to get the contextmenu classes by the type
	 * @access 	public
	 * @param	int		type	The type
	 * @return 	String|null		The CSS class or null (when entrie is not set)
	 */
	GetContextMenuClassByType: function (type)
	{
		for (entry in this.options.cssClasses.contextMenu.typeClasses)
		{
			if (entry.type == type) return entry.value;
		}
		return null;
	},
	
	/**
	 * Function to generate the table from tableData
	 * @access 	public
	 * @param 	Object 	tableData	The Table Data Object (Request response)
	 * @return void
	 */
	GenerateTable: function(tableData)
	{
		// tableData.header = tableData.information;
		this.tableData = tableData;
		
		if (typeof(tableData.request_information) != "undefined")
		{
			this.optimized_mode = tableData.request_information.session_active;
		}
		else
		{
			this.optimized_mode = false;
		}
		
		this.options.viewID = tableData.information.uniqueViewId;
		// ELEMENT.clone([keepcontents, keepid])
		this.element.empty();

		////////////////////////////////////
		// Get Info Table Elements
		var opt_css_infotable = this.options.cssClasses.informationTable;
		var info_table = $$(this.options.cssClasses.htmlLayoutWrapper+" "+opt_css_infotable.tableClass)[0].clone(true, false);
		
		////////////////////////////////////
		// Get Content Table Elements
		this.table = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.mainTable.tableClass)[0].clone(false, false);
		
		var thead = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.mainTable.tableClass+" "+this.options.cssClasses.mainTable.thead.mainCSSTag)[0].clone(false, false);
		var thead_row = new Element("tr");
		var thead_data = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.mainTable.tableClass+" "+this.options.cssClasses.mainTable.thead.mainCSSTag+" "+this.options.cssClasses.mainTable.thead.rowCSSTag)[0].clone(false, false);
		
		var tbody = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.mainTable.tableClass+" "+this.options.cssClasses.mainTable.tbody.mainCSSTag)[0].clone(false, false);
		var tbody_data = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.mainTable.tableClass+" "+this.options.cssClasses.mainTable.tbody.mainCSSTag+" "+this.options.cssClasses.mainTable.tbody.rowCSSTag);

		////////////////////////////////////
		// Generate the Info Table
		var info_tbody_tds = new Array();
		// Icon
		if (typeof(tableData.information.icon) != "undefined")
		{ 
			info_table.getElement(opt_css_infotable.content.icon).src = tableData.information.icon;
			
			var icon = info_table.getElement(opt_css_infotable.content.icon);
			icon.src = tableData.information.icon;
			
			this.information_elements["icon"] = icon;
		}
		
		// Headline
		if (typeof(tableData.information.headline) != "undefined")
		{
			var headline = info_table.getElement(opt_css_infotable.content.headline);
			headline.innerHTML = tableData.information.headline;
			
			this.information_elements["headline"] = headline;
		}
		
		// Einträge
		if (typeof(tableData.information.entriesCount) != "undefined")
		{
			var entriesCount = info_table.getElement(opt_css_infotable.content.entries);
			entriesCount.innerHTML = tableData.information.entriesCount;
			
			this.information_elements["entriescount"] = entriesCount;
		}

		// Einträge / Seite
		if (typeof(tableData.information.entriesPerPage) != "undefined")
		{
			var entriesperpage = info_table.getElement(opt_css_infotable.content.entriesperpage);
			var entriesperpagesubmit = info_table.getElement(opt_css_infotable.content.entriesperpagesubmit);
			
			entriesperpage.value = tableData.information.entriesPerPage;

			entriesperpagesubmit.addEvent("click", function(){
				this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
			}.bind(this));

			this.information_elements["entriesperpage"] = entriesperpage;
			this.information_elements["entriesperpagesubmit"] = entriesperpagesubmit;
			
			entriesperpage.addEvent("keydown", function(e){
				var key_code = e.event.keyCode | e.keyCode;
				if (key_code == 13) this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
			}.bind(this));
		}
		
		// Seite x / y
		if (typeof(tableData.information.currentPage) != "undefined" && typeof(tableData.information.pageCount) != "undefined")
		{
			var page_input = info_table.getElement(opt_css_infotable.content.page_input);
			var page_count = info_table.getElement(opt_css_infotable.content.page_count);
			
			page_input.value = tableData.information.currentPage;
			page_count.innerHTML = tableData.information.pageCount;
			
			this.information_elements["page_input"] = page_input;
			this.information_elements["page_count"] = page_count;
			
			page_input.addEvent("keydown", function(e){
				var key_code = e.event.keyCode | e.keyCode;
				if (key_code == 13) this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
			}.bind(this));
		}
		
		// Suche
		if (typeof(tableData.information.search) != "undefined")
		{
			var search_input = info_table.getElement(opt_css_infotable.content.search_input);
			var search_button = info_table.getElement(opt_css_infotable.content.search_button);
			var search_clear = info_table.getElement(opt_css_infotable.content.search_clear);
			
			search_input.value = tableData.information.search;
			search_button.addEvent("click", function(){
				this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
			}.bind(this));
			search_clear.addEvent("click", function(){
				this.information_elements["search_input"].value = "";
				this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
			}.bind(this));
			
			this.information_elements["search_input"] = search_input;
			this.information_elements["search_button"] = search_button;
			this.information_elements["search_clear"] = search_clear;
		
			search_input.addEvent("keydown", function(e){
				var key_code = e.event.keyCode | e.keyCode;
				if (key_code == 13) this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
			}.bind(this));
		}
		
		// Spalten ein-/ausblenden
		/************************************************************************************************************************************************************************************************************************/
		var column_visibility_button = info_table.getElement(opt_css_infotable.content.column_visibility_button);
		var column_visibility_wrapper = info_table.getElement(opt_css_infotable.content.column_visibility_wrapper);
		var table_contextmenu = new ContextMenu(column_visibility_button, column_visibility_wrapper, this, {x:-250,y:0});
		table_contextmenu.buttonText = "Anwenden";
		
		// Config
		/************************************************************************************************************************************************************************************************************************/
		var config_button = info_table.getElement(opt_css_infotable.content.config_button);
		var config_wrapper = info_table.getElement(opt_css_infotable.content.config_wrapper);
		var table_contextmenu2 = new ContextMenu(config_button, config_wrapper, this, {x:-250,y:0});
		table_contextmenu2.buttonText = "Anwenden";
		
		// Minimiert / Maximiert
		if (typeof(tableData.information.minimized) != "undefined")
		{
			var button_minimize = info_table.getElement(opt_css_infotable.content.button_minimize);
			var button_maximize = info_table.getElement(opt_css_infotable.content.button_maximize);
			
			this.information_elements["minimized"] = info_table.getElement(opt_css_infotable.content.table_minimized_state);
			this.information_elements["minimized"].value = tableData.information.minimized;
			
			if (tableData.information.minimized == false)
			{
				button_maximize.setProperty("class", opt_css_infotable.content.button_maximize.replace(/^\./g, "")+"_active");
				button_minimize.addEvent("click", function(){
					this.information_elements["minimized"].value = true;
					this.table.style.display = "none";
					this.tableData.information.minimized = true;
					this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
					this.GenerateTable(this.tableData);
				}.bind(this));
			}
			else
			{
				button_minimize.setProperty("class", opt_css_infotable.content.button_minimize.replace(/^\./g, "")+"_active");
				button_maximize.addEvent("click", function(){
					this.information_elements["minimized"].value = false;
					this.table.style.display = "table";
					this.tableData.information.minimized = false;
					this.RequestData({ type:DynamicTableRequestTypes.TYPE_INFORMATION });
					this.GenerateTable(this.tableData);
				}.bind(this));
			}
		}
		
		
		////////////////////////////////////
		// Generate the Main Table Header
		var header_td = null;
		var contextmenu = null;
		
		var columnVisibilityStateData = {
			header:{
				id: "COLUMNVISIBILITYSTATE"
			},
			filter:
			{
				id: "COLUMNVISIBILITYSTATE",
				data:[]
			}
		};
		
		var filter_button;
		for (var i=0; i<this.tableData.header.length; i++)
		{
			columnVisibilityStateData.filter.data.push({
				id: i,
				checked: this.tableData.header[i].visible,
				text: this.tableData.header[i].name
			});
			
			if (typeof(this.tableData.header[i]) == "undefined" || this.tableData.header[i].visible == false) continue;
			
			var header_text = new Element("span", { html: this.tableData.header[i].name });
			

			if (this.tableData.header[i].filteractive == false)
			{
				filter_button = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.filterIconNormal)[0].clone(true, false);
				filter_button.style.visibility="hidden";
			}
			else
			{
				filter_button = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.filterIconActive)[0].clone(true, false);
				filter_button.style.visibility="visible";
			}
			filter_button.id = "filter_button_"+i;
			
			var sort_icon = "";
			if (this.tableData.header[i].sortable == true)
			{
				header_text.set('class', 'header_text_sortable');
			}
			if (this.tableData.header[i].sortable == true && this.tableData.header[i].sortdirection != 0)
			{
				//style.cursor = "pointer";
				if (this.tableData.header[i].sortdirection == 1)
				{
					sort_icon = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.sortAsc)[0].clone(true, false);
				}
				else
				{
					sort_icon = $$(this.options.cssClasses.htmlLayoutWrapper+" "+this.options.cssClasses.sortDesc)[0].clone(true, false);
				}
				sort_icon.id = "sort_icon_"+i;
				sort_icon.style.visibility="visible";
			}

			header_text.set('rel', i);
			header_text.addEvent("click", function(e){
				var index = parseInt(e.target.get('rel'));
				
				for (var i=0; i<this.tableData.header.length; i++)
				{
					if (index == i) continue;
					this.tableData.header[i].sortdirection = 0;
				}
				
				if (this.tableData.header[index].sortdirection == 0)
				{
					this.tableData.header[index].sortdirection = 1;
				}
				else
				{
					if (this.tableData.header[index].sortdirection == 1)
					{
						this.tableData.header[index].sortdirection = 2;
					}
					else
					{
						this.tableData.header[index].sortdirection = 1;
					}
				}
				
				this.RequestData({type: DynamicTableRequestTypes.TYPE_HEADER, index:index});
			}.bind(this));
			
			header_td = thead_data.clone(false, false).adopt( 
				(new Element("table", {border:0, cellpadding:0, cellspacing:0, "class":"header_table", styles:{width:"100%"}})).adopt(
					(new Element("tbody")).adopt(
						[
							(new Element("tr")).adopt(
								[
									(new Element("td")).adopt(header_text),
									(new Element("td")).adopt(filter_button)
								]
							),
							(new Element("tr")).adopt(
								(new Element("td", {coslpan:2, styles:{textAlign:"center"}})).adopt(sort_icon)
							)
						]
					)
				)
			);
			thead_row.adopt( 
				header_td
			);

			if (this.tableData.header[i].filters.length > 0)
			{
				
				
				header_td.set('rel', i);
				
				if (this.tableData.header[i].filteractive == false)
				{
					header_td.addEvent("mouseover", function(){ $("filter_button_"+this.get('rel')).style.visibility="visible"; });
					header_td.addEvent("mouseleave", function(){ $("filter_button_"+this.get('rel')).style.visibility="hidden"; });
				}

				//header_td.adopt(filter_button);
				
				contextmenu = new ContextMenu(filter_button, header_td, this, {x:0,y:0});
				
				for (var a=0; a<this.tableData.header[i].filters.length; a++)
				{
					for (var b=0; b<this.tableData.header[i].filters[a].length; b++)
					{
						if (this.tableData.header[i].filters[a][b] == null) continue;
						contextmenu.AddContextMenu(this.tableData.header[i].filters[a][b].type, {
							header: this.tableData.header[i],
							filter: this.tableData.header[i].filters[a][b]
						});
					}
				}
			}
		}
		
		table_contextmenu.AddContextMenu(FilterTypes.TYPE_FILTER_CHECKBOX, columnVisibilityStateData);
		
		table_contextmenu.button1Text = "Anwenden";
		table_contextmenu.button2Text = "Zurücksetzen";
		
		
	
		// tableData.information.configurations
		if (typeof(this.tableData.information.configurations) != "undefined")
		{
			var FilterData = {
				header:{
					id: "loadconfiguration"
				},
				filter:
				{
					id: "loadconfiguration",
					data:[]
				}
			};
			for (var i=0; i<this.tableData.information.configurations.length; i++)
			{
				if (typeof(this.tableData.information.configurations[i].ligtboxurl) != "undefined") continue;
				FilterData.filter.data.push({ 
					id: this.tableData.information.configurations[i].id,
					text: this.tableData.information.configurations[i].name 
				});
			}
			if (FilterData.filter.data.length > 0) table_contextmenu2.AddContextMenu(FilterTypes.TYPE_SUBMIT_BUTTON_LIST, FilterData);

			for (var i=0; i<this.tableData.information.configurations.length; i++)
			{
				if (typeof(this.tableData.information.configurations[i].ligtboxurl) != "undefined")
				{
					table_contextmenu2.AddContextMenu(FilterTypes.TYPE_SHOW_LIGHTBOX_BUTTON, {
						text: this.tableData.information.configurations[i].name, 
						url: this.tableData.information.configurations[i].ligtboxurl
					});
					break;
				}
			}
		}
		

		thead.adopt(thead_row);
		
		////////////////////////////////////
		// Generate the Main Table Content		
		var row = null;
		var tdata = null;
		var clone_el = null;
		var tbody_index;
		var style_key = "";
		var style_val = "";
		// loop through all data rows
		for (var i=0; i<this.tableData.content.length; i++)
		{			
			row = new Element("tr");
			// group member
			if (typeof(this.tableData.content[i].belong_to_group_id)!="undefined")
			{
				row.addClass('belong_to_group_'+this.tableData.content[i].belong_to_group_id);
				row.setStyle("display", "none");
			}
			
			tdata = tbody_data[((i+1) % tbody_data.length)].clone(false, false);

			tbody_index = (i+1);
			
			// loop through all columns
			for (var a=0; a<this.tableData.header.length; a++)
			{
				// skip hidden columns
				if (typeof(this.tableData.header[a]) == "undefined" || this.tableData.header[a].visible == false) continue;
				
				clone_el = tdata.clone(false, false);
				
				// set special css class for GROUP
				if (typeof(this.tableData.content[i].group_id)!="undefined")
				{
					clone_el.addClass('group');
					row.addClass('group_' + this.tableData.content[i].group_id);
				}
				// set special css class for GROUPELEMENT
				if (typeof(this.tableData.content[i].belong_to_group_id)!="undefined")
				{
					clone_el.set('class', 'groupelement');
				}
				// assign column css styles
				if (this.tableData.header[a].styles != null && typeof(this.tableData.header[a].styles.length) == "undefined")
				{
					for (key in this.tableData.header[a].styles)
					{
						style_key = String(key).replace(/(\-[a-z])/g, function($1){return $1.toUpperCase().replace('-','');});
						style_val = String(this.tableData.header[a].styles[key]);
						
						clone_el.setStyle(style_key, style_val);
					}
				}
				
				// write row content for this column
				var cell_content = "";
				for (var b=0; b<this.tableData.content[i].column_data.length; b++)
				{
					
					if (this.tableData.content[i].column_data[b].column_id==this.tableData.header[a].id)
					{
						cell_content = this.tableData.content[i].column_data[b].content;
						break;
					}
				}
				
				row.adopt(
					clone_el.adopt( 
						new Element("span", { html: cell_content })
					)
				);
			}

			tbody.adopt(row);
		}
		
		////////////////////////////////////
		// Fill the Tables
		this.table.adopt(thead);
		this.table.adopt(tbody);
		
		if (this.tableData.information.minimized == true)this.table.style.display = "none";
		
		////////////////////////////////////
		// Add the tables to the Content
		this.element.adopt(info_table);
		this.element.adopt(this.table);
		
		// call callback function
		if (typeof(tableData.information.onLoadCallbackFunction) != "undefined")
		{
			eval(tableData.information.onLoadCallbackFunction);
		}
		
	},
	
	/**
	 * Toggle the visibility of the specified group rows
	 * @var string group_id
	 */
	ToggleGroupElements: function(group_id, icon_path)
	{
		// TODO: TR-Tag hat kein Name-attribute --> dies könnte zu prpblemen führen!! Im FF geht's nicht!!
		// var elementsToToggle = document.getElementsByName('groupid_'+group_id);

		var elementsToToggle = $$('tr[class=belong_to_group_'+group_id+']');

		if (elementsToToggle.length <= 0)
		{
			if (this.onsuccesscallback == null)
			{
				this.onsuccesscallback = function(data){
					if (data != null)
					{
						var group_id = data.groupID;
						var tr = null;
						var td = null;
						
						var injectafter = $$('tr[class=group_'+group_id+']')[0];
						
						// Create the content
						for (var i = 0, n = data.content.length; i < n; i++)
						{
							var tr = new Element("tr", { 'class': 'belong_to_group_' + group_id });
							
							for (var p = 0, q = data.header.length; p < q; p++)
							{
								if (typeof(data.header[p]) == "undefined" || data.header[p].visible == false) continue;
								
								for (var a = 0, b = data.content[i].column_data.length; a < b; a++)
								{
									if (typeof(data.header[p]) == "undefined" || data.header[p].visible == false) continue;
									
									if (data.content[i].column_data[a].column_id == data.header[p].id)
									{
										// Create td element
										td = new Element("td", { 'class': 'groupelement' }).adopt(new Element("span", { html: data.content[i].column_data[a].content }));
										
										// Add styles from header
										for (key in data.header[p].styles)
										{
											style_key = String(key).replace(/(\-[a-z])/g, function($1){return $1.toUpperCase().replace('-','');});
											style_val = String(data.header[p].styles[key]);
											td.setStyle(style_key, style_val);
										}
										
										tr.adopt(td);
										break;
									}
								}
							}
							if (injectafter != null) tr.inject(injectafter, 'after');
						}

						if (typeof($('img_'+group_id))!="undefined" && $('img_'+group_id) != null)
						{
							$('img_'+group_id).src= icon_path + 'group_close.png';
							$('img_'+group_id).onmouseover=function(){ this.src = icon_path + 'group_close_over.png'; }
							$('img_'+group_id).onmouseout=function(){ this.src = icon_path + 'group_close.png'; }
						}
					}					
				}.bind(this);
			}
			
			this.RequestData({
				type: DynamicTableRequestTypes.TYPE_LOAD_GROUP,
				groupID: group_id
			});
		}
		else if (elementsToToggle.length > 0)
		{
			/*var displayType = (elementsToToggle[0].style.display=="" || elementsToToggle[0].style.display=="undefined" ? "none" : "");
			for (var a=0; a<elementsToToggle.length; a++)
			{
				elementsToToggle[a].style.display=displayType;
			}
			if (typeof($('img_'+group_id))!="undefined")
			{
				$('img_'+group_id).src=icon_path+(displayType!="" ? 'group_open.png' : 'group_close.png');
				$('img_'+group_id).onmouseover=function(){ this.src = icon_path+(displayType!="" ? 'group_open_over.png' : 'group_close_over.png') }
				$('img_'+group_id).onmouseout=function(){ this.src = icon_path+(displayType!="" ? 'group_open.png' : 'group_close.png') }
			}*/
			for (var a=0; a<elementsToToggle.length; a++) { elementsToToggle[a].destroy(); }
			
			if (typeof($('img_'+group_id))!="undefined" && $('img_'+group_id) != null)
			{
				$('img_'+group_id).src=icon_path + 'group_open.png';
				$('img_'+group_id).onmouseover=function(){ this.src = icon_path + 'group_open_over.png'; }
				$('img_'+group_id).onmouseout=function(){ this.src = icon_path + 'group_open.png'; }
			}
		}
	},
	
	GetJSONVars: function(manual_filters, specificHeaderID)
	{		
		var json_vars = null;
		if (typeof(manual_filters) == "undefined" || manual_filters == null)
		{
			var json_filters = new Array();
			for (viewID in this.filters)
			{
				for (headerID in this.filters[viewID])
				{
					if (typeof(specificHeaderID) != "undefined" && specificHeaderID != null && specificHeaderID != headerID) continue;
					
					if (typeof(json_filters[headerID]) == "undefined") json_filters[headerID] = new Array();
					for (filters in this.filters[viewID][headerID])
					{						
						if (typeof(json_filters[headerID][filters]) == "undefined") json_filters[headerID][filters] = new Array();
						
						switch (parseInt(this.filters[viewID][headerID][filters].filterType))
						{
							case FilterTypes.TYPE_FILTER_CHECKBOX:
								json_filters[headerID][filters] = {
									id: this.filters[viewID][headerID][filters].id,
									data: new Array()
								};
								for (var i=0; i<this.filters[viewID][headerID][filters].elements.length; i++)
								{
									json_filters[headerID][filters].data[i] = {
										text: this.filters[viewID][headerID][filters].elements[i].name,
										type: parseInt(filters),
										id: this.filters[viewID][headerID][filters].elements[i].value,
										//value: this.filters[viewID][headerID][filters].elements[i].value,
										active: (this.filters[viewID][headerID][filters].elements[i].checked) ? 1 : 0
									};
									
									// alert(i+" --- "+this.filters[viewID][headerID][filters].elements[i].name+" --- "+((this.filters[viewID][headerID][filters].elements[i].checked) ? "true" : "false"));
								}
								break;
							case FilterTypes.TYPE_FILTER_FROM:
							case FilterTypes.TYPE_FILTER_TO:
							case FilterTypes.TYPE_FILTER_TEXTSEARCH:
								json_filters[headerID][filters] = {
									type: parseInt(filters),
									id: this.filters[viewID][headerID][filters].id,
									value: this.filters[viewID][headerID][filters].elements.value
								};
								break;
							case FilterTypes.TYPE_SORT_AZ:
							case FilterTypes.TYPE_SORT_ZA:
								json_filters[headerID][filters] = {
									type: parseInt(filters),
									id: this.filters[viewID][headerID][filters].id,
									active: this.filters[viewID][headerID][filters].elements.value
								};
								break;
							default:
								break;
						}
					}
				}
			}
			json_vars = {
				viewID: this.options.viewID,
				filters: json_filters
			};
		}
		else
		{
			if (manual_filters == this.options.viewID)
			{
				json_vars = {
					viewID: this.options.viewID
				};
			}
			else
			{
				if (specificHeaderID != "")
				{
					json_vars = {
						viewID: this.options.viewID,
						filters: manual_filters,
						headerID: specificHeaderID
					};
				}
				else
				{
					json_vars = {
						viewID: this.options.viewID,
						filters: manual_filters
					};
				}
			}
		}
		return json_vars;
	},
	
	GetJSONInformationVars: function()
	{
		var json_vars = {information: {}};
		for (element in this.information_elements)
		{
			if (this.information_elements[element].tagName == "INPUT")
			{
				json_vars.information[element] = this.information_elements[element].value;
			}
		}
		json_vars.viewID = this.options.viewID;
		
		return json_vars;
	},
	
	GetJSONHeaderVars: function(index)
	{
		var json_vars = {header: []};
		json_vars.header[0] = this.tableData.header[index];
		json_vars.header[0].filters = new Array();
		json_vars.viewID = this.options.viewID;
		return json_vars;
	},
	
	RequestData: function(args)
	{		
		var json_vars = {};
		if (typeof(args) != "undefined" && typeof(args.headerID)!="undefinded" && args.headerID == "COLUMNVISIBILITYSTATE")
		{
			for (var x=0; x<this.filters[this.options.viewID].COLUMNVISIBILITYSTATE[0].elements.length; x++)
			{
				this.tableData.header[x].visible = (this.filters[this.options.viewID].COLUMNVISIBILITYSTATE[0].elements[x].checked == true) ? true : false;
			}
			this.GenerateTable(this.tableData);
			
			this.filters[this.options.viewID].COLUMNVISIBILITYSTATE = new Array();
			
			if (args.type == DynamicTableRequestTypes.TYPE_FILTER_RESET)
			{
				json_vars = {
					viewID: this.options.viewID,
					resetheaderfilters: 1
				};
			}
			else
			{
				json_vars = {
					viewID: this.options.viewID,
					header: this.tableData.header
				};
			}
		}
		else
		{
			if (this.optimized_mode == null || this.optimized_mode != false)
			{
				if (typeof(args) == "undefined" || args == null) return false;			
				switch (args.type)
				{
					case DynamicTableRequestTypes.TYPE_INITIAL: // Initial Request
						json_vars = this.GetJSONVars(this.options.viewID, null); 
						break;
						
					case DynamicTableRequestTypes.TYPE_FILTER_ALL: // Send All Filters
						json_vars = this.GetJSONVars(null, null);
						break;
						
					case DynamicTableRequestTypes.TYPE_FILTER_SPECIFIC: // Specific Filters (with header information)
						json_vars = this.GetJSONVars(null, args.headerID);
						break;
					case DynamicTableRequestTypes.TYPE_FILTER_RESET:
						json_vars = {
							header: [{id: args.headerID, reset: 1}],
							viewID: this.options.viewID
						};
						break;
						
					case DynamicTableRequestTypes.TYPE_FILTER_MANUAL: // Header Information versenden
						json_vars = this.GetJSONVars(args.manualFilter, args.headerID);
						break;
						
					case DynamicTableRequestTypes.TYPE_INFORMATION: // Header Information versenden
						json_vars = this.GetJSONInformationVars();
						break;
						
					case DynamicTableRequestTypes.TYPE_HEADER:
						json_vars = this.GetJSONHeaderVars(args.index);
						break;
						
					case DynamicTableRequestTypes.TYPE_SUBMIT_BUTTON_LIST:
						json_vars = {
							loadconfiguration: true,
							viewID: this.options.viewID,
							configurationID: args.buttonID
						};
						break;
					
					case DynamicTableRequestTypes.TYPE_LOAD_GROUP:
						json_vars = {
							groupID: args.groupID,
							type: DynamicTableRequestTypes.TYPE_LOAD_GROUP,
							viewID: this.options.viewID,
							header: this.tableData.header,
						};
						break;
						
					case DynamicTableRequestTypes.TYPE_NONE:
					default:
						json_vars = this.GetJSONVars();
						break;
				}
			}
			else
			{
				json_vars = {};
				var filters = this.GetJSONVars();
				for (filter in filters)
				{
					json_vars[filter] = filters[filter];
				}
				var headers = this.GetJSONInformationVars();
				for (header in headers)
				{
					json_vars[header] = headers[header];
				}
			}
		}
		
		this.ShowLoading();
		
		if (this.request == null)
		{
			this.request = new Request.JSON({
				url: this.options.jsonRequestFile,
				method: "post",
				link: "chain",
				async: true,
				timeout: 0,
				onSuccess: function(responseJSON, responseText)
				{
					if (this.onsuccesscallback != null && typeof(responseJSON.groupID) != "undefined" && responseJSON.groupID != null) { 
						this.onsuccesscallback(responseJSON); 
						this.HideLoading();
					}
					if (responseJSON.request_information.reload_table == true)
					{
						this.GenerateTable(responseJSON);
					}
				}.bind(this),
				onError: function(response_text, error_message){
					if (typeof(console) != "undefined" && typeof(console.log) != "undefined"){try{ console.log("Request Error: "+error_message); }catch(e){}}
					this.HideLoading();
				}.bind(this),
				onFailure: function(xhr){
					if (typeof(console) != "undefined" && typeof(console.log) != "undefined"){try{ console.log("Request Failure"); }catch(e){}}
					this.HideLoading();
				}.bind(this)
			});
		}
		this.filters = new Array();
		this.request.post(json_vars);		
	},
	
	RegisterFilter: function(tableID, headerID, filterID, filterType, elements)
	{
		if (!this.filters[tableID]) this.filters[tableID] = {};
		if (!this.filters[tableID][headerID]) this.filters[tableID][headerID] = new Array(); //{};
		//if (!this.filters[tableID][headerID][filterType]) this.filters[tableID][headerID][filterType] = {};
		
		this.filters[tableID][headerID].push({
			tableID: tableID,
			id: filterID,
			headerID: headerID,
			filterType: filterType,
			elements: elements
		});
	}
});