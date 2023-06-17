import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, number_format, DBDateToViewDate,DBDateRangeToViewDate, 
    confirm_dialog, alert_dialog, setSelectOpt, setTableHRows, showTopMessage, setOptions,listenToEnterKey,addPaginationRowFlex, 
    checkAndSetSessionData, getDeviceOperatingSystem, popup_dialog600, date_picker, daterange_picker_dialog, checkDateOnBlur, 
    checkNumericInputOnKeydown, fetchData, addCustomeEventListener, actionBtnClick, serialize, onClickPagination, historyTable, 
    activityFieldAttributes, validateRequiredField, controllNumericField
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

let width82 = '';
if(OS =='unknown'){width82 = 'width: 82px;';}

const listsFieldAttributes = [{'align':'left', 'data-title':Translate('Start Date')},
                    {'align':'left', 'data-title':Translate('End Date')},
                    {'align':'left', 'data-title':Translate('Rule Field')},
                    {'align':'left','data-title':Translate('Rule Match')},
                    {'align':'center','data-title':Translate('Sales Person')},
                    {'align':'right', 'data-title':Translate('Commissions')},
                    {'align':'center', 'data-title':Translate('Based on')}];
const uriStr = segment1+'/view';

async function filter_Commissions_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	const srule_field = document.getElementById("srule_field").value;
	jsonData['srule_field'] = srule_field;
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;    

    const url = '/'+segment1+'/AJgetPage/filter';    
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        setSelectOpt('srule_field', 'All', Translate('All Types'), data.ruleFieOpt, 0, data.ruleFieOpt.length);
        createListRows(data.tableRows);
        document.getElementById("totalTableRows").value = data.totalRows;
        document.getElementById("srule_field").value = srule_field;		
        onClickPagination();

        
    }
}

async function loadTableRows_Commissions_lists(){
	const jsonData = {};
	jsonData['srule_field'] = document.getElementById("srule_field").value;
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
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
    const CommissionTypes = {
        '0':Translate('SALES'),
        '1':Translate('Cost'),
        '2':Translate('Profit'),
    }
    if(data.length){
        data.forEach(item=>{
            const row = cTag('tr');
            item.forEach((itemInfo,indx)=>{
                if([0,8].includes(indx)) return;
                const cell = cTag('td');
                const attributes = listsFieldAttributes[indx-1];
                for (const key in attributes) {
                    cell.setAttribute(key,attributes[key]);
                }
                if(indx===5){
                    itemInfo = itemInfo||Translate('All Salesman');
                }
                else if(indx===6){
                    if(item[8]>0) itemInfo = itemInfo+'%';
                    else  itemInfo = addCurrency(itemInfo);
                }
                else if(indx===7){
                    itemInfo = CommissionTypes[itemInfo];
                }


                const link = cTag('a',{'class':`anchorfulllink`, 'href':`/${uriStr}/${item[0]}`});
                if([1,2].includes(indx)){
                    itemInfo = DBDateToViewDate(itemInfo, 0, 1);
                }
                link.innerHTML = itemInfo||'\u2003';
                cell.appendChild(link);
                row.appendChild(cell);
            })
            table.appendChild(row);
        })
    }
    else{
		let colspan = listsFieldAttributes.length;
		const tableHeadRow2 = cTag('tr');
			const tdCol2 = cTag('td', {colspan:colspan});
			tdCol2.innerHTML = '';
        tableHeadRow2.appendChild(tdCol2);
		table.appendChild(tableHeadRow2);
	}
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    let input, list_filters, sortDropDown;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    //=====Hidden Fields for Pagination======//
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '30' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

    const titleRow = cTag('div', {class:'flexSpaBetRow outerListsTable'});
        const titleName = cTag('div', {class:'columnXS12 columnMD5 columnLG6'});
            const headerTitle = cTag('h2', {style: "text-align: start;"});
            headerTitle.innerHTML = Translate('Manage Commissions')+'&nbsp;';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title':Translate('Manage Commissions')});
            headerTitle.appendChild(infoIcon);
        titleName.appendChild(headerTitle);
    titleRow.appendChild(titleName);

        const buttonsName = cTag('div', {class: "columnXS12 columnMD7 columnLG6", 'style': "text-align: end;"});
            const createButton = cTag('a', {'href': "javascript:void(0);", title: Translate('Create Commission'), class: "btn cursor createButton", style: "margin-left: 10px;"});
            createButton.addEventListener('click', function(){AJgetPopup_Commissions(0);});
            createButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Commission'));
            const reportButton = cTag('a', {'href': "/Commissions/report", class: "btn defaultButton", title: Translate('Commissions Report')});
            reportButton.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Commissions Report'));
        buttonsName.append(reportButton, createButton);
    titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);

    const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
        sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnLG3"});
            const selectRuleField = cTag('select', {class: "form-control", name: "srule_field", id: "srule_field"});
            selectRuleField.addEventListener('change', filter_Commissions_lists);
                const ruleFieldOption = cTag('option', {'value': "All"});
                ruleFieldOption.innerHTML = Translate('All Types');
            selectRuleField.appendChild(ruleFieldOption);
        sortDropDown.appendChild(selectRuleField);
    filterRow.appendChild(sortDropDown);

        sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnLG3"});
            const selectSorting = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type"});
            selectSorting.addEventListener('change', filter_Commissions_lists);
            setOptions(selectSorting, {'0':Translate('Start Date DESC'), '1':Translate('Start Date ASC'), '2':Translate('Rule Field')}, 1, 0);
        sortDropDown.appendChild(selectSorting);
    filterRow.appendChild(sortDropDown);

        const searchDiv = cTag('div', {class: "columnXS12 columnSM4 columnLG3"});
            const SearchInGroup = cTag('div', {class: "input-group"}); 
                const searchField = cTag('input', {'keydown': listenToEnterKey(filter_Commissions_lists), 'type': "text", 'placeholder': Translate('Search Commissions'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50}); 
            SearchInGroup.appendChild(searchField);
                const span = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Commissions')});
                span.addEventListener('click', filter_Commissions_lists);
                    const searchIcon = cTag('i', {class: "fa fa-search"}); 
                span.appendChild(searchIcon);
            SearchInGroup.appendChild(span);
        searchDiv.appendChild(SearchInGroup);
    filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

    const divTable = cTag('div', {class: "flex"});
        const divTableColumn = cTag('div', {class: "columnXS12"});
            const divNoMore = cTag('div', {id: "no-more-tables"});
                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                    const listHead = cTag('thead', {class: "cf"}); 
                        const columnNames = listsFieldAttributes.map(colObj=>(colObj['data-title']));
                        const listHeadRow = cTag('tr',{class:'outerListsTable'});
                            const thCol0 = cTag('th', {'style': width82});
                            thCol0.innerHTML= columnNames[0];

                            const thCol1 = cTag('th', {'style': width82});
                            thCol1.innerHTML = columnNames[1];

                            const thCol2 = cTag('th', {'width': "20%"});
                            thCol2.innerHTML = columnNames[2];

                            const thCol3 = cTag('th');
                            thCol3.innerHTML = columnNames[3];

                            const thCol4 = cTag('th', {'width': "10%"});
                            thCol4.innerHTML = columnNames[4];

                            const thCol5 = cTag('th', {'width': "10%"});
                            thCol5.innerHTML = columnNames[5];

                            const thCol6 = cTag('th', {'width': "10%"});
                            thCol6.innerHTML = columnNames[5];
                        listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6);
                    listHead.appendChild(listHeadRow);
                listTable.appendChild(listHead);
                    const listBody = cTag('tbody', {id: "tableRows"});
                listTable.appendChild(listBody);
            divNoMore.appendChild(listTable);
        divTableColumn.appendChild(divNoMore);
    divTable.appendChild(divTableColumn);
    showTableData.appendChild(divTable);
    addPaginationRowFlex(showTableData);
    
    //======sessionStorage =======//
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    const srule_field = 'All', sorting_type = '0';

    checkAndSetSessionData('srule_field', srule_field, list_filters);
    checkAndSetSessionData('sorting_type', sorting_type, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    addCustomeEventListener('filter',filter_Commissions_lists);
    addCustomeEventListener('loadTable',loadTableRows_Commissions_lists);
    filter_Commissions_lists(true);
}

