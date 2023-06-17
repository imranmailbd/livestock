import {
    cTag, Translate, checkAndSetLimit, tooltip, addCurrency,listenToEnterKey, daterange_picker_dialog, checkNumericInputOnKeydown,
    DBDateToViewDate, confirm_dialog, showTopMessage, setOptions, activeLoader, hideLoader, addPaginationRowFlex, 
    checkAndSetSessionData, AJremove_tableRow, popup_dialog600, popup_dialog1000, date_picker, fetchData, addCustomeEventListener, 
    actionBtnClick, serialize, showthisurivalue, multiSelectAction, onClickPagination, wysiwyrEditor, triggerEvent, controllNumericField
} from './common.js'

if(segment2==='') segment2 = 'lists';

function createListRows(tableData){
	let td,tr;

    let company_subdomain = '';
    const domainname = extractRootDomain(window.location.hostname);
    const titleColTitles = ['ID#', 'Loc', 'Sub-domain', 'Name / Country', 'Total Login', 'IMEI 7-30-All', 'Repairs 7-30-All', 'Invoices 7-30-All', 'Payments', 'Plan', 'Last Login'];
    
    const tbody =  document.getElementById('tableRows');
    tbody.innerHTML = '';
    td = '';
    tr = '';
    
    tableData.forEach(function (oneCol){
        tr = cTag('tr');
        let p = 0;
        let t = 1;
        
        oneCol.forEach(function (){
            const page = oneCol[0];
            const accounts_id1 = oneCol[1];
            company_subdomain = oneCol[3];
            let next_payment_due = DBDateToViewDate(oneCol[12]);

            const clickstart = cTag('a',{class:"anchorfulllink", href:"/Admin/edit/"+page+'/'+ accounts_id1, title:"Change User Info"});
            const baseurl = 'http://'+ company_subdomain +'.'+ domainname +'/Account/login';
            const link = cTag('a', {class:"anchorfulllink", 'style': "color: #000;", target:"_blank", href: baseurl, title:"Login Now"})

            if(t>=12){return;}
            let align = 'center';
            let nowrap = '';
            if(p=== 0){align = 'right';}
            if(p=== 2 || p=== 3 || p=== 8){align = 'left';}
            if(p=== 3 || p=== 7 || p=== 8){nowrap = 'nowrap'}
            td = cTag('td', {'nowrap': nowrap, 'data-title': titleColTitles[p], 'align': align});
            if(t===1){
                td.innerHTML = (oneCol[t]);
            }
            else if(t===3){
                link.innerHTML = company_subdomain;
                td.appendChild(link);
            }
            else{
                if(t===9){
                    clickstart.innerHTML = next_payment_due+oneCol[t];
                }
                else if(t===11){
                    clickstart.innerHTML = DBDateToViewDate(oneCol[t]);
                    clickstart.style.width = 'max-content';
                }
                else{
                    clickstart.innerHTML = oneCol[t];
                }
                td.appendChild(clickstart);
            }
            p++;
            t++;
            tr.appendChild(td);
            tbody.appendChild(tr);
        });
    });
}

async function checkLanguage(){  
    const jsonData = {};
    const url = "/Admin/checkLanguage";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        showTopMessage('alert_msg', data.returnStr);
    }
}

function getNavSection(){
    let secondaryNavFlex = cTag('div',{'id':'adminNavButton','class':'flexEndRow'});
        const aTag = cTag('a',{ 'href':`javascript:void(0);`,'id':`navToggle` });
        aTag.appendChild(cTag('i',{ 'class':`fa fa-align-justify`, 'style': "color: #000; font-size: 2em;" }));
        aTag.addEventListener('click',function(){
            document.querySelector('#adminNavButton .navBar').classList.toggle('hideNavBar');
        });
    secondaryNavFlex.appendChild(aTag);
    let linkstr;
        const ulNavBar = cTag('ul',{ 'class':'navBar hideNavBar' });
            let value = {'our_notes':Translate('All Notes'),'invoicesReport':Translate('Invoices Report'), 'popup_message':Translate('Popup Message'), 'login_message':Translate('Login Message'), 'languages':Translate('Languages'), 'checkLanguage':Translate('Check Language'), 'lists':Translate('Manage Accounts')}
            for(const [module, moduletitle] of Object.entries(value)) { 
                let activeclass = '';
                if(segment2 === module){
                    activeclass = "active";
                }
                const liNavBar = cTag('li');

                    if(module=='checkLanguage'){
                        linkstr = cTag('a',{ class: 'navItem '+activeclass,'title':moduletitle });
                        linkstr.addEventListener('click',checkLanguage);
                    }
                    else{
                        linkstr = cTag('a',{ class: 'navItem '+activeclass,'href':'/Admin/'+module,'title':moduletitle });
                    }
                    if(module==='lists') linkstr.append(cTag('i',{'class':"fa fa-list", 'style': "margin-right: 5px;"}));
                    let titleSpan = cTag('span');
                    titleSpan.innerHTML = moduletitle;
                    linkstr.appendChild(titleSpan);
                liNavBar.appendChild(linkstr)
                ulNavBar.appendChild(liNavBar);
            }
    secondaryNavFlex.appendChild(ulNavBar);
    return secondaryNavFlex;
}

//=========lists==========
function lists(){
    let keyword_search = '';
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';

    let additionCond,ssorting_type,sorTypOpt,addConOpt,selected,list_filters;
    additionCond = '';
    ssorting_type = '';

    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    
    const rowHeight = 35;
    const totalRows = 0;

    sorTypOpt = '';
	const sorTypOpts = {
        '0':'New First',
        '1':'Old First',
        '2':'Name ASC',
        '3':'Name DESC',
        '4':'Company ASC',
        '5':'Company DESC',
        '6':'Email ASC',
        '7':'Email DESC',
        '8':'Last Login',
        '9':'Next Payment Due',
    };
                
        const callOutDiv = cTag('div',{'class': 'innerContainer', style: "background:#FFF;"});
        callOutDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`pageURI`,'id':`pageURI`,'value':segment1+ '/' + segment2 }));
        callOutDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`page`,'id':`page`,'value':page }));
        callOutDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`rowHeight`,'id':`rowHeight`,'value':rowHeight }));
        callOutDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`totalTableRows`,'id':`totalTableRows`,'value':totalRows }));
        callOutDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value': segment2 }));
            const titleRow = cTag('div',{ 'class':`columnXS12 flexSpaBetRow outerListsTable` });
                const manageAccountHeader = cTag('h2');
                manageAccountHeader.innerHTML = 'Manage Accounts ';
                manageAccountHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title': Translate('This page displays a list of Open and Closed purchase orders') }));
            titleRow.appendChild(manageAccountHeader);
            titleRow.appendChild(getNavSection());
        callOutDiv.appendChild(titleRow);

            const dropDownRow = cTag('div', {class: "flexEndRow outerListsTable"});                     
                const allAccountColumn = cTag('div', {class: "columnXS6 columnSM3"});
                    let selectAccountType = cTag('select',{ 'class':`form-control`,'name':`additionCond`,'id':`additionCond`});
                    selectAccountType.addEventListener('change',filter_Admin_lists);
                    addConOpt = {'0' : 'All Accounts','1': 'Free Accounts Only','2':'Exclude Free Accounts','3':'Accounts Past Due','5':'Only paid by Others','6':'Only Coupon'};
                    for(const [optValue, optLabel] of Object.entries(addConOpt)) { 
                        selected = '';
                        if(additionCond === optValue){selected = ' selected';console.log(additionCond);}
                        addConOpt = cTag('option',{value: optValue});
                        addConOpt.setAttribute('option',selected);
                            addConOpt.innerHTML = optLabel;
                        selectAccountType.appendChild(addConOpt);
                    }
                allAccountColumn.appendChild(selectAccountType);
            dropDownRow.appendChild(allAccountColumn);

                const statusColumn = cTag('div', {class: "columnXS6 columnSM3"});
                    let selectStatus = cTag('select',{ 'class':`form-control `,'name':`sstatus`,'id':`sstatus` });
                    selectStatus.addEventListener('change',filter_Admin_lists);
                statusColumn.appendChild(selectStatus);
            dropDownRow.appendChild(statusColumn);

                const sortingColumn = cTag('div', {class: "columnXS6 columnSM3"});
                    let selectSorting = cTag('select',{ 'class':`form-control `,'name':`ssorting_type`,'id':`ssorting_type`});
                    selectSorting.addEventListener('change',filter_Admin_lists);
                    for(const [optValue, optLabel] of Object.entries(sorTypOpts)) { 
                        selected = '';
                        if(ssorting_type === optValue){selected = ' selected';console.log(ssorting_type);}
                        sorTypOpt = cTag('option',{value: optValue});
                        sorTypOpt.setAttribute('option',selected);
                            sorTypOpt.innerHTML = optLabel;
                        selectSorting.appendChild(sorTypOpt);
                    }
                sortingColumn.appendChild(selectSorting);
            dropDownRow.appendChild(sortingColumn);

                const searchColumn = cTag('div', {class: "columnXS6 columnSM3"});
                    const searchInGroup = cTag('div', {class: "input-group"}); 
                    searchInGroup.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Admin_lists),'type':`text`,'placeholder':`ID/Sub-Domain/Name/Notes...`,'id':`keyword_search`,'name':`keyword_search`,'class':`form-control`,'maxlength':`50`}));
                        let searchSpan = cTag('span',{ 'class':`input-group-addon cursor` });
                        searchSpan.addEventListener('click',filter_Admin_lists)
                        searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                    searchInGroup.appendChild(searchSpan);
                searchColumn.appendChild(searchInGroup);
            dropDownRow.appendChild(searchColumn);
        callOutDiv.appendChild(dropDownRow);

            const adminListRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const adminListColumn = cTag('div',{ 'class':`columnXS12`,'style':`position:relative;` });
                    const noMoreTables = cTag('div',{ 'id':`no-more-tables` });
                        const adminTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                            const adminHead = cTag('thead',{ 'class':`cf` });
                                const adminHeadRow = cTag('tr');
                                    const thCol0 = cTag('th',{ 'width':`3%`,'align':`center` });
                                    thCol0.innerHTML = 'ID#';

                                    const thCol1 = cTag('th',{ 'align':`center`, 'width':`50px;` });
                                    thCol1.innerHTML = 'Loc';

                                    const thCol2 = cTag('th',{ 'width':`15%`,'align':`left` });
                                    thCol2.innerHTML = 'Sub-domain';

                                    const thCol3 = cTag('th',{ 'align':`left` });
                                    thCol3.innerHTML = 'Name / Country';

                                    const thCol4 = cTag('th',{ 'width':`8%`, 'style': "text-align: center;" });
                                    thCol4.append('Log In');

                                    const thCol5 = cTag('th',{ 'width':`8%`, 'style': "text-align: center;" });
                                    thCol5.append(Translate('IMEI'));

                                    const thCol6 = cTag('th',{ 'width':`8%`, 'style': "text-align: center;" });
                                    thCol6.append(Translate('Repairs'));

                                    const thCol7 = cTag('th',{ 'width':`8%`, 'style': "text-align: center;" });
                                    thCol7.append(Translate('Invoices'));

                                    const thCol8 = cTag('th',{ 'width':`8%`, 'style': "text-align: center;" });
                                    thCol8.innerHTML = Translate('Payments');

                                    const thCol9 = cTag('th',{ 'width':`8%`, 'style': "text-align: center;" });
                                    thCol9.innerHTML = 'Plan';

                                    const thCol10 = cTag('th',{ 'width':`8%`, 'style': "text-align: center;" });
                                    thCol10.innerHTML = 'Last Login';
                                adminHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8, thCol9, thCol10);
                            adminHead.appendChild(adminHeadRow);
                        adminTable.appendChild(adminHead);
                            const adminBody = cTag('tbody',{ 'id':`tableRows` });
                        adminTable.appendChild(adminBody);
                    noMoreTables.appendChild(adminTable);
                adminListColumn.appendChild(noMoreTables);
            adminListRow.appendChild(adminListColumn);
        callOutDiv.appendChild(adminListRow);
    Dashboard.appendChild(callOutDiv);
    addPaginationRowFlex(Dashboard);

    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}

    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    ssorting_type = document.getElementById('ssorting_type').value;
    checkAndSetSessionData('ssorting_type', ssorting_type, list_filters);
    const sstatus = document.getElementById('sstatus').value
    checkAndSetSessionData('sstatus', sstatus, list_filters);
    additionCond = document.getElementById('additionCond').value
    checkAndSetSessionData('additionCond', additionCond, list_filters);

    addCustomeEventListener('filter',filter_Admin_lists);
    addCustomeEventListener('loadTable',loadTableRows_Admin_lists);
    filter_Admin_lists();
}

async function filter_Admin_lists(){
    let page = 1;    
    const limit = checkAndSetLimit();
    document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById('ssorting_type').value;
	const sstatus = document.getElementById('sstatus').value;
	jsonData['sstatus'] = sstatus;
	jsonData['additionCond'] = document.getElementById('additionCond').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
    let url = "/Admin/AJgetPage_lists/filter";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        sessionStorage.setItem('list_filters', JSON.stringify(jsonData));
        
        const select = document.querySelector("#sstatus");
        select.innerHTML = '';
            const option = cTag('option', {value:''});
            option.innerHTML = 'All Status';
        select.appendChild(option);
        setOptions(select,data.staOpt, 0, 0);

        createListRows(data.tableData);           

        document.getElementById("totalTableRows").value = data.totalRows;
        document.getElementById("sstatus").value = sstatus;
        onClickPagination();
    }
}

async function loadTableRows_Admin_lists(){
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById('ssorting_type').value;
	jsonData['sstatus'] = document.getElementById('sstatus').value;
	jsonData['additionCond'] = document.getElementById('additionCond').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = "/Admin/AJgetPage_lists";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        sessionStorage.setItem('list_filters', JSON.stringify(jsonData));
        createListRows(data.tableData);            
        onClickPagination();
    }
}

