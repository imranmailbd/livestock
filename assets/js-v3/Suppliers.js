import {cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, emailcheck, checkPhone, noPermissionWarning, 
	confirm_dialog, setTableRows, setTableHRows, showTopMessage, setOptions, addPaginationRowFlex, 
	checkAndSetSessionData, popup_dialog600, listenToEnterKey, 
	applySanitizer, archiveData, unarchiveData, fetchData,
	addCustomeEventListener, actionBtnClick, serialize, onClickPagination, AJautoComplete, historyTable, 
	activityFieldAttributes, popup_dialog1000
} from './common.js';

if(segment2 === ''){segment2 = 'lists'}

const supplierListsAttributes = [
	{'datatitle':Translate('Name'), 'align':'justify'},
	{'datatitle':Translate('Email'), 'align':'left'},
	{'datatitle':Translate('Contact No'), 'align':'left'}
];
 
const uriStr = segment1+'/view';

//___________suppliers__________
async function lists(){
	let page = parseInt(segment3);
	if(page==='' || isNaN(page)){page = 1;}

	const showTableData = document.getElementById("viewPageInfo");
	showTableData.innerHTML = '';

	hidden_items(showTableData,page);

		const titleRow = cTag('div', {class: "flexSpaBetRow outerListsTable"});
			const titleName = cTag('div', {class: "columnXS6 columnSM6"});
				const headerTitle = cTag('h2', {'style': 'text-align: start;' });
				headerTitle.innerHTML = Translate('Manage Suppliers')+' ';
					const infoIcon = cTag('i', {class: "fa fa-info-circle", 'style': "font-size: 16px;", 'data-toggle': "tooltip", 'data-placement': "bottom", 'data-original-title': Translate('This page displays the list of your suppliers')});
				headerTitle.appendChild(infoIcon);
			titleName.appendChild(headerTitle);
		titleRow.appendChild(titleName);			
		let buttonName = cTag('div', {class: "columnXS12 columnSM6", 'style': "text-align: right;"});
			let supplierButton = cTag('button', {class: "btn createButton"});
				supplierButton.addEventListener('click', function (){addnewsupplierform('Suppliers', 0);});
				supplierButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('Create Supplier'));
			buttonName.appendChild(supplierButton);
		titleRow.appendChild(buttonName);
    showTableData.appendChild(titleRow);

	const parentRow = cTag('div', {class: "flexSpaBetRow"});

		 let callOutDivStyle = "background:#FFF;"
		 if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
		 let suppliersContainer = cTag('div', {class: "columnMD12", 'style': "margin: 0;"});
			  let callOutDiv = cTag('div', {class: "innerContainer", 'style': callOutDivStyle});
					const supplierTitleRow = cTag('div', {class: "flexSpaBetRow outerListsTable"});
						 buttonName = cTag('div', {class: "columnXS12 columnSM12 columnMD3"});
							  
					supplierTitleRow.appendChild(buttonName);
						 const supplierType = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
							  let selectDataType = cTag('select', {class: "form-control", name: "sdata_type", id: "sdata_type", 'change': filter_Suppliers_suppliers});
							 setOptions(selectDataType,{'All':Translate('All Suppliers'),'Archived':Translate('Archived Suppliers')},1,0);
						 supplierType.appendChild(selectDataType);
					supplierTitleRow.appendChild(supplierType);
						 const supplierDropDown = cTag('div', {class: "columnXS6 columnSM4 columnMD3"});
							  let selectSupplier = cTag('select', {class: "form-control", name: "sorting_type", id: "sorting_type", 'change': filter_Suppliers_suppliers});
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
									let inputField = cTag('input', {'keydown':listenToEnterKey(filter_Suppliers_suppliers),'type': "text", 'placeholder':Translate('Search Suppliers'), 'value': "", id: "keyword_search", name: "keyword_search", class: "form-control", 'maxlength': 50});
							  supplierInGroup.appendChild(inputField);
									let searchSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: Translate('Search Suppliers')});
									searchSpan.addEventListener('click', filter_Suppliers_suppliers);
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

	addCustomeEventListener('filter',filter_Suppliers_suppliers);
	addCustomeEventListener('loadTable',loadTableRows_Suppliers_suppliers);
	getSessionData();
	filter_Suppliers_suppliers(true);
}

