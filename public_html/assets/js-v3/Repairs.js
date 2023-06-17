import {
	cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, emailcheck, checkPhone, DBDateToViewDate, noPermissionWarning, 
	redirectTo, preventDot, printbyurl, confirm_dialog, alert_dialog, setSelectOpt, setTableHRows, showTopMessage, setOptions, alert_label_missing,
	addPaginationRowFlex, checkAndSetSessionData, popup_dialog, popup_dialog600, date_picker, createTabs, setCDinCookie, runImageScript,
	validDate, dynamicImport, applySanitizer, togglePaymentButton, AJget_notesData, AJget_modelOpt, controllNumericField, validifyCustomField,
	generateCustomeFields, fetchData, listenToEnterKey, addCustomeEventListener, confirmAJremove_tableRow, actionBtnClick, showNewInputOrSelect,
	callShowInputOrSelect, serialize, multiSelectAction, onClickPagination,AJautoComplete, historyTable, activityFieldAttributes, calculate
} from './common.js';

import {
	AJget_oneTimePopup, showCategoryPPProduct, addPOSPayment, showProductPicker, reloadProdPkrCategory, addCartsProduct,haveAnyOversoldProduct,
	cartsAutoFuncCall, calculateChangeCartTotal, showCartCompleteBtn, loadCartData, emaildetails, cancelemailform, emailthispage, 
	onChangeTaxesId, preNextCategory, updateCartData, showOrNotSquareup, AJautoComplete_cartProduct, checkMethod, loadPaymentData, calculateCartTotal
} from './cart.js';

if(segment2==='') segment2 = 'lists';

const listsFieldAttributes = [{'valign':'middle','datatitle':Translate('Customer Name/ Tech Assigned'), 'align':'left'},
	{'valign':'middle','datatitle':Translate('Quick Description'), 'align':'left'},
	{'valign':'middle','datatitle':Translate('Created'), 'align':'left'},
	{'valign':'middle','datatitle':Translate('Ticket # / Status'), 'align':'center'},
	{'valign':'middle','datatitle':Translate('Last Update'), 'align':'center'}
];

const uriStr = segment1+'/edit';

function createListRows(data){
    const table = document.getElementById("tableRows");
    table.innerHTML = '';
    if(data.length){
        data.forEach(item=>{
            const row = cTag('tr');
            item.forEach((itemInfo,indx)=>{
                if([0, 6, 7, 8, 9].includes(indx)) return;
                const cell = cTag('td');
                const attributes = listsFieldAttributes[indx-1];
                for (const key in attributes) {
					let attName = key;
					if(attName !=='' && attName==='datatitle')
						attName = attName.replace('datatitle', 'data-title');
                    cell.setAttribute(attName,attributes[key]);
                }

                const link = cTag('a',{'class':`anchorfulllink`, 'href':`/${uriStr}/${item[0]}`});
				if(indx===1){
					link.innerHTML = itemInfo;
					if(item[9] !=''){
						if(itemInfo !=''){link.append(cTag('br'), '\u2003\u2003Tech: ');}
						link.append(item[9]);
					}
				}
                else if(indx===3){
                    link.innerHTML = DBDateToViewDate(itemInfo, 0, 1);
					let due_datetime = DBDateToViewDate(item[6], 0, 1);
					if(due_datetime !==''){
						link.append(cTag('br'), due_datetime+' '+item[7]);
					}
                }
                else if(indx===4){
					link.innerHTML = 'T'+itemInfo;
					const spanTag = cTag('span');
					spanTag.innerHTML = item[8];
					link.append(cTag('br'), spanTag);
				}
				else{link.innerHTML = itemInfo;}
                
                cell.appendChild(link);
                row.appendChild(cell);
            })
            table.appendChild(row);
        })
    }
    else{
		//No_Invoices_meet
		let colspan = listsFieldAttributes.length;
		const tr = cTag('tr');
			const tdCol = cTag('td', {colspan:colspan, 'style': "color: #F00; font-size: 16px;"});
			tdCol.innerHTML = Translate('No repairs meet the criteria given')
		tr.appendChild(tdCol);
		table.appendChild(tr);
	}
}

async function filter_Repairs_lists(){
    let page = 1;
    document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById("ssorting_type").value;
	jsonData['sview_type'] = document.getElementById("sview_type").value;
	jsonData['sassign_to'] = document.getElementById("sassign_to").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetPage/filter';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		storeSessionData(jsonData);
		let vieTypOpt = {
			'2': Translate('Closed'), 
			'Invoiced': Translate('Invoiced'), 
			'Cancelled': Translate('Cancelled'), 
			'Estimate': Translate('Estimate'), 
			'New': Translate('New'),
			...data.vieTypOpt,
			'Finished': Translate('Finished'),
			'All': Translate('All Statuses'),
		}
		setSelectOpt('sview_type', 1, Translate('Open'), vieTypOpt, 1, Object.keys(vieTypOpt).length);

		const sassign_to = document.getElementById("sassign_to");
		sassign_to.innerHTML = '';
		const option = cTag('option', {'value': 0});
			option.innerHTML = Translate('Assigned to');
			sassign_to.appendChild(option);
		setOptions(sassign_to, data.assToOpt, 1, 1);
		
		createListRows(data.tableRows);			
		
		document.getElementById("totalTableRows").value = data.totalRows;
		document.getElementById("ssorting_type").value = jsonData['ssorting_type'];
		document.getElementById("sview_type").value = jsonData['sview_type'];
		document.getElementById("sassign_to").value = jsonData['sassign_to'];
		
		onClickPagination();
	}
}

async function loadTableRows_Repairs_lists(){
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById("ssorting_type").value;
	jsonData['sview_type'] = document.getElementById("sview_type").value;
	jsonData['sassign_to'] = document.getElementById("sassign_to").value;
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

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

     //======Hidden Fields for Pagination=======//
     [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '51' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        const input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

		let sortDropDown;
		const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
			const headerTitle = cTag('h2');
			headerTitle.innerHTML = Translate('Repairs')+' ';
				const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('This page displays the list of Open, Closed and Cancelled repair tickets')});
			headerTitle.appendChild(infoIcon);
		titleRow.appendChild(headerTitle);

			const createTicketLink = cTag('a', {'href': "/Repairs/add", title: Translate('Create Ticket')});
				const ticketButton = cTag('button', {class: "btn createButton"});
				ticketButton.append(cTag('i', {class: "fa fa-plus"}), ' ', Translate('Create Ticket'));
			createTicketLink.appendChild(ticketButton);
		titleRow.appendChild(createTicketLink);
    showTableData.appendChild(titleRow);

		const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
			sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
				const selectSorting = cTag('select', {class: "form-control", name: "ssorting_type", id: "ssorting_type"});
				selectSorting.addEventListener('change', filter_Repairs_lists);
				const options = {
					'0':Translate('First Name'),
					'1': Translate('Last Name'),
					'2':Translate('Due Date'),
					'3':Translate('Last Update'),
					'4':Translate('Ticket Number'),
					'5':Translate('Ticket Number DESC'),
					'6':Translate('TStatus'),
					'7':Translate('Problem'),
					'8':Translate('Tech Assigned')
				};
				for(const [key, value] of Object.entries(options)) {
					let sortingOption = cTag('option', {'value': key});
					sortingOption.innerHTML = value;
					selectSorting.appendChild(sortingOption);
				}
			sortDropDown.appendChild(selectSorting);
		filterRow.appendChild(sortDropDown);

			sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
				const selectView = cTag('select', {class: "form-control", name: "sview_type", id: "sview_type"});
				selectView.addEventListener('change', filter_Repairs_lists);
					let viewOption = cTag('option', {'value': 1});
					viewOption.innerHTML = Translate('Open');
				selectView.appendChild(viewOption);
			sortDropDown.appendChild(selectView);
		filterRow.appendChild(sortDropDown);

			sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
				const selectAssign = cTag('select', {class: "form-control", name: "sassign_to", id: "sassign_to"});
				selectAssign.addEventListener('change', filter_Repairs_lists);
					let assignOption = cTag('option', {'value': 0});
					assignOption.innerHTML = Translate('Assigned to');                    
				selectAssign.appendChild(assignOption);
			sortDropDown.appendChild(selectAssign);
		filterRow.appendChild(sortDropDown);

			const searchDiv = cTag('div', {class: "columnXS6 columnSM3"});
				const SearchInGroup = cTag('div', {class: "input-group"});
					const searchField = cTag('input', {keydown: listenToEnterKey(filter_Repairs_lists),'type': "text", 'placeholder': Translate('Search Repairs'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
				SearchInGroup.appendChild(searchField);
					let searchSpan = cTag('span', {keydown: listenToEnterKey(filter_Repairs_lists),class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('Search Repairs')});
					searchSpan.addEventListener('click', filter_Repairs_lists);
						const searchIcon = cTag('i', {class: "fa fa-search"});
					searchSpan.appendChild(searchIcon);
				SearchInGroup.appendChild(searchSpan);
			searchDiv.appendChild(SearchInGroup);
		filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

		const divTable = cTag('div', {class: "flex"});
			const listTableColumn = cTag('div', {class: "columnXS12"});
				const divNoMore = cTag('div', {id: "no-more-tables"});
					const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
						const listHead = cTag('thead', {class: "cf"});
							const columnNames = listsFieldAttributes.map(colObj=>(colObj.datatitle));
							const listHeadRow = cTag('tr',{class:'outerListsTable'});
								const thCol0 = cTag('th', {'width': "20%"});
								thCol0.innerHTML = columnNames[0];

								const thCol1 = cTag('th');
								thCol1.innerHTML = columnNames[1];

								const thCol2 = cTag('th', {'width': "14%"});
								thCol2.innerHTML = columnNames[2];

								const thCol3 = cTag('th', {'width': "15%"});
								thCol3.innerHTML = columnNames[3];

								const thCol4 = cTag('th', {'width': "10%"});
								thCol4.innerHTML = columnNames[4];
							listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
						listHead.appendChild(listHeadRow);
					listTable.appendChild(listHead);

						const listBody = cTag('tbody', {id: "tableRows"});
					listTable.appendChild(listBody);
				divNoMore.appendChild(listTable);
			listTableColumn.appendChild(divNoMore);
		divTable.appendChild(listTableColumn);
    showTableData.appendChild(divTable);
    addPaginationRowFlex(showTableData);

    //=======sessionStorage =========//
	let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    
    let ssorting_type = document.getElementById('defaultssorting_type').value, sview_type = 1, sassign_to = 0;

	checkAndSetSessionData('ssorting_type', ssorting_type, list_filters);
	checkAndSetSessionData('sview_type', sview_type, list_filters);
	checkAndSetSessionData('sassign_to', sassign_to, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

	addCustomeEventListener('filter',filter_Repairs_lists);
	addCustomeEventListener('loadTable',loadTableRows_Repairs_lists);
	filter_Repairs_lists(true);
}

async function AJsaveRepairs(event){
	if(event){event.preventDefault();}
	
    
	if(document.getElementById('customer_id').value==='0'){
		showTopMessage('alert_msg', 'Choose a Customer');
		document.getElementById('customer_name').focus();
		return false;
	}

	const problem = document.getElementById("problem");
	const problem_name = document.getElementById("problem_name");
	if(problem.value==='' && problem_name.value===''){
		if(problem.style.display === 'none'){
			problem_name.focus();
		}
		else{
			problem.focus();
		}
		showTopMessage('alert_msg', 'Missing Problem');
		return false;
	}
	
	const requiredForm = document.getElementsByClassName("requiredForm");
	if(requiredForm.length>0){
		for(let l = 0; l < requiredForm.length; l++){
			if(requiredForm[l].hasAttribute("disabled")===false){
				showTopMessage('alert_msg', 'FORM "'+requiredForm[l].innerHTML+'" is required. Please click and save it.');
				requiredForm[l].focus();
				return false;
			}
		}
	}
	let submitBtn = document.getElementById('submit');
	submitBtn.innerHTML = Translate('Saving')+'...';
	submitBtn.disabled = true;
	
	const jsonData = serialize('#frmrepairs');
	
    const url = '/'+segment1+'/save_repairs';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.savemsg !=='error' && data.id>0){
			window.location = '/Repairs/edit/'+data.id;
		}
		else{			
			if(data.message==='noCustomerName') showTopMessage('alert_msg', Translate('Customer name not found. Please try again with valid customer name.'));
			else if(data.message==='errorAdding') showTopMessage('alert_msg', Translate('Error occured while adding new repair! Please try again.'));

			if(parseInt(document.getElementById("repairs_id").value)===0){
				let submitBtn = document.getElementById('submit');
				submitBtn.innerHTML = Translate('Add');
				submitBtn.disabled = false;
			}
			else{
				let submitBtn = document.getElementById('submit');
				submitBtn.innerHTML = Translate('Update');
				submitBtn.disabled = false;
			}
		}
	}
	return false;
}

function addNewModel(){
	let addNewModel = document.querySelector("#addNewModel .fa").getAttribute('class');
	const modelStr = document.getElementById("modelStr");
	if(modelStr.style.display !== 'none'){modelStr.style.display = 'none';}
	
	if(addNewModel==='fa fa-plus'){		
		modelStr.innerHTML = '';
			const inputField = cTag('input', {'required': "required", 'type': "text", 'value': "", 'maxlength': 25, name: "model", id: "model", class: "form-control"});
		modelStr.appendChild(inputField);

			let newModelSpan = cTag('span', {'data-toggle':"tooltip", 'data-original-title': Translate('Model List'), id: "addNewModel", class: "input-group-addon cursor"});
			newModelSpan.addEventListener('click', addNewModel);
			newModelSpan.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('List'));
		modelStr.appendChild(newModelSpan);

		if(modelStr.style.display === 'none'){modelStr.style.display = '';}
		document.getElementById("model").value = '';
		document.getElementById("model").focus();
	}
	else{
		AJget_modelOpt();
	}
}

async function changeRepairInfo(customFields){
	const currentstatus = document.getElementById("repairs_status").value;
	let readonlystr = '';
	if(currentstatus==='Invoiced' || currentstatus==='Cancelled'){
		readonlystr = ' readonly';
	}
	const repairs_id = document.getElementById("repairs_id").value;

	if(repairs_id>0){
		const jsonData = {};
		jsonData['customFields'] = customFields;
		jsonData['repairs_id'] = repairs_id;
	
		const url = '/'+segment1+'/showRepairsData';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.length===1){
				showTopMessage('alert_msg', Translate('Could not found repair information'));
			}
			else{
				const formDialog = cTag('div');
					const repairInfoForm = cTag('form', {'action': "#", name: "frmChangeRepairInfo", id: "frmChangeRepairInfo", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
						/* const errorMessage = cTag('div', {class: "errormsg", id: "errorChangeRepairInfo"});
					repairInfoForm.appendChild(errorMessage); */

					let tabs, tab1;
					if(customFields>0){
						tabs = cTag('div', {id: "tabs", 'style': "max-height: 600px; padding: 0;"});
							let ulTab = cTag('ul');
								let liTabs1 = cTag('li');
									let basicInfoTab = cTag('a', {'href': "#tabs-1"});
									basicInfoTab.innerHTML = Translate('Basic Info');
								liTabs1.appendChild(basicInfoTab);
							ulTab.appendChild(liTabs1);
	
								let liTabs2 = cTag('li');
									let customTab =  cTag('a', {'href': "#tabs-2"});
									customTab.innerHTML = Translate('Custom Fields');
								liTabs2.appendChild(customTab)
							ulTab.appendChild(liTabs2);
						tabs.appendChild(ulTab);
						repairInfoForm.appendChild(tabs);
						tab1 = cTag('div', {class: "columnXS12", id: "tabs-1", 'align': "left"});
					}
					else{
						tabs = cTag('div', {class: "flexSpaBetRow"});
                    	tab1 = cTag('div', {class: "columnXS12"});
					}

						let checked, requred, display, errorSpan, inputField;
						const problemRow = cTag('div', {class: "flex", 'style': "align-items: center;"});
							const problemTitle = cTag('div', {class: "columnSM4"});
								const problemLabel = cTag('label', {'for': "problem"});
								problemLabel.innerHTML = Translate('Problem');
									errorSpan = cTag('span', {class: "errormsg"});
									errorSpan.innerHTML = '*';
								problemLabel.appendChild(errorSpan);
							problemTitle.appendChild(problemLabel);
						problemRow.appendChild(problemTitle);
							const problemDropDown = cTag('div', {class: "columnSM8"});
								let problemInGroup = cTag('div', {class: "input-group", id: "problemsStr"});
									let selectProblem = cTag('select', {class: "form-control", name: "problem", id: "problem", title: "Problem"});
										let problemOption = cTag('option', {'value': ""});
									selectProblem.appendChild(problemOption);
									setOptions(selectProblem, data.problemOptions, 0, 1);
								problemInGroup.appendChild(selectProblem);
									inputField = cTag('input', {'type': "text", 'value': "", 'maxlength': 35, name: "problem_name", id: "problem_name", class: "form-control", 'style': "display: none;"});
								problemInGroup.appendChild(inputField);
									const plusSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'data-original-title': Translate('Add New Problem')});
									plusSpan.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New'));
								problemInGroup.appendChild(plusSpan);
							problemDropDown.appendChild(problemInGroup);
							problemDropDown.appendChild(cTag('div', {id: "error_problem", class: "errormsg"}));
						problemRow.appendChild(problemDropDown);
					tab1.appendChild(problemRow);

						const dueDateRow = cTag('div', {class: "flex", 'style': "align-items: center;"});
							const dueDateTitle = cTag('div', {class: "columnSM4"});
								const dueDateLabel = cTag('label', {'for': "due_datetime"});
								dueDateLabel.innerHTML = Translate('Due Date');
							dueDateTitle.appendChild(dueDateLabel);
						dueDateRow.appendChild(dueDateTitle);
							const dateTimeContainer = cTag('div', {class: "columnSM8 flex"});
								const dateColumn = cTag('div', {class: "columnSM6"});
									inputField = cTag('input', {'type': "text", class: 'form-control', name: "due_datetime", id: "due_datetime", 'value': DBDateToViewDate(data.due_datetime), 'maxlength': 10});
									if(readonlystr!==''){
										inputField.setAttribute('required', "required");
									}
								dateColumn.appendChild(inputField);
								const timeColumn = cTag('div', {class: "columnSM6"});
									inputField = cTag('input', {'type': "text", 'placeholder': Translate('Time'), class: 'form-control', name: "due_time", id: "due_time", 'value': data.due_time, 'maxlength': 10});
									if(readonlystr!==''){
										inputField.setAttribute('required', "required");
									}
								timeColumn.appendChild(inputField);
							dateTimeContainer.append(dateColumn,timeColumn,cTag('div', {id: "error_duedate", class: "errormsg"}));
						dueDateRow.appendChild(dateTimeContainer);
					tab1.appendChild(dueDateRow);

					let notifyChecked = 0;
					if(data.notify_default_email !=='' || data.notify_default_sms !==''){
						const notifyRow = cTag('div', {class: "flex"});
							const sendNotificationTitle = cTag('div', {class: "columnSM4"});
								const sendNotificationLabel = cTag('label');
								sendNotificationLabel.innerHTML = Translate('Send Notifications');
							sendNotificationTitle.appendChild(sendNotificationLabel);
						notifyRow.appendChild(sendNotificationTitle);
							const notifyColumn = cTag('div', {class: "columnSM8"});
							if(data.notify_default_email !==''){
								checked = false;
								requred = false;
								display = 'none';
								if(data.notify_how===1){
									notifyChecked = 1;
									display = '';
									checked = true;
									requred = true;
								}
								let emailRow = cTag('div', {class: "flex"});
									const emailColumn = cTag('div', {class: "columnSM5"});
										let emailLabel = cTag('label', {'for': "notify_how_email"});
											inputField = cTag('input', {'data-already-checked':checked?true:false, class: "notify_how", 'type': "radio", name: "notify_how", id: "notify_how_email", 'value': 1});
											if(checked){inputField.checked = true;}
										emailLabel.appendChild(inputField);
										emailLabel.append(' '+Translate('Via Email'));
									emailColumn.appendChild(emailLabel);
								emailRow.appendChild(emailColumn);

									const notifyEmailColumn = cTag('div', {class: "columnSM7"});
										inputField = cTag('input', {'type': "text", name: "notify_email", id: "notify_email", class: "form-control",'style':`display:${display}`, 'placeholder': Translate('Email'), title: Translate('Email'), 'value': data.notify_email});
										if(requred){inputField.required = true;}
									notifyEmailColumn.appendChild(inputField);
										let errorEmail = cTag('div', {id: "error_notify_email", class: "errormsg"});
										inputField.addEventListener('blur',function(){
											if(this.value!='' && !emailcheck(this.value)) errorEmail.innerHTML = 'Invalid Email'
										});
										inputField.addEventListener('focus',function(){errorEmail.innerHTML = ''})
									notifyEmailColumn.appendChild(errorEmail);
								emailRow.appendChild(notifyEmailColumn);
								notifyColumn.appendChild(emailRow);
							}

							if(data.notify_default_sms !==''){
								checked = false;
								requred = false;
								display = 'none';
								if(data.notify_how===2){
									notifyChecked = 2;
									display = '';
									checked = true;
									requred = true;
								}
								let smsRow = cTag('div', {class: "flex"});
									const smsColumn = cTag('div', {class: "columnSM5"});
										let smsLabel = cTag('label', {'for': "notify_how_sms"});
											inputField = cTag('input', {'data-already-checked':checked?true:false,class: "notify_how", 'type': "radio", name: "notify_how", id: "notify_how_sms", 'value': 2});
											inputField.addEventListener('click',()=>{if(document.getElementById('error_notify_email')) document.getElementById('error_notify_email').innerHTML=''})
											if(checked){inputField.checked = true;}
										smsLabel.appendChild(inputField);
										smsLabel.append(' '+Translate('Via SMS'));
									smsColumn.appendChild(smsLabel);
								smsRow.appendChild(smsColumn);

									const smsField = cTag('div', {class: "columnSM7"});
										inputField = cTag('input', {'type': "text", name: "notify_sms", id: "notify_sms", class: "form-control",'style':`display:${display}`, 'placeholder':Translate('Mobile Number'), title: Translate('Mobile Number'), 'value': data.notify_sms});
										if(requred){inputField.required = true;}
										inputField.addEventListener('keyup',function(event) {
											if(!checkPhone("notify_sms", 0)) this.value = this.value.slice(0,-1);
										});
									smsField.appendChild(inputField);
										let errorSms = cTag('div', {id: "error_notify_sms", class: "errormsg"});
									smsField.appendChild(errorSms);
								smsRow.appendChild(smsField);
								notifyColumn.appendChild(smsRow);
							}
						notifyRow.appendChild(notifyColumn);
						tab1.appendChild(notifyRow);
					}

						const passwordRow = cTag('div', {class: "flex", 'style': "align-items: center;"});
							const passwordTitle = cTag('div', {class: "columnSM4"});
								const passwordLabel = cTag('label', {'for': "lock_password", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('Does this device have a password lock from the customer? If yes, enter it here')});
								passwordLabel.innerHTML = Translate('Password');
							passwordTitle.appendChild(passwordLabel);
						passwordRow.appendChild(passwordTitle);
							const passwordField = cTag('div', {class: "columnSM8"});
								inputField = cTag('input', {'type': "text", class: "form-control", name: "lock_password", id: "lock_password", 'value': data.lock_password, 'maxlength': 20});
								if(readonlystr!==''){
									inputField.setAttribute('required', "required");
								}
							passwordField.appendChild(inputField);
						passwordRow.appendChild(passwordField);
					tab1.appendChild(passwordRow);

						const binLocationRow = cTag('div', {class: "flex", 'style': "align-items: center;"});
							const binLocationTitle = cTag('div', {class: "columnSM4"});
								const binLocationLabel = cTag('label', {'for': "bin_location"});
								binLocationLabel.innerHTML = Translate('Bin Location');
							binLocationTitle.appendChild(binLocationLabel);
						binLocationRow.appendChild(binLocationTitle);
							const binLocationField = cTag('div', {class: "columnSM8"});
								inputField = cTag('input', {'type': "tel", class: "form-control", name: "bin_location", id: "bin_location", 'value': data.bin_location, 'maxlength': 20});
							binLocationField.appendChild(inputField);
						binLocationRow.appendChild(binLocationField);
					tab1.appendChild(binLocationRow);

						const technicianRow = cTag('div', {class: "flex", 'style': "align-items: center;"});
							const technicianTitle = cTag('div', {class: "columnSM4"});
								const technicianLabel = cTag('label', {'for': "assign_to"});
								technicianLabel.innerHTML = Translate('Technician');
									errorSpan = cTag('span', {class: "errormsg"});
									errorSpan.innerHTML = '*';
								technicianLabel.appendChild(errorSpan);
							technicianTitle.appendChild(technicianLabel);
						technicianRow.appendChild(technicianTitle);
							const technicianDropDown = cTag('div', {class: "columnSM8"});
								let selectAssign = cTag('select', {class: "form-control required", name: "assign_to", id: "assign_to", title: Translate('Technician')});
								setOptions(selectAssign,data.technicianOptions,1,1);
							technicianDropDown.appendChild(selectAssign);
							technicianDropDown.appendChild(cTag('div', {id: "error_assign_to", class: "errormsg"}));
						technicianRow.appendChild(technicianDropDown);
					tab1.appendChild(technicianRow);

						const salesManRow = cTag('div', {class: "flex", 'style': "align-items: center;"});
							const salesManColumn = cTag('div', {class: "columnSM4"});
								const salesManLabel = cTag('label', {'for': "employee_id"});
								salesManLabel.innerHTML = Translate('Salesman');
									errorSpan = cTag('span', {class: "errormsg"});
									errorSpan.innerHTML = '*';
								salesManLabel.appendChild(errorSpan);
							salesManColumn.appendChild(salesManLabel);
						salesManRow.appendChild(salesManColumn);
							const salesManDropDown = cTag('div', {class: "columnSM8"});
								let selectEmployee = cTag('select', {class: "form-control required", name: "employee_id", id: "employee_id", title: Translate('Salesman')});
								setOptions(selectEmployee,data.technicianOptions,1,1);
							salesManDropDown.appendChild(selectEmployee);
							salesManDropDown.appendChild(cTag('div', {id: "error_employee_id", class: "errormsg"}));
						salesManRow.appendChild(salesManDropDown);
					tab1.appendChild(salesManRow);
					tabs.appendChild(tab1);

					if(customFields>0){
						let tab2 = cTag('div', {class: "columnXS12", id: "tabs-2"});
						generateCustomeFields(tab2,data.customFieldsData);
                        tabs.appendChild(tab2);
					}					
					repairInfoForm.appendChild(tabs);

					//============Final append on Popup=========//
						inputField = cTag('input', {'type': "hidden", name: "customFields", id: "customFields", 'value': customFields});
					repairInfoForm.appendChild(inputField);
						inputField = cTag('input', {'type': "hidden", name: "repairs_id", id: "repairs_id", 'value': data.repairs_id});
					repairInfoForm.appendChild(inputField);
						inputField = cTag('input', {'type': "hidden", name: "pos_id", id: "pos_id", 'value': data.pos_id});
					repairInfoForm.appendChild(inputField);
						inputField = cTag('input', {'type': "hidden", name: "notifyChecked", id: "notifyChecked", 'value': notifyChecked});
					repairInfoForm.appendChild(inputField);
				formDialog.appendChild(repairInfoForm);

				popup_dialog600(Translate('Repair Information'),formDialog,Translate('Save'),saveChangeRepairInfo);
				
				setTimeout(function() {
					if(document.getElementById("problem")){
						document.getElementById("problem").value = data.problem;
					}
					if(document.getElementById("assign_to")){
						document.getElementById("assign_to").value = data.assign_to;
					}
					if(document.getElementById("employee_id")){
						document.getElementById("employee_id").value = data.employee_id;
					}
					if(customFields>0){
						if(document.querySelector("#tabs")) document.querySelector("#tabs").activateTab(0);						
					}
					
					if(document.querySelectorAll(".notify_how").length>0){
						document.querySelectorAll(".notify_how").forEach(oneRowObj=>{
							oneRowObj.addEventListener('click', checkNotifyEdit);
						});
					}
					if(document.getElementsByClassName("DateField").length>0){
						date_picker('.DateField');
					}
					if(document.getElementById("due_datetime")){
						date_picker('#due_datetime');
					}
					callShowInputOrSelect();
					applySanitizer(formDialog);
				}, 500);
				formDialog.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
			}
		}
	}
	return true;
}

