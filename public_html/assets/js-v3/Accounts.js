import {
	cTag, Translate, checkAndSetLimit, confirmAJremoveData, confirm_dialog, setOptions, addPaginationRowFlex,
	popup_dialog, popup_dialog600, popup_dialog1000, daterange_picker_dialog, applySanitizer, fetchData,
	listenToEnterKey, addCustomeEventListener, showNewInputOrSelect, serialize, onClickPagination, number_format,
	customAutoComplete, DBDateToViewDate, date_picker, btnEnableDisable, leftsideHide, getMobileOperatingSystem,
	showTopMessage
} from './common.js';

if (!segment2) { segment2 = 'dashboard'; }

//dummy-data
const dummyVoucherTableRows = [
	{
		id: 14,
		created_date: '30.09.2022',
		voucherNo: 5,
		ledgerDetails: [
			{ ledger_name: 'Abdus Shobhan', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 100000, credit: 0 },
			{ ledger_name: 'Mahabub', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 100000, credit: 0 },
			{ ledger_name: 'Cash in Hand', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 0, credit: 200000 },
		]
	},
	{
		id: 14,
		created_date: '30.09.2022',
		voucherNo: 5,
		ledgerDetails: [
			{ ledger_name: 'Kayser', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 300000, credit: 0 },
			{ ledger_name: 'Asif', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 500000, credit: 0 },
			{ ledger_name: 'Cash in Hand', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 0, credit: 800000 },
		]
	},
	{
		id: 14,
		created_date: '30.09.2022',
		voucherNo: 5,
		ledgerDetails: [
			{ ledger_name: 'Niloy', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 100000, credit: 0 },
			{ ledger_name: 'Rony', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 300000, credit: 0 },
			{ ledger_name: 'Cash in Hand', narration: 'Cash paid to Abdus Shobhan tk.1,00,000 for software development charge for Oct-20222', debit: 0, credit: 400000 },
		]
	}
]
const dummyLedgerReportTableRows = [
	{
		particular_name: 'Garments ERP',
		opening_balance: 0,
		debit: 764587,
		credit: 0,
		sub_reports: []
	},
	{
		particular_name: 'Accounts Payable',
		opening_balance: 0,
		debit: 700000,
		credit: 764587,
		sub_reports: [
			{
				particular_name: 'Abdus Shobhan',
				opening_balance: 0,
				debit: 700000,
				credit: 764587,
			}
		]
	},
	{
		particular_name: 'Cash & Cash Equivalents',
		opening_balance: 0,
		debit: 700000,
		credit: 700000,
		sub_reports: [
			{
				particular_name: 'Cash in Hand',
				opening_balance: 0,
				debit: 700000,
				credit: 700000,
			}
		]
	},
	{
		particular_name: 'Paid Up Capital',
		opening_balance: 0,
		debit: 0,
		credit: 700000,
		sub_reports: []
	}
];
let modulesInfo = {
	'1': { label: Translate('Manage Groups'), fileName: 'groups', icon: 'category.png' },
	'2': { label: Translate('Manage Ledger'), fileName: 'ledger', icon: 'ledger.png' },
	'3': { label: Translate('Receipt Voucher'), fileName: 'receiptVoucher', icon: 'receipt.png' },
	'4': { label: Translate('Payment Voucher'), fileName: 'paymentVoucher', icon: 'payment.png' },
	'5': { label: Translate('Journal Voucher'), fileName: 'journalVoucher', icon: 'journal.png' },
	'6': { label: Translate('Contra Voucher'), fileName: 'contraVoucher', icon: 'contra.png' },
	'7': { label: Translate('Purchase Voucher'), fileName: 'purchaseVoucher', icon: 'purchase.png' },
	'8': { label: Translate('Sales Voucher'), fileName: 'salesVoucher', icon: 'sales.png' },
}
let reportsInfo = {
	'q': { label: Translate('Day Book'), fileName: 'dayBook', icon: 'dayBook.png' },
	'w': { label: Translate('Ledger Report'), fileName: 'ledgerReport', icon: 'ledgerReport.png' },
	'e': { label: Translate('Trial Balance'), fileName: 'trialBalance', icon: 'trialBalance.png' },
	'r': { label: Translate('Receipt & Payment'), fileName: 'receiptPayment', icon: 'payment.png' },
}

function accountTypes(account_type = 0) {
	const data = { '0': 'Account Type', '1': 'Assets', '2': 'Liabilities', '3': 'Equity', '4': 'Revenue/Income', '5': 'Expenses', '6': 'Purchase' };
	if (account_type > 0) {
		if (account_type in data) {
			return data[account_type];
		}
		return '';
	}
	else { return data; }
}

function debitCredits(debit_credit = 0) {
	const data = { '1': 'Increase', '-1': 'Decrease' };
	if (debit_credit !== 0) {
		if (debit_credit in data) {
			return data[debit_credit];
		}
		return '';
	}
	else { return data; }
}

function voucherTypes(voucher_type) {
	const data = { '0': 'All Voucher', '1': 'Receipt', '2': 'Payment', '3': 'Journal', '4': 'Contra', '5': 'Purchase', '6': 'Sales' };
	if (voucher_type !== undefined) {
		if (voucher_type in data) {
			return data[voucher_type];
		}
		return '';
	}
	else { return data; }
}

function transactionTypes(debit_credit = 0) {
	const data = { '1': 'Credit', '-1': 'Debit' };
	if (debit_credit !== 0) {
		if (debit_credit in data) {
			return data[debit_credit];
		}
		return '';
	}
	else { return data; }
}

function publishTypes(publishVal = 0) {
	const data = { '1': 'Active', '2': 'In-Active', '3': 'All' };
	if (publishVal !== 0) {
		if (key in data) {
			return data[key];
		}
		return '';
	}
	else { return data; }
}

function checkAndLoadFilterData() {
	if (document.querySelector('#pageURI')) {
		const pathArray = document.querySelector('#pageURI').value.split('/');
		const loadData = 'filter_' + pathArray[0] + '_' + pathArray[1];
		const fn = window[loadData];
		if (typeof fn === "function") { fn(); }
	}
}

function dashboard() {

	let modulesRow, modulesColumn, modulesWidget, modulesWidgetHeader, modulesHeader, modulesContent, ulMenu, fonticon, module, labelVal, liMenu, homeDiv, aTag, iconTag

	const showTableData = document.getElementById('viewPageInfo');

	modulesRow = cTag('div', { 'class': `flexSpaBetRow` });
	modulesColumn = cTag('div', { 'class': `columnSM12`, 'style': "margin-top: 0px;" });
	modulesWidget = cTag('div', { 'class': `cardContainer ` });
	modulesWidgetHeader = cTag('div', { 'class': `cardHeader ` });
	modulesHeader = cTag('h3');
	modulesHeader.innerHTML = Translate('Account Modules list');
	modulesWidgetHeader.appendChild(modulesHeader);
	modulesWidget.appendChild(modulesWidgetHeader);
	modulesContent = cTag('div', { 'class': `cardContent` });
	ulMenu = cTag('ul', { 'class': `flexStartRow moduleLists`, 'style': "text-align: center;" });
	for (const key in modulesInfo) {
		if (modulesInfo[key]) {
			fonticon = modulesInfo[key].icon;
			module = modulesInfo[key].fileName;
			labelVal = modulesInfo[key].label;
			liMenu = cTag('li');
			homeDiv = cTag('div', { 'class': `homeiconmenu boxshadow `, 'style': "background: #0185b6;" });
			aTag = cTag('a', { 'class': `firstclild sidebarlink`, 'style': "color: white;", 'href': '/Accounts/' + module, 'title': labelVal });
			iconTag = cTag('img', { 'src': "/assets/images/Accounts/" + fonticon, 'style': "height:32px;", 'alt': labelVal });
			aTag.append(cTag('br'), iconTag, cTag('br'), key + ' ', cTag('i', { 'class': 'fa fa-arrow-right' }), ' ' + labelVal);
			homeDiv.appendChild(aTag);
			liMenu.appendChild(homeDiv);
			ulMenu.appendChild(liMenu);
		}
	}
	modulesContent.appendChild(ulMenu);
	modulesWidget.appendChild(modulesContent);
	modulesColumn.appendChild(modulesWidget);
	modulesRow.appendChild(modulesColumn);
	showTableData.appendChild(modulesRow);

	modulesRow = cTag('div', { 'class': `flexSpaBetRow` });
	modulesColumn = cTag('div', { 'class': `columnSM12`, 'style': "margin-top: 0px;" });
	modulesWidget = cTag('div', { 'class': `cardContainer ` });
	modulesWidgetHeader = cTag('div', { 'class': `cardHeader ` });
	modulesHeader = cTag('h3');
	modulesHeader.innerHTML = Translate('Accounting Reports');
	modulesWidgetHeader.appendChild(modulesHeader);
	modulesWidget.appendChild(modulesWidgetHeader);
	modulesContent = cTag('div', { 'class': `cardContent` });
	ulMenu = cTag('ul', { 'class': `flexStartRow moduleLists`, 'style': "text-align: center;" });
	for (const key in reportsInfo) {
		if (reportsInfo[key]) {
			fonticon = reportsInfo[key].icon;
			module = reportsInfo[key].fileName;
			labelVal = reportsInfo[key].label;
			liMenu = cTag('li');
			homeDiv = cTag('div', { 'class': `homeiconmenu boxshadow `, 'style': "background: #0185b6;" });
			aTag = cTag('a', { 'class': `firstclild sidebarlink`, 'style': "color: white;", 'href': '/Accounts/' + module, 'title': labelVal });
			iconTag = cTag('img', { 'src': "/assets/images/Accounts/" + fonticon, 'style': "height:32px;", 'alt': labelVal });
			aTag.append(cTag('br'), iconTag, cTag('br'), key + ' ', cTag('i', { 'class': 'fa fa-arrow-right' }), ' ' + labelVal);
			homeDiv.appendChild(aTag);
			liMenu.appendChild(homeDiv);
			ulMenu.appendChild(liMenu);
		}
	}
	modulesContent.appendChild(ulMenu);
	modulesWidget.appendChild(modulesContent);
	modulesColumn.appendChild(modulesWidget);
	modulesRow.appendChild(modulesColumn);
	showTableData.appendChild(modulesRow);

	AJ_dashboard_MoreInfo();
}

function AJ_dashboard_MoreInfo() {

}

function accountsPageTemplate(mainSection, title) {
	document.querySelectorAll('#dashboard_contant > div:not(#viewPageInfo)').forEach(item => item.remove());
	// document.getElementById('dashboard_contant').querySelectorAll('.flexStartRow,.flexSpaBetRow').forEach(item=>item.remove());
	const menuOptions = {
		...modulesInfo,
		...reportsInfo
	};
	for (const key in menuOptions) {
		if (menuOptions[key]) {
			let module = menuOptions[key].fileName;
			let labelVal = menuOptions[key].label.split('. ');
			if (module === segment2) { title = labelVal[1]; }
		}
	}

	let div, ul, li, a, span;
	const viewPageInfo = document.getElementById('viewPageInfo');
	div = cTag('div', { 'class': `flexStartRow` });
	const h2 = cTag('h2', { 'style': `padding: 5px; text-align: start;` });
	h2.append(title + ' ');
	h2.appendChild(cTag('i', { 'class': `fa fa-info-circle`, 'style': `font-size: 16px;`, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': ``, 'data-original-title': 'This page captures the accounts settings', 'data-tooltip-active': `true` }));
	div.appendChild(h2);
	viewPageInfo.appendChild(div);
	const div2 = cTag('div', { 'class': `flexSpaBetRow` });
	const div1 = cTag('div', { 'class': `columnMD2 columnSM3`, 'style': `margin: 0;` });
	div = cTag('div', { 'style': `padding-top: 0;`, 'class': `innerContainer` });
	a = cTag('a', { 'href': `javascript:void(0);`, 'id': `secondarySideMenu` });
	a.appendChild(cTag('i', { 'class': `fa fa-align-justify`, 'style': `margin-bottom: 10px; font-size: 2em;` }));
	div.appendChild(a);
	ul = cTag('ul', { 'class': `secondaryNavMenu settingslefthide` });
	li = cTag('li');
	a = cTag('a', { 'href': `/Accounts/dashboard`, 'title': 'Dashboard' });
	span = cTag('span');
	span.innerHTML = '<b><i class="fa fa-list"></i> Dashboard</b>';
	a.appendChild(span);
	li.appendChild(a);
	ul.appendChild(li);

	for (const key in menuOptions) {
		if (menuOptions[key]) {
			let module = menuOptions[key].fileName;
			let labelVal = menuOptions[key].label;
			if (module === segment2) {
				const li = cTag('li', { 'class': `activeclass` });
				const h4 = cTag('h4', { 'style': `font-size: 18px;` });
				h4.innerHTML = `${key} <i class="fa fa-arrow-right"></i> ${labelVal}`;
				li.appendChild(h4);
				ul.appendChild(li);
			} else {
				li = cTag('li');
				a = cTag('a', { 'href': `/Accounts/${module}`, 'title': labelVal });
				span = cTag('span');
				span.innerHTML = `${key} <i class="fa fa-arrow-right"></i> ${labelVal}`;
				a.appendChild(span);
				li.appendChild(a);
				ul.appendChild(li);
			}
		}
	}
	div.appendChild(ul);
	div1.appendChild(div);
	div2.appendChild(div1);
	const mainSectionContainer = cTag('div', { 'class': `columnMD10 columnSM9`, 'style': `margin: 0;` });
	mainSectionContainer.appendChild(mainSection);
	div2.appendChild(mainSectionContainer);
	viewPageInfo.appendChild(div2);
}

//============Manage Groups=========//
function groups() {
	let div, select, div1, tr, th;
	const mainSection = cTag('div', { class: "innerContainer", style: "background: #fff;" });
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/groups` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `page`, 'id': `page`, 'value': `1` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `rowHeight`, 'id': `rowHeight`, 'value': `34` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `totalTableRows`, 'id': `totalTableRows`, 'value': `20` }));
	const div2 = cTag('div', { 'class': `flexEndRow` });
	div = cTag('div', { 'class': `columnXS6 columnSM3`, 'align': `right` });
	const button = cTag('button', { 'class': `btn createButton`, 'click': () => AJgetGroupsPopup(0), 'title': `Create New Groups` });
	button.appendChild(cTag('i', { 'class': `fa fa-plus` }));
	button.append(' Groups');
	div.appendChild(button);
	div2.appendChild(div);
	div = cTag('div', { class: "columnXS6 columnSM3" });
	select = cTag('select', { class: "form-control", name: "sdata_type", id: "sdata_type" });
	select.addEventListener('change', filter_Accounts_groups);
	setOptions(select, publishTypes(), 1, 0);
	div.appendChild(select);
	div2.appendChild(div);

	div = cTag('div', { 'class': `columnXS6 columnSM3` });
	select = cTag('select', { 'name': `faccount_type`, 'id': `faccount_type`, 'class': `form-control`, 'change': filter_Accounts_groups });
	const options = { 0: 'All Account Type', 1: 'Assets', 2: 'Liabilities', 3: 'Equity', 4: 'Revenue/Income', 5: 'Expenses', 6: 'Purchase' }
	setOptions(select, options, 1, 1);
	div.appendChild(select);
	div2.appendChild(div);
	div1 = cTag('div', { 'class': `columnXS6 columnSM3` });
	div = cTag('div', { 'class': `input-group` });
	div.appendChild(cTag('input', { 'type': `text`, 'placeholder': `Search Information`, 'value': ``, 'id': `keyword_search`, 'name': `keyword_search`, 'class': `form-control`, 'keypress': listenToEnterKey(filter_Accounts_groups), 'maxlength': `50` }));
	const span = cTag('span', { 'class': `input-group-addon cursor`, 'click': filter_Accounts_groups, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Search Information` });
	span.appendChild(cTag('i', { 'class': `fa fa-search` }));
	div.appendChild(span);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	div1 = cTag('div', { 'class': `columnXS12` });
	div = cTag('div', { 'id': `no-more-tables` });
	const table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
	const thead = cTag('thead', { 'class': `cf` });
	tr = cTag('tr');
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Account Type Name';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Parent Group Name';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Groups Name';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Action';
	tr.appendChild(th);
	thead.appendChild(tr);
	table.appendChild(thead);
	table.appendChild(cTag('tbody', { 'id': `tableRows` }));
	div.appendChild(table);
	div1.appendChild(div);
	mainSection.appendChild(div1);
	addPaginationRowFlex(mainSection);
	accountsPageTemplate(mainSection);
	filter_Accounts_groups();
	addCustomeEventListener('loadTable', loadTableRows_Accounts_groups);
	addCustomeEventListener('filter', filter_Accounts_groups);
}

export function filter_Accounts_groups() {
	const page = 1;
	document.querySelector("#page").value = page;

	const url = "/Accounts/AJgetPage_groups/filter";
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		document.querySelector("#totalTableRows").value = data.totalRows;
		loadGroupsTableRows(data.tableRows);
		onClickPagination();
	}
}

export function loadTableRows_Accounts_groups() {
	const jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = document.querySelector('#limit').value;
	jsonData['page'] = document.querySelector('#page').value;

	const url = "/Accounts/AJgetPage_groups";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		loadGroupsTableRows(data.tableRows);
		onClickPagination();
	}
}

function loadGroupsTableRows(tableRows) {
	const node = document.getElementById('tableRows');
	node.innerHTML = '';
	let tr, td, a;
	tableRows.forEach((item, index) => {
		let publish = item[3];
		tr = cTag('tr');
		if (publish == 0) { tr.setAttribute('style', 'background:#fff2ec'); }
		td = cTag('td', { 'data-title': `Account Type Name`, 'align': `left` });
		td.innerHTML = item[1];
		tr.appendChild(td);
		td = cTag('td', { 'data-title': `Parent Groups Name`, 'align': `left` });
		td.innerHTML = item[2];
		tr.appendChild(td);
		td = cTag('td', { 'data-title': `Groups Name`, 'align': `left` });
		td.innerHTML = item[3];
		tr.appendChild(td);
		td = cTag('td', { 'data-title': 'Action', 'align': `center` });
		a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJgetGroupsPopup(item[0]), 'title': `Change this Information` });
		a.appendChild(cTag('i', { 'class': `fa fa-edit txt18` }));
		td.appendChild(a);
		td.append('   ');

		if (publish == 0) { publish = 1; }
		else { publish = 0; }

		a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJremove_Data('groups', item[0], item[3], publish), 'title': `Archive this Groups` });
		a.appendChild(cTag('i', { 'class': `fa fa-remove errormsg txt18` }));
		td.appendChild(a);
		tr.appendChild(td);
		node.appendChild(tr);
	})
}

function AJgetGroupsPopup(groups_id) {

	const url = "/Accounts/AJgetGroupsPopup";
	const data = { "groups_id": groups_id };

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		let div, div1, span, label, select;
		const popupContent = cTag('div');
		popupContent.appendChild(cTag('div', { 'id': `error_groups`, 'class': `errormsg` }));
		const form = cTag('form', { 'action': `#`, 'name': `frmSubGroup`, 'id': `frmSubGroup`, 'enctype': `multipart/form-data`, 'method': `post`, 'accept-charset': `utf-8` });
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `account_type` });
		label.append('Account Type');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		select = cTag('select', { 'required': `required`, 'class': `form-control`, 'name': `account_type`, 'id': `account_type`, 'change': () => setParGroOpt('') });
		setOptions(select, accountTypes(), 1, 0);
		select.value = data.account_type;
		div.appendChild(select);
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `parent_group_id` });
		label.append('Parent Group Name');
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		select = cTag('select', { 'class': `form-control`, 'name': `parent_group_id`, 'id': `parent_group_id` });
		let option = cTag('option', { 'value': `0` });
		option.innerHTML = 'Parent Group Name';
		select.appendChild(option);
		setOptions(select, data.parGroOpts, 1, 0);
		select.value = data.parent_group_id;
		div.appendChild(select);
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `name` });
		label.append('Groups Name');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		div.appendChild(cTag('input', { 'autocomplete': `off`, 'required': `required`, 'type': `text`, 'class': `form-control`, 'name': `name`, 'id': `name`, 'value': data.name, 'maxlength': `50` }));
		div1.appendChild(div);
		form.appendChild(div1);
		form.appendChild(cTag('input', { 'type': `hidden`, 'name': `groups_id`, 'value': groups_id }));
		popupContent.appendChild(form);

		popup_dialog600(Translate('Groups Information'), popupContent, Translate('Save'), saveGroupsForm);

		document.querySelector("#account_type").focus();
	}
	return true;
}

function setParGroOpt(preLetter = '') {
	let account_type = parseInt(document.querySelector("#" + preLetter + "account_type").value);
	if (isNaN(account_type)) { account_type = 0; }

	let select = document.querySelector('#' + preLetter + 'parent_group_id');
	select.innerHTML = '';
	let option = cTag('option', { 'value': `0` });
	option.innerHTML = 'Parent Group Name';
	select.appendChild(option);

	if (account_type > 0) {
		let parent_group_id = parseInt(select.value);
		if (isNaN(parent_group_id)) { parent_group_id = 0; }

		const url = "/Accounts/setParGroOpt/" + account_type + '/1';
		const data = {};
		fetchData(afterFetch, url, data);
		function afterFetch(data) {
			setOptions(select, data.parGroOpt, 1, 0);
			if (parent_group_id > 0) {
				checkSetSelectValue(preLetter + 'parent_group_id', parent_group_id);
			}
		}
	}
}

function saveGroupsForm(hidePopup) {
	let errorObj = document.getElementById('error_groups');
	errorObj.innerHTML = '';
	if (document.querySelector("#account_type").value == 0) {
		errorObj.innerHTML = 'Missing Group Name';
		document.querySelector("#account_type").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if (document.querySelector("#name").value == '') {
		errorObj.innerHTML = 'Missing Groups Name';
		document.querySelector("#name").focus();
		return false;
	}

	errorObj.innerHTML = '';

	const saveButton = document.querySelector(".btnmodel");
	btnEnableDisable(saveButton, Translate('Saving...'), true);

	const url = "/Accounts/AJsaveGroups/";
	const data = serialize('#frmSubGroup');

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		if (data.savemsg != 'error') {
			if (document.querySelector("#pageURI").value == 'Accounts/groups') {
				filter_Accounts_groups();
				hidePopup();
			}
		}
		else {
			errorObj.innerHTML = data.returnStr;
		}
		btnEnableDisable(saveButton, Translate('Save'), false);
	}
	return false;
}