async function filter_Commissions_view(){
    let page = 1;
	document.getElementById("page").value = page;
	const jsonData = {};
	jsonData['commissions_id'] = document.getElementById("table_idValue").value;
    
	jsonData['shistory_type'] = document.getElementById("shistory_type").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

    const url = '/'+segment1+'/AJgetHPage/filter';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        setTableHRows(data.tableRows, activityFieldAttributes);
        document.getElementById("totalTableRows").value = data.totalRows;
        onClickPagination();
    }
    
}

async function loadTableRows_Commissions_view(){
	const jsonData = {};
	jsonData['commissions_id'] = document.getElementById("table_idValue").value;
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

async function AJ_view_MoreInfo(){
	const commissions_id = document.getElementById("table_idValue").value;
	const jsonData = {};
	jsonData['commissions_id'] = commissions_id;
    const url = '/'+segment1+'/AJ_view_MoreInfo';
        
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const viewBasicInfo = document.getElementById("viewBasicInfo");
        viewBasicInfo.innerHTML = '';
            const viewLeft = cTag('div', {class: "customInfoGrid columnXS12 columnSM6", 'style': "border-right: 1px solid #CCC;"});
                const startDateLabel = cTag('label');
                startDateLabel.innerHTML = Translate('Start Date')+' : ';
                const startDateValue = cTag('span');
                startDateValue.innerHTML = DBDateToViewDate(data.start_date, 0, 1);
            viewLeft.append(startDateLabel, startDateValue);

                const endDateLabel = cTag('label');
                endDateLabel.innerHTML = Translate('End Date')+' : ';
                const endDateValue = cTag('span');
                endDateValue.innerHTML = DBDateToViewDate(data.end_date, 0, 1);
            viewLeft.append(endDateLabel, endDateValue);

                const ruleFieldLabel = cTag('label');
                ruleFieldLabel.innerHTML = Translate('Rule Field')+' : ';
                const ruleFieldValue = cTag('span');
                ruleFieldValue.innerHTML = data.rule_match;
            viewLeft.append(ruleFieldLabel, ruleFieldValue);
        viewBasicInfo.appendChild(viewLeft);

            const viewRight = cTag('div', {class: "customInfoGrid columnXS12 columnSM6"});
                const ruleMatchLabel = cTag('label');
                ruleMatchLabel.innerHTML = Translate('Rule Match')+' : ';
                const ruleMatchValue = cTag('span');
                ruleMatchValue.innerHTML = data.rule_match;
            viewRight.append(ruleMatchLabel, ruleMatchValue);
                    
                const commissionsLabel = cTag('label');
                commissionsLabel.innerHTML = Translate('Commissions')+' : ';
                const commissionsValue = cTag('span');
                if(data.is_percent>0) commissionsValue.innerHTML = number_format(data.amount)+'%';
                else commissionsValue.innerHTML = addCurrency(data.amount);
            viewRight.append(commissionsLabel, commissionsValue);

                const salesmanLabel = cTag('label');
                salesmanLabel.innerHTML = Translate('Salesman')+' : ';
                const salesmanValue = cTag('span');
                salesmanValue.innerHTML = data.salesmanStr||Translate('All Salesman');
            viewRight.append(salesmanLabel, salesmanValue);
        viewBasicInfo.appendChild(viewRight);

        if(data.commissions_publish>0){
            const buttonSection = cTag('div', {class: "columnXS12"});
                const buttonName = cTag('div', {class: "flexCenterRow"});
                    const editButton = cTag('button', {class: "btn editButton", style: "margin-bottom: 10px;", title: Translate('Edit')});
                    editButton.addEventListener('click', function(){AJgetPopup_Commissions(commissions_id, 0);});
                    editButton.innerHTML = Translate('Edit');
                buttonName.appendChild(editButton);
            buttonSection.appendChild(buttonName);

                    const removeButton = cTag('button', {class: "btn archiveButton", style: "margin-bottom: 10px;", title: Translate('Remove')});
                    removeButton.addEventListener('click', AJ_remove_Commissions);
                    removeButton.innerHTML = Translate('Remove');
                buttonName.appendChild(removeButton);
            buttonSection.appendChild(buttonName);
            viewBasicInfo.appendChild(buttonSection);
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

        filter_Commissions_view();
    }
}

function view(){
    let segment4 = 1;
    if(pathArray.length>4){segment4 = pathArray[4];}
    
    let commissions_id = parseInt(segment3);
    if(commissions_id==='' || isNaN(commissions_id)){commissions_id = 0;}    
    
    let list_filters;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow"});
            const titleName = cTag('div', {class: "columnXS8"});
                const headerTitle = cTag('h2', {style: "text-align: start;"});
                headerTitle.innerHTML = Translate('Commission Details')+' ';
                headerTitle.appendChild(cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays the list of your commissions')}));
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);

            const buttonName = cTag('div', {class: "columnXS4", 'style': "text-align: end;"});
                const aTag = cTag('a', {'href': "/Commissions/lists", class: "btn defaultButton", title: Translate('Commissions List')});
                aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Commissions List'));
            buttonName.appendChild(aTag);
        titleRow.appendChild(buttonName);
    showTableData.appendChild(titleRow);
    
        const viewInfoFlex = cTag('div', {class: "flexSpaBetRow"});
            const viewInfoColumn = cTag('div', {class: "columnXS12"});
            viewInfoColumn.appendChild(cTag('header', {class: "imageContainer flexSpaBetRow", 'style': "padding: 5px 15px;", id: "viewBasicInfo", align:"left"}));
        viewInfoFlex.appendChild(viewInfoColumn);
    showTableData.appendChild(viewInfoFlex);

    const divContainerFlex = cTag('div'); 
        const divContainerColumn = cTag('div', {class: "columnXS12"});
            let hiddenProperties = {
                'note_forTable': 'commissions' ,
                'table_idValue': commissions_id ,
            }
        divContainerColumn.appendChild(historyTable(Translate('Commission History'),hiddenProperties));
    divContainerFlex.appendChild(divContainerColumn);
    showTableData.appendChild(divContainerFlex);
    
    const loadData = 'AJ_'+segment2+'_MoreInfo';
    const fn = window[loadData];
    if(typeof fn === "function"){fn();}

    //======sessionStorage =======//
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    const shistory_type = '';
    checkAndSetSessionData('shistory_type', shistory_type, list_filters);

    addCustomeEventListener('filter',filter_Commissions_view);
    addCustomeEventListener('loadTable',loadTableRows_Commissions_view);
    AJ_view_MoreInfo();
}

function AJ_remove_Commissions(){
	confirm_dialog(Translate('Remove Commission'), Translate('Are you sure you want to remove this commission permanently?'), confirmAJ_remove_Commissions);
}

async function confirmAJ_remove_Commissions(hidePopup){
	const removeBtn = document.querySelector('.archive');
	removeBtn.innerHTML = Translate('Removing')+'...';
	removeBtn.disabled = true;

	const commissions_id = document.getElementById("table_idValue");
	
    const jsonData = {};
	jsonData['commissions_id'] = commissions_id.value;
    
    const url = '/'+segment1+'/AJ_remove_Commissions';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.removeCount>0){
			window.location = '/Commissions/lists';
		}
		else{						
			showTopMessage('alert_msg', Translate('Could not remove commission.'))
            removeBtn.innerHTML = Translate('Confirm');
            removeBtn.disabled = false;
		}
        hidePopup();
    }		
}

