import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, DBDateToViewDate, printbyurl, confirm_dialog, 
    setSelectOpt, setTableRows, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, checkAndSetSessionData, 
    getMobileOperatingSystem, popup_dialog600, date_picker, daterange_picker_dialog, checkDateOnBlur, checkNumericInputOnKeydown, 
    applySanitizer, fetchData, addCustomeEventListener, actionBtnClick, callShowInputOrSelect, serialize, onClickPagination, 
    historyTable, activityFieldAttributes, listenToEnterKey, controllNumericField
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

const listsFieldAttributes = [{'align':'left', 'datatitle': Translate('Bill Date')},
                    {'align':'right', 'datatitle': Translate('Bill Number')},
                    {'align':'left', 'datatitle': Translate('Expense Type')},
                    {'align':'left','datatitle': Translate('Vendor Name')},
                    {'align':'right', 'datatitle': Translate('Bill Amount')},
                    {'align':'left', 'datatitle': Translate('Bill Paid')},
                    {'align':'left', 'datatitle': Translate('Reference')}];
const uriStr = segment1+'/view';

async function filter_Expenses_lists(){
    let page = 1;
    document.getElementById("page").value = page;
	
	const jsonData = {};
	let svendors_id = parseInt( document.getElementById("svendors_id").value);
	if(isNaN(svendors_id) || svendors_id===''){svendors_id = 0;}
	jsonData['svendors_id'] = svendors_id;

	jsonData['date_range'] =  document.getElementById("date_range").value;
	const sexpense_type =  document.getElementById("sexpense_type").value;
    jsonData['sexpense_type'] = sexpense_type;
	jsonData['sorting_type'] =  document.getElementById("sorting_type").value;
	jsonData['keyword_search'] =  document.getElementById("keyword_search").value;
	jsonData['totalRows'] =  document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] =  document.getElementById("rowHeight").value;
	jsonData['limit'] =  checkAndSetLimit();
	jsonData['page'] = page;

    const url = '/'+segment1+'/AJgetPage/filter';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        setSelectOpt('svendors_id', 0, Translate('All Vendors'), data.venOpt, 1, Object.keys(data.venOpt).length);
        setSelectOpt('sexpense_type', '', Translate('All Expense Type'), data.expTypOpt, 0, data.expTypOpt.length);
        setTableRows(data.tableRows, listsFieldAttributes, uriStr,[5], [1, 6]);
        document.getElementById("totalTableRows").value = data.totalRows;
        document.getElementById("svendors_id").value = svendors_id;
        document.getElementById("sexpense_type").value = sexpense_type;

        onClickPagination();
    }
}

async function loadTableRows_Expenses_lists(){
    let page = parseInt(document.getElementById("page").value);
    if(isNaN(page) || page===0){
        page = 1;
        document.getElementById("page").value = page;
	}
	
	const jsonData = {};
	let svendors_id = parseInt( document.getElementById("svendors_id").value);
	if(isNaN(svendors_id) || svendors_id===''){svendors_id = 0;}
	jsonData['svendors_id'] = svendors_id;

	jsonData['date_range'] =  document.getElementById("date_range").value;
	const sexpense_type =  document.getElementById("sexpense_type").value;
    jsonData['sexpense_type'] = sexpense_type;
	jsonData['sorting_type'] =  document.getElementById("sorting_type").value;
	jsonData['keyword_search'] =  document.getElementById("keyword_search").value;
	jsonData['totalRows'] =  document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] =  document.getElementById("rowHeight").value;
	jsonData['limit'] =  checkAndSetLimit();
	jsonData['page'] = page;

    const url = '/'+segment1+'/AJgetPage';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        setTableRows(data.tableRows, listsFieldAttributes, uriStr,[5], [1, 6]);
        onClickPagination();
    }
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

    //=====Hidden Fields for Pagination======//
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '31' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        const input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

        let sortDropDown;
        const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable"});
            const titleName = cTag('div', {class: "columnXS12 columnMD6"});
                const headerTitle = cTag('h2', {'style': "text-align: start;"});
                headerTitle.innerHTML = Translate('Manage Expenses')+' ';
                    const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('This page displays the information of expense')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);

            const buttonsName = cTag('div', {class: "columnXS12 columnMD6", 'style': "text-align: end;"});
                const aTag = cTag('a', {'href': "/Expenses/profit_loss", class: "btn defaultButton", title: Translate('P&L Statement')});
                aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('P&L Statement'));
            buttonsName.appendChild(aTag); 
                const expenseButton = cTag('a', {class: "btn cursor createButton", 'style': "margin-left: 15px;", title: Translate('Create Expense'), 'href': "javascript:void(0);"});
                expenseButton.addEventListener('click', function(){AJgetPopup_Expenses(0);});
                expenseButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Expense'));
            buttonsName.appendChild(expenseButton);
                const printButton = cTag('button', {class: "btn printButton", 'style': "margin-left: 15px; padding-bottom: 10px;"});
                printButton.addEventListener('click', printExpLists);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.appendChild(printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);

        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM3 columnMD2"});
                const selectVendors = cTag('select', {class: "form-control", name: "svendors_id", id: "svendors_id"});
                selectVendors.addEventListener('change', filter_Expenses_lists);
                    const vendorOption = cTag('option', {'value': 0});
                    vendorOption.innerHTML = Translate('All Vendors');
                selectVendors.appendChild(vendorOption);
            sortDropDown.appendChild(selectVendors);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD2"});
                const selectExpense = cTag('select', {class: "form-control", name: "sexpense_type", id: "sexpense_type"});
                selectExpense.addEventListener('change', filter_Expenses_lists);
                    const expenseTypeOption = cTag('option', {'value': ""});
                    expenseTypeOption.innerHTML = Translate('All Expense Type');
                selectExpense.appendChild(expenseTypeOption);
            sortDropDown.appendChild(selectExpense);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM5 columnMD3"});
                const selectSorting = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type"});
                selectSorting.addEventListener('change', filter_Expenses_lists);
                setOptions(selectSorting, {'0':Translate('Bill Date DESC'), '1':Translate('Bill Date ASC'), '2':Translate('Expense Type')}, 1, 0);
            sortDropDown.appendChild(selectSorting);
        filterRow.appendChild(sortDropDown);

            const dateRange = cTag('div', {class: "columnXS6 columnMD2 daterangeContainer"});
                const dateRangeField = cTag('input', {class: "form-control date_range", 'style': "padding-left: 35px;", name: "date_range", id: "date_range", 'minlength': 23, 'maxlength': 23, 'type': 'text','autocomplete':"off"});
                daterange_picker_dialog(dateRangeField);
            dateRange.appendChild(dateRangeField);
        filterRow.appendChild(dateRange);

            const searchDiv = cTag('div', {class: "columnXS6 columnMD3"});
                const SearchInGroup = cTag('div', {class: "input-group"});
                    const searchField = cTag('input', {'keydown':listenToEnterKey(filter_Expenses_lists), class: "form-control", name: "keyword_search", id: "keyword_search", 'placeholder': Translate('Search Expenses'), 'maxlength': 50, 'type': 'text'});
                SearchInGroup.appendChild(searchField);
                    let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Expenses')});
                    searchSpan.addEventListener('click', filter_Expenses_lists);
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
                            thCol0.innerHTML= columnNames[0];
                        
                            const thCol1 = cTag('th', {'width': '10%'});
                            thCol1.innerHTML= columnNames[1];
                        
                            const thCol2 = cTag('th', {'width': '20%'});
                            thCol2.innerHTML = columnNames[2];
                        
                            const thCol3 = cTag('th');
                            thCol3.innerHTML=columnNames[3];
                        
                            const thCol4 = cTag('th', {'width': '10%'});
                            thCol4.innerHTML = columnNames[4];
                        
                            const thCol5 = cTag('th', {'style': "width: 80px;"});
                            thCol5.innerHTML = columnNames[5];

                            const thCol6 = cTag('th', {'width': '20%'});
                            thCol6.innerHTML = columnNames[6];
                        listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6);
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
    
    let svendors_id = 0, sexpense_type = '', sorting_type = '0';

    checkAndSetSessionData('svendors_id', svendors_id, list_filters);
    checkAndSetSessionData('sexpense_type', sexpense_type, list_filters);
    checkAndSetSessionData('sorting_type', sorting_type, list_filters);

    let date_range = '';
    if(list_filters.hasOwnProperty("date_range")){
        date_range = list_filters.date_range;
    }    
    document.getElementById("date_range").value = date_range;
    setTimeout(()=>document.getElementById("date_range").value = date_range,0);
       
    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    addCustomeEventListener('filter',filter_Expenses_lists);
    addCustomeEventListener('loadTable',loadTableRows_Expenses_lists);
    filter_Expenses_lists(true);
}

