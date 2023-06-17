import {cTag, Translate, tooltip, addCurrency, daterange_picker_dialog, fetchData} from './common.js';

const lowInventoryFieldAttributes = [{'datatitle':Translate('Product Name'), 'align':'left'},
    {'datatitle':Translate('In Stock'), 'align':'center'},
    {'datatitle':Translate('Min Level'), 'align':'center'}
];
if(segment2==='') segment2 = 'lists';

function setDashBoardDate(date_range, event=false){
	if(date_range !==''){
        document.getElementById("date_range").value = date_range;
    }
    if(event){
        document.querySelectorAll(".active").forEach(onebutton=>{
            onebutton.classList.remove('active');
        });
        if(!event.target.classList.contains('active')){event.target.classList.add('active');}
    }
	loadData_Dashboard_Data();
}

async function loadData_Dashboard_Data(){
	document.querySelectorAll(".allbutton").forEach(itemParent=>{
        itemParent.querySelectorAll("button.defaultButton").forEach(item=>{
            if(item.classList.contains('active')){
                item.classList.remove('active');
            }

            item.addEventListener('click',function(){
                document.querySelectorAll(".allbutton").forEach(btn=>{
                    btn.querySelector("button").classList.remove('active');
                })
                this.classList.add('active');
            });
        });
    })
	
	const jsonData = {};
    jsonData['date_range']	= document.getElementById('date_range').value;
    const url = '/'+segment1+'/AJgetPage';
    
    fetchData(loadDashBoardData,url,jsonData);
}