function checkNotifyEdit(event){
	let notify_how, notifyChecked, notify_email, notify_sms, email;
	let notify_howCheck = 0;
	notify_how = 0;

	if(event && this.getAttribute('data-already-checked')==='true'){
        this.checked = false;
        this.setAttribute('data-already-checked',false);
        if(this.id === 'notify_how_sms'){
            notify_sms = document.getElementById("notify_sms");
            notify_sms.style.display = 'none';
            notify_sms.required = false;
            notify_sms.value = '';
        }
        else if(this.id === 'notify_how_email'){
            notify_email = document.getElementById("notify_email");
            notify_email.style.display = 'none';
            notify_email.required = false;
            notify_email.value = '';
        }
    }

	if(document.getElementById("notify_how_email")){
		if(document.getElementById("notify_how_email").checked){
			notify_how = 1;
			this.setAttribute('data-already-checked',true);
            if(document.getElementById("notify_how_sms")) document.getElementById("notify_how_sms").setAttribute('data-already-checked',false);
        }
	}
	if(document.getElementById("notify_how_sms")){
		if(document.getElementById("notify_how_sms").checked){
			notify_how = 2;
			this.setAttribute('data-already-checked',true);
            if(document.getElementById("notify_how_email")) document.getElementById("notify_how_email").setAttribute('data-already-checked',false);
		}
	}
	if(notify_how>0){		
		notify_howCheck = 1;
	}
	
	notifyChecked = 0;
	if(document.getElementById("notifyChecked")){
		notifyChecked = document.getElementById("notifyChecked").value;
	}

	if(notify_howCheck>0 && notifyChecked !== notify_how){
		if(notify_how===1){
			if(document.getElementById("notify_email")){
				notify_email = document.getElementById("notify_email");
				if(notify_email.style.display === 'none'){notify_email.style.display = '';}
				if(!notify_email.getAttribute('required')){
					notify_email.setAttribute('required', 'required');
				}				
			}
			if(document.getElementById("notify_sms")){
				notify_sms = document.getElementById("notify_sms");
				if(notify_sms.style.display !== 'none'){notify_sms.style.display = 'none';}
				if(notify_sms.getAttribute('required')){
					notify_sms.removeAttribute('required');
				}
				notify_sms.value = '';
			}
		}
		else if(notify_how===2){
			if(document.getElementById("notify_email")){
				notify_email = document.getElementById("notify_email");
				if(notify_email.style.display !== 'none'){notify_email.style.display = 'none';}
				notify_email.required = false;
				notify_email.value = '';
			}
			if(document.getElementById("notify_sms")){
				notify_sms = document.getElementById("notify_sms");
				if(notify_sms.style.display === 'none'){notify_sms.style.display = '';}
				notify_sms.required = true;
			}
		}
		else{
			if(document.getElementById("notify_email")){
				notify_email = document.getElementById("notify_email");
				if(notify_email.style.display !== 'none'){notify_email.style.display = 'none';}
				notify_email.required = false;
				notify_email.value = '';
			}
			if(document.getElementById("notify_sms")){
				notify_sms = document.getElementById("notify_sms");
				if(notify_sms.style.display !== 'none'){notify_sms.style.display = 'none';}
				notify_sms.required = false;
				notify_sms.value = '';
			}
			
			document.querySelectorAll(".notify_how").forEach(oneRowObj=>{oneRowObj.checked = false;});

			document.getElementById("notifyChecked").value = 0;
			notify_how=0;
		}		
	}
	else if(notify_howCheck>0 && notifyChecked === notify_how){
		document.querySelectorAll(".notify_how").forEach(oneRowObj=>{oneRowObj.checked = false;});
		document.getElementById("notifyChecked").value = 0;
		if(document.getElementById("notify_email")){
			notify_email = document.getElementById("notify_email");
			if(notify_email.style.display !== 'none'){notify_email.style.display = 'none';}
			notify_email.required = false;
			notify_email.value = '';
		}
		if(document.getElementById("notify_sms")){
			notify_sms = document.getElementById("notify_sms");
			if(notify_sms.style.display !== 'none'){notify_sms.style.display = 'none';}
			notify_sms.required = false;
			notify_sms.value = '';
		}
	}
	if(notify_how>0){
		if(document.getElementById("customer_id").value !=='' && document.getElementById("customer_id").value>0){
			email = '';
			if(document.getElementById("customeremail")){email = document.getElementById("customeremail").innerHTML;}
			let phoneno = '';
			if(document.getElementById("phoneno")){phoneno = document.getElementById("phoneno").innerHTML;}
			if(notify_how===1 && document.getElementById('notify_email')){
				document.getElementById('notify_email').value = email;	
				if(document.getElementById('notify_sms')) document.getElementById('notify_sms').value = '';					
			}
			else if(document.getElementById("notify_sms")){
				document.getElementById('notify_sms').value = phoneno;
				if(document.getElementById('notify_email')) document.getElementById('notify_email').value = '';		
			}
		}
	}
	notify_how = 0;
	if(document.getElementById("notify_how_email")){
		if(document.getElementById("notify_how_email").checked){notify_how = 1;}
	}
	if(document.getElementById("notify_how_sms")){
		if(document.getElementById("notify_how_sms").checked){notify_how = 2;}
	}
	document.getElementById('notifyChecked').value = notify_how;
}

async function checkNotify(event){
	let notify_how, notifyChecked, notify_email, notify_sms;
	let notify_howCheck = 0;
	notify_how = 0;

    if(event && this.getAttribute('data-already-checked')==='true'){
        this.checked = false;
        this.setAttribute('data-already-checked',false);
        if(this.id === 'notify_how_sms'){
            notify_sms = document.getElementById("notify_sms");
            notify_sms.style.display = 'none';
            notify_sms.required = false;
            notify_sms.value = '';
        }
        else if(this.id === 'notify_how_email'){
            notify_email = document.getElementById("notify_email");
            notify_email.style.display = 'none';
            notify_email.required = false;
            notify_email.value = '';
        }
    }

	if(document.getElementById("notify_how_email")){
		if(document.getElementById("notify_how_email").checked){
            notify_how = 1;
            this.setAttribute('data-already-checked',true);
            if(document.getElementById("notify_how_sms")) document.getElementById("notify_how_sms").setAttribute('data-already-checked',false);
        }
	}
	if(document.getElementById("notify_how_sms")){
		if(document.getElementById("notify_how_sms").checked){
            notify_how = 2;
            this.setAttribute('data-already-checked',true);
            if(document.getElementById("notify_how_email")) document.getElementById("notify_how_email").setAttribute('data-already-checked',false);
        }
	}
	if(notify_how>0){		
		notify_howCheck = 1;
	}
	
	notifyChecked = 0;
	if(document.getElementById("notifyChecked")){
		notifyChecked = document.getElementById("notifyChecked").value;
	}

	if(notify_howCheck>0 && notifyChecked !== notify_how){
		
		if(notify_how===1){
			if(document.getElementById("notify_email")){
				notify_email = document.getElementById("notify_email");
				if(notify_email.style.display === 'none'){notify_email.style.display = '';}
				notify_email.required = true;
			}
			if(document.getElementById("notify_sms")){
				notify_sms = document.getElementById("notify_sms");
				if(notify_sms.style.display !== 'none'){notify_sms.style.display = 'none';}
				notify_sms.required = false;
				notify_sms.value = '';
			}
		}
		else if(notify_how===2){
			if(document.getElementById("notify_email")){
				notify_email = document.getElementById("notify_email");
				if(notify_email.style.display !== 'none'){notify_email.style.display = 'none';}
				notify_email.required = false;
				notify_email.value = '';
			}
			if(document.getElementById("notify_sms")){
				notify_sms = document.getElementById("notify_sms");
				if(notify_sms.style.display === 'none'){notify_sms.style.display = '';}
				notify_sms.required = true;
			}
		}
		else{
			if(document.getElementById("notify_email")){
				notify_email = document.getElementById("notify_email");
				if(notify_email.style.display !== 'none'){notify_email.style.display = 'none';}
				notify_email.required = false;
				notify_email.value = '';
			}
			if(document.getElementById("notify_sms")){
				notify_sms = document.getElementById("notify_sms");
				if(notify_sms.style.display !== 'none'){notify_sms.style.display = 'none';}
				notify_sms.required = false;
				notify_sms.value = '';
			}
			
			document.querySelectorAll(".notify_how").forEach(oneRowObj=>{oneRowObj.checked = false;});

			document.getElementById("notifyChecked").value = 0;
			notify_how=0;
		}
		
		if(notify_how>0){
			if(document.getElementById("customer_id").value !=='' && document.getElementById("customer_id").value>0){
				let frompage = segment1;

				const jsonData = {};
				jsonData['customers_id'] = document.getElementById("customer_id").value;

				const url = '/'+segment1+'/AJget_CustomersPopup';
				fetchData(afterFetch,url,jsonData);

				function afterFetch(data){
					if(document.getElementsByClassName("notify_how").length>0){                                                    
						if(notify_how===1){
							document.getElementById('notify_email').value = data.email;						
						}
						else if(document.getElementById("notify_sms")){
							document.getElementById('notify_sms').value = data.contact_no;
						}												
					}
				}
				document.getElementById('notifyChecked').value = notify_how;
			}
			else{				
				if(document.getElementById("notify_email")){
					notify_email = document.getElementById("notify_email");
					if(notify_email.style.display !== 'none'){notify_email.style.display = 'none';}
					notify_email.required = false;
					notify_email.value = '';
				}
				if(document.getElementById("notify_sms")){
					notify_sms = document.getElementById("notify_sms");
					if(notify_sms.style.display !== 'none'){notify_sms.style.display = 'none';}
					notify_sms.required = false;
					notify_sms.value = '';
				}
				document.querySelectorAll(".notify_how").forEach(oneRowObj=>{oneRowObj.checked = false;});

				document.getElementById("notifyChecked").value = 0;

				showTopMessage('alert_msg', Translate('Missing customer name'));
                if(document.getElementById("notify_how_email")) document.getElementById("notify_how_email").setAttribute('data-already-checked',false);
                if(document.getElementById("notify_how_sms")) document.getElementById("notify_how_sms").setAttribute('data-already-checked',false);

				if(document.getElementById("customer_name")){
					document.getElementById("customer_name").focus();
				}
			}
		}
	}
	else if(notify_howCheck>0 && notifyChecked === notify_how){
		document.querySelectorAll(".notify_how").forEach(oneRowObj=>{oneRowObj.checked = false;});
		if(document.getElementById("notify_email")){
			notify_email = document.getElementById("notify_email");
			if(notify_email.style.display !== 'none'){notify_email.style.display = 'none';}
			notify_email.required = false;
			notify_email.value = '';
		}
		if(document.getElementById("notify_sms")){
			notify_sms = document.getElementById("notify_sms");
			if(notify_sms.style.display !== 'none'){notify_sms.style.display = 'none';}
			notify_sms.required = false;
			notify_sms.value = '';
		}
	}
	notify_how = 0;
	if(document.getElementById("notify_how_email")){
		if(document.getElementById("notify_how_email").checked){notify_how = 1;}
	}
	if(document.getElementById("notify_how_sms")){
		if(document.getElementById("notify_how_sms").checked){notify_how = 2;}
	}
	document.getElementById('notifyChecked').value = notify_how;
}

async function saveChangeRepairInfo(hidePopup){
	let invalidDueDate = Array.from(document.getElementById('popup').querySelectorAll('#due_datetime')).filter(item=>{
		if(item.value!=='' && validDate(item.value)===false) return item;
	})
	if(invalidDueDate.length>0){
		document.getElementById("error_duedate").innerHTML = 'Invalid Date';
		document.querySelector("#tabs").activateTab(0);
		invalidDueDate[0].focus();
		return;
	}
	// let invalidDate = Array.from(document.getElementById('popup').querySelectorAll('.DateField')).filter(item=>{
	// 	if(item.value!=='' && validDate(item.value)===false) return item;
	// })
	// if(invalidDate.length>0){
	// 	document.getElementById("errorChangeRepairInfo").innerHTML = 'Invalid Date';
	// 	document.querySelector("#tabs").activateTab(1);
	// 	invalidDate[0].focus();
	// 	return;
	// }

	let errorIdObj = document.getElementById("error_problem");
	errorIdObj.innerHTML = '';
	
	let problem = document.getElementById("problem");
	let problem_name = document.getElementById("problem_name");
	if(problem.value==='' && problem_name.value===''){
		if(problem.style.display === 'none'){
			problem_name.focus();
			problem_name.classList.add('errorFieldBorder');
		}
		else{
			problem.focus();
			problem_name.classList.remove('errorFieldBorder');
		}

		if(document.querySelector("#tabs")) document.querySelector("#tabs").activateTab(0);
		errorIdObj.innerHTML = 'Missing Problem';
		return false;
	}
	
	if(document.getElementById('notify_how_sms') && document.getElementById('notify_how_sms').checked){
		if(document.getElementById('notify_sms').value===''){
			document.getElementById('error_notify_sms').innerHTML = 'Mobile Number is missing';
			if(document.querySelector("#tabs")) document.querySelector("#tabs").activateTab(0);
			return false;
		}
	}
	else if(document.getElementById('notify_how_email') && document.getElementById('notify_how_email').checked){
		if(document.getElementById('notify_email').value===''){
			document.getElementById('error_notify_email').innerHTML = 'Email is missing';
			if(document.querySelector("#tabs")) document.querySelector("#tabs").activateTab(0);
			return false;
		}
	}
	
	let validCustomFields = validifyCustomField(1);
	if(!validCustomFields) return;

	actionBtnClick('.btnmodel', Translate('Saving'), 1);

    const url = '/'+segment1+'/saveChangeRepairInfo';
	fetchData(afterFetch,url,document.getElementById('frmChangeRepairInfo'),'formData');

    function afterFetch(data){
		if(data.savemsg==='error'){
			showTopMessage('error_msg', Translate('Error occured while save changes this repair information.'));
		}
		else{
			document.querySelector('#problem_label').innerHTML = data.problem;
			document.querySelector('#due_date').innerHTML = DBDateToViewDate(data.due_datetime, 0, 1)+' '+data.due_time;
			if(data.notify_how ===1){
               document.querySelector('#notification').innerHTML = data.notify_email;
            }
            else if(data.notify_how ===2){
               document.querySelector('#notification').innerHTML = data.notify_sms;
            }
			document.querySelector('#password').innerHTML = data.lock_password;
			document.querySelector('#bin_locat').innerHTML = data.bin_location;
			document.querySelector('#technicia').innerHTML = data.technicianName;
			document.querySelector('#salesman').innerHTML = data.salesmanName;

            if(data.rCustomFields>0){
				const ul = document.getElementById('showCustomInfo');
				ul.innerHTML = '';
				for (const key in data.rCustomFieldsData){						
						let label = cTag('label');
						label.innerHTML = key+': ';
						const span = cTag('span');
						span.innerHTML = data.rCustomFieldsData[key];
					ul.append(label, span);
				}
            }

			if(document.querySelector('#showFormsDataInfo')){
				let repairs_id = document.querySelector('#frmChangeRepairInfo #repairs_id').value;
				AJget_formsData(repairs_id);
			}
			filter_Repairs_edit();
			hidePopup();
		}
	}
	return false;
}

async function checkRepairsStatus(autoload = 0){
	let repairs_id = document.getElementById("repairs_id").value;
	let currentstatus = document.getElementById("repairs_status").value;
	let oldstatus = document.getElementById("oldrepairs_status").value;
	
	if(currentstatus !== oldstatus || autoload===1){
		saveandshowstatus();
		document.querySelector("#repairs_status").style.background = document.querySelector('#repairs_status').querySelector('option:checked').style.background;
		document.querySelector("#repairs_status").style.color = document.querySelector('#repairs_status').querySelector('option:checked').style.color;

		const jsonData = {};
		jsonData['repairs_id'] = repairs_id;
		jsonData['repairs_status'] = currentstatus;

		const url = '/'+segment1+'/checkStatusNotification';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.notify_how>0){
				const formDialog = cTag('div');
					const divError = cTag('div', {id: "error_customer", class: "errormsg"});
				formDialog.appendChild(divError);
					const repairStatusForm = cTag('form', {'action': "#", name: "frmrepairStatus", id: "frmrepairStatus", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
					let textarea, inputField;
				if(data.notify_how===1){
					// title = _Email;
						const subjectRow = cTag('div', {class: "flexSpaBetRow"});
							const subjectTitle = cTag('div', {class: "columnSM4", 'align': "left"});
								const subjectLabel = cTag('label', {'for': "notify_default_subject"});
								subjectLabel.innerHTML = Translate('Subject');
							subjectTitle.appendChild(subjectLabel);
						subjectRow.appendChild(subjectTitle);
							const subjectField = cTag('div', {class: "columnSM8", 'align': "left"});
								inputField = cTag('input', {'required': "required", 'type': "text", id: "notify_default_subject", name: "notify_default_subject", class: "form-control",  'value': data.notify_default_subject}); 
							subjectField.appendChild(inputField);
						subjectRow.appendChild(subjectField);
					repairStatusForm.appendChild(subjectRow);

						const mailBodyRow = cTag('div', {class: "flexSpaBetRow"});
							const mailBodyTitle = cTag('div', {class: "columnSM4", 'align': "left"});
								const mailBodyLabel = cTag('label', {'for': "notify_default_email"});
								mailBodyLabel.innerHTML = Translate('Mail Body');
							mailBodyTitle.appendChild(mailBodyLabel);
						mailBodyRow.appendChild(mailBodyTitle);
							const mailBodyField = cTag('div', {class: "columnSM8", 'align': "left"});
								textarea = cTag('textarea', {'required': "required", class: "form-control", name: "notify_default_email",id: "notify_default_email",  'rows': 6});
								textarea.innerHTML = data.notify_default_email;
							mailBodyField.appendChild(textarea);
						mailBodyRow.appendChild(mailBodyField);
					repairStatusForm.appendChild(mailBodyRow);

						const emailAddressRow = cTag('div', {class: "flexSpaBetRow"});
							const emailAddressTitle = cTag('div', {class: "columnSM4", 'align': "left"});
								const emailAddressLabel = cTag('label', {'for': "notify_email"});
								emailAddressLabel.innerHTML = Translate('Email Address');
							emailAddressTitle.appendChild(emailAddressLabel);
						emailAddressRow.appendChild(emailAddressTitle);
							const emailAddressField = cTag('div', {class: "columnSM8", 'align': "left"});
								inputField = cTag('input', {'type': "email", 'required': "required", name: "notify_email", id: "notify_email", class: "form-control", 'placeholder': Translate('Email'), 'value': data.notify_email}); 
							emailAddressField.appendChild(inputField);
						emailAddressRow.appendChild(emailAddressField);
					repairStatusForm.appendChild(emailAddressRow);
				}
				else if(data.notify_how===2){
					// title = _Phone;
						const smsRow = cTag('div', {class: "flexSpaBetRow"});
							const smsTitle = cTag('div', {class: "columnSM4", 'align': "left"});
								const smsLabel = cTag('label', {'for': "notify_default_sms"});
								smsLabel.innerHTML = Translate('SMS');
							smsTitle.appendChild(smsLabel);
						smsRow.appendChild(smsTitle);
							const smsField = cTag('div', {class: "columnSM8", 'align': "left"});
								textarea = cTag('textarea', {'required': "required", class: "form-control", name: "notify_default_sms",id: "notify_default_sms",  'rows': 6});
								textarea.innerHTML = data.notify_default_sms;
							smsField.appendChild(textarea);
						smsRow.appendChild(smsField);
					repairStatusForm.appendChild(smsRow);

						const phoneNoRow = cTag('div', {class: "flexSpaBetRow"});
							const phoneNoColumn = cTag('div', {class: "columnSM4", 'align': "left"});
								const phoneNoLabel = cTag('label', {'for': "notify_sms"});
								phoneNoLabel.innerHTML = Translate('Phone No.');
							phoneNoColumn.appendChild(phoneNoLabel);
						phoneNoRow.appendChild(phoneNoColumn);
							const phoneNoField = cTag('div', {class: "columnSM8", 'align': "left"});
								inputField = cTag('input', {'type': "tel", 'required': "required", name: "notify_sms", id: "notify_sms", class: "form-control", 'placeholder': Translate('Phone No.'), 'value': data.notify_sms}); 
							phoneNoField.appendChild(inputField);
						phoneNoRow.appendChild(phoneNoField);
					repairStatusForm.appendChild(phoneNoRow);
				}						
						inputField = cTag('input', {'type': "hidden", name: "repairs_id", id: "repairs_id", 'value': repairs_id});
					repairStatusForm.appendChild(inputField);
						inputField = cTag('input', {'type': "hidden", name: "repairs_status", id: "repairs_status", 'value': currentstatus});
					repairStatusForm.appendChild(inputField);
						inputField = cTag('input', {'type': "hidden", name: "notify_how", id: "notify_how", 'value': data.notify_how});
					repairStatusForm.appendChild(inputField);
				formDialog.appendChild(repairStatusForm);
				
				popup_dialog600(Translate('Send Notifications'),formDialog,Translate('Send'),function(hidePopup) {
					if(data.notify_how>0){
						sendRepairEmailSMS(hidePopup);
					}
				});
				
				setTimeout(function() {		
					if(data.notify_how===1){
						document.getElementById("notify_default_email").focus();
					}
					else if(data.notify_how===2){
						document.getElementById("notify_default_sms").focus();
					}						
				}, 500);
			}
		}
	}
}

async function saveandshowstatus(){
	let repairsStatus;

	const repairs_id = document.getElementById("repairs_id").value;
	const currentstatus = document.getElementById("repairs_status").value;
	repairsStatus = document.getElementById("repairs_status");
	repairsStatus.classList.add('lightYellow');
	repairsStatus.disabled = true;

	const jsonData = {};
	jsonData['repairs_id'] = repairs_id;
	jsonData['status'] = currentstatus;

    const url = '/'+segment1+'/saveandshowstatus';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.action ==='Changed'){
			const repairs_status = document.getElementById("repairs_status");
			const optionList = document.querySelectorAll("#repairs_status option");
			repairs_status.innerHTML = '';
			optionList.forEach(option=>{
				if(option.text !=='Estimate'){
					const optTag = cTag('option', {'value': option.text});
					optTag.innerHTML =  option.text;
					if(option.hasAttribute('style')){
						optTag.setAttribute('style', 'background:'+option.style.backgroundColor+';color:'+option.style.color);
					}
					repairs_status.appendChild(optTag);
				}
			});
			
			document.getElementById("repairs_status").value = currentstatus;
			document.getElementById("oldrepairs_status").value = currentstatus;
			
			loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
			cartsAutoFuncCall();
			filter_Repairs_edit();
			check_andupdatestatustab();
			repairsStatus = document.getElementById("repairs_status");        
			repairsStatus.classList.remove('lightYellow');
			repairsStatus.classList.remove('lightRed');
			repairsStatus.disabled = false;
		}
		else{	
			showTopMessage('error_msg', Translate('Error occured while save this status.'));
			repairsStatus = document.getElementById("repairs_status");        
			repairsStatus.classList.remove('lightYellow');
			repairsStatus.classList.add('lightRed');
			repairsStatus.disabled = false;
		}
	}
}

async function sendRepairEmailSMS(hidePopup){
	const fieldNames = ['notify_default_subject', 'notify_default_email', 'notify_email', 'notify_default_sms', 'notify_sms'];
	fieldNames.forEach(mField => {
		if(document.querySelector('#'+mField)){
			if(document.querySelector('#'+mField).hasAttribute('required') &&  document.querySelector('#'+mField).value === ''){
				document.querySelector('#'+mField).focus();
				return false;
			}
		}
	});

	let repairsStatus;

	const currentstatus = document.getElementById("repairs_status").value;
	actionBtnClick('.btnmodel', Translate('Sending'), 1);
	repairsStatus = document.getElementById("repairs_status");
	repairsStatus.classList.add('lightYellow');
	repairsStatus.disabled = true;

	const jsonData = serialize('#frmrepairStatus');

    const url = '/'+segment1+'/sendRepairEmailSMS';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr==='error'){
			showTopMessage('error_msg', Translate('Error occured while save this status.'));

			repairsStatus = document.getElementById("repairs_status");        
			repairsStatus.classList.remove('lightYellow');
			repairsStatus.classList.add('lightRed');
			repairsStatus.disabled = false;
		}
		else{	
			document.getElementById("oldrepairs_status").value = currentstatus;
			hidePopup();
			repairsStatus = document.getElementById("repairs_status");
			repairsStatus.classList.remove('lightYellow');
			repairsStatus.classList.remove('lightRed');
			repairsStatus.disabled = false;
		
			filter_Repairs_edit();
			check_andupdatestatustab();
		}
		actionBtnClick('.btnmodel', Translate('Send'), 0);
	}
}