async function filter_Expenses_view(){
    let page = 1;
    document.getElementById("page").value = page;
    let shistory_type = document.getElementById("shistory_type").value;

	const jsonData = {};
	jsonData['expenses_id'] = document.getElementById("table_idValue").value;
	jsonData['shistory_type'] = shistory_type;
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

async function loadTableRows_Expenses_view(){
    let page = parseInt(document.getElementById("page").value);
    if(isNaN(page) || page===0){
        page = 1;
        document.getElementById("page").value = page;
	}

	const jsonData = {};
	jsonData['expenses_id'] = document.getElementById("table_idValue").value;
	jsonData['shistory_type'] = document.getElementById("shistory_type").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetHPage/';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        setTableHRows(data.tableRows, activityFieldAttributes);
        onClickPagination();
    }
}

async function AJ_view_MoreInfo(){
	const expenses_id = document.getElementById("table_idValue").value;
	const jsonData = {};
	jsonData['expenses_id'] = expenses_id;

    const url = '/'+segment1+'/AJ_view_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        const viewBasicInfo = document.getElementById("viewBasicInfo");
        viewBasicInfo.innerHTML = '';
            const viewLeft = cTag('div', {class: "customInfoGrid columnSM6", align:'left', 'style': "border-right: 1px solid #CCC;"});
                const expenseTypeLabel = cTag('label');
                expenseTypeLabel.innerHTML = Translate('Expense Type')+' : ';
                const expenseTypeValue = cTag('span');
                expenseTypeValue.innerHTML = data.expense_type;
            viewLeft.append(expenseTypeLabel, expenseTypeValue);

                const vendorNameLabel = cTag('label');
                vendorNameLabel.innerHTML = Translate('Vendor Name')+' : ';
                const vendorNameValue = cTag('span');
                vendorNameValue.innerHTML = data.vendors_name;
            viewLeft.append(vendorNameLabel, vendorNameValue);

                const billDateLabel = cTag('label');
                billDateLabel.innerHTML = Translate('Bill Date')+' : ';
                const billDateValue = cTag('span');
                billDateValue.innerHTML = DBDateToViewDate(data.bill_date);
            viewLeft.append(billDateLabel, billDateValue);

                const billNumberLabel = cTag('label');
                billNumberLabel.innerHTML = Translate('Bill Number')+' : ';
                const billNumberValue = cTag('span');
                billNumberValue.innerHTML = data.bill_number;
            viewLeft.append(billNumberLabel, billNumberValue);
        viewBasicInfo.appendChild(viewLeft);

            const viewRight = cTag('div', {class: "customInfoGrid columnSM6", align:'left'});
                const billAmountLabel = cTag('label');
                billAmountLabel.innerHTML = Translate('Bill Amount')+' : ';
                const billAmountValue = cTag('span');
                billAmountValue.innerHTML = data.bill_amount.toFixed(2);
            viewRight.append(billAmountLabel, billAmountValue);

                const billPaidDateLabel = cTag('label');
                billPaidDateLabel.innerHTML = Translate('Bill Paid Date')+' : ';
                const billPaidDateValue = cTag('span');
                billPaidDateValue.innerHTML = DBDateToViewDate(data.bill_paid);
            viewRight.append(billPaidDateLabel, billPaidDateValue);

                const referenceLabel = cTag('label');
                referenceLabel.innerHTML = Translate('Reference')+' : ';
                const referenceValue = cTag('span');
                referenceValue.innerHTML = data.ref;
            viewRight.append(referenceLabel, referenceValue);
                const emptyLabel = cTag('label', {'style': "border: none;"});
                    const emptyBr = cTag('br');
                emptyLabel.appendChild(emptyBr);
                const emptySpan = cTag('span', {'style': "border: none;"});
            viewRight.append(emptyLabel, emptySpan);
        viewBasicInfo.appendChild(viewRight);

        if(data.expenses_publish>0){
            const buttonSection = cTag('div', {class: "columnSM12"});
                const buttonName = cTag('div', {class:"flexCenterRow", 'style': "margin-bottom: 10px;"});
                    const editButton = cTag('button', {class: "btn editButton", 'style': "margin-right: 15px;", title: Translate('Change Information')});
                    editButton.addEventListener('click', function(){AJgetPopup_Expenses(0);});
                    editButton.innerHTML = Translate('Edit');
                buttonName.appendChild(editButton);
            buttonSection.appendChild(buttonName);

                    const similarButton = cTag('button', {class: "btn createButton", 'style': "margin-right: 15px;", title: Translate('Create Similar Expense')});
                    similarButton.addEventListener('click', function(){AJgetPopup_Expenses(1);});
                    similarButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Similar Expense'));
                    if(OS =='unknown'){
                        similarButton.innerHTML = Translate('Create Similar Expense');
                    }
                buttonName.appendChild(similarButton);
            buttonSection.appendChild(buttonName);

                    const removeButton = cTag('button', {class: "btn archiveButton", title: Translate('Remove')});
                    removeButton.addEventListener('click', AJremoveExpense);
                    removeButton.innerHTML = Translate('Remove');                
                buttonName.appendChild(removeButton);
            buttonSection.appendChild(buttonName);
            viewBasicInfo.appendChild(buttonSection);
        }

        filter_Expenses_view();
	}
}

