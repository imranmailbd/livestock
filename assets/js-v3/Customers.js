import {cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, emailcheck, checkPhone, noPermissionWarning, 
	confirm_dialog, alert_dialog, setSelectOpt, setTableRows, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, 
	checkAndSetSessionData, popup_dialog, popup_dialog600, date_picker, daterange_picker_dialog, validDate, dynamicImport, 
	applySanitizer, clearCustomerField, AJget_modelOpt, archiveData, unarchiveData, generateCustomeFields, fetchData,
	addCustomeEventListener, actionBtnClick, showNewInputOrSelect, serialize, onClickPagination, AJautoComplete, historyTable, 
	activityFieldAttributes, validifyCustomField
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

const listsFieldAttributes = [{'datatitle':Translate('Company'), 'align':'justify'},
                    {'datatitle':Translate('Email'), 'align':'justify'},
                    {'datatitle':Translate('Contact No'), 'align':'justify'}];
const uriStr = segment1+'/view';

const propertiesFieldAttributes = [{ 'datatitle':Translate('Brand'), 'align':'left'},
                    {'datatitle':Translate('Model'), 'align':'left'},
                    {'datatitle':Translate('More Details'), 'align':'left'},
                    {'datatitle':Translate('IMEI/Serial No.'), 'align':'right'},
                    {'datatitle':Translate('Action'), 'align':'center'}];

async function filter_Customers_lists(){
    let page = 1;
    document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.getElementById("sdata_type").value;
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	let scustomer_type = document.getElementById("scustomer_type").value;
	jsonData['scustomer_type'] = scustomer_type;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetPage/filter';
	fetchData(afterFetch,url,jsonData);
	
	function afterFetch(data){
		storeSessionData(jsonData);
		document.getElementById("totalTableRows").value = data.totalRows;
		
		setSelectOpt('scustomer_type', 'All', Translate('All Types'), data.custTypeOpt, 0, data.custTypeOpt.length);			
		setTableRows(data.tableRows, listsFieldAttributes, uriStr);			
		document.getElementById("scustomer_type").value = scustomer_type;
		onClickPagination();
	}
}

async function loadTableRows_Customers_lists(){
	const jsonData = {};
	jsonData['sdata_type'] = document.getElementById("sdata_type").value;
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['scustomer_type'] = document.getElementById("scustomer_type").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		setTableRows(data.tableRows, listsFieldAttributes, uriStr);
		onClickPagination();
	}
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    
    let showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '30' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        const input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
    showTableData.appendChild(input);
    });

		let sortDropDown;
		const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable"});
			const titleName = cTag('div', {class: "columnXS6 columnSM6"});
				const headerTitle = cTag('h2', {'style': 'text-align: start;' });
				headerTitle.innerHTML = Translate('Manage Customers')+' ';
					const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('This page displays the list of your customers')});
				headerTitle.appendChild(infoIcon);
			titleName.appendChild(headerTitle);
		titleRow.appendChild(titleName);
			const buttonName = cTag('div', {class: "columnXS12 columnSM6", 'style': "text-align: right;"});
				let crmButton = cTag('a', {class: "btn defaultButton", 'style': "margin-right: 10px; padding-top: 5px; padding-bottom: 5px;", 'href':'/Customers/crm'});
				if(cncrm===1) crmButton.addEventListener('click', event=>{
					event.preventDefault();
					noPermissionWarning('CRM');
				});
				crmButton.append(cTag('i',{ 'class':`fa fa-bullhorn` }), ' ', 'CRM');
			buttonName.appendChild(crmButton);
				let customerButton = cTag('button', {class: "btn createButton"});
				customerButton.addEventListener('click', function (){AJget_CustomersPopup(0);});
				customerButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Customer'));
			buttonName.appendChild(customerButton);
		titleRow.appendChild(buttonName);
    showTableData.appendChild(titleRow);

		const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
			sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
				const filterType = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type"});
				filterType.addEventListener('change', filter_Customers_lists);
				setOptions(filterType, {'All':Translate('All Customers'), 'Archived':Translate('Archived Customers')}, 1, 0);
			sortDropDown.appendChild(filterType);
		filterRow.appendChild(sortDropDown);

			sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
				const selectSorting = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type"});
				selectSorting.addEventListener('change', filter_Customers_lists);
				setOptions(selectSorting, {'0':Translate('Company, First and Last Name'), '1':Translate('First Name'), '2':Translate('Last Name')}, 1, 0);
			sortDropDown.appendChild(selectSorting);
		filterRow.appendChild(sortDropDown);

			sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
				const selectCustomer = cTag('select', {class: "form-control", name: "scustomer_type", id: "scustomer_type"});
				selectCustomer.addEventListener('change', filter_Customers_lists);
					let allOption = cTag('option', {'value': "All"});
					allOption.innerHTML = Translate('All Types');
				selectCustomer.appendChild(allOption);
			sortDropDown.appendChild(selectCustomer);
		filterRow.appendChild(sortDropDown);

			const searchDiv = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
				const SearchInGroup = cTag('div', {class: "input-group"});
					const searchField = cTag('input', {'type': "text", 'placeholder': Translate('Search Customers'), id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
                    searchField.addEventListener('keydown',event=>{if(event.which===13) filter_Customers_lists()});
				SearchInGroup.appendChild(searchField);
					let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Customers')});
					searchSpan.addEventListener('click', filter_Customers_lists);
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
							const thCol0 = cTag('th', {'width': "0%"});
							thCol0.innerHTML=columnNames[0];

							const thCol1 = cTag('th', {'width': "25%"});
							thCol1.innerHTML=columnNames[1];

							const thCol2 = cTag('th', {'width': "25%"});
							thCol2.innerHTML= columnNames[2];
						listHeadRow.append(thCol0, thCol1, thCol2);
					listHead.appendChild(listHeadRow);
				listTable.appendChild(listHead);
					const listBody = cTag('tbody', {id: "tableRows"});
				listTable.appendChild(listBody);
			divNoMore.appendChild(listTable);
		divTableColumn.appendChild(divNoMore);
    showTableData.appendChild(divTableColumn);
    addPaginationRowFlex(showTableData);
    
    //======sessionStorage =======//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    const sorting_type = '0', scustomer_type = 'All';
   
    checkAndSetSessionData('sorting_type', sorting_type, list_filters);
    checkAndSetSessionData('scustomer_type', scustomer_type, list_filters);
    checkAndSetSessionData('sdata_type', scustomer_type, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

	addCustomeEventListener('filter',filter_Customers_lists);
	addCustomeEventListener('loadTable',loadTableRows_Customers_lists);
	filter_Customers_lists(true);
}

async function filter_Customers_view(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['customers_id'] = document.getElementById("table_idValue").value;
    const shistory_type = document.getElementById("shistory_type");
	jsonData['shistory_type'] = shistory_type.value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetHPage/filter';
	fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
		storeSessionData(jsonData);		
		document.getElementById("totalTableRows").value = data.totalRows;
		setTableHRows(data.tableRows, activityFieldAttributes);
		
		onClickPagination();
	}
}

async function loadTableRows_Customers_view(){
	const jsonData = {};
	jsonData['customers_id'] = document.getElementById("table_idValue").value;
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

async function AJ_view_moreInfo(){
	const customers_id = document.getElementById("table_idValue").value;
	const jsonData = {};
	jsonData['customers_id'] = customers_id;

    const url = '/'+segment1+'/AJ_view_moreInfo';
	fetchData(afterFetch,url,jsonData);
	
	function afterFetch(data){
		const viewBasicInfo = document.getElementById("viewBasicInfo");
		viewBasicInfo.innerHTML = '';
			if(data.company !==''){
				let companyHeader = cTag('h3');
				companyHeader.innerHTML = data.company;
				viewBasicInfo.appendChild(companyHeader);
			}
			let nameHeader = cTag('h3');
			nameHeader.innerHTML = data.name;
		viewBasicInfo.appendChild(nameHeader);

			let envelopeDiv = cTag('div', {'style': "margin-bottom: 10px;"});
				const envelopeIcon = cTag('i', {class: "fa fa-envelope-o", 'style': "font-size: 18px; margin-top: 6px;"});
			envelopeDiv.appendChild(envelopeIcon);
				let emailSpan = cTag('span', {'style': "padding-left: 15px; color: #969595; font-weight: bold;"});
				emailSpan.innerHTML = data.email+' ';
				if(data.email !==''){
					let sendEmailLink = cTag('a', {'href':"javascript:void(0);",'title': Translate('Send Email')});
					sendEmailLink.addEventListener('click', function (){contactToCustomer(data.company, data.email, 'Email');});
						const faEnvelopeIcon = cTag('i', {class: "fa fa-envelope", 'style': "font-weight: bold; font-size: 20px;"});
					sendEmailLink.appendChild(faEnvelopeIcon);
					emailSpan.appendChild(sendEmailLink);
				}
			envelopeDiv.appendChild(emailSpan);
		viewBasicInfo.appendChild(envelopeDiv);
			
			let phoneIconDiv = cTag('div', {'style': "margin-bottom: 10px;"});
				const phoneIcon = cTag('i', {class: "fa fa-phone", 'style': "font-size: 18px; margin-top: 6px;"});
			phoneIconDiv.appendChild(phoneIcon);
				let contactSpan = cTag('span', {'style': "padding-left: 15px; color: #969595; font-weight: bold;"});
				contactSpan.innerHTML = data.contact_no+' ';
				if(data.contact_no !==''){
					let sentSmsLink = cTag('a', {'href':"javascript:void(0);",'title': Translate('Click for sent sms')});
					if(data.bulkSMSCountryCode !==''){
						sentSmsLink.addEventListener('click', function (){contactToCustomer(data.company, data.smsContact_no, 'SMS');});                            
							const commentIcon = cTag('i', {class: "fa fa-comment", 'style': "font-weight: bold; font-size: 20px;"});
						sentSmsLink.appendChild(commentIcon);
					}
					contactSpan.appendChild(sentSmsLink);
				}
			phoneIconDiv.appendChild(contactSpan);
		viewBasicInfo.appendChild(phoneIconDiv);

		if(data.secondary_phone !==''){
			let phone2ndIconDiv = cTag('div', {'style': "margin-bottom: 10px;"});
				const phone2ndIcon = cTag('i', {class: "fa fa-phone", 'style': "font-size: 18px; margin-top: 6px;"});
			phone2ndIconDiv.appendChild(phone2ndIcon);
				let secondarySpan = cTag('span', {'style': "padding-left: 15px; color: #969595; font-weight: bold;"});
				secondarySpan.innerHTML = data.secondary_phone+' ';
					let clickSmsLink = cTag('a', {'href':"javascript:void(0);", title: Translate('Click for sent sms')});
					if(data.bulkSMSCountryCode !==''){
						clickSmsLink.addEventListener('click', function (){contactToCustomer(data.company, data.smsSecondary_phone, 'SMS');});
							const faCommentLink = cTag('i', {class: "fa fa-comment", 'style': "font-weight: bold; font-size: 20px;"});
						clickSmsLink.appendChild(faCommentLink);
					}
				secondarySpan.appendChild(clickSmsLink);
			phone2ndIconDiv.appendChild(secondarySpan);
			viewBasicInfo.appendChild(phone2ndIconDiv);
		}
		
			let mapIconDiv = cTag('div', {'style': "margin-bottom: 10px;"});
				const mapIcon = cTag('i', {class: "fa fa-map-marker", 'style': "font-size: 18px; margin-top: 6px;"});
			mapIconDiv.appendChild(mapIcon);
				let addressSpan = cTag('span', {'style': "padding-left: 15px; color: #969595; font-weight: bold;"});
				addressSpan.innerHTML = data.address+' ';
			mapIconDiv.appendChild(addressSpan);
		viewBasicInfo.appendChild(mapIconDiv);

		if(data.customers_publish>0){
			let viewButtons = cTag('div', {'style': "margin-bottom: 10px;"});
				let editButton = cTag('button', {class: "btn editButton", 'style': "margin-right: 15px;", title: Translate('Edit')});
				editButton.addEventListener('click', function (){AJget_CustomersPopup(customers_id);});
				editButton.innerHTML = Translate('Edit');
			viewButtons.appendChild(editButton);
			
				let mergeButton = cTag('button', {class: "btn defaultButton", 'style': "margin-right: 15px;", title: Translate('Merge Customers')});
				mergeButton.addEventListener('click', function (){AJmergeCustomersPopup(customers_id);});
				mergeButton.innerHTML = Translate('Merge Customers');
			viewButtons.appendChild(mergeButton);

				let archiveButton = cTag('button', {class: "btn archiveButton", title: Translate('Archive')});
				archiveButton.innerHTML = Translate('Archive');
				if(data.allowed.length===0||(!Array.isArray(data.allowed) && !data.allowed['4'].includes('cnac')) && parseInt(customers_id) !== data.default_customer){
					if(data.canUnArchive==0 || customers_id== data.default_customer){
						archiveButton.addEventListener('click', function (){noPermissionWarning(Translate('Customer'))});
					}
					else{
						archiveButton.addEventListener('click',()=>archiveCustomer(customers_id));
					}
				}
				else{
					archiveButton.addEventListener('click', function (){noPermissionWarning(Translate('Customer'))});
				}
			viewButtons.appendChild(archiveButton);
			viewBasicInfo.appendChild(viewButtons);
		}
		else{
			let unarchiveButton = cTag('div', {'style': "margin-bottom: 10px;"});
				let archiveButton = cTag('button', {class: "btn bgcoolblue", 'style': "margin-right: 15px;", title: Translate('Unarchive')});
				archiveButton.innerHTML = Translate('Unarchive');
				if(data.canUnArchive==0 ||  parseInt(customers_id) === data.default_customer){
					archiveButton.addEventListener('click', function (){noPermissionWarning(Translate('Customer'))});
				}
				else{
					archiveButton.addEventListener('click', ()=>unarchiveCustomer(customers_id));
				}					
			unarchiveButton.appendChild(archiveButton);
			viewBasicInfo.appendChild(unarchiveButton);
		}
		
		const viewCustomInfo = document.getElementById("viewCustomInfo");
		viewCustomInfo.innerHTML = '';
		if(Object.keys(data.viewCustomInfo).length>0){
			const viewTable = cTag('div', {class:"customInfoGrid", 'style': "padding: 5px;"});
			for(const [key, value] of Object.entries(data.viewCustomInfo)) {
					let extraInfo = cTag('label');
					extraInfo.innerHTML = key+' : ';
					let extraValue = cTag('span');
					extraValue.innerHTML = value;
				viewTable.append(extraInfo, extraValue);
			}
			viewCustomInfo.appendChild(viewTable);
		}

		const shistory_type = document.getElementById("shistory_type");
		const shistory_typeVal = shistory_type.value;
		shistory_type.innerHTML = '';
		const option = document.createElement('option');
		option.setAttribute('value', '');
		option.innerHTML = Translate('All Activities');
		shistory_type.appendChild(option);
		setOptions(shistory_type, data.actFeeTitOpt, 0, 1);
		document.getElementById("shistory_type").value = shistory_typeVal;

		filter_Customers_view();
	}
}

async function customerProperties(){
	const jsonData = {};
	jsonData['customers_id'] = document.getElementById("table_idValue").value;

    const url = '/'+segment1+'/customerProperties';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let propertyHeadRow, propertyBody, tdCol;
		if(Object.keys(data.tableRows).length>0){
			const tableData = data.tableRows;
			propertyBody = document.getElementById("customerProperties");
			propertyBody.innerHTML = '';
			//======Create TBody TR Column======//
			if(tableData.length){                    
				tableData.forEach(oneRow => {
					let i = 0;
					propertyHeadRow = cTag('tr');
					oneRow.forEach(tdvalue => {
						if(i>0){
							let idVal = oneRow[0];
							tdCol = cTag('td');
							const oneTDObj2 = propertiesFieldAttributes[i-1];
							for(const [key, value] of Object.entries(oneTDObj2)) {
								let attName = key;
								if(attName !=='' && attName==='datatitle')
									attName = attName.replace('datatitle', 'data-title');
								tdCol.setAttribute(attName, value);
							}
							tdCol.innerHTML = tdvalue||'&nbsp;';
							propertyHeadRow.appendChild(tdCol);
						}
						i++;
					});
						tdCol = cTag('td');
						const oneTDObj2 = propertiesFieldAttributes[i-1];
						for(const [key, value] of Object.entries(oneTDObj2)) {
							let attName = key;
							if(attName !=='' && attName==='datatitle')
								attName = attName.replace('datatitle', 'data-title');
							tdCol.setAttribute(attName, value);
						}
							const editIcon = cTag('i',{'class':'fa fa-edit cursor'});
							editIcon.addEventListener('click',()=>AJget_propertiesPopup(oneRow[0]))
						tdCol.append(editIcon);
						propertyHeadRow.appendChild(tdCol);
					propertyBody.appendChild(propertyHeadRow);
				});
			}
		}
		else{
			propertyBody = document.getElementById("customerProperties");
			propertyBody.innerHTML = '';
				propertyHeadRow = cTag('tr');
					tdCol = cTag('td', {'colspan': 5});
					tdCol.innerHTML = '';
				propertyHeadRow.appendChild(tdCol);
			propertyBody.appendChild(propertyHeadRow);
		}
	}

	return false;
}

function view(){
    let segment4 = 1;
    if(pathArray.length>4){segment4 = pathArray[4];}
    
    let customers_id = parseInt(segment3);
    if(customers_id==='' || isNaN(customers_id)){customers_id = 0;}

	let noMoreTables;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
		const titleRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 5px;"});
			const headerTitle = cTag('h2');
			headerTitle.innerHTML = Translate('Customer Information')+' ';
				const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays the information of customer')});
			headerTitle.appendChild(infoIcon);
		titleRow.appendChild(headerTitle);

			const customerListLink = cTag('a', {'href': "/Customers/lists", class: "btn defaultButton", title: Translate('Customers List')});
			customerListLink.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Customers List'));
		titleRow.appendChild(customerListLink);
    showTableData.appendChild(titleRow);

		const viewColumn = cTag('div', {class: "columnSM12"});
			const supplierHeader = cTag('header', {class: "imageContainer flexSpaBetRow", 'style': "padding-left: 5px"});
				const imageColumn = cTag('div', {class: "columnSM4 columnMD3"});
					const imageDiv = cTag('div', {class: "image"});
						const imageProfile = cTag('img', {class: "img-responsive", 'alt': Translate('My Profile'), 'src': "/assets/images/man.jpg"});
					imageDiv.appendChild(imageProfile);
				imageColumn.appendChild(imageDiv);
			supplierHeader.appendChild(imageColumn);

				const imageContentColumn = cTag('div', {class: "columnSM8 columnMD5", 'style': "border-right: 1px solid #CCC;"});
					let imageContent = cTag('div', {class: "image_content", 'style': "text-align: left;", id: "viewBasicInfo"});
				imageContentColumn.appendChild(imageContent);
			supplierHeader.appendChild(imageContentColumn);

				let customInfoColumn = cTag('div', {class: "columnMD4", 'align': "left", id: "viewCustomInfo"});
			supplierHeader.appendChild(customInfoColumn);
		viewColumn.appendChild(supplierHeader);
    showTableData.appendChild(viewColumn);

		const viewRow = cTag('div', {class: "flexSpaBetRow"});
			const viewTableColumn = cTag('div', {class: "columnXS12"});
				let hiddenProperties = {
						'note_forTable': 'customers',
						'table_idValue': customers_id,
						'customers_id': customers_id,
				}
			viewTableColumn.appendChild(historyTable(Translate('Customer Order History'),hiddenProperties));

				//=========Properties=========//
				const propertyWidget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
					const propertyWidgetHeader = cTag('div', {class: "cardHeader"});
						const propertyColumn = cTag('div', {class: "columnSM4", 'style': "margin: 0;"});
							const propertyHeader = cTag('h3');
							propertyHeader.innerHTML = Translate('Properties');
						propertyColumn.appendChild(propertyHeader);
					propertyWidgetHeader.appendChild(propertyColumn);
				propertyWidget.appendChild(propertyWidgetHeader);

					const propertiesContent = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
						const propertiesColumn = cTag('div', {class: "columnXS12", 'style': "margin: 0; padding: 0;"});
							noMoreTables = cTag('div', {id: "no-more-tables"});
								const propertiesTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing", 'style': "margin-top: 2px;"});
									const propertiesHead = cTag('thead', {class: "cf"});
										const column2Names = propertiesFieldAttributes.map(colObj=>(colObj.datatitle));
										const propertiesRow = cTag('tr');
											const th2Col0 = cTag('th', {'width': "20%"});
											th2Col0.innerHTML = column2Names[0];

											const th2Col1 = cTag('th', {'width': "20%"});
											th2Col1.innerHTML = column2Names[1];

											const th2Col2 = cTag('th', {'width': "20%"});
											th2Col2.innerHTML = column2Names[2];

											const th2Col3 = cTag('th');
											th2Col3.innerHTML = column2Names[3];

											const th2Col4 = cTag('th', {'style': "width: 80px;"});
											th2Col4.innerHTML = column2Names[4];
										propertiesRow.append(th2Col0, th2Col1, th2Col2, th2Col3, th2Col4);                                        
									propertiesHead.appendChild(propertiesRow);
								propertiesTable.appendChild(propertiesHead);

									const propertiesBody = cTag('tbody', {id: "customerProperties"});
								propertiesTable.appendChild(propertiesBody);
							noMoreTables.appendChild(propertiesTable);
						propertiesColumn.appendChild(noMoreTables);
					propertiesContent.appendChild(propertiesColumn);
				propertyWidget.appendChild(propertiesContent);
			viewTableColumn.appendChild(propertyWidget);
		viewRow.appendChild(viewTableColumn);
    showTableData.appendChild(viewRow);
    
	addCustomeEventListener('filter',filter_Customers_view);
	addCustomeEventListener('loadTable',loadTableRows_Customers_view);
    AJ_view_moreInfo();
	customerProperties();

    //======sessionStorage =======//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null){
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    let shistory_type = '';
    checkAndSetSessionData('shistory_type', shistory_type, list_filters);
}

async function AJmergeCustomersPopup(customers_id){
    const jsonData = {};
	jsonData['customers_id'] = customers_id;
    
	if(customers_id>0){
        const url = '/'+segment1+'/AJget_CustomersPopup';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			const formDialog = cTag('div');
			formDialog.innerHTML = '';
				const errorMsg = cTag('div', {id: "error_customer", class: "errormsg"});
			formDialog.appendChild(errorMsg);

				let inputField;
				const mergeCustomerForm = cTag('form', {'action': "#", name: "frmMergeCustomer", id: "frmMergeCustomer", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
					const mergeThisTextTitle = cTag('h4', {class:'borderbottom', 'style': "font-weight: bold;"});
					mergeThisTextTitle.innerHTML = Translate('Merge this customer information');
				mergeCustomerForm.appendChild(mergeThisTextTitle);

					const mergeCustomerColumn = cTag('div', {class: "customInfoGrid columnSM12", 'style': "text-align: left;"});
						const nameTitle = cTag('label');
						nameTitle.innerHTML = Translate('Name')+': ';
						let nameSpan = cTag('span', {'style': "color: #969595;"});
						nameSpan.innerHTML = data.first_name+' '+data.last_name;
					mergeCustomerColumn.append(nameTitle, nameSpan);

						const phoneTitle = cTag('label');
						phoneTitle.innerHTML = Translate('Phone No.')+': ';
						let phoneSpan = cTag('span', {'style': "color: #969595;"});
						phoneSpan.innerHTML = data.contact_no;
					mergeCustomerColumn.append(phoneTitle, phoneSpan);

						const emailTitle = cTag('label');
						emailTitle.innerHTML = Translate('Email')+': ';
						let emailSpan = cTag('span', {'style': "color: #969595;"});
						emailSpan.innerHTML = data.email;
					mergeCustomerColumn.append(emailTitle, emailSpan);

						const companyTitle = cTag('label');
						companyTitle.innerHTML = Translate('Company')+': ';
						let companySpan = cTag('span', {'style': "color: #969595;"});
						companySpan.innerHTML = data.company;
					mergeCustomerColumn.append(companyTitle, companySpan);
				mergeCustomerForm.appendChild(mergeCustomerColumn);

					const toThisTitle = cTag('h4', {class:'borderbottom', 'style': "font-weight: bold;"});
					toThisTitle.innerHTML = Translate('To this customer');
				mergeCustomerForm.appendChild(toThisTitle);

					const customerNameRow = cTag('div', {class: "flex", 'style': "align-items: center;"});
						const customerNameColumn = cTag('div', {class: "columnSM2", 'align': "left"});
							const nameLabel = cTag('label', {'for': "customer_name"});
							nameLabel.innerHTML = Translate('Name');
								let requiredField = cTag('span', {class: "required"});
								requiredField.innerHTML = '*';
							nameLabel.appendChild(requiredField);
						customerNameColumn.appendChild(nameLabel);
					customerNameRow.appendChild(customerNameColumn);

						const nameSearchColumn = cTag('div', {class: "columnSM10"});
							inputField = cTag('input', {"maxlength": 50, 'type': "text", 'value': "", 'required': true, name: "customer_name", id: "customer_name", class: "form-control", 'placeholder': Translate('Search Customers')});
						nameSearchColumn.appendChild(inputField);
					customerNameRow.appendChild(nameSearchColumn);
				mergeCustomerForm.appendChild(customerNameRow);

					const customerInfoRow = cTag('div', {class: "flexSpaBetRow"});
						const customerInfoColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;", id: "toCustomerInfo"});
					customerInfoRow.appendChild(customerInfoColumn);
				mergeCustomerForm.appendChild(customerInfoRow);

					inputField = cTag('input', {'type': "hidden", name: "fromcustomers_id", id: "fromcustomers_id", 'value': customers_id});
				mergeCustomerForm.appendChild(inputField);
					inputField = cTag('input', {'type': "hidden", name: "tocustomers_id", id: "tocustomers_id", 'value': 0});
				mergeCustomerForm.appendChild(inputField);
			formDialog.appendChild(mergeCustomerForm);

			popup_dialog600(Translate('Merge the following two customers'), formDialog,Translate('Merge Customers'), AJmergeCustomers);
			document.querySelectorAll('.popup_footer_button')[1].style.display = 'none';//hide Merge initially
			// setTimeout(function() {		
			document.getElementById("customer_name").focus();
			if(document.getElementById("customer_name")){AJautoComplete('customer_name');}
			// }, 500);
		}

        return true;
    }
}

async function AJmergeCustomers(hidePopup){
	const error_customer = document.getElementById('error_customer');
    error_customer.innerHTML = '';
	if(parseInt(document.getElementById("tocustomers_id").value) ===0){
		showTopMessage('alert_msg','Merging customers is empty. Please search and choose different customer');            
        return false;
	}
	if(document.getElementById("fromcustomers_id").value === document.getElementById("tocustomers_id").value){
		showTopMessage('alert_msg','Merging customers is same. Please choose different customer.');            
        return false;
	}
    actionBtnClick('.btnmodel', Translate('Merging Customers'), 1);
	
    const jsonData = serialize('#frmMergeCustomer');
    const url = '/'+segment1+'/AJmergeCustomers';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg ==='Success' && data.id>0){
			hidePopup();
			window.location = '/Customers/view/'+data.id;
		}
		else{
            actionBtnClick('.btnmodel', Translate('Merge Customers'), 0);
            showTopMessage('alert_msg', Translate('There is an error while merging information.'));             
		}
	}

	return false;
}

