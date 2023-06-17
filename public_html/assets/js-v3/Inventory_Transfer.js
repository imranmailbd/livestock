import {
	cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, round, calculate, noPermissionWarning, redirectTo, fetchData,
	preventDot, printbyurl, confirm_dialog, alert_dialog, setSelectOpt, setTableRows, setTableHRows, showTopMessage, setOptions, 
	addPaginationRowFlex, checkAndSetSessionData, btnEnableDisable, popup_dialog, alert_label_missing, dynamicImport, 
	listenToEnterKey, addCustomeEventListener, actionBtnClick, serialize, onClickPagination, customAutoComplete, 
	multiSelectAction, historyTable, activityFieldAttributes, controllNumericField
} from './common.js';

import {
	showCategoryPPProduct, showProductPicker, reloadProdPkrCategory, cancelemailform, emailthispage, preNextCategory, AJautoComplete_IMEI
} from './cart.js';

if(segment2==='') segment2 = 'lists';

const listsFieldAttributes = [
	{'datatitle':Translate('Date'), 'align':'left'},
	{'datatitle':Translate('Transfer')+'#', 'align':'right'},
	{'datatitle':Translate('Transfer From'), 'align':'center'},
	{'datatitle':Translate('Transfer To'), 'align':'center'},
	{'datatitle':Translate('Total'), 'align':'right'},
	{'datatitle':Translate('Status'), 'align':'center'}
];

const uriStr = segment1+'/edit';

async function filter_Inventory_Transfer_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sview_type'] = document.getElementById("sview_type").value;
	const ssuppliers_id = document.getElementById("ssuppliers_id").value;
	jsonData['ssuppliers_id'] = ssuppliers_id;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetPage/filter';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);

		setSelectOpt('ssuppliers_id', 0, Translate('All Suppliers'), data.supOpt, 1, Object.keys(data.supOpt).length);
		setTableRows(data.tableRows, listsFieldAttributes, uriStr, [5], [1]);			
		document.getElementById("totalTableRows").value = data.totalRows;
		document.getElementById("ssuppliers_id").value = ssuppliers_id;
		
		onClickPagination();
	}
}

async function loadTableRows_Inventory_Transfer_lists(){
	const jsonData = {};
	jsonData['sview_type'] = document.getElementById("sview_type").value;
	jsonData['ssuppliers_id'] = document.getElementById("ssuppliers_id").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		setTableRows(data.tableRows, listsFieldAttributes, uriStr, [5], [1]);			
		onClickPagination();
	}
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    
	let list_filters, sortDropDown;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

    //=====Hidden Fields for Pagination======//
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '30' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

		const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
			const headerTitle = cTag('h2');
			headerTitle.innerHTML = Translate('Inventory Transfer')+' ';
				const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays a list of Open and Closed purchase orders')});
			headerTitle.appendChild(infoIcon);
		titleRow.appendChild(headerTitle);

			const transferLink = cTag('a', {'href': "/Inventory_Transfer/add", title: Translate('Create Transfer')});
				const transferButton = cTag('button', {class: "btn createButton"});
				transferButton.append(cTag('i', {class: "fa fa-plus"}), ' ', Translate('Create Transfer'));
			transferLink.appendChild(transferButton);
		titleRow.appendChild(transferLink);
    showTableData.appendChild(titleRow);

		const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
			sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
				const selectView = cTag('select', {class: "form-control", name: "sview_type", id: "sview_type"});
				selectView.addEventListener('change', filter_Inventory_Transfer_lists);
				setOptions(selectView, {'Open':Translate('Open'), 'Closed':Translate('Closed'), '':Translate('All Types')}, 1, 0);
			sortDropDown.appendChild(selectView);
		filterRow.appendChild(sortDropDown);

			sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
				const selectSuppliers = cTag('select', {class: "form-control", name: "ssuppliers_id", id: "ssuppliers_id"});
				selectSuppliers.addEventListener('change', filter_Inventory_Transfer_lists);
					const suppliersOption = cTag('option', {'value': 0});
					suppliersOption.innerHTML = Translate('All Suppliers');
				selectSuppliers.appendChild(suppliersOption);
			sortDropDown.appendChild(selectSuppliers);
		filterRow.appendChild(sortDropDown);

			const searchDiv = cTag('div', {class: "columnXS12 columnSM4 columnMD3"});
				const SearchInGroup = cTag('div', {class: "input-group"});
					const searchField = cTag('input', {'keydown':listenToEnterKey(filter_Inventory_Transfer_lists),'type': "text", 'placeholder': Translate('Search Transfers'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
				SearchInGroup.appendChild(searchField);
					const searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Transfers')});
					searchSpan.addEventListener('click', filter_Inventory_Transfer_lists);
						const searchIcon = cTag('i', {class: "fa fa-search"});
					searchSpan.appendChild(searchIcon);
				SearchInGroup.appendChild(searchSpan);
			searchDiv.appendChild(SearchInGroup);
		filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

		const divTableColumn = cTag('div', {class: "columnXS12"});
			const divNoMore = cTag('div', {id: "no-more-tables"});
				const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
					const listHead = cTag('thead', {class: "cf"});
						const columnNames = listsFieldAttributes.map(colObj=>(colObj.datatitle));
						const listHeadRow = cTag('tr',{class:'outerListsTable'});
							const thCol0 = cTag('th', {'style': "width: 80px;"});
							thCol0.innerHTML = columnNames[0];

							const thCol1 = cTag('th', {'width': "12%"});
							thCol1.innerHTML =  columnNames[1];

							const thCol2 = cTag('th', {'width': "30%"});
							thCol2.innerHTML = columnNames[2];

							const thCol3 = cTag('th');
							thCol3.innerHTML = columnNames[3];

							const thCol4 = cTag('th', {'width': "10%"});
							thCol4.innerHTML = columnNames[4];

							const thCol5 = cTag('th', {'width': "10%"});
							thCol5.innerHTML = columnNames[5];
						listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5);                        
					listHead.appendChild(listHeadRow);
				listTable.appendChild(listHead);
					const listBody = cTag('tbody', {id: "tableRows"});
				listTable.appendChild(listBody);
			divNoMore.appendChild(listTable);
		divTableColumn.appendChild(divNoMore);
    showTableData.appendChild(divTableColumn);
    addPaginationRowFlex(showTableData);

    //======sessionStorage =======//
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    
    const sview_type = 'Open', ssuppliers_id = 0;

	checkAndSetSessionData('sview_type', sview_type, list_filters);
	checkAndSetSessionData('ssuppliers_id', ssuppliers_id, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

	addCustomeEventListener('filter',filter_Inventory_Transfer_lists);
	addCustomeEventListener('loadTable',loadTableRows_Inventory_Transfer_lists);
	filter_Inventory_Transfer_lists(true);
}

function add(){
	const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
		const titleRow = cTag('div');
			const headerTitle = cTag('h2', { 'style': "padding: 5px; text-align: start;"});
			headerTitle.innerHTML = Translate('Create Inventory Transfer')+' ';
				const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page captures the basic details required to create a new purchase order')});
			headerTitle.appendChild(infoIcon);
		titleRow.appendChild(headerTitle);
	showTableData.appendChild(titleRow);

		const addRowColumn = cTag('div', {class: "columnXS12"});
			let divCallout = cTag('div', {class: "innerContainer"});
				const addInventoryForm = cTag('form', {'action': "#",  name: "frmAddIT", id: "frmAddIT", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
				addInventoryForm.addEventListener('submit', AJsave_IT);
					const addInventoryFlex = cTag('div', {class: "flex"});
						const addInventoryName = cTag('div', {class: "columnXS4 columnMD2"});
							const addInventoryLabel = cTag('label', {'for': "supplier_id", 'data-placement': "bottom"});
							addInventoryLabel.innerHTML = Translate('Transfer To');
								const requireField = cTag('span', {class: "required"});
								requireField.innerHTML = '*';
							addInventoryLabel.appendChild(requireField);
						addInventoryName.appendChild(addInventoryLabel);
					addInventoryFlex.appendChild(addInventoryName);

						const dropdown = cTag('div', {class: "columnXS8 columnMD4"});
							let selectSupplier = cTag('select', {'required': "", name: "supplier_id", id: "supplier_id", class: "form-control"});
								let supplierOption = cTag('option', {'value': ""});
								supplierOption.innerHTML = Translate('Select Location');
							selectSupplier.appendChild(supplierOption);
						dropdown.appendChild(selectSupplier);
							const supplierError = cTag('span', {class: "error_msg", id: "errmsg_supplier_name"});
						dropdown.appendChild(supplierError);
					addInventoryFlex.appendChild(dropdown);
				addInventoryForm.appendChild(addInventoryFlex);

					const buttonName = cTag('div', {class: "flex"});
						let buttonColumn = cTag('div', {class: "columnXS12 columnMD6", 'align': "right"});
							let hiddenPO = cTag('input', {'type': "hidden", name: "po_id", id: "po_id", 'value': 0});
						buttonColumn.appendChild(hiddenPO);
							const cancelButton = cTag('input', {'type': "button", class: "btn defaultButton", id: "cancelbutton", 'value': "Cancel"});
							cancelButton.addEventListener('click', function(){redirectTo('/Inventory_Transfer/lists')});
						buttonColumn.appendChild(cancelButton);
							const addButton = cTag('input', {class: "btn completeButton", 'style': "margin-left: 10px;", name: "submit", id: "submit", 'type': "submit", 'value': "Add"});
						buttonColumn.appendChild(addButton);
					buttonName.appendChild(buttonColumn);
				addInventoryForm.appendChild(buttonName);
			divCallout.appendChild(addInventoryForm);
		addRowColumn.appendChild(divCallout);
	showTableData.appendChild(addRowColumn);
	AJ_add_MoreInfo();
}

async function AJ_add_MoreInfo(){
	const jsonData = {};
    let supplier_id = document.getElementById('supplier_id');
	jsonData['supplier_id'] = supplier_id.value;
    const url = '/'+segment1+'/AJ_add_MoreInfo';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){		
		supplier_id.innerHTML = '';
			const option = cTag('option', {'value': ''});
			option.innerHTML = Translate('Select Location');
		supplier_id.appendChild(option);
		setOptions(supplier_id, data.supOpt, 1, 1);
		if(document.getElementById("frmAddIT") && document.getElementById("supplier_id")){
			setTimeout(function() {document.getElementById("supplier_id").focus();}, 500);
		}
	}
}

export async function addToITCart(event){
	let messageid, field, fieldname, fieldValue;
	if(event) fieldValue = event.detail;
	if(fieldValue){
		messageid = document.getElementById("error_productlist");
		fieldname = 'product_id';
	}
	else{
		messageid = document.getElementById("error_search_sku");
		fieldname = 'sku';
		field = document.getElementById("search_sku");
		fieldValue = field.value;
		field.value = '';
	}
	messageid.innerHTML = '';
	const po_id = document.getElementById("po_id").value;
	const supplier_id = document.getElementById("supplier_id").value;
	
	if(fieldValue===''){
		messageid.innerHTML = Translate('Missing product sku/IMEI Number');
		field.focus();
	}
	else{
		const jsonData = {};
		jsonData['fieldname'] = fieldname;
		jsonData['fieldvalue'] = fieldValue;
		jsonData['po_id'] = po_id;
		jsonData['supplier_id'] = supplier_id;

		const url = '/'+segment1+'/addToITCart';

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.action ==='Add' || data.action === 'Update'){
				loadITCartData(false,data.cartsData);	
				// calculateIT_Total();
			}
			else{
				if(data.action==='notProductTransfer') messageid.innerHTML = Translate('Sorry! could not add product to transfer.');
				else if(data.action==='notProductOrder') messageid.innerHTML = Translate('Sorry! could not add product to order.');
				else if(data.action==='imeiNotFound') messageid.innerHTML = Translate('IMEI Number not found')+' '+field.value;
				else if(data.action==='noOrderFound') messageid.innerHTML = Translate('There is no order found');
			}
			if(field) field.focus();
		}
	}
}

async function removeThisITItem(po_items_id){
	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;

	const url = '/'+segment1+'/removeThisITItem';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action ==='Removed'){
			loadITCartData(false,data.cartsData);		
			// calculateIT_Total();
			filter_Inventory_Transfer_edit();
		}
		else{
			showTopMessage('success_msg', Translate('Could not removed from inventory transfer cart.'));
		}
	}	
}