function view(){
    let segment4 = 1;
    if(pathArray.length>4){segment4 = pathArray[4];}
    
    let expenses_id = parseInt(segment3);
    if(expenses_id==='' || isNaN(expenses_id)){expenses_id = 0;}
    
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Expenses Details')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays the information of expense')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
            const aTag = cTag('a', {'href': "/Expenses/lists", class: "btn defaultButton", title: Translate('Expenses List')});
            aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Expenses List'));
        titleRow.appendChild(aTag);
    showTableData.appendChild(titleRow);
    
        const viewInfoColumn = cTag('div', {class: "columnSM12"});
            const viewInfoHeader = cTag('header', {class: "imageContainer flexSpaBetRow", 'style': "padding: 5px 15px;", id: "viewBasicInfo", align:"left"});
        viewInfoColumn.appendChild(viewInfoHeader);
    showTableData.appendChild(viewInfoColumn); 

        const divContainerFlex = cTag('div', {class: "flexSpaBetRow"});
            const divContainerColumn = cTag('div', {class: "columnXS12"});
            let hiddenProperties = {
                'note_forTable':'expenses',
                'table_idValue':expenses_id,
            }
            divContainerColumn.appendChild(historyTable(Translate('Expense History'),hiddenProperties));
        divContainerFlex.appendChild(divContainerColumn);
    showTableData.appendChild(divContainerFlex);
    
    //======sessionStorage =======//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    let shistory_type = '';
    checkAndSetSessionData('shistory_type', shistory_type, list_filters);

    addCustomeEventListener('filter',filter_Expenses_view);
    addCustomeEventListener('loadTable',loadTableRows_Expenses_view);
    AJ_view_MoreInfo();
}