function contactToCustomer(company_name, contact_no, sendType){
    const jsonData = {};
	jsonData['company_name'] = company_name;
	jsonData['contact_no'] = contact_no;
	jsonData['sendType'] = sendType;

    let smstophoneLabel, popUpTitle, maxMsg, inputField, requiredField;
	smstophoneLabel = Translate('To Phone');
	popUpTitle = Translate('Send SMS');
	maxMsg = 159;
    
    const formDialog = cTag('div');
    formDialog.innerHTML = '';
        const smsForm = cTag('form', {'action': "#", name: "frmsmsform", id: "frmsmsform", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
        smsForm.addEventListener('submit', sendMessage);
            const errormsg = cTag('div', {id: "error_msg", class: "errormsg"});
		smsForm.appendChild(errormsg);
    
            if(sendType==='Email'){
                smstophoneLabel = Translate('Email Address');
                popUpTitle = Translate('Send Email');
                    const formNameRow = cTag('div', {class: "flexColumn", 'style': "padding-bottom: 10px;"});
						const formNameLabel = cTag('label', {'for': "smsfromname", 'style': "text-align: start;"});
                        formNameLabel.innerHTML = Translate('From Name');
					formNameRow.appendChild(formNameLabel);
                        inputField = cTag('input', {name: "smsfromname", id: "smsfromname", class: "form-control smsForm", 'value': company_name, 'type': "text", 'size': 50, 'maxlength': 50 });
					formNameRow.appendChild(inputField);
				smsForm.appendChild(formNameRow);
            }
    
            const smsRow = cTag('div', {class: "flexColumn", 'style': "padding-bottom: 10px;"});
				const smsLabel = cTag('label', {'for': "smstophone", 'style': "text-align: start;"});
                smsLabel.innerHTML = smstophoneLabel;
                    requiredField = cTag('span', {class: "required"});
                    requiredField.innerHTML = '*';
				smsLabel.appendChild(requiredField);
			smsRow.appendChild(smsLabel);
                inputField = cTag('input', {'readonly': true, 'required': true, name: "smstophone", id: "smstophone", class: "form-control smsForm", 'value': contact_no, 'type': "text", 'size': 50, 'maxlength': 50});
			smsRow.appendChild(inputField);
		smsForm.appendChild(smsRow);

        if(sendType==='Email'){
            const emailRow = cTag('div', {class: "flexColumn", 'style': "padding-bottom: 10px;"});
				const emailLabel = cTag('label', {'for': "subject", 'style': "text-align: start;"});
                emailLabel.innerHTML = Translate('Subject');
			emailRow.appendChild(emailLabel);
                inputField = cTag('input', {'required': true, name: "subject", id: "subject", class: "form-control", 'value': "", 'type': "text", 'size': 200, 'maxlength': 200});
			emailRow.appendChild(inputField);
            maxMsg = 1000;
            smsForm.appendChild(emailRow);
        }

            const messageRow = cTag('div', {class: "flexColumn", 'style': "padding-bottom: 10px;"});
				const messageLabel = cTag('label', {'for': "smsmessage", 'style': "text-align: start;"});
                messageLabel.innerHTML = Translate('Message');
                    requiredField = cTag('span', {class: "required"});
                    requiredField.innerHTML = '*';
				messageLabel.appendChild(requiredField);
			messageRow.appendChild(messageLabel);
                const textarea = cTag('textarea', {'required': true, name: "smsmessage", id: "smsmessage", 'rows': 4, class: "form-control smsForm", 'maxlength': maxMsg});
			messageRow.appendChild(textarea);
		smsForm.appendChild(messageRow);

            inputField = cTag('input', {'type': "hidden", name: "sendType", id: "sendType", 'value': sendType});
		smsForm.appendChild(inputField);
	formDialog.appendChild(smsForm);		
        
        popup_dialog600(popUpTitle, formDialog, Translate('Send'), sendMessage);

	// setTimeout(function() {
	if(sendType==='Email'){
		document.getElementById("subject").focus();
	}
	else{
		document.getElementById("smsmessage").focus();
	}
	document.getElementById("smsmessage").addEventListener('keyup', checkContactForm);
	checkContactForm();
	// }, 500);
}

function checkContactForm(){
	let returnval = true;
	let sendType = document.getElementById("sendType").value;
	if(sendType==='Email'){
		let smsfromname = document.getElementById("smsfromname").value;
		if(smsfromname===''){returnval = false;}
	}
	
	let smstophone = document.getElementById("smstophone");
	if(smstophone.value===''){returnval = false;}
	else{
		if(document.getElementById("sendType").value==='Email'){
			returnval = emailcheck(smstophone.value);
		}
		else{
            checkPhone("smstophone", 1);
		}
	}
	
	let smsmessage = document.getElementById("smsmessage").value;
	if(smsmessage===''){
		returnval = false;
	}

	let btnmodel = document.querySelector(".btnmodel");        
	if(returnval === false){
		btnmodel.classList.add('is-disabled');
        btnmodel.disabled = true;
		return false;
	}
	else{
		btnmodel.classList.remove('is-disabled');
        btnmodel.disabled = false;
		return true;
	}
}

async function sendMessage(hidePopup){
	if(checkContactForm()===false){
		return false;
	}
	else{
        actionBtnClick('.btnmodel', Translate('Sending'), 1);
        
		let url = '/BulkSMS/sendSMS';
		let successMsg = Translate('Your sms has been sent successfully');
		if(document.getElementById("sendType").value==='Email'){
			url = '/Customers/sendEmail';
			successMsg = Translate('Email sent successfully');
		}
		const jsonData = serialize('#frmsmsform');
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.returnStr==='sent'){
                hidePopup();
                showTopMessage('success_msg', successMsg);
			}
			else{
                document.getElementById('error_msg').innerHTML = data.returnStr;
                actionBtnClick('.btnmodel', Translate('Send'), 0);
			}
		}

		return false;
	}
}

