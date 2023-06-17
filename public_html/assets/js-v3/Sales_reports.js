import {
    cTag, Translate, tooltip, storeSessionData, addCurrency, round, calculate, DBDateToViewDate, DBDateRangeToViewDate, setOptions, checkAndSetSessionData,
    getMobileOperatingSystem, fetchData, daterange_picker_dialog, listenToEnterKey, AJautoComplete, changeToDBdate_OnSubmit
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

async function AJ_lists_MoreInfo(){
	const jsonData = {};
    
    const url = '/'+segment1+'/AJ_lists_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData, 0);
        let select = document.getElementById('paymenttype');
        setOptions(select,data.poData, 0, 1);
        if(document.querySelector("#twoSegments")){
            if(document.getElementById("employee")){AJautoComplete('employee');}
            if(document.getElementById("product")){AJautoComplete('product');}
            if(document.getElementById("assign_to")){AJautoComplete('assign_to');}
            if(document.getElementById("customer_name")){AJautoComplete('customer_name');}
        }
    }
}

function lists(){
    let now = new Date();
    let date = now.getDate();
    let month = now.getMonth() + 1;

    let last = new Date(now.getTime() - (7 * 24 * 60 * 60 * 1000));
    let ldate = last.getDate();
    let lmonth = last.getMonth() + 1;

    let lastDate, todayDate;
    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
        lastDate = (ldate<10 ? '0'+ldate : ldate) +'-'+(lmonth<10 ? '0'+lmonth : lmonth)+'-'+last.getFullYear();
        todayDate = (date<10 ? '0'+date : date) +'-'+(month<10 ? '0'+month : month)+'-'+now.getFullYear();
    }
    else{
        lastDate = (lmonth<10 ? '0'+lmonth : lmonth)+'/'+ (ldate<10 ? '0'+ldate : ldate ) +'/'+last.getFullYear();
        todayDate = (month<10 ? '0'+month : month)+'/'+ (date<10 ? '0'+date : date ) +'/'+now.getFullYear();
    }
    let date_range = lastDate+' - '+todayDate;

    let reportHeader, reportHeaderTitle, dateRangeField, inputField, errorMsg, generateReport;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div');
            const headerTitle = cTag('h2', {'style': "padding: 5px; text-align: start;"});
            headerTitle.innerHTML = Translate('Sales Reports')+' ';
            headerTitle.appendChild(cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle':"tooltip", 'data-placement':"bottom", title:"", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')}));
        titleRow.appendChild(headerTitle);
    showTableData.appendChild(titleRow);

        inputField = cTag('input',{'type': "hidden", id: "twoSegments", 'value': "Sales_reports-lists"});
    showTableData.appendChild(inputField);
        inputField = cTag('input',{'type': "hidden", id: "pageURI", 'value': "Sales_reports/lists"});
    showTableData.appendChild(inputField);

        let reportTitle = cTag('div', {class: "columnSM12"});
            let reportTypeRow = cTag('div',{class: "flexSpaBetRow borderbottom"});
                reportHeader = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                    reportHeaderTitle = cTag('strong');
                    reportHeaderTitle.innerHTML = Translate('Report Type');
                reportHeader.appendChild(reportHeaderTitle);
            reportTypeRow.appendChild(reportHeader);
                reportHeader = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': `text-align: center;`});
                    reportHeaderTitle = cTag('strong');
                    reportHeaderTitle.innerHTML = Translate('From Date - To Date');
                reportHeader.appendChild(reportHeaderTitle);
            reportTypeRow.appendChild(reportHeader);
                reportHeader = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': `text-align: center;`});
                    reportHeaderTitle = cTag('strong');
                    reportHeaderTitle.innerHTML = Translate('Optional Keyword');
                reportHeader.appendChild(reportHeaderTitle);
            reportTypeRow.appendChild(reportHeader);
                reportHeader = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                    reportHeaderTitle = cTag('strong');
                    reportHeaderTitle.innerHTML = Translate('Get Report');
                reportHeader.appendChild(reportHeaderTitle);
            reportTypeRow.appendChild(reportHeader);
        reportTitle.appendChild(reportTypeRow);

            const salesDateForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_date',true),'enctype': "text/plain", 'method': "get", name: "frmsales_by_date", id: "frmsales_by_date", 'action': "/Sales_reports/sales_by_Date/"});
                const salesDateRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                    const salesDateColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                        const salesDateLabel = cTag('label',{for: "sales_by_date"});
                        salesDateLabel.innerHTML = Translate('Sales by Date');
                    salesDateColumn.appendChild(salesDateLabel);
                salesDateRow.appendChild(salesDateColumn);
                    dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                        inputField = cTag('input',{'keydown':listenToEnterKey(()=>checkSalesDate('Daily')),'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control sales_date checkSalesDate", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_by_date", 'value': date_range});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                salesDateRow.appendChild(dateRangeField);
                    let hiddenType = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                        inputField = cTag('input',{'type': "hidden", name: "report_type", 'value': "Daily"});
                    hiddenType.appendChild(inputField);
                        inputField = cTag('input',{'type': "hidden", name: "showing_type", 'value': "Summary"});
                    hiddenType.appendChild(inputField);
                        errorMsg = cTag('span',{class: "errormsg", id: "error_frmsales_by_date"});
                    hiddenType.appendChild(errorMsg);
                salesDateRow.appendChild(hiddenType);
                    const buttonNames = cTag('div',{class: "columnXS6 columnSM4 columnMD3 flex", 'style': "text-align: left; gap:10px; flex-wrap:wrap"});
                        const dailyButton = cTag('button',{class: "btn defaultButton", 'style': "flex-grow: 1;", 'type': "submit", title: Translate('Daily')});
                        dailyButton.addEventListener('click', function(){checkSalesDate('Daily');});
                        dailyButton.innerHTML = Translate('Daily');
                    buttonNames.appendChild(dailyButton);
                        const weeklyButton = cTag('button',{class: "btn defaultButton", 'style': "flex-grow: 1;", 'type': "submit", title: Translate('Weekly')});
                        weeklyButton.addEventListener('click', function(){checkSalesDate('Weekly');});
                        weeklyButton.innerHTML = Translate('Weekly');
                    buttonNames.appendChild(weeklyButton);
                        const monthlyButton = cTag('button',{class: "btn defaultButton", 'style': "flex-grow: 1;", 'type': "submit", title: Translate('Monthly')});
                        monthlyButton.addEventListener('click', function(){checkSalesDate('Monthly');});
                        monthlyButton.innerHTML = Translate('Monthly');
                    buttonNames.appendChild(monthlyButton);
                salesDateRow.appendChild(buttonNames);
            salesDateForm.appendChild(salesDateRow);
        reportTitle.appendChild(salesDateForm);

            const salesPersonForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_employee',true),'enctype': "text/plain", 'method': "get", name: "frmsales_by_employee", 'action': "/Sales_reports/sales_by_Employee/"});
                const salesPersonRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                    const salesPersonColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                        const salesPersonLabel = cTag('label',{for: "sales_by_employee"});
                        salesPersonLabel.innerHTML = Translate('Sales by Sales Person');
                    salesPersonColumn.appendChild(salesPersonLabel);
                salesPersonRow.appendChild(salesPersonColumn);
                    dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                        inputField = cTag('input',{'required': "", 'minlength':23, 'maxlength': 23, 'type': "text", class: "form-control sales_date check_sales_by_employee", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_by_employee", 'value': date_range});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                        errorMsg = cTag('span',{class: "errormsg", id: "error_frmsales_by_employee"});
                    dateRangeField.appendChild(errorMsg);
                salesPersonRow.appendChild(dateRangeField);
                    const searchSalesPerson = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                        inputField = cTag('input',{'type': "text",'maxlength': 50, class: "form-control check_sales_by_employee ui-autocomplete-input", name: "employee", id: "employee", 'value': "", 'placeholder': Translate('Sales Person'), 'autocomplete': "off"});
                    searchSalesPerson.appendChild(inputField);
                salesPersonRow.appendChild(searchSalesPerson);
                    generateReport = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                        if(getMobileOperatingSystem()==='unknown'){
                            inputField = cTag('input',{'type': "submit", 'value': Translate('Generate Report'), class: "bgnone", 'title': Translate('Generate Report')});
                        }
                        else{
                            inputField = cTag('button',{'type': "submit", class: "btn defaultButton", 'title': Translate('Generate Report')});
                            inputField.innerHTML = Translate('Generate Report')
                        }
                    generateReport.appendChild(inputField);
                salesPersonRow.appendChild(generateReport);
            salesPersonForm.appendChild(salesPersonRow);
        reportTitle.appendChild(salesPersonForm);

            const salesCustomerForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_customer',true),'enctype': "text/plain", 'method': "get", name: "frmsales_by_customer", 'action': "/Sales_reports/sales_by_Customer/"});
                const salesCustomerRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                    const salesCustomerColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                        const salesCustomerLabel = cTag('label',{for: "sales_by_customer"});
                        salesCustomerLabel.innerHTML = Translate('Sales by Customer');
                    salesCustomerColumn.appendChild(salesCustomerLabel);
                salesCustomerRow.appendChild(salesCustomerColumn);
                    dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                        inputField = cTag('input',{'required': "", 'minlength':23, 'maxlength': 23, 'type': "text", class: "form-control sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_by_customer", 'value': date_range});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                        errorMsg = cTag('span',{class: "errormsg", id: "error_frmsales_by_customer"});
                    dateRangeField.appendChild(errorMsg);
                salesCustomerRow.appendChild(dateRangeField);
                    const searchCustomer = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                        inputField = cTag('input',{'maxlength': 50, 'type': "text", class: "form-control ui-autocomplete-input", name: "customer_name", id: "customer_name", 'value': "", 'placeholder': Translate('Customer Name'), 'autocomplete': "off"});
                    searchCustomer.appendChild(inputField);
                        inputField = cTag('input',{'type': "hidden", name: "customers_id", id: "customers_id", 'value': 0});
                    searchCustomer.appendChild(inputField);
                salesCustomerRow.appendChild(searchCustomer);
                    generateReport = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                        if(getMobileOperatingSystem()==='unknown'){
                            inputField = cTag('input',{'type': "submit", 'value': Translate('Generate Report'), class: "bgnone", 'title': Translate('Generate Report')});
                        }
                        else{
                            inputField = cTag('button',{'type': "submit", class: "btn defaultButton", 'title': Translate('Generate Report')});
                            inputField.innerHTML = Translate('Generate Report')
                        }
                    generateReport.appendChild(inputField);
                salesCustomerRow.appendChild(generateReport);
            salesCustomerForm.appendChild(salesCustomerRow);
        reportTitle.appendChild(salesCustomerForm);

            const paymentTypeForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_paymenttype',true),'enctype': "text/plain", 'method': "get", name: "frmsales_by_paymenttype", 'action': "/Sales_reports/sales_by_Paymenttype/"});
                const paymentTypeRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                    const paymentTypeColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                        const paymentTypeLabel = cTag('label',{for: "sales_by_paymenttype"});
                        paymentTypeLabel.innerHTML = Translate('Payments Received by Type');
                    paymentTypeColumn.appendChild(paymentTypeLabel);
                paymentTypeRow.appendChild(paymentTypeColumn);
                    dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                        inputField = cTag('input',{'required': "", 'minlength':23, 'maxlength': 23, 'type': "text", class: "form-control sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_by_paymenttype", 'value': date_range});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                        errorMsg = cTag('span',{class: "errormsg", id: "error_frmsales_by_paymenttype"});
                    dateRangeField.appendChild(errorMsg);
                paymentTypeRow.appendChild(dateRangeField);
                    const paymentType = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                        let selectPaymentType = cTag('select', {id: "paymenttype", name: "paymenttype", class: "form-control"});               
                            let paymentTypeOption = cTag('option', {'value': ""});
                            paymentTypeOption.innerHTML = Translate('All Payment Types');
                        selectPaymentType.appendChild(paymentTypeOption);
                    paymentType.appendChild(selectPaymentType);
                paymentTypeRow.appendChild(paymentType);
                    generateReport = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                        if(getMobileOperatingSystem()==='unknown'){
                            inputField = cTag('input',{'type': "submit", 'value': Translate('Generate Report'), class: "bgnone", 'title': Translate('Generate Report')});
                        }
                        else{
                            inputField = cTag('button',{'type': "submit", class: "btn defaultButton", 'title': Translate('Generate Report')});
                            inputField.innerHTML = Translate('Generate Report')
                        }
                    generateReport.appendChild(inputField);
                paymentTypeRow.appendChild(generateReport);
            paymentTypeForm.appendChild(paymentTypeRow);
        reportTitle.appendChild(paymentTypeForm);

            const salesProductForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_product',true),'enctype': "text/plain", 'method': "get", name: "frmsales_by_product", 'action': "/Sales_reports/sales_by_Product/"});
                const salesProductRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                    const salesProductColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                        const salesProductLabel = cTag('label',{for: "sales_by_product"});
                        salesProductLabel.innerHTML = Translate('Sales by Product');
                    salesProductColumn.appendChild(salesProductLabel);
                salesProductRow.appendChild(salesProductColumn);
                    dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                        inputField = cTag('input',{'required': "", 'minlength':23, 'maxlength': 23, 'type': "text", class: "form-control sales_date2", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_by_product", 'value': date_range});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                        errorMsg = cTag('span',{class: "errormsg", id: "error_frmsales_by_product"});
                    dateRangeField.appendChild(errorMsg);
                salesProductRow.appendChild(dateRangeField);
                    const searchProduct = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                        inputField = cTag('input',{'type': "text", 'maxlength': 50, class: "form-control ui-autocomplete-input", name: "product", id: "product", 'value': "", 'placeholder': Translate('Product Name/SKU'), autocomplete: "off"});
                    searchProduct.appendChild(inputField);
                        inputField = cTag('input',{'type': "hidden", name: "sku", id: "sku", 'value': ""});
                    searchProduct.appendChild(inputField);
                salesProductRow.appendChild(searchProduct);
                    generateReport = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                        if(getMobileOperatingSystem()==='unknown'){
                            inputField = cTag('input',{'type': "submit", 'value': Translate('Generate Report'), class: "bgnone", 'title': Translate('Generate Report')});
                        }
                        else{
                            inputField = cTag('button',{'type': "submit", class: "btn defaultButton", 'title': Translate('Generate Report')});
                            inputField.innerHTML = Translate('Generate Report')
                        }
                    generateReport.appendChild(inputField);
                salesProductRow.appendChild(generateReport);
            salesProductForm.appendChild(salesProductRow);
        reportTitle.appendChild(salesProductForm);

            const salesCategoryForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_category',true),'enctype': "text/plain", 'method': "get", name: "frmsales_by_category", 'action': "/Sales_reports/sales_by_Category/"});
                const salesCategoryRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                    const salesCategoryColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                        const salesCategoryLabel = cTag('label',{for: "sales_by_category"});
                        salesCategoryLabel.innerHTML = Translate('Sales by Category');
                    salesCategoryColumn.appendChild(salesCategoryLabel);
                salesCategoryRow.appendChild(salesCategoryColumn);
                    dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                        inputField = cTag('input',{'required': "", 'minlength':23, 'maxlength': 23, 'type': "text", class: "form-control sales_date2 check_sales_by_category", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_by_category", 'value': date_range});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                        errorMsg = cTag('span',{class: "errormsg", id: "error_frmsales_by_category"});
                    dateRangeField.appendChild(errorMsg);
                salesCategoryRow.appendChild(dateRangeField);
                    const emptyColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3"});
                    emptyColumn.innerHTML = "&nbsp;";
                salesCategoryRow.appendChild(emptyColumn);
                    generateReport = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                        if(getMobileOperatingSystem()==='unknown'){
                            inputField = cTag('input',{'type': "submit", 'value': Translate('Generate Report'), class: "bgnone", 'title': Translate('Generate Report')});
                        }
                        else{
                            inputField = cTag('button',{'type': "submit", class: "btn defaultButton", 'title': Translate('Generate Report')});
                            inputField.innerHTML = Translate('Generate Report')
                        }
                    generateReport.appendChild(inputField);
                salesCategoryRow.appendChild(generateReport);
            salesCategoryForm.appendChild(salesCategoryRow);
        reportTitle.appendChild(salesCategoryForm);

            const salesTaxForm = cTag('form',{'submit':()=>changeToDBdate_OnSubmit('sales_by_tax',true),'enctype': "text/plain", 'method': "get", name: "frmsales_by_tax", 'action': "/Sales_reports/sales_by_Tax/"});
                const salesTaxRow = cTag('div',{class: "flexSpaBetRow borderbottom", 'style': "align-items: center; padding: 15px 0px;"});
                    const salesTaxColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: right;"});
                        const salesTaxLabel = cTag('label',{for: "sales_by_tax"});
                        salesTaxLabel.innerHTML = Translate('Sales by Tax');
                    salesTaxColumn.appendChild(salesTaxLabel);
                salesTaxRow.appendChild(salesTaxColumn);
                    dateRangeField = cTag('div',{class: "columnXS6 columnSM4 columnMD3 daterangeContainer"});
                        inputField = cTag('input',{'required': "", 'minlength':23, 'maxlength': 23, 'type': "text", class: "form-control sales_date2", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_by_tax", 'value': date_range});
                        daterange_picker_dialog(inputField);
                    dateRangeField.appendChild(inputField);
                        errorMsg = cTag('span',{class: "errormsg", id: "error_frmsales_by_tax"});
                    dateRangeField.appendChild(errorMsg);
                salesTaxRow.appendChild(dateRangeField);
                    const extraColumn = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                    extraColumn.innerHTML = "&nbsp;";
                salesTaxRow.appendChild(extraColumn);
                    generateReport = cTag('div',{class: "columnXS6 columnSM4 columnMD3", 'style': "text-align: left;"});
                        if(getMobileOperatingSystem()==='unknown'){
                            inputField = cTag('input',{'type': "submit", 'value': Translate('Generate Report'), class: "bgnone", 'title': Translate('Generate Report')});
                        }
                        else{
                            inputField = cTag('button',{'type': "submit", class: "btn defaultButton", 'title': Translate('Generate Report')});
                            inputField.innerHTML = Translate('Generate Report')
                        }
                    generateReport.appendChild(inputField);
                salesTaxRow.appendChild(generateReport);
            salesTaxForm.appendChild(salesTaxRow);
        reportTitle.appendChild(salesTaxForm);
    showTableData.appendChild(reportTitle);

    AJ_lists_MoreInfo();
}

function sales_by_Date(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));
    let report_type = params.get("report_type");
    let showing_type = params.get("showing_type");

    let list_filters, inputField;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Sales by Date')+' ';
                headerTitle.appendChild(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align:end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Sales_report);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click',function(){javascript:window.location='/Sales_reports/lists'});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' '+Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);

        inputField = cTag('input', {'type': "hidden", id: "twoSegments", 'value': "Sales_reports-sales_by_Date"});
    showTableData.appendChild(inputField);
        inputField = cTag('input', {'type': "hidden", id: "pageURI", 'value': "Sales_reports/sales_by_Date"});
    showTableData.appendChild(inputField);
        const filterRow = cTag('div', {class:"flexSpaBetRow"});
            const viewColumn = cTag('div', {class:"columnXS6 columnSM4"});
                let viewInGroup = cTag('div', {class:"input-group"});
                    const viewLabel = cTag('label', {class:"input-group-addon cursor", 'for': "showing_type"});
                    viewLabel.innerHTML = Translate('View');
                viewInGroup.appendChild(viewLabel);
                    const selectView = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectView.addEventListener('change', AJ_sales_by_Date_MoreInfo);
                        const summaryOption = cTag('option', {'value': "Summary"});
                        summaryOption.innerHTML = Translate('Summary');
                    selectView.appendChild(summaryOption);
                        const detailOption = cTag('option', {'value': "Details"});
                        detailOption.innerHTML = Translate('Detailed Summary');
                    selectView.appendChild(detailOption);
                viewInGroup.appendChild(selectView);
            viewColumn.appendChild(viewInGroup);
        filterRow.appendChild(viewColumn);

            let dateRangeField = cTag('div', {class:"columnXS6 columnSM4 daterangeContainer"});
                inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control search sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_date", 'value': sales_date});
            dateRangeField.appendChild(inputField);
        filterRow.appendChild(dateRangeField);

            let typeColumn = cTag('div', {class:"columnXS12 columnSM4"});
                let typeInGroup = cTag('div', {class:"input-group"});
                    const typeLabel = cTag('label', {'for':"report_type", class: "input-group-addon cursor"});
                    typeLabel.innerHTML = Translate('Type');
                typeInGroup.appendChild(typeLabel);
                    const selectType = cTag('select', {name: "report_type", id: "report_type", class: "form-control"});
                    selectType.addEventListener('change', AJ_sales_by_Date_MoreInfo);
                        const dailyOption = cTag('option', {'value': "Daily"});
                        dailyOption.innerHTML = Translate('Daily');
                    selectType.appendChild(dailyOption);
                        const weeklyOption = cTag('option', {'value': "Weekly"});
                        weeklyOption.innerHTML = Translate('Weekly');
                    selectType.appendChild(weeklyOption);
                        const monthlyOption = cTag('option', {'value': "Monthly"});
                        monthlyOption.innerHTML = Translate('Monthly');
                    selectType.appendChild(monthlyOption);
                typeInGroup.appendChild(selectType);
                    const searchField = cTag('span', {class:"input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Date wise Search')});
                    searchField.addEventListener('click', AJ_sales_by_Date_MoreInfo);
                        const searchIcon = cTag('i', {class:"fa fa-search"});
                    searchField.appendChild(searchIcon);
                typeInGroup.appendChild(searchField);
            typeColumn.appendChild(typeInGroup);
        filterRow.appendChild(typeColumn);
    showTableData.appendChild(filterRow);

        let searchResultColumn = cTag('div', {class:"columnXS12", 'style': "margin: 0;", id:"Searchresult"});
    showTableData.appendChild(searchResultColumn);

    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}

    checkAndSetSessionData('report_type', report_type, list_filters);
    checkAndSetSessionData('showing_type', showing_type, list_filters);
    if(list_filters.hasOwnProperty("sales_date")){
        sales_date = list_filters.sales_date;
    }
    document.getElementById("sales_date").value = sales_date;
    daterange_picker_dialog(document.getElementById("sales_date"));

    AJ_sales_by_Date_MoreInfo();
}

async function AJ_sales_by_Date_MoreInfo(){
    const jsonData = {};
    jsonData['showing_type'] = document.getElementById("showing_type").value;
    jsonData['sales_date'] = document.getElementById("sales_date").value;
    const report_type = document.getElementById("report_type").value;
    jsonData['report_type'] = report_type;

    const url = '/'+segment1+'/sales_by_DateData';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);

        let aTag, salesDateHeadRow, tdCol, grandTotalRow;
        const searchResult = document.getElementById("Searchresult");
        searchResult.innerHTML = '';
            grandTotalRow = cTag('div',{class:'flexSpaBetRow'})
                let allSalesButton = cTag('div',{class:'columnSM12', 'style': "text-align: right; margin: 0;", id:"filterby"})
                    let grandprofit_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px;margin-top: 5px; margin-bottom: 5px;"});
                    let gCost_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let gGrandTotal_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let gNontaxable_total_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let gTaxes_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let gtaxable_total_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                allSalesButton.append(gtaxable_total_button,gTaxes_button,gNontaxable_total_button,gGrandTotal_button,gCost_button,grandprofit_button);
            grandTotalRow.appendChild(allSalesButton);
        searchResult.appendChild(grandTotalRow);

            let dataTableRow = cTag('div',{class:'columnSM12'});
                const divNoMore = cTag('div', {id: "no-more-tables"});
                    const salesDateTable = cTag('table', {class: "bgnone table-bordered table-striped table-condensed cf listing"});
                        const salesDateHead = cTag('thead', {class: "cf"});                        
                        const tdAttributes = [{'datatitle':Translate('Sales Date'), 'align':'center'},
                                            {'datatitle':Translate('Taxable'), 'align':'right'},
                                            {'datatitle':Translate('Taxes'), 'align':'right'},
                                            {'datatitle':Translate('Non Taxable'), 'align':'right'},
                                            {'datatitle':Translate('Grand Total'), 'align':'right'},
                                            {'datatitle':Translate('Cost'), 'align':'right'},
                                            {'datatitle':Translate('Profit'), 'align':'right'}];
                        const uriStr = segment1+'/view';

                            salesDateHeadRow = cTag('tr');
                                const thCol0 = cTag('th', {'style': `text-align: center;`});
                                thCol0.innerHTML = tdAttributes[0].datatitle;

                                const thCol1 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                                thCol1.innerHTML = tdAttributes[1].datatitle;

                                const thCol2 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                                thCol2.innerHTML = tdAttributes[2].datatitle;

                                const thCol3 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol3.innerHTML = tdAttributes[3].datatitle;

                                const thCol4 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                                thCol4.innerHTML = tdAttributes[4].datatitle;

                                const thCol5 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol5.innerHTML = tdAttributes[5].datatitle;

                                const thCol6 = cTag('th', {'style': "text-align: right;", 'width': "15%"});
                                thCol6.innerHTML = tdAttributes[6].datatitle;
                            salesDateHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6);
                        salesDateHead.appendChild(salesDateHeadRow);
                    salesDateTable.appendChild(salesDateHead);
                        const salesDateBody = cTag('tbody');
                        let tableRows = data.tableData;
                        let gCost = 0;
                        let gGrandTotal = 0;
                        let gNontaxable_total = 0;
                        let gTaxes = 0;
                        let gtaxable_total = 0;
                        let gtaxable_totalexcl = 0;
                        if(tableRows.length>0){
                            tableRows.forEach(oneRow => {
                                let rTotal = calculate('add',oneRow.rtaxable_totalexcl,oneRow.rNontaxable_total,2);
                                let rProfit = calculate('sub',rTotal,oneRow.rCost,2);
                                let rowqtyprofit = 0;
                                if(rTotal !==0){
                                    rowqtyprofit = calculate('div',calculate('mul',rProfit,100,2),rTotal,2);
                                }
                                let rGrandTotal = calculate('add',oneRow.rTaxes,rTotal,2);

                                gCost = calculate('add',oneRow.rCost,gCost,2);
                                gGrandTotal = calculate('add',rGrandTotal,gGrandTotal,2);
                                gNontaxable_total = calculate('add',oneRow.rNontaxable_total,gNontaxable_total,2);
                                gTaxes = calculate('add',oneRow.rTaxes,gTaxes,2);
                                gtaxable_total = calculate('add',oneRow.rtaxable_total,gtaxable_total,2);
                                gtaxable_totalexcl = calculate('add',oneRow.rtaxable_totalexcl,gtaxable_totalexcl,2);
                                
                                //Set bold class
                                salesDateHeadRow = cTag('tr');
                                if(oneRow.boldclass !==''){
                                    salesDateHeadRow.style.fontWeight = 'bold';
                                }
                                    const tdCol0 = cTag('td', {'data-title': tdAttributes[0].datatitle});
                                    if(report_type=='Daily'){
                                        tdCol0.innerHTML = DBDateToViewDate(oneRow.sales_date, 0, 1);
                                    }
                                    else{
                                        tdCol0.innerHTML = DBDateRangeToViewDate(oneRow.sales_date, 0, 1);
                                    }                                        

                                    const tdCol1 = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                    tdCol1.innerHTML = addCurrency(oneRow.rtaxable_total);

                                    const tdCol2 = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                    tdCol2.innerHTML = addCurrency(oneRow.rTaxes);

                                    const tdCol3 = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                    tdCol3.innerHTML = addCurrency(oneRow.rNontaxable_total);

                                    const tdCol4 = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                    tdCol4.innerHTML = addCurrency(rGrandTotal);

                                    const tdCol5 = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                    tdCol5.innerHTML = addCurrency(oneRow.rCost);

                                    const tdCol6 = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});
                                    rProfit = addCurrency(rProfit);
                                    if(rowqtyprofit <0 ) rProfit += ' ('+rowqtyprofit*(-1)+'%)';
                                    else rProfit += ' ('+rowqtyprofit+'%)';
                                    tdCol6.innerHTML = rProfit;
                                salesDateHeadRow.append(tdCol0,tdCol1,tdCol2,tdCol3,tdCol4,tdCol5,tdCol6);
                                salesDateBody.appendChild(salesDateHeadRow);

                                if(oneRow.substrextra.length){
                                    let subData = oneRow.substrextra;
                                    subData.forEach(subOneRow => {
                                        let salesdatetime = DBDateToViewDate(subOneRow[0], 0, 1);
                                        let invoice_no = subOneRow[1];
                                        let customers_id = subOneRow[2];
                                        let customername = subOneRow[3];
                                        
                                        salesDateHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle});
                                                let invoice50 = cTag('span');
                                                    aTag = cTag('a', {'href': '/Invoices/view/'+invoice_no, 'style': "color: #009; text-decoration: underline;", title:Translate('View Invoice')});
                                                    aTag.append(invoice_no+' ',  cTag('i', {class:'fa fa-link'}));
                                                invoice50.append(salesdatetime+' ', aTag);
                                            tdCol.appendChild(invoice50); 
                                                let customer50 = cTag('span', {'style': "margin-left: 10px;"});
                                                    aTag = cTag('a', {'href': '/Customers/view/'+customers_id, title:Translate('View Customer Details')});                                                    
                                                    aTag.innerHTML = customername;
                                                customer50.appendChild(aTag);
                                            tdCol.appendChild(customer50);
                                        salesDateHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[4]);
                                        salesDateHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[5]);
                                        salesDateHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[6]);
                                        salesDateHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[7]);
                                        salesDateHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[8]);
                                        salesDateHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});

                                            let Profit = addCurrency(subOneRow[9]);
                                            if(subOneRow[10] <0 ) Profit += ' ('+round(subOneRow[10]*(-1),2)+'%)';
                                            else Profit += ' ('+round(subOneRow[10],2)+'%)';
                                    
                                            tdCol.innerHTML = Profit;
                                        salesDateHeadRow.appendChild(tdCol);
                                        salesDateBody.appendChild(salesDateHeadRow);
                                    });      
                                }
                            });
                        }
                        else{
                            let statusHeadRow = cTag('tr');
                                tdCol = cTag('td',{colspan:"7"});
                                tdCol.innerHTML = '';
                            statusHeadRow.appendChild(tdCol);
                            salesDateBody.appendChild(statusHeadRow);
                        }

                        let gProfit = calculate('sub',calculate('add',gtaxable_totalexcl,gNontaxable_total,2),gCost,2);
                        let grandprofit = 0;
                        if((gtaxable_totalexcl+gNontaxable_total) !==0){
                            grandprofit = calculate('div',calculate('mul',gProfit,100,2),calculate('add',gtaxable_totalexcl,gNontaxable_total,2),2);
                        }
                        gProfit = addCurrency(gProfit);
                        if(grandprofit <0 ) gProfit += ' ('+grandprofit*(-1)+'%)';
                        else gProfit += ' ('+grandprofit+'%)';
                        grandprofit_button.innerHTML = Translate('Profit')+' : '+gProfit;
                        gCost_button.innerHTML = Translate('Cost')+' : '+ addCurrency(gCost);
                        gGrandTotal_button.innerHTML = Translate('Grand Total')+' : '+ addCurrency(gGrandTotal);
                        if(gNontaxable_total) gNontaxable_total_button.innerHTML = Translate('Non Taxable')+' : '+ addCurrency(gNontaxable_total);
                        else gNontaxable_total_button.remove();
                        gTaxes_button.innerHTML = Translate('Taxes')+' : '+ addCurrency(gTaxes);
                        gtaxable_total_button.innerHTML = Translate('Taxable')+' : '+ addCurrency(gtaxable_total);

                    salesDateTable.appendChild(salesDateBody);
                divNoMore.appendChild(salesDateTable);
            dataTableRow.appendChild(divNoMore);
        searchResult.appendChild(dataTableRow);
    }
}

