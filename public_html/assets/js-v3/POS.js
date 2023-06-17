import {
    cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, DBDateToViewDate, noPermissionWarning, printbyurl, 
    confirm_dialog, alert_dialog, setTableHRows, showTopMessage, setOptions, popup_dialog600, date_picker, setCDinCookie, 
    dynamicImport, applySanitizer, togglePaymentButton, fetchData, listenToEnterKey, controllNumericField,validateRequiredField,
    actionBtnClick, serialize, onClickPagination, AJautoComplete, historyTable, addCurrency, round, emailcheck, 
} from './common.js';

import {
    showCategoryPPProduct, addPOSPayment, showProductPicker, reloadProdPkrCategory, cartsAutoFuncCall, calculateChangeCartTotal, 
    loadCartData, emaildetails, showOrNotSquareup, onChangeTaxesId, preNextCategory, updateCartData, calculateCartTotal,loadPaymentData,
    AJautoComplete_cartProduct, checkMethod, checkAvailCredit, addCartsProduct, haveAnyOversoldProduct
} from './cart.js';

segment2 = 'index';

async function index(){
    const url = '/'+segment1+'/AJ_index_MoreInfo';
    fetchData(afterFetch,url,{});

    function afterFetch(data){
        segment3 = data.pos_id;
        let span, select, option, list_filters, inputField, headRow, thCol, salesRegisterBody, tdCol, bTag, changeSpan;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
        Dashboard.appendChild(cTag('input',{ 'type':`hidden`,'id':`subPermission`,'value':data.subPermission.join(',') }));
            const titleRow = cTag('div',{ 'class':`flexSpaBetRow` });
                const titleName = cTag('div',{ 'class':`columnXS4`, 'style': "margin: 0;" });
                    const headerTitle = cTag('h2',{ 'style':'min-width: max-content; padding-top: 5px; text-align: start;' });
                    headerTitle.append(Translate('Sales Register')+' ');
                    headerTitle.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Launches the Cash Register (POS) module allowing you to complete sales activities and generate invoices') }));
                titleName.appendChild(headerTitle);
            titleRow.appendChild(titleName);
                const buttonsName = cTag('div',{ 'class':`columnXS8`, 'style': "text-align: end;" });
                if(data.petty_cash_tracking===1){
                        const cashButton = cTag('button',{ 'class':`btn cashButton`});
                        cashButton.addEventListener('click',()=>AJget_pettyCashPopup(0));
                        cashButton.innerHTML = Translate('Petty Cash');
                    buttonsName.appendChild(cashButton);
                }
                if(data.cash_drawer_sale === 1){
                        const cashDrawerButton = cTag('button',{ 'class':`btn defaultButton`, 'style': "margin-left: 10px;" });
                        cashDrawerButton.addEventListener('click',()=>printbyurl('/POS/openCashDrawer'));
                        cashDrawerButton.innerHTML = Translate('Open Cash Drawer');
                    buttonsName.appendChild(cashDrawerButton);
                }
            titleRow.appendChild(buttonsName);
        Dashboard.appendChild(titleRow);

            let salesRegisterColumn = cTag('div',{ 'class':`columnSM12`, 'style': "position: relative; margin-top: 0px;" });
                const salesRegisterForm = cTag('form',{ 'method':`post`,'action':`#`,'enctype':`multipart/form-data`,'name':`frm_pos`,'id':`frm_pos` });
                salesRegisterForm.addEventListener('submit',event=>event.preventDefault());
                salesRegisterForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
                    let salesPersonRow = cTag('div',{ 'class':`flexSpaBetRow` });
                        let salesPersonColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
                            const salesPersonFlex = cTag('div',{ 'class':`flexEndRow` });
                            if(OS ==='unknown'){                                            
                                const salesPersonTitle = cTag('div',{ 'class':`columnXS4 columnSM5 columnMD7`, 'style': "padding-top: 5px;", 'align':`right` });
                                    const salesPersonLabel = cTag('label',{ 'for':`employee_id` });
                                    salesPersonLabel.innerHTML = Translate('Sales Person');
                                        let requiredField = cTag('span',{ 'class':`required` });
                                        requiredField.innerHTML = '*';
                                    salesPersonLabel.appendChild(requiredField);
                                salesPersonTitle.appendChild(salesPersonLabel);
                                salesPersonFlex.appendChild(salesPersonTitle);
                            }
                                const salesPersonDropDown = cTag('div',{'class':'columnXS12'});
                                if(OS ==='unknown') salesPersonDropDown.setAttribute('class','columnXS8 columnSM7 columnMD5');
                                    let selectEmployee = cTag('select',{ 'name':`employee_id`,'id':`employee_id`,'class':`form-control`,'change': ()=>updatePOS('employee_id') });
                                    if(OS !=='unknown'){
                                        const option = cTag('option',{'disabled':''});
                                        option.innerHTML = Translate('Sales Person');
                                        selectEmployee.appendChild(option);
                                    }
                                    setOptions(selectEmployee,data.empOpt,1,1);
                                salesPersonDropDown.appendChild(selectEmployee);
                            salesPersonFlex.appendChild(salesPersonDropDown);
                        salesPersonColumn.appendChild(salesPersonFlex);

                        let customerColumn = cTag('div',{ 'class':`columnXS12 columnSM6`, 'style': 'padding-right: 0;' });
                            const customerFlex = cTag('div',{ 'class':`flexEndRow` });
                            if(OS==='unknown'){
                                const customerTitle = cTag('div',{ 'class':`columnXS4 columnSM4`, 'style': "padding-top: 5px;", 'align':`right` });
                                    const customerLabel = cTag('label',{ 'for':`customer_name` });
                                    customerLabel.innerHTML = Translate('Customer')+' : ';
                                customerTitle.appendChild(customerLabel);
                            customerFlex.appendChild(customerTitle);
                            }
                                let customerField = cTag('div',{ 'class':`columnXS12`, 'style': "padding-right: 10px;" });
                                if(OS ==='unknown') {
                                    customerField.setAttribute('class','columnXS8 columnSM8');
                                    customerField.setAttribute('style','padding-right: 0;');
                                }
                                    let customerInGroup = cTag('div',{ 'class':`input-group`,'id':`customerNameField` });
                                        inputField = cTag('input',{keydown: listenToEnterKey(filter_POS_index), 'maxlength':`50`,'type':`text`,'value':data.customer_name,'name':`customer_name`,'id':`customer_name`,'class':`form-control ui-autocomplete-input`,'placeholder':Translate('Search Customers') });
                                        if(data.customer_name !=='') inputField.setAttribute('readonly', 'readonly');
                                    customerInGroup.appendChild(inputField);
                                        let newCustomer = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Customer'),'class':`input-group-addon cursor`, id: "newCustomerId" });
                                        newCustomer.addEventListener('click',()=>dynamicImport('./Customers.js','AJget_CustomersPopup',[0, posCustomerSave]));
                                        newCustomer.appendChild(cTag('i',{ 'class':`fa fa-plus` }));
                                        newCustomer.append(' '+Translate('New'));
                                    customerInGroup.appendChild(newCustomer);
                                        let editCustomer = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Edit Customer'),'class':`input-group-addon cursor`, id: "editCustomerHide" });
                                        editCustomer.addEventListener('click',()=>{
                                            dynamicImport('./Customers.js','AJget_CustomersPopup',[document.getElementById('customer_id').value, ()=> {posCustomerSave();location.reload()}]);
                                        });
                                        editCustomer.appendChild(cTag('i',{ 'class':`fa fa-edit` }));
                                        editCustomer.append(' '+Translate('Edit'));

                                        changeSpan = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Clear Customer'),'class':`input-group-addon cursor`, id: "changeCustomerId" });
                                        changeSpan.addEventListener('click', function (){
                                            document.getElementById('customer_name').value = '';
                                            document.getElementById('customer_name').removeAttribute('readonly');
                                            this.style.display = 'none';
                                            document.getElementById('editCustomerHide').style.display = 'none';
                                            document.getElementById('newCustomerId').style.display = '';
                                            document.getElementById('customer_id').value = 0;
                                        });
                                        changeSpan.appendChild(cTag('i',{ 'class':`fa fa-exchange` }));
                                        changeSpan.append(' '+Translate('Change'));

                                        if(data.customer_id>0){
                                            newCustomer.style.display = 'none';
                                        }
                                        else{
                                            editCustomer.style.display = 'none';
                                            changeSpan.style.display = 'none';
                                        }
                                    customerInGroup.append(editCustomer, changeSpan);
                                customerField.appendChild(customerInGroup);
                                customerField.appendChild(cTag('input',{ 'type':`hidden`,'name':`customer_id`,'id':`customer_id`,'value':data.customer_id }));
                                customerField.appendChild(cTag('input',{ 'type':`hidden`,'name':`cash_reg_req_customer`,'id':`cash_reg_req_customer`,'value':data.cash_reg_req_customer }));
                                customerField.appendChild(cTag('input',{ 'type':`hidden`,'name':`email_address`,'id':`email_address`,'value':data.email_address }));
                                customerField.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos_id`,'id':`pos_id`,'value':data.pos_id }));
                                customerField.appendChild(cTag('input',{ 'type':`hidden`,'name':`default_invoice_printer`,'id':`default_invoice_printer`,'value':data.default_invoice_printer }));
                                customerField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_customer_id` }));
                                customerField.appendChild(cTag('span',{ 'class':`error_msg`,'id':`showerrormessage` }));
                            customerFlex.appendChild(customerField);
                        customerColumn.appendChild(customerFlex);
                    salesPersonRow.append(salesPersonColumn, customerColumn);
                salesRegisterForm.appendChild(salesPersonRow);
                
                    const salesRegisterContent = cTag('div',{ 'class':`cartContent`});
                        const emptyDiv = cTag('div',{ 'class':`flexSpaBetRow` });
                        emptyDiv.appendChild(cTag('div',{ 'class':`columnSM12 errormsg`, 'style': "margin-top: 0px; margin-bottom: 0px;", 'id':`errorposdata` }));
                    salesRegisterContent.appendChild(emptyDiv);
                        const salesRegisterTableColumn = cTag('div',{ 'class':`columnXS12`, 'style': "margin: 0; padding: 0;" });
                            const salesRegisterTable = cTag('table',{ 'class':`table table-bordered` });
                                const salesRegisterHead = cTag('thead');
                                    headRow = cTag('tr');
                                        thCol = cTag('th',{ 'width':`40px`, 'style': "text-align: right;" });
                                        thCol.innerHTML = '#';
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th');
                                        thCol.innerHTML = Translate('Description');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Need/Have/OnPO');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Time/Qty');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Unit Price');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`10%`, 'style': "text-align: right;" });
                                        thCol.innerHTML = Translate('Total');
                                    headRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`60px;`});
                                        thCol.appendChild(cTag('i',{ 'class':`fa fa-trash-o` }));
                                    headRow.appendChild(thCol);
                                salesRegisterHead.appendChild(headRow);
                            salesRegisterTable.appendChild(salesRegisterHead);
                                salesRegisterBody = cTag('tbody',{ 'id':`invoice_entry_holder` });
                                loadCartData(salesRegisterBody,data.cartsData);
                            salesRegisterTable.appendChild(salesRegisterBody);
                                salesRegisterBody = cTag('tbody');
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'style': "text-align: right;",'id':`barcodeserno` });
                                        tdCol.innerHTML = 1;
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'colspan':`6`});
                                            const searchDiv = cTag('div',{'class':'flexStartRow'});
                                                let newProductDiv = cTag('div',{ 'class':`input-group columnXS12 columnSM4 columnMD4` });
                                                newProductDiv.appendChild(cTag('input',{ 'type':`hidden`,'id':`temp_pos_cart_id`,'name':`temp_pos_cart_id`,'value':`0` }));
                                                let search_sku_field = cTag('input',{ 'maxlength':`50`,'type':`text`,'id':`search_sku`,'name':`search_sku`,'class':`form-control search_sku ui-autocomplete-input`, 'style': "min-width: 120px;", autocomplete:'off', 'placeholder':Translate('Search by product name, SKU or IMEI number') });
                                                newProductDiv.appendChild(search_sku_field);
                                                    span = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Add New Product'),'class':`input-group-addon cursor`});
                                                    if(data.pPermission && !data.subPermission.includes('cnanp')) span.addEventListener('click',()=>dynamicImport('./Products.js','AJget_ProductsPopup',['POS',0,0,addCartsProduct]));
                                                    else span.addEventListener('click',()=>noPermissionWarning('Product'));
                                                    span.appendChild(cTag('i',{ 'class':`fa fa-plus` }));
                                                    span.append(' '+Translate('New'));
                                                newProductDiv.appendChild(span);
                                            searchDiv.appendChild(newProductDiv);
                                            searchDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`clickYesNo`,'id':`clickYesNo`,'value':`0` }));
                                                const ppDiv = cTag('div',{'class':'columnXS12 columnSM8 columnMD8', 'style': "text-align: left;"});
                                                    const productPickerButton = cTag('button',{ 'type':`button`,'name':`showcategorylist`,'id':`product-picker-button`,'class':`btn productPickerButton` });
                                                    productPickerButton.addEventListener('click',showProductPicker);
                                                    productPickerButton.innerHTML = Translate('Open Product Picker');
                                                ppDiv.appendChild(productPickerButton);
                                            searchDiv.appendChild(ppDiv);
                                            searchDiv.appendChild(cTag('span',{ 'class':`error_msg`,'style':'margin-left:6px','id':`error_search_sku` }));
                                        tdCol.appendChild(searchDiv);
                                    headRow.appendChild(tdCol);
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'style':`padding: 0`,'colspan':`7` });
                                        tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`error_productlist` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`pagi_index`,'id':`pagi_index`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppcategory_id`,'id':`ppcategory_id`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`ppproduct_id`,'id':`ppproduct_id`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'autocomplete':`off`,'name':`totalrowscount`,'id':`totalrowscount`,'value':`0` }));
                                            const showProductDiv = cTag('div',{ 'class': `flexSpaBetRow`,'id':`filterrow`,'style': 'width: 100%; padding: 10px 60px 0 50px; gap:5px; display:none;'});
                                                const showProductFilter = cTag('div',{ 'id':`filter_name_html`});
                                                    const showProductInGroup = cTag('div',{ 'class':`input-group` });
                                                        const filter_name = cTag('input',{ 'maxlength':`50`,'type':`text`,'placeholder':Translate('Search name'),'value':``,'class':`form-control product-filter`,'name':`filter_name`,'id':`filter_name` });
                                                        filter_name.addEventListener('keyup', e=>{if(e.which===13) showCategoryPPProduct()});
                                                    showProductInGroup.appendChild(filter_name);
                                                        let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search name') });
                                                        searchSpan.addEventListener('click',showCategoryPPProduct);
                                                        searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                                                    showProductInGroup.appendChild(searchSpan);
                                                showProductFilter.appendChild(showProductInGroup);
                                            showProductDiv.appendChild(showProductFilter);
                                                let productFormData = cTag('div');
                                                productFormData.appendChild(cTag('label',{ 'id':`PPfromtodata` }));
                                            showProductDiv.appendChild(productFormData);
                                                let allCategoryDiv = cTag('div',{ 'id':`all-category-button`});
                                                    const allCategoryInGroup = cTag('div',{ 'class':`input-group` });
                                                        let allCategoryLink = cTag('a',{ 'href':`javascript:void(0);`,'title':Translate('All Category List') });
                                                        allCategoryLink.addEventListener('click',reloadProdPkrCategory);
                                                            let categorySpan = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "background: #a71d4c; color: #FFF; border-color: #a71d4c;" });
                                                                const allCategoryLabel = cTag('label');
                                                                allCategoryLabel.innerHTML = Translate('All Category List');
                                                            categorySpan.appendChild(allCategoryLabel);
                                                        allCategoryLink.appendChild(categorySpan);
                                                    allCategoryInGroup.appendChild(allCategoryLink);
                                                allCategoryDiv.appendChild(allCategoryInGroup);
                                            showProductDiv.appendChild(allCategoryDiv);
                                        tdCol.appendChild(showProductDiv);
                                            const categoryDiv = cTag('div',{'style': "width: 100%; position: relative;" });
                                                const categoryDivColumn = cTag('div',{ 'class':`columnSM12`,'id':`product-picker`,'style':'display:none;min-height:90px; align-items:center'});
                                                categoryDivColumn.appendChild(cTag('div',{ 'id':`allcategorylist`,'style':'padding:0 50px 0 40px;width:100%' }));
                                                categoryDivColumn.appendChild(cTag('div',{ 'id':`allproductlist`,'style':'padding:0 50px 0 40px;width:100%' }));
                                            categoryDiv.appendChild(categoryDivColumn);
                                                let previousDiv = cTag('div',{ 'class':`prevlist`,'style':'display:none'});
                                                    const leftArrowButton = cTag('button',{'click':preNextCategory, 'style':'background:initial', 'type':`button` });
                                                    leftArrowButton.innerHTML = '‹';
                                                previousDiv.appendChild(leftArrowButton);
                                            categoryDiv.appendChild(previousDiv);
                                                let nextDiv = cTag('div',{ 'class':`nextlist`,'style':'display:none' });
                                                    const rightArrowButton = cTag('button',{'click':preNextCategory, 'style':'background:initial', 'type':`button` });
                                                    rightArrowButton.innerHTML = '›';
                                                nextDiv.appendChild(rightArrowButton);
                                            categoryDiv.appendChild(nextDiv);
                                        tdCol.appendChild(categoryDiv);
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);

                                if(data.taxesRowCount>0){
                                        headRow = cTag('tr', {'class':`bgtitle`,});
                                            tdCol = cTag('td',{ 'colspan':`3`,'align':`right` });
                                            tdCol.innerHTML = ' ';
                                        headRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style': "text-align: right;" });
                                                let timeQtyTotal = cTag('label',{ 'id':`timeQtyTotal` });
                                                timeQtyTotal.innerHTML = 0;
                                            tdCol.appendChild(timeQtyTotal);
                                        headRow.appendChild(tdCol);
                                            tdCol = cTag('td',{'align':`right` });
                                                const taxableTotal = cTag('label');
                                                taxableTotal.innerHTML = Translate('Taxable Total')+' :';
                                            tdCol.appendChild(taxableTotal);
                                        headRow.appendChild(tdCol);
                                            tdCol = cTag('td',{'align':`right` });
                                                const currencyDiv = cTag('b',{ 'id':`taxable_totalstr`});
                                                currencyDiv.innerHTML = currency+'0.00';
                                            tdCol.appendChild(currencyDiv);                                                            
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable_total`,'id':`taxable_total`,'value':`0` }));
                                        headRow.appendChild(tdCol);
                                            tdCol = cTag('td');
                                            tdCol.innerHTML = ' ';
                                        headRow.appendChild(tdCol);
                                    salesRegisterBody.appendChild(headRow);

                                    if(data.taxesRowCount===1){
                                        let txtInc = '';
                                        if(data.tax_inclusive1>0){txtInc = ' Inclusive';}
                                        headRow = cTag('tr');
                                            tdCol = cTag('td',{'colspan':`5`, 'style': "text-align: right;"});
                                                let percentageDiv = cTag('span',{ 'style': "font-weight: bold;" });
                                                percentageDiv.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%`+`${txtInc}) :`;
                                            tdCol.appendChild(percentageDiv);
                                        headRow.appendChild(tdCol);
                                            tdCol = cTag('td',{'style': "text-align: right;" });
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':data.taxes_name1 }));
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':data.taxes_percentage1 }));
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':data.tax_inclusive1}));
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
                                                let currencyRow = cTag('span',{ 'id':`taxes_total1str`, 'style': "font-weight: bold; min-width:150px;display:inline-block; padding-left: 10px;" });
                                                currencyRow.innerHTML = currency+'0.00';
                                            tdCol.appendChild(currencyRow);
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
                                            tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                                                bTag = cTag('b',{ style:'display:none','id':`taxes_total2str` });
                                                bTag.innerHTML = currency+'0.00';
                                            tdCol.appendChild(bTag);
                                        headRow.appendChild(tdCol);
                                            tdCol = cTag('td');
                                            tdCol.innerHTML = ' ';
                                        headRow.appendChild(tdCol);
                                        salesRegisterBody.appendChild(headRow);
                                    }
                                    else{
                                            headRow = cTag('tr');
                                                tdCol = cTag('td',{ 'colspan':`5`, 'style': "text-align: right;" });
                                                    let taxDiv = cTag('div',{'class':'flexEndRow', 'style': "align-items: center;"});
                                                        let taxColumn = cTag('div',{ 'class':`columnXS3 columnMD1`, 'style': "font-weight: bold;" });
                                                        taxColumn.innerHTML = `${Translate('Tax')}${data.tax1} :`;
                                                    taxDiv.appendChild(taxColumn);
                                                        let taxDorpDown = cTag('div',{ 'class':` columnXS5 columnMD2` });
                                                            select = cTag('select',{ 'id':`taxes_id1`,'name':`taxes_id1`,'class':`form-control taxes_id`,'title':`1`,'change': ()=>onChangeTaxesId(1) });
                                                            setOptions(select,data.option1,1,1);
                                                        taxDorpDown.appendChild(select);
                                                    taxDiv.appendChild(taxDorpDown);
                                                tdCol.appendChild(taxDiv);
                                            headRow.appendChild(tdCol);
                                                tdCol = cTag('td',{'style': "text-align: right; vertical-align: middle;" });
                                                    let currencyValue = cTag('b',{'id':`taxes_total1str` });
                                                    currencyValue.innerHTML = currency+'0.00';
                                                tdCol.appendChild(currencyValue);
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':data.taxes_name1 }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':data.taxes_percentage1 }));
                                                tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':data.tax_inclusive1 }));
                                                tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_taxes_id1` }));
                                            headRow.appendChild(tdCol);
                                                tdCol = cTag('td');
                                                tdCol.innerHTML = ' ';
                                            headRow.appendChild(tdCol);
                                        salesRegisterBody.appendChild(headRow);

                                        if(data.defaultTaxCount>1){
                                                headRow = cTag('tr');
                                                    tdCol = cTag('td',{ 'colspan':`5`, 'style': "text-align: right;" });
                                                        let taxDiv = cTag('div',{'class':'flexEndRow', 'style': "align-items: center;"});
                                                            let taxColumn = cTag('div',{ 'class':`columnXS3 columnMD1`, 'style': "font-weight: bold;" });
                                                            taxColumn.innerHTML = `${Translate('Tax')}${data.tax2} :`;
                                                        taxDiv.appendChild(taxColumn);
                                                            let tax2DropDown = cTag('div',{ 'class':`columnXS5 columnMD2`, 'style': "font-weight: bold;" });
                                                                select = cTag('select',{ 'id':`taxes_id2`,'name':`taxes_id2`,'class':`form-control taxes_id`,'title':`2`,'change': ()=>onChangeTaxesId(2) });
                                                                setOptions(select,data.option2,1,1);
                                                            tax2DropDown.appendChild(select);
                                                        taxDiv.appendChild(tax2DropDown);
                                                    tdCol.appendChild(taxDiv);
                                                headRow.appendChild(tdCol);

                                                    tdCol = cTag('td',{'style': "text-align: right; vertical-align: middle;" });
                                                        let currencyValues = cTag('b',{ 'id':`taxes_total2str`});
                                                        currencyValues.innerHTML = currency+'0.00';
                                                    tdCol.appendChild(currencyValues);
                                                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                                                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':data.taxes_name2 }));
                                                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':data.taxes_percentage2 }));
                                                    tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':data.tax_inclusive2 }));
                                                    tdCol.appendChild(cTag('span',{ 'class':`error_msg`,'id':`errmsg_taxes_id2` }));
                                                headRow.appendChild(tdCol);
                                                    tdCol = cTag('td');
                                                    tdCol.innerHTML = ' ';
                                                headRow.appendChild(tdCol);
                                            salesRegisterBody.appendChild(headRow);
                                        }
                                        else{
                                            salesRegisterBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
                                            salesRegisterBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
                                            salesRegisterBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                                            salesRegisterBody.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
                                                bTag = cTag('b',{ style:'display:none','id':`taxes_total2str` });
                                                bTag.innerHTML = currency+'0.00';
                                            salesRegisterBody.appendChild(bTag);
                                        }
                                    }
                                }
                                else{
                                    headRow = cTag('tr',{ style:'display:none' });
                                        tdCol = cTag('td',{ 'colspan':`2` });
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'colspan':`2`,'class':`bgtitle`,'align':`right` });
                                            let taxableLabel = cTag('label');
                                            taxableLabel.innerHTML = Translate('Taxable Total')+' :';
                                        tdCol.appendChild(taxableLabel);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'class':`bgtitle`,'align':`right` });
                                            bTag = cTag('b',{ 'id':`taxable_totalstr` });
                                            bTag.innerHTML = currency+'0.00';
                                        tdCol.appendChild(bTag);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable_total`,'id':`taxable_total`,'value':`0` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'class':`bgtitle` });
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name1`,'id':`taxes_name1`,'value':`` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage1`,'id':`taxes_percentage1`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total1`,'id':`taxes_total1`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive1`,'id':`tax_inclusive1`,'value':`0` }));
                                        tdCol.appendChild(cTag('b',{ style:'display:none','id':`taxes_total1str` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_name2`,'id':`taxes_name2`,'value':`` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_percentage2`,'id':`taxes_percentage2`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxes_total2`,'id':`taxes_total2`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`tax_inclusive2`,'id':`tax_inclusive2`,'value':`0` }));
                                        tdCol.appendChild(cTag('b',{ style:'display:none','id':`taxes_total2str` }));
                                    headRow.appendChild(tdCol);
                                    salesRegisterBody.appendChild(headRow);
                                }                          
                                
                                    headRow = cTag('tr',{ 'id':`nontaxable_totalrow` });
                                        tdCol = cTag('td',{ 'colspan':`5`,'align':`right` });
                                            let nonTaxDiv = cTag('label');
                                            nonTaxDiv.innerHTML = Translate('Non Taxable Total')+' :';
                                        tdCol.appendChild(nonTaxDiv);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'align':`right` });
                                            let currencyValueDiv = cTag('b',{ 'id':`nontaxable_totalstr`});
                                            currencyValueDiv.innerHTML = currency+'0.00';
                                        tdCol.appendChild(currencyValueDiv);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`nontaxable_total`,'id':`nontaxable_total`,'value':`0` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td');
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);
                                    headRow = cTag('tr', {'class':`bgtitle`});
                                        tdCol = cTag('td',{ 'colspan':`5`,'align':`right`});
                                            let grandTotalDiv = cTag('label');
                                            grandTotalDiv.innerHTML = Translate('Grand Total')+' :';
                                        tdCol.appendChild(grandTotalDiv);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{'align':`right`});
                                            let grandCurrency = cTag('b',{'id':`grand_totalstr`});
                                            grandCurrency.innerHTML = currency+'0.00';
                                        tdCol.appendChild(grandCurrency);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`grand_total`,'id':`grand_total`,'value':`0` }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td');
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`2` });
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'class':`bgblack`,'colspan':`5`, 'style': "font-weight: bold; font-size: 16px;" });
                                        tdCol.innerHTML = Translate('Take payment');
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);
                            salesRegisterTable.appendChild(salesRegisterBody);
                                salesRegisterBody = cTag('tbody',{ 'id':`loadPOSPayment` });
                                loadPaymentData(salesRegisterBody,data.paymentData,filter_POS_index);
                            salesRegisterTable.appendChild(salesRegisterBody);
                                salesRegisterBody = cTag('tbody');
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`6`,'align':`right` });
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'readonly':``,'value':`${data.payment_datetime}?>`,'required':``,'name':`payment_datetime`,'id':`payment_datetime` }));
                                            let paymentDiv = cTag('div',{ 'class':`flexEndRow` });
                                                if(data.multiple_cash_drawers>0 && data.casDraOpts.length>0){
                                                    let drawerDropDown = cTag('div',{ 'class':`columnXS12 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
                                                        let selectDrawer = cTag('select',{ 'class':`form-control`,'name':`drawer`,'id':`drawer`,'change': setCDinCookie });
                                                        selectDrawer.addEventListener('change',togglePaymentButton)
                                                        if(data.drawer===''){
                                                                let drawerOption = cTag('option',{ 'value':`` });
                                                                drawerOption.innerHTML = Translate('Select Drawer');
                                                            selectDrawer.appendChild(drawerOption);
                                                        }
                                                        setOptions(selectDrawer,data.casDraOpts.filter(item=>item!==''),0,0);
                                                        selectDrawer.value = data.drawer;
                                                    drawerDropDown.appendChild(selectDrawer);
                                                    paymentDiv.appendChild(drawerDropDown);
                                                }
                                                else{
                                                    paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`drawer`,'id':`drawer`,'value':`` }));
                                                }

                                                let typeColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;" });
                                                    let inputGroupMethod = cTag('div',{ 'class':`input-group`, 'style': "min-width: 100px;"});
                                                        let typeSpan = cTag('span', { 'data-toggle':`tooltip`, 'data-original-title':Translate('Type'), 'class':`input-group-addon cursor`});
                                                        typeSpan.innerHTML = Translate('Type')+' :';

                                                        let selectType = cTag('select',{ 'class':`form-control`,'name':`method`,'id':`method`,'change': ()=>checkMethod(filter_POS_index) });
                                                        setOptions(selectType,data.methodOpts,0,0);
                                                    inputGroupMethod.append(typeSpan, selectType);
                                                typeColumn.appendChild(inputGroupMethod);
                                            paymentDiv.appendChild(typeColumn);

                                                let moneyColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnMD3 columnLG2`, 'style': "font-weight: bold;"});
                                                    let inputGroupAmount = cTag('div',{ 'class':`input-group`, 'style': "min-width: 100px;"});
                                                        let currencySpan = cTag('span',{ 'data-toggle':`tooltip`,'data-original-title':Translate('Currency'),'class':`input-group-addon cursor`});
                                                        currencySpan.innerHTML = currency;
                                                        inputField = cTag('input',{ 'type': "text",'data-min':'-9999999.99','data-max':'9999999.99','data-format':'d.dd','value':`0`,'name':`amount`,'id':`amount`,'class':` form-control`, 'style': "font-weight: bold; text-align: right;", 'keyup': ()=>checkMethod(filter_POS_index) });
                                                        inputField.addEventListener('keydown',event=>{if(event.which===13) addPOSPayment()});
                                                        controllNumericField(inputField, '#error_amount');
                                                    inputGroupAmount.append(currencySpan, inputField);
                                                moneyColumn.appendChild(inputGroupAmount);
                                            paymentDiv.appendChild(moneyColumn);
                                            
                                            paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`multiple_cash_drawers`,'id':`multiple_cash_drawers`,'value':data.multiple_cash_drawers }));
                                            paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`returnURL`,'id':`returnURL`,'value':`${location.origin}/POS/index/edit/` }));
                                            paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`sqrup_currency_code`,'id':`sqrup_currency_code`,'value':data.sqrup_currency_code }));
                                            paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`webcallbackurl`,'id':`webcallbackurl`,'value':data.webcallbackurl }));
                                            paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`accounts_id`,'id':`accounts_id`,'value':data.accounts_id }));
                                            paymentDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`user_id`,'id':`user_id`,'value':data.user_id }));
                                        tdCol.appendChild(paymentDiv);
                                        tdCol.appendChild(cTag('span',{ 'id':`error_amount`,'class':`errormsg` }));
                                    headRow.appendChild(tdCol);
                                    headRow.appendChild(cTag('td',{ 'id':`buttonPayment`,'style':'vertical-align: middle;' }));
                                salesRegisterBody.appendChild(headRow);
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'class':`bgtitle`,'colspan':`5`,'align':`right` });
                                            let amountDueLabel = cTag('label',{ 'for':`amount_due`,'id':`amount_duetxt` });
                                            amountDueLabel.innerHTML = Translate('Amount Due');
                                        tdCol.appendChild(amountDueLabel);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'class':`bgtitle`,'align':`right` });
                                            let currencyLabel = cTag('label',{ 'id':`amountduestr` });
                                            currencyLabel.innerHTML = currency+'0.00';
                                        tdCol.appendChild(currencyLabel);
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`amount_due`,'id':`amount_due`,'value':`` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`changemethod`,'id':`changemethod`,'value':'Cash' }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`available_credit`,'id':`available_credit`,'value':data.available_credit }));
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'class':`bgtitle` });
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);
                                    headRow = cTag('tr',{ 'id':`available_creditrow` });
                                    if(data.avaCreRowSty === 0) headRow.style.display = 'none'; 
                                        tdCol = cTag('td',{ 'colspan':`5`,'align':`right` });
                                            let creditLabel = cTag('label');
                                            creditLabel.innerHTML = Translate('Customer has available credit of')+' :';
                                        tdCol.appendChild(creditLabel);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                            let availableCreditLabel = cTag('label',{ 'id':`availableCreditLb` });
                                            availableCreditLabel.innerHTML = addCurrency(data.available_credit);
                                        tdCol.appendChild(availableCreditLabel);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td');
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);
                                    headRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`6` });
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`completed`,'id':`completed`,'value':`0` }));
                                        tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`frompage`,'id':`frompage`,'value':segment1 }));

                                            const buttonNames = cTag('div',{ 'class': "flexEndRow", 'style': "align-items: center;" });
                                                const buttonInGroup = cTag('div',{ 'class':`input-group` });
                                                    let completeButton = cTag('button',{ 'name':`CompleteBtn`,'id':`CompleteBtn`,'class':`btnFocus moneyIcon cursor`,'style':'display:none; border: none;' });
                                                    completeButton.addEventListener('click',completePOS);
                                                        let moneyIcon = cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;" });
                                                        let completeLabel = cTag('label');
                                                        completeLabel.innerHTML = Translate('Complete');
                                                    completeButton.append(moneyIcon, completeLabel);
                                                buttonInGroup.appendChild(completeButton);
                                                    let moneyDiv = cTag('button',{ 'name':`CompleteBtnDis`,'id':`CompleteBtnDis`,'class':`btnFocus`, 'style': "border: none;" });
                                                        let moneySpan = cTag('span',{ 'class':`input-group-addon` });
                                                        moneySpan.appendChild(cTag('i',{ 'class':`fa fa-money`, 'style': "font-size: 1.5em;" }));
                                                    moneyDiv.appendChild(moneySpan);
                                                        let spanComplete = cTag('span',{ 'class':`input-group-addon cursor`, 'style': "padding-left: 0;" });
                                                            let labelComplete = cTag('label');
                                                            labelComplete.innerHTML = Translate('Complete');
                                                        spanComplete.appendChild(labelComplete);
                                                    moneyDiv.appendChild(spanComplete);
                                                buttonInGroup.appendChild(moneyDiv);
                                                
                                                let posCartInGroup = cTag('div',{ 'class':`input-group`, 'style': " margin-right: 15px;"});
                                                    let posCartLink = cTag('button',{ 'class':`btnFocus iconButton cursor`, 'style': "border: none;", 'id':`clearPOSCart` });
                                                    posCartLink.addEventListener('click',clearPOSCart);													
                                                        let closeIcon = cTag('i',{ 'class':`fa fa-close`, 'style': "font-size: 1.5em;"});														
                                                        let startOverLabel = cTag('label');
                                                        startOverLabel.innerHTML = Translate('Start Over');
                                                    posCartLink.append(closeIcon, startOverLabel);
                                                posCartInGroup.appendChild(posCartLink);
                                            buttonNames.append(posCartInGroup, buttonInGroup);
                                        tdCol.appendChild(buttonNames);
                                    headRow.appendChild(tdCol);
                                        tdCol = cTag('td');
                                        tdCol.innerHTML = ' ';
                                    headRow.appendChild(tdCol);
                                salesRegisterBody.appendChild(headRow);
                            salesRegisterTable.appendChild(salesRegisterBody);
                        salesRegisterTableColumn.appendChild(salesRegisterTable);
                    salesRegisterContent.appendChild(salesRegisterTableColumn);
                salesRegisterForm.appendChild(salesRegisterContent);
            salesRegisterColumn.appendChild(salesRegisterForm);
        Dashboard.appendChild(salesRegisterColumn);

            const activityColumn = cTag('div',{ 'class':`columnSM12` });
            let hiddenProperties = {
                'note_forTable': 'pos' ,
                'spos_id': data.pos_id ,
                'table_idValue': data.pos_id ,
                'publicsShow': '1' ,
            }
            activityColumn.appendChild(historyTable(Translate('Sales History'),hiddenProperties,true));
            activityColumn.querySelector('#digital_signature_btn').addEventListener('click',()=>{printbyurl(`/${segment1}/prints/large/${data.pos_id}/${document.getElementById('amount_due').value}/signature`)});
        Dashboard.appendChild(activityColumn);

        //=======sessionStorage =========//
        if (sessionStorage.getItem("list_filters") !== null) {
            list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
        }
        else{
            list_filters = {};
        }
        let shistory_type = '';
        if(list_filters.hasOwnProperty("shistory_type")){
            shistory_type = list_filters.shistory_type;
            if(document.querySelector('#shistory_type')){
                select = document.querySelector('#shistory_type');
                    option = cTag('option', {'value': shistory_type});
                select.appendChild(option);
                select.value = shistory_type;
            }
        } 
        
        setTimeout(function() {
            if(document.getElementById("employee_id") && data.employee_id>0){
                document.getElementById("employee_id").value = data.employee_id;
            }
            if(document.getElementById("taxes_id1")){
                document.getElementById("taxes_id1").value = data.option1Val;
            }
            if(document.getElementById("taxes_id2")){
                document.getElementById("taxes_id2").value = data.option2Val;
            }
            if(document.getElementById("search_sku")){
                document.getElementById("search_sku").focus();
            }
        }, 500);

        document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));      

        if(document.getElementById("customerNameField") && document.getElementById("customer_name")){
            AJautoComplete('customer_name',selectCustomer)
        }

        if(document.getElementById("method") && document.getElementById("buttonPayment")){
            checkMethod();
            showOrNotSquareup();
        }

        AJautoComplete_cartProduct();

        cartsAutoFuncCall();
        togglePaymentButton();

        window.addEventListener('filter',filter_POS_index);
        window.addEventListener('loadTable',loadTableRows_POS_index);    
        window.addEventListener('changeCart',changeThisPOSRow);    
        filter_POS_index();
    }

    function posCustomerSave (customers_id,crlimit) {
        updatePOS('customer_id');					
        if(crlimit>0){
            checkAvailCredit(customers_id, crlimit);
        }
        else{
            document.getElementById("available_credit").value = 0;
            if(document.getElementById("available_creditrow").style.display !== 'none'){
                document.getElementById("available_creditrow").style.display = 'none';
            }
            document.getElementById('availableCreditLb').innerHTML = currency+'0.00';
            calculateCartTotal();
        }
    }
}

