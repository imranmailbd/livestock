import {
    cTag, Translate, tooltip, storeSessionData, addCurrency, DBDateToViewDate, setOptions, checkAndSetSessionData, 
    getDeviceOperatingSystem, fetchData, daterange_picker_dialog, DBDateRangeToViewDate, AJautoComplete, changeToDBdate_OnSubmit
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

async function AJ_lists_MoreInfo(){
	const jsonData = {};
    
    const url = '/'+segment1+'/AJ_lists_MoreInfo'
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let select, option;
        select = document.querySelector("#status");
        select.innerHTML = '';
            option = cTag('option', {value:''});
            option.innerHTML = Translate('All Statuses');
        select.appendChild(option);
        setOptions(select,data.statusOpt, 0, 1);

        select = document.querySelector("#problem");
        select.innerHTML = '';
            option = cTag('option', {value:''});
            option.innerHTML = Translate('Select Problem');
        select.appendChild(option);
        setOptions(select,data.problemOpt, 0, 1);

        select = document.querySelector("#status1");
        select.innerHTML = '';
            option = cTag('option', {value:''});
            option.innerHTML = Translate('All Statuses');
        select.appendChild(option);
        setOptions(select,data.statusOpt, 0, 1);
    }
}

function lists(){
    let inputField, dateRangeField, generateReportColumn;    

    const showTableData = document.getElementById('viewPageInfo');
    showTableData.innerHTML = '';
        const titleRow = cTag('div');
            const headerTitle = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
            headerTitle.innerHTML = Translate('Repairs Reports')+' ';
                const infoIcon = cTag('i',{ 'class':'fa fa-info-circle', 'style': "font-size: 16px;", 'data-toggle':'tooltip','data-placement':'bottom','title':'','data-original-title':Translate('This is the standard sales reporting page including many standard reports.') });
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
    showTableData.appendChild(titleRow);

        const repairsReportContainer = cTag('div',{ 'class': 'flex' });
            const repairsReportColumn = cTag('div',{ 'class':'columnSM12' });
                inputField = cTag('input',{ type:'hidden',id:'twoSegments',value:'Repairs_reports-lists' });
            repairsReportColumn.appendChild(inputField);
                inputField = cTag('input',{ type:'hidden',id:'pageURI',value:`${segment1}/${segment2}`});
            repairsReportColumn.appendChild(inputField);
                
				let padding0 = 'padding:15px 0';
				if(OS =='unknown'){padding0 = '';}
                const divCallOut = cTag('div',{ 'class':'innerContainer','style':'background:#FFF;'+padding0 });
                    const repairsReportTop = cTag('div',{ 'class':'columnSM12' });
                        const reportTitleRow = cTag('div',{ 'class':'flexSpaBetRow borderbottom', 'style': "background-color: #EEEEEE;" });
                            const reportTypeColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3', 'style': "text-align: right;" });
                                const reportTypeTitle = cTag('strong');
                                reportTypeTitle.innerHTML = Translate('Report Type');
                            reportTypeColumn.appendChild(reportTypeTitle);
                        reportTitleRow.appendChild(reportTypeColumn);
                            const fromToDateColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3', 'style': "text-align: center;" });
                                const fromToDateTitle = cTag('strong');
                                fromToDateTitle.innerHTML = Translate('From Date - To Date');
                            fromToDateColumn.appendChild(fromToDateTitle);
                        reportTitleRow.appendChild(fromToDateColumn);
                            const optionalColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3', 'style': "text-align: center;" });
                                const optionalTitle = cTag('strong');
                                optionalTitle.innerHTML = Translate('Optional Keyword');
                            optionalColumn.appendChild(optionalTitle);
                        reportTitleRow.appendChild(optionalColumn);
                            const reportColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                const reportTitle = cTag('strong');
                                reportTitle.innerHTML = Translate('Get Report');
                            reportColumn.appendChild(reportTitle);
                        reportTitleRow.appendChild(reportColumn);
                    repairsReportTop.appendChild(reportTitleRow);

                        // Repairs by status
                        const repairStatusForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('repairs_by_status',true), 'enctype':'text/plain','method':'get','name':'frmrepairs_by_status','id':'frmsales_by_date','action':'/Repairs_reports/repairs_by_status/' });
                            const repairStatusRow = cTag('div',{ 'class':'flex borderbottom', 'style': "align-items: center; padding: 15px 0px;" });
                                const repairStatusColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3', 'style': "text-align: right;" });
                                    const repairStatusLabel = cTag('label',{'for':'repairs_by_status' });
                                    repairStatusLabel.innerHTML = Translate('Repairs by status');
                                repairStatusColumn.appendChild(repairStatusLabel); 
                            repairStatusRow.appendChild(repairStatusColumn);
                                dateRangeField = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3 daterangeContainer' });
                                    inputField = cTag('input',{ 'required':'','minlength':23,'maxlength':23,'type':'text','class':'form-control sales_date checkSalesDate', 'style': "padding-left: 35px;", 'name':'sales_date','id':'repairs_by_status'});
                                    daterange_picker_dialog(inputField);
                                dateRangeField.appendChild(inputField); 
                                
                            repairStatusRow.appendChild(dateRangeField);
                                const allStatusColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    const selectStatus = cTag('select',{ id:"status",'name':'status','class':'form-control' });
                                allStatusColumn.appendChild(selectStatus);
                                    inputField = cTag('input',{ 'type':'hidden','name':'showing_type','value':'Summary' });
                                allStatusColumn.appendChild(inputField);
                            repairStatusRow.appendChild(allStatusColumn);
                                generateReportColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    const divReport = cTag('div');
                                        inputField = cTag('input',{ 'type':'submit','value':Translate('Generate Report'),'class':'btn defaultButton','title':Translate('Generate Report') });
                                    divReport.appendChild(inputField);
                                generateReportColumn.appendChild(divReport);
                            repairStatusRow.appendChild(generateReportColumn);
                        repairStatusForm.appendChild(repairStatusRow);
                    repairsReportTop.appendChild(repairStatusForm);

                        //Repairs by problem
                        const repairProblemForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('repairs_by_problem',true), 'enctype':'text/plain','method':'get','name':'frmrepairs_by_problem','id':'frmsales_by_date','action':'/Repairs_reports/repairs_by_problem/' });
                            const repairProblemRow = cTag('div',{ 'class':'flex borderbottom', 'style': "align-items: center; padding: 15px 0px;" });
                                const repairProblemColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3', 'style': "text-align: right;" });
                                    const repairProblemLabel = cTag('label',{'for':'repairs_by_problem' });
                                    repairProblemLabel.innerHTML =Translate('Repairs by problems');
                                repairProblemColumn.appendChild(repairProblemLabel); 
                            repairProblemRow.appendChild(repairProblemColumn);

                                dateRangeField = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3 daterangeContainer' });
                                    inputField = cTag('input',{ 'required':'','minlength':23,'maxlength':23,'type':'text','class':'form-control sales_date', 'style': "padding-left: 35px;", 'name':'sales_date','id':'repairs_by_problem','value':'' });
                                    daterange_picker_dialog(inputField);
                                dateRangeField.appendChild(inputField); 
                            repairProblemRow.appendChild(dateRangeField);
                                const problemColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    const selectProblem = cTag('select',{ 'id':'problem','name':'problem','class':'form-control' });
                                problemColumn.appendChild(selectProblem);
                                    inputField = cTag('input',{ 'type':'hidden','name':'showing_type','value':'Summary' });
                                problemColumn.appendChild(inputField);
                            repairProblemRow.appendChild(problemColumn); 
                                generateReportColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    inputField = cTag('input',{ 'type':'submit','value':Translate('Generate Report'),'class':'btn defaultButton','title':Translate('Generate Report') });
                                generateReportColumn.appendChild(inputField);
                            repairProblemRow.appendChild(generateReportColumn);
                        repairProblemForm.appendChild(repairProblemRow);
                    repairsReportTop.appendChild(repairProblemForm);

                        //Technician
                        const technicianForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_Technician',true), 'enctype':'text/plain','method':'get','name':'frmsales_by_Technician','action':'/Repairs_reports/sales_by_Technician/' });
                            const technicianRow = cTag('div',{ 'class':'flex borderbottom', 'style': "align-items: center; padding: 15px 0px;" });
                                const technicianColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3', 'style': "text-align: right;" });
                                    const technicianLabel = cTag('label',{'for':'sales_by_Technician' });
                                    technicianLabel.innerHTML = Translate('Sales by Technician');
                                technicianColumn.appendChild(technicianLabel);
                            technicianRow.appendChild(technicianColumn);
                                dateRangeField = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3 daterangeContainer' });
                                    inputField = cTag('input',{ 'required':'','minlength':23,'maxlength':23,'type':'text','class':'form-control sales_date', 'style': "padding-left: 35px;", 'name':'sales_date','id':'sales_by_Technician','value':'' });
                                    daterange_picker_dialog(inputField);
                                dateRangeField.appendChild(inputField); 
                            technicianRow.appendChild(dateRangeField);

                                const searchTechnician = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    inputField = cTag('input',{ 'maxlength':50,'type':'text','class':'form-control ui-autocomplete-input','name':'assign_to','id':'assign_to','value':'','placeholder':Translate('Technician'),'autocomplete':'off' });
                                searchTechnician.appendChild(inputField);
                                    inputField = cTag('input',{ 'type':'hidden','name':'showing_type','value':'Summary' });
                                searchTechnician.appendChild(inputField);
                            technicianRow.appendChild(searchTechnician);

                                generateReportColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    inputField = cTag('input',{ 'type':'submit','value':Translate('Generate Report'),'class':'btn defaultButton','title':Translate('Generate Report') });
                                generateReportColumn.appendChild(inputField);
                            technicianRow.appendChild(generateReportColumn); 
                        technicianForm.appendChild(technicianRow);
                    repairsReportTop.appendChild(technicianForm);

                        //RepairTickets
                        const repairTicketForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('repair_Tickets_Created',true), 'enctype':'text/plain','method':'get','name':'frmRepairTicketsCreated','action':'/Repairs_reports/repair_Tickets_Created/' });
                            const repairTicketRow = cTag('div',{ 'class':'flex borderbottom', 'style': "align-items: center; padding: 15px 0px;" });
                                const repairTicketColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3', 'style': "text-align: right;" });
                                    const repairTicketLabel = cTag('label',{'for':'repair_Tickets_Created' });
                                    repairTicketLabel.innerHTML = Translate('Repair Tickets Created');
                                repairTicketColumn.appendChild(repairTicketLabel);
                            repairTicketRow.appendChild(repairTicketColumn);

                                dateRangeField = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3 daterangeContainer' });
                                    inputField = cTag('input',{ 'required':'','minlength':23,'maxlength':23,'type':'text','class':'form-control sales_date', 'style': "padding-left: 35px;",'name':'sales_date','id':'repair_Tickets_Created','value':'' });
                                    daterange_picker_dialog(inputField);
                                dateRangeField.appendChild(inputField); 
                            repairTicketRow.appendChild(dateRangeField);

                                const allStatusColumn2 = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    const selectStatus2 = cTag('select',{ 'id':'status1','name':'status','class':'form-control' });
                                allStatusColumn2.appendChild(selectStatus2);
                                    inputField = cTag('input',{ 'type':'hidden','name':'showing_type','value':'Summary' });
                                allStatusColumn2.appendChild(inputField);
                            repairTicketRow.appendChild(allStatusColumn2);
                                generateReportColumn = cTag('div',{ 'class':'columnXS6 columnSM4 columnMD3' });
                                    inputField = cTag('input',{ 'type':'submit','value':Translate('Generate Report'),'class':'btn defaultButton','title':Translate('Generate Report') });
                                generateReportColumn.appendChild(inputField);
                            repairTicketRow.appendChild(generateReportColumn); 
                        repairTicketForm.appendChild(repairTicketRow); 
                    repairsReportTop.appendChild(repairTicketForm);
                divCallOut.appendChild(repairsReportTop);
            repairsReportColumn.appendChild(divCallOut);
        repairsReportContainer.appendChild(repairsReportColumn);
    showTableData.appendChild(repairsReportContainer);
    AJautoComplete('assign_to');
    AJ_lists_MoreInfo();
}