async function removeIMEIFromITCart(po_items_id, item_id){
	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;
	jsonData['item_id'] = item_id;

	const url = '/'+segment1+'/removeIMEIFromITCart';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action ==='Removed'){
			loadITCartData(false,data.cartsData);	
			// calculateIT_Total();
			filter_Inventory_Transfer_edit();
		}
		else{
			showTopMessage('success_msg', Translate('Could not removed from purchase order list'));
		}
	}
}

function changeThisITRow(po_items_id){
	let _Update, bTag, inputField;
	const cost = document.getElementById("cost"+po_items_id).value;
	const received_qty = document.getElementById("received_qty"+po_items_id).value;
	const item_type = document.getElementById("item_type"+po_items_id).value;
	
	if(item_type==='cellphones'){
	 	_Update = 'Import IMEI Numbers';
	}
		const formDialog = cTag('div');
			const updateITCartForm = cTag('form', {'action': "#", name: "frmPORow", id: "frmPORow", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
				const costRow = cTag('div', {class: "flexSpaBetRow"});
					const costName = cTag('div', {class: "columnXS4 columnSM3", 'align': "left"});
						const costLabel = cTag('label', {'for': "cost"});
						costLabel.innerHTML = Translate('Cost')+':';
					costName.appendChild(costLabel);
				costRow.appendChild(costName);
					const costField = cTag('div', {class: "columnXS8 columnSM4", 'align': "left"});
						inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'9999999.99','data-format':'d.dd', class: "form-control", 'style': "min-width: 70px;", name: "cost", id: "cost", 'value': cost});
						// checkNumericInputOnKeydown(inputField);
						controllNumericField(inputField, '#errmsg_cost');
						inputField.addEventListener('keyup', calculateITRowTotal);
					costField.appendChild(inputField);
				costRow.appendChild(costField);

					const currencyColumn = cTag('div', {class: "columnSM5", 'align': "right"});
					currencyColumn.append(cTag('b', {id: "cost_str"}),cTag('span', {class: "error_msg", id: "errmsg_cost"}));
				costRow.appendChild(currencyColumn);
			updateITCartForm.appendChild(costRow);

				const QtyRow = cTag('div', {class: "flexSpaBetRow"});
					const QtyName = cTag('div', {class: "columnXS4 columnSM3", 'align': "left"});
						const QtyLabel = cTag('label', {'for': "received_qty"});
						QtyLabel.innerHTML = Translate('QTY')+':';
							let requireField = cTag('span', {class: "required"});
							requireField.innerHTML = '*';
						QtyLabel.appendChild(requireField);
					QtyName.appendChild(QtyLabel);
				QtyRow.appendChild(QtyName);
					const QtyValue = cTag('div', {class: "columnXS8 columnSM4", 'align': "left"});
						inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'9999', 'data-format':'d', class: "form-control", name: "received_qty", id: "received_qty", 'value': received_qty});
						// checkNumericInputOnKeydown(inputField);
						controllNumericField(inputField, '#errmsg_received_qty');
						preventDot(inputField);
						if(item_type==='cellphones'){
							inputField.setAttribute('readonly', 'readonly');
						}
						inputField.addEventListener('keyup', calculateITRowTotal);
						inputField.addEventListener('change', calculateITRowTotal);
					QtyValue.appendChild(inputField);
					QtyValue.appendChild(cTag('span', {class: "error_msg", id: "errmsg_received_qty"}));
				QtyRow.appendChild(QtyValue);

					let subTotalColumn = cTag('div', {class: "columnSM5", 'align': "right"});
						bTag = cTag('b', {id: "received_qty_value_str"});
						bTag.innerHTML = 'currency'+' ';
					subTotalColumn.append(Translate('Subtotal')+': ',bTag,cTag('input', {'type': "hidden", name: "received_qty_value", id: "received_qty_value", 'value': 0}));
				QtyRow.appendChild(subTotalColumn);
			updateITCartForm.appendChild(QtyRow);
            updateITCartForm.appendChild(cTag('hr'));

					const totalName = cTag('div', {'align': "right"});
						const totalTitle = cTag('b');
						totalTitle.innerHTML = Translate('Total')+': ';
					totalName.append(totalTitle,bTag = cTag('b', {id: "total_str"}),cTag('input', {'type': "hidden", name: "total", id: "total", 'value': 0}));
			updateITCartForm.appendChild(totalName);

			//bulktextarea
			if(item_type==='cellphones'){
				let bulkRow = cTag('div', {class: "flexSpaBetRow", id: "bulkrow"});
					const imeiName = cTag('div', {class: "columnSM4", 'align': "left"});
						const imeiLabel = cTag('label', {for: "sales_price"});
						imeiLabel.innerHTML = Translate('IMEI Numbers')+ ':';
					imeiName.appendChild(imeiLabel);
				bulkRow.appendChild(imeiName);
		
					let imeiValue = cTag('div', {class: "columnSM5", 'align': "right"});
						let textarea = cTag('textarea', {'placeholder':Translate('One IMEI number per line'), 'name': "bulkimei", id: "bulkimei", 'cols': 20, 'rows': 3, class: "form-control"});
					imeiValue.appendChild(textarea);
				bulkRow.appendChild(imeiValue);
		
					const imeiError = cTag('div', {class: "columnSM3", 'align': "left"});
						let errorSpan2 = cTag('span', {class: "error_msg", id: "error_bulkimei"});
					imeiError.appendChild(errorSpan2);
				bulkRow.appendChild(imeiError);
			updateITCartForm.appendChild(bulkRow);
			_Update = 'Import IMEI Numbers';
		}
			inputField = cTag('input', {'type': "hidden", name: "po_items_idvalue", id: "po_items_idvalue", 'value': po_items_id});
			updateITCartForm.appendChild(inputField);
		formDialog.appendChild(updateITCartForm);

	popup_dialog(
		formDialog,
		{
			title: Translate('Update Inventory Transfer Cart'),
			width: 600,
			buttons: {
				"Cancel": {
					text: Translate('Cancel'),
					class: 'btn defaultButton', 
					click: function(hidePopup) {
						hidePopup();
					},
				},
				"Update":{
					text: Translate('Save'),
					class: 'btn saveButton btnmodel btnChangeRow savebulkLoad',
					click: function(hidePopup) {
						if(item_type==='cellphones') saveBulkITData(hidePopup);
						else updateITRow(hidePopup);
					},
				}
			}
		}
	);
	
	setTimeout(function() {
		document.getElementById("received_qty").focus();
		if(item_type==='cellphones'){
			document.getElementById("received_qty").readOnly = true; 
		}
		else{
			if(document.getElementById("received_qty").hasAttribute('readonly')){
				document.getElementById("received_qty").removeAttribute('readonly'); 
			}
		}
		
		calculateITRowTotal();
		
		if(item_type === 'cellphones'){
			const ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#";
			document.querySelector("#bulkimei").addEventListener('keyup', event=>{
				let sku = event.target.value.toUpperCase().replace(' ', '-');
				let IsNumber = true;
				let Char, i;
				let newsku = '';
				for (i = 0; i < sku.length && IsNumber === true; i++){ 
					Char = sku.charAt(i); 
					newsku = newsku+Char;
				}
				
				if(sku.length> newsku.length || event.target.value !== newsku){
					event.target.value = newsku;
				}
			});
		}
	}, 500);	
	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
}

