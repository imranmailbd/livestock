import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, DBDateToViewDate, printbyurl, setSelectOpt, setTableHRows, 
    showTopMessage, setOptions, addPaginationRowFlex, checkAndSetSessionData, popup_dialog600, popup_dialog1000, date_picker, validDate, 
    applySanitizer, generateCustomeFields, fetchData, listenToEnterKey, addCustomeEventListener, actionBtnClick, callPlaceholder, 
    serialize, onClickPagination, historyTable, activityFieldAttributes, noPermissionWarning, validifyCustomField, alert_label_missing
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

let segment5 = '';
if(pathArray.length>4){
    segment4 = pathArray[4];
    if(pathArray.length>5){segment5 = pathArray[5];}
}

const listsFieldAttributes = [
    {align:'center', 'data-title':Translate('Name'), style:'vertical-align:middle'},
    {align:'center', 'data-title':Translate('Color'), style:'vertical-align:middle'},
    {align:'center', 'data-title':Translate('TAG Number'), style:'vertical-align:middle'},
    {align:'right', 'data-title':Translate('Height')},
    {align:'right', 'class':'hidefor991-768', 'data-title':Translate('Weight')},
];

const uriStr = segment1+'/view';

async function filter_IMEI_lists(){

    let page = 1;
    
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sin_inventory'] = document.getElementById("sin_inventory").value;
	const sproduct_id = document.getElementById("sproduct_id");
	jsonData['sproduct_id'] = sproduct_id.value;
	const scarrier_name = document.getElementById("scarrier_name").value;
	jsonData['scarrier_name'] = scarrier_name;
	const scolour_name = document.getElementById("scolour_name").value;
	jsonData['scolour_name'] = scolour_name;
	const sphysical_condition_name = document.getElementById("sphysical_condition_name").value;
	jsonData['sphysical_condition_name'] = sphysical_condition_name;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetPage/filter';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);

        sproduct_id.innerHTML = '';
        const option = cTag('option',{'value': 0});
        option.innerHTML = Translate('All Livestocks');
        sproduct_id.appendChild(option);
        setOptions(sproduct_id, data.proNamOpt, 1, 1);

        setSelectOpt('scarrier_name', '', Translate('All Carriers'), data.carNamOpt, 0, data.carNamOpt.length);			
        setSelectOpt('scolour_name', '', Translate('All Colors'), data.colNamOpt, 0, data.colNamOpt.length);			
        setSelectOpt('sphysical_condition_name', '', Translate('All Cond'), data.phyConNamOpt, 0, data.phyConNamOpt.length);			
        
        //create table data rows
        createListsRow(data.tableRows, listsFieldAttributes, uriStr);

        document.getElementById("totalTableRows").value = data.totalRows;
        
        document.getElementById("sproduct_id").value = jsonData['sproduct_id'];
        document.getElementById("scarrier_name").value = scarrier_name;
        document.getElementById("scolour_name").value = scolour_name;
        document.getElementById("sphysical_condition_name").value = sphysical_condition_name;
        
        onClickPagination();
    }
}

async function loadTableRows_IMEI_lists(){
	const jsonData = {};
	jsonData['sin_inventory'] = document.getElementById("sin_inventory").value;
	jsonData['sproduct_id'] = document.getElementById("sproduct_id").value;
	jsonData['scarrier_name'] = document.getElementById("scarrier_name").value;
	jsonData['scolour_name'] = document.getElementById("scolour_name").value;
	jsonData['sphysical_condition_name'] = document.getElementById("sphysical_condition_name").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        createListsRow(data.tableRows, listsFieldAttributes, uriStr);
        onClickPagination();
    }
}

