import {
	cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, NeedHaveOnPO, DBDateToViewDate, noPermissionWarning, 
	redirectTo, printbyurl, confirm_dialog, alert_dialog, setSelectOpt, setTableHRows, showTopMessage, setOptions, alert_label_missing,
	addPaginationRowFlex, checkAndSetSessionData, popup_dialog, popup_dialog600, popup_dialog1000, date_picker, validDate, 
	checkDateOnBlur, dynamicImport, applySanitizer, generateCustomeFields, fetchData, listenToEnterKey, validifyCustomField,
	addCustomeEventListener, actionBtnClick, serialize, multiSelectAction, onClickPagination, customAutoComplete, AJautoComplete, 
	historyTable, activityFieldAttributes, autocompleteIMEI, round, calculate, controllNumericField, validateRequiredField
} from './common.js';

import {
	showCategoryPPProduct, showProductPicker, reloadProdPkrCategory, cancelemailform, emailthispage, preNextCategory
} from './cart.js';

import {smsPO} from './BulkSMS.js';

if(segment2==='') segment2 = 'lists';

let segment5,segment6,segment7;
if(pathArray.length>1){
	if(pathArray.length>7){
		[segment5,segment6,segment7] = pathArray.slice(5);
	}
}

const listsFieldAttributes = [
	{'data-title':Translate('Date'), 'align':'left'},
	{'data-title':Translate('PO'), 'align':'right'},
	{'data-title':Translate('Lot Ref. No.'), 'align':'left'},
	{'data-title':Translate('Supplier'), 'align':'left'},
	{'data-title':Translate('Sales Tax'), 'align':'right'},
	{'data-title':Translate('Shipping Cost'), 'align':'right'},
	{'data-title':Translate('Total'), 'align':'right'},
	{'data-title':Translate('Expected'), 'align':'right'},
	{'data-title':Translate('Return'), 'align':'center'},
	{'data-title':Translate('Status'), 'align':'left'}
];

const uriStr = segment1+'/edit';

async function filter_Purchase_orders_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById("ssorting_type").value;
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
		
		createListRows(data.tableRows);
		document.getElementById("totalTableRows").value = data.totalRows;
		document.getElementById("ssuppliers_id").value = ssuppliers_id;
		
		onClickPagination();
    }
}

async function loadTableRows_Purchase_orders_lists(){
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById("ssorting_type").value;
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
        createListRows(data.tableRows);
		onClickPagination();
    }
}

function createListRows(data){
    const table = document.getElementById("tableRows");
    table.innerHTML = '';
	if(data.length){
		data.forEach(item=>{
			const row = cTag('tr');
			item.forEach((itemInfo,indx)=>{
				if([0,5].includes(indx)) return;
				const cell = cTag('td');
				const attributes = listsFieldAttributes[indx-1];
				for (const key in attributes) {
					cell.setAttribute(key,attributes[key]);
				}
	
				if([1,9].includes(indx)){itemInfo = DBDateToViewDate(itemInfo, 1, 1)[0];}

				else if(indx===2){itemInfo = 'p'+itemInfo;}
				else if(indx===6){
					if(item[5]===0) itemInfo = addCurrency(itemInfo);
					else itemInfo = itemInfo+'%';
				}
				else if([7,8].includes(indx)){itemInfo = addCurrency(itemInfo);}
				else if(indx===10){
					if(itemInfo>0) itemInfo = 'Return';
					else itemInfo = ''; 
				}
				if(itemInfo===''){itemInfo = '\u2003'; }
		
				if(item[10]===1 && item[11] !== 'Closed'){
					const link = cTag('a',{'class':`anchorfulllink`, 'href':`/Purchase_orders/confirmReturn/${item[0]}`});
					link.innerHTML = itemInfo;
					cell.appendChild(link);
				}
				else{
					const link = cTag('a',{'class':`anchorfulllink`, 'href':`/${uriStr}/${item[0]}`});
					link.innerHTML = itemInfo;
					cell.appendChild(link);
				}
				row.appendChild(cell);
			})
			table.appendChild(row);
		})
	}	
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: 30 },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

		let optionsData, sortDropDown;
		const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
			const headerTitle = cTag('h2');
			headerTitle.innerHTML = Translate('Purchase Order')+' ';
				const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays a list of Open and Closed purchase orders')});
			headerTitle.appendChild(infoIcon);
		titleRow.appendChild(headerTitle);

			const createButton = cTag('button', {title: Translate('Create Purchase Order'), class: "btn createButton"});
				const strong = cTag('span');
				strong.append(cTag('i', {class: "fa fa-plus"}), ' ');
				if(OS =='unknown'){strong.append(Translate('Create Purchase Order'));}
				else{strong.append(Translate('Purchase Order'));}
			createButton.appendChild(strong);
			createButton.addEventListener('click', function (){window.location = '/Purchase_orders/add';});
		titleRow.appendChild(createButton);
    showTableData.appendChild(titleRow);

		const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
			sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
				const selectSorting = cTag('select', {class: "form-control", name: "ssorting_type", id: "ssorting_type"});
				selectSorting.addEventListener('change', filter_Purchase_orders_lists);
					optionsData = {
						'0':Translate('Date')+', '+Translate('PO'), 
						'1':Translate('Date'), 
						'2':Translate('PO Number'), 
						'3':Translate('Date Expected')
					};
					setOptions(selectSorting, optionsData, 1, 0);
			sortDropDown.appendChild(selectSorting);
		filterRow.appendChild(sortDropDown);

			sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
				const selectView = cTag('select', {class: "form-control", name: "sview_type", id: "sview_type"});
				selectView.addEventListener('change', filter_Purchase_orders_lists);
					optionsData = {'Open':Translate('Open'), 'Closed':Translate('Closed'), 'return_po':Translate('All Returns'), 'unpaid_po': Translate('Unpaid PO'), '':Translate('All Types')};
					setOptions(selectView, optionsData, 1, 0);
			sortDropDown.appendChild(selectView);
		filterRow.appendChild(sortDropDown)

			sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
				const selectSuppliers = cTag('select', {class: "form-control", name: "ssuppliers_id", id: "ssuppliers_id"});
				selectSuppliers.addEventListener('change', filter_Purchase_orders_lists);
					const suppliersOption = cTag('option', {'value': 0});
					suppliersOption.innerHTML =Translate('All Suppliers');
				selectSuppliers.appendChild(suppliersOption);
			sortDropDown.appendChild(selectSuppliers);
		filterRow.appendChild(sortDropDown)

			const searchDiv = cTag('div', {class: "columnXS6 columnSM3"});
				const SearchInGroup = cTag('div', {class: "input-group"});
					const searchField = cTag('input', {keydown: listenToEnterKey(filter_Purchase_orders_lists),'type': "text", 'placeholder': Translate('PO / Lot Ref./Suppliers Inv. No.'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
				SearchInGroup.appendChild(searchField);
					const searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('PO / Lot Ref./Suppliers Inv. No.')});
					searchSpan.addEventListener('click', filter_Purchase_orders_lists);
						const searchIcon = cTag('i', {class: "fa fa-search"});
					searchSpan.appendChild(searchIcon);
				SearchInGroup.appendChild(searchSpan);
			searchDiv.appendChild(SearchInGroup);
		filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

		const divTable = cTag('div', {class: "columnXS12"});
			const divNoMore = cTag('div', {id: "no-more-tables"});
				const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
					const listHead = cTag('thead', {class: "cf"});
						const columnNames = listsFieldAttributes.map(colObj=>(colObj['data-title']));
						const listHeadRow = cTag('tr',{class:'outerListsTable'});
							const thCol0= cTag('th', {'style': "width: 80px;"});
							thCol0.innerHTML=columnNames[0];

							const thCol1 = cTag('th', {'width': "7%"});
							thCol1.innerHTML=columnNames[1];

							const thCol2 = cTag('th', {'width': "10%"});
							thCol2.innerHTML=columnNames[2];

							const thCol3 = cTag('th');
							thCol3.innerHTML=columnNames[3];
							
							const thCol4 = cTag('th', {'width': "8%"});
							thCol4.innerHTML=columnNames[4];

							const thCol5 = cTag('th', {'width': "8%"});
							thCol5.innerHTML=columnNames[5];

							const thCol6 = cTag('th', {'width': "8%"});
							thCol6.innerHTML=columnNames[6];

							const thCol7 = cTag('th', {'style': "width: 80px;"});
							thCol7.innerHTML=columnNames[7];

							const thCol8 = cTag('th', {'width': "7%"});
							thCol8.innerHTML=columnNames[8];

							const thCol9 = cTag('th', {'width': "7%"});
							thCol9.innerHTML=columnNames[9];
						listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8, thCol9);
					listHead.appendChild(listHeadRow);
				listTable.appendChild(listHead);

					const listBody = cTag('tbody', {id: "tableRows"});
				listTable.appendChild(listBody);
			divNoMore.appendChild(listTable);
		divTable.appendChild(divNoMore);
    showTableData.appendChild(divTable);
    addPaginationRowFlex(showTableData);

    //=======sessionStorage =========//
	let	list_filters = '';
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    
    const ssorting_type = '0', sview_type = 'Open', ssuppliers_id = 0;

	checkAndSetSessionData('ssorting_type', ssorting_type, list_filters);
	checkAndSetSessionData('sview_type', sview_type, list_filters);
	checkAndSetSessionData('ssuppliers_id', ssuppliers_id, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

	addCustomeEventListener('filter',filter_Purchase_orders_lists);
	addCustomeEventListener('loadTable',loadTableRows_Purchase_orders_lists);
	filter_Purchase_orders_lists(true)
}

async function AJsend_POEmail(event = false){
	if(event){event.preventDefault();}
	const oField = document.getElementById("email_address");
	const email_address = oField.value;
	const po_id = document.getElementById("po_id").value;

    const jsonData = {};
    jsonData['po_id'] = po_id;
    jsonData['email_address'] = email_address;

    const url = '/'+segment1+'/AJsend_POEmail/';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr !=='Ok'){
            showTopMessage('alert_msg', Translate('Sorry! Could not send mail. Try again later.'));
		}
		else{
			document.querySelectorAll('.emailform').forEach(oneField=>{
				if(oneField.style.display !== 'none'){
					oneField.style.display = 'none';
				}
			});
			filter_Purchase_orders_edit();
		}
    }
	return false;
}

function ReceiveAllProducts(){								
	confirm_dialog(Translate('ARE YOU SURE?'), Translate('Please confirm you want to make all products (not mobile devices) received in full'), confirmReceivingAllProducts);
}

async function confirmReceivingAllProducts(hidePopup){
	const po_id = document.getElementById("po_id").value;

    const jsonData = {};
	jsonData['po_id'] = po_id;

    const url = '/'+segment1+'/confirmReceiveAllP';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action === 'Update'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();
			hidePopup();
		}
		else{
            showTopMessage('error_msg', Translate('There is no none cellphone product found on PO Cart which is Ordered Qty>Received Qty.'));
		}
    }
}

async function filter_Purchase_orders_edit(){
    let page = 1;
	document.getElementById("page").value = page;
	const jsonData = {};
	jsonData['spo_id'] = document.getElementById("table_idValue").value;
	jsonData['shistory_type'] = document.getElementById("shistory_type").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

    const url = '/'+segment1+'/AJgetHPage/filter';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);			
		let selectHistory = document.getElementById("shistory_type");
		selectHistory.innerHTML = '';
			let allOption = cTag('option');
			allOption.value = '';
			allOption.innerHTML = Translate('All Activities');
		selectHistory.appendChild(allOption);
		setOptions(selectHistory,data.actFeeTitOpt,0,1);
		selectHistory.value = jsonData['shistory_type'];
		document.getElementById("totalTableRows").value = data.totalRows;
		setTableHRows(data.tableRows, activityFieldAttributes);
		onClickPagination();
    }
}

async function loadTableRows_Purchase_orders_edit(){
	const jsonData = {};
	jsonData['spo_id'] = document.getElementById("table_idValue").value;
	jsonData['shistory_type'] = document.getElementById("shistory_type").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetHPage';
	fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        setTableHRows(data.tableRows, activityFieldAttributes);
		onClickPagination();
    }
}

async function savePO(event = false){
	if(event){event.preventDefault();}  

	const shippingCostField = document.getElementById('shipping'); 
	const taxesField = document.getElementById('taxes'); 
	if(!shippingCostField.valid()) return;
	if(!taxesField.valid()) return;
	
    document.getElementById('error_taxes').innerHTML = '';

    let invoice_date = document.getElementById('invoice_date');
    if(invoice_date.value!=='' && !validDate(invoice_date.value)) return; 

    let date_paid = document.getElementById('date_paid');
    if(date_paid.value!=='' && !validDate(date_paid.value)) return; 

	const returnval = parseInt(document.getElementById('return_po').value);
	let saveBtn = document.getElementById('submit');
	if(returnval===0){        
        saveBtn.innerHTML = Translate('Saving')+'...';
        saveBtn.disabled = true;

        const jsonData = serialize('#frmpo');
        
        const url = '/'+segment1+'/AJ_save_po/';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.savemsg !=='error' && data.id>0){
                window.location = '/Purchase_orders/edit/'+data.id+'/'+data.savemsg;
            }
            else{
                showTopMessage('alert_msg', Translate('Error occured while adding PO information! Please try again.'));
                if(parseInt(document.frmpo.po_id.value)===0){
                    saveBtn.innerHTML = Translate('Add');
                    saveBtn.disabled = false;
                }
                else{
                    saveBtn.innerHTML = Translate('Update');
                    saveBtn.disabled = false;
                }
            }
		}
		return false;
	}
	else{		
		saveBtn.innerHTML = Translate('Saving')+'...';
        saveBtn.disabled = true;
		window.localStorage.setItem('returnPOform',JSON.stringify(serialize('#frmpo')));
		window.location = '/Purchase_orders/returnPO';
	}
	return false;
}


export function AJautoComplete_ProductName(){
	if(document.querySelector("#product_name")){
		customAutoComplete(document.querySelector("#product_name"),{
			minLength: 2,
			source: async function (request, response) {
				const jsonData = {"product_name":request, "supplier_id":document.getElementById('supplier_id').value};
				const url = "/Purchase_orders/AJ_PO_Supplier_Product";

				await fetchData(afterFetch,url,jsonData,'JSON',0);

				function afterFetch(data){
					response(data.returnStr);
				}
			},
			select: function( event, info ) {
				let span;
				document.getElementById('errmsg_product_name').innerHTML = '';	
				const productnamestr = document.getElementById('productnamestr');
				productnamestr.innerHTML = '';
					const div = cTag('div',{ 'class':`input-group` });
					div.appendChild(cTag('input',{ 'maxlength':`100`,'readonly':``,'type':`text`,'value':info.label,'required':`required`,'name':`product_name`,'id':`product_name`,'class':`form-control` }));
						span = cTag('span',{ 'data-toggle':`tooltip`,'title':Translate('Clear Product'),'class':`input-group-addon cursor`,'click':clearProductField });
						span.appendChild(cTag('i',{ 'class':`fa fa-edit` }));
						span.append(' '+Translate('Change'));
					div.appendChild(span);
				productnamestr.appendChild(div);
				
				document.querySelector("#product_id" ).value = info.id;
				if(document.getElementById("qty_or_imeirow").style.display === 'none'){
					document.getElementById("qty_or_imeirow").style.display = '';
				}
				document.querySelector("#product_type" ).value = info.prdty;
				document.querySelector("#maxqty" ).value = info.inv;
				document.querySelector("#qty_or_imei" ).value = '';
				document.getElementById('qty_or_imei').value = '';
				document.getElementById('item_id').value = 0;
	
				const qty_or_imeilv = document.querySelector("#qty_or_imeilv");
				qty_or_imeilv.innerHTML = '';
				span = cTag('span',{ 'class':`required` });
				span.innerHTML = '*';
				
				if(info.prdty==='Live Stocks'){					
					qty_or_imeilv.append(Translate('IMEI Number'),span);
					autocompleteIMEI();
				}
				else{
					qty_or_imeilv.append(Translate('QTY'),span);
				}
				return false;
			}
		});
	}
}

function clearProductField(){
    const productnamestr = document.getElementById("productnamestr");
    productnamestr.innerHTML = '';
        const input = cTag('input', {'maxlength': 100, 'type': "text", 'value': "", 'required': "required", name: "product_name", id: "product_name", class: "form-control"});
	productnamestr.appendChild(input);
	if(document.getElementById("qty_or_imeirow").style.display !== 'none'){
		document.getElementById("qty_or_imeirow").style.display = 'none';
	}
	document.getElementById('product_id').value = 0;
	document.getElementById('item_id').value = 0;
	
	AJautoComplete_ProductName();
}	

