import {cTag, Translate, addCurrency, calculate, DBDateToViewDate, getCookie, fetchData, triggerEvent, barcodeLabel, encodeToCode128} from './common.js';

let segment1 = '';
let segment2 = '';
let segment3 = '';
let segment4 = '';
let segment5 = '';
let segment6 = '';
let segment7 = '';
if(pathArray.length>1){
    segment1 = pathArray[1];
    if(pathArray.length>2){
        segment2 = pathArray[2];
        if(pathArray.length>3){
            segment3 = pathArray[3].replace(/%20/g, " ");
            if(pathArray.length>4){
                segment4 = pathArray[4].replace(/%20/g, " ");
                if(pathArray.length>5){
                    segment5 = pathArray[5].replace(/%20/g, " ");
                    if(pathArray.length>6){
                        segment6 = pathArray[6].replace(/%20/g, " ");
                        if(pathArray.length>7){
                            segment7 = pathArray[7].replace(/%20/g, " ");
                        }
                    }
                }
            }
            
        }
    }
}

let table_id;
let for_table;

const barcodeCss = `
    @font-face {
        font-family: 'Libre Barcode';
        src: url('/assets/fonts/LibreBarcodeText.woff2') format('woff2')
    }
    .barcode{
        font-family: 'Libre Barcode';
        font-size: 40px;
        line-height: 40px;
        white-space:nowrap;
        overflow-wrap: normal;
        display:inline-block;
    }
`

function signatureTemplate(){
    const div4 = cTag('div',{ 'class':`columnXS12`, 'style':`padding:0` });
    div4.appendChild(cTag('div',{ 'id':`showmessagehere` }));
        const signatureWidget = cTag('div',{ 'class':`cardContainer`, 'style': "margin-bottom: 10px;" });
            const signatureHeader = cTag('div',{ 'class':`cardHeader flexSpaBetRow` });
                const signatureInfo12 = cTag('div',{ 'class':`flex` });
                    const mobileIcon = cTag('i',{ 'class':`fa fa-mobile`, 'style': "margin-top: 14px; margin-right: 10px;" });
                signatureInfo12.appendChild(mobileIcon);
                    const signatureTitle = cTag('h3');
                    signatureTitle.innerHTML = Translate('Digital Signature');
                signatureInfo12.appendChild(signatureTitle);
            signatureHeader.appendChild(signatureInfo12);
                let buttonDiv = cTag('div',{ 'class':`invoiceorcompleted`, 'style': "float: right; margin-top: -1px; padding-right: 10px;" });
                    const closeButton = cTag('button',{ 'class':`btn printButton`,'click':()=>window.close() });
                    closeButton.innerHTML = Translate('Close');
                buttonDiv.appendChild(closeButton);
            signatureHeader.appendChild(buttonDiv);
        signatureWidget.appendChild(signatureHeader);
            const signatureContent = cTag('div',{ 'class':`widget-content` });
                const style = cTag('style',{ 'type':`text/css` });
                style.append(
                    `#signatureparent {color:darkblue;background-color:darkgrey;padding:1px;}
                    /*This is the div within which the signature canvas is fitted*/
                    #signature {border: 2px dotted #333;background-color:#fff; min-height:150px;}				
                    /* Drawing the 'gripper' for touch-enabled devices */ 
                    html.touch #content {float:left;width:98%;}
                    html.touch #scrollgrabber {float:right;width:1%;margin-right:0;background-color:#fff;}
                    html.borderradius #scrollgrabber {border-radius: 1em;}`
                )
            signatureContent.appendChild(style);
                let contentDiv = cTag('div',{ 'id':`content` });
                    let signatureDiv = cTag('div',{ 'id':`signatureparent` });
                    signatureDiv.appendChild(cTag('div',{ 'id':`signature` }));
                contentDiv.appendChild(signatureDiv);
                contentDiv.appendChild(cTag('div',{ 'id':`tools`, 'style': "margin-top: 6px;" }));
            signatureContent.appendChild(contentDiv);
            signatureContent.appendChild(cTag('div',{ 'id':`scrollgrabber` }));
        signatureWidget.appendChild(signatureContent);
    div4.appendChild(signatureWidget);
    return div4;
}

async function Repairs_customer(){ 
    await getAndPrintLabel('repairCustomerLabel');
}

async function Repairs_prints(){ 
    if(segment3 === 'label'){
        await getAndPrintLabel('repairTicketLabel');
    }
    else{ 
        if(segment3==='large') await AJ_Repairs_prints_large_MoreInfo();
        else if(segment3==='small') await AJ_Repairs_prints_small_MoreInfo();
        if(segment5 === 'signature'){                
            document.body.appendChild(signatureTemplate());
            digitalSignature();
        }
    }
}

async function AJ_Repairs_prints_large_MoreInfo(){
    const jsonData = {'repairs_id':segment4};
    const url = '/'+segment1+`/AJ_prints_large_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        table_id = data.table_id;
        for_table = data.for_table;
        let address,strong, tableHeadRow, tdCol, tableHeadRow1, tdCol1, pTag, repairTable, thCol, bTag;
        let totalQty = 0;
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = Translate('Repair Summary of Ticket')+' T'+data.ticket_no;
        head.appendChild(title);
            const style = cTag('style');
            style.append(
                `@page {size:portrait;}
                body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; min-width:100%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
                ${barcodeCss}
                `
            );
        head.appendChild(style);

        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const repairTable1 = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                        const headerTitle = cTag('h2');
                        headerTitle.innerHTML = data.title;
                    tdCol.appendChild(headerTitle);
                tableHeadRow.appendChild(tdCol);
            repairTable1.appendChild(tableHeadRow);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td',{ 'align':`left` });
                        repairTable = cTag('table',{ 'width':`100%`,'cellpadding':`0`,'cellspacing':`0`, 'style': "margin-top: 10px;"});

                        let barcodeStr = document.createDocumentFragment();
                        if(data.barcode===1){
                            barcodeStr.appendChild(cTag('div',{ 'style':`clear:both` }));
                                let barcode = cTag('span',{class:'barcode'});
                                barcode.innerHTML = encodeToCode128(String('t'+data.ticket_no));
                            barcodeStr.appendChild(barcode);
                            barcodeStr.appendChild(cTag('div',{ 'style':`clear:both` }));
                        }

                        if(data.logo_placement==='Center'){
                            if(data.companylogo !==''){
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`2`,'align':`center`, 'style': "padding-bottom: 10px; padding-top: 10px;" });
                                        tdCol.appendChild(creatCompanylogo(data.companylogo,data.logo_size));
                                    tableHeadRow.appendChild(tdCol);
                                repairTable.appendChild(tableHeadRow);
                            }
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`2`,'align':`center` });
                                    tdCol.innerHTML = data.company_info;
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`left`,'width':`50%` });
                                        address = cTag('address',{ 'class':`mbottom0` });
                                            let billSpan = cTag('span');
                                            billSpan.innerHTML = Translate('Bill To')+': ';
                                        address.appendChild(billSpan);
                                            strong = cTag('strong');
                                            data.customerName.forEach((name,indx)=>indx>0?strong.append(cTag('br'),name):strong.append(name));
                                        address.appendChild(strong);
                                        if(data.customerAddress.length){
                                            data.customerAddress.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.contactNo.length){
                                            data.contactNo.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customerEmail.length){
                                            data.customerEmail.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customFieldsData){
                                            for (const key in data.customFieldsData) {
                                                address.append(cTag('br'),`${key}: ${data.customFieldsData[key]}`);
                                            }
                                        }
                                    tdCol.appendChild(address);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        let ticketHeader = cTag('h4');
                                        ticketHeader.innerHTML = Translate('Ticket')+'#: t'+data.ticket_no;
                                    tdCol.appendChild(ticketHeader);
                                    if(data.salesPerson!==''){
                                        pTag = cTag('p');
                                        pTag.innerHTML = Translate('Sales Person')+': '+data.salesPerson;
                                        tdCol.append(pTag);
                                    }
                                    tdCol.append(barcodeStr);
                                        pTag = cTag('p');
                                        pTag.innerHTML = DBDateToViewDate(data.invoiceDate);
                                    tdCol.appendChild(pTag);
                                    if(data.statusStr !=='') tdCol.append(cTag('br'),`${Translate('Status')} : ${data.statusStr}`);

                                    if(data.duedatetime ===1 && data.due_datetime !==''){
                                        tdCol.append(cTag('br'),`${Translate('Due Date')} : ${DBDateToViewDate(data.due_datetime)}`);
                                        if(data.due_time !=''){tdCol.append(' ', `${data.due_time}`);}
                                    }
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                        }
                        else{
                            tableHeadRow = cTag('tr');
                            if(data.companylogo !==''){
                                    tdCol = cTag('td',{ 'width':`150`,'valign':`top`,'class':`pright15` });
                                    tdCol.appendChild(creatCompanylogo(data.companylogo,data.logo_size));
                                tableHeadRow.appendChild(tdCol);
                            }
                                    tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                                    tdCol.innerHTML = data.company_info;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'width':`35%`,'align':`right`,'rowspan':'2' });
                                        address = cTag('address',{ 'class':`mbottom0` });
                                            let billToSpan = cTag('span');
                                            billToSpan.innerHTML = Translate('Bill To')+': ';
                                        address.appendChild(billToSpan);
                                            strong = cTag('strong');
                                            data.customerName.forEach((name,indx)=>indx>0?strong.append(cTag('br'),name):strong.append(name));
                                        address.appendChild(strong);
                                        if(data.customerAddress.length){
                                            data.customerAddress.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.contactNo.length){
                                            data.contactNo.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customerEmail.length){
                                            data.customerEmail.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customFieldsData){
                                            for (const key in data.customFieldsData) {
                                                address.append(cTag('br'),`${key}: ${data.customFieldsData[key]}`);
                                            }
                                        }
                                    tdCol.appendChild(address);
                                        let ticketNoHeader = cTag('h4');
                                        ticketNoHeader.innerHTML = Translate('Ticket')+'#: t'+data.ticket_no;
                                    tdCol.appendChild(ticketNoHeader);
                                    if(data.salesPerson!==''){
                                        pTag = cTag('p');
                                        pTag.innerHTML = Translate('Sales Person')+': '+data.salesPerson;
                                        tdCol.append(pTag);
                                    }
                                    tdCol.append(barcodeStr);
                                        pTag = cTag('p');
                                        pTag.innerHTML = DBDateToViewDate(data.invoiceDate);
                                    tdCol.appendChild(pTag);
                                    if(data.statusStr !=='') tdCol.append(`${Translate('Status')} : ${data.statusStr}`);
                                    if(data.duedatetime ===1 && data.due_datetime !==''){
                                        tdCol.append(cTag('br'),`${Translate('Due Date')} : ${DBDateToViewDate(data.due_datetime)}`);
                                        if(data.due_time !=''){tdCol.append(' ', `${data.due_time}`);}
                                    }
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);        
                        }
                    tdCol1.appendChild(repairTable);
                tableHeadRow1.appendChild(tdCol1);
            repairTable1.appendChild(tableHeadRow1);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td',{ 'valign':`top`, 'style': "padding-bottom:10px;", 'align':`left` });
                        repairTable = cTag('table',{ 'class':`border`,'cellpadding':`0`,'cellspacing':`0`,'width':`100%` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`50%`,'align':`justify` });
                                thCol.innerHTML = Translate('Ticket Info');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'align':`justify` });
                                thCol.innerHTML = Translate('Hardware Info');
                            tableHeadRow.appendChild(thCol);
                        repairTable.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'valign':`top` });
                                let ticketInfoAdded = false;
                                if(!Array.isArray(data.ticketInfo) && (data.ticketInfo.technician && data.ticketInfo.technician !=='')){
                                    bTag = cTag('b');
                                    bTag.innerHTML = Translate('Technician')+': ';
                                    tdCol.append(bTag,data.ticketInfo.technician);
                                    ticketInfoAdded = true;
                                }
                                if(!Array.isArray(data.ticketInfo) && (data.ticketInfo.problem && data.ticketInfo.problem!=='')){
                                    if(ticketInfoAdded) tdCol.append(cTag('br'));
                                    else ticketInfoAdded = true;
                                    bTag = cTag('b');
                                    bTag.innerHTML = Translate('Problem')+': ';
                                    tdCol.append(bTag,data.ticketInfo.problem);
                                }
                                if(!Array.isArray(data.ticketInfo) && data.ticketInfo.custom_fields){
                                    for (const key in data.ticketInfo.custom_fields) {
                                        if(ticketInfoAdded) tdCol.append(cTag('br'));
                                        else ticketInfoAdded = true;
                                        bTag = cTag('b');
                                        bTag.innerHTML = key+': ';
                                        tdCol.append(bTag,data.ticketInfo.custom_fields[key]);
                                    }
                                }
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'valign':`top` });
                                let hardwareInfoAdded = false;
                                if(data.IMEI_Serial_No!==''){
                                    bTag = cTag('b');
                                    bTag.innerHTML = Translate('IMEI/Serial No.')+': ';
                                    tdCol.append(bTag,data.IMEI_Serial_No);
                                    hardwareInfoAdded = true;
                                }
                                if(data.Brand_Model_Details!==''){
                                    if(hardwareInfoAdded) tdCol.append(cTag('br'));
                                    else hardwareInfoAdded = true;
                                    bTag = cTag('b');
                                    bTag.innerHTML = Translate('Brand/Model/More Details:')+': ';
                                    tdCol.append(bTag,data.Brand_Model_Details);
                                }
                                if(data.Bin_Location!==''){
                                    if(hardwareInfoAdded) tdCol.append(cTag('br'));
                                    else hardwareInfoAdded = true;
                                    bTag = cTag('b');
                                    bTag.innerHTML = Translate('Bin Location')+': ';
                                    tdCol.append(bTag,data.Bin_Location);
                                }
                                if(data.Password!==''){
                                    if(hardwareInfoAdded) tdCol.append(cTag('br'));
                                    bTag = cTag('b');
                                    bTag.innerHTML = Translate('Password')+': ';
                                    tdCol.append(bTag,data.Password);
                                }
                            tableHeadRow.appendChild(tdCol);
                        repairTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(repairTable);
                tableHeadRow1.appendChild(tdCol1);
            repairTable1.appendChild(tableHeadRow1);                
            if(data.statusStr==='Estimate'){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`center` });
                            bTag = cTag('b');
                            bTag.innerHTML = Translate('Estimate');
                        tdCol.appendChild(bTag);
                    tableHeadRow.appendChild(tdCol);
                repairTable1.appendChild(tableHeadRow);
            }
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');
                        repairTable = cTag('table',{ 'class':`border`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`3%`,'align':`right` });
                                thCol.innerHTML = '#';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'align':`left` });
                                thCol.innerHTML = Translate('Description');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`8%` });
                                thCol.innerHTML = Translate('Time/Qty');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = Translate('Unit Price');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = Translate('Total');
                            tableHeadRow.appendChild(thCol);
                        repairTable.appendChild(tableHeadRow);
                        if(data.cartData){
                            if(data.cartData.length>0){
                                data.cartData.forEach((item,indx)=>{
                                    totalQty += item.shipping_qty;
                                        tableHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = indx+1;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`left` });
                                            tdCol.innerHTML = item.description;                                              
                                            if(item.add_description !=''){
                                                let addDesHTML = cTag('div', {class:'flex'});
                                                addDesHTML.innerHTML = item.add_description;
                                                tdCol.appendChild(addDesHTML);
                                            }
                                            generateImeiInfo(tdCol, item.newimei_info);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = item.shipping_qty;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.sales_price);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = addCurrency(calculate('mul',item.sales_price,item.shipping_qty,2));
                                            if(item.discount_value>0){
                                                tdCol.append(cTag('br'),`-${addCurrency(item.discount_value)}`);
                                            }
                                            else if(item.discount_value<0){
                                                tdCol.append(cTag('br'),`${addCurrency(item.discount_value*(-1))}`);
                                            }
                                        tableHeadRow.appendChild(tdCol);
                                    repairTable.appendChild(tableHeadRow);
                                })
                            }
                            else{
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`5` });
                                        tdCol.innerHTML = Translate('There is no data found');
                                    tableHeadRow.appendChild(tdCol);
                                repairTable.appendChild(tableHeadRow);
                            }
                        }
                        else{
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`5` });
                                    tdCol.innerHTML = Translate('There is no data found');
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                        }

                        let ti1Str = '';
                        let taxes_total1 = data.taxes_total1;
                        if(data.tax_inclusive1>0) {
                            ti1Str = ' Inclusive';
                            taxes_total1 = 0;
                        }
                        if(data.taxes_name1 !==''){
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{'colspan':`2`});
                                    tdCol.innerHTML = '';
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right`});
                                        strong = cTag('strong');
                                        strong.innerHTML = totalQty;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right`});
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Taxable Total')+' :';
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.taxable_total);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        strong = cTag('strong');
                                        strong.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%${ti1Str}) :`;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.taxes_total1);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                        }
                        let ti2Str = '';
                        let taxes_total2 = data.taxes_total2;
                        if(data.tax_inclusive2>0) {
                            ti2Str = ' Inclusive';
                            taxes_total2 = 0;
                        }
                        if(data.taxes_name2 !==''){
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        strong = cTag('strong');
                                        strong.innerHTML = `${data.taxes_name2} (${data.taxes_percentage2}%${ti2Str}) :`;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.taxes_total2);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                        }
                        
                        if(data.nontaxable_total>0 || data.nontaxable_total<0){
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Non Taxable Total')+' :';
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.nontaxable_total);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                        }                      
                        const grand_total = calculate('add',calculate('add',data.taxable_total,taxes_total1,2),calculate('add',taxes_total2,data.nontaxable_total,2),2);
                            tableHeadRow = cTag('tr');
                                /* tdCol = cTag('td',{ 'align':`right`,'colspan':`3` });
                                    strong = cTag('strong');
                                    strong.innerHTML = totalQty;
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol); */
                                tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                    strong = cTag('strong');
                                    strong.innerHTML = Translate('Grand Total')+' :';
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right` });
                                    strong = cTag('strong');
                                    strong.innerHTML = addCurrency(grand_total);
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                        repairTable.appendChild(tableHeadRow);
                        if(data.paymentData){
                            data.paymentData.forEach(item=>{
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        tdCol.innerHTML = `${DBDateToViewDate(item.payment_datetime)} ${item.payment_method} ${Translate('Payment')}`;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = addCurrency(item.payment_amount);
                                    tableHeadRow.appendChild(tdCol);
                                repairTable.appendChild(tableHeadRow);
                            })
                        }
                        if(data.amountDue !==0){
                                tableHeadRow = cTag('tr',{ 'class':`border` });
                                    tdCol = cTag('td',{ 'align':`center`,'colspan':`3` });
                                    tdCol.innerHTML = `${Translate('Total amount due by')} ${DBDateToViewDate(data.amountDueDate, 0, 1)}`;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right`,'nowrap':`` });
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Amount Due')+' :';
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.amountDue);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            repairTable.appendChild(tableHeadRow);
                        }
                    tdCol1.appendChild(repairTable);
                tableHeadRow1.appendChild(tdCol1);
            repairTable1.appendChild(tableHeadRow1);

                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`justify` });
                        pTag = cTag('p');
                        pTag.appendChild(cTag('br'));
                        pTag.innerHTML += data.repair_message;
                    tdCol.appendChild(pTag);
                    if(data.additional_disclaimer !== ''){
                            pTag = cTag('p');
                            pTag.innerHTML = data.additional_disclaimer;
                        tdCol.appendChild(pTag);
                    }
                tableHeadRow.appendChild(tdCol);
            repairTable1.appendChild(tableHeadRow);
            
            if(data.noteData.length){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td');
                        getPublicNotes(tdCol,data.noteData);
                    tableHeadRow.appendChild(tdCol);
                repairTable1.appendChild(tableHeadRow);
            }
            if(data.formsPublicData.length){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`center` });
                        getPublicFormData(tdCol,data.formsPublicData,'Repairs');
                    tableHeadRow.appendChild(tdCol);
                repairTable1.appendChild(tableHeadRow);
            }
        Dashboard.appendChild(repairTable1);
    }   
}

