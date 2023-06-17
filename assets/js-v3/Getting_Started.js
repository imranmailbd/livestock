import {
	cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, emailcheck, checkPhone, confirm_dialog, serialize,resizeBarCode,listenToEnterKey,
	alert_dialog, showTopMessage, setOptions, addPaginationRowFlex, checkAndSetSessionData, btnEnableDisable, popup_dialog600, leftsideHide, controllNumericField,
	unarchiveData, fetchData, addCustomeEventListener, callPlaceholder, encodeToCode128,onClickPagination, trimLabelText, validateRequiredField
} from './common.js';

if(segment2==='') segment2 = 'accounts_setup';

const Languages = {
	'English': Translate('English'),
	'Spanish': Translate('Spanish'),
	'French': Translate('French'),
	'Greek': Translate('Greek'),
	'German': Translate('German'),
	'Italian': Translate('Italian'),
	'Dutch': Translate('Dutch'),
	'Arabic': Translate('Arabic'),
	'Chinese': Translate('Chinese'),
	'Hindi': Translate('Hindi'),
	'Bengali': Translate('Bengali'),
	'Portuguese': Translate('Portuguese'),
	'Russian': Translate('Russian'),
	'Japanese': Translate('Japanese'),
	'Korean': Translate('Korean'),
	'Turkey': Translate('Turkey'),
	'Finnish': Translate('Finnish')
}

//======Common Functions=========
function header(label){
	const header = cTag('div', {class:'outerListsTable','style': "padding: 5px;"});
		const headerTitle = cTag('h2', {'style': "text-align: start;"});
		headerTitle.append(label+' ');
			const infoIcon = cTag('i', {'class': "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", 'title': "", 'data-original-title': Translate('This page captures the accounts settings')});
		headerTitle.appendChild(infoIcon);
	header.appendChild(headerTitle);
	return header;
}

function createNavigator(){
    const NavLink = {
        accounts_setup: Translate('Accounts Setup'),
        company_info: Translate('Company Information'),
        taxes: Translate('Manage Taxes'),
        payment_options: Translate('Payment Options'),
        import_customers: Translate('Import Customers'),
        import_products: Translate('Import Products'),
        small_print: Translate('Receipt Printer & Cash Drawer'),
        label_printer: Translate('Manage Label Printer')
    }
    
	const navigator = cTag('div', {'class': "columnXS12 columnMD2 columnSM3", 'style': "margin: 0;"});
		const callOutDiv = cTag('div', {'class': "innerContainer", 'style': "padding-top: 0px;"});
			const navigatorLink = cTag('a', {'href': "javascript:void(0);", id: "secondarySideMenu"});
				const faIcon = cTag('i', {'class': "fa fa-align-justify", 'style': "margin-bottom: 10px; font-size: 2em;"});
			navigatorLink.appendChild(faIcon);
		callOutDiv.appendChild(navigatorLink);
			const ulMenu = cTag('ul', {'class': "secondaryNavMenu settingslefthide"});
			for (let uriVal in NavLink) {
					const liTag = cTag('li');
					if(segment2 === 'export_'){segment2 = 'export';}
					if(segment2 === uriVal){
						liTag.setAttribute('class', "activeclass");
							const navigatorHeader = cTag('h4', {'style': "font-size: 18px;"});
							navigatorHeader.innerHTML = NavLink[uriVal];
						liTag.appendChild(navigatorHeader);
					}else{
						const titleVal = NavLink[uriVal];
						const listLink = cTag('a', {'href': '/'+segment1+"/"+uriVal, 'title': titleVal});
							const span = cTag('span');
							span.innerHTML = NavLink[uriVal];
						listLink.appendChild(span);
						liTag.appendChild(listLink);
					}                    
				ulMenu.appendChild(liTag);
			}                
		callOutDiv.appendChild(ulMenu);
	navigator.appendChild(callOutDiv);
	return navigator;
}

function controller_bar(id,cancelHandler){
    let inputField;
	const controller = cTag('div', {class: "flexStartRow"});
		inputField = cTag('input', {'type': "hidden", name: id, id: id, 'value': 0});
    controller.appendChild(inputField);
		inputField = cTag('input', {'type': "submit", id: "submit", class: "btn saveButton", 'style': "margin-right: 10px;", 'value': Translate('Save') });
    controller.appendChild(inputField);
		inputField = cTag('input', {'type': "button", name: "reset", id: "reset", 'value': Translate('Cancel'), class: "btn defaultButton", 'style': "display:none;float: left; margin-left: 10px;"});
        inputField.addEventListener('click', function(){
            const fn = window[cancelHandler];
            if(typeof fn === "function"){fn();}
        });
    controller.appendChild(inputField);
		inputField = cTag('input', {'type': "button", name: "archive", id: "archive", 'value': Translate('Archive'), class: "btn archiveButton", 'style': "display:none; float: right;"});
    controller.appendChild(inputField);
    return controller;
}

function hidden_items(parent,page){
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '34' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        const inputField = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
    	parent.appendChild(inputField);
    });
}

function getSessionData(){
	let list_filters;
	if (sessionStorage.getItem("list_filters") !== null) {
		list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
	}
	else{
		list_filters = {};
	}

	const sdata_type = 'All';
    if(document.querySelector('#sdata_type')) checkAndSetSessionData('sdata_type', sdata_type, list_filters); 

	let keyword_search = '';
	if(list_filters.hasOwnProperty("keyword_search")){
		keyword_search = list_filters.keyword_search;
		if(document.getElementById("keyword_search")){
			document.getElementById("keyword_search").value = keyword_search;
		}
	}        

	let limit = 'auto';
	if(list_filters.hasOwnProperty("limit")){
		limit = list_filters.limit;
		if(document.getElementById("limit")){
			document.getElementById("limit").value = limit;
		}
	}   
}

function setTaxesTableRows(tableData, tdAttributes){
	const tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//======Create TBody TR Column======//
	if(tableData.length){
		tableData.forEach(oneRow => {
			let i =0;
			const tr = document.createElement('tr');
			tr.setAttribute('class','cursor');
			tr.addEventListener('click',()=>AJgetData_Taxes(oneRow[0]));
			oneRow.forEach(tdvalue => {
				if(i>0){
					const tdCol = document.createElement('td');
					const oneTDObj = tdAttributes[i-1];
					for(const [key, value] of Object.entries(oneTDObj)) {
						let attName = key;
						if(attName !=='' && attName==='datatitle')
							attName = attName.replace('datatitle', 'data-title');
						tdCol.setAttribute(attName, value);
						if(i===2) tdCol.innerHTML = tdvalue.toFixed(3);
						else tdCol.innerHTML = tdvalue;
					}
					tr.appendChild(tdCol);
				}
				i++;
			});
			tbody.appendChild(tr);
		});
	}
	else{
		let colspan = tdAttributes.length;
		const tableRow = cTag('tr');
			const tdCol = cTag('td', {colspan:colspan, 'style': "color: #F00; font-size: 16px;"});
			tdCol.innerHTML = Translate('There is no data found')
		tableRow.appendChild(tdCol);
		tbody.appendChild(tableRow);
	}
}

function setAccountsTableRows(tableData, tdAttributes){
	const tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//======Create TBody TR Column======//
	if(tableData.length){
		tableData.forEach(oneRow => {
			const tr = document.createElement('tr');
				const engLangCol = cTag('td',{'data-title':tdAttributes[0].datatitle,'align':tdAttributes[0].align});	
					engLangCol.innerText = oneRow[1];			
				const localLangCol = cTag('td',{'data-title':tdAttributes[1].datatitle,'align':tdAttributes[1].align});	
					localLangCol.innerText = oneRow[2];
				const actionCol = cTag('td',{'data-title':tdAttributes[2].datatitle,'align':tdAttributes[2].align});
					const editLink = cTag('i',{style:'cursor:pointer',class:'fa fa-edit',title:Translate('View/Edit'),click:()=>AJgetLangPopup(oneRow[0])})				
					const removeLink = cTag('i',{style:'cursor:pointer;margin-left:10px',class:'fa fa-remove',title:Translate('Remove'),click:()=>AJremoveLang(oneRow[1])})				
				if(oneRow[3]===1) actionCol.append(editLink,removeLink);
				else actionCol.append(editLink);
			tr.append(engLangCol,localLangCol,actionCol);			
			tbody.appendChild(tr);
		});
	}
}

//____________________Accounts Setup_______________________
async function accounts_setup(){  
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Accounts Setup')));
        const divRow = cTag('div', {class: "flexStartRow"});
        divRow.appendChild(createNavigator());
            const divExport = cTag('div', {class: "columnXS12 columnMD10 columnSM9", 'style': "margin: 0;"});
                const bsCallOut = cTag('div', {id:'accountSetupContainer', class: "innerContainer", style: "margin-top: 0; background: #fff"});
				if(OS!=='unknown') bsCallOut.style.padding = '10px 0';
                    const accountSetupColumn = cTag('div',{ "class":"columnSM12" });
                        const accountSetupForm = cTag('form',{ "name":"frmAS","id":"frmAS","action":"#","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                        accountSetupForm.addEventListener('submit',checkFrm_accounts_setup);
							const accountSetupName = cTag('div',{ "class":"columnXS12" });
								const accountSetupHeader = cTag('h4', {'style': "font-size: 18px;"});
								accountSetupHeader.innerHTML = Translate('Accounts Setup');
							accountSetupName.appendChild(accountSetupHeader);
						accountSetupForm.appendChild(accountSetupName);
                        [
                            {label:Translate('Currency'),id:'currency'},
                            {label:Translate('Time Zone'),id:'timezone'},
                            {label:Translate('Date Format'),id:'dateformat'},
                            {label:Translate('Time Format'),id:'timeformat'},
                            {label:Translate('Language'),id:'language'}
                        ].forEach((item,indx)=>{
                                const setupContent = cTag('div',{ "class": "flex"});
									const setupContentTitle = cTag('div',{ "class":"columnXS12 columnMD3 columnSM5 columnLG2" });
                                        const label = cTag('label',{ "for":item.id,"data-placement":"bottom" });
                                        label.append(item.label);
                                            const required = cTag('span',{ "class":"required" });
                                            required.innerHTML = '*';
                                        label.appendChild(required);
									setupContentTitle.appendChild(label);
								setupContent.appendChild(setupContentTitle);
									const setupContentValue = cTag('div',{ "class":"columnXS12 columnMD9 columnSM7 columnLG10" });
                                        const selectItem = cTag('select',{ "required":"","class":"form-control","name":item.id,"id":item.id });
                                        if(indx===0) selectItem.classList.add('txt16normal');
									setupContentValue.appendChild(selectItem);
								setupContent.appendChild(setupContentValue);
							accountSetupForm.appendChild(setupContent);
                        })
							const buttonNamesColumn = cTag('div',{ "class":"columnXS12","align":"center" });
							buttonNamesColumn.appendChild(cTag('input',{ "class":"btn saveButton","name":"submit","id":"submit","type":"submit","value":Translate('Save') }));
						accountSetupForm.appendChild(buttonNamesColumn);
					accountSetupColumn.appendChild(accountSetupForm);
				bsCallOut.appendChild(accountSetupColumn);
            divExport.appendChild(bsCallOut);
        divRow.appendChild(divExport);
    Dashboard.appendChild(divRow);

	addCustomeEventListener('filter',filter_Getting_Started_accounts_setup);
	addCustomeEventListener('loadTable',loadTableRows_Getting_Started_accounts_setup);
	AJ_accounts_setup_MoreInfo();
}

async function AJ_accounts_setup_MoreInfo(){
    const url = '/'+segment1+'/AJ_accounts_setup_MoreInfo';

	fetchData(afterFetch,url,{});

	function afterFetch(data){
		const prod_cat_man = document.querySelector('#prod_cat_man').value;
		const accounts_id = document.querySelector('#accounts_id').value;

		const dateFormateOptions = {
			'm/d/y': 'MM/DD/YY',
			'd-m-y': 'DD-MM-YY'
		};
		const timeFormateOptions = {
			'12 hour': Translate('12 hour'),
			'24 hour': Translate('24 hour')
		};

		[
			{id: 'currency', options: data.currencyData},
			{id: 'timezone', options: data.timezonedata},
			{id: 'dateformat', options: dateFormateOptions},
			{id: 'timeformat', options: timeFormateOptions},
			{id: 'language', options: Languages}
		].forEach(item=>{
			let select = document.getElementById(item.id);
			if(item.id==='currency'){
				for (const key in item.options) {
					let currencyOption = cTag('option',{'value':key});
					currencyOption.innerHTML = `${key}, ${item.options[key]}`;
					select.appendChild(currencyOption);
				}
			}
			else if(['timezone','language'].includes(item.id)){
				setOptions(select,item.options,1,1);
			}
			else setOptions(select,item.options,1,0);
			select.value = data[item.id];
		});
		if(prod_cat_man !== accounts_id){
			const currency = document.querySelector('#currency');
			currency.style.display = 'none';
			const currency2 = cTag('input',{ 'class':'form-control', 'style': "font-size: 16px;", 'name':'currency2','id':'currency2','readonly':'readonly','value':`${data.currency}, ${data.currencyData[data.currency]}` })
			currency.parentNode.insertBefore(currency2,currency);
		}
		if(data.language !=='English'){
			const container = document.querySelector('#accountSetupContainer');
			hidden_items(container,1);
				const languageRow = cTag('div',{ "class":"flexSpaBetRow" });
					let languageHeaderColumn = cTag('div',{ "class":"columnXS12 columnSM6" });
						const languageHeader = cTag('h2',{ 'style': "padding-top: 5px; text-align: start;" });
						languageHeader.append(Translate('Customize your language')+' ');
						languageHeader.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('Customize your language') }));
					languageHeaderColumn.appendChild(languageHeader);
				languageRow.appendChild(languageHeaderColumn);
					let searchColumn = cTag('div',{ "class":"columnXS12 columnSM6" });
						let SearchInGroup = cTag('div',{ "class":"input-group" });
						SearchInGroup.appendChild(cTag('input',{ "type":"text","placeholder":Translate('Search from list'),"value":"","id":"keyword_search","name":"keyword_search","class":"form-control","maxlength":"50" }));
							const searchSpan = cTag('span',{ "class":"input-group-addon cursor","click":filter_Getting_Started_accounts_setup,"data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('Search from list') });
							searchSpan.appendChild(cTag('i',{ "class":"fa fa-search" }));
						SearchInGroup.appendChild(searchSpan);
					searchColumn.appendChild(SearchInGroup);
				languageRow.appendChild(searchColumn);
			container.appendChild(languageRow);
				const tableDiv = cTag('div');
					const tableColumn = cTag('div',{ "class":"columnSM12" });
						const noMoreTables = cTag('div',{ "id":"language-table" });
							const accountSetupTable = cTag('table',{ "class":" columnSM12 table-bordered table-striped table-condensed cf listing " });
								const accountSetupHead = cTag('thead',{ "class":"cf" });
									const accountSetupHeadRow = cTag('tr');
										const thCol0 = cTag('th',{ "align":"left","width":"45%" });
										thCol0.innerHTML = Translate('English');

										const thCol1 = cTag('th',{ "align":"left","width":"45%" });
										thCol1.innerHTML = Languages[data.language];

										const thCol2 = cTag('th',{ "align":"left" });
										thCol2.innerHTML = Translate('Action');
									accountSetupHeadRow.append(thCol0,thCol1,thCol2);
								accountSetupHead.appendChild(accountSetupHeadRow);
							accountSetupTable.appendChild(accountSetupHead);
							accountSetupTable.appendChild(cTag('tbody',{ "id":"tableRows" }));
						noMoreTables.appendChild(accountSetupTable);
					tableColumn.appendChild(noMoreTables);
				tableDiv.appendChild(tableColumn);
				addPaginationRowFlex(tableDiv);
			container.appendChild(tableDiv);
			getSessionData();
			filter_Getting_Started_accounts_setup(true);
		}
	}
}

async function checkFrm_accounts_setup(event){
	event.preventDefault();

	const submitBtn = document.querySelector("#submit");
	btnEnableDisable(submitBtn,Translate('Saving'),true);

	const jsonData = serialize("#frmAS");
	const url = '/'+segment1+'/saveAS';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg !=='error' && data.id>0){
			btnEnableDisable(submitBtn,Translate('Update'),false);
			if(data.savemsg==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
			else if(data.savemsg==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
			location.reload();	
		}
		else{
			showTopMessage('alert_msg',Translate('Error occured while changing accounts setup information! Please try again.'));
			btnEnableDisable(submitBtn,Translate('Save'),false);
		}
	}
	return false;
}

async function filter_Getting_Started_accounts_setup(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['language'] = document.querySelector('#language').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

	const url = '/'+segment1+'/AJgetLangPage/filter';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);
		let tdAttributes = [
			{'datatitle':Translate('English'), 'align':'left'}, 
			{'datatitle':document.querySelector('#language-table').querySelectorAll('table thead tr th')[1].innerText, 'align':'left'}, 
			{'datatitle':Translate('Action'), 'align':'center'}
		];
		setAccountsTableRows(data.tableRows, tdAttributes);
		document.querySelector("#totalTableRows").value = data.totalRows;			
		onClickPagination();
	}
}

