import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, checkintpositive, DBDateToViewDate, redirectTo, preventDot, 
    printbyurl, confirm_dialog, setSelectOpt, setTableRows, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, 
    checkAndSetSessionData, btnEnableDisable, popup_dialog600, sanitizer, applySanitizer, fetchData, listenToEnterKey, 
    addCustomeEventListener, serialize, onClickPagination, historyTable, activityFieldAttributes, multiSelectAction, noPermissionWarning
} from './common.js';

if(segment2==='') segment2 = 'lists';

const listsFieldAttributes = [
    {'valign':'middle', 'datatitle':Translate('Date'), 'align':'left'},
    {'valign':'middle', 'datatitle':Translate('Reference'), 'align':'left'},
    {'valign':'right', 'datatitle':Translate('Manufacturer'), 'align':'left'},
    {'valign':'middle','datatitle':Translate('Category'), 'align':'left'},
    {'valign':'right','datatitle':Translate('Date Completed'), 'align':'left'},
    {'valign':'middle', 'datatitle':Translate('Status'), 'align':'center'}
];
const uriStr = segment1+'/edit';

//=======common functions=======
function getSessionData(){
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }
    let lists = [
        {id:'ssorting_type',dfltValue:'0'},
        {id:'sview_type',dfltValue:'Open'},
        {id:'limit',dfltValue:'auto'},
        {id:'shistory_type',dfltValue:''},
        {id:'sview2_type',dfltValue:1},
    ]
    lists.forEach(item=>{
        if(list_filters.hasOwnProperty(item.id)){
            if(document.getElementById(item.id)){
                document.getElementById(item.id).value = list_filters[item.id];
            }
        }else{
            if(document.getElementById(item.id)){
                document.getElementById(item.id).value = item.dfltValue;
            }
        }
    })
    
    if(list_filters.hasOwnProperty("scategory_id")){
        if(document.getElementById("scategory_id")){            
            document.getElementById("scategory_id").appendChild(cTag('option',{'selected':'true','value':list_filters.scategory_id}));
        }
    }else{
        if(document.getElementById("scategory_id")){    
            document.getElementById("scategory_id").appendChild(cTag('option',{'selected':'true','value':0}));
        }
    }

    if(list_filters.hasOwnProperty("keyword_search")){
        let keyword_search = list_filters.keyword_search;
        if(document.getElementById("keyword_search")){
            document.getElementById("keyword_search").value = keyword_search;
        }
        if(document.getElementById("SKU_Barcode")){
            document.getElementById("SKU_Barcode").value = keyword_search;
        }
    }      
}

//=======lists=========
async function filter_Stock_Take_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById("ssorting_type").value;
	jsonData['sview_type'] = document.getElementById("sview_type").value;
	const scategory_id = document.getElementById("scategory_id").value;
	jsonData['scategory_id'] = scategory_id;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetPage/filter';
    fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        storeSessionData(jsonData);

        setSelectOpt('scategory_id', 0, Translate('All Categories'), data.catOpt, 1, Object.keys(data.catOpt).length);
        setTableRows(data.tableRows, listsFieldAttributes, uriStr, [], [1, 5]);
        document.getElementById("totalTableRows").value = data.totalRows;
        document.getElementById("scategory_id").value = scategory_id;
        
        onClickPagination();
    }
}

async function loadTableRows_Stock_Take_lists(){
	const jsonData = {};
	jsonData['ssorting_type'] = document.getElementById("ssorting_type").value;
	jsonData['sview_type'] = document.getElementById("sview_type").value;
	jsonData['scategory_id'] = document.getElementById("scategory_id").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        storeSessionData(jsonData);
        setTableRows(data.tableRows, listsFieldAttributes, uriStr, [], [1, 5]);
        onClickPagination();
    }
}