//============Manage Ledger============//
function ledger() {
	let div1, div, div2, th, select;
	const mainSection = cTag('div', { 'class': `innerContainer`, 'style': `background: #fff` });
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/ledger` }));
	div1 = cTag('div', { 'class': `flexSpaBetRow` });
	div = cTag('div', { 'class': `columnXS6`, 'style': `margin-top: 0` });
	const h1 = cTag('h1', { 'class': `metatitle floatleft` });
	h1.append('Manage Ledger ');
	h1.appendChild(cTag('i', { 'class': `fa fa-info-circle txt16normal`, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Manage Ledger Information` }));
	div.appendChild(h1);
	div1.appendChild(div);
	div = cTag('div', { 'class': `columnXS6`, 'style': `margin-top: 0; text-align: right` });
	const button = cTag('button', { 'class': `btn createButton`, 'click': () => AJgetLedgerPopup(0), 'title': `Create New Ledger` });
	button.appendChild(cTag('i', { 'class': `fa fa-plus` }));
	button.append(' Ledger');
	div.appendChild(button);
	div1.appendChild(div);
	mainSection.appendChild(div1);
	div2 = cTag('div', { 'class': `flexStartRow` });
	div = cTag('div', { 'class': `columnMD2 columnSM4 columnXS6` });
	select = cTag('select', { class: "form-control", name: "sdata_type", id: "sdata_type", 'change': ledgerData });
	setOptions(select, publishTypes(), 1, 0);
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnMD2 columnSM4 columnXS6` });
	select = cTag('select', { class: "form-control", name: "faccount_type", id: "faccount_type" });
	select.addEventListener('change', () => setGroupsOpt('f'));
	setOptions(select, accountTypes(), 1, 0);
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnMD2 columnSM4 columnXS6` });
	div.appendChild(cTag('select', { 'name': `fgroups_id`, 'id': `fgroups_id`, 'class': `form-control`, 'change': ledgerData }));
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnMD2 columnSM4 columnXS6` });
	div.appendChild(cTag('select', { 'name': `fgroups_id1`, 'id': `fgroups_id1`, 'class': `form-control`, 'change': ledgerData }));
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnMD2 columnSM4 columnXS6` });
	select = cTag('select', { class: "form-control", name: "fvisible_on", id: "fvisible_on", 'change': ledgerData });
	setOptions(select, voucherTypes(), 1, 0);
	div.appendChild(select);
	div2.appendChild(div);
	div1 = cTag('div', { 'class': `columnMD2 columnSM4 columnXS6` });
	div = cTag('div', { 'class': `input-group` });
	div.appendChild(cTag('input', { 'type': `text`, 'placeholder': `Search Ledger Name`, 'keyup': listenToEnterKey(ledgerData), 'value': ``, 'id': `keyword_search`, 'name': `keyword_search`, 'class': `form-control`, 'maxlength': `50` }));
	const span = cTag('span', { 'class': `input-group-addon cursor`, 'click': ledgerData, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Search Information` });
	span.appendChild(cTag('i', { 'class': `fa fa-search` }));
	div.appendChild(span);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div1 = cTag('div', { 'class': `columnXS12` });
	div = cTag('div', { 'id': `no-more-tables` });
	const table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
	const thead = cTag('thead', { 'class': `cf` });
	const tr = cTag('tr');
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Account Type';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Groups Name';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Ledger Name';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Visible On';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center` });
	th.innerHTML = 'Debit';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center` });
	th.innerHTML = 'Closing Date';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center` });
	th.innerHTML = 'Action';
	tr.appendChild(th);

	thead.appendChild(tr);
	table.appendChild(thead);
	table.appendChild(cTag('tbody', { 'id': `tableRows` }));
	div.appendChild(table);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);

	accountsPageTemplate(mainSection);

	ledgerData();
}

function ledgerData() {
	let fgroups_id = document.querySelector('#fgroups_id').value;
	let fgroups_id1 = document.querySelector('#fgroups_id1').value;

	let jsonData = {};
	jsonData['sdata_type'] = document.querySelector('#sdata_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = fgroups_id;
	jsonData['fgroups_id1'] = fgroups_id1;
	jsonData['fvisible_on'] = document.querySelector('#fvisible_on').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;

	const url = "/Accounts/AJgetPage_ledger";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		let fgroups_idObj = document.querySelector("#fgroups_id");
		fgroups_idObj.innerHTML = '';
		let option = cTag('option', { 'value': '' });
		option.innerHTML = 'All Groups';
		fgroups_idObj.appendChild(option);
		setOptions(fgroups_idObj, data.groupsOpt, 1, 1);

		let fgroups_id1Obj = document.querySelector("#fgroups_id1");
		fgroups_id1Obj.innerHTML = '';
		option = cTag('option', { 'value': '' });
		option.innerHTML = 'All Sub-Groups1';
		fgroups_id1Obj.appendChild(option);
		setOptions(fgroups_id1Obj, data.groupsOpt1, 1, 0);

		document.querySelector('#fgroups_id').value = fgroups_id;
		document.querySelector('#fgroups_id1').value = fgroups_id1;

		const tableRows = document.getElementById('tableRows');
		tableRows.innerHTML = '';
		data.tableRows.forEach((item, index) => {
			let ledgerId = item[0];
			let tr, td, a, ledger_publish;
			ledger_publish = item[7];

			tr = cTag('tr');
			if (ledger_publish === 2) {
				tr.setAttribute('class', 'lightyellowrow');
			}
			td = cTag('td', { 'data-title': `Accounts Type Name`, 'align': `left` });
			td.innerHTML = accountTypes(item[1]);
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Groups Name`, 'align': `left` });
			td.innerHTML = item[2];
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Ledger Name`, 'align': `left` });
			td.innerHTML = item[3];
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Visible On`, 'align': `left` });
			td.innerHTML = item[4].split(',').map(voucherID => voucherTypes(voucherID)).join(', ') || voucherTypes(0);
			tr.appendChild(td);
			td = cTag('td', { 'nowrap': ``, 'data-title': `Debit`, 'align': `center` });
			td.innerHTML = debitCredits(item[5]);
			tr.appendChild(td);
			td = cTag('td', { 'nowrap': ``, 'data-title': `Closing Date`, 'align': `center` });
			td.innerHTML = DBDateToViewDate(item[6], 0, 1);
			tr.appendChild(td);
			td = cTag('td', { 'nowrap': ``, 'data-title': 'Action', 'align': `center` });
			a = cTag('a', { 'href': `/Accounts/ledgerView/${ledgerId}`, 'title': `View this Ledger Information` });
			a.appendChild(cTag('i', { 'class': `fa fa fa-info-circle txt18` }));
			td.appendChild(a);
			if (item[7] === 1) {
				td.append('   ');
				a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJgetLedgerPopup(ledgerId), 'title': `Change this Account Information` });
				a.appendChild(cTag('i', { 'class': `fa fa-edit txt18` }));
				td.appendChild(a);
			}

			td.append('   ');
			a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJarchive_Popup('ledger', ledgerId, item[3], item[7]), 'title': `${item[7] === 2 ? 'Active' : 'Archive'} this Ledger` });
			if (ledger_publish == 1) {
				a.appendChild(cTag('i', { 'class': `fa fa-remove txt18` }));
			}
			else {
				a.appendChild(cTag('i', { 'class': `fa fa-undo txt18` }));
			}
			td.appendChild(a);

			tr.appendChild(td);
			tableRows.appendChild(tr);
		})
	}
}

function AJgetLedgerPopup(ledger_id) {
	const url = "/Accounts/AJgetLedgerPopup";
	const data = { "ledger_id": ledger_id };

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		let div1, div, label, span, inputgroup, select, option, input;
		const popupContent = cTag('div');
		popupContent.appendChild(cTag('div', { 'id': `error_ledger`, 'class': `errormsg` }));
		const form = cTag('form', { 'action': `#`, 'name': `frmLedger`, 'id': `frmLedger`, 'enctype': `multipart/form-data`, 'method': `post`, 'accept-charset': `utf-8` });
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `account_type` });
		label.append('Account Type');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		select = cTag('select', { 'required': `required`, 'class': `form-control`, 'name': `account_type`, 'data-preLetter': ``, 'id': `account_type` });
		select.addEventListener('change', () => setGroupsOpt(''));
		setOptions(select, accountTypes(), 1, 0);
		select.value = data.account_type;
		div.appendChild(select);
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `groups_id` });
		label.append('Groups Name');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		inputgroup = cTag('div', { 'class': 'input-group' });
		select = cTag('select', { 'required': `required`, 'class': `form-control`, 'name': `groups_id`, 'id': `groups_id`, 'change': () => setSonGroupsOpt('', 0) });
		option = cTag('option', { 'value': `0` });
		option.innerHTML = 'Groups Name';
		select.appendChild(option);
		setOptions(select, data.groupsOpt, 1, 0);
		select.value = data.groups_id;
		inputgroup.appendChild(select);
		input = cTag('input', { 'type': 'text', 'value': '', 'maxlength': '50', 'name': 'groups_name', 'id': 'groups_name', 'class': 'form-control', 'style': 'display:none' });
		inputgroup.appendChild(input);
		span = cTag('span', { 'data-toggle': 'tooltip', 'class': 'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Group') });
		span.append(cTag('i', { 'class': 'fa fa-plus' }), ' ', Translate('New'));
		inputgroup.appendChild(span);
		div.appendChild(inputgroup);
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `groups_id1` });
		label.innerHTML = 'Son Group1: ';
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });

		inputgroup = cTag('div', { 'class': 'input-group' });
		select = cTag('select', { 'required': `required`, 'class': `form-control`, 'name': `groups_id1`, 'id': `groups_id1`, 'change': () => setSonGroupsOpt('', 1) });
		option = cTag('option', { 'value': `0` });
		option.innerHTML = 'Son Group1';
		select.appendChild(option);
		setOptions(select, data.sonGroups1Opt, 1, 0);
		select.value = data.groups_id1;
		inputgroup.appendChild(select);
		input = cTag('input', { 'type': 'text', 'value': '', 'maxlength': '50', 'name': 'groups_name1', 'id': 'groups_name1', 'class': 'form-control', 'style': 'display:none' });
		inputgroup.appendChild(input);
		span = cTag('span', { 'data-toggle': 'tooltip', 'class': 'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Son Group1') });
		span.append(cTag('i', { 'class': 'fa fa-plus' }), ' ', Translate('New'));
		inputgroup.appendChild(span);
		div.appendChild(inputgroup);

		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `groups_id2` });
		label.innerHTML = 'Son Group2: ';
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });

		inputgroup = cTag('div', { 'class': 'input-group' });
		select = cTag('select', { 'required': `required`, 'class': `form-control`, 'name': `groups_id2`, 'id': `groups_id2`, 'change': () => setSonGroupsOpt('', 2) });
		option = cTag('option', { 'value': `0` });
		option.innerHTML = 'Son Group2';
		select.appendChild(option);
		setOptions(select, data.sonGroups2Opt, 1, 0);
		select.value = data.groups_id2;
		inputgroup.appendChild(select);
		input = cTag('input', { 'type': 'text', 'value': '', 'maxlength': '50', 'name': 'groups_name2', 'id': 'groups_name2', 'class': 'form-control', 'style': 'display:none' });
		inputgroup.appendChild(input);
		span = cTag('span', { 'data-toggle': 'tooltip', 'class': 'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Son Group2') });
		span.append(cTag('i', { 'class': 'fa fa-plus' }), ' ', Translate('New'));
		inputgroup.appendChild(span);
		div.appendChild(inputgroup);

		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `groups_id3` });
		label.innerHTML = 'Son Group3: ';
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });

		inputgroup = cTag('div', { 'class': 'input-group' });
		select = cTag('select', { 'required': `required`, 'class': `form-control`, 'name': `groups_id3`, 'id': `groups_id3`, 'change': () => setSonGroupsOpt('', 3) });
		option = cTag('option', { 'value': `0` });
		option.innerHTML = 'Son Group2';
		select.appendChild(option);
		setOptions(select, data.sonGroups2Opt, 1, 0);
		select.value = data.groups_id2;
		inputgroup.appendChild(select);
		input = cTag('input', { 'type': 'text', 'value': '', 'maxlength': '50', 'name': 'groups_name3', 'id': 'groups_name3', 'class': 'form-control', 'style': 'display:none' });
		inputgroup.appendChild(input);
		span = cTag('span', { 'data-toggle': 'tooltip', 'class': 'input-group-addon cursor showNewInputOrSelect', 'title': Translate('Add New Son Group3') });
		span.append(cTag('i', { 'class': 'fa fa-plus' }), ' ', Translate('New'));
		inputgroup.appendChild(span);
		div.appendChild(inputgroup);

		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `name` });
		label.append('Name');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		div.appendChild(cTag('input', { 'autocomplete': `off`, 'required': `required`, 'type': `text`, 'class': `form-control`, 'name': `name`, 'id': `name`, 'value': data.name, 'maxlength': `50` }));
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `visible_on` });
		label.append('Visible On');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div.appendChild(cTag('br'));
		span = cTag('span', { 'style': `font-size:10px` });
		span.innerHTML = 'Do not check any if visible for all voucher.';
		div.appendChild(span);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		const vaoucherContainer = cTag('div', { class: "flexSpaBetRow" });
		const allVoucherVisible = data.visible_on.length === 0;
		for (const [key, type] of Object.entries(voucherTypes())) {
			const div = cTag('div', { 'class': 'columnMD6' });
			const label = cTag('label', { class: 'cursor', style: 'font-weight:normal' });
			const checkbox = cTag('input', { name: 'visible_on[]', type: 'checkbox', 'change': checkVisibility, value: key });
			if (allVoucherVisible) checkbox.checked = false;
			else checkbox.checked = data.visible_on.includes(key);
			label.append(checkbox, ` ${type}`)
			div.appendChild(label);
			vaoucherContainer.appendChild(div);
		}
		div.appendChild(vaoucherContainer);
		div.appendChild(cTag('div', { 'id': `error_visibleOn`, 'class': `errormsg` }));
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `debit` });
		label.append('Debit');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		select = cTag('select', { 'required': `required`, 'class': `form-control`, 'name': `debit`, 'id': `debit` });
		setOptions(select, { '1': 'Increase', '-1': 'Decrease' }, 1, 0);
		select.value = data.debit;
		div.appendChild(select);
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `opening_date` });
		label.innerHTML = 'Opening Date';
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		div.appendChild(cTag('input', { 'type': `text`, 'class': `form-control`, 'name': `opening_date`, 'id': `opening_date`, 'value': data.opening_date, 'maxlength': `10` }));
		div1.appendChild(div);
		form.appendChild(div1);
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `closing_date` });
		label.innerHTML = 'Closing Date';
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		div.appendChild(cTag('input', { 'type': `text`, 'class': `form-control`, 'name': `closing_date`, 'id': `closing_date`, 'value': data.closing_date, 'maxlength': `10` }));
		div1.appendChild(div);
		form.appendChild(div1);
		let opeBalRow = cTag('div', { 'id': `opeBalRow` });
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS6 columnSM5 columnMD4`, 'align': `right` });
		label = cTag('label', { 'for': `opening_balance` });
		label.innerHTML = 'Opening Balance';
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM7 columnMD8`, 'align': `left` });
		div.appendChild(cTag('input', { 'autocomplete': `off`, 'type': `text`, 'class': `form-control pricefield`, 'name': `opening_balance`, 'id': `opening_balance`, 'value': data.opening_balance, 'maxlength': `8` }));
		div1.appendChild(div);
		opeBalRow.appendChild(div1);
		form.appendChild(opeBalRow);
		form.appendChild(cTag('input', { 'type': `hidden`, 'name': `ledger_id`, 'value': data.ledger_id }));
		popupContent.appendChild(form);

		popup_dialog600('Ledger Information', popupContent, 'Save', saveLedgerForm);

		document.querySelector("#account_type").focus();

		setValidPrice();
		date_picker('#opening_date');
		date_picker('#closing_date');

		if (document.querySelectorAll(".showNewInputOrSelect")) {
			document.querySelectorAll(".showNewInputOrSelect").forEach(oneClassObj => {
				oneClassObj.addEventListener('click', function (event) {

					let parentDivObj = event.target.closest('.input-group');
					let dropdown = parentDivObj.querySelector('select');
					let input = parentDivObj.querySelector('input');
					let iTagObj = parentDivObj.querySelector('i');

					showNewInputOrSelect(event);

					if (iTagObj.className.includes('fa-plus')) {
						if (['groups_id', 'groups_id1', 'groups_id2', 'groups_id3'].includes(dropdown.name)) {
							let groupsIdNo = 0;
							if (dropdown.name.replace('groups_id', '') != '') {
								groupsIdNo = parseInt(dropdown.name.replace('groups_id', ''));
							}
							setSonGroupsOpt('', groupsIdNo);
						}
					}
				});
			});
		}
	}
	return true;

	function checkVisibility(event) {
		const node = event.target;
		const voucherCheckboxes = [...document.querySelectorAll('input[name="visible_on[]"][type="checkbox"]')];
		if (node.value === "0") {
			if (node.checked) {
				voucherCheckboxes.forEach(item => {
					if (item !== node) {
						item.checked = false;
						item.disabled = true;
					}
				})
			}
			else {
				voucherCheckboxes.forEach(item => {
					if (item !== node) {
						item.checked = false;
						item.disabled = false;
					}
				})
			}
		} else {
			//check if all vouchers are selected or not
			const uncheckedCheckboxes = voucherCheckboxes.filter(checkbox => {
				if (checkbox.value !== '0' && !checkbox.checked) return true;
				else return false;
			})
			if (uncheckedCheckboxes.length === 0) voucherCheckboxes[0].checked = true;
			else voucherCheckboxes[0].checked = false;
		}
	}
}

function setGroupsOpt(preLetter = '') {
	let account_type = parseInt(document.querySelector("#" + preLetter + "account_type").value);
	if (isNaN(account_type)) { account_type = 0; }

	let groups_id = parseInt(document.querySelector("#" + preLetter + "groups_id").value);
	if (isNaN(groups_id)) { groups_id = 0; }

	let select = document.querySelector('#' + preLetter + 'groups_id');
	select.innerHTML = '';
	let option = cTag('option', { 'value': `0` });
	option.innerHTML = 'Select Groups Name';
	select.appendChild(option);

	if (document.querySelector("#opeBalRow")) {
		if ([1, 2, 3].includes(account_type)) {
			document.querySelector("#opeBalRow").style.display = 'block';
		}
		else {
			document.querySelector("#opeBalRow").style.display = 'none';
			document.querySelector('#opening_balance').value = 0;
		}
	}

	if (account_type > 0) {
		const url = "/Accounts/setGroupsOpt";
		const data = { "account_type": account_type };

		fetchData(afterFetch, url, data);
		function afterFetch(data) {
			if (data.login != '') { window.location = '/' + data.login; }
			else {
				setOptions(select, data.groupsOpt, 1, 0);
				if (groups_id > 0) {
					checkSetSelectValue(preLetter + 'groups_id', groups_id);
				}
			}
		}
	}
}

function setSonGroupsOpt(preLetter = '', fromSonNo = 0) {
	fromSonNo = parseInt(fromSonNo);
	let nextSonNo = 1;
	if (isNaN(fromSonNo) || fromSonNo === 0) { fromSonNo = ''; }
	else { nextSonNo++; }
	let groups_id = parseInt(document.querySelector("#" + preLetter + "groups_id" + fromSonNo).value);
	if (isNaN(groups_id)) { groups_id = 0; }

	let select = document.querySelector('#' + preLetter + 'groups_id' + nextSonNo);
	select.innerHTML = '';
	let option = cTag('option', { 'value': `0` });
	option.innerHTML = 'Son Group' + nextSonNo;
	select.appendChild(option);

	if (groups_id > 0) {
		let sonGroupsId = parseInt(select.value);
		if (isNaN(sonGroupsId)) { sonGroupsId = 0; }

		const url = "/Accounts/setSonGroupsOpt/" + groups_id + '/1';
		const data = {};
		fetchData(afterFetch, url, data);
		function afterFetch(data) {
			setOptions(select, data.sonGroupOpt, 1, 0);
			if (sonGroupsId > 0) {
				checkSetSelectValue(preLetter + 'groups_id' + nextSonNo, sonGroupsId);
			}
		}
	}
}

function checkSetSelectValue(idName, defaultVal) {

	let valueExists = 0;
	let optionList = document.querySelectorAll("#" + idName + " option");
	optionList.forEach(option => {
		if (option.value == defaultVal) {
			valueExists++;
		}
	});

	if (valueExists > 0) {
		document.getElementById(idName).value = defaultVal;
	}
}

