import {
	cTag, Translate, tooltip, DBDateToViewDate, ViewDateToDBDate, alert_dialog, showTopMessage, setOptions, upload_dialog, 
	copyToClipboardMsg, popup_dialog600, date_picker, sanitizer, fetchData, addCustomeEventListener, callPlaceholder, 
    AJremove_Picture, serialize, multiSelectAction
} from './common.js';

if(segment2==='') segment2 = 'lists';

const fonFamOpts = ['Arial','Times New Roman','Verdana','Garamond','Comic Sans MS','Trebuchet MS','Arial Black','Impact', 'Cambria'];

const webPageOpts = {'lists': Translate('Please Select Page'), 'all_pages_header':Translate('All pages header'), 'home_page_body' : Translate('Home Page Body'), 
'all_pages_footer': Translate('All pages footer'), 'ContactUs': Translate('Contact Us'), 'Customer':Translate('Add New Customer'), 'services':Translate('Services'), 'products':Translate('Products'),
'cell_phones':Translate('Mobile Devices'), 'Quote':Translate('Request a Quote'), 'Appointment': Translate('Repair Appointment'), 'RStatus': Translate('Check Repair Status'),'preview': Translate('Preview Website')};

async function update_instance_home(instance_home_id, fieldname){
	const fieldchecked = document.getElementById(fieldname).checked;
	let fieldval = 0;
	if(fieldchecked === true){fieldval = 1;}

	const jsonData = {};
	jsonData['instance_home_id'] = instance_home_id;
	jsonData['fieldname'] = fieldname;
	jsonData['fieldval'] = fieldval;
	
    const url = '/'+segment1+'/update_instance_home';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.returnStr>0){showTopMessage('success_msg', Translate('Updated successfully.'));}
		else{showTopMessage('alert_msg', Translate('Could not update'));}
	
		if(['display_services', 'display_services_prices', 'enable_services_paypal'].includes(fieldname)){
			checkServicesChecks();
		}
		else if(['display_products', 'display_products_prices', 'enable_product_paypal'].includes(fieldname)){
			checkProductChecks();
		}
		else if(['display_inventory', 'display_cell_prices', 'enable_cell_paypal'].includes(fieldname)){
			checkCellChecks();
		}
		else if(fieldname==='website_on'){
			checkWebsiteEnable(fieldval);
		}
	}
}

async function checkWebsiteEnable(website_on){
	let pTag;
	const websiteStatusObj = document.getElementById("websiteStatus");
	websiteStatusObj.innerHTML = '';
	
	if(website_on===0){
		if(websiteStatusObj){
				pTag = cTag('p',{ 'style': "font-weight: bold; font-size: 20px;" });
				pTag.innerHTML = Translate('Customer-Facing Website') + ' ';
					let disabledSpan = cTag('span',{ 'style': "color: #F00;", });
					disabledSpan.innerHTML = Translate('DISABLED');
				pTag.appendChild(disabledSpan);
			websiteStatusObj.appendChild(pTag);

				pTag = cTag('p');
				pTag.innerHTML = Translate('Enable your customer-facing website by clicking the button below. Your customer-facing website allows you to quickly build a web presence to communicate your service offerings, locations and hours of business. Features include: sharing your devices inventory, accepting repair quotes, and checking repair statuses online.');
			websiteStatusObj.appendChild(pTag);

				pTag = cTag('p');
				pTag.innerHTML = Translate('Once enabled your customers can visit your website at')+' ';
					const strong = cTag('strong');
					strong.innerHTML = window.location.hostname;
				pTag.appendChild(strong);
			websiteStatusObj.appendChild(pTag);
		}
		document.querySelectorAll(".websitePage").forEach(oneClas=>{
			if(oneClas.style.display !== 'none'){
				oneClas.style.display = 'none';
			}
		});
	}
	else{
		if(websiteStatusObj){
				pTag = cTag('p',{ 'style': "font-weight: bold; font-size: 20px;" });
				pTag.innerHTML = Translate('Customer-Facing Website') + ' ';
					let enabledSpan = cTag('span',{ 'style': "color: #090;" });
					enabledSpan.innerHTML = Translate('ENABLED');
				pTag.appendChild(enabledSpan);
			websiteStatusObj.appendChild(pTag);

				pTag = cTag('p');
				pTag.innerHTML = Translate('Disable your customer-facing website by clicking the button below. By disabling your website you will be removing your customers ability to view your service offerings, location, hours of business, and other details about your business.');
			websiteStatusObj.appendChild(pTag);

				pTag = cTag('p');
				pTag.innerHTML = Translate('This will NOT affect your ability to access your internal COMPANYNAME accounts.');
			websiteStatusObj.appendChild(pTag);

				pTag = cTag('p');
				pTag.innerHTML = Translate('Your customers can visit your website at') + ' ';
					let aTag = cTag('a',{ 'style': "font-weight: bold; font-size: 20px;", 'target':'_blank','href':'//'+window.location.hostname,'title':window.location.hostname });
					aTag.innerHTML = window.location.hostname;
				pTag.appendChild(aTag);
			websiteStatusObj.appendChild(pTag);
		}
		document.querySelectorAll(".websitePage").forEach(oneClas=>{
			if(oneClas.style.display === 'none'){
				oneClas.style.display = '';
			}
		});
	}
}

function showWebsitePage(){
	const websitePage = document.getElementById("websitePage").value;
	if(websitePage==='preview'){
		let fullURL = window.location.href;
		let fullURL_split = fullURL.split('/');
		let preview_url = fullURL_split[0]+'//'+fullURL_split[2];
		window.open(preview_url, '_blank');
	}
	else{
		window.location = '/Website/'+websitePage;
	}
}

function viewPage(uri){
	if(uri==='preview'){
		window.open(window.location.origin, '_blank');
	}
	else{
		window.location = '/Website/'+uri;
	}
}

function header(label){
    const showTableData = document.getElementById("viewPageInfo");
    showTableData.innerHTML = '';
		const titleRow = cTag('div',{ "class":"flexSpaBetRow", 'style': "align-items: center;" });
			let titleColumn = cTag('div',{ "class":"columnXS12 columnSM5 columnMD7"});
				const titleHeader = cTag('h2',{ 'style': "text-align: start;" });
					let labelSpan = cTag('span',{ "id":"ptitle" });
					labelSpan.innerHTML = label+' ';
				titleHeader.appendChild(labelSpan);
				titleHeader.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":label }));
			titleColumn.appendChild(titleHeader);
		titleRow.appendChild(titleColumn);
			let manageColumn = cTag('div',{ "class":"columnXS5 columnSM3 columnMD2 websitePage" });
				let websiteLabel = cTag('label',{ "for":"websitePage" });
				websiteLabel.innerHTML = Translate('Manage Website') +' :';
			manageColumn.appendChild(websiteLabel);
		titleRow.appendChild(manageColumn);
			let websitePageColumn = cTag('div',{ "class":"columnXS7 columnSM4 columnMD3 websitePage"});
				let selectWebsite = cTag('select',{ "name":"websitePage","id":"websitePage","class":"form-control","change":showWebsitePage });
				setOptions(selectWebsite, webPageOpts, 1, 0);
			websitePageColumn.appendChild(selectWebsite);
		titleRow.appendChild(websitePageColumn);
	showTableData.appendChild(titleRow);
}

function color_font_controller({bgcLabel = Translate('Background Color'),colSize=[4,4],ids=['bg_color','color','font_family'],freeSpace=true,customer=false,quote=false,appointment=false,rsStatus=false}){
	let info_quote;
	if(customer) info_quote = {label:Translate('Display Add Customer Information'),id:'display_add_customer'};
	if(quote) info_quote = {label:Translate('Request a Quote'),id:'request_a_quote'};
	if(appointment) info_quote = {label:Translate('Repair Appointment'),id:'mobile_repair_appointment'};
	if(rsStatus) info_quote = {label:Translate('Check Repair Status Online'),id:'repair_status_online'};

	let frmDivCol, label, inputField;
	const formRow = cTag('div',{ 'class':'flexStartRow' });
	if(customer || quote || appointment || rsStatus){
			frmDivCol = cTag('div',{ "class":"columnSM3" });
				let quoteHeader = cTag('h4',{ "class":"borderbottom", 'style': "font-size: 18px;" });
				quoteHeader.innerHTML = info_quote.label;
			frmDivCol.appendChild(quoteHeader);
				label = cTag('label',{ "for":info_quote.id });
				label.appendChild(cTag('input',{ "type":"checkbox","value":"1","id":info_quote.id,"name":info_quote.id,"click": ()=>update_instance_home(1, info_quote.id)}));
				label.append(' '+Translate('Enable'));
			frmDivCol.appendChild(label);
		formRow.appendChild(frmDivCol);
	}
		frmDivCol = cTag('div',{ 'class':'columnXS12 columnSM'+colSize[0] });
			let bgcHeader = cTag('h4',{ 'class':'borderbottom', 'style': "font-size: 18px;" });
			bgcHeader.innerHTML = bgcLabel;
		frmDivCol.appendChild(bgcHeader);
			let colorRow = cTag('div',{ 'class':'columnSM12' });
				inputField = cTag('input',{ 'style':"width: 100%; height: 100px; border: none",'type':"color",'name':ids[0],'id':ids[0] });
			colorRow.appendChild(inputField);
		frmDivCol.appendChild(colorRow);
	formRow.appendChild(frmDivCol);

		frmDivCol = cTag('div',{ 'class':'columnXS12 columnSM'+colSize[1] });
			let textFontHeader = cTag('h4',{ 'class':'borderbottom', 'style': "font-size: 18px;" });
			textFontHeader.innerHTML = Translate('Text Color & Font Family');
		frmDivCol.appendChild(textFontHeader);
			let textColorRow = cTag('div',{ 'class':'flex', 'style': "align-items: center;" });
				let textColorColumn = cTag('div',{ 'class':'columnXS12 columnSM4' });
					let textColorLabel = cTag('label',{ 'for':ids[1] });
					textColorLabel.innerHTML = Translate('Text Color') + ' :';
				textColorColumn.appendChild(textColorLabel);
			textColorRow.appendChild(textColorColumn);
				let textColorField = cTag('div',{ 'class':'columnXS12 columnSM8' });
					inputField = cTag('input',{ 'style':"width: 100%; height: 30px; border: none",'type':"color",'name':ids[1],'id':ids[1] });
				textColorField.appendChild(inputField);
			textColorRow.appendChild(textColorField);
		frmDivCol.appendChild(textColorRow);
			let fontFamilyRow = cTag('div',{ 'class':'flex', 'style': "padding-top: 15px; align-items: center;" });
				let fontFamilyColumn = cTag('div',{ 'class':'columnXS12 columnSM4' });
					let fontFamilyLabel = cTag('label',{ 'for':ids[2] });
					fontFamilyLabel.innerHTML = Translate('Font Family') + ' :';
				fontFamilyColumn.appendChild(fontFamilyLabel);
			fontFamilyRow.appendChild(fontFamilyColumn);
				let fontFamilyField = cTag('div',{ 'class':'columnXS12 columnSM8' });
					let selectFontFamily = cTag('select',{ 'class':'form-control','name':ids[2],'id':ids[2] });
					setOptions(selectFontFamily, fonFamOpts, 0, 0);                                            
				fontFamilyField.appendChild(selectFontFamily);
			fontFamilyRow.appendChild(fontFamilyField);
		frmDivCol.appendChild(fontFamilyRow);
	formRow.appendChild(frmDivCol);
	return formRow;	
}

function btn_color_font_controller(){
	const divForm = cTag('div',{ 'class':"flexStartRow", 'style': "border-top: 1px solid #CCC; padding-top: 15px;" });
		let buttonDiv = cTag('div',{ 'class':"columnXS12 columnSM3" });
			const buttonHeader = cTag('h4', {'style': "font-size: 18px;"});
			buttonHeader.innerHTML = Translate('Button Background');
		buttonDiv.appendChild(buttonHeader);
	divForm.appendChild(buttonDiv);
		let buttonColorDiv = cTag('div',{ 'class':"columnXS12 columnSM5" });
			let buttonColorField = cTag('input',{ 'style':"width: 100%; height: 80px; border: none",'type':"color",'name':"but_bg_color",'id':"but_bg_color",'value':"#ef7f1b" });
		buttonColorDiv.appendChild(buttonColorField);
	divForm.appendChild(buttonColorDiv);
		let buttonTextColor = cTag('div',{ 'class':"columnXS12 columnSM4" });
			let buttonTextColorRow = cTag('div',{ 'class':"flex", 'style': "align-items: center;" });
				let textColorTitle = cTag('div',{ 'class':"columnXS12 columnSM4" });
					let textColorLabel = cTag('label',{ 'for':"but_color" });
					textColorLabel.innerHTML = Translate('Text Color') + ' :';
				textColorTitle.appendChild(textColorLabel);
			buttonTextColorRow.appendChild(textColorTitle);
				let colorValue = cTag('div',{ 'class':"columnXS12 columnSM8" });
				colorValue.appendChild(cTag('input',{ 'style':"width: 100%; height: 30px; border: none",'type':"color",'name':"but_color",'id':"but_color",'value':"#FFFFFF" }));
			buttonTextColorRow.appendChild(colorValue);
		buttonTextColor.appendChild(buttonTextColorRow);
			let fontFamilyRow = cTag('div',{ 'class':"flex", 'style': "align-items: center; padding-top: 15px;" });
				let fontFamilyTitle = cTag('div',{ 'class':"columnXS12 columnSM4" });
					let fontFamilyLabel = cTag('label',{ 'for':"but_font_family" });
					fontFamilyLabel.innerHTML = Translate('Font Family') + ' :';
				fontFamilyTitle.appendChild(fontFamilyLabel);
			fontFamilyRow.appendChild(fontFamilyTitle);
				let fontFamilyValue = cTag('div',{ 'class':"columnXS12 columnSM8" });
					let selectBtnFontFamily = cTag('select',{ 'class':"form-control",'name':"but_font_family",'id':"but_font_family" });
					setOptions(selectBtnFontFamily, fonFamOpts, 0, 0);                                            
				fontFamilyValue.appendChild(selectBtnFontFamily);
			fontFamilyRow.appendChild(fontFamilyValue);
		buttonTextColor.appendChild(fontFamilyRow);
	divForm.appendChild(buttonTextColor);

	return divForm;
}

function mail_currency_form(){
	const curCode = ['AUD','BRL','CAD','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','NOK','NZD','PHP','PLN','GBP','SGD','SEK','CHF','TWD','THB','USD'];

	const divForm = cTag('div',{ 'class':"columnSM6",'id':"paypalField"});
		const paypalRow = cTag('div',{ 'class':"flex" });
			const paypalTitle = cTag('div',{ 'class':"columnSM4" });
				let paypalLabel = cTag('label',{ 'for':"paypal_email" });
				paypalLabel.innerHTML = Translate('Paypal Email:');
			paypalTitle.appendChild(paypalLabel);
		paypalRow.appendChild(paypalTitle);
			let paypalEmailField = cTag('div',{ 'class':"columnSM8" });
			paypalEmailField.appendChild(cTag('input',{ 'required':"",'type':"email",'name':"paypal_email",'id':"paypal_email",'class':"form-control" }));
		paypalRow.appendChild(paypalEmailField);
	divForm.appendChild(paypalRow);
		const currencyRow = cTag('div',{ 'class':"flex" });
			const currencyTitle = cTag('div',{ 'class':"columnSM4" });
				let currencyLabel = cTag('label',{ 'for':"currency_code" });
				currencyLabel.innerHTML = Translate('Currency Code:');
			currencyTitle.appendChild(currencyLabel);
		currencyRow.appendChild(currencyTitle);
			let currencyDropDown = cTag('div',{ 'class':"columnSM8" });
				let selectCurrency = cTag('select',{ 'required':"",'name':"currency_code",'id':"currency_code",'class':"form-control" });
				setOptions(selectCurrency, curCode, 0, 0);                                            
			currencyDropDown.appendChild(selectCurrency);
		currencyRow.appendChild(currencyDropDown);
	divForm.appendChild(currencyRow);
		const buttonRow = cTag('div',{ 'class':"flex" });
			let emptyDiv = cTag('div',{ 'class':"columnSM4" });
			emptyDiv.innerHTML = ' ';
		buttonRow.appendChild(emptyDiv);
			let submitButton = cTag('div',{ 'class':"columnSM8" });
			submitButton.appendChild(cTag('input',{ 'class':"btn saveButton",'name':"btnSubmit",'id':"btnSubmit",'type':"submit",'value':"Save" }));
		buttonRow.appendChild(submitButton);
	divForm.appendChild(buttonRow);
	return divForm;
}

async function AJsave(save_properties){
	let submitBtn = document.querySelector(save_properties.btn_id);
	submitBtn.value = Translate('Saving') + "...";
	submitBtn.disabled = true;
	
	const jsonData = serialize(save_properties.form_id);	
    const url = '/'+segment1+'/'+save_properties.api_endpoint;
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		submitBtn.value = 'Save';
		submitBtn.disabled = false;

		if(data.returnStr === 'errorOnAdding') showTopMessage('alert_msg', Translate('Error occured while adding forms information! Please try again.'));
		else if(data.savemsg ==='Add')showTopMessage('success_msg', Translate('Inserted successfully.'));
		else if(data.savemsg ==='Update') showTopMessage('success_msg', Translate('Updated successfully.'));
		else showTopMessage('alert_msg', Translate('No changes / Error occurred while updating data! Please try again.'));
	}
}