function createListsRow(tableData,listsFieldAttributes, uriStr){
    const node = document.getElementById("tableRows");
    node.innerHTML = '';
    if(tableData.length){
        tableData.forEach(item=>{
            const tr = cTag('tr');
            item.forEach((itemInfo,index)=>{
                if(index===0 || index===7) return;
                const td = cTag('td');
                const attributes = listsFieldAttributes[index-1];
                // console.log(attributes);
                for (const key in attributes) {
                    td.setAttribute(key,attributes[key]);
                }
                // console.log(td);
                if(index===4){

                    if(itemInfo !== ''){                                                
                        let aTag = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "livestock_height", id: "livestock_height",  'value': "", 'size': 35, 'maxlength': 35});
                        td.appendChild(aTag);
                    }
                    else{td.append('\u2003');}

                }
                else if(index === 1){
                    if(itemInfo===''){itemInfo = '\u2003';}
                    const aTag = cTag('a',{'class':'anchorfulllink', 'style':'text-decoration:underline', 'href':`/${uriStr}/${item[3]}`});
                    
                    aTag.innerHTML = itemInfo;
                    const inputFieldHidd = cTag('input',{ 'type': 'hidden', 'name': 'product_id','id': 'product_id','value':`${item[0]}` });
                    aTag.appendChild(inputFieldHidd);
                    td.appendChild(aTag);
                }
                else if(index===5){

                    if(itemInfo !== ''){                                                
                        let aTag = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "livestock_weight", id: "livestock_weight",  'value': "", 'size': 35, 'maxlength': 35});
                        td.appendChild(aTag);
                    }
                    else{td.append('\u2003');}
                    
                    // if(itemInfo !== ''){
                    //     const aTag = cTag('a',{'style': "color: #009; text-decoration: underline;", 'href':`/Purchase_orders/edit/${itemInfo}`,'title':Translate('View PO')});
                    //     aTag.append(itemInfo,' ',cTag('i',{'class':'fa fa-link'}));
                    //     td.appendChild(aTag);
                    // }
                    // else{td.append('\u2003');}
                }
                else if(index===6){
                    if(item[10]===1){
                        const inventoryLink = cTag('a',{'class':'anchorfulllink','href':`/${uriStr}/${item[0]}`});
                        inventoryLink.innerHTML = Translate('In Inventory');
                        td.appendChild(inventoryLink);
                    }
                    else if(itemInfo>0){
                        const invoiceLink = cTag('a',{'href':`/Invoices/view/${itemInfo}`,'title':Translate('View Invoice')});
                        invoiceLink.append('s'+itemInfo,' ',cTag('i',{'class':'fa fa-link'}));
                        td.appendChild(invoiceLink);
                    }
                    else{td.append('\u2003');}
                }
                else{
                    if(itemInfo===''){itemInfo = '\u2003';}
                    const aTag = cTag('span');
                    aTag.innerHTML = itemInfo;
                    td.appendChild(aTag);
                }
                tr.appendChild(td);
            })
            node.appendChild(tr);
        })
    }
    else{

    }
}


function controller_bar(id,cancelHandler){
    const controller = cTag('div', {class: "flexStartRow", style:"float:right; margin-top:20px; margin-bottom:20px;"});
    // controller.appendChild(cTag('input', {'type': "hidden", name: id, id: id, 'value': 0}));
    controller.appendChild(cTag('input', {'type': "hidden", name: 'nameVal', id: 'nameVal', 'value': ''}));
    controller.appendChild(cTag('input', {'click':cancelHandler,'type': "button", name: "reset", id: "reset", 'value': Translate('Cancel'), class: "btn defaultButton", 'style': "margin-right: 10px;"}));
    controller.appendChild(cTag('input', {'type': "submit", id: "submit", class: "btn saveButton", 'style': "margin-right: 10px; float:right;", 'value': Translate('Save') }));
    return controller;
}


