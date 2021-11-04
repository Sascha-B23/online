var ContextMenu = new Class({	
	Implements: [Options, Events],
	Binds: ["Show", "AddContextMenu", "GetHTML", "DropData"],	
	
	element: null,
	wrapper: null,
	dynamicTable: null,
	table: null,
	old_table: null,
	addbutton: false,
	button1Text: "Filter anwenden",
	button2Text: "Filter zurücksetzen",
	
	position_adjuster: {x:0, y:0},
	
	contextmenus: new Array(),
	
	headerID: null,
	
	initialize: function (element, wrapper, dynamicTable, position_adjuster)
	{
		this.element = document.id(element);
		this.wrapper = document.id(wrapper);
		this.dynamicTable = dynamicTable;
		
		if (typeof(position_adjuster) != "undefined") this.position_adjuster = position_adjuster;
		
		this.element.addEvent("click", this.Show.bind(this));
	},
	
	Show: function(event)
	{
		if (event.target == this.element)
		{
			this.table = this.GetHTML();
			this.wrapper.adopt(this.table);
			document.addEvent("click", this.DropData);
		}
	},
	
	DropData: function(event)
	{
		if (event.target != this.element && !this.wrapper.contains(event.target))
		{
			this.Destroy(true);
			document.removeEvent("click", this.DropData);
		}
	},
	
	Destroy:function()
	{
		if (this.table != null)
		{
			this.table.empty();
			this.table.destroy(); 
			this.table=null;
		}
	},
	
	AddContextMenu: function (type, data)
	{
		if (typeof(data.header) != "undefined")
		{
			this.headerID = data.header.id;
		}
		var cssClasses = this.dynamicTable.options.cssClasses.contextMenu;
		
		var returnData = null;
		switch (type)
		{
			case FilterTypes.TYPE_SORT_AZ:
			case FilterTypes.TYPE_SORT_ZA:
				returnData = new ContextMenuBarLink(type, this, this.dynamicTable, cssClasses, data);
				break;
				
			case FilterTypes.TYPE_FILTER_CHECKBOX:
				this.addbutton = true;
				returnData = new ContextMenuBarCheckboxList(type, this, this.dynamicTable, cssClasses, data);
				break;
				
			case FilterTypes.TYPE_FILTER_FROM:
			case FilterTypes.TYPE_FILTER_TO:
			case FilterTypes.TYPE_FILTER_TEXTSEARCH:
				this.addbutton = true;
				returnData = new ContextMenuBarTextSearch(type, this, this.dynamicTable, cssClasses, data);
				break;
			
			case FilterTypes.TYPE_SUBMIT_BUTTON_LIST:
				this.addbutton = false;
				returnData = new ContextMenuBarSubmitButtonList(type, this, this.dynamicTable, cssClasses, data);
				break;
			
			case FilterTypes.TYPE_SHOW_LIGHTBOX_BUTTON:
				this.addbutton = false;
				returnData = new ContextMenuBarLightBoxButton(type, this, this.dynamicTable, cssClasses, data);
				break;
				
			case FilterTypes.TYPE_NONE:
			default:
				returnData = new ContextMenuBarEmpty(type, this, this.dynamicTable, cssClasses, data);
				break;
		}
		if (returnData == null) return false;

		this.contextmenus.push(returnData);
		return true;
	},
	
	GetHTML: function()
	{
		if (this.addbutton == true)
		{
			var button = new ContextMenuBarButton(FilterTypes.TYPE_SUBMIT_BUTTON, this, this.dynamicTable, this.dynamicTable.options.cssClasses.contextMenu, {}, true);
			button.SetText1(this.button1Text);
			button.SetText2(this.button2Text);
			this.contextmenus.push(button);
			this.addbutton = false;
		}
		
		var table = $$(this.dynamicTable.options.cssClasses.contextMenu.tableClass)[0].clone(false, false);
		
		table.style.position = "absolute";
		table.style.left = (this.element.getCoordinates().left - 10 + this.position_adjuster.x) + "px";
		table.style.top = (this.element.getCoordinates().top - 10 + this.position_adjuster.y) + "px";
		
		var tbody = new Element("tbody");
		
		var content = null;
		for (var i=0; i<this.contextmenus.length; i++)
		{
			content = this.contextmenus[i].GetHTML();
			tbody.adopt(
				content
			);
		}
		
		table.adopt(tbody);
		return table;
	}
});