async function repairs_by_statusData(){
    const sales_date = document.getElementById("sales_date").value;
	const showing_type = document.getElementById("showing_type").value;
	const status = document.getElementById("status").value;
	const jsonData = {};
	jsonData['sales_date'] = sales_date;
    jsonData['showing_type'] = showing_type;
    jsonData['status'] = status;
    
    const url = '/Repairs_reports/repairs_by_statusData';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
            
        let statusHeadRow, tdCol;
        const searchResult = document.querySelector('#Searchresult');
        searchResult.innerHTML = '';
            const noMoreTables = cTag('div', {id: "statusTable"});
                const totalButton = cTag('div',{class:'flexEndRow', 'style': "margin: 5px 0;", id:"filterby"})
                    const grandDuesvalue_button = cTag('button', {class:"btn reportButton" });
                totalButton.appendChild(grandDuesvalue_button)
            noMoreTables.appendChild(totalButton);
                const repairStatusTable = cTag('table', {class:" bgnone table-bordered table-striped table-condensed cf listing"});
                    const repairStatusHead = cTag('thead', {class:"cf"});
                        statusHeadRow = cTag('tr');
                            const titleColTitles = [Translate('Status'), Translate('Repair Total'), Translate('Amount Paid'), Translate('Amount Dues')];
                            const thCol0 = cTag('th');
                            thCol0.append(Translate('Status'));
                            const thCol1 = cTag('th', {'style': "text-align: right;", width:"25%"});
                            thCol1.append(Translate('Repair Total'));
                            const thCol2 = cTag('th', {'style': "text-align: right;", width:"25%"});
                            thCol2.append(Translate('Amount Paid'));
                            const thCol3 = cTag('th', {'style': "text-align: right;", width:"25%"});
                            thCol3.append(Translate('Amount Dues'));
                        statusHeadRow.append(thCol0,thCol1,thCol2,thCol3);
                    repairStatusHead.appendChild(statusHeadRow);
                repairStatusTable.appendChild(repairStatusHead);
                    const repairStatusBody = cTag('tbody');
                    //============Dynamic Data Loop Here===========//
                    const tableData = data.tableData;
                    let grandDuesvalue = 0;
                    if(tableData.length>0){
                        tableData.forEach(oneRowObj=>{
                            const status = oneRowObj.status;
                            const statusDetails = oneRowObj.statusDetails;
                            const boldclass = oneRowObj.boldclass;
                            const repairTotal = oneRowObj.statusDuesvalues[0];
                            const amountPaid = oneRowObj.statusDuesvalues[1];
                            const statusDuesvalue = repairTotal - amountPaid;
                            grandDuesvalue += statusDuesvalue;

                            statusHeadRow = cTag('tr');
                                tdCol = cTag('td', {'data-title': Translate('Status'), align:'left'});
                                if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;');}
                                tdCol.innerHTML = status;
                            statusHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Repair Total'), align:'right'});
                                if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;');}
                                tdCol.innerHTML = addCurrency(repairTotal);
                            statusHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Amount Paid'), align:'right'});
                                if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;');}
                                tdCol.innerHTML = addCurrency(amountPaid);
                            statusHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Status Dues'), align:'right'});
                                if(boldclass !==''){tdCol.style.fontWeight = 'bold';}
                                tdCol.innerHTML = addCurrency(statusDuesvalue);
                            statusHeadRow.appendChild(tdCol);
                            repairStatusBody.appendChild(statusHeadRow);

                            if(statusDetails.length>0){
                                statusDetails.forEach(oneRow=>{
                                    const ticket_no = oneRow[0];
                                    const repairs_id = oneRow[1];
                                    const customerName = oneRow[2];
                                    const customer_id = oneRow[3];
                                    const repairTotalDetails = oneRow[5][0];
                                    const amountPaidDetails = oneRow[5][1];
                                    const statusDuesvalueDetails = repairTotalDetails - amountPaidDetails;

                                    statusHeadRow = cTag('tr');
                                        tdCol = cTag('td', {'data-title': Translate('Sale Date'), align:'left'});
                                        tdCol.append('T'+ticket_no);                                                    
                                            const repairsLink = cTag('a', {href:'/Repairs/edit/'+repairs_id, 'style': "color: #009; text-decoration: underline;", title:Translate('View Repair Details')});
                                            repairsLink.appendChild(cTag('i', {'class': 'fa fa-link'}));
                                        tdCol.appendChild(repairsLink);
                                        tdCol.append('\u2003'+customerName);
                                            const customerLink = cTag('a', {href:'/Customers/view/'+customer_id, 'style': "color: #009; text-decoration: underline;", title:Translate('View Customer Details')});
                                            customerLink.appendChild(cTag('i', {'class': 'fa fa-link'}));
                                        tdCol.appendChild(customerLink);
                                    statusHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': Translate('Repair Total'), align:'right'});
                                        tdCol.innerHTML = addCurrency(repairTotalDetails);
                                    statusHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': Translate('Amount Paid'), align:'right'});
                                        tdCol.innerHTML = addCurrency(amountPaidDetails);
                                    statusHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': Translate('Total'), align:'right'});
                                        tdCol.innerHTML = addCurrency(statusDuesvalueDetails);
                                    statusHeadRow.appendChild(tdCol);
                                    repairStatusBody.appendChild(statusHeadRow);
                                });
                            }
                        });
                    }
                    else{
                        statusHeadRow = cTag('tr');
                            tdCol = cTag('td',{colspan:"4"});
                            tdCol.innerHTML = '';
                        statusHeadRow.appendChild(tdCol);
                        repairStatusBody.appendChild(statusHeadRow);
                    }
                    grandDuesvalue_button.append(Translate('Total')+': '+ addCurrency(grandDuesvalue));
                repairStatusTable.appendChild(repairStatusBody);
            noMoreTables.appendChild(repairStatusTable);
        searchResult.appendChild(noMoreTables);
    }
}