async function AJgetPopup_Commissions(commissions_id){
	const jsonData = {};
	jsonData['commissions_id'] = commissions_id;

    const url = '/'+segment1+'/AJgetPopup';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        let input, requireField, dropDown;
        const formDialog = cTag('div');
        formDialog.innerHTML = '';
        // formDialog.appendChild(cTag('div',{id:'error_commission',class:'errormsg'}));
            const commissionForm = cTag('form', {'action': "#", name: "frmcommission", id: "frmcommission", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
            commissionForm.addEventListener('submit', saveCommissionsForm);
            commissionForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
                const commissionFormContainer = cTag('div', {class: "columnXS12", 'align': "left"});
                    const ruleFieldFlex = cTag('div', {class: "flex"});
                        const ruleFieldName = cTag('div', {class: "columnXS4"}); 
                            const ruleFieldLabel = cTag('label', {'for': "rule_field", 'style': "padding-top: 8px;"});
                            ruleFieldLabel.innerHTML = Translate('Rule Field');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            ruleFieldLabel.appendChild(requireField);
                        ruleFieldName.appendChild(ruleFieldLabel);
                    ruleFieldFlex.appendChild(ruleFieldName);

                        dropDown = cTag('div', {class: "columnXS8"}); 
                            const selectRuleField = cTag('select', {'required': "required", class: "form-control", name: "rule_field", id: "rule_field"});
                            selectRuleField.addEventListener('change', showRuleMatch);
                                const ruleFieldOption = cTag('option', {'value': ""});
                                ruleFieldOption.innerHTML= Translate('Select Rule Field');
                            selectRuleField.appendChild(ruleFieldOption);
                            setOptions(selectRuleField, data.rule_fieldOptions, 0, 1);                                 
                        dropDown.appendChild(selectRuleField);
                        dropDown.appendChild(cTag('span',{id:'error_commission',class:'errormsg'}));
                    ruleFieldFlex.appendChild(dropDown);
                commissionFormContainer.appendChild(ruleFieldFlex);
                    
                    //Rule Match
                    const ruleMatchFlex = cTag('div', {class: "flex"});
                        const ruleMatchName = cTag('div', {class: "columnXS4"}); 
                            const ruleMatchLabel = cTag('label', {'for': "rule_match", 'style': "padding-top: 8px;"});
                            ruleMatchLabel.innerHTML = Translate('Rule Match');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            ruleMatchLabel.appendChild(requireField);
                        ruleMatchName.appendChild(ruleMatchLabel);
                    ruleMatchFlex.appendChild(ruleMatchName);

                        dropDown = cTag('div', {class: "columnXS8"}); 
                            const selectRuleMatch = cTag('select', {'required': "required", class: "form-control", name: "rule_match", id: "rule_match"});
                                const ruleMatchOption = cTag('option', {'value': ""});
                                ruleMatchOption.innerHTML = Translate('Select Rule Match');
                            selectRuleMatch.appendChild(ruleMatchOption);
                            setOptions(selectRuleMatch, data.rule_matchOptions, 1, 1);                                
                        dropDown.appendChild(selectRuleMatch);
                        dropDown.appendChild(cTag('span',{id:'error_match',class:'errormsg'}));
                    ruleMatchFlex.appendChild(dropDown);
                commissionFormContainer.appendChild(ruleMatchFlex);

                    // Per Fee
                    const perFeeBasedFlex = cTag('div', {class: "flex"}); 
                        const perFeeName = cTag('div', {class: "columnXS4"}); 
                            const perFeeLabel = cTag('label', {'for': "is_percent", 'style': "padding-top: 8px;"});
                            perFeeLabel.innerHTML = Translate('Per Fee');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            perFeeLabel.appendChild(requireField);
                        perFeeName.appendChild(perFeeLabel);
                    perFeeBasedFlex.appendChild(perFeeName);

                        dropDown = cTag('div', {class: "columnXS2"}); 
                            const selectCurrency = cTag('select', {'required': "required", class: "form-control", name: "is_percent", id: "is_percent"});
                            setOptions(selectCurrency, {'1':'%', '0':currency}, 1, 0);
                            selectCurrency.addEventListener('change',adjustAmountFieldAttributs);
                        dropDown.appendChild(selectCurrency);
                    perFeeBasedFlex.appendChild(dropDown);
                    
                        const basedOnName = cTag('div', {class: "columnXS3"}); 
                            const basedOnLabel = cTag('label', {'for': "is_cost", 'style': "padding-top: 8px;"});
                            basedOnLabel.innerHTML = Translate('Based on');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            basedOnLabel.appendChild(requireField);
                        basedOnName.appendChild(basedOnLabel);
                    perFeeBasedFlex.appendChild(basedOnName);

                        dropDown = cTag('div', {class: "columnXS3"}); 
                            const selectCost = cTag('select', {'required': "required", class: "form-control", name: "is_cost", id: "is_cost"});
                            setOptions(selectCost, {'0':Translate('SALES'), '1':Translate('Cost'),'2':Translate('Profit')}, 1, 0);
                        dropDown.appendChild(selectCost);
                    perFeeBasedFlex.appendChild(dropDown);
                commissionFormContainer.appendChild(perFeeBasedFlex);

                    //Amount
                    const amountFlex = cTag('div', {class: "flex"});
                        const amountName = cTag('div', {class: "columnXS4"});
                            const amountLabel = cTag('label', {'for': "amount", 'style': "padding-top: 8px;"});
                            amountLabel.innerHTML = Translate('Amount');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            amountLabel.appendChild(requireField);
                        amountName.appendChild(amountLabel);
                    amountFlex.appendChild(amountName);

                        const amountValue = cTag('div', {class: "columnXS8"}); 
                            let inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "amount", id: "amount", 'value': data.amount, 'data-min':'0', 'data-max': '999999.99', 'data-format':'d.dd'});
                            controllNumericField(inputField, '#error_amount');
                        amountValue.appendChild(inputField);
                        amountValue.appendChild(cTag('span',{id:'error_amount',class:'errormsg'}));
                    amountFlex.appendChild(amountValue);
                commissionFormContainer.appendChild(amountFlex);

                    //Start Date
                    const startDateFlex = cTag('div', {class: "flex"}); 
                        const startDateName = cTag('div', {class: "columnXS4"}); 
                            const startDateLabel = cTag('label', {'for': "start_date", 'style': "padding-top: 8px;"});
                            startDateLabel.innerHTML = Translate('Start Date');
                        startDateName.appendChild(startDateLabel);
                    startDateFlex.appendChild(startDateName);

                        const startDateValue = cTag('div', {class: "columnXS8"}); 
                            input = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "start_date", id: "start_date", 'value': DBDateToViewDate(data.start_date), 'maxlength': 10}); 
                            checkDateOnBlur(input,'#error_commission','Invalid Start Date');
                        startDateValue.appendChild(input);
                    startDateFlex.appendChild(startDateValue);
                commissionFormContainer.appendChild(startDateFlex);

                    //End date
                    const endDateFlex = cTag('div', {class: "flex"});
                        const endDateName = cTag('div', {class: "columnXS4"}); 
                            const endDateLabel = cTag('label', {'for': "end_date", 'style': "padding-top: 8px;"});
                            endDateLabel.innerHTML = Translate('End Date');
                        endDateName.appendChild(endDateLabel);
                    endDateFlex.appendChild(endDateName);

                        const endDateValue = cTag('div', {class: "columnXS8"});
                            input = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "end_date", id: "end_date", 'value': DBDateToViewDate(data.end_date), 'maxlength': 10}); 
                            checkDateOnBlur(input,'#error_commission','Invalid End Date');
                        endDateValue.appendChild(input);
                    endDateFlex.appendChild(endDateValue);
                commissionFormContainer.appendChild(endDateFlex);

                    //Salesman
                    const salesmanFlex = cTag('div', {class: "flex"});
                        const salesmanName = cTag('div', {class: "columnXS4"});
                            const salesmanLabel = cTag('label', {'for': "salesman", 'style': "padding-top: 8px;"});
                            salesmanLabel.innerHTML = Translate('Salesman');
                        salesmanName.appendChild(salesmanLabel);
                    salesmanFlex.appendChild(salesmanName);

                        dropDown = cTag('div', {class: "columnXS8"});
                            const selectSalesman = cTag('select', {class: "form-control", name: "salesman", id: "salesman"});
                                const salesmanOption = cTag('option', {'value': 0});
                                salesmanOption.innerHTML = Translate('All Salesman');
                            selectSalesman.appendChild(salesmanOption);
                            setOptions(selectSalesman, data.salesmanOptions, 1, 1);                                
                        dropDown.appendChild(selectSalesman);
                    salesmanFlex.appendChild(dropDown);
                commissionFormContainer.appendChild(salesmanFlex);
            commissionForm.appendChild(commissionFormContainer);

                input = cTag('input', {'type': "hidden", name: "commissions_id", 'value': commissions_id});
            commissionForm.appendChild(input);
        formDialog.appendChild(commissionForm);

        popup_dialog600(Translate('Commission Information'), formDialog, Translate('Save'),saveCommissionsForm);           
			
        setTimeout(function() {        
            adjustAmountFieldAttributs();       
            if(data.rule_field !==''){
                document.getElementById("rule_field").value = data.rule_field;
            }
            if(data.rule_match !==''){
                document.getElementById("rule_match").value = data.rule_match;
            }
            if(data.is_percent !==''){
                document.getElementById("is_percent").value = data.is_percent;
            }
            document.getElementById("is_cost").value = data.is_cost;
            
            if(data.salesman !==0){
                document.getElementById("salesman").value = data.salesman;
            }
                            
            document.getElementById("salesman").value = data.salesman;

            date_picker('#start_date');
            date_picker('#end_date');

            document.getElementById("rule_field").focus();
        }, 500);			
    }

	return true;
}

