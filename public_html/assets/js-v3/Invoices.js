import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, round, DBDateToViewDate, noPermissionWarning, 
    alertmessage, preventDot, printbyurl, setSelectOpt, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, 
    checkAndSetSessionData, popup_dialog, popup_dialog600, setCDinCookie, applySanitizer, togglePaymentButton, activeLoader,
    fetchData, listenToEnterKey, addCustomeEventListener, actionBtnClick, serialize, multiSelectAction, onClickPagination, 
    customAutoComplete, historyTable, activityFieldAttributes, calculate, emailcheck, controllNumericField, validateRequiredField
} from './common.js';

import {calculateTax, emaildetails, cancelemailform, emailthispage, checkMethod} from './cart.js';
import {smsInvoice} from './BulkSMS.js';

if(segment2 === ''){segment2 = 'lists'}

let listsFieldAttributes = [
    {'nowrap':'nowrap', 'datatitle':Translate('Date'), 'align':'center'},
    {'nowrap':'nowrap', 'datatitle':Translate('Time'), 'align':'right'},
    {'nowrap':'nowrap', 'datatitle':Translate('Invoice')+'#', 'align':'center'},
    {'datatitle':Translate('Customer Name'), 'align':'left'},
    {'datatitle':Translate('Sales Person'), 'align':'left'},
    {'nowrap':'nowrap', 'datatitle':Translate('Taxable'), 'align':'right'},
    {'nowrap':'nowrap', 'datatitle':Translate('Taxes'), 'align':'right'},
    {'nowrap':'nowrap', 'datatitle':Translate('Non Taxable'), 'align':'right'},
    {'nowrap':'nowrap', 'datatitle':Translate('Total'), 'align':'right'}
];

const uriStr = segment1+'/view';

async function filter_Invoices_lists(){
    let page = 1;    
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['sinvoice_type'] = document.getElementById("sinvoice_type").value;
	const semployee_id = document.getElementById("semployee_id").value;
	jsonData['semployee_id'] = semployee_id;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetPage/filter';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        setSelectOpt('semployee_id', 0, Translate('All Sales People'), data.empIdOpt, 1, Object.keys(data.empIdOpt).length);
        setTableRowsInvoices(data.tableRows, listsFieldAttributes, uriStr, [5,6,7,8]);
        document.getElementById("totalTableRows").value = data.totalRows;
        document.getElementById("semployee_id").value = semployee_id;
        onClickPagination();
    }
}

async function loadTableRows_Invoices_lists(){
	const jsonData = {};
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['sinvoice_type'] = document.getElementById("sinvoice_type").value;
	jsonData['semployee_id'] = document.getElementById("semployee_id").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;	
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        setTableRowsInvoices(data.tableRows, listsFieldAttributes, uriStr, [5,6,7,8])
        onClickPagination();
    }
}

function setTableRowsInvoices(tableData, attributes, uriStr, currencyAdd = [], dateFormatAdd = []){
    let tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//======Create TBody TR Column======//
	let tr, tdCol;
	if(tableData.length){
        let dateTimeArray, date, time, oneTDObj;
		tableData.forEach(oneRow => {
			let i=0;
			tr = cTag('tr');
            
            dateTimeArray = DBDateToViewDate(oneRow[1], 1, 1);
            date = dateTimeArray[0];
            tdCol = cTag('td');
            oneTDObj = attributes[0];
            for(const [key, value] of Object.entries(oneTDObj)) {
                let attName = key;
                if(attName !=='' && attName==='datatitle')
                    attName = attName.replace('datatitle', 'data-title');
                tdCol.setAttribute(attName, value);
            }
            tdCol.innerHTML = date;
            tr.appendChild(tdCol);

            time = dateTimeArray[1];
            tdCol = cTag('td');
            oneTDObj = attributes[1];
            for(const [key, value] of Object.entries(oneTDObj)) {
                let attName = key;
                if(attName !=='' && attName==='datatitle')
                    attName = attName.replace('datatitle', 'data-title');
                tdCol.setAttribute(attName, value);
            }
            tdCol.innerHTML = time;
            tr.appendChild(tdCol);

			oneRow.forEach(tdvalue => {
				if(i>1){
					let idVal = oneRow[0];
					tdCol = cTag('td');
					let oneTDObj = attributes[i];
                    for(const [key, value] of Object.entries(oneTDObj)) {
						let attName = key;
						if(attName !=='' && attName==='datatitle')
							attName = attName.replace('datatitle', 'data-title');
                        tdCol.setAttribute(attName, value);
					}
					if(isNaN(parseFloat(tdvalue)) && tdvalue.includes("<a ") || uriStr===''){
						tdCol.innerHTML = tdvalue;
					}
					else{
						let aTag = cTag('a',{ 'class': 'anchorfulllink','href': '/'+uriStr+'/'+idVal });
						
						if(currencyAdd.length>0 && currencyAdd.indexOf(i) !== -1){
							if(tdvalue.slice && tdvalue.slice(-1) === '%') aTag.innerHTML = tdvalue;
							else aTag.innerHTML = addCurrency(tdvalue);
						}
						else if(dateFormatAdd.length>0 && dateFormatAdd.indexOf(i) !== -1){
							aTag.innerHTML = DBDateToViewDate(tdvalue, 0, 1);
						}
						else{
							aTag.innerHTML=tdvalue;
						}
						tdCol.appendChild(aTag);
					}
					tr.appendChild(tdCol);
				}
				i++;
			});
			tbody.appendChild(tr);
		});
	}
	/* else{
		//No_Invoices_meet
		let colspan = attributes.length;
		tr = cTag('tr');
			tdCol = cTag('td', {colspan:colspan, 'style': "color: #F00; font-size: 16px;"});
			tdCol.innerHTML = noDataMeet
		tr.appendChild(tdCol);
		tbody.appendChild(tr);
	} */
	tbody.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item))
}

async function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

     //=====Hidden Fields for Pagination======//
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: 31 },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
            let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });
        let options, sortDropDown;
        const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
                const headerTitle = cTag('h2');
                headerTitle.innerHTML = Translate('Sales Invoices')+' ';
                    const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('Sales Invoices')});
                headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
            const salesRegisterLink = cTag('a', {'href': "/POS", class: "btn defaultButton", title: Translate('Sales Register')});
            salesRegisterLink.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Sales Register'));
        titleRow.appendChild(salesRegisterLink);
    showTableData.appendChild(titleRow);

        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectSorting = cTag('select', { class: "form-control", name: "sorting_type", id: "sorting_type"});
                selectSorting.addEventListener('change', filter_Invoices_lists);
                    options = {
                        '0':Translate('Date')+', '+Translate('Invoice No.'), 
                        '1':Translate('Date'), 
                        '2':Translate('Invoice No.')
                    };                    
                    for(const [key, value] of Object.entries(options)) {
                        let listOption = cTag('option', {'value': key});
                        listOption.innerHTML = value;
                        selectSorting.appendChild(listOption);
                    }
            sortDropDown.appendChild(selectSorting);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectInvoice = cTag('select', { class: "form-control", name: "sinvoice_type", id: "sinvoice_type"});
                selectInvoice.addEventListener('change', filter_Invoices_lists);
                    options = {'':Translate('All Types'), 'Refund':Translate('Refunds'), 'Unpaid':Translate('Unpaid')};
                    for(const [key, value] of Object.entries(options)) {
                        let invoiceOption = cTag('option', {'value': key});
                        invoiceOption.innerHTML = value;
                        selectInvoice.appendChild(invoiceOption);
                    }
            sortDropDown.appendChild(selectInvoice);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectEmployee = cTag('select', { class: "form-control", name: "semployee_id", id: "semployee_id"});
                selectEmployee.addEventListener('change', filter_Invoices_lists);
                    let employeeOption = cTag('option', {'value': 0});
                    employeeOption.innerHTML = Translate('All Sales People');
                selectEmployee.appendChild(employeeOption);
            sortDropDown.appendChild(selectEmployee);
        filterRow.appendChild(sortDropDown);

            const searchDiv = cTag('div', {class: "columnXS6 columnSM3"});
                const SearchInGroup = cTag('div', {class: "input-group"});
                    const searchField = cTag('input', {keydown: listenToEnterKey(filter_Invoices_lists), class: "form-control", 'type': "text", 'placeholder': Translate('Search Customer or Invoice'), 'value': "", id: "keyword_search", name: "keyword_search", 'maxlength':50});
                SearchInGroup.appendChild(searchField);
                    let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle':"tooltip", 'data-placement': "bottom", 'data-original-title': Translate('Search Customer or Invoice')});
                    searchSpan.addEventListener('click', filter_Invoices_lists);
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
                            const columnNames = listsFieldAttributes.map(colObj=>(colObj.datatitle));
                            const listHeadRow = cTag('tr',{class:'outerListsTable'});
                                const thCol0 = cTag('th', {'style': "width: 80px;"});
                                thCol0.innerHTML = columnNames[0];

                                const thCol1 = cTag('th', {'style': "width: 75px;"});
                                thCol1.innerHTML = columnNames[1];

                                const thCol2 = cTag('th', {'style': "width: 70px;"});
                                thCol2.innerHTML = columnNames[2];

                                const thCol3 = cTag('th');
                                thCol3.innerHTML = columnNames[3];

                                const thCol4 = cTag('th', {'width': "15%"});
                                thCol4.innerHTML = columnNames[4];

                                const thCol5 = cTag('th', {'width': "10%", 'style': "text-align: right;"});
                                thCol5.innerHTML = columnNames[5];

                                const thCol6 = cTag('th', {'width': "8%", 'style': "text-align: right;"});
                                thCol6.innerHTML = columnNames[6];

                                const thCol7 = cTag('th', {'width': "10%", 'style': "text-align: right;"});
                                thCol7.innerHTML = columnNames[7];

                                const thCol8 = cTag('th', {'width': "8%", 'style': "text-align: right;"});
                                thCol8.innerHTML = columnNames[8];
                            listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8);
                        listHead.appendChild(listHeadRow);
                    listTable.appendChild(listHead);

                        const listBody = cTag('tbody', {id: "tableRows"});
                    listTable.appendChild(listBody);
                divNoMore.appendChild(listTable);
            divTable.appendChild(divNoMore);
    showTableData.appendChild(divTable);
    addPaginationRowFlex(showTableData);
    
    //======sessionStorage =======//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
   
    const sorting_type = '0', sinvoice_type = '', semployee_id = '0';
    
    checkAndSetSessionData('sorting_type', sorting_type, list_filters);
    checkAndSetSessionData('sinvoice_type', sinvoice_type, list_filters);
    checkAndSetSessionData('semployee_id', semployee_id, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    addCustomeEventListener('filter',filter_Invoices_lists);
    addCustomeEventListener('loadTable',loadTableRows_Invoices_lists);
    filter_Invoices_lists(true);
}

async function checkInvoiceMethod(){
	const pos_id = document.getElementById("pos_id").value;
    const jsonData = {};
	jsonData['pos_id'] = pos_id;

    const url = '/Invoices/showpaymentlist';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.paymentData.length){
            const addInvoicesPaymentList = document.getElementById("addInvoicesPaymentList");
            addInvoicesPaymentList.innerHTML = '';
            let td,b;
            
            data.paymentData.forEach(item=>{
                let trusticon = ' ';
                if(item[4]>0){
                    trusticon = cTag('i',{ 'class':`fa fa-trash-o`,'click': ()=> removeInvoicePayment(item[0]),'style':`cursor:pointer;` })
                }

                    const tr = cTag('tr');
                        td = cTag('td',{ 'colspan':`2` });
                        td.innerHTML = ' ';
                    tr.appendChild(td);
                        td = cTag('td',{ 'align':`right` });
                            const label = cTag('label');
                            label.innerHTML = DBDateToViewDate(item[1]);
                        td.appendChild(label);
                    tr.appendChild(td);
                        td = cTag('td',{ 'align':`right` });
                            b = cTag('b');
                            b.innerHTML = item[2];
                        td.appendChild(b);
                        td.append(' :');
                    tr.appendChild(td);
                        td = cTag('td',{ 'align':`right` });
                            b = cTag('b');
                            b.append(addCurrency(item[3]),' ', trusticon);
                        td.appendChild(b);
                        td.appendChild(cTag('input',{ 'type':`hidden`,'name':`payment_amount[]`,'value':item[3] }));
                    tr.appendChild(td);
                addInvoicesPaymentList.appendChild(tr);
            });
        }
        else{
            document.getElementById("addInvoicesPaymentList").innerHTML = '<tr><td class="nodata" colspan="5"></td></tr>';
        }
        checkInvoiceTotal();
    }
}

async function filter_Invoices_view(){
    let page = 1;
	document.getElementById("page").value = page;

	const jsonData = {};
	jsonData['spos_id'] = document.getElementById("spos_id").value;
	jsonData['shistory_type'] = document.getElementById("shistory_type").value;
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

async function loadTableRows_Invoices_view(){
	const jsonData = {};
	jsonData['spos_id'] =document.getElementById("spos_id").value;
	jsonData['shistory_type'] =document.getElementById("shistory_type").value;
	jsonData['totalRows'] =document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] =document.getElementById("rowHeight").value;
	jsonData['limit'] =checkAndSetLimit();
	jsonData['page'] =document.getElementById("page").value;

    const url = '/'+segment1+'/AJgetHPage';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        setTableHRows(data.tableRows, activityFieldAttributes);
        onClickPagination();
    }
}

async function addInvoicesPayment(){
	const pos_id = document.getElementById("pos_id").value;
	const method = document.getElementById("method").value;

	const errorid = document.getElementById("error_amount");
	errorid.innerHTML = '';

    //drawer validation
    let multiple_cash_drawers = parseInt(document.getElementById("multiple_cash_drawers").value);
	let drawer = document.getElementById("drawer").value;
    if(multiple_cash_drawers===1 && drawer===''){
		errorid.innerHTML = Translate('Missing drawer');
		return false;
	}

    const amountField =  document.getElementById("amount");
    if(!validateRequiredField(amountField,'#error_amount') && !amountField.valid()) return;

	let amount = parseFloat(amountField.value);
	if(amount==='' || isNaN(amount)){amount = 0.00;}
	
	let amount_due = parseFloat(document.getElementById("amount_due").value);
	if(amount_due==='' || isNaN(amount_due)){amount_due = 0.00;}
   
	if(amount===0){
		errorid.innerHTML = 'Amount should > 0';
		return false;
	}
	else if(amount>amount_due){
		errorid.innerHTML = 'Amount should <= Amount Due';
		return false;
	}
	else{
        const saveBtn = document.getElementById("btnPayment");
        saveBtn.value = Translate('Save')+'...';
        saveBtn.disabled = true;
        
        const jsonData = {};
        jsonData['pos_id'] = pos_id;
        jsonData['payment_method'] = method;
        jsonData['payment_amount'] = amount;
        jsonData['amount_due'] = amount_due;
        jsonData['drawer'] = drawer;
        
        const url = '/'+segment1+'/addInvoicesPayment';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.returnStr>0){
				checkInvoiceMethod();
			}
			else{
                showTopMessage('alert_msg', Translate('Could not save payment.'));
			}			
            saveBtn.value = '+ '+Translate('Payment');
            saveBtn.disabled = false;
        }
	}
}