function saveLedgerForm(hidePopup) {
	let errorObj = document.getElementById('error_ledger');
	errorObj.innerHTML = '';
	if (document.querySelector("#account_type").value == 0) {
		errorObj.innerHTML = 'Missing Group Name';
		document.querySelector("#account_type").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if (document.querySelector("#groups_id").value == 0 && document.querySelector("#groups_name").value == '') {
		errorObj.innerHTML = 'Missing Groups Name';
		document.querySelector("#groups_id").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if (document.querySelector("#name").value == '') {
		errorObj.innerHTML = 'Missing Ledger Name';
		document.querySelector("#name").focus();
		return false;
	}
	errorObj.innerHTML = '';
	if (document.querySelector("#debit").value == '') {
		errorObj.innerHTML = 'Missing Debit';
		document.querySelector("#debit").focus();
		return false;
	}

	let visibleOnCheckedCount = 0;
	const voucherCheckboxes = [...document.querySelectorAll('input[name="visible_on[]"][type="checkbox"]')];
	voucherCheckboxes.forEach(item => {
		if (item.checked) {
			visibleOnCheckedCount++
		}
	})

	let errorObj1 = document.getElementById('error_visibleOn');
	errorObj1.innerHTML = '';
	if (visibleOnCheckedCount === 0) {
		errorObj1.innerHTML = 'Missing Visible On';
		voucherCheckboxes[0].focus();
		return false;
	}
	errorObj.innerHTML = '';
	errorObj1.innerHTML = '';

	const saveButton = document.querySelector(".btnmodel");
	btnEnableDisable(saveButton, Translate('Saving...'), true);

	const url = "/Accounts/AJsaveLedger/";
	const data = serialize('#frmLedger');

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		if (data.login != '') { window.location = '/' + data.login; }
		else if (data.savemsg != 'error') {
			if (document.querySelector("#chartContainer")) {
				location.reload();
			}
			else {
				ledgerData();
			}
			hidePopup();
		}
		else {
			errorObj.innerHTML = data.message;
		}
		btnEnableDisable(saveButton, Translate('Save'), false);
	}
	return false;
}

export function filter_Accounts_ledger() {

}
export function loadTableRows_Accounts_ledger() {

}

//================ledgerView============
function ledgerView() {
	fetchData(afterFetch, "/Accounts/AJ_ledgerview_moreInfo", { ledger_id: segment3 });
	// afterFetch({spid:0,id:3})
	function afterFetch(data) {
		let div2, div1, div, label, span, button, th;
		const mainSection = cTag('div', { 'class': `innerContainer`, 'style': `background: #fff` });
		div2 = cTag('div', { 'class': `flexSpaBetRow` });
		div1 = cTag('div', { 'class': `columnSM6` });
		div = cTag('div', { 'class': `customInfoGrid` });
		label = cTag('label');
		label.innerHTML = 'Account Type: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = `${accountTypes(data.account_type)}`;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Group Name: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = `${data.groupsName}`;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Account Name: ';
		div.appendChild(label);
		span = cTag('span');
		const h4 = cTag('h4', { 'id': `ledgerName` });
		h4.innerHTML = data.name;
		span.appendChild(h4);
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Visible On: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = data.visible_on.split(',').map(vaoucherID => voucherTypes(vaoucherID)).join(', ') || voucherTypes(0);;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Debit: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = `${debitCredits(data.debit)}`;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Opening Date: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = data.opening_date;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Closing Date: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = data.closing_date;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Opening Balance: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = `TK${number_format(data.openingBalance)}`;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Debit Total: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = `TK${number_format(data.incomeTotal - data.openingBalance)}`;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Credit Total: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = `TK${number_format(data.expenseTotal)}`;
		div.appendChild(span);
		label = cTag('label');
		label.innerHTML = 'Closing Balance: ';
		div.appendChild(label);
		span = cTag('span');
		span.innerHTML = `TK${data.incomeTotal - data.expenseTotal}`;
		div.appendChild(span);
		div1.appendChild(div);
		div2.appendChild(div1);
		div = cTag('div', { 'class': `columnSM6` });
		div.appendChild(cTag('div', { 'id': `chartContainer`, 'style': `height: 350px; width: 100%` }));
		div2.appendChild(div);
		mainSection.appendChild(div2);
		div1 = cTag('div', { 'class': `flexSpaBetRow` });
		div = cTag('div', { 'class': `columnSM12`, 'style': 'display:flex;gap:7px' });
		button = cTag('button', { 'type': `button`, 'class': `btn defaultButton`, 'click': () => AJgetLedgerPopup(segment3) });
		button.appendChild(cTag('i', { 'class': `fa fa-edit` }));
		button.append(' Change Information');
		div.appendChild(button);
		button = cTag('button', { 'class': `btn createButton`, 'click': () => AJgetLedgerPopup(0), 'title': `Create New Ledger` });
		button.appendChild(cTag('i', { 'class': `fa fa-plus` }));
		button.append(' New Ledger');
		div.appendChild(button);
		button = cTag('button', { 'class': `btn defaultButton`, 'click': () => window.location = '/Accounts/ledger/', 'title': `Back to List` });
		button.appendChild(cTag('i', { 'class': `fa fa-list` }));
		button.append(' Back to List');
		div.appendChild(button);
		div1.appendChild(div);
		mainSection.appendChild(div1);

		if (data.sonLedIdsData.length > 0) {
			let div2, div1, div, th;
			const div3 = cTag('div', { 'class': `widget`, 'style': 'margin-top:20px' });
			div2 = cTag('div', { 'class': `widget-header` });
				div1 = cTag('div', { 'class': `row` });
					div = cTag('div', { 'class': `columnSM12`, 'style': `position:relative;` });
						const h3 = cTag('h3');
						h3.innerHTML = `All Son Ledger of ${data.name}`;
					div.appendChild(h3);
				div1.appendChild(div);
			div2.appendChild(div1);
			div3.appendChild(div2);
			div2 = cTag('div', { 'class': `widget-content padding0` });
			div1 = cTag('div', { 'class': `row` });
			div = cTag('div', { 'class': `columnSM12`, 'style': `position:relative;` });
			const table = cTag('table', { 'class': `columnMD12 table-bordered table-striped table-condensed cf listing` });
			const thead = cTag('thead', { 'class': `cf` });
			const tr = cTag('tr');
			th = cTag('th', { 'align': `left`, 'width': `5%` });
			th.innerHTML = 'Group Name';
			tr.appendChild(th);
			th = cTag('th', { 'align': `left` });
			th.innerHTML = 'Groups Name';
			tr.appendChild(th);
			th = cTag('th', { 'align': `left` });
			th.innerHTML = 'Ledger Name';
			tr.appendChild(th);
			th = cTag('th', { 'align': `left`, 'width': `20%` });
			th.innerHTML = 'Visible On';
			tr.appendChild(th);
			th = cTag('th', { 'align': `center`, 'width': `5%` });
			th.innerHTML = 'Debit';
			tr.appendChild(th);
			th = cTag('th', { 'align': `center`, 'width': `5%` });
			th.innerHTML = 'Credit';
			tr.appendChild(th);
			th = cTag('th', { 'align': `center`, 'width': `5%` });
			th.innerHTML = 'Create Voucher';
			tr.appendChild(th);
			th = cTag('th', { 'align': `center`, 'width': `8%` });
			th.innerHTML = 'Action';
			tr.appendChild(th);
			thead.appendChild(tr);
			table.appendChild(thead);
			const tbody = cTag('tbody');

			data.sonLedIdsData.forEach(item => {
				let [accountType, groupsName, name, visible_on, debit, credit, ledger_publish, closing_date, editPer, hidePer, arrow, ledger_id, ledger_count] = item;

				let cls = '';
				let a;
				if (ledger_publish === 2) cls = 'lightyellowrow';

				const editIcon = document.createDocumentFragment();
				a = cTag('a', { 'href': `/Accounts/ledgerView/${ledger_id}`, 'title': `View this Ledger Information` });
				a.appendChild(cTag('i', { 'class': `fa fa fa-info-circle txt18` }));
				editIcon.appendChild(a);

				if (editPer === 1) {
					editIcon.append('   ');
					a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJgetLedgerPopup(0, ledger_id), 'title': `Change this Account Information` });
					a.appendChild(cTag('i', { 'class': `fa fa-edit txt18` }));
					editIcon.appendChild(a);
				}
				if (hidePer === 1) {
					editIcon.append('   ');
					a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJarchive_Popup('ledger', ledger_id, name, ledger_publish) });
					if (ledger_publish === 2) {
						a.setAttribute('title', `Active this Ledger`);
						a.appendChild(cTag('i', { 'class': `fa fa-arrow-circle-left errormsg txt18` }));
					}
					else {
						a.setAttribute('title', `Archive this Ledger`);
						a.appendChild(cTag('i', { 'class': `fa fa-remove txt18` }));
					}
					editIcon.appendChild(a);
				}

				if (closing_date > 0 && closing_date < strtotime(date('Y-m-d'))) cls = 'lightpinkrow';

				let ledger_countStr = 'Yes';
				if (ledger_count > 0) ledger_countStr = 'No';

				let td;
				const tr = cTag('tr', { 'class': cls });
				td = cTag('td', { 'nowrap': ``, 'data-title': `Group Name`, 'align': `left` });
				td.innerHTML = accountType;
				tr.appendChild(td);
				td = cTag('td', { 'nowrap': ``, 'data-title': `Groups Name`, 'align': `left` });
				td.innerHTML = groupsName;
				tr.appendChild(td);
				td = cTag('td', { 'data-title': `Ledger Name`, 'align': `left` });
				if (arrow > 0) {
					td.innerHTML = '&emsp;';
					td.appendChild(cTag('img', { 'src': `/assets/images/Accounts/sontabarrow.png`, 'alt': `Parent` }));
				}
				else td.appendChild(cTag('img', { 'src': `/assets/images/Accounts/firstarrow.png`, 'alt': `Parent` }));

				if (segment3 == ledger_id) {
					const span = cTag('b');
					span.innerHTML = name;
					td.append(span, ` (${ledger_id})`);
				}
				else td.append(name, ` (${ledger_id})`);
				tr.appendChild(td);
				td = cTag('td', { 'data-title': `Visible On`, 'align': `left` });
				td.innerHTML = visible_on.split(',').map(vaoucherID => voucherTypes(vaoucherID)).join(', ') || voucherTypes(0);;
				tr.appendChild(td);
				td = cTag('td', { 'nowrap': ``, 'data-title': `Debit`, 'align': `center` });
				td.innerHTML = debit;
				tr.appendChild(td);
				td = cTag('td', { 'nowrap': ``, 'data-title': `Credit`, 'align': `center` });
				td.innerHTML = credit;
				tr.appendChild(td);
				td = cTag('td', { 'nowrap': ``, 'data-title': `Create Voucher`, 'align': `center` });
				td.innerHTML = ledger_countStr;
				tr.appendChild(td);
				td = cTag('td', { 'nowrap': ``, 'data-title': 'Action', 'align': `center` });
				td.appendChild(editIcon);
				tr.appendChild(td);
				tbody.appendChild(tr);
			})
			table.appendChild(tbody);
			div.appendChild(table);
			div1.appendChild(div);
			div2.appendChild(div1);
			div3.appendChild(div2);
			mainSection.appendChild(div3);
		}

		const div4 = cTag('div', { 'style': `margin-top: 20px` });
		div4.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/ledgerView/${segment3}` }));
		div4.appendChild(cTag('input', { 'type': `hidden`, 'name': `page`, 'id': `page`, 'value': `1` }));
		div4.appendChild(cTag('input', { 'type': `hidden`, 'name': `rowHeight`, 'id': `rowHeight`, 'value': `34` }));
		div4.appendChild(cTag('input', { 'type': `hidden`, 'name': `totalTableRows`, 'id': `totalTableRows`, 'value': `0` }));
		div4.appendChild(cTag('input', { 'type': `hidden`, 'name': `publicsShow`, 'id': `table_idValue`, 'value': `3` }));
		const div3 = cTag('div', { 'class': `flexSpaBetRow widget-header` });
		div2 = cTag('div', { 'class': `columnMD4` });
			div1 = cTag('div', { 'class': `flexSpaBetRow` });
				div = cTag('div', { 'class': `columnXS8` });
					const h3 = cTag('h3');
					h3.innerHTML = 'Voucher All Transaction';
				div.appendChild(h3);
			div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS4`, 'align': `right` });
		button = cTag('button', { 'class': `btn defaultButton`, 'click': printLedger, 'title': `Print ${data.name}` });
		button.appendChild(cTag('i', { 'class': `fa fa-print` }));
		button.append(' Print');
		div.appendChild(button);
		div.appendChild(cTag('input', { type: "hidden", id: "reportInfo", value: `Account Type: ${accountTypes[data.account_type]}, &emsp; Parent Account: ${data.parentName}, &emsp;  Account Name: ${data.name}` }));
		div1.appendChild(div);
		div2.appendChild(div1);
		div3.appendChild(div2);
		div1 = cTag('div', { 'class': `columnMD3` });
		div = cTag('div', { 'class': `input-group` });
		const date_range = cTag('input', { 'type': `text`, 'name': `date_range`, 'id': `date_range`, 'class': `form-control`, 'keypress': listenToEnterKey(filter_Accounts_ledgerView), 'placeholder': `From to Todate`, 'value': `2023-05-09 - 2023-05-10` });
		daterange_picker_dialog(date_range);
		div.appendChild(date_range);
		span = cTag('span', { 'class': `input-group-addon cursor`, 'click': filter_Accounts_ledgerView, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Search by date range` });
		span.appendChild(cTag('i', { 'class': `fa fa-search` }));
		div.appendChild(span);
		div1.appendChild(div);
		div3.appendChild(div1);
		div4.appendChild(div3);
		div1 = cTag('div');
		div = cTag('div', { 'id': `no-more-tables` });
		const table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
		const thead = cTag('thead', { 'class': `cf` });
		const tr = cTag('tr');
		th = cTag('th', { 'align': `left`, 'width': `3%` });
		th.innerHTML = 'SL#';
		tr.appendChild(th);
		th = cTag('th', { 'align': `left`, 'width': `5%` });
		th.innerHTML = 'Voucher Date';
		tr.appendChild(th);
		th = cTag('th', { 'align': `left`, 'width': `20%` });
		th.innerHTML = 'Particulars';
		tr.appendChild(th);
		th = cTag('th', { 'align': `left` });
		th.innerHTML = 'Narration';
		tr.appendChild(th);
		th = cTag('th', { 'align': `left`, 'width': `7%` });
		th.innerHTML = 'Voucher No.';
		tr.appendChild(th);
		th = cTag('th', { 'align': `center`, 'width': `3%` });
		th.innerHTML = 'Qty';
		tr.appendChild(th);
		th = cTag('th', { 'align': `center`, 'width': `8%` });
		th.innerHTML = 'Unit Price';
		tr.appendChild(th);
		th = cTag('th', { 'align': `center`, 'width': `8%` });
		th.innerHTML = 'Debit';
		tr.appendChild(th);
		th = cTag('th', { 'align': `center`, 'width': `8%` });
		th.innerHTML = 'Credit';
		tr.appendChild(th);
		th = cTag('th', { 'align': `center`, 'width': `8%` });
		th.innerHTML = 'Balance';
		tr.appendChild(th);
		thead.appendChild(tr);
		table.appendChild(thead);
		table.appendChild(cTag('tbody', { 'id': `tableRows` }));
		div.appendChild(table);
		div1.appendChild(div);
		div4.appendChild(div1);
		mainSection.appendChild(div4);
		addPaginationRowFlex(mainSection);
		accountsPageTemplate(mainSection, 'Ledger Details Information');
		filter_Accounts_ledgerView();
		addCustomeEventListener('loadTable', loadTableRows_Accounts_ledgerView);
	}
}

export function filter_Accounts_ledgerView() {
	let limit = document.querySelector('#limit').value;
	let page = 1;
	document.querySelector("#page").value = page;

	let jsonData = {};
	jsonData['ledger_id'] = document.querySelector('#table_idValue').value;
	jsonData['date_range'] = document.querySelector('#date_range').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;

	const url = "/Accounts/AJgetHPageLedger/filter";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		document.querySelector("#totalTableRows").value = data.totalRows;
		// document.querySelector("#tableRows").innerHTML = data.tableRows;
		loadLedgerViewTableRows(data.tableRows);
		onClickPagination();
	}
}

export function loadTableRows_Accounts_ledgerView() {
	let jsonData = {};
	jsonData['ledger_id'] = document.querySelector('#table_idValue').value;
	jsonData['date_range'] = document.querySelector('#date_range').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = document.querySelector('#limit').value;
	jsonData['page'] = document.querySelector('#page').value;

	const url = "/Accounts/AJgetHPageLedger";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		// document.querySelector("#tableRows").innerHTML = data.tableRows;
		loadLedgerViewTableRows(data.tableRows);
		onClickPagination();
	}
}

function loadLedgerViewTableRows(tableRows) {
	const node = document.getElementById('tableRows');
	node.innerHTML = '';

	let balance = tableRows.balance;
	let totDeb, totCre, totQty;
	totDeb = totCre = totQty = 0;
	let sl = tableRows.starting_val;
	sl++;

	// tableRows.voucherData.forEach((item,index)=>{
	let tr, td, strong;
	tr = cTag('tr', { 'class': `lightpinkrow` });
	td = cTag('td', { 'data-title': `SL`, 'align': `left` });
	td.innerHTML = sl;
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Voucher#`, 'align': `right`, 'colspan': `4` });
	strong = cTag('strong');
	strong.innerHTML = 'Opening Balance';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Qty`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = totQty;
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Voucher#`, 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = `TK${number_format(totDeb)}`;
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = `TK${number_format(totCre)}`;
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = `TK${number_format(balance)}`;
	td.appendChild(strong);
	tr.appendChild(td);
	node.appendChild(tr);

	if (tableRows.voucherData.length > 0) {
		tableRows.voucherData.forEach(item => {
			let debit, credit;
			if (item[1] == 1) debit = item[2];
			else credit = item[2];

			let td;
			const tr = cTag('tr');
			tr.append('$voucherCols');
			td = cTag('td', { 'data-title': `Narration`, 'align': `left` });
			td.innerHTML = item[0];
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Voucher#`, 'align': `left` });
			td.innerHTML = '$vt$voucher_no $printIcon';
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
			td.innerHTML = item[3];
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
			td.innerHTML = `TK${number_format(item[4])}`;
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
			td.innerHTML = `TK${number_format(debit)}`;
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
			td.innerHTML = `TK${number_format(credit)}`;
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Balance`, 'align': `right` });
			td.innerHTML = '$currency".$this->taka_format($balance)."';
			tr.appendChild(td);
			node.appendChild(tr);
		})
	}
	else {
		tr = cTag('tr');
		td = cTag('td', { 'colspan': `8`, 'data-title': `No data found` });
		td.innerHTML = 'There is no data found';
		tr.appendChild(td);
		node.appendChild(tr);
	}
	tr = cTag('tr', { 'class': `lightpinkrow` });
	td = cTag('td', { 'data-title': 'Grand Total', 'align': `right`, 'colspan': `5` });
	strong = cTag('strong');
	strong.innerHTML = 'Grand Total: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Qty`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '0';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': 'Unit Price', 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '$TK0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '$TK0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = 'TK0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	node.appendChild(tr);
	// })
}

function printLedger() {
	const title = stripslashes(document.querySelector("#ledgerName").innerHTML) + ' Transactions (' + stripslashes(document.querySelector("#fromtodata").innerHTML) + ')';
	let filterby = '';
	let date_range = document.querySelector("#date_range").value;
	if (date_range != '') {
		filterby += 'Date Range: ' + date_range;
	}
	_print(title, filterby);
}

//============Manage Voucher1============//
function receiptVoucher() { voucher('Receipt Voucher', filter_Accounts_receiptVoucher, loadTableRows_Accounts_receiptVoucher) }
function paymentVoucher() { voucher('Payment Voucher', filter_Accounts_paymentVoucher, loadTableRows_Accounts_paymentVoucher) }
function journalVoucher() { voucher('Journal Voucher', filter_Accounts_journalVoucher, loadTableRows_Accounts_journalVoucher) }
function contraVoucher() { voucher('Contra Voucher', filter_Accounts_contraVoucher, loadTableRows_Accounts_contraVoucher) }
function purchaseVoucher() { voucher('Purchase Voucher', filter_Accounts_purchaseVoucher, loadTableRows_Accounts_purchaseVoucher) }
function salesVoucher() { voucher('Sales Voucher', filter_Accounts_salesVoucher, loadTableRows_Accounts_salesVoucher) }

function voucher(voucher_type, filterHandler, loadTableRowsHandler) {
	const voucherTypeData = { 'Receipt Voucher': 1, 'Payment Voucher': 2, 'Journal Voucher': 3, 'Contra Voucher': 4, 'Purchase Voucher': 5, 'Sales Voucher': 6 };

	let div2, div, div1, th, button, input, select, options;
	const mainSection = cTag('div', { class: "innerContainer", style: "background: #fff;" });
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/${segment2}` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `page`, 'id': `page`, 'value': `1` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `rowHeight`, 'id': `rowHeight`, 'value': `34` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `totalTableRows`, 'id': `totalTableRows`, 'value': `0` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `fvoucher_type`, 'id': `fvoucher_type`, 'value': parseInt(voucherTypeData[voucher_type] || 1) }));

		div2 = cTag('div', { 'class': `flexSpaBetRow` });
			div = cTag('div', { 'class': `columnXS6 columnSM4 columnMD2` });
				if (['Purchase Voucher', 'Sales Voucher'].includes(voucher_type)) {
					button = cTag('button', { 'class': `btn createButton`, 'click': () => AJgetVoucher2Popup(0), 'title': `Create New ${voucher_type}` });
				}
				else{
					button = cTag('button', { 'class': `btn createButton`, 'click': () => AJgetVoucher1Popup(0), 'title': `Create New ${voucher_type}` });
				}
				button.append(cTag('i', { 'class': `fa fa-plus` }), ` ${voucher_type}`);
			div.appendChild(button);
		div2.appendChild(div);

			div = cTag('div', { 'class': `columnXS6 columnSM4 columnMD2` });
				select = cTag('select', { class: "form-control", name: "fpublish", id: "fpublish" });
				select.addEventListener('change', filterHandler);
					options = { 0: 'Pending+Approved', 1: 'Pending', 2: 'Approved', 3: 'Archived' }
					setOptions(select, options, 1, 0);
			div.appendChild(select);
		div2.appendChild(div);
			div = cTag('div', { 'class': `columnXS6 columnSM4 columnMD2` });
				select = cTag('select', { 'name': `faccount_type`, 'id': `faccount_type`, 'class': `form-control` });
				select.addEventListener('change', () => {
					filterHandler();
					setGroupsOpt('f');
				});
					options = { 0: 'All Account Type', 1: 'Assets', 2: 'Liabilities', 3: 'Equity', 4: 'Revenue/Income', 5: 'Expenses', 6: 'Purchase' }
					setOptions(select, options, 1, 1);
			div.appendChild(select);
		div2.appendChild(div);

			div = cTag('div', { 'class': `columnXS6 columnSM4 columnMD2` });
				select = cTag('select', { 'name': `fgroups_id`, 'id': `fgroups_id`, 'class': `form-control`, 'change': filterHandler });
					let option = cTag('option', { 'value': '' });
					option.innerHTML = 'All Groups';
				select.appendChild(option);
			div.appendChild(select);
		div2.appendChild(div);

			div = cTag('div', { 'class': `columnXS6 columnSM4 columnMD2` });
			div.appendChild(cTag('input', { 'name': `ledgerName`, 'id': `ledgerName`, 'placeholder': `Ledgers Name...`, 'class': `ledgerName form-control` }));
			div.appendChild(cTag('input', { 'type': `hidden`, 'name': `fledger_id`, 'id': `fledger_id`, 'value': `0` }));
		div2.appendChild(div);

			div1 = cTag('div', { 'class': `columnXS6 columnSM4 columnMD2` });
				div = cTag('div', { 'class': `input-group` });
				div.appendChild(cTag('input', { 'type': `text`, 'placeholder': `V#/Naration...`, 'value': ``, 'id': `keyword_search`, 'name': `keyword_search`, 'class': `form-control`, 'keypress': listenToEnterKey(filterHandler), 'maxlength': `50` }));
					const span = cTag('span', { 'class': `input-group-addon cursor`, 'click': filterHandler, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Search Information` });
					span.appendChild(cTag('i', { 'class': `fa fa-search` }));
				div.appendChild(span);
			div1.appendChild(div);
		div2.appendChild(div1);

	mainSection.appendChild(div2);

	div2 = cTag('div', { 'class': `flexSpaBetRow` });
		div1 = cTag('div', { 'class': `columnXS12` });
			div = cTag('div', { 'id': `no-more-tables` });
				const table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
					const thead = cTag('thead', { 'class': `cf` });
						const tr = cTag('tr');
							th = cTag('th', { 'align': `left`, 'width': `10%` });
							th.innerHTML = 'Created Date';
						tr.appendChild(th);
							th = cTag('th', { 'align': `left`, 'width': `10%` });
							th.innerHTML = 'Voucher No.';
						tr.appendChild(th);
							th = cTag('th', { 'align': `left` });
							th.innerHTML = 'Ledger Name';
						tr.appendChild(th);
							th = cTag('th', { 'align': `center`, 'width': `20%` });
							th.innerHTML = 'Narration';
						tr.appendChild(th);
							if (['Purchase Voucher', 'Sales Voucher'].includes(voucher_type)) {
								th = cTag('th', { 'align': `left` });
								th.innerHTML = 'Qty';
								tr.appendChild(th);
								th = cTag('th', { 'align': `center`, 'width': `20%` });
								th.innerHTML = 'Unit Price';
								tr.appendChild(th);
							}
							th = cTag('th', { 'align': `center`, 'width': `10%` });
							th.innerHTML = 'Debit';
						tr.appendChild(th);
							th = cTag('th', { 'align': `center`, 'width': `10%` });
							th.innerHTML = 'Credit';
						tr.appendChild(th);
					thead.appendChild(tr);
				table.appendChild(thead);
				table.appendChild(cTag('tbody', { 'id': `tableRows` }));

			div.appendChild(table);
		div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	mainSection.appendChild(div);

	addPaginationRowFlex(mainSection);
	accountsPageTemplate(mainSection);
	filterHandler();
	addCustomeEventListener('loadTable', loadTableRowsHandler);
	AJautoComplete_Ledger();
}

export function filter_Accounts_receiptVoucher() { filter_Voucher1(); }
export function loadTableRows_Accounts_receiptVoucher() { loadTableRows_Voucher1(); }

export function filter_Accounts_paymentVoucher() { filter_Voucher1(); }
export function loadTableRows_Accounts_paymentVoucher() { loadTableRows_Voucher1(); }

export function filter_Accounts_journalVoucher() { filter_Voucher1(); }
export function loadTableRows_Accounts_journalVoucher() { loadTableRows_Voucher1(); }

export function filter_Accounts_contraVoucher() { filter_Voucher1(); }
export function loadTableRows_Accounts_contraVoucher() { loadTableRows_Voucher1(); }

export function filter_Voucher1() {
	let limit = document.querySelector('#limit').value;
	let page = 1;
	document.querySelector("#page").value = page;
	let fgroups_id = document.querySelector('#fgroups_id').value;
	let fledger_id = document.querySelector('#fledger_id').value;
	let jsonData = {};
	jsonData['fpublish'] = document.querySelector('#fpublish').value;
	jsonData['fvoucher_type'] = document.querySelector('#fvoucher_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = fgroups_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;

	const url = "/Accounts/AJgetPage_Voucher1/filter";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		document.querySelector("#totalTableRows").value = data.totalRows;

		loadVoucherTableRows({ tableRows: data.tableRows, section: segment2 });

		onClickPagination();
	}
}

export function loadTableRows_Voucher1() {
	let jsonData = {};
	jsonData['fpublish'] = document.querySelector('#fpublish').value;
	jsonData['fvoucher_type'] = document.querySelector('#fvoucher_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = document.querySelector('#fgroups_id').value;
	jsonData['fledger_id'] = document.querySelector('#fledger_id').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = document.querySelector('#limit').value;
	jsonData['page'] = document.querySelector('#page').value;

	const url = "/Accounts/AJgetPage_Voucher1";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		loadVoucherTableRows({ tableRows: data.tableRows, section: segment2 });
		onClickPagination();
	}
}

function loadVoucherTableRows({ tableRows, section }) {
	const node = document.getElementById('tableRows');
	let voucherData = tableRows[0];
	let voucherListData = tableRows[1];
	let LedIds = tableRows[2];

	node.innerHTML = '';
	let tr, td, a, strong, totalDebit, totalCredit, voucher_no, voucher_date, voucher_publish, voucharListInfo, rowspan, ledger_id, narration, debit_credit, amount, debit, credit;
	totalDebit = totalCredit = 0;

	for (const voucher_id in voucherData) {
		const voucherOneRow = voucherData[voucher_id];

		voucher_no = voucherOneRow[0];
		voucher_date = voucherOneRow[1];
		voucher_publish = voucherOneRow[2];

		const oneVListDatas = voucherListData[voucher_id];
		rowspan = oneVListDatas.length;

		oneVListDatas.forEach((oneVListData, index) => {
			ledger_id = LedIds[oneVListData['0']] || '';
			narration = oneVListData['1'];
			debit_credit = oneVListData['2'];
			amount = oneVListData['3'];

			debit = credit = 0;
			if (debit_credit > 0) { debit = amount; }
			else { credit = amount; }

			totalDebit += debit;
			totalCredit += credit;

			tr = cTag('tr');
			if (index === 0) {
				td = cTag('td', { 'rowspan': rowspan, 'data-title': `Created Date`, 'align': `center` });
				td.innerHTML = DBDateToViewDate(voucher_date);
				tr.appendChild(td);

				td = cTag('td', { 'data-title': `Voucher#`, 'nowrap': ``, 'align': `center`, 'rowspan': rowspan });
				td.innerHTML = voucher_no;

				const actionBtnContainer = cTag('div', { style: 'display:inline;margin-left:20px' });

				if (section !== "dayBook") {
					actionBtnContainer.setAttribute('style', 'display:flex;gap:10px;justify-content:center')
					if (voucher_publish == 2) {
						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Back to pending approve Voucher# ${voucher_no}`, 'voucher_publish', 1), 'title': `Back to Pending` });
						a.appendChild(cTag('i', { 'class': `fa fa-check txt18 txtgreen` }));
						actionBtnContainer.appendChild(a);
					}
					else if (voucher_publish == 1) {
						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Back to Active/Approve Voucher# ${voucher_no}`, 'voucher_publish', 2), 'title': `Back to Active/Approve` });
						a.appendChild(cTag('i', { 'class': `fa fa-arrow-circle-left txt18 txtred` }));
						actionBtnContainer.appendChild(a);

						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Archive Voucher# ${voucher_no}`, 'voucher_publish', 0), 'title': `Back to Archive` });
						a.appendChild(cTag('i', { 'class': `fa fa-trash-o txt18 txtred` }));
						actionBtnContainer.appendChild(a);
					}
					else {
						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Back to pending approve Voucher# ${voucher_no}`, 'voucher_publish', 1), 'title': `Back to Pending` });
						a.appendChild(cTag('i', { 'class': `fa fa-trash-o txt18 txtred` }));
						actionBtnContainer.appendChild(a);
					}

					a = cTag('a', { 'href': `javascript:void(0);`, 'title': `Change this Voucher Information` });
					if (['purchaseVoucher', 'salesVoucher'].includes(section)) a.addEventListener('click', () => AJgetVoucher2Popup(voucher_id, 2))
					else a.addEventListener('click', () => AJgetVoucher1Popup(voucher_id))
					a.appendChild(cTag('i', { 'class': `fa fa-edit txt18` }));
					actionBtnContainer.appendChild(a);
				}
				a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => printbyuri(`/Accounts/voucherPrint/${voucher_id}`), 'title': `Print this Voucher Information` });
				a.appendChild(cTag('i', { 'class': `fa fa-print txt18` }));
				actionBtnContainer.appendChild(a);
				td.appendChild(actionBtnContainer);
				tr.appendChild(td);
			}
			td = cTag('td', { 'data-title': `Ledger Name`, 'align': `left` });
			td.innerHTML = ledger_id;
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Narration`, 'align': `left` });
			td.innerHTML = narration;
			tr.appendChild(td);

			let debitStr = '';
			if (debit != 0) {
				debitStr = debit.toFixed(2);
			}

			let creditStr = '';
			if (credit != 0) {
				creditStr = credit.toFixed(2);
			}

			td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
			td.innerHTML = debitStr;
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
			td.innerHTML = creditStr;
			tr.appendChild(td);
			node.appendChild(tr);

		})
	}
	tr = cTag('tr', { 'class': `lightpinkrow` });
	td = cTag('td', { 'data-title': 'Grand Total', 'align': `right`, 'colspan': `4` });
	strong = cTag('strong');
	strong.innerHTML = 'Grand Total: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = totalDebit;
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = totalCredit;
	td.appendChild(strong);
	tr.appendChild(td);
	node.appendChild(tr);
}

function AJgetVoucher1Popup(voucher_id) {
	let voucher_type = parseInt(document.getElementById("fvoucher_type").value);
	if (isNaN(voucher_type) || voucher_type == 0) { voucher_type = 1; }

	const url = "/Accounts/AJgetVoucher1Popup";
	const data = { "voucher_id": voucher_id, voucher_type: voucher_type }

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		let lastCreRows = [];
		let div1, div, label, span, h3, ul, a;
		const popupContent = cTag('div');
		popupContent.appendChild(cTag('div', { 'id': `error_Voucher1`, 'class': `errormsg` }));
		const form = cTag('form', { 'action': `#`, 'name': `frmVoucher`, 'id': `frmVoucher`, 'enctype': `multipart/form-data`, 'method': `post`, 'accept-charset': `utf-8` });
		div1 = cTag('div', { 'class': `flexStartRow` });
		div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `right` });
		label = cTag('label', { 'for': `voucher_no` });
		label.append('Voucher No.');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM3`, 'align': `left` });
		div.appendChild(cTag('input', { 'type': `hidden`, 'name': `voucher_type`, 'value': data.voucher_type }));
		div.appendChild(cTag('input', { 'type': `hidden`, 'name': `voucher_id`, 'value': data.voucher_id }));
		div.appendChild(cTag('input', { 'readonly': `true`, 'required': `required`, 'type': `text`, 'class': `form-control`, 'name': `voucher_no`, 'id': `voucher_no`, 'value': data.voucher_no, 'maxlength': `11` }));
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `right` });
		label = cTag('label', { 'for': `voucher_date` });
		label.append('Voucher Date');
		span = cTag('span', { 'class': `required` });
		span.innerHTML = '*';
		label.appendChild(span);
		div.appendChild(label);
		div1.appendChild(div);
		div = cTag('div', { 'class': `columnXS6 columnSM3`, 'align': `left` });
		div.appendChild(cTag('input', { 'readonly': `true`, 'required': `required`, 'type': `text`, 'class': `form-control`, 'name': `voucher_date`, 'id': `voucher_date`, 'value': data.voucher_date, 'maxlength': `10` }));
		div1.appendChild(div);
		form.appendChild(div1);
		const div3 = cTag('div', { 'class': `flexStartRow` });
		const div2 = cTag('div', { 'class': `columnXS12`, 'align': `left` });
		const ul1 = cTag('ul', { 'class': `multiplerowlist`, 'style': 'list-style-type: none;' });
		const li1 = cTag('li', { 'class': `innerPage`, 'style': 'position:relative' });
			div1 = cTag('div', { 'class': `width100per borderbottom flexStartRow` });
				div = cTag('div', { 'class': `columnXS6 columnSM4` });
					h3 = cTag('h3');
					h3.innerHTML = 'Ledger Name';
				div.appendChild(h3);
			div1.appendChild(div);
				div = cTag('div', { 'class': `columnXS4 columnSM2` });
					h3 = cTag('h3');
					h3.innerHTML = 'Narration';
				div.appendChild(h3);
			div1.appendChild(div);
				div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
					h3 = cTag('h3');
					h3.innerHTML = 'Transaction';
				div.appendChild(h3);
			div1.appendChild(div);
				div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
					h3 = cTag('h3');
					h3.innerHTML = 'Debit';
				div.appendChild(h3);
			div1.appendChild(div);
				div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
					h3 = cTag('h3');
					h3.innerHTML = 'Credit';
				div.appendChild(h3);
			div1.appendChild(div);
		li1.appendChild(div1);
		ul = cTag('ul', { 'class': `multiplerowlist`, 'style': `position:relative;list-style-type: none;`, 'id': `vListDebit` });
		if (data.voucherLists.length > 0) {
			let lcsl = 0;
			let tsl = 0;
			data.voucherLists.forEach(function (key, value) {
				tsl++;
				let debit_credit = value[4];
				if (debit_credit == -1) {
					lastCreRows = value;
					lcsl = tsl;
				}
			});
			let sl = 0;
			data.voucherLists.forEach(function (vaoucher) {
				sl++;
				let voucher_list_id = vaoucher[0];
				let ledger_id = vaoucher[1];
				let ledgerName = vaoucher[2];
				let narration = vaoucher[3];
				let debit_credit = vaoucher[4];
				let amount = vaoucher[5];
				// let creSel = false;
				let debDis = false;
				let creDis = true;
				let debit = amount;
				let credit = 0;
				if (debit_credit == -1) {
					// creSel = true;
					debDis = true;
					creDis = false;
					debit = 0;
					credit = amount;
				}
				if (sl != lcsl) {
					let rowClass = ' lightgreenrow';
					if (sl % 2 == 0) { rowClass = ''; }

					let div, option;
					const li = cTag('li', { 'class': `width100per ${rowClass} flexStartRow` });
					div = cTag('div', { 'class': `columnXS6 columnSM3` });
					div.appendChild(cTag('input', { 'type': `text`, 'name': `ledgerName[]`, 'class': `form-control ledgerName`, 'value': ledgerName }));
					div.appendChild(cTag('input', { 'type': `hidden`, 'name': `voucher_list_id[]`, 'class': `voucher_list_id`, 'value': voucher_list_id }));
					div.appendChild(cTag('input', { 'type': `hidden`, 'name': `ledger_id[]`, 'class': `ledger_id`, 'value': ledger_id }));
					li.appendChild(div);
					div = cTag('div', { 'class': `columnXS6 columnSM3`, 'align': `left` });
					div.appendChild(cTag('input', { 'type': `text`, 'name': `narration[]`, 'class': `form-control narration`, 'placeholder': `Narration`, 'value': narration }));
					li.appendChild(div);
					div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
					const select = cTag('select', { 'required': `required`, 'class': `form-control debit_credit`, 'name': `debit_credit[]` });
					option = cTag('option', { 'value': `1` });
					option.innerHTML = 'Debit';
					select.appendChild(option);
					option = cTag('option', { 'value': `-1` });
					option.innerHTML = 'Credit';
					select.appendChild(option);
					select.value = debit_credit;
					div.appendChild(select);
					li.appendChild(div);
					div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
					div.appendChild(cTag('input', { 'type': `text`, 'name': `debit[]`, 'class': `form-control debit pricefield`, 'style': `display:${debDis ? 'none' : ''}`, 'placeholder': 'Amount', 'value': debit }));
					li.appendChild(div);
					div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
					div.appendChild(cTag('input', { 'type': `text`, 'name': `credit[]`, 'class': `form-control credit pricefield `, 'style': `display:${creDis ? 'none' : ''}`, 'placeholder': 'Amount', 'value': credit }));
					li.appendChild(div);
					ul.appendChild(li);
				}
			});
		}
		else {
			let div, option;
			const li = cTag('li', { 'class': `width100per lightgreenrow flexStartRow` });
				div = cTag('div', { 'class': `columnXS6 columnSM3` });
				div.appendChild(cTag('input', { 'type': `text`, 'name': `ledgerName[]`, 'class': `form-control ledgerName`, 'placeholder': `Ledger Name` }));
				div.appendChild(cTag('input', { 'type': `hidden`, 'name': `voucher_list_id[]`, 'class': `voucher_list_id`, 'value': `0` }));
				div.appendChild(cTag('input', { 'type': `hidden`, 'name': `ledger_id[]`, 'class': `ledger_id`, 'value': `0` }));
			li.appendChild(div);
				div = cTag('div', { 'class': `columnXS6 columnSM3`, 'align': `left` });
				div.appendChild(cTag('input', { 'type': `text`, 'name': `narration[]`, 'class': `form-control narration`, 'placeholder': `Narration`, 'value': `` }));
			li.appendChild(div);
				div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
					const select = cTag('select', { 'required': `required`, 'class': `form-control debit_credit`, 'name': `debit_credit[]` });
						option = cTag('option', { 'value': `1` });
						option.innerHTML = 'Debit';
					select.appendChild(option);
						option = cTag('option', { 'value': `-1` });
						option.innerHTML = 'Credit';
					select.appendChild(option);
				div.appendChild(select);
			li.appendChild(div);
				div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
				div.appendChild(cTag('input', { 'type': `text`, 'name': `debit[]`, 'class': `form-control debit pricefield`, 'placeholder': 'Amount', 'value': `0` }));
			li.appendChild(div);
				div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
				div.appendChild(cTag('input', { 'type': `text`, 'name': `credit[]`, 'class': `form-control credit pricefield`, 'placeholder': 'Amount', 'style': `display:none;`, 'value': `0` }));
			li.appendChild(div);
			ul.appendChild(li);
		}

		li1.appendChild(ul);
		div = cTag('div', { 'class': `addnewplusbotrig`, 'style': `top:50px;` });
		a = cTag('a', { 'href': `javascript:void(0);`, 'title': `Add More Voucher List` });
		a.addEventListener('click', () => addMoreVList('vListDebit'));
		a.appendChild(cTag('img', { 'align': `absmiddle`, 'alt': `Add More Voucher List`, 'title': `Add More Voucher List`, 'src': `/assets/images/Accounts/plus20x25.png` }));
		div.appendChild(a);
		li1.appendChild(div);

		if (data.voucherLists.length == 0 || lastCreRows.length > 0) {
			let voucher_list_id = 0;
			let ledger_id = 0;
			let ledgerName = '';
			let narration = '';
			let credit = 0;
			if (lastCreRows.length > 0) {
				voucher_list_id = lastCreRows[0];
				ledger_id = lastCreRows[1];
				ledgerName = lastCreRows[2];
				narration = lastCreRows[3];
				credit = lastCreRows[5];
			}
			let div, option;
			ul = cTag('ul', { 'class': `multiplerowlist`, 'style': `position:relative;list-style-type: none;`, 'id': `vListCredit` });
			const li = cTag('li', { 'class': `width100per lightgreenrow flexStartRow` });
			div = cTag('div', { 'class': `columnXS6 columnSM3` });
			div.appendChild(cTag('input', { 'type': `text`, 'name': `ledgerName[]`, 'class': `form-control ledgerName`, 'placeholder': `Ledger Name`, 'value': ledgerName }));
			div.appendChild(cTag('input', { 'type': `hidden`, 'name': `voucher_list_id[]`, 'class': `voucher_list_id`, 'value': voucher_list_id }));
			div.appendChild(cTag('input', { 'type': `hidden`, 'name': `ledger_id[]`, 'class': `ledger_id`, 'value': ledger_id }));
			li.appendChild(div);
			div = cTag('div', { 'class': `columnXS6 columnSM3`, 'align': `left` });
			div.appendChild(cTag('input', { 'type': `text`, 'name': `narration[]`, 'class': `form-control narration`, 'placeholder': `Narration`, 'value': narration }));
			li.appendChild(div);
			div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
			const select = cTag('select', { 'required': `required`, 'class': `form-control debit_credit`, 'name': `debit_credit[]` });
			option = cTag('option', { 'value': `1` });
			option.innerHTML = 'Debit';
			select.appendChild(option);
			option = cTag('option', { 'value': `-1`, 'selected': `true` });
			option.innerHTML = 'Credit';
			select.appendChild(option);
			div.appendChild(select);
			li.appendChild(div);
			div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
			div.appendChild(cTag('input', { 'type': `text`, 'name': `debit[]`, 'class': `form-control debit pricefield`, 'placeholder': 'Amount', 'style': `display:none;`, 'value': `0` }));
			li.appendChild(div);
			div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `left` });
			div.appendChild(cTag('input', { 'type': `text`, 'name': `credit[]`, 'class': `form-control credit pricefield`, 'placeholder': 'Amount', 'value': credit }));
			li.appendChild(div);
			ul.appendChild(li);
			li1.appendChild(ul);
			div = cTag('div', { 'class': `addnewplusbotrig`, 'style': `top:50px;` });
			a = cTag('a', { 'href': `javascript:void(0);`, 'title': `Add More Voucher List` });
			a.addEventListener('click', () => addMoreVList('vListCredit'));
			a.appendChild(cTag('img', { 'align': `absmiddle`, 'alt': `Add More Voucher List`, 'title': `Add More Voucher List`, 'src': `/assets/images/Accounts/plus20x25.png` }));
			div.appendChild(a);
			li1.appendChild(div);
		}

		div1 = cTag('div', { 'class': `width100per mtop10 ptop10 lightpinkrow flexStartRow` });
		div1.appendChild(cTag('input', { 'type': `hidden`, 'required': `required`, 'value': `Credit`, 'id': `adjustwith` }));
		div1.appendChild(cTag('div', { 'id': `error_voucherList`, 'class': `columnSM6 errormsg` }));
			div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `right` });
				h3 = cTag('h3');
				h3.innerHTML = 'Total:';
			div.appendChild(h3);
		div1.appendChild(div);
			div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `right` });
				h3 = cTag('h3', { 'id': `totDebit` });
				h3.innerHTML = '0.00';
			div.appendChild(h3);
		div1.appendChild(div);
			div = cTag('div', { 'class': `columnXS4 columnSM2`, 'align': `right` });
				h3 = cTag('h3', { 'id': `totCredit` });
				h3.innerHTML = '0.00';
			div.appendChild(h3);
		div1.appendChild(div);

		li1.appendChild(div1);
		ul1.appendChild(li1);
		div2.appendChild(ul1);
		div3.appendChild(div2);
		form.appendChild(div3);
		popupContent.appendChild(form);
		popup_dialog1000(data.voucherType + ' Voucher Information', popupContent, AJsaveVoucher1);

		setTimeout(function () {
			date_picker('#voucher_date', function (date) {
				let voucher_type = document.frmVoucher.voucher_type.value;
				getVoucherNo(date, voucher_type, voucher_id);
			});

			document.querySelector("#voucher_no").focus();
			document.querySelectorAll(".debit_credit").forEach(node => {
				node.addEventListener('change', function () {
					checkTransactionType(this);
				})
			})
			AJautoComplete_Ledger();
			setValidPrice();
			document.querySelectorAll(".debit, .credit").forEach(nodeElement => {
				nodeElement.addEventListener('keyup', calDebCre)
			});
			document.querySelector(".narration").addEventListener('keyup', function () {
				checkNarration(this);
			});

			calDebCre();
		}, 500);
	}
	return true;
}

function addMoreVList(ulIdName){
	let ulIdNameObj = document.querySelectorAll("ul#" + ulIdName + " li");
	let rowClass = ' lightgreenrow';
	if (ulIdNameObj.length % 2 != 0) { rowClass = ''; }
	let qtyLabel, UnitPriceLabel;
	if(['purchaseVoucher'].includes(segment2)){
		qtyLabel = 'Dollar';
		UnitPriceLabel = 'Currency Rate';
	}
	else {
		qtyLabel = 'Qty';
		UnitPriceLabel = 'Unit Price';
	}
	
	let li, row, div6, div, select, option;
	li = cTag('li',{'class':`row ${rowClass}`});
		div6 = cTag('div',{ 'class':`columnMD6`});
			row = cTag('div',{ 'class':`row`});
				div = cTag('div',{ 'class':`columnXS6` });
				div.appendChild(cTag('input',{ 'type':`text`,'name':`ledgerName[]`,'class':`form-control ledgerName`,'placeholder':`Ledger Name` }));
				div.appendChild(cTag('input',{ 'type':`hidden`,'name':`voucher_list_id[]`,'class':`voucher_list_id`,'value':`0` }));
				div.appendChild(cTag('input',{ 'type':`hidden`,'name':`ledger_id[]`,'class':`ledger_id`,'value':`0` }));
			row.appendChild(div);
				div = cTag('div',{ 'class':`columnXS6` });
				div.appendChild(cTag('input',{ 'type':`text`,'name':`narration[]`,'class':`form-control narration`,'placeholder':`Narration`,'value':`` }));
			row.appendChild(div);
		div6.appendChild(row);
	li.appendChild(div6);
		let columnXS3 = 'columnXS4';
		let columnXS2 = 'columnXS4';
		if(['purchaseVoucher', 'salesVoucher'].includes(segment2)){
			columnXS3 = 'columnXS3';
			columnXS2 = 'columnXS2';
		}

		div6 = cTag('div',{ 'class':`columnMD6`});
			row = cTag('div',{ 'class':`row`});
				div = cTag('div',{ 'class':`${columnXS3}`,'align':`left` });
					select = cTag('select',{ 'required':`required`,'class':`form-control debit_credit`,'name':`debit_credit[]` });
						option = cTag('option',{ 'value':`1` });
						option.innerHTML = 'Debit';
					select.appendChild(option);
						option = cTag('option',{ 'value':`-1` });
						option.innerHTML = 'Credit';
					select.appendChild(option);
				div.appendChild(select);
			row.appendChild(div);

			if(['purchaseVoucher', 'salesVoucher'].includes(segment2)){
					div = cTag('div',{ 'class':`${columnXS2}`,'align':`left` });
					div.appendChild(cTag('input', { 'type': `text`, 'name': `qty[]`, 'class': `form-control qty qtyfield`, 'placeholder': qtyLabel, 'value': ``}));
				row.appendChild(div);

					div = cTag('div',{ 'class':`${columnXS3}`,'align':`left` });
					div.appendChild(cTag('input', { 'type': `text`, 'name': `unit_price[]`, 'class': `form-control unit_price pricefield`, 'placeholder': UnitPriceLabel, 'value': `` }));
				row.appendChild(div);
			}
				div = cTag('div',{ 'class':`${columnXS2}`,'align':`left` });														
				div.appendChild(cTag('input',{ 'type':`text`,'name':`debit[]`,'class':`form-control debit pricefield`,'style':``,'placeholder':'Amount','value':`` }));
			row.appendChild(div);

				div = cTag('div',{ 'class':`${columnXS2}`,'align':`left` });
				div.appendChild(cTag('input',{ 'type':`text`,'name':`credit[]`,'class':`form-control credit pricefield `,'style':`display:none;`,'placeholder':'Amount','value':`` }));
			row.appendChild(div);
		div6.appendChild(row);
	li.appendChild(div6);

	document.querySelector("ul#" + ulIdName).appendChild(li);	

	document.querySelectorAll(".debit_credit").forEach(node => {
		node.addEventListener('change', function () {
			checkTransactionType(this);
		})
	})
	AJautoComplete_Ledger();
	setValidPrice();

	document.querySelectorAll(".qty, .unit_price, .debit, .credit").forEach(nodeElement => {
		nodeElement.addEventListener('keyup', calDebCre)
	});

	document.querySelector(".narration").addEventListener('keyup', function () {
		checkNarration(this);
	});
}

function AJsaveVoucher1(hidePopup) {
	let voucher_id = document.frmVoucher.voucher_id.value;
	let error_Voucher1 = document.getElementById('error_Voucher1');
	error_Voucher1.innerHTML = '';
	if (document.querySelector("#voucher_date").value == '') {
		error_Voucher1.innerHTML = 'Missing Voucher Date';
		document.querySelector("#voucher_date").focus();
		return false;
	}
	if (checkVList() == false) {
		return false;
	}

	let totDebit = parseFloat(document.querySelector('#totDebit').innerHTML);
	if (isNaN(totDebit) || totDebit == '') { totDebit = 0; }
	let totCredit = parseFloat(document.querySelector('#totCredit').innerHTML);
	if (isNaN(totCredit) || totCredit == '') { totCredit = 0; }

	let error_voucherList = document.getElementById('error_voucherList');
	error_voucherList.innerHTML = '';
	if (totDebit == 0) {
		error_voucherList.innerHTML = 'Total debit should be > 0';
		return false;
	}
	error_voucherList.innerHTML = '';
	if (totCredit == 0) {
		error_voucherList.innerHTML = 'Total credit should be > 0';
		return false;
	}
	error_voucherList.innerHTML = '';
	if (totDebit != totCredit) {
		error_voucherList.innerHTML = 'Total debit is not equal to total credit';
		return false;
	}

	error_voucherList.innerHTML = '';

	const saveButton = document.querySelector(".btnmodel");
	btnEnableDisable(saveButton, Translate('Saving...'), true);

	const url = "/Accounts/AJsaveVoucher1";
	const data = serialize("#frmVoucher");

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		if (data.savemsg != 'error') {
			filter_Voucher1();
			if (voucher_id == 0) {
				AJgetVoucher1Popup(0);
			}
			hidePopup();
		}
		else {
			error_voucherList.innerHTML = data.message;
		}
		btnEnableDisable(saveButton, Translate('Save'), false);
	}
	return false;
}

//============Manage Voucher2============//
export function filter_Accounts_purchaseVoucher() { filter_Voucher2(); }
export function loadTableRows_Accounts_purchaseVoucher() { loadTableRows_Voucher2(); }

export function filter_Accounts_salesVoucher() { filter_Voucher2(); }
export function loadTableRows_Accounts_salesVoucher() { loadTableRows_Voucher2(); }

export function filter_Voucher2() {
	let limit = document.querySelector('#limit').value;
	let page = 1;
	document.querySelector("#page").value = page;
	let fgroups_id = document.querySelector('#fgroups_id').value;
	let fledger_id = document.querySelector('#fledger_id').value;
	let jsonData = {};
	jsonData['fpublish'] = document.querySelector('#fpublish').value;
	jsonData['fvoucher_type'] = document.querySelector('#fvoucher_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = fgroups_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;

	const url = "/Accounts/AJgetPage_Voucher2/filter";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		document.querySelector("#totalTableRows").value = data.totalRows;

		loadVoucher2TableRows({ tableRows: data.tableRows, section: segment2 });

		onClickPagination();
	}
}

export function loadTableRows_Voucher2() {
	let jsonData = {};
	jsonData['fpublish'] = document.querySelector('#fpublish').value;
	jsonData['fvoucher_type'] = document.querySelector('#fvoucher_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = document.querySelector('#fgroups_id').value;
	jsonData['fledger_id'] = document.querySelector('#fledger_id').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = document.querySelector('#limit').value;
	jsonData['page'] = document.querySelector('#page').value;

	const url = "/Accounts/AJgetPage_Voucher2";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		loadVoucher2TableRows({ tableRows: data.tableRows, section: segment2 });
		onClickPagination();
	}
}

function loadVoucher2TableRows({ tableRows, section }) {
	const node = document.getElementById('tableRows');
	let voucherData = tableRows[0];
	let voucherListData = tableRows[1];
	let LedIds = tableRows[2];

	node.innerHTML = '';
	let tr, td, a, strong, totalDebit, totalCredit, voucher_no, voucher_date, voucher_publish, voucharListInfo, rowspan, ledger_id, narration, debit_credit, qty, unit_price, amount, debit, credit;
	totalDebit = totalCredit = 0;

	for (const voucher_id in voucherData) {
		const voucherOneRow = voucherData[voucher_id];

		voucher_no = voucherOneRow[0];
		voucher_date = voucherOneRow[1];
		voucher_publish = voucherOneRow[2];

		const oneVListDatas = voucherListData[voucher_id];
		rowspan = oneVListDatas.length;

		oneVListDatas.forEach((oneVListData, index) => {
			ledger_id = LedIds[oneVListData['0']] || '';
			narration = oneVListData['1'];
			debit_credit = oneVListData['2'];
			qty = oneVListData['3'];
			unit_price = oneVListData['4'];
			amount = oneVListData['5'];

			debit = credit = 0;
			if (debit_credit > 0) { debit = amount; }
			else { credit = amount; }

			totalDebit += debit;
			totalCredit += credit;

			tr = cTag('tr');
			if (index === 0) {
				td = cTag('td', { 'rowspan': rowspan, 'data-title': `Created Date`, 'align': `center` });
				td.innerHTML = DBDateToViewDate(voucher_date);
				tr.appendChild(td);

				td = cTag('td', { 'data-title': `Voucher#`, 'nowrap': ``, 'align': `center`, 'rowspan': rowspan });
				td.innerHTML = voucher_no;

				const actionBtnContainer = cTag('div', { style: 'display:inline;margin-left:20px' });

				if (section !== "dayBook") {
					actionBtnContainer.setAttribute('style', 'display:flex;gap:10px;justify-content:center')
					if (voucher_publish == 2) {
						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Back to pending approve Voucher# ${voucher_no}`, 'voucher_publish', 1), 'title': `Back to Pending` });
						a.appendChild(cTag('i', { 'class': `fa fa-check txt18 txtgreen` }));
						actionBtnContainer.appendChild(a);
					}
					else if (voucher_publish == 1) {
						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Back to Active/Approve Voucher# ${voucher_no}`, 'voucher_publish', 2), 'title': `Back to Active/Approve` });
						a.appendChild(cTag('i', { 'class': `fa fa-arrow-circle-left txt18 txtred` }));
						actionBtnContainer.appendChild(a);

						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Archive Voucher# ${voucher_no}`, 'voucher_publish', 0), 'title': `Back to Archive` });
						a.appendChild(cTag('i', { 'class': `fa fa-trash-o txt18 txtred` }));
						actionBtnContainer.appendChild(a);
					}
					else {
						a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => AJupdate_Data('voucher', voucher_id, `Back to pending approve Voucher# ${voucher_no}`, 'voucher_publish', 1), 'title': `Back to Pending` });
						a.appendChild(cTag('i', { 'class': `fa fa-trash-o txt18 txtred` }));
						actionBtnContainer.appendChild(a);
					}


					a = cTag('a', { 'href': `javascript:void(0);`, 'title': `Change this Voucher Information` });
					if (['purchaseVoucher', 'salesVoucher'].includes(section)) a.addEventListener('click', () => AJgetVoucher2Popup(voucher_id, 2))
					else a.addEventListener('click', () => AJgetVoucher1Popup(voucher_id))
					a.appendChild(cTag('i', { 'class': `fa fa-edit txt18` }));
					actionBtnContainer.appendChild(a);
				}
				a = cTag('a', { 'href': `javascript:void(0);`, 'click': () => printbyuri(`/Accounts/voucherPrint/${voucher_id}`), 'title': `Print this Voucher Information` });
				a.appendChild(cTag('i', { 'class': `fa fa-print txt18` }));
				actionBtnContainer.appendChild(a);
				td.appendChild(actionBtnContainer);
				tr.appendChild(td);
			}
			td = cTag('td', { 'data-title': `Ledger Name`, 'align': `left` });
			td.innerHTML = ledger_id;
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Narration`, 'align': `left` });
			td.innerHTML = narration;
			tr.appendChild(td);

			let qtyStr = '';
			if (qty != 0) {
				qtyStr = qty.toFixed(2);
			}

			let unit_priceStr = '';
			if (unit_price != 0) {
				unit_priceStr = unit_price.toFixed(2);
			}

			td = cTag('td', { 'data-title': `Qty`, 'align': `right` });
			td.innerHTML = qtyStr;
			tr.appendChild(td);

			td = cTag('td', { 'data-title': `Unit Price`, 'align': `right` });
			td.innerHTML = unit_priceStr;
			tr.appendChild(td);

			let debitStr = '';
			if (debit != 0) {
				debitStr = debit.toFixed(2);
			}

			let creditStr = '';
			if (credit != 0) {
				creditStr = credit.toFixed(2);
			}

			td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
			td.innerHTML = debitStr;
			tr.appendChild(td);
			td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
			td.innerHTML = creditStr;
			tr.appendChild(td);
			node.appendChild(tr);

		})
	}
	tr = cTag('tr', { 'class': `lightpinkrow` });
	td = cTag('td', { 'data-title': 'Grand Total', 'align': `right`, 'colspan': `6` });
	strong = cTag('strong');
	strong.innerHTML = 'Grand Total: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = totalDebit;
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = totalCredit;
	td.appendChild(strong);
	tr.appendChild(td);
	node.appendChild(tr);
}

function AJgetVoucher2Popup(voucher_id) {
	let voucher_type = parseInt(document.getElementById("fvoucher_type").value);
	if (isNaN(voucher_type) || voucher_type == 0) { voucher_type = 5; }

	const url = "/Accounts/AJgetVoucher2Popup";

	const data = { "voucher_id": voucher_id, voucher_type: voucher_type };

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		let lastCreRows = [];
		let pi_invoiceLabel, lc_phoneLabel, lc_dateLabel, qtyLabel, UnitPriceLabel;
		if (voucher_type == 6) {
			pi_invoiceLabel = 'Invoice';
			lc_phoneLabel = 'LC No.';
			lc_dateLabel = 'LC Date';
			qtyLabel = 'Dollar';
			UnitPriceLabel = 'Currency Rate';
		}
		else {
			pi_invoiceLabel = 'PI';
			lc_phoneLabel = 'LC No.';
			lc_dateLabel = 'LC Date';
			qtyLabel = 'Qty';
			UnitPriceLabel = 'Unit Price';
		}


		let row, ul, ul1, li1, div3, div2, div1, div, div6, label, span, h3, option, a;
		const popupContent = cTag('div');
		popupContent.appendChild(cTag('div', { 'id': `error_Voucher2`, 'class': `errormsg` }));
		const form = cTag('form', { 'action': `#`, 'name': `frmVoucher`, 'id': `frmVoucher`, 'onsubmit': `return AJsaveReceiptVoucher();`, 'enctype': `multipart/form-data`, 'method': `post`, 'accept-charset': `utf-8` });
			div3 = cTag('div', { 'class': `flexStartRow` });
				div2 = cTag('div', { 'class': `columnSM4` });
					div1 = cTag('div', { 'class': `form-group flexStartRow` });
						div = cTag('div', { 'class': `columnSM5`, 'align': `right` });
							label = cTag('label', { 'for': `voucher_no` });
							label.append('V. No.');
								span = cTag('span', { 'class': `required` });
								span.innerHTML = '*';
							label.appendChild(span);
						div.appendChild(label);
					div1.appendChild(div);

						div = cTag('div', { 'class': `columnSM7 paddingleft0 paddingright0`, 'align': `left` });
						div.appendChild(cTag('input', { 'type': `hidden`, 'name': `voucher_type`, 'value': data.voucher_type }));
						div.appendChild(cTag('input', { 'type': `hidden`, 'name': `voucher_id`, 'value': data.voucher_id }));
						div.appendChild(cTag('input', { 'readonly': `true`, 'required': `required`, 'type': `text`, 'class': `form-control`, 'name': `voucher_no`, 'id': `voucher_no`, 'value': data.voucher_no, 'maxlength': `11` }));
					div1.appendChild(div);
				div2.appendChild(div1);
					div1 = cTag('div', { 'class': `form-group flexStartRow` });
						div = cTag('div', { 'class': `columnSM5`, 'align': `right` });
							label = cTag('label', { 'for': `voucher_date` });
							label.append('V. Date');
								span = cTag('span', { 'class': `required` });
								span.innerHTML = '*';
							label.appendChild(span);
						div.appendChild(label);
					div1.appendChild(div);

						div = cTag('div', { 'class': `columnSM7 paddingleft0 paddingright0`, 'align': `left` });
						div.appendChild(cTag('input', { 'readonly': `true`, 'required': `required`, 'type': `text`, 'class': `form-control`, 'name': `voucher_date`, 'id': `voucher_date`, 'value': data.voucher_date, 'maxlength': `10` }));
					div1.appendChild(div);
				div2.appendChild(div1);
			div3.appendChild(div2);

				div2 = cTag('div', { 'class': `columnSM4` });
					div1 = cTag('div', { 'class': `form-group flexStartRow` });
						div = cTag('div', { 'class': `columnSM5`, 'align': `right` });
							label = cTag('label', { 'for': `pi_invoice_no` });
							label.append(`${pi_invoiceLabel} No.`);
								span = cTag('span', { 'class': `required` });
								span.innerHTML = '*';
							label.appendChild(span);
						div.appendChild(label);
					div1.appendChild(div);
						div = cTag('div', { 'class': `columnSM7 paddingleft0 paddingright0`, 'align': `left` });
						div.appendChild(cTag('input', { 'type': `text`, 'class': `form-control`, 'name': `pi_invoice_no`, 'id': `pi_invoice_no`, 'value': data.pi_invoice_no, 'maxlength': `30` }));
					div1.appendChild(div);
				div2.appendChild(div1);
				
					div1 = cTag('div', { 'class': `form-group flexStartRow` });
						div = cTag('div', { 'class': `columnSM5`, 'align': `right` });
							label = cTag('label', { 'for': `pi_invoice_date` });
							label.append(`${pi_invoiceLabel} Date`);
								span = cTag('span', { 'class': `required` });
								span.innerHTML = '*';
							label.appendChild(span);
						div.appendChild(label);
					div1.appendChild(div);
						div = cTag('div', { 'class': `columnSM7 paddingleft0 paddingright0`, 'align': `left` });
						div.appendChild(cTag('input', { 'type': `text`, 'class': `form-control`, 'name': `pi_invoice_date`, 'id': `pi_invoice_date`, 'value': data.pi_invoice_date, 'maxlength': `10` }));
					div1.appendChild(div);
				div2.appendChild(div1);
			div3.appendChild(div2);

				div2 = cTag('div', { 'class': `columnSM4` });
					div1 = cTag('div', { 'class': `form-group flexStartRow` });
						div = cTag('div', { 'class': `columnSM5`, 'align': `right` });
							label = cTag('label', { 'for': `lc_phone_no` });
							label.innerHTML = lc_phoneLabel;
						div.appendChild(label);
					div1.appendChild(div);
						div = cTag('div', { 'class': `columnSM7 paddingleft0`, 'align': `left` });
						div.appendChild(cTag('input', { 'type': `text`, 'class': `form-control`, 'name': `lc_phone_no`, 'id': `lc_phone_no`, 'value': data.lc_phone_no, 'maxlength': `200` }));
					div1.appendChild(div);
				div2.appendChild(div1);

					div1 = cTag('div', { 'class': `form-group flexStartRow` });
						div = cTag('div', { 'class': `columnSM5`, 'align': `right` });
							label = cTag('label', { 'for': `lc_date` });
							label.innerHTML = lc_dateLabel;
						div.appendChild(label);
					div1.appendChild(div);
						div = cTag('div', { 'class': `columnSM7 paddingleft0`, 'align': `left` });
						div.appendChild(cTag('input', { 'readonly': `true`, 'type': `text`, 'class': `form-control`, 'name': `lc_date`, 'id': `lc_date`, 'value': data.lc_date, 'maxlength': `10` }));
					div1.appendChild(div);
				div2.appendChild(div1);
			div3.appendChild(div2);			
		form.appendChild(div3);
			
			div3 = cTag('div',{ 'class':`flexStartRow` });
				div2 = cTag('div',{ 'class':`columnXS12`,'align':`left` });
					ul1 = cTag('ul',{ 'class':`multiplerowlist`, 'style':'list-style-type: none;' });
						li1 = cTag('li',{ 'class':`innerPage`,'style':'position:relative' });
							div1 = cTag('div',{ 'class':`row borderbottom` });
								div6 = cTag('div',{ 'class':`columnMD6`});
									row = cTag('div',{ 'class':`row`});
										div = cTag('div',{ 'class':`columnXS6` });
											h3 = cTag('h3');
											h3.innerHTML = 'Ledger Name';
										div.appendChild(h3);
									row.appendChild(div);
										div = cTag('div',{ 'class':`columnXS6` });
											h3 = cTag('h3');
											h3.innerHTML = 'Narration';
										div.appendChild(h3);
									row.appendChild(div);
								div6.appendChild(row);
							div1.appendChild(div6);
								div6 = cTag('div',{ 'class':`columnMD6`});
									row = cTag('div',{ 'class':`row`});
										div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
											h3 = cTag('h3');
											h3.innerHTML = 'Transaction';
										div.appendChild(h3);
									row.appendChild(div);
										div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
											h3 = cTag('h3');
											h3.innerHTML = 'Qty';
										div.appendChild(h3);
									row.appendChild(div);
										div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
											h3 = cTag('h3');
											h3.innerHTML = 'Unit Price';
										div.appendChild(h3);
									row.appendChild(div);
										div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
											h3 = cTag('h3');
											h3.innerHTML = 'Debit';
										div.appendChild(h3);
									row.appendChild(div);
										div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
											h3 = cTag('h3');
											h3.innerHTML = 'Credit';
										div.appendChild(h3);
									row.appendChild(div);
								div6.appendChild(row);
							div1.appendChild(div6);
						li1.appendChild(div1);
							ul = cTag('ul',{ 'class':`multiplerowlist`,'style':`position:relative;list-style-type: none;`,'id':`vListDebit` });
							let li, select, rowClass, sl;							
							sl=0;
							if(data.voucherLists.length>0){
								let lcsl=0;
								let tsl=0;
								data.voucherLists.forEach(function( key, value ) {
									tsl++;
									let debit_credit = value[4];
									if(debit_credit==-1){
										lastCreRows = value;
										lcsl = tsl;
									}
								});
								data.voucherLists.forEach(function( vaoucher) {
									sl++;
									let voucher_list_id = vaoucher[0];
									let ledger_id = vaoucher[1];
									let ledgerName = vaoucher[2];
									let narration = vaoucher[3];
									let debit_credit = vaoucher[4];
									let qty = vaoucher[5];
									let unit_price = vaoucher[6];
									let amount = vaoucher[7];
									// let creSel = false;
									let debDis = false;
									let creDis = true;
									let debit = amount;
									let credit = 0;
									if(debit_credit==-1){
										// creSel = true;
										debDis = true;
										creDis = false;
										debit = 0;
										credit = amount;
									}
									
									rowClass = ' lightgreenrow';
									if(sl%2==0){rowClass = '';}

									if(sl !=lcsl){

											li = cTag('li',{ 'class':`row ${rowClass}` });
												div6 = cTag('div',{ 'class':`columnMD6`});
													row = cTag('div',{ 'class':`row`});
														div = cTag('div',{ 'class':`columnXS6` });
														div.appendChild(cTag('input',{ 'type':`text`,'name':`ledgerName[]`,'class':`form-control ledgerName`,'value':ledgerName }));
														div.appendChild(cTag('input',{ 'type':`hidden`,'name':`voucher_list_id[]`,'class':`voucher_list_id`,'value':voucher_list_id }));
														div.appendChild(cTag('input',{ 'type':`hidden`,'name':`ledger_id[]`,'class':`ledger_id`,'value':ledger_id }));
													row.appendChild(div);	
														div = cTag('div',{ 'class':`columnXS6` });
														div.appendChild(cTag('input',{ 'type':`text`,'name':`narration[]`,'class':`form-control narration`,'placeholder':`Narration`,'value':narration }));
													row.appendChild(div);
												div6.appendChild(row);
											li.appendChild(div6);
												div6 = cTag('div',{ 'class':`columnMD6`});
													row = cTag('div',{ 'class':`row`});
														div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
															select = cTag('select',{ 'required':`required`,'class':`form-control debit_credit`,'name':`debit_credit[]` });
																option = cTag('option',{ 'value':`1` });
																option.innerHTML = 'Debit';
															select.appendChild(option);
																option = cTag('option',{ 'value':`-1` });
																option.innerHTML = 'Credit';
															select.appendChild(option);
															select.value = debit_credit;
														div.appendChild(select);
													row.appendChild(div);
														div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
														div.appendChild(cTag('input', { 'type': `text`, 'name': `qty[]`, 'class': `form-control qty qtyfield`, 'placeholder': qtyLabel, 'value': qty }));
													row.appendChild(div);
														div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
														div.appendChild(cTag('input', { 'type': `text`, 'name': `unit_price[]`, 'class': `form-control unit_price pricefield`, 'placeholder': UnitPriceLabel, 'value': unit_price }));
													row.appendChild(div);
														div = cTag('div',{ 'class':`columnXS2`,'align':`left` });														
														div.appendChild(cTag('input',{ 'type':`text`,'name':`debit[]`,'class':`form-control debit pricefield`,'style':`display:${debDis?'none':''}`,'placeholder':'Amount','value':debit }));
													row.appendChild(div);
														div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
														div.appendChild(cTag('input',{ 'type':`text`,'name':`credit[]`,'class':`form-control credit pricefield `,'style':`display:${creDis?'none':''}`,'placeholder':'Amount','value':credit }));
													row.appendChild(div);
												div6.appendChild(row);
											li.appendChild(div6);
										ul.appendChild(li);
									}
								});
							}
							else{
								sl++;
								rowClass = ' lightgreenrow';
								if(sl%2==0){rowClass = '';}

									li = cTag('li',{'class':`row ${rowClass}`});
										div6 = cTag('div',{ 'class':`columnMD6`});
											row = cTag('div',{ 'class':`row`});
												div = cTag('div',{ 'class':`columnXS6` });
												div.appendChild(cTag('input',{ 'type':`text`,'name':`ledgerName[]`,'class':`form-control ledgerName`,'placeholder':`Ledger Name` }));
												div.appendChild(cTag('input',{ 'type':`hidden`,'name':`voucher_list_id[]`,'class':`voucher_list_id`,'value':`0` }));
												div.appendChild(cTag('input',{ 'type':`hidden`,'name':`ledger_id[]`,'class':`ledger_id`,'value':`0` }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS6` });
												div.appendChild(cTag('input',{ 'type':`text`,'name':`narration[]`,'class':`form-control narration`,'placeholder':`Narration`,'value':`` }));
											row.appendChild(div);
										div6.appendChild(row);
									li.appendChild(div6);
										div6 = cTag('div',{ 'class':`columnMD6`});
											row = cTag('div',{ 'class':`row`});
												div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
													select = cTag('select',{ 'required':`required`,'class':`form-control debit_credit`,'name':`debit_credit[]` });
														option = cTag('option',{ 'value':`1` });
														option.innerHTML = 'Debit';
													select.appendChild(option);
														option = cTag('option',{ 'value':`-1` });
														option.innerHTML = 'Credit';
													select.appendChild(option);
												div.appendChild(select);
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
												div.appendChild(cTag('input', { 'type': `text`, 'name': `qty[]`, 'class': `form-control qty qtyfield`, 'placeholder': qtyLabel, 'value': `` }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
												div.appendChild(cTag('input', { 'type': `text`, 'name': `unit_price[]`, 'class': `form-control unit_price pricefield`, 'placeholder': UnitPriceLabel, 'value': `` }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS2`,'align':`left` });														
												div.appendChild(cTag('input',{ 'type':`text`,'name':`debit[]`,'class':`form-control debit pricefield`,'style':``,'placeholder':'Amount','value':`` }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
												div.appendChild(cTag('input',{ 'type':`text`,'name':`credit[]`,'class':`form-control credit pricefield `,'style':`display:none;`,'placeholder':'Amount','value':`` }));
											row.appendChild(div);
										div6.appendChild(row);
									li.appendChild(div6);
								ul.appendChild(li);
							}
							
							li1.appendChild(ul);
								div = cTag('div',{ 'class':`addnewplusbotrig`,'style':`top:50px;` });
									a = cTag('a',{ 'href':`javascript:void(0);`,'title':`Add More Voucher List`});
									a.addEventListener('click',()=>addMoreVList('vListDebit'));
									a.appendChild(cTag('img',{ 'align':`absmiddle`,'alt':`Add More Voucher List`,'title':`Add More Voucher List`,'src':`/assets/images/Accounts/plus20x25.png` }));
								div.appendChild(a);
							li1.appendChild(div);

							if(data.voucherLists.length==0 || lastCreRows.length>0){
								let voucher_list_id = 0;
								let ledger_id = 0;
								let ledgerName = '';
								let narration = '';
								let credit = 0;
								if(lastCreRows.length>0){
									voucher_list_id = lastCreRows[0];
									ledger_id = lastCreRows[1];
									ledgerName = lastCreRows[2];
									narration = lastCreRows[3];
									credit = lastCreRows[5];
								}
								
								sl++;
								rowClass = ' lightgreenrow';
								if(sl%2==0){rowClass = '';}

								ul = cTag('ul',{ 'class':`multiplerowlist`,'style':`position:relative;list-style-type: none;`,'id':`vListCredit` });
									li = cTag('li',{'class':`width100per ${rowClass} flexStartRow`});
										div6 = cTag('div',{ 'class':`columnMD6`});
											row = cTag('div',{ 'class':`row`});
												div = cTag('div',{ 'class':`columnXS6` });
												div.appendChild(cTag('input',{ 'type':`text`,'name':`ledgerName[]`,'class':`form-control ledgerName`,'placeholder':`Ledger Name`, value:ledgerName }));
												div.appendChild(cTag('input',{ 'type':`hidden`,'name':`voucher_list_id[]`,'class':`voucher_list_id`,'value':voucher_list_id }));
												div.appendChild(cTag('input',{ 'type':`hidden`,'name':`ledger_id[]`,'class':`ledger_id`,'value':ledger_id }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS6` });
												div.appendChild(cTag('input',{ 'type':`text`,'name':`narration[]`,'class':`form-control narration`,'placeholder':`Narration`,'value':narration}));
											row.appendChild(div);
										div6.appendChild(row);
									li.appendChild(div6);
										div6 = cTag('div',{ 'class':`columnMD6`});
											row = cTag('div',{ 'class':`row`});
												div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
													select = cTag('select',{ 'required':`required`,'class':`form-control debit_credit`,'name':`debit_credit[]` });
														option = cTag('option',{ 'value':`1` });
														option.innerHTML = 'Debit';
													select.appendChild(option);
														option = cTag('option',{ 'value':`-1` });
														option.innerHTML = 'Credit';
													select.appendChild(option);
												div.appendChild(select);
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
												div.appendChild(cTag('input', { 'type': `text`, 'name': `qty[]`, 'class': `form-control qty qtyfield`, 'placeholder': qtyLabel, 'value': `` }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS3`,'align':`left` });
												div.appendChild(cTag('input', { 'type': `text`, 'name': `unit_price[]`, 'class': `form-control unit_price pricefield`, 'placeholder': UnitPriceLabel, 'value': `` }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS2`,'align':`left` });														
												div.appendChild(cTag('input',{ 'type':`text`,'name':`debit[]`,'class':`form-control debit pricefield`,'style':`display:none;`,'placeholder':'Amount','value':`` }));
											row.appendChild(div);
												div = cTag('div',{ 'class':`columnXS2`,'align':`left` });
												div.appendChild(cTag('input',{ 'type':`text`,'name':`credit[]`,'class':`form-control credit pricefield `,'style':``,'placeholder':'Amount','value':credit }));
											row.appendChild(div);
										div6.appendChild(row);
									li.appendChild(div6);
								ul.appendChild(li);
								li1.appendChild(ul);
									div = cTag('div',{ 'class':`addnewplusbotrig`,'style':`top:50px;` });
										a = cTag('a',{ 'href':`javascript:void(0);`,'title':`Add More Voucher List`});
										a.addEventListener('click',()=>addMoreVList('vListCredit'));
										a.appendChild(cTag('img',{ 'align':`absmiddle`,'alt':`Add More Voucher List`,'title':`Add More Voucher List`,'src':`/assets/images/Accounts/plus20x25.png` }));
									div.appendChild(a);
								li1.appendChild(div);
							}

							div1 = cTag('div',{ 'class':`width100per mtop10 ptop10 lightpinkrow flexStartRow` });
							div1.appendChild(cTag('input',{ 'type':`hidden`,'required':`required`,'value':`Credit`,'id':`adjustwith` }));
							div1.appendChild(cTag('div',{ 'id':`error_voucherList`,'class':`columnSM6 errormsg` }));
								div = cTag('div',{ 'class':`columnXS4 columnSM2`,'align':`right` });
									h3 = cTag('h3');
									h3.innerHTML = 'Total:';
								div.appendChild(h3);
							div1.appendChild(div);
								div = cTag('div',{ 'class':`columnXS4 columnSM2`,'align':`right` });
									h3 = cTag('h3',{ 'id':`totDebit` });
									h3.innerHTML = '0.00';
								div.appendChild(h3);
							div1.appendChild(div);
								div = cTag('div',{ 'class':`columnXS4 columnSM2`,'align':`right` });
									h3 = cTag('h3',{ 'id':`totCredit` });
									h3.innerHTML = '0.00';
								div.appendChild(h3);
							div1.appendChild(div);

						li1.appendChild(div1);
					ul1.appendChild(li1);
				div2.appendChild(ul1);
			div3.appendChild(div2);
		form.appendChild(div3);
		popupContent.appendChild(form);

		popup_dialog1000(data.voucherType + ' Voucher Information', popupContent, AJsaveVoucher2);


		['voucher_date', 'pi_invoice_date', 'lc_date'].forEach(oneIdName => {
			date_picker('#' + oneIdName, function (date) {
				let voucher_type = document.frmVoucher.voucher_type.value;
				getVoucherNo(date, voucher_type, voucher_id);
			});
		});

		document.querySelector("#voucher_no").focus();
		document.querySelectorAll(".debit_credit").forEach(node => {
			node.addEventListener('change', function () {
				checkTransactionType(this);
			})
		})
		AJautoComplete_Ledger();

		setValidPrice();
		document.querySelectorAll(".qty, .unit_price, .debit, .credit").forEach(nodeElement => {
			nodeElement.addEventListener('keyup', calDebCre);
		});

		calDebCre();
		document.querySelectorAll(".narration").forEach(nodeElement => {
			nodeElement.addEventListener('keyup', function () {
				checkNarration(this);
			});
		});
	}
	return true;
}
function AJsaveVoucher2(hidePopup) {
	let voucher_id = document.frmVoucher.voucher_id.value;
	let voucher_type = document.frmVoucher.voucher_type.value;
	let error_Voucher2 = document.getElementById('error_Voucher2');
	error_Voucher2.innerHTML = '';
	if (document.querySelector("#voucher_date").value == '') {
		error_Voucher2.innerHTML = 'Missing Voucher Date';
		document.querySelector("#voucher_date").focus();
		return false;
	}
	if (checkVList() == false) {
		return false;
	}

	let totDebit = parseFloat(document.querySelector('#totDebit').innerHTML);
	if (isNaN(totDebit) || totDebit == '') { totDebit = 0; }
	let totCredit = parseFloat(document.querySelector('#totCredit').innerHTML);
	if (isNaN(totCredit) || totCredit == '') { totCredit = 0; }

	let error_voucherList = document.getElementById('error_voucherList');
	error_voucherList.innerHTML = '';
	if (totDebit == 0) {
		error_voucherList.innerHTML = 'Total debit should be > 0';
		return false;
	}
	error_voucherList.innerHTML = '';
	if (totCredit == 0) {
		error_voucherList.innerHTML = 'Total credit should be > 0';
		return false;
	}
	error_voucherList.innerHTML = '';
	if (totDebit != totCredit) {
		error_voucherList.innerHTML = 'Total debit is not equal to total credit';
		return false;
	}

	error_voucherList.innerHTML = '';

	const saveButton = document.querySelector(".btnmodel");
	btnEnableDisable(saveButton, Translate('Saving...'), true);

	const url = "/Accounts/AJsaveVoucher2";
	const data = serialize("#frmVoucher");

	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		if (data.savemsg != 'error') {
			filter_Voucher2();
			if (voucher_id == 0) {
				AJgetVoucher2Popup(0, voucher_type);
			}
			hidePopup();
		}
		else {
			error_voucherList.innerHTML = data.message;
		}
		btnEnableDisable(saveButton, Translate('Save'), false);
	}
	return false;
}

function checkVList() {
	let errorObj = document.getElementById('error_voucherList');
	let ledgerNames = document.getElementsByName('ledgerName[]');
	let ledger_ids = document.getElementsByName('ledger_id[]');
	let debit_credits = document.getElementsByName('debit_credit[]');
	let debits = document.getElementsByName('debit[]');
	let credits = document.getElementsByName('credit[]');
	if (ledgerNames.length > 0) {
		for (let l = 0; l < ledgerNames.length; l++) {
			let ledgerName = ledgerNames[l].value;
			let ledger_id = ledger_ids[l].value;
			let debit_credit = debit_credits[l].value;
			let debit = parseFloat(debits[l].value);
			if (isNaN(debit) || debit == '') { debit = 0; }
			let credit = parseFloat(credits[l].value);
			if (isNaN(credit) || credit == '') { credit = 0; }

			errorObj.innerHTML = '';
			if (ledger_id == 0 || ledgerName == '') {
				errorObj.innerHTML = 'Missing Ledger Name.';
				ledgerNames[l].focus();
				return false;
			}
			errorObj.innerHTML = '';
			if (debit_credit == 0) {
				errorObj.innerHTML = 'Missing Transaction.';
				debit_credits[l].focus();
				return false;
			}
			errorObj.innerHTML = '';
			if (debit_credit == 1 && debit == 0) {
				errorObj.innerHTML = 'Missing debit amount';
				debits[l].focus();
				return false;
			}
			errorObj.innerHTML = '';
			if (debit_credit == -1 && credit == 0) {
				errorObj.innerHTML = 'Missing credit amount';
				credits[l].focus();
				return false;
			}
		}
	}

	return true;
}
function calDebCre() {
	let vListDebit, vListCredit;
	vListDebit = document.querySelectorAll("ul#vListDebit li");
	vListCredit = document.querySelectorAll("ul#vListCredit li");

	if (vListDebit.length > 1 || vListCredit.length>1) {
		let voucher_id = document.frmVoucher.voucher_id.value;
		let startVal = 1;
		if (voucher_id > 0) { startVal = 1; }

		if(vListDebit.length>1){
			for (let l = startVal; l < vListDebit.length; l++) {
				if (document.querySelector("ul#vListDebit li:nth-child(" + (l + 1) + ") a.removeicon")) { }
				else {
					const a = cTag('a', { 'class': `removeicon`, 'href': `javascript:void(0);`, 'title': 'Remove this row' });
					a.appendChild(cTag('img', { 'align': `absmiddle`, 'alt': 'Remove this row', 'title': 'Remove this row', 'src': `/assets/images/Accounts/minus.gif` }));
					a.addEventListener('click', function () {
						this.parentNode.remove();
						calDebCre();
						return false;
					});
					document.querySelector(("ul#vListDebit li:nth-child(" + (l + 1) + ")")).appendChild(a);
				}
			}
		}
		if(vListCredit.length>1){
			for (let l = startVal; l < vListCredit.length; l++) {
				if (document.querySelector("ul#vListCredit li:nth-child(" + (l + 1) + ") a.removeicon")) { }
				else {
					const a = cTag('a', { 'class': `removeicon`, 'href': `javascript:void(0);`, 'title': 'Remove this row' });
					a.appendChild(cTag('img', { 'align': `absmiddle`, 'alt': 'Remove this row', 'title': 'Remove this row', 'src': `/assets/images/Accounts/minus.gif` }));
					a.addEventListener('click', function () {
						this.parentNode.remove();
						calDebCre();
						return false;
					});
					document.querySelector(("ul#vListCredit li:nth-child(" + (l + 1) + ")")).appendChild(a);
				}
			}
		}
	}

	let voucher_type = document.frmVoucher.voucher_type.value;
	let debit_credits = document.getElementsByName('debit_credit[]');
	let qtys = [];
	let unit_prices = [];
	if (voucher_type == 5 || voucher_type == 6) {
		qtys = document.getElementsByName('qty[]');
		unit_prices = document.getElementsByName('unit_price[]');
	}

	let debits = document.getElementsByName('debit[]');
	let credits = document.getElementsByName('credit[]');
	let totDeb = 0;
	let totCre = 0;
	if (debits.length > 0){
		let lastDebitIndex = -1;
		let lastCreditIndex = -1;
		for (let l = 0; l < debits.length; l++) {
			let debit_credit = debit_credits[l].value;
			if (debit_credit == '1') { lastDebitIndex = l; }
			else { lastCreditIndex = l; }
			//alert('debit_credit:'+debit_credit+', lastDebitIndex:'+lastDebitIndex);
			if (voucher_type == 5 || voucher_type == 6) {
				let qty = parseFloat(qtys[l].value);
				if (isNaN(qty) || qty == '') { qty = 0; }
				let unit_price = parseFloat(unit_prices[l].value);
				if (isNaN(unit_price) || unit_price == '') { unit_price = 0; }
				if(debit_credit == '1'){
					debits[l].value = qty * unit_price;
				}
				else{
					credits[l].value = qty * unit_price;
				}
			}

			let debit = parseFloat(debits[l].value);
			if (isNaN(debit) || debit == '') { debit = 0; }

			let credit = parseFloat(credits[l].value);
			if (isNaN(credit) || credit == '') { credit = 0; }

			totDeb += debit;
			totCre += credit;
		}

		let adjustwith = document.querySelector("#adjustwith").value;
		if (totDeb != totCre) {
			let diffVal = parseFloat(totDeb - totCre);
			if (diffVal != 0) {
				if (adjustwith == 'Debit') {
					if (lastDebitIndex >= 0) {
						let lastDebit = parseFloat(debits[lastDebitIndex].value);
						let adjustAmount = totDeb - lastDebit;
						//alert(adjustAmount);
						let newLastDebit = parseFloat(totCre - adjustAmount);
						debits[lastDebitIndex].value = newLastDebit;
						totDeb = totCre;
					}
				}
				else if (adjustwith == 'Credit') {
					if (lastCreditIndex >= 0) {
						let lastCredit = parseFloat(credits[lastCreditIndex].value);
						let adjustAmount = totCre - lastCredit;
						let newLastCredit = parseFloat(totDeb - adjustAmount);
						credits[lastCreditIndex].value = newLastCredit;
						totCre = totDeb;
					}
				}
			}
		}
	}
	document.querySelector('#totDebit').innerHTML = totDeb.toFixed(2);
	document.querySelector('#totCredit').innerHTML = totCre.toFixed(2);
}

function getVoucherNo(voucher_date, voucher_type, voucher_id) {
	if (voucher_date != '') {
		const url = "/Accounts/getVoucherNo/" + voucher_date + '/' + voucher_type + '/' + voucher_id;
		const data = { ajaxCall: 1 };

		fetchData(afterFetch, url, data);

		function afterFetch(data) {
			document.querySelector("#voucher_no").value = data;
		}
	}
}

function checkTransactionType(debCreObj) {
	let devCreVal = debCreObj.value;
	let parIdObj = debCreObj.parentNode.parentNode;
	let hideClass = '.credit';
	let showClass = '.debit';
	if (devCreVal === '-1') {
		hideClass = '.debit';
		showClass = '.credit';
	}
	parIdObj.querySelector(showClass).style.display = '';
	parIdObj.querySelector(hideClass).style.display = 'none';
}

function checkNarration(thisObj) {
	let narrationVal = thisObj.value;
	let narrObj = document.getElementsByClassName("narration");
	narrObj[parseInt(narrObj.length) - 1].value = narrationVal;
}

//============Reports:: dayBook=========//
function dayBook() {
	let div2, div, select, option, div1, tr, th;

	const mainSection = cTag('div', { 'class': `innerContainer`, 'style': `background: #fff` });
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/dayBook` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `page`, 'id': `page`, 'value': `1` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `rowHeight`, 'id': `rowHeight`, 'value': `34` }));
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `totalTableRows`, 'id': `totalTableRows`, 'value': `0` }));
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div = cTag('div', { 'class': `columnSM4 columnMD2` });
	div.appendChild(cTag('select', { 'name': `fvoucher_type`, 'id': `fvoucher_type`, 'class': `form-control`, 'change': filter_Accounts_dayBook }));
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM2 columnMD1 pleft0` });
	select = cTag('select', { 'name': `fpublish`, 'id': `fpublish`, 'class': `form-control`, 'change': filter_Accounts_dayBook });
	setOptions(select, { '=2': 'Approved', '=1': 'Pending', '=0': 'Archived', '>0': 'Pending+Approved' }, 1, 1)
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM2 columnMD1 pleft0 pright0` });
	select = cTag('select', { 'name': `faccount_type`, 'id': `faccount_type`, 'class': `form-control`, 'change': filter_Accounts_dayBook });
	setOptions(select, { 0: 'All Account Type', 1: 'Assets', 2: 'Liabilities', 3: 'Equity', 4: 'Revenue/Income', 5: 'Expenses', 6: 'Purchase' }, 1, 1)
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM4 columnMD2 pright0` });
	div.appendChild(cTag('select', { 'name': `fgroups_id`, 'id': `fgroups_id`, 'class': `form-control`, 'change': filter_Accounts_dayBook }));
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM4 columnMD2 pright0` });
	div.appendChild(cTag('select', { 'name': `fledger_id`, 'id': `fledger_id`, 'class': `form-control`, 'change': filter_Accounts_dayBook }));
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM4 columnMD2 pright0` });
	const dateRanger = cTag('input', { 'type': `text`, 'name': `date_range`, 'id': `date_range`, 'class': `form-control width180px floatright`, 'placeholder': `select daterange`, 'value': ``, 'keydown': listenToEnterKey(filter_Accounts_dayBook) });
	daterange_picker_dialog(dateRanger)
	div.appendChild(dateRanger);
	div2.appendChild(div);
	div1 = cTag('div', { 'class': `columnSM4 columnMD2 pbottom10` });
	div = cTag('div', { 'class': `input-group` });
	div.appendChild(cTag('input', { 'type': `text`, 'placeholder': `Voucher# / Narration`, 'value': ``, 'id': `keyword_search`, 'name': `keyword_search`, 'class': `form-control`, 'keydown': listenToEnterKey(filter_Accounts_dayBook), 'maxlength': `50` }));
	const span = cTag('span', { 'class': `input-group-addon cursor`, 'click': filter_Accounts_dayBook, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Voucher# / Narration` });
	span.appendChild(cTag('i', { 'class': `fa fa-search` }));
	div.appendChild(span);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div1 = cTag('div', { 'class': `columnSM12`, 'style': `position: relative` });
	div = cTag('div', { 'id': `no-more-tables` });
	const table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
	const thead = cTag('thead', { 'class': `cf` });
	tr = cTag('tr');
	th = cTag('th', { 'align': `left`, 'width': `10%` });
	th.innerHTML = 'Voucher Date';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left`, 'width': `10%` });
	th.innerHTML = 'Voucher No.';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left`, 'width': `25%` });
	th.innerHTML = 'Ledger Name';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center` });
	th.innerHTML = 'Narration';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center`, 'width': `10%` });
	th.innerHTML = 'Debit';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center`, 'width': `10%` });
	th.innerHTML = 'Credit';
	tr.appendChild(th);
	thead.appendChild(tr);
	table.appendChild(thead);
	table.appendChild(cTag('tbody', { 'id': `tableRows` }));
	div.appendChild(table);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	addPaginationRowFlex(mainSection);

	accountsPageTemplate(mainSection);
	filter_Accounts_dayBook();
	addCustomeEventListener('loadTable', loadTableRows_Accounts_dayBook);
}
export function filter_Accounts_dayBook() {
	let limit = document.querySelector('#limit').value;
	let page = 1;
	document.querySelector("#page").value = page;

	let fvoucher_type = document.querySelector('#fvoucher_type').value;
	let fgroups_id = document.querySelector('#fgroups_id').value;
	let fledger_id = document.querySelector('#fledger_id').value;
	let jsonData = {};
	jsonData['date_range'] = document.querySelector('#date_range').value;
	jsonData['fpublish'] = document.querySelector('#fpublish').value;
	jsonData['fvoucher_type'] = fvoucher_type;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = fgroups_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = limit;
	jsonData['page'] = page;

	const url = "/Accounts/AJgetPage_dayBook/filter";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		document.querySelector("#fvoucher_type").innerHTML = data.vouTypOpt;
		document.querySelector("#fgroups_id").innerHTML = data.subGroOpt;
		document.querySelector("#fledger_id").innerHTML = data.parLedOpt;

		document.querySelector('#fvoucher_type').value = fvoucher_type;
		document.querySelector('#fgroups_id').value = fgroups_id;
		document.querySelector('#fledger_id').value = fledger_id;
		document.querySelector("#totalTableRows").value = data.totalRows;

		loadVoucherTableRows({ tableRows: dummyVoucherTableRows, section: segment2 });

		onClickPagination();
	}
}

export function loadTableRows_Accounts_dayBook() {
	let jsonData = {};
	jsonData['date_range'] = document.querySelector('#date_range').value;
	jsonData['fpublish'] = document.querySelector('#fpublish').value;
	jsonData['fvoucher_type'] = document.querySelector('#fvoucher_type').value;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = document.querySelector('#fgroups_id').value;
	jsonData['fledger_id'] = document.querySelector('#fledger_id').value;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;
	jsonData['totalRows'] = document.querySelector('#totalTableRows').value;
	jsonData['rowHeight'] = document.querySelector('#rowHeight').value;
	jsonData['limit'] = document.querySelector('#limit').value;
	jsonData['page'] = document.querySelector('#page').value;

	const url = "/Accounts/AJgetPage_dayBook";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		loadVoucherTableRows({ tableRows: dummyVoucherTableRows, section: segment2 });
		onClickPagination();
	}
}

//============Reports:: ledgerReport============//
function ledgerReport() {
	let div2, div, select, div1, th;

	const mainSection = cTag('mainSection', { 'class': `innerContainer`, 'style': `background: #fff` });
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/ledgerReport` }));
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div = cTag('div', { 'class': `columnSM4 columnMD2` });
	select = cTag('select', { 'name': `fvoucher_type`, 'id': `fvoucher_type`, 'class': `form-control`, 'change': filter_Accounts_ledgerReport });
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM4 columnMD2 pleft0 pright0` });
	select = cTag('select', { 'name': `faccount_type`, 'id': `faccount_type`, 'class': `form-control`, 'change': filter_Accounts_ledgerReport });
	setOptions(select, { 0: 'All Account Type', 1: 'Assets', 2: 'Liabilities', 3: 'Equity', 4: 'Revenue/Income', 5: 'Expenses', 6: 'Purchase' }, 1, 1)
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM4 columnMD2 pright0` });
	select = cTag('select', { 'name': `fgroups_id`, 'id': `fgroups_id`, 'class': `form-control`, 'change': filter_Accounts_ledgerReport });
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM4 columnMD2 pright0` });
	select = cTag('select', { 'name': `fledger_id`, 'id': `fledger_id`, 'class': `form-control`, 'change': filter_Accounts_ledgerReport });
	div.appendChild(select);
	div2.appendChild(div);
	div = cTag('div', { 'class': `columnSM4 columnMD2 pright0` });
	const dateRange = cTag('input', { 'type': `text`, 'name': `date_range`, 'id': `date_range`, 'class': `form-control`, 'keypress': listenToEnterKey(filter_Accounts_ledgerReport), 'placeholder': `select daterange`, 'value': `` });
	daterange_picker_dialog(dateRange);
	div.appendChild(dateRange);
	div2.appendChild(div);
	div1 = cTag('div', { 'class': `columnSM4 columnMD2 pbottom10` });
	div = cTag('div', { 'class': `input-group` });
	div.appendChild(cTag('input', { 'type': `text`, 'placeholder': `Voucher# / Narration`, 'value': ``, 'id': `keyword_search`, 'name': `keyword_search`, 'class': `form-control`, 'keypress': listenToEnterKey(filter_Accounts_ledgerReport), 'maxlength': `50` }));
	const span = cTag('span', { 'class': `input-group-addon cursor`, 'click': filter_Accounts_ledgerReport, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Voucher# / Narration` });
	span.appendChild(cTag('i', { 'class': `fa fa-search` }));
	div.appendChild(span);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div1 = cTag('div', { 'class': `columnSM12`, 'style': `position: relative` });
	div = cTag('div', { 'id': `no-more-tables` });
	const table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
	const thead = cTag('thead', { 'class': `cf` });
	const tr = cTag('tr');
	th = cTag('th', { 'align': `left` });
	th.innerHTML = 'Particular';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left`, 'width': `10%` });
	th.innerHTML = 'Opening Balance';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center`, 'width': `10%` });
	th.innerHTML = 'Debit';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center`, 'width': `10%` });
	th.innerHTML = 'Credit';
	tr.appendChild(th);
	th = cTag('th', { 'align': `left`, 'width': `10%` });
	th.innerHTML = 'Closing Balance';
	tr.appendChild(th);
	thead.appendChild(tr);
	table.appendChild(thead);
	table.appendChild(cTag('tbody', { 'id': `tableRows` }));
	div.appendChild(table);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);

	accountsPageTemplate(mainSection);
	filter_Accounts_ledgerReport();
	addCustomeEventListener('loadTable', loadTableRows_Accounts_ledgerReport);
}

export function filter_Accounts_ledgerReport() {
	let fvoucher_type = document.querySelector('#fvoucher_type').value;
	let fgroups_id = document.querySelector('#fgroups_id').value;
	let fledger_id = document.querySelector('#fledger_id').value;
	let jsonData = {};
	jsonData['date_range'] = document.querySelector('#date_range').value;
	jsonData['fvoucher_type'] = fvoucher_type;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = fgroups_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;

	const url = "/Accounts/AJgetPage_ledgerReport";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		document.querySelector("#fvoucher_type").innerHTML = data.vouTypOpt;
		document.querySelector("#fgroups_id").innerHTML = data.subGroOpt;
		document.querySelector("#fledger_id").innerHTML = data.parLedOpt;

		document.querySelector('#fvoucher_type').value = fvoucher_type;
		document.querySelector('#fgroups_id').value = fgroups_id;
		document.querySelector('#fledger_id').value = fledger_id;
		loadLedgerReportTableRows(dummyLedgerReportTableRows);
	}
}
export function loadTableRows_Accounts_ledgerReport() {
	let fvoucher_type = document.querySelector('#fvoucher_type').value;
	let fgroups_id = document.querySelector('#fgroups_id').value;
	let fledger_id = document.querySelector('#fledger_id').value;
	let jsonData = {};
	jsonData['date_range'] = document.querySelector('#date_range').value;
	jsonData['fvoucher_type'] = fvoucher_type;
	jsonData['faccount_type'] = document.querySelector('#faccount_type').value;
	jsonData['fgroups_id'] = fgroups_id;
	jsonData['fledger_id'] = fledger_id;
	jsonData['keyword_search'] = document.querySelector('#keyword_search').value;

	const url = "/Accounts/AJgetPage_ledgerReport";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		loadLedgerReportTableRows(dummyLedgerReportTableRows);
	}
}

function loadLedgerReportTableRows(tableRows) {
	const table = document.getElementById('tableRows');
	table.innerHTML = '';
	let grand_opening_balance, grand_closing_balance, grand_debit, grand_credit, td, strong;
	grand_opening_balance = grand_closing_balance = grand_debit = grand_credit = 0;

	tableRows.forEach(report_info => {
		const report_row = getLedgerReportRow(report_info);
		table.appendChild(report_row);
		report_info.sub_reports.forEach(sub_report_info => {
			const sub_report_row = getLedgerReportRow({ ...sub_report_info, sub_report: true });
			table.appendChild(sub_report_row);
		})
	})
	const grandTotalRow = cTag('tr', { 'class': `lightpinkrow` });
	td = cTag('td', { 'data-title': 'Grand_Total', 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = 'Grand Total: ';
	td.appendChild(strong);
	grandTotalRow.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = number_format(grand_opening_balance);
	td.appendChild(strong);
	grandTotalRow.appendChild(td);
	td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = number_format(grand_debit);
	td.appendChild(strong);
	grandTotalRow.appendChild(td);
	td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = number_format(grand_credit);
	td.appendChild(strong);
	grandTotalRow.appendChild(td);
	td = cTag('td', { 'data-title': `Closing Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = number_format(grand_closing_balance);
	td.appendChild(strong);
	grandTotalRow.appendChild(td);
	table.appendChild(grandTotalRow);

	function getLedgerReportRow({ particular_name, opening_balance, debit, credit, sub_report }) {
		const closing_balance = opening_balance - (credit - debit);
		grand_opening_balance += opening_balance;
		grand_closing_balance += closing_balance;
		grand_debit += debit;
		grand_credit += credit;

		let td, strong;
		const row = cTag('tr');
		td = cTag('td', { 'data-title': `Particular Name`, 'align': `left` });
		strong = cTag('strong');
		if (sub_report) strong.innerHTML = `&emsp; &emsp;${particular_name}`;
		else strong.innerHTML = particular_name;
		td.appendChild(strong);
		row.appendChild(td);
		td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
		strong = cTag('strong');
		strong.innerHTML = number_format(opening_balance);
		td.appendChild(strong);
		row.appendChild(td);
		td = cTag('td', { 'data-title': `Debit`, 'align': `right` });
		strong = cTag('strong');
		strong.innerHTML = number_format(debit);
		td.appendChild(strong);
		row.appendChild(td);
		td = cTag('td', { 'data-title': `Credit`, 'align': `right` });
		strong = cTag('strong');
		strong.innerHTML = number_format(credit);
		td.appendChild(strong);
		row.appendChild(td);
		td = cTag('td', { 'data-title': `Closing Balance`, 'align': `right` });
		strong = cTag('strong');
		strong.innerHTML = number_format(closing_balance);
		td.appendChild(strong);
		row.appendChild(td);
		return row;
	}
}