async function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

     //======Hidden Fields for Pagination=======//
     [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '30' },
        { name: 'totalTableRows', value: 0 },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        const input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

        let sortDropDown;
        const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Stock Take Information')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays a list of Open and Closed Stock Take')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);

            const buttonTitle = cTag('a', {'href': "/Stock_Take/add", title: Translate('Create Stock Take')});
                let stockButton = cTag('button', {class: "btn createButton"});
                    const strong = cTag('span');
                    strong.innerHTML = Translate('Stock Take');
                    if(OS =='unknown'){
                        strong.innerHTML = Translate('Create Stock Take');
                    }
                stockButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', strong);
            buttonTitle.appendChild(stockButton);
        titleRow.appendChild(buttonTitle);
    showTableData.appendChild(titleRow);

        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnMD3"});
                const selectSorting = cTag('select', {class: "form-control", name: "ssorting_type", id: "ssorting_type"});
                selectSorting.addEventListener('change', filter_Stock_Take_lists);
                setOptions(selectSorting, {'0':'Date DESC', '1':'Date ASC', '2':'Date Completed DESC', '3':'Date Completed ASC'}, 1, 0);
            sortDropDown.appendChild(selectSorting);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnMD3"});
                const selectView = cTag('select', {class: "form-control", name: "sview_type", id: "sview_type"});
                selectView.addEventListener('change', filter_Stock_Take_lists);
                setOptions(selectView, {'Open':Translate('Open'), 'Closed':Translate('Closed'), '':Translate('All Types')}, 1, 0);
            sortDropDown.appendChild(selectView);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnMD3"});
                const selectCategory = cTag('select', {class: "form-control", name: "scategory_id", id: "scategory_id"});
                selectCategory.addEventListener('change', filter_Stock_Take_lists);
                    const categoryOption = cTag('option', {'value': 0});
                    categoryOption.innerHTML = Translate('All Categories');
                selectCategory.appendChild(categoryOption);
            sortDropDown.appendChild(selectCategory);
        filterRow.appendChild(sortDropDown);

            const searchDiv = cTag('div', {class: "columnXS6 columnMD3"});
                const SearchInGroup = cTag('div', {class: "input-group"});
                    const searchField = cTag('input', {'keydown':listenToEnterKey(filter_Stock_Take_lists), 'type': "text", 'placeholder': Translate('Manufacturer / Reference'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
                SearchInGroup.appendChild(searchField);
                    let span = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Manufacturer / Reference')});
                    span.addEventListener('click', filter_Stock_Take_lists);
                        const searchIcon = cTag('i', {class: "fa fa-search"});
                    span.appendChild(searchIcon);
                SearchInGroup.appendChild(span);
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

                            const thCol1 = cTag('th', {'width': "10%"});
                            thCol1.innerHTML = columnNames[1];
                        
                            const thCol2 = cTag('th', {'width': "30%"});
                            thCol2.innerHTML = columnNames[2];
                        
                            const thCol3 = cTag('th');
                            thCol3.innerHTML = columnNames[3];
                        
                            const thCol4 = cTag('th', {'width': "10%"});
                            thCol4.innerHTML = columnNames[4];
                        
                            const thCol5 = cTag('th', {'width': "10%"});
                            thCol5.innerHTML = columnNames[5];
                        listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5);
                    listHead.appendChild(listHeadRow);
                listTable.appendChild(listHead);
                    const listBody = cTag('tbody', {id: "tableRows"});
                listTable.appendChild(listBody);
            divNoMore.appendChild(listTable);
        divTable.appendChild(divNoMore);
    showTableData.appendChild(divTable);
    addPaginationRowFlex(showTableData);
    
    addCustomeEventListener('filter',filter_Stock_Take_lists);
    addCustomeEventListener('loadTable',loadTableRows_Stock_Take_lists);
    getSessionData();
    filter_Stock_Take_lists(true);
}

//=======add=========//
function add(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        let dropdown, errorDiv;
        const titleRow = cTag('div');
            const headerTitle = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
            headerTitle.append(Translate('Create Stock Take')+' ');
            headerTitle.appendChild(cTag('i',{ 'class':'fa fa-info-circle', 'style': "font-size: 16px;", 'data-toggle':'tooltip','data-placement':'bottom','title':'','data-original-title':Translate('New Stock Take') }));
        titleRow.appendChild(headerTitle);
    Dashboard.appendChild(titleRow);

        const addSegment = cTag('div',{ 'class':'columnSM12', 'style': "margin: 0;" });
            const bsCallout = cTag('div',{ 'class':'innerContainer'});
                const stockTakeForm = cTag('form',{ 'action':'#','name':'frmstock_take','id':'frmstock_take','enctype':'multipart/form-data','method':'post','accept-charset':'utf-8' });
                stockTakeForm.addEventListener('submit',check_frmstock_take);
                    const referenceRow = cTag('div',{ 'class':'flex', 'style': "align-items: center;" });
                        const referenceName = cTag('div',{ 'class':'columnXS4 columnMD2' });
                            const referenceLabel = cTag('label',{ 'for':'reference' });
                            referenceLabel.innerHTML = Translate('Reference');
                        referenceName.appendChild(referenceLabel);
                    referenceRow.appendChild(referenceName);
                        const referenceField = cTag('div',{ 'class':'columnXS8 columnMD4' });
                        referenceField.appendChild(cTag('input',{ 'type':'text','maxlength':'20','name':'reference','id':'reference','value':'','class':'form-control' }));
                    referenceRow.appendChild(referenceField);
                stockTakeForm.appendChild(referenceRow);

                    const categoryRow = cTag('div',{ 'class':'flex', 'style': "align-items: center;" });
                        const categoryName = cTag('div',{ 'class':'columnXS4 columnMD2' });
                            const categoryLabel = cTag('label',{ 'for':'category_id','data-placement':'bottom' });
                            categoryLabel.innerHTML = Translate('Category');
                        categoryName.appendChild(categoryLabel);
                    categoryRow.appendChild(categoryName);
                        dropdown = cTag('div',{ 'class':'columnXS8 columnMD4' });
                            const selectCategory = cTag('select',{ 'name':'category_id','id':'category_id','class':'form-control' });
                                const categoryOption = cTag('option',{ 'value':0 });
                                categoryOption.innerHTML = Translate('All Categories');
                            selectCategory.appendChild(categoryOption);
                        dropdown.appendChild(selectCategory);
                    categoryRow.appendChild(dropdown);
                        errorDiv = cTag('div');
                        errorDiv.appendChild(cTag('span',{ 'class':'error_msg','id':'errmsg_category_id' }));
                    categoryRow.appendChild(errorDiv);
                stockTakeForm.appendChild(categoryRow);

                    const manufacturerRow = cTag('div',{ 'class':'flex', 'style': "align-items: center;" });
                        const manufacturerName = cTag('div',{ 'class':'columnXS4 columnMD2' });
                            const manufacturerLabel = cTag('label',{ 'for':'manufacturer_id','data-placement':'bottom' });
                            manufacturerLabel.innerHTML = Translate('Manufacturer');
                        manufacturerName.appendChild(manufacturerLabel);
                    manufacturerRow.appendChild(manufacturerName);
                        dropdown = cTag('div',{ 'class':'columnXS8 columnMD4' });
                            const selectManufacturer = cTag('select',{ 'name':'manufacturer_id','id':'manufacturer_id','class':'form-control' });
                                const manufacturerOption = cTag('option',{ 'value':0 });
                                manufacturerOption.innerHTML = Translate('All Manufacturers');
                            selectManufacturer.appendChild(manufacturerOption);
                        dropdown.appendChild(selectManufacturer);
                    manufacturerRow.appendChild(dropdown);
                        errorDiv = cTag('div');
                        errorDiv.appendChild(cTag('span',{ 'class':'error_msg','id':'errmsg_manufacturer_id' }));
                    manufacturerRow.appendChild(errorDiv);
                stockTakeForm.appendChild(manufacturerRow);
                    const buttonRow = cTag('div',{ 'class':'flexSpaBetRow' });
                        const buttonName = cTag('div',{ 'class':'columnXS10 columnSM12 columnMD6','align':'right' });
                        buttonName.appendChild(cTag('input',{ 'type':'hidden','name':'stock_take_id','id':'stock_take_id','value':0 }));
                        buttonName.appendChild(cTag('input',{ 'type':'button','class':'btn defaultButton','id':'cancelbutton','click':()=>redirectTo("/Stock_Take/lists"),'value':Translate('Cancel') }));
                        buttonName.appendChild(cTag('input',{ 'class':'btn completeButton', 'style': "margin-left: 10px;", 'name':'submit','id':'submit','type':'submit','value':Translate('Add') }));
                    buttonRow.appendChild(buttonName);
                stockTakeForm.appendChild(buttonRow);
            bsCallout.appendChild(stockTakeForm);
        addSegment.appendChild(bsCallout);
    Dashboard.appendChild(addSegment);
    applySanitizer(Dashboard);

    AJ_add_MoreInfo();
}

async function AJ_add_MoreInfo(){
    const url = '/'+segment1+'/AJ_add_MoreInfo';
    fetchData(afterFetch,url,{});

	function afterFetch(data){
        setOptions(document.getElementById('category_id'),data.catOpt,1,1);            
        setOptions(document.getElementById('manufacturer_id'),data.manOpt,1,1);
    }
}

async function check_frmstock_take(event){
    event.preventDefault();
    let submitBtn = document.querySelector("#submit");
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    
    const jsonData = serialize("#frmstock_take");
    const url = '/'+segment1+'/AJ_save_stock_take';
    fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
			window.location = '/Stock_Take/edit/'+data.id+'/'+data.savemsg;
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while adding PO information! Please try again.'));
            btnEnableDisable(submitBtn,Translate('Save'),false);
		}
    }	
	return false; 
}