//===================CRM=====================
function crm(){
    let dateRangeField, requireField;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow", 'style': "padding: 5px;"});
			const headerTitle = cTag('h2');
			headerTitle.innerHTML = Translate('Manage CRM')+' ';
			headerTitle.appendChild(cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", 'data-original-title':Translate('This page displays the list of your customers')}));
        titleRow.appendChild(headerTitle);
			const aTag = cTag('a', {'href': "/Customers/lists", class: "btn defaultButton", title: Translate('Customers List')});
			aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Customers List'));
        titleRow.appendChild(aTag);
    showTableData.appendChild(titleRow);
    
        //======Second MainDiv======//
		const crmForm = cTag('form',{'name':"frmcrm", 'style': 'flex-grow: 1;', 'id':"frmcrm", 'action':"#", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset':"utf-8"});
		crmForm.addEventListener('submit', checkCRM);
			const spaBetRow = cTag('div', {class:"flexSpaBetRow"});
				const selectCustomerRow = cTag('div',{class: 'columnXS12 columnSM6 columnMD4'});
					const selectCustomerFlex = cTag('div',{class: "cardContainer flexColumn", 'style': "height: 100%;"});
						const widgetHeaderName = cTag('div',{class: "cardHeader"});
							const headerTitle3 = cTag('h3');
							headerTitle3.innerHTML = Translate('Select Customers to send to');
						widgetHeaderName.appendChild(headerTitle3);
					selectCustomerFlex.appendChild(widgetHeaderName);

						const customerContent = cTag('div', {class: "cardContent", 'style': "flex-grow: 1;"});
							const customerContentFlex = cTag('div',{class: "flexColumn"});
								const dateRangeName = cTag('div', {class: "columnXS12", 'style': "padding: 5px 15px;"});
									const dateRangeLabel = cTag('label', {'for':"date_range", id :"lbdate_range"});
									dateRangeLabel.innerHTML = Translate('Date Added Range');
								dateRangeName.appendChild(dateRangeLabel);
									let dateRangeContainer = cTag('div',{class:'daterangeContainer'});
										dateRangeField = cTag('input', {'type': "text",'name': "date_range", id:"date_range", class: "form-control", 'style': "padding-left: 35px;"});
										daterange_picker_dialog(dateRangeField,{cancel:(node)=>{node.value=''}},true);
										dateRangeField.value = '';
									dateRangeContainer.appendChild(dateRangeField);
								dateRangeName.appendChild(dateRangeContainer);
							customerContentFlex.appendChild(dateRangeName);

								const lastRangeName = cTag('div',{class: "columnXS12", 'style': "padding: 5px 15px;"});
									const lastRangeLabel = cTag('label', {'for': "date_invoiced_range"});
									lastRangeLabel.innerHTML = Translate('Last Purchased Range');
								lastRangeName.appendChild(lastRangeLabel);
									dateRangeContainer = cTag('div',{class:'daterangeContainer'});
										dateRangeField = cTag('input',{'type': "text",name: "date_invoiced_range", id: "date_invoiced_range", class: "form-control", 'style': "padding-left: 35px;"});
										daterange_picker_dialog(dateRangeField,{cancel:(node)=>{node.value=''}},true);
										dateRangeField.value = '';
									dateRangeContainer.appendChild(dateRangeField);
								lastRangeName.appendChild(dateRangeContainer);
							customerContentFlex.appendChild(lastRangeName);

								const customerTypeName = cTag('div',{class: "columnXS12", 'style': "padding: 5px 15px;"});
									const customerTypeLabel = cTag('label',{'for': "customer_type"});
									customerTypeLabel.innerHTML = Translate('Customer Type');
								customerTypeName.appendChild(customerTypeLabel);
									const selectCustomerType = cTag('select',{name: "customer_type", id: "customer_type",class: "form-control"});
										const customerTypeOption = cTag('option',{'value': "All"});
										customerTypeOption.innerHTML = Translate('All Customer Types');
									selectCustomerType.appendChild(customerTypeOption);
								customerTypeName.appendChild(selectCustomerType);
							customerContentFlex.appendChild(customerTypeName);
						customerContent.appendChild(customerContentFlex);
					selectCustomerFlex.appendChild(customerContent);
				selectCustomerRow.appendChild(selectCustomerFlex);
			spaBetRow.appendChild(selectCustomerRow);

				const mailInfoRow = cTag('div',{class: "columnXS12 columnSM6 columnMD8"});
					const mailInfoFlex = cTag('div',{class: "cardContainer", 'style': "height: 100%;"});
						const mailInfoHeader = cTag('div',{class: "cardHeader"});
							const mailInfoTitle = cTag('h3');
							mailInfoTitle.innerHTML = Translate('Mail Information');
						mailInfoHeader.appendChild(mailInfoTitle);
					mailInfoFlex.appendChild(mailInfoHeader);

						const mailInfoContent = cTag('div',{class: "cardContent"});
							const mailInfoContentFlex = cTag('div',{class: "flexColumn"});
								const subjectName = cTag('div',{class: "columnXS12 cursor", 'style': "padding: 5px 15px;"});
									const subjectLabel = cTag('label',{for: "subject"});
									subjectLabel.innerHTML = Translate('Subject');
										requireField = cTag('span',{class: "required"});
										requireField.innerHTML = ' *';
									subjectLabel.appendChild(requireField);
								subjectName.appendChild(subjectLabel);
									const subjectField = cTag('input',{type: "text",'required': "",'minlength': 2,class: "form-control",name: "subject",id: "subject"});
								subjectName.appendChild(subjectField);
							mailInfoContentFlex.appendChild(subjectName);

								const mailBodyName = cTag('div',{class: "columnXS12 cursor", 'style': "padding: 5px 15px;"});
									const mailBodyLabel = cTag('label',{'for': "mailbody"});
									mailBodyLabel.innerHTML = Translate('Mail Body');
										requireField = cTag('span',{class: "required"});
										requireField.innerHTML = ' *';
									mailBodyLabel.appendChild(requireField);
								mailBodyName.appendChild(mailBodyLabel);
									const textBox = cTag('textarea',{'required': "", 'minlength': 10, 'rows': 5, 'cols': 50, class: "form-control", name: "mailbody", id: "mailbody"});
								mailBodyName.appendChild(textBox);
							mailInfoContentFlex.appendChild(mailBodyName);

								const submitButton = cTag('div',{class: "columnXS12", 'style': "text-align: end;"});
									let sendButton = cTag('input',{'type': "submit", name: "submitbtn", 'value': "Send Email", class: "btn completeButton"});
								submitButton.appendChild(sendButton);
							mailInfoContentFlex.appendChild(submitButton);
						mailInfoContent.appendChild(mailInfoContentFlex);
							let errorSpan = cTag('span',{class: "errormsg", id: "error_mailbody"});
						mailInfoContent.appendChild(errorSpan);
					mailInfoFlex.appendChild(mailInfoContent);
				mailInfoRow.appendChild(mailInfoFlex);
			spaBetRow.appendChild(mailInfoRow);
		crmForm.appendChild(spaBetRow);
    showTableData.appendChild(crmForm);
            
    AJ_crm_MoreInfo();
}

async function AJ_crm_MoreInfo(){
	const jsonData = {};	
    const url = '/'+segment1+'/AJ_crm_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		const customer_type= document.getElementById('customer_type');
		customer_type.innerHTML = '';
		const option = document.createElement('option');
		option.setAttribute('value', 'All');
		option.innerHTML = Translate('All Customer Types');
		customer_type.appendChild(option);
		setOptions(customer_type, data.cusTypeOpt, 0, 0);
	}
}

async function checkCRM(event){
    if(event){ event.preventDefault();}
		
	const jsonData = serialize('#frmcrm');
    const url = '/'+segment1+'/checkCRM';
	fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
		if(data.returnStr>0){
			if(data.returnStr>100){
				alert_dialog(Translate('Alert message'), Translate('Sorry, can only send up to 100 at a time.'), Translate('Ok'));
			}
			else{
				confirm_dialog(Translate('Confirm mail'), Translate('Are you sure want to sent this message to')+" "+data.returnStr+" "+Translate('Customers'), crmConfirm);
			}
		}
		else{
			alert_dialog(Translate('Alert message'), Translate('No customers meet the criteria given'), Translate('Ok'));
		}
	}

	return false;
}

async function crmConfirm(hidePopup){
    const sendBtn = document.querySelector('.archive');
	sendBtn.innerHTML = Translate('Sending')+'...';
	sendBtn.disabled = true;
	
    const jsonData = serialize('#frmcrm');
    const url = '/'+segment1+'/sendCRM';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		hidePopup();
		document.getElementById("mailbody").value = '';
		if(data.returnStr==='noCustomer') showTopMessage('alert_msg', Translate('No customers meet the criteria given'));
		else if(data.returnStr==='messageSent') showTopMessage('success_msg', `${Translate('Your message has been sent to')} ${data.count} ${Translate('Customers')}.`);
		else if(data.returnStr==='messageNotSent') showTopMessage('alert_msg', `${Translate('Your message has not been sent to')} ${data.count} ${Translate('Customers')}`);
	}
}

