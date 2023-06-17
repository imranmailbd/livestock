import {
	cTag, Translate, checkAndSetLimit, tooltip, storeSessionData, addCurrency, number_format, DBDateToViewDate, ViewDateToDBDate, 
	ViewDateRangeToDBDate, DBDateRangeToViewDate, getCookie, printbyurl, showTopMessage, setOptions, addPaginationRowFlex, 
	btnEnableDisable, popup_dialog600, date_picker, daterange_picker_dialog, dynamicImport, sanitizer, noPermissionWarning,
	fetchData, addCustomeEventListener, serialize, onClickPagination, multiSelectAction, AJremove_tableRow, controllNumericField
} from './common.js';

if(segment2==='') segment2 = 'view';

let printingDate;
if(segment3.length >= 10){
	let [yy,mm,dd] = segment3.split('-')
	printingDate = `${yy}-${mm}-${dd}`;
	
}
else{
	let d = new Date();
	let dd = d.getDate();
	if(dd<10) dd = '0'+dd;
	let mm = d.getMonth()+1;
	if(mm<10) mm = '0'+mm;
	let yy = d.getFullYear();
	printingDate = `${yy}-${mm}-${dd}`;
}
let date = DBDateToViewDate(printingDate);

function view(){
	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const endOfDayForm = cTag('form',{ "action":"#","name":"frmend_of_day","id":"frmend_of_day","method":"post","enctype":"multipart/form-data" });
        endOfDayForm.addEventListener('submit',event=>event.preventDefault());
		endOfDayForm.appendChild(cTag('input',{type:'submit',style:'visibility:hidden; position:fixed;'}));//for making not to react on enter key 
			const endOfDayViewRow = cTag('div',{ "class":"flexSpaBetRow", 'style': "padding-left: 5px;" });
				const titleRow = cTag('div',{ "class": "columnXS12 columnMD5", 'style': "text-align: left;" });
					const headerTitle = cTag('h2');
					headerTitle.append(Translate('End of Day Report')+' ');
					headerTitle.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('Details the payments received FROM various payment methods as well as change provided. Often used to balance a cash register at the end of a business day.') }));
				titleRow.appendChild(headerTitle);
			endOfDayViewRow.appendChild(titleRow);
				const btnContainer = cTag('div',{'class':'flexSpaBetRow columnXS12 columnMD7', 'style': "margin: 0;"})
					let cashDrawerRow = cTag('div',{'class':'flex columnSM6', 'style': "text-align: left;"});
					cashDrawerRow.append(
						cTag('input',{ "class": "form-control", 'style': "background: #f05523; color: #FFF; border-color: #f05523; width: 120px; text-align: center;", "readonly":"","type":"text","name":"eod_date","id":"eod_date","value":date }),
						cTag('div',{ 'style': "width: 150px; margin-left: 10px;", "id":"cashDrawerContainer" }),
						cTag('span',{'id':'errorDrawer', class:"errormsg"})
					)
				btnContainer.appendChild(cashDrawerRow);

					const endOfDayColumn = cTag('div',{ "class": "flexEndRow columnSM6"});
						const aTag = cTag('a', {'href': "/End_of_Day/lists", class: "btn defaultButton", 'style': "margin-right: 15px; padding-top: 5px; padding-bottom: 5px;", title: Translate('End of Day List')});
						aTag.append(cTag('i', {class: "fa fa-list"}), ' ', Translate('End of Day List'));
					endOfDayColumn.appendChild(aTag);

						let printBtnDropDown = cTag('div',{ "class":"printBtnDropDown", id: 'EOPdropdown' });
							const printButton = cTag('button',{ "type":"button", "class":"btn printButton dropdown-toggle", "data-toggle":"dropdown","aria-haspopup":"true","aria-expanded":"false" });
							printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
							if(OS =='unknown'){
								printButton.append(' '+Translate('Print')+' ');
							}
							printButton.append('\u2000', cTag('span',{ "class":"caret"}));
								let toggleSpan = cTag('span',{ "class":"sr-only" });
								toggleSpan.innerHTML = Translate('Toggle Dropdown');
							printButton.appendChild(toggleSpan);
						printBtnDropDown.appendChild(printButton);
							const ulDropDown = cTag('ul',{ "class":"dropdown-menu"});
								let liFullPrint = cTag('li');
									let fullPrintLink = cTag('a',{ "href":"javascript:void(0);","click":()=>printbyurl(`/End_of_Day/prints/large/${printingDate}/${sessionStorage.getItem('eod_drawer')||''}`),"title":Translate('Full Page Printer') });
									fullPrintLink.innerHTML = Translate('Full Page Printer');
								liFullPrint.appendChild(fullPrintLink);
							ulDropDown.appendChild(liFullPrint);
							ulDropDown.appendChild(cTag('li',{ "role":"separator","class":"divider" }));
								let liThermalPrint = cTag('li');
									let thermalPrintLink = cTag('a',{ "href":"javascript:void(0);","click":()=>printbyurl(`/End_of_Day/prints/small/${printingDate}/${sessionStorage.getItem('eod_drawer')||''}`),"title":Translate('Thermal Printer') });
									thermalPrintLink.innerHTML = Translate('Thermal Printer');
								liThermalPrint.appendChild(thermalPrintLink);
							ulDropDown.appendChild(liThermalPrint);
						printBtnDropDown.appendChild(ulDropDown);
					endOfDayColumn.appendChild(printBtnDropDown);
				btnContainer.appendChild(endOfDayColumn);
			endOfDayViewRow.appendChild(btnContainer);
		endOfDayForm.appendChild(endOfDayViewRow);

			let endDayLoadColumn = cTag('div',{ "class":"columnSM12", "id":"loadData_EOD" });
		endOfDayForm.appendChild(endDayLoadColumn);
    Dashboard.appendChild(endOfDayForm);
    Dashboard.appendChild(cTag('input',{ "type":"hidden","id":"showEODPOSMessage" }));
    Dashboard.appendChild(cTag('input',{ "type":"hidden","id":"salesman_name" }));

		const paymentInfoTitleColumn = cTag('div');
			const paymentInfoHeader = cTag('h2',{ 'style': "text-align: start; padding: 10px;" });
			paymentInfoHeader.innerHTML = Translate('Payment Information');
		paymentInfoTitleColumn.appendChild(paymentInfoHeader);
    Dashboard.appendChild(paymentInfoTitleColumn);

		const paymentInfoLoadColumn = cTag('div',{ "class":"columnSM12" });
			const paymentForm = cTag('form',{ "action":"#","name":"frmpayment","method":"post","enctype":"multipart/form-data" });
				let paymentInfoDiv = cTag('div',{ "id":"load_payment_information" });
			paymentForm.appendChild(paymentInfoDiv);
		paymentInfoLoadColumn.appendChild(paymentForm);
    Dashboard.appendChild(paymentInfoLoadColumn);

        const pettyCashRow = cTag('div',{ "class":"flexSpaBetRow" });
        pettyCashRow.appendChild(cTag('div',{ "class":"columnSM12","id":"load_petty_cash" }));
    Dashboard.appendChild(pettyCashRow);
	multiSelectAction('EOPdropdown');
	AJ_view_MoreInfo();
}