//=======edit=========
function edit(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        let searchListField;
        const titleRow = cTag('div',{ 'class':'flexSpaBetRow' });
            const titleName = cTag('div',{ 'class':'columnXS8 columnSM5' });
                const headerTitle = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
                headerTitle.innerHTML = Translate('Stock Take Information');
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const statusName = cTag('div',{ 'class':'columnXS4 columnSM1' });
                const statusButton = cTag('a',{ 'class':'btn completeButton', 'href':'javascript:void(0);' });
                    const statusButtonLabel = cTag('span',{'id':'status'});
                statusButton.appendChild(statusButtonLabel);
            statusName.appendChild(statusButton);
        titleRow.appendChild(statusName);
            const buttonNames = cTag('div',{ 'class':'columnXS12 columnSM6', 'style': "text-align: end;" });
                const aTag = cTag('a', {'href': "/Stock_Take/lists", class: "btn defaultButton", 'style': "padding-top: 5px; padding-bottom: 5px;", title: Translate('Stock Take List')});
                aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Stock Take List'));
            buttonNames.appendChild(aTag);
                const printBtnDropDown = cTag('div',{ 'class':'printBtnDropDown', id: 'printdropdown' });
                    const printButton = cTag('button',{ 'type':'button', 'class':'btn printButton dropdown-toggle', 'style': "margin-left: 10px;", 'data-toggle':'dropdown','aria-hasstock_takepup':'true','aria-expanded':'false' });
                    printButton.appendChild(cTag('i',{ 'class':'fa fa-print' }));
                    if(OS ==='unknown') printButton.append(' '+Translate('Print')+' ');
                    printButton.append('\u2000', cTag('span',{ 'class':'caret'}));
                        let toggleSpan = cTag('span',{ 'class':'sr-only' });
                        toggleSpan.innerHTML = Translate('Toggle Dropdown');
                    printButton.appendChild(toggleSpan);
                printBtnDropDown.appendChild(printButton);
                    let ulMenu = cTag('ul',{ 'class':'dropdown-menu'});
                        let liFull = cTag('li');
                            const fullPagePrint = cTag('a',{ 'href':'javascript:void(0);','click':()=>stockTakePrint(segment3),'title':Translate('Full Page Printer') });
                            fullPagePrint.innerHTML = Translate('Full Page Printer');
                        liFull.appendChild(fullPagePrint);
                    ulMenu.appendChild(liFull);
                printBtnDropDown.appendChild(ulMenu);
            buttonNames.appendChild(printBtnDropDown);
        titleRow.appendChild(buttonNames);
    Dashboard.appendChild(titleRow);

        const stockTakeHeaderRow = cTag('div',{ 'class':'columnXS12', 'style': "margin: 0;" });
            const stockTakeWidget = cTag('div',{ 'class':'cardContainer', 'style': "margin-bottom: 10px;" });
                const stockTakeHeaderName = cTag('div',{ 'class':'cardHeader flexSpaBetRow' });
                    const stockTakeHeaderTitle = cTag('h3');
                    stockTakeHeaderTitle.append(cTag('i', {class: "fa fa-mobile"}), ' ', Translate('Stock Take Information'));
                stockTakeHeaderName.appendChild(stockTakeHeaderTitle);

                    const stockTakeButtonName = cTag('div',{ 'class':'invoiceorcompleted', 'style': "padding-right: 2px;" });
                        const stockTakeEditBtn = cTag('button', {class: "btn defaultButton", 'href':"javascript:void(0);"});
                        stockTakeEditBtn.innerHTML = Translate('Edit');
                        stockTakeEditBtn.addEventListener('click', function(){changeSTInfo();});
                    stockTakeButtonName.appendChild(stockTakeEditBtn);
                stockTakeHeaderName.appendChild(stockTakeButtonName);
            stockTakeWidget.appendChild(stockTakeHeaderName);
                let stockTakeUl, tdCol;
                const stockTakeContent = cTag('div',{ 'class':'cardContent flexSpaBetRow','id':'ST_Information' });
                    const referenceName = cTag('div',{ 'class':'columnSM6', 'style': "padding-left: 20px; border-right: 1px solid #CCC;" });
                        let stockTakeInfo = cTag('div',{'id':'order_info', class: "customInfoGrid" });
                            const referenceLabel = cTag('label');
                            referenceLabel.innerHTML = Translate('Reference')+': ';
                            let referenceSpan = cTag('span',{ 'id':'referencestr' });
                        stockTakeInfo.append(referenceLabel, referenceSpan);
                            const manufacturerLabel = cTag('label');
                            manufacturerLabel.innerHTML = Translate('Manufacturer')+': ';
                            let manufacturerSpan = cTag('span',{ 'id':'manufacturestr' });
                        stockTakeInfo.append(manufacturerLabel, manufacturerSpan);
                    referenceName.appendChild(stockTakeInfo);
                stockTakeContent.appendChild(referenceName);
                    const categoryName = cTag('div',{ 'class':'columnSM6', 'style': "padding-left: 20px;" });
                        stockTakeUl = cTag('div',{ 'class':'cardOrder customInfoGrid','id':'order_info' });
                            const categoryLabel = cTag('label');
                            categoryLabel.innerHTML = Translate('Category')+': ';
                            let categorySpan = cTag('span',{ 'id':'categorystr' });
                        stockTakeUl.append(categoryLabel, categorySpan);
                            const dateLabel = cTag('label');
                            dateLabel.innerHTML = Translate('Date Completed')+': ';
                            let completeSpan = cTag('span',{ 'id':'completedstr'} );
                        stockTakeUl.append(dateLabel, completeSpan);
                    categoryName.appendChild(stockTakeUl);
                stockTakeContent.appendChild(categoryName);
            stockTakeWidget.appendChild(stockTakeContent);
        stockTakeHeaderRow.appendChild(stockTakeWidget);
    Dashboard.appendChild(stockTakeHeaderRow);
    Dashboard.appendChild(cTag('input',{ 'type':'hidden','name':'pageURI','id':'pageURI','value':`${segment1}/${segment2}/${segment3}` }));
	Dashboard.appendChild(cTag('input',{ 'type':'hidden','name':'page','id':'page','value':1 }));
	Dashboard.appendChild(cTag('input',{ 'type':'hidden','name':'rowHeight','id':'rowHeight','value':'34' }));
	Dashboard.appendChild(cTag('input',{ 'type':'hidden','name':'totalTableRows','id':'totalTableRows','value':0 }));

        const countedRow = cTag('div',{ 'class':'columnXS12'});
            const countedContent = cTag('div',{ 'class':'cartContent'});
                const divTable = cTag('div',{ 'class':'flex' });
                    const divTableColumn = cTag('div',{ 'class':'columnXS12', 'style': "margin: 0; padding: 0;" });
                        const countedTable = cTag('table',{ 'class':'table table-bordered'});
                            const countedHead = cTag('thead');
                                const countedHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':'8', 'style': "text-align: right; background: none;" });
                                        const searchDiv = cTag('div',{'class':'flexStartRow'});
                                            const searchColumn = cTag('div',{'class':'columnXS12 columnSM3'});
                                                searchListField = cTag('div',{ 'class':'input-group' });
                                                searchListField.appendChild(cTag('input',{'keydown':listenToEnterKey(filter_Stock_Take_edit), 'type':'text','placeholder':Translate('Search from list'),'id':'SKU_Barcode','name':'SKU_Barcode','class':'form-control', 'style': "min-width: 120px;", 'maxlength':'50' }));
                                                    let plusIcon = cTag('span',{ 'class':'input-group-addon cursor','click':submitSKU_BarcodeST,'data-toggle':'tooltip','data-placement':'bottom','title':'','data-original-title':Translate('Search from list') });
                                                    plusIcon.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Counted'));
                                                searchListField.appendChild(plusIcon);
                                            searchColumn.appendChild(searchListField);
                                        searchDiv.appendChild(searchColumn);
                                        searchDiv.appendChild(cTag('div',{ 'id':'showSKU_BarcodeMSG', 'class': 'input-group columnXS12 columnSM3', 'style': "text-align: left;" }));
                                            const searchOptionDiv = cTag('div',{ 'class':'flexEndRow columnXS12 columnSM6','style':'gap:10px' });
                                                const selectView = cTag('select',{ 'class':'form-control', 'style': "width: auto ;", 'name':'sview2_type','id':'sview2_type','change':filter_Stock_Take_edit });
                                                setOptions(selectView,{'1':Translate('Counted SKUs Only'),'2':Translate('Count is Different'),'All':Translate('All Types')},1,0);                                      
                                            searchOptionDiv.appendChild(selectView);
                                                searchListField = cTag('div',{ 'class':'input-group' });
                                                searchListField.appendChild(cTag('input',{'keydown':listenToEnterKey(filter_Stock_Take_edit), 'type':'text','placeholder':Translate('Search from list'),'id':'keyword_search','name':'keyword_search','class':'form-control', 'style': "min-width: 120px;", 'maxlength':'50' }));
                                                    let searchSpan = cTag('span',{ 'class':'input-group-addon cursor','click':filter_Stock_Take_edit,'data-toggle':'tooltip','data-placement':'bottom','title':'','data-original-title':Translate('Search from list') });
                                                    searchSpan.appendChild(cTag('i',{ 'class':'fa fa-search' }));
                                                searchListField.appendChild(searchSpan);
                                            searchOptionDiv.appendChild(searchListField);
                                        searchDiv.appendChild(searchOptionDiv);
                                    tdCol.appendChild(searchDiv);
                                countedHeadRow.appendChild(tdCol);
                            countedHead.appendChild(countedHeadRow);

                                const listHeadRow = cTag('tr');
                                    const thCol0 = cTag('th',{ 'width':'3%', 'style': "text-align: right;" });
                                    thCol0.innerHTML = '#';

                                    const thCol1 = cTag('th',{ 'width':'10%' });
                                    thCol1.innerHTML = Translate('Manufacturer');

                                    const thCol2 = cTag('th',{ 'width':'10%' });
                                    thCol2.innerHTML = Translate('Category');

                                    const thCol3 = cTag('th');
                                    thCol3.innerHTML = Translate('Product Name');

                                    const thCol4 = cTag('th',{ 'width':'15%' });
                                    thCol4.innerHTML = Translate('SKU/Barcode');

                                    const thCol5 = cTag('th',{ 'width':'120', 'style': "text-align: right;" });
                                    thCol5.innerHTML = Translate('Current');

                                    const thCol6 = cTag('th',{ 'width':'170', 'style': "text-align: right;" });
                                    thCol6.innerHTML = Translate('Counted');

                                    const thCol7 = cTag('th',{ 'width':'20%' });
                                    thCol7.innerHTML = Translate('Note');
                                listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7);
                            countedHead.appendChild(listHeadRow);
                        countedTable.appendChild(countedHead);
                        countedTable.appendChild(cTag('tbody',{ 'id':'listsTable' }));                            
                    divTableColumn.appendChild(countedTable);
                divTable.appendChild(divTableColumn);
            countedContent.appendChild(divTable);
            addPaginationRowFlex(countedContent);
        countedRow.appendChild(countedContent);
    Dashboard.appendChild(countedRow);

        const activityRow = cTag('div',{ 'class':'flex' });
            const activityColumn = cTag('div',{ 'class':'columnXS12' });
            let hiddenProperties = {
                'note_forTable': 'stock_take' ,
                'sstock_take_id': segment3 ,
                'table_idValue': segment3 ,
                'publicsShow': 1 ,
            }
            activityColumn.appendChild(historyTable(Translate('Stock Take History'),hiddenProperties));
        activityRow.appendChild(activityColumn);
    Dashboard.appendChild(activityRow);

    //=======sessionStorage =========//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{ list_filters = {};}

    let sview2_type = 'All';
    checkAndSetSessionData('sview2_type', sview2_type, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    if(document.querySelector("#SKU_Barcode")){
        document.querySelector("#SKU_Barcode").addEventListener('keydown', e=>{
            if(e.which===13){submitSKU_BarcodeST();}
        });
    }

    addCustomeEventListener('filter',loadData_Stock_Take_edit);
    addCustomeEventListener('loadTable',loadTableRows_Stock_Take_edit);
    AJ_edit_MoreInfo();
    multiSelectAction('printdropdown');
}