async function saveBulkITData(hidePopup){
	let savebulkLoad;
	savebulkLoad = document.querySelector('.savebulkLoad');
	savebulkLoad.innerHTML = Translate('Importing IMEI')+'...';
	savebulkLoad.disabled = true;
	let po_items_id = parseInt(document.getElementById("po_items_idvalue").value);
	if(isNaN(po_items_id)){po_items_id = 0;}
	const cost = document.getElementById("cost").value;
	const received_qty = document.getElementById("received_qty").value;
	const bulkimei = document.getElementById("bulkimei").value;
	
	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;
	jsonData['cost'] = cost;
	jsonData['received_qty'] = received_qty;
	jsonData['bulkimei'] = bulkimei;

    const url = '/'+segment1+'/saveBulkITData';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action ==='Add'){
			loadITCartData(false,data.cartsData);	
			// calculateIT_Total();
			filter_Inventory_Transfer_edit();
		}
		hidePopup();
	}
	return false;
}

function calculateITRowTotal(){
	let cost = parseFloat(document.getElementById("cost").value);
	if(cost==='' || isNaN(cost)){cost = 0;}
	document.getElementById("cost_str").innerHTML = addCurrency(cost);

	let received_qty = parseInt(document.getElementById("received_qty").value);
	if(isNaN(received_qty) || received_qty===''){
		received_qty = 0;
	}
	
	let received_qty_value = calculate('mul',cost,received_qty,2);
	document.getElementById("received_qty_value").value = received_qty_value;
	document.getElementById("received_qty_value_str").innerHTML = addCurrency(received_qty_value);
	
	document.getElementById("total").value = received_qty_value;
	document.getElementById("total_str").innerHTML = addCurrency(received_qty_value);
}

async function updateITRow(hidePopup){
	let btnChangeRow;
	const po_items_id = parseInt(document.getElementById("po_items_idvalue").value);
	let cost = parseFloat(document.getElementById("cost").value);
	if(isNaN(cost) || cost===''){
		cost = 0;
	}
	const errorid = document.getElementById("errmsg_cost");
	const errmsg_received_qty = document.getElementById("errmsg_received_qty");
	errorid.innerHTML = '';
	errmsg_received_qty.innerHTML = '';

	let received_qty = parseFloat(document.getElementById("received_qty").value);
	if(isNaN(received_qty) || received_qty===''){
		received_qty = 0;
	}

	const total = parseFloat(document.getElementById("total").value);
						
	btnChangeRow = document.querySelector('.btnChangeRow');
	btnChangeRow.innerHTML = Translate('Saving')+'...';
	btnChangeRow.disabled = true;
	
	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;
	jsonData['cost'] = cost;
	jsonData['received_qty'] = received_qty;
	jsonData['total'] = total;
	
    const url = '/'+segment1+'/updateit_item';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action ==='Update'){
			loadITCartData(false,data.cartsData);
			// calculateIT_Total();
			filter_Inventory_Transfer_edit();
		}
		else{
			showTopMessage('success_msg', Translate('Could not update inventory transfer.'));			
			btnEnableDisable(btnChangeRow,'Change',false)
		}
		hidePopup();
	}
	return false;
}