//============Reports:: trialBalance============//
function trialBalance() {
	let div2, div, div1, span, tr, th;
	const mainSection = cTag('div', { class: "innerContainer", style: "background: #fff;" });
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/trialBalance` }));
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div = cTag('div', { 'class': `columnSM4 columnMD7 pright0` });
	const button = cTag('button', { 'class': `btn cursor p2x10 marginright15`, 'click': printTrialBalance, 'title': `Print Trial Balance` });
	button.appendChild(cTag('i', { 'class': `fa fa-print` }));
	button.append(' Print');
	div.appendChild(button);
	div2.appendChild(div);
	div1 = cTag('div', { 'class': `columnSM4 columnMD3 pbottom10 pright0` });
	div = cTag('div', { 'class': `input-group` });
	span = cTag('span', { 'class': `input-group-addon cursor`, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Views Type` });
	span.innerHTML = 'Views Type';
	div.appendChild(span);
	const select = cTag('select', { 'name': `fviews_type`, 'id': `fviews_type`, 'class': `form-control`, 'change': loadData_Accounts_trialBalance });
	setOptions(select, { '1': 'Group Only', '2': 'Group + Sub GROUP', '3': 'Details' }, 1, 1);
	div.appendChild(select);
	div1.appendChild(div);
	div2.appendChild(div1);
	div1 = cTag('div', { 'class': `columnSM4 columnMD2 pbottom10` });
	div = cTag('div', { 'class': `input-group` });
	const dateRanger = cTag('input', { 'type': `text`, 'placeholder': `Trial Balance Date`, 'value': `2023-05-12`, 'id': `fdate`, 'name': `fdate`, 'class': `form-control`, 'maxlength': `10` });
	daterange_picker_dialog(dateRanger);
	div.appendChild(dateRanger);
	span = cTag('span', { 'class': `input-group-addon cursor`, 'click': loadData_Accounts_trialBalance, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Search Trial Balance` });
	span.appendChild(cTag('i', { 'class': `fa fa-search` }));
	div.appendChild(span);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div1 = cTag('div', { 'class': `columnSM12`, 'style': `position: relative` });
	div = cTag('div', { 'id': `no-more-tables` });
	const table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
	const thead = cTag('thead', { 'class': `cf` });
	tr = cTag('tr');
	th = cTag('th', { 'rowspan': `2`, 'align': `left` });
	th.innerHTML = 'Particular';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center`, 'colspan': `2` });
	th.innerHTML = 'Amount (Balance)';
	tr.appendChild(th);
	thead.appendChild(tr);
	tr = cTag('tr');
	th = cTag('th', { 'align': `center`, 'width': `150` });
	th.innerHTML = 'Debit';
	tr.appendChild(th);
	th = cTag('th', { 'align': `center`, 'width': `150` });
	th.innerHTML = 'Credit';
	tr.appendChild(th);
	thead.appendChild(tr);
	table.appendChild(thead);
	table.appendChild(cTag('tbody', { 'id': `tableRows` }));
	div.appendChild(table);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);

	accountsPageTemplate(mainSection);
	loadData_Accounts_trialBalance();
}
const dummyTrialBalanceData = [
	{
		particular_name: 'Assets',
		debit: 764587,
		credit: 0,
		subdetails: [
			{
				particular_name: 'NON CURRENT ASSETS',
				debit: 764587,
				credit: 0
			}
		]
	},
	{
		particular_name: 'Liabilities',
		debit: 0,
		credit: -764587,
		subdetails: [
			{
				particular_name: 'CURRENT LIABILITES',
				debit: 0,
				credit: -64587
			},
			{
				particular_name: 'NON CURRENT LIABILITIES',
				debit: 0,
				credit: -700000
			}
		]
	},
]
export function loadData_Accounts_trialBalance() {
	let jsonData = {};
	jsonData['fdate'] = document.querySelector('#fdate').value;
	jsonData['fviews_type'] = document.querySelector('#fviews_type').value;

	const url = "/Accounts/AJgetPage_trialBalance";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		data.tableRows = dummyTrialBalanceData;

		let grand_debit, grand_credit;
		grand_debit = grand_credit = 0;

		const tableRows = document.querySelector("#tableRows");
		tableRows.innerHTML = '';

		data.tableRows.forEach(item => {
			grand_debit += item.debit;
			grand_credit += item.credit;
			const row = getTrialBalanceRow(item);
			tableRows.appendChild(row);
			item.subdetails.forEach(detailItem => {
				const detailRow = getTrialBalanceRow(detailItem);
				tableRows.appendChild(detailRow);
			})
		})

		let td, strong;
		const tr = cTag('tr', { 'class': `lightpinkrow` });
		td = cTag('td', { 'data-title': `Particular Name`, 'align': `right` });
		strong = cTag('strong');
		strong.innerHTML = 'Grand Total:';
		td.appendChild(strong);
		tr.appendChild(td);
		td = cTag('td', { 'nowrap': ``, 'data-title': `Debit`, 'align': `right` });
		strong = cTag('strong');
		strong.innerHTML = grand_debit;
		td.appendChild(strong);
		tr.appendChild(td);
		td = cTag('td', { 'nowrap': ``, 'data-title': `Credit`, 'align': `right` });
		strong = cTag('strong');
		strong.innerHTML = grand_credit;
		td.appendChild(strong);
		tr.appendChild(td);
	}
	function getTrialBalanceRow({ particular_name, debit, credit, subdetails }) {
		let subdetailRow = false;
		if (subdetails === undefined) subdetailRow = true;

		let td, strong;
		const row = cTag('tr', { 'class': subdetailRow ? 'lightbluerow' : 'lightgreenrow' });
		td = cTag('td', { 'data-title': `Particular Name`, 'align': `left` });
		if (subdetailRow) td.innerHTML = '&emsp;';
		strong = cTag('strong');
		strong.appendChild(cTag('img', { 'src': `/assets/images/Accounts/${subdetailRow ? 'sontabarrow' : 'firstarrow'}.png`, 'alt': `Group`, 'class': `mtop-6` }));
		strong.append(particular_name);
		td.appendChild(strong);
		row.appendChild(td);
		td = cTag('td', { 'nowrap': ``, 'data-title': `Debit`, 'align': `left` });
		strong = cTag('strong');
		if (subdetailRow) {
			strong.innerHTML = '&emsp;';
			strong.append(cTag('img', { 'src': `/assets/images/Accounts/sontabarrow.png`, 'alt': `Group`, 'class': `mtop-6` }));
			if (debit !== 0) strong.append(debit);
		}
		else if (debit !== 0) strong.innerHTML = number_format(debit);
		td.appendChild(strong);
		row.appendChild(td);
		td = cTag('td', { 'nowrap': ``, 'data-title': `Credit`, 'align': `left` });
		strong = cTag('strong');
		if (subdetailRow) {
			strong.innerHTML = '&emsp;';
			strong.append(cTag('img', { 'src': `/assets/images/Accounts/sontabarrow.png`, 'alt': `Group`, 'class': `mtop-6` }));
			if (credit !== 0) strong.append(credit);
		}
		else if (credit !== 0) strong.innerHTML = number_format(credit);
		td.appendChild(strong);
		row.appendChild(td);
		return row;
	}
}