async function loadTableRows_Getting_Started_accounts_setup(){
	const jsonData = {};
	jsonData['language'] = document.querySelector('#language').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;

	const url = '/'+segment1+'/AJgetLangPage';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);
		let tdAttributes = [
			{'datatitle':Translate('English'), 'align':'left'}, 
			{'datatitle':document.querySelector('#language-table').querySelectorAll('table thead tr th')[1].innerText, 'align':'left'}, 
			{'datatitle':Translate('Action'), 'align':'center'}
		];
		setAccountsTableRows(data.tableRows, tdAttributes);
		onClickPagination();
	}
}

async function AJgetLangPopup(languages_id){
	if(languages_id>0){
		const selectedLang = document.querySelector('#language').value;
		const jsonData = {languages_id:languages_id, selectedLang:selectedLang};
		const url = '/'+segment1+'/AJgetLangPopup';

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			const formHtml = cTag('div');
			formHtml.appendChild(cTag('div',{ id:"errorLanguage",class:"errormsg" }));
				const languageForm = cTag('form',{ "action":"#","name":"frmLanguage","id":"frmLanguage","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
					const languageColumn = cTag('div',{ "class":"columnXS12" });
						let englishRow = cTag('div',{ "class":"flexSpaBetRow" });
							let englishTitle = cTag('div',{ "class":"columnSM4","align":"left" });
								let englishLabel = cTag('label',{ "for":"imei_or_serial_no","data-placement":"bottom" });
								englishLabel.innerHTML = Translate('English');
							englishTitle.appendChild(englishLabel);
						englishRow.appendChild(englishTitle);
							let englishValue = cTag('div',{ "class":"columnSM8","align":"left" });
							englishValue.innerHTML = data.english;
						englishRow.appendChild(englishValue);
					languageColumn.appendChild(englishRow);
						const languageRow = cTag('div',{ "class":"flexSpaBetRow" });
							let languageDiv = cTag('div',{ "class":"columnSM4","align":"left" });
								let languageLabel = cTag('label',{ "for":"popuplanguage","data-placement":"bottom" });
								languageLabel.innerHTML = Languages[data.Language];
							languageDiv.appendChild(languageLabel);
						languageRow.appendChild(languageDiv);
							let languageField = cTag('div',{ "class":"columnSM8","align":"left" });
							languageField.appendChild(cTag('textarea',{ "rows":"4","class":"form-control","name":"popuplanguage","id":"popuplanguage" }));
						languageRow.appendChild(languageField);
					languageColumn.appendChild(languageRow);
				languageForm.appendChild(languageColumn);
				languageForm.appendChild(cTag('input',{ "type":"hidden","name":"languages_id","id":"languages_id","value":data.languages_id }));
				languageForm.appendChild(cTag('input',{ "type":"hidden","name":"php_js","id":"php_js","value":data.php_js }));
				languageForm.appendChild(cTag('input',{ "type":"hidden","name":"english","id":"english","value":data.english }));
			formHtml.appendChild(languageForm);
			
			popup_dialog600(Translate('You can customize the text for your language.'), formHtml, Translate('Save'), AJsaveLang);
			
			setTimeout(function() {
				document.querySelector("#popuplanguage").value = data.popuplanguage;
				document.querySelector("#popuplanguage").focus();
			}, 500);
		}
	}
}

async function AJsaveLang(hidePopup){
	const errorStatus = document.getElementById('errorLanguage');
	errorStatus.innerHTML = '';
	if(document.querySelector("#popuplanguage").value===''){
			const pTag = cTag('p');
			pTag.innerHTML = Translate('Missing Language');
		errorStatus.appendChild(pTag);
		document.querySelector("#popuplanguage").focus();
		return false;
	}
	
	const submitBtn = document.querySelector("#submit");
	btnEnableDisable(submitBtn,Translate('Saving'),true);

	
	
	const jsonData = serialize("#frmLanguage");
	const url = '/'+segment1+'/AJsaveLang';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg !=='error'){
			hidePopup();
			filter_Getting_Started_accounts_setup();
		}
		else{						
			errorStatus.innerHTML = Translate('Error occured while changing accounts setup information! Please try again.');
		}
		btnEnableDisable(submitBtn,Translate('Save'),false);		
	}
	return false;
}

function AJremoveLang(english){
	const popUpHtml = cTag('div');
	popUpHtml.appendChild(cTag('input',{ "type":"hidden","id":"english","value":english }));
	popUpHtml.append(`${Translate('Are you sure want to remove this information')} (${english})?`);
	confirm_dialog(Translate('Remove')+' '+english, popUpHtml, confirmAJremoveLang);
}

async function confirmAJremoveLang(hidePopup){
	const english = document.querySelector("#english").value;	
	const jsonData = {english:english};
	const url = '/'+segment1+'/AJremoveLang';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg==='Done'){
			filter_Getting_Started_accounts_setup();
			hidePopup();
			showTopMessage('success_msg',Translate('Data removed successfully.'));
		}
		else{
			showTopMessage('error_msg',Translate('Could not remove information'));
		}
	}
}

//____________________Company Info_______________________
async function company_info(){
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Company Information') ));
        const companyInfoRow = cTag('div', {class: "flexStartRow"});
        companyInfoRow.appendChild(createNavigator());
            const companyInfoColumn = cTag('div', {class: "columnXS12 columnMD10 columnSM9", 'style': "margin: 0;"});
                const container = cTag('div', {class: "innerContainer", style: "background: #fff"});
				if(OS!=='unknown') container.style.padding = '10px 0';
					let errorMessage = cTag('div',{id: "errormsgId", class: "flexCenterRow errormsg"});
				container.appendChild(errorMessage);
					const companyInfoForm = cTag('form',{ "name":"frmcompany_info","id":"frmcompany_info","action":"#","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
					companyInfoForm.addEventListener('submit',checkFrm_company_info)
					const formFields = [
						{ label:Translate('Sub-Domain'),id:'company_subdomain',maxLength:30 },
						{ label:Translate('Company Name'),id:'company_name',maxLength:40 },
						{ label:Translate('Company Phone No.'),id:'company_phone_no',maxLength:20 },
						{ label:Translate('Customer Service Email'),id:'customer_service_email',maxLength:50 },
						{ label:Translate('Street Address'),id:'company_street_address',maxLength:100 },
						{ label:Translate('City'),id:'company_city',maxLength:100 },
						{ label:Translate('State / Province'),id:'company_state_name',maxLength:100 },
						{ label:Translate('Zip/Postal Code'),id:'company_zip',maxLength:20 },
						{ label:Translate('Country'),id:'company_country_name' },
					];
					formFields.forEach((item,indx)=>{
							const companyInfoItem = cTag('div',{ "class":"flex"});
								const companyInfoName = cTag('div',{ "class":"columnXS12 columnMD4 columnSM5 columnLG3" });
									const label = cTag('label',{ "for":item.id });
									label.append(item.label);
									if(indx>0){
											const requiredSpan = cTag('span',{ "class":"required" });
											requiredSpan.innerHTML = '*';
										label.appendChild(requiredSpan);
									}
								companyInfoName.appendChild(label);
							companyInfoItem.appendChild(companyInfoName);
								const companyInfoValue = cTag('div',{ "class":"columnXS12 columnMD8 columnSM7 columnLG9" });
								if(formFields.length-1===indx){
										const select = cTag('select',{ "required":"","class":"form-control","name":item.id,"id":item.id });
									companyInfoValue.appendChild(select);
								}
								else{
									const inputField = cTag('input',{ "type":"text","name":item.id,"id":item.id,"class":"form-control" });
									if(item.id === 'customer_service_email'){
										companyInfoValue.appendChild(cTag('span',{'id':'mailError','class':'errormsg'}))
										inputField.setAttribute('type', 'email');
										inputField.addEventListener('blur',function(){
											if(this.value!='' && !emailcheck(this.value)) document.getElementById('mailError').innerHTML = 'Invalid Email'
										});
										inputField.addEventListener('focus',function(){document.getElementById('mailError').innerHTML = ''})
									};
									if(item.id === 'company_phone_no'){ 
										inputField.setAttribute('type', 'tel');
										inputField.addEventListener('keyup',function() {
											if(!checkPhone("company_phone_no", 0)) this.value = this.value.slice(0,-1);
										});
									};
									if(item.maxLength) inputField.setAttribute('maxlength',item.maxLength);
									if(indx===0) inputField.readOnly = true;
									if(indx>0) inputField.required = true;
									companyInfoValue.appendChild(inputField);
								}		
							companyInfoItem.appendChild(companyInfoValue);
						companyInfoForm.appendChild(companyInfoItem);
					})
						const buttonColumn = cTag('div',{ "class":"columnXS12","align":"center" });
						buttonColumn.appendChild(cTag('input',{ "class":"btn saveButton","name":"submit","id":"submit","type":"submit","value":Translate('Save') }));
					companyInfoForm.appendChild(buttonColumn);
				container.appendChild(companyInfoForm);
			companyInfoColumn.appendChild(container);
		companyInfoRow.appendChild(companyInfoColumn);
    Dashboard.appendChild(companyInfoRow);
	AJ_company_info_MoreInfo();
}

async function AJ_company_info_MoreInfo(){
    const url = '/'+segment1+'/AJ_company_info_MoreInfo';

	fetchData(afterFetch,url,{});

	function afterFetch(data){
		document.querySelectorAll('.form-control').forEach(item=>{
			if(item.nodeName === 'SELECT'){
					const option = cTag('option',{ value:'' });
					option.innerHTML = Translate('Select Country');
				item.appendChild(option);
				setOptions(item,data.countryData,0,0);
				item.value = data[item.name];
			}
			else{
				item.value = data[item.name];
			}
		})
	}
}

async function checkFrm_company_info(event){
	event.preventDefault();

	let error_customer = document.getElementById("errormsgId");
	error_customer.innerHTML = '';

	if(document.getElementById("customer_service_email").value !=='' && emailcheck(document.getElementById("customer_service_email").value)===false){
		error_customer.innerHTML = 'Invalid email address.';
		document.getElementById("customer_service_email").focus();
		return false;
	}
	if(document.getElementById("company_phone_no").value !=='' && checkPhone("company_phone_no", 0)===false){
		error_customer.innerHTML = 'Invalid phone number.';
		document.getElementById("company_phone_no").focus();
		return false;
	}

	const submitBtn = document.querySelector("#submit");
	btnEnableDisable(submitBtn,Translate('Saving'),true);	

	const jsonData = serialize("#frmcompany_info");
	const url = '/'+segment1+'/save_company_info';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg !=='error' && data.id>0){
			showTopMessage('success_msg', Translate('Updated successfully.'));
		}
		else{
			if(data.returnStr=='Name_Already_Exist'){
				showTopMessage('alert_msg', Translate('This sub-domain')+' '+ document.getElementById("company_subdomain").value+' '+Translate('already exist!'));
			}  
			else{
				showTopMessage('alert_msg', Translate('No changes / Error occurred while updating data! Please try again.'));
			}			
		}
		btnEnableDisable(submitBtn,Translate('Save'),false);	
	}
	return false;
}