async function AJ_repairs_by_status_MoreInfo(){
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const statusValue = params.get("status");
    
    let list_filters;
    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}
   
	const jsonData = {};
    
    const url = '/'+segment1+'/AJ_repairs_by_status_MoreInfo'
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        const select = document.querySelector("#status");
        select.innerHTML = '';
            const option = cTag('option', {value:''});
            option.innerHTML = Translate('All');
        select.appendChild(option);
        setOptions(select,data.statusOpt, 0, 1);
        checkAndSetSessionData('status', statusValue, list_filters);

        repairs_by_statusData();
    }
}

function repairs_by_status(){
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const showing_type = params.get("showing_type");
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));

    let list_filters, inputField;
    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Repairs by status')+' ';
                headerTitle.append(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Repairs_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Repairs_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        const repairStatusColumn = cTag('div', {class:'columnSM12'});
            inputField = cTag('input', {'type': 'hidden', id: 'twoSegments', 'value': 'Repairs_reports-repairs_by_status'});
        repairStatusColumn.appendChild(inputField);
            inputField = cTag('input', {'type': 'hidden', id: 'pageURI', 'value': 'Repairs_reports/repairs_by_status'});
        repairStatusColumn.appendChild(inputField);
            const divCallOut = cTag('div', {'style': 'background:#FFF;'});
                const repairStatusRow = cTag('div', {class:'flexSpaBetRow'});
                    const viewColumn = cTag('div', {class:'columnXS6 columnSM4'});
                        const viewInGroup = cTag('div', {class:'input-group'});
                            const viewLabel = cTag('label', {class:'input-group-addon cursor', 'for': 'showing_type'});
                            viewLabel.innerHTML = Translate('View');
                        viewInGroup.appendChild(viewLabel);
                            const selectShowingType = cTag('select', {name: 'showing_type', id: 'showing_type', class: 'form-control'});
                            selectShowingType.addEventListener('change', repairs_by_statusData);
                                const summaryOption = cTag('option', {'value': 'Summary'});
                                summaryOption.innerHTML = Translate('Summary');
                            selectShowingType.appendChild(summaryOption);
                                const detailOption = cTag('option', {'value': 'Details'});
                                detailOption.innerHTML = Translate('Detailed Summary');
                            selectShowingType.appendChild(detailOption);
                        viewInGroup.appendChild(selectShowingType);
                    viewColumn.appendChild(viewInGroup);
                repairStatusRow.appendChild(viewColumn);
                    const dateRangeField = cTag('div', {class:'columnXS6 columnSM4 daterangeContainer'});
                        inputField = cTag('input', {minlength: 23, 'maxlength': 23, 'type': 'text', class: 'form-control search sales_date', 'style': "padding-left: 35px;", name: 'sales_date', id: 'sales_date', value: sales_date});
                        daterange_picker_dialog(inputField,{submit:repairs_by_statusData});
                    dateRangeField.appendChild(inputField);
                repairStatusRow.appendChild(dateRangeField);
            divCallOut.appendChild(repairStatusRow);
        repairStatusColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(repairStatusColumn);
                    const statusColumn = cTag('div', {class:'columnXS12 columnSM4'});
                        const statusInGroup = cTag('div', {class:'input-group'});
                            const statusLabel = cTag('label', {for:'status', class: 'input-group-addon cursor'});
                            statusLabel.innerHTML = Translate('Status');
                        statusInGroup.appendChild(statusLabel);
                            const selectStatus = cTag('select', {id: 'status', name: 'status', class: 'form-control'});
                            selectStatus.addEventListener('change', repairs_by_statusData);
                        statusInGroup.appendChild(selectStatus);
                            const searchSpan = cTag('span', {class:'input-group-addon cursor', 'data-toggle': 'tooltip', 'data-placement': 'bottom', title: '', 'data-original-title': Translate('Date wise Search')});
                            searchSpan.addEventListener('click', repairs_by_statusData);
                                const searchIcon = cTag('i', {class:'fa fa-search'});
                            searchSpan.appendChild(searchIcon);
                        statusInGroup.appendChild(searchSpan);
                    statusColumn.appendChild(statusInGroup);
                repairStatusRow.appendChild(statusColumn);
            divCallOut.appendChild(repairStatusRow);
        repairStatusColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(repairStatusColumn);
                const searchResultColumn = cTag('div',{ class:"columnXS12", 'style': "margin: 0;"});
                    const searchResult = cTag('div',{id:"Searchresult"});
                    searchResult.append(' ');
                searchResultColumn.appendChild(searchResult);
            divCallOut.appendChild(searchResultColumn);       
        repairStatusColumn.append(divCallOut);
    viewPageInfo.appendChild(repairStatusColumn);
    
    //=======sessionStorage =========//
    if (sessionStorage.getItem('list_filters') !== null) {
        list_filters = JSON.parse(sessionStorage.getItem('list_filters'));
    }
    else{list_filters = {};}
    
    checkAndSetSessionData('showing_type', showing_type, list_filters);

    if(list_filters.hasOwnProperty("sales_date")){
        sales_date = list_filters.sales_date;
    }
    document.getElementById("sales_date").value = sales_date;

    AJ_repairs_by_status_MoreInfo();
}