var ContextMenuBarEmpty = new Class({
	Implements: [Options, Events],
	Binds: ["GetHTML"],
	
	type: FilterTypes.TYPE_NONE,
	element: null,
	dynamicTable: null,
	contextmenu: null,
	id: "",
	
	initialize: function (type, contextmenu, dynamicTable, cssClasses, data)
	{
		this.type = type;
		this.contextmenu = contextmenu;
		this.dynamicTable = dynamicTable;
		this.id = "";
		for (var i=0; i<cssClasses.typeClasses.length; i++)
		{
			if (cssClasses.typeClasses[i].type == this.type)
			{
				this.element = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value)[0].clone(true, true);
				break;
			}
		}
	},
	GetHTML: function(){ return this.element; }
});

var ContextMenuBarLink = new Class({
	Implements: [Options, Events],
	Binds: ["GetHTML"],
	
	type: FilterTypes.TYPE_SORT_AZ,
	element: null,
	dynamicTable: null,
	contextmenu: null,
	input: null,
	id: "", 
	
	initialize: function (type, contextmenu, dynamicTable, cssClasses, data)
	{
		this.type = type;
		this.contextmenu = contextmenu;
		this.dynamicTable = dynamicTable;
		this.id = data.filter.id;
		
		for (var i=0; i<cssClasses.typeClasses.length; i++)
		{
			if (cssClasses.typeClasses[i].type == this.type)
			{
				this.element = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value)[0].clone(true, true);
				break;
			}
		}

		this.input = this.element.getElement(cssClasses.contentClasses.element);

		this.input.value = data.filter.active;
		this.dynamicTable.RegisterFilter(this.dynamicTable.options.viewID, data.header.id, this.id, this.type, this.input);
		
		this.element.getElement(cssClasses.contentClasses.label).innerHTML = data.filter.text;
		this.element.getElement(cssClasses.contentClasses.wrapper).addEvent("click", function(event){
			event.preventDefault();
			
			this.input.value = (this.input.value=="false") ? true : false;
			
			this.dynamicTable.RequestData({
				type: DynamicTableRequestTypes.TYPE_FILTER_MANUAL,
				headerID: data.header.id,
				manualFilter: this.type
			});
			this.contextmenu.Destroy();
		}.bind(this));
	},
	
	GetHTML: function(){ return this.element; }
});

var ContextMenuBarCheckboxList = new Class({
	Implements: [Options, Events],
	Binds: ["GetHTML"],
	
	type: FilterTypes.TYPE_FILTER_CHECKBOX,
	element: null,
	dynamicTable: null,
	contextmenu: null,
	
	chekboxes: new Array(),
	all_checkbox: null,
	
	id: "", 
	
	initialize: function (type, contextmenu, dynamicTable, cssClasses, data)
	{
		this.type = type;
		this.contextmenu = contextmenu;
		this.dynamicTable = dynamicTable;
		this.id = data.filter.id;
		for (var i=0; i<cssClasses.typeClasses.length; i++)
		{
			if (cssClasses.typeClasses[i].type == this.type)
			{
				this.element = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value)[0].clone(false, false);
				break;
			}
		}
		
		var wrapper = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value+" "+cssClasses.contentClasses.wrapper)[0].clone(false, false);
		var ul = new Element("ul");
		wrapper.adopt(ul);
		
		/**
		 * Add the "ALL Checkbox"
		 */
		this.all_checkbox = new Element("input",{
			type: "checkbox",
			checked: true,
			name: "Alle",
			value: "all"
		});
		this.all_checkbox.addEvent("click", function(){
			var status = this.all_checkbox.checked;
			for (var i=0; i<this.chekboxes.length; i++)
			{
				this.chekboxes[i].checked = status;
			}
		}.bind(this));		
		ul.adopt(
			new Element("li").adopt(
				this.all_checkbox,
				new Element("span", { html: "Alle" })
			)
		);

		/**
		 * Add the other checkboxes
		 */
		var checkbox = null;
		for (var i=0; i<data.filter.data.length; i++)
		{
			if (typeof(data.filter.data[i].checked) != "boolean")
			{
				if (data.filter.data[i].checked == "true" || data.filter.data[i].checked == "")
				{
					data.filter.data[i].checked = true;
				}
				else 
				{
					data.filter.data[i].checked = false;
				}
			}
			checkbox = new Element("input",{
				type: "checkbox",
				checked: data.filter.data[i].checked,
				name: data.filter.data[i].text,
				value: data.filter.data[i].id
			});
			this.chekboxes.push(checkbox);
			//checkbox.addEvent("click", function(event){  });
			
			/*checkbox.addEvent("click", function(event){
				// event.preventDefault();
				// this.dynamicTable.RequestData(data.header.id, this.type, event.target.value);
				
				// alert(this.dynamicTable.filters[this.dynamicTable.options.viewID][data.header.id][this.type].elements.length);
			}.bind(this));*/
			
			ul.adopt(
				new Element("li").adopt(
					checkbox,
					new Element("span", { html: data.filter.data[i].text })
				)
			);
		}
		
		this.dynamicTable.RegisterFilter(this.dynamicTable.options.viewID, data.header.id, this.id, this.type, this.chekboxes);
		
		this.element.adopt(new Element("td", {colspan: 2}).adopt(wrapper));
	},
	
	GetHTML: function(){ return this.element; }
});
	