//=============edit=========
function edit(){
    let page, tableHeadRow, thCol;
    page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    const rowHeight = 60;
    const totalRows = 0;
    const accounts_id = segment4;
    const invoice_number = '';

    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const callOutDiv = cTag('div',{ 'class':`innerContainer`, style: `background:#FFF;`});
            let navigationColumn = cTag('div',{ 'class':`columnSM12` });
            navigationColumn.appendChild(getNavSection());
        callOutDiv.appendChild(navigationColumn);
            let informationRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const informationColumn = cTag('div',{ 'class':`columnSM12` });
                    const infoWidget = cTag('div',{ 'class':`cardContainer` });
                        let widgetHeader = cTag('div',{ 'class':`cardHeader` });
                            let widgetRows = cTag('div',{ 'class':`flexSpaBetRow` });
                                let headerRows = cTag('div',{ 'class':`columnSM6`, 'style': "margin: 0px; padding-left: 10px;" });
                                    const headerTitle = cTag('h3');
                                    headerTitle.innerHTML = 'Accounts Information '
                                    headerTitle.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':`Accounts information details` }));
                                headerRows.appendChild(headerTitle);
                            widgetRows.appendChild(headerRows);
                                let buttonsRows = cTag('div',{ 'class':`columnSM6`, 'style': "text-align: end; margin: 0px;" });
                                    let logInButton = cTag('button',{ 'target':`_blank`,'class':`btn defaultButton`, id: 'login_Account' });
                                    logInButton.innerHTML = 'Login Account';

                                    let listButton = cTag('button',{ 'class':`btn defaultButton`, 'style': "margin-right: 15px;"});
                                    listButton.addEventListener('click',function(){
                                        window.location = '/Admin/lists/' + segment3;
                                    })
                                    listButton.innerHTML = 'Accounts List';
                                buttonsRows.append(listButton, logInButton);
                            widgetRows.appendChild(buttonsRows);
                        widgetHeader.appendChild(widgetRows);
                    infoWidget.appendChild(widgetHeader);

                        let widgetContent = cTag('div',{ 'class':`cardContent` });
                            let widgetContentRow = cTag('div',{ 'class':`flexSpaBetRow`, 'style': "align-items: flex-start;", id: 'tableRow'});
                        widgetContent.appendChild(widgetContentRow);
                    infoWidget.appendChild(widgetContent);
                            let buttonsRow = cTag('div',{ 'class':`flexSpaBetRow` });
                                const buttonsColumn = cTag('div',{ 'class':`columnXS12`,'align':`center` });
                                    let editButton = cTag('button',{ 'class':`btn editButton`, 'style': "margin-right: 15px; margin-top: 2px;", id:'edit' });
                                    editButton.addEventListener('click',AJget_AccountsPopup);
                                    editButton.innerHTML = Translate('Edit');
                                buttonsColumn.appendChild(editButton);
                                    let resetButton = cTag('button',{ 'class':`btn editButton`, 'style': "margin-right: 15px; margin-top: 2px;" });
                                    resetButton.addEventListener('click',AJreset_AccountsPopup)
                                    resetButton.innerHTML = 'Reset';
                                buttonsColumn.appendChild(resetButton);
                                    let removeButton = cTag('button',{'class':`btn archiveButton`, 'style': "margin-right: 15px; margin-top: 2px;" });
                                    removeButton.addEventListener('click',AJremove_AccountsPopup);
                                    removeButton.innerHTML = Translate('Remove');
                                buttonsColumn.appendChild(removeButton);
                                    let paymentButton = cTag('button',{ 'class':`btn saveButton`, 'style': "margin-right: 15px; margin-top: 2px;", id: 'Paypal_Payment' });
                                    paymentButton.innerHTML = 'Paypal Payment';
                                buttonsColumn.appendChild(paymentButton);
                                    let popUpButton = cTag('button',{ 'class':`btn saveButton`, 'style': "margin-right: 15px; margin-top: 2px;" });
                                    popUpButton.addEventListener('click',AJget_AccountsMessage);
                                    popUpButton.innerHTML = 'Popup Message';
                                buttonsColumn.appendChild(popUpButton);
                                    let customerButton = cTag('button',{ 'class':`btn saveButton`, 'style': "margin-right: 15px; margin-top: 2px;", id: 'importCustomers' });
                                    customerButton.innerHTML = Translate('Import Customers');
                                buttonsColumn.appendChild(customerButton);
                                    let productButton = cTag('button',{ 'class':`btn saveButton`, 'style': "margin-right: 15px; margin-top: 2px;", id: 'importProduct' });
                                    productButton.innerHTML = Translate('Import Products');
                                buttonsColumn.appendChild(productButton);
                                    const popup_messagedata = cTag('div',{style:'display:none', id:"popup_messagedata"});
                                buttonsColumn.appendChild(popup_messagedata);
                            buttonsRow.appendChild(buttonsColumn);
                        widgetContent.appendChild(buttonsRow);
                    infoWidget.appendChild(widgetContent);
                informationColumn.appendChild(infoWidget);
            informationRow.appendChild(informationColumn);
        callOutDiv.appendChild(informationRow)

            const noteRow = cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-bottom: 10px;" });
                const noteColumn = cTag('div',{ 'class':`columnXS12`,'style':`position:relative;` });
                    let noMoreTables = cTag('div',{ 'id':`no-more-tables` });
                        let noteTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                            let noteTableHead = cTag('thead',{ 'class':`cf` });
                                 tableHeadRow = cTag('tr');
                                    thCol = cTag('th',{ 'align':`left`,'width':`15%` });
                                    thCol.innerHTML = Translate('Date');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th');
                                        let buttonName = cTag('div',{ 'class': "flexSpaBetRow" });
                                            let span = cTag('span');
                                            span.innerHTML = 'Note Description';
                                        buttonName.appendChild(span);
                                            let noteButton = cTag('a',{ 'href':`javascript:void(0);`,'class':`btn defaultButton`, 'style': "font-weight: normal;" });
                                            noteButton.addEventListener('click',function(){
                                                AJget_OurNotes(0);
                                            })
                                            noteButton.innerHTML = Translate('Add New Note');
                                        buttonName.appendChild(noteButton);
                                    thCol.appendChild(buttonName);
                                tableHeadRow.appendChild(thCol);
                            noteTableHead.appendChild(tableHeadRow);
                        noteTable.appendChild(noteTableHead);
                            let noteBody = cTag('tbody',{ 'id':`showNoteDescription` });
                        noteTable.appendChild(noteBody);
                    noMoreTables.appendChild(noteTable);
                noteColumn.appendChild(noMoreTables);
            noteRow.appendChild(noteColumn);
        callOutDiv.appendChild(noteRow)

            let accountInvoiceRow = cTag('div',{ 'class':`flexSpaBetRow` });
            accountInvoiceRow.appendChild(cTag('input',{ 'type':`hidden`,'name':`pageURI`,'id':`pageURI`,'value':segment1+'/'+segment2+'/'+segment3+'/'+segment4 }));
            accountInvoiceRow.appendChild(cTag('input',{ 'type':`hidden`,'name':`page`,'id':`page`,'value':page }));
            accountInvoiceRow.appendChild(cTag('input',{ 'type':`hidden`,'name':`rowHeight`,'id':`rowHeight`,'value':rowHeight }));
            accountInvoiceRow.appendChild(cTag('input',{ 'type':`hidden`,'name':`totalTableRows`,'id':`totalTableRows`,'value': totalRows }));
            accountInvoiceRow.appendChild(cTag('input',{ 'type':`hidden`,'name':`saccounts_id`,'id':`saccounts_id`,'value': accounts_id }));
                let accountInvoiceColumn = cTag('div',{ 'class':`columnXS12 columnSM6`,'align':`left` });
                    let accountInvoiceHeader = cTag('h4', {'style': "font-size: 18px;"});
                    accountInvoiceHeader.append('Account Invoice Information ');
                    accountInvoiceHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':`Account Invoice Information` }));
                accountInvoiceColumn.appendChild(accountInvoiceHeader);
            accountInvoiceRow.appendChild(accountInvoiceColumn);
                const searchColumn = cTag('div',{ 'class':`flexEndRow columnXS12 columnSM6`, 'style': "align-self: center;" });
                    let searchInput = cTag('div',{ 'class':`input-group` });
                    searchInput.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Admin_edit),'type':`text`,'placeholder':Translate('Search Invoice Number'),'value': invoice_number,'id':`invoice_number`,'name':`invoice_number`,'class':`form-control`,'maxlength':`50` }));
                        let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search Invoice Number') });
                        searchSpan.addEventListener('click',filter_Admin_edit);
                        searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                    searchInput.appendChild(searchSpan);
                searchColumn.appendChild(searchInput);
            accountInvoiceRow.appendChild(searchColumn);
        callOutDiv.appendChild(accountInvoiceRow)

            const infoInvoiceRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const infoInvoiceColumn = cTag('div',{ 'class':`columnXS12`,'style':`position:relative;` });
                    const noMoreTable = cTag('div',{ 'id':`no-more-tables` });
                        let infoInvoiceTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                            let infoInvoiceHead = cTag('thead',{ 'class':`cf` });
                                tableHeadRow = cTag('tr');
                                    const thCol0 = cTag('th',{ 'align':`left`,'width':`5%` });
                                    thCol0.innerHTML = Translate('Date');

                                    const thCol1 = cTag('th',{ 'align':`left`,'width':`3%` });
                                    thCol1.innerHTML = Translate('Invoice');
                                    
                                    const thCol3 = cTag('th',{ 'align':`left` });
                                    thCol3.innerHTML = Translate('Description');

                                    const thCol4 = cTag('th',{ 'align':`left`,'width':`10%` });
                                    thCol4.innerHTML = Translate('Paid By');

                                    const thCol5 = cTag('th',{ 'align':`left`,'width':`5%` });
                                    thCol5.innerHTML = 'Price/Location';

                                    const thCol6 = cTag('th',{ 'align':`left`,'width':`5%` });
                                    thCol6.innerHTML = Translate('Locations');

                                    const thCol7 = cTag('th',{ 'align':`left`,'width':`8%` });
                                    thCol7.innerHTML = Translate('Total');

                                    const thCol8 = cTag('th',{ 'align':`left`,'width':`8%` });
                                    thCol8.innerHTML = Translate('Next Payment Due');

                                    const thCol9 = cTag('th',{ 'align':`left`,'width':`3%` });
                                    thCol9.innerHTML = Translate('Print');
                                tableHeadRow.append(thCol0, thCol1, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8, thCol9);
                            infoInvoiceHead.appendChild(tableHeadRow);
                        infoInvoiceTable.appendChild(infoInvoiceHead);
                            let infoInvoiceBody = cTag('tbody',{ 'id':`tableRows` });
                        infoInvoiceTable.appendChild(infoInvoiceBody);
                    noMoreTable.appendChild(infoInvoiceTable);
                infoInvoiceColumn.appendChild(noMoreTable);
            infoInvoiceRow.appendChild(infoInvoiceColumn);
        callOutDiv.appendChild(infoInvoiceRow)
            page = cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-bottom: 10px;" });
                const colsm = cTag('div',{ class:"columnXS5", 'style': "padding-top: 5px;"})
            page.appendChild(colsm)
        callOutDiv.appendChild(page);
    Dashboard.appendChild(callOutDiv)
    addPaginationRowFlex(colsm);

    addCustomeEventListener('filter',loadData_Admin_edit);
    addCustomeEventListener('loadTable',loadTableRows_Admin_edit);
    AJ_edit_MoreInfo();
}

async function AJ_edit_MoreInfo(){
    const jsonData = {};
    jsonData['accounts_id'] = segment4;
    const url = '/'+segment1+'/AJ_edit_MoreInfo/'+segment4;

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const infoRow = document.getElementById('tableRow')
            let infoColumn = cTag('div',{ 'class':`customInfoGrid columnXS12 columnSM6`, 'style': "padding-bottom: 10px;" });
                let accountIdLabel = cTag('label');
                accountIdLabel.innerHTML = Translate('Account ID')+' : ';
                let accountId = cTag('span');
                accountId.innerHTML = data.accounts_id;
            infoColumn.append(accountIdLabel, accountId);

                let companyNameLabel = cTag('label');
                companyNameLabel.innerHTML = Translate('Company Name')+' : ';
                let companyName = cTag('span', {'id':`company_nameStr`});
                companyName.innerHTML = data.company_name;
            infoColumn.append(companyNameLabel, companyName);

                let subDomainLabel = cTag('label');
                subDomainLabel.innerHTML = Translate('Sub-Domain')+' : ';
                let subDomain = cTag('span');
                subDomain.innerHTML = data.company_subdomain;
            infoColumn.append(subDomainLabel, subDomain);

                let addressLabel = cTag('label');
                addressLabel.innerHTML = Translate('Company Address')+' : ';
                let addressValue = cTag('span');
                addressValue.innerHTML = data.address;
            infoColumn.append(addressLabel, addressValue);

                let phoneLabel = cTag('label');
                phoneLabel.innerHTML = Translate('Phone Number')+' : ';
                let phoneNo = cTag('span');
                phoneNo.innerHTML = data.company_phone_no;
            infoColumn.append(phoneLabel, phoneNo);

                let emailLabel = cTag('label');
                emailLabel.innerHTML = Translate('Email')+' : ';
                let userEmail = cTag('span');
                userEmail.innerHTML = data.user_email;
            infoColumn.append(emailLabel, userEmail);

                let paypalLabel = cTag('label');
                paypalLabel.innerHTML = Translate('Paypal ID')+' : ';
                let paypalSpan = cTag('span');
                paypalSpan.innerHTML = data.paypal_id;
            infoColumn.append(paypalLabel, paypalSpan);

                let locationLabel = cTag('label');
                locationLabel.innerHTML = Translate('Location info')+' : ';
                let locationSpan = cTag('span');
                locationSpan.innerHTML = data.locationInfo;
            infoColumn.append(locationLabel, locationSpan);

                let lastloginLabel = cTag('label');
                lastloginLabel.innerHTML = Translate('Last Login')+' : ';
                let lastloginSpan = cTag('span');
                lastloginSpan.innerHTML = DBDateToViewDate(data.last_login);
            infoColumn.append(lastloginLabel, lastloginSpan);

                let createdLabel = cTag('label');
                createdLabel.innerHTML = Translate('Created on')+' : ';
                let createdSpan = cTag('span');
                createdSpan.innerHTML = DBDateToViewDate(data.created_on);
            infoColumn.append(createdLabel, createdSpan);
        infoRow.appendChild(infoColumn);

            let infoColumn2 = cTag('div',{ 'class':`customInfoGrid columnXS12 columnSM6`, 'style': "border-left: 1px solid #CCC; padding-bottom: 10px; padding-left: 10px;" });
                let statusLabel = cTag('label');
                statusLabel.innerHTML = Translate('Status')+' : ';
                let statusSpan = cTag('span');
                statusSpan.innerHTML = data.status;
            infoColumn2.append(statusLabel, statusSpan);

                let dateLabel = cTag('label');
                dateLabel.innerHTML = Translate('Status Date')+' : ';
                let dateSpan = cTag('span');
                dateSpan.innerHTML = DBDateToViewDate(data.status_date);
            infoColumn2.append(dateLabel, dateSpan);

                let trialLabel = cTag('label');
                trialLabel.innerHTML = Translate('Trial Days')+' : ';
                let trailSpan = cTag('span');
                trailSpan.innerHTML = data.trial_days;
            infoColumn2.append(trialLabel, trailSpan);
                if(data.No_of_Location>1){
                        let locationLabel = cTag('label');
                        locationLabel.innerHTML = Translate('No of Location')+' : ';
                        let locationSpan = cTag('span');
                        locationSpan.innerHTML = data.No_of_Location;
                    infoColumn2.append(locationLabel, locationSpan);
                }
                    let frequencyLabel = cTag('label');
                    frequencyLabel.innerHTML = Translate('Pay Frequency')+' : ';
                    let frequencySpan = cTag('span');
                    frequencySpan.innerHTML = data.pay_frequency;
                infoColumn2.append(frequencyLabel, frequencySpan);

                    let priceLabel = cTag('label');
                    priceLabel.innerHTML = Translate('Price per account')+' : ';
                    let priceSpan = cTag('span');
                    priceSpan.innerHTML = currency + data.price_per_location;
                infoColumn2.append(priceLabel, priceSpan);

                if(data.No_of_Location>1){
                        let totalLabel = cTag('label');
                        totalLabel.innerHTML = Translate('Total')+' : ';
                        let totalSpan = cTag('span');
                        totalSpan.innerHTML = addCurrency(data.price_per_location*data.No_of_Location);
                    infoColumn2.append(totalLabel, totalSpan);
                }

                    let nextPaymentLabel = cTag('label');
                    nextPaymentLabel.innerHTML = Translate('Next Payment Due')+' : ';
                    let nextPaymentSpan = cTag('span');
                    nextPaymentSpan.innerHTML = DBDateToViewDate(data.next_payment_due);
                infoColumn2.append(nextPaymentLabel, nextPaymentSpan);

                    let couponLabel = cTag('label');
                    couponLabel.innerHTML = Translate('Coupon Code')+' : ';
                    let couponSpan = cTag('span');
                    couponSpan.innerHTML = data.coupon_code;
                infoColumn2.append(couponLabel, couponSpan);
                
            let msg = (new URLSearchParams(document.location.search)).get('msg');
            if(msg){
                let msgField = cTag('h3',{style:'padding: 10px;font-weight: bold;background: lightgray;margin-top: 20px;color: #dc3545'});
                msgField.innerHTML = msg;
                infoColumn2.appendChild(msgField);
            }
        infoRow.appendChild(infoColumn2)
            
        const OUR_DOMAINNAME = extractRootDomain(window.location.hostname);
        const login_Account =  document.getElementById('login_Account');
        const link = 'http://'+ data.company_subdomain + '.' + OUR_DOMAINNAME + '/Account/login';
        login_Account.addEventListener('click',function(){
            window.open(link)
        })
        
        let paypalPaymentButton =  document.getElementById('Paypal_Payment');
        paypalPaymentButton.addEventListener('click',function(){
            AJget_PaypalPayment(data.paypal_id, data.next_payment_due, data.nextnext_payment_due)
        });

        document.getElementById('popup_messagedata').innerHTML = Translate(data.popup_message);

        let importCustomerButton =  document.getElementById('importCustomers');
        importCustomerButton.addEventListener('click',function(){
            window.location = '/Admin/importCustomers/accountsId/' + data.accounts_id;
        });

        let importProductButton =  document.getElementById('importProduct');
        importProductButton.addEventListener('click',function(){
            window.location = '/Admin/importProduct/accountsId/' + data.accounts_id
        });     
        filter_Admin_edit();   
        loadData_Admin_edit();    
    }
}

async function loadData_Admin_edit(){
	const accounts_id = document.getElementById("saccounts_id").value;

	const jsonData = {};
	jsonData['accounts_id'] = accounts_id;
	jsonData['fromPage'] = 'Admin';
    const url = '/Admin/showNoteDescription/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let tableHeadRow,tdCol;
        const tbody = document.getElementById("showNoteDescription");
        tbody.innerHTML = '';
        const tabledata = data.tabledata;

        if(tabledata.length>0){
            tabledata.forEach(function (oneRow){
                tableHeadRow = cTag('tr');
                    tdCol =  cTag('td',{ 'data-title':Translate('Date'), align:"center"});
                    tdCol.innerHTML = DBDateToViewDate(oneRow[1]);
                tableHeadRow.appendChild(tdCol);
                    tdCol =  cTag('td',{ 'data-title':Translate('Description'), align:"left"});
                    tdCol.innerHTML = oneRow[2];
                        const iTag = cTag('i',{ class:'fa fa-edit cursor', 'data-original-title':'Edit Note' });
                        iTag.addEventListener('click',()=>AJget_OurNotes(oneRow[0]));
                    tdCol.append(' ', iTag);
                tableHeadRow.appendChild(tdCol);
            tbody.appendChild(tableHeadRow);
            });
        }
        else{
            tableHeadRow = cTag('tr');
                tdCol = cTag('td',{colspan:"11", 'style': "color: #ae2222;"});
                tdCol.innerHTML = '';
            tableHeadRow.appendChild(tdCol);
        tbody.appendChild(tableHeadRow)
        }
    }				
}