async function repairs_by_problemData(){
    const sales_date = document.getElementById("sales_date").value;
	const showing_type = document.getElementById("showing_type").value;
	const sproblem = document.getElementById("problem").value;
	const jsonData = {};
	jsonData['sales_date'] = sales_date;
    jsonData['showing_type'] = showing_type;
    jsonData['problem'] = sproblem;
    
    const url = '/Repairs_reports/repairs_by_problemData';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
            
        let repairProblemHeadRow, tdCol;
        const searchResult = document.querySelector('#Searchresult');
        searchResult.innerHTML = '';
            const noMoreTables = cTag('div', {id: "statusTable"});
                const buttonName = cTag('div',{class:'flexEndRow', 'style': "margin: 5px 0;", id:"filterby"})
                    const grandDuesvalue_button = cTag('button', {class:"btn reportButton"});
                buttonName.appendChild(grandDuesvalue_button)
            noMoreTables.appendChild(buttonName);
                const repairProblemTable = cTag('table', {class:" bgnone table-bordered table-striped table-condensed cf listing"});
                    const repairProblemHead = cTag('thead', {class:"cf"});
                        repairProblemHeadRow = cTag('tr');
                            const thCol0 = cTag('th');
                            thCol0.append(Translate('Problems'));

                            const thCol1 = cTag('th', {'style': "text-align: right;", width:"25%"});
                            thCol1.append(Translate('Amount Dues'));
                        repairProblemHeadRow.append(thCol0,thCol1);
                    repairProblemHead.appendChild(repairProblemHeadRow);
                repairProblemTable.appendChild(repairProblemHead);
                    const repairProblemBody = cTag('tbody');
                    //============Dynamic Data Loop Here===========//
                    const tableData = data.tableData;
                    let grandDuesvalue = 0;
                    if(tableData.length>0){
                        tableData.forEach(oneRowObj=>{
                            grandDuesvalue += oneRowObj.problemDuesvalue;
                            const problem = oneRowObj.problem;
                            const problemDuesvalueStr = addCurrency(oneRowObj.problemDuesvalue);
                            const problemDetails = oneRowObj.statusDetails;
                            const boldclass = oneRowObj.boldclass;

                            repairProblemHeadRow = cTag('tr');
                                tdCol = cTag('td', {'data-title': Translate('Status'), align:'left'});
                                if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;');}
                                tdCol.innerHTML = problem;
                            repairProblemHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Status Dues'), align:'right'});
                                if(boldclass !==''){tdCol.style.fontWeight = 'bold';}
                                tdCol.innerHTML = problemDuesvalueStr;
                            repairProblemHeadRow.appendChild(tdCol);
                            repairProblemBody.appendChild(repairProblemHeadRow);
    
                            if(problemDetails !== null && problemDetails.length>0){
                                problemDetails.forEach(oneRow=>{
                                    const ticket_no = oneRow[0];
                                    const repairs_id = oneRow[1];
                                    const customerName = oneRow[2];
                                    const customer_id = oneRow[3];
                                    const duesValueStr = addCurrency(oneRow[4]);
                                    repairProblemHeadRow = cTag('tr');
                                        tdCol = cTag('td', {'data-title': Translate('Sale Date'), align:'left'});
                                        tdCol.append('T'+ticket_no);                                                    
                                            const repairLink = cTag('a', {href:'/Repairs/edit/'+repairs_id, 'style': "color: #009; text-decoration: underline;", title:Translate('View Repair Details')});
                                            repairLink.appendChild(cTag('i', {'class': 'fa fa-link'}));
                                        tdCol.appendChild(repairLink);
                                        tdCol.append('\u2003'+customerName);                                                    
                                            const customerLink = cTag('a', {href:'/Customers/view/'+customer_id, 'style': "color: #009; text-decoration: underline;", title:Translate('View Customer Details')});
                                            customerLink.appendChild(cTag('i', {'class': 'fa fa-link'}));
                                        tdCol.appendChild(customerLink);
                                    repairProblemHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': Translate('Total'), align:'right'});
                                        tdCol.innerHTML = duesValueStr;
                                    repairProblemHeadRow.appendChild(tdCol);
                                    repairProblemBody.appendChild(repairProblemHeadRow);
                                });
                            }
                        });
                    }
                    else{
                        repairProblemHeadRow = cTag('tr');
                            tdCol = cTag('td',{colspan:"2"});
                            tdCol.innerHTML = '';
                        repairProblemHeadRow.appendChild(tdCol);
                        repairProblemBody.appendChild(repairProblemHeadRow);
                    }
                    grandDuesvalue_button.append(Translate('Total')+': '+ addCurrency(grandDuesvalue));
                repairProblemTable.appendChild(repairProblemBody);
            noMoreTables.appendChild(repairProblemTable);
        searchResult.appendChild(noMoreTables);
    }
}

async function AJ_repairs_by_problem_MoreInfo(){
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const problemValue = params.get("problem");
	const jsonData = {};
    
    const url = '/'+segment1+'/AJ_repairs_by_problem_MoreInfo'
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        const select = document.querySelector("#problem");
        select.innerHTML = '';
        const option = cTag('option', {value:''});
        option.innerHTML = Translate('All');
        select.appendChild(option);
        setOptions(select,data.problemOpt, 0, 1);
        select.value = problemValue;

        repairs_by_problemData();
    }
}

