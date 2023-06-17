import {
	cTag, Translate, tooltip, addCurrency, NeedHaveOnPO, noPermissionWarning, confirm_dialog, alert_dialog, controllNumericField,
	showTopMessage, setOptions, getMobileOperatingSystem, popup_dialog, popup_dialog600, validateRequiredField, fetchData, 
	triggerEvent, actionBtnClick, serialize, customAutoComplete, DBDateToViewDate, togglePaymentButton, round, calculate
} from './common.js';


/*======One Time Product======*/
export function calculateOneTimeTotal(){
	let discount_value;
	
	let sales_price = parseFloat(document.getElementById("sales_price").value);
	if(sales_price==='' || isNaN(sales_price)){sales_price = 0;}
	
	let qty = parseFloat(document.getElementById("qty").value);
	if(qty==='' || isNaN(qty)){qty = 0;}
	
	let qty_value = calculate('mul',sales_price,qty,2);
	document.getElementById("qty_value").value = qty_value;
	document.getElementById("qtyValueStr").innerHTML = addCurrency(qty_value);
	
	let discount_is_percent = document.getElementById("discount_is_percent").value;
	let discount = parseFloat(document.getElementById("discount").value);
	if(discount==='' || isNaN(discount)){discount = 0;}					
	
	let discountField = document.getElementById("discount");
	if(discount_is_percent>0){
		discountField.setAttribute('data-max','99.99');
	} 
	else{
		discountField.setAttribute('data-max',qty_value);
	} 

	if(qty_value !==0){
		if(discount_is_percent>0){
			if(discount>99.99){
				document.getElementById("discount").value = 99.99;
				discount = 99.99;
			}
			discount_value = calculate('mul',qty_value,calculate('mul',0.01,discount,false),2);
		}
		else{ 
			discount_value = calculate('mul',discount,qty,2);
			if(discount_value > qty_value){				
				document.getElementById("discount").value = sales_price;
				discount_value = calculate('mul',sales_price,qty,2);
			}
		}
	}
	else{
		if(discount_is_percent>0){
			if(discount<-99.99){
				document.getElementById("discount").value = -99.99;
				discount = -99.99;
			}
			discount_value = calculate('mul',qty_value,calculate('mul',0.01,discount,false),2);
		}
		else{ 
			discount_value = calculate('mul',discount,qty,2);
			if(discount < qty_value){				
				document.getElementById("discount").value = sales_price;
				discount_value = calculate('mul',sales_price,qty,2);
			}
		}
	}
	
	if(discount_value==='' || isNaN(discount_value)){discount_value = 0;}
	document.getElementById("discountValueStr").innerHTML = addCurrency(discount_value);
	
	let total = calculate('sub',qty_value,discount_value,2);
	if(total==='' || isNaN(total)){total = 0.00;}
	document.getElementById("totalValueStr").innerHTML = addCurrency(total);
		
	let cost = parseFloat(document.getElementById("cost").value);
	if(cost==='' || isNaN(cost)){cost = 0;}
	
	let cost_value = calculate('mul',cost,qty,2);
	document.getElementById("cost_value").value = cost_value;
	document.getElementById("costValueStr").innerHTML = addCurrency(cost_value);
}

export async function AJget_oneTimePopup(pos_cart_id){
	let discount_is_percent;
	discount_is_percent = 1;
	if(pos_cart_id>0) discount_is_percent = document.getElementById("discount_is_percent"+pos_cart_id).value;
	
	const jsonData = {};
	jsonData['pos_cart_id'] = pos_cart_id;
    const url = "/Carts/AJget_oneTimePopup/";

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let pos_id = document.getElementById("pos_id").value;
		let display = '';
		if(document.getElementById("order_status")){
			if(document.getElementById("order_status").value==='Quotes'){display = 'none';}
		}
		let  bTag, requiredField, inputField, errorSpan;
		const formhtml = cTag('div');
			const oneTimeForm = cTag('form', {'action': "#", name: "frmOneTimeProduct", id: "frmOneTimeProduct", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			oneTimeForm.addEventListener('submit', event=>{
				event.preventDefault();
				AJsave_oneTime();
			});
			oneTimeForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));
				const descriptionRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
					const descriptionTitle = cTag('div', {class: "columnSM3"});
						const descriptionLabel = cTag('label', {'for': "description"});
						descriptionLabel.innerHTML = Translate('Description')+ ':';
							requiredField = cTag('span', {class: "required"});
							requiredField.innerHTML = '*';
						descriptionLabel.appendChild(requiredField);
					descriptionTitle.appendChild(descriptionLabel);
				descriptionRow.appendChild(descriptionTitle);
					const descriptionField = cTag('div', {class: "columnSM9"});
						inputField = cTag('input', {class: "form-control requiredField", name: "description", id: "description", 'placeholder': Translate('Description'), 'value': data.description});
					descriptionField.appendChild(inputField);
						errorSpan = cTag('span', {class: "error_msg", id: "errmsg_description"});
					descriptionField.appendChild(errorSpan);
				descriptionRow.appendChild(descriptionField);
			oneTimeForm.appendChild(descriptionRow);

				const addDescriptionRow = cTag('div', {class: "flex", 'align': "left"});
					const addDescriptionTitle = cTag('div', {class: "columnSM3"});
						const addDescriptionLabel = cTag('label', {'for': "add_description"});
						addDescriptionLabel.innerHTML = Translate('Additional Description')+':';
					addDescriptionTitle.appendChild(addDescriptionLabel);
				addDescriptionRow.appendChild(addDescriptionTitle);
					const addDescriptionField = cTag('div', {class: "columnSM9"});
						let textarea = cTag('textarea', {class: "form-control", name: "add_description", id: "add_description", 'rows': 2, 'cols': 20});
						textarea.innerHTML = data.add_description;
					addDescriptionField.appendChild(textarea);
				addDescriptionRow.appendChild(addDescriptionField);
			oneTimeForm.appendChild(addDescriptionRow);

				const salesPriceRow = cTag('div', {class: "flex", 'align': "left", 'style': "align-items: center;"});
					const salesPriceTitle = cTag('div', {class: "columnSM3"});
						const salesPriceLabel = cTag('label', {'for': "sales_price"});
						salesPriceLabel.innerHTML = Translate('Unit Price')+ ':';
							requiredField = cTag('span', {class: "required"});
							requiredField.innerHTML = '*';
						salesPriceLabel.appendChild(requiredField);
					salesPriceTitle.appendChild(salesPriceLabel);
				salesPriceRow.appendChild(salesPriceTitle);
					const minMaxColumn = cTag('div', {class: "columnSM4"});
						inputField = cTag('input', {'type': "text",'data-min':'-9999999.99', 'data-max':'9999999.99','data-format':'d.dd', class: "form-control requiredField ", name: "sales_price", id: "sales_price", 'placeholder': Translate('Unit Price'), 'value': round(data.sales_price,2)});
						controllNumericField(inputField, '#errmsg_sales_price');
						inputField.addEventListener('keyup', calculateOneTimeTotal);
						inputField.addEventListener('change', calculateOneTimeTotal);
					minMaxColumn.appendChild(inputField);
					minMaxColumn.appendChild(cTag('span', {class: "error_msg", id: "errmsg_sales_price"}));
				salesPriceRow.appendChild(minMaxColumn);
			oneTimeForm.appendChild(salesPriceRow);

				const qtyRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
					const qtyTitle = cTag('div', {class: "columnSM3"});
						const qtyLabel = cTag('label', {'for': "qty"});
						qtyLabel.innerHTML = Translate('QTY')+ ':';
							requiredField = cTag('span', {class: "required"});
							requiredField.innerHTML = '*';
						qtyLabel.appendChild(requiredField);
					qtyTitle.appendChild(qtyLabel);
				qtyRow.appendChild(qtyTitle);
					const maxMinColumn = cTag('div', {class: "columnSM4"});
						inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'9999', 'data-format': "d", class: "form-control requiredField", name: "qty", id: "qty", 'placeholder': Translate('QTY'), 'value': data.qty});
						controllNumericField(inputField, '#errmsg_qty');
						inputField.addEventListener('keyup', calculateOneTimeTotal);
						inputField.addEventListener('change', calculateOneTimeTotal);
					maxMinColumn.appendChild(inputField);
						errorSpan = cTag('span', {class: "error_msg", id: "errmsg_qty"});
					maxMinColumn.appendChild(errorSpan);
				qtyRow.appendChild(maxMinColumn);
					const qtyValue = cTag('div', {class: "columnSM5", 'align': "right"});
					qtyValue.innerHTML = Translate('Subtotal')+ ': ';
						bTag = cTag('b', {id: "qtyValueStr"});
						bTag.innerHTML = currency+'0.00';
					qtyValue.appendChild(bTag);
						inputField = cTag('input', {'type': "hidden", name: "qty_value", id: "qty_value", 'value': 0});
					qtyValue.appendChild(inputField);
				qtyRow.appendChild(qtyValue);
			oneTimeForm.appendChild(qtyRow);

				const discountRow = cTag('div', {class: "flex", 'style': "align-items: center;", 'align': "left"});
					const discountTitle = cTag('div', {class: "columnSM3"});
						const discountLabel = cTag('label', {'for': "discount"});
						discountLabel.innerHTML = Translate('Discount')+' :';
					discountTitle.appendChild(discountLabel);
				discountRow.appendChild(discountTitle);
					const discountField = cTag('div', {class: "columnSM4"});
						let discountInGroup = cTag('div', {class: "input-group"});
							const discountSpan = cTag('span', {class: "input-group-addon cursor", 'style': "padding: 0;"});
								inputField = cTag('input', {id: "discount", name: "discount", 'type': "text",'data-min':'0', 'data-max': '99.99', 'data-format':'d.dd', 'value': round(data.discount,2), class: "form-control", 'style': "min-width: 120px;"});
								controllNumericField(inputField, '#errmsg_discount');
								inputField.addEventListener('keyup', calculateOneTimeTotal);
								inputField.addEventListener('change', calculateOneTimeTotal);
							discountSpan.appendChild(inputField);
						discountInGroup.appendChild(discountSpan);
							let discountSpan2 = cTag('span', {class: "input-group-addon", 'style': "width: 40px; padding: 0px;"});
								let selectDiscount = cTag('select', {id: "discount_is_percent", name: "discount_is_percent", class: "form-control bgnone", 'style': "width: 60px;"});
								selectDiscount.addEventListener('change', calculateOneTimeTotal);
									let percentOption = cTag('option', {'value': 1});
									percentOption.innerHTML = '%';
								selectDiscount.appendChild(percentOption);
									let currencyOption = cTag('option', {'value': 0});
									currencyOption.innerHTML = currency;
								selectDiscount.appendChild(currencyOption);
							discountSpan2.appendChild(selectDiscount);
						discountInGroup.appendChild(discountSpan2);
					discountField.appendChild(discountInGroup);
					discountField.appendChild(cTag('span', {class: "error_msg", id: "errmsg_discount"}));
				discountRow.appendChild(discountField);
					const discountValue = cTag('div', {class: "columnSM5", 'align': "right"});
						bTag = cTag('b', {id: "discountValueStr"});
						bTag.innerHTML = currency+'0.00';
					discountValue.appendChild(bTag);
						inputField = cTag('input', {'type': "hidden", name: "discountvalue", id: "discountvalue", 'value': 0});
					discountValue.appendChild(inputField);
				discountRow.appendChild(discountValue);
			oneTimeForm.appendChild(discountRow);

				const taxableRow = cTag('div', {class: "flex", 'align': "left"});
					const taxableTitle = cTag('div', {class: "columnXS4 columnSM3"});
						const taxableLabel = cTag('label', {'for': "taxable", class: "cursor"});
						taxableLabel.innerHTML = Translate('Taxable')+'?:';
					taxableTitle.appendChild(taxableLabel);
				taxableRow.appendChild(taxableTitle);
					const taxableValue = cTag('div', {class: "columnXS8 columnSM4"});
						inputField = cTag('input',{ 'type': 'checkbox', class: "cursor",'name': 'taxable','id': 'taxable','value': 1});
					taxableValue.appendChild(inputField);
				taxableRow.appendChild(taxableValue);
			oneTimeForm.appendChild(taxableRow);

				let underLine = cTag('hr');
			oneTimeForm.appendChild(underLine);

				const totalRow = cTag('div', {'align': "right"});
					bTag = cTag('b');
					bTag.innerHTML = Translate('Total')+' :';
				totalRow.appendChild(bTag);
					bTag = cTag('b', {id: "totalValueStr"});
					bTag.innerHTML = currency+'0.00';
				totalRow.appendChild(bTag);
			oneTimeForm.appendChild(totalRow);

				const supplierRow = cTag('div', {class: 'flex','style':`align-items: center; display:${display} `, 'align': "left"});
					const supplierTitle = cTag('div', {class: "columnXS4 columnSM3"});
						const supplierLabel = cTag('label', {'for': "suppliers_id"});
						supplierLabel.innerHTML = Translate('Supplier')+ ' :';
					supplierTitle.appendChild(supplierLabel);
				supplierRow.appendChild(supplierTitle);
					let supplierField = cTag('div', {class: "columnXS8 columnSM4"});
						let selectSupplier = cTag('select', {id: "suppliers_id", name: "suppliers_id", class: "form-control"});
							let option = cTag('option',{value:0});
							option.innerText = 'Select Supplier';
						selectSupplier.appendChild(option);
						setOptions(selectSupplier, data.supplierOptions, 1, 1);
						selectSupplier.value = data.suppliers_id;
					supplierField.appendChild(selectSupplier);
				supplierRow.appendChild(supplierField);
					const errorColumn = cTag('div', {class: "columnSM2"});
						errorSpan = cTag('span', {class: "error_msg", id: "errmsg_suppliers_id"});
					errorColumn.appendChild(errorSpan);
				supplierRow.appendChild(errorColumn);
			oneTimeForm.appendChild(supplierRow);

				const costRow = cTag('div', {class: 'flex','style':`align-items: center; display:${display}`, 'align': "left"});
					const costTitle = cTag('div', {class: "columnXS4 columnSM3"});
						const costLabel = cTag('label', {'for': "cost"});
						costLabel.innerHTML = Translate('Cost')+ ' :';
					costTitle.appendChild(costLabel);
				costRow.appendChild(costTitle);
					const costField = cTag('div', {class: "columnXS8 columnSM4"});
						inputField = cTag('input', {'type': "text",'data-min':'0','data-max':'999999.99','data-format':'d.dd', class: "form-control", name: "cost", id: "cost", 'value': round(data.ave_cost,2)});
						controllNumericField(inputField, '#errmsg_cost');
						inputField.addEventListener('keyup', calculateOneTimeTotal);
						inputField.addEventListener('change', calculateOneTimeTotal);
					costField.appendChild(inputField);
						errorSpan = cTag('span', {class: "error_msg", id: "errmsg_cost"});
					costField.appendChild(errorSpan);
				costRow.appendChild(costField);
					const emptyColumn = cTag('div', {class: "columnXS4 columnSM3"});
					emptyColumn.innerHTML = '\u00a0';
				costRow.appendChild(emptyColumn);
					const costValue = cTag('div', {class: "columnXS8 columnSM2", 'align': "right"});
						bTag = cTag('b', {id: "costValueStr"});
						bTag.innerHTML = currency+'0.00';
					costValue.appendChild(bTag);
						inputField = cTag('input', {'type': "hidden", name: "cost_value", id: "cost_value", 'value': 0});
					costValue.appendChild(inputField);
				costRow.appendChild(costValue);
			oneTimeForm.appendChild(costRow);

				const receivedRow = cTag('div', {class: 'flex','style':`display:${display}`, 'align': "left"});
					const receivedTitle = cTag('div', {class: "columnXS5 columnSM3"});
						const receivedLabel = cTag('label', {'for': "received", class: "cursor"});
						receivedLabel.innerHTML = Translate('Received Qty')+ '?:';
					receivedTitle.appendChild(receivedLabel);
				receivedRow.appendChild(receivedTitle);
					const receivedValue = cTag('div', {class: "columnXS7 columnSM4"});
						inputField = cTag('input', {'type': "checkbox", class: "cursor", name: "received", id: "received", 'value': 1});
					receivedValue.appendChild(inputField);
				receivedRow.appendChild(receivedValue);
			oneTimeForm.appendChild(receivedRow);

				inputField = cTag('input', {'type': "hidden", name: "pos_cart_idvalue", id: "pos_cart_idvalue", 'value': data.pos_cart_id});
			oneTimeForm.appendChild(inputField);
				inputField = cTag('input', {'type': "hidden", name: "pos_id", 'value': pos_id});
			oneTimeForm.appendChild(inputField);
				inputField = cTag('input', {'type': "hidden", name: "frompage", 'value': ""});
			oneTimeForm.appendChild(inputField);
				inputField = cTag('input', {'type': "hidden", name: "repairs_status", 'value': ""});
			oneTimeForm.appendChild(inputField);
		formhtml.appendChild(oneTimeForm);

		popup_dialog600(Translate('One Time Product'), formhtml, Translate('Save'),AJsave_oneTime);
		
		setTimeout(function() {		
			document.frmOneTimeProduct.description.focus();
			if(data.received>0){document.getElementById("received").checked = true;}
			else{document.getElementById("received").checked = false;}
			if(data.taxable>0){document.getElementById("taxable").checked = true;}
			else{document.getElementById("taxable").checked = false;}
			document.getElementById("discount_is_percent").value = discount_is_percent;
			calculateOneTimeTotal();
		});
	}
}