//=======Customers Module common-functions=======//
export async function AJget_CustomersPopup(customers_id,POScbf){
    const jsonData = {};
	jsonData['customers_id'] = customers_id;

    const url = '/'+segment1+'/AJget_CustomersPopup';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let aTag, textarea;
		const formDialog = cTag('div');
		formDialog.innerHTML = '';           
			let divTabs = cTag('div',{ 'id': 'tabs', 'style': "max-height: 600px;" });
				let ulTabs = cTag('ul',{'style':'flex-wrap:wrap; gap:5px;'});
					let liTabs1 = cTag('li',{'style': 'flex-grow: 1;'});
						aTag = cTag('a',{ 'href': '#tabs-1' });
						aTag.innerHTML = Translate('Basic Info');
					liTabs1.appendChild(aTag);
				ulTabs.appendChild(liTabs1);

					let liTabs2 = cTag('li',{'style': 'flex-grow: 1;'});
						aTag = cTag('a',{ 'href': '#tabs-2' });
						aTag.innerHTML = Translate('Address Info');
					liTabs2.appendChild(aTag);
				ulTabs.appendChild(liTabs2);

					let liTabs3 = cTag('li',{'style': 'flex-grow: 1;'});
						aTag = cTag('a',{ 'href': '#tabs-3' });
						aTag.innerHTML = Translate('Alert message');
					liTabs3.appendChild(aTag);
				ulTabs.appendChild(liTabs3);
				if(data.customFields>0){
					let liTabs4 = cTag('li',{'style': 'flex-grow: 1;'});
						aTag = cTag('a',{ 'href': '#tabs-4' });
						aTag.innerHTML = Translate('Custom Fields');
						liTabs4.appendChild(aTag);
					ulTabs.appendChild(liTabs4);
				}
			divTabs.appendChild(ulTabs);

			let customerForm = cTag('form',{ 'action': '#','name': 'frmcustomer','id': 'frmcustomer','enctype': 'multipart/form-data','method': 'post','accept-charset': 'utf-8' });
				let customerFormColumn = cTag('div',{ 'class': 'columnXS12','id': 'tabs-1', 'align': 'left'  });
					const firstNameRow = cTag('div',{ 'class': 'flex'});
						const firstNameColumn = cTag('div',{ 'class': 'columnSM4' });
							const firstNameLabel = cTag('label',{ 'for': 'first_name' });
							firstNameLabel.innerHTML = Translate('First Name');
								let requiredField = cTag('span',{ 'class': 'required' });
								requiredField.innerHTML = '*';
							firstNameLabel.appendChild(requiredField);
						firstNameColumn.appendChild(firstNameLabel);
					firstNameRow.appendChild(firstNameColumn);
						const firstNameField = cTag('div',{ 'class': 'columnSM8' });
						firstNameField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'first_name','id': 'first_name','value': data.first_name,'maxlength': '17' }));
						firstNameField.appendChild(cTag('div',{ 'id': 'error_customer','class': 'errormsg' }));
					firstNameRow.appendChild(firstNameField);
				customerFormColumn.appendChild(firstNameRow);

					// Last Name
					const lastNameRow = cTag('div',{ 'class': 'flex'});
						const lastNameColumn = cTag('div',{ 'class': 'columnSM4'});
							const lastNameLabel = cTag('label',{ 'for': 'last_name' });
							lastNameLabel.innerHTML = Translate('Last Name');
						lastNameColumn.appendChild(lastNameLabel);
					lastNameRow.appendChild(lastNameColumn);
						const lastNameField = cTag('div',{ 'class': 'columnSM8'});
						lastNameField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'last_name','id': 'last_name','value': data.last_name,'maxlength': '17' }));
					lastNameRow.appendChild(lastNameField);
				customerFormColumn.appendChild(lastNameRow);

					//Email Address
					const emailRow = cTag('div',{ 'class': 'flex'});
						const emailColumn = cTag('div',{ 'class': 'columnSM4'});
							const emailLabel = cTag('label',{ 'for': 'email' });
							emailLabel.innerHTML = Translate('Email Address');
						emailColumn.appendChild(emailLabel);
					emailRow.appendChild(emailColumn);
						const emailField = cTag('div',{ 'class': 'columnSM8'});
						emailField.appendChild(cTag('input',{ 'type': 'email','class': 'form-control','name': 'email','id': 'email','value': data.email,'maxlength': '50' }));
						emailField.appendChild(cTag('div',{ 'id': 'error_email','class': 'errormsg' }));
					emailRow.appendChild(emailField);
				customerFormColumn.appendChild(emailRow);

					//offers Email
					const offerMailRow = cTag('div',{ 'class': 'flex'});
						const offerMailColumn = cTag('div',{ 'class': 'columnXS6 columnSM4'});
							const offerMailLabel = cTag('label',{ 'for': 'offers_email' });
							offerMailLabel.innerHTML = Translate('Offers Email');
						offerMailColumn.appendChild(offerMailLabel);
					offerMailRow.appendChild(offerMailColumn);
						const offerMailBox = cTag('div',{ 'class': 'columnXS6 columnSM8'});
							let inputBox = cTag('input',{ 'type': 'checkbox','name': 'offers_email','id': 'offers_email','value':1 });
							if(data.offers_email>0){
								inputBox.setAttribute('checked',true);
							}
						offerMailBox.appendChild(inputBox);
					offerMailRow.appendChild(offerMailBox);
				customerFormColumn.appendChild(offerMailRow);

					//Company
					const companyRow = cTag('div',{ 'class': 'flex'});
						const companyColumn = cTag('div',{ 'class': 'columnSM4'});
							const companyLabel = cTag('label',{ 'for': 'company' });
							companyLabel.innerHTML = Translate('Company');
						companyColumn.appendChild(companyLabel);
					companyRow.appendChild(companyColumn);
						const companyField = cTag('div',{ 'class': 'columnSM8'});
						companyField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'company','id': 'company','value':data.company,'maxlength':'35' }));
					companyRow.appendChild(companyField);
				customerFormColumn.appendChild(companyRow);

					//Phone number
					const phoneRow = cTag('div',{ 'class': 'flex'});
						const phoneColumn = cTag('div',{ 'class': 'columnSM4'});
							const phoneLabel = cTag('label',{ 'for': 'contact_no' });
							phoneLabel.innerHTML = Translate('Phone No.');
								requiredField = cTag('span',{ 'class': 'required' });
								requiredField.innerHTML = '*';
							phoneLabel.appendChild(requiredField);
						phoneColumn.appendChild(phoneLabel);
					phoneRow.appendChild(phoneColumn);
						const phoneField = cTag('div',{ 'class': 'columnSM8'});
						phoneField.appendChild(cTag('input',{ 'type': 'tel','class': 'form-control','name': 'contact_no','id': 'contact_no','value':data.contact_no,'maxlength':'20' }));
						phoneField.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errorContact_no' }));
					phoneRow.appendChild(phoneField);
				customerFormColumn.appendChild(phoneRow);

					//Secondary Phone
					const secondaryPhoneRow = cTag('div',{ 'class': 'flex'});
						const secondaryPhoneColumn = cTag('div',{ 'class': 'columnSM4'});
							const secondaryPhoneLabel = cTag('label',{ 'for': 'secondary_phone' });
							secondaryPhoneLabel.innerHTML = Translate('Secondary Phone');
						secondaryPhoneColumn.appendChild(secondaryPhoneLabel);
					secondaryPhoneRow.appendChild(secondaryPhoneColumn);
						const secondaryPhoneField = cTag('div',{ 'class': 'columnSM8'});
						secondaryPhoneField.appendChild(cTag('input',{ 'type': 'tel','class': 'form-control','name': 'secondary_phone','id': 'secondary_phone','value':data.secondary_phone,'maxlength':'20' }));
						secondaryPhoneField.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errorSecondary_phone' }));
					secondaryPhoneRow.appendChild(secondaryPhoneField);
				customerFormColumn.appendChild(secondaryPhoneRow);

					// fax
					const faxRow = cTag('div',{ 'class': 'flex'});
						const faxColumn = cTag('div',{ 'class': 'columnSM4'});
							const faxLabel = cTag('label',{ 'for': 'fax' });
							faxLabel.innerHTML = Translate('Fax');
						faxColumn.appendChild(faxLabel);
					faxRow.appendChild(faxColumn);
						const faxField = cTag('div',{ 'class': 'columnSM8'});
						faxField.appendChild(cTag('input',{ 'type': 'tel','class': 'form-control','name': 'fax','id': 'fax','value':data.fax,'maxlength':'20' }));
					faxRow.appendChild(faxField);
				customerFormColumn.appendChild(faxRow);

					//Customers Type
					const customerTypeRow = cTag('div',{ 'class': 'flex'});
						const customerTypeColumn = cTag('div',{ 'class': 'columnSM4'});
							const customerTypeLabel = cTag('label',{ 'for': 'customer_type' });
							customerTypeLabel.innerHTML = Translate('Customer Type');
						customerTypeColumn.appendChild(customerTypeLabel);
					customerTypeRow.appendChild(customerTypeColumn);
						const customerTypeDropDown = cTag('div',{ 'class': 'columnSM8'});
							let customerInGroup = cTag('div',{ 'class': 'input-group' });
								let selectCustomerType = cTag('select',{ 'class':'form-control','id':'customer_type','name':'customer_type' });
									const customerTypeOption = cTag('option',{ 'value': '' });
									customerTypeOption.innerHTML = Translate('Select Customer Type');
								selectCustomerType.appendChild(customerTypeOption);
								setOptions(selectCustomerType, data.custTypeOpts, 0, 1); 
								selectCustomerType.value = data.customer_type;
							customerInGroup.appendChild(selectCustomerType);
							customerInGroup.appendChild(cTag('input',{ 'type': 'text','value':'','maxlength':'20','name': 'customer_type_name','id': 'customer_type_name','class': 'form-control', 'style': "display: none;"}));
								let addCustomerSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add New Customer Type'),'class': 'input-group-addon cursor showNewInputOrSelect' });
								addCustomerSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
							customerInGroup.appendChild(addCustomerSpan);
						customerTypeDropDown.appendChild(customerInGroup);   
					customerTypeRow.appendChild(customerTypeDropDown);
				customerFormColumn.appendChild(customerTypeRow);  
			customerForm.appendChild(customerFormColumn);

				//Tabs 2
				let customerForm2Column = cTag('div',{ 'class': 'columnXS12','id': 'tabs-2', 'align': 'left', 'style': "display: none;" });
					const addressRow = cTag('div',{ 'class': 'flex' });
						const addressColumn = cTag('div',{ 'class': 'columnSM4'});
							const addressLabel = cTag('label',{ 'for': 'shipping_address_one' });
							addressLabel.innerHTML = Translate('Address Line 1');
						addressColumn.appendChild(addressLabel);
					addressRow.appendChild(addressColumn);  
						const addressField = cTag('div',{ 'class': 'columnSM8'});
						addressField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'shipping_address_one','id': 'shipping_address_one','value':data.shipping_address_one,'maxlength':'35' }));
					addressRow.appendChild(addressField);
				customerForm2Column.appendChild(addressRow);

					//Shipping Address 2
					const shippingAddressRow = cTag('div',{ 'class': 'flex'});
						const shippingAddressColumn = cTag('div',{ 'class': 'columnSM4'});
							const shippingAddressLabel = cTag('label',{ 'for': 'shipping_address_two' });
							shippingAddressLabel.innerHTML = Translate('Address Line 2');
						shippingAddressColumn.appendChild(shippingAddressLabel);  
					shippingAddressRow.appendChild(shippingAddressColumn);
						const shippingAddressField = cTag('div',{ 'class': 'columnSM8'});
						shippingAddressField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'shipping_address_two','id': 'shipping_address_two','value':data.shipping_address_two,'maxlength':'35' }));
					shippingAddressRow.appendChild(shippingAddressField);
				customerForm2Column.appendChild(shippingAddressRow);

					//City/Town
					const cityRow = cTag('div',{ 'class': 'flex'});
						const cityColumn = cTag('div',{ 'class': 'columnSM4'});
							const cityLabel = cTag('label',{ 'for': 'shipping_city' });
							cityLabel.innerHTML = Translate('City / Town');
						cityColumn.appendChild(cityLabel);
					cityRow.appendChild(cityColumn);  
						const cityField = cTag('div',{ 'class': 'columnSM8'});
						cityField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'shipping_city','id': 'shipping_city','value':data.shipping_city,'maxlength':'30' }));
					cityRow.appendChild(cityField);
				customerForm2Column.appendChild(cityRow);

					//State/Province
					const stateRow = cTag('div',{ 'class': 'flex'});
						const stateColumn = cTag('div',{ 'class': 'columnSM4'});
							const stateLabel = cTag('label',{ 'for': 'shipping_state' });
							stateLabel.innerHTML = Translate('State / Province');
						stateColumn.appendChild(stateLabel);
					stateRow.appendChild(stateColumn);  
						const stateField = cTag('div',{ 'class': 'columnSM8'});
						stateField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'shipping_state','id': 'shipping_state','value':data.shipping_state,'maxlength':'20' }));
					stateRow.appendChild(stateField);
				customerForm2Column.appendChild(stateRow);

					//Zip
					const zipRow = cTag('div',{ 'class': 'flex'});
						const zipColumn = cTag('div',{ 'class': 'columnSM4'});
							const zipLabel = cTag('label',{ 'for': 'shipping_zip' });
							zipLabel.innerHTML = Translate('Zip/Postal Code');
						zipColumn.appendChild(zipLabel);
					zipRow.appendChild(zipColumn);
						const zipField = cTag('div',{ 'class': 'columnSM8'});
						zipField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'shipping_zip','id': 'shipping_zip','value':data.shipping_zip,'maxlength':'9' }));
					zipRow.appendChild(zipField);
				customerForm2Column.appendChild(zipRow);

					//Country
					const countryRow = cTag('div',{ 'class': 'flex'});
						const countryColumn = cTag('div',{ 'class': 'columnSM4'});
							const countryLabel = cTag('label',{ 'for': 'shipping_country' });
							countryLabel.innerHTML = Translate('Country');
						countryColumn.appendChild(countryLabel);
					countryRow.appendChild(countryColumn);  
						const countryField = cTag('div',{ 'class': 'columnSM8'});
						countryField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'shipping_country','id': 'shipping_country','value':data.shipping_country,'maxlength':'35' }));
					countryRow.appendChild(countryField);
				customerForm2Column.appendChild(countryRow);

					//Website
					const websiteRow = cTag('div',{ 'class': 'flex'});
						const websiteColumn = cTag('div',{ 'class': 'columnSM4'});
							const websiteLabel = cTag('label',{ 'for': 'website' });
							websiteLabel.innerHTML = Translate('Website');
						websiteColumn.appendChild(websiteLabel);
					websiteRow.appendChild(websiteColumn);  
						const websiteField = cTag('div',{ 'class': 'columnSM8'});
						websiteField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'website','id': 'website','value':data.website,'maxlength':'80' }));
					websiteRow.appendChild(websiteField);
				customerForm2Column.appendChild(websiteRow);
			customerForm.appendChild(customerForm2Column);

				//Tabs 3
				let customerForm3Column = cTag('div',{ 'class': 'columnXS12','id': 'tabs-3', 'style': "display: none;" });
					let alertColumn = cTag('div',{ 'class': 'columnXS12 columnSM12','align': 'left' });
						textarea = cTag('textarea',{ 'rows': '10','cols': '40','placeholder': Translate('Alert message'),'class': 'form-control','name': 'alert_message','id': 'alert_message' });
						textarea.innerHTML = data.alert_message;
					alertColumn.appendChild(textarea);  
				customerForm3Column.appendChild(alertColumn);
			customerForm.appendChild(customerForm3Column);

		if(data.customFields>0){
			let tab4 = cTag('div',{ 'class': 'columnXS12','id': 'tabs-4' });
			generateCustomeFields(tab4,data.customFieldsData);
			customerForm.appendChild(tab4);
		}
			customerForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'frompage','id': 'frompage','value':segment1 }));
			customerForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'customers_id','value': customers_id }));
			customerForm.appendChild(cTag('input',{ 'type': 'submit',style:'display:none' }));
		divTabs.appendChild(customerForm);
		formDialog.appendChild(divTabs);

		popup_dialog(
			formDialog,
			{
				title:Translate('Customer Information'),
				width:605,
				buttons: {
					_Cancel: {
						text:Translate('Cancel'),
						class: 'btn defaultButton', 'style': "margin-left: 10px;", click: function(hide) {
							hide();
						},
					},
					_Save:{
						text:Translate('Save'),
						class: 'btn saveButton btnmodel', 'style': "margin-left: 10px;", 
						click: (hidePopup)=>AJsave_Customers(hidePopup,POScbf)
					}
				}
			}
		);
		
		// setTimeout(function() {	                
		if(segment1 ==='order' || segment1 ==='pos' || segment1 ==='repairs'){
			document.getElementById('first_name').value = document.getElementById('customer_name').value;		
		}
		
		document.querySelector("#contact_no").addEventListener('blur',function(event) {
			if(this.value!='' && !checkPhone("contact_no", 0)) document.getElementById('errorContact_no').innerHTML = 'invalid phone no.';
		});
		document.querySelector("#contact_no").addEventListener('focus',function(event) {
			if(!checkPhone("contact_no", 0)) document.getElementById('errorContact_no').innerHTML = '';
		});
		document.querySelector("#secondary_phone").addEventListener('blur',function(event) {
			if(this.value!='' && !checkPhone("secondary_phone", 0)) document.getElementById('errorSecondary_phone').innerHTML = 'invalid phone no.';
		});
		document.querySelector("#secondary_phone").addEventListener('focus',function(event) {
			if(!checkPhone("secondary_phone", 0)) document.getElementById('errorSecondary_phone').innerHTML = '';
		});
		
		document.getElementById("first_name").focus();
		if(data.customFields>0 && document.getElementsByClassName("DateField").length>0){
			date_picker('.DateField');						
		}				

		if(document.querySelectorAll(".showNewInputOrSelect")){
			document.querySelectorAll(".showNewInputOrSelect").forEach(oneObj=>{
				oneObj.addEventListener('click', showNewInputOrSelect);
			});
		}
		applySanitizer(formDialog);
		// }, 500);
	}

	return true;
}