function lists(){
	let div3Header, pTag, customizeButton;
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Manage Website'));
		let SpaBetRow = cTag("div",{ 'class':'flexSpaBetRow'});
			let divCol6_1 =cTag('div',{ 'class':'columnSM6'});
				let websiteWidget = cTag('div',{ 'class':'cardContainer', 'style': "margin-bottom: 10px;" });
					let widgetsTitle = cTag('div',{ 'class':'cardHeader', 'style': "padding-left: 10px;" });
						let widgetsHeader = cTag('h3');
						widgetsHeader.innerHTML = Translate('Our widgets');
					widgetsTitle.appendChild(widgetsHeader);
				websiteWidget.appendChild(widgetsTitle);
					let widgetContent = cTag('div',{ 'class':'cardContent'});
						pTag = cTag('p');
							let enableLabel = cTag('label',{ 'for':'enable_widget','style':'cursor:pointer' });
							enableLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'enable_widget','name':'enable_widget' }));
							enableLabel.append(' '+Translate('Enable'));
						pTag.appendChild(enableLabel);
					widgetContent.appendChild(pTag);
					widgetContent.appendChild(cTag('hr'));
					widgetContent.appendChild(cTag('p'));
					widgetContent.appendChild(cTag('div',{ 'id': 'widgetScripts' }));
				websiteWidget.appendChild(widgetContent);
			divCol6_1.appendChild(websiteWidget);
		SpaBetRow.appendChild(divCol6_1);

			let websitePartColumn =cTag('div',{ 'class':'columnSM6'});
				let widgetWebsite = cTag('div',{ 'class':'cardContainer', 'style': "margin-bottom: 10px;" });
					div3Header = cTag('div',{ 'class':'cardHeader', 'style': "padding-left: 10px;" });
						let h3Widget = cTag('h3');
						h3Widget.innerHTML = Translate('Website')
					div3Header.appendChild(h3Widget);
				widgetWebsite.appendChild(div3Header);
					let websiteContent = cTag('div',{ 'class':'cardContent', 'style': "padding-left: 10px;" });
						let websiteStatusRow = cTag('div',{ 'class':'flexSpaBetRow' });
						websiteStatusRow.appendChild(cTag('div',{ 'class':'columnSM12','id':'websiteStatus' }));                      
					websiteContent.appendChild(websiteStatusRow);
						let customizeRow = cTag('div',{ 'class':'flexSpaBetRow' });					
							let customizeColumn = cTag('div',{ 'class':'columnSM12' });
								let websiteLabel = cTag('label',{'style':'cursor:pointer'});
								websiteLabel.appendChild(cTag('input',{ 'type':"checkbox",'value':"1",'id':"website_on",'name':"website_on" }));
								websiteLabel.append(' '+Translate('Enable'));
							customizeColumn.appendChild(websiteLabel);
							customizeColumn.appendChild(cTag('hr'));
								let customizeDiv = cTag('div',{ 'class':'columnSM12', 'style': "padding-left: 40px;" });
									let allLabel = cTag('label', {'style': "margin-bottom: 15px;"});
									allLabel.append(Translate('All pages header')+' ');                            
										customizeButton = cTag('button',{ 'class':'btn defaultButton', 'style': "margin-left: 10px;", 'click':()=> viewPage('all_pages_header')});
										customizeButton.append(Translate('Customize'));
									allLabel.appendChild(customizeButton);
								customizeDiv.appendChild(allLabel);
								customizeDiv.appendChild(cTag('br'));

									let homeLabel = cTag('label', {'style': "margin-bottom: 15px;"});
									homeLabel.append(Translate('Home Page Body')+' ');                            
										customizeButton = cTag('button',{ 'class':'btn defaultButton', 'style': "margin-left: 10px;", 'click':()=>viewPage('home_page_body')});
										customizeButton.append(Translate('Customize'));
									homeLabel.appendChild(customizeButton);
								customizeDiv.appendChild(homeLabel);
								customizeDiv.appendChild(cTag('br'));
							
									let footerLabel = cTag('label', {'style': "margin-bottom: 15px;"});
									footerLabel.append(Translate('All pages footer')+' ');                            
										customizeButton = cTag('button',{ 'class':'btn defaultButton', 'style': "margin-left: 10px;", 'click':()=>viewPage('all_pages_footer')});
										customizeButton.append(Translate('Customize'));
									footerLabel.appendChild(customizeButton);
								customizeDiv.appendChild(footerLabel);
								customizeDiv.appendChild(cTag('br'));
							
									let contactLabel = cTag('label', {'style': "margin-bottom: 15px;"});
									contactLabel.append(Translate('Contact Us')+' ');                            
										customizeButton = cTag('button',{ 'class':'btn defaultButton', 'style': "margin-left: 10px;", 'click':()=>viewPage('ContactUs')});
										customizeButton.append(Translate('Customize'));
									contactLabel.appendChild(customizeButton);
								customizeDiv.appendChild(contactLabel);
								customizeDiv.appendChild(cTag('br'));									
								
									let customerLabel = cTag('label',{ 'for':"display_add_customer",'style':'cursor:pointer; margin-bottom: 15px;' });
									customerLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'display_add_customer','name':'display_add_customer' }));
									customerLabel.append(' '+Translate('Display Add Customer Information')+' ');                          
								customizeDiv.appendChild(customerLabel);
									customizeButton = cTag('button',{ 'class':"btn defaultButton", 'style': "margin-left: 10px;", 'click': ()=> viewPage('Customer')});
									customizeButton.append(Translate('Customize'));
								customizeDiv.appendChild(customizeButton);
								customizeDiv.appendChild(cTag('br'));
							
									let displayLabel = cTag('label',{ 'for':"display_services",'style':'cursor:pointer; margin-bottom: 15px;' });
									displayLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'display_services','name':'display_services' }));
									displayLabel.append(' '+Translate('Display Services Link')+' ');
								customizeDiv.appendChild(displayLabel);
									customizeButton = cTag('button',{ 'class':"btn defaultButton", 'style': "margin-left: 10px;", 'click':()=>viewPage('services')});
									customizeButton.append(Translate('Customize'));
								customizeDiv.appendChild(customizeButton);
								customizeDiv.appendChild(cTag('br'));
																	
									let productLabel = cTag('label',{ 'for':"display_products",'style':'cursor:pointer; margin-bottom: 15px;' });												
									productLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'display_products','name':'display_products' }));
									productLabel.append(' '+Translate('Display Products Link')+' ');
								customizeDiv.appendChild(productLabel);
									customizeButton = cTag('button',{ 'class':"btn defaultButton", 'style': "margin-left: 10px;", 'click':()=>viewPage('products')});
									customizeButton.append(Translate('Customize'));
								customizeDiv.appendChild(customizeButton);
								customizeDiv.appendChild(cTag('br'));
							
									let inventoryLabel = cTag('label',{ 'for':"display_inventory",'style':'cursor:pointer; margin-bottom: 15px;' });
									inventoryLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'display_inventory','name':'display_inventory' }));
									inventoryLabel.append(' '+Translate('Display Mobile Devices Link')+' ');
								customizeDiv.appendChild(inventoryLabel);
									customizeButton = cTag('button',{ 'class':"btn defaultButton", 'style': "margin-left: 10px;", 'click':()=>viewPage('cell_phones')});
									customizeButton.append(Translate('Customize'));
								customizeDiv.appendChild(customizeButton);
								customizeDiv.appendChild(cTag('br'));
							
									let quoteLabel = cTag('label',{ 'for':"request_a_quote" });
									quoteLabel.append(' '+Translate('Display Repairs Link')+' ');
								customizeDiv.appendChild(quoteLabel);
								customizeDiv.appendChild(cTag('hr'));
								
									let websiteStatusDiv = cTag('div',{ 'class':'columnSM12', 'style': "padding-left: 40px;" });
										let requestLabel = cTag('label',{ 'for':'request_a_quote','style':'cursor:pointer; margin-bottom: 15px;' });                   
										requestLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'request_a_quote','name':'request_a_quote' })); 
										requestLabel.append(' '+Translate('Request a Quote Link')+' ');
									websiteStatusDiv.appendChild(requestLabel);  
										customizeButton = cTag('button',{ 'class':"btn defaultButton", 'style': "margin-left: 10px;", 'click':()=>viewPage('Quote')});
										customizeButton.append(Translate('Customize'));
									websiteStatusDiv.appendChild(customizeButton);
									websiteStatusDiv.appendChild(cTag('br'));

										let appointmentLabel = cTag('label',{ 'for':'mobile_repair_appointment','style':'cursor:pointer; margin-bottom: 15px;' });                               
										appointmentLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'mobile_repair_appointment','name':'mobile_repair_appointment' })); 
										appointmentLabel.append(' '+Translate('Repair Appointment')+' ');                             
									websiteStatusDiv.appendChild(appointmentLabel);  
										customizeButton = cTag('button',{ 'class':"btn defaultButton", 'style': "margin-left: 10px;", 'click':()=>viewPage('Appointment')});
										customizeButton.append(Translate('Customize'));
									websiteStatusDiv.appendChild(customizeButton);
									websiteStatusDiv.appendChild(cTag('br'));

										let statusLabel = cTag('label',{ 'for':'repair_status_online','style':'cursor:pointer; margin-bottom: 15px;' });                              
										statusLabel.appendChild(cTag('input',{ 'type':'checkbox','value':1,'id':'repair_status_online','name':'repair_status_online' }));
										statusLabel.append(' '+Translate('Check Repair Status Online')+' ');                             
									websiteStatusDiv.appendChild(statusLabel);
										customizeButton = cTag('button',{ 'class':"btn defaultButton", 'style': "margin-left: 10px;", 'click':()=>viewPage('RStatus')});
										customizeButton.append(Translate('Customize'));
									websiteStatusDiv.appendChild(customizeButton);
									websiteStatusDiv.appendChild(cTag('br'));
								customizeDiv.appendChild(websiteStatusDiv);
							customizeColumn.appendChild(customizeDiv);
						customizeRow.appendChild(customizeColumn);
					websiteContent.appendChild(customizeRow);
				widgetWebsite.appendChild(websiteContent);
			websitePartColumn.appendChild(widgetWebsite);
		SpaBetRow.appendChild(websitePartColumn);
    showTableData.appendChild(SpaBetRow);

	AJ_lists_MoreInfo();
}