export async function AJsave_oneTime(hidePopup){
	let errorStatus, saveBtn;
	let errmsg_discount = document.getElementById("errmsg_discount");
	errmsg_discount.innerHTML = '';
	
	if(document.getElementsByClassName("required").length>0){
		let requiredFields = document.getElementsByClassName("requiredField");
		
		for(let l=0;l<requiredFields.length; l++){
			let oneFieldVal = requiredFields[l].value;
			let fieldName = requiredFields[l].getAttribute('name');
			document.getElementById('errmsg_'+fieldName).innerHTML = '';
			if(oneFieldVal==='' || oneFieldVal==='0'){
				document.getElementById('errmsg_'+fieldName).innerHTML = requiredFields[l].placeholder+' '+Translate('is missing.');
				requiredFields[l].focus();
				return false;				
			}
		}
	}

	let sales_price = document.getElementById("sales_price");
    if (!sales_price.valid()) return;

	let qty = document.getElementById("qty");
    if (!qty.valid()) return;

	if (!document.getElementById('discount').valid()) return;

	let cost = document.getElementById("cost");
    if (!cost.valid()) return;
	
	/* let cost = parseFloat(document.getElementById("cost").value);
	if(cost==='' || isNaN(cost)){cost = 0;}
	errorStatus = document.getElementById("errmsg_cost");
	errorStatus.innerHTML = '';	
	if(cost>999999.99){
		errorStatus.innerHTML = Translate('Cost should be maximum 999999.99');
		document.getElementById("cost").focus();
		return false;
	} */
	
	errorStatus = document.getElementById("errmsg_suppliers_id");
	errorStatus.innerHTML = '';	
	if(document.getElementById("received").checked && parseInt(document.getElementById("suppliers_id").value)===0){
		errorStatus.innerHTML = 'Missing supplier';
		document.getElementById("suppliers_id").focus();
		return false;
	}
	let frompage = segment1;
	document.frmOneTimeProduct.pos_id.value = document.getElementById("pos_id").value;
	let repairs_status = '';
	if(frompage==='Repairs'){repairs_status = document.getElementById("repairs_status").value;}
	document.frmOneTimeProduct.repairs_status.value = repairs_status;
	
	saveBtn = document.querySelector(".btnmodel");
	saveBtn.innerHTML = Translate('Saving')+'...';
	saveBtn.disabled = true;

	const jsonData = serialize('#frmOneTimeProduct');
    const url = "/Carts/AJsave_oneTime/"+frompage;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action ==='reload'){location.reload();}
		else if(data.action ==='Add' || data.action === 'Update'){			
			loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
			cartsAutoFuncCall();
			hidePopup();
		}
		else{
			saveBtn = document.querySelector(".btnmodel");
			saveBtn.innerHTML = Translate('Save');
			saveBtn.disabled = false;
			showTopMessage('alert_msg', Translate('Sorry! could not add product to order.'));
		}
	}
}

/*=========Cart Used============*/
export function productPickerIMEI(product_id){
	document.getElementById('ppproduct_id').value = product_id;
	if(document.getElementById("filterrow") && document.getElementById("filterrow").style.display === 'none'){
		document.getElementById("filterrow").style.display = 'flex';
	}

	if(document.getElementById('filter_category_name_html')){
		document.getElementById('filter_category_name_html').style.display = 'none';
	}

	if(document.getElementById('filter_name_html')){
		if(document.getElementById('filter_name_html').style.display === 'none'){
			document.getElementById('filter_name_html').style.display = 'block';
		}
	}
	
	if(document.getElementById('all-category-button')){
		if(document.getElementById('all-category-button').style.display === 'none'){
			document.getElementById('all-category-button').style.display = 'block';
		}
	}
	
	if(document.getElementById('allproductlist')){
		document.getElementById('allproductlist').style.display = 'none';
	}

	if(document.getElementById('allcategorylist')){
		document.getElementById('allcategorylist').style.display = 'none';

	}	
	
	productPickerIMEICount();
}

export async function productPickerIMEICount(){
	document.getElementById('pagi_index').value = 0;
	let product_id = document.getElementById('ppproduct_id').value;

	const jsonData = {};
	jsonData['product_id'] = product_id;
	jsonData['returnvalue'] = 'datacount';
    const url = '/Carts/productPickerIMEI/';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.getElementById('totalrowscount').value = data.returnData;	
		showProductPickerIMEI();
	}
}

export async function addCartsProduct(){
	let errorStatus = document.getElementById("error_search_sku");
	errorStatus.innerHTML = '';
	let field = document.getElementById("search_sku");	
	if(field.value===''){
		errorStatus.innerHTML = Translate('Missing IMEI Number');
		setTimeout(()=>field.focus(), 150);
	}
	else{						
		let frompage = segment1;

		const jsonData = {};
		let pos_id = document.getElementById("pos_id").value;
		if(frompage==='Repairs'){
			jsonData['repairs_status'] = document.getElementById('repairs_status').value;
		}
		if(document.getElementById("clickYesNo")){
			jsonData['clickYesNo'] = document.getElementById('clickYesNo').value;
		}
		if(frompage==='Orders'){
			jsonData['orderStatus'] = document.getElementById('order_status').value;
		}
		jsonData['fieldname'] = "sku";
		jsonData['fieldvalue'] = field.value;
		jsonData['pos_id'] = pos_id;
		const url = "/Carts/addCartsProduct/"+frompage;

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.action ==='reload'){location.reload();}
			else if(data.action ==='Add' || data.action === 'Update'){
				if(frompage==='POS' && data.pos_id>0){
                    document.getElementById("pos_id").value = data.pos_id;
                    togglePaymentButton();
                }
				
				loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
				if(data.alertMessage !==''){alert_dialog(Translate('Alert message'), data.alertMessage, Translate('Ok'));}

				cartsAutoFuncCall();
			}
			else{
				if(data.action ==='notProductOrder') errorStatus.innerHTML = Translate('Sorry! could not add product to order.');
				else if(data.action ==='noDataFound') errorStatus.innerHTML = Translate('There is no data found');
				else if(data.action ==='noStock') errorStatus.innerHTML = Translate('There is no stock available.');
				else if(data.action ==='productExist') errorStatus.innerHTML = Translate('This Product already added into cart.');
				else if(data.action ==='noInventory') errorStatus.innerHTML = Translate('No inventory product meet the criteria given');
				else if(data.action ==='noOrder') errorStatus.innerHTML = Translate('There is no order found');
			}
			field.value = '';
			field.focus();
		}
	}
	return false;
}

export async function addCartsIMEI(pos_cart_id, item_number, fieldObj=false){
	let frompage = segment1;

	const jsonData = {};
	jsonData['pos_cart_id'] = pos_cart_id;
	jsonData['item_number'] = item_number;
    const url = "/Carts/addCartsIMEI/"+frompage;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action==='reload'){location.reload();}
		else if(data.action==='Add'){
			if(frompage==='POS' && data.pos_id>0){
                document.getElementById("pos_id").value = data.pos_id;
                togglePaymentButton();
            }
			if(segment1==='Inventory_Transfer'){
				// loadITCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
				triggerEvent('loadITCart',data.cartsData);
			}
			else{
				loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
				cartsAutoFuncCall();
			}
			if(document.getElementById("item_number"+pos_cart_id)){				
				setTimeout(function() {
					document.getElementById("item_number"+pos_cart_id).focus();
				}, 1000);
			}
		}
		else{
			showTopMessage('alert_msg', Translate('IMEI Number not found'));
			if(fieldObj){fieldObj.focus();}
		}
	}							
}

export async function addCartsSerial(pos_cart_id, serial_number){
	let frompage = segment1;	

	const jsonData = {};
	jsonData['pos_cart_id'] = pos_cart_id;
	jsonData['serial_number'] = serial_number;
    const url = "/Carts/addCartsSerial/"+frompage;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action==='reload'){location.reload();}
		else if(data.action==='Add'){
			if(frompage==='POS' && data.pos_id>0){
                document.getElementById("pos_id").value = data.pos_id;
                togglePaymentButton();
            }
			
			loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
			cartsAutoFuncCall();
		}
		else{
			let message;
			if(data.action==='Duplicate'){ message = Translate('Duplicate serial found');}
			else{ message = Translate('Could not add serial');}
			showTopMessage('alert_msg', message);
		}
	}
}

