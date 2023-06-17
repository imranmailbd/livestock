import {
    cTag, Translate, tooltip, storeSessionData, addCurrency, DBDateToViewDate, setOptions, checkAndSetSessionData, DBDateRangeToViewDate,
    getMobileOperatingSystem, date_picker_dialog, fetchData, daterange_picker_dialog, AJautoComplete, changeToDBdate_OnSubmit
} from './common.js';

if(segment2==='') segment2 = 'lists';

function lists(){
    let todayDate, inputField, dateRangeField, generateReportColumn, emptyColumn;
    let now = new Date();
    let date = now.getDate();
    let month = now.getMonth() + 1;
    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){todayDate = (date<10 ? '0'+date : date) +'-'+(month<10 ? '0'+month : month)+'-'+now.getFullYear();}
    else{todayDate = (month<10 ? '0'+month : month)+'/'+ (date<10 ? '0'+date : date ) +'/'+now.getFullYear();}

    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div');
            const headerTitle = cTag('h2', { 'style': "padding: 5px; text-align: start;"});
            headerTitle.append(Translate('Inventory Reports')+' ');
                const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
    viewPageInfo.appendChild(titleRow);

        let inventoryReportColumn = cTag('div',{class:"columnSM12"});
            inputField = cTag('input',{type:"hidden", id:"twoSegments", value:"Inventory_reports-lists"});
            let inputField2 = cTag('input',{type:"hidden", id:"pageURI", value:`${segment1}/${segment2}`});
        inventoryReportColumn.append(inputField, inputField2);
            let callOut = cTag('div',{ class:"innerContainer", style:"background:#FFF; padding:0"});
                const inventoryReportDiv = cTag('div',{class: "columnSM12"});
                    const inventoryReportHeader = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "background-color: #EEEEEE;"});
                        const reportTypeColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                            const reportTypeTitle = cTag('strong');
                            reportTypeTitle.innerHTML = Translate('Report Type');
                        reportTypeColumn.appendChild(reportTypeTitle);
                    inventoryReportHeader.appendChild(reportTypeColumn);
                        const fromToDateColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: center;"});
                            const formToDateTitle = cTag('strong');
                            formToDateTitle.innerHTML = Translate('From Date - To Date');
                        fromToDateColumn.appendChild(formToDateTitle);
                    inventoryReportHeader.appendChild(fromToDateColumn);
                        const optionalColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: center;"});
                            const optionalTitle = cTag('strong');
                            optionalTitle.innerHTML = Translate('Optional Keyword');
                        optionalColumn.appendChild(optionalTitle);
                    inventoryReportHeader.appendChild(optionalColumn);
                        const getReportColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                            const getReportTitle = cTag('strong');
                            getReportTitle.innerHTML = Translate('Get Report');
                        getReportColumn.appendChild(getReportTitle);
                    inventoryReportHeader.appendChild(getReportColumn);
                inventoryReportDiv.appendChild(inventoryReportHeader);

                    const inventoryValueForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('Inventory_Date'),enctype:"text/plain", method:"get", name:"frminventory_Value", id:"frminventory_Value", action:"/Inventory_reports/inventory_Value"});
                        const inventoryValueRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                            const inventoryValueColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                                const inventoryLabel = cTag('label',{for:"inventory_Value"});
                                inventoryLabel.append(Translate('Inventory Value'));
                            inventoryValueColumn.appendChild(inventoryLabel);
                            dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                                const hasDatepicker = cTag('input',{minlength:"10", maxlength:"10", type:"text", class:"form-control hasDatepicker", 'style': "padding-left: 35px;", name:"Inventory_Date", id:"Inventory_Date", value: todayDate});
                                date_picker_dialog(hasDatepicker,({date, month, year}, close)=>{
                                    close();
                                    let dateVal;
                                    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                                        dateVal = date+'-'+month+'-'+year;    
                                    }
                                    else{
                                        dateVal = month+'/'+date+'/'+year;    
                                    }
                                    hasDatepicker.value = dateVal;
                                });
                            dateRangeField.appendChild(hasDatepicker);
                            emptyColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                            emptyColumn.append(' ');
                            generateReportColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                                inputField = cTag('input',{type:"submit", value:Translate('Generate Report'), class:"btn defaultButton", title:Translate('Generate Report')});                                
                            generateReportColumn.appendChild(inputField);
                        inventoryValueRow.append(inventoryValueColumn,dateRangeField,emptyColumn,generateReportColumn);
                    inventoryValueForm.appendChild(inventoryValueRow);
                inventoryReportDiv.appendChild(inventoryValueForm);

                    const inventoryPurchasedForm = cTag('form',{submit:()=>changeToDBdate_OnSubmit('inventory_Purchased',true),enctype:"text/plain", method:"get", name:"frminventory_Purchased", action:"/Inventory_reports/inventory_Purchased"});
                        const inventoryPurchasedRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                            const inventoryPurchasedColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                                const inventoryPurchasedLabel = cTag('label',{for:"inventory_Value"});
                                inventoryPurchasedLabel.append(Translate('Inventory Purchased'));
                            inventoryPurchasedColumn.appendChild(inventoryPurchasedLabel);
                            dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                                inputField = cTag('input',{minlength: 23, maxlength: 23, type:"text", class:"form-control sales_date", 'style': "padding-left: 35px;", name:"po_datetime", id:"inventory_Purchased", value: todayDate+' - '+todayDate});
                                daterange_picker_dialog(inputField);
                            dateRangeField.appendChild(inputField);
                            const searchColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                                inputField = cTag('input',{maxlength: 50, type:"text", class:"form-control ui-autocomplete-input", name:"supplier", id:"supplier", value:"", placeholder: Translate('Search Suppliers'), autocomplete:"off"});
                            searchColumn.appendChild(inputField);
                            generateReportColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                                inputField = cTag('input',{type:"submit", value:Translate('Generate Report'), class:"btn defaultButton", title: Translate('Generate Report')});
                            generateReportColumn.appendChild(inputField);
                        inventoryPurchasedRow.append(inventoryPurchasedColumn,dateRangeField,searchColumn,generateReportColumn);
                    inventoryPurchasedForm.appendChild(inventoryPurchasedRow);
                inventoryReportDiv.appendChild(inventoryPurchasedForm);

                    const productReportForm = cTag('form',{enctype:"text/plain", method:"get", name:"frmproducts_Report", action:"/Inventory_reports/products_Report"});
                        const productReportRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"}); 
                            const productReportColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                                const productReportLabel = cTag('label',{ for:"inventory_Value"});
                                productReportLabel.append(Translate('Products Report'));
                            productReportColumn.appendChild(productReportLabel);
                            emptyColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                            emptyColumn.append(' ');
                            let emptyColumn2 = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                            emptyColumn2.append(' ');
                            generateReportColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                                inputField = cTag('input',{type:"submit", value:Translate('Generate Report'), class:"btn defaultButton", title: Translate('Generate Report') });
                            generateReportColumn.appendChild(inputField);
                        productReportRow.append(productReportColumn,emptyColumn,emptyColumn2,generateReportColumn);
                    productReportForm.appendChild(productReportRow);
                inventoryReportDiv.appendChild(productReportForm);

                    const purchaseOrderForm = cTag('form',{submit:()=>changeToDBdate_OnSubmit('po_datetime1',true),enctype:"text/plain", method:"get", name:"frmpurchase_Orders", action:"/Inventory_reports/purchase_Orders"});
                        const purchaseOrderRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                            const purchaseOrderColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                                const purchaseOrderLabel = cTag('label',{for:"inventory_Value"});
                                purchaseOrderLabel.append(Translate('Purchase Orders'));
                            purchaseOrderColumn.appendChild(purchaseOrderLabel);
                        purchaseOrderRow.appendChild(purchaseOrderColumn);
                            dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                                inputField = cTag('input',{minlength: 23, maxlength: 23, type:"text", class:"form-control sales_date", 'style': "padding-left: 35px;", name:"po_datetime", id:"po_datetime1", value: todayDate+' - '+todayDate});
                                daterange_picker_dialog(inputField);
                            dateRangeField.appendChild(inputField);
                        purchaseOrderRow.appendChild(dateRangeField);
                            const searchDiv = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                                inputField = cTag('input',{maxlength: 50, type:"text", class:"form-control search ui-autocomplete-input", name:"posupplier", id:"posupplier", value:"", autocomplete:"off"});
                            searchDiv.appendChild(inputField);
                        purchaseOrderRow.appendChild(searchDiv);
                            generateReportColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                                inputField = cTag('input',{type:"submit", value:Translate('Generate Report'), class:"btn defaultButton", title: Translate('Generate Report')});
                            generateReportColumn.appendChild(inputField);
                        purchaseOrderRow.append(generateReportColumn);
                    purchaseOrderForm.appendChild(purchaseOrderRow);
                inventoryReportDiv.appendChild(purchaseOrderForm);
            callOut.appendChild(inventoryReportDiv);
        inventoryReportColumn.append(callOut);
    viewPageInfo.appendChild(inventoryReportColumn);
    if(document.querySelector("#posupplier")){
        AJautoComplete('posupplier');
    
        document.getElementById("posupplier").addEventListener('keyup', e => {
            if(document.getElementById("suppliers_id")){
                document.getElementById("suppliers_id").value = 0;
            }
        });
    }
    if(document.querySelector("#supplier")){AJautoComplete('supplier');}
    storeSessionData({}, 0);    
}