async function filter_Admin_edit(){
    let page = 1;    
    document.getElementById("page").value = page;
    const limit = checkAndSetLimit();

	const jsonData = {};
	jsonData['saccounts_id'] = document.getElementById('saccounts_id').value;
	jsonData['invoice_number'] = document.getElementById('invoice_number').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;
    
    const url = "/Admin/AJgetPage_edit/filter";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let tr,td;
        const tbody = document.getElementById("tableRows");
        tbody.innerHTML = '';
        const tableRows = data.tableRows;
        const titleColTitles = ['Date', 'Invoice', 'Transaction ID', 'Description', 'Paid By', 'Price per Location', 'No of Location', 'Total', 'Next Payment Due', 'Print Invoice'];
        if(tableRows.length>0){
            tableRows.forEach(function (oneRow){
                tr = cTag('tr');
                let p = 0;
                oneRow.forEach(function (oneCol){
                    const align = 'center';
                    td = cTag('td', {'data-title': titleColTitles[p++], 'align': align});
                        if(p==1 && oneCol !=='Unpaid'){td.innerHTML = DBDateToViewDate(oneCol);}
                        else if(p==9){td.innerHTML = DBDateToViewDate(oneCol);}
                        else{td.innerHTML = oneCol;}                            
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }
        else{
            tr = cTag('tr');
                td = cTag('td',{colspan:"11", 'style': "color: #ae2222;"});
                td.innerHTML = '';
            tr.appendChild(td);
        tbody.appendChild(tr)
        }
        onClickPagination();
    }
}

async function loadTableRows_Admin_edit(){
	const jsonData = {};
	jsonData['saccounts_id'] = document.getElementById('saccounts_id').value;
	jsonData['invoice_number'] = document.getElementById('invoice_number').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = "/Admin/AJgetPage_edit";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){           
        const tbody = document.getElementById("tableRows");
        tbody.innerHTML = '';
        const tableRows = data.tableRows;
        const titleColTitles = ['Date', 'Invoice', 'Transaction ID', 'Description', 'Paid By', 'Price per Location', 'No of Location', 'Total', 'Next Payment Due', 'Print Invoice'];
        
        if(tableRows.length>0){
            tableRows.forEach(function (oneRow){
                let tr = cTag('tr');
                let p = 0;
                oneRow.forEach(function (oneCol){
                    const align = 'center';
                    let td = cTag('td', {'data-title': titleColTitles[p++], 'align': align});
                        if(p==1 && oneCol !=='Unpaid'){td.innerHTML = DBDateToViewDate(oneCol);}
                        else if(p==9){td.innerHTML = DBDateToViewDate(oneCol);}
                        else{td.innerHTML = oneCol;}                            
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }
        else{
                const tr = cTag('tr');
                    const td = cTag('td',{colspan:"11", 'style': "color: #ae2222;"});
                    td.innerHTML = '';
                tr.appendChild(td);
            tbody.appendChild(tr);
        }
        onClickPagination();
    }
}

async function AJget_AccountsPopup(){
	const accounts_id = document.getElementById("saccounts_id").value;
	const jsonData = {};
	jsonData['accounts_id'] = accounts_id;
    const url = "/Admin/AJget_AccountsPopup";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let dateVal, inputField, requiredField;
        const accountForm = cTag('form', {'action': "#", name: "frmAccounts", id: "frmAccounts", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
        accountForm.addEventListener('submit', AJsave_Accounts);
            
            let errorLabel = cTag('span', {class:'errormsg', id:'showErrorMsg'});
        accountForm.appendChild(errorLabel);

            const companyRow = cTag('div',{class: "flex", 'align': "left"});
                const companyTitle = cTag('div', {class: "columnSM4"});
                    const companyLabel = cTag('label');
                    companyLabel.innerHTML = Translate('Company Name')+':';
                companyTitle.appendChild(companyLabel);
            companyRow.appendChild(companyTitle);
                const companyField = cTag('div', {class: "columnSM8"});
                companyField.innerHTML = Translate(data.company_name)
            companyRow.appendChild(companyField);
        accountForm.appendChild(companyRow);

            const subDomainRow = cTag('div',{class: "flex", 'align': "left"});
                const subDomainTitle = cTag('div', {class: "columnSM4"});
                    const subDomainLabel = cTag('label', {'for': "company_subdomain"});
                    subDomainLabel.innerHTML = Translate('Sub-Domain');
                        requiredField = cTag('span', {class: "required"});
                        requiredField.innerHTML = '*';
                    subDomainLabel.appendChild(requiredField);
                subDomainTitle.appendChild(subDomainLabel);
            subDomainRow.appendChild(subDomainTitle);
                const subDomainField = cTag('div', {class: "columnSM8"});
                    inputField = cTag('input', {'required': "required", 'maxlength': 30, 'type': "text", class: "form-control", name: "company_subdomain", id: "name", 'value': data.company_subdomain});
                    inputField.addEventListener('keyup', showthisurivalue);
                subDomainField.appendChild(inputField);
                    let errorSpan = cTag('span', {id: "error_company_subdomain", class: "errormsg"});
                subDomainField.appendChild(errorSpan);
            subDomainRow.appendChild(subDomainField);
        accountForm.appendChild(subDomainRow);

            const emailRow = cTag('div',{class: "flex", 'align': "left"});
                const emailTitle = cTag('div', {class: "columnSM4"});
                    const emailLabel = cTag('label', {'for': "user_email"});
                    emailLabel.innerHTML = Translate('Email');
                        requiredField = cTag('span', {class: "required"});
                        requiredField.innerHTML = '*';
                    emailLabel.appendChild(requiredField);
                emailTitle.appendChild(emailLabel);
            emailRow.appendChild(emailTitle);
                const emailField = cTag('div', {class: "columnSM8"});
                    inputField = cTag('input', {'required': "required", 'maxlength': 50, 'type': "email", class: "form-control", name: "user_email", id: "user_email", 'value': data.user_email});
                emailField.appendChild(inputField);
                emailField.appendChild(cTag('span', {id: "error_user_email", class: "errormsg"}));
            emailRow.appendChild(emailField);
        accountForm.appendChild(emailRow);

            const trailDaysRow = cTag('div',{class: "flex", 'align': "left"});
                const trailDayTitle = cTag('div', {class: "columnSM4"});
                    const trailDayLabel = cTag('label', {'for': "trial_days"});
                    trailDayLabel.innerHTML = "Trial Days";
                        requiredField = cTag('span', {class: "required"});
                        requiredField.innerHTML = '*';
                    trailDayLabel.appendChild(requiredField);
                trailDayTitle.appendChild(trailDayLabel);
            trailDaysRow.appendChild(trailDayTitle);
                const trialField = cTag('div', {class: "columnSM8"});
                    inputField = cTag('input', {'required': "required", 'maxlength': 8, 'type': "text",'data-min':'0', 'data-max': '9999999', 'data-format':'d', class: "form-control", name: "trial_days", id: "trial_days", 'value': data.trial_days});
                    // checkNumericInputOnKeydown(inputField);
                    controllNumericField(inputField, '#error_trial_days');
                trialField.appendChild(inputField);
                trialField.appendChild(cTag('span', {class:'errormsg', id:'error_trial_days'}));
            trailDaysRow.appendChild(trialField);
        accountForm.appendChild(trailDaysRow);

            const payPalRow = cTag('div',{class: "flex", 'align': "left"});
                const payPalTitle = cTag('div', {class: "columnSM4"});
                    const payPalLabel = cTag('label', {'for': "paypal_id"});
                    payPalLabel.innerHTML = "bKash ID";
                payPalTitle.appendChild(payPalLabel);
            payPalRow.appendChild(payPalTitle);
                const payPalFields = cTag('div', {class: "columnSM8"});
                    inputField = cTag('input', {'required': "required", 'maxlength': 20, 'type': "text", class: "form-control", name: "paypal_id", id: "paypal_id", 'value': data.paypal_id});
                payPalFields.appendChild(inputField);
            payPalRow.appendChild(payPalFields);
        accountForm.appendChild(payPalRow);

            const statusRow = cTag('div',{class: "flex", 'align': "left"});
                const statusTitle = cTag('div', {class: "columnSM4"});
                    const statusLabel = cTag('label', {'for': "status"});
                    statusLabel.innerHTML = Translate('Status');
                statusTitle.appendChild(statusLabel);
            statusRow.appendChild(statusTitle);
                const statusDropDown = cTag('div', {class: "columnSM8"});
                    let selectStatus = cTag('select', {'required': "required", 'maxlength': 10, class: "form-control", name: "status", id: "status"});
                        let trialOption = cTag('option', {'value': "Trial"});
                        trialOption.innerHTML = "Trial";
                    selectStatus.appendChild(trialOption);
                        let activeOption = cTag('option', {'value': "Active"});
                        activeOption.innerHTML = "Active";
                    selectStatus.appendChild(activeOption);
                        let pendingOption = cTag('option', {'value': "Pending"});
                        pendingOption.innerHTML = "Pending";
                    selectStatus.appendChild(pendingOption);
                        let suspendOption = cTag('option', {'value': "SUSPENDED"});
                        suspendOption.innerHTML = "SUSPENDED";
                    selectStatus.appendChild(suspendOption);
                        let cancelOption = cTag('option', {'value': "CANCELED"});
                        cancelOption.innerHTML = "CANCELED";
                    selectStatus.appendChild(cancelOption);
                statusDropDown.appendChild(selectStatus);
            statusRow.appendChild(statusDropDown);
        accountForm.appendChild(statusRow);

            const priceLocationRow = cTag('div',{class: "flex", 'align': "left"});
                const priceLocationTitle = cTag('div', {class: "columnSM4"});
                    const priceLocationLabel = cTag('label', {'for': "price_per_location"});
                    priceLocationLabel.innerHTML = 'Price / Location :';
                priceLocationTitle.appendChild(priceLocationLabel);
            priceLocationRow.appendChild(priceLocationTitle);
                const priceLocationPrint = cTag('div', {class: "columnSM8"});
                    inputField = cTag('input', {'required': "required", 'maxlength': 10, 'type': "text",'data-min':'0','data-max': '9999999', 'data-format':'d', class: "form-control", name: "price_per_location", id: "price_per_location", 'value': data.price_per_location});
                    // checkNumericInputOnKeydown(inputField);
                    controllNumericField(inputField, '#error_price_per_location');
                priceLocationPrint.appendChild(inputField);
                priceLocationPrint.appendChild(cTag('span', {class:'errormsg', id:'error_price_per_location'}));
            priceLocationRow.appendChild(priceLocationPrint);
        accountForm.appendChild(priceLocationRow);

            const payFrequencyRow = cTag('div',{class: "flex", 'align': "left"});
                let payFrequencyColumn = cTag('div', {class: "columnSM4"});
                    const payFrequencyLabel = cTag('label', {'for': "pay_frequency"});
                    payFrequencyLabel.innerHTML = Translate('Pay Frequency')+ ':';
                payFrequencyColumn.appendChild(payFrequencyLabel);
            payFrequencyRow.appendChild(payFrequencyColumn);
                const frequencyField = cTag('div', {class: "columnSM8"});
                    let selectFrequency = cTag('select', {'required': "required", 'maxlength': 10, class: "form-control", name: "pay_frequency", id: "pay_frequency"});
                        let emptyOption = cTag('option', {'value': ""});
                    selectFrequency.appendChild(emptyOption);
                        let monthlyOption = cTag('option', {'value': "Monthly"});
                        monthlyOption.innerHTML = Translate('Monthly');
                    selectFrequency.appendChild(monthlyOption);
                        let quarterlyOption = cTag('option', {'value': "Quarterly"});
                        quarterlyOption.innerHTML = "Quarterly";
                    selectFrequency.appendChild(quarterlyOption);
                        let yearlyOption = cTag('option', {'value': "Yearly"});
                        yearlyOption.innerHTML = "Yearly";
                    selectFrequency.appendChild(yearlyOption);						
                frequencyField.appendChild(selectFrequency);
            payFrequencyRow.appendChild(frequencyField);
        accountForm.appendChild(payFrequencyRow);

            const nextPaymentRow = cTag('div',{class: "flex", 'align': "left"});
                const nextPaymentTitle = cTag('div', {class: "columnSM4"});
                    const nextPaymentLabel = cTag('label', {'for': "next_payment_due"});
                    nextPaymentLabel.innerHTML = 'Next invoice date:';
                nextPaymentTitle.appendChild(nextPaymentLabel);
            nextPaymentRow.appendChild(nextPaymentTitle);
                const nextPaymentDiv = cTag('div', {class: "columnSM8"});
                    inputField = cTag('input', {'required': "required", 'maxlength': 10, 'type': "text", class: "form-control", name: "next_payment_due", id: "next_payment_due", 'value': DBDateToViewDate(data.next_payment_due)});
                nextPaymentDiv.appendChild(inputField);
            nextPaymentRow.appendChild(nextPaymentDiv);
        accountForm.appendChild(nextPaymentRow);

            const couponCodeRow = cTag('div',{class: "flex", 'align': "left"});
                const couponCodeTitle = cTag('div', {class: "columnSM4"});
                    const couponCodeLabel = cTag('label', {'for': "coupon_code"});
                    couponCodeLabel.innerHTML = Translate('Coupon Code')+': ';
                couponCodeTitle.appendChild(couponCodeLabel);
            couponCodeRow.appendChild(couponCodeTitle);
                const couponCodeField = cTag('div', {class: "columnSM8"});
                    inputField = cTag('input', {'maxlength': 30, 'type': "text", class: "form-control", name: "coupon_code", id: "coupon_code", 'value': data.coupon_code});
                couponCodeField.appendChild(inputField);
            couponCodeRow.appendChild(couponCodeField);
        accountForm.appendChild(couponCodeRow);

            inputField = cTag('input', {'type': "hidden", name: "accounts_id", 'value': data.accounts_id});
        accountForm.appendChild(inputField);

        popup_dialog600("Change "+Translate(data.company_name)+"'s information details", accountForm, Translate('Save'), AJsave_Accounts);
        
        setTimeout(function() {
            document.getElementById("name").focus();
            document.getElementById("status").value = data.statusVal;
            document.getElementById("pay_frequency").value = data.pay_frequency;
            
            if (document.querySelector('#next_payment_due')){
                date_picker('#next_payment_due',(date,month,year)=>{
                    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                        dateVal =date+'-'+month+'-'+year;
                    }
                    else{
                        dateVal =month+'/'+date+'/'+year;
                    }
                    document.querySelector('#next_payment_due').value = dateVal;
                })
            }
        }, 500);			
    }
	return true;
}

async function AJsave_Accounts(hidePopup){
    let saveBtn;
	saveBtn = document.querySelector('.btnmodel');
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;
	const showErrorMsg = document.getElementById("showErrorMsg");
    showErrorMsg.innerHTML = '';

    if(document.getElementsByClassName("requiredField").length>0){
		const requiredFields = document.getElementsByClassName("requiredField");
		for(let l = 0; l<requiredFields.length; l++){
			const oneFieldVal = requiredFields[l].value;
            let errorMessage = document.getElementById("error_"+ requiredFields[l].getAttribute('name'));
			if(oneFieldVal===''){
                errorMessage.innerHTML = requiredFields[l].title+' '+Translate('is missing.');
				requiredFields[l].focus();
                requiredFields[l].classList.add('errorFieldBorder');
				return false;
			}
            else{
                errorMessage.innerHTML = '';
                requiredFields[l].classList.remove('errorFieldBorder');
            }
		}
	}
    	
	const jsonData = serialize('#frmAccounts');
    const url = "/Admin/AJsave_Accounts";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr ==='Save Successfully'){
            if(showErrorMsg.classList.contains('errormsg')){
                showErrorMsg.classList.remove('errormsg');
            }
            if(!showErrorMsg.style.color === '#090;'){
                showErrorMsg.style.color = '#090;';
            }
			showErrorMsg.innerHTML = data.returnStr;

			const pageURI = document.getElementById("pageURI").value;
			window.location = '/'+pageURI;
            hidePopup();
		}
		else{
            if(showErrorMsg.style.color === '#090;'){
                showErrorMsg.style.color = 'initial';
            }
            
            if(!showErrorMsg.classList.contains('errormsg')){
                showErrorMsg.classList.add('errormsg');
            }
			showErrorMsg.innerHTML = data.returnStr;
            saveBtn = document.querySelector('.btnmodel');
            saveBtn.innerHTML = Translate('Save');
            saveBtn.disabled = false;
		}
	}							
	return false;
}

async function AJreset_Accounts(hidePopup){
    let resetBtn;
	resetBtn = document.querySelector('.archive');
	resetBtn.innerHTML = 'Reseting...';
	resetBtn.disabled = true;	

	const jsonData = serialize('#frmaccountsRow');
    const url = "/Admin/AJreset_Accounts";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.login=== 'session_ended'){
			window.location = '/session_ended';
		}			
		else if(data.returnmsg ==='reset-success'){
			showTopMessage('success_msg', 'Reset all('+data.dtcount+') data successfully');
            hidePopup();
			filter_Admin_edit();
		}
		else{
			showTopMessage('alert_msg', data.returnmsg);
            resetBtn.innerHTML = 'Reset';
            resetBtn.disabled = false;
		}
	}
	return false;
}

function AJreset_AccountsPopup(){
	let inputField;
    const accounts_id = document.getElementById("saccounts_id").value;
	const company_name = document.getElementById("company_nameStr").innerHTML;
	const title = 'Reset '+company_name+"'s information";

		const resetAccountForm = cTag('form', {'action': "#", name: "frmaccountsRow", id: "frmaccountsRow", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
		resetAccountForm.addEventListener('submit', AJreset_Accounts);
			const textRow = cTag('div', {class: "flexSpaBetRow"});
                const textValue = cTag('div', {class: "columnXS12"});
					const divAlertMsg = cTag('div', {class: "innerContainer alert_msg"});
					divAlertMsg.innerHTML = 'Are you sure you want to reset "'+company_name+'" instance with all data remove permanently?'
                textValue.appendChild(divAlertMsg);
            textRow.appendChild(textValue);
        resetAccountForm.appendChild(textRow);

			const securityPasswordRow = cTag('div', {class: "flexStartRow"});
				const securityPasswordTitle = cTag('div', {class: "columnXS12 columnSM6", 'style': "text-align: right;"});
                    const securityPasswordLabel = cTag('label', {'for': "resetuserpassword"});
					securityPasswordLabel.innerHTML = 'Enter Password for security:';
                securityPasswordTitle.appendChild(securityPasswordLabel);
            securityPasswordRow.appendChild(securityPasswordTitle);
				const securityPasswordField = cTag('div', {class: "columnXS12 columnSM6"});
					inputField = cTag('input', {'type': "password", class: "form-control", name: "resetuserpassword", id: "resetuserpassword", 'autocomplete': "off", 'value': "", 'maxlength': 32 });
                securityPasswordField.appendChild(inputField);
            securityPasswordRow.appendChild(securityPasswordField);
        resetAccountForm.appendChild(securityPasswordRow);

			const keepProductRow = cTag('div', {class: "flexStartRow"});
                const keepProductTitle = cTag('div', {class: "columnXS12 columnSM6", 'style': "text-align: right;"});
                    const keepProductLabel = cTag('label', {'for': "keep_product"});
					keepProductLabel.innerHTML = 'Keep Products:';
                keepProductTitle.appendChild(keepProductLabel);
            keepProductRow.appendChild(keepProductTitle);
                const keepProductValue = cTag('div', {class: "columnXS12 columnSM6"});
					let yesLabel = cTag('label');
						inputField = cTag('input', {'type': "checkbox", name: "keep_product", id: "keep_product", 'value': 1});
                    yesLabel.appendChild(inputField);
					yesLabel.append(' '+Translate('Yes'));
                keepProductValue.appendChild(yesLabel);
            keepProductRow.appendChild(keepProductValue);
        resetAccountForm.appendChild(keepProductRow);

			const keepCustomerRow = cTag('div', {class: "flexStartRow"});
                const keepCustomerTitle = cTag('div', {class: "columnXS12 columnSM6", 'style': "text-align: right;"});
                    const keepCustomerLabel = cTag('label', {'for': "keep_customers"});
					keepCustomerLabel.innerHTML = 'Keep Customers:';
                keepCustomerTitle.appendChild(keepCustomerLabel);
            keepCustomerRow.appendChild(keepCustomerTitle);
                const keepCustomerValue = cTag('div', {class: "columnXS12 columnSM6"});
					let customerLabel = cTag('label');
						inputField = cTag('input', {'type': "checkbox", name: "keep_customers", id: "keep_customers", 'value': 1});
                    customerLabel.appendChild(inputField);
					customerLabel.append(' '+Translate('Yes'));
                keepCustomerValue.appendChild(customerLabel);
            keepCustomerRow.appendChild(keepCustomerValue);
        resetAccountForm.appendChild(keepCustomerRow);

			inputField = cTag('input', {'type': "hidden", name: "accounts_id", 'value': accounts_id});
        resetAccountForm.appendChild(inputField);

	confirm_dialog(title, resetAccountForm, AJreset_Accounts);
		
	setTimeout(function() {			
		document.getElementById("resetuserpassword").focus();
	}, 500);	
}

async function AJremove_Accounts(hidePopup){
    let archiveBtn;
	archiveBtn = document.querySelector('.archive');
    archiveBtn.innerHTML = Translate('Removing')+'...';
    archiveBtn.disabled = true;

	const jsonData = serialize('#frmAccounts');
    const url = "/Admin/AJremove_Accounts";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnmsg=== 'session_ended'){
			window.location = '/session_ended';
		}			
		else if(data.returnmsg ==='remove-success'){
            showTopMessage('success_msg', 'Removed instance all('+data.dtcount+') data  successfully');
			hidePopup();			
			window.location = '/Admin/lists/';
		}
		else{
			showTopMessage('alert_msg', data.returnmsg);
            archiveBtn.innerHTML = Translate('Remove');
            archiveBtn.disabled = false;
		}

	}
	return false;
}

function AJremove_AccountsPopup(){
	let inputField;
    const accounts_id = document.getElementById("saccounts_id").value;
	const company_name = document.getElementById("company_nameStr").innerHTML;
	
    const title = 'Remove '+company_name+"'s information";
    const removeAccountForm = cTag('form', {'action': "#", name: "frmAccounts", id: "frmAccounts", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
    removeAccountForm.addEventListener('submit', AJremove_Accounts);
        const sureToRemoveRow = cTag('div', {class: "flexSpaBetRow "});
            const sureToRemoveField = cTag('div', {class: "columnXS12"});
                const divAlertMsg = cTag('div', {class: "innerContainer alert_msg"});
                divAlertMsg.innerHTML = 'Are you sure you want to remove "'+company_name+'" instance with all data permanently?'
            sureToRemoveField.appendChild(divAlertMsg);
        sureToRemoveRow.appendChild(sureToRemoveField);
    removeAccountForm.appendChild(sureToRemoveRow);

        const securityPassword = cTag('div', {class: "flexStartRow"});
            const securityPasswordColumn = cTag('div', {class: "columnXS12 columnSM7", 'style': "text-align: right;"});
                const securityPasswordLabel = cTag('label', {'for': "deluserpassword"});
                securityPasswordLabel.innerHTML = 'Password for security:';
            securityPasswordColumn.appendChild(securityPasswordLabel);
        securityPassword.appendChild(securityPasswordColumn);
            const securityPasswordField = cTag('div', {class: "columnXS12 columnSM5"});
                inputField = cTag('input', {'type': "password", class: "form-control", name: "deluserpassword", id: "deluserpassword", 'autocomplete': "off", 'value': "", 'maxlength': 32 });
            securityPasswordField.appendChild(inputField);
        securityPassword.appendChild(securityPasswordField);
    removeAccountForm.appendChild(securityPassword);

        inputField = cTag('input', {'type': "hidden", name: "accounts_id", 'value': accounts_id});
    removeAccountForm.appendChild(inputField);
	
	confirm_dialog(title, removeAccountForm, AJremove_Accounts);
	
	setTimeout(function() {			
		document.getElementById("deluserpassword").focus();
	}, 500);
}

async function AJsave_PaypalPayment(hidePopup){
	let updateBtn;
    const paypal_id = document.getElementById("paypal_id").value;
	if(paypal_id ===''){
		showTopMessage('error_msg', "bKash ID is missing");
		return false;
	}
	
	const accounts_id = document.getElementById("accounts_id").value;
	const nextnext_payment_due = document.getElementById("nextnext_payment_due").value;
	
	if(nextnext_payment_due ==='' || nextnext_payment_due ==='0000-00-00' || nextnext_payment_due ==='1000-01-01'){
		showTopMessage('error_msg', "Next Payment Due is missing.");
		return false;
	}
	
	updateBtn = document.querySelector('.archive');
	updateBtn.innerHTML = 'Updating...';
	updateBtn.disabled = true;	

	const jsonData = {};
	jsonData['accounts_id'] = accounts_id;
	jsonData['paypal_id'] = paypal_id;
	jsonData['nextnext_payment_due'] = nextnext_payment_due;
    const url = "/Admin/AJsave_PaypalPayment/";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr ==='Save Successfully'){
			showTopMessage('success_msg', 'Paypal Payment updated successfully');
			hidePopup();
			location.reload();
		}
		else{
			showTopMessage('alert_msg', data.returnStr);
            updateBtn.innerHTML = Translate('Confirm');
            updateBtn.disabled = false;
		}
	}
	return false;
}

function AJget_PaypalPayment(paypal_id, next_payment_due, nextnext_payment_due){
    const accounts_id = document.getElementById("saccounts_id").value;

    let dateVal, inputField;
	const paypalPaymentDiv = cTag('div');
		const paypalPaymentHeader = cTag('h3', {id: "showMess"});
        paypalPaymentHeader.innerHTML = "Paypal Payment";
    paypalPaymentDiv.appendChild(paypalPaymentHeader);
		const payPalIDRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 10px;"});
			const payPalIDTitle = cTag('div', {class: "columnSM6", 'align': "right"});
                const payPalIDLabel = cTag('label');
				payPalIDLabel.innerHTML = "bKash ID:";
            payPalIDTitle.appendChild(payPalIDLabel);
        payPalIDRow.appendChild(payPalIDTitle);
			const payPalIDField = cTag('div', {class: "columnSM4", 'align': "left"});
                inputField = cTag('input', {'type':"text", class: "form-control", name: "paypal_id", id: "paypal_id", 'value': paypal_id});
            payPalIDField.appendChild(inputField);
        payPalIDRow.appendChild(payPalIDField);
    paypalPaymentDiv.appendChild(payPalIDRow);
				
    if(next_payment_due !==''){
        const previousPaymentDueRow = cTag('div', {class: "flexSpaBetRow"});
            const previousPaymentTitle = cTag('div', {class: "columnSM6", 'align': "right"});
                const previousPaymentLabel = cTag('label');
                previousPaymentLabel.innerHTML = "Prev Payment Due:";
            previousPaymentTitle.appendChild(previousPaymentLabel);
        previousPaymentDueRow.appendChild(previousPaymentTitle);
            const previousPaymentField = cTag('div', {class: "columnSM4", 'align': "left"});
            previousPaymentField.innerHTML = next_payment_due;
        previousPaymentDueRow.appendChild(previousPaymentField);
        paypalPaymentDiv.appendChild(previousPaymentDueRow);
    }

		const nextPaymentDueRow = cTag('div', {class: "flexSpaBetRow"});
			const nextPaymentColumn = cTag('div', {class: "columnSM6", 'align': "right"});
                const nextPaymentLabel = cTag('label', {'for': "nextnext_payment_due"});
				nextPaymentLabel.innerHTML = "New Next Payment Due";
					let requiredField = cTag('span', {class: "required"});
                    requiredField.innerHTML = '*';
                nextPaymentLabel.appendChild(requiredField);
            nextPaymentColumn.appendChild(nextPaymentLabel);
        nextPaymentDueRow.appendChild(nextPaymentColumn);
			const nextPaymentDueField = cTag('div', {class: "columnSM4", 'align': "left"});
                inputField = cTag('input', {'maxlength': 10, 'type':"text", class: "form-control", name: "nextnext_payment_due", id: "nextnext_payment_due", 'value': nextnext_payment_due});
            nextPaymentDueField.appendChild(inputField);
        nextPaymentDueRow.appendChild(nextPaymentDueField);
			const errorDiv = cTag('div', {class: "columnSM12", 'align': "left"});
				let errorSpan = cTag('span', {class: "error_msg", id: "errmsg_nextnext_payment_due"});
            errorDiv.appendChild(errorSpan);
        nextPaymentDueRow.appendChild(errorDiv);
    paypalPaymentDiv.appendChild(nextPaymentDueRow);

		inputField = cTag('input', {'type': "hidden", name: "accounts_id", id: "accounts_id", 'value': accounts_id});
    paypalPaymentDiv.appendChild(inputField);

	confirm_dialog('bKash Payment', paypalPaymentDiv, AJsave_PaypalPayment);
	
	setTimeout(function() {									
        if (document.querySelector('#nextnext_payment_due')){
            date_picker('#nextnext_payment_due',(date,month,year)=>{
                if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                    dateVal =date+'-'+month+'-'+year;
                }
                else{
                    dateVal =month+'/'+date+'/'+year;
                }
                document.querySelector('#nextnext_payment_due').value = dateVal;
            })
        }
		document.getElementById("paypal_id").focus();
	}, 500);
}

async function AJsave_AccountsMessage(hidePopup){
    let saveBtn;
	saveBtn = document.querySelector('.btnmodel');
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;

	const jsonData = serialize('#frmPopupMessage');
    const url = "/Admin/AJsave_AccountsMessage";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr ==='Save Successfully'){
			showTopMessage('success_msg', data.returnStr);
			hidePopup();
			const pageURI = document.getElementById("pageURI").value;
			window.location = '/'+pageURI;
		}
		else{
			showTopMessage('alert_msg', data.returnStr);
            saveBtn.innerHTML = Translate('Save');
            saveBtn.disabled = false;
		}
	}
	return false;
}

