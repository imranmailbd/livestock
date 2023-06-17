import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, printbyurl, confirm_dialog, alert_dialog, showTopMessage, 
    setOptions, addPaginationRowFlex, btnEnableDisable, upload_dialog, AJarchive_tableRow, AJremove_tableRow, 
    popup_dialog600, sanitizer, getOneRowInfo, unarchiveData, fetchData, listenToEnterKey, addCustomeEventListener, 
    triggerEvent, callPlaceholder, AJremove_Picture, serialize, onClickPagination, leftsideHide, barcodeLabel
} from './common.js';

if(segment2==='') segment2 = 'myInfo';

//=========common functions========
function header(label){
    const header = cTag('div');
        let headerTitle = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
        headerTitle.append(label+' ');
        headerTitle.appendChild(cTag('i',{ 'class':"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip",'data-placement':"bottom",'title':"",'data-original-title':Translate('This page captures the accounts settings') }));
    header.appendChild(headerTitle);
    return header;
}
function leftSideMenu(){
    let NavLink, navigatorLi;
    let SetupPer = 1;
    if(document.getElementById("SetupPer")){SetupPer = document.getElementById("SetupPer").value;}
    if(SetupPer===0){
        NavLink = [
            {
                navTitle: Translate('Accounts Setup'),
                navLinks: [
                    {title: Translate('My Information'), href: '/Settings/myInfo'},
                ]
            }
        ]
    }
    else{
        NavLink = [
            {
                navTitle: Translate('Accounts Setup'),
                navLinks: [
                    {title: Translate('My Information'), href: '/Settings/myInfo'},
                    {title: Translate('Setup Users'), href: '/Settings/user'},
                    {title: Translate('PO Setup'), href: '/Settings/po_setup'},
                    {title: Translate('Barcode Labels'), href: '/Settings/barcode_labels'},
                    {title: Translate('Restrict Access'), href: '/Settings/restrict_access'}
                ]
            },
            {
                navTitle: Translate('Devices'),
                navLinks: [
                    {title: Translate('Carriers'), href: '/Settings/carriers'},
                    {title: Translate('Conditions'), href: '/Settings/conditions'},
                    {title: Translate('Custom Fields'), href: '/Settings/devices_custom_fields'}
                ]
            },
            {
                navTitle: Translate('Cash Register'),
                navLinks: [
                    {title: Translate('General'), href: '/Settings/cash_Register_general'},
                    {title: Translate('Counting Cash Til'), href: '/Settings/counting_Cash_Til'},
                    {title: Translate('Multiple Drawers'), href: '/Settings/multiple_Drawers'},
                ]
            },
            {
                navTitle: Translate('Invoices'),
                navLinks: [{title: Translate('General'), href: '/Settings/invoices_general'},]
            },
            {
                navTitle: Translate('Orders'),
                navLinks: [
                    {title: Translate('Custom Statuses'), href: '/Settings/customStatuses'},
                    {title: Translate('Orders Print'), href: '/Settings/ordersPrint'},
                ]
            },
            {
                navTitle: Translate('Repairs'),
                navLinks: [
                    {title: Translate('General'), href: '/Settings/repairs_general'},
                    {title: Translate('Custom Statuses'), href: '/Settings/repairCustomStatuses'},
                    {title: Translate('Notifications'), href: '/Settings/notifications'},
                    {title: Translate('Custom Fields'), href: '/Settings/repairs_custom_fields'},
                    {title: Translate('Forms'), href: '/Settings/forms'},
                ]
            },
            {
                navTitle: Translate('Customers'),
                navLinks: [{title: Translate('Custom Fields'), href: '/Settings/customers_custom_fields'}]
            },
            {
                navTitle: Translate('Products'),
                navLinks: [{title: Translate('Custom Fields'), href: '/Settings/products_custom_fields'}]
            }
        ]
    }
    
    let navigator = cTag('div', {class: "columnMD2 columnSM3", 'style': "margin: 0; padding-right: 0;"});
        let callOutDiv = cTag('div', {'style': "margin-top: 0", class: "innerContainer bs-callout-info"});
            const navigatorLink = cTag('a', {'href': "javascript:void(0);", id: "secondarySideMenu"});
                let faFontSize = cTag('i', {class: "fa fa-align-justify", 'style': "font-size: 2em;"});
            navigatorLink.appendChild(faFontSize);
        callOutDiv.appendChild(navigatorLink);
            let navigatorUl = cTag('ul', {class: "secondaryNavMenu settingslefthide"});
            NavLink.forEach(navSection=>{
                navigatorLi = cTag('li');
                    let navigatorHeader = cTag('h4', {style: "font-size: 18px"});
                        let navigatorSpan = cTag('span');
                        navigatorSpan.innerHTML = navSection.navTitle;
                    navigatorHeader.appendChild(navigatorSpan);
                navigatorLi.appendChild(navigatorHeader);
            navigatorUl.appendChild(navigatorLi);
                navSection.navLinks.forEach(item=>{
                    navigatorLi = cTag('li');
                    if(segment2==='formFields' && item.href === `/${segment1}/forms`){
                        navigatorLi.setAttribute('class','activeclass');
                    }
                    else if(item.href === `/${segment1}/${segment2}`){
                        navigatorLi.setAttribute('class','activeclass');
                    }
                        let aTag = cTag('a', { 'style': "margin-left: 10px;", 'href':item.href, title: item.title});               
                            let span = cTag('span');
                            span.innerHTML = item.title;
                        aTag.appendChild(span);
                    navigatorLi.appendChild(aTag);
                navigatorUl.appendChild(navigatorLi)
                })
            })                
        callOutDiv.appendChild(navigatorUl);
    navigator.appendChild(callOutDiv);
    return navigator;
}
function createListRow(data,tdAttributes,tableName){
    let table = document.getElementById("tableRows");
    table.innerHTML = '';
    if(data.length){
        data.forEach((item)=>{
            const listTablesRow = cTag('tr');
            item.forEach((info,indx)=>{
                if(indx===0 || (tableName==='user' && indx===3)) return;
                    const tdCol1 = cTag('td');
                    const attributes = tdAttributes[indx-1];
                    for (const key in attributes) {
                        tdCol1.setAttribute(key,attributes[key]);
                    }
                    if(tableName==='user'){
                        let no_restrict_ip = parseInt(item[3]);
                        if(no_restrict_ip>0){
                            tdCol1.setAttribute('style', 'background:#EDFFEC');
                        }
                    }
                        const aTag = cTag('a',{class:"anchorfulllink", 'click':()=>getOneRowInfo(tableName, item[0],setUserRoll)});                    
                        aTag.innerHTML = info;
                    tdCol1.appendChild(aTag);
                listTablesRow.appendChild(tdCol1);
            })
            table.appendChild(listTablesRow);
        })
    }
    else{
		let colspan = tdAttributes.length;
		const listTableRow = cTag('tr');
			const tdCol = cTag('td', {colspan:colspan, 'style': "color: #F00; font-size: 16px;"});
			tdCol.innerHTML = Translate('There is no data found');
        listTableRow.appendChild(tdCol);
		table.appendChild(listTableRow);
	}
}
function custom_fields_creator(label,module){
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
	Dashboard.appendChild(header(label));
        const customFieldsContainer = cTag('div',{class: "flexSpaBetRow"});
        customFieldsContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let customFieldsColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDiv = cTag('div',{'class':"innerContainer bs-callout-info",'style': callOutDivStyle});
                    const customFieldsRow = cTag('div',{ "class":"flexSpaBetRow" });
						let customFields = cTag('div',{ "class":"columnXS12", 'style': "margin: 0;" });
                            const titleRow = cTag('div',{ "class":"flexSpaBetRow" });
                                const titleName = cTag('div',{ "class":"columnXS6 columnSM6", 'style': "margin: 0;" });
									let headerTitle = cTag('h4', {'style': "font-size: 18px"});
									headerTitle.append(Translate('Custom Fields')+' ');
									headerTitle.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip", "data-placement":"bottom", "title":"", "data-original-title":Translate('Custom Fields') }));
                                titleName.appendChild(headerTitle);
                            titleRow.appendChild(titleName);
                                const buttonName = cTag('div',{ "class":"columnXS6 columnSM6", 'style': "margin: 0; text-align: end;" });
									let createButton = cTag('button',{ "class":"btn createButton", "click":()=>AJgetPopup_custom_fields(module, 0), "title":Translate('Create Custom Fields') });
                                    createButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Custom Fields'));
                                buttonName.appendChild(createButton);
                            titleRow.appendChild(buttonName);
                        customFields.appendChild(titleRow);
							const customFieldsTable = cTag('div',{ "class":"flexSpaBetRow" });
								let customFieldsTableColumn = cTag('div',{ "class":"columnSM12", "style":"position:relative;" });
                                    const divNoMore = cTag('div',{ "id":"no-more-tables" });
										const customTable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing" });
											let customHead = cTag('thead',{ "class":"cf" });
                                                const customHeadRow = cTag('tr');
                                                    const tdCol0 = cTag('th',{ "align":"center", "width":"10%" });
													tdCol0.innerHTML = Translate('Order');
													const tdCol1 = cTag('th',{ "align":"left" });
													tdCol1.innerHTML = Translate('Field Name');
													const tdCol2 = cTag('th',{ "align":"center", "width":"10%" });
													tdCol2.innerHTML = Translate('Required');
													const tdCol3 = cTag('th',{ "align":"left", "width":"20%" });
													tdCol3.innerHTML = Translate('Field Type');
													const tdCol4 = cTag('th',{ "align":"center", "width":"5%" });
													tdCol4.appendChild(cTag('i',{ "class":"fa fa-remove", 'style': "font-size: 16px;" }));
                                                customHeadRow.append(tdCol0, tdCol1, tdCol2, tdCol3, tdCol4);
                                            customHead.appendChild(customHeadRow);
                                        customTable.appendChild(customHead);
										customTable.appendChild(cTag('tbody',{ "id":"tableRows" }));
                                    divNoMore.appendChild(customTable);
                                customFieldsTableColumn.appendChild(divNoMore);
                            customFieldsTable.appendChild(customFieldsTableColumn);
                        customFields.appendChild(customFieldsTable);
                    customFieldsRow.appendChild(customFields);
                callOutDiv.appendChild(customFieldsRow);
            customFieldsColumn.appendChild(callOutDiv);
        customFieldsContainer.appendChild(customFieldsColumn);
    Dashboard.appendChild(customFieldsContainer);
    AJ_custom_fields_MoreInfo();
}
async function AJ_custom_fields_MoreInfo(){
    let endpoint = '/AJ_'+segment2+'_MoreInfo'
    const url = '/'+segment1+endpoint;
    fetchData(afterFetch,url,{});
    function afterFetch(data){            
        setCustomFieldsTableRows(data.tabledata, [
            {'data-title':Translate('Order'), 'align':'left'},
            {'data-title':Translate('Field Name'), 'align':'left'},
            {'data-title':Translate('Required'), 'align':'center'},
            {'data-title':Translate('Field Type'), 'align':'left'},
            {'data-title':Translate('Remove'), 'align':'center'}
        ]);
    }
}
async function AJgetPopup_custom_fields(field_for, custom_fields_id){
    let checkstr, requiredField, inputField;	
	const jsonData = {"custom_fields_id":custom_fields_id};
	const url = "/Settings/AJgetPopup_custom_fields";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.field_required>0){checkstr = true;}
        else{checkstr = false;}
        let DropDownOptions = '';
        if(data.field_type==='DropDown'){
            DropDownOptions = data.parameters.split('||');									
        }
        const field_type_options = {
            'TextBox': Translate('Text Box'),
            'TextAreaBox':Translate('Text Area Box'),
            'Date':Translate('Date'),
            'DropDown':Translate('Drop Down'),
            'Checkbox':Translate('Checkbox'),
            'PDF':Translate('Upload PDF'),
            'Picture':Translate('Upload Picture'),
        }
        let formhtml = document.createElement('div');
        formhtml.appendChild(cTag('div',{ "id":"error_custom_fields", "class":"errormsg" }));
            const customForm = cTag('form',{ "action":"#", "name":"frmcustom_fields", "id":"frmcustom_fields", "enctype":"multipart/form-data", "method":"post", "accept-charset":"utf-8" });
                let customFieldNameRow = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;", "align":"left" });
                    let customFieldNameColumn = cTag('div',{ "class":"columnSM4" });
                        const customFieldLabel = cTag('label',{ "for":"field_name" });
                        customFieldLabel.append(Translate('Field Name'));
                            requiredField = cTag('span',{ "class":"required" });
                            requiredField.innerHTML = '*';
                        customFieldLabel.appendChild(requiredField);
                    customFieldNameColumn.appendChild(customFieldLabel);
                customFieldNameRow.appendChild(customFieldNameColumn);
                    let customFieldValue = cTag('div',{ "class":"columnSM8"});
                        inputField = cTag('input',{ "required":"required", "type":"text", "class":"form-control", "name":"field_name", "id":"field_name", "value":data.field_name, "maxlength":"30" });
                    customFieldValue.appendChild(inputField);
                customFieldNameRow.appendChild(customFieldValue);
            customForm.appendChild(customFieldNameRow);
                let customFieldTypeRow = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;", "align":"left" });
                    let customFieldTypeColumn = cTag('div',{ "class":"columnSM4" });
                        let customFieldTypeLabel = cTag('label',{ "for":"field_type" });
                        customFieldTypeLabel.append(Translate('Field Type'));
                            requiredField = cTag('span',{ "class":"required" });
                            requiredField.innerHTML = '*';
                        customFieldTypeLabel.appendChild(requiredField);
                    customFieldTypeColumn.appendChild(customFieldTypeLabel);
                customFieldTypeRow.appendChild(customFieldTypeColumn);
                    let customFieldTypeDropDown = cTag('div',{ "class":"columnSM8" });
                        let selectFieldType = cTag('select',{ "required":"required", "class":"form-control", "id":"field_type", "name":"field_type", "change":checkFieldType });
                            let fieldTypeOption = cTag('option',{ "value":"" });
                            fieldTypeOption.innerHTML = Translate('Select Field Type');
                        selectFieldType.appendChild(fieldTypeOption);
                        setOptions(selectFieldType,field_type_options,1,0);
                    customFieldTypeDropDown.appendChild(selectFieldType);
                customFieldTypeRow.appendChild(customFieldTypeDropDown);
            customForm.appendChild(customFieldTypeRow);
                let dropDownRows = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;", "align":"left", "id":"DropDownRow" });
                    let dropDownTitle = cTag('div',{ "class":"columnSM4"});
                        let dropDownLabel = cTag('label');
                        dropDownLabel.append(Translate('Drop Down Options'));
                            requiredField = cTag('span',{ "class":"required" });
                            requiredField.innerHTML = '*';
                        dropDownLabel.appendChild(requiredField);
                    dropDownTitle.appendChild(dropDownLabel);
                dropDownRows.appendChild(dropDownTitle);
                    let dropDownOption1 = cTag('div',{ "class":"columnSM8 plusIconPosition roundborder"});
                        let ulDropDown = cTag('ul',{ "id":"DropDownOptions", "class":"multipleRowList" });
                        if(DropDownOptions.length>0){
                            DropDownOptions.forEach(item=>{
                                    let liDropDown = cTag('li');
                                    liDropDown.appendChild(cTag('input',{ "type":"text","class":"form-control DropDown", 'style': "margin-bottom: 10px;", "name":"DropDown[]","value":item,"maxlength":"50" }));
                                        let removeLink = cTag('a',{ "class":"removeicon","href":"javascript:void(0);","title":Translate('Remove this row') });
                                        removeLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Remove this row'),"title":Translate('Remove this row'),"src":"/assets/images/cross-on-white.gif" }));
                                    liDropDown.appendChild(removeLink);
                                ulDropDown.appendChild(liDropDown);
                            })
                        }
                    dropDownOption1.appendChild(ulDropDown);
                        let addDropDown = cTag('div',{ "class":"addNewPlusIcon" });
                            let addLink = cTag('a',{ "href":"javascript:void(0);", "title":Translate('Add More Drop Down Options'), "click":addDropDownOptions });
                            addLink.appendChild(cTag('img',{ "align":"absmiddle", "alt":Translate('Add More Drop Down Options'), "title":Translate('Add More Drop Down Options'), "src":"/assets/images/plus20x25.png" }));
                        addDropDown.appendChild(addLink);
                    dropDownOption1.appendChild(addDropDown);
                dropDownRows.appendChild(dropDownOption1);
            customForm.appendChild(dropDownRows);
                let fieldDiv12 = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;", "align":"left"  });
                    let fieldTitle = cTag('div',{ "class":"columnXS5 columnSM4"});
                        requiredField = cTag('label',{ "class":"cursor", "for":"field_required" });
                        requiredField.innerHTML = Translate('Field Required');
                    fieldTitle.appendChild(requiredField);
                fieldDiv12.appendChild(fieldTitle);
                    let fieldValue = cTag('div',{ "class":"columnXS7 columnSM8" });
                        let fieldLabel = cTag('label',{ "for":"field_required" });
                            inputField = cTag('input',{ "class":"cursor", "type":"checkbox", "name":"field_required", "id":"field_required", "value":"1" });
                            inputField.checked = checkstr;
                        fieldLabel.appendChild(inputField);
                    fieldValue.appendChild(fieldLabel);
                fieldDiv12.appendChild(fieldValue);
            customForm.appendChild(fieldDiv12);
            customForm.appendChild(cTag('input',{ "type":"hidden", "name":"field_for", "value":field_for }));
            customForm.appendChild(cTag('input',{ "type":"hidden", "name":"custom_fields_id", "value":custom_fields_id }));
        formhtml.appendChild(customForm);
                        
        popup_dialog600(Translate('Custom Fields Information'), formhtml, Translate('Save'), AJsave_custom_fields);			
        setTimeout(function() {
            document.getElementById("error_custom_fields").innerHTML = '';
            document.querySelector("#field_type").value = data.field_type;
            document.getElementById("field_name").focus();
            checkFieldType();
        }, 500);			
    }
	return true;
}
function checkFieldType(){
	let field_type = document.querySelector("#field_type").value;
	let TextOnlyRow = document.querySelector("#TextOnlyRow");
	let DropDownRow = document.querySelector("#DropDownRow");
	if(field_type==='DropDown'){
        if(TextOnlyRow){
            if(TextOnlyRow.style.display !== 'none'){
                TextOnlyRow.style.display = 'none';
            } 
        }
		if(DropDownRow){
            if(DropDownRow.style.display === 'none'){
                DropDownRow.style.display = '';
            }
        }
		addDropDownOptions();
	}
	else if(field_type==='TextOnly'){
        if(DropDownRow){
            if(DropDownRow.style.display !== 'none'){
                DropDownRow.style.display = 'none';
            }
        }
		if(TextOnlyRow){
            if(TextOnlyRow.style.display === 'none'){
                TextOnlyRow.style.display = '';
            }
        }
	}
	else{
        if(TextOnlyRow){
            if(TextOnlyRow.style.display !== 'none'){
                TextOnlyRow.style.display = 'none';
            }
        }
        if(DropDownRow){
            if(DropDownRow.style.display !== 'none'){
                DropDownRow.style.display = 'none';
            }
        }
	}
}
async function AJsave_custom_fields(hidePopup){
	let error_custom_fields = document.getElementById("error_custom_fields");
	error_custom_fields.innerHTML = '';
	if(document.querySelector("#field_name").value===''){
		error_custom_fields.innerHTML = Translate('Missing Field name.');;
		document.querySelector("#field_name").focus();
		return false;
	}
	else if(document.querySelector("#field_type").value===''){
		error_custom_fields.innerHTML = Translate('Missing Field type.');
		document.querySelector("#field_type").focus();
		return false;
	}
	else if(document.querySelector("#field_type").value==='DropDown'){
		let DropDown = 0;
		let DropDownObj = document.getElementsByName("DropDown[]");
		for(let l=0; l<DropDownObj.length; l++){
			let oneRow = DropDownObj[l].value;
			if(oneRow !==''){DropDown++;}
		}
		if(DropDown===0){
			error_custom_fields.innerHTML = Translate('Missing Dropdown Option.');
			DropDownObj[0].focus();
			return false;
		}
	}
	
	let submitBtn = document.querySelector(".btnmodel")
	btnEnableDisable(submitBtn,Translate('Saving'),true);
	
	const jsonData = serialize("#frmcustom_fields");
	const url = '/Settings/AJsave_custom_fields';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){	
        btnEnableDisable(submitBtn,Translate('Save'),false);	
        if(data.savemsg !=='error'){
            hidePopup();
            location.reload();
        }
        else if(data.returnStr=='errorOnAdding'){
            error_custom_fields.innerHTML = Translate('Error occured while adding new custom field! Please try again.');
        }  
        else if(data.returnStr=='Name_Already_Exist'){
            error_custom_fields.innerHTML = Translate('Duplicate custom field found.');
        }  
        else{
            error_custom_fields.innerHTML = Translate('No changes / Error occurred while updating data! Please try again.');
        }
    }
	return false;
}
function addDropDownOptions(){
    let DropDownObj, li;
	if(document.querySelector("#field_type").value==='DropDown'){
		let parametersHTML = document.createDocumentFragment() ;
		let newRow = true;
		if(document.getElementsByClassName("DropDown").length>0){
			DropDownObj = document.getElementsByName("DropDown[]");
			for(let l=0; l<DropDownObj.length; l++){
				let oneRow = DropDownObj[l].value;
				if(oneRow ===''){
						li = cTag('li');
						li.appendChild(cTag('input',{ "type":"text", "class":"form-control DropDown", 'style': "margin-bottom: 10px;", "name":"DropDown[]", "value":`${oneRow}`, "maxlength":"50" }));
					parametersHTML.appendChild(li);
					newRow = false;
				}
				else{
						li = cTag('li');
						li.appendChild(cTag('input',{ "type":"text", "class":"form-control DropDown", 'style': "margin-bottom: 10px;", "name":"DropDown[]", "value":`${oneRow}`, "maxlength":"50" }));
							let a = cTag('a',{ "class":"removeicon", "href":"javascript:void(0);", "title":`${Translate('Remove this row')}` });
							a.appendChild(cTag('img',{ "align":"absmiddle", "alt":`${Translate('Remove this row')}`, "title":`${Translate('Remove this row')}`, "src":"/assets/images/cross-on-white.gif" }));
						li.appendChild(a);
					parametersHTML.appendChild(li);
				}
			}
		}
		
		if(newRow){
				li = cTag('li');
				li.appendChild(cTag('input',{ "type":"text", "class":"form-control DropDown", 'style': "margin-bottom: 10px;", "name":"DropDown[]", "value":"", "maxlength":"50" }));
			parametersHTML.appendChild(li);
		}
		let DropDownOptions = document.querySelector("#DropDownOptions");
		DropDownOptions.innerHTML = '';
		DropDownOptions.appendChild(parametersHTML);
		DropDownObj = document.getElementsByClassName("DropDown");
		DropDownObj[DropDownObj.length-1].focus();
	}
	document.querySelectorAll('.removeicon').forEach(item=>{
		item.addEventListener('click',function(){
			if(document.querySelector("ul#DropDownOptions").childElementCount>1){
				this.parentNode.remove();
			}
			else{
				alert_dialog(Translate('Remove Dropdown Option'), Translate('You could not remove all Dropdown options.'), Translate('Ok'));
			}
		})
	})
}
async function AJorderup_custom_fields(order_val, custom_fields_id, precustom_fields_id){
	const jsonData = {"order_val":order_val, "custom_fields_id":custom_fields_id, "precustom_fields_id":precustom_fields_id};
	const url = '/Settings/AJorderup_custom_fields';
	
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        location.reload();			
    }
}
function setCustomFieldsTableRows(tableData, tdAttributes){
    const tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//=======Create TBody TR Column=======//
    let tr, td;
	if(tableData.length>0){		
        tableData.forEach((item,itemIndex)=>{
            let redirectUri = '/Settings/custom_fields';
            if(item[1] === 'devices') redirectUri = '/Settings/devices_custom_fields';
            else if(item[1] === 'product') redirectUri = '/Settings/products_custom_fields';
            else if(item[1] === 'customers') redirectUri = '/Settings/customers_custom_fields';
            else if(item[1] === 'repairs') redirectUri = '/Settings/repairs_custom_fields';
            
            tr = cTag('tr');
                td = cTag('td',{'data-title':Translate('Order'), 'align':'left'});
                td.innerHTML = itemIndex+1;
                if(itemIndex>0){
                    let aTag = cTag('a',{ 'style': "float: right;", "href":"javascript:void(0);", "click":()=>AJorderup_custom_fields(item[2], item[0], item[3]), "title":Translate('Edit/View') });
                    aTag.appendChild(cTag('i',{ "class":"fa fa-arrow-up" }));
                    td.appendChild(aTag);
                }
            tr.appendChild(td);
            for(let indx=4;indx<7;indx++){
                td = cTag('td',tdAttributes[indx-3]);
                    const aTag = cTag('a',{'class':"anchorfulllink", 'click':()=>AJgetPopup_custom_fields(item[1], item[0]), 'title':Translate('Edit/View')});
                    aTag.innerHTML = item[indx];
                td.appendChild(aTag);
                tr.appendChild(td);
            }
                td = cTag('td',tdAttributes[4]);
                td.appendChild(cTag('i',{ 'class':`fa fa-remove`, 'style':`cursor: pointer; color: #F00;`,'data-toggle':`tooltip`,'click':()=>AJremove_tableRow('custom_fields', item[0], 'Custom Field', redirectUri),'data-original-title':Translate('Remove') }))
			tr.appendChild(td);
            tbody.appendChild(tr);
        })
	}
	else{
			tr = cTag('tr');
				td = cTag('td',{ "colspan":"5"});
				td.innerHTML = '';
			tr.appendChild(td);
		tbody.appendChild(tr);		
	}
}
function setFormFieldsTableRows(tableData, tdAttributes){
    const tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//=======Create TBody TR Column=======//
    let tableRow1, tdCol;
	if(tableData.length>0){		
        tableData.forEach((item,itemIndex)=>{            
            tableRow1 = cTag('tr');
                tdCol = cTag('td',{'data-title':Translate('Order'), 'align':'left'});
                tdCol.innerHTML = itemIndex+1;
                if(itemIndex>0){
                    if(item[2]===item[1]){
                        let aTag = cTag('a',{ 'style': "float: right;", "href":"javascript:void(0);", "click":()=>AJorderup_forms_field(item[0], item[1]+1, item[2]), "title":Translate('Edit/View') });
                        aTag.appendChild(cTag('i',{ "class":"fa fa-arrow-up" }));
                        tdCol.appendChild(aTag);
                    }
                    else{
                        let aTag2 = cTag('a',{ 'style': "float: right;", "href":"javascript:void(0);", "click":()=>AJorderup_forms_field(item[0], item[1], item[2]), "title":Translate('Edit/View') });
                        aTag2.appendChild(cTag('i',{ "class":"fa fa-arrow-up" }));
                        tdCol.appendChild(aTag2);
                    }
                }
            tableRow1.appendChild(tdCol);
            for(let indx=3;indx<6;indx++){
                tdCol = cTag('td',tdAttributes[indx-2]);
                    const aTag = cTag('a',{'class':"anchorfulllink", 'click':()=>AJgetPopup_forms_field(item[0], item[1]), 'title':Translate('Edit/View')});
                    aTag.innerHTML = item[indx];
                tdCol.appendChild(aTag);
                tableRow1.appendChild(tdCol);
            }
                tdCol = cTag('td',tdAttributes[4]);
                tdCol.appendChild(cTag('i',{ 'class':`fa fa-remove`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'click':()=>AJremove_tableRow('forms', `${item[0]}||${item[1]}`, 'Form Field', ''),'data-original-title':Translate('Remove') }))
            tableRow1.appendChild(tdCol);
            tbody.appendChild(tableRow1);
        })
	}
	else{
            tableRow1 = cTag('tr');
				tdCol = cTag('td',{ "colspan":"5"});
				tdCol.innerHTML = '';
            tableRow1.appendChild(tdCol);
		tbody.appendChild(tableRow1);
	}
}
function invoice_FormRow(invoiceLabel,id,placeholder,removeLogoCBF,changeLogoPlacementCBF){
    const form = document.createDocumentFragment();
    let formGroupRow, label, th, textarea;
    if(segment2 ==='invoices_general'|| segment2 ==='ordersPrint'){
        formGroupRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
            let printTypeTitle = cTag('div',{ "class":"columnSM2" });
                let printLabel = cTag('label',{ "for":"default_invoice_printer1" });
                printLabel.innerHTML = Translate('Default print type')+' :';
            printTypeTitle.appendChild(printLabel);
        formGroupRow.appendChild(printTypeTitle);
            let printValue = cTag('div',{ "class":"columnSM9" });
                let printValueRow = cTag('div',{ "class":"flexSpaBetRow" });
                [
                    {value:'Large',label:Translate('Full Page')},
                    {value:'Small',label:Translate('Thermal')},
                    {value:'Email',label:Translate('Email')},
                    {value:'No Receipt',label:Translate('No Receipt')},
                ].forEach((item,indx)=>{
                        let defaultDiv1 = cTag('div',{ "class":"columnSM3" });
                            let defaultLabel = cTag('label');
                            defaultLabel.appendChild(cTag('input',{ "type":"radio","value":item.value,"id":`default_invoice_printer${indx+1}`,"name":"default_invoice_printer" }));
                            defaultLabel.append(' '+item.label);
                        defaultDiv1.appendChild(defaultLabel);
                    printValueRow.appendChild(defaultDiv1);
                })
            printValue.appendChild(printValueRow);
        formGroupRow.appendChild(printValue);
        form.appendChild(formGroupRow);
    }        
        formGroupRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
            let invoiceLabelTitle = cTag('div',{ "class":"columnSM12" });
                let invoiceLabelHeader = cTag('h4',{ "class":"borderbottom" });
                invoiceLabelHeader.innerHTML = invoiceLabel;
            invoiceLabelTitle.appendChild(invoiceLabelHeader);
        formGroupRow.appendChild(invoiceLabelTitle);
    form.appendChild(formGroupRow);
    
    [
        {label:Translate('Logo Size'),id:'logo_size',options:{'Small Logo':Translate('Small Logo'),'Large Logo':Translate('Large Logo')},cbfOnChange:removeLogoCBF,title:true},
        {label:Translate('Logo Placement'),id:'logo_placement',options:{'Left':Translate('Left'),'Center':Translate('Center')},cbfOnChange:changeLogoPlacementCBF,title:false},
    ].forEach(item=>{
        formGroupRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
            let itemColumn = cTag('div',{ "class":"columnSM2" });
                label = cTag('label',{ "for":item.id });
                label.innerHTML = item.label+' :';
            itemColumn.appendChild(label);
        formGroupRow.appendChild(itemColumn);
            let itemDropDown = cTag('div',{ "class":"columnSM2" });
                let selectItem = cTag('select',{ "name":item.id,"id":item.id,"class":"form-control","change":item.cbfOnChange });
                setOptions(selectItem, item.options, 1, 0);
            itemDropDown.appendChild(selectItem);
            itemDropDown.appendChild(cTag('input',{ "type":"hidden","name":"old"+item.id,"id":"old"+item.id }));
        formGroupRow.appendChild(itemDropDown);
        if(item.title){
            let titleDiv = cTag('div',{ "class":"columnSM4" });
            titleDiv.appendChild(cTag('input',{ "maxlength":"20","type":"text","id":"title","name":"title","class":"form-control" }));
        formGroupRow.appendChild(titleDiv);
        }
            let emptyDiv = cTag('div',{ "class":"columnSM4" });
            emptyDiv.innerHTML = ' ';
        formGroupRow.appendChild(emptyDiv);
    form.appendChild(formGroupRow);
    })
        formGroupRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
        if(segment2 ==='invoices_general'|| segment2 ==='ordersPrint'){
                let messageDiv = cTag('div',{ "class":"columnSM12" });
                    textarea = cTag('textarea',{ "id":"invoice_message_above","rows":"2","name":"invoice_message_above","placeholder":Translate('Invoice Top Message'),"class":"form-control" });
                    textarea.addEventListener('blur',sanitizer);
                messageDiv.appendChild(textarea);
            formGroupRow.appendChild(messageDiv);
        }
            let invoiceColumn1 = cTag('div',{ "class":"columnSM12" });
            invoiceColumn1.appendChild(cTag('br'));
                const table = cTag('table',{ "class":"table table-bordered" });
                    const thead = cTag('thead');
                        let tableHeadRow = cTag('tr');
                    [
                        {width:'5%',label:'#'},
                        {label:Translate('Description')},
                        {width:'12%',label:Translate('QTY')},
                        {width:'15%',label:Translate('Unit Price')},
                        {width:'12%',label:Translate('Total')},
                    ].forEach(item=>{                                        
                        if(item.width) th = cTag('th',{ "width":item.width, 'style': "text-align: right;" });
                        else th = cTag('th');
                            th.innerHTML = item.label;
                        tableHeadRow.appendChild(th);
                    })
                    thead.appendChild(tableHeadRow);
                table.appendChild(thead);
                const tbody = cTag('tbody',{ "id":"invoice_entry_holder" });
                    let tableRow1 = cTag('tr');
                        let thCol5 = cTag('th',{ "colspan":"5", 'style': "text-align: right;" });
                            let zeroLabel = cTag('label',{ "class":"cursor","for":"print_price_zero" });
                            zeroLabel.append(Translate('Print products with price of zero')+'   ');
                            zeroLabel.appendChild(cTag('input',{ "type":"checkbox","value":"1","id":"print_price_zero","name":"print_price_zero" }));
                        thCol5.appendChild(zeroLabel);
                    tableRow1.appendChild(thCol5);
                tbody.appendChild(tableRow1);
                table.appendChild(tbody);
            invoiceColumn1.appendChild(table);
        formGroupRow.appendChild(invoiceColumn1);
            let placeholderColumn = cTag('div',{ "class":"columnSM12" });
                textarea = cTag('textarea',{ "id":id,"rows":"3","name":id,"placeholder":placeholder,"class":"form-control" });
                textarea.addEventListener('blur',sanitizer);
            placeholderColumn.appendChild(textarea);
        formGroupRow.appendChild(placeholderColumn);
            let publicNoteColumn = cTag('div',{ "class":"columnSM12" });
            publicNoteColumn.appendChild(cTag('br'));
                let notesLabel = cTag('label',{ "class":"cursor","for":"notes" });
                notesLabel.append(Translate('Include public notes')+'   ');
                notesLabel.appendChild(cTag('input',{ "type":"checkbox","value":"1","id":"notes","name":"notes" }));
            publicNoteColumn.appendChild(notesLabel);
        formGroupRow.appendChild(publicNoteColumn);
    form.appendChild(formGroupRow);
    
        formGroupRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
            let buttonNames = cTag('div',{ "class":"columnXS12","align":"center" });
            buttonNames.appendChild(cTag('input',{ "type":"hidden","name":"variables_id","id":"variables_id" }));
            buttonNames.appendChild(cTag('input',{ "class":"btn completeButton","name":"submit","id":"submit","type":"submit" }));
        formGroupRow.appendChild(buttonNames);
    form.appendChild(formGroupRow);
    return form;
}