async function AJ_edit_MoreInfo(){
    const jsonData = {stock_take_id:segment3};
    const url = '/'+segment1+'/AJ_edit_MoreInfo';
    fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        document.querySelector('#status').innerHTML = data.status;
        document.querySelector('#referencestr').innerHTML = data.reference;
        document.querySelector('#manufacturestr').innerHTML = data.manufacture||'All Manufacturers';
        document.querySelector('#categorystr').innerHTML = data.categoryname||'All Categories';
        document.querySelector('#completedstr').innerHTML = DBDateToViewDate(data.date_completed);
        if(data.status === "Open"){
            let buttonInput;
            const buttonBody = cTag('tbody');
                const buttonHeadRow = cTag('tr');
                    const tdCol = cTag('td',{ 'colspan':'9' });
                        const buttonNames = cTag('div',{ 'class': "flexEndRow", 'style': "align-items: center;" });
                            const floatRightDiv = cTag('div');
                                buttonInput = cTag('div',{ 'class':'input-group' });
                                    const buttonName = cTag('div',{ 'name':'stock_takessubmit','id':'stock_takessubmit','class':'bgnone cursor' });
                                        const buttonTitle = cTag('button',{ 'class':'btn completeButton','type':'button','click':completeST });
                                            const buttonLabel = cTag('span');
                                            buttonLabel.innerHTML = Translate('Mark Completed');
                                        buttonTitle.appendChild(buttonLabel);
                                    buttonName.appendChild(buttonTitle);
                                buttonInput.appendChild(buttonName);
                            floatRightDiv.appendChild(buttonInput);
                            const cancelButton = cTag('div',{ 'id':'stock_take_cancelled', 'style': " margin-right: 15px;" });
                                buttonInput = cTag('div',{ 'class':'input-group' });
                                    const cancelButtonName = cTag('a',{ 'href':'javascript:void(0);', 'class':`btnFocus iconButton cursor`, 'click':cancelST });
                                        let removeIcon = cTag('i',{ 'class':'fa fa-remove', 'style': "font-size: 1.5em;" });
                                        const cancelButtonLabel = cTag('span');
                                        cancelButtonLabel.innerHTML = ' '+Translate('Cancel');
                                    cancelButtonName.append(removeIcon, cancelButtonLabel);
                                buttonInput.appendChild(cancelButtonName);
                            cancelButton.appendChild(buttonInput);
                        buttonNames.append(cancelButton, floatRightDiv);
                    tdCol.appendChild(buttonNames);
                buttonHeadRow.appendChild(tdCol);
            buttonBody.appendChild(buttonHeadRow);
            document.querySelector('#listsTable').parentNode.appendChild(buttonBody);
        }
        filter_Stock_Take_edit(true);
        loadData_Stock_Take_edit();
    }
}