function adjustAmountFieldAttributs(){
    let is_percent = parseInt(document.getElementById('is_percent').value);
    let amountField = document.getElementById('amount');
    let error_commission = document.getElementById('error_commission');

	if(is_percent===1){
        amountField.setAttribute('data-max','99.99');
        if(amountField.value>99.99) error_commission.innerHTML = "Amount can't be > than 99.99";
        if(amountField.value<0) error_commission.innerHTML = "Amount can't be < than 0";

	}
    else{
        amountField.setAttribute('data-max','999999.99');
        error_commission.innerHTML = '';
    }
}

async function showRuleMatch(){
    const jsonData = {};
	jsonData['commissions_id'] = document.frmcommission.commissions_id.value;
    jsonData['rule_field'] = document.getElementById("rule_field").value;

    
    const url = '/'+segment1+'/showRuleMatchOptions';
    fetchData(afterFetch,url,jsonData);  

    function afterFetch(data){
        let select = document.getElementById("rule_match");
        select.innerHTML = '';
        let option= cTag('option', {'value': ""});
            option.innerHTML= Translate('Select Rule Match');
        select.appendChild(option);
        setOptions(select, data.ruleMatchOpt, 1, 1);
        if(data.rule_match !=='' && data.rule_match in data.ruleMatchOpt){
            select.value = data.rule_match;
        }
        
    }
    return false;
}

