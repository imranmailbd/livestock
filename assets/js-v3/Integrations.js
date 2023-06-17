import {
    cTag, Translate, tooltip, showTopMessage, setOptions, getDeviceOperatingSystem, AJremove_tableRow, fetchData, actionBtnClick, leftsideHide, copyToClipboardMsg
} from './common.js';

if(segment2==='') segment2 = 'bulkSMS';

function bulkSMS(){
    let link, inputField;
    const showTableData = document.getElementById('viewPageInfo');
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {'style': "padding: 5px; text-align: start;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Manage SMS Messaging')+' ';
                const infoIcon = cTag('i',{ 'class':'fa fa-info-circle', 'style': "font-size: 16px;", 'data-toggle':'tooltip','data-placement':'bottom','title': Translate('This page captures the integrations.') });
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
    showTableData.appendChild(titleRow);
        const leftSideMenuRow = cTag('div',{class:"flexSpaBetRow"});
            const leftSideMenuColumn = cTag('div',{class:"columnMD2 columnSM3", 'style': "margin: 0; padding-right: 10px;"});
                const callOutDiv = cTag('div',{ style:"margin-top:0", class: "innerContainer"});
                    const menuLink = cTag('a',{href:"javascript:void(0)", id:"secondarySideMenu"});
                        const faFont = cTag('i',{class:"fa fa-align-justify", 'style': "font-size: 2em;"});
                    menuLink.appendChild(faFont);
                callOutDiv.appendChild(menuLink);
            leftSideMenuColumn.appendChild(callOutDiv);
        leftSideMenuRow.appendChild(leftSideMenuColumn);
    showTableData.appendChild(leftSideMenuRow)

                const gettingStartedModules = {'bulkSMS':Translate('Manage SMS Messaging')};//'squareup':Translate('Square Credit Card Processing'), 
                const ul = cTag('ul',{class:"secondaryNavMenu settingslefthide"});
                for(const [module, moduletitle] of Object.entries(gettingStartedModules)) { 
                    const li = cTag('li');
                        if(segment2===module){
                            link = cTag('h4');
                            link.innerHTML = moduletitle;
                            li.setAttribute('class', 'activeclass');
                        }
                        else{
                            link = cTag('a',{href:"/Integrations/"+module, title:moduletitle});
                                const span = cTag('span');
                                span.append(moduletitle);
                            link.appendChild(span);
                        }
                    li.appendChild(link);
                ul.appendChild(li);
                }
                callOutDiv.appendChild(ul);
            leftSideMenuColumn.appendChild(callOutDiv);
        leftSideMenuRow.appendChild(leftSideMenuColumn);
    showTableData.appendChild(leftSideMenuRow);

            let bsCallOutStyle = "background:#FFF;"
            if(OS!=='unknown') bsCallOutStyle += "padding-left: 0; padding-right: 0;";
            const bulkSMSColumn = cTag('div',{class:"columnMD10 columnSM9", 'style': "margin: 0; padding-bottom: 4px;"});
                const bsCallOut = cTag('div',{class: "innerContainer", 'style': bsCallOutStyle });
                    const bulkSMSForm = cTag('form',{name:"frmBulkSMS", id:"frmBulkSMS", action:"#", enctype:"multipart/form-data", method:"post", 'accept-charset':"utf-8"});
                    bulkSMSForm.addEventListener('submit',AJsave_bulkSMS);
                        const bulkSMSTrialRow = cTag('div',{class:"flexSpaBetRow"});
                            const bulkSMSTrialColumn = cTag('div',{class:"columnSM12",id: "message"});
                                const p1 = cTag('p');
                                    p1.innerHTML = Translate('SMS marketing is one such cost-effective strategy. With everyone from a youngster to a retiree having their mobile device, SMS marketing is a strategy you might need to consider.');
                                const p2 = cTag('p');
                                    p2.innerHTML = Translate('SMS marketing stands out because of its flexibility. You can use it to thank your customers after purchase to hype them for the new store that opened in their vicinity. You can send them surveys to respond to and products to look forward to.');
                                const p3 = cTag('p');
                                    p3.innerHTML = Translate('Still, as an SMS is more personal than an email or a billboard, you should know its ins and outs before trying your luck and losing customers instead of generating sales.');
                            bulkSMSTrialColumn.append(p1,p2,p3);
                        bulkSMSTrialRow.appendChild(bulkSMSTrialColumn);
                    bulkSMSForm.appendChild(bulkSMSTrialRow);
                        
                        const senderIdRow = cTag('div',{ class:"notuscanada"});
                            const senderIdColumn = cTag('div',{class:"columnSM12"});
                                const pTag = cTag('p');
                                pTag.innerHTML = Translate('The following information needs to send any SMS via Bulk SMS.');
                            senderIdColumn.appendChild(pTag);
                        senderIdRow.appendChild(senderIdColumn);
                    bulkSMSForm.appendChild(senderIdRow);

                        const virtualRow = cTag('div',{ class:"flex"});
                            const virtualColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const virtualLabel = cTag('label',{for:"bulkSMSSenderID", class:'uscanada'});
                                virtualLabel.innerHTML = Translate('Virtual phone number');
                            virtualColumn.appendChild(virtualLabel);
                                const senderLabel = cTag('label',{for:"bulkSMSSenderID", class:'notuscanada'});
                                senderLabel.innerHTML = Translate('Sender ID');
                            virtualColumn.appendChild(senderLabel);
                            const virtualField = cTag('div',{class:"columnSM7 columnMD3"});
                                inputField = cTag('input',{type:"text", id:"bulkSMSSenderID", required: true, name:"bulkSMSSenderID", class:"form-control", maxlength:"16", value:''});
                            virtualField.appendChild(inputField);
                        virtualRow.append(virtualColumn,virtualField);
                    bulkSMSForm.appendChild(virtualRow);

                        const keyRow = cTag('div',{ class:"flex"});
                            const keyColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const keyLabel = cTag('label',{for:"bulkSMSPassword"});
                                keyLabel.innerHTML = Translate('Key/Password');
                            keyColumn.appendChild(keyLabel);
                            const keyValue = cTag('div',{class:"columnSM7 columnMD3"});
                                const keyField = cTag('input',{type:"text", id:"bulkSMSPassword", name:"bulkSMSPassword", required:true, class:"form-control", maxlength:"20", minlength:"8",value:''});
                            keyValue.appendChild(keyField);
                        keyRow.append(keyColumn,keyValue);
                    bulkSMSForm.appendChild(keyRow);

                        const secretRow = cTag('div',{ class:"flex"});
                            const secretColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const secretLabel = cTag('label',{for:"bulkSMSSecretToken"});
                                secretLabel.innerHTML = Translate('Secret/Token');
                            secretColumn.appendChild(secretLabel);
                            const secretValue = cTag('div',{class:"columnSM7 columnMD3"});
                                inputField = cTag('input',{type:"text", id:"bulkSMSSecretToken", name:"bulkSMSSecretToken", required:true, class:"form-control", maxlength:"100", minlength:"16", value:''});
                            secretValue.appendChild(inputField);
                        secretRow.append(secretColumn,secretValue);
                    bulkSMSForm.appendChild(secretRow);

                        const countryCodeRow = cTag('div',{ class:"flex"});
                            const countryCodeColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const countryCodeLabel = cTag('label',{for:"bulkSMSCountryCode"});
                                countryCodeLabel.innerHTML = Translate('Country Code');
                            countryCodeColumn.appendChild(countryCodeLabel);
                            const countryCodeField = cTag('div',{class:"columnSM7 columnMD3"});
                                inputField = cTag('input',{type:"text", id:"bulkSMSCountryCode", name:"bulkSMSCountryCode", required:true, class:"form-control", maxlength:"3", value:''});
                            countryCodeField.appendChild(inputField);
                        countryCodeRow.append(countryCodeColumn,countryCodeField);
                    bulkSMSForm.appendChild(countryCodeRow);

                        let borderBottom = cTag('div',{class: "borderbottom"});
                    bulkSMSForm.append(borderBottom);

                        const invoiceRow = cTag('div',{ class:"flex"});
                            const invoiceColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const invoiceLabel = cTag('label',{for:"bulkSMSinvoice"});
                                invoiceLabel.innerHTML = Translate('Invoice SMS Template');
                                const invoiceP = cTag('span',{style:'font-size:11px;color:#333;line-height:13px !important'});
                                invoiceP.innerHTML = '{{FirstName}}, {{LastName}}, {{InvoiceNumber}}, {{InvoiceDate}}, {{InvoiceTotal}}, {{PaymentTotal}}, {{TotalDues}}, {{InvoiceURL}}, {{CompanyName}}';
                            invoiceColumn.append(invoiceLabel, cTag('br'), invoiceP);
                                const invoiceField = cTag('div',{class:"columnSM7 columnMD5"});
                                inputField = cTag('textarea',{cols:"40", rows:"8", id:"bulkSMSinvoice", name:"bulkSMSinvoice", required:true, class:"form-control"});
                            invoiceField.appendChild(inputField);
                        invoiceRow.append(invoiceColumn,invoiceField);
                    bulkSMSForm.appendChild(invoiceRow);

                        const poRow = cTag('div',{ class:"flex"});
                            const poColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const poLabel = cTag('label',{for:"bulkSMSpo"});
                                poLabel.innerHTML = Translate('PO SMS Template');
                                const poP = cTag('span',{style:'font-size:11px;color:#333;line-height:13px !important'});
                                poP.innerHTML = '{{FirstName}}, {{LastName}}, {{PONumber}}, {{PODateExpected}}, {{POTotal}}, {{PaymentTotal}}, {{TotalDues}}, {{POURL}}, {{CompanyName}}';
                            poColumn.append(poLabel, cTag('br'), poP);
                                const poField = cTag('div',{class:"columnSM7 columnMD5"});
                                inputField = cTag('textarea',{cols:"40", rows:"8", id:"bulkSMSpo", name:"bulkSMSpo", required:true, class:"form-control"});
                            poField.appendChild(inputField);
                        poRow.append(poColumn,poField);
                    bulkSMSForm.appendChild(poRow);

                    //=========For uscanada / notuscanada Start=======//
                        const receiveRepliesRow = cTag('div',{ class:"uscanada"});
                            const receiveRepliesColumn = cTag('div',{class:"columnSM12"});
                                const para = cTag('p');
                                para.innerHTML = Translate('To receive replies to your SMS messages you MUST buy a virtual number from BulkSMS. They are very inexpensive at less than $1 a month. You can get details here')+'<br><a href=\"http://help.bulkSMS.com/hc/en-us/articles/215174158-How-to-add-a-Long-Virtual-Number-LVN-to-your-account\" target=\"_blank\" title=\"How to add a Long Virtual Number LVN to your account\">http://help.bulkSMS.com/hc/en-us/articles/215174158-How-to-add-a-Long-Virtual-Number-LVN-to-your-account</a><br><br>'+Translate('Please enter your virtual phone number here');
                            receiveRepliesColumn.appendChild(para);
                        receiveRepliesRow.appendChild(receiveRepliesColumn);
                    bulkSMSForm.appendChild(receiveRepliesRow);

                        const emailTextRow = cTag('div',{ class:"uscanada"} );
                            const emailTextColumn = cTag('div',{class:"columnSM12"});
                                const emailText = cTag('p');
                                emailText.innerHTML = Translate('You will also need to enter a email address that we will send the replies to that number to');
                            emailTextColumn.appendChild(emailText);
                        emailTextRow.appendChild(emailTextColumn);
                    bulkSMSForm.appendChild(emailTextRow);

                        const emailAddressRow = cTag('div',{ class:"flex uscanada"});
                            const emailColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const emailLabel = cTag('label',{for:"bulkSMSEmail"});
                                emailLabel.innerHTML = Translate('Email Address');
                            emailColumn.appendChild(emailLabel);
                            const emailField = cTag('div',{class:"columnSM7 columnMD3"});
                                inputField = cTag('input',{type:"email", id:"bulkSMSEmail",requred: true, name:"bulkSMSEmail", class:"form-control", maxlength:50, value:''});
                            emailField.appendChild(inputField);
                        emailAddressRow.append(emailColumn,emailField);
                    bulkSMSForm.appendChild(emailAddressRow);

                        const pasteTextRow = cTag('div',{ class:"uscanada"});
                            const pasteTextColumn = cTag('div',{class:"columnSM12"});
                                const pasteText = cTag('p');
                                pasteText.innerHTML = Translate('You will need to paste some text into your BulkSMS account. Log into your BulkSMS account and click the NUMBERS link at the top of the screen.  You will see your virtual number listed there and at the end of the line an EDIT link, click it.  Under the SMS <b>Webhook URL</b> paste in the URL below:');
                            pasteTextColumn.appendChild(pasteText);
                        pasteTextRow.appendChild(pasteTextColumn);
                    bulkSMSForm.appendChild(pasteTextRow);

                        const webHookUrlRow = cTag('div',{ class:"flex uscanada"});
                            const webHookUrlName = cTag('div',{class:"columnSM5 columnMD2"});
                                const webHookUrlLabel = cTag('label',{for:"bulkSMSWebHookURL"});
                                webHookUrlLabel.innerHTML = Translate('Webhook URL');
                            webHookUrlName.appendChild(webHookUrlLabel);
                            const webHookUrlField = cTag('div',{class:"columnSM5 columnMD8"});
                                inputField = cTag('input',{'focus':(event)=>event.target.select(),type:"text",readonly:'', id:"bulkSMSWebHookURL", name:"bulkSMSWebHookURL", class:"form-control", value:''});
                            webHookUrlField.appendChild(inputField);
                            const copyCodeColumn  = cTag('div',{class:"columnSM2"});
                                const copy = cTag('button',{'click':(e)=>{e.preventDefault();copyToClipboardMsg(document.getElementById("bulkSMSWebHookURL"))},class:"btn defaultButton", id:"copyButton"});
                                copy.innerHTML = Translate('Copy Code');
                            copyCodeColumn.appendChild(copy);
                        webHookUrlRow.append(webHookUrlName,webHookUrlField,copyCodeColumn);
                    bulkSMSForm.append(webHookUrlRow);

                        borderBottom = cTag('div',{class: "borderbottom"});
                    bulkSMSForm.append(borderBottom);

                        const deleteZeroRow = cTag('div',{ class:"flexSpaBetRow"});
                            const deleteZeroColumn = cTag('div',{class:"columnSM5 columnMD2"});
                            deleteZeroColumn.append(' ');
                            const deleteZeroField = cTag('div',{class:"columnSM7 columnMD10"});
                                const deleteZeroLabel = cTag('label',{class:"cursor", 'style': "padding-top: 10px;"});
                                    inputField = cTag('input',{ type:"checkbox", id:"leadingZeros", name:"leadingZeros", value:"1", 'style': "margin-right: 10px;"});
                                deleteZeroLabel.append(inputField, Translate('Delete leading zeros in customer phone numbers'));
                            deleteZeroField.appendChild(deleteZeroLabel);
                        deleteZeroRow.append(deleteZeroColumn,deleteZeroField);
                    bulkSMSForm.append(deleteZeroRow);

                        const buttonNames = cTag('div',{ class:"flexStartRow"});
                            const emptyField = cTag('div',{class:"columnSM5 columnMD2"});
                            emptyField.append(' ');
                            const buttonTitle = cTag('div',{class:"columnSM7 columnMD10"});
                                const hiddenInput = cTag('input',{ type:"hidden", name:"variables_id", id:"variables_id", value:0});
                            buttonTitle.appendChild(hiddenInput);
                                const saveButton = cTag('input',{ class:"btn saveButton", name:"btnSubmit", id:"btnSubmit", type:"submit", value:''});
                                const removeButton = cTag('input',{ class:"btn archiveButton", 'style': "margin-left: 10px;", name:"btnRemove", id:"btnRemove", type:"button", value:Translate('Remove')});
                                removeButton.addEventListener('click',removeBulkSMS);
                            buttonTitle.append(saveButton,removeButton);
                        buttonNames.append(emptyField,buttonTitle);
                    bulkSMSForm.append(buttonNames);
                bsCallOut.appendChild(bulkSMSForm);
            bulkSMSColumn.appendChild(bsCallOut);
        leftSideMenuRow.appendChild(bulkSMSColumn);
    showTableData.appendChild(leftSideMenuRow);
    AJ_bulkSMS_MoreInfo();
}

async function AJ_bulkSMS_MoreInfo(){
    const url ='/Integrations/AJ_bulkSMS_MoreInfo/';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        const variables_id = data.variables_id;
        const bulkSMSPassword = data.bulkSMSPassword;
        const bulkSMSSecretToken = data.bulkSMSSecretToken;
        const bulkSMSCountryCode = data.bulkSMSCountryCode;
        const bulkSMSinvoice = data.bulkSMSinvoice;
        const bulkSMSpo = data.bulkSMSpo;
        const company_country_name = data.company_country_name;
        const OUR_DOMAINNAME =  data.OUR_DOMAINNAME;
        const bulkSMSSenderID = data.bulkSMSSenderID;
        const bulkSMSEmail = data.bulkSMSEmail;
        const subdomain = data.subdomain;
        const leadingZeros = data.leadingZeros;

        const message = document.getElementById('message');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnRemove = document.getElementById('btnRemove');


        if(variables_id>0){
            const pTag1 = cTag('p',{ 'style': "font-weight: bold; color: #090; font-size: 20px;"});
            pTag1.innerHTML = Translate('Your SMS Integration is setup.');
            message.parentNode.insertBefore(pTag1, message);
            btnSubmit.value = Translate('Save');
            if(btnRemove.style.display === 'none'){
                btnRemove.style.display = '';
            }
        }
        else{
            btnSubmit.value = Translate('Add');
            if(btnRemove.style.display !== 'none'){
                btnRemove.style.display = 'none';
            }
        }

        if(['Canada','United States'].includes(company_country_name)){
            document.querySelectorAll('.notuscanada').forEach(oneRow=>{
                if(oneRow.style.display !== 'none'){
                    oneRow.style.display = 'none';
                }
            });
            document.querySelectorAll('.uscanada').forEach(oneRow=>{
                if(oneRow.style.display === 'none'){
                    oneRow.style.display = '';
                }
            });
        }   
        else{                
            document.querySelectorAll('.uscanada').forEach(oneRow=>{
                if(oneRow.style.display !== 'none'){
                    oneRow.style.display = 'none';
                }
            });

            document.querySelectorAll('.notuscanada').forEach(oneRow=>{
                if(oneRow.style.display === 'none'){
                    oneRow.style.display = '';
                }
            });
        }

        if(leadingZeros>0){
            document.getElementById('leadingZeros').checked = true;
        }            

        const bulkSMSWebHookURL = 'http://'+ subdomain +'.'+OUR_DOMAINNAME+'/BulkSMS/replySMS';

        document.getElementById('bulkSMSPassword').value= bulkSMSPassword;
        document.getElementById('bulkSMSSecretToken').value = bulkSMSSecretToken;
        document.getElementById('bulkSMSCountryCode').value = bulkSMSCountryCode;
        document.getElementById('bulkSMSinvoice').value = bulkSMSinvoice;
        document.getElementById('bulkSMSpo').value = bulkSMSpo;
        document.getElementById('bulkSMSSenderID').value = bulkSMSSenderID;
        document.getElementById('bulkSMSEmail').value = bulkSMSEmail;
        document.getElementById('bulkSMSWebHookURL').value = bulkSMSWebHookURL;
        document.getElementById('variables_id').value = variables_id;
    }
}