//==============Accounts Setup==================
function myInfo(){
    const loginInfoFields = [
        { attributes:{type: 'email', maxlength: '50',name: 'user_email', id: 'user_email', class:'form-control', required: true}, label: Translate('Email'), errormsg: true },
        { attributes:{type: 'password', maxlength: '32', name: 'user_password', id: 'user_password', class:'form-control',autocomplete:'new-password'}, label: Translate('Password'), errormsg: true },
    ]
    const basicInfoFields = [
        { attributes:{type: 'text', maxlength: '12',id: 'user_first_name', name: 'user_first_name', class:'form-control', required: true}, label: Translate('First Name') },
        { attributes:{type: 'text', maxlength: '17',id: 'user_last_name', required: true, name: 'user_last_name', class:'form-control'}, label: Translate('Last Name') },
        { attributes:{type: 'select', id: 'minute_to_logout'}, label: Translate('Log me out after'), options:[10,20,30,45,60], unit: 'Minutes'},
    ]
    const updateField = { attributes:{type: 'submit', id: 'submit', name: 'submit', class: 'btn completeButton', value: Translate('Update')}}
    function formGroupCreator(info){
        const divFormGroup = cTag('div',{ 'class':"flexStartRow", 'style': "padding-bottom: 15px; align-items: center;" });
            const myInfoColumn = cTag('div',{ 'class':"columnXS5 columnSM4 columnMD2", 'style': "text-align: right;" });
            if(info.label){
                let label = cTag('label',{ 'for':info.attributes.id });
                label.append(info.label);
                if(info.attributes && info.attributes.required){
                    let requiredField = cTag('span',{ 'class':"required" });
                    requiredField.innerHTML = '*';
                    label.appendChild(requiredField);
                }                
                label.append(' :');
            myInfoColumn.appendChild(label);
            }
        divFormGroup.appendChild(myInfoColumn);
            const myInfoColumnValue = cTag('div',{ 'class':"columnXS7 columnSM8 columnMD6" });
            if(info.attributes.type === 'select'){
                let selectField = cTag('select',{ 'id':info.attributes.id,'name':info.attributes.id,'class':"form-control" });
                info.options.forEach(item=>{
                    let optionField = cTag('option',{ 'value':item });
                    optionField.innerHTML = `${item} ${info.unit}`;
                    selectField.appendChild(optionField);
                })
            myInfoColumnValue.appendChild(selectField);
            }
            else{                   
                let inputField = cTag('input');
                for(let key in info.attributes){
                    inputField.setAttribute(key,info.attributes[key]);
                }
                myInfoColumnValue.appendChild(inputField);
            }
    
            if(info.errormsg){
                myInfoColumnValue.appendChild(cTag('span',{ 'id':`errmsg_${info.attributes.id}`,'class':"errormsg" }));
            }            
        divFormGroup.appendChild(myInfoColumnValue);
        return divFormGroup;
    }
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('My Information')));
        const myInfoRow = cTag('div',{ class: "flexSpaBetRow" });
        myInfoRow.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;";
            if(OS!=='unknown') callOutDivStyle += " padding-left: 10px; padding-right: 10px";
            let myInfoContainer = cTag('div',{ 'class':'columnMD10 columnSM9', 'style': "margin: 0;" });
                let callOutDiv = cTag('div',{ 'class':"innerContainer bs-callout-info",'style': callOutDivStyle});
                    let myInfoForm = cTag('form',{ 'name':"frmmyInfo",'id':"frmmyInfo",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                    myInfoForm.addEventListener('submit',check_myInfo);
                        const logInTitle = cTag('h4',{ 'class':"borderbottom", 'style': "font-size: 18px;" });
                        logInTitle.innerHTML = Translate('Login Information');
                    myInfoForm.appendChild(logInTitle);
                    loginInfoFields.forEach(item=>{
                        myInfoForm.appendChild(formGroupCreator(item));
                    })
                        const basicInfoTitle = cTag('h4',{ 'class':"borderbottom", 'style': "font-size: 18px;" });
                        basicInfoTitle.innerHTML = Translate('Basic Information');
                    myInfoForm.appendChild(basicInfoTitle);                 
                    basicInfoFields.forEach(item=>{
                        myInfoForm.appendChild(formGroupCreator(item));
                    })
                    myInfoForm.appendChild(formGroupCreator(updateField))                    
                callOutDiv.appendChild(myInfoForm);
            myInfoContainer.appendChild(callOutDiv)
        myInfoRow.appendChild(myInfoContainer);
    Dashboard.appendChild(myInfoRow);
    AJ_myInfo_MoreInfo();
}
async function AJ_myInfo_MoreInfo(){
    const url = '/'+segment1+'/AJ_myInfo_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        document.querySelector('#user_email').value = data.user_email;
        document.querySelector('#user_first_name').value = data.user_first_name;
        document.querySelector('#user_last_name').value = data.user_last_name;
        document.querySelector('#minute_to_logout').value = data.minute_to_logout;
    }
}
async function check_myInfo(event){
    event.preventDefault();
	let oField = document.frmmyInfo.user_password;
	let oElement = document.getElementById('errmsg_user_password');
	oElement.innerHTML = "";
	let user_passwordstrlength = oField.value.length;
	if(oField.value !== ""){
		if(user_passwordstrlength<5){
			oElement.innerHTML = Translate('Password should be greater than 4 letter');
			oField.focus();
			return(false);
		}
	}
	
	let submitBtn = document.querySelector("#submit");
    let butVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);    
    const jsonData = serialize('#frmmyInfo');
    const url = '/'+segment1+'/AJsave_myInfo';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
            showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('This email address already exist! Please try again with different email address.'));
		}
        btnEnableDisable(submitBtn, butVal,false);
    }
	return false;
}
//-------------------setup user-------------------
function user(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
   
    const newUserFields = [
        {attributes:{type:"text", name:"user_first_name", id:"user_first_name", class:"form-control", maxlength:"12", size:"12", required:true}, label:Translate('First Name')},
        {attributes:{type:"text", name:"user_last_name", id:"user_last_name", class:"form-control", maxlength:"17", size:"17", required: true}, label:Translate('Last Name')},
        {attributes:{type:"email", name:"user_email", id:"user_email", class:"form-control", maxlength:"50", required:true, autocomplete:'off', focus:()=>{if(this.value===' ') this.value=''}, blur:()=>{if(this.value==='') this.value=' '}}, label:Translate('Email'), addInfo:Translate('Enter the users email address to allow them to login. A temporary password will be emailed to the new user.')},
        {attributes:{type:"checkbox", name:"no_restrict_ip", id:"no_restrict_ip", value:1, class:'cursor'}, label:Translate('No Restrict IP')}
        
    ]
    const updateFields = [
        {type:"hidden", name:"user_id", id:"user_id", value:"0"},
        {type:"button", name:"reset", id:"reset", 'click':resetForm_user, value:"Cancel", class:"btn defaultButton", 'style': "display:none; margin-right: 10px;"},
        {type:"button", name:"unarchive", id:"unarchive", value:Translate('Unarchive'), class:"btn bgcoolblue",style:'display:none; margin-right: 10px;','click':unarchiveUserData},
        {type:"button", name:"archive", id:"archive", value:Translate('Archive'), class:"btn archiveButton",style:'display:none;','click':()=>AJarchive_tableRow('user', 'user_id', document.querySelector("#user_id").value, document.frmuser.user_first_name.value+' '+document.frmuser.user_last_name.value, 'user_publish', '')},
        {type:"submit", class:"btn saveButton", id:"submit", 'style': "margin-left: 10px;", value:Translate('Save')},
    ]
    
    let divFormGroup, label, list_filters, newUserColumn, requiredField, inputField;
    function formGroupCreator(info){
        divFormGroup = cTag('div',{ 'class':"flexStartRow", 'style': "align-items: center;" });
        if(info.addInfo){
            newUserColumn = cTag('div',{ 'class':"columnSM12", 'style': "padding-left: 20px;" });
                let pTag = cTag('p');
                pTag.innerHTML = info.addInfo;
            newUserColumn.appendChild(pTag);
        divFormGroup.appendChild(newUserColumn);
        }
        if(info.label){
            newUserColumn = cTag('div',{ 'class':"columnXS5 columnSM3", 'style': "text-align: right; padding-left: 0;" });
                label = cTag('label',{ 'for':info.attributes.id });
                label.append(info.label);
                if(info.attributes && info.attributes.required){
                    requiredField = cTag('span',{ 'class':"required" });
                    requiredField.innerHTML = '*';
                label.appendChild(requiredField);
                }                
                label.append(' :');
            newUserColumn.appendChild(label);
            divFormGroup.appendChild(newUserColumn);
        }
            newUserColumn = cTag('div',{ 'class':"columnXS7 columnSM9" });
                if(Array.isArray(info)){
                    info.forEach(attributes=>{
                        newUserColumn.appendChild(cTag('input',attributes));
                    })
                }
                else{
                    newUserColumn.appendChild(cTag('input',info.attributes));
                }       
        divFormGroup.appendChild(newUserColumn);
        return divFormGroup;
    }
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Setup Users')));
        const userContainer = cTag('div',{ class: "flexSpaBetRow" });
        userContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top:0; background:#FFF;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let userColumn = cTag('div',{ 'class':"columnMD10 columnSM9", 'style': "margin: 0;" });
                let callOutDiv = cTag('div',{ 'class':"innerContainer bs-callout-info", 'style': callOutDivStyle });
                [
                    { name: 'pageURI', value: segment1+'/'+segment2},
                    { name: 'page', value: page },
                    { name: 'rowHeight', value: '34' },
                    { name: 'totalTableRows', value: 0 },
                ].forEach(field=>{
                    callOutDiv.appendChild(cTag('input',{'type':"hidden", 'name':field.name, 'id':field.name, 'value':field.value}));
                });
                               
                    const userRow = cTag('div',{ class: "flexSpaBetRow" });
                        const userListColumn = cTag('div',{ 'class':"columnXS12 columnMD6", 'style': "margin: 0; padding-right: 10px;" });
                        userListColumn.appendChild(addTitleSearchRow(Translate('Users List'),Translate('Search Users'),filter_Settings_user));
                            const divTable = cTag('div',{ 'class': 'flexSpaBetRow' });
                                let divTableColumn = cTag('div',{ 'class': 'columnXS12','style': 'position:relative;' });
                                    const listTable = cTag('table',{ 'class': 'table-bordered table-striped table-condensed cf listing' });
                                        const listHead = cTag('thead',{ 'class': 'cf' });
                                            const listHeadRow = cTag('tr');
                                                const thCol0 = cTag('th');
                                                thCol0.innerHTML= Translate('Users Name');
                                                const thCol1 = cTag('th',{ 'width': '50%' });
                                                thCol1.innerHTML= Translate('Email');
                                            listHeadRow.append(thCol0,thCol1);
                                        listHead.appendChild(listHeadRow);
                                    listTable.appendChild(listHead);
                                    listTable.appendChild(cTag('tbody',{ 'id': 'tableRows' }));
                                divTableColumn.appendChild(listTable);
                            divTable.appendChild(divTableColumn);
                        userListColumn.appendChild(divTable);
                        addPaginationRowFlex(userListColumn);
                    userRow.appendChild(userListColumn);
                        let divNewUser = cTag('div',{ 'class':"columnXS12 columnMD6", 'style': "margin: 0; padding-left: 10px;" });
                            const newUserHeader = cTag('h4',{ 'class':"borderbottom", 'style': "font-size: 18px;", 'id':"formtitle" });
                            newUserHeader.innerHTML = Translate('Add New User');
                        divNewUser.appendChild(newUserHeader);
                            const userForm = cTag('form',{ 'action':"#",'name':"frmuser",'id':"frmuser",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                            userForm.addEventListener('submit',AJsave_user);
                            newUserFields.forEach(item=>{
                                userForm.appendChild(formGroupCreator(item));
                            })
                                divFormGroup = cTag('div',{ 'class':"flexSpaBetRow" });
                                    const modulePermissionColumn = cTag('div',{ 'class':"columnSM12" });
                                        let permissionLabel = cTag('label',{ 'for':'user_roll[0]' });
                                        permissionLabel.append(Translate('Modules Permission'));
                                            requiredField = cTag('span',{ 'class':"required" });
                                            requiredField.innerHTML = '*';
                                        permissionLabel.appendChild(requiredField);
                                        permissionLabel.append(' :');
                                    modulePermissionColumn.appendChild(permissionLabel);
                                divFormGroup.appendChild(modulePermissionColumn);
                                    let accessColumn = cTag('div',{ 'class':"columnSM12 roundborder",'id':'divInfo' });
                                        const accessRow = cTag('div',{ class: "flexSpaBetRow" });
                                            const accessLabelColumn = cTag('div',{ 'class': "columnSM12", 'style': "font-size: 16px;", 'align':"left" });
                                                let accessLabel = cTag('label',{ 'style':"font-weight:normal;" });
                                                accessLabel.appendChild(cTag('input',{ 'type':"checkbox",'class':"full_access",'name':"user_roll[]",'click':checkUserRolls,'value':"Full-Access" }));
                                                accessLabel.append(' '+Translate('Full Access'));
                                            accessLabelColumn.appendChild(accessLabel);
                                        accessRow.appendChild(accessLabelColumn);
                                    accessColumn.appendChild(accessRow);
                                        const individualModuleRow = cTag('div',{ class: "flexSpaBetRow" });
                                            const individualModuleColumn = cTag('div',{ 'class':"columnSM12" });
                                                let individualModuleHeader = cTag('h4',{ 'class':"borderbottom", 'style': "font-size: 16px;", 'id':"formtitle" });
                                                individualModuleHeader.innerHTML = Translate('Individual Modules Permission')+':';
                                            individualModuleColumn.appendChild(individualModuleHeader);
                                        individualModuleRow.appendChild(individualModuleColumn);
                                    accessColumn.appendChild(individualModuleRow);
                                    accessColumn.appendChild(cTag('span',{ 'class':"error_msg",'id':"errmsg_user_roll" }));
                                divFormGroup.appendChild(accessColumn);
                            userForm.appendChild(divFormGroup);
                            userForm.appendChild(formGroupCreator(updateFields));
                        divNewUser.appendChild(userForm);
                    userRow.appendChild(divNewUser);
                callOutDiv.appendChild(userRow);
            userColumn.appendChild(callOutDiv);
        userContainer.appendChild(userColumn)
    Dashboard.appendChild(userContainer);
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
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
    addCustomeEventListener('filter',filter_Settings_user);
    addCustomeEventListener('loadTable',loadTableRows_Settings_user);
    addCustomeEventListener('reset',resetForm_user);
    AJ_user_MoreInfo();
}
async function AJ_user_MoreInfo(){
    const url = '/'+segment1+'/AJ_user_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let label;
        let divInfo = document.querySelector('#divInfo');
            const row = cTag('div',{ class: "flexSpaBetRow columnXS12" });
                let firstCol = cTag('div',{ 'class':'columnSM6' });
            row.appendChild(firstCol);
                let secondCol = cTag('div',{ 'class':'columnSM6' });
            row.appendChild(secondCol);
            let halfCount = Math.ceil(Object.keys(data.modules).length/2);
            let l = 0;
            for (const key in data.modules) {
                l++;
                let info = {label: key, name: data.modules[key][0], id: data.modules[key][1]}
                if(l<halfCount) moduleCreator(firstCol,info);
                else moduleCreator(secondCol,info);
            }
            function moduleCreator(parent,info){
                let divModule = cTag('div',{ 'class':'columnSM12 Individual_Modules' });
                    label = cTag('label',{ 'style':'font-weight:normal' });
                    label.appendChild(cTag('input',{ 'type':'checkbox','class':'user_roll','name':'user_roll[]','click':checkUserRolls,'value':info.id }));
                    label.append(' '+Translate(info.label));
                divModule.appendChild(label);
                if([1,2,7].includes(info.id)){
                    divModule.appendChild(subModuleCreator("cnccp",' '+Translate('Can not change cart price')));
                    divModule.appendChild(subModuleCreator('cnanp',' '+Translate('Can not add new product')));
                    if(info.id===2) divModule.appendChild(subModuleCreator('cnanbm',' '+Translate('Can not add new Brand Model')));
                    if([2,7].includes(info.id)) divModule.appendChild(subModuleCreator('cncl',' '+Translate('Can not Cancel')));
                }
                else if(info.id===3){
                    divModule.appendChild(subModuleCreator('cnr',' '+Translate('Can not refund')));
                }
                else if(info.id===4){
                    divModule.appendChild(subModuleCreator('cnac',' '+Translate('Can not Archive Customer')));
                    divModule.appendChild(subModuleCreator('cncrm',' '+Translate('Can not CRM')));
                }
                else if(info.id===5){
                    divModule.appendChild(subModuleCreator('cnc',' '+Translate('Can not Create')));
                    divModule.appendChild(subModuleCreator('cne',' '+Translate('Can not Edit')));
                    divModule.appendChild(subModuleCreator('cnai',' '+Translate('Can not Adjust Inventory')));
                    divModule.appendChild(subModuleCreator('cnap',' '+Translate('Can not Archive Product')));
                    divModule.appendChild(subModuleCreator('cnain',' '+Translate('Can not Add Inventory')));
                }
                else if(info.id===6){
                    divModule.appendChild(subModuleCreator('cncpo',' '+Translate('Can not Complete PO')));
                }
                else if(info.id===8){
                    divModule.appendChild(subModuleCreator('cnrfi',' '+Translate('Can not Remove from Inventory')));
                }
                else if(info.id===9){
                    divModule.appendChild(subModuleCreator('cncst',' '+Translate('Can not Complete Stock Take')));
                }
                else if(info.id===14){
                    divModule.appendChild(subModuleCreator('cncpt',' '+Translate('Can not Change Payment Type')));
                }
                else if(info.id===25){
                    divModule.appendChild(subModuleCreator('cnas',' '+Translate('Can not Archive Supplier')));
                }
                function subModuleCreator(value,lbl){
                    let div = cTag('div',{ style:'display:none; font-size: 12px;','class':`errormsg subModule subModule${info.id}`,'align':"left" });
                        label = cTag('label',{ 'style':"font-weight:normal;padding-left:30px;" });
                        label.appendChild(cTag('input',{ 'type':"checkbox",'class':"user_roll",'name':`${info.name}[]`,'click':checkUserRolls,'value':value }));
                        label.append(lbl);
                    div.appendChild(label);
                    return div
                }
            parent.appendChild(divModule);
        }            
        divInfo.appendChild(row);
        filter_Settings_user();
    }
}
async function filter_Settings_user(firstLoad = false){
    let page = 1;
    if(firstLoad){
        page = parseInt(document.getElementById("page").value);
        if(isNaN(page) || page===0){
            page = 1;
        }
    }
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;    
    const url = '/'+segment1+'/AJgetPage_user/filter';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Users Name'), 'align':'left'}, {'datatitle':Translate('Email'), 'align':'left'}], 'user');
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}
async function loadTableRows_Settings_user(){
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
    const url = '/'+segment1+'/AJgetPage_user';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Users Name'), 'align':'left'}, {'datatitle':Translate('Email'), 'align':'left'}], 'user');
        onClickPagination();
    }	
}
function addTitleSearchRow(headerLabel,searchLabel,filterCBF){
    const div = cTag('div',{ 'class': 'flexSpaBetRow' });
        const titleName = cTag('div',{ 'class':'columnXS12' });
            let headerTitle = cTag('h2');
            headerTitle.innerHTML = headerLabel+' ';
            headerTitle.appendChild(cTag('i',{ 'class':'fa fa-info-circle', 'style': "font-size: 16px;", 'data-toggle':'tooltip','data-placement':'bottom','title':'','data-original-title':headerLabel }));
        titleName.appendChild(headerTitle);
    div.appendChild(titleName);
        const searchColumn = cTag('div',{ 'class':'flexEndRow columnXS12' });
            const filterDiv = cTag('div', {class: "columnXS6"});
                const filterType = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type"});
                filterType.addEventListener('change', filterCBF);
                setOptions(filterType, {'All':Translate('All')+' '+Translate('User'), 'Archived':Translate('Archived')+' '+Translate('User')}, 1, 0); 
            filterDiv.appendChild(filterType);       
        searchColumn.appendChild(filterDiv);
            let searchInGroup = cTag('div',{ 'class':'columnXS6 input-group' });
            searchInGroup.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filterCBF), 'type':'text','placeholder':searchLabel,'value':'','id':'keyword_search','name':'keyword_search','class':'form-control','maxlength':'50' }));
                let searchSpan = cTag('span',{ 'class':'input-group-addon cursor','click':filterCBF,'data-toggle':'tooltip','data-placement':'bottom','title':'','data-original-title':searchLabel });
                searchSpan.appendChild(cTag('i',{ 'class':'fa fa-search' }));                                    
            searchInGroup.appendChild(searchSpan);
        searchColumn.appendChild(searchInGroup);
    div.appendChild(searchColumn);
    return div;
}
function checkUserRolls(){
	let dparray = document.getElementsByName("user_roll[]");
	
	if(dparray[0].checked === true){
		document.querySelectorAll(".user_roll").forEach(item=>{
            item.checked = false;
            item.disabled = true;
        })
		
		if(!document.querySelectorAll(".Individual_Modules.txtdisabled").length){
            document.querySelectorAll(".Individual_Modules").forEach(item=>{
                item.classList.add('txtdisabled');
            })
		}
	}
	else{
		document.querySelectorAll(".user_roll").forEach(item=>{
            item.disabled = false;
        })
		
        if(document.querySelectorAll(".Individual_Modules.txtdisabled").length){
            document.querySelectorAll(".Individual_Modules").forEach(item=>{
                item.classList.remove('txtdisabled');
            })
		}
		let user_rollYN = '';
		for(let d=1; d<dparray.length && user_rollYN===''; d++){
			if(dparray[d].checked === true){
				if(document.querySelectorAll('.subModule'+dparray[d].value).length){
					document.querySelectorAll('.subModule'+dparray[d].value).forEach(item=>{
                        if(item.style.display === 'none'){
                            item.style.display = '';
                        }
                    })
				}
			}
			else if(document.querySelectorAll('.subModule'+dparray[d].value).length){
                document.querySelectorAll('.subModule'+dparray[d].value).forEach(item=>{
                    if(item.style.display !== 'none'){
                        item.style.display = 'none';
                    }
                })
			}
		}
		
		if(user_rollYN !==''){
			document.querySelector(".full_access").checked = false;
		}
	}	
}
async function AJsave_user(event){
    event.preventDefault();
	let checked = document.querySelectorAll("input[type=checkbox]:checked").length;
	let oElement = document.getElementById('errmsg_user_roll');
	oElement.innerHTML = "";
	if(!checked) {
		oElement.innerHTML = Translate('You must check at least one checkbox.');
		return false;
	}
	
	let submitBtn = document.querySelector("#submit");
    let butVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    
    const jsonData = serialize("#frmuser");
    const url = '/'+segment1+'/AJsave_user';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg ==='' && (data.returnStr==='Add' || data.returnStr==='Update')){
			resetForm_user();
			if(data.returnStr==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			filter_Settings_user();	
			checkUserRolls();
		}
		else{
			resetForm_user();
			filter_Settings_user();	
			if(data.savemsg === 'emailExist') alert_dialog(Translate('Alert message'), Translate('This email address already exists.'), Translate('Ok'));			
			if(data.savemsg === 'emailExistInArchive') alert_dialog(Translate('Alert message'), Translate('This email address is already in Archive.'), Translate('Ok'));			
			if(data.savemsg === 'userExist') alert_dialog(Translate('Alert message'), Translate('This user is already exist! Please try again with different user.'), Translate('Ok'));			
			if(data.savemsg === 'notSendMail') alert_dialog(Translate('Alert message'), Translate('Sorry! Could not send mail. Try again later.'), Translate('Ok'));			
			if(data.savemsg === 'errorNewUser') alert_dialog(Translate('Alert message'), Translate('Error occured while adding new user! Please try again.'), Translate('Ok'));			
		}
		btnEnableDisable(submitBtn, butVal, false);
    }	
	return false;
}
function resetForm_user(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New User');
	document.querySelector(".full_access").checked = false;
	document.querySelectorAll(".user_roll").forEach(item=>{
        item.checked = false;
        item.disabled = false;
    })
	document.querySelector("#user_id").value = 0;
	document.querySelector("#user_first_name").value = '';
	document.querySelector("#user_last_name").value = '';
	document.querySelector("#user_email").value = '';
    document.querySelector("#submit").style.display = '';
    document.querySelector("#reset").style.display = 'none';   
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
}
function setUserRoll(user_roll){
	if(user_roll.length<=2){
		document.querySelector(".full_access").checked = true;
		document.querySelectorAll(".user_roll").forEach(item=>{
            item.checked = false;
            item.disabled = true;
        })
	}
	else{
		document.querySelector(".full_access").checked = false;
		document.querySelectorAll(".user_roll").forEach(item=>{
            item.disabled = false;
        })
		if(document.querySelectorAll("ul.List50Per li.Individual_Modules.txtdisabled").length){
			document.querySelectorAll("ul.List50Per li.Individual_Modules").forEach(item=>{
                item.classList.remove('txtdisabled');
            })
		}
		let user_rolleditarray = JSON.parse(user_roll);
		let user_rollarray = document.getElementsByName("user_roll[]");
		
		if(user_rollarray.length>0){
			for(let d=0; d<user_rollarray.length; d++){
				let moduleId = user_rollarray[d].value;
				let ck = 0;
                for(let index in user_rolleditarray){
                    if(index===moduleId){
						let valueArray = user_rolleditarray[index]
						user_rollarray[d].checked = true;
						ck = 1;
						if(valueArray.length && document.querySelectorAll('.subModule'+moduleId).length){
							document.querySelectorAll('.subModule'+moduleId).forEach(function(item) {
								let index2Obj = item.querySelector("input");
								let index2Val = index2Obj.value;
								let ck2 = 0;
                                valueArray.forEach((value2)=>{
                                    if(value2===index2Val){
										index2Obj.checked = true;
										ck2 = 1;
									}
                                })
								if(ck2===0){
									index2Obj.checked = false;
								}
							});
						}
					}
                }
				
				if(ck===0){
					user_rollarray[d].checked = false;
				}
			}
		}				
	}
	checkUserRolls();
}
function unarchiveUserData(){
    confirm_dialog(Translate('Product Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{
        unarchiveData(null,{tablename:'user', tableidvalue:document.getElementById('user_id').value, publishname:'user_publish'},afterUnarchive);
        
        function afterUnarchive(){
            hidePopup();
            document.getElementById('sdata_type').value = 'All';
            triggerEvent('filter');
            triggerEvent('reset');
        }
    });
}
//-------------------PO setup-------------------
function po_setup(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('PO Setup')));
        const poSetUpContainer = cTag('div',{ class: "flexSpaBetRow" });
        poSetUpContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let poSetUpColumn = cTag('div',{ 'class':"columnMD10 columnSM9", 'style': "margin: 0;" });
                let callOutDiv = cTag('div',{ 'class':"innerContainer", 'style': callOutDivStyle });
                    const poSetUpForm = cTag('form',{ 'name':"frmpo_setup",'id':"frmpo_setup",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                    poSetUpForm.addEventListener('submit',AJsave_po_setup);
                        const nextPoRow = cTag('div',{ 'class':"flexStartRow" });
                            let nextPoColumn = cTag('div',{ 'class':"columnXS6 columnSM3 columnMD2", 'style': "text-align: right;" });
                                const poLabel = cTag('label');
                                poLabel.innerHTML = Translate('Next PO Number')+' :';
                            nextPoColumn.appendChild(poLabel);
                        nextPoRow.appendChild(nextPoColumn);
                            let nextPoValue = cTag('div',{ 'class':"columnXS6 columnSM3 columnMD2" });
                            nextPoValue.appendChild(cTag('input',{ 'type':"hidden",'id':"nextpo_number",'name':"nextpo_number" }));
                            nextPoValue.appendChild(cTag('input',{ 'name':"nextponumber",'id':"nextponumber",'maxlength':"8",'blur':checkPoNumber,'class':"form-control" }));
                            nextPoValue.appendChild(cTag('span',{ 'id':"errmsg_nextponumber",'class':"errormsg" }));
                        nextPoRow.appendChild(nextPoValue);
                    poSetUpForm.appendChild(nextPoRow);
                        const poMessageRow = cTag('div',{ 'class':"flexStartRow" });
                            let poMessageColumn = cTag('div',{ 'class':"columnXS6 columnSM3 columnMD2", 'style': "text-align: right;" });
                                const messageLabel = cTag('label',{ 'for':"po_message" });
                                messageLabel.innerHTML = Translate('PO Message')+' :';
                            poMessageColumn.appendChild(messageLabel);
                        poMessageRow.appendChild(poMessageColumn);
                            let poMessageValue = cTag('div',{ 'class':"columnSM9 columnMD10" });
                                const textarea = cTag('textarea',{ 'id':"po_message",'rows':"6",'name':"po_message",'class':"form-control" });
                                textarea.addEventListener('blur',sanitizer);
                            poMessageValue.appendChild(textarea);
                        poMessageRow.appendChild(poMessageValue);
                    poSetUpForm.appendChild(poMessageRow);
                        const buttonName = cTag('div',{ 'class':"flexStartRow" });
                            let emptyColumn = cTag('div',{ 'class':"columnSM3 columnMD2"});
                            emptyColumn.innerHTML = ' ';
                        buttonName.appendChild(emptyColumn);
                            let buttonColumn = cTag('div',{ 'class':"columnSM9 columnMD10" });
                            buttonColumn.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'id':"variables_id" }));
                            buttonColumn.appendChild(cTag('input',{ 'class':"btn completeButton",'name':"submit",'id':"submit",'type':"submit" }));
                        buttonName.appendChild(buttonColumn);
                    poSetUpForm.appendChild(buttonName);
                callOutDiv.appendChild(poSetUpForm);
            poSetUpColumn.appendChild(callOutDiv);
        poSetUpContainer.appendChild(poSetUpColumn);
    Dashboard.appendChild(poSetUpContainer);
    AJ_po_setup_MoreInfo();
}
async function AJ_po_setup_MoreInfo(){
    const url = '/'+segment1+'/AJ_po_setup_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        document.querySelector('#nextpo_number').value = document.querySelector('#nextponumber').value = data.nextpo_number;
        document.querySelector('#po_message').value = data.po_message
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }            
        document.querySelector('#variables_id').value = data.variables_id
    }
}
function checkPoNumber(){
	let nextponumber = parseInt(document.getElementById('nextponumber').value);
	let nextpo_number = parseInt(document.getElementById('nextpo_number').value);
	if(nextpo_number==='' || isNaN(nextpo_number)){
		nextpo_number = 1;
		document.getElementById('nextpo_number').value = 1;
	}
	
	let errorid = document.getElementById("errmsg_nextponumber");
	
	if(nextponumber==='' || isNaN(nextponumber)){
		errorid.innerHTML = Translate('PO Number')+' '+Translate('is missing.');
		return false;
	}
	else if(nextponumber<nextpo_number){
		errorid.innerHTML = Translate('Sorry, you must enter a PO Number greater than')+' '+parseInt(nextpo_number-1);
		return false;
	}
	return true;
}
async function AJsave_po_setup(event){
    event.preventDefault();
	if(checkPoNumber()===false){
		document.getElementById('nextponumber').focus();
		return false;
	}
	let nextponumber = parseInt(document.getElementById('nextponumber').value);
	let nextpo_number = parseInt(document.getElementById('nextpo_number').value);
	if(nextpo_number==='' || isNaN(nextpo_number)){
		nextpo_number = 1;
		document.getElementById('nextpo_number').value = 1;
	}
	
	if(nextponumber==='' || isNaN(nextponumber)){
		document.getElementById('nextponumber').value = nextpo_number;
	}	
	
	if(nextponumber>nextpo_number){
		document.getElementById('nextpo_number').value = nextponumber;
	}
    let submitBtn = document.querySelector("#submit");
    let butVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    const jsonData = serialize('#frmpo_setup');
    const url = '/'+segment1+'/AJsave_po_setup';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
			if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
			if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing PO setup information! Please try again.'));		
		}
		btnEnableDisable(submitBtn, butVal, false);
    }
	return false;
}
//-------------------Barcode -------------------
function barcode_labels(){  
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Barcode Labels')));
        const barcodeContainer = cTag('div',{ class: "flexSpaBetRow" });
        barcodeContainer.appendChild(leftSideMenu());
            let barcodeColumn = cTag('div',{ 'class':"columnMD10 columnSM9", 'style': "margin: 0;" });
                    let barcodeForm = cTag('form',{ 'name':"frmBL",'id':"frmBL",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                    barcodeForm.addEventListener('submit',AJsave_barcode_labels);
                        let bsCallOut = cTag('div',{ 'class':"innerContainer",'style':"margin-top: 0; background: #fff;" });
                        bsCallOut.appendChild(cTag('div',{ id:'barcodeRow','class':"columnSM12 flexSpaBetRow" }));
                    barcodeForm.appendChild(bsCallOut);
            barcodeColumn.appendChild(barcodeForm);
        barcodeContainer.appendChild(barcodeColumn);
    Dashboard.appendChild(barcodeContainer);
    AJ_barcode_labels_MoreInfo();
}
async function AJ_barcode_labels_MoreInfo(){   
    const url = '/'+segment1+'/AJ_barcode_labels_MoreInfo';
    await fetchData(afterFetch,url,{});
    function afterFetch(data){    
        const barcodeFields = [
            {
                title:Translate('Product Label'),
                id:'productLabel',
                tagLists:[
                    { label:Translate('Company Name'), tag:'CompanyName' },
                    { label:Translate('Product Name'), tag:'ProductName' },
                    { label:Translate('Price'), tag:'Price' },
                    ...generateCustomTagLists(data.productLabel_customfields),
                    { label:Translate('Barcode'), tag:'Barcode' },
                ],
                defaultLabel:`{{ProductName}}\n{{Barcode}}`,
            },
            {
                title:Translate('Device Label'),
                id:'deviceLabel',
                tagLists:[
                    { label:Translate('Company Name'), tag:'CompanyName' },
                    { label:Translate('Product Name'), tag:'ProductName' },
                    { label:Translate('Price'), tag:'Price' },
                    ...generateCustomTagLists(data.devicesLabel_customfields),
                    { label:Translate('Barcode'), tag:'Barcode' },
                ],
                defaultLabel:`{{ProductName}}\n{{Barcode}}`,
            },
            {
                title:Translate('Repair Ticket Label'),
                id:'repairTicketLabel',
                tagLists:[
                    { label:Translate('First Name'), tag:'FirstName' },
                    { label:Translate('Last Name'), tag:'LastName' },
                    { label:Translate('Company'), tag:'Company' },
                    { label:Translate('Phone No.'), tag:'PhoneNo' },
                    { label:Translate('Ticket Number'), tag:'TicketNo' },
                    { label:Translate('Due Date'), tag:'DueDate' },
                    { label:Translate('Brand/Model'), tag:'BrandModel' },
                    { label:Translate('More Details'), tag:'MoreDeails' },
                    { label:Translate('IMEI/Serial No.'), tag:'ImeiSerial' },
                    { label:Translate('Problem'), tag:'Problem' },
                    { label:Translate('Password'), tag:'Password' },
                    ...generateCustomTagLists(data.repairsLabel_customfields),
                    { label:Translate('Barcode'), tag:'Barcode' },
                ],
                defaultLabel:`{{FirstName}} {{LastName}} {{DueDate}}\n{{BrandModel}}\n{{MoreDeails}}\n{{ImeiSerial}}\n{{Problem}}\n{{Barcode}}`,
            },
            {
                title:Translate('Repair Customer Label'),
                id:'repairCustomerLabel',
                tagLists:[
                    { label:Translate('First Name'), tag:'FirstName' },
                    { label:Translate('Last Name'), tag:'LastName' },
                    { label:Translate('Company'), tag:'Company' },
                    { label:Translate('Phone No.'), tag:'PhoneNo' },
                    { label:Translate('Ticket Number'), tag:'TicketNo' },
                    { label:Translate('Due Date'), tag:'DueDate' },
                    ...generateCustomTagLists(data.customersLabel_customfields)
                ],
                defaultLabel:`{{FirstName}} {{LastName}}\n{{PhoneNo}}\n{{TicketNo}}\n{{DueDate}}`,
            }
        ]
        let barcodeRow = document.getElementById('barcodeRow');
        barcodeRow.append(cTag('input',{'type':'hidden','id':'font_size','value':data.fontSize}));    
            const customizeBtnContainer = cTag('div',{'class':'columnXS12 flexStartRow',style:'padding:0;gap:10px;display:none'})
                const controllToggler = cTag('button',{class:'btn archiveButton','data-controller':'checkController'});
                    controllToggler.innerText = Translate('Advanced Customizer');
                    controllToggler.addEventListener('click',function(event){
                        event.preventDefault();
                        if(this.getAttribute('data-controller')==='checkController'){
                            document.querySelectorAll('.checkController').forEach(controller=>controller.style.display = 'none');
                            document.querySelectorAll('.advancedController').forEach(controller=>controller.style.display = '');
                            document.getElementById('noticeSection_advancedController').style.visibility = '';
                            this.setAttribute('data-controller','advancedController');
                            this.innerText = Translate('Label Checker');                            
                        }
                        else{
                            document.querySelectorAll('.checkController').forEach(controller=>controller.style.display = '');
                            document.querySelectorAll('.advancedController').forEach(controller=>controller.style.display = 'none');
                            document.getElementById('noticeSection_advancedController').style.visibility = 'hidden';
                            this.setAttribute('data-controller','checkController');
                            this.innerText = Translate('Advanced Customizer');                            
                        }
                    });
            customizeBtnContainer.append(controllToggler);
                let pTag = cTag('p',{class:'noticeSection',id:'noticeSection_advancedController',style:'visibility:hidden'});
                pTag.innerHTML = Translate('You can enter the following tags so when the Label is created these TAGS will be inserted for you');
            customizeBtnContainer.appendChild(pTag);
        barcodeRow.append(customizeBtnContainer);
            barcodeFields.forEach(item=>{
                barcodeRow.appendChild(formGroupCreator(data,item));
            })
            let divFormGroup = cTag('div',{ 'class':"columnXS12",'align':"left" });
            divFormGroup.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'value':data.variables_id }));
            divFormGroup.appendChild(cTag('input',{ 'class':"btn completeButton", 'style': "margin-top: 10px;", 'name':"submit",'value':Translate(data.variables_id>0?'Update':'Add'),'type':"submit" }));
        barcodeRow.appendChild(divFormGroup)
    }
    function generateCustomTagLists(customfields){
        return customfields.map(item=>{ 
            item = stripslashes(''+item);
            return {label:item, tag:item.replace(/\s+/g,'_')} 
        })
    }            
    function formGroupCreator(data,fields){
        let validTags = [];
        let labeldiv = cTag('div',{ 'class':"columnXS12" });
            let labelTitle = cTag('label',{ 'style':"font-size: 18px;" });
            labelTitle.innerHTML = fields.title;
        labeldiv.appendChild(labelTitle);            
        labeldiv.appendChild(cTag('br'));  
            //checkBox controller
            const selectedLabels = getLabelsFromTemplate(data[fields.id]);
            const checkController = cTag('div',{'class':'checkController innerContainer columnMD11','style':'padding:10px'});
            const maxLabelLength = Math.max(...fields.tagLists.map(item=>item.label.length));
            fields.tagLists.forEach((item,index)=>{
                const LabelInfo = selectedLabels[item.tag];
                const LabelItem = cTag('div',{'class':'flexStartRow labelRow','style':'gap:15px;margin-bottom:7px'})
                    const checkBox = cTag('input',{'type':'checkbox','data-labelTag':item.tag,'change':labelChecker(fields.id),'style':'cursor:pointer'});
                    const labelsLabel = cTag('input',{'disabled':true,'type':'text','name':'labelsLabel','placeholder':`${item.tag}:`,'style':'padding-left:10px;','keyup':labelsLabelChecker(fields.id)});
                    if(item.tag==='Barcode') labelsLabel.style.visibility = 'hidden';
                    const label = cTag('label',{'style':`width:${maxLabelLength+3}ch`});
                    label.innerText = item.label;
                    const lineBreaker = cTag('button',{'disabled':true,'class':'fa fa-arrow-turn-down','data-toggle':"tooltip",'data-original-title':'line break','name':'lineBreaker','data-linebreak':'false',click:linebreakChecker(fields.id),'style':'display: flex; align-items: center; justify-content: center; padding: 3px 20px; border-radius: 2px; background: gray; color: white; font-size: 15px; border: none;'});
                    if(fields.tagLists.length-1===index) lineBreaker.style.display = 'none';
                    tooltip(lineBreaker);
                    if(LabelInfo){
                        checkBox.checked = true;
                        labelsLabel.value = LabelInfo.LabelsLabel;
                        labelsLabel.disabled = false;
                        lineBreaker.disabled = false;
                        if(LabelInfo.Linebreak){
                            lineBreaker.setAttribute('data-linebreak','true');
                            lineBreaker.style.background = "#0075ff";
                        }
                    }
                LabelItem.append(checkBox,labelsLabel,label,lineBreaker);
                checkController.appendChild(LabelItem);
            })
        labeldiv.appendChild(checkController);
            
            let controlpanel = cTag('div',{'class':'advancedController flexStartRow','style':'gap:15px;display:none'});  
                let tagsDiv = cTag('div',{'class':'innerContainer columnMD6','style':'padding:10px'});
                fields.tagLists.forEach(item=>{
                    let tag = `{{${item.tag}}}`;
                    validTags.push(tag);
                    let tagCode = cTag('code',{style:'margin-right:10px'})
                    tagCode.innerHTML = tag;
                        let tagLabel = cTag('div',{class:'copyTag'});
                        tagLabel.append(tagCode,item.label,cTag('i',{style:'margin-left:10px',class:'fa fa-copy',click:()=>copyTag(tag)}));
                    tagsDiv.append(tagLabel);
                })
            controlpanel.appendChild(tagsDiv);
                let controller = cTag('div',{class: 'columnMD5',style:'display:flex'});
                    let textArea = cTag('textarea',{
                        name:fields.id, class:'form-control',style:'height:100%;min-height:15ch',
                        blur:function(){
                            checkValidTemplate.call(this,validTags);
                            const labels = getLabelsFromTemplate(this.value);
                            // checkController.querySelectorAll('input[type="checkbox"]').forEach(item=>{
                            //     if(labels.includes(item.id)) item.checked = true;
                            //     else item.checked = false;
                            // })
                        },
                        focus:function(){this.classList.remove('selectedError');}
                    });
                    textArea.value = data[fields.id];
                    textArea.vaild = function(){return checkValidTemplate.call(this,validTags)};
                controller.append(textArea);
                    let btnContainer = cTag('div',{style:'width: min-content;display:flex;flex-direction: column;'});
                    btnContainer.append(cTag('i',{click:()=>{confirm_dialog('Reset', `Reset to default ${fields.title}?`, (hide_Popup)=>{textArea.value = fields.defaultLabel;hide_Popup()})},class:'fa fa-undo','data-toggle':'tooltip','data-original-title':'reset',style:'padding:5px; border:1px solid #ccc; border-radius:4px; cursor:pointer; background-color:whitesmoke;'}));
                    btnContainer.append(cTag('i',{click:()=>previewLabel(data.LabelSizeInfo,fields.id,textArea,validTags),class:'fa fa-preview','data-toggle':'tooltip','data-original-title':'preview',style:'padding:5px; border:1px solid #ccc; border-radius:4px; cursor:pointer; background-color:whitesmoke;'}));
                controller.append(btnContainer);
            controlpanel.appendChild(controller);
        labeldiv.appendChild(controlpanel);
        return labeldiv;            
    } 
    function labelChecker(textAreaName){
        return function(){
            const labelsLabel = this.parentNode.querySelector("[name='labelsLabel']");
            const lineBreaker = this.parentNode.querySelector("[name='lineBreaker']");
            if(!this.checked){
                if(lineBreaker.getAttribute('data-linebreak')==='true') lineBreaker.click();
                labelsLabel.disabled = true;
                lineBreaker.disabled = true;
            }
            else{
                labelsLabel.disabled = false;
                lineBreaker.disabled = false;
            }
            buildTemplate(document.querySelector(`[name="${textAreaName}"]`),this.parentNode.parentNode)
        }
    } 
    function labelsLabelChecker(textAreaName){
        return function(){
            buildTemplate(document.querySelector(`[name="${textAreaName}"]`),this.parentNode.parentNode)
        }
    }
    function linebreakChecker(textAreaName){
        return function(event){
            event.preventDefault();
            if(this.getAttribute('data-linebreak')==='true'){
                this.style.background = "gray";
                this.setAttribute('data-linebreak','false');
            }
            else{
                this.style.background = "#0075ff";
                this.setAttribute('data-linebreak','true');
            }
            buildTemplate(document.querySelector(`[name="${textAreaName}"]`),this.parentNode.parentNode)
        }
    } 
    function buildTemplate(textArea,labelCheckerContainer){
        const checkedLabels = [...labelCheckerContainer.querySelectorAll('.labelRow')].filter(labelRow=>labelRow.querySelector('input[type="checkbox"]').checked);
        textArea.value = '';
        checkedLabels.forEach(item=>{
            const labelsLabel = item.querySelector('input[name="labelsLabel"]').value;
            const labelTag = item.querySelector('input[type="checkbox"]').getAttribute('data-labelTag');
            const lineBreak = item.querySelector('button[name="lineBreaker"]').getAttribute('data-linebreak')==='true'?'\n':' ';
            if(labelsLabel) textArea.value += `${labelsLabel} {{${labelTag}}}${lineBreak}`; 
            else textArea.value += `{{${labelTag}}}${lineBreak}`; 
        })
    }
    function getLabelsFromTemplate(template){
        const TagInfo = template.match(/\{\{[^\}\}]+\}\}/);
        if(TagInfo){
            const Tag = TagInfo[0].replace('{{','').replace('}}','');
            const Linebreak = template.search(`{{${Tag}}}\n`)>=0?true:false;
            const LabelsLabel = template.slice(0,TagInfo.index).trim();
            const slicedTemplate = template.slice((TagInfo.index)+(Tag.length+4)+(Linebreak?1:0));
            const returnedObj = {};
            returnedObj[Tag] = { Linebreak, LabelsLabel };
            return {
                ...returnedObj,
                ...getLabelsFromTemplate(slicedTemplate)
            }
        }
        else return {};
    }     
    function copyTag(tag){
        if(navigator.clipboard){
            showTopMessage('success_msg',Translate(`Tag copied to clipboard`));
            navigator.clipboard.writeText(tag);
        }
        else showTopMessage('alert_msg',Translate(`Clipboard API not supported. You can copy manually`))
    }
    function previewLabel(labelSizeInfo,labelFor,textArea,validTags){
        if(!checkValidTemplate.call(textArea,validTags)) return;
        let labelTemplate = textArea.value;
        
        labelSizeInfo.fontSize = document.getElementById('font_size').value;
        let labelInfo = {
            productLabel:{
                ...labelSizeInfo,
                "title":"Product Barcode Print Preview",
                "CompanyName":"Cell-Store",
                "ProductName":"iPhone 11",
                "Price":"$250.00",
                "Barcode":"82003898980",
                "productLabel":labelTemplate,
            },
            deviceLabel:{
                ...labelSizeInfo,
                "title":"Device Barcode Print Preview",
                "CompanyName":"Cell-Store",
                "ProductName":"iPhone 11 Black 128gb",
                "Price":"$250.00",
                "Barcode":"98372938123983791",
                "deviceLabel":labelTemplate,
            },
            repairTicketLabel:{
                ...labelSizeInfo,
                "title":"Repair Ticket Label Preview",
                "FirstName":"Marco",
                "LastName":"Polo",
                "PhoneNo":"+880 1724 456236",
                "TicketNo":"T523",
                "DueDate":"10-12-2022",
                "PhoneNo":"+880 1724 456236",
                "BrandModel":"Apple iPhone 11,Black",
                "MoreDeails":"Brand",
                "ImeiSerial":"98372938123983791",
                "Problem":"Broken Screen",
                "Password":"155666",
                "Barcode":"T523",
                "repairTicketLabel":labelTemplate,
            },
            repairCustomerLabel:{
                ...labelSizeInfo,
                "title":"Repair Customer Label Preview",
                "FirstName":"Marco",
                "LastName":"Polo",
                "PhoneNo":"+880 1724 456236",
                "TicketNo":"T523",
                "DueDate":"10-12-2022",
                "repairCustomerLabel":labelTemplate,
            }
        }
        let w = 900;
        let h = 600;
        let scrl = 1;
        let winl = (screen.width - w) / 2;
        let wint = (screen.height - h) / 2;
        let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
        let printWindow = window.open('', '', winprops);
        printWindow.title = labelInfo[labelFor].title
        barcodeLabel(labelInfo[labelFor], labelFor, printWindow,true);
    }    
    
    function checkValidTemplate(validTags){
        let warn = (tag,cause)=>{
            this.focus();
            let startPosition = this.value.search(tag);
            let endPosition = startPosition+tag.length;
            this.setSelectionRange(startPosition,endPosition);
            this.classList.add('errorTag');
            showTopMessage('alert_msg',`${cause} Tag found`)
            return true
        }    
        let Tags = this.value.match(/\{\{[^\}\}]+\}\}/g)||[];
        const InvalidTags = Tags.filter(tag=>!validTags.find(validtag=>validtag===tag));;
        const DuplicateTags = Tags.filter(tag=>{
            let numberOfDuplication = -1;
            Tags.forEach(tagItem=>{
                if(tagItem===tag) numberOfDuplication++;
            })
            return numberOfDuplication>0?true:false;
        });
        if(InvalidTags.length || DuplicateTags.length){
            DuplicateTags.forEach(tag=>warn(tag,'Duplicate'));
            InvalidTags.forEach(tag=>warn(tag,'Invalid'));
            return false;
        }
        else return true              
    }
}
async function AJsave_barcode_labels(event){
    event.preventDefault();
    let invalidTemplate = ["productLabel","deviceLabel","repairTicketLabel","repairCustomerLabel"].find(templateEditor=> !document.querySelector(`[name="${templateEditor}"]`).vaild())
    if(invalidTemplate) return;
    let submitBtn = document.querySelector("[name='submit']");
    let butVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    // const jsonData = serialize("#frmBL");
    const jsonData = {};
    ['variables_id','deviceLabel','productLabel','repairCustomerLabel','repairTicketLabel'].forEach(item=>{
        jsonData[item] = document.querySelector(`[name='${item}']`).value;
    })
    const url = '/'+segment1+'/AJsave_barcode_labels';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){			
			document.querySelector("[name='variables_id']").value = data.id;
            if('insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            if('update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing Barcode Label information! Please try again.'));			
		}
        btnEnableDisable(submitBtn, butVal, false);
    }                        
	return false;
}
//-------------------Restrict Access -------------------
function restrict_access(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(Translate('Restrict Access')));
        const restrictAccess = cTag('div',{ class: "flexSpaBetRow" });
        restrictAccess.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;";
            if(OS!=='unknown') callOutDivStyle += " padding-left: 10px; padding-right: 10px";
            let restrictAccessColumn = cTag('div',{ 'class':"columnMD10 columnSM9", 'style': "margin: 0;" });
                    const restrictAccessForm = cTag('form',{ 'name':"frmrestrict_access",'id':"frmrestrict_access",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                    restrictAccessForm.addEventListener('submit',AJsave_restrict_access);
                        let bsCallOutDiv = cTag('div',{ 'class':"innerContainer",'style': callOutDivStyle });
                            let acccessText = cTag('div',{ 'class':"columnSM12", 'style': "padding-bottom: 15px;" });
                                let pTag = cTag('p');
                                pTag.innerHTML = Translate('If you would like to restrict access by IP address enter the IP addresses (One per line) that are allowed access below.');
                            acccessText.appendChild(pTag);
                        bsCallOutDiv.appendChild(acccessText);
                            let ipRow = cTag('div',{ 'class':"flexStartRow", 'style': "padding-bottom: 15px;" });
                                let ipColumn = cTag('div',{ 'class':"columnSM3 columnMD2" });
                                    let ipLabel = cTag('label',{ 'for':"ip_address" });
                                    ipLabel.innerHTML = Translate('IP Address')+' :';
                                ipColumn.appendChild(ipLabel);
                            ipRow.appendChild(ipColumn);
                                let ipField = cTag('div',{ 'class':"columnSM4 columnMD3" });
                                    const textarea = cTag('textarea',{ 'id':"ip_address",'rows':"6",'name':"ip_address",'class':"form-control" });
                                    textarea.addEventListener('blur',sanitizer);
                                ipField.appendChild(textarea);
                                ipField.appendChild(cTag('span',{ 'id':"errmsg_ip_address",'class':"errormsg" }));
                            ipRow.appendChild(ipField);
                        bsCallOutDiv.appendChild(ipRow);
                            let buttonNames = cTag('div',{ 'class':"flexStartRow", 'style': "padding-bottom: 15px;" });
                                let submitButton = cTag('div',{ 'class':"columnXS5", 'align': "center"});
                                submitButton.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'id':"variables_id" }));
                                submitButton.appendChild(cTag('input',{ 'class':"btn completeButton",'name':"submit",'id':"submit",'type':"submit" }));
                            buttonNames.appendChild(submitButton);
                        bsCallOutDiv.appendChild(buttonNames);
                    restrictAccessForm.appendChild(bsCallOutDiv);
            restrictAccessColumn.appendChild(restrictAccessForm);
        restrictAccess.appendChild(restrictAccessColumn);
    Dashboard.appendChild(restrictAccess);
    AJ_restrict_access_MoreInfo();
}
async function AJ_restrict_access_MoreInfo(){
    const url = '/'+segment1+'/AJ_restrict_access_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        document.querySelector('#ip_address').value = data.ip_address;
        document.querySelector('#variables_id').value = data.variables_id;
        if(data.id>0) document.querySelector('#submit').value = Translate('Update');
        else document.querySelector('#submit').value = Translate('Add');
    }
}
async function AJsave_restrict_access(event){
    event.preventDefault();
    if(checkIPAddress()===false){
		document.getElementById('ip_address').focus();
		return false;
	}
    let submitBtn = document.querySelector("#submit");
    let butVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    const jsonData = serialize("#frmrestrict_access");
    const url = '/'+segment1+'/AJsave_restrict_access';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){			
			document.getElementById("variables_id").value = data.id;
            if('insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            if('update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occurred while changing restrict access information! Please try again.'));			
		}
        btnEnableDisable(submitBtn, butVal, false);
    }                        
	return false;
}
function checkIPAddress(){
	let IPText = document.getElementById('ip_address').value;
	/*ipParts = IPText.split(".");
	if(ipParts.length===4){
	  for(i=0;i<4;i++){
		 
		TheNum = parseInt(ipParts[i]);
		if(TheNum >= 0 && TheNum <= 255){}
		else{break;}
		 
	  }
	  if(i===4)ValidIP=true; 
	}*/
	return true;
}
//========================Devices====================
function carriers_conditions(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    segment2==='carriers' && Dashboard.appendChild(header(Translate('Carriers')));
    segment2==='conditions' && Dashboard.appendChild(header(Translate('Conditions')));
        const carriersContainer = cTag('div',{class: "flexSpaBetRow"});
        carriersContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let carriersColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDiv = cTag('div',{'class':"innerContainer",'style': callOutDivStyle});                
                    const carrierForm = cTag('form',{'name':"frmdevices",'id':"frmdevices",'enctype':"multipart/form-data",'method':'post','accept-charset':"utf-8"});
                    carrierForm.addEventListener('submit',AJsave_devices);
                        let carrierRow = cTag('div',{'class':"flexStartRow", 'style': "padding-bottom: 15px;"});
                        if(segment2==='conditions') carrierRow.style.display = 'none';
                            let carrierName = cTag('div',{'class':"columnXS12 columnSM3", 'style': "padding-left: 10px;"});
                                let carrierLabel = cTag('label',{'for':"carriers[0]"});
                                carrierLabel.innerHTML = Translate('Carriers');
                            carrierName.appendChild(carrierLabel);
                        carrierRow.appendChild(carrierName);
                            let carrierListColumn = cTag('div',{'class':"columnXS12 columnSM9", 'style': "padding-right: 20px;"});
                                let carrierListRow = cTag('div',{'class':"columnXS12 plusIconPosition roundborder"});
                                carrierListRow.appendChild(cTag('ul',{'id':"carriersListRow",'class':"flexStartRow multipleRowList"}));
                                carrierListRow.appendChild(cTag('span',{'id':"error_CarriersListRow",'class':"errormsg"}));
                                    let addMoreCarrierDiv = cTag('div',{'class':"addNewPlusIcon"});
                                        let addMoreCarrierLink = cTag('a',{'href':"javascript:void(0);",'title':Translate('Add More Carriers'),'click':addNewCarriers});
                                        addMoreCarrierLink.appendChild(cTag('img',{'align':"absmiddle",'alt':Translate('Add More Carriers'),'title':Translate('Add More Carriers'),'src':"/assets/images/plus20x25.png"}));
                                    addMoreCarrierDiv.appendChild(addMoreCarrierLink);
                                carrierListRow.appendChild(addMoreCarrierDiv);
                            carrierListColumn.appendChild(carrierListRow);
                        carrierRow.appendChild(carrierListColumn);
                    carrierForm.appendChild(carrierRow);
                        let conditionsRow = cTag('div',{'class':"flexStartRow", 'style': "padding-bottom: 15px;"});
                        if(segment2==='carriers') conditionsRow.style.display = 'none';
                            let conditionName = cTag('div',{'class':"columnXS12 columnSM3", 'style': "padding-left: 10px;"});
                                let conditionLabel = cTag('label',{'for':"conditions[0]"});
                                conditionLabel.innerHTML = Translate('Conditions');
                            conditionName.appendChild(conditionLabel);
                        conditionsRow.appendChild(conditionName);
                            let conditionsColumn = cTag('div',{'class':"columnXS12 columnSM9", 'style': "padding-right: 20px;"});
                                let conditionListRow = cTag('div',{'class':"columnXS12 plusIconPosition roundborder"});
                                conditionListRow.appendChild(cTag('ul',{'id':"conditionsListRow",'class':"flexStartRow multipleRowList"}));
                                conditionListRow.appendChild(cTag('span',{'id':"error_conditionsListRow",'class':"errormsg"}));
                                    let addConditionDiv = cTag('div',{'class':"addNewPlusIcon"});
                                        let addConditionLink = cTag('a',{'href':"javascript:void(0);",'title':Translate('Add More Conditions'),'click':addNewCondition});
                                        addConditionLink.appendChild(cTag('img',{'align':"absmiddle",'alt':Translate('Add More Conditions'),'title':Translate('Add More Conditions'),'src':"/assets/images/plus20x25.png"}));
                                    addConditionDiv.appendChild(addConditionLink);
                                conditionListRow.appendChild(addConditionDiv);
                            conditionsColumn.appendChild(conditionListRow);
                        conditionsRow.appendChild(conditionsColumn);
                    carrierForm.appendChild(conditionsRow);
                        let buttonName = cTag('div',{'class':"flexStartRow", 'style': "padding-bottom: 15px;"});
                        buttonName.appendChild(cTag('div',{'class':'columnXS12 columnSM3'}));
                            let buttonTitle = cTag('div',{'class':"columnXS12 columnSM9"});
                            buttonTitle.appendChild(cTag('input',{'type':"hidden",'id':"devicesSetup",'value':"1"}));
                            buttonTitle.appendChild(cTag('input',{'class':"btn completeButton",'name':"submit",'id':"submit",'type':"submit",'value':Translate('Update')}));
                        buttonName.appendChild(buttonTitle);
                    carrierForm.appendChild(buttonName);
                callOutDiv.appendChild(carrierForm);
            carriersColumn.appendChild(callOutDiv);
        carriersContainer.appendChild(carriersColumn);
    Dashboard.appendChild(carriersContainer);
    AJ_carriers_conditions_MoreInfo();
}
async function AJ_carriers_conditions_MoreInfo(){
	let api = segment2==='carriers'?'AJ_carriers_MoreInfo':'AJ_conditions_MoreInfo'
    const url = '/'+segment1+'/'+api;
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let li;
        let ul = document.querySelector('#carriersListRow');
        if(data.carriersarray.length){      
            data.carriersarray.forEach((item,indx)=>{
                if(item!==''){
                    li = cTag('li');
                    li.appendChild(cTag('input',{'type':"text",'maxlength':"25",'placeholder':`${Translate('Enter new carrier')} ${indx+1}`, alt:`${Translate('Enter new carrier')} ${indx+1}`,'title':item,'name':"carriers[]",'value':item,'class':"form-control placeholder"}));
                ul.appendChild(li);
                }
            })
        }
            li = cTag('li');                                                               
            li.appendChild(cTag('input',{'type':"text",'maxlength':"25",'name':"carriers[]",'value':"",'placeholder':`${Translate('Enter new carrier')} ${data.carriersarray.length+1}`,'alt':`${Translate('Enter new carrier')} ${data.carriersarray.length+1}`,'class':"form-control placeholder"}));
        ul.appendChild(li);
        makedeletedicon('carriersListRow');	
        ul = document.querySelector('#conditionsListRow')
        if(data.conditionsarray.length){
            data.conditionsarray.forEach((item,indx)=>{
                if(item!==''){
                    li = cTag('li');
                    li.appendChild(cTag('input',{'type':"text",'maxlength':"3",'placeholder':`${Translate('Enter new condition')} ${indx+1}`,'alt':`${Translate('Enter new condition')} ${indx+1}`,'title':item,'name':"conditions[]",'value':item,'class':"form-control placeholder"}));
                ul.appendChild(li);
                }
            })
        }
            li = cTag('li');
            li.appendChild(cTag('input',{'type':"text",'maxlength':"3",'name':"conditions[]",'value':"",'placeholder':`${Translate('Enter new condition')} ${data.conditionsarray.length+1}`,'alt':`${Translate('Enter new condition')} ${data.conditionsarray.length+1}`,'class':"form-control placeholder"}));
        ul.appendChild(li);
        makedeletedicon('conditionsListRow');
    }
}
async function AJsave_devices(event){
    event.preventDefault();
	let carriersarray = document.getElementsByName('carriers[]');							
	let carriers_listarray = new Array();
    let error_messageid;
	error_messageid = document.getElementById('error_CarriersListRow');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < carriersarray.length; i++) {
														
		if(carriersarray[i].value !==''){
			if (carriers_listarray.length > 0 && carriers_listarray.indexOf(carriersarray[i].value) !== -1) {
				error_messageid.innerHTML = Translate('Duplicate Carriers')+parseInt(i+1);
				carriersarray[i].focus();
				return false;
			}
			else {
				carriers_listarray[i] = carriersarray[i].value;
			}
		}
	}
	
	let conditionsarray = document.getElementsByName('conditions[]');							
	let conditions_listarray = new Array();
	error_messageid = document.getElementById('error_conditionsListRow');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < conditionsarray.length; i++) {
														
		if(conditionsarray[i].value !==''){
			if (conditions_listarray.length > 0 && conditions_listarray.indexOf(conditionsarray[i].value) !== -1) {
				error_messageid.innerHTML = Translate('Duplicate Condition')+parseInt(i+1);
				conditionsarray[i].focus();
				return false;
			}
			else {
				conditions_listarray[i] = conditionsarray[i].value;
			}
		}
	} 
    let submitBtn = document.querySelector("#submit");
	let submitBtnVal = submitBtn.value;
    btnEnableDisable(submitBtn, Translate('Saving'), true);
	
    const jsonData = serialize("#frmdevices");
    const url = '/'+segment1+'/AJsave_devices';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){		
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing Devices Setup information! Please try again.'));
		}
        btnEnableDisable(submitBtn, submitBtnVal, false);
    }
	return false;
}
function checkCarriers(){	
	let carriersarray = document.getElementsByName('carriers[]');							
	let carriers_listarray = new Array();
	let error_messageid = document.getElementById('error_CarriersListRow');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < carriersarray.length; i++) {
														
		if(carriersarray[i].value===''){
			error_messageid.innerHTML = Translate('Missing Carriers')+parseInt(i+1);
			carriersarray[i].focus();
			return false;
		}
		else if (carriers_listarray.length > 0 && carriers_listarray.indexOf(carriersarray[i].value) !== -1) {
			error_messageid.innerHTML = Translate('Duplicate Carriers')+parseInt(i+1);
			carriersarray[i].focus();
			return false;
		}
		else {
			carriers_listarray[i] = carriersarray[i].value;
		}
	}
	return true;
}
function addNewCarriers(){
	if(checkCarriers()===false){return false;}
	else{
		let ulidname = 'carriersListRow';
		let index = document.querySelector("ul#"+ulidname).childElementCount;
		index = parseInt(index+1)
        let newmore_list = cTag('li');                                                               
        newmore_list.appendChild(cTag('input',{'type':"text",'maxlength':"25",'name':"carriers[]",'value':"",'placeholder':`${Translate('Enter new carrier')} ${index}`,'alt':`${Translate('Enter new carrier')} ${index}`,'class':"form-control placeholder carriers"}));
		document.querySelector("#"+ulidname).appendChild(newmore_list);
        callPlaceholder();
		document.getElementsByName('carriers[]')[parseInt(index-1)].focus();
		makedeletedicon('carriersListRow');												
	}
}
function checkConditions(){						
	let conditionsarray = document.getElementsByName('conditions[]');							
	let conditions_listarray = new Array();
	let error_messageid = document.getElementById('error_conditionsListRow');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < conditionsarray.length; i++) {
														
		if(conditionsarray[i].value===''){
			error_messageid.innerHTML = Translate('Missing Condition')+parseInt(i+1);
			conditionsarray[i].focus();
			return false;
		}
		else if (conditions_listarray.length > 0 && conditions_listarray.indexOf(conditionsarray[i].value) !== -1) {
			error_messageid.innerHTML = Translate('Duplicate Condition')+parseInt(i+1);
			conditionsarray[i].focus();
			return false;
		}
		else {
			conditions_listarray[i] = conditionsarray[i].value;
		}
	}
	return true;
}
function addNewCondition(){
	if(checkConditions()===false){return false;}
	else{
		let ulidname = 'conditionsListRow';
		let index = document.querySelector("ul#"+ulidname).childElementCount;
		index = parseInt(index+1)
        let newmore_list = cTag('li');                                                               
        newmore_list.appendChild(cTag('input',{'type':"text",'maxlength':"3",'name':"conditions[]",'value':"",'placeholder':`${Translate('Enter new condition')} ${index}`,'alt':`${Translate('Enter new condition')} ${index}`,'class':"form-control placeholder"}));
        document.querySelector("#"+ulidname).appendChild(newmore_list);
        callPlaceholder();
		
		document.getElementsByName('conditions[]')[parseInt(index-1)].focus();
		makedeletedicon('conditionsListRow');
	}
}
//---------------------Custom Fields-----------------------
function devices_custom_fields(){
    custom_fields_creator(Translate('Custom Fields'),'devices');
}
//========================Cash Register====================//
function cash_Register_general(){
    function formGroupCreator(info){
        let divFormGroup = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
            const checkBoxDiv = cTag('div',{ "class":"columnXS1 columnMD2","align":"right" });
                let checkBoxLabel = cTag('label',{ "class":"cursor","for":`${info.id}` });
                checkBoxLabel.appendChild(cTag('input',{ "type":"checkbox","name":`${info.id}`,"id":`${info.id}`,"value":"1" }));
            checkBoxDiv.appendChild(checkBoxLabel);
        divFormGroup.appendChild(checkBoxDiv);
            const cashRegisterText = cTag('div',{ "class":"columnXS11 columnMD10", 'style': "padding-left: 0;" });
                let cashRegisterLabel = cTag('label',{ "class":"cursor","for":`${info.id}` });
                cashRegisterLabel.innerHTML = info.label;
            cashRegisterText.appendChild(cashRegisterLabel);
        divFormGroup.appendChild(cashRegisterText);
        return divFormGroup;
    }
    const formGroupsInfo = [
        { id: 'cash_reg_req_customer', label: Translate('Click if Cash register requires a customer') },
        { id: 'cash_drawer_sale', label: Translate('Enable open cash drawer without sale button') },
        { id: 'petty_cash_tracking', label: Translate('Enable petty cash tracking') }
    ]
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Cash Register')} ${Translate('General')}`));
        const cashRegisterContainer = cTag('div',{class: "flexSpaBetRow"});
        cashRegisterContainer.appendChild(leftSideMenu());
            let cashRegisterColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                    const cashRegisterForm = cTag('form',{ "name":"frmgeneral","id":"frmgeneral","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                    cashRegisterForm.addEventListener('submit',AJsave_cash_Register_general);
                        let bsCallOut = cTag('div',{ "class":"innerContainer", "style":"margin-top: 0; background: #fff; padding-right: 0" });
                        formGroupsInfo.forEach(item=>{
                            bsCallOut.appendChild(formGroupCreator(item));
                        })
                            let buttonName = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            buttonName.appendChild(cTag('div',{'class':'columnXS1 columnMD2'}));
                                let buttonTitle = cTag('div',{ "class":"columnXS11 columnMD10" });
                                buttonTitle.appendChild(cTag('input',{ "type":"hidden","name":"variables_id","id":"variables_id" }));
                                buttonTitle.appendChild(cTag('input',{ "class":"btn completeButton","name":"submit","id":"submit","type":"submit" }));
                            buttonName.appendChild(buttonTitle);
                        bsCallOut.appendChild(buttonName);
                    cashRegisterForm.appendChild(bsCallOut);
            cashRegisterColumn.appendChild(cashRegisterForm);
        cashRegisterContainer.appendChild(cashRegisterColumn);
    Dashboard.appendChild(cashRegisterContainer);
    AJ_cash_Register_general_MoreInfo();
}
async function AJ_cash_Register_general_MoreInfo(){
    const url = '/'+segment1+'/AJ_cash_Register_general_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        if(data.cash_drawer_sale===1) document.querySelector('#cash_drawer_sale').checked = true;
        if(data.cash_reg_req_customer===1) document.querySelector('#cash_reg_req_customer').checked = true;
        if(data.petty_cash_tracking===1) document.querySelector('#petty_cash_tracking').checked = true;
        document.querySelector('#variables_id').value = data.variables_id;
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }  
    }
}
async function AJsave_cash_Register_general(event){
    event.preventDefault();
    let submitBtn = document.querySelector("#submit");
    let submitBtnVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    const jsonData = serialize("#frmgeneral");
    const url = '/'+segment1+'/AJsave_cash_Register_general';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occurred while adding new cash register options! Please try again.'));		
		}
        btnEnableDisable(submitBtn,submitBtnVal,false);
    }
	return false;
}
//----------------------counting_Cash_Til----------------------
function counting_Cash_Til(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Cash Register')} ${Translate('Counting Cash Til')}`));
        const countingCashContainer = cTag('div',{class: "flexSpaBetRow"});
        countingCashContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let countingCashColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDiv = cTag('div',{'class':"innerContainer",'style': callOutDivStyle});
                    const countingCashForm = cTag('form',{ "name":"frmdenomination_options","id":"frmdenomination_options","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                    countingCashForm.addEventListener('submit',AJsave_counting_Cash_Til);
                        const denominationRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;"});
                            const denominationColumn = cTag('div',{ "class":"columnSM3 columnMD2" });
                                let denominationLabel = cTag('label',{ "for":"denomination_options" });
                                denominationLabel.innerHTML = Translate('Denomination Options')+':';
                            denominationColumn.appendChild(denominationLabel);
                        denominationRow.appendChild(denominationColumn);
                            let denominationOptionRow = cTag('div',{ "class":"columnSM9 columnMD10" });
                                let denominationOptionColumn = cTag('div',{ "class":"columnXS12 roundborder" });
                                denominationOptionColumn.appendChild(cTag('ul',{ "id":"denominationsListRow","class":"multipleRowList" }));
                                denominationOptionColumn.appendChild(cTag('span',{ "id":"errorDenominations","class":"errormsg" }));
                                    const addDenominationDiv = cTag('div',{ 'style': "text-align: end;" });
                                        let addDenominationLink = cTag('a',{ "href":"javascript:void(0);","title":Translate('Add denomination options'),"click":addDenomination });
                                        addDenominationLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Add denomination options'),"title":Translate('Add denomination options'),"src":"/assets/images/plus20x25.png" }));
                                    addDenominationDiv.appendChild(addDenominationLink);
                                denominationOptionColumn.appendChild(addDenominationDiv);
                            denominationOptionRow.appendChild(denominationOptionColumn);
                        denominationRow.appendChild(denominationOptionRow);
                    countingCashForm.appendChild(denominationRow);
                        const buttonName = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            let buttonTitle = cTag('div',{ "class":"columnXS12","align":"right" });
                            buttonTitle.appendChild(cTag('input',{ "type":"hidden","id":"counting_Cash_Til","value":"1" }));
                            buttonTitle.appendChild(cTag('input',{ "type":"hidden","name":"variables_id","id":"variables_id" }));
                            buttonTitle.appendChild(cTag('input',{ "class":"btn completeButton","name":"submit","id":"submit","type":"submit" }));
                        buttonName.appendChild(buttonTitle);
                    countingCashForm.appendChild(buttonName);
                callOutDiv.appendChild(countingCashForm);
            countingCashColumn.appendChild(callOutDiv);
        countingCashContainer.appendChild(countingCashColumn);
    Dashboard.appendChild(countingCashContainer);
    AJ_counting_Cash_Til_MoreInfo()
}
async function AJ_counting_Cash_Til_MoreInfo(){
    const url = '/'+segment1+'/AJ_counting_Cash_Til_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        const denominationsListRow = document.querySelector('#denominationsListRow');
        let ds = 1;
        function listRowCreator(dOption='',dValue=null){
                let li = cTag('li');
                    let cashCountRow = cTag('div',{ "class":"flexSpaBetRow" });
                        let denominationOption = cTag('div',{ "class":"columnXS7" });
                        denominationOption.appendChild(cTag('input',{ "type":"text","maxlength":"20","placeholder":`${Translate('Enter denomination option')} ${ds}`,"alt":`${Translate('Enter denomination option')} ${ds}`,"name":"dOption[]","value":dOption,"class":"form-control placeholder" }));
                    cashCountRow.appendChild(denominationOption);
                        let denominationValue = cTag('div',{ "class":"columnXS5" });
                        denominationValue.appendChild(cTag('input',{ "type":"number",'step':'0.01',"placeholder":`${Translate('Enter denomination value')} ${ds}`,"alt":`${Translate('Enter denomination value')} ${ds}`,"name":"dValue[]","value":dValue===null?'':Number(dValue).toFixed(2),"class":"form-control placeholder" }));
                    cashCountRow.appendChild(denominationValue);
                li.appendChild(cashCountRow);
            denominationsListRow.appendChild(li)
        }
        if(data.denominationsArray.length>0){                
            data.denominationsArray.forEach(item=>{
                let oneDenominationInfo = item.split('=')
                if(oneDenominationInfo.length>0){
                    let [dOption,dValue] = oneDenominationInfo;                       
                    listRowCreator(dOption,dValue)
                    ds++;
                }
            })
        }
        listRowCreator();
        makedeletedicon('denominationsListRow');            
        document.querySelector('#variables_id').value = data.variables_id;
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }  
    }
}
async function AJsave_counting_Cash_Til(event){
    event.preventDefault();
	if(checkDenominations()===false){return false;}
	else{
        let submitBtn = document.querySelector("#submit");
        let submitBtnVal = submitBtn.value;
        btnEnableDisable(submitBtn,Translate('Saving'),true);
        
        const jsonData = serialize("#frmdenomination_options");
        const url = '/'+segment1+'/AJsave_counting_Cash_Til';
        fetchData(afterFetch,url,jsonData);
        function afterFetch(data){
            if(data.savemsg !=='error' && data.id>0){				
				document.getElementById("variables_id").value = data.id;
                if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Denomination Options Inserted'));
                else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Denomination Options Updated'));
			}
			else{
                showTopMessage('alert_msg',Translate('Error occured while changing denomination options information! Please try again.'));				
			}
            btnEnableDisable(submitBtn,submitBtnVal,false);
        }
		return false;
	}
}
function checkDenominations(){
	let dOptionArray = document.getElementsByName('dOption[]');
	let dValueArray = document.getElementsByName('dValue[]');
	let dOptionCkArray = new Array();
	let errorId = document.getElementById('errorDenominations');
	errorId.innerHTML = '';
	for(let i = 0; i < dOptionArray.length; i++) {
		let dOption = dOptionArray[i].value;
		if (dOptionCkArray.length > 0 && dOptionCkArray.indexOf(dOption) !== -1) {
			errorId.innerHTML = Translate('Duplicate Denomination Options')+parseInt(i+1);
			dOptionArray[i].focus();
			return false;
		}
		else {
			dOptionCkArray[i] = dOption;
		}
		let dValue = dValueArray[i].value;
		if (dOption!=='' && dValue<=0) {
			errorId.innerHTML = Translate('Missing Denomination value')+parseInt(i+1);
			dValueArray[i].focus();
			return false;
		}
	}
	return true;
}
function addDenomination(){
	if(checkDenominations()===false){return false;}
	else{
		let index = document.querySelector("ul#denominationsListRow").childElementCount;
		index = parseInt(index+1)
        let newmore_list = cTag('li');
            let enterDenominationRow = cTag('div',{ "class":"flexSpaBetRow" });
                let enterDenominationColumn = cTag('div',{ "class":"columnSM7" });
                enterDenominationColumn.appendChild(cTag('input',{ "type":"text","maxlength":"20","placeholder":`${Translate('Enter denomination option')} ${index}`,"alt":`${Translate('Enter denomination option')} ${index}`,"name":"dOption[]","class":"form-control placeholder" }));
            enterDenominationRow.appendChild(enterDenominationColumn);
                let denominationValue = cTag('div',{ "class":"columnSM5" });
                denominationValue.appendChild(cTag('input',{ "type":"text","maxlength":"6","placeholder":`${Translate('Enter denomination value')} ${index}`,"alt":`${Translate('Enter denomination value')} ${index}`,"name":"dValue[]","class":"form-control placeholder", 'style': "min-width: 70px;" }));
            enterDenominationRow.appendChild(denominationValue);
        newmore_list.appendChild(enterDenominationRow);
		document.querySelector("#denominationsListRow").appendChild(newmore_list);
		callPlaceholder();
		document.getElementsByName('dOption[]')[parseInt(index-1)].focus();
		makedeletedicon('denominationsListRow');
	}
}
//----------------------multiple_Drawers----------------------
function multiple_Drawers(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Cash Register')} ${Translate('Multiple Drawers')}`));
        const multipleDrawerContainer = cTag('div',{class: "flexSpaBetRow"});
        multipleDrawerContainer.appendChild(leftSideMenu());
            let multipleDrawerColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                const multipleDrawerForm = cTag('form',{ "name":"frmmultiple_drawers","id":"frmmultiple_drawers","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                multipleDrawerForm.addEventListener('submit',AJsave_multiple_Drawers);
                    let callOutDivStyle = "margin-top: 0; background: #fff;"
                    if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
                    let bsCallOut = cTag('div',{ "class":"innerContainer", "style": callOutDivStyle });
                        let multipleDrawerRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            let checkBoxDrawer = cTag('div',{ "class":"columnXS1 columnMD2","align":"right" });
                                let checkBoxLabel = cTag('label',{ "class":"cursor","for":"multiple_cash_drawers" });
                                const multiple_cash_drawers = cTag('input',{ "type":"checkbox","name":"multiple_cash_drawers","id":"multiple_cash_drawers","value":"1" })
                                multiple_cash_drawers.addEventListener('click', toggleMultipleCD);
                                checkBoxLabel.appendChild(multiple_cash_drawers);
                            checkBoxDrawer.appendChild(checkBoxLabel);
                        multipleDrawerRow.appendChild(checkBoxDrawer);
                            let enableCashDrawer = cTag('div',{ "class":"columnXS11 columnMD10", 'style': "padding-left: 0;" });
                                let enableCashLabel = cTag('label',{ "class":"cursor","for":"multiple_cash_drawers" });
                                enableCashLabel.innerHTML = Translate('Enable multiple cash drawers?');
                            enableCashDrawer.appendChild(enableCashLabel);
                        multipleDrawerRow.appendChild(enableCashDrawer);
                    bsCallOut.appendChild(multipleDrawerRow);
                        let cashDrawerRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;", "id":"Cash_DrawersRow" });
                            let cashDrawerColumn = cTag('div',{ "class":"columnSM4 columnMD2" });
                                let cashDrawerLabel = cTag('label',{ "for":"cash_drawers" });
                                cashDrawerLabel.innerHTML = Translate('Cash Drawers')+' :';
                            cashDrawerColumn.appendChild(cashDrawerLabel);
                        cashDrawerRow.appendChild(cashDrawerColumn);
                            let cashDrawerOption = cTag('div',{ "class":"columnSM9 columnMD10" });
                                let cashDrawerDiv = cTag('div',{ "class":"columnXS12 plusIconPosition roundborder" });
                                cashDrawerDiv.appendChild(cTag('ul',{ "id":"cdListRow","class":"flexStartRow multipleRowList" }));
                                cashDrawerDiv.appendChild(cTag('div',{ "id":"errorCDListRow","class":"errormsg columnXS6", 'style': "margin: 0; text-align: center;" }));
                                    let newCashDrawer = cTag('div',{ "class":"addNewPlusIcon" });
                                        let newCashDrawerLink = cTag('a',{ "href":"javascript:void(0);","title":Translate('New cash drawer'),"click":addMoreCD });
                                        newCashDrawerLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('New cash drawer'),"title":Translate('New cash drawer'),"src":"/assets/images/plus20x25.png" }));
                                    newCashDrawer.appendChild(newCashDrawerLink);
                                cashDrawerDiv.appendChild(newCashDrawer);
                            cashDrawerOption.appendChild(cashDrawerDiv);
                        cashDrawerRow.appendChild(cashDrawerOption);
                    bsCallOut.appendChild(cashDrawerRow);
                        const buttonName = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            let buttonTitle = cTag('div',{ "class":"columnXS5", 'align': "center"});
                            buttonTitle.appendChild(cTag('input',{ "type":"hidden","id":"multiple_Drawers","value":"1" }));
                            buttonTitle.appendChild(cTag('input',{ "type":"hidden","name":"variables_id","id":"variables_id" }));
                            buttonTitle.appendChild(cTag('input',{ "class":"btn completeButton","name":"submit","id":"submit","type":"submit" }));
                        buttonName.appendChild(buttonTitle);
                    bsCallOut.appendChild(buttonName);
                multipleDrawerForm.appendChild(bsCallOut);
            multipleDrawerColumn.appendChild(multipleDrawerForm);
        multipleDrawerContainer.appendChild(multipleDrawerColumn);
    Dashboard.appendChild(multipleDrawerContainer);
    AJ_multiple_Drawers_MoreInfo()
}
async function AJ_multiple_Drawers_MoreInfo(){
    const url = '/'+segment1+'/AJ_multiple_Drawers_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let li;
        const cdListRow = document.querySelector('#cdListRow');
        let serialNo = 1;
        if(data.cdData.length>0){
            data.cdData.forEach(cdOneValue=>{
                if(cdOneValue !== ''){
                        li = cTag('li');
                            const moveUpRow = cTag('div',{ "class":"flexSpaBetRow" });
                                let moveUpColumn = cTag('div',{ "class":"columnXS2 columnMD1 serialNumber flexSpaBetRow" });
                                moveUpColumn.innerHTML = serialNo;
                                    let arrowUp = cTag('a',{ "class":"cdOrderUp", "href":"javascript:void(0);","title":"Move to UP" });
                                    arrowUp.appendChild(cTag('i',{ "class":"fa fa-arrow-up" }));
                                moveUpColumn.appendChild(arrowUp);
                            moveUpRow.appendChild(moveUpColumn);
                                let drawersValue = cTag('div',{ "class":"columnXS10 columnMD11" });
                                let drawerInput = cTag('input',{ "type":"text","maxlength":"20","name":"cash_drawers[]","value":cdOneValue,"title":cdOneValue,"placeholder":Translate('New cash drawer name'),"alt":Translate('New cash drawer name'),"class":"form-control placeholder cash_drawers" });
                                drawerInput.addEventListener('keydown',preventSpecialCharacter);
                                drawersValue.appendChild(drawerInput);
                            moveUpRow.appendChild(drawersValue);
                        li.appendChild(moveUpRow);
                    cdListRow.appendChild(li);
                    serialNo++;
                }                        
            })
        }
            li = cTag('li');
                const serialNumberRow = cTag('div',{ "class":"flexSpaBetRow" });
                    let serialNumberColumn = cTag('div',{ "class":"columnXS2 columnMD1 serialNumber flexSpaBetRow" });
                    serialNumberColumn.innerHTML = serialNo;
                serialNumberRow.appendChild(serialNumberColumn);
                    let newCashDrawerColumn = cTag('div',{ "class":"columnXS10 columnMD11" });
                        let drawerInput = cTag('input',{ "type":"text","maxlength":"25","name":"cash_drawers[]","placeholder":Translate('New cash drawer name'),"alt":Translate('New cash drawer name'),"class":"form-control placeholder cash_drawers" });
                        drawerInput.addEventListener('keydown',preventSpecialCharacter);
                    newCashDrawerColumn.appendChild(drawerInput);
                serialNumberRow.appendChild(newCashDrawerColumn);
            li.appendChild(serialNumberRow);
        cdListRow.appendChild(li);
        
        if(data.multiple_cash_drawers === 1) document.querySelector('#multiple_cash_drawers').checked = true;
        document.querySelector('#variables_id').value = data.variables_id;
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }  
        toggleMultipleCD();
        rearrangeMDList()
    }
}
async function AJsave_multiple_Drawers(event){
    event.preventDefault();
    if(checkCDFields()===false){return false;}
    let error_messageid = document.getElementById('errorCDListRow');
    let cdData = document.getElementsByName('cash_drawers[]');
    if ((cdData.length < 2) || (cdData.length == 2 && cdData[1].value == '')) {
        error_messageid.innerHTML = Translate('Please Enter at least two drawer');
        cdData[cdData.length-1].focus();
        return false;
    }
    let submitBtn = document.querySelector("#submit");
    let submitBtnVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    
    const jsonData = serialize("#frmmultiple_drawers");
    const url = '/'+segment1+'/AJsave_multiple_Drawers';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(['insert-success', 'update-success'].includes(data.savemsg) && data.id>0){
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
        else if(data.savemsg ==='beforeAnyPayments'){
            showTopMessage('alert_msg',Translate('You can only make changes to this section BEFORE any payments have been taken for the day'));
        }
		else{
            showTopMessage('alert_msg',Translate('Error occurred while adding new cash register options! Please try again.'));
		}
        btnEnableDisable(submitBtn,submitBtnVal,false);  
    }
	return false;
}
function toggleMultipleCD(){
	let ckOrUnCk = document.querySelector('#multiple_cash_drawers').checked;
	if(ckOrUnCk===false){
        if(document.querySelector("#Cash_DrawersRow").style.display !== 'none'){
            document.querySelector("#Cash_DrawersRow").style.display = 'none';
        }
	}
	else{
        if(document.querySelector("#Cash_DrawersRow").style.display === 'none'){
            document.querySelector("#Cash_DrawersRow").style.display = '';
        }
	}
}
function cdOrderUp(cac){
    let topPos;
	topPos = cac-1;
	let totalLI = parseInt(document.querySelector("ul#cdListRow").childElementCount);
	if(totalLI>1){
		if(cac===0){
			topPos = totalLI-2;
		}
		
        let temp;
		if(document.querySelector("ul#cdListRow").childElementCount>1){
            temp = document.querySelectorAll('[name="cash_drawers[]"]')[topPos].value;
            document.querySelectorAll('[name="cash_drawers[]"]')[topPos].value = document.querySelectorAll('[name="cash_drawers[]"]')[cac].value;
            document.querySelectorAll('[name="cash_drawers[]"]')[cac].value = temp;
			rearrangeMDList();
		}
	}
}
function checkCDFields(){
	let multiple_cash_drawers = document.querySelector("#multiple_cash_drawers").checked;
	if(multiple_cash_drawers===true){
		let cdData = document.getElementsByName('cash_drawers[]');							
		let cdListData = new Array();
		let error_messageid = document.getElementById('errorCDListRow');
		error_messageid.innerHTML = '';
		
		for(let i = 0; i < cdData.length; i++) {
			let cdOneValue = cdData[i].value.toUpperCase();
			if (cdListData.length > 0 && cdListData.indexOf(cdOneValue) !== -1) {
				error_messageid.innerHTML = Translate('Duplicate Cash Drawer')+parseInt(i+1);
				cdData[i].focus();
				return false;
			}
			else {
				cdListData[i] = cdOneValue;
			}
		}
	}
	return true;
}
function addMoreCD(){
	if(checkCDFields()===false){return false;}
    let cdData = document.getElementsByName('cash_drawers[]');
    let i;
	i = 0;
	let error_messageid = document.getElementById('errorCDListRow');
	for(i = 0; i < cdData.length; i++){
		if(cdData[i].value===''){
			error_messageid.innerHTML = Translate('Cash Drawers')+' '+Translate('is missing.')+parseInt(i+1);
			cdData[i].focus();
			return false;
		}
	}
		
	let ulidname = 'cdListRow';
	let index = document.querySelector("ul#"+ulidname).childElementCount;
	index = parseInt(index+1);
	
	let newmore_list = cTag('li');
        const newCashDrawerName = cTag('div',{ "class":"flexSpaBetRow" });
            let newCashDrawerColumn = cTag('div',{ "class":"columnSM2 columnMD1 flexSpaBetRow" });
            newCashDrawerColumn.innerHTML = index;
        newCashDrawerName.appendChild(newCashDrawerColumn);
            let newCashDrawerValue = cTag('div',{ "class":"columnSM10 columnMD11" });
                let drawerInput = cTag('input',{ "type":"text","maxlength":"25","name":"cash_drawers[]","placeholder":Translate('New cash drawer name'),"alt":Translate('New cash drawer name'),"class":"form-control placeholder cash_drawers" });
                drawerInput.addEventListener('keydown',preventSpecialCharacter);
            newCashDrawerValue.appendChild(drawerInput);
        newCashDrawerName.appendChild(newCashDrawerValue);
    newmore_list.appendChild(newCashDrawerName);
								
	document.querySelector("#"+ulidname).appendChild(newmore_list);
	
	callPlaceholder();
	document.getElementsByName('cash_drawers[]')[parseInt(index-1)].focus();
	rearrangeMDList();
}
										