function printTrialBalance() {
	let title = 'Trial Balance for ' + document.querySelector("#fdate").value;
	let filterby = '';
	if (document.querySelector("#fdate").value != '') {
		filterby += 'Trial Balance Date : ' + document.querySelector("#fdate").value;
	}
	_print(title, filterby)
}

//============Reports:: receiptPayment============//
function receiptPayment() {
	let span, strong, div, div1, div2, table, tbody, thead, th, tr, td, td1
	let mainSection = cTag('div', { 'class': `innerContainer`, 'style': `background: #fff` });
	mainSection.appendChild(cTag('input', { 'type': `hidden`, 'name': `pageURI`, 'id': `pageURI`, 'value': `Accounts/receiptPayment` }));
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div = cTag('div', { 'class': `columnSM4 columnMD7 pright0` });
	let button = cTag('button', { 'class': `btn cursor p2x10 marginright15`, 'click': printreceiptPayment, 'title': `Print Receipt & Payment` });
	button.appendChild(cTag('i', { 'class': `fa fa-print` }));
	button.append(' Print');
	div.appendChild(button);
	div2.appendChild(div);
	div1 = cTag('div', { 'class': `columnSM4 columnMD3 pbottom10 pright0` });
	div = cTag('div', { 'class': `input-group` });
	span = cTag('span', { 'class': `input-group-addon cursor`, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Views Type` });
	span.innerHTML = 'Views Type';
	div.appendChild(span);
	let select = cTag('select', { 'name': `fviews_type`, 'id': `fviews_type`, 'class': `form-control`, 'change': loadRecPayData });
	setOptions(select, { '1': 'Group Only', '2': 'Group + Sub GROUP', '3': 'Details' }, 1, 1);
	div.appendChild(select);
	div1.appendChild(div);
	div2.appendChild(div1);
	div1 = cTag('div', { 'class': `columnSM4 columnMD2 pbottom10` });
	div = cTag('div', { 'class': `input-group` });
	const dateRanger = cTag('input', { 'type': `text`, 'placeholder': 'select daterange', 'value': `2023-05-12 - 2023-05-12`, 'id': `fdate`, 'name': `fdate`, 'class': `form-control`, 'keydown': listenToEnterKey(loadRecPayData), 'maxlength': `23` });
	daterange_picker_dialog(dateRanger);
	div.appendChild(dateRanger);
	span = cTag('span', { 'class': `input-group-addon cursor`, 'click': loadRecPayData, 'data-toggle': `tooltip`, 'data-placement': `bottom`, 'title': `Search Trial Balance` });
	span.appendChild(cTag('i', { 'class': `fa fa-search` }));
	div.appendChild(span);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);
	div2 = cTag('div', { 'class': `flexSpaBetRow` });
	div1 = cTag('div', { 'class': `columnSM12`, 'style': `position: relative` });
	div = cTag('div', { 'id': `no-more-tables` });
	let table1 = cTag('table', { 'class': `columnMD12` });
	let tbody1 = cTag('tbody');
	let tr1 = cTag('tr');
	td1 = cTag('td', { 'width': `49%`, 'valign': `top` });
	table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
	thead = cTag('thead', { 'class': `cf` });
	tr = cTag('tr');
	th = cTag('th', { 'align': `center` });
	th.innerHTML = 'RECEIPTS';
	tr.appendChild(th);
	th = cTag('th', { 'width': `15%`, 'align': `center` });
	th.innerHTML = 'Ledger Amount';
	tr.appendChild(th);
	th = cTag('th', { 'width': `15%`, 'align': `center` });
	th.innerHTML = 'Group Amount';
	tr.appendChild(th);
	thead.appendChild(tr);
	table.appendChild(thead);
	table.appendChild(cTag('tbody', { 'id': `receiptsRows` }));
	tbody = cTag('tbody');
	tr = cTag('tr', { 'class': `lightgreenrow` });
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `left` });
	strong = cTag('strong');
	strong.innerHTML = 'Opening Balance: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	tbody.appendChild(tr);
	tr = cTag('tr', { 'class': `lightgreenrow` });
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `left` });
	strong = cTag('strong');
	strong.innerHTML = 'Receipts Total: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	tbody.appendChild(tr);
	tr = cTag('tr', { 'class': `lightpinkrow` });
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `left` });
	strong = cTag('strong');
	strong.innerHTML = 'Grand Total: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	tbody.appendChild(tr);
	table.appendChild(tbody);
	td1.appendChild(table);
	tr1.appendChild(td1);
	td = cTag('td', { 'width': `5`, 'valign': `top` });
	td.innerHTML = ' ';
	tr1.appendChild(td);
	td1 = cTag('td', { 'valign': `top` });
	table = cTag('table', { 'class': `table-bordered table-striped table-condensed cf listing` });
	thead = cTag('thead', { 'class': `cf` });
	tr = cTag('tr');
	th = cTag('th', { 'align': `center` });
	th.innerHTML = 'PAYMENTS';
	tr.appendChild(th);
	th = cTag('th', { 'width': `15%`, 'align': `center` });
	th.innerHTML = 'Ledger Amount';
	tr.appendChild(th);
	th = cTag('th', { 'width': `15%`, 'align': `center` });
	th.innerHTML = 'Group Amount';
	tr.appendChild(th);
	thead.appendChild(tr);
	table.appendChild(thead);
	tbody = cTag('tbody');
	tr = cTag('tr', { 'class': `lightgreenrow` });
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `left` });
	strong = cTag('strong');
	strong.innerHTML = 'Payments Total: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	tbody.appendChild(tr);
	table.appendChild(tbody);
	table.appendChild(cTag('tbody'));
	table.appendChild(cTag('tbody', { 'id': `paymentsRows` }));
	tbody = cTag('tbody');
	tr = cTag('tr', { 'class': `lightgreenrow` });
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `left` });
	strong = cTag('strong');
	strong.innerHTML = 'Closing Balance: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': `Opening Balance`, 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	tbody.appendChild(tr);
	tr = cTag('tr', { 'class': `lightpinkrow` });
	td = cTag('td', { 'data-title': 'Grand Total', 'align': `left` });
	strong = cTag('strong');
	strong.innerHTML = 'Grand Total: ';
	td.appendChild(strong);
	tr.appendChild(td);
	td = cTag('td', { 'data-title': ``, 'align': `right` });
	td.innerHTML = ' ';
	tr.appendChild(td);
	td = cTag('td', { 'data-title': 'Amount', 'align': `right` });
	strong = cTag('strong');
	strong.innerHTML = '0.00';
	td.appendChild(strong);
	tr.appendChild(td);
	tbody.appendChild(tr);
	table.appendChild(tbody);
	td1.appendChild(table);
	tr1.appendChild(td1);
	tbody1.appendChild(tr1);
	table1.appendChild(tbody1);
	div.appendChild(table1);
	div1.appendChild(div);
	div2.appendChild(div1);
	mainSection.appendChild(div2);

	accountsPageTemplate(mainSection);
	loadRecPayData();
}

export function loadRecPayData() {
	let jsonData = {};
	jsonData['date_range'] = document.querySelector('#fdate').value;
	jsonData['fviews_type'] = document.querySelector('#fviews_type').value;

	const url = "/Accounts/AJgetPage_receiptPayment";

	fetchData(afterFetch, url, jsonData);

	function afterFetch(data) {
		// document.querySelector("#receiptsRows").innerHTML = data.receiptsRows;			
		// document.querySelector("#paymentsRows").innerHTML = data.paymentsRows;
	}
}

function printreceiptPayment() {
	let title = 'Trial Balance for ' + document.querySelector("#fdate").value;
	let filterby = '';
	if (document.querySelector("#fdate").value != '') {
		filterby += 'Trial Balance Date : ' + document.querySelector("#fdate").value;
	}
	_print(title, filterby);
}

//================Commonly Used==============//
function AJgetLedgerBalance(ledger_id) {
	if (document.querySelectorAll("#error_voucherList").length) {
		const url = "/Accounts/AJgetLedgerBalance";
		const data = { ledger_id: ledger_id };

		fetchData(afterFetch, url, data);

		function afterFetch(data) {
			if (data.login == '') document.querySelector("#error_voucherList").innerHTML = 'Balance: ' + data.balance;
		}
	}
}

function confirmAJupdate_Data(hidePopup) {
	const saveButton = document.querySelector(".saveButton");
	btnEnableDisable(saveButton, Translate('Removing...'), true);

	const url = "/Accounts/AJupdate_Data";
	const data = {
		tableName: document.querySelector("#tableName").value,
		idValue: document.querySelector("#idValue").value,
		description: document.querySelector("#description").value,
		fieldName: document.querySelector("#fieldName").value,
		updateValue: document.querySelector("#updateValue").value
	};


	fetchData(afterFetch, url, data);

	function afterFetch(data) {
		if (document.querySelector("#tableName").value == 'voucher' && ['receiptVoucher', 'paymentVoucher', 'journalVoucher', 'contraVoucher'].includes(segment2)) {
			loadTableRows_Voucher1();
		}
		else {
			//filter();
		}
		showTopMessage('alert_msg', data.returnData);

		hidePopup();
	}
}

function setValidQty() {
	document.querySelector(".qtyfield").addEventListener('focus', function () {
		qtyfield(this, 'focus');
	});
	document.querySelector(".qtyfield").addEventListener('blur', function () {
		qtyfield(this, 'blur');
	});
	document.querySelector(".qtyfield").addEventListener('keyup', function () {
		qtyfield(this, 'keyup');
	});
}

function qtyfield(field_id, onkeyname) {
	if (onkeyname == 'focus') {
		let price = field_id.value;
		if (price == 0) {
			field_id.value = '';
		}
	}
	else if (onkeyname == 'blur') {
		let price = field_id.value;
		if (price == '') {
			field_id.value = 0;
		}
	}
	else if (onkeyname == 'keyup') {
		let price = field_id.value;
		let ValidChars = "0123456789";
		let IsNumber = true;
		let Char;
		let validint = '';
		for (let i = 0; i < price.length && IsNumber == true; i++) {
			Char = price.charAt(i);
			if ((i == 0 && Char == 0) || ValidChars.indexOf(Char) == -1) { }
			else {
				validint = validint + Char;
			}
		}
		if (price.length > validint.length) {
			field_id.value = validint;
		}
	}
}

function setValidPrice() {
	document.querySelector(".pricefield").addEventListener('focus', function () {
		pricefield(this, 'focus');
	});
	document.querySelector(".pricefield").addEventListener('blur', function () {
		pricefield(this, 'blur');
	});
	document.querySelector(".pricefield").addEventListener('keyup', function () {
		pricefield(this, 'keyup');
	});
}

function pricefield(field_id, onkeyname) {
	if (onkeyname == 'focus') {
		let price = field_id.value;
		if (price == 0) {
			field_id.value = '';
		}
	}
	else if (onkeyname == 'blur') {
		let price = field_id.value;
		if (price == '') {
			field_id.value = 0;
		}
	}
	else if (onkeyname == 'keyup') {
		let price = field_id.value;
		let ValidChars = ".0123456789-";
		let IsNumber = true;
		let Char;
		let validint = '';
		for (let i = 0; i < price.length && IsNumber == true; i++) {
			Char = price.charAt(i);
			if ((i == 0 && Char == 0) || ValidChars.indexOf(Char) == -1) { }
			else {
				validint = validint + Char;
			}
		}
		if (price.length > validint.length) {
			field_id.value = validint;
		}
	}
}

function AJautoComplete_Ledger() {
	const voucherTypeData = { 'receiptVoucher': 1, 'paymentVoucher': 2, 'journalVoucher': 3, 'contraVoucher': 4, 'purchaseVoucher': 5, 'salesVoucher': 6 };

	document.querySelectorAll('.ledgerName').forEach(node => {
		customAutoComplete(node, {
			minLength: 2,
			source: async function (request, response) {
				let voucherTypeVal = voucherTypeData[segment2] || 1;
				const jsonData = { "keyword_search": request, 'voucherTypeVal': voucherTypeVal };
				const url = "/Accounts/AJautoComplete_Ledger";

				await fetchData(afterFetch, url, jsonData, 'JSON', 0);

				function afterFetch(data) {
					response(data.returnStr);
				}
			},
			select: function (event, info) {
				node.value = info.label;
				node.parentNode.querySelector('.ledger_id').value = info.lId;
				AJgetLedgerBalance(info.lId);
				btnEnableDisable(document.querySelector(".saveButton"), Translate('Save'), false);
			}
		})
	})
}

function AJupdate_Data(tableName, idValue, description, fieldName, updateValue) {
	const message = document.createDocumentFragment();
	message.append('Are you sure want to ');
	const b = cTag('b');
	b.innerHTML = description;
	message.append(b, '?');
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `tableName`, 'value': tableName }));
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `idValue`, 'value': idValue }));
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `description`, 'value': description }));
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `fieldName`, 'value': fieldName }));
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `updateValue`, 'value': updateValue }));

	confirm_dialog(description, message, confirmAJupdate_Data);
}

function AJarchive_Popup(tableName, idValue, description, activeInActive) {
	let title = 'Archive "' + description + '" data';
	const message = document.createDocumentFragment();
	const b = cTag('b');
	b.innerHTML = description;
	if (activeInActive == 1) {
		message.append('Are you sure want to Archive ');
		message.append(b, ' from all related list data? ');
	}
	else {
		title = 'Un-Archive "' + description + '" data';
		message.append('Are you sure want to Un-Archive ');
		message.append(b, ' into all related list data? ');
	}

	message.append(cTag('br'), cTag('br'));
	let center = cTag('center', { 'class': `txtred txt18` });
	if (activeInActive == 1) {
		center.innerHTML = 'Make sure it will be backed when you will active it again.';
	}
	else {
		center = cTag('center', { 'class': `txtgreen txt18` });
		center.innerHTML = 'Make sure it will be shown into everywhere(related).';
	}
	message.appendChild(center);

	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `tableName`, 'value': tableName }));
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `idValue`, 'value': idValue }));
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `description`, 'value': description }));
	message.appendChild(cTag('input', { 'type': `hidden`, 'id': `activeInActive`, 'value': activeInActive }));

	confirm_dialog(title, message, confirmArchive);
}

function confirmArchive(hidePopup) {
	const saveButton = document.querySelector(".saveButton");
	btnEnableDisable(saveButton, Translate('Removing...'), true);

	const url = "/Accounts/oneRowArchive/";
	const data = {
		tableName: document.querySelector("#tableName").value,
		idValue: document.querySelector("#idValue").value,
		description: document.querySelector("#description").value,
		activeInActive: document.querySelector("#activeInActive").value
	};

	fetchData(afterFetch, url, data);

	function afterFetch(data) {

		if (['Archived successfully.', 'Actived successfully.'].includes(data.returnData)) {

			showTopMessage('success_msg', data.returnData);

			if (document.querySelector("#tableName").value == 'ledger') {
				ledgerData();
			}
			else if (document.querySelector("#tableName").value == 'voucher' && ['receiptVoucher', 'paymentVoucher', 'journalVoucher', 'contraVoucher'].includes(segment2)) {
				loadTableRows_Voucher1();
			}
			else {
				//filter();
			}
		}
		else {
			showTopMessage('alert_msg', data.returnData);
		}
		hidePopup();
	}
}

function printbyuri(uri) {
	let day = new Date();
	let id = day.getTime();
	let w = 900;
	let h = 600;
	let scrl = 1;
	let winl = (screen.width - w) / 2;
	let wint = (screen.height - h) / 2;
	const winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scrl + ',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	window.open(uri, id, winprops);
	// eval("page" + id + " = window.open(uri, '" + id + "', winprops);");
}

function _print(title, filterby) {
	const divContents = document.querySelector("#no-more-tables").cloneNode(true);
	const now = new Date();
	const todayDate = now.getDate() + '-' + (now.getMonth() + 1) + '-' + now.getFullYear();

	let div1, div;
	let html = cTag('html');
	let head = cTag('head');
	let titleTag = cTag('title');
	titleTag.innerHTML = title;
	head.appendChild(titleTag);
	head.appendChild(cTag('link', { 'rel': "stylesheet", 'href': `${location.origin}/assets/css-v3/print.css` }));
	html.appendChild(head);
	let body = cTag('body');
	const headerSection = cTag('div');
	div1 = cTag('div', { 'class': `width100` });
	div = cTag('div', { 'class': `txtcenter txt20bold` });
	div.innerHTML = stripslashes(COMPANYNAME);
	div1.appendChild(div);
	headerSection.appendChild(div1);
	div1 = cTag('div', { 'class': `width100 mtop10` });
	div = cTag('div', { 'class': `floatleft txtleft txt18bold` });
	div.innerHTML = stripslashes(title);
	div1.appendChild(div);
	div = cTag('div', { 'class': `floatright txtright txt16normal` });
	div.innerHTML = todayDate;
	div1.appendChild(div);
	headerSection.appendChild(div1);
	div = cTag('div', { 'class': `width100` });
	div.appendChild(cTag('hr', { 'class': `mtop10 mbottom0` }));
	headerSection.appendChild(div);
	div = cTag('div', { 'class': `width100 mtop10`, 'id': `filterby` });
	div.innerHTML = filterby;
	headerSection.appendChild(div);
	body.append(headerSection, divContents);
	html.appendChild(body);

	const day = new Date();
	const id = day.getTime();
	const w = 900;
	const h = 600;
	const scrl = 1;
	const winl = (screen.width - w) / 2;
	const wint = (screen.height - h) / 2;
	const winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scrl + ',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';

	const printWindow = window.open('', '" + id + "', winprops);

	let document_focus;
	if (printWindow.document.write("<!DOCTYPE html>"),
		printWindow.document.appendChild(html),
		printWindow.document.close(),
		Boolean(window.chrome)) {
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
			if (state === 'interactive') { }
			else if (state === 'complete') {
				setTimeout(function () {
					printWindow.document.getElementById('interactive');
					printWindow.window.print();
					document_focus = true;
				}, 1000);
			}
		}
	}
	printWindow.setInterval(function () {
		let deviceOpSy = getMobileOperatingSystem();
		if (document_focus === true && deviceOpSy === 'unknown') { printWindow.window.close(); }
	}, 500);
}

function AJremove_Data(tableName, tableId, nameVal, publishVal = 0) {

	const showTableData = cTag('div');
	showTableData.innerHTML = '';
	let pTag = cTag('p');
	let title;
	if (publishVal == 0) {
		title = Translate('Confirm Archive Information');
		pTag.innerHTML = Translate('Are you sure you want to archive this information?');
	}
	else {
		title = Translate('Confirm ACTIVE Information');
		pTag.innerHTML = Translate('Are you sure you want to ACTIVE this information?');
	}
	showTableData.appendChild(pTag);
	popup_dialog(
		showTableData,
		{
			title: title,
			width: 400,
			buttons: {
				_Cancel: {
					text: Translate('Close'),
					class: 'btn defaultButton', 'style': "margin-left: 10px;",
					click: function (hide) {
						hide();
					},
				},
				_Confirm: {
					text: Translate('Confirm'),
					class: 'btn saveButton archive', 'style': "margin-left: 10px;",
					click: (hidePopup) => confirmAJremoveData(hidePopup, tableName, tableId, nameVal, publishVal)
				}
			}
		}
	);
}

function loadDataScript() {
	if (document.querySelectorAll(".AJgetGroupsPopup").length) {
		document.querySelectorAll(".AJgetGroupsPopup").forEach(oneField => {
			oneField.addEventListener('click', function () {
				AJgetGroupsPopup(this.getAttribute('data-id'));
			});
		});
	}

	if (document.querySelectorAll(".AJremoveData").length) {
		document.querySelectorAll(".AJremoveData").forEach(oneField => {
			oneField.addEventListener('click', function () {
				AJremoveData(this.getAttribute('data-table'), this.getAttribute('data-id'), this.getAttribute('data-description'));
			});
		});
	}
}

document.addEventListener('DOMContentLoaded', function () {
	const layoutFunctions = {
		dashboard, groups, receiptVoucher, paymentVoucher, journalVoucher, contraVoucher, purchaseVoucher, salesVoucher,
		ledger, ledgerView, dayBook, ledgerReport, trialBalance, receiptPayment
	};
	if (layoutFunctions[segment2]) layoutFunctions[segment2]();

	leftsideHide("secondarySideMenu", 'secondaryNavMenu');

	applySanitizer(document);

	document.onkeypress = function (event) {
		event = event || window.event;

		let keyLocation = event.location;
		let keyValue = event.key || '';
		let fieldName = event.target || '';
		if (fieldName != '') {
			if (fieldName.name && fieldName.name != '') { return true; }
		}

		//if(['', 'dashboard'].includes(segment2)){

		if (keyLocation === 0 && ['1', '2', '3', '4', '5', '6', '7', '8', 'q', 'w', 'e', 'r'].includes(keyValue)) {
			if (modulesInfo[keyValue]) {
				let oneModuleInfo = modulesInfo[keyValue];
				if (oneModuleInfo.fileName) {
					window.location = '/Accounts/' + oneModuleInfo.fileName;
				}
			}
			else if (reportsInfo[keyValue]) {
				let oneModuleInfo = reportsInfo[keyValue];
				if (oneModuleInfo.fileName) {
					window.location = '/Accounts/' + oneModuleInfo.fileName;
				}
			}
		}
		//showTopMessage('alert_msg', 'keyLocation : '+keyLocation+', keyValue: '+keyValue);
		//}
	};

});

//==============For Popup Dialog====================//

(function () {
	"use strict";

	//get all unique CSS classes defined in the main document
	let allClasses = Array.from(document.querySelectorAll('*'))
		.map(n => Array.from(n.classList))
		.reduce((all, a) => all ? all.concat(a) : a)
		.reduce((all, i) => all.add(i), new Set());

	//load contents of all CSS stylesheets applied to the document
	let loadStyleSheets = Array.from(document.styleSheets)
		.map(s => {
			if (s.href) {
				return fetch(s.href)
					.then(r => r.text())
					.catch(e => {
						console.warn('Coudn\'t load ' + s.href + ' - skipping');
						return "";
					});
			}

			return s.ownerNode.innerText
		});

	Promise.all(loadStyleSheets).then(s => {
		let text = s.reduce((all, s) => all + s);

		//get a list of all CSS classes that are not mentioned in the stylesheets
		let undefinedClasses = Array.from(allClasses)
			.filter(c => {
				let rgx = new RegExp(escapeRegExp('.' + c) + '[^_a-zA-Z0-9-]');

				return !rgx.test(text);
			});

		if (undefinedClasses.length) {
			console.log('List of ' + undefinedClasses.length + ' undefined CSS classes: ', undefinedClasses);
		} else {
			console.log('All CSS classes are defined!');
		}
	});

	function escapeRegExp(str) {
		return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
	}

})();
