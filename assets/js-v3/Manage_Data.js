import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, emailcheck, checkPhone, noPermissionWarning, removeVariables, AJremoveData, 
    confirm_dialog, alert_dialog, setTableRows, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, checkAndSetSessionData, 
    popup_dialog, popup_dialog600, popup_dialog1000, daterange_picker_dialog, dynamicImport, applySanitizer, getOneRowInfo, archiveData, 
    unarchiveData, fetchData, listenToEnterKey, triggerEvent, addCustomeEventListener, actionBtnClick, serialize, onClickPagination, 
    customAutoComplete, AJautoComplete, leftsideHide, historyTable, activityFieldAttributes
} from './common.js';

if(['','export'].includes(segment2)) segment2 = 'export_';

const supplierListsAttributes = [
    {'datatitle':Translate('Name'), 'align':'justify'},
    {'datatitle':Translate('Email'), 'align':'left'},
    {'datatitle':Translate('Contact No'), 'align':'left'}
];
  
const uriStr = segment1+'/view';

function createListRow(data,tdAttributes,tableName,filterCBF,resetCBF){
    let table = document.getElementById("tableRows");
    table.innerHTML = '';
    if(data.length){
        data.forEach((item)=>{
            const tr = cTag('tr');
            item.forEach((info,indx)=>{
                if(indx===0) return;
                    const td = cTag('td');
                    const attributes = tdAttributes[indx-1];
                    for (const key in attributes) {
                        td.setAttribute(key,attributes[key]);
                    }
                        const a = cTag('a',{class:"anchorfulllink", click:()=>getOneRowInfo(tableName,item[0],filterCBF,resetCBF)});                    
                        a.innerHTML = info;
                    td.appendChild(a);
                tr.appendChild(td);
            })
            table.appendChild(tr);
        })
    }
    /* else{
		let colspan = tdAttributes.length;
		const tr = cTag('tr');
			const td = cTag('td', {colspan:colspan, 'style': "color: #F00; font-size: 16px;"});
			td.innerHTML = Translate('There is no data found');
		tr.appendChild(td);
		table.appendChild(tr);
	} */
}

function listHeader(label){
    const header = cTag('div',{class:'outerListsTable'});
        const headerTitle = cTag('h2', { 'style': "padding: 5px; text-align: start;"});
        headerTitle.append(label+' ');
            let infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page captures the accounts settings')});
        headerTitle.appendChild(infoIcon);
    header.appendChild(headerTitle);
    return header;
}

function leftsideMenu(){
    const NavLink = {
        export: Translate('Export Data'),
        archive_Data: Translate('Archive Data'),
        lsnipplesizescore: Translate('Manage Nipple Size Score'),
        lsbcscore: Translate('Manage Body Condition Score'),
        lsclassification: Translate('Manage Classification'),
        lssection: Translate('Manage Section'),
        lsbreed: Translate('Manage Breed'),
        lsgroups: Translate('Manage Groups'),
        lslocation: Translate('Manage Location'),
        suppliers: Translate('Manage Suppliers'),
        category: Translate('Manage Categories'),
        manufacturer: Translate('Manage Manufacturer'),
        repair_problems: Translate('Manage Repair Problem'),
        brand_model: Translate('Manage Brand Model'),
        vendors: Translate('Manage Vendors'),
        expense_type: Translate('Manage Expense Type'),
        customer_type: Translate('Manage Customer Type'),
        eu_gdpr: Translate('Manage EU GDPR')
    }

    let sideMenu = cTag('div', {class: "columnMD2 columnSM3", 'style': "margin: 0;"});
        let callOutDiv = cTag('div', {'style': "padding-top: 0;", class: "innerContainer"});
            let sideMenuLink = cTag('a', {'href': "javascript:void(0);", id: "secondarySideMenu"});
                let faFontSize = cTag('i', {class: "fa fa-align-justify", 'style': "margin-bottom: 10px; font-size: 2em;"});
            sideMenuLink.appendChild(faFontSize);
        callOutDiv.appendChild(sideMenuLink);
            let ulSetting = cTag('ul', {class: "secondaryNavMenu settingslefthide"});
            for (let uriVal in NavLink) {
                    let menuItem = segment2;
                    let liTag = cTag('li');
                    if(menuItem === 'export_') menuItem = 'export';
                    else if(menuItem==='sview') menuItem = 'suppliers';

                    if(menuItem === uriVal){
                        liTag.setAttribute('class',"activeclass");
                        liTag.setAttribute('style',"padding-top: 10px; padding-bottom: 10px;");
                            let sideMenuHeader = cTag('h4', {'style': "font-size: 18px;"});
                            sideMenuHeader.innerHTML = NavLink[uriVal];
                            liTag.appendChild(sideMenuHeader);
                    }else{
                        let titleVal = NavLink[uriVal];
                        let aTag = cTag('a', {'href': "/Manage_Data/"+uriVal, title: titleVal});
                            let navSpan = cTag('span');
                            navSpan.innerHTML = NavLink[uriVal];
                        aTag.appendChild(navSpan);
                        liTag.appendChild(aTag);
                    }
                ulSetting.appendChild(liTag);
            }
        callOutDiv.appendChild(ulSetting);
    sideMenu.appendChild(callOutDiv);
    return sideMenu;
}

function subHeader_Search_Bar(headerLabel,searchLabel,filterCBF){
    const div = cTag('div', {class: "columnXS12 outerListsTable"});
        let subHeaderTitle = cTag('h2');
        subHeaderTitle.innerHTML = headerLabel+' '+Translate('List')+' ';
            const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': headerLabel});
        subHeaderTitle.appendChild(infoIcon);
    div.appendChild(subHeaderTitle);

        const searchColumn = cTag('div', {class: "flexEndRow columnXS12"});
            const filterDiv = cTag('div', {class: "columnXS6"});
                const filterType = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type"});
                filterType.addEventListener('change', filterCBF);
                setOptions(filterType, {'All':Translate('All')+' '+headerLabel, 'Archived':Translate('Archived')+' '+headerLabel}, 1, 0); 
            filterDiv.appendChild(filterType);       
        searchColumn.appendChild(filterDiv);
            let searchInGroup = cTag('div', {class: "columnXS6 input-group"});
                let inputField = cTag('input', {'keydown':listenToEnterKey(filterCBF),'type': "text", 'placeholder': searchLabel, 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
            searchInGroup.appendChild(inputField);
                let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: searchLabel});
                searchSpan.addEventListener('click',filterCBF);
                    const searchIcon = cTag('i', {class: "fa fa-search"});
                searchSpan.appendChild(searchIcon);                                    
            searchInGroup.appendChild(searchSpan);
        searchColumn.appendChild(searchInGroup);
    div.appendChild(searchColumn);
    return div;
}

function controller_bar(id,cancelHandler){
    const controller = cTag('div', {class: "flexStartRow"});
    controller.appendChild(cTag('input', {'type': "hidden", name: id, id: id, 'value': 0}));
    controller.appendChild(cTag('input', {'type': "hidden", name: 'nameVal', id: 'nameVal', 'value': ''}));
    controller.appendChild(cTag('input', {'click':cancelHandler,'type': "button", name: "reset", id: "reset", 'value': Translate('Cancel'), class: "btn defaultButton", 'style': "display:none; margin-right: 10px;"}));
    controller.appendChild( cTag('input', {'type': "button", name: "unarchive", id: "unarchive", 'value': Translate('Unarchive'), class: "btn bgcoolblue", 'style': "display:none; margin-right: 10px;",'click':unarchiveManageData}));
    controller.appendChild( cTag('input', {'type': "button", name: "archive", id: "archive", 'value': Translate('Archive'), class: "btn archiveButton", 'style': "display:none; margin-right: 10px;", 'click':AJremoveData}));
    controller.appendChild( cTag('input', {'type': "button", name: "merge", id: "merge", 'value': Translate('Merge'), class: "btn defaultButton",style:'display:none; margin-right: 10px;','click':mergeDataPopup}));
    controller.appendChild(cTag('input', {'type': "submit", id: "submit", class: "btn saveButton", 'style': "margin-right: 10px;", 'value': Translate('Save') }));
    return controller;
}

function hidden_items(parent,page){
    [
        { name: 'pageURI', value: segment1+'/'+segment2},
        { name: 'page', value: page },
        { name: 'rowHeight', value: '34' },
        { name: 'totalTableRows', value: 0 },
    ].forEach(field=>{
        let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
    parent.appendChild(input);
    });
}

function getSessionData(){
    let list_filters;
    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    let sorting_type = '0';
    if(document.querySelector('#sorting_type')) checkAndSetSessionData('sorting_type', sorting_type, list_filters); 
    
    const shistory_type = '';
    if(document.querySelector('#shistory_type')) checkAndSetSessionData('shistory_type', shistory_type, list_filters); 

    const sdata_type = 'All';
    if(document.querySelector('#sdata_type')) checkAndSetSessionData('sdata_type', sdata_type, list_filters); 

    let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
        if(document.getElementById("keyword_search")){
            document.getElementById("keyword_search").value = keyword_search;
        }
    }

    let limit = 'auto';
    if(list_filters.hasOwnProperty("limit")){
        limit = list_filters.limit;
        if(document.getElementById("limit")){
            document.getElementById("limit").value = limit;
        }
    }
}


