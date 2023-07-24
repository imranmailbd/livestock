import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, NeedHaveOnPO, round, calculate, DBDateToViewDate, 
    DBDateRangeToViewDate, noPermissionWarning, preventDot, printbyurl, confirm_dialog, setTableHRows, showTopMessage, setOptions, 
    addPaginationRowFlex, checkAndSetSessionData, upload_dialog, AJremove_tableRow, popup_dialog600, popup_dialog1000, date_picker, 
    checkDateOnBlur, dynamicImport, applySanitizer, archiveData, unarchiveData, alert_dialog,createTabs, validateRequiredField, alert_label_missing,
    generateCustomeFields, fetchData, listenToEnterKey, actionBtnClick, showNewInputOrSelect, serialize, multiSelectAction, controllNumericField,
    onClickPagination, wysiwyrEditor, historyTable, AJautoComplete, addCustomeEventListener, activityFieldAttributes, AJremove_Picture, stripslashes, validifyCustomField
} from './common.js';


if(segment2 === ''){segment2 = 'lists'}

const listsFieldAttributes = [
    {'datatitle':Translate('Manufacturer Name'), 'align':'left'},
    {'datatitle':Translate('Livestock Name'), 'align':'left'},
    {'datatitle':Translate('SKU/Barcode'), 'align':'left'},
    {'datatitle':Translate('Category Name'), 'align':'left'},
    {'datatitle':Translate('Selling Price'), 'align':'right'},
    {'datatitle':Translate('Need/Have/OnPO'), 'align':'center'}
];
const uriStr = segment1+'/view';

const avgCostFieldAttributes = [
                    {'valign':'top','datatitle':Translate('Date'), 'align':'left'},
                    {'valign':'top','datatitle':Translate('Time'), 'align':'right'},
                    {'valign':'top','datatitle':Translate('Activity'), 'align':'left'},
                    {'datatitle':Translate('PO')+' / '+Translate('Invoice No.'), 'align':'right'},
                    {'datatitle':'Prev QTY', 'align':'right'},
                    {'datatitle':'Prev Avg Cost', 'align':'right'},
                    {'datatitle':'Changed QTY', 'align':'right'},
                    {'datatitle':Translate('Cost'), 'align':'right'},
                    {'datatitle':Translate('QTY'), 'align':'right'},
                    {'datatitle':'New Avg Cost', 'align':'right'}
];

const priceFieldAttributes = [
                        {'datatitle':Translate('Price Type'), 'align':'left'},
                        {'datatitle':Translate('Type Match'), 'align':'left'},
                        {'datatitle':Translate('Fixed Price/ Percent Off'), 'align':'center'},
                        {'datatitle':Translate('Date Range'), 'align':'center'},
                        {'datatitle':Translate('Action'), 'align':'center'}
];
    