async function AJ_Repairs_prints_small_MoreInfo(){
    const jsonData = {'repairs_id':segment4};
    const url = '/'+segment1+`/AJ_prints_small_MoreInfo`;
    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){            
        let address,strong, tableHeadRow, tdCol, bTag, thCol, pTag;
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = Translate('Repair Summary of Ticket')+' T'+data.ticket_no;
        head.appendChild(title);
            const style = cTag('style');
            let addCss = '';
            if(data.left_margin>0) addCss+= `margin-left:${data.left_margin}px;`;
            if(data.right_margin>0) addCss+= `margin-right:${data.right_margin}px;`;
            style.append(
                `*{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica;font-size: 11px;}
                body{width:100%; margin:0; padding:0;background:#fff;color:#000;}
                @page {size:portrait;margin-top: 0;margin-bottom: 0;${addCss}}
                table{border-collapse:collapse;}
                tr.border td, tr.border th{ border:1px solid #CCC; padding:2px; vertical-align: top;}
                ${barcodeCss}`
            );
        head.appendChild(style);

        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const smallRepairTable = cTag('table',{ 'align':`center`,'width':`99.75%`,'cellpadding':`0`,'cellspacing':`0` });
            if(data.companylogo !=='') smallRepairTable.appendChild(creatCompanylogo(data.companylogo,data.logo_size,true));
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                        const titleHeader = cTag('h2');
                        titleHeader.innerHTML = data.title;
                    tdCol.appendChild(titleHeader);
                        address = cTag('address');
                        address.innerHTML = data.company_info;
                    tdCol.appendChild(address);
                tableHeadRow.appendChild(tdCol);
            smallRepairTable.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`left` });
                    tdCol.innerHTML = DBDateToViewDate(data.created_on, 1)[0];
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right`,'nowrap':`` });
                    tdCol.innerHTML = DBDateToViewDate(data.created_on, 1)[1];
                tableHeadRow.appendChild(tdCol);
            smallRepairTable.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });                        
                    if(data.customerName.length){
                        data.customerName.forEach((name,indx)=>indx>0?tdCol.append(cTag('br'),name):tdCol.append(name));
                    } 
                    if(data.customerAddress.length){
                        data.customerAddress.forEach(info=>{
                            tdCol.append(cTag('br'),info);
                        })
                    } 
                    if(data.contactNo.length){
                        data.contactNo.forEach(info=>{
                            tdCol.append(cTag('br'),info);
                        })
                    } 
                    if(data.customerEmail.length){
                        data.customerEmail.forEach(info=>{
                            tdCol.append(cTag('br'),info);
                        })
                    } 
                    if(data.customFieldsData){
                        for (const key in data.customFieldsData) {
                            tdCol.append(cTag('br'),`${key}: ${data.customFieldsData[key]}`);
                        }
                    }
                tableHeadRow.appendChild(tdCol);
            smallRepairTable.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                    tdCol.appendChild(cTag('br'));
                        strong = cTag('strong');
                        strong.innerHTML = Translate('Ticket')+'#: t'+data.ticket_no;
                    tdCol.appendChild(strong);
                    let barcodeStr = document.createDocumentFragment();
                    if(data.barcode===1){
                        barcodeStr.appendChild(cTag('div',{ 'style':`clear:both` }));
                            let barcode = cTag('span',{class:'barcode'});
                            barcode.innerHTML = encodeToCode128(String('t'+data.ticket_no));
                        barcodeStr.appendChild(barcode);
                        barcodeStr.appendChild(cTag('div',{ 'style':`clear:both` }));
                    }
                    if(data.salesPerson!=='') tdCol.append(cTag('br'),`${Translate('Sales Person')}: ${data.salesPerson}`);
                    
                    tdCol.append(barcodeStr);
                    tdCol.appendChild(cTag('br'));
                    tdCol.append(DBDateToViewDate(data.invoiceDate));
                    if(data.status !== '') tdCol.append(cTag('br'),`${Translate('Status')}: ${data.status}`);
                    if(data.duedatetime ===1 && data.due_datetime !==''){
                        tdCol.append(cTag('br'),`${Translate('Due Date')} : ${DBDateToViewDate(data.due_datetime)}`);
                        if(data.due_time !=''){tdCol.append(' ', `${data.due_time}`);}
                    }                        
                tableHeadRow.appendChild(tdCol);
            smallRepairTable.appendChild(tableHeadRow);
            if(data.technicianStr !== '' || data.problemStr !== ''){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'colspan':`2` });
                        tdCol.innerHTML = ' ';
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
                if(data.technicianStr !== ''){
                        tableHeadRow = cTag('tr');
                            tdCol = cTag('td',{ 'colspan':`2` });
                                bTag = cTag('b');
                                bTag.innerHTML = Translate('Technician')+': '; 
                            tdCol.append(bTag,data.technicianStr);
                        tableHeadRow.appendChild(tdCol);
                    smallRepairTable.appendChild(tableHeadRow);
                }
                if(data.problemStr !== ''){
                        tableHeadRow = cTag('tr');
                            tdCol = cTag('td',{ 'colspan':`2` });
                                bTag = cTag('b');
                                bTag.innerHTML = Translate('Problem')+': '; 
                            tdCol.append(bTag,data.problemStr);
                        tableHeadRow.appendChild(tdCol);
                    smallRepairTable.appendChild(tableHeadRow);
                }
            };
            if(data.custom_fields){
                for (const key in data.custom_fields) {
                        tableHeadRow = cTag('tr');
                            tdCol = cTag('td',{ 'colspan':`2` });
                            tdCol.append(`${key}: ${data.custom_fields[key]}`);
                        tableHeadRow.appendChild(tdCol);
                    smallRepairTable.appendChild(tableHeadRow);
                }
            }

                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'colspan':`2` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            smallRepairTable.appendChild(tableHeadRow);
            if(data.imei_or_serial_noStr !== ''){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                            bTag = cTag('b');
                            bTag.innerHTML = Translate('IMEI/Serial No.')+': '; 
                        tdCol.append(bTag,data.imei_or_serial_noStr);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
            if(data.Brand_Model_DetailsStr !== ''){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                            bTag = cTag('b');
                            bTag.innerHTML = Translate('Brand/Model/More Details:')+': '; 
                        tdCol.append(bTag,data.Brand_Model_DetailsStr);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
            if(data.bin_locationStr !== ''){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                            bTag = cTag('b');
                            bTag.innerHTML = Translate('Bin Location')+': '; 
                        tdCol.append(bTag,data.bin_locationStr);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
            if(data.lock_passwordStr !== ''){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                            bTag = cTag('b');
                            bTag.innerHTML = Translate('Password')+': '; 
                        tdCol.append(bTag,data.lock_passwordStr);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
                tableHeadRow = cTag('tr');
                    thCol = cTag('th',{ 'colspan':`2` });
                    thCol.appendChild(cTag('br'));
                tableHeadRow.appendChild(thCol);
            smallRepairTable.appendChild(tableHeadRow);
            if(data.status==='Estimate'){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                            bTag = cTag('b');
                            bTag.innerHTML = Translate('Estimate');
                        tdCol.appendChild(bTag);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    thCol = cTag('th',{ 'align':`left` });
                    thCol.innerHTML = Translate('Description');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`20%` });
                    thCol.innerHTML = Translate('Total');
                tableHeadRow.appendChild(thCol);
            smallRepairTable.appendChild(tableHeadRow);

            if(data.cartData){
                if(data.cartData.length>0){
                    data.cartData.forEach(item=>{
                        tableHeadRow = cTag('tr',{ 'class':`border` });
                            tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                            tdCol.innerHTML = item.description;                                              
                            if(item.add_description !=''){
                                let addDesHTML = cTag('div', {class:'flex'});
                                addDesHTML.innerHTML = item.add_description;
                                tdCol.appendChild(addDesHTML);
                            }
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'nowrap':``,'align':`right`,'valign':`top` });
                            tdCol.innerHTML = addCurrency(item.total);
                            if(item.discount_value>0){
                                tdCol.append(cTag('br'),`-${addCurrency(item.discount_value)}`);
                            }
                            else if(item.discount_value<0){
                                tdCol.append(cTag('br'),`${addCurrency(item.discount_value*(-1))}`);
                            }
                        tableHeadRow.appendChild(tdCol);
                        smallRepairTable.appendChild(tableHeadRow);
                    })
                }
                else{
                        tableHeadRow = cTag('tr',{ 'class':`border` });
                            tdCol = cTag('td',{ 'colspan':`2` });
                            tdCol.innerHTML = Translate('There is no data found');
                        tableHeadRow.appendChild(tdCol);
                    smallRepairTable.appendChild(tableHeadRow);
                }
            }
            else{
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'colspan':`2` });
                        tdCol.innerHTML = Translate('There is no data found');
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }

            let ti1Str = '';
            let taxes_total1 = data.taxes_total1;
            if(data.tax_inclusive1>0) {
                ti1Str = ' Inclusive';
                taxes_total1 = 0;
            }
            if(data.taxes_name1 !==''){
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = Translate('Taxable Total');
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(data.taxable_total);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%${ti1Str}) :`;
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(data.taxes_total1);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
            let ti2Str = '';
            let taxes_total2 = data.taxes_total2;
            if(data.tax_inclusive2>0) {
                ti2Str = ' Inclusive';
                taxes_total2 = 0;
            }
            if(data.taxes_name2 !==''){
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = `${data.taxes_name2} (${data.taxes_percentage2}%${ti2Str}) :`;
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(data.taxes_total2);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
            if(data.nontaxable_total>0 || data.nontaxable_total<0){
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = Translate('Non Taxable Total');
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(data.nontaxable_total);
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
            const grand_total = calculate('add',calculate('add',data.taxable_total,taxes_total1,2),calculate('add',taxes_total2,data.nontaxable_total,2),2);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = Translate('Grand Total');
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(grand_total);
                tableHeadRow.appendChild(tdCol);
            smallRepairTable.appendChild(tableHeadRow);

            if(data.paymentData){
                data.paymentData.forEach(item=>{
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = `${DBDateToViewDate(item.payment_datetime)} ${item.payment_method} ${Translate('Payment')}`;
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(item.payment_amount);
                    tableHeadRow.appendChild(tdCol);
                    smallRepairTable.appendChild(tableHeadRow);
                });
                if(data.amountDue !==0){
                        tableHeadRow = cTag('tr',{ 'class':`border` });
                            tdCol = cTag('td',{ 'align':`right`,'nowrap':`` });
                                strong = cTag('strong');
                                strong.innerHTML = Translate('Amount Due');
                            tdCol.appendChild(strong);
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'align':`right` });
                                strong = cTag('strong');
                                strong.innerHTML = addCurrency(data.amountDue);
                            tdCol.appendChild(strong);
                        tableHeadRow.appendChild(tdCol);
                    smallRepairTable.appendChild(tableHeadRow);
                }
            }
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'colspan':`2`,'align':`center` });
                        pTag = cTag('p');
                        pTag.appendChild(cTag('br'));
                        pTag.innerHTML += data.repair_message;
                    tdCol.appendChild(pTag);
                    if(data.additional_disclaimer !== ''){
                            pTag = cTag('p');
                            pTag.innerHTML = data.additional_disclaimer;
                        tdCol.appendChild(pTag);
                    }    
                tableHeadRow.appendChild(tdCol);
            smallRepairTable.appendChild(tableHeadRow);
            if(data.SmallNotes.length>0){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'colspan':`2` });
                        getPublicSmallNotes(tdCol,data.SmallNotes)
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
            if(data.formsPublicData.length){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'colspan':`2` });
                        getPublicFormData(tdCol,data.formsPublicData,'Repairs');
                    tableHeadRow.appendChild(tdCol);
                smallRepairTable.appendChild(tableHeadRow);
            }
        Dashboard.appendChild(smallRepairTable);
    }
}