function lists(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    let input, filterRow, sortDropDown;
    //====Hidden Fields for Pagination=====//
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '30' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

        const titleRow = cTag('div', {'class':'outerListsTable','style': "padding: 5px; text-align: start;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Livestock Growthinfo Entry');
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('A visual view of the phone models available in your inventory.')});
            headerTitle.append(' ', infoIcon);
        titleRow.appendChild(headerTitle);
    showTableData.appendChild(titleRow);

        filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnLG2"});
                const selectInventory = cTag('select', {class: "form-control", name: "sin_inventory", id: "sin_inventory"});
                selectInventory.addEventListener('change', filter_IMEI_lists);
                [
                    { value: 1, label: Translate('Livestock in Inventory')},
                    { value: 0, label: Translate('Livestock not in Inventory')},
                    { value: 2, label: Translate('All Livestock')},
                ].forEach(field=>{
                    const inventoryOption = cTag('option', {'value': field.value});
                    inventoryOption.innerHTML = field.label;
                    selectInventory.appendChild(inventoryOption);
                });
            sortDropDown.appendChild(selectInventory);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnLG3"});
                const selectsProduct = cTag('select', {class: "form-control", name: "sproduct_id", id: "sproduct_id"});
                selectsProduct.addEventListener('change', filter_IMEI_lists);
                    const productOption = cTag('option', {'value': 0});
                    productOption.innerHTML = Translate('All Device Models');
                selectsProduct.appendChild(productOption);
            sortDropDown.appendChild(selectsProduct);
        filterRow.appendChild(sortDropDown);

            let filterRow2 = cTag('div', {class: "columnXS12 columnLG7 flexEndRow", 'style': "margin: 0; padding-left: 0; padding-right: 0;"});
                
            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                    const selectCarrierName = cTag('select', {class: "form-control", name: "scarrier_name", id: "scarrier_name"});
                    selectCarrierName.addEventListener('change', filter_IMEI_lists);
                        const carrierOption = cTag('option', {'value': ""});
                        carrierOption.innerHTML = Translate('All Carriers');
                    selectCarrierName.appendChild(carrierOption);
                sortDropDown.appendChild(selectCarrierName);
            filterRow2.appendChild(sortDropDown);
                
                sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                    const selectColorName = cTag('select', {class: "form-control", name: "scolour_name", id: "scolour_name"});
                    selectColorName.addEventListener('change', filter_IMEI_lists);
                        const colorOption = cTag('option', {'value': ""});
                        colorOption.innerHTML = Translate('All Colors');
                    selectColorName.appendChild(colorOption);
                sortDropDown.appendChild(selectColorName);
            filterRow2.appendChild(sortDropDown);

                sortDropDown = cTag('div', {class: "columnXS6 columnSM2"});
                    const selectPhysical = cTag('select', {class: "form-control", name: "sphysical_condition_name", id: "sphysical_condition_name"});
                    selectPhysical.addEventListener('change', filter_IMEI_lists);
                        const physicalOption = cTag('option', {'value': ""});
                        physicalOption.innerHTML = Translate('All Cond');
                    selectPhysical.appendChild(physicalOption);
                sortDropDown.appendChild(selectPhysical);
            filterRow2.appendChild(sortDropDown);

                const searchDiv = cTag('div', {class: "columnXS6 columnSM4"});
                    const SearchInGroup = cTag('div', {class: "input-group"});
                        const searchField = cTag('input', {'keydown':listenToEnterKey(filter_IMEI_lists),'type': "text", 'placeholder': Translate('Search'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength':50,});
                    SearchInGroup.appendChild(searchField);
                        const searchSpan = cTag('span', {class: "input-group-addon cursor", 'click': filter_IMEI_lists, 'data-toggle':"tooltip", title: "", 'data-original-title': Translate('Search')});
                            const searchIcon = cTag('i', {class: "fa fa-search"});
                        searchSpan.appendChild(searchIcon);
                    SearchInGroup.appendChild(searchSpan);
                searchDiv.appendChild(SearchInGroup);
            filterRow2.appendChild(searchDiv);
        filterRow.appendChild(filterRow2);
    showTableData.appendChild(filterRow);

        const divTableColumn = cTag('div', {class: "columnXS12"});
            const bulkGrowthInfoEntryForm = cTag('form',{ 'method':`post`,'action':`#`,'enctype':`multipart/form-data`,'name':`frm_pos`,'id':`frm_pos` });
            bulkGrowthInfoEntryForm.addEventListener('submit',event=>event.preventDefault());
            bulkGrowthInfoEntryForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
            const divNoMore = cTag('div', {id: "no-more-tables"});
                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                    const listHead = cTag('thead', {class: "cf"});
                        const columnNames = listsFieldAttributes.map(colObj=>(colObj['data-title']));
                        const listHeadRow = cTag('tr',{class:'outerListsTable'});
                            const thCol0 = cTag('th');
                            thCol0.innerHTML = columnNames[0];
                            
                            const thCol1 = cTag('th', {'width': "15%"});
                            thCol1.innerHTML = columnNames[1];

                            const thCol2 = cTag('th', {'width': "15%"});
                            thCol2.innerHTML = columnNames[2];

                            const thCol3 = cTag('th', {'width': "8%", class: "hidefor991-768"});
                            thCol3.innerHTML = columnNames[3];

                            const thCol4 = cTag('th', {'width': "8%", class: "hidefor991-768"});
                            thCol4.innerHTML = columnNames[4];

                            // const thCol5 = cTag('th', {'width': "15%", class: "hidefor991-768"});
                            // thCol5.innerHTML = columnNames[5];

                            // const thCol6 = cTag('th', {'width': "7%"});
                            // thCol6.innerHTML = columnNames[6];

                            // const thCol7 = cTag('th', {'width': "12%", class: "hidefor991-768"});
                            // thCol7.innerHTML = columnNames[7];

                            // const thCol8 = cTag('th', {'width': "10%", class: "hidefor991-768"});
                            
                            // thCol8.innerHTML = columnNames[8];

                        // listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8);
                        listHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
                    listHead.appendChild(listHeadRow);
                listTable.appendChild(listHead);
                    const listBody = cTag('tbody', {id: "tableRows"});
                listTable.appendChild(listBody);
            divNoMore.appendChild(listTable);
            bulkGrowthInfoEntryForm.appendChild(divNoMore);
            bulkGrowthInfoEntryForm.appendChild(controller_bar('growthinfo_single_id',''));
        divTableColumn.appendChild(bulkGrowthInfoEntryForm);
    showTableData.appendChild(divTableColumn);
    addPaginationRowFlex(showTableData)
    
    //=====sessionStorage =====//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    let sin_inventory = '1', sproduct_id = '0', scarrier_name = '', scolour_name = '', sphysical_condition_name = '';
    if(segment3==='removed'){sin_inventory = '0';}
    else if(segment3==='added'){sin_inventory = '2';}
    else if(['product', 'inventory'].includes(segment4) && segment5 !==''){
        sproduct_id = parseInt(segment5);
        if(sproduct_id==='' || isNaN(sproduct_id)){sproduct_id = '0';}
    }
    
    checkAndSetSessionData('sin_inventory', sin_inventory, list_filters);
    checkAndSetSessionData('sproduct_id', sproduct_id, list_filters);
    checkAndSetSessionData('scarrier_name', scarrier_name, list_filters);
    checkAndSetSessionData('scolour_name', scolour_name, list_filters);
    checkAndSetSessionData('sphysical_condition_name', sphysical_condition_name, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    addCustomeEventListener('filter',filter_IMEI_lists);
    addCustomeEventListener('loadTable',loadTableRows_IMEI_lists);
    filter_IMEI_lists(true);
}

async function filter_IMEI_view(){
    let page = 1;
	document.getElementById("page").value = page;
    
	const jsonData = {};
	jsonData['sproduct_id'] = document.getElementById("sproduct_id").value;
	jsonData['sitem_id'] = document.getElementById("table_idValue").value;
	jsonData['item_number'] = document.getElementById("item_numberValue").value;
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

async function loadTableRows_IMEI_view(){
	const jsonData = {};
	jsonData['sproduct_id'] = document.getElementById("sproduct_id").value;
	jsonData['sitem_id'] = document.getElementById("table_idValue").value;
	jsonData['item_number'] = document.getElementById("item_numberValue").value;
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
	const item_number = document.getElementById("item_numberValue").value;
	const jsonData = {};
	jsonData['item_number'] = item_number;

    const url = '/'+segment1+'/AJ_view_MoreInfo';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        sessionStorage.setItem('data', JSON.stringify(data));
        document.getElementById("sproduct_id").value = data.product_id;
        document.getElementById("table_idValue").value = data.item_id;

        let span, aTag, br, linkIcon;
        const viewBasicInfo = document.getElementById("viewBasicInfo");
        viewBasicInfo.innerHTML = '';
            const imgColumn = cTag('div', {class: "columnSM5 columnMD3"});
                const divImg = cTag('div', {class: "image"});
                    const img = cTag('img', {'alt': data.product_name, class: "img-responsive", 'style': "max-height: 250px;", 'src': data.imei_imageURL});
                divImg.appendChild(img);
            imgColumn.appendChild(divImg);
        viewBasicInfo.appendChild(imgColumn);

        let customFieldDiv = cTag('div');
        if (data.customFields>0) {
            customFieldDiv = cTag('div', {class: 'columnSM7 columnMD5', 'style': "border-right: 1px solid #CCC;"});
        }
        else{
            customFieldDiv = cTag('div', {class : 'columnSM7 columnMD9'});
        }
            const imgContent = cTag('div', {class: "image_content", 'style': "text-align: left;"});
                const imgContentHeader = cTag('h3');
                imgContentHeader.innerHTML = data.item_number+' : ';
            imgContent.appendChild(imgContentHeader);
                const modelRow = cTag('div',{'style': "padding: 5px;"});
                    let modelTitle = cTag('label');
                    modelTitle.innerHTML = Translate('Model')+' : '+data.product_name;
                modelRow.appendChild(modelTitle);
            imgContent.appendChild(modelRow);

            if(data.colorlockcarrier !==''){
                const lockCarrier = cTag('div', {'style': "font-weight: bold; padding: 5px"});
                    let carrierTitle = cTag('label');
                    carrierTitle.innerHTML = Translate('Carrier')+' : '+data.colorlockcarrier;
                lockCarrier.appendChild(carrierTitle);
                imgContent.appendChild(lockCarrier);
            }

                const SKUBarcodeRow = cTag('div',{'style': "padding: 5px;"});
                    let SKUBarcodeTitle = cTag('label');
                    SKUBarcodeTitle.innerHTML = Translate('SKU/Barcode')+' : ';
                SKUBarcodeRow.appendChild(SKUBarcodeTitle);
                    const viewLink = cTag('a', {'style': "font-weight: bold; padding-left: 6px;", href: '/Products/view/'+data.product_id, title: Translate('View')+' '+data.sku_number+' '+Translate('Information')});
                    viewLink.innerHTML = data.sku_number+' ';
                        linkIcon = cTag('i', {class: "fa fa-link"});
                    viewLink.appendChild(linkIcon);
                SKUBarcodeRow.appendChild(viewLink);
            imgContent.appendChild(SKUBarcodeRow);
            
                const dateAddedRow = cTag('div',{'style': "padding: 5px;"});
                    let dateLabel = cTag('label');
                    dateLabel.innerHTML = Translate('Date Added')+' : '+DBDateToViewDate(data.created_on);
                dateAddedRow.appendChild(dateLabel);
            imgContent.appendChild(dateAddedRow);

                const poCostRow = cTag('div', {'style': "padding: 5px;"});
                    let poTitle = cTag('label',{'style': "margin-right: 10px;"});
                    poTitle.innerHTML = Translate('PO')+' : ';
                    if(data.po_number.length>0){
                        let l = 0;
                        data.po_number.forEach(onePONo=>{
                            let poLink = cTag('a', {'href': "/Purchase_orders/edit/"+onePONo, title: Translate('View PO')});
                            poLink.innerHTML = onePONo+' ';
                                linkIcon = cTag('i', {class: "fa fa-link"});
                            poLink.appendChild(linkIcon);
                            poTitle.appendChild(poLink);
                            if(l>0){
                                poTitle.append(', ');
                            }
                            l++;
                        });
                    }
                poCostRow.appendChild(poTitle);

                    const costLabel = cTag('strong');
                    if(data.allowed !==''){
                        costLabel.append(Translate('Cost')+' : '+addCurrency(data.cost));
                        poCostRow.append(costLabel);
                    }
            imgContent.appendChild(poCostRow);
            
                const flexRow = cTag('div', {style:'align-items:center; margin: 5px 0;'});
                if(data.in_inventory===0){
                    if(data.invoiceNo !==''){
                        flexRow.innerHTML = Translate('Sold on')+' : ';
                        span = cTag('span');
                        span.innerHTML = DBDateToViewDate(data.sales_datetime);
                        span.append('\u2003');//
                        if(data.orderInvNo !==''){
                            span.append(Translate('Order No.')+' : ');
                            aTag = cTag('a', {'href': "/Orders/edit/"+data.invoiceNo, title: Translate('View Order Details')});
                            aTag.innerHTML = 'o'+data.invoiceNo+' ';
                        }
                        else{
                            span.append(Translate('Invoice No.')+' : ');
                            aTag = cTag('a', {'href': "/Invoices/view/"+data.invoiceNo, title: Translate('View Invoice')});
                            aTag.innerHTML = 's'+data.invoiceNo+' ';
                        }
                        linkIcon = cTag('i', {class: "fa fa-link"});
                        aTag.appendChild(linkIcon);
                        span.appendChild(aTag);
                        flexRow.appendChild(span);

                        br = cTag('br');
                        flexRow.appendChild(br);
                        br = cTag('br');
                        flexRow.appendChild(br);
                    }
                }
                else{
                    const editButton = cTag('button', {class: "btn editButton", 'style': "margin-right: 15px;"});
                    editButton.addEventListener('click', AJgetPopup_IMEI);
                    editButton.innerHTML = ' '+Translate('Edit')+' ';
                    flexRow.appendChild(editButton);
                }

                if(data.in_inventory>0){
                    const removeButton = cTag('button', {class: "btn archiveButton", 'style': "margin-right: 15px;"});
                    if(allowed['8'] && allowed['8'].includes('cnrfi')) removeButton.addEventListener('click', function (){noPermissionWarning('to Remove from Inventory')});
                    else removeButton.addEventListener('click', AJremove_IMEI);
                    removeButton.innerHTML = Translate('Remove from inventory');
                    flexRow.appendChild(removeButton);
                }
                const printButton = cTag('button', {class: "btn completeButton"});
                printButton.append(cTag('i',{ 'class':`fa fa-print` }), ' ', Translate('Barcode Labels'));
                if(OS =='unknown'){
                    printButton.innerHTML = Translate('Barcode Label Print');
                }
                printButton.addEventListener('click', function (){printbyurl('/IMEI/prints/barcode/'+data.item_id);});
                flexRow.appendChild(printButton);
            imgContent.appendChild(flexRow);
        customFieldDiv.appendChild(imgContent);
        viewBasicInfo.appendChild(customFieldDiv);
        
        if(data.customFields>0){
            const customColumn = cTag('div', {class: "columnSM4", 'align': "left"});
                const customTable = cTag('div', {class:"columnSM12 customInfoGrid"});
                for(const [key, value] of Object.entries(data.customFieldData)) {
                    const customLabel = cTag('label');
                    customLabel.innerHTML = key+' : ';
                    const customValue = cTag('span');
                    customValue.innerHTML = value;
                    customTable.append(customLabel, customValue);
                }
            customColumn.appendChild(customTable);
            viewBasicInfo.appendChild(customColumn);

        }
        filter_IMEI_view();
    }
}

function view(){
    let segment4 = 1;
    if(pathArray.length>4){segment4 = pathArray[4];}
    
    let item_number = segment3;
    let item_id = 0;    
    
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Livestock Information')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays the information of IMEI')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
            const inventoryListLink = cTag('a', {class: "btn defaultButton", 'href':"/Growthinfos/lists", title:Translate('Livestock Inventory')});
            inventoryListLink.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('Livestock Inventory'));
        titleRow.appendChild(inventoryListLink);
    showTableData.appendChild(titleRow);
    
        const viewHeaderColumn = cTag('div', {class: "columnSM12"});
            let viewHeader = cTag('header', {class: "imageContainer flexSpaBetRow", 'style': "padding: 5px 15px;", id: "viewBasicInfo"});
        viewHeaderColumn.appendChild(viewHeader);
    showTableData.appendChild(viewHeaderColumn);

        const viewContentRow = cTag('div', {class: "flex"});
            const viewContentColumn = cTag('div', {class: "columnXS12"});
                let hiddenProperties = {
                    'sproduct_id': 0 ,
                    'note_forTable': 'item' ,
                    'table_idValue': item_id ,
                    'item_numberValue': item_number ,
                }
            viewContentColumn.appendChild(historyTable(Translate('RFID/TAG History'),hiddenProperties));
        viewContentRow.appendChild(viewContentColumn);
    showTableData.appendChild(viewContentRow);

    const loadData = 'AJ_'+segment2+'_MoreInfo';
    const fn = window[loadData];
    if(typeof fn === "function"){fn();}
    
    //=====sessionStorage =====//
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    const shistory_type = '';
    checkAndSetSessionData('shistory_type', shistory_type, list_filters);

    addCustomeEventListener('filter',filter_IMEI_view);
    addCustomeEventListener('loadTable',loadTableRows_IMEI_view);
    AJ_view_MoreInfo();
}

async function AJgetPopup_IMEI(){
    let storedData;
    if (sessionStorage.getItem("data") !== null) {
        storedData = JSON.parse(sessionStorage.getItem("data"));
    }
    else{
        AJ_view_MoreInfo();
        return false;
    }
    
    const item_id = document.getElementById("table_idValue").value;
    let customFields = 0;
    if(Object.keys(storedData).length>0){
        customFields = storedData.customFields
    }
    
    const jsonData = {};
	jsonData['item_id'] = item_id;
	jsonData['customFields'] = customFields;

	const frompage = segment1;
	
    const url = '/'+segment1+'/AJgetPopup';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let tabs, tab1, tab2, input, requiredField, errorMessage
        const formDialog = cTag('div');
            const imeiForm = cTag('form', {'action': "#", name: "frmitem", id: "frmitem", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
                
            if(customFields>0){
                tabs = cTag('div', {id: "tabs", 'style': "max-height: 600px; padding: 0;"});
                    const ulTabs = cTag('ul');
                        let liTabs1 = cTag('li');
                            const basicTab = cTag('a', {'href': "#tabs-1"});
                            basicTab.innerHTML = Translate('Basic Info');
                        liTabs1.appendChild(basicTab);
                    ulTabs.appendChild(liTabs1);

                        let liTabs2 = cTag('li');
                            const customTab =  cTag('a', {'href': "#tabs-2"});
                            customTab.innerHTML = Translate('Custom Fields');
                        liTabs2.appendChild(customTab)
                    ulTabs.appendChild(liTabs2);
                tabs.appendChild(ulTabs);
                tab1 = cTag('div', {class: "columnXS12", id: "tabs-1"});
            }
            else{
                tabs = cTag('div', {class: "flexSpaBetRow"});
                    tab1 = cTag('div', {class: "columnXS12"});
            }

                        const basicInfoRow = cTag('div', {class: "flexSpaBetRow"});
                            const basicInfoColumn = cTag('div', {class: "columnXS12 columnSM6", 'align': "left"});
                                const imeiNumberRow = cTag('div', {class: "flex"});
                                    const imeiNumberColumn = cTag('div', {class: "columnSM4"});
                                        const imeiNumberLabel = cTag('label', {'for': "item_number", 'data-toggle': "tooltip", 'data-placement': "right", title: Translate('Select the product name and number (if necessary) of the device you are entering.')});
                                        imeiNumberLabel.innerHTML = Translate('TAG Number');
                                            requiredField = cTag('span', {class: "required"});
                                            requiredField.innerHTML = '*';
                                        imeiNumberLabel.appendChild(requiredField);
                                    imeiNumberColumn.appendChild(imeiNumberLabel);
                                imeiNumberRow.appendChild(imeiNumberColumn);

                                    const imeiNumberField = cTag('div', {class: "columnSM8"});
                                        let inputField = cTag('input', {'type': 'text', 'required': "required", class: "form-control imeinumber", name: "item_number", id: "item_number", 'value': storedData.item_number, 'maxlength': 20, 'placeholder': Translate('TAG Number')});
                                        if(storedData.in_inventory ==='' || storedData.in_inventory===0){
                                            inputField.setAttribute('readonly','readonly');
                                        }
                                    imeiNumberField.appendChild(inputField);
                                        errorMessage = cTag('span', {class: "errormsg", id: "showErrorMessage"});
                                    imeiNumberField.appendChild(errorMessage);
                                imeiNumberRow.appendChild(imeiNumberField);
                            basicInfoColumn.appendChild(imeiNumberRow);

                                const modelRow = cTag('div', {class: "flex"});
                                    const modelColumn = cTag('div', {class: "columnSM4"});
                                        const modelLabel = cTag('label', {'for': "product_id", 'data-toggle': "tooltip", 'data-placement': "right", title: Translate('Select the product name and number (if necessary) of the device you are entering.')});
                                        modelLabel.innerHTML = Translate('Model');
                                            requiredField = cTag('span', {class: "required"});
                                            requiredField.innerHTML = '*';
                                        modelLabel.appendChild(requiredField);
                                    modelColumn.appendChild(modelLabel);
                                modelRow.appendChild(modelColumn);
                                    const modelDropDown = cTag('div', {class: "columnSM8"});
                                        let selectProduct = cTag('select', {'required': "required", class: "form-control", name: "product_id", id: "product_id"});
                                        if(storedData.in_inventory ==='' || storedData.in_inventory===0){
                                            selectProduct.setAttribute('disable','disable');
                                        }
                                            const productOption = cTag('option', {'value': 0});
                                        selectProduct.appendChild(productOption);
                                        setOptions(selectProduct, data.productOptions, 1, 1);
                                    modelDropDown.appendChild(selectProduct);
                                        errorMessage = cTag('span', {class: "errormsg", id: "errmsg_product_id"});
                                    modelDropDown.appendChild(errorMessage);
                                modelRow.appendChild(modelDropDown);
                            basicInfoColumn.appendChild(modelRow);
                        basicInfoRow.appendChild(basicInfoColumn);
                        
                            const carrierColumn = cTag('div', {class: "columnXS12 columnSM6"});
                                const carrierRow = cTag('div', {class: "flex", 'align': "left"});
                                    const carrierName = cTag('div', {class: "columnSM4"});
                                        const carrierLabel = cTag('label', {'for': "carrier_name", 'data-toggle': "tooltip", 'data-placement': "right", title: Translate('Select the Carrier of the phone you are entering. Select Unlocked or Unknown if original carrier is unknown or if device is unlocked.')});
                                        carrierLabel.innerHTML = Translate('Carrier');
                                    carrierName.appendChild(carrierLabel);
                                carrierRow.appendChild(carrierName);
                                    const carrierDropDown = cTag('div', {class: "columnSM8"});
                                        let selectCarrier = cTag('select', {'required': "required", class: "form-control", name: "carrier_name", id: "carrier_name"});
                                            if(storedData.in_inventory ==='' || storedData.in_inventory===0){
                                                selectCarrier.setAttribute('disable','disable');
                                            }                                        
                                        setOptions(selectCarrier, data.carrNamOpt, 0, 1);
                                    carrierDropDown.appendChild(selectCarrier);
                                carrierRow.appendChild(carrierDropDown);
                            carrierColumn.appendChild(carrierRow);
                        basicInfoRow.appendChild(carrierColumn);
                    tab1.appendChild(basicInfoRow);
                tabs.appendChild(tab1);

                if(customFields>0){
                    tab2 = cTag('div', {class: "columnXS12", id: "tabs-2"});
                    generateCustomeFields(tab2,data.customFieldsData);
                    tabs.appendChild(tab2);
                }
            imeiForm.appendChild(tabs);

                input= cTag('input', {'type': "hidden", name: "frompage", id: "frompage", 'value': frompage});
            imeiForm.appendChild(input);

                input= cTag('input', {'type': "hidden", name: "item_id", id: "item_id", 'value': item_id});
            imeiForm.appendChild(input);

                input= cTag('input', {'type': "hidden", name: "in_inventory", id: "in_inventory", 'value': data.in_inventory});
            imeiForm.appendChild(input);

                input= cTag('input', {'type': "hidden", name: "customFields", id: "customFields", 'value': customFields});
            imeiForm.appendChild(input);
        formDialog.appendChild(imeiForm);
        
        popup_dialog1000(Translate('Device Information'),formDialog, AJsave_IMEI);
                
        setTimeout(function() {
            document.getElementById("product_id").value = storedData.product_id;
            document.getElementById("carrier_name").value = data.carrier_name;

            if(customFields>0){
                document.querySelector("#tabs").activateTab(0);
            }
            const ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#";
            document.getElementById("item_number").focus();
            document.getElementById("item_number").addEventListener('keyup', e => {
                const sku = e.target.value.toUpperCase().replace(' ', '-');
                const IsNumber=true;
                let Char;
                let newsku = '';
                for (let i = 0; i < sku.length && IsNumber === true; i++){ 
                    Char = sku.charAt(i); 
                    if (ValidChars.indexOf(Char) === -1){}
                    else{
                        newsku = newsku+Char;
                    }
                }			
                if(sku.length> newsku.length || e.target.value !== newsku){
                    e.target.value = newsku;
                }
            });

            if(customFields>0 && document.getElementsByClassName("DateField").length>0){
                date_picker('.DateField');
            }
            applySanitizer(formDialog);
        }, 500);
    }
}

async function AJsave_IMEI(hidePopup){			
	const oElement = document.getElementById('showErrorMessage');
	const errmsg_product = document.getElementById('errmsg_product_id');
	oElement.innerHTML = '';
	errmsg_product.innerHTML = '';

    let invalidDate = Array.from(document.getElementById('popup').querySelectorAll('.DateField')).filter(item=>{
		if(item.value!=='' && validDate(item.value)===false) return item;
	})
	if(invalidDate.length>0){
		oElement.innerHTML = 'Invalid Date';
		document.querySelector("#tabs").activateTab(1)
		invalidDate[0].focus();
		return;
	}

    let itemNumber = document.getElementById("item_number");
	if(itemNumber.value==='' || itemNumber.value.length<2){
		if(document.querySelector("#tabs")) document.querySelector("#tabs").activateTab(0);
		oElement.innerHTML = Translate('IMEI should be min 2 characters.');
		itemNumber.focus();
        itemNumber.classList.add('errorFieldBorder');
		return false;
	}else {
        itemNumber.classList.remove('errorFieldBorder');
    }

	oElement.innerHTML = '';
    let missingModel = document.getElementById("product_id");
	if(parseInt(missingModel.value)===0){
		if(document.querySelector("#tabs")) document.querySelector("#tabs").activateTab(0);
		errmsg_product.innerHTML = Translate('Missing Model');
		missingModel.focus();
        missingModel.classList.add('errorFieldBorder');
		return false;
	}else {
        missingModel.classList.remove('errorFieldBorder');
    }
	
    let validCustomFields = validifyCustomField(1);
	if(!validCustomFields) return;

	actionBtnClick('.btnmodel', Translate('Saving'), 1);   
	
    const url = '/'+segment1+'/AJsave_IMEI';
    fetchData(afterFetch,url, document.querySelector('#frmitem'), 'formData');

    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
            hidePopup();
			window.location = '/Growthinfos/view/'+data.item_number+'/'+data.savemsg;
		}
		else{
            if(data.message=='No_IMEI'){
                showTopMessage('alert_msg', Translate('There is no IMEI selected.'));
            }
            else if(data.returnStr=='Missing_Model'){
                showTopMessage('alert_msg', Translate('Missing Model'));
            }
            else if(data.returnStr=='Name_Already_Exist'){
                showTopMessage('alert_msg', Translate('This item number')+' '+document.getElementById("item_number").value+' '+Translate('is already exist! Please try again with different item number.'));
            }  
            else{
                showTopMessage('alert_msg', Translate('No changes / Error occurred while updating data! Please try again.'));
            }			

			if(parseInt(document.frmitem.item_id.value)===0){
                actionBtnClick('.btnmodel', Translate('Add'), 0);
			}
			else{
                actionBtnClick('.btnmodel', Translate('Update'), 0);
			}
		}
    }
}

async function AJremove_IMEI(){
    const item_id = document.getElementById("table_idValue").value;
    const formDialog = cTag('div');
        const imeiRemoveForm = cTag('form', {'action': "#", name: "frmAJremove_IMEI", id: "frmAJremove_IMEI", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
            const imeiRemoveRow = cTag('div', {class: "flexSpaBetRow"});
                const imeiRemoveColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                    const textarea = cTag('textarea', {'required': "", name: "imeimessage",id: "imeimessage", class: "form-control placeholder", 'rows': 3, placeholder: Translate('Type a REASON'), alt:Translate('Type a REASON') });
                imeiRemoveColumn.appendChild(textarea);
            imeiRemoveRow.appendChild(imeiRemoveColumn);
        imeiRemoveForm.appendChild(imeiRemoveRow);

            const errorMessageRow = cTag('div', {class: "flexSpaBetRow"});
                const errorMessage = cTag('span', {class: "error_msg", id: "errmsg_imeimessage"});
            errorMessageRow.appendChild(errorMessage);
        imeiRemoveForm.appendChild(errorMessageRow);
            let inputField = cTag('input', {name: "item_id", id: "item_id", 'type': "hidden",'value': item_id});
        imeiRemoveForm.appendChild(inputField);
    formDialog.appendChild(imeiRemoveForm);
    
    popup_dialog600(Translate('REMOVED FROM INVENTORY'),formDialog,Translate('Remove'),checkAJremove_IMEI);		
            
    setTimeout(function() {         
        document.getElementById("imeimessage").focus();
        callPlaceholder();
     }, 100);
}

async function checkAJremove_IMEI(hidePopup){
	const imeimessage = document.getElementById("imeimessage");
	const errorId = document.getElementById("errmsg_imeimessage");
	errorId.innerHTML = '';
	if(imeimessage.value===''){
		errorId.innerHTML = Translate('Missing Reason');
		imeimessage.focus();
		return false;
	}
	else if(imeimessage.value.length<10){
		errorId.innerHTML = Translate('Reason should be minimum 10 characters.');
		imeimessage.focus();
		return false;
	}
    actionBtnClick('.btnmodel', Translate('Removing'), 1);
    
    const jsonData = serialize('#frmAJremove_IMEI');
    const url = '/'+segment1+'/AJremove_IMEI';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.returnStr==='Ok'){
            hidePopup();
			location.reload();
		}
		else{
			if(data !==''){
				errorId.innerHTML = Translate('Inventory could not remove.');
			}
            actionBtnClick('.btnmodel', Translate('Remove'), 0);
		}
    }
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, view};
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    addCustomeEventListener('labelSizeMissing',alert_label_missing);
});