function check_andupdatestatustab(){
	const repairs_statusoptions = document.getElementById("repairs_status").innerHTML;
	let currentstatus = document.getElementById("repairs_status").value;
	
	if(currentstatus==='Invoiced' || currentstatus==='Cancelled'){
		let repairsStatus = document.getElementById("repairs_status");
		repairsStatus.innerHTML = '';
			let invoiceOption = cTag('option', {'value': "Invoiced"});
			invoiceOption.innerHTML = Translate('Invoiced');
		repairsStatus.appendChild(invoiceOption);
			let cancelOption = cTag('option', {'value': "Cancelled"});
			cancelOption.innerHTML = Translate('Cancelled');
		repairsStatus.appendChild(cancelOption);
		document.getElementById("repairs_status").value = currentstatus;

		if(document.getElementById("status_cancelled").style.display !== 'none'){
			document.getElementById("status_cancelled").style.display = 'none';
		}
		if(document.getElementById("status_invoiced").style.display !== 'none'){
			document.getElementById("status_invoiced").style.display = 'none';
		}
		if(document.getElementById("status_completed").style.display !== 'none'){
			document.getElementById("status_completed").style.display = 'none';
		}
		 
		if(document.querySelector("#repairs_status").hasAttribute('readonly')){}
		else{
			document.querySelector("#repairs_status").setAttribute('disabled', "disabled"); 
		}
		
		document.querySelectorAll(".invoiceorcompleted").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display !== 'none'){
				oneFieldObj.style.display = 'none';
			}
		});
		document.querySelector("#assign_to").setAttribute('disabled', "disabled"); 
		document.querySelector("#employee_id").setAttribute('disabled', "disabled"); 
	}
	else{
		if(currentstatus==='Finished'){
			if(document.getElementById("status_completed")){
				if(document.getElementById("status_completed").style.display !== 'none'){
					document.getElementById("status_completed").style.display = 'none';
				}
			}

			if(document.getElementById("status_invoiced")){
				if(document.getElementById("status_invoiced").style.display === 'none'){
					document.getElementById("status_invoiced").style.display = '';
				}
			}
		}
		document.getElementById("repairs_status").innerHTML = repairs_statusoptions;
		document.getElementById("repairs_status").value = currentstatus;
	}		
}

function cancelRepair(){
	let totalshipping_qty = 0;
	let hasdata = document.getElementById("invoice_entry_holder").innerHTML.length;
	if(hasdata){
		let pos_cart_idarray = document.getElementsByName("pos_cart_id[]");
		if(pos_cart_idarray.length>0){
			for(let p = 0; p < pos_cart_idarray.length; p++){
				let pos_cart_id = pos_cart_idarray[p].value;
				let shipping_qty = parseFloat(document.getElementById("shipping_qty"+pos_cart_id).value);
				if(shipping_qty==='' || isNaN(shipping_qty)){shipping_qty = 0;}
				if(shipping_qty>0){
					totalshipping_qty += shipping_qty;
				}
			}
		}		
	}
	
	let grand_total = parseFloat(document.getElementById("grand_total").value);
	if(grand_total==='' || isNaN(grand_total)){grand_total = 0.00;}
	
	let amount_due = parseFloat(document.getElementById("amount_due").value);
	if(amount_due==='' || isNaN(amount_due)){amount_due = 0.00;}

	if(totalshipping_qty>0){
		alert_dialog(Translate('Ticket Cancel'), totalshipping_qty+' '+Translate('Item(s) has been delivered for this you can not cancel this ticket.'), Translate('Ok'));
	}
	else if(amount_due===grand_total){
		confirm_dialog(Translate('Ticket Cancel'), Translate('Are you sure you want to cancel this ticket?'), confirmRepairCancelation);
	}
	else{
		alert_dialog(Translate('Ticket Cancel'), Translate('You can not cancel this ticket because a payment has been made'), Translate('Ok'));
	}
}
                        
async function confirmRepairCancelation(hidePopup){
	let pos_id = document.getElementById("pos_id").value;
	let repairs_id = document.getElementById("repairs_id").value;
	const grand_total = parseFloat(document.getElementById("grand_total").value);
	if(grand_total==='' || isNaN(grand_total)){grand_total = 0.00;}
	
	let amount_due = parseFloat(document.getElementById("amount_due").value);
	if(amount_due==='' || isNaN(amount_due)){amount_due = 0.00;}
	
	if(amount_due===grand_total){
		let currentstatus = 'Cancelled';
		document.querySelectorAll(".archive").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display !== 'none'){
				oneFieldObj.style.display = 'none';
			}
		});

		const jsonData = {};
		jsonData['pos_id'] = pos_id;
		jsonData['repairs_id'] = repairs_id;
		jsonData['status'] = currentstatus;

		const url = '/'+segment1+'/AJ_changestatuscancelled';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.returnStr==='error'){
				document.querySelectorAll(".archive").forEach(oneFieldObj=>{
					if(oneFieldObj.style.display === 'none'){
						oneFieldObj.style.display = '';
					}
				});
				showTopMessage('error_msg', Translate('Error occured while save this status.'));
			}
			else{
				hidePopup();
				window.location = '/Repairs/lists';
			}
		}
	}
	else{
		showTopMessage('error_msg', Translate('You can not cancel this ticket because a payment has been made'));
	}
}

function changeThisRepairRow({detail:pos_cart_id}){
	let qty;
	const statusval = document.getElementById("repairs_status").value;
	const item_type = document.getElementById("item_type"+pos_cart_id).value;
	const product_type = document.getElementById("product_type"+pos_cart_id).value;
	let add_description = document.getElementById("add_description"+pos_cart_id).value;
    if(add_description !==''){add_description = add_description.replace(/<br\s*\/?>/gi,'');}
	const sales_price = document.getElementById("sales_price"+pos_cart_id).value;
	const minimum_price = document.getElementById("minimum_price"+pos_cart_id).value;
	qty = document.getElementById("qty"+pos_cart_id).value;
	if(['Finished', 'Invoiced', 'Cancelled'].includes(statusval)){
		qty = document.getElementById("shipping_qty"+pos_cart_id).value;
	}
	const discount_is_percent = document.getElementById("discount_is_percent"+pos_cart_id).value;
	const discount = document.getElementById("discount"+pos_cart_id).value;
	const require_serial_no = parseInt(document.getElementById("require_serial_no"+pos_cart_id).value);
		
	let priceReadonly = '';
	if(document.getElementById("subPermission") && document.getElementById("subPermission").value.includes('cnccp')){
		priceReadonly = ' readonly';
	}
	
	let input, bTag, inputField, errorSpan;
	const formDialog = cTag('div');
		const repairForm = cTag('form', {'action': "#", name: "frmRepairRow", id: "frmRepairRow", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			const errorDiv = cTag('div', {class: "flex"});
				let errorColumn = cTag('div', {class: "columnSM12 error_msg", id: "showErroMsg"});
			errorDiv.appendChild(errorColumn);
		repairForm.appendChild(errorDiv);

			const unitRow = cTag('div', {class: "flex", 'align': "left"});
				const unitTitle = cTag('div', {class: "columnSM3"});
					const unitLabel = cTag('label', {'for': "sales_price"});
					unitLabel.innerHTML = Translate('Unit Price');
				unitTitle.appendChild(unitLabel);
			unitRow.appendChild(unitTitle);
				const unitColumn = cTag('div', {class: "columnSM4"});
					inputField = cTag('input', {'type': "text", 'data-max':'9999999.99','data-format':'d.dd', class: "form-control calculateChangeCartTotal", name: "sales_price", id: "sales_price", 'value': sales_price});
                    if(minimum_price>0) inputField.setAttribute('data-min',minimum_price);
					controllNumericField(inputField, '#errmsg_sales_price');
                    if(priceReadonly!==''){
						inputField.setAttribute('required', "required");
					}
				unitColumn.appendChild(inputField);
				unitColumn.appendChild(cTag('span', {class: "error_msg", id: "errmsg_sales_price"}));
                unitColumn.appendChild(cTag('input', {type: "hidden", id: "minimum_price", value: minimum_price}));
			unitRow.appendChild(unitColumn);
				const unitPriceValue = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "salesPriceStr"});
					bTag.innerHTML = currency+'0.00';
				unitPriceValue.appendChild(bTag);
			unitRow.appendChild(unitPriceValue);
		repairForm.appendChild(unitRow);

			const qtyRow = cTag('div', {class: "flex", 'align': "left"});
				const qtyTitle = cTag('div', {class: "columnSM3"});
					const qtyLabel = cTag('label', {'for': "qty"});
					qtyLabel.innerHTML = Translate('QTY');
						errorSpan = cTag('span', {class: "errormsg"});
						errorSpan.innerHTML = '*';
					qtyLabel.appendChild(errorSpan);
				qtyTitle.appendChild(qtyLabel);
			qtyRow.appendChild(qtyTitle);
				const qtyField = cTag('div', {class: "columnSM4"});
					input = cTag('input', {'type': "text",'data-min':'0','data-max':"9999", 'data-format': "d", class: "form-control calculateChangeCartTotal", name: "qty", id: "qty", 'value': qty});
					controllNumericField(input, '#errmsg_qty');
					if(product_type==='Labor/Services') input.setAttribute('data-format','d.dd')
                    else preventDot(input);
                    if(item_type === 'cellphones' || (item_type === 'product' && require_serial_no === 1)){
						input.setAttribute('readonly', true);
					}
				qtyField.appendChild(input);
					errorSpan = cTag('span', {class: "error_msg", id: "errmsg_qty"});
				qtyField.appendChild(errorSpan);
			qtyRow.appendChild(qtyField);
				const subTotalValue = cTag('div', {class: "columnSM5", 'align': "right"});
				subTotalValue.innerHTML = Translate('Subtotal')+ ': ';
					bTag = cTag('b', {id: "qtyValueStr"});
					bTag.innerHTML = currency+'0.00';
				subTotalValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "qty_value", id: "qty_value", 'value': 0});
				subTotalValue.appendChild(inputField);
			qtyRow.appendChild(subTotalValue);
		repairForm.appendChild(qtyRow);

			const discountRow = cTag('div', {class: "flex", 'align': "left"});
				const discountTitle = cTag('div', {class: "columnSM3"});
					let discountLabel = cTag('label', {'for': "discount"});
					discountLabel.innerHTML = Translate('Discount');
				discountTitle.appendChild(discountLabel);
			discountRow.appendChild(discountTitle);
				const discountField = cTag('div', {class: "columnSM4"});
					let discountInGroup = cTag('div', {class: "input-group"});
						let discountSpan = cTag('span', {class: "input-group-addon cursor", 'style': "min-width: 120px; padding-left: 0; padding-right: 0;"});
							inputField = cTag('input', {id: "discount", name: "discount", 'type': "text",'data-min':'0','data-format':'d.dd', 'data-max': '99.99', 'value': discount, class: "form-control calculateChangeCartTotal", 'style': "text-align: right; min-width: 120px;"});
                            controllNumericField(inputField, '#errmsg_discount');
						discountSpan.appendChild(inputField);
					discountInGroup.appendChild(discountSpan);
						let discountValue = cTag('span', {class: "input-group-addon", 'style': "width: 40px; padding-left: 0; padding-right: 0;"});
							let selectDiscont = cTag('select', {id: "discount_is_percent", name: "discount_is_percent",  class: "form-control bgnone calculateChangeCartTotal", 'style': "width: 60px; padding-left: 0; padding-right: 0;"});
								let perOption = cTag('option', {'value': 1});
								perOption.innerHTML = '%';
							selectDiscont.appendChild(perOption);
								let courencyoption = cTag('option', {'value': 0});
								courencyoption.innerHTML = currency;
							selectDiscont.appendChild(courencyoption);
						discountValue.appendChild(selectDiscont);
					discountInGroup.appendChild(discountValue);
				discountField.appendChild(discountInGroup);
				discountField.appendChild(cTag('span', {class: "error_msg", id: "errmsg_discount"}));
			discountRow.appendChild(discountField);
				const currencyColumn = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "discountValueStr"});
					bTag.innerHTML = currency+'0.00';
				currencyColumn.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "discountvalue", id: "discountvalue", 'value': 0});
				currencyColumn.appendChild(inputField);
			discountRow.appendChild(currencyColumn);
		repairForm.appendChild(discountRow);
		repairForm.appendChild(cTag('hr'));

			const totalRow = cTag('div', {class: "flex", 'align': "left"});
				const  totalValue = cTag('div', {class: "columnSM12", 'align': "right"});
					bTag = cTag('b');
					bTag.innerHTML = Translate('Total')+': ';
				totalValue.appendChild(bTag);
					bTag = cTag('b', {id: "totalValueStr"});
					bTag.innerHTML = currency+'0.00';
				totalValue.appendChild(bTag);
                
					inputField = cTag('input', {'type': "hidden", name: "unitPrice", id: "unitPrice", 'value': 0});
				totalValue.appendChild(inputField);
			totalRow.appendChild(totalValue);
		repairForm.appendChild(totalRow);

			const addDescription = cTag('div', {class: "flex", 'align': "left"});
				const addDescriptionColumn = cTag('div', {class: "columnSM3"});
					const addDescriptionLabel = cTag('label', {'for': "add_description"});
					addDescriptionLabel.innerHTML = Translate('Additional Description');
				addDescriptionColumn.appendChild(addDescriptionLabel);
			addDescription.appendChild(addDescriptionColumn);
				let addDescriptionField = cTag('div', {class: "columnSM9"});
					const textarea = cTag('textarea', {class: "form-control", name: "add_description", id: "add_description", placeholder:Translate('Additional Description'), 'rows': 2, 'cols': 20});
				addDescriptionField.appendChild(textarea);
			addDescription.appendChild(addDescriptionField);
		repairForm.appendChild(addDescription);

			inputField = cTag('input', {'type': "hidden", name: "pos_cart_idvalue", id: "pos_cart_idvalue", 'value': pos_cart_id});
		repairForm.appendChild(inputField);
	formDialog.appendChild(repairForm);

	popup_dialog600(Translate('Update Repair Cart'),formDialog,Translate('Save'),updateCartData);
			
	setTimeout(function() {
		document.getElementById("sales_price").focus();		
		document.getElementById("discount_is_percent").value = discount_is_percent;		
		document.getElementById("add_description").value = add_description;

		calculateChangeCartTotal();

		document.querySelectorAll(".calculateChangeCartTotal").forEach(oneFieldObj=>{
			if(oneFieldObj.tagName==='INPUT'){
				oneFieldObj.addEventListener('keyup', calculateChangeCartTotal);
				oneFieldObj.addEventListener('change', calculateChangeCartTotal);
			}
			else if(oneFieldObj.tagName==='SELECT'){
				oneFieldObj.addEventListener('change', calculateChangeCartTotal);
			}
		});
		applySanitizer(formDialog);
	}, 500);
	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));	
}

function completeRepair(event,ignoreOverselling){	
	let currentstatus = document.getElementById("repairs_status").value;														
	if(currentstatus !=='Finished'){
		document.getElementById("repairs_status").focus();
		showTopMessage('alert_msg', Translate('You must set the status to Finished before you can complete it.'));
		return false;
	}
	
	if(document.getElementById('showCustomInfo') && document.getElementById('showCustomInfo').querySelectorAll('li span.requiredCustomField').length){
		const requiredCustomFields = [...document.getElementById('showCustomInfo').querySelectorAll('li span.requiredCustomField')];
		for (let index = 0; index < requiredCustomFields.length; index++) {
			if(requiredCustomFields[index].innerText===''){
				document.querySelector("#ticketTabs").activateTab(1);
				const msg = requiredCustomFields[index].parentNode.querySelector('label').innerText.slice(0,-3);
				showTopMessage('alert_msg', '"'+msg+ '" '+ 'is a required Custom Field');
				return false;
			}
		}
	}

	if(document.querySelectorAll(".form_required").length>0){
		let requiredFieldName = document.getElementsByClassName("form_required")[0].value;
		showTopMessage('alert_msg', '"'+requiredFieldName+ '" '+ Translate('is a REQUIRED form and you have not edit/updated it yet.'));
		if(document.querySelector("#ticketTabs2")){
			document.querySelector("#ticketTabs").activateTab(2);
		}
		else{
			document.querySelector("#ticketTabs").activateTab(1);
		}
		document.getElementById("newforms_id").focus();
		return false;
	}
	
	if(showCartCompleteBtn()===true){
		if(document.getElementsByName("pos_cart_id[]").length===0){
			showTopMessage('alert_msg', Translate('You need to add at least one product.'));
			return false;
		}
		else{
			//warn if any product oversold
			if(!ignoreOverselling && haveAnyOversoldProduct(completeRepair)) return;

			let amount_due = document.getElementById("amount_due").value;
			document.getElementById("changemethod").value = 'Cash';
			let changeamountofval = 0;
			if(amount_due<0){
				changeamountofval = amount_due;
			}

			let emptyRow, inputField;
			let formDialog = cTag('div');
				emptyRow = cTag('div', {class: "flexSpaBetRow"});
			formDialog.appendChild(emptyRow);

				const currencyFlex = cTag('div', {class: "flexSpaBetRow"});
					let currencyColumn = cTag('div', {class: "columnXS12", 'align': "center"});
						let changeAmountValue = cTag('span', {'style': "color:orange;font-size:48px;font-weight:500", id: "changeamountof"});
						changeAmountValue.innerHTML = addCurrency(-1*changeamountofval);
					currencyColumn.appendChild(changeAmountValue);
				currencyFlex.appendChild(currencyColumn);
			formDialog.appendChild(currencyFlex);

				emptyRow = cTag('div', {class: "flexSpaBetRow"});
			formDialog.appendChild(emptyRow);

				const exchangeMethod = cTag('div', {class: "flexSpaBetRow"});
					const exchangeMethodTitle = cTag('div', {class: "columnSM7", 'align': "left"});
						const exchangeMethodLabel = cTag('label', {'for': "exchangemethod"});
						exchangeMethodLabel.innerHTML = Translate('Choose how the change was given');
					exchangeMethodTitle.appendChild(exchangeMethodLabel);
				exchangeMethod.appendChild(exchangeMethodTitle);
					const exchangeMethodColumn = cTag('div', {class: "columnSM5", 'align': "left"});
						let selectExchangeMethod = cTag('select', {class: "form-control", name: "exchangemethod", id: "exchangemethod"});						
						const methodObj =  document.querySelector("#method");
                    	const methodOpts = methodObj.querySelectorAll('option');
                    	methodOpts.forEach(function(item){		
                    		const oneMethod = item.value;
                    		let addMethod = 0;
                    		if(oneMethod ==='Squareup'){}
                    		else{addMethod++;}
                    
                    		if(addMethod>0){
                    			const methodOptions = cTag('option',{'value':oneMethod});
                    			methodOptions.innerHTML = oneMethod;
                    			selectExchangeMethod.appendChild(methodOptions);
                    		}
                    	});
						selectExchangeMethod.addEventListener('change', function(event){document.getElementById("changemethod").value = event.value;});
					exchangeMethodColumn.appendChild(selectExchangeMethod);
				exchangeMethod.appendChild(exchangeMethodColumn);
			formDialog.appendChild(exchangeMethod);

				const printDropDown = cTag('div', {class: "flexSpaBetRow"});
					const printTypeTitle = cTag('div', {class: "columnSM4", 'align': "left"});
						const printTypeLabel = cTag('label', {'for': "default_invoice_printer1"});
						printTypeLabel.innerHTML = Translate('Choose print type');
					printTypeTitle.appendChild(printTypeLabel);
				printDropDown.appendChild(printTypeTitle);
					const printTypeColumn = cTag('div', {class: "flexStartRow columnSM8", 'align': "left"});
						const fullPagePrint = cTag('label', {class:'columnXS6 '});
							inputField = cTag('input', {'type': "radio", 'value': "Large", id: "default_invoice_printer1", name: "print_type", class: "print_type"});
						fullPagePrint.appendChild(inputField);
						fullPagePrint.append(' '+Translate('Full Page'));
					printTypeColumn.appendChild(fullPagePrint);
						const thermalLabel = cTag('label', {class:'columnXS6 '});
							inputField = cTag('input', {'type': "radio", 'value': "Small", id: "default_invoice_printer2", name: "print_type", class: "print_type"});
						thermalLabel.appendChild(inputField);
						thermalLabel.append(' '+Translate('Thermal'));
					printTypeColumn.appendChild(thermalLabel);
						const emailLabel = cTag('label', {class:'columnXS6 '});
							inputField = cTag('input', {'type': "radio", 'value': "Email", id: "default_invoice_printer3", name: "print_type", class: "print_type"});
						emailLabel.appendChild(inputField);
						emailLabel.append(' '+Translate('Email'));
					printTypeColumn.appendChild(emailLabel);
						const noReceiptLabel = cTag('label', {class:'columnXS6 '});
							inputField = cTag('input', {'type': "radio", 'value': "No Receipt", id: "default_invoice_printer4", name: "print_type", class: "print_type"});
						noReceiptLabel.appendChild(inputField);
						noReceiptLabel.append(' '+Translate('No Receipt'));
					printTypeColumn.appendChild(noReceiptLabel);
				printDropDown.appendChild(printTypeColumn);
			formDialog.appendChild(printDropDown);

				const invoiceEmailRow = cTag('div', {class: "flexSpaBetRow invcustomeremail",'style':'display:none'});
					let invoiceEmailTitle = cTag('div', {class: "columnSM3", 'align': "left"});
						let invoiceEmailLabel = cTag('label', {'for': "invcustomeremail"});
						invoiceEmailLabel.innerHTML = Translate('Email');
							let errorSpan = cTag('span', {class: "errormsg"});
							errorSpan.innerHTML = '*';
						invoiceEmailLabel.appendChild(errorSpan);
					invoiceEmailTitle.appendChild(invoiceEmailLabel);
				invoiceEmailRow.appendChild(invoiceEmailTitle);
					let invoiceEmailField = cTag('div', {class: "columnSM9", 'align': "left"});
						inputField = cTag('input', {'required': "required", 'maxlength': 50, 'type': "email", class: "form-control", name: "invcustomeremail", id: "invcustomeremail", 'value': document.querySelector("#customeremail").innerHTML});
					invoiceEmailField.appendChild(inputField);
				invoiceEmailRow.appendChild(invoiceEmailField);
			formDialog.appendChild(invoiceEmailRow);

				emptyRow = cTag('div', {class: "flexSpaBetRow"});
			formDialog.appendChild(emptyRow);
			
			let title = Translate('Give change amount of');
			let actionbutton = Translate('Complete');

			let print_type;

			popup_dialog600(title,formDialog,actionbutton,function(hidePopup){
				let print_typeselect = 0;
				let print_typeid = document.getElementsByName("print_type");
				print_type = '';
				if(print_typeid.length>0){
					for(let l = 0; l < print_typeid.length; l++){
						if(print_typeid[l].checked===true){
							print_typeselect++;
							print_type = print_typeid[l].value;
						}
					}
				}

				if(print_typeselect===0){
					showTopMessage('alert_msg', Translate('You are missing print type'));
					return false;
				}
				confirmRepairCompletion(print_type,hidePopup);
			});
			
			document.querySelectorAll(".print_type").forEach(item=>{
				item.addEventListener('click',e=>{
					print_type = e.target.value;
					if(print_type==='Email'){
						document.querySelectorAll(".invcustomeremail").forEach(oneFieldObj=>{
							if(oneFieldObj.style.display === 'none'){
								oneFieldObj.style.display = '';
							}
						});
					}
					else{
						document.querySelectorAll(".invcustomeremail").forEach(oneFieldObj=>{
							if(oneFieldObj.style.display !== 'none'){
								oneFieldObj.style.display = 'none';
							}
						});
					}
				});
			})
					
			setTimeout(function() {
				document.getElementById("completed").value = 1;
				print_type = document.getElementById("default_invoice_printer").value;
				if(print_type==='Large'){
					document.getElementById("default_invoice_printer1").checked = true;
				}
				else if(print_type==='Small'){
					document.getElementById("default_invoice_printer2").checked = true;
				}
				else if(print_type==='Email'){
					document.getElementById("default_invoice_printer3").checked = true;
					document.querySelectorAll(".invcustomeremail").forEach(oneFieldObj=>{
						if(oneFieldObj.style.display === 'none'){
							oneFieldObj.style.display = '';
						}
					});
				}				
				else{
					document.getElementById("default_invoice_printer4").checked = true;
				}
				document.getElementById("exchangemethod").focus();
			}, 500);

			return false;
		}
	}
}

async function confirmRepairCompletion(print_type,hidePopup){
	let repairs_id = document.getElementById("repairs_id").value;
	let pos_id = document.getElementById("pos_id").value;
	let completed = document.getElementById("completed").value;
	let changemethod = document.getElementById("changemethod").value;
	let amount_due = document.getElementById("amount_due").value;
	let email = document.getElementById("invcustomeremail").value;
	if(print_type==='Email' && !emailcheck(email)){
		document.getElementById("invcustomeremail").focus();
		return false;
	}

	let printType;
	printType = print_type;
	if(print_type !==''){
		if(print_type ==='Large')
			printType = 'large';
		else if(print_type ==='Small')
			printType = 'small';
	}
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
	
	const jsonData = {};
	jsonData['repairs_id'] = repairs_id;
	jsonData['pos_id'] = pos_id;
	jsonData['completed'] = completed;
	jsonData['changemethod'] = changemethod;
	jsonData['amount_due'] = amount_due;

    const url = '/'+segment1+'/updaterepairscomplete';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr >0){
			if(printType === 'large' || printType === 'small'){
				hidePopup();
				let redirectTo = '/Carts/cprints/'+printType+'/'+data.returnStr;
				if(amount_due <0){redirectTo = redirectTo+'/'+amount_due;}
				
				let day = new Date();
				let w = 900;
				let h = 600;
				let scrl = 1;
				let winl = (screen.width - w) / 2;
				let wint = (screen.height - h) / 2;
				let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
				window.open(redirectTo, '" + id + "', winprops);
				
				setTimeout(function() {
					if(completed>0){
						window.location = '/Repairs/lists/';
					}
					else{
						window.location = '/Repairs/lists/cancelled';
					}
				}, 1000);
			}
			else if(print_type==='Email'){
				if(email !=='' && pos_id>0){
					hidePopup();
					document.getElementById("pos_id").value = pos_id;
					document.getElementById("email_address").value = email;
					emaildetails(false, '/Carts/AJ_sendposmail');
					
					setTimeout(function() {
						if(completed>0){
							window.location = '/Repairs/lists/';
						}
						else{
							window.location = '/Repairs/lists/cancelled';
						}
					}, 1000);
				}
				else{
					actionBtnClick('.btnmodel', Translate('Complete'), 0);
					showTopMessage('alert_msg', Translate('There is no email address for customer.'));
				}
			}						
			else{
				hidePopup();
				window.location = '/Repairs/lists/';
			}
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else{
			actionBtnClick('.btnmodel', Translate('Complete'), 0);
			showTopMessage('alert_msg', Translate('Could not complete this order.'));
		}
	}
	return false;
}

async function createLinkedTicket(repairs_id){
	if(repairs_id>0){
		let createLinkedTicket = document.getElementById("createLinkedTicket");
		createLinkedTicket.innerHTML = Translate('Saving')+'...';
		createLinkedTicket.disabled = true;

		const jsonData = {};
		jsonData['repairs_id'] = repairs_id;

		const url = '/'+segment1+'/createLinkedTicket';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.returnStr==='error'){
				showTopMessage('error_msg', Translate('Error occured while adding new repair! Please try again.'));
			}
			else if(data.newrepairs_id>0){
				window.location = '/Repairs/edit/'+data.newrepairs_id;
			}
			else{
				showTopMessage('error_msg', Translate('Error occured while adding new repair! Please try again.'));
			}
		}
	}
}