async function AJgetPopup_Expenses(similar){
	let expenses_id = 0;
    if(segment2==='view'){
        expenses_id = document.getElementById("table_idValue").value;
    }
    
    const jsonData = {};
	jsonData['expenses_id'] = expenses_id;
    jsonData['similar'] = similar;
	
    const url = '/'+segment1+'/AJgetPopup';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let popupTitle;
        popupTitle = Translate('Expense Information');
        if(similar>0){
            expenses_id = 0;
            popupTitle = Translate('Create Similar Expense');
        }
        const formDialog = cTag('div');
            let inputGroup, requireField, dropDown, inputField;
            const expenseForm = cTag('form', {'action': "#", name: "frmexpense", id: "frmexpense", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                const expenseFormContainer = cTag('div', {class: "columnXS12"});
                    const expenseTypeRow = cTag('div', {class: "flex"});
                        const expenseTypeName = cTag('div', {class: "columnSM4", 'align': "left"});
                            const expenseTypeLabel = cTag('label', {'for': "expense_type"});
                            expenseTypeLabel.innerHTML = Translate('Expense Type');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            expenseTypeLabel.appendChild(requireField);
                        expenseTypeName.appendChild(expenseTypeLabel);
                    expenseTypeRow.appendChild(expenseTypeName);
                        dropDown = cTag('div', {class: "columnSM8", 'align': "left"});
                            inputGroup = cTag('div', {class: "input-group"});
                                const selectExpense = cTag('select', {'required': "required" ,class: "form-control", name: "expense_type", id: "expense_type"});
                                    const expenseOption = cTag('option', {'value': ""});
                                    expenseOption.innerHTML = Translate('Select Expense Type');
                                selectExpense.appendChild(expenseOption);
                                setOptions(selectExpense, data.expense_typeOptions, 0, 1);                             
                            inputGroup.appendChild(selectExpense);
                                const newExpenseName = cTag('input', {'type': "text", 'value': "", 'maxlength': 35, name: "expense_type_name", id: "expense_type_name", class: "form-control", 'style': "display: none;"});
                            inputGroup.appendChild(newExpenseName);
                                let newSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Expense Type')});
                                newSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
                            inputGroup.appendChild(newSpan);
                        dropDown.appendChild(inputGroup);
                        dropDown.appendChild(cTag('span',{id:'error_expense',class:'errormsg'}));
                    expenseTypeRow.appendChild(dropDown);
                expenseFormContainer.appendChild(expenseTypeRow);

                    const vendorRow = cTag('div', {class: "flex"});
                        const vendorName = cTag('div', {class: "columnSM4", 'align': "left"});
                            const vendorLabel = cTag('label', {'for': "vendors_id"});
                            vendorLabel.innerHTML = Translate('Vendor Name');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            vendorLabel.appendChild(requireField);
                        vendorName.appendChild(vendorLabel);
                    vendorRow.appendChild(vendorName);
                        dropDown = cTag('div', {class: "columnSM8", 'align': "left"});
                            inputGroup = cTag('div', {class: "input-group"});
                                const selectVendorName = cTag('select', {'required': "required", class: "form-control",  id: "vendors_id", name: "vendors_id"});
                                    const vendorNameOption = cTag('option', {'value': 0});
                                    vendorNameOption.innerHTML = Translate('Select Vendor Name');
                                selectVendorName.appendChild(vendorNameOption);
                                setOptions(selectVendorName, data.vendors_idOptions, 1, 1);
                            inputGroup.appendChild(selectVendorName);
                                const newVendorName = cTag('input', {'type': "text", 'value': "", 'maxlength': 35, name: "vendors_name", id: "vendors_name", class: "form-control", 'style': "display: none;"});
                            inputGroup.appendChild(newVendorName);
                                let newVendorSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Vendor')});
                                newVendorSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
                            inputGroup.appendChild(newVendorSpan);
                        dropDown.appendChild(inputGroup);
                        dropDown.appendChild(cTag('span',{id:'error_vendor',class:'errormsg'}));
                    vendorRow.appendChild(dropDown);
                expenseFormContainer.appendChild(vendorRow);
        
                    const billDateRow = cTag('div', {class: "flex"});
                        const billDateName = cTag('div', {class: "columnSM4", 'align': "left"});
                            const billDateLabel = cTag('label', {'for': "bill_date"});
                            billDateLabel.innerHTML = Translate('Bill Date');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            billDateLabel.appendChild(requireField);
                        billDateName.appendChild(billDateLabel);
                    billDateRow.appendChild(billDateName);
                        const billDateField = cTag('div', {class: "columnSM8", 'align': "left"});
                            inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "bill_date", id: "bill_date", 'value': DBDateToViewDate(data.bill_date), 'maxlength': 10});
                            checkDateOnBlur(inputField,'#error_date','Invalid '+Translate('Bill Date'));
                        billDateField.appendChild(inputField);
                        billDateField.appendChild(cTag('span',{id:'error_date',class:'errormsg'}));
                    billDateRow.appendChild(billDateField);
                expenseFormContainer.appendChild(billDateRow);

                    const billNumberRow = cTag('div', {class: "flex"});
                        const billNumberName = cTag('div', {class: "columnXS5 columnSM4", 'align': "left"});
                            const billNumberLabel = cTag('label', {'for': "bill_number"});
                            billNumberLabel.innerHTML = Translate('Bill Number');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            billNumberLabel.appendChild(requireField);
                        billNumberName.appendChild(billNumberLabel);
                    billNumberRow.appendChild(billNumberName);
                        const billNumberField = cTag('div', {class: "columnXS7 columnSM8", 'align': "left"});
                            inputField = cTag('input', {'required': "required", 'type': "text", class: "form-control", name: "bill_number", id: "bill_number", 'value': data.bill_number, 'maxlength': 20});
                        billNumberField.appendChild(inputField);
                        billNumberField.appendChild(cTag('span',{id:'error_bill',class:'errormsg'}));
                    billNumberRow.appendChild(billNumberField);
                expenseFormContainer.appendChild(billNumberRow);

                    const billAmountRow = cTag('div', {class: "flex"});
                        const billAmountName = cTag('div', {class: "columnXS5 columnSM4", 'align': "left"});
                            const billAmountLabel = cTag('label', {'for': "bill_amount"});
                            billAmountLabel.innerHTML = Translate('Bill Amount');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            billAmountLabel.appendChild(requireField);
                        billAmountName.appendChild(billAmountLabel);
                    billAmountRow.appendChild(billAmountName);
                        const billAmountField = cTag('div', {class: "columnXS7 columnSM8", 'align': "left"});
                            inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'999999.99','data-format':'d.dd', class: "form-control", name: "bill_amount", id: "bill_amount", 'value': data.bill_amount});
                            controllNumericField(inputField, '#error_bill_amount');
                        billAmountField.appendChild(inputField);
                        billAmountField.appendChild(cTag('span',{id:'error_bill_amount',class:'errormsg'}));
                    billAmountRow.appendChild(billAmountField);
                expenseFormContainer.appendChild(billAmountRow);

                    const billPaidRow = cTag('div', {class: "flex"});
                        const billPaidName = cTag('div', {class: "columnXS5 columnSM4", 'align': "left"});
                            const billPaidLabel = cTag('label', {'for': "bill_paid"});
                            billPaidLabel.innerHTML = Translate('Bill Paid Date');
                        billPaidName.appendChild(billPaidLabel);
                    billPaidRow.appendChild(billPaidName);
                        const billPaidDateField = cTag('div', {class: "columnXS7 columnSM8", 'align': "left"});
                            inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "bill_paid", id: "bill_paid", 'value': DBDateToViewDate(data.bill_paid), 'maxlength': 10});
                            checkDateOnBlur(inputField,'#error_expense','Invalid '+Translate('Bill Paid Date'));
                        billPaidDateField.appendChild(inputField);
                    billPaidRow.appendChild(billPaidDateField);
                expenseFormContainer.appendChild(billPaidRow);

                    const referenceRow = cTag('div', {class: "flex"});
                        const referenceName = cTag('div', {class: "columnXS5 columnSM4", 'align': "left"});
                            const referenceLabel = cTag('label', {'for': "ref"});
                            referenceLabel.innerHTML = Translate('Reference');
                        referenceName.appendChild(referenceLabel);
                    referenceRow.appendChild(referenceName);
                        const referenceField = cTag('div', {class: "columnXS7 columnSM8", 'align': "left"});
                            inputField = cTag('input', {'type': "text", class: "form-control", name: "ref", id: "ref", 'value': data.ref, 'maxlength': 30});
                        referenceField.appendChild(inputField);
                    referenceRow.appendChild(referenceField);
                expenseFormContainer.appendChild(referenceRow);

                    inputField = cTag('input', {'type': "hidden", name: "expenses_id", 'value': expenses_id});
                expenseFormContainer.appendChild(inputField);
            expenseForm.appendChild(expenseFormContainer);
        formDialog.appendChild(expenseForm);

        popup_dialog600(Translate('Expense Information'),formDialog,Translate('Save'),saveExpenseForm);
        setTimeout(function() {
            document.getElementById("expense_type").value = data.expense_type;
            document.getElementById("vendors_id").value = data.vendors_id;
            document.getElementById("expense_type").focus();
            date_picker('#bill_date');
            date_picker('#bill_paid');                

            callShowInputOrSelect();
            applySanitizer(formDialog);
        }, 500);
    }
}