async function saveReturnPO(event=false){
	if(event){event.preventDefault();}

	let oElement ;

	const product_name = document.getElementById('product_name');
	const product_id = parseInt(document.getElementById('product_id').value);
	oElement = document.getElementById('errmsg_product_name');
	oElement.innerHTML = "";
	if(product_id ==='' || product_id === 0){
		oElement.innerHTML = Translate('You have to select product from dropdown.');
		product_name.focus();
		return(false);
	}
	
	const product_type = document.getElementById('product_type').value;
	oElement.innerHTML = "";
	if(product_type ==='' || product_type === 0){
		oElement.innerHTML = Translate('You have to select product from dropdown.');
		product_name.focus();
		return(false);
	}
	
	const supplier_id = parseInt(document.getElementById('supplier_id').value);
	oElement.innerHTML = "";
	if(supplier_id ==='' || supplier_id === 0){
		oElement.innerHTML = Translate('Missing supplier name');
		product_name.focus();
		return(false);
	}
	
	let maxqty = parseInt(document.getElementById('maxqty').value);
	if(isNaN(maxqty) || maxqty===''){maxqty = 0;}
	const qty_or_imei = document.getElementById('qty_or_imei');
	let qty_or_imeiVal = parseFloat(qty_or_imei.value);
	if(isNaN(qty_or_imeiVal) || qty_or_imeiVal===''){qty_or_imeiVal = 0;}

	if(product_type==='Live Stocks'){
		qty_or_imeiVal = qty_or_imei.value.length;
		if(isNaN(qty_or_imeiVal) || qty_or_imeiVal===''){qty_or_imeiVal = 0;}
	}
	
	oElement = document.getElementById('errmsg_qty_or_imei');
	oElement.innerHTML = "";
	if(qty_or_imeiVal === 0){
		if(product_type==='Live Stocks'){
			oElement.innerHTML = Translate('Missing IMEI Number');
		}
		else{
			oElement.innerHTML = Translate('Missing QTY');
		}                        
		qty_or_imei.focus();
		return(false);
	}
	else if(product_type !=='Live Stocks' && qty_or_imeiVal>maxqty){
		oElement.innerHTML = Translate('Max qty can be')+" "+maxqty;
		qty_or_imei.focus();
		return(false);
	}
	
	const item_id = parseInt(document.getElementById('item_id').value);
	oElement.innerHTML = "";
	if(product_type==='Live Stocks' && (item_id===0 || item_id==='')){
		oElement.innerHTML = Translate('You have to select IMEI Number from dropdown.');
		qty_or_imei.focus();
		return(false);
	}
	
    const saveBtn = document.getElementById('submit');
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;

	const jsonData = serialize('#frmReturnPO');
    const url = '/'+segment1+'/AJ_save_returnpo/';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
			window.localStorage.removeItem('returnPOform');
			window.location = '/Purchase_orders/confirmReturn/'+data.id+'/'+data.savemsg;
		}
		else{
            showTopMessage('alert_msg', Translate('Error occured while adding PO information! Please try again.'));
            const confirmBtn = document.getElementById('submit');
            confirmBtn.innerHTML = Translate('Confirm Return');
            confirmBtn.disabled = false;
		}
    }
	return false;
}

async function saveConfirmReturn(event=false){
	if(event){event.preventDefault();}
	
    const saveBtn = document.getElementById('submit');
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;

	const jsonData = serialize('#frmConfirmReturn');
    const url = '/'+segment1+'/save_confirmReturn/';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
			window.location = '/Purchase_orders/edit/'+data.id+'/'+data.savemsg;
		}
		else{
            showTopMessage('alert_msg', data.message);
            const confirmBtn = document.getElementById('submit');
            confirmBtn.innerHTML = Translate('Mark Completed');
            confirmBtn.disabled = false;
		}
    }
	return false;				
}

async function changePOInfo(){
	const po_id = document.getElementById("po_id").value;
	if(po_id>0){
        const jsonData = {};
        jsonData['po_id'] = po_id;

        const url = '/'+segment1+'/showPOData';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			const formDialog = cTag('div');
				const errorMsg = cTag('div', {id: "error_customer",class: "errormsg"});
			formDialog.appendChild(errorMsg);
				let inputField;
				const changePOInfoForm = cTag('form', {'action': "#", name: "frmchangePOInfo", id: "frmchangePOInfo", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
				changePOInfoForm.addEventListener('submit', saveChangePOInfo);
				changePOInfoForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
					const poNoRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left" });
						const poNoTitle = cTag('div', {class: "columnSM4"});
							const poNoLabel = cTag('label', {'for': "po_number"});
							poNoLabel.innerHTML = Translate('Purchase Order No.');
						poNoTitle.appendChild(poNoLabel);
					poNoRow.appendChild(poNoTitle);
						const poNoField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'readonly': "", 'type': "text", class: "form-control", name: "po_number", id: "po_number", 'value': data.po_number, 'maxlength': 11});
						poNoField.appendChild(inputField);
					poNoRow.appendChild(poNoField);
				changePOInfoForm.appendChild(poNoRow);

					const refNoRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const refNoTitle = cTag('div', {class: "columnSM4"});
							const refNoLabel = cTag('label', {'for': "lot_ref_no"});
							refNoLabel.innerHTML = Translate('Lot Ref. No.');
						refNoTitle.appendChild(refNoLabel);
					refNoRow.appendChild(refNoTitle);
						const refNoField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'type': "text", class: "form-control", name: "lot_ref_no", id: "lot_ref_no", 'value': data.lot_ref_no, 'maxlength': 60});
						refNoField.appendChild(inputField);
					refNoRow.appendChild(refNoField);
				changePOInfoForm.appendChild(refNoRow);

					const expectedDateRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const expectedDateTitle = cTag('div', {class: "columnSM4"});
							const expectedDateLabel = cTag('label', {'for': "date_expected"});
							expectedDateLabel.innerHTML = Translate('Date Expected');
						expectedDateTitle.appendChild(expectedDateLabel);
					expectedDateRow.appendChild(expectedDateTitle);
						const expectedDateField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'type': "text", class: "form-control", name: "date_expected", id: "date_expected", 'value': DBDateToViewDate(data.date_expected), 'maxlength': 10});
						expectedDateField.appendChild(inputField);
					expectedDateRow.appendChild(expectedDateField);
				changePOInfoForm.appendChild(expectedDateRow);

					const taxesRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const salesTaxTitle = cTag('div', {class: "columnSM4"});
							const salesTaxLabel = cTag('label', {'for': "taxes"});
							salesTaxLabel.innerHTML = Translate('Sales Tax');
						salesTaxTitle.appendChild(salesTaxLabel);
					taxesRow.appendChild(salesTaxTitle);
						const taxesField = cTag('div', {class: "columnSM8"});
							const taxesFieldRow = cTag('div', {class: "flexSpaBetRow"});
								const taxAmount = cTag('div', {class: "columnXS8", 'style': "padding-right: 0;"});
									const inputTaxes = cTag('input', {'type':'text',id: "taxes", name: "taxes", 'value': round(data.taxes,3),'data-min':'0','data-format':'d.ddd', 'data-max': '999999.999', class: "form-control"});
									controllNumericField(inputTaxes, '#error_taxes');
								taxAmount.appendChild(inputTaxes);
								taxAmount.appendChild(cTag('span',{'class':'errormsg','id':'error_taxes'}));
							taxesFieldRow.appendChild(taxAmount);
								const taxPercentColumn = cTag('div', {class: "columnXS4"});
									const selectTaxIsPercent = cTag('select', {id: "tax_is_percent", name: "tax_is_percent", class: "form-control", 'style': "padding-left: 0; padding-right: 0;"});
										let percentOption = cTag('option', {'value': 1});
										percentOption.innerHTML = '%';
									selectTaxIsPercent.appendChild(percentOption);
										let moneyOption = cTag('option', {'value': 0});
										if(data.tax_is_percent===0){moneyOption.setAttribute('selected', true);}
										moneyOption.innerHTML = currency;
									selectTaxIsPercent.appendChild(moneyOption);
								taxPercentColumn.appendChild(selectTaxIsPercent);
							taxesFieldRow.appendChild(taxPercentColumn);
						taxesField.appendChild(taxesFieldRow);
					taxesRow.appendChild(taxesField);
				changePOInfoForm.appendChild(taxesRow);

					const shippingRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const shippingTitle = cTag('div', {class: "columnSM4"});
							const shippingLabel = cTag('label', {'for': "shipping"});
							shippingLabel.innerHTML = Translate('Shipping Cost');
						shippingTitle.appendChild(shippingLabel);
					shippingRow.appendChild(shippingTitle);
						const shippingCostField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'999999.99','data-format':'d.dd', name: "popup_shipping", id: "popup_shipping", 'value': round(data.shipping,2), class: "form-control"});
							controllNumericField(inputField, '#error_shipping');
						shippingCostField.appendChild(inputField);
						shippingCostField.appendChild(cTag('span',{'class':'errormsg','id':'error_shipping'}));
					shippingRow.appendChild(shippingCostField);
				changePOInfoForm.appendChild(shippingRow);

					const supplierInvoiceRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const supplierInvoiceTitle = cTag('div', {class: "columnSM4"});
							const supplierInvoiceLabel = cTag('label', {'for': "suppliers_invoice_no"});
							supplierInvoiceLabel.innerHTML = Translate('Suppliers Invoice No.');
						supplierInvoiceTitle.appendChild(supplierInvoiceLabel);
					supplierInvoiceRow.appendChild(supplierInvoiceTitle);
						const supplierInvoiceField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'type': "text", class: "form-control",name: "suppliers_invoice_no", id: "suppliers_invoice_no", 'value': data.suppliers_invoice_no, 'maxlength': 20});
						supplierInvoiceField.appendChild(inputField);
					supplierInvoiceRow.appendChild(supplierInvoiceField);
				changePOInfoForm.appendChild(supplierInvoiceRow);

					const invoiceDateRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const invoiceDateTitle = cTag('div', {class: "columnSM4"});
							const invoiceDateLabel = cTag('label', {'for': "invoice_date"});
							invoiceDateLabel.innerHTML = Translate('Invoice Date');
						invoiceDateTitle.appendChild(invoiceDateLabel);
					invoiceDateRow.appendChild(invoiceDateTitle);
						const invoiceDateField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'type': "text", class: "form-control",name: "invoice_date", id: "invoice_date", 'value': DBDateToViewDate(data.invoice_date), 'maxlength': 10});
							checkDateOnBlur(inputField,'#error_customer','Invalid Invoice Date');
						invoiceDateField.appendChild(inputField);
					invoiceDateRow.appendChild(invoiceDateField);
				changePOInfoForm.appendChild(invoiceDateRow);

					const datePaidRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const datePaidTitle = cTag('div', {class: "columnSM4"});
							const datePaidLabel = cTag('label', {'for': "date_paid"});
							datePaidLabel.innerHTML = Translate('Date Paid');
						datePaidTitle.appendChild(datePaidLabel);
					datePaidRow.appendChild(datePaidTitle);
						const datePaidField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'type': "text", class: "form-control",name: "date_paid", id: "date_paid", 'value': DBDateToViewDate(data.date_paid), 'maxlength': 10});
							checkDateOnBlur(inputField,'#error_customer','Invalid Date Paid');
						datePaidField.appendChild(inputField);
					datePaidRow.appendChild(datePaidField);
				changePOInfoForm.appendChild(datePaidRow);

					const paidByRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
						const paidByTitle = cTag('div', {class: "columnSM4"});
							const paidByLabel = cTag('label', {'for': "paid_by"});
							paidByLabel.innerHTML = Translate('Paid By');
						paidByTitle.appendChild (paidByLabel);
					paidByRow.appendChild(paidByTitle);
						const paidByField = cTag('div', {class: "columnSM8"});
							inputField = cTag('input', {'type': "text", class: "form-control",name: "paid_by", id: "paid_by", 'value': data.paid_by, 'maxlength': 20});
						paidByField.appendChild(inputField);
					paidByRow.appendChild(paidByField);
				changePOInfoForm.appendChild(paidByRow);

					inputField = cTag('input', {'type': "hidden", name: "po_id", 'value': po_id});
				changePOInfoForm.appendChild(inputField);
			formDialog.appendChild(changePOInfoForm);

			popup_dialog600(Translate('Change Purchase Order Information'),formDialog,Translate('Save'), saveChangePOInfo);
			
			setTimeout(function() {
				document.getElementById("lot_ref_no").focus();
				updateTaxFieldAttributes.apply(changePOInfoForm);
				selectTaxIsPercent.addEventListener('change', function(){
					updateTaxFieldAttributes.apply(changePOInfoForm);
				});
				date_picker('#date_expected');
				date_picker('#invoice_date');
				date_picker('#date_paid');
				applySanitizer(formDialog);
			}, 500);
		}
	}
	return true;
}

function updateTaxFieldAttributes(){
    let is_percent = parseInt(this.querySelector('#tax_is_percent').value);
    let taxes = this.querySelector('#taxes');
    let error_taxes = this.querySelector('#error_taxes');

	if(is_percent===1){
        taxes.setAttribute('data-max','99.999');
        if(taxes.value>99.999) error_taxes.innerHTML = "Tax can't be > than 99.999";

	}
    else{
        taxes.setAttribute('data-max','999999.999');
        error_taxes.innerHTML = '';
    }	
}

async function saveChangePOInfo(hidePopup){
    document.getElementById('error_customer').innerHTML = '';
    document.getElementById('error_taxes').innerHTML = '';
    document.getElementById('error_shipping').innerHTML = '';

	let taxes = document.querySelector("#popup #taxes");
    if (!taxes.valid()) return;

	let popup_shipping = document.getElementById("popup_shipping");
    if (!popup_shipping.valid()) return;

    let invoice_date = document.getElementById('invoice_date');
    if(invoice_date.value!=='' && !validDate(invoice_date.value)){
        document.getElementById('error_customer').innerHTML = 'Invalid Invoice Date';
        return;
    } 

    let date_paid = document.getElementById('date_paid');
    if(date_paid.value!=='' && !validDate(date_paid.value)){
        document.getElementById('error_customer').innerHTML = 'Invalid Date Paid';
        return;
    } 

	actionBtnClick('.btnmodel', Translate('Saving'), 1);

	const jsonData = serialize('#frmchangePOInfo');

    const url = '/'+segment1+'/saveChangePO';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr==='error'){
			showTopMessage('error_msg', Translate('Error occured while save changes this device information.'));
		}
		else{
			document.querySelector('#date_expectedStr').innerHTML = DBDateToViewDate(data.date_expected, 0, 1);
			document.querySelector('#shippingstr').innerHTML = addCurrency(data.shipping);
			document.querySelector('#lot_ref_nostr').innerHTML = data.lot_ref_no;
			document.querySelector('#taxes').value = data.taxes;
			document.querySelector('#tax_is_percent').value = data.tax_is_percent;
			document.querySelector('#shipping').value = data.shipping;
			const taxLabel = document.getElementById('taxLabel');

			if(data.taxes==0) document.getElementById('taxesRow').style.display = 'none';
			else document.getElementById('taxesRow').style.display = '';
			if(data.shipping==0) document.getElementById('shippingRow').style.display = 'none';
			else document.getElementById('shippingRow').style.display = '';
			if(data.taxes==0 && data.shipping==0) document.getElementById('grandTotalRow').style.display = 'none';
			else document.getElementById('grandTotalRow').style.display = '';

			if(data.tax_is_percent) taxLabel.innerHTML = `${Translate('Taxes')} (${data.taxes}%) :`;
			else taxLabel.innerHTML = Translate('Taxes')+' :';
			poCartsAutoFuncCall();
			hidePopup();			
		}
    }
	return false;
}

async function updatePOItem(po_items_id, item_number){
	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;
	jsonData['item_number'] = item_number;

	const url = '/'+segment1+'/updatePOItem';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action ==='Add' || data.action === 'Update'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();
			
			if(data.action==='Update'){
				showTopMessage('success_msg', Translate('This product already added into Order.'));
			}
		}
		else{
			showTopMessage('alert_msg', Translate('Duplicate IMEI Number found'));
		}
		if(document.querySelector("#item_number"+po_items_id)){
			document.getElementById("item_number"+po_items_id).value = '';
			document.getElementById("item_number"+po_items_id).focus();
		}
    }
}					

async function removeThisPOItem(po_items_id){
	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;

	const url = '/'+segment1+'/removeThisPOItem';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action ==='Removed'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();
		}
		else{
			showTopMessage('success_msg', Translate('Could not removed from purchase order list'));
		}
    }
}

async function removeIMEIFromPOCart(po_items_id, item_id){
	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;
	jsonData['item_id'] = item_id;

	const url = '/'+segment1+'/removeIMEIFromPOCart';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action ==='Removed'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();
		}
		else if(data.action ==='UsedOnPOSCart'){
			alert_dialog(Translate('Remove IMEI'), Translate('This IMEI can not be removed because it has already been sold or transferred.'), Translate('Ok'));
		}
		else{
			showTopMessage('error_msg', Translate('Could not removed from purchase order list'));
		}
    }
}