function viewPicturePopup(src){
	let w = 1100;
	let h = 850;
	let scrl = 1;
	let winl = (screen.width - w) / 2;
	let wint = (screen.height - h) / 2;
	let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	
	let printWindow = window.open('', '" + id + "', winprops);
	let html = cTag('html');
        let head = cTag('head');
            let titleTag = cTag('title');
            titleTag.innerHTML = 'View Original Picture';
        head.appendChild(titleTag);
		head.appendChild(cTag('meta',{ 'charset':`utf-8`}));
		const style = cTag('style');
            style.append(
                `@page {size: auto;}
                body{ font-family:Arial, sans-serif, Helvetica; min-width:98%; margin:0; padding:1%;background:#fff;}
                table{border-collapse:collapse;}
                .table-bordered td, .table-bordered th { border:1px solid #DDDDDD; padding:8px 10px;}
                .table-bordered td.bgnone {background-color:#FFF;border:0px solid #fff;}`
            );
        head.appendChild(style);
    html.appendChild(head);
		let body = cTag('body');
			let tabelItems = cTag('table', {'style': "width:100%"});
				let tableRow = cTag('tr');
					let tdCol = cTag('td', {'align': "center"});
						let image = cTag('img', {'style': "cursor: zoom-out;", 'click': window.close, 'src': src, 'alt': "Lare Picture"});
					tdCol.appendChild(image);
				tableRow.appendChild(tdCol);
			tabelItems.appendChild(tableRow);
		body.appendChild(tabelItems);
	html.appendChild(body);
	printWindow.document.write("<!DOCTYPE html>");
    printWindow.document.appendChild(html);
	printWindow.document.close();
}

async function AJget_formsData(table_id){
	const jsonData = {};
	jsonData['table_id'] = table_id;

    const url = '/'+segment1+'/AJget_formsData';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		const showFormsDataInfo = document.getElementById("showFormsDataInfo");
		showFormsDataInfo.innerHTML = '';

		let tdCol;
		if(data.returnData && data.returnData.length){
			data.returnData.forEach(item=>{
				let editLink = ()=>AJget_formDataPopup(item.forms_data_id, item.forms_id, item.table_id);
					let formHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'data-title':Translate('Name'),'align':`left` });
							let editViewLink = cTag('a',{ 'class':`anchorfulllink`,'href':`javascript:void(0);`,'click':editLink,'title':Translate('Edit/View') });
							editViewLink.innerHTML = stripslashes(item.form_name);
						tdCol.appendChild(editViewLink);
					formHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'data-title':Translate('Last Update'),'align':`center` });
							let lastEditViewLink = cTag('a',{ 'class':`anchorfulllink`,'href':`javascript:void(0);`,'click':editLink,'title':Translate('Edit/View') });
							lastEditViewLink.innerHTML = DBDateToViewDate(item.last_updated, 0, 1);
						tdCol.appendChild(lastEditViewLink);
					formHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'data-title':Translate('Public'),'align':`center` });
							let publicEditLink = cTag('a',{ 'class':`anchorfulllink`,'href':`javascript:void(0);`,'click':editLink,'title':Translate('Edit/View') });
							if(item.form_public === 1) publicEditLink.appendChild(cTag('i',{ 'class':`fa fa-check default_tax` }));
						tdCol.appendChild(publicEditLink);
					formHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'data-title':Translate('Required'),'align':`center` });
						if(item.required === 1) {
							tdCol.appendChild(cTag('i',{ 'class':`fa fa-check default_tax` }));
							if(['', '0000-00-00 00:00:00', '1000-01-01 00:00:00'].includes(item.last_updated)) tdCol.appendChild(cTag('input',{'type':"hidden",'class':"form_required",'value':item.form_name}))
						}
					formHeadRow.appendChild(tdCol);
				showFormsDataInfo.appendChild(formHeadRow);
			});
		}
	}
}

async function AJsave_formsData(hidePopup){
    let errorFormsData = document.getElementById("errorFormsData");
    errorFormsData.innerHTML = '';
    let errorFound = 0;

	let oneFieldVal;
    if(document.getElementsByClassName("requiredField").length>0){
        let requiredObjs = document.querySelectorAll(".requiredField");
        [...requiredObjs].every(oneFieldObj=>{
            if(oneFieldObj.getAttribute('type') && oneFieldObj.getAttribute('type')==='checkbox'){
                oneFieldVal = oneFieldObj.checked;
                if(!oneFieldVal){
                    if(oneFieldObj.getAttribute('title')){
                        errorFormsData.innerHTML = oneFieldObj.getAttribute('title')+' '+Translate('is missing.');
                    }
                    oneFieldObj.focus();
                    errorFound++;
                    return false;  
                }
            }
            else{
                oneFieldVal = oneFieldObj.value;
                if(oneFieldVal===''){
                    if(oneFieldObj.getAttribute('title')){
                        errorFormsData.innerHTML = oneFieldObj.getAttribute('title')+' '+Translate('is missing.');
                    }
                    oneFieldObj.focus();
                    errorFound++;
                    return false;              
                }
            }
            return true;
        });
    }
    if(errorFound>0){return false;}
    actionBtnClick('.btnmodel', Translate('Saving'), 1);

    const jsonData = serialize('#frmFormsData');
    const url = '/'+segment1+'/AJsave_formsData';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.savemsg==='error'){
            errorFormsData.innerHTML = Translate('Duplicate Form Name found.');
			document.querySelector('[name="form_name"]').focus();
			actionBtnClick('.btnmodel', Translate('Save'), 0);
        }
        else{
            if(document.getElementById("newforms_id")){document.getElementById("newforms_id").value = 0;}
            let table_id = document.frmFormsData.table_id.value;
            hidePopup();
            if(table_id>0){
                AJget_formsData(table_id);
                if(document.getElementById("noteslist")){
                    AJget_notesData();
                }
            }
            else{
                loadSessFormInfo();
            }
        }
	}
    return false;
}

async function AJget_formDataPopup(forms_data_id, forms_id, table_id){
	const jsonData = {};
	jsonData['forms_data_id'] = forms_data_id;
	jsonData['forms_id'] = forms_id;

    const url = '/'+segment1+'/AJget_formDataPopup';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){		
		let form_publicStr = 'No';
		if(data.form_public>0){form_publicStr = 'Yes';}
		let requiredStr = 'No';
		if(data.required>0){requiredStr = 'Yes';}
		
		let formDialog = cTag('div');
			let divErrorMsg = cTag('div', {id: "errorFormsData", class: "errormsg"});
		formDialog.appendChild(divErrorMsg);
			let div, errorSpan, pTag;
			const formNameRow = cTag('div', {class: "flex", 'align': "left"});
				const formNameTitle = cTag('div', {class: "columnSM4"});
					const formNameLabel = cTag('label');
					formNameLabel.innerHTML = Translate('Form Name');
						errorSpan = cTag('span', {class: "errormsg"});
						errorSpan.innerHTML = '*';
					formNameLabel.appendChild(errorSpan);
				formNameTitle.appendChild(formNameLabel);
			formNameRow.appendChild(formNameTitle);
				const formNameValue = cTag('div', {class: "columnSM8"});
				formNameValue.innerHTML = data.form_name;
			formNameRow.appendChild(formNameValue);
		formDialog.appendChild(formNameRow);

			const makePublicRow = cTag('div', {class: "flex", 'align': "left"});
				const makePublicTitle = cTag('div', {class: "columnSM4"});
					const makePublicLabel = cTag('label');
					makePublicLabel.innerHTML = Translate('Make Public');
				makePublicTitle.appendChild(makePublicLabel);
			makePublicRow.appendChild(makePublicTitle);
				const makePublicField = cTag('div', {class: "columnSM8"});
				makePublicField.innerHTML = form_publicStr;
			makePublicRow.appendChild(makePublicField);
		formDialog.appendChild(makePublicRow);

			const requiredRow = cTag('div', {class: "flex", 'align': "left"});
				const requiredTitle = cTag('div', {class: "columnSM4"});
					const requiredLabel = cTag('label', {'for': "required"});
					requiredLabel.innerHTML = Translate('Required');
				requiredTitle.appendChild(requiredLabel);
			requiredRow.appendChild(requiredTitle);
				const requiredField = cTag('div', {class: "columnSM8"});
				requiredField.innerHTML = requiredStr;
			requiredRow.appendChild(requiredField);
		formDialog.appendChild(requiredRow);

			const emptyRow = cTag('div', {class: "borderbottom"});
		formDialog.appendChild(emptyRow);

			let termsConditionRow = cTag('div');
			//item that are present into data.formFieldsData but not into data.form_definitionsData
			let formFieldsData = data.formFieldsData.filter(item=>!data.form_definitionsData.find(info=>info.field_name===item.field_name));
			
			if(data.form_definitionsData && data.form_definitionsData.length) createFormFields(data.form_definitionsData);
			if(formFieldsData && formFieldsData.length) createFormFields(formFieldsData);

			function createFormFields(formData){
				formData.forEach(item=>{
					if(['TextOnly', 'SectionBreak'].includes(item.field_type)){
							let fieldRow = cTag('div',{ 'class':`flexStartRow` });
								div = cTag('div',{ 'class':`columnXS12`,'align':`left` });									
								getFormControlFields(div,item);
							fieldRow.appendChild(div);
						termsConditionRow.appendChild(fieldRow);
					}
					else if(item.field_type==='Signature'){
							let signRow = cTag('div',{ 'class':`flexStartRow` });
								let signColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin-bottom: 10px;", 'align':`left` });
									let signLabel = cTag('label');
									signLabel.innerHTML = item.field_name;
									if(item.field_required>0) appendRequiredStar(signLabel);
								signColumn.appendChild(signLabel);
							signRow.appendChild(signColumn);
						termsConditionRow.appendChild(signRow);
							let signField = cTag('div',{ 'class':`flexStartRow` });
								div = cTag('div',{ 'class':`columnXS12`, 'style': "margin-bottom: 10px;", 'align':`left` });									
								getFormControlFields(div,item);
							signField.appendChild(div);
						termsConditionRow.appendChild(signField);
					}
					else if(item.field_type==='Checkbox'){
							let boxRow = cTag('div',{ 'class':`flex`,'align':`left` });
								let boxColumn = cTag('div',{ 'class':`columnSM4` });
									let boxLabel = cTag('label');
									boxLabel.innerHTML = item.field_name;
									if(item.field_required>0) appendRequiredStar(boxLabel);
								boxColumn.appendChild(boxLabel);
							boxRow.appendChild(boxColumn);
								div = cTag('div',{ 'class':`columnSM8` });									
								getFormControlFields(div,item);									
							boxRow.appendChild(div);
						termsConditionRow.appendChild(boxRow);
					}					
					else{
						let nameRow = cTag('div',{ 'class':`flex`, 'align':`left`});
							let nameColumn = cTag('div',{ 'class':`columnSM4` });
								let nameLabel = cTag('label');
								nameLabel.innerHTML = item.field_name;
								if(item.field_required>0) appendRequiredStar(nameLabel);
							nameColumn.appendChild(nameLabel);
						nameRow.appendChild(nameColumn);
							div = cTag('div',{ 'class':`columnSM8`});									
							getFormControlFields(div,item);									
						nameRow.appendChild(div);
						termsConditionRow.appendChild(nameRow);
					}
				})
			} 

			function getFormControlFields(parentNode,data){
				if(['TextBox', 'Date', 'DropDown','TextAreaBox'].includes(data.field_type)){
					parentNode.innerHTML = data.value;
				}
				else if(data.field_type==='Checkbox'){
					if(data.value !=='Yes'){
						parentNode.appendChild(cTag('input',{ 'type':`checkbox` }));
					}
					else{
						parentNode.appendChild(cTag('input',{ 'checked':'','type':`checkbox` }));
					}
				}
				else if(data.field_type==='TextOnly'){
						pTag = cTag('p');
						pTag.innerHTML = data.parameters;
					parentNode.appendChild(pTag);
				}
				else if(data.field_type==='SectionBreak'){
					if(data.parameters !==''){
							pTag = cTag('p',{ 'style': "font-weight: bold;" });
							pTag.innerHTML = data.parameters;
							pTag.appendChild(cTag('hr'));
						parentNode.appendChild(pTag);
					}
					else{
						parentNode.appendChild(cTag('hr'));
					}
				}
				else if(data.field_type==='Signature'){
					if(data.signatureCode !==''){
							let signDiv = cTag('div',{ 'class':`columnXS12`, 'style': "margin-bottom: 10px;" });
							signDiv.appendChild(cTag('img',{ 'style':`max-width:100%;`,'alt':``,'src':data.signatureCode }));
						parentNode.appendChild(signDiv);
					}
				}
				else if(data.field_type==='UploadImage'){
					if(data.value !== ''){
							let uploadImageColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin-bottom: 10px;" });
							uploadImageColumn.appendChild(cTag('img',{ 'click':()=>viewPicturePopup(data.value),'style':`max-width:100%; cursor: zoom-in;`, 'alt':``,'src':data.value }));
						parentNode.appendChild(uploadImageColumn);
					}
				}
			}
			function appendRequiredStar(node){
				let errorSpan = cTag('span', {class: "errormsg"});
					errorSpan.innerHTML = '*';
				node.appendChild(errorSpan);
			}
		formDialog.appendChild(termsConditionRow);
		
		popup_dialog(
			formDialog,
			{
				title:Translate('Form Information'),
				width:600,
				buttons: {
					'Cancel': {
						text:Translate('Cancel'),
						class: 'btn defaultButton',
						click: function(hidePopup) {
							hidePopup();
						},
					},
					'Edit':{
						text: Translate('Edit'),
						class: 'btn saveButton btnmodel',
						click: function(hidePopup) {
							hidePopup();
							AJget_formFieldsPopup(forms_data_id, forms_id, table_id);
						},
					},
					'Print':{
						text: Translate('Print'),
						class: 'btn printButton btnmodel',
						click: function() {
							printbyurl('/Repairs/formsprints/'+data.form_for+'/'+table_id+'/0/lr/'+forms_data_id);
						},
					},
					'Remove':{
						text: Translate('Remove'),
						class: 'btn archiveButton btnmodel',
						click: function(hidePopup) {
							hidePopup();
							removeFormDataPopup(forms_data_id, forms_id, table_id, data.form_name);
						},
					}
				}
			}
		);
	}
	return true;
}