async function Repairs_formsprints(){
    const jsonData = {
        'form_for':segment3,
        'table_id':segment4,
        'form_public':segment5,
        'viewfor':segment6,
        'forms_data_id':segment7,
    };
    const url = '/'+segment1+`/AJ_formsprints_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){ 
        const tt = 'Repairs '+Translate('Form');
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = tt;
        head.appendChild(title);
            const style = cTag('style');
            style.append(
                `@page {size: auto;}
                body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; min-width:100%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .pbottom10{padding-bottom:10px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:8px 10px; vertical-align: top;}`
            );
        head.appendChild(style);

        let tableHeadRow, tdCol;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const formPrintsTable = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                        const headerTitle = cTag('h2');
                        headerTitle.innerHTML = tt;
                    tdCol.appendChild(headerTitle);
                tableHeadRow.appendChild(tdCol);
            formPrintsTable.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            formPrintsTable.appendChild(tableHeadRow);
            if(data.tableData.length){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`center` });                    
                        getPublicFormData(tdCol,data.tableData,'Repairs');
                    tableHeadRow.appendChild(tdCol);
                formPrintsTable.appendChild(tableHeadRow);
            }
            else{
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`center` });
                        tdCol.innerHTML = Translate('There is no form data found.');
                    tableHeadRow.appendChild(tdCol);
                formPrintsTable.appendChild(tableHeadRow);
            }
        Dashboard.appendChild(formPrintsTable);    
    }
}

async function Carts_cprints(){
    await Orders_prints();
}

async function POS_prints(){
    await Orders_prints();
}

async function Orders_prints (){    
    if(segment3==='large') await AJ_Orders_prints_large_MoreInfo();
    else if(segment3==='small') await AJ_Orders_prints_small_MoreInfo();
    else if(segment3==='pick') await AJ_Orders_prints_pick_MoreInfo();
    if(segment6 === 'signature'){            
        document.body.appendChild(signatureTemplate());
        digitalSignature();
    }
    
}

async function AJ_Orders_prints_large_MoreInfo(){
    let amount_due = segment5;    
    const jsonData = {
        'amount_due':amount_due
    };
    if(segment1==='Carts') jsonData['invoice_no'] = segment4;
    else jsonData['pos_id'] = segment4;
    const url = '/'+segment1+`/AJ_${segment2}_${segment3}_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        table_id = data.table_id;
        for_table = data.for_table;
        let table,address,strong, tableHeadRow, tdCol, tableHeadRow1, tdCol1, orderTable, pTag, thCol;
        let totalQty = 0;

        const head = document.head;
            const title = cTag('title');
            if(data.title !=''){
                title.innerHTML = data.title;
            }
            else{
                title.innerHTML = `s${data.invoice_no}`;
            }
            title.append(' ', data.printerName);
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            let addCss = `size:${data.orientation};margin-top:${data.top_margin}px;margin-bottom:${data.bottom_margin}px;`;
            if(data.right_margin!==0) addCss+= `margin-right:${data.right_margin}px;`;
            if(data.left_margin!==0) addCss+= `margin-left:${data.left_margin}px;`;
            style.append(
                `@page {${addCss}}body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; min-width:100%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
                ${barcodeCss}`
            );
        head.appendChild(style);

        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const orderTable1 = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                        const titleHeader = cTag('h2');
                        titleHeader.innerHTML = data.title;
                    tdCol.appendChild(titleHeader);
                tableHeadRow.appendChild(tdCol);
            orderTable1.appendChild(tableHeadRow);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');                        
                        orderTable = cTag('table',{ 'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });

                        let barcodeStr = document.createDocumentFragment();
                        let invoiceNoStr = document.createDocumentFragment();
                        let invoicePreStr;
                        if(data.fromPage ==='Orders'){
                            const strong = cTag('strong');
                            strong.innerHTML = `${Translate('Order No.')}:o${data.invoice_no}`
                            invoiceNoStr.appendChild(strong);
                            invoicePreStr = 'o';
                        }
                        else{
                            const strong = cTag('strong');
                            strong.innerHTML = `${Translate('Sale Invoice #: s')}${data.invoice_no}`
                            invoiceNoStr.appendChild(strong);
                            invoicePreStr = 's';
                        }
                        if(data.barcode===1){
                                let barcode = cTag('span',{class:'barcode'});
                                barcode.innerHTML = encodeToCode128(String(invoicePreStr+data.invoice_no));
                            barcodeStr.appendChild(barcode);
                        }
                        let salesPerson='';
                        if(data.salesPerson!==''){
                            salesPerson = cTag('p');
                            salesPerson.innerHTML = Translate('Sales Person')+': '+data.salesPerson;
                        }

                        if(data.logo_placement==='Center'){
                            if(data.companylogo !==''){
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`2`,'align':`center`, 'style': "padding-bottom: 10px; padding-top: 10px;" });
                                    tdCol.appendChild(creatCompanylogo(data.companylogo,data.logo_size));
                                tableHeadRow.appendChild(tdCol);
                                orderTable.appendChild(tableHeadRow);
                            }
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`2`,'align':`center` });
                                    tdCol.innerHTML = data.company_info;
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`left`,'width':`50%` });
                                        address = cTag('address',{ 'class':`mbottom0` });
                                            let billSpan = cTag('span');
                                            billSpan.innerHTML = Translate('Bill To')+': ';
                                        address.appendChild(billSpan);
                                        address.append(' ');
                                            strong = cTag('strong');
                                            data.customerName.forEach((name,indx)=>indx>0?strong.append(cTag('br'),name):strong.append(name));
                                        address.appendChild(strong);
                                        if(data.customerAddress.length){
                                            data.customerAddress.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.contactNo.length){
                                            data.contactNo.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customerEmail.length){
                                            data.customerEmail.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customData){
                                            for (const key in data.customData) {
                                                address.append(cTag('br'),`${key}: ${data.customData[key]}`);
                                            }
                                        }
                                    tdCol.appendChild(address);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                    tdCol.append(invoiceNoStr, salesPerson, barcodeStr);
                                        pTag = cTag('p');
                                        pTag.innerHTML = DBDateToViewDate(data.invoiceDate);
                                    tdCol.appendChild(pTag);
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }
                        else{
                                tableHeadRow = cTag('tr');
                                if(data.companylogo !==''){
                                        tdCol = cTag('td',{ 'width':`150`,'valign':`top`,'class':`pright15` });
                                        tdCol.appendChild(creatCompanylogo(data.companylogo,data.logo_size));
                                    tableHeadRow.appendChild(tdCol);
                                }
                                    tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                                    tdCol.innerHTML = data.company_info;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'width':`35%`,'align':`right`,'rowspan':`2` });
                                        address = cTag('address',{ 'class':`mbottom0` });
                                            let billToSpan = cTag('span');
                                            billToSpan.innerHTML = Translate('Bill To')+':';
                                        address.appendChild(billToSpan);
                                        address.append(' ');
                                            strong = cTag('strong');
                                            data.customerName.forEach((name,indx)=>indx>0?strong.append(cTag('br'),name):strong.append(name));
                                        address.appendChild(strong);
                                        if(data.customerAddress.length){
                                            data.customerAddress.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.contactNo.length){
                                            data.contactNo.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customerEmail.length){
                                            data.customerEmail.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customData){
                                            for (const key in data.customData) {
                                                address.append(cTag('br'),`${key}: ${data.customData[key]}`);
                                            }
                                        }
                                    tdCol.appendChild(address);
                                    tdCol.append(invoiceNoStr, salesPerson, barcodeStr);
                                        pTag = cTag('p');
                                        pTag.innerHTML = DBDateToViewDate(data.invoiceDate);
                                    tdCol.appendChild(pTag);
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }

                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':data.colSpan,'align':`left`, 'style': "padding-bottom:10px;" });
                                tdCol.innerHTML = data.invoice_message_above;
                            tableHeadRow.appendChild(tdCol);
                        orderTable.appendChild(tableHeadRow); 
                    tdCol1.appendChild(orderTable);                           
                tableHeadRow1.appendChild(tdCol1);
            orderTable1.appendChild(tableHeadRow1);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');
                        orderTable = cTag('table',{ 'class':`border`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`3%`,'align':`right` });
                                thCol.innerHTML = '#';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'align':`left` });
                                thCol.innerHTML = Translate('Description');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`8%` });
                                thCol.innerHTML = Translate('Time/Qty');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`14%` });
                                thCol.innerHTML = Translate('Unit Price');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = Translate('Total');
                            tableHeadRow.appendChild(thCol);
                        orderTable.appendChild(tableHeadRow);

                        if(data.cartData){
                            if(data.cartData.length>0){
                                data.cartData.forEach((item,indx)=>{
                                    totalQty += item.shipping_qty;
                                        tableHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = indx+1;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`left` });
                                            tdCol.innerHTML = item.description;                                              
                                            if(item.add_description !=''){
                                                let addDesHTML = cTag('div', {class:'flex'});
                                                addDesHTML.innerHTML = item.add_description;
                                                tdCol.appendChild(addDesHTML);
                                            }
                                            generateImeiInfo(tdCol, item.newimei_info);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = item.shipping_qty;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.sales_price);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = addCurrency(calculate('mul',item.shipping_qty,item.sales_price,2));
                                            if(item.discount_value>0){
                                                tdCol.append(cTag('br'),`-${addCurrency(item.discount_value)}`);
                                            }
                                            else if(item.discount_value<0){
                                                tdCol.append(cTag('br'),`${addCurrency(item.discount_value*(-1))}`);
                                            }
                                        tableHeadRow.appendChild(tdCol);
                                    orderTable.appendChild(tableHeadRow);
                                })
                            }
                            else{
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`5` });
                                        tdCol.innerHTML = Translate('There is no data found');
                                    tableHeadRow.appendChild(tdCol);
                                orderTable.appendChild(tableHeadRow);
                            }
                        }
                        else{
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`5` });
                                    tdCol.innerHTML = Translate('There is no data found');
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }

                        let ti1Str = '';
                        let taxes_total1 = data.taxes_total1;
                        if(data.tax_inclusive1>0) {
                            ti1Str = ' Inclusive';
                            taxes_total1 = 0;
                        }
                        if(data.taxes_name1 !==''){
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{'colspan':`2`});
                                    tdCol.innerHTML = '';
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right`});
                                        strong = cTag('strong');
                                        strong.innerHTML = totalQty;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right`});
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Taxable Total')+' :';
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.taxable_total);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        strong = cTag('strong');
                                        strong.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%${ti1Str}) :`;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.taxes_total1);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }

                        let ti2Str = '';
                        let taxes_total2 = data.taxes_total2;
                        if(data.tax_inclusive2>0) {
                            ti2Str = ' Inclusive';
                            taxes_total2 = 0;
                        }
                        if(data.taxes_name2 !==''){
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        strong = cTag('strong');
                                        strong.innerHTML = `${data.taxes_name2} (${data.taxes_percentage2}%${ti2Str}) :`;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.taxes_total2);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }

                        if(data.nontaxable_total>0 || data.nontaxable_total<0){
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Non Taxable Total')+' :';
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.nontaxable_total);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }
                        const grand_total = calculate('add',calculate('add',data.taxable_total,taxes_total1,2),calculate('add',taxes_total2,data.nontaxable_total,2),2);
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                    strong = cTag('strong');
                                    strong.innerHTML = Translate('Grand Total')+' :';
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right` });
                                    strong = cTag('strong');
                                    strong.innerHTML = addCurrency(grand_total);
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                        orderTable.appendChild(tableHeadRow);

                        if(data.paymentData){
                            data.paymentData.forEach(item=>{
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                                        tdCol.innerHTML = `${DBDateToViewDate(item.payment_datetime)} ${item.payment_method} ${Translate('Payment')}`;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = addCurrency(item.payment_amount);
                                    tableHeadRow.appendChild(tdCol);
                                orderTable.appendChild(tableHeadRow);
                            });
                        }

                        if(data.amountDue !==0){
                                tableHeadRow = cTag('tr',{ 'class':`border` });
                                    tdCol = cTag('td',{ 'align':`center`,'colspan':`3` });
                                    if(data.DueByStr !==''){
                                        tdCol.innerHTML = data.DueByStr+DBDateToViewDate(data.amountDueDate, 0, 1);
                                    }                                        
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right`,'nowrap':`` });
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Amount Due')+' :';
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(data.amountDue);
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }

                        if(amount_due<0){
                                tableHeadRow = cTag('tr',{ 'class':`border` });
                                    tdCol = cTag('td',{ 'colspan':`5`,'align':`center` });
                                    tdCol.innerHTML = `${Translate('Please give change amount of')} ${addCurrency(amount_due*(-1))}`;
                                tableHeadRow.appendChild(tdCol);
                            orderTable.appendChild(tableHeadRow);
                        }
                    tdCol1.appendChild(orderTable);
                tableHeadRow1.appendChild(tdCol1);
            orderTable1.appendChild(tableHeadRow1);
            
            if(data.getNotes.length>0){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td');
                        getPublicNotes(tdCol,data.getNotes);
                    tableHeadRow.appendChild(tdCol);
                orderTable1.appendChild(tableHeadRow);
            }
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.innerHTML = data.invoice_message;
                tableHeadRow.appendChild(tdCol);
            orderTable1.appendChild(tableHeadRow);

            if(data.marketing_data !== ''){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                        tdCol.innerHTML = data.marketing_data;
                    tableHeadRow.appendChild(tdCol);
                orderTable1.appendChild(tableHeadRow);
            }
        Dashboard.appendChild(orderTable1);
    }
}

async function AJ_Orders_prints_small_MoreInfo(){
    let amount_due = segment5;
    const jsonData = {'amount_due':amount_due};

    if(segment1==='Carts') jsonData['invoice_no'] = segment4;
    else jsonData['pos_id'] = segment4;
    const url = '/'+segment1+`/AJ_${segment2}_${segment3}_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let tableHeadRow, tdCol, thCol;
        const head = document.head;
            const title = cTag('title');
            if(data.title !=''){
                title.innerHTML = data.title;
            }
            else{
                title.innerHTML = `s${data.invoice_no}`;
            }
            title.append(' ', data.printerName);
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            let addCss = `size:${data.orientation};margin-top:${data.top_margin}px;margin-bottom:${data.bottom_margin}px;`;
            if(data.right_margin!==0) addCss+= `margin-right:${data.right_margin}px;`;
            if(data.left_margin!==0) addCss+= `margin-left:${data.left_margin}px;`;
            style.append(
                `*{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica;font-size: 11px;}
                body{width:100%; margin:0; padding:0;background:#fff;color:#000;}
                @page {${addCss}}
                table{border-collapse:collapse;}
                tr.border td, tr.border th{ border:1px solid #CCC; padding:2px; vertical-align: top;}
                ${barcodeCss}`
            );
        head.appendChild(style);

        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';

        const smallOrderTable = cTag('table',{ 'align':`center`,'width':`99.75%`,'cellpadding':`0`,'cellspacing':`0` });
        if(data.companylogo !=='') smallOrderTable.appendChild(creatCompanylogo(data.companylogo,data.logo_size,true));
            tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                    const headerTitle = cTag('h2');
                    headerTitle.innerHTML = data.title;
                tdCol.appendChild(headerTitle);
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);
            tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                    const address = cTag('address');
                    address.innerHTML = data.company_info;
                tdCol.appendChild(address);
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);
            tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'align':`left` });
                tdCol.innerHTML = DBDateToViewDate(data.sales_datetime, 1)[0];
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'align':`right`,'nowrap':`` });
                    let dateTimeSpan = cTag('span');
                    dateTimeSpan.innerHTML = DBDateToViewDate(data.sales_datetime, 1)[1];
                tdCol.appendChild(dateTimeSpan);
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);
        if(data.salesPerson !== ''){
            tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                    const strong = cTag('strong');
                    strong.innerHTML = Translate('Sales Person')+' : ';
                tdCol.appendChild(strong);
                tdCol.append(data.salesPerson);
            tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }
            tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                if(data.customerName.length){
                    data.customerName.forEach((name,indx)=>indx>0?tdCol.append(cTag('br'),name):tdCol.append(name));
                } 
                if(data.customerAddress.length){
                    data.customerAddress.forEach(info=>{
                        tdCol.append(cTag('br'),info);
                    })
                } 
                if(data.contactNo.length){
                    data.contactNo.forEach(info=>{
                        tdCol.append(cTag('br'),info);
                    })
                } 
                if(data.customerEmail.length){
                    data.customerEmail.forEach(info=>{
                        tdCol.append(cTag('br'),info);
                    })
                } 
                if(data.customData){
                    for (const key in data.customData) {
                        tdCol.append(cTag('br'),`${key}: ${data.customData[key]}`);
                    }
                }
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);
             tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'align':`left`,'colspan':`2` });
                let barcodeStr = document.createDocumentFragment();
                let invoice_noStr = document.createDocumentFragment();
                let invoicePreStr;
                if(data.fromPage ==='Orders'){
                    const strong = cTag('strong');
                    strong.innerHTML = `${Translate('Order No.')}:o${data.invoice_no}`
                    invoice_noStr.appendChild(strong);
                    invoicePreStr = 'o';
                }
                else{
                    const strong = cTag('strong');
                    strong.innerHTML = `${Translate('Sale Invoice #: s')}${data.invoice_no}`
                    invoice_noStr.appendChild(strong);
                    invoicePreStr = 's';
                }
                if(data.barcode===1){
                        let barcode = cTag('span',{class:'barcode','style':'display:block'});
                        barcode.innerHTML = encodeToCode128(String(invoicePreStr+data.invoice_no));
                    barcodeStr.appendChild(barcode);
                }
                tdCol.append(invoice_noStr,barcodeStr);
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);
             tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'colspan':`2`,'align':`left`,'style':`padding-bottom:10px;` });
                tdCol.innerHTML = data.invoice_message_above;
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);
             tableHeadRow = cTag('tr',{ 'class':`border` });
                thCol = cTag('th',{ 'align':`left` });
                thCol.innerHTML = Translate('Description');
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'width':`10%` });
                thCol.innerHTML = Translate('Total');
            tableHeadRow.appendChild(thCol);
        smallOrderTable.appendChild(tableHeadRow);

        if(data.cartData){
            if(data.cartData.length>0){
                data.cartData.forEach(item=>{
                        tableHeadRow = cTag('tr',{ 'class':`border` });
                            tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                            tdCol.innerHTML = item.description;                                              
                            if(item.add_description !=''){
                                let addDesHTML = cTag('div', {class:'flex'});
                                addDesHTML.innerHTML = item.add_description;
                                tdCol.appendChild(addDesHTML);
                            }
                            generateImeiInfo(tdCol, item.newimei_info);
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'align':`right`,'nowrap':``,'valign':`top` });
                            tdCol.innerHTML = addCurrency(item.total);
                            if(item.discount_value>0){
                                tdCol.append(cTag('br'),`-${addCurrency(item.discount_value)}`);
                            }
                            else if(item.discount_value<0){
                                tdCol.append(cTag('br'),`${addCurrency(item.discount_value*(-1))}`);
                            }
                        tableHeadRow.appendChild(tdCol);
                    smallOrderTable.appendChild(tableHeadRow);
                })
            }
            else{
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'colspan':`2` });
                        tdCol.innerHTML = Translate('There is no data found');
                    tableHeadRow.appendChild(tdCol);
                smallOrderTable.appendChild(tableHeadRow);
            }
        }
        else{
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'colspan':`2` });
                    tdCol.innerHTML = Translate('There is no data found');
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }

        let ti1Str = '';
        let taxes_total1 = data.taxes_total1;
        if(data.tax_inclusive1>0) {
            ti1Str = ' Inclusive';
            taxes_total1 = 0;
        }
        if(data.taxes_name1 !== ''){
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = Translate('Taxable Total');
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.taxable_total);
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${data.taxes_name1} (${data.taxes_percentage1}%${ti1Str}) :`;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.taxes_total1);
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }

        let ti2Str = '';
        let taxes_total2 = data.taxes_total2;
        if(data.tax_inclusive2>0) {
            ti2Str = ' Inclusive';
            taxes_total2 = 0;
        }
        if(data.taxes_name2 !==''){
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${data.taxes_name2} (${data.taxes_percentage2}%${ti2Str}) :`;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.taxes_total2);
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }
        if(data.nontaxable_total>0 || data.nontaxable_total<0){
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = Translate('Non Taxable Total');
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.nontaxable_total);
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }
        const grand_total = calculate('add',calculate('add',data.taxable_total,taxes_total1,2),calculate('add',taxes_total2,data.nontaxable_total,2),2);
            tableHeadRow = cTag('tr',{ 'class':`border` });
                tdCol = cTag('td',{ 'align':`right` });
                tdCol.innerHTML = Translate('Grand Total');
            tableHeadRow.appendChild(tdCol);
                tdCol = cTag('td',{ 'align':`right` });
                tdCol.innerHTML = addCurrency(grand_total);
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);

        if(data.paymentData){
            data.paymentData.forEach(item=>{
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = `${DBDateToViewDate(item.payment_datetime)} ${item.payment_method} ${Translate('Payment')}`;
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(item.payment_amount);
                    tableHeadRow.appendChild(tdCol);
                smallOrderTable.appendChild(tableHeadRow);
            })
        }
        if(data.amountDue !==0){
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right`,'nowrap':`` });
                    if(segment1 === 'Orders') tdCol.innerHTML = Translate('Amount Due');
                    else tdCol.innerHTML = Translate('Total amount due by')+' '+DBDateToViewDate(data.amountDueDate, 0, 1);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.amountDue);
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }
        if(amount_due<0){
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'colspan':`2`,'align':`center` });
                    tdCol.innerHTML = `${Translate('Please give change amount of')} ${addCurrency(amount_due*(-1))}}`;
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }
        if(data.SmallNotes.length>0){
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                    getPublicSmallNotes(tdCol,data.SmallNotes);
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }

            tableHeadRow = cTag('tr');
                tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                tdCol.appendChild(cTag('br'));
                    const pTag = cTag('p');
                    pTag.innerHTML = data.invoice_message;
                tdCol.appendChild(pTag);
            tableHeadRow.appendChild(tdCol);
        smallOrderTable.appendChild(tableHeadRow);

        if(data.marketing_data !== ''){
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
                    tdCol.innerHTML = data.marketing_data;
                tableHeadRow.appendChild(tdCol);
            smallOrderTable.appendChild(tableHeadRow);
        }
        Dashboard.appendChild(smallOrderTable);
    }
}

async function AJ_Orders_prints_pick_MoreInfo(){
    const jsonData = {
        'pos_id':segment4,
        'amount_due':window.opener.document.getElementById('amount_due').value
    };
    const url = '/'+segment1+`/AJ_prints_pick_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let address,strong, tableHeadRow, tdCol, tdCol1, pickOrderTable, pTag, tableHeadRow1, thCol;
        const head = document.head;                
            const title = cTag('title');
            if(data.title !=''){
                title.innerHTML = data.title;
            }
            else{
                title.innerHTML = `s${data.invoice_no}`;
            }		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@page {size:portrait;}
                body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; min-width:100%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .pbottom10{padding-bottom:10px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
                ${barcodeCss}`
            );
        head.appendChild(style);

        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const pickOrderTable1 = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                        const headerTitle = cTag('h2');
                        headerTitle.innerHTML = data.title;
                    tdCol.appendChild(headerTitle);
                tableHeadRow.appendChild(tdCol);
            pickOrderTable1.appendChild(tableHeadRow);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');
                        pickOrderTable = cTag('table',{ 'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });

                        let barcodeStr = document.createDocumentFragment();
                        let invoiceNoStr = document.createDocumentFragment();
                        let invoicePreStr;
                        if(data.fromPage ==='Orders'){
                            const strong = cTag('strong');
                            strong.innerHTML = `${Translate('Order No.')}:o${data.invoice_no}`
                            invoiceNoStr.appendChild(strong);
                            invoicePreStr = 'o';
                        }
                        else{
                            const strong = cTag('strong');
                            strong.innerHTML = `${Translate('Sale Invoice #: s')}${data.invoice_no}`
                            invoiceNoStr.appendChild(strong);
                            invoicePreStr = 's';
                        }
                        if(data.barcode===1){
                                let barcode = cTag('span',{class:'barcode'});
                                barcode.innerHTML = encodeToCode128(String(invoicePreStr+data.invoice_no));
                            barcodeStr.appendChild(barcode);
                        }
                        let salesPerson='';
                        if(data.salesPerson!==''){
                            salesPerson = cTag('p');
                            salesPerson.innerHTML = Translate('Sales Person')+': '+data.salesPerson;
                        }
                        if(data.logo_placement==='Center'){
                            if(data.companylogo !==''){
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`2`,'align':`center`, 'style': "padding-bottom: 10px; padding-top: 10px;" });
                                        tdCol.appendChild(creatCompanylogo(data.companylogo,data.logo_size));
                                    tableHeadRow.appendChild(tdCol);
                                pickOrderTable.appendChild(tableHeadRow);
                            }
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`2`,'align':`center` });
                                    tdCol.innerHTML = data.company_info;
                                tableHeadRow.appendChild(tdCol);
                            pickOrderTable.appendChild(tableHeadRow);
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`left`,'width':`50%` });
                                        address = cTag('address',{ 'class':`mbottom0` });
                                            let billSpan = cTag('span');
                                            billSpan.innerHTML = Translate('Bill To')+':';
                                        address.appendChild(billSpan);
                                        address.append(' ');
                                            strong = cTag('strong');
                                            data.customerName.forEach((name,indx)=>indx>0?strong.append(cTag('br'),name):strong.append(name));                                            address.appendChild(strong);
                                        if(data.customerAddress.length){
                                            data.customerAddress.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.contactNo.length){
                                            data.contactNo.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customerEmail.length){
                                            data.customerEmail.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customData){
                                            for (const key in data.customData) {
                                                address.append(cTag('br'),`${key}: ${data.customData[key]}`);
                                            }
                                        }
                                    tdCol.appendChild(address);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                    tdCol.append(invoiceNoStr, salesPerson, barcodeStr);
                                        pTag = cTag('p');
                                        pTag.innerHTML = DBDateToViewDate(data.invoiceDate);
                                    tdCol.appendChild(pTag);
                                tableHeadRow.appendChild(tdCol);
                            pickOrderTable.appendChild(tableHeadRow);
                        }
                        else{
                                tableHeadRow = cTag('tr');
                                if(data.companylogo !==''){
                                        tdCol = cTag('td',{ 'width':`150`,'valign':`top`,'class':`pright15` });
                                        tdCol.appendChild(creatCompanylogo(data.companylogo,data.logo_size));
                                    tableHeadRow.appendChild(tdCol);
                                }
                                    tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                                    tdCol.innerHTML = data.company_info;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'width':`35%`,'align':`right`,'rowspan':`2` });
                                        address = cTag('address',{ 'class':`mbottom0` });
                                            let billToSpan = cTag('span');
                                            billToSpan.innerHTML = Translate('Bill To')+':';
                                        address.appendChild(billToSpan);
                                        address.append(' ');
                                            strong = cTag('strong');
                                            data.customerName.forEach((name,indx)=>indx>0?strong.append(cTag('br'),name):strong.append(name));
                                        address.appendChild(strong);
                                        if(data.customerAddress.length){
                                            data.customerAddress.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.contactNo.length){
                                            data.contactNo.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customerEmail.length){
                                            data.customerEmail.forEach(info=>{
                                                address.append(cTag('br'),info);
                                            })
                                        } 
                                        if(data.customData){
                                            for (const key in data.customData) {
                                                address.append(cTag('br'),`${key}: ${data.customData[key]}`);
                                            }
                                        }
                                    tdCol.appendChild(address);
                                    tdCol.append(invoiceNoStr, salesPerson, barcodeStr);
                                        pTag = cTag('p');
                                        pTag.innerHTML = DBDateToViewDate(data.invoiceDate);
                                    tdCol.appendChild(pTag);
                                tableHeadRow.appendChild(tdCol);
                            pickOrderTable.appendChild(tableHeadRow);
                        }
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':data.colSpan,'align':`left`, 'style': "padding-bottom: 10px;" });
                                tdCol.innerHTML = data.invoice_message_above;
                            tableHeadRow.appendChild(tdCol);
                        pickOrderTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(pickOrderTable);
                tableHeadRow1.appendChild(tdCol1);
            pickOrderTable1.appendChild(tableHeadRow1);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');
                        pickOrderTable = cTag('table',{ 'class':`border`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`5%`,'align':`right` });
                                thCol.innerHTML = '#';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'align':`left` });
                                thCol.innerHTML = Translate('Description');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = Translate('Time/Qty');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = Translate('Shipping Qty');
                            tableHeadRow.appendChild(thCol);
                        pickOrderTable.appendChild(tableHeadRow);

                        if(data.cartData){
                            if(data.cartData.length>0){
                                data.cartData.forEach((item,indx)=>{
                                        tableHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = indx+1;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`left` });
                                            tdCol.innerHTML = item.description;                                              
                                            if(item.add_description !=''){
                                                let addDesHTML = cTag('div', {class:'flex'});
                                                addDesHTML.innerHTML = item.add_description;
                                                tdCol.appendChild(addDesHTML);
                                            }
                                            generateImeiInfo(tdCol, item.newimei_info);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = item.qty;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'align':`right` });
                                            tdCol.innerHTML = item.shipping_qty;
                                        tableHeadRow.appendChild(tdCol);
                                    pickOrderTable.appendChild(tableHeadRow);
                                })
                            }
                            else{
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`4` });
                                        tdCol.innerHTML = Translate('There is no data found');
                                    tableHeadRow.appendChild(tdCol);
                                pickOrderTable.appendChild(tableHeadRow);
                            }
                        }
                        else{
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`4` });
                                    tdCol.innerHTML = Translate('There is no data found');
                                tableHeadRow.appendChild(tdCol);
                            pickOrderTable.appendChild(tableHeadRow);
                        }
                    tdCol1.appendChild(pickOrderTable);
                tableHeadRow1.appendChild(tdCol1);
            pickOrderTable1.appendChild(tableHeadRow1);
            if(data.getNotes.length>0){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td');
                        getPublicNotes(tdCol,data.getNotes);
                    tableHeadRow.appendChild(tdCol);
                pickOrderTable1.appendChild(tableHeadRow);
            }
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.innerHTML = data.invoice_message;
                tableHeadRow.appendChild(tdCol);
            pickOrderTable1.appendChild(tableHeadRow);
        Dashboard.appendChild(pickOrderTable1);
    }
}

async function Inventory_Transfer_prints(){
    await Purchase_orders_prints();
}

async function Purchase_orders_prints(){ 
    if(segment3 !== 'barcode'){
        await AJ_Purchase_orders_prints_large_MoreInfo();
    }
    else{
        await getAndPrintLabel();
    }
}

async function AJ_Purchase_orders_prints_large_MoreInfo(){
    const jsonData = {
        'po_number':segment4,
    };
    const url = '/'+segment1+`/AJ_prints_large_MoreInfo`;
    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let strong, purchaseOrderTable, tableHeadRow, tableHeadRow1, tdCol, tdCol1, pTag, thCol;
        const head = document.head;
            const title = cTag('title');
            if(data.title !=''){
                title.innerHTML = data.title;
            }
            else{
                title.innerHTML = `p${data.po_number}`;
            }
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@page {size:portrait;}
                body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; min-width:100%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
                .bgblack{background: #2f3949;color: #c6d2e5;}`
            );
        head.appendChild(style);

        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const purchaseOrderTable1 = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
            purchaseOrderTable1.appendChild(cTag('tr'));
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                        const topHeader = cTag('h2');
                        topHeader.innerHTML = data.topTitle;
                    tdCol.appendChild(topHeader);
                tableHeadRow.appendChild(tdCol);
            purchaseOrderTable1.appendChild(tableHeadRow);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');                                      
                        purchaseOrderTable = cTag('table',{ 'cellpadding':`0`,'cellspacing':`0`,'width':`100%` });
                            tableHeadRow = cTag('tr');
                            if(data.companylogo !=='') tableHeadRow.appendChild(creatCompanylogo(data.companylogo,data.logo_size));
                                tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                                tdCol.innerHTML = data.company_info;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'width':`35%`,'align':`right`,'rowspan':`2` });
                                    const address = cTag('address',{ 'class':`mbottom0` });
                                        strong = cTag('strong');
                                        strong.innerHTML = data.suplierLabel;
                                    address.appendChild(strong);
                                        pTag = cTag('p');
                                        data.supplierName.forEach((name,indx)=>indx>0?pTag.append(cTag('br'),name):pTag.append(name));
                                    address.appendChild(pTag);
                                tdCol.appendChild(address);
                                    const poNoHeader = cTag('h4');
                                    poNoHeader.innerHTML = `${data.PO_NumberLabel} #: p${data.po_number}`;
                                tdCol.appendChild(poNoHeader);
                                    pTag = cTag('p');
                                    pTag.innerHTML = DBDateToViewDate(data.po_datetime, 1)[0];
                                tdCol.appendChild(pTag);
                            tableHeadRow.appendChild(tdCol);
                        purchaseOrderTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(purchaseOrderTable);
                tableHeadRow1.appendChild(tdCol1);
            purchaseOrderTable1.appendChild(tableHeadRow1);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td'); 
                        purchaseOrderTable = cTag('table',{ 'class':`border`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`5%`,'align':`right` });
                                thCol.innerHTML = '#';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'align':`left` });
                                thCol.innerHTML = Translate('Description');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`12%` });
                                thCol.innerHTML = Translate('Ordered Qty');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`16%` });
                                thCol.innerHTML = Translate('Received Qty');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`10%` });
                                thCol.innerHTML = Translate('Unit Price');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`10%` });
                                thCol.innerHTML = Translate('Total');
                            tableHeadRow.appendChild(thCol);
                        purchaseOrderTable.appendChild(tableHeadRow);

                        let grand_total = 0;
                        let grandOrdQty = 0;
                        let grandRecQty = 0;

                        let subTotalStr = Translate('Total');
                        if(data.taxes!==0 || data.shipping!==0) subTotalStr = Translate('Subtotal');
                        
                        if(data.cartData){
                            if(data.cartData.length>0){
                                data.cartData.forEach((item,indx)=>{
                                    grand_total = calculate('add',item.total,grand_total,2);
                                    grandOrdQty = calculate('add',item.ordered_qty,grandOrdQty,2);
                                    grandRecQty = calculate('add',item.received_qty,grandRecQty,2);
                                        tableHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'style':item.bgproperty,'align':`right`,'valign':`top` });
                                            tdCol.innerHTML = indx+1;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style':item.bgproperty,'align':`left`,'valign':`top` });
                                            if(item.item_type==='cellphones'){
                                                    const descriptionWidth = cTag('div',{ 'class':`width100per` });
                                                        const descriptionColumn = cTag('div',{ 'class':`columnSM12`});
                                                        descriptionColumn.innerHTML = item.description;
                                                    descriptionWidth.appendChild(descriptionColumn);
                                                    if(item.item_numberInfo.length>0){
                                                        item.item_numberInfo.forEach(info=>{
                                                                const poReturnColumn = cTag('div',{ 'class':`columnSM12`, 'style': "padding-left: 10px;" });
                                                                poReturnColumn.innerHTML = info.item_number;
                                                                if(info.return_po_items_id>0 && info.po_or_return ===0){
                                                                        const returnSpan = cTag('span',{ 'style':`padding: 5px; margin-left:15px;`,'class':`bgblack` });
                                                                        returnSpan.innerHTML = Translate('Return');
                                                                    poReturnColumn.append(' ',returnSpan);
                                                                }
                                                            descriptionWidth.appendChild(poReturnColumn);
                                                        })
                                                    }
                                                tdCol.appendChild(descriptionWidth);
                                            }
                                            else{
                                                tdCol.innerHTML = item.description;
                                            }

                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style':item.bgproperty,'align':`right`,'valign':`top` });
                                            tdCol.innerHTML = item.ordered_qty;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style':item.bgproperty,'align':`right`,'valign':`top` });
                                            tdCol.innerHTML = item.received_qty;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style':item.bgproperty,'align':`right`,'valign':`top` });
                                            tdCol.innerHTML = addCurrency(item.cost);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'style':item.bgproperty,'align':`right`,'valign':`top` });
                                            tdCol.innerHTML = addCurrency(item.total);
                                        tableHeadRow.appendChild(tdCol);
                                    purchaseOrderTable.appendChild(tableHeadRow);
                                })
                            }
                            else{
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`6` });
                                        tdCol.innerHTML = Translate('There is no data found');
                                    tableHeadRow.appendChild(tdCol);
                                purchaseOrderTable.appendChild(tableHeadRow);
                            }
                        }
                        else{
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'colspan':`6` });
                                    tdCol.innerHTML = Translate('There is no data found');
                                tableHeadRow.appendChild(tdCol);
                            purchaseOrderTable.appendChild(tableHeadRow);
                        }
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'align':`right`,'colspan':`2` });
                                tdCol.innerHTML = '&nbsp';
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right` });
                                    strong = cTag('strong');
                                    strong.innerHTML = grandOrdQty;
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right` });
                                    strong = cTag('strong');
                                    strong.innerHTML = grandRecQty;
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right` });
                                    strong = cTag('strong');
                                    strong.innerHTML = subTotalStr;
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right` });
                                    strong = cTag('strong');
                                    strong.innerHTML = addCurrency(grand_total);
                                tdCol.appendChild(strong);
                            tableHeadRow.appendChild(tdCol);
                        purchaseOrderTable.appendChild(tableHeadRow);

                        if(data.taxes !==0 || data.shipping !==0){
                            let taxesTotal=0, shippingTotal=0;
                            if(data.taxes !==0){
                                if(data.tax_is_percent===0) taxesTotal = data.taxes;
                                else taxesTotal = calculate('mul',grand_total,calculate('mul',data.taxes,0.01,2),2);
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`5` });
                                            strong = cTag('strong');
                                            strong.innerHTML = Translate('Taxes');
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                            strong = cTag('strong');
                                            strong.innerHTML = addCurrency(taxesTotal);
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                purchaseOrderTable.appendChild(tableHeadRow);
                            }
                            if(data.shipping !==0){
                                shippingTotal = data.shipping;
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`5` });
                                            strong = cTag('strong');
                                            strong.innerHTML = Translate('Shipping Cost');
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                            strong = cTag('strong');
                                            strong.innerHTML = addCurrency(shippingTotal);
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                purchaseOrderTable.appendChild(tableHeadRow);
                            }
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'align':`right`,'colspan':`5` });
                                        strong = cTag('strong');
                                        strong.innerHTML = Translate('Grand Total');
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`right` });
                                        strong = cTag('strong');
                                        strong.innerHTML = addCurrency(calculate('add',grand_total,calculate('add',taxesTotal,shippingTotal,2),2));
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                            purchaseOrderTable.appendChild(tableHeadRow);
                        }
                    tdCol1.appendChild(purchaseOrderTable);
                tableHeadRow1.appendChild(tdCol1);
            purchaseOrderTable1.appendChild(tableHeadRow1);
            if(data.getNotes.length>0){
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td');
                        getPublicNotes(tdCol,data.getNotes);
                    tableHeadRow.appendChild(tdCol);
                purchaseOrderTable1.appendChild(tableHeadRow);
            }
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                        pTag = cTag('p');
                        pTag.innerHTML = data.po_message;
                    tdCol.appendChild(pTag);
                tableHeadRow.appendChild(tdCol);
            purchaseOrderTable1.appendChild(tableHeadRow);
        Dashboard.appendChild(purchaseOrderTable1)   
    }
}