async function AJsave_bulkSMS(event){
    if(event){event.preventDefault();}

    const variables_id = document.getElementById('variables_id').value;
    const bulkSMSPassword = document.getElementById('bulkSMSPassword').value;
    let leadingZeros = 0;
    if(document.getElementById('leadingZeros').checked){leadingZeros = 1;}
    const bulkSMSSecretToken = document.getElementById('bulkSMSSecretToken').value;
    const bulkSMSCountryCode = document.getElementById('bulkSMSCountryCode').value;
    const bulkSMSinvoice = document.getElementById('bulkSMSinvoice').value;
    const bulkSMSpo = document.getElementById('bulkSMSpo').value;
    const bulkSMSSenderID = document.getElementById('bulkSMSSenderID').value;
    const bulkSMSEmail = document.getElementById('bulkSMSEmail').value;
    actionBtnClick('#btnSubmit', Translate('Saving'), 1);
    const jsonData = {};
    jsonData['variables_id'] = variables_id;
    jsonData['leadingZeros'] = leadingZeros;
    jsonData['bulkSMSPassword'] = bulkSMSPassword;
    jsonData['bulkSMSSecretToken'] = bulkSMSSecretToken;
    jsonData['bulkSMSCountryCode'] = bulkSMSCountryCode;
    jsonData['bulkSMSinvoice'] = bulkSMSinvoice;
    jsonData['bulkSMSpo'] = bulkSMSpo;
    jsonData['bulkSMSSenderID'] = bulkSMSSenderID;
    jsonData['bulkSMSEmail'] = bulkSMSEmail;
    
    const url ='/Integrations/AJsave_bulkSMS/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg === 'error'){
            showTopMessage('alert_msg', Translate('Error occured while saving SMS Integration information! Please try again.'));
        }
        else{
            if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
            document.getElementById('btnRemove').style.display = '';                             
            actionBtnClick('#btnSubmit', Translate('Save'), 0);
        }
    }
}

