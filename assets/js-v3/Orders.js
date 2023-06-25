import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, DBDateToViewDate, noPermissionWarning, redirectTo, 
    preventDot, printbyurl, confirm_dialog, setSelectOpt, setTableRows, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, 
    checkAndSetSessionData, popup_dialog, popup_dialog600, setCDinCookie, dynamicImport, applySanitizer, multiSelectAction,emailcheck,
    togglePaymentButton, fetchData, listenToEnterKey, addCustomeEventListener, actionBtnClick, serialize,controllNumericField , 
    onClickPagination, historyTable, activityFieldAttributes, AJautoComplete
} from './common.js';

import {
    AJget_oneTimePopup, showCategoryPPProduct, addPOSPayment, showProductPicker, reloadProdPkrCategory, cartsAutoFuncCall, 
    calculateChangeCartTotal, showCartCompleteBtn, loadCartData, emaildetails, cancelemailform, emailthispage, showOrNotSquareup, 
    onChangeTaxesId, preNextCategory, updateCartData, checkMethod, loadPaymentData, AJautoComplete_cartProduct, addCartsProduct,calculateCartTotal
} from './cart.js';

import {smsInvoice} from './BulkSMS.js';

if(segment2 === ''){segment2 = 'lists'}

let listsFieldAttributes = [
    {'datatitle':Translate('Date'), 'nowrap':'nowrap', 'align':'left'},
    {'datatitle':Translate('Invoice No.'), 'nowrap':'nowrap', 'align':'right'},
    {'datatitle':Translate('Customer Name'), 'align':'left'},
    {'datatitle':Translate('Sales Person'), 'align':'left'},
    {'datatitle':Translate('Status'), 'align':'left'},
    {'datatitle':Translate('Taxable'), 'style': "text-align: right;"},
    {'datatitle':Translate('Taxes'), 'style': "text-align: right;"},
    {'datatitle':Translate('Non Taxable'), 'style': "text-align: right;"},
    {'datatitle':Translate('Total'), 'style': "text-align: right;"},
    {'datatitle':Translate('Amount Paid'), 'style': "text-align: right;"},
    {'datatitle':Translate('Current Due'), 'style': "text-align: right;"}
];

const uriStr = segment1+'/edit';

async function filter_Orders_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	const semployee_id = document.getElementById("semployee_id").value;
	jsonData['semployee_id'] = semployee_id;
    const sview_type = document.getElementById("sview_type").value;
	jsonData['sview_type'] = sview_type;
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
            'Quotes': Translate('Quotes'),
            'New': Translate('New'),
            ...data.vieTypOpt,
            'All': Translate('All Statuses'),
        }

        setSelectOpt('sview_type', 1, Translate('Open'), vieTypOpt, 1, Object.keys(vieTypOpt).length);
        setSelectOpt('semployee_id', 0, Translate('All Sales People'), data.empIdOpt, 1, Object.keys(data.empIdOpt).length);
        setTableRows(data.tableRows, listsFieldAttributes, uriStr, [6,7,8,9,10,11], [1]);
        document.getElementById("totalTableRows").value = data.totalRows;
        
        document.getElementById("sview_type").value = sview_type;
        document.getElementById("semployee_id").value = semployee_id;
        
        onClickPagination();
    }
}

async function loadTableRows_Orders_lists(){
	const jsonData = {};
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['semployee_id'] = document.getElementById("semployee_id").value;
	jsonData['sview_type'] = document.getElementById("sview_type").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;	
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        setTableRows(data.tableRows, listsFieldAttributes, uriStr, [6,7,8,9,10,11], [1]);
        onClickPagination();
    }
}