async function AJ_lists_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_lists_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let instance_home_id = data.instance_home_id;
			
		let enable_widgetObj = document.getElementById("enable_widget");
		enable_widgetObj.addEventListener('click',()=> update_instance_home(instance_home_id, 'enable_widget'));
		if(data.enable_widget>0){enable_widgetObj.checked = true;}
		else{enable_widgetObj.checked = false;}
		
		let widgetScripts = document.getElementById('widgetScripts');
		[
			{id:'repairAPICode',class:'repair_status', title:Translate('Widget for checking Repair Status'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to enter their name and ticket number to check.')},
			{id:'customerAPICode',class:'customer', title:Translate('Widget for adding new Customer'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to enter their information.')},
			{id:'appointmentAPICode',class:'appointment', title:Translate('Widget for adding new Appointment'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to enter their appointment information.')},
			{id:'contactUsWidgetCode',class:'contact_us', title:Translate('Widget for contact us'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to enter their information and send a message to you.')},
			{id:'servicesWidgetCode',class:'services', title:Translate('Widget for checking Services'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to see the services.')},
			{id:'productWidgetCode',class:'product', title:Translate('Widget for checking Products'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to see the products.')},
			{id:'cellPhonesWidgetCode',class:'cellPhones', title:Translate('Widget for checking Mobile Devices'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to see the Mobile Devices.')},
			{id:'quoteWidgetCode',class:'quote', title:Translate('Widget for Requesting Quote'),info:Translate('Just copy the code below and paste it into your HTML where you want your customers to Request for Quote.')},
		].forEach(item=>{
			let errID = item.id+'Msg';
				let header = cTag('h3');
				header.innerHTML = item.title;
			widgetScripts.appendChild(header);
			widgetScripts.appendChild(cTag('hr'));
				let pTag = cTag('p');
				pTag.innerHTML = Translate('If you have your own domain and website you can add the following to your website to allow your customers to check the status of their repair directly on your website.');
			widgetScripts.appendChild(pTag);
				pTag = cTag('p');
				pTag.innerHTML = item.info;
			widgetScripts.appendChild(pTag);
				let divForm = cTag('div',{ 'class':'flexColumn' });
					let scriptSection = cTag('div',{ 'class':'columnSM12'});
						let textarea = cTag('textarea',{ 'readonly':'','rows':'2','cols':'40','id':item.id,'class':'form-control' });
						textarea.innerHTML = `<script type="module" id="CSAPI" class="${item.class}" src="${window.location.origin}/assets/widget.js?${data.embedSubDomain}"></script>`;
						textarea.addEventListener('focus', textarea.select);
					scriptSection.appendChild(textarea);
						let codeMsgTxt = cTag('span', {'style': "color: #090;", id:errID});
					scriptSection.appendChild(codeMsgTxt);
				divForm.appendChild(scriptSection);
					let copyButtonColumn = cTag('div',{ 'class':'columnSM3' });
						let copyCodeButton = cTag('button',{ 'class':'btn defaultButton','id':'copyButton' });
						copyCodeButton.innerHTML = Translate('Copy Code');
						copyCodeButton.addEventListener('click', ()=>{
							copyToClipboardMsg(textarea, errID);
						});
					copyButtonColumn.appendChild(copyCodeButton);
				divForm.appendChild(copyButtonColumn);
			widgetScripts.appendChild(divForm);						
		})


		let  website_onObj = document.getElementById("website_on");
		website_onObj.addEventListener('click',()=> update_instance_home(instance_home_id, 'website_on'));
		if(data.website_on>0){website_onObj.checked = true;}
		else{website_onObj.checked = false;}
		
		let  display_add_customerObj = document.getElementById("display_add_customer");
		display_add_customerObj.addEventListener('click',()=> update_instance_home(instance_home_id, 'display_add_customer'));
		if(data.display_add_customer>0){display_add_customerObj.checked = true;}
		else{display_add_customerObj.checked = false;}
		
		let  display_servicesObj = document.getElementById("display_services");
		display_servicesObj.addEventListener('click', ()=>update_instance_home(instance_home_id, 'display_services'));
		if(data.display_services>0){display_servicesObj.checked = true;}
		else{display_servicesObj.checked = false;}
		
		let  display_productsObj = document.getElementById("display_products");
		display_productsObj.addEventListener('click',()=> update_instance_home(instance_home_id, 'display_products'));
		if(data.display_products>0){display_productsObj.checked = true;}
		else{display_productsObj.checked = false;}
		
		let  display_inventoryObj = document.getElementById("display_inventory");
		display_inventoryObj.addEventListener('click', ()=>update_instance_home(instance_home_id, 'display_inventory'));
		if(data.display_inventory>0){display_inventoryObj.checked = true;}
		else{display_inventoryObj.checked = false;}
		
		let  request_a_quoteObj = document.getElementById("request_a_quote");
		request_a_quoteObj.addEventListener('click',()=> update_instance_home(instance_home_id, 'request_a_quote'));
		if(data.request_a_quote>0){request_a_quoteObj.checked = true;}
		else{request_a_quoteObj.checked = false;}
		
		let  mobile_repair_appointmentObj = document.getElementById("mobile_repair_appointment");
		mobile_repair_appointmentObj.addEventListener('click',()=> update_instance_home(instance_home_id, 'mobile_repair_appointment'));
		if(data.mobile_repair_appointment>0){mobile_repair_appointmentObj.checked = true;}
		else{mobile_repair_appointmentObj.checked = false;}
		
		let  repair_status_onlineObj = document.getElementById("repair_status_online");
		repair_status_onlineObj.addEventListener('click',()=> update_instance_home(instance_home_id, 'repair_status_online'));
		if(data.repair_status_online>0){repair_status_onlineObj.checked = true;}
		else{repair_status_onlineObj.checked = false;}

		checkWebsiteEnable(data.website_on);		
	}
}

// All Page Header Section
async function all_pages_header(){
	let divForm;
	const showTableData = document.getElementById("viewPageInfo");
        header(Translate('All pages header'));
		const allPageRow = cTag('div',{ 'class':'columnSM12'});
			let divChild = cTag('div',{ 'class':'innerContainer bs-callout-info','style':'margin-top: 0; background: #fff' });
				const allPageForm = cTag('form',{ 'name':'frmAllPagesHeader','id':'frmAllPagesHeader','action':'#','enctype':"multipart/form-data",'method':'post','accept-charset':"utf-8" });
					divForm = cTag('div',{ 'class':'flexSpaBetRow' });
						let metaDataTitle = cTag('h4',{ 'class':'columnXS12  columnSM12 borderbottom', 'style': "font-size: 18px;" });
						metaDataTitle.innerHTML = Translate('META DATA');
					divForm.appendChild(metaDataTitle);
					
					[
						{id:'meta_keywords',label:Translate('Meta Keywords'),maxlength:'55'},
						{id:'meta_description',label:Translate('Meta Descriptions'),maxlength:'115'}
					].forEach(item=>{
							let itemColumn = cTag('div',{ 'class':' columnSM2' });
								let itemLabel = cTag('label',{ 'for':item.id });
								itemLabel.innerHTML = item.label;
							itemColumn.appendChild(itemLabel);
						divForm.appendChild(itemColumn);
							let itemValue = cTag('div',{ 'class':' columnSM4' });
							itemValue.appendChild(cTag('input',{ 'type':"text",'id':item.id,'name':item.id,'class':"form-control",'maxlength':item.maxlength }));
							itemValue.appendChild(cTag('span',{ 'id':'errmsg_'+item.id,'class':'errormsg' }));
						divForm.appendChild(itemValue);
					})

						let headerLogoColumn = cTag('div',{ 'class':'columnXS12 columnSM4' });
							let headerLogoTitle = cTag('h4',{ 'class':'borderbottom', 'style': "font-size: 18px;" });
							headerLogoTitle.innerHTML = Translate('Header Logo');
						headerLogoColumn.appendChild(headerLogoTitle);
							const headerLogoRow = cTag('div',{ 'class':'flexSpaBetRow' });
								let headerLogoDiv = cTag('div',{ 'class':'columnXS12' });
									let pictureDiv = cTag('div',{ 'style': "position: relative;", 'id':"all_pages_header_picture" });
									pictureDiv.appendChild(cTag('div',{ 'class':'currentPicture' }));
								headerLogoDiv.appendChild(pictureDiv);
							headerLogoRow.appendChild(headerLogoDiv);
						headerLogoColumn.appendChild(headerLogoRow);
					divForm.appendChild(headerLogoColumn);
						let headerBgColumn = cTag('div',{ 'class':'columnSM4' });
							let headerBgTitle = cTag('h4',{ 'class':'borderbottom', 'style': "font-size: 18px;" });
							headerBgTitle.innerHTML = Translate('Header Background Color');
						headerBgColumn.appendChild(headerBgTitle);
							let headerBgRow = cTag('div',{ 'class':'flexSpaBetRow' });
								let headerBgValue = cTag('div',{ 'class':'columnXS12  columnSM12' });
								headerBgValue.appendChild(cTag('input',{ 'style':'width: 100%; height: 100px; border: none','type':"color",'name':"bg_color",'id':"bg_color" }));                                            
							headerBgRow.appendChild(headerBgValue);
						headerBgColumn.appendChild(headerBgRow);
					divForm.appendChild(headerBgColumn);
						const textFontColumn = cTag('div',{ 'class':'columnSM4' });
							const textFontTitle = cTag('h4',{ 'class':'borderbottom', 'style': "font-size: 18px;" });
							textFontTitle.innerHTML = Translate('Text Color & Font Family')
						textFontColumn.appendChild(textFontTitle);
							const textColorRow = cTag('div',{ 'class':'flex', 'style': "align-items: center;" });
								const textColorTitle = cTag('div',{ 'class':'columnSM4' });
									let textColorLabel = cTag('label',{ 'for':'color' });
									textColorLabel.innerHTML = Translate('Text Color')+' :';
								textColorTitle.appendChild(textColorLabel);
							textColorRow.appendChild(textColorTitle);  
								let textColorValue = cTag('div',{ 'class':'columnSM8' });
								textColorValue.appendChild(cTag('input',{ 'style':'width: 100%; height: 30px; border: none','type':"color",'name':"color",'id':"color" }));
							textColorRow.appendChild(textColorValue);
						textFontColumn.appendChild(textColorRow);
							const fontRow = cTag('div',{ 'class':'flex', 'style': "padding-top: 15px; align-items: center;" });
								const fontTitle = cTag('div',{ 'class':'columnSM4' });
									let fontLabel = cTag('label',{ 'for':'font_family' });
									fontLabel.innerHTML = Translate('Font Family')+" :";
								fontTitle.appendChild(fontLabel);
							fontRow.appendChild(fontTitle); 
								let fontDropDown = cTag('div',{ 'class':'columnSM8' });
									let selectFont = cTag('select',{ 'class':'form-control','name':'font_family','id':'font_family' });
									setOptions(selectFont, fonFamOpts, 0, 0);
								fontDropDown.appendChild(selectFont);
							fontRow.appendChild(fontDropDown); 
						textFontColumn.appendChild(fontRow);
					divForm.appendChild(textFontColumn);
				allPageForm.appendChild(divForm);

					divForm = cTag('div',{ 'class':'columnSM12 roundborder','id':'headerPreview'});
						const webLogoRow = cTag('div',{ 'class':'flexStartRow bgColor','style':'align-items: center; background: #fafff9; padding: 15px 5px;' });
							const webLogoTitle = cTag('div',{'class':'columnXS12 columnSM2'});
								let newWebLogo = cTag('div',{'class':'logo','id':'addWebLogo'});
							webLogoTitle.appendChild(newWebLogo);
						webLogoRow.appendChild(webLogoTitle);
							const webLogoColumn = cTag('div',{ 'class':'columnXS12 columnSM9 flexEndRow' });
								let nav = cTag('nav',{ 'style':"border: none; margin-top: 20px;",'class':"navbar menu" });
									let container = cTag('div',{ 'class':'container-fluid' });
										let navbar1 = cTag('div',{ 'class':'collapse navbar-collapse','id':"bs-example-navbar-collapse-1",'style':"background: none" });
											let ulNavbar = cTag('ul',{ 'class':"flexEndRow nav navbar-nav"});
										navbar1.appendChild(ulNavbar);
									container.appendChild(navbar1);
								nav.appendChild(container);
							webLogoColumn.appendChild(nav);
						webLogoRow.appendChild(webLogoColumn);
					divForm.appendChild(webLogoRow);
				allPageForm.appendChild(divForm);
				
					divForm = cTag('div',{ 'class':'columnXS12', 'align':"center" });
					[
						'variables_id',
						'web_logo',
						'display_add_customer',
						'display_services',
						'display_products',
						'display_inventory',
						'request_a_quote',
						'mobile_repair_appointment',
						'repair_status_online',
						'company_name'
					].forEach(item=>{
						divForm.appendChild(cTag('input',{ 'type':'hidden','name':item,'id':item }));
					})
						let inputField = cTag('input',{ 'class':'btn saveButton','name':'submit','id':'submit','type':'submit','value':'Save' });
					divForm.appendChild(inputField);
				allPageForm.appendChild(divForm);
			divChild.appendChild(allPageForm);
		allPageRow.appendChild(divChild);
	showTableData.appendChild(allPageRow);

	addCustomeEventListener('preview',headerPreview);
	AJ_all_pages_header_MoreInfo();
}

async function AJ_all_pages_header_MoreInfo(){
	let currentPicture, deletedicon;

	const jsonData = {};
    const url = '/'+segment1+'/AJ_all_pages_header_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.getElementById("meta_keywords").value = data.meta_keywords;
		document.getElementById("meta_description").value = data.meta_description;
		let all_pages_header_picture = document.getElementById("all_pages_header_picture");
		if(data.onePicture !==''){
			all_pages_header_picture.innerHTML = '';
			currentPicture = cTag('div',{ 'class':'currentPicture' });
			currentPicture.appendChild(cTag('img',{ 'class':'img-responsive','src':data.onePicture,'alt':data.alt }));
			all_pages_header_picture.appendChild(currentPicture);				
		}
		else{
			all_pages_header_picture.innerHTML = Translate('Upload Logo');
			let br = cTag('br');
			all_pages_header_picture.appendChild(br);
				let button = cTag('button',{ 'class':'uploadButton', 'type':'button','name':'open','click':()=>upload_dialog(''+Translate('Upload Header Logo'), 'all_pages_header', 'web_logo_',headerPreview)});
				button.innerHTML = Translate('Upload')+'...';
			all_pages_header_picture.appendChild(button);
		}

		document.getElementById("bg_color").value = data.bg_color;
		document.getElementById("color").value = data.color;
		document.getElementById("font_family").value = data.font_family;
		document.getElementById("variables_id").value = data.variables_id;
		document.getElementById("web_logo").value = data.web_logo;
		document.getElementById("display_add_customer").value = data.display_add_customer;
		document.getElementById("display_services").value = data.display_services;
		document.getElementById("display_products").value = data.display_products;
		document.getElementById("display_inventory").value = data.display_inventory;
		document.getElementById("request_a_quote").value = data.request_a_quote;
		document.getElementById("mobile_repair_appointment").value = data.mobile_repair_appointment;
		document.getElementById("repair_status_online").value = data.repair_status_online;
		document.getElementById("company_name").value = data.company_name;
		
		document.querySelector('#bg_color').addEventListener('change', headerPreview);
		document.querySelector('#color').addEventListener('change', headerPreview);
		document.querySelector('#font_family').addEventListener('change', headerPreview);

		currentPicture = document.querySelector('.currentPicture');
		
		if(currentPicture){
			currentPicture.addEventListener('mouseover', () => {
				deletedicon = document.querySelector('.deletedicon');					
				if(!deletedicon){
					currentPicture.appendChild(cTag('div',{ 'class':'deletedicon','click':()=>AJremove_Picture(''+data.onePicture, 'all_pages_header','')}));
				}
			});

			currentPicture.addEventListener('mouseleave', () => {
				deletedicon = document.querySelector('.deletedicon');
				if(deletedicon){
					deletedicon.remove();
				}
			});
		}
		document.querySelector('#frmAllPagesHeader').addEventListener('submit',AJsave_all_pages_header)
		// checkWebsiteEnable(1);
		headerPreview();
	}
}

function headerPreview(){
	let web_logo, aTag;
	function linkCreator(parent,href,title){
			let menuLi = cTag('li');
				aTag = cTag('a',{ 'href':'/'+href,'title': title,'style': 'color:'+color+'; font-weight:600; font-family:\''+font_family+'\';' });
				aTag.innerHTML = title;
			menuLi.appendChild(aTag);
		parent.appendChild(menuLi);
	}
	let bg_color = document.getElementById("bg_color").value;
	let color = document.getElementById("color").value;
	let font_family = document.getElementById("font_family").value;
	web_logo ='';
	let currentPicture = document.querySelector('.currentPicture');
	if(currentPicture){
		web_logo = currentPicture.querySelector('.img-responsive').src;
	}
	let display_add_customer = document.getElementById("display_add_customer").value;
	let display_services = document.getElementById("display_services").value;
	let display_products = document.getElementById("display_products").value;
	let display_inventory = document.getElementById("display_inventory").value;
	let request_a_quote = document.getElementById("request_a_quote").value;
	let mobile_repair_appointment = document.getElementById("mobile_repair_appointment").value;
	let repair_status_online = document.getElementById("repair_status_online").value;
	let company_name = document.getElementById("company_name").value;
	
	document.querySelector(".bgColor").style.background = bg_color;
	let addWebLogo = document.getElementById("addWebLogo");
	addWebLogo.innerHTML = '';
	if(web_logo !==''){
			aTag = cTag('a',{'href':'/','title':company_name});
			aTag.appendChild(cTag('img',{ 'class':'img-responsive','src':web_logo,'alt':company_name,'style':"max-height:80px;" }))
		addWebLogo.appendChild(aTag);
	}else{
		aTag = cTag('a',{'href':'/','title':company_name, 'style':'color:'+color+';font-weight:600;'});
			let companyNameHeader = cTag('h1');
			companyNameHeader.innerHTML = company_name;
		aTag.appendChild(companyNameHeader)
		addWebLogo.appendChild(aTag);
	}

	let PagesHeader = document.querySelector("#frmAllPagesHeader").querySelector('.nav.navbar-nav');
	PagesHeader.innerHTML = '';
		
	linkCreator(PagesHeader,'',Translate('Home'));
	linkCreator(PagesHeader,'Contact-Us',Translate('Contact Us'));

	if(display_add_customer>0){
		linkCreator(PagesHeader,'Customer',Translate('Customer'));
	}

	if(display_services>0){
		linkCreator(PagesHeader,'Services',Translate('Services'));
	}

	if(display_products>0){
		linkCreator(PagesHeader,'Product',Translate('Product'));
	}

	if(display_inventory>0){
		linkCreator(PagesHeader,'CellPhones',Translate('Mobile Devices'));
	}

	if(request_a_quote>0 || mobile_repair_appointment>0 || repair_status_online>0){
		let li1 = cTag('li',{class:'', id:'repairsdropdown11'});
			aTag = cTag('a',{ 'href':'#','class':"dropdown-toggle",'data-toggle':"dropdown",'role':'button','aria-haspopup':"true",'aria-expanded':"false",'title':'Home','style':'font-size: 14px;color:'+color+';font-weight:600;line-height: 20px;font-family:\''+font_family+'\' !important;' });
			aTag.innerHTML = Translate('Repairs')
				let span = cTag('span',{ 'class':'caret' });
			aTag.appendChild(span);      
		li1.appendChild(aTag);

		let subUL = cTag('ul',{ 'class':'dropdown-menu' });
		if(request_a_quote>0){
			linkCreator(subUL,'Quote',Translate('Request a Quote'));
		}

		if(mobile_repair_appointment>0 ){
			linkCreator(subUL,'Appointment',Translate('Repair Appointment'));
		}
		if(repair_status_online>0){			
			linkCreator(subUL,'Check_Repair_Status',Translate('Check Repair Status'));
		}
		li1.appendChild(subUL);
		PagesHeader.appendChild(li1);
		multiSelectAction('repairsdropdown11');
	}
}

async function AJsave_all_pages_header(e){	
	e.preventDefault()
	AJsave({
		btn_id: '#submit',
		form_id: '#frmAllPagesHeader',
		api_endpoint: 'AJsave_all_pages_header'
	});
}

// Home page body part
function getTimebyFormate(time){
	if(time==='Closed') return time;
	if(timeformat==='24 hour' && (time.includes('am')||time.includes('pm'))){
		let [hourMinute,AM_PM] = time.split(' ');
		let [hour,minute='00'] = hourMinute.split(':');
		hour = parseInt(hour);
		if(AM_PM==='am'){
			if(hour===12) hour = 0;
			if(hour<10) hour = '0'+hour;
		}
		else{
			if(hour<12) hour = hour+12;
		}
		return `${hour}:${minute}`;
	}
	else if(timeformat==='12 hour' && !(time.includes('am')||time.includes('pm'))){
		let [hour,minute] = time.split(':');
		hour = parseInt(hour);

		if(hour===0){
			if(minute===0) return `12 am`;
			else return `12:${minute} am`;
		}
		else if(hour<12){
			if(minute===0) return `${hour} am`;
			else return `${hour}:${minute} am`;
		} 
		else if(hour===12){
			if(minute===0) return `${hour} pm`;
			else return `${hour}:${minute} pm`;
		}
		else if(hour<24){
			if(minute===0) return `${hour-12} pm`;
			else return `${hour-12}:${minute} pm`;
		}
	}
	return time;
}

async function showInstanceHomeForm(fromSegment){
	let formTitle = '';
	let focusFieldName = '';
	let formData;
	if(fromSegment==='homePreview1'){
		formTitle = Translate('Change Segment1 Information');
		focusFieldName = 'mst_one';
	}
	else if(fromSegment==='homePreview21'){
		formTitle = Translate('Change CELLULAR Services Information');
		focusFieldName = 'cellular_services1';
	}
	else if(fromSegment==='homePreview22'){
		formTitle = Translate('Change Business HOURS Information');
		focusFieldName = 'mon_from';
	}
	else if(fromSegment==='homePreview23'){
		formTitle = Translate('Change Business Address / Map Address Information');
		focusFieldName = 'business_address';
	}
	else if(fromSegment==='homePreview311'){
		formTitle = Translate('Change Business Details1 Icon');
		focusFieldName = 'bd_one_icon';
	}
	else if(fromSegment==='homePreview312'){
		formTitle = Translate('Change Business Details1 Information');
		focusFieldName = 'bd_one_headline';
	}
	else if(fromSegment==='homePreview321'){
		formTitle = Translate('Change Business Details2 Icon');
		focusFieldName = 'bd_two_icon';
	}
	else if(fromSegment==='homePreview322'){
		formTitle = Translate('Change Business Details2 Information');
		focusFieldName = 'bd_two_headline';
	}
	else if(fromSegment==='homePreview331'){
		formTitle = Translate('Change Business Details3 Icon');
		focusFieldName = 'bd_three_icon';
	}
	else if(fromSegment==='homePreview332'){
		formTitle = Translate('Change Business Details3 Information');
		focusFieldName = 'bd_three_headline';
	}

	let formGroup, div, label, span, divCol, divGroupCol, select;
	function businessDetailsForm(formDialog,fields){
		let data = formData;
			fields.forEach((item,indx)=>{
					formGroup = cTag('div',{ 'class':'flex', 'style': "text-align: left;" });
						let labelName = cTag('div',{ 'class':'columnSM4' });
							label = cTag('label',{ 'for':item.id });
							label.append(item.label);
							if(indx!==2){
									span = cTag('span',{ 'class':'required' });
									span.innerHTML = '*';
								label.appendChild(span);
							}
						labelName.appendChild(label);
					formGroup.appendChild(labelName);
						let labelValue = cTag('div',{ 'class':'columnSM8' });
						if(indx===2){
								let textArea = cTag('textarea',{ 'id':item.id,'rows':'8','name':item.id,'class':'form-control' });
								textArea.innerHTML = item.value ;
							labelValue.appendChild(textArea);
						}
						else{
							labelValue.appendChild(cTag('input',{ 'type':'text','value':item.value,'id':item.id,'name':item.id,'class':'form-control vrequired','maxlength':'200' }));
						}						
						labelValue.appendChild(cTag('span',{ 'class':'error_msg','id':`errmsg_${item.id}`}));
					formGroup.appendChild(labelValue);
				form.appendChild(formGroup);
			})
			form.appendChild(cTag('input',{ 'type':'hidden','name':'instance_home_id','id':'instance_home_id','value':data.formData.instance_home_id }));
		formDialog.appendChild(form);

		popup_dialog600(formTitle,formDialog,Translate('Save'),updateInstanceHome);
	}

	function iconCreator(id){
		let data = formData;
		let selectedIcon = data.formData[id];
		if(!selectedIcon){
			if(id==='bd_one_icon') selectedIcon = data.formData.faIcons[0];
			else if(id==='bd_two_icon') selectedIcon = data.formData.faIcons[1];
			else if(id==='bd_three_icon') selectedIcon = data.formData.faIcons[2];
		}
				divCol = cTag('div',{ 'class':'columnSM12' });
				data.formData.faIcons.forEach(icon=>{
					div = cTag('div',{ 'class':'cursor '+id,'style':'height: 80px; overflow: hidden; padding-top: 25px; width: 80px; float: left; text-align: center;', 'title':icon });									
					if(selectedIcon === icon){
						div.classList.add('boxborder');
					}
					div.appendChild(cTag('i',{ 'class':`fa fa-${icon}`, 'style': "font-size: 2em;" }));
					div.addEventListener('click',function(){
						document.querySelector(`[name=${id}]`).value = this.title;
						if(document.querySelector("#frmInstanceHome").querySelector(".boxborder")){
							document.querySelector("#frmInstanceHome").querySelector(".boxborder").classList.remove('boxborder');
						}
						this.classList.add('boxborder');
					})							
				divCol.appendChild(div);
				})
				divCol.appendChild(cTag('input',{ 'type':'hidden','id':id,'name':id,'class':'vrequired','maxlength':'50','value':selectedIcon }));							;
				divCol.appendChild(cTag('input',{ 'type':'hidden','value':data.formData.instance_home_id,'id':'instance_home_id','name':'instance_home_id' }));								
				divCol.appendChild(cTag('span',{ "class":"error_msg","id":"errmsg_bd_one_icon" }));							
			form.appendChild(divCol);
		formDialog.appendChild(form);
		popup_dialog600(formTitle,formDialog,Translate('Save'),updateInstanceHome);
	}

	const jsonData = {};
	jsonData['fromSegment'] = fromSegment;

    const url = '/'+segment1+'/showInstanceHomeForm';
	fetchData(afterFetch,url,jsonData);
	let formDialog,form;
	function afterFetch(data){
		formData = data;
		formDialog = cTag('div');
			form = cTag('form',{ 'action':"#",'name':"frmInstanceHome",'id':"frmInstanceHome" });
			form.appendChild(cTag('div',{ 'class':"errormsg",'id':"errorInstanceHome" }));				
			form.appendChild(cTag('input',{ 'type':"hidden",'value': fromSegment,'id':"fromSegment",'name':"fromSegment" }));

		if(fromSegment==='homePreview1'){
			let fields = [
				{id:'mst_one', label:Translate('1st Line'), value:data.formData.mst_one},
				{id:'mst_two', label:Translate('2nd Line'), value:data.formData.mst_two},
				{id:'mst_three', label:Translate('3rd Line'), value:data.formData.mst_three},
				{id:'mst_four', label:Translate('4th Line'), value:data.formData.mst_four}
			]				
			fields.forEach(item=>{
				let divGroup = cTag('div',{ 'class':"flex", 'style': "align-items: center; text-align: left;" });
					divGroupCol = cTag('div',{ 'class':"columnSM4" });
						let divGroupColLv = cTag('label',{ 'for':item.id });
						divGroupColLv.innerHTML = item.label;
							let requiredSpan = cTag('span',{ 'class':"required" });
							requiredSpan.innerHTML = '*';
						divGroupColLv.appendChild(requiredSpan);
					divGroupCol.appendChild(divGroupColLv);
				divGroup.appendChild(divGroupCol);

					divGroupCol = cTag('div',{ 'class':"columnSM8" });
					divGroupCol.appendChild(cTag('input',{ 'type':"text",'value':item.value,'id':item.id,'name':item.id,'class':"form-control vrequired",'maxlength':"100" }));
					divGroupCol.appendChild(cTag('span',{ 'id':"errmsg_mst_one",'class':"errormsg" }));
				divGroup.appendChild(divGroupCol);
				form.appendChild(divGroup);
			})				
			formDialog.appendChild(form);

			popup_dialog600(formTitle,formDialog,Translate('Save'),updateInstanceHome);
		}
		else if(fromSegment==='homePreview21'){
			for(let x = 1; x <= 7; x++){
					let cellularServiceInfo = cTag('div',{ 'class':"columnXS12", 'style': "padding-top: 5px;" });
					cellularServiceInfo.appendChild(cTag('input',{ 'type':"text",'class':"form-control",'name':`cellular_services${x}`,'id':`cellular_services${x}`,'value':data.formData[`cellular_services${x}`],'placeholder':`Cellular Services ${x}`,'maxlength':"40" }));
				form.appendChild(cellularServiceInfo);
			}
			formDialog.appendChild(form);

			popup_dialog600(formTitle,formDialog,Translate('Save'),updateInstanceHome);
		}
		else if(fromSegment==='homePreview22'){
			let dayKeys = {
				Monday:['mon_from','mon_to'],
				Tuesday:['tue_from','tue_to'],
				Wednesday:['wed_from','wed_to'],
				Thursday:['thu_from','thu_to'],
				Friday:['fri_from','fri_to'],
				Saturday:['sat_from','sat_to'],
				Sunday:['sun_from','sun_to'],
			}
			for (const key in dayKeys) {					
				formGroup = cTag('div',{ 'class':'flex', 'style': "align-items: center; text-align: left;" });
					divCol = cTag('div',{ 'class':'columnSM4' });
					divCol.innerHTML = key;
				formGroup.appendChild(divCol);
					divCol = cTag('div',{ 'class':'columnXS6 columnSM4' });
						select = cTag('select',{ 'class':'form-control','name':`${dayKeys[key][0]}`,'id':`${dayKeys[key][0]}` });
						setOptions(select, data.formData.hoursOpt, 0, 0);						
					divCol.appendChild(select);
				formGroup.appendChild(divCol);
					divCol = cTag('div',{ 'class':'columnXS6 columnSM4' });
						select = cTag('select',{ 'class':'form-control','name':`${dayKeys[key][1]}`,'id':`${dayKeys[key][1]}` });
						setOptions(select, data.formData.hoursOpt, 0, 0);
					divCol.appendChild(select);
				formGroup.appendChild(divCol);
				form.appendChild(formGroup);
			}
			formDialog.appendChild(form);

			popup_dialog600(formTitle,formDialog,Translate('Save'),updateInstanceHome);   

			for (const key in dayKeys) {
				document.getElementById(dayKeys[key][0]).value = getTimebyFormate(data.formData[dayKeys[key][0]]);		;
				document.getElementById(dayKeys[key][1]).value = getTimebyFormate(data.formData[dayKeys[key][1]]);
			}
		}
		else if(fromSegment==='homePreview23'){
				formGroup = cTag('div',{ 'class':'flex', 'style': "align-items: center;" });
					divCol = cTag('div',{ 'class':'columnSM2' });
						label = cTag('label',{ 'for':'bd_one_subheadline' });
						label.append(Translate('Address'));
					divCol.appendChild(label);
				formGroup.appendChild(divCol);
					divCol = cTag('div',{ 'class':'columnSM10' });
					divCol.appendChild(cTag('input',{ 'type':'text','value':data.formData.business_address,'placeholder':Translate('Business Address / Map Address'),'id':'business_address','name':'business_address','class':'form-control vrequired','maxlength':'200' }));
					divCol.appendChild(cTag('span',{ 'id':'errmsg_business_address','class':'errormsg' }));
				formGroup.appendChild(divCol);
			form.appendChild(formGroup);
			formDialog.appendChild(form);
			popup_dialog600(formTitle,formDialog,Translate('Save'),updateInstanceHome);
		}
		else if(fromSegment==='homePreview311'){
			iconCreator('bd_one_icon');					
		}
		else if(fromSegment==='homePreview321'){
			iconCreator('bd_two_icon');
		}
		else if(fromSegment==='homePreview331'){
			iconCreator('bd_three_icon');
		}
		else if(fromSegment==='homePreview312'){
			businessDetailsForm(formDialog,[
				{id:'bd_one_headline', label:Translate('1st Line'), value:data.formData.bd_one_headline},
				{id:'bd_one_subheadline', label:Translate('1st Sub-Headline'), value:data.formData.bd_one_subheadline},
				{id:'bd_one_details', label:Translate('1st Details'), value:data.formData.bd_one_details},
			]);			
		}
		else if(fromSegment==='homePreview322'){
			businessDetailsForm(formDialog,[
				{id:'bd_two_headline', label:Translate('2nd Line'), value:data.formData.bd_two_headline},
				{id:'bd_two_subheadline', label:Translate('2nd Sub-Headline'), value:data.formData.bd_two_subheadline},
				{id:'bd_two_details', label:Translate('2nd Details'), value:data.formData.bd_two_details},
			]);			
		}
		else if(fromSegment==='homePreview332'){
			businessDetailsForm(formDialog,[
				{id:'bd_three_headline', label:Translate('3rd Line'), value:data.formData.bd_three_headline},
				{id:'bd_three_subheadline', label:Translate('3rd Sub-Headline'), value:data.formData.bd_three_subheadline},
				{id:'bd_three_details', label:Translate('3rd Details'), value:data.formData.bd_three_details},
			]);			
		}
		else{
			let formhtml = cTag('form',{ "action":"#","name":"frmInstanceHome","id":"frmInstanceHome","submit":updateInstanceHome,"enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
			formhtml.appendChild(cTag('div',{ "id":"errorInstanceHome","class":"errormsg" }));
			formhtml.append(data.returnStr);
			formhtml.appendChild(cTag('input',{ "type":"hidden","name":"fromSegment","id":"fromSegment","value":fromSegment }));

			popup_dialog600(formTitle, formhtml, Translate('Save'), updateInstanceHome);
		}
	}

}

async function updateInstanceHome(hidePopup){
	let errorId = document.getElementById("errorInstanceHome");
	errorId.innerHTML = '';
	let allRequiredFields = document.getElementsByClassName("vrequired");
	if(allRequiredFields.length>0){
		for(let f = 0; f < allRequiredFields.length; f++){
			let fieldValue = allRequiredFields[f].value;
			errorId.innerHTML = '';
			if(fieldValue===''){
				errorId.innerHTML = Translate('Missing Data');
				allRequiredFields[f].focus();
				return false;
			}
		}
	}

	let mapAddress, icon, headline, subheadline, details;
    let fromSegment = document.getElementById("fromSegment").value;
	const jsonData = serialize('#frmInstanceHome')
    const url = '/'+segment1+'/updateInstanceHome';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.returnStr ==='Inserted' || data.returnStr ==='Updated'){
			if(fromSegment==='homePreview1'){
				document.querySelector(".mst_one").innerHTML = document.getElementById("mst_one").value;
				document.querySelector(".mst_two_three").innerHTML = document.getElementById("mst_two").value+'<br>'+document.getElementById("mst_three").value;
				document.querySelector(".mst_four").innerHTML = document.getElementById("mst_four").value;
			}
			else if(fromSegment==='homePreview21' || fromSegment==='homePreview22' || fromSegment==='homePreview23'){
				if(fromSegment === 'homePreview21'){
					const cellular_services_list = document.querySelector('ul#cellular_services_list');
					cellular_services_list.innerHTML = '';
					for (let index = 1; index < 8; index++) {
						const cellular_service_name = `cellular_services${index}`;
						const cellular_service_value = document.getElementById(cellular_service_name).value;
						if(cellular_service_value) {
							const list = cTag('li',{ 'class':cellular_service_name,style:"border-bottom: 1px solid #ffffff;list-style: none;line-height: 30px;" });
							list.innerText = cellular_service_value;
							cellular_services_list.appendChild(list);
						}
					}
					// document.querySelector('.cellular_services1').innerHTML = document.getElementById('cellular_services1').value;
					// document.querySelector('.cellular_services2').innerHTML = document.getElementById('cellular_services2').value;
					// document.querySelector('.cellular_services3').innerHTML = document.getElementById('cellular_services3').value;
					// document.querySelector('.cellular_services4').innerHTML = document.getElementById('cellular_services4').value;
					// document.querySelector('.cellular_services5').innerHTML = document.getElementById('cellular_services5').value;
					// document.querySelector('.cellular_services6').innerHTML = document.getElementById('cellular_services6').value;
					// document.querySelector('.cellular_services7').innerHTML = document.getElementById('cellular_services7').value;
				}
				else if(fromSegment === 'homePreview22'){
					let list = document.getElementById('business_hours_list').childNodes
					let mon_from = document.querySelector('#mon_from').value
					let tue_from = document.querySelector('#tue_from').value
					let wed_from = document.querySelector('#wed_from').value
					let thu_from = document.querySelector('#thu_from').value
					let fri_from = document.querySelector('#fri_from').value
					let sat_from = document.querySelector('#sat_from').value
					let sun_from = document.querySelector('#sun_from').value

					list[0].innerHTML = mon_from === 'Closed'? `» ${Translate('Monday Closed')}`: `» ${Translate('Monday')} ${mon_from} to ${document.querySelector('#mon_to').value}`
					list[1].innerHTML = tue_from === 'Closed'? `» ${Translate('Tuesday Closed')}`: `» ${Translate('Tuesday')} ${tue_from} to ${document.querySelector('#tue_to').value}`
					list[2].innerHTML = wed_from === 'Closed'? `» ${Translate('Wednesday Closed')}`: `» ${Translate('Wednesday')} ${wed_from} to ${document.querySelector('#wed_to').value}`
					list[3].innerHTML = thu_from === 'Closed'? `» ${Translate('Thursday Closed')}`: `» ${Translate('Thursday')} ${thu_from} to ${document.querySelector('#thu_to').value}`
					list[4].innerHTML = fri_from === 'Closed'? `» ${Translate('Friday Closed')}`: `» ${Translate('Friday')} ${fri_from} to ${document.querySelector('#fri_to').value}`
					list[5].innerHTML = sat_from === 'Closed'? `» ${Translate('Saturday Closed')}`: `» ${Translate('Saturday')} ${sat_from} to ${document.querySelector('#sat_to').value}`
					list[6].innerHTML = sun_from === 'Closed'? `» ${Translate('Sunday Closed')}`: `» ${Translate('Sunday')} ${sun_from} to ${document.querySelector('#sun_to').value}`
				}
				else if(fromSegment === 'homePreview23'){
					let business_address = document.querySelector('input#business_address').value;
					document.querySelector('.business_address').innerHTML = business_address;
					mapAddress = business_address.replace(' ', '-');
					mapAddress = mapAddress.replace(',', '-');
					document.querySelector('#mapAddress').src = 'http://maps.google.it/maps?q='+mapAddress+'&output=embed';
				}
			}
			else if(fromSegment==='homePreview311' || fromSegment==='homePreview312' || fromSegment==='homePreview321' || fromSegment==='homePreview322' || fromSegment==='homePreview331' || fromSegment==='homePreview332'){
				if(fromSegment==='homePreview311'){
					icon = document.querySelector('[name=bd_one_icon]').value
					document.querySelector('.homePreview311').children[0].setAttribute('class',`fa fa-${icon}`)
				}
				else if(fromSegment==='homePreview321'){
					icon = document.querySelector('[name=bd_two_icon]').value
					document.querySelector('.homePreview321').children[0].setAttribute('class',`fa fa-${icon}`)
				}
				else if(fromSegment==='homePreview331'){
					icon = document.querySelector('[name=bd_three_icon]').value
					document.querySelector('.homePreview331').children[0].setAttribute('class',`fa fa-${icon}`)
				}
				else if(fromSegment==='homePreview312'){
					headline = document.querySelector('input#bd_one_headline').value
					subheadline = document.querySelector('input#bd_one_subheadline').value
					details = document.querySelector('textarea#bd_one_details').value
					document.getElementById('bd_one_headline').innerHTML = `${headline} <br> ${subheadline}`
					document.getElementById('bd_one_details').innerHTML = details
				}
				else if(fromSegment==='homePreview322'){
					headline = document.querySelector('input#bd_two_headline').value
					subheadline = document.querySelector('input#bd_two_subheadline').value
					details = document.querySelector('textarea#bd_two_details').value
					document.getElementById('bd_two_headline').innerHTML = `${headline} <br> ${subheadline}`
					document.getElementById('bd_two_details').innerHTML = details;
				}
				else if(fromSegment==='homePreview332'){
					headline = document.querySelector('input#bd_three_headline').value
					subheadline = document.querySelector('input#bd_three_subheadline').value
					details = document.querySelector('textarea#bd_three_details').value
					document.getElementById('bd_three_headline').innerHTML = `${headline} <br> ${subheadline}`
					document.getElementById('bd_three_details').innerHTML = details;
				}
			}
			hidePopup();
		}
	}
}

function homePreview1(){
	let homePreview1, editicon, curPicDiv, onePicture, currentPicture;
	let bg_color = document.getElementById("bg_color1").value;
	let color = document.getElementById("color1").value;
	let font_family = document.getElementById("font_family1").value;

	homePreview1 = document.querySelector("#homePreview1").querySelector("section");
	homePreview1.style.background = bg_color;
	homePreview1.style.color = color;
	homePreview1.style.fontFamily = font_family;
	
	homePreview1  = document.querySelector('.homePreview1 ');
	if(homePreview1){
		homePreview1 .addEventListener('mouseover', () => {
			editicon = homePreview1 .querySelector('.editicon');
			if(!editicon){
					curPicDiv = cTag('div',{ 'style':'position:absolute; right:70px; top:30px; z-index:999; height:30px; width:70px; color:'+color+'; cursor:pointer; ','class':'editicon' });
					curPicDiv.appendChild(cTag('i',{ 'class':'fa fa-pencil-square', 'style': "font-size: 2em;", 'click':()=> showInstanceHomeForm('homePreview1')}));
				homePreview1.appendChild(curPicDiv);
			}
		});

		homePreview1 .addEventListener('mouseleave', () => {
			editicon = homePreview1 .querySelector('.editicon');
			if(editicon){
				editicon.parentNode.removeChild( editicon);
			}
		});
	}

	//=========Picture=======//
	onePicture = document.getElementById("onePicture").value;
	if(onePicture ===''){onePicture = '/assets/images/pagebodyseg11.png';}

	let home_page_body_picture = document.getElementById("home_page_body_picture");
	home_page_body_picture.innerHTML = '';
		currentPicture = cTag('div',{ 'class':'currentPicture' });
		currentPicture.appendChild(cTag('img',{ 'class':'img-responsive','src': onePicture,'alt': onePicture }));
	home_page_body_picture.appendChild(currentPicture);	
	
	currentPicture = document.querySelector('.currentPicture');			
	if(currentPicture){
		currentPicture.addEventListener('mouseover', () => {
			editicon = currentPicture.querySelector('.editicon');
			if(!editicon){
				curPicDiv = cTag('div',{ 'style':'position:absolute; right:70px; top:30px; z-index:999;height:30px;width:70px;color:'+color+'; cursor:pointer;','class':'editicon' });
				curPicDiv.appendChild(cTag('i',{ 'class':'fa fa-pencil-square', 'style': "font-size: 2em;", 'click':()=> upload_dialog(''+Translate('Upload Segment1 Image'), 'home_page_body', 'pagebodyseg1_',homePreview1) }));
				if(onePicture !=='/assets/images/pagebodyseg11.png'){
					curPicDiv.appendChild(cTag('i',{ 'class':'fa fa-remove', 'style': "margin-left: 10px; font-size: 2em;", 'click':()=> AJremove_Picture(''+onePicture, 'home_page_body','') }))
				}
				currentPicture.appendChild(curPicDiv);
			}
		});

		currentPicture.addEventListener('mouseleave', () => {
			editicon = currentPicture.querySelector('.editicon');
			if(editicon){
				editicon.parentNode.removeChild( editicon);
			}
		});
	}
}

function homePreview2(){
	let bg_color = document.getElementById("bg_color2").value;
	let color = document.getElementById("color2").value;
	let font_family = document.getElementById("font_family2").value;

	let homePreview = document.querySelector("#homePreview2").querySelector("section");
	homePreview.style.background = bg_color;
	homePreview.style.color = color;
	homePreview.style.fontFamily = font_family;
	
	let editicon;
	['homePreview21','homePreview22','homePreview23'].forEach(id=>{
		let homePreview  = document.querySelector('.'+id);			
		if(homePreview ){
			homePreview .addEventListener('mouseover', e => {
				editicon = homePreview .querySelector('.editicon');
				if(!editicon){
					let curPicDiv = cTag('div',{ 'style':'position:absolute;right:30px; top:0px; z-index:999;height:30px;color:'+color+'; cursor:pointer;','class':'editicon' });
					curPicDiv.appendChild(cTag('i',{ 'class':'fa fa-pencil-square','style': "font-size: 2em;", 'click': ()=>showInstanceHomeForm(id) }));
					homePreview .appendChild(curPicDiv);
				}
			});

			homePreview .addEventListener('mouseleave', () => {
				editicon = homePreview .querySelector('.editicon');
				if(editicon){
					editicon.remove();
				}
			});
		}
	})
}

function homePreview3(){
	let editicon;
	let bg_color = document.getElementById("bg_color3").value;
	let color = document.getElementById("color3").value;
	let font_family = document.getElementById("font_family3").value;

	let homePreview = document.querySelector("#homePreview3").querySelector("section");
	homePreview.style.background = bg_color;
	homePreview.style.color = color;
	homePreview.style.fontFamily = font_family;

	[
		'homePreview311','homePreview312',
		'homePreview321','homePreview322',
		'homePreview331','homePreview332',
	].forEach(id=>{
		let homePreview  = document.querySelector('.'+id);			
		if(homePreview ){
			homePreview .addEventListener('mouseover', e => {
				editicon = homePreview .querySelector('.editicon');
				if(!editicon){
					let curPicDiv = cTag('div',{ 'style':'position:absolute;right:0px; top:0px; z-index:999;height:30px;color:'+color+'; cursor:pointer;','class':'editicon' });
					curPicDiv.appendChild(cTag('i',{ 'class':'fa fa-pencil-square', 'style': "font-size: 2em;", 'click':()=> showInstanceHomeForm(id)}));
					homePreview .appendChild(curPicDiv);
				}
			});

			homePreview .addEventListener('mouseleave', () => {
				editicon = homePreview .querySelector('.editicon');
				if(editicon){
					editicon.remove();
				}
			});
		}
	})
}

async function AJ_home_page_body_MoreInfo() {
	const jsonData = {};
    const url = '/'+segment1+'/AJ_home_page_body_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.getElementById('bg_color1').value = data.bg_color1;
		document.getElementById('color1').value = data.color1;
		document.getElementById('font_family1').value = data.font_family1;
		document.getElementById('onePicture').value = data.onePicture;
		document.getElementById("variables_id").value = data.variables_id;
				
		document.querySelector(".mst_one").innerHTML = data.mst_one;
		document.querySelector(".mst_two_three").innerHTML = (data.mst_two)+'<br>'+(data.mst_three);
		document.querySelector(".mst_four").innerHTML = data.mst_four;
		
		homePreview1();

		document.getElementById('bg_color2').value = data.bg_color2;
		document.getElementById('color2').value = data.color2;
		document.getElementById('font_family2').value = data.font_family2;

		const cellular_services_list = document.querySelector('ul#cellular_services_list');
		for (let index = 1; index < 8; index++) {
			const cellular_service_name = `cellular_services${index}`;
			const cellular_service_value = data[cellular_service_name];
			if(cellular_service_value) {
				const list = cTag('li',{ 'class':cellular_service_name,style:"border-bottom: 1px solid #ffffff;list-style: none;line-height: 30px;" });
				list.innerText = cellular_service_value;
				cellular_services_list.appendChild(list);
			}
		}
		// document.querySelector('.cellular_services1').innerHTML = data.cellular_services1;
		// document.querySelector('.cellular_services2').innerHTML = data.cellular_services2;
		// document.querySelector('.cellular_services3').innerHTML = data.cellular_services3;
		// document.querySelector('.cellular_services4').innerHTML = data.cellular_services4;
		// document.querySelector('.cellular_services5').innerHTML = data.cellular_services5;
		// document.querySelector('.cellular_services6').innerHTML = data.cellular_services6;
		// document.querySelector('.cellular_services7').innerHTML = data.cellular_services7;

		let list = document.getElementById('business_hours_list').childNodes;
		list[0].innerHTML = data.mon_from === 'Closed'? `» ${Translate('Monday Closed')}`: `» ${Translate('Monday')} ${getTimebyFormate(data.mon_from)} to ${getTimebyFormate(data.mon_to)}`;
		list[1].innerHTML = data.tue_from === 'Closed'? `» ${Translate('Tuesday Closed')}`: `» ${Translate('Tuesday')} ${getTimebyFormate(data.tue_from)} to ${getTimebyFormate(data.tue_to)}`;
		list[2].innerHTML = data.wed_from === 'Closed'? `» ${Translate('Wednesday Closed')}`: `» ${Translate('Wednesday')} ${getTimebyFormate(data.wed_from)} to ${getTimebyFormate(data.wed_to)}`;
		list[3].innerHTML = data.thu_from === 'Closed'? `» ${Translate('Thursday Closed')}`: `» ${Translate('Thursday')} ${getTimebyFormate(data.thu_from)} to ${getTimebyFormate(data.thu_to)}`;
		list[4].innerHTML = data.fri_from === 'Closed'? `» ${Translate('Friday Closed')}`: `» ${Translate('Friday')} ${getTimebyFormate(data.fri_from)} to ${getTimebyFormate(data.fri_to)}`;
		list[5].innerHTML = data.sat_from === 'Closed'? `» ${Translate('Saturday Closed')}`: `» ${Translate('Saturday')} ${getTimebyFormate(data.sat_from)} to ${getTimebyFormate(data.sat_to)}`;
		list[6].innerHTML = data.sun_from === 'Closed'? `» ${Translate('Sunday Closed')}`: `» ${Translate('Sunday')} ${getTimebyFormate(data.sun_from)} to ${getTimebyFormate(data.sun_to)}`;
		
		let mapAddress;
		document.querySelector('.business_address').innerHTML = data.business_address;
		mapAddress = data.business_address.replace(' ', '-');
		mapAddress = mapAddress.replace(',', '-');
		document.querySelector('#mapAddress').src = 'http://maps.google.it/maps?q='+mapAddress+'&output=embed';

		homePreview2();

		document.getElementById('bg_color3').value = data.bg_color3;
		document.getElementById('color3').value = data.color3;
		document.getElementById('font_family3').value = data.font_family3;			

		document.getElementById('bd_one_details').innerHTML = data.bd_one_details;
		document.getElementById('bd_one_headline').innerHTML = `${data.bd_one_headline} <br> ${data.bd_one_subheadline}`;
		document.getElementById('bd_one_icon').setAttribute('class',`fa fa-${data.bd_one_icon}`);

		document.getElementById('bd_two_details').innerHTML = data.bd_two_details;
		document.getElementById('bd_two_headline').innerHTML = `${data.bd_two_headline} <br> ${data.bd_two_subheadline}`;
		document.getElementById('bd_two_icon').setAttribute('class',`fa fa-${data.bd_two_icon}`);

		document.getElementById('bd_three_details').innerHTML = data.bd_three_details;
		document.getElementById('bd_three_headline').innerHTML = `${data.bd_three_headline} <br> ${data.bd_three_subheadline}`;
		document.getElementById('bd_three_icon').setAttribute('class',`fa fa-${data.bd_three_icon}`);
		
		homePreview3();	
	}
			
	document.querySelector('#bg_color1').addEventListener('change', homePreview1);
	document.querySelector('#color1').addEventListener('change', homePreview1);
	document.querySelector('#font_family1').addEventListener('change', homePreview1);

	document.querySelector('#bg_color2').addEventListener('change', homePreview2);
	document.querySelector('#color2').addEventListener('change', homePreview2);
	document.querySelector('#font_family2').addEventListener('change', homePreview2);

	document.querySelector('#bg_color3').addEventListener('change', homePreview3);
	document.querySelector('#color3').addEventListener('change', homePreview3);
	document.querySelector('#font_family3').addEventListener('change', homePreview3);
}

async function AJsave_home_page_body(e){
	e.preventDefault();
	AJsave({
		btn_id: '#submit',
		form_id: '#frmHomePageBody',
		api_endpoint: 'AJsave_home_page_body'
	});
}

async function home_page_body() {
	let formRow;
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Home Page Body'));
		const divRow = cTag('div',{ 'class':'flexSpaBetRow' });
			let homePageColumn = cTag('div',{ 'class': 'columnSM12' });
				let divChild = cTag('div',{ 'class': 'innerContainer bs-callout-info','style': 'margin-top: 0; background: #fff' });				    
                    const homePageForm = cTag('form',{ 'name':'frmHomePageBody','id':'frmHomePageBody','action':'#','enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
					homePageForm.addEventListener('submit',AJsave_home_page_body);						
					homePageForm.appendChild(cTag('input',{ 'type':"hidden",'name':"onePicture",'id':"onePicture",'value':"" }));				
                    homePageForm.appendChild(color_font_controller({bgcLabel:Translate('Segment1 Background Color'),ids:['bg_color1','color1','font_family1']}));
						formRow = cTag('div',{ 'class':'flexSpaBetRow' });
							let homePreviewDiv = cTag('div',{ 'class':'columnSM12 roundborder','id':'homePreview1' });
								let homeSection = cTag('section',{ 'style':'width: 100%; background: #000075;padding: 130px 0; text-transform: uppercase;' });
									let homeContainer = cTag('div',{ 'class':'container' });
										let homePreviewRow = cTag('div',{ 'class':'flexSpaBetRow' });
											let homePreviewColumn = cTag('div',{ 'class':'homePreview1 columnSM7', 'style': "position: relative;", 'align':'left' });
											if(OS =='unknown'){
												homePreviewColumn.appendChild(cTag('h2',{ 'style':'font-size: 40px;','class':'mst_one' }));
												homePreviewColumn.appendChild(cTag('h1',{ 'style':'font-size: 80px;font-weight: 600;line-height: 80px;text-transform:uppercase','class':'mst_two_three' }));
												homePreviewColumn.appendChild(cTag('h2',{ 'style':'font-size: 40px;','class':'mst_four' }));
											}
											else{
												homePreviewColumn.appendChild(cTag('h2',{ 'style':'font-size: 25px;','class':'mst_one' }));
												homePreviewColumn.appendChild(cTag('h1',{ 'style':'font-size: 40px;font-weight: 600;line-height: 40px;','class':'mst_two_three' }));
												homePreviewColumn.appendChild(cTag('h2',{ 'style':'font-size: 25px;','class':'mst_four' }));
											}
										homePreviewRow.appendChild(homePreviewColumn);
											let homePreviewField = cTag('div',{ 'id':"home_page_body_picture",'class':"homePreview12 columnSM5" });
											homePreviewField.appendChild(cTag('div',{ 'class':'currentPicture' }));
										homePreviewRow.appendChild(homePreviewField);
									homeContainer.appendChild(homePreviewRow);
								homeSection.appendChild(homeContainer);
							homePreviewDiv.appendChild(homeSection);
						formRow.appendChild(homePreviewDiv);
					homePageForm.appendChild(formRow);						
                        
                    homePageForm.appendChild(color_font_controller({bgcLabel:Translate('Segment2 Background Color'),ids:['bg_color2','color2','font_family2']}));

						formRow = cTag('div',{ 'class':'flexSpaBetRow' });
							let cellularColumn = cTag('div',{ 'class':'columnSM12 roundborder','id':'homePreview2','style':'position: relative' });
								let cellularSection = cTag('section',{ 'style':'width: 100%; background: #f4f4f4;padding: 80px 0 100px;' });
									let cellularContainer = cTag('div',{ 'class':'container' });
										let cellularRow = cTag('div',{ 'class':'flexSpaBetRow' });
											let cellularTitle = cTag('div',{ 'class':'columnSM4 homePreview21', 'style': "position: relative; padding: 5px 15px;", 'align':'left' });
												let cellularHeader = cTag('h2',{ 'style':'text-transform: uppercase; font-size: 20px;' });
												cellularHeader.innerHTML = Translate('CELLULAR<br>Services');
											cellularTitle.appendChild(cellularHeader);
												let ulCellular = cTag('ul',{ id:'cellular_services_list','style':"margin-top: 10px; width: 100%;" });
												// for (let i = 1; i < 8; i++) {
												// 	ulCellular.appendChild(cTag('li',{ 'class':'cellular_services'+i,'style':"border-bottom: 1px solid #ffffff;list-style: none;line-height: 30px;" }));
												// }                                                
											cellularTitle.appendChild(ulCellular);
										cellularRow.appendChild(cellularTitle);
											let bussinessHourColumn = cTag('div',{ 'class':'columnSM4 homePreview22', 'style': "position: relative; padding: 5px 15px;", 'align':'left' });
												let bussinessHourTitle = cTag('h2',{ 'style':'text-transform: uppercase; font-size: 20px;' });
												bussinessHourTitle.innerHTML = Translate('Business<br>HOURS');
											bussinessHourColumn.appendChild(bussinessHourTitle);
												let ulBusiness = cTag('ul',{ 'id':'business_hours_list','style':"margin-top: 10px; width: 100%;" });
												for (let i = 1; i < 8; i++) {
													ulBusiness.appendChild(cTag('li',{ 'style':"border-bottom: 1px solid #ffffff;list-style: none;line-height: 30px;" }));
												}
											bussinessHourColumn.appendChild(ulBusiness);
										cellularRow.appendChild(bussinessHourColumn);
											let addressColumn = cTag('div',{ 'class':'columnSM4 homePreview23', 'style': "position: relative; padding: 5px 15px;", 'align':'left' });
												let addressHeader = cTag('h2',{ 'id':'business_address','style':'text-transform: uppercase; font-size: 20px;' });
												addressHeader.innerHTML = Translate('BUSINESS<br>ADDRESS');
											addressColumn.appendChild(addressHeader);
												let ulAddress = cTag('ul',{ 'style':"margin-top: 10px; width: 100%;" });
												ulAddress.appendChild(cTag('li',{ 'class':'business_address','style':"list-style: none; line-height: 30px" }));
											addressColumn.appendChild(ulAddress);
											addressColumn.appendChild(cTag('iframe',{ 'id':'mapAddress', 'width':'100%','height':'250','frameborder':'0','scrolling':'no','marginheight':'0','marginwidth':'0','src':"http://maps.google.it/maps?q=20-Whiteleas-Ave-Toronto-ON-Canada-m1b1w7&output=embed" }));
										cellularRow.appendChild(addressColumn);
									cellularContainer.appendChild(cellularRow);
								cellularSection.appendChild(cellularContainer);
							cellularColumn.appendChild(cellularSection);
						formRow.appendChild(cellularColumn);
					homePageForm.appendChild(formRow);
                        
                    homePageForm.appendChild(color_font_controller({bgcLabel:Translate('Segment3 Background Color'),ids:['bg_color3','color3','font_family3']}));

						formRow = cTag('div',{ 'class':'flexSpaBetRow' });
							let roundBorder = cTag('div',{ 'class':'columnSM12 roundborder','id':'homePreview3' });
								let previewSection = cTag('section',{ 'style':'width: 100%; background: #ffffff; padding: 70px 0 95px;' });
									let previewContainer = cTag('div',{ 'class':'container' });
										let previewRow = cTag('div',{ 'class':'flexSpaBetRow' });
										let id = ['one','two','three'];
										for(let indx=0;indx<3;indx++){
												let previewColumn = cTag('div',{ 'style': "padding: 5px 15px;", 'class':' columnXS12 columnSM4 homePreview3'+indx+1 });
													let previewId = cTag('div',{ 'class':`homePreview3${indx+1}1`, 'style': "position: relative; text-align: center;" });
													previewId.appendChild(cTag('div',{ 'style':'font-size: 100px;line-height: 100px;','id':`bd_${id[indx]}_icon` }));
												previewColumn.appendChild(previewId) ;
													let previewIndex = cTag('div',{ 'class':`homePreview3${indx+1}2`, 'style': "position: relative;" });
													previewIndex.appendChild(cTag('h3',{ 'id':`bd_${id[indx]}_headline`,'align':'center', 'style':"font-size: 20px; text-transform: uppercase; line-height: 25px; padding: 10px 0px;" }));
													previewIndex.appendChild(cTag('p',{ 'id':`bd_${id[indx]}_details`,'align':'left' }));
												previewColumn.appendChild(previewIndex);
											previewRow.appendChild(previewColumn);
										}
									previewContainer.appendChild(previewRow);
								previewSection.appendChild(previewContainer);
							roundBorder.appendChild(previewSection);
						formRow.appendChild(roundBorder);
					homePageForm.appendChild(formRow);
						formRow = cTag('div',{ 'class':'flexSpaBetRow' });
							let buttonColumn = cTag('div',{ 'class':'columnXS12', 'align':'center' });
							buttonColumn.appendChild(cTag('input',{ 'type':'hidden','name':'variables_id','id':'variables_id'}));
							buttonColumn.appendChild(cTag('input',{ 'class':'btn saveButton','type':'submit','name':'submit','id':'submit','value':'Save' }));
						formRow.appendChild(buttonColumn);
					homePageForm.appendChild(formRow);					
				divChild.appendChild(homePageForm);
			homePageColumn.appendChild(divChild);
		divRow.appendChild(homePageColumn);
	showTableData.appendChild(divRow);

	addCustomeEventListener('preview',homePreview1);
	AJ_home_page_body_MoreInfo();
}

// Footer Section
async function all_pages_footer(){
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('All pages footer'));
		const divRow = cTag('div',{ 'class':'flexSpaBetRow' });
			let footerColumn = cTag('div',{ 'class':'columnSM12'});
				let callOutDiv = cTag('div',{ 'class':'innerContainer bs-callout-info','style':"margin-top: 0; background: #fff;" });
					const footerForm = cTag('form',{ 'name':'frmAllPagesFooter','id':'frmAllPagesFooter','action':'#','enctype':"multipart/form-data",'method':'post','accept-charset':"utf-8" });
					footerForm.addEventListener('submit',AJsave_all_pages_footer);
                    footerForm.appendChild(color_font_controller({bgcLabel:Translate('Footer Background Color')}));

						let roundBorder = cTag('div',{ 'class':'columnSM12 roundborder','id':'footerPreview' });
							let footerStyle = cTag('footer',{ 'style':'width: 100%;' });
								let footerContainer = cTag('div',{ 'class':'container' });
									let addressRow = cTag('div',{ 'class':'flexSpaBetRow' });
										let addressColumn = cTag('div',{ 'class':'columnMD5' });
											let mapDiv = cTag('div',{ 'id':'name_address', 'class':"flex", 'style':"line-height: 17px;text-transform: uppercase;" });
											mapDiv.appendChild(cTag('i',{ 'class':'fa fa-map-marker','style':"width: 50px; font-size: 42px; line-height: 50px;" }));
										addressColumn.appendChild(mapDiv);
									addressRow.appendChild(addressColumn);
										let footerValue = cTag('div',{ 'class':'columnMD5' });
											let contactDiv = cTag('div',{'style':"display: flex; align-items: center; line-height: 17px; text-transform: uppercase;" });
											contactDiv.appendChild(cTag('i',{ 'class':'fa fa-mobile','style':"width: 50px; font-size: 42px; line-height: 50px;" }));
												let contactDiv2 = cTag('div');
													let telephoneDiv = cTag('div');
														let telephoneLabel = cTag('label');
														telephoneLabel.innerHTML = 'Telephone'+':';
													telephoneDiv.appendChild(telephoneLabel);
														let telephoneNo = cTag('a',{ 'style':"padding-left: 10px;",'href':'tel:+8801911718043','title':'+8801911718043','id':'company_phone_no'});
													telephoneDiv.appendChild(telephoneNo);
												contactDiv2.appendChild(telephoneDiv);
													let emailDiv = cTag('div');
														let emailLabel = cTag('label');
														emailLabel.innerHTML = 'E-mail'+':';
													emailDiv.appendChild(emailLabel);
														let emailAddress = cTag('a',{ 'style':"text-transform: uppercase; padding-left: 10px;", 'href':'mailto:mdshobhancse@gmail.com','title':'mdshobhancse@gmail.com','id':'customer_service_email'});
													emailDiv.appendChild(emailAddress);
												contactDiv2.appendChild(emailDiv);
											contactDiv.appendChild(contactDiv2);
										footerValue.appendChild(contactDiv);
									addressRow.appendChild(footerValue);
										let copyRightDiv = cTag('div',{ 'class':'columnMD2' });
										copyRightDiv.appendChild(cTag('div',{ 'style':"line-height: 17px; text-transform: uppercase; padding-left: 51px;",'id':'copyright' }));
									addressRow.appendChild(copyRightDiv);
								footerContainer.appendChild(addressRow);
							footerStyle.appendChild(footerContainer);
						roundBorder.appendChild(footerStyle);
					footerForm.appendChild(roundBorder);

						let buttonRow = cTag('div',{ 'class':'flexSpaBetRow' });
							let footerButton = cTag('div',{ 'class':'columnXS12', 'align':'center' });
							footerButton.appendChild(cTag('input',{ 'type':'hidden','name':'variables_id','id':'variables_id'}));
							footerButton.appendChild(cTag('input',{ 'class':'btn saveButton','type':'submit','name':'submit','id':'submit','value':'Save' }));
						buttonRow.appendChild(footerButton);
					footerForm.appendChild(buttonRow);
				callOutDiv.appendChild(footerForm);
			footerColumn.appendChild(callOutDiv);
        divRow.appendChild(footerColumn);
	showTableData.appendChild(divRow);

	AJ_all_pages_footer_MoreInfo();
}

async function AJ_all_pages_footer_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_all_pages_footer_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.getElementById('bg_color').value = data.bg_color;
		document.getElementById('color').value = data.color;
		document.getElementById('font_family').value = data.font_family;
		document.getElementById("variables_id").value = data.variables_id;

		document.querySelector('#name_address').append(data.company_name);
		document.querySelector('#name_address').appendChild(cTag('br'));
		document.querySelector('#name_address').append(data.business_address);
		
		document.querySelector('#company_phone_no').setAttribute('href', 'tel:'+data.company_phone_no);
		document.querySelector('#company_phone_no').setAttribute('title', data.company_phone_no);
		document.querySelector('#company_phone_no').setAttribute('style', 'padding-left: 10px;color:'+data.color);
		document.querySelector('#company_phone_no').innerHTML = data.company_phone_no;
		
		document.querySelector('#customer_service_email').setAttribute('href', 'mailto:'+data.customer_service_email);
		document.querySelector('#customer_service_email').setAttribute('title', data.customer_service_email);
		document.querySelector('#customer_service_email').setAttribute('style', 'text-transform: uppercase; padding-left: 10px;color:'+data.color);
		document.querySelector('#customer_service_email').innerHTML = data.customer_service_email;
		
		document.querySelector('#copyright').append(data.company_name);
		document.querySelector('#copyright').append(cTag('br'));
		document.querySelector('#copyright').append('© '+(new Date).getFullYear());
		
		footerPreview();	
	}
			
	document.querySelector('#bg_color').addEventListener('change', footerPreview);
	document.querySelector('#color').addEventListener('change', footerPreview);
	document.querySelector('#font_family').addEventListener('change', footerPreview);
}

function footerPreview(){
	let footerPreview = document.querySelector('footer');
	footerPreview.style.background = document.getElementById('bg_color').value;
	footerPreview.style.color = document.getElementById('color').value ;
	footerPreview.style.fontFamily = document.getElementById('font_family').value ;
}

async function AJsave_all_pages_footer(e){
	e.preventDefault();
	AJsave({
		btn_id: '#submit',
		form_id: '#frmAllPagesFooter',
		api_endpoint: 'AJsave_all_pages_footer'
	});
}

// Contact Us Segment
async function ContactUs(){
	let divForm; 
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Contact Us'));
		const contactUsRow = cTag('div',{ 'class':"flexSpaBetRow" });
			let contactUsColumn = cTag('div',{ 'class':"columnSM12"});
				let callout = cTag('div',{ 'class':"innerContainer bs-callout-info",'style':"margin-top: 0; background: #fff;" });
					const contactUsForm = cTag('form',{ 'name':"frmContactUs",'id':"frmContactUs",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
					contactUsForm.appendChild(color_font_controller({colSize:[6,6],freeSpace:false}));
					contactUsForm.appendChild(btn_color_font_controller());

						divForm = cTag('div',{ 'class':"flexCenterRow" });
							let roundBorder = cTag('div',{ 'class':"columnSM6 roundborder",'id':"previewContactUs" });
								let contactUsSection = cTag('section',{ 'style':"padding: 20px;" });
									let withUsTitle = cTag('div',{ 'class':"columnSM10" });
										let withUsHeader = cTag('h2',{ 'style':"border-bottom: 1px solid #363947; margin-bottom: 20px" });
											const strong = cTag('strong');
											strong.innerHTML = Translate('Get in touch with us');
										withUsHeader.appendChild(strong);
									withUsTitle.appendChild(withUsHeader);
								contactUsSection.appendChild(withUsTitle);
									let nameColumn = cTag('div',{ 'class':"columnSM10", 'style': "padding-right: 20px;" });
										let nameTitle = cTag('div',{ 'style':"height: 45px; padding-left: 10px; background: rgba(255, 255, 255, 0.7); border: 1px solid #ccc; line-height: 45px; margin-bottom: 20px;" });
										nameTitle.innerHTML = Translate('Name');
									nameColumn.appendChild(nameTitle);
								contactUsSection.appendChild(nameColumn);
									let emailColumn = cTag('div',{ 'class':"columnSM10", 'style': "padding-right: 20px;" });
										let emailTitle = cTag('div',{ 'style':"height: 45px; padding-left: 10px; background: rgba(255, 255, 255, 0.7); border: 1px solid #ccc; line-height: 45px; margin-bottom: 20px;" });
										emailTitle.innerHTML = Translate('Email');
									emailColumn.appendChild(emailTitle);
								contactUsSection.appendChild(emailColumn);

									let messageColumn = cTag('div',{ 'class':"columnSM10", 'style': "padding-right: 20px;" });
										let messageTitle = cTag('div',{ 'style':"height: 145px; padding-left: 10px; background: rgba(255, 255, 255, 0.7); border: 1px solid #ccc; line-height: 45px; margin-bottom: 20px;" });
										messageTitle.innerHTML = Translate('Message');
									messageColumn.appendChild(messageTitle);
								contactUsSection.appendChild(messageColumn);
									let buttonName = cTag('div',{ 'class':"columnXS12" });
										let saveButton = cTag('button',{ 'click':event=>event.preventDefault(),'style':"width: 132px; height: 51px; border: none; font-size: 14px; text-transform: uppercase;" });
										saveButton.innerHTML = Translate('Send Message');
									buttonName.appendChild(saveButton);
								contactUsSection.appendChild(buttonName);
							roundBorder.appendChild(contactUsSection);
						divForm.appendChild(roundBorder);
					contactUsForm.appendChild(divForm);
						divForm = cTag('div',{ 'class':"flexSpaBetRow " });
							let buttonColumn = cTag('div',{ 'class':"columnXS12", 'align':"center" });
							buttonColumn.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'id':"variables_id"}));
							buttonColumn.appendChild(cTag('input',{ 'class':"btn saveButton",'name':"submit",'id':"submitContactUs",'type':"submit",'value':" Save " }));
						divForm.appendChild(buttonColumn);
					contactUsForm.appendChild(divForm);
				callout.appendChild(contactUsForm);
			contactUsColumn.appendChild(callout);
		contactUsRow.appendChild(contactUsColumn);
    showTableData.appendChild(contactUsRow);

	AJ_ContactUs_MoreInfo();
}

async function AJ_ContactUs_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_ContactUs_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.getElementById('bg_color').value = data.bg_color;
		document.getElementById('color').value = data.color;
		document.getElementById('font_family').value = data.font_family;
		document.getElementById("variables_id").value = data.variables_id;

		document.getElementById('but_bg_color').value = data.but_bg_color;
		document.getElementById('but_color').value = data.but_color;
		document.getElementById('but_font_family').value = data.but_font_family;

		document.querySelector('#frmContactUs').addEventListener('submit',AJsave_ContactUs);
	
		document.querySelector('#bg_color').addEventListener('change', previewContactUs);
		document.querySelector('#color').addEventListener('change', previewContactUs);
		document.querySelector('#font_family').addEventListener('change', previewContactUs);

		document.querySelector('#but_bg_color').addEventListener('change', previewContactUs);
		document.querySelector('#but_color').addEventListener('change', previewContactUs);
		document.querySelector('#but_font_family').addEventListener('change', previewContactUs);
		
		previewContactUs();	
	}
}

function previewContactUs(){
	let previewContactUs = document.querySelector('#previewContactUs').querySelector('section');
	let previewButton = previewContactUs.querySelector('button');

	previewContactUs.style.background = document.getElementById('bg_color').value;
	previewContactUs.style.color = document.getElementById('color').value;
	previewContactUs.style.fontFamily = document.getElementById('font_family').value;

	previewButton.style.background =  document.getElementById('but_bg_color').value;
	previewButton.style.color = document.getElementById('but_color').value ;
	previewButton.style.fontFamily = document.getElementById('but_font_family').value;
}

async function AJsave_ContactUs(e){
	e.preventDefault();
	AJsave({
		btn_id: '#submitContactUs',
		form_id: '#frmContactUs',
		api_endpoint: 'AJsave_ContactUs'
	});
}					

// Customer Section
async function Customer(){
	let div171, div169, div168, div170, label;
	const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Display Add Customer Information'));
		const divRow = cTag('div',{ 'class':"flexSpaBetRow" });
			let divCol = cTag('div',{ 'class':"columnSM12" });
				let callOutDiv = cTag('div',{ 'class':"innerContainer bs-callout-info",'style':"margin-top: 0; background: #fff;" });
					const customerForm = cTag('form',{ 'name':"frmCustomer",'id':"frmCustomer",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
					customerForm.addEventListener('submit',AJsave_Customer);
                    customerForm.appendChild(color_font_controller({colSize:[5,4],freeSpace:false,customer:true}));
                    customerForm.appendChild(btn_color_font_controller());

						let customerRow = cTag('div',{ 'class':"flexCenterRow" });
							let customerColumn = cTag('div',{ 'class':"columnSM8 roundborder",'id':"previewCustomer" });
								let customerSection = cTag('section',{ 'style':"overflow: hidden; padding: 20px;" });
									let customerInfoRow = cTag('div',{ 'class':"columnSM12" });
										const customerInfo = cTag('h2',{ 'style':"border-bottom: 1px solid #363947" });
										customerInfo.innerHTML = Translate('Add Customer Information');
									customerInfoRow.appendChild(customerInfo);
								customerSection.appendChild(customerInfoRow);
									let basicInfoRow = cTag('div',{ 'class':"flexSpaBetRow" });
										let basicInfoColumn = cTag('div',{ 'class':"columnXS10 columnSM10" });
											let basicInfo = cTag('h4',{ 'style': "font-size: 18px;" });
											basicInfo.innerHTML = Translate('Basic Information');
										basicInfoColumn.appendChild(basicInfo);
									basicInfoRow.appendChild(basicInfoColumn);
										let visibleTitle = cTag('div',{ 'class':"columnXS2 columnSM2" });
											let visibleHeader = cTag('h4',{ 'style': "font-size: 18px;" });
											visibleHeader.innerHTML = Translate('Visible');
										visibleTitle.appendChild(visibleHeader);
									basicInfoRow.appendChild(visibleTitle);
								customerSection.appendChild(basicInfoRow);
								[
									{label:Translate('First Name')},
									{label:Translate('Last Name')},
									{label:Translate('Email Address'),id:'email',checkbox:true},
									{label:Translate('Offers Email'),id:'offers_email',checkbox:true},
									{label:Translate('Company'),id:'company',checkbox:true},
									{label:Translate('Phone No.'),id:'contact_no',checkbox:true},
									{label:Translate('Secondary Phone'),id:'secondary_phone',checkbox:true},
									{label:Translate('Fax'),id:'fax',checkbox:true}									
								].forEach((item,indx)=>{
										div171 = cTag('div');
										if(indx === 0) div171.setAttribute('class',"flexSpaBetRow");
										else {
											div171.setAttribute('class', "flexSpaBetRow");
											div171.setAttribute('style', "margin-top: 10px;");
										}
											div169 = cTag('div',{ 'class':"columnXS10 columnSM10" });
												div168 = cTag('div',{ 'style':"color: inherit;" });
												if(item.id) div168.setAttribute('class',"form-control "+item.id);
												else div168.setAttribute('class',"form-control");
												if(item.id === 'offers_email'){
													div168.appendChild(cTag('input',{ 'type':"checkbox",'value':"1" }));
													div168.append(' ');
												}
												div168.append(item.label);
											div169.appendChild(div168);
										div171.appendChild(div169);
										if(item.checkbox){
												div170 = cTag('div',{ 'class':"columnXS2 columnSM2" });
													label = cTag('label',{ 'for':item.id,'class':"cursor" });
													label.appendChild(cTag('input',{ 'id':item.id,'name':item.id,'class':"cursor customerCk", 'style': "margin-top: 10px;", 'type':"checkbox",'value':"1" }));
												div170.appendChild(label);
											div171.appendChild(div170);
										}
									customerSection.appendChild(div171);
								})
									
									let addressInfoDiv = cTag('div',{ 'class':"columnSM10", 'style': "margin-top: 10px;" });
										let addressInfoTitle = cTag('h4',{ 'style': "font-size: 18px;" });
										addressInfoTitle.innerHTML = Translate('Address Info');
									addressInfoDiv.appendChild(addressInfoTitle);
								customerSection.appendChild(addressInfoDiv);
								[
									{label:Translate('Address Line 1'),id:'shipping_address_one',checkbox:true},
									{label:Translate('Address Line 2'),id:'shipping_address_two',checkbox:true},
									{label:Translate('City / Town'),id:'shipping_city',checkbox:true},
									{label:Translate('State / Province'),id:'shipping_state',checkbox:true},
									{label:Translate('Zip/Postal Code'),id:'shipping_zip',checkbox:true},
									{label:Translate('Country'),id:'shipping_country',checkbox:true},
									{label:Translate('Website'),id:'website',checkbox:true},
								].forEach((item)=>{
										div171 = cTag('div',{ 'class':"flexSpaBetRow", 'style': "margin-top: 10px;" });
											div169 = cTag('div',{ 'class':"columnXS10 columnSM10" });
												div168 = cTag('div',{ 'style':"color: inherit;",'class':"form-control "+item.id });
												div168.innerHTML = item.label;
											div169.appendChild(div168);
										div171.appendChild(div169);
										if(item.checkbox){
												div170 = cTag('div',{ 'class':"columnXS2 columnSM2"});
													label = cTag('label',{ 'for':item.id,'class':"cursor" });
														let input = cTag('input',{ 'id':item.id,'name':item.id,'class':"cursor customerCk", 'style': "margin-top: 10px;", 'type':"checkbox",'value':"1" });
													label.appendChild(input);
												div170.appendChild(label);
											div171.appendChild(div170);
										}
									customerSection.appendChild(div171);
								})
							customerColumn.appendChild(customerSection);
						customerRow.appendChild(customerColumn);
					customerForm.appendChild(customerRow);

						let saveButtonRow = cTag('div',{ 'class':"flexSpaBetRow" });
							let sumbitButton = cTag('div',{ 'class':"columnXS12", 'align':"center" });
							sumbitButton.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'id':"variables_id"}));
							sumbitButton.appendChild(cTag('input',{ 'class':"btn saveButton",'name':"submit",'id':"submitCustomer",'type':"submit",'value':" Save " }));
						saveButtonRow.appendChild(sumbitButton);
					customerForm.appendChild(saveButtonRow);
				callOutDiv.appendChild(customerForm);
            divCol.appendChild(callOutDiv);
        divRow.appendChild(divCol);
    showTableData.appendChild(divRow);

	AJ_Customer_MoreInfo();
}

async function AJ_Customer_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_Customer_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let display_add_customer = document.getElementById('display_add_customer')
		if(data.display_add_customer){
			display_add_customer.value = data.display_add_customer;
			display_add_customer.checked = true;
		}else{
			display_add_customer.value = data.display_add_customer;
			display_add_customer.checked = false;
		}
		
		document.getElementById('bg_color').value = data.bg_color;
		document.getElementById('color').value = data.color;
		document.getElementById('font_family').value = data.font_family;
		document.getElementById("variables_id").value = data.variables_id;

		document.getElementById('but_bg_color').value = data.but_bg_color;
		document.getElementById('but_color').value = data.but_color;
		document.getElementById('but_font_family').value = data.but_font_family;

		let section = document.querySelector('#previewCustomer').querySelector('section')			
		
		let divRow, divCol, div, option, input;
		if(data.customFields.length>0){
				let xtraInformationRow = cTag('div',{ 'class':"columnSM10", 'style': "margin-top: 10px;" });
					let xtraInformationTitle = cTag('h4',{ 'style': "font-size: 18px;" });
					xtraInformationTitle.innerHTML = Translate('Extra Information');
				xtraInformationRow.appendChild(xtraInformationTitle);
			section.appendChild(xtraInformationRow);
		
			data.customFields.forEach(field=>{	
				if(!['Picture','PDF'].includes(field.field_type)){
					divRow = cTag('div',{ 'class':"flexSpaBetRow", 'style': "margin-top: 10px;" });
						divCol = cTag('div',{ 'class':"columnXS10 columnSM10" });
							if(field.field_type === "DropDown"){
									div = cTag('div',{ 'style':"color: inherit;" });
										let select = cTag('select',{ 'class':`form-control cf${field.custom_fields_id}` });
											select.classList.add('is-disabled');
											//default option
											option = cTag('option',{ 'value':"" });
											option.innerHTML = 'Select '+field.field_name;
										select.appendChild(option);
											//others parameters option 
											field.parameters.split('||').forEach(parameter=>{
												option = cTag('option',{ 'value':parameter });
												option.innerHTML = parameter;
											select.appendChild(option);
											})									
									div.appendChild(select);
								divCol.appendChild(div);
							}								
							else{
								div = cTag('div',{ 'class':`form-control cf${field.custom_fields_id}`,'style':"color: inherit;" });
								div.classList.add('is-disabled');
								if(field.field_type==="Checkbox") div.append(cTag('input',{'type':'checkbox'}),' ');
								div.append(field.field_name+(field.field_required?' *':'')); 
								divCol.appendChild(div);
							}
					divRow.appendChild(divCol);
						divCol = cTag('div',{ 'class':"columnXS2 columnSM2" });
							let label = cTag('label',{ 'for':`cf${field.custom_fields_id}`,'class':"cursor" });
								input = cTag('input',{ 'id':`cf${field.custom_fields_id}`,'name':`cf${field.custom_fields_id}`,'class':"cursor customerCk", 'style': "margin-top: 10px;", 'type':"checkbox",'value':field.custom_fields_id });
								input.onclick = function(){
									document.querySelector(`.cf${field.custom_fields_id}`).classList.toggle('is-disabled');
								}
							label.appendChild(input);
						divCol.appendChild(label);
					divRow.appendChild(divCol);
				section.appendChild(divRow);
				}				
			})
		}

			let buttonName = cTag('div',{ 'class':"flexSpaBetRow", 'style': "margin-top: 10px;" });
				let buttonColumn = cTag('div',{ 'class':"columnXS10 columnSM8 columnMD5" });
				buttonColumn.appendChild(cTag('input',{ 'type':"hidden",'name':"customFieldCount",'value':"4" }));
					let saveButton = cTag('button',{ 'click':event=>event.preventDefault(),'style':" width: 132px; height: 51px; border: none; font-size: 14px; text-transform: uppercase; border-radius: 4px;" });
					saveButton.innerHTML = Translate('Save');
				buttonColumn.appendChild(saveButton);
			buttonName.appendChild(buttonColumn);
		section.appendChild(buttonName);

		// check fields...
		for (const field in data.fieldNames) {
			let formControll = document.querySelector(`.${field}`);
			input = document.querySelector(`#${field}`);					

			if(formControll && input){
				if(!data.fieldNames[field]){
					formControll.classList.add('is-disabled');
					input.checked = false;
				}else{
					formControll.classList.remove('is-disabled');
					input.checked = true;
				}
			}
			if(input){
				input.onclick = function(){
					document.querySelector(`.${field}`).classList.toggle('is-disabled');
				}
			}
		}
		previewCustomer();
	}
			
	document.querySelector('#bg_color').addEventListener('change', previewCustomer);
	document.querySelector('#color').addEventListener('change', previewCustomer);
	document.querySelector('#font_family').addEventListener('change', previewCustomer);

	document.querySelector('#but_bg_color').addEventListener('change', previewCustomer);
	document.querySelector('#but_color').addEventListener('change', previewCustomer);
	document.querySelector('#but_font_family').addEventListener('change', previewCustomer);
}

function previewCustomer(){
	let bg_color = document.getElementById('bg_color').value ;
	let color = document.getElementById('color').value;
	let font_family = document.getElementById('font_family').value ;

	let but_bg_color = document.getElementById('but_bg_color').value ;
	let but_color = document.getElementById('but_color').value ;
	let but_font_family = document.getElementById('but_font_family').value ;

	let previewCustomer = document.querySelector('#previewCustomer');
	let previewButton = previewCustomer.querySelector('button');
	previewCustomer.setAttribute('style',`background:${bg_color};color:${color};font-Family:${font_family}`);
	if(previewButton){
		previewButton.style.background = but_bg_color;
		previewButton.style.color = but_color;
		previewButton.style.fontFamily = but_font_family;	
	}
}

async function AJsave_Customer(e){
	e.preventDefault()
	AJsave({
		btn_id: '#submitCustomer',
		form_id: '#frmCustomer',
		api_endpoint: 'AJsave_Customer'
	});
}

// Service Section
async function services(){
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Services'));
		const divRow = cTag('div',{ 'class':"flexSpaBetRow" });
            let servicesColumn = cTag('div',{ 'class':"columnXS12" });
                let callout = cTag('div',{ 'class':"innerContainer",'style':"margin-top: 0; background: #fff;" });
                    const form = cTag('form',{ 'name':"frmServices",'id':"frmServices",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                        let servicesRow = cTag('div',{ 'class':"flexStartRow" });
						[
							{label:Translate('Display Services Link'),id:'display_services'},
							{label:Translate('Display Prices'),id:'display_services_prices'},
							{label:Translate('Enable Paypal Ecommerce'),id:'enable_services_paypal'},
						].forEach(item=>{
								let div = cTag('div',{ 'class':"columnSM2" });
									let label = cTag('label',{ 'for':item.id,'style':'cursor:pointer' });
									label.appendChild(cTag('input',{ 'type':"checkbox",'value':"1",'id':item.id,'name':item.id,'click':()=>update_instance_home(1, item.id)}));
									label.append(' '+item.label);
								div.appendChild(label);
							servicesRow.appendChild(div);					
						}) 
                        servicesRow.appendChild(mail_currency_form());
                    form.appendChild(servicesRow);
				callout.appendChild(form);
			servicesColumn.appendChild(callout);
        divRow.appendChild(servicesColumn);
    showTableData.appendChild(divRow);

	AJ_services_MoreInfo();
}

async function AJ_services_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_services_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let display_services = document.getElementById('display_services');
		let display_services_prices = document.getElementById('display_services_prices');
		let enable_services_paypal = document.getElementById('enable_services_paypal');
		let paypal_email = document.getElementById('paypal_email');
		let currency_code = document.getElementById('currency_code');

		data.display_services === 1? display_services.checked = true: display_services.checked = false;
		data.display_services_prices === 1? display_services_prices.checked = true: display_services_prices.checked = false;
		data.enable_services_paypal === 1? enable_services_paypal.checked = true: enable_services_paypal.checked = false;
		paypal_email.value = data.paypal_email;
		currency_code.value = data.currency_code;
		
		checkServicesChecks();
	}
	document.querySelector('#frmServices').addEventListener('submit',AJsave_Services);
}

function checkServicesChecks(){
	let display_services = document.getElementById('display_services');
	let display_services_prices = document.getElementById('display_services_prices');
	let enable_services_paypal = document.getElementById('enable_services_paypal');
	let paypalField = document.getElementById('paypalField');

	if(display_services){
		if(display_services_prices){
			if(display_services.checked){
				display_services_prices.disabled = false
			}else{
				display_services_prices.disabled = true
				display_services_prices.checked = false
			}
		}
	}
	if(display_services_prices){
		if(enable_services_paypal){
			if(display_services_prices.checked){
				enable_services_paypal.disabled = false
			}else{
				enable_services_paypal.disabled = true
				enable_services_paypal.checked = false
			}
		}
	}
	if(enable_services_paypal){
		if(paypalField){
			if(enable_services_paypal.checked){
				if(paypalField.style.display === 'none'){
					paypalField.style.display = '';
				}
			}else{
				if(paypalField.style.display !== 'none'){
					paypalField.style.display = 'none';
				}
			}
		}
	}
}

async function AJsave_Services(e){
	e.preventDefault();
	AJsave({
		btn_id: '#btnSubmit',
		form_id: '#frmServices',
		api_endpoint: 'AJsave_Product'
	});
}

// Product Section
async function products(){
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Products'));
		const productRow = cTag('div',{ 'class':"flexSpaBetRow" });
            let productColumn = cTag('div',{ 'class':"columnXS12"});
                let callOutDiv = cTag('div',{ 'class':"innerContainer",'style':"margin-top: 0; background: #fff;" });
                    const form = cTag('form',{ 'name':"frmProduct",'id':"frmProduct",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                        let productDiv = cTag('div',{ 'class':"flexStartRow" });
						[
							{label:Translate('Display Product Link'),id:'display_products'},
							{label:Translate('Display Prices'),id:'display_products_prices'},
							{label:Translate('Enable Paypal Ecommerce'),id:'enable_product_paypal'},
						].forEach(item=>{
								let productValue = cTag('div',{ 'class':"columnSM2" });
									let label = cTag('label',{ 'for':item.id,'style':'cursor:pointer' });
									label.appendChild(cTag('input',{ 'type':"checkbox",'value':"1",'id':item.id,'name':item.id,'click':()=>update_instance_home(1,item.id)}));
									label.append(' '+item.label);
								productValue.appendChild(label);
							productDiv.appendChild(productValue);
						})
                        productDiv.appendChild(mail_currency_form());
                    form.appendChild(productDiv);
				callOutDiv.appendChild(form);
			productColumn.appendChild(callOutDiv);
		productRow.appendChild(productColumn);
    showTableData.appendChild(productRow);

	AJ_products_MoreInfo();
}

async function AJ_products_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_products_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let display_products = document.getElementById('display_products');
		let display_products_prices = document.getElementById('display_products_prices');
		let enable_product_paypal = document.getElementById('enable_product_paypal');
		let paypal_email = document.getElementById('paypal_email');
		let currency_code = document.getElementById('currency_code');

		data.display_products === 1? display_products.checked = true: display_products.checked = false;
		data.display_products_prices === 1? display_products_prices.checked = true: display_products_prices.checked = false;
		data.enable_product_paypal === 1? enable_product_paypal.checked = true: enable_product_paypal.checked = false;
		paypal_email.value = data.paypal_email;
		currency_code.value = data.currency_code;
		
		checkProductChecks()
	}
	document.querySelector('#frmProduct').addEventListener('submit', AJsave_Product);
}

async function checkProductChecks(){
	let display_products = document.getElementById('display_products');
	let display_products_prices = document.getElementById('display_products_prices');
	let enable_product_paypal = document.getElementById('enable_product_paypal');
	let paypalField = document.getElementById('paypalField');

	if(display_products){
		if(display_products_prices){
			if(display_products.checked){
				display_products_prices.disabled = false
			}else{
				display_products_prices.disabled = true
				display_products_prices.checked = false
			}
		}
	}
	if(display_products_prices){
		if(enable_product_paypal){
			if(display_products_prices.checked){
				enable_product_paypal.disabled = false
			}else{
				enable_product_paypal.disabled = true
				enable_product_paypal.checked = false
			}
		}
	}
	if(enable_product_paypal){
		if(paypalField){
			if(enable_product_paypal.checked){
				if(paypalField.style.display === 'none'){
					paypalField.style.display = '';
				}
			}else{
				if(paypalField.style.display !== 'none'){
					paypalField.style.display = 'none';
				}
			}
		}
	}
}

async function AJsave_Product(e){
	e.preventDefault();
	AJsave({
		btn_id: '#btnSubmit',
		form_id: '#frmProduct',
		api_endpoint: 'AJsave_Product'
	});
}

// Mobile Devices Section
async function cell_phones(){
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Mobile Devices'));
		const cellPhoneRow = cTag('div',{ 'class':"flexSpaBetRow" });
            let cellPhoneColumn = cTag('div',{ 'class':"columnXS12" });
                let callOutDiv = cTag('div',{ 'class':"innerContainer",'style':"margin-top: 0; background: #fff;" });
                    const form = cTag('form',{ 'name':"frmCellPhones",'id':"frmCellPhones",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
                        let cellPhoneDiv = cTag('div',{ 'class':"flexStartRow" });
						[
							{label:Translate('Display Mobile Devices Link'),id:'display_inventory'},
							{label:Translate('Display Prices'),id:'display_cell_prices'},
							{label:Translate('Enable Paypal Ecommerce'),id:'enable_cell_paypal'},
						].forEach(item=>{
								let cellPhoneField = cTag('div',{ 'class':"columnSM2" });
									let label = cTag('label',{ 'for':item.id,'style':'cursor:pointer' });
									label.appendChild(cTag('input',{ 'type':"checkbox",'value':"1",'id':item.id,'name':item.id,'click':()=>update_instance_home(1, item.id)}));
									label.append(' '+item.label);
								cellPhoneField.appendChild(label);
							cellPhoneDiv.appendChild(cellPhoneField);					
						})
                        cellPhoneDiv.appendChild(mail_currency_form());
                    form.appendChild(cellPhoneDiv);
				callOutDiv.appendChild(form);
			cellPhoneColumn.appendChild(callOutDiv);
		cellPhoneRow.appendChild(cellPhoneColumn);
   	showTableData.appendChild(cellPhoneRow);

	AJ_cell_phones_MoreInfo();
}

async function AJ_cell_phones_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_cell_phones_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let display_inventory = document.getElementById('display_inventory');
		let display_cell_prices = document.getElementById('display_cell_prices');
		let enable_cell_paypal = document.getElementById('enable_cell_paypal');
		let paypal_email = document.getElementById('paypal_email');
		let currency_code = document.getElementById('currency_code');

		data.display_inventory === 1? display_inventory.checked = true: display_inventory.checked = false;
		data.display_cell_prices === 1? display_cell_prices.checked = true: display_cell_prices.checked = false;
		data.enable_cell_paypal === 1? enable_cell_paypal.checked = true: enable_cell_paypal.checked = false;
		paypal_email.value = data.paypal_email;
		currency_code.value = data.currency_code;
		
		checkCellChecks()
	}
	document.querySelector('#frmCellPhones').addEventListener('submit',AJsave_cell_phones);
}

async function checkCellChecks(){
	let display_inventory = document.getElementById('display_inventory');
	let display_cell_prices = document.getElementById('display_cell_prices');
	let enable_cell_paypal = document.getElementById('enable_cell_paypal');
	let paypalField = document.getElementById('paypalField');

	if(display_inventory){
		if(display_cell_prices){
			if(display_inventory.checked){
				display_cell_prices.disabled = false
			}else{
				display_cell_prices.disabled = true
				display_cell_prices.checked = false
			}
		}
	}
	if(display_cell_prices){
		if(enable_cell_paypal){
			if(display_cell_prices.checked){
				enable_cell_paypal.disabled = false
			}else{
				enable_cell_paypal.disabled = true
				enable_cell_paypal.checked = false
			}
		}
	}
	if(enable_cell_paypal){
		if(paypalField){
			if(enable_cell_paypal.checked){
				if(paypalField.style.display === 'none'){
					paypalField.style.display = '';
				}
			}else{
				if(paypalField.style.display !== 'none'){
					paypalField.style.display = 'none';
				}
			}
		}
	}	
}

async function AJsave_cell_phones(e){  
	e.preventDefault();
	AJsave({
		btn_id: '#btnSubmit',
		form_id: '#frmCellPhones',
		api_endpoint: 'AJsave_Product'
	});
}

// Quote Section
async function Quote(){
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Request a Quote'));
		const quoteRow = cTag('div',{ 'class':"flexSpaBetRow" });
			let quoteColumn = cTag('div',{ 'class':"columnSM12"});
				let calloutDiv = cTag('div',{ 'class':"innerContainer bs-callout-info",'style':"margin-top: 0; background: #fff;" });
					const form = cTag('form',{ 'name':"frmQuote",'id':"frmQuote",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
		            form.appendChild(color_font_controller({colSize:[5,4],freeSpace:false,quote:true}));
						let roundBorderDiv = cTag('div',{ 'class':"columnSM12 roundborder",'id':"previewQuote" });
							let section = cTag('section',{ 'style':"padding: 20px 0; " });
								let quoteColumn25 = cTag('div',{ 'class':"columnXS12" });
									let quoteRow4 = cTag('div',{ 'class':"flexSpaBetRow" });
									[
										Translate('Name'),Translate('Phone No.'),Translate('Email'),Translate('Brand and model of device'),Translate('Problem'),Translate('SEND QUOTE')
									].forEach((label,indx)=>{
											let QuoteDiv13 = cTag('div');
											if(indx < 4) QuoteDiv13.setAttribute('class',"columnSM6 columnXS12");
											else QuoteDiv13.setAttribute('class',"columnXS12");
												let div12 = cTag('div');
												if(indx===4) div12.setAttribute('style',"height: 145px; padding-left: 10px; background: rgba(255, 255, 255, 0.7); border: 1px solid #ccc; line-height: 45px; margin-bottom: 20px; ");
												else if(indx===5) div12.setAttribute('style'," float: left; height: 50px; background: #ef7f1b; color: #fff; font-family: 'Arial'; font-size: 15px; line-height: 50px; text-align: center; padding: 0 40px; border-radius: 4px;");
												else div12.setAttribute('style',"height: 45px; padding-left: 10px; background: rgba(255, 255, 255, 0.7); border: 1px solid #ccc; line-height: 45px;");
												div12.innerHTML = label;
											QuoteDiv13.appendChild(div12);
										quoteRow4.appendChild(QuoteDiv13);
									})
								quoteColumn25.appendChild(quoteRow4);
							section.appendChild(quoteColumn25);
						roundBorderDiv.appendChild(section);
					form.appendChild(roundBorderDiv);
						let buttonRow = cTag('div',{ 'class':"flexSpaBetRow" });
							let saveButton = cTag('div',{ 'class':"columnXS12", 'align':"center" });
							saveButton.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'id':"variables_id"}));
							saveButton.appendChild(cTag('input',{ 'class':"btn saveButton",'name':"submit",'id':"submitQuote",'type':"submit",'value':" Save " }));
						buttonRow.appendChild(saveButton);
					form.appendChild(buttonRow);
				calloutDiv.appendChild(form);
			quoteColumn.appendChild(calloutDiv);
		quoteRow.appendChild(quoteColumn);
	showTableData.appendChild(quoteRow);

	AJ_Quote_MoreInfo();
}

async function AJ_Quote_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_Quote_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.request_a_quote){
			document.getElementById('request_a_quote').checked = true
		}else{
			document.getElementById('request_a_quote').checked = false
		}

		let bg_colorQuote = document.getElementById('bg_color')
		bg_colorQuote.value = data.bg_color
		bg_colorQuote.addEventListener('change',previewQuote)
		document.getElementById("variables_id").value = data.variables_id;
		
		let colorQuote = document.getElementById('color')
		colorQuote.value = data.color
		colorQuote.addEventListener('change',previewQuote)
		
		let font_familyQuote = document.getElementById('font_family')
		font_familyQuote.value = data.font_family
		font_familyQuote.addEventListener('change',previewQuote)

		previewQuote();
	}
	document.querySelector('#frmQuote').addEventListener('submit',AJsave_Quote);
}