function removeBulkSMS(){
	const variables_id = document.getElementById("variables_id").value;
	AJremove_tableRow('variables', variables_id, 'BulkSMS', '/Integrations/bulkSMS');
}

function squareup(){
    const currencyOptions = [
        'AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN',
        'BAM','BBD','BDT','BGN','BHD','BIF','BMD','BND','BOB','BOV',
        'BRL','BSD','BTN','BWP','BYR','BZD','CAD','CDF','CHE','CHF',
        'CHW','CLF','CLP','CNY','COP','COU','CRC','CUC','CUP','CVE',
        'CZK','DJF','DKK','DOP','DZD','EGP','ERN','ETB','EUR','FJD',
        'FKP','GBP','GEL','GHS','GIP','GMD','GNF','GTQ','GYD','HKD',
        'HNL','HRK','HTG','HUF','IDR','ILS','INR','IQD','IRR','ISK',
        'JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KWD',
        'KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','LYD',
        'MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MUR','MVR',
        'MWK','MXN','MXV','MYR','MZN','NAD','NGN','NIO','NOK','NPR',
        'NZD','OMR','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR',
        'RON','RSD','RUB','RWF','SAR','SBD','SCR','SDG','SEK','SGD',
        'SHP','SLL','SOS','SRD','SSP','STD','SVC','SYP','SZL','THB',
        'TJS','TMT','TND','TOP','TRY','TTD','TWD','TZS','UAH','UGX',
        'USD','USN','USS','UYI','UYU','UZS','VEF','VND','VUV','WST',
        'XAF','XAG','XAU','XBA','XBB','XBC','XBD','XCD','XDR','XOF',
        'XPD','XPF','XPT','XTS','XXX','YER','ZAR','ZMK','ZMW'
    ]

    let link;
    const showTableData = document.getElementById('viewPageInfo');
    showTableData.innerHTML = '';
        const titleRow = cTag('div', {'style': "padding: 5px; text-align: start;"});
            const headerTitle = cTag('h2');
            headerTitle.innerHTML = Translate('Square Credit Card Processing')+' ';
                const infoIcon = cTag('i',{ 'class':'fa fa-info-circle', 'style': "font-size: 16px;", 'data-toggle':'tooltip','data-placement':'bottom','title': Translate('This page captures the integrations.') });
            headerTitle.appendChild(infoIcon);
        titleRow.appendChild(headerTitle);
    showTableData.appendChild(titleRow);

        const leftSideMenuRow = cTag('div',{class:"flex"});
            const leftSideMenuColumn = cTag('div',{class:"columnMD2 columnSM3", 'style': "margin: 0; padding-right: 10px;"});
                const callOutDiv = cTag('div',{ style:"margin-top:0", class: "innerContainer"});
                    const menuLink = cTag('a',{href:"javascript:void(0)", id:"secondarySideMenu"});
                        const faFont = cTag('i',{class:"fa fa-align-justify", 'style': "font-size: 2em;"});
                    menuLink.appendChild(faFont);
                callOutDiv.appendChild(menuLink);
            leftSideMenuColumn.appendChild(callOutDiv);
        leftSideMenuRow.appendChild(leftSideMenuColumn);
    showTableData.appendChild(leftSideMenuRow)

                const gettingStartedModules = {'bulkSMS':Translate('Manage SMS Messaging')}    //'squareup':Translate('Square Credit Card Processing'), 
                const ul = cTag('ul',{class:"secondaryNavMenu settingslefthide"});
                for(const [module, moduletitle] of Object.entries(gettingStartedModules)) {
                    const li = cTag('li');
                        if(segment2===module){
                            link = cTag('h4');
                            link.innerHTML = moduletitle;
                            li.setAttribute('class', 'activeclass');
                        }
                        else{
                            link = cTag('a',{href:"/Integrations/"+module, title:moduletitle});
                                const span = cTag('span');
                                span.append(moduletitle);
                            link.appendChild(span);
                        }
                    li.appendChild(link);
                ul.appendChild(li);
                }
                callOutDiv.appendChild(ul);
            leftSideMenuColumn.appendChild(callOutDiv);
        leftSideMenuRow.appendChild(leftSideMenuColumn);
    showTableData.appendChild(leftSideMenuRow);

            let bsCallOutStyle = "background:#FFF;"
            if(OS!=='unknown') bsCallOutStyle += "padding-left: 0; padding-right: 0;";
            const squareUpColumn = cTag('div',{class:"columnMD10 columnSM9", 'style': "margin: 0;"});
                const bsCallOut = cTag('div',{class: "innerContainer", 'style': bsCallOutStyle});
                    const squareUpForm = cTag('form',{name:"frmSquareup", id:"frmSquareup", action:"#", enctype:"multipart/form-data", method:"post", 'accept-charset':"utf-8"});
                    squareUpForm.addEventListener('submit',AJsave_squareup);
                        let squareCreditCardText = cTag('div',{class:"columnSM12",id: "massage1"});
                    squareUpForm.appendChild(squareCreditCardText);
                        const noneDeviceMsg = cTag('div',{class:"None_Device_Message", 'style': "display: none;"});
                            const noneDeviceMsgColumn = cTag('div',{ class:"columnSM12 roundborder", style:"background:#f4f4f4; padding:40px 30px 30px;"});
                                const noneDeviceMsgField = cTag('b');
                                noneDeviceMsgField.innerHTML = nl2br(Translate('You do not appear to be using a TABLET or Phone at this time. You can only integrate Square if you are on a TABLET or Phone with the APP installed from the instructions above.<br><br>If you are on a tablet please contact us.'),'','');
                            noneDeviceMsgColumn.appendChild(noneDeviceMsgField);
                        noneDeviceMsg.appendChild(noneDeviceMsgColumn);
                    squareUpForm.appendChild(noneDeviceMsg);
                        const deviceMsgRow = cTag('div',{class:"flex Device_Message"});
                            const deviceMsgColumn = cTag('div',{class:"columnSM5 columnMD2"});
                                const currencyLabel = cTag('div',{for:"sqrup_currency_code"});
                                currencyLabel.innerHTML = Translate('Currency Code');
                            deviceMsgColumn.appendChild(currencyLabel);
                        deviceMsgRow.appendChild(deviceMsgColumn);
                            const deviceMsgValue = cTag('div',{class:"columnSM7 columnMD3"});
                                const selectCurrency = cTag('select',{id:"sqrup_currency_code", name:"sqrup_currency_code", class:"form-control", maxlength:"3"});
                                    const currencyOption = cTag('option',{value:''});
                                    currencyOption.innerHTML = '';
                                selectCurrency.appendChild(currencyOption);
                                setOptions(selectCurrency,currencyOptions, 0, 0);
                            deviceMsgValue.appendChild(selectCurrency);
                        deviceMsgRow.appendChild(deviceMsgValue);
                    squareUpForm.appendChild(deviceMsgRow);
                        const amountMessageRow = cTag('div',{class:"Device_Message"});
                            const amountMessageColumn = cTag('div',{class:"columnSM12"});
                                const pTag = cTag('p');
                                pTag.innerHTML = Translate('When you enter the amount to process and select Squareup and click +Payment in our software and it will start the Square App and enter the amount to process.  Process the customers card in the Square App and when it completes you will be taken back to our software and that amount (if accepted) will be added to the payments.');
                            amountMessageColumn.appendChild(pTag);
                        amountMessageRow.appendChild(amountMessageColumn);
                    squareUpForm.appendChild(amountMessageRow);
                        const lastSegmentRow = cTag('div',{class:"flex"});
                            const emptyColumn = cTag('div',{class:"columnSM5 columnMD2"});
                            emptyColumn.append(' ');
                            const buttonName = cTag('div',{class:"columnSM7 columnMD10"});
                                const inputField = cTag('input',{type:"hidden", name:"variables_id", id:"variables_id", value:"0"});
                                const submitButton = cTag('input',{name:"btnSubmit", id:"btnSubmit", type:"submit", value:''});
                            buttonName.append(inputField,submitButton);
                        lastSegmentRow.append(emptyColumn,buttonName);
                    squareUpForm.appendChild(lastSegmentRow);
                    squareUpForm.append(window.navigator.userAgent);
                bsCallOut.appendChild(squareUpForm);
            squareUpColumn.appendChild(bsCallOut);
        leftSideMenuRow.appendChild(squareUpColumn);
    showTableData.appendChild(leftSideMenuRow);
    AJ_squareup_MoreInfo();
}