function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById('viewPageInfo');
    showTableData.innerHTML = '';

    //======Hidden Fields for Pagination=======//
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '50' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

        let sortDropDown;
        const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Customer Orders');
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays a list of all orders created using the Orders module')});
            headerTitle.append(' ', infoIcon);
        titleRow.appendChild(headerTitle);

            const createButton = cTag('a', {class: "btn createButton", 'href': "/Orders/add", title: Translate('Create Order')});
            createButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Order'));
        titleRow.appendChild(createButton);
    showTableData.appendChild(titleRow);

        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectSorting = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type"});
                selectSorting.addEventListener('change', filter_Orders_lists);
                const options = {
                    '0':Translate('Date')+', '+Translate('Invoice No.'), 
                    '1':Translate('Date'), 
                    '2':Translate('Invoice No.')
                };
                for(const [key, value] of Object.entries(options)) {
                    let sortingOption = cTag('option', {'value': key});
                    sortingOption.innerHTML = value;
                    selectSorting.appendChild(sortingOption);
                }
            sortDropDown.appendChild(selectSorting);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectSview = cTag('select', {class: "form-control", name: "sview_type", id: "sview_type"});
                selectSview.addEventListener('change', filter_Orders_lists);
                    let sViewOption = cTag('option', {'value': 1});
                    sViewOption.innerHTML = Translate('Open');                    
                selectSview.appendChild(sViewOption);
            sortDropDown.appendChild(selectSview);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectSemployee = cTag('select', {class: "form-control", name: "semployee_id", id: "semployee_id"});
                selectSemployee.addEventListener('change', filter_Orders_lists);
                    let sEmployeeOption = cTag('option', {'value': 0});
                    sEmployeeOption.innerHTML = Translate('All Sales Person');
                selectSemployee.appendChild(sEmployeeOption);
            sortDropDown.appendChild(selectSemployee);
        filterRow.appendChild(sortDropDown);

            const searchDiv = cTag('div', {class: "columnXS6 columnSM3"});
                const SearchInGroup = cTag('div', {class: "input-group"});
                    const search = cTag('input', {keydown: listenToEnterKey(filter_Orders_lists), 'type': "text", 'placeholder': Translate('Search Customer or Invoice'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
                SearchInGroup.appendChild(search);
                    const searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Customer or Invoice')});
                    searchSpan.addEventListener('click', filter_Orders_lists);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                SearchInGroup.appendChild(searchSpan);
            searchDiv.appendChild(SearchInGroup);
        filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);

        const divTableColumn = cTag('div', {class: "columnXS12"});
            const divNoMore= cTag('div', {id: "no-more-tables"});
                const listTable = cTag('table', {class: "table-bordered  table-striped table-condensed cf listing"});
                    const listHead = cTag('thead', {class: "cf"});
                    const columnNames = listsFieldAttributes.map(colObj=>(colObj.datatitle));
                        const listHeadRow = cTag('tr',{class:'outerListsTable'});
                            const thCol0 = cTag('th', {'style': "width: 80px;"});
                            thCol0.innerHTML = columnNames[0];
                            
                            const thCol1 = cTag('th');
                            thCol1.innerHTML = columnNames[1];

                            const thCol2 = cTag('th');
                            thCol2.innerHTML= columnNames[2];

                            const thCol3 = cTag('th', {'width': "15%"});
                            thCol3.innerHTML = columnNames[3];

                            const thCol4 = cTag('th');
                            thCol4.innerHTML = columnNames[4];

                            const thCol5 = cTag('th');
                            thCol5.innerHTML = columnNames[5];

                            const thCol6 = cTag('th');
                            thCol6.innerHTML = columnNames[6];

                            const thCol7 = cTag('th');
                            thCol7.innerHTML = columnNames[7];

                            const thCol8 = cTag('th');
                            thCol8.innerHTML = columnNames[8];

                            const thCol9 = cTag('th');
                            thCol9.innerHTML = columnNames[9];

                            const thCol10 = cTag('th');
                            thCol10.innerHTML = columnNames[10];
                        listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8, thCol9, thCol10);
                    listHead.appendChild(listHeadRow);
                listTable.appendChild(listHead);

                    const listBody = cTag('tbody', {id: "tableRows"});
                listTable.appendChild(listBody);
            divNoMore.appendChild(listTable);
        divTableColumn.appendChild(divNoMore);
    showTableData.appendChild(divTableColumn);
    addPaginationRowFlex(showTableData);

     //=======sessionStorage =========//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    
    const sorting_type = '0', sview_type = 1, semployee_id = 0;

    checkAndSetSessionData('sorting_type', sorting_type, list_filters);
    checkAndSetSessionData('sview_type', sview_type, list_filters);
    checkAndSetSessionData('semployee_id', semployee_id, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    addCustomeEventListener('filter',filter_Orders_lists);
    addCustomeEventListener('loadTable',loadTableRows_Orders_lists);
    filter_Orders_lists(true)
}

//=======add=========
function add(){
    let requiredField;
    const dashBoard = document.getElementById('viewPageInfo');
    dashBoard.innerHTML = '';
        const titleRow = cTag('div');
            const titleHeader = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
            titleHeader.append(Translate('Add Order')+' ');
            titleHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('This page captures the basic details required to create orders taken from telephone or online sales.') }));
        titleRow.appendChild(titleHeader);
    dashBoard.appendChild(titleRow);
        const newOrderColumn = cTag('div',{ 'class':`columnSM12`});
            const callOutDiv = cTag('div',{ 'class':`innerContainer`});

                const newOrderForm = cTag('form',{ 'id':`frmAddOrders`,'action':`#`,'name':`frmAddOrders`,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
                newOrderForm.addEventListener('submit',AJsave_Orders);
                    const quoteRow = cTag('div',{ 'class':`flexStartRow` });
                        const quoteColumn = cTag('div',{ 'class':`columnXS12 columnSM4 columnLG2` });
                            const quoteLabel = cTag('label',{ 'for':`startQuotes`,'data-placement':`bottom` });
                            quoteLabel.innerHTML = `${Translate('Start As Quotes')}?`;
                        quoteColumn.appendChild(quoteLabel);
                    quoteRow.appendChild(quoteColumn);
                        const quoteDropDown = cTag('div',{ 'class':`columnXS12 columnSM6 columnLG4` });
                            const selectQuote = cTag('select',{ 'required':``,'class':`form-control`,'name':`startQuotes`,'id':`startQuotes` });
                            setOptions(selectQuote,[Translate('No'),Translate('Yes')],0,0);
                        quoteDropDown.appendChild(selectQuote);
                    quoteRow.appendChild(quoteDropDown);
                newOrderForm.appendChild(quoteRow);

                    const customerRow = cTag('div',{ 'class':`flexStartRow` });
                        const customerColumn = cTag('div',{ 'class':`columnXS12 columnSM4 columnLG2` });
                            const customerLabel = cTag('label',{ 'for':`customer_name`,'data-placement':`bottom` });
                            customerLabel.append(Translate('Customer Name'));
                                requiredField = cTag('span',{ 'class':`required` });
                                requiredField.innerHTML = '*';
                            customerLabel.appendChild(requiredField);
                        customerColumn.appendChild(customerLabel);
                    customerRow.appendChild(customerColumn);
                        const customerField = cTag('div',{ 'class':`columnXS12 columnSM6 columnLG4` });
                            const customerInGroup = cTag('div',{ 'class':`input-group`,'id':`customerNameField` });
                            customerInGroup.appendChild(cTag('input',{ 'maxlength':`50`,'type':`text`,'value':``,'required':``,'name':`customer_name`,'id':`customer_name`,'class':`form-control ui-autocomplete-input`,'placeholder':Translate('Search Customers'),'autocomplete':`off` }));
                                let addNewSpan = cTag('span',{ id:'add_new_customer','data-toggle':`tooltip`,'data-original-title':Translate('Add New Customer'),'class':`input-group-addon cursor` });
                                addNewSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
                            customerInGroup.appendChild(addNewSpan);
                        customerField.appendChild(customerInGroup);
                        customerField.appendChild(cTag('input',{ 'type':`hidden`,'name':`customer_id`,'id':`customer_id`,'value':`0` }));
                        customerField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_customer_id` }));
                    customerRow.appendChild(customerField);
                        const errorColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
                        errorColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_customer_name` }));
                    customerRow.appendChild(errorColumn);
                newOrderForm.appendChild(customerRow);

                    const salesPersonRow = cTag('div',{ 'class':`flexStartRow` });
                        const salesPersonColumn = cTag('div',{ 'class':`columnXS12 columnSM4 columnLG2` });
                            const salesPersonLabel = cTag('label',{ 'for':`salesman_id`,'data-placement':`bottom` });
                            salesPersonLabel.append(Translate('Sales Person'));
                                requiredField = cTag('span',{ 'class':`required` });
                                requiredField.innerHTML = '*';
                            salesPersonLabel.appendChild(requiredField);
                        salesPersonColumn.appendChild(salesPersonLabel);
                    salesPersonRow.appendChild(salesPersonColumn);
                        const salesPersonDropDown = cTag('div',{ 'class':`columnXS12 columnSM6 columnLG4` });
                        salesPersonDropDown.appendChild(cTag('select',{ 'required':``,'name':`salesman_id`,'id':`salesman_id`,'class':`form-control` }));
                    salesPersonRow.appendChild(salesPersonDropDown);
                        const errorMessage = cTag('div',{ 'class':`columnXS12 columnSM4` });
                        errorMessage.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_salesman_id` }));
                    salesPersonRow.appendChild(errorMessage);
                newOrderForm.appendChild(salesPersonRow);
                
                    const buttonNames = cTag('div',{ 'class':`flex` });
                        let buttonTitle = cTag('div',{ 'class':`columnXS12 columnSM10 columnLG6`, 'style': "text-align: right;" });
                        buttonTitle.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos_id`,'id':`pos_id`,'value':`0` }));
                        buttonTitle.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`,'id':`cancelbutton`,'click': ()=> redirectTo('/Orders/lists'),'value':Translate('Cancel') }));
                        buttonTitle.appendChild(cTag('input',{ 'class':`btn completeButton`, 'style': "margin-left: 10px;", 'name':`submit`,'id':`submit`,'type':`submit`,'value':Translate('Add') }));
                    buttonNames.appendChild(buttonTitle);
                newOrderForm.appendChild(buttonNames);
            callOutDiv.appendChild(newOrderForm);
        newOrderColumn.appendChild(callOutDiv);
    dashBoard.appendChild(newOrderColumn);
    setTimeout(function() {document.getElementById("customer_name").focus();}, 500);
    AJautoComplete('customer_name')
    AJ_add_MoreInfo();
}

async function AJ_add_MoreInfo(){
    const url = '/'+segment1+'/AJ_add_MoreInfo';
    fetchData(afterFetch,url,{});

    function afterFetch(data){
        document.querySelector('#add_new_customer').addEventListener('click',()=>dynamicImport('./Customers.js','AJget_CustomersPopup',[0]));
        const salesman_id = document.querySelector('#salesman_id');
        setOptions(salesman_id,data.salManOpt,1,1);
        salesman_id.value = data.salesman_id;
    }
}

//=======edit=========
function edit(){
    const dashBoard = document.getElementById('viewPageInfo');
    dashBoard.innerHTML = '';
    dashBoard.appendChild(cTag('input',{ 'type':`hidden`,'id':`subPermission` }));

        let span, select, strong, headerTitle, editButton, headRow, tdCol, label, thCol, productBody;
        const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
            const titleColumn = cTag('div',{ 'class':`columnSM7 columnMD6 flex`, 'style': "margin: 0; justify-content: space-between;" });
                const titleHeader = cTag('h2',{ 'style': "padding-top: 5px;" });
                titleHeader.append(`${Translate('Edit Order')} o${segment3}`);
            titleColumn.appendChild(titleHeader);
                let selectStatus = cTag('select',{ 'class':`btn cursor`, 'style': "width: 200px; height: 35px; margin-top: 5px;", 'name':`order_status`,'id':`order_status`,'change': AJsave_orderStatus });             
            titleColumn.appendChild(selectStatus);
            titleColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`oldorder_status`,'id':`oldorder_status` }));
            titleColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`employee_id`,'id':`employee_id` }));
        titleRow.appendChild(titleColumn);
            const buttonNames = cTag('div',{ 'class':`columnSM5 columnMD6`, 'style': "text-align: end;"});
                let buttonTitle = cTag('a',{ 'class':`btn defaultButton cursor` });
                buttonTitle.addEventListener('click',function(){javascript:window.location= '/Orders/lists'});
                buttonTitle.appendChild(cTag('i',{ 'class':`fa fa-list` }));
                buttonTitle.append(' '+Translate('List Orders'));
            buttonNames.appendChild(buttonTitle);

                const printButtonGroup = cTag('div',{ 'class':`printBtnDropDown`,'id':'orders_prints' });
                    const printButtonTitle = cTag('button',{ 'type':`button`, 'class':`btn printButton dropdown-toggle`, 'style': "margin-left: 10px; padding-bottom: 10px;", 'data-toggle':`dropdown`,'aria-haspopup':`true`,'aria-expanded':`false` });
                    printButtonTitle.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                    if(OS =='unknown'){
                        printButtonTitle.append(' '+Translate('Print')+' ');
                    }
                    printButtonTitle.append('\u2000', cTag('span',{ 'class':`caret`}));
                        const dropDownSpan = cTag('span',{ 'class':`sr-only` });
                        dropDownSpan.innerHTML = Translate('Toggle Dropdown');
                    printButtonTitle.appendChild(dropDownSpan);
                printButtonGroup.appendChild(printButtonTitle);
                    let ulDropDown = cTag('ul',{ 'class':`dropdown-menu`});
                        let liFullPrint = cTag('li');
                            let fullPrint = cTag('a',{ 'href':`javascript:void(0);`,'id':'full_page_print','title':Translate('Full Page Printer') });
                            fullPrint.innerHTML = Translate('Full Page Printer');
                        liFullPrint.appendChild(fullPrint);
                    ulDropDown.appendChild(liFullPrint);
                    ulDropDown.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
                        let liThermalPrint = cTag('li');
                            let thermalPrint = cTag('a',{ 'href':`javascript:void(0);`,'id':'small_page_print','title':Translate('Thermal Printer') });
                            thermalPrint.innerHTML = Translate('Thermal Printer');
                        liThermalPrint.appendChild(thermalPrint);
                    ulDropDown.appendChild(liThermalPrint);
                    ulDropDown.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
                        let liEmail = cTag('li');
                            let emailOrderLink = cTag('a',{ 'href':`javascript:void(0)`,'click':emailthispage,'title':Translate('Email Order') });
                            emailOrderLink.innerHTML = Translate('Email Order');
                        liEmail.appendChild(emailOrderLink);
                    ulDropDown.appendChild(liEmail);
                        let liSMS = cTag('li');
                            let smsPrint = cTag('a',{ 'href':`javascript:void(0)`,'click': smsInvoice,'title':Translate('SMS Invoice') });
                            smsPrint.innerHTML = Translate('SMS Invoice');
                        liSMS.appendChild(smsPrint);
                    ulDropDown.appendChild(liSMS);
                    ulDropDown.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
                        let liPickList = cTag('li');
                            let pickListLink = cTag('a',{ 'href':`javascript:void(0);`,'title':Translate('Print Pick List'),'id':'pick_list_print' });
                            pickListLink.innerHTML = Translate('Pick List');
                        liPickList.appendChild(pickListLink);
                    ulDropDown.appendChild(liPickList);
                printButtonGroup.appendChild(ulDropDown);
            buttonNames.appendChild(printButtonGroup);

                let emailMainDiv = cTag('div',{ 'class':"flexEndRow"});
                    const emailDiv = cTag('div',{ 'style': "margin-top: 5px;" });
                        const sendEmailForm = cTag('form',{ 'method':`post`,'name':`frmSendOrdersEmail`,'id':`frmSendOrdersEmail`,'enctype':`multipart/form-data`,'action':`#`,'submit': (event)=> emaildetails(event, '/Orders/AJsend_OrdersEmail') });
                            const emailTable = cTag('table',{ 'align':`center`,'width':`100%`,'border':`0`,'cellspacing':`0`,'cellpadding':`10` });
                                const emailBody = cTag('tbody');
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`2` });
                                        tdCol.appendChild(cTag('div',{ 'id':`showerrormessage` }));
                                        tdCol.appendChild(cTag('div',{ 'id':`showsuccessmessage` }));
                                    headRow.appendChild(tdCol);
                                emailBody.appendChild(headRow);
                                    headRow = cTag('tr',{ 'class':`emailform`,style:'display:none'});
                                        tdCol = cTag('td',{ 'style': "padding-bottom: 15px;" });
                                        tdCol.appendChild(cTag('input',{ 'type':`email`,'required':``,'name':`email_address`,'id':`email_address`,'class':`form-control`,'value':``,'maxlength':`50` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'style': "padding-bottom: 15px;", 'align':`right`,'valign':`middle`,'nowrap':`` });
                                        tdCol.appendChild(cTag('input',{ 'type':`submit`,'class':`btn completeButton sendbtn`, 'style': "margin-left: 10px;", 'value':` ${Translate('Email')} ` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`, 'style': "margin-left: 10px;", 'click': cancelemailform,'value':` ${Translate('Cancel')} ` }));
                                    headRow.appendChild(tdCol);
                                emailBody.appendChild(headRow);
                            emailTable.appendChild(emailBody);
                        sendEmailForm.appendChild(emailTable);
                    emailDiv.appendChild(sendEmailForm);
                emailMainDiv.appendChild(emailDiv);
            buttonNames.appendChild(emailMainDiv);
        titleRow.appendChild(buttonNames);
    dashBoard.appendChild(titleRow);

        const infoContainer = cTag('div',{ 'class':`flexSpaBetRow` });
            const customerInfo = cTag('div',{ 'class':`columnMD6 columnXS12` });
                const infoWidget = cTag('div',{ 'class':`cardContainer`});
                    const infoHeader = cTag('div',{ 'class':`cardHeader flexSpaBetRow`, 'style': "padding-right: 2px;" });
                        const infoContainer12 = cTag('div',{ 'class':`flex` });
                            let userInfo = cTag('i',{ 'class':`fa fa-user`, 'style': "margin: 12px; margin-left: 0;" });
                        infoContainer12.appendChild(userInfo);
                                headerTitle = cTag('h3');
                                headerTitle.innerHTML = Translate('Customer info');;
                        infoContainer12.appendChild(headerTitle);
                    infoHeader.appendChild(infoContainer12);
                        editButton = cTag('button',{ 'id':'edit_customer_info', 'class':`btn defaultButton invoiceorcompleted`, 'style': "margin: 4px 0;" });
                        editButton.innerHTML = Translate('Edit');
                    infoHeader.appendChild(editButton);
                infoWidget.appendChild(infoHeader);
                    const infoWidgetContent = cTag('div',{ 'class':`cardContent columnXS12 customInfoGrid`, 'style': "padding-left: 5px;", 'id':`customer_information` });
                        const customerLabel = cTag('label');
                        customerLabel.innerHTML = `${Translate('Customer')}: `;
                        const customerFlex = cTag('div', {'style': 'border-bottom: 1px solid #CCC; padding-bottom: 5px; margin-bottom: 5px;'});
                            const customerLink = cTag('a', {'id':'customer_link', 'style': "color: #009; text-decoration: underline;",'title':Translate('View Customer Details')});
                            let changeBtn = cTag('button',{ 'style':'padding:2px 10px; margin-left: 10px;', 'class':'btn defaultButton', 'title':Translate('Change Customer') });
                            changeBtn.innerText = 'Change';
                            changeBtn.addEventListener('click',()=>dynamicImport('./Customers.js','changeCustomerPopup',[calculateCartTotal]));
                        customerFlex.append(customerLink, changeBtn);
                    infoWidgetContent.append(customerLabel, customerFlex);

                        const emailLabel = cTag('label');
                        emailLabel.innerHTML = `${Translate('Email')}: `;
                        const emailValue = cTag('span',{ 'id':'customeremail' });
                    infoWidgetContent.append(emailLabel, emailValue);

                        const phoneLabel = cTag('label');
                        phoneLabel.innerHTML = `${Translate('Phone No.')}: `;
                        const phoneValue = cTag('span',{ 'id':'customerphone' });
                    infoWidgetContent.append(phoneLabel, phoneValue);
                infoWidget.appendChild(infoWidgetContent);
            customerInfo.appendChild(infoWidget);
        infoContainer.appendChild(customerInfo);

            const orderInfo = cTag('div',{ 'class':`columnMD6 columnXS12` });
                const orderInfoWidget = cTag('div',{ 'class':`cardContainer`});
                    const orderInfoHeader = cTag('div',{ 'class':`cardHeader flexSpaBetRow` });
                        const orderInfo12 = cTag('div',{ 'class':`flex` });
                            const mobileIcon = cTag('i',{ 'class':`fa fa-mobile`, 'style': "margin: 12px; margin-left: 0;" });
                        orderInfo12.appendChild(mobileIcon);
                            let orderInfoTitle = cTag('h3');
                            orderInfoTitle.innerHTML = Translate('Order Info');
                        orderInfo12.appendChild(orderInfoTitle);
                    orderInfoHeader.appendChild(orderInfo12);
                        const headerButton = cTag('div',{ 'class':`invoiceorcompleted`, 'style': "padding-right: 2px;" });
                            editButton = cTag('button',{ 'id':`changeOrderInfo`,'href':`javascript:void(0);`,'class':`btn defaultButton`, 'style': "margin: 4px 0;"});
                            editButton.innerHTML = Translate('Edit');
                        headerButton.appendChild(editButton);
                    orderInfoHeader.appendChild(headerButton);
                orderInfoWidget.appendChild(orderInfoHeader);

                    const orderInfoContent = cTag('div',{ 'class':`cardContent columnXS12 customInfoGrid`,'id':`order_info` });
                        const invoiceLabel = cTag('label');
                        invoiceLabel.innerHTML = `${Translate('Invoice No.')}: `;
                        const invoiceValue = cTag('span',{ 'id':'invoice_no_in_orderInfo' });
                    orderInfoContent.append(invoiceLabel, invoiceValue);

                        const salesmanLabel = cTag('label');
                        salesmanLabel.innerHTML = `${Translate('Sales Person')}: `;
                        const salesmanValue = cTag('span',{ 'id':'salesman_namestr' });
                    orderInfoContent.append(salesmanLabel, salesmanValue);

                        let dateLabel = cTag('label');
                        dateLabel.innerHTML = `${Translate('Date')}: `;
                        const dateValue = cTag('span',{ 'id':'sales_datetime' });
                    orderInfoContent.append(dateLabel, dateValue);
                orderInfoWidget.appendChild(orderInfoContent);
            orderInfo.appendChild(orderInfoWidget);
        infoContainer.appendChild(orderInfo);
    dashBoard.appendChild(infoContainer);

        const productDetailRow = cTag('div',{ 'class':`flexSpaBetRow` });
            const productDetailColumn = cTag('div',{ 'class':`columnXS12`, 'style': "position: relative;" });
                const iBoxContent = cTag('div',{ 'class':`cartContent`});
                    const productTableRow = cTag('div',{ 'class':`flexSpaBetRow` });
                        const productTableColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin: 0; padding: 0;" });
                            const productTable = cTag('table',{ 'class':` table-bordered`, 'style': "margin-bottom: 0px;" });
                                const productHead = cTag('thead');
                                    headRow = cTag('tr');
                                        thCol = cTag('th',{ 'width':`40px`,'style': "text-align: right;" });
                                        thCol.innerHTML = '#';
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th');
                                        thCol.innerHTML = Translate('Description');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`,'class':`EstimateTitle`, 'style': "text-align: right;" });
                                        if(OS !=='unknown') thCol.innerHTML = Translate('Need-Have-onPO');
                                        else thCol.innerHTML = Translate('Need/Have/OnPO');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`6%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Time/Qty') ;
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Shipping Time/Qty');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Unit Price');;
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Total');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`60px`});
                                        thCol.appendChild(cTag('i',{ 'class':`fa fa-trash-o` }));
                                    headRow.appendChild(thCol);
                                productHead.appendChild(headRow);
                            productTable.appendChild(productHead);
                            productTable.appendChild(cTag('tbody',{ 'id':`invoice_entry_holder` }));
                                productBody = cTag('tbody');
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'style': "text-align: right;",'id':`barcodeserno` });
                                        tdCol.innerHTML = 1;
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'colspan':`7` });
                                            const searchDiv = cTag('div',{ 'class':`flexStartRow` });
                                            searchDiv.appendChild(cTag('input',{ 'type':`hidden`,'id':`temp_pos_cart_id`,'name':`temp_pos_cart_id`,'value':`0` }));
                                                const newProductInGroup = cTag('div',{ 'class':`input-group columnXS12 columnSM4 columnMD4` });
                                                newProductInGroup.appendChild(cTag('input',{ 'maxlength':`50`,'type':`text`,'id':`search_sku`,'name':`search_sku`,'class':`form-control search_sku ui-autocomplete-input`, 'style': "min-width: 120px;", 'placeholder':Translate('Search by product name or SKU'),'autocomplete':`off` }));
                                                    let newProductSpan = cTag('span',{ 'id':'add_new_product', 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Product'),'class':`input-group-addon cursor` });
                                                    newProductSpan.appendChild(cTag('i',{ 'class':`fa fa-plus` }));
                                                    newProductSpan.append(' '+Translate('New'));
                                                newProductInGroup.appendChild(newProductSpan);
                                            searchDiv.appendChild(newProductInGroup);
                                            searchDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`clickYesNo`,'id':`clickYesNo`,'value':`0` }));
                                                const div = cTag('div',{ 'class':`columnXS12 columnSM8 columnMD8`, 'style': "text-align: start;" });
                                                    let productPickerButton = cTag('button',{ 'type':`button`,'name':`showcategorylist`,'id':`product-picker-button`,'click': showProductPicker,'class':`btn productPickerButton` });
                                                    productPickerButton.innerHTML = Translate('Open Product Picker');
                                                div.appendChild(productPickerButton);
                                                    let oneTimeButton = cTag('button',{ 'click': ()=> AJget_oneTimePopup(0), 'class':`btn defaultButton`,  'style': "margin-left: 15px;" });
                                                    oneTimeButton.innerHTML = Translate('Add One Time Product');
                                                div.appendChild(oneTimeButton);
                                            searchDiv.appendChild(div);
                                            searchDiv.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_search_sku`,'style':"margin-left:6px" }));
                                        tdCol.appendChild(searchDiv);
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'style':`padding: 0`,'colspan':`8` });
                                        tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_productlist` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`pagi_index`,'id':`pagi_index`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppcategory_id`,'id':`ppcategory_id`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppproduct_id`,'id':`ppproduct_id`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`totalrowscount`,'id':`totalrowscount`,'value':`0` }));
                                            let searchFilterRow = cTag('div',{ 'class':`flexSpaBetRow`,'id':`filterrow`,'style':'display:none;padding:10px 60px 0 50px;gap:5px'});
                                                let searchFilterDiv = cTag('div',{ style:'display:none', 'id':`filter_name_html`});
                                                    const searchInGroup = cTag('div',{ 'class':`input-group` });
                                                        const filter_name = cTag('input',{ 'maxlength':`50`,'type':`text`,'placeholder':Translate('Search name'),'value':``,'class':`form-control product-filter`,'name':`filter_name`,'id':`filter_name` });
                                                        filter_name.addEventListener('keyup', e=>{if(e.which===13) showCategoryPPProduct()});
                                                    searchInGroup.appendChild(filter_name);
                                                        let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'click': showCategoryPPProduct,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search name') });
                                                        searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                                                    searchInGroup.appendChild(searchSpan);
                                                searchFilterDiv.appendChild(searchInGroup);
                                            searchFilterRow.appendChild(searchFilterDiv);
                                                const  searchFormData = cTag('div');
                                                searchFormData.appendChild(cTag('label',{ 'id':`PPfromtodata` }));
                                            searchFilterRow.appendChild(searchFormData);
                                                let categoryListDiv = cTag('div',{ style:'display:none', 'id':`all-category-button`});
                                                    let categoryListGroup = cTag('div',{ 'class':`input-group` });
                                                        const categoryListLink = cTag('a',{ 'href':`javascript:void(0);`,'title':Translate('All Category List'),'click': reloadProdPkrCategory });
                                                            let hiLightSpan = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "background: #a71d4c; color: #FFF; border-color: #a71d4c;" });
                                                                let listLabel = cTag('label');
                                                                listLabel.innerHTML = Translate('All Category List');
                                                            hiLightSpan.appendChild(listLabel);
                                                        categoryListLink.appendChild(hiLightSpan);
                                                    categoryListGroup.appendChild(categoryListLink);
                                                categoryListDiv.appendChild(categoryListGroup);
                                            searchFilterRow.appendChild(categoryListDiv);
                                        tdCol.appendChild(searchFilterRow);
                                            const productPickerDiv = cTag('div',{ 'style': "position: relative;" });
                                                let pickerDiv = cTag('div',{ 'class':`columnSM12`,'id':`product-picker`,'style':'display:none; align-items:center; min-height: 90px;'});
                                                pickerDiv.appendChild(cTag('div',{ 'id':`allcategorylist`,'style':'display:none;padding:0 50px 0 40px;width:100%' }));
                                                pickerDiv.appendChild(cTag('div',{ 'id':`allproductlist`,'style':'display:none;padding:0 50px 0 40px;width:100%' }));
                                            productPickerDiv.appendChild(pickerDiv);
                                                const previousArrow = cTag('div',{ 'class':`prevlist`,style:'display:none'});
                                                    const previousButton = cTag('button',{ 'type':`button`, 'click':preNextCategory, 'style':'background:initial'});
                                                    previousButton.innerHTML = '‹';
                                                previousArrow.appendChild(previousButton);
                                            productPickerDiv.appendChild(previousArrow);
                                                const nextArrow = cTag('div',{ 'class':`nextlist`,style:'display:none'});
                                                    const nextButton = cTag('button',{ 'type':`button`, 'click':preNextCategory, 'style':'background:initial' });
                                                    nextButton.innerHTML = '›';
                                                nextArrow.appendChild(nextButton);
                                            productPickerDiv.appendChild(nextArrow);
                                        tdCol.appendChild(productPickerDiv);
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                                    headRow = cTag('tr',{'id':'display', 'class':`bgtitle`});
                                        tdCol = cTag('td',{ 'colspan':`3`,'align':`right` });
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'style': "text-align: right;" });
                                            let timeQtyTotal = cTag('label',{ 'id':`timeQtyTotal` });
                                            timeQtyTotal.innerHTML = 0;
                                        tdCol.appendChild(timeQtyTotal);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                            let shippingTimeQty = cTag('label',{ 'id':`shippingTimeQty` });
                                            shippingTimeQty.innerHTML = 0;
                                        tdCol.appendChild(shippingTimeQty);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'align':`right` });
                                            let totalDiv = cTag('label');
                                            totalDiv.innerHTML = `${Translate('Taxable Total')} :`;
                                        tdCol.appendChild(totalDiv);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'align':`right` });
                                            let currencyDiv = cTag('b',{ 'id':`taxable_totalstr` });
                                            currencyDiv.innerHTML = currency+'0.00';
                                        tdCol.appendChild(currencyDiv);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable_total`,'id':`taxable_total`,'value':`0.00` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td');
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                                productBody.appendChild(cTag('tr',{'id':'tax_rows1'}));
                                productBody.appendChild(cTag('tr',{'id':'tax_rows2'}));

                                    headRow = cTag('tr',{ 'id':`nontaxable_totalrow` });
                                        tdCol = cTag('td',{ 'colspan':`6`,'align':`right` });
                                            let nonTaxDiv = cTag('label');
                                            nonTaxDiv.innerHTML = `${Translate('Non Taxable Total')} :`;
                                        tdCol.appendChild(nonTaxDiv);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'align':`right` });
                                            let currencyDiv1 = cTag('b',{ 'id':`nontaxable_totalstr`});
                                            currencyDiv1.innerHTML = currency+'0.00';
                                        tdCol.appendChild(currencyDiv1);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`nontaxable_total`,'id':`nontaxable_total`,'value':`0.00` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td');
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                                    headRow = cTag('tr', {'class':`bgtitle`});
                                        tdCol = cTag('td',{ 'colspan':`6`,'align':`right`});
                                            let grandTotalDiv = cTag('label');
                                            grandTotalDiv.innerHTML = `${Translate('Grand Total')} :`;
                                        tdCol.appendChild(grandTotalDiv);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'align':`right` });
                                            let currencyDiv2 = cTag('b',{ 'id':`grand_totalstr`});
                                            currencyDiv2.innerHTML = currency+'0.00';
                                        tdCol.appendChild(currencyDiv2);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':`0.00` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td');
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`2` });
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'colspan':`6`,'class':`bgblack`, 'style': "font-weight: bold; font-size: 16px;" });
                                        tdCol.innerHTML = Translate('Take payment');
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                            productTable.appendChild(productBody);
                            productTable.appendChild(cTag('tbody',{ 'id':`loadPOSPayment` }));
                                productBody = cTag('tbody');
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'id':'paymentproperties', 'colspan':`7`,'align':`right` });
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'readonly':``,'required':``,'name':`payment_datetime`,'id':`payment_datetime` }));
                                        tdCol.appendChild(cTag('span',{ 'id':`error_amount`,'class':`errormsg` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'id':`buttonPayment`, 'style': "padding-top: 10px" });
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'class':`bgtitle`,'colspan':`6`,'align':`right` });
                                            let amountDueLabel = cTag('label',{ 'for':`amount_due`,'id':`amount_duetxt` });
                                            amountDueLabel.innerHTML = Translate('Amount Due');
                                        tdCol.appendChild(amountDueLabel);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'class':`bgtitle`,'align':`right` });
                                            let currencyValueLabel = cTag('label',{ 'id':`amountduestr` });
                                            currencyValueLabel.innerHTML = currency+'0.00';
                                        tdCol.appendChild(currencyValueLabel);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`amount_due`,'id':`amount_due`,'value':`0.00` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`changemethod`,'id':`changemethod`,'value':Translate('Cash') }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`available_credit`,'id':`available_credit` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'class':`bgtitle` });
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                                    const creditHeadRow = cTag('tr',{'id':'available_creditrow',style:'display:none'});
                                            tdCol = cTag('td',{ 'colspan':`6`,'align':`right` });
                                                label = cTag('label');
                                                label.innerHTML = Translate('Customer has available credit of')+' :';
                                            tdCol.appendChild(label);
                                        creditHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.appendChild(cTag('label',{id:'available_credit_label'}));
                                        creditHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td');
                                            tdCol.innerHTML = ' ';
                                        creditHeadRow.appendChild(tdCol);                            
                                productBody.appendChild(creditHeadRow);
                                    
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'class':'ashbdclass', 'colspan':`7` });
                                        let buttonName = cTag('div',{ 'class':'flexEndRow', 'style': "align-items: center;" });
                                            let hiddenInput = cTag('div');
                                            hiddenInput.appendChild(cTag('input',{ 'type':`hidden`,'id':`pos_id`,'name':`pos_id` }));
                                            hiddenInput.appendChild(cTag('input',{ 'type':`hidden`,'name':`invoice_no`,'id':`invoice_no` }));
                                            hiddenInput.appendChild(cTag('input',{ 'type':`hidden`,'name':`customer_id`,'id':`customer_id` }));
                                            hiddenInput.appendChild(cTag('input',{ 'type':`hidden`,'name':`completed`,'id':`completed`,'value':`0` }));
                                            hiddenInput.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage` }));
                                            hiddenInput.appendChild(cTag('input',{ 'type':`hidden`,'name':`default_invoice_printer`,'id':`default_invoice_printer` }));
                                                const completeButton = cTag('div',{ 'class':`input-group` });
                                                    let completeButtonDiv = cTag('button',{ 'name':`CompleteBtn`,'id':`CompleteBtn`,'class':`btnFocus moneyIcon cursor`,style:'display:none' });
                                                    completeButtonDiv.addEventListener('click',completeOrder);
                                                        let moneySpan = cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;" });
                                                        const completeLabel = cTag('label');
                                                        completeLabel.innerHTML = Translate('Complete');
                                                    completeButtonDiv.append(moneySpan, completeLabel);
                                                completeButton.appendChild(completeButtonDiv);
                                                    const completeButton2 = cTag('button',{ 'name':`CompleteBtnDis`,'id':`CompleteBtnDis`,'class':`btnFocus` });
                                                        let moneyIcon = cTag('span',{ 'class':`input-group-addon cursor` });
                                                        moneyIcon.appendChild(cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;" }));
                                                    completeButton2.appendChild(moneyIcon);
                                                        span = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "padding-left: 0;" });
                                                            let titleLabel = cTag('label');
                                                            titleLabel.innerHTML = Translate('Complete');
                                                        span.appendChild(titleLabel);
                                                    completeButton2.appendChild(span);
                                                completeButton.appendChild(completeButton2);
                                            hiddenInput.appendChild(completeButton);

                                            let cancelOrderButton = cTag('div',{ 'id':`status_cancelled`, 'style': "margin-right: 10px;" });
                                                const cancelOrderInGroup = cTag('div',{ 'class':`input-group` });
                                                    const cancelOrderLink = cTag('button',{ 'class':`btnFocus iconButton cursor` });
                                                    if(allowed['7'] && allowed['7'].includes('cncl')) cancelOrderLink.addEventListener('click', function (){noPermissionWarning('to Cancel Orders')});
                            			            else cancelOrderLink.addEventListener('click', cancelOrder);
                                                        const removeSpan = cTag('i',{ 'class':`fa fa-remove`, 'style': "font-size: 1.5em;"});
                                                        let buttonLabel = cTag('label');
                                                        buttonLabel.innerHTML = ` ${Translate('Cancel Order')} `;
                                                    cancelOrderLink.append(removeSpan, buttonLabel);
                                                cancelOrderInGroup.appendChild(cancelOrderLink);
                                            cancelOrderButton.appendChild(cancelOrderInGroup);

                                            let shipProductButtonDiv = cTag('div',{ 'style': "margin-right: 10px;", 'id':`ShippedAllProducts` });
                                                let shipProductButton = cTag('button',{ 'class':`btn bgblack`, 'style': "color: #fff; padding: 10px 10px;", 'type':`button`,'click': ShippedAllProducts });
                                                    const shipProductTitle = cTag('span');
                                                    if(OS !=='unknown') shipProductTitle.innerHTML = 'Ship All';
                                                    else  shipProductTitle.innerHTML = Translate('Ship All Products');
                                                shipProductButton.appendChild(shipProductTitle);
                                            shipProductButtonDiv.appendChild(shipProductButton);
                                            buttonName.append(shipProductButtonDiv, cancelOrderButton, hiddenInput);
                                        tdCol.appendChild(buttonName);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'class':'ashbdclass'});
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                productBody.appendChild(headRow);
                            productTable.appendChild(productBody);
                        productTableColumn.appendChild(productTable);
                    productTableRow.appendChild(productTableColumn);
                iBoxContent.appendChild(productTableRow);
            productDetailColumn.appendChild(iBoxContent);
        productDetailRow.appendChild(productDetailColumn);
    dashBoard.appendChild(productDetailRow);

        const activityRow = cTag('div',{ 'class':`flexSpaBetRow` });
            const activityColumn = cTag('div',{ 'class':`columnSM12` });
                let hiddenProperties = {
                    'note_forTable': 'pos' ,
                    'spos_id': '' ,
                    'table_idValue': '' ,
                    'publicsShow': '1' ,
            }
            activityColumn.appendChild(historyTable(Translate('Product History'),hiddenProperties, true));
        activityRow.appendChild(activityColumn);
    dashBoard.appendChild(activityRow);

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
            select = document.querySelector('#shistory_type');
                const option = cTag('option', {'value': shistory_type});
            select.appendChild(option);
            select.value = shistory_type;
        }
    }

    addCustomeEventListener('filter',filter_Orders_edit);
    addCustomeEventListener('loadTable',loadTableRows_Orders_edit);
    addCustomeEventListener('changeCart',changeThisOrderRow);
    AJ_edit_MoreInfo();
}

async function AJ_edit_MoreInfo(){
    const jsonData = {invoice_no:segment3};
    const url = '/'+segment1+'/AJ_edit_MoreInfo';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        document.querySelector('#subPermission').value = data.subPermission.join(',');
        document.querySelector('#oldorder_status').value = data.status;
        const order_status = document.querySelector('#order_status');
        order_status.style.background = data.currentStatusBG;
        order_status.style.color = data.color;
        for (const key in data.OrdStaOpt) {
            const [values, bgColor,color] =  data.OrdStaOpt[key];
                const option = cTag('option',{ 'value':values, 'style':`background:${bgColor};color:${color};`})
                option.innerHTML = values;
            order_status.appendChild(option);
        }
        order_status.value = data.status;

        document.getElementById('full_page_print').addEventListener('click',()=>{printbyurl(`/Orders/prints/large/${data.pos_id}/${document.getElementById('amount_due').value}`)});
        document.getElementById('small_page_print').addEventListener('click',()=>{printbyurl(`/Orders/prints/small/${data.pos_id}/${document.getElementById('amount_due').value}`)});
        document.getElementById('pick_list_print').addEventListener('click',()=>{printbyurl(`/Orders/prints/pick/${data.pos_id}`)});

        document.querySelector('#email_address').value = data.customeremail;
        document.querySelector('#edit_customer_info').addEventListener('click',()=>dynamicImport('./Customers.js','AJget_CustomersPopup',[document.getElementById('customer_id').value]));
        
        const customer_link = document.querySelector('#customer_link');
        customer_link.setAttribute('href',`/Customers/view/${data.customer_id}`,);
        customer_link.append(data.customername,' ',cTag('i',{'class':'fa fa-link'}));
        document.querySelector('#customeremail').innerHTML = data.customeremail;
        document.querySelector('#customerphone').innerHTML = data.customerphone;
        document.querySelector('#changeOrderInfo').addEventListener('click', ()=>changeOrderInfo(data.invoice_no, DBDateToViewDate(data.sales_datetime, 1)[0]),)
        document.querySelector('#employee_id').value =  data.employee_id;
        document.querySelector('#salesman_namestr').innerHTML = data.salesman_name;
        document.querySelector('#invoice_no_in_orderInfo').append(data.invoice_no);
        document.querySelector('#sales_datetime').append(DBDateToViewDate(data.sales_datetime, 1, 1)[0]);
        loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
        const addProduct = document.querySelector('#add_new_product');
        if(data.pPermission !== 1) addProduct.addEventListener('click',()=>noPermissionWarning('Product'));
        else addProduct.addEventListener('click',()=>dynamicImport('./Products.js','AJget_ProductsPopup',['Orders',0,0,addCartsProduct]));
        document.querySelector('#display').style.display = data.display;

        let label, tdCol, bTag, div, span, select;
        const txr1 = document.querySelector('#tax_rows1');
        const txr2 = document.querySelector('#tax_rows2');
        if(data.no_of_result_rows>0){
            if(data.no_of_result_rows===1){ 
                let txtInc = '';
                if(data.tax_inclusive1>0){txtInc = ' Inclusive';}
                    tdCol = cTag('td',{'colspan':`6`, 'style': "text-align: right;"});
                        let taxPercentageDiv = cTag('span',{ 'style': "font-weight: bold;" });
                        taxPercentageDiv.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%`+`${txtInc}) :`;
                    tdCol.appendChild(taxPercentageDiv);
                txr1.appendChild(tdCol);

                    tdCol = cTag('td',{'style': "text-align: right;" });
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':data.taxes_name1 }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':data.taxes_percentage1 }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':data.tax_inclusive1 }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
                        let taxCurrencyDiv = cTag('span',{ 'id':`taxes_total1str`, 'style': "font-weight: bold; width: 150px; display:inline-block;" });
                        taxCurrencyDiv.innerHTML = data.currency+'0.00';
                    tdCol.appendChild(taxCurrencyDiv);
                        
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                        bTag = cTag('b',{ style:'display:none','id':`taxes_total2str` });
                        bTag.innerHTML = data.currency+'0.00';
                    tdCol.appendChild(bTag);
                txr1.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                txr1.appendChild(tdCol);
            }
            else{
                let tax1 = '';
                if(data.no_of_default_rows>1) tax1 = 1;
                    tdCol = cTag('td',{ 'colspan':`6`, 'style': "text-align: right;" });
                        let taxDiv = cTag('div',{'class':'flexEndRow', 'style': "align-items: center;"});
                            let taxColumn = cTag('div',{ 'class':`columnXS3 columnMD1`, 'style': "font-weight: bold;" });
                            taxColumn.innerHTML = `${Translate('Tax')}${tax1} :`;
                        taxDiv.appendChild(taxColumn);
                            let taxDropdown = cTag('div',{ 'class':` columnXS6 columnMD2`});
                                let selectTax = cTag('select',{ 'id':`taxes_id1`,'name':`taxes_id1`,'class':`form-control taxes_id`,'title':`Tax1`,'change':()=>onChangeTaxesId(1) });
                                setOptions(selectTax,data.option1,1,1);
                                selectTax.value = data.option1Val;
                            taxDropdown.appendChild(selectTax);
                        taxDiv.appendChild(taxDropdown);
                    tdCol.appendChild(taxDiv);
                    txr1.appendChild(tdCol);
                    tdCol = cTag('td',{'style': "text-align: right; vertical-align: middle;" });
                        let currencyValue = cTag('b',{'id':`taxes_total1str` });
                        currencyValue.innerHTML = currency+'0.00';
                    tdCol.appendChild(currencyValue);
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0.00` }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':data.taxes_name1 }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':data.taxes_percentage1 }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':data.tax_inclusive1 }));
                    tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_taxes_id1` }));
                txr1.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                txr1.appendChild(tdCol);

                if(data.no_of_default_rows>1){
                        tdCol = cTag('td',{ 'colspan':`6`, 'style': "text-align: right;" });
                            let taxDiv = cTag('div',{'class':'flexEndRow', 'style': "align-items: center;"});
                                let tax2Column = cTag('div',{ 'class':`columnXS3 columnMD1`, 'style': "font-weight: bold;" });
                                tax2Column.innerHTML = Translate('Tax2')+' :';
                            taxDiv.appendChild(tax2Column);
                                let tax2Value = cTag('div',{ 'class':`columnXS6 columnMD2`,'style':'font-weight: bold;' });
                                    select = cTag('select',{ 'id':`taxes_id2`,'name':`taxes_id2`,'class':`form-control taxes_id`,'title':`2`,'change':()=>onChangeTaxesId(2) });
                                    select.appendChild(cTag('option',{ 'value':`0` }));
                                    setOptions(select,data.option2,1,1);
                                    select.value = data.option2Val;
                                tax2Value.appendChild(select);
                            taxDiv.appendChild(tax2Value);
                        tdCol.appendChild(taxDiv);
                    txr2.appendChild(tdCol);

                        tdCol = cTag('td',{'style': "text-align: right; vertical-align: middle;" });
                            let tax2Field = cTag('b',{'id':`taxes_total2str`});
                            tax2Field.innerHTML = currency+'0.00';
                        tdCol.appendChild(tax2Field);
                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':data.taxes_name2 }));
                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':data.taxes_percentage2 }));
                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':data.tax_inclusive2 }));
                        tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_taxes_id2` }));
                    txr2.appendChild(tdCol);
                        tdCol = cTag('td');
                        tdCol.innerHTML = ' ';
                    txr2.appendChild(tdCol);
                }
                else{
                    txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
                    txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
                    txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
                    txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                        bTag = cTag('b',{ style:'display:none','id':`taxes_total2str` });
                        bTag.innerHTML = data.currency+'0.00';
                    txr1.appendChild(bTag);
                }
            }
        }
        else{
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':`` }));
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':`0` }));
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':`0` }));
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
            txr1.appendChild(cTag('b',{ style:'display:none','id':`taxes_total1str` }));
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
            txr1.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                bTag = cTag('b',{ style:'display:none','id':`taxes_total2str` });
                bTag.innerHTML = data.currency+'0.00';
            txr1.appendChild(bTag);
        }

        loadPaymentData(document.querySelector('#loadPOSPayment'),data.paymentData);
        document.querySelector('#payment_datetime').value = DBDateToViewDate(data.payment_datetime);           
        
        let paymentDiv = cTag('div',{ 'class':`flexEndRow` });
            if(data.multiple_cash_drawers>0 && data.draOpt.length>0){
                div = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
                    select = cTag('select',{ 'class':`form-control`,'name':`drawer`,'id':`drawer`,'change': setCDinCookie });
                    select.addEventListener('change',togglePaymentButton);
                    if(data.drawer===''){
                        let option = cTag('option',{ 'value':`` });
                            option.innerHTML = Translate('Select Drawer');
                        select.appendChild(option);
                    }
                    setOptions(select,data.draOpt.filter(item=>item!==''),0,0);
                    select.value = data.drawer;
                div.appendChild(select);
            }
            else{
                div = cTag('input',{ 'type':`hidden`,'name':`drawer`,'id':`drawer`,'value':`` });
            }
        paymentDiv.appendChild(div);

            div = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;" });
                let inputGroupMethod = cTag('div',{ 'class':`input-group`, 'style': "min-width: 120px;"});
                    span = cTag('span', { 'data-toggle':`tooltip`, 'data-original-title':Translate('Type'), 'class':`input-group-addon cursor`});
                    span.innerHTML = Translate('Type')+' :';
                    select = cTag('select',{ 'class':`form-control`,'name':`method`,'id':`method`,'change': checkMethod });
                    setOptions(select,data.metOpt,0,0);
                inputGroupMethod.append(span, select);
            div.appendChild(inputGroupMethod);
        paymentDiv.appendChild(div);

            div = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
                let inputGroupAmount = cTag('div',{ 'class':`input-group`, 'style': "min-width: 120px;"});
                    span = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Currency'),'class':`input-group-addon cursor`});
                    span.innerHTML = currency;
                    const input = cTag('input',{ 'type': "text",'data-min':`-${data.paymentData.reduce((total,item)=>total+item.payment_amount,0)}`,'data-max':'9999999.99','data-format':'d.dd','value':`0`,'name':`amount`,'id':`amount`,'class':`form-control`, 'style': "font-weight: bold; text-align: right;", 'keyup': checkMethod });
                    input.addEventListener('keydown',event=>{if(event.which===13) addPOSPayment()});
                    controllNumericField(input, '#error_amount');
                inputGroupAmount.append(span, input);
            div.appendChild(inputGroupAmount);
        paymentDiv.appendChild(div);
        paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`multiple_cash_drawers`,'id':`multiple_cash_drawers`,'value':data.multiple_cash_drawers }));
        paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`returnURL`,'id':`returnURL`,'value':`${location.origin}/Orders/edit/${data.invoice_no}/` }));
        paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`sqrup_currency_code`,'id':`sqrup_currency_code`,'value':data.sqrup_currency_code }));
        paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`webcallbackurl`,'id':`webcallbackurl`,'value':data.webcallbackurl }));
        paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`accounts_id`,'id':`accounts_id`,'value':data.accounts_id }));
        paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`user_id`,'id':`user_id`,'value':data.user_id }));
        paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`available_credit`,'id':`available_credit`,'value':data.available_credit }));
        document.getElementById('paymentproperties').appendChild(paymentDiv);

        if(data.available_credit>0){
            document.getElementById('available_credit_label').innerHTML = addCurrency(data.available_credit);
            document.getElementById('available_creditrow').style.display = '';
        }
        if(data.ashbdclass!==''){
            document.querySelectorAll('.ashbdclass').forEach(item=>{
                item.classList.add(data.ashbdclass);
            });
        }
        const segment2Name = segment1;
        document.querySelector('#pos_id').value = data.pos_id;
        document.querySelector('#invoice_no').value = data.invoice_no;
        document.querySelector('#customer_id').value = data.customer_id;
        document.querySelector('#frompage').value = segment2Name;
        document.querySelector('#default_invoice_printer').value = data.default_invoice_printer;
        document.querySelector('#spos_id').value =  document.querySelector('#table_idValue').value = data.pos_id;
        document.querySelector('#digital_signature_btn').addEventListener('click',()=>printbyurl(`/${segment2Name}/prints/large/${data.pos_id}/${document.getElementById('amount_due').value}/signature`))

        cartsAutoFuncCall(); 

        multiSelectAction('orders_prints');

        if(document.getElementById("method") && document.getElementById("buttonPayment")){
            checkMethod();
            showOrNotSquareup();
            togglePaymentButton();
        }
    
        setTimeout(function() {document.getElementById("search_sku").focus();}, 500);
        AJautoComplete_cartProduct();
        filter_Orders_edit();
    }
}

async function filter_Orders_edit(){
    let page = 1;
	document.getElementById("page").value = page;
	const jsonData = {};
	jsonData['spos_id'] = document.getElementById('table_idValue').value;
	jsonData['shistory_type'] = document.getElementById('shistory_type').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

    const url = "/Orders/AJgetHPage/filter";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);			
        document.getElementById("totalTableRows").value = data.totalRows;
        setTableHRows(data.tableRows, activityFieldAttributes);
        
        const select = document.getElementById("shistory_type");
        select.innerHTML = '';
            const historyOption = cTag('option');
            historyOption.value = '';
            historyOption.innerHTML = Translate('All Activities');
        select.appendChild(historyOption);
        setOptions(select,data.actFeeTitOpt,0,1);
        select.value = jsonData['shistory_type'];

        onClickPagination();
    }
}

async function loadTableRows_Orders_edit(){
	const jsonData = {};
	jsonData['spos_id'] = document.getElementById('table_idValue').value;
	jsonData['shistory_type'] = document.getElementById('shistory_type').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = "/Orders/AJgetHPage";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        setTableHRows(data.tableRows, activityFieldAttributes);
        onClickPagination();
    }
}

async function AJsave_Orders(event){
    if(event){ event.preventDefault();}

    let submitBtn;
	submitBtn = document.querySelector('#submit');
	submitBtn.value = Translate('Saving')+'...';
	submitBtn.disabled = true;
	
	const jsonData = serialize('#frmAddOrders');
    const url = "/Orders/AJsave_Orders/";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
			window.location = '/Orders/edit/'+data.id+'/'+data.savemsg;
		}
		else{
			if(data.message==='noCustomerName') showTopMessage('alert_msg', Translate('Customer name not found. Please try again with valid customer name.'));
			else if(data.message==='errorAdding') showTopMessage('alert_msg', Translate('Error occured while adding order information! Please try again.'));
			
			if(parseInt(document.getElementById("pos_id").value)===0){
				submitBtn = document.querySelector('#submit');
				submitBtn.value = Translate('Add');
				submitBtn.disabled = false;
			}
			else{
				submitBtn = document.querySelector('#submit');
				submitBtn.value = Translate('Update');
				submitBtn.disabled = false;
			}
		}
    }
	return false;
}

async function changeOrderInfo(invoice_no, sale_date){
	const pos_id = document.getElementById("pos_id").value;
    let employee_id = document.getElementById("employee_id").value;
	if(pos_id>0){
		const jsonData = {};
		jsonData['employee_id'] = employee_id;

		const url = "/Orders/showEmplyeeOptions";
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            let inputField;
            const formhtml = cTag('div');
                const customerOrderInfoForm = cTag('form', {'action': "#", name: "frmorders", id: "frmorders", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                    /* const divError = cTag('div', {id: "error_orders", class: "errormsg"});
                customerOrderInfoForm.appendChild(divError); */
                    const invoiceRow = cTag('div',{class: "flex", 'align': "left", 'style': "align-items: center;"});
                        const invoiceColumn = cTag('div', {class: "columnSM4"});
                            const invoiceLabel = cTag('label', {'for': "invoice_number"});
                            invoiceLabel.innerHTML = Translate('Invoice No.');
                        invoiceColumn.appendChild(invoiceLabel);
                    invoiceRow.appendChild(invoiceColumn);
                        const invoiceField = cTag('div', {class: "columnSM8"});
                            inputField = cTag('input', {'readonly': true, 'required': "required", 'type': "text", class: "form-control", name: "invoice_number", id: "invoice_number",  'value': invoice_no, 'size': 12, 'maxlength': 12});
                        invoiceField.appendChild(inputField);
                    invoiceRow.appendChild(invoiceField);
                customerOrderInfoForm.appendChild(invoiceRow);

                    const salesmanRow = cTag('div',{class: "flex", 'align': "left", 'style': "align-items: center;"});
                        const salesmanColumn = cTag('div', {class: "columnSM4"});
                            const salesmanLabel = cTag('label', {'for': "last_name"});
                            salesmanLabel.innerHTML = Translate('Salesman');
                                const requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            salesmanLabel.appendChild(requiredField);
                        salesmanColumn.appendChild(salesmanLabel);
                    salesmanRow.appendChild(salesmanColumn);
                        const salesmanDropDown = cTag('div', {class: "columnSM8"});
                            const selectEmployee = cTag('select', {'required': "required", name: "employee_id", id: "employee_id", class: "form-control"});
                                const employeeOption = cTag('option', {'value': ""});
                            selectEmployee.appendChild(employeeOption);
                            setOptions(selectEmployee, data.emplyeeOpts, 1, 1);
                        salesmanDropDown.appendChild(selectEmployee);
                        salesmanDropDown.appendChild(cTag('div', {id: "error_orders", class: "errormsg"}));
                    salesmanRow.appendChild(salesmanDropDown);
                customerOrderInfoForm.appendChild(salesmanRow);

                    const dateRow = cTag('div',{class: "flex", 'align': "left", 'style': "align-items: center;"});
                        const dateColumn = cTag('div', {class: "columnSM4"});
                            const dateLabel = cTag('label', {'for': "sale_date"});
                            dateLabel.innerHTML = Translate('Date');
                        dateColumn.appendChild(dateLabel);
                    dateRow.appendChild(dateColumn);
                        const dateField = cTag('div', {class: "columnSM8"});
                            inputField = cTag('input', {'readonly': true, 'type': "text", class: "form-control", name: "sale_date", id: "sale_date",  'value': sale_date, 'maxlength': 10});
                        dateField.appendChild(inputField);
                    dateRow.appendChild(dateField);
                customerOrderInfoForm.appendChild(dateRow);

                    let hiddenInput = cTag('input', {'type': "hidden", name: "pos_id", 'value': pos_id});
                customerOrderInfoForm.appendChild(hiddenInput);
            formhtml.appendChild(customerOrderInfoForm);
            
            popup_dialog600(Translate('Change Order Information'), formhtml, Translate('Save'), saveChangeOrderInfo);
            
            setTimeout(function() {
                formhtml.querySelector("#employee_id").value = employee_id;
                formhtml.querySelector("#employee_id").focus();
            }, 500);
        }
	}
	return true;
}

async function saveChangeOrderInfo(hidePopup){
    const employee_id = document.querySelector('#popup #employee_id').value;

    if(employee_id===''){
        document.getElementById('error_orders').innerHTML = "Salesman required";
        return;
    }
    let saveBtn;
	saveBtn = document.querySelector(".btnmodel");
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;

	const jsonData = serialize('#frmorders');
    const url = "/Orders/saveChangeOrderInfo/";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr !=='error'){
			document.getElementById("salesman_namestr" ).innerHTML = data.returnStr;
			/* const changeOrderInfo = document.getElementById("changeOrderInfo");
			const changeOrderInfoSplit = changeOrderInfo.getAttribute("onclick").split(", ");
			const newOnClickStr = changeOrderInfoSplit[0]+", '"+employee_id+"', "+changeOrderInfoSplit[2];
			changeOrderInfo.setAttribute('onclick', newOnClickStr); */
            document.getElementById("employee_id").value = employee_id;
			filter_Orders_edit();
			hidePopup();			
		}
		else{
			document.getElementById('error_orders').innerHTML = Translate('Can not save order information');
			saveBtn.innerHTML = Translate('Save');
			saveBtn.disabled = false;
		}
    }
	return false;
}

function changeThisOrderRow({detail:pos_cart_id}){
	const item_type = document.getElementById("item_type"+pos_cart_id).value;
	const product_type = document.getElementById("product_type"+pos_cart_id).value;
	const require_serial_no = parseInt(document.getElementById("require_serial_no"+pos_cart_id).value);
	let add_description = document.getElementById("add_description"+pos_cart_id).value;
    if(add_description !==''){add_description = add_description.replace(/<br\s*\/?>/gi,'');}
	const sales_price = document.getElementById("sales_price"+pos_cart_id).value;
	const minimum_price = document.getElementById("minimum_price"+pos_cart_id).value;
	const qty = document.getElementById("qty"+pos_cart_id).value;
	const shipping_qty = document.getElementById("shipping_qty"+pos_cart_id).value;
	const discount_is_percent = document.getElementById("discount_is_percent"+pos_cart_id).value;
	const discount = document.getElementById("discount"+pos_cart_id).value;
	const frompage = segment1;

    let bTag, span, textarea, inputField, errorSpan, requiredField;
	const formhtml = cTag('div');
		const updateCartForm = cTag('form', {'action': "#", name: "frmordersRow", id: "frmordersRow", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			const errorMessageRow = cTag('div', {class: "flex"});
				const errorMessageColumn = cTag('div', {class: "columnSM12", 'align': "left", id: "showErroMsg"});
            errorMessageRow.appendChild(errorMessageColumn);
        updateCartForm.appendChild(errorMessageRow);

			const unitPriceRow = cTag('div', {class: "flex", 'align': "left"});
                const unitPriceColumn = cTag('div', {class: "columnXS5 columnSM3"});
                    const unitPriceLabel = cTag('label', {'for': "sales_price"});
					unitPriceLabel.innerHTML = Translate('Unit Price')+':';
                unitPriceColumn.appendChild(unitPriceLabel);
            unitPriceRow.appendChild(unitPriceColumn);
                const unitPriceField = cTag('div', {class: "columnXS7 columnSM4"});
					inputField = cTag('input', {'type': "text", 'data-max':'9999999.99','data-format':'d.dd', class: "form-control", name: "sales_price", id: "sales_price", 'value': sales_price});
                    if(minimum_price>0) inputField.setAttribute('data-min',minimum_price);
                    controllNumericField(inputField, '#errmsg_sales_price');
					if(document.getElementById("subPermission") && document.getElementById("subPermission").value.includes('cnccp')){
						inputField.setAttribute('readonly',"")
					}
					inputField.addEventListener('keyup', calculateChangeCartTotal);
					inputField.addEventListener('change', calculateChangeCartTotal);
                unitPriceField.appendChild(inputField);
                unitPriceField.appendChild(cTag('span', {class: "error_msg", id: "errmsg_sales_price"}));
                unitPriceField.appendChild(cTag('input', {type: "hidden", id: "minimum_price", value: minimum_price}));
            unitPriceRow.appendChild(unitPriceField);
                const unitPriceValue = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "salesPriceStr"});
					bTag.innerHTML = currency+'0.00';
                unitPriceValue.appendChild(bTag);
            unitPriceRow.appendChild(unitPriceValue);
        updateCartForm.appendChild(unitPriceRow);

			const qtyRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
                const qtyColumn = cTag('div', {class: "columnXS5 columnSM3"});
                    const qtyLabel = cTag('label', {'for': "qty"});
					qtyLabel.innerHTML = Translate('QTY')+' :';
						requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML = '*';
                    qtyLabel.appendChild(requiredField);
                qtyColumn.appendChild(qtyLabel);
            qtyRow.appendChild(qtyColumn);
                const qtyFieldColumn = cTag('div', {class: "columnXS7 columnSM4"});
					inputField = cTag('input', {'type': "text",'data-min':'0', 'data-max':'9999', 'data-format': 'd', class: 'form-control updatecartfields', name: "qty", id: "qty", 'value': qty});
                    controllNumericField(inputField, '#errmsg_qty');
                    if(product_type==='Labor/Services') inputField.setAttribute('data-format','d.dd')
                    else preventDot(inputField);
					inputField.addEventListener('keyup', calculateChangeCartTotal);
					inputField.addEventListener('change', calculateChangeCartTotal);
                qtyFieldColumn.appendChild(inputField);
                    errorSpan = cTag('span', {class: "error_msg", id: "errmsg_qty"});
                qtyFieldColumn.appendChild(errorSpan);
            qtyRow.appendChild(qtyFieldColumn);
                
                const qtySubTotalValue = cTag('div', {class: "columnSM5", 'align': "right"});
                qtySubTotalValue.innerHTML = Translate('Subtotal')+ ': ';
					bTag = cTag('b', {id: "qtyValueStr"});
					bTag.innerHTML = currency+'0.00';
                qtySubTotalValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "qty_value", id: "qty_value", 'value': 0});
                qtySubTotalValue.appendChild(inputField);
            qtyRow.appendChild(qtySubTotalValue);
        updateCartForm.appendChild(qtyRow);

			const shippingQtyRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
                const shippingQtyColumn = cTag('div', {class: "columnXS5 columnSM3"});
                    const shippingQtyLabel = cTag('label', {'for': "shipping_qty"});					
					shippingQtyLabel.innerHTML = Translate('Shipping Qty')+':';
						requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML = '*';
                    shippingQtyLabel.appendChild(requiredField);
                shippingQtyColumn.appendChild(shippingQtyLabel);
            shippingQtyRow.appendChild(shippingQtyColumn);
                const shippingQtyField = cTag('div', {class: "columnXS7 columnSM4"});
					inputField = cTag('input', {'type': "text",'data-min':'0', 'data-max':'9999', 'data-format':'d', class: 'form-control', name: "shipping_qty", id: "shipping_qty", 'value': shipping_qty});
                    controllNumericField(inputField, '#errmsg_shipping_qty');
                    if(product_type==='Labor/Services') inputField.setAttribute('data-format','d.dd')
                    else preventDot(inputField);
					inputField.addEventListener('keyup', calculateChangeCartTotal);
					inputField.addEventListener('change', calculateChangeCartTotal);
					if(item_type==='cellphones' || (item_type==='product' && require_serial_no===1)){
						inputField.setAttribute('readonly', 'readonly');
					}
                shippingQtyField.appendChild(inputField);
                    errorSpan = cTag('span', {class: "error_msg", id: "errmsg_shipping_qty"});
                shippingQtyField.appendChild(errorSpan);
            shippingQtyRow.appendChild(shippingQtyField);
                const subTotalColumnValue = cTag('div', {class: "columnSM5", 'align': "right"});
				subTotalColumnValue.innerHTML = Translate('Subtotal')+ ': ';
					bTag = cTag('b', {id: "shippingQtyValueStr"});
					bTag.innerHTML = currency+'0.00';
                subTotalColumnValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "shipping_qty_value", id: "shipping_qty_value", 'value': 0});
                subTotalColumnValue.appendChild(inputField);
            shippingQtyRow.appendChild(subTotalColumnValue);
        updateCartForm.appendChild(shippingQtyRow);

			const discountRow = cTag('div', {class: "flex", 'align': "left"}); 
                const discountColumn = cTag('div', {class: "columnXS5 columnSM3"});
                    const discountLabel = cTag('label', {'for': "discount"});
					discountLabel.innerHTML = Translate('Discount')+' :';
                discountColumn.appendChild(discountLabel);
            discountRow.appendChild(discountColumn);
                const discountField = cTag('div', {class: "columnXS7 columnSM4"});
					const discountInGroup = cTag('div', {class: "input-group"});
						span = cTag('span', {class: "input-group-addon cursor", 'style': "padding: 0px;"});
							inputField = cTag('input', { id: "discount", name: "discount", 'type': "text",'data-min':'0', 'data-max': sales_price-minimum_price, 'data-format':'d.dd', 'value': discount, class: "form-control", 'style': "min-width: 120px;"});
							controllNumericField(inputField, '#errmsg_discount');
                            inputField.addEventListener('keyup', calculateChangeCartTotal);
                            inputField.addEventListener('change', calculateChangeCartTotal);
						span.appendChild(inputField);
                    discountInGroup.appendChild(span);
						span = cTag('span', {class: "input-group-addon", 'style': "width: 40px; padding: 0px;"});
							const selectDiscount = cTag('select', {id: "discount_is_percent", name: "discount_is_percent", class: "form-control bgnone", 'style': "width: 60px;"});
							selectDiscount.addEventListener('change', calculateChangeCartTotal);
								let percentOption = cTag('option', {'value': 1});
								percentOption.innerHTML = '%';
                            selectDiscount.appendChild(percentOption);
								let currencyOption = cTag('option', {'value': 0});
								currencyOption.innerHTML = currency;
                            selectDiscount.appendChild(currencyOption);
						span.appendChild(selectDiscount);
                    discountInGroup.appendChild(span);
                discountField.appendChild(discountInGroup);
                discountField.appendChild(cTag('span', {class: "error_msg", id: "errmsg_discount"}));
            discountRow.appendChild(discountField);
				const discountValue = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "discountValueStr"});
					bTag.innerHTML = currency+'0.00';
                discountValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "discountvalue", id: "discountvalue", 'value': 0});
                discountValue.appendChild(inputField);
            discountRow.appendChild(discountValue);
        updateCartForm.appendChild(discountRow);
        updateCartForm.appendChild(cTag('hr'));

			const totalRow = cTag('div', {class: "flex"});
                const totalValue = cTag('div', {class: "columnSM12", 'align': "right"});
                    bTag = cTag('b');
					bTag.innerHTML = Translate('Total')+' :';
                totalValue.appendChild(bTag);
					bTag = cTag('b', {id: "totalValueStr"});
					bTag.innerHTML = currency+'0.00';
                totalValue.appendChild(bTag);
                    inputField = cTag('input', {'type': "hidden", name: "unitPrice", id: "unitPrice", 'value': 0});
                totalValue.appendChild(inputField);
            totalRow.appendChild(totalValue);
        updateCartForm.appendChild(totalRow);

			const additionalDescriptionRow = cTag('div', {class: "flex", 'align': "left"});
                const additionalDescriptionColumn = cTag('div', {class: "columnSM3"});
                    const additionalDescriptionLabel = cTag('label', {'for': "add_description"});
					additionalDescriptionLabel.innerHTML = Translate('Additional Description')+ ':';
                additionalDescriptionColumn.appendChild(additionalDescriptionLabel);
            additionalDescriptionRow.appendChild(additionalDescriptionColumn);
                const additionalDescriptionField = cTag('div', {class: "columnSM9"});
					textarea = cTag('textarea', {class: "form-control", name: "add_description", id: "add_description", 'rows': 2, 'cols': 20});
					textarea.innerHTML = add_description;
                additionalDescriptionField.appendChild(textarea);
            additionalDescriptionRow.appendChild(additionalDescriptionField);
        updateCartForm.appendChild(additionalDescriptionRow);

		//bulktextarea
			if(item_type==='cellphones'){
					const bulkLoadRow = cTag('div', {class: "flex",style:'display:none', id: "bulkrow", 'align': "left"});
                        const bulkLoadColumn = cTag('div', {class: "columnSM3"});
                            const bulkLoadLabel = cTag('label', {for: "sales_price"});
							bulkLoadLabel.innerHTML = Translate('IMEI Numbers')+ ':';
                        bulkLoadColumn.appendChild(bulkLoadLabel);
                    bulkLoadRow.appendChild(bulkLoadColumn);
						const bulkLoadField = cTag('div', {class: "columnSM9", 'align': "right"});
							textarea = cTag('textarea', {'placeholder':Translate('One IMEI number per line'), 'name': "bulkimei", id: "bulkimei", 'cols': 20, 'rows': 3, class: "form-control"});
                        bulkLoadField.appendChild(textarea);
                    bulkLoadRow.appendChild(bulkLoadField);
						const bulkLoadError = cTag('div', {class: "columnSM10", 'style': "text-align: center;"});
                            errorSpan = cTag('span', {class: "error_msg", id: "error_bulkimei"});
                        bulkLoadError.appendChild(errorSpan);
                    bulkLoadRow.appendChild(bulkLoadError);
                updateCartForm.appendChild(bulkLoadRow);
			}
			inputField = cTag('input', {'type': "hidden", name: "pos_cart_idvalue", id: "pos_cart_idvalue", 'value': pos_cart_id});
        updateCartForm.appendChild(inputField);
	formhtml.appendChild(updateCartForm);
	
	if(item_type==='cellphones'){
		popup_dialog(
			formhtml,
			{
				title:Translate('Update Order Cart'),
				width:600,
				buttons: {
					_Bulk_load_IMEI:{
						text: Translate('Bulk load IMEI numbers'), class: 'btn defaultButton orderBulkLoad', 
                        click: showOrderBulkField,
					},
					_Import_IMEI_numbers:{
						text: Translate('Import IMEI numbers'), class: 'btn defaultButton saveOrderBulkLoad',style:'display:none', 
                        click: addCartsBulkIMEI,
					},
                    _Cancel: {
						text: Translate('Cancel'), class: 'btn defaultButton', click: function(hidePopup) {
							hidePopup();
						},
					},
					_Save:{
						text: Translate('Save'), class: 'btn saveButton btnmodel btnOrderSaveRow', 
                        click: updateCartData,
					}
			    }
			}
		);		
	}
	else{	
		popup_dialog(
			formhtml,
			{
				title:Translate('Update Order Cart'),
				width:600,
				buttons: {
					_Cancel: {
						text: Translate('Cancel'), class: 'btn defaultButton', click: function(hidePopup) {
							hidePopup();
						},
					},
					_Save:{
						text: Translate('Save'), class: 'btn saveButton btnmodel btnOrderChangeRow', click: updateCartData
					}
                }
			}
		);
	}
			
	setTimeout(function() {
		document.getElementById("sales_price").focus();		
		document.getElementById("discount_is_percent").value = discount_is_percent;
		
		if(frompage==='Orders'){
			const order_status = document.getElementById("order_status").value;
			if(order_status==='Quotes'){
				document.querySelector("#shipping_qty").readOnly = true;
			}
		}
		
		calculateChangeCartTotal();
		
		if(item_type==='cellphones'){
			let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#";		
			document.querySelector("#bulkimei").addEventListener('keyup',function(){
				let sku = this.value.toUpperCase().replace(' ', '-');
				let IsNumber=true;
				let Char;
				let newsku = '';
				for (let i = 0; i < sku.length && IsNumber === true; i++){ 
					Char = sku.charAt(i); 
					newsku = newsku+Char;
				}
				
				if(sku.length> newsku.length || this.value !== newsku){
					this.value = newsku;
				}
			});
		}
		applySanitizer(formhtml);
	}, 500);
}

function showOrderBulkField(){
    if(document.getElementById("bulkrow").style.display === 'none'){
        document.getElementById("bulkrow").style.display = '';
    }
	if(document.querySelectorAll(".btnOrderSaveRow").length>0){
		document.querySelectorAll(".btnOrderSaveRow").forEach(oneFieldObj=>{
            if(oneFieldObj.style.display !== 'none'){
                oneFieldObj.style.display = 'none';
            }
		});
	}
	if(document.querySelectorAll(".orderBulkLoad").length>0){
		document.querySelectorAll(".orderBulkLoad").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display !== 'none'){
                oneFieldObj.style.display = 'none';
            }
		});
	}
	if(document.querySelectorAll(".saveOrderBulkLoad").length>0){
		document.querySelectorAll(".saveOrderBulkLoad").forEach(oneFieldObj=>{
            if(oneFieldObj.style.display === 'none'){
                oneFieldObj.style.display = '';
            }
		});
	}
	document.getElementById("bulkimei").focus();
}

async function addCartsBulkIMEI(){
    let loadBtn;
	loadBtn = document.querySelector('.saveOrderBulkLoad');
	loadBtn.innerHTML = Translate('Importing IMEI numbers')+'...';
	loadBtn.disabled = true;
    let error_bulkimei = document.getElementById("error_bulkimei");

	const pos_cart_id = document.getElementById("pos_cart_idvalue").value;
	const bulkimei = document.getElementById("bulkimei").value;
	if(bulkimei !==''){
		
        function appendError(data){
            const TotalIMEI = document.getElementById("bulkimei").value.split('\n').filter(item=>item!=='').length;
            error_bulkimei.innerHTML = '';
            data.message.split('|').forEach(item=>{
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
		jsonData['pos_cart_id'] = pos_cart_id;
		jsonData['bulkimei'] = bulkimei;

		const url = "/Orders/addCartsBulkIMEI";
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.action ==='reload'){location.reload();}
			else if(data.action === 'Add'){
				loadBtn = document.querySelector('.saveOrderBulkLoad');
				loadBtn.innerHTML = Translate('Import IMEI numbers');
				loadBtn.disabled = false;
				if(data.message !=='' && typeof data.message==='string'){
                    appendError(data);
					setTimeout(function() {
						if(error_bulkimei){
                            error_bulkimei.innerHTML = '';
                        }
					}, 5000);
				}
				else{error_bulkimei.innerHTML = '';}
				
                if(document.getElementById("bulkrow").style.display !== 'none'){
                    document.getElementById("bulkrow").style.display = 'none';
                }
				document.querySelectorAll(".saveOrderBulkLoad").forEach(item=>{
                    if(item.style.display !== 'none'){
                        item.style.display = 'none';
                    }
                });
				document.querySelectorAll(".btnOrderSaveRow").forEach(item=>{
                    if(item.style.display === 'none'){
                        item.style.display = '';
                    }
                });
				document.querySelectorAll(".orderBulkLoad").forEach(item=>{
                    if(item.style.display === 'none'){
                        item.style.display = '';
                    }
                });
				document.getElementById("qty").value = data.qty;
				document.getElementById("shipping_qty").value = data.shipping_qty;
				calculateChangeCartTotal();
                loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);	
				cartsAutoFuncCall();
			}
			else{
                appendError(data);
				loadBtn = document.querySelector('.saveOrderBulkLoad');
				loadBtn.innerHTML = Translate('Import IMEI numbers');
				loadBtn.disabled = false;
			}
        }
	}
	else{
		error_bulkimei.innerHTML = Translate('Missing IMEI Number');
	}
	return false;
}

function ShippedAllProducts(){								
	confirm_dialog(Translate('ARE YOU SURE?'), Translate('Please confirm you want to make all products (not mobile devices) shipped in full'), confirmShippingAllProducts);
}

async function confirmShippingAllProducts(hidePopup){
	const pos_id = document.getElementById("pos_id").value;

	const jsonData = {};
	jsonData['pos_id'] = pos_id;

    const url = "/Orders/confirmShippedAllP";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.action === 'Update'){
            loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);	
			hidePopup();
			showTopMessage('success_msg', Translate('All products (not mobile devices) shipped in full.'));
			cartsAutoFuncCall();
			filter_Orders_edit();
		}
		else{
            if(data.action==='canNotAdd') showTopMessage('error_msg', Translate('Quotes order could not add shipping QTY.'));
            else showTopMessage('error_msg', data.action);
		}
    }
}

function completeOrder(){
    let amount_due, paymentYN, listcount;
	amount_due = parseInt(document.getElementById("amount_due").value);		
	const hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	paymentYN = 0;
	const payment_amountarray = document.getElementsByName("payment_amount[]");
	listcount = 0;
	if(document.querySelector("#loadPOSPayment")){
		listcount = document.querySelectorAll("#loadPOSPayment .paymentRow").length;
	}
	if(listcount>0){
		for(let m = 0; m < listcount; m++) {
			let payment_amount = parseFloat(payment_amountarray[m].value);
			if(payment_amount==='' || isNaN(payment_amount)){payment_amount =0.00;}
			if(payment_amount !==0){paymentYN++;}
		}
	}
	if(hasdata.length<10 && amount_due===0 && paymentYN === 0){	
		showTopMessage('alert_msg', Translate('Missing cart. Please choose/add new product'));
		document.getElementById("search_sku").focus();
		return(false);
	}
	
	if(showCartCompleteBtn()===true){
		amount_due = document.getElementById("amount_due").value;
		document.getElementById("changemethod").value = 'Cash';
		let changeamountofval = 0;
		if(amount_due<0){
			changeamountofval = amount_due;
		}
		
        let formGroup, inputField; 
		const formhtml = cTag('div');
			formGroup = cTag('div',{"class":"flexSpaBetRow"});
		formhtml.appendChild(formGroup);
			formGroup = cTag('div',{"class":"flexSpaBetRow"});
				const divCol12 = cTag('div', {class: "columnXS12", 'align': "center"});
					const changeAmountValue = cTag('span', {'style': "color:orange;font-size:48px;font-weight:500", id: "changeamountof"});
                    changeAmountValue.innerHTML = addCurrency(-1*changeamountofval);
				divCol12.appendChild(changeAmountValue);
			formGroup.appendChild(divCol12);
		formhtml.appendChild(formGroup);

			formGroup = cTag('div',{"class":"flexSpaBetRow"});
		formhtml.appendChild(formGroup);
			formGroup = cTag('div', {class: "flexSpaBetRow"});
				const exchangeMethodColumn = cTag('div', {class: "columnSM7", 'align': "left"});
                    const exchangeMethodLabel= cTag('label', {'for': "exchangemethod"});
					exchangeMethodLabel.innerHTML = Translate('Choose how the change was given')+':';
                exchangeMethodColumn.appendChild(exchangeMethodLabel);
			formGroup.appendChild(exchangeMethodColumn);
                const exchangeMethodDropDown = cTag('div', {class: "columnSM5", 'align': "left"});
					const selectExchangeMethod = cTag('select', {class: "form-control", name: "exchangemethod", id: "exchangemethod"});
					selectExchangeMethod.addEventListener('change', e => {document.getElementById('changemethod').value=e.target.value;});
					selectExchangeMethod.innerHTML = document.getElementById("method").innerHTML;
                exchangeMethodDropDown.appendChild(selectExchangeMethod);
			formGroup.appendChild(exchangeMethodDropDown);
		formhtml.appendChild(formGroup);

			formGroup = cTag('div', {class: "flexSpaBetRow"});
				const printTypeColumn = cTag('div', {class: "columnSM4", 'align': "left"});
                    const printTypeLabel = cTag('label', {'for': "default_invoice_printer1"});
					printTypeLabel.innerHTML = Translate('Choose print type')+':';
                printTypeColumn.appendChild(printTypeLabel);
			formGroup.appendChild(printTypeColumn);
                const printTypeDropDown = cTag('div', {class: "columnSM8 flexStartRow"});
					const fullPageLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
						inputField = cTag('input', {'type': "radio", 'value': "Large", id: "default_invoice_printer1", name: "print_type", class: "print_type"});
                    fullPageLabel.appendChild(inputField);
					fullPageLabel.append(' '+Translate('Full Page'));
                printTypeDropDown.appendChild(fullPageLabel);
					const thermalLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "Small", id: "default_invoice_printer2", name: "print_type", class: "print_type"});
                    thermalLabel.appendChild(inputField);
					thermalLabel.append(' '+Translate('Thermal'));
                printTypeDropDown.appendChild(thermalLabel);
					const emailLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "Email", id: "default_invoice_printer3", name: "print_type", class: "print_type"});
                    emailLabel.appendChild(inputField);
					emailLabel.append(' '+Translate('Email'));
                printTypeDropDown.appendChild(emailLabel);
					const noReceiptLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "No Receipt", id: "default_invoice_printer4", name: "print_type", class: "print_type"});
                    noReceiptLabel.appendChild(inputField);
					noReceiptLabel.append(' '+Translate('No Receipt'));
                printTypeDropDown.appendChild(noReceiptLabel);
			formGroup.appendChild(printTypeDropDown);
		formhtml.appendChild(formGroup);

			formGroup = cTag('div', {class: "flexSpaBetRow invcustomeremail",style:'display:none'});
				const emailColumn = cTag('div', {class: "columnSM3", 'align': "left"});
                    const emailLabel2 = cTag('label', {'for': "invcustomeremail"});
					emailLabel2.innerHTML = Translate('Email');
						const requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML = '*';
                    emailLabel2.appendChild(requiredField);
                emailColumn.appendChild(emailLabel2);
			formGroup.appendChild(emailColumn);
				const emailField = cTag('div', {class: "columnSM9", 'align': "left"});
					inputField = cTag('input', {'required': "required", 'maxlength': 50, 'type': "email", class: "form-control", name: "invcustomeremail", id: "invcustomeremail", 'value': document.getElementById("customeremail").innerHTML});
                emailField.appendChild(inputField);
			formGroup.appendChild(emailField);
		formhtml.appendChild(formGroup);

		const title = Translate('Please give CHANGE of');
		const actionbutton = Translate('Complete');

        let print_type;

		popup_dialog600(title, formhtml, actionbutton, function(hidePopup) {
			let print_typeselect = 0;
			let print_typeid = document.getElementsByName("print_type");
			print_type = '';
			if(print_typeid.length>0){
				for(let l=0; l<print_typeid.length; l++){
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
			
            confirmOrderCompletion(print_type,hidePopup);
		});		
		
		document.querySelectorAll(".print_type").forEach(oneRadioObj=>{
			oneRadioObj.addEventListener('click', e => {
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
		});
		
		setTimeout(function() {
			document.querySelectorAll(".invcustomeremail").forEach(oneFieldObj=>{
                if(oneFieldObj.style.display !== 'none'){
                    oneFieldObj.style.display = 'none';
                }
			});
			print_type = document.querySelector("#default_invoice_printer").value;
			if(print_type==='Large'){
				document.querySelector("#default_invoice_printer1").checked = true;
			}
			else if(print_type==='Small'){
				document.querySelector("#default_invoice_printer2").checked = true;
			}
			else if(print_type==='Email'){
				document.querySelector("#default_invoice_printer3").checked = true;
				document.querySelectorAll(".invcustomeremail").forEach(oneFieldObj=>{
                    if(oneFieldObj.style.display === 'none'){
                        oneFieldObj.style.display = '';
                    }
				});
			}
			else{
				document.querySelector("#default_invoice_printer4").checked = true;
			}
			document.getElementById("exchangemethod").focus();
		}, 500);
		
		return false;
	}
}

async function confirmOrderCompletion(print_type,hidePopup){
	const pos_id = document.getElementById("pos_id").value;
	const completed = document.getElementById("completed").value;
	const changemethod = document.getElementById("changemethod").value;
	const amount_due = document.getElementById("amount_due").value;
	const invoice_no = document.getElementById("invoice_no").value;
	const email = document.getElementById("invcustomeremail").value;
	if(print_type==='Email' && !emailcheck(email)){
		document.getElementById("invcustomeremail").focus();
		return false;
	}
	const saveBtn = document.querySelector(".btnmodel");
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;

	const jsonData = {};
	jsonData['pos_id'] = pos_id;
	jsonData['completed'] = completed;
	jsonData['order_status'] = 2;
	jsonData['changemethod'] = changemethod;
	jsonData['amount_due'] = amount_due;

    const url = "/Orders/completeOrder";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.returnStr ===''){
            hidePopup();
            let redirectURL, printType, redirectTo;
			if(completed>0){
				redirectURL = '/Orders/lists/';
			}
			else{
				redirectURL = '/Orders/lists/cancelled';
			}
			if(print_type !=='')
				printType = print_type.toLowerCase();
			else
				printType = 'large';
			
			if(printType === 'large' || printType === 'small'){
				redirectTo = '/Carts/cprints/'+printType+'/'+invoice_no;
				if(amount_due<0){redirectTo = redirectTo+'/'+amount_due;}
				let day = new Date();
				let id = day.getTime();
				let w = 900;
				let h = 600;
				let scrl = 1;
				let winl = (screen.width - w) / 2;
				let wint = (screen.height - h) / 2;
				let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
				window.open(redirectTo, '" + id + "', winprops);
				
				setTimeout(function() {
					window.location = redirectURL;	
				}, 1000);
			}
			else if(printType==='email'){
				if(email !=='' && pos_id>0){
					document.getElementById("pos_id").value = pos_id;
					document.getElementById("email_address").value = email;
					emaildetails(false, '/Carts/AJ_sendposmail');
					setTimeout(function() {
						window.location = redirectURL;	
					}, 1000);
				}
				else{
                    actionBtnClick('.btnmodel', Translate('Complete'), 0);
					showTopMessage('alert_msg', Translate('There is no email address for customer.'));
					return false;
				}
			}
			else{
				window.location = redirectURL;
			}		
		}
		else{
			actionBtnClick('.btnmodel', Translate('Complete'), 0);
			showTopMessage('alert_msg', Translate('Can not complete this order.'));
		}
    }
}

function cancelOrder(){
	let grand_total = parseFloat(document.getElementById("grand_total").value);
	if(grand_total==='' || isNaN(grand_total)){grand_total = 0.00;}
	
	let amount_due = parseFloat(document.getElementById("amount_due").value);
	if(amount_due==='' || isNaN(amount_due)){amount_due = 0.00;}
	const paymentLenth = document.getElementsByName("payment_amount[]").length;
	
	if(amount_due !==grand_total || paymentLenth>0){
		showTopMessage('error_msg', Translate('You can not cancel this order because a payment has been made.'));
		return false;
	}
	
	let shippingqty;
    shippingqty = 0;
	const hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	if(hasdata.length>10){
		const pos_cart_idarray = document.getElementsByName("pos_cart_id[]");
		if(pos_cart_idarray.length>0){
			for(let p=0; p<pos_cart_idarray.length; p++){
				const pos_cart_id = pos_cart_idarray[p].value;
				
				let shipping_qty = parseFloat(document.getElementById("shipping_qty"+pos_cart_id).value);
				if(shipping_qty==='' || isNaN(shipping_qty)){shipping_qty = 0;}
				if(shipping_qty>0){
					shippingqty++;
				}
			}
		}
	}
	
	if(shippingqty>0){
		showTopMessage('error_msg', shippingqty+' '+Translate('Product(s) has been shipped for this you can not cancel this order.'));
		return false;
	}
	
	confirm_dialog(Translate('Cancel Order'), Translate('Are you sure you want to cancel this order?'), confirmOrderCancelation);
}

async function confirmOrderCancelation(hidePopup){
	const pos_id = document.getElementById("pos_id").value;
	const changemethod = document.getElementById("changemethod").value;

	const jsonData = {};
	jsonData['pos_id'] = pos_id;
	jsonData['completed'] = 0;
	jsonData['order_status'] = 0;
	jsonData['changemethod'] = changemethod;

    const url = "/Orders/completeOrder";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.returnStr ===''){
			window.location = '/Orders/lists/cancelled';			
			hidePopup();
		}
		else{
            let pTag = document.querySelector( "#popup p");
            pTag.setAttribute('style', "color: #F00;")
            pTag.innerHTML = data.returnStr;
            if(document.querySelector('[text="Confirm"]')) document.querySelector('[text="Confirm"]').style.display = 'none';
		}
    }
}

async function AJsave_orderStatus(){
	const pos_id = document.getElementById("pos_id").value;
	const currentstatus = document.getElementById("order_status").value;
	const oldstatus = document.getElementById("oldorder_status").value;
	
	if(currentstatus !== oldstatus){
		const orderStatus = document.getElementById("order_status");        
		orderStatus.style.background = orderStatus.querySelector('option:checked').style.background;
		orderStatus.style.color = orderStatus.querySelector('option:checked').style.color;

		orderStatus.classList.add('lightYellow');
		orderStatus.disabled = true;
	
		const jsonData = {};
		jsonData['pos_id'] = pos_id;
		jsonData['status'] = currentstatus;
		jsonData['oldstatus'] = oldstatus;
	
		const url = "/Orders/AJsave_orderStatus";
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.action ==='Changed'){
				// const orderStatusOpts = orderStatus.querySelectorAll("option");
				// orderStatus.innerHTML = '';
				// orderStatusOpts.forEach(function(item) {
				// 	if(item.innerText !=='Quotes'){
				// 		const order_statusOptions = cTag('option',{'value':item.innerText,'style':item.getAttribute('style')});
				// 		order_statusOptions.innerHTML = item.innerText;
				// 		orderStatus.appendChild(order_statusOptions);
				// 	}
				// });

				// orderStatus.value = currentstatus;
				
				// document.getElementById("oldorder_status").value = currentstatus;
				if(oldstatus==='Quotes' && data.cartsData.length>0){
                    loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
				}
				cartsAutoFuncCall();
				filter_Orders_edit();
				orderStatus.classList.remove('lightYellow');
				orderStatus.classList.remove('lightRed');
				orderStatus.disabled = false;
			}
			else{
				showTopMessage('error_msg', Translate('Error occured while save this status.'));
				orderStatus.classList.remove('lightYellow');
				orderStatus.classList.add('lightRed');
				orderStatus.disabled = false;
			}
        }
	}
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, add, edit};
    layoutFunctions[segment2]();
    
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));   
});