async function removeInvoicePayment(pos_payment_id) {
	const pos_id = document.getElementById("pos_id").value;
	
    const jsonData = {};
    jsonData['pos_id'] = pos_id;
	jsonData['pos_payment_id'] = pos_payment_id;

    const url = '/'+segment1+'/removeInvoicePayment';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr>0){
            checkInvoiceMethod();
            filter_Invoices_view();
        }
        else{
            showTopMessage('alert_msg', Translate('Could not remove payment.'));
        }
    }
}

async function updateCartMobileAveCost(){	
    const jsonData = {};

    const spos_id = document.getElementById("spos_id").value;
	const sales_datetime = document.getElementById("sales_datetime").value;

    jsonData['sales_datetime'] = sales_datetime;
	jsonData['pos_id'] = spos_id;

    const url = '/'+segment1+'/updateCartMobileAveCost';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        location.reload();
    }
}

async function AJrefund_Invoices(invoice_no){
    const jsonData = {};
	jsonData['invoice_no'] = invoice_no;

    const url = '/Invoices/AJrefund_Invoices';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr===''){
			window.location = '/Invoices/refund/';
		}
		else{
            if(data.returnStr==='noRefundMeet') showTopMessage('alert_msg', Translate('No refund meet the criteria given'));
            else if(data.returnStr==='noProductMeet') showTopMessage('alert_msg', Translate('No product meet the criteria given'));  
		}
    }
}

function checkInvoiceTotal(){
    const refundBtn = document.getElementById('refundBtn');
    let grand_total;
	grand_total = parseFloat(document.getElementById("grand_total").value);
	if(grand_total==='' || isNaN(grand_total)){grand_total = 0;}
	
	const listcount = document.querySelector("#addInvoicesPaymentList").querySelector('.nodata');
    if(!listcount){
        let totalPaid = 0;
		const payment_amountarray = document.getElementsByName("payment_amount[]");
        for(let m = 0; m < payment_amountarray.length; m++) {
			let payment_amount = parseFloat(payment_amountarray[m].value);
			if(payment_amount==='' || isNaN(payment_amount)){payment_amount =0.00;}
			totalPaid = calculate('add',totalPaid,payment_amount,2);
		}
		grand_total = calculate('sub',grand_total,totalPaid,2);
	}
	
	if(grand_total>0){
        document.querySelectorAll(".duerow").forEach(oneField=>{
            if(oneField.style.display === 'none'){
                oneField.style.display = '';
            }
        });
		document.getElementById("amount").value = grand_total;
		document.getElementById("amountDueStr").innerHTML = addCurrency(grand_total);
		document.getElementById("amount_due").value = grand_total;
        if(refundBtn){
            refundBtn.style.display = 'none';
        }
	}
	else{
        if(refundBtn) refundBtn.style.display = '';
        document.querySelectorAll(".duerow").forEach(oneField=>{
            if(oneField.style.display !== 'none'){
                oneField.style.display = 'none';
            }
        });
	}
}