function calculateIT_Total(){
	let received_qtytotal = 0;
	let grand_total = 0;
	
	let hasdatacount = document.getElementById("invoice_entry_holder").innerHTML.length;
	let validation = true;
	if(hasdatacount >10){
		let po_items_idarray = document.getElementsByName("po_items_id[]");
		if(po_items_idarray.length>0){
			if(document.getElementById("barcodeserno")) document.getElementById("barcodeserno").innerHTML = parseInt(po_items_idarray.length+1);
			for(let p = 0; p < po_items_idarray.length; p++){
				let po_items_id = po_items_idarray[p].value;
				
				let received_qty = parseFloat(document.getElementById("received_qty"+po_items_id).value);
				if(isNaN(received_qty) || received_qty===''){received_qty = 0;}
				if(validation===true && received_qty===0){validation=false;}
				let total = parseFloat(document.getElementById("total"+po_items_id).value);
				if(isNaN(total) || total===''){total = 0;}
				
				received_qtytotal = calculate('add',received_qtytotal,received_qty,2);
				grand_total = calculate('add',total,grand_total,2);
			}
		}
		else{
			return false;
		}
	}
	else{
		if(document.getElementById("barcodeserno")) document.getElementById("barcodeserno").innerHTML = 1;
		validation = false;
	}
 
	document.getElementById("receivedQtyTotal").innerHTML = received_qtytotal;
	document.getElementById("grand_totalstr").innerHTML = addCurrency(grand_total);	
	document.getElementById("grand_total").value = grand_total;
	
	if(validation===true){
		if(document.getElementById("possubmit")){
			if(document.getElementById("possubmit").style.display==='none'){
				document.getElementById("possubmit").style.display='block';
			}
		}
		if(document.getElementById("possubmitdis")){
			if(document.getElementById("possubmitdis").style.display!=='none'){
				document.getElementById("possubmitdis").style.display='none';
			}
		}
		return true;
	}
	else{
		if(document.getElementById("possubmit")){
			if(document.getElementById("possubmit").style.display!=='none'){
				document.getElementById("possubmit").style.display='none';
			}
		}
		if(document.getElementById("possubmitdis")){
			if(document.getElementById("possubmitdis").style.display==='none'){
				document.getElementById("possubmitdis").style.display='block';
			}
		}
		return false;
	}
}

function cancelIT(){
	let po_itemscount, s;
	let hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	po_itemscount = 0;
	if(hasdata.length > 10){
		po_itemscount = document.getElementsByName("po_items_id[]").length;
	}
	
	if(po_itemscount > 0){
		s = '';
		if(po_itemscount>1){
			s = 's';
		}
		let message = po_itemscount+' '+Translate('Item(s) has been received for this you can not cancel a inventory transfer.');
		alert_dialog(Translate('Cancel Inventory Transfer'), message, Translate('Close'))
	}
	else{
		confirm_dialog(Translate('Cancel Inventory Transfer'), Translate('Are you sure you want to cancel this Inventory Transfer?'), confirmITCancelation);
	}
}

async function confirmITCancelation(hidePopup){
	const po_id = document.getElementById("po_id").value;
	const jsonData = {};
	jsonData['po_id'] = po_id;
	jsonData['status'] = Translate('Cancel');
    const url = '/'+segment1+'/itCancel';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.returnStr >0){
			window.location = '/Inventory_Transfer/lists';
			hidePopup();
		}
		else{
			let cancellmessage = document.getElementById("cancellmessage");
			cancellmessage.innerHTML = Translate('Could not cancel this Invenoty transfer.');
			if(cancellmessage.style.display==='none'){
				cancellmessage.style.display='block';
			}
		}
	}
}

function completeIT(){
	let hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	if(hasdata.length<10){
		showTopMessage('alert_msg', Translate('Missing cart. Please choose/add new product'));
		document.getElementById("search_sku").focus();
		return(false);
	}
	confirm_dialog(Translate('Complete Inventory Transfer'), Translate('Once you Complete a Transfer you can no longer edit any of the information on it.'), confirmITCompletion);
}

async function confirmITCompletion(hidePopup){
	const po_id = document.getElementById("po_id").value;	

	let archive;
	archive = document.querySelector('.archive');
	archive.innerHTML = Translate('Saving')+'...';
	archive.disabled = true;		
	
	const jsonData = {};
	jsonData['po_id'] = po_id;
    const url = '/'+segment1+'/update_it_complete';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){    	
		if(data.returnStr >0){
			window.location = '/Inventory_Transfer/lists/closed';
		}
		else{
			archive = document.querySelector('.archive');
			archive.innerHTML = Translate('Confirm');
			archive.disabled = false;
			showTopMessage('alert_msg', Translate('Could not complete this inventory transfer.'));		
		}
		hidePopup();
	}
}

async function AJsave_IT(event){
	if(event){ event.preventDefault();}

	let submit;
	submit = document.querySelector('#submit');
	submit.innerHTML = Translate('Saving')+'...';
	submit.disabled = true;	

	const jsonData = serialize('#frmAddIT');
    const url = '/'+segment1+'/AJsave_IT/';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){	
		if(data.savemsg !=='error' && data.id>0){
			window.location = '/Inventory_Transfer/edit/'+data.id+'/'+data.savemsg;
		}
		else{
			showTopMessage('alert_msg', Translate('Error occured while adding PO information! Please try again.'));
			
			if(document.frmAddIT.po_id.value===0){
				submit = document.querySelector('#submit');
				submit.value = Translate('Save');
				submit.disabled = false;
			}
			else{
				submit = document.querySelector('#submit');
				submit.value = Translate('Update');
				submit.disabled = false;
			}
		}
	}
	return false; 
}

async function AJsend_ITEmail(event){
	if(event) event.preventDefault();
	let email_address = document.getElementById("email_address").value;
	let po_id = document.getElementById("po_id").value;
	
	actionBtnClick('.sendbtn', Translate('Sending'), 1);
	
	const jsonData = {};
	jsonData['email_address'] = email_address;
	jsonData['po_id'] = po_id;
    const url = '/'+segment1+'/AJsend_ITEmail/';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.returnStr !=='Ok'){
			showTopMessage('alert_msg', Translate('Sorry! Could not send mail. Try again later.'));
		}
		else{
			setTimeout(() => {
				document.querySelectorAll(".emailform").forEach(oneField=>{
					if(oneField.style.display!=='none'){
						oneField.style.display='none';
					}
				});
			}, 1000);
		}
		actionBtnClick('.sendbtn', Translate('Email'), 0);
	}
	return false
}