async function Accounts_Receivables_prints(){
    if(segment3=='arlists'){
        const jsonData = {
            'scustomer_type':segment4,
            'sorting_type':segment5,
            'keyword_search':segment6,
            'page':segment7,
        };
        const url = '/'+segment1+`/AJ_prints_arlists_MoreInfo`;

        await fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            const head = document.head;
                const title = cTag('title');
                title.innerHTML = Translate('Accounts Receivables Statement');		
            head.appendChild(title);
                const style = cTag('style');
                style.setAttribute('type','text/css');
                style.append(
                    `body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; width:100%; margin:0; padding:0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                    @page {size: auto;}
                    h2{font-size:22px; line-height:30px;padding-bottom:0; font-weight:500;}
                    address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                    .pright15{padding-right:15px;}
                    .pbottom10{padding-bottom:10px;}
                    .mbottom0{ margin-bottom:0px;}
                    table{border-collapse:collapse; width:100%;font-size: 12px; line-height:20px;}
                    .border th{background:#F5F5F6;}
                    .border td, .border th{ border:1px solid #DDDDDD; padding:8px 10px; }`
                );
            head.appendChild(style);

            let tableHeadRow, tdCol, thCol, oneCustCurrent, oneCustPastDue0_30, oneCustPastDue31_60, oneCustPastDue61_90, oneCustPastDue91_plus, oneCustTotal, GrandCurrent, GrandPastDue0_30, GrandPastDue31_60, GrandPastDue61_90, GrandPastDue91_plus, GrandTotal;
            const Dashboard = document.querySelector('#viewPageInfo');
            Dashboard.innerHTML = '';
                const accountReceivableTable = cTag('table',{ 'class':`border`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                tableHeadRow = cTag('tr');
                    thCol = cTag('th',{ 'width':`10%`,'align':`center` });
                    thCol.innerHTML = Translate('Invoice Date');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'align':`center` });
                    thCol.innerHTML = Translate('Invoice Number');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`center` });
                    thCol.innerHTML = Translate('Date Due');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                    thCol.innerHTML = Translate('Total');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                    thCol.innerHTML = Translate('Total Paid');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                    thCol.innerHTML = Translate('Current');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                    thCol.innerHTML = Translate('0-30 Past Due');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                    thCol.innerHTML = Translate('31-60 Past Due');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                    thCol.innerHTML = Translate('61-90 Past Due');
                tableHeadRow.appendChild(thCol);
                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                    thCol.innerHTML = Translate('91+ Past due');
                tableHeadRow.appendChild(thCol);
            accountReceivableTable.appendChild(tableHeadRow);

            GrandCurrent =GrandPastDue0_30 = GrandPastDue31_60 = GrandPastDue61_90 = GrandPastDue91_plus = GrandTotal = 0;

            if(data.tabledata && data.tabledata.length>0){
                data.tabledata.forEach(oneCustomerRow=>{
                    oneCustCurrent = oneCustPastDue0_30 = oneCustPastDue31_60 = oneCustPastDue61_90 = oneCustPastDue91_plus = oneCustTotal = 0;
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`left`, colspan:10 });
                            const customerInfo = cTag('strong');
                            customerInfo.append(oneCustomerRow.name, ', \u2003 Cr. Limit: '+oneCustomerRow.credit_limit, ', \u2003 Cr. Days: '+oneCustomerRow.credit_days, ', \u2003 Total Dues: '+oneCustomerRow.totalDue);
                        tdCol.appendChild(customerInfo);
                    tableHeadRow.appendChild(tdCol);
                    accountReceivableTable.appendChild(tableHeadRow);

                    let customerCartData = oneCustomerRow.customerCartData;
                    if(customerCartData && customerCartData.length>0){
                        customerCartData.forEach(item=>{

                            let total = item.Current+item.PastDue0_30+item.PastDue31_60+item.PastDue61_90+item.PastDue91_plus+item.amountPaid;
                            
                            oneCustCurrent = calculate('add',item.Current,oneCustCurrent,2);
                            oneCustPastDue0_30 = calculate('add',item.PastDue0_30,oneCustPastDue0_30,2);
                            oneCustPastDue31_60 = calculate('add',item.PastDue31_60,oneCustPastDue31_60,2);
                            oneCustPastDue61_90 = calculate('add',item.PastDue61_90,oneCustPastDue61_90,2);
                            oneCustPastDue91_plus = calculate('add',item.PastDue91_plus,oneCustPastDue91_plus,2);
                            oneCustTotal = calculate('add',total,oneCustTotal,2);

                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'nowrap':``,'align':`center`,'data-title':Translate('Invoice Date') });
                                    tdCol.innerHTML = DBDateToViewDate(item.invoiceDate);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'nowrap':``,'align':`center`,'data-title':Translate('Invoice No.') });
                                    tdCol.innerHTML = item.invoice_no;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'nowrap':``,'align':`center`,'data-title':Translate('Due Date') });
                                    tdCol.innerHTML = DBDateToViewDate(item.dueDate);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Total'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(total);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Total Paid'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.amountPaid);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Current Due'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.Current);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Past Due 0-30'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.PastDue0_30);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Past Due 31-60'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.PastDue31_60);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Past Due 61-90'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.PastDue61_90);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Past Due 91+'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.PastDue91_plus);
                                tableHeadRow.appendChild(tdCol);
                            accountReceivableTable.appendChild(tableHeadRow);
                        })
                    }

                    GrandCurrent = calculate('add',oneCustCurrent,GrandCurrent,2);
                    GrandPastDue0_30 = calculate('add',oneCustPastDue0_30,GrandPastDue0_30,2);
                    GrandPastDue31_60 = calculate('add',oneCustPastDue31_60,GrandPastDue31_60,2);
                    GrandPastDue61_90 = calculate('add',oneCustPastDue61_90,GrandPastDue61_90,2);
                    GrandPastDue91_plus = calculate('add',oneCustPastDue91_plus,GrandPastDue91_plus,2);
                    GrandTotal = calculate('add',oneCustTotal,GrandTotal,2);

                    tableHeadRow = cTag('tr');
                        thCol = cTag('th',{ 'align':`right`,'colspan':`3` });
                        thCol.innerHTML = Translate('Customer Total')+' :';
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'data-title':Translate('Total'),'align':`right` });
                        thCol.innerHTML = addCurrency(oneCustTotal);
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'align':`right` });
                        thCol.innerHTML = ' ';
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'data-title':Translate('Current Due'),'align':`right` });
                        thCol.innerHTML = addCurrency(oneCustCurrent);
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'data-title':Translate('Past Due 0-30'),'align':`right` });
                        thCol.innerHTML = addCurrency(oneCustPastDue0_30);
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'data-title':Translate('Past Due 31-60'),'align':`right` });
                        thCol.innerHTML = addCurrency(oneCustPastDue31_60);
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'data-title':Translate('Past Due 61-90'),'align':`right` });
                        thCol.innerHTML = addCurrency(oneCustPastDue61_90);
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'data-title':Translate('Past Due 91+'),'align':`right` });
                        thCol.innerHTML = addCurrency(oneCustPastDue91_plus);
                    tableHeadRow.appendChild(thCol);
                    accountReceivableTable.appendChild(tableHeadRow);
                    
                });
            }
            else{
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'colspan':`10`, 'style': "color: #ae2222;" });
                        tdCol.innerHTML = Translate('No dues meet the criteria given');
                    tableHeadRow.appendChild(tdCol);
                accountReceivableTable.appendChild(tableHeadRow);
            }

            tableHeadRow = cTag('tr');
                thCol = cTag('th',{ 'align':`right`,'colspan':`3` });
                thCol.innerHTML = Translate('Grand Total')+' :';
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'data-title':Translate('Total'),'align':`right` });
                thCol.innerHTML = addCurrency(GrandTotal);
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'align':`right` });
                thCol.innerHTML = ' ';
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'data-title':Translate('Current Due'),'align':`right` });
                thCol.innerHTML = addCurrency(GrandCurrent);
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'data-title':Translate('Past Due 0-30'),'align':`right` });
                thCol.innerHTML = addCurrency(GrandPastDue0_30);
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'data-title':Translate('Past Due 31-60'),'align':`right` });
                thCol.innerHTML = addCurrency(GrandPastDue31_60);
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'data-title':Translate('Past Due 61-90'),'align':`right` });
                thCol.innerHTML = addCurrency(GrandPastDue61_90);
            tableHeadRow.appendChild(thCol);
                thCol = cTag('th',{ 'data-title':Translate('Past Due 91+'),'align':`right` });
                thCol.innerHTML = addCurrency(GrandPastDue91_plus);
            tableHeadRow.appendChild(thCol);
            accountReceivableTable.appendChild(tableHeadRow);
            Dashboard.appendChild(accountReceivableTable);
        }
    }
    else{
        const jsonData = {
            'customers_id':segment3,
        };
        const url = '/'+segment1+`/AJ_prints_MoreInfo`;

        await fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            const tt = Translate('Accounts Receivables Statement for')+' '+data.name;
            const head = document.head;
                const title = cTag('title');
                title.innerHTML = tt;		
            head.appendChild(title);
                const style = cTag('style');
                style.setAttribute('type','text/css');
                style.append(
                    `body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; width:100%; margin:0; padding:0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                    @page {size: auto;}
                    h2{font-size:22px; line-height:30px;padding-bottom:0; font-weight:500;}
                    address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                    .pright15{padding-right:15px;}
                    .pbottom10{padding-bottom:10px;}
                    .mbottom0{ margin-bottom:0px;}
                    table{border-collapse:collapse; width:100%;font-size: 12px; line-height:20px;}
                    .border th{background:#F5F5F6;}
                    .border td, .border th{ border:1px solid #DDDDDD; padding:8px 10px; }`
                );
            head.appendChild(style);

            let tableHeadRow, tdCol, thCol;
            const Dashboard = document.querySelector('#viewPageInfo');
            Dashboard.innerHTML = '';
                const accountReceivableTable1 = cTag('table',{ 'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td',{ 'align':`center` });
                            const header2 = cTag('h2');
                            header2.innerHTML = tt;
                        tdCol.appendChild(header2);
                            const pTag = cTag('p');
                            pTag.append(data.name);
                            pTag.appendChild(cTag('br'));
                            pTag.append(data.address);
                        tdCol.appendChild(pTag);
                    tableHeadRow.appendChild(tdCol);
                accountReceivableTable1.appendChild(tableHeadRow);
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td');
                        tdCol.innerHTML = ' ';
                    tableHeadRow.appendChild(tdCol);
                accountReceivableTable1.appendChild(tableHeadRow);
                    const tableHeadRow1 = cTag('tr');
                        const tdCol1 = cTag('td');
                            const accountReceivableTable = cTag('table',{ 'class':`border`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                                tableHeadRow = cTag('tr');
                                    thCol = cTag('th',{ 'width':`10%`,'align':`center` });
                                    thCol.innerHTML = Translate('Invoice Date');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`center` });
                                    thCol.innerHTML = Translate('Invoice Number');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`center` });
                                    thCol.innerHTML = Translate('Date Due');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                                    thCol.innerHTML = Translate('Total');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                                    thCol.innerHTML = Translate('Total Paid');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                                    thCol.innerHTML = Translate('Current');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                                    thCol.innerHTML = Translate('0-30 Past Due');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                                    thCol.innerHTML = Translate('31-60 Past Due');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                                    thCol.innerHTML = Translate('61-90 Past Due');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'width':`10%`,'align':`right` });
                                    thCol.innerHTML = Translate('91+ Past due');
                                tableHeadRow.appendChild(thCol);
                            accountReceivableTable.appendChild(tableHeadRow);

                            let GrandCurrent = 0;
                            let GrandPastDue0_30 = 0;
                            let GrandPastDue31_60 = 0;
                            let GrandPastDue61_90 = 0;
                            let GrandPastDue91_plus = 0;
                            let GrandTotal = 0;

                            if(data.tabledata && data.tabledata.length>0){
                                data.tabledata.forEach(item=>{
                                    let total = item.Current+item.PastDue0_30+item.PastDue31_60+item.PastDue61_90+item.PastDue91_plus+item.amountPaid;
                                    GrandCurrent = calculate('add',item.Current,GrandCurrent,2);
                                    GrandPastDue0_30 = calculate('add',item.PastDue0_30,GrandPastDue0_30,2);
                                    GrandPastDue31_60 = calculate('add',item.PastDue31_60,GrandPastDue31_60,2);
                                    GrandPastDue61_90 = calculate('add',item.PastDue61_90,GrandPastDue61_90,2);
                                    GrandPastDue91_plus = calculate('add',item.PastDue91_plus,GrandPastDue91_plus,2);
                                    GrandTotal = calculate('add',total,GrandTotal,2);
                                        tableHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'nowrap':``,'align':`center`,'data-title':Translate('Invoice Date') });
                                            tdCol.innerHTML = DBDateToViewDate(item.invoiceDate);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'nowrap':``,'align':`center`,'data-title':Translate('Invoice No.') });
                                            tdCol.innerHTML = item.invoice_no;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'nowrap':``,'align':`center`,'data-title':Translate('Due Date') });
                                            tdCol.innerHTML = DBDateToViewDate(item.dueDate);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Total'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(total);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Total Paid'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.amountPaid);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Current Due'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.Current);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Past Due 0-30'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.PastDue0_30);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Past Due 31-60'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.PastDue31_60);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Past Due 61-90'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.PastDue61_90);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Past Due 91+'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(item.PastDue91_plus);
                                        tableHeadRow.appendChild(tdCol);
                                    accountReceivableTable.appendChild(tableHeadRow);
                                })
                            }
                            else{
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'colspan':`10`, 'style': "color: #ae2222;" });
                                        tdCol.innerHTML = Translate('No dues meet the criteria given');
                                    tableHeadRow.appendChild(tdCol);
                                accountReceivableTable.appendChild(tableHeadRow);
                            }
                                tableHeadRow = cTag('tr');
                                    thCol = cTag('th',{ 'align':`right`,'colspan':`3` });
                                    thCol.innerHTML = Translate('Grand Total')+' :';
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'data-title':Translate('Total'),'align':`right` });
                                    thCol.innerHTML = addCurrency(GrandTotal);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`right` });
                                    thCol.innerHTML = ' ';
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'data-title':Translate('Current Due'),'align':`right` });
                                    thCol.innerHTML = addCurrency(GrandCurrent);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'data-title':Translate('Past Due 0-30'),'align':`right` });
                                    thCol.innerHTML = addCurrency(GrandPastDue0_30);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'data-title':Translate('Past Due 31-60'),'align':`right` });
                                    thCol.innerHTML = addCurrency(GrandPastDue31_60);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'data-title':Translate('Past Due 61-90'),'align':`right` });
                                    thCol.innerHTML = addCurrency(GrandPastDue61_90);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'data-title':Translate('Past Due 91+'),'align':`right` });
                                    thCol.innerHTML = addCurrency(GrandPastDue91_plus);
                                tableHeadRow.appendChild(thCol);
                            accountReceivableTable.appendChild(tableHeadRow);
                        tdCol1.appendChild(accountReceivableTable);
                    tableHeadRow1.appendChild(tdCol1);
                accountReceivableTable1.appendChild(tableHeadRow1);
                    tableHeadRow = cTag('tr');
                        tdCol = cTag('td');
                        getPublicNotes(tdCol,data.allNotes);
                    tableHeadRow.appendChild(tdCol);
                accountReceivableTable1.appendChild(tableHeadRow);
            Dashboard.appendChild(accountReceivableTable1);
        }
    }
}