async function view(){
    const jsonData = {'invoice_no':segment3};
    const url = '/'+segment1+'/AJ_view_MoreInfo';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let strong, sendEmailHeadRow, tdCol, viewTableHead, viewHeadRow, thCol, viewTableBody, bTag;
        const Dashboard = document.getElementById('viewPageInfo');
        Dashboard.innerHTML = '';
            const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const titleColumn = cTag('div',{ 'class':`columnSM6` });
                    const titleHeader = cTag('h2',{ 'style': "text-align: start;"});
                    titleHeader.append(`${Translate('View Invoice')} - s${segment3} `);
                    titleHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':`${Translate('View Invoice')} - s${segment3}` }));
                titleColumn.appendChild(titleHeader);
            titleRow.appendChild(titleColumn);
                const buttonNames = cTag('div',{ 'class':`columnSM6`, 'style': "text-align: end;" });
                    let salesInvoiceLink = cTag('a',{ 'class':`btn defaultButton`,'href':`/Invoices/lists`,'title':Translate('Sales Invoices') });
                    salesInvoiceLink.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Sales Invoices'));
                buttonNames.appendChild(salesInvoiceLink);

                    let printButton = cTag('div',{ 'class':`printBtnDropDown`, id: `invoicePrint` });
                        let printButtonTitle = cTag('button',{ 'type':`button`,'class':`btn printButton dropdown-toggle`, 'style': "margin-left: 10px; padding-bottom: 10px;", 'data-toggle':`dropdown`,'aria-haspopup':`true`,'aria-expanded':`false` });
                            let printIcon = cTag('span');
                            printIcon.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                            if(OS =='unknown'){
                                printIcon.append(' '+Translate('Print')+' ');
                            }
                            printIcon.append('\u2000', cTag('span',{ 'class':`caret`, 'style': "color: #000;" }));
                        printButtonTitle.appendChild(printIcon);
                            let toggleSpan = cTag('span',{ 'class':`sr-only` });
                            toggleSpan.innerHTML = Translate('Toggle Dropdown');
                        printButtonTitle.appendChild(toggleSpan);
                    printButton.appendChild(printButtonTitle);
                        let ulDropDown = cTag('ul',{ 'class':`dropdown-menu` });
                            let liFullPrint = cTag('li');
                                let fullPagePrint = cTag('a',{ 'href':`javascript:void(0);`,'title':Translate('Full Page Printer') });
                                fullPagePrint.addEventListener('click',()=>printbyurl(`/Carts/cprints/large/${data.invoice_no}/${document.getElementById('amount_due').value}`))
                                fullPagePrint.innerHTML = Translate('Full Page Printer');
                            liFullPrint.appendChild(fullPagePrint);
                        ulDropDown.appendChild(liFullPrint);
                        ulDropDown.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
                            let liThermalPrint = cTag('li');
                                let thermalPrint = cTag('a',{ 'href':`javascript:void(0);`,'title':Translate('Thermal Printer') });
                                thermalPrint.addEventListener('click',()=>printbyurl(`/Carts/cprints/small/${data.invoice_no}/${document.getElementById('amount_due').value}`))
                                thermalPrint.innerHTML = Translate('Thermal Printer');
                            liThermalPrint.appendChild(thermalPrint);
                        ulDropDown.appendChild(liThermalPrint);
                        ulDropDown.appendChild(cTag('li',{ 'role':`separator`,'class':`divider` }));
                            let liEmail = cTag('li');
                                let emailPrint = cTag('a',{ 'href':`javascript:void(0)`,'click': emailthispage,'title':Translate('Email Invoice') });
                                emailPrint.innerHTML = Translate('Email Invoice');
                            liEmail.appendChild(emailPrint);
                        ulDropDown.appendChild(liEmail);
                            let liSMS = cTag('li');
                                let smsPrint = cTag('a',{ 'href':`javascript:void(0)`,'click': smsInvoice,'title':Translate('SMS Invoice') });
                                smsPrint.innerHTML = Translate('SMS Invoice');
                            liSMS.appendChild(smsPrint);
                        ulDropDown.appendChild(liSMS);
                    printButton.appendChild(ulDropDown);
                buttonNames.appendChild(printButton);

                    let emailFlexRow = cTag('div',{ 'class': `flexEndRow`,'style':'width:100%' });
                        let emailMargin = cTag('div',{ 'style': `margin-top: 10px;` });
                            const sendEmailForm = cTag('form',{ 'method':`post`,'name':`sendEmail`,'enctype':`multipart/form-data`,'action':`#`,'submit': (event)=> emaildetails(event, '/Carts/AJ_sendposmail') });
                                const sendEmailTable = cTag('table',{ 'align':`center`,'width':`100%`,'border':`0`,'cellspacing':`0`,'cellpadding':`10` });
                                    const sendEmailBody = cTag('tbody');
                                        sendEmailHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'colspan':`2` });
                                            tdCol.appendChild(cTag('div',{ 'id':`showerrormessage` }));
                                            tdCol.appendChild(cTag('div',{ 'id':`showsuccessmessage` }));
                                        sendEmailHeadRow.appendChild(tdCol);
                                    sendEmailBody.appendChild(sendEmailHeadRow);
                                        sendEmailHeadRow = cTag('tr',{ 'class':`emailform`,style:'display:none'});
                                            tdCol = cTag('td');
                                                const emailInput = cTag('input',{ 'type':`email`,'required':``,'name':`email_address`,'id':`email_address`,'class':`form-control`,'value':data.customeremail,'maxlength':`50` });
                                                emailInput.addEventListener('keydown',event=>{
                                                    if(event.which===13) emaildetails(event, '/Carts/AJ_sendposmail')
                                                })
                                            tdCol.appendChild(emailInput);
                                        sendEmailHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'width':`150`,'align':`right`,'valign':`middle`,'nowrap':`` });
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'id':`pos_id`,'name':`pos_id`,'value':data.pos_id }));
                                                const sendEmail = cTag('input',{ 'type':`submit`,'class':`btn completeButton sendbtn` , 'style': "margin-right: 5px;", 'value':` ${Translate('Email')} ` });
                                                sendEmail.addEventListener('click',event=>emaildetails(event, '/Carts/AJ_sendposmail'));
                                            tdCol.appendChild(sendEmail);
                                            tdCol.appendChild(cTag('input',{ 'type':`button`,'class':`btn defaultButton`,'click': cancelemailform,'value':` ${Translate('Cancel')} ` }));
                                        sendEmailHeadRow.appendChild(tdCol);
                                    sendEmailBody.appendChild(sendEmailHeadRow);
                                sendEmailTable.appendChild(sendEmailBody);
                            sendEmailForm.appendChild(sendEmailTable);
                        emailMargin.appendChild(sendEmailTable);
                    emailFlexRow.appendChild(emailMargin);
                buttonNames.appendChild(emailFlexRow);
            titleRow.appendChild(buttonNames);
        Dashboard.appendChild(titleRow);

            const invoiceContainerRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const customerInfoColumn = cTag('div',{ 'class':`columnSM6 cardContainer` });
                    const customerInfoHead = cTag('div',{ 'class':`flex cardHeader` });
                    customerInfoHead.appendChild(cTag('i',{ 'class':`fa fa-user`, 'style': "margin: 12px;" }));
                        const customerInfoHeader = cTag('h3');
                        customerInfoHeader.innerHTML = Translate('Customer info');
                    customerInfoHead.appendChild(customerInfoHeader);
                customerInfoColumn.appendChild(customerInfoHead);
                    const customerContent = cTag('div',{ 'class':`cardContent customInfoGrid`,'id':`customer_information` });
                        const customerLabel = cTag('label');
                        customerLabel.innerHTML = Translate('Customer')+': ';
                        let customerLink = cTag('a',{ 'style': "color: #009; text-decoration: underline; border-bottom: 1px solid #CCC; margin-bottom: 5px; padding-bottom: 5px;", 'href':`/Customers/view/${data.customer_id}`,'title':Translate('View Customer Details') });
                        customerLink.append(data.customername+' ');
                        customerLink.appendChild(cTag('i',{ 'class':`fa fa-link` }));
                    customerContent.append(customerLabel, customerLink);
                        let emailLabel = cTag('label');
                        emailLabel.innerHTML = Translate('Email')+': ';
                        let emailSpan = cTag('span');
                        emailSpan.innerHTML = data.customeremail;
                    customerContent.append(emailLabel, emailSpan);

                        let phoneLabel = cTag('label');
                        phoneLabel.innerHTML = Translate('Phone No.')+': ';
                        let liPhone = cTag('span');
                        liPhone.innerHTML = data.customerphone;
                    customerContent.append(phoneLabel, liPhone);
                customerInfoColumn.appendChild(customerContent);
            invoiceContainerRow.appendChild(customerInfoColumn);

                const orderInfoColumn = cTag('div',{ 'class':`columnSM6 cardContainer` });
                    const orderInfoHeader = cTag('div',{ 'class':`flex cardHeader` });
                    orderInfoHeader.appendChild(cTag('i',{ 'class':`fa fa-mobile`, 'style': "margin: 12px;" }));
                        const orderInfoTitle = cTag('h3');
                        orderInfoTitle.innerHTML = Translate('Order Info');
                    orderInfoHeader.appendChild(orderInfoTitle);
                orderInfoColumn.appendChild(orderInfoHeader);
                    let orderContent = cTag('div',{ 'class':`cardContent customInfoGrid`,'id':`order_info` });
                        if(data.pos_type==='Repairs'){
                            const ticketLabel = cTag('label');
                            ticketLabel.innerHTML = Translate('Ticket No.');
                            let repairLink = cTag('a',{ 'style': "color: #009; text-decoration: underline;", 'href':`/Repairs/edit/${data.repairs_id}`,'title':Translate('View Ticket Details') });
                            repairLink.append(`t${data.ticket_no} `);
                            repairLink.appendChild(cTag('i',{ 'class':`fa fa-link` }));
                            orderContent.append(ticketLabel, repairLink);
                        }
                        else{
                            const invoiceLabel = cTag('label');
                            invoiceLabel.innerHTML = Translate('Invoice No.');
                            let invoiceValue = cTag('span');
                            invoiceValue.innerHTML = 's'+data.invoice_no;
                            orderContent.append(invoiceLabel, invoiceValue);
                        }

                        const salesPersonLabel = cTag('label');
                        salesPersonLabel.innerHTML = Translate('Sales Person')+': ';
                        let salesManName = cTag('span',{ 'id':`salesman_namestr` });
                        salesManName.innerHTML = data.salesPersonName;
                    orderContent.append(salesPersonLabel, salesManName);

                        let dateLabel = cTag('label');
                        dateLabel.innerHTML = Translate('Date')+': ';
                        let liDateSpan = cTag('span',{ 'id':`salesman_namestr` });
                        liDateSpan.innerHTML = DBDateToViewDate(data.sales_datetime, 1, 1)[0];
                    orderContent.append(dateLabel, liDateSpan);
                orderInfoColumn.appendChild(orderContent);
            invoiceContainerRow.appendChild(orderInfoColumn);
        Dashboard.appendChild(invoiceContainerRow);

            const invoiceTableColumn = cTag('div',{ 'class':`columnXS12`, 'style': "position: relative;" });
                const invoiceTableContent = cTag('div',{ 'class':`cartContent`});
                    const errorDiv = cTag('div',{ 'class':`flex` });
                    errorDiv.appendChild(cTag('div',{ 'class':`columnSM12 `, 'style': "margin: 0;", 'id':`errorposdata` }));
                invoiceTableContent.appendChild(errorDiv);
                    const viewTableColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin: 0; padding: 0;" });
                        const viewTable = cTag('table',{ 'class':`table table-bordered` });
                            viewTableHead = cTag('thead');
                                viewHeadRow = cTag('tr');
                                    thCol = cTag('th',{ 'width':`40px`, 'style': "text-align: right;" });
                                    thCol.innerHTML = '#';
                                viewHeadRow.appendChild(thCol);
                                    thCol = cTag('th');
                                    thCol.innerHTML = Translate('Description');
                                viewHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`12%`,'style': "text-align: right;" });
                                    thCol.innerHTML = Translate('Time/Qty');
                                viewHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`12%`, 'style': "text-align: right;" });
                                    thCol.innerHTML = Translate('Unit Price');
                                viewHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`12%`, 'style': "text-align: right;" });
                                    thCol.innerHTML = Translate('Total');
                                viewHeadRow.appendChild(thCol);
                            viewTableHead.appendChild(viewHeadRow);
                        viewTable.appendChild(viewTableHead);
                            viewTableBody = cTag('tbody',{ 'id':`invoice_entry_holder` });
                        if(data.cartData.length>0){
                            data.cartData.forEach((item,indx)=>{
                                    viewHeadRow = cTag('tr',{ 'class':`orderrow${item.pos_cart_id}` });
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = indx+1;
                                    viewHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`left` });
                                            let description = item.description;
                                            let linkedsku = ''; 
                                        if(item.item_type !=='one_time' && item.sku !='' && description.search(`(${item.sku})`)!==-1){
                                            linkedsku = cTag('a',{'href':`/Products/view/${item.product_id}`, 'style': "color: #009; text-decoration: underline;", 'title':Translate('Edit')});
                                            linkedsku.append(`(${item.sku}) `,cTag('i',{'class':"fa fa-link"}));
                                            description = description.replace(`(${item.sku})`,'');
                                        }
                                        let add_description = item.add_description;
                                        if(add_description !==''){
                                            add_description = cTag('p', {style: "margin: 0; padding-left: 10px;"});
                                            add_description.innerHTML = item.add_description;
                                        }
                                        let imei_info = '';
                                        if(item.item_type==='cellphones'){
                                            imei_info = cTag('p', {style: "margin: 0; padding-left: 10px;"});
                                            let l = 0;
                                            item.newimei_info.forEach(info=>{
                                                l++;
                                                if(l>1){imei_info.appendChild(cTag('br'));}
                                                description = description.replace(info.item_number,'');
                                                    const imeiViewLink = cTag('a',{ 'href':`/IMEI/view/${info.item_number}`, 'style': "color: #009; text-decoration: underline;", 'title':Translate('View IMEI details')});
                                                    imeiViewLink.append(info.item_number,' ',cTag('i',{'class':'fa fa-link'}));
                                                imei_info.appendChild(imeiViewLink);
                                                if(info.carrier_name !==''){
                                                    imei_info.append(' '+info.carrier_name);
                                                }
                                                if(info.sale_or_refund===0){
                                                    imei_info.append(' (Refund)');
                                                }
                                                if(info.return_pos_cart_id >0 ){
                                                    const refundedSpan = cTag('span',{'class':"bgblack", 'style': "display: inline-block; margin: 0 0 3px 10px; padding: 5px; color: white;"});
                                                    refundedSpan.innerHTML = Translate('Refunded');
                                                    imei_info.appendChild(refundedSpan);
                                                }
                                            })
                                            tdCol.append(description,linkedsku,add_description,imei_info);
                                        }
                                        else if(item.item_type==='product'){
                                            tdCol.append(description,linkedsku,add_description);
                                            item.newimei_info.forEach(info=>{
                                                // tdCol.innerHTML += `<p>${info}</p>`;
                                                let pTag = cTag ('p',{style: "margin: 0; padding-left: 10px;"});
                                                pTag.innerHTML = info;
                                                tdCol.appendChild(pTag);
                                            })
                                        }
                                        else{
                                            tdCol.append(description, add_description); 
                                        }
                                    viewHeadRow.appendChild(tdCol);

                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = `${item.shipping_qty} `;
                                        if(item.return_qty>0){
                                            const qtySpan = cTag('span',{'class':"bgblack", 'style': "margin-left: 15px; padding: 5px; color: white;"});
                                            qtySpan.innerHTML = `-${item.return_qty}`
                                            tdCol.appendChild(qtySpan);
                                        }
                                    viewHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = addCurrency(item.sales_price);
                                    viewHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'id':`totalstr${item.pos_cart_id}`,'align':`right` });
                                        tdCol.innerHTML = addCurrency(calculate('mul',item.sales_price,item.shipping_qty,2));
                                        if(item.discount_value>0){
                                            tdCol.append(cTag('br'),`-${addCurrency(item.discount_value)}`);
                                        }
                                        else if(item.discount_value<0){
                                            tdCol.append(cTag('br'),`${addCurrency(item.discount_value*(-1))}`);
                                        }
                                    viewHeadRow.appendChild(tdCol);
                                viewTableBody.appendChild(viewHeadRow);
                            });
                        }
                        viewTable.appendChild(viewTableBody);

                            viewTableHead = cTag('thead');
                            if(data.taxes_name1 !==''){
                                    viewHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`4`, 'style': "text-align: right;" });
                                            const taxableLabel = cTag('label');
                                            taxableLabel.innerHTML = Translate('Taxable Total')+' :';
                                        tdCol.appendChild(taxableLabel);
                                    viewHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'style': "text-align: right;" });
                                            bTag = cTag('b');
                                            bTag.innerHTML = addCurrency(data.taxable_total);
                                        tdCol.appendChild(bTag);
                                    viewHeadRow.appendChild(tdCol);
                                viewTableHead.appendChild(viewHeadRow);
                            }

                            let ti1Str = '';
                            let taxes_total1 = data.taxes_total1;
                            if(data.tax_inclusive1>0) {
                                ti1Str = ' Inclusive';
                                taxes_total1 = 0;
                            }
                            if(data.taxes_name1 !==''){
                                    viewHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                            strong = cTag('strong');
                                            strong.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%${ti1Str}) :`;
                                        tdCol.appendChild(strong);
                                    viewHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                            bTag = cTag('b');
                                            bTag.innerHTML = addCurrency(data.taxes_total1);
                                        tdCol.appendChild(bTag);
                                    viewHeadRow.appendChild(tdCol);
                                viewTableHead.appendChild(viewHeadRow);
                            }
                            let ti2Str = '';
                            let taxes_total2 = data.taxes_total2;
                            if(data.tax_inclusive2>0) {
                                ti2Str = ' Inclusive';
                                taxes_total2 = 0;
                            }
                            if(data.taxes_name2 !==''){
                                    viewHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                            strong = cTag('strong');
                                            strong.innerHTML = `${data.taxes_name2} (${data.taxes_percentage2}%${ti2Str}) :`;
                                        tdCol.appendChild(strong);
                                    viewHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                            bTag = cTag('b');
                                            bTag.innerHTML = addCurrency(data.taxes_total2);
                                        tdCol.appendChild(bTag);
                                    viewHeadRow.appendChild(tdCol);
                                viewTableHead.appendChild(viewHeadRow);
                            }
                            if(data.nontaxable_total !== 0){
                                    viewHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`4`, 'style': "text-align: right;" });
                                            const nonTaxLabel = cTag('label');
                                            nonTaxLabel.innerHTML = Translate('Non Taxable Total')+' :';
                                        tdCol.appendChild(nonTaxLabel);
                                    viewHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'style': "text-align: right;" });
                                            bTag = cTag('b');
                                            bTag.innerHTML = addCurrency(data.nontaxable_total);
                                        tdCol.appendChild(bTag);
                                    viewHeadRow.appendChild(tdCol);
                                viewTableHead.appendChild(viewHeadRow);
                            }
                            const grand_total = data.taxable_total+taxes_total1+taxes_total2+data.nontaxable_total;
                                viewHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Grand Total')+' :';
                                    tdCol.appendChild(strong);
                                viewHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        bTag = cTag('b');
                                        bTag.append(addCurrency(grand_total));
                                        bTag.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos_id`,'id':`pos_id`,'value':data.pos_id }));
                                        bTag.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':grand_total }));
                                    tdCol.appendChild(bTag);
                                viewHeadRow.appendChild(tdCol);
                            viewTableHead.appendChild(viewHeadRow);

                            if(data.is_due>0){
                                viewTable.appendChild(viewTableHead);
                                    viewTableBody = cTag('tbody');
                                        viewHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'colspan':`2`,'id':`checkInvoiceMethod` });
                                            tdCol.innerHTML = ' ';
                                        viewHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'colspan':`3`,'class':`bgblack`, 'style': "font-weight: bold; font-size: 16px;" });
                                            tdCol.append(Translate('Take payment'));
                                        viewHeadRow.appendChild(tdCol);
                                    viewTableBody.appendChild(viewHeadRow);
                                viewTable.appendChild(viewTableBody);
                                    viewTableBody = cTag('tbody',{ 'id':`addInvoicesPaymentList` });
                                        viewHeadRow = cTag('tr');
                                        viewHeadRow.appendChild(cTag('td',{ 'class':`nodata`,'colspan':`5` }));
                                    viewTableBody.appendChild(viewHeadRow);
                                viewTable.appendChild(viewTableBody);
                                    viewTableBody = cTag('tbody',{ 'class':`duerow` });
                                        viewHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'colspan':`4`,'align':'right' });
                                                let paymentDiv = cTag('div',{ 'class':`flexEndRow` });
                                                paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`multiple_cash_drawers`,'id':`multiple_cash_drawers`,'value':data.multiple_cash_drawers }));
                                                if(data.multiple_cash_drawers>0 && data.cashDrawerOptions.length>0){
                                                    let drawerDropDown = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
                                                        let selectDrawer = cTag('select',{ 'class':`form-control`,'name':`drawer`,'id':`drawer`,'change': setCDinCookie });
                                                        selectDrawer.addEventListener('change',togglePaymentButton);
                                                        if(data.drawer===''){
                                                                let drawerOption = cTag('option',{ 'value':`` });
                                                                drawerOption.innerHTML = Translate('Select Drawer');
                                                            selectDrawer.appendChild(drawerOption);
                                                        }
                                                        setOptions(selectDrawer,data.cashDrawerOptions.filter(item=>item!==''),0,0);
                                                        selectDrawer.value = data.drawer;
                                                    drawerDropDown.appendChild(selectDrawer);
                                                    paymentDiv.appendChild(drawerDropDown);
                                                }
                                                else{
                                                    paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`drawer`,'id':`drawer`,'value':`` }));
                                                }
                                                let typeColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
                                                    const typeInGroup = cTag('div',{ 'class':`input-group` });
                                                        const typeSpan = cTag('span',{ 'class':`input-group-addon cursor` });
                                                        typeSpan.innerHTML = Translate('Type');
                                                    typeInGroup.appendChild(typeSpan);
                                                        let selectMethod = cTag('select',{ 'class':`form-control`,'name':`method`,'id':`method` });
                                                        setOptions(selectMethod,data.paymentgetwayarray,0,0); 
                                                    typeInGroup.appendChild(selectMethod);
                                                typeColumn.appendChild(typeInGroup);
                                                paymentDiv.appendChild(typeColumn);
                                                let moneyColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
                                                    let inputGroupAmount = cTag('div',{'class':"input-group"});
                                                        let currencySpan = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Currency'),'class':`input-group-addon cursor`});
                                                        currencySpan.innerHTML = currency;
                                                    inputGroupAmount.appendChild(currencySpan);
                                                        let input_payment = cTag('input',{'type': "text",'data-min':'-9999999.99','data-max':'9999999.99','data-format':'d.dd','value':`0`,'name':`amount`,'id':`amount`,'class':`form-control`, 'style': "text-align: right;" });
                                                        input_payment.addEventListener('keydown',event=>{if(event.which===13) addInvoicesPayment()});
                                                        controllNumericField(input_payment, '#error_amount');
                                                    inputGroupAmount.appendChild(input_payment);
                                                moneyColumn.appendChild(inputGroupAmount);
                                                paymentDiv.appendChild(moneyColumn);
                                            tdCol.appendChild(paymentDiv);
                                            tdCol.appendChild(cTag('span',{ 'id':`error_amount`,'class':`errormsg` }));
                                        viewHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{align:"right"});
                                                let paymentBtn = cTag('button',{ 'class':`btn defaultButton`, 'style': "margin-top: 5px;", 'id':`btnPayment`,'click': addInvoicesPayment });
                                                paymentBtn.innerHTML = Translate('Payment');
                                            tdCol.appendChild(paymentBtn);
                                        viewHeadRow.appendChild(tdCol);
                                    viewTableBody.appendChild(viewHeadRow);
                                        viewHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'style':`border: 1px solid #dddddd; background: #f5f5f6; padding: 8px 10px;`,'align':`right`,'colspan':`3` });
                                            tdCol.innerHTML = Translate('Total amount due by')+' '+DBDateToViewDate(data.amountDueDate, 0, 1);
                                        viewHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style':`border: 1px solid #dddddd; background: #f5f5f6; padding: 8px 10px; font-weight: bold;`,'align':`right` });				
                                            tdCol.innerHTML = Translate('Amount Due')+' :';
                                        viewHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style':`border: 1px solid #dddddd; background: #f5f5f6; padding: 8px 10px; font-weight: bold;`,'align':`right`,'id':`amountDueStr` });
                                            tdCol.innerHTML = addCurrency(data.amountDue);
                                        viewHeadRow.appendChild(tdCol);
                                    viewTableBody.appendChild(viewHeadRow);
                                viewTable.appendChild(viewTableBody);
                            }
                            else{
                                data.paymentData.forEach(item=>{
                                        viewHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                            tdCol.innerHTML = DBDateToViewDate(item[0])+` ${item[1]} ${Translate('Payment')}`;
                                        viewHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = addCurrency(item[2]);
                                        viewHeadRow.appendChild(tdCol);
                                    viewTableHead.appendChild(viewHeadRow);
                                })
                                viewTable.appendChild(viewTableHead);
                            }
                    viewTableColumn.appendChild(viewTable);
                invoiceTableContent.appendChild(viewTableColumn);
                    const refundRow = cTag('div',{ 'class':`columnXS12 flexEndRow`, 'style': "padding-bottom: 15px;"});
                    if(data.canrefund>0 && data.pos_publish>0){ 
                        let onclick;
                        if(data.refundPer!==0) onclick = ()=> AJrefund_Invoices(data.invoice_no);
                        else onclick = ()=> noPermissionWarning('Refund');
                        refundRow.appendChild(cTag('input',{ 'type':`button`,'class':`btn createButton`,'id':'refundBtn', 'style': "margin-top: 10px;", 'click':onclick,'value':Translate('Create Refund') }));
                    }
                invoiceTableContent.appendChild(refundRow);
            invoiceTableColumn.appendChild(invoiceTableContent);
        Dashboard.appendChild(invoiceTableColumn);

            const historyRow = cTag('div',{ 'class':`flex` });
                const historyColumn = cTag('div',{ 'class':`columnXS12` });
                let hiddenProperties = {
                    'note_forTable':'pos',
                    'spos_id':data.pos_id,
                    'table_idValue':data.pos_id,
                    'sales_datetime':data.sales_datetime,
                    'amount_due':data.amountDue,
                    'publicsShow': '1' ,
                }
                historyColumn.appendChild(historyTable(Translate('Invoice History'),hiddenProperties));
            historyRow.appendChild(historyColumn);
        Dashboard.appendChild(historyRow);

        let cartcostHeadRow;
        if(data.CartAverageCostIssue.length>0){
            const cartCostWidget = cTag('div',{ 'class':`cardContainer`, 'style': "margin-bottom: 10px; padding-left: 5px; padding-right: 5px;" });
                const cartCostHeader = cTag('div',{ 'class':`flex cardHeader` });
                cartCostHeader.appendChild(cTag('i',{ 'class':`fa fa-user`, 'style': "margin: 12px;" }));
                    const cartCostTitle = cTag('h3');
                    cartCostTitle.innerHTML = 'Cart Average Cost Issue';
                cartCostHeader.appendChild(cartCostTitle);
            cartCostWidget.appendChild(cartCostHeader);
                const cartCostContent = cTag('div',{ 'class':`cardContent`, 'style': "padding: 0;" });
                    const cartCostTableColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin: 0; padding: 0;" });
                        const noMoreTable = cTag('div',{ 'id':`no-more-tables` });
                            const cartCostTable = cTag('table',{ 'class':`table-bordered table-striped table-condensed cf listing `, 'style': "margin-top: 2px;" });
                                const cartCostTableHead = cTag('thead',{ 'class':`cf` });
                                    cartcostHeadRow = cTag('tr');
                                        tdCol = cTag('th',{ 'align':`left`,'width':`10%` });
                                        tdCol.innerHTML = 'POS Cart ID';
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('th',{ 'align':`left` });
                                        tdCol.innerHTML = Translate('Description');
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('th',{ 'align':`left`,'width':`10%` });
                                        tdCol.innerHTML = 'Cart Cost';
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('th',{ 'align':`left`,'width':`10%` });
                                        tdCol.innerHTML = 'QTY';
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('th',{ 'align':`left`,'width':`10%` });
                                        tdCol.innerHTML = 'Calculated Cost';
                                    cartcostHeadRow.appendChild(tdCol);
                                cartCostTableHead.appendChild(cartcostHeadRow);
                            cartCostTable.appendChild(cartCostTableHead);
                                const cartCostBody = cTag('tbody');
                                data.CartAverageCostIssue.forEach(item=>{
                                    cartcostHeadRow = cTag('tr');
                                    if(item.cls !== '') cartcostHeadRow.classList.add('bgyellow');
                                        tdCol = cTag('td',{ 'align':`center`,'data-title':"POS Cart ID" });
                                        tdCol.innerHTML = item.pos_cart_id;
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`left`,'data-title':Translate('Description') });
                                        tdCol.innerHTML = `(${item.item_type}) ${item.description} ${item.addDesc}`;
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right`,'data-title':'Cart Cost' });
                                        tdCol.innerHTML = addCurrency(item.cost);
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right`,'data-title':'QTY' });
                                        tdCol.innerHTML = item.shipping_qty;
                                    cartcostHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right`,'data-title':'Calculated Cost' });
                                        tdCol.innerHTML = addCurrency(item.newCost);
                                    cartcostHeadRow.appendChild(tdCol);
                                    cartCostBody.appendChild(cartcostHeadRow);
                                });
                            cartCostTable.appendChild(cartCostBody);
                        noMoreTable.appendChild(cartCostTable);

                            let averageCostButtonDiv = cTag('div',{ 'class': `flexEndRow`});
                                const averageCostButton = cTag('button',{ 'class':`btn completeButton`, 'style': "margin: 10px 10px;", 'click': updateCartMobileAveCost });
                                averageCostButton.innerHTML = "Update Cart's Cellphone Average Cost";
                            averageCostButtonDiv.appendChild(averageCostButton);
                        noMoreTable.appendChild(averageCostButtonDiv);
                    cartCostTableColumn.appendChild(noMoreTable);
                cartCostContent.appendChild(cartCostTableColumn);
            cartCostWidget.appendChild(cartCostContent);
            Dashboard.appendChild(cartCostWidget);
        }
        togglePaymentButton();
        multiSelectAction('invoicePrint');
        if(document.getElementById("checkInvoiceMethod") !==null){
            checkInvoiceMethod();
        }

        addCustomeEventListener('filter',filter_Invoices_view);
        addCustomeEventListener('loadTable',loadTableRows_Invoices_view);
        filter_Invoices_view();
    }
}

//=====================Refund====================//
async function refund(){
    let refundTableHead, refundTableHeadRow, thCol, tdCol, bTag;
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
            const titleColumn = cTag('div',{ 'class':`columnSM12`, 'style': "margin: 0;" });
                const refundItemHeader = cTag('h2',{ 'style': "padding-top: 5px; text-align: start;" });
                refundItemHeader.innerHTML = Translate('Refund Items');
            titleColumn.appendChild(refundItemHeader);
        titleRow.appendChild(titleColumn);
    Dashboard.appendChild(titleRow);
        const refundContainerRow = cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-top: 10px;" });
            const refundContainerColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin: 0;" });
                const refundContent = cTag('div',{ 'class':`refundContent` });
                    const entriesTitle = cTag('h3',{ 'style':`line-height: 30px; text-align: left; border-bottom: 1px solid #ccc; padding-left: 25px;` });
                    entriesTitle.innerHTML = Translate('Invoice Entries');
                refundContent.appendChild(entriesTitle);
                    const refundForm = cTag('form',{ 'method':`post`,'action':`#`,'enctype':`multipart/form-data`,'name':`frmRefund`,'id':`frmRefund` });                    
                    refundForm.addEventListener('submit',event=>event.preventDefault());
                    refundForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
                        const refundColumn = cTag('div',{ 'class':`flexEndRow`, 'style': "align-items: center; padding: 10px 0px;" });
                            const salesmanColumn = cTag('div',{ 'class':`columnXS6 columnSM3 columnMD2`,'align':`right` });
                                const salesmanLabel = cTag('label',{ 'for':`employee_id`, 'data-toggle':`tooltip`, 'data-original-title':Translate('Enter Sales Person') });
                                salesmanLabel.innerHTML = Translate('Sales Person')+' *: ';
                            salesmanColumn.appendChild(salesmanLabel);
                        refundColumn.appendChild(salesmanColumn);
                            const salesmanDropDown = cTag('div',{ 'class':`columnXS6 columnSM3 columnMD2` });
                                const selectEmployee = cTag('select',{ 'name':`employee_id`,'id':`employee_id`,'class':`form-control`,'change': setSessEmpId });
                                    const employeeOption = cTag('option',{ 'value':`0` });
                                    employeeOption.innerHTML = Translate('Select Sales Person');
                                selectEmployee.appendChild(employeeOption);
                            salesmanDropDown.appendChild(selectEmployee);
                        refundColumn.appendChild(salesmanDropDown);
                            const customerColumn = cTag('div',{ 'class':`columnXS6 columnSM3 columnMD2 `,'align':`right` });
                                const customerLabel = cTag('label',{ 'for':`customer_id`, 'data-toggle':`tooltip`,'data-original-title':Translate('Enter Customer Name') });
                                customerLabel.innerHTML = Translate('Customer')+' *: ';
                            customerColumn.appendChild(customerLabel);
                        refundColumn.appendChild(customerColumn);
                            const customerField = cTag('div',{ 'class':`columnXS6 columnSM3 columnMD2` });
                            customerField.appendChild(cTag('input',{ 'maxlength':`50`,'readonly':``,'type':`text`,'required':``,'name':`customer_name`,'id':`customer_name`,'class':`form-control`,'placeholder':Translate('Search Customers') }));
                            customerField.appendChild(cTag('input',{ 'type':`hidden`,'name':`customer_id`,'id':`customer_id` }));
                            customerField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_customer_id` }));
                        refundColumn.appendChild(customerField);
                    refundForm.appendChild(refundColumn);

                        const refundTableContent = cTag('div',{ 'class':`cartContent`});
                            let errorMessage = cTag('div',{ 'class':`flex` });
                            errorMessage.appendChild(cTag('div',{ 'class':`columnSM12 errormsg`, style: "margin: 0;", 'id':`errorposdata` }));
                        refundTableContent.appendChild(errorMessage);
                            const refundTableRow = cTag('div',{ 'class':`flexSpaBetRow` });
                                const refundTableColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin: 0; padding: 0;" });
                                    const refundTable = cTag('table',{ 'class':`table table-bordered` });
                                        refundTableHead = cTag('thead');
                                            refundTableHeadRow = cTag('tr');
                                                thCol = cTag('th',{ 'width':`40px`,'style': "text-align: right;" });
                                                thCol.innerHTML = '#';
                                            refundTableHeadRow.appendChild(thCol);
                                                thCol = cTag('th');
                                                thCol.innerHTML = Translate('Description');
                                            refundTableHeadRow.appendChild(thCol);

                                                thCol = cTag('th',{ 'width':`12%`,'style': "text-align: right;" });
                                                thCol.innerHTML = Translate('Purchased Time/Qty');
                                            refundTableHeadRow.appendChild(thCol);
                                                thCol = cTag('th',{ 'width':`12%`,'style': "text-align: right;" });
                                                thCol.innerHTML = Translate('Previously Returned Time/Qty');
                                            refundTableHeadRow.appendChild(thCol);
                                                thCol = cTag('th',{ 'width':`12%`,'style': "text-align: right;" });
                                                thCol.innerHTML = Translate('Return Time/Qty');
                                            refundTableHeadRow.appendChild(thCol);
                                                thCol = cTag('th',{ 'width':`12%`,'style': "text-align: right;" });
                                                thCol.innerHTML = Translate('Unit Price');
                                            refundTableHeadRow.appendChild(thCol);
                                                thCol = cTag('th',{ 'width':`12%`,'style': "text-align: right;" });
                                                thCol.innerHTML = Translate('Total');
                                            refundTableHeadRow.appendChild(thCol);
                                                thCol = cTag('th',{ 'width':`80px`});
                                                thCol.appendChild(cTag('i',{ 'class':`fa fa-trash-o` }));
                                            refundTableHeadRow.appendChild(thCol);
                                        refundTableHead.appendChild(refundTableHeadRow);
                                    refundTable.appendChild(refundTableHead);
                                        const refundBody = cTag('tbody',{ 'id':`invoice_entry_holder` });
                                        refundBody.innerHTML = ' ';
                                    refundTable.appendChild(refundBody);
                                        refundTableHead = cTag('thead');
                                            refundTableHeadRow = cTag('tr');
                                                tdCol = cTag('td',{ 'colspan':`6`,'style': "text-align: right;" });
                                                    const taxLabel = cTag('label');
                                                    taxLabel.innerHTML = Translate('Taxable Total')+' :';
                                                tdCol.appendChild(taxLabel);
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{ 'style': "text-align: right;" });
                                                    bTag = cTag('b',{ 'id':`taxable_totalstr` });
                                                tdCol.appendChild(bTag);
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable_total`,'id':`taxable_total`,'value':`99.50` }));
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = ' ';
                                            refundTableHeadRow.appendChild(tdCol);
                                        refundTableHead.appendChild(refundTableHeadRow);
                                        refundTableHead.appendChild(cTag('tr',{'id':'taxes_1'}));
                                        refundTableHead.appendChild(cTag('tr',{'id':'taxes_2'}));

                                            refundTableHeadRow = cTag('tr',{ 'id':`nontaxable_totalrow` });
                                                tdCol = cTag('td',{ 'colspan':`6`, 'style': "text-align: right;" });
                                                    const nonTaxLabel = cTag('label');
                                                    nonTaxLabel.innerHTML = Translate('Non Taxable Total')+' :';
                                                tdCol.appendChild(nonTaxLabel);
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{ 'style': "text-align: right;" });
                                                    bTag = cTag('b',{ 'id':`nontaxable_totalstr` });
                                                tdCol.appendChild(bTag);
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`nontaxable_total`,'id':`nontaxable_total`,'value':`0` }));
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = ' ';
                                            refundTableHeadRow.appendChild(tdCol);
                                        refundTableHead.appendChild(refundTableHeadRow);
                                            refundTableHeadRow = cTag('tr');
                                                tdCol = cTag('td',{ 'colspan':`6`, 'style': "text-align: right;" });
                                                    const totalLabel = cTag('label');
                                                    totalLabel.innerHTML = Translate('Grand Total')+' :';
                                                tdCol.appendChild(totalLabel);
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{ 'style': "text-align: right;" });
                                                    bTag = cTag('b',{ 'id':`grand_totalstr` });
                                                tdCol.appendChild(bTag);
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value':segment1 }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':`0` }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`changemethod`,'id':`changemethod`,'value':Translate('Cash') }));
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = ' ';
                                            refundTableHeadRow.appendChild(tdCol);
                                        refundTableHead.appendChild(refundTableHeadRow);
                                            refundTableHeadRow = cTag('tr');
                                                tdCol = cTag('td',{ 'colspan':`6`, 'style': "text-align: right;" });
                                                    const paymentLabel = cTag('label');
                                                    paymentLabel.innerHTML = Translate('Payment Received')+' :';
                                                tdCol.appendChild(paymentLabel);
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{ 'style': "text-align: right;" });
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`receipt_total`,'id':`receipt_total` }));
                                                    bTag = cTag('b',{ 'id':`payment_receiptstr` });
                                                tdCol.appendChild(bTag);
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = ' ';
                                            refundTableHeadRow.appendChild(tdCol);
                                        refundTableHead.appendChild(refundTableHeadRow);
                                            refundTableHeadRow = cTag('tr');
                                                tdCol = cTag('td',{ 'colspan':`6`,'style': "text-align: right;" });
                                                    const refundDiv = cTag('div',{ 'style': "width: 150px; display: inline-block;" });
                                                        const refundLabel = cTag('label');
                                                        refundLabel.innerHTML = Translate('Refund Total')+' :';
                                                    refundDiv.appendChild(refundLabel);
                                                tdCol.appendChild(refundDiv);
                                                tdCol.appendChild(cTag('div',{ 'style': "width: 150px; display: inline-block; margin-left: 10px;", 'id':'drawer_container' }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`multiple_cash_drawers`,'id':`multiple_cash_drawers` }));
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{ 'style': "text-align: right;" });
                                                    let amountLabel = cTag('label',{ 'id':`amountduestr` });
                                                tdCol.appendChild(amountLabel);
												tdCol.append(cTag('input',{'id':'amountdue',style:'display:none','value':'0'}));
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = ' ';
                                            refundTableHeadRow.appendChild(tdCol);
                                        refundTableHead.appendChild(refundTableHeadRow);
                                            refundTableHeadRow = cTag('tr');
                                                tdCol = cTag('td',{ 'colspan':`7` });
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`default_invoice_printer`,'id':`default_invoice_printer` }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`email_address`,'id':`email_address`,'value':`` }));
                                                tdCol.appendChild(cTag('select',{ 'class':`form-control`,style:'display:none','name':`method`,'id':`method` }));
                                                    
                                                    const refundButtonDiv = cTag('div',{ 'class':`flexEndRow`, 'style': "align-items: center;" });
                                                        const refundButtonInGroup = cTag('div',{ 'class':`input-group` });
                                                            let completeButton = cTag('button',{ 'name':`CompleteBtn`,'id':`CompleteBtn`,'class':`btnFocus moneyIcon cursor`,style:'display:none' });
                                                            completeButton.addEventListener('click',completeRefund);
                                                                let moneySpan = cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;"});
                                                                let refundItemLabel = cTag('label');
                                                                if(OS !=='unknown') refundItemLabel.innerHTML = 'Refund';
                                                                else refundItemLabel.innerHTML = Translate('Refund Items');
                                                            completeButton.append(moneySpan, refundItemLabel);
                                                        refundButtonInGroup.appendChild(completeButton);
                                                            let completeButtonDiv = cTag('button',{ 'name':`CompleteBtnDis`,'id':`CompleteBtnDis`,'class':`btnFocus` });
                                                                const moneyIcon = cTag('span',{ 'class':`input-group-addon` });
                                                                moneyIcon.appendChild(cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;" }));
                                                            completeButtonDiv.appendChild(moneyIcon);
                                                                let inputSpan = cTag('span',{ 'class':`input-group-addon`, 'style': "padding-left: 0;" });
                                                                    const refundLabels = cTag('label');
                                                                    if(OS !=='unknown') refundItemLabel.innerHTML = 'Refund';
                                                                    else refundLabels.innerHTML = Translate('Refund Items');
                                                                inputSpan.appendChild(refundLabels);
                                                            completeButtonDiv.appendChild(inputSpan);
                                                        refundButtonInGroup.appendChild(completeButtonDiv);

                                                        const cancelDiv = cTag('div',{ 'style': " margin-right: 15px;" });
                                                            const cancelInGroup = cTag('div',{ 'class':`input-group` });
                                                                const cancelLink = cTag('button',{ 'title':"Cancel",'id':`clearReturnCart`,'class':`btnFocus iconButton cursor` });
                                                                    const closeIcon = cTag('i',{ 'class':`fa fa-close`, 'style': "font-size: 1.5em;" });
                                                                    let cancelLabel = cTag('label');
                                                                    cancelLabel.innerHTML = Translate('Cancel');
                                                                cancelLink.append(closeIcon, cancelLabel);
                                                            cancelInGroup.appendChild(cancelLink);
                                                        cancelDiv.appendChild(cancelInGroup);
                                                    refundButtonDiv.append(cancelDiv, refundButtonInGroup);
                                                tdCol.appendChild(refundButtonDiv);
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos_id`,'id':`pos_id` }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`SquareupCount`,'id':`SquareupCount` }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`temp_pos`,'id':`temp_pos`,'value':`0` }));
                                            refundTableHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = ' ';
                                            refundTableHeadRow.appendChild(tdCol);
                                        refundTableHead.appendChild(refundTableHeadRow);
                                    refundTable.appendChild(refundTableHead);
                                refundTableColumn.appendChild(refundTable);
                            refundTableRow.appendChild(refundTableColumn);
                        refundTableContent.appendChild(refundTableRow);
                    refundForm.appendChild(refundTableContent);
                refundContent.appendChild(refundForm);
            refundContainerColumn.appendChild(refundContent);
        refundContainerRow.appendChild(refundContainerColumn);
    Dashboard.appendChild(refundContainerRow);

    AJ_Refund_MoreInfo();
}

async function AJ_Refund_MoreInfo(){
    const url = '/'+segment1+'/AJ_Refund_MoreInfo';
    fetchData(afterFetch,url,{});

    function afterFetch(data){
        setOptions(document.getElementById('employee_id'),data.empOpt,1,1);
        document.getElementById('employee_id').value = data.employee_id;
        document.getElementById('customer_name').value = data.customer_name;
        document.getElementById('customer_id').value = data.customer_id;

        document.getElementById('taxable_totalstr').innerHTML = 
        document.getElementById('nontaxable_totalstr').innerHTML = 
        document.getElementById('grand_totalstr').innerHTML = 
        document.getElementById('amountduestr').innerHTML = currency+'0.00';

        let strong, tdCol, taxHeadRow, bTag, tableHead;
        if(data.taxes_name1 !==''){
            let txtInc = '';
            if(data.tax_inclusive1>0){txtInc = ' Inclusive';}
            taxHeadRow = document.getElementById('taxes_1');
                tdCol = cTag('td',{ 'style':`border: 1px solid #dddddd;background: #f5f5f6;padding: 8px 10px;`,'align':`right`,'colspan':`6` });
                    strong = cTag('strong');
                    strong.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%`+txtInc+`) :`;
                tdCol.appendChild(strong);
            taxHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'style':`border: 1px solid #dddddd;background: #f5f5f6;padding: 8px 10px;`,'align':`right` });
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':data.taxes_name1 }));
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':data.taxes_percentage1 }));
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':data.tax_inclusive1 }));
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':0 }));
                    bTag = cTag('b',{ 'id':`taxes_total1str` });
                    bTag.innerHTML = currency+'0.00';
                tdCol.appendChild(bTag);
            taxHeadRow.appendChild(tdCol);
                tdCol = cTag('td');
                tdCol.innerHTML = ' ';
            taxHeadRow.appendChild(tdCol);
        }
        else{
            tableHead = document.getElementById('taxes_1').parentNode;
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':`` }));
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':`0` }));
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':`0` }));
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
            tableHead.appendChild(cTag('b',{ style:'display:none','id':`taxes_total1str` }));
        }
        if(data.taxes_name2 !==''){
            let txtInc = '';
            if(data.tax_inclusive2>0){txtInc = ' Inclusive';}
            taxHeadRow = document.getElementById('taxes_2');
                tdCol = cTag('td',{ 'style':`border: 1px solid #dddddd;background: #f5f5f6;padding: 8px 10px;`,'align':`right`,'colspan':`6` });
                    strong = cTag('strong');
                    strong.innerHTML = `${data.taxes_name2} (${data.taxes_percentage2}%`+txtInc+`) :`;
                tdCol.appendChild(strong);
            taxHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'style':`border: 1px solid #dddddd;background: #f5f5f6;padding: 8px 10px;`,'align':`right` });
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':data.taxes_name2 }));
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':data.taxes_percentage2 }));
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':data.tax_inclusive2 }));
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                    bTag = cTag('b',{ 'id':`taxes_total2str` });
                    bTag.innerHTML = currency+'0.00';
                tdCol.appendChild(bTag);
            taxHeadRow.appendChild(tdCol);
                tdCol = cTag('td');
                tdCol.innerHTML = ' ';
            taxHeadRow.appendChild(tdCol);
        }
        else{
            tableHead = document.getElementById('taxes_2').parentNode;
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
            tableHead.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
                bTag = cTag('b',{ style:'display:none','id':`taxes_total2str` });
                bTag.innerHTML = currency+'0.00';
            tableHead.appendChild(bTag);
        }

        document.getElementById('receipt_total').value = data.totalPayment;
        document.getElementById('payment_receiptstr').innerHTML = addCurrency(data.totalPayment);

        if(data.multiple_cash_drawers>0 && data.drawerOpt.length>0){                
                const selectDrawer = cTag('select',{ 'class':`form-control`,'name':`drawer`,'id':`drawer`,'change': setCDinCookie });
                if(data.drawer===''){
                        const drawerOption = cTag('option',{ 'value':`` });
                        drawerOption.innerHTML = Translate('Select Drawer');
                    selectDrawer.appendChild(drawerOption);
                }
                setOptions(selectDrawer,data.drawerOpt,0,1);
                selectDrawer.value = data.drawer;
            document.getElementById('drawer_container').appendChild(selectDrawer);
        }
        else{
            document.getElementById('drawer_container').appendChild(cTag('input',{'type':"hidden", 'name':"drawer", 'id':"drawer", 'value':""}));
        }

        document.getElementById('multiple_cash_drawers').value = data.multiple_cash_drawers;
        document.getElementById('default_invoice_printer').value = data.default_invoice_printer;

        if(data.methodOpt.length>0){
            setOptions(document.getElementById('method'),data.methodOpt,0,0);
            if(data.methodOpt.includes(data.payment_method)){
                document.getElementById('method').value = data.payment_method;
            }
        }
        document.getElementById('clearReturnCart').addEventListener('click',()=>clearReturnCart(data.invoice_no));
        document.getElementById('pos_id').value = data.pos_id;
        document.getElementById('SquareupCount').value = data.SquareupCount;

        loadRefundCart(0);
        setTimeout(function() {document.getElementById("employee_id").focus();}, 500);
    }
}