function changeThisPORow(po_items_id){
	const cost = document.getElementById("cost"+po_items_id).value;
	const ordered_qty = document.getElementById("ordered_qty"+po_items_id).value;   
	const received_qty = document.getElementById("received_qty"+po_items_id).value;
	const item_type = document.getElementById("item_type"+po_items_id).value;
	
	const formDialog = cTag('div');
		let bulkimei, inputField, requiredField, errorSpan, bTag;
		const poRowform = cTag('form', {'action': "#", name: "frmPORow", id: "frmPORow", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			const costRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
				const costTitle = cTag('div', {class: "columnSM3"});
					const costLabel = cTag('label', {'for': "cost"});
					costLabel.innerHTML = Translate('Cost')+': ';
				costTitle.appendChild(costLabel);
			costRow.appendChild(costTitle);
				const costColumn = cTag('div', {class: "columnSM4"});
					inputField = cTag('input', {'type': "text",'data-min':'-9999999.99','data-max':'9999999.99','data-format':'d.dd', class: "form-control calculatePORowTotal", name: "cost", id: "cost", 'value': cost});
					controllNumericField(inputField, '#errmsg_cost');
				costColumn.appendChild(inputField);
				costColumn.appendChild(cTag('span', {class: "error_msg", id: "errmsg_cost"}));
			costRow.appendChild(costColumn);
				const costValue = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "cost_str"});
					bTag.innerHTML = currency+''+0.00;
				costValue.appendChild(bTag);
			costRow.appendChild(costValue);
		poRowform.appendChild(costRow);

			const orderedQtyRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
				const orderedQtyTitle = cTag('div', {class: "columnSM3"});
					const orderedQtyLabel = cTag('label', {'for': "ordered_qty"});
					orderedQtyLabel.innerHTML = Translate('Ordered Qty');
						requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML = "*";
					orderedQtyLabel.appendChild(requiredField);
				orderedQtyTitle.appendChild(orderedQtyLabel);
			orderedQtyRow.appendChild(orderedQtyTitle);
				const orderedQtyColumn = cTag('div', {class: "columnSM4"});
					inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'9999', 'data-format': 'd',  class: "form-control calculatePORowTotal", name: "ordered_qty", id: "ordered_qty", 'value': ordered_qty});
					controllNumericField(inputField, '#errmsg_ordered_qty');
				orderedQtyColumn.appendChild(inputField);
					errorSpan = cTag('span', {class: "error_msg", id: "errmsg_ordered_qty"});
				orderedQtyColumn.appendChild(errorSpan);
			orderedQtyRow.appendChild(orderedQtyColumn);
				const orderedQtyValue = cTag('div', {class: "columnSM5", 'align': "right"});
				orderedQtyValue.innerHTML = Translate('Subtotal')+': ';
					bTag = cTag('b', {id: "ordered_qty_value_str"});
					bTag.innerHTML = currency+''+0.00;
				orderedQtyValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "ordered_qty_value", id: "ordered_qty_value", 'value': 0});
				orderedQtyValue.appendChild(inputField);
			orderedQtyRow.appendChild(orderedQtyValue);
		poRowform.appendChild(orderedQtyRow);

			const receivedQtyRow = cTag('div', {class: "flex borderbottom", 'align': "left", 'style': "align-items: center;"});
				const receivedQtyTitle = cTag('div', {class: "columnSM3"});
					const receivedQtyLabel = cTag('label', {'for': "received_qty"});
					receivedQtyLabel.innerHTML = Translate('Received Qty');
						requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML = "*";
					receivedQtyLabel.appendChild(requiredField);
				receivedQtyTitle.appendChild(receivedQtyLabel);
			receivedQtyRow.appendChild(receivedQtyTitle);
				const receivedQtyColumn = cTag('div', {class: "columnSM4"});
					inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'9999','data-format':'d', class: "form-control calculatePORowTotal", name: "received_qty", id: "received_qty", 'value': received_qty});
					controllNumericField(inputField,'#errmsg_received_qty');
					if(item_type==='cellphones'){
						inputField.setAttribute('readonly', true);
					}
				receivedQtyColumn.appendChild(inputField);
					errorSpan = cTag('span', {class: "error_msg", id: "errmsg_received_qty"});
				receivedQtyColumn.appendChild(errorSpan);
			receivedQtyRow.appendChild(receivedQtyColumn);
				const receivedQtyValue = cTag('div', {class: "columnSM5", 'align': "right"});
				receivedQtyValue.innerHTML = Translate('Subtotal')+': ';
					bTag = cTag('b', {id: "received_qty_value_str"});
					bTag.innerHTML = currency+''+0.00;
				receivedQtyValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "received_qty_value", id: "received_qty_value", 'value': 0});
				receivedQtyValue.appendChild(inputField);
			receivedQtyRow.appendChild(receivedQtyValue);
		poRowform.appendChild(receivedQtyRow);

			const imeiPerLineRow = cTag('div', {class: "flex", 'align': "right"});
				const imeiPerLineColumn = cTag('div', {class: "columnSM7"});
				if(item_type==='cellphones'){
					bulkimei = cTag('textarea', {'placeholder': Translate('One IMEI number per line'), name: "bulkimei", id: "bulkimei", 'cols': 20, 'rows': 3, class: "form-control",style:'display:none'});
					imeiPerLineColumn.appendChild(bulkimei);
				}
			imeiPerLineRow.appendChild(imeiPerLineColumn);
				let totalColumn = cTag('div', {class: "columnSM5"});
					bTag = cTag('b');
					bTag.innerHTML = Translate('Total')+': ';
				totalColumn.appendChild(bTag);
					bTag = cTag('b', {id: "total_str"});
					bTag.innerHTML = currency+''+0.00;
				totalColumn.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "total", id: "total", 'value': 0});
				totalColumn.appendChild(inputField);
			imeiPerLineRow.appendChild(totalColumn);
		poRowform.appendChild(imeiPerLineRow);

			const errorRow = cTag('div', {class: "flexStartRow"});
				const errorColumn = cTag('div', {class: "columnSM8", 'align': "right"});
					errorSpan = cTag('span', {class: "error_msg", id: "error_bulkimei"});
				errorColumn.appendChild(errorSpan);
			errorRow.appendChild(errorColumn);
		poRowform.appendChild(errorRow);

			inputField = cTag('input', {'type': "hidden", name: "po_items_idvalue", id: "po_items_idvalue", 'value': po_items_id});
		poRowform.appendChild(inputField);
	formDialog.appendChild(poRowform);

	if(item_type==='cellphones'){
		popup_dialog(
			formDialog,
			{
				title:Translate('Update Purchase Order Cart'),
				width:600,
				buttons: {
					_Bulk_load_IMEI:{
						text:Translate('Bulk load IMEI numbers'),
						id:'btnBulkLoad',
						class: 'btn defaultButton', 'style': "margin-left: 10px;",
						click: showBulkField,
					},
					_Cancel: {
						text: Translate('Cancel'), 
						class: 'btn defaultButton', 'style': "margin-left: 10px;",
						click: function(hidePopup) {
							hidePopup();
						},
					},
					_Update:{
						text: Translate('Update'),
						id:'btnChangeRow',
						class: 'btn saveButton btnmodel', 'style': "margin-left: 10px;",
						click: checkUpdatePORow,
					},
					_Import_IMEI_numbers:{
						text: Translate('Import IMEI numbers'),
						id:'btnImportIMEI',
						class: 'btn saveButton btnmodel', 'style': "display:none;margin-left: 10px;",
						click: saveBulkData,
					}
				}
			}
		);
	}
	else{
		popup_dialog(
			formDialog,
			{
			title: Translate('Update Purchase Order Cart'),
			width: 600,
			buttons: {
				_Cancel: {
					text: Translate('Cancel'), 
					class: 'btn defaultButton', 'style': "margin-left: 10px;",
					click: function(hidePopup) {
						hidePopup();
					},
				},
				_Update:{
					text: Translate('Update'),
					id:'btnChangeRow',
					class: 'btn saveButton btnmodel', 'style': "margin-left: 10px;",
					click: checkUpdatePORow,
				}
			}
		});
	}

	setTimeout(function() {
		document.getElementById("cost").focus();		
		if(item_type==='cellphones'){
			 document.getElementById("received_qty").readOnly = 'readonly'; 
		}
		else{
			if(document.getElementById("received_qty").hasAttribute('readonly')){
				document.getElementById("received_qty").removeAttribute(readonly); 
			}
		}
		calculatePORowTotal();
		
		document.querySelectorAll(".calculatePORowTotal").forEach(oneFieldObj=>{
			oneFieldObj.addEventListener('keyup', calculatePORowTotal);
			oneFieldObj.addEventListener('change', calculatePORowTotal);
		});

		
		if(document.getElementById("bulkimei")){
			document.getElementById("bulkimei").addEventListener('keyup', function (eventObj){
				const ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#\n";
				bulkimei = eventObj.target.value.toUpperCase().replace(' ', '-');
				let IsNumber = true;
				let Char, i;
				let newIMEI = '';
				for (i = 0; i < bulkimei.length && IsNumber === true; i++){ 
					Char = bulkimei.charAt(i);
					if (ValidChars.indexOf(Char) === -1){}
					else{
						newIMEI = newIMEI+Char;
					}
				}
				
				if(bulkimei.length> newIMEI.length || eventObj.target.value !== newIMEI){
					eventObj.target.value = bulkimei = newIMEI;
				}				
			});
		}
	}, 500);
	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));	
	
	calculatePORowTotal();       
}

function showBulkField(){
	if(document.querySelector("#btnChangeRow")){
		if(document.querySelector("#btnChangeRow").style.display !== 'none'){
			document.querySelector("#btnChangeRow").style.display = 'none';
		}
	}
	
	if(document.querySelector("#btnBulkLoad")){
		if(document.querySelector("#btnBulkLoad").style.display !== 'none'){
			document.querySelector("#btnBulkLoad").style.display = 'none';
		}
	}
	
	if(document.querySelector("#btnImportIMEI")){
		if(document.querySelector("#btnImportIMEI").style.display === 'none'){
			document.querySelector("#btnImportIMEI").style.display = '';
		}
	}
	if(document.querySelector("#bulkimei")){
		if(document.querySelector("#bulkimei").style.display === 'none'){
			document.querySelector("#bulkimei").style.display = '';
			document.querySelector("#bulkimei").value = '';
		}
		document.getElementById("bulkimei").focus();
	}
}

async function saveBulkData(){
	let saveBtn;
	saveBtn = document.querySelector('#btnImportIMEI');
	saveBtn.innerHTML = Translate('Importing IMEI')+'...';
	saveBtn.disabled = true;

	const po_items_id = document.getElementById("po_items_idvalue").value;
	const ordered_qty = document.getElementById("ordered_qty"+po_items_id).value;
	const bulkimei = document.getElementById("bulkimei").value;

	const error_bulkimei = document.getElementById("error_bulkimei");
	
	function appendError(data){
		let errors = data.message;
		const TotalIMEI = document.getElementById("bulkimei").value.split('\n').filter(item=>item!=='').length;
		error_bulkimei.innerHTML = '';
		errors.split('|').forEach(item=>{
			const msg = cTag('p');
			if(item==='smallerIMEI'){
				msg.append(`${Translate('Total')} (${data.smallerIMEI}) ${Translate('IMEI numbers smaller than 2 characters found.')}`)
				error_bulkimei.appendChild(msg);
			} 
			else if(item==='largerIMEI'){
				msg.append(`${Translate('Total')} (${data.largerIMEI}) ${Translate('IMEI numbers longer than 20 characters found.')}`)
				error_bulkimei.appendChild(msg);
			}
			else if(item==='duplicateIMEI'){
				msg.append(`${Translate('Total')} (${data.duplicateIMEI}) ${Translate('duplicate IMEI numbers found')}`)
				error_bulkimei.appendChild(msg);
			}
			else if(item==='IMEIsavedError'){
				msg.append(`${Translate('Total')} (${data.savedIMEI}) ${Translate('IMEI numbers saved')} ${Translate('from')} ${TotalIMEI}`)
				error_bulkimei.appendChild(msg);
			}
			else if(item==='IMEIsaved'){
				msg.append(`${data.savedIMEI} ${Translate('IMEI numbers saved')}`)
				error_bulkimei.appendChild(msg);
			}
			else if(item==='noIMEIsaved'){
				msg.append(Translate('No IMEI Number saved'))
				error_bulkimei.appendChild(msg);
			}
			else if(item==='missingIMEI'){
				msg.append(Translate('Missing IMEI Number'))
				error_bulkimei.appendChild(msg);
			}
		})
	}

	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;
	jsonData['ordered_qty'] = ordered_qty;
	jsonData['bulkimei'] = bulkimei;
	
    const url = '/'+segment1+'/saveBulkData';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action ==='Add'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();

			saveBtn = document.querySelector('#btnImportIMEI');
			saveBtn.innerHTML = Translate('Import IMEI numbers');
			saveBtn.disabled = false;
			
			if(data.message !==''){
				appendError(data);
				setTimeout(function() {error_bulkimei.innerHTML = '';},5000);
			}
			else{error_bulkimei.innerHTML = '';}
			if(document.getElementById("bulkimei").style.display !== 'none'){
				document.getElementById("bulkimei").style.display = 'none';
			}			

			if(document.querySelector("#btnImportIMEI")){
				if(document.querySelector("#btnImportIMEI").style.display !== 'none'){
					document.querySelector("#btnImportIMEI").style.display = 'none';
				}
			}

			if(document.querySelector("#btnBulkLoad")){
				if(document.querySelector("#btnBulkLoad").style.display === 'none'){
					document.querySelector("#btnBulkLoad").style.display = '';
				}
			}
	
			if(document.querySelector("#btnChangeRow")){
				if(document.querySelector("#btnChangeRow").style.display === 'none'){
					document.querySelector("#btnChangeRow").style.display = '';
				}
			}

			if(document.getElementById("ordered_qty").value < data.received_qty){
				document.getElementById("ordered_qty").value = data.received_qty;
			}
			document.getElementById("received_qty").value = data.received_qty;
		}
		else{
			appendError(data);
			saveBtn = document.querySelector('#btnImportIMEI');
			saveBtn.innerHTML = Translate('Import IMEI numbers');
			saveBtn.disabled = false;
		}
    }
	return false;
}

function calculatePORowTotal(){
	let cost = parseFloat(document.getElementById("cost").value);
	if(cost==='' || isNaN(cost)){cost = 0;}
	document.getElementById("cost_str").innerHTML = addCurrency(cost);

	let ordered_qty = parseInt(document.getElementById("ordered_qty").value);
	if(ordered_qty==='' || isNaN(ordered_qty)){ordered_qty = 0;}

	let received_qty = parseInt(document.getElementById("received_qty").value);
	if(isNaN(received_qty) || received_qty===''){received_qty = 0;}
	if(received_qty>ordered_qty){
		document.getElementById("ordered_qty").value = ordered_qty = received_qty;
	}
	
	const ordered_qty_value = calculate('mul',cost,ordered_qty,2);
	document.getElementById("ordered_qty_value").value = ordered_qty_value;
	document.getElementById("ordered_qty_value_str").innerHTML = addCurrency(ordered_qty_value);

	const received_qty_value = calculate('mul',cost,received_qty,2);
	document.getElementById("received_qty_value").value = received_qty_value;
	document.getElementById("received_qty_value_str").innerHTML = addCurrency(received_qty_value);
	
	let total = ordered_qty_value;
	if(total==='' || isNaN(total)){total = 0;}
	document.getElementById("total").value = total;
	document.getElementById("total_str").innerHTML = addCurrency(total);
}
 
async function checkUpdatePORow(hidePopup){
	const po_items_id = document.getElementById("po_items_idvalue").value;

	let cost = document.getElementById("cost");
    if (!cost.valid()) return;

	let errorid;
	errorid = document.getElementById("errmsg_cost");
	errorid.innerHTML = '';	

	let ordered_qty = document.getElementById("ordered_qty");
    if (validateRequiredField(ordered_qty,'#errmsg_ordered_qty') && !ordered_qty.valid()) return;	

	let received_qty = document.getElementById("received_qty");
    if (validateRequiredField(received_qty,'#errmsg_received_qty') && !received_qty.valid()) return;
	
	const total = document.getElementById("total").value;						
	const saveBtn = document.querySelector('#btnChangeRow');
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;

	const jsonData = {};
	jsonData['po_items_id'] = po_items_id;
	jsonData['cost'] = parseFloat(cost.value);
	jsonData['ordered_qty'] = parseFloat(ordered_qty.value);
	jsonData['received_qty'] = parseFloat(received_qty.value);
	jsonData['total'] = total;
	
    const url = '/'+segment1+'/updatepo_item';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action ==='Update'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();
		}
		else{
			showTopMessage('success_msg', Translate('There is no changes made. Please try again.'));
		}
		hidePopup();
    }
	return false;
}