async function AJ_view_MoreInfo(){
	const eod_date = ViewDateToDBDate(document.querySelector("#eod_date").value);
	let eod_drawer = sessionStorage.getItem('eod_drawer');
    if(!eod_drawer){
        eod_drawer = getCookie('drawer');
        sessionStorage.setItem('eod_drawer',eod_drawer);
    }

	const jsonData = {"eod_date":eod_date,'eod_drawer':eod_drawer};
	const url = '/'+segment1+'/AJ_view_MoreInfo';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		const cashDrawerContainer = document.querySelector('#cashDrawerContainer');
		cashDrawerContainer.innerHTML = '';
		if(data.multiple_cash_drawers>0 && data.cashDrawersOptions.length){
				const select = cTag('select',{"class":"form-control","name":"drawer","id":"drawer" });
				select.addEventListener('change',function(){
					sessionStorage.setItem('eod_drawer',this.value)
					location.reload();
				})
				data.cashDrawersOptions.forEach(optData=>{
					let option = cTag('option',{ 'value': optData });
					option.innerHTML = stripslashes(optData);
					select.appendChild(option);
				});
				if(data.cashDrawersOptions.filter(item=>item ===eod_drawer)){
					select.value = eod_drawer;
				}
			cashDrawerContainer.appendChild(select);
		}
		else{
			cashDrawerContainer.appendChild(cTag('input',{ "type":"hidden","name":"drawer","id":"drawer","value":"" }));
		}
		
		cashDrawerContainer.appendChild(cTag('input',{ "type":"hidden","name":"multiple_cash_drawers","id":"multiple_cash_drawers" }));
		document.querySelector('#multiple_cash_drawers').value = data.multiple_cash_drawers;
		document.querySelector('#salesman_name').value = data.salesman_name;
		loadEODdata(data.loadData_EOD);
		loadPaymentInfo(data.loadData_payment);
		loadPettyCashData(data.loadData_petty_cash);
		calculateCash();

		if (document.querySelector('#eod_date')){
			date_picker('#eod_date',(date,month,year)=>{
				window.location = `/End_of_Day/view/${year}-${month}-${date}`; 
			})
		}
	}
}