async function loadRefundCart(pos){
	const pos_id = document.getElementById("pos_id").value;
	const jsonData = {};
	jsonData['pos_id'] = pos_id;

    const url = "/Invoices/AJload_RefundCart";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(document.getElementById('_tooltip_')) document.getElementById('_tooltip_').remove();
			const invoice_entry_holder = document.getElementById('invoice_entry_holder');
			invoice_entry_holder.innerHTML = '';
            let tdCol;
			data.cartData.forEach((item)=>{
                const invoiceHeadRow = cTag('tr',{ 'class':`salesrow${item.pos}` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = item.pos;
                invoiceHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`left` });

                    let descriptionDiv;
                    let rowremoveicon = true;
                    let detailedDescription = document.createDocumentFragment();
                    if(item.item_type =='one_time'){
                        detailedDescription.append(" [1]");
                    }
                    else{
                        let description = item.description.replace(`(${item.sku})`,' ');
                        let linkedsku = cTag('a',{href:`/Products/view/${item.item_id}`, 'class':"txtunderline txtblue", 'title':Translate('View/Edit')});
                        linkedsku.append(item.sku,' ',cTag('i',{'class':'fa fa-link'}));
                        detailedDescription.append(description,linkedsku);
                    }
                    if(item.add_description){
                        let add_description = cTag('p', {style: "margin: 0; padding-left: 10px;"});
                        add_description.innerHTML = item.add_description;
                        detailedDescription.appendChild(add_description);
                    }

                    if(item.item_type==='cellphones' && item.max_qty>0){
                        descriptionDiv = cTag('div');
                            let description = cTag('div',{'class':'columnSM12'});
                            description.append(detailedDescription);
                        descriptionDiv.appendChild(description);
                            let newimei_info_container = cTag('div',{'class':'columnSM12'});

                                let allCheckbox = cTag('label',{'class': 'flex cursor', 'style': 'font-weight: 100;'});
                                    let checkBoxField = cTag('input',{'style': 'margin-left: 10px;', 'type':'checkbox', id:"check-all", 'checked': ''});
                                    checkBoxField.addEventListener('change', function() {
                                        let checked = this.checked;
                                        document.querySelectorAll('[name = "imeiInfoCheck"]').forEach(item => {
                                            if(checked && !item.checked) item.click();
                                            else if(!checked && item.checked) item.click();
                                            activeLoader();
                                        })
                                        let allNoneSelect = document.getElementById('allNoneSelect');
                                        if(checked){
                                            allNoneSelect.innerHTML = 'Deselect All';
                                        }else{
                                            allNoneSelect.innerHTML = 'Select All';
                                        }
                                    });
                                    let selectAll = cTag('p',{style: "margin: 0; padding-left: 10px;", id: 'allNoneSelect'});
                                    selectAll.innerHTML = 'Deselect All';
                                allCheckbox.append(checkBoxField, selectAll);
                            newimei_info_container.appendChild(allCheckbox);

                            item.newimei_info.forEach(imeiDetails=>{
                                let newIMEIinfo = cTag('label',{'class': 'flex cursor', 'style': 'font-weight: 100;', 'id':`imei_idstr${item.pos}`});
                                    let inputField = cTag('input',{'click':function(){checkingIMEI(this,item.pos_cart_id,imeiDetails[0],item.pos)}, 'style': 'margin-left: 10px;', 'type':'checkbox','name':'imeiInfoCheck', 'id':'imeiInfoCheck' , 'value': 1});
                                    inputField.checked = !item.uncheckedItemIds.includes(imeiDetails[0]);
                                    let pTag = cTag('p',{style: "margin: 0; padding-left: 10px;", id:`${item.pos}${imeiDetails[0]}`});
                                    pTag.append(imeiDetails[1]);
                                newIMEIinfo.append(inputField, pTag);
                                newimei_info_container.append(newIMEIinfo);
                            })
                        descriptionDiv.appendChild(newimei_info_container);
                        descriptionDiv.append(cTag('input',{'type':"hidden", 'name':`item_number${item.pos}`, 'id':`item_number${item.pos}`, 'title':item.pos, 'placeholder':Translate('IMEI Number'), 'maxlength':"20"}));
                    }
                    else if(item.item_type==='product' && item.imei_id !==''){
                        descriptionDiv = cTag('div');
                            let description = cTag('div',{'class':'columnSM12'});
                            description.append(detailedDescription);
                        descriptionDiv.appendChild(description);
                            let serialDiv = cTag('div',{'class':'columnSM12', 'style': "padding-left: 15px;"});
                            if(item.imei_id !=''){
                                rowremoveicon = false;
                                item.imei_id.split(', ').forEach((imei_id,indx)=>{
                                        let pTag = cTag('p',{style: "margin: 0; padding-left: 10px;", id:`${item.pos}${indx+1}`});
                                        pTag.append(imei_id,' ',cTag('i',{'style':"cursor:pointer;", 'data-toggle':"tooltip", 'data-original-title':Translate('Remove Serial/IMEI Number'), 'click':()=>removeIMEIFromRefundCart(item.pos, imei_id), 'class':"fa fa-trash-o"}))
                                    serialDiv.append(pTag);
                                })
                            }
                        descriptionDiv.appendChild(serialDiv);
                            let errorDescription = cTag('div',{'class':'columnSM6 error_msg','id':`error_serial_number${item.item_id}`});
                        descriptionDiv.appendChild(errorDescription);
                    }
                    
                    if(descriptionDiv) tdCol.append(descriptionDiv);
                    else tdCol.append(detailedDescription);
                invoiceHeadRow.appendChild(tdCol);


                tdCol = cTag('td',{ 'align':`right`});
                    tdCol.innerHTML = item.return_qty+item.max_qty;
                invoiceHeadRow.appendChild(tdCol);

                    tdCol = cTag('td',{ 'align':`right`});
                    tdCol.innerHTML = item.return_qty;
                invoiceHeadRow.appendChild(tdCol);

                    tdCol = cTag('td',{ 'align':`right`,'id':`qtystr${item.pos}` });
                    tdCol.innerHTML = ' ';
                invoiceHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(item.sales_price);
                invoiceHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'id':`totalstr${item.pos}`,'align':`right` });
                    tdCol.innerHTML = ' ';
                invoiceHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`center` });
                    if(rowremoveicon) tdCol.append(cTag('i',{'style':"cursor:pointer;", 'data-toggle':"tooltip", 'data-original-title':"Remove Item", 'id':`delete${item.pos}`, 'click':()=>deletethisrefundrow(item.pos), 'class':"fa fa-trash-o"}),'  ')
                    tdCol.appendChild(cTag('i',{ 'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Edit Item'),'click': ()=> changethisrefundrow(item.pos),'class':`fa fa-edit` }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos[]`,'value':item.pos }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos_cart_id${item.pos}`,'id':`pos_cart_id${item.pos}`,'value':item.pos_cart_id }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_id${item.pos}`,'id':`item_id${item.pos}`,'value':item.item_id }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_type${item.pos}`,'id':`item_type${item.pos}`,'value':item.item_type }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`product_type${item.pos}`,'id':`product_type${item.pos}`,'value':item.product_type }));
                        let textarea = cTag('textarea',{ style:'display:none','name':`add_description${item.pos}`,'id':`add_description${item.pos}` });
                        textarea.innerHTML = item.add_description;
                    tdCol.appendChild(textarea);
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`require_serial_no${item.pos}`,'id':`require_serial_no${item.pos}`,'value':item.require_serial_no }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`sales_price${item.pos}`,'id':`sales_price${item.pos}`,'value':round(item.sales_price,2) }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`max_sales_price${item.pos}`,'id':`max_sales_price${item.pos}`,'value':item.max_sales_price }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`discount_is_percent${item.pos}`,'id':`discount_is_percent${item.pos}`,'value':item.discount_is_percent }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`discount${item.pos}`,'id':`discount${item.pos}`,'value':round(item.discount,2) }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`qty${item.pos}`,'id':`qty${item.pos}`,'value':round(item.qty,2) }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`max_qty${item.pos}`,'id':`max_qty${item.pos}`,'value':round(item.max_qty,2) }));
                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable${item.pos}`,'id':`taxable${item.pos}`,'value':item.taxable }));
                invoiceHeadRow.appendChild(tdCol);
            invoice_entry_holder.appendChild(invoiceHeadRow);
        })
        
        if(document.querySelectorAll(".item_number")){
            document.querySelectorAll(".item_number").forEach(oneRowObj=>{
                oneRowObj.addEventListener('keypress', e => {
                    if(e.which === 13) {
                        const item_number = e.target.value;
                        const pos = e.target.title;					
                        findItemIdByItemNumber(pos, item_number);
                    }
                });	
            });
            AJautoComplete_IMEIRefund();
        }			
        
        calculateRefundTotal();	
        document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
        
        if(document.getElementById("item_number"+pos)){document.getElementById("item_number"+pos).focus();}
    }
}

async function checkingIMEI(checkBox,pos_cart_id,imei_id,positionID){
    const IMEIChecked = checkBox.checked?1:0;
    const qtyField = document.getElementById(`qty${positionID}`);
    // let qty = Number(qtyField.value);
    const maxQtyField = document.getElementById(`max_qty${positionID}`);
    let maxQty = Number(maxQtyField.value);
    const url = "/Invoices/checkingIMEI";
    const jsonData = { pos_cart_id,imei_id,checked:IMEIChecked }
    await fetchData(afterFetch,url,jsonData);
    function afterFetch(){
        let qty = Number(qtyField.value);
        qtyField.value = IMEIChecked?(qty+1):(qty-1);
        maxQtyField.value = IMEIChecked?(maxQty+1):(maxQty-1);
        calculateRefundTotal();
    }
}

function AJautoComplete_IMEIRefund(){
	const item_number = document.querySelector('.item_number');
	if(item_number){
		customAutoComplete(item_number,{								
			minLength:2,
			search: function () {
				document.getElementById("temp_pos").value = item_number.getAttribute('title');										
			},
			source: async function (request, response) {
				const pos = document.getElementById("temp_pos").value;
				const jsonData = {
					'pos':document.getElementById("temp_pos").value, 
					'pos_cart_id':document.getElementById("pos_cart_id"+pos).value, 
					'item_number':request
				};
	
				const url = "/Invoices/autoItemNumber";
                await fetchData(afterFetch,url,jsonData);

                function afterFetch(data){
                    response(data.returnStr);
                }
			},
			select: function( event, info ) {
				item_number.value = info.label;	
				const pos = document.getElementById("temp_pos").value;									
				addItemNumberIntoRefund(pos, info.item_id);
				item_number.focus();
				return false;
			}
		});
		item_number.addEventListener('keyup',function (event) {
			if(event.which === 13) {
				item_number.hide();
			}
		});
	}
}

/*=========Refund Module==============*/
function completeRefund(){
    if(document.getElementsByName("pos[]").length == 0){
		showTopMessage('alert_msg', 'Nothing to Refund');
		return;
	} 
	
    	
	let oElement,changeamountofval, print_type, inputField;
	const customer_id = document.getElementById("customer_id");
	oElement = document.getElementById('errmsg_customer_id');
	oElement.innerHTML = "";
	if(customer_id.value === "" || parseInt(customer_id.value) === 0){
		oElement.innerHTML = Translate('Missing customer. Please choose/add new customer');
		document.getElementById("customer_name").focus();
		return(false);
	}
	
	oElement = document.getElementById('errorposdata');
	oElement.innerHTML = "";
	const hasdata = document.querySelector("#invoice_entry_holder").innerHTML;
	if(hasdata.length<10){
		oElement.innerHTML = Translate('Missing cart. Please choose/add new product');
		document.getElementById("search_item_number").focus();
		return(false);
	}
	
	let grand_total = parseFloat(document.getElementById("grand_total").value);
	if(grand_total==='' || isNaN(grand_total)){grand_total = 0.00;}
	let receipt_total = parseFloat(document.getElementById("receipt_total").value);
	if(receipt_total==='' || isNaN(receipt_total)){receipt_total = 0.00;}
	
	if(hasdata.length>=10){
		document.getElementById("changemethod").value = 'Cash';
		if(receipt_total<=grand_total){
			changeamountofval = receipt_total;
		}
		else{
			changeamountofval = grand_total;
		}
		if(changeamountofval<0){changeamountofval = 0;}
		const changeamountofvalStr = addCurrency(changeamountofval);
		
		const formhtml = cTag('div');
			const emptyRow = cTag('div', {class: "flexSpaBetRow"});
		formhtml.appendChild(emptyRow);
			const changeAmountRow = cTag('div', {class: "flexSpaBetRow"});
                const changeAmountColumn = cTag('div', {class: "columnXS12", 'align': "center"});
                    let changeAmountValue = cTag('span', {'style': "color:orange; font-size:48px;font-weight:500", id: "changeamountof"});
                    changeAmountValue.innerHTML = changeamountofvalStr;
                changeAmountColumn.appendChild(changeAmountValue);
            changeAmountRow.appendChild(changeAmountColumn);
		formhtml.appendChild(changeAmountRow);

			const emptyField = cTag('div', {class: "flexSpaBetRow", id: ""});
		formhtml.appendChild(emptyField);

			const refundMethodRow = cTag('div', {class: "flexSpaBetRow"});
                const refundMethodTitle = cTag('div', {class: "columnSM6", 'align': "left"});
                    const refundMethodLabel = cTag('label', {'for': "exchangemethod"});
					refundMethodLabel.innerHTML = Translate('Choose how the refund was given')+':';
                refundMethodTitle.appendChild(refundMethodLabel);
            refundMethodRow.appendChild(refundMethodTitle);
                const refundMethodOption = cTag('div', {class: "columnSM6 flexSpaBetRow",'style':"gap:5px", 'align': "left"});
					let selectExchange = cTag('select', {class: "form-control", 'style': "width: auto ;", name: "exchangemethod", id: "exchangemethod"});
					selectExchange.addEventListener('change', e => {document.getElementById('changemethod').value=e.target.value;checkMethod();});
					selectExchange.innerHTML = document.getElementById("method").innerHTML;
                refundMethodOption.appendChild(selectExchange);
				if(document.getElementById('drawer').options){
						let selectDrawer = cTag('select', {class: "form-control",id:'chooseDrawer', 'style': "width: auto ;"});//new
						selectDrawer.addEventListener('change',function(){
							document.getElementById('drawer').value = this.value;
							setCDinCookie();
						});
						selectDrawer.innerHTML = document.getElementById("drawer").innerHTML;
						selectDrawer.value = document.getElementById('drawer').value;
                    refundMethodOption.appendChild(selectDrawer);
				}
					let errorSpan = cTag('span', {id: "error_amount", class: "errormsg"});
                refundMethodOption.appendChild(errorSpan);
            refundMethodRow.appendChild(refundMethodOption);
		formhtml.appendChild(refundMethodRow);

			const paymentRow = cTag('div', {class: "flexSpaBetRow"});
				let paymentColumn = cTag('div', {class: "columnSM12", 'align': "left", id: "buttonSqPayment"});
            paymentRow.appendChild(paymentColumn);
		formhtml.appendChild(paymentRow);

			const printTypeRow = cTag('div', {class: "flexSpaBetRow"});
                const printTypeTitle = cTag('div', {class: "columnSM4", 'align': "left"});
                    const printTypeLabel = cTag('label', {'for': "default_invoice_printer1"});
					printTypeLabel.innerHTML = Translate('Choose print type')+':';
                printTypeTitle.appendChild(printTypeLabel);
            printTypeRow.appendChild(printTypeTitle);
                const printTypeColumn = cTag('div', {class: "columnSM8 flexStartRow", 'style': "padding-left: 20px;"});
					const fullPrintLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
						inputField = cTag('input', {'type': "radio", 'value': "Large", id: "default_invoice_printer1", name: "print_type", class: "print_type"});
                    fullPrintLabel.appendChild(inputField);
					fullPrintLabel.append(' '+Translate('Full Page'));
                printTypeColumn.appendChild(fullPrintLabel);
					const thermalPrintLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "Small", id: "default_invoice_printer2", name: "print_type", class: "print_type"});
                    thermalPrintLabel.appendChild(inputField);
                    thermalPrintLabel.append(' '+Translate('Thermal'));
                printTypeColumn.appendChild(thermalPrintLabel);
                    const emailLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "Email", id: "default_invoice_printer3", name: "print_type", class: "print_type"});
                    emailLabel.appendChild(inputField);
                    emailLabel.append(' '+Translate('Email'));
                printTypeColumn.appendChild(emailLabel);
                    const receiptLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "No Receipt", id: "default_invoice_printer4", name: "print_type", class: "print_type"});
                    receiptLabel.appendChild(inputField);
                    receiptLabel.append(' '+Translate('No Receipt'));
                printTypeColumn.appendChild(receiptLabel);
            printTypeRow.appendChild(printTypeColumn);
        formhtml.appendChild(printTypeRow);

            const emailRow = cTag('div', {class: "flexSpaBetRow invcustomeremail",style:'display:none'});
                const emailTitle = cTag('div', {class: "columnSM3", 'align': "left"});
                    const invoiceEmailLabel = cTag('label', {'for': "invcustomeremail"});
                    invoiceEmailLabel.innerHTML = Translate('Email');
                        const requiredField = cTag('span', {class: "required"});
                        requiredField.innerHTML = '*';
                    invoiceEmailLabel.appendChild(requiredField);
                emailTitle.appendChild(invoiceEmailLabel);
            emailRow.appendChild(emailTitle);
                const emailField = cTag('div', {class: "columnSM9", 'align': "left"});
                    inputField = cTag('input', {'required': "required", 'maxlength': 50, 'type': "email", class: "form-control", name: "invcustomeremail", id: "invcustomeremail", 'value': document.getElementById("email_address").value});
                emailField.appendChild(inputField);
                    inputField = cTag('input', {'type': "hidden", name: "changeamountofval", id: "changeamountofval", 'value': changeamountofval});
                emailField.appendChild(inputField);
            emailRow.appendChild(emailField);
        formhtml.appendChild(emailRow);

	popup_dialog(
		formhtml,
		{
			title:Translate('Please give REFUND of'),
			width:600,
			buttons: {
				'Cancel': {
					text: Translate('Cancel'), class: 'btn defaultButton', click: function(hide) {
                        if(OS !=='unknown') document.querySelector("#CompleteBtn").querySelector('label').innerHTML = 'Refund';
                        else document.querySelector("#CompleteBtn").querySelector('label').innerHTML = Translate('Refund Items');
						hide();
					},
				},
				'Complete':{
					text: Translate('Complete'), class: 'btn completeButton btnmodel popupCompleteBtn', click: function(hidePopup) {
					let print_typeselect = 0;
					const print_typeid = document.getElementsByName("print_type");
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
					
					confirmRefundCompletion(hidePopup,print_type);
				},
				}
			}
		}
	);
		
		document.querySelectorAll(".print_type").forEach(item=>{
			item.addEventListener('click', e => {
				print_type = e.target.value;
				if(print_type==='Email'){
					document.querySelectorAll(".invcustomeremail").forEach(mailItem=>{
						if(mailItem.style.display === 'none'){
							mailItem.style.display = '';
						}
					})
				}
				else{
					document.querySelectorAll(".invcustomeremail").forEach(mailItem=>{
						if(mailItem.style.display !== 'none'){
							mailItem.style.display = 'none';
						}
					})
				}
			});
		})
		
		setTimeout(function() {
			document.querySelectorAll(".invcustomeremail").forEach(item=>{
				if(item.style.display !== 'none'){
					item.style.display = 'none';
				}
			})
			print_type = document.querySelector("#default_invoice_printer").value;
			if(print_type==='Large'){
				document.querySelector("#default_invoice_printer1").checked = true;
			}
			else if(print_type==='Small'){
				document.querySelector("#default_invoice_printer2").checked = true;
			}
			else if(print_type==='Email'){
				document.querySelector("#default_invoice_printer3").checked = true;
				document.querySelectorAll(".invcustomeremail").forEach(item=>{
					if(item.style.display === 'none'){
						item.style.display = '';
					}
				})
			}
			else{
				document.querySelector("#default_invoice_printer4").checked = true;
			}
            document.getElementById("exchangemethod").value = document.getElementById("method").value;
            document.getElementById('changemethod').value = document.getElementById("method").value;
			document.getElementById("exchangemethod").focus();
			checkRefundMethod();
			additionalPaymentOptions();
		},500);
	}
	
	document.querySelector("#CompleteBtn").querySelector('label').innerHTML = Translate('Saving')+'...';

	return false;
}

async function confirmRefundCompletion(hidePopup,print_type){
	const chooseDrawer = document.getElementById("chooseDrawer");
	if(chooseDrawer && chooseDrawer.value === ''){
		chooseDrawer.focus();
		return false;
	}
	const email = document.getElementById("invcustomeremail").value;
	if(print_type==='Email' && !emailcheck(email)){
		document.getElementById("invcustomeremail").focus();
		return false;
	}
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
	
	const jsonData = serialize('#frmRefund');
    const url = "/Invoices/AJsave_Refund/";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg ==='success' && data.invoice_no>0){
			let printType;
			if(print_type !=='')
				printType = print_type.toLowerCase();
			else
				printType = 'large';

			if(printType === 'large' || printType === 'small'){
				const redirectTo = '/Carts/cprints/'+printType+'/'+data.invoice_no;
				const day = new Date();
				const id = day.getTime();
				const w = 900;
				let h = 600;
				const scrl = 1;
				const winl = (screen.width - w) / 2;
				const wint = (screen.height - h) / 2;
				const winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
				window.open(redirectTo, '" + id + "', winprops);
				
				setTimeout(function() {
					if(data.invoice_no>0){
						window.location = '/Invoices/lists/';
					}
					else{
						window.location = '/Invoices/lists/cancelled';
					}
				}, 1500);
			}
			else if(print_type==='Email'){				
				if(email !=='' && data.id>0){
					document.getElementById("pos_id").value = data.id;
					
					document.getElementById("email_address").value = email;
					emaildetails(false, '/Carts/AJ_sendposmail');
					
					setTimeout(function() {
						if(data.invoice_no>0){
							window.location = '/Invoices/lists/';
						}
						else{
							window.location = '/Invoices/lists/cancelled';
						}
					}, 1000);
					
				}
				else{
					showTopMessage('alert_msg', Translate('There is no email address for customer.'));
				}
			}			
			else{
				window.location = '/Invoices/lists/';
			}
            hidePopup();
		}
		else{
			actionBtnClick('.btnmodel', Translate('Complete'), 0);
            if(data.returnStr=='errorOnAdding'){
                showTopMessage('alert_msg', Translate('Error occured while adding new pos! Please try again.'));
            }
            else if(data.returnStr=='errorOnEditing'){
                showTopMessage('alert_msg', Translate('There is no transaction found for removing.'));
            }
            else{
                showTopMessage('alert_msg', Translate('Could not refund this invoice.'));
            }		

			if(OS !=='unknown') document.querySelector("#CompleteBtn").querySelector('label').innerHTML = 'Refund';
            else document.querySelector("#CompleteBtn").querySelector('label').innerHTML = Translate('Refund Items');
		}
    }
	return false;
}

async function setSessEmpId(){
	const employee_id = document.getElementById("employee_id").value;

	const jsonData = {};
	jsonData['employee_id'] = employee_id;

    const url = "/Invoices/setSessEmpId";

    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        
    }
}

async function findItemIdByItemNumber(pos, item_number){
	const pos_cart_id = document.getElementById("pos_cart_id"+pos).value;

	const  jsonData = {};
	jsonData['pos'] = pos;
	jsonData['pos_cart_id'] = pos_cart_id;
	jsonData['item_number'] = item_number;

    const url = "/Invoices/findItemIdByItemNumber";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr !==''){
			addItemNumberIntoRefund(pos, data.returnStr);
		}
        else{
            showTopMessage('alert_msg', Translate('IMEI Number not found'));
        }
    }
}

async function addItemNumberIntoRefund(pos, item_id){
	const pos_cart_id = document.getElementById("pos_cart_id"+pos).value;

	const jsonData = {};
	jsonData['pos'] = pos;
	jsonData['pos_cart_id'] = pos_cart_id;
	jsonData['item_id'] = item_id;

    const url = "/Invoices/addItemNumberIntoRefund";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr !==''){
			loadRefundCart(pos);
		}
        else{
            showTopMessage('alert_msg', Translate('IMEI Number not found'));
        }
    }
}

function additionalPaymentOptions(){
	const SquareupCount = document.getElementById("SquareupCount").value;
	
	let methodOptions;
	if(SquareupCount>0 ){
		document.querySelectorAll("#exchangemethod option").forEach(function(item){
			const oneMethod = item.value;
			if(oneMethod ==='Squareup'){}
			else{
				methodOptions = cTag('option',{'value':oneMethod});
				methodOptions.innerHTML = oneMethod;
				document.querySelector("#exchangemethod").appendChild(methodOptions);
			}
		});
		
		if(SquareupCount>0){
			methodOptions = cTag('option',{'value':'Squareup'});
			methodOptions.innerHTML = Translate('Squareup');
			document.querySelector("#exchangemethod").appendChild(methodOptions);
		}
	}
}	
                                        
function checkRefundMethod(){
	const SquareupCount = document.querySelector("#SquareupCount").value;
	document.querySelector("#buttonSqPayment").innerHTML = '';
	if(document.querySelector("#exchangemethod").value==='Squareup' && SquareupCount>0){
		const btnSqPayment = document.querySelector("#buttonSqPayment");
		btnSqPayment.innerHTML = "";
			const pTag = cTag('p');
			pTag.innerHTML = Translate('You must do a square refund within the SQUARE APP yourself.  Please log into your SQUARE APP and find the transaction and refund it.');
		btnSqPayment.appendChild(pTag);
	}
}

function changethisrefundrow(pos){
	const add_description = document.getElementById("add_description"+pos).value;
	const item_type = document.getElementById("item_type"+pos).value;
	const product_type = document.getElementById("product_type"+pos).value;
	const require_serial_no = parseInt(document.getElementById("require_serial_no"+pos).value);
	let sales_price = document.getElementById("sales_price"+pos).value;
	const max_sales_price = document.getElementById("max_sales_price"+pos).value;
	let qty = document.getElementById("qty"+pos).value;		
	const max_qty = document.getElementById("max_qty"+pos).value;	
	const discount_is_percent = document.getElementById("discount_is_percent"+pos).value;
	const discount = document.getElementById("discount"+pos).value;		
	const taxable = document.getElementById("taxable"+pos).value;
		
	let currencyoption = currency;
	if(currency ==='<i class="fa fa-inr" aria-hidden="true"></i>'){currencyoption = 'RS';}
	
    let bTag, inputField, errorSpan, errorDiv;
	const formhtml = cTag('div');
		const refundCartForm = cTag('form', {'action': "#", name: "frmchangerefundrow", id: "frmchangerefundrow", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
            const refundCartRow = cTag('div', {class: "flex", 'align': "left"});
				const unitPriceColumn = cTag('div', {class: "columnXS4 columnSM3"});
                    const unitPriceLabel = cTag('label', {'for': "sales_price"});
					unitPriceLabel.innerHTML = Translate('Unit Price')+ ':';
                unitPriceColumn.appendChild(unitPriceLabel);
            refundCartRow.appendChild(unitPriceColumn);
                const unitPriceField = cTag('div', {class: "columnXS8 columnSM4"});
					inputField = cTag('input', {'type': "text",'data-min':'-9999999.99', 'data-max':'9999999.99','data-format':'d.dd', class: "form-control updatecartfields calculateRefundItemTotal", name: "sales_price", id: "sales_price", 'value': sales_price});
					controllNumericField(inputField, '#errmsg_sales_price');
                unitPriceField.appendChild(inputField);
                unitPriceField.appendChild(cTag('span', {class: "error_msg", id: "errmsg_sales_price"}));
            refundCartRow.appendChild(unitPriceField);
                const unitPriceValue = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "salesPriceStr"});
					bTag.innerHTML = currency+'0.00';
                unitPriceValue.appendChild(bTag);
            refundCartRow.appendChild(unitPriceValue);
        refundCartForm.appendChild(refundCartRow);

			const qtyRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
                const qtyTitle = cTag('div', {class: "columnXS4 columnSM3"});
                    const qtyLabel = cTag('label', {'for': "qty"});
					qtyLabel.innerHTML = Translate('QTY')+' *:';
                qtyTitle.appendChild(qtyLabel);
			qtyRow.appendChild(qtyTitle);
                const qtyField = cTag('div', {class: "columnXS8 columnSM4"});
					inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'9999', 'data-format':'d', class: 'form-control updatecartfields calculateRefundItemTotal', name: "qty", id: "qty", 'value': qty});
					controllNumericField(inputField, '#errmsg_qty');
                    if(product_type==='Labor/Services') inputField.setAttribute('data-format','d.dd')
                    else preventDot(inputField);
                qtyField.appendChild(inputField);
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
        refundCartForm.appendChild(qtyRow);

			const discountRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"}); 
                const discountColumn = cTag('div', {class: "columnXS4 columnSM3"});
                    const discountLabel = cTag('label', {'for': "discount"});
					discountLabel.innerHTML = Translate('Discount')+' :';
                discountColumn.appendChild(discountLabel);
			discountRow.appendChild(discountColumn);
                const discountField = cTag('div', {class: "columnXS8 columnSM4"});
					const discountInGroup = cTag('div', {class: "input-group"});
						let discountSpan = cTag('span', {class: "input-group-addon cursor", 'style': "min-width: 120px; padding-left: 0; padding-right: 0;"});
							inputField = cTag('input', {id: "discount", name: "discount", 'type': "text",'data-min':'0','data-format':'d.dd', 'data-max': '99.99', 'value': discount, class: "form-control updatecartfields calculateRefundItemTotal", 'style': "min-width: 120px;"});
							controllNumericField(inputField, '#errmsg_discount');
                            inputField.addEventListener('change', calculateRefundItemTotal);
                        discountSpan.appendChild(inputField);
                    discountInGroup.appendChild(discountSpan);
						let currencySpan = cTag('span', {class: "input-group-addon", 'style': "width: 40px; padding-left: 0; padding-right: 0;"});
							const selectPercent = cTag('select', {id: "discount_is_percent", name: "discount_is_percent", class: "form-control bgnone", 'style': "width: 60px; padding-left: 0; padding-right: 0;"});
							selectPercent.addEventListener('change', calculateRefundItemTotal);
								let percentOption = cTag('option', {'value': 1});
								percentOption.innerHTML = '%';
                            selectPercent.appendChild(percentOption);
								let currencyOption = cTag('option', {'value': 0});
								currencyOption.innerHTML = currencyoption;
                            selectPercent.appendChild(currencyOption);
                        currencySpan.appendChild(selectPercent);
                    discountInGroup.appendChild(currencySpan);
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
        refundCartForm.appendChild(discountRow);
        refundCartForm.appendChild(cTag('hr'));

			const totalRow = cTag('div', {class: "flex"});
				const totalValue = cTag('div', {class: "columnSM12", 'align': "right"});
                    bTag = cTag('b');
					bTag.innerHTML = Translate('Total')+' :';
                totalValue.appendChild(bTag);
					bTag = cTag('b', {id: "totalValueStr"});
					bTag.innerHTML = currency+'0.00';
                totalValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "taxable", id: "taxable", 'value': taxable});
                totalValue.appendChild(inputField);
                    inputField = cTag('input', {'type': "hidden", name: "total", id: "total", 'value': 0});
                totalValue.appendChild(inputField);
            totalRow.appendChild(totalValue);
        refundCartForm.appendChild(totalRow);

			const descriptionRow = cTag('div', {class: "flex", 'align': "left"});
                const descriptionColumn = cTag('div', {class: "columnSM3"});
                    const descriptionLabel = cTag('label', {'for': "add_description"});
					descriptionLabel.innerHTML = Translate('Additional Description')+ ':';
                descriptionColumn.appendChild(descriptionLabel);
            descriptionRow.appendChild(descriptionColumn);
				const descriptionField = cTag('div', {class: "columnSM9"});
					const textarea = cTag('textarea', {class: "form-control", name: "add_description", id: "add_description", 'rows': 2, 'cols': 20});
					textarea.innerHTML = add_description;
                descriptionField.appendChild(textarea);
            descriptionRow.appendChild(descriptionField);
        refundCartForm.appendChild(descriptionRow);

			inputField = cTag('input', {'type': "hidden", name: "max_sales_price", id: "max_sales_price", 'value': max_sales_price});
        refundCartForm.appendChild(inputField);
            inputField = cTag('input', {'type': "hidden", name: "max_qty", id: "max_qty", 'value': max_qty});
        refundCartForm.appendChild(inputField);
            inputField = cTag('input', {'type': "hidden", name: "posvalue", id: "posvalue", 'value': pos});
        refundCartForm.appendChild(inputField);
	formhtml.appendChild(refundCartForm);
	
	popup_dialog600(Translate('Update Refund Cart'), formhtml, Translate('Save'), check_updaterefundrow_form);

	setTimeout(function() {
		document.getElementById("sales_price").focus();
		if(item_type==='product' && require_serial_no===0){
			if(document.querySelector("#qty").readOnly){
				document.querySelector("#qty").readOnly = null;
			}
		}
		else{
			if(document.querySelector("#qty").readOnly===false){
				document.querySelector("#qty").readOnly = true;
			}		
		}
		
		document.getElementById("discount_is_percent").value = discount_is_percent;
		calculateRefundItemTotal();
		
		document.querySelectorAll(".calculateRefundItemTotal").forEach(oneFieldObj=>{
			oneFieldObj.addEventListener('keyup', calculateRefundItemTotal);
			oneFieldObj.addEventListener('change', calculateRefundItemTotal);
		});
        applySanitizer(formhtml);
	}, 500);
}

async function check_updaterefundrow_form(hidePopup){
	let pos,errorid;
	pos = document.getElementById("posvalue").value;
	const item_type = document.getElementById("item_type"+pos).value;
	const require_serial_no = parseInt(document.getElementById("require_serial_no"+pos).value);
	const add_description = document.getElementById("add_description").value;

    let sales_price = document.getElementById("sales_price");
    if (!sales_price.valid()) return;

	/* let sales_price = parseFloat(document.getElementById("sales_price").value);
	if(isNaN(sales_price) || sales_price===''){
		sales_price = 0;
	} */
	errorid = document.getElementById("errmsg_sales_price");
	errorid.innerHTML = '';
	
	let qty = parseFloat(document.getElementById("qty").value);
	if(isNaN(qty) || qty===''){
		qty = 0;
	}
	let max_qty = parseFloat(document.getElementById("max_qty").value);
	if(isNaN(max_qty) || max_qty===''){
		max_qty = 0;
	}
	errorid = document.getElementById("errmsg_qty");
	errorid.innerHTML = '';
	if(qty===0 && item_type==='product' && require_serial_no===0){
		errorid.innerHTML = Translate('Missing QTY');
		document.getElementById("qty").focus();
		return false;
	}
	else if(qty>max_qty){
		errorid.innerHTML = Translate('Maximum return QTY')+': '+max_qty;
		return false;
	}
	const discount_is_percent = document.getElementById("discount_is_percent").value;
	if (!document.getElementById('discount').valid()) return;
	
	const jsonData = {};
	jsonData['pos'] = pos;
	jsonData['add_description'] = add_description;
	jsonData['sales_price'] = sales_price.value;
	jsonData['max_qty'] = max_qty;
	jsonData['qty'] = qty;
	jsonData['discount_is_percent'] = discount_is_percent;
	jsonData['discount'] = discount.value;

    const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
    const url = "/Invoices/AJupdate_RefundCart/";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.message !==''){
			alertmessage(data.message, data.editlink);
			document.getElementById("qty"+pos).value = data.maxqty;
		}
		else{
			document.querySelectorAll(".salesrow"+pos).forEach(item=>{
				item.innerHTML = data.returnstr;	
			})
			if(document.querySelectorAll(".errmsg_message_p")){
				document.querySelectorAll(".errmsg_message_p").forEach(oneClassObj=>{
					oneClassObj.innerHTML = '';
				});
			}

			if(document.querySelectorAll(".item_number")){
				document.querySelectorAll(".item_number").forEach(oneRowObj=>{
					oneRowObj.addEventListener('keypress', e => {
						if(e.which === 13) {
							const item_number = e.target.value;
							pos = e.target.title;					
							findItemIdByItemNumber(pos, item_number);
							e.target.focus();
							return false;
						}
					});	
				});
				AJautoComplete_IMEIRefund();
			}
			
			loadRefundCart(pos);
			document.querySelectorAll('[data-toggle="tooltip"]').forEach(node=>tooltip(node));
		}
		calculateRefundTotal();
        hidePopup();
    }
}

async function deletethisrefundrow(pos){
	const jsonData = {};
	jsonData['pos'] = pos;

	const url = "/Invoices/AJremove_RefundCart";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        loadRefundCart(pos);
    }
}

function calculateRefundTotal(){
	let discountvalue;
	let taxable_total = 0;
	let nontaxable_total = 0;
	const hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	if(hasdata.length>10){
		const posarray = document.getElementsByName("pos[]");
		if(posarray.length>0){
			if(document.getElementById("barcodeserno")){
				document.getElementById("barcodeserno").innerHTML = parseInt(posarray.length+1);
			}
			for(let p=0; p<posarray.length; p++){
				const pos = posarray[p].value;
				
				let qty = parseFloat(document.getElementById("qty"+pos).value);
				if(qty==='' || isNaN(qty)){qty=0;}
				
				let sales_price = parseFloat(document.getElementById("sales_price"+pos).value);
				if(sales_price==='' || isNaN(sales_price)){sales_price=0;}
				
				const qty_value = calculate('mul',sales_price,qty,2);
				
				const discount_is_percent = document.getElementById("discount_is_percent"+pos).value;
				let discount = parseFloat(document.getElementById("discount"+pos).value);
				if(discount==='' || isNaN(discount)){discount = 0;}					
				
				if(discount_is_percent>0){
					discountvalue = calculate('mul',qty_value,calculate('mul',0.01,discount,false),2);
				}
				else{ 
					discountvalue = calculate('mul',discount,qty,2);
				}
				
				if(discountvalue==='' || isNaN(discountvalue)){discountvalue = 0;}
				
				let total = calculate('sub',qty_value,discountvalue,2);
				if(total==='' || isNaN(total)){total = 0;}
				
				const taxable = parseFloat(document.getElementById("taxable"+pos).value);
				if(taxable===0){nontaxable_total = calculate('add',nontaxable_total,total,2);}
				else{taxable_total = calculate('add',taxable_total,total,2);}
				
				let rowtotalstr = addCurrency(qty_value);
				if(discountvalue!==0) rowtotalstr += '<br />'+addCurrency(discountvalue*-1);
				
				document.querySelector("#qtystr"+pos).innerHTML = qty;
				document.querySelector("#totalstr"+pos).innerHTML = rowtotalstr;
			}
		}
		else{
			return false;
		}
	}
	else if(document.querySelector("#barcodeserno")){
		document.querySelector("#barcodeserno").innerHTML = 1;
	}
	
	document.getElementById("taxable_totalstr").innerHTML = addCurrency(taxable_total);
	
	const nonTaxRowObj = document.getElementById("nontaxable_totalrow");
	if(nontaxable_total >0 || nontaxable_total <0){
		if(nonTaxRowObj.style.display === 'none'){nonTaxRowObj.style.display = '';}
	}
	else if(nonTaxRowObj.style.display !== 'none'){nonTaxRowObj.style.display = 'none';}

	document.getElementById("nontaxable_totalstr").innerHTML = addCurrency(nontaxable_total);
	
	document.getElementById("taxable_total").value = taxable_total;
	document.getElementById("nontaxable_total").value = nontaxable_total;
	
	/*=========Calculate taxes total value=======*/
	let taxes_percentage1 = parseFloat(document.getElementById("taxes_percentage1").value);
	if(taxes_percentage1==='' || isNaN(taxes_percentage1)){taxes_percentage1 = 0;}			
	let tax_inclusive1 = parseInt(document.getElementById("tax_inclusive1").value);
	if(tax_inclusive1==='' || isNaN(tax_inclusive1)){tax_inclusive1 = 0;}			
	
	let taxes_total1 = calculateTax(taxable_total, taxes_percentage1, tax_inclusive1);		
	document.getElementById("taxes_total1").value = taxes_total1;
	document.getElementById("taxes_total1str").innerHTML = addCurrency(taxes_total1);
	
	let taxes_percentage2 = parseFloat(document.getElementById("taxes_percentage2").value);
	if(taxes_percentage2==='' || isNaN(taxes_percentage2)){taxes_percentage2 = 0;}			
	let tax_inclusive2 = parseInt(document.getElementById("tax_inclusive2").value);
	if(tax_inclusive2==='' || isNaN(tax_inclusive2)){tax_inclusive2 = 0;}			
	
	let taxes_total2 = calculateTax(taxable_total, taxes_percentage2, tax_inclusive2);
	document.getElementById("taxes_total2").value = taxes_total2;
	document.getElementById("taxes_total2str").innerHTML = addCurrency(taxes_total2);
	
	if(tax_inclusive1>0){taxes_total1 = 0;}
	if(tax_inclusive2>0){taxes_total2 = 0;}
	
	const grand_total = calculate('add',calculate('add',taxes_total1,taxes_total2,2),calculate('add',taxable_total,nontaxable_total,2),2);
	document.getElementById("grand_totalstr").innerHTML = addCurrency(grand_total);
	
	document.getElementById("grand_total").value = grand_total;
	
	let receipt_total = parseFloat(document.getElementById("receipt_total").value);
	if(receipt_total==='' || isNaN(receipt_total)){receipt_total = 0;}
	
	let amountdue = grand_total;
	if(amountdue<0){amountdue = 0;}
	document.getElementById("amountdue").value = amountdue;
	document.getElementById("amountduestr").innerHTML = addCurrency(amountdue);
	
	checkrefundmethod();
}

function calculateRefundItemTotal(){
	let errorid,discountvalue;
	let sales_price = parseFloat(document.getElementById("sales_price").value);
	if(sales_price==='' || isNaN(sales_price)){sales_price = 0;}
	
	let max_sales_price = parseFloat(document.getElementById("max_sales_price").value);
	if(max_sales_price==='' || isNaN(max_sales_price)){max_sales_price = 0;}
	
	errorid = document.getElementById("errmsg_sales_price");
	errorid.innerHTML = '';
	if(sales_price>max_sales_price){
		sales_price = max_sales_price;
		errorid.innerHTML = Translate('Maximum price')+': '+max_sales_price;
		document.getElementById("sales_price").value = sales_price;
	}
	document.getElementById("salesPriceStr").innerHTML = addCurrency(sales_price);
	
	let qty = parseFloat(document.getElementById("qty").value);
	if(qty==='' || isNaN(qty)){qty = 0;}
	
	let max_qty = parseFloat(document.getElementById("max_qty").value);
	if(max_qty==='' || isNaN(max_qty)){max_qty = 0;}
	
	errorid = document.getElementById("errmsg_qty");
	errorid.innerHTML = '';
	if(qty===0){
		errorid.innerHTML = Translate('Missing QTY');
	}
	else if(qty>max_qty){
		errorid.innerHTML = Translate('Maximum return QTY')+': '+max_qty;
		document.getElementById("qty").value = qty = max_qty;			
	}
			
	const qty_value = calculate('mul',sales_price,qty,2);
	document.getElementById("qty_value").value = qty_value;
	document.getElementById("qtyValueStr").innerHTML = addCurrency(qty_value);
	
	let discount_is_percent = document.getElementById("discount_is_percent").value;
	let discount = parseFloat(document.getElementById("discount").value);
	if(discount==='' || isNaN(discount)){discount = 0;}					
	
	let discountField = document.getElementById("discount");
	if(discount_is_percent>0){
		discountField.setAttribute('max','99.99');
	} 
	else{
		discountField.removeAttribute('max');
	} 

	
	if(discount_is_percent>0){
		if(discount>99.99){
			document.getElementById("discount").value = 99.99;
			discount = 99.99;
		}
		discountvalue = calculate('mul',qty_value,calculate('mul',0.01,discount,false),2);
	}
	else{ 
		if(discount > qty_value){				
			document.getElementById("discount").value = qty_value;
			discount = sales_price;
		}
		discountvalue = calculate('mul',discount,qty,2);
	}
	
	if(discountvalue==='' || isNaN(discountvalue)){discountvalue = 0;}
	document.getElementById("discountValueStr").innerHTML = addCurrency(discountvalue);
	
	let total = calculate('sub',qty_value,discountvalue,2);
	if(total==='' || isNaN(total)){total = 0;}
	document.getElementById("total").value = total;
	document.getElementById("totalValueStr").innerHTML = addCurrency(total);
}

async function clearReturnCart(invoice_no){
    const url = "/Invoices/clearReturnCart";
    fetchData(afterFetch,url,{});

    function afterFetch(data){
        window.location = '/Invoices/view/'+invoice_no;
    }
}	
	
function checkrefundmethod(){
	const hasdata = document.querySelector("#invoice_entry_holder").innerHTML;
	if(hasdata.length>=10){
		if(document.querySelector("#CompleteBtnDis").style.display !== 'none'){
			document.querySelector("#CompleteBtnDis").style.display = 'none';
		}
		if(document.querySelector("#CompleteBtn").style.display === 'none'){
			document.querySelector("#CompleteBtn").style.display = '';
		}
	}
	else{
		if(document.querySelector("#CompleteBtn").style.display !== 'none'){
			document.querySelector("#CompleteBtn").style.display = 'none';
		}
		if(document.querySelector("#CompleteBtnDis").style.display === 'none'){
			document.querySelector("#CompleteBtnDis").style.display = '';
		}
	}	
}

async function removeIMEIFromRefundCart(pos, singleimei_id){
	const jsonData = {};
	jsonData['pos'] = pos;
	jsonData['singleimei_id'] = singleimei_id;

    const url = "/Invoices/AJremove_RefundCartIMEI";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr==='Ok'){
			loadRefundCart(pos);
		}
    }
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, refund, view};
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item))
});