export async function removeCartsProduct(pos_cart_id, shipping_qty){
	let frompage = segment1;
	if(shipping_qty>0){
		let input;
		let htmlStr = cTag('div');
		htmlStr.innerHTML = Translate('Are you sure you want to remove')+'?';
			input = cTag('input', {'type': "hidden", name: "rpos_cart_id", id: "rpos_cart_id", 'value': pos_cart_id});
		htmlStr.appendChild(input);
			input = cTag('input', {'type': "hidden", name: "rshipping_qty", id: "rshipping_qty", 'value': 0});
		htmlStr.appendChild(input);
		confirm_dialog(Translate('Product remove from Cart'), htmlStr, confirmRemoveCartsProduct);
	}
	else{
		const jsonData = {};
		jsonData['pos_cart_id'] = pos_cart_id;
		const url = "/Carts/removeCartsProduct/"+frompage;

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.login !==''){window.location = '/'+data.login;}
			else if(data.action==='reload'){location.reload();}
			else if(data.action==='Removed'){
				loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);	
				cartsAutoFuncCall();
				triggerEvent('filter');
			}
			else{
				showTopMessage('alert_msg', Translate('Product can not remove from cart list'));
			}
		}	
	}
}

export async function removeCartsIMEI(pos_cart_id, item_id, pos_cart_item_id){
	let frompage = segment1;

	const jsonData = {};
	jsonData['pos_cart_id'] = pos_cart_id;
	jsonData['item_id'] = item_id;
	jsonData['pos_cart_item_id'] = pos_cart_item_id;
    const url = "/Carts/removeCartsIMEI/"+frompage;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action==='reload'){location.reload();}
		else if(data.action==='Removed'){
			loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
			cartsAutoFuncCall();
			triggerEvent('filter');
		}
		else{
			showTopMessage('alert_msg', Translate('Can not removed from order list.'));
		}
	}
}

export async function removeCartsSerial(pos_cart_id, serial_number_id){
	let frompage = segment1;

	const jsonData = {};
	jsonData['pos_cart_id'] = pos_cart_id;
	jsonData['serial_number_id'] = serial_number_id;
    const url = "/Carts/removeCartsSerial/"+frompage;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action==='reload'){location.reload();}
		else if(data.action==='Removed'){
			loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);
			cartsAutoFuncCall();
			triggerEvent('filter');
		}
		else{
			showTopMessage('alert_msg', Translate('Could not removed serial'));
		}
	}
}

export async function updateCartData(hidePopup){
	let errorStatus, shipping_qty, editlink, aTag;
	let frompage = segment1;
	errorStatus = document.getElementById("errmsg_sales_price");
	errorStatus.innerHTML = '';

	let pos_cart_id = parseInt(document.getElementById("pos_cart_idvalue").value);
	if(isNaN(pos_cart_id) || pos_cart_id===''){pos_cart_id = 0;}
	let add_description = document.getElementById("add_description").value;

	let sales_price = document.getElementById("sales_price");
   if (!sales_price.valid()) return;

	let qty = document.getElementById("qty");
   if (!validateRequiredField(qty,'#errmsg_qty') || !qty.valid()) return;	
	
	shipping_qty = 0;
	if(frompage ==='POS'){}
	else{
		let repairs_status ='Estimate';
    	if(frompage==='Repairs'){
			repairs_status = document.getElementById("repairs_status").value;
			if(['Finished', 'Invoiced', 'Cancelled'].includes(repairs_status)){shipping_qty = qty.value;}
		}
		else{
			shipping_qty = parseFloat(document.getElementById("shipping_qty").value);
			if(isNaN(shipping_qty) || shipping_qty===''){shipping_qty = 0;}
		}
		if(frompage !=='Repairs'){
			errorStatus = document.getElementById("errmsg_shipping_qty");
			errorStatus.innerHTML = '';
			if(shipping_qty<0){
				errorStatus.innerHTML = Translate('Shipping/Delivered QTY should be >=0');
				return false;
			}
		}
	}
	
	let discount_is_percent = document.getElementById("discount_is_percent").value;

	if (!document.getElementById('discount').valid()) return;

	let minimum_price = document.getElementById("minimum_price").value;
	if(isNaN(minimum_price) || minimum_price===''){minimum_price = 0;}
	let unitPrice = document.getElementById("unitPrice").value;
	if(isNaN(unitPrice) || unitPrice===''){unitPrice = 0;}

	if(minimum_price>0 && minimum_price>unitPrice){
		document.getElementById("errmsg_unitPrice").innerHTML = 'Discounted Unit Price should be >='+minimum_price;
		return;
	}

	actionBtnClick('.btnmodel', Translate('Saving'), 1);

	const jsonData = {};
	jsonData['pos_cart_id'] = pos_cart_id;
	jsonData['add_description'] = add_description;
	jsonData['sales_price'] = sales_price.value;
	jsonData['qty'] = qty.value;
	jsonData['shipping_qty'] = shipping_qty;
	jsonData['discount_is_percent'] = discount_is_percent;
	jsonData['discount'] = discount.value;
    const url = "/Carts/updateCartData/"+frompage;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.action==='reload'){location.reload();}
		else if(data.action==='Updated'){
			loadCartData(document.getElementById("invoice_entry_holder"),data.cartsData);	
			cartsAutoFuncCall();
			triggerEvent('filter');
			actionBtnClick('.btnmodel', Translate('Save'), 0);
			hidePopup();
		}
		else{
			let messageInfo = '';
			if(data.maxqty === 0){
				messageInfo = Translate('There is no stock available.');
			}
			else if(data.message === ''){
				messageInfo = Translate('There is no changes made. Please try again.');
			}
			else{
				editlink = cTag('div');
					aTag = cTag('a', {'href': data.editlink, title: Translate('View Product Details')});
					aTag.innerHTML = Translate('View Product Details');
				editlink.append(`${Translate('This products availalbe qty:')} ${data.maxqty} `,aTag);
				messageInfo = editlink;
			}
			actionBtnClick('.btnmodel', Translate('Save'), 0);
			showTopMessage('alert_msg', messageInfo);
		}
	}
	return false;
}

export function categoryProductPicker(category_id){
	document.getElementById('ppcategory_id').value = category_id;
	if(document.getElementById("filterrow") && document.getElementById("filterrow").style.display === 'none'){
		document.getElementById("filterrow").style.display = 'flex';
	}
	
	if(document.getElementById("filter_category_name_html")){
		document.getElementById('filter_category_name_html').style.display = 'none';
	}

	if(document.querySelector("#filter_name_html").style.display === 'none'){
		document.querySelector("#filter_name_html").style.display = 'block';
	}
	
	if(document.querySelector("#all-category-button").style.display === 'none'){
		document.querySelector("#all-category-button").style.display = 'block';
	}

	document.querySelector("#allproductlist").style.display = 'none';

	document.querySelector("#allcategorylist").style.display = 'none';
	
	showCategoryPPProduct();
}

export async function showCategoryPPProduct(){
	document.getElementById('pagi_index').value = 0;
	let frompage = document.getElementById("frompage").value;
	let fetchingproductdataurl = '/Carts/categoryProductPicker/'+frompage;
	
	let category_id = document.getElementById('ppcategory_id').value;
	let filter_name = document.getElementById('filter_name').value;

	const jsonData = {};
	jsonData['category_id'] = category_id;
	jsonData['name'] = filter_name;
	jsonData['returnvalue'] = "datacount";
    const url = fetchingproductdataurl;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.getElementById('totalrowscount').value = data.returnStr;	
		categoryPPProduct();
	}
}

export async function addPOSPayment(){
	if(document.querySelector("#method").value==='Squareup') return;	
    const amountField =  document.getElementById("amount");
    if(!validateRequiredField(amountField,'#error_amount') || !amountField.valid()) return;

	let frompage = document.getElementById("frompage").value;
	let multiple_cash_drawers = parseInt(document.getElementById("multiple_cash_drawers").value);
	let drawer = document.getElementById("drawer").value;
	let pos_id = document.getElementById("pos_id").value;
	let method = document.getElementById("method").value;
	let amount = parseFloat(amountField.value);
	if(amount==='' || isNaN(amount)){amount = 0.00;}
	
	let amount_due = document.getElementById("amount_due").value;
	if(amount_due==='' || isNaN(amount_due)){amount_due = 0.00;}
   
	let errorStatus = document.getElementById("error_amount");
	errorStatus.innerHTML = '';
	if(multiple_cash_drawers===1 && drawer===''){
		errorStatus.innerHTML = Translate('Missing drawer');
		return false;
	}
	else if(amount===0){
		errorStatus.innerHTML = Translate('Amount should > 0');
		return false;
	}
	else{		
		const jsonData = {};
		jsonData['pos_id'] = pos_id;
		jsonData['payment_method'] = method;
		jsonData['payment_amount'] = amount;
		jsonData['amount_due'] = amount_due;
		jsonData['drawer'] = drawer;
		const url = "/Payments/addPOSPayment/"+frompage;

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.action==='reload'){location.reload();}
			else if(data.action==='Add'){
				loadPaymentData(document.querySelector('#loadPOSPayment'),data.paymentData);
				showCartCompleteBtn();
			}
			else{
				showTopMessage('alert_msg', Translate('Could not save payment.'));
			}			
		}
	}
}

export async function removePOSPayment(pos_payment_id, squareYN) {
	let dialogConfirm, pTag, jsonData;
	let frompage = segment1;
	let pos_id = document.getElementById("pos_id").value;
	if(squareYN===1){
		dialogConfirm = cTag('div');
			pTag = cTag('p', {'style': "text-align: left;"});
			pTag.innerHTML = Translate('You must void this payment from within your SQUARE APP manually.');
			pTag.appendChild(cTag('br'));
			pTag.appendChild(cTag('br'));
			pTag.append(Translate('Have you already removed this payment from your SQUARE APP?'));
		dialogConfirm.appendChild(pTag);

		popup_dialog(
			dialogConfirm,
			{
				title:Translate('Squareup payment remove'),
				width:400,
				buttons: {
					_Close: {
						text: Translate('Close'), class: 'btn defaultButton', 'style': "margin-left: 10px;", click: function(hide) {
							hide();
							return false;
						},
					},
					_Yes:{
						text: Translate('Yes'), class: 'btn saveButton archive', 'style': "margin-left: 10px;", click: function(hide) {
							removePOSPayment(pos_payment_id, 0);
							hide();
							return false;
						},
					}
				}
			}
		);
	}
	else{
		jsonData = {};
		jsonData['pos_id'] = pos_id;
		jsonData['pos_payment_id'] = pos_payment_id;
		const url = "/Payments/removePOSPayment/"+frompage;

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.action==='reload'){location.reload();}
			else if(data.action==='Removed'){
				loadPaymentData(document.querySelector('#loadPOSPayment'),data.paymentData);				
				showCartCompleteBtn();
				triggerEvent('filter');
			}
			else{
				showTopMessage('alert_msg', Translate('Could not remove payment.'));
			}
		}
	}
}

/*=========Product Picker=========*/
export async function showProductPicker(){
	let product_picker_button;
	let frompage = document.getElementById("frompage").value;
	let btnName = document.getElementById("product-picker-button").name;
	if(btnName==='showcategorylist'){
		product_picker_button = document.getElementById("product-picker-button");
		product_picker_button.setAttribute('name', 'closeallcategorylist');
		product_picker_button.setAttribute('title', Translate('Close Product Picker'));
		product_picker_button.innerHTML = Translate('Close Product Picker');

		document.getElementById('all-category-button').style.display = 'none';

		if(document.getElementById('product-picker').style.display === 'none'){
			document.getElementById('product-picker').style.display = 'block';
		}
		
		if(document.getElementById("filterrow") && document.getElementById("filterrow").style.display === 'none'){
			document.getElementById("filterrow").style.display = 'flex';
		}

		document.getElementById('filter_name_html').style.display = 'none';

		document.getElementById('allproductlist').style.display = 'none';
		
		if(document.getElementById('allcategorylist').style.display === 'none'){
			document.getElementById('allcategorylist').style.display = 'block';
		}
		
		if(document.querySelector(".prevlist").style.display === 'none'){
			document.querySelector(".prevlist").style.display = 'block';
		}
		
		if(document.querySelector(".nextlist").style.display === 'none'){
			document.querySelector(".nextlist").style.display = 'block';
		}
				
		document.getElementById('pagi_index').value = 0;

		const jsonData = {};
		jsonData['returnvalue'] = "datacount";
		jsonData['frompage'] = frompage;
		const url = '/Carts/productPicker/';

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			document.getElementById('totalrowscount').value = data.returnStr;
			productPickerCategory();
		}
	}
	else{
		document.querySelector("#product-picker").style.display = 'none';
		
		if(document.getElementById("filterrow")){
			document.getElementById("filterrow").style.display = 'none';;
		}

		document.querySelector("#all-category-button").style.display = 'none';
			
		product_picker_button = document.getElementById("product-picker-button");
		product_picker_button.setAttribute('name', 'showcategorylist');
		product_picker_button.setAttribute('title',Translate('Open Product Picker'));
		product_picker_button.innerHTML = Translate('Open Product Picker');
		
		document.querySelector("#allproductlist").style.display = 'none';

		document.querySelector("#allcategorylist").style.display = 'none';

		document.querySelector(".prevlist").style.display = 'none';

		document.querySelector(".nextlist").style.display = 'none';
	}
}

export function reloadProdPkrCategory(){
	document.getElementById('pagi_index').value = 0;
	document.getElementById("product-picker-button").name = 'showcategorylist';
	showProductPicker();
}