function rearrangeMDList(){
    let countList = document.querySelector("ul#cdListRow").childElementCount;	
    document.querySelectorAll('.cdOrderUp').forEach(item=>{
		item.remove();
	});
	if(countList > 0){
		document.querySelectorAll("ul#cdListRow li").forEach((item,indx)=>{
            let upArrow = cTag('a',{ "class":"cdOrderUp", "href":"javascript:void(0);","title":"Move to UP" });
            upArrow.appendChild(cTag('i',{ "class":"fa fa-arrow-up" }));
            upArrow.addEventListener('click',function(){
                let cac = [...document.querySelectorAll('.cdOrderUp')].indexOf(this);
                cdOrderUp(cac);
            })
            let node = item.querySelector('.flexSpaBetRow').querySelector('.columnMD1');
            node.innerHTML = '';
			node.append(indx+1);
			if(countList!==indx+1) node.appendChild(upArrow);
		});
	}
	
	document.querySelectorAll('.removeicon').forEach(item=>{
		item.remove();
	});
	
	if(countList>1){
		for(let l=1; l<countList; l++){													
            let a = cTag('a',{ "class":`removeicon`, "href":"javascript:void(0);", "title":Translate('Remove this row') });
            a.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Remove this row'),"title":Translate('Remove this row'),"src":"/assets/images/cross-on-white.gif" }))
            a.addEventListener('click',function(){
                if(document.querySelector("ul#cdListRow").childElementCount>1){
                    this.parentNode.remove();
                    rearrangeMDList();                   
                }
            })
            document.querySelector("ul#cdListRow li:nth-child("+l+")").appendChild(a);
		}
	}	
}
function preventSpecialCharacter(event){
    if(!/[\w\s]/.test(event.key)) event.preventDefault();
}
//========================Invoices====================//
function invoices_general(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Invoice Setup')} ${Translate('General')}`));
        const invoiceGeneralContainer = cTag('div',{class: "flexSpaBetRow"});
        invoiceGeneralContainer.appendChild(leftSideMenu());
            let invoiceGeneralColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDivStyle = "margin-top: 0; background: #fff;"
                if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
                let callOutDiv = cTag('div',{'class':"innerContainer bs-callout-info", 'style': callOutDivStyle });
                    let invoiceGeneralForm = cTag('form',{ "name":"frmgeneral","id":"frmgeneral","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                    invoiceGeneralForm.addEventListener('submit',event=>{
                        event.preventDefault();
                        AJsave_Invoice_Setup_general(0);
                    });
                        const invoiceNumberRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const invoiceNumberColumn = cTag('div',{ "class":"columnSM3" });
                                const invoiceLabel = cTag('label');
                                invoiceLabel.innerHTML = Translate('Next Invoice Number')+' :';
                            invoiceNumberColumn.appendChild(invoiceLabel);
                        invoiceNumberRow.appendChild(invoiceNumberColumn);
                            let invoiceNumberValue = cTag('div',{ "class":"columnSM4" });
                            invoiceNumberValue.appendChild(cTag('input',{ 'type':'number','min':'1',"name":"nextinvoiceno","id":"nextinvoiceno","maxlength":"8","blur":checkInvoiceNo,"class":"form-control" }));
                            invoiceNumberValue.appendChild(cTag('span',{ "id":"errmsg_nextinvoiceno","class":"errormsg" }));
                        invoiceNumberRow.appendChild(invoiceNumberValue);
                    invoiceGeneralForm.appendChild(invoiceNumberRow);
                    invoiceGeneralForm.appendChild(cTag('input',{ "type":"hidden","id":"nextinvoice_no","name":"nextinvoice_no" }));
                        const invoiceBackUpEmail = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const invoiceBackUpEmailColumn = cTag('div',{ "class":"columnSM3" });
                                const backUpEmailLabel = cTag('label',{ "for":"invoice_backup_email" });
                                backUpEmailLabel.innerHTML = Translate('Invoice Backup Email')+' :';
                            invoiceBackUpEmailColumn.appendChild(backUpEmailLabel);
                        invoiceBackUpEmail.appendChild(invoiceBackUpEmailColumn);
                            let invoiceBackUpEmailValue = cTag('div',{ "class":"columnSM4" });
                            invoiceBackUpEmailValue.appendChild(cTag('input',{ "maxlength":"50","type":"email","id":"invoice_backup_email","name":"invoice_backup_email","class":"form-control" }));
                        invoiceBackUpEmail.appendChild(invoiceBackUpEmailValue);
                    invoiceGeneralForm.appendChild(invoiceBackUpEmail);
                    invoiceGeneralForm.appendChild(invoice_FormRow(Translate('Printed Invoice'),'invoice_message',Translate('Invoice Below Message'),invoices_general_removeLogo,invoices_general_changeLogoPlacement));
                callOutDiv.appendChild(invoiceGeneralForm);
            invoiceGeneralColumn.appendChild(callOutDiv);
        invoiceGeneralContainer.appendChild(invoiceGeneralColumn);
    Dashboard.appendChild(invoiceGeneralContainer);
    AJ_invoices_general_MoreInfo();
}
async function AJ_invoices_general_MoreInfo(){
    const url = '/'+segment1+'/AJ_invoices_general_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let fragment = document.createDocumentFragment();
        let logoHTML;
        if(data.onePicture){
            logoHTML = cTag('div',{ "class":"currentPicture" });                    
            logoHTML.addEventListener('mouseenter',function(){
                let deletedicon = cTag('div',{ "class":"deletedicon","click":()=>AJremove_Picture(data.onePicture,'invoice_setup') });
                this.appendChild(deletedicon);
            })
            logoHTML.addEventListener('mouseleave',function(){
                this.querySelector('.deletedicon').remove();
            })
            logoHTML.appendChild(cTag('img',{ "class":"img-responsive",src:data.onePicture,alt:data.alt }));
        }else{
            logoHTML = document.createDocumentFragment();
            logoHTML.append(Translate('Upload Logo'));
            logoHTML.appendChild(cTag('br'));
                let uploadButton = cTag('button',{"type":"button", "class":"uploadButton", "name":"open", "click":()=>upload_dialog(Translate('Upload Invoice Logo'),'invoice_setup','app_logo_')});
                uploadButton.innerHTML = Translate('Upload')+'...';
            logoHTML.appendChild(uploadButton);
        }
        let customFieldsHTML = document.createDocumentFragment();
        let label, input, textarea, infoRightHeadRow, tdCol;
        if(data.customFields){
            for (const key in data.customFields) {
                    label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                    label.append(data.customFields[key][0]+'   ');
                        input = cTag('input',{ "type":"checkbox","name":"cf"+key,"value":"1" });
                        if(data.value[`cf${key}`]>0) input.checked = true;
                    label.appendChild(input);
                customFieldsHTML.appendChild(label)
                customFieldsHTML.appendChild(cTag('br'));
            }
        }
        if(data.logo_placement === 'Center'){
                const centerLogoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const emptyCloumn = cTag('div',{ "class":"columnSM2" });
                    emptyCloumn.innerHTML = ' ';
                centerLogoRow.appendChild(emptyCloumn);
                    const centerLogoColumn = cTag('div',{ "class":"columnSM8","align":"center" });
                        let centerLogo = cTag('div',{ 'style': "position: relative;", "id":"invoice_setup_picture" });                            
                        centerLogo.appendChild(logoHTML);
                    centerLogoColumn.appendChild(centerLogo);
                centerLogoRow.appendChild(centerLogoColumn);
            fragment.appendChild(centerLogoRow);
                const companyInfoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const companyInfoValue = cTag('div',{ "class":"columnSM12" });
                        textarea = cTag('textarea',{ "id":"company_info","rows":"4","name":"company_info","placeholder":Translate('Company Name, Address, Phone Number, Email, TAX ID'),"class":"form-control" });
                        textarea.innerHTML = data.company_info;
                        textarea.addEventListener('blur',sanitizer);
                    companyInfoValue.appendChild(textarea);
                companyInfoRow.appendChild(companyInfoValue);
            fragment.appendChild(companyInfoRow);
                const customerInfoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const customerInfoColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                        const customerInfoTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                            const customerInfoBody = cTag('tbody');
                                const customerHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                        const customerHeader = cTag('h4');
                                        customerHeader.innerHTML = Translate('Customer Information')+'  ';
                                    tdCol.appendChild(customerHeader);
                                customerHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ "width":"210" });
                                    [
                                        {label:Translate('Customer Name'),id:'customer_name'},
                                        {label:Translate('Customer Address'),id:'customer_address'},
                                        {label:Translate('Customer Phone'),id:'customer_phone'},
                                        {label:Translate('Secondary Phone'),id:'secondary_phone'},
                                        {label:Translate('Customer Email'),id:'customer_email'},
                                        {label:Translate('Customer Type'),id:'customer_type'},
                                    ].forEach(item=>{
                                            label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                            label.append(item.label+'   ');
                                                input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                                if(data[item.id] === 1) input.checked = true;
                                            label.appendChild(input);
                                        tdCol.appendChild(label);
                                        tdCol.appendChild(cTag('br'));
                                    })
                                        tdCol.appendChild(customFieldsHTML);
                                customerHeadRow.appendChild(tdCol);
                            customerInfoBody.appendChild(customerHeadRow);
                        customerInfoTable.appendChild(customerInfoBody);
                    customerInfoColumn.appendChild(customerInfoTable);
                customerInfoRow.appendChild(customerInfoColumn);
                    const invoiceDetailTableColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                        const invoiceDetailTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                            const invoiceDetailBody = cTag('tbody');
                                const invoiceHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                        const invoiceHeader = cTag('h4');
                                        invoiceHeader.innerHTML = Translate('Invoice Details')+'  ';
                                    tdCol.appendChild(invoiceHeader);
                                invoiceHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td');
                                    [
                                        {label:Translate('Sales Person'),id:'sales_person'},
                                        {label:Translate('Barcode (invoice number)'),id:'barcode'},
                                    ].forEach(item=>{
                                            label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                            label.append(item.label+'   ');
                                                input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                                if(data[item.id] === 1) input.checked = true;
                                            label.appendChild(input);
                                        tdCol.appendChild(label);
                                        tdCol.appendChild(cTag('br'));
                                    })
                                invoiceHeadRow.appendChild(tdCol);
                            invoiceDetailBody.appendChild(invoiceHeadRow);
                        invoiceDetailTable.appendChild(invoiceDetailBody);
                    invoiceDetailTableColumn.appendChild(invoiceDetailTable);
                customerInfoRow.appendChild(invoiceDetailTableColumn);
            fragment.appendChild(customerInfoRow);
        }
        else{
            const leftLogoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                const leftLogoColumn = cTag('div',{ "class":"columnSM2" });
                    const leftLogo = cTag('div',{ "id":"invoice_setup_picture", 'style': "position: relative;" });                            
                    leftLogo.appendChild(logoHTML);
                leftLogoColumn.appendChild(leftLogo);
            leftLogoRow.appendChild(leftLogoColumn);
                const leftLogoValue = cTag('div',{ "class":"columnSM4" });
                    textarea = cTag('textarea',{ "id":"company_info","rows":"4","name":"company_info","placeholder":Translate('Company Name, Address, Phone Number, Email, TAX ID'),"class":"form-control" });
                    textarea.innerHTML = data.company_info;
                    textarea.addEventListener('blur',sanitizer);
                leftLogoValue.appendChild(textarea);                    
            leftLogoRow.appendChild(leftLogoValue);
                const customerInfoRightColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                    const customerInfoRightTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                        const customerInfoRightBody = cTag('tbody');
                            infoRightHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                    const customerTitle = cTag('h4');
                                    customerTitle.innerHTML = Translate('Customer Information')+'  ';
                                tdCol.appendChild(customerTitle);
                            infoRightHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ "width":"210" });
                                [
                                    {label:Translate('Customer Name'),id:'customer_name'},
                                    {label:Translate('Customer Address'),id:'customer_address'},
                                    {label:Translate('Customer Phone'),id:'customer_phone'},
                                    {label:Translate('Secondary Phone'),id:'secondary_phone'},
                                    {label:Translate('Customer Email'),id:'customer_email'},
                                    {label:Translate('Customer Type'),id:'customer_type'},
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                                tdCol.appendChild(customFieldsHTML);
                            infoRightHeadRow.appendChild(tdCol);
                        customerInfoRightBody.appendChild(infoRightHeadRow);
                            infoRightHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "colspan":"2" });
                                tdCol.innerHTML = '&nbsp;';
                            infoRightHeadRow.appendChild(tdCol);
                        customerInfoRightBody.appendChild(infoRightHeadRow);
                            infoRightHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                    const invoiceTitle = cTag('h4');
                                    invoiceTitle.innerHTML = Translate('Invoice Details')+'  ';
                                tdCol.appendChild(invoiceTitle);
                            infoRightHeadRow.appendChild(tdCol);
                                tdCol = cTag('td');
                                [
                                    {label:Translate('Sales Person'),id:'sales_person'},
                                    {label:Translate('Barcode (invoice number)'),id:'barcode'},
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                            infoRightHeadRow.appendChild(tdCol);
                        customerInfoRightBody.appendChild(infoRightHeadRow);
                    customerInfoRightTable.appendChild(customerInfoRightBody);
                customerInfoRightColumn.appendChild(customerInfoRightTable);
            leftLogoRow.appendChild(customerInfoRightColumn);
        fragment.appendChild(leftLogoRow);
        }
        document.querySelector('#frmgeneral').insertBefore(fragment,document.querySelectorAll('.flexStartRow')[6]);
        document.querySelector('#nextinvoiceno').value = data.nextinvoice_no;
        document.querySelector('#invoice_backup_email').value = data.invoice_backup_email;
        document.getElementsByName('default_invoice_printer').forEach(item=>{
            if(item.value === data.default_invoice_printer) item.checked = true;
        })
        document.querySelector('#logo_size').value = data.logo_size;
        document.querySelector('#oldlogo_size').value = data.logo_size;
        document.querySelector('#title').value = data.title;
        document.querySelector('#logo_placement').value = data.logo_placement;
        document.querySelector('#oldlogo_placement').value = data.logo_placement;
        if(data.print_price_zero === 1) document.querySelector('#print_price_zero').checked = true;
        if(data.notes === 1) document.querySelector('#notes').checked = true;
        document.querySelector('#invoice_message').value = data.invoice_message;
        document.querySelector('#invoice_message_above').value = data.invoice_message_above;
        document.querySelector('#variables_id').value = data.variables_id;
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }  
        if(data.nextinvoice_no>1){
            document.querySelector('#submit').parentNode.appendChild(cTag('input',{ "class":"btn saveButton", 'style': "margin-left: 10px;", "name":"preview","id":"preview","type":"button","value":Translate('Save & Preview'),"click":()=>AJsave_Invoice_Setup_general(1) }));
        }
    }
}
function invoices_general_removeLogo(){
	if(document.querySelector("#logo_size").value !== document.querySelector("#oldlogo_size").value){
		if(document.querySelector("#invoice_setup_picture div") && document.querySelector("#invoice_setup_picture div").classList.contains('currentPicture')){
			let picturepath = document.querySelector(".currentPicture").querySelector("img").getAttribute('src');
			AJremove_Picture(picturepath, 'invoice_setup');
		}
		else{
			AJsave_Invoice_Setup_general(0);
		}
	}
} 
function invoices_general_changeLogoPlacement(){
	if(document.querySelector("#logo_placement").value !== document.querySelector("#oldlogo_placement").value){
		AJsave_Invoice_Setup_general(0);
	}
}
async function AJsave_Invoice_Setup_general(preview){
    if(segment2 == 'ordersPrint') return;
	if(checkInvoiceNo()===false){
		document.getElementById('nextinvoiceno').focus();
		return false;
	}
	const nextinvoiceno = parseInt(document.getElementById('nextinvoiceno').value);
	let nextinvoice_no = parseInt(document.getElementById('nextinvoice_no').value);
	if(nextinvoice_no==='' || isNaN(nextinvoice_no)){
		nextinvoice_no = 1;
		document.getElementById('nextinvoice_no').value = 1;
	}
	
	if(nextinvoiceno==='' || isNaN(nextinvoiceno)){
		document.getElementById('nextinvoiceno').value = nextinvoice_no;
	}	
	
	if(nextinvoiceno>nextinvoice_no){
		document.getElementById('nextinvoice_no').value = nextinvoiceno;
        nextinvoice_no = nextinvoiceno;
	}	
		
    const submitBtn = document.querySelector("#submit");
    const submitBtnVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);
	
    const jsonData = serialize("#frmgeneral");
    const url = '/'+segment1+'/AJsave_invoices_general';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){		
        if(data.savemsg !=='error' && data.id>0){
			document.getElementById("variables_id").value = data.id;
			document.querySelector("#oldlogo_size").value = document.querySelector("#logo_size").value;
			if(document.querySelector("#logo_placement").value !== document.querySelector("#oldlogo_placement").value){
				window.location = window.location.href;
			}
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));				
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing invoice setup information! Please try again.'));		
		}
        0 === document.getElementById("variables_id").value ? btnEnableDisable(submitBtn, submitBtnVal,false) : btnEnableDisable(submitBtn, submitBtnVal,false);
        if(preview>0){
            printbyurl('/Carts/cprints/large/'+(nextinvoice_no-1));
        }
    }
	return false;
}
function checkInvoiceNo(){
	let nextinvoiceno = parseInt(document.getElementById('nextinvoiceno').value);
	let nextinvoice_no = parseInt(document.getElementById('nextinvoice_no').value);
	if(nextinvoice_no==='' || isNaN(nextinvoice_no)){
		nextinvoice_no = 1;
		document.getElementById('nextinvoice_no').value = 1;
	}
	
	let errorid = document.getElementById("errmsg_nextinvoiceno");
	
	if(nextinvoiceno==='' || isNaN(nextinvoiceno)){
		errorid.innerHTML = Translate('Sorry, you must enter a valid invoice number');
		return false;
	}		
	else if(nextinvoiceno<nextinvoice_no){
		errorid.innerHTML = Translate('Sorry, you must enter a invoice number greater than')+parseInt(nextinvoice_no-1);
		return false;
	}		
	return true;
}
//========================customStatuses for Orders/Repairs====================//

