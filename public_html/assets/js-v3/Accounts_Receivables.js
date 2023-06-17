import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData,addCurrency, DBDateToViewDate,preventDot, printbyurl, alert_dialog, 
    setSelectOpt, setTableRows, setTableHRows,  showTopMessage, setOptions, activeLoader, addCustomeEventListener, addPaginationRowFlex, 
    checkAndSetSessionData, popup_dialog600, checkNumericInputOnKeydown,listenToEnterKey, fetchData, actionBtnClick, serialize, 
    onClickPagination, AJautoComplete, historyTable, activityFieldAttributes, controllNumericField, validateRequiredField
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

const listsFieldAttributes = [
    {'datatitle':Translate('Name'), 'align':'justify'},
    {'datatitle':Translate('Phone'), 'align':'right'},
    {'datatitle':Translate('Credit Limit'), 'align':'right'},
    {'datatitle':Translate('Credit Days'), 'align':'right'},
    {'datatitle':Translate('Total Due'), 'align':'right'}
];
const uriStr = segment1+'/view';

const unpaidInvAttributes = [
    { 'datatitle':Translate('Due Date'), 'align':'left'},
    {'datatitle':Translate('Invoice No.'), 'align':'left'},
    {'datatitle':Translate('Grand Total'), 'align':'right'},
    {'datatitle':Translate('Amount Paid'), 'align':'left'},
    {'datatitle':Translate('Amount Due'), 'align':'left'}
];

async function filter_Accounts_Receivables_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	const scustomer_type = document.getElementById("scustomer_type").value;
	jsonData['scustomer_type'] = scustomer_type;
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
        setSelectOpt('scustomer_type', 'All', Translate('All Types'), data.custTypeOpt, 0, data.custTypeOpt.length);			
        setTableRows(data.tableRows, listsFieldAttributes, uriStr, [3, 5]);

        document.getElementById("totalTableRows").value = data.totalRows;
        document.getElementById("scustomer_type").value = scustomer_type;
        
        onClickPagination();
    }
}

async function loadTableRows_Accounts_Receivables_lists(){
	const jsonData = {};
	jsonData['scustomer_type'] = document.getElementById("scustomer_type").value;
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';

    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        setTableRows(data.tableRows, listsFieldAttributes, uriStr, [3, 5]);
        onClickPagination();
    }
}