function setLivestockTableRows(tableData, attributes, uriStr){
    let tbody = document.getElementById("tableRows");
    tbody.innerHTML = '';
    //=======Create TBody TR Column=======//
    if(tableData.length){
        tableData.forEach(oneRow => {
            let i = 0;
            let tr = cTag('tr');
            oneRow.forEach(tdvalue => {
                if(i>1){
                    let idVal = oneRow[0];
                    let alertVal = oneRow[1];
                    let td = cTag('td');
                    if(alertVal !==''){
                        td.setAttribute('class', alertVal);
                    }
                    const oneTDObj = attributes[i-2];
                    for(const [key, value] of Object.entries(oneTDObj)) {
                        let attName = key;
                        if(attName !=='' && attName==='datatitle')
                            attName = attName.replace('datatitle', 'data-title');
                        td.setAttribute(attName, value);
                    }
                    if(tdvalue==='') tdvalue = '\u2003';
                    if(i===7){                        
                        tdvalue = NeedHaveOnPO(tdvalue, idVal);
                        td.appendChild(tdvalue||'&nbsp;');
                    }
                    else{
                        if(i===6){tdvalue = addCurrency(tdvalue);}
                        let aTag = cTag('a', {class: "anchorfulllink",'href': '/'+uriStr+'/'+idVal});
                        aTag.innerHTML = tdvalue;
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
                    
async function filter_Livestocks_lists(){
    let page = 1;
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.getElementById("sdata_type").value;
	let smanufacturer_id = document.getElementById("smanufacturer_id");
    let smanufacturerVal = smanufacturer_id.value;
	jsonData['smanufacturer_id'] = smanufacturerVal;
	let scategory_id = document.getElementById("scategory_id");
    let scategoryVal = scategory_id.value;
	jsonData['scategory_id'] = scategoryVal;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
    
    const url = '/'+segment1+'/AJgetPage/filter';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
        setLivestockTableRows(data.tableRows, listsFieldAttributes, uriStr);

        smanufacturer_id.innerHTML = '';
        let option = cTag('option', {'value': "All"});
            option.innerHTML = Translate('All Manufacturers');
        smanufacturer_id.appendChild(option);
        setOptions(smanufacturer_id, data.manOpt, 1, 1);

        scategory_id.innerHTML = '';
        let option1 = cTag('option', {'value': "All"});
            option1.innerHTML = Translate('All Categories');
            scategory_id.appendChild(option1);
        setOptions(scategory_id, data.catOpt, 1, 1);
        
        document.getElementById("totalTableRows").value = data.totalRows;
        
        smanufacturer_id.value = smanufacturerVal;
        scategory_id.value = scategoryVal;
        
        onClickPagination();
    }
}

async function  loadTableRows_Livestocks_lists(){
	const jsonData = {};
	jsonData['sdata_type'] = document.getElementById("sdata_type").value;
	jsonData['smanufacturer_id'] = document.getElementById("smanufacturer_id").value;
	jsonData['scategory_id'] = document.getElementById("scategory_id").value;
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById("page").value;
	
    const url = '/'+segment1+'/AJgetPage';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        setLivestockTableRows(data.tableRows, listsFieldAttributes, uriStr);
        onClickPagination();
    }
}

function  lists(){
   
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    let showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

    //======Hidden Fields for Pagination=======//
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '31' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        const input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
        showTableData.appendChild(input);
    });

        const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Manage Livestocks')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('A visual view of the phone models available in your inventory.')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);

            const createLivestockLink = cTag('a', {'href': "javascript:void(0);", title: Translate('Create Livestock')});
            createLivestockLink.addEventListener('click', function (){AJget_LivestocksPopup('List', 0, 0);});
                const productButton = cTag('button', {class: "btn createButton"});
                productButton.append(cTag('i', {class: "fa fa-plus"}), ' ', Translate('Create Livestock'));
            createLivestockLink.appendChild(productButton);
        titleRow.appendChild(createLivestockLink);
    showTableData.appendChild(titleRow);

        let sortDropDown;
        const filterRow = cTag('div', {class: "flexEndRow outerListsTable"});
            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectData = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type"});
                selectData.addEventListener('change', filter_Livestocks_lists);
                    const options = {'All':Translate('All Livestocks'), 'Available':Translate('Available'), 'Low Stock':Translate('Low Stock'), 'Archived':Translate('Archived Livestocks')};
                    for(const [key, value] of Object.entries(options)) {
                    let dataOption = cTag('option', {'value': key});
                    dataOption.innerHTML = value;
                selectData.appendChild(dataOption);
                }
            sortDropDown.appendChild(selectData);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectManufacturer = cTag('select', {class: "form-control", name: "smanufacturer_id", id: "smanufacturer_id"});
                selectManufacturer.addEventListener('change', filter_Livestocks_lists);
                    let manufacturerOption = cTag('option', {'value': "All"});
                    manufacturerOption.innerHTML = Translate('All Manufacturers');
                selectManufacturer.appendChild(manufacturerOption);
            sortDropDown.appendChild(selectManufacturer);
        filterRow.appendChild(sortDropDown);

            sortDropDown = cTag('div', {class: "columnXS6 columnSM3"});
                const selectCategory = cTag('select', {class: "form-control", name: "scategory_id", id: "scategory_id"});
                selectCategory.addEventListener('change', filter_Livestocks_lists);
                    let categoryOption = cTag('option', {'value': "All"});
                    categoryOption.innerHTML = Translate('All Categories');
                selectCategory.appendChild(categoryOption);
            sortDropDown.appendChild(selectCategory);
        filterRow.appendChild(sortDropDown);

            const searchDiv = cTag('div', {class: "columnXS6 columnSM3"});
                const SearchInGroup = cTag('div', {class: "input-group"});
                    const searchField = cTag('input', {'keydown':listenToEnterKey(filter_Livestocks_lists),'type': "text", 'placeholder': Translate('Search Livestocks'), id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
                SearchInGroup.appendChild(searchField);
                    const searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search Livestocks')});
                    searchSpan.addEventListener('click', filter_Livestocks_lists);
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
                            const thCol0 = cTag('th', {'width': "15%"});
                            thCol0.innerHTML = columnNames[0];

                            const thCol1 = cTag('th');
                            thCol1.innerHTML = columnNames[1];

                            const thCol2 = cTag('th', {'width': "22%"});
                            thCol2.innerHTML = columnNames[2];

                            const thCol3 = cTag('th', {'width': "15%"});
                            thCol3.innerHTML = columnNames[3];

                            const thCol4 = cTag('th', {'width': "10%"});
                            thCol4.innerHTML = columnNames[4];

                            const thCol5 = cTag('th', {'width': "8%"});
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
    
    //=======sessionStorage =========//
    let list_filters = '';
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    const sdata_type = 'All', smanufacturer_id = 'All', scategory_id = 'All'/* , keyword_search = '' */;
    
    checkAndSetSessionData('sdata_type', sdata_type, list_filters);
    checkAndSetSessionData('smanufacturer_id', smanufacturer_id, list_filters);
    checkAndSetSessionData('scategory_id', scategory_id, list_filters);

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
    }
    document.getElementById("keyword_search").value = keyword_search;

    addCustomeEventListener('filter',filter_Livestocks_lists);
    addCustomeEventListener('loadTable',loadTableRows_Livestocks_lists);
    filter_Livestocks_lists(true);
}

async function filter_Livestocks_view(){
    let page = 1;
	document.getElementById("page").value = page;

	const jsonData = {};
	jsonData['sproduct_id'] = document.getElementById("table_idValue").value;
    const shistory_type = document.getElementById("shistory_type");
	jsonData['shistory_type'] = shistory_type.value;
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

async function  loadTableRows_Livestocks_view(){
	const jsonData = {};
	jsonData['sproduct_id'] = document.getElementById("table_idValue").value;
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

async function  AJ_view_MoreInfo(){
    
	const product_id = document.getElementById("table_idValue").value;
	const jsonData = {};
	jsonData['product_id'] = product_id;

    const url = '/'+segment1+'/AJ_view_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        document.getElementById("defaultImageSRC").value = data.defaultImageSRC;
        document.getElementById("productImage").setAttribute('src', data.prodImgUrl);

        if(document.querySelector("#products_picture div").classList.contains('currentPicture')){
			document.querySelectorAll('.currentPicture').forEach(oneRowObj=>{
				oneRowObj.addEventListener('mouseenter', e => {
					let picturepath = e.target.querySelector('img').getAttribute('src');
					let defaultImageSRC = document.getElementById("defaultImageSRC").value;
					if(picturepath !==defaultImageSRC){
						let deletedicon = cTag('div', {class: "deletedicon"});
						deletedicon.addEventListener('click', function(){AJremove_Picture(picturepath, 'products')});
						e.target.append(deletedicon);
					}
				});

				oneRowObj.addEventListener('mouseleave',function(){
					if(oneRowObj.querySelector(".deletedicon")){
						oneRowObj.querySelectorAll('.deletedicon').forEach(oneRowObj=>{
							oneRowObj.remove();
						});
					}
				});
			});
		}

        let span, option, td, pTag, requiredFields, inputField, errorSpan, p;
        const viewBasicInfo = document.getElementById("viewBasicInfo");
        viewBasicInfo.innerHTML = '';
            if(data.product_publish ===0){
                const statusHeader = cTag('h1');
                statusHeader.innerHTML = Translate('Status: Archived');
                viewBasicInfo.appendChild(statusHeader);
            }
            const productHeader = cTag('h3');
            productHeader.innerHTML = data.product_name;
        viewBasicInfo.appendChild(productHeader);

        const viewLeft = cTag('div', {class: "customInfoGrid columnSM6", align:'left', 'style': "border-right: 1px solid #CCC;"});
               

            // const arrivalDateLabel = cTag('label');
            // arrivalDateLabel.innerHTML = Translate('Arrival Date')+' : ';
            //     const arrivalDateValue = cTag('span');
            //     arrivalDateValue.innerHTML = DBDateToViewDate(data.arrival_date);
            //     viewLeft.append(arrivalDateLabel, arrivalDateValue);

            // const birthlDateLabel = cTag('label');
            // birthDateLabel.innerHTML = Translate('Arrival Date')+' : ';
            //     const birthDateValue = cTag('span');
            //     birthDateValue.innerHTML = DBDateToViewDate(data.birth_date);
            //     viewLeft.append(birthDateLabel, birthDateValue);

            viewBasicInfo.appendChild(viewLeft);    

            let productInfoDiv = cTag('div', {class: "flex", 'style': "padding: 5px 0px;"});
                let categoryLabel = cTag('label');
                categoryLabel.innerHTML = Translate('Category')+' : ';
                let categorySpan = cTag('b', {'style': "padding: 0px 10px; color: #969595;"});
                categorySpan.innerHTML = data.category_name+' ';
            productInfoDiv.append(categoryLabel, categorySpan);
                let productLabel = cTag('label');
                productLabel.innerHTML = Translate('Livestock Type')+' : ';
                let productTypeSpan = cTag('b', {'style': "padding: 0px 10px; color: #969595;"});
                productTypeSpan.innerHTML = data.product_type+' ';
            productInfoDiv.append(productLabel, productTypeSpan);
        viewBasicInfo.appendChild(productInfoDiv);
            
            let skuInfoDiv = cTag('div', {class: "flex", 'style': "padding: 5px 0px;"});
                let skuInfoLabel = cTag('label');
                skuInfoLabel.innerHTML = Translate('SKU/Barcode')+' : ';
            skuInfoDiv.appendChild(skuInfoLabel);
                let skuSpan = cTag('b', {'style': "padding: 0px 10px; color: #969595;"});
                skuSpan.innerHTML = data.sku+' ';
            skuInfoDiv.appendChild(skuSpan);
        viewBasicInfo.appendChild(skuInfoDiv);
        
        if(data.manage_inventory_count>0 && data.product_type !== 'Labor/Services'){
            let onPODiv = cTag('div', {class: "flex", 'style': "align-items: center; padding: 5px 0px;"});
                let onPOLabel = cTag('label');
                onPOLabel.innerHTML = Translate('Need/Have/OnPO')+' : ';
                let haveOnSpan = cTag('span', {'style': "padding: 0px 10px; color: #969595; font-weight: bold;"});
                haveOnSpan.appendChild(NeedHaveOnPO(data.NeedHaveOnPO,product_id));
            onPODiv.append(onPOLabel, haveOnSpan);
            
           viewBasicInfo.appendChild(onPODiv);
            

                let stockDiv = cTag('div', {class: "flex", 'style': "padding: 5px 0px;"});
                    let stockLabel = cTag('label');
                    stockLabel.innerHTML = Translate('Minimum Stock')+' : ';
                    let inventorySpan = cTag('b', {'style': "padding: 0px 10px; color: #969595;"});
                    inventorySpan.innerHTML = data.low_inventory_alert+' \u2003 ';
                stockDiv.append(stockLabel, inventorySpan);
                
            viewBasicInfo.appendChild(stockDiv);
        }
        else if(data.product_type === 'Standard' && data.manage_inventory_count>0){
            let countDiv = cTag('div', {class: "flex", 'style': "padding: 5px 0px;"});
                let countLabel = cTag('label');
                countLabel.innerHTML = Translate('Count Inventory')+' : ';
            countDiv.appendChild(countLabel);
                let countSpan = cTag('span', {'style': "padding: 0px 10px; color: #969595; font-weight: bold;"});
                countSpan.innerHTML = Translate('No');
            countDiv.appendChild(countSpan);
            viewBasicInfo.appendChild(countDiv);
        }

        let regular_price = currency+'0.00';
        if(data.inventoryObj){
            regular_price = document.createDocumentFragment();
                const priceSpan = cTag('b', {'style': "padding: 0px 10px; color: #969595;"});
                priceSpan.innerHTML = addCurrency(data.regular_price);
            regular_price.appendChild(priceSpan);
        }

                let sellingDiv = cTag('div', {class: "flex", 'style': "padding: 5px 0px;"});
                    let sellingLabel = cTag('label');
                    sellingLabel.innerHTML = Translate('Selling Price')+' : ';
                sellingDiv.append(sellingLabel, regular_price);
            viewBasicInfo.appendChild(sellingDiv);

            let minimumSellingDiv = cTag('div', {class: "flex", 'style': "padding: 5px 0px;"});
                let minimumSellingLabel = cTag('label');
                minimumSellingLabel.innerHTML = Translate('Minimum Selling Price')+' : ';
            minimumSellingDiv.appendChild(minimumSellingLabel);
                let minimumSellingValue = cTag('b', {'style': "padding: 0px 10px; color: #969595;"});
                minimumSellingValue.innerHTML = currency + data.minimum_price;
            minimumSellingDiv.appendChild(minimumSellingValue);
            viewBasicInfo.appendChild(minimumSellingDiv);

            let taxableDiv = cTag('div', {class: "flex", 'style': "padding: 5px 0px;"});
                let taxableLabel = cTag('label');
                taxableLabel.innerHTML = Translate('Taxable')+' : ';
            taxableDiv.appendChild(taxableLabel);
                let taxableSpan = cTag('b', {'style': "padding: 0px 10px; color: #969595;"});
                taxableSpan.innerHTML = data.taxable?'Yes':'No';
            taxableDiv.appendChild(taxableSpan);
            viewBasicInfo.appendChild(taxableDiv);
        let viewLocationInvInfo =  document.getElementById("viewLocationInvInfo");
        viewLocationInvInfo.innerHTML = '';
        let isTab = false;
        if(Object.keys(data.viewCustomInfo).length>0 && Object.keys(data.viewLocationInvInfo).length>0){
            let ticketUl = cTag('ul',{ 'class':`ticketTabber` });
                let ticketLi = cTag('li');
                    let basicTab = cTag('a',{ 'href':`#locationInfoTab` });
                    basicTab.innerHTML = Translate('Location Info');
                ticketLi.appendChild(basicTab);
            ticketUl.appendChild(ticketLi);
                ticketLi = cTag('li');
                    basicTab = cTag('a',{ 'href':`#customInfoTab` });
                    basicTab.innerHTML = Translate('Custom Info');
                ticketLi.appendChild(basicTab);
            ticketUl.appendChild(ticketLi);
            viewLocationInvInfo.appendChild(ticketUl);
            isTab = true;
        }

        if(Object.keys(data.viewLocationInvInfo).length>0){
            let supOpts = [];
            for (let key in data.viewLocationInvInfo) {
                supOpts.push([key+'||'+data.viewLocationInvInfo[key]]);
            }
            supOpts.sort();
            
            let thCol, otherLocationHeadRow, tdCol;
            const otherLocationColumn = cTag('div', {class: "columnXS12", id: "locationInfoTab", 'style': "margin-bottom: 10px;"});
                const noMoreTables = cTag('div');
                    const otherLocationTable = cTag('table', {class: "columnMD12 table-bordered table-striped table-condensed cf listing"});
                        const otherLocationHead = cTag('thead', {class: "cf"});
                            otherLocationHeadRow = cTag('tr');
                                thCol = cTag('th', {'align': "left"});
                                thCol.innerHTML = Translate('Inventory at other location(s)');
                            otherLocationHeadRow.appendChild(thCol);
                        otherLocationHead.appendChild(otherLocationHeadRow);
                    otherLocationTable.appendChild(otherLocationHead);
                        const otherLocationBody = cTag('tbody');
                        let totalQty = parseInt(data.current_inventory);
                        if(isNaN(totalQty)){totalQty = 0;}

                        supOpts.forEach(function (optData){
                            let supOneRow = optData[0].split('||');
                            let invQty = parseInt(supOneRow[1]);
                            if(isNaN(invQty)){invQty = 0;}
                            totalQty += invQty;
                            
                            otherLocationHeadRow = cTag('tr');
                                tdCol = cTag('td', {'data-title': Translate('Location Name'), 'align': "left"});
                                tdCol.innerHTML = supOneRow[0]+' : '+supOneRow[1];
                            otherLocationHeadRow.appendChild(tdCol);
                            otherLocationBody.appendChild(otherLocationHeadRow);
                        });
                    otherLocationTable.appendChild(otherLocationBody);
                        const otherLocationFoot = cTag('tfoot');
                            otherLocationHeadRow = cTag('tr');
                                thCol = cTag('th', {'align': "left"});
                                thCol.innerHTML = Translate('Total Quantity')+' '+totalQty;
                            otherLocationHeadRow.appendChild(thCol);
                        otherLocationFoot.appendChild(otherLocationHeadRow);
                    otherLocationTable.appendChild(otherLocationFoot);
                noMoreTables.appendChild(otherLocationTable);
            otherLocationColumn.appendChild(noMoreTables);
            viewLocationInvInfo.appendChild(otherLocationColumn);
        }
        
        if(Object.keys(data.viewCustomInfo).length>0){
            const customColumn = cTag('div', {class: "columnSM12 customInfoGrid", id: "customInfoTab", 'align': "left"});
                for(const [key, value] of Object.entries(data.viewCustomInfo)) {
                        const customHead = cTag('label');
                        customHead.innerHTML = stripslashes(key)+' : ';
                        const customCol1 = cTag('span');
                        customCol1.innerHTML = value;
                    customColumn.append(customHead, customCol1);
                }
            viewLocationInvInfo.appendChild(customColumn);
        }

        if(isTab) createTabs(viewLocationInvInfo);

        let buttonRow =  document.getElementById("buttonRow");
        buttonRow.innerHTML = '';
        if(data.product_publish===1){	
            						
            let buttonRowDiv = cTag('div', {class: "columnSM12", 'align': "left"});
                let changeButton = cTag('button', {class: "btn editButton", 'style': "margin-right: 15px; margin-bottom: 10px;", id: "picbutton"});
                changeButton.innerHTML = Translate('Change Picture');
                changeButton.addEventListener('click', function (){upload_dialog(Translate('Upload Livestock Picture'), 'products', 'prod_'+product_id+'_');});
            buttonRowDiv.appendChild(changeButton);

                let descriptionButton = cTag('button', {class: "btn editButton", 'style': "margin-right: 15px; margin-bottom: 10px;", id: "desbotton"});
                descriptionButton.innerHTML = Translate('Web Description');
                descriptionButton.addEventListener('click', AJget_LivestocksDescPopup);
            buttonRowDiv.appendChild(descriptionButton);

                let editButton = cTag('button', {class: "btn editButton", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Edit')});
                editButton.innerHTML = Translate('Edit');
                if(data.prodPer===0) {
                    editButton.addEventListener('click', function (){noPermissionWarning('Livestock');});
                }
                else{
                    editButton.addEventListener('click', function (){AJget_LivestocksPopup('Livestocks', product_id, 0);});
                }
            buttonRowDiv.appendChild(editButton);

                let conditionalButton = cTag('button', {class: "btn editButton", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Conditional Pricing')});
                conditionalButton.innerHTML = Translate('Conditional Pricing');
                conditionalButton.addEventListener('click', function (){AJget_LivestocksPricePopup(0, product_id);});
            buttonRowDiv.appendChild(conditionalButton);

                let similarButton = cTag('button', {class: "btn createButton", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Create Similar Livestock')});
                similarButton.innerHTML = Translate('Create Similar Livestock');
                if(data.prodPer2===0) {
                    similarButton.addEventListener('click', function (){noPermissionWarning('Livestock');});
                }
                else{
                    similarButton.addEventListener('click', function (){AJget_LivestocksPopup('Livestocks', product_id, 1);});
                }
            buttonRowDiv.appendChild(similarButton);

                    let archiveButton = cTag('button', {class: "btn archiveButton", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Archive')});
                    archiveButton.innerHTML = Translate('Archive');
                    if(data.allowed.length===0||(!Array.isArray(data.allowed) && !data.allowed['5'].includes('cnap'))) archiveButton.addEventListener('click',()=>archiveLivestock(data.sku,data.NeedHaveOnPO));
                    else archiveButton.addEventListener('click', function (){noPermissionWarning('Livestock')});
                buttonRowDiv.appendChild(archiveButton);
            buttonRow.appendChild(buttonRowDiv);
        }
        else{
            buttonRow.classList.add('flexStartRow');
            buttonRow.appendChild(cTag('div', {class: "columnSM3"}));
                let buttonRowDiv = cTag('div', {class: "columnSM5"});
                    let changeButton = cTag('button', {class: "btn bgcoolblue", 'style': "margin-right: 15px; margin-bottom: 10px;"});
                    changeButton.innerHTML = Translate('Unarchive');
                    changeButton.addEventListener('click',()=>unarchiveLivestock(data.sku));
                buttonRowDiv.appendChild(changeButton);
            buttonRow.appendChild(buttonRowDiv);
        }

        if(data.product_type ==='Standard' && data.manage_inventory_count>0 && data.aveCostPermission===1){
            let productCostBody = document.getElementById("productAveCost");
            productCostBody.innerHTML = '';

            let tableData = data.productAveCost;
            if(tableData.length){
                let dateTimeArray, date, time, oneTDObj;
                p = 0;
                let totalRowsCount = tableData.length;
                let prodInv = parseFloat(data.current_inventory);
                tableData.forEach(oneRow => {     
                    let cls = oneRow[0];
                    let newInvQty = parseFloat(oneRow[8]);
                    p++;
                    
                    if(p===totalRowsCount && prodInv !== newInvQty){cls = 'bgyellow';}
                            
                    let i=0;
                    let tr = cTag('tr');
                    
                    dateTimeArray = DBDateToViewDate(oneRow[1], 1, 1);
                    
                    date = dateTimeArray[0];
                    td = cTag('td');
                    oneTDObj = avgCostFieldAttributes[0];
                    for(const [key, value] of Object.entries(oneTDObj)) {
                        let attName = key;
                        if(attName !=='' && attName==='datatitle')
                            attName = attName.replace('datatitle', 'data-title');
                        td.setAttribute(attName, value);
                    }
                    td.innerHTML = date;
                    tr.appendChild(td);

                    time = dateTimeArray[1];
                    td = cTag('td');
                    oneTDObj = avgCostFieldAttributes[1];
                    for(const [key, value] of Object.entries(oneTDObj)) {
                        let attName = key;
                        if(attName !=='' && attName==='datatitle')
                            attName = attName.replace('datatitle', 'data-title');
                        td.setAttribute(attName, value);
                    }
                    td.innerHTML = time;
                    tr.appendChild(td);

                    oneRow.forEach(tdvalue => {
                        if(i>1){
                            let td = cTag('td');
                            if(cls !=='' && cls==='bgyellow'){
                                td.setAttribute('class', 'bgyellow');
                            }
                    
                            let oneTDObj = avgCostFieldAttributes[i];
                            for(const [key, value] of Object.entries(oneTDObj)) {
                                let attName = key;
                                if(attName !=='' && attName==='datatitle')
                                    attName = attName.replace('datatitle', 'data-title');
                                td.setAttribute(attName, value);
                            }
                            td.innerHTML = tdvalue;
                            tr.appendChild(td);
                        }
                        i++;
                    });
                    productCostBody.appendChild(tr);
                });
            }
        }
        else{
            document.getElementById('calAveCost').innerHTML = '';
        }
                    
        let desbotton;
        if(document.getElementById("desbotton")){
            if(data.isDesYes===0){desbotton = Translate('Add New Description');}
            else{desbotton = Translate('Change Description');}
            let desbottonBtn = document.getElementById("desbotton");
            desbottonBtn.title = desbotton;
            desbottonBtn.innerHTML = desbotton;
        }

        if(data.productPrices.length>0){
            if(document.querySelector("#productPricesInfo").style.display === 'none'){
                document.querySelector("#productPricesInfo").style.display = '';
            }
            
            let productPriceBody = document.getElementById("productPrices");
            productPriceBody.innerHTML = '';

            let tableData = data.productPrices;
            if(tableData.length){                    
                tableData.forEach(oneRow => { 
                    let i=0;
                    let tr = cTag('tr');
                    let addBr = '';
                    if(OS!=='unknown') addBr = '</br>&nbsp;'
                    oneRow.forEach(tdvalue => {
                        if(i>0){                                
                            let td = cTag('td');
                            let oneTDObj = priceFieldAttributes[i-1];
                            for(const [key, value] of Object.entries(oneTDObj)) {
                                let attName = key;
                                if(attName !=='' && attName==='datatitle')
                                    attName = attName.replace('datatitle', 'data-title');
                                td.setAttribute(attName, value);
                            }

                            if(i===3) tdvalue = tdvalue>0?`${oneRow[5]}${Translate('% Off')}${addBr}`:`${Translate('Fixed')} ${addCurrency(oneRow[5])}${addBr}`;
                            else if(i===4 && oneRow[i] !=''){
                                tdvalue = `${Translate('from')} ${DBDateRangeToViewDate(oneRow[i], 1).replace(' - ', ' '+Translate('To')+' ')}`;
                            }

                            if(i===5){
                                    const editIcon = cTag('i',{ 'class':`fa fa-edit`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Edit Price') });
                                    editIcon.addEventListener('click',()=>AJget_LivestocksPricePopup(oneRow[0], product_id))
                                td.appendChild(editIcon);
                                td.append('  ');
                                    const trashIcon = cTag('i',{ 'class':`fa fa-trash-o`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove Price') });
                                    trashIcon.addEventListener('click',()=>AJremove_tableRow('product_prices', oneRow[0], 'Livestock Prices', `/Livestocks/view/${product_id}`))
                                td.appendChild(trashIcon);
                            }
                            else td.innerHTML = tdvalue;
                            tr.appendChild(td);
                        }
                        i++;
                    });
                    productPriceBody.appendChild(tr);
                });
            }
        }
        else{
            if(document.querySelector("#productPricesInfo").style.display !== 'none'){
                document.querySelector("#productPricesInfo").style.display = 'none';
            }
        }
        
        const shistory_type = document.getElementById("shistory_type");
        const shistory_typeVal = shistory_type.value;
        shistory_type.innerHTML = '';
        const allOption = document.createElement('option');
        allOption.setAttribute('value', '');
        allOption.innerHTML = Translate('All Activities');
        shistory_type.appendChild(allOption);
        setOptions(shistory_type, data.actFeeTitOpt, 0, 1);
        document.getElementById("shistory_type").value = shistory_typeVal;

        filter_Livestocks_view();
    }
}

function view(){
    
    let product_id = parseInt(segment3);
    if(product_id==='' || isNaN(product_id)){product_id = 0;}    
    
    let showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Livestock Information')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page displays the information of Livestocks')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);

            const listLivestockLink = cTag('a', {'href': "/Livestocks/lists", class: "btn defaultButton", title: Translate('List Livestocks')});
            listLivestockLink.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('List Livestocks'));
        titleRow.appendChild(listLivestockLink);
    showTableData.appendChild(titleRow);

        const productViewColumn = cTag('div', {class: "columnSM12"});
            const supplierHeader = cTag('header', {class: "imageContainer flexSpaBetRow", 'style': "padding: 5px 15px;"});
                const imageColumn = cTag('div', {class: "columnSM3", id: "products_picture"});
                    const currentImage = cTag('div', {class: "currentPicture"});
                        const imageSegment = cTag('img', {class: "img-responsive", id: "productImage", 'alt': Translate('Livestock Information'), 'src': "/assets/images/default.png"});
                    currentImage.appendChild(imageSegment);
                imageColumn.appendChild(currentImage);
            supplierHeader.appendChild(imageColumn);

                const imageContentColumn = cTag('div', {class: "columnSM5"});
                    let imageContentDiv = cTag('div', {class: "image_content", 'style': "text-align: left;", id: "viewBasicInfo"});
                imageContentColumn.appendChild(imageContentDiv);
            supplierHeader.appendChild(imageContentColumn);

                let locationInfoColumn = cTag('div', {class: "columnSM4", 'align': "left", 'style': "border-left: 1px solid #CCC;", id: "viewLocationInvInfo"});
            supplierHeader.appendChild(locationInfoColumn);

                let allButtonName = cTag('div', {class: "columnSM12", id: "buttonRow"});
            supplierHeader.appendChild(allButtonName);
        productViewColumn.appendChild(supplierHeader);
    showTableData.appendChild(productViewColumn);

        const productContentColumn = cTag('div', {class: "columnSM12"});
            let hiddenProperties = {
                    'note_forTable': 'product' ,
                    'table_idValue': product_id ,
                    'defaultImageSRC': '' ,
            }
        productContentColumn.appendChild(historyTable(Translate('Livestock History'),hiddenProperties));

            //===========Calculated average costs============//
            const averageCostWidget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;", id: "calAveCost"});
                const averageCostwidgetHeader = cTag('div', {class: "cardHeader"});
                    const averageCostColumn = cTag('div', {class: "columnSM4", 'style': "margin: 0;"});
                        const averageCostHeader = cTag('h3');
                        averageCostHeader.innerHTML = Translate('Calculated average costs');
                    averageCostColumn.appendChild(averageCostHeader);
                averageCostwidgetHeader.appendChild(averageCostColumn);
            averageCostWidget.appendChild(averageCostwidgetHeader);

                const averageCostDiv = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
                    const averageCostCol = cTag('div', {class: "columnXS12", 'style': "margin: 0; padding: 0;"});
                        const noMoreTables = cTag('div', {id: "no-more-tables"});
                            const averageTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing", 'style': "margin-top: 2px;"});
                                const averageHead = cTag('thead', {class: "cf"});
                                    const column2Names = avgCostFieldAttributes.map(colObj=>(colObj.datatitle));
                                    const averageHeadRow = cTag('tr');
                                        const th2Col0 = cTag('th', {'style': "width: 80px;"});
                                        th2Col0.innerHTML = column2Names[0];

                                        const th2Col1 = cTag('th', {'style': "width: 75px;"});
                                        th2Col1.innerHTML = column2Names[1];

                                        const th2Col2 = cTag('th');
                                        th2Col2.innerHTML = column2Names[2];

                                        const th2Col3 = cTag('th', {'width': "7%"});
                                        th2Col3.innerHTML = column2Names[3];

                                        const th2Col4 = cTag('th', {'width': "6%"});
                                        th2Col4.innerHTML = column2Names[4];

                                        const th2Col5 = cTag('th', {'width': "8%"});
                                        th2Col5.innerHTML = column2Names[5];

                                        const th2Col6 = cTag('th', {'width': "8%"});
                                        th2Col6.innerHTML = column2Names[6];

                                        const th2Col7 = cTag('th', {'width': "8%"});
                                        th2Col7.innerHTML = column2Names[7];

                                        const th2Col8 = cTag('th', {'width': "8%"});
                                        th2Col8.innerHTML = column2Names[8];

                                        const th2Col9 = cTag('th', {'width': "8%"});
                                        th2Col9.innerHTML = column2Names[9];
                                    averageHeadRow.append(th2Col0, th2Col1, th2Col2, th2Col3, th2Col4, th2Col5, th2Col6, th2Col7, th2Col8, th2Col9); 
                                averageHead.appendChild(averageHeadRow);
                            averageTable.appendChild(averageHead);
                                const averageBody = cTag('tbody', {id: "productAveCost"});
                            averageTable.appendChild(averageBody);
                        noMoreTables.appendChild(averageTable);
                    averageCostCol.appendChild(noMoreTables);
                        let costButton = cTag('button', {class: "btn defaultButton", 'style': "margin: 6px;"});
                        let accounts_id = document.getElementById("accounts_id").value;
                        costButton.append('Update Average Cost');
                        costButton.addEventListener('click', function (){updateProdAveCost(accounts_id, product_id);});
                    averageCostCol.appendChild(costButton);
                        let inventoryButton = cTag('button', {class: "btn defaultButton", 'style': "margin: 6px;"});
                        inventoryButton.append('Update Inventory');
                        inventoryButton.addEventListener('click', function (){updateProdInventory(accounts_id, product_id);});
                    averageCostCol.appendChild(inventoryButton);
                averageCostDiv.appendChild(averageCostCol);
            averageCostWidget.appendChild(averageCostDiv);
        productContentColumn.appendChild(averageCostWidget);
        
        //===========Livestock price information============//
            const productPriceWidget = cTag('div', {class: "cardContainer", 'style': "display:none;margin-bottom: 10px;", id: "productPricesInfo"});
                const productPriceWidgetHeader = cTag('div', {class: "cardHeader"});
                    const productPriceTitle = cTag('h3', {'style': "padding-left: 6px;"});
                    productPriceTitle.innerHTML = Translate('Livestock price information');
                productPriceWidgetHeader.appendChild(productPriceTitle);
            productPriceWidget.appendChild(productPriceWidgetHeader);

                const productPrice = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
                    const productPriceColumn = cTag('div', {class: "columnXS12", 'style': "margin: 0; padding: 0;"});
                        const noMoreTableDiv = cTag('div', {id: "no-more-tables"});
                            const productPriceTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing", 'style': "margin: 0;"});
                                const productPriceHead  = cTag('thead', {class: "cf"});
                                    const column3Names = priceFieldAttributes.map(colObj=>(colObj.datatitle));
                                    const productPriceRow = cTag('tr');
                                        const th3Col0 = cTag('th', {'width': "25%"});
                                        th3Col0.innerHTML = column3Names[0];

                                        const th3Col1 = cTag('th');
                                        th3Col1.innerHTML = column3Names[1];

                                        const th3Col2 = cTag('th', {'width': "20%"});
                                        th3Col2.innerHTML = column3Names[2];

                                        const th3Col3 = cTag('th', {'width': "25%"});
                                        th3Col3.innerHTML = column3Names[3];

                                        const th3Col4 = cTag('th', {'width': "6%"});
                                        th3Col4.innerHTML = column3Names[4];
                                    productPriceRow.append(th3Col0, th3Col1, th3Col2, th3Col3, th3Col4);
                                productPriceHead.appendChild(productPriceRow);
                            productPriceTable.appendChild(productPriceHead);
                                const productPriceBody = cTag('tbody', {id: "productPrices"});
                            productPriceTable.appendChild(productPriceBody);
                        noMoreTableDiv.appendChild(productPriceTable);
                    productPriceColumn.appendChild(noMoreTableDiv);            
                productPrice.appendChild(productPriceColumn);
            productPriceWidget.appendChild(productPrice);
        productContentColumn.appendChild(productPriceWidget);
    showTableData.appendChild(productContentColumn);   
   

    //=======sessionStorage =========//
    let list_filters = '';
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}

    let shistory_type = '';
    checkAndSetSessionData('shistory_type', shistory_type, list_filters);

    addCustomeEventListener('filter',filter_Livestocks_view);
    addCustomeEventListener('loadTable',loadTableRows_Livestocks_view);
    AJ_view_MoreInfo();
}

function addLivestockPO(){
    let product_type = document.getElementById("po_product_type").value;
    if(document.querySelector("#addPOForm").style.display === 'none'){
        document.querySelector("#addPOForm").style.display = '';
    }
	
    if(product_type==='Standard'){
        if(document.querySelector("#bulkimeiRow").style.display !== 'none'){
            document.querySelector("#bulkimeiRow").style.display = 'none';
        }
		document.getElementById("ordered_qty").readOnly = false;
	}
	else{
        if(document.querySelector("#bulkimeiRow").style.display === 'none'){
            document.querySelector("#bulkimeiRow").style.display = '';
        }
		document.getElementById("ordered_qty").readOnly = true;
	}
}

function calculateCostTotal(){
	let cost = parseFloat(document.getElementById("cost").value);
	if(cost==='' || isNaN(cost)){cost = 0;}
	
	let ordered_qty = parseInt(document.getElementById("ordered_qty").value);
	if(ordered_qty==='' || isNaN(ordered_qty)){ordered_qty = 0;}
	
	let ordered_qty_total = calculate('mul',cost,ordered_qty,2);
	document.getElementById("ordered_qty_total").value = ordered_qty_total;
    document.getElementById("ordered_qty_total_str").innerHTML = 'Total : '+addCurrency(ordered_qty_total);
}

function calculateIMEIQty(event = false){
	if(event){
        if(event.which===13){
            let IMEIs = this.value.split('\n').map(IMEI=>IMEI.replace(/-/g, ' ').trim().replace(/ /g,'-'))
            this.value = IMEIs.join('\n');
        }
        let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#\n";
        let bulkimei = event.target.value.toUpperCase().replace(' ', '-');
        let IsNumber = true;
        let Char, i;
        let newIMEI = '';
        for (i = 0; i < bulkimei.length && IsNumber === true; i++){ 
            Char = bulkimei.charAt(i);
            if (ValidChars.indexOf(Char) === -1){}
		    else{
                newIMEI = newIMEI+Char;
            }
        }
        
        if(bulkimei.length> newIMEI.length || event.target.value !== newIMEI){
            event.target.value = bulkimei = newIMEI;
        }
        
        let bulkimeiArray = bulkimei.split("\n");
        let ordered_qty = 0;
        bulkimeiArray.forEach(function (oneIMEI){
            if(oneIMEI.length>0 ){
                ordered_qty++;
            }
        });

        document.getElementById("ordered_qty").value = ordered_qty;
        calculateCostTotal();
    }
}

async function AJsave_PO(event){
    if(event){ event.preventDefault();}

    let cost = document.getElementById('cost');
    if (!cost.valid()) return;

	let ordered_qty = document.getElementById("ordered_qty");
    if (!ordered_qty.valid()) return;
	
	let errorid = document.getElementById("errmsg_ordered_qty");
	errorid.innerHTML = '';
	
	let product_type = document.getElementById("po_product_type").value;
    let bulkimei = document.getElementById("bulkimei").value;
    errorid = document.getElementById("errmsg_bulkimei");
    errorid.innerHTML = '';
    if(bulkimei===''){
        errorid.innerHTML = Translate('Missing IMEI Number');
        document.getElementById("bulkimei").focus();
        return false;
    }
	
    let saveBtn = document.getElementById("btnAddRow");
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;
	
    const jsonData = serialize('#frmPO');
    
    const url = '/'+segment1+'/AJsave_PO';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        if(data.savemsg === 'saved'){
            if(!document.querySelector("#frmPO").style.display !== 'none'){
                document.querySelector("#frmPO").style.display = 'none';
            }

			if(document.getElementById("barcodePrint").checked === true && data.po_number>0){
				printbyurl('/Purchase_orders/prints/barcode/'+data.po_number);
			}
			location.reload();															
		}
		else{
            let saveBtn = document.getElementById("btnAddRow");
			saveBtn.innerHTML = Translate('Save');
			saveBtn.disabled = false;

            if(data.savemsg==='smallerIMEI') errorid.innerHTML = `${Translate('Total')} ${data.message} ${Translate('IMEI numbers smaller than 2 characters found.')}`;
            if(data.savemsg==='longerIMEI') errorid.innerHTML = `${Translate('Total')} ${data.message} ${Translate('IMEI numbers longer than 20 characters found.')}`;
            if(data.savemsg==='duplicateIMEI') errorid.innerHTML = `${Translate('Total')} ${data.message} ${Translate('duplicate IMEI numbers found')}`;
            if(data.savemsg==='noIMEI') errorid.innerHTML = Translate('No IMEI Number saved');
            if(data.savemsg==='errorAddPO') errorid.innerHTML = Translate('Error occured while adding PO information! Please try again.');
            if(data.savemsg==='noInventory') errorid.innerHTML = Translate('There is no inventory data found for this product');
		}
        let saveBtn = document.getElementById("btnAddRow");
		saveBtn.innerHTML = Translate('Save');
		saveBtn.disabled = false;
    }

	return false;
}


async function AJsave_LivestocksDesc(hidePopup){
	const oField = document.getElementById('description');
	actionBtnClick('.btnmodel', Translate('Saving'), 1);

	const jsonData = serialize('#frmLivestockDesc');
    const url = '/'+segment1+'/AJsave_LivestocksDesc';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        if(data.savemsg !=='update-success'){
			showTopMessage('error_msg', Translate('Error occured while updating product description information! Please try again.'));
		}
		else{
            let desbotton, description;
			hidePopup();
			if(oField.value===''){
				desbotton = Translate('Add New Description');
				description = '';
			}
			else{
				desbotton = Translate('Change Description');
                description = document.createElement('textarea');
				description.innerHTML = '';
					let pTag= cTag('p');
					pTag.innerHTML = Translate('Description');
						let span= cTag('span');
						span.innerHTML = oField.value;
					pTag.appendChild(span);
				description.appendChild(pTag);
			}
            let desbottonBtn = document.getElementById("desbotton");
            desbottonBtn.title = desbotton;
            desbottonBtn.innerHTML = desbotton;            
		}
        actionBtnClick('.btnmodel', Translate('Save'), 0);
    }
	return false;
}

async function AJget_LivestocksPricePopup(product_prices_id, product_id){
	const jsonData = {};
	jsonData['product_prices_id'] = product_prices_id;
	jsonData['product_id'] = product_id;

    const url = '/'+segment1+'/AJget_LivestocksPricePopup';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let requiredField, inputField;
        let currencyoption = currency;

        let formDialog = cTag('div');
            /* let divErrorMsg = cTag('div', {id: "error_product_prices", class: "errormsg"});
        formDialog.appendChild(divErrorMsg); */
            const productsPriceForm = cTag('form', {'action': "#", name: "frmproduct_prices", id: "frmproduct_prices", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                const productsPriceColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                    const priceTypeRow = cTag('div', {class: "flex"});
                        const priceTypeColumn = cTag('div', {class: "columnSM4", });
                            const priceTypeLabel = cTag('label', {'for': "price_type"});
                            priceTypeLabel.innerHTML = Translate('Price Type');
                                requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            priceTypeLabel.appendChild(requiredField);
                        priceTypeColumn.appendChild(priceTypeLabel);
                    priceTypeRow.appendChild(priceTypeColumn);
                        const productsPriceDropDown = cTag('div', {class: "columnSM8"});
                            let selectPriceType = cTag('select', {'required': "required", class: "form-control", name: "price_type", id: "price_type"});
                            selectPriceType.addEventListener('change', showTypeMatch);           
                                let priceTypeOption = cTag('option', {'value': ""});
                                priceTypeOption.innerHTML = Translate('Select Price Type');
                            selectPriceType.appendChild(priceTypeOption);
                            setOptions(selectPriceType, data.price_typeOptions, 0, 1);
                        productsPriceDropDown.appendChild(selectPriceType);
                        productsPriceDropDown.appendChild(cTag('span', {id: "error_product_prices", class: "errormsg"}));
                    priceTypeRow.appendChild(productsPriceDropDown);
                productsPriceColumn.appendChild(priceTypeRow);

                    const typeMatchRow = cTag('div', {class: "flex", id: "type_matchrow"});
                        const typeMatchColumn = cTag('div', {class: "columnSM4"});
                            const typeMatchLabel = cTag('label', {'for': "customer_type"});
                            typeMatchLabel.innerHTML = Translate('Type Match');
                                requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            typeMatchLabel.appendChild(requiredField);
                        typeMatchColumn.appendChild(typeMatchLabel);
                    typeMatchRow.appendChild(typeMatchColumn);
                        const typeMatchDropDown = cTag('div', {class: "columnSM8"});
                            let selectTypeMatch = cTag('select', {'required': "required", class: "form-control", id: "customer_type", name: "customer_type"});                    
                                let typeMatchOption = cTag('option', {'value': ""});
                                typeMatchOption.innerHTML = Translate('Select Customer Type');
                            selectTypeMatch.appendChild(typeMatchOption);
                            setOptions(selectTypeMatch, data.customer_typeOptions, 0, 1);
                        typeMatchDropDown.appendChild(selectTypeMatch);
                            inputField = cTag('input', {'type': "text", 'required': "required", 'data-min': '2', 'data-max': '99', 'data-format': "d", class: "form-control",style:'display:none', name: "type_match", id: "type_match", 'value': data.type_match, 'maxlength': 10, 'placeholder': Translate('Quantity')});
                            controllNumericField(inputField, '#error_type');
                        typeMatchDropDown.appendChild(cTag('span', {id: "error_type", class: "errormsg"}));
                        typeMatchDropDown.appendChild(inputField);
                    typeMatchRow.appendChild(typeMatchDropDown);
                productsPriceColumn.appendChild(typeMatchRow);

                    const percentRow = cTag('div', {class: "flex"});
                        const percentColumn = cTag('div', {class: "columnSM4"});
                            const percentLabel = cTag('label', {'for': "is_percent"});
                            percentLabel.innerHTML = Translate('Fixed Price/ Percent Off');
                                requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            percentLabel.appendChild(requiredField);
                        percentColumn.appendChild(percentLabel);
                    percentRow.appendChild(percentColumn);
                        const percentDropDown = cTag('div', {class: "columnSM8"});
                            let selectPercent = cTag('select', {'required': "required", id: "is_percent", name: "is_percent", class: "form-control"});
                            selectPercent.addEventListener('change', showpricelabel);
                                let percentOption = cTag('option', {'value': 1});
                                percentOption.innerHTML = '%';
                            selectPercent.appendChild(percentOption);
                                let currencyOption = cTag('option', {'value': 0});
                                currencyOption.innerHTML = currencyoption;
                            selectPercent.appendChild(currencyOption);
                        percentDropDown.appendChild(selectPercent);
                    percentRow.appendChild(percentDropDown);
                productsPriceColumn.appendChild(percentRow);
                                        
                    const percentageOffRow = cTag('div', {class: "flex"});
                        const percentageOffColumn = cTag('div', {class: "columnSM4"});
                            let percentageOffLabel = cTag('label', {'for': "price", id: "pricelabel"});
                                requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            percentageOffLabel.appendChild(requiredField);
                        percentageOffColumn.appendChild(percentageOffLabel);
                    percentageOffRow.appendChild(percentageOffColumn);
                        const percentageOffField = cTag('div', {class: "columnSM8"});
                            inputField = cTag('input', {'required': "required", id: "price", name: "price", 'type': "text",'data-min':'0','data-max': '99.99', 'data-format':'d.dd', 'value': round(data.price,2), class: "form-control"});
                            controllNumericField(inputField, '#error_price');
                        percentageOffField.appendChild(inputField);
                        percentageOffField.appendChild(cTag('span', {id: "error_price", class: "errormsg"}));
                    percentageOffRow.appendChild(percentageOffField);
                productsPriceColumn.appendChild(percentageOffRow);

                    const startDateRow = cTag('div', {class: "flex"});
                        const startDateColumn = cTag('div', {class: "columnSM4"});
                            const startDateLabel = cTag('label', {'for': "start_date"});
                            startDateLabel.innerHTML = Translate('Start Date');
                        startDateColumn.appendChild(startDateLabel);
                    startDateRow.appendChild(startDateColumn);
                        const startDateField = cTag('div', {class: "columnSM8"});
                            inputField = cTag('input', {'required': "required", 'type': "text", class: "form-control", name: "start_date", id: "start_date", 'value': DBDateToViewDate(data.start_date),'maxlength': 10});
                            checkDateOnBlur(inputField,'#error_product_prices','Invalid Start Date');
                        startDateField.appendChild(inputField);
                    startDateRow.appendChild(startDateField);
                productsPriceColumn.appendChild(startDateRow);

                    const endDateRow = cTag('div', {class: "flex"});
                        const endDateColumn = cTag('div', {class: "columnSM4"});
                            const endDateLabel = cTag('label', {'for': "end_date"});
                            endDateLabel.innerHTML = Translate('End Date');
                        endDateColumn.appendChild(endDateLabel);
                    endDateRow.appendChild(endDateColumn);
                        const endDateField = cTag('div', {class: "columnSM8"});
                            inputField = cTag('input', {'required': "required", 'type': "text", class: "form-control", name: "end_date", id: "end_date", 'value': DBDateToViewDate(data.end_date),'maxlength': 10});
                            checkDateOnBlur(inputField,'#error_product_prices','Invalid End Date');
                        endDateField.appendChild(inputField);
                    endDateRow.appendChild(endDateField);
                productsPriceColumn.appendChild(endDateRow);
            productsPriceForm.appendChild(productsPriceColumn);

                inputField = cTag('input', {'type': "hidden", name: "product_prices_id", 'value': product_prices_id});
            productsPriceForm.appendChild(inputField);
                inputField = cTag('input', {'type': "hidden", name: "product_id", 'value': product_id});
            productsPriceForm.appendChild(inputField);
        formDialog.appendChild(productsPriceForm);
        
        popup_dialog1000(Translate('Livestock price information'),formDialog,AJsave_LivestocksPrice);			

        setTimeout(function() {
            document.getElementById("price_type").value = data.price_type;
            
            showTypeMatch();
            if(data.is_percent !==''){
                document.getElementById("is_percent").value = data.is_percent;
            }
            showpricelabel();

            date_picker('#start_date');
            date_picker('#end_date');

            if(data.price_type==='Customer Type'){
                document.getElementById("customer_type").value = data.type_match;
            }
            else{
                document.getElementById("type_match").value = data.type_match;
            }
            document.getElementById("price_type").focus();
        }, 500);
    }
	return true;
}

async function AJsave_LivestocksPrice(hidePopup){
	let errormsg = document.getElementById('error_product_prices');
	let errorType = document.getElementById('error_type');
	let error_price = document.getElementById('error_price');
	errormsg.innerHTML = '';
	errorType.innerHTML = '';
	error_price.innerHTML = '';

    const price_type = document.getElementById("price_type");
    if(price_type.value===''){
        errormsg.innerHTML = Translate('Missing Price Type');
        price_type.focus();
        price_type.classList.add('errorFieldBorder');
        return false;
    }else {
        price_type.classList.remove('errorFieldBorder');
    }

    let customer_type = document.getElementById("customer_type");
    if(price_type.value === 'Customer Type' && customer_type.value===''){
	    errorType.innerHTML = Translate('Missing customer type');
	    customer_type.focus();
        customer_type.classList.add('errorFieldBorder');
	    return false;
    }
    else if(price_type.value === 'Quantity' && (!validateRequiredField(type_match,'#error_product_prices') || !type_match.valid())) return;

    let priceField = document.getElementById("price");
    if(!validateRequiredField(priceField,'#error_price') || !priceField.valid()) return;

	let product_id = document.frmproduct_prices.product_id.value;
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
			
    const jsonData = serialize('#frmproduct_prices');
    
    const url = '/'+segment1+'/AJsave_LivestocksPrice';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        if(data.savemsg ===''){
            hidePopup();
			window.location = '/Livestocks/view/'+product_id;						
		}
		else{						
			if(data.savemsg==='priceInfoExist') document.getElementById('error_product_prices').innerHTML = Translate('This product price info already exists. Try again with different field values.');
			if(data.savemsg==='errorAddingPrice') document.getElementById('error_product_prices').innerHTML = Translate('Error occured while adding new product prices! Please try again.');
		}
        actionBtnClick('.btnmodel', Translate('Save'), 0);
    }
	return false;
}

function showTypeMatch(){
	let price_type = document.getElementById("price_type").value;
    if(document.getElementById("type_matchrow").style.display !== 'none'){document.getElementById("type_matchrow").style.display = 'none';}
	if(price_type==='Customer Type'){
        if(document.getElementById("type_matchrow").style.display === 'none'){document.getElementById("type_matchrow").style.display = '';}
        if(document.getElementById("type_match").style.display !== 'none'){document.getElementById("type_match").style.display = 'none';}
        if(document.getElementById("customer_type").style.display === 'none'){document.getElementById("customer_type").style.display = '';}
	}
	else if(price_type==='Quantity'){
        if(document.getElementById("type_matchrow").style.display === 'none'){document.getElementById("type_matchrow").style.display = '';}
        if(document.getElementById("customer_type").style.display !== 'none'){document.getElementById("customer_type").style.display = 'none';}
        if(document.getElementById("type_match").style.display === 'none'){document.getElementById("type_match").style.display = '';}
	}
}

function showpricelabel(){
	let is_percent = parseInt(document.getElementById("is_percent").value);
    let priceLabel, span;
    let priceField = document.getElementById('price');
    let error_product_prices = document.getElementById('error_product_prices');

	if(is_percent===1){
        priceLabel = document.getElementById("pricelabel");
		priceLabel.innerHTML = Translate('Percentage Off');
			span = cTag('span', {class: "required"});
			span.innerHTML = '*';
		priceLabel.appendChild(span);
        priceField.setAttribute('data-max','99.99');
        if(priceField.value>99.99) error_product_prices.innerHTML = "Percentage can't be > than 99.99";
        if(priceField.value<0) error_product_prices.innerHTML = "Percentage can't be < than 0";

	}
	else{
        priceLabel = document.getElementById("pricelabel");
		priceLabel.innerHTML = Translate('Price');
			span = cTag('span', {class: "required"});
			span.innerHTML = '*';
		priceLabel.appendChild(span);
        priceField.setAttribute('data-max','9999999.99');
        error_product_prices.innerHTML = '';
	}	
}

async function updateProdAveCost(accounts_id, product_id){
    const jsonData = {};
	jsonData['postSubmit'] = 'post';
	
	const url = "/Common/updateProdAveCost/"+accounts_id+'/'+product_id;
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        location.reload();
    }
}

async function updateProdInventory(accounts_id, product_id){
	const jsonData = {};
	jsonData['postSubmit'] = 'post';

	const url = "/Common/updateProdInventory/"+accounts_id+'/'+product_id;
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        location.reload();
    }
}

async function AJget_LivestocksDescPopup(){
    let product_id = document.getElementById("table_idValue").value;

    const jsonData = {};
	jsonData['product_id'] = product_id;

    const url = '/'+segment1+'/AJget_LivestocksDescPopup';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let formDialog = cTag('div');
            let productDescriptionForm = cTag('form', {'action': "#", name: "frmLivestockDesc", id: "frmLivestockDesc", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
            productDescriptionForm.appendChild(wysiwyrEditor('description'));
                let errorSpan = cTag('span', {id: "errmsg_description", class: "errormsg"});
            productDescriptionForm.appendChild(errorSpan);
                let inputField = cTag('input', {'type': "hidden", name: "product_id", id: "product_id", 'value': product_id});
            productDescriptionForm.appendChild(inputField);
        formDialog.appendChild(productDescriptionForm);

        popup_dialog1000(Translate('Website Livestock Description'),formDialog,AJsave_LivestocksDesc);

        setTimeout(function() {			
            let editor = document.getElementById('wysiwyrEditor');
            editor.querySelector("#description").innerHTML = editor.querySelector("#editingArea").contentWindow.document.body.innerHTML = data.description;
            editor.querySelector("#description").focus();
            multiSelectAction('wysiwyrEditorDropdown')
        }, 100);
    }
}

//--------------------------Inventory Adjustment------------------------------
async function adjustInventory(sku){
    const jsonData = {"sku":sku};
    const url = '/'+segment1+'/AJ_showLivestockRow';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        if(data.returnStr ==='Ok'){
            let inputField, errorSpan;
            let formDialog = cTag('div');
                const adjustInventoryForm = cTag('form', {'enctype':"text/plain", 'method':"post", name: "frmproduct", 'action': "#"});
                    let adjustInventoryRow = cTag('div', {class: "flexSpaBetRow", id: "jquerybtnAI_form"});
                        const adjustInventoryColumn = cTag('div', {class: "columnXS12", 'align': "left"});
                            const manufacturRow = cTag('div', {class: "flex"});
                                const manufacturColumn = cTag('div', {class: "columnSM6"});
                                    const manufacturLabel = cTag('label', {'style': "padding-top: 0;"});
                                    manufacturLabel.innerHTML = Translate('Manufacturer Name');
                                manufacturColumn.appendChild(manufacturLabel);
                            manufacturRow.appendChild(manufacturColumn);
                                let manufacturValue = cTag('div', {class: "columnSM6", id: "manufacturer_name"});
                                manufacturValue.innerHTML = data.manufacture;
                            manufacturRow.appendChild(manufacturValue);
                        adjustInventoryColumn.appendChild(manufacturRow);

                            const productRow = cTag('div', {class: "flex"});
                                const productColumn = cTag('div', {class: "columnSM6"});
                                    const productLabel = cTag('label', {'style': "padding-top: 0;"});
                                    productLabel.innerHTML = Translate('Livestock Name');
                                productColumn.appendChild(productLabel);
                            productRow.appendChild(productColumn);
                                let productValue = cTag('div', {class: "columnSM6", id: "product_name"});
                                productValue.innerHTML =  data.product_name;
                            productRow.appendChild(productValue);
                        adjustInventoryColumn.appendChild(productRow);

                            const skuRow = cTag('div', {class: "flex"});
                                const skuColumn = cTag('div', {class: "columnSM6"});
                                    const skuLabel = cTag('label', {'style': "padding-top: 0;"});
                                    skuLabel.innerHTML = Translate('SKU/Barcode');
                                skuColumn.appendChild(skuLabel);
                            skuRow.appendChild(skuColumn);
                                const skuField = cTag('div', {class: "columnSM6"});
                                    inputField = cTag('input', {'readonly': "", 'type': "text", 'maxlength': 20, name: "sku", id: "sku", 'required': "", class: "form-control", 'value': data.sku});
                                skuField.appendChild(inputField);
                            skuRow.appendChild(skuField);
                        adjustInventoryColumn.appendChild(skuRow);

                            const currentInventoryRow = cTag('div', {class: "flex"});
                                const currentInventoryColumn = cTag('div', {class: "columnSM6"});
                                    const currentInventoryLabel = cTag('label', {'style': "padding-top: 0;", 'for': "existing_inventory"});
                                    currentInventoryLabel.innerHTML = Translate('Current Inventory');
                                currentInventoryColumn.appendChild(currentInventoryLabel);
                            currentInventoryRow.appendChild(currentInventoryColumn);
                                const currentInventoryField = cTag('div', {class: "columnSM6"});
                                    inputField = cTag('input', {'type': "text", 'readonly': "", class: "form-control productinventorypopup", name: "existing_inventory", id: "existing_inventory", 'value': data.current_inventory});
                                currentInventoryField.appendChild(inputField);
                                    errorSpan = cTag('span', {class: "error_msg", id: "errmsg_existing_inventory"});
                                currentInventoryField.appendChild(errorSpan);
                            currentInventoryRow.appendChild(currentInventoryField);
                        adjustInventoryColumn.appendChild(currentInventoryRow);

                            const adjustRow = cTag('div', {class: "flex"});
                                const adjustColumn = cTag('div', {class: "columnSM6"});
                                    const adjustLabel = cTag('label', {'for': "adjust_type"});
                                    adjustLabel.append(Translate('Adjust'));
                                        let requiredField = cTag('span', {class: "required"});
                                        requiredField.innerHTML = '*';
                                    adjustLabel.appendChild(requiredField);
                                adjustColumn.appendChild(adjustLabel);
                            adjustRow.appendChild(adjustColumn);
                                const addColumn = cTag('div', {class: "columnSM6"});
                                    let selectAdjustType = cTag('select', { name: "adjust_type", id: "adjust_type",class: "form-control"});
                                    selectAdjustType.addEventListener('change', Calculate_adjust_inventory);
                                        let addOption = cTag('option', {'value': 'Add'});
                                        addOption.innerHTML = Translate('Add');
                                    selectAdjustType.appendChild(addOption);
                                        let subtractOption = cTag('option', {'value': 'Subtract'});
                                        subtractOption.innerHTML = Translate('Subtract');
                                    selectAdjustType.appendChild(subtractOption);
                                addColumn.appendChild(selectAdjustType);
                                    errorSpan = cTag('span', {class: "error_msg", id: "errmsg_existing_inventory"});
                                addColumn.appendChild(errorSpan);
                            adjustRow.appendChild(addColumn);
                        adjustInventoryColumn.appendChild(adjustRow);

                            const adjustQuantityRow = cTag('div', {class: "flex"});
                                const adjustQuantityColumn = cTag('div', {class: "columnSM6"});
                                    const adjustQuantityLabel = cTag('label', {'for': "new_inventory"});
                                    adjustQuantityLabel.append(Translate('Adjusted Quantity'));
                                        requiredField = cTag('span', {class: "required"});
                                        requiredField.innerHTML = '*';
                                    adjustQuantityLabel.appendChild(requiredField);
                                adjustQuantityColumn.appendChild(adjustQuantityLabel);
                            adjustQuantityRow.appendChild(adjustQuantityColumn);
                                const adjustQuantityField = cTag('div', {class: "columnSM6"});
                                    inputField = cTag('input', {'type': "text",'data-max': '9999','data-min':'1', 'data-format':'d', class: "form-control productinventorypopup", name: "new_inventory", id: "new_inventory", 'value': 0});
                                    controllNumericField(inputField, '#errmsg_new_inventory');
                                    inputField.addEventListener('keyup', Calculate_adjust_inventory);
                                    inputField.addEventListener('change', Calculate_adjust_inventory);
                                adjustQuantityField.appendChild(inputField);
                                    errorSpan = cTag('span', {class: "error_msg", id: "errmsg_new_inventory"});
                                adjustQuantityField.appendChild(errorSpan);
                            adjustQuantityRow.appendChild(adjustQuantityField);
                        adjustInventoryColumn.appendChild(adjustQuantityRow);

                            const newInventoryQtyRow = cTag('div', {class: "flex"});
                                const newInventoryQtyColumn = cTag('div', {class: "columnSM6"});
                                    const newInventoryQtyLabel = cTag('label', {'for': "total_inventory"});
                                    newInventoryQtyLabel.innerHTML = Translate('New Inventory Qty');
                                newInventoryQtyColumn.appendChild(newInventoryQtyLabel);
                            newInventoryQtyRow.appendChild(newInventoryQtyColumn);
                                const newInventoryQtyField = cTag('div', {class: "columnSM6"});
                                    inputField = cTag('input', {'maxlength': 11, 'type': "text", 'readonly':"", class: "form-control productinventorypopup", name: "total_inventory", id: "total_inventory", 'value': 0});
                                newInventoryQtyField.appendChild(inputField);
                                    errorSpan = cTag('span', {class: "error_msg", id: "errmsg_total_inventory"});
                                newInventoryQtyField.appendChild(errorSpan);
                            newInventoryQtyRow.appendChild(newInventoryQtyField);
                        adjustInventoryColumn.appendChild(newInventoryQtyRow);

                            const adjustFeedColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                                let pTag = cTag('p');
                                pTag.innerHTML = Translate('This adjustment will be added to the activity feed');
                            adjustFeedColumn.appendChild(pTag);
                        adjustInventoryColumn.appendChild(adjustFeedColumn);

                            const emptyRow = cTag('div', {class: "flex"});
                                let emptyColumn = cTag('div', {class: "columnSM6"});
                            emptyRow.appendChild(emptyColumn);
                                let hiddenColumn = cTag('div', {class: "columnSM6"});
                                [
                                    {type:'hidden', name:'adjust_inventory', id:'adjust_inventory', value:1},
                                    {type:'hidden', name:'product_id', id:'product_id', value:data.product_id},
                                ].forEach(item=>{
                                    inputField = cTag('input');
                                    for(let key in item){   
                                        inputField.setAttribute(key,item[key]);
                                    }
                                    hiddenColumn.appendChild(inputField);
                                })
                            emptyRow.appendChild(hiddenColumn);
                        adjustInventoryColumn.appendChild(emptyRow);
                    adjustInventoryRow.appendChild(adjustInventoryColumn);
                        let errorMessage = cTag('div', {class: "columnSM6 errormsg", id: "error_productinventorypopup"});
                    adjustInventoryRow.appendChild(errorMessage);
                adjustInventoryForm.appendChild(adjustInventoryRow);
            formDialog.appendChild(adjustInventoryForm);
            popup_dialog600('Adjust Inventory', formDialog, Translate('Save'), AJsave_adjust_inventory);            
			Calculate_adjust_inventory();
		}
        else{
            showTopMessage('alert_msg',`${Translate('Can not find item')} ${sku}`)
        }
    }
	return false;
}
async function Calculate_adjust_inventory(){
    let total_inventory;
	let existing_inventory = parseInt(document.getElementById('existing_inventory').value);
	if(existing_inventory==='' || isNaN(existing_inventory)){existing_inventory=0;}
	
	let new_inventory = parseInt(document.getElementById('new_inventory').value);
	if(new_inventory==='' || isNaN(new_inventory)){new_inventory=0;}
	
	if(document.getElementById('adjust_type').value==='Add'){
		total_inventory = Math.floor(existing_inventory+new_inventory);
	}
	else{
		total_inventory = Math.floor(existing_inventory-new_inventory);
	}
	document.getElementById('total_inventory').value = total_inventory;
	
}
async function AJsave_adjust_inventory(hidePopup){
	let product_id = document.getElementById('product_id').value;
	
    let total_inventory;
	let existing_inventory = parseInt(document.getElementById('existing_inventory').value);
	if(existing_inventory==='' || isNaN(existing_inventory)){existing_inventory=0;}
	
	let new_inventory = document.getElementById('new_inventory');
	if(!validateRequiredField(new_inventory,'#errmsg_new_inventory') || !new_inventory.valid()) return;
    
	let adjust_type = document.getElementById('adjust_type').value;
	if(adjust_type==='Add'){
		total_inventory = Math.floor(existing_inventory+new_inventory);
	}
	else{
		total_inventory = Math.floor(existing_inventory-new_inventory);
	}

    const jsonData = {
        "existing_inventory":existing_inventory, 
        "new_inventory":new_inventory.value, 
        "total_inventory":total_inventory, 
        "product_id":product_id, 
        "adjust_type":adjust_type
    }
    const url = '/'+segment1+'/AJsave_adjust_inventory';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        if(data.returnStr !=='Ok'){
			document.getElementById('error_productinventorypopup').innerHTML = Translate('Could not add inventory to the product table.');
		}
		else{
            showTopMessage('success_msg',Translate('Updated successfully.'));
            hidePopup();
            location.reload();
		}
    }
}

//=======For Livestock from common=======//
export async function AJget_LivestocksPopup(frompage, product_id, similarproduct,addCartCBF){
    const jsonData = {};
	jsonData['frompage'] = frompage;
	jsonData['product_id'] = product_id;
	jsonData['similarproduct'] = similarproduct;

    const url = '/'+segment1+'/AJget_LivestocksPopup';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let message = '';
		if(data.login !==''){
            window.location = '/'+data.login;
        }
		else{

            if(product_id===0 && data.cnc===1){
                noPermissionWarning('Livestock');
                return false;
            }
            else if(product_id>0 && data.cne===1){
                noPermissionWarning('Livestock');
                return false;
            }            
            //console.log(data);

			let divCol12, aTag, inputField, requiredField, requireSpan;
			const formDialog = cTag('div');
            // formDialog.appendChild(cTag('div',{ 'class': 'errormsg','id': 'error_product' }));

            formDialog.appendChild(cTag('div',{ 'class': 'errormsg','id': '' }));

				    let productForm = cTag('form',{ 'action': '#','name': 'frmproduct','id': 'frmproduct','enctype': 'multipart/form-data','method': 'post','accept-charset': 'utf-8' });
                    
					    let formFields2 = cTag('div',{ 'class': 'flexSpaBetRow', 'style':'border:1px solid lightgrey' });
                    
					        let divTabs = cTag('div',{ 'id':'tabs', 'style': "max-height: 600px; width: 100%;" });

					            let ulTabs = cTag('ul');

                                        let liTabs1 = cTag('li');
                                            aTag = cTag('a',{ 'href': '#tabs-1' });
                                            aTag.innerHTML = Translate('Basic Info');
                                            liTabs1.appendChild(aTag);
                                        ulTabs.appendChild(liTabs1);

                                        let liTabs2 = cTag('li');
                                            aTag = cTag('a',{ 'href': '#tabs-2' });
                                            aTag.innerHTML =Translate('Arrival');
                                            liTabs2.appendChild(aTag);
                                        ulTabs.appendChild(liTabs2);                                        

                                        let liTabs3 = cTag('li');
                                            aTag = cTag('a',{ 'href': '#tabs-3' });
                                            aTag.innerHTML = Translate('Pedigree');
                                            liTabs3.appendChild(aTag);
                                        ulTabs.appendChild(liTabs3);

                                        let liTabs4 = cTag('li');
                                            aTag = cTag('a',{ 'href': '#tabs-4' });
                                            aTag.innerHTML = Translate('Weaning');
                                            liTabs4.appendChild(aTag);
                                        ulTabs.appendChild(liTabs4);

                                        // let liTabs5 = cTag('li');
                                        //     aTag = cTag('a',{ 'href': '#tabs-5' });
                                        //     aTag.innerHTML = Translate('Birth');
                                        //     liTabs5.appendChild(aTag);
                                        // ulTabs.appendChild(liTabs5);

                                        if(data.customFieldsData.length>0){
                                            // let liTabs3 = cTag('li');
                                            //     aTag = cTag('a',{ 'href': '#tabs-5' });
                                            //     aTag.innerHTML = Translate('Custom Fields');
                                            // liTabs3.appendChild(aTag);
                                            // ulTabs.appendChild(liTabs3);
                                        }

                                divTabs.appendChild(ulTabs);

                                    
                            //======divTabs1 Start======//
                            let divTabs1 = cTag('div',{ 'class': 'columnXS12 flexSpaBetRow','id': 'tabs-1' });

                            let divCol7 = cTag('div',{ 'class': 'columnXS12 columnMD7' });
                            divCol7.appendChild(cTag('input',{ 'type': 'hidden','readonly': '','name': 'product_type','id': 'product_type','value': 'Live Stocks','class': 'form-control' }));


                                //#### Serial No or SKU barcode ######
                                const skuRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                        const skuTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                            const skuLabel = cTag('label',{ 'for': 'sku' });
                                            skuLabel.innerHTML = Translate('SL No.');
                                        skuTitle.appendChild(skuLabel);
                                    skuRow.appendChild(skuTitle);
                                        const skuField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                        let skuHidden = 'hidden';
                                        if(data.sku !=''){
                                            skuHidden = 'readonly';
                                        }
                                        skuField.appendChild(cTag('input',{ 'type': skuHidden, 'readonly':'readonly', 'class': 'form-control','name': 'sku','id': 'sku','value': data.sku,'size': '20','maxlength': '20' }));
                                        if(data.sku ===''){
                                            skuField.appendChild(cTag('input',{ 'type': 'text', 'readonly':'readonly', 'class': 'form-control','value': 'Auto Generate','size': '20','maxlength': '20' }));
                                        }
                                        skuField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_sku' }));
                                    skuRow.appendChild(skuField);
                                divCol7.appendChild(skuRow);

                                //####### Tag ###########
                                const tagRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                        const tagTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                            const tagLabel = cTag('label',{ 'for': 'tag','id': 'lbtag' });
                                            tagLabel.innerHTML = Translate('Tag');

                                                requireSpan = cTag('span',{ 'class': 'required' });
                                                requireSpan.innerHTML = '*';
                                            tagLabel.appendChild(requireSpan);

                                        tagTitle.appendChild(tagLabel);
                                    tagRow.appendChild(tagTitle);

                                        const tagField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                        tagField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'tag','id': 'tag','value': '','maxlength': '100' }));
                                        tagField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_tag' }));
                                    tagRow.appendChild(tagField);
                                divCol7.appendChild(tagRow); 
                                

                                //########## Tag Color ##########        
                                const tagColorDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                    const tagColorRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                        const tagColorTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                            const tagColorLabel = cTag('label',{ 'for': 'tag_color' });
											tagColorLabel.innerHTML = Translate('Tag Color');
                                            tagColorTitle.appendChild(tagColorLabel);
                                        tagColorRow.appendChild(tagColorTitle);
                                    tagColorDiv.appendChild(tagColorRow);
                                        const tagcolorDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                            const tagColorInGroup = cTag('div',{ 'class': 'input-group' });
												let selectTagColor = cTag('select',{ 'class': 'form-control','name': 'tag_color','id': 'tag_color' });
												selectTagColor.appendChild(cTag('option',{ 'value': '' }));
												setOptions(selectTagColor, data.tagColOpt, 0, 1);                             
                                                tagColorInGroup.appendChild(selectTagColor);
                                                tagColorInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '50','name': 'tag_color2','id': 'tag_color2','class': 'form-control', 'style': 'display:none'}));
												let tagColorSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add New Tag Color Name'),'class': 'input-group-addon cursor showNewInputOrSelect' });
												tagColorSpan.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                                                tagColorInGroup.appendChild(tagColorSpan);
                                            tagcolorDropDown.appendChild(tagColorInGroup);
                                        tagColorRow.appendChild(tagcolorDropDown);
                                    tagColorDiv.appendChild(tagColorRow);
                                    tagColorDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_tag_color' }));
							    divCol7.appendChild(tagColorDiv);


                                //######## Alternate Tag ################    
                                const altTagRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const altTagTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const altTagLabel = cTag('label',{ 'for': 'alt_tag','id': 'alt_tag' });
                                        altTagLabel.innerHTML = Translate('Alternate/RFID Tag');
                                        altTagTitle.appendChild(altTagLabel);
                                    altTagRow.appendChild(altTagTitle);
                                    const altTagField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                    altTagField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'alt_tag','id': 'alt_tag','value': data.alt_tag,'maxlength': '150' }));
                                    altTagField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_alt_tag' }));
                                    altTagRow.appendChild(altTagField);
                                divCol7.appendChild(altTagRow);


                                //############## Breed #################
								const breedRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const breedTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const breedLabel = cTag('label',{ 'for': 'category_id' });
                                        breedLabel.innerHTML = Translate('Breed Name');
                                        breedTitle.appendChild(breedLabel);
                                    breedRow.appendChild(breedTitle);
                                    const breedDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                        const breedInGroup = cTag('div',{ 'class': 'input-group' });
                                            let selectBreed = cTag('select',{ 'class': 'form-control','name': 'category_id','id': 'category_id' });
                                                const breedOpt = cTag('option',{ 'value': '0' });
                                                breedOpt.innerHTML = '';
                                                selectBreed.appendChild(breedOpt);
                                            setOptions(selectBreed, data.breedOpt, 1, 1);                      
                                            breedInGroup.appendChild(selectBreed);
                                            breedInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '35','name': 'category_name','id': 'category_name','class': 'form-control',style:'display:none'}));
                                            let breedSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Category')});
                                            breedSpan.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New'));
                                            breedInGroup.appendChild(breedSpan);
                                        breedDropDown.appendChild(breedInGroup);
                                        breedDropDown.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_category_id' }));
                                    breedRow.appendChild(breedDropDown);
                                divCol7.appendChild(breedRow);


                                //############# Livestock Name ################
								const productNameRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const productNameTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const productNameLabel = cTag('label',{ 'for': 'product_name','id': 'lbproduct_name' });
                                        productNameLabel.innerHTML = Translate('Livestock Name');
                                            requireSpan = cTag('span',{ 'class': 'required' });
                                            requireSpan.innerHTML = '*';
                                        productNameLabel.appendChild(requireSpan);
                                        productNameTitle.appendChild(productNameLabel);
                                    productNameRow.appendChild(productNameTitle);
                                        const productNameField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                        productNameField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'product_name','id': 'product_name','value': '','maxlength': '100' }));
                                        productNameField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_product_name' }));
                                    productNameRow.appendChild(productNameField);
                                divCol7.appendChild(productNameRow);


                                //############## Color ###################
                                const colorDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                    const colorRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                        const colorTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                            const colorLabel = cTag('label',{ 'for': 'colour_name' });
                                            colorLabel.innerHTML = Translate('Color Name');
                                        colorTitle.appendChild(colorLabel);
                                    colorRow.appendChild(colorTitle);
                                colorDiv.appendChild(colorRow);
                                const colorNameDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                    const colorNameInGroup = cTag('div',{ 'class': 'input-group' });
                                        let selectColor = cTag('select',{ 'class': 'form-control','name': 'colour_name','id': 'colour_name' });
                                        selectColor.appendChild(cTag('option',{ 'value': '' }));
                                        setOptions(selectColor, data.colNamOpt, 0, 1);                             
                                        colorNameInGroup.appendChild(selectColor);
                                        colorNameInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '15','name': 'colour_name2','id': 'colour_name2','class': 'form-control', 'style': 'display:none'}));
                                            let newColorSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add New Color Name'),'class': 'input-group-addon cursor showNewInputOrSelect' });
                                            newColorSpan.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                                        colorNameInGroup.appendChild(newColorSpan);
                                    colorNameDropDown.appendChild(colorNameInGroup);
                                colorRow.appendChild(colorNameDropDown);
                                colorDiv.appendChild(colorRow);
                                colorDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_colour_name' }));
                                divCol7.appendChild(colorDiv);


                                //############ Animal Description ###################
                                const anmlDescriptionRow = cTag('div',{  'class': 'flex', 'style': "text-align: left;" });
                                    const anmlDescriptionTitle = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                                        const anmlDescriptionLabel = cTag('label',{ 'for': 'anml_description','id': 'anml_description' });
                                        anmlDescriptionLabel.innerHTML = Translate('Description');
                                        anmlDescriptionTitle.appendChild(anmlDescriptionLabel);
                                        anmlDescriptionRow.appendChild(anmlDescriptionTitle);

                                        const anmlDescArea = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'anml_description_div'});
                                        anmlDescArea.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'anml_description','id': 'anml_description_ta' }));
                                        anmlDescArea.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_anml_description' }));
                                        // anmlDescArea.innerHTML = data.anml_description;
                                        // anmlDescArea.val = data.anml_description;
                                        anmlDescriptionRow.appendChild(anmlDescArea);
                                divCol7.appendChild(anmlDescriptionRow);


                                //########### Location ###################
                                const locationRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const locationTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const locationLabel = cTag('label',{ 'for': 'location_id' });
                                        locationLabel.innerHTML = Translate('Location Name');
                                        locationTitle.appendChild(locationLabel);
                                    locationRow.appendChild(locationTitle);
                                    const locationDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                        const locationInGroup = cTag('div',{ 'class': 'input-group' });
                                            let selectLocation = cTag('select',{ 'class': 'form-control','name': 'location_id','id': 'location_id' });
                                                const locationOpt = cTag('option',{ 'value': '0' });
                                                locationOpt.innerHTML = '';
                                                selectLocation.appendChild(locationOpt);
                                            setOptions(selectLocation, data.locationOpt, 1, 1);                      
                                            locationInGroup.appendChild(selectLocation);
                                            locationInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '10','name': 'location_id2','id': 'location_id2','class': 'form-control',style:'display:none'}));
                                            let locationSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Location')});
                                            locationSpan.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New'));
                                            locationInGroup.appendChild(locationSpan);
                                        locationDropDown.appendChild(locationInGroup);
                                        locationDropDown.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_location_id' }));
                                    locationRow.appendChild(locationDropDown);
                                divCol7.appendChild(locationRow);


                                //############# Group ####################
                                const groupRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const groupTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const groupLabel = cTag('label',{ 'for': 'group_id' });
                                        groupLabel.innerHTML = Translate('Group Name');
                                        groupTitle.appendChild(groupLabel);
                                        groupRow.appendChild(groupTitle);

                                        const groupDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                            const groupInGroup = cTag('div',{ 'class': 'input-group' });
                                            
                                                let selectGroup = cTag('select',{ 'class': 'form-control','name': 'group_id','id': 'group_id' });
                                                    const groupOpt = cTag('option',{ 'value': '0' });
                                                    groupOpt.innerHTML = '';
                                                    selectGroup.appendChild(groupOpt);
                                                setOptions(selectGroup, data.groupOpt, 1, 1);                      
                                                groupInGroup.appendChild(selectGroup);
                                                // groupInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '35','name': 'group_id','id': 'group_id','class': 'form-control',style:'display:none'}));
                                                let groupSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Group')});
                                                groupSpan.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New Group'));
                                                groupInGroup.appendChild(groupSpan);

                                            groupDropDown.appendChild(groupInGroup);
                                            groupDropDown.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_group_id' }));
                                        groupRow.appendChild(groupDropDown);
                                divCol7.appendChild(groupRow);                     


                                //########### Gender ###############
                                const genderRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const genderTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const genderLabel = cTag('label',{ 'for': 'gender_id' });
                                    genderLabel.innerHTML = Translate('Gender Name');
                                    genderTitle.appendChild(genderLabel);
                                    genderRow.appendChild(genderTitle);
                                                
                                    const genderRadio = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                        const genderInGroup = cTag('div',{ 'class': 'input-group' });
                                            
                                        genderInGroup.appendChild(cTag('input',{ 'type': 'radio', 'name': 'gender_id','id': 'gender_id_m','value': 1 }));
                                        genderInGroup.append(' '+Translate('Male'));
                                        // genderInGroup.append(' ');
                                        // if(data.gender_id==1){
                                        //     alert(data.gender_id);
										// 	genderInGroup.setAttribute('checked',true);
                                        //     document.getElementById("_1234").checked = true;
										// }
                                        const genderOpt = cTag('span',{ 'value': '&nbsp;' });
                                        genderOpt.innerHTML = '&nbsp;&nbsp;';
                                        genderInGroup.appendChild(genderOpt);
                                        genderInGroup.appendChild(cTag('input',{ 'type': 'radio', 'name': 'gender_id','id': 'gender_id_f','value': 2 }));
                                        genderInGroup.append(' '+Translate('Female'));

                                    genderRadio.appendChild(genderInGroup); 
                                    genderRadio.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_gender_id' }));
                                    genderRow.appendChild(genderRadio);
                                divCol7.appendChild(genderRow);                            


                                //############## Classification #################
                                const classificationRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const classificationTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const classificationLabel = cTag('label',{ 'for': 'classification_id' });
                                        classificationLabel.innerHTML = Translate('Classification');
                                            classificationTitle.appendChild(classificationLabel);
                                        classificationRow.appendChild(classificationTitle);
                                        const classificationDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                            const classificationInGroup = cTag('div',{ 'class': 'input-group' });
                                                let selectClassification = cTag('select',{ 'class': 'form-control','name': 'classification_id','id': 'classification_id' });
                                                    const clasfOpt = cTag('option',{ 'value': '0' });
                                                    clasfOpt.innerHTML = '';
                                                    selectClassification.appendChild(clasfOpt);
                                                setOptions(selectClassification, data.clasfOpt, 1, 1);                      
                                                classificationInGroup.appendChild(selectClassification);
                                                // classificationInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '35','name': 'classification_id','id': 'classification_id','class': 'form-control',style:'display:none'}));
                                                let clasfSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Classification')});
                                                clasfSpan.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New'));
                                                classificationInGroup.appendChild(clasfSpan);
                                            classificationDropDown.appendChild(classificationInGroup);
                                            classificationDropDown.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_classification_id' }));
                                        classificationRow.appendChild(classificationDropDown);
                                divCol7.appendChild(classificationRow);
                                                             
                                
                                //############# Purpose #################        
                                const purposeDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                    const purposeRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                        const purposeTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                            const purposeLabel = cTag('label',{ 'for': 'purpose' });
											purposeLabel.innerHTML = Translate('Purpose');
                                            purposeTitle.appendChild(purposeLabel);
                                            purposeRow.appendChild(purposeTitle);
                                        purposeDiv.appendChild(purposeRow);
                                        const purposeDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                            const purposeInGroup = cTag('div',{ 'class': 'input-group' });
												let selectPurpose = cTag('select',{ 'class': 'form-control','name': 'purpose','id': 'purpose' });
												selectPurpose.appendChild(cTag('option',{ 'value': '' }));
												setOptions(selectPurpose, data.purposeOpt, 0, 1);                             
                                                purposeInGroup.appendChild(selectPurpose);
                                                // purposeInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '50','name': 'purpose2','id': 'purpose2','class': 'form-control', 'style': 'display:none'}));
												let purposeSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add Purpose'),'class': 'input-group-addon cursor showNewInputOrSelect' });
												purposeSpan.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                                                purposeInGroup.appendChild(purposeSpan);
                                                purposeDropDown.appendChild(purposeInGroup);
                                            purposeRow.appendChild(purposeDropDown);
                                        purposeDiv.appendChild(purposeRow);
                                        purposeDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_purpose' }));
							    divCol7.appendChild(purposeDiv);


                                //################### Age In Year ###################
								const ageInYearDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                    const ageInYearRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                        const ageInYearTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                            const ageInYearLabel = cTag('label',{ 'for': 'age_in_year','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                            ageInYearLabel.innerHTML = Translate('Age in Year');
                                            ageInYearTitle.appendChild(ageInYearLabel);
                                        ageInYearRow.appendChild(ageInYearTitle);
                                        ageInYearDiv.appendChild(ageInYearRow);
                                            const ageInYearField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                            ageInYearField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'age_in_year','id': 'age_in_year','class': 'form-control','value': data.age_in_year }));
                                            ageInYearRow.appendChild(ageInYearField);
                                        ageInYearDiv.appendChild(ageInYearRow);
                                        ageInYearDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_age_in_year' }));
                                divCol7.appendChild(ageInYearDiv);


                                //################### No of Teeth ##################
								const noOfTeethDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                const noOfTeethRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const noOfTeethTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const noOfTeethLabel = cTag('label',{ 'for': 'no_of_teeth','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                        noOfTeethLabel.innerHTML = Translate('No of Teeth');
                                        noOfTeethTitle.appendChild(noOfTeethLabel);
                                        noOfTeethRow.appendChild(noOfTeethTitle);
                                    noOfTeethDiv.appendChild(noOfTeethRow);
                                        const noOfTeethField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                        noOfTeethField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'no_of_teeth','id': 'no_of_teeth','class': 'form-control','value': data.no_of_teeth }));
                                        noOfTeethRow.appendChild(noOfTeethField);
                                        noOfTeethDiv.appendChild(noOfTeethRow);
                                        noOfTeethDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_no_of_teeth' }));
                                divCol7.appendChild(noOfTeethDiv);


                                
                            //     //Category Name
                            // 	const categoryRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                            //         const categoryTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                            //             const categoryLabel = cTag('label',{ 'for': 'category_id' });
                            //             categoryLabel.innerHTML = Translate('Category Name');
                            //         categoryTitle.appendChild(categoryLabel);
                            //     categoryRow.appendChild(categoryTitle);
                            //         const categoryDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            //             const categoryInGroup = cTag('div',{ 'class': 'input-group' });
                            //                 let selectCategory = cTag('select',{ 'class': 'form-control','name': 'category_id','id': 'category_id' });
                            //                     const categoryOpt = cTag('option',{ 'value': '0' });
                            //                     categoryOpt.innerHTML = '';
                            //                 selectCategory.appendChild(categoryOpt);
                            //                 setOptions(selectCategory, data.catOpt, 1, 1);                      
                            //             categoryInGroup.appendChild(selectCategory);
                            //             categoryInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '35','name': 'category_name','id': 'category_name','class': 'form-control',style:'display:none'}));
                            //                 let newSpan = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Category')});
                            //                 newSpan.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New'));
                            //             categoryInGroup.appendChild(newSpan);
                            //         categoryDropDown.appendChild(categoryInGroup);
                            //         categoryDropDown.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_category_id' }));
                            //     categoryRow.appendChild(categoryDropDown);
                            // divCol7.appendChild(categoryRow);

                            //     //Manufacturer Name
                            //     const manufacturerDiv = cTag('div',{ 'class': ' LiveStocks' });
                            //         const manufacturerRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                            //             const manufacturerTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                            //                 const manufacturerLabel = cTag('label',{ 'for': 'manufacturer_id' });
                            // 				manufacturerLabel.innerHTML = Translate('Manufacturer Name');
                            //             manufacturerTitle.appendChild(manufacturerLabel);
                            //         manufacturerRow.appendChild(manufacturerTitle);
                            //     manufacturerDiv.appendChild(manufacturerRow);
                            //             const manufacturerDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            //                 const manufacturerInGroup = cTag('div',{ 'class': 'input-group' });
                            // 					let selectManufacturer = cTag('select',{ 'class': 'form-control','name': 'manufacturer_id','id': 'manufacturer_id' });
                            //                         const manufacturerOpt = cTag('option',{ 'value': '0' });
                            //                         manufacturerOpt.innerHTML = '';
                            //                     selectManufacturer.appendChild(manufacturerOpt);
                            //                     setOptions(selectManufacturer, data.manOpt, 1, 1);                       
                            //                 manufacturerInGroup.appendChild(selectManufacturer);
                            // 				manufacturerInGroup.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '30','name': 'manufacture','id': 'manufacture','class': 'form-control', style:'display:none'}));
                            // 					let newManfacturerSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add New Manufacturer'),'class': 'input-group-addon cursor showNewInputOrSelect' });
                            // 					newManfacturerSpan.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                            //                 manufacturerInGroup.appendChild(newManfacturerSpan);
                            //             manufacturerDropDown.appendChild(manufacturerInGroup);
                            // 			manufacturerDropDown.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_manufacturer_id' }));
                            //         manufacturerRow.appendChild(manufacturerDropDown);
                            //     manufacturerDiv.appendChild(manufacturerRow);
                            // divCol7.appendChild(manufacturerDiv);
								                       

                            //     //Storage
                            // 		const storageDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            //         const storageRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                            //             const storageTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                            //                 const storageLabel = cTag('label',{ 'for': 'storage','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                            //                 storageLabel.innerHTML = Translate('Storage');
                            //             storageTitle.appendChild(storageLabel);
                            //         storageRow.appendChild(storageTitle);
                            //     storageDiv.appendChild(storageRow);
                            //             const storageField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                            //             storageField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'storage','id': 'storage','class': 'form-control','value': data.storage }));
                            //         storageRow.appendChild(storageField);
                            //     storageDiv.appendChild(storageRow);
                            //     storageDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_storage' }));
                            // divCol7.appendChild(storageDiv);


							// 	//physical condition
							// 	const physicalDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            //         const physicalRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                            //             const physicalTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                            //                 const physicalLabel = cTag('label',{ 'for': 'physical_condition_name','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('What would you grade the device in terms of cosmetic appearance?') });
							// 				physicalLabel.innerHTML = Translate('Physical Condition');
                            //             physicalTitle.appendChild(physicalLabel);
                            //         physicalRow.appendChild(physicalTitle);
                            //     physicalDiv.appendChild(physicalRow);
                            //             const physicalConditionDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left' });
							// 				let selectCondition = cTag('select',{ 'class': 'form-control','id': 'physical_condition_name','name': 'physical_condition_name' });
							// 				selectCondition.appendChild(cTag('option',{ 'value': '' }));
							// 				setOptions(selectCondition, data.phyConNamOpt, 0, 1);  
                            //             physicalConditionDropDown.appendChild(selectCondition);
							// 			physicalConditionDropDown.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_physical_condition_name' }));
                            //         physicalRow.appendChild(physicalConditionDropDown);
                            //     physicalDiv.appendChild(physicalRow);
							// divCol7.appendChild(physicalDiv);

                            // 		//regular price
                            // 		const regularRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                            //             const regularTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                            //                 const regularLabel = cTag('label',{ 'for': 'regular_price','data-placement': 'bottom' });
                            // 				regularLabel.innerHTML = Translate('Selling Price');
                            //             regularTitle.appendChild(regularLabel);
                            //         regularRow.appendChild(regularTitle);
                            //             const regularDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            // 				let sellPrice = cTag('input',{ 'type': 'text','data-min':'-9999999.99','data-max':'9999999.99','data-format':'d.dd','class': 'form-control','name': 'regular_price','id': 'regular_price','value': round(data.regular_price,2) });
                            // 				controllNumericField(sellPrice,'#errmsg_regular_price');
                            //                 sellPrice.addEventListener('blur',function(){
                            // 					if(this.value<0) document.getElementById('error_product').innerHTML = "Selling Price can't be < than 0"
                            // 				})
                            //             regularDropDown.appendChild(sellPrice);
                            // 			regularDropDown.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_regular_price' }));
                            //         regularRow.appendChild(regularDropDown);
                            // 	divCol7.appendChild(regularRow);
                            // divTabs1.appendChild(divCol7);

								//Minimum Selling price
							// 	const minimumSellingRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                            //         const minimumSellingTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                            //             const minimumSellingLabel = cTag('label',{ 'for': 'minimum_price'});
							// 			minimumSellingLabel.innerHTML = Translate('Minimum Selling Price');
                            //         minimumSellingTitle.appendChild(minimumSellingLabel);
                            //     minimumSellingRow.appendChild(minimumSellingTitle);
                            //         const minimumSellingField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
							// 			let sellPriceInput = cTag('input',{ 'type': 'text','data-min':'-9999999.99','data-max':'9999999.99','data-format':'d.dd','class': 'form-control','name': 'minimum_price','id': 'minimum_price','value': round(data.minimum_price,2)});
							// 			controllNumericField(sellPriceInput,'#errmsg_minimum_price');
                            //         minimumSellingField.appendChild(sellPriceInput);
							// 		minimumSellingField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_minimum_price' }));
                            //     minimumSellingRow.appendChild(minimumSellingField);
							// divCol7.appendChild(minimumSellingRow);


						divTabs1.appendChild(divCol7);

                        let divCol5 = cTag('div',{ 'class': 'columnXS12 columnMD5', 'style': "padding: 5px 15px;" });
                            
                            //############### taxable ###############
							const taxRow = cTag('div',{ 'class': 'flex', 'style': "margin-bottom: 10px;" });
                                    const taxLabel = cTag('label',{ 'for': 'taxable' });
										inputField = cTag('input',{ 'type': 'checkbox','name': 'taxable','id': 'taxable','value': 1 });
										if(data.taxable){
											inputField.setAttribute('checked',true);
										}
                                    taxLabel.appendChild(inputField);
									taxLabel.append(' '+Translate('Taxable'));
                                taxRow.appendChild(taxLabel);
							divCol5.appendChild(taxRow);

                            //############# Inventory count ############
							const inventoryDiv = cTag('div',{ 'class': 'LiveStocks manage_inventory_count' });
                                    const inventoryRow = cTag('div',{ 'class': 'flex', 'style': "margin-bottom: 10px;" });
                                        let inventoryLabel = cTag('label',{ 'for': 'manage_inventory_count' });
											inputField = cTag('input',{ 'type': 'checkbox','name': 'manage_inventory_count','id': 'manage_inventory_count','value': 1 });
                                            if(data.manage_inventory_count){
                                                inputField.setAttribute('checked',true);
                                            }
											inputField.addEventListener('click',checkManageInventory);
                                        inventoryLabel.appendChild(inputField);
										inventoryLabel.append(' '+Translate('Count Inventory'));
                                    inventoryRow.appendChild(inventoryLabel);
                                inventoryDiv.appendChild(inventoryRow);
								inventoryDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_manage_inventory_count' }));
							divCol5.appendChild(inventoryDiv);
								let divHidden = cTag('div',{ style:'display:none' });
									let currentInventoryColumn = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        let currentInventoryLabel = cTag('label',{ 'for': 'current_inventory' });
										currentInventoryLabel.innerHTML = Translate('Current Inventory');
                                    currentInventoryColumn.appendChild(currentInventoryLabel);
								divHidden.appendChild(currentInventoryColumn);
                                    let currentInventoryDiv = cTag('div',{ 'class': 'columnXS2', 'style': "padding-right: 0;" });
											inputField = cTag('input',{ 'maxlength': '9','type': 'number' });
									if(data.current_inventoryReadonly !==''){
										inputField.setAttribute('checked',true);
									}
                                        inputField.setAttribute('class', 'form-control qtyfield');
										inputField.setAttribute('name', 'current_inventory');
										inputField.setAttribute('id', 'current_inventory');
										inputField.setAttribute('value', data.current_inventory);
                                    currentInventoryDiv.appendChild(inputField);
								divHidden.appendChild(currentInventoryDiv);
								divHidden.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_current_inventory' }));
							divCol5.appendChild(divHidden);

                            //########### manage inventory ############
                            const manageRow = cTag('div',{ 'class': 'manage_inventory' });
                                    const manageDiv = cTag('div',{ 'class': 'flex', 'style': "margin-bottom: 10px;" });
                                        const manageTitle = cTag('div',{ 'class': 'columnXS5', 'style': "padding-top: 5px; text-align: left;" });
                                            const manageLabel = cTag('label',{ 'for': 'low_inventory_alert' });
											manageLabel.innerHTML = Translate('Alert Below');
                                        manageTitle.appendChild(manageLabel);
                                    manageDiv.appendChild(manageTitle);
                                manageRow.appendChild(manageDiv);
                                        const manageField = cTag('div',{ 'class': 'columnXS7' });
										manageField.appendChild(cTag('input',{ 'type': 'number','maxlength': '2','class': 'form-control','name': 'low_inventory_alert','id': 'low_inventory_alert','value': data.low_inventory_alert }));
										manageField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_low_inventory_alert' }));
                                    manageDiv.appendChild(manageField);
                                manageRow.appendChild(manageDiv);
							divCol5.appendChild(manageRow);

                            //############## Additional description ###################
                            const descriptionRow = cTag('div',{ 'class': 'flex', 'style': "margin-bottom: 10px;" });
                                    const descriptionLabel = cTag('label',{ 'for': 'add_description' });
									descriptionLabel.innerHTML = Translate('Additional Description');
                                descriptionRow.appendChild(descriptionLabel);
							divCol5.appendChild(descriptionRow);

                                const addDescriptionRow = cTag('div',{ 'class': 'flex', 'style': "margin-bottom: 10px;" });
									let descriptionArea = cTag('textarea',{ 'class': 'form-control','rows': '2','cols': '20','name': 'add_description','id': 'add_description' });
									descriptionArea.innerHTML = data.add_description;
                                addDescriptionRow.appendChild(descriptionArea);
								addDescriptionRow.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_add_description' }));
							divCol5.appendChild(addDescriptionRow);

							// 	const imeiLivestockDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            //         const imeiLivestockRow = cTag('div',{ 'class': 'flex' });
                            //             const imeiLivestockColumn = cTag('div',{ 'class': 'columnXS12 roundborder bgcoolblue', 'style': "padding-left: 20px; padding-right: 20px; padding-top: 15px; padding-bottom: 15px;" });
							// 				let pTag = cTag('p');
							// 				pTag.innerHTML = Translate('IMEI numbers are added from the Livestock Information page after you save this new mobile device.');
                            //             imeiLivestockColumn.appendChild(pTag);
                            //         imeiLivestockRow.appendChild(imeiLivestockColumn);
                            //     imeiLivestockDiv.appendChild(imeiLivestockRow);
							// divCol5.appendChild(imeiLivestockDiv);


						divTabs1.appendChild(divCol5);
					divTabs.appendChild(divTabs1);             
                              

                            // //####### Tag ###########
                            // const tagRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                            //         const tagTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                            //             const tagLabel = cTag('label',{ 'for': 'tag','id': 'lbtag' });
                            //             tagLabel.innerHTML = Translate('Tag');

                            //                 requireSpan = cTag('span',{ 'class': 'required' });
                            //                 requireSpan.innerHTML = '*';
                            //             tagLabel.appendChild(requireSpan);

                            //         tagTitle.appendChild(tagLabel);
                            //     tagRow.appendChild(tagTitle);

                            //         const tagField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            //         tagField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'tag','id': 'tag','value': '','maxlength': '100' }));
                            //         tagField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_tag' }));
                            //     tagRow.appendChild(tagField);
                            // divCol7.appendChild(tagRow); 




                    //======divTabs2 Start======//
                    let divTabs2 = cTag('div',{ 'class': 'columnXS12','id': 'tabs-2' });
                   
                        let tab2DivCol7 = cTag('div',{ 'class': 'columnXS12 columnMD7' });

                            //########## Arrival Date ############        
                            const arrivalDateRow = cTag('div', {class: "flex"});
                                    const arrivalDateName = cTag('div', {class: "columnSM4", 'align': "left"});
                                        const arrivalDateLabel = cTag('label', {'for': "arrival_date"});
                                        arrivalDateLabel.innerHTML = Translate('Arrival Date');

                                        let adrequiredField = cTag('span', {class: "required"});
                                        adrequiredField.innerHTML = '*';
                                            arrivalDateLabel.appendChild(adrequiredField);
                                        arrivalDateName.appendChild(arrivalDateLabel);
                                        
                                    arrivalDateRow.appendChild(arrivalDateName);
                                    const arrivalDateField = cTag('div', {class: "columnSM8", 'align': "left"});
                                        inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "arrival_date", id: "arrival_date", 'value': '', 'maxlength': 10});
                                        checkDateOnBlur(inputField,'#error_date','Invalid '+Translate('Arrival Date'));
                                        arrivalDateField.appendChild(inputField);
                                        arrivalDateField.appendChild(cTag('span',{id:'error_arrival_date',class:'errormsg'}));
                                    arrivalDateRow.appendChild(arrivalDateField);
                            tab2DivCol7.appendChild(arrivalDateRow);     
                                              


                            //########### Arrival Weight ##############
                            const arrivalWeightDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                const arrivalWeightRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const arrivalWeightTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const arrivalWeightLabel = cTag('label',{ 'for': 'arrival_weight','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                        arrivalWeightLabel.innerHTML = Translate('Arrival Weight');
                                        arrivalWeightTitle.appendChild(arrivalWeightLabel);
                                        arrivalWeightRow.appendChild(arrivalWeightTitle);
                                        arrivalWeightDiv.appendChild(arrivalWeightRow);
                                        const arrivalWeightField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                        arrivalWeightField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'arrival_weight','id': 'arrival_weight','class': 'form-control','value': data.arrival_weight }));
                                        arrivalWeightRow.appendChild(arrivalWeightField);
                                        arrivalWeightDiv.appendChild(arrivalWeightRow);
                                        arrivalWeightDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_arrival_weight' }));
                            tab2DivCol7.appendChild(arrivalWeightDiv);


                            //############ Arrival Type ################        
                            const arrivalTypeDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                const arrivalTypeRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const arrivalTypeTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const arrivalTypeLabel = cTag('label',{ 'for': 'arrival_type' });
                                        arrivalTypeLabel.innerHTML = Translate('Arrival Type');
                                        arrivalTypeTitle.appendChild(arrivalTypeLabel);
                                        arrivalTypeRow.appendChild(arrivalTypeTitle);
                                        arrivalTypeDiv.appendChild(arrivalTypeRow);
                                    const arrivalTypeDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                        const arrivalTypeInGroup = cTag('div',{ 'class': 'input-group' });
                                            let arrivalTypePurpose = cTag('select',{ 'class': 'form-control','name': 'arrival_type','id': 'arrival_type' });
                                            arrivalTypePurpose.appendChild(cTag('option',{ 'value': '' }));
                                            setOptions(arrivalTypePurpose, data.arrvtypeOpt, 0, 1);                             
                                            arrivalTypeInGroup.appendChild(arrivalTypePurpose);
                                            let arrivalTypeSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add Arrival Type'),'class': 'input-group-addon cursor showNewInputOrSelect' });
                                            arrivalTypeSpan.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                                            arrivalTypeInGroup.appendChild(arrivalTypeSpan);
                                            arrivalTypeDropDown.appendChild(arrivalTypeInGroup);
                                            arrivalTypeRow.appendChild(arrivalTypeDropDown);
                                        arrivalTypeDiv.appendChild(arrivalTypeRow);
                                    arrivalTypeDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_arrival_type' }));
                            tab2DivCol7.appendChild(arrivalTypeDiv);


                            //########### Location ###################                         
                                                  
                            //############ Purchased From ############ 
                            const supplierNameRow = cTag('div',{ 'class':`flex`, 'style': "text-align: left;" });
                                const customerNameTitle = cTag('div',{ 'class':`columnXS12 columnSM4` });
                                    let customerNameLabel = cTag('label',{ 'for':`supplier`,'data-placement':`bottom` });
                                        customerNameLabel.append(Translate('Purchased From'));

                                            let errorSpan = cTag('span', {class: "required"});
                                            errorSpan.innerHTML = '*';
                                        customerNameLabel.appendChild(errorSpan);
                                        
                                    customerNameTitle.appendChild(customerNameLabel);
                                    supplierNameRow.appendChild(customerNameTitle);
                                    //==================================================
                                    const supplierNameField = cTag('div',{ 'class':`columnXS12 columnSM8` });
                                        const customerInGroup = cTag('div',{ 'class':`input-group`,'id':`supplierNameField` });

                                        customerInGroup.appendChild(cTag('input',{ 'autocomplete':`off`,'maxlength':`50`,'type':`text`,'value':``,'required':``,'name':`supplier`,'id':`supplier`,'class':`form-control ui-autocomplete-input`,'placeholder':Translate('Search Supplier') }));
                                        customerInGroup.appendChild(cTag('input',{ 'type':`hidden`,'name':`supplier_id`,'value':`0`,'id':'supplier_id' }));
                                            let newSpan = cTag('span',{ 'id':'add_new_supplier','data-toggle':`tooltip`,'data-original-title':Translate('Add New Supplier'),'class':`input-group-addon cursor` });
                                            newSpan.addEventListener('click',()=>dynamicImport('./Manage_Data.js','addnewsupplierform',['addLiveStocks', 0]))
                                            newSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
                                        customerInGroup.appendChild(newSpan);

                                    supplierNameField.appendChild(customerInGroup);
                                    supplierNameField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_suppliers_id` }));
                                    supplierNameRow.appendChild(supplierNameField);
                                    const errorColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
                                    errorColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_supplier` }));
                                    supplierNameRow.appendChild(errorColumn);
                            tab2DivCol7.appendChild(supplierNameRow);
                            //==================================================                                                  
                            

                            //########### Purchase Price ##############
                            const pursPriceDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                                const pursPriceRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                    const pursPriceTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                        const pursPriceLabel = cTag('label',{ 'for': 'purchase_price','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select pp.') });
                                        pursPriceLabel.innerHTML = Translate('Purchase Price');
                                        pursPriceTitle.appendChild(pursPriceLabel);
                                        pursPriceRow.appendChild(pursPriceTitle);
                                        pursPriceDiv.appendChild(pursPriceRow);
                                        const pursPriceField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                        pursPriceField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'purchase_price','id': 'purchase_price','class': 'form-control','value': data.purchase_price }));
                                        pursPriceRow.appendChild(pursPriceField);
                                        pursPriceDiv.appendChild(pursPriceRow);
                                        pursPriceDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_purchase_price' }));
                            tab2DivCol7.appendChild(pursPriceDiv);



                            //############ Arrival Note ###################
                            const arrivalNoteRow = cTag('div',{  'class': 'flex', 'style': "text-align: left;" });
                                const arrivalNoteTitle = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                                    const arrivalNoteLabel = cTag('label',{ 'for': 'arrival_note','id': 'arrival_note' });
                                    arrivalNoteLabel.innerHTML = Translate('Arrival Note');
                                    arrivalNoteTitle.appendChild(arrivalNoteLabel);
                                    arrivalNoteRow.appendChild(arrivalNoteTitle);

                                    const arrivalNoteArea = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'arrival_note_div'});
                                    arrivalNoteArea.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'arrival_note','id': 'arrival_note_ta' }));
                                    anmlDescArea.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_arrival_note' }));
                                    // arrivalNoteArea.innerHTML = data.arrival_note;
                                    // arrivalNoteArea.val = data.arrival_note;
                                    arrivalNoteRow.appendChild(arrivalNoteArea);
                            tab2DivCol7.appendChild(arrivalNoteRow);



                        divTabs2.appendChild(tab2DivCol7);
                    divTabs.appendChild(divTabs2);


                                  
                    


                    //======divTabs3 Start======//
                    let divTabs3 = cTag('div',{ 'class': 'columnXS12','id': 'tabs-3' , 'style': "border:0px solid red;"});
                   
                        let tab3DivCol7 = cTag('div',{ 'class': 'columnXS12 columnMD6', 'style': "border:0px solid red; float:left;" });
                                              
                        tab3DivCol7.appendChild(cTag('input',{ 'type': 'hidden','readonly': '','name': 'product_type','id': 'product_type','value': 'Live Stocks','class': 'form-control' }));
                        

                        //############ Parent Info Maternal ############ 
                        const motherNameRow = cTag('div',{ 'class':'flex manage_maternal_block_in', 'style': 'text-align: left;' });
                        const motherNameTitle = cTag('div',{ 'class':'columnXS12 columnSM4' });
                            let motherNameLabel = cTag('label',{ 'for':'product_id','data-placement':'bottom' });
                            motherNameLabel.append(Translate('Mother Tag'));

                                let motherErrSpan = cTag('span', {class: 'required'});
                                motherErrSpan.innerHTML = '*';
                                motherNameLabel.appendChild(motherErrSpan);
                                    
                                motherNameTitle.appendChild(motherNameLabel);
                                motherNameRow.appendChild(motherNameTitle);
                                //==================================================
                                const motherNameField = cTag('div',{ 'class':'columnXS12 columnSM8' });
                                    const motherInGroup = cTag('div',{ 'class':'input-group columnXS12 columnSM8','id':'toPedigreeInfo' });
                                    motherNameField.appendChild(cTag('input',{ 'type':'text', 'class':'form-control ui-autocomplete-input', 'name':'lsproduct','id':'lsproduct', 'maxlength':'50', 'autocomplete':'off', 'required':'', 'placeholder':Translate('Search Mother') }));
                                    motherNameField.appendChild(cTag('input',{ 'type':'hidden','name':'lsproduct_id','value':'0','id':'lsproduct_id' }));
                                        // let motherNewSpan = cTag('span',{ 'id':'add_new_supplier','data-toggle':'tooltip','data-original-title':Translate('Add New Pedigree'),'class':'input-group-addon cursor' });
                                        // motherNewSpan.addEventListener('click',()=>dynamicImport('./Manage_Data.js','addnewsupplierform',['addLiveStocks', 0]))
                                        // motherNewSpan.append(cTag('i',{ 'class':'fa fa-plus' }), ' ', Translate('New'));
                                        // motherInGroup.appendChild(motherNewSpan);
                                    motherNameField.appendChild(motherInGroup);
                                    motherNameField.appendChild(cTag('span',{ 'class':'error_msg','id':'errmsg_lsproduct_id' }));
                                motherNameRow.appendChild(motherNameField);
                                const motherErrColumn = cTag('div',{ 'class':'columnXS12 columnSM6' });
                                motherErrColumn.appendChild(cTag('span',{ 'class':'error_msg','id':'errmsg_lsproduct' }));
                                motherNameRow.appendChild(motherErrColumn);
                         tab3DivCol7.appendChild(motherNameRow);
                        //==================================================  

                    //     const supplierInfoRow = cTag('div', {class: "flexSpaBetRow"});
					// 		  const supplierInfoColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;",  id: "toSupplierInfo"});
					// 	 supplierInfoRow.appendChild(supplierInfoColumn);
					// mergeSupplierForm.appendChild(supplierInfoRow);

                        


                        //############# Check Maternal ############
						const maternalDiv = cTag('div',{ 'class': 'LiveStocks manage_maternal_block' });
                            const maternalRow = cTag('div',{ 'class': 'flex', 'style': "margin-bottom: 10px;" });
                                let maternalLabel = cTag('label',{ 'for': 'manage_maternal_block' });
                                    inputField = cTag('input',{ 'type': 'checkbox','name': 'manage_maternal_block','id': 'manage_maternal_block','value': 0 });
                                    // if(data.manage_inventory_count){
                                    //     inputField.setAttribute('checked',true);
                                    // }
                                    inputField.addEventListener('click',checkMaternalBlock);
                                    maternalLabel.appendChild(inputField);
                                    maternalLabel.append(' '+Translate('External Parent'));
                                maternalRow.appendChild(maternalLabel);
                            maternalDiv.appendChild(maternalRow);
                            maternalDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_manage_maternal_block' }));
                        tab3DivCol7.appendChild(maternalDiv);



                        //######## Alternate RFID Tag Maternal ################    
                        const altTagRowMaternal = cTag('div',{ 'class': 'flex manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const altTagTitleMaternal = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                const altTagLabelMaternal = cTag('label',{ 'for': 'alt_tag','id': 'alt_tag' });
                                altTagLabelMaternal.innerHTML = Translate('Alternate/RFID Tag of Mother');
                                altTagTitleMaternal.appendChild(altTagLabelMaternal);
                                altTagRowMaternal.appendChild(altTagTitleMaternal);
                            const altTagFieldMother = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            altTagFieldMother.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'alt_tag','id': 'alt_tag','value': data.alt_tag,'maxlength': '150' }));
                            altTagFieldMother.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_alt_tag' }));
                            altTagRowMaternal.appendChild(altTagFieldMother);
                        tab3DivCol7.appendChild(altTagRowMaternal);


                        //############## Maternal Breed #################
                        const breedRowMaternal = cTag('div',{ 'class': 'flex manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const breedTitleMaternal = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                const breedLabelMaternal = cTag('label',{ 'for': 'category_id_maternal' });
                                breedLabelMaternal.innerHTML = Translate('Mother Breed Name');
                                breedTitleMaternal.appendChild(breedLabelMaternal);
                                breedRowMaternal.appendChild(breedTitleMaternal);
                            const breedDropDownMaternal = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                const breedInGroupMaternal = cTag('div',{ 'class': 'input-group' });
                                    let selectBreedMaternal = cTag('select',{ 'class': 'form-control','name': 'category_id_maternal','id': 'category_id_maternal' });
                                        const breedOptMaternal = cTag('option',{ 'value': '0' });
                                        breedOptMaternal.innerHTML = '';
                                        selectBreedMaternal.appendChild(breedOptMaternal);
                                    setOptions(selectBreedMaternal, data.breedOpt, 1, 1);                      
                                    breedInGroupMaternal.appendChild(selectBreedMaternal);
                                    breedInGroupMaternal.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '35','name': 'category_name_maternal','id': 'category_name_maternal','class': 'form-control',style:'display:none'}));
                                    let breedSpanMaternal = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Category')});
                                    breedSpanMaternal.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New'));
                                    breedInGroupMaternal.appendChild(breedSpanMaternal);
                                    breedDropDownMaternal.appendChild(breedInGroupMaternal);
                                    breedDropDownMaternal.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_category_id_maternal' }));
                                breedRowMaternal.appendChild(breedDropDownMaternal);
                        tab3DivCol7.appendChild(breedRowMaternal);

                        
                        //############# Maternal Livestock Name ################
                        const maternalNameRow = cTag('div',{ 'class': 'flex manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const maternalNameTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                const maternalNameLabel = cTag('label',{ 'for': 'mother_name','id': 'lbmother_name' });
                                maternalNameLabel.innerHTML = Translate('Mother Name');
                                    requireSpan = cTag('span',{ 'class': 'required' });
                                    requireSpan.innerHTML = '*';
                                    maternalNameLabel.appendChild(requireSpan);
                                maternalNameTitle.appendChild(maternalNameLabel);
                                maternalNameRow.appendChild(maternalNameTitle);
                                const maternalNameField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                maternalNameField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'mother_name','id': 'mother_name','value': '','maxlength': '100' }));
                                maternalNameField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_mother_name' }));
                                maternalNameRow.appendChild(maternalNameField);
                        tab3DivCol7.appendChild(maternalNameRow);


                        //############## Mother Cattle Color ###################
                        const colorDivMother = cTag('div',{ 'class': 'displayNotAll manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const colorRowMother = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const colorTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const colorLabelMother = cTag('label',{ 'for': 'colour_name' });
                                    colorLabelMother.innerHTML = Translate('Color Name');
                                    colorTitleMother.appendChild(colorLabelMother);
                                colorRowMother.appendChild(colorTitleMother);
                            colorDivMother.appendChild(colorRowMother);
                        const colorNameDropDownMother = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            const colorNameInGroupMother = cTag('div',{ 'class': 'input-group' });
                                let selectColorMother = cTag('select',{ 'class': 'form-control','name': 'colour_name_mother','id': 'colour_name_mother' });
                                selectColorMother.appendChild(cTag('option',{ 'value': '' }));
                                setOptions(selectColorMother, data.colNamOpt, 0, 1);                             
                                colorNameInGroupMother.appendChild(selectColorMother);
                                colorNameInGroupMother.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '15','name': 'colour_name_mother2','id': 'colour_name_mother2','class': 'form-control', 'style': 'display:none'}));
                                    let newColorSpanMother = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add New Color Name'),'class': 'input-group-addon cursor showNewInputOrSelect' });
                                    newColorSpanMother.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                                    colorNameInGroupMother.appendChild(newColorSpanMother);
                                colorNameDropDownMother.appendChild(colorNameInGroupMother);
                            colorRowMother.appendChild(colorNameDropDownMother);
                        colorDivMother.appendChild(colorRowMother);
                        colorDivMother.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_colour_name' }));
                        tab3DivCol7.appendChild(colorDivMother);
                        

                        //########## Maternal Birth Date ############        
                        const birthDateRowMother = cTag('div', {class: 'flex manage_maternal_block_out', 'style': "text-align: left; display:none" });
                                const birthDateNameMother = cTag('div', {class: "columnSM4", 'align': "left"});
                                    const birthDateLabelMother = cTag('label', {'for': "birth_date_mother"});
                                    birthDateLabelMother.innerHTML = Translate('Birth Date');

                                    let bdrequiredFieldMother = cTag('span', {class: "required"});
                                    bdrequiredFieldMother.innerHTML = '*';
                                    birthDateLabelMother.appendChild(bdrequiredFieldMother);
                                    birthDateNameMother.appendChild(birthDateLabelMother);
                                    
                                    birthDateRowMother.appendChild(birthDateNameMother);
                                const birthDateFieldMother = cTag('div', {class: "columnSM8", 'align': "left"});
                                    let inputFieldMother = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "birth_date_mother", id: "birth_date_mother", 'value': '', 'maxlength': 10});
                                    checkDateOnBlur(inputFieldMother,'#error_date','Invalid '+Translate('Birth Date'));
                                    birthDateFieldMother.appendChild(inputFieldMother);
                                    birthDateFieldMother.appendChild(cTag('span',{id:'error_birth_date_mother',class:'errormsg'}));
                                    birthDateRowMother.appendChild(birthDateFieldMother);
                        tab3DivCol7.appendChild(birthDateRowMother);      
                        
                        
                        //########### Maternal No Of Teeth ##############
                        const noTeethDivMother = cTag('div',{ 'class': 'manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const noTeethRowMother = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const noTeethTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const noTeethLabelMother = cTag('label',{ 'for': 'no_teeth_mother','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    noTeethLabelMother.innerHTML = Translate('No Of Teeth');
                                    noTeethTitleMother.appendChild(noTeethLabelMother);
                                    noTeethRowMother.appendChild(noTeethTitleMother);
                                    noTeethDivMother.appendChild(noTeethRowMother);
                                    const noTeethFieldMother = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    noTeethFieldMother.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'no_teeth_mother','id': 'no_teeth_mother','class': 'form-control','value': data.no_teeth_parent }));
                                    noTeethRowMother.appendChild(noTeethFieldMother);
                                    noTeethDivMother.appendChild(noTeethRowMother);
                                    noTeethDivMother.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_no_teeth_mother' }));
                        tab3DivCol7.appendChild(noTeethDivMother);


                        //############ Maternal Physical Condition ###################
                        const physicConditionRowMother = cTag('div',{  'class': 'flex manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const physicConditionTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                                const physicConditionLabelMother = cTag('label',{ 'for': 'physical_condition_parent' });
                                physicConditionLabelMother.innerHTML = Translate('Physical Condition');
                                physicConditionTitleMother.appendChild(physicConditionLabelMother);
                                physicConditionRowMother.appendChild(physicConditionTitleMother);

                                const physicCondAreaMother = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'physical_condition_parent_div'});
                                physicCondAreaMother.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'physical_condition_parent','id': 'physical_condition_parent_ta' }));
                                physicConditionRowMother.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_physical_condition_parent' }));
                                // physicCondAreaMother.innerHTML = data.physical_condition_parent;
                                // physicCondAreaMother.val = data.physical_condition_parent;
                                physicConditionRowMother.appendChild(physicCondAreaMother);
                        tab3DivCol7.appendChild(physicConditionRowMother);


                     
                        // //########### Maternal Calving Count ##############
                        const calvingCountDivMother = cTag('div',{ 'class': 'manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const calvingCountRowMother = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const calvingCountTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const calvingCountLabelMother = cTag('label',{ 'for': 'calving_count','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    calvingCountLabelMother.innerHTML = Translate('Calving Count');
                                    calvingCountTitleMother.appendChild(calvingCountLabelMother);
                                    calvingCountRowMother.appendChild(calvingCountTitleMother);
                                    calvingCountDivMother.appendChild(calvingCountRowMother);
                                    const calvingCountFieldMother = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    calvingCountFieldMother.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'calving_count','id': 'calving_count','class': 'form-control','value': data.calving_count }));
                                    calvingCountRowMother.appendChild(calvingCountFieldMother);
                                    calvingCountDivMother.appendChild(calvingCountRowMother);
                                    calvingCountDivMother.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_calving_count' }));
                        tab3DivCol7.appendChild(calvingCountDivMother);
                        

                        //########### Maternal Weight ##############
                        const birthWeightDivMother = cTag('div',{ 'class': 'manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const birthWeightRowMother = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const birthWeightTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const birthWeightLabelMother = cTag('label',{ 'for': 'birth_weight_mother','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    birthWeightLabelMother.innerHTML = Translate('Weight');
                                    birthWeightTitleMother.appendChild(birthWeightLabelMother);
                                    birthWeightRowMother.appendChild(birthWeightTitleMother);
                                    birthWeightDivMother.appendChild(birthWeightRowMother);
                                    const birthWeightFieldMother = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    birthWeightFieldMother.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'birth_weight_mother','id': 'birth_weight_mother','class': 'form-control','value': data.birth_weight_mother }));
                                    birthWeightRowMother.appendChild(birthWeightFieldMother);
                                    birthWeightDivMother.appendChild(birthWeightRowMother);
                                    birthWeightDivMother.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_birth_weight_mother' }));
                        tab3DivCol7.appendChild(birthWeightDivMother);


                        //########### Maternal Height ##############
                        const birthHeightDivMother = cTag('div',{ 'class': 'manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const birthHeightRowMother = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const birthHeightTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const birthHeightLabelMother = cTag('label',{ 'for': 'birth_height_mother','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    birthHeightLabelMother.innerHTML = Translate('Height');
                                    birthHeightTitleMother.appendChild(birthHeightLabelMother);
                                    birthHeightRowMother.appendChild(birthHeightTitleMother);
                                    birthHeightDivMother.appendChild(birthHeightRowMother);
                                    const birthHeightFieldMother = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    birthHeightFieldMother.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'birth_height_mother','id': 'birth_height_mother','class': 'form-control','value': data.birth_height_mother }));
                                    birthHeightRowMother.appendChild(birthHeightFieldMother);
                                    birthHeightDivMother.appendChild(birthHeightRowMother);
                                    birthHeightDivMother.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_birth_height_mother' }));
                        tab3DivCol7.appendChild(birthHeightDivMother);


                        //############ Metarnal Current Address ###################
                        const currentAddressRowMother = cTag('div',{  'class': 'flex manage_maternal_block_out', 'style': "text-align: left; display:none" });
                        const currentAddressTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                            const currentAddressLabelMother = cTag('label',{ 'for': 'current_address_mother','id': 'current_address_mother' });
                            currentAddressLabelMother.innerHTML = Translate('Current Address');
                            currentAddressTitleMother.appendChild(currentAddressLabelMother);
                            currentAddressRowMother.appendChild(currentAddressTitleMother);

                            const currentAddressAreaMother = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'current_address_mother_div'});
                            currentAddressAreaMother.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'current_address_mother','id': 'current_address_mother_ta' }));
                            currentAddressRowMother.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_current_address_mother' }));
                            // currentAddressAreaMother.innerHTML = data.current_address_mother;
                            // currentAddressAreaMother.val = data.current_address_mother;
                            currentAddressRowMother.appendChild(currentAddressAreaMother);
                        tab3DivCol7.appendChild(currentAddressRowMother);

                      
                        //############ Maternal Animal Description ###################
                        const anmlDescriptionRowMother = cTag('div',{  'class': 'flex manage_maternal_block_out', 'style': "text-align: left; display:none" });
                            const anmlDescriptionTitleMother = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                                const anmlDescriptionLabelMother = cTag('label',{ 'for': 'anml_description_mother','id': 'anml_description_mother' });
                                anmlDescriptionLabelMother.innerHTML = Translate('Description');
                                anmlDescriptionTitleMother.appendChild(anmlDescriptionLabelMother);
                                anmlDescriptionRowMother.appendChild(anmlDescriptionTitleMother);

                                const anmlDescAreaMother = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'anml_description_mother_div'});
                                anmlDescAreaMother.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'anml_description_mother','id': 'anml_description_mother_ta' }));
                                anmlDescAreaMother.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_anml_description_mother' }));
                                // anmlDescAreaMother.innerHTML = data.anml_description;
                                // anmlDescAreaMother.val = data.anml_description;
                                anmlDescriptionRowMother.appendChild(anmlDescAreaMother);
                        tab3DivCol7.appendChild(anmlDescriptionRowMother);



                        divTabs3.appendChild(tab3DivCol7);  

                        let tab3DivCol7_2 = cTag('div',{ 'class': 'columnXS12 columnMD6', 'style': "border:0px solid red; float:right;" });



                        //############ Parent Info Paternal ############ 
                        const fatherNameRow = cTag('div',{ 'class':'flex manage_paternal_block_in', 'style': 'text-align: left;' });
                        const fatherNameTitle = cTag('div',{ 'class':'columnXS12 columnSM4' });
                            let fatherNameLabel = cTag('label',{ 'for':'supplier','data-placement':'bottom' });
                            fatherNameLabel.append(Translate('Father Tag'));

                                let fatherErrSpan = cTag('span', {class: 'required'});
                                fatherErrSpan.innerHTML = '*';
                                fatherNameLabel.appendChild(fatherErrSpan);
                                    
                                fatherNameTitle.appendChild(fatherNameLabel);
                                fatherNameRow.appendChild(fatherNameTitle);
                                //==================================================
                                const fatherNameField = cTag('div',{ 'class':'columnXS12 columnSM8' });
                                    // const fatherInGroup = cTag('div',{ 'class':`input-group`,'id':`supplierNameField` });
                                    fatherNameField.appendChild(cTag('input',{ 'autocomplete':'off','maxlength':'50','type':'text','value':'','required':'','name':'plsproduct','id':'plsproduct','class':'form-control ui-autocomplete-input','placeholder':Translate('Search Father') }));
                                    fatherNameField.appendChild(cTag('input',{ 'type':`hidden`,'name':`plsproduct_id`,'value':`0`,'id':'plsproduct_id' }));
                                        // let fatherNewSpan = cTag('span',{ 'id':'add_new_supplier','data-toggle':`tooltip`,'data-original-title':Translate('Add New Supplier'),'class':`input-group-addon cursor` });
                                        // fatherNewSpan.addEventListener('click',()=>dynamicImport('./Manage_Data.js','addnewsupplierform',['addLiveStocks', 0]))
                                        // fatherNewSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
                                        // fatherInGroup.appendChild(fatherNewSpan);

                                    // fatherNameField.appendChild(fatherInGroup);
                                    fatherNameField.appendChild(cTag('span',{ 'class':'error_msg','id':'errmsg_plsproduct_id' }));
                                    fatherNameRow.appendChild(fatherNameField);
                                const fatherErrColumn = cTag('div',{ 'class':'columnXS12 columnSM6' });
                                fatherErrColumn.appendChild(cTag('span',{ 'class':'error_msg','id':'errmsg_plsproduct' }));
                                fatherNameRow.appendChild(fatherErrColumn);
                         tab3DivCol7_2.appendChild(fatherNameRow);
                        //================================================== 


                        //############# Check Paternal ############
						const paternalDiv = cTag('div',{ 'class': 'LiveStocks manage_paternal_block' });
                            const paternalRow = cTag('div',{ 'class': 'flex', 'style': "margin-bottom: 10px;" });
                                let paternalLabel = cTag('label',{ 'for': 'manage_paternal_block' });
                                    inputField = cTag('input',{ 'type': 'checkbox','name': 'manage_paternal_block','id': 'manage_paternal_block','value': 0 });
                                    // if(data.manage_inventory_count){
                                    //     inputField.setAttribute('checked',true);
                                    // }
                                    inputField.addEventListener('click',checkPaternalBlock);
                                    paternalLabel.appendChild(inputField);
                                    paternalLabel.append(' '+Translate('External Parent'));
                                paternalRow.appendChild(paternalLabel);
                            paternalDiv.appendChild(paternalRow);
                            paternalDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_manage_inventory_count' }));
                        tab3DivCol7_2.appendChild(paternalDiv);


                                      


                        //######## Alternate RFID Tag Paternal ################    
                        const altTagRowPaternal = cTag('div',{ 'class': 'flex manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const altTagTitlePaternal = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                const altTagLabelPaternal = cTag('label',{ 'for': 'alt_tag','id': 'alt_tag' });
                                altTagLabelPaternal.innerHTML = Translate('Alternate/RFID Tag of Father');
                                altTagTitlePaternal.appendChild(altTagLabelPaternal);
                                altTagRowPaternal.appendChild(altTagTitlePaternal);
                            const altTagFieldFather = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            altTagFieldFather.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'alt_tag','id': 'alt_tag','value': data.alt_tag,'maxlength': '150' }));
                            altTagFieldFather.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_alt_tag' }));
                            altTagRowPaternal.appendChild(altTagFieldFather);
                        tab3DivCol7_2.appendChild(altTagRowPaternal);


                        //############## Paternal Breed #################
                        const breedRowPaternal = cTag('div',{ 'class': 'flex manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const breedTitlePaternal = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                const breedLabelPaternal = cTag('label',{ 'for': 'category_id_paternal' });
                                breedLabelPaternal.innerHTML = Translate('Father Breed Name');
                                breedTitlePaternal.appendChild(breedLabelPaternal);
                                breedRowPaternal.appendChild(breedTitlePaternal);
                            const breedDropDownPaternal = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                const breedInGroupPaternal = cTag('div',{ 'class': 'input-group' });
                                    let selectBreedPaternal = cTag('select',{ 'class': 'form-control','name': 'category_id_paternal','id': 'category_id_paternal' });
                                        const breedOptPaternal = cTag('option',{ 'value': '0' });
                                        breedOptPaternal.innerHTML = '';
                                        selectBreedPaternal.appendChild(breedOptPaternal);
                                    setOptions(selectBreedPaternal, data.breedOpt, 1, 1);                      
                                    breedInGroupPaternal.appendChild(selectBreedPaternal);
                                    breedInGroupPaternal.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '35','name': 'category_name_paternal','id': 'category_name_paternal','class': 'form-control',style:'display:none'}));
                                    let breedSpanPaternal = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Category')});
                                    breedSpanPaternal.append(cTag('i', {'class':'fa fa-plus'}), ' ', Translate('New'));
                                    breedInGroupPaternal.appendChild(breedSpanPaternal);
                                    breedDropDownPaternal.appendChild(breedInGroupPaternal);
                                    breedDropDownPaternal.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_category_id_paternal' }));
                                breedRowPaternal.appendChild(breedDropDownPaternal);
                        tab3DivCol7_2.appendChild(breedRowPaternal);


                        //############# Paternal Livestock Name ################
                        const paternalNameRow = cTag('div',{ 'class': 'flex manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const paternalNameTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                const paternalNameLabel = cTag('label',{ 'for': 'father_name','id': 'lbfather_name' });
                                paternalNameLabel.innerHTML = Translate('Father Name');
                                    requireSpan = cTag('span',{ 'class': 'required' });
                                    requireSpan.innerHTML = '*';
                                    paternalNameLabel.appendChild(requireSpan);
                                paternalNameTitle.appendChild(paternalNameLabel);
                                paternalNameRow.appendChild(paternalNameTitle);
                                const paternalNameField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                paternalNameField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'father_name','id': 'father_name','value': '','maxlength': '100' }));
                                paternalNameField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_father_name' }));
                                paternalNameRow.appendChild(paternalNameField);
                        tab3DivCol7_2.appendChild(paternalNameRow);


                        //############## Paternal Cattle Color ###################
                        const colorDivFather = cTag('div',{ 'class': 'displayNotAll manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const colorRowFather = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const colorTitleFather = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const colorLabelFather = cTag('label',{ 'for': 'colour_name' });
                                    colorLabelFather.innerHTML = Translate('Color Name');
                                    colorTitleFather.appendChild(colorLabelFather);
                                colorRowFather.appendChild(colorTitleFather);
                            colorDivFather.appendChild(colorRowFather);
                        const colorNameDropDownFather = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                            const colorNameInGroupFather = cTag('div',{ 'class': 'input-group' });
                                let selectColorFather = cTag('select',{ 'class': 'form-control','name': 'colour_name_father','id': 'colour_name_father' });
                                selectColorFather.appendChild(cTag('option',{ 'value': '' }));
                                setOptions(selectColorFather, data.colNamOpt, 0, 1);                             
                                colorNameInGroupFather.appendChild(selectColorFather);
                                colorNameInGroupFather.appendChild(cTag('input',{ 'type': 'text','value': '','maxlength': '15','name': 'colour_name_father2','id': 'colour_name_father2','class': 'form-control', 'style': 'display:none'}));
                                    let newColorSpanFather = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add New Color Name'),'class': 'input-group-addon cursor showNewInputOrSelect' });
                                    newColorSpanFather.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                                    colorNameInGroupFather.appendChild(newColorSpanFather);
                                colorNameDropDownFather.appendChild(colorNameInGroupFather);
                            colorRowFather.appendChild(colorNameDropDownFather);
                        colorDivFather.appendChild(colorRowFather);
                        colorDivFather.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_colour_name' }));
                        tab3DivCol7_2.appendChild(colorDivFather);
                        

                        //########## Paternal Birth Date ############        
                        const birthDateRowFather = cTag('div', {class: 'flex manage_paternal_block_out', 'style': "text-align: left; display:none" });
                                const birthDateNameFather = cTag('div', {class: "columnSM4", 'align': "left"});
                                    const birthDateLabelFather = cTag('label', {'for': "birth_date_father"});
                                    birthDateLabelFather.innerHTML = Translate('Birth Date');

                                    let bdrequiredFieldFather = cTag('span', {class: "required"});
                                    bdrequiredFieldFather.innerHTML = '*';
                                    birthDateLabelFather.appendChild(bdrequiredFieldFather);
                                    birthDateNameFather.appendChild(birthDateLabelFather);
                                    
                                    birthDateRowFather.appendChild(birthDateNameFather);
                                const birthDateFieldFather = cTag('div', {class: "columnSM8", 'align': "left"});
                                    let inputFieldFather = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "birth_date_father", id: "birth_date_father", 'value': '', 'maxlength': 10});
                                    checkDateOnBlur(inputFieldFather,'#error_date','Invalid '+Translate('Birth Date'));
                                    birthDateFieldFather.appendChild(inputFieldFather);
                                    birthDateFieldFather.appendChild(cTag('span',{id:'error_birth_date_father',class:'errormsg'}));
                                    birthDateRowFather.appendChild(birthDateFieldFather);
                        tab3DivCol7_2.appendChild(birthDateRowFather);     
                        
                        
                        //########### Paternal No Of Teeth ##############
                        const noTeethDivFather = cTag('div',{ 'class': 'manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const noTeethRowFather = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const noTeethTitleFather = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const noTeethLabelFather = cTag('label',{ 'for': 'no_teeth_father','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    noTeethLabelFather.innerHTML = Translate('No Of Teeth');
                                    noTeethTitleFather.appendChild(noTeethLabelFather);
                                    noTeethRowFather.appendChild(noTeethTitleFather);
                                    noTeethDivFather.appendChild(noTeethRowFather);
                                    const noTeethFieldFather = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    noTeethFieldFather.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'no_teeth_father','id': 'no_teeth_Father','class': 'form-control','value': data.no_teeth_parent }));
                                    noTeethRowFather.appendChild(noTeethFieldFather);
                                    noTeethDivFather.appendChild(noTeethRowFather);
                                    noTeethDivFather.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_no_teeth_father' }));
                        tab3DivCol7_2.appendChild(noTeethDivFather);


                        //############ Maternal Physical Condition ###################
                        const physicConditionRowFather = cTag('div',{  'class': 'flex manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const physicConditionTitleFather = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                                const physicConditionLabelFather = cTag('label',{ 'for': 'physical_condition_parent' });
                                physicConditionLabelFather.innerHTML = Translate('Physical Condition');
                                physicConditionTitleFather.appendChild(physicConditionLabelFather);
                                physicConditionRowFather.appendChild(physicConditionTitleFather);

                                const physicCondAreaFather = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'physical_condition_parent_div'});
                                physicCondAreaFather.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'physical_condition_parent','id': 'physical_condition_parent_ta' }));
                                physicConditionRowFather.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_physical_condition_parent' }));
                                // physicCondAreaFather.innerHTML = data.physical_condition_parent;
                                // physicCondAreaFather.val = data.physical_condition_parent;
                                physicConditionRowFather.appendChild(physicCondAreaFather);
                                tab3DivCol7_2.appendChild(physicConditionRowFather);
                        

                        //########### Paternal Weight ##############
                        const birthWeightDivFather = cTag('div',{ 'class': 'manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const birthWeightRowFather = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const birthWeightTitleFather = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const birthWeightLabelFather = cTag('label',{ 'for': 'birth_weight_father','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    birthWeightLabelFather.innerHTML = Translate('Weight');
                                    birthWeightTitleFather.appendChild(birthWeightLabelFather);
                                    birthWeightRowFather.appendChild(birthWeightTitleFather);
                                    birthWeightDivFather.appendChild(birthWeightRowFather);
                                    const birthWeightFieldFather = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    birthWeightFieldFather.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'birth_weight_father','id': 'birth_weight_father','class': 'form-control','value': data.birth_weight_father }));
                                    birthWeightRowFather.appendChild(birthWeightFieldFather);
                                    birthWeightDivFather.appendChild(birthWeightRowFather);
                                    birthWeightDivFather.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_birth_weight_father' }));
                        tab3DivCol7_2.appendChild(birthWeightDivFather);


                        //########### Paternal Height ##############
                        const birthHeightDivFather = cTag('div',{ 'class': 'manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const birthHeightRowFather = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const birthHeightTitleFather = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const birthHeightLabelFather = cTag('label',{ 'for': 'birth_height_father','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    birthHeightLabelFather.innerHTML = Translate('Height');
                                    birthHeightTitleFather.appendChild(birthHeightLabelFather);
                                    birthHeightRowFather.appendChild(birthHeightTitleFather);
                                    birthHeightDivFather.appendChild(birthHeightRowFather);
                                    const birthHeightFieldFather = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    birthHeightFieldFather.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'birth_height_father','id': 'birth_height_father','class': 'form-control','value': data.birth_height_father }));
                                    birthHeightRowFather.appendChild(birthHeightFieldFather);
                                    birthHeightDivFather.appendChild(birthHeightRowFather);
                                    birthHeightDivFather.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_birth_height_father' }));
                        tab3DivCol7_2.appendChild(birthHeightDivFather);


                        //############ Metarnal Current Address ###################
                        const currentAddressRowFather = cTag('div',{  'class': 'flex manage_paternal_block_out', 'style': "text-align: left; display:none" });
                        const currentAddressTitleFather = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                            const currentAddressLabelFather = cTag('label',{ 'for': 'current_address_father','id': 'current_address_father' });
                            currentAddressLabelFather.innerHTML = Translate('Current Address');
                            currentAddressTitleFather.appendChild(currentAddressLabelFather);
                            currentAddressRowFather.appendChild(currentAddressTitleFather);

                            const currentAddressAreaFather = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'current_address_father_div'});
                            currentAddressAreaFather.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'current_address_father','id': 'current_address_father_ta' }));
                            currentAddressRowFather.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_current_address_father' }));
                            // currentAddressAreaFather.innerHTML = data.current_address_father;
                            // currentAddressAreaFather.val = data.current_address_father;
                            currentAddressRowFather.appendChild(currentAddressAreaFather);
                        tab3DivCol7_2.appendChild(currentAddressRowFather);


                        //############ Paternal Animal Description ###################
                        const anmlDescriptionRowFather = cTag('div',{  'class': 'flex manage_paternal_block_out', 'style': "text-align: left; display:none" });
                            const anmlDescriptionTitleFather = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                                const anmlDescriptionLabelFather = cTag('label',{ 'for': 'anml_description_father','id': 'anml_description_father' });
                                anmlDescriptionLabelFather.innerHTML = Translate('Description');
                                anmlDescriptionTitleFather.appendChild(anmlDescriptionLabelFather);
                                anmlDescriptionRowFather.appendChild(anmlDescriptionTitleFather);

                                const anmlDescAreaFather = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'anml_description_father_div'});
                                anmlDescAreaFather.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'anml_description_father','id': 'anml_description_father_ta' }));
                                anmlDescAreaFather.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_anml_description_father' }));
                                // anmlDescAreaFather.innerHTML = data.anml_description;
                                // anmlDescAreaFather.val = data.anml_description;
                                anmlDescriptionRowFather.appendChild(anmlDescAreaFather);
                        tab3DivCol7_2.appendChild(anmlDescriptionRowFather);



                        
                        divTabs3.appendChild(tab3DivCol7_2);  

                    divTabs.appendChild(divTabs3);

                    //======divTabs3 End======//



                    //======divTabs4 Start======//
                    let divTabs4 = cTag('div',{ 'class': 'columnXS12','id': 'tabs-4'});
                   
                        let tab4DivCol7 = cTag('div',{ 'class': 'columnXS12 columnMD7' });


                        //########## Wean Date ############        
                        const weanDateRow = cTag('div', {class: "flex"});
                                const weanDateName = cTag('div', {class: "columnSM4", 'align': "left"});
                                    const weanDateLabel = cTag('label', {'for': "wean_date"});
                                    weanDateLabel.innerHTML = Translate('Wean Date');

                                    let wdrequiredField = cTag('span', {class: "required"});
                                    wdrequiredField.innerHTML = '*';
                                    weanDateLabel.appendChild(wdrequiredField);
                                    weanDateName.appendChild(weanDateLabel);
                                    
                                    weanDateRow.appendChild(weanDateName);
                                const weanDateField = cTag('div', {class: "columnSM8", 'align': "left"});
                                    inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "wean_date", id: "wean_date", 'value': '', 'maxlength': 10});
                                    checkDateOnBlur(inputField,'#error_date','Invalid '+Translate('Birth Date'));
                                    weanDateField.appendChild(inputField);
                                    weanDateField.appendChild(cTag('span',{id:'error_wean_date',class:'errormsg'}));
                                    weanDateRow.appendChild(weanDateField);
                        tab4DivCol7.appendChild(weanDateRow);                        


                        //########### Wean Weight ##############
                        const weanWeightDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            const weanWeightRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const weanWeightTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const weanWeightLabel = cTag('label',{ 'for': 'wean_weight','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    weanWeightLabel.innerHTML = Translate('Wean Weight');
                                    weanWeightTitle.appendChild(weanWeightLabel);
                                    weanWeightRow.appendChild(weanWeightTitle);
                                    weanWeightDiv.appendChild(weanWeightRow);
                                    const weanWeightField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    weanWeightField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'wean_weight','id': 'wean_weight','class': 'form-control','value': data.wean_weight }));
                                    weanWeightRow.appendChild(weanWeightField);
                                    weanWeightDiv.appendChild(weanWeightRow);
                                    weanWeightDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_wean_weight' }));
                        tab4DivCol7.appendChild(weanWeightDiv);


                        //########### Wean Avg. Daily Gain ##############
                        const weanADGDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            const weanADGRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const weanADGTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const weanADGLabel = cTag('label',{ 'for': 'wean_avg_daily_gain','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select pp.') });
                                    weanADGLabel.innerHTML = Translate('Wean Avg. Daily Gain');
                                    weanADGTitle.appendChild(weanADGLabel);
                                    weanADGRow.appendChild(weanADGTitle);
                                    weanADGDiv.appendChild(weanADGRow);
                                    const weanADGField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    weanADGField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'wean_avg_daily_gain','id': 'wean_avg_daily_gain','class': 'form-control','value': data.wean_avg_daily_gain }));
                                    weanADGRow.appendChild(weanADGField);
                                    weanADGDiv.appendChild(weanADGRow);
                                    weanADGDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_wean_avg_daily_gain' }));
                        tab4DivCol7.appendChild(weanADGDiv);


                        //########### Creep Feed Days ##############
                        const creepFeedDaysDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            const creepFeedDaysRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const creepFeedDaysTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const creepFeedDaysLabel = cTag('label',{ 'for': 'creep_feed_days','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select pp.') });
                                    creepFeedDaysLabel.innerHTML = Translate('Creep Feed Days');
                                    creepFeedDaysTitle.appendChild(creepFeedDaysLabel);
                                    creepFeedDaysRow.appendChild(creepFeedDaysTitle);
                                    creepFeedDaysDiv.appendChild(creepFeedDaysRow);
                                    const creepFeedDaysField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    creepFeedDaysField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'creep_feed_days','id': 'creep_feed_days','class': 'form-control','value': data.creep_feed_days }));
                                    creepFeedDaysRow.appendChild(creepFeedDaysField);
                                    creepFeedDaysDiv.appendChild(creepFeedDaysRow);
                                    creepFeedDaysDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_creep_feed_days' }));
                        tab4DivCol7.appendChild(creepFeedDaysDiv);



                        
                        divTabs4.appendChild(tab4DivCol7); 

                    divTabs.appendChild(divTabs4);




                    //======divTabs5 Start (Birth Tab)======//
                    /* let divTabs5 = cTag('div',{ 'class': 'columnXS12','id': 'tabs-5' });
                   
                        let tab5DivCol7 = cTag('div',{ 'class': 'columnXS12 columnMD7' });

                        
                        //########## Birth Date ############        
                        const birthDateRow = cTag('div', {class: "flex"});
                                const birthDateName = cTag('div', {class: "columnSM4", 'align': "left"});
                                    const birthDateLabel = cTag('label', {'for': "birth_date"});
                                    birthDateLabel.innerHTML = Translate('Birth Date');

                                    let bdrequiredField = cTag('span', {class: "required"});
                                    bdrequiredField.innerHTML = '*';
                                    birthDateLabel.appendChild(bdrequiredField);
                                        birthDateName.appendChild(birthDateLabel);
                                    
                                    birthDateRow.appendChild(birthDateName);
                                const birthDateField = cTag('div', {class: "columnSM8", 'align': "left"});
                                    inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "birth_date", id: "birth_date", 'value': '', 'maxlength': 10});
                                    checkDateOnBlur(inputField,'#error_date','Invalid '+Translate('Birth Date'));
                                    birthDateField.appendChild(inputField);
                                    birthDateField.appendChild(cTag('span',{id:'error_birth_date',class:'errormsg'}));
                                    birthDateRow.appendChild(birthDateField);
                        tab5DivCol7.appendChild(birthDateRow);                        


                        //########### Birth Weight ##############
                        const birthWeightDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            const birthWeightRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const birthWeightTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const birthWeightLabel = cTag('label',{ 'for': 'birth_weight','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select the internal memory capacity of the device you are entering.') });
                                    birthWeightLabel.innerHTML = Translate('Birth Weight');
                                    birthWeightTitle.appendChild(birthWeightLabel);
                                    birthWeightRow.appendChild(birthWeightTitle);
                                    birthWeightDiv.appendChild(birthWeightRow);
                                    const birthWeightField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    birthWeightField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'birth_weight','id': 'birth_weight','class': 'form-control','value': data.birth_weight }));
                                    birthWeightRow.appendChild(birthWeightField);
                                    birthWeightDiv.appendChild(birthWeightRow);
                                    birthWeightDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_birth_weight' }));
                        tab5DivCol7.appendChild(birthWeightDiv);


                        //############ Birth Type ################        
                        const birthTypeDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            const birthTypeRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const birthTypeTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const birthTypeLabel = cTag('label',{ 'for': 'birth_type' });
                                    birthTypeLabel.innerHTML = Translate('Birth Type');
                                    birthTypeTitle.appendChild(birthTypeLabel);
                                    birthTypeRow.appendChild(birthTypeTitle);
                                    birthTypeDiv.appendChild(birthTypeRow);
                                const birthTypeDropDown = cTag('div',{ 'class': 'columnXS12 columnSM8' });
                                    const birthTypeInGroup = cTag('div',{ 'class': 'input-group' });
                                        let birthTypePurpose = cTag('select',{ 'class': 'form-control','name': 'birth_type','id': 'birth_type' });
                                        birthTypePurpose.appendChild(cTag('option',{ 'value': '' }));
                                        setOptions(birthTypePurpose, data.birthtypeOpt, 0, 1);                             
                                        birthTypeInGroup.appendChild(birthTypePurpose);
                                        let birthTypeSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add Arrival Type'),'class': 'input-group-addon cursor showNewInputOrSelect' });
                                        birthTypeSpan.append(cTag('i',{ 'class': 'fa fa-plus' }), ' ', Translate('New'));
                                        birthTypeInGroup.appendChild(birthTypeSpan);
                                        birthTypeDropDown.appendChild(birthTypeInGroup);
                                        birthTypeRow.appendChild(birthTypeDropDown);
                                    birthTypeDiv.appendChild(birthTypeRow);
                                birthTypeDiv.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_birth_type' }));
                        tab5DivCol7.appendChild(birthTypeDiv);
                        

                        //############ Calving Assist Reason ###################
                        const clavingAssReasonRow = cTag('div',{  'class': 'flex', 'style': "text-align: left;" });
                        const clavingAssReasonTitle = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                            const clavingAssReasonLabel = cTag('label',{ 'for': 'calving_assist_reason','id': 'calving_assist_reason' });
                            clavingAssReasonLabel.innerHTML = Translate('Calving Assist Reason');
                            clavingAssReasonTitle.appendChild(clavingAssReasonLabel);
                            clavingAssReasonRow.appendChild(clavingAssReasonTitle);

                            const clavingAssReasonArea = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'calving_assist_reason_div'});
                            clavingAssReasonArea.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'calving_assist_reason','id': 'calving_assist_reason_ta' }));
                            anmlDescArea.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_calving_assist_reason' }));
                            // clavingAssReasonArea.innerHTML = data.calving_assist_reason;
                            // clavingAssReasonArea.val = data.calving_assist_reason;
                            clavingAssReasonRow.appendChild(clavingAssReasonArea);
                        tab5DivCol7.appendChild(clavingAssReasonRow);


                        //########### Calving Number ##############
                        const calvingNoDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            const calvingNoRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const calvingNoTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const calvingNoLabel = cTag('label',{ 'for': 'calving_no','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select pp.') });
                                    calvingNoLabel.innerHTML = Translate('Calving No.');
                                    calvingNoTitle.appendChild(calvingNoLabel);
                                    calvingNoRow.appendChild(calvingNoTitle);
                                    calvingNoDiv.appendChild(calvingNoRow);
                                    const calvingNoField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    calvingNoField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'calving_no','id': 'calving_no','class': 'form-control','value': data.calving_no }));
                                    calvingNoRow.appendChild(calvingNoField);
                                    calvingNoDiv.appendChild(calvingNoRow);
                                    calvingNoDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_calving_no' }));
                        tab5DivCol7.appendChild(calvingNoDiv);


                        //########### Sibling Count ##############
                        const siblingCountDiv = cTag('div',{ 'class': 'displayNotAll LiveStocks' });
                            const siblingCountRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
                                const siblingCountTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
                                    const siblingCount = cTag('label',{ 'for': 'sibling_count','data-toggle': 'tooltip','data-placement': 'bottom','title': Translate('Select pp.') });
                                    siblingCount.innerHTML = Translate('Sibling Count');
                                    siblingCountTitle.appendChild(siblingCount);
                                    siblingCountRow.appendChild(siblingCountTitle);
                                    siblingCountDiv.appendChild(siblingCountRow);
                                    const siblingCountField = cTag('div',{ 'class': 'columnXS12 columnSM8','align': 'left','id': 'parentstorage' });
                                    siblingCountField.appendChild(cTag('input',{ 'maxlength': '6','type': 'text','name': 'sibling_count','id': 'sibling_count','class': 'form-control','value': data.sibling_count }));
                                    siblingCountRow.appendChild(siblingCountField);
                                    siblingCountDiv.appendChild(siblingCountRow);
                                    siblingCountDiv.appendChild(cTag('span',{ 'class': 'errormsg','id': 'errmsg_sibling_count' }));
                        tab5DivCol7.appendChild(siblingCountDiv);


                        //############ Birth Location ###################
                        const birthLocationRow = cTag('div',{  'class': 'flex', 'style': "text-align: left;" });
                        const birthLocationTitle = cTag('div',{ 'class': 'columnXS12 columnSM4'});
                            const birthLocationLabel = cTag('label',{ 'for': 'birth_location','id': 'birth_location' });
                            birthLocationLabel.innerHTML = Translate('Birth Location');
                            birthLocationTitle.appendChild(birthLocationLabel);
                            birthLocationRow.appendChild(birthLocationTitle);

                            const birthLocationArea = cTag('div',{ 'class': 'columnXS12 columnSM8' , 'id':'birth_location_div'});
                            birthLocationArea.appendChild(cTag('textarea',{ 'rows': '4','cols': '20', 'class': 'form-control','name': 'birth_location','id': 'birth_location_ta' }));
                            anmlDescArea.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_birth_location' }));
                            // birthLocationArea.innerHTML = data.birth_location;
                            // birthLocationArea.val = data.birth_location;
                            birthLocationRow.appendChild(birthLocationArea);
                        tab5DivCol7.appendChild(birthLocationRow);


                        //############ Child Information ################  
                        const parentsInfoRow = cTag('div',{ 'class':`flex`, 'style': "text-align: left;" });
                        const parentsInfoTitle = cTag('div',{ 'class':`columnXS12 columnSM4` });
                            let parentsInfoLabel = cTag('label',{ 'for':`supplier_name`,'data-placement':`bottom` });
                            parentsInfoLabel.append(Translate('Child Info'));
                                    let pierrorSpan = cTag('span', {class: "errormsg"});
                                    pierrorSpan.innerHTML = '*';
                                    parentsInfoLabel.appendChild(pierrorSpan);
                                parentsInfoTitle.appendChild(parentsInfoLabel);
                            parentsInfoRow.appendChild(parentsInfoTitle);
                            const parentsInfoField = cTag('div',{ 'class':`columnXS12 columnSM8` });
                                const parentsInfoInGroup = cTag('div',{ 'class':`input-group`,'id':`supplierNameField` });
                                    const parentsInfoInName = cTag('input',{ 'autocomplete':`off`,'maxlength':`50`,'type':`text`,'value':``,'required':``,'name':`supplier_name`,'id':`supplier_name`,'class':`form-control ui-autocomplete-input`,'placeholder':Translate('Search Child') });
                                    parentsInfoInName.addEventListener('blur',updateSupplierId);
                                    parentsInfoInGroup.appendChild(parentsInfoInName);
                                    let pinewSpan = cTag('span',{ 'id':'add_new_customer_btn','data-toggle':`tooltip`,'data-original-title':Translate('Add New Supplier'),'class':`input-group-addon cursor` });
                                    pinewSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
                                    parentsInfoInGroup.appendChild(pinewSpan);
                                parentsInfoField.appendChild(parentsInfoInGroup);
                                parentsInfoField.appendChild(cTag('input',{ 'type':`hidden`,'name':`supplier_id`,'id':`supplier_id`,'value':`0` }));
                                parentsInfoField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_supplier_id` }));
                            parentsInfoRow.appendChild(parentsInfoField);
                            const pierrorColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
                            pierrorColumn.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_customer_name` }));
                            parentsInfoRow.appendChild(pierrorColumn);
                        tab5DivCol7.appendChild(parentsInfoRow);


                        divTabs5.appendChild(tab5DivCol7);  

                    divTabs.appendChild(divTabs5);   */

                    // if(data.customFieldsData.length>0){
                        // let divTabs3 = cTag('div',{ 'class': 'columnXS12','id': 'tabs-3',style:'display:none' });
                        // generateCustomeFields(divTabs3,data.customFieldsData);
                        // divTabs.appendChild(divTabs3);
                    // }

                    //======divTabs5 End (Birth Tab)======//                 




                formFields2.appendChild(divTabs);
                productForm.appendChild(formFields2);
                
				productForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'frompage','id': 'frompage','value': frompage }));
                productForm.appendChild(cTag('input',{ 'type': 'hidden','name': 'product_id','id': 'product_id','value': data.product_id }));
                productForm.appendChild(cTag('input',{ 'type': 'submit',style:'display:none' }));
                formDialog.appendChild(productForm);

			popup_dialog1000(Translate('Livestock Information'),formDialog,(hidePopup)=>AJsave_Livestocks(hidePopup,addCartCBF));



            // setTimeout(function() {
            //     document.getElementById("supplier").focus();
            //     alert('t1');
            // }, 500);
            // AJautoComplete('supplier')
            // AJ_add_MoreInfo();

            //OK
            // if(document.querySelector("#supplier")){AJautoComplete('supplier');}

            if(document.querySelector("#supplier")){
                
                AJautoComplete('supplier');

                // let queryString = location.search;
                // let params = new URLSearchParams(queryString);
                // let po_datetime = DBDateRangeToViewDate(params.get("po_datetime"));
                // let supplier = params.get("supplier");
                            
            
                document.getElementById("supplier").addEventListener('keyup', e => {
                    if(document.getElementById("suppliers_id")){
                        document.getElementById("suppliers_id").value = 0;
                    }
                });
            }


            
            if(document.querySelector("#product")){
                
                AJautoComplete('product');
            
                document.getElementById("supplier").addEventListener('keyup', e => {
                    if(document.getElementById("suppliers_id")){
                        document.getElementById("suppliers_id").value = 0;
                    }
                });

            }


            if(document.querySelector("#lsproduct")){
                
                AJautoComplete('lsproduct');
            
                document.getElementById("supplier").addEventListener('keyup', e => {
                    if(document.getElementById("suppliers_id")){
                        document.getElementById("suppliers_id").value = 0;
                    }
                });

            }

            if(document.querySelector("#plsproduct")){
                
                AJautoComplete('plsproduct');
            
                document.getElementById("supplier").addEventListener('keyup', e => {
                    if(document.getElementById("suppliers_id")){
                        document.getElementById("suppliers_id").value = 0;
                    }
                });

            }

            // AJ_add_MoreInfo()


			setTimeout(function() {
                
				if(parseInt(document.getElementById("product_id").value)===0){
					document.getElementById("product_type").focus();
                    document.getElementById("manage_inventory_count").checked = true;
				}
				else{
					document.getElementById("product_name").value = data.product_name;
					document.getElementById("product_name").focus();
					if(similarproduct===1){
						document.getElementById("product_id").value=0;
						document.getElementById("sku").value='';
					}
				}

                date_picker('#arrival_date');
                date_picker('#birth_date_mother');
                date_picker('#birth_date_father');
                date_picker('#wean_date');

				document.getElementById("tag").value = data.tag;
				document.getElementById("category_id").value = data.category_id;
				document.getElementById("location_id").value = data.location_id;
				document.getElementById("group_id").value = data.group_id;
				document.getElementById("classification_id").value = data.classification_id;
				document.getElementById("purpose").value = data.purpose;
				document.getElementById("arrival_type").value = data.arrival_type;
				// document.getElementById("birth_type").value = data.birth_type;
				document.getElementById("colour_name").value = data.colour_name;
				document.getElementById("tag_color").value = data.tag_color;
				document.getElementById("alt_tag").value = data.alt_tag;
				document.getElementById("anml_description_ta").innerHTML  = data.anml_description;
				document.getElementById("arrival_note_ta").innerHTML  = data.arrival_note;
				document.getElementById("lsproduct_id").innerHTML  = data.lsproduct_id;
				document.getElementById("lsproduct").innerHTML  = data.lsproduct;
				// document.getElementById("calving_assist_reason_ta").innerHTML  = data.calving_assist_reason;
				// document.getElementById("birth_location_ta").innerHTML  = data.birth_location;
				document.getElementById("supplier").value  = data.supplier;
                // console.log(data.anml_description);
				// document.getElementById("manufacturer_id").value = data.manufacturer_id;
				// document.getElementById("physical_condition_name").value = data.physical_condition_name;

                if(data.gender_id==1){
                    // genderInGroup.setAttribute('checked',true);
                    document.getElementById("gender_id_m").checked = true;
                }else if(data.gender_id==2){
                    document.getElementById("gender_id_f").checked = true;
                }
				
				if(data.customFieldsData.length>0 && document.getElementsByClassName("DateField").length>0){					
					date_picker('.DateField');                    
				}

                if(data.arrival_date != null){
                    document.getElementById("arrival_date").value = DBDateToViewDate(data.arrival_date);                    
                }

                // if(data.birth_date != null){
                //     document.getElementById("birth_date").value = DBDateToViewDate(data.birth_date);                    
                // }
                if(data.birth_date_mother != null){
                    document.getElementById("birth_date_mother").value = DBDateToViewDate(data.birth_date_mother);                    
                }
                if(data.birth_date_father != null){
                    document.getElementById("birth_date_father").value = DBDateToViewDate(data.birth_date_father);                    
                }

                if(data.wean_date != null){
                    document.getElementById("wean_date").value = DBDateToViewDate(data.wean_date);                    
                }
			
				checkManageInventory();
				checkLivestockType();
				document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));

				let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#";
				document.getElementById("sku").addEventListener('keyup', e=>{
					let sku = e.target.value.toUpperCase().replace(' ', '-');
					let IsNumber = true;
					let Char, i;
					let newsku = '';
					for ( i = 0; i < sku.length && IsNumber === true; i++){ 
						Char = sku.charAt(i); 
						if (ValidChars.indexOf(Char) === -1){}
						else{
							newsku = newsku+Char;
						}
					}
					if(sku.length> newsku.length || e.target.value !== newsku){
						document.getElementById("sku").value = newsku;
					}
				});
				if(document.querySelectorAll(".showNewInputOrSelect")){
					document.querySelectorAll(".showNewInputOrSelect").forEach(oneClassObj=>{
						oneClassObj.addEventListener('click', showNewInputOrSelect);
					});
				}
                applySanitizer(formDialog);
			}, 500);

           
            
		}
        
    }
    
	return true;
}


 //#################
 async function AJ_add_MoreInfo(){
    const url = '/'+segment1+'/AJ_add_MoreInfo';
    fetchData(afterFetch,url,{});

    function afterFetch(data){
        // alert('test');
        const supplier_id = document.querySelector('#supplier_id')
        if(data.suppliers_id>0){
            if(data.supplier_name !== ''){
                supplier_id.appendChild(cTag('input',{ 'readonly':'','type':`text`,'value':data.supplier_name,'required':``,'name':`supplier_name`,'id':`supplier_name`,'class':`form-control`,'placeholder':Translate('Type Supplier Name') }));
                supplier_id.appendChild(cTag('input',{ 'type':`hidden`,'name':`supplier_id`,'id':`supplier_id`,'value':data.suppliers_id }));
            }
        }
        else{
                const option = cTag('option',{'value':''});
                option.innerHTML = Translate('Select Supplier');
            supplier_id.appendChild(option);
            setOptions(supplier_id,data.supOpt,1,1);	
        }			
        document.querySelector('#date_expected').value = DBDateToViewDate(data.date_expected);
        document.querySelector('#invoice_date').value = DBDateToViewDate(data.invoice_date);
        document.querySelector('#date_paid').value = DBDateToViewDate(data.date_paid);

        if (document.querySelector("#date_expected") && document.querySelector("#invoice_date") && document.querySelector("#date_paid")) {
            date_picker("#date_expected");
            date_picker("#invoice_date");
            date_picker("#date_paid");
        }
    }
}