//____________________Taxes_______________________
async function taxes(){
    let requireField;
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Manage Taxes')));
        const manageTaxContainer = cTag('div', {class: "flexStartRow"});
        manageTaxContainer.appendChild(createNavigator());
            const manageTaxColumn = cTag('div', {class: "columnXS12 columnMD10 columnSM9", 'style': "margin: 0;"});
                let container = cTag('div', {class: "innerContainer", style: "padding-top: 0px; margin-top: 0; background: #fff"});
                if(OS!=='unknown') container.style.padding = '10px 0';
				hidden_items(container,1);
					const manageTaxRow = cTag('div',{ "class":"flexSpaBetRow" });
						const taxColumn = cTag('div',{ "class":"columnXS12 columnMD7", 'style': "margin: 0;" });
							const titleRow = cTag('div',{ "class":"flexSpaBetRow outerListsTable" });
								const titleName = cTag('div',{ "class":"columnXS12" });
									const headerTitle = cTag('h2',{ 'style': "padding-top: 5px;" });
									headerTitle.append(Translate('Taxes List')+' ');
									headerTitle.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('Taxes List') }));
								titleName.appendChild(headerTitle);
							titleRow.appendChild(titleName);
								const filterDiv = cTag('div', {class: "columnXS6"});
									const filterType = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type"});
									filterType.addEventListener('change', filter_Getting_Started_taxes);
									setOptions(filterType, {'All':Translate('All')+' '+Translate('Taxes'), 'Archived':Translate('Archived')+' '+Translate('Taxes')}, 1, 0); 
								filterDiv.appendChild(filterType);       
							titleRow.appendChild(filterDiv);
								const searchDiv = cTag('div',{ "class":"columnXS6" });
									const SearchInGroup = cTag('div',{ "class":"input-group" });
									SearchInGroup.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Getting_Started_taxes),"type":"text","placeholder":Translate('Search Taxes'),"value":"","id":"keyword_search","name":"keyword_search","class":"form-control","maxlength":"50" }));
										const searchSpan = cTag('span',{ "class":"input-group-addon cursor","click":filter_Getting_Started_taxes,"data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('Search Taxes') });
										searchSpan.appendChild(cTag('i',{ "class":"fa fa-search" }));
									SearchInGroup.appendChild(searchSpan);
								searchDiv.appendChild(SearchInGroup);
							titleRow.appendChild(searchDiv);
						taxColumn.appendChild(titleRow);

							const tableContainer = cTag('div',{ "class":"columnXS12" });
								const listTable = cTag('table',{ "class":" columnMD12 table-bordered table-striped table-condensed cf listing " });
									const listHead = cTag('thead',{ "class":"cf" });
										const listHeadRow = cTag('tr');
											const thCol0 = cTag('th',{ 'style': "text-align: center;" });
											thCol0.innerHTML = Translate('Name');

											const thCol1 = cTag('th',{ 'style': "text-align: center;", "width":"20%" });
											thCol1.innerHTML = Translate('Percentage (%)');

											const thCol2 = cTag('th',{ 'style': "text-align: center;", "width":"15%" });
											thCol2.innerHTML = Translate('Default Tax');

											const thCol3 = cTag('th',{ 'style': "text-align: center;", "width":"15%" });
											thCol3.innerHTML = Translate('Tax Inclusive');
										listHeadRow.append(thCol0, thCol1, thCol2, thCol3);
									listHead.appendChild(listHeadRow);
								listTable.appendChild(listHead);
								listTable.appendChild(cTag('tbody',{ "id":"tableRows" }));
							tableContainer.appendChild(listTable);
						taxColumn.appendChild(tableContainer);
						addPaginationRowFlex(taxColumn);
					manageTaxRow.appendChild(taxColumn);
						const newTaxColumn = cTag('div',{ "class":"columnXS12 columnMD5", 'style': "margin: 0;" });
							const newTaxName = cTag('div',{ "class":"columnXS12"});
								const newTaxTitle = cTag('h4',{ "class":"borderbottom", 'style': "font-size: 18px;", "id":"formtitle" });
								newTaxTitle.innerHTML = Translate('Add New Taxes');
							newTaxName.appendChild(newTaxTitle);
						newTaxColumn.appendChild(newTaxName);
							const newTaxForm = cTag('form',{ "action":"#","name":"frmtaxes","id":"frmtaxes","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
							newTaxForm.addEventListener('submit', AJsaveTaxes);
								const taxNameRow = cTag('div');
									const taxNameLabel = cTag('label',{ "for":"taxes_name" });
									taxNameLabel.append(Translate('Name'));
										requireField = cTag('span',{ "class":"required" });
										requireField.innerHTML = '*';
									taxNameLabel.appendChild(requireField);
								taxNameRow.appendChild(taxNameLabel);
								taxNameRow.appendChild(cTag('input',{ "type":"text","required":"","class":"form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", "name":"taxes_name","id":"taxes_name","value":"","size":"20","maxlength":"20" }));
							newTaxForm.appendChild(taxNameRow);

								const taxPercentageRow = cTag('div');
									const taxPercentageLabel = cTag('label',{ "for":"taxes_percentage" });
									taxPercentageLabel.append(Translate('Percentage (%)'));
										requireField = cTag('span',{ "class":"required" });
										requireField.innerHTML = '*';
									taxPercentageLabel.appendChild(requireField);
								taxPercentageRow.appendChild(taxPercentageLabel);
									const taxField = cTag('input',{ 'required':'',"type":"text",'data-min':'0','data-max':'99.999','data-format':'d.ddd',"class":"form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", "name":"taxes_percentage","id":"taxes_percentage" });
									controllNumericField(taxField, '#errmsg_taxes_percentage')
								taxPercentageRow.appendChild(taxField);
								taxPercentageRow.appendChild(cTag('span',{ "class":"error_msg","id":"errmsg_taxes_percentage" }));
							newTaxForm.appendChild(taxPercentageRow);

								const defaultTaxRow = cTag('div');
									const defaultTaxLabel = cTag('label',{ "for":"default_tax" });
									defaultTaxLabel.appendChild(cTag('input',{ "type":"checkbox","name":"default_tax","value":"1","id":"default_tax", 'style': "margin-top: 10px; margin-bottom: 10px;" }));
									defaultTaxLabel.append(' '+Translate('Default Tax'));
								defaultTaxRow.appendChild(defaultTaxLabel);
							newTaxForm.appendChild(defaultTaxRow);

								const inclusiveTaxRow  = cTag('div');
									const inclusiveTaxLabel = cTag('label',{ "for":"tax_inclusive" });
									inclusiveTaxLabel.appendChild(cTag('input',{ "type":"checkbox","name":"tax_inclusive","value":"1","id":"tax_inclusive", 'style': "margin-top: 10px; margin-bottom: 10px;" }));
									inclusiveTaxLabel.append(' '+Translate('Tax Inclusive'));
								inclusiveTaxRow.appendChild(inclusiveTaxLabel);
							newTaxForm.appendChild(inclusiveTaxRow);
							newTaxForm.appendChild(cTag('div',{ "class":"flexStartRow","id":"formcreatedon" }));
								const buttonsName = cTag('div');
								buttonsName.appendChild(cTag('input',{ "type":"hidden","name":"taxes_id","id":"taxes_id","value":"0" }));
								buttonsName.appendChild(cTag('input',{ "type":"submit","id":"submit","class":"btn saveButton", 'style': "margin-right: 10px;", "value":Translate('Save') }));
									let archive = cTag('input',{ "type":"button","name":"archive","id":"archive","value":Translate('Archive'),"class":"btn archiveButton", 'style':"display:none; margin-right: 10px;" });
									archive.addEventListener('click',()=>{
										let taxes_id = document.getElementById('taxes_id').value;
										let taxes_name = document.getElementById('taxes_name').value;
										let taxes_percentage = document.getElementById('taxes_percentage').value;
										taxes_name = taxes_name.replace(/'/g, "\\'")+' ('+taxes_percentage+'%)';
										AJremoveTaxes(taxes_id, taxes_name);
									})
								buttonsName.appendChild(archive);
									let unarchive = cTag('input',{ "type":"button","name":"unarchive","id":"unarchive","value":Translate('Unarchive'),"class":"btn bgcoolblue", 'style': "display:none; margin-right: 10px;" });
									unarchive.addEventListener('click', ()=>unarchiveTaxes(document.getElementById('taxes_id').value))
								buttonsName.appendChild(unarchive);
								buttonsName.appendChild(cTag('input',{ "type":"button","name":"reset","id":"reset","click":resetForm_taxes,"value":Translate('Cancel'),"class":"btn defaultButton", 'style': "display:none;" }));
							newTaxForm.appendChild(buttonsName);
						newTaxColumn.appendChild(newTaxForm);
					manageTaxRow.appendChild(newTaxColumn);
				container.appendChild(manageTaxRow);
			manageTaxColumn.appendChild(container);
		manageTaxContainer.appendChild(manageTaxColumn);
    Dashboard.appendChild(manageTaxContainer);

	addCustomeEventListener('filter',filter_Getting_Started_taxes);
	addCustomeEventListener('loadTable',loadTableRows_Getting_Started_taxes);
	getSessionData();
	filter_Getting_Started_taxes(true);
}

async function filter_Getting_Started_taxes(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

	const url = '/'+segment1+'/AJgetPageTaxes/filter';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);
		let tdAttributes = [
			{'datatitle':Translate('Name'), 'align':'left'}, 
			{'datatitle':Translate('Percentage (%)'), 'align':'center'}, 
			{'datatitle':Translate('Default Tax'), 'align':'center'},
			{'datatitle':Translate('Tax Inclusive'), 'align':'center'}
		];
		setTaxesTableRows(data.tableRows, tdAttributes);
		document.querySelector("#totalTableRows").value = data.totalRows;			
		onClickPagination();
	}
}

async function loadTableRows_Getting_Started_taxes(){
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;

	const url = '/'+segment1+'/AJgetPageTaxes';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);
		let tdAttributes = [
			{'datatitle':Translate('Name'), 'align':'left'}, 
			{'datatitle':Translate('Percentage (%)'), 'align':'center'}, 
			{'datatitle':Translate('Default Tax'), 'align':'center'},
			{'datatitle':Translate('Tax Inclusive'), 'align':'center'}
		];
		setTaxesTableRows(data.tableRows, tdAttributes);
		onClickPagination();
	}
}

async function AJsaveTaxes(event){
	event.preventDefault();

	const errorStatus = document.getElementById('errmsg_taxes_percentage');
	errorStatus.innerHTML = '';

	let taxes_percentage = document.getElementById('taxes_percentage');
    if (!taxes_percentage.valid()) return;

	/* const taxes_percentage = document.querySelector("#taxes_percentage");
	const namevalue = parseFloat(taxes_percentage.value);
	if(namevalue<0 || isNaN(namevalue)){
		errorStatus.innerHTML = 'Invalid taxes percentage.';	
		taxes_percentage.value = '';
		taxes_percentage.focus();
		return false;
	}
	else if(namevalue>=100){
		errorStatus.innerHTML = 'Maximum taxes percentage is 99.999.';	
		taxes_percentage.value = '';
		taxes_percentage.focus();
		return false;
	} */

	const submitBtn = document.querySelector("#submit");
	btnEnableDisable(submitBtn,Translate('Saving'),true);	

	const jsonData = serialize("#frmtaxes");
	const url = '/'+segment1+'/AJsaveTaxes';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(['Add', 'Update'].includes(data.savemsg)){
			resetForm_taxes();
			if(data.returnStr==='Add'){
				showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
				showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			document.getElementById('sdata_type').value = 'All'
			filter_Getting_Started_taxes();	
		}
		else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occured while adding new taxes name! Please try again.'), Translate('Ok'));
		}
		else if(data.returnStr=='errorOnEditing'){
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occured while updating taxes data! Please try again.'), Translate('Ok'));
		}
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This taxes name is already exist! Please try again with different taxes name.'), Translate('Ok'));
		}
		else if(data.returnStr=='Name_ExistInArchive'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists <b>IN ARCHIVED</b>! Please try again with a different name.'), Translate('Ok'));
		}
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}
		btnEnableDisable(submitBtn,Translate('Add'),false);	
	}
	return false;
}