async function changeImeiOnPO(item_id, product_id, carrier_name){
	if(item_id>0){	
		const jsonData = {};
		jsonData['item_id'] = item_id;
		jsonData['product_id'] = product_id;
		jsonData['carrier_name'] = carrier_name;
		
		const url = '/'+segment1+'/showImeiOnPO';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			const po_id = document.getElementById("po_id").value;
			const formDialog = cTag('div');
				let tabs, tab1, errorSpan, requiredField, inputField;
				const changeImeiForm = cTag('form', {'action': "#", name: "frmchangeImeiOnPO", id: "frmchangeImeiOnPO", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
					errorSpan = cTag('span', {class: "errormsg", id: "showErrorMessage"});
				changeImeiForm.appendChild(errorSpan);

				if(data.customFields>0){
					tabs = cTag('div', {id: "tabs", 'style': "max-height: 600px; padding: 0;"});
						const ulTabs = cTag('ul');
							let liTabs1 = cTag('li');
								const aTag = cTag('a', {'href': "#tabs-1"});
								aTag.innerHTML = Translate('Basic Info');
							liTabs1.appendChild(aTag);
						ulTabs.appendChild(liTabs1);

							let liTabs2 = cTag('li');
								let customFieldLink =  cTag('a', {'href': "#tabs-2"});
								customFieldLink.innerHTML = Translate('Custom Fields');
							liTabs2.appendChild(customFieldLink)
						ulTabs.appendChild(liTabs2);
					tabs.appendChild(ulTabs);
					tab1 = cTag('div', {class: "columnSM12", id: "tabs-1"});
				}
				else{
					tabs = cTag('div', {class: "flexSpaBetRow"});
					tab1 = cTag('div', {class: "columnSM12"});
				}

						let itemNumberColumn = cTag('div', {class: "columnSM12"});
							const itemNumberRow = cTag('div', {class: "flexStartRow"});
								const itemNumberTitle = cTag('div', {class: "columnSM4", 'align': "left"});
									const itemNumberLabel = cTag('label', {'for': "item_number", 'data-toggle': "tooltip", 'data-placement': "right", title: Translate('Enter serial number here. This field validates for 15 digit IMEI and 11 ESN lengths. Locate serial number on most phones by entering *#06# on the dialpad or located under the battery.')});
									itemNumberLabel.innerHTML = Translate('IMEI Number');
										requiredField = cTag('span', {'style': "color: #F00;"});
										requiredField.innerHTML = '*';
									itemNumberLabel.appendChild(requiredField);
								itemNumberTitle.appendChild(itemNumberLabel);
							itemNumberRow.appendChild(itemNumberTitle);
								const itemNumberField = cTag('div', {class: "columnSM8", 'align': "left"});
									inputField = cTag('input', {'readonly': "readonly", 'type': 'text', 'required': "required", class: "form-control", name: "item_number", id: "item_number", 'value': data.item_number, 'maxlength': 20, 'placeholder': Translate('IMEI Number')});
								itemNumberField.appendChild(inputField);
									errorSpan = cTag('span', {class: "errormsg", id: "errmsg_item_number"});
								itemNumberField.appendChild(errorSpan);
							itemNumberRow.appendChild(itemNumberField);
						itemNumberColumn.appendChild(itemNumberRow);

							const carrierRow = cTag('div', {class: "flexStartRow"});
								const carrierTitle = cTag('div', {class: "columnSM4", 'align': "left"});
									const carrierLabel = cTag('label', {'for': "carrier_name", 'data-toggle': "tooltip", 'data-placement': "bottom", title: Translate('Select the Carrier of the phone you are entering. Select Unlocked or Unknown if original carrier is unknown or if device is unlocked.')});
									carrierLabel.innerHTML = Translate('Carrier');
								carrierTitle.appendChild(carrierLabel);
							carrierRow.appendChild(carrierTitle);
								const carrierDropDown = cTag('div', {class: "columnSM8", 'align': "left"});
									let selectCarrierName = cTag('select', {class: "form-control", id: "carrier_name", name: "carrier_name"});
									setOptions(selectCarrierName, data.carOpts, 0, 1);
								carrierDropDown.appendChild(selectCarrierName);
							carrierRow.appendChild(carrierDropDown);
						itemNumberColumn.appendChild(carrierRow);
					tab1.appendChild(itemNumberColumn);
				tabs.appendChild(tab1);

						if(data.customFields>0){
							const tab2 = cTag('div', {class: "columnXS12", id: "tabs-2"});
							generateCustomeFields(tab2,data.customFieldsData);
							tabs.appendChild(tab2);
						}
				changeImeiForm.appendChild(tabs);
					inputField = cTag('input', {'type': "hidden", name: "item_id", 'value': item_id});
				changeImeiForm.appendChild(inputField);
					inputField = cTag('input', {'type': "hidden", name: "po_id", 'value': po_id});
				changeImeiForm.appendChild(inputField);
					inputField = cTag('input', {'type': "hidden", name: "customFields", id: "customFields", 'value': data.customFields});
				changeImeiForm.appendChild(inputField);
			formDialog.appendChild(changeImeiForm);
			popup_dialog1000(Translate('Change IMEI Information'),formDialog,saveChangeImeiOnPO);

			setTimeout(function() {		
				document.querySelector("#carrier_name").value = data.carrier_name;
				if(data.customFields>0){						
					document.querySelector('#tabs').activateTab(0);
					document.getElementById("carrier_name").focus();					 
				
					if(document.getElementsByClassName("DateField").length>0){
						date_picker('.DateField');
					}
				}
				else{
					document.getElementById("carrier_name").focus();					 
				}
			}, 500);
		}
	}
	return true;
}

async function saveChangeImeiOnPO(hidePopup){
	const oElement = document.getElementById('showErrorMessage');
	oElement.innerHTML = '';

	/* if(document.getElementById('customFields') && document.getElementById('customFields').value>0){
		const requiredFields = document.getElementsByClassName("required");
		if(requiredFields.length>0){
			for(let l=0;l<requiredFields.length; l++){
				const oneFieldVal = requiredFields[l].value;
				if(oneFieldVal===''){
					document.querySelector('#tabs').activateTab(1);
					oElement.innerHTML = requiredFields[l].title+' '+Translate('is missing.');
					requiredFields[l].focus();
					return false;
				}
			}
		}
	} */

	let validCustomFields = validifyCustomField(1);
	if(!validCustomFields) return;

	actionBtnClick('.btnmodel', Translate('Saving'), 1);
	
    const url = '/'+segment1+'/saveChangeImeiOnPO';
	fetchData(afterFetch,url,document.getElementById('frmchangeImeiOnPO'),'formData');

    function afterFetch(data){
        if(data.action ==='Update'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();
		}
		else{
			showTopMessage('error_msg', Translate('Error occured while save changes this IMEI information.'));
		}
		hidePopup();
    }
	return false;
}

function completePO(){
	if(allowed.length>0 && allowed['6'].includes('cncpo')){
		noPermissionWarning(Translate('Mark Completed'));
		return;
	}
	const listcount = document.querySelector("#invoice_entry_holder").querySelector('.nodata');
	if(listcount){
		
		showTopMessage('alert_msg', Translate('Missing cart. Please choose/add new product'));
		document.getElementById("search_sku").focus();
		return(false);
	}
	confirmPOcompletion();
}

async function confirmPOcompletion(){
	const po_id = document.getElementById("po_id").value;

	const jsonData = {};
	jsonData['po_id'] = po_id;
	
    const url = '/'+segment1+'/update_po_complete';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr>0){window.location = '/Purchase_orders/lists/closed';}
		else{
			showTopMessage('alert_msg', Translate('Could not complete this order.'));
			
		}
    }
}

async function poReOpen(){
	const po_id = document.getElementById("po_id").value;
	
	const jsonData = {};
	jsonData['po_id'] = po_id;

    const url = '/'+segment1+'/updatepoReOpen';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr>0){window.location = '/Purchase_orders/edit/'+data.returnStr;}
		else{
			showTopMessage('alert_msg', Translate('Could not re-open this order.'));
		}
    }
}

function cancelPO(){
	const listcount = document.querySelector("#invoice_entry_holder").querySelector('.nodata');
	let po_itemscount = 0;
	if(!listcount){
		const poItemsIds = document.getElementsByName("po_items_id[]");
		if(poItemsIds.length>0){
			for(let l=0; l<poItemsIds.length; l++){
				let po_items_id = poItemsIds[l].value;
				po_itemscount = calculate('add',po_itemscount,parseFloat(document.getElementById("received_qty"+po_items_id).value),2);
			}
		}
	}
	
	if(po_itemscount>0){
		let message = po_itemscount+' '+Translate('Item(s) has been received for this you can not cancel a PO.');
		alert_dialog(Translate('Cancel Purchase Order'), message, Translate('Close'))
	}
	else{
		let message = cTag('span',{id:'cancellmessage'});
		message.innerHTML = Translate('Are you sure you want to cancel this Purchase Order?');
		confirm_dialog(Translate('Cancel Purchase Order'), message, confirmPOcancelation);	
	}
}

async function confirmPOcancelation(hidePopup){
	const po_id = document.getElementById("po_id").value;
	
	const jsonData = {};
	jsonData['po_id'] = po_id;
	jsonData['status'] = 'Cancel';

    const url = '/'+segment1+'/poCancel';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr>0){
			window.location = '/Purchase_orders/lists';
			hidePopup();
		}
		else{
			document.getElementById("cancellmessage").innerHTML = Translate('Could not cancel this PO.');
		}
    }
}