function repairCustomStatuses(){
    customStatuses();
}

function customStatuses(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Custom Statuses')}`));
        const customStatusContainer = cTag('div',{class: "flexSpaBetRow"});
        customStatusContainer.appendChild(leftSideMenu());
            let customStatusColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDivStyle = "margin-top: 0; background: #fff;"
                if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
                let callOutDiv = cTag('div',{'class':"innerContainer",'style': callOutDivStyle});
                    let customStatusForm = cTag('form',{ "name":"frmgeneral","id":"frmgeneral","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                    customStatusForm.addEventListener('submit',AJsave_Order_customStatuses);
                        const customStatusRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const allStatusColumn = cTag('div',{ "class":"columnSM5 columnMD3" });
                                let statusLabel = cTag('label',{ "for":"repair_message" });
                                statusLabel.innerHTML = Translate('All Statuses');
                            allStatusColumn.appendChild(statusLabel);
                        customStatusRow.appendChild(allStatusColumn);
                            let statusField = cTag('div',{ "class":"columnSM7 columnMD9" });
                                let statusFieldRow = cTag('div',{ "class":"columnXS12 roundborder",style:'position:relative' });
                                    let statusName = cTag('div',{ "class":"flexSpaBetRow " });
                                        const statusTitle = cTag('div',{ "class":"columnSM6", 'style': `text-align: center;` });
                                        statusTitle.innerHTML = Translate('Custom Statuses');
                                    statusName.appendChild(statusTitle);
                                        const bgColorColumn = cTag('div',{ "class":"columnSM6", 'style': `text-align: center;` });
                                        bgColorColumn.innerHTML = Translate('Background Color');
                                    statusName.appendChild(bgColorColumn);
                                statusFieldRow.appendChild(statusName);
                                    const statusLists = cTag('ul',{ "id":"customStatusList","class":"plusIconPosition flexStartRow multipleRowList" });
                                        const addStatusDiv = cTag('div',{ "class":"addNewPlusIcon" });
                                            let addStatusLink = cTag('a',{ "href":"javascript:void(0);","title":Translate('Add Order status'),"click":addMoreStatus });
                                            addStatusLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Add Order status'),"title":Translate('Add Order status'),"src":"/assets/images/plus20x25.png" }));
                                        addStatusDiv.appendChild(addStatusLink);
                                    // statusLists.appendChild(addStatusDiv);
                                statusFieldRow.appendChild(statusLists);
                                statusFieldRow.appendChild(addStatusDiv);
                                statusFieldRow.appendChild(cTag('span',{ "id":"error_customStatusList","class":"errormsg" }));
                            statusField.appendChild(statusFieldRow);
                        customStatusRow.appendChild(statusField);
                    customStatusForm.appendChild(customStatusRow);
                        const buttonName = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                        buttonName.appendChild(cTag('div',{'class':'columnSM5 columnMD3'}));
                            let buttonTitle = cTag('div',{ "class":"columnSM7 columnMD9" });
                            buttonTitle.appendChild(cTag('input',{ "type":"hidden","name":"variables_id","id":"variables_id" }));
                            buttonTitle.appendChild(cTag('input',{ "class":"btn completeButton","name":"submit","id":"submit","type":"submit" }));
                        buttonName.appendChild(buttonTitle);                        
                    customStatusForm.appendChild(buttonName);                        
                callOutDiv.appendChild(customStatusForm);
            customStatusColumn.appendChild(callOutDiv);
        customStatusContainer.appendChild(customStatusColumn);
    Dashboard.appendChild(customStatusContainer);
    AJ_customStatuses_MoreInfo();
}
async function AJ_customStatuses_MoreInfo(){
    const url = `/${segment1}/AJ_${segment2}_MoreInfo`;

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        let Statuses = data[segment2==='customStatuses'?'order_statusesarray':'repairStatuses']
        if(segment2==='customStatuses'){
            Statuses = ['New','Quotes',...Statuses];
        }
        else if(segment2==='repairCustomStatuses'){
            Statuses = ['New','Finished',...Statuses];
        }
        
        let customStatusList = document.querySelector('#customStatusList');
        
        if(Statuses.length>0){
            Statuses.filter(status=>status!=='').forEach((status,index)=>{
                let bgColor = '#FFFFFF';
                if(data.statusColors.length>0 && data.statusColors[index] !== undefined) bgColor = data.statusColors[index];
                customStatusList.appendChild(listCreator(status,bgColor,index));
            })
        }                
        addMoreStatus();
        function listCreator(status,bgColor,serialNo){
            const statusName = segment2==='customStatuses'?'order_statuses[]':'repair_statuses[]';
            const readonly = ['New','Quotes','Finished'].includes(status);
            // let firstReadonlyStatus = (segment2==='customStatuses'&&serialNo===2)||(segment2==='repairCustomStatuses'&&serialNo===1);
            let firstReadonlyStatus = (serialNo===2);
            let height = 30;
            let list = cTag('li');
            if(readonly) list.classList.add('readonlyStatus')
                const statusField = cTag('div',{ "class":"flexSpaBetRow" });                    
                if(!readonly && !firstReadonlyStatus) statusField.appendChild(moveupArrow());
                else statusField.appendChild(cTag('div',{ "class":"columnSM1" }));
                    const statusColumn = cTag('div',{ "class":"columnSM6" });
                        let input = cTag('input',{ "type":"text","maxlength":"20","placeholder":`${Translate('Enter status')} ${serialNo}`,"alt":`${Translate('Enter status')} ${serialNo}`,"title":status,"name":statusName,"value":status,"class":"form-control order_statuses placeholder" });
                        if(readonly) input.setAttribute('readonly','');
                    statusColumn.appendChild(input);
                statusField.appendChild(statusColumn);
                    const statusColorColumn = cTag('div',{ "class":"columnSM5" });
                    statusColorColumn.appendChild(cTag('input',{ "type":"color","class":"statusescolor","maxlength":"7","name":"status_colors[]","value":bgColor,"style":`width: 100%; height: ${height}px; border: none` }));
                statusField.appendChild(statusColorColumn);
            list.appendChild(statusField);
            return list
        }
        document.querySelector('#variables_id').value = data.variables_id;            
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }
        makedeletedicon('customStatusList');
    }
}
function moveupArrow(){
    const statusName = segment2==='customStatuses'?'order_statuses[]':'repair_statuses[]';
    const arrow = cTag('i',{'class':'fa fa-arrow-up columnSM1',style:'text-align:right;transform: translateY(25%);cursor:pointer'});
    arrow.addEventListener('click',function(){
        const self = this.closest('li');
        const allStatuses = self.parentNode.querySelectorAll('li');
        const index = [...allStatuses].indexOf(self);
        const previousStatusField = allStatuses[index-1].querySelector(`[name="${statusName}"]`);
        const previousColorField = allStatuses[index-1].querySelector('[name="status_colors[]"]');
        const previousStatus = previousStatusField.value;
        const previousColor = previousColorField.value;
        const statusField = self.querySelector(`[name="${statusName}"]`);
        const colorField = self.querySelector('[name="status_colors[]"]');
        const status = statusField.value;
        const color = colorField.value;
        //
        statusField.value = previousStatus;
        colorField.value = previousColor;
        previousStatusField.value = status;
        previousColorField.value = color;
    });
    return arrow;
}
function checkOSFields(){
    const statusName = segment2==='customStatuses'?'order_statuses[]':'repair_statuses[]';
	let order_statusesarray = document.getElementsByName(statusName);							
	let order_statuses_listarray = new Array();
	let error_messageid = document.getElementById('error_customStatusList');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < order_statusesarray.length; i++) {
														
		if(order_statusesarray[i].value===''){
			error_messageid.innerHTML = Translate('Missing status')+parseInt(i+1);
			order_statusesarray[i].focus();
			return false;
		}
		else if(order_statusesarray[i].value==='Quotes' && i>1){
			error_messageid.innerHTML = Translate('Quotes status is not allowed')+' '+parseInt(i+1);
			order_statusesarray[i].value = '';
			order_statusesarray[i].focus();
			return false;
		}
		else if (order_statuses_listarray.length > 0 && order_statuses_listarray.indexOf(order_statusesarray[i].value) !== -1) {
			error_messageid.innerHTML = Translate('Duplicate status found')+parseInt(i+1);
			order_statusesarray[i].focus();
			return false;
		}
		else {
			order_statuses_listarray[i] = order_statusesarray[i].value;
		}
	}
	return true;
}
function addMoreStatus(){
	if(checkOSFields()===false){return false;}
	else{
        const statusName = segment2==='customStatuses'?'order_statuses[]':'repair_statuses[]';
		let ulidname = 'customStatusList';
		let index = document.querySelectorAll("ul#customStatusList li").length;
		index = parseInt(index+1)
		
		let newmore_list = cTag('li');
            let enterStatusRow = cTag('div',{ "class":"flexSpaBetRow" });
            enterStatusRow.appendChild(moveupArrow())
                let enterStatusColumn = cTag('div',{ "class":"columnSM6" });
                    let input = cTag('input',{ "type":"text","maxlength":"20","placeholder":`${Translate('Enter status')} ${index}`,"alt":`${Translate('Enter status')} ${index}`,"title":status,"name":statusName,"class":"form-control order_statuses placeholder" });
                enterStatusColumn.appendChild(input);
            enterStatusRow.appendChild(enterStatusColumn);
                let colorColumn = cTag('div',{ "class":"columnSM5" });
                colorColumn.appendChild(cTag('input',{ "type":"color","class":"statusescolor","maxlength":"7","name":"status_colors[]","value":'#FFFFFF',"style":'width: 100%; height: 30px; border: none' }));
            enterStatusRow.appendChild(colorColumn);
        newmore_list.appendChild(enterStatusRow);
									
		document.querySelector("#"+ulidname).appendChild(newmore_list);
		
		callPlaceholder();
		
		document.getElementsByName(statusName)[parseInt(index-1)].focus();
		makedeletedicon('customStatusList');
	}
}
async function AJsave_Order_customStatuses(event){
    event.preventDefault();

    const statusName = segment2==='customStatuses'?'order_statuses[]':'repair_statuses[]';
	let order_statusesarray = document.getElementsByName(statusName);							
	let order_statuses_listarray = new Array();
	let error_messageid = document.getElementById('error_customStatusList');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < order_statusesarray.length; i++) {
														
		if(order_statusesarray[i].value !==''){
			if (order_statuses_listarray.length > 0 && order_statuses_listarray.indexOf(order_statusesarray[i].value) !== -1) {
				error_messageid.innerHTML = Translate('Duplicate order status');
				order_statusesarray[i].focus();
				return false;
			}								
			else if(order_statusesarray[i].value===Translate('Cancelled')){
				error_messageid.innerHTML = Translate('Cancelled is not allowed');
				order_statusesarray[i].focus();
				return false;
			}
			else {
				order_statuses_listarray[i] = order_statusesarray[i].value;
			}
		}
	}
    let submitBtn = document.querySelector("#submit");
    let submitBtnVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'),true);	
    const jsonData = serialize("#frmgeneral");
    // const url = '/'+segment1+'/AJsave_customStatuses';
    const url = `/${segment1}/AJsave_${segment2}`;
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error'){
			document.getElementById("variables_id").value = data.id;
            showTopMessage('success_msg',data.message);
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
            else if(data.id===0) showTopMessage('alert_msg',Translate('There is no post data found.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing repair setup information! Please try again.'));
		}
        btnEnableDisable(submitBtn,submitBtnVal,false);
    }
	return false;
}
//=================ordersPrint==============
function ordersPrint(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Orders Print')}`));
        const orderPrintContainer = cTag('div',{class: "flexSpaBetRow"});
        orderPrintContainer.appendChild(leftSideMenu());
            let orderPrintColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDivStyle = "margin-top: 0; background: #fff;"
                if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
                let callOutDiv = cTag('div',{'class':"innerContainer bs-callout-info",'style': callOutDivStyle});
                    const orderPrintForm = cTag('form',{ "name":"frmordersPrint","id":"frmordersPrint","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                    orderPrintForm.addEventListener('submit',event=>{
                        event.preventDefault();
                        AJsave_ordersPrint(0);
                    });
                    orderPrintForm.appendChild(invoice_FormRow(Translate('Printed Invoice'),'invoice_message',Translate('Invoice Below Message'),ordersPrint_removeLogo,ordersPrint_changeLogoPlacement));   
                callOutDiv.appendChild(orderPrintForm);
            orderPrintColumn.appendChild(callOutDiv);
        orderPrintContainer.appendChild(orderPrintColumn);
    Dashboard.appendChild(orderPrintContainer);
    AJ_ordersPrint_MoreInfo()
}
async function AJ_ordersPrint_MoreInfo(){
    const url = '/'+segment1+'/AJ_ordersPrint_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let label, input, textarea, tdCol, customerInfoHeadRow;
        let fragment = document.createDocumentFragment();
        let logoHTML;
        if(data.onePicture){
            logoHTML = cTag('div',{ "class":"currentPicture" });
            logoHTML.addEventListener('mouseenter',function(){
                let deletedicon = cTag('div',{ "class":"deletedicon","click":()=>AJremove_Picture(data.onePicture,'invoice_setup') });
                this.appendChild(deletedicon);
            })
            logoHTML.addEventListener('mouseleave',function(){
                this.querySelector('.deletedicon').remove();
            })
            logoHTML.appendChild(cTag('img',{ "class":"img-responsive",src:data.onePicture,alt:data.alt }));
        }
        else{
            logoHTML = document.createDocumentFragment();
            logoHTML.append(Translate('Upload Logo'));
            logoHTML.appendChild(cTag('br'));
                let uploadButton = cTag('button',{"type":"button", "class":"uploadButton", "name":"open", "click":()=>upload_dialog(Translate('Upload Invoice Logo'),'invoice_setup','app_logo_')});
                uploadButton.innerHTML = Translate('Upload')+'...';
            logoHTML.appendChild(uploadButton);
        }
        let customFieldsHTML = document.createDocumentFragment();
        if(data.customFields){
            for (const key in data.customFields) {
                    label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                    label.append(data.customFields[key][0]+'   ');
                        input = cTag('input',{ "type":"checkbox","name":"cf"+key,"value":"1" });
                        if(data.value[`cf${key}`]>0) input.checked = true;
                    label.appendChild(input);
                customFieldsHTML.appendChild(label)
                customFieldsHTML.appendChild(cTag('br'));
            }
        }
        if(data.logo_placement === 'Center'){
                const logoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const emptyColumn = cTag('div',{ "class":"columnSM2" });
                    emptyColumn.innerHTML = ' ';
                logoRow.appendChild(emptyColumn);
                    const logoColumn = cTag('div',{ "class":"columnSM8","align":"center" });
                        let logoPicture = cTag('div',{ 'style': "position: relative;", "id":"invoice_setup_picture" });                            
                        logoPicture.appendChild(logoHTML);
                    logoColumn.appendChild(logoPicture);
                logoRow.appendChild(logoColumn);
            fragment.appendChild(logoRow);
                const companyInfo = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;"});
                    const companyInfoColumn = cTag('div',{ "class":"columnSM12" });
                        textarea = cTag('textarea',{ "id":"company_info","rows":"4","name":"company_info","placeholder":Translate('Company Name, Address, Phone Number, Email, TAX ID'),"class":"form-control" });
                        textarea.innerHTML = data.company_info;
                        textarea.addEventListener('blur',sanitizer);
                    companyInfoColumn.appendChild(textarea);
                companyInfo.appendChild(companyInfoColumn);
            fragment.appendChild(companyInfo);
                const customerInformationRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const customerInformationColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                        const customerInfoTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                            const customerInfoBody = cTag('tbody');
                                const customerHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                        const customerInfoHeader = cTag('h4');
                                        customerInfoHeader.innerHTML = Translate('Customer Information')+'  ';
                                    tdCol.appendChild(customerInfoHeader);
                                customerHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ "width":"210" });
                                    [
                                        {label:Translate('Customer Name'),id:'customer_name'},
                                        {label:Translate('Customer Address'),id:'customer_address'},
                                        {label:Translate('Customer Phone'),id:'customer_phone'},
                                        {label:Translate('Secondary Phone'),id:'secondary_phone'},
                                        {label:Translate('Customer Email'),id:'customer_email'},
                                        {label:Translate('Customer Type'),id:'customer_type'},
                                    ].forEach(item=>{
                                            label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                            label.append(item.label+'   ');
                                                input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                                if(data[item.id] === 1) input.checked = true;
                                            label.appendChild(input);
                                        tdCol.appendChild(label);
                                        tdCol.appendChild(cTag('br'));
                                    })
                                    tdCol.appendChild(customFieldsHTML);
                                customerHeadRow.appendChild(tdCol);
                            customerInfoBody.appendChild(customerHeadRow);
                        customerInfoTable.appendChild(customerInfoBody);
                    customerInformationColumn.appendChild(customerInfoTable);
                customerInformationRow.appendChild(customerInformationColumn);
                    const invoiceDetailColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                        const invoiceDetailTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                            const invoiceDetailBody = cTag('tbody');
                                const invoiceHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                        const invoiceHeader = cTag('h4');
                                        invoiceHeader.innerHTML = Translate('Invoice Details')+'  ';
                                    tdCol.appendChild(invoiceHeader);
                                invoiceHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td');
                                    [
                                        {label:Translate('Sales Person'),id:'sales_person'},
                                        {label:Translate('Barcode (invoice number)'),id:'barcode'},
                                    ].forEach(item=>{
                                            label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                            label.append(item.label+'   ');
                                                input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                                if(data[item.id] === 1) input.checked = true;
                                            label.appendChild(input);
                                        tdCol.appendChild(label);
                                        tdCol.appendChild(cTag('br'));
                                    })
                                invoiceHeadRow.appendChild(tdCol);
                            invoiceDetailBody.appendChild(invoiceHeadRow);
                        invoiceDetailTable.appendChild(invoiceDetailBody);
                    invoiceDetailColumn.appendChild(invoiceDetailTable);
                customerInformationRow.appendChild(invoiceDetailColumn);
            fragment.appendChild(customerInformationRow);
        }
        else{
            const leftLogoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                const leftLogoColumn = cTag('div',{ "class":"columnSM2" });
                    let invoiceLeftImg = cTag('div',{ "id":"invoice_setup_picture", 'style': "position: relative;" });
                    invoiceLeftImg.appendChild(logoHTML);
                leftLogoColumn.appendChild(invoiceLeftImg);
            leftLogoRow.appendChild(leftLogoColumn);
                const companyAdressColumn = cTag('div',{ "class":"columnSM4" });
                    textarea = cTag('textarea',{ "id":"company_info","rows":"4","name":"company_info","placeholder":Translate('Company Name, Address, Phone Number, Email, TAX ID'),"class":"form-control" });
                    textarea.innerHTML = data.company_info;
                    textarea.addEventListener('blur',sanitizer);
                companyAdressColumn.appendChild(textarea);
            leftLogoRow.appendChild(companyAdressColumn);
                const customerInfoColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                    const customerInfoTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                        const customerInfoBody = cTag('tbody');
                            customerInfoHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                    const customerInfoHeader = cTag('h4');
                                    customerInfoHeader.innerHTML = Translate('Customer Information')+'  ';
                                tdCol.appendChild(customerInfoHeader);
                            customerInfoHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ "width":"210" });
                                [
                                    {label:Translate('Customer Name'),id:'customer_name'},
                                    {label:Translate('Customer Address'),id:'customer_address'},
                                    {label:Translate('Customer Phone'),id:'customer_phone'},
                                    {label:Translate('Secondary Phone'),id:'secondary_phone'},
                                    {label:Translate('Customer Email'),id:'customer_email'},
                                    {label:Translate('Customer Type'),id:'customer_type'},
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                                tdCol.appendChild(customFieldsHTML);
                            customerInfoHeadRow.appendChild(tdCol);
                        customerInfoBody.appendChild(customerInfoHeadRow);
                            customerInfoHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "colspan":"2" });
                                tdCol.innerHTML = '&nbsp;';
                            customerInfoHeadRow.appendChild(tdCol);
                        customerInfoBody.appendChild(customerInfoHeadRow);
                            customerInfoHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                    const invoiceDetailHeader = cTag('h4');
                                    invoiceDetailHeader.innerHTML = Translate('Invoice Details')+'  ';
                                tdCol.appendChild(invoiceDetailHeader);
                            customerInfoHeadRow.appendChild(tdCol);
                                tdCol = cTag('td');
                                [
                                    {label:Translate('Sales Person'),id:'sales_person'},
                                    {label:Translate('Barcode (invoice number)'),id:'barcode'},
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                            customerInfoHeadRow.appendChild(tdCol);
                        customerInfoBody.appendChild(customerInfoHeadRow);
                    customerInfoTable.appendChild(customerInfoBody);
                customerInfoColumn.appendChild(customerInfoTable);
            leftLogoRow.appendChild(customerInfoColumn);
        fragment.appendChild(leftLogoRow);
        }
        document.querySelector('#frmordersPrint').insertBefore(fragment,document.querySelectorAll('.flexStartRow')[4]);
        
        document.getElementsByName('default_invoice_printer').forEach(item=>{
            if(item.value === data.default_invoice_printer) item.checked = true;
        })
        document.querySelector('#logo_size').value = data.logo_size;
        document.querySelector('#oldlogo_size').value = data.logo_size;
        document.querySelector('#title').value = data.title;
        document.querySelector('#logo_placement').value = data.logo_placement;
        document.querySelector('#oldlogo_placement').value = data.logo_placement;
        if(data.print_price_zero === 1) document.querySelector('#print_price_zero').checked = true;
        if(data.notes === 1) document.querySelector('#notes').checked = true;
        document.querySelector('#invoice_message').value = data.invoice_message;
        document.querySelector('#invoice_message_above').value = data.invoice_message_above;
        document.querySelector('#variables_id').value = data.variables_id;
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }  
        if(data.lastOrderNo>1){
            document.querySelector('#submit').parentNode.appendChild(cTag('input',{ "class":"btn saveButton", 'style': "margin-left: 10px;", "name":"preview","id":"preview","type":"button","value":Translate('Save & Preview'),"click":()=>AJsave_ordersPrint(data.lastOrderNo) }));
        }
    }
}
function ordersPrint_removeLogo(){
	if(document.querySelector("#logo_size").value !== document.querySelector("#oldlogo_size").value){
        if(document.querySelector("#invoice_setup_picture div") && document.querySelector("#invoice_setup_picture div").classList.contains('currentPicture')){
			let picturepath = document.querySelector(".currentPicture").querySelector("img").getAttribute('src');
			AJremove_Picture(picturepath, 'invoice_setup');
		}
		else{
			AJsave_ordersPrint(0);
		}
	}
}
function ordersPrint_changeLogoPlacement(){
	if(document.querySelector("#logo_placement").value !== document.querySelector("#oldlogo_placement").value){
		AJsave_ordersPrint(0);
	}
}
async function AJsave_ordersPrint(lastOrderNo){
	const submitBtn = document.querySelector("#submit");
    const submitBtnVal = submitBtn.value;
    btnEnableDisable(submitBtn,Translate('Saving'), true);
    const jsonData = serialize("#frmordersPrint");
    const url = '/'+segment1+'/AJsave_ordersPrint';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){ 
        if(data.savemsg !=='error' && data.id>0){
			document.getElementById("variables_id").value = data.id;
			document.querySelector("#oldlogo_size").value = document.querySelector("#logo_size").value;
			if(document.querySelector("#logo_placement").value !== document.querySelector("#oldlogo_placement").value){
				window.location = window.location.href;
			}
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));		
		}
		else{
            showTopMessage('alert_msg',Translate('Changing orders print'));
		}
		btnEnableDisable(submitBtn, submitBtnVal, false);
        if(lastOrderNo>0){
            printbyurl('/Orders/prints/large/'+lastOrderNo);
        }
    }
	return false;
}
//-------------------General----------------------
function repairs_general(){
    let repair_sort = {
        '0':Translate('First Name'),
        '1': Translate('Last Name'),
        '2':Translate('Due Date'),
        '3':Translate('Last Update'),
        '4':Translate('Ticket Number'),
        '5':Translate('Ticket Number DESC'),
        '6':Translate('TStatus'),
        '7':Translate('Problem'),
        '8':Translate('Tech Assigned')
    }
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Repairs')} ${Translate('General')}`));
        const repairGeneralContainer = cTag('div',{class: "flexSpaBetRow"});
        repairGeneralContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let repairGeneralColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDiv = cTag('div',{'class':"innerContainer bs-callout-info", 'style': callOutDivStyle });
                    let repairGeneralForm = cTag('form',{ "name":"frmgeneral","id":"frmgeneral","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                    repairGeneralForm.addEventListener('submit',event=>{
                        event.preventDefault();
                        AJsave_Settings_repairs_general(0);
                    });
                        const nextRepairNoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const nextRepairNoColumn = cTag('div',{ "class":"columnXS6 columnSM5 columnMD3" });
                                const nextRepairLabel = cTag('label',{ "for":"nextrepairticketno" });
                                nextRepairLabel.append(Translate('Next Repair Number'));
                                    let requiredField = cTag('span',{ "class":"required" });
                                    requiredField.innerHTML = '*';
                                nextRepairLabel.appendChild(requiredField);
                            nextRepairNoColumn.appendChild(nextRepairLabel);
                        nextRepairNoRow.appendChild(nextRepairNoColumn);
                            const nextRepairValue = cTag('div',{ "class":"columnXS6 columnSM5 columnMD4" });
                            nextRepairValue.appendChild(cTag('input',{ "name":"nextrepairticketno","id":"nextrepairticketno","maxlength":"8","blur":checkTicketNo,"class":"form-control", }));
                            nextRepairValue.appendChild(cTag('input',{ "type":"hidden","id":"nextrepairticket_no","name":"nextrepairticket_no", }));
                        nextRepairNoRow.appendChild(nextRepairValue);
                            const errorMessage = cTag('div',{ "class":"columnSM2 columnMD5" });
                            errorMessage.appendChild(cTag('span',{ "id":"errmsg_nextrepairticketno","class":"errormsg", }));
                        nextRepairNoRow.appendChild(errorMessage);
                    repairGeneralForm.appendChild(nextRepairNoRow);
                        const repairSortRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const repairSortColumn = cTag('div',{ "class":"columnXS6 columnSM5 columnMD3" });
                                const repairSortLabel = cTag('label',{ "for":"repair_message" });
                                repairSortLabel.innerHTML = Translate('Repair Sort');
                            repairSortColumn.appendChild(repairSortLabel);
                        repairSortRow.appendChild(repairSortColumn);
                            const repairSortDropDown = cTag('div',{ "class":"columnXS6 columnSM7 columnMD6" });
                                let selectRepairSort = cTag('select',{ "class":"form-control","name":"repair_sort","id":"repair_sort" });
                                setOptions(selectRepairSort,repair_sort,1,0);
                            repairSortDropDown.appendChild(selectRepairSort);
                        repairSortRow.appendChild(repairSortDropDown);
                    repairGeneralForm.appendChild(repairSortRow);
                        const listRepairStatusRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;display:none" });
                            const listRepairStatusColumn = cTag('div',{ "class":"columnSM5 columnMD3" });
                                const listRepairLabel = cTag('label',{ "for":"repair_message" });
                                listRepairLabel.innerHTML = Translate('List of Repair Statuses');
                            listRepairStatusColumn.appendChild(listRepairLabel);
                        listRepairStatusRow.appendChild(listRepairStatusColumn);
                            const listRepairColorRow = cTag('div',{ "class":"flexSpaBetRow columnSM7 columnMD9" });
                                let listRepairColorColumn = cTag('div',{ "class":"columnXS12 plusIconPosition roundborder" });
                                    let listRepairColor = cTag('div',{ "class":"flexSpaBetRow" });
                                        const statusTitle = cTag('div',{ "class":"columnSM6", 'style': `text-align: center;` });
                                        statusTitle.innerHTML = Translate('List of Repair Statuses');
                                    listRepairColor.appendChild(statusTitle);
                                        const colorTitle = cTag('div',{ "class":"columnSM6", 'style': `text-align: center;` });
                                        colorTitle.innerHTML = Translate('Background Color');
                                    listRepairColor.appendChild(colorTitle);
                                listRepairColorColumn.appendChild(listRepairColor);
                                listRepairColorColumn.appendChild(cTag('ul',{ "id":"rsListRow","class":"multipleRowList", }));
                                listRepairColorColumn.appendChild(cTag('span',{ "id":"error_rsListRow","class":"errormsg", }));
                                    let addListRepair = cTag('div',{ "class":"addNewPlusIcon" });
                                        let addListRepairLink = cTag('a',{ "href":"javascript:void(0);","title":Translate('Add More List of Repair Status'),"click":addMoreRS });
                                        addListRepairLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Add More List of Repair Status'),"title":Translate('Add More List of Repair Status'),"src":"/assets/images/plus20x25.png", }));
                                    addListRepair.appendChild(addListRepairLink);
                                listRepairColorColumn.appendChild(addListRepair);
                            listRepairColorRow.appendChild(listRepairColorColumn);
                        listRepairStatusRow.appendChild(listRepairColorRow);
                    repairGeneralForm.appendChild(listRepairStatusRow);
                    repairGeneralForm.appendChild(invoice_FormRow(Translate('Printed Repair Ticket'),'repair_message',Translate('Repair Below Message'),repairs_general_removeLogo,repairs_general_changeLogoPlacement));                    
                callOutDiv.appendChild(repairGeneralForm);
            repairGeneralColumn.appendChild(callOutDiv);
        repairGeneralContainer.appendChild(repairGeneralColumn);
    Dashboard.appendChild(repairGeneralContainer);
    AJ_repairs_general_MoreInfo();
}
async function AJ_repairs_general_MoreInfo(){
    const url = '/'+segment1+'/AJ_repairs_general_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        //---------Repair_Statuses-------------
        let newColor, input, label, textarea, tdCol, customerHeadRow;
        let rs = 1;
        let rsListRow = document.querySelector('#rsListRow');
        [
            { status:'New', bgColor:data.statusColors[0] },
            { status:'Finished', bgColor:data.statusColors[1] }
        ].forEach(item=>{
            rsListRow.appendChild(listCreator(item.status,item.bgColor,1));
        })
        if(data.repairStatuses.length>0){
            let sl = 0;
            data.repairStatuses.forEach(status=>{
                if(!['New', 'Finished'].includes(status) && status !=='' && data.repairStatuses.hasOwnProperty(sl)){
                    let newStatus = data.repairStatuses[sl];
                    if(data.statusColors.hasOwnProperty(sl))
                        newColor = data.statusColors[sl];
                    else
                        newColor = '#FFFFFF';
                    rsListRow.appendChild(listCreator(newStatus, newColor));
                }
                sl++;
            })
        }                
        rsListRow.appendChild(listCreator('', '#FFFFFF'));
        function listCreator(status, bgColor, readonly_0_1=0){
            let height = 30;
            let list = cTag('li');
                const newRepairRow = cTag('div',{ "class":"flexSpaBetRow" });
                    const newRepairColumn = cTag('div',{ "class":"columnSM6" });
                        input = cTag('input',{ "type":"text","maxlength":"20","placeholder":`${Translate('Enter new repair status')} ${rs}`,"alt":`${Translate('Enter new repair status')} ${rs}`,"title":status,"name":"repair_statuses[]","value":status,"class":"form-control order_statuses placeholder" });
                        if(readonly_0_1 === 1) input.setAttribute('readonly','');
                    newRepairColumn.appendChild(input);
                newRepairRow.appendChild(newRepairColumn);
                    const newRepairValue = cTag('div',{ "class":"columnSM6" });
                    newRepairValue.appendChild(cTag('input',{ "type":"color","class":"statusescolor","maxlength":"7","name":"status_colors[]","value":bgColor,"style":`width: 100%; height: ${height}px; border: none` }));
                newRepairRow.appendChild(newRepairValue);
            list.appendChild(newRepairRow);
            rs++;
            return list
        }
        makedeletedicon('rsListRow');
        let fragment = document.createDocumentFragment();
        let logoHTML;
        if(data.onePicture){
            logoHTML = cTag('div',{ "class":"currentPicture" });                    
            logoHTML.addEventListener('mouseenter',function(){
                let deletedicon = cTag('div',{ "class":"deletedicon","click":()=>AJremove_Picture(data.onePicture,'invoice_setup') });
                this.appendChild(deletedicon);
            })
            logoHTML.addEventListener('mouseleave',function(){
                this.querySelector('.deletedicon').remove();
            })
            logoHTML.appendChild(cTag('img',{ "class":"img-responsive",src:data.onePicture,alt:data.alt }));
        }else{
            logoHTML = document.createDocumentFragment();
            logoHTML.append(Translate('Upload Logo'));
            logoHTML.appendChild(cTag('br'));
                let button = cTag('button',{"type":"button", "class":"uploadButton", "name":"open", "click":()=>upload_dialog(Translate('Upload Invoice Logo'),'invoice_setup','app_logo_')});
                button.innerHTML = Translate('Upload')+'...';
            logoHTML.appendChild(button);
        }
        let customFieldsHTML = document.createDocumentFragment();
        if(data.customFields){
            for (const key in data.customFields) {
                    label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                    label.append(data.customFields[key][0]+'   ');
                        input = cTag('input',{ "type":"checkbox","name":"cf"+key,"value":"1" });
                        if(data.value[`cf${key}`]>0) input.checked = true;
                    label.appendChild(input);
                customFieldsHTML.appendChild(label)
                customFieldsHTML.appendChild(cTag('br'));
            }
        }
        let customFieldsRepairHTML = document.createDocumentFragment();
        if(data.customFieldsRepair){
            for (const key in data.customFieldsRepair) {
                    label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                    label.append(data.customFieldsRepair[key][0]+'   ');
                        input = cTag('input',{ "type":"checkbox","name":"cf"+key,"value":"1" });
                        if(data.value[`cf${key}`]>0) input.checked = true;
                    label.appendChild(input);
                customFieldsRepairHTML.appendChild(label)
                customFieldsRepairHTML.appendChild(cTag('br'));
            }
        }
        if(data.logo_placement === 'Center'){
                const centerLogoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const emptyColumn = cTag('div',{ "class":"columnSM2" });
                    emptyColumn.innerHTML = ' ';
                centerLogoRow.appendChild(emptyColumn);
                    const centerLogoColumn = cTag('div',{ "class":"columnSM8","align":"center" });
                        const centerLogoImg = cTag('div',{ 'style': "position: relative;", "id":"invoice_setup_picture" });                            
                        centerLogoImg.appendChild(logoHTML);
                    centerLogoColumn.appendChild(centerLogoImg);
                centerLogoRow.appendChild(centerLogoColumn);
            fragment.appendChild(centerLogoRow);
                const companyInfoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const companyInfoColumn = cTag('div',{ "class":"columnSM12" });
                        textarea = cTag('textarea',{ "id":"company_info","rows":"4","name":"company_info","placeholder":Translate('Company Name, Address, Phone Number, Email, TAX ID'),"class":"form-control" });
                        textarea.innerHTML = data.company_info;
                        textarea.addEventListener('blur',sanitizer);
                    companyInfoColumn.appendChild(textarea);
                companyInfoRow.appendChild(companyInfoColumn);
            fragment.appendChild(companyInfoRow);
                const customerInfoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                    const customerInfoColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                        const customerInfoTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                            const customerInfoBody = cTag('tbody');
                                const customerInfoHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                        const customerInfoTitle = cTag('h4');
                                        customerInfoTitle.innerHTML = Translate('Customer Information')+'  ';
                                    tdCol.appendChild(customerInfoTitle);
                                customerInfoHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ "width":"210" });
                                    [
                                        {label:Translate('Customer Name'),id:'customer_name'},
                                        {label:Translate('Customer Address'),id:'customer_address'},
                                        {label:Translate('Customer Phone'),id:'customer_phone'},
                                        {label:Translate('Secondary Phone'),id:'customer_secondary_phone'},
                                        {label:Translate('Customer Email'),id:'customer_email'},
                                        {label:Translate('Customer Type'),id:'customer_type'},
                                    ].forEach(item=>{
                                            label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                            label.append(item.label+'   ');
                                                input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                                if(data[item.id] === 1) input.checked = true;
                                            label.appendChild(input);
                                        tdCol.appendChild(label);
                                        tdCol.appendChild(cTag('br'));
                                    })
                                    tdCol.appendChild(customFieldsHTML);
                                customerInfoHeadRow.appendChild(tdCol);
                            customerInfoBody.appendChild(customerInfoHeadRow);
                        customerInfoTable.appendChild(customerInfoBody);
                    customerInfoColumn.appendChild(customerInfoTable);
                customerInfoRow.appendChild(customerInfoColumn);
                    const ticketDetailColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                        const ticketDetailTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                            const ticketDetailBody = cTag('tbody');
                                const ticketDetailHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                        const ticketTitle = cTag('h4');
                                        ticketTitle.innerHTML = Translate('Ticket Details')+'  ';
                                    tdCol.appendChild(ticketTitle);
                                ticketDetailHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td');
                                    [
                                        {label:Translate('Sales Person'),id:'sales_person'},
                                        {label:Translate('Barcode (invoice number)'),id:'barcode'},
                                        {label:Translate('Status'),id:'status'},
                                        {label:Translate('Due Date and Time'),id:'duedatetime'},
                                    ].forEach(item=>{
                                            label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                            label.append(item.label+'   ');
                                                input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                                if(data[item.id] === 1) input.checked = true;
                                            label.appendChild(input);
                                        tdCol.appendChild(label);
                                        tdCol.appendChild(cTag('br'));
                                    })
                                ticketDetailHeadRow.appendChild(tdCol);
                            ticketDetailBody.appendChild(ticketDetailHeadRow);
                        ticketDetailTable.appendChild(ticketDetailBody);
                    ticketDetailColumn.appendChild(ticketDetailTable);
                customerInfoRow.appendChild(ticketDetailColumn);
            fragment.appendChild(customerInfoRow);
        }
        else{
            const leftLogoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                const leftLogoColumn = cTag('div',{ "class":"columnSM2" });
                    const leftLogoImg = cTag('div',{ 'style': "position: relative;", "id":"invoice_setup_picture" });                            
                    leftLogoImg.appendChild(logoHTML);
                leftLogoColumn.appendChild(leftLogoImg);
            leftLogoRow.appendChild(leftLogoColumn);
                const addressColumn = cTag('div',{ "class":"columnSM4" });
                    textarea = cTag('textarea',{ "id":"company_info","rows":"4","name":"company_info","placeholder":Translate('Company Name, Address, Phone Number, Email, TAX ID'),"class":"form-control" });
                    textarea.innerHTML = data.company_info;
                    textarea.addEventListener('blur',sanitizer);
                addressColumn.appendChild(textarea);                    
            leftLogoRow.appendChild(addressColumn);
                const customerInformationDiv = cTag('div',{ "class":"columnSM6", 'style': "text-align: right;" });
                    const customerInformationTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                        const customerInformationBody = cTag('tbody');
                            customerHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                    const infoHeader = cTag('h4');
                                    infoHeader.innerHTML = Translate('Customer Information')+'  ';
                                tdCol.appendChild(infoHeader);
                            customerHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ "width":"210" });
                                [
                                    {label:Translate('Customer Name'),id:'customer_name'},
                                    {label:Translate('Customer Address'),id:'customer_address'},
                                    {label:Translate('Customer Phone'),id:'customer_phone'},
                                    {label:Translate('Secondary Phone'),id:'customer_secondary_phone'},
                                    {label:Translate('Customer Email'),id:'customer_email'},
                                    {label:Translate('Customer Type'),id:'customer_type'},
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                                tdCol.appendChild(customFieldsHTML);
                            customerHeadRow.appendChild(tdCol);
                        customerInformationBody.appendChild(customerHeadRow);
                            customerHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "colspan":"2" });
                                tdCol.innerHTML = '&nbsp;';
                            customerHeadRow.appendChild(tdCol);
                        customerInformationBody.appendChild(customerHeadRow);
                            customerHeadRow = cTag('tr');
                                tdCol = cTag('td',{ "align":"right", 'style': "border-right: 1px solid #CCC;" });
                                    const ticketHeader = cTag('h4');
                                    ticketHeader.innerHTML = Translate('Ticket Details')+'  ';
                                tdCol.appendChild(ticketHeader);
                            customerHeadRow.appendChild(tdCol);
                                tdCol = cTag('td');
                                [
                                    {label:Translate('Sales Person'),id:'sales_person'},
                                    {label:Translate('Barcode (invoice number)'),id:'barcode'},
                                    {label:Translate('Status'),id:'status'},
                                    {label:Translate('Due Date and Time'),id:'duedatetime'},
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                            customerHeadRow.appendChild(tdCol);
                        customerInformationBody.appendChild(customerHeadRow);
                    customerInformationTable.appendChild(customerInformationBody);
                customerInformationDiv.appendChild(customerInformationTable);
            leftLogoRow.appendChild(customerInformationDiv);
        fragment.appendChild(leftLogoRow);
        }
            const ticketInfoRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                const ticketInfoColumn = cTag('div',{ "class":"columnSM12" });
                    const ticketInfoTable = cTag('table',{ "width":"100%","border":"0","cellspacing":"0","cellpadding":"0" });
                        const ticketInfoBody = cTag('tbody');
                            const ticketHeaderRow = cTag('tr');
                                tdCol = cTag('td',{ "align":"left", 'style': "border-right: 1px solid #CCC;", "width":"47%" });
                                    const ticketInfoTitle = cTag('h4');
                                    ticketInfoTitle.innerHTML = Translate('Ticket Information');
                                tdCol.appendChild(ticketInfoTitle);
                                [
                                    {label:Translate('Technician'),id:'technician'},
                                    {label:Translate('Problem'),id:'short_description'}
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                                tdCol.appendChild(customFieldsRepairHTML);
                            ticketHeaderRow.appendChild(tdCol);
                                tdCol = cTag('td',{ "align":"left", 'style': "padding-left: 40px;" });
                                    const deviceTitle = cTag('h4');
                                    deviceTitle.innerHTML = Translate('Device Info');
                                tdCol.appendChild(deviceTitle);
                                [
                                    {label:Translate('IMEI/Serial No.'),id:'imei'},
                                    {label:Translate('Brand/Model/Color'),id:'brand'},
                                    {label:Translate('Bin Location'),id:'bin_location'},
                                    {label:Translate('Password'),id:'lock_password'}
                                ].forEach(item=>{
                                        label = cTag('label',{ "class":"cursor", 'style': "padding-top: 0;" });
                                        label.append(item.label+'   ');
                                            input = cTag('input',{ "type":"checkbox","name":item.id,"id":item.id,"value":"1" });
                                            if(data[item.id] === 1) input.checked = true;
                                        label.appendChild(input);
                                    tdCol.appendChild(label);
                                    tdCol.appendChild(cTag('br'));
                                })
                            ticketHeaderRow.appendChild(tdCol);
                        ticketInfoBody.appendChild(ticketHeaderRow);
                    ticketInfoTable.appendChild(ticketInfoBody);
                ticketInfoColumn.appendChild(ticketInfoTable);
            ticketInfoRow.appendChild(ticketInfoColumn);
        fragment.appendChild(ticketInfoRow);
        document.querySelector('#frmgeneral').insertBefore(fragment,document.querySelectorAll('.flexStartRow')[document.querySelector('#frmgeneral').childElementCount-2]);
        document.querySelector('#nextrepairticket_no').value = document.querySelector('#nextrepairticketno').value = data.nextrepairticket_no;
        document.querySelector('#logo_size').value = document.querySelector('#oldlogo_size').value = data.logo_size;
        document.querySelector('#logo_placement').value = document.querySelector('#oldlogo_placement').value =  data.logo_placement;
        document.querySelector('#title').value = data.title;
        document.querySelector('#repair_sort').value = data.repair_sort;
        if(data.print_price_zero === 1) document.querySelector('#print_price_zero').checked = true;
        if(data.notes === 1) document.querySelector('#notes').checked = true;
        document.querySelector('#company_info').value = data.company_info;
        document.querySelector('#repair_message').value = data.repair_message;
        document.querySelector('#variables_id').value = data.variables_id;
        document.querySelector('#submit').value = Translate('Add');
        if(data.variables_id>0){
            document.querySelector('#submit').value = Translate('Update');
        }  
    }
}
function repairs_general_removeLogo(){
	if(document.querySelector("#logo_size").value !== document.querySelector("#oldlogo_size").value){
		if(document.querySelector("#invoice_setup_picture div") && document.querySelector("#invoice_setup_picture div").classList.contains('currentPicture')){
			let picturepath = document.querySelector(".currentPicture").querySelector("img").getAttribute('src');
			AJremove_Picture(picturepath, 'invoice_setup');
		}
		else{
			AJsave_Settings_repairs_general(0);
		}
	}
}
function repairs_general_changeLogoPlacement(){
	if(document.querySelector("#logo_placement").value !== document.querySelector("#oldlogo_placement").value){
		AJsave_Settings_repairs_general(0);
	}
}
function checkTicketNo(){
	let nextrepairticketno = parseInt(document.getElementById('nextrepairticketno').value);
	let nextrepairticket_no = parseInt(document.getElementById('nextrepairticket_no').value);
	if(nextrepairticket_no==='' || isNaN(nextrepairticket_no)){
		nextrepairticket_no = 1;
		document.getElementById('nextrepairticket_no').value = 1;
	}
	
	let errorid = document.getElementById("errmsg_nextrepairticketno");
	
	if(nextrepairticketno==='' || isNaN(nextrepairticketno)){
		errorid.innerHTML = Translate('Ticket Number')+' '+Translate('is missing.');
		return false;
	}		
	else if(nextrepairticketno<nextrepairticket_no){
		errorid.innerHTML = Translate('Sorry, you must enter a invoice number greater than')+parseInt(nextrepairticket_no-1);
		return false;
	}		
	return true;
}
async function AJsave_Settings_repairs_general(viewoption){
	if(checkTicketNo()===false){
		document.getElementById('nextrepairticketno').focus();
		return false;
	}
	const nextrepairticketno = parseInt(document.getElementById('nextrepairticketno').value);
	let nextrepairticket_no = parseInt(document.getElementById('nextrepairticket_no').value);
	if(nextrepairticket_no==='' || isNaN(nextrepairticket_no)){
		nextrepairticket_no = 1;
		document.getElementById('nextrepairticket_no').value = 1;
	}
	
	if(nextrepairticketno==='' || isNaN(nextrepairticketno)){
		document.getElementById('nextrepairticketno').value = nextrepairticket_no;
	}	
	
	if(nextrepairticketno>nextrepairticket_no){
		document.getElementById('nextrepairticket_no').value = nextrepairticketno;
	}	
	
	const repair_statusesarray = document.getElementsByName('repair_statuses[]');							
	const repair_statuses_listarray = new Array();
	const error_messageid = document.getElementById('error_rsListRow');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < repair_statusesarray.length; i++) {
														
		if(repair_statusesarray[i].value !==''){
			if (repair_statuses_listarray.length > 0 && repair_statuses_listarray.indexOf(repair_statusesarray[i].value) !== -1) {
				error_messageid.innerHTML = Translate('Duplicate List of Repair Status')+parseInt(i+1);
				repair_statusesarray[i].focus();
				return false;
			}								
			else if(repair_statusesarray[i].value===Translate('Cancelled')){
				error_messageid.innerHTML = Translate('Cancelled is not allowed for Repair Status')+parseInt(i+1);
				repair_statusesarray[i].focus();
				return false;
			}
			else {
				repair_statuses_listarray[i] = repair_statusesarray[i].value;
			}
		}
	} 
	
	const submitBtn = document.querySelector("#submit");
    btnEnableDisable(submitBtn,Translate('Saving'),true);
	
    const jsonData = serialize("#frmgeneral");
    const url = '/'+segment1+'/AJsave_repairs_general';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error'){
            btnEnableDisable(submitBtn,Translate('Update'),false);
			document.getElementById("variables_id").value = data.id;
			document.querySelector("#oldlogo_size").value = document.querySelector("#logo_size").value;
			if(document.querySelector("#logo_placement").value !== document.querySelector("#oldlogo_placement").value){
				window.location = window.location.href;
			}
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
            else if(data.id===0) showTopMessage('alert_msg',Translate('There is no post data found.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing repair setup information! Please try again.'));
            btnEnableDisable(submitBtn,Translate('Save'),false);
		}
    }
	return false;
}
function checkRSFields(){
	let repairStatuses = document.getElementsByName('repair_statuses[]');							
	let repair_statuses_listarray = new Array();
	let error_messageid = document.getElementById('error_rsListRow');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < repairStatuses.length; i++) {
														
		if(repairStatuses[i].value===''){
			error_messageid.innerHTML = Translate('Missing List of Repair Status')+parseInt(i+1);
			repairStatuses[i].focus();
			return false;
		}
		else if(repairStatuses[i].value===Translate('Estimate')){
			error_messageid.innerHTML = Translate('Estimate Repair Status is not allowed of')+' '+parseInt(i+1);
			repairStatuses[i].value = '';
			repairStatuses[i].focus();
			return false;
		}
		else if (repair_statuses_listarray.length > 0 && repair_statuses_listarray.indexOf(repairStatuses[i].value) !== -1) {
			error_messageid.innerHTML = Translate('Duplicate List of Repair Status')+parseInt(i+1);
			repairStatuses[i].focus();
			return false;
		}
		else {
			repair_statuses_listarray[i] = repairStatuses[i].value;
		}
	}
	return true;
}
function addMoreRS(){
	if(checkRSFields()===false){return false;}
	else{
		let ulidname = 'rsListRow';
		let index = document.querySelector("ul#"+ulidname).childElementCount;
		index = parseInt(index+1)
        let newmore_list = cTag('li');
            const newRepairRow = cTag('div',{ "class":"flexSpaBetRow" });
                const newRepairColumn = cTag('div',{ "class":"columnSM6" });
                    let input = cTag('input',{ "type":"text","maxlength":"20","placeholder":`${Translate('Enter new repair status')} ${index}`,"alt":`${Translate('Enter new repair status')} ${index}`,"title":status,"name":"repair_statuses[]","class":"form-control order_statuses placeholder" });
                newRepairColumn.appendChild(input);
            newRepairRow.appendChild(newRepairColumn);
                const statusColorColumn = cTag('div',{ "class":"columnSM6" });
                statusColorColumn.appendChild(cTag('input',{ "type":"color","class":"statusescolor","maxlength":"7","name":"status_colors[]","value":'#FFFFFF',"style":'width: 100%; height: 30px; border: none' }));
            newRepairRow.appendChild(statusColorColumn);
        newmore_list.appendChild(newRepairRow);
		document.querySelector("#"+ulidname).appendChild(newmore_list);
        callPlaceholder();		
		
		document.getElementsByName('repair_statuses[]')[parseInt(index-1)].focus();
		makedeletedicon('rsListRow');
	}
}
//-------------------Notifications----------------------//
function notifications(){
    let textarea;
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Repairs')} ${Translate('Notifications')}`));
        const notificationContainer = cTag('div',{class: "flexSpaBetRow"});
        notificationContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let notificationColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDiv = cTag('div',{'class':"innerContainer",'style': callOutDivStyle });
                    let notificationForm = cTag('form',{ "name":"frmnotifications","id":"frmnotifications","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
                    notificationForm.addEventListener('submit',event=>{
                        event.preventDefault();
                        AJsave_notifications();
                    });
                        const tagRow = cTag('div',{ "class":"flexEndRow", 'style': "padding-bottom: 15px;" });
                            let tagColumn = cTag('div',{ "class":"columnSM9 columnMD10" });
                                const bsCallOut = cTag('div',{ "class":"innerContainer", 'style': "margin: 0;" });
                                    let pTag = cTag('p');
                                    pTag.innerHTML = Translate('You can enter the following tags so when the default email/SMS is created these TAGS will be inserted for you');
                                bsCallOut.appendChild(pTag);
                                    pTag = cTag('p');
                                    pTag.innerHTML = Translate('Tags Data');
                                bsCallOut.append(pTag, '-----------------------------------------------------');
                                bsCallOut.append(cTag('br'), '{{FirstName}} ', Translate('First Name'));
                                bsCallOut.append(cTag('br'), '{{LastName}} ', Translate('Last Name'));
                                bsCallOut.append(cTag('br'), '{{TicketNumber}} ', Translate('Ticket Number'));
                                bsCallOut.append(cTag('br'), '{{IMEINumber}} ', Translate('IMEI Number'));
                                bsCallOut.append(cTag('br'), '{{BrandName}} ', Translate('Brand Name'));
                                bsCallOut.append(cTag('br'), '{{ModelName}} ', Translate('Model Name'));
                                bsCallOut.append(cTag('br'), '{{MoreDetails}} ', Translate('More Details'));
                                bsCallOut.append(cTag('br'), '{{ProblemName}} ', Translate('Problem'));
                                bsCallOut.append(cTag('br'), '{{RepairStatus}} ', Translate('Status'));
                                bsCallOut.append(cTag('br'), '{{DueDateTime}} ', Translate('Due Date and Time'));
                                bsCallOut.append(cTag('br'), '{{CustomField}} ', Translate('Repair Custom Field'));
                            tagColumn.appendChild(bsCallOut);
                        tagRow.appendChild(tagColumn);
                    notificationForm.appendChild(tagRow);
                        const statusRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const statusColumn = cTag('div',{ "class":"columnSM3 columnMD2" });
                                const statusLabel = cTag('label',{ "for":"status" });
                                statusLabel.innerHTML = Translate('Status');
                            statusColumn.appendChild(statusLabel);
                        statusRow.appendChild(statusColumn);
                            const statusDropDown = cTag('div',{ "class":"columnSM9 columnMD10" });
                                let selectStatus = cTag('select',{ "id":"status","name":"status","class":"form-control" });
                                selectStatus.addEventListener('change',function(){
                                    AJgetNotificationsData(stripslashes(''+this.value));
                                })
                            statusDropDown.appendChild(selectStatus);
                        statusRow.appendChild(statusDropDown);
                    notificationForm.appendChild(statusRow);
                        const finishedSubjectRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const finishedSubjectColumn = cTag('div',{ "class":"columnSM3 columnMD2" });
                                const finishedSubjectLabel = cTag('label',{ "for":"subject","class":"lbsubject" });
                                finishedSubjectLabel.innerHTML = Translate('Finished Subject');
                            finishedSubjectColumn.appendChild(finishedSubjectLabel);
                        finishedSubjectRow.appendChild(finishedSubjectColumn);
                            const finishedSubjectField = cTag('div',{ "class":"columnSM9 columnMD10" });
                            finishedSubjectField.appendChild(cTag('input',{ "type":"text", "id":"subject","name":"subject","class":"form-control", }));
                        finishedSubjectRow.appendChild(finishedSubjectField);
                    notificationForm.appendChild(finishedSubjectRow);
                        const finishedEmailRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const finishedEmailColumn = cTag('div',{ "class":"columnSM3 columnMD2" });
                                const finishedEmailLabel = cTag('label',{ "for":"email_body","class":"lbemail_body" });
                                finishedEmailLabel.innerHTML = Translate('Finished Email');
                            finishedEmailColumn.appendChild(finishedEmailLabel);
                        finishedEmailRow.appendChild(finishedEmailColumn);
                            const emailBody = cTag('div',{ "class":"columnSM9 columnMD10" });
                                textarea = cTag('textarea',{ "id":"email_body","rows":"6","name":"email_body","class":"form-control", });
                                textarea.addEventListener('blur',sanitizer);
                            emailBody.appendChild(textarea);
                        finishedEmailRow.appendChild(emailBody);
                    notificationForm.appendChild(finishedEmailRow);
                        const finishedSmsRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            const finishedSmsColumn = cTag('div',{ "class":"columnSM3 columnMD2" });
                                const finishedSmsLabel = cTag('label',{ "for":"sms_text","class":"lbsms_text" });
                                finishedSmsLabel.innerHTML = Translate('Finished')+' '+Translate('SMS Messaging');
                            finishedSmsColumn.appendChild(finishedSmsLabel);
                        finishedSmsRow.appendChild(finishedSmsColumn);
                            const smsBody = cTag('div',{ "class":"columnSM9 columnMD10" });
                                textarea = cTag('textarea',{ "id":"sms_text","rows":"3","name":"sms_text","class":"form-control","maxlength":"159", });
                                textarea.addEventListener('blur',sanitizer);
                            smsBody.appendChild(textarea);
                        finishedSmsRow.appendChild(smsBody);
                    notificationForm.appendChild(finishedSmsRow);
                        const buttonName = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;" });
                            let buttonTitle = cTag('div',{ "class":"columnXS12","align":"center" });
                            buttonTitle.appendChild(cTag('input',{ "class":"btn saveButton","name":"submit","id":"submit","type":"submit","value":Translate('Save'), }));
                        buttonName.appendChild(buttonTitle);
                    notificationForm.appendChild(buttonName);
                callOutDiv.appendChild(notificationForm);
                    const notificationTableRow = cTag('div',{ "class":"flexSpaBetRow" });
                        const notificationTableColumn = cTag('div',{ "class":"columnSM12","style":"position: relative" });
                            const noMoreTables = cTag('div',{ "id":"no-more-tables" });
                                const notificationTable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing " });
                                    let notificationHead = cTag('thead',{ "class":"cf" });
                                        let notificationHeadRow = cTag('tr');
                                            let tdCol0 = cTag('th',{ "align":"center","width":"10%" });
                                            tdCol0.innerHTML = Translate('Status');
                                            let tdCol1 = cTag('th',{ "align":"left","width":"20%" });
                                            tdCol1.innerHTML = Translate('Subject');
                                            let tdCol2 = cTag('th',{ "align":"center" });
                                            tdCol2.innerHTML = Translate('Email');
                                            let tdCol3 = cTag('th',{ "align":"left","width":"20%" });
                                            tdCol3.innerHTML = Translate('SMS Messaging');
                                            let tdCol4 = cTag('th',{ "align":"center","width":"7%" });
                                                let editIcon = cTag('i',{ "class":"fa fa-edit", 'style': "font-size: 16px;" });
                                                editIcon.append(' ');
                                                editIcon.appendChild(cTag('i',{ "class":"fa fa-remove", 'style': "font-size: 16px;" }));
                                            tdCol4.appendChild(editIcon);
                                        notificationHeadRow.append(tdCol0, tdCol1, tdCol2, tdCol3, tdCol4);
                                    notificationHead.appendChild(notificationHeadRow);
                                notificationTable.appendChild(notificationHead);
                                notificationTable.appendChild(cTag('tbody',{ "id":"tableRows", }));
                            noMoreTables.appendChild(notificationTable);
                        notificationTableColumn.appendChild(noMoreTables);
                    notificationTableRow.appendChild(notificationTableColumn);
                callOutDiv.appendChild(notificationTableRow);
            notificationColumn.appendChild(callOutDiv);
        notificationContainer.appendChild(notificationColumn);
    Dashboard.appendChild(notificationContainer);
    AJ_notifications_MoreInfo();
}
async function AJ_notifications_MoreInfo(){
    const url = '/'+segment1+'/AJ_notifications_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let select = document.querySelector('#status');
        select.innerHTML = '';
        setOptions(select,data.repairStatuses,0,0);
        select.value = data.status;
        AJgetNotificationsData(data.status);
        AJgetNotificationsLists();
    }
}
async function AJgetNotificationsLists(){
    const url = '/'+segment1+'/AJgetNotificationsLists';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let notifiListHeadRow;
        let tableRows = document.querySelector("#tableRows")
        tableRows.innerHTML = '';
        if(data.tabledata.length>0){
            data.tabledata.forEach(item=>{
                if(item.length===0)  return;
                    notifiListHeadRow = cTag('tr');
                        let td2 = cTag('td',{ "data-title":Translate('Status'),"align":"left" });
                        td2.innerHTML = item[0]||'&nbsp;';
                    notifiListHeadRow.appendChild(td2);
                        let td3 = cTag('td',{ "data-title":Translate('Subject'),"align":"left" });
                        td3.innerHTML = item[1]||'&nbsp;';
                    notifiListHeadRow.appendChild(td3);
                        let td4 = cTag('td',{ "data-title":Translate('Email'),"align":"left" });
                        td4.innerHTML = item[2]||'&nbsp;';
                    notifiListHeadRow.appendChild(td4);
                        let td5 = cTag('td',{ "data-title":Translate('SMS Messaging'),"align":"left" });
                        td5.innerHTML = item[3]||'&nbsp;';
                    notifiListHeadRow.appendChild(td5);
                        let td6 = cTag('td',{ "data-title":Translate('Change'),"align":"center" });
                        td6.appendChild(cTag('i',{ "class":"fa fa-edit","style":"cursor: pointer","data-toggle":"tooltip","click":()=>AJgetNotificationsData(item[0]),"data-original-title":Translate('Change'), }));
                        td6.append(' ');
                        td6.appendChild(cTag('i',{ "class":"fa fa-remove","style":"cursor: pointer","data-toggle":"tooltip","click":()=>AJremoveNotificationsData(item[0]),"data-original-title":Translate('Remove'), }));
                    notifiListHeadRow.appendChild(td6);
                tableRows.appendChild(notifiListHeadRow);
            })
        }
        else{
                notifiListHeadRow = cTag('tr');
                    let td = cTag('td',{ "colspan":"5"});
                    td.innerHTML = '';
                notifiListHeadRow.appendChild(td);
            document.querySelector('#tableRows').appendChild(notifiListHeadRow);		
        }
    }                      
	return false;
}
 