//###############

// async function AJ_add_MoreInfo(){
//     const url = '/'+segment1+'/AJ_add_MoreInfo';
//     fetchData(afterFetch,url,{});

//     function afterFetch(data){
//         document.querySelector('#add_new_supplier').addEventListener('click',()=>dynamicImport('./Customers.js','AJget_CustomersPopup',[0]));
//         const salesman_id = document.querySelector('#salesman_id');
//         setOptions(salesman_id,data.salManOpt,1,1);
//         salesman_id.value = data.salesman_id;
//     }
// }

async function AJsave_Livestocks(hidePopup,addCartCBF){
	let oField, oElement, labelStr;
	oField = document.getElementById('product_type');
	let errmsg_sku = document.getElementById('errmsg_sku');
	// oElement = document.getElementById('errmsg_minimum_price');
	// oElement.innerHTML = "";

    let pTag;

    oField = document.querySelector('#frmproduct #tag');
	// oElement = document.getElementById('errmsg_tag');
	// oElement.innerHTML = "";
	if(oField.value === ""){
		// oElement.innerHTML = Translate('Missing tag');
		document.querySelector("#tabs").activateTab(0);
		oField.focus();
        oField.classList.add('errorFieldBorder');
		return(false);
	}
    else {
        oField.classList.remove('errorFieldBorder');
    }


    // if(arrival_date.value===''){
    //     pTag = cTag('p', {'style': "margin: 0;"});
    //     pTag.innerHTML = Translate('Missing Arrival Date');
    //     error_date.appendChild(pTag);
    //     arrival_date.focus();
    //     arrival_date.classList.add('errorFieldBorder');
    //     return false;
    // }else{
    //     arrival_date.classList.remove('errorFieldBorder');
    // }

    // if(birth_date.value===''){
    //     pTag = cTag('p', {'style': "margin: 0;"});
    //     pTag.innerHTML = Translate('Missing Birth Date');
    //     error_date.appendChild(pTag);
    //     birth_date.focus();
    //     birth_date.classList.add('errorFieldBorder');
    //     return false;
    // }else{
    //     birth_date.classList.remove('errorFieldBorder');
    // }

    if(birth_date_mother.value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Mother Birth Date');
        error_date.appendChild(pTag);
        birth_date_mother.focus();
        birth_date_mother.classList.add('errorFieldBorder');
        return false;
    }else{
        birth_date_mother.classList.remove('errorFieldBorder');
    }

    if(birth_date_father.value===''){
        pTag = cTag('p', {'style': "margin: 0;"});
        pTag.innerHTML = Translate('Missing Father Birth Date');
        error_date.appendChild(pTag);
        birth_date_father.focus();
        birth_date_father.classList.add('errorFieldBorder');
        return false;
    }else{
        birth_date_father.classList.remove('errorFieldBorder');
    }


    // if(wean_date.value===''){
    //     pTag = cTag('p', {'style': "margin: 0;"});
    //     pTag.innerHTML = Translate('Missing Wean Date');
    //     error_date.appendChild(pTag);
    //     wean_date.focus();
    //     wean_date.classList.add('errorFieldBorder');
    //     return false;
    // }else{
    //     wean_date.classList.remove('errorFieldBorder');
    // }
    
			
	oField = document.querySelector('#frmproduct #product_name');
	oElement = document.getElementById('errmsg_product_name');
	oElement.innerHTML = "";
	if(oField.value === ""){
		oElement.innerHTML = Translate('Missing product name');
		document.querySelector("#tabs").activateTab(0);
		oField.focus();
        oField.classList.add('errorFieldBorder');
		return(false);
	}
    else {
        oField.classList.remove('errorFieldBorder');
    }


    

	let regular_price = document.getElementById('regular_price');
    // if(!regular_price.valid()) return;

	let minimum_price = document.getElementById('minimum_price');
    // if(!minimum_price.valid()) return;	

    let validCustomFields = validifyCustomField(2);
	if(!validCustomFields) return;

    actionBtnClick('.btnmodel', Translate('Saving'), 1);
	
	if(document.frmproduct.product_id.value===0){labelStr = Translate('Add');}
	else{labelStr = Translate('Update');}
	
    const url = '/'+segment1+'/AJsave_Livestocks';
    fetchData(afterFetch,url, document.getElementById('frmproduct'), 'formData');
    
    function afterFetch(data){
        if(data.returnStr !==''){
            document.getElementById('error_product').innerHTML = data.returnStr;
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else if(['update-success', 'add-success'].includes(data.savemsg) && data.id>0){
			if(['POS','Orders','Repairs','Purchase_orders'].includes(segment1)){
				document.getElementById("search_sku").value = data.sku;				
				setTimeout(function() {
					addCartCBF();
				}, 500);
			}
			else {
				window.location = '/Livestocks/view/'+data.id+'/'+data.savemsg;
			}
            hidePopup();
		}
		else{
            if(data.savemsg==='Tag_Already_Exist') document.getElementById("errmsg_tag").innerHTML = Translate('This tag with the manufacturer already exists! Please try again with a different tag.');
            else if(data.savemsg==='Name_Already_Exist') document.getElementById("errmsg_product_name").innerHTML = Translate('This product name with the manufacturer already exists! Please try again with a different product name.');
            else if(data.savemsg==='Name_ExistInArchive') document.getElementById("errmsg_product_name").innerHTML = Translate('This product name with the manufacturer already exists <b>IN ARCHIVED</b>! Please try again with a different product name.');
            else if(data.savemsg==='SKU_Already_Exist') errmsg_sku.innerHTML = Translate('This SKU already exists! Please try again with a different SKU.');
            else if(data.savemsg==='SKU_ExistInArchive') errmsg_sku.innerHTML = Translate('This SKU already exists <b>IN ARCHIVED</b>! Please try again with a different SKU.');
            else if(data.savemsg==='error-adding-product') showTopMessage('error_msg', Translate('Error occured while adding new product! Please try again.'));
			document.querySelector("#tabs").activateTab(0);

			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
    }

	return false;
}


//=======add=========
async function updateSupplierId(){
	let customer_id = document.getElementById('customer_id');
	let customer_name = document.getElementById('customer_name');
	if(customer_id.value==='0' && customer_name.value!==''){
		const jsonData = {"keyword_search":this.value, 'fieldIdName':'customer_name', 'frompage':segment1};
		
		const url = "/Common/AJautoComplete_supplier";
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.returnStr.length>0) document.getElementById('customer_id').value = data.returnStr[0].id;
			else{
				customer_name.focus();
				showTopMessage('error_msg','Please click the new button to add a new customer because no customer was found for the search name you entered');
			}
		}
	}
}