async function previewQuote(){
	let previewQuoteSection = document.getElementById('previewQuote').querySelector('section')

	previewQuoteSection.style.background = document.getElementById('bg_color').value
	previewQuoteSection.style.color = document.getElementById('color').value
	previewQuoteSection.style.fontFamily = document.getElementById('font_family').value
}

async function AJsave_Quote(e){
	e.preventDefault();
	AJsave({
		btn_id: '#submitQuote',
		form_id: '#frmQuote',
		api_endpoint: 'AJsave_Quote'
	});
}

// Appointment part
async function Appointment(){
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Repair Appointment'));
		let appointmentContainer = cTag('div',{ 'class':"flexSpaBetRow" });
            let appointmentDiv = cTag('div',{ 'class':"columnSM12"});
                let calloutDiv = cTag('div',{ 'class':"innerContainer bs-callout-info",'style':"margin-top: 0; border-left: 1px solid #eeeeee; background: #fff" });
                    const form = cTag('form',{ 'name':"frmAppointment",'id':"frmAppointment",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
					form.appendChild(color_font_controller({colSize:[5,4],freeSpace:false,appointment:true}));
                    form.appendChild(btn_color_font_controller());

						let roundBorder = cTag('div',{ 'class':"columnSM12 roundborder",'id':"previewAppointment" });
							let appointmentSection = cTag('section',{ 'style':" width: 100%; background: #ffffff; padding: 20px 0; " });
								let appointmentRow = cTag('div',{ 'class':"flexStartRow columnXS12", 'style': "margin: 0;" });
									let appointmentTitle = cTag('div',{ 'class':"columnSM3" });
										let appointmentHeader = cTag('h4', {'style': "font-size: 18px;"});
										appointmentHeader.innerHTML = 'Appointment'+Translate('Form Fields')+':';
									appointmentTitle.appendChild(appointmentHeader);
								appointmentRow.appendChild(appointmentTitle);
									let div44 = cTag('div',{ 'class':"columnSM5 plusIconPosition" });
									div44.appendChild(cTag('ul',{ 'id':"AFNListRow",'class':"multipleRowList" }));
									div44.appendChild(cTag('span',{ 'id':"errorAFNListRow",'class':"errormsg" }));
										let div43 = cTag('div',{ 'class':"addNewPlusIcon" });
											let aTag = cTag('a',{ 'href':"javascript:void(0);",'title':"Add New appointment field",'click':addMoreAFN});
											aTag.appendChild(cTag('img',{ 'align':"absmiddle",'alt':"Add New appointment field",'title':"Add New appointment field",'src':"/assets/images/plus20x25.png" }));
										div43.appendChild(aTag);
									div44.appendChild(div43);
								appointmentRow.appendChild(div44);
							appointmentSection.appendChild(appointmentRow);
								let schedulesRow = cTag('div',{ 'class':"flexSpaBetRow columnXS12", 'style': "padding-top: 20px;" });
									let schedulesTitle = cTag('div',{ 'class':"columnSM12 columnMD2" });
										let schedulesHeader = cTag('h4', {'style': "font-size: 18px;"});
										schedulesHeader.innerHTML = Translate('Set Appointment Schedules:');
									schedulesTitle.appendChild(schedulesHeader);
								schedulesRow.appendChild(schedulesTitle);
									let appointmentScheduleDiv = cTag('div',{ 'class':"flex columnSM12 columnMD10" });
									appointmentScheduleDiv.appendChild(cTag('div',{ 'id':"tabs", 'style': "border-radius: 6px;" }));
								schedulesRow.appendChild(appointmentScheduleDiv);
									let BlockOutDatesTitle = cTag('h4', {'style': "font-size: 18px;margin-top:50px",'class':"columnXS5 columnMD2"});
									BlockOutDatesTitle.innerHTML = Translate('Set Block Out Dates:');
								schedulesRow.appendChild(BlockOutDatesTitle);
									let BlockOutDates = cTag('div',{ 'class':"flex columnXS7 columnMD10",'style':'margin-top:50px' });
										let BlockOutDatePicker = cTag('div',{ 'class':`input-group` });
										BlockOutDatePicker.append(cTag('input',{ 'keydown':(event)=>event.preventDefault(),'id':"blockoutDatePicker",'class':`form-control`,'placeholder':'Add New Block-out Date' }));
											let addBlockOutDateBtn = cTag('span',{ 'class':`input-group-addon cursor` });
											addBlockOutDateBtn.addEventListener('click',()=>{
												let date = ViewDateToDBDate(document.getElementById('blockoutDatePicker').value);
												let dateCanBeAddedToLists = checkBlockOutDate(date);
												if(dateCanBeAddedToLists) addBlockOutDate(date);
											});
											addBlockOutDateBtn.append(cTag('i',{ 'class':`fa fa-plus` }),' '+Translate('Add Date'));
										BlockOutDatePicker.append(addBlockOutDateBtn);
									BlockOutDates.appendChild(BlockOutDatePicker);
								schedulesRow.appendChild(BlockOutDates);
								schedulesRow.append(cTag('div',{ 'class':'columnXS12 columnMD2' }));
								schedulesRow.append(cTag('div',{ 'id':`blockoutDateLists`,'class':'flexStartRow columnXS12 columnMD10','style':'gap:5px' }));
									let appointmentColumn = cTag('div',{ 'class':"columnXS12",'style':'margin-top:50px' });
										let  appointmentButton = cTag('button',{ 'click':event=>event.preventDefault(),'style':" width: 200px; height: 51px; background: #ef7f1b; border: none; color: #ffffff; font-family: 'Arial'; font-size: 14px; text-transform: uppercase; border-radius: 4px;" });
										appointmentButton.innerHTML = Translate('SEND APPOINTMENT');
									appointmentColumn.appendChild(appointmentButton);
								schedulesRow.appendChild(appointmentColumn);
							appointmentSection.appendChild(schedulesRow);
						roundBorder.appendChild(appointmentSection);
                    form.appendChild(roundBorder);

						let buttonName = cTag('div',{ 'class':"flexSpaBetRow" });
                            let submitButton = cTag('div',{ 'class':"columnXS12", 'align':"center" });
                            submitButton.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'id':"variables_id"}));
                            submitButton.appendChild(cTag('input',{ 'class':"btn saveButton",'name':"submit",'id':"submitAppointment",'type':"submit",'value':" Save " }));
						buttonName.appendChild(submitButton);
                    form.appendChild(buttonName);
				calloutDiv.appendChild(form);
			appointmentDiv.appendChild(calloutDiv);
		appointmentContainer.appendChild(appointmentDiv);
    showTableData.appendChild(appointmentContainer);
	date_picker('#blockoutDatePicker');

	AJ_Appointment_MoreInfo();
}