async function AJsave_Customers(hidePopup,POScbf){
	let error_customer = document.getElementById("error_customer");
	let error_email = document.getElementById("error_email");
	let errorContact_no = document.getElementById("errorContact_no");
	error_customer.innerHTML = '';
	error_email.innerHTML = '';
	errorContact_no.innerHTML = '';
	
	let missingName = document.getElementById("first_name");
	if(missingName.value===''){
		document.querySelector("#tabs").activateTab(0);
		error_customer.innerHTML = Translate('Missing first name.');
		missingName.focus();
		missingName.classList.add('errorFieldBorder');
		return false;
	}else{
		missingName.classList.remove('errorFieldBorder');
	}
	
	if(document.getElementById("email").value !=='' && emailcheck(document.getElementById("email").value)===false){
		document.querySelector("#tabs").activateTab(0);
		error_email.innerHTML = 'Invalid email address.';
		document.getElementById("email").focus();
		return false;
	}

	if(document.getElementById("contact_no").value ==''){
		errorContact_no.innerHTML = 'Missing phone number.';
		document.getElementById("contact_no").focus();
		return false;
	}	
	else if(checkPhone("contact_no", 0)===false){
		errorContact_no.innerHTML = 'Invalid phone number.';
		document.getElementById("contact_no").focus();
		return false;
	}
	
	let validCustomFields = validifyCustomField(3);
	if(!validCustomFields) return;
	
	let customers_id = Number(document.querySelector('#frmcustomer [name="customers_id"]').value);
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
	
	const url = '/'+segment1+'/AJsave_Customers';
	fetchData(afterFetch,url,document.getElementById('frmcustomer'),'formData');
	
	function afterFetch(data){
		let first_name, last_name, contact_no, email, customerNameField, notify_email, notify_sms;
		if(data.savemsg !=='error'){
			if(segment1 ==='Orders'){
				first_name = document.frmcustomer.first_name.value;
				last_name = document.frmcustomer.last_name.value;
				contact_no = document.frmcustomer.contact_no.value;
				email = document.frmcustomer.email.value;
				if(customers_id===0){
					document.getElementById("customer_id").value = data.customers_id;
					customerNameField = document.getElementById('customerNameField');
					customerNameField.innerHTML = '';
						let inputField = cTag('input',{ 'readonly': true,'type': 'text','value': data.resulthtml,'required': 'required','name':'customer_name','id':'customer_name','class':'form-control','placeholder':Translate('Search Customers') });
					customerNameField.appendChild(inputField);
						let changeSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Clear Customer'),'class': 'input-group-addon cursor','name':'customer_name' });
						changeSpan.addEventListener('click', function (){clearCustomerField(POScbf);});
						changeSpan.appendChild(cTag('i',{ 'class': 'fa fa-edit' }));
						changeSpan.append(' '+Translate('Change'));
					customerNameField.appendChild(changeSpan);
				}
				else{
					first_name = document.frmcustomer.first_name.value;
					last_name = document.frmcustomer.last_name.value;
					contact_no = document.frmcustomer.contact_no.value;
					email = document.frmcustomer.email.value;
					const customer_link = document.querySelector('#customer_link');
					if(customer_link != null){
						customer_link.innerHTML = '';
						customer_link.setAttribute('href',`/Customers/view/${customers_id}`,);
						customer_link.append(first_name+' '+last_name,' ',cTag('i',{'class':'fa fa-link'}));
					}
					if(document.querySelector('#customeremail') != null){document.querySelector('#customeremail').innerHTML = email;}
					if(document.querySelector('#customerphone')!=null){document.querySelector('#customerphone').innerHTML = contact_no; }
				}
				if(document.querySelector("#customer_id" ) !== null){   
					document.getElementById("customer_id").value = data.customers_id;
				}
				if(document.querySelector("#customerNameField") !== null){
					customerNameField = document.getElementById('customerNameField');
					customerNameField.innerHTML = '';
					customerNameField.appendChild(cTag('input',{ 'readonly': true,'type': 'text','value': data.resulthtml,'required': 'required','name':'customer_name','id':'customer_name','class':'form-control','placeholder':Translate('Search Customers') }));
					let editSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Clear Customer'),'class': 'input-group-addon cursor' });
					editSpan.addEventListener('click', function (){clearCustomerField(POScbf);});
					editSpan.appendChild(cTag('i',{ 'class': 'fa fa-edit' }));
					editSpan.append(' '+Translate('Change'));
					customerNameField.appendChild(editSpan);
				}
			}
			else if(segment1==='Customers'){
				if(customers_id>0){
					location.reload();				
				}
				else{
					window.location = '/Customers/view/'+data.customers_id;
				}
			}
			else{
				if(segment1==='Repairs'){
					if(customers_id===0){
						document.querySelector("#customer_devices").innerHTML = '';
						if(document.getElementsByClassName("notify_how").length>0 && document.frmrepairs.notify_how.checked){
							let notify_how = document.frmrepairs.notify_how.value;
							if(notify_how===1){
								notify_email = document.getElementById("notify_email");
								notify_email.required = true;
								notify_email.value = data.email;
								if(notify_email.style.display === 'none'){
									notify_email.style.display = '';
								}

								notify_sms = document.getElementById("notify_sms");
								notify_sms.required = false;
								notify_sms.value = "";
								if(notify_sms.style.display !== 'none'){
									notify_sms.style.display = 'none';
								}
							}
							else if(notify_how===2){
								notify_email = document.getElementById("notify_email");
								notify_email.required = false;
								notify_email.value = "";
								if(notify_email.style.display !== 'none'){
									notify_email.style.display = 'none';
								}

								notify_sms = document.getElementById("notify_sms");
								notify_sms.required = true;
								notify_sms.value = data.contact_no;
								if(notify_sms.style.display === 'none'){
									notify_sms.style.display = '';
								}
							}
						}
					}
					else{	
						let customername = document.frmcustomer.company.value;
						first_name = document.frmcustomer.first_name.value;
						last_name = document.frmcustomer.last_name.value;
						if(customername !==''){customername += ', ';}
						customername += first_name;
						if(customername !==''){customername += ' ';}
						customername += last_name;
						
						contact_no = document.frmcustomer.contact_no.value;
						email = document.frmcustomer.email.value;
						if(document.getElementById('customer_information')!=null){					
							let view_customer_details = document.querySelector('#view_customer_details');
							view_customer_details.innerHTML = '';
							view_customer_details.setAttribute('href',`/Customers/view/${data.customers_id}`);
							view_customer_details.append(customername+' ',cTag('i',{ 'class':`fa fa-link` }));
							document.querySelector('#customeremail').innerHTML = email;
							document.querySelector('#phoneno').innerHTML = contact_no;							
						}
					}
				}
				if(document.querySelector("#customer_id" ) !== null){   
					document.getElementById("customer_id").value = data.customers_id;
				}
				if(document.querySelector("#customerNameField") !== null){
					customerNameField = document.getElementById('customerNameField');
					let customer_name = '';
					if(data.resulthtml){customer_name = data.resulthtml;}
					if(customer_name !='' && customer_name.indexOf("'")){customer_name = stripslashes(customer_name);}
					if(segment1==='POS'){
						const customer_nameField = customerNameField.querySelector('#customer_name');
						customer_nameField.value = customer_name;
						customer_nameField.setAttribute('readonly','');
						customerNameField.querySelector('#newCustomerId').style.display = 'none';
						customerNameField.querySelector('#editCustomerHide').style.display = '';
						customerNameField.querySelector('#changeCustomerId').style.display = '';
					}
					else{
						customerNameField.innerHTML = '';		
						customerNameField.appendChild(cTag('input',{ 'readonly': true,'type': 'text','value': customer_name,'required': 'required','name':'customer_name','id':'customer_name','class':'form-control','placeholder':Translate('Search Customers') }));
							let clearSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Clear Customer'),'class': 'input-group-addon cursor' });
							clearSpan.addEventListener('click', function (){clearCustomerField(POScbf);});
							clearSpan.appendChild(cTag('i',{ 'class': 'fa fa-edit' }));
							clearSpan.append(' '+Translate('Change'));
						customerNameField.appendChild(clearSpan);
					}					
				}
				if(document.querySelector("#errmsg_customer_id") !== null){
					document.getElementById('errmsg_customer_id').innerHTML = '';
				}
				if(document.querySelector("#error_customer") !== null){
					document.getElementById('error_customer').innerHTML = '';
				}
				if(segment1==='POS'){
					if(customers_id===0){
						document.getElementById('email_address').value = data.email
					}
					POScbf(data.customers_id,data.crlimit);					
				}
			}
			hidePopup();
		}
		else if(data.returnStr=='errorOnAdding'){
			if(document.querySelector("#errmsg_customer_id") !== null && (segment1 ==='Orders' || segment1 ==='POS' || segment1 ==='Repairs')){
				document.getElementById('errmsg_customer_id').innerHTML = '';				
			}
			document.getElementById('error_customer').innerHTML = Translate('Error occured while adding new customer! Please try again.');
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else if(data.returnStr==='Name_Already_Exist'){
			if(document.querySelector("#errmsg_customer_id") !== null && (segment1 ==='Orders' || segment1 ==='POS' || segment1 ==='Repairs')){
				document.getElementById('errmsg_customer_id').innerHTML = '';				
			}
			document.getElementById('error_customer').innerHTML = Translate('This name and email already exist. Try again with a different name/email.');
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else if(data.returnStr==='Name_ExistInArchive'){
			if(document.querySelector("#errmsg_customer_id") !== null && (segment1 ==='Orders' || segment1 ==='POS' || segment1 ==='Repairs')){
				document.getElementById('errmsg_customer_id').innerHTML = '';				
			}
			document.getElementById('error_customer').innerHTML = Translate('This name and email already exist <b>IN ARCHIVED</b>!. Try again with a different name/email.');
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else{
			if(document.querySelector("#errmsg_customer_id") !== null && (segment1 ==='Orders' || segment1 ==='POS' || segment1 ==='Repairs')){
				document.getElementById('errmsg_customer_id').innerHTML = '';				
			}
			document.getElementById('error_customer').innerHTML = Translate('No changes / Error occurred while updating data! Please try again.');
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
	}

	return false;
}

export async function AJget_propertiesPopup(properties_id){
    const jsonData = {};
	jsonData['properties_id'] = properties_id;
	let customers_id = 0;
	if(document.getElementById('customer_id')){customers_id = document.getElementById('customer_id').value;}
	else if(document.getElementById('customers_id')){customers_id = document.getElementById('customers_id').value;}
	
	if(customers_id===0){
		document.getElementById('errmsg_customer_id').innerHTML = Translate('Missing customer name');
		document.getElementById("customer_name").focus();
		return false;
	}
	jsonData['customers_id'] = customers_id;

    const url = '/'+segment1+'/AJget_propertiesPopup';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let noPermission = data.cnanbm;
		if(segment1!=='Repairs') noPermission = 0;

		let repairs_id = 0;
		if(document.getElementById("repairs_id") !== null){
			repairs_id = document.getElementById("repairs_id").value;
		}

		let requiredField;
		const formDialog = cTag('div');
		formDialog.innerHTML = '';
			const propertyForm = cTag('form',{ 'action': '#','name': 'frmProperties','id': 'frmProperties','enctype': 'multipart/form-data','method': 'post','accept-charset': 'utf-8' });
			propertyForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
				const propertyColumn = cTag('div',{ 'class':"columnSM12", 'align': 'left' });
					const imeiSerialRow = cTag('div',{ 'class': 'flex'});
						const imeiSerialColumn = cTag('div',{ 'class': 'columnSM4'});
							const imeiSerialLabel = cTag('label',{ 'for': 'imei_or_serial_no','data-placement': 'bottom' });
							imeiSerialLabel.innerHTML = Translate('IMEI/Serial No.');
						imeiSerialColumn.appendChild(imeiSerialLabel);
					imeiSerialRow.appendChild(imeiSerialColumn);
						const imeiSerialField = cTag('div',{ 'class': 'columnSM8'});
							let imeiInput = cTag('input',{ 'maxlength': '20','type': 'text','autocomplete': 'off','class': 'form-control','name': 'imei_or_serial_no','id': 'imei_or_serial_no','value': data.imei_or_serial_no });
							imeiInput.addEventListener('keyup', function() {
								let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#";				
								let sku = this.value.toUpperCase().replace(' ', '-');
								let IsNumber = true;
								let Char;
								let newsku = '';
								for ( let i = 0; i < sku.length && IsNumber === true; i++){ 
									Char = sku.charAt(i); 
									if (ValidChars.indexOf(Char) === -1){}
									else{
										newsku = newsku+Char;
									}
								}								
								if(sku.length> newsku.length || this.value !== newsku){
									this.value = newsku;
								}
							});	
						imeiSerialField.appendChild(imeiInput);
					imeiSerialRow.appendChild(imeiSerialField);
				propertyColumn.appendChild(imeiSerialRow);

					const brandRow = cTag('div',{ 'class': 'flex'});
						const brandColumn = cTag('div',{ 'class': 'columnSM4'});
							const brandLabel = cTag('label',{ 'for': 'brand','data-placement': 'bottom' });
							brandLabel.innerHTML = Translate('Brand');
								requiredField = cTag('span',{ 'class': 'required' });
								requiredField.innerHTML = '*';
							brandLabel.appendChild(requiredField);
						brandColumn.appendChild(brandLabel);
					brandRow.appendChild(brandColumn);
						const brandDropDown = cTag('div',{ 'class': 'columnSM8'});
							const brandInGroup = cTag('div');
								let selectBrand = cTag('select',{ 'required': 'required','class': 'form-control','name': 'brand','id': 'brand' });
								setOptions(selectBrand, data.brandOpts, 0, 1);
								selectBrand.addEventListener('change', AJget_modelOpt);
							brandInGroup.appendChild(selectBrand);
							brandInGroup.appendChild(cTag('input',{ 'type': 'text','class': 'form-control', 'name': 'brand_name','id': 'brand_name','value': '','maxlength': '15', 'style': "display: none;"}));
								brandInGroup.classList.add('input-group');
									let newSpan = cTag('span',{ 'data-toggle': 'tooltip','data-original-title': Translate('Add New Brand'),'class': 'input-group-addon cursor showNewInputOrSelect' });
									newSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
								brandInGroup.appendChild(newSpan);
						brandDropDown.appendChild(brandInGroup);
						brandDropDown.appendChild(cTag('span',{ 'id':"errorProperties",'class':"errormsg" }));
					brandRow.appendChild(brandDropDown);
				propertyColumn.appendChild(brandRow);

					const modelRow = cTag('div',{ 'class': 'flex'});
						const modelColumn = cTag('div',{ 'class': 'columnSM4'});
							const modelLabel = cTag('label',{ 'for': 'model','data-placement': 'bottom' });
							modelLabel.innerHTML = Translate('Model');
								requiredField = cTag('span',{ 'class': 'required' });
								requiredField.innerHTML = '*';
							modelLabel.appendChild(requiredField);
						modelColumn.appendChild(modelLabel);
					modelRow.appendChild(modelColumn);
						const modelDropDown = cTag('div',{ 'class': 'columnSM8'});
							const modelInGroup = cTag('div');
								let selectModel = cTag('select',{ 'required': 'required','class': 'form-control','name': 'model','id': 'model' });
								if(!data.modelOpts.length) setOptions(selectModel, [''], 0, 1);
								setOptions(selectModel, data.modelOpts, 0, 1);
							modelInGroup.appendChild(selectModel);
							modelInGroup.appendChild(cTag('input',{ 'type': 'text','class': 'form-control', 'style': "display: none;", 'name': 'model_name','id': 'model_name','value': '','maxlength': '25'}));
								modelInGroup.classList.add('input-group');
									let newModelSpan = cTag('span',{ 'data-toggle': 'tooltip','data-original-title': Translate('Add New Model'),'class': 'input-group-addon cursor showNewInputOrSelect' });
									newModelSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
								modelInGroup.appendChild(newModelSpan);
						modelDropDown.appendChild(modelInGroup);
						modelDropDown.appendChild(cTag('span',{ 'id':"error_model",'class':"errormsg" }));
					modelRow.appendChild(modelDropDown);
				propertyColumn.appendChild(modelRow);

					const moreDetailRow = cTag('div',{ 'class': 'flex'});
						const moreDetailColumn = cTag('div',{ 'class': 'columnSM4'});
							const moreDetailLabel = cTag('label',{ 'for': 'more_details','data-placement': 'bottom' });
							moreDetailLabel.innerHTML = Translate('More Details');
						moreDetailColumn.appendChild(moreDetailLabel);
					moreDetailRow.appendChild(moreDetailColumn);
						const moreDetailField = cTag('div',{ 'class': 'columnSM8'});
						moreDetailField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'more_details','id': 'more_details','value': data.more_details,'maxlength': '45' }));
					moreDetailRow.appendChild(moreDetailField);
				propertyColumn.appendChild(moreDetailRow);
			propertyForm.appendChild(propertyColumn);
			propertyForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'repairs_id','value': repairs_id }));
			propertyForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'customers_id','id': 'customers_id','value': data.customers_id }));
			propertyForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'brand_model_id','id': 'brand_model_id','value': data.brand_model_id }));
			propertyForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'properties_id','id': 'properties_id','value': data.properties_id }));
		formDialog.appendChild(propertyForm);

		popup_dialog600(Translate('Customer Properties Information'),formDialog,Translate('Save'), AJsave_properties);
		formDialog.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item))

		// setTimeout(function() {				
		document.querySelector('#frmProperties #imei_or_serial_no').focus();				
		document.querySelector('#frmProperties #brand').value = data.brand;				
		document.querySelector('#frmProperties #model').value = data.model;				

		if(noPermission){
			formDialog.querySelectorAll(".showNewInputOrSelect").forEach((oneObj,indx)=>{
				let msg = Translate('Sorry, you do not have permission to add new Model');
				if(indx===1) msg = Translate('Sorry, you do not have permission to add new Brand');
				oneObj.addEventListener('click', function(){
					alert_dialog(Translate('Can Not Add'), msg, Translate('Ok'));
				});
			});
		}
		else{
			formDialog.querySelectorAll(".showNewInputOrSelect").forEach(oneObj=>{
				oneObj.addEventListener('click', showNewInputOrSelect);
			});
		}
		// }, 500);
	}

	return false;
}