function setLivestockType(product_type){
    if(product_type ===''){product_type = 'Standard';}    
    document.getElementById("product_type").value = product_type;

	if(product_type !==''){
        let fromTop = parseFloat(document.querySelector("#popup").style.top.replace('px',''));
        if(isNaN(fromTop)){fromTop = 50;}
        if(fromTop>100){
            document.querySelector("#popup").style.top = '75px';
        }
	}
	checkLivestockType();
}  

function checkManageInventory(){
	let product_id = 0;
	if(document.getElementById("product_id")){product_id = parseInt(document.getElementById("product_id").value);}
	if(isNaN(product_id)){product_id = 0;}

	let product_type = document.getElementById("product_type").value;
	if(product_type==='Live Stocks'){
		document.getElementById("manage_inventory_count").checked=true;
	}
	let manage_inventory_countid = document.getElementById("manage_inventory_count");
	if(manage_inventory_countid.checked===true){	
        document.querySelectorAll(".manage_inventory").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === 'none'){
				oneFieldObj.style.display = '';
			}
		});
    }
	else{
		document.querySelectorAll(".manage_inventory").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display !== 'none'){
				oneFieldObj.style.display = 'none';
			}
		});
		document.getElementById("low_inventory_alert").value = 0;
	}
}


function checkMaternalBlock(){
	// let product_id = 0;
	// if(document.getElementById("product_id")){product_id = parseInt(document.getElementById("product_id").value);}
	// if(isNaN(product_id)){product_id = 0;}
	// let product_type = document.getElementById("product_type").value;
	// if(product_type==='Live Stocks'){
	// 	document.getElementById("manage_inventory_count").checked=true;
	// }

    let matBlock = document.getElementById("manage_maternal_block");
    let isChecked = matBlock.checked;
    console.log(isChecked);

	let manage_maternal_blockid = document.getElementById("manage_maternal_block");
	if(manage_maternal_blockid.checked===true){	
        document.querySelectorAll(".manage_maternal_block_out").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === 'none'){
				oneFieldObj.style.display = '';
			}
		});
        document.querySelectorAll(".manage_maternal_block_in").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === ''){
				oneFieldObj.style.display = 'none';
			}
		});
    }
	else{
		document.querySelectorAll(".manage_maternal_block_out").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === ''){
				oneFieldObj.style.display = 'none';
			}
		});
        document.querySelectorAll(".manage_maternal_block_in").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === 'none'){
				oneFieldObj.style.display = '';
			}            
		});
	}
    
}