function loadEODdata(data){
	let tdCol, inputField, cashCounterButton, thCol, tableHeadRow;
	const loadDataEOD = document.querySelector('#loadData_EOD');
	loadDataEOD.innerHTML = '';
		const loadDataEODColumn = cTag('div',{ "class":"columnXS12" });
			const EODtable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing" });
				const EODbody = cTag('tbody');
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.innerHTML = Translate('Cash Counted')+' : ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "data-title":Translate('Counted'),"align":"right" });
							inputField = cTag('input',{ 'type': "text",'data-min':'0','data-max':'9999999.99','data-format':'d.dd',"name":"cash_counted","id":"cash_counted","value":"0","class":"form-control", 'style': "text-align: right;", "keyup":calculateCash,"value":data.counted_cash.toFixed(2) });
							controllNumericField(inputField, '#error_cash_counted');
						tdCol.appendChild(inputField);
						tdCol.append(cTag('span', {class: "error_msg", 'style': 'padding-right: 12px;', id: 'error_cash_counted'}));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "data-title":Translate('Difference'),"align":"left" });
							if(OS =='unknown'){
								cashCounterButton = cTag('button',{ "class":"btn defaultButton","click":()=>cashDrawerCounter('cash_counted') });
							}
							else{
								cashCounterButton = cTag('a',{ 'style': "font-weight: bold; color: #000;", "click":()=>cashDrawerCounter('cash_counted') });
							}
							cashCounterButton.innerHTML = Translate('Cash Drawer Counter');
						tdCol.appendChild(cashCounterButton);
					tableHeadRow.appendChild(tdCol);
				EODbody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.innerHTML = Translate('Starting Balance')+' : ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'style': "text-align: end;" });
							inputField = cTag('input',{ 'type': "text",'data-min':'0','data-max':'9999999.99','data-format':'d.dd',"name":"starting_cash","id":"starting_cash","class":"form-control", 'style': "text-align: right;", "value":data.starting_cash.toFixed(2),"keyup":calculateCash });
							controllNumericField(inputField, '#error_starting_cash');
						tdCol.appendChild(inputField);
						tdCol.append(cTag('span', {class: "error_msg", 'style': 'padding-right: 12px;', id: 'error_starting_cash'}));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "data-title":Translate('Counted'),"align":"left" });
						if(OS =='unknown'){
							cashCounterButton = cTag('button',{ "class":"btn defaultButton","click":()=>cashDrawerCounter('starting_cash') });
						}
						else{
							cashCounterButton = cTag('a',{ 'style': "font-weight: bold; color: #000;", "click":()=>cashDrawerCounter('starting_cash') });
						}
							cashCounterButton.innerHTML = Translate('Cash Drawer Counter');
						tdCol.appendChild(cashCounterButton);
					tableHeadRow.appendChild(tdCol);
				EODbody.appendChild(tableHeadRow);
				if(data.petty_cash !==0){
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.innerHTML = Translate('Petty Cash')+' : ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'style': "text-align: end; padding-right: 18px;" });
						tdCol.innerHTML = addCurrency(data.petty_cash);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "data-title":Translate('Counted'),"align":"right" });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				}
				EODbody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.innerHTML = Translate('Calculated Cash')+' : ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ 'style': "text-align: end;" });
						tdCol.appendChild(cTag('input',{ "type":"hidden","name":"petty_cash","id":"petty_cash","value":data.petty_cash }));
						tdCol.appendChild(cTag('input',{ "type":"hidden","name":"calculatedCash","id":"calculatedCash","value":data.calculatedCash }));
						tdCol.append((addCurrency(data.calculatedCash)));
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "data-title":Translate('Counted'),"align":"right","id":"countedCashStr", 'style': "padding-right: 17px;" });
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "data-title":Translate('Difference'),"align":"right" });
						tdCol.appendChild(cTag('input',{ "type":"hidden","name":"countedCash","id":"countedCash","value":"0" }));
						tdCol.appendChild(cTag('input',{ "type":"text","readonly":"","name":"cash_difference","id":"cash_difference","value":"0","class":"form-control", 'style': "text-align: end;" }));
					tableHeadRow.appendChild(tdCol);
				EODbody.appendChild(tableHeadRow);
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ "align":"right", "colspan":"4", "style":"border: 0px solid #f5f5f5;" });
						tdCol.innerHTML = ' ';
					tableHeadRow.appendChild(tdCol);
				EODbody.appendChild(tableHeadRow);
				if(data.paymentData.length){
					tableHeadRow = cTag('tr');
						thCol = cTag('th',{ "style":"background-color: #e0dfdf;","align":"left" });
						thCol.innerHTML = Translate('Payment Type');
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ "style":"background-color: #e0dfdf; text-align: end;", "width":"20%" });
						thCol.innerHTML = Translate('Calculated');
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ "style":"background-color: #e0dfdf; text-align: end;", "width":"20%" });
						thCol.innerHTML = Translate('Counted');
					tableHeadRow.appendChild(thCol);
						thCol = cTag('th',{ "style":"background-color: #e0dfdf; text-align: end;","width":"20%" });
						thCol.innerHTML = Translate('Difference');
					tableHeadRow.appendChild(thCol);
					EODbody.appendChild(tableHeadRow);
					data.paymentData.forEach(item=>{
						let fieldType = item[0]=='Change'? 'hidden':'text';
						tableHeadRow = cTag('tr');
							tdCol = cTag('td',{ "data-title":Translate('Payment Type'),"align":"left" });
							tdCol.append(item[0]);
							tdCol.appendChild(cTag('input',{ "type":"hidden","name":"payment_method[]","id":"payment_method[]","value":item[0] }));
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Calculated'),"align":"right" });
							tdCol.append(addCurrency(item[1]));
							tdCol.appendChild(cTag('input',{ "type":"hidden","name":"calculated[]","id":"calculated[]","value":item[1] }));
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Counted'),"align":"right" });
								inputField = cTag('input',{ "type":'text', 'data-min':'0','data-max':'9999999.99', 'data-format':'d.dd', "name":"counted[]","id":"counted[]","class":"form-control cardCounted", 'style': "text-align: end;", "value":item[2],"keyup":calculateEODTotal });
								controllNumericField(inputField, '#error_counted');
							tdCol.appendChild(inputField);
							tdCol.append(cTag('span', {class: "error_msg", 'style': 'padding-right: 12px;', id: 'error_counted'}));
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Difference'),"align":"right" });
							tdCol.appendChild(cTag('input',{ "type":fieldType,"readonly":"true","name":"difference[]","id":"difference[]","value":item[2]-item[1],"class":"form-control", 'style': "text-align: end;", }));
						tableHeadRow.appendChild(tdCol);
						EODbody.appendChild(tableHeadRow);
					})							
				}
					tableHeadRow = cTag('tr');
						tdCol = cTag('td',{ "class":"bgtitle", 'style': "text-align: end;"});
						tdCol.innerHTML = Translate('Total')+' : ';
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "class":"bgtitle", 'style': "text-align: end;"});
							let totalSpan = cTag('span',{ "id":"total_calculatedtxt" });
						tdCol.appendChild(totalSpan);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "class":"bgtitle", 'style': "text-align: end;"});
							let countSpan = cTag('span',{ "id":"total_countedtxt", 'style': "padding-right: 13px;" });
						tdCol.appendChild(countSpan);
					tableHeadRow.appendChild(tdCol);
						tdCol = cTag('td',{ "class":"bgtitle", 'style': "text-align: end;"});
							let differentSpan = cTag('span',{ "id":"total_differencetxt", 'style': "padding-right: 10px;" });
						tdCol.appendChild(differentSpan);
					tableHeadRow.appendChild(tdCol);
				EODbody.appendChild(tableHeadRow);
			EODtable.appendChild(EODbody);
		loadDataEODColumn.appendChild(EODtable);
	loadDataEOD.appendChild(loadDataEODColumn);

		const commentRow = cTag('div',{ "class":"flex" });
			const commentColumn = cTag('div',{ "class":"columnSM2","align":"center" });
				const commentLabel = cTag('label');
				commentLabel.innerHTML = Translate('Comments')+' :';
			commentColumn.appendChild(commentLabel);
		commentRow.appendChild(commentColumn);
			const commentField = cTag('div',{ "class":"columnSM10"});
				const textarea = cTag('textarea',{ "class":"form-control","name":"comments","id":"comments","cols":"40","rows":"2" });
				textarea.innerHTML = data.comments;
				textarea.addEventListener('blur',sanitizer);
			commentField.appendChild(textarea);
		commentRow.appendChild(commentField);
	loadDataEOD.appendChild(commentRow);
		const submitButton = cTag('div',{ "class":"columnSM12","align":"center" });
		submitButton.appendChild(cTag('input',{ "type":"hidden","name":"end_of_day_id","id":"end_of_day_id","value":data.end_of_day_id }));
			const submit = cTag('input',{ "type":"submit","name":"submit","id":"submit","class":"btn completeButton","value":data.end_of_day_id>0?Translate('Update'):Translate('Save') });
			submit.addEventListener('click',check_frmend_of_day);
		submitButton.appendChild(submit);
	loadDataEOD.appendChild(submitButton);
}