async function AJsave_properties(hidePopup){
	let pTag;
	const errorStatus = document.getElementById('errorProperties');
	const error_model = document.getElementById('error_model');
	errorStatus.innerHTML = '';
	error_model.innerHTML = '';

	let brand = document.getElementById("brand");
	let brand_name = document.getElementById("brand_name");
    if((brand.value===''&&brand.style.display!=='none') || (brand_name.value===''&&brand_name.style.display!=='none')){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Brand');
        errorStatus.appendChild(pTag);
		brand.focus();
		brand_name.focus();
		brand_name.classList.add('errorFieldBorder');
        return false;
    }else{
		brand_name.classList.remove('errorFieldBorder');
	}

    errorStatus.innerHTML = '';

	let model = document.getElementById("model");
	let model_name = document.getElementById("model_name");
    if((model.value===''&&model.style.display!=='none') || (model_name.value===''&&model_name.style.display!=='none')){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Model');
        error_model.appendChild(pTag);
		model.focus();
		model_name.focus();
		model_name.classList.add('errorFieldBorder');
        return false;
    }else{
		model_name.classList.remove('errorFieldBorder');
	}

    errorStatus.innerHTML = '';
	if(document.getElementById("customer_id") ==='null' && document.getElementById("customers_id") ==='null'){
		hidePopup();
		document.getElementById("customer_name").focus();
        return false;
    }	
    const properties_id = document.getElementById('properties_id').value;
	errorStatus.innerHTML = '';
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
			
    const jsonData = serialize('#frmProperties');
    const url = '/'+segment1+'/AJsave_properties';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg !=='error'){
			if(properties_id>0){
				if(segment1==='Repairs'){
					let imei_or_serial_no = data.propertyInfo.imei_or_serial_no;
					let brand = data.propertyInfo.brand;
					let model = data.propertyInfo.model;
					let more_details =data.propertyInfo.more_details;
										
					const propertiesInfo= document.getElementById('propertiesInfo');
					propertiesInfo.innerHTML = '';
						let serialLabel = cTag('label');
						serialLabel.innerHTML = Translate('IMEI/Serial No.');
						let serialSpan = cTag('span');
						serialSpan.innerHTML = stripslashes(imei_or_serial_no);
					propertiesInfo.append(serialLabel, serialSpan);

						let brandLabel = cTag('label');
						brandLabel.innerHTML = Translate('Brand');
						let brandSpan = cTag('span');
						brandSpan.innerHTML = stripslashes(brand);
					propertiesInfo.append(brandLabel, brandSpan);

						let modelLabel = cTag('label');
						modelLabel.innerHTML = Translate('Model');
						let modelSpan = cTag('span');
						modelSpan.innerHTML = stripslashes(model);
					propertiesInfo.append(modelLabel, modelSpan);

						let detailLabel = cTag('label');
						detailLabel.innerHTML = Translate('More Details');
						let detailSpan = cTag('span');
						detailSpan.innerHTML = stripslashes(more_details);
					propertiesInfo.append(detailLabel, detailSpan);	
				}
				else{
					customerProperties();
				}
				filter_Customers_view();
			}
			else{
				let select= document.getElementById("customer_devices");
				select.innerHTML = '';
				select.append(cTag('option', {value:''}));
				setOptions(select, data.propOpts, 1, 1);
				select.value = data.properties_id;
			}
			hidePopup();
		}
		else{
			if(data.returnStr=='errorOnAdding'){
				errorStatus.innerHTML = Translate('Error occured while adding new customer properties! Please try again.');
			}
			else if(data.returnStr=='errorOnEditing'){
				errorStatus.innerHTML = Translate('There is no changes made. Please try again.');
			}
			else if(data.returnStr==='Name_Already_Exist'){
				errorStatus.innerHTML = Translate('This property name already exists! Please try again with different information.');				
			}
			else{
				errorStatus.innerHTML = Translate('Customer properties information missing.');
			}
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
	}

	return false;
}