//---------stock_take
async function Stock_Take_prints(){
    const jsonData = {
        'stock_take_id':segment4,
        'sview2_type':segment5,
        'keyword_search':segment6,
    };
    const url = '/'+segment1+`/AJ_prints_large_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let stockTable, tableHeadRow, tableHeadRow1, tdCol, tdCol1, pTag, thCol;
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = data.title;		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@page {size:portrait;}
                body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; min-width:100%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}
                .bgblack{background: #2f3949;color: #c6d2e5;}`
            );
        head.appendChild(style);

        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const stockTable1 = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
            stockTable1.appendChild(cTag('tr'));
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                        const stockTakeHeader = cTag('h2');
                        stockTakeHeader.innerHTML = Translate('Stock Take Information');
                    tdCol.appendChild(stockTakeHeader);
                tableHeadRow.appendChild(tdCol);
            stockTable1.appendChild(tableHeadRow);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');                    
                        stockTable = cTag('table',{ 'cellpadding':`0`,'cellspacing':`0`,'width':`100%` });
                            tableHeadRow = cTag('tr');
                            if(data.onePicture !== ""){
                                    tdCol = cTag('td',{ 'width':`150`,'valign':`top`,'class':`pright15` });
                                    tdCol.appendChild(cTag('img',{ 'style':`max-height:100px;max-width:135px;float:left;`,'src':data.onePicture,'title':Translate('Logo') }));
                                tableHeadRow.append(tdCol);
                            }
                                tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                                tdCol.innerHTML = data.company_info;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'width':`35%`,'align':`right`,'rowspan':`2` });
                                    const address = cTag('address',{ 'class':`mbottom0` });
                                    address.append(Translate('Reference')+' : ');
                                        const strong = cTag('strong');
                                        strong.innerHTML = data.reference;
                                    address.appendChild(strong);
                                        pTag = cTag('p');
                                        pTag.innerHTML = `${Translate('Manufacturer')} : ${data.manufacture||'All Manufacturers'}`;
                                    address.appendChild(pTag);
                                tdCol.appendChild(address);
                                    pTag = cTag('p');
                                    pTag.innerHTML = `${Translate('Category')} : ${data.categoryname||'All Categories'}`;
                                tdCol.appendChild(pTag);
                                    pTag = cTag('p');
                                    pTag.innerHTML = `${Translate('Date Completed')} : ${DBDateToViewDate(data.date_completed)}`;
                                tdCol.appendChild(pTag);
                            tableHeadRow.appendChild(tdCol);
                        stockTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(stockTable);
                tableHeadRow1.appendChild(tdCol1);
            stockTable1.appendChild(tableHeadRow1);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td');                    
                        stockTable = cTag('table',{ 'class':`border`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`3%`,'class':`text-right` });
                                thCol.innerHTML = '#';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`10%` });
                                thCol.innerHTML = Translate('Manufacturer');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`10%` });
                                thCol.innerHTML = Translate('Category');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th');
                                thCol.innerHTML = Translate('Product Name');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = Translate('SKU/Barcode');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`120`,'class':`text-right` });
                                thCol.innerHTML = Translate('Current');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`170`,'class':`text-right` });
                                thCol.innerHTML = Translate('Counted');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`10%` });
                                thCol.innerHTML = Translate('Difference');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`10%` });
                                thCol.innerHTML = Translate('Total Cost');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`16%` });
                                thCol.innerHTML = Translate('Totals Price');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`20%` });
                                thCol.innerHTML = Translate('Note');
                            tableHeadRow.appendChild(thCol);
                        stockTable.appendChild(tableHeadRow);
                        
                        let GTInvCur = 0;
                        let GTInvCou = 0;
                        let GTCost = 0;
                        let GTPrice = 0;

                        if(data.cartData && data.cartData.length>0){
                            data.cartData.forEach((item,indx)=>{
                                let inventory_current = item.inventory_current;
                                let inventory_count = item.inventory_count;	
                                let differVal = inventory_current;
                                GTInvCur += inventory_current;
                                if(inventory_count===-1){inventory_count = '';}
                                else{
                                    differVal = inventory_current-inventory_count;
                                    GTInvCou += inventory_count;
                                }
                                const rowTotalCost = calculate('mul',differVal,item.ave_cost,2);
                                const rowTotalPrice = calculate('mul',differVal,item.regular_price,2);
                                GTCost = calculate('add',rowTotalCost,GTCost,2);
                                GTPrice = calculate('add',rowTotalPrice,GTPrice,2);
                                
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = indx+1;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`left` });
                                        tdCol.innerHTML = item.manufacture;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`left` });
                                        tdCol.innerHTML = item.categoryName;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`left` });
                                        tdCol.innerHTML = item.productName;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`center` });
                                        tdCol.innerHTML = item.sku;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = inventory_current;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = inventory_count;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = differVal;
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = addCurrency(rowTotalCost);
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                        tdCol.innerHTML = addCurrency(rowTotalPrice);
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`left` });
                                        tdCol.innerHTML = item.note;
                                    tableHeadRow.appendChild(tdCol);
                                stockTable.appendChild(tableHeadRow);
                            })
                                tableHeadRow = cTag('tr');
                                    thCol = cTag('th',{ 'colspan':`5`,'align':`right` });
                                    thCol.innerHTML = Translate('Grand Total');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`right` });
                                    thCol.innerHTML = GTInvCur;
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`right` });
                                    thCol.innerHTML = GTInvCou;
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`right` });
                                    thCol.innerHTML = GTInvCur-GTInvCou;
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`right` });
                                    thCol.innerHTML = addCurrency(GTCost);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`right` });
                                    thCol.innerHTML = addCurrency(GTPrice);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`left` });
                                    thCol.innerHTML = data.note;
                                tableHeadRow.appendChild(thCol);
                            stockTable.appendChild(tableHeadRow);  
                        }                       
                    tdCol1.appendChild(stockTable);
                tableHeadRow1.appendChild(tdCol1);
            stockTable1.appendChild(tableHeadRow1);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td');
                    getPublicNotes(tdCol,data.noteData);
                tableHeadRow.appendChild(tdCol);
            stockTable1.appendChild(tableHeadRow);
        Dashboard.appendChild(stockTable1);
    }
}

//----------End_of_Day
async function End_of_Day_prints(){
    if(segment3==='large') await AJ_End_of_Day_prints_large_MoreInfo();
    else if(segment3==='small') await AJ_End_of_Day_prints_small_MoreInfo();
    else if(segment3==='eodlist') await AJ_End_of_Day_prints_eodlist_MoreInfo();
}

async function AJ_End_of_Day_prints_large_MoreInfo(){
    const jsonData = {
        'eod_date':segment4,
        'printType':segment3,
        'drawer':segment5,
    };
    const url = '/'+segment1+`/AJ_prints_large_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = data.title;		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@page {size:portrait;}
                body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; min-width:100%; margin:0; padding:15px 0.5% 0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .pbottom10{padding-bottom:10px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:4px 10px; vertical-align: top;}`
            );
        head.appendChild(style);

        let strong, endDayTable, tableHeadRow, tableHeadRow1, tdCol, tdCol1, thCol;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const endDayTable1 = cTag('table',{ 'align':`center`,'width':`99.75%`,'cellpadding':`0`,'cellspacing':`0` });
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td',{ 'align':`center`,'colspan':`4` });
                         endDayTable = cTag('table',{ 'width':`100%`,'cellspacing':`0`,'cellpadding':`0` });
                             tableHeadRow = cTag('tr');
                                 tdCol = cTag('td',{ 'colspan':`2`,'align':`center` });
                                    const headerTitle = cTag('h2');
                                    headerTitle.innerHTML = data.title;
                                tdCol.appendChild(headerTitle);
                            tableHeadRow.appendChild(tdCol);
                        endDayTable.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'width':`50%` });
                                    let companyNameHeader = cTag('h4');
                                    companyNameHeader.innerHTML = data.company_name;
                                tdCol.appendChild(companyNameHeader);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right` });
                                    let printedHeader = cTag('h4');
                                    printedHeader.innerHTML = `${Translate('Date printed')}: ${DBDateToViewDate(data.todayDate)}`;
                                tdCol.appendChild(printedHeader);
                            tableHeadRow.appendChild(tdCol);
                        endDayTable.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':`2`, 'style': `padding-top: 10px;` });
                                let drawerStr = '';
                                if(getCookie('drawer') !=='') drawerStr = ` ${Translate('Drawer')}: ${getCookie('drawer')}`; 
                                tdCol.innerHTML = `${Translate('Date')}: ${DBDateToViewDate(data.eod_date, 0, 1)}.${drawerStr}`;
                            tableHeadRow.appendChild(tdCol);
                        endDayTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(endDayTable);
                tableHeadRow1.appendChild(tdCol1);
            endDayTable1.appendChild(tableHeadRow1);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${Translate('Cash Counted')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                    tdCol.innerHTML = addCurrency(data.counted_cash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            endDayTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${Translate('Starting Balance')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.starting_cash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            endDayTable1.appendChild(tableHeadRow);
            if(data.petty_cash !==0){
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = `${Translate('Petty Cash')} : `;
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td');
                        tdCol.innerHTML = ' ';
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(data.petty_cash);
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td');
                        tdCol.innerHTML = ' ';
                    tableHeadRow.appendChild(tdCol);
                endDayTable1.appendChild(tableHeadRow);
            }
            const countedCash = calculate('sub',calculate('sub',data.counted_cash,data.starting_cash,2),data.petty_cash,2);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${Translate('Calculated Cash')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.calculatedCash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                    tdCol.innerHTML = addCurrency(countedCash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'data-title':Translate('Difference'),'align':`right` });
                    tdCol.innerHTML = addCurrency(calculate('sub',countedCash,data.calculatedCash,2));
                tableHeadRow.appendChild(tdCol);
            endDayTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            endDayTable1.appendChild(tableHeadRow);

            let str = '';
            let changestr = '';
            let total_counted = countedCash;
            let total_calculated = data.calculatedCash;
            if(data.posPaymentData && data.posPaymentData.length>0){
                str = document.createDocumentFragment();
                data.posPaymentData.forEach(item=>{
                        tableHeadRow = cTag('tr',{ 'class':`border` });
                            tdCol = cTag('td',{ 'data-title':Translate('Payment Type'),'align':`left` });
                            tdCol.innerHTML = item.payment_method;
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'data-title':Translate('Calculated'),'align':`right` });
                            tdCol.innerHTML = addCurrency(item.calculated);
                            total_calculated = calculate('add',item.calculated,total_calculated,2);
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                            tdCol.innerHTML = addCurrency(item.counted);
                            total_counted = calculate('add',item.counted,total_counted,2);
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'data-title':Translate('Difference'),'align':`right` });
                            tdCol.innerHTML = addCurrency(calculate('sub',item.counted,item.calculated,2));
                        tableHeadRow.appendChild(tdCol);
                    str.appendChild(tableHeadRow);
                })
            }
            else{
                str = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'colspan':`4`});
                    tdCol.innerHTML = '';
                str.appendChild(tdCol);
            }

            if(str !=='' || changestr !==''){
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`left` });
                        thCol.innerHTML = Translate('Payment Type');
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`right`,'width':`20%` });
                        thCol.innerHTML = Translate('Calculated');
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`right`,'width':`20%` });
                        thCol.innerHTML = Translate('Counted');
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`right`,'width':`20%` });
                        thCol.innerHTML = Translate('Difference');
                    tableHeadRow.appendChild(thCol);
                endDayTable1.appendChild(tableHeadRow);
                endDayTable1.append(changestr,str);
            }

                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = `${Translate('Total')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = addCurrency(total_calculated);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = addCurrency(total_counted);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = addCurrency(calculate('sub',total_counted,total_calculated,2));
                tableHeadRow.appendChild(tdCol);
            endDayTable1.appendChild(tableHeadRow);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td',{ 'align':`center`,'colspan':`4` });
                    
                    str = '';
                    let total_petty_cash = 0;
                    if(data.petty_cashData && data.petty_cashData.length>0){
                        str = document.createDocumentFragment();
                        data.petty_cashData.forEach(item=>{
                                tableHeadRow = cTag('tr',{ 'class':`border` });
                                    tdCol = cTag('td',{ 'data-title':Translate('Reason'),'align':`left` });
                                    tdCol.innerHTML = item.reason;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Add/Sub'),'align':`left` });
                                    tdCol.innerHTML = item.type;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Amount'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.amount);
                                    total_petty_cash = calculate('add',item.amount,total_petty_cash,2);
                                tableHeadRow.appendChild(tdCol);
                            str.appendChild(tableHeadRow);
                        })
                    }

                    if(str !==''){
                            endDayTable = cTag('table',{ 'align':`center`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                                const thead = cTag('thead');
                                    const blankRow = cTag('tr');
                                        const blankTd = cTag('td',{'colspan':'4'});
                                        blankTd.innerHTML = '&nbsp;'
                                    blankRow.appendChild(blankTd);
                                thead.appendChild(blankRow);
                                    tableHeadRow = cTag('tr');
                                        thCol = cTag('th',{ 'colspan':`3`,'align':`left` });
                                        thCol.innerHTML = Translate('Petty Cash Information');
                                    tableHeadRow.appendChild(thCol);
                                thead.appendChild(tableHeadRow);
                                    tableHeadRow = cTag('tr',{ 'class':`border` });
                                        thCol = cTag('th',{ 'style':` text-align: left; background-color:#e0dfdf` });
                                        thCol.innerHTML = Translate('Reason');
                                    tableHeadRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'width':`20%`, 'style':`text-align: left; background-color:#e0dfdf` });
                                        thCol.innerHTML = Translate('Add/Sub');
                                    tableHeadRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'style': "text-align: right;",'width':`20%`,'style':`background-color:#e0dfdf` });
                                        thCol.innerHTML = Translate('Amount');
                                    tableHeadRow.appendChild(thCol);
                                thead.appendChild(tableHeadRow);
                            endDayTable.appendChild(thead);
                                const tbody = cTag('tbody');
                                tbody.append(str);
                                    tableHeadRow = cTag('tr',{ 'class':`border` });
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`2`,'style':`background-color:#e0dfdf` });
                                            strong = cTag('strong');
                                            strong.innerHTML = `${Translate('Total')} : `;
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right`,'style':`background-color:#e0dfdf` });
                                            strong = cTag('strong');
                                            strong.innerHTML = addCurrency(total_petty_cash);
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                tbody.appendChild(tableHeadRow);
                            endDayTable.appendChild(tbody);
                        tdCol1.appendChild(endDayTable);
                    }
                tableHeadRow1.appendChild(tdCol1);
            endDayTable1.appendChild(tableHeadRow1);
            if(data.comments !==''){
                    const blankRow = cTag('tr');
                        const blankTd = cTag('td',{'colspan':'4'});
                        blankTd.innerHTML = '&nbsp;'
                    blankRow.appendChild(blankTd);
                endDayTable.appendChild(blankRow);
                    tableHeadRow1 = cTag('tr');
                        tdCol1 = cTag('td',{ 'align':`center`,'colspan':`4` });                                                   
                            endDayTable = cTag('table',{ 'align':`center`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                                tableHeadRow = cTag('tr',{ 'class':`border` });
                                    tdCol = cTag('td',{ 'align':`right`,'width':`10%`,'style':`background-color:#e0dfdf` });
                                        strong = cTag('strong');
                                        strong.innerHTML = `${Translate('Comments')}: `;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`left` });
                                    tdCol.innerHTML = data.comments;
                                tableHeadRow.appendChild(tdCol);
                            endDayTable.appendChild(tableHeadRow);
                        tdCol1.appendChild(endDayTable);
                    tableHeadRow1.appendChild(tdCol1);
                endDayTable1.appendChild(tableHeadRow1);
            }
        Dashboard.appendChild(endDayTable1);       
    }
}