async function AJget_formFieldsPopup(forms_data_id, forms_id, table_id){
	const jsonData = {};
	jsonData['forms_data_id'] = forms_data_id;
	jsonData['forms_id'] = forms_id;

    const url = '/'+segment1+'/AJget_formFieldsPopup';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		let errorSpan, inputField;
		let signatureImageSource;
		const formDialog = cTag('div');
			const divErrorMsg = cTag('div', {id: "errorFormsData", class: "errormsg"});
		formDialog.appendChild(divErrorMsg);
			const infoForm = cTag('form', {'action': "/Repairs/AJsave_formsData", name: "frmFormsData", id: "frmFormsData", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
				const formNameRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
					const formNameTitle = cTag('div', {class: "columnSM4"});
						const formNameLabel = cTag('label', {'for': "form_name"});
						formNameLabel.innerHTML = Translate('Form Name');
							errorSpan = cTag('span', {class: "errormsg"});
							errorSpan.innerHTML = '*';
						formNameLabel.appendChild(errorSpan);
					formNameTitle.appendChild(formNameLabel);
				formNameRow.appendChild(formNameTitle);
					const formNameField = cTag('div', {class: "columnSM8"});
						inputField = cTag('input', {'type': "text", title: Translate('Form Name'), class: "form-control required", name: "form_name", 'value': data.form_name.replace('"', '&quot;'), 'maxlength': 15});
					formNameField.appendChild(inputField);
				formNameRow.appendChild(formNameField);
			infoForm.appendChild(formNameRow);

				const publicRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
					const publicTitle = cTag('div', {class: "columnSM4"});
						const publicLabel = cTag('label', {'for': "form_public"});
						publicLabel.innerHTML = Translate('Make Public');
					publicTitle.appendChild(publicLabel);
				publicRow.appendChild(publicTitle);
					const publicField = cTag('div', {class: "columnSM8"});
						inputField = cTag('input', {'type': "checkbox", name: "form_public", id: "form_public", 'value': 1});
						if(data.form_public>0){
							inputField.setAttribute('checked', "checked");
						}
					publicField.appendChild(inputField);
				publicRow.appendChild(publicField);
			infoForm.appendChild(publicRow);

				const requiredRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
					const requiredTitle = cTag('div', {class: "columnSM4"});
						const requiredLabel = cTag('label', {'for': "required"});
						requiredLabel.innerHTML = Translate('Required');
					requiredTitle.appendChild(requiredLabel);
				requiredRow.appendChild(requiredTitle);
					const requiredField = cTag('div', {class: "columnSM8"});
						inputField = cTag('input', {'type': "checkbox", name: "required", id: "required", 'value': 1});
						if(data.required>0){
							inputField.setAttribute('checked', "checked");
						}
					requiredField.appendChild(inputField);
				requiredRow.appendChild(requiredField);
			infoForm.appendChild(requiredRow);

				const emptyDiv = cTag('div', {class: "borderbottom"});
			infoForm.appendChild(emptyDiv);

				const fieldsDataDiv = cTag('div');
				if(data.formFieldsData && data.formFieldsData.length){
					data.formFieldsData.forEach(item=>{
						if(['TextOnly', 'SectionBreak'].includes(item.field_type)){
								const fieldTypeRow = cTag('div',{ 'class':`flexStartRow` });
									const fieldTypeColumn = cTag('div',{ 'class':`columnXS12`,'align':`left` });									
									getFormControlFields(fieldTypeColumn,item);
								fieldTypeRow.appendChild(fieldTypeColumn);
							fieldsDataDiv.appendChild(fieldTypeRow);
						}
						else if(item.field_type==='Signature'){
								signatureImageSource = item.value;
								const signatureRow = cTag('div',{ 'class':`flexStartRow` });
									const signatureColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin-bottom: 10px;", 'align':`left` });
										let signatureLabel = cTag('label',{'for':`ff${item.order_val}`});
										signatureLabel.innerHTML = item.field_name;
										if(item.field_required>0) {
												errorSpan = cTag('span', {class: "errormsg"});
												errorSpan.innerHTML = '*'
											signatureLabel.appendChild(errorSpan)
										}
									signatureColumn.appendChild(signatureLabel);
								signatureRow.appendChild(signatureColumn);
							fieldsDataDiv.appendChild(signatureRow);
								const signatureField = cTag('div',{ 'class':`flex` });
									const signatureFieldColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin-bottom: 10px;", 'align':`left` });									
									getFormControlFields(signatureFieldColumn,item);
								signatureField.appendChild(signatureFieldColumn);
							fieldsDataDiv.appendChild(signatureField);
						}
						else if(item.field_type==='Checkbox'){
								const checkboxRow = cTag('div',{ 'class':`flex`,'align':`left` });
									const checkboxColumn = cTag('div',{ 'class':`columnSM4` });
										let checkboxLabel = cTag('label',{'for':`ff${item.order_val}`});
										checkboxLabel.innerHTML = item.field_name;
										if(item.field_required>0) {
												errorSpan = cTag('span', {class: "errormsg"});
												errorSpan.innerHTML = '*'
											checkboxLabel.appendChild(errorSpan)
										}
									checkboxColumn.appendChild(checkboxLabel);
								checkboxRow.appendChild(checkboxColumn);
									const checkboxField = cTag('div',{ 'class':`columnSM8` });
									getFormControlFields(checkboxField,item);									
								checkboxRow.appendChild(checkboxField);
							fieldsDataDiv.appendChild(checkboxRow);
						}					
						else{
								const fieldNameRow = cTag('div',{ 'class':`flex`,'align':`left` });
									const fieldNameColumn = cTag('div',{ 'class':`columnSM4` });
										let fieldNameLabel = cTag('label',{'for':`ff${item.order_val}`});
										fieldNameLabel.innerHTML = item.field_name;
										if(item.field_required>0) {
												errorSpan = cTag('span', {class: "errormsg"});
												errorSpan.innerHTML = '*'
											fieldNameLabel.appendChild(errorSpan)
										}
									fieldNameColumn.appendChild(fieldNameLabel);
								fieldNameRow.appendChild(fieldNameColumn);
									const fieldNameValue = cTag('div',{ 'class':`columnSM8`});									
									getFormControlFields(fieldNameValue,item);									
								fieldNameRow.appendChild(fieldNameValue);
							fieldsDataDiv.appendChild(fieldNameRow);
						}
					})
				}

				function getFormControlFields(parentNode,data){
					let option,pTag;
					let requiredStr = '';
					if(data.field_required>0) requiredStr = 'requiredField';

					if(data.field_type === 'TextBox'){
						parentNode.appendChild(cTag('input',{ 'type':`text`,'title':data.field_name,'class':`form-control ${requiredStr}`,'name':`ff${data.order_val}`,'value':data.value,'maxlength':`35` }));
					}
					else if(data.field_type === 'TextAreaBox'){
							const textarea = cTag('textarea',{ 'rows':`2`,'title':data.field_name,'class':`form-control ${requiredStr}`,'name':`ff${data.order_val}` });
							textarea.innerHTML = data.value;
						parentNode.appendChild(textarea);
					}
					else if(data.field_type === 'Date'){
						parentNode.appendChild(cTag('input',{ 'type':`text`,'title':data.field_name,'class':`form-control DateField ${requiredStr}`,'name':`ff${data.order_val}`,'value':data.value,'maxlength':`35` }));
					}
					else if(data.field_type === 'DropDown'){
							const select = cTag('select',{ 'title':data.field_name,'class':`form-control ${requiredStr}`,'name':`ff${data.order_val}` });
							if(data.parameters !== ''){
									option = cTag('option',{ 'value':`` });
									option.innerHTML = data.field_name;
								select.appendChild(option);
								data.parameters.split('||').forEach(item=>{
										option = cTag('option',{ 'selected':``,'value':item });
										option.innerHTML = item;
									select.appendChild(option);
								});
								select.value = data.value;
							}
						parentNode.appendChild(select);
					}
					else if(data.field_type==='Checkbox'){
						if(data.value !=='Yes'){
							parentNode.appendChild(cTag('input',{ 'type':`checkbox`,'title':data.field_name, 'class':`cursor ${requiredStr}`, 'name':`ff${data.order_val}`, 'value':"Yes" }));
						}
						else{
							parentNode.appendChild(cTag('input',{ 'checked':'','type':`checkbox`, 'title':data.field_name, 'class':`cursor ${requiredStr}`, 'name':`ff${data.order_val}`, 'value':"Yes" }));
						}
					}
					else if(data.field_type==='TextOnly'){
							pTag = cTag('p');
							pTag.innerHTML = data.parameters;
						parentNode.appendChild(pTag);
					}
					else if(data.field_type==='SectionBreak'){
						if(data.parameters !==''){
								pTag = cTag('p',{ 'style': "font-weight: bold;" });
								pTag.innerHTML = data.parameters;
								pTag.appendChild(cTag('hr'));
							parentNode.appendChild(pTag);
						}
						else{
							parentNode.appendChild(cTag('hr'));
						}
					}
					else if(data.field_type==='Signature'){
							let signatureDiv = cTag('div',{ 'id':`signatureID${data.order_val}` });
							signatureDiv.innerHTML = `Signature will be here ${data.order_val}`;
						parentNode.appendChild(signatureDiv);
						parentNode.appendChild(cTag('input',{ 'type':`hidden`,'name':`ff${data.order_val}`,'id':`ff${data.order_val}`,'class':`Signature ${requiredStr}`,'title':data.field_name,'value':data.digital_signature_id }));
					}
					else if(data.field_type==='UploadImage'){
							let uploadDiv = cTag('div',{ 'id':`UploadImageID${data.order_val}` });
							uploadDiv.innerHTML = `Image will be here ${data.order_val}`;
						parentNode.appendChild(uploadDiv);
						parentNode.appendChild(cTag('input',{ 'type':`hidden`,'name':`ff${data.order_val}`,'id':`ff${data.order_val}`,'class':`fieldImages ${requiredStr}`,'title':data.field_name,'value':data.value }));
					}
				}
			infoForm.appendChild(fieldsDataDiv);

				inputField = cTag('input', {'type': "hidden", name: "forms_data_id", id: "forms_data_id", 'value': forms_data_id});
			infoForm.appendChild(inputField);
				inputField = cTag('input', {'type': "hidden", name: "table_id",  id: "table_id",'value': table_id});
			infoForm.appendChild(inputField);
				inputField = cTag('input', {'type': "hidden", name: "forms_id", id: "forms_id", 'value': forms_id});
			infoForm.appendChild(inputField);
		formDialog.appendChild(infoForm);
			
		popup_dialog600(Translate('Form Information'),formDialog,Translate('Save'),AJsave_formsData);
		
		setTimeout(function() {
			document.getElementById("errorFormsData").innerHTML = '';
			if(document.getElementsByClassName("Signature").length>0){
					const style = cTag('style',{ 'type':`text/css` });
					style.append(
						'.signatureparent {color:darkblue;background-color:darkgrey;padding:1px;}'+
						'.signatureImages{position:relative}'+
						'.mainsignature {border: 2px dotted #333;background-color:#fff; min-height:100px;}'+
						'html.touch .signaturecontent {float:left;width:92%;}'+
						'html.touch .scrollgrabber {float:right;width:4%;margin-right:2%;background-color:#fff;}'+
						'html.borderradius .scrollgrabber {border-radius: 1em;}'
					)
				document.getElementById('errorFormsData').parentNode.appendChild(style);					
				runSignatureScript(signatureImageSource);
			}
			
			if(document.getElementsByClassName("DateField").length>0){					
				date_picker('.DateField');	
			}				
			
			if(document.getElementsByClassName("fieldImages").length>0){
				runImageScript();
			}
			applySanitizer(formDialog);
		}, 500);			
	}
	return true;
}

function runSignatureScript(imgSource){
	let SignaturesID = document.getElementsByClassName("Signature");
	for(let l = 0; l < SignaturesID.length; l++){
		let digital_signature_id = SignaturesID[l].value;
		let showing_order = SignaturesID[l].getAttribute('name').replace('ff', '');
		if(digital_signature_id===''){
			let container = document.querySelector("#signatureID"+showing_order);
			container.innerHTML = '';
				let signatureContentDiv = cTag('div',{ 'class':`signaturecontent` });
					let signatureParentDiv = cTag('div',{ 'class':`signatureparent` });
						let mainSignatureDiv = cTag('div',{ 'class':`mainsignature`,'id':`signature${showing_order}` });
					signatureParentDiv.appendChild(mainSignatureDiv);
				signatureContentDiv.appendChild(signatureParentDiv);
					let tlsCntnr = cTag('div',{ 'id':`tools${showing_order}`, 'style': "margin-top: 6px;" });
				signatureContentDiv.appendChild(tlsCntnr);
			container.appendChild(signatureContentDiv);
			container.appendChild(cTag('div',{ 'class':"scrollgrabber" }));
			
			digitalSignature(mainSignatureDiv,tlsCntnr);
		}
		else{
			const signatureContiner = document.getElementById("signatureID"+showing_order);
			signatureContiner.innerHTML = '';
			signatureContiner.appendChild(cTag('div',{ 'class':`clear` }));
				let signatureImageDiv = cTag('div',{ 'class':`columnSM12 signatureImages`, 'style': "margin-bottom: 10px;" });
				signatureImageDiv.appendChild(cTag('img',{ 'style':`max-width: 100%`,'alt':digital_signature_id,'title':showing_order,'src':imgSource }));
			signatureContiner.appendChild(signatureImageDiv);
			
			document.getElementById("ff"+showing_order).value = digital_signature_id;
			document.querySelectorAll(".signatureImages").forEach(oneRowObj=>{
				oneRowObj.addEventListener('mouseenter', function() {
					let digital_signature_id = this.querySelector('img').alt;
					let showing_order = this.querySelector('img').title;
					let deleteIcon = cTag('div', {class: "deletedicon"});
					deleteIcon.addEventListener('click', function(){removeSignature(digital_signature_id, showing_order)});
					this.append(deleteIcon);
				});
				oneRowObj.addEventListener('mouseleave',function() {
					this.querySelector(".deletedicon").remove();
				})
			});
		}
	}
}

function digitalSignature(cnvsCntnr,tlsCntnr){
    let canvasContainer = cnvsCntnr || document.getElementById('signature');
    let toolsContainer = tlsCntnr || document.getElementById('tools');
    canvasContainer.innerHTML = toolsContainer.innerHTML = '';
    let imgData = 'data:image/gif;base64,R0lGODlhtABJAOf/AAABAAACAAEEAAIFAQQHAgUIBAcJBQoJAAgLBwwLAAoMCA0MAA8OAwwPCxMQAA8RDRURABYSARITERMVBBQVExcXABgYAhYYFRkaBBwbABkaGB0cAR4dAhocGRsdGh8fBSIgAB0fHB4gHSAhHyQjBSckACIjISgmASQlIyooBSYnJSwpAC0qAC4rAS8rAikqKCssKjEuBTMvAC0uLDUwAjcyBS8xLzg0Bjs1ATIzMTw3Azk5BDQ2MzY3NTs7Bjw8Bzg5Nz49ADo7OUA/AkJABEJBBTw+O0RCBj5APUVECEdFAUFCQElGAkpIBERGQ0xKBk5LB09MCEdJRlJOAlNPA0pLSVRQBFZSBk1PTFdTB1hUCVtVAE9RTlxXAl5YBFJUUVRVU2BaBmFbCFZXVWJcCVZYVVlbWWZgAmhhBGliBVxeW15gXWxlCW1mC2BiX25nDHBoAXJqA2NlYnRrBmZnZXFuCGhpZ3NwCnRxDHVyAHdzAWxta3l1A3p2BW9xbnx4CX55DIB7AHN1coJ8AYN+A3Z4dXh6d4eBCYiCDImDDnt9eoyFAI2GAo6HA36AfZCJBoCCf4KEgZOLDJWND4WHhJiQAYiKh5qSBZ2UCouNip+VDqGXAI6QjaSaA5CSj6ecB5SWk6ieC6qfDq2hAJeZlq+jAJmbmLCkAqynBJyem66oCZ2fnLCqDaCin7StALKsEKOloriwA7qyCaeppry0DamrqL+2AL21EMG4AKyuq8O6Aq6wrca8CbCyr7O1ssm/EcvBAM3DALa4tc/EAri6t7q8udLHCdPIDr2/vNXKEtjMANrNAMDCvtvPAsLEwd3QB8PFwsXHxN/SDcfJxuHUEuTWAOXXAOfYAMvNyejaBc3PzOrcC9DSz+zdEOjgE+viANPV0uzjAO3kAO7lANfZ1vDnBvLoC9nb2PPpD/brAPTqEtze2/ftAPnuAPrvAN/h3vvwAPzxBeHk4P7yCeXn5Ofp5unr6Ovt6u7w7fHz8PP18vX49Pn7+Pv9+v7//P///yH5BAEKAP8ALAAAAAC0AEkAAAj+AP8JHEhQoL+DCAsqXMiwocOHECNKnEixosWHCDNq9Hexo8ePIEOKhHiwXiQVFy6M+EKJ2L2NHEfKnEmz5sWDoAgE2MmT50pLxfDBtEm0qNGQBzntpGJLnLhqqgAxqdAzgIkxmZDlG3q0q9evBqHt1DSvrFmz1VD1URKhqgozmZht3Qi2rl2QB8EEGHS2r19qo/QcadtThRpO0OZqvMu4cUN/8ggs8Oa3cmVpovAQYVAVxhpP0fZxdUy6rr9ZAaxYXm053rJPdYIsqDrDDahpoumW3m3zoKAAiVgLXx1P2ac5O2bzJGBDDils/Ebznt7x4JIAt4ZrZ93u2KY3OJT+7ySQgw4pbdF1U19P0l+DAOK2yxfezpgmNjUM9DTQw04qbuktxt6ABPlDTgAnzKegdu0Ec0kaNOjEkwFA7NEKOAFmROB6/sASwBYLhrgdO8BUckYMEu5kgBB+XNiPdBvadZAfATQi4o3ysfPLJGK4UJUCRvgBCzkwxRSjVwcZEQAuODY5Hzq8SOIFC1U1gIQgs5xT5JFF+dOPAgGY4+SYCpqDSyNdnFDVA0sUUss6W3I5kj/gBMACmXiGWI4ti2hBQlUSOGFILu/EKedNrQQQBoOjeEHCCTggUk2eeYpDSyJXgFAVBVIosos8hh6KkT97BCDJcMrQUFUABPBV1ij+kuASDqVOhiMLIlRwUNUFVTjSCz2hilqgP0AEwItw3nAmwyeyGCPLGZzNKk5PJMARizq0OulNLINEsUFVHWABiS/1wHioP/wYQAC2rNURwAbs9IXOpGUhEl5PCzShSrZkbuPKIE1YAC4XkQhjj7kE+sNNADIM10UAOmz3RAAuELaAX7h4EYQYyfAb4jVRTVWVCCy5hDBv/qQSwBnDfbITFLzEw1o8beHSjix1kGUWOmhUlUV8HouYVh9JEOYTGEAJpV5pB9kRQCXaLUIYBnrQ61cwARgQr1/WUBkAEYPgsJMMMs8jjhixBB0iYHoQAUFVJpSRlWIaNnYQDwEEs13+OYhQtdOpfjESwA+VlePjAqiUFU8lEBhA2Tx/BBDBNWrjiNkdm7llBidynUwUugYY0M587GiSwk6b+MVEAH/41c7qBhx7VjfSmJXFTnNY1s4zlcvn2nGyVfXCYdDo4/mc2ARQQ4jtTBEAB6Ob1c5ssvilyU6fCFfDTgsA3RcgAYjS+4LtKNMJcuLt5BlouR3UJSkBsMFaNY1E3xc1O1Fz1jE7odOXOBNg3XDeph9EVMZ5YhgfjrrznXstpzbEcF9NDiKHAOjMMnkIQO78soydUM4sowgACfwSuQ+UbTXd+FsALLC1s2hhZasJRgWyp0AG2Qc/+mFVLiQ4k4PkIAD+xmDN9QjQCfvNgx3OO0JfBhEAKvjlT3yJxzV4MYpYKINdZZFFADAQjxhY0C8Tc1Vl4BAAPdSQfMHAA6t2wcM57UMyRnSdFXaygUFIAhF/+BYBZGeWK+ylL1gLQDW8UYJVVYAJ35jHIpo4D1QEIAUnLIuaLugXL7piNaJg0hktw0QC9KKNSJlGAHAwnHZ84garcoHezhKPAFbvLJUIwA7mUY309SSIL2TEPOLho0tKTydpq8y04GMZl4FoHrwAAR4GYUdVdOOMnfQFKD/iD1AEAA7yAUYf0JAFQMTCfq7AwRHmGABVeG8eTCRCWcTRjXCUgx3HKGBZ/qTJUzDMfs/+2EnHKqOKANBgNTsIAA3fsCoCMOET/hsf+AwgjGnexB9uCEAnxjSHVQVAmWXRgyxd9wM7jS6FASiH4nwEuHm8ImtxNIsa72CZcuzkcVF4JBSsEIRv7YQBzxxfH7IWQSN55CAzCIAyxsQOUSxiDle4gXIWMDpXBKACH1zn6rBTln6C4CwhXMAHGxGAG6yGCAFIXGW0iAGz+IiGZflFG2o2D2kwgQiJ2Ce/NGqAYjiUIge5ADFpFY9njOIXZVHHtzbQh1GoQg8YYFXqyrLTKbBSVVooSxoC8IbVtKV2lWHiFcryjZ1g9izt+GAbejKFofLrDllDxl0lkte9Vu4ZNu3+SQReWZYg/PEswNhJ9WyL1r5cg1W8CEYwjrEMb0RvdVBr5LuEY9smyGA8tPCLLS5xjWpYI6XyqagBVOvTihykA0JVoDk2oYQayOCgWJwHOoDpl9GWQB2c4R0/LbqTCaRAP8soCxkXxZpvMWkUbWGAac2iqZ58YA6rVFBFFcCM1Y6KDhrc5Gq0yIAWmkUcVAmD5FZDxgq4gAUpOMEG0rcBswagDso451nMsRNrlOUaqwPB445IX4qNYkEEVQA0HOwQOgVgAQmW8FmWEYHWVcZlO2nCaqgkvr6UQxoa5q8yqrIAEhBBDJOI3kktAFoqya8s0mDVNr6xDWBcAqwB+ED+iNgQAAVEg8cMOYgaJMdHIZvFwn45wk4mYZltvNQyvC2LyxaQ2FVpcrT8NYsjSyxoO/nlGo3Y14LiMdkGTAPOC0HXF3aCg0ocA7t29m0MIpBTv7jsn5Z5G2bPEIA2zKMd1aBiI+Zwh6396cZnwdrFJNtqSsWD1Q3ABqYVgi5H6JV7OoBDJ5QBajujQ6SWYbUZK5NCppaFyazJpwGgXRZ0xPROkgwArvMUDzEE4AHC7i5r0VULN8wgRT/2wRw+oYxIhno1p7OFZXBBsbK0QyefrYwkdlKDJkxBC10IoEBfvJNS56kdXgiABLQx7IJoZB/R8MQaYEDlINThE8+w973+y4IHKoD6elkoizX6x5rbWdQCTZ4HVxFEBC/ooRKqCEaz5wNxiXOj4sTeiD6gwQk1vKAqDCACHkYR8JGvhomAKEtud43vAExBQgsYRDAi+TD6qnNM7diCz4H+mI3kgxmZMIMKqhKBI+hhFPpzul+OsQV6hYNVbfgFnsvCjp08IxheS0Mcv7UIo87BCjhIrGPJ1I4XUgAcZG+PRvCBDEuMwQRsV0IfUGE1uZ+F1eNJgRYG8YnoGQOlR3TXh+wXZteaReRNasccLwB5dcsEJvggBiW+IIKqHBIQqoiq3F+xBaP9rdGoLgsTI9xocFeuHVQIwAWIZPsJbuQewqAEFzz+UBULNAEQr9iG3OszikFsQQmmdVkd+hLxALwyEQFQ8vjaEVMNaKn6XdpIPXwBCSyAtycbAAWDEAsO53m8kFB3ZlslUBYVlUAK1A4T0wH35xgwQQ+94AhVcGw8sQFTQAixkEie5xfhQARpkFEBwAQz1jvs0AQB0AFwgn9gUSTykAuKIAUSUBUgYAWIIAsqFoLzcAsqQgRwoAm/kIL8wg6rEwKFAoN3USTvkAuG4AQ32BMgkAWLcAs9OHKvoCZVwQI7R1RJEAAisIQbUiTrUAuFsATv0RMlsAWMgAvcNnLPIAlioANtEQFiojbqoGcjACpMiDIwcQ6zIAhIACY9kQL+XRAreeh03rCIaoMOYGUCfigs/1Ak5AALfmAEhsgTLOAFknCAPsgv1bAIPhIAKFAuf1iGG9EP4NAKewAEOcQTLoBlv5BeoRgi3TAJAbUTDeAGxBAglBhnG8EP3LAKdtADscgqMXAGlQAMe3eLliEOmqBnKlIGu9A+qRiMlTiM2mAKdJAD8EYANJAGl6Bz0NgX5vAJTyAhBIAFs0A32vhTw4gNpCAHNgBvBlADbKAJxvCFm8QOqqAF4uEEq6A0kRePPbYR+zANoOBuVIYDbbAJn2Zn7RALYsAZO2EEoICKB4mQeKWQGWd0VJZsy+aPYxIPuMAGRpMDmeCHHemR1GS+ds5QdGuHL/JGb7DXJMBQB7H1ApBwfy8Jkz1kdsiQCWWAAh33cSGHI8fwB3+yEyZgCD9XN0LJHrhXDJYABpjXE0m3dE3HGtIwCKXYgn5waVRZlVwCE/ege7zHdm4Hd5UBaduzExJAB8jwIgmBlggJE/YgDJHABf/HExGgeaigDJVgW7yoBsKQIXrZmNuoEfvnCFigARZlAGCQC9jomJppcRshD72gCFXQAVUAC/C4maaZaUWSl6e5mgkZlBYREAA7'

    let startPosition;
    const styles = {
        saveBtn:{
            'cursor': 'pointer',
            'color': '#fff',
            'background-color': '#337ab7',
            'border-color': '#2e6da4',
            'padding': '6px 12px',
            'font-size': '14px',
            'font-weight': '400',
            'line-height': '1.42857143',
            'border-radius': '4px'
        },
        resetBtn:{
            'cursor': 'pointer',
            'color': '#333',
            'background-color': '#fff',
            'border-color': '#CCCCCC',
            'padding': '6px 12px',
            'font-size': '14px',
            'font-weight': '400',
            'line-height': '1.42857143',
            'border-radius': '4px'
        },
        img:{
            'position':'absolute',
            'min-width': '90px',
            'max-width': '180px' ,
            'width': '10%',
        }
    }
	
    let containerInfo = canvasContainer.getBoundingClientRect();

    // setting up tools
        let signHereImg = cTag('img',{'src':imgData});
        setStyles(signHereImg,styles.img)
    canvasContainer.appendChild(signHereImg);

        let saveBtn = cTag('input',{'type':'button','value':'Save'});
        setStyles(saveBtn,styles.saveBtn);
        saveBtn.addEventListener('click',()=>{
            saveSignature(canvas.toDataURL('image/png',0.5));
        });
    toolsContainer.appendChild(saveBtn);
        let resetBtn = cTag('input',{'type':'button','value':'Reset','id':'resetCanvas'});
        setStyles(resetBtn,styles.resetBtn);
        resetBtn.addEventListener('click',()=>{
			if(signHereImg.style.display === 'none'){
				signHereImg.style.display = '';
			}
            drawingObj.clearRect(0, 0, containerInfo.width, containerInfo.height);
            drawingObj.beginPath();
        })
    toolsContainer.appendChild(resetBtn);

    // setting up canvas
    let canvas = cTag('canvas',{'id':'signatureCanvas'});
    canvas.innerHTML = 'signature is not supported'
    canvas.height = containerInfo.height;
    canvas.width = containerInfo.width;
    setStyles(canvas,styles.canvas);
    canvasContainer.appendChild(canvas);

    let drawingObj = canvas.getContext('2d');
    drawingObj.lineWidth = 2;

    let points = [];

    //making the canvas drawable attaching mousemove event followed by mousedown event
    canvas.addEventListener('mousedown',function(event){
		if(signHereImg.style.display !== 'none'){
			signHereImg.style.display = 'none';
		}
        // initialize the starting position first 
        startPosition = {
            x:event.offsetX,
            y:event.offsetY,
        };
        points.push({x:event.offsetX,y:event.offsetY});

        drawingObj.fillRect(startPosition.x,startPosition.y,2,2);
        this.addEventListener('mousemove',draw);
    });
    // stop drawing when the mouse button is realesed or leave the canvas area
    canvas.addEventListener('mouseup',function(){
        this.removeEventListener('mousemove',draw);
        points = []
    });
    canvas.addEventListener('mouseleave',function(){
        this.removeEventListener('mousemove',draw);
    });
    // support for touch based devices
    canvas.addEventListener('touchstart',function(event){
		if(signHereImg.style.display !== 'none'){
			signHereImg.style.display = 'none';
		}
        // initialize the starting position first 
        startPosition = {
            x:event.touches[0].clientX-canvasContainer.getBoundingClientRect().left,
            y:event.touches[0].clientY-canvasContainer.getBoundingClientRect().top,
        };
    });
    canvas.addEventListener('touchmove',function(event){
        event.preventDefault();
        event.offsetX = event.touches[0].clientX-canvasContainer.getBoundingClientRect().left,
        event.offsetY = event.touches[0].clientY-canvasContainer.getBoundingClientRect().top;
        draw(event);
    });
    canvasContainer.addEventListener('touchend',()=>{
        points = [];
    });

    function draw(event){ 
        let endPosition;
        points.push({x:event.offsetX,y:event.offsetY});

        if(points.length>=3){
            let lastTwoPosition = points.slice(-2);
            let controllPosition = lastTwoPosition[0];
            endPosition = {
                x: (lastTwoPosition[0].x+lastTwoPosition[1].x)/2,
                y: (lastTwoPosition[0].y+lastTwoPosition[1].y)/2
            }
            drawingObj.beginPath();
            drawingObj.moveTo(startPosition.x, startPosition.y);
            drawingObj.quadraticCurveTo(controllPosition.x, controllPosition.y, endPosition.x, endPosition.y);
            drawingObj.stroke();
            drawingObj.closePath();                    
            // update the last position 
            startPosition = endPosition;
        }
    }
    function setStyles(node,stylesObj){
        for (const property in stylesObj) {
            node.style[property] = stylesObj[property];
        }
    }
}

async function saveSignature(note){
    if(note !== ""){
		let Signature = document.querySelector(".Signature");
		const jsonData = {
			'for_table': "forms_data",
			'table_id': document.getElementById("forms_data_id").value,
			'note': note
		};		

        const url = '/Common/AJsave_digitalSignature';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.savemsg==='Add'){
				Signature.value = data.id;
				runSignatureScript(data.note);
			}
			else{
				let saveSignatureColumn = cTag('div',{ 'class':`columnXS12` });
					let callOutDiv = cTag('div',{ 'class':`innerContainer error_msg` });
				
				if(data.returnStr=='errorOnAdding'){
					callOutDiv.innerHTML = Translate('Error occured while adding new digital signature! Please try again.');
				}
				else if(data.returnStr=='errorOnEditing'){
					callOutDiv.innerHTML = Translate('Error occured while editing new digital signature! Please try again.');
				}
				else{
					callOutDiv.innerHTML = Translate('No changes / Error occurred while updating data! Please try again.');
				}
				saveSignatureColumn.appendChild(callOutDiv);
			}
			
			if(document.querySelectorAll(".disScreen")){document.querySelectorAll(".disScreen").forEach(item=>item.remove())}       
		}
    }
}

function removeSignature(digital_signature_id, showing_order){
	let inputField;
	if(digital_signature_id !==''){
		let popUpHml = cTag('div');
			inputField = cTag('input', {'type': "hidden", id: "tableName", 'value': "digital_signature"});
		popUpHml.appendChild(inputField);
			inputField = cTag('input', {'type': "hidden", id: "tableIdValue", 'value': digital_signature_id});
		popUpHml.appendChild(inputField);
			inputField = cTag('input', {'type': "hidden", id: "description", 'value': showing_order});
		popUpHml.appendChild(inputField);
			inputField = cTag('input', {'type': "hidden", id: "redirectURI", 'value': ''});
		popUpHml.append(inputField, Translate('Do you sure want to remove this picture permanently?'));
		confirm_dialog(Translate('Remove')+' '+Translate('Signature'), popUpHml, (hidePopup)=>confirmAJremove_tableRow(hidePopup,runSignatureScript));
	}
}

function removeFormDataPopup(forms_data_id, forms_id, table_id, form_name){
	let inputField;
	let formDialog = cTag('div');
		inputField = cTag('input', {'type': "hidden", name: "table_id", id: "table_id", 'value': table_id});
	formDialog.appendChild(inputField);
		inputField = cTag('input', {'type': "hidden", name: "tableName", id: "tableName", 'value': "forms_data"});
	formDialog.appendChild(inputField);
		inputField = cTag('input', {'type': "hidden", name: "tableIdValue", id: "tableIdValue", 'value': forms_data_id});
	formDialog.appendChild(inputField);
		inputField = cTag('input', {'type': "hidden", name: "description", id: "description", 'value': form_name.replace('"', '&quot;')});
	formDialog.appendChild(inputField);
		inputField = cTag('input', {'type': "hidden", name: "redirectURI", id: "redirectURI", 'value': ""});
	formDialog.appendChild(inputField);
	formDialog.append(Translate('Are you sure want to remove this information')+' ('+form_name+')?');
		
	popup_dialog(
		formDialog,
		{
			title:Translate('Remove')+' '+Translate('Form Information'),
			width:600,
			buttons: {
				'Cancel': {
                    text:Translate('Cancel'),
					class: 'btn defaultButton', 
					click: function(hidePopup) {
						hidePopup();
						AJget_formDataPopup(forms_data_id, forms_id, table_id);
					},
				},
				'Confirm':{
                    text:Translate('Confirm'),
					class: 'btn saveButton btnmodel', 
					click: (hidePopup)=>confirmAJremove_tableRow(hidePopup,AJget_formsData),
				}
			}
		}
	);
}

function addNewFormsData(table_id){
	let errorId = document.getElementById('error_newforms_id');
	errorId.innerHTML = '';
	if(parseInt(document.getElementById("newforms_id").value)===0){
			let pTag = cTag('p');
			pTag.innerHTML = Translate('Missing Form Name.');
        errorId.appendChild(pTag);
		document.getElementById("newforms_id").focus();
		return false;
	}
	let forms_id = document.getElementById("newforms_id").value;
	AJget_formFieldsPopup(0, forms_id, table_id);
}

async function loadSessFormInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/loadSessFormInfo';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		let formsInfo = document.getElementById("formsInfo");
		formsInfo.innerHTML = '';
		addFormsInfo(formsInfo,data.returnData);

		document.querySelectorAll( ".addForm" ).forEach(function( item ) {
			item.disabled = false;
		});
		if(document.querySelectorAll(".newFormId").length>0){
			document.querySelectorAll( ".newFormId" ).forEach(function( item ) {
				let forms_id = item.value;
				document.querySelector("#addForm"+forms_id).disabled = true;
			});
		}
	}
}

async function removeFormRow(forms_id){
	const jsonData = {};
	jsonData['forms_id'] = forms_id;

    const url = '/'+segment1+'/removeFormRow';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		loadSessFormInfo();
	}							
}