function printAR(){
	printbyurl('/Accounts_Receivables/prints/arlists/'+document.querySelector('#scustomer_type').value+'/'+document.querySelector('#sorting_type').value+'/'+document.querySelector('#keyword_search').value+'/'+document.querySelector('#page').value);
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

    let input,list_filters, sortDropDown;

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
    
        const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Accounts Receivables')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays the list of your customers')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
        
            const customerCreditBtn = cTag('a', {'href': "javascript:void(0);", title: Translate('Allow a customer credit'), style:"margin-left: auto;", class: "btn cursor createButton"});
            customerCreditBtn.addEventListener('click', function (){AJgetPopup_Accounts_Receivables(0);});
            customerCreditBtn.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Allow a customer credit'));
        titleRow.appendChild(customerCreditBtn);

            let printButton = cTag('a',{ "href":"javascript:void(0);","click":printAR,"class":"btn printButton", 'style': "margin-left: 15px;", "title":Translate('Print Reports') });
            printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
            if(OS =='unknown'){
                printButton.append(' '+Translate('Print')+' ');
            }                
        titleRow.appendChild(printButton);

    showTableData.appendChild(titleRow);

        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                const selectCustomer = cTag('select', {class: "form-control", name: "scustomer_type", id: "scustomer_type"});
                selectCustomer.addEventListener('change', filter_Accounts_Receivables_lists);
                    const customerOption = cTag('option', {'value': "All"});
                    customerOption.innerHTML = Translate('All Types');
                selectCustomer.appendChild(customerOption);
            sortDropDown.appendChild(selectCustomer);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                const selectSorting = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type"});
                selectSorting.addEventListener('change', filter_Accounts_Receivables_lists);
                setOptions(selectSorting, {'0':Translate('Company, First and Last Name'), '1':Translate('Company Name'), '2':Translate('First Name'), '3':Translate('Last Name')}, 1, 0);
            sortDropDown.appendChild(selectSorting);
        filterRow.appendChild(sortDropDown);

            const searchDiv = cTag('div', {class: "columnXS12 columnSM4 columnMD3"});
                const SearchInGroup = cTag('div', {class: "input-group"});
                    const searchField = cTag('input', {'keydown':listenToEnterKey(filter_Accounts_Receivables_lists),'type': "text", 'placeholder': Translate('Search Accounts Receivables'), 'value':"", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
                SearchInGroup.appendChild(searchField);
                    const searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Accounts Receivables')});
                    searchSpan.addEventListener('click', filter_Accounts_Receivables_lists);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                SearchInGroup.appendChild(searchSpan);
            searchDiv.appendChild(SearchInGroup);
        filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

        const divTableRow = cTag('div', {class: "flex"});
            const divTableColumn = cTag('div', {class: "columnXS12"});
                const divNoMore = cTag('div', {id: "no-more-tables"});
                    const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                        const listHead = cTag('thead', {class: "cf"});
                            const columnNames = listsFieldAttributes.map(colObj=>(colObj.datatitle));
                            const listHeadRow = cTag('tr',{class:'outerListsTable'});
                                const thCol0 = cTag('th');
                                thCol0.innerHTML = columnNames[0];

                                const thCol1 = cTag('th', {'width': '15%'});
                                thCol1.innerHTML = columnNames[1];

                                const thCol2 = cTag('th', {'width': '15%'});
                                thCol2.innerHTML = columnNames[2];

                                const thCol3 = cTag('th', {'width': '10%'});
                                thCol3.innerHTML = columnNames[3];

                                const thCol4 = cTag('th', {'width': '10%'});
                                thCol4.innerHTML = columnNames[4];
                            listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
                        listHead.appendChild(listHeadRow);
                    listTable.appendChild(listHead);
                        const listBody = cTag('tbody', {id: 'tableRows'});
                    listTable.appendChild(listBody);
                divNoMore.appendChild(listTable);
            divTableColumn.appendChild(divNoMore);
        divTableRow.appendChild(divTableColumn);
    showTableData.appendChild(divTableRow);
    addPaginationRowFlex(showTableData);

    //======sessionStorage =======//
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    const sorting_type = '0', scustomer_type = 'All';
   
    checkAndSetSessionData('sorting_type', sorting_type, list_filters);
    checkAndSetSessionData('scustomer_type', scustomer_type, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    addCustomeEventListener('filter',filter_Accounts_Receivables_lists);
    addCustomeEventListener('loadTable',loadTableRows_Accounts_Receivables_lists);
    filter_Accounts_Receivables_lists(true);
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
}

async function filter_Accounts_Receivables_view(){
    let page = 1;
    document.getElementById("page").value = page;
    
    const jsonData = {};
    jsonData['customers_id'] = document.getElementById("table_idValue").value;
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

async function loadTableRows_Accounts_Receivables_view(){
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
        let tdCol, moreInfoHeadRow;
        const viewBasicInfo = document.getElementById("viewBasicInfo");
        viewBasicInfo.innerHTML = '';
            const nameHeader = cTag('h3');
            nameHeader.innerHTML = data.name;
        viewBasicInfo.appendChild(nameHeader);
            
            const contactNoDiv = cTag('div', { 'style': "margin-bottom: 10px;"});
                const phoneIcon = cTag('i', {class: "fa fa-phone", 'style': "font-size: 16px;"});
            contactNoDiv.appendChild(phoneIcon);
                const contactNo = cTag('span', {'style': "padding-left: 15px; font-weight: bold; color: #969595;"});
                contactNo.innerHTML = data.contact_no+'&nbsp;';
            contactNoDiv.appendChild(contactNo);
        viewBasicInfo.appendChild(contactNoDiv);
        
            const creditCardDiv = cTag('div', {'style': "margin-bottom: 10px;"});
                const creditCardIcon = cTag('i', {class: "fa fa-credit-card", 'style': "font-size: 16px;"});
            creditCardDiv.appendChild(creditCardIcon);
                const creditCardValue = cTag('span', {'style': "padding-left: 15px; font-weight: bold; color: #969595;"});
                creditCardValue.innerHTML = addCurrency(data.credit_limit)+' ';
            creditCardDiv.appendChild(creditCardValue);
        viewBasicInfo.appendChild(creditCardDiv);

        document.getElementById("credit_limit").value = data.credit_limit;
        document.getElementById("available_credit").value = data.available_credit;

            const creditDaysDiv = cTag('div', {'style': "margin-bottom: 10px;"});
                const sunIcon = cTag('i', {class: "fa fa-sun-o", 'style': "font-size: 16px;"});
            creditDaysDiv.appendChild(sunIcon);
                const creditDaysValue = cTag('span', {'style': "padding-left: 15px; font-weight: bold; color: #969595;"});
                creditDaysValue.innerHTML = data.credit_days+' ';
            creditDaysDiv.appendChild(creditDaysValue);
        viewBasicInfo.appendChild(creditDaysDiv);

            const divButtons = cTag('div', {class: "flexColumn"});
                const divButtonsColumn = cTag('div', {class: "columnSM12", 'style': "padding: 0px;"});
                    const editButton = cTag('button', {class: "btn editButton arEdit", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Edit')});
                    editButton.addEventListener('click', function (){AJgetPopup_Accounts_Receivables(customers_id);});
                    editButton.innerHTML = Translate('Edit');
                divButtonsColumn.appendChild(editButton);

                    const removeButton = cTag('button', {class: "btn archiveButton arRemove", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Remove')});
                    removeButton.addEventListener('click', function(){AJremoveAccountsReceivables(customers_id);});
                    removeButton.innerHTML = Translate('Remove');                
                divButtonsColumn.appendChild(removeButton);

                    const printButton = cTag('button', {class: "btn printButton arPrint", 'style': "margin-right: 15px; margin-bottom: 10px;",});
                    printButton.addEventListener('click', function (){printbyurl('/Accounts_Receivables/prints/'+customers_id);});
                    printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                    if(OS =='unknown'){
                        printButton.append(' '+Translate('Print')+' ');
                    }
                divButtonsColumn.appendChild(printButton);

                    const emailButton = cTag('button', {class: "btn defaultButton arEmail", 'style': "margin-bottom: 10px;"});
                    emailButton.addEventListener('click', emailARStatement);
                    emailButton.append(cTag('i', {class: "fa fa-envelope"}),' '+Translate('Email Statement'));
                divButtonsColumn.appendChild(emailButton);
            divButtons.appendChild(divButtonsColumn);
        viewBasicInfo.appendChild(divButtons);

                const emailStatementColumn = cTag('div', {class: "columnSM6", 'style': "padding: 0px;"});
                    const emailStatementDiv = cTag('div', {id: "emailARStatement", 'style': "display: none;"});
                        const emailStatementForm = cTag('form', {'method': "post", name: "sendEmail", id: "sendEmail", 'enctype': "multipart/form-data"});
                        emailStatementForm.addEventListener('submit',sendEmailARStatement);
                            const emailTable = cTag('table', {'width': "100%", 'border': 0, 'cellspacing': 0, 'cellpadding': 0});
                                const emailBody = cTag('tbody');
                                    const emailHeadRow = cTag('tr');
                                        tdCol = cTag('td');
                                            const addressSign = cTag('div', {class: "input-group"});
                                                const addressSignTitle = cTag('div', {class: "input-group-addon"});
                                                addressSignTitle.innerHTML = '@';
                                            addressSign.appendChild(addressSignTitle);
                                        tdCol.appendChild(addressSign);
                                                const emailInput = cTag('input', {'type': "email", 'required': "", class: "form-control", id: "emailAddress", 'placeholder': Translate('Email Address'), 'value':data.email});
                                            addressSign.appendChild(emailInput);
                                        tdCol.appendChild(addressSign);
                                    emailHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'width': 50, 'style': "padding-left: 10px;"});
                                            const closeButton = cTag('button', {'type': "button", class: "btn defaultButton"});
                                            closeButton.addEventListener('click', closeemailAR);
                                            closeButton.innerHTML = Translate('Cancel');
                                        tdCol.appendChild(closeButton);
                                    emailHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'width': 50, 'style': "padding-left: 10px;"});
                                            const sendButton = cTag('button', {'type': "submit", class: "btn completeButton sendmail"});
                                            sendButton.innerHTML = Translate('Send');
                                        tdCol.appendChild(sendButton);
                                    emailHeadRow.appendChild(tdCol);
                                emailBody.appendChild(emailHeadRow);
                            emailTable.appendChild(emailBody);
                        emailStatementForm.appendChild(emailTable);
                    emailStatementDiv.appendChild(emailStatementForm);
                emailStatementColumn.appendChild(emailStatementDiv);
            divButtons.appendChild(emailStatementColumn);
        viewBasicInfo.appendChild(divButtons);

        const unpaidInvoice = document.getElementById("unpaidInvoice");
        unpaidInvoice.innerHTML = '';
        
        if(data.unpaidInvoices.length>0){
            data.unpaidInvoices.forEach (supOpts=>{
                const columnNames = unpaidInvAttributes.map(colObj=>(colObj.datatitle));
                moreInfoHeadRow = cTag('tr');
                    tdCol = cTag('td', {'data-title': columnNames[0]});
                    tdCol.innerHTML = DBDateToViewDate(supOpts[1]);
                moreInfoHeadRow.appendChild(tdCol);

                    tdCol = cTag('td', {'data-title': columnNames[1], align:'center'});
                        const aTag = cTag('a', {'href': "/Invoices/view/"+supOpts[0], title: Translate('View Details')});
                        aTag.innerHTML='s'+supOpts[0]+' ';
                            const linkIcon = cTag('i', {class: "fa fa-link"});
                        aTag.append(linkIcon);
                    tdCol.appendChild(aTag);
                moreInfoHeadRow.appendChild(tdCol);

                    tdCol = cTag('td', {'data-title': columnNames[2], align:'right'});
                    tdCol.innerHTML = addCurrency(supOpts[2]);
                moreInfoHeadRow.appendChild(tdCol);

                    tdCol = cTag('td', {'data-title': columnNames[3], align:'right'});
                    tdCol.innerHTML = addCurrency(supOpts[3]);
                moreInfoHeadRow.appendChild(tdCol);

                    tdCol = cTag('td', {'data-title': columnNames[4], align:'right'});
                    tdCol.innerHTML = addCurrency(supOpts[4]);
                moreInfoHeadRow.appendChild(tdCol);
                unpaidInvoice.appendChild(moreInfoHeadRow);
            });
        }
        else{
            moreInfoHeadRow = cTag('tr');
                tdCol = cTag('td', {colspan:5});
                tdCol.innerHTML = '';
            moreInfoHeadRow.appendChild(tdCol);
            unpaidInvoice.appendChild(moreInfoHeadRow); 
        }

        const shistory_type = document.getElementById("shistory_type");
        const shistory_typeVal = shistory_type.value;
        shistory_type.innerHTML = '';
            const option = cTag('option', {'value': ""});
            option.innerHTML = Translate('All Activities');
        shistory_type.appendChild(option);

        setOptions(shistory_type, data.actFeeTitOpt, 0, 1) ;
        shistory_type.value = shistory_typeVal;
        filter_Accounts_Receivables_view();
        document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    }
}

function view(){    
    let customers_id = parseInt(segment3);
    if(customers_id==='' || isNaN(customers_id)){customers_id = 0;}
        
    let noMoreTables, columnNames, list_filters;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow"});
            const titleName = cTag('div', {class: "columnSM8", 'style': "text-align: start;"});
                const headerTitle = cTag('h2');
                headerTitle.innerHTML = Translate('Accounts Receivables Details')+' ';
                    const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title':Translate('This page displays the information of customer')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
    
            const buttonName = cTag('div', {class: "columnSM4", 'style': "text-align: end;"});
                const listButton = cTag('button', {class:"btn defaultButton", title: Translate('Accounts Receivables List')});
                listButton.addEventListener('click', function (){window.location='/Accounts_Receivables/lists';});
                listButton.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Accounts Receivables List'));
            buttonName.appendChild(listButton);
        titleRow.appendChild(buttonName);
    showTableData.appendChild(titleRow);
    
        const detailViewRow = cTag('div', {class: "columnSM12"});
            const headerRow = cTag('header', {class: "imageContainer flexSpaBetRow", 'style': "margin-bottom: 10px; padding-left: 5px;"});
                const imageColumn = cTag('div', {class: "columnSM3"});
                    const imageSegment = cTag('div', {class: "image"});
                        const imageSource = cTag('img', {class: "img-responsive", 'alt': Translate('My Profile'), 'src': "/assets/images/man.jpg"});
                    imageSegment.appendChild(imageSource);
                imageColumn.appendChild(imageSegment);
            headerRow.appendChild(imageColumn);

                const detailInfoColumn = cTag('div', {class: "columnSM9"});
                    let imageContent = cTag('div', {class: "image_content", 'style': "text-align: left;", id: "viewBasicInfo"});
                detailInfoColumn.appendChild(imageContent);
            headerRow.appendChild(detailInfoColumn);
        detailViewRow.appendChild(headerRow);
    showTableData.appendChild(detailViewRow);
    
        //=========Unpaid Invoice List=========//
        const unpaidInvoiceDiv = cTag('div', {class: "columnXS12"});
            const unpaidInvoiceWidget = cTag('div', {class: "flexColumn cardContainer", 'style': "margin-bottom: 10px;"});
                const unpaidInvoiceHeader = cTag('div', {class: "cardHeader"});
                    const unpaidInvoiceTitle = cTag('h3', {'style': "padding-left: 5px;"});
                    unpaidInvoiceTitle.innerHTML = Translate('Unpaid Invoices List');
                unpaidInvoiceHeader.appendChild(unpaidInvoiceTitle);
            unpaidInvoiceWidget.appendChild(unpaidInvoiceHeader);
                const unpaidInvoiceContent = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
                    const unpaidInvoiceColumn = cTag('div', {class: "columnXS12", 'style': "margin: 0; padding: 0;"});
                        noMoreTables = cTag('div', {id: "no-more-tables"});
                            const unpaidInvoiceTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                const unpaidInvoiceHead = cTag('thead', {class: "cf"});
                                    columnNames = unpaidInvAttributes.map(colObj=>(colObj.datatitle));
                                    const unpaidInvoiceHeadRow = cTag('tr');
                                        const thCol0 = cTag('th', {'style': "width: 90px;"});
                                        thCol0.innerHTML = columnNames[0];

                                        const thCol1 = cTag('th', {'width': "22%"});
                                        thCol1.innerHTML = columnNames[1];

                                        const thCol2 = cTag('th', {'width': "22%"});
                                        thCol2.innerHTML = columnNames[2];

                                        const thCol3 = cTag('th', {'width': "22%"});
                                        thCol3.innerHTML = columnNames[3];

                                        const thCol4 = cTag('th');
                                        thCol4.innerHTML = columnNames[4];
                                    unpaidInvoiceHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
                                unpaidInvoiceHead.appendChild(unpaidInvoiceHeadRow);
                            unpaidInvoiceTable.appendChild(unpaidInvoiceHead);
                                const unpaidInvoiceBody = cTag('tbody', {id: "unpaidInvoice"});
                            unpaidInvoiceTable.appendChild(unpaidInvoiceBody);
                        noMoreTables.appendChild(unpaidInvoiceTable);
                    unpaidInvoiceColumn.appendChild(noMoreTables);
                unpaidInvoiceContent.appendChild(unpaidInvoiceColumn);
            unpaidInvoiceWidget.appendChild(unpaidInvoiceContent);
        unpaidInvoiceDiv.appendChild(unpaidInvoiceWidget);
    showTableData.appendChild(unpaidInvoiceDiv);
                
        //===========Note List============//
        const activityContent = cTag('div', {class: "flexSpaBetRow"});
            const activityContentColumn = cTag('div', {class: "columnXS12"});
                let hiddenProperties = {
                    'note_forTable': 'customers' ,
                    'table_idValue': customers_id ,
                    'credit_limit': 0 ,
                    'available_credit': 0 ,
                }
            activityContentColumn.appendChild(historyTable(Translate('Note'),hiddenProperties));
        activityContent.appendChild(activityContentColumn);
    showTableData.appendChild(activityContent);
        
    const loadData = 'AJ_'+segment2+'_moreInfo';
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

    addCustomeEventListener('filter',filter_Accounts_Receivables_view);
    addCustomeEventListener('loadTable',loadTableRows_Accounts_Receivables_view);
    AJ_view_moreInfo();
}

async function AJgetPopup_Accounts_Receivables(customers_id){
    let customerCreditForm, phoneNoLabel, creditLimitRow, creditLimit, creditLimitLabel,requireField, creditLimitField, creditDaysRow, creditDays, creditDaysLabel, creditDaysField, inputField;
    if(customers_id>0){
        const jsonData = {};
        jsonData['customers_id'] = customers_id;
        const url = '/'+segment1+'/AJgetPopup';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            let formDialog = cTag('div');
                customerCreditForm = cTag('form', {'action': "#", name: "frmAccRec", id: "frmAccRec", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                    let customerName = cTag('div', {class: "flexSpaBetRow"});
                        let customerTitle = cTag('div', {class: "columnXS5", 'align': "left"});
                            let customerLabel = cTag('label');
                            customerLabel.innerHTML = Translate('Name');
                        customerTitle.appendChild(customerLabel);
                    customerName.appendChild(customerTitle);
                        let customerValue = cTag('div', {class: "columnXS7", 'align': "left"});
                            let customerLabelValue = cTag('label');
                            customerLabelValue.innerHTML = data.first_name+ ' '+data.last_name;
                        customerValue.appendChild(customerLabelValue);
                    customerName.appendChild(customerValue);
                customerCreditForm.appendChild(customerName);
                        
                    let phoneNoRow = cTag('div', {class: "flexSpaBetRow"});
                        let phoneNoColumn = cTag('div', {class: "columnXS5", 'align': "left"});
                            phoneNoLabel = cTag('label');
                            phoneNoLabel.innerHTML = Translate('Phone No.');
                        phoneNoColumn.appendChild(phoneNoLabel);
                    phoneNoRow.appendChild(phoneNoColumn);
                        let phoneNoValue = cTag('div', {class: "columnXS7", 'align': "left"});
                            phoneNoLabel = cTag('label');
                            phoneNoLabel.innerHTML = data.contact_no;
                        phoneNoValue.appendChild(phoneNoLabel);
                    phoneNoRow.appendChild(phoneNoValue);
                customerCreditForm.appendChild(phoneNoRow);

                    creditLimitRow = cTag('div', {class: "flexSpaBetRow"});
                        creditLimit = cTag('div', {class: "columnXS5", 'align': "left"});
                            creditLimitLabel = cTag('label', {'for': "credit_limit"});
                            creditLimitLabel.innerHTML = Translate('Credit Limit');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            creditLimitLabel.appendChild(requireField);
                        creditLimit.appendChild(creditLimitLabel);
                    creditLimitRow.appendChild(creditLimit);
                        creditLimitField = cTag('div', {class: "columnXS7", 'align': "left"});
                            inputField = cTag('input', {'type': "text",'data-min':'1','data-max':'9999999.99','data-format':'d.dd', class: "form-control", name: "credit_limit", id: "credit_limit", 'value': data.credit_limit});
                            controllNumericField(inputField, '#error_limit');
                        creditLimitField.appendChild(inputField);
                        creditLimitField.appendChild(cTag('span', {id: "error_limit", class: "errormsg"}));
                    creditLimitRow.appendChild(creditLimitField);
                customerCreditForm.appendChild(creditLimitRow);

                    creditDaysRow = cTag('div', {class: "flexSpaBetRow"});
                        creditDays = cTag('div', {class: "columnXS5", 'align': "left"});
                            creditDaysLabel = cTag('label', {'for': "credit_days"});
                            creditDaysLabel.innerHTML = Translate('Credit Days');
                                requireField = cTag('span', {class: "required"});
                                requireField.innerHTML = '*';
                            creditDaysLabel.appendChild(requireField);
                        creditDays.appendChild(creditDaysLabel);
                    creditDaysRow.appendChild(creditDays);
                        creditDaysField = cTag('div', {class: "columnXS7", 'align': "left"});
                            inputField = cTag('input', {'type': "text",'data-min':'1','data-max':'999', 'data-format':'d', class: "form-control", name: "credit_days", id: "credit_days", 'value':data.credit_days});
                            controllNumericField(inputField, '#error_days');
                        creditDaysField.appendChild(inputField);
                        creditDaysField.appendChild(cTag('span', {id: "error_days", class: "errormsg"}));
                    creditDaysRow.appendChild(creditDaysField);
                customerCreditForm.appendChild(creditDaysRow);

                    inputField = cTag('input', {'type': "hidden", name: "customers_id", 'value':customers_id});
                customerCreditForm.appendChild(inputField);
            formDialog.appendChild(customerCreditForm);

            popup_dialog600(Translate('Change Accounts Receivables Information'), formDialog, Translate('Save'),AJsaveAccountsReceivables);
            
            setTimeout(function() {
                document.getElementById("credit_limit").value = data.credit_limit;
                document.getElementById("credit_days").value = data.credit_days;
                document.getElementById("credit_limit").focus();
            }, 500);			
        }
    }
    else{
        let formDialog2 = cTag('div');
            customerCreditForm = cTag('form', {'action': "#", name: "frmAccRec", id: "frmAccRec", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                let customerName2 = cTag('div', {class: "flexSpaBetRow"});
                    let customerNameTitle = cTag('div', {class: "columnXS5", 'align': "left"});
                        let customerNameLabel = cTag('label', {'for': "customer_name"});
                        customerNameLabel.innerHTML = Translate('Name');
                            requireField = cTag('span', {class: "required"});
                            requireField.innerHTML = '*';
                        customerNameLabel.appendChild(requireField);
                    customerNameTitle.appendChild(customerNameLabel);
                customerName2.appendChild(customerNameTitle);
                    let customerNameValue = cTag('div', {class: "columnXS7"});
                        const inputGroup = cTag('div', {class: "input-group", id: "customerNameField"});
                            inputField = cTag('input', {'maxlength': 50, 'type': "text", 'value':"", 'required': true, name: "customer_name", id: "customer_name", class: "form-control", 'placeholder': Translate('Search Customers')});
                        inputGroup.appendChild(inputField);
                        inputGroup.appendChild(cTag('span', {id: "error_customer", class: "errormsg"}));
                    customerNameValue.appendChild(inputGroup);
                customerName2.appendChild(customerNameValue);
            customerCreditForm.appendChild(customerName2);
    
                let contactNoRow = cTag('div', {class: "flexSpaBetRow"});
                    let phoneNo = cTag('div', {class: "columnXS5", 'align': "left"});
                        phoneNoLabel = cTag('label');
                        phoneNoLabel.innerHTML = Translate('Phone No.');
                    phoneNo.appendChild(phoneNoLabel);
                contactNoRow.appendChild(phoneNo);
                    let contactNoValue = cTag('div', {class: "columnXS7", 'align': "left"});
                        phoneNoLabel = cTag('label', {id: "lbphone"});
                    contactNoValue.appendChild(phoneNoLabel);
                contactNoRow.appendChild(contactNoValue);
            customerCreditForm.appendChild(contactNoRow);
    
                creditLimitRow = cTag('div', {class: "flexSpaBetRow"});
                    creditLimit = cTag('div', {class: "columnXS5", 'align': "left"});
                        creditLimitLabel = cTag('label', {'for': "credit_limit"});
                        creditLimitLabel.innerHTML = Translate('Credit Limit');
                            requireField = cTag('span', {class: "required"});
                            requireField.innerHTML = '*';
                        creditLimitLabel.appendChild(requireField);
                    creditLimit.appendChild(creditLimitLabel);
                creditLimitRow.appendChild(creditLimit);
                    creditLimitField = cTag('div', {class: "columnXS7", 'align': "left"});
                        inputField = cTag('input', {'type': "text",'data-min':'1','data-max':'9999999.99','data-format':'d.dd', class: "form-control", name: "credit_limit", id: "credit_limit", 'value':"0"});
                        controllNumericField(inputField, '#error_limit');
                    creditLimitField.appendChild(inputField);
                    creditLimitField.appendChild(cTag('span', {id: "error_limit", class: "errormsg"}));
                creditLimitRow.appendChild(creditLimitField);
            customerCreditForm.appendChild(creditLimitRow);
    
                creditDaysRow = cTag('div', {class: "flexSpaBetRow"});
                    creditDays = cTag('div', {class: "columnXS5", 'align': "left"});
                        creditDaysLabel = cTag('label', {'for': "credit_days"});
                        creditDaysLabel.innerHTML = Translate('Credit Days');
                            requireField = cTag('span', {class: "required"});
                            requireField.innerHTML = '*';
                        creditDaysLabel.appendChild(requireField);
                    creditDays.appendChild(creditDaysLabel);
                creditDaysRow.appendChild(creditDays);
                    creditDaysField = cTag('div', {class: "columnXS7", 'align': "left"});
                        inputField = cTag('input', {'type': "text",'data-min':'1','data-max':'999', 'data-format':'d', class: "form-control", name:"credit_days", id:"credit_days", 'value':"0"});
                        controllNumericField(inputField, '#error_days');
                    creditDaysField.appendChild(inputField);
                    creditDaysField.appendChild(cTag('span', {id: "error_days", class: "errormsg"}));
                creditDaysRow.appendChild(creditDaysField);
            customerCreditForm.appendChild(creditDaysRow);
                inputField = cTag('input', {'type': "hidden", name: "customers_id", id: "customers_id", 'value': 0});
            customerCreditForm.appendChild(inputField);
        formDialog2.appendChild(customerCreditForm);
    
        popup_dialog600(Translate('Allow a customer credit Information'), formDialog2, Translate('Save'),AJsaveAccountsReceivables);        
        
        setTimeout(function() { 
            if(document.getElementById("customer_name")){AJautoComplete('customer_name');}
            document.getElementById("customer_name").focus();
        }, 500);
        return true;
    }
}

async function AJsaveAccountsReceivables(hidePopup){
    const jsonData = serialize('#frmAccRec');
    const error = document.getElementById("error_customer");
    const error_limit = document.getElementById("error_limit");
    const error_days = document.getElementById("error_days");
    if(error) error.innerHTML = '';
    error_limit.innerHTML = '';
    error_days.innerHTML = '';

    let customers_id = 0;
    if (jsonData.hasOwnProperty("customers_id")){
        customers_id = jsonData.customers_id;
    }
    
    let customerName = document.querySelector('#frmAccRec #customer_name')
    if(parseInt(customers_id)===0){
        const pTag = cTag('p', {'style': "margin: 0; text-align: left;"});
        pTag.innerHTML = "Select Customer";
        error.appendChild(pTag);
        customerName.focus();
        customerName.classList.add('errorFieldBorder');
        return false;
    }else if(customerName){
		customerName.classList.remove('errorFieldBorder');
	}
    
    let credit_limit = document.querySelector("#popup #credit_limit");
    if(!validateRequiredField(credit_limit,'#error_limit') || !credit_limit.valid()) return;
    
    let credit_days = document.getElementById("credit_days");
    if(!validateRequiredField(credit_days,'#error_days') || !credit_days.valid()) return;
    
    if(credit_limit.value>0 && credit_days.value>0 && customers_id>0){
        actionBtnClick('.btnmodel', Translate('Saving'), 1);
        
        const url = '/'+segment1+'/AJsaveAccountsReceivables';
        fetchData(afterFetch,url,jsonData);
        function afterFetch(data){
            if(data.savemsg === 'session_ended'){
                window.location = '/session_ended';
            }
            else if(data.savemsg === 'saved'){
                window.location = '/Accounts_Receivables/view/'+customers_id;
                hidePopup();
            }
            else{
                document.getElementById("error_customer").innerHTML = data.savemsg;
            }
            actionBtnClick('.btnmodel', Translate('Save'), 0);
        }
	    return false;
    }
}

async function AJremoveAccountsReceivables(customers_id){
    const jsonData = {};
    jsonData['customers_id'] = customers_id;
    jsonData['note'] = Translate('This Accounts Receivables Removed successfully.');

    let credit_limit = parseFloat(document.getElementById("credit_limit").value);
	if(isNaN(credit_limit) || credit_limit===''){credit_limit = 0;}
	let available_credit = parseFloat(document.getElementById("available_credit").value);
	if(isNaN(available_credit) || available_credit===''){available_credit = 0;}
	
	if(credit_limit>available_credit){
		alert_dialog(Translate('Remove Accounts Receivables'), Translate('Sorry, you can not remove a customer with a balance due'), Translate('Ok'));
		return false;
	}
	else{
        const removeBtn = document.querySelector('.arRemove');
        removeBtn.innerHTML = Translate('Removing')+'...';
        removeBtn.disabled = true;

        const url = '/'+segment1+'/AJremoveAccountsReceivables';
        
        fetchData(afterFetch,url,jsonData);
        
        function afterFetch(data){
            if(data.returnmsg ==='success'){
                showTopMessage('success_msg', Translate('This Accounts Receivables Removed successfully.'));
                document.querySelectorAll('.arEdit').forEach((oneRow)=>{
                    oneRow.style.display = 'none';
                });
                document.querySelectorAll('.arRemove').forEach((oneRow)=>{
                    oneRow.style.display = 'none';
                });
                window.location = '/Accounts_Receivables/lists';
            }
            else{
                showTopMessage('alert_msg', data.returnmsg);    
                removeBtn.innerHTML = Translate('Remove');
                removeBtn.disabled = false;
            }
        }                
		return false;
	}
}

function emailARStatement(){
    if(document.querySelector("#emailARStatement").style.display === 'none'){
        document.querySelector("#emailARStatement").style.display = 'block';
    }
	document.getElementById("emailAddress").focus();
}

async function sendEmailARStatement(e){
    if(e){e.preventDefault();}
	const email_address = document.getElementById("emailAddress").value;
	const customers_id = document.getElementById("table_idValue").value;
	
    const sendBtn = document.querySelector('.sendmail');
    sendBtn.innerHTML = Translate('Sending')+'...';
    sendBtn.disabled = true;
    
    activeLoader();
    const jsonData =  {"customers_id":customers_id, 
        "email_address":email_address, 
        'Accounts_Receivables_Statement':Translate('Accounts Receivables Statement for'),
        'Invoice_Date':Translate('Invoice Date'),
        'Invoice_Number':Translate('Invoice Number'),
        'Date_Due':Translate('Date Due'),
        'Grand_Total':Translate('Grand Total'),
        'Total_Paid':Translate('Total Paid'),
        'Current':Translate('Current'),
        '_30_Past_Due':Translate('0-30 Past Due'),
        '_3160_Past_Due':Translate('31-60 Past Due'),
        '_6190_Past_Due':Translate('61-90 Past Due'),
        '_91_Past_Due':Translate('91+ Past due'),
        'No_Dues_Meet':Translate('No dues meet the criteria given'),
        'Grand_Total':Translate('Grand Total'),
        'Not_Send_Mail':Translate('Sorry! Could not send mail. Try again later.'),
        'No_Invoice_Meet':Translate('No invoice meet the criteria given.')
    };

    const url = '/'+segment1+'/sendEmailARStatement';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        sendBtn.innerHTML = Translate('Send');
        sendBtn.disabled = false;
        if(data.returnStr !=='Ok'){
            showTopMessage('alert_msg', data.returnStr);
        }
        else{
            showTopMessage('success_msg', Translate('Email sent successfully'));
            closeemailAR();
        }
    }    
    return false;
}

function closeemailAR(){
    document.querySelector("#emailARStatement").style.display = 'none';
}

document.addEventListener('DOMContentLoaded', async()=>{   
    const layoutFunctions = {lists,view};
    if(segment2==='') segment2 = 'lists';
    layoutFunctions[segment2]();
});