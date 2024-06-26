import {
    cTag, Translate, checkAndSetLimit, tooltip, round, controllNumericField, validateRequiredField, storeSessionData, addCurrency, DBDateToViewDate, printbyurl, setSelectOpt, setTableHRows, 
    showTopMessage, checkDateOnBlur, setOptions, addPaginationRowFlex, checkAndSetSessionData, popup_dialog600, popup_dialog1000, date_picker, validDate, 
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
        
        // onClickPagination();
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
            // console.log(item);
            item.forEach((itemInfo,index)=>{
                if(index===0 || index===7) return;
                const td = cTag('td');
                const attributes = listsFieldAttributes[index-1];
                // console.log(attributes);
                for (const key in attributes) {
                    td.setAttribute(key,attributes[key]);
                }
                // console.log(td);
                if(index === 1){
                    if(itemInfo===''){itemInfo = '\u2003';}
                    const aTag = cTag('a',{'class':'anchorfulllink', 'style':'text-decoration:underline', 'href':`/${uriStr}/${item[3]}`});
                    
                    aTag.innerHTML = itemInfo;
                    const inputFieldHidd = cTag('input',{ 'type': 'hidden', 'name': 'product_id[]','id': 'product_id','value':`${item[0]}` });
                    aTag.appendChild(inputFieldHidd);
                    td.appendChild(aTag);
                }
                else if(index===4){
                    if(itemInfo !== ''){                                                
                        let aTag = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "livestock_height[]", id: "livestock_height",  'value': "", 'size': 35, 'maxlength': 35});
                        td.appendChild(aTag);
                    }
                    else{td.append('\u2003');}
                }                
                else if(index===5){
                    if(itemInfo !== ''){                                                
                        let aTag = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "livestock_weight[]", id: "livestock_weight",  'value': "", 'size': 35, 'maxlength': 35});
                        td.appendChild(aTag);
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
    controller.appendChild(cTag('input', {'type': "hidden", name: id, id: id, 'value': 0}));
    controller.appendChild(cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", style:'margin-right:10px; width:50% !important;',  name: "review_date_blk", id: "review_date_blk", 'value': '', 'maxlength': 10}));
    // inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "arrival_date", id: "arrival_date", 'value': '', 'maxlength': 10});
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
            const bulkGrowthInfoEntryForm = cTag('form',{ 'method':`post`,'action':`#`,'enctype':`multipart/form-data`,'name':`frm_growth_info`,'id':`frm_growth_info` });
            // bulkGrowthInfoEntryForm.addEventListener('submit',event=>event.preventDefault());
            // bulkGrowthInfoEntryForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
            bulkGrowthInfoEntryForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'livestock_height',AJsave_growthinfos));
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

    let bdate = date_picker('#review_date_blk');
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = today.getFullYear();
    today = mm + '/' + dd + '/' + yyyy;
    document.getElementById("review_date_blk").value = today;
    
    // $('#review_date_blk').datePicker({
    //         "setDate": new Date(),
    //         "autoclose": true
    // });

    addCustomeEventListener('filter',filter_IMEI_lists);
    addCustomeEventListener('loadTable',loadTableRows_IMEI_lists);
    filter_IMEI_lists(true);
}


async function AJsave_ManageData(event=false,fieldID,proceedToSave){

    if(event){event.preventDefault();}

    let submit =  document.querySelector("#submit");
    // submit.value = Translate('Saving')+'...';
    // submit.disabled = true;

    /*let jsonData = {keyword_search:document.getElementById(fieldID).value,sdata_type: "Archived",limit: 9};
    const url = `/Growthinfos/AJgetPage_${segment2}/filter`;

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const ArchivedData = data;
        let inArchive = ArchivedData.tableRows.filter(item=>{
            if(segment2==='brand_model'){
                return item[1]===document.getElementById('brand').value && item[2]===document.getElementById('model').value;
            }
            else if(segment2==='category'){
                return item[1]===document.getElementById('category_name').value ;
            }
            else{
                return item[1]===document.getElementById('name').value;
            }
        })
        
        if(inArchive.length>0){
            let dialog = cTag('div');
            dialog.innerHTML = Translate('This name already exists <b>IN ARCHIVED!</b> Please try again with a different name. Do you really want to unarchive it?');
            popup_dialog(
                dialog,
                {
                    title:Translate('Unarchive'),
                    width:500,
                    buttons: {
                        _Cancel: {
                            text:Translate('Cancel'),
                            class: 'btn defaultButton', 'style': "margin-left: 10px;", click: function(hidePopup) {
                                hidePopup();
                            },
                        },
                        actionbutton:{
                            text:Translate('Unarchive'),
                            class: 'btn bgcoolblue btnmodel', 'style': "margin-left: 10px;", click: function(hidePopup) {
                                document.getElementById(segment2+'_id').value = inArchive[0][0];
                                unarchiveManageData();
                                hidePopup();
                            },
                        }
                    }
                }
            );
            submit.value = Translate('Save');
            submit.disabled = false;
        }
        else{
            proceedToSave();
        }
    }*/

    proceedToSave();
}