async function AJ_Appointment_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_Appointment_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.mobile_repair_appointment){
			document.getElementById('mobile_repair_appointment').checked = true;
		}else{
			document.getElementById('mobile_repair_appointment').checked = false;
		}
		document.getElementById('variables_id').value = data.variables_id;
		//section part
		let bg_colorAppointment = document.getElementById('bg_color');
		bg_colorAppointment.value = data.bg_color;
		bg_colorAppointment.addEventListener('change',previewAppointment);
		
		let colorAppointment = document.getElementById('color');
		colorAppointment.value = data.color;
		colorAppointment.addEventListener('change',previewAppointment);
		
		let font_familyAppointment = document.getElementById('font_family');
		font_familyAppointment.value = data.font_family;
		font_familyAppointment.addEventListener('change',previewAppointment);

		//button part
		let but_bg_colorAppointment = document.getElementById('but_bg_color');
		but_bg_colorAppointment.value = data.but_bg_color;
		but_bg_colorAppointment.addEventListener('change',previewAppointment);
		
		let but_colorAppointment = document.getElementById('but_color');
		but_colorAppointment.value = data.but_color;
		but_colorAppointment.addEventListener('change',previewAppointment);
		
		let but_font_familyAppointment = document.getElementById('but_font_family');
		but_font_familyAppointment.value = data.but_font_family;
		but_font_familyAppointment.addEventListener('change',previewAppointment);

		appointment_form_fields(data);
        appointment_schedule(data);
		createTabs(document.getElementById('tabs'));
		previewAppointment();
		data.blockoutDates.sort().forEach(date=>addBlockOutDate(date));
	}
	document.querySelector('#frmAppointment').addEventListener('submit',AJsave_Appointment);
}