async function saveExpenseForm(hidePopup){
    const errorStatus = document.getElementById("error_expense");
    const error_vendor = document.getElementById("error_vendor");
    const error_date = document.getElementById("error_date");
    const error_bill = document.getElementById("error_bill");
    const error_bill_amount = document.getElementById("error_bill_amount");
    errorStatus.innerHTML = '';
    error_vendor.innerHTML = '';
    error_date.innerHTML = '';
    error_bill.innerHTML = '';
    error_bill_amount.innerHTML = '';

    let bill_amount = document.getElementById('bill_amount');
    if(!bill_amount.valid()) return;

    let expense_type = document.getElementById("expense_type");
    let pTag;
    if(expense_type.value ==='' && document.getElementById("expense_type_name").value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Expense Type');
        errorStatus.appendChild(pTag);
        expense_type.focus();
        expense_type.classList.add('errorFieldBorder');
        return false;
    }else{
        expense_type.classList.remove('errorFieldBorder');
    }

    let vendorsId = document.getElementById("vendors_id");
    if(vendorsId.value == 0 && document.getElementById("vendors_name").value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Vendor Name');
        error_vendor.appendChild(pTag);
        vendorsId.focus();
        vendorsId.classList.add('errorFieldBorder');
        return false;
    }else{
        vendorsId.classList.remove('errorFieldBorder');
    }

    if(bill_date.value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Bill Date');
        error_date.appendChild(pTag);
        bill_date.focus();
        bill_date.classList.add('errorFieldBorder');
        return false;
    }else{
        bill_date.classList.remove('errorFieldBorder');
    }

    if(bill_number.value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Bill Number');
        error_bill.appendChild(pTag);
        bill_number.focus();
        bill_number.classList.add('errorFieldBorder');
        return false;
    }else{
        bill_number.classList.remove('errorFieldBorder');
    }

    actionBtnClick('.btnmodel', Translate('Saving'), 1);
    const jsonData = serialize('#frmexpense');
    
    const url = '/'+segment1+'/AJsaveExpense';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error'){
            window.location = '/Expenses/view/'+data.expenses_id;           
            hidePopup();
        }
        else if(data.returnStr===''){
            hidePopup();
        }
        else if(data.returnStr=='errorOnAdding'){
            errorStatus.innerHTML = Translate('Error occured while adding new expense! Please try again.');
            actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else if(data.returnStr=='Name_Already_Exist'){
            errorStatus.innerHTML = Translate('This vendor and bill number already exists. Try again with different vendor/bill number.');
            actionBtnClick('.btnmodel', Translate('Save'), 0);
		}  
		else{
            errorStatus.innerHTML = Translate('No changes / Error occurred while updating data! Please try again.');
            actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
    }

    return false;
}

async function AJremoveExpense(){
	confirm_dialog(Translate('Remove Expense'), Translate('Are you sure you want to remove this expense permanently?'), confirmRemoveExpense);
}

async function confirmRemoveExpense(){
    let saveBtn = document.querySelector('.archive');
    saveBtn.innerHTML = Translate('Removing')+'...';
    saveBtn.disabled = true;
    const expenses_id = document.getElementById("table_idValue");

    const jsonData = {};
    jsonData['expenses_id'] = expenses_id.value;

    const url = '/'+segment1+'/AJremoveExpense';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.removeCount>0){
            window.location = '/Expenses/lists';
        }
        else{                
            showTopMessage('alert_msg', Translate('Could not remove expense.'));
            saveBtn.innerHTML = Translate('Confirm');
            saveBtn.disabled = false;
        }
    }
}

async function AJ_profit_loss_MoreInfo(){
    if(document.getElementById('date_range').value ===''){
        
        const jsonData = {};	
        const url = '/'+segment1+'/AJ_profit_loss_MoreInfo';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            document.getElementById('showing_type').value = data.showing_type;
            document.getElementById('paymenttype').value = data.paymenttype;
            document.getElementById('date_range').value = DBDateToViewDate(data.startdate)+' - '+DBDateToViewDate(data.enddate);
            AJprofit_lossData();
        }
    }
    else{       
        AJprofit_lossData();
    }
}

async function profit_lossPrint(){
    let divContents = document.getElementById("profit_loss-table").cloneNode(true);
	let title = Translate('P&L Statement');
	let filterby = '';
	
	let showing_type = document.getElementById('showing_type').options[document.getElementById('showing_type').selectedIndex].text;
	filterby += Translate('View')+': '+showing_type;
	
	let paymenttype = document.getElementById("paymenttype").value;
	if(paymenttype !==''){
		if(filterby !==''){filterby +=', ';}
		filterby += Translate('Tax Basis')+': '+paymenttype;
	}
	
	let date_range = document.getElementById("date_range").value;
	if(date_range !==''){
		if(filterby !==''){filterby +=', ';}
		filterby += Translate('Date Range')+': '+date_range;
	}					
	
	let now = new Date();
    let todayDate;
	if(calenderDate.toLowerCase() === 'dd-mm-yyyy'){todayDate = now.getDate()+'-'+(now.getMonth()+1)+'-'+now.getFullYear();}
	else{todayDate = (now.getMonth()+1)+'/'+now.getDate()+'/'+now.getFullYear();}

	const PLHead = cTag('thead');
		const PLheadRow = cTag('tr');
			const PLTableData = cTag('td',{ 'class':`bgnone`,'colspan':`6` });
				const div100Width = cTag('div',{'class':'flexSpaBetRow'});
					let companyNameDiv = cTag('div',{'style': "font-size: 18px; font-weight: bold;" });
					companyNameDiv.innerHTML = stripslashes(companyName);
                div100Width.appendChild(companyNameDiv);
					let titleDiv = cTag('div',{ 'style': "font-size: 20px; font-weight: bold;" });
					titleDiv.innerHTML = title;
                div100Width.appendChild(titleDiv);
					let todayDateDiv = cTag('div',{ 'style': "font-size: 16px;" });
					todayDateDiv.innerHTML = todayDate;
                div100Width.appendChild(todayDateDiv);
            PLTableData.appendChild(div100Width);
				let hr100Width = cTag('div');
				hr100Width.appendChild(cTag('hr',{ 'style': "margin-top: 10px; margin-bottom: 0px;" }));
            PLTableData.appendChild(hr100Width);
				let byDiv100 = cTag('div',{ 'style': "margin-top: 10px;", 'id':`filterby` });
				byDiv100.innerHTML = filterby;
            PLTableData.appendChild(byDiv100);
        PLheadRow.appendChild(PLTableData);
    PLHead.appendChild(PLheadRow);
	
	divContents.querySelector('table').insertBefore(PLHead,divContents.querySelector('tbody'));
	
	let day = new Date();
	let id = day.getTime();
	let w = 900;
	let h = 600;
	let scrl = 1;
	let winl = (screen.width - w) / 2;
	let wint = (screen.height - h) / 2;
	let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	let printWindow = window.open('', '" + id + "', winprops);

		const html = cTag('html');
            const head = cTag('head');
                const titleTag = cTag('title');
                titleTag.innerHTML = title;
            head.appendChild(titleTag);
            head.appendChild(cTag('meta',{ 'charset':`utf-8` }));
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
            const body = cTag('body');
            body.append(divContents);
        html.appendChild(body);

	printWindow.document.write("<!DOCTYPE html>")
	printWindow.document.appendChild(html)
	printWindow.document.close();
	let is_chrome = Boolean(window.chrome);
	let document_focus;
	if (is_chrome) {
		printWindow.onload = function () {
			printWindow.window.print();
			document_focus = true;
		};
	}
	else {
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
		let deviceOpSy = getMobileOperatingSystem();
		if (document_focus === true && deviceOpSy==='unknown') { printWindow.window.close(); }
	}, 500);
}