async function filter_Suppliers_suppliers(){
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
		 setTableRows(data.tableRows, supplierListsAttributes, segment1+'/view');
		 document.querySelector("#totalTableRows").value = data.totalRows;			
		 onClickPagination();
	}
}

async function loadTableRows_Suppliers_suppliers(){
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
		 setTableRows(data.tableRows, supplierListsAttributes, segment1+'/view');
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
  else{frompage2 = 'Suppliers';}
		
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
										input.setAttribute('value',  1);
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

					input = cTag('span', {class: "error_msg", id: "error_supplier"});
			  	form.appendChild(input);
				  	input = cTag('input', {'type': "hidden", name: "frompage", id: "error_supplier", value: frompage});
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
					company_link.setAttribute('href',`/Suppliers/view/${data.suppliers_id}`);
					company_link.innerHTML = company+' ';
					company_link.appendChild(cTag('i',{ 'class':`fa fa-link` }));               

					let suppliername = document.querySelector('#suppliername');
					suppliername.innerHTML = first_name+' '+last_name;
						 const edit = cTag('i',{ 'style':'cursor:pointer','class':`fa fa-edit` });
						 edit.addEventListener('click',()=>dynamicImport('./Suppliers.js','addnewsupplierform',['editpo', data.suppliers_id]))
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
					window.location = '/Suppliers/view/'+data.suppliers_id
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
		 archiveData('/Suppliers/AJ_suppliers_archive','/Suppliers/lists', {"suppliers_id":suppliers_id}, Translate('Supplier'),Translate('Could not found supplier for archive'));
		 hidePopup();
	});				
}

async function unarchiveSupplier(suppliers_id){
  confirm_dialog(Translate('Supplier')+' '+Translate('Unarchive'), Translate('Are you sure you want to unarchive this?'), (hidePopup)=>{       
		 unarchiveData(`/Suppliers/view/${suppliers_id}`, {tablename:'suppliers', tableidvalue:suppliers_id, publishname:'suppliers_publish'});
		 hidePopup();
	});
}
//_________________SView-Part________________________
async function view(){
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
			  let supplierButton = cTag('a', {class: "btn defaultButton", 'href': "/Suppliers/lists", title: Translate('All Suppliers')});
			  supplierButton.append(cTag('i',{'class':'fa fa-list'}),' ',Translate('All Suppliers'));
		 titleRow.appendChild(supplierButton);
	Dashboard.appendChild(titleRow);

		 const supplierContainer = cTag('div', {class: "flexSpaBetRow"});
		 
			  let callOutDivStyle = "margin-top: 0; background: #fff"
			  if(OS!=='unknown') callOutDivStyle += "padding-left: 0; padding-right: 0;"
			  let supplierColumn = cTag('div', {class: "columnMD12", 'style': "margin: 0;"});
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

	addCustomeEventListener('filter',filter_Suppliers_view);
	addCustomeEventListener('loadTable',loadTableRows_Suppliers_view);
	AJ_view_MoreInfo();
}

async function AJ_view_MoreInfo(){
	let suppliers_id = document.getElementById("table_idValue").value;
	const jsonData = {};
  jsonData['suppliers_id'] = suppliers_id;
	const url = '/'+segment1+'/AJ_view_MoreInfo';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		 document.querySelector('#company').innerHTML = data.company;
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
					editInput.addEventListener('click', function(){addnewsupplierform('Suppliers', suppliers_id);});
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
		 filter_Suppliers_view(true);
	}
}