function loadPaymentInfo(data){
	let strong, tdCol, paymentHeadRows;
	const loadDataPayment = document.querySelector('#load_payment_information');
	loadDataPayment.innerHTML = '';
		const paymentTableColumn = cTag('div',{ "class":"columnXS12" });
			const noMoreTables = cTag('div',{ "id":"no-more-tables" });
				const paymentTable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing " });
					const paymentHead = cTag('thead',{ "class":"cf" });
						paymentHeadRows = cTag('tr');
							const thCol0 = cTag('th',{ "style": "text-align: left;", "width":"15%" });
							thCol0.innerHTML = Translate('User');

							const thCol1 = cTag('th',{ "width":"80px" });
							thCol1.innerHTML = Translate('Time');

							const thCol2 = cTag('th',{ "width":"12%" });
							thCol2.innerHTML = Translate('Invoice No.');

							const thCol3 = cTag('th');
							thCol3.innerHTML = Translate('Customer Name');

							const thCol4 = cTag('th',{ "style": "text-align: left;", "width":"15%" });
							thCol4.innerHTML = Translate('Payment Type');

							const thCol5 = cTag('th',{ "width":"15%" });
							thCol5.innerHTML = Translate('Amount');
						paymentHeadRows.append(thCol0, thCol1, thCol2, thCol3, thCol4, thCol5);
					paymentHead.appendChild(paymentHeadRows);
				paymentTable.appendChild(paymentHead);
					const paymentBody = cTag('tbody');
					if(data.paymentData.length){
						data.paymentData.forEach(item=>{
							paymentHeadRows = cTag('tr');
								tdCol = cTag('td',{ "data-title":Translate('User'),"align":"left" });
								tdCol.innerHTML = item[1];
							paymentHeadRows.appendChild(tdCol);
								tdCol = cTag('td',{ "data-title":Translate('Time'),"align":"right" });
								tdCol.innerHTML = DBDateToViewDate(item[2], 1)[1];
							paymentHeadRows.appendChild(tdCol);
								tdCol = cTag('td',{ "data-title":Translate('Invoice No.'),"align":"right" });
									const editLink = cTag('a',{ "href":item[3], 'style': "color: #009; text-decoration: underline;", "title":Translate('Edit') });
									editLink.append(item[4]+' ');
									editLink.appendChild(cTag('i',{ "class":"fa fa-link" }));
								tdCol.appendChild(editLink);
							paymentHeadRows.appendChild(tdCol);
								tdCol = cTag('td',{ "data-title":Translate('Customer Name'),"align":"left" });
								tdCol.innerHTML = item[5];
							paymentHeadRows.appendChild(tdCol);
								tdCol = cTag('td',{ "data-title":Translate('Payment Type'),"align":"left" });
									const selectPayment = cTag('select',{ "name":"paymentmethod[]","id":`paymentmethod${item[0]}`,"class":"form-control","change":function(){AJ_update(item[0], this.value)}});
									setOptions(selectPayment,item[7],0,0);
									selectPayment.value = item[6];
								tdCol.appendChild(selectPayment);
							paymentHeadRows.appendChild(tdCol);
								tdCol = cTag('td',{ "data-title":Translate('Amount'),"align":"right" });
								tdCol.innerHTML = addCurrency(item[8]);
							paymentHeadRows.appendChild(tdCol);
						paymentBody.appendChild(paymentHeadRows);
						})
					}
					else{
							paymentHeadRows = cTag('tr');
								tdCol = cTag('td',{ "colspan":"6"});
								tdCol.innerHTML = '';
							paymentHeadRows.appendChild(tdCol);
						paymentBody.appendChild(paymentHeadRows);
					}						
						paymentHeadRows = cTag('tr');
							tdCol = cTag('td',{ "align":"right","colspan":"5" });
								strong = cTag('strong');
								strong.innerHTML = Translate('Total')+' : ';
							tdCol.appendChild(strong);
						paymentHeadRows.appendChild(tdCol);
							tdCol = cTag('td',{ 'style': "text-align: right;" });
								strong = cTag('strong');
								strong.innerHTML = addCurrency(data.total_payment);
							tdCol.appendChild(strong);
						paymentHeadRows.appendChild(tdCol);
					paymentBody.appendChild(paymentHeadRows);
				paymentTable.appendChild(paymentBody);
			noMoreTables.appendChild(paymentTable);
		paymentTableColumn.appendChild(noMoreTables);
	loadDataPayment.appendChild(paymentTableColumn)
}