var ContextMenuBarTextSearch = new Class({
	Implements: [Options, Events],
	Binds: ["GetHTML", "Validate"],
	
	type: FilterTypes.TYPE_FILTER_TEXTSEARCH,
	element: null,
	dynamicTable: null,
	contextmenu: null,
	regex: null,
	
	inputbox: null,
	id: "", 
	
	initialize: function (type, contextmenu, dynamicTable, cssClasses, data)
	{
		this.type = type;
		this.contextmenu = contextmenu;
		this.dynamicTable = dynamicTable;
		this.id = data.filter.id;
		this.regex = data.filter.regEx;
		
		for (var i=0; i<cssClasses.typeClasses.length; i++)
		{
			if (cssClasses.typeClasses[i].type == this.type)
			{
				this.element = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value)[0].clone(false, false);
				break;
			}
		}
		
		var label = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value+" "+cssClasses.contentClasses.label)[0].clone(false, false);
		this.inputbox = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value+" "+cssClasses.contentClasses.element)[0].clone(false, false);
		
		label.innerHTML = data.filter.text+":";
		this.inputbox.value = data.filter.value;
		
		this.inputbox.addEvent("keyup", function(e){
			if (this.Validate() == true)
			{
				var key_code = e.event.keyCode | e.keyCode;
				if (key_code == 13)
				{
					this.dynamicTable.RequestData({
						type: DynamicTableRequestTypes.TYPE_FILTER_SPECIFIC,
						headerID: this.contextmenu.headerID
					});
					this.contextmenu.Destroy();
				}
			}
		}.bind(this));
		
		var td_left = new Element("td");
		var td_right = new Element("td");
		
		td_left.adopt(label);
		td_right.adopt(this.inputbox);
		
		if (typeof(data.filter.hint) != "undefined" && data.filter.hint.trim() != "")
		{
			td_right.adopt([
				new Element("br"),
				new Element("span", {'class':cssClasses.contentClasses.hint, html:data.filter.hint})
			]);
		}

		this.dynamicTable.RegisterFilter(this.dynamicTable.options.viewID, data.header.id, this.id, this.type, this.inputbox);
		
		this.element.adopt(td_left, td_right);
	},
	
	Validate: function()
	{
		if (this.inputbox.value == "")
		{
			this.inputbox.set("class", "normal");
			return true;
		}
		if (typeof(this.regex) != "undefined" && this.regex.trim() != "")
		{
			var regex = new RegExp(this.regex, "g");
			if (this.inputbox.value.match(regex))
			{
				this.inputbox.set("class", "normal");
				return true;
			}
			this.inputbox.set("class", "error");
			return false;
		}
		return true;
	},
	
	GetHTML: function(){ return this.element; }
});