function sales_by_Employee(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));
    let employee = params.get("employee");

    let list_filters, inputField;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const salesPersonTitle = cTag('span', {id:"ptitle"});
                    salesPersonTitle.innerHTML = Translate('Sales by Sales Person')+' ';
                headerTitle.appendChild(salesPersonTitle);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Sales_report);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
            buttonsName.appendChild(printButton);
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click',function(){javascript:window.location='/Sales_reports/lists'});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' '+Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);

        inputField = cTag('input', {'type': "hidden", id: "twoSegments", 'value': "Sales_reports-sales_by_Employee"});
    showTableData.appendChild(inputField);
        inputField = cTag('input', {'type': "hidden", id: "pageURI", 'value': "Sales_reports/sales_by_Employee"});
    showTableData.appendChild(inputField);

        const salesEmployeeRow = cTag('div', {class:"flexSpaBetRow"});
            const viewColumn = cTag('div', {class:"columnXS6 columnSM4"});
                let divviewInGroup = cTag('div', {class:"input-group"});
                    const viewLabel = cTag('label', {class:"input-group-addon cursor", 'for': "showing_type"});
                    viewLabel.innerHTML = Translate('View');
                divviewInGroup.appendChild(viewLabel);
                    let selectSummary = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectSummary.addEventListener('change', AJ_sales_by_Employee_MoreInfo);
                        const summaryOption = cTag('option', {'value': "Summary"});
                        summaryOption.innerHTML = Translate('Summary');
                    selectSummary.appendChild(summaryOption);
                        const detailOption = cTag('option', {'value': "Details"});
                        detailOption.innerHTML = Translate('Detailed Summary');
                    selectSummary.appendChild(detailOption);
                divviewInGroup.appendChild(selectSummary);
            viewColumn.appendChild(divviewInGroup);
        salesEmployeeRow.appendChild(viewColumn);

            let dateRangeField = cTag('div', {class:"columnXS6 columnSM4 daterangeContainer"});
                inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control search sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_date", 'value': ""});
            dateRangeField.appendChild(inputField);
        salesEmployeeRow.appendChild(dateRangeField);

            let employeeSearch = cTag('div', {class:"columnXS12 columnSM4"});
                let employeeInSearch = cTag('div', {class:"input-group"});
                    const employeeSearchLabel = cTag('label', {'for':"employee", class: "input-group-addon cursor"});
                    employeeSearchLabel.innerHTML = Translate('Sales Person');
                employeeInSearch.appendChild(employeeSearchLabel);
                    inputField = cTag('input', {'maxlength': 50, 'type': "text", 'minlength': 23,   class: "form-control search ui-autocomplete-input", name: "employee", id: "employee", 'value': "", 'autocomplete': "off"});
                    inputField.addEventListener('keydown',event=>{if(event.which===13) AJ_sales_by_Employee_MoreInfo()});
                employeeInSearch.appendChild(inputField);
                    const searchField = cTag('span', {class:"input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Date wise Search')});
                    searchField.addEventListener('click', AJ_sales_by_Employee_MoreInfo);
                        const searchIcon = cTag('i', {class:"fa fa-search"});
                    searchField.appendChild(searchIcon);
                employeeInSearch.appendChild(searchField);
            employeeSearch.appendChild(employeeInSearch);
        salesEmployeeRow.appendChild(employeeSearch);
    showTableData.appendChild(salesEmployeeRow);

        let searchResultColumn = cTag('div', {class:"columnXS12", 'style': "margin: 0;", id:"Searchresult"});
    showTableData.appendChild(searchResultColumn);

    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}

    if(list_filters.hasOwnProperty("sales_date"))
        sales_date = list_filters.sales_date;
    document.getElementById("sales_date").value = sales_date;
    daterange_picker_dialog(document.getElementById("sales_date"));

    if(list_filters.hasOwnProperty("employee")){
        employee = list_filters.employee;
    }
    document.getElementById("employee").value = employee;

    checkAndSetSessionData('showing_type', 'Summary', list_filters);

    AJ_sales_by_Employee_MoreInfo();
}