function loadPettyCashData(data){
	let strong, pattyCashHeadRow, tdCol;
	const loadDataPattyCash = document.querySelector('#load_petty_cash');
	loadDataPattyCash.innerHTML = '';
	if(data.petty_cashData.length){
			const titleRow = cTag('div',{ "class":"flexSpaBetRow" });
				const titleColumn = cTag('div',{ "class":"columnXS12" });
					const titleHeader = cTag('h2',{ 'style': "padding-top: 5px; text-align: start;" });
					titleHeader.innerHTML = Translate('Petty Cash Information');
				titleColumn.appendChild(titleHeader);
			titleRow.appendChild(titleColumn);
		loadDataPattyCash.appendChild(titleRow);
			const pattyCashRow = cTag('div',{ "class":"flexSpaBetRow" });
				const pattyCashColumn = cTag('div',{ "class":"columnXS12" });
					const noMoreTables = cTag('div');
						const pattyCashTable = cTag('table',{ "class":" columnMD12 table-bordered table-striped table-condensed cf listing " });
							const pattyCashHead = cTag('thead',{ "class":"cf" });
								pattyCashHeadRow = cTag('tr');
									const thCol0 = cTag('th',{ "style": "text-align: left;" });
									thCol0.innerHTML = Translate('Reason');
									const thCol1 = cTag('th',{ "style": "text-align: right;", "width":"15%" });
									thCol1.innerHTML = Translate('Add/Sub');
									const thCol2 = cTag('th',{ "style": "text-align: right;", "width":"25%" });
									thCol2.innerHTML = Translate('Amount');
									const thCol3 = cTag('th',{ 'style': "text-align: center;", "width":"100px" });
									thCol3.innerHTML = Translate('Action');
								pattyCashHeadRow.append(thCol0, thCol1, thCol2, thCol3);
							pattyCashHead.appendChild(pattyCashHeadRow);
						pattyCashTable.appendChild(pattyCashHead);
							const pattyCashBody = cTag('tbody');

							data.petty_cashData.forEach(item=>{
									pattyCashHeadRow = cTag('tr');
										tdCol = cTag('td',{ "data-title":Translate('Reason'),"align":"left" });
										tdCol.innerHTML = item[1];
									pattyCashHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ "data-title":Translate('Add/Sub'),"align":"right" });
										tdCol.innerHTML = item[2];
									pattyCashHeadRow.appendChild(tdCol);
										tdCol = cTag('td',{ "data-title":Translate('Amount'),"align":"right" });
										tdCol.innerHTML = addCurrency(item[3]);
									pattyCashHeadRow.appendChild(tdCol);

									tdCol = cTag('td',{ "data-title":Translate('Action'),"align":"center" });
									tdCol.append(cTag('i',{ 'class':`fa fa-edit`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'click':()=>dynamicImport('./POS.js','AJget_pettyCashPopup',[item[0]]),'data-original-title':Translate('Change Petty Cash') }),
										'  ', cTag('i',{ 'class':`fa fa-trash-o`,'style':`cursor: pointer`,'data-toggle':`tooltip`,'click':()=>AJremove_tableRow('petty_cash', item[0], 'Petty Cash', '', AJ_view_MoreInfo),'data-original-title':Translate('Remove Petty Cash') }))
									pattyCashHeadRow.appendChild(tdCol);
								pattyCashBody.appendChild(pattyCashHeadRow);
							})
								pattyCashHeadRow = cTag('tr');
									tdCol = cTag('td',{ "align":"right","colspan":"2" });
										strong = cTag('strong');
										strong.innerHTML = Translate('Total')+' : ';
									tdCol.appendChild(strong);
								pattyCashHeadRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'style': "text-align: right;" });
										strong = cTag('strong');
										strong.innerHTML = addCurrency(data.total_petty_cash);
									tdCol.appendChild(strong);
								pattyCashHeadRow.appendChild(tdCol);
									tdCol = cTag('td',{ 'style': "text-align: right;" });
									tdCol.innerHTML = ' ';
								pattyCashHeadRow.appendChild(tdCol);
							pattyCashBody.appendChild(pattyCashHeadRow);
						pattyCashTable.appendChild(pattyCashBody);
					noMoreTables.appendChild(pattyCashTable);
				pattyCashColumn.appendChild(noMoreTables);
			pattyCashRow.appendChild(pattyCashColumn);
		loadDataPattyCash.appendChild(pattyCashRow);
	}
}

function calculateCash(){
	let calculatedCash = parseFloat(document.getElementById("calculatedCash").value);
	if(calculatedCash==='' || isNaN(calculatedCash)){calculatedCash = 0;}	

	let cash_counted = parseFloat(document.getElementById("cash_counted").value);
	if(cash_counted==='' || isNaN(cash_counted)){cash_counted = 0;}	

	let starting_cash = parseFloat(document.getElementById("starting_cash").value);
	if(starting_cash==='' || isNaN(starting_cash)){starting_cash = 0;}	

	let petty_cash = parseFloat(document.getElementById("petty_cash").value);
	if(petty_cash==='' || isNaN(petty_cash)){petty_cash = 0;}	

	const countedCash = parseFloat(cash_counted-starting_cash-petty_cash);

	document.getElementById("countedCashStr").innerHTML = addCurrency(countedCash);
	document.getElementById("countedCash").value = countedCash;	

	const cash_difference = parseFloat(countedCash-calculatedCash).toFixed(2);	

	document.getElementById("cash_difference").value = cash_difference;
	calculateEODTotal();	

}

function calculateEODTotal(){	
	let totalcalculated = parseFloat(document.getElementById('calculatedCash').value);
	if(totalcalculated==='' || isNaN(totalcalculated)){totalcalculated = 0;}

	let totalcounted = parseFloat(document.getElementById("countedCash").value);
	if(totalcounted==='' || isNaN(totalcounted)){totalcounted = 0;}

	if(document.getElementsByClassName("cardCounted").length>0){
		const calculatedarray = document.getElementsByName("calculated[]");
		const countedarray = document.getElementsByName("counted[]");
		const differencearray = document.getElementsByName("difference[]");		

		for(let i=0; i<calculatedarray.length;i++){			

			let calculated = parseFloat(calculatedarray[i].value);
			if(calculated==='' || isNaN(calculated)){calculated = 0;}			

			let counted = parseFloat(countedarray[i].value);
			if(counted==='' || isNaN(counted)){counted = 0;}			

			const difference = parseFloat(counted-calculated);
			differencearray[i].value = difference.toFixed(2);			

			totalcalculated += calculated;
			totalcounted += counted;

		}
	}		

	document.getElementById("total_calculatedtxt").innerHTML = addCurrency(totalcalculated);
	document.getElementById("total_countedtxt").innerHTML = addCurrency(totalcounted);
	document.getElementById("total_differencetxt").innerHTML = addCurrency(totalcounted-totalcalculated);
}

async function AJ_update(tableidvalue, updatedfieldvalue){
	if(allowed['14'] && allowed['14'].includes('cncpt')){
		noPermissionWarning(Translate('Payment Type'));
		return;
	}

	if(tableidvalue>0 && updatedfieldvalue !==''){		

		const paymentmethod = document.querySelector("#paymentmethod"+tableidvalue);
		paymentmethod.classList.add('lightYellow');
		paymentmethod.disabled = true;		
	
		const jsonData = {"tableidvalue":tableidvalue, "updatedfieldvalue":updatedfieldvalue};
		const url = '/'+segment1+'/AJ_update';

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.returnStr !=='error'){
				showTopMessage('success_msg',Translate('Updated successfully.'));
				AJ_view_MoreInfo();
			}
			else{
				showTopMessage('alert_msg',Translate('Could not update'));
			}
			paymentmethod.classList.remove('lightYellow');
			paymentmethod.disabled = false;
		}
	}
}		