//change Repairs,Orders customer
export function changeCustomerPopup(calculateCartTotalCBF){
	const formDialog = cTag('div');
	// formDialog.innerHTML = '';
	// formDialog.appendChild(cTag('div',{ 'id':"errorCustomerInfo",'class':"errormsg" }));
		const customerChangeForm = cTag('form',{ 'action': '#','name': 'frmCustomerChange','id': 'frmCustomerChange','enctype': 'multipart/form-data','method': 'post','accept-charset': 'utf-8' });
			const customerChangeDiv = cTag('div',{ 'class':"flex" });
				const customerRow = cTag('div',{ 'class': 'columnSM4', 'align': 'left', 'style': "align-items: center;" });
					const customerNameLable = cTag('label',{ 'for': 'customer_name' });
					customerNameLable.innerHTML = Translate('Customer Name');
				customerRow.appendChild(customerNameLable);
				const customerNameField = cTag('div',{ 'class': 'columnSM8'});
				customerNameField.appendChild(cTag('input',{ 'maxlength': '20','type': 'text','autocomplete': 'off','class': 'form-control','name': 'customer_name','id': 'customer_name' }));
				customerNameField.appendChild(cTag('span',{ 'id':"errorCustomerInfo",'class':"errormsg" }));
			customerChangeDiv.append(customerRow, customerNameField);
		customerChangeForm.appendChild(customerChangeDiv);
		customerChangeForm.appendChild(cTag('input',{ 'type': 'hidden','id': 'changed_customer_id' }));
	formDialog.appendChild(customerChangeForm);
	popup_dialog600(Translate('Change Customer'), formDialog, Translate('Change'), (hidePopup)=>{
		changeCustomer(customerChangeForm.querySelector('#changed_customer_id').value,hidePopup,calculateCartTotalCBF);
	});
	// setTimeout(() => {
	AJautoComplete('customer_name');
	formDialog.querySelector('#customer_name').focus();
	// }, 0);
}