async function changeSTInfo(){
    const stock_take_id = document.querySelector("#sstock_take_id").value;
	if(stock_take_id>0){
        const jsonData = {"stock_take_id":stock_take_id};
        const url = '/'+segment1+'/showSTData';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            let changeStockTakeRow;
            const fragment = cTag('div');
            fragment.appendChild(cTag('div',{ 'id':'errorST','class':'errormsg' }));
                const form = cTag('form',{ 'action':'#','name':'frmchangeSTInfo','id':'frmchangeSTInfo','enctype':'multipart/form-data','method':'post','accept-charset':'utf-8' });
                form.addEventListener('submit',saveChangeSTInfo);
                    changeStockTakeRow = cTag('div',{ 'class':'flex groupField', 'style': "text-align: left; align-items: center;" });
                        const referenceName = cTag('div',{ 'class':'columnXS4'});
                            const referenceLabel = cTag('label',{ 'for':'reference' });
                            referenceLabel.innerHTML = Translate('Reference');
                        referenceName.appendChild(referenceLabel);
                    changeStockTakeRow.appendChild(referenceName);
                        const referenceField = cTag('div',{ 'class':'columnXS8' });
                        referenceField.appendChild(cTag('input',{ 'type':'text','class':'form-control','name':'reference','id':'reference','value':data.reference,'maxlength':'20' }));
                    changeStockTakeRow.appendChild(referenceField);
                form.appendChild(changeStockTakeRow);

                    changeStockTakeRow = cTag('div',{ 'class':'flex groupField', 'style': "text-align: left; align-items: center;"});
                        const categoryName = cTag('div',{ 'class':'columnXS4'});
                            const categoryLabel = cTag('label',{ 'for':'category_id' });
                            categoryLabel.innerHTML = Translate('Category Name');
                        categoryName.appendChild(categoryLabel);
                    changeStockTakeRow.appendChild(categoryName);
                        const categoryDropDown = cTag('div',{ 'class':'columnXS8' });
                            const selectCategory = cTag('select',{ 'disabled':'disabled','class':'form-control','name':'category_id','id':'category_id' });
                                const allCategory = cTag('option',{ 'value':'0' });
                                allCategory.innerText = Translate('All Category');
                            selectCategory.appendChild(allCategory);
                            setOptions(selectCategory,data.catOpt,1,1);
                        categoryDropDown.appendChild(selectCategory);
                    changeStockTakeRow.appendChild(categoryDropDown);
                form.appendChild(changeStockTakeRow);

                    changeStockTakeRow = cTag('div',{ 'class':'flex standardField LiveStocks LaborServices', 'style': "text-align: left; align-items: center;"  });
                        const manufacturerName = cTag('div',{ 'class':'columnXS4'});
                            const manufacturerLabel = cTag('label',{ 'for':'manufacturer_id' });
                            manufacturerLabel.innerHTML = Translate('Manufacturer Name');
                        manufacturerName.appendChild(manufacturerLabel);
                    changeStockTakeRow.appendChild(manufacturerName);
                        const manufactureDropDown = cTag('div',{ 'class':'columnXS8' });
                            const selectManufacturer = cTag('select',{ 'disabled':'disabled','class':'form-control','name':'manufacturer_id','id':'manufacturer_id' });
                                const allMenufecturer = cTag('option',{ 'value':'0' });
                                allMenufecturer.innerText = Translate('All Manufacturers');
                            selectManufacturer.appendChild(allMenufecturer);
                            setOptions(selectManufacturer,data.manOpt,1,1);
                        manufactureDropDown.appendChild(selectManufacturer);
                    changeStockTakeRow.appendChild(manufactureDropDown);
                form.appendChild(changeStockTakeRow);
                form.appendChild(cTag('input',{ 'type':'hidden','name':'stock_take_id','value':stock_take_id }));
            fragment.appendChild(form);

            popup_dialog600(Translate('Change Stock Take'), fragment, Translate('Save'), saveChangeSTInfo);

            setTimeout(function() {		
                document.getElementById("reference").focus();
                document.querySelector("#category_id").value = data.category_id;
                document.querySelector("#manufacturer_id").value = data.manufacturer_id;
                applySanitizer(form);
            }, 500);
        }
	}
	return true;
}

async function saveChangeSTInfo(hidePopup){
    const submitBtn = document.querySelector(".btnmodel");
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    
    const jsonData = serialize("#frmchangeSTInfo");
    const url = '/'+segment1+'/saveChangeST';
    fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        document.querySelector("#referencestr").innerHTML = data.returnData.reference;
        document.querySelector("#manufacturestr").innerHTML = data.returnData.manufacture;
        document.querySelector("#categorystr").innerHTML = data.returnData.categoryname;
        document.querySelector("#completedstr").innerHTML = DBDateToViewDate(data.returnData.date_completed);
        loadData_Stock_Take_edit();
        hidePopup();
    }
	return false;
}

async function loadData_Stock_Take_edit(){
	const jsonData = {};
	jsonData['sstock_take_id'] = document.querySelector('#table_idValue').value;
	jsonData['shistory_type'] = document.querySelector('#shistory_type').value;

    const url = '/'+segment1+'/fetching_editdata';
    fetchData(afterFetch,url,jsonData);
	function afterFetch(data){
        const shistory_type = document.getElementById("shistory_type");
        const shistory_typeVal = shistory_type.value;
        shistory_type.innerHTML = '';
        const option = document.createElement('option');
        option.setAttribute('value', '');
        option.innerHTML = Translate('All Activities');
        shistory_type.appendChild(option);
        setOptions(shistory_type, data.actFeeTitOpt, 0, 1);
        document.getElementById("shistory_type").value = shistory_typeVal;
        
        setTableHRows(data.tabledata, activityFieldAttributes);
    }
	return false;
}

async function filter_Stock_Take_edit(){
    let page = 1;
	document.querySelector("#page").value = page;
	const jsonData = {};
	jsonData['sstock_take_id'] = document.querySelector('#table_idValue').value;
	jsonData['sview2_type'] = document.querySelector('#sview2_type').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

    const url = '/'+segment1+'/AJgetHPage/filter';
    fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        storeSessionData(jsonData);
        document.querySelector("#totalTableRows").value = data.totalRows;
        set_StockTake_TableRows(data.tableRows);	
        onClickPagination();
    }
}

async function loadTableRows_Stock_Take_edit(){
	const jsonData = {};
	jsonData['sstock_take_id'] = document.querySelector('#table_idValue').value;
	jsonData['sview2_type'] = document.querySelector('#sview2_type').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;

    const url = '/'+segment1+'/AJgetHPage';
    fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        setSessionData();			
        set_StockTake_TableRows(data.tableRows);			
        onClickPagination();
    }
}

function changeSTCartInfo(stock_take_items_id, idPre, itemType){
    let invCntVal,html,span,textarea;
	const stCInfoObj = document.querySelector("#"+idPre+stock_take_items_id);
	if(idPre === 'invCnt'){
		const preVal = checkintpositive(stCInfoObj.innerText);
		invCntVal = parseFloat(preVal);
		if(isNaN(invCntVal) || invCntVal===''){
			invCntVal = parseFloat(document.querySelector("#invCur"+stock_take_items_id).innerText);
			if(isNaN(invCntVal) || invCntVal==='' || invCntVal<0){invCntVal = 0;}
		}	
        
        html = cTag('div',{ 'class':'input-group' });
            const inputField = cTag('input',{ 'min':0,'type':'number','class':'form-control','id':`inventoryVal${stock_take_items_id}`,'value':`${invCntVal}`,'style':'min-width:50px' });
            preventDot(inputField);
        html.appendChild(inputField);
        html.appendChild(cTag('input',{ 'type':'hidden','id':`itemType${stock_take_items_id}`,'value':`${itemType}` }));
        html.appendChild(cTag('input',{ 'type':'hidden','id':`inventoryValPre${stock_take_items_id}`,'value':`${preVal}` }));
            span = cTag('span',{ 'class':'input-group-addon' });
            span.appendChild(cTag('i',{ 'class':'fa-1 fa fa-check cursor', 'style': "color: #090;", 'click':()=>AJupdateSTIIC(stock_take_items_id,'invCnt','Save') }));
            span.append(' | ');
            span.appendChild(cTag('i',{ 'class':'fa-1 fa fa-remove cursor', 'style': "color: #F00;", 'click':()=>AJupdateSTIIC(stock_take_items_id,'invCnt','Cancel') }));
        html.appendChild(span);
	}
	else{
		const noteVal = stCInfoObj.innerText;
		
        html = cTag('div',{ 'class':'input-group' });
            textarea = cTag('textarea',{ 'cols':'20','rows':1,'class':'form-control','id':`noteVal${stock_take_items_id}`,'style':'display:block;min-width:100px' });
            textarea.innerHTML = noteVal;
            textarea.addEventListener('blur',sanitizer);
        html.appendChild(textarea);
            textarea = cTag('textarea',{ 'cols':'20','rows':1,style:'display:none','id':`noteValPre${stock_take_items_id}` });
            textarea.innerHTML = noteVal;
        html.appendChild(textarea);
        html.appendChild(cTag('input',{ 'type':'hidden','id':`itemType${stock_take_items_id}`,'value':itemType }));
            span = cTag('span',{ 'class':'input-group-addon' });
            span.appendChild(cTag('i',{ 'class':'fa-1 fa fa-check cursor', 'style': "color: #090;", 'click':()=>AJupdateSTIIC(stock_take_items_id,'note','Save') }));
            span.append(' | ');
            span.appendChild(cTag('i',{ 'class':'fa-1 fa fa-remove cursor', 'style': "color: #F00;", 'click':()=>AJupdateSTIIC(stock_take_items_id,'note','Cancel') }));
        html.appendChild(span);
	}
	
	stCInfoObj.innerHTML = '';
	stCInfoObj.appendChild(html);
	
	if(idPre==='invCnt'){
		stCInfoObj.querySelector("input").focus();
		
		document.querySelector('#inventoryVal'+stock_take_items_id).addEventListener('keydown',function (e) {
			if (e.which === 13) {
                e.preventDefault();
				AJupdateSTIIC(stock_take_items_id, idPre, 'save');
			}
		});
	}
	else{
		stCInfoObj.querySelector("textarea").focus();
	}
}