async function cashDrawerCounter(idName){	
	const jsonData = {"idName":idName};
	const url = '/'+segment1+'/cashDrawerCounter';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let tr,th,td,span,h4;
		const div = cTag('div',{ "id":"no-more-tables" });
		div.append(cTag('span', {class: "error_msg", 'style': 'padding-right: 12px;', id: 'errorCashDrawer'}))
			const table = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing" });
				const thead = cTag('thead');
					tr = cTag('tr');
						th = cTag('th',{ "width":"35%" });
						th.innerHTML = Translate('Denomination');
					tr.appendChild(th);
						th = cTag('th',{ "width":"30%" });
						th.innerHTML = Translate('Quantity');
					tr.appendChild(th);
						th = cTag('th');
						th.innerHTML = Translate('Total');
					tr.appendChild(th);
				thead.appendChild(tr);
			table.appendChild(thead);
				const tbody = cTag('tbody');
				data.denominationsData.forEach(item=>{
					const denomination = item.split('=');
						tr = cTag('tr');
							td = cTag('td',{ 'style': "text-align: right;", "align":"right" });
							td.append(denomination[0]);
							td.appendChild(cTag('input',{ "type":"hidden","class":"denomination","value":denomination[1] }));
						tr.appendChild(td);
							td = cTag('td',{ "align":"right" });
								let quantity = cTag('input',{ "type":"text","class":"form-control qtyfield", 'style': "text-align: right;", 'data-min':'0','data-max':'99999', 'data-format': 'd' });
								controllNumericField(quantity, '#errorCashDrawer');
								quantity.addEventListener('change',calculateCashCounted);
							td.appendChild(quantity);
						tr.appendChild(td);
							td = cTag('td',{ 'style': "text-align: right;" });
							td.append('$');
								span = cTag('span',{ "class":"sub-total" });
								span.innerHTML = '0.00';
							td.appendChild(span);
						tr.appendChild(td);
					tbody.appendChild(tr);
				})
					tr = cTag('tr');
						td = cTag('td',{ "colspan":"2","align":"right" });
							h4 = cTag('h4');
							h4.innerHTML = Translate('Grand Total');
						td.appendChild(h4);
					tr.appendChild(td);
						td = cTag('td',{ 'style': "text-align: right;" });
						td.appendChild(cTag('input',{ "type":"hidden","id":"idName","value":idName }));
						td.appendChild(cTag('input',{ "type":"hidden","id":"grandTotal","value":"0" }));
							h4 = cTag('h4');
							h4.append('$');
								span = cTag('span',{ "id":"grandTotalStr" });
								span.innerHTML = '0.00';
							h4.appendChild(span);
						td.appendChild(h4);
					tr.appendChild(td);
				tbody.appendChild(tr);
			table.appendChild(tbody);
		div.appendChild(table);

		popup_dialog600(Translate('Cash Drawer Counter'), div, Translate('Save'), calculateCashDrawer);
		setTimeout(function() {
			if(document.getElementsByClassName("qtyfield").length) document.getElementsByClassName("qtyfield")[0].focus();
			calculateCashCounted();
			document.querySelectorAll(".qtyfield").forEach(item=>{
				item.addEventListener('keyup',function() {	
					calculateCashCounted();
				})
			})
		});
	}
}

function calculateCashCounted(){
	const denominationArray = document.getElementsByClassName("denomination");
	const quantityArray = document.getElementsByClassName("qtyfield");
	const subTotalArray = document.getElementsByClassName("sub-total");
	let grandTotal = 0;
	if(denominationArray.length>0){
		for(let l=0; l<denominationArray.length; l++){
			let denomination = parseFloat(denominationArray[l].value);
			if(isNaN(denomination) || denomination===''){denomination = 0;}			

			let quantity = parseFloat(quantityArray[l].value);
			if(isNaN(quantity) || quantity===''){quantity = 0;}			

			const subTotal = denomination*quantity;
			grandTotal += subTotal;
			subTotalArray[l].innerHTML = number_format(subTotal);
		}
	}	

	document.querySelector("#grandTotal").value = grandTotal;
	document.querySelector("#grandTotalStr").innerHTML = number_format(grandTotal);

}

function calculateCashDrawer(hidePopup){
	let invalidFields = [...document.querySelectorAll('qtyfield')].filter(item => !item.valid());
	if(invalidFields.length > 0) return;

	let grandTotal;
	grandTotal = parseFloat(document.querySelector("#grandTotal").value);
	grandTotal = number_format(grandTotal, 2).replace(',', '');
	const idName = document.querySelector("#idName").value;
	document.querySelector("#"+idName).value = grandTotal;
	calculateCash();
	hidePopup();
}

async function check_frmend_of_day(event=false){
	if(event){event.preventDefault();}

	if(document.getElementById("errorDrawer")){
		document.getElementById("errorDrawer").innerHTML = '';
	}

	let multiple_cash_drawers = 0;
	if(document.getElementById("multiple_cash_drawers")){
		multiple_cash_drawers = parseInt(document.getElementById("multiple_cash_drawers").value);
		if(isNaN(multiple_cash_drawers)){multiple_cash_drawers = 0;}
	}
	if(multiple_cash_drawers>0 && document.getElementById("drawer") && document.getElementById("drawer").value==''){
		if(document.getElementById("errorDrawer")){
			document.getElementById("errorDrawer").innerHTML = Translate('Missing drawer');
		}
		document.getElementById("drawer").focus();
		return false;
	}

	if (!document.getElementById('cash_counted').valid()) return;
	if (!document.getElementById('starting_cash').valid()) return;

    const submitBtn = document.querySelector("#submit");
    btnEnableDisable(submitBtn,Translate('Saving'),true);
  
    const jsonData = serialize("#frmend_of_day");
    const url = '/'+segment1+'/saveend_of_day';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
        if(data.returnStr !=='error'){
			AJ_view_MoreInfo();
		}
		else{
            showTopMessage('alert_msg',data.message);
			if(parseInt(document.getElementById("end_of_day_id").value)===0){
                btnEnableDisable(submitBtn,Translate('Add'),false);
			}
			else{
                btnEnableDisable(submitBtn,Translate('Update'),false);
			}
		}
    }
	return false;
}