function repairs_by_problem(){
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const sales_date = DBDateRangeToViewDate(params.get("sales_date"));

    let inputField;
    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Repairs by problems')+' ';
                headerTitle.append(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Repairs_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Repairs_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        const repairProblemColumn = cTag('div', {class:'columnSM12'});
            inputField = cTag('input', {'type': 'hidden', id: 'twoSegments', 'value': 'Repairs_reports-repairs_by_problemData'});
        repairProblemColumn.appendChild(inputField);
            inputField = cTag('input', {'type': 'hidden', id: 'pageURI', 'value': 'Repairs_reports/repairs_by_problemData'});
        repairProblemColumn.appendChild(inputField);
            const divCallOut = cTag('div', {'style': 'background:#FFF;'});
                const repairProblemRow = cTag('div', {class:'flexSpaBetRow'});
                    const viewColumn = cTag('div', {class:'columnXS6 columnSM4'});
                        const viewInGroup = cTag('div', {class:'input-group'});
                            const viewLabel = cTag('label', {class:'input-group-addon cursor', 'for': 'showing_type'});
                            viewLabel.innerHTML = Translate('View');
                        viewInGroup.appendChild(viewLabel);
                            const selectShowingType = cTag('select', {name: 'showing_type', id: 'showing_type', class: 'form-control'});
                            selectShowingType.addEventListener('change', repairs_by_problemData);
                                const summaryOption = cTag('option', {'value': 'Summary'});
                                summaryOption.innerHTML = Translate('Summary');
                            selectShowingType.appendChild(summaryOption);
                                const detailOption = cTag('option', {'value': 'Details'});
                                detailOption.innerHTML = Translate('Detailed Summary');
                            selectShowingType.appendChild(detailOption);
                        viewInGroup.appendChild(selectShowingType);
                    viewColumn.appendChild(viewInGroup);
                repairProblemRow.appendChild(viewColumn);
                    const dateRangeField = cTag('div', {class:'columnXS6 columnSM4 daterangeContainer'});
                        inputField = cTag('input', {minlength: 23, 'maxlength': 23, 'type': 'text', class: 'form-control search sales_date', 'style': "padding-left: 35px;", name: 'sales_date', id: 'sales_date', value: sales_date});
                        daterange_picker_dialog(inputField,{submit:repairs_by_problemData});
                    dateRangeField.appendChild(inputField);
                repairProblemRow.appendChild(dateRangeField);
            divCallOut.appendChild(repairProblemRow);
        repairProblemColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(repairProblemColumn);
                    const problemColumn = cTag('div', {class:'columnXS12 columnSM4'});
                        const problemInGroup = cTag('div', {class:'input-group'});
                            const problemLabel = cTag('label', {for:'problem', class: 'input-group-addon cursor'});
                            problemLabel.innerHTML = Translate('Problem');
                        problemInGroup.appendChild(problemLabel);
                            const selectProblem = cTag('select', {id: 'problem', name: 'problem', class: 'form-control'});
                            selectProblem.addEventListener('change', repairs_by_problemData);
                        problemInGroup.appendChild(selectProblem);
                            const searchSpan = cTag('span', {class:'input-group-addon cursor', 'data-toggle': 'tooltip', 'data-placement': 'bottom', title: '', 'data-original-title': Translate('Date wise Search')});
                            searchSpan.addEventListener('click', repairs_by_problemData);
                                const searchIcon = cTag('i', {class:'fa fa-search'});
                            searchSpan.appendChild(searchIcon);
                        problemInGroup.appendChild(searchSpan);
                    problemColumn.appendChild(problemInGroup);
                repairProblemRow.appendChild(problemColumn);
            divCallOut.appendChild(repairProblemRow);
        repairProblemColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(repairProblemColumn);
                const searchResultColumn = cTag('div',{ class:"columnXS12"});
                    const searchResult = cTag('div',{id:"Searchresult"});
                    searchResult.append(' ');
                searchResultColumn.appendChild(searchResult);
            divCallOut.appendChild(searchResultColumn);
        repairProblemColumn.append(divCallOut);
    viewPageInfo.appendChild(repairProblemColumn);

    AJ_repairs_by_problem_MoreInfo();
}

async function AJ_sales_by_Technician_MoreInfo(){
    const sales_date = document.getElementById("sales_date").value;
	const showing_type = document.getElementById("showing_type").value;
	const assign_to = document.getElementById("assign_to").value;

	const jsonData = {};
	jsonData['sales_date'] = sales_date;
    jsonData['showing_type'] = showing_type;
    jsonData['assign_to'] = assign_to;
    
    const url = '/Repairs_reports/sales_by_TechnicianData';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
            
        let technicianHeadRow, tdCol;
        const Searchresult = document.querySelector('#Searchresult');
        Searchresult.innerHTML = '';
            const noMoreTables = cTag('div', {id:"statusTable"});
                const buttonName = cTag('div',{class:'flexEndRow', 'style': "margin: 5px 0;", id:"filterby"})
                    const totalqty_button2 = cTag('button', {class:" btn reportButton", 'style': "margin-left: 10px;"});
                    const grandtotal_button1 = cTag('button', {class:" btn reportButton", 'style': "margin-left: 10px;"});
                buttonName.append(grandtotal_button1, totalqty_button2);
            noMoreTables.appendChild(buttonName)
                const technicianTable = cTag('table', {class:" bgnone table-bordered table-striped table-condensed cf listing"});
                    const technicianHead = cTag('thead', {class:"cf"});                           
                        technicianHeadRow = cTag('tr');
                            const thCol0 = cTag('th',{'style': "text-align: center;"});
                            thCol0.append(Translate('Product Info'));

                            const thCol1 = cTag('th', {'style': "text-align: right;", width:"8%"});
                            thCol1.append(Translate('Shipping Qty'));

                            const thCol2 = cTag('th', {'style': "text-align: right;", width:"10%"});
                            thCol2.append(Translate('Unit Price'));

                            const thCol3 = cTag('th', {'style': "text-align: right;", width:"12%"});
                            thCol3.append(Translate('Total'));
                        technicianHeadRow.append(thCol0, thCol1,thCol2,thCol3);                       
                    technicianHead.appendChild(technicianHeadRow);
                technicianTable.appendChild(technicianHead);
                    const technicianBody = cTag('tbody');
                    //============Dynamic Data Loop Here===========//
                    const tableData = data.tableData;
                    let grandtotal = 0;
                    let totalqty = 0;

                    if(tableData.length>0){
                        tableData.forEach(oneRowObj=>{
                            grandtotal += oneRowObj.subrowgrandtotal;
                            totalqty += oneRowObj.subrowtotalqty;                                    

                            const technician = oneRowObj.technician;
                            const subrowtotalqty = oneRowObj.subrowtotalqty;
                            const unitpricestr = addCurrency(oneRowObj.unitprice);
                            const subrowgrandtotalstr = addCurrency(oneRowObj.subrowgrandtotal);
                            const substrextra = oneRowObj.substrextra;
                            const boldclass = oneRowObj.boldclass;

                            technicianHeadRow = cTag('tr');
                                tdCol = cTag('td', {'data-title': Translate('Product Info'), align:"left"});
                                if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;');}
                                tdCol.innerHTML = technician;
                            technicianHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Shipping Qty'), align:'right'});
                                if(boldclass !==''){tdCol.style.fontWeight = 'bold';}
                                tdCol.innerHTML = subrowtotalqty;
                            technicianHeadRow.appendChild(tdCol);                                    
                                tdCol = cTag('td', {'data-title': Translate('Unit Price'), align:'right'});
                                if(boldclass !==''){tdCol.style.fontWeight = 'bold';}
                                tdCol.innerHTML = unitpricestr;
                            technicianHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Grand Total'), align:'right'});
                                if(boldclass !==''){tdCol.style.fontWeight = 'bold';}
                                tdCol.innerHTML = subrowgrandtotalstr;
                            technicianHeadRow.appendChild(tdCol);                                    
                            technicianBody.appendChild(technicianHeadRow);
                            if(substrextra !== null && substrextra.length>0){
                                substrextra.forEach(oneRow=>{
                                    const description = oneRow.description;
                                    const rowtotalqty = oneRow.rowtotalqty;
                                    const rowunitpricestr = addCurrency(oneRow.unitprice);
                                    let rowgrandtotalstr = addCurrency(oneRow.rowgrandtotal);
                                    technicianHeadRow = cTag('tr');
                                        tdCol = cTag('td', {'style':"font-weight: bold;", 'data-title': Translate('Product Info'), align:"left"});
                                        tdCol.append(description);                                                    
                                    technicianHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'style':"font-weight: bold;",'data-title': Translate('Shipping Qty'), align:"right"});
                                        tdCol.append(rowtotalqty);                                                    
                                    technicianHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'style':"font-weight: bold;",'data-title': Translate('Unit Price'), align:"right"});
                                        tdCol.append(rowunitpricestr);                                                    
                                    technicianHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'style':"font-weight: bold;",'data-title': Translate('Grand Total'), align:"right"});
                                        tdCol.append(rowgrandtotalstr);                                             
                                    technicianHeadRow.appendChild(tdCol);
                                    technicianBody.appendChild(technicianHeadRow);
                                    const subsubData = oneRow.subsubstrextra;
                                    if(subsubData !== null && subsubData.length>0){
                                        subsubData.forEach(oneCol=>{
                                        const salesdatetime = DBDateToViewDate(oneCol[0], 0, 1);
                                        // const invoice_no = oneCol[1];
                                        // const shipping_qty = oneCol[2];
                                        // const customer_id = oneCol[3];
                                        // const dsales_pricestr = addCurrency(oneCol[4]);
                                        // const drowgrandtotalstr = addCurrency(oneCol[5]);
                                        const invoice_no = oneCol[1];
                                        const customer_id = oneCol[2];
                                        const shipping_qty = oneCol[4];
                                        const dsales_pricestr = addCurrency(oneCol[5]);
                                        const drowgrandtotalstr = addCurrency(oneCol[6]);
                                            technicianHeadRow = cTag('tr');
                                                tdCol = cTag('td',{'data-title':Translate('Sale Date'), align:"left"});
                                                    const invoiceDiv1 = cTag('span',{'style': "margin-right: 10px;"});
                                                    invoiceDiv1.append(salesdatetime+'\u2003');
                                                        const invoiceLink = cTag('a',{href:'/Invoices/view/'+invoice_no, 'style': "color: #009; text-decoration: underline;", title:Translate('View Invoice')});
                                                        invoiceLink.append(invoice_no);
                                                            const linkIcon = cTag('i',{class:"fa fa-link"});
                                                        invoiceLink.appendChild(linkIcon);
                                                    invoiceDiv1.appendChild(invoiceLink);
                                                tdCol.appendChild(invoiceDiv1);
                                                    const invoiceDiv2 = cTag('span',{'style': "margin-left: 10px;"});
                                                        const viewInvoice = cTag('a',{href:'/Customers/view/'+customer_id, 'style': "color: #009; text-decoration: underline;", title:Translate('View Customer Details')});
                                                        viewInvoice.append(oneCol[3]);
                                                    invoiceDiv2.appendChild(viewInvoice);
                                                tdCol.appendChild(invoiceDiv2);
                                            technicianHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{'data-title':Translate('Shipping Qty'), align:"right"});
                                                tdCol.append(shipping_qty);
                                            technicianHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{'data-title':Translate('Unit Price'), align:"right"});
                                                tdCol.append(dsales_pricestr);
                                            technicianHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td',{'data-title':Translate('Total'), align:"right"});
                                                tdCol.append(drowgrandtotalstr);
                                            technicianHeadRow.appendChild(tdCol);
                                            technicianBody.appendChild(technicianHeadRow);
                                        });
                                    }
                                });
                            }
                        });
                    }
                    else{
                        technicianHeadRow = cTag('tr');
                            tdCol = cTag('td',{colspan:"4"});
                            tdCol.innerHTML = '';
                        technicianHeadRow.appendChild(tdCol);
                        technicianBody.appendChild(technicianHeadRow);
                    }
                    grandtotal_button1.append(Translate('Grand Total')+' : '+addCurrency(grandtotal));
                    totalqty_button2.append(Translate('Shipping Qty')+' : '+totalqty);

                technicianTable.appendChild(technicianBody);
            noMoreTables.appendChild(technicianTable);
        Searchresult.appendChild(noMoreTables);
    }
}