async function AJ_End_of_Day_prints_small_MoreInfo(){
    const jsonData = {
        'eod_date':segment4,
        'printType':segment3,
        'drawer':segment5,
    };
    const url = '/'+segment1+`/AJ_prints_small_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = data.title;		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `*{ font-family:Arial, sans-serif, Helvetica;font-size: 11px;}
                body{box-sizing: border-box; width:100%; margin:0; padding:0;background:#fff;color:#000;}
                @page {size:portrait;margin-top: 0;margin-bottom: 0;'.$addCss.'}
                table{border-collapse:collapse;}
                tr.border td, tr.border th{ border:1px solid #CCC; padding:2px; vertical-align: top;}`
            );
        head.appendChild(style);

        let strong, endDaySmallTable, tableHeadRow, tableHeadRow1, tdCol, tdCol1, thCol;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const endDaySmallTable1 = cTag('table',{ 'align':`center`,'width':`99.75%`,'cellpadding':`0`,'cellspacing':`0` });
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td',{ 'align':`center`,'colspan':`4` });                        
                        endDaySmallTable = cTag('table',{ 'width':`100%`,'cellspacing':`0`,'cellpadding':`0` });
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':`2`,'align':`center`,'style':"font-size:20px;" });
                                tdCol.innerHTML = data.title;
                            tableHeadRow.appendChild(tdCol);
                        endDaySmallTable.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'align':"left", 'style':"font-size:18px;" });
                                tdCol.innerHTML = data.company_name;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right`, 'nowrap':'' });
                                    let datePrintSpan = cTag('span');
                                    datePrintSpan.innerHTML = `${Translate('Date printed')}: ${DBDateToViewDate(data.todayDate)}`;
                                tdCol.appendChild(datePrintSpan);
                            tableHeadRow.appendChild(tdCol);
                        endDaySmallTable.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':`2`, 'style':`padding-top: 10px;` });
                                let drawerStr = '';
                                if(getCookie('drawer') !=='') drawerStr = ` ${Translate('Drawer')}: ${getCookie('drawer')}`; 
                                tdCol.innerHTML = `${Translate('Date')}: ${DBDateToViewDate(data.eod_date, 0, 1)}.${drawerStr}`;
                            tableHeadRow.appendChild(tdCol);
                        endDaySmallTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(endDaySmallTable);
                tableHeadRow1.appendChild(tdCol1);
            endDaySmallTable1.appendChild(tableHeadRow1);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${Translate('Cash Counted')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                    tdCol.innerHTML = addCurrency(data.counted_cash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            endDaySmallTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${Translate('Starting Balance')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.starting_cash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td');
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            endDaySmallTable1.appendChild(tableHeadRow);
            if(data.petty_cash !==0){
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = `${Translate('Petty Cash')} : `;
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td');
                        tdCol.innerHTML = ' ';
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td',{ 'align':`right` });
                        tdCol.innerHTML = addCurrency(data.petty_cash);
                    tableHeadRow.appendChild(tdCol);
                        tdCol = cTag('td');
                        tdCol.innerHTML = ' ';
                    tableHeadRow.appendChild(tdCol);
                endDaySmallTable1.appendChild(tableHeadRow);
            }

            const countedCash = calculate('sub',calculate('sub',data.counted_cash,data.starting_cash,2),data.petty_cash,2);
                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = `${Translate('Calculated Cash')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'align':`right` });
                    tdCol.innerHTML = addCurrency(data.calculatedCash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                    tdCol.innerHTML = addCurrency(countedCash);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'data-title':Translate('Difference'),'align':`right` });
                    tdCol.innerHTML = addCurrency(calculate('sub',countedCash,data.calculatedCash,2));
                tableHeadRow.appendChild(tdCol);
            endDaySmallTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`right`,'colspan':`4` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            endDaySmallTable1.appendChild(tableHeadRow);

            let str = '';
            let changestr = '';
            let total_counted = countedCash;
            let total_calculated = data.calculatedCash;
            if(data.posPaymentData && data.posPaymentData.length>0){
                str = document.createDocumentFragment();
                data.posPaymentData.forEach(item=>{
                        tableHeadRow = cTag('tr',{ 'class':`border` });
                            tdCol = cTag('td',{ 'data-title':Translate('Payment Type'),'align':`left` });
                            tdCol.innerHTML = item.payment_method;
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'data-title':Translate('Calculated'),'align':`right` });
                            tdCol.innerHTML = addCurrency(item.calculated);
                            total_calculated = calculate('add',item.calculated,total_calculated,2);
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                            tdCol.innerHTML = addCurrency(item.counted);
                            total_counted = calculate('add',item.counted,total_counted,2);
                        tableHeadRow.appendChild(tdCol);
                            tdCol = cTag('td',{ 'data-title':Translate('Difference'),'align':`right` });
                            tdCol.innerHTML = addCurrency(calculate('sub',item.counted,item.calculated,2));
                        tableHeadRow.appendChild(tdCol);
                    str.appendChild(tableHeadRow);
                })
            }
            else{
                str = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'colspan':`4`});
                    tdCol.innerHTML = '';
                str.appendChild(tdCol);
            }

            if(str !=='' || changestr !==''){
                    tableHeadRow = cTag('tr',{ 'class':`border` });
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`left` });
                        thCol.innerHTML = Translate('Payment Type');
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`right`,'width':`20%` });
                        thCol.innerHTML = Translate('Calculated');
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`right`,'width':`20%` });
                        thCol.innerHTML = Translate('Counted');
                    tableHeadRow.appendChild(thCol);
                        thCol = cTag('th',{ 'style':`background-color:#e0dfdf`,'align':`right`,'width':`20%` });
                        thCol.innerHTML = Translate('Difference');
                    tableHeadRow.appendChild(thCol);
                endDaySmallTable1.appendChild(tableHeadRow);
                endDaySmallTable1.append(changestr,str);
            }

                tableHeadRow = cTag('tr',{ 'class':`border` });
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = `${Translate('Total')} : `;
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = addCurrency(total_calculated);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = addCurrency(total_counted);
                tableHeadRow.appendChild(tdCol);
                    tdCol = cTag('td',{ 'style':`background-color:#e0dfdf`,'align':`right` });
                    tdCol.innerHTML = addCurrency(calculate('sub',total_counted,total_calculated,2));
                tableHeadRow.appendChild(tdCol);
            endDaySmallTable1.appendChild(tableHeadRow);
                tableHeadRow1 = cTag('tr');
                    tdCol1 = cTag('td',{ 'align':`center`,'colspan':`4` });
                    
                    str = '';
                    let total_petty_cash = 0;
                    if(data.petty_cashData && data.petty_cashData.length>0){
                        str = document.createDocumentFragment();
                        data.petty_cashData.forEach(item=>{
                                tableHeadRow = cTag('tr',{ 'class':`border` });
                                    tdCol = cTag('td',{ 'data-title':Translate('Reason'),'align':`left` });
                                    tdCol.innerHTML = item.reason;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Add/Sub'),'align':`left` });
                                    tdCol.innerHTML = item.type;
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Amount'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item.amount);
                                    total_petty_cash = calculate('add',item.amount,total_petty_cash,2);
                                tableHeadRow.appendChild(tdCol);
                            str.appendChild(tableHeadRow);
                        })
                    }

                    if(str !==''){
                            endDaySmallTable = cTag('table',{ 'align':`center`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                                const thead = cTag('thead');
                                    tableHeadRow = cTag('tr',{ 'class':`border` });
                                        thCol = cTag('th',{ 'colspan':`3`,'style':`background-color:#e0dfdf`,'align':`left` });
                                        thCol.innerHTML = Translate('Petty Cash Information');
                                    tableHeadRow.appendChild(thCol);
                                thead.appendChild(tableHeadRow);
                                    tableHeadRow = cTag('tr');
                                        thCol = cTag('th',{ 'style': `text-align: left;` });
                                        thCol.innerHTML = Translate('Reason');
                                    tableHeadRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'style': `text-align: left;`,'width':`20%` });
                                        thCol.innerHTML = Translate('Add/Sub');
                                    tableHeadRow.appendChild(thCol);
                                        thCol = cTag('th',{ 'style': "text-align: right;",'width':`20%` });
                                        thCol.innerHTML = Translate('Amount');
                                    tableHeadRow.appendChild(thCol);
                                thead.appendChild(tableHeadRow);
                            endDaySmallTable.appendChild(thead);
                                const tbody = cTag('tbody');
                                tbody.append(str);
                                    tableHeadRow = cTag('tr');
                                        tdCol = cTag('td',{ 'align':`right`,'colspan':`2` });
                                            strong = cTag('strong');
                                            strong.innerHTML = `${Translate('Total')} : `;
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                        tdCol = cTag('td',{ 'align':`right` });
                                            strong = cTag('strong');
                                            strong.innerHTML = addCurrency(total_petty_cash);
                                        tdCol.appendChild(strong);
                                    tableHeadRow.appendChild(tdCol);
                                tbody.appendChild(tableHeadRow);
                            endDaySmallTable.appendChild(tbody);
                        tdCol1.appendChild(endDaySmallTable);
                    }

                tableHeadRow1.appendChild(tdCol1);
            endDaySmallTable1.appendChild(tableHeadRow1);
            if(data.comments !==''){
                    tableHeadRow1 = cTag('tr');
                        tdCol1 = cTag('td',{ 'align':`center`,'colspan':`4` });                                                   
                            endDaySmallTable = cTag('table',{ 'align':`center`,'width':`100%`,'cellpadding':`0`,'cellspacing':`0` });
                                tableHeadRow = cTag('tr',{ 'class':`border` });
                                    tdCol = cTag('td',{ 'align':`right`,'width':`10%` });
                                        strong = cTag('strong');
                                        strong.innerHTML = `${Translate('Comments')}: `;
                                    tdCol.appendChild(strong);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'align':`left` });
                                    tdCol.innerHTML = data.comments;
                                tableHeadRow.appendChild(tdCol);
                            endDaySmallTable.appendChild(tableHeadRow);
                        tdCol1.appendChild(endDaySmallTable);
                    tableHeadRow1.appendChild(tdCol1);
                endDaySmallTable1.appendChild(tableHeadRow1);
            }
        Dashboard.appendChild(endDaySmallTable1);
    }
}

async function AJ_End_of_Day_prints_eodlist_MoreInfo(){
    segment4 = segment4.replace(/%20/g, ' ');
		
    const jsonData = {'eod_date':segment4};
    const url = '/'+segment1+`/AJ_prints_eodlist_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = data.title;		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@charset "utf-8";
                body{box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; width:98%; margin:0; padding:1%;background:#fff;color:#666;line-height:20px; font-size: 12px;}
                @page {size: auto;margin-top: 0;margin-bottom: 0;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                
                .pright15{padding-right:15px;}
                .pbottom10{padding-bottom:10px;}
                
                .mtop10{ margin-top:10px;}
                .mbottom0{ margin-bottom:0px;}
                .mleft10{ margin-left:10px;}
                
                table{border-collapse:collapse; width:100%;}
                .table-bordered th{background:#F5F5F6;}
                .table-bordered td, .table-bordered th{ border:1px solid #DDDDDD; padding:8px 10px;}
                .table-bordered td.bgnone {background-color:#FFF;border:0px solid #fff;}
                
                .width25{ width:25%; float:left;}
                .width30{ width:30%; float:left;}
                .width35{ width:35%; float:left;}
                .width40{ width:40%; float:left;}
                .width45{ width:45%; float:left;}
                .width50{ width:50%; float:left;}
                .width100{ width:100%; float:left;}
                
                .txtbold{font-size:14px; font-weight:bold; line-height:22px;}
                .txt16normal{font-size:16px; font-weight:normal; line-height:22px;}
                .txt18bold{font-size:18px; font-weight:bold; text-transform:capitalize;}
                .txt20bold{font-size:20px; font-weight:bold; text-transform:capitalize;}
                .txtleft{ text-align:left;}
                .txtcenter{ text-align:center;}
                .txtright{ text-align:right;}
                
                .hilightbutton2{background:#a71d4c; border:1px solid #a71d4c; color:#fff; border-radius:4px; padding:4px 10px;}
                .floatright{ float:right;}
                .bordertop{ border-top:1px solid #dddddd;}
                `
            );
        head.appendChild(style);

        let tableHeadRow, tdCol, thCol;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const companyNameDiv = cTag('div',{ 'class':`width100` });
                let companyNameWidth = cTag('div',{ 'class':`txtbold`, 'style': "text-align: left; width:30%; float:left;"});
                companyNameWidth.innerHTML = data.companyName;
            companyNameDiv.appendChild(companyNameWidth);
                let endReportDiv = cTag('div',{ 'class':`width40 txtcenter txtbold`, 'style': "font-size: 20px;" });
                endReportDiv.innerHTML = Translate('End of Day Report');
            companyNameDiv.appendChild(endReportDiv);
                let dbDateDiv = cTag('div',{ 'class':`txt16normal`, 'style': "text-align: right; width:30%; float:left;" });
                dbDateDiv.innerHTML = `${Translate('Date printed')}: ${DBDateToViewDate(data.todayDate)}`;
            companyNameDiv.appendChild(dbDateDiv);
        Dashboard.appendChild(companyNameDiv);
            let hrDiv = cTag('div',{ 'class':`width100` });
            hrDiv.appendChild(cTag('hr',{ 'class':`mbottom0`, 'style': "margin-top: 10px;" }));
        Dashboard.appendChild(hrDiv);
            let dateRangeDiv = cTag('div',{ 'class':`width100`, 'style': "margin-top: 10px;", 'id':`filterby` });
            dateRangeDiv.innerHTML = `${Translate('Date Range')}: ${DBDateToViewDate(data.startdate)} to ${DBDateToViewDate(data.enddate)}`;
        Dashboard.appendChild(dateRangeDiv);
            let filterDiv = cTag('div',{ 'class':`width100`,'id':`filterby` });
            filterDiv.innerHTML = window.location.host;
        Dashboard.appendChild(filterDiv);
            let printEndDay = cTag('div',{ 'class':`width100`, 'style': "margin-top: 10px;" });
                const printEndDayTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                    const endDayHead = cTag('thead',{ 'class':`cf` });
                        tableHeadRow = cTag('tr');
                            tdCol = cTag('td',{ 'style': "width: 80px;" });
                            tdCol.innerHTML = Translate('Date');
                        tableHeadRow.appendChild(tdCol);
                            thCol = cTag('th',{ 'align':`left`,'width':`15%` });
                            thCol.innerHTML = Translate('Payment Type');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'style': "text-align: right;", 'width':`15%` });
                            thCol.innerHTML = Translate('Calculated');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'style': "text-align: right;", 'width':`15%` });
                            thCol.innerHTML = Translate('Counted');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'style': "text-align: right;", 'width':`15%` });
                            thCol.innerHTML = Translate('Difference');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'style': "text-align: right;" });
                            thCol.innerHTML = Translate('Comments');
                        tableHeadRow.appendChild(thCol);
                    endDayHead.appendChild(tableHeadRow);
                printEndDayTable.appendChild(endDayHead);
                    const endDayBody = cTag('tbody');
                    if(data.posPaymentData){
                        let gtotal_calculated = 0;
                        let gtotal_counted = 0;

                        data.posPaymentData.forEach(item=>{
                            let total_calculated = 0;
                            let total_counted = 0;
                            const rowspan = item.subPosPaymentData.length+1;
                            item.subPosPaymentData.forEach((info)=>{
                                total_calculated = calculate('add',info.calculated,total_calculated,2);
                                total_counted = calculate('add',info.counted,total_counted,2);

                                if(info.i===1){
                                        tableHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'rowspan':rowspan,'data-title':Translate('Date'),'align':`center` });
                                            tdCol.innerHTML = DBDateToViewDate(info.payment_datetime);
                                            if(info.drawer !=''){tdCol.append(cTag('br'), info.drawer);}
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Payment Type'),'align':`left` });
                                            tdCol.innerHTML = info.payment_method;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Calculated'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(info.calculated);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(info.counted);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Difference'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(calculate('sub',info.calculated,info.counted,2));
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'rowspan':rowspan,'data-title':Translate('Comments'),'align':`left` });
                                            tdCol.innerHTML = info.comments;
                                        tableHeadRow.appendChild(tdCol);
                                    endDayBody.appendChild(tableHeadRow);
                                }
                                else{
                                        tableHeadRow = cTag('tr');
                                            tdCol = cTag('td',{ 'data-title':Translate('Payment Type'),'align':`left` });
                                            tdCol.innerHTML = info.payment_method;
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Calculated'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(info.calculated);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Counted'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(info.counted);
                                        tableHeadRow.appendChild(tdCol);
                                            tdCol = cTag('td',{ 'data-title':Translate('Difference'),'align':`right` });
                                            tdCol.innerHTML = addCurrency(calculate('sub',info.calculated,info.counted,2));
                                        tableHeadRow.appendChild(tdCol);
                                    endDayBody.appendChild(tableHeadRow);
                                }
                            })
                                tableHeadRow = cTag('tr');
                                    thCol = cTag('th',{ 'style': "text-align: right;" });
                                    thCol.innerHTML = `${Translate('Total')} : `;
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'style': "text-align: right;" });
                                    thCol.innerHTML = addCurrency(total_calculated);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'style': "text-align: right;" });
                                    thCol.innerHTML = addCurrency(total_counted);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'style': "text-align: right;" });
                                    thCol.innerHTML = addCurrency(calculate('sub',total_calculated,total_counted,2));
                                tableHeadRow.appendChild(thCol);
                            endDayBody.appendChild(tableHeadRow);

                            gtotal_calculated = calculate('add',total_calculated,gtotal_calculated,2);
                            gtotal_counted = calculate('add',total_counted,gtotal_counted,2);
                        })
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'colspan':`2`, 'style': "text-align: right;" });
                                thCol.innerHTML = `${Translate('Grand Total')} : `;
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'style': "text-align: right;" });
                                thCol.innerHTML = addCurrency(gtotal_calculated);
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'style': "text-align: right;" });
                                thCol.innerHTML = addCurrency(gtotal_counted);
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'style': "text-align: right;" });
                                thCol.innerHTML = addCurrency(calculate('sub',gtotal_calculated,gtotal_counted,2));
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'style': "text-align: right;" });
                                thCol.innerHTML = ' ';
                            tableHeadRow.appendChild(thCol);
                        endDayBody.appendChild(tableHeadRow);
                    }
                    else{
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':`6`});
                                tdCol.innerHTML = '';
                            tableHeadRow.appendChild(tdCol);
                        endDayBody.appendChild(tableHeadRow);
                    }
                printEndDayTable.appendChild(endDayBody);
            printEndDay.appendChild(printEndDayTable);
        Dashboard.appendChild(printEndDay);
    }
}