//=======list=========
function lists(){
 	const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';

	Dashboard.appendChild(cTag('input',{ "type":"hidden","name":"pageURI","id":"pageURI","value":`${segment1}/${segment2}` }));
	Dashboard.appendChild(cTag('input',{ "type":"hidden","name":"page","id":"page","value":"1" }));
	Dashboard.appendChild(cTag('input',{ "type":"hidden","name":"rowHeight","id":"rowHeight","value":"65" }));
	Dashboard.appendChild(cTag('input',{ "type":"hidden","name":"totalTableRows","id":"totalTableRows","value":"1" }));
		const titleRow = cTag('div',{ "class":"flexSpaBetRow outerListsTable" });
			const titleColumn = cTag('div',{ "class":"columnXS12 columnSM6 "});
				const titleHeader = cTag('h2',{ 'style': "text-align: start;" });
				titleHeader.append(Translate('Manage End of Day')+' ');
				titleHeader.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('Manage End of Day') }));
			titleColumn.appendChild(titleHeader);
		titleRow.appendChild(titleColumn);

			const buttonNames = cTag('div',{ "class": "columnXS12 columnSM6", 'style': "text-align: end;" });
				let endOfDayButton = cTag('a',{ "href":"/End_of_Day/view","title":Translate('End of Day'),"class": "btn createButton" });
				endOfDayButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('End of Day'));
			buttonNames.appendChild(endOfDayButton);
				let printButton = cTag('a',{ "href":"javascript:void(0);","click":printEOD,"class":"btn printButton", 'style': "margin-left: 15px;", "title":Translate('Print Reports') });
                printButton.appendChild(cTag('i',{ 'class':`fa fa-print` }));
                if(OS =='unknown'){
                    printButton.append(' '+Translate('Print')+' ');
                }
			buttonNames.appendChild(printButton);
		titleRow.appendChild(buttonNames);
	Dashboard.appendChild(titleRow);
		const filterRow = cTag('div',{ "class":"flexEndRow outerListsTable" });
			const dropDownColumn = cTag('div',{ "class":"columnXS4 columnSM4 columnLG3" });
				let selectSortingType = cTag('select',{ "class":"form-control", 'style': "height: 100%;", "name":"sorting_type","id":"sorting_type","change":filter_End_of_Day_lists });
                setOptions(selectSortingType,{"0":Translate('Date')+' ASC',"1":Translate('Date')+' DESC'},1,0);
			dropDownColumn.appendChild(selectSortingType);            
		filterRow.appendChild(dropDownColumn);
			const searchColumn = cTag('div',{ "class":"columnXS8 columnSM4 columnLG3" });
				const searchInput = cTag('div',{ "class":"input-group daterangeContainer" });
					const dateRangeField = cTag('input',{"type":"text","placeholder":Translate('End of Day'),"id":"date_range","name":"date_range","class":"form-control", 'style': "padding-left: 35px;", "maxlength":"23","value":`${date} - ${date}` });
					daterange_picker_dialog(dateRangeField);
				searchInput.appendChild(dateRangeField);

					const searchSpan = cTag('span',{ "class":"input-group-addon cursor","click":filter_End_of_Day_lists,"data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('End of Day') });
					searchSpan.appendChild(cTag('i',{ "class":"fa fa-search" }));
				searchInput.appendChild(searchSpan);
			searchColumn.appendChild(searchInput);
		filterRow.appendChild(searchColumn);
	Dashboard.appendChild(filterRow);

		const listTableColumn = cTag('div',{ "class":"columnXS12","style":"position: relative" });
			const noMoreDiv = cTag('div',{ "id":"no-more-tables" });
				const listTable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing " });
					const listHead = cTag('thead',{ "class":"cf" });
						const listHeadRow = cTag('tr',{class:'outerListsTable'});
							const tdCol = cTag('th',{ 'style': "width: 80px;" });
							tdCol.innerHTML = Translate('Date');
						listHeadRow.appendChild(tdCol);
							const thCol0 = cTag('th',{ "align":"left","width":"15%" });
							thCol0.innerHTML = Translate('Payment Type');
						listHeadRow.appendChild(thCol0);
							const thCol1 = cTag('th',{ "align":"left","width":"15%" });
							thCol1.innerHTML = Translate('Calculated');
						listHeadRow.appendChild(thCol1);
							const thCol2 = cTag('th',{ "align":"left","width":"15%" });
							thCol2.innerHTML = Translate('Counted');
						listHeadRow.appendChild(thCol2);
							const thCol3 = cTag('th',{ "align":"left","width":"15%" });
							thCol3.innerHTML = Translate('Difference');
						listHeadRow.appendChild(thCol3);
							const thCol4 = cTag('th',{ "align":"left" });
							thCol4.innerHTML = Translate('Comments');
						listHeadRow.appendChild(thCol4);
					listHead.appendChild(listHeadRow);
				listTable.appendChild(listHead);
				listTable.appendChild(cTag('tbody',{ "id":"tableRows" }));
			noMoreDiv.appendChild(listTable);
		listTableColumn.appendChild(noMoreDiv);
	Dashboard.appendChild(listTableColumn);
	addPaginationRowFlex(Dashboard);

	let list_filters;
	if (sessionStorage.getItem("list_filters") !== null) {
        list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
    }
    else{
        list_filters = {};
    }

    let sorting_type = '';
    if(list_filters.hasOwnProperty("sorting_type")){
        sorting_type = list_filters.sorting_type;
        if(document.getElementById("sorting_type")){
            document.getElementById("sorting_type").value = sorting_type;
        }
    }

    let date_range = '';
    if(list_filters.hasOwnProperty("date_range")){
        date_range = list_filters.date_range;
        if(document.getElementById("date_range")){
			if(date_range !=''){date_range = DBDateRangeToViewDate(date_range);}
            document.getElementById("date_range").value = date_range;
        }
    }

    let limit = 'auto';
    if(list_filters.hasOwnProperty("limit")){
        limit = list_filters.limit;
        if(document.getElementById("limit")){
            document.getElementById("limit").value = limit;
        }
    }
    
	addCustomeEventListener('filter',filter_End_of_Day_lists);
	addCustomeEventListener('loadTable',loadTableRows_End_of_Day_lists);
	filter_End_of_Day_lists(true);
}

function printEOD(){
	printbyurl('/End_of_Day/prints/eodlist/'+ViewDateRangeToDBDate(document.querySelector('#date_range').value));
}

async function filter_End_of_Day_lists(){
    let page = 1;
	document.querySelector("#page").value = page;	
	
	const jsonData = {};
	jsonData['sorting_type'] = document.querySelector('#sorting_type').value;
	jsonData['date_range'] = ViewDateRangeToDBDate(document.querySelector('#date_range').value);
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;	

	const url = '/'+segment1+'/AJgetPage/filter';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);
		loadListsTableRows(data.tableRows);
		document.querySelector("#totalTableRows").value = data.totalRows;
		onClickPagination();
	}
}