function AJget_AccountsMessage(){
    const accounts_id = document.getElementById("saccounts_id").value;	
    const title = 'Change message information details';	

    const form = cTag('form', {'action': "#", name: "frmPopupMessage", id: "frmPopupMessage", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
    form.appendChild(wysiwyrEditor('popup_message'));
        const input = cTag('input', {'type': "hidden", name: "accounts_id", 'value': accounts_id});
    form.appendChild(input);
    popup_dialog1000(title,form,AJsave_AccountsMessage);

    setTimeout(function() {        
        const popup_message = document.querySelector("#popup_messagedata").innerHTML;
        let editor = document.getElementById('wysiwyrEditor');
        editor.querySelector("#popup_message").innerHTML = editor.querySelector("#editingArea").contentWindow.document.body.innerHTML = popup_message;
        editor.querySelector("#popup_message").focus();
        multiSelectAction('wysiwyrEditorDropdown');
    }, 500);					
}

//=========import-customer===============
function checkImportCustomers(event = false){
    if(event){ event.preventDefault();}
    const elementmessage = document.getElementById('errmsg_import_file');
    elementmessage.innerHTML = '';
    if(document.querySelector('#import_file').value===''){
        elementmessage.innerHTML = 'Customer Import File is mandatory.';
        document.querySelector('#import_file').focus();
        return false;
    } 
    else{
        const ext = document.querySelector('#import_file').value.split('.').pop().toLowerCase();
        if(ext !== 'csv') {
            elementmessage.innerHTML = 'File type is invalid. Only .csv are allowed.';
            document.querySelector('#import_file').focus();
            return false;
        }
    }
    const errmsg_import_file = document.getElementById('errmsg_import_file');
    const numberOfBlankHeaderCell = [...document.querySelectorAll('#ctable thead tr td')].filter(item=>item.innerText=='').length;
    if(numberOfBlankHeaderCell){
        errmsg_import_file.innerHTML = 'there are customers that might have some extra column';
        return false;
    }

	const showMsgHere = document.getElementById('showMsgHere');
    showMsgHere.innerHTML = '';
    const fullName = document.querySelector('#column1').value;
    const firstName = document.querySelector('#column2').value;
    if(fullName==='' && firstName===''){
        showMsgHere.innerHTML = 'please select Full-Name or First-Name';
        return false;
    }

    const instance_id = document.frmImportCustomers.instance_id;
    const oElement = document.getElementById('errmsg_instance_id');
    oElement.innerHTML = "";
    if(instance_id.value === 0){
        oElement.innerHTML = "You are missing Instance Name.";
        instance_id.focus();
        return(false);
    }
    
    let text;
    const mandatoryField = ['first_name'];
	let errormsg = '';
	const ourFields = document.getElementsByName("ourFields[]");
	const importColumn = document.getElementsByName("column[]");
	let mcount = mandatoryField.length;    
	
	if(document.querySelector("#column1").value !==''){
		mcount--;
	}
	
	const fieldsArray = new Array();
	if(importColumn.length>0){
		let p = 2;
		for(let l=0; l<importColumn.length; l++){
			if(importColumn[l].value !==''){
                text = importColumn[l].value;
				if(text.localeCompare(fieldsArray) === 0){
					errormsg += document.querySelector("#column"+p).value +" is duplicate.<br />";
                    p++;
				}
				else{
					fieldsArray[l] = importColumn[l].value;
					text = ourFields[l].value;

					if(text.localeCompare(mandatoryField) === 0){
						mcount--;
					}
                    p++;			
				}
			}
		}
	}
	
	if(mcount>0){
		showTopMessage('error_msg', 'Missing '+mcount+' mandatory fields from ('+mandatoryField.join(" / ")+' / Full Name)');
        return false;
	}
	else if(errormsg !==''){
		showTopMessage('error_msg', errormsg);
        return false;
	}
    
    hideLoader();
	confirm_dialog('Import Customer Data', 'Are you sure want to import this file to the customers list?', importCustomerConfirm);
	return false;
}

function importCustomerConfirm(hidePopup){
	document.getElementById('frmImportCustomers').submit();
	hidePopup();
}

function resetImpCusform(){
	document.frmImportCustomers.instance_id.value = 0;
	document.querySelector('#import_file').value = '';
	document.getElementById("formtitle").innerHTML = 'Add New Customer Import';
	document.getElementById("formsave").value = ' Upload and Import ';
}

function Customer_previewFile() {
    const [file] = document.querySelector('input[type=file]').files;
    const reader = new FileReader();
    let data = 0;
    reader.addEventListener("load", () => {
        const dataval =  reader.result.replace(/['"]+/g, '');
        data = dataval.split('\n');

        let table,thead,tr,td,option;
        table = document.getElementById('ctable');
        let p = 1;        
        data.forEach(oneEle =>{
            const value = oneEle.split(/[,]/);
                if(p===1){
                    thead = cTag('thead',{class:"cf"});
                    tr = cTag('tr');
                }
                else{                    
                    thead = cTag('tbody',{class:"cf"});
                    tr = cTag('tr');
                }
                    value.forEach(oneEle=>{
                        td = cTag('td');
                        td.innerHTML = oneEle;
                    tr.appendChild(td);
                    if(p===6){return}
                thead.appendChild(tr)
            table.append(thead);
            });
            if(p===6){return}
            p++;
        });

        const ourFields = {'first_name':'First Name', 'last_name':'Last Name', 'email':'Email', 
        'company':'Company', 'contact_no':'Contact No', 'secondary_phone':'Secondary phone', 'fax':'Fax', 'customer_type':'Customer Type',
        'shipping_address_one':'Shipping address one', 'shipping_address_two':'Shipping address two', 'shipping_city':'Shipping city', 
        'shipping_state':'Shipping state', 'shipping_zip':'Shipping zip', 'shipping_country':'Shipping country'};

            table = document.getElementById('selecttable');
            const tbody = cTag('tbody')
            const dataStr = data[0].replace(/['"]+/g, '')
            const result = dataStr.split(/[;,]/);
            let l = 1;
            let op = 0
            for(const [module, moduletitle] of Object.entries(ourFields)) { 
                tr = cTag('tr',{id: 'fieldrow'+l});
                    td = cTag('td',{width:"40% "});
                        const label = cTag('label',{for:'column'+l});
                        label.innerHTML = moduletitle;
                        let required = cTag('span', {class: 'errormsg'});
                        required.innerHTML = '*';
                        if (module === 'first_name') label.append( ' ', required);
                    td.appendChild(label);
                        const input = cTag('input', {type:"hidden", name:"ourFields[]", value:module});
                    td.appendChild(input)
                tr.appendChild(td);
                    td = cTag('td');
                        const select =  cTag('select',{name:"column[]", id:'column'+l, class:"form-control"});
                            option = cTag('option',{value:''});
                            if(module==='offers_email'){
                                const option1 = cTag('option', {value:"Yes"})
                                option1.innerHTML = 'Yes'
                                const option2 = cTag('option', {value:"No"})
                                option2.innerHTML = 'No';
                            select.append(option1,option2)
                            }
                        select.appendChild(option);
                        let ic = 0;
                        result.forEach(oneTitle=>{
                            if(oneTitle !==''){
                                option = cTag('option',{value:ic});
                                option.innerHTML = oneTitle;
                                if (oneTitle === moduletitle) option.selected = true;
                                select.appendChild(option);
                            }
                            ic++;
                        });
                    td.appendChild(select)
                tr.appendChild(td);

                tbody.appendChild(tr);
                l++;
                op++
            }
            table.appendChild(tbody);

            /* const column1 = document.getElementById('column1');
            column1.addEventListener('change',function(){
                if(document.getElementById('fieldrow2').style.display !== 'none'){
                    document.getElementById('fieldrow2').style.display = 'none';
                }
                if(document.getElementById('fieldrow3').style.display !== 'none'){
                    document.getElementById('fieldrow3').style.display = 'none';
                }
            });

            const column2 = document.getElementById('column2');
            column2.addEventListener('change',function(){
                if(document.getElementById('fieldrow1').style.display !== 'none'){
                    document.getElementById('fieldrow1').style.display = 'none';
                }
            }); */
    }, false);

    if (file) {
        reader.readAsText(file);
    }
}

function Customer_Import_Information(){
    const importcus = document.getElementById('importcus');
        const customerTitle = cTag('div',{ 'class':`columnXS12` });
            const customerHeader = cTag('h3',{ 'class':`borderbottom`, 'style': "color: #5BC0DE;", 'id':`formtitle` });
            customerHeader.innerHTML = 'Customer Import Information';
        customerTitle.appendChild(customerHeader);
            const customerInfoColumn = cTag('div',{ 'class':`columnSM12` });
                let moreTable = cTag('div',{ 'id':`no-more-tables` });
                    let informationTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing`, id: 'ctable' });
                moreTable.appendChild(informationTable)
            customerInfoColumn.appendChild(moreTable);
            customerInfoColumn.appendChild(cTag('hr'));

                const customerImportRow = cTag('div',{ 'class':`flexSpaBetRow` });
                    const customerImportColumn = cTag('div',{ 'class':`columnSM6` });
                        let noTable = cTag('div',{ 'id':`no-more-tables` });
                            let customerImportTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing`, id: 'selecttable' });
                                const customerImportHead = cTag('thead',{ 'class':`cf` });
                                    const tableHeadRow = cTag('tr');
                                        let thCol0 = cTag('th',{ 'width':`30%` });
                                        thCol0.innerHTML = 'Our Fields';

                                        let thCol1 = cTag('th');
                                        thCol1.innerHTML = 'Import Columns';
                                    tableHeadRow.append(thCol0, thCol1);
                                customerImportHead.appendChild(tableHeadRow);
                            customerImportTable.appendChild(customerImportHead);
                        noTable.appendChild(customerImportTable);
                    customerImportColumn.appendChild(noTable);
                customerImportRow.appendChild(customerImportColumn);
            customerInfoColumn.appendChild(customerImportRow);
        customerTitle.appendChild(customerInfoColumn);
    importcus.appendChild(customerTitle);

    Customer_previewFile();
}

function importCustomers(){
    let inputField;
    const accounts_id = segment4;
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const callOutDiv = cTag('div',{ 'class':`innerContainer`,'style':`background:#FFF;`});
            let navColumn = cTag('div',{ 'class':`columnSM12` });
            navColumn.appendChild(getNavSection());
        callOutDiv.appendChild(navColumn);
            const newCustomerColumn = cTag('div',{ 'class':`columnSM12`, 'style': "margin-top: 10px;" });
                const newCustomerCallOut = cTag('div',{ 'class':`innerContainer`});
                    const newCustomerRow = cTag('div',{ 'class':`flexSpaBetRow` });
                        const newCustomerTitle = cTag('div',{ 'class':`columnXS12 ` });
                            let newCustomerHeader = cTag('h3',{ 'class':`borderbottom`, 'style': "color: #5BC0DE;", 'id':`formtitle` });
                            newCustomerHeader.innerHTML = 'Add New Customer Import';
                        newCustomerTitle.appendChild(newCustomerHeader);
                            const importCustomerForm = cTag('form',{ 'id':`frmImportCustomers`,'class':`frmImportCustomers`,'action':`/Admin/saveImportCustomers`,'name':`frmImportCustomers`, 'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
                            importCustomerForm.addEventListener('submit', checkImportCustomers);
                                let errorDiv = cTag('div',{ 'class':`flexStartRow ` });
                                    let errorLabel = cTag('label',{ 'class':`errormsg`, id: 'showMsgHere' });
                                errorDiv.appendChild(errorLabel);
                            importCustomerForm.appendChild(errorDiv);
                                let customerNameRow = cTag('div',{ 'class':`flex` });
                                    let customerLabel = cTag('label',{ 'for':`instance_id`, id: 'cname' });
                                customerNameRow.appendChild(customerLabel);
                            importCustomerForm.appendChild(customerNameRow);
                                let subDomainRow = cTag('div',{ 'class':`flex`, 'style': "padding-top: 15px;" });
                                    let subDomainLabel = cTag('label',{ 'for':`instance_id`, id: 'csubdomain' });
                                subDomainRow.appendChild(subDomainLabel);
                                subDomainRow.appendChild(cTag('input',{ 'type':`hidden`,'id':`instance_id`,'name':`instance_id`,'value':accounts_id }));
                                subDomainRow.appendChild(cTag('span',{ 'class':`errormsg`,'id':`errmsg_instance_id` }));
                            importCustomerForm.appendChild(subDomainRow);
                            
                                const importFileRow = cTag('div',{ 'class':`flex`, 'style': "padding-top: 15px;" });
                                    const importFileLabel = cTag('label');
                                    importFileLabel.innerHTML = 'Customer Import File*:';
                                    inputField = cTag('input',{ 'type':`file`,'name':`import_file`,'id':`import_file`, 'style': "padding-left: 10px;" });
                                    inputField.addEventListener('change', (event) => {
                                        const flenght = document.getElementById('import_file').value.length;
                                        if(flenght === 0){
                                            return;
                                        }
                                        else{
                                            const file = event.target.files;
                                            const name = file[0].name;
                                            const array = name.split('.');
                                            if(array[1] === 'csv'){
                                                Customer_Import_Information();
                                            }
                                        }
                                    });
                                importFileRow.append(importFileLabel, inputField);
                            importCustomerForm.appendChild(importFileRow);
                                let errorRow = cTag('div',{ 'class':`flex` });
                                errorRow.appendChild(cTag('span',{ 'class':`errormsg`,'id':`errmsg_import_file` }));
                            importCustomerForm.appendChild(errorRow);
                                const checked = 'checked'
                                let importCustomerDiv = cTag('div',{ 'class':`flex`, 'style': "padding-top: 15px;", id: "importcus" });
                                    inputField = cTag('input',{'checked': checked,'type':`checkbox`,'name':`containheading`, 'id':'checkbox' , 'value': 1 });
                                    inputField.addEventListener('click',function(){
                                        if(inputField.value === 1){
                                            inputField.setAttribute('value',0);
                                            inputField.setAttribute('checked','');
                                        }
                                        else{
                                            inputField.setAttribute('value',1);
                                            inputField.setAttribute('checked','checked');
                                        }
                                    });
                                importCustomerDiv.appendChild(inputField);
                                    let headingLabel = cTag('label',{ 'style': "padding-left: 10px; margin: 0;"});
                                    headingLabel.innerHTML = 'First row contains column headings';
                                importCustomerDiv.append(' ',headingLabel);
                            importCustomerForm.appendChild(importCustomerDiv);
                            importCustomerForm.appendChild(cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-top: 15px;" }));
                                let buttonRow = cTag('div',{ 'class':`flexStartRow` });
                                buttonRow.appendChild(cTag('input',{ 'type':`submit`,'id':`formsave`,'class':`btn saveButton`, 'style': "margin-right: 10px;", 'value':` Upload and Import ` }));
                                    let resetBtn = cTag('input',{ 'type':`reset`,'name':`reset`,'id':`reset`,'value':Translate('Cancel'),'class':`btn defaultButton`,style:'display:none' });
                                    resetBtn.addEventListener('click',resetImpCusform);
                                buttonRow.appendChild(resetBtn);
                            importCustomerForm.appendChild(buttonRow);
                        newCustomerTitle.appendChild(importCustomerForm);
                    newCustomerRow.appendChild(newCustomerTitle);
                newCustomerCallOut.appendChild(newCustomerRow);
            newCustomerColumn.appendChild(newCustomerCallOut);
        callOutDiv.appendChild(newCustomerColumn);
    Dashboard.appendChild(callOutDiv);
    AJ_importCustomers_MoreInfo();
}

async function AJ_importCustomers_MoreInfo(){
    const jsonData = {};
    jsonData['accounts_id'] = document.getElementById('instance_id').value;
    const url = '/'+segment1+'/AJ_importCustomers_MoreInfo';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(segment4>0){
            document.getElementById('cname').innerHTML = 'Company Name : ' + data.company_name;
            document.getElementById('csubdomain').innerHTML = 'Sub-domain Name : ' + data.company_subdomain;
        }
    }
}

//=========import-product===============//
function resetImpProduct(){
	document.frmImportProduct.instance_id.value = 0;
	document.querySelector('#import_file').value = '';
	document.getElementById("formtitle").innerHTML = 'Add New Product Import';
	document.getElementById("formsave").value = ' Upload and Import ';                                                
}

function importProductConfirm(hidePopup){
	document.getElementById('frmImportProduct').submit();
	hidePopup();
}

function check_frmimportProduct(event=false){
	if(event){event.preventDefault();}

	let text;
    const mandatoryField = ['product_name'];
	let errormsg = '';
	const ourFields = document.getElementsByName("ourFields[]");
	const importColumn = document.getElementsByName("column[]");
	let mcount = mandatoryField.length;

    
    activeLoader();

	const fieldsArray = new Array();
	if(importColumn.length>0){
        let p = 1;
		for(let l=0; l<importColumn.length; l++){
			if(importColumn[l].value ==='' || importColumn[l].value ==='No' || importColumn[l].value ==='Yes'){
            }
			else{
                text = importColumn[l].value;
				if(text.localeCompare(fieldsArray) === 0){
                    errormsg += document.querySelector("#column"+p).value +" is duplicate.<br />";
                    p++;
                }
				else{
					fieldsArray[l] = importColumn[l].value;
					text = ourFields[l].value;

					if(text.localeCompare(mandatoryField) === 0){
						mcount--;
					}
                    p++;
				}
			}
		}
	}
	
	if(mcount>0){
		showTopMessage('error_msg', 'Missing '+mcount+' mandatory fields from ('+mandatoryField.join(" / ")+')');
		return false;
	}
	else if(errormsg !==''){
		showTopMessage('error_msg', errormsg);
		return false;
	}
    hideLoader();
	confirm_dialog('Import Product Data', 'Are you sure want to import this file to the product list?', importProductConfirm);
	return false;
}

function Product_previewFile(){
    let table,thead,tr,td,option,option1;
    const [file] = document.querySelector('input[type=file]').files;
    const reader = new FileReader();
    reader.addEventListener("load", () => {
        const data2 =  reader.result.replace(/['"]+/g, '');
        const data = data2.split('\n');
        table = document.getElementById('ctable');
        let p = 1;
        data.forEach(oneEle =>{
            const value = oneEle.split(/[;,]/);
                if(p===1){
                thead = cTag('thead',{class:"cf"});
                    tr = cTag('tr');
                }
                else{
                thead = cTag('tbody',{class:"cf"});
                    tr = cTag('tr');
                }
                    value.forEach(oneEle=>{
                        td = cTag('td');
                        td.innerHTML = oneEle;
                    tr.appendChild(td);
                    if(p===6){return}
                thead.appendChild(tr)
            table.append(thead);
            });
            if(p===6){return}
            p++;
        });

            table = document.getElementById('selecttable');
            const tbody = cTag('tbody')
            const someStr = data[0].replace(/['"]+/g, '')
            const result = someStr.split(/[;,]/);
            let l = 1;

            let ourFields;
            const product_type = document.getElementById('product_type').value;
            if(product_type === 'Standard'){
                ourFields = {
                    'category_id': 'Category name','manufacturer_id': 'Manufacturer name','product_name': 'Product name','sku':'SKU','ave_cost': 'Cost',
                    'regular_price': 'Selling Price', 'taxable': 'Taxable', 'current_inventory': 'Current inventory', 'manage_inventory_count': 'Count Inventory',
                    'low_inventory_alert': 'Minimum stock', 'require_serial_no': 'Require Serial Number', 'allow_backorder': 'Allow Over Selling'
                }
            }
            else if(product_type === 'Live Stocks'){
                ourFields = {
                    'category_id': 'Category name','manufacturer_id': 'Manufacturer name','product_name': 'Product name', 'colour_name': 'Color Name',
                    'storage': 'Storage', 'physical_condition_name': 'Physical Condition', 'sku':'SKU', 'item_number': 'IMEI Number', 'regular_price': 'Selling Price', 
                    'ave_cost': 'Cost', 'taxable': 'Taxable', 'manage_inventory_count': 'Count Inventory', 
                    'low_inventory_alert': 'Minimum stock'
                }
            }
            else{
                ourFields = {
                    'category_id': 'Category name','manufacturer_id': 'Manufacturer name','product_name': 'Product name','sku':'SKU',
                    'ave_cost': 'Cost price','regular_price': 'Selling Price', 'taxable': 'Taxable'
                }
            }

            for(const [module, moduletitle] of Object.entries(ourFields)) { 
                tr = cTag('tr',{id: 'fieldrow'+l});
                    td = cTag('td',{width:"40%"});
                        const label = cTag('label',{for:'column'+l});
                        label.innerHTML = moduletitle;
                        let required = cTag('span', {class: 'errormsg'});
                        required.innerHTML = '*';
                        if (module === 'product_name') label.append( ' ', required);
                    td.appendChild(label);
                        const input = cTag('input', {type:"hidden", name:"ourFields[]", value:module});
                    td.appendChild(input)
                tr.appendChild(td);
                    td = cTag('td');
                        const select =  cTag('select',{name:"column[]", id:'column'+l, class:"form-control"});
                            option = cTag('option',{value:''});
                            if(module==='sku'){
                                option1 = cTag('option', {value:"Auto Create"});
                                option1.innerHTML = 'Auto Create';
                            select.append(option1);
                            }
                            else if(module==='taxable' || module==='manage_inventory_count' || module==='allow_backorder' || module==='require_serial_no' || module==='ave_cost_is_percent'){
                                option1 = cTag('option', {value:"Yes"})
                                option1.innerHTML = 'Yes'
                                const option2 = cTag('option', {value:"No"})
                                option2.innerHTML = 'No'
                            select.append(option1,option2)
                            }
                        select.appendChild(option);
                        let ic = 0;
                        result.forEach(oneTitle=>{
                            if(oneTitle !==''){
                                option = cTag('option',{value:ic});
                                option.innerHTML = oneTitle;
                                if (oneTitle === moduletitle) option.selected = true;
                                select.appendChild(option);
                            }
                            ic++;
                        });
                        if (module==='require_serial_no') select.value = 'No';
                    td.appendChild(select)
                tr.appendChild(td);
                tbody.appendChild(tr);
                l++;
            }
            table.appendChild(tbody);
    }, false);

    if (file) {
        reader.readAsText(file);
    }
}

function Product_Import_Information(){
    const importpro = document.getElementById('importpro');
    importpro.innerHTML = '';
        const importProductColumn = cTag('div',{ 'class':`columnXS12` });
            const importProductHeader = cTag('h3',{ 'class':`borderbottom`,'id':`formtitle`, 'style': "color: #5BC0DE;" });
            importProductHeader.innerHTML = 'Product Import Information';
        importProductColumn.appendChild(importProductHeader);
            const importProductTableColumn = cTag('div',{ 'class':`columnSM12` });
                let moreTable = cTag('div',{ 'id':`no-more-tables` });
                    let productTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing`,id: 'ctable' });
                moreTable.appendChild(productTable)
            importProductTableColumn.appendChild(moreTable);
            importProductTableColumn.appendChild(cTag('hr'));

                const importProductTableRow = cTag('div',{ 'class':`flexSpaBetRow` });
                    const productTableColumn = cTag('div',{ 'class':`columnSM6` });
                        let noTables = cTag('div',{ 'id':`no-more-tables` });
                            let importTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing`, id: 'selecttable' });
                                const importTableHead = cTag('thead',{ 'class':`cf` });
                                    const tableHeadRow = cTag('tr');
                                        let thCol0 = cTag('th',{ 'width':`30%` });
                                        thCol0.innerHTML = 'Our Fields';

                                        let thCol1 = cTag('th');
                                        thCol1.innerHTML = 'Import Columns';
                                    tableHeadRow.append(thCol0, thCol1);
                                importTableHead.appendChild(tableHeadRow);
                            importTable.appendChild(importTableHead);
                        noTables.appendChild(importTable);
                    productTableColumn.appendChild(noTables);
                importProductTableRow.appendChild(productTableColumn);
            importProductTableColumn.appendChild(importProductTableRow);
        importProductColumn.appendChild(importProductTableColumn);
    importpro.appendChild(importProductColumn);

    Product_previewFile();
}

function importProduct(){
    let inputField;
    const accounts_id = segment4;
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const files = 0;
        const callOutDiv = cTag('div',{ 'class':`innerContainer`,'style':`background:#FFF;` });
            let navigationColumn = cTag('div',{ 'class':`columnSM12` });
            navigationColumn.appendChild(getNavSection());
        callOutDiv.appendChild(navigationColumn); 
            const newProductColumn = cTag('div',{ 'class':`columnSM12`, 'style': "margin-top: 10px;" });
                const callOut = cTag('div',{ 'class':`innerContainer`});
                    const newProductRow = cTag('div',{ 'class':`flexSpaBetRow` });
                        let newImportProduct = cTag('div',{ 'class':`columnXS12`, id: 'customer' });
                            let newImportProductHeader = cTag('h3',{ 'class':`borderbottom`, 'style': "color: #5BC0DE;", 'id':`formtitle` });
                            newImportProductHeader.innerHTML = 'Add New Product Import';
                        newImportProduct.appendChild(newImportProductHeader);
                            const importProductForm = cTag('form',{ id:"frmImportProduct", class:"frmImportProduct", action:"/Admin/saveImportProduct", name:"frmImportProduct", enctype:"multipart/form-data", method:"post", 'accept-charset':"utf-8" });
                            importProductForm.addEventListener('submit', check_frmimportProduct);
                                let nameRow = cTag('div',{ 'class':`flexStartRow`, 'style': "padding-top: 15px;" });
                                    let nameLabel = cTag('label',{ 'for':`instance_id`, id:'cname' });
                                nameRow.appendChild(nameLabel);
                            importProductForm.appendChild(nameRow);

                                let companyRow = cTag('div',{ 'class':`flexStartRow`, 'style': "padding-top: 15px;", id:'ccomapny' });
                                    let companyLabel = cTag('label',{ 'for':`instance_id`, id:'csubdomain' });
                                companyRow.appendChild(companyLabel);
                                companyRow.appendChild(cTag('input',{ 'type':`hidden`,'id':`instance_id`,'name':`instance_id`,'value': accounts_id }));
                                companyRow.appendChild(cTag('span',{ 'class':`errormsg`,'id':`errmsg_instance_id` }));
                            importProductForm.appendChild(companyRow);

                                const productTypeRow = cTag('div',{ 'class':`flexStartRow`, 'style': "align-items: center; padding-top: 15px;" });
                                    let productTypeTitle = cTag('div');
                                        const productTypeLabel = cTag('label',{ 'for':`product_type` });
                                        productTypeLabel.innerHTML = 'Product Type*:';
                                    productTypeTitle.appendChild(productTypeLabel);
                                productTypeRow.appendChild(productTypeTitle);

                                    let productType = cTag('div',{ 'style':`padding-left: 15px;`});
                                        const selectProductType = cTag('select',{ 'class':`form-control`,'id':`product_type`,'name':`product_type` });
                                        selectProductType.addEventListener('change',()=>{
                                            let import_file = document.getElementById('import_file');
                                            if(selectProductType.value!==''){
                                                import_file.disabled = false;
                                                import_file.dispatchEvent(new Event('change'));
                                            }
                                            else import_file.disabled = true;
                                        })
                                            let selectType = cTag('option',{ 'value':'' });
                                            selectType.innerHTML = Translate('Select Type');
                                        selectProductType.appendChild(selectType);
                                            let standardOption = cTag('option',{ 'value':'Standard' });
                                            standardOption.innerHTML = Translate('Standard');
                                        selectProductType.appendChild(standardOption);
                                            let mobileOption = cTag('option',{ 'value':'Live Stocks' });
                                            mobileOption.innerHTML = Translate('Live Stocks');
                                        selectProductType.appendChild(mobileOption);
                                            let laborOption = cTag('option',{ 'value':'Labor/Services' });
                                            laborOption.innerHTML = Translate('Labor/Services');
                                        selectProductType.appendChild(laborOption);
                                    productType.appendChild(selectProductType);
                                productTypeRow.appendChild(productType);
                            importProductForm.appendChild(productTypeRow);

                                let importFileRow = cTag('div',{ 'class':`flexStartRow`, 'style': "padding-top: 15px;" });
                                    let importFileLabel = cTag('label');
                                    importFileLabel.innerHTML = 'Product Import File*:';
                                importFileRow.appendChild(importFileLabel);

                                    let importFileDiv = cTag('div',{ 'style': `padding-left: 15px;` });
                                        inputField = cTag('input',{ 'type':`file`,'name':`import_file`,'id':`import_file`,'disabled':'true' });
                                        inputField.addEventListener('change', (event) => {
                                            const flenght = document.getElementById('import_file').value.length;
                                            if(flenght === 0){
                                                return;
                                            }
                                            else{
                                                const file = event.target.files;
                                                const name = file[0].name;
                                                const array = name.split('.');
                                                if(array[1] === 'csv'){
                                                    Product_Import_Information();
                                                }
                                            }
                                        });
                                    importFileDiv.appendChild(inputField);
                                importFileRow.appendChild(importFileDiv);
                            importProductForm.appendChild(importFileRow);

                                const errorRow = cTag('div',{ 'class':`flexSpaBetRow` });
                                errorRow.appendChild(cTag('span',{ 'class':`errormsg`,'id':`errmsg_import_file` }));
                            importProductForm.appendChild(errorRow);

                                const checked = 'checked';
                                let headingRow = cTag('div',{ 'class':`flexStartRow`, 'style': "padding-top: 15px;"});
                                    inputField = cTag('input',{'checked': checked,'type':`checkbox`,'name':`containheading`, 'id':'checkbox' , 'value': 1 });
                                    inputField.addEventListener('click',function(){
                                        if(inputField.value === 1){
                                            inputField.setAttribute('value',0);
                                            inputField.setAttribute('checked','');
                                        }
                                        else{
                                            inputField.setAttribute('value',1);
                                            inputField.setAttribute('checked','checked');
                                        }
                                    });
                                headingRow.appendChild(inputField);
                                    let headingLabel = cTag('label',{ 'style': "padding-left: 10px;"});
                                    headingLabel.innerHTML = 'First row contains column headings';
                                headingRow.append(' ',headingLabel);
                            importProductForm.appendChild(headingRow);

                                let importProductDiv = cTag('div',{ 'class':`flexSpaBetRow`, id: "importpro"  });
                            importProductForm.appendChild(importProductDiv);
                            importProductForm.appendChild(cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-top: 15px;", 'id':`formcreatedon` }));
                                let buttonRow = cTag('div',{ 'class':`flexStartRow` });
                                buttonRow.appendChild(cTag('input',{ 'type':`submit`,'id':`formsave`,'class':`btn saveButton`, 'style': "margin-right: 10px;", 'value':` Upload and Import ` }));
                                    let resetBtn = cTag('input',{ 'type':`reset`,'name':`reset`,'id':`reset`,'value':Translate('Cancel'),'class':`btn defaultButton`,style:'display:none' });
                                    resetBtn.addEventListener('click',resetImpProduct);
                                buttonRow.appendChild(resetBtn);
                            importProductForm.appendChild(buttonRow);
                        newImportProduct.appendChild(importProductForm);
                    newProductRow.appendChild(newImportProduct);
                callOut.appendChild(newProductRow);
            newProductColumn.appendChild(callOut);
        callOutDiv.appendChild(newProductColumn);
    Dashboard.appendChild(callOutDiv);
    AJ_importProduct_MoreInfo();
}

async function AJ_importProduct_MoreInfo(){
    const jsonData = {};
    jsonData['accounts_id'] = document.getElementById('instance_id').value;
    const url = '/'+segment1+'/AJ_importCustomers_MoreInfo';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(segment4>0){
            document.getElementById('cname').innerHTML = 'Company Name : ' + data.company_name;
            document.getElementById('csubdomain').innerHTML = 'Sub-domain Name : ' + data.company_subdomain;
        }
    }
}

//=========popup-message=============
function popup_message(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const callOutDiv = cTag('div',{ 'class':`innerContainer`, style: `background:#FFF;`});
            let navigationLinkColumn = cTag('div',{ 'class':`columnSM12` });
            navigationLinkColumn.appendChild(getNavSection());
        callOutDiv.appendChild(navigationLinkColumn);
            let messageColumn = cTag('div',{ 'class':`columnSM12` });
                const popupMessageForm = cTag('form',{ 'name':`frmpopup_message`,'id':`frmpopup_message`,'action':`#`,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
                popupMessageForm.addEventListener('submit', checkAJsave_userPopupMessage);
                    const popupCallOut = cTag('div',{ 'class':`innerContainer`});
                        const popupMessageRow = cTag('div',{ 'class':`flexSpaBetRow` });
                            const popupMessageColumn = cTag('div',{ 'class':`columnSM12` });
                                let headerTitle = cTag('h4',{ 'class':`borderbottom`, 'style': "font-size: 18px;" });
                                headerTitle.innerHTML = 'Update Popup Message';
                            popupMessageColumn.appendChild(headerTitle);
                                const descriptionRow = cTag('div',{ 'class':`flexSpaBetRow` });
                                    let descriptionColumn = cTag('div',{ 'class':`columnXS12` });
                                    descriptionColumn.appendChild(wysiwyrEditor('description'));
                                descriptionRow.appendChild(descriptionColumn);
                                    descriptionColumn.appendChild(cTag('span',{ 'id':`errmsg_popup_message`,'class':`errormsg` }));
                                descriptionRow.appendChild(descriptionColumn);
                            popupMessageColumn.appendChild(descriptionRow);
                                let buttonRow = cTag('div',{ 'class':`flexSpaBetRow` });
                                    let buttonsColumn = cTag('div',{ 'class':`columnXS12`, 'style': "text-align: end; width: 95%" });
                                    buttonsColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`alluserupdate`,'id':`alluserupdate`,'value':0}));
                                        let updateButton = cTag('button',{ 'class':`btn completeButton`, 'style': "margin-right: 10px;", 'name':`business_submit`,'id':`business_submit`,'type':`submit` });
                                        updateButton.addEventListener('click',function(){
                                            document.getElementById('alluserupdate').value = 0;
                                        });
                                        updateButton.innerHTML = ' Update Message';
                                    buttonsColumn.appendChild(updateButton);
                                        let userButton = cTag('button',{ 'class':`btn saveButton`, 'name':`business_submit`, 'id':`business_submit2`, 'type':`submit` });
                                        userButton.addEventListener('click',function(){
                                            document.getElementById('alluserupdate').value = 1;
                                        });
                                        userButton.innerHTML = ' Publish to All Users';
                                    buttonsColumn.appendChild(userButton);
                                buttonRow.appendChild(buttonsColumn);
                            popupMessageColumn.appendChild(buttonRow);
                        popupMessageRow.appendChild(popupMessageColumn);
                    popupCallOut.appendChild(popupMessageRow);
                popupMessageForm.appendChild(popupCallOut);
            messageColumn.appendChild(popupMessageForm);
        callOutDiv.appendChild(messageColumn);
    Dashboard.appendChild(callOutDiv);
    AJ_popup_message_MoreInfo();
}

async function AJ_popup_message_MoreInfo(){
    const url = '/'+segment1+'/AJ_popup_message_MoreInfo';

    fetchData(afterFetch,url,{});

    function afterFetch(data){            
        let editor = document.getElementById('wysiwyrEditor');
        editor.querySelector("#description").innerHTML = editor.querySelector("#editingArea").contentWindow.document.body.innerHTML = data.popup_message;
        editor.querySelector("#description").focus();
        multiSelectAction('wysiwyrEditorDropdown');
    }
}

async function AJsave_userPopupMessage(hidePopup){
	const popup_message = document.getElementById('description').value;
	const alluserupdate  = document.getElementById('alluserupdate').value;

    if(alluserupdate===1){
        const saveBtn = document.getElementById('business_submit2');
        saveBtn.innerHTML = Translate('Saving')+'...';
        saveBtn.disabled = true;
    }

    const jsonData = {};
	jsonData['popup_message'] = popup_message;
	jsonData['alluserupdate'] = alluserupdate;
    const url = "/Admin/AJsave_userPopupMessage";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnmsg === 'session_ended'){
			window.location = '/session_ended';
		}
		else if(data.returnmsg ==='update-success'){
			showTopMessage('success_msg', Translate('Updated successfully.'));
		}
		else if(data.returnmsg ==='allupdate-success'){
			showTopMessage('success_msg', "Updated all successfully.");
		}
		else{
			showTopMessage('error_msg', data.returnmsg);
		}
		if(alluserupdate==='1') hidePopup();
	}
}

function checkAJsave_userPopupMessage(event){
    if(event){event.preventDefault();}

	const alluserupdate  = document.getElementById('alluserupdate').value;
	if(alluserupdate==='1'){
		confirm_dialog('Make sure', 'This will make all users get updated message. Are you sure?', AJsave_userPopupMessage);
	}
	else{
		AJsave_userPopupMessage();
	}
    return false;
}

/*======User login Message======*/
function login_message(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const callOutDiv = cTag('div',{ 'class':`innerContainer`,'style':`background:#FFF;`});
            let navColumn = cTag('div',{ 'class':`columnSM12` });
            navColumn.appendChild(getNavSection());
        callOutDiv.appendChild(navColumn);
            const loginMessageColumn = cTag('div',{ 'class':`columnSM12` });
                const loginMessageForm = cTag('form',{ 'name':`frmlogin_message`,'id':`frmlogin_message`,'action':`#`,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
                loginMessageForm.addEventListener('submit', AJsave_userloginMessage);
                    const loginCallOut = cTag('div',{ 'class':`innerContainer`});
                        const loginMessageRow = cTag('div',{ 'class':`flexSpaBetRow` });
                            const loginMessageTitle = cTag('div',{ 'class':`columnSM12` });
                                let loginMessageHeader = cTag('h4',{ 'class':`borderbottom`, 'style': "font-size: 18px;" });
                                loginMessageHeader.innerHTML = 'Update Login Message';
                            loginMessageTitle.appendChild(loginMessageHeader);
                                const descriptionRow = cTag('div',{ 'class':`flexSpaBetRow` });
                                    let descriptionColumn = cTag('div',{ 'class':`columnXS12` });
                                    descriptionColumn.appendChild(wysiwyrEditor('description'));
                                    descriptionColumn.appendChild(cTag('span',{ 'id':`errmsg_login_message`,'class':`errormsg` }));
                                descriptionRow.appendChild(descriptionColumn);
                            loginMessageTitle.appendChild(descriptionRow);
                                const updateRow = cTag('div',{ 'class':`flexSpaBetRow` });
                                    const updateColumn = cTag('div',{ 'class':`columnXS12`, 'style': "padding-top: 10px;", 'align':`center` });
                                        const updateButton = cTag('button',{ 'class':`btn completeButton`,'name':`business_submit`,'id':`business_submit`,'type':`submit` });
                                        updateButton.innerHTML = ' Update Message';
                                    updateColumn.appendChild(updateButton);
                                updateRow.appendChild(updateColumn);
                            loginMessageTitle.appendChild(updateRow);
                        loginMessageRow.appendChild(loginMessageTitle);
                    loginCallOut.appendChild(loginMessageRow);
                loginMessageForm.appendChild(loginCallOut);
            loginMessageColumn.appendChild(loginMessageForm);
        callOutDiv.appendChild(loginMessageColumn);
    Dashboard.appendChild(callOutDiv);
    AJ_login_message_MoreInfo();
}

async function AJ_login_message_MoreInfo(){
    const url = '/'+segment1+'/AJ_login_message_MoreInfo';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        let editor = document.getElementById('wysiwyrEditor');
        editor.querySelector("#description").innerHTML = editor.querySelector("#editingArea").contentWindow.document.body.innerHTML = data.login_message;
        editor.querySelector("#description").focus();
        multiSelectAction('wysiwyrEditorDropdown');
    }
}

async function AJsave_userloginMessage(event){
	if(event){ event.preventDefault();}

	const login_message = document.getElementById('description').value;	

    const jsonData = {};
	jsonData['login_message'] = login_message;
    const url = "/Admin/AJsave_userloginMessage";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnmsg === 'session_ended'){
			window.location = '/session_ended';
		}
		else if(data.returnmsg ==='update-success'){
			showTopMessage('success_msg', Translate('Updated successfully.'));
		}
		else{
			showTopMessage('error_msg', data.returnmsg);
		}
	}
	return false;
}



//===============language=============
function languages(){
    let list_filters, requireSpan, inputField, translateLink;
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    let keyword_search = '';
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    const rowHeight = 30;
    const totalRows = 0;

        const callOutDiv = cTag('div',{ 'class':`flexSpaBetRow innerContainer`, style: `background:#FFF;` });
            let titleColumn = cTag('div',{ 'class':`columnXS12 flexSpaBetRow outerListsTable` });
                const titleHeader = cTag('h2');
                titleHeader.innerHTML = 'Languages ';
                titleHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;"}));
            titleColumn.appendChild(titleHeader);
            titleColumn.appendChild(getNavSection());
        callOutDiv.appendChild(titleColumn);
            const languageColumn = cTag('div',{ 'class':`columnXS12 columnMD8` });
            languageColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`pageURI`,'id':`pageURI`,'value':`Admin/languages` }));
            languageColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`page`,'id':`page`,'value':page }));
            languageColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`rowHeight`,'id':`rowHeight`,'value':rowHeight }));
            languageColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`totalTableRows`,'id':`totalTableRows`,'value':totalRows }));
            languageColumn.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value':`languages` }));
                const variableNameRow = cTag('div',{ 'class':`flexSpaBetRow outerListsTable` });
                    const variableNameColumn = cTag('div',{ 'class':`columnXS6 columnSM4`, 'style': "padding-left: 0; padding-top: 12px;" });
                        let selectOrder = cTag('select',{ 'class':`form-control`,'name':`sorder_by`,'id':`sorder_by` });
                        selectOrder.addEventListener('change',filter_Admin_languages);
                            let variableOption = cTag('option',{ 'selected':``,'value':`0` });
                            variableOption.innerHTML = 'English ASC';
                        selectOrder.appendChild(variableOption);
                            let englishOption = cTag('option',{ 'value':`1` });
                            englishOption.innerHTML = 'English DESC';
                        selectOrder.appendChild(englishOption);
                    variableNameColumn.appendChild(selectOrder);
                variableNameRow.appendChild(variableNameColumn);
                    const phpJsDropDown = cTag('div',{ 'class':`columnXS6 columnSM4`, 'style': "padding-left: 0; padding-top: 12px;" });
                        let selectphpJs = cTag('select',{ 'class':`form-control`,'name':`sphp_js`,'id':`sphp_js` });
                        selectphpJs.addEventListener('change',filter_Admin_languages);
                            let allOption = cTag('option',{ 'value':`0` });
                            allOption.innerHTML = Translate('All');
                        selectphpJs.appendChild(allOption);
                            let phpOption = cTag('option',{ 'value':`1` });
                            phpOption.innerHTML = 'PHP';
                        selectphpJs.appendChild(phpOption);
                            let bothOption = cTag('option',{ 'value':`2` });
                            bothOption.innerHTML = 'PHP+JS';
                        selectphpJs.appendChild(bothOption);
                            let jsOption = cTag('option',{ 'value':`3` });
                            jsOption.innerHTML = 'Only JS';
                        selectphpJs.appendChild(jsOption);
                            let jsNotOption = cTag('option',{ 'value':`4` });
                            jsNotOption.innerHTML = 'Not used';
                        selectphpJs.appendChild(jsNotOption);
                    phpJsDropDown.appendChild(selectphpJs);
                variableNameRow.appendChild(phpJsDropDown);
                    const searchColumn = cTag('div',{ 'class':`columnXS12 columnSM4`, 'style': "padding-left: 0; padding-top: 12px;" });
                        const searchInput = cTag('div',{ 'class':`input-group` });
                        searchInput.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Admin_languages),'type':`text`,'placeholder':`Search from list...`,'value':``,'id':`keyword_search`,'name':`keyword_search`,'class':`form-control`,'maxlength':`50` }));
                            let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':`Search from list...` });
                            searchSpan.addEventListener('click',filter_Admin_languages);
                            searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                        searchInput.appendChild(searchSpan);
                    searchColumn.appendChild(searchInput);
                variableNameRow.appendChild(searchColumn);
            languageColumn.appendChild(variableNameRow);

                const languageTableRow = cTag('div',{ 'class':`flexSpaBetRow` });
                    const languageTableColumn = cTag('div',{ 'class':`columnXS12`,'style':`position:relative;` });
                        let noMoreTable = cTag('div',{ 'id':`no-more-tables` });
                            const languageTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                                const languageHead = cTag('thead',{ 'class':`cf` });
                                    const languageHeadRow = cTag('tr',{class:'outerListsTable'});

                                        let phpJSCol = cTag('th',{ 'width':`8%` });
                                        phpJSCol.innerHTML = 'PHP/JS';

                                        let engCol = cTag('th');
                                        engCol.innerHTML = Translate('English');
                                    languageHeadRow.append(phpJSCol,engCol);
                                languageHead.appendChild(languageHeadRow);
                            languageTable.appendChild(languageHead);
                            languageTable.appendChild(cTag('tbody',{ 'id':`tableRows` }));
                        noMoreTable.appendChild(languageTable);
                    languageTableColumn.appendChild(noMoreTable);
                languageTableRow.appendChild(languageTableColumn);
            languageColumn.appendChild(languageTableRow);
        callOutDiv.appendChild(languageColumn);

            let languageFormColumn = cTag('div',{ 'class':` columnXS12 columnMD4` });
                let languageFormRow = cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-top: 10px;" });
                    let languageFormTitle = cTag('div',{ 'class':`columnSM6` });
                        let languageFormHeader = cTag('h4',{ 'id':`formtitle`, 'style': "font-size: 18px;" });
                        languageFormHeader.innerHTML = 'Languages Form';
                    languageFormTitle.appendChild(languageFormHeader);
                languageFormRow.appendChild(languageFormTitle);
                
                    let languageColumn10 = cTag('div',{ 'class':`columnSM6`, 'align':`right`});
                    const OUR_DOMAINNAME = extractRootDomain(window.location.hostname);
                    if(OUR_DOMAINNAME !=='machouse.com.bd'){
                        const rewriteButton = cTag('button',{ 'class':`btn defaultButton` });
                        rewriteButton.innerHTML = 'Rewrite Language Files';
                        rewriteButton.addEventListener('click', rewrittenLangFile);
                        languageColumn10.appendChild(rewriteButton);
                    }
                languageFormRow.appendChild(languageColumn10);
            languageFormColumn.appendChild(languageFormRow);
                const languageForm = cTag('form',{ 'name':`frmlanguages`,'id':`frmlanguages`,'action':`#`,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
                languageForm.addEventListener('submit',AJsaveLanguage);
                   
                    let phpRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let phpLabel = cTag('label',{ 'for':`php_js` });
                        phpLabel.append('PHP/JS ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        phpLabel.appendChild(requireSpan);
                        phpLabel.append(':');
                    phpRow.appendChild(phpLabel);
                        let phpSelect = cTag('select',{ 'name':`php_js`,'id':`php_js`,'class':`form-control`,'required':`` });
                            let selectOption = cTag('option',{ 'value':`` });
                            selectOption.innerHTML = 'Select';
                        phpSelect.appendChild(selectOption);
                            let phPOption = cTag('option',{ 'selected':``,'value':`1` });
                            phPOption.innerHTML = 'PHP';
                        phpSelect.appendChild(phPOption);
                            let dualOption = cTag('option',{ 'value':`2` });
                            dualOption.innerHTML = 'PHP+JS';
                        phpSelect.appendChild(dualOption);
                            let onlyOption = cTag('option',{ 'value':`3` });
                            onlyOption.innerHTML = 'Only JS';
                        phpSelect.appendChild(onlyOption);
                    phpRow.appendChild(phpSelect);
                languageForm.appendChild(phpRow);
                
                    let englishRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let englishLabel = cTag('label',{ 'for':`english` });
                        englishLabel.append('English ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        englishLabel.appendChild(requireSpan);
                        englishLabel.append(':');
                    englishRow.appendChild(englishLabel);
                        let langInput = cTag('textarea',{ 'class':`form-control`,'name':`english`,'id':`english`,'rows':`3`,'cols':`30`,'required':`` });
                        langInput.addEventListener('blur',()=>AJgetTranslate(0))
                    englishRow.appendChild(langInput);
                    englishRow.appendChild(cTag('textarea',{ style:'display:none','name':`oldenglish`,'id':`oldenglish`,'rows':`3`,'cols':`30` }));
                languageForm.appendChild(englishRow);

                    let spanishRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let spanishLabel = cTag('label',{ 'for':`spanish` });
                        spanishLabel.append('Spanish ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        spanishLabel.appendChild(requireSpan);
                        spanishLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        spanishLabel.appendChild(translateLink);
                    spanishRow.appendChild(spanishLabel);
                    spanishRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`spanish`,'id':`spanish`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(spanishRow);

                    let frenchRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let frenchLabel = cTag('label',{ 'for':`french` });
                        frenchLabel.append('French ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        frenchLabel.appendChild(requireSpan);
                        frenchLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        frenchLabel.appendChild(translateLink);
                    frenchRow.appendChild(frenchLabel);
                    frenchRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`french`,'id':`french`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(frenchRow);

                    let greekRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let greekLabel = cTag('label',{ 'for':`greek` });
                        greekLabel.append('Greek ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        greekLabel.appendChild(requireSpan);
                        greekLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        greekLabel.appendChild(translateLink);
                    greekRow.appendChild(greekLabel);
                    greekRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`greek`,'id':`greek`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(greekRow);

                    let germanRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let germanLabel = cTag('label',{ 'for':`german` });
                        germanLabel.append('German ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        germanLabel.appendChild(requireSpan);
                        germanLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        germanLabel.appendChild(translateLink);
                    germanRow.appendChild(germanLabel);
                    germanRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`german`,'id':`german`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(germanRow);

                    let italianRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let italianLabel = cTag('label',{ 'for':`italian` });
                        italianLabel.append('Italian ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        italianLabel.appendChild(requireSpan);
                        italianLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        italianLabel.appendChild(translateLink);
                    italianRow.appendChild(italianLabel);
                    italianRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`italian`,'id':`italian`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(italianRow);

                    let dutchRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let dutchLabel = cTag('label',{ 'for':`dutch` });
                        dutchLabel.append('Dutch ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        dutchLabel.appendChild(requireSpan);
                        dutchLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        dutchLabel.appendChild(translateLink);
                    dutchRow.appendChild(dutchLabel);
                    dutchRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`dutch`,'id':`dutch`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(dutchRow);

                    let arabicRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let arabicLabel = cTag('label',{ 'for':`arabic` });
                        arabicLabel.append('Arabic ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        arabicLabel.appendChild(requireSpan);
                        arabicLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        arabicLabel.appendChild(translateLink);
                    arabicRow.appendChild(arabicLabel);
                    arabicRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`arabic`,'id':`arabic`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(arabicRow);

                    let chineseRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let chineseLabel = cTag('label',{ 'for':`chinese` });
                        chineseLabel.append('Chinese (Mandarin) ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        chineseLabel.appendChild(requireSpan);
                        chineseLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        chineseLabel.appendChild(translateLink);
                    chineseRow.appendChild(chineseLabel);
                    chineseRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`chinese`,'id':`chinese`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(chineseRow);

                    let hindiRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let hindiLabel = cTag('label',{ 'for':`hindi` });
                        hindiLabel.append('Hindi ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        hindiLabel.appendChild(requireSpan);
                        hindiLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        hindiLabel.appendChild(translateLink);
                    hindiRow.appendChild(hindiLabel);
                    hindiRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`hindi`,'id':`hindi`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(hindiRow);

                    let bengaliRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let bengaliLabel = cTag('label',{ 'for':`bengali` });
                        bengaliLabel.append('Bengali ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        bengaliLabel.appendChild(requireSpan);
                        bengaliLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        bengaliLabel.appendChild(translateLink);
                    bengaliRow.appendChild(bengaliLabel);
                    bengaliRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`bengali`,'id':`bengali`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(bengaliRow);

                    let portugueseRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let portugueseLabel = cTag('label',{ 'for':`portuguese` });
                        portugueseLabel.append('Portuguese ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        portugueseLabel.appendChild(requireSpan);
                        portugueseLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        portugueseLabel.appendChild(translateLink);
                    portugueseRow.appendChild(portugueseLabel);
                    portugueseRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`portuguese`,'id':`portuguese`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(portugueseRow);

                    let russianRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let russianLabel = cTag('label',{ 'for':`russian` });
                        russianLabel.append('Russian ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        russianLabel.appendChild(requireSpan);
                        russianLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        russianLabel.appendChild(translateLink);
                    russianRow.appendChild(russianLabel);
                    russianRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`russian`,'id':`russian`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(russianRow);

                    let japaneseRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let japaneseLabel = cTag('label',{ 'for':`japanese` });
                        japaneseLabel.append('Japanese ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        japaneseLabel.appendChild(requireSpan);
                        japaneseLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        japaneseLabel.appendChild(translateLink);
                    japaneseRow.appendChild(japaneseLabel);
                    japaneseRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`japanese`,'id':`japanese`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(japaneseRow);

                    let koreanRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let koreanLabel = cTag('label',{ 'for':`korean` });
                        koreanLabel.append('Korean ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        koreanLabel.appendChild(requireSpan);
                        koreanLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        koreanLabel.appendChild(translateLink);
                    koreanRow.appendChild(koreanLabel);
                    koreanRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`korean`,'id':`korean`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(koreanRow);

                    let turkeyRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let turkeyLabel = cTag('label',{ 'for':`turkey` });
                        turkeyLabel.append('Turkey ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        turkeyLabel.appendChild(requireSpan);
                        turkeyLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        turkeyLabel.appendChild(translateLink);
                    turkeyRow.appendChild(turkeyLabel);
                    turkeyRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`turkey`,'id':`turkey`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(turkeyRow);

                    let finnishRow = cTag('div',{ 'class':`flexColumn`, 'style': "margin-bottom: 10px;" });
                        let finnishLabel = cTag('label',{ 'for':`finnish` });
                        finnishLabel.append('Finnish ');
                            requireSpan = cTag('span',{ 'class':`required` });
                            requireSpan.innerHTML = '*';
                        finnishLabel.appendChild(requireSpan);
                        finnishLabel.append(': ');
                            translateLink = cTag('a',{ 'href':`javascript:void(0);` });
                            translateLink.addEventListener('click',()=>AJgetTranslate(1));
                            translateLink.innerHTML = 'Translate';
                        finnishLabel.appendChild(translateLink);
                    finnishRow.appendChild(finnishLabel);
                    finnishRow.appendChild(cTag('textarea',{ 'class':`form-control`,'name':`finnish`,'id':`finnish`,'rows':`3`,'cols':`30`,'required':`` }));
                languageForm.appendChild(finnishRow);

                    let buttonNames = cTag('div',{ 'class':`flexStartRow` });
                    buttonNames.appendChild(cTag('input',{ 'type':`hidden`,'name':`languages_id`,'id':`languages_id`,'value':0 }));
                        inputField = cTag('input',{ 'type':`reset`,'name':`reset`,'id':`reset`,'value':Translate('Cancel'), 'class':`btn defaultButton`, 'style': "display:none; margin-right: 10px;" });
                        inputField.addEventListener('click',resetLanForm);
                    buttonNames.appendChild(inputField);
                    buttonNames.appendChild(cTag('input',{ 'type':`button`,'name':`archive`,'id':`archive`,'value':Translate('Remove'), 'class':`btn archiveButton`,style:'display:none' }));
                    buttonNames.appendChild(cTag('input',{ 'type':`submit`,'id':`formsave`,'class':`btn saveButton`, 'style': "margin-left: 10px;", 'value':` Save `}));
                languageForm.appendChild(buttonNames);
            languageFormColumn.appendChild(languageForm);
        callOutDiv.appendChild(languageFormColumn);
    Dashboard.appendChild(callOutDiv);
    addPaginationRowFlex(languageColumn);

    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}

    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    const sorder_by = document.getElementById('sorder_by').value;
    checkAndSetSessionData('sorder_by', sorder_by, list_filters);
    const sphp_js = document.getElementById('sphp_js').value
    checkAndSetSessionData('sphp_js', sphp_js, list_filters);

    addCustomeEventListener('filter',filter_Admin_languages);
    addCustomeEventListener('loadTable',loadTableRows_Admin_languages);
    filter_Admin_languages();
}

async function filter_Admin_languages(){
    let page = 1;
	const limit = checkAndSetLimit();
    document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sorder_by'] = document.getElementById('sorder_by').value;
	jsonData['sphp_js'] = document.getElementById('sphp_js').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
    const url = "/Admin/AJgetPage_languages/filter";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        sessionStorage.setItem('list_filters', JSON.stringify(jsonData));
        const tbody =  document.getElementById('tableRows');
        tbody.innerHTML = '';
        const tableRows = data.tableRows;
        let langRow, jsPHPCol, englishCol, php_js, editlinkstart;            
        tableRows.forEach(oneRow =>{
            const languages_id = parseInt(oneRow[0]);
            php_js = oneRow[1];
            langRow = cTag('tr');
                editlinkstart = cTag('a', {class:"anchorfulllink", href:"javascript:void(0)", title:"Edit/View"});
                editlinkstart.innerHTML = php_js;
                editlinkstart.addEventListener('click',function(){
                    AJgetInfoLang(languages_id);
                });

                jsPHPCol = cTag('td', {'data-title': 'PHP/JS', 'align': 'left'});
                jsPHPCol.appendChild(editlinkstart);
            langRow.appendChild(jsPHPCol);

                editlinkstart = cTag('a', {class:"anchorfulllink", href:"javascript:void(0)", title:"Edit/View"});
                editlinkstart.innerHTML = oneRow[2];
                editlinkstart.addEventListener('click',function(){
                    AJgetInfoLang(languages_id);
                });
                englishCol = cTag('td', {'data-title':'English', 'align': 'left'});
                englishCol.appendChild(editlinkstart);
            langRow.appendChild(englishCol);
            tbody.appendChild(langRow);
        })
        document.getElementById("totalTableRows").value = data.totalRows;
        onClickPagination();
    }
}

async function loadTableRows_Admin_languages(){
	const jsonData = {};
	jsonData['sorder_by'] = document.getElementById('sorder_by').value;
	jsonData['sphp_js'] = document.getElementById('sphp_js').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
    
    const url = "/Admin/AJgetPage_languages";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        sessionStorage.setItem('list_filters', JSON.stringify(jsonData));
        const tbody =  document.getElementById('tableRows');
        tbody.innerHTML = '';
        
        const tableRows = data.tableRows;
        let langRow, jsPHPCol, englishCol, php_js, editlinkstart;            
        tableRows.forEach(oneRow =>{
            const languages_id = parseInt(oneRow[0]);
            php_js = oneRow[1];
            langRow = cTag('tr');
                editlinkstart = cTag('a', {class:"anchorfulllink", href:"javascript:void(0)", title:"Edit/View"});
                editlinkstart.innerHTML = php_js;
                editlinkstart.addEventListener('click',function(){
                    AJgetInfoLang(languages_id);
                });

                jsPHPCol = cTag('td', {'data-title': 'PHP/JS', 'align': 'left'});
                jsPHPCol.appendChild(editlinkstart);
            langRow.appendChild(jsPHPCol);

                editlinkstart = cTag('a', {class:"anchorfulllink", href:"javascript:void(0)", title:"Edit/View"});
                editlinkstart.innerHTML = oneRow[2];
                editlinkstart.addEventListener('click',function(){
                    AJgetInfoLang(languages_id);
                });
                englishCol = cTag('td', {'data-title':'English', 'align': 'left'});
                englishCol.appendChild(editlinkstart);
            langRow.appendChild(englishCol);
            tbody.appendChild(langRow);
        });
        onClickPagination();
    }
}

async function AJsaveLanguage(event){
	if(event){ event.preventDefault();}

	const jsonData = serialize("#frmlanguages");
    const url = '/Admin/AJsaveLanguage';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.savemsg !=='error'){
			resetLanForm();
			filter_Admin_languages();
			showTopMessage('success_msg', data.message);
		}
		else{
			showTopMessage('error_msg', data.message);
		}
	}
	return false;
}

async function AJgetInfoLang(languages_id){
    const jsonData = {};
	jsonData['languages_id'] = languages_id;
    const url = "/Admin/AJgetInfoLang";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		document.frmlanguages.languages_id.value = data.languages_id;
		document.frmlanguages.php_js.value = data.php_js;
		document.frmlanguages.english.value = data.english;
		document.frmlanguages.oldenglish.value = data.english;
		document.frmlanguages.spanish.value = data.spanish;
		document.frmlanguages.french.value = data.french;
		document.frmlanguages.greek.value = data.greek;
		document.frmlanguages.german.value = data.german;
		document.frmlanguages.italian.value = data.italian;
		document.frmlanguages.dutch.value = data.dutch;
		document.frmlanguages.arabic.value = data.arabic;
		document.frmlanguages.chinese.value = data.chinese;
		document.frmlanguages.hindi.value = data.hindi;
		document.frmlanguages.bengali.value = data.bengali;
		document.frmlanguages.portuguese.value = data.portuguese;
		document.frmlanguages.russian.value = data.russian;
		document.frmlanguages.japanese.value = data.japanese;
		document.frmlanguages.korean.value = data.korean;
		document.frmlanguages.turkey.value = data.turkey;
		document.frmlanguages.finnish.value = data.finnish;
		
		if(document.querySelector("#reset").style.display === 'none'){
            document.querySelector("#reset").style.display = '';
        }
        if(document.querySelector("#archive").style.display === 'none'){
            document.querySelector("#archive").style.display = '';
        }
		document.querySelector("#archive").addEventListener('click', function (){AJremove_tableRow('languages', data.languages_id, data.english, '');} )
	}                                          
}

async function AJgetTranslate(byforch) {
	const english = document.frmlanguages.english.value;
	const oldenglish = document.frmlanguages.oldenglish.value;
	const php_js = document.getElementById("php_js").value;
	
	if(oldenglish !== english || byforch===1){
		const jsonData = {};
		jsonData['english'] = english;
		const url = "/Admin/AJgetTranslate";

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
			document.frmlanguages.spanish.value = data.spanish;
			document.frmlanguages.french.value = data.french;
			document.frmlanguages.greek.value = data.greek;
			document.frmlanguages.german.value = data.german;
			document.frmlanguages.italian.value = data.italian;
			document.frmlanguages.dutch.value = data.dutch;
			document.frmlanguages.arabic.value = data.arabic;
			document.frmlanguages.chinese.value = data.chinese;
			document.frmlanguages.hindi.value = data.hindi;
			document.frmlanguages.bengali.value = data.bengali;
			document.frmlanguages.portuguese.value = data.portuguese;
			document.frmlanguages.russian.value = data.russian;
			document.frmlanguages.japanese.value = data.japanese;
			document.frmlanguages.korean.value = data.korean;
			document.frmlanguages.turkey.value = data.turkey;
			document.frmlanguages.finnish.value = data.finnish;            
		}
	}	
	return false;
}

function resetLanForm(){
	document.frmlanguages.languages_id.value = 0;
	document.frmlanguages.php_js.value = '';
	document.frmlanguages.english.value = '';
	document.frmlanguages.oldenglish.value = '';
	document.frmlanguages.spanish.value = '';
	document.frmlanguages.french.value = '';
	document.frmlanguages.greek.value = '';
	document.frmlanguages.german.value = '';
	document.frmlanguages.italian.value = '';
	document.frmlanguages.dutch.value = '';
	document.frmlanguages.arabic.value = '';
	document.frmlanguages.chinese.value = '';
	document.frmlanguages.hindi.value = '';
	document.frmlanguages.bengali.value = '';
	document.frmlanguages.portuguese.value = '';
	document.frmlanguages.russian.value = '';
	document.frmlanguages.japanese.value = '';
	document.frmlanguages.korean.value = '';
	document.frmlanguages.turkey.value = '';
	document.frmlanguages.finnish.value = '';
    if(document.querySelector("#reset").style.display !== 'none'){
        document.querySelector("#reset").style.display = 'none';
    }
    if(document.querySelector("#archive").style.display !== 'none'){
        document.querySelector("#archive").style.display = 'none';
    }
}

function rewrittenLangFile(){
    const formhtml = cTag('div');
	formhtml.innerHTML = '';
		const rewriteLanguageForm = cTag('form', {'action': "#", name: "rewrittenLangFileForm", id: "rewrittenLangFileForm", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
		rewriteLanguageForm.addEventListener('submit', confirmRewriteLangFiles);
			const rewriteLanguageColumn = cTag('div', {class: "columnXS12", 'style': "padding-top: 10px;"}); 
				const languageNameRow = cTag('div', {class: "flexSpaBetRow"}); 
                    const languageNameColumn = cTag('div', {class: "columnSM4", 'align': "left"});
						let languageNameLabel = cTag('label', {'for': "languageName"});
						languageNameLabel.innerHTML = 'Language Name:';
                    languageNameColumn.appendChild(languageNameLabel);
                languageNameRow.appendChild(languageNameColumn);
					let languageNameDropDown = cTag('div', {class: "columnSM8", 'align': "left"});
						let selectLanguageName = cTag('select', {'required': "required", class: "form-control", name: "languageName", id: "languageName"});
							let allOption = cTag('option', {'value': "All"});
							allOption.innerHTML = Translate('All');
                        selectLanguageName.appendChild(allOption);
                    languageNameDropDown.appendChild(selectLanguageName);
                languageNameRow.appendChild(languageNameDropDown);
            rewriteLanguageColumn.appendChild(languageNameRow);
        rewriteLanguageForm.appendChild(rewriteLanguageColumn);
	formhtml.appendChild(rewriteLanguageForm);
	popup_dialog600('Rewrite Language Files', formhtml, 'Rewrite', confirmRewriteLangFiles);
}

async function confirmRewriteLangFiles(hidePopup){
	const jsonData = serialize('#rewrittenLangFileForm');
    const url = '/Admin/languagesVarWrite/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.login === 'session_ended'){
			window.location = '/session_ended';
		}
		else if(data.returnmsg !=='error'){
			showTopMessage('error_msg', data.returnmsg);
			hidePopup();
		}
		else{
			showTopMessage('error_msg', 'Error! while rewriting language variables.');
		}
	}
}

//==============invoice-report==============
function invoicesReport(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    const rowHeight = 60;
    const totalRows = 0;

    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const invoiceCallOut = cTag('div',{ 'class':`innerContainer`,'style':`background:#FFF;`});
        invoiceCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`pageURI`,'id':`pageURI`,'value':segment1+ '/' + segment2 }));
        invoiceCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`page`,'id':`page`,'value':page }));
        invoiceCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`rowHeight`,'id':`rowHeight`,'value':rowHeight }));
        invoiceCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`totalTableRows`,'id':`totalTableRows`,'value':totalRows }));
        invoiceCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value': segment2 }));
            const invoiceTitle = cTag('div',{ 'class':`columnXS12 flexSpaBetRow outerListsTable` });
                const invoiceReportHeader = cTag('h2');
                invoiceReportHeader.innerHTML = "Invoices Report " 
                invoiceReportHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':'Invoices Report' }));
            invoiceTitle.appendChild(invoiceReportHeader);
            invoiceTitle.appendChild(getNavSection());
        invoiceCallOut.appendChild(invoiceTitle);

            const dropDownRow = cTag('div', {class: "flexEndRow outerListsTable"});  
                let orderColumn = cTag('div',{ 'class':`columnXS6 columnSM3` });
                    const ordByOpts = {
                        '0':'Invoice No. DESC',
                        '1':'Invoice No. ASC',
                        '2':'Paid Date DESC',
                        '3':'Paid Date ASC'
                    };

                    let selectOrder = cTag('select',{ 'class':`form-control`,'name':`sorder_by`,'id':`sorder_by` });
                    selectOrder.addEventListener('change',filter_Admin_invoicesReport);
                    for(const [optValue, optLabel] of Object.entries(ordByOpts)) { 
                        let orderOption = cTag('option', {value:optValue});
                        orderOption.innerHTML = optLabel;
                        selectOrder.appendChild(orderOption)
                    }
                orderColumn.appendChild(selectOrder);
            dropDownRow.appendChild(orderColumn);

                const paidColumn = cTag('div',{ 'class':`columnXS6 columnSM3` });
                    let selectPaid = cTag('select',{ 'class':`form-control`,'name':`spaid_by`,'id':`spaid_by` });
                    selectPaid.addEventListener('change',filter_Admin_invoicesReport);
                        let allOption = cTag('option', {value:""});
                        allOption.innerHTML = 'All';
                    selectPaid.appendChild(allOption)
                        let paypalOption = cTag('option', {value:"bKash"});
                        paypalOption.innerHTML = 'bKash';
                    selectPaid.appendChild(paypalOption)
                paidColumn.appendChild(selectPaid);
            dropDownRow.appendChild(paidColumn);

                const dateRangeColumn = cTag('div',{ 'class':`columnXS6 columnSM3 daterangeContainer` });
                    const dateRangeField = cTag('input',{ 'class':`form-control`, 'style': "padding-left: 35px;", 'name':`date_range`,'id':`date_range`,'value':``,'maxlength':`23`,'placeholder':Translate('Date Range') });
                    daterange_picker_dialog(dateRangeField);
                    dateRangeField.value = '';
                dateRangeColumn.appendChild(dateRangeField);
            dropDownRow.appendChild(dateRangeColumn);

                const searchColumn = cTag('div',{ 'class':`columnXS6 columnSM3` });
                    let searchInput = cTag('div',{ 'class':`input-group` });
                    searchInput.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Admin_invoicesReport),'type':`text`,'placeholder':`Search Invoice Number...`,'value':``,'id':`keyword_search`,'name':`keyword_search`,'class':`form-control`,'maxlength':`50` }));
                        let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':`Search Invoice Number...` });
                        searchSpan.addEventListener('click',filter_Admin_invoicesReport);
                        searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                    searchInput.appendChild(searchSpan);
                searchColumn.appendChild(searchInput);
            dropDownRow.appendChild(searchColumn);
        invoiceCallOut.appendChild(dropDownRow);

            const invoiceReportRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const invoiceReportColumn = cTag('div',{ 'class':`columnSM12`,'style':`position:relative;` });
                    const noMoreTable = cTag('div',{ 'id':`no-more-tables` });
                        const invoiceReportTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                            const invoiceReportHead = cTag('thead',{ 'class':`cf` });
                                const invoiceReportHeadRow = cTag('tr',{class:'outerListsTable'});
                                    const thCol0 = cTag('th',{ 'align':`left`});
                                    thCol0.innerHTML = Translate('Date');

                                    const thCol1 = cTag('th',{ 'align':`left` });
                                    thCol1.innerHTML = Translate('Invoice');
                                    
                                    const thCol3 = cTag('th',{ 'align':`left` });
                                    thCol3.innerHTML = Translate('Description');

                                    const thCol4 = cTag('th',{ 'align':`left` });
                                    thCol4.innerHTML = Translate('Paid By');

                                    const thCol5 = cTag('th',{ 'align':`left`});
                                    thCol5.innerHTML = 'Price/Location';

                                    const thCol6 = cTag('th',{ 'align':`left`});
                                    thCol6.innerHTML = Translate('Locations');

                                    const thCol7 = cTag('th',{ 'align':`left` });
                                    thCol7.innerHTML = Translate('Total');

                                    const thCol8 = cTag('th',{ 'align':`left` });
                                    thCol8.innerHTML = Translate('Next Payment Due');
                                    
                                invoiceReportHeadRow.append(thCol0,thCol1,thCol3,thCol4,thCol5,thCol6,thCol7,thCol8);
                            invoiceReportHead.appendChild(invoiceReportHeadRow);
                        invoiceReportTable.appendChild(invoiceReportHead);
                            const invoiceReportBody = cTag('tbody',{ 'id':`tableRows` });
                        invoiceReportTable.appendChild(invoiceReportBody);
                    noMoreTable.appendChild(invoiceReportTable);
                invoiceReportColumn.appendChild(noMoreTable);
            invoiceReportRow.appendChild(invoiceReportColumn);
        invoiceCallOut.appendChild(invoiceReportRow);
    Dashboard.appendChild(invoiceCallOut)
    addPaginationRowFlex(invoiceCallOut);

    addCustomeEventListener('filter',filter_Admin_invoicesReport);
    addCustomeEventListener('loadTable',loadTableRows_Admin_invoicesReport);
    filter_Admin_invoicesReport();
}