export function preNextCategory(){	
	document.getElementById('pagi_index').value = this.getAttribute('data-page');
	if(this.getAttribute('data-category')==='main') productPickerCategory();
	else if(this.getAttribute('data-category')==='sub') categoryPPProduct();
	else if(this.getAttribute('data-category')==='imei') showProductPickerIMEI();
}

export function addCartsProductPicker(sku){
	document.getElementById("search_sku").value = sku;
	if(document.getElementById("frompage").value==='Inventory_Transfer'){
		triggerEvent('changeCart');
		showProductPicker();
	}
	else{
		addCartsProduct();
		showProductPicker();
	}
}

export function calculateTax(taxable_total, taxes_rate, tax_inclusive){
	let taxes_percentage = calculate('mul',taxes_rate,0.01,false);
	let returntax = 0.00;
	if(tax_inclusive>0){
		returntax =  calculate('sub',taxable_total,calculate('div',taxable_total,calculate('add',taxes_percentage,1,false),2),2);
	}
	else{
		returntax = calculate('mul',taxable_total,taxes_percentage,2);
	}
	return returntax;
}

export function cartsAutoFuncCall(){
	autoSearchSerial();
	AJautoComplete_IMEI();
	calculateCartTotal();
}


//product-picker---------------------------
export async function productPickerCategory(){
	let frompage = document.querySelector("#frompage").value;	
	let pagi_index = parseInt(document.getElementById('pagi_index').value);
	if(isNaN(pagi_index) || pagi_index===''){pagi_index = 0;}	
	
	let starting_val = parseInt(pagi_index*12);
	if(isNaN(starting_val) || starting_val ===''){starting_val = 0;}
	let ending_val = parseInt(starting_val+12);
	
	let totalrowscount = parseInt(document.getElementById('totalrowscount').value);
	if(isNaN(totalrowscount) || totalrowscount ===''){totalrowscount = 0;}
	
	if(totalrowscount<ending_val){ending_val = totalrowscount;}

	if(totalrowscount===0){
		document.querySelector("#PPfromtodata").innerHTML = '"0 - 0 / 0"';
	}
	else{
		document.querySelector("#PPfromtodata").innerHTML = parseInt(starting_val+1)+' - '+ending_val +' / '+totalrowscount;
	}
	
	if(pagi_index>0){
		document.querySelectorAll(".prevlist button").forEach(item=>{
			item.disabled = false;
			item.setAttribute('data-page',pagi_index-1);
			item.setAttribute('data-category','main');
		})
	}
	else{
		document.querySelectorAll(".prevlist button").forEach(item=>{
			item.disabled = true;
			item.removeAttribute('data-page');
			item.removeAttribute('data-category');
		})
	}
	if(totalrowscount>ending_val){
		document.querySelectorAll(".nextlist button").forEach(item=>{
			item.disabled = false;
			item.setAttribute('data-page',pagi_index+1);
			item.setAttribute('data-category','main');
		})
	}
	else{
		document.querySelectorAll(".nextlist button").forEach(item=>{
			item.disabled = true;
			item.removeAttribute('data-page');
			item.removeAttribute('data-category');
		})
	}
	
	const jsonData = {};
	jsonData['frompage'] = frompage;
	jsonData['returnvalue'] = "data";
	jsonData['starting_val'] = starting_val;
    const url = '/Carts/productPicker/';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let PPID = document.querySelector('#allcategorylist');
		PPID.innerHTML = '';
		let productPickerCategoryRow = cTag('div', {class: "flexStartRow"});
		if(data.returnStr && data.returnStr.length>0){
			data.returnStr.forEach(item=>{
					let productPickerCategoryColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnLG3` });
						let productPickerCategoryName = cTag('div',{ 'class':`redbg`, 'style': "height: 80px;", 'click':()=>categoryProductPicker(item.category_id) });
							let pTag = cTag('p',{ 'style':`color: white;` });
							if(item.category_name==='noCategory') pTag.innerHTML = Translate('No Category');
							else pTag.innerHTML = item.category_name;
						productPickerCategoryName.appendChild(pTag);
					productPickerCategoryColumn.appendChild(productPickerCategoryName);
				productPickerCategoryRow.appendChild(productPickerCategoryColumn);
			})
		}
		PPID.appendChild(productPickerCategoryRow);
	}
}

export async function categoryPPProduct(){
	let frompage = document.querySelector("#frompage").value;
	let fetchingproductdataurl = '/Carts/categoryProductPicker/'+frompage;
	
	let pagi_index = parseInt(document.getElementById('pagi_index').value);
	if(isNaN(pagi_index) || pagi_index===''){pagi_index = 0;}
	
	let starting_val = parseInt(pagi_index*12);
	if(isNaN(starting_val) || starting_val ===''){starting_val = 0;}
	let ending_val = parseInt(starting_val+12);
	
	let totalrowscount = parseInt(document.getElementById('totalrowscount').value);
	if(isNaN(totalrowscount) || totalrowscount ===''){totalrowscount = 0;}
	if(totalrowscount<ending_val){
		ending_val = totalrowscount;
	}
	if(totalrowscount===0){
		document.querySelector("#PPfromtodata").innerHTML = '"0 - 0 / 0"';
	}
	else{
		document.querySelector("#PPfromtodata").innerHTML = parseInt(starting_val+1)+' - '+ending_val +' / '+totalrowscount;
	}
				
	if(pagi_index>0){
		document.querySelectorAll(".prevlist button").forEach(item=>{
			item.disabled = false;
			item.setAttribute('data-page',pagi_index-1);
			item.setAttribute('data-category','sub');
		})
	}
	else{
		document.querySelectorAll(".prevlist button").forEach(item=>{
			item.disabled = true;
			item.removeAttribute('data-page');
			item.removeAttribute('data-category');
		})
	}
	if(totalrowscount>ending_val){
		document.querySelectorAll(".nextlist button").forEach(item=>{
			item.disabled = false;
			item.setAttribute('data-page',pagi_index+1);
			item.setAttribute('data-category','sub');
		})
	}
	else{
		document.querySelectorAll(".nextlist button").forEach(item=>{
			item.disabled = true;
			item.removeAttribute('data-page');
			item.removeAttribute('data-category');
		})
	}
	
	let category_id = document.getElementById('ppcategory_id').value;
	let filter_name = document.getElementById('filter_name').value;
	document.getElementById('filter_name').value = '';
	
	const jsonData = {};
	jsonData['category_id'] = category_id;
	jsonData['name'] = filter_name;
	jsonData['starting_val'] = starting_val;
	jsonData['returnvalue'] = "data";
    const url = fetchingproductdataurl;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let PPID = document.querySelector('#allproductlist');
		PPID.innerHTML = '';
		if(PPID.style.display === 'none'){
			PPID.style.display = 'block';
		}
		let productCategoryRow = cTag('div', {class: "flexStartRow"});
		if(data.returnStr && data.returnStr.length>0){
			data.returnStr.forEach(item=>{
				let pTag;
				let productCategoryColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnLG3` });
					let productCategoryValue = cTag('div',{ 'class':`redbg product`, 'style': "height: 100%;", 'id':item.product_id,'click':()=>getClickHandler(item,data.frompage),'title':item.product_name });
						pTag = cTag('p',{ 'style': "text-align: left; color: white;" });
						pTag.innerHTML = item.shortproduct_name;
					productCategoryValue.appendChild(pTag);
						let skuPriceContainer = cTag('div',{class:'flexSpaBetRow'});							
							pTag = cTag('p',{ 'style': "color: white;" });
							pTag.innerHTML = `${Translate('SKU/Barcode')}: ${item.sku}`;
						skuPriceContainer.appendChild(pTag);
							pTag = cTag('p',{ 'style': "color: white;" });
							pTag.innerHTML = `${addCurrency(item.regular_price)} (${item.current_inventory})`;
						skuPriceContainer.appendChild(pTag);
					productCategoryValue.appendChild(skuPriceContainer);
				productCategoryColumn.appendChild(productCategoryValue);
				productCategoryRow.appendChild(productCategoryColumn);
			})
		}
		PPID.appendChild(productCategoryRow);
		
		document.querySelectorAll(".product").forEach(item=>{
			item.addEventListener('click',function(){
				if(!this.classList.contains('active')){this.classList.add('active');}
			});
		});
	}
	

	function getClickHandler(info,frompage){
		let click;
		click = ()=>addCartsProductPicker(info.sku);
		if(frompage === 'Purchase_orders'){
			click = ()=>triggerEvent('changeCart',info.product_id);
		}
		else if(frompage === 'Inventory_Transfer'){
			// click = ()=>addProductToITCart(info.product_id, info.product_name);
			click = ()=>triggerEvent('changeCart',info.product_id);
		}
		if(['POS', 'Inventory_Transfer'].includes(frompage) && info.product_type==='Mobile Devices'){
			click = ()=>productPickerIMEI(info.product_id);
			if(frompage === 'Inventory_Transfer'){
				click = ()=>allITImeiList(info.product_id, info.product_name);
			}
		}
		click();
	}	
}