async function saveCommissionsForm(hidePopup){
    const errorId = document.getElementById("error_commission");
    const error_match = document.getElementById("error_match");
    const error_amount = document.getElementById("error_amount");
    errorId.innerHTML = '';
    error_match.innerHTML = '';
    error_amount.innerHTML = '';
    
    let pTag;
    const rule_field = document.getElementById("rule_field");
	if(rule_field.value==='' && rule_field.value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Rule field');
        errorId.appendChild(pTag);
		rule_field.focus();
        rule_field.classList.add('errorFieldBorder');
		return false;
	}else{
		rule_field.classList.remove('errorFieldBorder');
	}

	const rule_match = document.getElementById("rule_match");
	if(rule_match.value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Rule Match');
        error_match.appendChild(pTag);
		rule_match.focus();
        rule_match.classList.add('errorFieldBorder');
		return false;
	}else{
		rule_match.classList.remove('errorFieldBorder');
	}

	const is_percent = document.getElementById("is_percent");
	if(is_percent.value===''){
        pTag = cTag('p');
        pTag.innerHTML = Translate('Missing Per Fee');
        errorId.appendChild(pTag);
		is_percent.focus();
		return false;
	}
    	
	const amount = document.getElementById("amount");
    if(!validateRequiredField(amount,'#error_commission') || !amount.valid()) return;

    actionBtnClick('.btnmodel', Translate('Saving'), 1);
    const jsonData = serialize('#frmcommission');
	
    const url = '/'+segment1+'/AJsaveCommissions';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        actionBtnClick('.btnmodel', Translate('Save'), 0);

        if(data.savemsg !=='error'){
			window.location = '/Commissions/view/'+data.commissions_id;
            
		}
        else if(data.returnStr=='errorOnAdding'){
            alert_dialog(Translate('Alert message'), Translate('Error occured while adding new commission! Please try again.'), Translate('Ok'));
            
        }  
        else if(data.returnStr=='Name_Already_Exist'){
            alert_dialog(Translate('Alert message'), Translate('This rule field and rule match already exists. Try again with different rule field /rule match.'), Translate('Ok'));
            
        }  
        else{
            alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
            
        }
        hidePopup();
    }
	return false;
}