async function filter_Admin_invoicesReport(){
    let page = 1;   
    const limit = checkAndSetLimit();
    document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sorder_by'] = document.getElementById('sorder_by').value;
	jsonData['spaid_by'] = document.getElementById('spaid_by').value;
	jsonData['date_range'] = document.getElementById('date_range').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
    const url = "/Admin/AJgetPage_invoicesReport/filter";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        loadInvoiceReportTable(data.tableRows);         
        document.getElementById("totalTableRows").value = data.totalRows;
        onClickPagination();
    }
}

async function loadTableRows_Admin_invoicesReport(){
	const jsonData = {};
	jsonData['sorder_by'] = document.getElementById('sorder_by').value;
	jsonData['spaid_by'] = document.getElementById('spaid_by').value;
	jsonData['date_range'] = document.getElementById('date_range').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
		
    const url = "/Admin/AJgetPage_invoicesReport";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){			
        loadInvoiceReportTable(data.tableRows);
        onClickPagination();
    }
}

function loadInvoiceReportTable(tableRows){
    const titleColTitles = ['Date', 'Invoice', 'Description', 'Paid By', 'Price per Location', 'No of Location', 'Total', 'Next Payment Due'];
    const tbody =  document.getElementById('tableRows');
    tbody.innerHTML = '';
    let td,tr;
    tableRows.forEach(oneRow =>{
        tr = cTag('tr');
        let p = 0;
        let t = 1;
        oneRow.forEach(oneCol=>{
            
            let nowrap = '';
            if(p===0 || p===8){nowrap = 'nowrap'}
            let align = 'center'
            if(p===3){align = 'left'}
            else if(p===5 || p===6 || p===7){align = 'right'}
            
            td = cTag('td', { 'data-title': titleColTitles[p++], 'align': align});
            if([1,6].includes(t)) td.setAttribute('nowrap', nowrap);
            if(t==1 && oneRow[t] !=='Unpaid'){td.innerHTML = DBDateToViewDate(oneRow[t]);}
            else if(t==8){td.innerHTML = DBDateToViewDate(oneRow[t]);}
            else if(t>8){t++;return;}
            else{
                td.innerHTML = oneRow[t];
            }
            t++;
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
}

//==========all_notes============
async function our_notes(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const ourNotesCallOut = cTag('div',{ 'class':`innerContainer`,'style':`background:#FFF;`});
        ourNotesCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`pageURI`,'id':`pageURI`,'value':segment1+ '/' + segment2 }));
        ourNotesCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`page`,'id':`page`,'value':page }));
        ourNotesCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`rowHeight`,'id':`rowHeight`,'value':'50' }));
        ourNotesCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`totalTableRows`,'id':`totalTableRows`,'value':'0' }));
        ourNotesCallOut.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value': segment2 }));
            const ourNotesTitle = cTag('div',{ 'class':`columnXS12 flexSpaBetRow outerListsTable` });
                const ourNotesReportHeader = cTag('h2');
                ourNotesReportHeader.innerHTML = "All Notes " 
                ourNotesReportHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':'ourNotess Report' }));
            ourNotesTitle.appendChild(ourNotesReportHeader);
            ourNotesTitle.appendChild(getNavSection());
        ourNotesCallOut.appendChild(ourNotesTitle);                    

            const dropDownRow = cTag('div', {class: "flexEndRow outerListsTable"});  
                let orderColumn = cTag('div',{ 'class':`columnXS6 columnSM3` });
                    const ordByOpts = {
                        '0':'All Notes in DESC',
                        '1':'All Notes in ASC'
                    }
                    let selectOrder = cTag('select',{ 'class':`form-control`,'name':`sorder_by`,'id':`sorder_by` });
                    selectOrder.addEventListener('change',filter_Admin_our_notes);
                    for(const [optValue, optLabel] of Object.entries(ordByOpts)) { 
                        let orderOption = cTag('option', {value:optValue});
                        orderOption.innerHTML = optLabel;
                        selectOrder.appendChild(orderOption)
                    }
                orderColumn.appendChild(selectOrder);
            dropDownRow.appendChild(orderColumn);                

                const dateRangeColumn = cTag('div',{ 'class':`columnXS6 columnSM3 daterangeContainer` });
                    const dateRangeField = cTag('input',{ 'class':`form-control `, 'style': "padding-left: 35px;", 'name':`date_range`,'id':`date_range`,'value':``,'maxlength':`23`,'placeholder':Translate('Date Range') });
                    daterange_picker_dialog(dateRangeField);
                    dateRangeField.value = '';
                dateRangeColumn.appendChild(dateRangeField);
            dropDownRow.appendChild(dateRangeColumn);

                const searchColumn = cTag('div',{ 'class':`columnXS12 columnSM3` });
                    let searchInput = cTag('div',{ 'class':`input-group` });
                    searchInput.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Admin_our_notes),'type':`text`,'placeholder':`Search Notes....`,'value':``,'id':`keyword_search`,'name':`keyword_search`,'class':`form-control`,'maxlength':`50` }));
                        let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':`Search Notes...` });
                        searchSpan.addEventListener('click',filter_Admin_our_notes);
                        searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                    searchInput.appendChild(searchSpan);
                searchColumn.appendChild(searchInput);
            dropDownRow.appendChild(searchColumn);
        ourNotesCallOut.appendChild(dropDownRow);

            const ourNotesReportRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const ourNotesReportColumn = cTag('div',{ 'class':`columnSM12`,'style':`position:relative;` });
                    const noMoreTable = cTag('div',{ 'id':`no-more-tables` });
                        const ourNotesReportTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                            const ourNotesReportHead = cTag('thead',{ 'class':`cf` });
                                const ourNotesReportHeadRow = cTag('tr',{class:'outerListsTable'});
                                    const thCol0 = cTag('th',{'align':`left`});
                                    thCol0.innerHTML = 'Account#';

                                    const thCol1 = cTag('th',{'align':`left`});
                                    thCol1.innerHTML = 'Sub-domain';

                                    const thCol3 = cTag('th',{'align':`left`});
                                    thCol3.innerHTML = Translate('Date');

                                    const thCol4 = cTag('th',{'align':`left`});
                                    thCol4.innerHTML = Translate('Note Description');

                                ourNotesReportHeadRow.append(thCol0,thCol1,thCol3,thCol4);
                            ourNotesReportHead.appendChild(ourNotesReportHeadRow);
                        ourNotesReportTable.appendChild(ourNotesReportHead);
                            const ourNotesReportBody = cTag('tbody',{ 'id':`tableRows` });
                        ourNotesReportTable.appendChild(ourNotesReportBody);
                    noMoreTable.appendChild(ourNotesReportTable);
                ourNotesReportColumn.appendChild(noMoreTable);
            ourNotesReportRow.appendChild(ourNotesReportColumn);
        ourNotesCallOut.appendChild(ourNotesReportRow);
    Dashboard.appendChild(ourNotesCallOut)
    addPaginationRowFlex(ourNotesCallOut);

    addCustomeEventListener('filter',filter_Admin_our_notes);
    addCustomeEventListener('loadTable',loadTableRows_Admin_our_notes);
    filter_Admin_our_notes();
}