async function AJ_sales_by_Employee_MoreInfo(){
	const jsonData = {};
	jsonData['sales_date'] = document.getElementById('sales_date').value;
	jsonData['showing_type'] = document.getElementById('showing_type').value;
	jsonData['employee'] = document.getElementById('employee').value;
	AJautoComplete('employee');
    
    const url = '/'+segment1+'/sales_by_EmployeeData';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);

        let salesEmployeeHeadRow, tdCol, grandTotalRow, col12;
        const Searchresult = document.getElementById("Searchresult");
        Searchresult.innerHTML = '';
            grandTotalRow = cTag('div',{class:'flexSpaBetRow'})
                col12 = cTag('div',{class:'columnSM12', 'style': "text-align: right; margin: 0;", id:"filterby"})
                    let gtaxable_total_button = cTag('button', {class:"btn reportButton", 'style': "margin-top: 5px; margin-bottom: 5px;"});
                col12.appendChild(gtaxable_total_button)
                    let taxestotalvalue_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                col12.appendChild(taxestotalvalue_button)
                    let gnontaxable_total_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                col12.appendChild(gnontaxable_total_button)
                    let grandtotalvalue_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                col12.appendChild(grandtotalvalue_button)
                    let totalcost_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                col12.appendChild(totalcost_button)
                    let gqtyprofitval_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                col12.appendChild(gqtyprofitval_button)
            grandTotalRow.appendChild(col12);
        Searchresult.appendChild(grandTotalRow);

        let dataTableRow = cTag('div',{class:'columnSM12'});
            const divNoMore = cTag('div', {id: "no-more-tables"});
                const salesEmployeeTable = cTag('table', {class: "bgnone table-bordered table-striped table-condensed cf listing"});
                    const salesEmployeeHead = cTag('thead', {class: "cf"});
                    
                    const tdAttributes = [{'datatitle':Translate('Sales Person'), 'align':'left'},
                                        {'datatitle':Translate('Taxable'), 'align':'right'},
                                        {'datatitle':Translate('Taxes'), 'align':'right'},
                                        {'datatitle':Translate('Non Taxable'), 'align':'right'},
                                        {'datatitle':Translate('Grand Total'), 'align':'right'},
                                        {'datatitle':Translate('Cost'), 'align':'right'},
                                        {'datatitle':Translate('Profit'), 'align':'right'}];
                    const uriStr = segment1+'/view';

                        salesEmployeeHeadRow = cTag('tr');
                            const thCol0 = cTag('th', {'style': `text-align: left;`});
                            thCol0.innerHTML = tdAttributes[0].datatitle;

                            const thCol1 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol1.innerHTML = tdAttributes[1].datatitle;

                            const thCol2 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol2.innerHTML = tdAttributes[2].datatitle;

                            const thCol3 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol3.innerHTML = tdAttributes[3].datatitle;

                            const thCol4 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol4.innerHTML = tdAttributes[4].datatitle;

                            const thCol5 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                            thCol5.innerHTML = tdAttributes[5].datatitle;

                            const thCol6 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                            thCol6.innerHTML = tdAttributes[6].datatitle;
                        salesEmployeeHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6);
                    salesEmployeeHead.appendChild(salesEmployeeHeadRow);
                salesEmployeeTable.appendChild(salesEmployeeHead);

                    const salesEmployeeBody = cTag('tbody');
                        let tableRows = data.tableData;
                        let gtaxable_totalexcl = 0;
                        let totalcost = 0;
                        let gnontaxable_total = 0;
                        let taxestotalvalue = 0;
                        let gtaxable_total = 0;

                        if(tableRows.length>0){
                            tableRows.forEach(oneRow => {
                                let rowgrand_total = calculate('add',calculate('add',oneRow.rtaxable_totalexcl,oneRow.rTaxes,2),oneRow.rNontaxable_total,2);                                
                                let qtyprofitval = calculate('sub',calculate('add',oneRow.rtaxable_totalexcl,oneRow.rNontaxable_total,2),oneRow.rCost,2);
                                let qtyprofit = 0;
                                if((oneRow.rtaxable_totalexcl+oneRow.rNontaxable_total) !==0){
                                    qtyprofit = calculate('div',calculate('mul',qtyprofitval,100,2),calculate('add',oneRow.rtaxable_totalexcl,oneRow.rNontaxable_total,2),2);
                                }
                                
                                gtaxable_totalexcl = calculate('add',oneRow.rtaxable_totalexcl,gtaxable_totalexcl,2);
                                totalcost = calculate('add',oneRow.rCost,totalcost,2);
                                gnontaxable_total = calculate('add',oneRow.rNontaxable_total,gnontaxable_total,2);
                                taxestotalvalue = calculate('add',oneRow.rTaxes,taxestotalvalue,2);
                                gtaxable_total = calculate('add',oneRow.rtaxable_total,gtaxable_total,2);
                                //Set bold class
                                salesEmployeeHeadRow = cTag('tr');
                                if(oneRow.boldclass !==''){
                                    salesEmployeeHeadRow.style.fontWeight = 'bold';
                                }
                                    const tdCol0 = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                    tdCol0.innerHTML = oneRow.employeename;

                                    const tdCol1 = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                    tdCol1.innerHTML = addCurrency(oneRow.rtaxable_total);

                                    const tdCol2 = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                    tdCol2.innerHTML = addCurrency(oneRow.rTaxes);

                                    const tdCol3 = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                    tdCol3.innerHTML = addCurrency(oneRow.rNontaxable_total);

                                    const tdCol4 = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                    tdCol4.innerHTML = addCurrency(rowgrand_total);

                                    const tdCol5 = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                    tdCol5.innerHTML = addCurrency(oneRow.rCost);

                                    const tdCol6 = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});                                    
                                    qtyprofitval = addCurrency(qtyprofitval);
                                    if(qtyprofit <0 ) qtyprofitval += ' ('+qtyprofit*(-1)+'%)';
                                    else qtyprofitval += ' ('+qtyprofit+'%)';                                    
                                    tdCol6.innerHTML = qtyprofitval;
                                salesEmployeeHeadRow.append(tdCol0,tdCol1,tdCol2,tdCol3,tdCol4,tdCol5,tdCol6);
                                salesEmployeeBody.appendChild(salesEmployeeHeadRow);

                                if(oneRow.substrextra.length){
                                    let subData = oneRow.substrextra;
                                    subData.forEach(subOneRow => {
                                        let salesdatetime = DBDateToViewDate(subOneRow[0], 0, 1);
                                        let invoice_no = subOneRow[1];
                                        let customer_id = subOneRow[2];
                                        let customername = subOneRow[3];

                                        salesEmployeeHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                                let invoiceLink50 = cTag('span');
                                                    const invoiceLink = cTag('a', {'href': '/Invoices/view/'+invoice_no, 'style': "color: #009; text-decoration: underline;", title:Translate('View Invoice')});
                                                    invoiceLink.append(invoice_no+' ',  cTag('i', {class:'fa fa-link'}));
                                                invoiceLink50.append(salesdatetime+' ', invoiceLink);
                                            tdCol.appendChild(invoiceLink50); 
                                                let customerLink50 = cTag('span', {class:"columnSM6", 'style': "margin-left: 10px;"});
                                                    const customerLink = cTag('a', {'href': '/Customers/view/'+customer_id, title:Translate('View Customer Details')});                                                    
                                                    customerLink.innerHTML = customername;
                                                customerLink50.appendChild(customerLink);
                                            tdCol.appendChild(customerLink50);
                                        salesEmployeeHeadRow.appendChild(tdCol);

                                            tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[4]);
                                        salesEmployeeHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[5]);
                                        salesEmployeeHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[6]);
                                        salesEmployeeHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[7]);
                                        salesEmployeeHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[8]);
                                        salesEmployeeHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});
                                            
                                            let Profit = addCurrency(subOneRow[9]);
                                            if(subOneRow[10] <0 ) Profit += ' ('+round(subOneRow[10]*(-1),2)+'%)';
                                            else Profit += ' ('+round(subOneRow[10],2)+'%)';
                                            
                                            tdCol.innerHTML = Profit;
                                        salesEmployeeHeadRow.appendChild(tdCol);
                                        salesEmployeeBody.appendChild(salesEmployeeHeadRow);
                                    });
                                }
                            })
                        }
                        else{
                            let salesHeadRow = cTag('tr');
                                tdCol = cTag('td',{colspan:"7"});
                                tdCol.innerHTML = '';
                            salesHeadRow.appendChild(tdCol);
                            salesEmployeeBody.appendChild(salesHeadRow);
                        }

                        let qtyprofitval = calculate('sub',calculate('add',gtaxable_totalexcl,gnontaxable_total,2),totalcost,2);
                        let qtyprofit = 0;
                        if((gtaxable_totalexcl+gnontaxable_total) !==0){
                            qtyprofit = calculate('div',calculate('mul',qtyprofitval,100,2),calculate('add',gtaxable_totalexcl,gnontaxable_total,2),2);
                        }

                        let grandtotalvalue = calculate('add',gtaxable_totalexcl,calculate('add',taxestotalvalue,gnontaxable_total,2),2);
                        
                        qtyprofitval = addCurrency(qtyprofitval);
                        if(qtyprofit <0 ) qtyprofitval += ' ('+qtyprofit*(-1)+'%)';
                        else qtyprofitval += ' ('+qtyprofit+'%)';
                        gqtyprofitval_button.innerHTML = Translate('Profit')+' : '+ qtyprofitval;
                        totalcost_button.innerHTML = Translate('Cost')+' : '+ addCurrency(totalcost);
                        grandtotalvalue_button.innerHTML = Translate('Grand Total')+' : '+ addCurrency(grandtotalvalue);
                        if(gnontaxable_total) gnontaxable_total_button.innerHTML = `${Translate('Non Taxable')} : ${addCurrency(gnontaxable_total)}`;
                        else gnontaxable_total_button.remove();
                        taxestotalvalue_button.innerHTML = Translate('Taxes')+' : '+ addCurrency(taxestotalvalue);
                        gtaxable_total_button.innerHTML = Translate('Taxable')+' : '+ addCurrency(gtaxable_total);
                salesEmployeeTable.appendChild(salesEmployeeBody);
            divNoMore.appendChild(salesEmployeeTable);
        dataTableRow.appendChild(divNoMore);
        Searchresult.appendChild(dataTableRow);
    }
}