function changeThisPOSRow({detail:pos_cart_id}){
    let add_description = document.getElementById("add_description"+pos_cart_id).value;
    if(add_description !==''){add_description = add_description.replace(/<br\s*\/?>/gi,'');}
	const item_type = document.getElementById("item_type"+pos_cart_id).value;
	const product_type = document.getElementById("product_type"+pos_cart_id).value;
	let require_serial_no = parseInt(document.getElementById("require_serial_no"+pos_cart_id).value);
    if(isNaN(require_serial_no)){require_serial_no = 0;}
    let qtyRequired = 0;
    if(require_serial_no===1 || item_type==='cellphones'){qtyRequired = 1;}

	const sales_price = document.getElementById("sales_price"+pos_cart_id).value;
	const minimum_price = document.getElementById("minimum_price"+pos_cart_id).value;
	const qty = document.getElementById("qty"+pos_cart_id).value;		
	const discount_is_percent = document.getElementById("discount_is_percent"+pos_cart_id).value;
	const discount = document.getElementById("discount"+pos_cart_id).value;
	const taxable = document.getElementById("taxable"+pos_cart_id).value;
	let currencyoption = currency;
	if(currency ==='<i class="fa fa-inr" aria-hidden="true"></i>'){currencyoption = 'RS';}
	let priceReadonly = '';
	if(document.querySelector("#subPermission") && document.querySelector("#subPermission").value.includes('cnccp')){
		priceReadonly = ' readonly';
	}
	
	const formhtml = cTag('div');
		const posForm = cTag('form', {'action': "#", name: "frmPOSRow", id: "frmPOSRow", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			const errorRow = cTag('div', {class: "flexStartRow"});
				const divErroMsg = cTag('div', {class: "columnSM12", 'align': "left", id: "showErroMsg"});
            errorRow.appendChild(divErroMsg);
        posForm.appendChild(errorRow);
        
            let bTag, inputField, errorSpan;
			const unitPriceRow = cTag('div', {class: "flex", 'align': "left"});
                const unitPriceTitle = cTag('div', {class: "columnSM3"});
                    const unitPriceLabel = cTag('label', {'for': "sales_price"});
					unitPriceLabel.innerHTML = Translate('Unit Price')+ ':';
                unitPriceTitle.appendChild(unitPriceLabel);
            unitPriceRow.appendChild(unitPriceTitle);
                const unitPriceField = cTag('div', {class: "columnSM4"});
					inputField = cTag('input', {'type': "text", 'data-max':'9999999.99','data-format':'d.dd', class: "form-control updatecartfields", name: "sales_price", id: "sales_price", 'value': sales_price});
                    if(minimum_price>0) inputField.setAttribute('data-min', minimum_price);
					controllNumericField(inputField, '#errmsg_sales_price');
                    if(priceReadonly!==''){
						inputField.setAttribute('required', "required");
					}
                unitPriceField.appendChild(inputField);
                unitPriceField.appendChild(cTag('span', {class: "error_msg", id: "errmsg_sales_price"}));
                unitPriceField.appendChild(cTag('input', {type: "hidden", id: "minimum_price", value: minimum_price}));
            unitPriceRow.appendChild(unitPriceField);
                const unitPriceValue = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "salesPriceStr"});
					bTag.innerHTML = currency+'0.00';
                unitPriceValue.appendChild(bTag);
            unitPriceRow.appendChild(unitPriceValue);
        posForm.appendChild(unitPriceRow);

			const qtyRow = cTag('div', {class: "flex", 'align': "left"}); 
                const qtyTitle = cTag('div', {class: "columnSM3"});
                    const qtyLabel = cTag('label', {'for': "qty"});
                        const errSpan = cTag('span',{'class':'err_msg'});
                        errSpan.innerHTML = ' *';
					qtyLabel.append(Translate('QTY'),errSpan,':');
                qtyTitle.appendChild(qtyLabel);
            qtyRow.appendChild(qtyTitle);
                const qtyField = cTag('div', {class: "columnSM4"});
					inputField = cTag('input', {class: 'form-control updatecartfields', 'type': "text",'data-min':'0','data-max':'9999','data-format':'d', name: "qty", id: "qty", 'value': qty});
					controllNumericField(inputField,'#errmsg_qty');
                    if(product_type==='Labor/Services') inputField.setAttribute('data-format','d.dd');
                    if(qtyRequired===1){inputField.setAttribute('readonly', 'readonly');}
                qtyField.appendChild(inputField);
                    errorSpan = cTag('span', {class: "error_msg", id: "errmsg_qty"});
                qtyField.appendChild(errorSpan);
            qtyRow.appendChild(qtyField);
                const subTotalValue = cTag('div', {class: "columnSM5", 'align': "right"});
                subTotalValue.innerHTML = Translate('Subtotal')+ ': ';
					bTag = cTag('b', {id: "qtyValueStr"});
					bTag.innerHTML = currency+'0.00';
                subTotalValue.appendChild(bTag);
                    inputField = cTag('input', {'type': "hidden", name: "qty_value", id: "qty_value", 'value': 0});
                subTotalValue.appendChild(inputField);
            qtyRow.appendChild(subTotalValue);
        posForm.appendChild(qtyRow);

			const discountRow = cTag('div', {class: "flex", 'align': "left"}); 
                const discountTitle = cTag('div', {class: "columnSM3"});
                    const discountLabel = cTag('label', {'for': "discount"});
					discountLabel.innerHTML = Translate('Discount')+' :';
                discountTitle.appendChild(discountLabel);
            discountRow.appendChild(discountTitle);
                const discountField = cTag('div', {class: "columnSM4"});
					const discountInGroup = cTag('div', {class: "input-group"});
                        const discountSpan = cTag('span', {class: "input-group-addon", 'style': "min-width: 120px; padding: 0;"});
							inputField = cTag('input', {'maxlength': 9, id: "discount", name: "discount", 'type': "text",'data-min':'0','data-format':'d.dd', 'data-max': sales_price-minimum_price, 'value': discount, class: "form-control updatecartfields", 'style': "min-width: 120px;"});
                            controllNumericField(inputField, '#errmsg_discount');
							inputField.addEventListener('change', calculateChangeCartTotal);
                        discountSpan.appendChild(inputField);
                    discountInGroup.appendChild(discountSpan);
						let percentSpan = cTag('span', {class: "input-group-addon", 'style': "width: 40px; padding: 0;"});
							const selectPercent = cTag('select', {id: "discount_is_percent", name: "discount_is_percent", class: "form-control bgnone", 'style': "width: 40px; padding-left: 0; padding-right: 0;", 'value': "discount"});
							selectPercent.addEventListener('change', calculateChangeCartTotal);
								let percentOption = cTag('option', {'value': 1});
								percentOption.innerHTML = '%';
                            selectPercent.appendChild(percentOption);
								let currencyOption = cTag('option', {'value': 0});
								currencyOption.innerHTML = currencyoption;
                            selectPercent.appendChild(currencyOption);
                        percentSpan.appendChild(selectPercent);
                    discountInGroup.appendChild(percentSpan);
                discountField.appendChild(discountInGroup);
                discountField.appendChild(cTag('span', {class: "error_msg", id: "errmsg_discount"}));
            discountRow.appendChild(discountField);
				let discountValue = cTag('div', {class: "columnSM5", 'align': "right"});
					bTag = cTag('b', {id: "discountValueStr"});
					bTag.innerHTML = currency+'0.00';
                discountValue.appendChild(bTag);
					inputField = cTag('input', {'type': "hidden", name: "discountvalue", id: "discountvalue", 'value': 0});
                discountValue.appendChild(inputField);
            discountRow.appendChild(discountValue);
        posForm.appendChild(discountRow);
        posForm.appendChild(cTag('hr'));
        
			const totalRow = cTag('div', {class: "flexEndRow"});
                const totalValueError = cTag('div', {class: "columnSM7", 'align': "right"});
                totalValueError.appendChild(cTag('span', {class: "error_msg", id: "errmsg_unitPrice"}));
            totalRow.appendChild(totalValueError);
                const totalValue = cTag('div', {class: "columnSM5", 'align': "right"});
                    bTag = cTag('b');
                    bTag.innerHTML = Translate('Total')+': ';
                totalValue.appendChild(bTag);
					bTag = cTag('b', {id: "totalValueStr"});
					bTag.innerHTML = currency+'0.00';
                totalValue.appendChild(bTag);
                    inputField = cTag('input', {'type': "hidden", name: "unitPrice", id: "unitPrice", 'value': 0});
                totalValue.appendChild(inputField);
					inputField = cTag('input', {'type': "hidden", name: "taxable", id: "taxable", 'value': taxable});
                totalValue.appendChild(inputField);
            totalRow.appendChild(totalValue);
        posForm.appendChild(totalRow);

			const descriptionRow = cTag('div', {class: "flex", 'align': "left"}); 
                const descriptionTitle = cTag('div', {class: "columnSM3"});
                    const descriptionLabel = cTag('label', {'for': "add_description"});
					descriptionLabel.innerHTML = Translate('Additional Description')+':';
                descriptionTitle.appendChild(descriptionLabel);
            descriptionRow.appendChild(descriptionTitle);
				const descriptionField = cTag('div', {class: "columnSM9"});
					const textarea = cTag('textarea', {class: "form-control", name: "add_description", id: "add_description", 'rows': 2, 'cols': 20});
					textarea.innerHTML = add_description;
                descriptionField.appendChild(textarea);
            descriptionRow.appendChild(descriptionField);
        posForm.appendChild(descriptionRow);

			inputField = cTag('input', {'type': "hidden", name: "pos_cart_idvalue", id: "pos_cart_idvalue", 'value': pos_cart_id});
        posForm.appendChild(inputField);
	formhtml.appendChild(posForm);
	
	popup_dialog600(Translate('Update POS Cart'), formhtml, Translate('Save'), updateCartData);
			
	// setTimeout(function() {
    document.getElementById("sales_price").focus();
    if(item_type==='product' && require_serial_no===0){
        if(document.querySelector("#qty").readOnly){
            document.querySelector("#qty").readOnly = false;
        }
    }
    else{
        if(document.querySelector("#qty").readOnly===false){
            document.querySelector("#qty").readOnly = true;
        }		
    }
    
    document.getElementById("discount_is_percent").value = discount_is_percent;
    calculateChangeCartTotal();
    document.querySelectorAll(".updatecartfields").forEach(oneFieldObj=>{
        oneFieldObj.addEventListener('keyup', calculateChangeCartTotal);
        oneFieldObj.addEventListener('change', calculateChangeCartTotal);
    });
    applySanitizer(formhtml);
	// }, 500);
}