function edit(){
	const dashboard = document.getElementById("viewPageInfo");
    dashboard.innerHTML = '';
		let tableHeadRow, tdCol, thCol;
		const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const titleColumn = cTag('div',{ 'class':`columnXS10 columnSM6 columnMD5`, 'style': "margin: 0;" });
				const titleHeader = cTag('h2',{ 'style': "padding-top: 5px; text-align: start;" });
				titleHeader.innerHTML = `${Translate('Purchase Order')} p${segment3}`;
			titleColumn.appendChild(titleHeader);
		titleRow.appendChild(titleColumn);
			const statusColumn = cTag('div',{ 'class':`columnXS2 columnSM1` });
				let statusLink = cTag('a',{ 'class':`btn completeButton`, 'style': "margin-left: 10px;", 'href':`javascript:void(0);` });
				statusLink.appendChild(cTag('label',{'id':'status_label', 'style': "margin-bottom: 0px;"}));
			statusColumn.appendChild(statusLink);
		titleRow.appendChild(statusColumn);
			const buttonNames = cTag('div',{ 'class':`columnSM5 columnMD6`, 'style': "text-align: end;" });
				const aTag = cTag('a', {'href': "/Purchase_orders/lists", class: "btn defaultButton", 'style': "margin-right: 15px; padding-top: 5px; padding-bottom: 5px;", title: Translate('List Purchase')});
				aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('List Purchase'));
			buttonNames.appendChild(aTag);

				let buttonDropDown = cTag('div',{ 'class':`printBtnDropDown`, id: 'Purchasedropdown' });
					let printButton = cTag('button',{ 'type':`button`,'class':`btn printButton dropdown-toggle`, 'data-toggle':`dropdown`,'aria-haspopup':`true`,'aria-expanded':`false` });
					printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
					if(OS =='unknown'){
						printButton.append(' '+Translate('Print')+' ');
					}
					printButton.append('\u2000', cTag('span',{ 'class':`caret`}));
						let toggleSpan = cTag('span',{ 'class':`sr-only` });
						toggleSpan.innerHTML = Translate('Toggle Dropdown');
					printButton.appendChild(toggleSpan);
				buttonDropDown.appendChild(printButton);
					let printUl = cTag('ul',{ 'class':`dropdown-menu`});
						let fullPagePrintLi = cTag('li');
							let fullPagePrint = cTag('a',{ 'id':'full_page_printer','href':`javascript:void(0);`,'title':Translate('Full Page Printer') });
							fullPagePrint.innerHTML = Translate('Full Page Printer');
						fullPagePrintLi.appendChild(fullPagePrint);
					printUl.appendChild(fullPagePrintLi);
					printUl.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						let emailLi = cTag('li');
							let emailPo = cTag('a',{ 'href':`javascript:void(0)`,'click':emailthispage,'title':Translate('Email PO') });
							emailPo.innerHTML = Translate('Email PO');
						emailLi.appendChild(emailPo);
					printUl.appendChild(emailLi);
						let liSMS = cTag('li');
							let smsPrint = cTag('a',{ 'href':`javascript:void(0)`,'click': smsPO,'title':Translate('SMS PO') });
							smsPrint.innerHTML = Translate('SMS PO');
						liSMS.appendChild(smsPrint);
					printUl.appendChild(liSMS);
					printUl.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						let barcodeLi = cTag('li');
							let barcodeLabel = cTag('a',{ 'id':'print_barcode','href':`javascript:void(0)`,'title':Translate('Print Barcode Label') });
							barcodeLabel.append(cTag('i',{ 'class':`fa fa-print` }), ' ', Translate('Barcode Labels'));
							if(OS =='unknown'){
								barcodeLabel.innerHTML = Translate('Barcode Label Print');
							}
						barcodeLi.appendChild(barcodeLabel);
					printUl.appendChild(barcodeLi);
				buttonDropDown.appendChild(printUl);
			buttonNames.appendChild(buttonDropDown);

				const emailRow = cTag('div',{ 'class': "flexEndRow", 'style': "width: 100%;" });
					const sendEmailForm = cTag('form',{ 'method':`post`,'name':`frmSendPOEmail`,'id':`frmSendPOEmail`, 'style': "margin-top: 6px;", 'enctype':`multipart/form-data`,'action':`#`,'submit':AJsend_POEmail });
						let emailTable = cTag('table',{ 'align':`center`,'width':`100%`,'border':`0`,'cellspacing':`0`,'cellpadding':`10` });
							const emailBody = cTag('tbody');
								tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'colspan':`2` });
									tdCol.appendChild(cTag('div',{ 'id':`showerrormessage` }));
									tdCol.appendChild(cTag('div',{ 'id':`showsuccessmessage` }));
								tableHeadRow.appendChild(tdCol);
							emailBody.appendChild(tableHeadRow);
								tableHeadRow = cTag('tr',{ 'class':`emailform`,style:'display:none'});
									tdCol = cTag('td');
									tdCol.appendChild(cTag('input',{ 'type':`email`,'required':``,'name':`email_address`,'id':`email_address`,'class':`form-control`,'maxlength':`50` }));
								tableHeadRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'width':`155`,'align':`right`,'valign':`middle` });
									tdCol.appendChild(cTag('input',{ 'type':`submit`,'class':`btn completeButton`,'value':` ${Translate('Email')} ` }));
									tdCol.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`, 'style': "margin-left: 6px;", 'click':cancelemailform,'value':` ${Translate('Cancel')} ` }));
								tableHeadRow.appendChild(tdCol);
							emailBody.appendChild(tableHeadRow);
						emailTable.appendChild(emailBody);
					sendEmailForm.appendChild(emailTable);
				emailRow.appendChild(sendEmailForm);
			buttonNames.appendChild(emailRow);
		titleRow.appendChild(buttonNames);
	dashboard.appendChild(titleRow);

		const supplierRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const supplierColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
				const supplierWidget = cTag('div',{ 'class':`cardContainer`, 'style': "margin-bottom: 10px;" });
					const supplierHeader = cTag('div',{ 'class': `flexSpaBetRow cardHeader` });
						const supplierInfoHeader = cTag('h3');
						supplierInfoHeader.append(cTag('i', {class: "fa fa-user"}), ' ', Translate('Supplier Info'));
					supplierHeader.appendChild(supplierInfoHeader);
						let editDiv = cTag('div',{ 'id':'edit_supplier','class':`invoiceorcompleted`, 'style': "padding-right: 2px;" });
					supplierHeader.appendChild(editDiv);
				supplierWidget.appendChild(supplierHeader);

					let supplierContent = cTag('div',{ 'class':`cardContent customInfoGrid`, 'id':`supplier_information` });
						let companyLabel = cTag('label');
						companyLabel.innerHTML = Translate('Company')+' : ';
						let companyLink = cTag('a', {'id':'company_link', 'style': "color: #009; text-decoration: underline; border-bottom: 1px solid #CCC; margin-bottom: 5px; padding-bottom: 5px;",'title':Translate('View Supplier Details')});
					supplierContent.append(companyLabel, companyLink);

						let supplierLabel = cTag('label');
						supplierLabel.innerHTML = Translate('Supplier')+' : ';
						let supplierName = cTag('span',{'id':'suppliername'});
					supplierContent.append(supplierLabel, supplierName);

						let emailLabel = cTag('label');
						emailLabel.innerHTML = Translate('Email')+' : ';
						let emailValue = cTag('span',{ 'id':'supplieremail' });
					supplierContent.append(emailLabel, emailValue);
				supplierWidget.appendChild(supplierContent);
			supplierColumn.appendChild(supplierWidget);
		supplierRow.appendChild(supplierColumn);
			const purchseOrderColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
				const purchseOrderWidget = cTag('div',{ 'class':`cardContainer`, 'style': "margin-bottom: 10px;" });
					const purchseOrderHeader = cTag('div',{ 'class':`cardHeader flexSpaBetRow` });
						const purchseOrderTitle = cTag('h3');
						purchseOrderTitle.append(cTag('i', {class: "fa fa-mobile"}), ' ', Translate('Purchase Order Info'));
					purchseOrderHeader.appendChild(purchseOrderTitle);
					
						let buttonDiv = cTag('div',{ 'class':`invoiceorcompleted`, 'style': "padding-right: 2px;" });
							let editButton = cTag('button',{ 'href':`javascript:void(0);`,'click':changePOInfo,'class':`btn defaultButton` });
							editButton.innerHTML = Translate('Edit');
						buttonDiv.appendChild(editButton);
					purchseOrderHeader.appendChild(buttonDiv);
				purchseOrderWidget.appendChild(purchseOrderHeader);
					const purchseOrderContent = cTag('div',{ 'class':`cardContent` });
						let orderInfoDiv = cTag('div',{ 'class':`cardOrder customInfoGrid`,'id':`order_info` });
							let expectedDateLabel = cTag('label');
							expectedDateLabel.innerHTML = Translate('Date Expected')+' : ';
							let expectedDateValue = cTag('span',{'id':'date_expectedStr'});
						orderInfoDiv.append(expectedDateLabel, expectedDateValue);

							let shippingCostLabel = cTag('label');
							shippingCostLabel.innerHTML = Translate('Shipping Cost')+' : ';
							let shippingValue = cTag('span', {'id':`shippingstr` });
						orderInfoDiv.append(shippingCostLabel, shippingValue);

							let refLbel = cTag('label');
							refLbel.innerHTML = Translate('Lot Ref. No.')+' : ';
							let refValue = cTag('span',{ 'id':`lot_ref_nostr` });
						orderInfoDiv.append(refLbel, refValue);
					purchseOrderContent.appendChild(orderInfoDiv);
				purchseOrderWidget.appendChild(purchseOrderContent);
			purchseOrderColumn.appendChild(purchseOrderWidget);
		supplierRow.appendChild(purchseOrderColumn);
	dashboard.appendChild(supplierRow);

		const editColumn = cTag('div',{ 'class':`columnXS12`, 'style': "position: relative;" });
			const editIbox = cTag('div',{ 'class':`cartContent`});
				const editTableColumn = cTag('div',{ 'class':`columnXS12`, 'style': "padding: 0;" });
					const editTable = cTag('table',{ 'class':`table table-bordered`});
						const editHead = cTag('thead');
							tableHeadRow = cTag('tr',{'id':'invoice_entry_holder_headingRow'});
								thCol = cTag('th',{ 'width':`3%`,'class':`text-right` });
								thCol.innerHTML = '#';
							tableHeadRow.appendChild(thCol);
								thCol = cTag('th');
								thCol.innerHTML = Translate('Description');
							tableHeadRow.appendChild(thCol);
								thCol = cTag('th',{ 'width':`10%`,'class':`text-right` });
								if(OS =='unknown'){thCol.innerHTML = Translate('Need/Have/OnPO');}
								else{thCol.innerHTML = Translate('Need-Have-onPO');}
							tableHeadRow.appendChild(thCol);
								thCol = cTag('th',{ 'width':`10%`,'class':`text-right` });
								thCol.innerHTML = Translate('Ordered Qty');
							tableHeadRow.appendChild(thCol);
								thCol = cTag('th',{ 'width':`10%`,'class':`text-right` });
								thCol.innerHTML = Translate('Received Qty');
							tableHeadRow.appendChild(thCol);
								thCol = cTag('th',{ 'width':`10%`,'class':`text-right` });
								thCol.innerHTML = Translate('Unit Cost');
							tableHeadRow.appendChild(thCol);
								thCol = cTag('th',{ 'width':`10%`,'class':`text-right` });
								thCol.innerHTML = Translate('Total');
							tableHeadRow.appendChild(thCol);
						editHead.appendChild(tableHeadRow);
					editTable.appendChild(editHead);
					editTable.appendChild(cTag('tbody',{ 'id':`invoice_entry_holder` }));
					editTable.appendChild(cTag('tbody',{ 'id':`invoice_entry_holder_bottomSection` }));							
					editTable.appendChild(cTag('thead',{'id':'total_section'}));
				editTableColumn.appendChild(editTable);
			editIbox.appendChild(editTableColumn);
		editColumn.appendChild(editIbox);
	dashboard.appendChild(editColumn);

		const historyRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const historyColumn = cTag('div',{ 'class':`columnXS12` });
				let hiddenProperties = {
                    'note_forTable': 'po' ,
                    'table_idValue': '' ,
                    'spo_id': '' ,
					'publicsShow':1
            }
        	historyColumn.appendChild(historyTable(Translate('Purchase Order History'),hiddenProperties));
		historyRow.appendChild(historyColumn);
	dashboard.appendChild(historyRow);

	//=======sessionStorage =========//
	let list_filters;

	if (sessionStorage.getItem("list_filters") !== null) {
		list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
	}
	else{
		list_filters = {};
	}

    let shistory_type = '';
    if(list_filters.hasOwnProperty("shistory_type")){
        shistory_type = list_filters.shistory_type;
        if(document.querySelector('#shistory_type')){
            const select = document.querySelector('#shistory_type');
                const option = cTag('option', {'value': shistory_type});
            select.appendChild(option);
            select.value = shistory_type;
        }
    }

	addCustomeEventListener('filter',filter_Purchase_orders_edit);
	addCustomeEventListener('loadTable',loadTableRows_Purchase_orders_edit);
    addCustomeEventListener('changeCart',addProductToPOCart)
	AJ_edit_MoreInfo();
}

async function AJ_edit_MoreInfo(){
	const jsonData = {po_number:segment3};
    const url = '/'+segment1+'/AJ_edit_MoreInfo';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        
		document.querySelector('#status_label').innerHTML = data.status;
		document.querySelector('#full_page_printer').addEventListener('click',()=>printbyurl(`/Purchase_orders/prints/large/${data.po_number}`));
		document.querySelector('#print_barcode').addEventListener('click',()=>printbyurl(`/Purchase_orders/prints/barcode/${data.po_number}`));
		document.querySelector('#email_address').value = data.supplieremail;
		
		document.querySelector('#supplier_information').append(cTag('input',{'type':'hidden','id':'supPermission','value':data.supPermission}));
		const company_link = document.querySelector('#company_link');
		company_link.setAttribute('href',`/Manage_Data/sview/${data.suppliers_id}`);
		company_link.innerHTML = data.company+' ';
		company_link.appendChild(cTag('i',{ 'class':`fa fa-link` }));
		document.querySelector('#suppliername').append(data.suppliername);
		document.querySelector('#supplieremail').append(data.supplieremail);
		document.querySelector('#date_expectedStr').innerHTML = DBDateToViewDate(data.date_expected, 0, 1);
		if(data.return_po===1){
			let returnHTML = cTag('span', {style:"background:#610f0f; color:#FFFFFF; padding:2px 8px;margin-left:15px"});
			returnHTML.innerHTML = 'Returned';
			document.querySelector('#date_expectedStr').appendChild(returnHTML);
		}
		document.querySelector('#shippingstr').innerHTML = addCurrency(data.shipping);
		document.querySelector('#lot_ref_nostr').innerHTML = data.lot_ref_no;
		if(data.status === "Open"){				
				let thCol = cTag('th',{ 'width':`7%`, 'style': "text-align: center;" });
				thCol.appendChild(cTag('i',{ 'class':`fa fa-trash-o` }));
			document.querySelector('#invoice_entry_holder_headingRow').appendChild(thCol);
		}
		if(data.status==="Open"){
			if(data.supPermission===1){
				const edit = cTag('i',{ 'style':'cursor:pointer','class':`fa fa-edit` });
				edit.addEventListener('click',()=>dynamicImport('./Manage_Data.js','addnewsupplierform',['editpo', data.suppliers_id]))
				document.querySelector('#suppliername').append('  ',edit);
			} 
			const changeDiv = cTag('div',{ 'style': "margin-top: 4px; display: flex;" });
				let  changeInput = cTag('input',{'type':`text`,'id':`supplier`,'class':`form-control ui-autocomplete-input`,style:'display:none'});
			changeDiv.appendChild(changeInput);
				const btn = cTag('button',{ 'class':`btn defaultButton` });
				btn.addEventListener('click',()=>{
					let supplier = document.getElementById('supplier');
					if(supplier.style.display==='none') supplier.style.display = '';
					else supplier.style.display = 'none';
				});
				btn.append(' '+Translate('Change'));
			changeDiv.appendChild(btn);
			document.querySelector('#edit_supplier').appendChild(changeDiv);
		}

		let subTotalStr, tableHeadRow, tdCol, bTag;
		if(data.status === "Open"){
				tableHeadRow = cTag('tr');
					let thCol = cTag('th',{ 'style': "text-align: right;", 'id':`barcodeserno` });
					thCol.innerHTML = 1;
				tableHeadRow.appendChild(thCol);
					tdCol = cTag('td',{ 'colspan':Math.floor(data.cols) });
						const searchDiv = cTag('div',{ 'class':`flexStartRow` });
							let newProductDiv = cTag('div',{ 'class':`input-group columnXS7 columnSM4 columnMD4` });
							newProductDiv.appendChild(cTag('input',{ 'maxlength':`50`,'type':`text`,'id':`search_sku`,'name':`search_sku`,'class':`form-control search_sku ui-autocomplete-input`, 'style': "min-width: 120px;", autocomplete:'off', 'placeholder':Translate('Search by product name or SKU') }));
								let newSpan = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Product'),'class':`input-group-addon cursor` });
								if(data.pPermission === 1) newSpan.addEventListener('click',()=>dynamicImport('./Products.js','AJget_ProductsPopup',['Purchase_orders',0,0,addToPOCart]));
								else newSpan.addEventListener('click',()=>noPermissionWarning('Product'));
								newSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
							newProductDiv.appendChild(newSpan);
						searchDiv.appendChild(newProductDiv);
							let productPickerDiv = cTag('div',{'class':'columnXS5 columnSM8 columnMD8', 'style': "text-align: start;"});
								let productPickerButton = cTag('button',{ 'type':`button`,'name':`showcategorylist`,'id':`product-picker-button`,'click':showProductPicker,'class':`btn productPickerButton` });
								productPickerButton.innerHTML = Translate('Open Product Picker');
							productPickerDiv.appendChild(productPickerButton);
						searchDiv.appendChild(productPickerDiv);
						searchDiv.appendChild(cTag('span',{ 'class':`error_msg`,'style':'margin-left:6px','id':`error_search_sku` }));
					tdCol.appendChild(searchDiv);
				tableHeadRow.appendChild(tdCol);
			document.querySelector('#invoice_entry_holder_bottomSection').appendChild(tableHeadRow);
		}

		const total_section = document.querySelector('#total_section');
			tableHeadRow = cTag('tr');
				tdCol = cTag('td',{ 'style':`padding: 0`,'colspan':`8` });
				if(data.status ==="Open"){
						let openDiv = cTag('div',{ 'class':`columnSM12` });
						openDiv.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_productlist` }));
						openDiv.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`pagi_index`,'id':`pagi_index`,'value':`0` }));
						openDiv.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppcategory_id`,'id':`ppcategory_id`,'value':`0` }));
						openDiv.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`totalrowscount`,'id':`totalrowscount`,'value':`0` }));
					tdCol.appendChild(openDiv);
						let filterRowDiv = cTag('div',{ 'class':`flexSpaBetRow`,'id':`filterrow`,'style':'display:none;padding:10px 60px 0 50px;gap:5px'});
							let filterName = cTag('div',{ style:'display:none', 'id':`filter_name_html`});
								let filterInput = cTag('div',{ 'class':`input-group` });
									const filter_name = cTag('input',{ 'maxlength':`50`,'type':`text`,'placeholder':Translate('Search by search'),'value':``,'class':`form-control product-filter`,'name':`filter_name`,'id':`filter_name` });
									filter_name.addEventListener('keyup', e=>{if(e.which===13) showCategoryPPProduct()});
								filterInput.appendChild(filter_name);
									let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'click':showCategoryPPProduct,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search by Product') });
									searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
								filterInput.appendChild(searchSpan);
							filterName.appendChild(filterInput);
						filterRowDiv.appendChild(filterName);
							let filterWidth = cTag('div');
							filterWidth.appendChild(cTag('label',{ 'id':`PPfromtodata` }));
						filterRowDiv.appendChild(filterWidth);
							let categoryDiv = cTag('div',{ style:'display:none',  'id':`all-category-button`});
								let categoryInput = cTag('div',{ 'class':`input-group` });
									let productPickerLink = cTag('a',{ 'href':`javascript:void(0);`,'title':"All Category List",'click':reloadProdPkrCategory });
										let categorySpan = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "background: #a71d4c; color: #FFF; border-color: #a71d4c;" });
											let categoryLabel = cTag('label');
											categoryLabel.innerHTML = Translate('All Category List');
										categorySpan.appendChild(categoryLabel);
									productPickerLink.appendChild(categorySpan);
								categoryInput.appendChild(productPickerLink);
							categoryDiv.appendChild(categoryInput);
						filterRowDiv.appendChild(categoryDiv);
					tdCol.appendChild(filterRowDiv);
						let productPickerWidth = cTag('div',{ 'style': "position: relative;" });
							let productListDiv = cTag('div',{ 'class':`columnSM12`,'id':`product-picker`,'style':'display:none; align-items:center; min-height: 90px;'});
							productListDiv.appendChild(cTag('div',{ 'id':`allcategorylist`,'style':'display:none;padding:0 50px 0 40px;width:100%' }));
							productListDiv.appendChild(cTag('div',{ style:'display:none','id':`allproductlist`,'style':'padding:0 50px 0 40px;width:100%' }));
						productPickerWidth.appendChild(productListDiv);
							let leftArrowDiv = cTag('div',{ 'class':`prevlist`,style:'display:none'});
								let leftArrowButton = cTag('button',{ 'click':preNextCategory, 'style':'background:initial' });
								leftArrowButton.innerHTML = '‹';
							leftArrowDiv.appendChild(leftArrowButton);
						productPickerWidth.appendChild(leftArrowDiv);
							let rightArrowDiv = cTag('div',{ 'class':`nextlist`,style:'display:none'});
								let rightArrowButton = cTag('button',{ 'click':preNextCategory, 'style':'background:initial' });
								rightArrowButton.innerHTML = '›';
							rightArrowDiv.appendChild(rightArrowButton);
						productPickerWidth.appendChild(rightArrowDiv);
					tdCol.appendChild(productPickerWidth);
				}
			tableHeadRow.appendChild(tdCol);
		total_section.appendChild(tableHeadRow);
			tableHeadRow = cTag('tr');
				tdCol = cTag('td',{ 'colspan':`3`, 'style': "text-align: right;" });
				tdCol.innerHTML = ' ';
			tableHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'style': "text-align: right;" });
					let orderQtyLabel = cTag('label',{ 'id':`orderQtyTotal` });
					orderQtyLabel.innerHTML = 0;
				tdCol.appendChild(orderQtyLabel);
			tableHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'style': "text-align: right;" });
					let receivedQtyLabel = cTag('label',{ 'id':`receivedQtyTotal` });
					receivedQtyLabel.innerHTML = 0;
				tdCol.appendChild(receivedQtyLabel);
			tableHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'style': "text-align: right;" });
					let totalLabel = cTag('label',{'id':'subTotalStr'});	
					subTotalStr = Translate('Total');
					if(data.taxes !==0 || data.shipping !==0){
						subTotalStr = Translate('Subtotal');
					}
					totalLabel.innerHTML = subTotalStr+' :';					
				tdCol.appendChild(totalLabel);
			tableHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'style': "text-align: right;" });
					bTag = cTag('b',{ 'id':`grand_totalstr` });
					bTag.innerHTML = currency+'0.00';
				tdCol.appendChild(bTag);
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes`,'id':`taxes`,'value':`${data.taxes}` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_is_percent`,'id':`tax_is_percent`,'value':`${data.tax_is_percent}` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`shipping`,'id':`shipping`,'value':`${data.shipping}` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':`0` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`total_item`,'id':`total_item`,'value':`0` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`status`,'id':`status`,'value':data.status }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`supplier_id`,'id':`supplier_id`,'value':data.suppliers_id }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_id`,'id':`po_id`,'value':data.po_id }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value':segment1 }));
			tableHeadRow.appendChild(tdCol);
			if(data.status === 'Open'){
					tdCol = cTag('td');
					tdCol.innerHTML = ' ';
				tableHeadRow.appendChild(tdCol);
			}
		total_section.appendChild(tableHeadRow);
		
			tableHeadRow = cTag('tr',{'id':'taxesRow'});
			if(data.taxes==0) tableHeadRow.style.display = 'none';
				tdCol = cTag('td',{ 'colspan':Math.floor(data.cols-1), 'style': "text-align: right;" });
					let taxesLabel = cTag('label',{'id':'taxLabel'});
					if(data.tax_is_percent) taxesLabel.innerHTML = `${Translate('Taxes')} (${data.taxes}%) :`;
					else taxesLabel.innerHTML = Translate('Taxes')+' :';
				tdCol.appendChild(taxesLabel);
			tableHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'style': "text-align: right;" });
					bTag = cTag('b',{ 'id':`taxesTotalstr` });
				tdCol.appendChild(bTag);
			tableHeadRow.appendChild(tdCol);
			if(data.status === "Open"){
					tdCol = cTag('td');
					tdCol.innerHTML = ' ';
				tableHeadRow.appendChild(tdCol);
			}
			total_section.appendChild(tableHeadRow);
		
			tableHeadRow = cTag('tr',{'id':'shippingRow'});
			if(data.shipping==0) tableHeadRow.style.display = 'none';
				tdCol = cTag('td',{ 'colspan':Math.floor(data.cols-1), 'style': "text-align: right;" });
					let shippingLabel = cTag('label');
					shippingLabel.innerHTML = Translate('Shipping Cost')+' :';
				tdCol.appendChild(shippingLabel);
			tableHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'style': "text-align: right;" });
					bTag = cTag('b',{ 'id':`shippingTotalstr` });
				tdCol.appendChild(bTag);
			tableHeadRow.appendChild(tdCol);
			if(data.status === "Open"){
					tdCol = cTag('td');
					tdCol.innerHTML = ' ';
				tableHeadRow.appendChild(tdCol);
			}
			total_section.appendChild(tableHeadRow);
			tableHeadRow = cTag('tr',{'id':'grandTotalRow'});
			if(data.taxes==0 && data.shipping==0) tableHeadRow.style.display = 'none';
				tdCol = cTag('td',{ 'colspan':Math.floor(data.cols-1), 'style': "text-align: right;" });
					let grandTotalLabel = cTag('label');
					grandTotalLabel.innerHTML = Translate('Grand Total')+' :';
				tdCol.appendChild(grandTotalLabel);
			tableHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'style': "text-align: right;" });
					bTag = cTag('b',{ 'id':`grandTotalstr` });
					bTag.innerHTML = currency+'0.00';
				tdCol.appendChild(bTag);
			tableHeadRow.appendChild(tdCol);
			if(data.status === "Open"){
					tdCol = cTag('td');
					tdCol.innerHTML = ' ';
				tableHeadRow.appendChild(tdCol);
			}
			total_section.appendChild(tableHeadRow);					

		if(data.status === "Open"){
			let statusBody = cTag('tbody');
				tableHeadRow = cTag('tr');
					tdCol = cTag('td',{ 'colspan':data.cols });
						const buttonsRow = cTag('div',{ 'class': "flexEndRow", 'style': "align-items: center;" });
							let buttonInGroup = cTag('div',{ 'class':`input-group` });
								let submitDiv = cTag('div',{ 'name':`possubmit`,'id':`possubmit`,'class':`bgnone cursor`,style:'display:none' });
									let completeButton = cTag('button',{ 'class':`btn completeButton`, 'type':`button`,'click':completePO });
										let completeSpan = cTag('span');
										completeSpan.innerHTML = Translate('Mark Completed');
									completeButton.appendChild(completeSpan);
								submitDiv.appendChild(completeButton);
							buttonInGroup.appendChild(submitDiv);
								let submitButtonDiv = cTag('div',{ 'name':`possubmitdis`,'id':`possubmitdis`,'class':`bgnone` });
									let markCompleteButton = cTag('button',{ 'class':`btn defaultButton`, 'style': "cursor: not-allowed;", 'type':`button` });
										let markCompleteSpan = cTag('span');
										markCompleteSpan.innerHTML = Translate('Mark Completed');
									markCompleteButton.appendChild(markCompleteSpan);
								submitButtonDiv.appendChild(markCompleteButton);
							buttonInGroup.appendChild(submitButtonDiv);

							let cancelDiv = cTag('div',{ 'id':`po_cancelled`, 'style': " margin-right: 15px;" });
								let cancelInGroup = cTag('div',{ 'class':`input-group` });
									let cancelLink = cTag('button',{ 'class':`btnFocus iconButton cursor`,'click':cancelPO });
										let removeSpan = cTag('i',{ 'class':`fa fa-remove`, 'style': "font-size: 1.5em;" });
										let cancelLabel = cTag('span');
										cancelLabel.innerHTML = ` ${Translate('Cancel')} `;
									cancelLink.append(removeSpan, cancelLabel);
								cancelInGroup.appendChild(cancelLink);
							cancelDiv.appendChild(cancelInGroup);

							let receiveProductDiv = cTag('div',{ 'style': "display:none; margin-right: 15px;", 'id':`ReceiveAllProducts` });
								let receiveProductButton = cTag('button',{ 'class':`btn bgblack`, 'style': "color: #fff;", 'type':`button`,'click':ReceiveAllProducts });
									let receiveSpan = cTag('span',{ 'style': "font-weight: bold;" });
									if(OS !=='unknown') receiveSpan.innerHTML = 'Receive all';
									else receiveSpan.innerHTML = Translate('Receive all products');
								receiveProductButton.appendChild(receiveSpan);
							receiveProductDiv.appendChild(receiveProductButton);
						buttonsRow.append(receiveProductDiv, cancelDiv, buttonInGroup);
					tdCol.appendChild(buttonsRow);
				tableHeadRow.appendChild(tdCol);
				if(data.status === "Open"){
						tdCol = cTag('td');
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				}
			statusBody.appendChild(tableHeadRow);
			total_section.parentNode.appendChild(statusBody);
		}
		else if(data.return_po===0){
			let returnBody = cTag('tbody');
				tableHeadRow = cTag('tr');
					tdCol = cTag('td',{ 'colspan':data.cols });
						let reOpenDiv = cTag('div',{ 'class': "flexEndRow" });
							let reOpenInGroup = cTag('div',{ 'class':`input-group` });
								let reOpenSubmit = cTag('div',{ 'name':`possubmit`,'id':`possubmit`,'class':`bgnone cursor` });
									let reOpenButton = cTag('button',{ 'class':`btn createButton`,'type':`button`,'click':poReOpen });
									reOpenButton.innerHTML = Translate('Re-Open PO');
								reOpenSubmit.appendChild(reOpenButton);
							reOpenInGroup.appendChild(reOpenSubmit);
						reOpenDiv.appendChild(reOpenInGroup);
					tdCol.appendChild(reOpenDiv);
				tableHeadRow.appendChild(tdCol);
				if(data.status === "Open"){
						tdCol = cTag('td');
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				}
			returnBody.appendChild(tableHeadRow);
			total_section.parentNode.appendChild(returnBody);
		}

		document.querySelector('#spo_id').value = data.po_id;
		document.querySelector('#table_idValue').value = data.po_id;

		loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);

		if(document.querySelector("#supplier")){AJautoComplete('supplier',saveChangePOSupplier);}	
		multiSelectAction('Purchasedropdown');
		if(document.querySelector("#frmSendPOEmail") && document.querySelector(".emailform")){
			AJautoComplete_cartPOProduct();
		}
    }
}