//----------Admin
async function Admin_printsInvoice(){
    const jsonData = {
        'our_invoices_id':segment3
    };
    const url = '/'+segment1+`/AJ_printsInvoice_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = data.title;		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@page {size: auto;}						
                body{ box-sizing: border-box; font-family:Arial, sans-serif, Helvetica; width:100%; margin:0; padding:0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px; line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .pbottom10{padding-bottom:10px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:8px 10px; vertical-align: top;}`
            );
        head.appendChild(style);

        let address,strong, tableHeadRow, tdCol, atag, thCol;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const adminPrintTable1 = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center`,'style':`margin-top: 10px` });
                        address = cTag('address');
                            strong = cTag('strong');
                            strong.innerHTML = data.COMPANYNAME+' Software';
                        address.appendChild(strong);
                        address.appendChild(cTag('br'));
                        address.append('PO Box 66121 Town Center');
                        address.appendChild(cTag('br'));
                        address.append('Pickering ON L1V1B0, Canada');
                        address.appendChild(cTag('br'));
                            atag = cTag('a',{ 'href':`tel:647.556.1181` });
                            atag.innerHTML = '647.556.1181 Canada';
                        address.appendChild(atag);
                        address.appendChild(cTag('br'));
                            atag = cTag('a',{ 'href':`tel:702.482.9233` });
                            atag.innerHTML = '702.482.9233 USA';
                        address.appendChild(atag);
                        address.appendChild(cTag('br'));
                            atag = cTag('a',{ 'href':`mailto:info@skitsbd.com` });
                            atag.innerHTML = 'info@skitsbd.com';
                        address.appendChild(atag);
                    tdCol.appendChild(address);
                tableHeadRow.appendChild(tdCol);
            adminPrintTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`left` });
                        strong = cTag('strong');
                        strong.innerHTML = 'BILL TO:';
                    tdCol.appendChild(strong);
                    tdCol.appendChild(cTag('br'));
                        address = cTag('address');
                        address.innerHTML = data.company_info;
                    tdCol.appendChild(address);
                tableHeadRow.appendChild(tdCol);
            adminPrintTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            adminPrintTable1.appendChild(tableHeadRow);
                const tableHeadRow1 = cTag('tr');
                    const tdCol1 = cTag('td');
                        const adminPrintTable = cTag('table',{ 'class':`border`,'cellpadding':`0`,'cellspacing':`0`,'width':`100%` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'align':`left` });
                                thCol.innerHTML = 'Description';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`20%` });
                                thCol.innerHTML = 'Number Location';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = 'Price/Location';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = 'Total';
                            tableHeadRow.appendChild(thCol);
                        adminPrintTable.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr',{ 'class':`border` });
                                tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                                tdCol.innerHTML = data.description;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right`,'valign':`top` });
                                tdCol.innerHTML = data.num_locations;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right`,'valign':`top` });
                                tdCol.innerHTML = addCurrency(data.price_per_location);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'nowrap':``,'align':`right`,'valign':`top` });
                                tdCol.innerHTML = addCurrency(data.total);
                            tableHeadRow.appendChild(tdCol);
                        adminPrintTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(adminPrintTable);
                tableHeadRow1.appendChild(tdCol1);
            adminPrintTable1.appendChild(tableHeadRow1);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            adminPrintTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.appendChild(cTag('br'));
                        strong = cTag('strong');
                        strong.innerHTML = 'PAYMENT DETAILS:';
                    tdCol.appendChild(strong);
                    tdCol.appendChild(cTag('br'));
                        address = cTag('address');
                        address.innerHTML = data.paid_by;
                        address.append(' ', DBDateToViewDate(data.paid_on));
                    tdCol.appendChild(address);
                tableHeadRow.appendChild(tdCol);
            adminPrintTable1.appendChild(tableHeadRow);
        Dashboard.appendChild(adminPrintTable1)
    }
}

//----------Account
async function Account_prints(){
    const jsonData = {
        'our_invoices_id':segment3
    };
    const url = '/'+segment1+`/AJ_prints_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const head = document.head;
            const title = cTag('title');
            title.innerHTML = data.title;		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@page {size: auto;}						
                body{ font-family:Arial, sans-serif, Helvetica; width:100%; margin:0; padding:0;background:#fff;color:#000;line-height:20px; font-size: 12px;}
                h2{font-size:22px;line-height:30px; margin-bottom:0; padding-bottom:0; font-weight:500;}
                .h4, h4 {font-size: 18px;margin-bottom: 10px;margin-top: 10px; font-weight:500;}
                address {font-style: normal;line-height: 1.42857;margin-bottom: 20px;}
                .pright15{padding-right:15px;}
                .pbottom10{padding-bottom:10px;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse;}
                .border th{background:#F5F5F6;}
                .border td, .border th{ border:1px solid #DDDDDD; padding:8px 10px; vertical-align: top;}`
            );
        head.appendChild(style);

        let address,strong, tableHeadRow, tdCol, aTag, thCol;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const billingTable1 = cTag('table',{ 'cellpadding':`0`,'cellspacing':`1`,'width':`100%` });
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center`,'style':`margin-top: 10px` });
                        address = cTag('address');
                            strong = cTag('strong');
                            strong.innerHTML = data.COMPANYNAME+' Software';
                        address.appendChild(strong);
                        address.appendChild(cTag('br'));
                        address.append('PO Box 66121 Town Center');
                        address.appendChild(cTag('br'));
                        address.append('Pickering ON L1V1B0, Canada');
                        address.appendChild(cTag('br'));
                            aTag = cTag('a',{ 'href':`tel:647.556.1181` });
                            aTag.innerHTML = '647.556.1181 Canada';
                        address.appendChild(aTag);
                        address.appendChild(cTag('br'));
                            aTag = cTag('a',{ 'href':`tel:702.482.9233` });
                            aTag.innerHTML = '702.482.9233 USA';
                        address.appendChild(aTag);
                        address.appendChild(cTag('br'));
                            aTag = cTag('a',{ 'href':`mailto:info@skitsbd.com` });
                            aTag.innerHTML = 'info@skitsbd.com';
                        address.appendChild(aTag);
                    tdCol.appendChild(address);
                tableHeadRow.appendChild(tdCol);
            billingTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`left` });
                        strong = cTag('strong');
                        strong.innerHTML = 'BILL TO:';
                    tdCol.appendChild(strong);
                    tdCol.appendChild(cTag('br'));
                        address = cTag('address');
                        address.innerHTML = data.company_info;
                    tdCol.appendChild(address);
                tableHeadRow.appendChild(tdCol);
            billingTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            billingTable1.appendChild(tableHeadRow);
                const tableHeadRow1 = cTag('tr');
                    const tdCol1 = cTag('td');
                        const billingTable = cTag('table',{ 'class':`border`,'cellpadding':`0`,'cellspacing':`0`,'width':`100%` });
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'align':`left` });
                                thCol.innerHTML = 'Description';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`20%` });
                                thCol.innerHTML = 'Number Location';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = 'Price/Location';
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = 'Total';
                            tableHeadRow.appendChild(thCol);
                        billingTable.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr',{ 'class':`border` });
                                tdCol = cTag('td',{ 'align':`left`,'valign':`top` });
                                tdCol.innerHTML = data.description;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right`,'valign':`top` });
                                tdCol.innerHTML = data.num_locations;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'align':`right`,'valign':`top` });
                                tdCol.innerHTML = currency+data.price_per_location;
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'nowrap':``,'align':`right`,'valign':`top` });
                                tdCol.innerHTML = currency+data.total;
                            tableHeadRow.appendChild(tdCol);
                        billingTable.appendChild(tableHeadRow);
                    tdCol1.appendChild(billingTable);
                tableHeadRow1.appendChild(tdCol1);
            billingTable1.appendChild(tableHeadRow1);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.innerHTML = ' ';
                tableHeadRow.appendChild(tdCol);
            billingTable1.appendChild(tableHeadRow);
                tableHeadRow = cTag('tr');
                    tdCol = cTag('td',{ 'align':`center` });
                    tdCol.appendChild(cTag('br'));
                        strong = cTag('strong');
                        strong.innerHTML = 'PAYMENT DETAILS:';
                    tdCol.appendChild(strong);
                    tdCol.appendChild(cTag('br'));
                        address = cTag('address');
                        address.innerHTML = data.paid_by;
                        address.append(' ', DBDateToViewDate(data.paid_on));
                    tdCol.appendChild(address);
                tableHeadRow.appendChild(tdCol);
            billingTable1.appendChild(tableHeadRow);
        Dashboard.appendChild(billingTable1)
    }
}

//----------Expense
async function Expenses_prints(){
    const searchParams = new URL(window.location).searchParams;
    const jsonData = {
        'dr':searchParams.get('dr'),
        'vid':searchParams.get('vid'),
        'et':searchParams.get('et'),
        'st':searchParams.get('st'),
        'ks':searchParams.get('ks')
    };
    const url = '/'+segment1+`/AJ_prints_MoreInfo`;

    await fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        const head = document.head;
            const title = cTag('title');
            title.innerHTML =Translate('Expenses List');		
        head.appendChild(title);
            const style = cTag('style');
            style.setAttribute('type','text/css');
            style.append(
                `@charset "utf-8";
                body{ font-family:Arial, sans-serif, Helvetica; width:98%; margin:0; padding:1%;background:#fff;color:#666;line-height:20px; font-size: 12px;}
                @page {size: auto;margin-top: 0;margin-bottom: 0;}
                .mbottom0{ margin-bottom:0px;}
                table{border-collapse:collapse; width:100%;}
                .table-bordered th{background:#F5F5F6;}
                .table-bordered td, .table-bordered th{ border:1px solid #DDDDDD; padding:8px 10px;}
                .table-bordered td.bgnone {background-color:#FFF;border:0px solid #fff;}
                .width40{ width:40%; float:left;}
                .width100{ width:100%; float:left;}                    
                .txtbold{font-size:14px; font-weight:bold; line-height:22px;}
                .txt16normal{font-size:16px; font-weight:normal; line-height:22px;}
                .txtcenter{ text-align:center;}
                `
            );
        head.appendChild(style);

        let tableHeadRow, thCol, tdCol;
        const Dashboard = document.querySelector('#viewPageInfo');
        Dashboard.innerHTML = '';
            const companyNameDiv = cTag('div',{ 'class':`width100` });
                let companyNameTitle = cTag('div',{ 'class':`txtbold`, 'style': " font-size: 18px; text-align: left; width:30%; float:left;" });
                companyNameTitle.innerHTML = data.company_name;
            companyNameDiv.appendChild(companyNameTitle);
                let expenseTitle = cTag('div',{ 'class':`width40 txtcenter txtbold`, 'style': "font-size: 20px;" });
                expenseTitle.innerHTML = Translate('Expenses List');
            companyNameDiv.appendChild(expenseTitle);
                let printedDate = cTag('div',{ 'class':`txt16normal`, 'style': "text-align: right; width:30%; float:left;" });
                printedDate.innerHTML = `${Translate('Date printed')}: ${DBDateToViewDate(data.todayDate)}`;
            companyNameDiv.appendChild(printedDate);
        Dashboard.appendChild(companyNameDiv);
            let hrTag = cTag('div',{ 'class':`width100` });
            hrTag.appendChild(cTag('hr',{ 'class':`mbottom0`, 'style': "margin-top: 10px;" }));
        Dashboard.appendChild(hrTag);
            let filterWidth = cTag('div',{ 'class':`width100`, 'style': "margin-top: 10px;", 'id':`filterby` });
            filterWidth.innerHTML = `${Translate('Vendor Name')}: ${data.svendorsStr||'All Vendors'}, ${Translate('Expense Type')}: ${data.sexpense_type||'All Expense Type'}${data.date_range!=''?', '+Translate('Date Range')+': '+data.date_range+', ':''}${data.keyword_search!=''?Translate('Search Expenses')+': '+data.keyword_search:''}`;
        Dashboard.appendChild(filterWidth);
            let expensePrintDiv = cTag('div',{ 'class':`width100`, 'style': "margin-top: 10px;" });
                const expensePrintTable = cTag('table',{ 'class':`columnMD12 table-bordered table-striped table-condensed cf listing` });
                    const expenseHead = cTag('thead',{ 'class':`cf` });
                        tableHeadRow = cTag('tr');
                            thCol = cTag('th',{ 'align':`left`,'width':`10%` });
                            thCol.innerHTML = Translate('Bill Date');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'align':`left`,'width':`10%` });
                            thCol.innerHTML = Translate('Bill Number');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'align':`left` });
                            thCol.innerHTML = Translate('Expense Type');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'align':`left`,'width':`20%` });
                            thCol.innerHTML = Translate('Vendor Name');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'align':`left`,'width':`10%` });
                            thCol.innerHTML = Translate('Bill Amount');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'align':`left`,'width':`10%` });
                            thCol.innerHTML = Translate('Bill Paid');
                        tableHeadRow.appendChild(thCol);
                            thCol = cTag('th',{ 'align':`left`,'width':`10%` });
                            thCol.innerHTML = Translate('Reference');
                        tableHeadRow.appendChild(thCol);
                    expenseHead.appendChild(tableHeadRow);
                expensePrintTable.appendChild(expenseHead);
                    const expenseBody = cTag('tbody');
                    let totalExpese = 0;
                    if(data.tableData.length){
                        data.tableData.forEach(item=>{
                            totalExpese = calculate('add',item[4],totalExpese,2);
                                tableHeadRow = cTag('tr');
                                    tdCol = cTag('td',{ 'nowrap':``,'data-title':Translate('Bill Date'),'align':`justify` });
                                    tdCol.innerHTML = DBDateToViewDate(item[0],0,1);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'nowrap':``,'data-title':Translate('Bill Number'),'align':`justify` });
                                    tdCol.innerHTML = item[1];
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Expense Type'),'align':`left` });
                                    tdCol.innerHTML = item[2];
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Vendor Name'),'align':`left` });
                                    tdCol.innerHTML = item[3];
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'nowrap':``,'data-title':Translate('Bill Amount'),'align':`right` });
                                    tdCol.innerHTML = addCurrency(item[4]);
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'nowrap':``,'data-title':Translate('Bill Paid'),'align':`justify` });
                                    tdCol.innerHTML = item[5];
                                tableHeadRow.appendChild(tdCol);
                                    tdCol = cTag('td',{ 'data-title':Translate('Reference'),'align':`left` });
                                    tdCol.innerHTML = item[6];
                                tableHeadRow.appendChild(tdCol);
                            expenseBody.appendChild(tableHeadRow);
                        })        
                            tableHeadRow = cTag('tr',{ 'class':`thRow txtbold` });
                                tdCol = cTag('td',{ 'colspan':`4`,'data-title':Translate('Total'), 'style': "text-align: right;" });
                                tdCol.innerHTML = Translate('Total')+' :';
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'data-title':Translate('Bill Amount'), 'style': "text-align: right;" });
                                tdCol.innerHTML = addCurrency(totalExpese);
                            tableHeadRow.appendChild(tdCol);
                                tdCol = cTag('td',{ 'colspan':`2`,'data-title':``,'align':`justify` });
                                tdCol.innerHTML = ' ';
                            tableHeadRow.appendChild(tdCol);
                        expenseBody.appendChild(tableHeadRow);
                    }
                    else{
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':`7`,'style':`color: red` });
                                tdCol.innerHTML = Translate('No expenses meet the criteria given');
                            tableHeadRow.appendChild(tdCol);
                        expenseBody.appendChild(tableHeadRow);
                    }
                expensePrintTable.appendChild(expenseBody);
            expensePrintDiv.appendChild(expensePrintTable);
        Dashboard.appendChild(expensePrintDiv);
    }
}

function getPublicFormData(parentNode, data, formsFor){
        let strong, pTag;
        const nameOffDiv = cTag('div',{ 'style':`clear: both;margin:15px 0;width:100%; text-align:left; float:left;` });
            let nameOffTitle = cTag('div',{ 'style':`background: linear-gradient(to bottom, #FAFAFA 0%, #E9E9E9 100%) repeat-x scroll 0 0 #E9E9E9;border: 1px solid #D5D5D5;border-top-left-radius: 4px;border-top-right-radius: 4px;height: 40px;line-height: 40px;padding-left:15px;` });
                const nameOffHeader = cTag('h3',{ 'style':`font-size:11px;margin:0; padding:0;color: #555555;display: inline-block;line-height: 18px;text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);` });
                nameOffHeader.innerHTML = ' '+Translate('Name of')+' '+formsFor+' '+Translate('Form')+' ';
            nameOffTitle.appendChild(nameOffHeader);
        nameOffDiv.appendChild(nameOffTitle);
            let anotherDiv = cTag('div',{ 'style':`background:#fff;border: 1px solid #D5D5D5; float:left; width:100%;border-radius: 0px 0px 5px 5px;padding:20px 0px; margin-top: -1px;` });
                const publicTable = cTag('table',{ 'width':`99%`,'cellpadding':`5`,'cellspacing':`0` });
                data.forEach(item=>{
                        const tableHeadRow = cTag('tr');
                            const tdCol = cTag('td',{ 'style':`padding-top: 5px;word-wrap: break-word;font-size:11px;line-height: 21px; padding:0 20px;` });
                            if(item.i>0) tdCol.appendChild(cTag('hr'));
                            tdCol.innerHTML += item.form_name;
                            if(item.formsData.length){
                                item.formsData.forEach(formdata=>{
                                    if(formdata.field_type==='TextOnly' && formdata.parameters !==''){
                                            pTag = cTag('p');
                                            pTag.innerHTML = formdata.parameters;
                                        tdCol.appendChild(pTag);
                                    }
                                    else if(formdata.field_type==='SectionBreak'){
                                        if(formdata.parameters !==''){
                                                pTag = cTag('p',{ class: "txtbold" });
                                                pTag.innerHTML = formdata.parameters;
                                                pTag.appendChild(cTag('hr',{ 'style': "margin-top: 10px; margin-bottom: 10px;" }));
                                            tdCol.appendChild(pTag);
                                        }
                                        else{
                                            tdCol.appendChild(cTag('hr',{ 'style': "margin-top: 10px; margin-bottom: 10px;" }));
                                        }
                                    }
                                    else if(formdata.fieldVal !==''){
                                        if(formdata.fieldType==='UploadImage' && formdata.fieldVal !==''){
                                                pTag = cTag('p',{ 'style':`margin:0` });
                                                    strong = cTag('strong');
                                                    strong.innerHTML = formdata.oneFieldLb+' : ';
                                                pTag.appendChild(strong);
                                                pTag.appendChild(cTag('img',{ 'align':`center`,'src':formdata.fieldVal,'style':`max-width:100%; margin-bottom:10px;` }));
                                            tdCol.appendChild(pTag);
                                        }
                                        else if(formdata.fieldType==='Signature' && formdata.fieldVal !==''){
                                                pTag = cTag('p',{ 'style':`margin:0` });
                                                    strong = cTag('strong');
                                                    strong.innerHTML = formdata.oneFieldLb+' :';
                                                pTag.appendChild(strong);
                                            tdCol.appendChild(pTag);
                                            tdCol.appendChild(cTag('div',{ 'style':`clear: both` }));
                                                pTag = cTag('p',{ 'style':`margin: 0` });
                                                pTag.appendChild(cTag('img',{ 'style':`max-width: 100%; margin-bottom: 10px`,'alt':formdata.fieldType,'src':formdata.fieldVal }));
                                            tdCol.appendChild(pTag);
                                        }
                                        else{
                                                pTag = cTag('p',{ 'style':`margin:0` });
                                                    strong = cTag('strong');
                                                    strong.innerHTML = formdata.oneFieldLb+' : ';
                                                pTag.appendChild(strong);
                                                pTag.append(formdata.fieldVal);
                                            tdCol.appendChild(pTag);
                                        }
                                    }
                                })
                            }
                        tableHeadRow.appendChild(tdCol);
                    publicTable.appendChild(tableHeadRow);
                })
            anotherDiv.appendChild(publicTable);
        nameOffDiv.appendChild(anotherDiv);
    parentNode.appendChild(nameOffDiv)
}

function getPublicNotes(parentNode,notes){
    if(Array.isArray(notes) && notes.length){
        parentNode.innerHTML = '';
            const noteHistoryDiv = cTag('div',{ 'style':`clear: both;margin:15px 0;width:99.80%;text-align:left; float:left;` });
                let noteHistoryStyle = cTag('div',{ 'style':`background: linear-gradient(to bottom, #FAFAFA 0%, #E9E9E9 100%) repeat-x scroll 0 0 #E9E9E9;border: 1px solid #D5D5D5;border-top-left-radius: 4px;border-top-right-radius: 4px;height: 40px;line-height: 40px;padding-left:15px;` });
                    const noteHistoryHeader = cTag('h3',{ 'style':`font-size: 14px;margin:0; padding:0;color: #555555;display: inline-block;line-height: 18px;text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);` });
                    noteHistoryHeader.innerHTML = Translate('Note History');
                noteHistoryStyle.appendChild(noteHistoryHeader);
            noteHistoryDiv.appendChild(noteHistoryStyle);
                let userDiv = cTag('div',{ 'style':`background:#fff;border: 1px solid #D5D5D5; float:left; width:100%;border-radius: 0px 0px 5px 5px;padding:20px 0px; margin-top: -1px;` });
                    const userTable = cTag('table',{ 'width':`99%`,'cellpadding':`5`,'cellspacing':`0` });
                    notes.forEach((item,indx)=>{
                            const tableHeadRow = cTag('tr');
                                const tdCol = cTag('td',{ 'style':`padding-top: 5px;word-wrap: break-word;font-size: 14px;line-height: 21px; padding:0 20px;` });
                                if(indx>0) tdCol.append(cTag('hr'));
                                    const strong = cTag('strong');
                                    strong.innerHTML = `${DBDateToViewDate(item.created_on,1)[0]} By ${item.user_name}`;
                                tdCol.appendChild(strong);
                                tdCol.appendChild(cTag('br'));
                                if(item.fromTable==='digital_signature'){
                                    tdCol.appendChild(cTag('div',{ 'class':`clear` }));
                                    tdCol.appendChild(cTag('img',{ 'style':`max-width: 100%`,'alt':Translate('Signature'),'src':item.note }));
                                }
                                else{
                                    let spanTag = cTag('span');
                                    spanTag.innerHTML = item.note;  
                                    tdCol.appendChild(spanTag);
                                }
                            tableHeadRow.appendChild(tdCol);
                        userTable.appendChild(tableHeadRow);
                    });                    
                userDiv.appendChild(userTable);
            noteHistoryDiv.appendChild(userDiv);
        parentNode.appendChild(noteHistoryDiv);
    }
}

function getPublicSmallNotes(parentNode,notes){
    if(Array.isArray(notes) && notes.length){
        parentNode.innerHTML = '';
            const smallHistoryDiv = cTag('div',{ 'style':`clear: both;margin:15px 0;width:100%; text-align:left; float:left;` });
                let smallHistoryStyle = cTag('div',{ 'style':`background: linear-gradient(to bottom, #FAFAFA 0%, #E9E9E9 100%) repeat-x scroll 0 0 #E9E9E9;border: 1px solid #D5D5D5;border-top-left-radius: 4px;border-top-right-radius: 4px;height: 40px;line-height: 40px;padding-left:15px;` });
                    const historyHeader = cTag('h3',{ 'style':`font-size:11px;margin:0; padding:0;color: #555555;display: inline-block;line-height: 18px;text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);` });
                    historyHeader.innerHTML = Translate('Note History');
                smallHistoryStyle.appendChild(historyHeader);
            smallHistoryDiv.appendChild(smallHistoryStyle);
                let userHistoryDiv = cTag('div',{ 'style':`background:#fff;border: 1px solid #D5D5D5; float:left; width:100%;border-radius: 0px 0px 5px 5px;padding:20px 0px; margin-top: -1px;` });
                    const historyTable = cTag('table',{ 'width':`99%`,'cellpadding':`5`,'cellspacing':`0` });
                    notes.forEach((item,indx)=>{
                            const tableHeadRow = cTag('tr');
                                const tdCol = cTag('td',{ 'style':`padding-top: 5px;word-wrap: break-word;font-size:11px;line-height: 21px; padding:0 20px;` });
                                if(indx>0) tdCol.appendChild(cTag('hr'));
                                    const strong = cTag('strong');
                                    strong.innerHTML = `${DBDateToViewDate(item.created_on,1)[0]} By ${item.user_name}`;
                                tdCol.appendChild(strong);
                                tdCol.appendChild(cTag('br'));
                                if(item.fromTable==='digital_signature'){
                                    tdCol.appendChild(cTag('div',{ 'class':`clear` }));
                                    tdCol.appendChild(cTag('img',{ 'style':`max-width:100%;`,'alt':Translate('Signature'),'src':item.note }));
                                }
                                else tdCol.append(item.note);
                            tableHeadRow.appendChild(tdCol);
                        historyTable.appendChild(tableHeadRow);
                    })
                userHistoryDiv.appendChild(historyTable);
            smallHistoryDiv.appendChild(userHistoryDiv);
        parentNode.appendChild(smallHistoryDiv);
    }
}