function clearPOSCart(){
	const hasdata = document.getElementById("invoice_entry_holder").innerHTML;
    let externalPayment;
	if(hasdata.length>10){
		externalPayment = 0;
		const payment_methodarray = document.getElementsByName("payment_method[]");
		if(payment_methodarray.length>0){
			for(let p=0; p<payment_methodarray.length; p++){
				const payment_method = payment_methodarray[p].value;
				if(payment_method === 'Squareup'){
					externalPayment++;
				}
			}
		}		
	}
	
	if(externalPayment>0){
		alert_dialog(Translate('Sales Start Over'), Translate('You can not cancel this sale because a payment has been made.'), Translate('Ok'));
	}
	else{
		confirm_dialog(Translate('Sales Start Over'), Translate('Are you sure you want to start over this sale?'), startOverPOS);
	}
}

async function completePOS(event,ignoreOverselling){
    const cash_reg_req_customer = document.getElementById("cash_reg_req_customer").value;
	if(cash_reg_req_customer>0){
		if(document.getElementById("customer_id").value==='' || parseInt(document.getElementById("customer_id").value)===0){
			showTopMessage('alert_msg', Translate('Missing customer name'));
			document.getElementById("customer_name").value = '';
			document.getElementById("customer_name").focus();
			return(false);
		}
	}
	const hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	const hasdata2 = document.getElementById("loadPOSPayment").innerHTML;
	if(hasdata.length<10 && hasdata2.length<10){
		showTopMessage('alert_msg', Translate('Missing cart. Please choose/add new product'));
		document.getElementById("search_sku").focus();
		return(false);
	}
	
	const amount_due = document.getElementById("amount_due").value;
	document.getElementById("changemethod").value = 'Cash';
	let changeamountofval = 0;
	if(amount_due<0){
		changeamountofval = amount_due;
	}

    //warn if any product oversold
    if(!ignoreOverselling && haveAnyOversoldProduct(completePOS)) return;

    let inputField;
	const formhtml = cTag('div');
		const completeForm = cTag('form', {'action': "#", name: "frmComplete", id: "frmComplete", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			const emptyDiv = cTag('div', {class: "flexSpaBetRow"});
        completeForm.appendChild(emptyDiv);
		
			const changeRow = cTag('div', {class: "flexSpaBetRow"});
				const changeTitle = cTag('div', {class: "columnXS12", 'align': "center"});
					const changeHeader = cTag('h4');
						let headerSpan = cTag('span', {'style': "color:orange; font-size:50px;", id: "changeamountof"});
						headerSpan.innerHTML = addCurrency(-1*changeamountofval);
                    changeHeader.appendChild(headerSpan);
                changeTitle.appendChild(changeHeader);
            changeRow.appendChild(changeTitle);
        completeForm.appendChild(changeRow);

            const emptyRow = cTag('div', {class: "flexSpaBetRow"});
        completeForm.appendChild(emptyRow);

			const exchangeMethodRow = cTag('div', {class: "flexStartRow"});
				const exchangeMethodTitle = cTag('div', {class: "columnSM7", 'align': "left"});
                    const exchangeMethodLabel = cTag('label', {'for': "exchangemethod"});
					exchangeMethodLabel.innerHTML = Translate('Choose how the change was given');
                exchangeMethodTitle.appendChild(exchangeMethodLabel);
            exchangeMethodRow.appendChild(exchangeMethodTitle);
				const exchangeMethodDropDown = cTag('div', {class: "columnSM5", 'align': "left"});
					const selectExchangeMethod = cTag('select', {class: "form-control", name: "exchangemethod", id: "exchangemethod"});
					selectExchangeMethod.addEventListener('change', e => {document.getElementById('changemethod').value = e.target.value;});
					selectExchangeMethod.innerHTML = document.getElementById("method").innerHTML;
                exchangeMethodDropDown.appendChild(selectExchangeMethod);
            exchangeMethodRow.appendChild(exchangeMethodDropDown);
        completeForm.appendChild(exchangeMethodRow);

			const choosePrintRow = cTag('div', {class: "flexStartRow"});
				const choosePrintTitle = cTag('div', {class: "columnSM4", 'align': "left"});
                    const choosePrintLabel = cTag('label', {'for': "default_invoice_printer1"});
					choosePrintLabel.innerHTML = Translate('Choose print type')+':';
                choosePrintTitle.appendChild(choosePrintLabel);
            choosePrintRow.appendChild(choosePrintTitle);
				const printColumn = cTag('div', {class: "columnSM8 flexStartRow"});
					const fullPrintLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
						inputField = cTag('input', {'type': "radio", 'value': "Large", id: "default_invoice_printer1", name: "print_type", class: "print_type"});
                    fullPrintLabel.appendChild(inputField);
					fullPrintLabel.append(' '+Translate('Full Page'));
                printColumn.appendChild(fullPrintLabel);
					const thermalLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "Small", id: "default_invoice_printer2", name: "print_type", class: "print_type"});
                    thermalLabel.appendChild(inputField);
					thermalLabel.append(' '+Translate('Thermal'));
                printColumn.appendChild(thermalLabel);
					const emailLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "Email", id: "default_invoice_printer3", name: "print_type", class: "print_type"});
                    emailLabel.appendChild(inputField);
					emailLabel.append(' '+Translate('Email'));
                printColumn.appendChild(emailLabel);
					const receiptLabel = cTag('label', {class:'columnXS6', 'style': "text-align: left;"});
                        inputField = cTag('input', {'type': "radio", 'value': "No Receipt", id: "default_invoice_printer4", name: "print_type", class: "print_type"});
                    receiptLabel.appendChild(inputField);
					receiptLabel.append(' '+Translate('No Receipt'));
                printColumn.appendChild(receiptLabel);
            choosePrintRow.appendChild(printColumn);
        completeForm.appendChild(choosePrintRow);

			const emailRow = cTag('div', {class: "flexStartRow invcustomeremail",style:'display:none'});
				const emailColumn = cTag('div', {class: "columnSM3", 'align': "left"});
                    const invoiceEmailLabel = cTag('label', {'for': "invcustomeremail"});
					invoiceEmailLabel.innerHTML = Translate('Email');
						const requiredSpan = cTag('span', {class: "required"});
						requiredSpan.innerHTML = '*';
                    invoiceEmailLabel.appendChild(requiredSpan);
                emailColumn.appendChild(invoiceEmailLabel);
            emailRow.appendChild(emailColumn);
				const emailField = cTag('div', {class: "columnSM9", 'align': "left"});
					const email_address = document.querySelector("#email_address").value;
					inputField = cTag('input', {'required': "required", 'maxlength': 50, 'type': "email", class: "form-control", name: "invcustomeremail", id: "invcustomeremail", 'value': email_address});
                emailField.appendChild(inputField);
            emailRow.appendChild(emailField);
        completeForm.appendChild(emailRow);
	formhtml.appendChild(completeForm);
	
	const title = Translate('Please give CHANGE of');
	const actionbutton = Translate('Complete');

    let print_type;
	popup_dialog600(title, formhtml, actionbutton, function(hidePopup) {
        actionBtnClick('.btnmodel', Translate('Saving'), 1);
        let print_typeselect = 0;
            const print_typeid = document.getElementsByName("print_type");
            print_type = '';
            if(print_typeid.length>0){
                for(let l=0; l<print_typeid.length; l++){
                    if(print_typeid[l].checked===true){
                        print_typeselect++;
                        print_type = print_typeid[l].value;
                    }
                }
            }
            
            if(print_typeselect===0){
                showTopMessage('alert_msg', Translate('You are missing print type'));
                actionBtnClick('.btnmodel', Translate('Complete'), 0);
                return false;
            }
            
            conformPOScompletion(print_type,hidePopup);
        });

        document.querySelectorAll(".print_type").forEach(oneFieldObj=>{
            oneFieldObj.addEventListener('click', e => {
                print_type = e.target.value;
                if(print_type==='Email'){
                    document.querySelectorAll(".invcustomeremail").forEach(e=>{
                        if(e.style.display === 'none'){
                            e.style.display = '';
                        }
                    });
                }
                else{
                    document.querySelectorAll(".invcustomeremail").forEach(e=>{
                        if(e.style.display !== 'none'){
                            e.style.display = 'none';
                        }
                    });
                }
            });
        });
	
	// setTimeout(function() {
		document.querySelectorAll(".invcustomeremail").forEach(e=>{
			if(e.style.display !== 'none'){
				e.style.display = 'none';
			}
		});
		print_type = document.getElementById("default_invoice_printer").value;
		if(print_type==='Large'){
			document.getElementById("default_invoice_printer1").checked = true;
		}
		else if(print_type==='Small'){
			document.getElementById("default_invoice_printer2").checked = true;
		}
		else if(print_type==='Email'){
			document.getElementById("default_invoice_printer3").checked = true;
			document.querySelectorAll(".invcustomeremail").forEach(e=>{
				if(e.style.display === 'none'){
					e.style.display = '';
				}
			});
		}		
		else{
			document.getElementById("default_invoice_printer4").checked = true;
		}
		document.getElementById("exchangemethod").focus();
	// }, 500);
	
	return false;
}