function checkPaternalBlock(){
	
    let matBlock = document.getElementById("manage_paternal_block");
    let isChecked = matBlock.checked;
    console.log(isChecked);

	let manage_maternal_blockid = document.getElementById("manage_paternal_block");
	if(manage_maternal_blockid.checked===true){	
        document.querySelectorAll(".manage_paternal_block_out").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === 'none'){
				oneFieldObj.style.display = '';
			}
		});
        document.querySelectorAll(".manage_paternal_block_in").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === ''){
				oneFieldObj.style.display = 'none';
			}
		});
    }
	else{
		document.querySelectorAll(".manage_paternal_block_out").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === ''){
				oneFieldObj.style.display = 'none';
			}
		});
        document.querySelectorAll(".manage_paternal_block_in").forEach(oneFieldObj=>{
			if(oneFieldObj.style.display === 'none'){
				oneFieldObj.style.display = '';
			}            
		});
	}
    
}


function checkLivestockType(){
	let productName, requiredField;
    let product_type = document.getElementById("product_type").value;
	
	let savebutvalue = Translate('Add');
	
	actionBtnClick('.btnmodel', Translate('Save'), 1);

	document.querySelectorAll(".displayNotAll").forEach(el => {
		if(el.style.display !== 'none'){
			el.style.display = 'none';
		}
	});

	if(product_type !==''){
		actionBtnClick('.btnmodel', Translate('Save'), 0);
		
		document.querySelectorAll(".groupField").forEach(el => {
			if(el.style.display === 'none'){
				el.style.display = '';
			}
		});

		productName = document.getElementById("lbproduct_name");
		productName.innerHTML = Translate('Livestock Name');
			requiredField = cTag('span',{ 'class': 'required' });
			requiredField.innerHTML = '*';
		productName.appendChild(requiredField);
        document.getElementById("product_name").maxlength = 100;


        // tagName = document.getElementById("tag");
		// tagName.innerHTML = Translate('Tag');
		// 	requiredField = cTag('span',{ 'class': 'required' });
		// 	requiredField.innerHTML = '*';
        //     tagName.appendChild(requiredField);
        // document.getElementById("tag").maxlength = 100;



		if(product_type==='Live Stocks'){
			document.querySelectorAll(".LiveStocks").forEach(el => {
				if(el.style.display === 'none'){
					el.style.display = '';
				}
			});

			productName = document.getElementById("lbproduct_name");
			productName.innerHTML = Translate('Livestock Name');
                requiredField = cTag('span',{ 'class': 'required' });
				requiredField.innerHTML = '*';
			productName.appendChild(requiredField);
            document.getElementById("manage_inventory_count").checked=true;
            document.getElementById("current_inventory").readOnly = 'readonly';
			if(document.getElementById("current_inventory").value ===''){
				document.getElementById("current_inventory").value = 0;
			}
		}
		else if(product_type==='Labor/Services'){
            document.querySelectorAll(".LaborServices").forEach(el => {
				if(el.style.display === 'none'){
					el.style.display = '';
				}
			});
            document.getElementById("current_inventory").readOnly = 'readonly';
            document.getElementById("require_serial_no").checked = false;
			document.getElementById("manage_inventory_count").checked = false;
			document.getElementById("current_inventory").value = 0;
		}
		else{
            document.querySelectorAll(".standardField").forEach(el => {
				if(el.style.display === 'none'){
					el.style.display = '';
				}
			});
		}		
		checkManageInventory();	
	}	
}

