import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, emailcheck, checkPhone, noPermissionWarning, removeVariables, AJremoveData, 
    confirm_dialog, alert_dialog, setTableRows, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, checkAndSetSessionData, 
    popup_dialog, popup_dialog600, popup_dialog1000, daterange_picker_dialog, dynamicImport, applySanitizer, getOneRowInfo, archiveData, 
    unarchiveData, fetchData, listenToEnterKey, triggerEvent, addCustomeEventListener, actionBtnClick, serialize, onClickPagination, 
    customAutoComplete, AJautoComplete, leftsideHide, historyTable, activityFieldAttributes
} from './common.js';

if([''].includes(segment2)) segment2 = 'dashboard';

const uriStr = segment1+'/view';

function createListRow(data,tdAttributes,tableName,filterCBF,resetCBF){
    let table = document.getElementById("tableRows");
    table.innerHTML = '';
    if(data.length){
        data.forEach((item)=>{
            const tr = cTag('tr');
            item.forEach((info,indx)=>{
                if(indx===0) return;
                    const td = cTag('td');
                    const attributes = tdAttributes[indx-1];
                    for (const key in attributes) {
                        td.setAttribute(key,attributes[key]);
                    }
                    if(tableName==='sub_group' && indx===1){
                        info = accountTypes(info);
                    }
                        const a = cTag('a',{class:"anchorfulllink", click:()=>getOneRowInfo(tableName,item[0],filterCBF,resetCBF)});                    
                        a.innerHTML = info;
                    td.appendChild(a);
                tr.appendChild(td);
            })
            table.appendChild(tr);
        })
    }
}

function listHeader(label){
    const header = cTag('div',{class:'outerListsTable'});
        const headerTitle = cTag('h2', { 'style': "padding: 5px; text-align: start;"});
        headerTitle.append(label+' ');
            let infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page captures the accounts settings')});
        headerTitle.appendChild(infoIcon);
    header.appendChild(headerTitle);
    return header;
}

function leftsideMenu(){
    const NavLink = {
        dashboard: Translate('Account Dashboard'),
        sub_group: Translate('Manage Sub-Group'),
        ledger: Translate('Manage Ledger'),
        receiptVoucher: Translate('Receipt Voucher'),
        paymentVoucher: Translate('Payment Voucher'),
        journalVoucher: Translate('Journal Voucher'),
        contraVoucher: Translate('Contra Voucher'),
        purchaseVoucher: Translate('Purchase Voucher'),
        salesVoucher: Translate('Sales Voucher'),
        dayBook: Translate('Day Book'),
        ledgerReport: Translate('Ledger Report'),
        trialBalance: Translate('Trial Balance'),
        receiptPayment: Translate('Receipt & Payment')
    }

    let sideMenu = cTag('div', {class: "columnMD2 columnSM3", 'style': "margin: 0;"});
        let callOutDiv = cTag('div', {'style': "padding-top: 0;", class: "innerContainer"});
            let sideMenuLink = cTag('a', {'href': "javascript:void(0);", id: "secondarySideMenu"});
                let faFontSize = cTag('i', {class: "fa fa-align-justify", 'style': "margin-bottom: 10px; font-size: 2em;"});
            sideMenuLink.appendChild(faFontSize);
        callOutDiv.appendChild(sideMenuLink);
            let ulSetting = cTag('ul', {class: "secondaryNavMenu settingslefthide"});
            for (let uriVal in NavLink) {
                    let menuItem = segment2;
                    let liTag = cTag('li');

                    if(menuItem === uriVal){
                        liTag.setAttribute('class',"activeclass");
                        liTag.setAttribute('style',"padding-top: 10px; padding-bottom: 10px;");
                            let sideMenuHeader = cTag('h4', {'style': "font-size: 18px;"});
                            sideMenuHeader.innerHTML = NavLink[uriVal];
                            liTag.appendChild(sideMenuHeader);
                    }else{
                        let titleVal = NavLink[uriVal];
                        let aTag = cTag('a', {'href': "/Accounts/"+uriVal, title: titleVal});
                            let navSpan = cTag('span');
                            navSpan.innerHTML = NavLink[uriVal];
                        aTag.appendChild(navSpan);
                        liTag.appendChild(aTag);
                    }
                ulSetting.appendChild(liTag);
            }
        callOutDiv.appendChild(ulSetting);
    sideMenu.appendChild(callOutDiv);
    return sideMenu;
}