async function conformPOScompletion(print_type,hidePopup){
	let changeAmount;
    const email = document.getElementById("invcustomeremail").value;
	if(print_type==='Email' && !emailcheck(email)){
		document.getElementById("invcustomeremail").focus();
        actionBtnClick('.btnmodel', Translate('Complete'), 0);
		return false;
	}

	const jsonData = serialize('#frm_pos');

    const url = "/POS/completePOS/";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg ==='Completed'){
    		hidePopup();
			location.reload();
			return false;
		}
		else if(data.savemsg ==='error' && data.message !==''){
			if(data.message==='noCartAdded') showTopMessage('alert_msg', Translate('There is no cart added. Please try again with valid customer name.'));
			else if(data.message==='salesNotFound') showTopMessage('alert_msg', Translate('Sales name not found. Please try again with valid sales name.'));
			else if(data.message==='notAddPos') showTopMessage('alert_msg', Translate('Could not add data into POS.'));

			actionBtnClick('.btnmodel', Translate('Complete'), 0);
			return false;
		}
		else if(data.savemsg ==='success' && data.id>0){
            hidePopup();
			const printType = print_type.toLowerCase();
			const amount_due = parseFloat(document.getElementById("amount_due").value);
			const changemethod = document.getElementById("changemethod").value;
			changeAmount = 0;
			if(amount_due !==0 && changemethod==='Cash'){
				changeAmount = amount_due;
			}
			
			if(printType === 'large' || printType === 'small'){
                let redirectTo = '/Carts/cprints/'+printType+'/'+data.id;
				if(changeAmount !==0){redirectTo = redirectTo+'/'+changeAmount;}
				
                const day = new Date();
                const id = day.getTime();
                const w = 900;
                let h = 600;
                const scrl = 1;
                const winl = (screen.width - w) / 2;
                const wint = (screen.height - h) / 2;
                const winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
                window.open(redirectTo, '" + id + "', winprops);
                
				setTimeout(function() {
					window.location = '/POS';
					return false;
				}, 1000);
			}
			else if(printType==='email'){
				if(email !=='' && data.pos_id>0){
					document.getElementById("pos_id").value = data.pos_id;
					document.getElementById("email_address").value = email;
					emaildetails(false, '/Carts/AJ_sendposmail');
					setTimeout(function() {
						window.location = '/POS';return false;	
					}, 1000);
				}
				else{
					actionBtnClick('.btnmodel', Translate('Complete'), 0);
					showTopMessage('alert_msg', Translate('There is no email address for customer.'));
					return false;
				}
			}
			else{
				window.location = '/POS';return false;
			}
		}		
    }
	return false;
}