function sales_by_Customer(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));
    let customer_name = params.get("customer_name");
    let customers_id = params.get("customers_id");
    
    let list_filters, inputField;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Sales by Customer')+' ';
                headerTitle.appendChild(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Sales_report);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click',function(){javascript:window.location='/Sales_reports/lists'});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' '+Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);

        inputField = cTag('input', {'type': "hidden", id: "twoSegments", 'value': "Sales_reports-sales_by_Customer"});
    showTableData.appendChild(inputField);
        inputField = cTag('input', {'type': "hidden", id: "pageURI", 'value': "Sales_reports/sales_by_Customer"});
    showTableData.appendChild(inputField);

        const salesCustomerRow = cTag('div', {class:"flexSpaBetRow"});
            const dropDownColumn = cTag('div', {class:"columnXS6 columnSM4 columnLG2"});
                const selectOrder = cTag('select', {name: "sorder_by", id: "sorder_by", class: "form-control"});
                selectOrder.addEventListener('change', sales_by_CustomerData);
                    const totalOption = cTag('option', {'value': "total"});
                    totalOption.innerHTML = Translate('Sort by Total');
                selectOrder.appendChild(totalOption);
                    const nameOption = cTag('option', {'value': "customer"});
                    nameOption.innerHTML = Translate('Sort by Name');
                selectOrder.appendChild(nameOption);
            dropDownColumn.appendChild(selectOrder);
        salesCustomerRow.appendChild(dropDownColumn);

            const viewColumn = cTag('div', {class:"columnXS6 columnSM4 columnLG2"});
                let viewInGroup = cTag('div', {class:"input-group"});
                    let viewLabel = cTag('label', {class:"input-group-addon cursor", 'for': "showing_type"});
                    viewLabel.innerHTML = Translate('View');
                viewInGroup.appendChild(viewLabel);
                    const selectShowingType = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectShowingType.addEventListener('change', sales_by_CustomerData);
                        const summaryOption = cTag('option', {'value': "Summary"});
                        summaryOption.innerHTML = Translate('Summary');
                    selectShowingType.appendChild(summaryOption);
                        const detailedOption = cTag('option', {'value': "Details"});
                        detailedOption.innerHTML = Translate('Detailed Summary');
                    selectShowingType.appendChild(detailedOption);
                viewInGroup.appendChild(selectShowingType);
            viewColumn.appendChild(viewInGroup);
        salesCustomerRow.appendChild(viewColumn);

            let dateRangeField = cTag('div', {class:"columnXS6 columnSM4 columnLG3 daterangeContainer"});
                inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control search sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_date", 'value': ""});
            dateRangeField.appendChild(inputField);
        salesCustomerRow.appendChild(dateRangeField);

            const customerTypeColumn = cTag('div', {class:"columnXS6 columnLG2"});
                const selectCustomerType = cTag('select', {name: "customer_type", id: "customer_type", class: "form-control"});
                selectCustomerType.addEventListener('change', sales_by_CustomerData);
                    const customerTypeOption = cTag('option', {'value': "All"});
                    customerTypeOption.innerHTML = Translate('All Customer Type');
                selectCustomerType.appendChild(customerTypeOption);
            customerTypeColumn.appendChild(selectCustomerType);
        salesCustomerRow.appendChild(customerTypeColumn);

            const searchColumn = cTag('div', {class:"columnXS12 columnSM6 columnLG3"});
                let searchInGroup = cTag('div', {class:"input-group"});
                    inputField = cTag('input', {'maxlength': 50, 'type': "text", class: "form-control search", name: "customer_name", id: "customer_name", 'value': "", 'placeholder': Translate('Customer Name'), 'autocomplete': "off"});
                    inputField.addEventListener('keydown',event=>{if(event.which===13) sales_by_CustomerData()});
                searchInGroup.appendChild(inputField);
                    inputField = cTag('input', {'type': "hidden", name: "customers_id", id: "customers_id", 'value': customers_id});
                searchInGroup.appendChild(inputField);
                    const searchSpan = cTag('span', {class:"input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Date wise Search')});
                    searchSpan.addEventListener('click', sales_by_CustomerData);
                        const searchIcon = cTag('i', {class:"fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                searchInGroup.appendChild(searchSpan);
            searchColumn.appendChild(searchInGroup);
        salesCustomerRow.appendChild(searchColumn);
    showTableData.appendChild(salesCustomerRow);

        let searchResultColumn = cTag('div', {class:"columnXS12", 'style': "margin: 0;", id:"Searchresult"});
    showTableData.appendChild(searchResultColumn);

    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}

    checkAndSetSessionData('sorder_by', 'total', list_filters);
    checkAndSetSessionData('showing_type', 'Summary', list_filters);

    if(list_filters.hasOwnProperty("sales_date")){
        sales_date = list_filters.sales_date;
    }
    document.getElementById("sales_date").value = sales_date;
    daterange_picker_dialog(document.getElementById("sales_date"));

    checkAndSetSessionData('customer_type', 'All', list_filters);

    if(list_filters.hasOwnProperty("customer_name")){
        customer_name = list_filters.customer_name;
    }
    document.getElementById("customer_name").value = customer_name;

    AJ_sales_by_Customer_MoreInfo();
}

async function AJ_sales_by_Customer_MoreInfo(){
	const jsonData = {};
	jsonData['customers_id'] = document.getElementById('customers_id').value;
	jsonData['customer_type'] = document.getElementById('customer_type').value;
    if(document.getElementById("customer_name")){AJautoComplete('customer_name');}

    const url = '/'+segment1+'/AJ_sales_by_Customer_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let customer_type = document.getElementById("customer_type");
        customer_type.innerHTML = '';
            let customerTypeOption = cTag('option', {'value': 'All'});
            customerTypeOption.innerHTML = Translate('All Customer Type');
            customer_type.appendChild(customerTypeOption);
        setOptions(customer_type, data.cusTypeOpt, 0, 1);
        customer_type.value = jsonData['customer_type'];

        sales_by_CustomerData();
    }
}

async function sales_by_CustomerData(){
	const jsonData = {};
	jsonData['sorder_by'] = document.getElementById('sorder_by').value;
	jsonData['sales_date'] = document.getElementById('sales_date').value;
	jsonData['showing_type'] = document.getElementById('showing_type').value;
	jsonData['customer_type'] = document.getElementById('customer_type').value;
	jsonData['customer_name'] = document.getElementById('customer_name').value;
	jsonData['customers_id'] = document.getElementById('customers_id').value;

    const url = '/'+segment1+'/sales_by_CustomerData';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        storeSessionData(jsonData);

        let salesCustomerHeadRow, tdCol, grandTotalRow, col12;
        const searchResult = document.getElementById("Searchresult");
        searchResult.innerHTML = '';
            grandTotalRow = cTag('div',{class:'flexSpaBetRow'})
                col12 = cTag('div',{class:'columnSM12', 'style': "text-align: right; margin: 0;", id:"filterby"});
                    let grandtotalvalue_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px;margin-top: 5px; margin-bottom: 5px;"});
                    let taxestotalvalue_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let gtaxable_total_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                col12.append(gtaxable_total_button,taxestotalvalue_button,grandtotalvalue_button);
            grandTotalRow.appendChild(col12);
        searchResult.appendChild(grandTotalRow);

            let dataTableRow = cTag('div',{class:'columnSM12'})
                const divNoMore = cTag('div', {id: "no-more-tables"});
                    const salesCustomerTable = cTag('table', {class: "bgnone table-bordered table-striped table-condensed cf listing"});
                        const salesCustomerHead = cTag('thead', {class: "cf"});

                        const tdAttributes = [{'datatitle':Translate('Customer info'), 'align':'left'},
                                            {'datatitle':Translate('Taxable'), 'align':'right'},
                                            {'datatitle':Translate('Taxes'), 'align':'right'},
                                            {'datatitle':Translate('Non Taxable'), 'align':'right'},
                                            {'datatitle':Translate('Grand Total'), 'align':'right'}];
                            const uriStr = segment1+'/view';

                            salesCustomerHeadRow = cTag('tr');
                                const thCol0 = cTag('th', {'style': `text-align: left;`});
                                thCol0.innerHTML = tdAttributes[0].datatitle;

                                const thCol1 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                                thCol1.innerHTML = tdAttributes[1].datatitle;

                                const thCol2 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                                thCol2.innerHTML = tdAttributes[2].datatitle;

                                const thCol3 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                                thCol3.innerHTML = tdAttributes[3].datatitle;

                                const thCol4 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                                thCol4.innerHTML = tdAttributes[4].datatitle;
                            salesCustomerHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
                        salesCustomerHead.appendChild(salesCustomerHeadRow);
                    salesCustomerTable.appendChild(salesCustomerHead);

                        const salesCustomerBody = cTag('tbody');
                        let gtaxable_totalexcl = 0;
                        let taxestotalvalue = 0;
                        let gtaxable_total = 0;
                        let gnontaxable_total = 0;
                        let tableRows = data.tableData;

                        if(tableRows.length>0){
                            tableRows.forEach(oneRow => {
                                let rowgrand_total = calculate('add',oneRow.rtaxable_totalexcl,calculate('add',oneRow.rowtotaltaxes,oneRow.rowtotalnontaxable,2),2);
                                gtaxable_totalexcl = calculate('add',oneRow.rtaxable_totalexcl,gtaxable_totalexcl,2);
                                taxestotalvalue = calculate('add',oneRow.rowtotaltaxes,taxestotalvalue,2);
                                gtaxable_total = calculate('add',oneRow.rtaxable_total,gtaxable_total,2);
                                gnontaxable_total = calculate('add',oneRow.rowtotalnontaxable,gnontaxable_total,2);
                                //Set bold class
                                salesCustomerHeadRow = cTag('tr');
                                if(oneRow.boldclass !==''){
                                    salesCustomerHeadRow.style.fontWeight = 'bold';
                                }
                                    tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                        let customerLink = cTag('a',{'href':`/Customers/view/${oneRow.customer_id}`, title:"View Customer Details"});
                                        customerLink.innerHTML = oneRow.customername;
                                    tdCol.appendChild(customerLink);
                                salesCustomerHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                    tdCol.innerHTML = addCurrency(oneRow.rtaxable_total);
                                salesCustomerHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                    tdCol.innerHTML = addCurrency(oneRow.rowtotaltaxes);
                                salesCustomerHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                    tdCol.innerHTML = addCurrency(oneRow.rowtotalnontaxable);
                                salesCustomerHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                    tdCol.innerHTML = addCurrency(rowgrand_total);
                                salesCustomerHeadRow.appendChild(tdCol);
                            salesCustomerBody.appendChild(salesCustomerHeadRow);

                                if(oneRow.substrextra.length){
                                    let subData = oneRow.substrextra;
                                    subData.forEach(subOneRow => {
                                        let salesdatetime = DBDateToViewDate(subOneRow[0], 0, 1);
                                        let invoice_no = subOneRow[1];

                                        salesCustomerHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'data-title':Translate('Sale Date'), align:'left'});
                                            tdCol.append(salesdatetime+'     ');
                                                let invoiceLink = cTag('a', {'href': '/Invoices/view/'+invoice_no, 'style': "color: #009; text-decoration: underline;", title:Translate('View Invoice')});
                                                invoiceLink.append(invoice_no+' ',  cTag('i', {class:'fa fa-link'}));
                                            tdCol.appendChild(invoiceLink); 
                                        salesCustomerHeadRow.appendChild(tdCol);

                                            tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[2]);
                                        salesCustomerHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[3]);
                                        salesCustomerHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[4]);
                                        salesCustomerHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[5]);
                                        salesCustomerHeadRow.appendChild(tdCol);
                                        salesCustomerBody.appendChild(salesCustomerHeadRow);
                                    })
                                }
                            })
                        }
                        else{
                            let customerHeadRow = cTag('tr');
                                tdCol = cTag('td',{colspan:"5"});
                                tdCol.innerHTML = '';
                            customerHeadRow.appendChild(tdCol);
                            salesCustomerBody.appendChild(customerHeadRow);
                        }

                        let grandtotalvalue = calculate('add',gtaxable_totalexcl,calculate('add',taxestotalvalue,gnontaxable_total,2),2);
                        grandtotalvalue_button.innerHTML = Translate('Grand Total')+' : '+ addCurrency(grandtotalvalue);
                        taxestotalvalue_button.innerHTML = Translate('Taxes')+' : '+ addCurrency(taxestotalvalue);
                        gtaxable_total_button.innerHTML = Translate('Taxable')+' : '+ addCurrency(gtaxable_total);

                    salesCustomerTable.appendChild(salesCustomerBody);
                divNoMore.appendChild(salesCustomerTable);
            dataTableRow.appendChild(divNoMore);
        searchResult.appendChild(dataTableRow);
    }
}