//======edit=======//
function edit(){
	let tdCol, divInnerFlex, list_filters, emailHeadRow;
	const Dashboard = document.getElementById('viewPageInfo');
	Dashboard.innerHTML = '';
		const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const titleName = cTag('div',{ 'class':`columnXS10 columnMD5` });
				const headerTitle = cTag('h2',{ 'style': "text-align: start;" });	
				headerTitle.innerHTML = `${Translate('Inventory Transfer')} p${segment3}`;
			titleName.appendChild(headerTitle);
		titleRow.appendChild(titleName);
			const statusName = cTag('div',{ 'class':`columnXS2 columnMD2`, 'align':`center` });
				const centerStatus = cTag('div',{ 'align':`center`, 'class':`input-group` });
					const statusButton = cTag('a',{ 'class':`btn completeButton`,'href':`javascript:void(0);` });
						const statusButtonLabel = cTag('label',{ 'style': "margin-bottom: 0px;", 'id':'status_label'});
					statusButton.appendChild(statusButtonLabel);
				centerStatus.appendChild(statusButton);
			statusName.appendChild(centerStatus);
		titleRow.appendChild(statusName);
			const buttonsName = cTag('div',{ 'class':`columnMD5`, 'style': "text-align: end;" });
				const aTag = cTag('a', {'href': "/Inventory_Transfer/lists", class: "btn defaultButton", 'style': "margin-left: 10px;", title: Translate('Inventory Transfer')});
				aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Inventory Transfer'));
			buttonsName.appendChild(aTag);

				let printDiv = cTag('div',{ 'class':`printBtnDropDown`, id: 'Intradropdown' });
					const buttonTitle = cTag('button',{ 'type':`button`,'class':`btn printButton dropdown-toggle`, 'style': "margin-left: 10px;", 'data-toggle':`dropdown`,'aria-haspopup':`true`,'aria-expanded':`false` });
						const buttonLabel = cTag('label');
						buttonLabel.appendChild(cTag('i',{ 'class':`fa fa-print` }));
						if(OS =='unknown'){
							buttonLabel.append(' '+Translate('Print')+' ');
						}
						buttonLabel.append('\u2000', cTag('span',{ 'class':`caret`, 'style': "color: #000;" }));
					buttonTitle.appendChild(buttonLabel);
						let dropDownSpan = cTag('span',{ 'class':`sr-only` });
						dropDownSpan.innerHTML = Translate('Toggle Dropdown');
					buttonTitle.appendChild(dropDownSpan);
				printDiv.appendChild(buttonTitle);
					let ulDropDown = cTag('ul',{ 'class':`dropdown-menu`});
						let liFullPrint = cTag('li');
							let fullPagePrint = cTag('a',{ 'href':`javascript:void(0);`,'id':'full_page_print','title':Translate('Full Page Printer') });
							fullPagePrint.innerHTML = Translate('Full Page Printer');
						liFullPrint.appendChild(fullPagePrint);
					ulDropDown.appendChild(liFullPrint);
					ulDropDown.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						let liEmail = cTag('li');
							const emailTransfer = cTag('a',{ 'href':`javascript:void(0)`,'click':emailthispage,'title':Translate('Email Transfer') });
							emailTransfer.innerHTML = Translate('Email Transfer');
						liEmail.appendChild(emailTransfer);
					ulDropDown.appendChild(liEmail);
					ulDropDown.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						let liBarcode = cTag('li');
							let barcodePrint = cTag('a',{ 'href':`javascript:void(0)`,'id':'barcode_print','title':Translate('Print Barcode Label') });
							barcodePrint.append(cTag('i',{ 'class':`fa fa-print` }), ' ', Translate('Barcode Labels'));
							if(OS =='unknown'){
								barcodePrint.innerHTML = Translate('Barcode Label Print');
							}
						liBarcode.appendChild(barcodePrint);
					ulDropDown.appendChild(liBarcode);
				printDiv.appendChild(ulDropDown);
			buttonsName.appendChild(printDiv);
			
				const emailButtons = cTag('div',{ 'class': "flexEndRow", 'style': "width: 100%;" });
					const emailSendDiv = cTag('div',{ 'style': "margin-top: 10px;" });
						const emailSendForm = cTag('form',{ 'method':`post`,'name':`frmSendITEmail`,'id':`frmSendITEmail`,'enctype':`multipart/form-data`,'action':`#`,'submit':AJsend_ITEmail });
							const emailTable = cTag('table',{ 'align':`center`,'width':`100%`,'border':`0`,'cellspacing':`0`,'cellpadding':`10` });
								const emailBody = cTag('tbody');
									emailHeadRow = cTag('tr');
										tdCol = cTag('td',{ 'colspan':`2` });
										tdCol.appendChild(cTag('div',{ 'id':`showerrormessage` }));
										tdCol.appendChild(cTag('div',{ 'id':`showsuccessmessage` }));
									emailHeadRow.appendChild(tdCol);
								emailBody.appendChild(emailHeadRow);
									emailHeadRow = cTag('tr',{ 'class':`emailform`,style:'display:none'});
										tdCol = cTag('td');
										tdCol.appendChild(cTag('input',{ 'type':`email`,'required':``,'name':`email_address`,'id':`email_address`,'class':`form-control`,'maxlength':`50` }));
									emailHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'width':`150`,'align':`right`,'valign':`middle` });
										tdCol.appendChild(cTag('input',{ 'type':`submit`,'class':`btn completeButton sendbtn`,'value':` ${Translate('Email')} ` }));
										tdCol.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`, 'style': "margin-left: 4px;", 'click':cancelemailform,'value':` ${Translate('Cancel')} ` }));
									emailHeadRow.appendChild(tdCol);
								emailBody.appendChild(emailHeadRow);
							emailTable.appendChild(emailBody);
						emailSendForm.appendChild(emailTable);
					emailSendDiv.appendChild(emailSendForm);
				emailButtons.appendChild(emailSendDiv);
			buttonsName.appendChild(emailButtons);
		titleRow.appendChild(buttonsName);
	Dashboard.appendChild(titleRow);

		const locationInfoRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const locationInfoColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
				const widget = cTag('div',{ 'class':`cardContainer` });
					const widgetHeader = cTag('div',{ 'class':`cardHeader` });
						const widgetHeaderTitle = cTag('h3',{'id':'location_info'});
						widgetHeaderTitle.appendChild(cTag('i',{ 'class':`fa fa-user` }));
					widgetHeader.appendChild(widgetHeaderTitle);
				widget.appendChild(widgetHeader);
					const widgetContent = cTag('div',{ 'class':`cardContent`});
						divInnerFlex = cTag('div',{ 'class':`flex`, 'style': "padding-left: 20px;" });
							let ulTransfer = cTag('div',{ 'class':`cardOrder` });
							ulTransfer.appendChild(cTag('div',{'id':'transfer_links'}));
						divInnerFlex.appendChild(ulTransfer);
					widgetContent.appendChild(divInnerFlex);
				widget.appendChild(widgetContent);
			locationInfoColumn.appendChild(widget);
		locationInfoRow.appendChild(locationInfoColumn);
			const mobileInfoColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
				const mobileWidget = cTag('div',{ 'class':`cardContainer` });
					const mobileWidgetHeader = cTag('div',{ 'class':`cardHeader` });
						const mobileHeaderTitle = cTag('h3');
						mobileHeaderTitle.innerHTML = ' ';
						mobileHeaderTitle.appendChild(cTag('i',{ 'class':`fa fa-mobile` }));
					mobileWidgetHeader.appendChild(mobileHeaderTitle);
				mobileWidget.appendChild(mobileWidgetHeader);
					const mobileWidgetContent = cTag('div',{ 'class':`cardContent`,'id':`order_info` });
						divInnerFlex = cTag('div',{ 'class':`flex`, 'style': "padding-left: 20px;" });
							let ulDateTime = cTag('div',{ 'class':`cardOrder` });
								let liDateTime = cTag('div',{'id':'po_datetime_label'});
									const mobileWidgetLabel = cTag('label', {'style': "width: 125px;"});
									mobileWidgetLabel.innerHTML = Translate('Date');
								liDateTime.appendChild(mobileWidgetLabel);
							ulDateTime.appendChild(liDateTime);
						divInnerFlex.appendChild(ulDateTime);
					mobileWidgetContent.appendChild(divInnerFlex);
				mobileWidget.appendChild(mobileWidgetContent);
			mobileInfoColumn.appendChild(mobileWidget);
		locationInfoRow.appendChild(mobileInfoColumn);
	Dashboard.appendChild(locationInfoRow);

		const editColumn = cTag('div',{ 'class':`columnXS12`, 'style': "position: relative; margin-bottom: 10px;" });
			const editTable = cTag('table',{ 'class':` table-bordered`, 'style': "margin-bottom: 0px;" });
				const editHead = cTag('thead');
					const editHeadRow = cTag('tr',{'id':'cartHeader'});
						const thCol0 = cTag('th',{ 'width':`40px`, 'style': "text-align: right;" });
						thCol0.innerHTML = '#';

						const thCol1 = cTag('th');
						thCol1.innerHTML = Translate('Description');

						const thCol2 = cTag('th',{ 'width':`12%`, 'style': "text-align: right;" });
						thCol2.innerHTML = Translate('QTY');

						const thCol3 = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
						thCol3.innerHTML = Translate('Unit Cost');

						const thCol4 = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
						thCol4.innerHTML = Translate('Total');
					editHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
				editHead.appendChild(editHeadRow);
			editTable.appendChild(editHead);
			editTable.appendChild(cTag('tbody',{ 'id':`invoice_entry_holder` }));															
		editColumn.appendChild(editTable);
	Dashboard.appendChild(editColumn);

		let inventoryHistoryColumn = cTag('div',{ 'class':`columnXS12` });
		let hiddenProperties = {
			'note_forTable':'po',
			'spo_id':'',
			'table_idValue':'',
		}
		inventoryHistoryColumn.appendChild(historyTable(Translate('Expense History'),hiddenProperties));
	Dashboard.appendChild(inventoryHistoryColumn);

	//======sessionStorage =======//
	if (sessionStorage.getItem("list_filters") !== null) {
		list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
	}
	else{
		list_filters = {};
	}	
	let shistory_type = '';
	checkAndSetSessionData('shistory_type', shistory_type, list_filters);
	multiSelectAction('Intradropdown');
	addCustomeEventListener('filter',filter_Inventory_Transfer_edit);
	addCustomeEventListener('loadTable',loadTableRows_Inventory_Transfer_edit);
	addCustomeEventListener('changeCart',addToITCart);
	addCustomeEventListener('loadITCart',loadITCartData);
	AJ_edit_MoreInfo();
}

async function AJ_edit_MoreInfo(){
	let table, moreInfoBody, moreInfoHeadRow, tdCol;
    const jsonData = {po_number:segment3};
    const url = '/'+segment1+'/AJ_edit_MoreInfo';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.querySelector('#status_label').innerHTML = data.status;
		document.querySelector('#email_address').value = data.user_email;
		document.querySelector('#location_info').append(` ${Translate('Location')} ${data.toOrFrom==='to'?Translate('To'):Translate('from')} ${Translate('Info')}`);

		document.getElementById('full_page_print').addEventListener('click',()=>printbyurl(`/Inventory_Transfer/prints/large/${data.po_number}`));
		document.getElementById('barcode_print').addEventListener('click',()=>printbyurl(`/Inventory_Transfer/prints/barcode/${data.po_number}`));

		let liTransfer = document.querySelector('#transfer_links');
			let transferLabel = cTag('label', {'style': "width: 125px;"});
			transferLabel.innerHTML = `${Translate('Transfer')} ${data.toOrFrom==='to'?Translate('To'):Translate('from')}`;
		liTransfer.appendChild(transferLabel);
		liTransfer.append(`: `);
			let domainName = location.hostname.split('.');
			domainName.shift();
			let baseurl = `http://${data.company_subdomain}.${domainName.join('.')}`
			let loginLink = cTag('a',{'target':"_blank",'href':`${baseurl}/Account/login`,'title':"Login Now"});
			loginLink.innerHTML = data.company_subdomain;
		liTransfer.appendChild(loginLink);
		liTransfer.append(' ');
			let editLink = cTag('a',{'target':"_blank",'href':`${baseurl}/Inventory_Transfer/edit/${data.lot_ref_no}`,'title':"Login Now"});
			editLink.innerHTML = `IT${data.lot_ref_no}`;
		liTransfer.appendChild(editLink);

		let date = new Date(data.po_datetime);
		let month = date.getMonth()+1;
		month = month<10?'0'+month:month;
		let day = date.getDate();
		day = day<10?'0'+day:day;
		let year = date.getFullYear().toString().slice(2);
		if(calenderDate.toLocaleLowerCase() === 'mm/dd/yyyy') date = `${month}/${day}/${year}`;
		else date = `${day}-${month}-${year}`;
		document.querySelector('#po_datetime_label').append(`: ${date}`);

		if(data.status === 'Open'){
				let thCol = cTag('th',{ 'width':`80px`});
				thCol.appendChild(cTag('i',{ 'class':`fa fa-trash-o` }));
			document.querySelector('#cartHeader').appendChild(thCol);
		}

		table = document.querySelector('#invoice_entry_holder').parentNode;
		if(data.status === 'Open'){
				moreInfoBody = cTag('tbody');
					moreInfoHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style': "text-align: right;",'id':`barcodeserno` });
						tdCol.innerHTML = 1;
					moreInfoHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'colspan':Math.floor(data.cols) });
							const searchProductPicker = cTag('div',{ 'class':`flexStartRow` });
								const newProductAdd = cTag('div',{ 'class':`input-group columnXS6 columnSM4` });
								newProductAdd.appendChild(cTag('input',{ 'maxlength':`50`,'type':`text`,'id':`search_sku`,'name':`search_sku`,'class':`form-control search_sku ui-autocomplete-input`, autocomplete:'off', 'placeholder':Translate('Search by product name or SKU') }));
									let newSpan = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Product'),'class':`input-group-addon cursor` });
									if(data.pPermission !== 1) newSpan.addEventListener('click',()=>noPermissionWarning('Product'));
									else newSpan.addEventListener('click',()=>dynamicImport('./Products.js','AJget_ProductsPopup',['PO']))
									newSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
								newProductAdd.appendChild(newSpan);
							searchProductPicker.appendChild(newProductAdd);
								const ProductPicker = cTag('div',{ 'class':`columnXS6 columnSM8`, 'style': "text-align: start;" });
									let productPickerBtn = cTag('button',{ 'type':`button`,'name':`showcategorylist`,'id':`product-picker-button`,'click':showProductPicker,'class':`btn productPickerButton` });
									productPickerBtn.innerHTML = Translate('Open Product Picker');
								ProductPicker.appendChild(productPickerBtn);
							searchProductPicker.appendChild(ProductPicker);
							searchProductPicker.appendChild(cTag('span',{ 'class':`error_msg`,'style':'margin-left:6px','id':`error_search_sku` }));
						tdCol.appendChild(searchProductPicker);
					moreInfoHeadRow.appendChild(tdCol);
				moreInfoBody.appendChild(moreInfoHeadRow);
					moreInfoHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style':`padding: 0`,'colspan':Math.floor(data.cols+1) });
						tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_productlist` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`pagi_index`,'id':`pagi_index`,'value':`0` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppcategory_id`,'id':`ppcategory_id`,'value':`0` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppproduct_id`,'id':`ppproduct_id`,'value':`0` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`totalrowscount`,'id':`totalrowscount`,'value':`0` }));
							let searchDiv = cTag('div',{ 'class':`flexSpaBetRow`,'id':`filterrow`,'style':'display:none;padding:10px 60px 0 50px;gap:5px'});
								let searchDivFilter = cTag('div',{ style:'display:none', 'id':`filter_name_html`});
									const searchName = cTag('div',{ 'class':`input-group` });
										const filter_name = cTag('input',{ 'maxlength':`50`,'type':`text`,'placeholder':Translate('Search name'),'value':``,'class':`form-control product-filter`,'name':`filter_name`,'id':`filter_name` });
										filter_name.addEventListener('keyup', e=>{if(e.which===13) showCategoryPPProduct()});
									searchName.appendChild(filter_name);
										let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'click':showCategoryPPProduct,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search name') });
										searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
									searchName.appendChild(searchSpan);
								searchDivFilter.appendChild(searchName);
							searchDiv.appendChild(searchDivFilter);
								const paginationDiv = cTag('div');
								paginationDiv.appendChild(cTag('label',{ 'id':`PPfromtodata` }));
							searchDiv.appendChild(paginationDiv);
								let allCategoryDiv = cTag('div',{ style:'display:none', 'id':`all-category-button`});
									const allCategoryGroup = cTag('div',{ 'class':`input-group` });
										let productCategoryLink = cTag('a',{ 'href':`javascript:void(0);`,'title':Translate('All Category List'),'click':reloadProdPkrCategory });
											let listSpan = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "background: #a71d4c; color: #FFF; border-color: #a71d4c;" });
												const labelName = cTag('label');
												labelName.innerHTML = Translate('All Category List');
											listSpan.appendChild(labelName);
										productCategoryLink.appendChild(listSpan);
									allCategoryGroup.appendChild(productCategoryLink);
								allCategoryDiv.appendChild(allCategoryGroup);
							searchDiv.appendChild(allCategoryDiv);
						tdCol.appendChild(searchDiv);
							const allProductListDiv = cTag('div',{ 'style': "position: relative;" });
								let allProductListCol = cTag('div',{ 'class':`columnSM12`, 'style': "display:none;min-height: 90px", 'id':`product-picker`});
								allProductListCol.appendChild(cTag('div',{ 'id':`allcategorylist`,style:'display:none;padding:0 50px 0 40px' }));
								allProductListCol.appendChild(cTag('div',{ style:'display:none','id':`allproductlist`,'style':"padding:0 50px 0 40px;width:100%" }));
							allProductListDiv.appendChild(allProductListCol);
								const previousBtnDiv = cTag('div',{ 'class':`prevlist`,style:'display:none'});
									const previousBtn = cTag('button',{ 'click':preNextCategory, 'style':'background:initial'});
									previousBtn.innerHTML = '‹';
								previousBtnDiv.appendChild(previousBtn);
							allProductListDiv.appendChild(previousBtnDiv);
								const nextBtnDiv = cTag('div',{ 'class':`nextlist`,style:'display:none'});
									const nextBtn = cTag('button',{ 'click':preNextCategory, 'style':'background:initial'});
									nextBtn.innerHTML = '›';
								nextBtnDiv.appendChild(nextBtn);
							allProductListDiv.appendChild(nextBtnDiv);
						tdCol.appendChild(allProductListDiv);
					moreInfoHeadRow.appendChild(tdCol);
				moreInfoBody.appendChild(moreInfoHeadRow);
			table.appendChild(moreInfoBody);
		}

			const moreInfoHead = cTag('thead');
				moreInfoHeadRow = cTag('tr');
					tdCol = cTag('th',{ 'colspan':`2`,'style': "text-align: right;" });
					tdCol.innerHTML = ' ';
				moreInfoHeadRow.appendChild(tdCol);
					tdCol = cTag('th',{ 'style': "text-align: right;" });
						let receiveLabel = cTag('label',{ 'id':`receivedQtyTotal` });
						receiveLabel.innerHTML = '0';
					tdCol.appendChild(receiveLabel);
				moreInfoHeadRow.appendChild(tdCol);
					tdCol = cTag('th',{ 'style': "text-align: right;" });
						let totalLabel = cTag('label');
						totalLabel.innerHTML = `${Translate('Total')} :`;
					tdCol.appendChild(totalLabel);
				moreInfoHeadRow.appendChild(tdCol);
					tdCol = cTag('th',{ 'style': "text-align: right;" });
						let bTag = cTag('b',{ 'id':`grand_totalstr` });
						bTag.innerHTML = currency+'0.00';
					tdCol.appendChild(bTag);
					tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_id`,'id':`po_id`,'value':data.po_id }));
					tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':`0` }));
					tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`total_item`,'id':`total_item`,'value':`0` }));
					tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`status`,'id':`status`,'value':data.status }));
					tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`supplier_id`,'id':`supplier_id`,'value':data.supplier_id }));
					tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value':segment1}));
				moreInfoHeadRow.appendChild(tdCol);
				if(data.status === 'Open'){
						tdCol = cTag('th');
						tdCol.innerHTML = ' ';
					moreInfoHeadRow.appendChild(tdCol);
				}
			moreInfoHead.appendChild(moreInfoHeadRow);
		table.appendChild(moreInfoHead);

		if(data.status === "Open"){
				moreInfoBody = cTag('tbody');
					moreInfoHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'colspan':data.cols });
							const buttonNames = cTag('div',{ 'class': "flexEndRow" });
								const completedDiv = cTag('div');
									const completedInput = cTag('div',{ 'class':`input-group` });
										let completePos = cTag('div',{ 'name':`possubmit`,'id':`possubmit`,'disabled':``,'class':`bgnone cursor`,style:'display:none' });
											let completedButton = cTag('button',{ 'class': `btn completeButton`, 'type':`button`,'click':completeIT });
												const buttonLabel = cTag('span');
												buttonLabel.innerHTML = Translate('Mark Completed');
											completedButton.appendChild(buttonLabel);
										completePos.appendChild(completedButton);
									completedInput.appendChild(completePos);
										let completePos2 = cTag('div',{ 'name':`possubmitdis`,'id':`possubmitdis`,'class':`bgnone` });
											let completedButton2 = cTag('button',{ 'class': `btn defaultButton`, 'style': "cursor: not-allowed;", 'type':`button` });
												const buttonLabel2 = cTag('span');
												buttonLabel2.innerHTML = Translate('Mark Completed');
											completedButton2.appendChild(buttonLabel2);
										completePos2.appendChild(completedButton2);
									completedInput.appendChild(completePos2);
								completedDiv.appendChild(completedInput);

								const cancelDiv = cTag('div',{ 'id':`po_cancelled`, 'style': "margin-right: 15px;" });
									const cancelInput = cTag('div',{ 'class':`input-group` });
										let cancelOnClik = cTag('button',{ 'class':`btnFocus iconButton cursor`, 'click': cancelIT });
											const removeIcon = cTag('i',{ 'class':`fa fa-remove`, 'style': "font-size: 1.5em;" });
											let cancelLabel = cTag('span');
											cancelLabel.innerHTML = Translate('Cancel');
										cancelOnClik.append(removeIcon, cancelLabel);
									cancelInput.appendChild(cancelOnClik);
								cancelDiv.appendChild(cancelInput);
							buttonNames.append(cancelDiv, completedDiv);
						tdCol.appendChild(buttonNames);
					moreInfoHeadRow.appendChild(tdCol);
						tdCol = cTag('td');
						tdCol.innerHTML = ' ';
					moreInfoHeadRow.appendChild(tdCol);
				moreInfoBody.appendChild(moreInfoHeadRow);
			table.appendChild(moreInfoBody);
		}

		document.querySelector('#spo_id').value = data.po_id;
		document.querySelector('#table_idValue').value = data.po_id;
		loadITCartData(false,data.cartsData);

		if(document.getElementById("frmSendITEmail")){
			let statusVal = document.getElementById("status").value;
			
			if(!['Closed', 'Cancel'].includes(statusVal)){
				setTimeout(function() {document.getElementById("search_sku").focus();}, 500);
			}
			// calculateIT_Total();
			if (document.getElementById('search_sku')){AJautoComplete_skuIT();}
		}
		filter_Inventory_Transfer_edit();
	}
}