async function startOverPOS(hidePopup){
	let frompage = '';
	if(document.querySelector( "#frompage")){frompage = document.querySelector( "#frompage").value;}
	if(document.querySelectorAll( ".archive").length){
		document.querySelectorAll( ".archive").forEach(item=>{
			if(item.style.display !== 'none'){
				item.style.display = 'none';
			}
		});
	}

	const jsonData = {};
    const url = "/POS/startOverPOS/1";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.message !=='OK'){
			document.querySelectorAll( ".archive").forEach(item=>{
				if(item.style.display === 'none'){
					item.style.display = '';
				}
			});
			showTopMessage('error_msg', data.message);
			hidePopup();
		}
		else{
			hidePopup();
			if(frompage==='POS'){
				window.location = '/POS/';
			}
		}
    }
}

async function filter_POS_index(firstLoad = false){
    let page = 1;
    if(document.getElementById("page")){
        if(firstLoad){
            page = parseInt(document.getElementById("page").value);
            if(isNaN(page) || page===0){
                page = 1;
            }
        }
    	document.getElementById("page").value = page;
    }
	const jsonData = {};
	jsonData['spos_id'] = document.getElementById('table_idValue').value;
	jsonData['shistory_type'] = document.getElementById('shistory_type').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
    jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

    const url = "/POS/AJgetHPage/filter";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        const tdAttributes = [
            {'datatitle':Translate('Date'), 'nowrap':'nowrap', 'align':'left'},
            {'datatitle':Translate('Time'), 'nowrap':'nowrap', 'align':'right'},
            {'datatitle':Translate('User'),'align':'left'},
            {'datatitle':Translate('Activity'),'align':'left'},
            {'datatitle':Translate('Details'),'align':'left'},
        ];
        const select = document.getElementById("shistory_type");
        select.innerHTML = '';
            const option = cTag('option');
            option.value = '';
            option.innerHTML = Translate('All Activities');
        select.appendChild(option);
        setOptions(select,data.actFeeTitOpt,0,1);
        select.value = jsonData['shistory_type'];
        document.getElementById("totalTableRows").value = data.totalRows;
        setTableHRows(data.tableRows, tdAttributes);
        onClickPagination();
    }
}