function appointment_form_fields(data){
    let ul = document.getElementById('AFNListRow')
	let AFN_Index = 1
    function fieldCreator(fields){
		fields.forEach((field)=>{
				let li = cTag('li',{ 'style': "padding-right: 10px;" });
					let appointmentFormRow = cTag('div',{ 'class':"flexSpaBetRow" });
					appointmentFormRow.appendChild(cTag('div',{ 'class':"columnXS2 flexSpaBetRow" }));
						let appointmentDivCol = cTag('div',{ 'class':"columnXS10" });
							let inputField = cTag('input',{ 'type':"text",'maxlength':"25",'placeholder':`Appointment field ${AFN_Index}`,'alt':`Appointment field ${AFN_Index}`,'title':`${field}`,'name':"fieldNames[]",'value':`${field}`,'class':"form-control placeholder fieldNames" });
							if(data.requiredFields.includes(field))	inputField.setAttribute('readonly',"");
						appointmentDivCol.appendChild(inputField);
					appointmentFormRow.appendChild(appointmentDivCol);
				li.appendChild(appointmentFormRow);
			ul.appendChild(li);
			AFN_Index++ ;
		})
	}
	fieldCreator(data.requiredFields)
	fieldCreator(data.fieldNames.filter(field=> !data.requiredFields.includes(field)))
	addMoreAFN()
}