function add(){
	const dashboard = document.getElementById("viewPageInfo");
    dashboard.innerHTML = '';
		const titleRow = cTag('div');
			const headerTitle = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
			headerTitle.append(Translate('Create Purchase Order')+' ');
			headerTitle.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('This page captures the basic details required to create a new purchase order') }));
		titleRow.appendChild(headerTitle);
	dashboard.appendChild(titleRow);

		const addColumn = cTag('div',{ 'class':`columnSM12`, 'style': "margin: 0;" });
			let padding0 = 'padding:15px 0';
			if(OS =='unknown'){padding0 = '';}
			let callOutDiv = cTag('div',{ 'class':`innerContainer`, style: `background: #fff;${padding0}`});
				const addForm = cTag('form',{ 'action':`#`,'name':`frmpo`,'id':`frmpo`,'submit':savePO,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
					
					const supplierNameRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const supplierTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const supplierLabel = cTag('label',{ 'for':`supplier_name`,'data-placement':`bottom` });
							supplierLabel.append(Translate('Supplier Name'));
								let requiredField = cTag('span',{ 'class':`required` });
								requiredField.innerHTML = '*';
							supplierLabel.appendChild(requiredField);
						supplierTitle.appendChild(supplierLabel);
					supplierNameRow.appendChild(supplierTitle);
						const supplierNameField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
							const supplierInGroup = cTag('div',{ 'class':`input-group` });
								let selectSupplier = cTag('select',{ 'required':``,'name':`supplier_id`,'id':`supplier_id`,'class':`form-control` });										
							supplierInGroup.appendChild(selectSupplier);
							supplierInGroup.appendChild(cTag('input',{ 'type':`hidden`,'value':``,'required':``,'name':`supplier_name`,'id':`supplier_name`,'class':`form-control` }));
								let newSupplier = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Supplier'),'class':`input-group-addon cursor` });
								newSupplier.addEventListener('click',()=>dynamicImport('./Manage_Data.js','addnewsupplierform',['addpo', 0]))
								newSupplier.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
							supplierInGroup.appendChild(newSupplier);
						supplierNameField.appendChild(supplierInGroup);
						supplierNameField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_supplier_id` }));
					supplierNameRow.appendChild(supplierNameField);
						const errorDiv = cTag('div',{ 'class':`columnXS12 columnMD6` });
						errorDiv.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_supplier_name` }));
					supplierNameRow.appendChild(errorDiv);
				addForm.appendChild(supplierNameRow);

					const dateExpectedRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const dateExpectedTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const dateExpectedLabel = cTag('label',{ 'for':`date_expected` });
							dateExpectedLabel.innerHTML = Translate('Date Expected');
						dateExpectedTitle.appendChild(dateExpectedLabel);
					dateExpectedRow.appendChild(dateExpectedTitle);
						const dateExpectedField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
						dateExpectedField.appendChild(cTag('input',{ 'type':`text`,'maxlength':`10`,'name':`date_expected`,'id':`date_expected`,'class':`form-control` }));
					dateExpectedRow.appendChild(dateExpectedField);
				addForm.appendChild(dateExpectedRow);

					const lotRefRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const lotRefTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const lotRefLabel = cTag('label',{ 'for':`lot_ref_no` });
							lotRefLabel.innerHTML = Translate('Lot Ref. No.');
						lotRefTitle.appendChild(lotRefLabel);
					lotRefRow.appendChild(lotRefTitle);
						const lotRefField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
						lotRefField.appendChild(cTag('input',{ 'type':`text`,'maxlength':`60`,'name':`lot_ref_no`,'id':`lot_ref_no`,'value':``,'class':`form-control` }));
					lotRefRow.appendChild(lotRefField);
				addForm.appendChild(lotRefRow);

					const salesTaxRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const salesTaxTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const salesTaxLabel = cTag('label',{ 'for':`taxes` });
							salesTaxLabel.innerHTML = Translate('Sales Tax');
						salesTaxTitle.appendChild(salesTaxLabel);
					salesTaxRow.appendChild(salesTaxTitle);
						const salesTaxField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
							const amountRow = cTag('div',{ 'class':`flexSpaBetRow` });
								const amountColumn = cTag('div',{ 'class':`columnXS8`, 'style': "padding-right: 0;" });
									let inputField = cTag('input', {id: "taxes", name: "taxes",'type': "text",'data-min':'0','data-format':'d.ddd', 'data-max': '99.999', class: "form-control"});
									controllNumericField(inputField, '#error_taxes');
								amountColumn.appendChild(inputField);
								amountColumn.appendChild(cTag('p',{'class':'errormsg','id':'error_taxes'}));
							amountRow.appendChild(amountColumn);
								const dropDownColumn = cTag('div',{ 'class':`columnXS4` });
									let selectPercent = cTag('select',{ 'id':`tax_is_percent`,'name':`tax_is_percent`,'class':`form-control`, 'style': "padding-left: 0; padding-right: 0;" });
									selectPercent.addEventListener('change',()=>updateTaxFieldAttributes.apply(document));
										let percentOption = cTag('option',{ 'value':`1` });
										percentOption.innerHTML = '%';
									selectPercent.appendChild(percentOption);
										let currencyOption = cTag('option',{ 'value':`0` });
										currencyOption.innerHTML = currency;
									selectPercent.appendChild(currencyOption);
								dropDownColumn.appendChild(selectPercent);
							amountRow.appendChild(dropDownColumn);
						salesTaxField.appendChild(amountRow);
					salesTaxRow.appendChild(salesTaxField);
				addForm.appendChild(salesTaxRow);

					const shippingRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const shippingTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const shippingLabel = cTag('label',{ 'for':`shipping` });
							shippingLabel.innerHTML = Translate('Shipping Cost');
						shippingTitle.appendChild(shippingLabel);
					shippingRow.appendChild(shippingTitle);
						const shippingCostField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
							let costField = cTag('input',{ 'type': "text",'data-min':'0','data-max':'999999.99','data-format':'d.dd','name':`shipping`,'id':`shipping`,'value':``,'class':`form-control` });
							controllNumericField(costField,'#error_shipping');
						shippingCostField.appendChild(costField);
						shippingCostField.appendChild(cTag('p',{'class':'errormsg','id':'error_shipping'}));
					shippingRow.appendChild(shippingCostField);
				addForm.appendChild(shippingRow);

					const supplierInvoiceRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const supplierInvoiceTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const supplierInvoiceLabel = cTag('label',{ 'for':`suppliers_invoice_no` });
							supplierInvoiceLabel.innerHTML = Translate('Suppliers Invoice No.');
						supplierInvoiceTitle.appendChild(supplierInvoiceLabel);
					supplierInvoiceRow.appendChild(supplierInvoiceTitle);
						const supplierInvoiceField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
						supplierInvoiceField.appendChild(cTag('input',{ 'type':`text`,'maxlength':`20`,'name':`suppliers_invoice_no`,'id':`suppliers_invoice_no`,'value':``,'class':`form-control` }));
					supplierInvoiceRow.appendChild(supplierInvoiceField);
				addForm.appendChild(supplierInvoiceRow);

					const invoiceDateRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const invoiceDateTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const invoiceDateLabel = cTag('label',{ 'for':`invoice_date` });
							invoiceDateLabel.innerHTML = Translate('Invoice Date');
						invoiceDateTitle.appendChild(invoiceDateLabel);
					invoiceDateRow.appendChild(invoiceDateTitle);
						const invoiceDateField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
							let invoiceDate = cTag('input',{ 'type':`text`,'maxlength':`10`,'name':`invoice_date`,'id':`invoice_date`,'class':`form-control` });
							checkDateOnBlur(invoiceDate,'#error_invoiceDate','Invalid Invoice Date');
						invoiceDateField.appendChild(invoiceDate);
						invoiceDateField.appendChild(cTag('p',{'class':'errormsg','id':'error_invoiceDate'}));
					invoiceDateRow.appendChild(invoiceDateField);
				addForm.appendChild(invoiceDateRow);

					const datePaidRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const datePaidTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const datePaidLabel = cTag('label',{ 'for':`date_paid` });
							datePaidLabel.innerHTML = Translate('Date Paid');
						datePaidTitle.appendChild(datePaidLabel);
					datePaidRow.appendChild(datePaidTitle);
						const datePaidField = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
							let datePaid = cTag('input',{ 'type':`text`,'maxlength':`10`,'name':`date_paid`,'id':`date_paid`,'class':`form-control` });
							checkDateOnBlur(datePaid,'#error_datePaid','Invalid Date Paid');
						datePaidField.appendChild(datePaid);
						datePaidField.appendChild(cTag('p',{'class':'errormsg','id':'error_datePaid'}));
					datePaidRow.appendChild(datePaidField);
				addForm.appendChild(datePaidRow);

					const returnRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
						const returnTitle = cTag('div',{ 'class':`columnXS4 columnSM3 columnMD2` });
							const returnLabel = cTag('label',{ 'for':`return_po`,'data-toggle':`tooltip`,'data-placement':`top`,'data-original-title':Translate('Choose Yes to return inventory to the supplier') });
							returnLabel.innerHTML = Translate('Is this for a RETURN? :');
						returnTitle.appendChild(returnLabel);
					returnRow.appendChild(returnTitle);
						const returnDropDown = cTag('div',{ 'class':`columnXS8 columnSM6 columnMD4` });
							let selectReturn = cTag('select',{ 'name':`return_po`,'id':`return_po`,'class':`form-control` });
								let noOption = cTag('option',{ 'value':`0` });
								noOption.innerHTML = Translate('No');
							selectReturn.appendChild(noOption);
								let yesOption = cTag('option',{ 'value':`1` });
								yesOption.innerHTML = Translate('Yes');
							selectReturn.appendChild(yesOption);
						returnDropDown.appendChild(selectReturn);
					returnRow.appendChild(returnDropDown);
						let errorColumn = cTag('div',{ 'class':`columnXS12` });
						errorColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_return` }));
					returnRow.appendChild(errorColumn);
				addForm.appendChild(returnRow);

					const buttonRow = cTag('div',{ 'class':`flexStartRow` });
						const buttonColumn = cTag('div',{ 'class':`columnXS12 columnMD6`,'align':`right` });
						buttonColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_id`,'id':`po_id`,'value':`0` }));
						buttonColumn.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`,'id':`cancelbutton`,'click': ()=>redirectTo('/Purchase_orders/lists'),'value':Translate('Cancel') }));
						buttonColumn.appendChild(cTag('input',{ 'class':`btn completeButton`, 'style': "margin-left: 10px;", 'name':`submit`,'id':`submit`,'type':`submit`,'value': Translate('Add') }));
					buttonRow.appendChild(buttonColumn);
				addForm.appendChild(buttonRow);
			callOutDiv.appendChild(addForm);
		addColumn.appendChild(callOutDiv);
	dashboard.appendChild(addColumn);
    updateTaxFieldAttributes.apply(document);
    applySanitizer(dashboard);

	AJ_add_MoreInfo()
}

async function AJ_add_MoreInfo(){
    const url = '/'+segment1+'/AJ_add_MoreInfo';
	fetchData(afterFetch,url,{});

    function afterFetch(data){
        const supplier_id = document.querySelector('#supplier_id')
		if(data.suppliers_id>0){
			if(data.supplier_name !== ''){
				supplier_id.appendChild(cTag('input',{ 'readonly':'','type':`text`,'value':data.supplier_name,'required':``,'name':`supplier_name`,'id':`supplier_name`,'class':`form-control`,'placeholder':Translate('Type Supplier Name') }));
				supplier_id.appendChild(cTag('input',{ 'type':`hidden`,'name':`supplier_id`,'id':`supplier_id`,'value':data.suppliers_id }));
			}
		}
		else{
				const option = cTag('option',{'value':''});
				option.innerHTML = Translate('Select Supplier');
			supplier_id.appendChild(option);
			setOptions(supplier_id,data.supOpt,1,1);	
		}			
		document.querySelector('#date_expected').value = DBDateToViewDate(data.date_expected);
		document.querySelector('#invoice_date').value = DBDateToViewDate(data.invoice_date);
		document.querySelector('#date_paid').value = DBDateToViewDate(data.date_paid);

		if (document.querySelector("#date_expected") && document.querySelector("#invoice_date") && document.querySelector("#date_paid")) {
			date_picker("#date_expected");
			date_picker("#invoice_date");
			date_picker("#date_paid");
		}
    }
}

function returnPO(){
	const {supplier_id,date_expected,lot_ref_no,taxes,tax_is_percent,shipping,suppliers_invoice_no,invoice_date,date_paid} = JSON.parse(window.localStorage.getItem('returnPOform'));
	
	let requiredField;
	const Dashboard = document.getElementById('viewPageInfo');
		const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const titleColumn = cTag('div',{ 'class':`columnSM12` });
				const titleHeader = cTag('h2',{ 'style': "padding-top: 5px; text-align: start;" });
				titleHeader.append(Translate('Return Purchase Order')+' ');
				titleHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('This page captures the basic details required to return purchase order') }));
			titleColumn.appendChild(titleHeader);
		titleRow.appendChild(titleColumn);
	Dashboard.appendChild(titleRow);
		const returnPoRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const returnPoColumn = cTag('div',{ 'class':`columnSM12`});
				let callOutDiv = cTag('div',{ 'class':`innerContainer`});
					const returnPoForm = cTag('form',{'action':`#`,'name':`frmReturnPO`,'id':`frmReturnPO`,'submit':saveReturnPO,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
						const productNameRow = cTag('div',{ 'class':`flexStartRow` });
							const productNameTitle = cTag('div',{ 'class':`columnXS6 columnSM2` });
								const productNameLabel = cTag('label',{ 'for':`` });
								productNameLabel.append(Translate('Product Name'));
									requiredField = cTag('span',{ 'class':`required` });
									requiredField.innerHTML = '*';
								productNameLabel.appendChild(requiredField);
							productNameTitle.appendChild(productNameLabel);
						productNameRow.appendChild(productNameTitle);
							let productNameField = cTag('div',{ 'class':`columnXS6 columnSM4`,'id':`productnamestr` });
							productNameField.appendChild(cTag('input',{ 'maxlength':`50`,'type':`text`,'value':``,'required':``,'name':`product_name`,'id':`product_name`,'class':`form-control returnPO` }));
						productNameRow.appendChild(productNameField);
							let errorDiv = cTag('div',{ 'class':`columnXS12 columnSM6` });
							errorDiv.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_product_name` }));
						productNameRow.appendChild(errorDiv);
					returnPoForm.appendChild(productNameRow);

						let qtyImeiRow = cTag('div',{ 'class':`flexStartRow`,'id':`qty_or_imeirow` });
							const qtyTitle = cTag('div',{ 'class':`columnXS6 columnSM2` });
								const qtyLabel = cTag('label',{ 'for':`qty_or_imei`,'id':`qty_or_imeilv` });
								qtyLabel.append(Translate('QTY'));
									requiredField = cTag('span',{ 'class':`required` });
									requiredField.innerHTML = '*';
								qtyLabel.appendChild(requiredField);
							qtyTitle.appendChild(qtyLabel);
						qtyImeiRow.appendChild(qtyTitle);
							const qtyField = cTag('div',{ 'class':`columnXS6 columnSM4` });
							qtyField.appendChild(cTag('input',{ 'type':`text`,'required':``,'maxlength':`20`,'name':`qty_or_imei`,'id':`qty_or_imei`,'value':``,'class':`form-control` }));
						qtyImeiRow.appendChild(qtyField);
							let errorColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
							errorColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_qty_or_imei` }));
						qtyImeiRow.appendChild(errorColumn);
					returnPoForm.appendChild(qtyImeiRow);

						let allPoRow = cTag('div',{ 'class':`flexStartRow` });
							let allPoColumn = cTag('div',{ 'class':`columnSM12 columnMD6`,'align':`right` });
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`product_id`,'id':`product_id`,'value':`0` }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`product_type`,'id':`product_type`,'value':`` }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`maxqty`,'id':`maxqty`,'value':`0` }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_id`,'id':`item_id`,'value':`0` }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`supplier_id`,'id':`supplier_id`,'value':supplier_id }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`date_expected`,'id':`date_expected`,'value':date_expected }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`lot_ref_no`,'id':`lot_ref_no`,'value':lot_ref_no }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes`,'id':`taxes`,'value':taxes }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_is_percent`,'id':`tax_is_percent`,'value':tax_is_percent }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`shipping`,'id':`shipping`,'value':shipping }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`suppliers_invoice_no`,'id':`suppliers_invoice_no`,'value':suppliers_invoice_no }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`invoice_date`,'id':`invoice_date`,'value':invoice_date }));
							allPoColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`date_paid`,'id':`date_paid`,'value':date_paid }));
							allPoColumn.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`,'id':`cancelbutton`,'click': ()=>{window.localStorage.removeItem('returnPOform');redirectTo('/Purchase_orders/add')},'value':Translate('Cancel') }));
							allPoColumn.appendChild(cTag('input',{ 'class':`btn completeButton`, 'style': "margin-left: 10px;", 'name':`submit`,'id':`submit`,'type':`submit`,'value':Translate('Confirm Return') }));
						allPoRow.appendChild(allPoColumn);
					returnPoForm.appendChild(allPoRow);
				callOutDiv.appendChild(returnPoForm);
			returnPoColumn.appendChild(callOutDiv);
		returnPoRow.appendChild(returnPoColumn);
	Dashboard.appendChild(returnPoRow);
	if(document.querySelector("#productnamestr") && document.querySelector("#product_name")){
		AJautoComplete_ProductName();
	}
}