async function loadTableRows_POS_index(){
	const jsonData = {};
	jsonData['spos_id'] = document.getElementById('table_idValue').value;
	jsonData['shistory_type'] = document.getElementById('shistory_type').value;
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
    jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = "/POS/AJgetHPage";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        const tdAttributes = [
            {'datatitle':Translate('Date'), 'nowrap':'nowrap', 'align':'left'},
            {'datatitle':Translate('Time'), 'nowrap':'nowrap', 'align':'right'},
            {'datatitle':Translate('User'),'align':'left'},
            {'datatitle':Translate('Activity'),'align':'left'},
            {'datatitle':Translate('Details'),'align':'left'},
        ];			
        setTableHRows(data.tableRows, tdAttributes);
        onClickPagination();
    }
}

async function updatePOS(fieldName){
	const fieldValue = document.querySelector("#"+fieldName).value;

	const jsonData = {};
	jsonData['fieldName'] = fieldName;
	jsonData['fieldValue'] = fieldValue;

    const url = "/POS/updatePOS";
    fetchData(afterFetch,url,jsonData);
    function afterFetch(data){
        if(data.returnval>999){location.reload();}
		else if(data.returnStr !==''){
			showTopMessage('alert_msg', data.returnStr);            
		}
        else{
            if(document.getElementById('customer_id').value==='0' && document.querySelector('#available_creditrow')) document.querySelector('#available_creditrow').style.display = 'none';
        }
    }
}