async function AJ_inventory_Value_MoreInfo(){
    let Inventory_Date = document.getElementById("Inventory_Date").value;
	const jsonData = {};
	jsonData['Inventory_Date'] = Inventory_Date;
		
    const url = '/'+segment1+'/AJ_inventory_Value_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
            
        const Searchresult = document.querySelector('#Searchresult');
        Searchresult.innerHTML = '';
            let inventoryValueHeadRow, thCol;
            const noMoreTables = cTag('div', {id:"no-more-tables"});
                const inventoryValueTable = cTag('table', {class:"bgnone table-bordered table-striped table-condensed cf listing"});
                    const inventoryValueHead = cTag('thead', {class:"cf"}); 
                    const titleColTitles = [Translate('Product Name'), Translate('QTY'), Translate('Ave Cost'), Translate('Total'), Translate('QTY'), Translate('Total'), Translate('QTY'), Translate('Ave Cost'), Translate('Total')];
                        inventoryValueHeadRow = cTag('tr');
                            thCol = cTag('th', {'style': "text-align: center;", rowspan:"2"});
                            thCol.append(Translate('Product Name'));
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {'style': "text-align: center;", rowspan:"2", width:"10%"});
                            thCol.append(Translate('Product Type'));
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: center;", colspan:"3", width:"20%"});
                            thCol.append(Translate('Current Value'));
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: center;", colspan:"2", width:"15%"});
                            thCol.append(Translate('Amount Changed Since')+' '+Inventory_Date);
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: center;", colspan:"3", width:"20%"});
                            thCol.append(Translate('Inventory Value on')+' '+Inventory_Date);
                        inventoryValueHeadRow.appendChild(thCol);
                    inventoryValueHead.appendChild(inventoryValueHeadRow);
                        inventoryValueHeadRow = cTag('tr');
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"5%"});
                            thCol.append(Translate('QTY'));
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"7%"});
                            thCol.append(Translate('Ave Cost'));
                        inventoryValueHeadRow.appendChild(thCol); 
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;"});
                            thCol.append(Translate('Total'));
                        inventoryValueHeadRow.appendChild(thCol);  
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"5%"});
                            thCol.append(Translate('QTY'));
                        inventoryValueHeadRow.appendChild(thCol); 
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;"});
                            thCol.append(Translate('Total'));
                        inventoryValueHeadRow.appendChild(thCol);   
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"5%"});
                            thCol.append(Translate('QTY'));
                        inventoryValueHeadRow.appendChild(thCol);    
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"7%"});
                            thCol.append(Translate('Ave Cost'));
                        inventoryValueHeadRow.appendChild(thCol);  
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;"});
                            thCol.append(Translate('Total'));
                        inventoryValueHeadRow.appendChild(thCol);                         
                    inventoryValueHead.appendChild(inventoryValueHeadRow);
                inventoryValueTable.appendChild(inventoryValueHead);
                    const inventoryValueBody = cTag('tbody');
                    //=========Dynamic Data Loop Here=========//
                    let tableData = data.tableData;
                    let dateTotal = 0;
                    tableData.forEach(function (oneRow){

                        dateTotal += oneRow[10];
                        inventoryValueHeadRow = cTag('tr');
                        let p = 0;
                        oneRow.forEach(function (oneCol,indx){
                            let align = 'left';
                            if(p>0){align = 'right';}
                            let tdCol = cTag('td', {'data-title': titleColTitles[p], 'align': align});

                            if(indx===0) {
                                let sku = cTag('a', {class: "txtunderline txtblue", href:`/Products/view/${oneRow[0]}`})
                                sku.innerHTML = oneRow[2];
                                tdCol.append(oneRow[1], ' (', sku, ')');
                                inventoryValueHeadRow.appendChild(tdCol);

                                tdCol = cTag('td', {'data-title': 'Product Type', 'align': align});
                                tdCol.innerHTML = oneRow[11];
                            }
                            else if([1, 2, 11].includes(indx)) return;
                            else if([4,5,7,9,10].includes(indx)) {
                                oneCol = addCurrency(oneCol);
                                tdCol.innerHTML = oneCol;
                            }
                            else if([3,6,8].includes(indx)) {
                                tdCol.innerHTML = oneCol;
                            }
                            p++;
                            inventoryValueHeadRow.appendChild(tdCol);
                        });
                        inventoryValueBody.appendChild(inventoryValueHeadRow);
                    });
                    let InventoryValueon = document.getElementById("InventoryValueon");
                    InventoryValueon.innerHTML = Translate('Inventory Value on')+' '+Inventory_Date+': '+ addCurrency(dateTotal);
                
                inventoryValueTable.appendChild(inventoryValueBody);
            noMoreTables.appendChild(inventoryValueTable);
        Searchresult.appendChild(noMoreTables);
    }
}