async function reportData(){
    const semployee_id = document.getElementById("semployee_id");
    const date_range = document.getElementById("date_range").value;

	const jsonData = {};
	jsonData['semployee_id'] = semployee_id.value;
    jsonData['showing_type'] = document.getElementById("showing_type").value;
    jsonData['date_range'] = date_range;
    
    
	
    const url = '/'+segment1+'/fetching_reportdata';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        storeSessionData(jsonData);
        semployee_id.innerHTML = '';
            const option= cTag('option', {'value': ''});
            option.innerHTML = Translate('All Salesman')
        semployee_id.appendChild(option);
        if(date_range.length>0){
            setOptions(semployee_id, data.employeeOpts, 1, 1);
        }
        semployee_id.value = jsonData['semployee_id'];

        let commitionstr, reportHeadRow, tdCol;
        const Searchresult = document.getElementById("Searchresult");
        Searchresult.innerHTML = '';
        const tableData = data.tableData;
        if(tableData.length>0){
            tableData.forEach(oneRow => {
                const employeename = oneRow.employeename;
                let totalcommissions = oneRow.totalcommissions;
                const tableSubData = oneRow.tableSubData;
                
                reportHeadRow = cTag('tr');
                    tdCol = cTag('td', {'style': 'font-weight: bold;', 'data-title': Translate('Sales Person'), 'align': "left", 'colspan': 5});
                    tdCol.innerHTML = employeename;
                reportHeadRow.appendChild(tdCol);
                    tdCol = cTag('td', {'style': 'font-weight: bold;', 'data-title': Translate('Total Commissions'), 'align': "right"});
                    tdCol.innerHTML = addCurrency(totalcommissions);
                reportHeadRow.appendChild(tdCol);
                Searchresult.appendChild(reportHeadRow);
                        
                if(tableSubData.length>0){
                    tableSubData.forEach(oneRowObj=>{
                        const boldclass = oneRowObj.boldclass;
                        let rule_field = oneRowObj.rule_field;
                        const rule_match = oneRowObj.rule_match;
                        let fromtoStr = oneRowObj.fromtoStr;
                        if(fromtoStr !=''){
                            fromtoStr = 'from '+DBDateRangeToViewDate(fromtoStr, 1).replace(' - ', ' to ');
                        }
                        commitionstr = oneRowObj.commitionstr;
                        let rowtotalpricestr = oneRowObj.rowtotalprice;
                        const rowtotalcommissions = addCurrency(oneRowObj.rowtotalcommissions);
                        const statusDetails = oneRowObj.statusDetails;

                        if(oneRowObj.is_percent>0){
                            commitionstr = number_format(commitionstr)+'%';
                            rowtotalpricestr = addCurrency(oneRowObj.rowtotalprice);
                        }
                        else{
                            commitionstr = addCurrency(commitionstr);
                        }

                        reportHeadRow = cTag('tr');
                            tdCol = cTag('td', {'data-title': ""});
                            tdCol.innerHTML = '\u00a0';
                        reportHeadRow.appendChild(tdCol);
                            tdCol = cTag('td', {'data-title': Translate('Rule Information'), 'align': "left"});
                            if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;')};
                            tdCol.append(rule_field = rule_match +' '+ fromtoStr);
                        reportHeadRow.appendChild(tdCol);
                            tdCol = cTag('td', {width: "10%", 'data-title': Translate('Commissions'), 'align': "right"});
                            if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;')};
                            tdCol.innerHTML = commitionstr;
                        reportHeadRow.appendChild(tdCol);
                            tdCol = cTag('td', {width: "10%", 'data-title': Translate('Qty/Sales'), 'align': "right"});
                            if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;')};
                            tdCol.innerHTML = rowtotalpricestr;
                        reportHeadRow.appendChild(tdCol);
                            tdCol = cTag('td', {width: "10%", 'data-title': Translate('Total Commissions'), 'align': "right"});
                            if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;')};
                            tdCol.innerHTML = rowtotalcommissions;
                        reportHeadRow.appendChild(tdCol);
                            tdCol = cTag('td', {'data-title': ""});
                            tdCol.innerHTML = '\u00a0';
                        reportHeadRow.appendChild(tdCol);
                        Searchresult.appendChild(reportHeadRow);

                        if(statusDetails.length){
                            statusDetails.forEach(subOneRow => {
                                const salesdatetime = DBDateToViewDate(subOneRow[0], 0, 1);
                                const invoice_no = subOneRow[1];
                                commitionstr = subOneRow[2];
                                let qtytotalvaluestr = subOneRow[3];
                                const qtytotalcommissionsstr = addCurrency(subOneRow[4]);

                                if(oneRowObj.is_percent>0){
                                    commitionstr = number_format(commitionstr)+'%';
                                    qtytotalvaluestr = addCurrency(qtytotalvaluestr);
                                }
                                else{
                                    commitionstr = addCurrency(commitionstr);
                                }
            
                                reportHeadRow = cTag('tr');
                                    tdCol = cTag('td', {'data-title': ""});
                                    tdCol.innerHTML = '\u00a0';
                                reportHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': Translate('Sales Information'), 'align': "left"});
                                    tdCol.innerHTML = '\u2003 \u2003' + salesdatetime + '\u2003';
                                        const invoiceLink = cTag('a', {'href': "/Invoices/view/"+invoice_no, 'style': "color: #009; text-decoration: underline;", title: Translate('View Invoice')});
                                        invoiceLink.innerHTML = invoice_no +' ';    
                                            const linkIcon = cTag('i', {class: "fa fa-link"}); 
                                        invoiceLink.appendChild(linkIcon);
                                    tdCol.appendChild(invoiceLink);
                                reportHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': Translate('Commissions'), 'align': "right"});
                                    tdCol.innerHTML = commitionstr;
                                reportHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': Translate('Qty/Sales'), 'align': "right"});
                                    tdCol.innerHTML = qtytotalvaluestr;
                                reportHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': Translate('Total Commissions'), 'align': "right"});
                                    tdCol.innerHTML = qtytotalcommissionsstr;
                                reportHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': ""});
                                    tdCol.innerHTML = '\u00a0';
                                reportHeadRow.appendChild(tdCol);
                                Searchresult.appendChild(reportHeadRow);
                            });      
                        }
                    })
                }
                
            })
        }
        else{
            reportHeadRow = cTag('tr');
                tdCol = cTag('td', {'colspan':"6"});
                tdCol.innerHTML = '';
            reportHeadRow.appendChild(tdCol);
        Searchresult.appendChild(reportHeadRow);
        }

        
    }
}