//=========Petty Cash============//
export async function AJget_pettyCashPopup(petty_cash_id){
	const jsonData = {};
	jsonData['petty_cash_id'] = petty_cash_id;

    const url = "/POS/AJget_pettyCashPopup";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let hiddenClass, requiredSpan, inputField;
        hiddenClass = '';
        if(data.petty_cash_id===0){hiddenClass = 'display:none;';}
        let multiple_cash_drawers = parseInt(document.getElementById("multiple_cash_drawers").value);
                                                
        let formhtml = cTag('div');
            let divError = cTag('div', {id: "error_petty_cash", class: "errormsg"});
        formhtml.appendChild(divError);

            const pettyCashForm = cTag('form', {'action': "#", name: "frmpetty_cash", id: "frmpetty_cash", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
                let pettyCashColumn = cTag('div',{ 'class': 'columnXS12' });
                        let eodDateRow = cTag('div',{class: 'flex',style:hiddenClass, 'align': "left"});
                            let eodDateTitle = cTag('div', {class: "columnSM4"});
                                let eodDateLabel = cTag('label', {'for': "peod_date"});
                                eodDateLabel.innerHTML = Translate('EOD Date');
                                    requiredSpan = cTag('span', {class: "required"});
                                    requiredSpan.innerHTML = '*';
                                eodDateLabel.appendChild(requiredSpan);
                            eodDateTitle.appendChild(eodDateLabel);
                        eodDateRow.appendChild(eodDateTitle);
                            const eodDateField = cTag('div', {class: "columnSM8"});
                                inputField = cTag('input', {'readonly': true, 'required': "required", 'type': "text", class: "form-control", name: "eod_date", id: "peod_date",  'value': DBDateToViewDate(data.eod_date), 'maxlength': 10});
                            eodDateField.appendChild(inputField);
                        eodDateRow.appendChild(eodDateField);
                    pettyCashColumn.appendChild(eodDateRow);

                        const addSubRow = cTag('div',{ 'class': 'flex', 'align': "left" });
                            const addSubTitle = cTag('div', {class: "columnSM4"});
                                const addSubLabel = cTag('label', {'for': "add_sub"});
                                addSubLabel.innerHTML = Translate('Add / Sub');
                                    requiredSpan = cTag('span', {class: "required"});
                                    requiredSpan.innerHTML = '*';
                                addSubLabel.appendChild(requiredSpan);
                            addSubTitle.appendChild(addSubLabel);
                        addSubRow.appendChild(addSubTitle);
                            const addSubDropDown = cTag('div', {class: "columnSM8"});
                                let selectAddSub = cTag('select', {'required': "required", class: "form-control", id: "add_sub", name: "add_sub"});
                                    let subtractOption = cTag('option', {'value': -1});
                                    subtractOption.innerHTML = Translate('Subtract');
                                selectAddSub.appendChild(subtractOption);
                                    let addittinOption = cTag('option', {'value': 1});
                                    if(data.add_sub===1){addittinOption.setAttribute('selected', 'selected');}
                                    addittinOption.innerHTML = Translate('Addition');
                                selectAddSub.appendChild(addittinOption);
                                selectAddSub.value = data.add_sub;
                            addSubDropDown.appendChild(selectAddSub);
                        addSubRow.appendChild(addSubDropDown);
                    pettyCashColumn.appendChild(addSubRow);

                    if(multiple_cash_drawers>0){
                            const cashDrawerRow = cTag('div',{ 'class': "flex drawerRow", 'align': "left" });
                                const cashDrawerTitle = cTag('div', {class: "columnSM4"});
                                    const cashDrawerLabel = cTag('label', {'for': "pdrawer"});
                                    cashDrawerLabel.innerHTML = Translate('Cash Drawers');
                                        requiredSpan = cTag('span', {class: "required"});
                                        requiredSpan.innerHTML = '*';
                                    cashDrawerLabel.appendChild(requiredSpan);
                                cashDrawerTitle.appendChild(cashDrawerLabel);
                            cashDrawerRow.appendChild(cashDrawerTitle);
                                const cashDrawerDropDown = cTag('div', {class: "columnSM8"});
                                    let selectDrawer = cTag('select', {'required': "required", class: "form-control", id: "pdrawer", name: "pdrawer"});
                                    selectDrawer.innerHTML = document.querySelector("#drawer").innerHTML;
                                    selectDrawer.value = document.querySelector("#drawer").value;
                                cashDrawerDropDown.appendChild(selectDrawer);
                                cashDrawerDropDown.appendChild(cTag('span', {id: "error_petty_cash_drawer", class: "errormsg"}));
                            cashDrawerRow.appendChild(cashDrawerDropDown);
                        pettyCashColumn.appendChild(cashDrawerRow);
                    }
                    else{
                            inputField = cTag('input', {'type': "hidden", class: "form-control", name: "drawer", id: "pdrawer",  'value': ""});
                        pettyCashColumn.appendChild(inputField);
                    }

                        const amountRow = cTag('div',{ 'class': "flex", 'align': "left" });
                            const amountTitle = cTag('div', {class: "columnSM4"});
                                const amountLabel = cTag('label', {'for': "pamount"});
                                amountLabel.innerHTML = Translate('Amount');
                                    requiredSpan = cTag('span', {class: "required"});
                                    requiredSpan.innerHTML = '*';
                                amountLabel.appendChild(requiredSpan);
                            amountTitle.appendChild(amountLabel);
                        amountRow.appendChild(amountTitle);
                            const amountField = cTag('div', {class: "columnSM8"});
                                inputField = cTag('input', {'required': "required", 'type': "text", 'data-min':'0', 'data-max':'9999999.99', 'data-format': 'd.dd', class: "form-control", name: "amount", id: "pamount",  'value': round(data.amount,2), 'maxlength': 10});
                                controllNumericField(inputField, '#error_pamount');
                            amountField.appendChild(inputField);
                            amountField.appendChild(cTag('span', {id: "error_pamount", class: "errormsg"}));
                        amountRow.appendChild(amountField);
                    pettyCashColumn.appendChild(amountRow);

                        const reasonRow = cTag('div',{ 'class': "flex", 'align': "left" });
                            const reasonTitle = cTag('div', {class: "columnSM4"});
                                const reasonLabel = cTag('label', {'for': "reason"});
                                reasonLabel.innerHTML = Translate('Reason');
                                    requiredSpan = cTag('span', {class: "required"});
                                    requiredSpan.innerHTML = '*';
                                reasonLabel.appendChild(requiredSpan);
                            reasonTitle.appendChild(reasonLabel);
                        reasonRow.appendChild(reasonTitle);
                            const reasonField = cTag('div', {class: "columnSM8"});
                                let textarea = cTag('textarea', {'required': "required", 'rows': 6, class: "form-control", name: "reason",id: "reason", 'maxlength': 250});
                                textarea.innerHTML = data.reason;
                            reasonField.appendChild(textarea);
                            reasonField.appendChild(cTag('span', {id: "error_reason", class: "errormsg"}));
                        reasonRow.appendChild(reasonField);
                    pettyCashColumn.appendChild(reasonRow);
                pettyCashForm.appendChild(pettyCashColumn);
                    inputField = cTag('input', {'type': "hidden", name: "petty_cash_id", id: "ppetty_cash_id",  'value': data.petty_cash_id});
                pettyCashForm.appendChild(inputField);
            formhtml.appendChild(pettyCashForm);
        
        popup_dialog600(Translate('Petty Cash Information'), formhtml, Translate('Save'), AJsave_pettyCash);
        
        setTimeout(function() {
            document.getElementById('add_sub').focus();
            date_picker('#peod_date');
            applySanitizer(formhtml);
        }, 500);
    
    }
	return false;
}

async function AJsave_pettyCash(hidePopup){
	let errorStatus = document.getElementById('error_petty_cash');
	let error_pamount = document.getElementById('error_pamount');
	let error_reason = document.getElementById('error_reason');
	errorStatus.innerHTML = '';
	error_pamount.innerHTML = '';
	error_reason.innerHTML = '';

	if(document.getElementById("peod_date").value===''){
		errorStatus.innerHTML = Translate('Missing EOD Date');
		document.getElementById("peod_date").focus();
		return false;
	}

	let multiple_cash_drawers = parseInt(document.getElementById("multiple_cash_drawers").value);
    let multipleDrawers = document.getElementById("pdrawer");
    if(multiple_cash_drawers>0){
        if(multipleDrawers.value===''){
            let error_petty_cash_drawer = document.getElementById('error_petty_cash_drawer');
            error_petty_cash_drawer.innerHTML = Translate('Missing drawer');
            multipleDrawers.focus();
            multipleDrawers.classList.add('errorFieldBorder');
            return false;
        }else {
            document.getElementById('error_petty_cash_drawer').innerHTML = '';
            multipleDrawers.classList.remove('errorFieldBorder');
        }
    }


    let pamount = document.getElementById("pamount");
    if (!validateRequiredField(pamount,'#error_pamount') || !pamount.valid()) return;
		
    let reasonId = document.getElementById("reason");
	if(reasonId.value===''){
		error_reason.innerHTML = Translate('Missing Reason');
		reasonId.focus();
        reasonId.classList.add('errorFieldBorder');
		return false;
	}else {
        reasonId.classList.remove('errorFieldBorder');
    }
		
	let petty_cash_id = document.getElementById('ppetty_cash_id').value;
	actionBtnClick('.btnmodel', Translate('Saving'), 1);

	const jsonData = serialize('#frmpetty_cash');

    const url = "/POS/AJsave_pettyCash/";
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error'){
			if(petty_cash_id>0){location.reload();}
			hidePopup();			
		}
        else if(data.returnStr=='errorOnAdding'){
			errorStatus.innerHTML = Translate('Error occured while adding new petty cash! Please try again.');
    		actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else if(data.returnStr=='errorOnEditing'){
			errorStatus.innerHTML = Translate('There is no changes made. Please try again.');
    		actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else{
			errorStatus.innerHTML = Translate('Petty cash information missing.');
            actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
    }
	return false;
}

function selectCustomer(fieldName,info){
    updatePOS(fieldName);
    if(info.crlimit>0){
        checkAvailCredit(info.id, info.crlimit);
    }
    else{
        document.querySelector( "#available_credit" ).value = 0;
        if(document.getElementById("available_creditrow").style.display !== 'none'){document.getElementById("available_creditrow").style.display = 'none';}
        document.getElementById('availableCreditLb').innerHTML = currency+'0.00';
    }
    calculateCartTotal();
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {index};
    layoutFunctions[segment2]();

});