function inventory_Value(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let Inventory_Date = DBDateToViewDate(params.get("Inventory_Date"));

    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    let inventoryValueTitle = cTag('span', {id:"ptitle"});
                    inventoryValueTitle.innerHTML = Translate('Inventory Value')+' ';
                headerTitle.append(inventoryValueTitle);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);

            let hiddenValue1 = cTag('input',{type:"hidden", id:"twoSegments", value:"Inventory_reports-inventory_Value"});
            let hiddenValue2 = cTag('input',{type:"hidden", id:"pageURI", value:`${segment1}/${segment2}`});        
        titleRow.append(hiddenValue1, hiddenValue2);
        
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': " text-align: end;"});
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Inventory_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));

                let printButton = cTag('button', {class:"btn printButton", 'style': " margin-left: 10px;"});
                printButton.addEventListener('click', print_Inventory_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.append( reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        const inventoryValueRow = cTag('div',{class:"flexSpaBetRow"});
            let inventoryValueColumn = cTag('div',{class:"columnSM12", 'style': "padding: 0;"});
                let divStyle = cTag('div',{style:"background:#FFF;"});
                    const dropDownRow = cTag('div',{class: "flexEndRow"})
                        let dateRange = cTag('div',{class:"input-group daterangeContainer", style:"width:180px; margin-right:15px"});
                            let inputField = cTag('input',{minlength:"10", maxlength:"10", type:"text", class:"form-control search hasDatepicker", 'style': "padding-left: 35px;", name:"Inventory_Date", id:"Inventory_Date", value:Inventory_Date});
                            date_picker_dialog(inputField,({date, month, year}, close)=>{
                                close();
                                let dateVal;
                                if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                                    dateVal = date+'-'+month+'-'+year;    
                                }    
                                else{    
                                    dateVal = month+'/'+date+'/'+year;    
                                }    
                                inputField.value = dateVal;
                            });
                            let searchSpan = cTag('span',{class:"input-group-addon cursor", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title':"Inventory Value"});
                            searchSpan.append(cTag('i',{class:"fa fa-search"}));
                            searchSpan.addEventListener('click', AJ_inventory_Value_MoreInfo);
                        dateRange.append(inputField, searchSpan);
                    dropDownRow.appendChild(dateRange);
                    
                        let dateTotal_button = cTag('button', {class:"btn reportButton", id:"InventoryValueon", style:"margin-right:5px"});
                    dropDownRow.appendChild(dateTotal_button);

                divStyle.appendChild(dropDownRow);
                    let searchResultColumn = cTag('div',{class:"columnXS12"});
                        let searchResult = cTag('div',{id:"Searchresult"});
                        searchResult.append(' ');
                    searchResultColumn.appendChild(searchResult);
                divStyle.appendChild(searchResultColumn);
                //======Template Start=====//            
            inventoryValueColumn.appendChild(divStyle);
        inventoryValueRow.appendChild(inventoryValueColumn);
    viewPageInfo.appendChild(inventoryValueRow);
    //======Template End=====//

    let list_filters;

    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}
 
    if(list_filters.hasOwnProperty("Inventory_Date")){
        Inventory_Date = list_filters.Inventory_Date;
    }
    document.getElementById("Inventory_Date").value = Inventory_Date;

    AJ_inventory_Value_MoreInfo();
}

async function AJ_inventory_ValueN_MoreInfo(){
    let Inventory_Date = document.getElementById("Inventory_Date").value;
	const jsonData = {};
	jsonData['Inventory_Date'] = Inventory_Date;
		
    const url = '/'+segment1+'/AJ_inventory_ValueN_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
            
        const Searchresult = document.querySelector('#Searchresult');
        Searchresult.innerHTML = '';
            let inventoryValueHeadRow, thCol;
            const noMoreTables = cTag('div', {id:"no-more-tables"});
                const inventoryValueTable = cTag('table', {class:"bgnone table-bordered table-striped table-condensed cf listing"});
                    const inventoryValueHead = cTag('thead', {class:"cf"}); 
                    const titleColTitles = [Translate('Product Name'), Translate('QTY'), Translate('Ave Cost'), Translate('Total'), Translate('QTY'), Translate('Total'), Translate('QTY'), Translate('Ave Cost'), Translate('Total')];
                        inventoryValueHeadRow = cTag('tr');
                            thCol = cTag('th', {'style': "text-align: center;", rowspan:"2"});
                            thCol.append(Translate('Product Name'));
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {'style': "text-align: center;", rowspan:"2", width:"10%"});
                            thCol.append(Translate('Product Type'));
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: center;", colspan:"3", width:"20%"});
                            thCol.append('Amount in Temp on '+Inventory_Date);
                        inventoryValueHeadRow.appendChild(thCol);
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: center;", colspan:"3", width:"20%"});
                            thCol.append(Translate('Inventory Value on')+' '+Inventory_Date);
                        inventoryValueHeadRow.appendChild(thCol);
                    inventoryValueHead.appendChild(inventoryValueHeadRow);
                        inventoryValueHeadRow = cTag('tr');
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"5%"});
                            thCol.append(Translate('QTY'));
                        inventoryValueHeadRow.appendChild(thCol);   
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"7%"});
                            thCol.append(Translate('Ave Cost'));
                        inventoryValueHeadRow.appendChild(thCol);  
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;"});
                            thCol.append(Translate('Total'));
                        inventoryValueHeadRow.appendChild(thCol);   
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"5%"});
                            thCol.append(Translate('QTY'));
                        inventoryValueHeadRow.appendChild(thCol);    
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;", width:"7%"});
                            thCol.append(Translate('Ave Cost'));
                        inventoryValueHeadRow.appendChild(thCol);  
                            thCol = cTag('th', {class:"boxborder", 'style': "text-align: right;"});
                            thCol.append(Translate('Total'));
                        inventoryValueHeadRow.appendChild(thCol);                         
                    inventoryValueHead.appendChild(inventoryValueHeadRow);
                inventoryValueTable.appendChild(inventoryValueHead);
                    const inventoryValueBody = cTag('tbody');
                    //=========Dynamic Data Loop Here=========//
                    let tableData = data.tableData;
                    let dateTotal = 0;
                    tableData.forEach(function (oneRow){

                        dateTotal += oneRow[8];
                        inventoryValueHeadRow = cTag('tr');
                        let p = 0;
                        oneRow.forEach(function (oneCol,indx){
                            let align = 'left';
                            if(p>0){align = 'right';}
                            let tdCol = cTag('td', {'data-title': titleColTitles[p], 'align': align});

                            if(indx===0) {
                                let sku = cTag('a', {class: "txtunderline txtblue", target:'_blank', href:`http://${oneRow[10]}.machousel.com.bd/Products/view/${oneRow[0]}`})
                                sku.innerHTML = oneRow[2];
                                tdCol.append(oneRow[1], ' (', sku, ')');
                                inventoryValueHeadRow.appendChild(tdCol);

                                tdCol = cTag('td', {'data-title': 'Product Type', 'align': align});
                                tdCol.innerHTML = oneRow[9];
                            }
                            else if([1, 2, 9, 10].includes(indx)) return;
                            else if([4,5,7,8].includes(indx)) {
                                oneCol = addCurrency(oneCol);
                                tdCol.innerHTML = oneCol;
                            }
                            else if([3,6].includes(indx)) {
                                tdCol.innerHTML = oneCol;
                            }
                            p++;
                            inventoryValueHeadRow.appendChild(tdCol);
                        });
                        inventoryValueBody.appendChild(inventoryValueHeadRow);
                    });
                    let InventoryValueon = document.getElementById("InventoryValueon");
                    InventoryValueon.innerHTML = Translate('Inventory Value on')+' '+Inventory_Date+': '+ addCurrency(dateTotal);
                
                inventoryValueTable.appendChild(inventoryValueBody);
            noMoreTables.appendChild(inventoryValueTable);
        Searchresult.appendChild(noMoreTables);
    }
}

