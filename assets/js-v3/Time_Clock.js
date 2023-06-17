import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, DBDateToViewDate, DBDateRangeToViewDate, confirm_dialog, setTableRows, 
    showTopMessage, setOptions, addPaginationRowFlex, checkAndSetSessionData, getDeviceOperatingSystem, AJarchive_tableRow, 
    AJremove_tableRow, popup_dialog600, popup_dialog1000, date_picker, daterange_picker_dialog, applySanitizer, AJget_notesData, 
    AJget_notesPopup, unarchiveData, fetchData, listenToEnterKey, addCustomeEventListener, actionBtnClick,
    serialize, onClickPagination
} from './common.js';
if(segment2==='') segment2 = 'lists';
const listsFieldAttributes = [{'datatitle':Translate('Name'), 'align':'left'},
                    {'datatitle':Translate('Employee Number'), 'align':'right'},
                    {'datatitle':Translate('PIN'), 'align':'right'}];
const uriStr = segment1+'/view';
const historyFieldAttributes = [{'datatitle':Translate('Day of the Week'), 'align':'left'},
                    {'datatitle':Translate('Clock In Date'), 'align':'center'},
                    {'datatitle':Translate('Clock In Time'), 'align':'right'},
                    {'datatitle':Translate('Clock Out Date'), 'align':'center'},
                    {'datatitle':Translate('Clock Out Time'), 'align':'right'},
                    {'datatitle':Translate('Time'), 'align':'right'},
                    {'datatitle':Translate('Action'), 'align':'center' }];