var ContextMenuBarButton = new Class({
	Implements: [Options, Events],
	Binds: ["GetHTML"],
	
	type: FilterTypes.TYPE_SUBMIT_BUTTON,
	element: null,
	dynamicTable: null,
	contextmenu: null,
	
	button1: null,
	button2: null,
	id: "",
	
	initialize: function (type, contextmenu, dynamicTable, cssClasses, data, reset)
	{
		this.type = type;
		this.contextmenu = contextmenu;
		this.dynamicTable = dynamicTable;
		//this.id = data.filter.id;
		
		for (var i=0; i<cssClasses.typeClasses.length; i++)
		{
			if (cssClasses.typeClasses[i].type == this.type)
			{
				this.element = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value)[0].clone(false, false);
				break;
			}
		}
		
		this.button1 = new Element("button", {html: "Filter anwenden", type:"button"});
		this.button1.addEvent("click", function(){
			var check = true;
			for (var i=0; i<this.contextmenu.contextmenus.length; i++)
			{
				if (this.contextmenu.contextmenus[i].type == FilterTypes.TYPE_FILTER_TEXTSEARCH || this.contextmenu.contextmenus[i].type == FilterTypes.TYPE_FILTER_FROM || this.contextmenu.contextmenus[i].type == FilterTypes.TYPE_FILTER_TO) 
				{
					if (this.contextmenu.contextmenus[i].Validate() == false) check = false;
				}
			}
			if (check == false) return false;
		
			this.dynamicTable.RequestData({
				type: DynamicTableRequestTypes.TYPE_FILTER_SPECIFIC,
				headerID: this.contextmenu.headerID
			});
			this.contextmenu.Destroy();
		}.bind(this));
		
		if (reset == true)
		{
			this.button2 = new Element("button", {html: "Filter zurücksetzen", type:"button"});
			this.button2.addEvent("click", function(){
				this.dynamicTable.RequestData({
					type: DynamicTableRequestTypes.TYPE_FILTER_RESET,
					headerID: this.contextmenu.headerID
				});
				this.contextmenu.Destroy();
			}.bind(this));
			this.element.adopt([
				new Element("td").adopt(this.button1),
				new Element("td").adopt(this.button2)
			]);
		}
		else
		{
			this.element.adopt(new Element("td", {colspan:2}).adopt(this.button1));
		}
	},
	SetText1: function(text)
	{
		this.button1.innerHTML = text;
	},
	SetText2: function(text)
	{
		this.button2.innerHTML = text;
	},
	GetHTML: function(){ return this.element; }
});

var ContextMenuBarSubmitButtonList = new Class({
	Implements: [Options, Events],
	Binds: ["GetHTML"],
	
	type: FilterTypes.TYPE_SUBMIT_BUTTON_LIST,
	element: null,
	dynamicTable: null,
	contextmenu: null,
	
	buttons: new Array(),
	all_checkbox: null,
	
	id: "", 
	
	initialize: function (type, contextmenu, dynamicTable, cssClasses, data)
	{
		this.type = type;
		this.contextmenu = contextmenu;
		this.dynamicTable = dynamicTable;
		for (var i=0; i<cssClasses.typeClasses.length; i++)
		{
			if (cssClasses.typeClasses[i].type == this.type)
			{
				this.element = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value)[0].clone(false, false);
				break;
			}
		}

		var wrapper = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value+" "+cssClasses.contentClasses.wrapper)[0].clone(false, false);
		
		var btn = null;
		for (var i=0; i<data.filter.data.length; i++)
		{
			
			btn = new Element("button",{
				type: "button",
				name: data.filter.data[i].id,
				html: data.filter.data[i].text
			});
			this.buttons.push(btn);
			
			btn.addEvent("click", function(event){  });
			
			btn.addEvent("click", function(event){
				event.preventDefault();
				this.dynamicTable.RequestData({
					type: DynamicTableRequestTypes.TYPE_SUBMIT_BUTTON_LIST,
					headerID: data.header.id,
					manualFilter: this.type,
					buttonID: event.target.name
				});
				this.contextmenu.Destroy();
			}.bind(this));
			
			wrapper.adopt(btn, new Element("br"));
		}
		
		this.dynamicTable.RegisterFilter(this.dynamicTable.options.viewID, data.header.id, this.id, this.type, this.chekboxes);
		
		this.element.adopt(new Element("td", {colspan: 2}).adopt(wrapper));
		
	},
	
	GetHTML: function(){ return this.element; }
});

var ContextMenuBarLightBoxButton = new Class({
	Implements: [Options, Events],
	Binds: ["GetHTML"],
	
	type: FilterTypes.TYPE_SHOW_LIGHTBOX_BUTTON,
	element: null,
	dynamicTable: null,
	contextmenu: null,
	
	buttons: new Array(),
	all_checkbox: null,
	
	id: "", 
	
	initialize: function (type, contextmenu, dynamicTable, cssClasses, data)
	{
		this.type = type;
		this.contextmenu = contextmenu;
		this.dynamicTable = dynamicTable;
		for (var i=0; i<cssClasses.typeClasses.length; i++)
		{
			if (cssClasses.typeClasses[i].type == this.type)
			{
				this.element = $$(cssClasses.tableClass+" "+cssClasses.typeClasses[i].value)[0].clone(false, false);
				break;
			}
		}

		var btn = new Element("button",{
			type: "button",
			html: data.text
		});
		
		btn.addEvent("click", function(url, lightbox){
			lightbox.showIframe(url, 1024, 768);
			this.contextmenu.Destroy();
		}.bind(this, [data.url, MOOTOOLS_LIGHTBOX]));
		
		this.element.adopt(new Element("td", {colspan: 2}).adopt(btn));
	},
	
	GetHTML: function(){ return this.element; }
});