function subHeader_Search_Bar(headerLabel,searchLabel,filterCBF){
    const div = cTag('div', {class: "columnXS12 outerListsTable"});
        let subHeaderTitle = cTag('h2');
        subHeaderTitle.innerHTML = headerLabel+' '+Translate('List')+' ';
            const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': headerLabel});
        subHeaderTitle.appendChild(infoIcon);
    div.appendChild(subHeaderTitle);

        const searchColumn = cTag('div', {class: "flexEndRow columnXS12"});
            const filterDiv = cTag('div', {class: "columnXS6"});
                const filterType = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type"});
                filterType.addEventListener('change', filterCBF);
                setOptions(filterType, {'All':Translate('All')+' '+headerLabel, 'Archived':Translate('Archived')+' '+headerLabel}, 1, 0); 
            filterDiv.appendChild(filterType);       
        searchColumn.appendChild(filterDiv);
            let searchInGroup = cTag('div', {class: "columnXS6 input-group"});
                let inputField = cTag('input', {'keydown':listenToEnterKey(filterCBF),'type': "text", 'placeholder': searchLabel, 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
            searchInGroup.appendChild(inputField);
                let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: searchLabel});
                searchSpan.addEventListener('click',filterCBF);
                    const searchIcon = cTag('i', {class: "fa fa-search"});
                searchSpan.appendChild(searchIcon);                                    
            searchInGroup.appendChild(searchSpan);
        searchColumn.appendChild(searchInGroup);
    div.appendChild(searchColumn);
    return div;
}

function controller_bar(id,cancelHandler){
    const controller = cTag('div', {class: "flexStartRow"});
    controller.appendChild(cTag('input', {'type': "hidden", name: id, id: id, 'value': 0}));
    controller.appendChild(cTag('input', {'type': "hidden", name: 'nameVal', id: 'nameVal', 'value': ''}));
    controller.appendChild(cTag('input', {'click':cancelHandler,'type': "button", name: "reset", id: "reset", 'value': Translate('Cancel'), class: "btn defaultButton", 'style': "display:none; margin-right: 10px;"}));
    controller.appendChild( cTag('input', {'type': "button", name: "unarchive", id: "unarchive", 'value': Translate('Unarchive'), class: "btn bgcoolblue", 'style': "display:none; margin-right: 10px;",'click':unarchiveManageData}));
    controller.appendChild( cTag('input', {'type': "button", name: "archive", id: "archive", 'value': Translate('Archive'), class: "btn archiveButton", 'style': "display:none; margin-right: 10px;", 'click':AJremoveData}));
    //controller.appendChild( cTag('input', {'type': "button", name: "merge", id: "merge", 'value': Translate('Merge'), class: "btn defaultButton",style:'display:none; margin-right: 10px;','click':mergeDataPopup}));
    controller.appendChild(cTag('input', {'type': "submit", id: "submit", class: "btn saveButton", 'style': "margin-right: 10px;", 'value': Translate('Save') }));
    return controller;
}