function sales_by_Paymenttype(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));
    let paymenttype = params.get("paymenttype");

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        let list_filters, inputField, allPaymentOption;
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Payments Received by Type')+' ';
                headerTitle.appendChild(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Sales_report);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click',function(){javascript:window.location='/Sales_reports/lists'});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' '+Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);
        inputField = cTag('input', {'type': "hidden", id: "twoSegments", 'value': "Sales_reports-sales_by_Paymenttype"});
    showTableData.appendChild(inputField);
        inputField = cTag('input', {'type': "hidden", id: "pageURI", 'value': "Sales_reports/sales_by_Paymenttype"});
    showTableData.appendChild(inputField);

    let paymentTypeRow = cTag('div', {class:"flexSpaBetRow"});
        let viewColumn = cTag('div', {class:"columnXS6 columnLG3"});
            let viewInGroup = cTag('div', {class:"input-group"});
                const viewLabel = cTag('label', {class: "input-group-addon cursor", 'for': "showing_type"});
                viewLabel.innerHTML = Translate('View');
            viewInGroup.appendChild(viewLabel);
                const selectPaymentType = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                selectPaymentType.addEventListener('change', Sales_by_PaymenttypeData);
                    const summaryOption = cTag('option', {'value': "Summary"});
                    summaryOption.innerHTML = Translate('Summary');
                selectPaymentType.appendChild(summaryOption);
                    const detailOption = cTag('option', {'value': "Details"});
                    detailOption.innerHTML = Translate('Detailed Summary');
                selectPaymentType.appendChild(detailOption);
            viewInGroup.appendChild(selectPaymentType);
        viewColumn.appendChild(viewInGroup);
    paymentTypeRow.appendChild(viewColumn);

        let dateRangeField = cTag('div', {class:"columnXS6 columnLG3 daterangeContainer"});
            inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control search sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_date", 'value': ""});
        dateRangeField.appendChild(inputField);
    paymentTypeRow.appendChild(dateRangeField);

        const userColumn = cTag('div', {class:"columnXS6 columnLG2"});
            const selectUser = cTag('select', {name: "puser_id", id: "puser_id", class: "form-control"});
            selectUser.addEventListener('change', Sales_by_PaymenttypeData);
                const userOption = cTag('option', {'value': 0});
                userOption.innerHTML = Translate('All Users');
            selectUser.appendChild(userOption);
        userColumn.appendChild(selectUser);
    paymentTypeRow.appendChild(userColumn);

        const allPaymentColumn = cTag('div', {class:"columnXS6 columnLG4"});
            let allPaymentInGroup = cTag('div', {class:"input-group"});
                const paymentLabel = cTag('label', {'for': "paymenttype", class: "input-group-addon cursor"});
                paymentLabel.innerHTML = Translate('Payment Type');
            allPaymentInGroup.appendChild(paymentLabel);
                const selectPayment = cTag('select', {id: "paymenttype", name: "paymenttype", class: "form-control"});
                selectPayment.addEventListener('change', Sales_by_PaymenttypeData);
                    allPaymentOption = cTag('option', {'value': ""});
                    allPaymentOption.innerHTML = Translate('All Payment Types');
                selectPayment.appendChild(allPaymentOption);
                    allPaymentOption = cTag('option', {'value': paymenttype});
                    allPaymentOption.innerHTML = paymenttype;
                selectPayment.appendChild(allPaymentOption);
            allPaymentInGroup.appendChild(selectPayment);
                const searchSpan = cTag('span', {class:"input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title:"", 'data-original-title': Translate('Date wise Search')});
                searchSpan.addEventListener('click', Sales_by_PaymenttypeData);
                    const searchIcon = cTag('i', {class:"fa fa-search"});
                searchSpan.appendChild(searchIcon);
            allPaymentInGroup.appendChild(searchSpan);
        allPaymentColumn.appendChild(allPaymentInGroup);
    paymentTypeRow.appendChild(allPaymentColumn);
    showTableData.appendChild(paymentTypeRow);

        let searchResultColumn = cTag('div', {class:"columnXS12", 'style': "margin: 0;", id:"Searchresult"});
    showTableData.appendChild(searchResultColumn);

    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}

    checkAndSetSessionData('showing_type', 'Summary', list_filters);

    if(list_filters.hasOwnProperty("sales_date")){
        sales_date = list_filters.sales_date;
    }
    document.getElementById("sales_date").value = sales_date;
    daterange_picker_dialog(document.getElementById("sales_date"));

    checkAndSetSessionData('puser_id', 0, list_filters);
    checkAndSetSessionData('paymenttype', paymenttype, list_filters);

    AJ_Sales_by_Paymenttype_MoreInfo();
}

async function AJ_Sales_by_Paymenttype_MoreInfo(){
	const jsonData = {};
	jsonData['showing_type'] = document.getElementById('showing_type').value;
    let puser_idObj = document.getElementById('puser_id');
	jsonData['puser_id'] = puser_idObj.value
    let paymenttypeObj = document.getElementById('paymenttype');
	jsonData['paymenttype'] = paymenttypeObj.value;

    const url = '/'+segment1+'/AJ_Sales_by_Paymenttype_MoreInfo';
    fetchData(afterFetch,url,jsonData);
    
    function afterFetch(data){
        let option;
        puser_idObj.innerHTML = '';
            option = cTag('option', {'value': 0});
            option.innerHTML = Translate('All Users');
        puser_idObj.appendChild(option);
        setOptions(puser_idObj, data.useNamOpt, 1, 1);
        puser_idObj.value = jsonData['puser_id'];

        paymenttypeObj.innerHTML = '';
            option = cTag('option', {'value': ''});
            option.innerHTML = Translate('All Payment Types');
        paymenttypeObj.appendChild(option);
        setOptions(paymenttypeObj, data.poData, 0, 1);
        paymenttypeObj.value = jsonData['paymenttype'];

        Sales_by_PaymenttypeData();
    }
}

async function Sales_by_PaymenttypeData(){
	const jsonData = {};
	jsonData['sales_date'] = document.getElementById('sales_date').value;
	jsonData['showing_type'] = document.getElementById('showing_type').value;
	jsonData['puser_id'] = document.getElementById('puser_id').value;
	jsonData['paymenttype'] = document.getElementById('paymenttype').value;

    const url = '/'+segment1+'/Sales_by_PaymenttypeData';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);

        let paymentTypeHeadRow, tdCol, grandTotalRow, col12;
        const Searchresult = document.getElementById("Searchresult");
        Searchresult.innerHTML = '';
            grandTotalRow = cTag('div',{class:'flexSpaBetRow'})
                col12 = cTag('div',{class:'columnSM12', 'style': "text-align: right; margin: 0;", id:"filterby"})
                    let payment_amounttotalvalue_button = cTag('button', {class:"btn reportButton"});
                col12.appendChild(payment_amounttotalvalue_button)
            grandTotalRow.appendChild(col12);
        Searchresult.appendChild(grandTotalRow);

        let dataTableRow = cTag('div',{class:'columnSM12'})
            const divNoMore = cTag('div', {id: "no-more-tables"});
                const paymentTypeTable = cTag('table', {class: "bgnone table-bordered table-striped table-condensed cf listing"});
                    const paymentTypeHead = cTag('thead', {class: "cf"});
                    
                    const tdAttributes = [{'datatitle':Translate('Payment Type'), 'align':'left'},
                                        {'datatitle':Translate('Total'), 'align':'right'}];
                    const uriStr = segment1+'/view';

                        paymentTypeHeadRow = cTag('tr');
                            const thCol0 = cTag('th', {'align': "left"});
                            thCol0.innerHTML = tdAttributes[0].datatitle;

                            const thCol1 = cTag('th', {'style': "text-align: right;", 'width': "25%"});
                            thCol1.innerHTML = tdAttributes[1].datatitle;
                        paymentTypeHeadRow.append(thCol0,thCol1);
                    paymentTypeHead.appendChild(paymentTypeHeadRow);
                paymentTypeTable.appendChild(paymentTypeHead);

                    const paymentTypeBody = cTag('tbody');
                    let tableRows = data.tableData;
                    let payment_amounttotalvalue = 0;

                    if(tableRows.length){
                        tableRows.forEach(oneRow => {
                            payment_amounttotalvalue = calculate('add',oneRow.total_payment_amount,payment_amounttotalvalue,2);
                            paymentTypeHeadRow = cTag('tr');
                            //set bold class
                            if(oneRow.boldclass !==''){
                                paymentTypeHeadRow.style.fontWeight = 'bold';
                            }
                                tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                tdCol.innerHTML = oneRow.payment_method;
                            paymentTypeHeadRow.appendChild(tdCol);
                                tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                tdCol.innerHTML = addCurrency(oneRow.total_payment_amount);
                            paymentTypeHeadRow.appendChild(tdCol);
                            paymentTypeBody.appendChild(paymentTypeHeadRow);

                            let subData = oneRow.substrextra;
                            if(subData.length){
                                subData.forEach(subOneRow => {
                                    let paymentdatetime = DBDateToViewDate(subOneRow[0], 0, 1);
                                    let invoice_no = subOneRow[1];
                                    let linkUrl = subOneRow[2];
                                    let totalpayment_amountstr = addCurrency(subOneRow[3]);

                                    paymentTypeHeadRow = cTag('tr');
                                        tdCol = cTag('td', {'data-title':Translate('Payment Type'), align:'left'});
                                        tdCol.innerHTML = paymentdatetime;
                                        if(linkUrl !==''){
                                            let aTag = cTag('a', {'href': linkUrl, 'style': "color: #009; text-decoration: underline;", title:Translate('View Invoice')});
                                            aTag.append(invoice_no+' ',  cTag('i', {class:'fa fa-link'}));
                                            tdCol.append('\u2003 \u2003', aTag);
                                        }
                                    paymentTypeHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                        tdCol.innerHTML = totalpayment_amountstr;
                                    paymentTypeHeadRow.appendChild(tdCol);
                                    paymentTypeBody.appendChild(paymentTypeHeadRow);
                                });
                            }
                        });
                    }
                    else{
                        let paymentHeadRow = cTag('tr');
                            tdCol = cTag('td',{colspan:"2"});
                            tdCol.innerHTML = '';
                        paymentHeadRow.appendChild(tdCol);
                        paymentTypeBody.appendChild(paymentHeadRow);
                    }
                    payment_amounttotalvalue_button.innerHTML = Translate('Total')+' : '+ addCurrency(payment_amounttotalvalue);
                paymentTypeTable.appendChild(paymentTypeBody);
            divNoMore.appendChild(paymentTypeTable);
        dataTableRow.appendChild(divNoMore);
        Searchresult.appendChild(dataTableRow);
    }
}