function confirmReturn(){
	let requiredField;
	const Dashboard = document.getElementById('viewPageInfo');
		const titleRow = cTag('div');
			const headerTitle = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
			headerTitle.append(Translate('Confirm Return Purchase Order')+' ');
			headerTitle.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('This page captures the basic details required to return purchase order') }));
		titleRow.appendChild(headerTitle);
	Dashboard.appendChild(titleRow);
		const confirmReturnColumn = cTag('div',{ 'class':`columnSM12` });
			const callOutDiv = cTag('div',{ 'class':`innerContainer`, 'style':`background: #fff;` });
				const confirmReturnForm = cTag('form',{ 'name':`frmConfirmReturn`,'id':`frmConfirmReturn`,'action':`#`,'submit':saveConfirmReturn,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
					const supplierNameRow = cTag('div',{ 'class':`flex`, 'style': "align-items: center;" });
						const supplierNameTitle = cTag('div',{ 'class':`columnXS6 columnSM2` });
							const supplierNameLabel = cTag('label',{ 'for':`` });
							supplierNameLabel.append(Translate('Supplier Name'));
								requiredField = cTag('span',{ 'class':`required` });
								requiredField.innerHTML = '*';
							supplierNameLabel.appendChild(requiredField);
						supplierNameTitle.appendChild(supplierNameLabel);
					supplierNameRow.appendChild(supplierNameTitle);
						const supplierNameField = cTag('div',{ 'class':`columnXS6 columnSM4` });
						supplierNameField.appendChild(cTag('input',{ 'type':`text`,'readonly':``,'required':``,'name':`supplier_name`,'id':`supplier_name`,'class':`form-control returnPO` }));
					supplierNameRow.appendChild(supplierNameField);
				confirmReturnForm.appendChild(supplierNameRow);

					const lotRefRow = cTag('div',{ 'class':`flex`, 'style': "align-items: center;" });
						const lotRefTitle = cTag('div',{ 'class':`columnXS6 columnSM2` });
							const lotRefLabel = cTag('label',{ 'for':`` });
							lotRefLabel.innerHTML = Translate('Lot Ref. No.');
						lotRefTitle.appendChild(lotRefLabel);
					lotRefRow.appendChild(lotRefTitle);
						const lotRefField = cTag('div',{ 'class':`columnXS6 columnSM4` });
						lotRefField.appendChild(cTag('input',{ 'type':`text`,'readonly':``,'required':``,'name':`lot_ref_no`,'id':`lot_ref_no`,'class':`form-control returnPO` }));
					lotRefRow.appendChild(lotRefField);
				confirmReturnForm.appendChild(lotRefRow);

					const productNameRow = cTag('div',{ 'class':`flex`, 'style': "align-items: center;" });
						const productNameTitle = cTag('div',{ 'class':`columnXS6 columnSM2` });
							const productNameLabel = cTag('label',{ 'for':`` });
							productNameLabel.append(Translate('Product Name'));
								requiredField = cTag('span',{ 'class':`required` });
								requiredField.innerHTML = '*';
							productNameLabel.appendChild(requiredField);
						productNameTitle.appendChild(productNameLabel);
					productNameRow.appendChild(productNameTitle);
						const productNameField = cTag('div',{ 'class':`columnXS6 columnSM4` });
						productNameField.appendChild(cTag('input',{ 'maxlength':`100`,'type':`text`,'readonly':``,'required':``,'name':`product_name`,'id':`product_name`,'class':`form-control returnPO` }));
					productNameRow.appendChild(productNameField);
				confirmReturnForm.appendChild(productNameRow);

					const oldQtyRow = cTag('div',{ 'class':`flex`, 'style': "align-items: center;" });
						let oldQtyTitle = cTag('div',{ 'class':`columnXS6 columnSM2` });								
						oldQtyTitle.appendChild(cTag('label',{ 'id':`QtyOrIMIELable` }));
					oldQtyRow.appendChild(oldQtyTitle);
						let oldQtyValue = cTag('div',{ 'class':`columnXS6 columnSM4` });
						oldQtyValue.appendChild(cTag('input',{ 'type':`text`,'required':``,'readonly':``,'name':`qty`,'id':`qty`,'class':`form-control returnPO` }));
						oldQtyValue.appendChild(cTag('input',{ 'type':`hidden`,'name':`oldqty`,'id':`oldqty` }));
					oldQtyRow.appendChild(oldQtyValue);
				confirmReturnForm.appendChild(oldQtyRow);

					const noteRow = cTag('div',{ 'class':`flex`});
						const noteTitle = cTag('div',{ 'class':`columnXS6 columnSM2` });
							const noteLabel = cTag('label',{ 'for':`` });
							noteLabel.innerHTML = Translate('Note')+' ';
						noteTitle.appendChild(noteLabel);
					noteRow.appendChild(noteTitle);
						const noteField = cTag('div',{ 'class':`columnXS6 columnSM10` });
						noteField.appendChild(cTag('textarea',{ 'rows':`4`,'name':`note`,'id':`note`,'class':`form-control returnPO` }));
					noteRow.appendChild(noteField);
				confirmReturnForm.appendChild(noteRow);

					let buttonDiv = cTag('div',{ 'class':`flexCenterRow` });
					buttonDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_id`,'id':`po_id` }));
					buttonDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_items_id`,'id':`po_items_id` }));
					buttonDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`cost`,'id':`cost` }));
					buttonDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_type`,'id':`item_type` }));
					buttonDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_number`,'id':`po_number` }));
					buttonDiv.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`,'id':`cancelbutton`,'click': ()=>redirectTo('/Purchase_orders/lists'),'value':Translate('Cancel') }));
					buttonDiv.appendChild(cTag('input',{ 'class':`btn completeButton`, 'style': "margin-left: 10px;", 'name':`submit`,'id':`submit`,'type':`submit`,'value':Translate('Mark Completed') }));
				confirmReturnForm.appendChild(buttonDiv);
			callOutDiv.appendChild(confirmReturnForm);
		confirmReturnColumn.appendChild(callOutDiv);
	Dashboard.appendChild(confirmReturnColumn);

	AJ_confirmReturn_MoreInfo();
}

async function AJ_confirmReturn_MoreInfo(){
    const jsonData = {po_number:segment3};
    const url = '/'+segment1+'/AJ_confirmReturn_MoreInfo';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        document.querySelector('#supplier_name').value = data.supplier_name;
		document.querySelector('#lot_ref_no').value = data.lot_ref_no;
		document.querySelector('#product_name').value = data.product_name;
		const label = document.querySelector('#QtyOrIMIELable');
		label.append(data.readonly==='readonly'?Translate('IMEI Numbers'):Translate('QTY'));
			const requiredSpan = cTag('span',{ 'class':`required` });
			requiredSpan.innerHTML = '*';
		label.appendChild(requiredSpan);
		const qty = document.querySelector('#qty');
		qty.value = data.qty;
		if(data.readonly === "readonly") qty.readOnly = true;
		document.querySelector('#oldqty').value = data.qty;
		document.querySelector('#po_id').value = data.po_id;
		document.querySelector('#po_items_id').value = data.po_items_id;
		document.querySelector('#cost').value = data.cost;
		document.querySelector('#item_type').value = data.item_type;
		document.querySelector('#po_number').value = data.po_number;
    }
}

function AJautoComplete_cartPOProduct(){
	const statusVal = document.querySelector("#status").value;
	if(!['Closed', 'Cancel'].includes(statusVal)){
		const search_sku = document.getElementById("search_sku");
		setTimeout(function() {search_sku.focus();}, 500);
		if(search_sku){					
			customAutoComplete(search_sku,{								
				minLength:2,
				source: async function (request, response) {
					const jsonData = {};
					jsonData['keyword_search'] = request;
	
					const url = "/Purchase_orders/AJautoComplete_cartProductPO";
					await fetchData(afterFetch,url,jsonData,'JSON',0);

					function afterFetch(data){
						response(data.returnStr);
					}
				},
				select: function( event, info ) {
					search_sku.value = info.labelval;
					addToPOCart();
					return false;
				},
				renderItem: function( item ) {
					const li = cTag('li',{ 'class':`ui-menu-item` });
						const stockQtyDiv = cTag('div');
							const bTag = cTag('b');
							bTag.innerHTML = `(${item.stockQty})`;
						stockQtyDiv.innerHTML = item.label;
						stockQtyDiv.append(' ',bTag);
					li.appendChild(stockQtyDiv);
					return li;
				}
			});
		}	
		search_sku.addEventListener('keydown',function (event) {
			if (event.which === 13) {
				search_sku.hide();
				addToPOCart();
				return false;
			}
		});	
	}
	poCartsAutoFuncCall();
}

//PO-Cart

async function addToPOCart(){
	const messageid = document.getElementById("error_search_sku");
	messageid.innerHTML = '';
	const field = document.getElementById("search_sku");
	const po_id = document.getElementById("po_id").value;
	const supplier_id = document.getElementById("supplier_id").value;
	
	if(field.value===''){
		messageid.innerHTML = Translate('Missing product sku');
		field.focus();
	}
	else{	
		const jsonData = {};
		jsonData['fieldname'] = "sku";
		jsonData['fieldvalue'] = field.value;
		jsonData['po_id'] = po_id;
		jsonData['supplier_id'] = supplier_id;
		field.value = '';

		const url = '/'+segment1+'/addProductToPOCart';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.action ==='Add' || data.action === 'Update'){
				if(data.cartsData.length>0){
					loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
				}
				else{
					document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
				}
				poCartsAutoFuncCall();
				
				if(data.action==='Update'){
					showTopMessage('success_msg', Translate('This product already added into Order.'));
				}
			}
			else if(data.action ==='Add_Product_Order') messageid.innerHTML = Translate('Sorry! could not add product to order');
			else if(data.action ==='Product_Found_Sku') messageid.innerHTML = Translate('There is no product found by this sku');
			else if(data.action ==='No_Order_Found') messageid.innerHTML = Translate('There is no order found');
			else messageid.innerHTML = data.action;
			field.focus();
		}
	}
}

async function addProductToPOCart({detail:product_id}){
	const messageid = document.getElementById("error_productlist");
	messageid.innerHTML = '';
	const po_id = document.getElementById("po_id").value;

	const jsonData = {};
	jsonData['fieldname'] = "product_id";
	jsonData['fieldvalue'] = product_id;
	jsonData['po_id'] = po_id;

	const url = '/'+segment1+'/addProductToPOCart';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action ==='Add' || data.action === 'Update'){
			if(data.cartsData.length>0){
                loadPOCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
            }
            else{
                document.getElementById("invoice_entry_holder").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
            }
			poCartsAutoFuncCall();
			document.getElementById('search_sku').value = '';
			
			if(data.action==='Update'){
				showTopMessage('success_msg', Translate('This product already added into Order.'));
			}
		}
		else{
			messageid.innerHTML = data.action;
		}
    }
}

function poCartsAutoFuncCall(){
	calculatePOTotal();
	filter_Purchase_orders_edit();
	let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#";
	document.querySelectorAll(".item_number").forEach(oneRowObj=>{

		oneRowObj.addEventListener('keypress', e => {
			if(e.which === 13){
				let item_number = e.target.value.replace(/-/g, ' ').trim().replace(/ /g, '-');
				let po_items_id = e.target.id.replace('item_number', '');
				let oElement = document.getElementById("error_item_number"+po_items_id);
				oElement.innerHTML = '';
								
				if(item_number.length<2){oElement.innerHTML = Translate('IMEI should be min 2 characters.');}
				else{updatePOItem(po_items_id, item_number);}
				e.target.focus();
				return false;
			}
		});
		oneRowObj.addEventListener('blur', function(){
			this.value = this.value.replace(/-/g, ' ').trim().replace(/ /g, '-')
		});
		oneRowObj.addEventListener('keyup', e => {
			let sku = e.target.value.toUpperCase().replace(' ', '-');
			let IsNumber=true;
			let Char;
			let newsku = '';
			for (let i = 0; i < sku.length && IsNumber === true; i++){ 
				Char = sku.charAt(i); 
				if (ValidChars.indexOf(Char) === -1){}
				else{
					newsku = newsku+Char;
				}
			}			
			if(sku.length> newsku.length || e.target.value !== newsku){
				e.target.value = newsku;
			}
		});	
	});
	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
}

function calculatePOTotal(){
	let ordered_qtytotal = 0;
	let received_qtytotal = 0;
	let grand_total = 0;
	
	const listcount = document.querySelector("#invoice_entry_holder").querySelector('.nodata');
	let RAllP = 0;
	if(!listcount){
		const po_items_idarray = document.getElementsByName("po_items_id[]");
		if(po_items_idarray.length>0){
			if(document.getElementById("barcodeserno")){
				document.getElementById("barcodeserno").innerHTML = parseInt(po_items_idarray.length+1);
			}
			for(let p=0; p<po_items_idarray.length; p++){
				const po_items_id = po_items_idarray[p].value;
				
				let ordered_qty = parseFloat(document.getElementById("ordered_qty"+po_items_id).value);
				if(isNaN(ordered_qty) || ordered_qty===''){ordered_qty = 0;}
				
				let received_qty = parseFloat(document.getElementById("received_qty"+po_items_id).value);
				if(isNaN(received_qty) || received_qty===''){received_qty = 0;}
				
				const item_type = document.getElementById("item_type"+po_items_id).value;
				if(item_type==='product' && ordered_qty>received_qty){RAllP++;}
				
				let total = parseFloat(document.getElementById("total"+po_items_id).value);
				if(isNaN(total) || total===''){total = 0;}
				
				ordered_qtytotal = calculate('add',ordered_qtytotal,ordered_qty,2);
				received_qtytotal = calculate('add',received_qtytotal,received_qty,2);
				grand_total = calculate('add',total,grand_total,2);
			}
		}
		else{
			let taxes = parseFloat(document.getElementById("taxes").value);
			let taxesStr;						
			if(parseInt(document.getElementById("tax_is_percent").value)===0) taxesStr = currency+taxes;
			else taxesStr = currency+'0.00';
			document.getElementById("taxesTotalstr").innerHTML = taxesStr;
			const shipping = parseFloat(document.getElementById("shipping").value)
			document.getElementById("shippingTotalstr").innerHTML = addCurrency(shipping);	
			let subTotalStr = Translate('Total');
			if(taxes !==0 || shipping !==0){
				subTotalStr = Translate('Subtotal');
			}
			document.getElementById('subTotalStr').innerHTML = subTotalStr+' :';
			return false;
		}
	}
	else{
		document.getElementById("barcodeserno").innerHTML = 1;
	}
	document.getElementById("orderQtyTotal").innerHTML = ordered_qtytotal;
	document.getElementById("receivedQtyTotal").innerHTML = received_qtytotal;
	
	document.getElementById("grand_totalstr").innerHTML = addCurrency(grand_total);
	document.getElementById("grand_total").value = grand_total;
	
	let taxes = parseFloat(document.getElementById("taxes").value);
	if(isNaN(taxes) || taxes===''){taxes = 0;}
	let shipping = parseFloat(document.getElementById("shipping").value);
	if(isNaN(shipping) || shipping===''){shipping = 0;}
	document.getElementById("shippingTotalstr").innerHTML = addCurrency(shipping);
	let subTotalStr = Translate('Total');
	if(taxes !==0 || shipping !==0){
		subTotalStr = Translate('Subtotal');
	}
	document.getElementById('subTotalStr').innerHTML = subTotalStr+' :';

	let tax_is_percent = parseInt(document.getElementById("tax_is_percent").value);
	if(isNaN(tax_is_percent) || tax_is_percent===''){tax_is_percent = 0;}
	
	let taxesTotal = 0;									
	if(tax_is_percent===0)
		taxesTotal = taxes;
	else
		taxesTotal = calculate('mul',grand_total,calculate('mul',taxes,0.01,false),2);
	
	document.getElementById("taxesTotalstr").innerHTML = addCurrency(taxesTotal);

	document.getElementById("shippingTotalstr").innerHTML = addCurrency(shipping);		

	grand_total = calculate('add',grand_total,calculate('add',taxesTotal,shipping,2),2);
	document.getElementById("grandTotalstr").innerHTML = addCurrency(grand_total);

	if(document.getElementById("ReceiveAllProducts")){
		if(RAllP>0){
			if(document.querySelector("#ReceiveAllProducts").style.display==='none'){
				document.querySelector("#ReceiveAllProducts").style.display='block';
			}
		}
		else{
			if(document.getElementById("ReceiveAllProducts").style.display!=='none'){
				document.getElementById("ReceiveAllProducts").style.display='none';
			}
		}
	}
	if(document.getElementById("possubmitdis")){
		if(ordered_qtytotal===received_qtytotal){
			if(document.getElementById("possubmitdis").style.display!=='none'){
				document.getElementById("possubmitdis").style.display='none';
			}
			if(document.querySelector("#possubmit").style.display==='none'){
				document.querySelector("#possubmit").style.display='block';
			}
			return true;
		}
		else{
			if(document.getElementById("possubmit").style.display!=='none'){
				document.getElementById("possubmit").style.display='none';
			}
			if(document.querySelector("#possubmitdis").style.display==='none'){
				document.querySelector("#possubmitdis").style.display='block';
			}
			return false;
		}
	}
}

function loadPOCartData(parentNode,cartsData){
    const ReceiveAllProducts = document.getElementById('ReceiveAllProducts');
	if(cartsData && cartsData.length>0){
		parentNode.innerHTML = '';
	
        if(OS==='unknown'){
            if(ReceiveAllProducts && ReceiveAllProducts.style.display === 'none') ReceiveAllProducts.style.display = '';
        }

		cartsData.forEach((item,indx) => {
			let td,div;
			const product_name = document.createDocumentFragment();
			let spanTag = cTag('span');
			spanTag.innerHTML = item.product_name;
			product_name.append(spanTag);
			
			if(item.item_type!=='one_time'){
					const productViewLink = cTag('a',{ 'href':`/Products/view/${item.product_id}`, 'style': "color: #009; text-decoration: underline;", 'title':Translate('View Product Details') });
					productViewLink.append(item.sku,' ');
					productViewLink.appendChild(cTag('i',{ 'class':`fa fa-link` }));
				product_name.append(' (',productViewLink,')');
			}
			let description = product_name;
			if(item.item_type==='cellphones'){
					const descriptionDiv = cTag('div');
						let descriptionColumn = cTag('div',{ 'class':`columnSM12`});
						descriptionColumn.append(description);
					descriptionDiv.appendChild(descriptionColumn);

					item.cellPhoneData.forEach(info=>{
						let addistr = document.createDocumentFragment();									
						if(info.carrier_name !==''){ 
							addistr.append(info.carrier_name);
						}
						if(info.return_po_items_id>0 && info.po_or_return===0){
								let returnSpan = cTag('span',{ 'class':`bgblack`, 'style': "margin-left: 15px; padding: 5px; color: white;" });
								returnSpan.innerHTML = Translate('Return');
							addistr.append(' ',returnSpan);
						}

						let editremoveicon = document.createDocumentFragment();
						if(item.status === "Open" && info.in_inventory==1){
							editremoveicon.append(' ',cTag('i',{ 'class':`fa fa-edit`,'data-original-title':Translate('Edit IMEI details'),'data-toggle':`tooltip`,'click':()=>changeImeiOnPO(info.item_id, item.product_id, info.carrier_name),'style':`cursor:pointer;` }));
							if(info.cartUsedCount>0 || info.return_po_items_id>0){
								editremoveicon.append('  ',cTag('i',{ 'style':`cursor:pointer;`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove IMEI'),'click':()=>alert_dialog(Translate('Remove IMEI'), Translate('This IMEI can not be removed because it has already been sold or transferred.'), Translate('Ok')),'class':`fa fa-trash-o` }));
							}
							else if(info.in_inventory>0){
								editremoveicon.append('  ',cTag('i',{ 'style':`cursor:pointer;`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove IMEI'),'click': ()=>removeIMEIFromPOCart(item.po_items_id, info.item_id),'class':`fa fa-trash-o` }));
							}
						}
							
							let imeiColumn = cTag('div',{ 'class':`columnSM12`, 'style': "padding-left: 10px;" });
							if(item.status === "Closed"){
									const imeiViewLink = cTag('a',{ 'href':`/IMEI/view/${info.item_number}`, 'style': "color: #009; text-decoration: underline;", 'title':Translate('View IMEI details') });
									imeiViewLink.append(info.item_number,' ');
									imeiViewLink.appendChild(cTag('i',{ 'class':`fa fa-link` }));
								imeiColumn.appendChild(imeiViewLink);
								imeiColumn.append(addistr);
							}
							else imeiColumn.append(info.item_number,' ',addistr,editremoveicon);
						descriptionDiv.appendChild(imeiColumn);
					})

					if(item.status ==='Open'){
							let statusColumn = cTag('div',{ 'class':`columnSM5`, 'style': "padding-left: 10px;" });
							if(parseInt(item.ordered_qty)>parseInt(item.received_qty)){
								let input, inputGroup, spanTag;
								if(OS==='unknown'){
									inputGroup = cTag('input',{ 'class':`form-control item_number`,'name':`item_number${item.po_items_id}`,'id':`item_number${item.po_items_id}`,'title':``,'placeholder':Translate('IMEI Number'),'maxlength':`20` });
								}
								else{
									inputGroup = cTag('div',{ 'class': 'input-group' });
									input = cTag('input',{ 'class':`form-control item_number`, 'style': "min-width: 120px;", 'name':`item_number${item.po_items_id}`,'id':`item_number${item.po_items_id}`,'title':``,'placeholder':Translate('IMEI Number'),'maxlength':`20` });
									inputGroup.appendChild(input);
										spanTag = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor', 'title': 'Click for Enter'});
											let turnIcon = cTag('i', {'class':'fa fa-turn-down-left'});
										spanTag.appendChild(turnIcon);
										spanTag.addEventListener('click', function(){
											let item_number = input.value.replace(/-/g, ' ').trim().replace(/ /g, '-');
											let po_items_id = input.id.replace('item_number', '');
											let oElement = document.getElementById("error_item_number"+po_items_id);
											oElement.innerHTML = '';
															
											if(item_number.length<2){oElement.innerHTML = Translate('IMEI should be min 2 characters.');}
											else{updatePOItem(po_items_id, item_number);}
											input.focus();
											return false;
										});
									inputGroup.appendChild(spanTag);
								}
								statusColumn.appendChild(inputGroup);
							}
							else{
								statusColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_number${item.po_items_id}`,'id':`item_number${item.po_items_id}`,'title':``,'placeholder':Translate('IMEI Number'),'maxlength':`20` }));
							}
						descriptionDiv.appendChild(statusColumn);
						descriptionDiv.appendChild(cTag('div',{ 'class':`columnSM7 error_msg`, 'style': "padding-left: 0;", 'id':`error_item_number${item.po_items_id}` }));
					}
				description = descriptionDiv;
			}

			let $class = '';
			if(parseInt(item.ordered_qty)>parseInt(item.received_qty) && parseInt(item.received_qty)>0){$class = "alert alert-danger";}

			const del_edit = document.createDocumentFragment();
			if(item.status === 'Open'){
					const td = cTag('td',{ 'align':`center` });
					if(item.imeicount === 0 && item.received_qty===0){
						td.appendChild(cTag('i',{ 'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove Item'),'click':()=>removeThisPOItem(item.po_items_id),'class':`fa fa-trash-o` }));
						td.append('  ');
					}
					td.appendChild(cTag('i',{ 'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Edit Purchase Order'),'click':()=>changeThisPORow(item.po_items_id),'class':`fa fa-edit` }));
				del_edit.appendChild(td);
			}
			
			const tr = cTag('tr',{ 'class':`poRow${item.po_items_id}` });
				td = cTag('td',{ 'align':`right` });
				td.innerHTML = indx+1;
			tr.appendChild(td);
				td = cTag('td',{ 'align':`left` });
				td.append(description);
			tr.appendChild(td);
				td = cTag('td',{ 'align':`right` });
				td.appendChild(NeedHaveOnPO(item.HaveOnPO, item.product_id));
			tr.appendChild(td);
				td = cTag('td',{ 'align':`right` });
				td.innerHTML = item.ordered_qty;
			tr.appendChild(td);
				td = cTag('td',{ 'class':$class,'align':`right` });
				td.innerHTML = item.received_qty;
			tr.appendChild(td);
				td = cTag('td',{ 'align':`right` });
				td.innerHTML = addCurrency(item.cost);
			tr.appendChild(td);
				td = cTag('td',{ 'id':`totalstr${item.po_items_id}`,'align':`right` });
				td.append(addCurrency(item.total));
				td.appendChild(cTag('input',{ 'type':`hidden`,'name':`po_items_id[]`,'value':`${item.po_items_id}` }));
				td.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_id${item.po_items_id}`,'id':`item_id${item.po_items_id}`,'value':item.product_id }));
				td.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_type${item.po_items_id}`,'id':`item_type${item.po_items_id}`,'value':item.item_type }));
				td.appendChild(cTag('input',{ 'type':`hidden`,'name':`cost${item.po_items_id}`,'id':`cost${item.po_items_id}`,'value':round(item.cost,2) }));
				td.appendChild(cTag('input',{ 'type':`hidden`,'name':`ordered_qty${item.po_items_id}`,'id':`ordered_qty${item.po_items_id}`,'value':item.ordered_qty }));
				td.appendChild(cTag('input',{ 'type':`hidden`,'name':`received_qty${item.po_items_id}`,'id':`received_qty${item.po_items_id}`,'value':item.received_qty }));
				td.appendChild(cTag('input',{ 'type':`hidden`,'name':`total${item.po_items_id}`,'id':`total${item.po_items_id}`,'value':item.total }));
			tr.appendChild(td);
			tr.append(del_edit);
			parentNode.appendChild(tr);
		});
	}
    else{
        if(ReceiveAllProducts && ReceiveAllProducts.style.display !== 'none') ReceiveAllProducts.style.display = 'none';
    }
}