async function AJgetNotificationsData(sstatus){
	if(sstatus===''){
        sstatus = 'Finished';
        document.querySelector("#status").value = Translate('Finished');
    }
	document.querySelector(".lbsubject").innerHTML = sstatus+' '+Translate('Subject');
	document.querySelector(".lbemail_body").innerHTML = sstatus+' '+Translate('Email');
	document.querySelector(".lbsms_text").innerHTML = sstatus+' '+Translate('SMS Messaging'); 
    const jsonData = {'status':sstatus};
    const url = '/'+segment1+'/AJgetNotificationsData';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        document.querySelector("#status").value = data.status;
        document.querySelector("#subject").value = data.subject;
        document.querySelector("#email_body").value = data.email_body;
        document.querySelector("#sms_text").value = data.sms_text;
    }
}
function AJremoveNotificationsData(status){
	let message = cTag('div');
    message.append(Translate('Are you sure you want to remove')+'?');
        let input = cTag('input',{ type:"hidden", name:"prstatus", id:"prstatus", value:status });
    message.appendChild(input);
	confirm_dialog('Remove Notification', message.innerHTML, confirmNotificationsRemoval);
}
async function confirmNotificationsRemoval(hidePopup){
	let prstatus = document.querySelector("#prstatus").value;
    const jsonData = {status:prstatus};
    const url = '/'+segment1+'/AJremoveNotificationsData';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !== 'error'){
            AJgetNotificationsLists();
			hidePopup();
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
        }
        else{
            showTopMessage('alert_msg',Translate('Error occured while changing Notifications information! Please try again.'));
        }
    }
}
async function AJsave_notifications(){
    if(document.getElementById('email_body').value.trim()==='' && document.getElementById('sms_text').value.trim()===''){
        showTopMessage('alert_msg', 'At least one field required ( Email / SMS )');
        return;
    }
    if(document.getElementById('email_body').value.trim() !=='' && document.getElementById('subject').value.trim()===''){
        showTopMessage('alert_msg', Translate('Subject')+' '+Translate('is missing.'));
        return;
    }
    
    const submitBtn = document.querySelector("#submit");
    btnEnableDisable(submitBtn,Translate('Saving'),true);
	
    const jsonData = serialize("#frmnotifications");
    const url = '/'+segment1+'/AJsave_notifications';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
            btnEnableDisable(submitBtn,Translate('Update'),false);
			AJgetNotificationsLists();
            if(data.savemsg ==='insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            if(data.savemsg ==='update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing Notifications information! Please try again.'));
            btnEnableDisable(submitBtn,Translate('Save'),false);
		}
	}	            
	return false;
}                    
//-------------------Custom Fields----------------------
function repairs_custom_fields(){
    custom_fields_creator(`${Translate('Repairs')} ${Translate('Custom Fields')}`,'repairs');
}
//-------------------forms----------------------
function forms(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Repairs')} ${Translate('Forms')}`));
        const repairsFormContainer = cTag('div',{class: "flexSpaBetRow"});
        repairsFormContainer.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let repairsFormColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDiv = cTag('div',{'class':"innerContainer bs-callout-info",'style': callOutDivStyle });
                    const repairsFormRow = cTag('div',{ "class":"flexSpaBetRow" });
                        let formContainer = cTag('div',{ "class":"columnXS12" });
                            const titleRow = cTag('div',{ "class":"flexSpaBetRow" });
                                const titleName = cTag('div',{ "class":"columnXS7", 'style': "margin: 0;" });
                                    let headerTitle = cTag('h4', {'style': "font-size: 18px;"});
                                    headerTitle.append(Translate('Form Information')+' ');
                                    headerTitle.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('Custom Fields'), }));
                                titleName.appendChild(headerTitle);
                            titleRow.appendChild(titleName);
                                const buttonName = cTag('div',{ "class":"columnXS5", 'style': "text-align: end; margin: 0;" });
                                    let createButton = cTag('button',{ "class":"btn createButton","click":()=>AJgetPopup_forms(0, 'repairs', 0),"title":Translate('Create Form') });
                                    createButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Form'));
                                buttonName.appendChild(createButton);
                            titleRow.appendChild(buttonName);
                        formContainer.appendChild(titleRow);
                            const divTable = cTag('div',{ "class":"flexSpaBetRow" });
                                let divTableColumn = cTag('div',{ "class":"columnSM12","style":"position: relative" });
                                    const divNoMore = cTag('div',{ "id":"no-more-tables" });
                                        const formTable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing " });
                                            const formHead = cTag('thead',{ "class":"cf" });
                                                const formHeadRow = cTag('tr');
                                                    const tdCol0 = cTag('th',{ "align":"left" });
                                                    tdCol0.innerHTML = Translate('Form Name');
                                                    const tdCol1 = cTag('th',{ "align":"center","width":"10%" });
                                                    tdCol1.innerHTML = Translate('Make Public');
                                                    const tdCol2 = cTag('th',{ "align":"center","width":"10%" });
                                                    tdCol2.innerHTML = Translate('Required');
                                                    const tdCol3 = cTag('th',{ "align":"center","width":"20%" });
                                                    tdCol3.innerHTML = Translate('Form Condition');
                                                    const tdCol4 = cTag('th',{ "align":"center","width":"20%" });
                                                    tdCol4.innerHTML = Translate('Form Match');
                                                    const tdCol5 = cTag('th',{ "align":"center","width":"5%" });
                                                    tdCol5.appendChild(cTag('i',{ "class":"fa fa-trash-o", 'style': "font-size: 16px;"}));
                                                formHeadRow.append(tdCol0, tdCol1, tdCol2, tdCol3, tdCol4, tdCol5);
                                            formHead.appendChild(formHeadRow);
                                        formTable.appendChild(formHead);
                                        const formBody = cTag('tbody',{ "id":"tableRows" });
                                        formTable.appendChild(formBody);
                                    divNoMore.appendChild(formTable);
                                divTableColumn.appendChild(divNoMore);
                            divTable.appendChild(divTableColumn);
                        formContainer.appendChild(divTable);
                    repairsFormRow.appendChild(formContainer);
                callOutDiv.appendChild(repairsFormRow);
            repairsFormColumn.appendChild(callOutDiv);
        repairsFormContainer.appendChild(repairsFormColumn);
    Dashboard.appendChild(repairsFormContainer);
    AJ_forms_MoreInfo()
}
async function AJ_forms_MoreInfo(){
    const url = '/'+segment1+'/AJ_forms_MoreInfo';
    fetchData(afterFetch,url,{});
    function afterFetch(data){
        let tdAttributes = [
                {'data-title':Translate('Form Name'), 'align':'left'},
                {'data-title':Translate('Make Public'), 'align':'center'},
                {'data-title':Translate('Required'), 'align':'center'},
                {'data-title':Translate('Form Condition'), 'align':'left'},
                {'data-title':Translate('Form Match'), 'align':'left'},
                {'data-title':Translate('Archive'), 'align':'center'}
            ];
        const tbody = document.getElementById("tableRows");
        tbody.innerHTML = '';
        //=======Create TBody TR Column=======//
        let formHeadRow, tdCol;
        if(data.tabledata.length>0){		
            data.tabledata.forEach(item=>{                             
                formHeadRow = cTag('tr');                 
                item.forEach((info,indx)=>{
                    if(indx===0) return;
                    else if(indx===2 || indx===3) info = info===1?'Yes':'No';
                    tdCol = cTag('td',tdAttributes[indx-1]);
                        const editLink = cTag('a',{'class':"anchorfulllink", 'href':`/Settings/formFields/${item[0]}`, 'title':Translate('Edit/View')});
                        if(OS!=='unknown' && indx===4) editLink.innerHTML = info+'</br>'+'&nbsp;';
                        else editLink.innerHTML = info||'&nbsp;';
                    tdCol.appendChild(editLink);
                    formHeadRow.appendChild(tdCol);
                })
                if(accountsInfo[0]===accountsInfo[1]){
                    tdCol = cTag('td',tdAttributes[5]);
                    tdCol.appendChild(cTag('i',{ 'class':`fa fa-trash-o`,'style':`cursor:pointer;`,'data-toggle':`tooltip`,'click':()=>AJarchive_tableRow('forms', 'forms_id', item[0], item[1], 'forms_publish', '/Settings/forms'),'data-original-title':Translate('Archive') }))
                    formHeadRow.appendChild(tdCol);
                }
                tbody.appendChild(formHeadRow);
            })
        }
        else{
                formHeadRow = cTag('tr');
                    tdCol = cTag('td',{ "colspan":"6"});
                    tdCol.innerHTML = '';
                formHeadRow.appendChild(tdCol);
            tbody.appendChild(formHeadRow);
        }
    }
}
async function AJgetPopup_forms(forms_id, form_for, similar){
    const jsonData = {forms_id:forms_id, form_for:form_for};
    const url = "/Settings/AJgetPopup_forms";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        let form_name, saveButton, requiredField;
        form_name = data.form_name;
        saveButton = Translate('Save');
        if(similar>0){
            form_name = form_name+'1';
            saveButton = Translate('Create Similar Form');
        }
        
        let formhtml =  cTag('div');
            let errorMessage = cTag('div',{ "id":"error_forms","class":"errormsg", });
        formhtml.appendChild(errorMessage);
            const similarForm = cTag('form',{ "action":"#","name":"frmForms","id":"frmForms","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8", });
                let similarFormColumn = cTag('div',{ "class":"columnXS12"});
                    let nameRow = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;" ,"align":"left"});
                        let nameColumn = cTag('div',{ "class":"columnSM4" });
                            const labelTitle = cTag('label',{ "for":"form_name"});
                            labelTitle.append(Translate('Name'));
                                requiredField = cTag('span',{ "class":"required"});
                                requiredField.innerHTML = '*';
                            labelTitle.appendChild(requiredField);
                        nameColumn.appendChild(labelTitle);
                    nameRow.appendChild(nameColumn);
                        let nameValueColumn = cTag('div',{ "class":"columnSM8"});
                        nameValueColumn.appendChild(cTag('input',{ "required":"required","type":"text","class":"form-control","name":"form_name","id":"form_name","value":form_name,"maxlength":"15", }));
                    nameRow.appendChild(nameValueColumn);
                similarFormColumn.appendChild(nameRow);
                    let makePublicRow = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;", "align":"left"});
                        let makePublicColumn = cTag('div',{ "class":"columnXS5 columnSM4"});
                            const publicLabel = cTag('label',{ "for":"form_public"});
                            publicLabel.innerHTML = Translate('Make Public');
                        makePublicColumn.appendChild(publicLabel);
                    makePublicRow.appendChild(makePublicColumn);
                        let publicCheckBox = cTag('div',{ "class":"columnXS7 columnSM8"});
                            let publicCheckBoxInput = cTag('input',{ "type":"checkbox","name":"form_public","id":"form_public","value":"1", });
                            if(data.form_public>0) publicCheckBoxInput.checked = true;
                        publicCheckBox.appendChild(publicCheckBoxInput);
                    makePublicRow.appendChild(publicCheckBox);
                similarFormColumn.appendChild(makePublicRow);
                    let requiredRow = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;", "align":"left"});
                        let requiredColumn = cTag('div',{ "class":"columnXS5 columnSM4" });
                            const requiredLabel = cTag('label',{ "for":"required"});
                            requiredLabel.innerHTML = Translate('Required');
                        requiredColumn.appendChild(requiredLabel);
                    requiredRow.appendChild(requiredColumn);
                        let requiredCheckBox = cTag('div',{ "class":"columnXS7 columnSM8"});
                            let requiredInput = cTag('input',{ "type":"checkbox","name":"required","id":"required","value":"1", });
                            if(data.required>0) requiredInput.checked = true;
                        requiredCheckBox.appendChild(requiredInput);
                    requiredRow.appendChild(requiredCheckBox);
                similarFormColumn.appendChild(requiredRow);
                    let formConditionRow = cTag('div',{ "class":"flex", 'style': "padding-bottom: 15px;", "align":"left"});
                        let formConditionColumn = cTag('div',{ "class":"columnSM4"});
                            const conditionLabel = cTag('label',{ "for":"form_condition"});
                            conditionLabel.innerHTML = Translate('Form Condition');
                                requiredField = cTag('span',{ "class":"required"});
                                requiredField.innerHTML = '*';
                            conditionLabel.appendChild(requiredField);
                        formConditionColumn.appendChild(conditionLabel);
                    formConditionRow.appendChild(formConditionColumn);
                        let formConditionDropDown = cTag('div',{ "class":"columnSM8"});
                            let selectFormCondition = cTag('select',{ "required":"required","class":"form-control","id":"form_condition","name":"form_condition","change":showConditionMatch, });
                            setOptions(selectFormCondition,data.form_conditionOptions,0,0);
                        formConditionDropDown.appendChild(selectFormCondition);
                    formConditionRow.appendChild(formConditionDropDown);
                similarFormColumn.appendChild(formConditionRow);
                    let formMatchRow = cTag('div',{ "class":"flex", "align":"left", 'style': "padding-bottom: 15px; display: none;", "id":"form_matchesRow",});
                        let formMatchColumn = cTag('div',{ "class":"columnSM4"});
                            const formMatchLabel = cTag('label',{ "for":"form_matches"});
                            formMatchLabel.append(Translate('Form Match'));
                                requiredField = cTag('span',{ "class":"required"});
                                requiredField.innerHTML = '*';
                            formMatchLabel.appendChild(requiredField);
                        formMatchColumn.appendChild(formMatchLabel);
                    formMatchRow.appendChild(formMatchColumn);
                        let formMatchDropDown = cTag('div',{ "class":"columnSM8"});
                            let selectFormMatch = cTag('select',{ "required":"required","class":"form-control","id":"form_matches","name":"form_matches","change":showModelMatch, });
                            if(data.form_condition === 'Problem') setOptions(selectFormMatch,{'':Translate('Select Problem'),...data.form_matchesOptions},1,0);
                            if(data.form_condition === 'Brand/Model') setOptions(selectFormMatch,{'':Translate('Select Brand'),...data.form_matchesOptions},1,0);
                        formMatchDropDown.appendChild(selectFormMatch);
                    formMatchRow.appendChild(formMatchDropDown);
                similarFormColumn.appendChild(formMatchRow);
                    let modelRow = cTag('div',{ "class":"flex", "align":"left", 'style': "display:none;padding-bottom: 15px;", "id":"form_matches2Row"});
                        let modelColumn = cTag('div',{ "class":"columnSM4"});
                            const modelLabel = cTag('label',{ "for":"model"});
                            modelLabel.append(Translate('Model'));
                                requiredField = cTag('span',{ "class":"required"});
                                requiredField.innerHTML = '*';
                            modelLabel.appendChild(requiredField);
                        modelColumn.appendChild(modelLabel);
                    modelRow.appendChild(modelColumn);
                        let modelValue = cTag('div',{ "class":"columnSM8"});
                            let selectModel = cTag('select',{ "required":"required","class":"form-control","id":"model","name":"model"});
                                let modelOption = cTag('option',{ "value":""});
                                modelOption.innerHTML = Translate('Any');
                            selectModel.appendChild(modelOption);
                        modelValue.appendChild(selectModel);
                    modelRow.appendChild(modelValue);
                similarFormColumn.appendChild(modelRow);
            similarForm.appendChild(similarFormColumn);
            similarForm.appendChild(cTag('input',{ "type":"hidden","name":"forms_id","id":"forms_id","value":forms_id, }));
            similarForm.appendChild(cTag('input',{ "type":"hidden","name":"similar","id":"similar","value":similar, }));
            similarForm.appendChild(cTag('input',{ "type":"hidden","name":"form_for","id":"form_for","value":form_for, }));
        formhtml.appendChild(similarForm);            
        
        popup_dialog600(Translate('Forms Information'), formhtml, saveButton, AJsave_forms);
        
        setTimeout(function() {
            document.querySelector('#form_condition').value = data.form_condition;
            document.querySelector('#form_matches').value = data.form_matches;
            if(document.querySelector("#form_matchesRow").style.display !== 'none'){
                document.querySelector("#form_matchesRow").style.display = 'none';
            }
            if(document.querySelector("#form_matches2Row").style.display !== 'none'){
                document.querySelector("#form_matches2Row").style.display = 'none';
            }
            if(data.form_condition==='Problem'){
                if(document.querySelector("#form_matchesRow").style.display === 'none'){
                    document.querySelector("#form_matchesRow").style.display = '';
                }
                if(data.form_matches !==''){
                    document.querySelector("#form_matches").value = data.form_matches;
                }
            }
            else if(data.form_condition==='Brand/Model'){
                if(document.querySelector("#form_matchesRow").style.display === 'none'){
                    document.querySelector("#form_matchesRow").style.display = '';
                }
                if(document.querySelector("#form_matches2Row").style.display === 'none'){
                    document.querySelector("#form_matches2Row").style.display = '';
                }
                if(data.form_matches !==''){
                    document.querySelector("#form_matches").value = data.form_matches;
                }
                if(data.model !==''){
                    document.querySelector("#model").value = data.model;
                }
            }
            
            document.getElementById("form_name").focus();
        }, 500);
    }
	return true;
}
async function showConditionMatch(){
	let forms_id = document.querySelector("#forms_id").value;
	let form_for = document.querySelector("#form_for").value;
	let form_condition = document.querySelector("#form_condition").value;
	if(['', 'All Repairs', 'Create Repair'].includes(form_condition)){
        if(document.querySelector("#form_matchesRow").style.display !== 'none'){
            document.querySelector("#form_matchesRow").style.display = 'none';
        }
        if(document.querySelector("#form_matches2Row").style.display !== 'none'){
            document.querySelector("#form_matches2Row").style.display = 'none';
        }
		document.querySelector("#form_matches").value = '';
		document.querySelector("#model").value = '';
	}
	else{
        const jsonData = {"forms_id":forms_id, form_for:form_for, form_condition:form_condition};
        const url = "/Settings/showConditionMatch";
        fetchData(afterFetch,url,jsonData);
        function afterFetch(data){
            let form_matchesOptions = data.form_matchesOptions;
            let matches = document.querySelector("#form_matches");
            matches.innerHTML = '';
            if(form_condition==='Problem') form_matchesOptions = {'':Translate('Select Problem'),...form_matchesOptions};
            if(form_condition==='Brand/Model') form_matchesOptions = {'':Translate('Select Brand'),...form_matchesOptions};
            setOptions(matches,form_matchesOptions,1,0);
            if(document.querySelector("#form_matchesRow").style.display === 'none'){
                document.querySelector("#form_matchesRow").style.display = '';
            }
        }
	}
	return false;	
}
async function showModelMatch(){
	let forms_id = document.querySelector("#forms_id").value;
	let form_for = document.querySelector("#form_for").value;
	let form_condition = document.querySelector("#form_condition").value;
	let form_matches = document.querySelector("#form_matches").value;
	if(form_condition==='Brand/Model'){
		if(form_matches===''){
            if(document.querySelector("#form_matches2Row").style.display !== 'none'){
                document.querySelector("#form_matches2Row").style.display = 'none';
            }
			document.querySelector("#model").value = '';
		}
		else{
            const jsonData = {"forms_id":forms_id, form_for:form_for, form_condition:form_condition, form_matches:form_matches};
            const url = "/Settings/showModelMatch";
            fetchData(afterFetch,url,jsonData);
            function afterFetch(data){
                let modelOpt = data.modelOpt;
                let model = document.querySelector("#model");
                model.innerHTML = '';
                if(form_condition==='Brand/Model' && form_matches !=='') modelOpt = {'Any Model':Translate('Any Model'),...modelOpt};
                setOptions(model,modelOpt,1,0);
                if(document.querySelector("#form_matches2Row").style.display === 'none'){
                    document.querySelector("#form_matches2Row").style.display = '';
                }
            }
		}
	}
	else{
        if(document.querySelector("#form_matches2Row").style.display !== 'none'){
            document.querySelector("#form_matches2Row").style.display = 'none';
        }
		document.querySelector("#model").value = '';
	}
	return false;
}
async function AJsave_forms(hidePopup){
    let p;
	if(document.querySelector("#form_name").value===''){
        p = cTag('p');
        p.innerHTML = Translate('Missing Name');
		document.getElementById('error_forms').innerHTML = '';
		document.getElementById('error_forms').appendChild(p);
		document.querySelector("#form_name").focus();
		return false;
	}
	if(document.querySelector("#form_condition").value===''){
		document.getElementById('error_forms').innerHTML = 'Missing Form Condition';
		document.querySelector("#form_condition").focus();
		return false;
	}		
	if(['', 'All Repairs', 'Create Repair'].includes(document.querySelector("#form_condition").value)){}
	else if(document.querySelector("#form_matches").value===''){
        p = cTag('p');
        p.innerHTML = Translate('Missing Match');
		document.getElementById('error_forms').innerHTML = '';
		document.getElementById('error_forms').appendChild(p);
		document.querySelector("#form_matches").focus();
		return false;
	}
    let submitBtn = document.querySelector(".btnmodel");
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    const jsonData = serialize("#frmForms");
    const url = "/Settings/AJsave_forms/";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.savemsg !=='error'){			
			window.location = '/Settings/formFields/'+data.forms_id;
			hidePopup();
		}
        else if(data.returnStr=='errorOnAdding'){
			document.getElementById('error_forms').innerHTML = Translate('Error occured while adding forms information! Please try again.');
		}
		else if(data.returnStr=='Name_Already_Exist'){
			document.getElementById('error_forms').innerHTML = Translate('This form for, name and condition already exists. Try again with different form for, name and condition.');
		}  
		else{
			document.getElementById('error_forms').innerHTML = Translate('No changes / Error occurred while updating data! Please try again.');;
		}
        btnEnableDisable(submitBtn,Translate('Save'),false);
    }
	return false;
}
//-------------------formFields----------------------
function formFields(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(header(`${Translate('Repairs')} ${Translate('Form Fields')}`));
        const formFieldsContiner = cTag('div',{class: "flexSpaBetRow"});
        formFieldsContiner.appendChild(leftSideMenu());
            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
            let formFieldsColumn = cTag('div',{'class':'columnMD10 columnSM9', 'style': "margin: 0;"});
                let callOutDiv = cTag('div',{'class':"innerContainer bs-callout-info",'style': callOutDivStyle });
                    const similarFormRow = cTag('div',{ "class":"flexEndRow" });
                        const similarFormColumn = cTag('div',{ "class":"columnSM12", 'style': "text-align: end;" });
                            const createButton = cTag('button',{ "id":"_Create_Similar_Form","class":"btn createButton" });
                            createButton.innerHTML = Translate('Create Similar Form');
                        similarFormColumn.appendChild(createButton);
                    similarFormRow.appendChild(similarFormColumn);
                callOutDiv.appendChild(similarFormRow);
                    const formInfoRow = cTag('div',{ "class":"flexSpaBetRow" });
                        let formInfoColumn = cTag('div',{ "class":"columnSM12" });
                            let formInfoWidget = cTag('div',{ "class":"cardContainer", 'style': "margin-bottom: 10px;" });
                                const widgetHeader = cTag('div',{ "class":"cardHeader flexSpaBetRow" });
                                    const widgetHeaderDiv = cTag('div',{ "class":"flex" });
                                        const userIcon = cTag('i',{ "class":"fa fa-user", 'style': "margin: 12px; margin-left: 0;"});
                                    widgetHeaderDiv.appendChild(userIcon);
                                        let widgetHeaderTitle = cTag('h3');
                                        widgetHeaderTitle.innerHTML = ' '+Translate('Form Information');
                                    widgetHeaderDiv.appendChild(widgetHeaderTitle);
                                widgetHeader.appendChild(widgetHeaderDiv);
                                    const editButton = cTag('button',{ "id":"_Edit", "class":"btn defaultButton", 'style': "margin-top: 3px; margin-right: 6px; margin-bottom: 6px;" });
                                    editButton.innerHTML = Translate('Edit');
                                widgetHeader.appendChild(editButton);
                            formInfoWidget.appendChild(widgetHeader);
                                let widgetContent = cTag('div',{ "class":"cardContent", 'style': "padding-bottom: 8px;" });
                                    const widgetContentRow = cTag('div',{ "class":"flexSpaBetRow" });
                                        let widgetContentList = cTag('div',{ "class":"columnSM6" });
                                            let orderUl = cTag('div',{ "id":"customer_information1" });
                                            [Translate('Name'),Translate('Make Public'),Translate('Required')].forEach(item=>{
                                                    let itemLi = cTag('div', {'class': "flex"});
                                                        let itemLabel = cTag('label',{ 'style': "flex-basis: 35%;" });
                                                        itemLabel.innerHTML = item+': ';
                                                    itemLi.appendChild(itemLabel);
                                                orderUl.appendChild(itemLi);
                                            })
                                        widgetContentList.appendChild(orderUl);
                                    widgetContentRow.appendChild(widgetContentList);
                                        let customerInfoList = cTag('div',{ "class":"columnSM6" });
                                            let cardOrder = cTag('div',{ "id":"customer_information2" });
                                            [Translate('Form Condition'),Translate('Form Match')].forEach(item=>{
                                                    let formLi = cTag('div', {'class': "flex"});
                                                        let formLabel = cTag('label',{ 'style': "flex-basis: 35%;" });
                                                        formLabel.innerHTML = item+': ';
                                                    formLi.appendChild(formLabel);
                                                cardOrder.appendChild(formLi);
                                            })
                                        customerInfoList.appendChild(cardOrder);
                                    widgetContentRow.appendChild(customerInfoList);
                                widgetContent.appendChild(widgetContentRow);
                            formInfoWidget.appendChild(widgetContent);
                        formInfoColumn.appendChild(formInfoWidget);
                    formInfoRow.appendChild(formInfoColumn);
                callOutDiv.appendChild(formInfoRow);
                    const formFieldsRow = cTag('div',{ "class":"flexSpaBetRow" });
                        const formFieldColumn = cTag('div',{ "class":"columnSM6" });
                            let fieldHeader = cTag('h4', {'style': "font-size: 18px;"});
                            fieldHeader.innerHTML = Translate('Form Fields');
                        formFieldColumn.appendChild(fieldHeader);
                    formFieldsRow.appendChild(formFieldColumn);
                        const createFormColumn = cTag('div',{ "class":"columnSM6", 'style': "text-align: end;" });
                            const createFormButton = cTag('button',{ "class":"btn createButton", "id":"_Create_Form_Field","title":Translate('Create Form Field') });
                            createFormButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Form Field'));
                        createFormColumn.appendChild(createFormButton);
                    formFieldsRow.appendChild(createFormColumn);
                callOutDiv.appendChild(formFieldsRow);
                    const formFieldTableRow  = cTag('div',{ "class":"flexSpaBetRow" });
                        const formFieldTableColumn = cTag('div',{ "class":"columnSM12","style":"position: relative" });
                            const noMoreTables = cTag('div',{ "id":"no-more-tables" });
                                let formFieldTable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing " });
                                    let formFieldHead = cTag('thead',{ "class":"cf" });
                                        let formFieldHeadRow = cTag('tr');
                                            let tdCol0 = cTag('th',{ "align":"center","width":"10%" });
                                            tdCol0.innerHTML = Translate('Order');
                                            let tdCol1 = cTag('th',{ "align":"left" });
                                            tdCol1.innerHTML = Translate('Field Name');
                                            let tdCol2 = cTag('th',{ "align":"center","width":"10%" });
                                            tdCol2.innerHTML = Translate('Required');
                                            let tdCol3 = cTag('th',{ "align":"left","width":"20%" });
                                            tdCol3.innerHTML = Translate('Field Type');
                                            let tdCol4 = cTag('th',{ "align":"center","width":"5%" });
                                            tdCol4.appendChild(cTag('i',{ "class":"fa fa-remove", 'style': "font-size: 16px;"}));
                                        formFieldHeadRow.append(tdCol0, tdCol1, tdCol2, tdCol3, tdCol4);
                                    formFieldHead.appendChild(formFieldHeadRow);
                                formFieldTable.appendChild(formFieldHead);
                                formFieldTable.appendChild(cTag('tbody',{ "id":"tableRows", }));
                            noMoreTables.appendChild(formFieldTable);
                        formFieldTableColumn.appendChild(noMoreTables);
                    formFieldTableRow.appendChild(formFieldTableColumn);
                callOutDiv.appendChild(formFieldTableRow);
            formFieldsColumn.appendChild(callOutDiv);
        formFieldsContiner.appendChild(formFieldsColumn);
    Dashboard.appendChild(formFieldsContiner);
    AJ_formFields_MoreInfo();
}
async function AJ_formFields_MoreInfo(){
    const jsonData = {"forms_id":segment3};
    const url = '/'+segment1+'/AJ_formFields_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        document.querySelector('#_Create_Similar_Form').addEventListener('click',()=>{AJgetPopup_forms(data.forms_id, 'repairs', 1)});
        document.querySelector('#_Edit').addEventListener('click',()=>{AJgetPopup_forms(data.forms_id, 'repairs', 0)});
        document.querySelector('#_Create_Form_Field').addEventListener('click',()=>{AJgetPopup_forms_field(data.forms_id, 0);});
        document.querySelector('#customer_information1').children[0].append(data.formsOneRow.form_name);
        document.querySelector('#customer_information1').children[1].append(data.MakePublic);
        document.querySelector('#customer_information1').children[2].append(data.Required);
        
        document.querySelector('#customer_information2').children[0].append(data.formsOneRow.form_condition);
        document.querySelector('#customer_information2').children[1].append(data.formsOneRow.form_matches);
        if(data.model !== ''){
                let li = cTag('li');
                    let label = cTag('label',{ 'style': "margin-left: 10px; margin-bottom: 0px;" });
                    label.innerHTML = Translate('Model')+': ';
                li.appendChild(label);
                li.append(data.model);
            document.querySelector('#customer_information2').appendChild(li);
        }
        const tableData = data.tabledata.map(item=>{
            if(item[4]===1) item[4] = 'Yes';
            else item[4] = 'No';
            return item;
        })
        setFormFieldsTableRows(tableData, [
            {'data-title':Translate('Order Value'), 'align':'left'},
            {'data-title':Translate('Field Name'), 'align':'left'},
            {'data-title':Translate('Required'), 'align':'center'},
            {'data-title':Translate('Field Type'), 'align':'left'},
            {'data-title':Translate('Remove'), 'align':'center'},
        ],'');
    }
}
async function AJgetPopup_forms_field(forms_id, order_val){
	const fieldTypeOptions = {
		"TextBox":Translate('Text Box'),
		"TextAreaBox":Translate('Text Area Box'),
		"Date":Translate('Date'),
		"DropDown":Translate('Drop Down'),
		"Checkbox":Translate('Checkbox'),
		"TextOnly":Translate('Text Only'),
		"Signature":Translate('Signature'),
		"SectionBreak":Translate('Section Break'),
		"UploadImage":Translate('Upload Image')
	}	
	const jsonData = {"forms_id":forms_id, "order_val":order_val};
	const url = "/Settings/AJgetPopup_forms_field";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        let DropDownOptions = '';
        if(data.field_type==='DropDown'){
            DropDownOptions = data.parameters.split("||");									
        }
        let requiredField;
        let formhtml = cTag('div');
        formhtml.appendChild(cTag('div',{ "id":"error_forms_field","class":"errormsg", }));
            const formsFieldForm = cTag('form',{ "action":"#","name":"frmFormsField","id":"frmFormsField","enctype":"multipart/form-data","method":"post","accept-charset":"utf-8", });
                const fieldNameRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;"});
                    const fieldNameColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                        const fieldNameLabel = cTag('label',{ "for":"field_name", });
                        fieldNameLabel.append(Translate('Field Name'));
                            requiredField = cTag('span',{ "class":"required", });
                            requiredField.innerHTML = '*';
                        fieldNameLabel.appendChild(requiredField);
                    fieldNameColumn.appendChild(fieldNameLabel);
                fieldNameRow.appendChild(fieldNameColumn);
                    const fieldNameValue = cTag('div',{ "class":"columnSM8","align":"left", });
                    fieldNameValue.appendChild( cTag('input',{ "required":"required","type":"text","class":"form-control","name":"field_name","id":"field_name","value":data.field_name,"maxlength":"35", }));
                fieldNameRow.appendChild(fieldNameValue);
            formsFieldForm.appendChild(fieldNameRow);
                const fieldTypeRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;"});
                    const fieldTypeColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                        const fieldTypeLabel = cTag('label',{ "for":"field_type", });
                        fieldTypeLabel.append(Translate('Field Type'));
                            requiredField = cTag('span',{ "class":"required", });
                            requiredField.innerHTML = '*';
                        fieldTypeLabel.appendChild(requiredField);
                    fieldTypeColumn.appendChild(fieldTypeLabel);
                fieldTypeRow.appendChild(fieldTypeColumn);
                    const fieldTypeDropDown = cTag('div',{ "class":"columnSM8","align":"left", });
                        let selectFieldType = cTag('select',{ "required":"required","class":"form-control","id":"field_type","name":"field_type","change":checkFormFieldType, });
                            let fieldTypeOption = cTag('option',{ "value":"", });
                            fieldTypeOption.innerHTML = Translate('Select Field Type');
                        selectFieldType.appendChild(fieldTypeOption);
                        setOptions(selectFieldType,fieldTypeOptions,1,0);
                    fieldTypeDropDown.appendChild(selectFieldType);
                fieldTypeRow.appendChild(fieldTypeDropDown);
            formsFieldForm.appendChild(fieldTypeRow);
                const dropDownRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;", "id":"DropDownRow", });
                    const dropDownColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                        const dropDownLabel = cTag('label');
                        dropDownLabel.append(Translate('Drop Down Options'));
                            requiredField = cTag('span',{ "class":"required", });
                            requiredField.innerHTML = '*';
                        dropDownLabel.appendChild(requiredField);
                    dropDownColumn.appendChild(dropDownLabel);
                dropDownRow.appendChild(dropDownColumn);
                    let dropDownValue = cTag('div',{ "class":"columnSM8 plusIconPosition roundborder","align":"left", });
                        let ul = cTag('ul',{ "id":"DropDownOptions","class":"multipleRowList", });
                        if(DropDownOptions.length>0){
                            DropDownOptions.forEach(item=>{
                                    let li = cTag('li');
                                    li.appendChild(cTag('input',{ "type":"text","class":"form-control DropDown", 'style': "margin-bottom: 10px;", "name":"DropDown[]","value":item,"maxlength":"50" }));
                                        let removeIconLink = cTag('a',{ "class":"removeicon","href":"javascript:void(0);","title":Translate('Remove this row') });
                                        removeIconLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Remove this row'),"title":Translate('Remove this row'),"src":"/assets/images/cross-on-white.gif" }));
                                    li.appendChild(removeIconLink);
                                ul.appendChild(li);
                            })
                        }
                    dropDownValue.appendChild(ul);
                        const addDropDownDiv = cTag('div',{ "class":"addNewPlusIcon", });
                            let addDropDownLink = cTag('a',{ "href":"javascript:void(0);","title":Translate('Add More Drop Down Options'),"click":addDropDownOptions, });
                            addDropDownLink.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Add More Drop Down Options'),"title":Translate('Add More Drop Down Options'),"src":"/assets/images/plus20x25.png", }));
                        addDropDownDiv.appendChild(addDropDownLink);
                    dropDownValue.appendChild(addDropDownDiv);
                dropDownRow.appendChild(dropDownValue);
            formsFieldForm.appendChild(dropDownRow);
                const textOnlyDiv = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;", "id":"TextOnlyRow", });
                    const textOnlyColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                        const textOnlyLabel = cTag('label',{ "class":"TextOnly","for":"field_required", });
                        textOnlyLabel.append(Translate('Text Only'));
                            requiredField = cTag('span',{ "class":"required", });
                            requiredField.innerHTML = '*';
                        textOnlyLabel.appendChild(requiredField);
                    textOnlyColumn.appendChild(textOnlyLabel);
                textOnlyDiv.appendChild(textOnlyColumn);
                    let textOnlyField = cTag('div',{ "class":"columnSM8","align":"left", });
                        const textarea = cTag('textarea',{ "class":"form-control","rows":"4","name":"TextOnly","id":"TextOnly", });
                        textarea.addEventListener('blur',sanitizer);
                    textOnlyField.appendChild(textarea);
                textOnlyDiv.appendChild(textOnlyField);
            formsFieldForm.appendChild(textOnlyDiv);
                const sectionTitleRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;", "id":"SectionBreakRow", });
                    const sectionTitleColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                        const sectionTitleLabel = cTag('label',{ "class":"SectionBreak","for":"SectionBreak", });
                        sectionTitleLabel.innerHTML = Translate('Section Title');
                    sectionTitleColumn.appendChild(sectionTitleLabel);
                sectionTitleRow.appendChild(sectionTitleColumn);
                    const sectionTitleField = cTag('div',{ "class":"columnSM8","align":"left", });
                    sectionTitleField.appendChild(cTag('input',{ "class":"form-control","name":"SectionBreak","id":"SectionBreak","value":data.SectionBreak, }));
                sectionTitleRow.appendChild(sectionTitleField);
            formsFieldForm.appendChild(sectionTitleRow);
                const addImgRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;", "id":"AddImageRow", });
                    const addImgColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                        const addImgLabel = cTag('label',{ "class":"AddImage","for":"AddImage", });
                        addImgLabel.append(Translate('Add Image'));
                            requiredField = cTag('span',{ "class":"required", });
                            requiredField.innerHTML = '*';
                        addImgLabel.appendChild(requiredField);
                    addImgColumn.appendChild(addImgLabel);
                addImgRow.appendChild(addImgColumn);
                    const addImgField = cTag('div',{ "class":"columnSM8","align":"left", });
                    addImgField.appendChild(cTag('div',{ "id":"showAddImage", }));
                    addImgField.appendChild(cTag('input',{ "type":"file","name":"AddImage","id":"AddImage", }));
                addImgRow.appendChild(addImgField);
            formsFieldForm.appendChild(addImgRow);
                const addFileFlex = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;", "id":"AddFileRow", });
                    const addFileColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                        const addFileLabel = cTag('label',{ "class":"AddFile","for":"AddFile", });
                        addFileLabel.append(Translate('Add File'));
                            requiredField = cTag('span',{ "class":"required", });
                            requiredField.innerHTML = '*';
                        addFileLabel.appendChild(requiredField);
                    addFileColumn.appendChild(addFileLabel);
                addFileFlex.appendChild(addFileColumn);
                    const addFileField = cTag('div',{ "class":"columnSM8","align":"left", });
                    addFileField.appendChild(cTag('div',{ "id":"showAddFile", }));
                    addFileField.appendChild(cTag('input',{ "type":"file","name":"AddFile","id":"AddFile", }));
                addFileFlex.appendChild(addFileField);
            formsFieldForm.appendChild(addFileFlex);
                const requiredFieldRow = cTag('div',{ "class":"flexStartRow", 'style': "padding-bottom: 15px;", "id":"FieldRequiredRow", });
                    const requiredFieldColumn = cTag('div',{ "class":"columnSM4","align":"left", });
                    if(OS !=='unknown') requiredFieldColumn.classList.add('columnXS5');
                        const requiredFieldLabel = cTag('label',{ "class":"cursor","for":"field_required", });
                        requiredFieldLabel.innerHTML = Translate('Field Required');
                    requiredFieldColumn.appendChild(requiredFieldLabel);
                requiredFieldRow.appendChild(requiredFieldColumn);
                    const requiredFieldValue = cTag('div',{ "class":"columnSM8","align":"left", });
                    if(OS !=='unknown') requiredFieldValue.classList.add('columnXS7');
                        const requiredBoxLabel = cTag('label',{ "for":"field_required", });
                            let input = cTag('input',{ "class":"cursor","type":"checkbox","name":"field_required","id":"field_required","value":"1", });
                            if(data.field_required>0) input.checked = true;
                        requiredBoxLabel.appendChild(input);
                    requiredFieldValue.appendChild(requiredBoxLabel);
                requiredFieldRow.appendChild(requiredFieldValue);
            formsFieldForm.appendChild(requiredFieldRow);
            formsFieldForm.appendChild(cTag('input',{ "type":"hidden","name":"oldImageOrFile","id":"oldImageOrFile","value":"", }));
            formsFieldForm.appendChild(cTag('input',{ "type":"hidden","name":"forms_id","value":forms_id, }));
            formsFieldForm.appendChild(cTag('input',{ "type":"hidden","name":"order_val","value":order_val, }));
        formhtml.appendChild(formsFieldForm);
                        
        popup_dialog600(Translate('Form Fields Information'), formhtml, Translate('Save'), AJsave_forms_field);			
        setTimeout(function() {
            document.getElementById("error_forms_field").innerHTML = '';
            document.querySelector("#oldImageOrFile").value = '';
            document.querySelector("#field_type").value = data.field_type;
            if(data.field_type==='TextOnly' || data.field_type==='SectionBreak'){
                document.querySelector("#"+data.field_type).value = data.parameters;
            }
            else if((data.field_type==='AddImage' || data.field_type==='AddFile') && data.parameters !==''){
                document.querySelector("#oldImageOrFile").value = data.parameters;
                if(data.field_type==='AddImage'){
                    let showAddImage = document.querySelector("#showAddImage");
                    showAddImage.innerHTML = '';
                    showAddImage.appendChild(cTag('img',{ "src":data.parameters, "alt":"", "class":"img-responsive" }));
                }
                else if(data.field_type==='AddFile'){
                    let showAddFile = document.querySelector("#showAddFile");
                    showAddFile.innerHTML = '';
                        let viewLink = cTag('a',{'style': "color: #009; text-decoration: underline; margin-bottom: 10px;", "target":"_blank", "href":data.parameters, "alt":Translate('View File') });
                        viewLink.innerHTML = Translate('View File');
                    showAddFile.appendChild(viewLink);
                }
            }
            document.getElementById("field_name").focus();
            checkFormFieldType();
        }, 500);			
    }
	return true;
}
function checkFormFieldType(){
	let field_type = document.querySelector("#field_type").value;
	['TextOnlyRow', 'DropDownRow', 'SectionBreakRow', 'AddImageRow', 'AddFileRow'].forEach(item=>{
        if(document.querySelector(`#${item}`).style.display !== 'none'){
            document.querySelector(`#${item}`).style.display = 'none';
        }
    });
    if(document.querySelector("#FieldRequiredRow").style.display === 'none'){
        document.querySelector("#FieldRequiredRow").style.display = '';
    }
	if(field_type==='DropDown'){
        if(document.querySelector("#DropDownRow").style.display === 'none'){
            document.querySelector("#DropDownRow").style.display = '';
        }
		addDropDownOptions();
	}
	else if(['TextOnly', 'SectionBreak'].includes(field_type)){
        if(document.querySelector("#"+field_type+"Row").style.display === 'none'){
            document.querySelector("#"+field_type+"Row").style.display = '';
        }
        if(document.querySelector("#FieldRequiredRow").style.display !== 'none'){
            document.querySelector("#FieldRequiredRow").style.display = 'none';
        }
	}
	else if(['AddImage', 'AddFile'].includes(field_type)){
        if(document.querySelector("#"+field_type+"Row").style.display === 'none'){
            document.querySelector("#"+field_type+"Row").style.display = '';
        }
	}
}
async function AJsave_forms_field(hidePopup){
    let submitBtn = document.querySelector(".btnmodel");
	let error_forms_field = document.getElementById("error_forms_field");
	error_forms_field.innerHTML = '';
	let field_type = document.querySelector("#field_type");
	if(document.querySelector("#field_name").value===''){
		error_forms_field.innerHTML = Translate('Missing Field name.');;
		document.querySelector("#field_name").focus();
		return false;
	}
	
	if(field_type.value===''){
		error_forms_field.innerHTML = Translate('Missing Field type.');
		field_type.focus();
		return false;
	}
	else if(field_type.value==='DropDown'){
		let DropDown = 0;
		let DropDownObj = document.getElementsByName("DropDown[]");
		for(let l=0; l<DropDownObj.length; l++){
			let oneRow = DropDownObj[l].value;
			if(oneRow !==''){DropDown++;}
		}
		if(DropDown===0){
			error_forms_field.innerHTML = Translate('Missing Dropdown Option.');
			DropDownObj[0].focus();
			return false;
		}
	}
	else if(field_type.value==='TextOnly' && document.querySelector("#TextOnly").value ===''){
		error_forms_field.innerHTML = Translate('Missing Text Only.');
		document.querySelector("#TextOnly").focus();
		return false;
	}
	else if(field_type.value==='AddImage' && document.querySelector("#AddImage").value ===''){
		error_forms_field.innerHTML = Translate('Missing Add Image');
		document.querySelector("#AddImage").focus();
		return false;
	}
	else if(field_type.value==='AddFile' && document.querySelector("#AddFile").value ===''){
		error_forms_field.innerHTML = Translate('Missing Add File');
		document.querySelector("#AddFile").focus();
		return false;
	}		
	btnEnableDisable(submitBtn,Translate('Saving'),true);
	
	const jsonData = serialize("#frmFormsField");
	const url = "/Settings/AJsave_forms_field/";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        btnEnableDisable(submitBtn,Translate('Save'),false);
        if(data.savemsg !=='error'){
            hidePopup();
            location.reload();
        }
        else{
            if(data.message === 'signatureExists')	error_forms_field.innerHTML = Translate('Signature already exists');		
            else if(data.message === 'noDataFound')	error_forms_field.innerHTML = Translate('There is no data found');		
            else error_forms_field.innerHTML = data.message+' '+Translate('already exists');		
        }
    }		
	return false;
}
async function AJorderup_forms_field(forms_id, order_val, preorder_val){
    const jsonData = {"forms_id":forms_id, "order_val":order_val, "preorder_val":preorder_val};
    const url = "/Settings/AJorderup_forms_field";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        location.reload();
    }				
}
//========================Customers====================//
//-------------------Custom Fields----------------------
function customers_custom_fields(){
    custom_fields_creator(`${Translate('Customers')} ${Translate('Custom Fields')}`,'customers')
}
//========================Products====================//
//-------------------Custom Fields----------------------
function products_custom_fields(){
    custom_fields_creator(`${Translate('Products')} ${Translate('Custom Fields')}`,'product');
}