function sales_by_Product(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));
    let product = params.get("product");
    let sku = params.get("sku");

    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        let list_filters, inputField;
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Sales by Product')+' ';
                headerTitle.appendChild(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Sales_report);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click',function(){javascript:window.location='/Sales_reports/lists'});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' '+Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);

        inputField = cTag('input', {'type': "hidden", id: "twoSegments", 'value': "Sales_reports-sales_by_Product"});
    showTableData.appendChild(inputField);
        inputField = cTag('input', {'type': "hidden", id: "pageURI", 'value': "Sales_reports/sales_by_Product"});
    showTableData.appendChild(inputField);

        const salesProductRow = cTag('div', {class:"flexSpaBetRow"});
            const viewColumn = cTag('div', {class:"columnXS6 columnSM4"});
                let viewInGroup = cTag('div', {class:"input-group"});
                    const viewLabel = cTag('label', {class:"input-group-addon cursor", 'for': "showing_type"});
                    viewLabel.innerHTML = Translate('View');
                viewInGroup.appendChild(viewLabel);
                    let selectShowingType = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectShowingType.addEventListener('change', AJ_sales_by_Product_MoreInfo);
                        const summaryOption = cTag('option', {'value': "Summary"});
                        summaryOption.innerHTML = Translate('Summary');
                    selectShowingType.appendChild(summaryOption);
                        const detailOption = cTag('option', {'value': "Details"});
                        detailOption.innerHTML = Translate('Detailed Summary');
                    selectShowingType.appendChild(detailOption);
                viewInGroup.appendChild(selectShowingType);
            viewColumn.appendChild(viewInGroup);
        salesProductRow.appendChild(viewColumn);

            let dateRangeField = cTag('div', {class:"columnXS6 columnSM4 daterangeContainer"});
                inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control search sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_date", 'value': ""});
            dateRangeField.appendChild(inputField);
        salesProductRow.appendChild(dateRangeField);

            let searchProductColumn = cTag('div', {class:"columnXS12 columnSM4"});
                let productInGroup = cTag('div', {class:"input-group"});
                    const productLabel = cTag('label', {'for':"product", class: "input-group-addon cursor"});
                    productLabel.innerHTML = Translate('Product');
                productInGroup.appendChild(productLabel);
                    inputField = cTag('input', {'maxlength': 50, 'type': "text", class: "form-control search ui-autocomplete-input", name: "product", id: "product", 'value': "", 'placeholder': Translate('Product Name/SKU'), 'autocomplete': "off"});
                    inputField.addEventListener('keydown',event=>{if(event.which===13) AJ_sales_by_Product_MoreInfo()});
                productInGroup.appendChild(inputField);
                    inputField = cTag('input', {'type': "hidden", name: "sku", id: "sku", 'value': sku});
                productInGroup.appendChild(inputField);
                    const searchSpan = cTag('span', {class:"input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Product Name/SKU')});
                    searchSpan.addEventListener('click', AJ_sales_by_Product_MoreInfo);
                        const searchIcon = cTag('i', {class:"fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                productInGroup.appendChild(searchSpan);
            searchProductColumn.appendChild(productInGroup);
        salesProductRow.appendChild(searchProductColumn);
    showTableData.appendChild(salesProductRow);

        let searchResultColumn = cTag('div', {class:"columnXS12", 'style': "margin: 0;", id:"Searchresult"});
    showTableData.appendChild(searchResultColumn);

    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}

    checkAndSetSessionData('showing_type', 'Summary', list_filters);

    if(list_filters.hasOwnProperty("sales_date")){
        sales_date = list_filters.sales_date;
    }
    document.getElementById("sales_date").value = sales_date;
    daterange_picker_dialog(document.getElementById("sales_date"));

    if(list_filters.hasOwnProperty("product")){
        product = list_filters.product;
    }
    document.getElementById("product").value = product;

    AJ_sales_by_Product_MoreInfo();
}

async function AJ_sales_by_Product_MoreInfo(){
	const jsonData = {};
	jsonData['sales_date'] = document.getElementById('sales_date').value;
	jsonData['showing_type'] = document.getElementById('showing_type').value;
	jsonData['product'] = document.getElementById('product').value;
	jsonData['sku'] = document.getElementById('sku').value;
    AJautoComplete('product');

    const url = '/'+segment1+'/sales_by_ProductData';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);

        let ProductHeadRow, tdCol, grandTotalRow, col12;
        const Searchresult = document.getElementById("Searchresult");
        Searchresult.innerHTML = '';
            grandTotalRow = cTag('div',{class:'flexSpaBetRow'})
                col12 = cTag('div',{class:'columnSM12', 'style': "text-align: right; margin: 0;", id:"filterby"})
                    let grandprofitval_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px;margin-top: 5px; margin-bottom: 5px;"});
                    let totalcost_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let grandtotal_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let totaldiscount_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let totalqty_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                col12.append(totalqty_button,totaldiscount_button,grandtotal_button,totalcost_button,grandprofitval_button);
            grandTotalRow.appendChild(col12);
        Searchresult.appendChild(grandTotalRow);

            let dataTableRow = cTag('div',{class:'columnSM12'})
                const divNoMore = cTag('div', {id: "no-more-tables"});
                    const salesProductTable = cTag('table', {class: " bgnone table-bordered table-striped table-condensed cf listing"});
                        const salesProductHead = cTag('thead', {class: "cf"});
                        
                        const tdAttributes = [{'datatitle':Translate('Product Info'), 'align':'left'},
                                            {'datatitle':Translate('Unit Price'), 'align':'right'},
                                            {'datatitle':Translate('Shipping Qty'), 'align':'right'},
                                            {'datatitle':Translate('Discount'), 'align':'right'},
                                            {'datatitle':Translate('Total'), 'align':'right'},
                                            {'datatitle':Translate('Cost'), 'align':'right'},
                                            {'datatitle':Translate('Profit'), 'align':'right'}];
                        const uriStr = segment1+'/view';

                            ProductHeadRow = cTag('tr');
                                const thCol0 = cTag('th', {'style': `text-align: center;`});
                                thCol0.innerHTML = tdAttributes[0].datatitle;

                                const thCol1 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                                thCol1.innerHTML = tdAttributes[1].datatitle;

                                const thCol2 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol2.innerHTML = tdAttributes[2].datatitle;

                                const thCol3 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol3.innerHTML = tdAttributes[3].datatitle;

                                const thCol4 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                                thCol4.innerHTML = tdAttributes[4].datatitle;

                                const thCol5 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol5.innerHTML = tdAttributes[5].datatitle;

                                const thCol6 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                                thCol6.innerHTML = tdAttributes[6].datatitle;
                            ProductHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6);
                        salesProductHead.appendChild(ProductHeadRow);
                    salesProductTable.appendChild(salesProductHead);

                        const salesProductBody = cTag('tbody');
                            let tableRows = data.tableData;
                            let totalprice = 0;
                            let totalcost = 0;
                            let grandtotal = 0;
                            let totaldiscount = 0;
                            let totalqty = 0;
                            
                            if(tableRows.length){
                                tableRows.forEach(oneRow => {
                                    let rowqtyprofitval = calculate('sub',calculate('sub',oneRow.rowtotalprice,oneRow.rowtotaldiscount,2),oneRow.rowtotalcost,2);
                                    let rowqtyprofit = 0;
                                    if(calculate('sub',oneRow.rowtotalprice,oneRow.rowtotaldiscount,2) !==0){
                                        rowqtyprofit = calculate('div',calculate('mul',rowqtyprofitval,100,2),calculate('sub',oneRow.rowtotalprice,oneRow.rowtotaldiscount,2),2);
                                    }

                                    totalprice = calculate('add',oneRow.rowtotalprice,totalprice,2);
                                    totalcost = calculate('add',oneRow.rowtotalcost,totalcost,2);
                                    grandtotal = calculate('add',oneRow.rowgrandtotal,grandtotal,2);
                                    totaldiscount = calculate('add',oneRow.rowtotaldiscount,totaldiscount,2);
                                    totalqty = calculate('add',oneRow.rowtotalqty,totalqty,2);

                                    ProductHeadRow = cTag('tr');
                                    if(oneRow.boldclass !==''){
                                        ProductHeadRow.style.fontWeight = 'bold';
                                    }
                                        tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                        tdCol.innerHTML = oneRow.description;
                                    ProductHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                        tdCol.innerHTML = addCurrency(oneRow.unitprice);
                                    ProductHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                        tdCol.innerHTML = oneRow.rowtotalqty;
                                    ProductHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rowtotaldiscount);
                                    ProductHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rowgrandtotal);
                                    ProductHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rowtotalcost);
                                    ProductHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});
                                        rowqtyprofitval = addCurrency(rowqtyprofitval);
                                        if(rowqtyprofit <0 ) rowqtyprofitval += ' ('+rowqtyprofit*(-1)+'%)';
                                        else rowqtyprofitval += ' ('+rowqtyprofit+'%)';
                                        tdCol.innerHTML = rowqtyprofitval;
                                    ProductHeadRow.appendChild(tdCol);
                                    salesProductBody.appendChild(ProductHeadRow);

                                    let subData = oneRow.substrextra;
                                    if(subData.length){                                
                                        subData.forEach(subOneRow => {
                                            let salesdatetime = DBDateToViewDate(subOneRow[0], 0, 1);
                                            let invoice_no = subOneRow[1];

                                            ProductHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'data-title':Translate('Sale Date'), align:'left'});
                                            tdCol.append(salesdatetime+'     ');
                                                let invoiceLink = cTag('a', {'href': '/Invoices/view/'+invoice_no, 'style': "color: #009; text-decoration: underline;", title:Translate('View Invoice')});
                                                invoiceLink.append(invoice_no+' ',  cTag('i', {class:'fa fa-link'}));
                                                tdCol.appendChild(invoiceLink);
                                            ProductHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                                tdCol.innerHTML = addCurrency(subOneRow[2]);
                                            ProductHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                                tdCol.innerHTML = subOneRow[3];
                                            ProductHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                                tdCol.innerHTML = addCurrency(subOneRow[4]);
                                            ProductHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                                tdCol.innerHTML = addCurrency(subOneRow[5]);
                                            ProductHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                                tdCol.innerHTML = addCurrency(subOneRow[6]);
                                            ProductHeadRow.appendChild(tdCol);
                                                tdCol = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});
                                                
                                                let Profit = addCurrency(subOneRow[7]);
                                                if(subOneRow[8] <0 ) Profit += ' ('+round(subOneRow[8]*(-1),2)+'%)';
                                                else Profit += ' ('+round(subOneRow[8],2)+'%)';
                                                    
                                                tdCol.innerHTML = Profit;
                                            ProductHeadRow.appendChild(tdCol);
                                            salesProductBody.appendChild(ProductHeadRow);
                                        });
                                    }
                                });
                            }
                            else{
                                    ProductHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`7`});
                                        tdCol.innerHTML = '';
                                    ProductHeadRow.appendChild(tdCol);
                                salesProductBody.appendChild(ProductHeadRow);
                            }
                            let grandprofitval = calculate('sub',calculate('sub',totalprice,totaldiscount,2),totalcost,2);
                            let grandprofit = 0;
                            if(calculate('sub',totalprice,totaldiscount,2) !==0){
                                grandprofit = calculate('div',calculate('mul',grandprofitval,100,2),calculate('sub',totalprice,totaldiscount,2),2);
                            }
                            grandprofitval = addCurrency(grandprofitval);
                            if(grandprofit <0 ) grandprofitval += ' ('+grandprofit*(-1)+'%)';
                            else grandprofitval += ' ('+grandprofit+'%)';
                            grandprofitval_button.innerHTML = Translate('Grand Profit')+' : '+ grandprofitval;
                            totalcost_button.innerHTML = Translate('Grand Cost')+' : '+ addCurrency(totalcost);
                            grandtotal_button.innerHTML = Translate('Grand Total')+' : '+ addCurrency(grandtotal);
                            totaldiscount_button.innerHTML = Translate('Discount')+' : '+ addCurrency(totaldiscount);
                            totalqty_button.innerHTML = Translate('Shipping Qty')+' : '+totalqty;

                        salesProductTable.appendChild(salesProductBody);
                divNoMore.appendChild(salesProductTable);
            dataTableRow.appendChild(divNoMore);
        Searchresult.appendChild(dataTableRow);
    }
}