export async function showProductPickerIMEI(){
	let prevBtn, nextBtn, pTag;
	let pagi_index = parseInt(document.getElementById('pagi_index').value);
	if(isNaN(pagi_index) || pagi_index===''){pagi_index = 0;}
	
	let starting_val = parseInt(pagi_index*12);
	if(isNaN(starting_val) || starting_val ===''){starting_val = 0;}
	let ending_val = parseInt(starting_val+12);
	
	let totalrowscount = parseInt(document.getElementById('totalrowscount').value);
	if(isNaN(totalrowscount) || totalrowscount ===''){totalrowscount = 0;}
	if(totalrowscount<ending_val){ending_val = totalrowscount;}

	if(totalrowscount===0){
		document.querySelector("#PPfromtodata").innerHTML = "0 - 0 / 0";
	}
	else{
		document.querySelector("#PPfromtodata").innerHTML = parseInt(starting_val+1)+' - '+ending_val +' / '+' '+totalrowscount;
	}
	if(pagi_index>0){
		prevBtn = document.querySelector(".prevlist button");
		prevBtn.disabled = false;
		prevBtn.setAttribute('data-page',pagi_index-1);
		prevBtn.setAttribute('data-category','imei');
	}
	else{
		prevBtn = document.querySelector(".prevlist button");
		prevBtn.disabled = true;
		prevBtn.removeAttribute('data-page');
		prevBtn.removeAttribute('data-category');
	}
	if(totalrowscount>ending_val){
		nextBtn = document.querySelector(".nextlist button");
		nextBtn.disabled = false;
		nextBtn.setAttribute('data-page',pagi_index+1);
		nextBtn.setAttribute('data-category','imei');
	}
	else{
		nextBtn = document.querySelector(".nextlist button");
		nextBtn.disabled = true;
		nextBtn.removeAttribute('data-page');
		nextBtn.removeAttribute('data-category');
	}
	
	let product_id = document.getElementById('ppproduct_id').value;

	const jsonData = {};
	jsonData['product_id'] = product_id;
	jsonData['starting_val'] = starting_val;
	jsonData['returnvalue'] = 'data';
    const url = '/Carts/productPickerIMEI/';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let allproductlist = document.getElementById('allproductlist');
		allproductlist.innerHTML = '';
		let showProductRow = cTag('div', {class: "flexStartRow"});
		if(data.returnData && data.returnData.length>0){
			data.returnData.forEach(item=>{
					let showProductColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnLG3` });
						let showProductField = cTag('div',{ 'class':`redbg product`, 'style': " height: 100%;", 'id':item.item_id,'click':()=>addCartsProductPicker(item.item_number),'title':item.product_name });
							pTag = cTag('p',{ 'style': "text-align: left; color: white;" });
							pTag.innerHTML = item.shortproduct_name;
						showProductField.appendChild(pTag);
							let skuPriceContainer = cTag('div',{class:'flexSpaBetRow'});							
								pTag = cTag('p',{ 'style': "color: white;" });
								pTag.innerHTML = item.item_number;
							skuPriceContainer.appendChild(pTag);
								pTag = cTag('p',{ 'style': "color: white;" });
								pTag.innerHTML = `${addCurrency(item.regular_price)} (${item.current_inventory})`;
							skuPriceContainer.appendChild(pTag);
						showProductField.appendChild(skuPriceContainer);
					showProductColumn.appendChild(showProductField);
				showProductRow.appendChild(showProductColumn);
			})
		}
		allproductlist.appendChild(showProductRow);
		if(document.getElementById('allproductlist').style.display === 'none'){
			document.getElementById('allproductlist').style.display = 'block';
		}
		
		document.querySelectorAll(".product").forEach(item=>{
			item.addEventListener('click', event=>{
				if(event.target.classList.contains('active')){
					event.target.classList.remove('active');
				}
				else{
					event.target.classList.add('active');
				}
			});
		})
	}
}

/*=======Used for POS, Orders and Repairs===========*/
export function calculateCartTotal(){
	let discountvalue, rowtotalstr;
	let timeQtyTotal = 0;
	let shippingTimeQty = 0;

	let repairs_status = 'Estimate';
    if(document.getElementById("repairs_status")){repairs_status = document.getElementById("repairs_status").value;}
	
	if(document.getElementById("invoice_entry_holder")===null){return false;}
	let taxable_total = 0;
	let nontaxable_total = 0;
	let hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	if(hasdata.length>10){
		let pos_cart_idarray = document.getElementsByName("pos_cart_id[]");

		if(pos_cart_idarray.length>0){
			document.getElementById("barcodeserno").innerHTML = parseInt(pos_cart_idarray.length+1);
			
			for(let p=0; p<pos_cart_idarray.length; p++){
				let pos_cart_id = pos_cart_idarray[p].value;
				let item_type = document.getElementById("item_type"+pos_cart_id).value;
                let qty = parseFloat(document.getElementById("qty"+pos_cart_id).value);
                if(['Finished', 'Invoiced', 'Cancelled'].includes(repairs_status) && item_type !== 'one_time'){
                	qty = parseFloat(document.getElementById("shipping_qty"+pos_cart_id).value);
               	}
				if(qty==='' || isNaN(qty)){qty=0;}
				
				timeQtyTotal = timeQtyTotal + qty;
				if(segment1 === 'Orders'){
					shippingTimeQty = shippingTimeQty + parseFloat(document.getElementById("shipping_qty"+pos_cart_id).value);
				}

				let sales_price = parseFloat(document.getElementById("sales_price"+pos_cart_id).value);
				if(sales_price==='' || isNaN(sales_price)){sales_price=0;}
				
				let qty_value = calculate('mul',sales_price,qty,2);
				
				let discount_is_percent = document.getElementById("discount_is_percent"+pos_cart_id).value;
				let discount = parseFloat(document.getElementById("discount"+pos_cart_id).value);
				if(discount==='' || isNaN(discount)){discount = 0;}					
				
				if(discount_is_percent>0){
					discountvalue = calculate('mul',qty_value,calculate('mul',0.01,discount,false),2);
				}
				else{ 
					discountvalue = calculate('mul',discount,qty,2);
				}
				
				if(discountvalue==='' || isNaN(discountvalue)){discountvalue = 0;}
				
				let total = calculate('sub',qty_value,discountvalue,2);
				if(total==='' || isNaN(total)){total = 0;}
				
				let taxable = parseFloat(document.getElementById("taxable"+pos_cart_id).value);
				if(taxable===0){
					nontaxable_total = calculate('add',nontaxable_total,total,2);
				}
				else{
					taxable_total = calculate('add',taxable_total,total,2);						
				}
				
				rowtotalstr = addCurrency(qty_value);
				if(discountvalue !== 0) rowtotalstr += '<br />'+addCurrency(discountvalue*-1);				
				document.getElementById("totalstr"+pos_cart_id).innerHTML = rowtotalstr;
			}
		}
		else{
			return false;
		}
	}
	else{
		document.getElementById("barcodeserno").innerHTML = 1;
	}
	
	document.getElementById("taxable_totalstr").innerHTML = addCurrency(taxable_total);

	if(['Repairs', 'POS', 'Orders'].includes(segment1) && document.getElementById("timeQtyTotal")){
		document.getElementById("timeQtyTotal").innerHTML = timeQtyTotal;
	}
	if('Orders'=== segment1){
		document.getElementById("shippingTimeQty").innerHTML = shippingTimeQty;
	}

	let nonTaxRowObj = document.getElementById("nontaxable_totalrow");
	if(nontaxable_total >0 || nontaxable_total <0){
		if(nonTaxRowObj.style.display === 'none'){nonTaxRowObj.style.display = 'block';}
	}
	else if(!nonTaxRowObj.style.display === 'none'){nonTaxRowObj.style.display = 'none';}

	let nontaxable_totalstr = document.getElementById("nontaxable_totalstr");
	nontaxable_totalstr.innerHTML = addCurrency(nontaxable_total);

	if(nontaxable_total === 0.00) {
		nonTaxRowObj.style.display = 'none';
	}
	else {
		nonTaxRowObj.style.display = '';
	}

	document.getElementById("taxable_total").value = taxable_total;
	document.getElementById("nontaxable_total").value = nontaxable_total;
	
	/*=======Calculate taxes total value======*/
	let taxes_percentage1 = parseFloat(document.getElementById("taxes_percentage1").value);
	if(taxes_percentage1==='' || isNaN(taxes_percentage1)){taxes_percentage1 = 0;}			
	let tax_inclusive1 = parseInt(document.getElementById("tax_inclusive1").value);
	if(tax_inclusive1==='' || isNaN(tax_inclusive1)){tax_inclusive1 = 0;}			
	let taxes_total1 = calculateTax(taxable_total, taxes_percentage1, tax_inclusive1);
	document.getElementById("taxes_total1").value = taxes_total1;
	document.getElementById("taxes_total1str").innerHTML = addCurrency(taxes_total1);
	
	let taxes_percentage2 = parseFloat(document.getElementById("taxes_percentage2").value);
	if(taxes_percentage2==='' || isNaN(taxes_percentage2)){taxes_percentage2 = 0;}			
	let tax_inclusive2 = parseInt(document.getElementById("tax_inclusive2").value);
	if(tax_inclusive2==='' || isNaN(tax_inclusive2)){tax_inclusive2 = 0;}			
	let taxes_total2 = calculateTax(taxable_total, taxes_percentage2, tax_inclusive2);
	
	document.getElementById("taxes_total2").value = taxes_total2;
	document.getElementById("taxes_total2str").innerHTML = addCurrency(taxes_total2);
	
	taxable_total = parseFloat(document.getElementById("taxable_total").value);
	taxes_total1 = parseFloat(document.getElementById("taxes_total1").value);
	taxes_total2 = parseFloat(document.getElementById("taxes_total2").value);
	nontaxable_total = parseFloat(document.getElementById("nontaxable_total").value);
	
	if(tax_inclusive1>0){taxes_total1 = 0;}
	if(tax_inclusive2>0){taxes_total2 = 0;}
	
	let grand_total = calculate('add',calculate('add',taxes_total1,taxes_total2,2),calculate('add',taxable_total,nontaxable_total,2),2);
	document.getElementById("grand_totalstr").innerHTML = addCurrency(grand_total);
	
	document.getElementById("grand_total").value = grand_total;
	showCartCompleteBtn();
}

export function calculateChangeCartTotal(){
    if(!document.getElementById("sales_price")) return;
	let shipping_qty, discount_value;
	let repairs_status='Estimate';
    if(segment1==='Repairs'){repairs_status = document.getElementById("repairs_status").value;}
	
	let sales_price = parseFloat(document.getElementById("sales_price").value);
	let minimum_price = parseFloat(document.getElementById("minimum_price").value);
	if(sales_price==='' || isNaN(sales_price)){sales_price = 0;}
	document.getElementById("salesPriceStr").innerHTML = addCurrency(sales_price);
	
	let qty = parseFloat(document.getElementById("qty").value);
	if(qty==='' || isNaN(qty)){qty = 0;}
	if(segment1 !=='POS'){
		if(segment1 ==='Repairs'){
			shipping_qty = 0;
			if(['Finished', 'Invoiced', 'Cancelled'].includes(repairs_status)){shipping_qty = qty;}
		}
		else{
			shipping_qty = parseFloat(document.getElementById("shipping_qty").value);
			if(isNaN(shipping_qty) || shipping_qty===''){
				shipping_qty = 0;
			}
		}
		if(shipping_qty>qty && segment1 !=='Repairs'){								
			document.getElementById("qty").value = qty = shipping_qty;
		}
	}
	let qty_value = calculate('mul',sales_price,qty,2);
	document.getElementById("qty_value").value = qty_value;
	document.getElementById("qtyValueStr").innerHTML = addCurrency(qty_value);
	
	if(segment1 ==='POS' || segment1 ==='Repairs'){}
	else{
		let shipping_qty_value = calculate('mul',sales_price,shipping_qty,2);
		document.getElementById("shipping_qty_value").value = shipping_qty_value;
		document.getElementById("shippingQtyValueStr").innerHTML = addCurrency(shipping_qty_value);
	}
	
	let discount_is_percent = document.getElementById("discount_is_percent").value;
	let discount = parseFloat(document.getElementById("discount").value);
	if(discount==='' || isNaN(discount)){discount = 0;}					
	
	let discountField = document.getElementById("discount");
	if(discount_is_percent>0){
		const maxDiscount = sales_price-minimum_price
		let maxDiscountPercent = ((maxDiscount / sales_price)*100).toString() ;
		//round into 2 decimal-point
		const indexOfDot = maxDiscountPercent.indexOf('.');
		if(indexOfDot>=0) maxDiscountPercent = maxDiscountPercent.slice(0,indexOfDot+3);
		discountField.setAttribute('data-max',maxDiscountPercent);
	} 
	else{
		discountField.setAttribute('data-max', sales_price-minimum_price);
	} 

	if(qty_value !==0){
		if(discount_is_percent>0){
			if(discount>99.99){
				document.getElementById("discount").value = 99.99;
				discount = 99.99;
			}
			discount_value = calculate('mul',qty_value,calculate('mul',0.01,discount,false),2);
		}
		else{ 
			discount_value = calculate('mul',discount,qty,2);
			if(discount_value > qty_value){				
				document.getElementById("discount").value = sales_price;
				discount_value = calculate('mul',sales_price,qty,2);
			}
			// discount_value = discount;
		}
	}
	else{
		if(discount_is_percent>0){
			if(discount<-99.99){
				document.getElementById("discount").value = -99.99;
				discount = -99.99;
			}
			discount_value = calculate('mul',qty_value,calculate('mul',0.01,discount,false),2);
		}
		else{ 
			discount_value = calculate('mul',discount,qty,2);
			if(discount_value < qty_value){				
				document.getElementById("discount").value = sales_price;
				discount_value = calculate('mul',sales_price,qty,2);
			}
			// discount_value = discount;
		}
	}
	
	if(discount_value==='' || isNaN(discount_value)){discount_value = 0;}
	document.getElementById("discountValueStr").innerHTML = addCurrency(discount_value);
	
	let total = calculate('sub',qty_value,discount_value,2);
	if(total==='' || isNaN(total)){total = 0.00;}
	document.getElementById("totalValueStr").innerHTML = addCurrency(total);

	let unitPrice = calculate('div',total,qty,2);
	if(unitPrice==='' || isNaN(unitPrice)){unitPrice = 0.00;}
	document.getElementById("unitPrice").value = unitPrice;
	
}

export function confirmRemoveCartsProduct(hidePopup){
	let pos_cart_id = document.getElementById("rpos_cart_id").value;
	let shipping_qty = document.getElementById("rshipping_qty").value;
	hidePopup();
	removeCartsProduct(pos_cart_id, shipping_qty);
}

export function showCartCompleteBtn(){
	let shipping_qty, payment_amountarray, payment_amount, listcount, grand_total, amountduestr, m;
	
	let completed = 1;
	let error = 0;
	let showShipAllBtn = 0;
	if(segment1==='Repais'){
		let status = document.getElementById("repairs_status").value;
	
		if(status==='Estimate'){
			document.querySelectorAll(".EstimateTitle").forEach(oneFieldObj=>{oneFieldObj.style = {"font-weight":'bold'};});
			document.querySelectorAll(".Estimate").forEach(oneFieldObj=>{oneFieldObj.style = {"background-color":'#fff', "font-weight":'bold'};});
			document.querySelectorAll(".DeliveredTitle").forEach(oneFieldObj=>{oneFieldObj.style = {"font-weight":'normal'};});
			document.querySelectorAll(".Delivered").forEach(oneFieldObj=>{oneFieldObj.style = {"background-color":'#f5f5f6', "font-weight":'normal'};});			
		}
		else{
			document.querySelectorAll(".EstimateTitle").forEach(oneFieldObj=>{oneFieldObj.style = {"font-weight":'normal'};});
			document.querySelectorAll(".Estimate").forEach(oneFieldObj=>{oneFieldObj.style = {"background-color":'#f5f5f6',"font-weight":'normal'};});
			document.querySelectorAll(".DeliveredTitle").forEach(oneFieldObj=>{oneFieldObj.style = {"font-weight":'bold'};});
			document.querySelectorAll(".Delivered").forEach(oneFieldObj=>{oneFieldObj.style = {"background-color":'#fff', "font-weight":'bold'};});	
		}
	}
	
	listcount = 0;
	if(document.querySelector("#loadPOSPayment")){
		listcount = document.querySelectorAll("#loadPOSPayment .paymentRow").length;
	}
	
	let hasdata = document.getElementById("invoice_entry_holder").innerHTML;
	if(hasdata.length>10){
		let totalQty = 0;
		let pos_cart_idarray = document.getElementsByName("pos_cart_id[]");
		if(pos_cart_idarray.length>0){
			for(let p=0; p<pos_cart_idarray.length; p++){
				let pos_cart_id = pos_cart_idarray[p].value;
														
				let qty = parseFloat(document.getElementById("qty"+pos_cart_id).value);
				if(qty==='' || isNaN(qty)){qty = 0;}
				
				if(segment1 ==='Repairs'){
					shipping_qty = parseFloat(document.getElementById("shipping_qty"+pos_cart_id).value);
					if(shipping_qty==='' || isNaN(shipping_qty)){shipping_qty = 0;}
					totalQty = calculate('add',shipping_qty,totalQty,2);
					if(shipping_qty===0){error++;}
				}
				else{
					totalQty = calculate('add',qty,totalQty,2);
					if(qty===0){error++;}
				}
				if(segment1 ==='Orders'){
					shipping_qty = parseFloat(document.getElementById("shipping_qty"+pos_cart_id).value);
					if(shipping_qty==='' || isNaN(shipping_qty)){shipping_qty = 0;}
					if(qty>shipping_qty){
						error++;
						let item_type = document.getElementById("item_type"+pos_cart_id).value;
						let require_serial_no = parseInt(document.getElementById("require_serial_no"+pos_cart_id).value);
						if(item_type==='product' && require_serial_no===0){showShipAllBtn++;}
					}
				}
			}
		}
		
		if(totalQty===0){
			completed = 0;
		}
	}
	else{
		completed = 0;
		if(segment1 ==='Orders'){
			let paymentYN = 0;
			payment_amountarray = document.getElementsByName("payment_amount[]");
			for( m = 0; m < listcount; m++) {
				payment_amount = parseFloat(payment_amountarray[m].value);
				if(payment_amount==='' || isNaN(payment_amount)){payment_amount =0.00;}
				if(payment_amount !==0){paymentYN++;}
			}
			if(paymentYN>0){completed = 1;}
		}
	}
	
	grand_total = parseFloat(document.getElementById("grand_total").value);
	if(grand_total==='' || isNaN(grand_total)){grand_total =0.00;}
	if(listcount>0){
		let paymenttotalamount = 0;
		payment_amountarray = document.getElementsByName("payment_amount[]");
		for( m = 0; m < listcount; m++) {
			payment_amount = parseFloat(payment_amountarray[m].value);
			if(payment_amount==='' || isNaN(payment_amount)){payment_amount =0.00;}
			paymenttotalamount = calculate('add',paymenttotalamount,payment_amount,2);
		}
		grand_total = calculate('sub',grand_total,paymenttotalamount,2);					    
	}
	
	grand_total = grand_total;
	let available_credit = parseFloat(document.getElementById("available_credit").value);
	if(available_credit==='' || isNaN(available_credit) || available_credit<0){available_credit = 0;}
	
	if((grand_total-available_credit) > 0 ){
		error++;
	}
	
	if(grand_total<0) document.getElementById("amount_duetxt").innerHTML = Translate('Give change amount of');
	else document.getElementById("amount_duetxt").innerHTML = Translate('Amount Due');
	amountduestr = addCurrency(grand_total);
	
	document.getElementById("amountduestr").innerHTML = amountduestr;
	
	document.getElementById("amount").value = grand_total;
	document.getElementById("amount_due").value = grand_total;
	document.getElementById("completed").value = completed;
	if(document.querySelector("#ShippedAllProducts")){
		if(showShipAllBtn===0){
			document.querySelector("#ShippedAllProducts").style.display = 'none';
		}
		else{
			if(document.querySelector("#ShippedAllProducts").style.display === 'none'){
				document.querySelector("#ShippedAllProducts").style.display = 'block';
			}
		}
	}
	if(error===0){
		if(document.querySelector("#CompleteBtn").style.display === 'none'){
			document.querySelector("#CompleteBtn").style.display = 'flex';
		}
		
		document.querySelector("#CompleteBtnDis").style.display = 'none';

		return true;
	}
	else{
		document.querySelector("#CompleteBtn").style.display = 'none';

		if(document.querySelector("#CompleteBtnDis").style.display === 'none'){
			document.querySelector("#CompleteBtnDis").style.display = 'block';
		}
		return false;
	}	
}

//load Cart-data
export function loadCartData(parentNode,cartsData){
	parentNode.innerHTML = '';
	if(!Array.isArray(cartsData) && cartsData.cartData.length>0){
		let frompage = segment1;
	
		cartsData.cartData.forEach((item,indx) => {
			let description = document.createDocumentFragment();
			let linkedsku;
			let rowremoveicon = cTag('i',{ 'style':`cursor:pointer;`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove Item'),'click':()=>removeCartsProduct(item.pos_cart_id, item.shipping_qty),'class':`fa fa-trash-o` });
			
			if(item.item_id>0){
				linkedsku = cTag('a',{ 'href':`/Products/view/${item.item_id}`,'style': "color: #009; text-decoration: underline;", 'title':Translate('Edit') });
				linkedsku.append(item.sku+' ');
				linkedsku.appendChild(cTag('i',{ 'class':`fa fa-link` }));
			}
			let spanTag = cTag('span',{id:`productname${item.pos_cart_id}`});
			if(item.item_type !=='one_time'){
				spanTag.innerHTML = item.description.replace(`(${item.sku})`,'');
				description.append(spanTag, '(', linkedsku, ')');
			}
			else{
				spanTag.innerHTML = item.description;
				description.append(spanTag);
			}
			if(item.add_description !==''){
					let pTag = cTag('p', {style: "margin: 0; padding-left: 10px;"});
					pTag.innerHTML = item.add_description;
				description.appendChild(pTag);
			}
	
			if(item.item_type==='cellphones' || (item.item_type==='product' && item.require_serial_no>0)){
					const cellPhoneRow = cTag('div',{ 'class':`flexSpaBetRow` });
						const cellPhoneColumn = cTag('div',{ 'class':`columnLG12` });
						cellPhoneColumn.append(description);
					cellPhoneRow.appendChild(cellPhoneColumn);
					if(item.item_type==='cellphones'){
						if(item.cellphonesInfo.length){
							rowremoveicon = ""; 
							item.cellphonesInfo.forEach(info=>{
									let imeiViewColumn = cTag('div',{ 'class':`columnLG12`, 'style': "padding-left: 10px;" });
										let aTag = cTag('a',{ 'href':`/IMEI/view/${info.item_number}`,'style': "color: #009; text-decoration: underline;", 'title':Translate('View IMEI details') });
										aTag.append(info.item_number+' ');
										aTag.appendChild(cTag('i',{ 'class':`fa fa-link` }));
									imeiViewColumn.appendChild(aTag);
									imeiViewColumn.append(info.carrier_name+' ');
									imeiViewColumn.appendChild(cTag('i',{ 'class':`fa fa-trash-o`,'click':()=>removeCartsIMEI(item.pos_cart_id, info.item_id, info.pos_cart_item_id),'data-original-title':Translate('Remove IMEI Number'),'data-toggle':`tooltip`,'style':`cursor: pointer` }));
								cellPhoneRow.appendChild(imeiViewColumn);
							})
						}
						if(frompage==='Orders' && item.qty<=item.shipping_qty){}
						else if(cartsData.edit>0){
								let orderColumn = cTag('div',{ 'class':`columnSM6`, 'style': "padding-left: 10px;" });
								orderColumn.appendChild(cTag('input',{ 'class':`form-control item_number`,'name':`item_number${item.pos_cart_id}`,'id':`item_number${item.pos_cart_id}`,'title':item.pos_cart_id,'placeholder':Translate('IMEI Number'),'maxlength':`20` }));
							cellPhoneRow.appendChild(orderColumn);
						}
					}
					else if(item.item_type==='product' && item.require_serial_no>0){
						if(item.serialInfo.length){
							rowremoveicon = ""; 
							item.serialInfo.forEach(info=>{
									let productSerialColumn = cTag('div',{ 'class':`columnLG12`, 'style': "padding-left: 10px;" });
									// productSerialColumn.append(`${info.serial_number}  `);
									productSerialColumn.append(info.serial_number  );
									productSerialColumn.appendChild(cTag('i',{ 'style':`cursor: pointer; padding-left: 10px;`,'data-toggle':`tooltip`,'data-original-title':Translate('Remove Serial Number'),'click':()=>removeCartsSerial(item.pos_cart_id, info.serial_number_id),'class':`fa fa-trash-o` }));
								cellPhoneRow.appendChild(productSerialColumn);
							})
						}
						if(cartsData.edit>0){
								let cartsEditColumn = cTag('div',{ 'class':`columnSM6`, 'style': "padding-left: 10px;" });
								cartsEditColumn.appendChild(cTag('input',{ 'class':`form-control serial_number`,'alt':`item_id${item.item_id}`,'id':`serial${item.pos_cart_id}`,'placeholder':Translate('Serial Number'),'maxlength':`20` }));
							cellPhoneRow.appendChild(cartsEditColumn);
							cellPhoneRow.appendChild(cTag('div',{ 'class':`columnSM6 error_msg`, 'style': "padding-left: 0;", 'id':`error_serial_number${item.pos_cart_id}` }));
						}
					}
				description = cellPhoneRow;
			}
	
			let sales_pricestr = addCurrency(item.sales_price);
			if(item.sales_price<0){
				sales_pricestr = '-'+addCurrency(item.sales_price*-1);
			}
			let actionIcon = cTag('td',{ 'align':`center` });
			actionIcon.append(rowremoveicon,'  ');
				let editIcon = cTag('i',{ 'style':`cursor: pointer`,'data-toggle':`tooltip`,'data-original-title':Translate('Edit Cart Info'),'class':`fa fa-edit` });
				if(cartsData.cartPer ===0) editIcon.addEventListener('click',()=>noPermissionWarning('to change Cart Price'));
				else{
					if(item.item_type==='one_time') editIcon.addEventListener('click',()=>AJget_oneTimePopup(item.pos_cart_id));
					else editIcon.addEventListener('click',()=>triggerEvent('changeCart',item.pos_cart_id));
				}
			actionIcon.appendChild(editIcon);
	
			let shippingColumn = '';
			let HaveOnPOCol = '';
			let qtyLabel = item.qty;
			let HaveOnPOStyle = (item.HaveOnPO.need < item.HaveOnPO.have) || (item.HaveOnPO.manage_inventory_count === 0) ? '' : 'background: #f2dede; padding: 2px 4px;';
			HaveOnPOCol = cTag('td',{ 'align':`right` });
			HaveOnPOCol.appendChild(NeedHaveOnPO(item.HaveOnPO,item.item_id, HaveOnPOStyle));
			
			if(frompage!=='POS'){
				if(item.item_type==='one_time'){
					if(frompage==='Repairs' && ['Finished', 'Invoiced', 'Cancelled'].includes(cartsData.status)){
						if(item.shipping_qty===0){
							HaveOnPOCol = cTag('td',{ 'align':`right`, 'style': "color: #F00;" });
							HaveOnPOCol.innerHTML = "Qty did't Receive";
						}
					}
					if(frompage==='Orders'){
						shippingColumn = cTag('td',{ 'align':`right` });
						shippingColumn.innerHTML = item.shipping_qty;
					}
				}
				else if(frompage==='Repairs'){
					if(['Finished', 'Invoiced', 'Cancelled'].includes(cartsData.status)){
						qtyLabel = item.shipping_qty;
					}
				}
				else{
					shippingColumn = cTag('td',{ 'align':`right` });
					shippingColumn.innerHTML = item.shipping_qty;
				}
			}

			if(cartsData.status==='Invoiced' || cartsData.status==='Cancelled') actionIcon = "";

			let tdCol;
			let cartHeadRow = cTag('tr',{ 'class':`cartrow${item.pos_cart_id}` });
				tdCol = cTag('td',{ 'align':`right` });
				tdCol.innerHTML = indx+1;
			cartHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'align':`left` });            
				tdCol.append(description);
			cartHeadRow.appendChild(tdCol);        
			cartHeadRow.append(HaveOnPOCol);
				tdCol = cTag('td',{ 'align':`right` });
				tdCol.innerHTML = qtyLabel;
			cartHeadRow.appendChild(tdCol);
			cartHeadRow.append(shippingColumn);
				tdCol = cTag('td',{ 'align':`right` });
				tdCol.append(sales_pricestr);
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`pos_cart_id[]`,'value':`${item.pos_cart_id}` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_id${item.pos_cart_id}`,'id':`item_id${item.pos_cart_id}`,'value':`${item.item_id}` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`item_type${item.pos_cart_id}`,'id':`item_type${item.pos_cart_id}`,'value':`${item.item_type}` }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`product_type${item.pos_cart_id}`,'id':`product_type${item.pos_cart_id}`,'value':`${item.product_type}` }));
					let textarea = cTag('textarea',{ style:'display:none','name':`add_description${item.pos_cart_id}`,'id':`add_description${item.pos_cart_id}` });
					textarea.innerHTML = item.add_description;
				tdCol.appendChild(textarea);
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`require_serial_no${item.pos_cart_id}`,'id':`require_serial_no${item.pos_cart_id}`,'value':item.require_serial_no }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`sales_price${item.pos_cart_id}`,'id':`sales_price${item.pos_cart_id}`,'value':round(item.sales_price,2)}));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`minimum_price${item.pos_cart_id}`,'id':`minimum_price${item.pos_cart_id}`,'value':round(item.minimum_price,2)}));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`qty${item.pos_cart_id}`,'id':`qty${item.pos_cart_id}`,'value':item.qty }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`shipping_qty${item.pos_cart_id}`,'id':`shipping_qty${item.pos_cart_id}`,'value':item.shipping_qty }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`discount_is_percent${item.pos_cart_id}`,'id':`discount_is_percent${item.pos_cart_id}`,'value':item.discount_is_percent }));
				let discount = round(item.discount,2);
				if(item.discount_is_percent !==1){discount = discount;}
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`discount${item.pos_cart_id}`,'id':`discount${item.pos_cart_id}`,'value':discount }));
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`taxable${item.pos_cart_id}`,'id':`taxable${item.pos_cart_id}`,'value':item.taxable }));
                let overSold = false;
                if(['POS','Orders'].includes(segment1) && item.allow_backorder===0 && item.HaveOnPO.need>item.HaveOnPO.have) overSold = true;
                else if(segment1==='Repairs' && item.allow_backorder===0 && item.HaveOnPO.have<0) overSold = true;
				tdCol.appendChild(cTag('input',{ 'type':`hidden`,'name':`overSold${item.pos_cart_id}`,'id':`overSold${item.pos_cart_id}`,'value':overSold }));
			cartHeadRow.appendChild(tdCol);
				tdCol = cTag('td',{ 'id':`totalstr${item.pos_cart_id}`,'align':`right` });
				tdCol.innerHTML = ' ';
			cartHeadRow.appendChild(tdCol);
			cartHeadRow.append(actionIcon);
			parentNode.appendChild(cartHeadRow);
		});
		parentNode.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
	}
}
export function haveAnyOversoldProduct(completionCBF){
    let oversoldProduct = [];
    document.querySelectorAll('[name="pos_cart_id[]"]').forEach(product=>{
        const id = product.value;
        const name = document.getElementById(`productname${id}`).innerText;
        const oversold = document.getElementById(`overSold${id}`).value==='true';
        if(oversold) oversoldProduct.push(name);
    })
    if(oversoldProduct.length>0){
        const oversoldedInfo = cTag('div',{style:'margin-left:20px;text-align:left'});
        if(oversoldProduct.length===1){
            const productNameElement = cTag('b');
            productNameElement.innerText = oversoldProduct[0];
            oversoldedInfo.append('You added (',productNameElement,') more than you have in your stock')
        }else{
                const title = cTag('h4',{style:'font-weight:bold'});
                title.innerText = Translate('You added these products more than you have in your stock');
            oversoldedInfo.append(title);
                const ul = cTag('ul',{style:'list-style: decimal inside;font-size:small;margin-top:7px'})
                oversoldProduct.forEach(productName=>{
                    const li = cTag('li');
                    li.innerText = productName;
                    ul.append(li);
                })
            oversoldedInfo.append(ul);
        }
        popup_dialog600(Translate('Overselling'), oversoldedInfo, Translate('Continue Overselling'), function(hidePopup){
            hidePopup();
            completionCBF(null,true);
        });
        return true;
    }else{
        return false
    }
}