//======edit=======
function edit(){
	let repairs_id = parseInt(segment3);
	if(isNaN(repairs_id) || repairs_id===0){
		if(window.navigator.onLine){
			const jsonData = {name: 'Invalid Repairs ID: '+segment3, message: 'Previous URL: '+document.referrer, url: document.location.href};
			const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
			fetch('/Home/handleErr/', options);
		}
		window.location = '/Repairs/lists/';
		return false;
	}
	
	let liPrint, emailHeadRow, tdCol;
	const Dashboard = document.querySelector('#viewPageInfo');
	Dashboard.innerHTML = '';
	Dashboard.appendChild(cTag('input',{ 'type':`hidden`,'id':`subPermission`,'value':`` }));
		const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const titleColumn = cTag('div',{ 'class':`columnSM6`});
				const titleHeader = cTag('h2',{ 'id':'title_heading', 'style': "text-align: start;" });
			titleColumn.appendChild(titleHeader);
		titleRow.appendChild(titleColumn);
			const buttonNames = cTag('div',{ 'class':`columnSM6`, 'style': "text-align: end;" });
				const repairTicketButton = cTag('a', {'href': "/Repairs/lists", class: "btn defaultButton", title: Translate('Repair Tickets')});
                    const repairTicket = cTag('span');
					repairTicket.innerHTML = Translate('Repair Tickets');
					if(OS !='unknown'){
                    	repairTicket.innerHTML = Translate('Repairs');
					}
				repairTicketButton.append(cTag('i', {class: "fa fa-list"}), ' ', repairTicket);
			buttonNames.appendChild(repairTicketButton);
			buttonNames.appendChild(cTag('span',{'id':'linkedTicket_container','style':'margin-left: 10px;'}));

				let repairPrintButton = cTag('div',{ 'class':`printBtnDropDown`, 'id': `repairPrint` });
					let buttonTitle = cTag('a',{ 'type':`button`,'class':`btn printButton dropdown-toggle`, 'style': "margin-left: 10px;", 'data-toggle':`dropdown`,'aria-haspopup':`true`,'aria-expanded':`false` });
					buttonTitle.appendChild(cTag('i',{ 'class':`fa fa-print` }));
					if(OS =='unknown'){
						buttonTitle.append(' '+Translate('Print')+' ');
					}
					buttonTitle.append('\u2000', cTag('span',{ 'class':`caret`}));
						const toggleSpan = cTag('span',{ 'class':`sr-only` });
						toggleSpan.innerHTML = Translate('Toggle Dropdown');
					buttonTitle.appendChild(toggleSpan);
				repairPrintButton.appendChild(buttonTitle);
					let ulMenu = cTag('ul',{ 'class':`dropdown-menu`});
						liPrint = cTag('li');
							let fullPrintLink = cTag('a',{ 'href':`javascript:void(0);`,'id':'full_page_print','title':Translate('Full Page Printer') });
							fullPrintLink.innerHTML = Translate('Full Page Printer');
						liPrint.appendChild(fullPrintLink);
					ulMenu.appendChild(liPrint);
					ulMenu.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						liPrint = cTag('li');
							let thermalPrint = cTag('a',{ 'href':`javascript:void(0);`,'id':'thermal_print','title':Translate('Thermal Printer') });
							thermalPrint.innerHTML = Translate('Thermal Printer');
						liPrint.appendChild(thermalPrint);
					ulMenu.appendChild(liPrint);
					ulMenu.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						liPrint = cTag('li');
							let emailLink = cTag('a',{ 'href':`javascript:void(0);`,'click':emailthispage,'title':Translate('Email Ticket') });
							emailLink.innerHTML = Translate('Email Ticket');
						liPrint.appendChild(emailLink);
					ulMenu.appendChild(liPrint);
					ulMenu.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						liPrint = cTag('li');
							let ticketLink = cTag('a',{ 'href':`javascript:void(0);`,'id':'ticket_print','title':Translate('Ticket Label') });
							ticketLink.innerHTML = Translate('Ticket Label');
						liPrint.appendChild(ticketLink);
					ulMenu.appendChild(liPrint);
					ulMenu.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
						liPrint = cTag('li');
							let customerLink = cTag('a',{ 'id':'customer_label_link', 'href':`javascript:void(0);`,'title':Translate('Customer Label') });
							customerLink.innerHTML = Translate('Customer Label');
						liPrint.appendChild(customerLink);
					ulMenu.appendChild(liPrint);
				repairPrintButton.appendChild(ulMenu);
			buttonNames.appendChild(repairPrintButton);

				const emailRow = cTag('div',{ 'class': "flexEndRow" });
					const sendEmailForm = cTag('form',{ 'method':`post`,'name':`sendEmail`,'enctype':`multipart/form-data`,'action':`#`,'submit':emailrepairdetails });
						const sendEmailTable = cTag('table',{ 'align':`center`,'width':`100%`,'border':`0`,'cellspacing':`0`,'cellpadding':`10` });
							const sendEmailBody = cTag('tbody');
								emailHeadRow = cTag('tr',{ 'class':`emailform`, 'style':'display:none; padding-top: 6px;'});
									tdCol = cTag('td');
									tdCol.appendChild(cTag('input',{ 'type':`email`,'required':``,'name':`email_address`,'id':`email_address`,'class':`form-control email`,'maxlength':`50` }));
								emailHeadRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'width':`150`,'align':`right`,'valign':`middle`,'nowrap':'' });
									tdCol.appendChild(cTag('input',{ 'type':`submit`,'class':`btn completeButton sendbtn`,'value':Translate('Email') }));
									tdCol.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`, 'style': "margin-left: 10px;", 'click':cancelemailform,'value':Translate('Cancel') }));
								emailHeadRow.appendChild(tdCol);
							sendEmailBody.appendChild(emailHeadRow);
								emailHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'colspan':`2` });
									tdCol.appendChild(cTag('div',{ 'id':`showerrormessage` }));
								emailHeadRow.appendChild(tdCol);
							sendEmailBody.appendChild(emailHeadRow);
						sendEmailTable.appendChild(sendEmailBody);
					sendEmailForm.appendChild(sendEmailTable);
				emailRow.appendChild(sendEmailForm);
			buttonNames.appendChild(emailRow);
		titleRow.appendChild(buttonNames);
	Dashboard.appendChild(titleRow);

		let customerInfoRow = cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-top: 5px;" });
			const customerInfoColumn = cTag('div',{ 'class':`columnMD6` });
				const customerInfoFlex = cTag('div',{ 'class':`flexColumn cardContainer`, 'style': "height: 100%;" });
					const customerWidget = cTag('div',{ 'class':`flex cardHeader` });
					customerWidget.appendChild(cTag('i',{ 'class':`fa fa-user`, 'style': "margin: 12px; margin-left: 0;" }));
						const customerHeader = cTag('h3');
						customerHeader.innerHTML = Translate('Customer Information');
					customerWidget.appendChild(customerHeader);
				customerInfoFlex.appendChild(customerWidget);
					const customerInfoContent = cTag('div',{ 'class':`flexColumn cardContent`, 'style': "flex-grow: 1;" });
						const customerInfoTitle = cTag('div',{ 'class':`flexSpaBetRow columnXS12`});
							let infoHeader = cTag('h3',{ 'style':`color: #5bc0de; font-weight: bold; font-size: 16px;` });
							infoHeader.append(Translate('Customer info'));
							let customerChangeLink = cTag('a',{ 'id':'change_customer_info', 'href':`javascript:void(0);`, 'style': "color: #009;" });
							customerChangeLink.appendChild(cTag('i',{ 'class':`fa fa-edit`,'data-toggle': "tooltip",'data-original-title':Translate('Change Customer Info') }));
						customerInfoTitle.append(infoHeader, customerChangeLink);
					customerInfoContent.appendChild(customerInfoTitle);
						const customerInFoField = cTag('div',{ 'class':`columnXS12`, 'style': "padding-left: 20px;" });
							let customerInformation = cTag('div',{ 'id':`customer_information`, class: "customInfoGrid" });
								const nameLabel = cTag('label');
								nameLabel.innerHTML = Translate('Name')+': ';
								let viewCustomerDiv = cTag('div', {'style': 'border-bottom: 1px solid #CCC; padding-bottom: 5px; margin-bottom: 5px;'});
									const viewCustomerLink = cTag('a',{ 'id':'view_customer_details', 'style': "color: #009; text-decoration: underline;", 'title':Translate('View Customer Details') });
									let changeBtn = cTag('button',{ 'style':'padding:2px 10px; margin-left: 10px; ','class':'btn defaultButton', id:'changeCustomer', 'title':Translate('Change Customer') });
									changeBtn.innerText = 'Change';
									changeBtn.addEventListener('click',()=>dynamicImport('./Customers.js','changeCustomerPopup',[calculateCartTotal]))
								viewCustomerDiv.append(viewCustomerLink, changeBtn);
							customerInformation.append(nameLabel, viewCustomerDiv);

								const emailLabel = cTag('label');
								emailLabel.innerHTML = Translate('Email')+': ';
							customerInformation.appendChild(emailLabel);
							customerInformation.appendChild(cTag('span',{ 'id':`customeremail` }));

								const phoneLabel = cTag('label');
								phoneLabel.innerHTML = Translate('Phone')+': ';
							customerInformation.appendChild(phoneLabel);
							customerInformation.appendChild(cTag('span',{ 'id':`phoneno` }));
						customerInFoField.appendChild(customerInformation);
					customerInfoContent.appendChild(customerInFoField);

						const propertyInfoTitle = cTag('div',{ 'class':`columnXS12`});
							let propertyInfoHeader = cTag('h3',{ 'id':'change_property_info', 'style':`color: #5bc0de; font-weight: bold; font-size: 16px;` });
							propertyInfoHeader.append(Translate('Property Info'));
						propertyInfoTitle.appendChild(propertyInfoHeader);
					customerInfoContent.appendChild(propertyInfoTitle);
						const propertyInfoColumn = cTag('div',{ 'class':`columnXS12`, 'style': "padding-left: 20px;" });
							let propertyInfoDiv = cTag('div',{ 'id':`propertiesInfo`, class: "customInfoGrid" });
								const imeiNoLabel = cTag('label');
								imeiNoLabel.innerHTML = Translate('IMEI/Serial No.');
							propertyInfoDiv.appendChild(imeiNoLabel);
							propertyInfoDiv.appendChild(cTag('span',{'id':'imei_number'}));

								const brandLabel = cTag('label');
								brandLabel.innerHTML = Translate('Brand')+':';
							propertyInfoDiv.appendChild(brandLabel);
							propertyInfoDiv.appendChild(cTag('span',{'id':'brand_name'}));

								const modelLabel = cTag('label');
								modelLabel.innerHTML = Translate('Model')+':';
							propertyInfoDiv.appendChild(modelLabel);
							propertyInfoDiv.appendChild(cTag('span',{'id':'model_name'}));

								const detailLabel = cTag('label');
								detailLabel.innerHTML = Translate('More Details')+':';
							propertyInfoDiv.appendChild(detailLabel);
							propertyInfoDiv.appendChild(cTag('span',{'id':'moreDtails'}));
						propertyInfoColumn.appendChild(propertyInfoDiv);
					customerInfoContent.appendChild(propertyInfoColumn);
				customerInfoFlex.appendChild(customerInfoContent);
			customerInfoColumn.appendChild(customerInfoFlex);
		customerInfoRow.appendChild(customerInfoColumn);

			let ticketInfoFlex = cTag('div',{ 'class':`flex columnMD6` });
				const ticketInfoColumn = cTag('div',{ 'class':`flexColumn cardContainer`, 'style': "height: 100%;" });
					const ticketWidget = cTag('div',{ 'class':`flexSpaBetRow cardHeader` });
						const ticketDiv = cTag('div',{ 'class':`flex` });
							let mobileIcon = cTag('i',{ 'class':`fa fa-mobile`, 'style': "margin: 12px; margin-left: 0;" });
						ticketDiv.appendChild(mobileIcon);
							const ticketHeader = cTag('h3');
							ticketHeader.innerHTML = Translate('Ticket Information');
						ticketDiv.appendChild(ticketHeader);
					ticketWidget.appendChild(ticketDiv);
						const buttonName = cTag('div',{ 'class':`invoiceorcompleted`, 'style': "padding-right: 3px;" });
							let editButton = cTag('button',{ 'id':'edit_ticket_info','href':`javascript:void(0);`,'class':`btn defaultButton` });
							editButton.innerHTML = Translate('Edit');
						buttonName.appendChild(editButton);
					ticketWidget.appendChild(buttonName);
				ticketInfoColumn.appendChild(ticketWidget);
					const ticketInfoContent = cTag('div',{ 'class':`flexSpaBetRow cardContent`, 'style': "padding: 0; flex-grow: 1;" });
						let ticketTabDiv = cTag('div',{ 'id':`ticketTabs`,'class':`columnXS12` });
							let ticketUl = cTag('ul',{ 'class':`ticketTabber` });
								let ticketLi = cTag('li');
									let basicTab = cTag('a',{ 'href':`#ticketTabs1` });
									basicTab.innerHTML = Translate('Basic Info');
								ticketLi.appendChild(basicTab);
							ticketUl.appendChild(ticketLi);
						ticketTabDiv.appendChild(ticketUl);
							const basicTabContent = cTag('div',{ 'class':`columnXS12`,'id':`ticketTabs1` });
								let basicUl = cTag('div',{ 'id':`showBasicInfo`, class: "customInfoGrid" });
									const problemLabel = cTag('label');
									problemLabel.innerHTML = Translate('Problem')+':';
									const problemSpan = cTag('span',{'id':'problem_label'});
								basicUl.append(problemLabel, problemSpan);
									const dateLabel = cTag('label');
									dateLabel.innerHTML = Translate('Due Date')+':';
									const dueSpan = cTag('span',{'id':'due_date'});
								basicUl.append(dateLabel, dueSpan);
									const notifyLabel = cTag('label');
									notifyLabel.innerHTML = Translate('Notifications')+':';
									let notifyLi = cTag('span',{'id':'notification'});
								basicUl.append(notifyLabel, notifyLi);
									const passwordLabel = cTag('label');
									passwordLabel.innerHTML = Translate('Password')+':';
									let passwordLi = cTag('span',{'id':'password'});
								basicUl.append(passwordLabel, passwordLi);
									const binLabel = cTag('label');
									binLabel.innerHTML = Translate('Bin Locat')+':';
									let binLi = cTag('span',{'id':'bin_locat'});
								basicUl.append(binLabel, binLi);
									const technicianLabel = cTag('label');
									technicianLabel.innerHTML = Translate('Technician')+':';
									let technicianLi = cTag('span',{'id':'technicia'});
								basicUl.append(technicianLabel, technicianLi);
									const salesmanLabel = cTag('label');
									salesmanLabel.innerHTML = Translate('Salesman')+':';
									let salesmanLi = cTag('span',{'id':'salesman'});
								basicUl.append(salesmanLabel, salesmanLi);
							basicTabContent.appendChild(basicUl);
						ticketTabDiv.appendChild(basicTabContent);
					ticketInfoContent.appendChild(ticketTabDiv);
				ticketInfoColumn.appendChild(ticketInfoContent);
			ticketInfoFlex.appendChild(ticketInfoColumn);
		customerInfoRow.appendChild(ticketInfoFlex);
	Dashboard.appendChild(customerInfoRow);

		const moreContent = cTag('div',{ 'class':`columnXS12`, 'style': "position: relative; margin-bottom: 10px;" });
			let moreContentDiv = cTag('div',{ 'id':'tableContainer','class':`ibox-content`});
		moreContent.appendChild(moreContentDiv);
	Dashboard.appendChild(moreContent);

		const historyRow = cTag('div',{ 'class':`flexSpaBetRow` });
			const historyColumn = cTag('div',{ 'class':`columnSM12` });
            let hiddenProperties = {
                'note_forTable': 'repairs' ,
                'srepairs_id': '' ,
                'table_idValue': '' ,
                'publicsShow': '1' ,
            }
            historyColumn.appendChild(historyTable(Translate('Product History'),hiddenProperties,true));
		historyRow.appendChild(historyColumn);
	Dashboard.appendChild(historyRow);

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
            let select = document.querySelector('#shistory_type');
                let option = cTag('option', {'value': shistory_type});
            select.appendChild(option);
            select.value = shistory_type;
        }
    }

	addCustomeEventListener('filter',filter_Repairs_edit);
	addCustomeEventListener('loadTable',loadTableRows_Repairs_edit);
	addCustomeEventListener('changeCart',changeThisRepairRow);
	AJ_edit_MoreInfo();
}