async function filter_Admin_our_notes(){
    let page = 1;
    const limit = checkAndSetLimit();
    document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sorder_by'] = document.getElementById('sorder_by').value;
	jsonData['date_range'] = document.getElementById('date_range').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;
	
    const url = "/Admin/AJgetPage_our_notes/filter";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let tableRows = document.getElementById('tableRows');
        tableRows.innerHTML = '';
        let tr,td;
        data.tableRows.forEach(item=>{
            tr = cTag('tr');
                td = cTag('td',{'data-title':'Account#'});
                td.innerText = item[1];
            tr.appendChild(td);
                td = cTag('td',{'data-title':'Sub-domain'});
                td.innerText = item[3];
            tr.appendChild(td);
                td = cTag('td',{'data-title':Translate('Date')});
                td.innerText = DBDateToViewDate(item[2]);
            tr.appendChild(td);
                td = cTag('td',{'data-title':Translate('Note Description')});
                    let edit = cTag('i',{'class':'fa fa-edit cursor', 'data-original-title':Translate('Edit Note')});
                    edit.addEventListener('click',()=>AJget_OurNotes(item[0],item[1]));
                td.append(item[4],' ',edit);
            tr.appendChild(td);
            tableRows.appendChild(tr);
        });
        document.getElementById("totalTableRows").value = data.totalRows;
        onClickPagination();
    }   
}