async function AJupdateSTIIC(stock_take_items_id, idPre, resetYN){
    let fieldVal;
	const itemType = document.querySelector("#itemType"+stock_take_items_id).value;
	if(idPre==='invCnt'){
		fieldVal = parseFloat(document.querySelector("#inventoryVal"+stock_take_items_id).value);
		if(isNaN(fieldVal) || fieldVal===''){fieldVal = 0;}
	}
	else{
		fieldVal = document.querySelector("#noteVal"+stock_take_items_id).value;
	}
	if(resetYN==='Cancel'){
		if(idPre==='invCnt'){
			fieldVal = document.querySelector("#inventoryValPre"+stock_take_items_id).value;
		}
		else{
			fieldVal = document.querySelector("#noteValPre"+stock_take_items_id).value;
		}
		replaceSTTdText(stock_take_items_id, fieldVal, idPre, itemType);
		return false;
	}
    
    const jsonData = {stock_take_items_id:stock_take_items_id, idPre:idPre, fieldVal:fieldVal};
    const url = '/'+segment1+'/AJupdateSTIIC';
    fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        if(data.returnStr==='error'){
            showTopMessage('error_msg',Translate('Error occurred while changing information! Please try again.'));
		}
		else{
			replaceSTTdText(stock_take_items_id, fieldVal, idPre, itemType);
		}
    }
	return false;	
}

function replaceSTTdText(id, fieldVal, idPre, itemType){
    let node;
	if(itemType==='Live Stocks' && idPre==='invCnt'){
        node = document.querySelector('#'+idPre+id);
        node.innerHTML = fieldVal+' \u2003';
        node.appendChild(cTag('i',{ 'class':'cursor fa fa-edit', 'click':()=>changeSTCartIMEI(id,idPre,itemType) }));
	}
	else{
        node = document.querySelector('#'+idPre+id);
        node.innerHTML = fieldVal+' \u2003';
        node.appendChild(cTag('i',{ 'class':'cursor fa fa-edit', 'style': "float: right;", 'click':()=>changeSTCartInfo(id,idPre,itemType) }));
	}
	
	if(idPre==='invCnt'){
		let currentVal = parseInt(checkintpositive(document.querySelector("#invCur"+id).innerHTML));
		if(currentVal==='' || isNaN(currentVal)){currentVal = 0;}
		if(currentVal !== fieldVal){			
			if(!document.querySelector("#"+idPre+id).classList.contains('alert-danger')){
				document.querySelector("#"+idPre+id).classList.add('alert-danger');
			}
		}
		else{
			if(document.querySelector("#"+idPre+id).classList.contains('alert-danger')){
				document.querySelector("#"+idPre+id).classList.remove('alert-danger');
			}
		}
	}
}

async function changeSTCartIMEI(stock_take_items_id, idPre){
	const stCInfoObj = document.querySelector("#"+idPre+stock_take_items_id);
	if(stCInfoObj){
        const jsonData = {"stock_take_items_id":stock_take_items_id};
        const url = '/'+segment1+'/showSTCartIMEI';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            let boldValue, cartHeadRow, cursorLabel, tdCol;
            const formhtml = cTag('div');
            formhtml.appendChild(cTag('div',{ 'id':'errorST','class':'errormsg' }));
                const form = cTag('form',{ 'action':'#','name':'frmSTCartIMEI','id':'frmSTCartIMEI','enctype':'multipart/form-data','method':'post','accept-charset':'utf-8' });
                form.addEventListener('submit',saveChangeSTIInfo);
                    const inventoryFrom = cTag('div',{ 'class':'groupField', 'style': "text-align: left;"});
                        const inventoryName = cTag('div',{ 'class':'columnXS12'});
                            const inventoryLabel = cTag('label');
                            inventoryLabel.append(Translate('Current Inventory')+': ');
                                boldValue = cTag('b',{ 'id':'curInvVal' });
                                boldValue.innerHTML = 0;
                            inventoryLabel.appendChild(boldValue);
                        inventoryName.appendChild(inventoryLabel);
                    inventoryFrom.appendChild(inventoryName);
                        const countedName = cTag('div',{ 'class':'columnXS12'});
                            const countedLabel = cTag('label');
                            countedLabel.append(Translate('Counted')+': ');
                                boldValue = cTag('b',{ 'id':'countedVal' });
                                boldValue.innerHTML = 0;
                            countedLabel.appendChild(boldValue);
                        countedName.appendChild(countedLabel);
                    inventoryFrom.appendChild(countedName);
                form.appendChild(inventoryFrom);
                    const cartTableDiv = cTag('div',{ 'style':'position: relative' });
                        const cartTable = cTag('table',{ 'class':'table-bordered table-striped table-condensed cf listing' });
                            const cartHead = cTag('thead',{ 'class':'cf' });
                                cartHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':'left' });
                                        const searchInput = cTag('div',{ 'class':'input-group' });
                                        searchInput.appendChild(cTag('input',{ 'type':'text','placeholder':Translate('Search IMEI'),'value':'','id':'STCartIMEI','name':'STCartIMEI','class':'form-control','maxlength':'20' }));
                                            let span = cTag('span',{ 'class':'input-group-addon cursor','click':()=>changeSTCartIMEI(),'data-toggle':'tooltip','data-placement':'bottom','title':Translate('Search IMEI') });
                                            span.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Counted'));
                                        searchInput.appendChild(span);
                                    tdCol.appendChild(searchInput);
                                cartHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':'left','width':'15%' });
                                        cursorLabel = cTag('label',{ 'class':'cursor' });
                                        cursorLabel.appendChild(cTag('input',{ 'type':'checkbox','class':'cursor','name':'checkAll','id':'checkAll','value':1 }));
                                            boldValue = cTag('b');
                                            boldValue.innerHTML = Translate('All');
                                        cursorLabel.append(' ', boldValue);
                                    tdCol.appendChild(cursorLabel);
                                cartHeadRow.appendChild(tdCol);
                            cartHead.appendChild(cartHeadRow);
                        cartTable.appendChild(cartHead);
                            const frmSTCartIMEITableRows = cTag('tbody',{id:'frmSTCartIMEITableRows'});
                            const currentImeis = data.currentImeis;
                            const countedImeis = data.countedImeis;
                            if(currentImeis.length>0){
                                currentImeis.forEach(function( value ) {
                                    if(value !==''){
                                        cartHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'align':'left' });
                                            tdCol.innerHTML = value;
                                        cartHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td');
                                                cursorLabel = cTag('label',{ 'class':'cursor' });
                                                    const input = cTag('input',{ 'type':'checkbox','class':'cursor oneId','name':'oneId[]','value':value });
                                                    if(countedImeis.includes(value)) input.checked = true;
                                                cursorLabel.appendChild(input);
                                            tdCol.appendChild(cursorLabel);
                                        cartHeadRow.appendChild(tdCol);
                                        frmSTCartIMEITableRows.appendChild(cartHeadRow);
                                    }
                                })
                            }
                        cartTable.appendChild(frmSTCartIMEITableRows);
                    cartTableDiv.appendChild(cartTable);
                form.appendChild(cartTableDiv);
                form.appendChild(cTag('input',{ 'type':'hidden','name':'stock_take_items_id','id':'stock_take_items_id','value':stock_take_items_id }));
            formhtml.appendChild(form);				
            
            popup_dialog600(Translate('Change Stock Take'), formhtml, Translate('Save'), saveChangeSTIInfo);

            setTimeout(function() {		
                document.getElementById("STCartIMEI").focus();
                document.querySelector("#checkAll").addEventListener('click', function(){
                    let checkedOrNot = this.checked;
                    document.querySelectorAll(".oneId").forEach(item=>{
                        item.checked = checkedOrNot;
                    })
                    countSTIMEIckt();
                });
                document.querySelectorAll(".oneId").forEach(item=>{
                    item.addEventListener('click',function(){
                        countSTIMEIckt();
                    });
                })
                document.querySelector('#STCartIMEI').addEventListener('keydown',function (e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        searchSTCartIMEI();
                    }
                });

                autoCompleteData("#STCartIMEI", data.autoCurIMEI, STCartIMEIAutoComplete);
               
                countSTIMEIckt();
            }, 500);
        }
	}	
}