function sales_by_Category(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));
    
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        let list_filters, inputField;
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Sales by Category')+' ';
                headerTitle.appendChild(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': "margin-left: 10px;"});
                printButton.addEventListener('click', print_Sales_report);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click',function(){javascript:window.location='/Sales_reports/lists'});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' '+Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);
        inputField = cTag('input', {'type': "hidden", id: "twoSegments", 'value': "Sales_reports-sales_by_Category"});
    showTableData.appendChild(inputField);
        inputField = cTag('input', {'type': "hidden", id: "pageURI", 'value': "Sales_reports/sales_by_Category"});
    showTableData.appendChild(inputField);

        const salesCategoryRow = cTag('div', {class:"flexSpaBetRow"});
            let viewColumn = cTag('div', {class:"columnXS6 columnLG3"});
                let viewInGroup = cTag('div', {class:"input-group"});
                    const viewLabel = cTag('label', {class: "input-group-addon cursor", 'for': "showing_type"});
                    viewLabel.innerHTML = Translate('View');
                viewInGroup.appendChild(viewLabel);
                    const selectShowingType = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectShowingType.addEventListener('change', AJ_sales_by_Category_MoreInfo);
                        const summaryOption = cTag('option', {'value': "Summary"});
                        summaryOption.innerHTML = Translate('Summary');
                    selectShowingType.appendChild(summaryOption);
                        const detailOption = cTag('option', {'value': "Details"});
                        detailOption.innerHTML = Translate('Detailed Summary');
                    selectShowingType.appendChild(detailOption);
                viewInGroup.appendChild(selectShowingType);
            viewColumn.appendChild(viewInGroup);
        salesCategoryRow.appendChild(viewColumn);

            let dateRangeField = cTag('div', {class:"columnXS6 columnLG3 daterangeContainer"});
                inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control search sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_date", 'value': ""});
            dateRangeField.appendChild(inputField);
        salesCategoryRow.appendChild(dateRangeField);

            const employeeColumn = cTag('div', {class:"columnXS6 columnLG3"});
                const employeeInGroup = cTag('div', {class:"input-group"});
                    const employeeLabel = cTag('label', {'for': "employee_id", class: "input-group-addon cursor"});
                    employeeLabel.innerHTML = Translate('Sales Person');
                employeeInGroup.appendChild(employeeLabel);
                    const selectEmployee = cTag('select', {name: "employee_id", id: "employee_id", class: "form-control"});
                    selectEmployee.addEventListener('change', AJ_sales_by_Category_MoreInfo);
                        const employeeOption = cTag('option', {'value': ''});
                        employeeOption.innerHTML = Translate('All');
                    selectEmployee.appendChild(employeeOption);
                employeeInGroup.appendChild(selectEmployee);
            employeeColumn.appendChild(employeeInGroup);
        salesCategoryRow.appendChild(employeeColumn);

            const categoryColumn = cTag('div', {class:"columnXS6 columnLG3"});
                const categoryInGroup = cTag('div', {class:"input-group"});
                    const categoryLabel = cTag('label', {'for': "category_id", class: "input-group-addon cursor"});
                    categoryLabel.innerHTML = Translate('Category');
                categoryInGroup.appendChild(categoryLabel);
                    const selectCategory = cTag('select', {name: "category_id", id: "category_id", class: "form-control"});
                    selectCategory.addEventListener('change', AJ_sales_by_Category_MoreInfo);
                        const categoryOption = cTag('option', {'value': ''});
                        categoryOption.innerHTML = Translate('All');
                    selectCategory.appendChild(categoryOption);
                categoryInGroup.appendChild(selectCategory);
                    const searchSpan = cTag('span', {class:"input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title:"", 'data-original-title': Translate('Date wise Search')});
                    searchSpan.addEventListener('click', AJ_sales_by_Category_MoreInfo);
                        const searchIcon = cTag('i', {class:"fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                categoryInGroup.appendChild(searchSpan);
            categoryColumn.appendChild(categoryInGroup);
        salesCategoryRow.appendChild(categoryColumn);
    showTableData.appendChild(salesCategoryRow);

        let searchResultColumn = cTag('div', {class:"columnXS12", 'style': "margin: 0;", id:"Searchresult"});
    showTableData.appendChild(searchResultColumn);

    if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{ list_filters = {};}

    checkAndSetSessionData('showing_type', 'Summary', list_filters);

    if(list_filters.hasOwnProperty("sales_date")){
        sales_date = list_filters.sales_date;
    }
    document.getElementById("sales_date").value = sales_date;
    daterange_picker_dialog(document.getElementById("sales_date"));

    checkAndSetSessionData('employee_id', '', list_filters);
    checkAndSetSessionData('category_id', '', list_filters);

    AJ_sales_by_Category_MoreInfo();
}

async function AJ_sales_by_Category_MoreInfo(){
	const jsonData = {};
	jsonData['sales_date'] = document.getElementById('sales_date').value;
	jsonData['showing_type'] = document.getElementById('showing_type').value;
	jsonData['employee_id'] = document.getElementById('employee_id').value;
	jsonData['category_id'] = document.getElementById('category_id').value;

    const url = '/'+segment1+'/sales_by_CategoryData';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        storeSessionData(jsonData);
        let select, option, salesCategoryHeadRow, tdCol, grandTotalRow, col12;
        const Searchresult = document.getElementById("Searchresult");
        Searchresult.innerHTML = '';

            select = document.querySelector("#employee_id");
            select.innerHTML = '';
                option = cTag('option', {value:''});
                option.innerHTML = Translate('All');
            select.appendChild(option);
            setOptions(select,data.employeeIds, 1, 1);
            select.value = jsonData['employee_id'];

            select = document.querySelector("#category_id");
            select.innerHTML = '';
                option = cTag('option', {value:''});
                option.innerHTML = Translate('All');
            select.appendChild(option);
            setOptions(select, data.categoryIds, 1, 1);            
            select.value = jsonData['category_id'];

            grandTotalRow = cTag('div',{class:'flexSpaBetRow'})
                col12 = cTag('div',{class:'columnSM12', 'style': "text-align: right; margin: 0;", id:"filterby"})
                    let grandprofitval_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px;margin-top: 5px; margin-bottom: 5px;"});
                    let totalcost_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let grandtotal_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let totaldiscount_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px; margin-top: 5px; margin-bottom: 5px;"});
                    let totalqty_button = cTag('button', {class:"btn reportButton", 'style': "margin-left: 10px;"});
                col12.append(totalqty_button,totaldiscount_button,grandtotal_button,totalcost_button,grandprofitval_button);
            grandTotalRow.appendChild(col12)
        Searchresult.appendChild(grandTotalRow)
        
        let dataTableRow = cTag('div',{class:'columnSM12'})
            const divNoMore = cTag('div', {id: "no-more-tables"});
                const salesCategoryTable = cTag('table', {class: " bgnone table-bordered table-striped table-condensed cf listing"});
                    const salesCategoryHead = cTag('thead', {class: "cf"});
            
                        const tdAttributes = [{'datatitle':Translate('Category Info'), 'align':'left'},
                                            {'datatitle':Translate('QTY'), 'align':'right'},
                                            {'datatitle':Translate('Discount'), 'align':'right'},
                                            {'datatitle':Translate('Total'), 'align':'right'},
                                            {'datatitle':Translate('Cost'), 'align':'right'},
                                            {'datatitle':Translate('Profit'), 'align':'right'}];
                        const uriStr = segment1+'/view';

                        salesCategoryHeadRow = cTag('tr');
                            const thCol0 = cTag('th', {'style': "text-align: left;"});
                            thCol0.innerHTML = tdAttributes[0].datatitle;

                            const thCol1 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol1.innerHTML = tdAttributes[1].datatitle;

                            const thCol2 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol2.innerHTML = tdAttributes[2].datatitle;

                            const thCol3 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol3.innerHTML = tdAttributes[3].datatitle;

                            const thCol4 = cTag('th', {'style': "text-align: right;", 'width': "10%"});
                            thCol4.innerHTML = tdAttributes[4].datatitle;

                            const thCol5 = cTag('th', {'style': "text-align: right;", 'width': "12%"});
                            thCol5.innerHTML = tdAttributes[5].datatitle;
                        salesCategoryHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5);
                    salesCategoryHead.appendChild(salesCategoryHeadRow);
                salesCategoryTable.appendChild(salesCategoryHead);

                    const salesCategoryBody = cTag('tbody');
                        let tableRows = data.tableData;
                        let totalprice = 0;
                        let totalcost = 0;
                        let grandtotal = 0;
                        let totaldiscount = 0;
                        let totalqty = 0;

                        if(tableRows.length){
                            tableRows.forEach(oneRow => {
                                let rowqtyprofitval = calculate('sub',calculate('sub',oneRow.subrowtotalprice,oneRow.subrowtotaldiscount,2),oneRow.subrowtotalcost,2);
                                let rowqtyprofit = 0;
                                if(calculate('sub',oneRow.subrowtotalprice,oneRow.subrowtotaldiscount,2) !==0){
                                    rowqtyprofit = calculate('div',calculate('mul',rowqtyprofitval,100,2),calculate('sub',oneRow.subrowtotalprice,oneRow.subrowtotaldiscount,2),2);
                                }

                                totalprice = calculate('add',oneRow.subrowtotalprice,totalprice,2);
                                totalcost = calculate('add',oneRow.subrowtotalcost,totalcost,2);
                                grandtotal = calculate('add',oneRow.subrowgrandtotal,grandtotal,2);
                                totaldiscount = calculate('add',oneRow.subrowtotaldiscount,totaldiscount,2);
                                totalqty = calculate('add',oneRow.subrowtotalqty,totalqty,2);
                                //set bold class
                                salesCategoryHeadRow = cTag('tr');
                                if(oneRow.boldclass !==''){
                                    salesCategoryHeadRow.style.fontWeight = 'bold';
                                }
                                    tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                    tdCol.innerHTML = oneRow.category_name;
                                salesCategoryHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                    tdCol.innerHTML = oneRow.subrowtotalqty;
                                salesCategoryHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                    tdCol.innerHTML = addCurrency(oneRow.subrowtotaldiscount);
                                salesCategoryHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                    tdCol.innerHTML = addCurrency(oneRow.subrowgrandtotal);
                                salesCategoryHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                    tdCol.innerHTML = addCurrency(oneRow.subrowtotalcost);
                                salesCategoryHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                    rowqtyprofitval = addCurrency(rowqtyprofitval);
                                    if(rowqtyprofit <0 ) rowqtyprofitval += ' ('+rowqtyprofit*(-1)+'%)';
                                    else rowqtyprofitval += ' ('+rowqtyprofit+'%)';
                                    tdCol.innerHTML = rowqtyprofitval;
                                salesCategoryHeadRow.appendChild(tdCol);
                                salesCategoryBody.appendChild(salesCategoryHeadRow);

                                if(oneRow.substrextra.length){
                                    let subData = oneRow.substrextra;
                                    subData.forEach(subOneRow => {
                                        salesCategoryHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                            tdCol.innerHTML = subOneRow[0];
                                        salesCategoryHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                            tdCol.innerHTML = subOneRow[1];
                                        salesCategoryHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[2]);
                                        salesCategoryHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[3]);
                                        salesCategoryHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[4]);
                                        salesCategoryHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});

                                            let Profit = addCurrency(subOneRow[5]);
                                            if(subOneRow[6] <0 ) Profit += ' ('+subOneRow[6]*(-1)+'%)';
                                            else Profit += ' ('+subOneRow[6]+'%)';

                                            tdCol.innerHTML = Profit;
                                        salesCategoryHeadRow.appendChild(tdCol);
                                        salesCategoryBody.appendChild(salesCategoryHeadRow);
                                    })
                                }
                            })
                        }
                        else{
                            let categoryHeadRow = cTag('tr');
                                tdCol = cTag('td',{colspan:"6"});
                                tdCol.innerHTML = '';
                            categoryHeadRow.appendChild(tdCol);
                            salesCategoryBody.appendChild(categoryHeadRow);
                        }

                        let grandprofitval = calculate('sub',calculate('sub',totalprice,totaldiscount,2),totalcost,2);
                        let grandprofit = 0;
                        if(calculate('sub',totalprice,totaldiscount,2) !==0){
                            grandprofit = calculate('div',calculate('mul',grandprofitval,100,2),calculate('sub',totalprice,totaldiscount,2),2);
                        }
                        grandprofitval = addCurrency(grandprofitval);
                        if(grandprofit <0 ) grandprofitval += ' ('+grandprofit*(-1)+'%)';
                        else grandprofitval += ' ('+grandprofit+'%)';
                        grandprofitval_button.innerHTML = Translate('Grand Profit')+' : '+ grandprofitval;
                        totalcost_button.innerHTML = Translate('Grand Cost')+' : '+ addCurrency(totalcost);
                        grandtotal_button.innerHTML = Translate('Grand Total')+' : '+ addCurrency(grandtotal);
                        totaldiscount_button.innerHTML = Translate('Discount')+' : '+ addCurrency(totaldiscount);
                        totalqty_button.innerHTML = Translate('Shipping Qty')+' : '+totalqty;

                    salesCategoryTable.appendChild(salesCategoryBody);
                divNoMore.appendChild(salesCategoryTable);
            dataTableRow.appendChild(divNoMore);
        Searchresult.appendChild(dataTableRow);
    }
}