async function loadTableRows_Admin_our_notes(){
	const jsonData = {};
	jsonData['sorder_by'] = document.getElementById('sorder_by').value;
	jsonData['date_range'] = document.getElementById('date_range').value;
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = "/Admin/AJgetPage_our_notes";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let tableRows = document.getElementById('tableRows');
        tableRows.innerHTML = '';
        let tr,td;
        data.tableRows.forEach(item=>{
            tr = cTag('tr');
                td = cTag('td',{'data-title':'Account#'});
                td.innerText = item[1];
            tr.appendChild(td);
                td = cTag('td',{'data-title':'Sub-domain'});
                td.innerText = item[3];
            tr.appendChild(td);
                td = cTag('td',{'data-title':Translate('Date')});
                td.innerText = DBDateToViewDate(item[2]);
            tr.appendChild(td);
                td = cTag('td',{'data-title':Translate('Note Description')});
                    let edit = cTag('i',{'class':'fa fa-edit cursor', 'data-original-title':Translate('Edit Note')});
                    edit.addEventListener('click',()=>AJget_OurNotes(item[0],item[1]));
                td.append(item[4],' ',edit);
            tr.appendChild(td);
            tableRows.appendChild(tr);
        });
        onClickPagination();
    }
}

async function AJget_OurNotes(our_notes_id,saccounts_id){
	const jsonData = {};
	jsonData['our_notes_id'] = our_notes_id;
    const url = "/Admin/AJget_OurNotes/";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const ourNoteForm = cTag('form', {'action': "#", name: "frmOurNotes", id: "frmOurNotes", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
        ourNoteForm.addEventListener('submit', AJsave_OurNotes);
            const descriptionColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                const textarea = cTag('textarea',{'required': "required", name: "description", id: "description", class: "form-control placeholder", 'rows': 5, 'placeholder': "Enter Note..."});
                textarea.innerHTML = data.returnStr;
            descriptionColumn.appendChild(textarea);
        ourNoteForm.appendChild(descriptionColumn);

            const errorRow = cTag('div', {class: "flexSpaBetRow"});
                const errorSpan = cTag('span', {class: "error_msg", id: "errmsg_description"});
            errorRow.appendChild(errorSpan);
        ourNoteForm.appendChild(errorRow);
        if(document.getElementById('pageURI').value==='Admin/our_notes')ourNoteForm.appendChild(cTag('input', {'type': "hidden", name: "saccounts_id", id: "saccounts_id", 'value': saccounts_id}));
        ourNoteForm.appendChild(cTag('input', {'type': "hidden", name: "our_notes_id", id: "our_notes_id", 'value': our_notes_id}));

        popup_dialog600('Add New Note', ourNoteForm, 'Save', AJsave_OurNotes);
                
        setTimeout(function() {
            document.getElementById("description").focus();
            
            document.querySelector(".placeholder").addEventListener('focus', e => {
                if(e.target.value ===''){
                    e.target.placeholder = '';
                }
            });
            
            document.querySelector(".placeholder").addEventListener('blur', e => {
                if(e.target.value===''){
                    const altval = e.target.alt;
                    e.target.placeholder = altval;
                }
            });
        }, 1000);
    }
}

async function AJsave_OurNotes(hidePopup){
	const accounts_id = document.getElementById("saccounts_id").value;
	const our_notes_id =  document.getElementById("our_notes_id").value;
	const description = document.frmOurNotes.description;
	const oElement = document.getElementById('errmsg_description');
	oElement.innerHTML = "";
	if(description.value === ""){
		oElement.innerHTML = 'Missing note';
		description.focus();
		return(false);
	}
	else{
		actionBtnClick('.btnmodel', Translate('Saving'), 1); 
		
		const jsonData = {};
		jsonData['our_notes_id'] = our_notes_id;
		jsonData['description'] = description.value;
		jsonData['accounts_id'] = accounts_id;
		
		const url = "/Admin/AJsave_OurNotes/";

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
			if(data.returnStr ==='Save'){
				// filter_Admin_our_notes();
                triggerEvent('filter')
				hidePopup();
			}
			else{
				showTopMessage('error_msg', data.returnStr);
                actionBtnClick('.btnmodel', Translate('Save'), 0);
			}
		}
	}
	return false;
}

function extractRootDomain(url) {
    let domain = url;
    const splitArr = domain.split('.');
    const arrLen = splitArr.length;
    if (arrLen > 2) {
        domain = splitArr[arrLen - 2]+ '.' + splitArr[arrLen - 1];
    }
    return domain;
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists,edit,importCustomers,importProduct,languages,login_message,popup_message,invoicesReport,our_notes}
    layoutFunctions[segment2]();
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});