async function AJ_edit_MoreInfo(){
	let repairs_id = parseInt(segment3);

    const jsonData = {repairs_id:repairs_id};
    const url = '/'+segment1+'/AJ_edit_MoreInfo';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		let title = `${Translate('Repair Ticket')} t${data.ticket_no}`;
		document.querySelector('#subPermission').value = data.pSubPermission;
		
		let title_heading = document.querySelector('#title_heading');
		document.title = title_heading.innerHTML = title;
		if(data.status==='Invoiced'){
			title_heading.appendChild(cTag('input',{ 'type':`hidden`,'name':`repairs_status`,'id':`repairs_status`,'value':data.status }));
				let invoiceViewLink = cTag('a',{ 'class':`btn defaultButton`, 'href':`/Invoices/view/${data.invoice_no}`,'style':`padding-top:10px; padding-bottom: 10px; font-size: 20px; float: right; line-height: 0;background: ${data.currentStatusBG}; color: ${data.currentColor}`,'title':Translate('View Invoice') });
				invoiceViewLink.append(`${data.status} #s${data.invoice_no} `);
				invoiceViewLink.appendChild(cTag('i',{ 'class':`fa fa-link` }));
			title_heading.appendChild(invoiceViewLink);
		}
		else{
			let select = cTag('select',{ 'class':`btn defaultButton`, 'style':`margin-right: 5px; background: ${data.currentStatusBG}; color: ${data.currentColor}; width: 200px; float: right;`,'name':`repairs_status`,'id':`repairs_status`,'change':checkRepairsStatus });
			for (const key in data.repairsStaOpts) {
				let [bgColor,color] =  data.repairsStaOpts[key];
					let option = cTag('option',{ 'value':key, 'style':`background:${bgColor};color:${color};`})
					option.innerHTML = key;
				select.appendChild(option);
			}
			select.value = data.status;
			title_heading.appendChild(select);
		}

		title_heading.appendChild(cTag('input',{ 'type':`hidden`,'name':`oldrepairs_status`,'id':`oldrepairs_status`,'value':data.status }));
		
		document.getElementById('full_page_print').addEventListener('click',()=>printbyurl(`/Repairs/prints/large/${data.repairs_id}`));
		document.getElementById('thermal_print').addEventListener('click',()=>printbyurl(`/Repairs/prints/small/${data.repairs_id}`));
		document.getElementById('ticket_print').addEventListener('click',()=>printbyurl(`/Repairs/prints/label/${data.repairs_id}`));
		document.getElementById('customer_label_link').addEventListener('click',()=>printbyurl(`/Repairs/customer/label/${data.repairs_id}/${data.customer_id}`))

		if(data.linkedRepairsID >0 && data.linkedTickerNo >0){
			let button = cTag('button',{'class':`btn defaultButton` });
			button.addEventListener('click',function(){javascript:window.location=`/Repairs/edit/${data.linkedRepairsID}`;});
			button.innerHTML = `${Translate('View Linked Ticket #')}${data.linkedTickerNo}`;
			if(OS !='unknown'){
				button.innerHTML = '';
				button.append(cTag('i',{ 'class':`fa fa-list` }), ' ', `${Translate('Linked Ticket')}${data.linkedTickerNo}`);
			}
			document.querySelector('#linkedTicket_container').appendChild(button);
		}
		else if(data.status==='Invoiced'){
			let button = cTag('button',{ 'type':`button`,'click':()=>createLinkedTicket(data.repairs_id),'id':`createLinkedTicket`,'class':`btn defaultButton`, 'style': "margin-left: 10px;" });
			button.innerHTML = Translate('Create Linked Ticket');
			if(OS !='unknown'){
				button.innerHTML = '';
				button.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', `${Translate('Linked Ticket')}`);
			}
			document.querySelector('#linkedTicket_container').appendChild(button);
		}
		if(data.linkedRepairsID2 >0 && data.linkedTickerNo2 >0){
			let button = cTag('button',{ 'class':`btn defaultButton`,'style': "margin-left: 10px;" });
			button.addEventListener('click',()=>{javascript:window.location= `/Repairs/edit/${data.linkedRepairsID2}`;});
			button.innerHTML = `${Translate('Created From Ticket #')}${data.linkedTickerNo2}`;
			if(OS !='unknown'){
				button.innerHTML = '';
				button.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', `${Translate('From Ticket')}${data.linkedTickerNo2}`);
			}
			document.querySelector('#linkedTicket_container').appendChild(button);
		}

		document.querySelector('#email_address').value = data.customeremail;
		if(['Invoiced', 'Cancelled'].includes(data.status)){
			document.querySelector('#change_customer_info').innerHTML = '';
			document.querySelector('#changeCustomer').style.display = 'none';
		}
		else{
			document.querySelector('#change_customer_info').addEventListener('click',()=>dynamicImport('./Customers.js','AJget_CustomersPopup',[document.getElementById('customer_id').value]));
		}
		let view_customer_details = document.querySelector('#view_customer_details');
		view_customer_details.setAttribute('href',`/Customers/view/${data.customer_id}`);
		view_customer_details.append(data.customername+' ',cTag('i',{ 'class':`fa fa-link` }));
		document.querySelector('#customeremail').innerHTML = data.customeremail;
		document.querySelector('#phoneno').innerHTML = data.customerphone;

		if(data.properties_id>0 && ['Invoiced', 'Cancelled'].includes(data.status)===false){
			let propertyLink = cTag('a',{ 'href':`javascript:void(0);`, 'style': "color: #009; float: right;" });
			propertyLink.addEventListener('click',()=>dynamicImport('./Customers.js','AJget_propertiesPopup',[document.getElementById('properties_id').value]));
			propertyLink.appendChild(cTag('i',{ 'style':`cursor: pointer`,'class':`fa fa-edit`,'data-toggle': "tooltip",'data-original-title':Translate('Change Properties Info') }));
			document.querySelector('#change_property_info').appendChild(propertyLink);
		}
		document.querySelector('#imei_number').append(data.imei_or_serial_no);
		document.querySelector('#brand_name').append(data.brand);
		document.querySelector('#model_name').append(data.model);
		document.querySelector('#moreDtails').append(data.more_details);
		if(['Invoiced', 'Cancelled'].includes(data.status)===false){
			document.querySelector('#edit_ticket_info').addEventListener('click',()=>changeRepairInfo(data.rCustomFields));
		}
		else{
			document.querySelector('#edit_ticket_info').style.display = 'none';
		}
		if(data.rCustomFields>0){
				let li = cTag('li');
					let customLink = cTag('a',{ 'href':`#ticketTabs2` });
					customLink.innerHTML = Translate('Custom Fields');
				li.appendChild(customLink);
			document.querySelector('.ticketTabber').appendChild(li);
		}
		if(data.formsCount>0){
			let li = cTag('li');
				let repairFormLink = cTag('a',{ 'href':`#ticketTabs3` });
				repairFormLink.innerHTML = Translate('Repairs')+Translate('Forms');
			li.appendChild(repairFormLink);
			document.querySelector('.ticketTabber').appendChild(li);
		}
		document.querySelector('#problem_label').innerHTML = data.problem;
		document.querySelector('#due_date').innerHTML = DBDateToViewDate(data.due_datetime, 0, 1)+' '+data.due_time;
		if(data.notify_how ===1){
			document.querySelector('#notification').innerHTML = data.notify_email;
		 }
		 else if(data.notify_how ===2){
			document.querySelector('#notification').innerHTML = data.notify_sms;
		 }
		document.querySelector('#password').innerHTML = data.lock_password;
		document.querySelector('#bin_locat').innerHTML = data.bin_location;
		document.querySelector('#technicia').innerHTML = data.technicianName;
		document.querySelector('#salesman').innerHTML = data.salesmanName;

		document.querySelector('#pageURI').value = `${segment1}/${segment2}/${data.repairs_id}`;

		const ticketTabs = document.querySelector('#ticketTabs');
		if(data.rCustomFields>0){
				let customInfoTabs = cTag('div',{ 'class':`columnXS12`,'style':'display:none','id':`ticketTabs2` });
					let customInfoDiv = cTag('div',{ 'id':`showCustomInfo`, class:"customInfoGrid"});
					for (const key in data.rCustomFieldsData) {								
							let customLabel = cTag('label');
							customLabel.innerHTML = key+': ';									
							const customValue = cTag('span');
							customValue.innerHTML = data.rCustomFieldsData[key];
						customInfoDiv.append(customLabel, customValue);
					}
				customInfoTabs.appendChild(customInfoDiv);
			ticketTabs.appendChild(customInfoTabs);
		}

		if(data.formsCount>0){
			let ticketTabsHeadRow, thCol, tdCol, aTag;
				let ticketTabsColumn = cTag('div',{ 'class':`columnXS12`,'style':'display:none','id':`ticketTabs3` });
					let ticketTabsRow = cTag('div',{ 'class':`flexSpaBetRow` });
						let ticketTabsDiv = cTag('div',{ 'class':`columnXS12` });
							const ticketTabsTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
								const ticketTabsHead = cTag('thead',{ 'class':`cf` });
									ticketTabsHeadRow = cTag('tr');
										thCol = cTag('th',{ 'style': `text-align: center;` });
										thCol.innerHTML = Translate('Name');
									ticketTabsHeadRow.appendChild(thCol);
										thCol = cTag('th',{ 'style': `text-align: center;`, 'width':`30%` });
										thCol.innerHTML = Translate('Last Update');
									ticketTabsHeadRow.appendChild(thCol);
										thCol = cTag('th',{ 'style': `text-align: center;`, 'width':`10%` });
										thCol.innerHTML = Translate('Public');
									ticketTabsHeadRow.appendChild(thCol);
										thCol = cTag('th',{ 'style': `text-align: center;`, 'width':`10%` });
										thCol.innerHTML = Translate('Required');
									ticketTabsHeadRow.appendChild(thCol);
								ticketTabsHead.appendChild(ticketTabsHeadRow);
							ticketTabsTable.appendChild(ticketTabsHead);
								let ticketTabsBody = cTag('tbody',{ 'id':`showFormsDataInfo` });
								data.showFormsData.forEach(item=>{
									let editLink = ()=>AJget_formDataPopup(item.forms_data_id, item.forms_id, item.table_id);
									
										ticketTabsHeadRow = cTag('tr');
											tdCol = cTag('td',{ 'data-title':Translate('Name'),'align':`left` });
											
											aTag = cTag('a',{ 'class':`anchorfulllink`,'href':`javascript:void(0);`,'click':editLink,'title':Translate('Edit/View') });
											aTag.innerHTML = item.form_name;
											tdCol.appendChild(aTag);
											
										ticketTabsHeadRow.appendChild(tdCol);
											tdCol = cTag('td',{ 'data-title':Translate('Last Update'),'align':`center` });
												aTag = cTag('a',{ 'class':`anchorfulllink`,'href':`javascript:void(0);`,'click':editLink,'title':Translate('Edit/View') });
												aTag.innerHTML = DBDateToViewDate(item.last_updated, 0, 1);
											tdCol.appendChild(aTag);
										ticketTabsHeadRow.appendChild(tdCol);
											tdCol = cTag('td',{ 'data-title':Translate('Public'),'align':`center` });
												aTag = cTag('a',{ 'class':`anchorfulllink`,'href':`javascript:void(0);`,'click':editLink,'title':Translate('Edit/View') });
												if(item.form_public === 1) aTag.appendChild(cTag('i',{ 'class':`fa fa-check default_tax` }));
											tdCol.appendChild(aTag);
										ticketTabsHeadRow.appendChild(tdCol);
											tdCol = cTag('td',{ 'data-title':Translate('Required'),'align':`center` });
											if(item.required === 1) {
												tdCol.appendChild(cTag('i',{ 'class':`fa fa-check default_tax` }));
												if(['', '0000-00-00 00:00:00', '1000-01-01 00:00:00'].includes(item.last_updated)) tdCol.appendChild(cTag('input',{'type':"hidden",'class':"form_required",'value':item.form_name}))
											}
										ticketTabsHeadRow.appendChild(tdCol);
									ticketTabsBody.appendChild(ticketTabsHeadRow);
								});
							ticketTabsTable.appendChild(ticketTabsBody);
						ticketTabsDiv.appendChild(ticketTabsTable);
					ticketTabsRow.appendChild(ticketTabsDiv);
				ticketTabsColumn.appendChild(ticketTabsRow);

					const ticketTabsDropDown = cTag('div',{ 'class':`flexSpaBetRow` });
						const dropDownColumn = cTag('div',{ 'class':`columnXS6` });
							let selectNewForm = cTag('select',{ 'name':`newforms_id`,'id':`newforms_id`,'class':`form-control` });
								let newFormOption = cTag('option',{ 'value':`0` });
								newFormOption.innerHTML = 'Select Form Name';
							selectNewForm.appendChild(newFormOption);
							setOptions(selectNewForm,data.formsOptions,1,1);
						dropDownColumn.appendChild(selectNewForm);
						dropDownColumn.appendChild(cTag('span',{ 'id':`error_newforms_id`,'class':`errormsg` }));
					ticketTabsDropDown.appendChild(dropDownColumn);
						let addNewButton = cTag('div',{ 'class':`columnXS6` });
						addNewButton.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`,'value':`Add New`,'click':()=>addNewFormsData(data.repairs_id)}));
					ticketTabsDropDown.appendChild(addNewButton);
				ticketTabsColumn.appendChild(ticketTabsDropDown);
			ticketTabs.appendChild(ticketTabsColumn);
		}

		let tableContainer = document.getElementById('tableContainer');
		if(['Invoiced','Cancelled'].includes(data.status)){
			let tiStr, strong, headRow, thCol, tdCol, bTag;
			let table = cTag('table',{ 'class':`table table-bordered`, 'style': "margin-bottom: 0px;" });
				let tableHead = cTag('thead');
					headRow = cTag('tr');
						thCol = cTag('th',{ 'width':`3%`, 'style': "text-align: right;" });
						thCol.innerHTML = '#';
					headRow.appendChild(thCol);
						thCol = cTag('th');
						thCol.innerHTML = Translate('Description');
					headRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`10%`,'class':`DeliveredTitle`, 'style': "text-align: right;" });
						thCol.innerHTML = Translate('QTY');
					headRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`12%`, 'style': "text-align: right;" });
						thCol.innerHTML = Translate('Unit Price');
					headRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`12%`, 'style': "text-align: right;" });
						thCol.innerHTML = Translate('Total');
					headRow.appendChild(thCol);
				tableHead.appendChild(headRow);
			table.appendChild(tableHead);
				let tableBody = cTag('tbody',{ 'id':`invoice_entry_holder` });
				data.cartData.forEach((item,indx)=>{
						headRow = cTag('tr',{ 'class':`repairscart${item.pos_cart_id}` });
							tdCol = cTag('td',{ 'align':`right` });
							tdCol.innerHTML = indx+1;
						headRow.appendChild(tdCol);
							tdCol = cTag('td',{ 'align':`left` });
							tdCol.innerHTML = `${item.description} ${item.newimei_info}`;
						headRow.appendChild(tdCol);
							tdCol = cTag('td',{ 'align':`right` });
							tdCol.innerHTML = `${item.shipping_qty}`;
							if(item.return_qty>0){
								const span = cTag('span',{'class':"bgblack", 'style': "margin-left: 15px; padding: 5px; color: white;"});
								span.innerHTML = `-${item.return_qty}`
								tdCol.appendChild(span);
							}
						headRow.appendChild(tdCol);
							tdCol = cTag('td',{ 'align':`right` });
							tdCol.innerHTML = addCurrency(item.sales_price);
						headRow.appendChild(tdCol);
							tdCol = cTag('td',{ 'id':`totalstr$pos_cart_id`,'align':`right` });
							tdCol.innerHTML = addCurrency(item.total);
							if(item.discount_value>0){
								tdCol.append(cTag('br'),`-${addCurrency(item.discount_value)}`);
							}
						headRow.appendChild(tdCol);
					tableBody.appendChild(headRow);
				});

				if(data.posObj){
						headRow = cTag('tr');
						if(data.taxes_name1 ==='') headRow.style.display = 'none';
							tdCol = cTag('td',{ 'colspan':`4`,'class':`bgtitle`,'align':`right` });
								let taxableLabel = cTag('label');
								taxableLabel.innerHTML = Translate('Taxable Total')+' :';
							tdCol.appendChild(taxableLabel);
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable_total`,'id':`taxable_total`,'value':`0` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`nontaxable_total`,'id':`nontaxable_total`,'value':`0` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':`` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':`0` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':`0` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':`0` }));
							tdCol.appendChild(cTag('b',{ 'style':'display:none','id':`taxes_total1str` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
							tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
						headRow.appendChild(tdCol);
							tdCol = cTag('td',{ 'class':`bgtitle`,'align':`right` });
								bTag = cTag('b');
								bTag.innerHTML = addCurrency(data.taxable_total);
							tdCol.appendChild(bTag);
						headRow.appendChild(tdCol);
					tableBody.appendChild(headRow);

					let ti1Str = '';
					let taxes_total1 = data.taxes_total1;
					if(data.tax_inclusive1>0) {
						ti1Str = ' Inclusive';
						taxes_total1 = 0;
					}
					if(data.taxes_name1 !==''){							
							headRow = cTag('tr');
								tdCol = cTag('td',{ 'colspan':`4`,'align':`right` });
									bTag = cTag('b');
									bTag.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%${ti1Str}) :`;
								tdCol.appendChild(bTag);
							headRow.appendChild(tdCol);
								tdCol = cTag('td',{ 'align':`right` });
									bTag = cTag('b');
									bTag.innerHTML = addCurrency(taxes_total1);
								tdCol.appendChild(bTag);
							headRow.appendChild(tdCol);
						tableBody.appendChild(headRow);
					}						
					let ti2Str = '';
					let taxes_total2 = data.taxes_total2;
					if(data.tax_inclusive2>0) {
						ti2Str = ' Inclusive';
						taxes_total2 = 0;
					}
					if(data.taxes_name2 !==''){
							headRow = cTag('tr');
								tdCol = cTag('td',{ 'colspan':`4`,'align':`right` });
									strong = cTag('strong');
									strong.innerHTML = `${data.taxes_name2} (${data.taxes_percentage2}%${ti2Str}) :`;
								tdCol.appendChild(strong);
							headRow.appendChild(tdCol);
								tdCol = cTag('td',{ 'align':`right` });
									bTag = cTag('b');
									bTag.innerHTML = addCurrency(taxes_total2);
								tdCol.appendChild(bTag);
							headRow.appendChild(tdCol);
						tableBody.appendChild(headRow);
					}
					if(data.nontaxable_total > 0){
							headRow = cTag('tr');
								tdCol = cTag('td',{ 'colspan':`4`,'align':`right` });
									let nonTaxableLabel = cTag('label');
									nonTaxableLabel.innerHTML = Translate('Non Taxable Total')+' :';
								tdCol.appendChild(nonTaxableLabel);
							headRow.appendChild(tdCol);
								tdCol = cTag('td',{ 'align':`right` });
									bTag = cTag('b');
									bTag.innerHTML = addCurrency(data.nontaxable_total);
								tdCol.appendChild(bTag);
							headRow.appendChild(tdCol);
						tableBody.appendChild(headRow);
					}

						headRow = cTag('tr');
							tdCol = cTag('td',{ 'colspan':`4`,'class':`bgtitle`,'align':`right` });
								bTag = cTag('b');
								bTag.innerHTML = Translate('Grand Total')+' :';
							tdCol.appendChild(bTag);
						headRow.appendChild(tdCol);
							tdCol = cTag('td',{ 'class':`bgtitle`,'align':`right` });
								bTag = cTag('b');
								bTag.innerHTML = addCurrency(calculate('add',calculate('add',data.taxable_total,taxes_total1,2),calculate('add',taxes_total2,data.nontaxable_total,2),2));
							tdCol.appendChild(bTag);
						headRow.appendChild(tdCol);
					tableBody.appendChild(headRow);
						headRow = cTag('tr');
						if(OS ==='unknown'){
							tdCol = cTag('td',{ 'colspan':`2` });
							tdCol.innerHTML = ' ';
							headRow.appendChild(tdCol);
							tdCol = cTag('td',{ 'colspan':`3`,'class':`bgblack`, 'style': "font-weight: bold; font-size: 16px;" });
						}
						else{
							tdCol = cTag('td',{ 'colspan':`5`,'class':`bgblack`, 'style': "font-weight: bold; font-size: 16px;" });
						}
							tdCol.innerHTML = Translate('Take payment');
						headRow.appendChild(tdCol);
					tableBody.appendChild(headRow);
					
					let rowspan = data.paymentData.length; 
					data.paymentData.forEach((item,indx)=>{
						if(indx === 0){
								headRow = cTag('tr',{ 'class':`border` });
								if(OS ==='unknown'){
									tdCol = cTag('td',{ 'colspan':`2`,'rowspan':rowspan,'align':`right` });
									tdCol.innerHTML = ' ';
									headRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
								}
								else{
									tdCol = cTag('td',{ 'align':`left`,'colspan':`4` });
								}
										strong = cTag('strong');
										strong.innerHTML = DBDateToViewDate(item[0]);
									tdCol.appendChild(strong);
										let paymentsDiv = cTag('div',{ 'style':`float: right`,'nowrap':`` });
											strong = cTag('strong');
											strong.innerHTML = item[1]+':';
										paymentsDiv.appendChild(strong);
									tdCol.appendChild(paymentsDiv);
								headRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'align':`right` });
									tdCol.innerHTML = addCurrency(item[2]);
								headRow.appendChild(tdCol);
							tableBody.appendChild(headRow);
						}
						else{
							headRow = cTag('tr',{ 'class':`border` });
								tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
									strong = cTag('strong');
									strong.innerHTML = DBDateToViewDate(item[0]);
								tdCol.appendChild(strong);
									let payment2Div = cTag('div',{ 'style':`float: right`,'nowrap':`` });
										strong = cTag('strong');
										strong.innerHTML = item[1]+':';
									payment2Div.appendChild(strong);
								tdCol.appendChild(payment2Div);
							headRow.appendChild(tdCol);
								tdCol = cTag('td',{ 'align':`right` });
								tdCol.innerHTML = addCurrency(item[2]);
							headRow.appendChild(tdCol);
							tableBody.appendChild(headRow);
						}
					})
				}
			table.appendChild(tableBody);
			tableContainer.appendChild(table);
		}
		else{
			let select, tableHeadRow, thCol, tableBody, tdCol;
			let table = cTag('table',{ 'class':`table table-bordered`, 'style': "margin-bottom: 0px;" });
				let tableContentHead = cTag('thead');
					tableHeadRow = cTag('tr');
						thCol = cTag('th',{ 'width':`3%`, 'style': "text-align: right;" });
						thCol.innerHTML = '#';
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th');
						thCol.innerHTML = Translate('Description');
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`10%`,'class':`EstimateTitle`, 'style': "text-align: right;" });
						if(OS =='unknown'){thCol.innerHTML = Translate('Need/Have/OnPO');}
						else{thCol.innerHTML = Translate('Need-Have-onPO');}
						
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`8%`,'nowrap':``,'class':`DeliveredTitle`, 'style': "text-align: right;" });
						thCol.innerHTML = Translate('Time/Qty');
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
						thCol.innerHTML = Translate('Unit Price');
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
						thCol.innerHTML = Translate('Total');
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ 'width':`10px`, 'style': `text-align: center;` });
						thCol.appendChild(cTag('i',{ 'class':`fa fa-trash-o` }));
					tableHeadRow.appendChild(thCol);
				tableContentHead.appendChild(tableHeadRow);
			table.appendChild(tableContentHead);
				tableBody = cTag('tbody',{ 'id':`invoice_entry_holder` });
				loadCartData(tableBody,data.cartsData);
			table.appendChild(tableBody);
				tableBody = cTag('tbody');
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style': "text-align: right;", 'id':`barcodeserno` });
						tdCol.innerHTML = 1;
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'colspan':`6`, 'style': "text-align: left;" });
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'id':`temp_pos_cart_id`,'name':`temp_pos_cart_id`,'value':`0` }));
							let autoSearchDiv = cTag('div',{'class':'flexStartRow'});
								let autoSearchCol1 = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD4` });
									let newProductInGroup = cTag('div',{ 'class':`input-group` });
									newProductInGroup.appendChild(cTag('input',{ 'maxlength':`50`,'type':`text`,'id':`search_sku`,'name':`search_sku`,'class':`form-control search_sku ui-autocomplete-input`, 'style': "min-width: 120px;", autocomplete:'off', 'placeholder':Translate('Search by product name or SKU') }));
										let productSpan = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Product'),'class':`input-group-addon cursor` });
										if(!data.pSubPermission.includes('cnanp')) productSpan.addEventListener('click',()=>dynamicImport('./Products.js','AJget_ProductsPopup',['Repairs',0,0,addCartsProduct]));
										else productSpan.addEventListener('click',()=>noPermissionWarning('Product'));
										productSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
									newProductInGroup.appendChild(productSpan);
								autoSearchCol1.appendChild(newProductInGroup);
								autoSearchCol1.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_search_sku` }));

								let autoSearchCol2 = cTag('div',{ 'class':`columnXS12 columnSM8 columnMD8` });
								autoSearchCol2.appendChild(cTag('input',{ 'type':`hidden`,'name':`clickYesNo`,'id':`clickYesNo`,'value':`0` }));
								
									let productPickerButton = cTag('button',{ 'type':`button`,'name':`showcategorylist`,'id':`product-picker-button`,'click':showProductPicker,'class':`btn productPickerButton` });
									productPickerButton.innerHTML = Translate('Open Product Picker');
								autoSearchCol2.appendChild(productPickerButton);
									let oneTimeProductButton = cTag('button',{ 'click':()=>AJget_oneTimePopup(0),'class':`btn defaultButton`, 'style': "margin-left: 15px;" });
									if(OS =='unknown'){oneTimeProductButton.innerHTML = Translate('Add One Time Product');}
									else{oneTimeProductButton.append(cTag('i',{ 'class':`fa fa-plus`}), ' ', Translate('One Time Product'))}
									
								autoSearchCol2.appendChild(oneTimeProductButton);
							autoSearchDiv.append(autoSearchCol1, autoSearchCol2);
						tdCol.appendChild(autoSearchDiv);
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style':`padding: 0`,'colspan':`7` });
						tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_productlist` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`pagi_index`,'id':`pagi_index`,'value':`0` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppcategory_id`,'id':`ppcategory_id`,'value':`0` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppproduct_id`,'id':`ppproduct_id`,'value':`0` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`totalrowscount`,'id':`totalrowscount`,'value':`0` }));
							let searchRow = cTag('div',{ 'class':`flexSpaBetRow`,'id':`filterrow`,'style':'display:none; padding:10px 60px 0 50px; gap:5px; width: 100%;'});
								let searchDiv = cTag('div',{ 'style':'display:none', 'id':`filter_name_html`});
									let searchInGroup = cTag('div',{ 'class':`input-group` });
										const filter_name = cTag('input',{ 'maxlength':`50`,'type':`text`,'placeholder':Translate('Search name'),'value':``,'class':`form-control product-filter`,'name':`filter_name`,'id':`filter_name` });
										filter_name.addEventListener('keyup', e=>{if(e.which===13) showCategoryPPProduct()});
									searchInGroup.appendChild(filter_name);
										let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'click':showCategoryPPProduct,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search name') });
										searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
									searchInGroup.appendChild(searchSpan);
								searchDiv.appendChild(searchInGroup);
							searchRow.appendChild(searchDiv);
								let productFormDiv = cTag('div');
								productFormDiv.appendChild(cTag('label',{ 'id':`PPfromtodata` }));
							searchRow.appendChild(productFormDiv);
								let divSearch = cTag('div',{ 'style':'display:none', 'id':`all-category-button`});
									let divSearchInGroup = cTag('div',{ 'class':`input-group` });
										let categoryList = cTag('a',{ 'href':`javascript:void(0);`,'title':Translate('All Category List'),'click':reloadProdPkrCategory });
											let divSearchSpan = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "background: #a71d4c; color: #FFF; border-color: #a71d4c;" });
												const categoryLabel = cTag('label');
												categoryLabel.innerHTML = Translate('All Category List');
											divSearchSpan.appendChild(categoryLabel);
										categoryList.appendChild(divSearchSpan);
									divSearchInGroup.appendChild(categoryList);
								divSearch.appendChild(divSearchInGroup);
							searchRow.appendChild(divSearch);
						tdCol.appendChild(searchRow);
							let allProductListDiv = cTag('div',{ 'style': "position: relative; width: 100%;" });
								let allProductListColumn = cTag('div',{ 'class':`columnSM12`,'id':`product-picker`,'style':'display:none; align-items:center; min-height: 90px;'});
								allProductListColumn.appendChild(cTag('div',{ 'id':`allcategorylist`,'style':'display:none;padding:0 50px 0 40px;width:100%'}));
								allProductListColumn.appendChild(cTag('div',{ 'style':'display:none','id':`allproductlist`,'style':'padding:0 50px 0 40px;width:100%' }));
							allProductListDiv.appendChild(allProductListColumn);
								let previousButtonDiv = cTag('div',{ 'class':`prevlist`,'style':'display:none'});
									let previousButton = cTag('button',{ 'click':preNextCategory, 'style':'background:initial','type':`button` });
									previousButton.innerHTML = '‹';
								previousButtonDiv.appendChild(previousButton);
							allProductListDiv.appendChild(previousButtonDiv);
								let nextButtonDiv = cTag('div',{ 'class':`nextlist`,'style':'display:none'});
									let nextButton = cTag('button',{'click':preNextCategory, 'style':'background:initial', 'type':`button` });
									nextButton.innerHTML = '›';
								nextButtonDiv.appendChild(nextButton);
							allProductListDiv.appendChild(nextButtonDiv);
						tdCol.appendChild(allProductListDiv);
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr', {'class':`bgtitle`});
					if(data.taxableTotalDisplay === 0) tableHeadRow.style.display = 'none';
						tdCol = cTag('td',{ 'colspan':`3`,'align':`right` });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'style': "text-align: right;" });
							let timeQtyTotal = cTag('label',{ 'id':`timeQtyTotal` });
							timeQtyTotal.innerHTML = 0;
						tdCol.appendChild(timeQtyTotal);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{'align':`right` });
							let taxableTitle = cTag('label');
							taxableTitle.innerHTML = Translate('Taxable Total')+' :';
						tdCol.appendChild(taxableTitle);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{'align':`right` });
							const currencyDiv = cTag('b',{ 'id':`taxable_totalstr`});
						tdCol.appendChild(currencyDiv);  
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable_total`,'id':`taxable_total`,'value':`0.00` }));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'class':`bgtitle` });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);

				if(data.taxesRowCount>0){
					if(data.taxesRowCount===1){
						let txtInc = '';
						if(data.tax_inclusive1>0){txtInc = ' Inclusive';}

							tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{'colspan':`5`, 'style': "text-align: right;"});
                                    let taxNameDiv = cTag('div',{ 'style': "font-weight: bold;" });
                                    taxNameDiv.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%`+`${txtInc}):`;
                                tdCol.appendChild(taxNameDiv);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{'style': "text-align: right;" });
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':data.taxes_name1 }));
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':data.taxes_percentage1 }));
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':data.tax_inclusive1 }));
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
                                tdCol.appendChild(cTag('div',{ 'id':`taxes_total1str`, 'style': "font-weight: bold; width: 150px; float: right;" }));
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                                tdCol.appendChild(cTag('b',{ 'style':'display:none','id':`taxes_total2str` }));
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td');
                                tdCol.innerHTML = ' ';
                            tableHeadRow.appendChild(tdCol);
                        tableBody.appendChild(tableHeadRow);
					}
					else{
							tableHeadRow = cTag('tr');
								tdCol = cTag('td',{ 'colspan':`5`, 'style': "text-align: right;" });
									let taxesDiv = cTag('div',{'class':'flexEndRow', 'style': "align-items: center;"});
										let taxTitle = cTag('div',{ 'class':`columnXS3 columnMD1`, 'style': "font-weight: bold;" });
										taxTitle.innerHTML = `${Translate('Tax')}${data.tax1} :`;
									taxesDiv.appendChild(taxTitle);
										let selectTaxes = cTag('div',{ 'class':` columnXS6 columnMD2` });
											select = cTag('select',{ 'id':`taxes_id1`,'name':`taxes_id1`,'class':`form-control taxes_id`,'title':`1`,'change':()=>onChangeTaxesId(1) });
											setOptions(select,data.taxesOption1,1,1);
										selectTaxes.appendChild(select);
									taxesDiv.appendChild(selectTaxes);
									// taxesDiv.appendChild(cTag('div',{ 'class':`columnXS3 columnMD1`,'id':`taxes_total1str`, 'style': " min-width:150px;font-weight: bold; padding: 0;" }));
								tdCol.appendChild(taxesDiv);
								tableHeadRow.appendChild(tdCol);
								tdCol = cTag('td',{ 'style': "text-align: right; vertical-align: middle;" });
								tdCol.appendChild(cTag('b',{ 'class':`columnXS3 columnMD1`,'id':`taxes_total1str`}))
								tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
								tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':data.taxes_name1 }));
								tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':data.taxes_percentage1 }));
								tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':data.tax_inclusive1 }));
								tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_taxes_id1` }));
							tableHeadRow.appendChild(tdCol);
								tdCol = cTag('td');
								tdCol.innerHTML = ' ';
							tableHeadRow.appendChild(tdCol);
						tableBody.appendChild(tableHeadRow);
						if(data.defaultTaxCount>1){
								tableHeadRow = cTag('tr');
									tdCol = cTag('td',{ 'colspan':`5`, 'style': "text-align: right;" });
										let taxesDiv = cTag('div',{'class':'flexEndRow', 'style': "align-items: center;"});
											let taxTitle = cTag('div',{ 'class':`columnXS3 columnMD1`, 'style': "font-weight: bold;" });
											taxTitle.innerHTML = Translate('Tax2')+' :';
										taxesDiv.appendChild(taxTitle);
											let taxDropDown = cTag('div',{ 'class':`columnXS6 columnMD2`, 'style': "font-weight: bold;" });
												let selectTax = cTag('select',{ 'id':`taxes_id2`,'name':`taxes_id2`,'class':`form-control taxes_id`,'title':`2`,'change':()=>onChangeTaxesId(2)});
												selectTax.appendChild(cTag('option',{ 'value':`0` }));
												setOptions(selectTax,data.taxesOption2,1,1);
											taxDropDown.appendChild(selectTax);
										taxesDiv.appendChild(taxDropDown);
									tdCol.appendChild(taxesDiv);
								tableHeadRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'style': "text-align: right; vertical-align: middle;" });
									tdCol.appendChild(cTag('b',{ 'class':`columnXS3 columnMD1`,'id':`taxes_total2str`}))
									tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
									tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':data.taxes_name2 }));
									tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':data.taxes_percentage2 }));
									tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':data.tax_inclusive2 }));
									tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_taxes_id2` }));
								tableHeadRow.appendChild(tdCol);
									tdCol = cTag('td');
									tdCol.innerHTML = ' ';
								tableHeadRow.appendChild(tdCol);
							tableBody.appendChild(tableHeadRow);
						}
						else{
							tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
							tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
							tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
							tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
							tableBody.appendChild(cTag('b',{ 'style':'display:none','id':`taxes_total2str` }));
						}
					}
				}
				else{
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':`` }));
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':`0` }));
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':`0` }));
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
					tableBody.appendChild(cTag('b',{ 'style':'display:none','id':`taxes_total1str` }));
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
					tableBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
					tableBody.appendChild(cTag('b',{ 'style':'display:none','id':`taxes_total2str` }));
				}

					tableHeadRow = cTag('tr',{ 'id':`nontaxable_totalrow` });
						tdCol = cTag('td',{ 'colspan':`5`,'align':`right` });
							let nonTaxDiv = cTag('label');
							nonTaxDiv.innerHTML = Translate('Non Taxable Total')+' :';
						tdCol.appendChild(nonTaxDiv);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{'align':`right` });
						tdCol.appendChild(cTag('b',{ 'id':`nontaxable_totalstr`}));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`nontaxable_total`,'id':`nontaxable_total`,'value':`0` }));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td');
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr', {'class':`bgtitle`});
						tdCol = cTag('td',{ 'colspan':`5`,'align':`right` });
							let grandDiv = cTag('label');
							grandDiv.innerHTML = Translate('Grand Total')+' :';
						tdCol.appendChild(grandDiv);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{'align':`right` });
						tdCol.appendChild(cTag('b',{ 'id':`grand_totalstr` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':`0` }));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td');
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr');
					if(OS ==='unknown'){
						tdCol = cTag('td',{ 'colspan':`2` });
						tdCol.innerHTML = ' ';
						tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'colspan':`5`,'class':`bgblack`, 'style': "font-weight: bold; font-size: 16px;" });
					}
					else{
						tdCol = cTag('td',{ 'colspan':`7`,'class':`bgblack`, 'style': "font-weight: bold; font-size: 16px;" });
					}
						tdCol.innerHTML = Translate('Take payment');
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
			table.appendChild(tableBody);
				tableBody = cTag('tbody',{ 'id':`loadPOSPayment` });
				loadPaymentData(tableBody,data.paymentData);
			table.appendChild(tableBody);
				tableBody = cTag('tbody');
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'colspan':`6`,'align':`right` });
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'readonly':``,'value':`${data.payment_datetime}?>`,'required':``,'name':`payment_datetime`,'id':`payment_datetime` }));
							let paymentDiv = cTag('div',{ 'class':`flexEndRow` });
								let paymentCol = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
									if(data.multiple_cash_drawers>0 && data.drawerOpt.length>0){
										select = cTag('select',{ 'class':`form-control`,'name':`drawer`,'id':`drawer`,'change':()=>setCDinCookie});
										select.addEventListener('change',togglePaymentButton);
										if(data.drawer===''){
											let option = cTag('option',{ 'value':`` });
												option.innerHTML = Translate('Select Drawer');
											select.appendChild(option);
										}
										setOptions(select,data.drawerOpt.filter(item=>item!==''),0,0);
										select.value = data.drawer;
									}
									else{
										select = cTag('input',{ 'type':`hidden`,'name':`drawer`,'id':`drawer`,'value':`` });
									}
								paymentCol.appendChild(select);
							paymentDiv.appendChild(paymentCol);

								let typeColumn2 = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2` });
									let inputGroupMethod = cTag('div',{ 'class':`input-group`, 'style': "min-width: 120px;"});
										let typeSpan = cTag('span', { 'data-toggle':`tooltip`, 'style': "font-weight: bold;", 'data-original-title':Translate('Type'), 'class':`input-group-addon cursor`});
										typeSpan.innerHTML = Translate('Type')+' :';

										let selectMethod = cTag('select',{ 'class':`form-control`,'name':`method`,'id':`method`,'change':checkMethod });
										setOptions(selectMethod,data.methodOpt,0,0);
									inputGroupMethod.append(typeSpan, selectMethod);
								typeColumn2.appendChild(inputGroupMethod);
							paymentDiv.appendChild(typeColumn2);

								let currencyColumn2 = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2`});
									let inputGroupAmount = cTag('div',{ 'class':`input-group`, 'style': "min-width: 120px;"});
										let currencySpan = cTag('span',{ 'data-toggle':`tooltip`, 'style': "font-weight: bold;", 'data-original-title':Translate('Currency'),'class':`input-group-addon cursor`});
										currencySpan.innerHTML = currency;
										const input = cTag('input',{ 'type': "text",'data-min':`-${data.paymentData.reduce((total,item)=>total+item.payment_amount,0)}`,'data-max':'9999999.99','data-format':'d.dd','value':`0`,'name':`amount`,'id':`amount`,'class':` form-control`, 'style': "font-weight: bold; text-align: right;", 'keyup':checkMethod });
										input.addEventListener('keydown',event=>{if(event.which===13) addPOSPayment()});
										controllNumericField(input, '#error_amount');
									inputGroupAmount.append(currencySpan, input);
								currencyColumn2.appendChild(inputGroupAmount);
							paymentDiv.appendChild(currencyColumn2);
							
							paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`multiple_cash_drawers`,'id':`multiple_cash_drawers`,'value':data.multiple_cash_drawers }));
							paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`returnURL`,'id':`returnURL`,'value':`${location.origin}/Repairs/edit/${data.repairs_id}/` }));
							paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`sqrup_currency_code`,'id':`sqrup_currency_code`,'value':data.sqrup_currency_code }));
							paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`webcallbackurl`,'id':`webcallbackurl`,'value':data.webcallbackurl }));
							paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`accounts_id`,'id':`accounts_id`,'value':data.accounts_id }));
							paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`user_id`,'id':`user_id`,'value':data.user_id }));
						tdCol.appendChild(paymentDiv);
						tdCol.appendChild(cTag('span',{ 'id':`error_amount`,'class':`errormsg` }));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'id':`buttonPayment`,'style':'vertical-align: middle;' });
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'class':`bgtitle`,'colspan':`5`,'align':`right` });
							let amountDueLabel = cTag('span',{ 'for':`amount_due`,'id':`amount_duetxt`, 'style': "font-weight: bold;" });
							amountDueLabel.innerHTML = Translate('Amount Due');
						tdCol.appendChild(amountDueLabel);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'class':`bgtitle`,'align':`right` });
						tdCol.appendChild(cTag('span',{ 'id':`amountduestr`, 'style': "font-weight: bold;" }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`amount_due`,'id':`amount_due`,'value':`0.00` }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`changemethod`,'id':`changemethod`,'value':'Cash' }));
						tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`available_credit`,'id':`available_credit`,'value':data.available_credit }));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'class':`bgtitle` });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr',{ 'id':`available_creditrow` });
					if(data.avaCreRowSty === 0) tableHeadRow.style.display = 'none';
						tdCol = cTag('td',{ 'colspan':`5`,'align':`right` });
							let creditLabel = cTag('span', {'style': "font-weight: bold;"});
							creditLabel.innerHTML = Translate('Customer has available credit of')+' :';
						tdCol.appendChild(creditLabel);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'align':`right` });
							let creditSpan = cTag('span',{ 'id':`available_credit_label`, 'style': "font-weight: bold;" });
							creditSpan.innerHTML = addCurrency(data.available_credit);
						tdCol.appendChild(creditSpan);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td');
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'class':data.ashbdclass,'colspan':`6` });
							const buttonNames = cTag('div',{ 'class': "flexEndRow", 'style': "align-items: center;" });
								let completeDiv = cTag('div');
								completeDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`completed`,'id':`completed`,'value':`0` }));
								completeDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value':segment1 }));
									let completeButtonDiv = cTag('div',{ 'class':`input-group` });
										let completeButton = cTag('button',{ 'name':`CompleteBtn`,'id':`CompleteBtn`,'class':`btnFocus moneyIcon cursor`,'style':'display:none','click':completeRepair });
											let moneyIcon = cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;" });
											let completeLabel = cTag('label');
											completeLabel.innerHTML = Translate('Complete');
										completeButton.append(moneyIcon, completeLabel);
									completeButtonDiv.appendChild(completeButton);
										let completeBtnDiv = cTag('button',{ 'name':`CompleteBtnDis`,'id':`CompleteBtnDis`,'class':`btnFocus` });
											let moneySpan = cTag('span',{ 'class':`input-group-addon cursor` });
											moneySpan.appendChild(cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;" }));
										completeBtnDiv.appendChild(moneySpan);
											let inputSpan = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "padding-left: 0;" });
												let inputLabel = cTag('label');
												inputLabel.innerHTML = Translate('Complete');
											inputSpan.appendChild(inputLabel);
										completeBtnDiv.appendChild(inputSpan);
									completeButtonDiv.appendChild(completeBtnDiv);
								completeDiv.appendChild(completeButtonDiv);

								let cancelButtonDiv = cTag('div',{ 'id':`status_cancelled`, 'style': "margin-right: 15px;" });
									let cancelDiv = cTag('div',{ 'class':`input-group` });
										let cancelButton = cTag('button',{ 'class':`btnFocus iconButton cursor` });
										if(allowed['2'] && allowed['2'].includes('cncl')) cancelButton.addEventListener('click', function (){noPermissionWarning('to Cancel Repairs')});
										else cancelButton.addEventListener('click', cancelRepair);
											let removeSpan = cTag('i',{ 'class':`fa fa-remove`, 'style': "font-size: 1.5em;" });
											let cancelLabel = cTag('label');
											cancelLabel.innerHTML = ` ${Translate('Cancel')} `;
										cancelButton.append(removeSpan, cancelLabel);
									cancelDiv.appendChild(cancelButton);
								cancelButtonDiv.appendChild(cancelDiv);
							buttonNames.append(cancelButtonDiv, completeDiv);
						tdCol.appendChild(buttonNames);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'class':data.ashbdclass });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				tableBody.appendChild(tableHeadRow);
			table.appendChild(tableBody);
			tableContainer.appendChild(table);
		}

		tableContainer.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos_id`,'id':`pos_id`,'value':data.pos_id }));
		tableContainer.appendChild(cTag('input',{ 'type':`hidden`,'name':`repairs_id`,'id':`repairs_id`,'value':data.repairs_id }));
		tableContainer.appendChild(cTag('input',{ 'type':`hidden`,'name':`customer_id`,'id':`customer_id`,'value':data.customer_id }));
		tableContainer.appendChild(cTag('input',{ 'type':`hidden`,'name':`properties_id`,'id':`properties_id`,'value':data.properties_id }));
		tableContainer.appendChild(cTag('input',{ 'type':`hidden`,'name':`default_invoice_printer`,'id':`default_invoice_printer`,'value':data.default_invoice_printer }));

		document.querySelector('#srepairs_id').value = data.repairs_id;
		document.querySelector('#table_idValue').value = data.repairs_id;
		document.querySelector('#digital_signature_btn').addEventListener('click',()=>printbyurl(`/${segment1}/prints/large/${data.repairs_id}/signature`));
		createTabs(document.getElementById('ticketTabs'));

		setTimeout(function() {
			if(document.getElementById("taxes_id1")){
				document.getElementById("taxes_id1").value = data.option1Val;
			}
			if(document.getElementById("taxes_id2")){
				document.getElementById("taxes_id2").value = data.option2Val;
			}
			checkMethod();
			togglePaymentButton();

			if(data.startNotifyPopup && segment4==='add-success'){
				checkRepairsStatus(1);
			}
		}, 500);   
		
		multiSelectAction('repairPrint');

		if(!['Invoiced','Cancelled'].includes(data.status)){
			AJautoComplete_cartProduct();                   
			cartsAutoFuncCall();
			setTimeout(function() {document.getElementById("search_sku").focus();}, 500);
		}
		if(document.getElementById("frompage")) check_andupdatestatustab();
		if(document.getElementById("method") && document.getElementById("buttonPayment")) showOrNotSquareup();
		filter_Repairs_edit();
	}
}

async function filter_Repairs_edit(){
    let page = 1;
    document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['srepairs_id'] = document.getElementById("table_idValue").value;
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

async function loadTableRows_Repairs_edit(){
	const jsonData = {};
	jsonData['srepairs_id'] = document.getElementById("table_idValue").value;
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

async function emailrepairdetails(event=false){
	if(event) event.preventDefault();

	let repairs_id =  document.getElementById("repairs_id").value;
	let email_address = document.getElementById("email_address").value;
	
	actionBtnClick('.sendbtn', Translate('Sending'), 1);

	const jsonData = {};
	jsonData['email_address'] = email_address;
	jsonData['repairs_id'] = repairs_id;

    const url = '/'+segment1+'/AJsend_RepairsEmail/';
	fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr !=='Ok'){
			showTopMessage('alert_msg', data.returnStr);
		}
		else{
			showTopMessage('success_msg', Translate('Mail has been successfully sent.'));
			setTimeout(() => {
				document.querySelectorAll(".emailform").forEach(oneFieldObj=>{
					if(oneFieldObj.style.display !== 'none'){
						oneFieldObj.style.display = 'none';
					}
				});
			}, 1000);
		}
		actionBtnClick('.sendbtn', Translate('Email'), 0);
	}
	return false;
}

//=======add=========
async function updateCustomerId(){
	let customer_id = document.getElementById('customer_id');
	let customer_name = document.getElementById('customer_name');
	if(customer_id.value==='0' && customer_name.value!==''){
		const jsonData = {"keyword_search":this.value, 'fieldIdName':'customer_name', 'frompage':segment1};
		
		const url = "/Common/AJautoComplete_customer_name";
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.returnStr.length>0) document.getElementById('customer_id').value = data.returnStr[0].id;
			else{
				customer_name.focus();
				showTopMessage('error_msg','Please click the new button to add a new customer because no customer was found for the search name you entered');
			}
		}
	}
}

function add(){
	const Dashboard = document.getElementById('viewPageInfo');
	Dashboard.innerHTML = '';
		let errorSpan, errorDiv;
		const titleRow = cTag('div',{ 'style':`text-align: left; padding: 5px;` });
			let titleHeader = cTag('h2');
			titleHeader.append(Translate('New Repair Ticket')+' ');
			titleHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('This page captures the basic details required to create a repair ticket') }));
		titleRow.appendChild(titleHeader);
	Dashboard.appendChild(titleRow);
		let newRepairAddRow = cTag('div',{ 'class':`flexSpaBetRow` });
			let newRepairAddColumn = cTag('div',{ 'class':`columnSM12`});
				let callOutDiv = cTag('div',{ 'class':`innerContainer`});
					const repairAddForm = cTag('form',{ 'id':`frmrepairs`,'action':`#`,'name':`frmrepairs`,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
						const startEstimateRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
							const startEstimateTitle = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
								let startEstimateLabel = cTag('label',{ 'for':`startEstimate`,'data-placement':`bottom` });
								startEstimateLabel.innerHTML = Translate('Start As Estimate')+'?';
							startEstimateTitle.appendChild(startEstimateLabel);
						startEstimateRow.appendChild(startEstimateTitle);
							const estimateDropDown = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
								let selectEstimate = cTag('select',{ 'required':``,'class':`form-control customer_devices`,'name':`startEstimate`,'id':`startEstimate` });
									let noOption = cTag('option',{ 'value':Translate('No') });
									noOption.innerHTML = Translate('No');
								selectEstimate.appendChild(noOption);
									let yesOption = cTag('option',{ 'value':Translate('Yes') });
									yesOption.innerHTML = Translate('Yes');
								selectEstimate.appendChild(yesOption);
							estimateDropDown.appendChild(selectEstimate);
						startEstimateRow.appendChild(estimateDropDown);
					repairAddForm.appendChild(startEstimateRow);

						const customerNameRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
							const customerNameTitle = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
								let customerNameLabel = cTag('label',{ 'for':`customer_name`,'data-placement':`bottom` });
								customerNameLabel.append(Translate('Customer Name'));
									errorSpan = cTag('span', {class: "errormsg"});
									errorSpan.innerHTML = '*';
								customerNameLabel.appendChild(errorSpan);
							customerNameTitle.appendChild(customerNameLabel);
						customerNameRow.appendChild(customerNameTitle);
							const customerNameField = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
								const customerInGroup = cTag('div',{ 'class':`input-group`,'id':`customerNameField` });
									const customerName = cTag('input',{ 'autocomplete':`off`,'maxlength':`50`,'type':`text`,'value':``,'required':``,'name':`customer_name`,'id':`customer_name`,'class':`form-control ui-autocomplete-input`,'placeholder':Translate('Search Customers') });
									// customerName.addEventListener('blur',updateCustomerId);
								customerInGroup.appendChild(customerName);
									let newSpan = cTag('span',{ 'id':'add_new_customer_btn','data-toggle':`tooltip`,'data-original-title':Translate('Add New Customer'),'class':`input-group-addon cursor` });
									newSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
								customerInGroup.appendChild(newSpan);
							customerNameField.appendChild(customerInGroup);
							customerNameField.appendChild(cTag('input',{ 'type':`hidden`,'name':`customer_id`,'id':`customer_id`,'value':`0` }));
							customerNameField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_customer_id` }));
						customerNameRow.appendChild(customerNameField);
							const errorColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
							errorColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_customer_name` }));
						customerNameRow.appendChild(errorColumn);
					repairAddForm.appendChild(customerNameRow);

						let customerDeviceRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;"  });
							let customerDeviceTitle = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
								let customerDeviceLabel = cTag('label',{ 'for':`customer_devices`,'data-placement':`bottom` });
								customerDeviceLabel.append(Translate('Customers Devices'));
									errorSpan = cTag('span', {class: "errormsg"});
									errorSpan.innerHTML = '*';
								customerDeviceLabel.appendChild(errorSpan);
							customerDeviceTitle.appendChild(customerDeviceLabel);
						customerDeviceRow.appendChild(customerDeviceTitle);
							const newCustomerDevice = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
								const newCustomerInGroup = cTag('div',{ 'class':`input-group` });
									let selectCustomer = cTag('select',{ 'required':``,'class':`form-control customer_devices`,'name':`customer_devices`,'id':`customer_devices` });
									selectCustomer.appendChild(cTag('option',{ 'value':`` }));
								newCustomerInGroup.appendChild(selectCustomer);
									let newCustomerSpan = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Customer Properties'),'class':`input-group-addon cursor` });
									newCustomerSpan.addEventListener('click',()=>dynamicImport('./Customers.js','AJget_propertiesPopup',[0]));
									newCustomerSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
								newCustomerInGroup.appendChild(newCustomerSpan);
							newCustomerDevice.appendChild(newCustomerInGroup);
						customerDeviceRow.appendChild(newCustomerDevice);
					repairAddForm.appendChild(customerDeviceRow);

						const dueDateRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
							const dueDateTitle = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
								const dueDateLabel = cTag('label',{ 'for':`due_datetime`,'data-placement':`bottom` });
								dueDateLabel.innerHTML = Translate('Due Date');
							dueDateTitle.appendChild(dueDateLabel);
						dueDateRow.appendChild(dueDateTitle);
							let dateRangeColumn = cTag('div',{ 'class':`columnXS6 columnSM3 columnMD2` });
							dateRangeColumn.appendChild(cTag('input',{ 'autocomplete':`off`,'id':`due_datetime`,'class':`form-control`,'name':`due_datetime`,'value':``,'maxlength':`10`,'type':`text` }));
						dueDateRow.appendChild(dateRangeColumn);
							let timeColumn = cTag('div',{ 'class':`columnXS6 columnSM3 columnMD2` });
							timeColumn.appendChild(cTag('input',{ 'autocomplete':`off`,'id':`due_time`,'class':`form-control`,'placeholder':Translate('Time'),'name':`due_time`,'value':``,'maxlength':`10`,'type':`text` }));
						dueDateRow.appendChild(timeColumn);
						dueDateRow.appendChild(cTag('div',{ 'class':`columnXS12 columnSM6` }));
					repairAddForm.appendChild(dueDateRow);

						const problemRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
							const problemTitle = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
								const problemLabel = cTag('label',{ 'for':`problem`,'data-placement':`bottom` });
								problemLabel.append(Translate('Problem'));
									errorSpan = cTag('span', {class: "errormsg"});
									errorSpan.innerHTML = '*';
								problemLabel.appendChild(errorSpan);
							problemTitle.appendChild(problemLabel);
						problemRow.appendChild(problemTitle);
							const problemDropDown = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
								const problemInGroup = cTag('div',{ 'class':`input-group`,'id':`problemsStr` });
									let selectProblem = cTag('select',{ 'class':`form-control`,'name':`problem`,'id':`problem` });
									selectProblem.appendChild(cTag('option',{ 'value':`` }));
								problemInGroup.appendChild(selectProblem);
								problemInGroup.appendChild(cTag('input',{ 'type':`text`,'value':``,'maxlength':`35`,'name':`problem_name`,'id':`problem_name`,'class':`form-control`, 'style': "display: none;"}));
									let problemSpan = cTag('span',{ 'click':showNewInputOrSelect,'data-toggle':`tooltip`,'data-original-title':Translate('Add New Problem'),'class':`input-group-addon cursor showNewInputOrSelect` });
									problemSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
								problemInGroup.appendChild(problemSpan);
							problemDropDown.appendChild(problemInGroup);
						problemRow.appendChild(problemDropDown);
							errorDiv = cTag('div',{ 'class':`columnXS12 columnSM6` });
							errorDiv.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_problem` }));
						problemRow.appendChild(errorDiv);
					repairAddForm.appendChild(problemRow);

						const passwordRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
							const passwordTitle = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
								const passwordLabel = cTag('label',{ 'for':`lock_password`,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Does this device have a password lock from the customer? If yes, enter it here') });
								passwordLabel.innerHTML = Translate('Password');
							passwordTitle.appendChild(passwordLabel);
						passwordRow.appendChild(passwordTitle);
							const passwordField = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
							passwordField.appendChild(cTag('input',{ 'maxlength':`20`,'type':`text`,'class':`form-control`,'name':`lock_password`,'id':`lock_password`,'value':`` }));
							passwordField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_lock_password` }));
						passwordRow.appendChild(passwordField);
							errorDiv = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
							errorDiv.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_lock_password` }));
						passwordRow.appendChild(errorDiv);
					repairAddForm.appendChild(passwordRow);

						const binRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center;" });
							const binTitle = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
								const binLabel = cTag('label',{ 'for':`bin_location`,'data-placement':`bottom` });
								binLabel.innerHTML = Translate('Bin Location');
							binTitle.appendChild(binLabel);
						binRow.appendChild(binTitle);
							const binField = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
							binField.appendChild(cTag('input',{ 'type':`text`,'autocomplete':`off`,'class':`form-control`,'name':`bin_location`,'id':`bin_location`,'value':``,'maxlength':`20` }));
						binRow.appendChild(binField);
							errorDiv = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
							errorDiv.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_bin_location` }));
						binRow.appendChild(errorDiv);
					repairAddForm.appendChild(binRow);
					repairAddForm.appendChild(cTag('div',{ 'id':'form_container','class':`flexStartRow` }));
					repairAddForm.appendChild(cTag('div',{ 'id':'notifications_container','class':`flexStartRow` }));
						const addButtonRow = cTag('div',{ 'class':`columnXS12 columnSM6`});
							const addButtonName = cTag('div',{ 'class':`flexEndRow` });
							addButtonName.appendChild(cTag('input',{ 'type':`hidden`,'name':`repairs_id`,'id':`repairs_id`,'value':`0` }));
							addButtonName.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`,'id':`cancelbutton`,'click':()=>redirectTo('/Repairs/lists'),'value':Translate('Cancel') }));
							addButtonName.appendChild(cTag('input',{ 'type':`submit`,'class':`btn completeButton`, 'style': "margin-left: 10px;", 'name':`submit`,'id':`submit`,'value':Translate('Add') }));
						addButtonRow.appendChild(addButtonName);
					repairAddForm.appendChild(addButtonRow);
					repairAddForm.appendChild(cTag('div',{ 'class':`flexSpaBetRow`,'id':`notestr` }));
				callOutDiv.appendChild(repairAddForm);
			newRepairAddColumn.appendChild(callOutDiv);
		newRepairAddRow.appendChild(newRepairAddColumn);
	Dashboard.appendChild(newRepairAddRow);
	applySanitizer(Dashboard);
	AJautoComplete('customer_name', AJget_propertiesOpt);
	AJ_add_MoreInfo();
}

async function AJ_add_MoreInfo(){
    const url = '/'+segment1+'/AJ_add_MoreInfo';
	fetchData(afterFetch,url,{});

    function afterFetch(data){
		document.getElementById('add_new_customer_btn').addEventListener('click',()=>dynamicImport('./Customers.js','AJget_CustomersPopup',[0,AJget_propertiesOpt]));
		setOptions(document.getElementById('problem'),data.proOpt,0,1);

		let label;
		if(Object.keys(data.formsFields).length>0){
			let form_container = document.getElementById('form_container');
				const formColumn = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
					let formLabel = cTag('label',{ 'for':`bin_location`,'data-placement':`bottom` });
					formLabel.innerHTML = Translate('Form');
				formColumn.appendChild(formLabel);
			form_container.appendChild(formColumn);
				const formValue = cTag('div',{ 'class':`columnXS12 columnSM6 flexStartRow`});                     
				for (const key in data.formsFields) {
					let requiredClass = '';
					if(data.formsFields[key][1]===1) requiredClass = ' requiredForm';
						let button = cTag('button',{ 'type':`button`, 'class':`btn defaultButton addForm ${requiredClass}`, 'id':`addForm${key}`, 'name':`addForm`, 'title':key, 'style':"margin-right: 5px;"});
						button.innerHTML = data.formsFields[key][0];
					formValue.appendChild(button);                        
				}
					let select = cTag('select',{ 'name':`newforms_id`,'id':`newforms_id`,'style':'display:none' });
					select.appendChild(cTag('option',{ 'value':`` }));
					for (const key in data.formsFields){
							let option = cTag('option',{ 'value':key });
							option.innerHTML = data.formsFields[key][0];
						select.appendChild(option);
					}
				formValue.appendChild(select);
				formValue.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_newforms_id` }));
					let formsInfo = cTag('div',{ 'id':`formsInfo`,'style':'width:100%' });
					addFormsInfo(formsInfo,data.formsInfo);
				formValue.appendChild(formsInfo);
			form_container.appendChild(formValue);
		}
		if(data.notify_default_email !=='' || data.notify_default_sms !== ''){
			let notifications_container = document.getElementById('notifications_container');
				const notificationColumn = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD2` });
					label = cTag('label',{ 'for':`notify_how_email` });
					label.innerHTML = Translate('Send Notifications');
				notificationColumn.appendChild(label);
			notifications_container.appendChild(notificationColumn);
				let notificationField = cTag('div',{ 'class':`columnXS12 columnSM6 columnMD4` });
				if(data.notify_default_email !==''){
						const notificationRow = cTag('div',{ 'class':`flexSpaBetRow`});
							const notificationTitle = cTag('div',{ 'class':`columnXS5 columnSM4` });
								label = cTag('label',{ 'for':`notify_how_email` });
								label.appendChild(cTag('input',{ 'data-already-checked':false,'class':`notify_how`,'type':`radio`,'name':`notify_how`,'id':`notify_how_email`,'value':`1` }));
								label.append(' '+Translate('Via Email'));
							notificationTitle.appendChild(label);
							notificationTitle.append('  ');
						notificationRow.appendChild(notificationTitle);
							const notificationEmailColumn = cTag('div',{ 'class':`columnXS7 columnSM8` });
							notificationEmailColumn.appendChild(cTag('input',{ 'type':`text`,'name':`notify_email`,'id':`notify_email`,'class':`form-control notify_email`,style:'display:none','placeholder':Translate('Email Address') }));
							notificationEmailColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_notify_how` }));
						notificationRow.appendChild(notificationEmailColumn);                               
					notificationField.appendChild(notificationRow);
				}
				if(data.notify_default_sms !==''){
						const smsRow = cTag('div',{ 'class':`flexSpaBetRow` });
							const smsColumn = cTag('div',{ 'class':`columnXS5 columnSM4` });
								label = cTag('label',{ 'for':`notify_how_sms` });
								label.appendChild(cTag('input',{ 'data-already-checked':false,'class':`notify_how`,'type':`radio`,'name':`notify_how`,'id':`notify_how_sms`,'value':`2` }));
								label.append(' '+Translate('Via SMS'));
							smsColumn.appendChild(label);
							smsColumn.append('  ');
						smsRow.appendChild(smsColumn);
							let notifySmsColumn = cTag('div',{ 'class':`columnXS7 columnSM8` });
							notifySmsColumn.appendChild(cTag('input',{ 'type':`text`,'name':`notify_sms`,'id':`notify_sms`,'class':`form-control notify_sms`,style:'display:none','placeholder':Translate('Phone No.') }));
							notifySmsColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_notify_sms` }));
						smsRow.appendChild(notifySmsColumn);
							let error2Div = cTag('div',{ 'class':`columnSM4` });
							error2Div.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_notify_sms` }));
						smsRow.appendChild(error2Div);
					notificationField.appendChild(smsRow);
				}

			notifications_container.appendChild(notificationField);
			notifications_container.parentNode.appendChild(cTag('input',{ 'type':`hidden`,'name':`notifyChecked`,'id':`notifyChecked`,'value':`0` }));
		}

		if(document.getElementById("frmrepairs")){
			document.querySelector("#frmrepairs").addEventListener('submit', AJsaveRepairs);
		}
		if(document.getElementById("due_datetime")){
			date_picker('#due_datetime');
		}
		if(document.querySelectorAll(".addForm").length>0){
			document.querySelectorAll(".addForm").forEach(oneRowObj=>{
				oneRowObj.addEventListener('click', e => {
					document.querySelector("#newforms_id").value = e.target.title;
					addNewFormsData(0);
				});
			});
		}
		if(document.querySelectorAll(".newFormId").length>0){
			document.querySelectorAll(".newFormId").forEach(e=>{
				let forms_id = e.value;
				if(document.querySelector("#addForm"+forms_id))
					document.querySelector("#addForm"+forms_id).disabled = true;
			});
		}
		if(document.querySelectorAll(".notify_how").length>0){
			checkNotify();
			document.querySelectorAll(".notify_how").forEach(oneRowObj=>{
				oneRowObj.addEventListener('click', checkNotify);
			});
		}
		if(document.getElementById("due_datetime")){
			if(document.getElementById("customer_name")){
				setTimeout(function() {document.getElementById("customer_name").focus();}, 500);
			}                			 
			
			document.querySelector("#customer_name").addEventListener('keyup', function() {
				document.getElementById('customer_id').value = 0;
			});
		}
	}
}