async function appointment_schedule(data){
	const weekDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']
	let activeTab;
	let activeTabpanel;
	let tabs = document.getElementById('tabs');		
		let ul = cTag('ul');
        weekDays.forEach((day,indx)=>{
			let li = cTag('li');
				let aTag = cTag('a',{ 'href':`#tabs-${indx+1}`,'style':'width:max-content'});
				aTag.append(`${day}`);  
				if(Object.keys(data.schedules).includes(day) && data.schedules[day][0]===1){
					aTag.appendChild(cTag('i',{ 'class':"fa fa-check" })); 
                }
			li.appendChild(aTag);
		    ul.appendChild(li);	
        })                             
	tabs.appendChild(ul);
	tabs.appendChild(cTag('div',{ 'id':"error_customer",'class':"errormsg" }));
	
	let input, tr, th;
    weekDays.forEach((day,indx)=>{        
		let div5 = cTag('div',{'id':`tabs-${indx+1}`});
			let div2 = cTag('div',{ 'class':"flexSpaBetRow" });
				let div1 = cTag('div',{ 'class':"columnSM12",'align':"left" });
					let label = cTag('label',{ 'class':"cursor",'for':`weekDays_${indx+1}` });
						input = cTag('input',{ 'type':"checkbox",'name':`weekDays_${indx+1}`,'id':`weekDays_${indx+1}`,'value':"1" });
						if(Object.keys(data.schedules).includes(day) && data.schedules[day][0]===1) input.checked = true;
					label.appendChild(input);
					label.append(' ', `Set ${day} as a appointment day`);
				div1.appendChild(label);
			div2.appendChild(div1);
		div5.appendChild(div2);			
			let div4 = cTag('div',{ 'class':"flexSpaBetRow" });
				let div3 = cTag('div',{ 'class':"columnSM12" });
					const table = cTag('table',{ 'class':" table-bordered table-striped table-condensed cf listing " });
						const thead = cTag('thead',{ 'class':"cf" });
							tr = cTag('tr');
								th = cTag('th',{ 'align':"center" });
								th.innerHTML = 'H↓ / M→';
							tr.appendChild(th);
							for(let minutes=0; minutes<12; minutes++){
									th = cTag('th',{ 'style':"text-align: center;" });
									th.innerHTML = minutes<=1 ? '0'+ minutes*5 : minutes*5;
								tr.appendChild(th);
							} 	
						thead.appendChild(tr);              
					table.appendChild(thead);
						const tbody = cTag('tbody',{ 'id':"tableRows" });
						for(let hours=0; hours<24; hours++){
								tr = cTag('tr');
									th = cTag('th',{ 'style':"text-align: center;" });
									th.innerHTML = hours<10 ? '0'+hours : hours;
								tr.appendChild(th);

								for(let minutes=0; minutes<12; minutes++){
									let name = `${indx+1}_${hours<10?'0'+hours:hours}_${minutes<=1?'0'+minutes*5:minutes*5}`
									let td = cTag('td',{ 'align':"center" });
										input = cTag('input',{ 'type':"checkbox",'class':"cursor",'name':name,'value':"1" });
										if(Object.keys(data.schedules).includes(day) && data.schedules[day][0]===1 && data.schedules[day][1] && data.schedules[day][1].includes(name)) input.checked = true;
									td.appendChild(input);
								tr.appendChild(td);
								} 
							tbody.appendChild(tr);
						}																					
					table.appendChild(tbody);
				div3.appendChild(table);
			div4.appendChild(div3);
		div5.appendChild(div4);
		if(indx===0){
            div5.setAttribute('aria-hidden',"false");
            div5.setAttribute('style',"");
			activeTabpanel = div5;
		}
	    tabs.appendChild(div5);
    })
}