async function changeCustomer(customer_id,hidePopup,calculateCartTotalCBF){
	//validation
	let errorInfo = document.getElementById('errorCustomerInfo');
	if(customer_id===document.getElementById('customer_id').value){
		errorInfo.innerHTML = 'You have choosen the same Customer you already have.';
		return;
	} 
	if(customer_id===''){
		errorInfo.innerHTML = 'Please choose a customer first';
		return;
	} 

	let pos_id = document.querySelector('#pos_id').value;

	const jsonData = { pos_id, customer_id };
	if(segment1==='Repairs') jsonData.repairs_id = document.querySelector('#repairs_id').value;
	else if(segment1==='Orders') jsonData.invoice_no = document.querySelector('#invoice_no').value;

    const url = '/'+segment1+'/updateCustomerInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.querySelector('#customer_id').value = customer_id;
		if(segment1==='Repairs') document.querySelector('#properties_id').value = data.returnData.propertiesId;
		document.querySelector('#email_address').value = data.returnData.customeremail;

		let view_customer_details;
		if(segment1==='Orders') view_customer_details = document.getElementById('customer_link');
		else if(segment1==='Repairs') view_customer_details = document.getElementById('view_customer_details');
		view_customer_details.innerHTML = '';
		view_customer_details.setAttribute('href',`/Customers/view/${customer_id}`);
		view_customer_details.append(data.returnData.customername+' ',cTag('i',{ 'class':`fa fa-link` }));
		document.querySelector('#customeremail').innerHTML = data.returnData.customeremail;

		if(segment1==='Orders') document.querySelector('#customerphone').innerHTML = data.returnData.customerphone;
		else if(segment1==='Repairs') document.querySelector('#phoneno').innerHTML = data.returnData.customerphone;

		document.getElementById('available_credit').value = data.returnData.available_credit;
		document.getElementById('available_credit_label').innerHTML = addCurrency(data.returnData.available_credit);
		if(data.returnData.available_credit>0) document.getElementById("available_creditrow").style.display = '';
		else document.getElementById("available_creditrow").style.display = 'none';

		calculateCartTotalCBF();
		hidePopup();
		showTopMessage('success_msg', Translate('Customer changed'));
	}
}

//========archive customer===========
async function archiveCustomer(customers_id){
	confirm_dialog(Translate('Customer Archive'), Translate('Are you sure you want to archive this information?'), (hidePopup)=>{
		archiveData(`/${segment1}/AJ_customers_archive/`,'/Customers/lists', {"customers_id":customers_id}, Translate('Customer'));
		hidePopup();
	});
}

async function unarchiveCustomer(customers_id){
	confirm_dialog(Translate('Customer')+' '+Translate('Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{		
		unarchiveData(`/Customers/view/${customers_id}`,  {tablename:'customers', tableidvalue:customers_id, publishname:'customers_publish'});
		hidePopup();
	});
}

document.addEventListener('DOMContentLoaded', async()=>{
	let layoutFunctions = {lists, view, crm};
	layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});
