import {
    cTag, Translate, checkAndSetLimit, tooltip, addCurrency,listenToEnterKey, daterange_picker_dialog, checkNumericInputOnKeydown,
    DBDateToViewDate, confirm_dialog, showTopMessage, setOptions, activeLoader, hideLoader, addPaginationRowFlex, 
    checkAndSetSessionData, AJremove_tableRow, popup_dialog600, popup_dialog1000, date_picker, fetchData, addCustomeEventListener, 
    actionBtnClick, serialize, showthisurivalue, multiSelectAction, onClickPagination, wysiwyrEditor, triggerEvent, controllNumericField
} from './common.js'

if(segment2==='') segment2 = 'lists';

export async function smsInvoice(){
	let pos_id = document.getElementById("pos_id").value;
	let popUpHml = document.createElement('div');
    popUpHml.appendChild(cTag('input',{ 'type':"hidden", 'id':'tableIdValue', 'value':pos_id }));
	popUpHml.append(Translate('Are you sure want to send SMS this Invoice information'));
      let invoiceHTML = cTag('div', {class:'cardContent customInfoGrid', style:'border:1px solid #D5D5D5; margin-top:10px'});
      invoiceHTML.innerHTML = document.getElementById("order_info").innerHTML+document.getElementById("customer_information").innerHTML;
   popUpHml.appendChild(invoiceHTML);
	confirm_dialog(Translate('Send SMS'), popUpHml, (hidePopup)=>confirmSMSInvoice(hidePopup));
}

export async function confirmSMSInvoice(hidePopup){
	let pos_id = document.querySelector("#tableIdValue").value;
	
	const jsonData = {"pos_id":pos_id};
	const url = '/BulkSMS/AJsendInvoiceSMS/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr==='Success'){
			hidePopup();
			showTopMessage('success_msg', Translate('SMS sent successfully.'));
		}
		else{
			showTopMessage('error_msg',Translate('Could not send SMS'));
		}
	}
}

export async function smsPO(){
	let po_id = document.getElementById("po_id").value;
	let popUpHml = document.createElement('div');
    popUpHml.appendChild(cTag('input',{ 'type':"hidden", 'id':'tableIdValue', 'value':po_id }));
	popUpHml.append(Translate('Are you sure want to send SMS this PO information'));
      let invoiceHTML = cTag('div', {class:'cardContent customInfoGrid', style:'border:1px solid #D5D5D5; margin-top:10px'});
      invoiceHTML.innerHTML = document.getElementById("order_info").innerHTML+document.getElementById("supplier_information").innerHTML;
   popUpHml.appendChild(invoiceHTML);
	confirm_dialog(Translate('Send SMS'), popUpHml, (hidePopup)=>confirmSMSPO(hidePopup));
}

export async function confirmSMSPO(hidePopup){
	let po_id = document.querySelector("#tableIdValue").value;
	
	const jsonData = {"po_id":po_id};
	const url = '/BulkSMS/AJsendPOSMS/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr==='Success'){
			hidePopup();
			showTopMessage('success_msg', Translate('SMS sent successfully.'));
		}
		else{
			showTopMessage('error_msg',Translate('Could not send SMS'));
		}
	}
}