async function AJsave_growthinfos(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frm_growth_info");
    const url = '/'+segment1+'/AJsave_growthinfos';

    // console.log(jsonData);

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lsnipplesizescore();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lsnipplesizescore();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lsnipplesizescore! Please try again.'), Translate('Ok'));
		}
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
		}
		else if(data.returnStr=='Name_ExistInArchive'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists <b>IN ARCHIVED</b>! Please try again with a different name.'), Translate('Ok'));
		}
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}
        submit.value = Translate('Add')
        submit.disabled = false;
    }
    return false;
    
}

async function filter_growth_view(){
    let page = 1;
	document.getElementById("page").value = page;
    
	const jsonData = {};
	jsonData['sproduct_id'] = document.getElementById("sproduct_id").value;
	jsonData['sitem_id'] = document.getElementById("table_idValue").value;
	jsonData['item_number'] = document.getElementById("item_numberValue").value;
	// jsonData['shistory_type'] = document.getElementById("shistory_type").value;
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = '/'+segment1+'/AJgetHPage_Growth/filter';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
        setTableHRowsGrowth(data.tableRows, growthFieldAttributes);		
        document.getElementById("totalTableRows").value = data.totalRows;
        
        // const shistory_type = document.getElementById("shistory_type");
        // const shistory_typeVal = shistory_type.value;
        // shistory_type.innerHTML = '';

        const option = document.createElement('option');
        option.setAttribute('value', '');
        option.innerHTML = Translate('All Activities');


        // shistory_type.appendChild(option);
        // setOptions(shistory_type, data.actFeeTitOpt, 0, 1);
        // document.getElementById("shistory_type").value = shistory_typeVal;


        onClickPagination();
    }
}