async function AJgetData_Taxes(taxes_id){
	if(taxes_id>0){	
		const jsonData = {taxes_id:taxes_id};
		const url = '/'+segment1+'/AJgetData_Taxes';

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			document.frmtaxes.taxes_id.value = data.taxes_id;
			document.frmtaxes.taxes_name.value = data.taxes_name;
			document.frmtaxes.taxes_percentage.value = data.taxes_percentage.toFixed(3);
			if(data.default_tax>0){
				document.querySelector("#default_tax").disabled = false;
				document.frmtaxes.default_tax.checked = true;
			}
			else{
				const defaulttaxcount = document.querySelectorAll(".default_tax").length;
				if(defaulttaxcount===2){
					document.querySelector("#default_tax").disabled = "disabled";
				}
				else{				
					document.querySelector("#default_tax").disabled = false;
				}
				document.frmtaxes.default_tax.checked = false;
			}
			if(data.tax_inclusive>0){
				document.frmtaxes.tax_inclusive.checked = true;
			}
			else{
				document.frmtaxes.tax_inclusive.checked = false;
			}
			if(document.querySelector("#reset").style.display === 'none'){
				document.querySelector("#reset").style.display = '';
			}
			if(data.taxes_publish===1){
				document.querySelector("#formtitle").innerHTML = Translate('Update Taxes');
				document.querySelector("#submit").style.display = '';
				document.querySelector("#archive").style.display = '';
				document.querySelector("#unarchive").style.display = 'none';

				// let taxes_name = data.taxes_name;
				// taxes_name = taxes_name.replace(/'/g, "\\'")+' ('+data.taxes_percentage+'%)';
				// document.querySelector("#archive").addEventListener('click', ()=>AJremoveTaxes(data.taxes_id, data.taxes_name));
				 
				setTimeout(function() {
					document.frmtaxes.taxes_name.focus();
				});
			}
			else{
				document.querySelector("#formtitle").innerHTML = Translate('Unarchive Taxes');
				document.querySelector("#submit").style.display = 'none';
				document.querySelector("#archive").style.display = 'none';
				document.querySelector("#unarchive").style.display = '';
				// unarchive.addEventListener('click', ()=>unarchiveTaxes(data.taxes_id));
			}
		}
	}
}

function AJremoveTaxes(taxes_id, taxes_name){	
	if(taxes_id>0){
		const message = cTag('div');
		message.append(Translate('Are you sure you want to archive this information?'));
		message.appendChild(cTag('input',{ type:"hidden", name:"artaxes_id", id:"artaxes_id", value:`${taxes_id}` }));
		message.appendChild(cTag('input',{ type:"hidden", name:"artaxes_name", id:"artaxes_name", value:`${taxes_name}` }));
		confirm_dialog(Translate('Archive Taxes'), message, confirmAJremoveTaxes);
	}
}

async function confirmAJremoveTaxes(hidePopup){
	const taxes_id = document.querySelector("#artaxes_id").value;
	const taxes_name = document.querySelector("#artaxes_name").value;
	const jsonData = {taxes_id:taxes_id, taxes_name:taxes_name};
	const url = '/'+segment1+'/AJremoveTaxes';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.returnStr==='archive-success'){
			filter_Getting_Started_taxes();
			resetForm_taxes();
		}
		else{
			showTopMessage('error_msg',Translate('Error occured while archiving information! Please try again.'));
		}									
		hidePopup();
	}
}

function resetForm_taxes(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Taxes');	
	document.frmtaxes.taxes_id.value = 0;
	document.frmtaxes.taxes_name.value = '';
	document.frmtaxes.taxes_percentage.value = '';
	document.frmtaxes.default_tax.checked = false;
	document.frmtaxes.tax_inclusive.checked = false;
	const defaulttaxcount = document.querySelectorAll(".default_tax").length;
	if(defaulttaxcount===2){
		document.querySelector("#default_tax").disabled = "disabled";
	}
	else{				
		document.querySelector("#default_tax").disabled = false;
	}
	document.querySelector("#submit").style.display = '';
	document.querySelector("#reset").style.display = 'none';
	document.querySelector("#archive").style.display = 'none';
	document.querySelector("#unarchive").style.display = 'none';
}
function unarchiveTaxes(tableidvalue){
    confirm_dialog(Translate('Tax Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{        
        unarchiveData(null,{tablename:'taxes', tableidvalue, publishname:segment2+'_publish'},afterUnarchive);
		function afterUnarchive(){
			hidePopup();
			document.getElementById('sdata_type').value = 'All';
			filter_Getting_Started_taxes();
			resetForm_taxes();
		}
    });
}