async function saveChangePOSupplier(info){
	if(document.getElementById('supplier_id').value === info.id){
		showTopMessage('error_msg',Translate('You have chosen the existing supplier'));
		document.getElementById('supplier').value = '';
		return;
	} 
	
	const jsonData = {
		po_id:document.getElementById('po_id').value, 
		supplier_id:info.id
	};
    const url = '/'+segment1+'/saveChangePOSupplier';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const company_link = document.querySelector('#company_link');
		company_link.setAttribute('href',`/Manage_Data/sview/${info.id}`);
		company_link.innerHTML = data.company;
		company_link.appendChild(cTag('i',{ 'class':`fa fa-link` }));
		
		const suppliername = document.querySelector('#suppliername');
		suppliername.innerHTML = '';
		suppliername.append(data.name)
		if(parseInt(document.getElementById('supPermission').value)===1){
			const edit = cTag('i',{ 'style':'cursor:pointer','class':`fa fa-edit` });
			edit.addEventListener('click',()=>dynamicImport('./Manage_Data.js','addnewsupplierform',['editpo', info.id]));
			suppliername.append(' ',edit);       
		} 
		
		document.querySelector('#supplieremail').innerHTML = data.email;
		document.querySelector('#email_address').value = data.email;

		document.getElementById('supplier').value = '';
		document.getElementById('supplier_id').value = info.id;
    }
}
		
document.addEventListener('DOMContentLoaded', async()=>{
	let layoutFunctions = {lists, edit, add, returnPO, confirmReturn};
	layoutFunctions[segment2]();

	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    addCustomeEventListener('labelSizeMissing',alert_label_missing);
});
