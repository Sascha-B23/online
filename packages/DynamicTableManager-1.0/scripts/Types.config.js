var TableRowTypes = {
	TYPE_NONE: 0,
	TYPE_INT: 1,
	TYPE_FLOAT: 2, 
	TYPE_BOOL: 3,
	TYPE_STRING: 4, 
	TYPE_DATE: 5
};

var FilterTypes = {
	TYPE_NONE: 0,
	TYPE_SORT_AZ: 1,				// Array("type" => FilterTypes::TYPE_SORT_AZ, "text" => "Sortieren von A bis Z", "active" => false),
	TYPE_SORT_ZA: 2,				// Array("type" => FilterTypes::TYPE_SORT_ZA, "text" => "Sortieren von Z bis A", "active" => false),
	TYPE_FILTER_CHECKBOX: 3,		// Array("type" => FilterTypes::TYPE_FILTER_CHECKBOX, "value" => "1", "text" => "Hebelhuber", "checked" => true ),
	TYPE_FILTER_FROM: 4, 			// Array("type" => FilterTypes::TYPE_FILTER_FROM, "text" => "Von", "value" => "" ),
	TYPE_FILTER_TO: 5,				// Array("type" => FilterTypes::TYPE_FILTER_TO, "text" => "Bis", "value" => "" )
	TYPE_FILTER_TEXTSEARCH: 6,		// Array("type" => FilterTypes::TYPE_FILTER_TEXTSEARCH, "text" => "Suche", "value" => "")
	TYPE_SUBMIT_BUTTON: 7,
	TYPE_SUBMIT_RESET: 8,
	TYPE_SUBMIT_BUTTON_LIST: 9,
	TYPE_SHOW_LIGHTBOX_BUTTON: 10
};

var DynamicTableRequestTypes = {
	TYPE_NONE: 0,
	TYPE_INITIAL: 1,
	TYPE_FILTER_ALL: 2,
	TYPE_FILTER_SPECIFIC: 3,
	TYPE_FILTER_MANUAL: 4,
	TYPE_INFORMATION: 5,
	TYPE_HEADER: 6,
	TYPE_FILTER_RESET: 7,
	TYPE_SUBMIT_BUTTON_LIST: 8,
	TYPE_LOAD_GROUP: 9
};