function report(){
    let todayDate, lastWeekDate, input, list_filters, sortDropDown, dropDownName, ShowingTypeOption;
    const now = new Date();
    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){todayDate = now.getDate()+'-'+(now.getMonth()+1)+'-'+now.getFullYear();}
    else{todayDate = (now.getMonth()+1)+'/'+now.getDate()+'/'+now.getFullYear();}
    
    const lastWeek = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000));
    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){lastWeekDate = lastWeek.getDate()+'-'+(lastWeek.getMonth()+1)+'-'+lastWeek.getFullYear();}
    else{lastWeekDate = (lastWeek.getMonth()+1)+'/'+lastWeek.getDate()+'/'+lastWeek.getFullYear();}
    
    let date_range = lastWeekDate + ' - ' + todayDate;

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow"});
            const titleName = cTag('div', {class: "columnSM6"});
                const headerTitle = cTag('h2', {'style': "text-align: start;"});
                headerTitle.innerHTML = Translate('Commission Report')+' ';
                    const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title':Translate('Commission Report')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);

            const buttonsName = cTag('div', {class: "columnSM6", 'style': "text-align: end;"});
                const printButton = cTag('a', {class: "btn printButton", 'style': "margin-left: 15px;", 'href': "javascript:void(0);", title: Translate('Print')});
                printButton.addEventListener('click', Commissions_reportPrint);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.appendChild(printButton);
                const aTag = cTag('a', {'href': "/Commissions/lists", class: "btn defaultButton", 'style': " margin-left: 15px;", title: Translate('Commissions List')});
                aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Commissions List'));
            buttonsName.appendChild(aTag);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);

        const filterRow = cTag('div', {class: "flexEndRow"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                dropDownName = cTag('div', {class: "input-group", id: "date_rangeid"});
                    const employeeLabel = cTag('label', {for: "semployee_id", class: "input-group-addon cursor"});
                    employeeLabel.innerHTML = Translate('Salesman');
                dropDownName.appendChild(employeeLabel);
                    const selectEmployee = cTag('select', {name: "semployee_id", id: "semployee_id", class: "form-control"});
                    selectEmployee.addEventListener('change', reportData);
                        const employeeOption = cTag('option', {value: "0"});
                        employeeOption.innerHTML = Translate('All Salesman');
                    selectEmployee.appendChild(employeeOption);
                dropDownName.appendChild(selectEmployee);
            sortDropDown.appendChild(dropDownName);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                dropDownName = cTag('div', {class: "input-group", id: "date_rangeid"});
                    const showingLabel = cTag('label', {for: "showing_type", class: "input-group-addon cursor"});
                    showingLabel.innerHTML = Translate('View');
                dropDownName.appendChild(showingLabel);
                    const selectShowingType = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectShowingType.addEventListener('change', reportData);
                        ShowingTypeOption = cTag('option', {value: "Summary"});
                        ShowingTypeOption.innerHTML = Translate('Summary');
                    selectShowingType.appendChild(ShowingTypeOption);
                        ShowingTypeOption = cTag('option', {value: "Detailed"});
                        ShowingTypeOption.innerHTML = Translate('Detailed Summary');
                    selectShowingType.appendChild(ShowingTypeOption);
                dropDownName.appendChild(selectShowingType);
            sortDropDown.appendChild(dropDownName);
        filterRow.appendChild(sortDropDown);

            const searchDiv = cTag('div', {class: "columnXS12 columnSM4 columnMD3"});
                const dateRangeSearch = cTag('div', {class: "input-group daterangeContainer"});
                    input = cTag('input', {type: "hidden", name: "pageURI", id: "pageURI", value: "Commissions/report"});
                dateRangeSearch.appendChild(input);
                    input = cTag('input', {'required': "required", 'minlength': 23, 'maxlength': 23,  type: "text", class: "form-control", 'style': "padding-left: 35px;", name: "date_range", id: "date_range", 'value': date_range});
                    daterange_picker_dialog(input);
                dateRangeSearch.appendChild(input);
                    const span = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('Date wise Search')});
                    span.addEventListener('click', reportData);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    span.appendChild(searchIcon);
                dateRangeSearch.appendChild(span);
            searchDiv.appendChild(dateRangeSearch);
        filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

        const divTable = cTag('div', {class: "flex"});
            const divTableColumn = cTag('div', {class: "columnXS12"}); 
                const divNoMore = cTag('div', {id: "no-more-tables"});
                    const reportTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                        const reportHead = cTag('thead', {class: "cf"});
                            const reportHeadRow = cTag('tr');
                                const thCol0 = cTag('th', {'width': "8%"});
                                thCol0.innerHTML = Translate('Salesman');
                                const thCol1 = cTag('th', {'colspan': 4});
                                thCol1.innerHTML = '&nbsp;';
                                const thCol2 = cTag('th', {'width': "8%"});
                                thCol2.innerHTML = Translate('Commissions');
                            reportHeadRow.append(thCol0, thCol1, thCol2);
                        reportHead.appendChild(reportHeadRow);
                    reportTable.appendChild(reportHead);
                        const reportBody = cTag('tbody', {id: "Searchresult"});
                    reportTable.appendChild(reportBody);
                divNoMore.appendChild(reportTable);
            divTableColumn.appendChild(divNoMore);
        divTable.appendChild(divTableColumn);
    showTableData.appendChild(divTable);

    //======sessionStorage =======//
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    const semployee_id = '', showing_type = 'Summary';

    checkAndSetSessionData('semployee_id', semployee_id, list_filters);
    checkAndSetSessionData('showing_type', showing_type, list_filters);

    if(list_filters.hasOwnProperty("date_range")){
        date_range = list_filters.date_range;
    }
    document.getElementById("date_range").value = date_range;
    reportData();
}