function loadDashBoardData({loadData}){
    let infoDiv, infoDiv2, tableData;
    const salesInfo = document.querySelectorAll("#salesInfo div");
    infoDiv = salesInfo[0];
    infoDiv.innerHTML = '';
        let totalLabel = cTag('label', {style: "flex-basis: 30%;"});
        totalLabel.innerHTML = Translate('Total')+':';
    infoDiv.append(totalLabel, loadData.total);
    infoDiv2 = salesInfo[1];
    infoDiv2.innerHTML = '';
        let totalSalesLabel = cTag('label', {style: "flex-basis: 30%;"}); 
        totalSalesLabel.innerHTML = Translate('Total Sales')+':';
    infoDiv2.append(totalSalesLabel, loadData.totalSales.toFixed(2));

    const repairsInfo = document.querySelectorAll("#repairsInfo div");
    infoDiv = repairsInfo[0];
    infoDiv.innerHTML = '';
        let openLabel = cTag('label', {style: "flex-basis: 30%;"}); 
        openLabel.innerHTML = Translate('Open')+':';
    infoDiv.append(openLabel, loadData.ropen);
    infoDiv2 = repairsInfo[1];
    infoDiv2.innerHTML = '';
        let addedLabel = cTag('label', {style: "flex-basis: 30%;"});
        addedLabel.innerHTML = Translate('Added')+':';
    infoDiv2.append(addedLabel, loadData.radded);
    let infoDiv3 = repairsInfo[2];
    infoDiv3.innerHTML = '';
        let invoiceLabel = cTag('label', {style: "flex-basis: 30%;"});
        invoiceLabel.innerHTML = Translate('Invoiced')+':';
    infoDiv3.append(invoiceLabel, loadData.rinvoiced);

    const customersInfo = document.querySelectorAll("#customersInfo div");
    infoDiv = customersInfo[0];
    infoDiv.innerHTML = '';
        let addedTitle = cTag('label', {style: "flex-basis: 30%;"}); 
        addedTitle.innerHTML = Translate('Added')+':';
    infoDiv.append(addedTitle, loadData.cadded);
    infoDiv2 = customersInfo[1];
    infoDiv2.innerHTML = '';
        let purchasedLabel = cTag('label', {style: "flex-basis: 30%;"});
        purchasedLabel.innerHTML = Translate('Purchased')+':';
    infoDiv2.append(purchasedLabel, loadData.cpurchased);
    
    let paymentBody = document.getElementById("tableRows1");
    paymentBody.innerHTML = '';
    tableData = loadData.paymentData;
    //======Create TBody TR Column======//
    let paymentHeadRow;
    if(Object.keys(tableData).length){
        for(const [key, value] of Object.entries(tableData)) {
            paymentHeadRow = cTag('tr');
                let paymentTdCol0 = cTag('td', {'data-title':Translate('Payment Type')});
                paymentTdCol0.innerHTML = key;

                let paymentTdCol1 = cTag('td', {'data-title':Translate('Total'), align:'right'});
                paymentTdCol1.innerHTML = value.toFixed(2);
            paymentHeadRow.append(paymentTdCol0, paymentTdCol1);
            paymentBody.appendChild(paymentHeadRow);
        }
    }
    else{
        paymentHeadRow = cTag('tr');
            let paymentTdCol2 = cTag('td', {'colspan': 2});
            paymentTdCol2.innerHTML = '';
        paymentHeadRow.appendChild(paymentTdCol2);
        paymentBody.appendChild(paymentHeadRow);
    }

    let categoryBody = document.getElementById("tableRows2");
    categoryBody.innerHTML = '';
    tableData = loadData.categoriesData;
    //======Create TBody TR Column======//
    let categoryHeadRow;
    if(tableData.length){
        tableData.forEach(oneRow => {
            let i = 0;
            categoryHeadRow = cTag('tr');
                let categoryTdCol0 = cTag('td', {'data-title':Translate('Categories Name')});
                categoryTdCol0.innerHTML = oneRow[0]||'&nbsp;';

                let categoryTdCol1 = cTag('td', {'data-title':Translate('QTY in Inventory'), align:'right'});
                categoryTdCol1.innerHTML = oneRow[1];

                let categoryTdCol2 = cTag('td', {'data-title':Translate('Cost in Inventory'), align:'right'});
                categoryTdCol2.innerHTML = addCurrency(oneRow[2]);

                let categoryTdCol3 = cTag('td', {'data-title':Translate('QTY in Purchased'), align:'right'});
                categoryTdCol3.innerHTML = oneRow[3];

                let categoryTdCol4 = cTag('td', {'data-title':Translate('Total Cost'), align:'right'});
                categoryTdCol4.innerHTML = addCurrency(oneRow[4]);

                let categoryTdCol5 = cTag('td', {'data-title':Translate('QTY in Sales'), align:'right'});
                categoryTdCol5.innerHTML = oneRow[5];

                let categoryTdCol6 = cTag('td', {'data-title':Translate('Total Sales'), align:'right'});
                categoryTdCol6.innerHTML = addCurrency(oneRow[6]);
            categoryHeadRow.append(categoryTdCol0, categoryTdCol1, categoryTdCol2, categoryTdCol3, categoryTdCol4, categoryTdCol5, categoryTdCol6);
            categoryBody.appendChild(categoryHeadRow);
        });
    }
    else{
        categoryHeadRow = cTag('tr');
            let categoryTdCol7 = cTag('td', {'data-title':Translate('Categories Name'), colspan:7});
            categoryTdCol7.innerHTML = '';
        categoryHeadRow.appendChild(categoryTdCol7);
        categoryBody.appendChild(categoryHeadRow);
    }
}