function sales_by_Technician(){
    let inputField;
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const summary = params.get("showing_type");
    const assign_to = params.get("assign_to");
    const sales_date = DBDateRangeToViewDate(params.get("sales_date"));

    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Sales by Technician')+' ';
                headerTitle.append(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Repairs_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Repairs_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));
            buttonsName.append( reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        const technicianColumn = cTag('div', {class:'columnXS12'});
            inputField = cTag('input', {'type': 'hidden', id: 'twoSegments', 'value': 'Repairs_reports-sales_by_Technician'});
        technicianColumn.appendChild(inputField);
            inputField = cTag('input', {'type': 'hidden', id: 'pageURI', 'value': 'Repairs_reports/sales_by_Technician'});
        technicianColumn.appendChild(inputField);
            const divCallOut = cTag('div', {'style': 'background:#FFF;'});
                const technicianRow = cTag('div', {class:'flexSpaBetRow'});
                    const viewColumn = cTag('div', {class:'columnXS6 columnSM4'});
                        const viewInGroup = cTag('div', {class:'input-group'});
                            const viewLabel = cTag('label', {class:'input-group-addon cursor', 'for': 'showing_type'});
                            viewLabel.innerHTML = Translate('View');
                        viewInGroup.appendChild(viewLabel);
                            const selectShowingType = cTag('select', {name: 'showing_type', id: 'showing_type', class: 'form-control'});
                            selectShowingType.addEventListener('change', AJ_sales_by_Technician_MoreInfo);
                                const summaryOption = cTag('option', {'value': 'Summary'});
                                summaryOption.innerHTML = Translate('Summary');
                            selectShowingType.appendChild(summaryOption);
                                const detailOption = cTag('option', {'value': 'Details'});
                                detailOption.innerHTML = Translate('Detailed Summary');
                            selectShowingType.appendChild(detailOption);
                        viewInGroup.appendChild(selectShowingType);
                    viewColumn.appendChild(viewInGroup);
                technicianRow.appendChild(viewColumn);
                    const dateRangeField = cTag('div', {class:'columnXS6 columnSM4 daterangeContainer'});
                        inputField = cTag('input', {minlength: 23, 'maxlength': 23, 'type': 'text', class: 'form-control search sales_date', 'style': "padding-left: 35px;", name: 'sales_date', id: 'sales_date', value: sales_date});
                        daterange_picker_dialog(inputField,{submit:AJ_sales_by_Technician_MoreInfo});
                    dateRangeField.appendChild(inputField);
                technicianRow.appendChild(dateRangeField);
            divCallOut.appendChild(technicianRow);
        technicianColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(technicianColumn);
                    const searchColumn = cTag('div', {class:'columnXS12 columnSM4'});
                        const searchInGroup = cTag('div', {class:'input-group'});
                            inputField = cTag('input', {maxlength:50, type:"text", class:"form-control search", name:"assign_to", id:"assign_to", value:assign_to, placeholder:Translate('Sales by Technician')});
                            inputField.addEventListener('keydown',event=>{if(event.which===13) AJ_sales_by_Technician_MoreInfo()});
                        searchInGroup.appendChild(inputField);
                            const searchSpan = cTag('span', {class:'input-group-addon cursor', 'data-toggle': 'tooltip', 'data-placement': 'bottom', title: Translate('Sales by Technician')});
                            searchSpan.addEventListener('click', AJ_sales_by_Technician_MoreInfo);
                                const searchIcon = cTag('i', {class:'fa fa-search'});
                            searchSpan.appendChild(searchIcon);
                        searchInGroup.appendChild(searchSpan);
                    searchColumn.appendChild(searchInGroup);
                technicianRow.appendChild(searchColumn);
            divCallOut.appendChild(technicianRow);
        technicianColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(technicianColumn);
                const searchResultColumn = cTag('div',{ class:"columnXS12", 'style': "margin: 0;"});
                    const searchResult = cTag('div',{id:"Searchresult"});
                    searchResult.append(' ');
                searchResultColumn.appendChild(searchResult);
            divCallOut.appendChild(searchResultColumn);
        technicianColumn.append(divCallOut);
    viewPageInfo.appendChild(technicianColumn);
    AJautoComplete('assign_to');
    AJ_sales_by_Technician_MoreInfo();
}