function Commissions_reportPrint(){
    let todayDate;
	let title = Translate('Commission Report');
    let semployee_id = document.getElementById('semployee_id');
    let filterby = Translate('Salesman')+': '+semployee_id.options[semployee_id.selectedIndex].innerText;
	let showing_type = document.getElementById("showing_type");
	filterby += ', '+Translate('View')+': '+showing_type.options[showing_type.selectedIndex].innerText;
	let date_range = document.getElementById("date_range").value;
	if(date_range !==''){
		filterby += ', '+Translate('Date Range')+': '+date_range;
	}
	
    let divContents = document.getElementById("no-more-tables").cloneNode(true);
	
	let now = new Date();
	if(calenderDate.toLowerCase()==='dd-mm-yyyy'){todayDate = now.getDate()+'-'+(now.getMonth()+1)+'-'+now.getFullYear();}
	else{todayDate = (now.getMonth()+1)+'/'+now.getDate()+'/'+now.getFullYear();}

    const additionaltoprows = cTag('div');
        let companyNameDiv = cTag('div',{ 'class':`flexSpaBetRow` });
            let divWidth30 = cTag('div',{ 'style': "font-weight: bold; font-size: 18px;" });
            divWidth30.innerHTML = stripslashes(companyName);
        companyNameDiv.appendChild(divWidth30);
            let titleDiv = cTag('div',{ 'style': "font-size: 20px; font-weight: bold;" });
            titleDiv.innerHTML = title;
        companyNameDiv.appendChild(titleDiv);
            let dateDiv = cTag('div',{ 'style': "font-size: 16px;" });
            dateDiv.innerHTML = todayDate;
        companyNameDiv.appendChild(dateDiv);
    additionaltoprows.appendChild(companyNameDiv);
    additionaltoprows.appendChild(cTag('div',{ 'style': "border-top: 1px solid #CCC; margin-top: 10px;" }));
        let div100Width = cTag('div',{style:'margin-bottom:10px'});
        div100Width.innerHTML = filterby;
    additionaltoprows.appendChild(div100Width);    
    divContents.prepend(additionaltoprows);
	
	let day = new Date();
	let id = day.getTime();
	let w = 900;
	let h = 600;
	let scrl = 1;
	let winl = (screen.width - w) / 2;
	let wint = (screen.height - h) / 2;
	let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	
    let printWindow = window.open('', '" + id + "', winprops);
    let html = cTag('html');
        let head = cTag('head');
            let titleTag = cTag('title');
            titleTag.innerHTML = title;
        head.appendChild(titleTag);
        head.appendChild(cTag('meta',{ 'charset':`utf-8`}));
            const style = cTag('style');
            style.append(
                `@page {size: auto;}
                body{ font-family:Arial, sans-serif, Helvetica; min-width:98%; margin:0; padding:1%;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                .flexSpaBetRow {display: flex;flex-flow: row wrap;justify-content: space-between;}
                table{border-collapse:collapse; width: 100%;}
                .table-bordered th {background:#F5F5F6;}
                .table-bordered td, .table-bordered th { border:1px solid #DDDDDD; padding:8px 10px;}
                .table-bordered td.bgnone {background-color:#FFF;border:0px solid #fff;}`
            );
        head.appendChild(style);
    html.appendChild(head);
        let body = cTag('body');
        body.append(divContents);
    html.appendChild(body);
    printWindow.document.write("<!DOCTYPE html>");
    printWindow.document.appendChild(html);
	printWindow.document.close();
	
	let is_chrome = Boolean(window.chrome);
    let document_focus;
	if (is_chrome) {
		printWindow.onload = function () {
			printWindow.window.print();
			document_focus = true;
		};
	}
	else{
		document_focus = false;
		printWindow.document.onreadystatechange = function () {
			let state = document.readyState
			if (state === 'interactive') {}
			else if (state === 'complete') {
				setTimeout(function(){
					document.getElementById('interactive');
					printWindow.print();
					document_focus = true;
				},1000);
			}
		}
	}
	printWindow.setInterval(function() {
		let deviceOpSy = getDeviceOperatingSystem();
		if (document_focus === true && deviceOpSy==='unknown') { printWindow.window.close(); }
	}, 500);
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, view, report};
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});