async function filter_Suppliers_view(){
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

async function loadTableRows_Suppliers_view(){
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
					const mergeSupplierForm = cTag('form', {'action': "#", name: "frmMergeSupplier", id: "frmMergeSupplier", 'enctype': "multipart/form-data", 'method': "post", "accept-charset": 'utf-8'});
						 const mergeThisText = cTag('div', {class: "flexSpaBetRow"});
							  const mergeThisTextColumn = cTag('div', {class: "columnSM12", 'align': "left"});
									const mergeThisTextTitle = cTag('h4', {class:'borderbottom'});
									mergeThisTextTitle.innerHTML = Translate('Merge this supplier information');
							  mergeThisTextColumn.appendChild(mergeThisTextTitle);
						 mergeThisText.appendChild(mergeThisTextColumn);
					mergeSupplierForm.appendChild(mergeThisText);

						 const mergeSupplierRow = cTag('div', {class: "flexSpaBetRow"});
							  const mergeSupplierColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;"});
									pTag = cTag('p');
									pTag.innerHTML = Translate('Name');
										 let nameSpan = cTag('span');
										 nameSpan.innerHTML = data.first_name+' '+data.last_name;
									pTag.append(': ', nameSpan);
							  mergeSupplierColumn.appendChild(pTag);

									pTag = cTag('p');
									pTag.innerHTML = Translate('Phone No.');
										 let phoneSpan = cTag('span');
										 phoneSpan.innerHTML = data.contact_no;
									pTag.append(': ', phoneSpan);
							  mergeSupplierColumn.appendChild(pTag);

									pTag = cTag('p');
									pTag.innerHTML = Translate('Email');
										 let emailSpan = cTag('span');
										 emailSpan.innerHTML = data.email;
									pTag.append(': ', emailSpan);
							  mergeSupplierColumn.appendChild(pTag);

									pTag = cTag('p');
									pTag.innerHTML = Translate('Company');
										 let companySpan = cTag('span');
										 companySpan.innerHTML = data.company;
									pTag.append(': ', companySpan);
							  mergeSupplierColumn.appendChild(pTag);
						 mergeSupplierRow.appendChild(mergeSupplierColumn);
					mergeSupplierForm.appendChild(mergeSupplierRow);

						 const toThisRow = cTag('div', {class: "flexSpaBetRow"});
							  const toThisColumn = cTag('div', {class: "columnSM12", 'align': "left"});
									const toThisTitle = cTag('h4', {class:'borderbottom'});
									toThisTitle.innerHTML = Translate('To this supplier');
							  toThisColumn.appendChild(toThisTitle);
						 toThisRow.appendChild(toThisColumn);
					mergeSupplierForm.appendChild(toThisRow);

						 const supplierNameRow = cTag('div', {class: "flexSpaBetRow"});
							  const supplierNameColumn = cTag('div', {class: "columnSM2", 'align': "left"});
									const nameLabel = cTag('label', {'for': "supplier"});
									nameLabel.innerHTML = Translate('Name');
										 let requiredField = cTag('span', {class: "required"});
										 requiredField.innerHTML = '*';
									nameLabel.appendChild(requiredField);
							  supplierNameColumn.appendChild(nameLabel);
						 supplierNameRow.appendChild(supplierNameColumn);

							  const nameSearchColumn = cTag('div', {class: "columnSM10"});
									inputField = cTag('input', {"maxlength": 50, 'type': "text", 'value': "", 'required': true, name: "supplier", id: "supplier", class: "form-control", 'placeholder': Translate('Search Suppliers')});
							  nameSearchColumn.appendChild(inputField);
						 supplierNameRow.appendChild(nameSearchColumn);
					mergeSupplierForm.appendChild(supplierNameRow);

						 const supplierInfoRow = cTag('div', {class: "flexSpaBetRow"});
							  const supplierInfoColumn = cTag('div', {class: "columnSM12 image_content", 'style': "text-align: left;",  id: "toSupplierInfo"});
						 supplierInfoRow.appendChild(supplierInfoColumn);
					mergeSupplierForm.appendChild(supplierInfoRow);

						 inputField = cTag('input', {'type': "hidden", name: "fromsuppliers_id", id: "fromsuppliers_id", 'value': suppliers_id});
					mergeSupplierForm.appendChild(inputField);
						 inputField = cTag('input', {'type': "hidden", name: "tosuppliers_id", id: "tosuppliers_id", 'value': 0});
					mergeSupplierForm.appendChild(inputField);
			  formDialog.appendChild(mergeSupplierForm);

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
		  window.location = `/${segment1}/view/${data.id}`;
	  }
	  else{
			  actionBtnClick('.btnmodel', Translate('Merge Suppliers'), 0);
			  showTopMessage('alert_msg', Translate('There is an error while merging information.'));             
	  }
  }
  return false;
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

document.addEventListener('DOMContentLoaded', async()=>{
	let layoutFunctions = {lists, view};
	layoutFunctions[segment2]();

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});