function inventory_ValueN(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let Inventory_Date = DBDateToViewDate(params.get("Inventory_Date"));

    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    let inventoryValueTitle = cTag('span', {id:"ptitle"});
                    inventoryValueTitle.innerHTML = Translate('Inventory Value')+' ';
                headerTitle.append(inventoryValueTitle);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);

            let hiddenValue1 = cTag('input',{type:"hidden", id:"twoSegments", value:"Inventory_reports-inventory_Value"});
            let hiddenValue2 = cTag('input',{type:"hidden", id:"pageURI", value:`${segment1}/${segment2}`});        
        titleRow.append(hiddenValue1, hiddenValue2);
        
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': " text-align: end;"});
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Inventory_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));

                let printButton = cTag('button', {class:"btn printButton", 'style': " margin-left: 10px;"});
                printButton.addEventListener('click', print_Inventory_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.append( reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        const inventoryValueRow = cTag('div',{class:"flexSpaBetRow"});
            let inventoryValueColumn = cTag('div',{class:"columnSM12", 'style': "padding: 0;"});
                let divStyle = cTag('div',{style:"background:#FFF;"});
                    const dropDownRow = cTag('div',{class: "flexEndRow"})
                        let dateRange = cTag('div',{class:"input-group daterangeContainer", style:"width:180px; margin-right:15px"});
                            let inputField = cTag('input',{minlength:"10", maxlength:"10", type:"text", class:"form-control search hasDatepicker", 'style': "padding-left: 35px;", name:"Inventory_Date", id:"Inventory_Date", value:Inventory_Date});
                            date_picker_dialog(inputField,({date, month, year}, close)=>{
                                close();
                                let dateVal;
                                if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                                    dateVal = date+'-'+month+'-'+year;    
                                }    
                                else{    
                                    dateVal = month+'/'+date+'/'+year;    
                                }    
                                inputField.value = dateVal;
                            });
                            let searchSpan = cTag('span',{class:"input-group-addon cursor", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title':"Inventory Value"});
                            searchSpan.append(cTag('i',{class:"fa fa-search"}));
                            searchSpan.addEventListener('click', AJ_inventory_ValueN_MoreInfo);
                        dateRange.append(inputField, searchSpan);
                    dropDownRow.appendChild(dateRange);
                    
                        let dateTotal_button = cTag('button', {class:"btn reportButton", id:"InventoryValueon", style:"margin-right:5px"});
                    dropDownRow.appendChild(dateTotal_button);

                divStyle.appendChild(dropDownRow);
                    let searchResultColumn = cTag('div',{class:"columnXS12"});
                        let searchResult = cTag('div',{id:"Searchresult"});
                        searchResult.append(' ');
                    searchResultColumn.appendChild(searchResult);
                divStyle.appendChild(searchResultColumn);
                //======Template Start=====//            
            inventoryValueColumn.appendChild(divStyle);
        inventoryValueRow.appendChild(inventoryValueColumn);
    viewPageInfo.appendChild(inventoryValueRow);
    //======Template End=====//

    let list_filters;

    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}
 
    if(list_filters.hasOwnProperty("Inventory_Date")){
        Inventory_Date = list_filters.Inventory_Date;
    }
    document.getElementById("Inventory_Date").value = Inventory_Date;

    AJ_inventory_ValueN_MoreInfo();
}

function inventory_ValueN1(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let Inventory_Date = DBDateToViewDate(params.get("Inventory_Date"));

    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    let inventoryValueTitle = cTag('span', {id:"ptitle"});
                    inventoryValueTitle.innerHTML = Translate('Inventory Value')+' ';
                headerTitle.append(inventoryValueTitle);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);

            let hiddenValue1 = cTag('input',{type:"hidden", id:"twoSegments", value:"Inventory_reports-inventory_Value"});
            let hiddenValue2 = cTag('input',{type:"hidden", id:"pageURI", value:`${segment1}/${segment2}`});        
        titleRow.append(hiddenValue1, hiddenValue2);
        
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': " text-align: end;"});
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Inventory_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));

                let printButton = cTag('button', {class:"btn printButton", 'style': " margin-left: 10px;"});
                printButton.addEventListener('click', print_Inventory_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.append( reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        const inventoryValueRow = cTag('div',{class:"flexSpaBetRow"});
            let inventoryValueColumn = cTag('div',{class:"columnSM12", 'style': "padding: 0;"});
                let divStyle = cTag('div',{style:"background:#FFF;"});
                    const dropDownRow = cTag('div',{class: "flexEndRow"})
                        let dateRangeField = cTag('div',{class:"columnXS12 columnSM4 columnLG3"});
                        dateRangeField.append(' ');
                            let dateRange = cTag('div',{class:"input-group daterangeContainer"});
                                let inputField = cTag('input',{minlength:"10", maxlength:"10", type:"text", class:"form-control search hasDatepicker", 'style': "padding-left: 35px;", name:"Inventory_Date", id:"Inventory_Date", value:Inventory_Date});
                                date_picker_dialog(inputField,({date, month, year}, close)=>{
                                    close();
                                    let dateVal;
                                    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                                        dateVal = date+'-'+month+'-'+year;    
                                    }    
                                    else{    
                                        dateVal = month+'/'+date+'/'+year;    
                                    }    
                                    inputField.value = dateVal;
                                });
                                let searchSpan = cTag('span',{class:"input-group-addon cursor", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title':"Inventory Value"});
                                searchSpan.append(cTag('i',{class:"fa fa-search"}));
                                searchSpan.addEventListener('click', AJ_inventory_ValueN_MoreInfo);
                            dateRange.append(inputField, searchSpan);
                        dateRangeField.appendChild(dateRange);
                    dropDownRow.appendChild(dateRangeField);
                divStyle.appendChild(dropDownRow);
                    let searchResultColumn = cTag('div',{class:"columnXS12"});
                        let searchResult = cTag('div',{id:"Searchresult"});
                        searchResult.append(' ');
                    searchResultColumn.appendChild(searchResult);
                divStyle.appendChild(searchResultColumn);
                //======Template Start=====//            
            inventoryValueColumn.appendChild(divStyle);
        inventoryValueRow.appendChild(inventoryValueColumn);
    viewPageInfo.appendChild(inventoryValueRow);
    //======Template End=====//

    let list_filters;

    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}
 
    if(list_filters.hasOwnProperty("Inventory_Date")){
        Inventory_Date = list_filters.Inventory_Date;
    }
    document.getElementById("Inventory_Date").value = Inventory_Date;

    AJ_inventory_ValueN_MoreInfo();
}

async function AJ_inventory_Purchased_MoreInfo(){
    let Purchased_Date = document.getElementById("po_datetime").value;
    const jsonData = {};
    jsonData['po_datetime'] = Purchased_Date;
    jsonData['supplier'] = document.getElementById("supplier").value;
    
    const url = '/'+segment1+'/AJ_inventory_Purchased_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
            
        const Searchresult = document.querySelector('#Searchresult');
        Searchresult.innerHTML = '';
            let purchasedHeadRow, tdCol;
            const noMoreTables = cTag('div', {id:"no-more-tables"});
                const buttonName = cTag('div',{class:'flexEndRow', 'style': "padding-bottom: 4px;", id:"filterby"})
                    let grandtotal_button = cTag('button', {class:"btn reportButton"});
                buttonName.appendChild(grandtotal_button);
            noMoreTables.appendChild(buttonName);
                const inventoryPurchasedTable = cTag('table', {class:"bgnone table-bordered table-striped table-condensed cf listing"});
                    const inventoryPurchasedHead = cTag('thead', {class:"cf"}); 
                    const titleColTitles = [Translate('PO Date'), Translate('PO'), Translate('Lot Ref. No.'), Translate('SKU/Barcode'), Translate('Product Name'), Translate('QTY'), Translate('Cost'), Translate('Total Cost')];
                        purchasedHeadRow = cTag('tr');
                            const thCol0 = cTag('th', { width:"11%"});
                            thCol0.append(Translate('PO Date'));
                            const thCol1 = cTag('th', { width:"8%"});
                            thCol1.append(Translate('PO')+'#');
                            const thCol2 = cTag('th', { width:"10%"});
                            thCol2.append(Translate('Lot Ref. No.')+'#');
                            const thCol3 = cTag('th', {width:"13%"});
                            thCol3.append(Translate('SKU/Barcode'));
                            const thCol4 = cTag('th');
                            thCol4.append(Translate('Product Name'));
                            const thCol5 = cTag('th',{'style': "text-align: right;", width:"8%"});
                            thCol5.append(Translate('QTY'));
                            const thCol6 = cTag('th',{'style': "text-align: right;", width:"10%"});
                            thCol6.append(Translate('Cost'));
                            const thCol7 = cTag('th',{'style': "text-align: right;", width:"10%"});
                            thCol7.append(Translate('Total Cost'));
                        purchasedHeadRow.append(thCol0,thCol1,thCol2,thCol3,thCol4,thCol5,thCol6,thCol7);
                    inventoryPurchasedHead.appendChild(purchasedHeadRow);
                inventoryPurchasedTable.appendChild(inventoryPurchasedHead);
                    const inventoryPurchasedBody = cTag('tbody');
                    const tableData = data.tableData;
                    let grandtotal = 0;
                    tableData.forEach(function (oneRow){
                        const total = oneRow[5]*oneRow[6];
                        grandtotal += total;
                        purchasedHeadRow = cTag('tr');
                        let p = 0;
                        oneRow.forEach(function (oneCol,indx){
                            if(indx===6) oneCol = addCurrency(oneCol);
                            let align = 'center';
                            if(p === 2 || p === 3){align = 'left';}
                            else if(p > 3){align = 'right'}
                            tdCol = cTag('td', {'data-title': titleColTitles[p], 'align': align});
                            
                            if(p===0){
                                tdCol.innerHTML = DBDateToViewDate(oneCol, 0, 1);
                                    tdCol.setAttribute('align', 'left');
                            }
                            else{
                                tdCol.innerHTML = oneCol||'&nbsp;';
                            }
                                p++;
                            purchasedHeadRow.appendChild(tdCol);
                        });
                            tdCol = cTag('td', {'data-title': titleColTitles[7], 'align': 'right'});
                            tdCol.innerHTML = addCurrency(total);
                            purchasedHeadRow.appendChild(tdCol);
                        inventoryPurchasedBody.appendChild(purchasedHeadRow);
                    });
                        if(tableData.length === 0){
                            purchasedHeadRow = cTag('tr');
                                tdCol = cTag('td', {colspan:"10"});
                                tdCol.innerHTML = '';
                            purchasedHeadRow.appendChild(tdCol);
                        inventoryPurchasedBody.appendChild(purchasedHeadRow);
                    }
                    grandtotal_button.append(Translate('Grand Total')+' : '+ addCurrency(grandtotal));
                inventoryPurchasedTable.appendChild(inventoryPurchasedBody);
            noMoreTables.appendChild(inventoryPurchasedTable);
        Searchresult.appendChild(noMoreTables);
        if(document.querySelector("#supplier")){AJautoComplete('supplier');}
    }
}