function hidden_items(parent,page){
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '34' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
    parent.appendChild(input);
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

    let sorting_type = '0';
    if(document.querySelector('#sorting_type')) checkAndSetSessionData('sorting_type', sorting_type, list_filters); 
    
    const shistory_type = '';
    if(document.querySelector('#shistory_type')) checkAndSetSessionData('shistory_type', shistory_type, list_filters); 

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

async function AJsave_Accounts(event=false,fieldID,proceedToSave){
    if(event){event.preventDefault();}

    let submit =  document.querySelector("#submit");
    submit.value = Translate('Saving')+'...';
    submit.disabled = true;

    let jsonData = {keyword_search:document.getElementById(fieldID).value,sdata_type: "Archived",limit: 9};
    const url = `/Accounts/AJgetPage_${segment2}/filter`;

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const ArchivedData = data;
        let inArchive = ArchivedData.tableRows.filter(item=>{
            if(segment2==='sub_group'){
                return item[1]===document.getElementById('account_type').value && item[2]===document.getElementById('name').value;
            }
            else{
                return item[1]===document.getElementById('name').value;
            }
        })
        
        if(inArchive.length>0){
            let dialog = cTag('div');
            dialog.innerHTML = Translate('This name already exists <b>IN ARCHIVED!</b> Please try again with a different name. Do you really want to unarchive it?');
            popup_dialog(
                dialog,
                {
                    title:Translate('Unarchive'),
                    width:500,
                    buttons: {
                        _Cancel: {
                            text:Translate('Cancel'),
                            class: 'btn defaultButton', 'style': "margin-left: 10px;", click: function(hidePopup) {
                                hidePopup();
                            },
                        },
                        actionbutton:{
                            text:Translate('Unarchive'),
                            class: 'btn bgcoolblue btnmodel', 'style': "margin-left: 10px;", click: function(hidePopup) {
                                document.getElementById(segment2+'_id').value = inArchive[0][0];
                                unarchiveManageData();
                                hidePopup();
                            },
                        }
                    }
                }
            );
            submit.value = Translate('Save');
            submit.disabled = false;
        }
        else{
            proceedToSave();
        }
    }
}

function unarchiveManageData(){
    let tableName = segment2;
    let tableId = document.getElementById(segment2+'_id').value;
    confirm_dialog(Translate('Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{        
        unarchiveData(null,{tablename:tableName, tableidvalue:tableId, publishname:segment2+'_publish'},afterUnarchive);
        function afterUnarchive(){
            hidePopup();
            document.getElementById('sdata_type').value = 'All';
            triggerEvent('filter');
            triggerEvent('reset');
        }
    })
}

function accountTypes(account_type=0){
    const data = {'0' : 'Select Group Name','1': 'Assets','2':'Liabilities','3':'Equity','4':'Revenue/Income','5':'Expenses','6':'Purchase'};
    if(account_type>0){
        if (account_type in data) {
            return data[account_type];
        }
        return '';
    }
    else{return data;}
}
//==========================Dashboard-Part=========================//
async function dashboard(){
	
    let modulesInfo = {
		'1': {label:Translate('Manage Sub-Group'),fileName:'sub_group',icon:'category.png'},
		'2': {label:Translate('Manage Ledger'),fileName:'ledger',icon:'ledger.png'},
		'3': {label:Translate('Receipt Voucher'),fileName:'receiptVoucher',icon:'receipt.png'},
		'4': {label:Translate('Payment Voucher'),fileName:'paymentVoucher',icon:'payment.png'},
		'5': {label:Translate('Journal Voucher'),fileName:'journalVoucher',icon:'journal.png'},
		'6': {label:Translate('Contra Voucher'),fileName:'contraVoucher',icon:'contra.png'},
		'7': {label:Translate('Purchase Voucher'),fileName:'purchaseVoucher',icon:'purchase.png'},
		'8': {label:Translate('Sales Voucher'),fileName:'salesVoucher',icon:'sales.png'},
		'9': {label:Translate('Day Book'),fileName:'dayBook',icon:'dayBook.png'},
		'10': {label:Translate('Ledger Report'),fileName:'ledgerReport',icon:'ledgerReport.png'},
		'11': {label:Translate('Trial Balance'),fileName:'trialBalance',icon:'trialBalance.png'},
		'13': {label:Translate('Receipt & Payment'),fileName:'receiptPayment',icon:'payment.png'},
	}
	const showTableData = document.getElementById('viewPageInfo');

    const modulesRow = cTag('div',{ 'class':`flexSpaBetRow` });
		const modulesColumn = cTag('div',{ 'class':`columnSM12`, 'style': "margin-top: 0px;" });
			const modulesWidget = cTag('div',{ 'class':`cardContainer ` });
				let modulesWidgetHeader = cTag('div',{ 'class':`cardHeader ` });
					const modulesHeader = cTag('h3');
					modulesHeader.innerHTML = Translate('Account Modules list');
				modulesWidgetHeader.appendChild(modulesHeader);
			modulesWidget.appendChild(modulesWidgetHeader);
				let modulesContent = cTag('div',{ 'class':`cardContent` });
					let ulMenu = cTag('ul',{ 'class':`flexStartRow moduleLists`, 'style': "text-align: center;" });
					for (const key in modulesInfo) {
						if(modulesInfo[key]){
							let fonticon = modulesInfo[key].icon;
							let module = modulesInfo[key].fileName;
							let labelVal = modulesInfo[key].label;
							let liMenu = cTag('li');
								let homeDiv = cTag('div',{ 'class':`homeiconmenu boxshadow `, 'style': "background: #0185b6;" });
									let aTag = cTag('a',{ 'class':`firstclild sidebarlink`, 'style': "color: white;", 'href': '/Accounts/'+module , 'title': labelVal  });
										let iconTag = cTag('img',{ 'src':"/assets/images/Accounts/"+ fonticon, 'style': "height:32px;", 'alt': labelVal});
									aTag.append(cTag('br'), iconTag, cTag('br'), labelVal);
								homeDiv.appendChild(aTag);
							liMenu.appendChild(homeDiv);
							ulMenu.appendChild(liMenu);
						}
					}
				modulesContent.appendChild(ulMenu);
			modulesWidget.appendChild(modulesContent);
		modulesColumn.appendChild(modulesWidget);
	modulesRow.appendChild(modulesColumn);
	showTableData.appendChild(modulesRow);
    AJ_dashboard_MoreInfo();
}