export function setTableHRowsGrowth(tableData, tdAttributes){
    
    let tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';

	//======Create TBody TR Column======//
	let reviewDate, height, weight, product_id, dateTimeArray, date, time, oneTDObj, tabelHeadRow, tdCol;
    //console.log(tableData[0][3])

	if(tableData.length){

		tableData.forEach(oneRow => {

			let i=0;
			tabelHeadRow = cTag('tr');
			
			let idVal = oneRow[0];
			reviewDate = oneRow[1];
			height = oneRow[2];
			weight = oneRow[3];
			product_id = oneRow[4];
			
			dateTimeArray = DBDateToViewDate(oneRow[1], 1, 1);
			date = dateTimeArray[0];
            console.log(product_id)	

			tdCol = cTag('td');

			oneTDObj = tdAttributes[0];
			for(const [key, value] of Object.entries(oneTDObj)) {
				let attName = key;
				if(attName !=='' && attName==='datatitle')
					attName = attName.replace('datatitle', 'data-title');
				tdCol.setAttribute(attName, value);
			}

			tdCol.innerHTML = date;
			tabelHeadRow.appendChild(tdCol);

			
			tdCol = cTag('td');
			oneTDObj = tdAttributes[1];
			for(const [key, value] of Object.entries(oneTDObj)) {
				let attName = key;
				if(attName !=='' && attName==='datatitle')
					attName = attName.replace('datatitle', 'data-title');
				tdCol.setAttribute(attName, value);
			}
			tdCol.innerHTML = height;
			tabelHeadRow.appendChild(tdCol);


            tdCol = cTag('td');
            oneTDObj = tdAttributes[2];
            for(const [key, value] of Object.entries(oneTDObj)) {
                let attName = key;
                if(attName !=='' && attName==='datatitle')
                    attName = attName.replace('datatitle', 'data-title');
                tdCol.setAttribute(attName, value);
            }
            tdCol.innerHTML = weight;
			tabelHeadRow.appendChild(tdCol);

				
            // let pTag = cTag('p');
            // pTag.innerHTML=idVal;
            // tdCol.appendChild(pTag);
            // let cursorIcon = cTag('i',{ 'class': 'fa fa-edit cursor',  'data-toggle':"tooltip", 'click':()=>AJget_notesPopup(0,idVal), 'data-original-title':"Edit Note"});
            // tdCol.append(' ', cursorIcon);

                                    
            // let aTag = cTag('a',{ 'style': "color: #009; text-decoration: underline;", title:Translate('View Details'), 'href': height });							
            // aTag.innerHTML=tdvalue;
            // let linkIcon = cTag('i',{ 'class': 'fa fa-link'});
            // aTag.append(' ',linkIcon,' ');
            // tdCol.appendChild(aTag);
        
            
            // tdCol.innerHTML = tdvalue;
            // if(i===4 && ['notes', 'digital_signature'].includes(tableName) && height===1){
            //     let publicSpan = cTag('span',{ 'class': 'bgblack', 'style': "color: white; margin-left: 15px; padding: 5px;"});
            //     publicSpan.innerHTML= Translate('Public');
            //     tdCol.append(' ', publicSpan);

			// tabelHeadRow.appendChild(tdCol);
            
            tdCol = cTag('td');
            oneTDObj = tdAttributes[0];
            for(const [key, value] of Object.entries(oneTDObj)) {
                let attName = key;
                if(attName !=='' && attName==='datatitle')
                    attName = attName.replace('datatitle', 'data-title');
                tdCol.setAttribute(attName, value);
            }
            // tdCol.innerHTML = weight;

            const editIcon = cTag('i',{ 'class':`fa fa-edit`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Edit Growth') });
            editIcon.addEventListener('click',()=>AJget_LivestocksGrowthInfoPopup(idVal, product_id))
            tdCol.appendChild(editIcon);
            tdCol.append('  ');
            const trashIcon = cTag('i',{ 'class':`fa fa-trash-o`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove Weight') });
            trashIcon.addEventListener('click',()=>AJremove_tableRow('product_prices', idVal, 'Livestock Prices', `/Livestocks/view/${product_id}`))
            tdCol.appendChild(trashIcon);

			tabelHeadRow.appendChild(tdCol);

			tbody.appendChild(tabelHeadRow);

		});
	}
    
	tbody.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item))

}


async function AJget_LivestocksGrowthInfoPopup(product_growthinfo_id, product_id){
    
	const jsonData = {};
	jsonData['product_growthinfo_id'] = product_growthinfo_id;
	jsonData['product_id'] = product_id;

    const url = '/Growthinfos/AJget_LivestocksGrowthInfoPopup';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let requiredField, inputField;
        let currencyoption = currency;

        let formDialog = cTag('div');
            
            const productsGrowthInfoForm = cTag('form', {'action': "#", name: "frmproduct_growthinfo", id: "frmproduct_growthinfo", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                const productsGrowthInfoColumn = cTag('div', {class: "columnSM12", 'align': "left"});       

                    const growthInfoRow = cTag('div', {class: "flex"});
                        const growthInfoColumn = cTag('div', {class: "columnSM4"});
                            let growthInfoLabel = cTag('label', {'for': "growth"});
                            growthInfoLabel.innerHTML = Translate('Height');
                                requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                                growthInfoLabel.appendChild(requiredField);
                            growthInfoColumn.appendChild(growthInfoLabel);
                        growthInfoRow.appendChild(growthInfoColumn);
                        const growthInfoField = cTag('div', {class: "columnSM8"});
                            inputField = cTag('input', {'required': "required", id: "growth", name: "growth", 'type': "text",'data-min':'0', 'data-format':'d.dd', 'value': round(data.growth,2), class: "form-control"});
                            controllNumericField(inputField, '#error_growth');
                            growthInfoField.appendChild(inputField);
                            growthInfoField.appendChild(cTag('span', {id: "error_growth", class: "errormsg"}));
                        growthInfoRow.appendChild(growthInfoField);
                    productsGrowthInfoColumn.appendChild(growthInfoRow);



                    const weightInfoRow = cTag('div', {class: "flex"});
                        const weightInfoColumn = cTag('div', {class: "columnSM4"});
                            let weightInfoLabel = cTag('label', {'for': "weight"});
                            weightInfoLabel.innerHTML = Translate('Weight');
                                requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                                weightInfoLabel.appendChild(requiredField);
                                weightInfoColumn.appendChild(weightInfoLabel);
                            weightInfoRow.appendChild(weightInfoColumn);
                        const weightInfoField = cTag('div', {class: "columnSM8"});
                            inputField = cTag('input', {'required': "required", id: "weight", name: "weight", 'type': "text",'data-min':'0', 'data-format':'d.dd', 'value': round(data.weight,2), class: "form-control"});
                            controllNumericField(inputField, '#error_weight');
                            weightInfoField.appendChild(inputField);
                            weightInfoField.appendChild(cTag('span', {id: "error_weight", class: "errormsg"}));
                            weightInfoRow.appendChild(weightInfoField);
                    productsGrowthInfoColumn.appendChild(weightInfoRow);

                    //########## Review Date ############        
                    const arrivalDateRow = cTag('div', {class: "flex"});
                        const arrivalDateName = cTag('div', {class: "columnSM4", 'align': "left"});
                            const arrivalDateLabel = cTag('label', {'for': "review_date"});
                            arrivalDateLabel.innerHTML = Translate('Review Date');

                            let adrequiredField = cTag('span', {class: "required"});
                            adrequiredField.innerHTML = '*';
                                arrivalDateLabel.appendChild(adrequiredField);
                            arrivalDateName.appendChild(arrivalDateLabel);
                            
                        arrivalDateRow.appendChild(arrivalDateName);
                        const arrivalDateField = cTag('div', {class: "columnSM8", 'align': "left"});
                            inputField = cTag('input', {'autocomplete': "off", 'required': "required", 'type': "text", class: "form-control", name: "review_date", id: "review_date", 'value': DBDateToViewDate(data.review_date), 'maxlength': 10});
                            checkDateOnBlur(inputField,'#error_date','Invalid '+Translate('Arrival Date'));
                            arrivalDateField.appendChild(inputField);
                            arrivalDateField.appendChild(cTag('span',{id:'error_review_date',class:'errormsg'}));
                        arrivalDateRow.appendChild(arrivalDateField);
                    productsGrowthInfoColumn.appendChild(arrivalDateRow);


                    // const startDateRow = cTag('div', {class: "flex"});
                    //     const startDateColumn = cTag('div', {class: "columnSM4"});
                    //         const startDateLabel = cTag('label', {'for': "start_date"});
                    //         startDateLabel.innerHTML = Translate('Start Date');
                    //     startDateColumn.appendChild(startDateLabel);
                    // startDateRow.appendChild(startDateColumn);
                    //     const startDateField = cTag('div', {class: "columnSM8"});
                    //         inputField = cTag('input', {'required': "required", 'type': "text", class: "form-control", name: "start_date", id: "start_date", 'value': DBDateToViewDate(data.start_date),'maxlength': 10});
                    //         checkDateOnBlur(inputField,'#error_product_prices','Invalid Start Date');
                    //     startDateField.appendChild(inputField);
                    // startDateRow.appendChild(startDateField);
                    // productsGrowthInfoColumn.appendChild(startDateRow);


                productsGrowthInfoForm.appendChild(productsGrowthInfoColumn);

                inputField = cTag('input', {'type': "hidden", name: "product_growthinfo_id", 'value': product_growthinfo_id});
                productsGrowthInfoForm.appendChild(inputField);
                inputField = cTag('input', {'type': "hidden", name: "product_id", 'value': product_id});
                productsGrowthInfoForm.appendChild(inputField);
        formDialog.appendChild(productsGrowthInfoForm);
        
        popup_dialog1000(Translate('Livestock growth information'),formDialog,AJsave_LivestocksGrowth);			

        setTimeout(function() {
            // document.getElementById("price_type").value = data.price_type;
            // date_picker('#start_date');
            date_picker('#review_date');
        }, 500);
    }
	return true;
}



async function AJsave_LivestocksGrowth(hidePopup){
	let errormsg = document.getElementById('error_growth');
	let error_weight = document.getElementById('error_weight');
	errormsg.innerHTML = '';
	error_weight.innerHTML = '';

    // const price_type = document.getElementById("price_type");
    // if(price_type.value===''){
    //     errormsg.innerHTML = Translate('Missing Price Type');
    //     price_type.focus();
    //     price_type.classList.add('errorFieldBorder');
    //     return false;
    // }else {
    //     price_type.classList.remove('errorFieldBorder');
    // }

    let growthField = document.getElementById("growth");
    if(!validateRequiredField(growthField,'#error_growth') || !growthField.valid()) return;

	let product_id = document.frmproduct_growthinfo.product_id.value;
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
			
    const jsonData = serialize('#frmproduct_growthinfo');
    
    const url = '/'+segment1+'/AJsave_LivestocksGrowth';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        if(data.savemsg ===''){
            hidePopup();
			window.location = '/Growthinfos/view/'+product_id;						
		}
		else{						
			if(data.savemsg==='growthInfoExist') document.getElementById('error_growth').innerHTML = Translate('This info already exists. Try again with different field values.');
			// if(data.savemsg==='errorAddingPrice') document.getElementById('error_growth').innerHTML = Translate('Error occured while adding new product prices! Please try again.');
		}
        actionBtnClick('.btnmodel', Translate('Save'), 0);
    }
	return false;
}


async function loadTableRows_IMEI_view(){
	const jsonData = {};
	jsonData['sproduct_id'] = document.getElementById("sproduct_id").value;
	jsonData['sitem_id'] = document.getElementById("table_idValue").value;
	jsonData['item_number'] = document.getElementById("item_numberValue").value;
	// jsonData['shistory_type'] = document.getElementById("shistory_type").value;
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
        filter_growth_view();
    }
}

function viewOLD(){
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

    // const shistory_type = '';
    // checkAndSetSessionData('shistory_type', shistory_type, list_filters);

    addCustomeEventListener('filter',filter_growth_view);
    addCustomeEventListener('loadTable',loadTableRows_IMEI_view);
    AJ_view_MoreInfo();
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
            headerTitle.innerHTML = Translate('Livestock Growth Information')+' ';
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
            viewContentColumn.appendChild(growthHistoryTable(Translate('Growth History'),hiddenProperties));
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

    // const shistory_type = '';
    // checkAndSetSessionData('shistory_type', shistory_type, list_filters);

    addCustomeEventListener('filter',filter_growth_view);
    addCustomeEventListener('loadTable',loadTableRows_IMEI_view);
    AJ_view_MoreInfo();
}


export function getDeviceOperatingSystem() {
	let userAgent = navigator.userAgent;

	if(!userAgent) return 'unknown';
	else if(/linux|android/i.test(userAgent)) return 'Android';
	else if(/iPad|iPhone|iPod/i.test(userAgent)) return 'iOS';
	//latest tablet has same info as iMac has, so to differentiate between two checking if screen is touch enabled
	else if(/macintosh/i.test(userAgent) && navigator.maxTouchPoints>0) return 'iOS'; 

	return 'unknown';
}


export let  growthFieldAttributes = [
	{ 'valign':'top','datatitle':Translate('Date'), 'align':'center'},
	{'valign':'top','datatitle':Translate('Height'), 'align':'center'},
	{'valign':'top','datatitle':Translate('Weight'), 'align':'center'},
	{'valign':'top','datatitle':Translate('Action'), 'align':'left'}
];


export function growthHistoryTable(title,hiddenProperties,haveSignatureBtn=false){
	let page = 1;
	let pathArray = window.location.pathname.split('/');
	if(pathArray.length>4) page = parseInt(pathArray[4]);

	hiddenProperties = {
		'pageURI':segment1+'/'+segment2+'/'+segment3,
		'page':page,
		'rowHeight':'34',
		'totalTableRows':0,
		'publicsShow':0,
		...hiddenProperties
	}
	const widget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
	//=====Hidden Fields for Pagination======//
	for (const key in hiddenProperties){		
		widget.appendChild(cTag('input', {'type': "hidden",name: key, id: key, 'value': hiddenProperties[key]}));
	}

		const widgetHeader = cTag('div', {class: "cardHeader"});
			const widgetHeaderRow = cTag('div', {class: "flexSpaBetRow"});
				const widgetHeaderName = cTag('div', {class: "columnXS12 columnSM4", 'style': "margin: 0;"});
					const widgetHeaderTitle = cTag('h3');
					widgetHeaderTitle.innerHTML = title;
				widgetHeaderName.appendChild(widgetHeaderTitle);
			widgetHeaderRow.appendChild(widgetHeaderName);

			// 	const sortDropDown = cTag('div', {class: "columnXS6 columnSM4", 'style': "margin: 0;"});
			// 		const selectHistory = cTag('select', {class: "form-control", 'style': "margin-top: 2px;", name: "shistory_type", id: "shistory_type"});
			// 		selectHistory.addEventListener('change', ()=>triggerEvent('filter'));
			// 			const historyOption = cTag('option', {'value': ""});
			// 			historyOption.innerHTML = Translate('All Activities');
			// 		selectHistory.appendChild(historyOption);
			// 	sortDropDown.appendChild(selectHistory);
			// widgetHeaderRow.appendChild(sortDropDown);

				// const buttonTitle = cTag('div', {class: "columnXS6 columnSM4 flexEndRow", 'style': "margin:0; gap:10px; align-items: center;"});
				// if(haveSignatureBtn){
				// 	let signatureButton = cTag('button',{ 'id':'digital_signature_btn','href':`javascript:void(0);`,'class':`btn defaultButton` });
				// 		signatureButton.innerHTML = Translate('Add Digital Signature');
				// 		if(getDeviceOperatingSystem() !='unknown'){
				// 			signatureButton.innerHTML = '';
				// 			signatureButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', `${Translate('Digital Signature')}`);
				// 		}
				// 	buttonTitle.appendChild(signatureButton);
				// }

				// 	const noteButton = cTag('button', {class: "btn defaultButton"});
				// 	noteButton.innerHTML = Translate('Add New Note');
				// 	if(getDeviceOperatingSystem() !=='unknown'){
				// 		noteButton.innerHTML = '';
				// 		noteButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', `${Translate('Note')}`);
				// 	}
				// 	noteButton.addEventListener('click', function(){AJget_notesPopup(0);});
				// buttonTitle.appendChild(noteButton);
			// widgetHeaderRow.appendChild(buttonTitle);

		widgetHeader.appendChild(widgetHeaderRow);
	widget.appendChild(widgetHeader);

		const activityDiv = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
			const divTable = cTag('div', {class: "flexSpaBetRow"});
				const divTableColumn = cTag('div', {class: "columnXS12", 'style': "margin: 0; padding: 0;"});
					const noMoreTables = cTag('div', {id: "no-more-tables"});
						const activityTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing", 'style': "margin-top: 2px;"});
							const activityHead = cTag('thead', {class: "cf"});
								const columnNames = growthFieldAttributes.map(colObj=>(colObj.datatitle));
								const activityHeadRow = cTag('tr');
									const thCol0 = cTag('th', {'style': "width: 20px;"});
									thCol0.innerHTML = columnNames[0];

									const thCol1 = cTag('th', {'style': "width: 80px;"});
									thCol1.innerHTML = columnNames[1];

									const thCol2 = cTag('th', {'style': "width: 80px;"});
									thCol2.innerHTML = columnNames[2];

									const thCol3 = cTag('th', {'style': "width: 80px;"});
									thCol3.innerHTML = columnNames[3];
                                    
								activityHeadRow.append(thCol0, thCol1, thCol2, thCol3);
							activityHead.appendChild(activityHeadRow);
						activityTable.appendChild(activityHead);
							const activityBody = cTag('tbody', {id: "tableRows"});
						activityTable.appendChild(activityBody);
					noMoreTables.appendChild(activityTable);
				divTableColumn.appendChild(noMoreTables);
			divTable.appendChild(divTableColumn);
		activityDiv.appendChild(divTable);
		addPaginationRowFlex(activityDiv,true);
	widget.appendChild(activityDiv);
	return widget;
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