async function printExpLists(){
	let dr = document.getElementById("date_range").value;
	let vid = document.getElementById("svendors_id").value;
	let et = document.getElementById("sexpense_type").value;
	let st = document.getElementById("sorting_type").value;
	let ks = document.getElementById("keyword_search").value;
	
	printbyurl('/Expenses/prints/?dr='+dr+'&vid='+vid+'&et='+et+'&st='+st+'&ks='+ks);
}

async function AJprofit_lossData(){
    let showing_type = document.getElementById('showing_type').value;
    const jsonData = {};
    jsonData['showing_type'] = showing_type;
    const date_range = document.getElementById('date_range').value;
    jsonData['date_range'] = date_range;
    jsonData['paymenttype'] = document.getElementById('paymenttype').value;
	
    const url = '/'+segment1+'/AJprofit_lossData';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        let colspan2 = parseInt(data.colspan2);
        let colspan3 = parseInt(data.colspan3);
        let colspan4 = parseInt(data.colspan4);
        
        let searchResult = document.getElementById('Searchresult');
        searchResult.innerHTML = '';
            const PLWidget = cTag('div', {class: "cardContainer"});
                const PLHeader = cTag('div', {class: "cardHeader"});
                    const PLRow = cTag('div', {class: "flexSpaBetRow"});
                        const PLName = cTag('div', {class: "columnSM5", 'style': "margin: 0;"});
                            const PLHeaderTitle = cTag('h3');
                            PLHeaderTitle.innerHTML = Translate('P&L Report');
                        PLName.appendChild(PLHeaderTitle);
                    PLRow.appendChild(PLName);
                        const printOnDate = cTag('div', {class: "columnSM7", 'style': "text-align: end; margin: 0;"});
                        let printOnDateStr = 'Printed on '+DBDateToViewDate(data.todayDate, 0, 1);
                        if(data.startdate !=='' && data.enddate !==''){
                            printOnDateStr += ' '+Translate('for Date range of')+' '+DBDateToViewDate(data.startdate, 0, 1)+' to '+DBDateToViewDate(data.enddate, 0, 1)+'&#09;';
                        }
                        printOnDate.innerHTML = printOnDateStr;
                    PLRow.appendChild(printOnDate);
                PLHeader.appendChild(PLRow);
            PLWidget.appendChild(PLHeader);
        
                let aTag, strong, PLHeadRow, tdCol;
                const PLContent = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
                    const PLContentFlex = cTag('div', {class: "flex"});
                        const PLContentColumn = cTag('div', {class: "columnXS12", 'style': "margin: 0; padding: 0;"});
                            const noMoreTables = cTag('div', {id: "profit_loss-table"});
                                const PLtable = cTag('table', {class: "columnMD12 table-bordered table-striped table-condensed cf listing", 'style': "margin-top: 2px;"});
                                    const PLBody = cTag('tbody');
                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', { 'style': `padding-left: 20px; ${data.boldclass!==''?'font-weight:bold':''}`});
                                            if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                            tdCol.innerHTML = Translate('Income');
                                        PLHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', { 'style': `text-align: right; width: 200px; padding-right: 20px; ${data.boldclass!==''?'font-weight:bold':''}`});
                                            tdCol.innerHTML = addCurrency(data.incomeTotal);
                                        PLHeadRow.appendChild(tdCol);
                                    PLBody.appendChild(PLHeadRow);
                                        if(showing_type === 'Detailed' && data.incomedetails.length>0){
                                            data.incomedetails.forEach(oneRow => {
                                                PLHeadRow = cTag('tr');
                                                    tdCol = cTag('td', { 'style': "width: 80px; padding-left: 20px;"});
                                                    tdCol.innerHTML = '&nbsp;';
                                                PLHeadRow.appendChild(tdCol);
                                                    tdCol = cTag('td', {'style': "width: 150px; padding-left: 15px;"});
                                                    tdCol.innerHTML = DBDateToViewDate(oneRow[0], 0, 1);
                                                PLHeadRow.appendChild(tdCol);
                                                    tdCol = cTag('td', { 'style': "padding-left: 20px;"});                                                    
                                                        aTag = cTag('a',{ 'href':`/Invoices/view/${oneRow[1]}`, 'style': "color: #009; text-decoration: underline;", 'title':Translate('View Invoice') });
                                                        aTag.append(oneRow[1]+' ');
                                                        aTag.appendChild(cTag('i',{ 'class':`fa fa-link` }));
                                                    tdCol.appendChild(aTag);
                                                    if(colspan2>0){
                                                        tdCol.setAttribute('colspan', colspan2)
                                                    }
                                                PLHeadRow.appendChild(tdCol);
                                                    tdCol = cTag('td', { 'style': "text-align: right; width: 150px; padding-right: 20px;"});
                                                    tdCol.innerHTML = addCurrency(oneRow[2]);
                                                PLHeadRow.appendChild(tdCol);
                                                    tdCol = cTag('td');
                                                    tdCol.innerHTML = '&nbsp;';
                                                PLHeadRow.appendChild(tdCol);
                                                PLBody.appendChild(PLHeadRow);
                                            });
                                        }
                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', { 'style': `padding-left: 20px; ${data.boldclass!==''?'font-weight:bold':''}`});
                                            if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                            tdCol.innerHTML = Translate('Cost of Goods');
                                        PLHeadRow.appendChild(tdCol);

                                            tdCol = cTag('td', { 'style': `text-align: right; width: 200px; padding-right: 20px;${data.boldclass!==''?'font-weight:bold':''}`});
                                            tdCol.innerHTML = addCurrency(data.costTotal);
                                        PLHeadRow.appendChild(tdCol);
                                    PLBody.appendChild(PLHeadRow);
                                    
                                    if(data.costDetailed.length>0){
                                        data.costDetailed.forEach(oneRow => {
                                            PLHeadRow = cTag('tr');
                                                tdCol = cTag('td', { 'style': "width: 80px; padding-left: 20px;"});
                                                tdCol.innerHTML = '&nbsp;';
                                            PLHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', {'style': "padding-left: 20px;"});
                                                tdCol.innerHTML = DBDateToViewDate(oneRow[0], 0, 1);
                                            PLHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', { 'style': "width: 150px; padding-left: 15px;"});
                                                if(oneRow[1] !=='' && oneRow[1]>0){
                                                    aTag = cTag('a', {href: '/Purchase_orders/edit/'+oneRow[1], 'style': "color: #009; text-decoration: underline;", title: Translate('View Invoice')});
                                                    aTag.innerHTML = oneRow[1]+' ';
                                                    aTag.appendChild(cTag('i', {class: "fa fa-link"}));
                                                    tdCol.appendChild(aTag);
                                                }
                                                else{
                                                    tdCol.innerHTML = oneRow[1];
                                                }
                                            PLHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', { 'style': "padding-left: 20px; text-align: left;"});
                                                tdCol.innerHTML = oneRow[2];
                                            PLHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', { 'style': "text-align: right; padding-right: 20px;"});
                                                tdCol.innerHTML = addCurrency(oneRow[3]);
                                            PLHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = '&nbsp;';
                                            PLHeadRow.appendChild(tdCol);
                                            PLBody.appendChild(PLHeadRow);
                                        });
                                    }

                                    if(data.costTaxTotal > 0){
                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', { 'style': `text-align: right; padding-right: 20px;${data.boldclass!==''?'font-weight:bold':''}`});
                                            if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                                strong = cTag('strong');
                                                strong.innerHTML = Translate('Tax');
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);

                                            tdCol = cTag('td', { 'style': `text-align: right; width: 200px; padding-right: 20px;${data.boldclass!==''?'font-weight:bold':''}`});
                                                strong = cTag('strong');
                                                strong.innerHTML = addCurrency(data.costTaxTotal);
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);
                                        PLBody.appendChild(PLHeadRow);
                                    }
                                    
                                    if(data.costShippingTotal > 0){
                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', { 'style': `text-align: right; padding-right: 20px;${data.boldclass!==''?'font-weight:bold':''}`});
                                            if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                                strong = cTag('strong');
                                                strong.innerHTML = Translate('Shipping');
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);

                                            tdCol = cTag('td', { 'style': `text-align: right; width: 200px; padding-right: 20px;${data.boldclass!==''?'font-weight:bold':''}`});
                                                strong = cTag('strong');
                                                strong.innerHTML = addCurrency(data.costShippingTotal);
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);
                                        PLBody.appendChild(PLHeadRow);
                                    }
                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', { 'style': "text-align: right; padding-right: 20px;", class: data.bgashclass});
                                            if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                                strong = cTag('strong');
                                                strong.innerHTML = Translate('Gross Income');
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);

                                            tdCol = cTag('td', { 'style': "text-align: right; width: 200px; padding-right: 20px;", class: data.bgashclass});
                                                strong = cTag('strong');
                                                strong.innerHTML = addCurrency(data.grossIncome);
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);
                                    PLBody.appendChild(PLHeadRow);
                                    
                                    if(data.expenseTableData.length>0){
                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'style': "padding-left: 20px; text-align: center;", class: data.bgashclass});
                                            if(colspan4>0){tdCol.setAttribute('colspan', colspan4);}
                                                strong = cTag('strong');
                                                strong.innerHTML = Translate('Expenses');
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);
                                        PLBody.appendChild(PLHeadRow);
                                        
                                        data.expenseTableData.forEach(oneRow => {
                                            let firstColumnVal = oneRow[0];
                                            if(firstColumnVal ==='||'){
                                                let expenseTableInfo = oneRow[1];
                                                expenseTableInfo.forEach(oneRow1 => {
                                                    PLHeadRow = cTag('tr');
                                                        tdCol = cTag('td', { 'style': "width: 80px; padding-left: 20px;"});
                                                        tdCol.innerHTML = '&nbsp;';
                                                    PLHeadRow.appendChild(tdCol);
                                                        tdCol = cTag('td', {'style': "width: 150px; padding-left: 15px;"});
                                                        tdCol.innerHTML = DBDateToViewDate(oneRow1[2], 0, 1);
                                                    PLHeadRow.appendChild(tdCol);
                                                        tdCol = cTag('td', { 'style': "width: 150px; padding-left: 15px;"});
                                                        if(oneRow1[0] !=='' && oneRow1[0]>0){
                                                            aTag = cTag('a', {href: '/Expenses/view/'+oneRow1[0], 'style': "color: #009; text-decoration: underline;", title: Translate('View Expense Details')});
                                                            aTag.innerHTML = oneRow1[1]+' ';
                                                            aTag.appendChild(cTag('i', {class: "fa fa-link"}));
                                                            tdCol.appendChild(aTag);
                                                        }
                                                        else{
                                                            tdCol.innerHTML = oneRow1[1];
                                                        }
                                                    PLHeadRow.appendChild(tdCol);
                                                        tdCol = cTag('td', { 'style': "padding-left: 20px; text-align: left;"});
                                                        tdCol.innerHTML = oneRow1[3];
                                                    PLHeadRow.appendChild(tdCol);
                                                        tdCol = cTag('td', { 'style': "text-align: right; padding-right: 20px;"});
                                                        tdCol.innerHTML = addCurrency(oneRow1[4]);
                                                    PLHeadRow.appendChild(tdCol);
                                                        tdCol = cTag('td');
                                                        tdCol.innerHTML = '&nbsp;';
                                                    PLHeadRow.appendChild(tdCol);
                                                    PLBody.appendChild(PLHeadRow);
                                                });
                                            }
                                            else{
                                                PLHeadRow = cTag('tr');
                                                    tdCol = cTag('td', {'style': `padding-left: 20px; text-align: left;${data.boldclass!==''?'font-weight:bold':''}`});
                                                    if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                                    tdCol.innerHTML = oneRow[0];
                                                PLHeadRow.appendChild(tdCol);
                                                    tdCol = cTag('td', { 'style': `text-align: right; padding-right: 20px;${data.boldclass!==''?'font-weight:bold':''}`});
                                                    tdCol.innerHTML = addCurrency(oneRow[1]);
                                                PLHeadRow.appendChild(tdCol);
                                                PLBody.appendChild(PLHeadRow);
                                            }
                                        });

                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'style': "text-align: right; padding-left: 20px;", class: data.bgashclass});
                                            if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                                strong = cTag('strong');
                                                strong.innerHTML = Translate('Total Expenses');
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'style': "text-align: right; width: 200px; padding-right: 20px;", class: data.bgashclass});
                                                strong = cTag('strong');
                                                strong.innerHTML = addCurrency(data.expenseTotal);
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);
                                        PLBody.appendChild(PLHeadRow);
                                    }

                                        PLHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'style': "text-align: right; padding-right: 20px;", class: data.bgashclass});
                                            if(colspan3>0){tdCol.setAttribute('colspan', colspan3);}
                                                strong = cTag('strong');
                                                strong.innerHTML = Translate('Net Income');
                                            tdCol.appendChild(strong);
                                        PLHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'style': "text-align: right; width: 200px; padding-right: 20px;", class: data.bgashclass});
                                                strong = cTag('strong');
                                                strong.innerHTML = addCurrency(data.netIncome);
                                            tdCol.appendChild(strong);  
                                        PLHeadRow.appendChild(tdCol);
                                    PLBody.appendChild(PLHeadRow);
                                PLtable.appendChild(PLBody);
                            noMoreTables.appendChild(PLtable);
                        PLContentColumn.appendChild(noMoreTables);
                    PLContentFlex.appendChild(PLContentColumn);
                PLContent.appendChild(PLContentFlex);
            PLWidget.appendChild(PLContent);
        searchResult.appendChild(PLWidget);
    }
}