//____________________Export-Part_______________________
async function export_(){    
    const export_type_options = {
        "": Translate('Choose data type to Export'),
        customer: Translate('Customers'),
        product_inventory: Translate('Product Inventory'),
        pos: Translate('Product Sold'),
        po: Translate('Product Purchased'),
        imei: Translate('IMEI'),
        invoice: Translate('Invoices'),
        order: Translate('Order'),
        repairs: Translate('Repairs'),
        petty_cash: Translate('Petty Cash'),
        expenses: Translate('Expenses'),
        time_clock: Translate('Time Clock'),
    }

    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(listHeader(Translate('Export Data')));
        const exportDataContainer = cTag('div', {class: "flexSpaBetRow"});
        exportDataContainer.appendChild(leftsideMenu());
            
            let callOutDivStyle = "background: #fff;"
            if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
            let exportDataColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                    const exportForm = cTag('form', {name: "frmexport", id: "frmexport", 'action': "/Manage_Data/export_data_csv", 'enctype': "multipart/form-data", 'submit': checkExport, 'method': "post", 'accept-charset': "utf-8"});
                        const chooseExport = cTag('div', {class: "flex"});
                            const exportDropDown = cTag('div', {class: "columnSM12 columnMD4"});
                                let selectExportType = cTag('select', {'required': "", name: "export_type", id: "export_type", class: "form-control", 'change': checkExportType});
                                setOptions(selectExportType,export_type_options,1,0);
                            exportDropDown.appendChild(selectExportType);
                                let errorMessage = cTag('span', {class: "errormsg", id: "error_export_type"});
                            exportDropDown.appendChild(errorMessage);
                        chooseExport.appendChild(exportDropDown);
                    exportForm.appendChild(chooseExport);

                        const hr = cTag('hr');
                    exportForm.appendChild(hr);

                        let threeColumnRow = cTag('div', {class: "flexSpaBetRow", style:'display:none', id: "allthreecolumn"});
                            const emptyColumn = cTag('div', {class: "columnXS12", 'style': "margin: 0;"});
                        threeColumnRow.appendChild(emptyColumn);

                            const threeColumn = cTag('div', {class: "columnSM12 columnMD4 "});
                                let filterWidget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
                                    const widgetHeader = cTag('div', {class: "cardHeader"});
                                        let filterHeader = cTag('h3');
                                        filterHeader.innerHTML = Translate('Filter Data') ;
                                    widgetHeader.appendChild(filterHeader);
                                filterWidget.appendChild(widgetHeader);
                                    let filterContent = cTag('div', {class: "cardContent"});
                                        let filterContentRow = cTag('div', {class: "flexSpaBetRow"});
                                            let dateAddedColumn = cTag('div', {class: "columnSM12", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let label = cTag('label', {'for': "date_range", id: "lbdate_range"});
                                                label.innerHTML = Translate('Date Added') ;
                                            dateAddedColumn.appendChild(label);
                                                let inputField = cTag('input', {'type': "text", name: "date_range", id: "date_range", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;", 'value': ""});
                                                daterange_picker_dialog(inputField);
                                            dateAddedColumn.appendChild(inputField);
                                        filterContentRow.appendChild(dateAddedColumn);

                                            let customerTypeRow = cTag('div', {class: "columnSM12 customerFilters", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let customerTypeLabel = cTag('label', {'for': "customer_type"});
                                                customerTypeLabel.innerHTML = Translate('Customer Type');
                                            customerTypeRow.appendChild(customerTypeLabel);
                                                const selectCustomerType = cTag('select', {name: "customer_type", id: "customer_type", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;"});
                                                    const allOption = cTag('option', {'value': 'All'});
                                                    allOption.innerHTML = Translate('All Customer Type');
                                                selectCustomerType.appendChild(allOption);
                                            customerTypeRow.appendChild(selectCustomerType);
                                        filterContentRow.appendChild(customerTypeRow);

                                            let categoryColumn = cTag('div', {class: "columnSM12 productFilters", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let categoryLabel = cTag('label', {'for': "category_id"});
                                                categoryLabel.innerHTML = Translate('Category') ;
                                            categoryColumn.appendChild(categoryLabel);
                                                const selectCategory = cTag('select', {name: "category_id", id: "category_id", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;"});
                                                    const categoryOption = cTag('option', {'value': ""});
                                                    categoryOption.innerHTML = Translate('All Category');
                                                selectCategory.appendChild(categoryOption);                                           
                                            categoryColumn.appendChild(selectCategory);
                                        filterContentRow.appendChild(categoryColumn);

                                            let manufacturerColumn = cTag('div', {class: "columnSM12 productFilters", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let manufacturerLabel = cTag('label', {'for': "manufacturer_id"});
                                                manufacturerLabel.innerHTML = Translate('Manufacturer') ;
                                            manufacturerColumn.appendChild(manufacturerLabel);
                                                const selectManufacturer = cTag('select', {name: "manufacturer_id", id: "manufacturer_id", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;"});
                                                    const manufacturerOption = cTag('option', {'value': ""});
                                                    manufacturerOption.innerHTML = Translate('All Manufacturers');
                                                selectManufacturer.appendChild(manufacturerOption);
                                            manufacturerColumn.appendChild(selectManufacturer);
                                        filterContentRow.appendChild(manufacturerColumn);

                                            let manufacturerDiv = cTag('div', {class: "columnSM12 itemFilters", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let manufacturerLabel2 = cTag('label', {'for': "imanufacturer_id"});
                                                manufacturerLabel2.innerHTML = Translate('Manufacturer');
                                            manufacturerDiv.appendChild(manufacturerLabel2);
                                                let selectManufacturer2 = cTag('select', {name: "imanufacturer_id", id: "imanufacturer_id", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;"});
                                                    const allManuFacturerOption = cTag('option', {'value': ""});
                                                    allManuFacturerOption.innerHTML = Translate('All Manufacturers');
                                                selectManufacturer2.appendChild(allManuFacturerOption);
                                            manufacturerDiv.appendChild(selectManufacturer2);
                                        filterContentRow.appendChild(manufacturerDiv);

                                            let productTypeColumn = cTag('div', {class: "columnSM12 productFilters", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let productTypeLabel = cTag('label', {'for': "product_type"});
                                                productTypeLabel.innerHTML = Translate('Product Type');
                                            productTypeColumn.appendChild(productTypeLabel);
                                                const selectProductType = cTag('select', {change:checkExportType,name: "product_type", id: "product_type", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;"});
                                                    const productTypeOption = cTag('option', {'value': ""});
                                                    productTypeOption.innerHTML = Translate('All Product Type') ;
                                                selectProductType.appendChild(productTypeOption);
                                            productTypeColumn.appendChild(selectProductType);
                                        filterContentRow.appendChild(productTypeColumn);

                                            let statusColumn = cTag('div', {class: "columnSM12 repairsFilters", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let statusLabel = cTag('label', {'for': "status"});
                                                statusLabel.innerHTML = Translate('Status');
                                            statusColumn.appendChild(statusLabel);
                                                const selectStatus = cTag('select', {name: "status", id: "status", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;"});
                                                    const statusOption = cTag('option', {'value': ""});
                                                    statusOption.innerHTML = Translate('All Statuses');
                                                selectStatus.appendChild(statusOption);
                                            statusColumn.appendChild(selectStatus);
                                        filterContentRow.appendChild(statusColumn);

                                            let employeeColumn = cTag('div', {class: "columnSM12 timeClockFilters", 'style': "margin-top: 10px; padding-left: 10px;"});
                                                let employeeLabel = cTag('label', {'for': "user_id"});
                                                employeeLabel.innerHTML = Translate('Employee');
                                            employeeColumn.appendChild(employeeLabel);
                                                const selectEmployee = cTag('select', {name: "user_id", id: "user_id", class: "form-control", 'style': "margin-top: 5px; margin-bottom: 10px;"});
                                                    const employeeOption = cTag('option', {'value': ""});
                                                    employeeOption.innerHTML = Translate('All Employee');
                                                selectEmployee.appendChild(employeeOption);
                                            employeeColumn.appendChild(selectEmployee);
                                        filterContentRow.appendChild(employeeColumn);
                                    filterContent.appendChild(filterContentRow);
                                filterWidget.appendChild(filterContent);
                            threeColumn.appendChild(filterWidget);
                        threeColumnRow.appendChild(threeColumn);

                            let selectFieldColumn = cTag('div', {class: "columnSM12 columnMD4 "});
                                let selectFieldWidget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
                                    let selectFieldWidgetHeader = cTag('div', {class: "cardHeader"});
                                        let selectFieldTitle = cTag('h3');
                                        selectFieldTitle.append(Translate('Select Fields')+' ');
                                            const requiredField = cTag('span', {class: "required"});
                                            requiredField.innerHTML = '*';
                                        selectFieldTitle.appendChild(requiredField);
                                    selectFieldWidgetHeader.appendChild(selectFieldTitle);
                                selectFieldWidget.appendChild(selectFieldWidgetHeader);
                                    let selectFieldContent = cTag('div', {class: "cardContent"});
                                        let selectFieldRow = cTag('div', {class: "flexSpaBetRow"});
                                            let allFieldList = cTag('div', {class: "columnSM12", 'style': "padding-left: 10px;", id: "fieldsList"});
                                        selectFieldRow.appendChild(allFieldList);
                                    selectFieldContent.appendChild(selectFieldRow);
                                        let span1 = cTag('span', {class: "errormsg", id: "error_fieldsname"});
                                    selectFieldContent.appendChild(span1);
                                selectFieldWidget.appendChild(selectFieldContent);
                            selectFieldColumn.appendChild(selectFieldWidget);
                        threeColumnRow.appendChild(selectFieldColumn);

                            let exportColumn = cTag('div', {class: "columnSM12 columnMD4 "});
                                let exportWidget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
                                    let exportWidgetHeader = cTag('div', {class: "cardHeader"});
                                        let exportTitle = cTag('h3');
                                        exportTitle.innerHTML = Translate('Export Options');
                                    exportWidgetHeader.appendChild(exportTitle);
                                exportWidget.appendChild(exportWidgetHeader);
                                    let exportContent = cTag('div', {class: "cardContent"});
                                        let exportButton = cTag('div', {class: "columnSM12"});
                                            let input1 = cTag('input', {'type': "submit", name: "submit", id: "submit", class: "btn completeButton", 'value': Translate('Export')});
                                            input1.addEventListener('click',event=>{
                                                if([...document.querySelectorAll('#fieldsList input[type="checkbox"]')].filter(item=>item.checked).length===0){
                                                    event.preventDefault();
                                                    showTopMessage('error_msg', "You did not select any Field");
                                                } 
                                            })
                                        exportButton.appendChild(input1);
                                    exportContent.appendChild(exportButton);
                                exportWidget.appendChild(exportContent);
                            exportColumn.appendChild(exportWidget);
                        threeColumnRow.appendChild(exportColumn);
                    exportForm.appendChild(threeColumnRow);
                callOutDiv.appendChild(exportForm);
            exportDataColumn.appendChild(callOutDiv);
        exportDataContainer.appendChild(exportDataColumn);
    Dashboard.appendChild(exportDataContainer);
    AJ_export_MoreInfo();
}

async function AJ_export_MoreInfo(){
    const url = '/'+segment1+'/AJ_export_MoreInfo';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        setOptions(document.querySelector('#customer_type'),data.cusTypOpt,0,0);
        setOptions(document.querySelector('#category_id'),data.catIdOpt,1,1);
        setOptions(document.querySelector('#manufacturer_id'),data.manOpt,1,1);
        setOptions(document.querySelector('#imanufacturer_id'),data.proManOpt,1,1);
        setOptions(document.querySelector('#product_type'),data.proTypOpt,0,1);
        setOptions(document.querySelector('#status'),data.repStaOpt,0,1);
        setOptions(document.querySelector('#user_id'),data.useIdOpt,1,1);
     }
}

async function checkExportType(){

    function createFieldLists(label,data){
        let fieldsList = document.querySelector("#fieldsList");
        fieldsList.innerHTML = '';
        data.forEach(item=>{
                let label = cTag('label', {class: "cursor"});
                    let input = cTag('input', {'type': "checkbox", name: "fieldsname[]", 'value': `${item[0]}:${item[1]}`});
                    if(item[3] === 1){
                        input.checked = true;
                    }
                label.appendChild(input);
                label.append(` ${item[2]}`);
            fieldsList.appendChild(label);
                let br = cTag('br');
            fieldsList.appendChild(br);
        })
        document.querySelector("#lbdate_range").innerHTML = label;
    }    

	document.querySelector('#date_range').value = '';
	let export_type = document.querySelector("#export_type").value;

	if(export_type===''){
        if(document.querySelector("#allthreecolumn").style.display !== 'none'){
            document.querySelector("#allthreecolumn").style.display = 'none';
        }
	}
	else{
		document.querySelector("#error_fieldsname").innerHTML = "";
        if(document.querySelector("#allthreecolumn").style.display === 'none'){
            document.querySelector("#allthreecolumn").style.display = 'flex';
        }

		if(export_type==='customer'){
            document.querySelectorAll(".customerFilters").forEach(oneField=>{
                if(oneField.style.display === 'none'){
                    oneField.style.display = '';
                }
            });
        }
		else{
            document.querySelectorAll(".customerFilters").forEach(oneField=>{
                if(oneField.style.display !== 'none'){
                    oneField.style.display = 'none';
                }
            });
        }

		if(export_type==='product_inventory' || export_type==='pos' || export_type==='po'){
            document.querySelectorAll(".productFilters").forEach(oneField=>{
                if(oneField.style.display === 'none'){
                    oneField.style.display = '';
                }
            });
        }
		else{
            document.querySelectorAll(".productFilters").forEach(oneField=>{
                if(oneField.style.display !== 'none'){
                    oneField.style.display = 'none';
                }
            });
        }

		if(export_type==='imei'){
            document.querySelectorAll(".itemFilters").forEach(oneField=>{
                if(oneField.style.display === 'none'){
                    oneField.style.display = '';
                }
            });
        }
		else{
            document.querySelectorAll(".itemFilters").forEach(oneField=>{
                if(oneField.style.display !== 'none'){
                    oneField.style.display = 'none';
                }
            });
        }

		if(export_type==='repairs'){
            document.querySelectorAll(".repairsFilters").forEach(oneField=>{
                if(oneField.style.display === 'none'){
                    oneField.style.display = '';
                }
            });
        }
		else{
            document.querySelectorAll(".repairsFilters").forEach(oneField=>{
                if(oneField.style.display !== 'none'){
                    oneField.style.display = 'none';
                }
            });
        }

		if(export_type==='time_clock'){
            document.querySelectorAll(".timeClockFilters").forEach(oneField=>{
                if(oneField.style.display === 'none'){
                    oneField.style.display = '';
                }
            });
        }
		else{
            document.querySelectorAll(".timeClockFilters").forEach(oneField=>{
                if(oneField.style.display !== 'none'){
                    oneField.style.display = 'none';
                }
            });
        }
		        
        
        if(['invoice','customer','product_inventory','imei'].includes(export_type)){   
            const url = '/Manage_Data/exportFieldsList';
            fetchData(afterFetch,url,{export_type});  
            
            function afterFetch(data){
                let DynamicFieldLists = data.fieldsList;
                let fieldLists = [];
                if(export_type=='customer'){
                    fieldLists.push(['customers_id', 'ID', Translate('ID'), 1]);
                    fieldLists.push(['user_id', 'Created by User', Translate('Created by User'), 0]);
                    fieldLists.push(['first_name', 'First Name', Translate('First Name'), 1]);
                    fieldLists.push(['last_name', 'Last Name', Translate('Last Name'), 1]);
                    fieldLists.push(['email', 'Email', Translate('Email'), 1]);
                    fieldLists.push(['company', 'Company', Translate('Company'), 1]);
                    fieldLists.push(['contact_no', 'Contact No', Translate('Contact No'), 1]);
                    fieldLists.push(['secondary_phone', 'Secondary phone', Translate('Secondary Phone'), 1]);
                    fieldLists.push(['fax', 'Fax', Translate('Fax'), 1]);
                    fieldLists.push(['customer_type', 'Customer Type', Translate('Customer Type'), 0]);
                    fieldLists.push(['shipping_address_one', 'Shipping address one', Translate('Shipping address one'), 1]);
                    fieldLists.push(['shipping_address_two', 'Shipping address two', Translate('Shipping address two'), 1]);
                    fieldLists.push(['shipping_city', 'Shipping city', Translate('Shipping city'), 1]);
                    fieldLists.push(['shipping_state', 'Shipping state', Translate('Shipping state'), 1]);
                    fieldLists.push(['shipping_zip', 'Shipping zip', Translate('Shipping zip'), 1]);
                    fieldLists.push(['shipping_country', 'Shipping country', Translate('Shipping country'), 1]);
                    fieldLists = [...fieldLists,...DynamicFieldLists];
                }
                else if(export_type=='product_inventory'){
                    let product_type = document.getElementById('product_type').value;                    
                    fieldLists.push(['p.product_id', 'ID', Translate('ID'), 1]);
                    fieldLists.push(['p.product_type', 'Product Type', Translate('Product Type'), 1]);
                    fieldLists.push(['category.category_name', 'Category name', Translate('Category Name'), 1]);
                    fieldLists.push(['p.manufacturer_id', 'Manufacturer name', Translate('Manufacturer Name'), 1]);
                    fieldLists.push(['p.product_name', 'Product name', Translate('Product Name'), 1]);
                    if(['','Live Stocks'].includes(product_type)){
                        fieldLists.push(['p.colour_name', 'Color Name', Translate('Color Name'), 1]);
                        fieldLists.push(['p.storage', 'Storage', Translate('Storage'), 1]);
                        fieldLists.push(['p.physical_condition_name', 'Physical Condition', Translate('Physical Condition'), 1]);
                    }                  
                    fieldLists.push(['p.sku', 'SKU', Translate('SKU/Barcode'), 1]);
                    fieldLists.push(['i.ave_cost', 'Cost price', Translate('Cost price'), 1]);
                    fieldLists.push(['i.regular_price', 'Selling Price', Translate('Selling Price'), 1]);
                    fieldLists.push(['p.taxable', 'Taxable', Translate('Taxable'), 0]);
                    if(['','Live Stocks','Standard'].includes(product_type)){
                        fieldLists.push(['i.current_inventory', 'Current inventory', Translate('Current Inventory'), 1]);
                        fieldLists.push(['p.manage_inventory_count', 'Count Inventory', Translate('Count Inventory'), 0]);
                        fieldLists.push(['i.low_inventory_alert', 'Minimum stock', Translate('Minimum stock'), 0]);
                    }
                    if(['','Standard'].includes(product_type)){
                        fieldLists.push(['p.require_serial_no', 'Require Serial Number', Translate('Require serial number'), 0]);
                        fieldLists.push(['p.allow_backorder', 'Allow Over Selling', Translate('Allow Over Selling'), 0]);
                    }
                    fieldLists = [...fieldLists,...DynamicFieldLists];
                }
                else if(export_type=='imei'){
                    fieldLists.push(['item.item_id', 'ID', Translate('ID'), 1]);
                    fieldLists.push(['item.item_number', 'IMEI number', Translate('IMEI Number'), 1]);
                    fieldLists.push(['created_on_date', 'Created on Date', Translate('Created on Date'), 0]);
                    fieldLists.push(['created_by_username', 'Created by User Name', Translate('Created by User Name'), 0]);
                    fieldLists.push(['category.category_name', 'Category name', Translate('Category Name'), 0]);
                    fieldLists.push(['p.manufacturer_id', 'Manufacturer name', Translate('Manufacturer Name'), 0]);
                    fieldLists.push(['p.product_name', 'Product name', Translate('Product Name'), 1]);
                    fieldLists.push(['p.colour_name', 'Color Name', Translate('Color Name'), 1]);
                    fieldLists.push(['p.storage', 'Storage', Translate('Storage'), 1]);
                    fieldLists.push(['p.physical_condition_name', 'Physical Condition', Translate('Physical Condition'), 1]);
                    fieldLists.push(['item.carrier_name', 'Carrier', Translate('Carrier'), 1]);
                    fieldLists.push(['po.po_number', 'PO number', Translate('PO Number'), 1]);
                    fieldLists.push(['po.lot_ref_no', 'Lot #', Translate('Lot Ref. No.'), 1]);
                    fieldLists.push(['p.sku', 'SKU', Translate('SKU/Barcode'), 0]);
                    fieldLists.push(['po_items.cost', 'Cost price', Translate('Cost price'), 0]);
                    fieldLists.push(['item.in_inventory', 'In Inventory', Translate('In Inventory'), 0]);
                    fieldLists.push(['i.regular_price', 'Selling Price', Translate('Selling Price'), 0]);
                    fieldLists.push(['p.taxable', 'Taxable', Translate('Taxable'), 0]);
                    fieldLists.push(['i.low_inventory_alert', 'Minimum stock', Translate('Minimum stock'), 0]);
                    fieldLists = [...fieldLists,...DynamicFieldLists];
                }
                else if(export_type=='invoice'){
                    fieldLists.push(['date', 'Date', Translate('Date'), 1]);
                    fieldLists.push(['time', 'Time', Translate('Time'), 1]);
                    fieldLists.push(['invoice_no', 'Invoice No', Translate('Invoice No.'), 1]);
                    fieldLists.push(['customername', 'Customer Name', Translate('Customer Name'), 1]);
                    fieldLists.push(['customeremail', 'Customer Email', Translate('Customer Email'), 1]);
                    fieldLists.push(['customerphone', 'Customer Phone Number', Translate('Customer Phone'), 1]);
                    fieldLists.push(['customeraddress', 'Customer Address', Translate('Customer Address'), 1]);

                    fieldLists = [...fieldLists,...DynamicFieldLists];

                    fieldLists.push(['salesname', 'Sales Person', Translate('Sales Person'), 1]);
                    fieldLists.push(['taxable_total', 'Taxable', Translate('Taxable'), 1]);
                    fieldLists.push(['taxes_total', 'Taxes', Translate('Taxes'), 1]);
                    fieldLists.push(['nontaxable_total', 'Non Taxable', Translate('Non Taxable'), 1]);
                    fieldLists.push(['grand_total', 'Total', Translate('Total'), 1]);

                }
                createFieldLists(Translate('Date Added'),fieldLists)
            }

        }
        else if(export_type=='order'){            
			let fieldLists = [];
            fieldLists.push(['date', 'Date', Translate('Date'), 1]);
            fieldLists.push(['time', 'Time', Translate('Time'), 1]);
            fieldLists.push(['invoice_no', 'Order No', Translate('Order No.'), 1]);
            fieldLists.push(['customername', 'Customer Name', Translate('Customer Name'), 1]);
            fieldLists.push(['customercompany', 'Company', Translate('Company'), 1]);
            fieldLists.push(['customeremail', 'Customer Email', Translate('Customer Email'), 1]);
            fieldLists.push(['customerphone', 'Customer Phone Number', Translate('Customer Phone'), 1]);    
            		
            fieldLists.push(['customersecondary_phone', 'Secondary phone', Translate('Secondary Phone'), 1]);
            fieldLists.push(['customerfax', 'Fax', Translate('Fax'), 1]);
            fieldLists.push(['customercustomer_type', 'Customer Type', Translate('Customer Type'), 0]);
            fieldLists.push(['customershipping_address_one', 'Shipping address one', Translate('Shipping address one'), 1]);
            fieldLists.push(['customershipping_address_two', 'Shipping address two', Translate('Shipping address two'), 1]);
            fieldLists.push(['customershipping_city', 'Shipping city', Translate('Shipping city'), 1]);
            fieldLists.push(['customershipping_state', 'Shipping state', Translate('Shipping state'), 1]);
            fieldLists.push(['customershipping_zip', 'Shipping zip', Translate('Shipping zip'), 1]);
            fieldLists.push(['customershipping_country', 'Shipping country', Translate('Shipping country'), 1]);

            fieldLists.push(['salesname', 'Sales Person', Translate('Sales Person'), 1]);
            fieldLists.push(['taxable_total', 'Taxable', Translate('Taxable'), 1]);
            fieldLists.push(['taxes_total', 'Taxes', Translate('Taxes'), 1]);
            fieldLists.push(['nontaxable_total', 'Non Taxable', Translate('Non Taxable'), 1]);
            fieldLists.push(['grand_total', 'Total', Translate('Total'), 1]);

            createFieldLists(Translate('Sales Date'),fieldLists);
        }
        else if(export_type=='repairs'){
            const url = '/Manage_Data/exportFieldsList';
            fetchData(afterFetch,url,{export_type});  
            
            function afterFetch(data){
                const DynamicFieldLists =  data.fieldsList;;  
                let fieldLists = [];
                fieldLists.push(['ticket_no', 'Ticket #', Translate('Ticket'), 1]);
                fieldLists.push(['techassigned', 'Tech Assigned', Translate('Tech Assigned'), 1]);
                fieldLists.push(['problem', 'Problem', Translate('Problem'), 1]);
                fieldLists.push(['imei_or_serial_no', 'IMEI/Serial No.', Translate('IMEI/Serial No.'), 1]);
                fieldLists.push(['brand', 'Brand', Translate('Brand'), 1]);
                fieldLists.push(['model', 'Model', Translate('Model'), 1]);
                fieldLists.push(['more_details', 'More Details', Translate('More Details'), 1]);
                fieldLists.push(['created_on', 'Created', Translate('Created'), 1]);
                fieldLists.push(['status', 'Status', Translate('Status'), 1]);
                fieldLists.push(['last_updated', 'Last Update', Translate('Last Update'), 1]);
                fieldLists.push(['bin_location', 'Bin Location', Translate('Bin Location'), 0]);
                fieldLists.push(['lock_password', 'Password', Translate('Password'), 0]);            
    
                fieldLists = [...fieldLists,...DynamicFieldLists];
    
                fieldLists.push(['company', 'Company', Translate('Company'), 0]);
                fieldLists.push(['first_name', 'First Name', Translate('First Name'), 0]);
                fieldLists.push(['last_name', 'Last Name', Translate('Last Name'), 0]);
                fieldLists.push(['email', 'Email', Translate('Email'), 0]);
                fieldLists.push(['contact_no', 'Contact No', Translate('Contact No'), 0]);
                fieldLists.push(['secondary_phone', 'Secondary phone', Translate('Secondary Phone'), 0]);
                fieldLists.push(['fax', 'Fax', Translate('Fax'), 0]);
                fieldLists.push(['customer_type', 'Customer Type', Translate('Customer Type'), 0]);
                fieldLists.push(['shipping_address_one', 'Shipping address one', Translate('Shipping address one'), 0]);
                fieldLists.push(['shipping_address_two', 'Shipping address two', Translate('Shipping address two'), 0]);
                fieldLists.push(['shipping_city', 'Shipping city', Translate('Shipping city'), 0]);
                fieldLists.push(['shipping_state', 'Shipping state', Translate('Shipping state'), 0]);
                fieldLists.push(['shipping_zip', 'Shipping zip', Translate('Shipping zip'), 0]);
                fieldLists.push(['shipping_country', 'Shipping country', Translate('Shipping country'), 0]);
                
                createFieldLists(Translate('Repair Created'),fieldLists);
            }          
            
        }
        else if(export_type=='pos'){
            let fieldLists = [];
            fieldLists.push(['invoice_no', 'Invoice #', Translate('Invoice')+' #', 1]);
            fieldLists.push(['invoice_salesman', 'Invoice Salesman', Translate('Invoice Salesman'), 0]);
            fieldLists.push(['sales_datetime', 'POS Date', Translate('POS Date'), 1]);
            fieldLists.push(['first_name', 'Customer name', Translate('Customer Name'), 0]);
            fieldLists.push(['contact_no', 'Customer Phone Number', Translate('Customer Phone'), 0]);
            fieldLists.push(['address', 'Customer Address', Translate('Customer Address'), 0]);
            fieldLists.push(['email', 'Customer Email', Translate('Customer Email'), 0]);
            fieldLists.push(['customer_type', 'Customer Type', Translate('Customer Type'), 0]);
            fieldLists.push(['product_type', 'Product Type', Translate('Product Type'), 1]);
            fieldLists.push(['manufacturer_id', 'Manufacturer name', Translate('Manufacturer Name'), 1]);
            fieldLists.push(['category_id', 'Category name', Translate('Category Name'), 1]);
            fieldLists.push(['description', 'Product Name', Translate('Product Name'), 1]);
            fieldLists.push(['sku', 'SKU', Translate('SKU'), 0]);
            fieldLists.push(['shipping_qty', 'Qty Sold', Translate('Qty Sold'), 1]);
            fieldLists.push(['sales_price', 'Price', Translate('Price'), 1]);
            fieldLists.push(['discount', 'Discount', Translate('Discount'), 1]);
            fieldLists.push(['total', 'Total', Translate('Total'), 1]);
            fieldLists.push(['ave_cost', 'Cost', Translate('Cost'), 1]);
            fieldLists.push(['profit', 'Profit', Translate('Profit'), 1]);

            createFieldLists(Translate('Sold Date'),fieldLists);
        }
        else if(export_type=='po'){
            let fieldLists = [];
            fieldLists.push(['po_number', 'PO #', Translate('PO Number'), 1]);
            fieldLists.push(['created_by_username', 'Created by User Name', Translate('Created by User Name'), 0]);
            fieldLists.push(['suppilername', 'Suppiler Name', Translate('Supplier Name'), 1]);
            fieldLists.push(['suppliers_invoice_no', 'Suppliers Invoice No.', Translate('Suppliers Invoice No.'), 0]);
            fieldLists.push(['suppliers_invoice_date', 'Suppliers Invoice Date', Translate('Suppliers Invoice Date'), 0]);
            fieldLists.push(['date_paid', 'Date Paid', Translate('Date Paid'), 0]);
            fieldLists.push(['paid_by', 'Paid By', Translate('Paid By'), 0]);
            fieldLists.push(['po_datetime', 'PO Date', Translate('PO Date'), 1]);
            fieldLists.push(['product_type', 'Product Type', Translate('Product Type'), 1]);
            fieldLists.push(['manufacturer_id', 'Manufacturer name', Translate('Manufacturer Name'), 1]);
            fieldLists.push(['category_id', 'Category name', Translate('Category Name'), 1]);
            fieldLists.push(['product_name', 'Product Name', Translate('Product Name'), 1]);
            fieldLists.push(['sku', 'SKU', Translate('SKU'), 0]);
            fieldLists.push(['received_qty', 'Qty Purchased', Translate('Qty Purchased'), 1]);
            fieldLists.push(['cost', 'Cost', Translate('Cost'), 1]);
            fieldLists.push(['total', 'Total', Translate('Total'), 1]);

            createFieldLists(Translate('PO Created'),fieldLists);
        }
        else if(export_type==='petty_cash'){
			let fieldLists = [];
			fieldLists.push(['eod_date', 'Date Added', Translate('Date Added'), 1]);
			fieldLists.push(['add_sub', 'Add / Sub', Translate('Add / Sub'), 1]);
			fieldLists.push(['amount', 'Amount', Translate('Amount'), 1]);
			fieldLists.push(['reason', 'Reason', Translate('Reason'), 1]);

            createFieldLists(Translate('Date Added'),fieldLists);
        }
        else if(export_type=='expenses'){
            let fieldLists = [];
            fieldLists.push(['expense_type', 'Expense Type', Translate('Expense Type'), 1]);
            fieldLists.push(['vendors_id', 'Vendor Name', Translate('Vendor Name'), 1]);
            fieldLists.push(['bill_date', 'Bill Date', Translate('Bill Date'), 1]);
            fieldLists.push(['bill_number', 'Bill Number', Translate('Bill Number'), 1]);
            fieldLists.push(['bill_amount', 'Bill Amount', Translate('Bill Amount'), 1]);
            fieldLists.push(['bill_paid', 'Bill Paid Date', Translate('Bill Paid Date'), 1]);
            fieldLists.push(['ref', 'Reference', Translate('Reference'), 1]);

            createFieldLists(Translate('Bill Date'),fieldLists);
        }
        else if(export_type=='time_clock'){
            let fieldLists = [];
            fieldLists.push(['user_first_name', 'Employee Name', Translate('Employee'), 1]);
            fieldLists.push(['employee_number', 'Employee Number', Translate('Employee Number'), 1]);
            fieldLists.push(['pin', 'PIN', Translate('PIN'), 1]);
            fieldLists.push(['clock_in_date', 'Clock In Date', Translate('Clock In Date'), 1]);
            fieldLists.push(['clock_in_time', 'Clock In Time', Translate('Clock In Time'), 1]);
            fieldLists.push(['clock_out_date', 'Clock Out Date', Translate('Clock Out Date'), 1]);
            fieldLists.push(['clock_out_time', 'Clock Out Time', Translate('Clock Out Time'), 1]);
            
            createFieldLists(Translate('Clock In Date'),fieldLists);
        }
	}
}

function checkExport(){
	let export_type = document.querySelector("#export_type").value;
	document.querySelector("#error_export_type").innerHTML = "";
	if(export_type===''){
		document.querySelector("#error_export_type").innerHTML = Translate('Missing export data type');
		return false;
	}
	
	let fieldcheckcount = 0;
	document.querySelector("#error_fieldsname").innerHTML = "";
	let fieldsnameArray = document.getElementsByName("fieldsname[]");
	if(fieldsnameArray.length>0){
		for(let l = 0; l < fieldsnameArray.length; l++){
			if(fieldsnameArray[l].checked===true){fieldcheckcount++;}
		}
		
		if(fieldcheckcount===0){
			document.querySelector("#error_fieldsname").innerHTML = Translate('Please choose at least one field.');
			return false;
		}							
	}
	else{
		document.querySelector("#error_fieldsname").innerHTML = Translate('There is no field found.');
		return false;
	}
	return true;
}

//____________________Archive-part______________________________
async function archive_Data(){
    let fieldset, legend, inputField, requiredField, errorMessage, archiveButton, hiddenInput;
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(listHeader(Translate('Archive Data')));
        const archiveDataContainer = cTag('div', {class: "flexSpaBetRow"});
        archiveDataContainer.appendChild(leftsideMenu());

            let callOutDivStyle = "background: #fff;"
            if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
            let archiveDataColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                    inputField = cTag('input', {'type': "hidden", id: "archive_Data", 'value': 1});
                callOutDiv.appendChild(inputField);
                    fieldset = cTag('fieldset');
                        legend = cTag('legend');
                        legend.innerHTML = Translate('Invoice');
                    fieldset.appendChild(legend);
                        const invoiceForm = cTag('form', {'enctype':"text/plain", 'method':"post", name: "frmInvoices", 'action': "#"});
                        invoiceForm.addEventListener('submit', archiveInvoices);
                            const invoiceRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 15px 0px;"});
                                const invoiceColumn = cTag('div', {class: "columnSM12 columnMD3"});
                                    const invoiceLabel = cTag('label', { 'for': "invoice_no"});
                                    invoiceLabel.append(Translate('Invoice Number'));
                                        requiredField = cTag('span', {class: "required"});
                                        requiredField.innerHTML = '*';
                                    invoiceLabel.appendChild(requiredField);
                                invoiceColumn.appendChild(invoiceLabel);
                            invoiceRow.appendChild(invoiceColumn);
                                const searchInvoiceNumber = cTag('div', {class: "columnXS8 columnSM9 columnMD7"});
                                    inputField = cTag('input', {'type': "text", 'required': "",class: "form-control", name: "invoice_no", id: "invoice_no", 'value': "", 'placeholder':Translate('Invoice Number'), 'maxlength': 20, 'autocomplete': "off"});
                                searchInvoiceNumber.appendChild(inputField);
                                    errorMessage = cTag('span', {class: "errormsg", id: "error_invoice_no"});
                                searchInvoiceNumber.appendChild(errorMessage);
                            invoiceRow.appendChild(searchInvoiceNumber);
                                archiveButton = cTag('div', {class: "columnXS4 columnSM3 columnMD2"});
                                    inputField = cTag('input', {'type': "submit", name: "Invoices_archive", id: "Invoices_archive", class: "btn archiveButton", 'value': Translate('Archive')});
                                archiveButton.appendChild(inputField);
                            invoiceRow.appendChild(archiveButton);
                        invoiceForm.appendChild(invoiceRow);
                    fieldset.appendChild(invoiceForm);
                callOutDiv.appendChild(fieldset);
            archiveDataColumn.appendChild(callOutDiv)
        archiveDataContainer.appendChild(archiveDataColumn);
    Dashboard.appendChild(archiveDataContainer);
    if(document.querySelector("#invoice_no")){AJautoComplete('invoice_no',archiveInvoices);}
}

async function archiveInvoices(event=false){
    if(event){event.preventDefault();}
	confirm_dialog(Translate('Invoice Archive'), Translate('Are you sure you want to archive this information?'), (hidePopup)=>{
        document.querySelectorAll(".archive").forEach(oneField=>{
            if(oneField.style.display !== 'none'){
                oneField.style.display = 'none';
            }
        });
        let invoice_no = document.getElementById('invoice_no');
        
        archiveData('/Manage_Data/AJ_pos_archive/',false, {"invoice_no":invoice_no.value}, Translate('Invoice'),Translate('Could not found invoice for archive'));
        hidePopup();
        invoice_no.value = '';
    });
}


/*======lsnipplesizescore Module======*/
async function lsnipplesizescore(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Nipple Size Score')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lsnipplesizescoreContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lsnipplesizescoreRow = cTag('div', {class: "flexSpaBetRow"});
                    const lsnipplesizescoreHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lsnipplesizescoreHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lsnipplesizescore'),Translate('Search Nipple Size Score'),filter_Manage_Data_lsnipplesizescore));
                        const lsnipplesizescoreTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lsnipplesizescoreTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Nipple Size Score');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lsnipplesizescoreTableColumn.appendChild(listTable);
                        lsnipplesizescoreTableRow.appendChild(lsnipplesizescoreTableColumn);
                    lsnipplesizescoreHeaderColumn.appendChild(lsnipplesizescoreTableRow);
                    addPaginationRowFlex(lsnipplesizescoreHeaderColumn);
                lsnipplesizescoreRow.appendChild(lsnipplesizescoreHeaderColumn);

                    const addProductlsnipplesizescore = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlsnipplesizescoreHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlsnipplesizescoreHeader.innerHTML =Translate('Add New Nipple Size Score');
                    addProductlsnipplesizescore.appendChild(productlsnipplesizescoreHeader);

                        const addProductlsnipplesizescoreForm = cTag('form', {'action': "#", name: "frmlsnipplesizescore", id: "frmlsnipplesizescore", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlsnipplesizescoreForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lsnipplesizescore_name',AJsave_lsnipplesizescore));
                            const addProductlsnipplesizescoreRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lsnipplesizescoreLabel = cTag('label', {'for': "lsnipplesizescore_name"});
                                lsnipplesizescoreLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lsnipplesizescoreLabel.appendChild(requiredField);
                            addProductlsnipplesizescoreRow.appendChild(lsnipplesizescoreLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lsnipplesizescore_name", id: "lsnipplesizescore_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlsnipplesizescoreRow.appendChild(input);
                        addProductlsnipplesizescoreForm.appendChild(addProductlsnipplesizescoreRow);
                        addProductlsnipplesizescoreForm.appendChild(controller_bar('lsnipplesizescore_id',resetForm_lsnipplesizescore));
                    addProductlsnipplesizescore.appendChild(addProductlsnipplesizescoreForm);
                lsnipplesizescoreRow.appendChild(addProductlsnipplesizescore);
            callOutDiv.appendChild(lsnipplesizescoreRow);
        lsnipplesizescoreContainer.appendChild(callOutDiv);
    parentRow.appendChild(lsnipplesizescoreContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lsnipplesizescore);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lsnipplesizescore);
    addCustomeEventListener('reset',resetForm_lsnipplesizescore);
    getSessionData();    
    filter_Manage_Data_lsnipplesizescore(true);
}

async function filter_Manage_Data_lsnipplesizescore(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lsnipplesizescore/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Nipple Size Score'), 'align':'left'}],'lsnipplesizescore',filter_Manage_Data_lsnipplesizescore,resetForm_lsnipplesizescore);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lsnipplesizescore(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lsnipplesizescore';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Nipple Size Score'), 'align':'left'}],'lsnipplesizescore',filter_Manage_Data_lsnipplesizescore,resetForm_lsnipplesizescore);
        onClickPagination();
    }
}

async function AJsave_lsnipplesizescore(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlsnipplesizescore");
    const url = '/'+segment1+'/AJsave_lsnipplesizescore';

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

async function resetForm_lsnipplesizescore(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Nipple Size Score');
	document.querySelector("#lsnipplesizescore_id").value = 0;
	document.querySelector("#lsnipplesizescore_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======lsbcscore Module======*/
async function lsbcscore(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Body Condition Score')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lsbcscoreContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lsbcscoreRow = cTag('div', {class: "flexSpaBetRow"});
                    const lsbcscoreHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lsbcscoreHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lsbcscore'),Translate('Search Body Condition Score'),filter_Manage_Data_lsbcscore));
                        const lsbcscoreTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lsbcscoreTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Body Condition Score');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lsbcscoreTableColumn.appendChild(listTable);
                        lsbcscoreTableRow.appendChild(lsbcscoreTableColumn);
                    lsbcscoreHeaderColumn.appendChild(lsbcscoreTableRow);
                    addPaginationRowFlex(lsbcscoreHeaderColumn);
                lsbcscoreRow.appendChild(lsbcscoreHeaderColumn);

                    const addProductlsbcscore = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlsbcscoreHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlsbcscoreHeader.innerHTML =Translate('Add New Body Condition Score');
                    addProductlsbcscore.appendChild(productlsbcscoreHeader);

                        const addProductlsbcscoreForm = cTag('form', {'action': "#", name: "frmlsbcscore", id: "frmlsbcscore", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlsbcscoreForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lsbcscore_name',AJsave_lsbcscore));
                            const addProductlsbcscoreRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lsbcscoreLabel = cTag('label', {'for': "lsbcscore_name"});
                                lsbcscoreLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lsbcscoreLabel.appendChild(requiredField);
                            addProductlsbcscoreRow.appendChild(lsbcscoreLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lsbcscore_name", id: "lsbcscore_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlsbcscoreRow.appendChild(input);
                        addProductlsbcscoreForm.appendChild(addProductlsbcscoreRow);
                        addProductlsbcscoreForm.appendChild(controller_bar('lsbcscore_id',resetForm_lsbcscore));
                    addProductlsbcscore.appendChild(addProductlsbcscoreForm);
                lsbcscoreRow.appendChild(addProductlsbcscore);
            callOutDiv.appendChild(lsbcscoreRow);
        lsbcscoreContainer.appendChild(callOutDiv);
    parentRow.appendChild(lsbcscoreContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lsbcscore);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lsbcscore);
    addCustomeEventListener('reset',resetForm_lsbcscore);
    getSessionData();    
    filter_Manage_Data_lsbcscore(true);
}

async function filter_Manage_Data_lsbcscore(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lsbcscore/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Body Condition Score'), 'align':'left'}],'lsbcscore',filter_Manage_Data_lsbcscore,resetForm_lsbcscore);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lsbcscore(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lsbcscore';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Body Condition Score'), 'align':'left'}],'lsbcscore',filter_Manage_Data_lsbcscore,resetForm_lsbcscore);
        onClickPagination();
    }
}

async function AJsave_lsbcscore(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlsbcscore");
    const url = '/'+segment1+'/AJsave_lsbcscore';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lsbcscore();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lsbcscore();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lsbcscore! Please try again.'), Translate('Ok'));
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

async function resetForm_lsbcscore(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Body Condition Score');
	document.querySelector("#lsbcscore_id").value = 0;
	document.querySelector("#lsbcscore_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======lsclassification Module======*/
async function lsclassification(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Classification')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lsclassificationContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lsclassificationRow = cTag('div', {class: "flexSpaBetRow"});
                    const lsclassificationHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lsclassificationHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lsclassification'),Translate('Search Classification'),filter_Manage_Data_lsclassification));
                        const lsclassificationTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lsclassificationTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Classification Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lsclassificationTableColumn.appendChild(listTable);
                        lsclassificationTableRow.appendChild(lsclassificationTableColumn);
                    lsclassificationHeaderColumn.appendChild(lsclassificationTableRow);
                    addPaginationRowFlex(lsclassificationHeaderColumn);
                lsclassificationRow.appendChild(lsclassificationHeaderColumn);

                    const addProductlsclassification = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlsclassificationHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlsclassificationHeader.innerHTML =Translate('Add New Live Stock Classification');
                    addProductlsclassification.appendChild(productlsclassificationHeader);

                        const addProductlsclassificationForm = cTag('form', {'action': "#", name: "frmlsclassification", id: "frmlsclassification", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlsclassificationForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lsclassification_name',AJsave_lsclassification));
                            const addProductlsclassificationRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lsclassificationLabel = cTag('label', {'for': "lsclassification_name"});
                                lsclassificationLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lsclassificationLabel.appendChild(requiredField);
                            addProductlsclassificationRow.appendChild(lsclassificationLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lsclassification_name", id: "lsclassification_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlsclassificationRow.appendChild(input);
                        addProductlsclassificationForm.appendChild(addProductlsclassificationRow);
                        addProductlsclassificationForm.appendChild(controller_bar('lsclassification_id',resetForm_lsclassification));
                    addProductlsclassification.appendChild(addProductlsclassificationForm);
                lsclassificationRow.appendChild(addProductlsclassification);
            callOutDiv.appendChild(lsclassificationRow);
        lsclassificationContainer.appendChild(callOutDiv);
    parentRow.appendChild(lsclassificationContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lsclassification);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lsclassification);
    addCustomeEventListener('reset',resetForm_lsclassification);
    getSessionData();    
    filter_Manage_Data_lsclassification(true);
}

async function filter_Manage_Data_lsclassification(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lsclassification/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Section Name'), 'align':'left'}],'lsclassification',filter_Manage_Data_lsclassification,resetForm_lsclassification);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lsclassification(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lsclassification';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Section Name'), 'align':'left'}],'lsclassification',filter_Manage_Data_lsclassification,resetForm_lsclassification);
        onClickPagination();
    }
}

async function AJsave_lsclassification(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlsclassification");
    const url = '/'+segment1+'/AJsave_lsclassification';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lsclassification();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lsclassification();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lsclassification! Please try again.'), Translate('Ok'));
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

async function resetForm_lsclassification(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Live Stock Classification');
	document.querySelector("#lsclassification_id").value = 0;
	document.querySelector("#lsclassification_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}


/*======lsbreed Module======*/
async function lsbreed(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Breed')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lsbreedContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lsbreedRow = cTag('div', {class: "flexSpaBetRow"});
                    const lsbreedHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lsbreedHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lsbreed'),Translate('Search Breed'),filter_Manage_Data_lsbreed));
                        const lsbreedTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lsbreedTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Breed Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lsbreedTableColumn.appendChild(listTable);
                        lsbreedTableRow.appendChild(lsbreedTableColumn);
                    lsbreedHeaderColumn.appendChild(lsbreedTableRow);
                    addPaginationRowFlex(lsbreedHeaderColumn);
                lsbreedRow.appendChild(lsbreedHeaderColumn);

                    const addProductlsbreed = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlsbreedHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlsbreedHeader.innerHTML =Translate('Add New Live Stock Breed');
                    addProductlsbreed.appendChild(productlsbreedHeader);

                        const addProductlsbreedForm = cTag('form', {'action': "#", name: "frmlsbreed", id: "frmlsbreed", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlsbreedForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lsbreed_name',AJsave_lsbreed));
                            const addProductlsbreedRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lsbreedLabel = cTag('label', {'for': "lsbreed_name"});
                                lsbreedLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lsbreedLabel.appendChild(requiredField);
                            addProductlsbreedRow.appendChild(lsbreedLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lsbreed_name", id: "lsbreed_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlsbreedRow.appendChild(input);
                        addProductlsbreedForm.appendChild(addProductlsbreedRow);
                        addProductlsbreedForm.appendChild(controller_bar('lsbreed_id',resetForm_lsbreed));
                    addProductlsbreed.appendChild(addProductlsbreedForm);
                lsbreedRow.appendChild(addProductlsbreed);
            callOutDiv.appendChild(lsbreedRow);
        lsbreedContainer.appendChild(callOutDiv);
    parentRow.appendChild(lsbreedContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lsbreed);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lsbreed);
    addCustomeEventListener('reset',resetForm_lsbreed);
    getSessionData();    
    filter_Manage_Data_lsbreed(true);
}

async function filter_Manage_Data_lsbreed(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lsbreed/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Breed Name'), 'align':'left'}],'lsbreed',filter_Manage_Data_lsbreed,resetForm_lsbreed);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lsbreed(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lsbreed';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Breed Name'), 'align':'left'}],'lsbreed',filter_Manage_Data_lsbreed,resetForm_lsbreed);
        onClickPagination();
    }
}

async function AJsave_lsbreed(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlsbreed");
    const url = '/'+segment1+'/AJsave_lsbreed';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lsbreed();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lsbreed();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lsbreed! Please try again.'), Translate('Ok'));
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

async function resetForm_lsbreed(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Live Stock Breed');
	document.querySelector("#lsbreed_id").value = 0;
	document.querySelector("#lsbreed_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}


/*======lssection Module======*/
async function lssection(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Section')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lssectionContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lssectionRow = cTag('div', {class: "flexSpaBetRow"});
                    const lssectionHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lssectionHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lssection'),Translate('Search Section'),filter_Manage_Data_lssection));
                        const lssectionTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lssectionTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Section Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lssectionTableColumn.appendChild(listTable);
                        lssectionTableRow.appendChild(lssectionTableColumn);
                    lssectionHeaderColumn.appendChild(lssectionTableRow);
                    addPaginationRowFlex(lssectionHeaderColumn);
                lssectionRow.appendChild(lssectionHeaderColumn);

                    const addProductlssection = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlssectionHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlssectionHeader.innerHTML =Translate('Add New Live Stock Section');
                    addProductlssection.appendChild(productlssectionHeader);

                        const addProductlssectionForm = cTag('form', {'action': "#", name: "frmlssection", id: "frmlssection", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlssectionForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lssection_name',AJsave_lssection));
                            const addProductlssectionRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lssectionLabel = cTag('label', {'for': "lssection_name"});
                                lssectionLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lssectionLabel.appendChild(requiredField);
                            addProductlssectionRow.appendChild(lssectionLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lssection_name", id: "lssection_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlssectionRow.appendChild(input);
                        addProductlssectionForm.appendChild(addProductlssectionRow);
                        addProductlssectionForm.appendChild(controller_bar('lssection_id',resetForm_lssection));
                    addProductlssection.appendChild(addProductlssectionForm);
                lssectionRow.appendChild(addProductlssection);
            callOutDiv.appendChild(lssectionRow);
        lssectionContainer.appendChild(callOutDiv);
    parentRow.appendChild(lssectionContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lssection);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lssection);
    addCustomeEventListener('reset',resetForm_lssection);
    getSessionData();    
    filter_Manage_Data_lssection(true);
}

async function filter_Manage_Data_lssection(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lssection/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Section Name'), 'align':'left'}],'lssection',filter_Manage_Data_lssection,resetForm_lssection);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lssection(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lssection';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Section Name'), 'align':'left'}],'lssection',filter_Manage_Data_lssection,resetForm_lssection);
        onClickPagination();
    }
}

async function AJsave_lssection(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlssection");
    const url = '/'+segment1+'/AJsave_lssection';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lssection();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lssection();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lssection! Please try again.'), Translate('Ok'));
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

async function resetForm_lssection(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Live Stock Section');
	document.querySelector("#lssection_id").value = 0;
	document.querySelector("#lssection_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======lslocation Module======*/
async function lslocation(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Location')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lslocationContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lslocationRow = cTag('div', {class: "flexSpaBetRow"});
                    const lslocationHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lslocationHeaderColumn.appendChild(subHeader_Search_Bar(Translate('lslocation'),Translate('Search Location'),filter_Manage_Data_lslocation));
                        const lslocationTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lslocationTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Location Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lslocationTableColumn.appendChild(listTable);
                        lslocationTableRow.appendChild(lslocationTableColumn);
                    lslocationHeaderColumn.appendChild(lslocationTableRow);
                    addPaginationRowFlex(lslocationHeaderColumn);
                lslocationRow.appendChild(lslocationHeaderColumn);

                    const addProductlslocation = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlslocationHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlslocationHeader.innerHTML =Translate('Add New Live Stock Location');
                    addProductlslocation.appendChild(productlslocationHeader);

                        const addProductlslocationForm = cTag('form', {'action': "#", name: "frmlslocation", id: "frmlslocation", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlslocationForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lslocation_name',AJsave_lslocation));
                            const addProductlslocationRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lslocationLabel = cTag('label', {'for': "lslocation_name"});
                                lslocationLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lslocationLabel.appendChild(requiredField);
                            addProductlslocationRow.appendChild(lslocationLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lslocation_name", id: "lslocation_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlslocationRow.appendChild(input);
                        addProductlslocationForm.appendChild(addProductlslocationRow);
                        addProductlslocationForm.appendChild(controller_bar('lslocation_id',resetForm_lslocation));
                    addProductlslocation.appendChild(addProductlslocationForm);
                lslocationRow.appendChild(addProductlslocation);
            callOutDiv.appendChild(lslocationRow);
        lslocationContainer.appendChild(callOutDiv);
    parentRow.appendChild(lslocationContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lslocation);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lslocation);
    addCustomeEventListener('reset',resetForm_lslocation);
    getSessionData();    
    filter_Manage_Data_lslocation(true);
}

async function filter_Manage_Data_lslocation(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lslocation/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Location Name'), 'align':'left'}],'lslocation',filter_Manage_Data_lslocation,resetForm_lslocation);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lslocation(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lslocation';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Live Stock Location Name'), 'align':'left'}],'lslocation',filter_Manage_Data_lslocation,resetForm_lslocation);
        onClickPagination();
    }
}

async function AJsave_lslocation(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlslocation");
    const url = '/'+segment1+'/AJsave_lslocation';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lslocation();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lslocation();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lslocation! Please try again.'), Translate('Ok'));
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

async function resetForm_lslocation(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Live Stock Location');
	document.querySelector("#lslocation_id").value = 0;
	document.querySelector("#lslocation_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======lsgroups Module======*/
async function lsgroups(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Live Stock Groups')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let lsgroupsContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let lsgroupsRow = cTag('div', {class: "flexSpaBetRow"});
                    const lsgroupsHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    lsgroupsHeaderColumn.appendChild(subHeader_Search_Bar(Translate('Live Stock Groups'), Translate('Search Live Stock Groups'),filter_Manage_Data_lsgroups));
                        const lsgroupsTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const lsgroupsTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Live Stock Groups Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            lsgroupsTableColumn.appendChild(listTable);
                        lsgroupsTableRow.appendChild(lsgroupsTableColumn);
                    lsgroupsHeaderColumn.appendChild(lsgroupsTableRow);
                    addPaginationRowFlex(lsgroupsHeaderColumn);
                lsgroupsRow.appendChild(lsgroupsHeaderColumn);

                    const addProductlsgroups = cTag('div', {class: "columnXS12 columnMD5"});
                        let productlsgroupsHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productlsgroupsHeader.innerHTML =Translate('Add New Live Stock Groups');
                    addProductlsgroups.appendChild(productlsgroupsHeader);

                        const addProductlsgroupsForm = cTag('form', {'action': "#", name: "frmlsgroups", id: "frmlsgroups", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductlsgroupsForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'lsgroups_name', AJsave_lsgroups));
                            const addProductlsgroupsRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let lsgroupsLabel = cTag('label', {'for': "lsgroups_name"});
                                lsgroupsLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                lsgroupsLabel.appendChild(requiredField);
                            addProductlsgroupsRow.appendChild(lsgroupsLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "lsgroups_name", id: "lsgroups_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductlsgroupsRow.appendChild(input);
                        addProductlsgroupsForm.appendChild(addProductlsgroupsRow);
                        addProductlsgroupsForm.appendChild(controller_bar('lsgroups_id',resetForm_lsgroups));
                    addProductlsgroups.appendChild(addProductlsgroupsForm);
                lsgroupsRow.appendChild(addProductlsgroups);
            callOutDiv.appendChild(lsgroupsRow);
        lsgroupsContainer.appendChild(callOutDiv);
    parentRow.appendChild(lsgroupsContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_lsgroups);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_lsgroups);
    addCustomeEventListener('reset',resetForm_lsgroups);
    getSessionData();    
    filter_Manage_Data_lsgroups(true);
}

async function filter_Manage_Data_lsgroups(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_lsgroups/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('lsgroups Name'), 'align':'left'}],'lsgroups',filter_Manage_Data_lsgroups,resetForm_lsgroups);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_lsgroups(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_lsgroups';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('lsgroups Name'), 'align':'left'}],'lsgroups',filter_Manage_Data_lsgroups,resetForm_lsgroups);
        onClickPagination();
    }
}

async function AJsave_lsgroups(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmlsgroups");
    const url = '/'+segment1+'/AJsave_lsgroups';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_lsgroups();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_lsgroups();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new lsgroups! Please try again.'), Translate('Ok'));
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

async function resetForm_lsgroups(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Category');
	document.querySelector("#lsgroups_id").value = 0;
	document.querySelector("#lsgroups_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

//___________suppliers__________
async function suppliers(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

    hidden_items(showTableData,page);

    showTableData.appendChild(listHeader(Translate('Manage Suppliers')));
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "background:#FFF;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let suppliersContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                const supplierTitleRow = cTag('div', {class: "flexSpaBetRow outerListsTable"});
                    const buttonName = cTag('div', {class: "columnXS12 columnSM12 columnMD3"});
                        let buttonTitle = cTag('a', {'href': "javascript:void(0);", title: Translate('Create Supplier'), class: "btn cursor createButton"});
                        buttonTitle.addEventListener('click', function(){addnewsupplierform('addsupplier', 0);});
                        buttonTitle.append(cTag('i', {class: "fa fa-plus"}), ' ', Translate('Create Supplier'));
                    buttonName.appendChild(buttonTitle);
                supplierTitleRow.appendChild(buttonName);
                    const supplierType = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                        let selectDataType = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type", 'change': filter_Manage_Data_suppliers});
                       setOptions(selectDataType,{'All':Translate('All Suppliers'),'Archived':Translate('Archived Suppliers')},1,0);
                    supplierType.appendChild(selectDataType);
                supplierTitleRow.appendChild(supplierType);
                    const supplierDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
                        let selectSupplier = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type", 'change': filter_Manage_Data_suppliers});
                        const options = {
                            '0':'Company First and Last Name', 
                            '1':Translate('Company Name'), 
                            '2':Translate('First Name'),
                            '3':Translate('Last Name')
                        };
                        for(const [key, value] of Object.entries(options)) {
                            let supplierOption = cTag('option', {'value': key});
                            supplierOption.innerHTML = value;
                            selectSupplier.appendChild(supplierOption);
                        }
                    supplierDropDown.appendChild(selectSupplier);
                supplierTitleRow.appendChild(supplierDropDown);
                    const supplierSearch = cTag('div', {class: "columnXS12 columnSM4 columnMD3"});
                        const supplierInGroup = cTag('div', {class: "input-group"});
                            let inputField = cTag('input', {'keydown':listenToEnterKey(filter_Manage_Data_suppliers),'type': "text", 'placeholder':Translate('Search Suppliers'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
                        supplierInGroup.appendChild(inputField);
                            let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: Translate('Search Suppliers')});
                            searchSpan.addEventListener('click', filter_Manage_Data_suppliers);
                                const searchIcon = cTag('i', {class: "fa fa-search"});
                            searchSpan.appendChild(searchIcon);
                        supplierInGroup.appendChild(searchSpan);
                    supplierSearch.appendChild(supplierInGroup);
                supplierTitleRow.appendChild(supplierSearch);
            callOutDiv.appendChild(supplierTitleRow);

                const supplierTableColumn = cTag('div', {class: "columnSM12", 'style': "position:relative;"});
                    const noMoreTables = cTag('div', {id: "no-more-tables"});
                        const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                            const listHead = cTag('thead', {class: "cf"});
                                const columnNames = supplierListsAttributes.map(colObj=>(colObj.datatitle));
                                const listHeadRow = cTag('tr');
                                    const thCol0 = cTag('th');
                                    thCol0.innerHTML = columnNames[0];

                                    const thCol1 = cTag('th', {'width': "25%"});
                                    thCol1.innerHTML = columnNames[1];

                                    const thCol2 = cTag('th', {'width': "25%"});
                                    thCol2.innerHTML = columnNames[2];
                                listHeadRow.append(thCol0, thCol1, thCol2);
                            listHead.appendChild(listHeadRow);
                        listTable.appendChild(listHead);
                            const listBody = cTag('tbody', {id: "tableRows"});
                        listTable.appendChild(listBody);
                    noMoreTables.appendChild(listTable);
                supplierTableColumn.appendChild(noMoreTables);
            callOutDiv.appendChild(supplierTableColumn);
            addPaginationRowFlex(callOutDiv);
        suppliersContainer.appendChild(callOutDiv);
    parentRow.appendChild(suppliersContainer);
    showTableData.appendChild(parentRow);

    addCustomeEventListener('filter',filter_Manage_Data_suppliers);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_suppliers);
    getSessionData();
    filter_Manage_Data_suppliers(true);
}

async function filter_Manage_Data_suppliers(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;
	jsonData['sorting_type'] = document.querySelector('#sorting_type').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
    
    const url = '/'+segment1+'/AJgetPageSupplier/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        setTableRows(data.tableRows, supplierListsAttributes, segment1+'/sview');
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_suppliers(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;
	jsonData['sorting_type'] = document.querySelector('#sorting_type').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;

    const url = '/'+segment1+'/AJgetPageSupplier';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        setTableRows(data.tableRows, supplierListsAttributes, segment1+'/sview');
        onClickPagination();
    }
}

export async function addnewsupplierform(frompage, suppliers_id){
    const formItems = [
        {label: Translate('Company'), required: 'required', type: 'text', class: 'form-control', name: 'company', id: 'company', maxlength: '35'},
        {label: Translate('First Name'),required: 'required',type: 'text',class: 'form-control',name: 'first_name',id: 'first_name',maxlength: '12'},
        {label: Translate('Last Name'),type: 'text',class: 'form-control',name: 'last_name',id: 'last_name',maxlength: '17'},
        {label: Translate('Email Address'),type: 'email',class: 'form-control',name: 'email',id: 'email',maxlength: '50'},
        {label: Translate('Offers Email'),autocomplete: 'off',type: 'checkbox',name: 'offers_email',id: 'offers_email',value: 1},
        {label: Translate('Phone No.'),type: 'tel',class: 'form-control',name: 'contact_no',id: 'contact_no',maxlength: '15'},
        {label: Translate('Secondary Phone'),type: 'tel',class: 'form-control',name: 'secondary_phone',id: 'secondary_phone',maxlength: '15'},
        {label:Translate('Fax'),type: 'tel',class: 'form-control',name: 'fax',id: 'fax',maxlength: '15'},
        {label: Translate('Address Line 1'),type: 'text',class: 'form-control',name: 'shipping_address_one',id: 'shipping_address_one',maxlength: '35'},
        {label:Translate('Address Line 2'),type: 'text',class: 'form-control',name: 'shipping_address_two',id: 'shipping_address_two',maxlength: '35'},
        {label: Translate('City / Town'),type: 'text',class: 'form-control',name: 'shipping_city',id: 'shipping_city',maxlength: '30'},
        {label: Translate('State / Province'),type: 'text',class: 'form-control',name: 'shipping_state',id: 'shipping_state',maxlength: '20'},
        {label: Translate('Zip/Postal Code'),type: 'text',class: 'form-control',name: 'shipping_zip',id: 'shipping_zip',maxlength: '9'},
        {label: Translate('Country'),type: 'text',class: 'form-control',name: 'shipping_country',id: 'shipping_country',maxlength: '35'},
        {label: Translate('Website'),type: 'text',class: 'form-control',name: 'website',id: 'website',maxlength: '80'}
    ]

	let frompage2, div, input;
	if(frompage==='addpo' || frompage==='editpo'){frompage2 = 'Purchase_orders';}
	else if(frompage==='Products'){frompage2 = 'Products';}
	else{frompage2 = 'Manage_Data';}
	    
    const jsonData = {"suppliers_id":suppliers_id};
    const url = "/"+frompage2+"/AJget_SuppliersPopup";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let formhtml = cTag('div');
            let divError = cTag('div');
            div = cTag('div', {id: "error_supplier", class: "errormsg"});
            divError.appendChild(div);
        formhtml.appendChild(divError) 
            const form = cTag('form', {'action': "#", name: "frmsupplier", id: "frmsupplier", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
            form.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
                const SpaBetRow = cTag('div',{ 'class':`flexSpaBetRow` });
                    let firstCol = cTag('div', {class: "columnXS12 columnSM6", 'style': "padding-right: 10px;"});
                SpaBetRow.appendChild(firstCol);
                    let secondCol = cTag('div', {class: "columnXS12 columnSM6", 'style': "padding-left: 10px;"});
                SpaBetRow.appendChild(secondCol);
            form.appendChild(SpaBetRow);
                formItems.forEach((item,indx)=>{
                    if(indx<8){
                        itemCreator(item,firstCol);
                    }else{
                        itemCreator(item,secondCol);
                    }
                })                    
                function itemCreator(item,parent){
                    let errorField = '';
                    const divFormRow = cTag('div', {class: "flex", 'align': "left"});
                        let formLabel = cTag('div', {class: "columnSM4"});
                            let label = cTag('label', {'for': item.name});
                            label.append(item.label);
                            if(item.required){
                                let requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            label.appendChild(requiredField);
                            }
                        formLabel.appendChild(label);
                    divFormRow.appendChild(formLabel);
                        let fieldColumn = cTag('div', {class: "columnSM8"});
                            input = cTag('input', {'value': data[item.name]});
                            for(let x in item){
                                input.setAttribute(x,item[x]);
                            }							
                            if(item.type==='checkbox'){
                                input.setAttribute('value',1);
                                if(data.offers_email === 1){
                                    input.checked = true;
                                }
                            }
                            if(item.type==='email'){
                                errorField = cTag('span',{'class':'errormsg','id':'errorEmail'});
                                input.addEventListener('blur',function(){
                                    if(this.value!='' && !emailcheck(this.value)) errorField.innerHTML = 'Invalid Email'
                                });
                                input.addEventListener('focus',()=>errorField.innerHTML='')
                            }
                        if(item.required){
                            errorField = cTag('span',{ 'class': 'error_msg','id': 'error_'+item.name });
                        }
                        fieldColumn.append(input, errorField);
                    divFormRow.appendChild(fieldColumn);
                    parent.appendChild(divFormRow);
                }

                input = cTag('input', {'type': "hidden", name: "frompage", id: "frompage", value: frompage});
            form.appendChild(input);
                input = cTag('input', {'type': "hidden", name: "suppliers_id", 'value': data.suppliers_id});
            form.appendChild(input);
        formhtml.appendChild(form);
            
        popup_dialog1000(Translate('Supplier Information'), formhtml, save_supplier);
        
        setTimeout(function() {
            if(frompage==='addpo'){
                document.getElementById('company').value = document.getElementById('supplier_name').value;
            }
            
            document.getElementById("company").focus();				
            document.querySelector("#contact_no").addEventListener('keyup',function(event) {
                if(!checkPhone("contact_no", 0)) this.value = this.value.slice(0,-1);
            });
            applySanitizer(formhtml);
        }, 500);
    }
	return true;
}
	
async function save_supplier(hidePopup){
	let error_company = document.getElementById("error_company");
	let error_first_name = document.getElementById("error_first_name");
	let errorEmail = document.getElementById("errorEmail");
	error_company.innerHTML = '';
	error_first_name.innerHTML = '';
	errorEmail.innerHTML = '';

    let missingCompany = document.querySelector("#company");
	if(missingCompany.value===''){
		error_company.innerHTML = Translate('Missing company name');
		missingCompany.focus();
        missingCompany.classList.add('errorFieldBorder');
		return false;
	}else {
        missingCompany.classList.remove('errorFieldBorder');
    }
	
    let missingName = document.querySelector("#first_name");
	if(missingName.value===''){
		error_first_name.innerHTML = Translate('Missing first name.');
		missingName.focus();
        missingName.classList.add('errorFieldBorder');
		return false;
	}else {
        missingName.classList.remove('errorFieldBorder');
    }

	let email = document.querySelector('#popup #email');
	if(email.value.trim()!='' && !emailcheck(email.value)){		
		errorEmail.innerHTML = 'Invalid Email';
		email.focus();
		return;
	} 
	actionBtnClick('.btnmodel', Translate('Saving'), 1);
	let suppliers_id = parseInt(document.frmsupplier.suppliers_id.value);
	if(isNaN(suppliers_id) || suppliers_id===''){suppliers_id = 0;}
	
	let frompage = document.frmsupplier.frompage.value;
    
    const jsonData = serialize("#frmsupplier");
    const url = "/"+segment1+"/AJsave_Suppliers/";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        actionBtnClick('.btnmodel', Translate('Save'), 0);
        
        if(['add-success','update-success'].includes(data.savemsg)){
            if(frompage ==='editpo'){
                let company = document.getElementById('company').value;
                let first_name = document.getElementById('first_name').value;
                let last_name = document.getElementById('last_name').value;
                let email = document.getElementById('email').value;
                
                const company_link = document.querySelector('#company_link');
                company_link.setAttribute('href',`/Manage_Data/sview/${data.suppliers_id}`);
                company_link.innerHTML = company+' ';
                company_link.appendChild(cTag('i',{ 'class':`fa fa-link` }));               

                let suppliername = document.querySelector('#suppliername');
                suppliername.innerHTML = first_name+' '+last_name;
                    const edit = cTag('i',{ 'style':'cursor:pointer','class':`fa fa-edit` });
                    edit.addEventListener('click',()=>dynamicImport('./Manage_Data.js','addnewsupplierform',['editpo', data.suppliers_id]))
                suppliername.append('  ',edit);

                document.querySelector('#email_address').value = email;                    
                document.querySelector('#supplieremail').innerHTML = email;
            }
            else if(['addpo', 'Products'].includes(frompage)){
                let supplier_idObj = document.querySelector("#supplier_id");
                supplier_idObj.innerHTML = '';
                    let supplierOption = cTag('option', {'value': 0});
                    supplierOption.innerHTML = Translate('Select Supplier');
                supplier_idObj.appendChild(supplierOption);
                setOptions(supplier_idObj, data.supplierOpt, 1, 1);
                supplier_idObj.value = data.suppliers_id;
                if(frompage==='addpo'){
                    document.querySelector("#supplier_name").value = data.supplier_name;
                    document.getElementById('errmsg_supplier_name').innerHTML = '';
                }
                else{
                    document.getElementById('errmsg_supplier_id').innerHTML = '';
                }
            }
            else{
                window.location = '/Manage_Data/sview/'+data.suppliers_id
            }			
            hidePopup();	
        }
        else{
            if(frompage==='addpo'){
                document.getElementById('errmsg_supplier_id').innerHTML = '';
            }
            if(data.savemsg==='nameEmailExist') document.getElementById('error_supplier').innerHTML = Translate('This name with email already exists! Please try again with a different name with email.');
            else if(data.savemsg==='nameEmailExistInArchive') document.getElementById('error_supplier').innerHTML = Translate('This name with email already exists <b>IN ARCHIVED</b>! Please try again with a different name with email.');
            else if(data.savemsg==='errorAdding') document.getElementById('error_supplier').innerHTML = Translate('Error occured while adding new supplier! Please try again.');
            else if(data.savemsg==='companyEmailExist') document.getElementById('error_supplier').innerHTML = Translate('This company name with email already exists! Please try again with a different company name with email.');
        }		
    }
	return false;
}

async function archiveSupplier(suppliers_id){
	confirm_dialog(Translate('Supplier Archive'), Translate('Are you sure you want to archive this information?'), (hidePopup)=>{
        archiveData('/Manage_Data/AJ_suppliers_archive','/Manage_Data/suppliers', {"suppliers_id":suppliers_id}, Translate('Supplier'),Translate('Could not found supplier for archive'));
        hidePopup();
    });				
}

async function unarchiveSupplier(suppliers_id){
	confirm_dialog(Translate('Supplier')+' '+Translate('Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{       
        unarchiveData(`/Manage_Data/sview/${suppliers_id}`, {tablename:'suppliers', tableidvalue:suppliers_id, publishname:'suppliers_publish'});
        hidePopup();
    });
}

//_________________SView-Part________________________
async function sview(){
    let segment4 = 1;
    if(pathArray.length>4){segment4 = pathArray[4];}
   
    let page = parseInt(segment4);
    if(page==='' || isNaN(page)){page = 1;}
    let suppliers_id = parseInt(segment3);
    if(suppliers_id==='' || isNaN(suppliers_id)){suppliers_id = 0;}    
 
    let input;
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const titleRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding: 5px;"});
            const headerTitle = cTag('h2');
            headerTitle.append(Translate('Suppliers Information')+' ');
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This page captures the accounts settings')});
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
            let supplierButton = cTag('a', {class: "btn defaultButton", 'href': "/Manage_Data/suppliers", title: Translate('All Suppliers')});
            supplierButton.append(cTag('i',{'class':'fa fa-list'}),' ',Translate('All Suppliers'));
        titleRow.appendChild(supplierButton);
    Dashboard.appendChild(titleRow);

        const supplierContainer = cTag('div', {class: "flexSpaBetRow"});
        supplierContainer.appendChild(leftsideMenu());

            let callOutDivStyle = "margin-top: 0; background: #fff"
            if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
            let supplierColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                    let supplierDetailColumn = cTag('div', {class: "columnSM12 outerListsTable"});
                        const supplierHeader = cTag('header', {class: "imageContainer flexSpaBetRow"});
                            let imageColumn = cTag('div', {class: "columnSM3"});
                                const imageDiv = cTag('div', {class: "image"});
                                    let manImage = cTag('img', {class: "img-responsive", 'alt': "My Profile", 'src': "/assets/images/man.jpg"});
                                imageDiv.appendChild(manImage);
                            imageColumn.appendChild(imageDiv);
                        supplierHeader.appendChild(imageColumn);
                            let imageContentColumn = cTag('div', {class: "columnSM9"});
                                let imageContent = cTag('div', {class: "image_content", 'style': "text-align: left;"});
                                    let imageContentTitle = cTag('h3', {id: "company"});
                                imageContent.appendChild(imageContentTitle);

                                    let name = cTag('h3', {id: "name"});
                                imageContent.appendChild(name);

                                    const envelopeDiv = cTag('div', {'style': "margin-bottom: 10px;"});
                                        const envelopeIcon = cTag('i', {class: "fa fa-envelope-o", 'style': "font-size: 16px;"});
                                    envelopeDiv.appendChild(envelopeIcon);
                                        const emailSpan = cTag('span', {id: "email", 'style': "padding-left: 15px; font-weight: bold; color: #969595;"});
                                    envelopeDiv.appendChild(emailSpan);
                                imageContent.appendChild(envelopeDiv);

                                    const phoneDiv = cTag('div', {'style': "margin-bottom: 10px;"});
                                        const phoneIcon = cTag('i', {class: "fa fa-phone", 'style': "font-size: 16px;"});
                                    phoneDiv.appendChild(phoneIcon);
                                        const contactSpan = cTag('span', {id: "contact_no", 'style': "padding-left: 15px; font-weight: bold; color: #969595;"});
                                    phoneDiv.appendChild(contactSpan);
                                imageContent.appendChild(phoneDiv);

                                    const mapDiv = cTag('div', {'style': "margin-bottom: 10px;"});
                                        const markerIcon = cTag('i', {class: "fa fa-map-marker", 'style': "font-size: 16px;"});
                                    mapDiv.appendChild(markerIcon);
                                        const addressSpan = cTag('span', {id: "shipping_address", 'style': "padding-left: 15px; font-weight: bold; color: #969595;"});
                                    mapDiv.appendChild(addressSpan);
                                imageContent.appendChild(mapDiv);
                                imageContent.appendChild(cTag('p',{'id':'actionButtons'}));
                            imageContentColumn.appendChild(imageContent);
                        supplierHeader.appendChild(imageContentColumn);
                    supplierDetailColumn.appendChild(supplierHeader);
                callOutDiv.appendChild(supplierDetailColumn);

                    const activityContainer = cTag('div', {class: "flex"});
                        let activityColumn = cTag('div', {class: "columnXS12"});
                            const hiddenProperties = {
                                'note_forTable': 'suppliers',
                                'table_idValue': suppliers_id,
                            }
                        activityColumn.appendChild(historyTable(Translate('Supplier Purchase History'),hiddenProperties));
                    activityContainer.appendChild(activityColumn);
                callOutDiv.appendChild(activityContainer);
            supplierColumn.appendChild(callOutDiv);
        supplierContainer.appendChild(supplierColumn);
    Dashboard.appendChild(supplierContainer);

    addCustomeEventListener('filter',filter_Manage_Data_sview);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_sview);
    AJ_sview_MoreInfo();
}

async function AJ_sview_MoreInfo(){
    let suppliers_id = document.getElementById("table_idValue").value;
    const jsonData = {};
	jsonData['suppliers_id'] = suppliers_id;
    const url = '/'+segment1+'/AJ_sview_MoreInfo';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        document.querySelector('#company').innerHTML = data.company;
        document.querySelector('#name').innerHTML = data.first_name + ' ' + data.last_name;
        document.querySelector('#contact_no').innerHTML = data.contact_no;
        document.querySelector('#email').innerHTML = data.email;
        document.querySelector('#shipping_address').innerHTML = data.shipping_address;
        document.querySelector('#table_idValue').value = data.suppliers_id;
        if(data.suppliers_publish===0){
            document.querySelectorAll(".btnAddSup").forEach(oneField=>{
                if(oneField.style.display !== 'none'){
                    oneField.style.display = 'none';
                }
            });
        }
        if(data.suppliers_publish===1){
            let pTag = document.getElementById('actionButtons');
                const editInput = cTag('input', {'type': "button", class: "btn editButton btnAddSup", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Edit'), 'value': Translate('Edit')});
                editInput.addEventListener('click', function(){addnewsupplierform('editsuppliers', suppliers_id);});
            pTag.appendChild(editInput);
                const mergeSupplier = cTag('input', {'type': "button", class: "btn defaultButton btnAddSup", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Merge Suppliers'), 'value': Translate('Merge Suppliers')});
                mergeSupplier.addEventListener('click', ()=>AJmergeSuppliersPopup(data.suppliers_id));
            pTag.appendChild(mergeSupplier);                        
                const arciveButton = cTag('button', {'id':'arciveButton', class: "btn archiveButton", 'style': "margin-bottom: 10px;", title: Translate('Archive')});
                arciveButton.innerHTML = Translate('Archive'); 
                if(data.allowed.length===0||(!Array.isArray(data.allowed) && !data.allowed['25'].includes('cnas'))) arciveButton.addEventListener('click',()=>archiveSupplier(data.suppliers_id));
                else arciveButton.addEventListener('click', function (){noPermissionWarning(Translate('Supplier'))});                                           
            pTag.appendChild(arciveButton);
        }
        else{
            let pTag = document.getElementById('actionButtons');
                const unarciveButton = cTag('button', {'id':'unarciveButton', class: "btn bgcoolblue", 'style': "margin-right: 15px; margin-bottom: 10px;", title: Translate('Unarchive')});
                unarciveButton.innerHTML = Translate('Unarchive');    
                unarciveButton.addEventListener('click',()=>unarchiveSupplier(data.suppliers_id))                                        
            pTag.appendChild(unarciveButton);
        }
        
        getSessionData()
        filter_Manage_Data_sview(true);
    }
}

async function filter_Manage_Data_sview(){
    let page = 1;
    document.getElementById("page").value = page;

	const jsonData = {};
	jsonData['suppliers_id'] = document.querySelector('#table_idValue').value;
    jsonData['shistory_type'] = document.querySelector('#shistory_type').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
    
    const url = '/'+segment1+'/AJgetHPageSupplier/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){    
        storeSessionData(jsonData);
        document.querySelector("#totalTableRows").value = data.totalRows;
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

async function loadTableRows_Manage_Data_sview(){
	const jsonData = {};
	jsonData['suppliers_id'] = document.querySelector('#table_idValue').value;
	jsonData['shistory_type'] = document.querySelector('#shistory_type').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetHPageSupplier';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        setTableHRows(data.tableRows, activityFieldAttributes);
        onClickPagination();
    }
}

async function AJmergeSuppliersPopup(suppliers_id){
    const jsonData = {suppliers_id};
    
	if(suppliers_id>0){
        const url = '/'+segment1+'/AJget_SuppliersPopup';
        
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            const formDialog = cTag('div');
                const errorMsg = cTag('div', {id: "error_supplier", class: "errormsg"});
            formDialog.appendChild(errorMsg);
                let pTag, inputField;
                const mergeCustomerForm = cTag('form', {'action': "#", name: "frmMergeSupplier", id: "frmMergeSupplier", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
                    const mergeThisText = cTag('div', {class: "flexSpaBetRow"});
                        const mergeThisTextColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                            const mergeThisTextTitle = cTag('h4', {class:'borderbottom'});
                            mergeThisTextTitle.innerHTML = Translate('Merge this supplier information');
                        mergeThisTextColumn.appendChild(mergeThisTextTitle);
                    mergeThisText.appendChild(mergeThisTextColumn);
                mergeCustomerForm.appendChild(mergeThisText);

                    const mergeCustomerRow = cTag('div', {class: "flexSpaBetRow"});
                        const mergeCustomerColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;"});
                            pTag = cTag('p');
                            pTag.innerHTML = Translate('Name');
                                let nameSpan = cTag('span');
                                nameSpan.innerHTML = data.first_name+' '+data.last_name;
                            pTag.append(': ', nameSpan);
                        mergeCustomerColumn.appendChild(pTag);

                            pTag = cTag('p');
                            pTag.innerHTML = Translate('Phone No.');
                                let phoneSpan = cTag('span');
                                phoneSpan.innerHTML = data.contact_no;
                            pTag.append(': ', phoneSpan);
                        mergeCustomerColumn.appendChild(pTag);

                            pTag = cTag('p');
                            pTag.innerHTML = Translate('Email');
                                let emailSpan = cTag('span');
                                emailSpan.innerHTML = data.email;
                            pTag.append(': ', emailSpan);
                        mergeCustomerColumn.appendChild(pTag);

                            pTag = cTag('p');
                            pTag.innerHTML = Translate('Company');
                                let companySpan = cTag('span');
                                companySpan.innerHTML = data.company;
                            pTag.append(': ', companySpan);
                        mergeCustomerColumn.appendChild(pTag);
                    mergeCustomerRow.appendChild(mergeCustomerColumn);
                mergeCustomerForm.appendChild(mergeCustomerRow);

                    const toThisRow = cTag('div', {class: "flexSpaBetRow"});
                        const toThisColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                            const toThisTitle = cTag('h4', {class:'borderbottom'});
                            toThisTitle.innerHTML = Translate('To this supplier');
                        toThisColumn.appendChild(toThisTitle);
                    toThisRow.appendChild(toThisColumn);
                mergeCustomerForm.appendChild(toThisRow);

                    const customerNameRow = cTag('div', {class: "flexSpaBetRow"});
                        const customerNameColumn = cTag('div', {class: "columnSM2", 'align': "left"});
                            const nameLabel = cTag('label', {'for': "supplier"});
                            nameLabel.innerHTML = Translate('Name');
                                let requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            nameLabel.appendChild(requiredField);
                        customerNameColumn.appendChild(nameLabel);
                    customerNameRow.appendChild(customerNameColumn);

                        const nameSearchColumn = cTag('div', {class: "columnSM10"});
                            inputField = cTag('input', {"maxlength": 50, 'type': "text", 'value': "", 'required': true, name: "supplier", id: "supplier", class: "form-control", 'placeholder': Translate('Search Suppliers')});
                        nameSearchColumn.appendChild(inputField);
                    customerNameRow.appendChild(nameSearchColumn);
                mergeCustomerForm.appendChild(customerNameRow);

                    const customerInfoRow = cTag('div', {class: "flexSpaBetRow"});
                        const customerInfoColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;",  id: "toSupplierInfo"});
                    customerInfoRow.appendChild(customerInfoColumn);
                mergeCustomerForm.appendChild(customerInfoRow);

                    inputField = cTag('input', {'type': "hidden", name: "fromsuppliers_id", id: "fromsuppliers_id", 'value': suppliers_id});
                mergeCustomerForm.appendChild(inputField);
                    inputField = cTag('input', {'type': "hidden", name: "tosuppliers_id", id: "tosuppliers_id", 'value': 0});
                mergeCustomerForm.appendChild(inputField);
            formDialog.appendChild(mergeCustomerForm);

            popup_dialog600(Translate('Merge the following two suppliers'), formDialog,Translate('Merge Suppliers'), AJmergeSupplier);
            document.querySelectorAll('.popup_footer_button')[1].style.display = 'none';//hide Merge initially
            setTimeout(function() {		
                document.getElementById("supplier").focus();
                if(document.getElementById("supplier")){AJautoComplete('supplier');}
            }, 500);
        }
        return true;
    }
}
async function AJmergeSupplier(){
	const error_supplier = document.getElementById('error_supplier');
    error_supplier.innerHTML = '';
	if(parseInt(document.getElementById("tosuppliers_id").value) ===0){
		showTopMessage('alert_msg',Translate('You did not choose any supplier to be Merged. Please search and choose a supplier.'));            
        return false;
	}
    actionBtnClick('.btnmodel', Translate('Merging Suppliers'), 1);
	
    const jsonData = serialize('#frmMergeSupplier');
    const url = '/'+segment1+'/AJmergeSupplier';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.savemsg ==='Success' && data.id>0){
			window.location = `/${segment1}/sview/${data.id}`;
		}
		else{
            actionBtnClick('.btnmodel', Translate('Merge Suppliers'), 0);
            showTopMessage('alert_msg', Translate('There is an error while merging information.'));             
		}
	}
	return false;
}

/*======Category Module======*/
async function category(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Categories')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top: 0; background: #fff; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let categoryContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                let categoryRow = cTag('div', {class: "flexSpaBetRow"});
                    const categoryHeaderColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    categoryHeaderColumn.appendChild(subHeader_Search_Bar(Translate('Category'),Translate('Search Categories'),filter_Manage_Data_category));
                        const categoryTableRow = cTag('div', {class: "flexSpaBetRow"});
                            const categoryTableColumn = cTag('div', {class: "columnXS12"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Category Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            categoryTableColumn.appendChild(listTable);
                        categoryTableRow.appendChild(categoryTableColumn);
                    categoryHeaderColumn.appendChild(categoryTableRow);
                    addPaginationRowFlex(categoryHeaderColumn);
                categoryRow.appendChild(categoryHeaderColumn);

                    const addProductCategory = cTag('div', {class: "columnXS12 columnMD5"});
                        let productCategoryHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;",  id: "formtitle"});
                        productCategoryHeader.innerHTML =Translate('Add New Product Category');
                    addProductCategory.appendChild(productCategoryHeader);

                        const addProductCategoryForm = cTag('form', {'action': "#", name: "frmcategory", id: "frmcategory", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        addProductCategoryForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'category_name',AJsave_category));
                            const addProductCategoryRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let categoryLabel = cTag('label', {'for': "category_name"});
                                categoryLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                categoryLabel.appendChild(requiredField);
                            addProductCategoryRow.appendChild(categoryLabel);
                                let input = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "category_name", id: "category_name",  'value': "", 'size': 35, 'maxlength': 35});
                            addProductCategoryRow.appendChild(input);
                        addProductCategoryForm.appendChild(addProductCategoryRow);
                        addProductCategoryForm.appendChild(controller_bar('category_id',resetForm_category));
                    addProductCategory.appendChild(addProductCategoryForm);
                categoryRow.appendChild(addProductCategory);
            callOutDiv.appendChild(categoryRow);
        categoryContainer.appendChild(callOutDiv);
    parentRow.appendChild(categoryContainer);
    showTableData.appendChild(parentRow);   

    addCustomeEventListener('filter',filter_Manage_Data_category);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_category);
    addCustomeEventListener('reset',resetForm_category);
    getSessionData();    
    filter_Manage_Data_category(true);
}

async function filter_Manage_Data_category(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_category/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Category Name'), 'align':'left'}],'category',filter_Manage_Data_category,resetForm_category);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_category(){
    const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_category';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Category Name'), 'align':'left'}],'category',filter_Manage_Data_category,resetForm_category);
        onClickPagination();
    }
}

async function AJsave_ManageData(event=false,fieldID,proceedToSave){
    if(event){event.preventDefault();}

    let submit =  document.querySelector("#submit");
    submit.value = Translate('Saving')+'...';
    submit.disabled = true;

    let jsonData = {keyword_search:document.getElementById(fieldID).value,sdata_type: "Archived",limit: 9};
    const url = `/Manage_Data/AJgetPage_${segment2}/filter`;

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
    }
}

async function AJsave_category(event=false){
    if(event){event.preventDefault();}

	let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmcategory");
    const url = '/'+segment1+'/AJsave_category';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
            resetForm_category();
            if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
            }
            else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
            }
            filter_Manage_Data_category();	
        }
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occurred while adding new category! Please try again.'), Translate('Ok'));
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

async function resetForm_category(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Category');
	document.querySelector("#category_id").value = 0;
	document.querySelector("#category_name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======Manufacturer Module======*/
async function manufacturer(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';

    hidden_items(showTableData,page);

    showTableData.appendChild(listHeader(Translate('Manage Manufacturer')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top:0; background:#FFF; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let manufacturerContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                const manufacturerRow = cTag('div', {class: "flexSpaBetRow"});
                    const manufacturerColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    manufacturerColumn.appendChild(subHeader_Search_Bar(Translate('Manufacturer'),Translate('Search Manufacturers'),filter_Manage_Data_manufacturer));
                        const manufacturerTable = cTag('div', {class: "flexSpaBetRow"});
                            const manufacturerTableColumn = cTag('div', {class: "columnXS12", 'style': "position:relative;"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Manufacturer Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            manufacturerTableColumn.appendChild(listTable);
                        manufacturerTable.appendChild(manufacturerTableColumn);
                    manufacturerColumn.appendChild(manufacturerTable);
                    addPaginationRowFlex(manufacturerColumn);
                manufacturerRow.appendChild(manufacturerColumn);

                    const newManufacturerColumn = cTag('div', {class: "columnXS12 columnMD5"});
                        let manufacturerTitle = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;", id: "formtitle"});
                        manufacturerTitle.innerHTML =Translate('Add New Manufacturer');
                    newManufacturerColumn.appendChild(manufacturerTitle);

                        const manufacturerForm = cTag('form', {'action': "#", name: "frmmanufacturer", id: "frmmanufacturer", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        manufacturerForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'name',AJsave_manufacturer));
                            const manufacturerFormRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let manufacturerLabel = cTag('label', {'for': "name"});
                                manufacturerLabel.innerHTML = Translate('Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                manufacturerLabel.appendChild(requiredField);
                            manufacturerFormRow.appendChild(manufacturerLabel);
                                let inputField = cTag('input', {'type': "text",'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "name", id: "name",  'value': "", 'size': 30, 'maxlength': 30});
                            manufacturerFormRow.appendChild(inputField);
                        manufacturerForm.appendChild(manufacturerFormRow);
                        manufacturerForm.appendChild(controller_bar('manufacturer_id',resetForm_manufacturer));
                    newManufacturerColumn.appendChild(manufacturerForm);
                manufacturerRow.appendChild(newManufacturerColumn);                
            callOutDiv.appendChild(manufacturerRow);
        manufacturerContainer.appendChild(callOutDiv);
    parentRow.appendChild(manufacturerContainer);
    showTableData.appendChild(parentRow);    
    
    addCustomeEventListener('filter',filter_Manage_Data_manufacturer);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_manufacturer);
    addCustomeEventListener('reset',resetForm_manufacturer);
    getSessionData();
    filter_Manage_Data_manufacturer(true);
}
async function filter_Manage_Data_manufacturer(){
    let page = 1;
	document.querySelector("#page").value = page;
    	
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;			
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;		
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_manufacturer/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Manufacturer Name'), 'align':'left'}],'manufacturer',filter_Manage_Data_manufacturer,resetForm_manufacturer);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_manufacturer(){
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_manufacturer';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows,[{'data-title':Translate('Manufacturer Name'), 'align':'left'}],'manufacturer',filter_Manage_Data_manufacturer,resetForm_manufacturer);
        onClickPagination();
    }
}

async function AJsave_manufacturer(event=false){
    if(event){event.preventDefault();}
    let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmmanufacturer");
    const url = '/'+segment1+'/AJsave_manufacturer';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
			resetForm_manufacturer();
			if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			filter_Manage_Data_manufacturer();
		}
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occured while adding new manufacturer! Please try again.'), Translate('Ok'));
		}  
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
		}  
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}
        submit.value = Translate('Add')
        submit.disabled = false;   
    }
	return false;
}

async function resetForm_manufacturer(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Manufacturer');
	document.querySelector("#manufacturer_id").value = 0;
	document.querySelector("#name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======Repair_Problems Module======*/

async function repair_problems(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Repair Problem')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top:0; background:#FFF; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let repairProblemColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                const repairProblemRow = cTag('div', {class: "flexSpaBetRow"});
                    const repairProblemHeader = cTag('div', {class: "columnXS12 columnMD7"});
                    repairProblemHeader.appendChild(subHeader_Search_Bar(Translate('Repair Problem'),Translate('Search here'),filter_Manage_Data_repair_problems));
                        const repairProblemTable = cTag('div', {class: "flexSpaBetRow"});
                            const repairTableColumn = cTag('div', {class: "columnXS12", 'style': "position:relative;"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Problem Name');

                                            const thCol1 = cTag('th', {'width': "65%"});
                                            thCol1.innerHTML = Translate('Additional Disclaimer');
                                        listHeadRow.append(thCol0, thCol1);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            repairTableColumn.appendChild(listTable);
                        repairProblemTable.appendChild(repairTableColumn);
                    repairProblemHeader.appendChild(repairProblemTable);
                    addPaginationRowFlex(repairProblemHeader);
                repairProblemRow.appendChild(repairProblemHeader);

                    const newRepairColumn = cTag('div', {class: "columnXS12 columnMD5"});
                        let newRepairTitle = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;", id: "formtitle"});
                        newRepairTitle.innerHTML = Translate('Add New Repair Problems');
                    newRepairColumn.appendChild(newRepairTitle);

                        const newRepairForm = cTag('form', {'action': "#", name: "frmrepair_problems", id: "frmrepair_problems", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        newRepairForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'name',AJsave_repair_problems));
                            const newRepairRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                const newRepairLabel = cTag('label', {'for': "name"});
                                newRepairLabel.innerHTML =Translate('Repair Problem');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML ='*';
                                newRepairLabel.appendChild(requiredField);
                            newRepairRow.appendChild(newRepairLabel);
                                let inputField = cTag('input', {'type': "text", 'required': "", class:"form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "name", id: "name", 'value': "", 'size': 50, 'maxlength': 50});
                            newRepairRow.appendChild(inputField);
                        newRepairForm.appendChild(newRepairRow);

                            const additionalRow = cTag('div', {class: "flexSpaBetRow"});
                                const additionalLabel = cTag('label', {'for': "additional_disclaimer"});
                                additionalLabel.innerHTML =Translate('Additional Disclaimer');
                            additionalRow.appendChild(additionalLabel);
                                let additionalTextArea = cTag('textarea', {class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", 'rows': 15, name: "additional_disclaimer", id: "additional_disclaimer"});
                            additionalRow.appendChild(additionalTextArea);
                        newRepairForm.appendChild(additionalRow);
                        newRepairForm.appendChild(controller_bar('repair_problems_id',resetForm_repair_problems));
                    newRepairColumn.appendChild(newRepairForm);
                repairProblemRow.appendChild(newRepairColumn);
            callOutDiv.appendChild(repairProblemRow);
        repairProblemColumn.appendChild(callOutDiv);
    parentRow.appendChild(repairProblemColumn);
    showTableData.appendChild(parentRow);
    
    addCustomeEventListener('filter',filter_Manage_Data_repair_problems);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_repair_problems);
    addCustomeEventListener('reset',resetForm_repair_problems);
    getSessionData();
    filter_Manage_Data_repair_problems(true);
}

async function filter_Manage_Data_repair_problems(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	

    const url = '/'+segment1+'/AJgetPage_repair_problems/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Problem Name'), 'align':'left'}, {'datatitle':Translate('Additional Disclaimer'), 'align':'left'}],'repair_problems',filter_Manage_Data_repair_problems,resetForm_repair_problems);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_repair_problems(){
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_repair_problems';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Problem Name'), 'align':'left'}, {'datatitle':Translate('Additional Disclaimer'), 'align':'left'}],'repair_problems',filter_Manage_Data_repair_problems,resetForm_repair_problems);
        onClickPagination();
    }
}

async function AJsave_repair_problems(event=false){
    if(event){event.preventDefault();}
    let submit =  document.querySelector("#submit");
        
    const jsonData = serialize("#frmrepair_problems");
    const url = '/'+segment1+'/AJsave_repair_problems';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
			resetForm_repair_problems();
			if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			filter_Manage_Data_repair_problems();
		}
		else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('An error occurred while adding new repair problems! Please try again.'), Translate('Ok'));
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

async function resetForm_repair_problems(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Repair Problems');
	document.querySelector("#repair_problems_id").value = 0;
	document.querySelector("#name").value = '';
	document.querySelector("#additional_disclaimer").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======Brand_Model Module======*/

async function brand_model(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Brand Model')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top:0; background:#FFF; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let bandModelColumn = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                const bandModelRow = cTag('div', {class: "flexSpaBetRow"});
                    const bandModelHeader = cTag('div', {class: "columnXS12 columnMD7"});
                    bandModelHeader.appendChild(subHeader_Search_Bar(Translate('Brand Model'),Translate('Search here'),filter_Manage_Data_brand_model));
                        const bandModelTable = cTag('div', {class: "flexSpaBetRow"});
                            const bandModelTableColumn = cTag('div', {class: "columnXS12", 'style': "position:relative;"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Brand Name');

                                            const thCol1 = cTag('th', {'width': "50%"});
                                            thCol1.innerHTML = Translate('Model Name');
                                        listHeadRow.append(thCol0, thCol1);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            bandModelTableColumn.appendChild(listTable);
                        bandModelTable.appendChild(bandModelTableColumn);
                    bandModelHeader.appendChild(bandModelTable);
                    addPaginationRowFlex(bandModelHeader);
                bandModelRow.appendChild(bandModelHeader);

                    const addBandModel = cTag('div', {class: "columnXS12 columnMD5"});
                        let bandModelTitle = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;", id: "formtitle"});
                        bandModelTitle.innerHTML = Translate('Add New Brand Models');
                    addBandModel.appendChild(bandModelTitle);

                        const bandModelForm = cTag('form', {'action': "#", name: "frmbrand_model", id: "frmbrand_model", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        bandModelForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'brand',AJsave_brand_model));
                            const bandNameRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                const bandNameLabel = cTag('label', {'for': "brand"});
                                bandNameLabel.innerHTML = Translate('Brand Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML = '*';
                                bandNameLabel.appendChild(requiredField);
                            bandNameRow.appendChild(bandNameLabel);
                                const inputField = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "brand", id: "brand", 'value': "", 'size': 35, 'maxlength': 35});
                            bandNameRow.appendChild(inputField);
                        bandModelForm.appendChild(bandNameRow);

                            const modelNameRow = cTag('div', {class: "flexSpaBetRow"});
                                const modelLabel = cTag('label', {'for': "model"});
                                modelLabel.innerHTML = Translate('Model Name');
                            modelNameRow.appendChild(modelLabel);
                                const modelInputField = cTag('input', {'type': "text", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "model", id: "model", 'value': "", 'size': 25, 'maxlength': 25});
                            modelNameRow.appendChild(modelInputField);
                        bandModelForm.appendChild(modelNameRow);
                        bandModelForm.appendChild(controller_bar('brand_model_id',resetForm_brand_model));
                    addBandModel.appendChild(bandModelForm);
                bandModelRow.appendChild(addBandModel);                
            callOutDiv.appendChild(bandModelRow);
        bandModelColumn.appendChild(callOutDiv);
    parentRow.appendChild(bandModelColumn);
    showTableData.appendChild(parentRow);  
    
    addCustomeEventListener('filter',filter_Manage_Data_brand_model);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_brand_model);
    addCustomeEventListener('reset',resetForm_brand_model);
    getSessionData();
    filter_Manage_Data_brand_model(true);
}

async function filter_Manage_Data_brand_model(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;	
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_brand_model/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Brand Name'), 'align':'left'}, {'datatitle':Translate('Model Name'), 'align':'left'}], 'brand_model',filter_Manage_Data_brand_model,resetForm_brand_model);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_brand_model(){
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	    
    const url = '/'+segment1+'/AJgetPage_brand_model';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Brand Name'), 'align':'left'}, {'datatitle':Translate('Model Name'), 'align':'left'}], 'brand_model',filter_Manage_Data_brand_model,resetForm_brand_model);
        onClickPagination();
    }
}

async function AJsave_brand_model(event=false){
    if(event){event.preventDefault();}
    let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmbrand_model");
    const url = '/'+segment1+'/AJsave_brand_model';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg==='Add' || data.savemsg==='Update'){
			resetForm_brand_model();
			if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			filter_Manage_Data_brand_model();
		}
		else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occured while adding new model! Please try again.'), Translate('Ok'));
		}  
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
		}  
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}
        submit.value = Translate('Add')
        submit.disabled = false;     
    }
	return false;
}

async function resetForm_brand_model(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Brand Models');
	document.querySelector("#brand_model_id").value = 0;
	document.querySelector("#brand").value = '';
	document.querySelector("#model").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======Vendors Module======*/

async function vendors(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Vendors')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top:0; border-left:1px solid #EEEEEE; background:#FFF; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let vendorContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                const vendorRow = cTag('div', {class: "flexSpaBetRow"});
                    const vendorColumn = cTag('div', {class: "columnXS12 columnMD7"});
                    vendorColumn.appendChild(subHeader_Search_Bar(Translate('Vendors'),Translate('Search Vendors'),filter_Manage_Data_vendors));
                        const vendorTable = cTag('div', {class: "flexSpaBetRow"});
                            const vendorTableColumn = cTag('div', {class: "columnXS12", 'style': "position:relative;"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Vendor Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            vendorTableColumn.appendChild(listTable);
                        vendorTable.appendChild(vendorTableColumn);
                    vendorColumn.appendChild(vendorTable);
                    addPaginationRowFlex(vendorColumn);
                vendorRow.appendChild(vendorColumn);

                    const newVendorColumn = cTag('div', {class: "columnXS12 columnMD5"});
                        let newVendorHeader = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;", id: "formtitle"});
                        newVendorHeader.innerHTML =Translate('Add New Vendor');
                    newVendorColumn.appendChild(newVendorHeader);

                        const vendorForm = cTag('form', {'action': "#", name: "frmvendors", id: "frmvendors", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        vendorForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'name',AJsave_vendors));
                            const newVendorRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let vendorLabel = cTag('label', {'for': "name"});
                                vendorLabel.innerHTML = Translate('Vendor Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML = '*';
                                vendorLabel.appendChild(requiredField);
                            newVendorRow.appendChild(vendorLabel);
                                let inputField = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "name", id: "name", 'value': "", 'size': 30, 'maxlength': 35});
                            newVendorRow.appendChild(inputField);
                        vendorForm.appendChild(newVendorRow);
                        vendorForm.appendChild(controller_bar('vendors_id',resetForm_vendors));
                    newVendorColumn.appendChild(vendorForm);
                vendorRow.appendChild(newVendorColumn);
            callOutDiv.appendChild(vendorRow);
        vendorContainer.appendChild(callOutDiv);
    parentRow.appendChild(vendorContainer);
    showTableData.appendChild(parentRow);
    
    addCustomeEventListener('filter',filter_Manage_Data_vendors);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_vendors);
    addCustomeEventListener('reset',resetForm_vendors);
    getSessionData();
    filter_Manage_Data_vendors(true);
}

async function filter_Manage_Data_vendors(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_vendors/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Vendor Name'), 'align':'left'}], 'vendors',filter_Manage_Data_vendors,resetForm_vendors);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_vendors(){
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;	 

    const url = '/'+segment1+'/AJgetPage_vendors';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Vendor Name'), 'align':'left'}], 'vendors',filter_Manage_Data_vendors,resetForm_vendors);
        onClickPagination();
    }
}

async function AJsave_vendors(event=false){
    if(event){event.preventDefault();}
    let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmvendors");
    const url = '/'+segment1+'/AJsave_vendors';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg==='Add' || data.savemsg==='Update'){
			resetForm_vendors();
			if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			filter_Manage_Data_vendors();		
		}
		else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occured while adding new vendors! Please try again.'), Translate('Ok'));
		}  
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
		}  
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}
        submit.value = Translate('Add')
        submit.disabled = false;
    }
	return false;
}

async function resetForm_vendors(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Vendor');
	document.querySelector("#vendors_id").value = 0;
	document.querySelector("#name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======Expense_type Module======*/

async function expense_type(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Expense Type')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top:0; background:#FFF; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let expenseTypeContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                const expenseTypeRow = cTag('div', {class: "flexSpaBetRow"});
                    const expenseTypeHeader = cTag('div', {class: "columnXS12 columnMD7"});
                    expenseTypeHeader.appendChild(subHeader_Search_Bar(Translate('Expense Type'),Translate('Search Expense Type'),filter_Manage_Data_expense_type));
                        const expenseTypeTable = cTag('div', {class: "flexSpaBetRow"});
                            const expenseTypeTableColumn = cTag('div', {class: "columnXS12", 'style': "position:relative;"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Expense Type Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            expenseTypeTableColumn.appendChild(listTable);
                        expenseTypeTable.appendChild(expenseTypeTableColumn);
                    expenseTypeHeader.appendChild(expenseTypeTable);
                    addPaginationRowFlex(expenseTypeHeader);
                expenseTypeRow.appendChild(expenseTypeHeader);

                    const addNewExpenseColumn = cTag('div', {class: "columnXS12 columnMD5"});
                        let expenseTitle = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;", id: "formtitle"});
                        expenseTitle.innerHTML =Translate('Add New Expense Type');
                    addNewExpenseColumn.appendChild(expenseTitle);
                        const newExpenseForm = cTag('form', {'action': "#", name: "frmexpense_type", id: "frmexpense_type", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        newExpenseForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'name',AJsave_expense_type));
                            const newExpenseRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                let expenseLabel = cTag('label', {'for': "name"});
                                expenseLabel.innerHTML = Translate('Expense Type Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML = '*';
                                expenseLabel.appendChild(requiredField);
                            newExpenseRow.appendChild(expenseLabel);
                                let inputField = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-top: 10px; margin-bottom: 10px;", name: "name", id: "name", 'value': "", 'size': 35, 'maxlength': 35});
                            newExpenseRow.appendChild(inputField);
                        newExpenseForm.appendChild(newExpenseRow);
                        newExpenseForm.appendChild(controller_bar('expense_type_id',resetForm_expense_type));
                    addNewExpenseColumn.appendChild(newExpenseForm);
                expenseTypeRow.appendChild(addNewExpenseColumn);                
            callOutDiv.appendChild(expenseTypeRow);
        expenseTypeContainer.appendChild(callOutDiv);
    parentRow.appendChild(expenseTypeContainer);
    showTableData.appendChild(parentRow);
    
    addCustomeEventListener('filter',filter_Manage_Data_expense_type);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_expense_type);
    addCustomeEventListener('reset',resetForm_expense_type);
    getSessionData();
    filter_Manage_Data_expense_type(true);
}

async function filter_Manage_Data_expense_type(){
    let page = 1;
	document.querySelector("#page").value = page;    
	
	const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_expense_type/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Expense Type Name'), 'align':'left'}], 'expense_type',filter_Manage_Data_expense_type,resetForm_expense_type);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_expense_type(){
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;	  

    const url = '/'+segment1+'/AJgetPage_expense_type';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Expense Type Name'), 'align':'left'}], 'expense_type',filter_Manage_Data_expense_type,resetForm_expense_type);
        onClickPagination();
    }
}

async function AJsave_expense_type(event=false){
    if(event){event.preventDefault();}
    let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmexpense_type");
    const url = '/'+segment1+'/AJsave_expense_type';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
			resetForm_expense_type();
			if(data.savemsg==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			filter_Manage_Data_expense_type();
		}
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occured while adding new expense type! Please try again.'), Translate('Ok'));
		}  
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
		}  
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}
        submit.value = Translate('Add')
        submit.disabled = false;    
    }
	return false;
}

async function resetForm_expense_type(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Expense Type');
	document.querySelector("#expense_type_id").value = 0;
	document.querySelector("#name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

/*======Customer Type Module======*/

async function customer_type(){
    let page = parseInt(segment3);
    if(page==='' || isNaN(page)){page = 1;}

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
    hidden_items(showTableData,page);
    showTableData.appendChild(listHeader(Translate('Manage Customer Type')));
    
    const parentRow = cTag('div', {class: "flexSpaBetRow"});
    parentRow.appendChild(leftsideMenu());

        let callOutDivStyle = "margin-top:0; background:#FFF; padding-top: 0;"
        if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
        let customerTypeContainer = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
            let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                const customerTypeRow = cTag('div', {class: "flexSpaBetRow"});
                    const customerTypeHeader = cTag('div', {class: "columnXS12 columnMD7"});
                    customerTypeHeader.appendChild(subHeader_Search_Bar(Translate('Customer Type'),Translate('Search Customer Type'),filter_Manage_Data_customer_type));
                        const customerTypeTable = cTag('div', {class: "flexSpaBetRow"});
                            const customerTableColumn = cTag('div', {class: "columnXS12", 'style': "position:relative;"});
                                const listTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"});
                                    const listHead = cTag('thead', {class: "cf"});
                                        const listHeadRow = cTag('tr');
                                            const thCol0 = cTag('th');
                                            thCol0.innerHTML = Translate('Customer Type Name');
                                        listHeadRow.appendChild(thCol0);
                                    listHead.appendChild(listHeadRow);
                                listTable.appendChild(listHead);
                                    const listBody = cTag('tbody', {id: "tableRows", 'style': "cursor: pointer;"});
                                listTable.appendChild(listBody);
                            customerTableColumn.appendChild(listTable);
                        customerTypeTable.appendChild(customerTableColumn);
                    customerTypeHeader.appendChild(customerTypeTable);
                    addPaginationRowFlex(customerTypeHeader);
                customerTypeRow.appendChild(customerTypeHeader);

                    const newCustomerType = cTag('div', {class: "columnXS12 columnMD5"});
                        let newCustomerTitle = cTag('h4', {class: "borderbottom", 'style': "font-size: 18px;", id: "formtitle"});
                        newCustomerTitle.innerHTML = Translate('Add New Customer Type');
                    newCustomerType.appendChild(newCustomerTitle);
                        const newCustomerTypeForm = cTag('form', {'action': "#", name: "frmcustomer_type", id: "frmcustomer_type", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                        newCustomerTypeForm.addEventListener('submit',(event)=>AJsave_ManageData(event,'name',AJsave_customer_type));
                            const newCustomerRow = cTag('div', {class: "flexSpaBetRow", 'style': "margin-top: 15px;"});
                                const newCustomerLabel = cTag('label', {'for': "name"});
                                newCustomerLabel.innerHTML = Translate('Customer Type Name');
                                    let requiredField = cTag('span', {class: "required"});
                                    requiredField.innerHTML = '*';
                                newCustomerLabel.appendChild(requiredField);
                            newCustomerRow.appendChild(newCustomerLabel);
                                let inputField = cTag('input', {'type': "text", 'required': "", class: "form-control", 'style': "margin-bottom: 10px;", name: "name", id: "name", 'value': "", 'size': 20, 'maxlength': 20});
                            newCustomerRow.appendChild(inputField);
                        newCustomerTypeForm.appendChild(newCustomerRow);
                        newCustomerTypeForm.appendChild(controller_bar('customer_type_id',resetForm_customer_type));
                    newCustomerType.appendChild(newCustomerTypeForm);
                customerTypeRow.appendChild(newCustomerType);
            callOutDiv.appendChild(customerTypeRow);
        customerTypeContainer.appendChild(callOutDiv);
    parentRow.appendChild(customerTypeContainer);
    showTableData.appendChild(parentRow);    

    addCustomeEventListener('filter',filter_Manage_Data_customer_type);
    addCustomeEventListener('loadTable',loadTableRows_Manage_Data_customer_type);
    addCustomeEventListener('reset',resetForm_customer_type);
    getSessionData();
    filter_Manage_Data_customer_type(true);
}

async function filter_Manage_Data_customer_type(){
    let page = 1;
	document.querySelector("#page").value = page;
	
	const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	
    
    const url = '/'+segment1+'/AJgetPage_customer_type/filter';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Customer Type Name'), 'align':'left'}], 'customer_type',filter_Manage_Data_customer_type,resetForm_customer_type);
        document.querySelector("#totalTableRows").value = data.totalRows;			
        onClickPagination();
    }
}

async function loadTableRows_Manage_Data_customer_type(){
    const jsonData = {};
    jsonData['sdata_type'] = document.querySelector('#sdata_type').value;	
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;			
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;
	
    const url = '/'+segment1+'/AJgetPage_customer_type';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createListRow(data.tableRows, [{'datatitle':Translate('Customer Type Name'), 'align':'left'}], 'customer_type',filter_Manage_Data_customer_type,resetForm_customer_type);
        onClickPagination();
    }
}

async function AJsave_customer_type(event=false){
    if(event){event.preventDefault();}
    let submit =  document.querySelector("#submit");

    const jsonData = serialize("#frmcustomer_type");
    const url = '/'+segment1+'/AJsave_customer_type';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && (data.savemsg==='Add' || data.savemsg==='Update')){
			resetForm_customer_type();
			if(data.returnStr==='Add'){
                showTopMessage('success_msg',Translate('Added successfully.'));
			}
			else{
                showTopMessage('success_msg',Translate('Updated successfully.'));
			}
			filter_Manage_Data_customer_type();
		}
		else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occured while adding new customer type! Please try again.'), Translate('Ok'));
		}  
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('This name already exists! Please try again with a different name.'), Translate('Ok'));
		}  
		else{
			alert_dialog(Translate('Alert message'), Translate('No changes / Error occurred while updating data! Please try again.'), Translate('Ok'));
		}         
        submit.value = Translate('Add')
        submit.disabled = false; 
    }
	return false;
}

async function resetForm_customer_type(){
	document.querySelector("#formtitle").innerHTML = Translate('Add New Customer Type');
	document.querySelector("#customer_type_id").value = 0;
	document.querySelector("#name").value = '';
    //hide all btn other than save
    document.querySelector("#submit").style.display = '';
    document.querySelector("#archive").style.display = 'none';
    document.querySelector("#unarchive").style.display = 'none';
    document.querySelector("#merge").style.display = 'none';
    document.querySelector("#reset").style.display = 'none';
}

//_______________________Manage EU GDPR-Part____________________________________
async function eu_gdpr(){
    let pTag, inputField, borderBottom;
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    Dashboard.appendChild(listHeader(Translate('Manage EU GDPR')));
        const manageGdprContainer = cTag('div', {class: "flexSpaBetRow"});
        manageGdprContainer.appendChild(leftsideMenu());

            let callOutDivStyle = "margin-top: 0; background: #fff;"
            if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
            let manageGdprRow = cTag('div', {class: "columnMD10 columnSM9", 'style': "margin: 0;"});
                let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
                    const euGdprForm = cTag('form', {name: "frmeu_gdpr", id: "frmeu_gdpr", 'action': "#", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                    euGdprForm.addEventListener('submit',AJsave_eu_gdpr);
                        const guidelinesRow = cTag('div', {class: "flex"});
                            let guidelinesText = cTag('div', {class: "columnSM12", 'style': "padding-top: 15px;"});
                                pTag = cTag('p');
                                pTag.innerHTML = Translate('To meet the GDPR guidelines you will need to have personal data removed after a certain amount of time that you determine.  Please enter how many months you want to keep your customers personal data since the last interaction before it gets removed.');
                            guidelinesText.appendChild(pTag);
                        guidelinesRow.appendChild(guidelinesText);
                    euGdprForm.appendChild(guidelinesRow);
                        const euGdprMonth = cTag('div', {class: "flexSpaBetRow "});
                            let euGdprMonthColumn = cTag('div', {class: "columnSM2 columnMD1"});
                                const euGdprLabel = cTag('label', {'for': "eu_gdprMonth"});
                                euGdprLabel.innerHTML = Translate('Months')+':';
                            euGdprMonthColumn.appendChild(euGdprLabel);
                        euGdprMonth.appendChild(euGdprMonthColumn);
                            let euGdprMonthField = cTag('div', {class: "columnSM4 columnMD3"});
                                inputField = cTag('input', {'type': "number", id: "eu_gdprMonth", name: "eu_gdprMonth", class: "form-control", 'required': "", 'min': 1, 'max': 99});
                            euGdprMonthField.appendChild(inputField);
                        euGdprMonth.appendChild(euGdprMonthField);
                            let removeColumn = cTag('div', {class: "columnSM6 columnMD8"});
                                pTag = cTag('p');
                                pTag.innerHTML = Translate('(enter 0 to never remove)');
                            removeColumn.appendChild(pTag);
                        euGdprMonth.appendChild(removeColumn);
                    euGdprForm.appendChild(euGdprMonth);
                        const addButtonRow = cTag('div', {class: "flexStartRow"});
                            let emptyColumn = cTag('div', {class: "columnSM2 columnMD1"});
                            emptyColumn.innerHTML = ' ';
                        addButtonRow.appendChild(emptyColumn);
                            let addButton = cTag('div', {class: "columnSM7 columnMD10"});
                                inputField = cTag('input', {'type': "hidden", name: "variables_id", id: "variables_id", 'value': 0});
                            addButton.appendChild(inputField);                                
                        addButtonRow.appendChild(addButton);
                    euGdprForm.appendChild(addButtonRow);
                callOutDiv.appendChild(euGdprForm);
                    borderBottom = cTag('div', {class: "borderbottom", 'style': "margin-top: 15px;"});
                callOutDiv.appendChild(borderBottom);

                    const removeDataForm = cTag('form', {name: "frmremovePD", id: "frmremovePD", 'action': "#", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                    removeDataForm.addEventListener('submit', removePerData);
                        let removeDataRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding-top: 15px;"});
                            let removeDataColumn = cTag('div', {class: "columnSM5 columnMD3"});
                                const removeDataLabel = cTag('label', {'for': "customer_name"});
                                removeDataLabel.innerHTML = Translate('Remove all personal data for');
                            removeDataColumn.appendChild(removeDataLabel);
                        removeDataRow.appendChild(removeDataColumn);
                            let removeDataField = cTag('div', {class: "columnSM4 columnMD3", 'style': "padding-right: 0;"});
                                inputField = cTag('input', {'type': "text", id: "customer_name", name: "customer_name", class: "form-control", 'placeholder': Translate('Customer Name'), 'required': "", 'minlength': 2, 'maxlength': 20});
                                inputField.addEventListener('keyup', function (){document.frmremovePD.customers_id.value=0;});
                            removeDataField.appendChild(inputField);
                                inputField = cTag('input', {'type': "hidden", id: "customers_id", name: "customers_id", 'required': "", 'value': 0});
                            removeDataField.appendChild(inputField);
                        removeDataRow.appendChild(removeDataField);
                            let removeButtonColumn = cTag('div', {class: "columnSM3 columnMD6"});
                                let removeButton = cTag('button', {'type': "submit", class: "btn archiveButton", id: "btnRemovePD"});
                                removeButton.innerHTML = Translate('Remove');
                            removeButtonColumn.appendChild(removeButton);
                        removeDataRow.appendChild(removeButtonColumn);
                    removeDataForm.appendChild(removeDataRow);
                callOutDiv.appendChild(removeDataForm);
                    borderBottom = cTag('div', {class: "borderbottom", 'style': "margin-top: 15px;"});
                callOutDiv.appendChild(borderBottom);

                    const exportDataForm = cTag('form', {name: "frmexportPD", id: "frmexportPD", 'action': "/Manage_Data/exportPerData", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                    exportDataForm.addEventListener('submit', function(event){
                        event.preventDefault();
                        confirm_dialog(Translate('Export Personal Data'), Translate('Are you sure you want to export this Personal Data?'), exportPerData)
                    });
                        let exportDataRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding-top: 15px;"});
                            let exportDataColumn = cTag('div', {class: "columnSM5 columnMD3"});
                                const exportLabel = cTag('label', {'for': "customer_name"});
                                exportLabel.innerHTML = Translate('To export all the data for:');
                            exportDataColumn.appendChild(exportLabel);
                        exportDataRow.appendChild(exportDataColumn);
                            let exportCustomerField = cTag('div', {class: "columnSM4 columnMD3", 'style': "padding-right: 0;"});
                                inputField = cTag('input', {'type': "text", id: "customer", name: "customer", class: "form-control", 'placeholder': Translate('Customer Name'), 'required': "", 'minlength': 2, 'maxlength': 20});                                
                            exportCustomerField.appendChild(inputField);
                                inputField = cTag('input', {'type': "hidden", id: "customer_id", name: "customer_id", 'required': "", 'value': 0});
                            exportCustomerField.appendChild(inputField);
                        exportDataRow.appendChild(exportCustomerField);
                            let exportButton = cTag('div', {class: "columnSM3 columnMD6"});
                                let inputButton = cTag('input', {'type': "submit", class: "btn completeButton", 'style': "width: 80px;", id: "btnExportPD", 'value': Translate('Export')});
                            exportButton.appendChild(inputButton);
                        exportDataRow.appendChild(exportButton);
                    exportDataForm.appendChild(exportDataRow);
                callOutDiv.appendChild(exportDataForm);
                    borderBottom = cTag('div', {class: "borderbottom", 'style': "margin-top: 15px;"});
                callOutDiv.appendChild(borderBottom);

                    const marketingDataForm = cTag('form', {name: "frmMarketingData", id: "frmMarketingData", 'action': "#", 'enctype':"multipart/form-data", 'method':"post", 'accept-charset': "utf-8"});
                    marketingDataForm.addEventListener('submit',AJsaveMarketingData);
                        let marketingDataRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding-top: 15px;"});
                            let marketingDataColumn = cTag('div', {class: "columnSM4 columnMD3"});
                                const marketingDataLabel = cTag('label', {'for': "marketing_data"});
                                marketingDataLabel.innerHTML = Translate('Marketing Data:');
                            marketingDataColumn.appendChild(marketingDataLabel);
                        marketingDataRow.appendChild(marketingDataColumn);
                            let marketingDataText = cTag('div', {class: "columnSM8 columnMD9"});
                            marketingDataText.innerHTML = Translate('When obtaining information from your customer you now need to allow them to accept marketing to them. We have always had a checkbox in the customer information for Offers email that tell you if you can email a customer marketing offers. In addition here we are allowing you to add text to notify a customer on their INVOICE that they have agreed to except marketing from you. If the offers email is checked then the text you enter below will printed at the bottom of that customers invoice.');
                        marketingDataRow.appendChild(marketingDataText);
                    marketingDataForm.appendChild(marketingDataRow);
                        const marketingDataField = cTag('div', {class: "flexSpaBetRow"});
                            let emptyColumn1 = cTag('div', {class: "columnSM4 columnMD3"});
                            emptyColumn1.innerHTML = ' ';
                        marketingDataField.appendChild(emptyColumn1);
                            let marketingDataDiv = cTag('div', {class: "columnSM8 columnMD9"});
                                let textarea = cTag('textarea', {'rows': 5, 'cols': 40, id: "marketing_data", name: "marketing_data", class: "form-control", 'required': ""});
                            marketingDataDiv.appendChild(textarea);
                                inputField = cTag('input', {'type': "hidden", name: "variables_id", 'required': "", 'value': 0});
                            marketingDataDiv.appendChild(inputField);
                        marketingDataField.appendChild(marketingDataDiv);
                    marketingDataForm.appendChild(marketingDataField);
                        const saveButtonRow = cTag('div', {class: "flexSpaBetRow"});
                            let saveButtonColumn = cTag('div', {class: "columnSM12", 'align': "center"});
                                inputField = cTag('input', {'type': "submit", id: "submit", class: "btn saveButton", 'style': "width: 80px;"});
                            saveButtonColumn.appendChild(inputField);
                        saveButtonRow.appendChild(saveButtonColumn);
                    marketingDataForm.appendChild(saveButtonRow);
                callOutDiv.appendChild(marketingDataForm);
            manageGdprRow.appendChild(callOutDiv);
        manageGdprContainer.appendChild(manageGdprRow);
    Dashboard.appendChild(manageGdprContainer);
    AJ_eu_gdpr_MoreInfo();
}

async function AJ_eu_gdpr_MoreInfo(){
    const url = '/'+segment1+'/AJ_eu_gdpr_MoreInfo';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        document.querySelector('#eu_gdprMonth').value = data.eu_gdprMonth ;
        if(data.readonly){
            document.querySelector('#eu_gdprMonth').setAttribute('readonly','');
        }
        document.querySelector('#marketing_data').value = data.marketing_data;
        if(data.variables_id2>0)  document.querySelector('#submit').value = Translate('Update');
        else document.querySelector('#submit').value = Translate('Save');
        document.querySelectorAll('[name=variables_id]')[0].value = data.variables_id;
        document.querySelectorAll('[name=variables_id]')[1].value = data.variables_id2;
        let input;
        if(data.variables_id>0){
            input = cTag('input', {class: "btn archiveButton", name: "removeBtn", id: "removeBtn", 'type': "button", 'value': Translate('Remove EU GDPR Intergration')});
            input.addEventListener('click', function (){
                if(data.canGDPRAddRemove==0){
                    alert_dialog(Translate('Sorry! could not access')+' EU GDPR', Translate('Please setup this feature from your MAIN account. It is not available here.'), Translate('Ok'));
                }
                else{
                    removeVariables('EU GDPR');
                }
            });
                
            document.querySelector('#variables_id').parentNode.appendChild(input); 
        }else{
            if(data.canGDPRAddRemove==0){
                input = cTag('input', {class: "btn completeButton", name: "btnSubmit", id: "btnSubmit",'type': "button", 'value': Translate('Add')});
                input.addEventListener('click', function (){
                    alert_dialog(Translate('Sorry! could not access')+' EU GDPR', Translate('Please setup this feature from your MAIN account. It is not available here.'), Translate('Ok'));
                });
            }
            else{
                input = cTag('input', {class: "btn completeButton", name: "btnSubmit", id: "btnSubmit",'type': "submit", 'value': Translate('Add')});
            }
                
            document.querySelector('#variables_id').parentNode.appendChild(input); 
        }

        if (document.querySelector('#frmremovePD') || document.getElementById("customerNameField")){
            if(document.getElementById("customer_name")){AJautoComplete('customer_name');}
        }
        if (document.querySelector('#frmexportPD')){
            AJautoComplete('customer');
            document.getElementById("customer").addEventListener('keyup', e => {
                if (document.querySelector('#customer_id')){
                    document.getElementById("customer_id").value = 0;
                }
            });
        }
        if (document.querySelector('#customer_name')){
            document.getElementById("customer_name").addEventListener('keyup', e => {
                if (document.querySelector('#customers_id')){
                    document.getElementById("customers_id").value = 0;
                }
            });
        }
    }
}

async function AJsave_eu_gdpr(event=false){
    if(event){event.preventDefault();}
    let submit = document.querySelector("#btnSubmit");
    submit.value = Translate('Saving')+'...';
    submit.disabled = true;
    
    const jsonData = serialize("#frmeu_gdpr");
    const url = '/'+segment1+'/AJsave_eu_gdpr';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.variables_id !==''){
            if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
			location.reload();
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while saving EU GDPR information! Please try again.'));
		}
        submit.value = Translate('Add');
        submit.disabled = false;  
    }
	return false;
}

async function removePerData(event=false){
    if(event){event.preventDefault();}
	confirm_dialog(Translate('Remove Personal Data'), Translate('Are you sure you want to remove this Personal Data?'), confirmRemovePerData);
	return false;
}

async function confirmRemovePerData(hidePopup){
	let customers_id = document.frmremovePD.customers_id.value;
	if(customers_id===0){
		document.frmremovePD.customer_name.value = '';
		document.frmremovePD.customer_name.focus();
        return false;
	}
	let rmBtn = document.querySelector("#btnRemovePD")
    rmBtn.innerHTML = Translate('Removing')+'...';
    rmBtn.disabled = true;    
    
    const jsonData = serialize("#frmremovePD");
    const url = '/'+segment1+'/removePerData';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
            showTopMessage('success_msg',Translate('Personal data removed successfully.'));
			document.frmremovePD.customer_name.value = '';
			document.frmremovePD.customers_id.value = 0;
			document.frmremovePD.customer_name.focus();
            rmBtn.innerHTML = Translate('Remove');
            rmBtn.disabled = false; 
		}
		else{
            showTopMessage('alert_msg',Translate('You could not remove personal information without customer chosen.'));
		}
        hidePopup();
    }
	return false;
}

function exportPerData(hidePopup){
    hidePopup();
    document.frmexportPD.submit();
	return false;
}

async function AJsaveMarketingData(event=false){
    if(event){event.preventDefault();}
    let submit = document.querySelector("#submit");
    submit.value = Translate('Saving')+'...';
    submit.disabled = true;    

    const jsonData = serialize("#frmMarketingData");
    const url = '/'+segment1+'/AJsaveMarketingData';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
            document.getElementById("variables_id").value = data.id;
            if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
		}
		else{
            showTopMessage('alert_msg',Translate('Error occured while changing marketing data information! Please try again.'));
		}
        submit.value = Translate('Update');
        submit.disabled = false;   
    }
	return false;
}

//=======common=======//
function AJautoCompleteTableData(fieldId,tableId){   
	const node = document.querySelector("#"+fieldId);
	if(node){
		customAutoComplete(node,{
			minLength: 2,
			source: async function (request, response) {
				const jsonData = {"keyword_search":request};
				const url = `/${segment1}/AJgetPage_${segment2}/filter`;

                await fetchData(afterFetch,url,jsonData,'JSON',0);

                function afterFetch(data){
					if(data.login !== ''){window.location = '/'+data.login;}
					else{
                        let responseData = [];
                        data.tableRows.forEach(item=>{
                            if(parseInt(item[0])===tableId) return;
                            if(segment2=='brand_model') responseData.push({ id:item[0], brand:item[1], model:item[2], label:`${item[1]} ${item[2]}` });
                            else responseData.push({ id:item[0], name:item[1], label:item[1] });
                        })
						response(responseData);
					}
				} 
			},
			select: function( event, info ) {
				// node.value = info.label;				
				if(document.querySelector("#toTableDataInfo")){
                    document.querySelectorAll('.popup_footer_button')[1].style.display = '';//show Merge
                    node.value = '';
                    let pTag,nameSpan;
					const toTableDataInfo = document.getElementById('toTableDataInfo');
                    toTableDataInfo.innerHTML = '';
                    if(segment2=='brand_model'){                                
                            pTag = cTag('p');
                            pTag.innerHTML = Translate('Brand');
                                nameSpan = cTag('span');
                                nameSpan.innerHTML = info.brand;
                            pTag.append(': ', nameSpan);
                        toTableDataInfo.appendChild(pTag);
                            pTag = cTag('p');
                            pTag.innerHTML = Translate('Model');
                                nameSpan = cTag('span');
                                nameSpan.innerHTML = info.model;
                            pTag.append(': ', nameSpan);
                        toTableDataInfo.appendChild(pTag);
                    }
                    else{
                            pTag = cTag('p');
                            pTag.innerHTML = Translate('Name');
                                nameSpan = cTag('span');
                                nameSpan.innerHTML = info.name;
                            pTag.append(': ', nameSpan);
                        toTableDataInfo.appendChild(pTag);
                    }                   
                    document.getElementById("toTableData_id").value = info.id;
				}				
				return false;
			}
		});
	}
}
async function mergeDataPopup(){
    let tableName = segment2;
    let tableId = document.getElementById(segment2+'_id').value;
    const labels = {
        category:Translate('Category'),
        manufacturer:Translate('Manufacturer'),
        repair_problems:Translate('Repair Problem'),
        brand_model:Translate('Brand Model'),
        vendors:Translate('Vendors'),
        expense_type:Translate('Expense Type'),
        customer_type:Translate('Customer Type'),
    }
    const label = labels[segment2];
    const jsonData = {tableName,tableId};
    
	if(tableId>0){	
        const url = '/Common/getOneRowInfo/';

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            const formDialog = cTag('div');
                const errorMsg = cTag('div', {id: "error_tableData", class: "errormsg"});
            formDialog.appendChild(errorMsg);
                let pTag, inputField,nameSpan;
                const mergeCustomerForm = cTag('form', {'action': "#", name: "frmMergeTableData", id: "frmMergeTableData", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
                    const mergeThisText = cTag('div', {class: "flexSpaBetRow"});
                        const mergeThisTextColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                            const mergeThisTextTitle = cTag('h4', {class:'borderbottom'});
                            mergeThisTextTitle.innerHTML = Translate('Merge this')+' '+label+' '+Translate('Information');
                        mergeThisTextColumn.appendChild(mergeThisTextTitle);
                    mergeThisText.appendChild(mergeThisTextColumn);
                mergeCustomerForm.appendChild(mergeThisText);

                    const mergeCustomerRow = cTag('div', {class: "flexSpaBetRow"});
                        const mergeCustomerColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;"});
                        if(segment2=='brand_model'){                                
                                pTag = cTag('p');
                                pTag.innerHTML = Translate('Brand');
                                    nameSpan = cTag('span');
                                    nameSpan.innerHTML = data.brand;
                                pTag.append(': ', nameSpan);
                            mergeCustomerColumn.appendChild(pTag);
                                pTag = cTag('p');
                                pTag.innerHTML = Translate('Model');
                                    nameSpan = cTag('span');
                                    nameSpan.innerHTML = data.model;
                                pTag.append(': ', nameSpan);
                            mergeCustomerColumn.appendChild(pTag);
                        }
                        else{
                                pTag = cTag('p');
                                pTag.innerHTML = Translate('Name');
                                    nameSpan = cTag('span');
                                    nameSpan.innerHTML = data.name||data.category_name;
                                pTag.append(': ', nameSpan);
                            mergeCustomerColumn.appendChild(pTag);
                        }
                    mergeCustomerRow.appendChild(mergeCustomerColumn);
                mergeCustomerForm.appendChild(mergeCustomerRow);

                    const toThisRow = cTag('div', {class: "flexSpaBetRow"});
                        const toThisColumn = cTag('div', {class: "columnSM12", 'align': "left"});
                            const toThisTitle = cTag('h4', {class:'borderbottom'});
                            toThisTitle.innerHTML = Translate('To this')+' '+label;
                        toThisColumn.appendChild(toThisTitle);
                    toThisRow.appendChild(toThisColumn);
                mergeCustomerForm.appendChild(toThisRow);

                    const customerNameRow = cTag('div', {class: "flexSpaBetRow"});
                        const customerNameColumn = cTag('div', {class: "columnSM3", 'align': "left"});
                            const nameLabel = cTag('label', {'for': "tableData"});
                            nameLabel.innerHTML = label;
                                let requiredField = cTag('span', {class: "required"});
                                requiredField.innerHTML = '*';
                            nameLabel.appendChild(requiredField);
                        customerNameColumn.appendChild(nameLabel);
                    customerNameRow.appendChild(customerNameColumn);

                        const nameSearchColumn = cTag('div', {class: "columnSM9"});
                            inputField = cTag('input', {"maxlength": 50, 'type': "text", 'value': "", 'required': true, name: "tableData", id: "tableData", class: "form-control", 'placeholder': Translate('Search')+' '+label});
                        nameSearchColumn.appendChild(inputField);
                    customerNameRow.appendChild(nameSearchColumn);
                mergeCustomerForm.appendChild(customerNameRow);

                    const customerInfoRow = cTag('div', {class: "flexSpaBetRow"});
                        const customerInfoColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;", id: "toTableDataInfo"});
                    customerInfoRow.appendChild(customerInfoColumn);
                mergeCustomerForm.appendChild(customerInfoRow);

                mergeCustomerForm.appendChild(cTag('input', {'type': "hidden", name: "tableName", id: "tableName", 'value': tableName}));
                mergeCustomerForm.appendChild(cTag('input', {'type': "hidden", name: "fromTableData_id", id: "fromTableData_id", 'value': tableId}));
                mergeCustomerForm.appendChild(cTag('input', {'type': "hidden", name: "toTableData_id", id: "toTableData_id", 'value': 0}));
            formDialog.appendChild(mergeCustomerForm);

            popup_dialog600(Translate('Merge')+' '+label, formDialog,Translate('Merge'), mergeData);
            document.querySelectorAll('.popup_footer_button')[1].style.display = 'none';//hide Merge initially
            setTimeout(function() {		
                document.getElementById("tableData").focus();
                if(document.getElementById("tableData")){AJautoCompleteTableData('tableData',tableId);}
            }, 500);
        }
        return true;
    }
}
async function mergeData(hidePopup){
	const error_tableData = document.getElementById('error_tableData');
    error_tableData.innerHTML = '';	
    actionBtnClick('.btnmodel', Translate('Merging'), 1);
	
    const jsonData = serialize('#frmMergeTableData');    
    const url = '/Common/AJmergeTableData';
    
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.savemsg ==='Success' && data.id>0){
            hidePopup();
            // document.getElementById('reset').click();
            triggerEvent('filter');
            triggerEvent('reset');
            showTopMessage('success_msg', Translate('Successfully Merged'));
		}
		else{
            actionBtnClick('.btnmodel', Translate('Merge'), 0);
            showTopMessage('alert_msg', Translate('There is an error while merging information.'));             
		}
	}
	return false;
}
function unarchiveManageData(){
    let tableName = segment2;
    let tableId = document.getElementById(segment2+'_id').value;
    confirm_dialog(Translate('Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{        
        unarchiveData(null,{tablename:tableName, tableidvalue:tableId, publishname:segment2+'_publish'},afterUnarchive);
        function afterUnarchive(){
            hidePopup();
            document.getElementById('sdata_type').value = 'All';
            triggerEvent('filter');
            triggerEvent('reset');
        }
    })
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = { export_,archive_Data, lsnipplesizescore, lsbcscore, lsclassification, lssection, lsbreed, lslocation, lsgroups, suppliers,sview,category,manufacturer,repair_problems,brand_model,vendors,expense_type,customer_type,eu_gdpr };
    layoutFunctions[segment2]();

    leftsideHide("secondarySideMenu",'secondaryNavMenu');

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    
    applySanitizer(document);
});