async function AJ_dashboard_MoreInfo(){
    const url = '/'+segment1+'/AJ_dashboard_MoreInfo';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        
     }
}

/*======Sub-Group Module======*/
async function sub_group(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
 
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Sub-Group')));
     
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());
 
        let callOutDivStyle = "margin-top:0; background:#FFF; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let subGroupColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
            const subGroupRow = cTag('div', {class: "flexSpaBetRow"});
                const subGroupHeader = cTag('div', {class: "columnXS12 columnMD7"});
                subGroupHeader.appendChild(subHeader_Search_Bar(Translate('Sub-Group'),Translate('Search here'),filter_Accounts_sub_group));
                    const subGroupTable = cTag('div', {class: "flexSpaBetRow"});
                        const subGroupTableColumn = cTag('div', {class: "columnXS12", 'style': "position:relative;"});
                            const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                const listHead = cTag('thead', {class: "cf"});
                                    const listHeadRow = cTag('tr');
                                        const thCol0 = cTag('th');
                                        thCol0.innerHTML = Translate('Group Name');
 
                                        const thCol1 = cTag('th', {'width': "60%"});
                                        thCol1.innerHTML = Translate('Sub-Group Name');
                                    listHeadRow.append(thCol0, thCol1);
                                listHead.appendChild(listHeadRow);
                            listTable.appendChild(listHead);
                                const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                            listTable.appendChild(listBody);
                        subGroupTableColumn.appendChild(listTable);
                    subGroupTable.appendChild(subGroupTableColumn);
                subGroupHeader.appendChild(subGroupTable);
                addPaginationRowFlex(subGroupHeader);
            subGroupRow.appendChild(subGroupHeader);
 
                const addSubGroup = cTag('div', {class: "columnXS12 columnMD5"});
                   let subGroupTitle = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;", id: "formtitle"});
                   subGroupTitle.innerHTML = Translate('Add New Sub-Groups');
                addSubGroup.appendChild(subGroupTitle);
 
                   const subGroupForm = cTag('form', {'action': "#", name: "frmSubGroup", id: "frmSubGroup", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                   subGroupForm.addEventListener('submit',(event)=>AJsave_Accounts(event, 'name', AJsave_sub_group));
                      const accountTypeRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                         const accountTypeLabel = cTag('label', {'for': "account_type"});
                         accountTypeLabel.innerHTML = Translate('Group Name');
                            let requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                         accountTypeLabel.appendChild(requiredField);
                      accountTypeRow.appendChild(accountTypeLabel);
                         const selectAccountType = cTag('select', {class: "form-control", 'required': "", name: "account_type", id: "account_type"});
                         const accountTypeOpt = accountTypes();
                         let optField;
                         for(const [optValue, optLabel] of Object.entries(accountTypeOpt)) { 
                               optField = cTag('option',{value: optValue});
                               optField.innerHTML = optLabel;
                            selectAccountType.appendChild(optField);
                         }
                      accountTypeRow.appendChild(selectAccountType);
                   subGroupForm.appendChild(accountTypeRow);
 
                      const subGroupNameRow = cTag('div', {class: "flexSpaBetRow"});
                         const nameLabel = cTag('label', {'for': "name"});
                         nameLabel.innerHTML = Translate('Sub-Group Name');
                      subGroupNameRow.appendChild(nameLabel);
                         const nameInputField = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "name", id: "name", 'value': "", 'size': 50, 'maxlength': 50});
                      subGroupNameRow.appendChild(nameInputField);
                   subGroupForm.appendChild(subGroupNameRow);
                   subGroupForm.appendChild(controller_bar('sub_group_id',resetForm_sub_group));
                addSubGroup.appendChild(subGroupForm);
             subGroupRow.appendChild(addSubGroup);                
          callOutDiv.appendChild(subGroupRow);
       subGroupColumn.appendChild(callOutDiv);
    parentRow.appendChild(subGroupColumn);
    showTableData.appendChild(parentRow);  
    
    addCustomeEventListener('filter',filter_Accounts_sub_group);
    addCustomeEventListener('loadTable',loadTableRows_Accounts_sub_group);
    addCustomeEventListener('reset',resetForm_sub_group);
    getSessionData();
    filter_Accounts_sub_group(true);
}
 