function inventory_Purchased(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let po_datetime = DBDateRangeToViewDate(params.get("po_datetime"));
    let supplier = params.get("supplier");
    
    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        let list_filters, input, inputField;
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS12 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    let title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Inventory Purchased')+' ';
                headerTitle.append(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS12 columnSM4", 'style': "text-align: end;"});
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Inventory_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));

                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Inventory_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        let purchasedRow = cTag('div',{class:"flexSpaBetRow"});
            let purchasedColumn = cTag('div',{class:"columnSM12"});
                let input1 = cTag('input',{type:"hidden", id:"twoSegments", value:"Inventory_reports-inventory_Value"});
                let input2 = cTag('input',{type:"hidden", id:"pageURI", value:`${segment1}/${segment2}`});        
                let callout = cTag('div',{style:"background: #FFF;"});
                    const dropDownRow = cTag('div',{class:"flexEndRow"});
                        let dateRangeField = cTag('div',{class:"columnXS12 columnSM5 columnLG3 daterangeContainer"});
                            inputField = cTag('input',{minlength:23, maxlength:23, type:"text", class:"form-control search sales_date", 'style': "padding-left: 35px;", name:"po_datetime", id:"po_datetime",value: po_datetime});
                            daterange_picker_dialog(inputField);
                        dateRangeField.appendChild(inputField);
                    dropDownRow.appendChild(dateRangeField);
                        let searchColumn = cTag('div',{class:"columnXS12 columnSM5 columnLG3"});
                            let searchInGroup = cTag('div',{class:"input-group"});
                                let supplierLabel = cTag('label',{for:"supplier", class:"input-group-addon cursor"});
                                supplierLabel.append(Translate('Supplier'));
                                inputField = cTag('input',{maxlength:50, type:"text", class:"form-control", name:"supplier", id:"supplier", value: supplier, placeholder: Translate('Search Suppliers')});
                                inputField.addEventListener('keydown',event=>{if(event.which===13) AJ_inventory_Purchased_MoreInfo()});
                                let searchSpan = cTag('span',{class:"input-group-addon cursor", 'data-toggle':"tooltip", 'data-placement':"bottom", title: Translate('Date wise Search')});
                                searchSpan.addEventListener('click',AJ_inventory_Purchased_MoreInfo);
                                    let searchIcon = cTag('i',{class:"fa fa-search"});
                                searchSpan.appendChild(searchIcon);
                            searchInGroup.append(supplierLabel,inputField,searchSpan);
                        searchColumn.appendChild(searchInGroup);
                    dropDownRow.appendChild(searchColumn);
                callout.appendChild(dropDownRow);
                    let searchResultColumn = cTag('div',{ class:"columnXS12"});
                        let searchResult = cTag('div',{id:"Searchresult"});
                        searchResult.append(' ');
                    searchResultColumn.appendChild(searchResult);
                callout.appendChild(searchResultColumn);
            purchasedColumn.append(input1, input2, callout);
        purchasedRow.appendChild(purchasedColumn);
    viewPageInfo.appendChild(purchasedRow);
        
    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}
 
    if(list_filters.hasOwnProperty("po_datetime")){
        po_datetime = list_filters.po_datetime;
    }
    document.getElementById("po_datetime").value = po_datetime;

    if(list_filters.hasOwnProperty("supplier")){
        supplier = list_filters.supplier;
    }
    document.getElementById("supplier").value = supplier;

    AJ_inventory_Purchased_MoreInfo();
}

async function AJ_products_Report_MoreInfo(){
    const url = '/'+segment1+'/AJ_products_Report_MoreInfo';
    fetchData(afterFetch,url);

    function afterFetch(data){
        let select, option;
        select = document.querySelector("#smanufacturer_id");
        select.innerHTML = '';
        option = cTag('option', {value:''});
        option.innerHTML = Translate('All Manufacturers');
        select.appendChild(option);
        setOptions(select,data.manOpt, 1, 1);

        select = document.querySelector("#scategory_id");
        select.innerHTML = '';
        option = cTag('option', {value:''});
        option.innerHTML = Translate('All Categories');
        select.appendChild(option);
        setOptions(select,data.catOpt, 1, 1);
    }

    products_ReportData();
}