export async function emaildetails(event, uri){
	if(event){event.preventDefault();}
	let oField = document.getElementById("email_address");
	let email_address = oField.value;
	let pos_id = document.getElementById("pos_id").value;

	actionBtnClick('.sendbtn', Translate('Sending'), 1);

	let amount_due;
	let frompage = segment1;
	amount_due = 0;
	let noteCrOrNot = 1;
	if(['POS', 'Repairs', 'Orders'].includes(frompage)){
		let amountDue = parseFloat(document.getElementById("amount_due").value);
		if(amountDue<0){
			amount_due = amountDue;
		}
	}

	const jsonData = {};
	jsonData['pos_id'] = pos_id;
	jsonData['email_address'] = email_address;
	jsonData['amount_due'] = amount_due;
	jsonData['noteCrOrNot'] = noteCrOrNot;
    const url = uri;

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.returnStr !=='Ok'){
			showTopMessage('alert_msg', Translate('Sorry! Could not send mail. Try again later.'));
		}
		else{
			if(document.querySelectorAll('.emailform')){
				document.querySelectorAll('.emailform').forEach(oneRowObj=>{
					oneRowObj.style.display = 'none';
				});
			}			
			if(uri==='/Orders/AJsend_OrdersEmail' || uri==='/Carts/AJ_sendposmail') triggerEvent('filter');			;
		
		}
		actionBtnClick('.sendbtn', Translate('Email'), 0);
		return false;
	}
	return false;
}