async function repair_Tickets_CreatedData(){
    const sales_date = document.getElementById("sales_date").value;
    const showing_type = document.getElementById("showing_type").value;
    const status = document.getElementById("status").value;
    const jsonData = {};
    jsonData['sales_date'] = sales_date;
    jsonData['showing_type'] = showing_type;
    jsonData['status'] = status;
    
    const url = '/Repairs_reports/repair_Tickets_CreatedData';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);
        let repairTicketHeadRow, tdCol;
        const Searchresult = document.querySelector('#Searchresult');
        Searchresult.innerHTML = '';
            const noMoreTables = cTag('div', {id: "statusTable"});
                const buttondName = cTag('div',{class:'flexEndRow', 'style': "margin: 5px 0;", id:"filterby"})
                    const grandTotal_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px;"});
                buttondName.appendChild(grandTotal_button)
            noMoreTables.appendChild(buttondName);
                const repairTicketTable = cTag('table', {class:" bgnone table-bordered table-striped table-condensed cf listing"});
                    const repairTicketHead = cTag('thead', {class:"cf"});
                        repairTicketHeadRow = cTag('tr');
                            const titleColTitles = [Translate('Status'), Translate('Amount Dues')];
                            const thCol0 = cTag('th',{align:"left"});
                            thCol0.append(Translate('Date'));

                            const thCol1 = cTag('th', {align:"left", width:"15%"});
                            thCol1.append(Translate('Qty Created'));

                            const thCol2 = cTag('th', {'style': "text-align: right;", width:"20%"});
                            thCol2.append(Translate('Grand Total'));
                        repairTicketHeadRow.append(thCol0, thCol1, thCol2);                       
                    repairTicketHead.appendChild(repairTicketHeadRow);
                repairTicketTable.appendChild(repairTicketHead);
                    const  repairTicketBody = cTag('tbody');
                    //============Dynamic Data Loop Here===========//
                    const tableData = data.tableData;
                    let grandTotal = 0;
                    if(tableData.length>0){
                        tableData.forEach(oneRowObj=>{
                            grandTotal += oneRowObj.createdOnGrandTotal;
                            const createdOn = oneRowObj.createdOn;
                            const qtyCreated = oneRowObj.qtyCreated;
                            const createdOnGrandTotalStr = addCurrency(oneRowObj.createdOnGrandTotal);
                            const boldclass = oneRowObj.boldclass;
                            const createdOnDetails = oneRowObj.createdOnDetails;
                            repairTicketHeadRow = cTag('tr');
                                tdCol = cTag('td', {'data-title': Translate('Created On'), align:'left'});
                                if(boldclass !==''){tdCol.setAttribute('style', 'font-weight: bold;');}
                                tdCol.innerHTML = createdOn;
                            repairTicketHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Qty Created'), align:'right'});
                                if(boldclass !==''){tdCol.style.fontWeight = 'bold';}
                                tdCol.innerHTML = qtyCreated;
                            repairTicketHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': Translate('Grand Total'), align:'right'});
                                if(boldclass !==''){tdCol.style.fontWeight = 'bold';}
                                tdCol.innerHTML = createdOnGrandTotalStr;
                            repairTicketHeadRow.appendChild(tdCol);
                            repairTicketBody.appendChild(repairTicketHeadRow);

                            if(createdOnDetails !== null && createdOnDetails.length>0){
                                createdOnDetails.forEach(oneRow=>{
                                    const ticket_no = oneRow[0];
                                    const repairs_id = oneRow[1];
                                    const customerName = oneRow[2];
                                    const customer_id = oneRow[3];
                                    const posGrandTotalStr = addCurrency(oneRow[4]);
                                    repairTicketHeadRow = cTag('tr');
                                        tdCol = cTag('td', {'data-title': Translate('Sale Date'), align:"left"});
                                        tdCol.append('T'+ticket_no);                                                    
                                            const repairsLink = cTag('a', {href:'/Repairs/edit/'+repairs_id, 'style': "color: #009; text-decoration: underline;", title:Translate('View Repair Details')});
                                            repairsLink.appendChild(cTag('i', {'class': 'fa fa-link'}));
                                        tdCol.appendChild(repairsLink);
                                        tdCol.append('\u2003'+customerName);                                                    
                                            const customerLink = cTag('a', {href:'/Customers/view/'+customer_id, 'style': "color: #009; text-decoration: underline;", title:Translate('View Customer Details')});
                                            customerLink.appendChild(cTag('i', {'class': 'fa fa-link'}));
                                        tdCol.appendChild(customerLink);
                                    repairTicketHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': Translate('Qty Created'), align:'right'});
                                        tdCol.innerHTML = 1;
                                    repairTicketHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': Translate('Invoice Grand Total'), align:'right'});
                                        tdCol.innerHTML = posGrandTotalStr;
                                    repairTicketHeadRow.appendChild(tdCol);
                                    repairTicketBody.appendChild(repairTicketHeadRow);
                                });
                            }
                        });
                    }
                    else{
                        repairTicketHeadRow = cTag('tr');
                            tdCol = cTag('td',{colspan:"3"});
                            tdCol.innerHTML = '';
                        repairTicketHeadRow.appendChild(tdCol);
                        repairTicketBody.appendChild(repairTicketHeadRow);
                    }
                    grandTotal_button.append(Translate('Total')+': '+ addCurrency(grandTotal));
                repairTicketTable.appendChild(repairTicketBody);
            noMoreTables.appendChild(repairTicketTable);
        Searchresult.appendChild(noMoreTables);
    }
}

async function AJ_repair_Tickets_Created_MoreInfo(){
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const statusValue = params.get("status");
	const jsonData = {};
    
    const url = '/'+segment1+'/AJ_repair_Tickets_Created_MoreInfo'
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        const select = document.querySelector("#status");
        select.innerHTML = '';
            const option = cTag('option', {value:''});
            option.innerHTML = Translate('All');
        select.appendChild(option);
        setOptions(select,data.statusOpt, 0, 1);
        select.value = statusValue;
        repair_Tickets_CreatedData();
    }
}