function createTabs(node){
	let styles = {
		tabs:{
			'border': '1px solid #d3d3d3',
			'overflow': 'auto',
		},
		tablists:{
			'list-style-type': 'none',
			'display': 'flex',
			'flex-wrap':'wrap',
			'border-bottom': '1px solid #d3d3d3',
			'padding': '.2em .2em 0',
			'gap':'3px'
		},
		tab:{
			'font-weight': 'normal',
			'border-radius': '3px !important',
			'flex-grow':'1'
		},
		tab_anchor:{
			'position': 'relative',
			'top': '1px',
			'display': 'block',
			'padding': '.5em 1em',
			'text-decoration': 'none',
			'cursor': 'pointer',
			'border-radius': '3px 3px 0 0',
			'width':'100%',
			'text-align':'center'  
		},
		hoveredTab:{
			'border': '1px solid #999999',
			'color': '#212121',
			'background-color':' #dadada' ,
		},
		unhoveredTab:{
			'border': '1px solid #d3d3d3',
			'color': '#555555',
			'background-color': '#e6e6e6' ,
		},
		activeTab:{
			'border': '1px solid #999999',
			'background-color': 'rgb(255, 255, 255)',
			'border-bottom-color':'transparent',
		},
		inactiveTab:{
			'border-bottom-color':'#d3d3d3',
		},
		tabPanel:{
			'padding':'1em 1.4em'
		}
  	}

	if(node.querySelector('ul li a')){
		setStyles(node,styles.tabs);
		setStyles(node.querySelector('ul'),styles.tablists);

		node.querySelector('ul').querySelectorAll('li').forEach((tabItem,indx)=>{
			setStyles(tabItem,styles.tab);
			let tab_anchor = tabItem.querySelector('a');
			tab_anchor.setAttribute('class','tab-anchor');
			setStyles(tab_anchor,styles.tab_anchor);
			setStyles(tab_anchor,styles.unhoveredTab);

			let tabPanel = node.querySelector(`${tab_anchor.getAttribute('href')}`);
			setStyles(tabPanel,styles.tabPanel);

			// activate the first tab initially
			if(indx===0){
				tab_anchor.classList.add('activeTab');
				setStyles(tab_anchor,styles.activeTab);
			}else{
				if(tabPanel.style.display !== 'none'){
					tabPanel.style.display = 'none';
				}
			}

			// add necessery mouse-event to the tab
			tab_anchor.addEventListener('mouseover',function(){if(!(this.classList.contains('activeTab'))) setStyles(this,styles.hoveredTab)});
			tab_anchor.addEventListener('mouseout',function(){if(!(this.classList.contains('activeTab'))) setStyles(this,styles.unhoveredTab)});
			tab_anchor.addEventListener('click',function(event){
				event.preventDefault();

				//make clicked item active
				this.classList.add('activeTab');
				setStyles(this,styles.activeTab);
				if(node.querySelector(`${this.getAttribute('href')}`).style.display === 'none'){
					node.querySelector(`${this.getAttribute('href')}`).style.display = '';
				}

				//make other items inactive
				node.querySelectorAll('a.tab-anchor').forEach(item=>{
					if(item!==this) {
						item.classList.remove('activeTab');
						setStyles(item,styles.inactiveTab);
						setStyles(item,styles.unhoveredTab)
						if(node.querySelector(`${item.getAttribute('href')}`).style.display !== 'none'){
							node.querySelector(`${item.getAttribute('href')}`).style.display = 'none';
						}
					};
				})
			});
		})
		node.activateTab = function(indx){
			node.querySelector('ul').children[indx].children[0].click();
		};
	}

	function setStyles(node, stylesObj){
		for (const property in stylesObj) {
			node.style[property] = stylesObj[property];
		}
	}

}

function previewAppointment(){
	let previewSection = document.getElementById('previewAppointment').querySelector('section');
	let previewButton = previewSection.querySelector('button');

	previewSection.style.background = document.getElementById('bg_color').value;
	previewSection.style.color = document.getElementById('color').value;
	previewSection.style.fontFamily = document.getElementById('font_family').value;

	previewButton.style.background = document.getElementById('but_bg_color').value;
	previewButton.style.color = document.getElementById('but_color').value;
	previewButton.style.fontFamily = document.getElementById('but_font_family').value;

	rearrangeAFNList();	
}

function checkAFN(){
	let AFNData = document.getElementsByName('fieldNames[]');							
	let AFNListData = new Array();

	const error_messageid = document.getElementById('errorAFNListRow');
	error_messageid.innerHTML = '';
	
	for(let i = 0; i < AFNData.length; i++) {
		let AFNOneValue = AFNData[i].value.toUpperCase();
		if (AFNListData.length > 0 && AFNListData.indexOf(AFNOneValue) !== -1) {
			error_messageid.innerHTML = 'Duplicate Form Fields '+parseInt(i+1);
			AFNData[i].focus();
			return false;
		}
		else {
			AFNListData[i] = AFNOneValue;
		}
	}
	return true;
}

function addMoreAFN(){
	if(checkAFN()===false){return false;}
	
	let i = 0;
	let AFNData = document.getElementsByName('fieldNames[]');							
	let error_messageid = document.getElementById('errorAFNListRow');
	for(let i = 0; i < AFNData.length; i++) {
		if (AFNData[i].value==='') {
			error_messageid.innerHTML = 'Form Fields '+Translate('is missing.')+parseInt(i+1);
			AFNData[i].focus();
			return false;
		}
	}
		
	let ulidname = 'AFNListRow';
	let index = document.querySelector(`ul#${ulidname}`).children.length;

	index = parseInt(index+1);
	let newmore_list = cTag('li',{ 'style': "padding-right: 10px;" });
		let newRow = cTag('div',{ 'class':'flexSpaBetRow' });
			let indexColumn = cTag('div',{ 'class':'columnXS2 flexSpaBetRow' });
			indexColumn.innerHTML = index
		newRow.appendChild(indexColumn);
			let indexField = cTag('div',{ 'class':'columnXS10' });
				let inputField = cTag('input',{ 'type':'text','maxlength':'25','name':'fieldNames[]','value':'','placeholder':`Appointment field ${index}`,'alt':`Appointment field ${index}`,'class':'form-control placeholder fieldNames' })
				inputField.addEventListener('blur',sanitizer);
			indexField.appendChild(inputField);
		newRow.appendChild(indexField);
	newmore_list.appendChild(newRow);
	
	document.querySelector(`#${ulidname}`).appendChild(newmore_list);
	
	callPlaceholder();
	document.getElementsByName('fieldNames[]')[parseInt(index-1)].focus();
	rearrangeAFNList();
}

function rearrangeAFNList(){
	let l;
	let AFNListRow = document.querySelector('ul#AFNListRow');
	if(AFNListRow.children.length>1){
		l = 1;		
		AFNListRow.querySelectorAll('li').forEach(list=>{
			let upArrow = cTag('a',{ 'class':'AFNOrderUp', 'href':'javascript:void(0);','title':'Move to UP' });
			upArrow.appendChild(cTag('i',{ 'class':'fa fa-arrow-up' }));
			if(l === AFNListRow.children.length){upArrow = ''};
			list.querySelector('.flexSpaBetRow').querySelector('.columnXS2').innerHTML = ''
			list.querySelector('.flexSpaBetRow').querySelector('.columnXS2').append(l,upArrow);
			l++;
		})
	}	

	document.querySelectorAll('.AFNOrderUp').forEach((arrow)=>{
		arrow.addEventListener('click',function(){
			const cac = [...AFNListRow.children].indexOf(this.parentNode.parentNode.parentNode);
			AFNOrderUp(cac);
		})
	})
	
	document.querySelectorAll('.removeicon').forEach(remover=>{
		remover.remove();
	})
	
	document.querySelectorAll('.fieldNames').forEach(field=>{
		field.addEventListener('change',function(){
			let fieldNames = this.value;
			this.setAttribute('value',fieldNames);
			this.title = fieldNames;
		})
	})

	const requiredFields = ['NAME', 'PHONE NO.', 'EMAIL'];
	let countList = document.querySelector('ul#AFNListRow').children.length;
	if(countList>1){
		for(l = 1; l < countList; l++){
			let poValue = document.querySelector("ul#AFNListRow li:nth-child("+l+")").querySelector('.fieldNames').value.toUpperCase()												
			if(requiredFields.includes(poValue)){}
			else{
				let a = cTag('a',{ 'class':'removeicon','href':'javascript:void(0);','title':Translate('Remove this row') });
				a.appendChild(cTag('img',{ 'align':'absmiddle','alt':Translate('Remove this row'),'title':Translate('Remove this row'),'src':'/assets/images/cross-on-white.gif' }));
				document.querySelector("ul#AFNListRow li:nth-child("+l+")").appendChild(a);				
			}
		}
		document.querySelectorAll('.removeicon').forEach(remover=>{
			remover.addEventListener('click',function(){
				if(document.querySelector('ul#AFNListRow').children.length>1){
					this.parentElement.remove();
					rearrangeAFNList();
				}else{
					alert_dialog('Remove Form Fields', 'You could not remove all options', Translate('Ok'));
				}
			})
		})
	}	
}

function AFNOrderUp(cac){
	let topPos = cac-1;
	let l;
	let totalLI = parseInt(document.querySelector("ul#AFNListRow").children.length);
	if(totalLI>1){
		if(cac===0){
			topPos = totalLI-2;
		}
		
		let prevLI = '';
		let nextLI = '';

		if(document.querySelector("ul#AFNListRow").children.length>1){
			l = 0;
			document.querySelector("ul#AFNListRow").querySelectorAll('li').forEach(list=>{
				if(l===topPos){
					nextLI = list.querySelector('.flexSpaBetRow').querySelector('.columnXS10').innerHTML;
				}else if(l===cac){
					prevLI = list.querySelector('.flexSpaBetRow').querySelector('.columnXS10').innerHTML;
				}
				l++;
			})
			l = 0;
			document.querySelector("ul#AFNListRow").querySelectorAll('li').forEach(list=>{
				if(l===topPos){
					list.querySelector('.flexSpaBetRow').querySelector('.columnXS10').innerHTML = prevLI ;
				}else if(l===cac){
					list.querySelector('.flexSpaBetRow').querySelector('.columnXS10').innerHTML = nextLI ;
				}
				l++;
			})
			rearrangeAFNList();
		}
	}
}

async function AJsave_Appointment(e){
	e.preventDefault();
	AJsave({
		btn_id: '#submitAppointment',
		form_id: '#frmAppointment',
		api_endpoint: 'AJsave_Appointment'
	});
}

function checkBlockOutDate(date){
    let blockoutDates = [...document.querySelectorAll('#blockoutDateLists input')];
    let dateAlreadyExist = blockoutDates.filter(item=>item.value===date);
    if(!date){
        showTopMessage('alert_msg','Choose a Date');
        document.getElementById('blockoutDatePicker').focus();
        return false;
    }
    else if(dateAlreadyExist.length){
        showTopMessage('alert_msg','The Date you choose is already exist');
        let dateItem = dateAlreadyExist[0].parentNode.querySelector('span');
        dateItem.classList.add('blockoutDateExist');
        setTimeout(()=>dateItem.classList.remove('blockoutDateExist'), 3000);
        return false;
    }
    document.getElementById('blockoutDatePicker').value = '';
    return true;
}
function addBlockOutDate(date){
	let blockoutDateItem = cTag('div',{id:'blockoutDateItem',style:'position:relative'});
	blockoutDateItem.append(cTag('input',{'type':'hidden','name':'blockoutDates[]','value':date}));
        let dateSpan = cTag('span',{'class':'btn','style':'width:12ch;border: 1px solid #ccc;background: #fff;'});
        dateSpan.innerHTML = DBDateToViewDate(date);
	blockoutDateItem.append(dateSpan);
		let deleteItem = cTag('a',{'data-toggle':"tooltip",'data-original-title':"Remove this Blockout Date",});
		deleteItem.append(cTag('img',{'src':"/assets/images/cross-on-white.gif",'style':'transform:scale(1.3)'}));
		deleteItem.addEventListener('click',()=>blockoutDateItem.remove());
		tooltip(deleteItem);
	blockoutDateItem.append(deleteItem);
	document.getElementById('blockoutDateLists').append(blockoutDateItem);

}

//Repair Status Section
async function RStatus(){
    const showTableData = document.getElementById("viewPageInfo");
        header(Translate('Check Repair Status'));
		const rStatusRow = cTag('div',{ 'class':"flexSpaBetRow" });
            let rStatusColumn = cTag('div',{ 'class':"columnSM12"});
                let calloutDiv = cTag('div',{ 'class':"innerContainer bs-callout-info",'style':"margin-top: 0; border-left: 1px solid #eeeeee; background: #fff" });
                    const form = cTag('form',{ 'name':"frmRStatus",'id':"frmRStatus",'action':"#",'enctype':"multipart/form-data",'method':"post",'accept-charset':"utf-8" });
					form.appendChild(color_font_controller({colSize:[5,4],freeSpace:false,rsStatus:true}));
                    form.appendChild(btn_color_font_controller());

						const repairStatusRow = cTag('div',{ 'class':"flexCenterRow" });
							let roundBorder = cTag('div',{ 'class':"columnSM8 roundborder", 'id': "previewRStatus" });
								let repairSection = cTag('section',{ 'style': "padding: 20px 0;"});
									[
										Translate('First Name'),Translate('Ticket Number'),Translate('Check Repair Status')
									].forEach((label,indx)=>{
											let RepairStatusDiv = cTag('div');
											if(indx === 2) RepairStatusDiv.setAttribute('class',"columnXS12");
											else RepairStatusDiv.setAttribute('class',"columnSM10");
												let RepairStatusButton = cTag('div');
												if(indx === 2){
													RepairStatusButton.setAttribute('id','RStatusButtton')
													RepairStatusButton.setAttribute('class','btn')
													RepairStatusButton.setAttribute('style'," height: 50px; font-size: 15px; line-height: 50px; text-align: center; padding: 0 40px; border-radius: 4px;");
												}
												else RepairStatusButton.setAttribute('style'," width: 100%; height: 45px; padding-left: 10px; background: rgba(255, 255, 255, 0.7); border: 1px solid #ccc; font-size: 15px; line-height: 45px; margin-bottom: 20px; ");
												RepairStatusButton.innerHTML = label;
											RepairStatusDiv.appendChild(RepairStatusButton);
										repairSection.appendChild(RepairStatusDiv);
									})
							roundBorder.appendChild(repairSection);
						repairStatusRow.appendChild(roundBorder);
                    form.appendChild(repairStatusRow);

						let buttonRow = cTag('div',{ 'class':"flexSpaBetRow" });
                            let submitButton = cTag('div',{ 'class':"columnXS12", 'align':"center" });
                            submitButton.appendChild(cTag('input',{ 'type':"hidden",'name':"variables_id",'id':"variables_id"}));
                            submitButton.appendChild(cTag('input',{ 'class':"btn saveButton",'name':"submit",'id':"submitRStatus",'type':"submit",'value':" Save " }));
						buttonRow.appendChild(submitButton);
                    form.appendChild(buttonRow);
				calloutDiv.appendChild(form);
			rStatusColumn.appendChild(calloutDiv);
		rStatusRow.appendChild(rStatusColumn);
	showTableData.appendChild(rStatusRow);

	AJ_RStatus_MoreInfo();
}

async function AJ_RStatus_MoreInfo(){
	const jsonData = {};
    const url = '/'+segment1+'/AJ_RStatus_MoreInfo';
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.repair_status_online){
			document.getElementById('repair_status_online').checked = true
		}else{
			document.getElementById('repair_status_online').checked = false
		}

		document.getElementById("variables_id").value = data.variables_id;

		let bg_colorRStatus = document.getElementById('bg_color')
		bg_colorRStatus.value = data.bg_color
		bg_colorRStatus.addEventListener('change',previewRStatus)
		
		let colorRStatus = document.getElementById('color')
		colorRStatus.value = data.color
		colorRStatus.addEventListener('change',previewRStatus)
		
		let font_familyRStatus = document.getElementById('font_family')
		font_familyRStatus.value = data.font_family
		font_familyRStatus.addEventListener('change',previewRStatus)

		let but_bg_colorRStatus = document.getElementById('but_bg_color')
		but_bg_colorRStatus.value = data.but_bg_color
		but_bg_colorRStatus.addEventListener('change',previewRStatus)
		
		let but_colorRStatus = document.getElementById('but_color')
		but_colorRStatus.value = data.but_color
		but_colorRStatus.addEventListener('change',previewRStatus)
		
		let but_font_familyRStatus = document.getElementById('but_font_family')
		but_font_familyRStatus.value = data.but_font_family
		but_font_familyRStatus.addEventListener('change',previewRStatus)

		previewRStatus();
	}
	document.querySelector('#frmRStatus').addEventListener('submit', AJsave_RStatus);
}

function previewRStatus(){
	let previewRStatusSection = document.getElementById('previewRStatus').querySelector('section')
	let RStatusButtton = previewRStatusSection.querySelector('#RStatusButtton')
	
	previewRStatusSection.style.background = document.getElementById('bg_color').value
	previewRStatusSection.style.color = document.getElementById('color').value
	previewRStatusSection.style.fontFamily = document.getElementById('font_family').value

	RStatusButtton.style.background = document.getElementById('but_bg_color').value
	RStatusButtton.style.color = document.getElementById('but_color').value
	RStatusButtton.style.fontFamily = document.getElementById('but_font_family').value
}

async function AJsave_RStatus(e){
	e.preventDefault();
	AJsave({
		btn_id: '#submitRStatus',
		form_id: '#frmRStatus',
		api_endpoint: 'AJsave_RStatus'
	});	
}

//============New Functions=========//
document.addEventListener('DOMContentLoaded', async()=>{
	let layoutFunctions = {lists, header, all_pages_header, home_page_body, all_pages_footer,ContactUs,Customer,services,products,cell_phones,Quote,Appointment,RStatus};
	layoutFunctions[segment2]();

	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
    document.getElementById("websitePage").value = segment2;
});