function autoCompleteData(fieldId, data, returnFunc){
	const searchInput = document.querySelector(fieldId);
	searchInput.addEventListener('keyup', function (){displayMatches(fieldId, data, returnFunc)});
}

function displayMatches(fieldId, data, returnFunc){
	let selft = {};
	selft.fnc = returnFunc;
    selft.fieldId = fieldId;
	selft.selected = '';

	let autoResult;
    if(document.querySelector("#autoResult")){
		autoResult = document.querySelector("#autoResult");
		autoResult.parentElement.removeChild(autoResult);
	}
	let autoComIdObj = document.querySelector(fieldId);
	let autoWidth = autoComIdObj.clientWidth;
	
	autoResult = cTag('div', {id:'autoResult'});
	autoResult.style.width = autoWidth+'px';
	autoComIdObj.parentNode.insertBefore(autoResult, autoComIdObj.nextSibling);
	let autVal = autoComIdObj.value;

	if (autVal === '') {return;}
	else{
		const matchArray =  data.filter(place=>{
			const regex = new RegExp(autVal, 'gi');
			return place.label.match(regex);
		});
		
		let ul = cTag('ul');
		matchArray.map(place=>{
            const regex = RegExp(autVal, 'gi');
            const labelStr = place.label.replace(regex, `<span style = "background-color: pink;">${autVal}</span>`);
			selft.selected = place;
            let li = cTag('li');
			li.addEventListener('click', ()=>{
				let fn = selft.fnc;
        		if(typeof fn === "function"){
					fn(place, selft.fieldId);
				}
			});
			li.innerHTML = labelStr;
			ul.appendChild(li); 
        });
		autoResult.appendChild(ul); 
    }

	autoResult = document.querySelector("#autoResult");
	document.addEventListener('click', function(event) {		
		if(autoResult){
			let isClickInsideElement = autoResult.contains(event.target);
			if (!isClickInsideElement){
				autoResult.innerHTML = '';
				if(autoResult.style.display !== 'none'){
					autoResult.style.display = 'none';
				}
			}
		}
	});	
}

function STCartIMEIAutoComplete(objectData, fieldId){
	if(document.querySelector("#autoResult")){
		let autoResult = document.querySelector("#autoResult");
		autoResult.parentElement.removeChild(autoResult);
	}	
	document.querySelector(fieldId).value = objectData.label;
	searchSTCartIMEI();
	return false;
}

function searchSTCartIMEI(){
	if(document.querySelectorAll(".oneId").length){
		const STCartIMEI = document.querySelector('#STCartIMEI').value;
        document.querySelectorAll(".oneId").forEach(oneRow=>{
			let rowIMEI = oneRow.value;
			if(STCartIMEI===rowIMEI){
				oneRow.checked = true;
			}
		});
		document.querySelector('#STCartIMEI').value = '';
		countSTIMEIckt();
	}
}

async function saveChangeSTIInfo(hidePopup){
	if(document.querySelectorAll(".oneId").length){
		let counted_imeis = '';
		let inventory_count = 0;
        document.querySelectorAll(".oneId").forEach(oneRow=>{
			if(oneRow.checked === true){
				inventory_count++;
				if(inventory_count>1){counted_imeis += '|';}
				counted_imeis += oneRow.value;
			}
		});

		const stock_take_items_id = document.querySelector('#stock_take_items_id').value;
        
        const jsonData = {stock_take_items_id:stock_take_items_id, inventory_count:inventory_count, counted_imeis:counted_imeis};
        const url = '/'+segment1+'/AJupdateSTIimei';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.returnStr==='error'){
                showTopMessage('error_msg',Translate('Error occurred while changing information! Please try again.'));
			}
			else{
				replaceSTTdText(stock_take_items_id, inventory_count, 'invCnt', 'Live Stocks');
			}			
			hidePopup();
        }
		return false;
	}
}

function countSTIMEIckt(){
    if(document.querySelectorAll(".oneId").length){
		let curInvVal = 0;
		let countedVal = 0;
        document.querySelectorAll(".oneId").forEach(oneRow=>{
			curInvVal++;
			if(oneRow.checked === true){
				countedVal++;
			}
		});
		document.querySelector("#curInvVal").innerHTML = curInvVal;
		document.querySelector("#countedVal").innerHTML = countedVal;
	}
}

function cancelST(){
	const stock_take_id = document.querySelector("#sstock_take_id").value;
	if(stock_take_id>0){
		let htmlStr = Translate('Are you sure you want to cancel this Stock Take?');
		confirm_dialog(Translate('Cancel Stock Take'), htmlStr, confirmCancelST);
	}
}

async function confirmCancelST(hidePopup){
	const stock_take_id = document.querySelector("#sstock_take_id").value;

	if(stock_take_id>0){
        const jsonData = {"stock_take_id":stock_take_id, 'status':'Cancel'};
        const url = '/'+segment1+'/cancelST';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.action==='reload'){location.reload();}
			else if(data.action==='Canceled'){
				window.location = '/Stock_Take/lists';
			}
			else{
                showTopMessage('alert_msg',Translate('Could not cancel this Stock Take'));
			}
            hidePopup();
        }
	}
}