async function AJ_squareup_MoreInfo(){
    const url ='/Integrations/AJ_squareup_MoreInfo/';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        let p1;
        const variables_id = data.variables_id;
        let saveBtn = Translate('Save');
        let saveBtnCls = ' saveButton';
        const message1 = document.getElementById('massage1');
        if(variables_id>0){
            p1 = cTag('p',{'style': "font-weight: bold; color: #090; font-size: 20px;"});
            p1.innerHTML = Translate('Your Squareup Integration is setup.');
            message1.appendChild(p1);
            saveBtn = Translate('Remove Integration:');
            saveBtnCls = ' archiveButton';
        }

            p1 = cTag('p');
            p1.innerHTML = Translate('<strong>Square credit card processing ONLY works on mobile devices</strong> (not computers).<br><br>If you have an account with Square you can use that or you can get details and create one at')+' <a href=\"http://squareup.com/\" target=\"_blank\" title=\"www.squareup.com\">http://www.squareup.com/</a>';
            const p2 = cTag('p');
            p2.innerHTML = Translate('Once you have signed up you will need to download their POS APP from the links below');
            const p3 = cTag('p');
            p3.innerHTML = Translate('If you have an iOS device you download the app here')+'<br><a href=\"http://itunes.apple.com/us/app/square-register-point-sale/id335393788?mt=8\" target=\"_blank\" title=\"download the iOS device Apps\">http://itunes.apple.com/us/app/square-register-point-sale/id335393788?mt=8</a>';
            const p4 = cTag('p');
            p4.innerHTML = Translate('If you have an Android device you download the app here')+'<br><a href=\"http://play.google.com/store/apps/details?id=com.squareup\" target=\"_blank\" title=\"download the Android device Apps\">http://play.google.com/store/apps/details?id=com.squareup</a>';
            const p5 = cTag('p');
            p5.innerHTML = Translate('You can use their swipe or chip card reader with their APP.  You can find more details here');
            p5.append(cTag('br'));
                const A1 = cTag('a',{href:"http://squareup.com/shop/hardware/us/en/products/chip-credit-card-reader", target:"_blank", title:"download the chip credit card reader"});
                A1.append('http://squareup.com/shop/hardware/us/en/products/chip-credit-card-reader',cTag('br'));
                const A2 = cTag('a',{href:"http://squareup.com/shop/hardware/us/en/products/free-credit-card-reader", target:"_blank", title:"download the chip credit card reader"});
                A2.append('http://squareup.com/shop/hardware/us/en/products/free-credit-card-reader');
            p5.append(A1,A2);
            const p6 = cTag('p');
            p6.innerHTML = Translate('We suggest that you first log into your Square APP you just installed and look it over and even run a couple of transactions before you use it with our software.  Also, go through the settings page and update as needed.');
            const p7 = cTag('p');
            p7.innerHTML = Translate('Once you are familiar with the Square APP then leave that APP open and open our software in a Browser and update the question below once you save it then when you look at your payment options you will find a new option for')+' "Squareup".';
        message1.append(p1,p2,p3,p4,p5,p6,p7);

        document.getElementById('sqrup_currency_code').value = data.sqrup_currency_code;

        document.getElementById('variables_id').value = variables_id;
        const btn = document.getElementById('btnSubmit');
        btn.value = saveBtn;
        btn.setAttribute('class','btn'+saveBtnCls);           

        if(variables_id===0){
            const deviceOpSy = getDeviceOperatingSystem();
            
            if (deviceOpSy==='unknown') {
                if(document.querySelector("#btnSubmit").style.display !== 'none'){
                    document.querySelector("#btnSubmit").style.display = 'none';
                }
                document.querySelectorAll('.Device_Message').forEach(oneRowObj=>{
                    if(oneRowObj.style.display !== 'none'){
                        oneRowObj.style.display = 'none';
                    }
                });
                document.querySelectorAll('.None_Device_Message').forEach(oneRowObj=>{
                    if(oneRowObj.style.display === 'none'){
                        oneRowObj.style.display = '';
                    }
                });
            }
            else{
                if(document.querySelector("#btnSubmit").style.display === 'none'){
                    document.querySelector("#btnSubmit").style.display = '';
                }
                document.querySelectorAll('.Device_Message').forEach(oneRowObj=>{
                    if(oneRowObj.style.display === 'none'){
                        oneRowObj.style.display = '';
                    }
                });
                document.querySelectorAll('.None_Device_Message').forEach(oneRowObj=>{
                    if(oneRowObj.style.display !== 'none'){
                        oneRowObj.style.display = 'none';
                    }
                });
            }
        }
    }
}