function makedeletedicon(listidname){
	let countList = document.querySelector("ul#"+listidname).childElementCount;
	let removeclass = 'r'+listidname;
	if(document.querySelector( "#"+listidname ).classList.contains(removeclass)){
		document.querySelectorAll( "."+removeclass ).forEach(item=>{
			item.remove();
		})
	}	
	if(countList>1){
		for(let l=0; l<countList; l++){
			let listno = parseInt(l+1);
			
			if(listno<countList){
                const listNode = document.querySelector(`ul#${listidname} li:nth-child(${listno})`);				
				if(listidname==='rsListRow' && (listno===1 || listno===2)){}
                else{
					let aTag = cTag('a',{ "class":`removeicon ${removeclass}`, "href":"javascript:void(0);", "title":Translate('Remove this row') });
					aTag.appendChild(cTag('img',{ "align":"absmiddle","alt":Translate('Remove this row'),"title":Translate('Remove this row'),"src":"/assets/images/cross-on-white.gif" }))
					aTag.addEventListener('click',function(){
						this.parentNode.remove();
					})
					if(!listNode.classList.contains('readonlyStatus')) listNode.appendChild(aTag);
				}
			}
		}
	}                                    
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {
        myInfo, user, po_setup, barcode_labels, restrict_access, cash_Register_general, counting_Cash_Til, multiple_Drawers,
        invoices_general, customStatuses, repairCustomStatuses, ordersPrint, repairs_general, notifications, forms, formFields, devices_custom_fields,
        repairs_custom_fields, customers_custom_fields, products_custom_fields
    };
    if(['carriers',"conditions"].includes(segment2)) carriers_conditions();
    else await layoutFunctions[segment2]();            
    
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    leftsideHide("secondarySideMenu",'secondaryNavMenu');
});