export function cancelemailform(){
	document.querySelectorAll(".emailform").forEach(oneFieldObj=>{
		oneFieldObj.style.display = 'none';
	});
	actionBtnClick('.sendbtn', Translate('Email'), 0);
}

export function emailthispage(){
	document.querySelectorAll(".emailform").forEach(oneFieldObj=>{
		if(oneFieldObj.style.display === 'none'){
			oneFieldObj.style.display = 'block';
		}
	});
	actionBtnClick('.sendbtn', Translate('Email'), 0);
	document.getElementById("email_address").focus();
}

//=========Common Cart============//
export function iOSSquareup(){
	let pos_id = document.getElementById("pos_id").value;
	let sqrup_currency_code = document.getElementById("sqrup_currency_code").value;
	let webcallbackurl = document.getElementById("webcallbackurl").value;
	let accounts_id = document.getElementById("accounts_id").value;
	let user_id = document.getElementById("user_id").value;
	let returnURL = document.getElementById("returnURL").value+pos_id;
	let amount = parseFloat(document.getElementById("amount").value);
	if(amount==='' || isNaN(amount)){amount = 0.00;}
	amount = amount*100;
	const dataParameter = {
			"amount_money": {
				"amount" : amount,
				"currency_code" : sqrup_currency_code
			},
			"callback_url" : "http://"+webcallbackurl+"/Squareup/callbackurls",
			"client_id" : "sq0idp-RWkWlHYGGDqxHdC_amRaiw",
			"version": "1.3",
			"notes": Translate('POS Payment'),
			"options" : {
				"supported_tender_types" : ["CREDIT_CARD","CASH","OTHER","SQUARE_GIFT_CARD","CARD_ON_FILE"]
			},
			"state":returnURL+"|"+pos_id+"|"+amount+"|"+accounts_id+"|"+user_id,
			"auto_return":true,
			"skip_receipt":true
		};
	window.location = "square-commerce-v1://payment/create?data=" + encodeURIComponent(JSON.stringify(dataParameter));
}

export function showOrNotSquareup(){
	let operSys = getMobileOperatingSystem();
	let methodObj =  document.querySelector("#method");
	let methodOpts = methodObj.querySelectorAll('option');
	methodObj.innerHTML = '';
	methodOpts.forEach(function(item){		
		let method = item.value;
		let addMethod = 0;
		if(method ==='Squareup'){
			if((operSys==='Android' || operSys==='iOS') && document.querySelector("#sqrup_currency_code").value !==''){addMethod++;}
		}
		else{addMethod++;}

		if(addMethod>0){
			let methodOptions = cTag('option',{'value':method});
			methodOptions.innerHTML = method;
			methodObj.appendChild(methodOptions);
		}
	});
}

export async function onChangeTaxesId(taxid){
	let taxid2,jsonData;
	const frompage = segment1;
	let pos_id = document.getElementById("pos_id").value;

	let taxes_id = parseInt(document.getElementById("taxes_id"+taxid).value);
	let returnstr = true;
	if(taxes_id===0 || taxes_id===''){
		document.getElementById("taxes_name"+taxid).value = '';
		document.getElementById("taxes_percentage"+taxid).value = 0;
		document.getElementById("tax_inclusive"+taxid).value = 0;
		returnstr = false;
	}
	else{		
		let taxcount = document.getElementsByClassName("taxes_id").length;
		if(taxcount>1){
			if(taxid===1){taxid2 = 2;}
			else{taxid2 = 1;}
			
			let others_taxid = document.getElementById("taxes_id"+taxid2).value;																		
			document.getElementById("errmsg_taxes_id"+taxid).innerHTML = '';
			document.getElementById("errmsg_taxes_id"+taxid2).innerHTML = '';
			if(others_taxid>0 && others_taxid === taxes_id){
				document.getElementById("errmsg_taxes_id"+taxid).innerHTML = Translate('Both Tax1 and Tax2 should not same.');
				document.getElementById("taxes_id"+taxid).value = 0;
				document.getElementById("taxes_name"+taxid).value = '';
				document.getElementById("taxes_percentage"+taxid).value = 0;
				document.getElementById("tax_inclusive"+taxid).value = 0;
				returnstr = false;
			}
		}		
	}
	
	if(!returnstr){
		jsonData = {};
		jsonData['pos_id'] = pos_id;
		jsonData['taxes_name1'] = document.getElementById("taxes_name1").value;
		jsonData['taxes_percentage1'] = document.getElementById("taxes_percentage1").value;
		jsonData['tax_inclusive1'] = document.getElementById("tax_inclusive1").value;
		jsonData['taxes_name2'] = document.getElementById("taxes_name2").value;
		jsonData['taxes_percentage2'] = document.getElementById("taxes_percentage2").value;
		jsonData['tax_inclusive2'] = document.getElementById("tax_inclusive2").value;
		const url = "/Common/updatePosTax/"+frompage;

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			calculateCartTotal();
			triggerEvent('filter');
		}
	}
	else{
		jsonData = {};
		jsonData['taxes_id'] = taxes_id;
		jsonData['pos_id'] = pos_id;
		jsonData['taxid'] = taxid;
		const url = "/Common/getTaxesData/"+frompage;

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			let taxesNameField = "taxes_name"+taxid;
			let taxes_name = data.taxes_name;
			document.getElementById(taxesNameField).value = taxes_name;
			
			let taxesPercField = "taxes_percentage"+taxid;
			let taxes_percentage = round(data.taxes_percentage,3);
			document.getElementById(taxesPercField).value = taxes_percentage;
			
			let taxesIncField = "tax_inclusive"+taxid;
			let tax_inclusive = parseInt(data.tax_inclusive);
			document.getElementById(taxesIncField).value = tax_inclusive;
			
			calculateCartTotal();
			triggerEvent('filter');
		}
	}
}

export async function checkAvailCredit(customers_id, credit_limit){
	const jsonData = {};
    const url = "/Common/calAvailCr/"+customers_id+'/'+credit_limit;
	
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		if(data.message !==''){
			showTopMessage('error_msg', data.message);
		}
		else{
			if((document.getElementById("available_creditrow").style.display === 'none') && data.available_credit!==0){
				document.getElementById("available_creditrow").style.display = '';
			}
			document.getElementById("available_credit" ).value = data.available_credit;
			document.getElementById('availableCreditLb').innerHTML = addCurrency(data.available_credit);
			calculateCartTotal();
		}
	}
}

export function AJautoComplete_cartProduct(){
	let frompage;
	if(document.querySelector("#frompage")) frompage = document.querySelector("#frompage").value;
	const search_sku = document.querySelector('#search_sku');
	if(search_sku){		
		customAutoComplete(search_sku,{		
			search: function () {
				if(document.querySelector("#clickYesNo")) document.querySelector("#clickYesNo").value = 0;
			},
			minLength: 2,
			source: async function (request, response) {
				const jsonData = {'keyword_search':request};
				const url = "/Common/AJautoComplete_cartProduct/"+frompage;
	
				await fetchData(afterFetch,url,jsonData,'JSON',0);

				function afterFetch(data){
					response(data.returnStr);
				}
			},
			select: function( event, info ) {
				document.querySelector("#search_sku").value = info.labelval;
				if(document.querySelector("#clickYesNo")){document.querySelector("#clickYesNo").value = 1;}
				addCartsProduct();
				return false;
			},
			renderItem: function( item ) {
				let qtystr = cTag('b');
				qtystr.innerHTML = '('+item.stockQty+')';
				if(item.label.includes("- IMEI ")){qtystr = '';}
				const li = cTag('li',{ 'class':`ui-menu-item` });
					const div = cTag('div');
					div.innerHTML = item.label;
					div.append(' ',qtystr);
				li.appendChild(div);
				return li;
			}
		});
		search_sku.addEventListener('keydown',function (e) {
			if (e.which === 13) {
				search_sku.hide();
				addCartsProduct();
				return false;
			}
		});	
	}
}

export function AJautoComplete_IMEI(){
	let frompage;
	if(document.querySelector("#frompage")) frompage = document.querySelector("#frompage").value;
	const item_numbers = [...document.querySelectorAll(".item_number")];

	item_numbers.forEach(item_number=>{	
		customAutoComplete(item_number,{
			minLength:2,
			search: function () {
				document.getElementById("temp_pos_cart_id").value = item_number.getAttribute('title');										
			},
			source: async function (request, response) {
				const jsonData = {"pos_cart_id":document.getElementById("temp_pos_cart_id").value, "keyword_search":request};
				const url = "/Common/AJautoComplete_IMEI/"+frompage;

				await fetchData(afterFetch,url,jsonData,'JSON',0);
	
				function afterFetch(data){						
					response(data.returnStr);
				}
			},
			select: function( event, info ) {
				item_number.value = info.label;	
				const pos_cart_id = document.getElementById("temp_pos_cart_id").value;									
				addCartsIMEI(pos_cart_id, info.label, item_number);
				return false;
			}
		})
		item_number.addEventListener('keydown',function (e) {
			if (e.which === 13) {
				item_number.hide();
				const itemNumber = this.value;
				const pos_cart_id = this.getAttribute('title');
				addCartsIMEI(pos_cart_id, itemNumber, this);
				return false;
			}
		});
	})
}