async function products_ReportData(){
	const jsonData = {};
    jsonData['sortby'] = document.getElementById("sortby").value;
    jsonData['data_type'] = document.getElementById("data_type").value;
    jsonData['smanufacturer_id'] = document.getElementById("smanufacturer_id").value;
    jsonData['scategory_id'] = document.getElementById("scategory_id").value;

    const url = '/Inventory_reports/products_ReportData/';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
            
        const Searchresult = document.querySelector('#Searchresult');
        Searchresult.innerHTML = '';
            let productReportHeadRow;
            const noMoreTables = cTag('div', {id:"no-more-tables"});
                const buttonName = cTag('div',{class:'flexEndRow', 'style': "padding-bottom: 4px;", id:"filterby"})
                    const grandtotal_button2 = cTag('button',{class:" btn reportButton", 'style': "margin-right: 15px; margin-bottom: 5px;"});
                buttonName.appendChild(grandtotal_button2);
                    const grandCostTotal_button1 = cTag('button', {class:" btn reportButton ", 'style': "margin-bottom: 5px;"});
                buttonName.appendChild(grandCostTotal_button1);
            noMoreTables.appendChild(buttonName)
                const productReportTable = cTag('table', {class:"bgnone table-bordered table-striped table-condensed cf listing"});
                    const productReportHead = cTag('thead', {class:"cf"}); 
                    const titleColTitles = [Translate('Manufacturer'), Translate('Product Name'), Translate('SKU/Barcode'), Translate('Category'), Translate('Price'), Translate('Cost'), Translate('QTY in Inventory')];
                        productReportHeadRow = cTag('tr');
                            const thCol0 = cTag('th', { width:"15%"});
                            thCol0.innerHTML = titleColTitles[0];

                            const thCol1 = cTag('th');
                            thCol1.innerHTML = titleColTitles[1];

                            const thCol2 = cTag('th', {width:"22%"});
                            thCol2.innerHTML = titleColTitles[2];

                            const thCol3 = cTag('th', {width:"15%"});
                            thCol3.innerHTML = titleColTitles[3];

                            const thCol4 = cTag('th',{'style': "text-align: right;", width:"10%"});
                            thCol4.innerHTML = titleColTitles[4];

                            const thCol5 = cTag('th',{'style': "text-align: right;", width:"10%"});
                            thCol5.innerHTML = titleColTitles[5];

                            const thCol6 = cTag('th',{'style': "text-align: right;", width:"8%"});
                            thCol6.innerHTML = titleColTitles[6];
                        productReportHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6);
                    productReportHead.appendChild(productReportHeadRow);
                productReportTable.appendChild(productReportHead);
                    const productReportBody = cTag('tbody',{id:'tableRows'});
                    let tableData = data.tableData;
                    let grandtotal = 0;
                    let grandCostTotal = 0;
                    tableData.forEach(function (oneRow){
                        if(oneRow[6]!=='*'){
                            grandtotal += (oneRow[6]*oneRow[4]);
                            grandCostTotal += (oneRow[6]*oneRow[5]);
                        }
                        productReportHeadRow = cTag('tr');
                        let alertclass = oneRow[7];
                        if(alertclass !==''){productReportHeadRow.setAttribute('class', alertclass);}
                        let p = 0;
                        let k = 0;
                        oneRow.forEach(function (oneCol,indx){
                            if([4,5].includes(indx)) oneCol = addCurrency(oneCol);
                            let align = 'right';
                            if(p < 4){align = 'left';}
                            if(k < 7){
                                let tdCol = cTag('td', {'data-title': titleColTitles[p], 'align': align});
                                tdCol.innerHTML = oneCol||'&nbsp;';
                                p++;
                                productReportHeadRow.appendChild(tdCol);
                            }
                            k++;
                        });
                        productReportBody.appendChild(productReportHeadRow);
                    });
                    grandCostTotal_button1.append(Translate('Grand Cost')+' : '+ addCurrency(grandCostTotal));
                    grandtotal_button2.append(Translate('Grand Price')+' : '+ addCurrency(grandtotal));
                productReportTable.appendChild(productReportBody);
            noMoreTables.appendChild(productReportTable);
        Searchresult.appendChild(noMoreTables);
    }
}

function products_Report(){
    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow", 'style': "padding: 5px;"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    let title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Products Report')+' ';
                headerTitle.append(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Inventory_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));

                let printButton = cTag('button', {class:"btn printButton", 'style': " margin-left: 10px;"});
                printButton.addEventListener('click', print_Inventory_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        let productColumn = cTag('div',{class:"columnSM12", 'style': "margin: 0;"});
            let input1 = cTag('input',{type:"hidden", id:"twoSegments", value:"Inventory_reports-products_Report"});
            let input2 = cTag('input',{type:"hidden", id:"pageURI", value:`${segment1}/${segment2}`});        
            let styleDiv = cTag('div',{style:"background:#FFF;"});
                const productReportFlex = cTag('div',{class:"flexEndRow"});
                    let viewColumn = cTag('div',{class:"columnXS6 columnSM3"});
                        const viewInGroup = cTag('div',{class:"input-group"});
                            const viewLabel = cTag('label',{class:"input-group-addon cursor", for:"sortby"});
                            viewLabel.append(Translate('View'));
                        viewInGroup.appendChild(viewLabel);
                            const selectView = cTag('select',{name:"sortby", id:"sortby", class:"form-control"});
                            selectView.addEventListener('change',products_ReportData);
                                const nameOption = cTag('option',{value:"name"});
                                nameOption.append(Translate('Sort by Name'));
                            selectView.appendChild(nameOption);
                                const skuOption = cTag('option',{value:"sku"});
                                skuOption.append(Translate('Sort by SKU'));
                            selectView.appendChild(skuOption);
                        viewInGroup.appendChild(selectView);
                    viewColumn.appendChild(viewInGroup);
                productReportFlex.appendChild(viewColumn);
                    let typeColumn = cTag('div',{class:"columnXS6 columnSM3"});
                        const typeInGroup = cTag('div',{class:"input-group"});
                            const typeLabel = cTag('label',{class:"input-group-addon cursor", for:"data_type"});
                            typeLabel.append(Translate('Type'));
                        typeInGroup.appendChild(typeLabel);
                            const selectType = cTag('select',{name:"data_type", id:"data_type", class:"form-control"});
                            selectType.addEventListener('change',products_ReportData);
                                const allProductOption = cTag('option',{value:"All"});
                                allProductOption.append(Translate('All Products'));
                            selectType.appendChild(allProductOption);
                                const availableOption = cTag('option',{value:"Available"});
                                availableOption.append(Translate('Available'));
                            selectType.appendChild(availableOption);
                                let stockOption = cTag('option',{value:"Low Stock"});
                                stockOption.append(Translate('Low Stock'));
                            selectType.appendChild(stockOption);
                        typeInGroup.appendChild(selectType);
                    typeColumn.appendChild(typeInGroup);
                productReportFlex.appendChild(typeColumn);
                    let manufacturerColumn = cTag('div',{class:"columnXS6 columnSM3"});
                        const selectManufacturer = cTag('select',{name:"smanufacturer_id", id:"smanufacturer_id", class:"form-control"});
                        selectManufacturer.addEventListener('change',products_ReportData);
                            const manufacturerOption = cTag('option', {value:''});
                            manufacturerOption.innerHTML = Translate('All Manufacturers');
                        selectManufacturer.appendChild(manufacturerOption);
                    manufacturerColumn.appendChild(selectManufacturer);
                productReportFlex.appendChild(manufacturerColumn);
                    const categoriesColumn = cTag('div',{class:"columnXS6 columnSM3"});
                        const categorieInGroup = cTag('div',{class:"input-group"});
                            let selectCategories = cTag('select',{name:"scategory_id", id:"scategory_id", class:"form-control"});
                            selectCategories.addEventListener('change',products_ReportData);
                                const categorieOption = cTag('option', {value:''});
                                categorieOption.innerHTML = Translate('All Categories');
                            selectCategories.appendChild(categorieOption);
                        categorieInGroup.appendChild(selectCategories);
                            const searchSpan = cTag('span', {class:'input-group-addon cursor', 'data-toggle': 'tooltip', 'data-placement': 'bottom', title: '', 'data-original-title': 'Search by Categories'});
                            searchSpan.addEventListener('click', products_ReportData);
                                const searchIcon = cTag('i', {class:'fa fa-search'});
                            searchSpan.appendChild(searchIcon)
                        categorieInGroup.appendChild(searchSpan);
                    categoriesColumn.appendChild(categorieInGroup);
                productReportFlex.appendChild(categoriesColumn);
            styleDiv.appendChild(productReportFlex);
                const searchResultRow = cTag('div',{ class:"columnXS12"});
                    let SearchResult = cTag('div',{id:"Searchresult"});
                    SearchResult.append(' ');
                searchResultRow.appendChild(SearchResult);
            styleDiv.appendChild(searchResultRow);
        productColumn.append(input1, input2, styleDiv);
    viewPageInfo.appendChild(productColumn);

     //======sessionStorage =======//
     let list_filters;
     if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}

    let smanufacturer_id = '';
    checkAndSetSessionData('smanufacturer_id', smanufacturer_id, list_filters);
    let scategory_id = '';
    checkAndSetSessionData('scategory_id', scategory_id, list_filters);

    AJ_products_Report_MoreInfo();
}