async function filter_Inventory_Transfer_edit(){
    let page = 1;
	document.getElementById("page").value = page;
	const jsonData = {};
	jsonData['spo_id'] = document.getElementById('table_idValue').value;
	jsonData['shistory_type'] = document.getElementById('shistory_type').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

    const url = '/'+segment1+'/AJgetHPage/filter';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);
		
		document.getElementById("totalTableRows").value = data.totalRows;
		setTableHRows(data.tableRows, activityFieldAttributes);

		const shistory_type = document.getElementById("shistory_type");
		const shistory_typeVal = shistory_type.value;
		shistory_type.innerHTML = '';
		const option = document.createElement('option');
		option.setAttribute('value', '');
		option.innerHTML = Translate('All Activities');
		shistory_type.appendChild(option);
		setOptions(shistory_type, data.actFeeTitOpt, 0, 1);
		document.getElementById("shistory_type").value = shistory_typeVal;

		onClickPagination();
	}
}

async function loadTableRows_Inventory_Transfer_edit(){
	const jsonData = {};
	jsonData['spo_id'] = document.getElementById('table_idValue').value;
	jsonData['shistory_type'] = document.getElementById('shistory_type').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = '/'+segment1+'/AJgetHPage';
	fetchData(afterFetch,url,jsonData);
	function afterFetch(data){			
		setTableHRows(data.tableRows, activityFieldAttributes);
		onClickPagination();
	}
}