function creatCompanylogo(imgSource,logo_size,wrappedByTr){
    let logo;
    if(wrappedByTr){
        logo = cTag('tr');
            const tdCol = cTag('td',{ 'align':`center`,'colspan':`2` });
            tdCol.appendChild(cTag('img',{ 'style':`max-height:100px;max-width:100%;`,'src':imgSource,'title':Translate('Logo') }));
        logo.appendChild(tdCol);
    }
    else{
        let style;
        if(logo_size==='Large Logo'){
            style = 'max-height:150px;max-width:350px;';
        }
        else{
            style = 'max-height:100px;max-width:150px;';
        }
        logo = cTag('img',{'style':style,'src':imgSource,'title':Translate('Logo')});
    }
    return logo;
}

//=========Products/Devices=======
async function Products_prints(){
    await getAndPrintLabel('productLabel');
}

async function IMEI_prints(){
    await getAndPrintLabel('deviceLabel');
}

//=========signature======
function digitalSignature(){
    let canvasContainer = document.getElementById('signature');
    let toolsContainer = document.getElementById('tools');
    canvasContainer.innerHTML = toolsContainer.innerHTML = '';
    let imgData = 'data:image/gif;base64,R0lGODlhtABJAOf/AAABAAACAAEEAAIFAQQHAgUIBAcJBQoJAAgLBwwLAAoMCA0MAA8OAwwPCxMQAA8RDRURABYSARITERMVBBQVExcXABgYAhYYFRkaBBwbABkaGB0cAR4dAhocGRsdGh8fBSIgAB0fHB4gHSAhHyQjBSckACIjISgmASQlIyooBSYnJSwpAC0qAC4rAS8rAikqKCssKjEuBTMvAC0uLDUwAjcyBS8xLzg0Bjs1ATIzMTw3Azk5BDQ2MzY3NTs7Bjw8Bzg5Nz49ADo7OUA/AkJABEJBBTw+O0RCBj5APUVECEdFAUFCQElGAkpIBERGQ0xKBk5LB09MCEdJRlJOAlNPA0pLSVRQBFZSBk1PTFdTB1hUCVtVAE9RTlxXAl5YBFJUUVRVU2BaBmFbCFZXVWJcCVZYVVlbWWZgAmhhBGliBVxeW15gXWxlCW1mC2BiX25nDHBoAXJqA2NlYnRrBmZnZXFuCGhpZ3NwCnRxDHVyAHdzAWxta3l1A3p2BW9xbnx4CX55DIB7AHN1coJ8AYN+A3Z4dXh6d4eBCYiCDImDDnt9eoyFAI2GAo6HA36AfZCJBoCCf4KEgZOLDJWND4WHhJiQAYiKh5qSBZ2UCouNip+VDqGXAI6QjaSaA5CSj6ecB5SWk6ieC6qfDq2hAJeZlq+jAJmbmLCkAqynBJyem66oCZ2fnLCqDaCin7StALKsEKOloriwA7qyCaeppry0DamrqL+2AL21EMG4AKyuq8O6Aq6wrca8CbCyr7O1ssm/EcvBAM3DALa4tc/EAri6t7q8udLHCdPIDr2/vNXKEtjMANrNAMDCvtvPAsLEwd3QB8PFwsXHxN/SDcfJxuHUEuTWAOXXAOfYAMvNyejaBc3PzOrcC9DSz+zdEOjgE+viANPV0uzjAO3kAO7lANfZ1vDnBvLoC9nb2PPpD/brAPTqEtze2/ftAPnuAPrvAN/h3vvwAPzxBeHk4P7yCeXn5Ofp5unr6Ovt6u7w7fHz8PP18vX49Pn7+Pv9+v7//P///yH5BAEKAP8ALAAAAAC0AEkAAAj+AP8JHEhQoL+DCAsqXMiwocOHECNKnEixosWHCDNq9Hexo8ePIEOKhHiwXiQVFy6M+EKJ2L2NHEfKnEmz5sWDoAgE2MmT50pLxfDBtEm0qNGQBzntpGJLnLhqqgAxqdAzgIkxmZDlG3q0q9evBqHt1DSvrFmz1VD1URKhqgozmZht3Qi2rl2QB8EEGHS2r19qo/QcadtThRpO0OZqvMu4cUN/8ggs8Oa3cmVpovAQYVAVxhpP0fZxdUy6rr9ZAaxYXm053rJPdYIsqDrDDahpoumW3m3zoKAAiVgLXx1P2ac5O2bzJGBDDils/Ebznt7x4JIAt4ZrZ93u2KY3OJT+7ySQgw4pbdF1U19P0l+DAOK2yxfezpgmNjUM9DTQw04qbuktxt6ABPlDTgAnzKegdu0Ec0kaNOjEkwFA7NEKOAFmROB6/sASwBYLhrgdO8BUckYMEu5kgBB+XNiPdBvadZAfATQi4o3ysfPLJGK4UJUCRvgBCzkwxRSjVwcZEQAuODY5Hzq8SOIFC1U1gIQgs5xT5JFF+dOPAgGY4+SYCpqDSyNdnFDVA0sUUss6W3I5kj/gBMACmXiGWI4ti2hBQlUSOGFILu/EKedNrQQQBoOjeEHCCTggUk2eeYpDSyJXgFAVBVIosos8hh6KkT97BCDJcMrQUFUABPBV1ij+kuASDqVOhiMLIlRwUNUFVTjSCz2hilqgP0AEwItw3nAmwyeyGCPLGZzNKk5PJMARizq0OulNLINEsUFVHWABiS/1wHioP/wYQAC2rNURwAbs9IXOpGUhEl5PCzShSrZkbuPKIE1YAC4XkQhjj7kE+sNNADIM10UAOmz3RAAuELaAX7h4EYQYyfAb4jVRTVWVCCy5hDBv/qQSwBnDfbITFLzEw1o8beHSjix1kGUWOmhUlUV8HouYVh9JEOYTGEAJpV5pB9kRQCXaLUIYBnrQ61cwARgQr1/WUBkAEYPgsJMMMs8jjhixBB0iYHoQAUFVJpSRlWIaNnYQDwEEs13+OYhQtdOpfjESwA+VlePjAqiUFU8lEBhA2Tx/BBDBNWrjiNkdm7llBidynUwUugYY0M587GiSwk6b+MVEAH/41c7qBhx7VjfSmJXFTnNY1s4zlcvn2nGyVfXCYdDo4/mc2ARQQ4jtTBEAB6Ob1c5ssvilyU6fCFfDTgsA3RcgAYjS+4LtKNMJcuLt5BlouR3UJSkBsMFaNY1E3xc1O1Fz1jE7odOXOBNg3XDeph9EVMZ5YhgfjrrznXstpzbEcF9NDiKHAOjMMnkIQO78soydUM4sowgACfwSuQ+UbTXd+FsALLC1s2hhZasJRgWyp0AG2Qc/+mFVLiQ4k4PkIAD+xmDN9QjQCfvNgx3OO0JfBhEAKvjlT3yJxzV4MYpYKINdZZFFADAQjxhY0C8Tc1Vl4BAAPdSQfMHAA6t2wcM57UMyRnSdFXaygUFIAhF/+BYBZGeWK+ylL1gLQDW8UYJVVYAJ35jHIpo4D1QEIAUnLIuaLugXL7piNaJg0hktw0QC9KKNSJlGAHAwnHZ84garcoHezhKPAFbvLJUIwA7mUY309SSIL2TEPOLho0tKTydpq8y04GMZl4FoHrwAAR4GYUdVdOOMnfQFKD/iD1AEAA7yAUYf0JAFQMTCfq7AwRHmGABVeG8eTCRCWcTRjXCUgx3HKGBZ/qTJUzDMfs/+2EnHKqOKANBgNTsIAA3fsCoCMOET/hsf+AwgjGnexB9uCEAnxjSHVQVAmWXRgyxd9wM7jS6FASiH4nwEuHm8ImtxNIsa72CZcuzkcVF4JBSsEIRv7YQBzxxfH7IWQSN55CAzCIAyxsQOUSxiDle4gXIWMDpXBKACH1zn6rBTln6C4CwhXMAHGxGAG6yGCAFIXGW0iAGz+IiGZflFG2o2D2kwgQiJ2Ce/NGqAYjiUIge5ADFpFY9njOIXZVHHtzbQh1GoQg8YYFXqyrLTKbBSVVooSxoC8IbVtKV2lWHiFcryjZ1g9izt+GAbejKFofLrDllDxl0lkte9Vu4ZNu3+SQReWZYg/PEswNhJ9WyL1r5cg1W8CEYwjrEMb0RvdVBr5LuEY9smyGA8tPCLLS5xjWpYI6XyqagBVOvTihykA0JVoDk2oYQayOCgWJwHOoDpl9GWQB2c4R0/LbqTCaRAP8soCxkXxZpvMWkUbWGAac2iqZ58YA6rVFBFFcCM1Y6KDhrc5Gq0yIAWmkUcVAmD5FZDxgq4gAUpOMEG0rcBswagDso451nMsRNrlOUaqwPB445IX4qNYkEEVQA0HOwQOgVgAQmW8FmWEYHWVcZlO2nCaqgkvr6UQxoa5q8yqrIAEhBBDJOI3kktAFoqya8s0mDVNr6xDWBcAqwB+ED+iNgQAAVEg8cMOYgaJMdHIZvFwn45wk4mYZltvNQyvC2LyxaQ2FVpcrT8NYsjSyxoO/nlGo3Y14LiMdkGTAPOC0HXF3aCg0ocA7t29m0MIpBTv7jsn5Z5G2bPEIA2zKMd1aBiI+Zwh6396cZnwdrFJNtqSsWD1Q3ABqYVgi5H6JV7OoBDJ5QBajujQ6SWYbUZK5NCppaFyazJpwGgXRZ0xPROkgwArvMUDzEE4AHC7i5r0VULN8wgRT/2wRw+oYxIhno1p7OFZXBBsbK0QyefrYwkdlKDJkxBC10IoEBfvJNS56kdXgiABLQx7IJoZB/R8MQaYEDlINThE8+w973+y4IHKoD6elkoizX6x5rbWdQCTZ4HVxFEBC/ooRKqCEaz5wNxiXOj4sTeiD6gwQk1vKAqDCACHkYR8JGvhomAKEtud43vAExBQgsYRDAi+TD6qnNM7diCz4H+mI3kgxmZMIMKqhKBI+hhFPpzul+OsQV6hYNVbfgFnsvCjp08IxheS0Mcv7UIo87BCjhIrGPJ1I4XUgAcZG+PRvCBDEuMwQRsV0IfUGE1uZ+F1eNJgRYG8YnoGQOlR3TXh+wXZteaReRNasccLwB5dcsEJvggBiW+IIKqHBIQqoiq3F+xBaP9rdGoLgsTI9xocFeuHVQIwAWIZPsJbuQewqAEFzz+UBULNAEQr9iG3OszikFsQQmmdVkd+hLxALwyEQFQ8vjaEVMNaKn6XdpIPXwBCSyAtycbAAWDEAsO53m8kFB3ZlslUBYVlUAK1A4T0wH35xgwQQ+94AhVcGw8sQFTQAixkEie5xfhQARpkFEBwAQz1jvs0AQB0AFwgn9gUSTykAuKIAUSUBUgYAWIIAsqFoLzcAsqQgRwoAm/kIL8wg6rEwKFAoN3USTvkAuG4AQ32BMgkAWLcAs9OHKvoCZVwQI7R1RJEAAisIQbUiTrUAuFsATv0RMlsAWMgAvcNnLPIAlioANtEQFiojbqoGcjACpMiDIwcQ6zIAhIACY9kQL+XRAreeh03rCIaoMOYGUCfigs/1Ak5AALfmAEhsgTLOAFknCAPsgv1bAIPhIAKFAuf1iGG9EP4NAKewAEOcQTLoBlv5BeoRgi3TAJAbUTDeAGxBAglBhnG8EP3LAKdtADscgqMXAGlQAMe3eLliEOmqBnKlIGu9A+qRiMlTiM2mAKdJAD8EYANJAGl6Bz0NgX5vAJTyAhBIAFs0A32vhTw4gNpCAHNgBvBlADbKAJxvCFm8QOqqAF4uEEq6A0kRePPbYR+zANoOBuVIYDbbAJn2Zn7RALYsAZO2EEoICKB4mQeKWQGWd0VJZsy+aPYxIPuMAGRpMDmeCHHemR1GS+ds5QdGuHL/JGb7DXJMBQB7H1ApBwfy8Jkz1kdsiQCWWAAh33cSGHI8fwB3+yEyZgCD9XN0LJHrhXDJYABpjXE0m3dE3HGtIwCKXYgn5waVRZlVwCE/ege7zHdm4Hd5UBaduzExJAB8jwIgmBlggJE/YgDJHABf/HExGgeaigDJVgW7yoBsKQIXrZmNuoEfvnCFigARZlAGCQC9jomJppcRshD72gCFXQAVUAC/C4maaZaUWSl6e5mgkZlBYREAA7'

    let startPosition;
    const styles = {
        saveBtn:{
            'cursor': 'pointer',
            'color': '#fff',
            'background-color': '#337ab7',
            'border-color': '#2e6da4',
            'padding': '6px 12px',
            'font-size': '14px',
            'font-weight': '400',
            'line-height': '1.42857143',
            'border-radius': '4px'
        },
        resetBtn:{
            'cursor': 'pointer',
            'color': '#333',
            'background-color': '#fff',
            'border-color': '#CCCCCC',
            'padding': '6px 12px',
            'font-size': '14px',
            'font-weight': '400',
            'line-height': '1.42857143',
            'border-radius': '4px'
        },
        img:{
            'position':'absolute',
            'min-width': '90px',
            'max-width': '180px' ,
            'width': '10%',
        }
    }
    let containerInfo = canvasContainer.getBoundingClientRect();

    // setting up tools
        let signHereImg = cTag('img',{'src':imgData});
        setStyles(signHereImg,styles.img)
    canvasContainer.appendChild(signHereImg);

        let saveBtn = cTag('input',{'type':'button','value':'Save'});
        setStyles(saveBtn,styles.saveBtn);
        saveBtn.addEventListener('click',()=>{
            saveSignature(canvas.toDataURL('image/png'));
        });
    toolsContainer.appendChild(saveBtn);
        let resetBtn = cTag('input',{'type':'button','value':'Reset','id':'resetCanvas'});
        setStyles(resetBtn,styles.resetBtn);
        resetBtn.addEventListener('click',()=>{
            if(signHereImg.style.display === 'none'){
                signHereImg.style.display = '';
            }            
            drawingObj.clearRect(0, 0, containerInfo.width, containerInfo.height);
            drawingObj.beginPath();
        })
    toolsContainer.appendChild(resetBtn);

    // setting up canvas
    let canvas = cTag('canvas',{'id':'signatureCanvas'});
    canvas.innerHTML = 'signature is not supported'
    canvas.height = containerInfo.height;
    canvas.width = containerInfo.width;
    setStyles(canvas,styles.canvas);
    canvasContainer.appendChild(canvas);

    let drawingObj = canvas.getContext('2d');
    drawingObj.lineWidth = 2;


    let points = [];

    //making the canvas drawable attaching mousemove event followed by mousedown event
    canvas.addEventListener('mousedown',function(event){
        if(signHereImg.style.display !== 'none'){
            signHereImg.style.display = 'none';
        } 
        // initialize the starting position first 
        startPosition = {
            x:event.offsetX,
            y:event.offsetY,
        };
        points.push({x:event.offsetX,y:event.offsetY});

        drawingObj.fillRect(startPosition.x,startPosition.y,2,2);
        this.addEventListener('mousemove',draw);
    });
    // stop drawing when the mouse button is realesed or leave the canvas area
    canvas.addEventListener('mouseup',function(){
        this.removeEventListener('mousemove',draw);
        points = []
    });
    canvas.addEventListener('mouseleave',function(){
        this.removeEventListener('mousemove',draw);
    });
    // support for touch based devices
    canvas.addEventListener('touchstart',function(event){
        if(signHereImg.style.display !== 'none'){
            signHereImg.style.display = 'none';
        } 
        // initialize the starting position first 
        startPosition = {
            x:event.touches[0].clientX-canvasContainer.getBoundingClientRect().left,
            y:event.touches[0].clientY-canvasContainer.getBoundingClientRect().top,
        };
    });
    canvas.addEventListener('touchmove',function(event){
        event.preventDefault();
        event.offsetX = event.touches[0].clientX-canvasContainer.getBoundingClientRect().left,
        event.offsetY = event.touches[0].clientY-canvasContainer.getBoundingClientRect().top;
        draw(event);
    });
    canvasContainer.addEventListener('touchend',()=>{
        points = [];
    });

    // resize the canvas when window is...
    window.addEventListener('resize',()=>{
        let imgData = drawingObj.getImageData(0, 0, containerInfo.width, containerInfo.height);
        containerInfo = canvasContainer.getBoundingClientRect();
        canvas.width = containerInfo.width;
        drawingObj.lineWidth = 2;
        drawingObj.putImageData(imgData, 0, 0);
    })

    function draw(event){ 
        let endPosition;
        points.push({x:event.offsetX,y:event.offsetY});

        if(points.length>=3){
            let lastTwoPosition = points.slice(-2);
            let controllPosition = lastTwoPosition[0];
            endPosition = {
                x: (lastTwoPosition[0].x+lastTwoPosition[1].x)/2,
                y: (lastTwoPosition[0].y+lastTwoPosition[1].y)/2
            }
            drawingObj.beginPath();
            drawingObj.moveTo(startPosition.x, startPosition.y);
            drawingObj.quadraticCurveTo(controllPosition.x, controllPosition.y, endPosition.x, endPosition.y);
            drawingObj.stroke();
            drawingObj.closePath();                    
            // update the last position 
            startPosition = endPosition;
        }
    }

    function setStyles(node,stylesObj){
        for (const property in stylesObj) {
            node.style[property] = stylesObj[property];
        }
    }
}

async function saveSignature(note){                                
    if(note !== ""){
        const jsonData = {
            'table_id':table_id,
            'for_table':for_table,
            'note':note
        };
        const url = '/Common/AJsave_digitalSignature';

        await fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            if(data.savemsg==='Add'){
                document.querySelector('#resetCanvas').click();
                triggerEvent('filter',{},window.opener);
                window.close();
            }
            else{
                let signatureColumn = cTag('div',{ 'class':`columnXS12` });
                    let signatureCallout = cTag('div',{ 'class':`innerContainer error_msg` });
    
                if(data.returnStr=='errorOnAdding'){
                    signatureCallout.innerHTML = Translate('Error occured while adding new digital signature! Please try again.');
                }
                else if(data.returnStr=='errorOnEditing'){
                    signatureCallout.innerHTML = Translate('Error occured while editing new digital signature! Please try again.');
                }
                else{
                    signatureCallout.innerHTML = Translate('No changes / Error occurred while updating data! Please try again.');
                }
                signatureColumn.appendChild(signatureCallout);
            }
        }
    }
}

function getLabelInfoURL(){
    return window.location.href.replace(/\/barcode|\/label/,'/label_MoreInfo');
}

function generateImeiInfo (parentNode, newimei_info) {
    if(Array.isArray(newimei_info)){
        newimei_info.forEach(imeiItem=>{
            let pTag = cTag ('p',{style: "margin: 0; padding-left: 10px;"});
            pTag.innerHTML = imeiItem;
            parentNode.appendChild(pTag);
        })
    }
}

//
async function getAndPrintLabel(labelFor){
    async function afterFetch(data){
        if(!data.commonInfo.labelSizeMissing){
            if(data.labelsInfo){
                for (const labelItem of data.labelsInfo) {
                    await barcodeLabel({...data.commonInfo,...labelItem},labelItem.item_type==='product'?'productLabel':'deviceLabel',window);
                }
            }
            else await barcodeLabel({...data,...data.commonInfo},labelFor,window);
        }
        else{
            triggerEvent('labelSizeMissing',null,window.opener);
            window.close();
        }
    }
    await fetchData( afterFetch,getLabelInfoURL(),{},'JSON',0 );
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {
        Repairs_prints,Repairs_customer,Repairs_formsprints,Carts_cprints,POS_prints,Orders_prints,Inventory_Transfer_prints,Purchase_orders_prints,
        Accounts_Receivables_prints,Stock_Take_prints,End_of_Day_prints,Admin_printsInvoice,Account_prints,Expenses_prints,Products_prints,IMEI_prints,
    };
    let functionName = `${segment1}_${segment2}`;
    await layoutFunctions[functionName]();
    
    if(![segment5,segment6].includes('signature')){
        setTimeout(()=>{
            window.print();
            setTimeout(()=>{if(OS==='unknown') window.close()}, 100);
        },1000)
    }
});