function sales_by_Tax(){
    let queryString = location.search;
    let params = new URLSearchParams(queryString);
    let sales_date = DBDateRangeToViewDate(params.get("sales_date"));

    let list_filters, inputField;
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {class:"flexSpaBetRow"});
            const titleName = cTag('div', {class:"columnXS6 columnSM8"});
                const headerTitle = cTag('h2', { 'style': "text-align: start;"});
                    const title = cTag('span', {id:"ptitle"});
                    title.innerHTML = Translate('Sales by Tax')+' ';
                headerTitle.appendChild(title);
                    const infoIcon = cTag('i', {class:"fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('This is the standard sales reporting page including many standard reports.')});
                headerTitle.appendChild(infoIcon);
            titleName.appendChild(headerTitle);
        titleRow.appendChild(titleName);
            const buttonsName = cTag('div', {class:"columnXS6 columnSM4", 'style': "text-align: end;"});
                const printButton = cTag('button', {class:"btn printButton", 'style': " margin-left: 10px;"});
                printButton.addEventListener('click', print_Sales_report);
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
                const reportButton = cTag('button', {class:"btn defaultButton"});
                reportButton.addEventListener('click',function(){javascript:window.location='/Sales_reports/lists'});
                reportButton.append(cTag('i', {class:"fa fa-list"}),' '+Translate('All Reports'));
            buttonsName.append(reportButton, printButton);
        titleRow.appendChild(buttonsName);
    showTableData.appendChild(titleRow);
        inputField = cTag('input', {'type': "hidden", id: "twoSegments", 'value': "Sales_reports-sales_by_Tax"});
    showTableData.appendChild(inputField);
        inputField = cTag('input', {'type': "hidden", id: "pageURI", 'value': "Sales_reports/sales_by_Tax"});
    showTableData.appendChild(inputField);

        let salesTaxRow = cTag('div', {class:"flexEndRow"});
            const viewColumn = cTag('div', {class:"columnXS6 columnSM4"});
                const viewInGroup = cTag('div', {class:"input-group"});
                    let viewLabel = cTag('label', {class: "input-group-addon cursor", 'for': "showing_type"});
                    viewLabel.innerHTML = Translate('View');
                viewInGroup.appendChild(viewLabel);
                    let selectShowingType = cTag('select', {name: "showing_type", id: "showing_type", class: "form-control"});
                    selectShowingType.addEventListener('change', AJ_sales_by_Tax_MoreInfo);
                        const summaryOption = cTag('option', {'value': "Summary"});
                        summaryOption.innerHTML = Translate('Summary');
                    selectShowingType.appendChild(summaryOption);
                        const detailOption = cTag('option', {'value': "Details"});
                        detailOption.innerHTML = Translate('Detailed Summary');
                    selectShowingType.appendChild(detailOption);
                viewInGroup.appendChild(selectShowingType);
            viewColumn.appendChild(viewInGroup);
        salesTaxRow.appendChild(viewColumn);

            let dateRangeColumn = cTag('div', {class:"columnXS6 columnSM4"});
                const dateRange = cTag('div', {class:"input-group daterangeContainer"});
                    inputField = cTag('input', {'required': "", 'minlength': 23, 'maxlength': 23, 'type': "text", class: "form-control search sales_date", 'style': "padding-left: 35px;", name: "sales_date", id: "sales_date", 'value': ""});
                dateRange.appendChild(inputField);
                    const searchSpan = cTag('span', {class:"input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title:"", 'data-original-title': Translate('Date wise Search')});
                    searchSpan.addEventListener('click', AJ_sales_by_Tax_MoreInfo);
                        const searchIcon = cTag('i', {class:"fa fa-search"});
                    searchSpan.appendChild(searchIcon);
                dateRange.appendChild(searchSpan);
            dateRangeColumn.appendChild(dateRange);
        salesTaxRow.appendChild(dateRangeColumn);
    showTableData.appendChild(salesTaxRow);

        let searchResultRow = cTag('div', {class:"columnXS12", 'style': "margin: 0;", id:"Searchresult"});
    showTableData.appendChild(searchResultRow);

    if(sessionStorage.getItem("list_filters") !== null){
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{list_filters = {};}

    checkAndSetSessionData('showing_type', 'Summary', list_filters);

    if(list_filters.hasOwnProperty("sales_date")){
        sales_date = list_filters.sales_date;
    }
    document.getElementById("sales_date").value = sales_date;
    daterange_picker_dialog(document.getElementById("sales_date"));

    AJ_sales_by_Tax_MoreInfo();
}

async function AJ_sales_by_Tax_MoreInfo(){
	const jsonData = {};
	jsonData['sales_date'] = document.getElementById('sales_date').value;
	jsonData['showing_type'] = document.getElementById('showing_type').value;

    const url = '/'+segment1+'/sales_by_TaxData';
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        storeSessionData(jsonData);

        let salesTaxHeadRow, tdCol;
        const Searchresult = document.getElementById("Searchresult");
        Searchresult.innerHTML = '';
            let dataTableRow = cTag('div',{class:'columnSM12'})
                const divNoMore = cTag('div', {id: "no-more-tables"});
                    const salesTaxTable = cTag('table', {class: " bgnone table-bordered table-striped table-condensed cf listing"});
                        const salesTaxHead = cTag('thead', {class: "cf"});

                            const tdAttributes = [{'datatitle':Translate('Taxes name 1'), 'align':'left'},
                                                {'datatitle':Translate('Taxable'), 'align':'right'},
                                                {'datatitle':Translate('Taxes % 1'), 'align':'right'},
                                                {'datatitle':Translate('Taxes 1'), 'align':'right'},
                                                {'datatitle':Translate('Taxes name 2'), 'align':'left'},
                                                {'datatitle':Translate('Taxes % 2'), 'align':'right'},
                                                {'datatitle':Translate('Taxes 2'), 'align':'right'},
                                                {'datatitle':Translate('Non Taxable'), 'align':'right'},
                                                {'datatitle':Translate('Grand Total'), 'align':'right'}];
                            const uriStr = segment1+'/view';

                            salesTaxHeadRow = cTag('tr');
                                const thCol0 = cTag('th', {'style': "text-align: left;"});
                                thCol0.innerHTML = tdAttributes[0].datatitle;

                                const thCol1 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol1.innerHTML = tdAttributes[1].datatitle;

                                const thCol2 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol2.innerHTML = tdAttributes[2].datatitle;

                                const thCol3 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol3.innerHTML = tdAttributes[3].datatitle;

                                const thCol4 = cTag('th', {class: "taxes2", 'style': "text-align: left;", 'width': "15%"});
                                thCol4.innerHTML = tdAttributes[4].datatitle;

                                const thCol5 = cTag('th', {class: "taxes2", 'style': "text-align: right;", 'width': "8%"});
                                thCol5.innerHTML = tdAttributes[5].datatitle;

                                const thCol6 = cTag('th', {class: "taxes2", 'style': "text-align: right;", 'width': "8%"});
                                thCol6.innerHTML = tdAttributes[6].datatitle;

                                const thCol7 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol7.innerHTML = tdAttributes[7].datatitle;

                                const thCol8 = cTag('th', {'style': "text-align: right;", 'width': "8%"});
                                thCol8.innerHTML = tdAttributes[8].datatitle;
                            salesTaxHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5, thCol6, thCol7, thCol8);
                        salesTaxHead.appendChild(salesTaxHeadRow);
                    salesTaxTable.appendChild(salesTaxHead);

                        const salesTaxBody = cTag('tbody');
                            let tableRows = data.tableData;

                            if(tableRows.length){
                                tableRows.forEach(oneRow => {
                                    salesTaxHeadRow = cTag('tr');
                                    if(oneRow.boldclass !==''){
                                        salesTaxHeadRow.style.fontWeight = 'bold';
                                    }
                                        tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                        tdCol.innerHTML = oneRow.taxes_name1||'&nbsp;';
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rtaxable_total);
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                        tdCol.innerHTML = oneRow.taxes_percentage1;
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rowtotaltaxes1);
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                        tdCol.innerHTML = oneRow.taxes_name2||'&nbsp;';
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                        tdCol.innerHTML = oneRow.taxes_percentage2;
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rowtotaltaxes2);
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[7].datatitle, align:tdAttributes[7].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rowtotalnontaxable);
                                    salesTaxHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td', {'data-title': tdAttributes[8].datatitle, align:tdAttributes[8].align});
                                        tdCol.innerHTML = addCurrency(oneRow.rgrandtotal);
                                    salesTaxHeadRow.appendChild(tdCol);
                                    salesTaxBody.appendChild(salesTaxHeadRow);

                                if(oneRow.substrextra.length){
                                    let subData = oneRow.substrextra;
                                    subData.forEach(subOneRow => {
                                        let dtaxes_name1 = subOneRow[0];
                                        let invoice_no = subOneRow[1];
                                        
                                        salesTaxHeadRow = cTag('tr');
                                            tdCol = cTag('td', {'data-title': tdAttributes[0].datatitle, align:tdAttributes[0].align});
                                                let viewInvoice50 = cTag('div');
                                                    let invoiceLink = cTag('a', {'href': '/Invoices/view/'+invoice_no, 'style': "color: #009; text-decoration: underline;", title:Translate('View Invoice')});
                                                    invoiceLink.append(invoice_no+' ',  cTag('i', {class:'fa fa-link'}));
                                                viewInvoice50.append(dtaxes_name1+' ', invoiceLink);
                                            tdCol.appendChild(viewInvoice50);
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[1].datatitle, align:tdAttributes[1].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[2]);
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[2].datatitle, align:tdAttributes[2].align});
                                            tdCol.innerHTML = subOneRow[3];
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[3].datatitle, align:tdAttributes[3].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[4]);
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[4].datatitle, align:tdAttributes[4].align});
                                            tdCol.innerHTML = subOneRow[5];
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[5].datatitle, align:tdAttributes[5].align});
                                            tdCol.innerHTML = subOneRow[6];
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[6].datatitle, align:tdAttributes[6].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[7]);
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[7].datatitle, align:tdAttributes[7].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[8]);
                                        salesTaxHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td', {'data-title': tdAttributes[8].datatitle, align:tdAttributes[8].align});
                                            tdCol.innerHTML = addCurrency(subOneRow[9]);
                                        salesTaxHeadRow.appendChild(tdCol);
                                        salesTaxBody.appendChild(salesTaxHeadRow);
                                    })
                                }
                            })
                            }
                            else{
                                let taxHeadRow = cTag('tr');
                                    tdCol = cTag('td',{colspan:"9"});
                                    tdCol.innerHTML = '';
                                taxHeadRow.appendChild(tdCol);
                                salesTaxBody.appendChild(taxHeadRow);
                            }
                    salesTaxTable.appendChild(salesTaxBody);
                divNoMore.appendChild(salesTaxTable);
            dataTableRow.appendChild(divNoMore);
        Searchresult.appendChild(dataTableRow);
    }
}

function print_Sales_report() {
    let todayDate, document_focus;
	let divContents = document.querySelector("#no-more-tables").cloneNode(true);
    let filterby = '';
	let ColSpan = 7;

    let sorder_by = document.getElementById('sorder_by');
    if(sorder_by){
	    filterby += sorder_by.options[sorder_by.selectedIndex].innerText+', ';
    }

    let showing_type = document.getElementById("showing_type");
    if(showing_type){
	    filterby += Translate('View')+': '+showing_type.options[showing_type.selectedIndex].innerText;
    }

    let sales_date = document.getElementById("sales_date").value;
	if(sales_date !==''){
		filterby += ', '+Translate('Date Range')+': '+sales_date;
	}

    let report_type = document.getElementById("report_type");
    if(report_type){
	    filterby += ', '+Translate('Type')+': '+report_type.options[report_type.selectedIndex].innerText;
    }
    
    let employee = document.getElementById("employee");    
	if(employee && employee.value !== ''){
			filterby += ', '+Translate('Sales Person')+': '+employee.value;
	}

    let customer_type = document.getElementById("customer_type");    
	if(customer_type){
		ColSpan = 5;
		if(customer_type.value !== '') filterby += ', '+Translate('Customer Type')+': '+customer_type.value;
		let customer = document.getElementById("customers_id").value;
		if(customer != 0){
			filterby += ', '+Translate('Customer')+': '+customer;
		}
	}

    let puser_id = document.getElementById("puser_id");
	if(puser_id){
		ColSpan = 3;
        let user = puser_id.options[puser_id.selectedIndex].innerText;
        filterby += ', '+Translate('User')+': '+user;
		let paymenttype = document.getElementById("paymenttype");
		filterby += ', '+Translate('Payment Type')+': '+paymenttype.options[paymenttype.selectedIndex].innerText;
	}

    let product = document.getElementById("product");
	if(product && product.value !==''){
		filterby += ', '+Translate('Product')+': '+product.value;
	}

    let employee_id = document.getElementById("employee_id");
	if(employee_id){
		ColSpan = 6;						
		let employee = employee_id.options[employee_id.selectedIndex].innerText;
		filterby += ', '+Translate('Employee')+': '+employee;
		let category_id = document.getElementById("category_id");
        let category = category_id.options[category_id.selectedIndex].innerText;
		filterby += ', '+Translate('Category')+': '+category;
	}

	if(document.getElementById("sales_by_Tax")){ColSpan = 9;}

	let titleP = document.getElementById("ptitle").innerHTML;
	let now = new Date();
    let date = now.getDate();
    let month = now.getMonth() + 1;
    if(calenderDate.toLowerCase()==='dd-mm-yyyy'){todayDate = (date<10 ? '0'+date : date) +'-'+(month<10 ? '0'+month : month)+'-'+now.getFullYear();}
    else{todayDate = (month<10 ? '0'+month : month)+'/'+ (date<10 ? '0'+date : date ) +'/'+now.getFullYear();}

	 const additionaltoprows = cTag('div');
        let companyNameDiv = cTag('div',{ 'class':`flexSpaBetRow` });
            let divWidth30 = cTag('div',{ 'style': "font-weight: bold; font-size: 18px; text-align: left; " });
            divWidth30.innerHTML = stripslashes(companyName);
        companyNameDiv.appendChild(divWidth30);
            let titleDiv = cTag('div',{ 'style': "font-size: 20px; font-weight: bold;" });
            titleDiv.innerHTML = titleP;
        companyNameDiv.appendChild(titleDiv);
            let dateDiv = cTag('div',{ 'style': "font-size: 16px;" });
            dateDiv.innerHTML = todayDate;
        companyNameDiv.appendChild(dateDiv);
    additionaltoprows.appendChild(companyNameDiv);
    additionaltoprows.appendChild(cTag('div',{ 'style': "border-top: 1px solid #CCC; margin-top: 10px;" }));
        let div100Width = cTag('div',{style:'margin-bottom:10px'});
        div100Width.innerHTML = filterby;
    additionaltoprows.appendChild(div100Width);    
    divContents.prepend(additionaltoprows);
	
	let day = new Date();
	let w = 900;
	let h = 600;
	let scrl = 1;
	let winl = (screen.width - w) / 2;
	let wint = (screen.height - h) / 2;
	let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	let printWindow = window.open('', '" + id + "', winprops);

        let html = cTag('html');
            let head = cTag('head');
                let title = cTag('title');
                title.innerHTML = titleP;
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
            let body = cTag('body');
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
			let state = printWindow.document.readyState;
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
		let deviceOpSy = getMobileOperatingSystem();
		if (document_focus === true && deviceOpSy==='unknown') { printWindow.window.close(); }
	}, 500);
}

function checkSalesDate(report_type){
	let oField = document.frmsales_by_date.sales_date;
	let oElement = document.getElementById('error_frmsales_by_date');
	oElement.innerHTML = "";
	if(oField.value === ""){
		oElement.innerHTML = Translate('You are missing date');
		oField.focus();
		return(false);
	}
	if(report_type !==''){
		document.frmsales_by_date.report_type.value = report_type;
	}
	document.frmsales_by_date.submit();
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {
        lists, sales_by_Date, sales_by_Employee, sales_by_Customer, sales_by_Paymenttype, sales_by_Product, sales_by_Category, sales_by_Tax
    };
    layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});