function profit_loss(){
    let sortDropDown, inputField;
    const showTableData = document.getElementById("viewPageInfo");
        const titleRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('P&L Statement');
        titleRow.appendChild(headerTitle);
            const buttonsNames = cTag('div', {class: "flex"});
                const aTag = cTag('a', {'href': "/Expenses/lists", class: "btn defaultButton", title: Translate('Expenses List')});
                aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Expenses List'));

                const printButton = cTag('a', {class: "btn printButton", 'style': "margin-right: 10px;", 'href': "javascript:void(0);", title: Translate('Print Statement')});
                printButton.addEventListener('click', profit_lossPrint);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsNames.append(printButton, aTag);
        titleRow.appendChild(buttonsNames);
    showTableData.appendChild(titleRow);
    
        const filterRow = cTag('div', {class: "flexEndRow", 'style': "margin-top: 6px;"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                const showingTypeName = cTag('div', {class: "input-group"});
                    const showingTypeLabel = cTag('label', {'for': "showing_type", class: "input-group-addon cursor"});
                    showingTypeLabel.innerHTML = Translate('View');
                showingTypeName.appendChild(showingTypeLabel);
                    const selectShowingType = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectShowingType.addEventListener('change', AJprofit_lossData);
                    setOptions(selectShowingType, {'Summary':Translate('Summary'), 'Detailed':Translate('Detailed Summary')}, 1, 0);
                showingTypeName.appendChild(selectShowingType);
            sortDropDown.appendChild(showingTypeName);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                const taxBasisName = cTag('div', {class: "input-group"});
                    const taxBasisLabel = cTag('label', {'for': "paymenttype", class: "input-group-addon cursor"});
                    taxBasisLabel.innerHTML = Translate('Tax Basis');
                taxBasisName.appendChild(taxBasisLabel);
                    const selectPaymentType = cTag('select', {name: "paymenttype", id: "paymenttype", class: "form-control"});
                    selectPaymentType.addEventListener('change', AJprofit_lossData);
                    setOptions(selectPaymentType, {'Cash':Translate('Cash'), 'Accrual':Translate('Accrual')}, 1, 0);
                taxBasisName.appendChild(selectPaymentType);
            sortDropDown.appendChild(taxBasisName);
        filterRow.appendChild(sortDropDown);

            const dateRageSearch = cTag('div', {class: "columnXS12 columnSM4 columnMD3"});
                const dateRageSearchField = cTag('div', {class: "input-group daterangeContainer"});
                    inputField = cTag('input', {'type': "hidden", name: "pageURI", id: "pageURI", 'value': "Expenses/profit_loss"});
                dateRageSearchField.appendChild(inputField);
                    inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control date_range", 'style': "padding-left: 35px;", name: "date_range", id: "date_range", 'value': ""});
                    daterange_picker_dialog(inputField);
                dateRageSearchField.appendChild(inputField);
                    let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: Translate('Date wise Search')});
                    searchSpan.addEventListener('click', AJprofit_lossData);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                dateRageSearchField.appendChild(searchSpan);
            dateRageSearch.appendChild(dateRageSearchField);
        filterRow.appendChild(dateRageSearch);
    showTableData.appendChild(filterRow);

        const searchResultColumn = cTag('div', {class: "columnXS12", 'style': "margin-top: 6px;"});
            let searchResult = cTag('div', {id: "Searchresult"});
        searchResultColumn.appendChild(searchResult);
    showTableData.appendChild(searchResultColumn);

    //======sessionStorage =======//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    let showing_type = 'Summary', paymenttype = 'Cash', date_range = '';

    checkAndSetSessionData('showing_type', showing_type, list_filters);
    checkAndSetSessionData('paymenttype', paymenttype, list_filters);

    if(list_filters.hasOwnProperty("date_range")){
        date_range = list_filters.date_range;
    }
    document.getElementById("date_range").value = date_range;

    AJ_profit_loss_MoreInfo();
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, view, profit_loss};
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});