async function loadTableRows_End_of_Day_lists(){
	const jsonData = {};
	jsonData['sorting_type'] = document.querySelector('#sorting_type').value;
	jsonData['date_range'] = ViewDateRangeToDBDate(document.querySelector('#date_range').value);
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.querySelector('#page').value;	
	
	const url = '/'+segment1+'/AJgetPage';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		storeSessionData(jsonData);
		loadListsTableRows(data.tableRows);
		onClickPagination();
	}
}

function loadListsTableRows(data){
	let tableHeadRow, tdCol, thCol;
	const tbody = document.querySelector('#tableRows');
	tbody.innerHTML = '';
	let gtotal_calculated = 0;
	let gtotal_counted = 0;

	if(data.tabledata.length>0){
		data.tabledata.forEach(item=>{
			let total_calculated = 0;
			let total_counted = 0;

			let rowSpan = item.cashData.length+1;
			item.cashData.forEach((cashDataItem,indx)=>{
				total_calculated+=cashDataItem[3];
				total_counted+=cashDataItem[4];

				if(indx===0){
						tableHeadRow = cTag('tr');
							tdCol = cTag('td',{ "rowspan":rowSpan,"data-title":Translate('Date'),"align":"center" });
							tdCol.innerHTML = DBDateToViewDate(cashDataItem[1],1)[0];
							let drawer = cashDataItem[5];
							if(drawer !=''){
								tdCol.append(cTag('br'), drawer);
							}
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Payment Type'),"align":"left" });
							tdCol.innerHTML = cashDataItem[2];
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Calculated'),"align":"right" });
							tdCol.innerHTML = addCurrency(cashDataItem[3]);
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Counted'),"align":"right" });
							tdCol.innerHTML = addCurrency(cashDataItem[4]);
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Difference'),"align":"right" });
							tdCol.innerHTML = addCurrency(cashDataItem[3]-cashDataItem[4]);
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "rowspan":rowSpan,"data-title":Translate('Comments'),"align":"left" });
							tdCol.innerHTML = item.comments||'&nbsp;';
						tableHeadRow.appendChild(tdCol);
					tbody.appendChild(tableHeadRow);
				}
				else{
						tableHeadRow = cTag('tr');
							tdCol = cTag('td',{ "data-title":Translate('Payment Type'),"align":"left" });
							tdCol.innerHTML = cashDataItem[2];
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Calculated'),"align":"right" });
							tdCol.innerHTML = addCurrency(cashDataItem[3]);
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Counted'),"align":"right" });
							tdCol.innerHTML = addCurrency(cashDataItem[4]);
						tableHeadRow.appendChild(tdCol);
							tdCol = cTag('td',{ "data-title":Translate('Difference'),"align":"right" });
							tdCol.innerHTML = addCurrency(cashDataItem[3]-cashDataItem[4]);
						tableHeadRow.appendChild(tdCol);
					tbody.appendChild(tableHeadRow);
				}
			})
				tableHeadRow = cTag('tr');
					thCol = cTag('th',{ 'style': "text-align: right;" });
					thCol.innerHTML = Translate('Total')+' : ';
				tableHeadRow.appendChild(thCol);
					thCol = cTag('th',{ 'style': "text-align: right;" });
					thCol.innerHTML = addCurrency(total_calculated);
				tableHeadRow.appendChild(thCol);
					thCol = cTag('th',{ 'style': "text-align: right;" });
					thCol.innerHTML = addCurrency(total_counted);
				tableHeadRow.appendChild(thCol);
					thCol = cTag('th',{ 'style': "text-align: right;" });
					thCol.innerHTML = addCurrency(total_calculated-total_counted);
				tableHeadRow.appendChild(thCol);
			tbody.appendChild(tableHeadRow);

			gtotal_calculated += total_calculated;
			gtotal_counted += total_counted;
		});
	}

		tableHeadRow = cTag('tr');
			thCol = cTag('th',{ "colspan":"2", 'style': "text-align: right;" });
			thCol.innerHTML = Translate('Grand Total')+' : ';
		tableHeadRow.appendChild(thCol);
			thCol = cTag('th',{ 'style': "text-align: right;" });
			thCol.innerHTML = addCurrency(gtotal_calculated);
		tableHeadRow.appendChild(thCol);
			thCol = cTag('th',{ 'style': "text-align: right;" });
			thCol.innerHTML = addCurrency(gtotal_counted);
		tableHeadRow.appendChild(thCol);
			thCol = cTag('th',{ 'style': "text-align: right;" });
			thCol.innerHTML = addCurrency(gtotal_calculated-gtotal_counted);
		tableHeadRow.appendChild(thCol);
			thCol = cTag('th',{ 'style': "text-align: right;" });
			thCol.innerHTML = ' ';
		tableHeadRow.appendChild(thCol);
	tbody.appendChild(tableHeadRow);
}

document.addEventListener('DOMContentLoaded', async()=>{	
	let layoutFunctions = {lists,view};
	layoutFunctions[segment2]();            
	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});