//=========Archive===========//
function archiveLivestock(sku,{need,have,onPO}){
    if(need>0 || have>0 || onPO>0){
        let NeedHaveOnPO = {Need:need,Have:have,OnPO:onPO};
        let msg = cTag('div',{style:'text-align:left'});
            let warn = cTag('h4',{style:'color:red;font-weight: bolder;margin-bottom:10px'});
            warn.append('You could not remove this product because:');
        msg.appendChild(warn);
        for (const key in NeedHaveOnPO) {
            if(NeedHaveOnPO[key]>0){
                let list = cTag('span',{style:'margin-left:20px;font-weight:bold;display:block'});
                list.innerHTML = key+': '+NeedHaveOnPO[key];
                msg.appendChild(list);
            }
        }
        alert_dialog(Translate('Can not Archive'), msg, Translate('Close'))
    }
    else{
        // const product_id = document.getElementById("table_idValue").value;
        confirm_dialog(Translate('Livestock Archive'), Translate('Are you sure you want to archive this information?'), (hidePopup)=>{        
            archiveData(`/${segment1}/AJ_product_archive/`, '/Livestocks/lists/', {"sku":sku}, segment1, Translate('Could not found product for archive'));
            hidePopup();
        });
    }
}
function unarchiveLivestock(){
    const product_id = document.getElementById("table_idValue").value;
    confirm_dialog(Translate('Livestock Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{        
        unarchiveData('/Livestocks/view/'+product_id,{tablename:'product', tableidvalue:product_id, publishname:'product_publish'});
        hidePopup();
    });
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, view};
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    addCustomeEventListener('labelSizeMissing',alert_label_missing);
});