function addFormsInfo(parentNode,infoData){
	if(infoData && infoData.length){
			let addHeadRow, thCol,tdCol;
			let addFormRow = cTag('div',{ 'class':`flexStartRow` });
				let addFormColumn = cTag('div',{ 'class':`columnXS12` });
					let noMoreDiv = cTag('div',{ 'id':`no-more-tables` });
						const table = cTag('table',{ 'class':`table-bordered table-striped table-condensed cf listing` });
							const addHead = cTag('thead',{ 'class':`cf` });
								addHeadRow = cTag('tr');
									thCol = cTag('th',{ 'style': `text-align: center;` });
									thCol.innerHTML = Translate('Name');
								addHeadRow.appendChild(thCol);
									thCol = cTag('th',{ 'style': `text-align: center;`, 'width':`10%` });
									thCol.innerHTML = Translate('Public');
								addHeadRow.appendChild(thCol);
									thCol = cTag('th',{ 'style': `text-align: center;`, 'width':`10%` });
									thCol.innerHTML = Translate('Required');
								addHeadRow.appendChild(thCol);
									thCol = cTag('th',{ 'style': `text-align: center;`, 'width':`10%` });
									thCol.innerHTML = Translate('Remove');
								addHeadRow.appendChild(thCol);
							addHead.appendChild(addHeadRow);
						table.appendChild(addHead);
							const tbody = cTag('tbody');
							infoData.forEach(item=>{
									addHeadRow = cTag('tr');
										tdCol = cTag('td',{ 'data-title':Translate('Name'),'align':`left` });
										tdCol.innerHTML = item.form_name;
									addHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'data-title':Translate('Public'),'align':`center` });
										tdCol.innerHTML = item.form_public;
									addHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'data-title':Translate('Required'),'align':`center` });
										tdCol.append(item.required);
										tdCol.appendChild(cTag('input',{ 'type':`hidden`,'class':`newFormId`,'value':item.forms_id }));
									addHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ 'data-title':Translate('Action'),'align':`center` });
										tdCol.appendChild(cTag('i',{ 'class':`fa fa-remove cursor`, 'style': "color: #F00;", 'title':Translate('Action'),'click':()=>removeFormRow(item.forms_id)}));
									addHeadRow.appendChild(tdCol);
								tbody.appendChild(addHeadRow);
							})
						table.appendChild(tbody);
					noMoreDiv.appendChild(table);
				addFormColumn.appendChild(noMoreDiv);
			addFormRow.appendChild(addFormColumn);
		parentNode.appendChild(addFormRow);
	}
}

async function AJget_propertiesOpt(){
	const customers_id = document.getElementById("customer_id").value;

	if(customers_id !=='' && customers_id>0){
		const jsonData = {};
        jsonData['customers_id'] = customers_id;

        const url = '/Repairs/AJget_propertiesOpt';
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			document.getElementById('customer_devices').innerHTML = data.returnStr;
		}
    }
	else{
        let customer_devices = document.getElementById('customer_devices');
        customer_devices.innerHTML = '';
		customer_devices.appendChild(cTag('option',{ 'value': '' }));
	}
}

document.addEventListener('DOMContentLoaded', async()=>{
	let layoutFunctions = {lists, edit, add};
	layoutFunctions[segment2]();
    
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    addCustomeEventListener('labelSizeMissing',alert_label_missing);
});