async function AJsave_squareup(event=false){
    if(event){event.preventDefault();}
    
    const variables_id = document.getElementById('variables_id').value;
    let sqrup_currency_code = document.getElementById('sqrup_currency_code').value;
    const btnSubmit = document.getElementById('btnSubmit');
    if(variables_id>0){
        sqrup_currency_code = '';
        btnSubmit.value = Translate('Removing')+'... '
        btnSubmit.disabled = true;
    }
    else{
        btnSubmit.value = Translate('Saving')+'... '
        btnSubmit.disabled = true;
    }
    
    const jsonData = {};
    jsonData['variables_id'] = variables_id;
    jsonData['sqrup_currency_code'] = sqrup_currency_code;

    const url ='/Integrations/AJsave_squareup/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg==='error'){
            showTopMessage('alert_msg',Translate('Error occured while saving Square Credit Card Processing information! Please try again.'));
        }
        else{
            if(data.savemsg === 'insert-success') showTopMessage('success_msg',Translate('Inserted successfully.'));
            else if(data.savemsg === 'update-success') showTopMessage('success_msg',Translate('Updated successfully.'));
            location.reload();
        }
    }
}

function nl2br (str, replaceMode, isXhtml) {
    const breakTag = (isXhtml) ? '<br />' : '<br>';
    const replaceStr = (replaceMode) ? '$1'+ breakTag : '$1'+ breakTag +'$2';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, replaceStr);
}

document.addEventListener('DOMContentLoaded', async()=>{    
    let layoutFunctions = {squareup,bulkSMS};
    layoutFunctions[segment2]();
            
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    leftsideHide("secondarySideMenu",'secondaryNavMenu');
});