function getTodayDate(){
    let d = new Date();
	let dd = d.getDate();
	if(dd<10) dd = '0'+dd;
	let mm = d.getMonth()+1;
	if(mm<10) mm = '0'+mm;
	let yy = d.getFullYear();
	if(calenderDate.toLocaleLowerCase()==='dd-mm-yyyy') return `${dd}-${mm}-${yy}`;
	else return `${mm}/${dd}/${yy}`;
}
async function filter_Time_Clock_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.getElementById("sdata_type").value;
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetPage/filter';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        setTableRows(data.tableRows, listsFieldAttributes, uriStr);
        document.getElementById("totalTableRows").value = data.totalRows;
        storeSessionData(jsonData);
        onClickPagination();
    }
}
async function loadTableRows_Time_Clock_lists(){
	const jsonData = {};
	jsonData['sdata_type'] = document.getElementById("sdata_type").value;
	jsonData['sorting_type'] = document.getElementById("sorting_type").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        setTableRows(data.tableRows, listsFieldAttributes, uriStr);
        onClickPagination();
    }
}
function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}
   
    let input,list_filters;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
     //======Hidden Fields for Pagination=======//
     [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '30' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });
        const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable"});
            const titleName = cTag('div', {class: "columnXS12 columnSM4 columnLG6"});
                const headerTitle = cTag('h2', {'style': "text-align: start;"});
                headerTitle.innerHTML = Translate('Time Clock Manager')+' ';
                    const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Time Clock Manager')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class: "columnXS12 columnSM8 columnLG6", 'style': "text-align: end;"});
                const createButton = cTag('a', {'href': "javascript:void(0);", title: Translate('Create Employee'), class: "btn cursor createButton", 'style': "margin-left: 15px; margin-bottom: 4px;"});
                createButton.addEventListener('click', AJgetPopup_Time_Clock);
                createButton.append(cTag('i',{ 'class':`fa fa-plus` }),' ', Translate('Create Employee'));
                const timeReportButton = cTag('a', {'href': "/Time_Clock/report", class: "btn defaultButton", 'style': "margin-left: 10px; margin-bottom: 4px;", title: Translate('Time Report')});
                timeReportButton.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Time Report'));
                const enableDisableBtn = cTag('a', {'href': "javascript:void(0);"});
                const timeclock_enabled = parseInt(document.getElementById("timeclock_enableds").value);
                let tceTitle = Translate('Enable Time Clock Now');
                let tceClass = 'color: #090;';
                let iClass = 'fa fa-check';
                if(timeclock_enabled===1){
                    tceTitle = Translate('Disable Time Clock');
                    tceClass = 'color: #F00;';
                    iClass = 'fa fa-close';
                }
                enableDisableBtn.setAttribute('title',tceTitle);
                enableDisableBtn.setAttribute('class','btn cursor defaultButton ');
                enableDisableBtn.setAttribute('style', "margin-left: 15px; margin-bottom: 4px;"+' '+ tceClass);
                enableDisableBtn.addEventListener('click', AJupdateDefaultTimeClock);
                    const i = cTag('i', {class: iClass});
                enableDisableBtn.append(i,' '+ tceTitle);
            buttonsName.append(enableDisableBtn, timeReportButton, createButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);
        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            const archiveDropDown = cTag('div', {class: "columnXS6 columnMD3"});
                const selectArchive = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type"});
                selectArchive.addEventListener('change', filter_Time_Clock_lists);
                setOptions(selectArchive, {'All':Translate('All Employee'), 'Archived':Translate('Archived Employee')}, 1, 0);
            archiveDropDown.appendChild(selectArchive);
        filterRow.appendChild(archiveDropDown);
            const sortDropDown = cTag('div', {class: "columnXS6 columnMD3"});
                const selectSorting = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type"});
                selectSorting.addEventListener('change', filter_Time_Clock_lists);
                const options = {
                    '0':Translate('First and Last Name'), 
                    '1':Translate('First Name'),
                    '2':Translate('Last Name')
                };
                for(const [key, value] of Object.entries(options)) {
                    const sortingOption = cTag('option', {'value': key});
                    sortingOption.innerHTML = value;
                    selectSorting.appendChild(sortingOption);
                }
            sortDropDown.appendChild(selectSorting);
        filterRow.appendChild(sortDropDown);
            const searchDiv = cTag('div', {class: "columnXS6 columnMD3"});
                const SearchInGroup = cTag('div', {class: "input-group"});
                    const searchField = cTag('input', {'keydown':listenToEnterKey(filter_Time_Clock_lists), 'type': "text", 'placeholder': Translate('Search Time Clock'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
                SearchInGroup.appendChild(searchField);
                    const span = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Time Clock')});
                    span.addEventListener('click', filter_Time_Clock_lists);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    span.appendChild(searchIcon);
                SearchInGroup.appendChild(span);
            searchDiv.appendChild(SearchInGroup);
        filterRow.appendChild(searchDiv);
    showTableData.appendChild(filterRow);
        const divTableRow = cTag('div', {class: "columnXS12"});
            const divNoMore = cTag('div', {id: "no-more-tables"});
                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                    const listHead = cTag('thead', {class: "cf"});
                        const columnNames = listsFieldAttributes.map(colObj=>(colObj.datatitle));
                        const listHeadRow = cTag('tr',{class:'outerListsTable'});
                            const thCol0 = cTag('th');
                            thCol0.innerHTML = columnNames[0];
                            const thCol1 = cTag('th', {'width': "25%"});
                            thCol1.innerHTML = columnNames[1];
                            const thCol2 = cTag('th', {'width': "15%"});
                            thCol2.innerHTML = columnNames[2];
                        listHeadRow.append(thCol0, thCol1, thCol2);
                    listHead.appendChild(listHeadRow);
                listTable.appendChild(listHead);
                    const listBody = cTag('tbody', {id: "tableRows"});
                listTable.appendChild(listBody);
            divNoMore.appendChild(listTable);
        divTableRow.appendChild(divNoMore);
    showTableData.appendChild(divTableRow);
    addPaginationRowFlex(showTableData);
     //=======sessionStorage =========//
     if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    
    const sorting_type = '0';
    checkAndSetSessionData('sorting_type', sorting_type, list_filters);
    const sdata_type = 'All';
    checkAndSetSessionData('sdata_type', sdata_type, list_filters);
    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;
    addCustomeEventListener('filter',filter_Time_Clock_lists);
    addCustomeEventListener('loadTable',loadTableRows_Time_Clock_lists);
    filter_Time_Clock_lists(true);
}
async function filter_Time_Clock_view(){
    let page = 1;
	document.getElementById("page").value = page;

	const jsonData = {};
	jsonData['user_id'] = document.getElementById("table_idValue").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetHPage/filter';
    fetchData(afterFetch,url,jsonData);
    
	function afterFetch(data){        
        let tbody = document.getElementById('tableRows');
        tbody.innerHTML = '';
        if(data.tableRows.length>0){
            data.tableRows.forEach(item=>{
                const tr = cTag('tr');
                item.forEach((info,indx)=>{
                    if(indx==0) return;
                    if([2,4].indexOf(indx) !== -1){
                        info = DBDateToViewDate(info, 1, 1)[0];
                    }
                    if([3,5].indexOf(indx) !== -1){
                        info = DBDateToViewDate(info, 1, 0)[1];
                    }
                    const td = cTag('td');
                    const attributes = historyFieldAttributes[indx-1];
                    for (let key in attributes) {
                        const value = attributes[key];
                        if(key=='datatitle') key = 'data-title';
                        td.setAttribute(key,value);
                    }
                    if(info==''){info = '\u2003';}
                    td.innerHTML = info;
                    tr.appendChild(td);
                })
                const td = cTag('td', {'valign':'top', 'data-title':Translate('Action'), 'align':'center' });
                td.append(
                    cTag('i',{ 'class':`fa fa-edit`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'click':()=>AJgetHPopup_Time_Clock(item[0]),'data-original-title':Translate('Edit Time Clock') }),
                    '  ',
                    cTag('i',{ 'class':`fa fa-trash-o`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'click':()=>AJremove_tableRow('time_clock', item[0], 'Time Clock', '', AJget_notesData),'data-original-title':Translate('Remove Time Clock') })
                )                     
                tr.appendChild(td);
                tbody.appendChild(tr);
            })
        }
        else{               
            const tr = cTag('tr');
                const td = cTag('td',{ 'colspan':`7`});
                td.innerHTML = '';
            tr.appendChild(td);
            tbody.appendChild(tr);
        }
        document.getElementById("totalTableRows").value = data.totalRows;
        
        onClickPagination();
    }
}
async function loadTableRows_Time_Clock_view(){
	const jsonData = {};
	jsonData['user_id'] = document.getElementById("table_idValue").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetHPage';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        setTableRows(data.tableRows, historyFieldAttributes, '');
        onClickPagination();
    }
}
async function Time_Clock_view_MoreInfo(){
	const user_id = document.getElementById("table_idValue").value;
	const jsonData = {};
	jsonData['user_id'] = user_id;
    const url = '/'+segment1+'/AJ_view_MoreInfo';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        const viewBasicInfo = document.getElementById("viewBasicInfo");
        viewBasicInfo.innerHTML = '';
            const nameHeader = cTag('h3');
            nameHeader.innerHTML = data.name;
        viewBasicInfo.appendChild(nameHeader);
            
            let cardDiv = cTag('div', {'style': "margin-bottom: 10px;"});
                let cardIcon = cTag('i', {class: "fa fa-id-card-o", 'style': "font-size: 16px;"});
            cardDiv.appendChild(cardIcon);
                let employeeNoTitle = cTag('span', {'style': "padding-left: 15px; color: #969595; font-weight: bold;"});
                employeeNoTitle.innerHTML = Translate('Employee Number')+' : ';
            cardDiv.appendChild(employeeNoTitle);
                let employeeNoValue = cTag('span',{'style': 'font-weight: bold; padding-left: 15px;'});
                employeeNoValue.innerHTML = data.userEmpNo;
            cardDiv.append(employeeNoValue);
        viewBasicInfo.appendChild(cardDiv);
        
            let PinDiv = cTag('div', {'style': "margin-bottom: 10px;"});
                let thumbIcon = cTag('i', {class: "fa fa-thumb-tack", 'style': "font-size: 16px;"});
            PinDiv.appendChild(thumbIcon);
                let pinTitle = cTag('span', {'style': "padding-left: 15px; color: #969595; font-weight: bold;"});
                pinTitle.innerHTML = Translate('PIN')+' : ';
            PinDiv.appendChild(pinTitle);
                let pinValue = cTag('span',{'style':'font-weight: bold; padding-left: 15px;'});
                pinValue.innerHTML = data.pin;
            PinDiv.append(pinValue);
        viewBasicInfo.appendChild(PinDiv);
        
        if(data.user_publish===1){
            let buttonNames = cTag('div');
                const editButton = cTag('button', {class: "btn editButton", 'href': "javascript:void(0);", title: Translate('Change Information')});
                editButton.addEventListener('click', AJgetPopup_Time_Clock);
                editButton.innerHTML = Translate('Edit');
            buttonNames.appendChild(editButton);
            if(data.hasOwnProperty('user_email') && data.user_email.length<4){
                    const archiveButton = cTag('a',{ 'class':`btn archiveButton`, 'style': "margin-left: 10px;", 'title':Translate('Archive'),'href':`javascript:void(0);`,'click':()=>archiveEmployee(data.user_id, data.name) });
                    archiveButton.innerHTML = Translate('Archive');
                buttonNames.appendChild(archiveButton);
            }
            viewBasicInfo.appendChild(buttonNames);
        }
        else{
            const unarchiveButton = cTag('a',{ 'class':`btn bgcoolblue`,'title':Translate('Unarchive'),'href':`javascript:void(0);`,'click':()=> unarchiveEmployee(data.user_id)});
            unarchiveButton.innerHTML = Translate('Unarchive');
            viewBasicInfo.appendChild(unarchiveButton);
        }
        filter_Time_Clock_view();
    }
}
function view(){
    let segment4 = 1;
    if(pathArray.length>4){segment4 = pathArray[4];}
    
    let user_id = parseInt(segment3);
    if(user_id==='' || isNaN(user_id)){user_id = 0;}
    
    let page = parseInt(segment4);
    if(page==='' || isNaN(page)){page = 1;}
    
    let widget,input,widgetHeader,widgetContent;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Time Clock Information')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays the information of employee')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
            const listButton = cTag('a', {'href': "/Time_Clock/lists", class: "btn defaultButton", title: Translate('Time Clock List')});
            listButton.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Time Clock List'));
        titleRow.appendChild(listButton);
    showTableData.appendChild(titleRow);
            const imgContainer = cTag('div', {class: "columnXS12"});
                const header = cTag('header', {class: "imageContainer flexSpaBetRow", 'style': "padding-left: 5px;"});
                    const headerColumn = cTag('div', {class: "columnSM3"});
                        const divImg = cTag('div', {class: "image"});
                            const img = cTag('img', {class: "img-responsive", 'alt': Translate('My Profile'), 'src': "/assets/images/man.jpg"});
                        divImg.appendChild(img);
                    headerColumn.appendChild(divImg);
                header.appendChild(headerColumn);
                    const imgContentDiv = cTag('div', {class: "columnSM9"});
                        const imgContent = cTag('div', {class: "image_content", 'style': "text-align: left;", id: "viewBasicInfo"});
                    imgContentDiv.appendChild(imgContent);
                header.appendChild(imgContentDiv);
            imgContainer.appendChild(header);
    showTableData.appendChild(imgContainer);
        const parentRow = cTag('div', {class: "flexSpaBetRow"});
            const parentColumn = cTag('div', {class: "columnMD12"});
                widget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
                //======Hidden Fields for Pagination=======//
                [
                    { name: 'pageURI', value: segment1+'/'+segment2+'/'+segment3},
                    { name: 'page', value: page },
                    { name: 'rowHeight', value: '34' },
                    { name: 'totalTableRows', value: 0 },
                ].forEach(field=>{
                    input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
                    widget.appendChild(input);
                });
                    widgetHeader = cTag('div', {class: "cardHeader flexSpaBetRow"});
                        const widgetHeaderTitle = cTag('h3');
                        widgetHeaderTitle.innerHTML = Translate('Employee Clock In & Out History');
                    widgetHeader.appendChild(widgetHeaderTitle);
                        const newEntryBtn = cTag('button',{'class':'btn defaultButton', 'style': "margin: 2px 5px;"});
                        newEntryBtn.innerHTML = 'Add New Entry';
                        newEntryBtn.addEventListener('click',()=>{AJgetHPopup_Time_Clock(0)});
                    widgetHeader.appendChild(newEntryBtn);
                widget.appendChild(widgetHeader);
                    widgetContent = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
                        const widgetContentRow = cTag('div', {class: "flex"});
                            const widgetContentColumn = cTag('div', {class: "columnXS12", 'style': "padding: 0; margin: 0;"});
                                const noMoreTables = cTag('div', {id: "no-more-tables"});
                                    const viewTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                        const viewHead = cTag('thead', {class: "cf"});
                                            const columnNames = historyFieldAttributes.map(colObj=>(colObj.datatitle));
                                            const viewHeadRow = cTag('tr');
                                                const thCol0 = cTag('th');
                                                thCol0.innerHTML = columnNames[0];
                                                const thCol1 = cTag('th', {'style': "min-width: 80px;"});
                                                thCol1.innerHTML = columnNames[1];
                                                const thCol2 = cTag('th', {'style': "min-width: 80px;"});
                                                thCol2.innerHTML= columnNames[2];
                                                const thCol3 = cTag('th', {'style': "min-width: 80px;"});
                                                thCol3.innerHTML = columnNames[3];
                                                const thCol4 = cTag('th', {'style': "min-width: 80px;"});
                                                thCol4.innerHTML = columnNames[4];
                                                const thCol5 = cTag('th');
                                                thCol5.innerHTML = columnNames[5];
                                                const thCol6 = cTag('th');
                                                thCol6.innerHTML = columnNames[6];
                                            viewHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6);
                                        viewHead.appendChild(viewHeadRow);
                                    viewTable.appendChild(viewHead);
                                        const viewBody = cTag('tbody', {id: "tableRows"});
                                    viewTable.appendChild(viewBody);
                                noMoreTables.appendChild(viewTable);
                            widgetContentColumn.appendChild(noMoreTables);
                        widgetContentRow.appendChild(widgetContentColumn);
                    widgetContent.appendChild(widgetContentRow);
                    addPaginationRowFlex(widgetContent);
                widget.appendChild(widgetContent);
            parentColumn.appendChild(widget);
        parentRow.appendChild(parentColumn);
            
            const noteParentColumn = cTag('div', {class: "columnMD12"});
                widget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
                    input = cTag('input', {'type': "hidden", name: "note_forTable", id: "note_forTable", 'value': "user"});
                widget.appendChild(input);
                    input = cTag('input', {'type': "hidden", name: "table_idValue", id: "table_idValue", 'value': user_id});
                widget.appendChild(input);
                    input = cTag('input', {'type': "hidden", name: "publicsShow", id: "publicsShow", 'value': 0});
                widget.appendChild(input);  
                    widgetHeader = cTag('div', {class: "cardHeader flexSpaBetRow"});
                        const widgetNoteTitle = cTag('h3');
                        widgetNoteTitle.innerHTML = Translate('Note History');
                    widgetHeader.appendChild(widgetNoteTitle);
                        const noteButton = cTag('button', {class: "btn defaultButton", 'href': "javascript:void(0);", title: Translate('List Products'), 'style': "margin: 2px 5px;"});
                        noteButton.addEventListener('click', function (){AJget_notesPopup(0);});
                        noteButton.innerHTML = Translate('Add New Note');
                        if(OS !='unknown'){
                            noteButton.innerHTML = '';
                            noteButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', `${Translate('Note')}`);
                        }
                    widgetHeader.appendChild(noteButton);
                widget.appendChild(widgetHeader);
                    widgetContent = cTag('div', {class: "cardContent"});
                        const noteListRow = cTag('div',{class:"flexSpaBetRow"});
                            const noteListSection = cTag('section', {class: "comments", 'style': "font-size: 16px; padding: 0px 20px;", id: "noteslist"});
                        noteListRow.appendChild(noteListSection);
                    widgetContent.appendChild(noteListRow);
                widget.appendChild(widgetContent);
            noteParentColumn.appendChild(widget);
        parentRow.appendChild(noteParentColumn);
    showTableData.appendChild(parentRow);
    addCustomeEventListener('filter',filter_Time_Clock_view);
    addCustomeEventListener('loadTable',loadTableRows_Time_Clock_view);
    Time_Clock_view_MoreInfo();
    AJget_notesData();
}
async function AJgetPopup_Time_Clock(){
    let user_id = 0;
    if(document.getElementById("table_idValue")){
        user_id = document.getElementById("table_idValue").value;
    }
	const jsonData = {};
	jsonData['user_id'] = user_id;
    const url = '/'+segment1+'/AJgetPopup';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        let requiredField, inputField;
        const formDialog = cTag('div');
            const errorMsg = cTag('div', {id: "error_employee", class: "errormsg"});
        formDialog.appendChild(errorMsg);
            const employeeInfoForm = cTag('form', {'action': "#", id: "frmEmployee", name: "frmEmployee", 'enctype': "multipart/form-data", 'method': "post", onsubmit:'saveTime_ClockForm(event)', 'accept-charset': "utf-8"});                
                const firstNameRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
                    const firstNameColumn = cTag('div', {class: "columnSM4"});
                        const firstNameLabel = cTag('label', {'for': "user_first_name"});
                        firstNameLabel.innerHTML = Translate('First Name');
                            requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                        firstNameLabel.appendChild(requiredField);
                    firstNameColumn.appendChild(firstNameLabel);
                firstNameRow.appendChild(firstNameColumn);
                    const firstNameField =  cTag('div', {class: "columnSM8"});
                        inputField = cTag('input', {'type': "text", class: "form-control requiredField submitForm", name: "user_first_name", id: "user_first_name", 'value': data.user_first_name, 'maxlength': 12, title: Translate('First Name')});                      
                    firstNameField.appendChild(inputField);
                    firstNameField.appendChild(cTag('div', {id: "error_user_first_name", class: "errormsg"}));
                firstNameRow.appendChild(firstNameField);
            employeeInfoForm.appendChild(firstNameRow);
            
                const lastNameRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
                    const lastNameColumn = cTag('div', {class: "columnSM4"});
                        const lastNameLabel = cTag('label', {'for': "user_last_name"});
                        lastNameLabel.innerHTML = Translate('Last Name');
                    lastNameColumn.appendChild(lastNameLabel);
                lastNameRow.appendChild(lastNameColumn);
                    const lastNameField = cTag('div', {class: "columnSM8"});
                        inputField = cTag('input', {'type': "text", class: "form-control submitForm", name: "user_last_name", id: "user_last_name", 'value': data.user_last_name, 'maxlength': 17});
                    lastNameField.appendChild(inputField);
                lastNameRow.appendChild(lastNameField);
            employeeInfoForm.appendChild(lastNameRow);
                const employeeNoRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
                    const employeeNoColumn = cTag('div', {class: "columnSM4"});
                        const employeeNoLabel = cTag('label', {'for': "userEmpNo"});
                        employeeNoLabel.innerHTML = Translate('Employee Number');
                            requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                        employeeNoLabel.appendChild(requiredField);
                    employeeNoColumn.appendChild(employeeNoLabel);
                employeeNoRow.appendChild(employeeNoColumn);
                    const employeeNoField = cTag('div', {class: "columnSM8"});
                        inputField = cTag('input', {'type': "text", class: "form-control requiredField submitForm", name: "userEmpNo", id: "userEmpNo", 'value': data.userEmpNo, 'maxlength': 20, title: Translate('Employee Number')});
                    employeeNoField.appendChild(inputField);
                    employeeNoField.appendChild(cTag('div', {id: "error_userEmpNo", class: "errormsg"}));
                employeeNoRow.appendChild(employeeNoField);
            employeeInfoForm.appendChild(employeeNoRow);
                const pinRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
                    const pinColumn = cTag('div', {class: "columnSM4"});
                        const pinLabel = cTag('label', {'for': "pin"});
                        pinLabel.innerHTML = Translate('PIN');
                            requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                        pinLabel.appendChild(requiredField);
                    pinColumn.appendChild(pinLabel);
                pinRow.appendChild(pinColumn);
                    const pinField = cTag('div', {class: "columnSM8"});
                        inputField = cTag('input', {'type': "text", class: "form-control requiredField submitForm", name: "pin", id: "pin", 'value': data.pin, 'maxlength': 10, title: Translate('PIN')});
                    pinField.appendChild(inputField);
                    pinField.appendChild(cTag('div', {id: "error_pin", class: "errormsg"}));
                pinRow.appendChild(pinField);
            employeeInfoForm.appendChild(pinRow);
                inputField = cTag('input', {'type': "hidden", name: "user_id", 'value': user_id});
            employeeInfoForm.appendChild(inputField);
        formDialog.appendChild(employeeInfoForm);
        
        popup_dialog600(Translate('Employee Information'),formDialog,Translate('Save'), saveTime_ClockForm);
        applySanitizer(formDialog);
    }
	return true;
}
async function saveTime_ClockForm(hidePopup){
	let errorFormsData = document.getElementById("error_employee");
	errorFormsData.innerHTML = '';
	
	if(document.getElementsByClassName("requiredField").length>0){
		const requiredFields = document.getElementsByClassName("requiredField");
		for(let l = 0; l<requiredFields.length; l++){
			const oneFieldVal = requiredFields[l].value;
            let errorTime = document.getElementById("error_"+ requiredFields[l].getAttribute('name'));
			if(oneFieldVal===''){
                errorTime.innerHTML = requiredFields[l].title+' '+Translate('is missing.');
				requiredFields[l].focus();
                requiredFields[l].classList.add('errorFieldBorder');
				return false;
			}
            else{
                errorTime.innerHTML = '';
                requiredFields[l].classList.remove('errorFieldBorder');
            }
		}
	}
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
    		
    const jsonData = serialize('#frmEmployee');
    
    const url = '/'+segment1+'/AJsaveTime_Clock';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        if(data.savemsg !=='error'){	
			hidePopup();		
			location.reload();
		}
        else if(data.returnStr=='errorOnAdding'){
			errorFormsData.innerHTML = Translate('Error occured while adding new employee! Please try again.');
            actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else if(data.returnStr=='Name_Already_Exist'){
			errorFormsData.innerHTML = Translate('This employee number already exists.');
            actionBtnClick('.btnmodel', Translate('Save'), 0);
		}  
		else{
			errorFormsData.innerHTML = Translate('No changes / Error occurred while updating data! Please try again.');
            actionBtnClick('.btnmodel', Translate('Save'), 0);
		} 
    }
	return false;
}
async function AJupdateDefaultTimeClock(e){
    let timeclock_enabled = parseInt(document.getElementById("timeclock_enableds").value);
    if(timeclock_enabled===1){timeclock_enabled = 0;}
    else{timeclock_enabled = 1;}
	const jsonData = {};
	jsonData['fieldval'] = timeclock_enabled;
    const url = '/'+segment1+'/AJupdateDefaultTimeClock';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        if(data.returnStr===1){
            showTopMessage('success_msg', Translate('Updated successfully.'));
            location.reload();
		}
		else{
            showTopMessage('alert_msg', Translate('Could not update'));
		}
    }
}
function archiveEmployee(user_id, employeeName){
	AJarchive_tableRow('user', 'user_id', user_id, 'Employee: '+employeeName, 'user_publish', '/Time_Clock/lists');
}
async function unarchiveEmployee(user_id){    
    confirm_dialog(Translate('Employee')+' '+Translate('Unarchive'), Translate('Are you sure you want to unarchive this?'),(hidePopup)=>{		
		unarchiveData(`/Time_Clock/view/${user_id}`,  {tablename:'user', tableidvalue:user_id, publishname:'user_publish'});
		hidePopup();
	});
}
async function AJgetHPopup_Time_Clock(time_clock_id){
    const jsonData = {};
	jsonData['time_clock_id'] = time_clock_id;
	jsonData['tuser_id'] = segment3;
    const url = '/'+segment1+'/AJgetHPopup';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        let span, employeeNoLabel, requiredField, inputField;
        const formDialog = cTag('div');
            const newEntryForm = cTag('form', {'action': "#", name: "frmTimeClock", id: "frmTimeClock", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                const errorMsg = cTag('div', {id: "error_TimeClock",class: "errormsg"});
            newEntryForm.appendChild(errorMsg);
                const employeeNoRow = cTag('div', {class: "flex", 'style': "text-align: left;"});
                    const employeeNoColumn = cTag('div', {class: "columnSM4 columnXS8"});
                        employeeNoLabel = cTag('label');
                        employeeNoLabel.innerHTML = Translate('Employee Number');
                    employeeNoColumn.appendChild(employeeNoLabel);
                employeeNoRow.appendChild(employeeNoColumn);
                    const employeeNoValue = cTag('div', {class: "columnSM8 columnXS4"});
                        employeeNoLabel = cTag('label');
                        employeeNoLabel.innerHTML = data.userEmpNo;
                    employeeNoValue.appendChild(employeeNoLabel);
                employeeNoRow.appendChild(employeeNoValue);
            newEntryForm.appendChild(employeeNoRow);
                const clockInDateRow = cTag('div', {class: "flex", 'style': "text-align: left; align-items: center;"});
                    const clockInDateColumn = cTag('div', {class: "columnSM4"});
                        const clockInDateLabel = cTag('label', {'for': "clock_in_date"});
                        clockInDateLabel.innerHTML = Translate('Clock In Date');
                            requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                        clockInDateLabel.appendChild(requiredField);
                    clockInDateColumn.appendChild(clockInDateLabel);
                clockInDateRow.appendChild(clockInDateColumn);
                    const clockInDateValue = cTag('div', {class: "columnSM8"});
                        inputField = cTag('input', {'readonly': "readonly", 'required': "required", name: "clock_in_date", id: "clock_in_date", class: "form-control", 'value': DBDateToViewDate(data.clock_in_date, 1)[0], 'type': "text", 'size': 10, 'maxlength': 10});
                    clockInDateValue.appendChild(inputField);
                clockInDateRow.appendChild(clockInDateValue);
            newEntryForm.appendChild(clockInDateRow);
                const clockInTimeRow = cTag('div', {class: "flex", 'style': "text-align: left; align-items: center;"});
                    const clockInTimeColumn = cTag('div', {class: "columnSM4 columnXS12"});
                        const clockInTimeLabel = cTag('label', {'for': "clock_in_time"});
                        clockInTimeLabel.innerHTML = Translate('Clock In Time');
                            requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                        clockInTimeLabel.appendChild(requiredField);
                    clockInTimeColumn.appendChild(clockInTimeLabel);
                clockInTimeRow.appendChild(clockInTimeColumn);
                    
                    const hourColumn = cTag('div', {class: "columnXS4 columnSM3", 'style': "padding-right: 0;"});
                        const hourInGroup = cTag('div', {class: "input-group"});
                            inputField = cTag('input', {'required': "required", name: "clock_in_hour", id: "clock_in_hour", class: "form-control", 'value': data.clock_in_hour, 'type': "number", 'size': 2, 'maxlength': 2, 'min': 0, 'max': (timeformat == '12' ? 12:23), 'change': keyUpHour, 'placeholder': Translate('Hour')});
                        hourInGroup.appendChild(inputField);
                            span = cTag('span', {class: "input-group-addon", 'style': "padding-left: 2px; padding-right: 2px;"});
                            span.innerHTML = ':';
                        hourInGroup.appendChild(span);
                    hourColumn.appendChild(hourInGroup);
                clockInTimeRow.appendChild(hourColumn);
                    const minuteColumn = cTag('div', {class: "columnXS4 columnSM3", 'style': "padding: 0;"});
                        inputField = cTag('input', {'required': "required", name: "clock_in_minute", id: "clock_in_minute", class: "form-control", 'value': data.clock_in_minute, 'type': "number", 'size': 2, 'maxlength': 2, 'min': 0, 'max': 59, 'placeholder': Translate('Minute')});
                    minuteColumn.appendChild(inputField);
                clockInTimeRow.appendChild(minuteColumn);
                    const amPmColumn = cTag('div', {class: "columnXS4 columnSM2 twelveFormat", 'style': "padding-left: 0;"});
                        const selectAmPm = cTag('select', {name: "clock_in_ampm", id: "clock_in_ampm", class: "form-control"});
                            const amOption = cTag('option', {'value': "AM"});
                            amOption.innerHTML = 'AM';
                        selectAmPm.appendChild(amOption);
                            const pmOption = cTag('option', {'value': "PM"});
                            pmOption.innerHTML = 'PM';
                        selectAmPm.appendChild(pmOption);
                    amPmColumn.appendChild(selectAmPm);
                clockInTimeRow.appendChild(amPmColumn);
            newEntryForm.appendChild(clockInTimeRow);
                const clockOutDateRow = cTag('div', {class: "flex", 'style': "text-align: left; align-items: center;"});
                    const clockOutDateColumn = cTag('div', {class: "columnSM4"});
                        const clockOutDateLabel = cTag('label', {'for': "clock_out_date"});
                        clockOutDateLabel.innerHTML = Translate('Clock Out Date');
                            requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                        clockOutDateLabel.appendChild(requiredField);
                    clockOutDateColumn.appendChild(clockOutDateLabel);
                clockOutDateRow.appendChild(clockOutDateColumn);
                    const clockOutDateValue = cTag('div', {class: "columnSM8"});
                        inputField = cTag('input', {'readonly': "readonly", 'required': "required", name: "clock_out_date", id: "clock_out_date", class: "form-control", 'value': DBDateToViewDate(data.clock_out_date, 1)[0], 'type': "text", 'size': 10, 'maxlength': 10});
                    clockOutDateValue.appendChild(inputField);
                clockOutDateRow.appendChild(clockOutDateValue);
            newEntryForm.appendChild(clockOutDateRow);
                const clockOutTimeRow = cTag('div', {class: "flex", 'style': "text-align: left; align-items: center;"});
                    const clockOutTimeColumn = cTag('div', {class: "columnXS12 columnSM4"});
                        const clockOutTimeLabel = cTag('label', {'for': "clock_out_time"});
                        clockOutTimeLabel.innerHTML = Translate('Clock Out Time');
                            requiredField = cTag('span', {class: "required"});
                            requiredField.innerHTML = '*';
                        clockOutTimeLabel.appendChild(requiredField);
                    clockOutTimeColumn.appendChild(clockOutTimeLabel);
                clockOutTimeRow.appendChild(clockOutTimeColumn);
                    const clockOutHour = cTag('div', {class: "columnXS4 columnSM3", 'style': "padding-right: 0;"});
                        const divInput = cTag('div', {class: "input-group"});
                            inputField = cTag('input', {'required': "required", name: "clock_out_hour", id: "clock_out_hour", class: "form-control", 'value': data.clock_out_hour, 'type': "number", 'size': 2, 'maxlength': 2, 'min': 0, 'max': (timeformat == '12' ? 12:23), 'change': keyUpHour, 'placeholder': Translate('Hour')});
                        divInput.appendChild(inputField);
                            span = cTag('span', {class: "input-group-addon", 'style': "padding-left: 2px; padding-right: 2px;"});
                            span.innerHTML = ':';
                        divInput.appendChild(span);
                    clockOutHour.appendChild(divInput);
                clockOutTimeRow.appendChild(clockOutHour);
                    const clockOutMinute = cTag('div', {class: "columnXS4 columnSM3", 'style': "padding: 0;"});
                        inputField = cTag('input', {'required': "required", name: "clock_out_minute", id: "clock_out_minute", class: "form-control", 'value': data.clock_out_minute, 'type': "number", 'size': 2, 'maxlength': 2, 'min': 0, 'max': 59, 'placeholder': Translate('Minute')});
                    clockOutMinute.appendChild(inputField);
                clockOutTimeRow.appendChild(clockOutMinute);
                    const clockOutAmPm = cTag('div', {class: "columnXS4 columnSM2 twelveFormat", 'style': "padding-left: 0;"});
                        const selectClockOutAmPm = cTag('select', {name: "clock_out_ampm", id: "clock_out_ampm", class: "form-control"});
                            const clockOutAmOption = cTag('option', {'value': "AM"});
                            clockOutAmOption.innerHTML = 'AM';
                        selectClockOutAmPm.appendChild(clockOutAmOption);
                            const clockOutPmOption = cTag('option', {'value': "PM"});
                            clockOutPmOption.innerHTML = 'PM';
                        selectClockOutAmPm.appendChild(clockOutPmOption);
                    clockOutAmPm.appendChild(selectClockOutAmPm);
                clockOutTimeRow.appendChild(clockOutAmPm);
            newEntryForm.appendChild(clockOutTimeRow);
                inputField = cTag('input', {'type': "hidden", name: "time_clock_id", id: "time_clock_id", 'value': data.time_clock_id});
            newEntryForm.appendChild(inputField);
                inputField = cTag('input', {'type': "hidden", name: "tuser_id", id: "tuser_id", 'value': data.user_id});
            newEntryForm.appendChild(inputField);
        formDialog.appendChild(newEntryForm);
        function keyUpHour() {
            this.value = this.value<10 ? '0'+this.value: this.value;
        }
        
        popup_dialog1000(Translate('Change Time Clock'),formDialog,AJupdate_Time_Clock);
        
        setTimeout(function() {
            document.getElementById("clock_in_hour").focus();
            if(data.clock_in_ampm !==''){
                document.getElementById("clock_in_ampm").value = data.clock_in_ampm;
            }
            if(data.clock_out_ampm !==''){
                document.getElementById("clock_out_ampm").value = data.clock_out_ampm;
            }
            date_picker('#clock_in_date');
            date_picker('#clock_out_date');
            
            let twelveFormatObjs = document.querySelectorAll('.twelveFormat');
            if(twelveFormatObjs.length>0){
                twelveFormatObjs.forEach(oneFieldObj=>{
                    if(timeformat === '24 hour') {
                        oneFieldObj.style.display = 'none';
                    }
                    else{
                        oneFieldObj.style.display = '';
                    }
                });
            }
            
        }, 500);
    }
}
async function AJupdate_Time_Clock(hidePopup){
	let date1,date2;
    const clock_in_date = document.getElementById("clock_in_date");
    const error_TimeClock = document.getElementById("error_TimeClock");
    error_TimeClock.innerHTML = '';
	if(clock_in_date.value===''){
        error_TimeClock.innerHTML = Translate('Missing Clock In Date');
        if(error_TimeClock.style.display === 'none'){
            error_TimeClock.style.display = '';
        }
		clock_in_date.focus();
		return false;
	}
	const clock_in_hour = document.getElementById("clock_in_hour");
	let clock_in_hourval = parseInt(clock_in_hour.value);	
	const clock_in_ampm = document.getElementById("clock_in_ampm").value;
	
	if(clock_in_hour.value===''){
        error_TimeClock.innerHTML = Translate('Missing Clock In Hour');
        if(error_TimeClock.style.display === 'none'){
            error_TimeClock.style.display = '';
        }
		clock_in_hour.focus();
		return false;
	}
	else if(isNaN(clock_in_hourval)){
		clock_in_hour.value='';
        error_TimeClock.innerHTML = Translate('Invalid Clock In Hour');
        if(error_TimeClock.style.display === 'none'){
            error_TimeClock.style.display = '';
        }
		clock_in_hour.focus();
		return false;
	}	
	else if(clock_in_hourval>12 && timeformat == '12 hour'){
		clock_in_hour.value=12;
        error_TimeClock.innerHTML = Translate('Max Clock In Hour is 12');
        if(error_TimeClock.style.display === 'none'){
            error_TimeClock.style.display = '';
        }
		clock_in_hour.focus();
		return false;
	}
	if(clock_in_ampm==='PM' && clock_in_hourval<12){clock_in_hourval += 12;}
	const clock_in_minute = document.getElementById("clock_in_minute");
	const clock_in_minuteval = parseInt(clock_in_minute.value);	
	if(clock_in_minute.value===''){
        error_TimeClock.innerHTML = Translate('Missing Clock In Minute');
        if(error_TimeClock.style.display === 'none'){
            error_TimeClock.style.display = '';
        }
		clock_in_minute.focus();
		return false;
	}
	else if(isNaN(clock_in_minuteval)){
		clock_in_minute.value='';
        error_TimeClock.innerHTML = Translate('Invalid Clock In Minute');
        if(error_TimeClock.style.display === 'none'){
            error_TimeClock.style.display = '';
        }
		clock_in_minute.focus();
		return false;
	}	
	else if(clock_in_minuteval>59){
		clock_in_minute.value=59;
        error_TimeClock.innerHTML = Translate('Max Clock In Minute is 59');
        if(error_TimeClock.style.display === 'none'){
            error_TimeClock.style.display = '';
        }
		clock_in_minute.focus();
		return false;
	}
	const clock_out_date = document.getElementById("clock_out_date");
	if(clock_out_date.value !==''){
		if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
			const clock_in_dateArray = clock_in_date.value.split('-');
			date1 = new Date(clock_in_dateArray[2]+'-'+clock_in_dateArray[1]+'-'+clock_in_dateArray[0]);
			const clock_out_dateArray = clock_out_date.value.split('-');
			date2 = new Date(clock_out_dateArray[2]+'-'+clock_out_dateArray[1]+'-'+clock_out_dateArray[0]);
		}
		else{
			date1 = new Date(clock_in_date.value);
			date2 = new Date(clock_out_date.value);
		}
		const timeDiff = Math.round(date2.getTime() - date1.getTime());
		const diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
		const clock_out_ampm = document.getElementById("clock_out_ampm").value;
		
		if(diffDays<0){
            error_TimeClock.innerHTML = Translate('Clock Out Date should be >= Clock In Date.');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_date.focus();
			return false;
		}
		else if(diffDays===0 && clock_in_ampm==='PM' && clock_out_ampm==='AM'){
            error_TimeClock.innerHTML = Translate('Clock Out Time should be PM.');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			document.getElementById("clock_out_ampm").focus();
			return false;
		}
		const clock_out_hour = document.getElementById("clock_out_hour");
		let clock_out_hourval = parseInt(clock_out_hour.value);	
        if(clock_out_hour.value===''){
            error_TimeClock.innerHTML = Translate('Missing Clock Out Hour');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_hour.focus();
			return false;
		}
		else if(isNaN(clock_out_hourval)){
			clock_out_hour.value='';
            error_TimeClock.innerHTML = Translate('Invalid Clock Out Hour');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_hour.focus();
			return false;
		}	
		else if(clock_out_hourval>12 && timeformat == '12 hour'){
			clock_out_hour.value=12;
            error_TimeClock.innerHTML = Translate('Max Clock Out Hour is 12');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_hour.focus();
			return false;
		}
		if(clock_out_ampm==='PM' && clock_out_hourval<12){clock_out_hourval += 12;}
		if(diffDays===0 && clock_in_hourval>clock_out_hourval){
            error_TimeClock.innerHTML = Translate('Clock Out Hour should be >= In Hour.');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_hour.focus();
			return false;
		}
		const clock_out_minute = document.getElementById("clock_out_minute");
		const clock_out_minuteval = parseInt(clock_out_minute.value);	
		if(clock_out_minute.value===''){
            error_TimeClock.innerHTML = Translate('Missing Clock Out Minute');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_minute.focus();
			return false;
		}
		else if(isNaN(clock_out_minuteval)){
			clock_out_minute.value='';
            error_TimeClock.innerHTML = Translate('Invalid Clock Out Minute');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_minute.focus();
			return false;
		}	
		else if(clock_out_minuteval>59){
			clock_out_minute.value=59;
            error_TimeClock.innerHTML = Translate('Max Clock Out Minute is 59');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_minute.focus();
			return false;
		}
		else if(diffDays===0 && clock_in_hourval===clock_out_hourval && clock_in_minuteval>clock_out_minuteval){
            error_TimeClock.innerHTML = Translate('Clock Out Hour should be >= In Hour.');
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
			clock_out_hour.focus();
			return false;
		}
	}
	
    const jsonData = serialize('#frmTimeClock');
    const url = '/'+segment1+'/AJupdate_Time_Clock';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        if(data.returnStr ==='Update' || data.returnStr === "Add"){
            hidePopup();
            AJget_notesData();
            filter_Time_Clock_view();
        }
        else{
            if(data.returnStr==='timeClockMissing') error_TimeClock.innerHTML = Translate('Time Clock Id is missing.');
            else error_TimeClock.innerHTML = data.returnStr;
            if(error_TimeClock.style.display === 'none'){
                error_TimeClock.style.display = '';
            }
            setTimeout(function() {
                if(error_TimeClock.style.display !== 'none'){
                    error_TimeClock.style.display = 'none';
                }
            }, 5000);
        }
    }
}
function Time_Clock_reportPrint(){
    const divContents = document.querySelector("#timeReportresult").cloneNode(true);
    const showing_type = document.getElementById("showing_type");
	let filterby = Translate('View')+': '+showing_type.options[showing_type.selectedIndex].innerText;
    const ColSpan = 8;
    let title;
	title = Translate('Time Report');
	
	const date_range = document.getElementById("date_range").value;
	if(date_range !==''){
		if(filterby !==''){filterby +=', ';}
		filterby += Translate('Date Range')+': '+date_range;
	}
    	
    let todayDate, document_focus;
	const now = new Date();
	if(calenderDate.toLowerCase()==='dd-mm-yyyy'){todayDate = now.getDate()+'-'+(now.getMonth() + 1)+'-'+now.getFullYear();}
	else{todayDate = (now.getMonth() + 1)+'/'+now.getDate()+'/'+now.getFullYear();}
    
     const additionaltoprows = cTag('div');
        const divWidth100 = cTag('div',{ 'class':`flexSpaBetRow` });
            let divWidth30 = cTag('div',{ 'style': "font-size: 18px; " });
            divWidth30.innerHTML = Translate(companyName);
        divWidth100.appendChild(divWidth30);
            let divWidth40 = cTag('div',{ 'style': "font-size: 20px; font-weight: bold;" });
            divWidth40.innerHTML = title;
        divWidth100.appendChild(divWidth40);
            let todayDiv = cTag('div',{ 'style': "font-size: 16px;" });
            todayDiv.innerHTML = todayDate;
        divWidth100.appendChild(todayDiv);
    additionaltoprows.appendChild(divWidth100);
    additionaltoprows.appendChild(cTag('div',{ 'style': "border-top: 1px solid #CCC; margin-top: 10px;" }));
        let divWidthBy = cTag('div');
        divWidthBy.innerHTML = filterby;
    additionaltoprows.appendChild(divWidthBy);
    const tr = cTag('tr');
        const td = cTag('td',{ 'class':`bgnone`,'colspan':ColSpan });
        td.appendChild(additionaltoprows);
    tr.appendChild(td);
    divContents.querySelector('tbody').prepend(tr);
	
	const day = new Date();
	const id = day.getTime();
	const w = 900;
	const h = 600;
	const scrl = 1;
	const winl = (screen.width - w) / 2;
	const wint = (screen.height - h) / 2;
	const winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	const printWindow = window.open('', '" + id + "', winprops);
        const html = cTag('html');
            const head = cTag('head');
                title = cTag('title');
                title.innerHTML = Translate('Time Report');
            head.appendChild(title);
            head.appendChild(cTag('meta',{ 'charset':`utf-8` }));
                const style = cTag('style');
                style.append(
                    `@page {size: auto;}
                    body{ font-family:Arial, sans-serif, Helvetica; min-width:98%; margin:0; padding:1%;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                    .flexSpaBetRow {display: flex;flex-flow: row wrap;justify-content: space-between; }
                    table{border-collapse:collapse; width: 100%;}
                    .table-bordered th {background:#F5F5F6; }
                    .table-bordered td, .table-bordered th { border:1px solid #DDDDDD; padding:8px 10px; }
                    .table-bordered td.bgnone {background-color:#FFF;border:0px solid #fff;}`
                );
            head.appendChild(style);
        html.appendChild(head);
            const body = cTag('body');
            body.append(divContents);
        html.appendChild(body);
    if (printWindow.document.write("<!DOCTYPE html>"),
    printWindow.document.appendChild(html),
	printWindow.document.close(),
	Boolean(window.chrome)){
		document_focus = false;
		printWindow.onload = function () {
			printWindow.window.print();
			document_focus = true;
		}
	}
	else {
		document_focus = false;
		printWindow.document.onreadystatechange = function () {
			const state = printWindow.document.readyState;
			if (state === 'interactive') {}
			else if (state === 'complete') {
				setTimeout(function(){
					printWindow.document.getElementById('interactive');
					printWindow.window.print();
					document_focus = true;
				},1000);
			}
		}
	}
	printWindow.setInterval(function() {
		const deviceOpSy = getDeviceOperatingSystem();
		if (document_focus === true && deviceOpSy==='unknown') { printWindow.window.close(); }
	}, 500);
}
// Time Clock report
function report(){
    let divInGroup, list_filters, inputField;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow"});
            const titleName = cTag('div', {class: "columnXS5 columnSM6"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                headerTitle.innerHTML = Translate('Time Report');
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class: "columnXS7 columnSM6", 'style': "text-align: end;"});
                const printButton = cTag('a', {class: "btn printButton", 'style': " margin-right: 10px;", 'href': "javascript:void(0);", title: Translate('Time Report')});
                printButton.addEventListener('click', Time_Clock_reportPrint);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.appendChild(printButton);
                
                const aTag = cTag('a', {'href': "/Time_Clock/lists", class: "btn defaultButton", title: Translate('Time Clock List')});
				aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Time Clock List'));
            buttonsName.appendChild(aTag);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);
        
        const filterRow = cTag('div', {class: "flexEndRow"});
            const viewColumn = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                divInGroup = cTag('div', {class: "input-group", id: "date_rangeid"});
                    const viewLabel = cTag('label', {'for': "showing_type", class: "input-group-addon cursor"});
                    viewLabel.innerHTML = Translate('View');
                divInGroup.appendChild(viewLabel);
                    const selectView = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectView.addEventListener('change', loadData_Time_Clock_report);
                    setOptions(selectView, {'Summary':Translate('Summary'), 'Detailed':Translate('Detailed Summary')}, 1, 0);
                divInGroup.appendChild(selectView);
            viewColumn.appendChild(divInGroup);
        filterRow.appendChild(viewColumn);
            const dateRangeSearch = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                divInGroup = cTag('div', {class: "input-group daterangeContainer"});
                    inputField = cTag('input', {'type': "hidden", name: "pageURI", id: "pageURI", 'value': "Time_Clock/report"});
                divInGroup.appendChild(inputField);
                    inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control", 'style': "padding-left: 35px;", name: "date_range", id: "date_range", 'value': ""});
                    daterange_picker_dialog(inputField);
                divInGroup.appendChild(inputField);
                    const searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Date wise Search')});
                    searchSpan.addEventListener('click', loadData_Time_Clock_report);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                divInGroup.appendChild(searchSpan);
            dateRangeSearch.appendChild(divInGroup);
        filterRow.appendChild(dateRangeSearch);
    showTableData.appendChild(filterRow);
        const searchResultRow = cTag('div', {class: "columnSM12"});
        searchResultRow.appendChild(cTag('div', {id: "Searchresult"}));
    showTableData.appendChild(searchResultRow);
    //=======sessionStorage =========//
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    const showing_type = 'Summary';
    checkAndSetSessionData('showing_type', showing_type, list_filters);
    let date_range = '';
    if(list_filters.hasOwnProperty("date_range")){
        date_range = list_filters.date_range;
    }
    document.getElementById("date_range").value = date_range;
    setTimeout(() => {
        loadData_Time_Clock_report();
    }, 0);
}
function loadData_Time_Clock_report(){
	const showing_type = document.getElementById('showing_type').value;
	const date_range = document.getElementById('date_range').value;
	sendReturnData('/Time_Clock/fetching_reportdata/', {"date_range":date_range, "showing_type":showing_type}, 'Searchresult');
}
async function sendReturnData(uri, sendingData, returnID){
    const jsonData = sendingData;
	
    const url = uri;
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        storeSessionData(jsonData);
        const container = document.querySelector('#'+returnID);
        container.innerHTML='';
        let strong, reportHeadRow, tdCol;
        const widget = cTag('div',{ 'class':`cardContainer` });
            const widgetHeader = cTag('div',{ 'class':`cardHeader` });
                const widgetHeaderRow = cTag('div',{ 'class':`flexSpaBetRow` });
                    const titleName = cTag('div',{ 'class':`columnSM5`, 'style': "margin: 0;" });
                        const titleHeader = cTag('h3');
                        titleHeader.innerHTML = Translate('Time Report');
                    titleName.appendChild(titleHeader);
                widgetHeaderRow.appendChild(titleName);
                    let widgetHeaderColumn = cTag('div',{ 'class':`columnSM7`, 'style': "text-align: right; margin: 0;" });
                    widgetHeaderColumn.innerHTML = Translate('Printed on')+' '+DBDateToViewDate(data.todayDate, 0, 1)+' '+Translate('for Date range of')+' '+DBDateRangeToViewDate(data.printedonstr, 1).replace(' - ', ` ${Translate('To')} `)+' '+'  ';
                widgetHeaderRow.appendChild(widgetHeaderColumn);
            widgetHeader.appendChild(widgetHeaderRow);
        widget.appendChild(widgetHeader);
            const widgetContent = cTag('div',{ 'class':`cardContent`, 'style': "padding: 0;" });
                const timeReportRow = cTag('div',{ 'class':`flexSpaBetRow` });
                    const timeReportColumn = cTag('div',{ 'class':`columnSM12`,'style':`position:relative; margin: 0px;` });
                        const noMoreTables = cTag('div', {id: "timeReportresult"});
                            const timeReportTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                                const timeReportBody = cTag('tbody');
                                if(data.tabledata && data.tabledata.length){
                                    let Total_minutes = 0;
                                    let Total_hours = 0;
                                    if(sendingData.showing_type === 'Summary'){
                                        data.tabledata.forEach(item=>{
                                            Total_minutes += item.minutes;
                                            Total_hours += item.hours;
                                                reportHeadRow = cTag('tr');
                                                    tdCol = cTag('td',{ 'colspan':data.colspan5, 'style': "padding-left: 20px; text-align: left;" });
                                                    tdCol.innerHTML = item.employeeName;
                                                reportHeadRow.appendChild(tdCol);
                                                    tdCol = cTag('td',{ 'style': "text-align: right; padding-right: 20px;" });
                                                    tdCol.innerHTML = `${item.hours} hrs ${item.minutes} min`;
                                                reportHeadRow.appendChild(tdCol);
                                            timeReportBody.appendChild(reportHeadRow);
                                        })
                                    }
                                    else{
                                        data.tabledata.forEach(item=>{
                                            Total_minutes += item.minutes;
                                            Total_hours += item.hours;
                                                reportHeadRow = cTag('tr');
                                                    let boldStyle = '';
                                                    if(data.boldclass != ''){
                                                        boldStyle = 'font-weight: bold;'
                                                    }
                                                    tdCol = cTag('td',{ 'colspan':data.colspan5, 'style': "padding-left: 20px; text-align: left;"+ boldStyle});
                                                    tdCol.innerHTML = item.employeeName;
                                                reportHeadRow.appendChild(tdCol);
                                                    tdCol = cTag('td',{ 'style': "text-align: right; padding-right: 20px;"+ boldStyle});
                                                    tdCol.innerHTML = `${item.hours} hrs ${item.minutes} min`;
                                                reportHeadRow.appendChild(tdCol);
                                            timeReportBody.appendChild(reportHeadRow);
                                            if(sendingData.showing_type==='Detailed'){
                                                item.details.forEach(detailInfo=>{
                                                        reportHeadRow = cTag('tr');
                                                            tdCol = cTag('td',{ 'style': "width: 80px; padding-left: 20px;" });
                                                            tdCol.innerHTML = ' ';
                                                        reportHeadRow.appendChild(tdCol);
                                                            tdCol = cTag('td',{ 'data-title':Translate('Day of the Week'),'align':`left` });
                                                            tdCol.innerHTML = detailInfo.weekDay;
                                                        reportHeadRow.appendChild(tdCol);
                                                            tdCol = cTag('td',{ 'data-title':Translate('Clock In Date'),'align':`left` });
                                                            tdCol.innerHTML = DBDateToViewDate(detailInfo.clocked_in, 1, 1)[0];
                                                        reportHeadRow.appendChild(tdCol);
                                                            tdCol = cTag('td',{ 'data-title':Translate('Clock In Time'),'align':`right` });
                                                            tdCol.innerHTML = DBDateToViewDate(detailInfo.clocked_in, 1, 1)[1];
                                                        reportHeadRow.appendChild(tdCol);
                                                            tdCol = cTag('td',{ 'data-title':Translate('Clock Out Date'),'align':`left` });
                                                            tdCol.innerHTML = DBDateToViewDate(detailInfo.clocked_out, 1, 1)[0];
                                                        reportHeadRow.appendChild(tdCol);
                                                            tdCol = cTag('td',{ 'data-title':Translate('Clock Out Time'),'align':`right` });
                                                            tdCol.innerHTML = DBDateToViewDate(detailInfo.clocked_out, 1, 1)[1];
                                                        reportHeadRow.appendChild(tdCol);
                                                            tdCol = cTag('td',{ 'data-title':Translate('Time'),'align':`right` });
                                                            tdCol.innerHTML = detailInfo.times;
                                                        reportHeadRow.appendChild(tdCol);
                                                            tdCol = cTag('td');
                                                            tdCol.innerHTML = ' ';
                                                        reportHeadRow.appendChild(tdCol);
                                                    timeReportBody.appendChild(reportHeadRow);
                                                })
                                            }
                                        })
                                    }   
                                    if(Total_minutes>59){
                                        Total_hours += parseInt(Total_minutes/60);
                                        Total_minutes = Total_minutes%60;
                                    } 
                                        reportHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'colspan':data.colspan5, 'style': "text-align: right; padding-left: 20px;", 'class': data.bgashclass});
                                                strong = cTag('strong');
                                                strong.innerHTML = Translate('Total Time');
                                            tdCol.appendChild(strong);
                                        reportHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'width':`15%`, 'style': "text-align: right; padding-right: 20px;", 'class': data.bgashclass });
                                                strong = cTag('strong');
                                                strong.innerHTML = `${Total_hours} hrs ${Total_minutes} min`;
                                            tdCol.appendChild(strong);
                                        reportHeadRow.appendChild(tdCol);
                                    timeReportBody.appendChild(reportHeadRow);
                                }
                                else{
                                        reportHeadRow = cTag('tr');
                                            tdCol = cTag('td');
                                                const pTag = cTag('p');
                                                pTag.innerHTML = '';
                                            tdCol.appendChild(pTag);
                                        reportHeadRow.appendChild(tdCol);
                                    timeReportBody.appendChild(reportHeadRow);
                                }
                            timeReportTable.appendChild(timeReportBody);
                        noMoreTables.appendChild(timeReportTable);
                    timeReportColumn.appendChild(noMoreTables);
                timeReportRow.appendChild(timeReportColumn);
            widgetContent.appendChild(timeReportRow);
        widget.appendChild(widgetContent);
        container.appendChild(widget);
    }
	return false;
}
document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, view, report};
    layoutFunctions[segment2]();
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});