export function autoSearchSerial(){
	document.querySelectorAll('.serial_number').forEach(item=>{
		item.addEventListener('keypress',function (e) {
			if (e.which === 13) {
				const serial_number = this.value;
				const pos_cart_id = this.getAttribute('id').replace('serial', '');
				const oElement = document.getElementById("error_serial_number"+pos_cart_id);
				oElement.innerHTML = '';			
				if(serial_number===''){
					oElement.innerHTML = Translate('Missing Serial Number');
				}
				else if(serial_number.length<2){
					oElement.innerHTML = Translate('Serial/IMEI should be min 2 characters.');
				}
				else if(serial_number !==''){
					addCartsSerial(pos_cart_id, serial_number);				
				}
			}
		});
	});
	
	document.querySelectorAll('.serial_number').forEach(item=>{
		item.addEventListener('keyup',function(){
			const ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+_./&#";		
			const sku = this.value.toUpperCase().replace(' ', '-');
			const IsNumber=true;
			let Char;
			let newsku = '';
			for (let i = 0; i < sku.length && IsNumber === true; i++){ 
				Char = sku.charAt(i); 
				if (ValidChars.indexOf(Char) === -1){}
				else{newsku = newsku+Char;}
			}		
			if(sku.length> newsku.length || this.value !== newsku){this.value = newsku;}
		})
	})	
}

export function checkMethod(){
	const paymentButton = document.querySelector("#buttonPayment");
	if(paymentButton) paymentButton.innerHTML = '';

	if(document.querySelector("#method") && document.querySelector("#method").value==='Squareup'){	 
		const operSys = getMobileOperatingSystem();
		const pos_id = document.querySelector("#pos_id").value;
		const returnURL = document.querySelector("#returnURL").value+pos_id;
		let amount = parseFloat(document.querySelector("#amount").value);
		if(amount==='' || isNaN(amount)){amount = 0.00;}
		amount = amount*100;
		const webcallbackurl = document.querySelector("#webcallbackurl").value;
		const sqrup_currency_code = document.querySelector("#sqrup_currency_code").value;
		const accounts_id = document.querySelector("#accounts_id").value;
		const user_id = document.querySelector("#user_id").value;
		if(operSys==='Android'){
			const a = cTag('a',{ 'class':`btn defaultButton`,'href':'intent:#Intent;action=com.squareup.pos.action.CHARGE;package=com.squareup;S.browser_fallback_url=http://'+webcallbackurl+'/Squareup/fallbackurl;S.com.squareup.pos.WEB_CALLBACK_URI=http://'+webcallbackurl+'/Squareup/callbackurls;S.com.squareup.pos.CLIENT_ID=sq0idp-RWkWlHYGGDqxHdC_amRaiw;S.com.squareup.pos.API_VERSION=v2.0;i.com.squareup.pos.TOTAL_AMOUNT='+amount+';S.com.squareup.pos.CURRENCY_CODE='+sqrup_currency_code+';S.com.squareup.pos.TENDER_TYPES=com.squareup.pos.TENDER_CARD,com.squareup.pos.TENDER_CARD_ON_FILE,com.squareup.pos.TENDER_CASH,com.squareup.pos.TENDER_OTHER;S.com.squareup.pos.REQUEST_METADATA='+returnURL+'|'+pos_id+'|'+amount+'|'+accounts_id+'|'+user_id+';end' });
			a.append(cTag('i',{'class':"fa fa-plus", 'style': "font-size: 20px; font-weight: bold;"}),' ',cTag('i',{'class':"fa fa-money", 'style': "font-size: 20px; font-weight: bold;"}));
			paymentButton.appendChild(a);
		}
		else if(operSys==='iOS'){
			const a = cTag('a',{ 'class':`btn defaultButton`,'href':"javascript:void(0);",'click':iOSSquareup });
			a.append(cTag('i',{'class':"fa fa-plus", 'style': "font-size: 20px; font-weight: bold;"}),' ',cTag('i',{'class':"fa fa-money", 'style': "font-size: 20px; font-weight: bold;"}));
			paymentButton.appendChild(a);
		}
	}
	else{
		if(paymentButton){
			const paymentBtn = cTag('button',{ 'type':`button`,'class':`btn defaultButton`,'id':`btnPayment`,'click':addPOSPayment });
			if(getMobileOperatingSystem() =='unknown'){
                    const span = cTag('span');
                    span.innerHTML = ' '+Translate('Payment');
                paymentBtn.append(cTag('i',{'class':"fa fa-plus"}),span);
			}
			else{
                paymentBtn.append(cTag('i',{'class':"fa fa-plus", 'style': "font-size: 20px; font-weight: bold;"}),' ',cTag('i',{'class':"fa fa-money", 'style': "font-size: 20px; font-weight: bold;"}));
				paymentBtn.style.paddingTop = '5px';
				paymentBtn.style.paddingBottom = '5px';
			}
			paymentButton.appendChild(paymentBtn)
		}
	}
}

export function loadPaymentData(parentNode, paymentData){
    parentNode.innerHTML = '';

    if(paymentData.length>0){
        paymentData.forEach(item=>{
			let td, colSpan = item.colSpan;
            const tr = cTag('tr',{'class':'paymentRow'});
			if(segment1 === 'POS' ) {colSpan = colSpan + 1};
				td = cTag('td',{ 'colspan':colSpan,'align':`right`, 'style': "padding-right: 3px;" });
					const paymentEndRows = cTag('div', {class: "flexEndRow"});
						const paymentEndCols = cTag('div', {class: "columnXS12 columnSM10 columnMD8 columnLG6", 'style': "margin: 0; padding: 0;"});
							const paymentTable = cTag('table', {class: "bgnone table-striped table-condensed cf listing", width:'100%'});
								const paymentTBody = cTag('tbody');
									const paymentTr = cTag('tr');
										const paymentTdDrawer = cTag('td', {'style': "text-align: right; font-weight: bold;", width:'25%', nowrap:``});
										paymentTdDrawer.innerHTML = stripslashes(item.drawer);

										const paymentTdDT = cTag('td', {'style': "text-align: right; font-weight: bold;", nowrap:``});
										paymentTdDT.innerHTML = DBDateToViewDate(item.payment_datetime);

										const paymentTdPM = cTag('td', {'style': "text-align: right; font-weight: bold;", width:'25%', nowrap:``});
										paymentTdPM.innerHTML = item.payment_method+' :';

										const paymentTdPA = cTag('td', {'style': "text-align: right;", width:'20%','id':`grand_totalstr`});
											const bTag = cTag('b');
											bTag.innerHTML = addCurrency(item.payment_amount);
										paymentTdPA.appendChild(bTag);
										paymentTdPA.appendChild(cTag('input',{ 'type':`hidden`,'name':`payment_amount[]`,'value':item.payment_amount }));
										paymentTdPA.appendChild(cTag('input',{ 'type':`hidden`,'name':`payment_method[]`,'value':item.payment_method }));
									paymentTr.append(paymentTdDrawer, paymentTdDT, paymentTdPM, paymentTdPA);
								paymentTBody.appendChild(paymentTr);
							paymentTable.appendChild(paymentTBody);
						paymentEndCols.appendChild(paymentTable);
					paymentEndRows.appendChild(paymentEndCols);
				td.appendChild(paymentEndRows);
            tr.appendChild(td);
                td = cTag('td',{ 'align':`center`,'id':`showRemoveIcon` });
					let trusticon = ' ';
					if(item.trusticon>0){
						trusticon = cTag('i',{ 'class':`fa fa-trash-o cursor`,'click':()=>removePOSPayment(item.pos_payment_id, item.squareYN)})
					}
                td.append(trusticon);
            tr.appendChild(td);
			parentNode.appendChild(tr);
        })
    }
}



//=============Inventory-Transfer=======[
function allITImeiList(product_id){
	document.getElementById('ppproduct_id').value = product_id;
	if(document.getElementById("customer_type_name")){
		if(document.getElementById("customer_type_name").style.display!=='none'){
			document.getElementById("customer_type_name").style.display='none';
		}
	}

	if(document.getElementById("filterrow") && document.getElementById("filterrow").style.display==='none'){
		document.getElementById("filterrow").style.display='block';
	}
	if(document.getElementById("filter_category_name_html")){
		if(document.getElementById("filter_category_name_html").style.display!=='none'){
			document.getElementById("filter_category_name_html").style.display='none';
		}
	}
	if(document.getElementById("filter_name_html")){
		if(document.getElementById("filter_name_html").style.display==='none'){
			document.getElementById("filter_name_html").style.display='block';
		}
	}
	if(document.getElementById("all-category-button")){
		if(document.getElementById("all-category-button").style.display==='none'){
			document.getElementById("all-category-button").style.display='block';
		}
	}
	
	if(document.getElementById("allproductlist")){
		if(document.getElementById("allproductlist").style.display!=='none'){
			document.getElementById("allproductlist").style.display='none';
		}
	}
	if(document.getElementById("allcategorylist")){
		if(document.getElementById("allcategorylist").style.display!=='none'){
			document.getElementById("allcategorylist").style.display='none';
		}
	}

	allITImeiListCount();
}

async function allITImeiListCount(){
	document.getElementById('pagi_index').value = 0;
	
	const product_id = document.getElementById('ppproduct_id').value;
	const filter_name = document.getElementById('filter_name').value;

	const jsonData = {};
	jsonData['product_id'] = product_id;
	jsonData['name'] = filter_name;
	jsonData['returnvalue'] = 'datacount';
	
	const url = '/'+segment1+'/allITImeiList';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		document.getElementById('totalrowscount').value = data.dataCount;	
		showITImeiList();
	}
}

async function showITImeiList(){
	let prevBtn,nextBtn;
	let pagi_index = parseInt(document.getElementById('pagi_index').value);
	if(isNaN(pagi_index) || pagi_index===''){pagi_index = 0;}
	
	let starting_val = parseInt(pagi_index*12);
	if(isNaN(starting_val) || starting_val ===''){starting_val = 0;}
	let ending_val = parseInt(starting_val+12);
	
	let totalrowscount = parseInt(document.getElementById('totalrowscount').value);
	if(isNaN(totalrowscount) || totalrowscount ===''){totalrowscount = 0;}
	
	if(totalrowscount===0){
		document.getElementById("PPfromtodata").innerHTML = '0 - 0 of 0';
	}
	else{
		document.getElementById("PPfromtodata").innerHTML = parseInt(starting_val+1)+' - '+ending_val +' / '+totalrowscount;
	}
				
	if(pagi_index>0){
		prevBtn = document.querySelector(".prevlist button");
		prevBtn.disabled = false;
		prevBtn.setAttribute('data-page',pagi_index-1);
		prevBtn.setAttribute('data-category','sub');		
	}
	else{
		prevBtn = document.querySelector(".prevlist button");
		prevBtn.disabled = true;
		prevBtn.removeAttribute('data-page');
		prevBtn.removeAttribute('data-category');
	}
	if(totalrowscount>ending_val){
		nextBtn = document.querySelector(".nextlist button");
		nextBtn.disabled = false;
		nextBtn.setAttribute('data-page',pagi_index+1);
		nextBtn.setAttribute('data-category','sub');
	}
	else{
		nextBtn = document.querySelector(".nextlist button");
		nextBtn.disabled = true;
		nextBtn.removeAttribute('data-page');
		nextBtn.removeAttribute('data-category');
	}
	
	const product_id = document.getElementById('ppproduct_id').value;
	const filter_name = document.getElementById('filter_name').value;	

	const jsonData = {};
	jsonData['product_id'] = product_id;
	jsonData['name'] = filter_name;
	jsonData['starting_val'] = starting_val;
	jsonData['returnvalue'] = 'data';
	
	const url = '/'+segment1+'/allITImeiList';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let PPID = document.querySelector('#allproductlist');
		PPID.innerHTML = '';
		if(PPID.style.display==='none'){
			PPID.style.display='block';
		}
		let container = cTag('div', {class: "flexStartRow"});
		if(data.tableData.length){
			data.tableData.forEach(item=>{
				let pTag;
				const numberColumn = cTag('div',{ 'class':`columnXS6 columnSM4 columnLG3`, 'style': "margin-bottom: 10px;" });
					const div = cTag('div',{ 'class':`redbg product`, 'style': "height:100%", 'id':item.item_id,'click':()=>addCartsProductPicker(item.item_number),'title':item.product_name });
						pTag = cTag('p',{ 'style': "text-align: left; color: white;" });
						pTag.innerHTML = item.shortproduct_name;
					div.appendChild(pTag);
						let skuPriceContainer = cTag('div',{class:'flexSpaBetRow',style:'color:white'});							
							pTag = cTag('p');
							pTag.innerHTML = item.item_number;
						skuPriceContainer.appendChild(pTag);
							pTag = cTag('p');
							pTag.innerHTML = `${addCurrency(item.regular_price)} (${item.current_inventory})`;
						skuPriceContainer.appendChild(pTag);
					div.appendChild(skuPriceContainer);
				numberColumn.appendChild(div);
				container.appendChild(numberColumn);
			})
		}
		PPID.appendChild(container);
	
		document.querySelectorAll(".product").forEach(item=>{
			item.addEventListener('click', event=>{
				if(event.target.classList.contains('active')){
					event.target.classList.remove('active');
				}
				else{
					event.target.classList.add('active');
				}
			});
		})
	}
}