async function submitSKU_BarcodeST(){
	const stock_take_id = document.querySelector("#sstock_take_id").value;
	const SKU_Barcode = document.querySelector("#SKU_Barcode").value;

	if(stock_take_id>0 && SKU_Barcode !==''){
        const jsonData = {"stock_take_id":stock_take_id, 'SKU_Barcode':SKU_Barcode};
        const url = '/'+segment1+'/AJupdateSTIICBySKU';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.action==='reload'){location.reload();}
			else if(data.action==='Added'){
				if(data.id>0 && document.querySelector("#invCnt"+data.id)){
					replaceSTTdText(data.id, data.fieldVal, 'invCnt', data.itemType);
				}
			}
            document.querySelector("#SKU_Barcode").value = '';
        
			let showSKU_BarcodeMSG = document.querySelector("#showSKU_BarcodeMSG");
            showSKU_BarcodeMSG.innerHTML = '';
            if(data.message===''){
                const span = cTag('span',{'style': "color: #F00;",});
                span.innerText = `${Translate('There is no product found by this sku')}: ${SKU_Barcode}`
                showSKU_BarcodeMSG.appendChild(span) ;
            }
            else{
                if(data.message.status==='added'){
                    const span = cTag('span',{'style': "color: #090;"});
                    span.innerText = `${Translate('SKU/Barcode')}: ${data.message.sku}, IMEI: ${data.message.SKU_Barcode}, ${Translate('Current Inventory')}: ${data.message.inventory_current}, ${Translate('Counted')}: ${data.message.inventory_count}`;
                    showSKU_BarcodeMSG.appendChild(span) ;
                }
                else if(data.message.status==='duplicate'){
                    const span = cTag('span',{'style': "color: #F00;",});
                    span.innerText = `${Translate('SKU/Barcode')}: ${data.message.sku}, IMEI: ${data.message.SKU_Barcode}, ${Translate('Current Inventory')}: ${data.message.inventory_current}, ${Translate('Counted')}: ${data.message.inventory_count}. This IMEI already counted.`;
                    showSKU_BarcodeMSG.appendChild(span) ;
                }
                else if(data.message.status==='error'){
                    const span = cTag('span',{'style': "color: #090;"});
                    span.innerText = `${Translate('SKU/Barcode')}: ${data.message.SKU_Barcode}, ${Translate('Current Inventory')}: ${data.message.inventory_current}, ${Translate('Counted')}: ${data.message.inventory_count}`;
                    showSKU_BarcodeMSG.appendChild(span) ;
                }
            }

			setTimeout(function() {showSKU_BarcodeMSG.innerHTML = '&nbsp;';}, 10000);
        }
	}
}

function completeST(){
    if(allowed['9'] && allowed['9'].includes('cncst')){
		noPermissionWarning(Translate('Mark Completed'));
		return;
	}
	let htmlStr = Translate('This will update the current inventory count with the COUNTED quantity and can not be changed later.  Are you sure you want to do this?');
	confirm_dialog(Translate('Confirm'), htmlStr, confirmCompleteST);
}

async function confirmCompleteST(hidePopup){
	const stock_take_id = document.querySelector('#table_idValue').value;
	if(stock_take_id>0){
        const jsonData = {"stock_take_id":stock_take_id, 'status':'Closed'};
        const url = '/'+segment1+'/confirmCompleteST';
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.action==='reload'){location.reload();}
			else if(data.action==='Completed'){
				window.location = '/Stock_Take/lists';
			}
			else{
                showTopMessage('alert_msg',Translate('Could not cancel this Stock Take'));
			}
            hidePopup();
        }
	}
}

function stockTakePrint(stock_take_id){
	const sview2_type = document.querySelector("#sview2_type").value;
	const keyword_search = document.querySelector("#keyword_search").value;
	
	printbyurl('/Stock_Take/prints/large/'+stock_take_id+'/'+sview2_type+'/'+keyword_search);
}

function set_StockTake_Searchresult(tableID,tableData, attributes, uriStr){
    const tbody = document.getElementById(tableID);
	tbody.innerHTML = '';
	//=======Create TBody TR Column=======//
	if(tableData.length){
		tableData.forEach(oneRow => {
			let i = 0;
			let tr = document.createElement('tr');
			oneRow.forEach(tdvalue => {
				if(i>=0){
					let idVal = oneRow[0];
					let td = document.createElement('td');
					let oneTDObj = attributes[i];
                    for(const [key, value] of Object.entries(oneTDObj)) {
						let attName = key;
						if(attName !=='' && attName==='datatitle')
							attName = attName.replace('datatitle', 'data-title');
                        td.setAttribute(attName, value);
					}
					if(tdvalue.includes("<a ") || uriStr===''){
						td.innerHTML = tdvalue;
					}
					else{
						let aTag = document.createElement('a');
						aTag.setAttribute('class', 'anchorfulllink');
						aTag.setAttribute('href', '/'+uriStr+'/'+idVal);
						aTag.innerHTML=tdvalue;
						td.appendChild(aTag);
					}
					tr.appendChild(td);
				}
				i++;
			});
			tbody.appendChild(tr);
		});
	}
}

function set_StockTake_TableRows(tableRowsData){
    let tdCol;
    const tableRows = document.getElementById('listsTable');
    tableRows.innerHTML = '';
    if(tableRowsData.length>0){
        tableRowsData.forEach((data,indx)=>{
            let del_edit = document.createDocumentFragment();
            del_edit.append(data[7]);
        
            let del_edit2 = document.createDocumentFragment();
            del_edit2.append(data[8]);
            if(document.querySelector('#status').innerText==="Open"){
                if(data[6]==='Live Stocks'){
                    del_edit.append(' \u2003',cTag('i',{ 'class':"cursor fa fa-edit", 'click':()=>changeSTCartIMEI(data[0], 'invCnt', data[6]) }));
                }
                else{
                    del_edit.append(' \u2003',cTag('i',{ 'class':"cursor fa fa-edit", 'style': "float: right;", 'click':()=>changeSTCartInfo(data[0], 'invCnt', data[6]) }));
                }
                del_edit2.append(' \u2003',cTag('i',{ 'class':"cursor fa fa-edit", 'style': "float: right;", 'click':()=>changeSTCartInfo(data[0], 'note', data[6])}))
            }
            let tableHeadRow = cTag('tr',{ 'class':`stock_takeRow${data[0]}` });
                tdCol = cTag('td',{ 'align':`right` });
                tdCol.innerHTML = ++indx;
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'align':`left` });
                tdCol.innerHTML = data[1];
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'align':`left` });
                tdCol.innerHTML = data[2];
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'align':`left` });
                tdCol.innerHTML = data[3];
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'align':`center` });
                tdCol.append(data[4]||'&nbsp;');
                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`stock_take_items_id[]`,'value':`${data[0]}` }));
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'id':`invCur${data[0]}`,'align':`right` });
                tdCol.innerHTML = data[5];
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{'id':`invCnt${data[0]}`,'align':`right` });
                let currentQty = data[5];
                let countedQty = data[7];
                if(currentQty !== countedQty && countedQty !==''){
                    tdCol.setAttribute('class', "alert-danger");
                }
                tdCol.appendChild(del_edit);
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'id':`note${data[0]}`,'align':`left` });
                tdCol.appendChild(del_edit2);
            tableHeadRow.appendChild(tdCol);
            tableRows.appendChild(tableHeadRow);
        })
    }    
}

function setSessionData(){
    const jsonData = {};
	jsonData['shistory_type'] = document.querySelector('#shistory_type').value;    
	jsonData['sview2_type'] = document.querySelector('#sview2_type').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['limit'] = checkAndSetLimit();

    storeSessionData(jsonData);
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, add, edit};
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});