async function filter_Accounts_sub_group(){
    let page = 1;
    document.querySelector("#page").value = page;
     
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
    jsonData['keyword_search'] = document.querySelector('#keyword_search').value;	
    jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
    jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
    jsonData['limit'] = checkAndSetLimit();
    jsonData['page'] = page;	
     
    const url = '/'+segment1+'/AJgetPage_sub_group/filter';
 
    fetchData(afterFetch,url,jsonData);
 
    function afterFetch(data){
       storeSessionData(jsonData);
       createListRow(data.tableRows, [{'datatitle':Translate('Group Name'), 'align':'left'}, {'datatitle':Translate('Sub-Group Name'), 'align':'left'}], 'sub_group',filter_Accounts_sub_group,resetForm_sub_group);
       document.querySelector("#totalTableRows").value = data.totalRows;			
       onClickPagination();
    }
}
 
async function loadTableRows_Accounts_sub_group(){
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
    jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
    jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
    jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
    jsonData['limit'] = checkAndSetLimit();
    jsonData['page'] = document.querySelector('#page').value;
         
    const url = '/'+segment1+'/AJgetPage_sub_group';
 
    fetchData(afterFetch,url,jsonData);
 
    function afterFetch(data){
       storeSessionData(jsonData);
       createListRow(data.tableRows, [{'datatitle':Translate('Group Name'), 'align':'left'}, {'datatitle':Translate('Sub-Group Name'), 'align':'left'}], 'sub_group',filter_Accounts_sub_group,resetForm_sub_group);
       onClickPagination();
    }
}
 
async function AJsave_sub_group(event=false){
    if(event){event.preventDefault();}
    let submit =  document.querySelector("#submit");
 
    const jsonData = serialize("#frmSubGroup");
    const url = '/'+segment1+'/AJsave_sub_group';
 
    fetchData(afterFetch,url,jsonData);
 
    function afterFetch(data){
        if(data.savemsg==='Add' || data.savemsg==='Update'){
            resetForm_sub_group();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Accounts_sub_group();
        }
        else if(data.returnStr=='errorOnAdding'){
            alert_dialog(Translate('Alert message'), Translate('Error occured while adding new name! Please try again.'), Translate('Ok'));
        }  
        else if(data.returnStr=='Name_Already_Exist'){
            alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
        }  
        else{
            alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
        }
        submit.value = Translate('Add')
        submit.disabled = false;     
    }
 
    return false;
}
 
async function resetForm_sub_group(){
    document.querySelector("#formtitle").innerHTML = Translate('Add New Sub-Groups');
    document.querySelector("#sub_group_id").value = 0;
    document.querySelector("#account_type").value = 0;
    document.querySelector("#name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = { dashboard, sub_group};
    layoutFunctions[segment2]();

    leftsideHide("secondarySideMenu",'secondaryNavMenu');

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    
    applySanitizer(document);
});