async function AJ_purchase_Orders_MoreInfo(){
    let Purchased_Date = document.getElementById("po_datetime").value;
    
	const jsonData = {};
	jsonData['po_datetime'] = Purchased_Date;
    jsonData['suppliers_id'] = document.getElementById("suppliers_id").value;
    jsonData['posupplier'] = document.getElementById("posupplier").value;

    const url = '/Inventory_reports/purchase_OrdersData/';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
            
        const Searchresult = document.querySelector('#Searchresult');
        Searchresult.innerHTML = '';
            let purchaseOrderHeadRow, tdCol;
            const noMoreTables = cTag('div', {id:"no-more-tables"});
                const buttonName = cTag('div',{class:'flexEndRow', 'style': "padding-bottom: 4px;", id:"filterby"})
                    let grandtotal_button = cTag('button', {class:"btn reportButton"});
                buttonName.appendChild(grandtotal_button)
            noMoreTables.appendChild(buttonName);

                const purchaseOrderTable = cTag('table', {class:"bgnone table-bordered table-striped table-condensed cf listing"});
                    const purchaseOrderHead = cTag('thead', {class:"cf"}); 
                    const titleColTitles = [Translate('Date'), Translate('Purchase Orders'), Translate('Lot Ref. No.'), Translate('Supplier'), Translate('Goods Total'), Translate('Sales Tax'), Translate('Shipping Cost'), Translate('Total'), Translate('Return'), Translate('Status')];
                        purchaseOrderHeadRow = cTag('tr');
                            const thCol0 = cTag('th', {'style': "width: 80px;"});
                            thCol0.append(Translate('Date'));
                            const thCol1 = cTag('th', {width:"8%"});
                            thCol1.append(Translate('Purchase Orders'));
                            const thCol2 = cTag('th', {width:"10%"});
                            thCol2.append(Translate('Lot Ref. No.'));
                            const thCol3 = cTag('th');
                            thCol3.append(Translate('Supplier'));
                            const thCol4 = cTag('th',{width:"8%"});
                            thCol4.append(Translate('Goods Total'));
                            const thCol5 = cTag('th',{width:"8%"});
                            thCol5.append(Translate('Sales Tax'));
                            const thCol6 = cTag('th',{width:"8%"});
                            thCol6.append(Translate('Shipping Cost'));
                            const thCol7 = cTag('th',{ width:"8%"});
                            thCol7.append(Translate('Total'));
                            const thCol8 = cTag('th',{width:"8%"});
                            thCol8.append(Translate('Return'));
                            const thCol9 = cTag('th',{width:"8%"});
                            thCol9.append(Translate('Status'));
                        purchaseOrderHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8, thCol9);
                    purchaseOrderHead.appendChild(purchaseOrderHeadRow);
                purchaseOrderTable.appendChild(purchaseOrderHead);
                    const purchaseOrderBody = cTag('tbody');
                    let tableData = data.tableData;
                    let grandtotal = 0;
                    tableData.forEach(function (oneRow){
                        let total = oneRow[4];
                        if(oneRow[9]===0) total += oneRow[5]; 
                        else total += total*oneRow[5]*0.01; 
                        total += oneRow[6];
                        oneRow.splice(7,0,total);
                        grandtotal += total;
                        purchaseOrderHeadRow = cTag('tr');
                        let p = 0;    

                        oneRow.forEach(function (oneCol,indx){
                            if(indx===10) return;
                            else if([4,6,7].includes(indx)) oneCol = addCurrency(oneCol);
                            else if(indx===5){
                                if(oneRow[10]===0) oneCol = addCurrency(oneCol);
                                else oneCol = oneCol+'%';
                            }
                            let align= 'right';
                            if( p === 2 || p === 3 || p === 9){align = 'left';}
                            tdCol = cTag('td', {'data-title': titleColTitles[p], 'align': align});
                            if(p===0){
                                tdCol.innerHTML = DBDateToViewDate(oneCol, 0, 1);
                            }
                            else{
                                tdCol.innerHTML = oneCol;
                            }
                            p++;
                            purchaseOrderHeadRow.appendChild(tdCol);
                        });
                        purchaseOrderBody.appendChild(purchaseOrderHeadRow);
                    });
                        if(tableData.length === 0){
                            purchaseOrderHeadRow = cTag('tr');
                                tdCol = cTag('td', {colspan:"10"});
                                tdCol.innerHTML = '';
                            purchaseOrderHeadRow.appendChild(tdCol);
                        purchaseOrderBody.appendChild(purchaseOrderHeadRow);
                    }
                    grandtotal_button.append(Translate('Grand Total')+' : '+ addCurrency(grandtotal));
                purchaseOrderTable.appendChild(purchaseOrderBody);
            noMoreTables.appendChild(purchaseOrderTable);
        Searchresult.appendChild(noMoreTables);
        if(document.querySelector("#posupplier")){
            AJautoComplete('posupplier');
        
            document.getElementById("posupplier").addEventListener('keyup', e => {
                if(document.getElementById("suppliers_id")){
                    document.getElementById("suppliers_id").value = 0;
                }
            });
        }
    }
}

function purchase_Orders(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let po_datetime = DBDateRangeToViewDate(params.get("po_datetime"));
    let posupplier = params.get("posupplier");

    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        let inputField, inputField2, list_filters;
        const titleRow = cTag('div', {class:"flexSpaBetRow", 'style': "padding: 5px;"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    let title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Purchase Orders')+' ';
                headerTitle.append(title);
                    let infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Inventory_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));

                let printButton = cTag('button', {class:"btn printButton", 'style': " margin-left: 10px;"});
                printButton.addEventListener('click', print_Inventory_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        let purchaseOrderColumn = cTag('div',{class:"columnSM12"});
            inputField = cTag('input',{type:"hidden", id:"twoSegments", value:"Inventory_reports-purchase_Orders"});
            inputField2 = cTag('input',{type:"hidden", id:"pageURI", value:`${segment1}/${segment2}`});
        purchaseOrderColumn.append(inputField,inputField2);
            let callout = cTag('div',{style:"background: #FFF;"});
                const purchaseOrderFlex = cTag('div',{class:"flexEndRow"});
                    let dateRangeField = cTag('div',{class:"columnXS12 columnSM4 daterangeContainer"});
                        inputField = cTag('input',{minlength:23, maxlength:23, type:"text", class:"form-control search sales_date", 'style': "padding-left: 35px;", name:"po_datetime", id:"po_datetime",value: po_datetime});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                purchaseOrderFlex.appendChild(dateRangeField);
                    let supplierColumn = cTag('div',{class:"columnXS12 columnSM4"});
                        let supplierInGroup = cTag('div',{class:"input-group"});
                            let supplierLabel = cTag('label',{for:"posupplier", class:"input-group-addon cursor"});
                            supplierLabel.append(Translate('Supplier'));
                        supplierInGroup.appendChild(supplierLabel);
                            inputField = cTag('input',{maxlength:50, type:"text", class:"form-control", name:"posupplier", id:"posupplier", placeholder: Translate('Search Suppliers')});
                            inputField.addEventListener('keydown',event=>{if(event.which===13) AJ_purchase_Orders_MoreInfo()});
                            inputField2 = cTag('input',{type:"hidden", name:"suppliers_id", id:"suppliers_id", value: posupplier});
                        supplierInGroup.append(inputField,inputField2); 
                            let searchSpan = cTag('span',{class:"input-group-addon cursor", 'data-toggle':"tooltip", 'data-placement':"bottom", title: Translate('Date wise Search')});
                            searchSpan.addEventListener('click',AJ_purchase_Orders_MoreInfo);
                                let searchIcon = cTag('i',{class:"fa fa-search"});
                            searchSpan.appendChild(searchIcon);
                        supplierInGroup.append(searchSpan);
                    supplierColumn.appendChild(supplierInGroup);
                purchaseOrderFlex.appendChild(supplierColumn);
            callout.appendChild(purchaseOrderFlex);
                let searchResultColumn = cTag('div',{ class:"columnXS12"});
                    let searchResult = cTag('div',{id:"Searchresult"});
                    searchResult.append(' ');
                searchResultColumn.appendChild(searchResult);
            callout.appendChild(searchResultColumn);
        purchaseOrderColumn.append(callout);
    viewPageInfo.appendChild(purchaseOrderColumn);

    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}
 
    if(list_filters.hasOwnProperty("po_datetime")){
        po_datetime = list_filters.po_datetime;
    }
    document.getElementById("po_datetime").value = po_datetime;
    let suppliers_id = 0;
    if(list_filters.hasOwnProperty("suppliers_id")){
        suppliers_id = list_filters.suppliers_id;
    }
    document.getElementById("suppliers_id").value = suppliers_id;

    if(list_filters.hasOwnProperty("posupplier")){
        posupplier = list_filters.posupplier;
    }
    document.getElementById("posupplier").value = posupplier;

    AJ_purchase_Orders_MoreInfo();
}