//____________________Payment_______________________
async function payment_options(){
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Payment Options')));
        const paymentOptionContainer = cTag('div', {class: "flexStartRow"});
        paymentOptionContainer.appendChild(createNavigator());
            const paymentOptionColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                let callOutDiv = cTag('div', {class: "innerContainer"});
				if(OS!=='unknown') callOutDiv.style.padding = '10px 0';
					const paymentOptionDiv = cTag('div',{ "class":"columnSM12" });
						const paymentOptionForm = cTag('form',{ "name":"frmPO","id":"frmPO","action":"#","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
						paymentOptionForm.addEventListener('submit',check_frmPO);
							const paymentOptionRow = cTag('div',{ "class":"flexStartRow" });
								const paymentOptionTitle = cTag('div',{ "class":"columnSM3 columnMD2" });
									const paymentOptionLabel = cTag('label',{ "for":"payment_options" });
									paymentOptionLabel.innerHTML = Translate('Payment Options')+':';
								paymentOptionTitle.appendChild(paymentOptionLabel);
							paymentOptionRow.appendChild(paymentOptionTitle);
								const paymentOptionList = cTag('div',{ "class":"columnSM9 columnMD10" });
									const paymentOptionListRow = cTag('div',{ "class": "flex"});
										const allPaymentOption = cTag('div',{ "class":"columnXS12 plusIconPosition roundborder" });
										allPaymentOption.appendChild(cTag('ul',{ "id":"poListRow","class":"multipleRowList" }));
										allPaymentOption.appendChild(cTag('span',{ "id":"errorPOListRow","class":"errormsg" }));
											const addNewPayment = cTag('div',{ "class":"addNewPlusIcon" });
												const addNewPaymentLink = cTag('a',{ "href":"javascript:void(0);","title":Translate('Add More Payment Options'),"click":addMorePO });
												addNewPaymentLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Add More Payment Options'),"title":Translate('Add More Payment Options'),"src":"/assets/images/plus20x25.png" }));
											addNewPayment.appendChild(addNewPaymentLink);
										allPaymentOption.appendChild(addNewPayment);
									paymentOptionListRow.appendChild(allPaymentOption);
								paymentOptionList.appendChild(paymentOptionListRow);
							paymentOptionRow.appendChild(paymentOptionList);
						paymentOptionForm.appendChild(paymentOptionRow);
							const buttonRow = cTag('div',{ "class":"flexSpaBetRow" });
								let saveButton = cTag('div',{ "class":"columnXS12","align":"right" });
								saveButton.appendChild(cTag('input',{ "type":"hidden","name":"sqrup_currency_code","id":"sqrup_currency_code" }));
								saveButton.appendChild(cTag('input',{ "class":"btn saveButton","name":"submit","id":"submit","type":"submit","value":Translate('Save') }));
							buttonRow.appendChild(saveButton);
						paymentOptionForm.appendChild(buttonRow);
					paymentOptionDiv.appendChild(paymentOptionForm);
				callOutDiv.appendChild(paymentOptionDiv);
			paymentOptionColumn.appendChild(callOutDiv);
		paymentOptionContainer.appendChild(paymentOptionColumn);
    Dashboard.appendChild(paymentOptionContainer);
	AJ_payment_options_MoreInfo();
}

async function AJ_payment_options_MoreInfo(){
    const url = '/'+segment1+'/AJ_payment_options_MoreInfo';

	fetchData(afterFetch,url,{});

	function afterFetch(data){
		const ul = document.querySelector('#poListRow');
		let serialNumber = 1;			

		if(!data.poData.includes('Cash')){
			ul.appendChild(poLister('Cash',serialNumber,true));
			serialNumber++;
		}
		data.poData.forEach((item)=>{
				if(['CASH', 'SQUAREUP'].includes(item.toUpperCase())) ul.appendChild(poLister(item,serialNumber,true));
				else ul.appendChild(poLister(item,serialNumber));
				serialNumber++;
		})
		if(!data.poData.includes('Squareup') && data.sqrup_currency_code !==''){
			ul.appendChild(poLister('Squareup',serialNumber,true));
			serialNumber++;
		}
		ul.appendChild(poLister('',serialNumber));
		
		document.querySelector('#sqrup_currency_code').value = data.sqrup_currency_code;

		rearrangePOList();
	}
}

function poLister(item,serialNumber,readonly){
	const li = cTag('li');
		const paymentFieldRow = cTag('div',{ "class":"flexStartRow" });
			const moveUpColumn = cTag('div',{ "class":"flex columnXS2 columnMD1", 'style': "justify-content: space-between;" });
			moveUpColumn.append(serialNumber);
				const arrowIcon = cTag('a',{ "class":"poOrderUp", "href":"javascript:void(0);","title":"Move to UP" });
				arrowIcon.appendChild(cTag('i',{ "class":"fa fa-arrow-up" }));
			moveUpColumn.appendChild(arrowIcon);
		paymentFieldRow.appendChild(moveUpColumn);
			const paymentFieldValue = cTag('div',{ "class":"columnXS10 columnMD11" });
				const input = cTag('input',{ "type":"text","maxlength":"25","placeholder":`${Translate('Enter new payment option')} ${serialNumber}`, alt:`${Translate('Enter new payment option')} ${serialNumber}`,"title":item,"name":"payment_options[]","value":item,"class":"form-control placeholder payment_options" });
				if(readonly) input.readOnly = true;
			paymentFieldValue.appendChild(input);
		paymentFieldRow.appendChild(paymentFieldValue);
	li.appendChild(paymentFieldRow);
	return li;
}

function poOrderUp(position){
	let topPosition = position-1;
	let  prevLI = '', nextLI = '';

	const totalLI = parseInt(document.querySelector("ul#poListRow").childElementCount);
	if(totalLI>1){
		if(position===0){
			topPosition = totalLI-2;
		}

		if(document.querySelector("ul#poListRow").childElementCount>1){
			document.querySelector("ul#poListRow").querySelectorAll('li').forEach(function(list,indx){
				if(indx===topPosition){nextLI = list.querySelector('.flexStartRow').querySelector('.columnMD11').innerHTML;}
				else if(indx===position){prevLI = list.querySelector('.flexStartRow').querySelector('.columnMD11').innerHTML;}
			});
			document.querySelector("ul#poListRow").querySelectorAll('li').forEach(function(list,indx){
				if(indx===topPosition){list.querySelector('.flexStartRow').querySelector('.columnMD11').innerHTML = prevLI;}
				else if(indx===position){list.querySelector('.flexStartRow').querySelector('.columnMD11').innerHTML = nextLI;}
			});
			rearrangePOList();
		}
	}
}

function checkPO(){
	const poData = document.getElementsByName('payment_options[]');
	const poListData = new Array();

	const errorStatus = document.getElementById('errorPOListRow');
	errorStatus.innerHTML = '';

	for(let i = 0; i < poData.length; i++) {
		const poOneValue = poData[i].value.toUpperCase();
		if (poListData.length > 0 && poListData.indexOf(poOneValue) !== -1) {
			errorStatus.innerHTML = Translate('Duplicate Payment Options')+parseInt(i+1);
			poData[i].focus();
			return false;
		}
		else {
			poListData[i] = poOneValue;
		}
	}
	return true;
}

function addMorePO(){
	if(checkPO()===false){return false;}
	
	const poData = document.getElementsByName('payment_options[]');							
	const errorStatus = document.getElementById('errorPOListRow');
	for(let i = 0; i < poData.length; i++) {
		if (poData[i].value==='') {
			errorStatus.innerHTML = Translate('Payment Options')+' '+Translate('is missing.')+parseInt(i+1);
			poData[i].focus();
			return false;
		}
	}
		
	const ulidname = 'poListRow';
	let index = document.querySelector("ul#"+ulidname).childElementCount;
	index = parseInt(index+1);
								
	document.querySelector("#"+ulidname).appendChild(poLister('',index));	

	document.getElementsByName('payment_options[]')[parseInt(index-1)].focus();
	rearrangePOList();
}

function checkSqrUp(AddOrRemove){
	let SqrYN, attr;
	SqrYN = 'No';
	let i = 0;
	if(document.querySelectorAll('.payment_options').length){
		document.querySelectorAll('.payment_options').forEach(function(item){
			const poOneValue = item.value.toUpperCase();
			if (poOneValue==='SQUAREUP') {
				if(AddOrRemove===0){
					item.parentNode.parentNode.parentNode.remove();
					check_frmPO();
				}
				else{
					SqrYN = 'Yes';
					attr = item.readOnly;
					if (attr=== false) {
						item.readOnly = true;
					}
				}
			}
			i++;
		});
		
		if(SqrYN==='No' && AddOrRemove===1){
			document.querySelectorAll('.payment_options')[i-1].value = 'Squareup';
			check_frmPO();
			addMorePO();
			checkSqrUp(1);
		}
		rearrangePOList();
	}
}
										
function rearrangePOList(){
	if(document.querySelector("ul#poListRow").childElementCount>1){
		let l = 1;		

		document.querySelector("ul#poListRow").querySelectorAll('li').forEach(function(list){
			let upArrow = cTag('a',{ "class":"poOrderUp", "href":"javascript:void(0);","title":"Move to UP" });
			upArrow.appendChild(cTag('i',{ "class":"fa fa-arrow-up" }));

			if(l===document.querySelector("ul#poListRow").childElementCount){upArrow = '';}
			let container = list.querySelector('.flexStartRow').querySelector('.columnMD1');
			container.innerHTML = '';
			container.append(l);
			container.append(upArrow);
			l++;
		});
	}

	document.querySelectorAll('.poOrderUp').forEach(item=>{
		item.addEventListener('click',(function(){
			[...poListRow.children].indexOf(this.parentNode.parentNode.parentNode)
			const cac = [...poListRow.children].indexOf(this.parentNode.parentNode.parentNode);
			poOrderUp(cac);
		}))
	})

	callPlaceholder();
	
	document.querySelectorAll('.removeicon').forEach(function(item){
		item.remove();
	});

	document.querySelectorAll('.payment_options').forEach(item=>{
		item.addEventListener('change',function(){
			const payment_options = this.value;
			this.setAttribute('value',payment_options);
			this.title = payment_options;
		})
	})

	const countList = document.querySelector("ul#poListRow").childElementCount;								
	if(countList>1){
		for(let l=1; l<countList; l++){														
			const poValue = document.querySelector("ul#poListRow li:nth-child("+l+")").querySelector('.payment_options').value.toUpperCase();
			if(['SQUAREUP','CASH'].includes(poValue)){}
			else{
				const aTag = cTag('a',{ "class":"removeicon","href":"javascript:void(0);","title":Translate('Remove this row') });
				aTag.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Remove this row'),"title":Translate('Remove this row'),"src":"/assets/images/cross-on-white.gif" }));
				document.querySelector("ul#poListRow li:nth-child("+l+")").appendChild(aTag);
			}
		}

		document.querySelectorAll('.removeicon').forEach(remover=>{
			remover.addEventListener('click',function(){
				if(document.querySelector('ul#poListRow').children.length>1){
					this.parentElement.remove();
					rearrangePOList();
				}else{
					alert_dialog(Translate('Remove Payment Option'), Translate('You could not remove all payment options'), Translate('Ok'));
				}
			})
		})
	}	
}

async function check_frmPO(event){
	event.preventDefault();

	if(checkPO()===false){return false;}
	else{
		const submitBtn = document.querySelector("#submit");
		btnEnableDisable(submitBtn,Translate('Saving'),true);
		const jsonData = serialize("#frmPO");
		const url = '/'+segment1+'/savePO';

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.savemsg !=='error' && data.id>0){
				if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
				else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			else{
				showTopMessage('alert_msg',Translate('Error occured while changing Payment Options information! Please try again.'));
			}
			btnEnableDisable(submitBtn,Translate('Save'),false);
		}                            
		return false;
	}
}

//____________________import_customers_______________________
function import_customers(){
	let pTag, customerHeadRow;
	const supportEmail = document.getElementById('supportEmail');
	supportEmail.hidden = true;

    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Import Customers')));
        const importCustomerContainer = cTag('div', {class: "flexStartRow"});
        importCustomerContainer.appendChild(createNavigator());
            const importCustomerColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                let container = cTag('div', {class: "innerContainer", 'style': "padding-top: 0px; background: #fff"});
				if(OS!=='unknown') container.style.padding = '10px 0';
					const importCustomerRow = cTag('div',{ "class":"flexColumn columnSM12" });
						const customerTextColumn = cTag('div',{ "class":"columnSM12" });
							pTag = cTag('p',{ "align":"justify" });
							pTag.innerHTML = Translate('You can import your customer list into our software.  We use a standard CSV file type.  That file is like a spreadsheet file and all spreadsheet programs will export to it.  The following table shows the data we can import into our system.');
						customerTextColumn.appendChild(pTag);
					importCustomerRow.appendChild(customerTextColumn);

						const tableColumn = cTag('div',{ "class":"columnXS12" });
							const customerTable = cTag('table',{ "class":" columnMD12 table-bordered table-striped table-condensed cf listing " });
								const customerHead = cTag('thead',{ "class":"cf" });
									customerHeadRow = cTag('tr');
										const thCol0 = cTag('th',{ "width":"30%" });
										thCol0.innerHTML = Translate('Column Name');
										const thCol1 = cTag('th');
										thCol1.innerHTML = Translate('Description');
									customerHeadRow.append(thCol0, thCol1);
								customerHead.appendChild(customerHeadRow);
							customerTable.appendChild(customerHead);
								const customerBody = cTag('tbody');
								[
									{name:Translate('First Name'), description:Translate('Required')},
									{name:Translate('Last Name'), description:Translate('Optional / If you have both first and last name in one column that is OK')},
									{name:Translate('Email'), description:Translate('Optional')},
									{name:Translate('Company'), description:Translate('Optional')},
									{name:Translate('Contact No'), description:Translate('Optional')},
									{name:Translate('Secondary phone'), description:Translate('Optional')},
									{name:Translate('Fax'), description:Translate('Optional')},
									{name:Translate('Customer Type'), description:Translate('Optional')},
									{name:Translate('Shipping address one'), description:Translate('Optional')},
									{name:Translate('Shipping address two'), description:Translate('Optional')},
									{name:Translate('Shipping city'), description:Translate('Optional')},
									{name:Translate('Shipping state'), description:Translate('Optional')},
									{name:Translate('Shipping zip'), description:Translate('Optional')},
									{name:Translate('Shipping country'), description:Translate('Optional')},
								].forEach(item=>{
										customerHeadRow = cTag('tr');
											const tdCol0 = cTag('td');
											tdCol0.innerHTML = item.name;

											const tdCol1 = cTag('td');
											tdCol1.innerHTML = item.description;
										customerHeadRow.append(tdCol0, tdCol1);
									customerBody.appendChild(customerHeadRow);
								})										
							customerTable.appendChild(customerBody);
						tableColumn.appendChild(customerTable);
					importCustomerRow.appendChild(tableColumn);

						const bottomTextColumn = cTag('div',{ "class":"columnSM12", 'style': "padding-top: 30px;" });
							pTag = cTag('p',{ "align":"justify" });
							pTag.innerHTML = Translate('If you are currently evaluating the software once you have a pretty good idea your would like to subscribe to our service you can email your file to us to review before you attempt to import it.  Email it to SUPPORTEMAIL').replace('SUPPORTEMAIL',supportEmail.value);
						bottomTextColumn.appendChild(pTag);
					importCustomerRow.appendChild(bottomTextColumn);
				container.appendChild(importCustomerRow);
			importCustomerColumn.appendChild(container);
		importCustomerContainer.appendChild(importCustomerColumn);
    Dashboard.appendChild(importCustomerContainer);
}

//____________________import_products_______________________
function import_products(){
    let pTag, centerTag, tableTitle, productHeadRow, thCol, tdCol;
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Import Products')));
        const importProductContainer = cTag('div', {class: "flexStartRow"});
        importProductContainer.appendChild(createNavigator());
            const importProductColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                const callOutdiv = cTag('div', {class: "innerContainer", style: "padding-top: 0px; background: #fff"});
				if(OS!=='unknown') callOutdiv.style.padding = '10px 0';
					const importProductDiv = cTag('div',{ "class":"flexColumn columnXS12" });
						const importProducText = cTag('div',{ "class":"columnXS12", style: "padding-top: 15px;" });
							pTag = cTag('p',{ "align":"justify" });
							pTag.innerHTML = Translate('We can import your product names and inventory count from your current software in a CSV file.  These files are like spreadsheet files and are the normal way to transfer data.');
						importProducText.appendChild(pTag);
							pTag = cTag('p',{ "align":"justify" });
							pTag.innerHTML = Translate('If you have MULTIPLE locations contact us because their are some differences for all locations after the first one.');
						importProducText.appendChild(pTag);
							pTag = cTag('p',{ 'style': "padding-top: 15px;", "align":"justify" });
							pTag.innerHTML = Translate('We have 3 different types of products and you will need to create a separate import file for each one you want to import.');
						importProducText.appendChild(pTag);
					importProductDiv.appendChild(importProducText);

						const importProductTypeColumn = cTag('div',{ "class":"columnXS12" });
							const olProductType = cTag('ol',{ 'style': "padding-left: 50px; text-align: start;" });
								let liStandardProducts = cTag('li');
								liStandardProducts.innerHTML = Translate('Standard Products');
							olProductType.appendChild(liStandardProducts);
								let liLaborService = cTag('li');
								liLaborService.innerHTML = Translate('Labor/Services');;
							olProductType.appendChild(liLaborService);
								let liMobileDevice = cTag('li');
								liMobileDevice.innerHTML = Translate('Live Stocks');
							olProductType.appendChild(liMobileDevice);
						importProductTypeColumn.appendChild(olProductType);
					importProductDiv.appendChild(importProductTypeColumn);

						const importProductTemplateRow  = cTag('div',{ "class":"flexColumn" });
							const standardProductTemplate = cTag('div',{ "class":"columnXS12", 'style': "padding: 15px 5px;" });
								centerTag = cTag('center');
									tableTitle = cTag('b');
									tableTitle.innerHTML = Translate('FOR STANDARD PRODUCTS USE THE TEMPLATE BELOW');
								centerTag.appendChild(tableTitle);
							standardProductTemplate.appendChild(centerTag);
								const standardProductTable = cTag('table',{ "class":"columnMD12 table-bordered table-striped table-condensed cf listing" });
									const standardProductHead = cTag('thead',{ "class":"cf" });
										productHeadRow = cTag('tr');
											thCol = cTag('th',{ "width":"30%" });
											thCol.innerHTML = Translate('Column Name');
										productHeadRow.appendChild(thCol);
											thCol = cTag('th');
											thCol.innerHTML = Translate('Description');
										productHeadRow.appendChild(thCol);
									standardProductHead.appendChild(productHeadRow);
								standardProductTable.appendChild(standardProductHead);
								const standardProductBody = cTag('tbody');
									[
										{name:Translate('Category Name'), description:Translate('Optional')},
										{name:Translate('Manufacturer Name'), description:Translate('Optional')},
										{name:Translate('Product Name'), description:Translate('Required')},
										{name:Translate('SKU'), description:Translate('Optional, if present each must be unique')},
										{name:Translate('Selling Price'), description:Translate('Optional')},
										{name:Translate('Taxable'), description:Translate('Y/N Optional (Default:Y)')},
										{name:Translate('Current Inventory'), description:Translate('Optional')},
										{name:Translate('Count Inventory'), description:Translate('Y/N Optional (Default:Y)')},
										{name:Translate('Minimum stock'), description:Translate('Optional')},
										{name:Translate('Require serial number'), description:Translate('Y/N Optional (Default:N)')},
										{name:Translate('Allow Over Selling'), description:Translate('Y/N Optional (Default:Y)')}
									].forEach(item=>{
											productHeadRow = cTag('tr');
												tdCol = cTag('td');
												tdCol.innerHTML = item.name;
											productHeadRow.appendChild(tdCol);
												tdCol = cTag('td');
												tdCol.innerHTML = item.description;
											productHeadRow.appendChild(tdCol);
										standardProductBody.appendChild(productHeadRow);
									})										
								standardProductTable.appendChild(standardProductBody);
							standardProductTemplate.appendChild(standardProductTable);
						importProductTemplateRow.appendChild(standardProductTemplate);

							const laborProductTemplate = cTag('div',{ "class":"columnXS12", 'style': "padding: 15px 5px;" });
								centerTag = cTag('center');
									tableTitle = cTag('b');
									tableTitle.innerHTML = Translate('FOR LABOR/SERVICES PRODUCTS USE THE TEMPLATE BELOW');
								centerTag.appendChild(tableTitle);
							laborProductTemplate.appendChild(centerTag);
								const laborProductTable = cTag('table',{ "class":"columnMD12 table-bordered table-striped table-condensed cf listing" });
									const laborProductHead = cTag('thead',{ "class":"cf" });
										productHeadRow = cTag('tr');
											thCol = cTag('th',{ "width":"30%" });
											thCol.innerHTML = Translate('Column Name');
										productHeadRow.appendChild(thCol);
											thCol = cTag('th');
											thCol.innerHTML = Translate('Description');
										productHeadRow.appendChild(thCol);
									laborProductHead.appendChild(productHeadRow);
								laborProductTable.appendChild(laborProductHead);
									const laborProductBody = cTag('tbody');
									[
										{name:Translate('Category Name'), description:Translate('Optional')},
										{name:Translate('Manufacturer Name'), description:Translate('Optional')},
										{name:Translate('Product Name'), description:Translate('Required')},
										{name:Translate('SKU'), description:Translate('Optional, if present each must be unique')},
										{name:Translate('Cost price'), description:Translate('Optional')},
										{name:Translate('Selling Price'), description:Translate('Optional')},
										{name:Translate('Taxable'), description:Translate('Y/N Optional (Default:Y)')},

									].forEach(item=>{
											productHeadRow = cTag('tr');
												tdCol = cTag('td');
												tdCol.innerHTML = item.name;
											productHeadRow.appendChild(tdCol);
												tdCol = cTag('td');
												tdCol.innerHTML = item.description;
											productHeadRow.appendChild(tdCol);
										laborProductBody.appendChild(productHeadRow);
									})										
								laborProductTable.appendChild(laborProductBody);
							laborProductTemplate.appendChild(laborProductTable);
						importProductTemplateRow.appendChild(laborProductTemplate);

							const imeiTemplateRow = cTag('div',{ "class":"columnXS12", 'style': "padding: 15px 5px;" });
								centerTag = cTag('center');
								centerTag.append(' ');
									tableTitle = cTag('b');
									tableTitle.innerHTML = Translate('FOR DEVICES with IMEI # USE THE TEMPLATE BELOW');
								centerTag.appendChild(tableTitle);
							imeiTemplateRow.appendChild(centerTag);
								const imeiTable = cTag('table',{ "class":"columnMD12 table-bordered table-striped table-condensed cf listing" });
									const imeiHead = cTag('thead',{ "class":"cf" });
										productHeadRow = cTag('tr');
											thCol = cTag('th',{ "width":"30%" });
											thCol.innerHTML = Translate('Column Name');
										productHeadRow.appendChild(thCol);
											thCol = cTag('th');
											thCol.innerHTML = Translate('Description');
										productHeadRow.appendChild(thCol);
									imeiHead.appendChild(productHeadRow);
								imeiTable.appendChild(imeiHead);
								const imeiBody = cTag('tbody');
									[
										{name:Translate('Category Name'), description:Translate('Optional')},
										{name:Translate('Manufacturer Name'), description:Translate('Optional')},
										{name:Translate('Product Name'), description:Translate('Required')},
										{name:Translate('Color Name'), description:Translate('Optional')},
										{name:Translate('Storage'), description:Translate('Optional')},
										{name:Translate('Physical Condition'), description:Translate('Optional')},
										{name:Translate('SKU'), description:Translate('Optional, if present each must be unique')},
										{name:Translate('Selling Price'), description:Translate('Optional')},
										{name:Translate('Taxable'), description:Translate('Y/N Optional (Default:Y)')},
										{name:Translate('Current Inventory'), description:Translate('Optional')},
										{name:Translate('Count Inventory'), description:Translate('Y/N Optional (Default:Y)')},
										{name:Translate('Minimum stock'), description:Translate('Optional')},
									].forEach(item=>{
											productHeadRow = cTag('tr');
												tdCol = cTag('td');
												tdCol.innerHTML = item.name;
											productHeadRow.appendChild(tdCol);
												tdCol = cTag('td');
												tdCol.innerHTML = item.description;
											productHeadRow.appendChild(tdCol);
										imeiBody.appendChild(productHeadRow);
									})										
								imeiTable.appendChild(imeiBody);
							imeiTemplateRow.appendChild(imeiTable);
						importProductTemplateRow.appendChild(imeiTemplateRow);
					importProductDiv.appendChild(importProductTemplateRow);
				callOutdiv.appendChild(importProductDiv);
			importProductColumn.appendChild(callOutdiv);
		importProductContainer.appendChild(importProductColumn);
    Dashboard.appendChild(importProductContainer);
}

//____________________small_print_______________________
function small_print(){
    let inputField, pixelName;
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Receipt Printer & Cash Drawer')));
        const printCashContainer = cTag('div', {class: "flexStartRow"});
        printCashContainer.appendChild(createNavigator());
            const printCashColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                const callOutDiv = cTag('div', {class: "innerContainer", style: "background: #fff"});
				if(OS!=='unknown') callOutDiv.style.padding = '10px 0';
					const printCashDiv = cTag('div',{ "class":"columnXS12" });
						const printCashForm = cTag('form',{ "name":"frmsmall_print","id":"frmsmall_print","action":"#","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
						printCashForm.addEventListener('submit',check_frmsmall_print);
							const receiptPrinterRow = cTag('div',{ "class":"flex", 'style': "margin-bottom: 10px;" });
								const receiptPrinterText = cTag('div',{ "class":"columnXS12" });
								receiptPrinterText.innerHTML = Translate('Since our software is browser based any receipt printer that will print from your browser should work. We do suggest at least 80mm wide paper as it is easier to read.  We are often asked for suggests and we have found that Epson printers like the model <b>TM-T88VI</b> works well.  The cash drawer must connect to the receipt printer to work.');
							receiptPrinterRow.appendChild(receiptPrinterText);
						printCashForm.appendChild(receiptPrinterRow);

							const leftMarginRow = cTag('div',{ "class":"flex", 'style': "margin-bottom: 10px;" });
								const leftMarginName = cTag('div',{ "class":"columnXS4 columnSM5 columnMD4" });
									const leftMarginLabel = cTag('label',{ "for":"left_margin" });
									leftMarginLabel.innerHTML = Translate('Left Margin');
								leftMarginName.appendChild(leftMarginLabel);
							leftMarginRow.appendChild(leftMarginName);
								const leftMarginField = cTag('div',{ "class":"columnXS4 columnSM2 columnMD3" });
									inputField = cTag('input',{ "type":"text",'data-min':'0','data-max':'999', 'data-format': 'd', "id":"left_margin","name":"left_margin","class":"form-control","value":"15", required:'required' });
									controllNumericField(inputField, '#error_left_margin');
								leftMarginField.appendChild(inputField);
							leftMarginRow.appendChild(leftMarginField);
								pixelName = cTag('div',{ "class":"columnXS4 columnSM5", 'style': "padding-top: 10px;" });
								pixelName.append(Translate('PX'));
								pixelName.appendChild(cTag('span',{ "id":"error_left_margin", 'style': "color: #F00; padding-left: 10px;"}));
							leftMarginRow.appendChild(pixelName);
						printCashForm.appendChild(leftMarginRow);

							const rightMarginRow = cTag('div',{ "class":"flex", 'style': "margin-bottom: 10px;" });
								const rightMarginName = cTag('div',{ "class":"columnXS4 columnSM5 columnMD4" });
									const rightMarginLabel = cTag('label',{ "for":"right_margin" });
									rightMarginLabel.innerHTML = Translate('Right Margin');
								rightMarginName.appendChild(rightMarginLabel);
							rightMarginRow.appendChild(rightMarginName);
								const rightMarginField = cTag('div',{ "class":"columnXS4 columnSM2 columnMD3" });
									inputField = cTag('input',{ "type":"text",'data-min':'0','data-max':'999', 'data-format': 'd', "id":"right_margin","name":"right_margin","class":"form-control","value":"15","maxlength":"3", required:'required' })
									controllNumericField(inputField, '#error_right_margin');
								rightMarginField.appendChild(inputField);
							rightMarginRow.appendChild(rightMarginField);
								pixelName = cTag('div',{ "class":"columnXS4 columnSM5", 'style': "padding-top: 10px;" });
								pixelName.append(Translate('PX'));
								pixelName.appendChild(cTag('span',{ "id":"error_right_margin", 'style': "color: #F00; padding-left: 10px;" }));
							rightMarginRow.appendChild(pixelName);
						printCashForm.appendChild(rightMarginRow);

							const buttonColumn = cTag('div',{ "class":"columnXS12", "align":"center"});
							buttonColumn.appendChild(cTag('input',{ "type":"hidden","name":"variables_id","id":"variables_id", }));
							buttonColumn.appendChild(cTag('input',{ "class":"btn saveButton","name":"submit","id":"submit","type":"submit","value":Translate('Save') }));
						printCashForm.appendChild(buttonColumn);
					printCashDiv.appendChild(printCashForm);
				callOutDiv.appendChild(printCashDiv);
			printCashColumn.appendChild(callOutDiv);
		printCashContainer.appendChild(printCashColumn);
    Dashboard.appendChild(printCashContainer);
	AJ_small_print_MoreInfo();
}

async function AJ_small_print_MoreInfo(){
    const url = '/'+segment1+'/AJ_small_print_MoreInfo';

	fetchData(afterFetch,url,{});

	function afterFetch(data){
		document.querySelector('#left_margin').value = data.left_margin;
		document.querySelector('#right_margin').value = data.right_margin;
		document.querySelector('#variables_id').value = data.variables_id;
	 }
}

async function check_frmsmall_print(event){
	event.preventDefault();
	document.querySelector("#error_left_margin").innerHTML = '';

	let left_margin = document.querySelector("#left_margin");
    if (!left_margin.valid()) return;

	/* if(document.querySelector("#left_margin").value !==''){
		const left_margin = parseInt(document.querySelector("#left_margin").value);
		if(isNaN(left_margin)){
			document.querySelector("#error_left_margin").innerHTML = Translate('Left margin is invalid.');
			document.querySelector("#left_margin").focus();
			return false;
		}
	} */

	let right_margin = document.querySelector("#right_margin");
    if (!right_margin.valid()) return;

	/* document.querySelector("#error_right_margin").innerHTML = '';
	if(document.querySelector("#right_margin").value !==''){
		const right_margin = parseInt(document.querySelector("#right_margin").value);
		if(isNaN(right_margin)){
			document.querySelector("#error_left_margin").innerHTML = Translate('Right margin is invalid.');;
			document.querySelector("#right_margin").focus();
			return false;
		}
	} */

	const submitBtn = document.querySelector("#submit");
	btnEnableDisable(submitBtn,Translate('Saving'),true);

	const jsonData = serialize("#frmsmall_print");
	const url = '/'+segment1+'/save_small_print';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg !=='error' && data.id>0){
			document.getElementById("variables_id").value = data.id;
			if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
			else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
			showTopMessage('alert_msg',Translate('Error occured while changing Receipt Printer & Cash Drawer information! Please try again.'));
		}
		btnEnableDisable(submitBtn,Translate('Save'),false);
	}
	return false;
}

//____________________label_printer_______________________
function label_printer(){
	const font = new FontFace("Libre Barcode", "url(/assets/fonts/LibreBarcodeText.woff2)");
    document.fonts.add(font);
    font.load().then(async()=>{
		let label,span,select;
		const Dashboard = document.querySelector('#viewPageInfo');
		Dashboard.innerHTML = '';
		Dashboard.appendChild(header(Translate('Manage Label Printer')));
			const managePrinterContainer = cTag('div', {class: "flexStartRow"});
			managePrinterContainer.appendChild(createNavigator());
				const managePrinterColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
					const callOutDiv = cTag('div', {class: "innerContainer", style: "background: #fff"});
					if(OS!=='unknown') callOutDiv.style.padding = '10px 0';
						const managePrinterDiv = cTag('div',{ "class":"columnXS12" });
						managePrinterDiv.appendChild(cTag('input',{ "type":"hidden","name":"Label_Printer","id":"Label_Printer","value":"" }));
							const managePrinterForm = cTag('form',{ "name":"frmLP","id":"frmLP","action":"#","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
							managePrinterForm.addEventListener('submit',check_frmLP);
								const printTextColumn = cTag('div',{ "class":"columnXS12 noticeSection",style:'font-weight:normal' });
								printTextColumn.innerHTML = Translate('Our software uses your browser to print from so it does not require anything special. You should be able to print from any printer that your browser allows you to print to.  We have found that many users like the <b>Dymo Labelwriter 450</b> if you want a suggestion.');
							managePrinterForm.appendChild(printTextColumn);
	
								const labelSizeRow = cTag('div',{ "class":"flexStartRow" });
									const labelSizeColumn = cTag('div',{ "class":"columnSM3 columnMD2" });
										const labelTitle = cTag('label',{ "for":"label_size" });
										labelTitle.innerHTML = Translate('Label Size');
									labelSizeColumn.appendChild(labelTitle);
								labelSizeRow.appendChild(labelSizeColumn);
									const labelSizeValue = cTag('div',{ "class":"columnSM9 columnMD10" });
									[
										{value:"57|31",label:'2.25" (57mm) x 1.25" (32mm) Dymo 30334'},
										{value:"54|25",label:'2.12" (54mm)  x 1" (25mm) Dymo 30336'},
										{value:"62|28",label:'2.4" (62mm)  x 1.1" (28mm) Brother DK1209'},
									].forEach((item)=>{
											let radioLabel = cTag('label',{ "class": "cursor", 'style': "margin-bottom: 10px;" });
												const input = cTag('input',{ "type":"radio","name":"label_size","value":item.value,"click":checkCustomSize });
											radioLabel.appendChild(input);
											radioLabel.append(' '+item.label);
										labelSizeValue.appendChild(radioLabel);
										labelSizeValue.appendChild(cTag('br'));
									})
	
									const customOptionRow = cTag('div',{ "class":"flexStartRow" });
										const customOptionColumn = cTag('div',{ "class":"columnXS12", 'style': "padding-left: 0;" });
											label = cTag('label',{ "class":"cursor" });
											label.appendChild(cTag('input',{ "type":"radio","name":"label_size","id":"customLabelSize","value":"customSize","click":checkCustomSize }));
											label.append(' '+Translate('Custom'));
										customOptionColumn.appendChild(label);
									customOptionRow.appendChild(customOptionColumn);
									[
										{id:'units', label:Translate('Unit'), type:'option',value:['Inches','mm']}, 
										{id:'Width', type:'number', label:Translate('Width')}, 
										{id:'Height', type:'number', label:Translate('Height')},
									].forEach((item,indx)=>{
											const customOptionField = cTag('div',{ "class":"columnXS7 columnMD4 customSize",style:'display:none' });
												const customInGroup = cTag('div',{ "class":"input-group" });
													span = cTag('span',{ "data-toggle":"tooltip","title":"","class":"input-group-addon cursor", 'style': "min-width: 75px;", "data-original-title":item.label });
													span.innerHTML = item.label;
												customInGroup.appendChild(span);
												if(item.type==='option'){
													select = cTag('select',{ 'change':lpPreview,"name":item.id,"id":item.id,"class":"form-control" });
													setOptions(select,item.value,0,0);
													customInGroup.appendChild(select);
												}
												else{
													let input = cTag('input',{ 'change':lpPreview,"name":"label_size"+item.id,"id":"label_size"+item.id,"class":"form-control","type":"text",'data-min':'0','data-max':'99','data-format':'d.dd' });
													controllNumericField(input, '#errorSize'+item.id);
													customInGroup.appendChild(input);
												} 
											customOptionField.appendChild(customInGroup);									
											if(indx>0) customOptionField.appendChild(cTag('span',{ 'style': "color: #F00;", "id":"errorSize"+item.id }));
											customOptionRow.appendChild(customOptionField);
											labelSizeValue.appendChild(customOptionRow);
									})
								labelSizeRow.appendChild(labelSizeValue);
							managePrinterForm.appendChild(labelSizeRow);
									
								const DetailedLabelConfig = cTag('div',{id:'DetailedLabelConfig'});
									const LabelSizeWarningContainer = cTag('div',{class:"flexStartRow"});
									LabelSizeWarningContainer.append(cTag('div',{class:'columnSM3 columnMD2'}));
										const labelSizeWarning = cTag('div',{class:'columnSM9 columnMD10',style:'padding:10px'});
											const warning = cTag('div',{id:'labelWarning',class:'roundborder',style:'padding:10px'});
												const warnMsg = cTag('ul',{style:'list-style-type:disclosure-closed;list-style-position: inside;'});
													const warningHeading = cTag('b');
													warningHeading.innerHTML = "The label-size you chose:";
												warnMsg.append(warningHeading);
												warnMsg.append(cTag('li',{id:'warnTinyBarcode'}));
												warnMsg.appendChild(cTag('li',{id:'warnLineNumber'}));
												warnMsg.appendChild(cTag('li',{id:'warnCharNumber'}));

											warning.append(warnMsg);
												const cntrlr = cTag('div',{style:'display:flex;align-item:center;margin:10px 15px 0 0'})
													const lengthLabel = cTag('b');
													lengthLabel.innerHTML = 'Regular Barcode Length: '
												cntrlr.append(lengthLabel);
												cntrlr.append(cTag('input',{change:function(){document.getElementById('barcodeLengthIndicator').innerText = this.value;lpPreview()},name:'barcodeLength',type:'range',min:'1',max:'20',value:'16',style:'margin:0 10px 0 10px;width:30ch'}));
												cntrlr.append(cTag('b',{id:'barcodeLengthIndicator',style:'margin-right:3px'}),'character');
											warning.append(cntrlr);
										labelSizeWarning.append(warning);
									LabelSizeWarningContainer.append(labelSizeWarning);								
								DetailedLabelConfig.append(LabelSizeWarningContainer);								
								
									const marginRow = cTag('div',{ "class":"flex" });
										const marginColumn = cTag('div',{ "class":"columnSM3 columnMD2", 'style': "margin-top: 10px;" });
											const marginLabel = cTag('label',{ "for":"top_margin" });
											marginLabel.innerHTML = Translate('Margins');
										marginColumn.appendChild(marginLabel);
									marginRow.appendChild(marginColumn);
										const marginValue = cTag('div',{ "class":"columnSM9 columnMD10 flexStartRow", 'style': "padding-left: 10px;" });
										[
											{id:'top_margin',label:Translate('Top')},
											{id:'left_margin',label:Translate('Left')},
											{id:'bottom_margin',label:Translate('Bottom')},
											{id:'right_margin',label:Translate('Right')}
										].forEach(item=>{
												const marginValueColumn = cTag('div',{ "class":"columnXS6 columnMD3", 'style': "margin-top: 0px; padding-left: 0;" });
													const marginInGroup = cTag('div',{ "class":"input-group" });
														span = cTag('span',{ "data-toggle":"tooltip","title":"","class":"input-group-addon cursor", 'style': "min-width: 75px;", "data-original-title":item.label });
														span.innerHTML = item.label;
													marginInGroup.appendChild(span);
														let input = cTag('input',{ 'change':lpPreview,"required":"","name":item.id,"id":item.id,"class":"form-control","type":"text",'data-min':'0','data-max':'999','data-format':'d.dd' });
														controllNumericField(input, '#error_'+item.id);
													marginInGroup.appendChild(input);
												marginValueColumn.appendChild(marginInGroup);
												marginValueColumn.appendChild(cTag('span',{ 'id':'error_'+item.id,'class':'errormsg' }));
											marginValue.appendChild(marginValueColumn);
										})
									marginRow.appendChild(marginValue);
								DetailedLabelConfig.appendChild(marginRow);
		
									const orientationRow = cTag('div',{ "class":"flex"});
										const orientationColumn = cTag('div',{ "class":"columnXS3 columnMD2", 'style': "margin-top: 10px;" });
											const orientationLabel = cTag('label',{ "for":"orientation" });
											orientationLabel.innerHTML = Translate('Orientation');
										orientationColumn.appendChild(orientationLabel);
									orientationRow.appendChild(orientationColumn);
										const orientationValue = cTag('div',{ "class":"columnXS9 columnMD10" });
											const orientationDropdown = cTag('div',{ "class":"columnXS7 columnMD3", 'style': "margin-top: 0px;" });
												const selectOrientation = cTag('select',{ 'change':lpPreview,"name":"orientation","id":"orientation","class":"form-control" });
													let OrientationOption = cTag('option',{ 'value': 'Portrait'});
													OrientationOption.innerHTML = Translate('Portrait');
												selectOrientation.appendChild(OrientationOption);
													OrientationOption = cTag('option',{ 'value': 'Landscape'});
													OrientationOption.innerHTML = Translate('Landscape');
												selectOrientation.appendChild(OrientationOption);											
											orientationDropdown.appendChild(selectOrientation);
										orientationValue.appendChild(orientationDropdown);
									orientationRow.appendChild(orientationValue);
								DetailedLabelConfig.appendChild(orientationRow);

									const fontSizeRow = cTag('div',{ "class":"flex"});
										const fontSizeColumn = cTag('div',{ "class":"columnXS3 columnMD2", 'style': "margin-top: 10px;" });
											const fontSizeLabel = cTag('label',{ "for":"font_size" });
											fontSizeLabel.innerHTML = Translate('Font Size');
										fontSizeColumn.appendChild(fontSizeLabel);
									fontSizeRow.appendChild(fontSizeColumn);
										const fontSizeValue = cTag('div',{ "class":"columnXS9 columnMD10" });
											const fontSizeDropdown = cTag('div',{ "class":"columnXS7 columnMD3", 'style': "margin-top: 0px;" });
												const selectFontSize = cTag('select',{ 'change':lpPreview,"name":"fontSize","id":"font_size","class":"form-control" });
												setOptions(selectFontSize,['Small','Regular','Large'],0,0);
											fontSizeDropdown.appendChild(selectFontSize);
										fontSizeValue.appendChild(fontSizeDropdown);
									fontSizeRow.appendChild(fontSizeValue);
								DetailedLabelConfig.appendChild(fontSizeRow);
									
									const fontFamilyRow = cTag('div',{ "class":"flex"});
										const fontFamilyColumn = cTag('div',{ "class":"columnXS3 columnMD2", 'style': "margin-top: 10px;" });
											const fontFamilyLabel = cTag('label',{ "for":"font_size" });
											fontFamilyLabel.innerHTML = Translate('Font Family');
										fontFamilyColumn.appendChild(fontFamilyLabel);
									fontFamilyRow.appendChild(fontFamilyColumn);
										const fontFamilyValue = cTag('div',{ "class":"columnXS9 columnMD10" });
											const fontFamilyDropdown = cTag('div',{ "class":"columnXS7 columnMD3", 'style': "margin-top: 0px;" });
												const selectFontFamily = cTag('select',{ 'change':lpPreview,"name":"fontFamily","id":"font_family","class":"form-control" });
												setOptions(selectFontFamily,['Arial','Times New Roman','Verdana','Garamond','Comic Sans MS','Trebuchet MS','Arial Black','Impact', 'Cambria'],0,0);
											fontFamilyDropdown.appendChild(selectFontFamily);
										fontFamilyValue.appendChild(fontFamilyDropdown);
									fontFamilyRow.appendChild(fontFamilyValue);
								DetailedLabelConfig.appendChild(fontFamilyRow);
		
									const sampleLabelColumn  = cTag('div',{ "class":"columnXS12",'style':'margin-top:50px' });
										const notice = cTag('div',{class:'noticeSection'});
										notice.innerText = Translate('This is a sample label. Please make a test print and you should be able to see all 4 sides of the border around this label. If you do not then you need to increase the margin on any side you do not see when printed until you do see it.');
									sampleLabelColumn.appendChild(notice);
										let sampleRoundBorder = cTag('div',{ "class":"columnSM12 roundborder", 'style': "background: #FAFAD2; padding: 30px 0;", "id":"lpPreview" });
									sampleLabelColumn.appendChild(sampleRoundBorder);
								DetailedLabelConfig.appendChild(sampleLabelColumn);
							managePrinterForm.appendChild(DetailedLabelConfig);

								const buttonNames = cTag('div',{ "class":"flexCenterRow", 'style': "margin-top: 10px;" });
								buttonNames.appendChild(cTag('input',{ "class":"btn saveButton","name":"submit","id":"submit","type":"submit","value":Translate('Save') }));
								buttonNames.appendChild(cTag('input',{ "class":"btn defaultButton", 'style': "margin-left: 10px;", "name":"printLabel","id":"printLabel","type":"button","value":Translate('Print Test Label'),"click":testLabel }));
							managePrinterForm.appendChild(buttonNames);
						managePrinterDiv.appendChild(managePrinterForm);
					callOutDiv.appendChild(managePrinterDiv);
				managePrinterColumn.appendChild(callOutDiv);
			managePrinterContainer.appendChild(managePrinterColumn);
		Dashboard.appendChild(managePrinterContainer);
		AJ_label_printer_MoreInfo();
	})
	
}

async function AJ_label_printer_MoreInfo(){
    const url = '/'+segment1+'/AJ_label_printer_MoreInfo';

	fetchData(afterFetch,url,{});

	function afterFetch(data){
		document.frmLP.label_size.value = data.label_size;
		if(data.label_size==='customSize') {
			document.querySelectorAll('.customSize').forEach(item=>{
				if(item.style.display === 'none'){
					item.style.display = '';
				}
			});
		}

		document.querySelector('[name="barcodeLength"]').value = data.barcodeLength||'20';
		document.querySelector('#barcodeLengthIndicator').innerText = data.barcodeLength||20;

		document.querySelector('#bottom_margin').value = data.bottom_margin;
		document.querySelector('#left_margin').value = data.left_margin;
		document.querySelector('#right_margin').value = data.right_margin;
		document.querySelector('#top_margin').value = data.top_margin;

		document.querySelector('#font_size').value = data.fontSize;
		document.querySelector('#font_family').value = data.fontFamily;
		document.querySelector('#label_sizeHeight').value = data.label_sizeHeight;
		document.querySelector('#label_sizeWidth').value = data.label_sizeWidth;
		document.querySelector('#orientation').value = data.orientation;
		document.querySelector('#units').value = data.units;
		lpPreview();
	}
}

async function testLabel(){
	let label_sizeWidth = parseFloat(document.querySelector("#label_sizeWidth").value);
	if(label_sizeWidth==='' || isNaN(label_sizeWidth)){label_sizeWidth = 0;}
	let label_sizeHeight = parseFloat(document.querySelector("#label_sizeHeight").value);
	if(label_sizeHeight==='' || isNaN(label_sizeHeight)){label_sizeHeight = 0;}
	document.querySelector("#errorSizeWidth").innerHTML = '';
	document.querySelector("#errorSizeHeight").innerHTML = '';
	if(document.querySelector("#units").value==='Inches'){
		if(label_sizeWidth>4){
			document.querySelector("#errorSizeWidth").innerHTML = Translate('Width should be < 4');
			document.querySelector("#label_sizeWidth").focus();
			return false;
		}
		if(label_sizeHeight>4){
			document.querySelector("#errorSizeHeight").innerHTML = Translate('Height should be < 2');
			document.querySelector("#label_sizeHeight").focus();
			return false;
		}
	}
	else{
		if(label_sizeWidth>99){
			document.querySelector("#errorSizeWidth").innerHTML = Translate('Width should be < 100');
			document.querySelector("#label_sizeWidth").focus();
			return false;
		}
		if(label_sizeWidth>99){
			document.querySelector("#errorSizeHeight").innerHTML = Translate('Height should be < 100');
			document.querySelector("#label_sizeHeight").focus();
			return false;
		}
	}

	document.querySelector("#error_top_margin").innerHTML = '';
	if(document.querySelector("#top_margin").value !==''){
		const top_margin = parseInt(document.querySelector("#top_margin").value);
		if(isNaN(top_margin)){
			document.querySelector("#error_top_margin").innerHTML = Translate('Top margin is invalid.');
			document.querySelector("#top_margin").focus();
			return false;
		}
	}

	document.querySelector("#error_right_margin").innerHTML = '';
	if(document.querySelector("#right_margin").value !==''){
		const right_margin = parseInt(document.querySelector("#right_margin").value);
		if(isNaN(right_margin)){
			document.querySelector("#error_right_margin").innerHTML = Translate('Right margin is invalid.');;
			document.querySelector("#right_margin").focus();
			return false;
		}
	}

	document.querySelector("#error_bottom_margin").innerHTML = '';
	if(document.querySelector("#bottom_margin").value !==''){
		const bottom_margin = parseInt(document.querySelector("#bottom_margin").value);
		if(isNaN(bottom_margin)){
			document.querySelector("#error_bottom_margin").innerHTML = Translate('Bottom margin is invalid.');
			document.querySelector("#bottom_margin").focus();
			return false;
		}
	}

	document.querySelector("#error_left_margin").innerHTML = '';
	if(document.querySelector("#left_margin").value !==''){
		const left_margin = parseInt(document.querySelector("#left_margin").value);
		if(isNaN(left_margin)){
			document.querySelector("#error_left_margin").innerHTML = Translate('Left margin is invalid.');
			document.querySelector("#left_margin").focus();
			return false;
		}
	}

	const submitBtn = document.querySelector("#submit");
	btnEnableDisable(submitBtn,Translate('Saving'),true);

	const jsonData = serialize("#frmLP");
	const url = '/'+segment1+'/saveLP';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg !=='error' && data.id>0){
			if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
			else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
			const label = document.querySelector('#lpPreview center div').cloneNode(true);
			label.style.textAlign = 'center';
			label.style.border = 'none';

			let printWindow = window.open('', '', 'height=600,width=900');
			if(!printWindow){
				btnEnableDisable(submitBtn,Translate('Save'),false);
				alert_dialog(Translate('Popup Blocked'),Translate("It looks like your Browser Blocked to pop up a new window. Please change into Settings to allow."),Translate('Ok'));
				return;
			}
				let style = cTag('style');
				style.innerHTML = `
					*{margin:0;box-sizing: border-box;} 
					@media print{@page {size:${document.getElementById('orientation').value};margin: 0px;}}
					#previewBarcode:before,#previewBarcode:after{
						content:'';
						height: calc(60% + 10px);
						width: 1px;
						background: white;
						position: absolute;
						top: -5px;
						left: -1px;
					  }
					#previewBarcode:after{ left:unset;right:-1px; }
				`;
			printWindow.document.head.append(style);			

			const font = new FontFace("Libre Barcode", "url(/assets/fonts/LibreBarcodeText.woff2)");
			printWindow.document.fonts.add(font)
			font.load().then(async()=>{
				printWindow.document.body.append(label);
				printWindow.print();
				printWindow.close();
			})
		}
		else{
			showTopMessage('alert_msg',Translate('Error occured while changing Label Printer information! Please try again.'));
		}
		btnEnableDisable(submitBtn,Translate('Save'),false);
	}
}

function checkCustomSize(){
	const label_size = document.frmLP.label_size.value;
	if(label_size==='customSize'){
		document.querySelectorAll('.customSize').forEach(item=>{
			if(item.style.display === 'none'){
				item.style.display = '';
			}
		});
		document.querySelector('#label_sizeWidth').required = true;
		document.querySelector('#label_sizeHeight').required = true;
		document.querySelector( "#top_margin").value = 2;
		document.querySelector( "#left_margin").value = 2;
		document.querySelector( "#bottom_margin").value = 2;
		document.querySelector( "#right_margin").value = 2;
	}
	else{
		document.querySelectorAll('.customSize').forEach(item=>{
			if(item.style.display !== 'none'){
				item.style.display = 'none';
			}
		});
		document.querySelector('#label_sizeWidth').required = false;
		document.querySelector('#label_sizeHeight').required = false;
		if(label_size==='57|31'){
			document.querySelector( "#top_margin").value = 5;
			document.querySelector( "#left_margin").value = 3;
			document.querySelector( "#bottom_margin").value = 3;
			document.querySelector( "#right_margin").value = 3;
		}
		else if(label_size==='54|25'){
			document.querySelector( "#top_margin").value = 5;
			document.querySelector( "#left_margin").value = 6;
			document.querySelector( "#bottom_margin").value = 2;
			document.querySelector( "#right_margin").value = 8;
		}
		else if(label_size==='62|28'){
			document.querySelector( "#top_margin").value = 10;
			document.querySelector( "#left_margin").value = 5;
			document.querySelector( "#bottom_margin").value = 10;
			document.querySelector( "#right_margin").value = 5;
		}
	}
	lpPreview();
}

async function lpPreview(){
	const DetailedLabelConfig = document.getElementById('DetailedLabelConfig');
	const printLabel = document.getElementById('printLabel');
	const label_size = document.frmLP.label_size.value;
	if(label_size===''){
		DetailedLabelConfig.style.display = 'none';
		printLabel.style.display = 'none';
		return;
	}
	else{
		DetailedLabelConfig.style.display = '';
		printLabel.style.display = '';
	}

	if(label_size==='customSize'){
		let label_sizeWidth = parseFloat(document.querySelector("#label_sizeWidth").value);
		if(label_sizeWidth==='' || isNaN(label_sizeWidth)){label_sizeWidth = 0;}
		let label_sizeHeight = parseFloat(document.querySelector("#label_sizeHeight").value);
		if(label_sizeHeight==='' || isNaN(label_sizeHeight)){label_sizeHeight = 0;}
		document.querySelector("#errorSizeWidth").innerHTML = '';
		document.querySelector("#errorSizeHeight").innerHTML = '';
		if(document.querySelector("#units").value==='Inches'){
			if(label_sizeWidth===0) document.querySelector("#label_sizeWidth").value = '2.7';
			if(label_sizeHeight===0) document.querySelector("#label_sizeHeight").value = '1.2';

			if(label_sizeWidth>4){
				document.querySelector("#errorSizeWidth").innerHTML = Translate('Width should be < 4');
				document.querySelector("#label_sizeWidth").focus();
				return false;
			}
			if(label_sizeHeight>2){
				document.querySelector("#errorSizeHeight").innerHTML = Translate('Height should be < 2');
				document.querySelector("#label_sizeHeight").focus();
				return false;
			}
		}
		else{
			if(label_sizeWidth===0) document.querySelector("#label_sizeWidth").value = '70';
			if(label_sizeHeight===0) document.querySelector("#label_sizeHeight").value = '30';

			if(label_sizeWidth>99){
				document.querySelector("#errorSizeWidth").innerHTML = Translate('Width should be < 100');
				document.querySelector("#label_sizeWidth").focus();
				return false;
			}
			if(label_sizeHeight>99){
				document.querySelector("#errorSizeHeight").innerHTML = Translate('Height should be < 100');
				document.querySelector("#label_sizeHeight").focus();
				return false;
			}
		}
	}

	const labelInfo = serialize("#frmLP");
	let unit = labelInfo.units==='mm'?3.779:96;
	let labelSize = {
		"57|31":{width:215.43,height:117.17},
		"54|25":{width:204.09,height:94.49},
		"62|28":{width:234.33,height:105.83},
	}

	let labelwidth = labelSize[labelInfo.label_size]?labelSize[labelInfo.label_size].width:labelInfo.label_sizeWidth*unit;
	let labelheight = labelSize[labelInfo.label_size]?labelSize[labelInfo.label_size].height:labelInfo.label_sizeHeight*unit;
	let font_size = ({'Small':'11', 'Regular':'12', 'Large':'13'})[document.getElementById('font_size').value];
	const font_family = document.getElementById('font_family').value;

	const TopMargin = parseInt(labelInfo.top_margin);
	const BottomMargin =  parseInt(labelInfo.bottom_margin);
	const LeftMargin =  parseInt(labelInfo.bottom_margin);
	const RightMargin =  parseInt(labelInfo.bottom_margin);
	const AvailableSpace = {
		height: labelheight-(TopMargin+BottomMargin),//2px needs for margin
		width: labelwidth-(LeftMargin+RightMargin)
	}

	const lpPreview = document.querySelector("#lpPreview");
	lpPreview.innerHTML = '';
	if(!(labelwidth ===0|| labelheight===0)){
			let center = cTag('center');
				let Label = cTag('div',{ 'style':`width: ${labelwidth}px; height: ${labelheight}px; border: 1px solid black; page-break-after: always; background: #fff; overflow:hidden;` });
					let availabeSpaceContainer = cTag('div',{'style':`display:flex;flex-direction:column;justify-content:space-around;line-height: 1;height:${AvailableSpace.height}px;border:1px solid black;background:#fff;margin:${labelInfo.top_margin}px ${labelInfo.right_margin}px ${labelInfo.bottom_margin}px ${labelInfo.left_margin}px`})
						let labelContent = cTag('pre',{style:`font-family:${font_family};font-size:${font_size}px;white-space: pre-wrap;overflow-wrap: break-word;`});
					availabeSpaceContainer.appendChild(labelContent);
						let barcode = cTag('span',{id:'previewBarcode',style:`font-family: 'Libre Barcode';font-size: 35px;white-space:nowrap;overflow-wrap: normal;display:inline-block;position: relative;`});
						barcode.innerHTML = encodeToCode128('FGR14528978563214555'.slice(0,document.querySelector("[name='barcodeLength']").value));
					availabeSpaceContainer.appendChild(barcode);
				Label.appendChild(availabeSpaceContainer);
			center.appendChild(Label);
		lpPreview.appendChild(center);

		let BarcodeSize = resizeBarCode(availabeSpaceContainer.getBoundingClientRect().width,barcode);
		if(BarcodeSize>=30) {
			let charInBarcode = document.querySelector("[name='barcodeLength']").value;
			document.getElementById('warnTinyBarcode').innerHTML = `has enough space to produce readable barcode of <b>${charInBarcode}</b> character. If you think the Barcode might be larger then feel free to change the size and see the result`;
			document.getElementById('labelWarning').style.backgroundColor = 'lightcyan';
		}
		else{
			document.getElementById('warnTinyBarcode').innerHTML = "might produce tiny barcode, so please consider checking Preview with your Scanner";
			document.getElementById('labelWarning').style.backgroundColor = 'lightpink';
		} 

		let availableSpaceForText = AvailableSpace.height-BarcodeSize;
		let numberOfLineTobeFit = Math.floor(availableSpaceForText/font_size);//lineHeight is equal to fontSize
		
		let charTobeFitInLine = Math.floor(AvailableSpace.width/(font_size*0.5));//we found font takes avarage 50% space of it's size
        document.getElementById('warnLineNumber').innerHTML = `might contain <b>${numberOfLineTobeFit}</b> lines of information including Barcode`;
        document.getElementById('warnCharNumber').innerHTML = `might contain <b>${charTobeFitInLine}</b> characters in each line`;

		let infos = ['Cell-Store\n','iPhone-11\n','$250.00\n']
		for (let index = 0; index < numberOfLineTobeFit; index++) {
			labelContent.append(infos[index%3]);
		}
		trimLabelText(labelContent,availableSpaceForText); 		
	}
}

async function check_frmLP(event){
	event.preventDefault();

	const label_size = [...document.querySelectorAll('input[type="radio"][name="label_size"]')].filter(radio=>radio.checked)[0].value;
	
	if(label_size==='customSize'){
		let label_sizeWidth = parseFloat(document.querySelector("#label_sizeWidth").value);
		if(label_sizeWidth==='' || isNaN(label_sizeWidth)){label_sizeWidth = 0;}
		let label_sizeHeight = parseFloat(document.querySelector("#label_sizeHeight").value);
		if(label_sizeHeight==='' || isNaN(label_sizeHeight)){label_sizeHeight = 0;}
		document.querySelector("#errorSizeWidth").innerHTML = '';
		document.querySelector("#errorSizeHeight").innerHTML = '';
	
		if(document.querySelector("#units").value==='Inches'){
			if(label_sizeWidth>4){
				document.querySelector("#errorSizeWidth").innerHTML = Translate('Width should be < 4');
				document.querySelector("#label_sizeWidth").focus();
				return false;
			}
			if(label_sizeHeight>4){
				document.querySelector("#errorSizeHeight").innerHTML = Translate('Height should be < 2');
				document.querySelector("#label_sizeHeight").focus();
				return false;
			}
		}
		else{
			if(label_sizeWidth>99){
				document.querySelector("#errorSizeWidth").innerHTML = Translate('Width should be < 100');
				document.querySelector("#label_sizeWidth").focus();
				return false;
			}
			if(label_sizeWidth>99){
				document.querySelector("#errorSizeHeight").innerHTML = Translate('Height should be < 100');
				document.querySelector("#label_sizeHeight").focus();
				return false;
			}
		}
	}
	
	
	document.querySelector("#error_top_margin").innerHTML = '';
	if(document.querySelector("#top_margin").value !==''){
		const top_margin = parseInt(document.querySelector("#top_margin").value);
		if(isNaN(top_margin)){
			document.querySelector("#error_top_margin").innerHTML = Translate('Top margin is invalid.');
			document.querySelector("#top_margin").focus();
			return false;
		}
	}
	
	document.querySelector("#error_right_margin").innerHTML = '';
	if(document.querySelector("#right_margin").value !==''){
		const right_margin = parseInt(document.querySelector("#right_margin").value);
		if(isNaN(right_margin)){
			document.querySelector("#error_right_margin").innerHTML = Translate('Right margin is invalid.');;
			document.querySelector("#right_margin").focus();
			return false;
		}
	}
	
	document.querySelector("#error_bottom_margin").innerHTML = '';
	if(document.querySelector("#bottom_margin").value !==''){
		const bottom_margin = parseInt(document.querySelector("#bottom_margin").value);
		if(isNaN(bottom_margin)){
			document.querySelector("#error_bottom_margin").innerHTML = Translate('Bottom margin is invalid.');
			document.querySelector("#bottom_margin").focus();
			return false;
		}
	}
	
	document.querySelector("#error_left_margin").innerHTML = '';
	if(document.querySelector("#left_margin").value !==''){
		const left_margin = parseInt(document.querySelector("#left_margin").value);
		if(isNaN(left_margin)){
			document.querySelector("#error_left_margin").innerHTML = Translate('Left margin is invalid.');
			document.querySelector("#left_margin").focus();
			return false;
		}
	}

	const submitBtn = document.querySelector("#submit");
	btnEnableDisable(submitBtn,Translate('Saving'),true);	

	const jsonData = serialize("#frmLP");
	const url = '/'+segment1+'/saveLP';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.savemsg !=='error' && data.id>0){
			if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
			else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
			lpPreview(false);
		}
		else{
			showTopMessage('alert_msg',Translate('Error occured while changing Label Printer information! Please try again.'));
		}
		btnEnableDisable(submitBtn,Translate('Save'),false);
	}
	return false;
}

document.addEventListener('DOMContentLoaded', async()=>{	
	let layoutFunctions = { accounts_setup, company_info, taxes, payment_options, import_customers, import_products, small_print, label_printer };
	layoutFunctions[segment2]();
	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
	leftsideHide("secondarySideMenu",'secondaryNavMenu');
});