function repair_Tickets_Created(){
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const sales_date = DBDateRangeToViewDate(params.get("sales_date"));

    let inputField;
    const viewPageInfo = document.querySelector('#viewPageInfo');
    viewPageInfo.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Repair Tickets Created')+' ';
                headerTitle.append(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});    
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style':"text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Repairs_reports);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click', function (){window.location='/Repairs_reports/lists';});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' ',Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    viewPageInfo.appendChild(titleRow);

        const repairTicketColumn = cTag('div', {class:'columnSM12'});
            inputField = cTag('input', {'type': 'hidden', id: 'twoSegments', 'value': 'Repairs_reports-repair_Tickets_Created'});
        repairTicketColumn.appendChild(inputField);
            inputField = cTag('input', {'type': 'hidden', id: 'pageURI', 'value': 'Repairs_reports/repair_Tickets_Created'});
        repairTicketColumn.appendChild(inputField);
            const divCallOut = cTag('div', {'style': 'background:#FFF;'});
                const repairTicketRow = cTag('div', {class:'flexSpaBetRow'});
                    const viewColumn = cTag('div', {class:'columnXS6 columnSM4'});
                        const viewInGroup = cTag('div', {class:'input-group'});
                            const viewLabel = cTag('label', {class:'input-group-addon cursor', 'for': 'showing_type'});
                            viewLabel.innerHTML = Translate('View');
                        viewInGroup.appendChild(viewLabel);
                            const selectShowingType = cTag('select', {name: 'showing_type', id: 'showing_type', class: 'form-control'});
                            selectShowingType.addEventListener('change', repair_Tickets_CreatedData);
                                const summaryOption = cTag('option', {'value': 'Summary'});
                                summaryOption.innerHTML = Translate('Summary');
                            selectShowingType.appendChild(summaryOption);
                                const detailOption = cTag('option', {'value': 'Details'});
                                detailOption.innerHTML = Translate('Detailed Summary');
                            selectShowingType.appendChild(detailOption);
                        viewInGroup.appendChild(selectShowingType);
                    viewColumn.appendChild(viewInGroup);
                repairTicketRow.appendChild(viewColumn);
                    const dateRangeField = cTag('div', {class:'columnXS6 columnSM4 daterangeContainer'});
                        inputField = cTag('input', {minlength: 23, 'maxlength': 23, 'type': 'text', class: 'form-control search sales_date', 'style': "padding-left: 35px;", name: 'sales_date', id: 'sales_date', value: sales_date});
                        daterange_picker_dialog(inputField,{submit:repair_Tickets_CreatedData});
                    dateRangeField.appendChild(inputField);
                repairTicketRow.appendChild(dateRangeField);
            divCallOut.appendChild(repairTicketRow);
        repairTicketColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(repairTicketColumn);
                    const statusColumn = cTag('div', {class:'columnXS12 columnSM4'});
                        const statusInGroup = cTag('div', {class:'input-group'});
                            const statusLabel = cTag('label', {for:'status', class: 'input-group-addon cursor'});
                            statusLabel.innerHTML = Translate('Status');
                        statusInGroup.appendChild(statusLabel);
                            const selectStatus = cTag('select', {id: 'status', name: 'status', class: 'form-control'});
                            selectStatus.addEventListener('change', repair_Tickets_CreatedData);
                        statusInGroup.appendChild(selectStatus);
                            const searchSpan = cTag('span', {class:'input-group-addon cursor', 'data-toggle': 'tooltip', 'data-placement': 'bottom', title: '', 'data-original-title': Translate('Date wise Search')});
                            searchSpan.addEventListener('click', repair_Tickets_CreatedData);
                                const searchIcon = cTag('i', {class:'fa fa-search'});
                            searchSpan.appendChild(searchIcon);
                        statusInGroup.appendChild(searchSpan);
                    statusColumn.appendChild(statusInGroup);
                repairTicketRow.appendChild(statusColumn);
            divCallOut.appendChild(repairTicketRow);
        repairTicketColumn.appendChild(divCallOut);
    viewPageInfo.appendChild(repairTicketColumn);
                const searchResultColumn = cTag('div',{ class:"columnXS12"});
                    const searchResult = cTag('div',{id:"Searchresult"});
                    searchResult.append(' ');
                searchResultColumn.appendChild(searchResult);
            divCallOut.appendChild(searchResultColumn);
        repairTicketColumn.append(divCallOut);
    viewPageInfo.appendChild(repairTicketColumn);

    AJ_repair_Tickets_Created_MoreInfo()
}

function print_Repairs_reports() {
	let todayDate,document_focus,filterby;
    const divContents = document.querySelector("#statusTable").cloneNode(true);
	const showing_type = document.getElementById("showing_type").options[document.querySelector("#showing_type").selectedIndex].text;
	filterby = Translate('View')+': '+showing_type;

    let ColSpan = 12;
    
    let sales_date = document.getElementById("sales_date").value;    
	if(sales_date !==''){
		filterby += ', '+Translate('Date Range')+': '+sales_date;
	}

    let status = document.getElementById("status");
	if(status){
		ColSpan = 2;        
		if(document.getElementById("twoSegments").value==='Repairs_reports-repairs_by_problem'){ColSpan = 12;}
		else if(document.getElementById("twoSegments").value==='Repairs_reports-repair_Tickets_Created'){ColSpan = 12;}
		filterby += ', '+Translate('Status')+': '+status.options[status.selectedIndex].innerText;
	}
    let problem = document.getElementById("problem");
	if(problem){
		ColSpan = 12;
		filterby += ', '+Translate('Problem')+': '+problem.options[problem.selectedIndex].innerText;
	}
    let assign_to = document.getElementById("assign_to");
	if(assign_to){
		ColSpan = 12;
		if(assign_to.value!=='')filterby += ', '+Translate('Technician')+': '+assign_to.value;	
	}

    if(document.getElementById("customer")){
	    const customer = document.getElementById("customer").value;
		if(customer !==''){
			if(filterby !==''){filterby +=', ';}
			filterby += Translate('Customer')+': '+customer;
		}
    }
    
	const titleP = document.getElementById("ptitle").innerHTML;
	const now = new Date();
	if(calenderDate.toLowerCase()==='dd-mm-yyyy'){todayDate = now.getDate()+'-'+(now.getMonth() + 1)+'-'+now.getFullYear();}
	else{todayDate = (now.getMonth() + 1)+'/'+now.getDate()+'/'+now.getFullYear();}

    const additionaltoprows = cTag('div');
        const companyNameDiv1 = cTag('div',{ 'class':`flexSpaBetRow` });
            let companyNameWidth = cTag('div',{ 'style': "font-weight: bold; font-size: 18px;" });
            companyNameWidth.innerHTML = stripslashes(companyName);
        companyNameDiv1.appendChild(companyNameWidth);
            let titleWidth = cTag('div',{ 'style': "font-weight: bold; font-size: 20px;" });
            titleWidth.innerHTML = titleP;
        companyNameDiv1.appendChild(titleWidth);
            let todayDateDiv = cTag('div',{ 'style': "text-align: right; font-size: 16px;" });
            todayDateDiv.innerHTML = todayDate;
        companyNameDiv1.appendChild(todayDateDiv);
    additionaltoprows.appendChild(companyNameDiv1);
    additionaltoprows.appendChild(cTag('div',{ 'style': "border-top: 1px solid #CCC; margin-top: 10px;" }));
        let filterbyDiv = cTag('div');
        filterbyDiv.innerHTML = filterby;
    additionaltoprows.appendChild(filterbyDiv);    
    divContents.prepend(additionaltoprows);

	const day = new Date();
	const id = day.getTime();
	let w = 900;
	let h = 600;
	const scrl = 1;
	const winl = (screen.width - w) / 2;
	const wint = (screen.height - h) / 2;
	const winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	const printWindow = window.open('', '" + id + "', winprops);

        const html = cTag('html');
            const head = cTag('head');
                const title = cTag('title');
                title.innerHTML = titleP;
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
		if (document_focus === true && deviceOpSy==='unknown') printWindow.window.close()
	}, 500);
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists, repairs_by_status, repairs_by_problem, repairs_by_problem, sales_by_Technician, repair_Tickets_Created};
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));

    // if(document.querySelector('#sales_date')){
    //     document.querySelector("#sales_date").addEventListener('click', e => {
    //         setTimeout(function() {
    //             if(document.querySelector("#twoSegments").value !=='Repairs_reports-lists' && document.querySelectorAll('.applyBtn').length){
    //                 document.querySelectorAll('.applyBtn').forEach(item=>{
    //                     item.addEventListener('click', function(){
    //                         loadData = segment2+'Data';
    //                         fn = window[loadData];
    //                         if(typeof fn === "function"){fn();}
    //                     });
    //                 })
    //             }
    //         }, 1000);
    //     });
    // }
    
    // if(document.querySelector("#twoSegments") && ['Repairs_reports-lists', 'Repairs_reports-sales_by_Technician'].includes(document.querySelector("#twoSegments").value)){
    //     if(document.getElementById("assign_to")){AJautoComplete('assign_to');}
    // }
});