function print_Inventory_reports() {
    let div, c, p, s, l, n, r, m, d, 
    e = document.querySelector("#no-more-tables").cloneNode(true),
       a = "", t = 12;

    if ("Inventory_reports-inventory_Purchased" === document.querySelector("#twoSegments").value ? t = 8 : "Inventory_reports-products_Report" === document.querySelector("#twoSegments").value ? t = 7 : "Inventory_reports-purchase_Orders" === document.querySelector("#twoSegments").value && (t = 9),
    document.querySelector("#Inventory_Date")) {
        let i = document.querySelector("#Inventory_Date").value;
        "" !== i && ("" !== a && (a += ", "),
        a += Translate('Date') + ": " + i);

        let InventoryValueon = document.querySelector("#InventoryValueon");
        if(InventoryValueon && InventoryValueon.innerHTML !=='') {
            if(a!=='') a+=', ';
            a += InventoryValueon.innerHTML;
        } 
    }
    
    let po_datetime = document.querySelector("#po_datetime");
    if(po_datetime && po_datetime.value!=='') {
        a += Translate('Date Range') + ": " + po_datetime.value;
    }

    let supplier = document.querySelector("#supplier") || document.querySelector("#posupplier");
    if(supplier && supplier.value!=='') {
        if(a!=='') a+=', ';
        a += Translate('Supplier') + ": " + supplier.value;
    }

    let sortby = document.querySelector("#sortby");
    if(sortby) {
        sortby = sortby.options[sortby.selectedIndex].innerText;
        a += Translate('View') + ": " + sortby;
    }
    
    let data_type = document.querySelector("#data_type");
    if(data_type) {
        data_type = data_type.options[data_type.selectedIndex].innerText;
        a += ', '+Translate('Type') + ": " + data_type;
    }
    
    let smanufacturer_id = document.querySelector("#smanufacturer_id");
    if(smanufacturer_id) {
        let smanufacturer = smanufacturer_id.options[smanufacturer_id.selectedIndex].innerText;
        a += ', '+Translate('Manufacturer') + ": " + smanufacturer;
    }
    
    let scategory_id = document.querySelector("#scategory_id");
    if(scategory_id) {
        let scategory = scategory_id.options[scategory_id.selectedIndex].innerText;
        a += ', '+Translate('Category') + ": " + scategory;
    }
    
     r = document.querySelector("#ptitle").innerHTML
      , d = new Date;
    if ("dd-mm-yyyy" === calenderDate.toLowerCase())
        c = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear();
    else
        c = d.getMonth() + 1 + "/" + d.getDate() + "/" + d.getFullYear();
        let divWidth1 = cTag('div',{ 'class':`flexSpaBetRow` });
            let companyNameDiv = cTag('div',{ 'style': "font-weight: bold; font-size: 18px; text-align: left;" });
            companyNameDiv.innerHTML = stripslashes(companyName);
        divWidth1.appendChild(companyNameDiv);
            let rDiv = cTag('div',{ 'style': "font-size: 20px; font-weight: bold;" });
            rDiv.innerHTML = r;
        divWidth1.appendChild(rDiv);
            let cDiv = cTag('div',{ 'style': "font-size: 16px;" });
            cDiv.innerHTML = c;
        divWidth1.appendChild(cDiv);
        let aDiv = cTag('div');
        aDiv.innerHTML = a;
    let div3 = cTag('div');
        let topBorder = cTag('div',{ 'style': "border-top: 1px solid #CCC; margin-top: 10px; padding-bottom: 10px;" });
    div3.appendChild(topBorder);
    e.prepend(divWidth1,div3,aDiv);
    (new Date).getTime();
    let u = (screen.width - 900) / 2
      , v = (screen.height - 600) / 2;
    let winprops = "height=600,width=900,top=" + v + ",left=" + u + ",scrollbars=1,toolbar=0,location=0,statusbar=0,menubar=0,resizable=0";
    let g = window.open("", '" + id + "', winprops);
        let html = cTag('html');
            let head = cTag('head');
                let title = cTag('title');
                title.innerHTML = r;
            head.appendChild(title);
            head.appendChild(cTag('meta',{ 'charset':`utf-8` }));
                const style = cTag('style');
                style.append(
                    `@page {size: auto;}
                    body{ font-family:Arial, sans-serif, Helvetica; min-width:98%; margin:0; padding:1%;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                    .flexSpaBetRow {display: flex;flex-flow: row wrap;justify-content: space-between; }
                    .flexEndRow {display: flex;flex-flow: row wrap; justify-content: flex-end;}
                    .reportButton {background: #a71d4c;color: #FFF;border-color: #a71d4c; padding: 4px; border-radius: 3px;}
                    table{border-collapse:collapse; width: 100%;}
                    .table-bordered th {background:#F5F5F6; }
                    .table-bordered td, .table-bordered th { border:1px solid #DDDDDD; padding:8px 10px; }
                    .table-bordered td.bgnone {background-color:#FFF;border:0px solid #fff;}`
                );
            head.appendChild(style);
        html.appendChild(head);
            let body = cTag('body');
            body.append(e);
        html.appendChild(body);
    if (g.document.write("<!DOCTYPE html>"),
    g.document.appendChild(html),
    g.document.close(),
    Boolean(window.chrome)) {
        p = !1;
        g.onload = function() {
            g.window.print(),
            p = !0
        }
    } else {
        p = !1;
        g.document.onreadystatechange = function() {
            e = g.document.readyState;
            "interactive" === e || "complete" === e && setTimeout(function() {
                g.document.getElementById("interactive"),
                g.window.print(),
                p = !0
            }, 1e3)
        }
    }
    g.setInterval(function() {
        e = getMobileOperatingSystem();
        !0 === p && "unknown" === e && g.window.close()
    }, 500)
}

function set_Productdata_TableRows(tableData, tdAttributes, uriStr){
    const tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//======Create TBody TR Column======//
	if(tableData.length){
		tableData.forEach(oneRow => {
			let i = 0;
			let tr = document.createElement('tr');
			oneRow.forEach(tdvalue => {
				if(i >= 0){
					let idVal = oneRow[0];
					let td = document.createElement('td');
					let oneTDObj = tdAttributes[i];
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

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, inventory_Value, inventory_ValueN, inventory_Purchased, products_Report, purchase_Orders};
    console.log(segment2);
    layoutFunctions[segment2]();
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});