async function lists(){
    let todayDate, last1Date, thisWMonDate, thisWSunDate, lastWMonDate, lastWSunDate, thisMStartDate, thisMLastDate, lastMStartDate,lastMLastDate;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleName = cTag('div');
            const headerTitle = cTag('h2', {'style': "text-align: start; padding: 5px;"});
            headerTitle.innerHTML = Translate('Dashboard')+' ';
                const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This dashboard is a high level overview of IMEI movements and other general data.')});
            headerTitle.appendChild(infoIcon);
        titleName.appendChild(headerTitle);
    showTableData.appendChild(titleName);

    let now = new Date();
    let tdate = now.getDate();
    let tmonth = now.getMonth() + 1;

    let last1D = new Date(now.getTime() - (24 * 60 * 60 * 1000));
    let l1date = last1D.getDate();
    let l1month = last1D.getMonth() + 1;

    let thisWMon = new Date(now.getTime() - ((now.getDay()-1) * 24 * 60 * 60 * 1000));
    let thisWMondate = thisWMon.getDate();
    let thisWMonmonth = thisWMon.getMonth() + 1;

    let thisWSun = new Date(thisWMon.getTime() + (6 * 24 * 60 * 60 * 1000));
    let thisWSundate = thisWSun.getDate();
    let thisWSunmonth = thisWSun.getMonth() + 1;

    let lastWMon = new Date(thisWMon.getTime() - (7 * 24 * 60 * 60 * 1000));
    let lastWMondate = lastWMon.getDate();
    let lastWMonmonth = lastWMon.getMonth() + 1;

    let lastWSun = new Date(lastWMon.getTime() + (6 * 24 * 60 * 60 * 1000));
    let lastWSundate = lastWSun.getDate();
    let lastWSunmonth = lastWSun.getMonth() + 1;

    let thisMLast = new Date(now.getFullYear(), tmonth, 0);
    let thisMLastdate = thisMLast.getDate();

    let lastMLast = new Date(now.getFullYear(), now.getMonth(), 0);
    let lastMLastdate = lastMLast.getDate();
    let lastMLastmonth = lastMLast.getMonth() + 1;

    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
        todayDate = (tdate<10 ? '0'+tdate : tdate) +'-'+(tmonth<10 ? '0'+tmonth : tmonth)+'-'+now.getFullYear();
        last1Date = (l1date<10 ? '0'+l1date : l1date) +'-'+(l1month<10 ? '0'+l1month : l1month)+'-'+last1D.getFullYear();
        thisWMonDate = (thisWMondate<10 ? '0'+thisWMondate : thisWMondate) +'-'+(thisWMonmonth<10 ? '0'+thisWMonmonth : thisWMonmonth)+'-'+last1D.getFullYear();
        thisWSunDate = (thisWSundate<10 ? '0'+thisWSundate : thisWSundate) +'-'+(thisWSunmonth<10 ? '0'+thisWSunmonth : thisWSunmonth)+'-'+thisWSun.getFullYear();
        lastWMonDate = (lastWMondate<10 ? '0'+lastWMondate : lastWMondate) +'-'+(lastWMonmonth<10 ? '0'+lastWMonmonth : lastWMonmonth)+'-'+lastWMon.getFullYear();
        lastWSunDate = (lastWSundate<10 ? '0'+lastWSundate : lastWSundate) +'-'+(lastWSunmonth<10 ? '0'+lastWSunmonth : lastWSunmonth)+'-'+lastWSun.getFullYear();
        thisMStartDate = '01-'+(tmonth<10 ? '0'+tmonth : tmonth)+'-'+now.getFullYear();
        thisMLastDate = (thisMLastdate<10 ? '0'+thisMLastdate : thisMLastdate) +'-'+(tmonth<10 ? '0'+tmonth : tmonth)+'-'+thisWSun.getFullYear();
        lastMStartDate = '01-'+(lastMLastmonth<10 ? '0'+lastMLastmonth : lastMLastmonth)+'-'+lastMLast.getFullYear();
        lastMLastDate = (lastMLastdate<10 ? '0'+lastMLastdate : lastMLastdate) +'-'+(lastMLastmonth<10 ? '0'+lastMLastmonth : lastMLastmonth)+'-'+lastMLast.getFullYear();
    }
    else{
        todayDate = (tmonth<10 ? '0'+tmonth : tmonth)+'/'+ (tdate<10 ? '0'+tdate : tdate ) +'/'+now.getFullYear();
        last1Date = (l1month<10 ? '0'+l1month : l1month)+'/'+ (l1date<10 ? '0'+l1date : l1date ) +'/'+last1D.getFullYear();
        thisWMonDate = (thisWMonmonth<10 ? '0'+thisWMonmonth : thisWMonmonth)+'/'+ (thisWMondate<10 ? '0'+thisWMondate : thisWMondate ) +'/'+thisWMon.getFullYear();
        thisWSunDate = (thisWSunmonth<10 ? '0'+thisWSunmonth : thisWSunmonth)+'/'+ (thisWSundate<10 ? '0'+thisWSundate : thisWSundate ) +'/'+thisWSun.getFullYear();
        lastWMonDate = (thisWMonmonth<10 ? '0'+thisWMonmonth : thisWMonmonth)+'/'+ (lastWMondate<10 ? '0'+lastWMondate : lastWMondate ) +'/'+lastWMon.getFullYear();
        lastWSunDate = (lastWSunmonth<10 ? '0'+lastWSunmonth : lastWSunmonth)+'/'+ (lastWSundate<10 ? '0'+lastWSundate : lastWSundate ) +'/'+lastWSun.getFullYear();
        thisMStartDate = (tmonth<10 ? '0'+tmonth : tmonth)+'/01/'+now.getFullYear();
        thisMLastDate = (tmonth<10 ? '0'+tmonth : tmonth)+'/'+ (thisMLastdate<10 ? '0'+thisMLastdate : thisMLastdate ) +'/'+thisWSun.getFullYear();
        lastMStartDate = (lastMLastmonth<10 ? '0'+lastMLastmonth : lastMLastmonth)+'/01/'+lastMLast.getFullYear();
        lastMLastDate = (lastMLastmonth<10 ? '0'+lastMLastmonth : lastMLastmonth)+'/'+ (lastMLastdate<10 ? '0'+lastMLastdate : lastMLastdate ) +'/'+lastMLast.getFullYear();
    }

        let tdCol, inputField;
        const buttonListRow = cTag('div', {class: "columnXS12"});
            const buttonListColumn = cTag('div', {class: "flexSpaBetRow"});
                    const queryDiv = cTag('div');
                        const todayButton = cTag('button', {class: "btn defaultButton Today active", 'style': "margin-left: 5px", title: Translate('Today')});
                        todayButton.innerHTML = Translate('Today');
                        todayButton.addEventListener('click', e=>{setDashBoardDate(todayDate+' - '+todayDate, e);});
                    queryDiv.appendChild(todayButton);
                        const yesterdayButton = cTag('button', {class: "btn defaultButton Yesterday", 'style': "margin-left: 5px", title: Translate('Yesterday')});
                        yesterdayButton.innerHTML = Translate('Yesterday');
                        yesterdayButton.addEventListener('click', e=>{setDashBoardDate(last1Date+' - '+last1Date, e);});
                    queryDiv.appendChild(yesterdayButton);
                        const weekButton = cTag('button', {class: "btn defaultButton This_Week", 'style': "margin-left: 5px", title: Translate('This week (Mon-Sun)')});
                        weekButton.innerHTML = Translate('This week (Mon-Sun)');
                        weekButton.addEventListener('click', e=>{setDashBoardDate(thisWMonDate+' - '+thisWSunDate, e);});
                    queryDiv.appendChild(weekButton);
                        const lastWeekButton = cTag('button', {class: "btn defaultButton Last_Week", 'style': "margin-left: 5px", title: Translate('Last Week (Mon-Sun)')});
                        lastWeekButton.innerHTML = Translate('Last Week (Mon-Sun)');
                        lastWeekButton.addEventListener('click', e=>{setDashBoardDate(lastWMonDate+' - '+lastWSunDate, e);});
                    queryDiv.appendChild(lastWeekButton);
                        const monthButton = cTag('button', {class: "btn defaultButton This_Month", 'style': "margin-left: 5px", title: Translate('This Month')});
                        monthButton.innerHTML = Translate('This Month');
                        monthButton.addEventListener('click', e=>{setDashBoardDate(thisMStartDate+' - '+thisMLastDate, e);});
                    queryDiv.appendChild(monthButton);
                        const lastMonthButton = cTag('button', {class: "btn defaultButton Last_Month", 'style': "margin-left: 5px", title: Translate('Last Month')});
                        lastMonthButton.innerHTML = Translate('Last Month');
                        lastMonthButton.addEventListener('click', e=>{setDashBoardDate(lastMStartDate+' - '+lastMLastDate, e);});
                    queryDiv.appendChild(lastMonthButton);
                buttonListColumn.appendChild(queryDiv);

                    const dateRangeField = cTag('div', {class: "input-group daterangeContainer", 'style': "margin-right: 5px; margin-left: 5px;"});
                        inputField = cTag('input', {'type': "hidden", name: "dashboaddate", id: "dashboaddate", 'value': ""});
                    dateRangeField.appendChild(inputField);
                        inputField = cTag('input', {'required': "", 'maxlength': 23, 'minlength': 23, 'type': "text", class: "form-control", 'style': "padding-left: 35px;", name: "date_range", id: "date_range", 'value': todayDate+' - '+todayDate});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                        let searchSpan = cTag('span', { class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Search by Date')});
                        searchSpan.addEventListener('click', loadData_Dashboard_Data);
                            const searchIcon = cTag('i', {class: "fa fa-search"});
                        searchSpan.appendChild(searchIcon);
                    dateRangeField.appendChild(searchSpan);
                buttonListColumn.appendChild(dateRangeField);
        buttonListRow.appendChild(buttonListColumn);
    showTableData.appendChild(buttonListRow);

        const divSearch = cTag('div', { id: "Searchresult"});
            const dashBoardContentRow = cTag('div', {class: "flexSpaBetRow", 'style': "padding-top: 20px;"});
                const salesColumn = cTag('div', {class: "columnSM6 columnMD4", 'style': "margin: 0;"});
                    const salesWidget = cTag('div', {class: " cardContainer", 'style': "margin-bottom: 10px;"});
                        const salesWidgetHeader = cTag('div', {class: "flex cardHeader", 'style': "align-items: center;"});
                            const cloudIcon = cTag('i', {class: "fa fa-cloud-upload", 'style': "padding: 10px;"}); 
                        salesWidgetHeader.appendChild(cloudIcon);
                            const salesHeader = cTag('h3', {'style': "font-weight: bold; font-size: 15px;"});
                            salesHeader.innerHTML =  " "+Translate('SALES').toUpperCase();
                        salesWidgetHeader.appendChild(salesHeader);
                    salesWidget.appendChild(salesWidgetHeader);
                        const salesContent = cTag('div', {class: "cardContent "});
                            let salesInfo = cTag('div', {id: "salesInfo"});
                                let salesInfoDiv = cTag('div', {'class': "flex"});
                            salesInfo.appendChild(salesInfoDiv);
                                let totalSalesDiv = cTag('div', {'class': "flex"});
                            salesInfo.appendChild(totalSalesDiv);
                                let emptyDiv = cTag('div');
                                    let emptyLabel = cTag('label');
                                emptyDiv.appendChild(emptyLabel);
                            salesInfo.appendChild(emptyDiv);
                        salesContent.appendChild(salesInfo);
                    salesWidget.appendChild(salesContent);
                salesColumn.appendChild(salesWidget);
            dashBoardContentRow.appendChild(salesColumn);

                const repairColumn = cTag('div', {class: "columnSM6 columnMD4", 'style': "margin: 0;"});
                    const repairWidget = cTag('div', {class: " cardContainer", 'style': "margin-bottom: 10px;"});
                        const repairWidgetHeader = cTag('div', {class: "flex cardHeader", 'style': "align-items: center;"}); 
                            const wrenchIcon = cTag('i', {class: "fa fa-wrench", 'style': "padding: 10px;"}); 
                        repairWidgetHeader.appendChild(wrenchIcon);
                            const repairHeader = cTag('h3', {'style': "font-weight: bold; font-size: 15px;"});
                            repairHeader.innerHTML =  ' '+Translate('Repairs').toUpperCase();
                        repairWidgetHeader.appendChild(repairHeader);
                    repairWidget.appendChild(repairWidgetHeader);

                        const repairContent = cTag('div', {class: "cardContent ", id: "customer_information"});
                            let repairInfoDiv = cTag('div', {id: "repairsInfo"});
                                let repairInfoTitle = cTag('div', {class: "flex"});
                            repairInfoDiv.appendChild(repairInfoTitle);
                                let addedDiv = cTag('div', {class: "flex"});
                            repairInfoDiv.appendChild(addedDiv);
                                let invoiceDiv = cTag('div', {class: "flex"});
                            repairInfoDiv.appendChild(invoiceDiv);
                        repairContent.appendChild(repairInfoDiv);
                    repairWidget.appendChild(repairContent);
                repairColumn.appendChild(repairWidget);
            dashBoardContentRow.appendChild(repairColumn);

                const customerColumn = cTag('div', {class: "columnSM6 columnMD4", 'style': "margin: 0;"});
                    const customerWidget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
                        const customerWidgetHeader = cTag('div', {class: "flex cardHeader", 'style': "align-items: center;"});
                            const userIcon = cTag('i', {class: "fa fa-users", 'style': "padding: 10px;"});
                        customerWidgetHeader.appendChild(userIcon);
                            const customerHeader = cTag('h3', {'style': "font-weight: bold; font-size: 15px;"});
                            customerHeader.innerHTML =  ' '+Translate('Customers').toUpperCase();
                        customerWidgetHeader.appendChild(customerHeader);
                    customerWidget.appendChild(customerWidgetHeader);
                        const customerContent = cTag('div', {class: "cardContent ", id: "customer_information"});
                            let customerInfo = cTag('div', {id: "customersInfo"});
                                let addDiv = cTag('div', {class: "flex"});
                            customerInfo.appendChild(addDiv);
                                let purchaseDiv = cTag('div', {class: "flex"});
                            customerInfo.appendChild(purchaseDiv);
                                let freeDiv = cTag('div');
                                    let nullLabel = cTag('label');
                                freeDiv.appendChild(nullLabel);
                            customerInfo.appendChild(freeDiv);
                        customerContent.appendChild(customerInfo);
                    customerWidget.appendChild(customerContent);
                customerColumn.appendChild(customerWidget);
            dashBoardContentRow.appendChild(customerColumn);
        divSearch.appendChild(dashBoardContentRow);
        
            let paymentHeadRow;
            const paymentContent = cTag('div', {class: "image_content", 'style': "padding-top: 20px; text-align: left;"});
                const paymentHeader = cTag('h3', { 'style': "margin: 0; padding-left: 10px;"});
                paymentHeader.innerHTML = Translate('Payments');
            paymentContent.appendChild(paymentHeader);
                const paymentTableRow = cTag('div', {class: "flex"});
                    const paymentTableColumn = cTag('div', {class: "columnXS12"}); 
                        const paymentTable = cTag('table', {class: "columnMD12 table-bordered table-striped table-condensed cf listing"}); 
                            const paymentHead = cTag('thead', {class: "cf"});
                                paymentHeadRow = cTag('tr');
                                    const thCol0 = cTag('th'); 
                                    thCol0.innerHTML = Translate('Payment Type');
                                paymentHeadRow.appendChild(thCol0);
                                    const thCol1 = cTag('th', {'width': "30%", 'style': "text-align: right;"});
                                    thCol1.innerHTML = Translate('Total');
                                paymentHeadRow.appendChild(thCol1);
                            paymentHead.appendChild(paymentHeadRow);
                        paymentTable.appendChild(paymentHead);

                            const paymentBody = cTag('tbody', {id:'tableRows1'});
                                paymentHeadRow = cTag('tr');
                                    tdCol = cTag('td', {'colspan': 2});
                                    tdCol.innerHTML = '';
                                paymentHeadRow.appendChild(tdCol);
                            paymentBody.appendChild(paymentHeadRow);
                        paymentTable.appendChild(paymentBody);
                    paymentTableColumn.appendChild(paymentTable);
                paymentTableRow.appendChild(paymentTableColumn);
            paymentContent.appendChild(paymentTableRow);
        divSearch.appendChild(paymentContent);

            const categoryContent = cTag('div', {class: "image_content", 'style': "padding-top: 20px; text-align: left;"});
                const categoryContentHeader = cTag('h3', {'style': "margin: 0; padding-left: 10px;"});
                categoryContentHeader.innerHTML = Translate('Categories');
            categoryContent.appendChild(categoryContentHeader);
            const categoryTableColumn = cTag('div', {class: "columnXS12 columnSM12"}); 
                const categoryNoMore = cTag('div', {id: "no-more-tables"}); 
                    const categoryTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing"}); 
                        const categoryHead = cTag('thead', {class: "cf"});
                            const categoryHeadRow = cTag('tr');
                                const cThCol0 = cTag('th', {'align': "left"}); 
                                cThCol0.innerHTML = Translate('Categories Name');

                                const cThCol1 = cTag('th', {'width': "12%"});  
                                cThCol1.innerHTML = Translate('QTY in Inventory');

                                const cThCol2 = cTag('th', {'width': "12%"});  
                                cThCol2.innerHTML = Translate('Cost in Inventory');

                                const cThCol3 = cTag('th', {'width': "12%"});  
                                cThCol3.innerHTML = Translate('QTY in Purchased');

                                const cThCol4 = cTag('th', {'width': "12%"});  
                                cThCol4.innerHTML = Translate('Total Cost');

                                const cThCol5 = cTag('th', {'width': "12%"});  
                                cThCol5.innerHTML = Translate('QTY in Sales');

                                const cThCol6 = cTag('th', {'width': "12%"});  
                                cThCol6.innerHTML = Translate('Total Sales');
                            categoryHeadRow.append(cThCol0, cThCol1, cThCol2, cThCol3, cThCol4, cThCol5, cThCol6);
                        categoryHead.appendChild(categoryHeadRow);
                    categoryTable.appendChild(categoryHead);
                        const categoryBody = cTag('tbody', {id: "tableRows2"});
                    categoryTable.appendChild(categoryBody);
                categoryNoMore.appendChild(categoryTable);
            categoryTableColumn.appendChild(categoryNoMore);
        categoryContent.appendChild(categoryTableColumn);
    divSearch.appendChild(categoryContent);
    buttonListRow.appendChild(divSearch); 
    showTableData.appendChild(buttonListRow);

        const lowInventoryContent = cTag('div', {class: "columnSM12"}); 
            const lowInventoryDiv = cTag('div', {class: "image_content", 'style': "padding-top: 20px; text-align: left;"});
                const lowInventoryHeader = cTag('h3', {'style': "margin: 0; padding-left: 10px;"});
                lowInventoryHeader.innerHTML = Translate('Low Inventory');
            lowInventoryDiv.appendChild(lowInventoryHeader);
                const lowInventoryTableRow = cTag('div', {class: "flex"});
                    const lowInventoryTableColumn = cTag('div', {class: "columnXS12"});
                        const lowInventoryTable = cTag('table', {class: "columnMD12 table-bordered table-striped table-condensed cf listing"}); 
                            const lowInventoryHead = cTag('thead', {class: "cf"});
                                const columnNames = lowInventoryFieldAttributes.map(colObj=>(colObj.datatitle));
                                const lowInventoryHeadRow = cTag('tr');
                                    const lowThCol0 = cTag('th');
                                    lowThCol0.innerHTML=columnNames[0];

                                    const lowThCol1 = cTag('th', {'width': "15%", 'style': "text-align: right;"}); 
                                    lowThCol1.innerHTML=columnNames[1];

                                    const lowThCol2 = cTag('th', {'width': "15%", 'style': "text-align: right;"});
                                    lowThCol2.innerHTML=columnNames[2];
                                lowInventoryHeadRow.append(lowThCol0, lowThCol1, lowThCol2);
                            lowInventoryHead.appendChild(lowInventoryHeadRow);
                        lowInventoryTable.appendChild(lowInventoryHead);
                            const lowInventoryBody = cTag('tbody', {id: "tableRows3"});
                        lowInventoryTable.appendChild(lowInventoryBody);
                    lowInventoryTableColumn.appendChild(lowInventoryTable);
                lowInventoryTableRow.appendChild(lowInventoryTableColumn);
            lowInventoryDiv.appendChild(lowInventoryTableRow);

                const moreProductColumn = cTag('div', {class: "columnSM12"});
                    let pTag = cTag('P',{id:'morePCount'});
                moreProductColumn.appendChild(pTag);
            lowInventoryDiv.appendChild(moreProductColumn);
        lowInventoryContent.appendChild(lowInventoryDiv);
    showTableData.appendChild(lowInventoryContent);
    AJ_lists_MoreInfo();
}

async function AJ_lists_MoreInfo(){
	const jsonData = {};
    jsonData['date_range']	= document.getElementById('date_range').value;
    const url = '/'+segment1+'/AJ_lists_MoreInfo';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const tbody = document.getElementById("tableRows3");
        tbody.innerHTML = '';
        let tableData = data.lowInvData;
        //======Create TBody TR Column======//
        let tdCol;
        if(tableData.length){     
            const columnNames = lowInventoryFieldAttributes.map(colObj=>(colObj.datatitle));           
            tableData.forEach(oneRow => {
                let i = 0;
                let tr = cTag('tr');
                    tdCol = cTag('td', {'data-title':columnNames[0]});
                    tdCol.innerHTML = oneRow[0];
                tr.appendChild(tdCol);
                    tdCol = cTag('td', {'data-title':columnNames[1], align:'right'});
                    tdCol.innerHTML = oneRow[1];
                tr.appendChild(tdCol);
                    tdCol = cTag('td', {'data-title':columnNames[2], align:'right'});
                    tdCol.innerHTML = oneRow[2];
                tr.appendChild(tdCol);
                tbody.appendChild(tr);
            });
        }
        else{
            let tr = cTag('tr');
                let td = cTag('td',{'class':'errormsg'});
                td.innerHTML = Translate('There is no Low Inventory');
            tr.append(td);
            tbody.appendChild(tr);
        }
        let morePCount = data.totalcount-tableData.length;
        if(morePCount>0) document.getElementById("morePCount").innerHTML = morePCount+' '+Translate('more products are below minimum stock level.');
        loadDashBoardData(data);
    }
}

document.addEventListener('DOMContentLoaded', async()=>{    
    let layoutFunctions = {lists};
    layoutFunctions[segment2]();
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});