function AJautoComplete_skuIT(){
	const search_sku = document.getElementById('search_sku');
	const statusVal = document.querySelector("#status").value;
	if(!['Closed', 'Cancel'].includes(statusVal) && search_sku){
		setTimeout(function() {search_sku.focus();}, 500);	
		customAutoComplete(search_sku,{
			minLength:2,
			source: async function (request, response) {
				const jsonData = {"keyword_search":request};
				const url = "/Inventory_Transfer/AJ_search_sku/";

				await fetchData(afterFetch,url,jsonData,'JSON',0);

				function afterFetch(data){
					response(data.returnStr);
				} 
			},
			select: function( event, info ) {
				search_sku.value = info.labelval;
				addToITCart();
				return false;
			},
			renderItem : function( item ) {
				const li = cTag('li',{ 'class':`ui-menu-item` });
					const div = cTag('div');
						const b = cTag('b');
						b.innerHTML = `(${item.stockQty})`;
					div.innerHTML = item.label;
					div.append(' ',b);
				li.appendChild(div);
				return li;
			}
		})
		search_sku.addEventListener('keydown',function (event) {
			if (event.which === 13) {
				search_sku.hide();
				addToITCart();
				return false;
			}
		});
	}
}

//load IT Cart-data
function loadITCartData(event,cartsData){
	if(event) cartsData = event.detail;
	let parentNode = document.getElementById("invoice_entry_holder");
	parentNode.innerHTML = '';
	if(cartsData && cartsData.length>0){
		cartsData.forEach((item,indx) => {
			let imeiDiv, tdCol;
			let description = document.createDocumentFragment();
				let viewProduct = cTag('a',{ 'href':`/Products/view/${item.product_id}`, 'style': "color: #009; text-decoration: underline;", 'title':Translate('View Product Details') });
				viewProduct.append(item.sku,' ');
				viewProduct.appendChild(cTag('i',{ 'class':`fa fa-link` }));
			let spanTag = cTag('span');
			spanTag.innerHTML = item.product_name;
			description.append(spanTag,' (',viewProduct,')');
			
			if(item.item_type==='cellphones'){
					const imeiDivRow = cTag('div');
						imeiDiv = cTag('div',{ 'class':`columnSM12` });
						imeiDiv.append(description);
					imeiDivRow.appendChild(imeiDiv);

					item.cellPhoneData.forEach(info=>{
						let addistr = '';									
						if(info.carrier_name !==''){
							addistr = ' '+info.carrier_name;
						}

						let editremoveicon = document.createDocumentFragment();						
						if(item.status === "Open"){
							editremoveicon.append('  ',cTag('i',{ 'style':`cursor:pointer;`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove IMEI'),'click':()=>removeIMEIFromITCart(item.po_items_id, info.item_id),'class':`fa fa-trash-o` }));
						}

							imeiDiv = cTag('div',{ 'class':`columnSM12`, 'style': "padding-left: 10px;" });
							if(item.status === "Closed"){
									const imeiView = cTag('a',{ 'href':`/IMEI/view/${info.item_number}`, 'style': "color: #009; text-decoration: underline;", 'title':Translate('View IMEI details') });
									imeiView.append(info.item_number,' ');
									imeiView.appendChild(cTag('i',{ 'class':`fa fa-link` }));
								imeiDiv.appendChild(imeiView);
								imeiDiv.append(addistr);
							}
							else imeiDiv.append(info.item_number,' ',addistr,editremoveicon);
						imeiDivRow.appendChild(imeiDiv);
					})

					if(item.status ==='Open'){
							imeiDiv = cTag('div',{ 'class':`columnSM5`, 'style': "padding-left: 10px;" });
							imeiDiv.appendChild(cTag('input',{ 'type':`hidden`,'id':`temp_pos_cart_id`,'name':`temp_pos_cart_id`,'value':`0` }));
							imeiDiv.appendChild(cTag('input',{ 'class':"form-control item_number", 'name':`item_number${item.po_items_id}`,'id':`item_number${item.po_items_id}`,'title':item.po_items_id,'placeholder':Translate('IMEI Number'),'maxlength':`20` }));
						imeiDivRow.appendChild(imeiDiv);
						imeiDivRow.appendChild(cTag('div',{ 'class':`columnSM7 error_msg`, 'id':`error_item_number${item.po_items_id}` }));
					}
				description = imeiDivRow;
			}

			const del_edit = document.createDocumentFragment();
			if(item.status === 'Open'){
					tdCol = cTag('td',{ 'align':`center` });
					if(item.cellPhoneData.length==0){
						tdCol.append(cTag('i',{ 'style':`cursor:pointer;`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove Item'),'click':()=>removeThisITItem(item.po_items_id),'class':`fa fa-trash-o` }),'  ')
					}				
					tdCol.append(cTag('i',{ 'style':`cursor:pointer;`,'data-toggle':`tooltip`,'data-original-title':Translate('Edit Info'),'click':()=>changeThisITRow(item.po_items_id),'class':`fa fa-edit` }));
				del_edit.appendChild(tdCol);
			}
			const total = item.cost*item.received_qty;
			const itHeadRow = cTag('tr',{ 'class':`poRow${item.po_items_id}` });
				tdCol = cTag('td',{ 'align':`right` });
				tdCol.innerHTML = indx+1;
			itHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'align':`left` });
				tdCol.append(description);
			itHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'class':`$class`,'align':`right` });
				tdCol.innerHTML = item.received_qty;
			itHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'align':`right` });
				tdCol.innerHTML = addCurrency(item.cost);
			itHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'id':`totalstr${item.po_items_id}`,'align':`right` });
				tdCol.append(addCurrency(total));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_items_id[]`,'value':`${item.po_items_id}` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_id${item.po_items_id}`,'id':`item_id${item.po_items_id}`,'value':item.product_id }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_type${item.po_items_id}`,'id':`item_type${item.po_items_id}`,'value':item.item_type }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`cost${item.po_items_id}`,'id':`cost${item.po_items_id}`,'value':item.cost }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`received_qty${item.po_items_id}`,'id':`received_qty${item.po_items_id}`,'value':item.received_qty }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`total${item.po_items_id}`,'id':`total${item.po_items_id}`,'value':total }));
			itHeadRow.appendChild(tdCol);
			itHeadRow.append(del_edit);
			parentNode.appendChild(itHeadRow);
		});
		parentNode.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
        AJautoComplete_IMEI();
	}
	calculateIT_Total();
}

document.addEventListener('DOMContentLoaded', async()=>{	
	let layoutFunctions = {lists, add, edit};
	layoutFunctions[segment2]();     
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    addCustomeEventListener('labelSizeMissing',alert_label_missing);
});