OS = getDeviceOperatingSystem();
let heartBeatingTimerID;
export let  activityFieldAttributes = [
	{ 'valign':'top','datatitle':Translate('Date'), 'align':'left'},
	{'valign':'top','datatitle':Translate('Time'), 'align':'right'},
	{'valign':'top','datatitle':Translate('User'), 'align':'left'},
	{'valign':'top','datatitle':Translate('Activity'), 'align':'left'},
	{'valign':'top','datatitle':Translate('Details'), 'align':'left'}
];

let domainName = window.location.hostname.split('.').slice(-2).join('.');
let prevSegment1 = '';
let prevSegment2 = '';
const filterStorageAddiCond = ['Getting_Started', 'Manage_Data', 'Settings', 'Integrations'];
if (sessionStorage.getItem("prevSegments") !== null) {
	let prevSegments = JSON.parse(sessionStorage.getItem("prevSegments"));
    prevSegment1 = prevSegments.segment1;
	prevSegment2 = prevSegments.segment2;
	if((prevSegment1 === segment1 && !filterStorageAddiCond.includes(segment1)) || (prevSegment1 === segment1 && filterStorageAddiCond.includes(segment1) && prevSegment2 === segment2)){}
	else{
		sessionStorage.setItem('list_filters', JSON.stringify({}));
	}
}

sessionStorage.setItem('prevSegments', JSON.stringify({segment1, segment2}));

export function stripslashes(text) {
	text = text.replace(/\\'/g, '\'');
	text = text.replace(/\\"/g, '"');
	text = text.replace(/\\0/g, '\0');
	text = text.replace(/\\\\/g, '\\');
	return text;
}

export function Translate(text){
	if(loadLangFile != 'English'){
		if(langModifiedData !==undefined && langModifiedData[text] !==undefined){
			return stripslashes(langModifiedData[text]);
		}
		else if(languageData[text] !==undefined){
			return stripslashes(languageData[text]);
		}
		else{
			let message = '';
			const errorInfo = new Error('myError');
			const splitsStack = errorInfo.stack.split('at help (');
			if(splitsStack.length>1){
				const atHelpFilePath = splitsStack[1].split(')');
				const FilePath = atHelpFilePath[0].split('assets/js-'+swVersion+'/');
				if(splitsStack.length>1){
					message = FilePath[1];
				}
			}
			
			const jsonData = {name: 'JS Translate issue: '+text, message: message+', Language: '+loadLangFile, url: document.location.href};
			const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
			fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));
		}
	}
	return stripslashes(text);
}

//===================error-handler============================
window.addEventListener('error',function(errorEvent){
	if(['cellstorelocal.co', 'machousel.com.bd'].includes(domainName)){
		alert(errorEvent.error.message);
	}
	const {message,stack} = errorEvent.error;
	let errMessage = stack;
	if(stack.indexOf(message)<0) errMessage = `${message} at ${stack}`;
	let url = `current: ${document.location.href}`;
	if(document.referrer) url += `\nprevious: ${document.referrer}`;
	const jsonData = {name: 'Script Error', message: '1: '+errMessage+` ---UserAgent: ${navigator.userAgent}`, url};
	let options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
    fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));	
})
window.onunhandledrejection = function(errorEvent) {
    if(!errorEvent.reason) return;
	if(['cellstorelocal.co', 'machousel.com.bd'].includes(domainName)){
		alert(errorEvent.reason.message);
	}	
	const {message,stack} = errorEvent.reason;
	let errMessage = stack;
	if(stack.indexOf(message)<0) errMessage = `${message} at ${stack}`;
	let url = `current: ${document.location.href}`;
	if(document.referrer) url += `\nprevious: ${document.referrer}`;
    const jsonData = {name: 'Script Error', message: '2: '+errMessage+` ---UserAgent: ${navigator.userAgent}`, url};
    let options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
    fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));	
};

window.addEventListener('online',()=>sendCachedError('cachedError'));

export async function sendCachedError(storageID){
    if(window.localStorage.getItem(storageID)){
    	let cachedError = JSON.parse(window.localStorage.getItem(storageID));
    	if(cachedError.length){
			for (const error of cachedError) {
				let options = {method: "POST", body:JSON.stringify(error), headers:{'Content-Type':'application/json'}};
        		if(window.navigator.onLine) await fetch('/Home/handleErr/', options);
			}        	
        	window.localStorage.removeItem(storageID);
    	}
    }
}
export function cacheError(error,storageID){
	let cachedError = JSON.parse(window.localStorage.getItem(storageID));
    if(!cachedError) cachedError = [];
    let existInCache = cachedError.find(errorItem=>{
        return (errorItem.name===error.name && errorItem.message===error.message && errorItem.url===error.url)
    })
    if(!existInCache){
        if(cachedError) window.localStorage.setItem(storageID,JSON.stringify([...cachedError,error]));
    	else window.localStorage.setItem(storageID,JSON.stringify([error]));
    }	
}

export function checkForSuccessfulRequest(response){
	let serverIsOffline = (400<=response.status && response.status<600)?true:false;
	//if server is online and there is any cachedServerOfflineError, then send it first
	if(!serverIsOffline) sendCachedError('cachedServerOfflineError');

	return new Promise((resolve,reject)=>{
        if(response.ok) resolve(response);
        else{
			let error = new Error(response.status+' '+response.statusText);
			if(serverIsOffline) error.serverOffline = true; // setting a flag that server is offline
            else error.serverIssue = true;
            reject(error);
        } 
    })
}

export function handleErr(err,api_endpoint) {
	let issue = { haveIssue:true };
	if(err.serverIssue){
		const jsonData = {name: 'Server Issue', message: err.stack+' for API: '+api_endpoint, url: document.location.href};
		const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
		fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));

		issue.warnTitle = Translate('Resource Issue')
		issue.warnMsg = 'Server is not responding with the requested Resource';
	}
	else if(err.serverOffline){					 
		const jsonData = {name: 'Server is Unavailable/Offline', message: ' at '+Date()+' when retrieving data from API: '+api_endpoint, url: document.location.href}; 			
		cacheError(jsonData,'cachedServerOfflineError');// if server is offline then just cache the error  

		issue.warnTitle = Translate('Server Issue')
		issue.warnMsg = 'Server is Unavailable/Offline';
	}
	else if(err.message.search('Unexpected token')>=0){
		const jsonData = {name: 'JSON Issue', message: err.message +': '+ err.stack+' for API: '+api_endpoint, url: document.location.href};
		const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
		fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));

		issue.warnTitle = Translate('JSON Issue')
		issue.warnMsg = 'The Response server send is not in JSON format';
	}
	else{
		issue.warnTitle = Translate('Internet Issue')
		issue.warnMsg = Translate('Internet connection problem. Retry again.');
	}
	return issue;
}
//===================error-handler============================


export function checkAndSetLimit(){
    let rowHeight = 31;
    if(segment1==='Repairs' || segment2==='invoicesReport') rowHeight = 51;
    else if(segment2==='End_of_Day') rowHeight = 62;
    let limit = parseInt(document.getElementById("limit").value);
    if(isNaN(limit) || limit==0){
        limit = 15;
        if(!['edit','view','sview'].includes(segment2)){
            const displayHeight = window.innerHeight;
            
            const topheaderbar = parseFloat(document.querySelector("#topheaderbar").offsetHeight);
            if(isNaN(topheaderbar) || topheaderbar==0){topheaderbar = 60;}
    
            let outerTableHeight = 0;
            const outerTableHeightObj = document.querySelectorAll(".outerListsTable");
            if(outerTableHeightObj.length>0){
                outerTableHeightObj.forEach(oneHeightObj=>{
                    let oneHeight = parseFloat(oneHeightObj.offsetHeight);
                    if(isNaN(oneHeight) || oneHeight==0){oneHeight = 45;}
                    outerTableHeight += oneHeight;
                })
            }
    
            let extraPaddingMargin = 25;
            if(['Manage_Data','Getting_Started'].includes(segment1)) extraPaddingMargin = 100;
            else if(segment1==='Admin') extraPaddingMargin = 120;
            const bodyHeight = displayHeight-topheaderbar-outerTableHeight-extraPaddingMargin;
            if(bodyHeight<=0 || bodyHeight<=rowHeight){limit = 1;}
            else{
                limit = Math.floor(bodyHeight/rowHeight);
            }
        }
    }
    return limit;
}

export function tooltip(node){
    if(node.getAttribute('data-tooltip-active')) return;

    let styles = {
        tooltip:{
            'position':'absolute',
            'background':'black',
            'color':'white',
            'padding':'3px 8px',
            'font-size':'12px',
            'text-align':'center',
            'border':'1px solid',
            'border-radius':'5px',
            'transition':'0.5s',
            'opacity':0,
			'z-index':'100000'
        },
        arrow:{
            'position':'absolute',
            'border': '7px solid black',
            'left': '50%',
        },
        upArrow:{
            'border-color': 'transparent transparent #000000 transparent',
            'top': '1px',
            'transform': 'translate(-50%,-100%)',
        },
        downArrow:{
            'border-color': '#000000 transparent transparent',
            'bottom': '1px',
            'transform': 'translate(-50%,100%)',
        }
    }
    let tooltip;

    node.setAttribute('data-tooltip-active',true);
    node.addEventListener('mouseenter',()=>{
        if(!(node.getAttribute('data-original-title'))) return;
        let boundingClientRect = node.getBoundingClientRect();
        let tooltipPosition = {
            'top':boundingClientRect.top+window.scrollY-10+'px',
            'left':boundingClientRect.left+(boundingClientRect.width/2)+window.scrollX+'px',
            'transform': 'translate(-50%,-100%)'
        }
        if(node.getAttribute('data-placement')==="bottom"){
          tooltipPosition.top = boundingClientRect.bottom+window.scrollY+10+'px';
          tooltipPosition.transform = 'translate(-50%)';
        }
        tooltip = cTag('div',{'id':'_tooltip_'});
        setStyles(tooltip,styles.tooltip);
        setStyles(tooltip,tooltipPosition);
        tooltip.innerHTML = node.getAttribute('data-original-title');
            let arrow = cTag('span');
            setStyles(arrow,styles.arrow);
            if(node.getAttribute('data-placement')==="bottom") setStyles(arrow,styles.upArrow);
            else setStyles(arrow,styles.downArrow);
        tooltip.appendChild(arrow);
        document.body.appendChild(tooltip);
        setTimeout(() => {
            tooltip.style.opacity = 1;
        }, 0);
    });
	
    node.addEventListener('mouseleave',()=>{
		if(tooltip){
        	tooltip.remove();
		}
    });
    node.addEventListener('mousedown',()=>{
		if(tooltip){
        	tooltip.remove();
		}
    });    
    
    function setStyles(node,stylesObj){
        for (const property in stylesObj) {
            node.style[property] = stylesObj[property];
        }
    }
}

export function storeSessionData(currentData, merge = 1){
	let previousData = {};
	if((prevSegment1 === segment1 && !filterStorageAddiCond.includes(segment1)) || (prevSegment1 === segment1 && filterStorageAddiCond.includes(segment1) && prevSegment2 === segment2)){
		previousData = JSON.parse(sessionStorage.getItem("list_filters"));
	}
	let newData;
	if(merge === 0) newData = currentData;
	else newData = {...previousData, ...currentData};
	sessionStorage.setItem('list_filters', JSON.stringify(newData));
}

export function cTag(tagName, attributes){
    let node = document.createElement(tagName);
    if(attributes){
        for(const [key, value] of Object.entries(attributes)) {
            if(typeof value === 'function') node.addEventListener(key,value);
			else node.setAttribute(key, value);
        }
    }
    return node;
}

export function addCurrency(amount){
	if(amount>=0){
		return `${currency}${number_format(amount)}`;
	}	
	return `-${currency}${number_format(amount*(-1))}`;
}

export function NeedHaveOnPO(NHOInfo, product_id, HaveOnPOStyle){
	let have = NHOInfo.have;
	let manage_inventory_count = NHOInfo.manage_inventory_count;
	let need = NHOInfo.need;
	let onPO = NHOInfo.onPO;
	let product_type = NHOInfo.product_type;

	let HHPStr = cTag('a', {class: "anchorfulllink", 'style': HaveOnPOStyle, 'href': 'javascript:void(0)', title:Translate('Edit')});
    HHPStr.innerHTML = '&nbsp;';
	if(manage_inventory_count>0){
		HHPStr.innerHTML = have;
	}

	if(product_type==='Live Stocks' && have>0){
		HHPStr = cTag('a', {class: "anchorfulllink", 'style': HaveOnPOStyle, 'href': '/IMEI/lists/1/product/'+product_id, title:Translate('View IMEI')});
		HHPStr.innerHTML = have;
	}

	if(['Standard', 'Live Stocks'].includes(product_type) && manage_inventory_count>0){
		HHPStr = cTag('a', {'style': "color: #009; text-decoration: underline;" + HaveOnPOStyle, 'href': 'javascript:void(0)', title:Translate('View Details'), click: ()=> showOnPOInfo(product_id, product_type)});
		HHPStr.innerHTML = need+' / '+have+' / '+onPO+' ';
		HHPStr.appendChild(cTag('i', {class: "fa fa-link"}));
	}

	return HHPStr;
}round

export function emailcheck(mail) {
	let regex = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/;	
	return regex.test(mail);					
}

export function number_format(number){
    const roundNumber = round(number,2).toFixed(2);
    const parts = roundNumber.toString().split(".");
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
}

export function checkPhone(fieldIdName,validForNumberOnly) {
	let contactNo = document.getElementById(fieldIdName).value;
	let regex;
	if(validForNumberOnly) regex = /^\d+$/;
	else regex = /^[\d\s()\-+.]+$/;
	return regex.test(contactNo);
}

export function checkintpositive(price){
	let ValidChars = ".0123456789-";
	let IsNumber=true;
	let Char;
	let validint = '';
	for (let i = 0; i < price.length && IsNumber === true; i++){ 
		Char = price.charAt(i);
		if ((i===0 && Char===0) || ValidChars.indexOf(Char) === -1){}
		else{
			validint = validint+Char;
		}
	}
	return validint;
}

//=======Date Conversion=========//
export function DBDateToViewDate(datetime, arrayYN=0, shortYear=0){
	let dateValue, timeValue;
	dateValue = timeValue = '';
	if(['0000-00-00', '1000-01-01', '0000-00-00 00:00:00', '1000-01-01 00:00:00'].includes(datetime)){datetime = '';}

	if(datetime.length >= 10 && ['0000-00-00', '1000-01-01'].includes(datetime)===false){
		let [yyyy, mm, dd] = datetime.substring(0, 10).split('-');
		if(shortYear==1){yyyy = yyyy.substring(2, 4)}
		if(calenderDate.toLowerCase()==='dd-mm-yyyy'){dateValue = `${dd}-${mm}-${yyyy}`;}
		else{dateValue = `${mm}/${dd}/${yyyy}`;}

		if(datetime.length>10){
			let [hh,ii] = datetime.substring(11, 16).split(':');
			hh = parseInt(hh);
			if(timeformat.toLowerCase()==='24 hour'){timeValue = `${hh}:${ii}`;}
			else{
				let ampm = 'am';
				if(hh>11){ampm = 'pm';}
				if(hh>12){hh = hh-12;}
				timeValue = `${hh}:${ii} ${ampm}`;
			}
		}
	}

	if(arrayYN===1){
		return [dateValue, timeValue];
	}
	if(timeValue !==''){
		return dateValue+' '+timeValue;
	}
	return dateValue;
}

export function ViewDateToDBDate(datetime){
	let dateValue, timeValue;
	dateValue = timeValue = '';
	if(datetime.length >= 10){		
		if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
			let [dd,mm,yyyy] = datetime.substring(0, 10).split('-');
			dd = parseInt(dd);
			if(dd<10) dd = '0'+dd;
			mm = parseInt(mm);
			if(mm<10) mm = '0'+mm;
			dateValue = `${yyyy}-${mm}-${dd}`;
		}
		else{
			let [mm,dd,yyyy] = datetime.substring(0, 10).split('/');
			dd = parseInt(dd);
			if(dd<10) dd = '0'+dd;
			mm = parseInt(mm);
			if(mm<10) mm = '0'+mm;
			dateValue = `${yyyy}-${mm}-${dd}`;
		}
		
		if(datetime.length>10){
			let [hh,ii] = datetime.substring(11, 16).split(':');
			hh = parseInt(hh);
			if(timeformat.toLowerCase()==='24 hour'){timeValue = `${hh}:${ii}:00`;}
			else{

				let ampm = datetime.substring(17, 19).trim();
				if(ampm==='pm' && hh<12){hh = hh+12;}
				timeValue = `${hh}:${ii}:00`;
			}
		}
	}
	if(['0000-00-00', '1000-01-01'].includes(dateValue)){dateValue = '';}

	if(timeValue !==''){
		return dateValue+' '+timeValue;
	}
	return dateValue;
}

export function ViewDateRangeToDBDate(daterange){
	if(daterange !=''){
		if(daterange.includes(' - ')){
			let [startDate, endDate] = daterange.split(' - ');
			startDate = ViewDateToDBDate(startDate);
			endDate = ViewDateToDBDate(endDate);
			daterange = startDate+' - '+endDate;
		}
		else{
			daterange = ViewDateToDBDate(daterange);
		}
	}
	return daterange;
}

export function DBDateRangeToViewDate(daterange, shortYear=0){
	if(daterange !=''){
		if(daterange.includes(' - ')){
			let [startDate, endDate] = daterange.split(' - ');
			startDate = DBDateToViewDate(startDate, 0, shortYear);
			endDate = DBDateToViewDate(endDate, 0, shortYear);
			daterange = startDate+' - '+endDate;
		}
		else{
			daterange = DBDateToViewDate(daterange, 0, shortYear);
		}
	}
	return daterange;
}
export function changeToDBdate_OnSubmit(fieldID,dateRange){
    let dateField = document.getElementById(fieldID);
    if(dateRange) dateField.value = ViewDateRangeToDBDate(dateField.value);
    else dateField.value = ViewDateToDBDate(dateField.value);
}

export function noPermissionWarning(modulename){
	alert_dialog(Translate('Sorry! could not access')+' '+modulename, Translate('You do not have permission to access this module. Please contact admin.'), Translate('Ok'));
}

export function redirectTo(url){
	window.location = url;
}

export function alertmessage(message, editlink){
	if(editlink !==''){
		let message = cTag('div');
			let aTag = cTag('a', {'href': "editlink", title: Translate('View Product Details')});
			aTag.innerHTML = Translate('View Product Details');
		message.appendChild(aTag);
	}
	if(message !==''){
		showTopMessage('alert_msg', message);
	}
}

export function formatTime(time){
	let returnstr = '';
	let minutes = Math.floor(time / 60);
	if(minutes>0){		
		if(minutes>1){returnstr += minutes+' '+Translate('Minutes');}
		else{returnstr += minutes+' '+Translate('Minute');}
	}
	
	let seconds = Math.floor(time - Math.floor(minutes * 60));
	if(seconds>0){		
		if(seconds>1){returnstr += ' '+seconds+' '+Translate('Seconds');}
		else{returnstr += ' '+seconds+' '+Translate('Second');}
	}
	return returnstr;
}

export function getCookie(name){
	let [cookie] = document.cookie.split(';').
	map(item=>{
		let [name,value] = item.trim().split('=');
		return {name,value};
	}).
	filter(item=>item.name===name);
	
	if(cookie) return cookie.value;
}

/*=======Common Function =======*/
export function preventDot(node){
	node.addEventListener('keydown',event=>{
		if(event.key==='.') event.preventDefault();
	})
}

export function removeVariables(integrationName){
	confirm_dialog(Translate('Remove')+' '+integrationName+' '+Translate('Intergration'), Translate('Are you sure want to remove this information')+' ('+integrationName+')?', confirmRemoveVariables);
}

export async function confirmRemoveVariables(){
	let variables_id = document.getElementById("variables_id").value;
	actionBtnClick('.archive', Translate('Removing'), 1);
	
    let removeBtn = document.getElementById("removeBtn");
	let buttonVal = removeBtn.value;
    
    removeBtn.innerHTML = Translate('Removing Integration')+'...';
    removeBtn.disabled = true;   

    const jsonData = {};
	jsonData['variables_id'] = variables_id;
    const url = "/Common/removeVariables/";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error' && data.id>0){
			if(document.querySelector("#refreshToken")){revokeToken();}
            showTopMessage('success_msg', Translate('Integration removed successfully.'));
			location.reload();
		}
		else{
            showTopMessage('alert_msg', data.message);
            removeBtn.innerHTML = buttonVal;
            removeBtn.disabled = false;
		}
    }    
	return false;
}

export async function getOneRowInfo(tableName, tableId, setUserRoll){
	if(tableId>0){        
        const jsonData = {tableName: tableName, tableId:tableId};
        const url = '/Common/getOneRowInfo/';

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            let nameField = '';
            let nameVal = '';
            for (let field in data) {
                if(document.querySelector("#"+field)){
							if(['no_restrict_ip'].includes(field)){
								if(data[field]>0){
									document.querySelector("#"+field).checked = true;
								}
								else{
									document.querySelector("#"+field).checked = false;
								}
					  		}
							else{
								document.querySelector("#"+field).value = data[field];
								if(['category_name', 'name', 'brand'].includes(field)){
									nameField = field;
									nameVal = data[field];
								}
							}
                }
                else if(tableName==='user' && field==='user_roll'){
                    setUserRoll(data[field]);
                }
            }				

            if(data[segment2+'_publish']==='1'){
                if(document.querySelector("#formtitle")){document.querySelector("#formtitle").innerHTML = Translate('Update')}
                if(document.querySelector("#unarchive")) document.querySelector("#unarchive").style.display = 'none';
                if(document.querySelector("#submit")) document.querySelector("#submit").style.display = '';
                if(document.querySelector("#merge")){
                    document.querySelector("#merge").style.display = '';
                } 
                if(document.querySelector("#reset")) document.querySelector("#reset").style.display = '';
                if(document.querySelector("#archive") && nameVal !==''){
                    let archive = document.querySelector("#archive");
                    archive.style.display = '';
                    nameVal = nameVal.replace(/'/g, "\\'");
                    document.getElementById('nameVal').value = nameVal;
                }
                else{
                    let user_id = parseInt(document.frmuser.user_id.value);
                    if(isNaN(user_id)){user_id = 0;}
                    if(user_id>0){
                        document.querySelector("#archive").style.display = '';
                    }
                }
                if(nameField !=='' && document.querySelector("#"+nameField)){
                    setTimeout(function() {document.querySelector("#"+nameField).focus();});
                }					
            }
            else{
                if(document.querySelector("#formtitle")){document.querySelector("#formtitle").innerHTML = Translate('Unarchive')}
                [document.querySelector("#submit"),document.querySelector("#archive")].forEach(item=>{
                    item.style.display = 'none';
                });
                document.querySelector("#reset").style.display = '';
                if(document.querySelector("#merge")) document.querySelector("#merge").style.display = 'none'
                document.querySelector("#unarchive").style.display = '';                
            }            
        }
	}
}

export function AJremoveData(){
    let tableName = segment2;
    let tableId = document.getElementById(segment2+'_id').value;
    let nameVal = document.getElementById('nameVal').value;

    const showTableData = cTag('div');
    showTableData.innerHTML = '';
        let pTag = cTag('p');
        pTag.innerHTML = Translate('Are you sure you want to archive this information?');
    showTableData.appendChild(pTag);
    //=====Hidden Fields for Pagination======//
    [
        { name: 'tableName', value: tableName},
        { name: 'tableId', value: tableId },
        { name: 'nameVal', value: nameVal }
    ].forEach(field=>{
        showTableData.appendChild(cTag('input',{ 'type':"hidden",'name':field.name,'id':field.name,'value':field.value }));
    });    

	popup_dialog(
		showTableData,
		{
			title:Translate('Confirm Archive Information'),
			width:400,
			buttons: {
				_Cancel: {
                    text:Translate('Close'),
					class: 'btn defaultButton', 'style': "margin-left: 10px;",
					click: function(hide) {
						hide();
					},
				},
				_Confirm:{
                    text:Translate('Confirm'),
					class: 'btn saveButton archive', 'style': "margin-left: 10px;",
					click: (hidePopup)=>confirmAJremoveData(hidePopup,tableName, tableId, nameVal)
				}
			}
		}
	);
}

export async function confirmAJremoveData(hidePopup,tableName, tableId, nameVal, publishVal=0){
	const jsonData = {tableName,tableId,nameVal, publishVal};
    const url = "/Common/AJremoveData/";

    fetchData(afterFetch,url,jsonData);

    async function afterFetch(data){
        if(data.returnStr==='archive-success'){			
            triggerEvent('filter');
            triggerEvent('reset');
            showTopMessage('success_msg', Translate('archived successfully'));
		}
		else{
            showTopMessage('error_msg', Translate('Error occured while archiving information! Please try again.'));
		}
		hidePopup();
    }
}

export async function clearCustomerField(selectCBF){
    if(document.querySelector("#customerNameField")){
        let customerNameField = document.getElementById('customerNameField');
        customerNameField.innerHTML = '';
			let inputField = cTag('input',{ 'maxlength': '50','type': 'text','value': '','required': 'required','name':'customer_name','id':'customer_name','class':'form-control','placeholder':Translate('Search Customers') });
        customerNameField.appendChild(inputField);
		if(!document.querySelector("#frmAccRec")){
            let addCustomerSpan = cTag('span',{ 'data-toggle': 'tooltip','title': Translate('Add New Customer'),'class': 'input-group-addon cursor' });
            addCustomerSpan.addEventListener('click',()=>dynamicImport('./Customers.js','AJget_CustomersPopup',[0, selectCBF]));
			addCustomerSpan.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', Translate('New'));
        	customerNameField.appendChild(addCustomerSpan);
		}
		if(document.getElementById("customer_name")){
			AJautoComplete('customer_name',selectCBF);
		}
        if(segment1 !== 'Accounts_Receivables') document.getElementById("customer_id").value = 0;
	}
}

export function printbyurl(URL){
	let day = new Date();
	let id = day.getTime();
	let w = 900;
	let h = 600;
	let scrl = 1;
	let winl = (screen.width - w) / 2;
	let wint = (screen.height - h) / 2;
	let winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scrl+',toolbar=0,location=0,statusbar=0,menubar=0,resizable=0';
	window.open(URL, id, winprops);
}

export function confirm_dialog(title, message, callbackfunction){
    let dialogConfirm = cTag('p',{"style": "text-align: left;"});
    if(typeof message ==='string') dialogConfirm.innerHTML = message;
    else dialogConfirm.appendChild(message);    

	popup_dialog(
		dialogConfirm,
		{
			title:title,
			width:400,
			buttons: {
				_Cancel: {
                    text:Translate('Close'),
					class: 'btn defaultButton', 'style': "margin-left: 10px;",
					click: function(hide) {
						hide();
					},
				},
				_Confirm:{
                    text:Translate('Confirm'),
					class: 'btn saveButton archive', 'style': "margin-left: 10px;",
					click: callbackfunction
				}
			}
		}
	);
}

export function alert_dialog(title, message, btnname){
	let dialogConfirm = cTag('div');
	if(typeof message==='string') dialogConfirm.innerHTML = message; 
	else dialogConfirm.appendChild(message);
	
	popup_dialog(
		dialogConfirm,
		{
			title:title,
			width:500,
			buttons: {
				btnname: {
					text: btnname, 
					class: 'btn saveButton', 'style': "margin-left: 10px;",
					click: function(hide) {
						hide();
					},
				}
			}
		}
	);	
}
export function alert_label_missing(){
	let dialogConfirm = cTag('div');
	dialogConfirm.innerHTML = Translate('You have no label-size selected. Do you want to configure?'); 
	
	popup_dialog(
		dialogConfirm,
		{
			title:'Missing Label size',
			width:500,
			buttons: {
				btnname: {
					text: Translate('Configure Label'), 
					class: 'btn saveButton', 'style': "margin-left: 10px;",
					click: function(hide) {
						hide();
                        window.location = '/Getting_Started/label_printer'
					},
				}
			}
		}
	);	
}

export function setSelectOpt(nodeID, firstOptVal, firstOptLabel, optData, objY, dataCount){
	let option;
	let selectNode = document.getElementById(nodeID);
	selectNode.innerHTML = '';
		option = cTag('option',{ 'value': firstOptVal });
		option.innerHTML = firstOptLabel;
	selectNode.appendChild(option);
	if(dataCount>0){
		if(objY===1){
			let selOpts = [];
			let l = 100;
			for(const [key, value] of Object.entries(optData)) {

				selOpts.push(value.toUpperCase()+'||'+l+'||'+key);
				l++;
			}
			selOpts.sort();

			selOpts.forEach(optData2=>{
				let optsOneRow = optData2.split('||');
				let value = optsOneRow[2];
				let labelVal = optData[value];
				
				option = cTag('option',{ 'value': value });
				option.innerHTML = labelVal;
				selectNode.appendChild(option);
			});
		}
		else{
			optData.forEach(function (optVal){
				if(optVal !== firstOptVal){
					option = cTag('option',{ 'value': optVal });
					option.innerHTML = optVal;
					selectNode.appendChild(option);
				}
			});
		}
	}
}

export function setTableRows(tableData, tdAttributes, uriStr, currencyAdd = [], dateFormatAdd = []){
    let tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//======Create TBody TR Column======//
	let tableHeadRow, tdCol;
	if(tableData.length){
		tableData.forEach(oneRow => {
			let i=0;
			tableHeadRow = cTag('tr');
			oneRow.forEach(tdvalue => {
				if(i>0){
					let idVal = oneRow[0];
					tdCol = cTag('td');
					let oneTDObj = tdAttributes[i-1];
                    for(const [key, value] of Object.entries(oneTDObj)) {
						let attName = key;
						if(attName !=='' && attName==='datatitle')
							attName = attName.replace('datatitle', 'data-title');
						tdCol.setAttribute(attName, value);
					}
					if(isNaN(parseFloat(tdvalue)) && tdvalue.includes("<a ") || uriStr===''){
						tdCol.innerHTML = tdvalue;
					}
					else{
						let aTag = cTag('a',{ 'class': 'anchorfulllink','href': '/'+uriStr+'/'+idVal });
						
						if(currencyAdd.length>0 && currencyAdd.indexOf(i) !== -1){
							if(tdvalue.slice && tdvalue.slice(-1) === '%') aTag.innerHTML = tdvalue;
							else aTag.innerHTML = addCurrency(tdvalue);
						}
						else if(dateFormatAdd.length>0 && dateFormatAdd.indexOf(i) !== -1){
							tdvalue = DBDateToViewDate(tdvalue, 0, 1);
							if(tdvalue===''){tdvalue = '\u2003';}
							aTag.innerHTML = tdvalue;
						}
						else{
							if(tdvalue===''){tdvalue = '\u2003';}
							aTag.innerHTML=tdvalue;
						}
						tdCol.appendChild(aTag);
					}
					tableHeadRow.appendChild(tdCol);
				}
				i++;
			});
			tbody.appendChild(tableHeadRow);
		});
	}
	tbody.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item))
}

export function setTableHRows(tableData, tdAttributes){
    let tbody = document.getElementById("tableRows");
	tbody.innerHTML = '';
	//======Create TBody TR Column======//
	let tableName, uriStr, dateTimeArray, date, time, oneTDObj, tabelHeadRow, tdCol;
	if(tableData.length){
		tableData.forEach(oneRow => {
			let i=0;
			tabelHeadRow = cTag('tr');
			
			let idVal = oneRow[0];
			tableName = oneRow[1];
			uriStr = oneRow[2];
			
			dateTimeArray = DBDateToViewDate(oneRow[3], 1, 1);
			
			date = dateTimeArray[0];
			tdCol = cTag('td');
			oneTDObj = tdAttributes[0];
			for(const [key, value] of Object.entries(oneTDObj)) {
				let attName = key;
				if(attName !=='' && attName==='datatitle')
					attName = attName.replace('datatitle', 'data-title');
				tdCol.setAttribute(attName, value);
			}
			tdCol.innerHTML = date;
			tabelHeadRow.appendChild(tdCol);

			time = dateTimeArray[1];
			tdCol = cTag('td');
			oneTDObj = tdAttributes[1];
			for(const [key, value] of Object.entries(oneTDObj)) {
				let attName = key;
				if(attName !=='' && attName==='datatitle')
					attName = attName.replace('datatitle', 'data-title');
				tdCol.setAttribute(attName, value);
			}
			tdCol.innerHTML = time;
			tabelHeadRow.appendChild(tdCol);

			oneRow.forEach(tdvalue => {
				if(i>3){
					tdCol = cTag('td');
					oneTDObj = tdAttributes[i-2];
                    for(const [key, value] of Object.entries(oneTDObj)) {
						let attName = key;
						if(attName !=='' && attName==='datatitle')
							attName = attName.replace('datatitle', 'data-title');
                        tdCol.setAttribute(attName, value);
					}
					if(i===6){
						if(tableName==='item' && idVal !=='' && isNaN(parseInt(idVal))){
							let pTag = cTag('p');
							pTag.innerHTML=idVal;
							tdCol.appendChild(pTag);
						}
						if(['notes', 'digital_signature'].includes(tableName)){
							if(tableName ==='notes'){
								tdCol.innerHTML = tdvalue;

								if(accountsInfo[3]===1 && segment1 !== 'Activity_Feed'){
									let cursorIcon = cTag('i',{ 'class': 'fa fa-edit cursor',  'data-toggle':"tooltip", 'click':()=>AJget_notesPopup(0,idVal), 'data-original-title':"Edit Note"});
									tdCol.append(' ', cursorIcon);
								}
							}
							else{
								let clearDiv = cTag('div',{ 'style': 'clear: both;'});
								let signImg = cTag('img',{ 'style': 'max-width:100%;', alt:Translate('Signature'), src:tdvalue});
								tdCol.append(clearDiv, signImg);
							}
						}
						else if(uriStr !==''){							
							let aTag = cTag('a',{ 'style': "color: #009; text-decoration: underline;", title:Translate('View Details'), 'href': uriStr });
							if(['po', 'poreturn'].includes(tableName)){tdvalue = 'p'+tdvalue;}
							else if(tableName ==='repairs'){tdvalue = 'Ticket No. t'+tdvalue;}
							else if(['pos', 'posreturn'].includes(tableName)){
								if(oneRow[6]==='Order Created'){
									tdvalue = Translate('Order')+' o'+tdvalue;
								}
								else{
									tdvalue = Translate('Invoice')+' s'+tdvalue;
								}								
							}
								aTag.innerHTML=tdvalue;
									let linkIcon = cTag('i',{ 'class': 'fa fa-link'});
								aTag.append(' ',linkIcon,' ');
							tdCol.appendChild(aTag);
						}
						else{
							tdCol.innerHTML = tdvalue;
						}
						
						if(['pos', 'repairs', 'track_edits', 'posreturn', 'poreturn', 'po'].includes(tableName) && idVal !==''){
							if(tableName==='po'){
								let spanTag = cTag('span');
								spanTag.innerHTML = idVal;
								tdCol.append(' ', spanTag);
							}
							else{
								let pTag = cTag('span');
								pTag.innerHTML = idVal;
								tdCol.appendChild(pTag);
							}
						}
					}
					else{
						tdCol.innerHTML = tdvalue;
						if(i===4 && ['notes', 'digital_signature'].includes(tableName) && uriStr===1){
							let publicSpan = cTag('span',{ 'class': 'bgblack', 'style': "color: white; margin-left: 15px; padding: 5px;"});
							publicSpan.innerHTML= Translate('Public');
							tdCol.append(' ', publicSpan);
						}
					}
					tabelHeadRow.appendChild(tdCol);
				}
				i++;
			});
			tbody.appendChild(tabelHeadRow);
		});
	}
	tbody.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item))
}

export function showTopMessage( msgClass, message){	
	let msgContainer = cTag('div',{'class':"innerContainer "+msgClass, 'style':'position:fixed; left:50%; top:0px; z-index:99999; margin-left:-250px; width:500px;' });
	if(typeof message==='string') msgContainer.innerHTML = message;
	else msgContainer.append(message);
	document.body.appendChild(msgContainer);
	setTimeout(() => msgContainer.remove(), 5000);
}

export function setOptions(Node, optionsData, object1_0, sort1_0){
	object1_0 = parseInt(object1_0);
	if(isNaN(object1_0)){object1_0 = 0;}

	sort1_0 = parseInt(sort1_0);
	if(isNaN(sort1_0)){sort1_0 = 0;}

	let option;
	if(object1_0===1){
		
		let selOpts = [];
		let l = 100;
		for(const [key, value] of Object.entries(optionsData)) {
			if(sort1_0===0){
				selOpts.push(l+'||'+value+'||'+key);
			}
			else{
				let value2 = value;
			    if(value && value !==''){value2 = value.toUpperCase();}
				selOpts.push(value2+'||'+l+'||'+key);
			}
			l++;
		}
		
		if(sort1_0===1){
			selOpts.sort();
		}
		selOpts.forEach(optData2=>{
			let optsOneRow = optData2.split('||');
			let value = optsOneRow[2];
			let labelVal = optionsData[value];
			
			option = cTag('option',{ 'value': value });
			option.innerHTML = stripslashes(''+labelVal);
			Node.appendChild(option);
		});
	}
	else{
		if(sort1_0===1){
			optionsData.sort((a,b)=>b.toString().localeCompare(a));
			optionsData.reverse()
		}
		optionsData.forEach(optData=>{
			option = cTag('option',{ 'value': optData });
			option.innerHTML = stripslashes(''+optData);
			Node.appendChild(option);
		});
	}
}

export function activeLoader(){	
    if(document.getElementById('loaderOverlay')) return;
	let disScreen = cTag('div',{ 'class': 'disScreen',id:'loaderOverlay','style':'display:flex;justify-content:center;align-items:center;z-index:99999;opacity: .5;position: fixed;top: 0;left: 0;width:100%;height: 100vh;background: #fff;' });	
	disScreen.appendChild(cTag('img',{ 'src': '/assets/images/ajax-loader.gif' }));
    document.body.appendChild(disScreen);
}
export function hideLoader(){
    if(document.getElementById('loaderOverlay')) document.getElementById('loaderOverlay').remove();
}

export function addPaginationRowFlex(Node,historyTable=false){
	const paginationRow = cTag('div', {class: "outerListsTable"});
        let paginationColumn = cTag('div', {class: "flexSpaBetRow columnXS12", 'style': "align-items: center;"});
			const paginationDiv1 = cTag('div', {'class': "flex"});
				let selectLimit = cTag('select', { class: "form-control", 'style': "width: 100px;", name: "limit", id: "limit" });
				selectLimit.addEventListener('change',()=>triggerEvent('loadTable'));
				setOptions(selectLimit, ['auto', 15, 20, 25, 50, 100, 500], 0, 0);
				if(historyTable) selectLimit.value = 15;
			paginationDiv1.appendChild(selectLimit);

				let paginationLabel = cTag('b', {id: "fromtodata"});
			paginationDiv1.appendChild(paginationLabel);
		paginationColumn.appendChild(paginationDiv1);
            
            let paginationDiv = cTag('div', { id: "Pagination"});
		paginationColumn.appendChild(paginationDiv);        
	paginationRow.appendChild(paginationColumn);
    Node.appendChild(paginationRow);
}

export function checkAndSetSessionData(idName, defaultVal, list_filters){
    if(list_filters.hasOwnProperty(idName)){
        for(let [key, value] of Object.entries(list_filters)){
            if(key===idName){defaultVal = value;}
        }
    }
    
    let valueExists = 0;
    let optionList = document.querySelectorAll("#"+idName+" option");
    optionList.forEach(option=>{
        if(option.value ===defaultVal){
            valueExists++;
        }
    });
    if(valueExists===0){
        let select = document.getElementById(idName);
        let option = cTag('option', {'value': defaultVal});
        option.innerHTML = defaultVal;
        select.appendChild(option);
    }
    document.getElementById(idName).value = defaultVal;
}

export function btnEnableDisable(btn,label,disable){
	if(btn.tagName==='INPUT') disable? btn.value = label+'...': btn.value = label;
	if(btn.tagName==='BUTTON') disable? btn.innerText = label+'...': btn.innerText = label;
    btn.disabled = disable;
}

export async function AJget_notesData(){	
	let note_for = document.getElementById("note_forTable").value;
	let table_id = document.getElementById("table_idValue").value;
	if(note_for==='' || table_id===''){
		showTopMessage('alert_msg', Translate('Could not add this note.'));
	}
	else{
		const jsonData = {};
		jsonData['note_for'] = note_for;
		jsonData['table_id'] = table_id;        
        const url = '/Common/AJget_notesData';

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            let article;
            let noteslist =  document.getElementById("noteslist");
            noteslist.innerHTML = '';
            
            let editPer = data.editPer;
            let mainUserId = data.mainUserId;
            let tableData = data.tabledata;
            let user_id = data.user_id;
            if(tableData.length){
                tableData.forEach((oneRow,indx) => {
                    article = cTag('article',{ 'class': 'comment' });
                        let articleCont = cTag('div',{ 'class': 'formatted_content' });
                        if(indx>0){
                            articleCont.appendChild(cTag('hr',{'style':"margin-top:10px;margin-bottom: 10px;"}))
                        }
                        let tableId = oneRow[0];
                        let user_name = oneRow[1];
                        let publics = oneRow[2];							
                        let fromTable = oneRow[3];
                        let note = oneRow[4];
                        let created_on = DBDateToViewDate(oneRow[5]);
                        
                            let strong = cTag('strong');
                            strong.innerHTML = created_on+' By '+user_name;
                        articleCont.appendChild(strong);
                        
                        if(publics>0){
                            let publicSpan = cTag('span',{ 'class': 'bgblack', 'style': "color: white; margin-left: 15px; padding: 5px;" });
                            publicSpan.innerHTML = Translate('Public');
                            articleCont.appendChild(publicSpan);
                        }
                        
                        if(user_id ===mainUserId && fromTable ==='notes' && editPer===1){
                            articleCont.append(' \u2003');
                                let editIcon = cTag('i',{ 'class': 'fa fa-edit','style': 'cursor:pointer;','click': ()=>AJget_notesPopup(publics,tableId) });
                            articleCont.appendChild(editIcon);
                        }
                        let pTag = cTag('p');
                        pTag.innerHTML = note;
                        articleCont.appendChild(pTag);
                    article.appendChild(articleCont);
                    noteslist.appendChild(article);
                });
            }
        }
	}
}

export async function AJget_notesPopup(publics=0,notes_id){
	let note = '';	
	let publicsShow = document.getElementById("publicsShow").value;
	const formhtml = cTag('div');
		let notesForm = cTag('form', {'action': "#", name: "frmnotes", id: "frmnotes", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
		let divFormGroup, textarea, inputField;
	if(publicsShow>0){
			const publicRow = cTag('div',{ "class":"flex", 'style': "align-items: center;", "align":"left"});
				let publicTitle = cTag('div',{ "class":"columnSM2 columnXS6"});
					let publicLabel = cTag('label',{ "for":"publics"});
					publicLabel.innerHTML = Translate('Public');
				publicTitle.appendChild(publicLabel);
			publicRow.appendChild(publicTitle);
				let publicField = cTag('div',{ "class":"columnSM3 columnXS6"});
					let selectPublic = cTag('select',{ "class":"form-control","name":"publics","id":"publics"});
						let noOption = cTag('option',{ "value":0});
						noOption.innerHTML = Translate('No');
					selectPublic.appendChild(noOption);
						let yesOption = cTag('option',{ "value":1});
						if(publics>0){
							yesOption.setAttribute('selected', 'selected');
						}
						yesOption.innerHTML = Translate('Yes');
					selectPublic.appendChild(yesOption);
				publicField.appendChild(selectPublic);
			publicRow.appendChild(publicField);
		notesForm.appendChild(publicRow);
	}
	else{
			inputField = cTag('input',{ "type":"hidden","name":"publics","id":"publics","value":0 });
		notesForm.appendChild(inputField);
	}
			let divCol12 = cTag('div',{ "class":"columnXS12","align":"left"});
						
	if(notes_id){
		const jsonData = {};
        jsonData['notes_id'] = notes_id;
        
        const url = "/Common/AJget_notesPopup";

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
                notesForm.querySelector('#publics').value = data.publics;
                        textarea = cTag('textarea',{ "required":"required", name: "note", id: "note", class: "form-control placeholder", rows: 5, placeholder: Translate('Enter Note')+"...", alt: Translate('Enter Note')+"..."});
                        textarea.innerHTML = data.note;
                        textarea.addEventListener('blur',sanitizer);
                    divCol12.appendChild(textarea);
                notesForm.appendChild(divCol12);

                    divFormGroup = cTag('div',{ "class":"flexSpaBetRow"});
                        let errorSpan = cTag('span',{ "class":"error_msg", id: "errmsg_note" });
                    divFormGroup.appendChild(errorSpan);
                notesForm.appendChild(divFormGroup);
                    inputField = cTag('input',{ "type":"hidden","name":"notes_id","id":"notes_id","value": data.notes_id });
                notesForm.appendChild(inputField);
            formhtml.appendChild(notesForm);
            
            popup_dialog600(Translate('Add New Note'), formhtml, Translate('Save'), AJsave_notes);
        
            setTimeout(function() {					
                if(document.getElementById("note")){
                    document.getElementById("note").focus();
                }
                callPlaceholder();
            }, 1000);
        }
	}
	else{
					textarea = cTag('textarea',{ "required":"required", name: "note", id: "note", class: "form-control placeholder", 'rows': 5, placeholder: Translate('Enter Note')+"...", alt: Translate('Enter Note')+"..."});
					textarea.innerHTML = note;
					textarea.addEventListener('blur',sanitizer);
				divCol12.appendChild(textarea);
			notesForm.appendChild(divCol12);

				divFormGroup = cTag('div',{ "class":"flexSpaBetRow"});
					let noteError = cTag('span',{ "class":"error_msg", id: "errmsg_note" });
				divFormGroup.appendChild(noteError);
			notesForm.appendChild(divFormGroup);
				inputField = cTag('input',{ "type":"hidden","name":"notes_id","id":"notes_id","value":notes_id });
			notesForm.appendChild(inputField);
		formhtml.appendChild(notesForm);
		
		popup_dialog600(Translate('Add New Note'), formhtml, Translate('Save'), AJsave_notes);
		
		setTimeout(function() {
			if(document.getElementById("note")){
			    document.getElementById("note").focus();
		    }
			callPlaceholder();
		}, 1000);
	}	
}

export async function AJsave_notes(hidePopup){
	let note = document.querySelector('#frmnotes #note');
	let oElement = document.getElementById('errmsg_note');
	oElement.innerHTML = "";
	if(note.value === ""){
		oElement.innerHTML = Translate('Missing note');
		note.focus();
		return(false);
	}
	else{
		actionBtnClick('.btnmodel', Translate('Saving'), 1);
		
		const jsonData = serialize('#frmnotes');
		jsonData['table_id'] = document.getElementById("table_idValue").value;
		jsonData['note_for'] = document.getElementById("note_forTable").value;
		
		
		const url = '/Common/AJsave_notes';

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
			if(data.savemsg==='Add'){
                triggerEvent('filter');				
				if(document.querySelector("#noteslist")){AJget_notesData();}
				hidePopup();
			}
			else if(data.returnStr=='errorOnAdding'){
				showTopMessage('error_msg', Translate('Error occured while adding new note! Please try again.'));
				actionBtnClick('.btnmodel', Translate('Add Note'), 0);
			}
			else{
				showTopMessage('error_msg', Translate('No changes / Error occurred while updating data! Please try again.'));
				actionBtnClick('.btnmodel', Translate('Add Note'), 0);
			}	
		} 
	}
	return false;
}

export function upload_dialog(title, frompage, fileprename, CBF){
	
	let picturepath, defaultImageSRC;
	let oldfilename = '';
	if(frompage==='invoice_setup' || frompage==='orders_print' || frompage==='homepage' || frompage==='all_pages_header'){
		if(document.querySelector("#"+frompage+"_picture div") && document.querySelector("#"+frompage+"_picture div").classList.contains('currentPicture')){
			oldfilename = document.querySelector("#"+frompage+"_picture div.currentPicture").querySelector("img").getAttribute("src");
		}
	}
	else if(frompage==='home_page_body'){
		if(document.querySelector("#"+frompage+"_picture div") && document.querySelector("#"+frompage+"_picture div").classList.contains('currentPicture')){
			oldfilename = document.querySelector("#"+frompage+"_picture div.currentPicture").querySelector("img").getAttribute("src");
			if( oldfilename==='/assets/images/pagebodyseg11.png'){oldfilename = '';}
		}
	}
	else if(frompage==='products'){
		picturepath = '';
		if(document.querySelector("#"+frompage+"_picture div") && document.querySelector("#"+frompage+"_picture div").classList.contains('currentPicture')){
			picturepath = document.querySelector("#"+frompage+"_picture div.currentPicture").querySelector("img").getAttribute("src");
		}
		defaultImageSRC = document.querySelector("#defaultImageSRC").value;				
		if(picturepath !=='' && picturepath !==defaultImageSRC){
			oldfilename = picturepath;
		}
	}	
	else if(frompage==='repairs'){
		fileprename += parseInt(document.getElementsByClassName("repairsPic").length+1)+'_';        
	}
	let formDialog = cTag('div');
		let form = cTag('form',{ 'name':`frmupload`,'id':`frmupload`,'action':`/Common/uploadpicture`,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
		form.appendChild(cTag('input',{ 'name':`filename`,'id':`filename`,'type':`file`, 'style': "display: block;" }));
		form.appendChild(cTag('input',{ 'name':`frompage`,'id':`frompage`,'type':`hidden`,'value':frompage }));
		form.appendChild(cTag('input',{ 'type':'hidden','name':`MAX_FILE_SIZE`,'value':`30000` }));
		form.appendChild(cTag('input',{ 'name':`fileprename`,'id':`fileprename`,'type':`hidden`,'value':fileprename }));
		form.appendChild(cTag('input',{ 'name':`oldfilename`,'id':`oldfilename`,'type':`hidden`,'value':oldfilename }));
		form.appendChild(cTag('span',{ 'id':`errmsg_filename`,'class':`errormsg` }));
	formDialog.appendChild(form);

	popup_dialog(
		formDialog,
		{
			title:title,
			width:400,
			buttons: {
				_Cancel: {
                    text:Translate('Cancel'),
					class: 'btn defaultButton', 'style': "margin-left: 10px;",
					click: function(hide) {
						hide();
					},
				},
				_Save:{
                    text:Translate('Upload'),
					class: 'btn saveButton btnupload', 'style': "margin-left: 10px;",
					click: formSubmit
				}
			}
		}
	);
	
	let targetval = '#'+frompage+'_picture';
	if(frompage==='repairs'){
		targetval = '';
	}
	let max_height;
	if(['invoice_setup', 'orders_print', 'homepage', 'all_pages_header'].includes(frompage)) max_height = 100;
	if(['home_page_body', 'fieldImages'].includes(frompage)) max_height = 600;
	if(frompage === 'products') max_height = 250;

	async function formSubmit(hidePopup){
		if(!(beforeSubmit()===false)){
			const url = form.getAttribute('action');

            fetchData(afterFetch,url,form,'formData');

            function afterFetch(data){
                if(data.savemsg==='noFile'){
                    showTopMessage('error_msg', Translate('There is no file sent'));				
                }
                else if(data.savemsg==='invalid'){
                    showTopMessage('error_msg', Translate('Possible file upload attack. Filename:')+' '+data.returnStr);
                }
                else if(data.savemsg==='largeFile'){
                    showTopMessage('error_msg', Translate('File size')+' '+data.returnStr+' '+Translate('issue. Please upload less than 6'));				
                }
                else if(data.savemsg==='fileNotSupported'){
                    showTopMessage('error_msg', Translate('NOT A JPG/JPEG/PNG/GIF FILE!!!! TRY AGAIN'));
                }
                else{
                    let target = document.querySelector(targetval);			
                    target.innerHTML = '';
                        let currentPicture = cTag('div',{ 'class':'currentPicture' });
                        currentPicture.appendChild(cTag('img',{ 'class':'img-responsive','src': data.returnStr,'style':`max-height:${max_height}px` }));
                    target.appendChild(currentPicture);
                    afterSuccess(hidePopup);
                }
            }
		} 
	}
	
	function beforeSubmit(){
		let uploadBtn = document.querySelector(".btnupload");
	   //check whether browser fully supports all File API
	   if (window.File && window.FileReader && window.FileList && window.Blob)
		{	
			if( !document.querySelector('#filename').value) //check empty input filed
			{	
				document.querySelector("#errmsg_filename").innerHTML = Translate('Missing picture');
				return false
			}
			
			let fsize = document.querySelector('#filename').files[0].size; //get file size
			let ftype = document.querySelector('#filename').files[0].type; // get file type
			
			//allow only valid image file types			
			switch(ftype)
			{
				case 'image/png': case 'image/gif': case 'image/jpeg': case 'image/pjpeg':
					break;
				default:
					document.querySelector("#errmsg_filename").innerHTML = ftype+' is '+Translate('Unsupported file type');
					return false
			}
			
			//Allowed file size is less than 1 MB (1048576)
			if(fsize>4194304) 
			{
				let bsize = cTag('b');
				bsize.innerHTML = bytesToSize(fsize);
				document.querySelector("#errmsg_filename").append(bsize,Translate('Too big Image file! <br />Please reduce the size of your photo using an image editor.'));
				return false
			}
			
			btnEnableDisable(uploadBtn,Translate('Uploading'),true);

			document.querySelector("#errmsg_filename").innerHTML = "";  
		}
		else
		{
			btnEnableDisable(uploadBtn,Translate('Uploading'),false);
			//Output error to older unsupported browsers that doesn't support HTML5 File API
			document.querySelector("#errmsg_filename").innerHTML = Translate('Please upgrade your browser, because your current browser lacks some new features we need!');
			return false;
		}
	}
	
	function afterSuccess(hidePopup){
		if(document.querySelector("#"+frompage+"_picture div").classList.contains('currentPicture')){
			if(frompage==='home_page_body'){
				document.getElementById("onePicture").value = document.querySelector('.currentPicture img').getAttribute('src');
			}
			document.querySelector(".currentPicture").addEventListener('mouseenter',function(){				
				picturepath = this.querySelector("img").getAttribute("src");
				if(frompage==='invoice_setup' || frompage==='homepage' || frompage==='all_pages_header'){
					this.appendChild(cTag('div',{'class':"deletedicon",'click': ()=> AJremove_Picture(picturepath,frompage)}));
				}
				else if(frompage==='home_page_body'){
					triggerEvent('preview');
				}
				else if(frompage==='products'){
					defaultImageSRC = document.querySelector("#defaultImageSRC").value;
					if(picturepath !==defaultImageSRC){
						this.appendChild(cTag('div',{'class':"deletedicon",'click': ()=> AJremove_Picture(picturepath,frompage)}));
					}
				}		
			});
			document.querySelector(".currentPicture").addEventListener('mouseleave',function(){
				if(document.querySelector("#"+frompage+"_picture div.deletedicon")){
					this.querySelector(".deletedicon").remove();
				}
			});			
		}
		
		if(frompage==='all_pages_header'){
			picturepath = document.querySelector(".currentPicture").querySelector("img").getAttribute("src");
			document.querySelector("#web_logo").value = picturepath;
            triggerEvent('preview');
		}
		hidePopup();
	}
}

export function showMessAndRedi(title, formhtml){
	let redirectTo = document.querySelector("#redirectTo").value;
	let dialog = cTag('div');
	if(typeof formhtml==='string') dialog.innerHTML = formhtml;
	else dialog.appendChild(formhtml);
	
	popup_dialog(
		dialog,
		{
			title:title,
			width:400,
			buttons: {
				'Ok': {
					text: 'Ok', class: 'btn saveButton', 'style': "margin-left: 10px;", click: function(hide) {
						window.location = redirectTo;
						hide();
						activeLoader();
					},
				}
			}
		},
        false
	);
}

export function getDeviceOperatingSystem() {
	let userAgent = navigator.userAgent;

	if(!userAgent) return 'unknown';
	else if(/linux|android/i.test(userAgent)) return 'Android';
	else if(/iPad|iPhone|iPod/i.test(userAgent)) return 'iOS';
	//latest tablet has same info as iMac has, so to differentiate between two checking if screen is touch enabled
	else if(/macintosh/i.test(userAgent) && navigator.maxTouchPoints>0) return 'iOS'; 

	return 'unknown';
}

export function AJarchive_tableRow(tablename, idname, idvalue, description, publishname, redirect){
	if(idvalue>0){
		let message = cTag('div');
		message.append(Translate('Are you sure you want to archive this information?')+' ('+description+')?');		
		confirm_dialog(Translate('Archive Data'), message, (hidePopup)=>{
            const jsonData = {"tablename":tablename,"tableidname":idname, "tableidvalue":idvalue, "publishname":publishname};
            const url = "/Common/AJarchive_tableRow";

            fetchData(afterFetch,url,jsonData);

            function afterFetch(data){
                if(data.returnStr==='archive-success'){
                    if(redirect !==''){
                        window.location = redirect;
                    }
                    else{
                        triggerEvent('filter');
                        triggerEvent('reset');
                    }
                }		
                else{
                    showTopMessage('error_msg',Translate('Error occured while archiving information! Please try again.'));
                }		
                hidePopup();
            }
        });
	}
}

export function AJremove_tableRow(tableName, tableIdValue, description, redirectURI, afterRemoveCBF){
	let table_id = 0;
	if(document.querySelector("#table_id")){
		table_id = document.querySelector("#table_id").value;
	}
	let popUpHml = document.createElement('div');
    popUpHml.appendChild(cTag('input',{ 'type':"hidden", 'id':'tableName', 'value':tableName }));
    popUpHml.appendChild(cTag('input',{ 'type':"hidden", 'id':'table_id', 'value':table_id }));
    popUpHml.appendChild(cTag('input',{ 'type':"hidden", 'id':'tableIdValue', 'value':tableIdValue }));
    popUpHml.appendChild(cTag('input',{ 'type':"hidden", 'id':'description', 'value':description }));
    popUpHml.appendChild(cTag('input',{ 'type':"hidden", 'id':'redirectURI', 'value':redirectURI }));
	popUpHml.append(Translate('Are you sure want to remove this information')+' ('+description+')?');
	confirm_dialog(Translate('Remove')+' '+description, popUpHml, (hidePopup)=>confirmAJremove_tableRow(hidePopup,afterRemoveCBF));
}

export async function confirmAJremove_tableRow(hidePopup,afterRemoveCBF){
	let tableName = document.querySelector("#tableName").value;
	let tableIdValue = document.querySelector("#tableIdValue").value;
	let description = document.querySelector("#description").value;
	let redirectURI = document.querySelector("#redirectURI").value;

	const jsonData = {"tableName":tableName, "tableIdValue":tableIdValue, "description":description};
	const url = '/Common/AJremove_tableRow/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.savemsg==='Done'){
			if(redirectURI===''){
				let table_id = document.querySelector("#table_id").value;
				if(tableName === 'forms'){
					location.reload();
				}
				else if(tableName === 'forms_data'){
                    afterRemoveCBF(table_id);
				}
				else if(tableName === 'digital_signature'){
					document.getElementById("ff"+description).value = '';
                    afterRemoveCBF();	
				}
				else if(tableName === 'time_clock'){					
                    triggerEvent('filter');
					afterRemoveCBF();
				}
				else if(tableName === 'petty_cash'){
                    afterRemoveCBF();
                }
				else{
                    triggerEvent('filter');
				}
			}
			else{
				window.location = redirectURI;
			}
			hidePopup();
			showTopMessage('success_msg',description+' '+Translate('Data removed successfully.'));
		}
		else{
			showTopMessage('error_msg',Translate('Could not remove information'));
		}
	}
}

export async function AJget_modelOpt(eventObj = false){
	if(eventObj){
		let brand = eventObj.target.value;
		if(brand ===''){
			eventObj.target.focus();
			return false;
		}
		let modelObj = document.getElementById("model");
		let model = modelObj.value;
		
        
		const jsonData = {brand};
        const url = '/'+segment1+'/AJget_modelOpt';

        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            modelObj.innerHTML = '';
            if(data.modelOpts.length===0){
                let option = cTag('option',{ 'value': '' });
                option.innerHTML = '';					
                modelObj.appendChild(option);
            }
            setOptions(modelObj, data.modelOpts, 0, 1);
            if(modelObj.style.display === 'none'){
                modelObj.style.display = '';
            }

			if( data.modelOpts.includes(model)) document.getElementById("model").value = model;            
        }		
	}
}


export function runImageScript(){
	let fieldImagesID = document.getElementsByClassName("fieldImages");
	for(let l=0; l<fieldImagesID.length; l++){
		let imagePath = fieldImagesID[l].value;
		let showing_order = fieldImagesID[l].getAttribute('name').replace('ff', '');
		if(imagePath===''){
			let UploadImageID = document.querySelector("#UploadImageID"+showing_order);
			UploadImageID.innerHTML = '';
				let uploadButton = cTag('button',{ 'type':"button",'class':"uploadButton", name: "open"});
				uploadButton.addEventListener('click', function (){uploadImage(showing_order);});
				uploadButton.innerHTML = Translate('Upload');
			UploadImageID.appendChild(uploadButton);
		}
		else{
			showImage(imagePath, showing_order);
		}
	}
}

//======Copy Clipboard=======//
					
export function copyToClipboardMsg(elem) {
	let succeed = copyToClipboard(elem);
	let message;
	if (!succeed) {
		message = Translate('Copy not supported or blocked.  Press Ctrl+c to copy.');
	} else {
		message = Translate('Text copied to the clipboard.');
	}

	let msgContainer = cTag('div',{'class':"innerContainer", 'style':'position:fixed; left:50%; top:0px; z-index:99999; margin-left:-250px; width:500px;' });
	msgContainer.innerHTML = message;
	document.body.appendChild(msgContainer);
	setTimeout(() => msgContainer.remove(), 2000);	
}

export function copyToClipboard(elem) {
	let target;
	  // create hidden text element, if it doesn't already exist
	let targetId = "_hiddenCopyText_";
	let isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
	let origSelectionStart, origSelectionEnd;
	if (isInput) {
		// can just use the original source element for the selection and copy
		target = elem;
		origSelectionStart = elem.selectionStart;
		origSelectionEnd = elem.selectionEnd;
	} else {
		// must use a temporary form element for the selection and copy
		target = document.getElementById(targetId);
		if (!target) {
			let target = cTag("textarea");
			target.style.position = "absolute";
			target.style.left = "-9999px";
			target.style.top = "0";
			target.id = targetId;
			document.body.appendChild(target);
		}
		target.textContent = elem.textContent;
	}
	// select the content
	let currentFocus = document.activeElement;
	target.focus();
	target.setSelectionRange(0, target.value.length);
	
	// copy the selection
	let succeed;
	try {
		  succeed = document.execCommand("copy");
	} catch(e) {
		succeed = false;
	}
	// restore original focus
	if (currentFocus && typeof currentFocus.focus === "function") {
		currentFocus.focus();
	}
	
	if (isInput) {
		// restore prior selection
		elem.setSelectionRange(origSelectionStart, origSelectionEnd);
	} else {
		// clear temporary content
		target.textContent = "";
	}
	return succeed;
}

//======popup_dialog======
export function popup_dialog(popupContent,properties,haveCloseButton=true){
	// styles properties------------------
	let styles = {
        popup_container:{
            'position':'fixed',
            'display':'flex',
            'width':'100%',            
            'top':0,
            'left':0,
            'justify-content': 'center',
			'align-items': 'flex-start',
			'z-index':'1'
        },
		overlay:{
			'width':'100%',
            'height':'100%',
			'position':'fixed',
			'background-color': 'rgba(0, 0, 0, .5)',
			'top':0,
			'left':0
		},
		popup:{
			'padding':'.2em',
			'background': '#ffffff',
			'border': '1px solid #d3d3d3',
			'border-radius': '4px',
			'font-family': 'Verdana,Arial,sans-serif',
            'transform':'translateY(5vh)',
			'position': 'absolute',
			'max-height':'85vh',
			'overflow-y':'auto',
			'z-index':'100',
		},
		popup_header:{
			'background-color': '#d6d6d6',
			'border':  '1px solid #aaaaaa',
			'border-radius': '3px',
			'cursor': 'move',
			'position': 'relative',
			'padding': '5px 10px',
            'text-align':'left'
		},
		popup_close_button:{
			'cursor': 'pointer',
			'border': 'none',
			'width': '35px',
			'height': '35px',
			'position': 'absolute',
			'right': '.3em',
			'top':' 50%',
			'transform': 'translateY(-50%) scale(.7)',
			'border': 'none',
		},
		popup_footer:{
			'margin-top': '.5em',
			'border-top': '1px solid #aaaaaa',
			'padding': '0 10px',
			'display': 'flex',
			'justify-content': 'flex-end',
		},
		popup_footer_button:{
			'margin': '.5em .4em .5em 0',
		}
   	}

	//query for number-fields and format their value
	popupContent.querySelectorAll('[data-format]').forEach(numberField=>{
		const format = numberField.getAttribute('data-format');
		if(['d.dd','d.ddd'].includes(format) && Number.isInteger(Number(numberField.value))){
			if(format==='d.dd') numberField.value += '.00';
			else if(format==='d.ddd') numberField.value += '.000';
		}
		else if(format==='d.dd' && numberField.value.split('.')[1].length<2) numberField.value += '0';
		else if(format==='d.ddd' && numberField.value.split('.')[1].length===1) numberField.value += '00';
		else if(format==='d.ddd' && numberField.value.split('.')[1].length===2) numberField.value += '0';
	})

	//some default style
	popupContent.style.padding = '10px 15px';
	popupContent.style.overflow = 'auto';
	
  	// set necessary functionality,if popup has tabbar
	if(popupContent.querySelector('#tabs ul li a')){
        createTabs(popupContent.querySelector('#tabs'));
	}

	// basic popup template
    let popup_container = cTag('div',{class:'popup_container'});
    setStyles(popup_container,styles.popup_container);
		let popup = cTag('div',{id:'popup'});
		setStyles(popup,styles.popup);
		properties.width && window.screen.width>properties.width? setStyle(popup,'width',`${properties.width}px`):setStyle(popup,'max-width','90vw');
		properties.height? setStyle(popup,'height',`${properties.height}px`):setStyle(popup,'height','auto');
			let header = cTag('header',{id:'popup_header'});
			setStyles(header,styles.popup_header);
				let headerTitle = cTag('h3');
				headerTitle.innerText = properties.title;
				setStyles(headerTitle,{'margin':0,'font-size':'1.1em','font-weight':'bold'});
			header.appendChild(headerTitle);
			if(haveCloseButton){
					let closeBtn = cTag('button',{id:'popup_close_button'});
					closeBtn.addEventListener('click',hide_Popup);
					setStyles(closeBtn,styles.popup_close_button);
				header.appendChild(closeBtn)
			}
		popup.appendChild(header);        
		popup.appendChild(popupContent);
			let footer = cTag('footer',{id:'popup_footer'});
			setStyles(footer,styles.popup_footer);
				let btnset = cTag('div',{id:'buttonset'})
				for (let buttonskey in properties.buttons) {
					let {...btnProperties} = properties.buttons[buttonskey];
					let btn = cTag('button');
					btn.innerText = btnProperties.text;
					for (const btnPropertieskey in btnProperties) {
						if(typeof btnProperties[btnPropertieskey] === 'function') btn.addEventListener(btnPropertieskey,()=>btnProperties[btnPropertieskey](hide_Popup));
						else btn.setAttribute(btnPropertieskey,btnProperties[btnPropertieskey]);
					}
					btn.classList.add('popup_footer_button');
					setStyles(btn,styles.popup_footer_button);
					btnset.appendChild(btn);
				}
			footer.appendChild(btnset);
		popup.appendChild(footer);       
    popup_container.appendChild(popup);

		//popup-overlay
		let overlay = cTag('div',{'id':'popup_overlay'});
		setStyles(overlay,styles.overlay);
	popup_container.appendChild(overlay)
	document.body.appendChild(popup_container);
	

	//add necessery functionality and styling on popupContent.....
	setStyles(popupContent,{'padding':'10px 15px','overflow':'auto'});
	popupContent.close = ()=>hide_Popup(popupContent);
	if(popupContent.style.display === 'none'){
		popupContent.style.display = '';
	}
	makeDraggable(header);

	document.addEventListener('keydown',listenOnHittingEnterKey);
	function listenOnHittingEnterKey(event){
		let btn = btnset.querySelector('.saveButton');
        if(haveCloseButton && event.which===27) hide_Popup();
		else if(event.target.nodeName!=='TEXTAREA' && event.which===13 && btn){
            event.preventDefault();
            btn.click();
        } 
	}

	function makeDraggable(draggableSection){
		let lastPosition_x = 0;
		let lastPosition_y = 0;
		let newPosition_x = 0;
		let newPosition_y = 0;

		let popupContainer = draggableSection.parentNode;

		draggableSection.addEventListener('mousedown',function (event){
			event.preventDefault();
			newPosition_x = event.x;
			newPosition_y = event.y;
			document.addEventListener('mousemove',drager);
		})
		draggableSection.addEventListener('mouseup',function(){
			document.removeEventListener('mousemove',drager);
		})
		function drager(event){
			event.preventDefault();

			lastPosition_x = newPosition_x;
			lastPosition_y = newPosition_y;
			newPosition_x = event.x;
			newPosition_y = event.y;

			popupContainer.style.top = popupContainer.offsetTop - (lastPosition_y-newPosition_y) + 'px';
			popupContainer.style.left = popupContainer.offsetLeft - (lastPosition_x-newPosition_x) + 'px';
			
		}
	}

	function hide_Popup(){
		document.removeEventListener('keydown',listenOnHittingEnterKey);		
		popup_container.remove();
	}

	function setStyle(node,property,value){
		node.style[property] = value;
	}
	
	function setStyles(node,stylesObj){
		for (const property in stylesObj) {
			node.style[property] = stylesObj[property];
		}
	}

}

export function popup_dialog600(title, popup_content, actionbutton, callbackfunction){
    popup_dialog(
		popup_content,
		{
			title:title,
			width:600,
			buttons: {
				_Cancel: {
                    text:Translate('Cancel'),
					class: 'btn defaultButton', 'style': "margin-left: 10px;", click: function(hide) {
						hide();
					},
				},
				actionbutton:{
                    text:actionbutton,
					class: 'btn saveButton btnmodel', 'style': "margin-left: 10px;", 
					click: callbackfunction,
				}
			}
		}
	);
}

export function popup_dialog1000(title, popup_content, callbackfunction){    
    popup_dialog(
		popup_content,
		{
			title:title,
			width:1000,
			buttons: {
				_Cancel: {
                    text:Translate('Cancel'),
					class: 'btn defaultButton', 'style': "margin-left: 10px;", click: function(hide) {
						hide();
					},
				},
				_Save:{
                    text:Translate('Save'),
					class: 'btn saveButton btnmodel', 'style': "margin-left: 10px;", 
					click: callbackfunction,
				}
			}
		}
	);
}

//======date-picker_dialog======
export function date_picker_dialog(node,callback){ 
    // generating an unique ID to assign
    let dialogID = '';
    for(let i=0;i<10;i++){
        dialogID+=String.fromCharCode(9*(parseInt(Math.random()*10))+40);
    }

    // styles
    const styles = {
        datePicker:{
            'background':'#ffffff',
            'width':'17em',
            'height':'auto',
            'padding':'.3em',
            'border':'1px solid #dddddd',
            'border-radius':'3px',
            'overflow':'hidden',
            'position':'absolute',
            'transition':'0.3s',
			'z-index': 99999,
            'opacity':0
        },
        header:{
            'display':'flex',
            'background':'#e9e9e9',
            'border':'1px solid #e9e9e9',
            'border-radius':'3px',
            'align-items':'center'
        },
        title:{
            'padding': '.3em 0',
            'font-family': 'Verdana,Arial,sans-serif',
            'font-size': '1.1em',
            'flex-grow':1,
            'text-align':'center',
            'font-weight':'bold',
        },
        pre_next:{
            'width': '27px',
            'text-align': 'center',
            'padding':'8px 0',
            'cursor':'pointer',
            'transition':'0.2s',
            'border':'transparent',
        },
        unhovered_pre_next:{
            'color': 'black',
            'background': 'transparent',
        },
        hovered_pre_next:{
            'color': 'white',
            'background': '#646464',
        },
        calendar:{
            'width':'100%',
        },
        dateCell:{
            'display':'flex',
            'justify-content':'flex-end',
            'align-items':'flex-end',
            'text-align':'center',
            'cursor':'pointer',
            'padding':'0.2em'
        },
        unhoveredDateCell:{
            'background':'#eeeeee',
            'border':'1px solid #ddd',
        },
        hoveredDateCell:{
            'background':'#d8d8d8',
            'border':'1px solid gray',

        },
        today:{
            'background':'#fcfbf2',
            'border':'1px solid #fcefa1'
        },
        selectedDate:{
            'background':'#ffffff',
            'border':'1px solid gray'
        }
    }
    const Months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const days = [
            {id:'Su',title:'Sunday'},
            {id:'Mo',title:'Monday'},
            {id:'Tu',title:'Tuesday'},
            {id:'We',title:'Wednesday'},
            {id:'Th',title:'Thursday'},
            {id:'Fr',title:'Friday'},
            {id:'Sa',title:'Saturday'}
        ];
    let selectedDate={};
    let today = new Date();
    let Month = today.getMonth();
    let Year = today.getFullYear();
    today = {date:today.getDate(),month:Month,year:Year};
    let datePicker;
    let title;
    let calendar;

    node.addEventListener('click',dialogMaker);
    
    node.addEventListener('keydown',(event)=>{
		if(event.which === 13 && datePicker){
            event.preventDefault();
            datePicker.remove();
            dialogMaker();
        }
    });

    function dialogMaker(){
        if(node.value && validDate(node.value)){
            if(calenderDate.toLowerCase()==='mm/dd/yyyy'){
                let [mm,dd,yy] = node.value.split('/').map(item=>parseInt(item));
				selectedDate = {date:dd,month:mm,year:yy};
				mm = mm-1;
				[Month,Year] = [mm,yy*1];
            }
            else{
                let [dd,mm,yy] = node.value.split('-').map(item=>parseInt(item));
				selectedDate = {date:dd,month:mm,year:yy};
				mm = mm-1;
				[Month,Year] = [mm,yy*1];
            }
        }
        dialog();
    }

    function dialog(){
        if(document.getElementById(dialogID)) return;
        // basic layout     
        datePicker = cTag('div',{id:dialogID});
        setStyles(datePicker,styles.datePicker);
        setStyles(datePicker,{'top':node.offsetHeight+node.getBoundingClientRect().top+window.scrollY+'px','left':node.getBoundingClientRect().left+window.scrollX+'px',});
            let header = cTag('div');
            setStyles(header,styles.header);
                let previous = cTag('span',{class:'fa fa-chevron-left glyphicon glyphicon-chevron-left'});
                setStyles(previous,styles.pre_next);
                setStyles(previous,styles.unhovered_pre_next);
                setHoverStyle(previous,styles.hovered_pre_next,styles.unhovered_pre_next);
                previous.addEventListener('click',()=>{
                    setMonthYear(-1);
                    calendarMaker();
                });
            header.appendChild(previous);
                title = cTag('span');
                setStyles(title,styles.title);
            header.appendChild(title);
                let next = cTag('span',{class:'fa fa-chevron-right glyphicon glyphicon-chevron-right'});
                setStyles(next,styles.pre_next);
                setStyles(next,styles.unhovered_pre_next);
                setHoverStyle(next,styles.hovered_pre_next,styles.unhovered_pre_next);
                next.addEventListener('click',()=>{
                    setMonthYear(1);
                    calendarMaker();
                });
            header.appendChild(next);
        datePicker.appendChild(header);
            calendar = cTag('table');
            calendarMaker(Month,Year);
            setStyles(calendar,styles.calendar);            
        datePicker.appendChild(calendar);
        document.body.appendChild(datePicker);
        setTimeout(() => {
            setStyle(datePicker,'opacity',1);
        },0);
        // hide when clicked anywhere on document other than the dialog
        setTimeout(() => {
            document.addEventListener('mousedown',closeDatePickerOnMouseDown);
        }, 0);
    }

    function closeDatePickerOnMouseDown(event){
        if(!(datePicker.contains(event.target)||node===event.target)) {
            closeDatePicker();
            setTimeout(() => {
                document.removeEventListener('mousedown',closeDatePickerOnMouseDown);
            }, 500);
        }
    }

    function calendarMaker(){
        title.innerText = `${Months[Month]} ${Year}`;
        calendar.innerHTML = '';
        getDays();
        getDates();
    }
    function getDays(){
        if(calendar.querySelector('thead')) return;
        let dayBar = cTag('thead');
        setStyles(dayBar,styles.dayBar);
        days.forEach(item=>{
                let day = cTag('th',{title:item.title});
                setStyle(day,'padding','2px');
                day.innerText = item.id;
                setStyles(day,styles.day);
            dayBar.appendChild(day);
        });
        calendar.appendChild(dayBar);
    }
    function getDates(){
        let date = new Date(Year, Month, 1);// initialize the date as a first-day of Month
        let dateRows = [];
        let dateCellsCount = 0;
        for (let i=0; i<6; i++){ // Rows of dates could be at most 6
            let row = cTag('tr');
            dateRows[i] = row;
        }
        for(let i=0;i<date.getDay();i++){
            let td = cTag('td');
            dateRows[parseInt(dateCellsCount/7)].appendChild(td);
            dateCellsCount++;
        }
        while (date.getMonth() === Month) {
            let td = cTag('td',{'data-month':Month,'data-year':Year});
            setStyle(td,'padding','1px');
                let span = cTag('span');
                setStyles(span,styles.dateCell);
                if(date.getDate()===selectedDate.date && date.getMonth()+1===selectedDate.month && date.getFullYear()===selectedDate.year){
                    setStyles(span,styles.selectedDate);
                }
                else if(date.getDate()===today.date && date.getMonth()===today.month && date.getFullYear()===today.year){
                    setStyles(span,styles.today);
                }
                
                else setHoverStyle(span,styles.hoveredDateCell,styles.unhoveredDateCell);
                span.innerText = date.getDate();
                span.addEventListener('click',function(){
                    let date = this.innerText*1;
                    date = date<10?'0'+date:date;
                    let month = 1+this.parentNode.getAttribute('data-month')*1;
                    month = month<10?'0'+month:month;
                    let year = this.parentNode.getAttribute('data-year');
                    callback({date,month,year},closeDatePicker);
                    selectedDate = {date,month,year};
                })
            td.appendChild(span);
            dateRows[parseInt(dateCellsCount/7)].appendChild(td);
            dateCellsCount++;
            date = new Date(Year, Month,date.getDate()+1)
        }
        dateRows.forEach(row=>{
            calendar.appendChild(row);
        })
    }
    function closeDatePicker(){
        setStyle(datePicker,'opacity',0);
        setTimeout(() => {
            datePicker.remove();
        }, 500);
    }
    function setMonthYear(pre_next){
        if(pre_next===1){
            if(Month<11) Month+=1;
            else{
                Month = 0;
                Year +=1 
            }
        }
        else if(pre_next===-1){
            if(Month>0) Month-=1;
            else{
                Month = 11;
                Year -=1 
            }
        }
        else{
            console.log('call setMonthYear with 1 or -1 only')
        }
    }
    function setStyle(node,property,value){
        node.style[property] = value;
    }       
    function setStyles(node,stylesObj){
        for (const property in stylesObj) {
            node.style[property] = stylesObj[property];
        }
    }
    function setHoverStyle(node,hoverStyle,unhoverStyle){
        setStyles(node,unhoverStyle);
        node.addEventListener('mouseenter',()=>{setStyles(node,hoverStyle)})
        node.addEventListener('mouseleave',()=>{setStyles(node,unhoverStyle)})
    }
}

export function date_picker(fieldName,cbf){
	let dateVal;
	if(fieldName.includes('#')){
		let field = document.querySelector(fieldName);
		date_picker_dialog(field,({date,month,year},close)=>{
			close();
			if(calenderDate.toLowerCase()==='dd-mm-yyyy'){    
				dateVal = date+'-'+month+'-'+year;    
			}    
			else{    
				dateVal = month+'/'+date+'/'+year;    
			}    
			field.value = dateVal;
			if(typeof cbf==='function') cbf(date,month,year);
		})
	}
	else{
		document.querySelectorAll(fieldName).forEach(field=>{
			date_picker_dialog(field,({date,month,year},close)=>{
				close();
				if(calenderDate.toLowerCase()==='dd-mm-yyyy'){    
					dateVal = date+'-'+month+'-'+year;    
				}    
				else{    
					dateVal = month+'/'+date+'/'+year;    
				}    
				field.value = dateVal;
				if(typeof cbf==='function') cbf(date,month,year);
			});
		});
	}
}

//======daterange-picker-dialog====
export function daterange_picker_dialog(node,{start_Date,end_Date,submit,cancel}={},canBeEmpty=false){
    let daterangepicker;
    let rangesContainer;
    let arrow;
    let startDatepicked = true;//identifier of startDate picking
    let startDate = start_Date;
    let endDate = end_Date;    
    
    if(node.value ){
        if(node.value.split(' - ').length<2) node.value = '';
        else dateExtractor();
    }
	
	if((startDate===false || endDate===false) && window.location.search && new URLSearchParams(window.location.search).get(node.getAttribute('name'))){
		[startDate,endDate] = new URLSearchParams(window.location.search).get(node.getAttribute('name')).split(' - ')
	}
	
	let selectedOption = startDate&&endDate? getSelectedOption(): Translate('Last 7 Days');
	let selectedDateRange;

    const Months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const days = [
            {id:'Su',title:'Sunday'},
            {id:'Mo',title:'Monday'},
            {id:'Tu',title:'Tuesday'},
            {id:'We',title:'Wednesday'},
            {id:'Th',title:'Thursday'},
            {id:'Fr',title:'Friday'},
            {id:'Sa',title:'Saturday'}
        ];    
    const styles = {
        daterangepicker:{
            'background':'white',
            'height':'auto',
            'padding':'.3em',
            'border':'1px solid #D3D3D3',
            'border-radius':'3px',
            'position':'absolute',
            'transition':'0.3s',
            'display':'flex',
            'justify-content': 'space-between',
			'text-align': 'left',
        },
        daterangepickerLT700:{
            'flex-direction':'column',
			'gap':'10px'
        },        
        daterangepickerGT700:{
            'flex-direction':'row',
        },        
        rangesContainer:{
            'overflow':'hidden',
            'position':'relative',
            'padding':'0 5px',
        },
        ranges:{
            'list-style': 'none',
            'margin': 0,
            'padding': 0,
            'min-width':'160px'
        },
        rangeOption:{
            'font-size': '13px',				
            'border-radius': '4px',
            'padding': '3px 12px',
            'margin-bottom': '8px',
            'cursor': 'pointer',
        },
        unhoveredRangeOption:{
            'background-color': '#f5f5f5',
            'border': '1px solid #f5f5f5',
            'color': '#08c',
        },
        hoveredRangeOption:{
            'background-color': '#08c',
            'border': '1px solid #08c',
            'color': '#fff',
        },
        submit:{				
            'border': 'none',
            'padding': '5px 10px',
            'border-radius': '3px',
            'color': '#fff',
            'border-color': '#122b40',
            'cursor':'pointer',
        },
        unhoveredSubmit:{
            'background-color': '#2475bc',
        },
        hoveredSubmit:{
            'background-color': '#204d74',
        },
        cancel:{
            'color': '#333',
            'border': '1px solid #ccc',
            'padding': '5px 10px',
            'border-radius': '3px',
            'cursor':'pointer',
            'margin-left':'3px'
        },
        unhoveredCancel:{
            'background-color': 'white',
        },
        hoveredCancel:{
            'background-color': '#d4d4d4',
        },
        arrow:{
            'position':'absolute',

        },
        arrowBottom:{
            'border': '8px solid transparent',
            'border-bottom-color': '#D3D3D3',
            'position':'absolute',
            'top':0,
            'left':0
        },
        arrowUp:{
            'border': '7px solid transparent',
            'border-bottom-color': 'white', 
            'position':'absolute',
            'top':'2px',
            'left':'1px'
        },
        daterangepickerInputContainer:{
            'display': 'flex',
            'position': 'relative',
            'align-items': 'center',
        },
        daterangepickerInput:{
            'flex-grow': 1,
            'padding': '5px 0px 5px 25px',
            'border': '1px solid #08c',
            'border-radius': '4px',
        },
        calendarIcon:{
            'position': 'absolute',
            'left': '8px',
            'top':'8px'
        },
        header:{
            'display':'flex',
            'padding':'5px 0',
            'justify-content':'space-around',
            'align-items':'center'
        },
        title:{
            'padding': '.3em 0',
            'font-family': 'Verdana,Arial,sans-serif',
            'font-size': '1.1em',
            'flex-grow':1,
            'text-align':'center',
            'font-weight':'bold',
        },
        pre_next:{
            'padding': '5px 7px',
            'text-align': 'center',
            'font-weight':'bold',
            'cursor':'pointer',
            'border':'transparent',
            'border-radius':'2px'
        },
        unhovered_pre_next:{
            'background': '#fff',
        },
        hovered_pre_next:{
            'background': '#eee',
        },
        datePicker:{
            'width':'100%',           
        },
        calendar:{
            'width':'100%',
            'border-collapse':'collapse'
        },
        preNextDateCell:{
            'text-align':'center',
            'border-radius':'3px',
            'padding':'1px 5px',
            'color':'#7d7d7d',
            'background':'inherit'
        },
        dateCell:{
            'display':'block',
            'text-align':'center',
            'border':'transparent',
            'border-radius':'3px',
            'cursor':'pointer',
            'padding':'4px'
        },
        unhoveredDateCell:{
            'background':'#fff',
        },
        hoveredDateCell:{
            'background':'#eee',
        },
        startDateCell:{
            'background':'#357ebd',
            'border-radius':'3px 0 0 3px',
            'color':'white'
        },
        endDateCell:{
            'background':'#357ebd',
            'border-radius':'0 3px 3px 0',
            'color':'white'
        },
        dateCellIntoRange:{
            'background':'#d1e8f2',
            'border-radius':0
        }
    }

    node.addEventListener('click',()=>{
        if(daterangepicker){
            if(daterangepicker.style.display === 'none') toggleDateRangePicker();
            return;
        };
    });
    node.addEventListener('keydown',(event)=>{
		if(event.which === 13){
			event.preventDefault();
			if(node.value==='' && canBeEmpty) return;
			if(node.value.split(' - ').length<2) node.value = startDate+' - '+endDate;
			dateExtractor();
			selectedOption = getSelectedOption();
			daterangepicker.remove();
			daterangeDialog();
			daterangepicker.style.display = 'none';
			toggleDateRangePicker();
		}
	});


    //initial call
    daterangeDialog();
    toggleDateRangePicker();

    function daterangeDialog(){
        daterangepicker = cTag('div',{'id':'daterangepicker'});
        setStyles(daterangepicker,styles.daterangepicker);
            rangesContainer = cTag('div');            
            setStyles(rangesContainer,styles.rangesContainer);
                let ulRanges = cTag('ul');
                setStyles(ulRanges,styles.ranges);
                [
                    Translate('Today'),Translate('Yesterday'),Translate('Last 7 Days'),Translate('Last 30 Days'),Translate('This Month'),Translate('Last Month'),Translate('Custom')
                ].forEach(item=>{
                        let liRange = cTag('li',{ 'data-range-key':item });
                        liRange.innerHTML = item;
                        if(item!==Translate('Custom')){
                            liRange.addEventListener('click',()=>{
                                place_dateRange(item);
                                getCalendar(false,true);
                                if(daterangepicker.querySelector('.calendar').style.display!=='none'){
                                    setTimeout(() => {
                                        toggleCalendar();
                                    }, 300);
                                }
                                toggleDateRangePicker();
                            });
                            liRange.addEventListener('mouseover',function(){
                                daterangepicker.querySelector('.daterangepicker_input input[name="startDate"]').value = get_dateRange(item)[0];
                                daterangepicker.querySelector('.daterangepicker_input input[name="endDate"]').value = get_dateRange(item)[1];
                            })
                            liRange.addEventListener('mouseleave',function(){
                                daterangepicker.querySelector('.daterangepicker_input input[name="startDate"]').value = selectedDateRange.split(' - ')[0];
                                daterangepicker.querySelector('.daterangepicker_input input[name="endDate"]').value = selectedDateRange.split(' - ')[1];
                            })
                        }
                        else{
                            liRange.addEventListener('click',()=>{
                                toggleCalendar();
                            });
                        }
					ulRanges.appendChild(liRange);
                });
            rangesContainer.appendChild(ulRanges);
				let button;
                const buttonDiv = cTag('div');
                    let submitButton = cTag('button',{ 'type':`button`,'id':'submit','class':'applyBtn' });
                    submitButton.innerHTML = Translate('Submit');
                    setStyles(submitButton,styles.submit);
                    setHoverStyle(submitButton,styles.hoveredSubmit,styles.unhoveredSubmit);
					if(submit){
						submitButton.addEventListener('click',()=>{
							// submit(node,selectedDateRange,toggleDateRangePicker);
                            node.value = selectedDateRange;
							toggleDateRangePicker();
                            submit();
						});
					}else{
						submitButton.addEventListener('click',()=>{
							node.value = selectedDateRange;
							toggleDateRangePicker();
						});
					}
				buttonDiv.appendChild(submitButton);

                    let clearButton = cTag('button',{ 'type':`button` });
                    clearButton.innerHTML = Translate('Clear');
                    setStyles(clearButton,styles.cancel);
                    setHoverStyle(clearButton,styles.hoveredCancel,styles.unhoveredCancel);
					if(cancel){
						clearButton.addEventListener('click',()=>{
							cancel(node,selectedDateRange,toggleDateRangePicker);
						});
					}else {
						clearButton.addEventListener('click',toggleDateRangePicker);
					}
				buttonDiv.appendChild(clearButton);
            rangesContainer.appendChild(buttonDiv);
        daterangepicker.appendChild(rangesContainer);
        // set the top arrow----------
            arrow = cTag('div');
            setStyles(arrow,styles.arrow);
                let arrowBottom = cTag('span');
                setStyles(arrowBottom,styles.arrowBottom);
            arrow.appendChild(arrowBottom);
                let arrowUp = cTag('span');
                setStyles(arrowUp,styles.arrowUp);
            arrow.appendChild(arrowUp);
        daterangepicker.appendChild(arrow);
        
        place_dateRange(selectedOption);// calling this function will define startDate and endDate value, that will be used by daterangepickerInput of calendar
        
        getCalendar(false);//calendar part having selected date-range

        document.body.appendChild(daterangepicker);
            
        // hide when clicked anywhere on document other than the dialog
        setTimeout(() => {
            document.addEventListener('mousedown',closeDateRangePickerOnMouseDown);
        }, 0);
    }
    function getCalendar(selectOnlyStartDate,hidden){// selectOnlyStartDate=true when picking startDate. hidden=true to hide calendars when selecting Range
        let display = 'none';
        let calendars = daterangepicker.querySelectorAll('.calendar');
        if(calendars.length>0){
            if(!hidden) display = 'block';
            calendars.forEach(calendar=>{
                calendar.remove();
            })
        }
        let leftCalendar = cTag('div',{'class':'calendar'});
            setStyles(leftCalendar,{'margin-right':'5px','display':display,'width':'245px'});
            leftCalendar.appendChild(calenderDialog('left',selectOnlyStartDate));
        daterangepicker.appendChild(leftCalendar);
            let rightCalendar = cTag('div',{'class':'calendar'});
            setStyles(rightCalendar,{'display':display,'width':'245px'});
            rightCalendar.appendChild(calenderDialog('right',selectOnlyStartDate));
        daterangepicker.appendChild(rightCalendar);
        if(window.innerWidth<700){
            setStyle(leftCalendar,'margin-right','5px',);
            setStyle(leftCalendar,'margin-bottom','5px',);
        }
    }
    function place_dateRange(key){
        if(key){
			if(key!==Translate('Custom')) [startDate,endDate] = get_dateRange(key);
		}
        node.value = `${startDate} - ${endDate}`;
        selectedDateRange = `${startDate} - ${endDate}`;
        selectedOption = key;
        optionHighlighter();
    } 
    function optionHighlighter(){
        daterangepicker.querySelectorAll('ul li').forEach(item=>{
            item.style = '';
            setStyles(item,styles.rangeOption);
            if(item.getAttribute('data-range-key')===selectedOption){
                setStyles(item,styles.hoveredRangeOption);
                item.removeEventListener('mouseenter',mouseEnterhandler);
                item.removeEventListener('mouseleave',mouseLeavehandler);
            }
            else{
                setStyles(item,styles.unhoveredRangeOption);
                item.addEventListener('mouseenter',mouseEnterhandler);
                item.addEventListener('mouseleave',mouseLeavehandler);
            }
        })
        function mouseEnterhandler(){
            if(this.getAttribute('data-range-key')===selectedOption) return;
            setStyles(this,styles.hoveredRangeOption)
        }
        function mouseLeavehandler(){
            if(this.getAttribute('data-range-key')===selectedOption) return;
            setStyles(this,styles.unhoveredRangeOption)
        }
    }  
    function get_dateRange(option){
        let startDate,endDate;
        let time = new Date();
        if(option === Translate('Today')) { startDate = getDate(0); endDate = getDate(0) };
        if(option === Translate('Yesterday')){ startDate = getDate(-1); endDate = getDate(-1) };
        if(option === Translate('Last 7 Days')){ startDate = getDate(-6); endDate = getDate(0) };
        if(option === Translate('Last 30 Days')){ startDate = getDate(-29); endDate = getDate(0) };
        if(option === Translate('This Month')){
            time.setDate(1);
            startDate = getDate(0);
            endDate = getDate(daysInMonth(time.getMonth())-1);
        }
        if(option === Translate('Last Month')){
            let lastMonth = time.getMonth();
            if(lastMonth===0) time.setFullYear(time.getFullYear()-1);
            lastMonth = lastMonth>0?lastMonth-1:11;
            time.setMonth(lastMonth);
            time.setDate(1);
            startDate = getDate(0);
            endDate = getDate(daysInMonth(lastMonth)-1);
        }

        function getDate(pre_next){
            let time2 = new Date(time);
            time2.setTime(time2.getTime()+(pre_next*1000*86400));
            let date = time2.getDate();
            let month = time2.getMonth()+1;
            if(month<10) month = '0'+month;
            if(date<10) date = '0'+date;
            let year = time2.getFullYear();

			return getDateByFormates(date,month,year);
        }
        function daysInMonth(month){
            let time = new Date();
            time.setMonth(month);
            time.setDate(1);

            let numberOfDays=0;
            while(time.getMonth()===month){
                numberOfDays++;
                time.setDate(time.getDate()+1)
            }
            return numberOfDays;
        }
        return [startDate,endDate];
    }    
    function closeDateRangePickerOnMouseDown(event){
        if(!(daterangepicker.contains(event.target)||node===event.target)) {
            if(daterangepicker.style.display!=='none') toggleDateRangePicker();
        }
    }    
    function toggleDateRangePicker(){			
        if(daterangepicker.style.display === 'none'){
            daterangepicker.style.display = 'flex';
            positionDialog();
            if(selectedOption===Translate('Custom'))  daterangepicker.querySelectorAll('.calendar').forEach(calendar=>calendar.style.display='');
            setTimeout(() => {
                setStyle(daterangepicker,'opacity',1);			
            }, 0);
        }
        else{
            setStyle(daterangepicker,'opacity','0');
            setTimeout(() => {
                daterangepicker.style.display = 'none';
                daterangepicker.querySelectorAll('.calendar').forEach(item=>{//hide calendar otherwise window.innerwidth will be incresed that cause responsive issue
                    item.style.display = 'none';
                })
            }, 300);
        }
    }
    function toggleCalendar(){
        daterangepicker.querySelectorAll('.calendar').forEach(item=>{
            if(item.style.display==='none') item.style.display = '';
            else item.style.display = 'none';
        })
    }
	function getDateByFormates(dd,mm,yy){
		if(calenderDate.toLowerCase()==='dd-mm-yyyy'){    
			return `${dd}-${mm}-${yy}`;
        }    
        else{    
			return `${mm}/${dd}/${yy}`;
        } 
	}
    function positionDialog(){
        let daterangepickerPosition={};

        let availableRoom;
        let nodeHeight = daterangepicker.getBoundingClientRect().height;

        let rightClearence = document.body.getBoundingClientRect().width - node.getBoundingClientRect().left; //available space on right-side
        let leftClearence = node.getBoundingClientRect().right; //available space on left-side
        if(rightClearence<leftClearence){//position, on left side if there is not enough room on right-side            
            daterangepickerPosition.right = (document.body.getBoundingClientRect().width-node.getBoundingClientRect().right)+window.scrollX+'px';
            setStyles(arrow,{right: '30px'});
            availableRoom = leftClearence;
            if(!(availableRoom<700)) setStyle(rangesContainer,'order',1);
        }else{
            daterangepickerPosition.left = node.getBoundingClientRect().left+window.scrollX+'px';
            setStyles(arrow,{left: '25px'});
            availableRoom = rightClearence;
        }
		daterangepickerPosition.top = node.getBoundingClientRect().bottom+3+window.scrollY+'px';
		setStyles(arrow,{top: '-16px'});        

        setStyles(daterangepicker,daterangepickerPosition);
        if(availableRoom<700){//when viewport < 700px, apply flex-direction to column
            setStyles(daterangepicker,styles.daterangepickerLT700);
        }else{
            setStyles(daterangepicker,styles.daterangepickerGT700);
        }

    }
    function dateExtractor(){
        let dates = node.value.split(' - ');
        if(calenderDate.toLowerCase()==='mm/dd/yyyy' && new Date(dates[0])!='Invalid Date' && new Date(dates[1])!='Invalid Date'){
            if(new Date(dates[0])<=new Date(dates[1])) [startDate,endDate] = dates;
        }
        else if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
            let [dds,mms,yys] = dates[0].split('-');
            let [dde,mme,yye] = dates[1].split('-');

            if(new Date(`${mms}/${dds}/${yys}`)!='Invalid Date' && new Date(`${mme}/${dde}/${yye}`)!='Invalid Date'){
                if(new Date(`${mms}/${dds}/${yys}`)<=new Date(`${mme}/${dde}/${yye}`)) [startDate,endDate] = dates;
            }
        }
    }
    function getSelectedOption(){
        if(startDate && endDate){
            let option = [Translate('Today'),Translate('Yesterday'),Translate('Last 7 Days'),Translate('Last 30 Days'),Translate('This Month'),Translate('Last Month'),Translate('Custom')].filter(item=>{
                let dates = get_dateRange(item);
                if(startDate===dates[0] && endDate===dates[1]) return true;
            })[0]
            if(option) return option;
            else return Translate('Custom');
        }
    }

    function calenderDialog(position,selectOnlyStartDate){
        let Month = position==='left'?getStartEndDates(startDate)[0]:getStartEndDates(endDate)[0];
        let Year = position==='left'?getStartEndDates(startDate)[2]:getStartEndDates(endDate)[2];
        let selectYear;

        let datePicker = cTag('div');
        setStyles(datePicker,styles.datePicker);
            let daterangepickerInputContainer = cTag('form',{ 'class':`daterangepicker_input` });
            daterangepickerInputContainer.addEventListener('submit',(event)=>{
                event.preventDefault();
                let input = daterangepickerInputContainer.querySelector('input');
                let date = input.value;

                if(calenderDate.toLowerCase()==='mm/dd/yyyy' && new Date(date)!='Invalid Date'){
                    if(input.name==='startDate'){
                        startDate = date ;
                        if(new Date(date)>new Date(endDate)) endDate = startDate;
                    }
                    else if(input.name==='endDate'){
                        endDate = date ;
                        if(new Date(date)<new Date(startDate)) startDate = endDate;
                    }
                }
                else if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                    let [dd,mm,yy] = input.value.split('-');
                    let date = `${mm}/${dd}/${yy}`;
        
                    if(new Date(date)!='Invalid Date'){
                        if(input.name==='startDate'){
                            startDate = `${dd}-${mm}-${yy}` ;
                            let [d,m,y] = endDate.split('-');
                            if(new Date(date)>new Date(`${m}/${d}/${y}`)) endDate = startDate;
                        }
                        else if(input.name==='endDate'){
                            endDate = `${dd}-${mm}-${yy}` ;
                            let [d,m,y] = startDate.split('-');
                            if(new Date(date)<new Date(`${m}/${d}/${y}`)) startDate = endDate;
                        }
                    }
                }
                startDatepicked = true;
                getCalendar(false);
                place_dateRange(getSelectedOption());
                setStyles(daterangepicker.querySelector('button#submit'),{'cursor':'pointer','opacity':1});
            })

            setStyles(daterangepickerInputContainer,styles.daterangepickerInputContainer);
                let daterangepickerInput = cTag('input',{ 'type':`text` });
                if(position==='left') daterangepickerInput.setAttribute('name','startDate');
                else if(position==='right') daterangepickerInput.setAttribute('name','endDate');
                setStyles(daterangepickerInput,styles.daterangepickerInput);
            daterangepickerInputContainer.appendChild(daterangepickerInput);
                let calendarIcon = cTag('i',{ 'class':`fa fa-calendar glyphicon glyphicon-calendar` });
                setStyles(calendarIcon,styles.calendarIcon);
            daterangepickerInputContainer.appendChild(calendarIcon);
        datePicker.appendChild(daterangepickerInputContainer)
            let header = cTag('div');
            setStyles(header,styles.header);
                let previous = cTag('i',{class:'fa fa-chevron-left glyphicon glyphicon-chevron-left'});
                setStyles(previous,styles.pre_next);
                setStyles(previous,styles.unhovered_pre_next);
                setHoverStyle(previous,styles.hovered_pre_next,styles.unhovered_pre_next);
                previous.addEventListener('click',()=>{
                    setMonthYear(-1);
                    calendarMaker();
                });
            header.appendChild(previous);
                let selectMonth = cTag('select');
                Months.forEach((item,indx)=>{
                    let option = cTag('option',{'value':indx});
					setStyle(option,'color','inherit');
                    option.innerText = item;
                    selectMonth.appendChild(option)
                })
                selectMonth.value = Month;
                selectMonth.addEventListener('change',()=>{
                    Month = selectMonth.value*1;
                    calendarMaker();
                })
            header.appendChild(selectMonth);
                selectYear = cTag('select');
                if(position==='left') selectYear.setAttribute('id','startYear');
                else selectYear.setAttribute('id','endYear');
                selectYear.addEventListener('change',()=>{
                    Year = selectYear.value*1;
                    calendarMaker();

                    let selectEndYear = daterangepicker.querySelector('#endYear');
                    selectEndYear.innerHTML = '';
                    let toYear = getStartEndDates(endDate)[2];
                    for(let i=Year;i<=toYear+5;i++){
                        let option = cTag('option',{'value':i});
                        setStyle(option,'color','inherit');
                        option.innerText = i;
                        selectEndYear.appendChild(option);
                    };
                    selectEndYear.value = toYear;
                })
            header.appendChild(selectYear);
                let next = cTag('i',{class:'fa fa-chevron-right glyphicon glyphicon-chevron-right'});
                setStyles(next,styles.pre_next);
                setStyles(next,styles.unhovered_pre_next);
                setHoverStyle(next,styles.hovered_pre_next,styles.unhovered_pre_next);
                next.addEventListener('click',()=>{
                    setMonthYear(1);
                    calendarMaker();
                });
            header.appendChild(next);
        datePicker.appendChild(header);
            let calendar = cTag('table');
            setStyles(calendar,styles.calendar);            
            calendarMaker(Month,Year);
        datePicker.appendChild(calendar);
        return datePicker;

        function calendarMaker(){
            let input = daterangepickerInputContainer.querySelector('input');
            if(input.name==='startDate') input.value = startDate;
            else if(input.name==='endDate') input.value = endDate;

            selectMonth.value = Month;
            getYearOptions();
            calendar.innerHTML = '';
            getDays();
            getDates();
        }
        function getDays(){
            if(calendar.querySelector('thead')) return;
            let dayBar = cTag('thead');
            days.forEach(item=>{
                    let day = cTag('th',{title:item.title});
                    setStyles(day,{'padding':'0','text-align':'center'});
                    day.innerText = item.id;
                dayBar.appendChild(day);
            });
            calendar.appendChild(dayBar);
        }
        function getDates(){
            let date = new Date(Year, Month, 1);// initialize the date as a first-day of Month
            let dateRows = [];
            let dateCellsCount = 0;
            const dateStart = getStartEndDates(startDate);
            const dateEnd = getStartEndDates(endDate);

            for (let i=0; i<6; i++){ // Rows of dates could be at most 6
                let row = cTag('tr');
                dateRows[i] = row;
            }
            for(let i=date.getDay();i>0;i--){//assigning the blanck date fields with previous-months dates
                let previousMonthDates = new Date(date);
                previousMonthDates.setTime(previousMonthDates.getTime()-(i*1000*86400));
                let td = cTag('td');
                setStyles(td,styles.preNextDateCell);
                td.innerText = previousMonthDates.getDate();
                dateRows[parseInt(dateCellsCount/7)].appendChild(td);
                dateCellsCount++;
            }

            while (date.getMonth() === Month) {// assining current Month's dates
                let td = cTag('td',{'data-month':Month,'data-year':Year});
                setStyle(td,'padding','0');
                    let span = cTag('span');
                    setStyles(span,styles.dateCell);                    
                    let inBetweenRange = false;
                    let startTime = new Date(dateStart[2],dateStart[0],dateStart[1]).getTime();
                    let currentTime = new Date(Year,Month,date.getDate()).getTime();
                    let endTime = new Date(dateEnd[2],dateEnd[0],dateEnd[1]).getTime();
                    if(startTime<currentTime && currentTime<endTime) inBetweenRange = true;

                    if(selectOnlyStartDate===false){
                        if(inBetweenRange) setHoverStyle(span,styles.hoveredDateCell,styles.dateCellIntoRange);
                        else{
                            if(!((dateStart[1]===date.getDate()&&dateStart[0]===Month&&dateStart[2]===Year)||(dateEnd[1]===date.getDate()&&dateEnd[0]===Month&&dateEnd[2]===Year))) setHoverStyle(span,styles.hoveredDateCell,styles.unhoveredDateCell);
                        }
                        
                        if((dateStart[1]===date.getDate()&&dateStart[0]===Month&&dateStart[2]===Year)){
                            setStyles(span,styles.startDateCell);
                        }
                        else if((dateEnd[1]===date.getDate()&&dateEnd[0]===Month&&dateEnd[2]===Year)){
                            setStyles(span,styles.endDateCell);
                        }                        
                    }else{
                        if((dateStart[1]===date.getDate()&&dateStart[0]===Month&&dateStart[2]===Year)){
                            setStyles(span,styles.startDateCell);
                        }else{
                            setHoverStyle(span,styles.hoveredDateCell,styles.unhoveredDateCell);
                        }
                    }

                    span.innerText = date.getDate();
                    span.addEventListener('click',function(){
                        let date = this.innerText*1;
                        date = date<10?'0'+date:date;
                        let month = 1+this.parentNode.getAttribute('data-month')*1;
                        month = month<10?'0'+month:month;
                        let year = this.parentNode.getAttribute('data-year');

                        if(startDatepicked===true){
                            startDatepicked = false;
							startDate =  getDateByFormates(date,month,year);

                            const [oldEndMonth,oldEndDate,oldEndYear] = getStartEndDates(endDate);
                            // if(year<oldEndYear) 
                            if(new Date(oldEndYear,oldEndMonth,oldEndDate)<new Date(year,month-1,date)) endDate =  getDateByFormates(date,month,year);
                            // endDate =  getDateByFormates(date,month,year);

                            daterangepicker.querySelector('.daterangepicker_input [name="startDate"]').value = startDate;
                            setStyles(daterangepicker.querySelector('button#submit'),{'cursor':'not-allowed','opacity':'0.65'});
                            getCalendar(true);
                        }
                        else if(startDatepicked===false){
                            const [oldStartMonth,oldStartDate,oldStartYear] = getStartEndDates(startDate);
                            if(new Date(oldStartYear,oldStartMonth,oldStartDate)>new Date(year,month-1,date)){
								startDate =  getDateByFormates(date,month,year);
                                // endDate =  getDateByFormates(date,month,year);//added
                                daterangepicker.querySelector('.daterangepicker_input [name="startDate"]').value = startDate;
                                getCalendar(true);

                            }
                            else{
                                startDatepicked = true;
								endDate =  getDateByFormates(date,month,year);
                                daterangepicker.querySelector('.daterangepicker_input [name="endDate"]').value = endDate;
                                selectedOption = Translate('Custom');
                                setStyles(daterangepicker.querySelector('button#submit'),{'cursor':'pointer','opacity':1});
                                optionHighlighter();
                                getCalendar(false);
                                selectedDateRange = daterangepicker.querySelector('.daterangepicker_input input[name="startDate"]').value+' - '+daterangepicker.querySelector('.daterangepicker_input input[name="endDate"]').value;
                            } 
                        }
                    })
                td.appendChild(span);
                dateRows[parseInt(dateCellsCount/7)].appendChild(td);
                dateCellsCount++;
                date = new Date(Year, Month,date.getDate()+1)
            }

            let nextMonthDates = 42-dateCellsCount;
            for(let i=1;i<=nextMonthDates;i++){//assigning the blanck date fields with next-months dates         
                let td = cTag('td');
                setStyles(td,styles.preNextDateCell);
                td.innerText = i;
                dateRows[parseInt(dateCellsCount/7)].appendChild(td);
                dateCellsCount++;
            }

            dateRows.forEach(row=>{
                calendar.appendChild(row);
            })
        }
        function setMonthYear(pre_next){
            if(pre_next===1){
                if(Month<11) Month+=1;
                else{
                    Month = 0;
                    Year +=1 
                }
            }
            else if(pre_next===-1){
                if(Month>0) Month-=1;
                else{
                    Month = 11;
                    Year -=1 
                }
            }
            else{
                console.log('call setMonthYear with 1 or -1 only')
            }
        }
        function getYearOptions(){
            selectYear.innerHTML = '';

            if(position==='left'){
                for(let i=Year-50;i<=Year+5;i++){
                    let option = cTag('option',{'value':i});
					setStyle(option,'color','inherit');
                    option.innerText = i;
                    selectYear.appendChild(option);
                }
            }
            else if(position==='right'){
                // let currentYear = new Date().getFullYear(); 
                let fromYear = getStartEndDates(startDate)[2];
                let toYear = getStartEndDates(endDate)[2];
                for(let i=fromYear;i<=toYear+5;i++){
                    let option = cTag('option',{'value':i});
					setStyle(option,'color','inherit');
                    option.innerText = i;
                    selectYear.appendChild(option);
                }
            }
            selectYear.value = Year
        }
        function getStartEndDates(date){
			if(calenderDate.toLowerCase()==='dd-mm-yyyy'){   
				let [oldStartDate,oldStartMonth,oldStartYear] = date.split('-');
				return [oldStartMonth-1,oldStartDate*1,oldStartYear*1];
			}    
			else{ 
				let [oldStartMonth,oldStartDate,oldStartYear] = date.split('/');
				return [oldStartMonth-1,oldStartDate*1,oldStartYear*1];
			} 
        }
    }    
    
    function setStyle(node,property,value){
        node.style[property] = value;
    } 
    function setStyles(node,stylesObj){
        for (const property in stylesObj) {
            node.style[property] = stylesObj[property];
        }
    }    
    function setHoverStyle(node,hoverStyle,unhoverStyle){
        setStyles(node,unhoverStyle);
        node.addEventListener('mouseenter',()=>{setStyles(node,hoverStyle)})
        node.addEventListener('mouseleave',()=>{setStyles(node,unhoverStyle)})
    }
}

export function createTabs(node){
	let styles = {
		tabs:{
			//'border': '1px solid #d3d3d3',
			'overflow': 'auto',
		},
		tablists:{
			'list-style-type': 'none',
			'display': 'flex',
			'border-bottom': '1px solid #d3d3d3',
			'padding': '.2em .2em 0',
		},
		tab:{
			'margin': '1px .2em 0 0',
			'font-weight': 'normal',
			'border-radius': '3px !important',
		},
		tab_anchor:{
			'position': 'relative',
			'top': '1px',
			'display': 'block',
			'padding': '.5em 1em',
			'text-decoration': 'none',
			'cursor': 'pointer',
			'border-radius': '3px 3px 0 0',      
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
				if(tabPanel.style.display!=='none'){
					tabPanel.style.display='none';
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
				if(node.querySelector(`${this.getAttribute('href')}`).style.display==='none'){
					node.querySelector(`${this.getAttribute('href')}`).style.display='';
				}

				//make other items inactive
				node.querySelectorAll('a.tab-anchor').forEach(item=>{
					if(item!==this) {
						item.classList.remove('activeTab');
						setStyles(item,styles.inactiveTab);
						setStyles(item,styles.unhoveredTab)
						if(node.querySelector(`${item.getAttribute('href')}`).style.display!=='none'){
							node.querySelector(`${item.getAttribute('href')}`).style.display='none';
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

export function setCDinCookie(){
	let now = new Date();
	let time = now.getTime();
	let expireTime = time + 1000*2592000;
	now.setTime(expireTime);
	let drawer = document.getElementById("drawer").value;
	document.cookie = "drawer="+drawer+"; expires="+now.toGMTString()+"; path=/";
}

export function validDate(date){
	if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
		let [dd,mm,yy] = date.split('-');
		if(new Date(`${mm}/${dd}/${yy}`) != 'Invalid Date') return true;
		else return false;
	}
	else{
		let [mm,dd,yy] = date.split('/');
		if(new Date(`${mm}/${dd}/${yy}`) != 'Invalid Date') return true;
		else return false;
	}
}

export function checkDateOnBlur(dateField,errorFieldID,errorMsg){
    dateField.addEventListener('blur',function(){
        if(this.value!=='' && validDate(this.value)===false){
            document.querySelector(errorFieldID) && (document.querySelector(errorFieldID).innerHTML = errorMsg);
            this.focus();
        }
        else document.querySelector(errorFieldID) && (document.querySelector(errorFieldID).innerHTML = '');
    })
}

export function checkNumericInputOnKeydown(field){
    field.addEventListener('keydown',function(){
        if(this.getAttribute('max')||this.getAttribute('min')){
            let oldValue = this.value;
			let pattern = /^-?\d*(\.\d{0,2})?$/;
			if(this.getAttribute('step')==='0.001') pattern = /^-?\d*(\.\d{0,3})?$/;
            setTimeout(() => {
                let invalidPattern = false;
                invalidPattern = !RegExp(pattern).test(this.value);
                let aboveMax = false;
                if(this.getAttribute('max')) aboveMax = Number(this.value) > Number(this.getAttribute('max'));
                let underMin = false;
                if(this.getAttribute('min')) underMin = Number(this.value) < Number(this.getAttribute('min'));
                
                if(invalidPattern||aboveMax||underMin) this.value = oldValue;
            }, 0);
        }
    })
}

export function controllNumericField(field,warningNodeSelector){
	if(!field.getAttribute('data-format')){
		throw(new Error('format is missing'));
	}
	field.valid = validator;	

	field.addEventListener('focus',()=>{
		field.addEventListener('blur',validator);
	});
	
	field.addEventListener('keydown',event=>{	
		if(!((event.ctrlKey||event.metaKey) && ['v','V','c','C','x','X'].includes(event.key)) && (event.key && event.key.length===1 && !/[-.]|\d/.test(event.key))){
			event.preventDefault();
		}
	})

	function validator(){
		//check validity if and only if the node is present in the documetn and have some value;
		if(this.closest('body') && this.value!==''){			
			const value = Number(this.value);
			if(isNaN(value)){
				document.querySelector(warningNodeSelector).innerHTML = 'Invalid Number';
				this.focus();
				return;
			}
			const maxValue = Number(this.getAttribute('data-max')||undefined);
			const minValue = Number(this.getAttribute('data-min')||undefined);
			const format = this.getAttribute('data-format');
			const { valid, reason } = numericValidator(value,minValue,maxValue,format);

			if(!valid) {
				this.classList.add('errorFieldBorder');
				this.focus();
				document.querySelector(warningNodeSelector).innerHTML = reason;
				return false;
			}
			else{
				this.classList.remove('errorFieldBorder');
				document.querySelector(warningNodeSelector).innerHTML = '';
				this.removeEventListener('blur',validator);
				return true;
			}
		}
        else if(this.value===''){
			this.value = 0;
			return true;
		}
	}
}

function numericValidator(value,minValue,maxValue,format){
	let pattern, invalidPatternWarning;
	if(format==='d'){
		pattern = /^-?\d*$/;
		invalidPatternWarning = 'Integer only';
	}
	else if(format==='d.dd'){
		pattern = /^-?\d*(\.\d{0,2})?$/;
		invalidPatternWarning = '2 digits after decimal';
	}
	else if(format==='d.ddd'){
		pattern = /^-?\d*(\.\d{0,3})?$/;
		invalidPatternWarning = '3 digits after decimal';
	}


	let warning = '';
	let invalidPattern =  !RegExp(pattern).test(value);
	let aboveMax = value > maxValue;
	let underMin = value < minValue;
	if(invalidPattern) warning = invalidPatternWarning;
	else if(aboveMax) warning = `max: ${maxValue}`;
	else if(underMin) warning = `min: ${minValue}`;

	return {
		valid: !(invalidPattern||aboveMax||underMin),
		reason: warning
	}
}
export function validateRequiredField(node,warningNodeSelector){
	if (node.value===''){
		node.focus();
		document.querySelector(warningNodeSelector).innerHTML = `${Translate('Missing')} ${node.getAttribute('name')||''}`;
		return false;
	}
	return true;
}

export function dynamicImport(path,functionName,params){
	let jsfile = path.replace('./', '').replace('.js', '');
	if(jsFileNewNames[jsfile] !==undefined){path = './'+jsFileNewNames[jsfile]+'.js';}
    activeLoader();
    import(path)
    .then(obj => {
		hideLoader();
        obj[functionName](...params);
    })
    .catch(err => console.log(err));
}

export function sanitizer(){
	let input = this.value;
    const ContainsOnEventListener = /<[^>]*on\w+=[^>]*>/gi.test(input)
    const ConstinsScriptTag = /<\/?\s*script/gi.test(input);    
    if(ContainsOnEventListener || ConstinsScriptTag){
        this.value = '';
        showTopMessage('alert_msg', 'Warning: Potential malcious code exists in your input-text'); 
    }
}
export function applySanitizer(node){
	node.querySelectorAll('input,textarea').forEach(field=>field.addEventListener('blur',sanitizer));
}

export async function showOnPOInfo(product_id, product_type){
    const jsonData = {};
	jsonData['product_id'] = product_id;
	jsonData['product_type'] = product_type;

	if(product_id>0){
        const url = "/Common/showOnPOInfo";
        fetchData(afterFetch,url,jsonData);

        function afterFetch(data){
            let dialogConfirm = cTag('div');
            let table,tableHead, tableHeadRow, tdCol, thCol, tbody;
            if(data.needData.length){
                    table = cTag('table',{ 'class':` table-bordered`, 'style': "margin-bottom: 0px;" });
                        tableHead = cTag('thead');
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':`3` });
                                    let neededHeader = cTag('h4',{ 'align':`left` });
                                    neededHeader.innerHTML = Translate('NEEDED');
                                tdCol.appendChild(neededHeader);
                            tableHeadRow.appendChild(tdCol);
                        tableHead.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`20%` });
                                thCol.innerHTML = Translate('QTY');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`30%` });
                                thCol.innerHTML = Translate('Invoice');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th');
                                thCol.innerHTML = Translate('Status');
                            tableHeadRow.appendChild(thCol);
                        tableHead.appendChild(tableHeadRow);
                    table.appendChild(tableHead);
                        tbody = cTag('tbody');
                        data.needData.forEach(item=>{
                                tableHeadRow = cTag('tr');
                                    thCol = cTag('th');
                                    thCol.innerHTML = item[0];
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th');
                                    if(item[2]==='Open Order'){
                                            let orderViewLink = cTag('a',{ 'href':`/Orders/edit/${item[1]}`,'title':Translate('View Order Details'), 'style': "color: #009; text-decoration: underline;" });
                                            orderViewLink.append(`o${item[1]}`);
                                            orderViewLink.appendChild(cTag('i',{ 'class':`fa fa-link` }));
                                        thCol.appendChild(orderViewLink);
                                    }
                                    else{
                                            let repairView = cTag('a',{ 'href':`/Repairs/edit/${item[3]}`,'title':Translate('View Repair Details'), 'style': "color: #009; text-decoration: underline;" });
                                            repairView.append(`t${item[1]}`);
                                            repairView.appendChild(cTag('i',{ 'class':`fa fa-link` }));
                                        thCol.appendChild(repairView);
                                    }
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th');
                                    thCol.innerHTML = item[2];
                                tableHeadRow.appendChild(thCol);
                            tbody.appendChild(tableHeadRow);
                        })
                    table.appendChild(tbody);
                dialogConfirm.appendChild(table);
            }
            let inventoryHeader = cTag('h4',{'align':"left", 'style': "margin-top: 10px;"});
            inventoryHeader.innerHTML = `${Translate('Quantity In Inventory')} : `; 
                if(product_type==='Live Stocks'){
                    let inventoryLink = cTag('a',{ 'href':`/IMEI/lists/1/inventory/${product_id}`,'title':`${Translate('IMEIs Available')}: ${data.have}` });
                    inventoryLink.append(`${data.have} `,cTag('i',{ 'class':`fa fa-link` }));
                    inventoryHeader.appendChild(inventoryLink);
                }
                else inventoryHeader.append(`${data.have} `);
            dialogConfirm.appendChild(inventoryHeader);
            if(data.onPOData.length){
                    table = cTag('table',{ 'class':` table-bordered`, 'style': "margin-top: 10px; margin-bottom: 0px;" });
                        tableHead = cTag('thead');
                            tableHeadRow = cTag('tr');
                                tdCol = cTag('td',{ 'colspan':`4` });
                                    let onPoHeader = cTag('h4',{ 'align':`left` });
                                    onPoHeader.innerHTML = Translate('On PO');
                                tdCol.appendChild(onPoHeader);
                            tableHeadRow.appendChild(tdCol);
                        tableHead.appendChild(tableHeadRow);
                            tableHeadRow = cTag('tr');
                                thCol = cTag('th',{ 'width':`15%` });
                                thCol.innerHTML = Translate('QTY');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`25%` });
                                thCol.innerHTML = Translate('PO Number');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th');
                                thCol.innerHTML = Translate('Lot Ref. No.');
                            tableHeadRow.appendChild(thCol);
                                thCol = cTag('th',{ 'width':`25%` });
                                thCol.innerHTML = Translate('Date Expected');
                            tableHeadRow.appendChild(thCol);
                        tableHead.appendChild(tableHeadRow);
                    table.appendChild(tableHead);
                        tbody = cTag('tbody');
                        data.onPOData.forEach(item=>{
                                tableHeadRow = cTag('tr');
                                    thCol = cTag('th');
                                    thCol.innerHTML = item[0];
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th');
                                        let poViewLink = cTag('a',{ 'href':`/Purchase_orders/edit/${item[1]}`,'title':Translate('View PO'), 'style': "color: #009; text-decoration: underline;" });
                                        poViewLink.append(`p${item[1]} `);
                                        poViewLink.appendChild(cTag('i',{ 'class':`fa fa-link` }));
                                    thCol.appendChild(poViewLink);
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th');
                                    thCol.innerHTML = item[2];
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th');
                                    thCol.innerHTML = DBDateToViewDate(item[3], 0, 1);
                                tableHeadRow.appendChild(thCol);
                            tbody.appendChild(tableHeadRow);
                        })
                    table.appendChild(tbody);
                dialogConfirm.appendChild(table);
            } 

            popup_dialog(
                dialogConfirm,
                {
                    title:Translate('Need/Have/OnPO'),
                    width:600,
                    buttons: {
                        _Ok: {
                            text: Translate('Ok'), 
                            class: 'btn saveButton', 'style': "margin-left: 10px;",
                            click: function(hide) {
                                hide();
                            },
                        }
                    }
                }
            );
        }
	}
	return false;
}

export async function archiveData(url, redirectTo, sendingData, msgTitle,errorMsg){
    fetchData(afterFetch,url,sendingData);

    function afterFetch(data){
		if(data.returnStr ==='archive-success'){
			showTopMessage('success_msg', `${msgTitle} ${Translate('archived successfully')}`);
			if(redirectTo) window.location = redirectTo;
		}
		else{
            if(data.savemsg==='reasonOfNotRemoving') showTopMessage('error_msg', data.returnStr);
			else showTopMessage('error_msg', errorMsg);
		}
	}
}

export async function unarchiveData( redirectTo, sendingData, afterUnarchive){    
	const url = '/Common/AJunArchive_tableRow';
    await fetchData(afterFetch,url,sendingData);

    function afterFetch(data){
		if(data.returnStr ==='unarchive-success'){
			if(redirectTo) window.location = redirectTo;
            else afterUnarchive();
		}
		else{
			showTopMessage('error_msg', Translate('Error occured while unarchiving information! Please try again.'));
		}
	}
}

//toggle between Enable/Disable state of payment-options based on drawer selection
export function togglePaymentButton(){ 
	if((!document.getElementById('multiple_cash_drawers')) || (document.getElementById('multiple_cash_drawers').value==='0')) return;
	else{
		let drawer = document.getElementById('drawer');
		let method = document.getElementById('method');
		let amount = document.getElementById('amount');
		let btnPayment = document.getElementById('btnPayment');
        if((segment1!=='POS' && drawer.value!=='') || (segment1==='POS' && document.getElementById('pos_id').value>0 && drawer.value!=='')){
            method.removeAttribute('disabled','');
			amount.removeAttribute('disabled','');
			btnPayment.removeAttribute('disabled','');
        }
		else{
			method.setAttribute('disabled','');
			amount.setAttribute('disabled','');
			btnPayment.setAttribute('disabled','');
		}
	}
}

export function listenToEnterKey(listener){
	return function(event){
		if(event.which===13) listener();
	}
}

export function generateCustomeFields(parentNode,data){
	parentNode.setAttribute('data-cf', true)
	let input;
	data.forEach(function (oneCFObj){
		const custom_fields_id = oneCFObj.custom_fields_id;
		const field_name = oneCFObj.field_name;
		const required = oneCFObj.required;
		let requiredCls = '';
		const field_type = oneCFObj.field_type;
		const parameters = oneCFObj.parameters;
		const value = oneCFObj.value;

		const customFieldRow = cTag('div', {class: "flex", 'align': "left"});
			const fieldNameColumn = cTag('div', {class: "columnSM4"});
				let fieldNameLabel = cTag('label', {'for': 'cf'+custom_fields_id});
				fieldNameLabel.innerHTML = field_name;
				if(required>0){
						let requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML = '*';
					fieldNameLabel.appendChild(requiredField);
					requiredCls = ' required';
				}
			fieldNameColumn.appendChild(fieldNameLabel);
		customFieldRow.appendChild(fieldNameColumn);

			const customFieldValue = cTag('div', {class: "columnSM8"});
			if(field_type==='TextBox'){
					input = cTag('input', {'type': "text", title: field_name, class: 'form-control'+requiredCls, name: 'cf'+custom_fields_id, 'value': value, 'maxlength': 35});
				customFieldValue.appendChild(input);
			}
			else if(field_type==='TextAreaBox'){
					const textarea= cTag('textarea', {'rows': 2, title: field_name, class: 'form-control'+requiredCls, name:'cf'+custom_fields_id});
					textarea.innerHTML = value;
				customFieldValue.appendChild(textarea);
			}
			else if(field_type==='Date'){
				let dd,mm,yyyy;
				let date = value;
				if(calenderDate==='MM/DD/YYYY' && date.indexOf('-')!==-1){
					[dd,mm,yyyy] = date.split('-');
					date = `${mm}/${dd}/${yyyy}`;
				}
				else if(calenderDate==='DD-MM-YYYY' && date.indexOf('/')!==-1){
					[mm,dd,yyyy] = date.split('/');
					date = `${dd}-${mm}-${yyyy}`;
				}
					input = cTag('input', {'type': "text", title: field_name, class: 'form-control DateField'+requiredCls, name: 'cf'+custom_fields_id, 'value': date, 'maxlength': 10});
				customFieldValue.appendChild(input);
			}
			else if(field_type==='Picture'){
				let uploadBtn = cTag('input', {'type': "file",accept:"image/*", title: field_name, class: requiredCls, name: 'cf'+custom_fields_id, 'value': ''});
				customFieldValue.appendChild(uploadBtn);				
				if(value !=''){					
					uploadBtn.style.display = 'none';
					uploadBtn.classList.remove('required');
					let photoContainer = cTag('div',{class:"customPicture"})
						let currentFile = cTag('a',{ target:'_blank', 'href':value, title:'Download Picture',name: 'cf'+custom_fields_id});
						currentFile.appendChild(cTag('img',{ 'src': '/assets/images/photoFile.png'}));
					photoContainer.appendChild(currentFile);
						let removeFile = cTag('a',{title:'Remove Picture', 'style': "padding-left: 8px;"});
						removeFile.appendChild(cTag('i',{ 'class':'fa fa-remove errormsg', style:'font-size:22px'}));
						removeFile.addEventListener('click',()=>AJremove_Picture(value,'customFile','image'))
					photoContainer.appendChild(removeFile);
					customFieldValue.appendChild(photoContainer);
				}		                                 
			}
			else if(field_type==='PDF'){
				let uploadBtn = cTag('input', {'type': "file",accept:".pdf", title: field_name, class: requiredCls, name: 'cf'+custom_fields_id, 'value': ''});
				customFieldValue.appendChild(uploadBtn);
				if(value !=''){
					uploadBtn.style.display = 'none';
					uploadBtn.classList.remove('required');
					let pdfContainer = cTag('div',{class:"customPDF"})
						let currentFile = cTag('a',{ target:'_blank', 'href':value, title:'Download PDF File',name: 'cf'+custom_fields_id});
						currentFile.appendChild(cTag('img',{ 'src': '/assets/images/pdfFile.png'}));
					pdfContainer.appendChild(currentFile);
						let removeFile = cTag('a',{title:'Remove PDF File'});
						removeFile.appendChild(cTag('i',{ 'class':'fa fa-remove errormsg', style:'font-size:22px'}));
						removeFile.addEventListener('click',()=>AJremove_Picture(value,'customFile','pdf'))
					pdfContainer.appendChild(removeFile);
					customFieldValue.appendChild(pdfContainer);
				}
			}
			else if(field_type==='DropDown'){
				let selectFieldName = cTag('select', {title: field_name, class: 'form-control'+requiredCls, name: 'cf'+custom_fields_id});
					let fieldNameOption = cTag('option', {'value': ""});
					fieldNameOption.innerHTML = 'Select '+field_name;
				selectFieldName.appendChild(fieldNameOption);
				if(parameters !==''){
					let parametersData = parameters.split('||');
					parametersData.forEach(function (oneRow){
						let oneRowOption = cTag('option', {'value': oneRow});
						if(value===oneRow){oneRowOption.setAttribute('selected', 'selected');}
						oneRowOption.innerHTML = oneRow;
						selectFieldName.appendChild(oneRowOption);
					});
				}
				customFieldValue.appendChild(selectFieldName);					
			}
			else if(field_type==='Checkbox'){
				input = cTag('input');
				input = cTag('input', {'type': "checkbox", title: field_name, class: 'cursor'+requiredCls, name: 'cf'+custom_fields_id,value:'Yes'});
				if(value==='Yes') input.checked = true;
				customFieldValue.appendChild(input);
			}
			if(required>0 || field_type==='Date'){
				customFieldValue.appendChild(cTag('span',{ 'class': 'error_msg','id': 'error_cf'+custom_fields_id }));
			}
		customFieldRow.appendChild(customFieldValue);
		parentNode.appendChild(customFieldRow);
	});
}

export function validifyCustomField(tab){
	//hide errorElelment
	document.querySelectorAll(`[data-cf='true'] span.error_msg`).forEach(element=>{ element.innerHTML = '' });
	//check validity for required fields
	let requiredFields = [...document.querySelectorAll("[data-cf='true'] .required")].filter(node=>['INPUT','SELECT','TEXTAREA'].includes(node.nodeName));
	requiredFields.forEach(item=>{ item.classList.remove('errorFieldBorder') });
	if(requiredFields.length>0){
		for(let l=0;l<requiredFields.length; l++){
			let errorField = document.getElementById('error_'+requiredFields[l].getAttribute('name'));
			if((requiredFields[l].type=='checkbox' && !requiredFields[l].checked)|| !requiredFields[l].value){
				document.querySelector("#tabs").activateTab(tab);
				errorField.innerHTML = requiredFields[l].title+' '+Translate('is missing.');
				requiredFields[l].focus();
				requiredFields[l].classList.add('errorFieldBorder');
				return false;
			}			
		}
	}
	//check validity for date fields
	let dateFields = [...document.querySelectorAll("[data-cf='true'] .DateField")];
	for (let index = 0; index < dateFields.length; index++) {
		const dateField = dateFields[index];
		if(!validDate(dateField.value)){
			dateField.parentNode.querySelector(`#error_${dateField.getAttribute('name')}`).innerHTML = 'Invalid Date';
			return false;
		}
	}
	return true;
}

export async function fetchData(afterFetch,url,payload,contentType='JSON',numberOfFetchRequest=1){
	//stop heart-beating timer
	clearTimeout(heartBeatingTimerID);

	if(numberOfFetchRequest) activeLoader();

	let options;
    if(contentType==='formData') options = {method: "POST", body:new FormData(payload)};
	else options = {method: "POST", body:JSON.stringify(payload), headers:{'Content-Type':'application/json'}};

	let data = await fetch(url,options).then(checkForSuccessfulRequest).then(response=>response.json()).catch((err)=>handleErr(err,url));
	
    if (data.haveIssue){
		if(numberOfFetchRequest===0){
			showTopMessage('error_msg',data.warnMsg);
		}
		if(numberOfFetchRequest<5){
			await new Promise(resolve=>{
				setTimeout(async()=>{
					await fetchData(afterFetch,url,payload,contentType,++numberOfFetchRequest);
					resolve();
				}, 2000);
			})
		}
		else{
			let message = cTag('p',{'class':'txtleft'});
			message.innerHTML = data.warnMsg;
			popup_dialog(
				message,
				{
					title:data.warnTitle,
					width:400,
					buttons: {
						_Retry:{
							text: Translate('Retry'), 
							class: 'btn saveButton archive', 'style': "margin-left: 10px;",
							click: function(hide) {
								hide();
								fetchData(afterFetch,url,payload,contentType,1);
							},
						}
					}
				}
			);

         hideLoader();
      }
	}
	else{
		if(data.login !== ''){window.location = '/'+data.login;}
		else{
			initiateHeartBeating();
            hideLoader();
			afterFetch(data);
		}
	}
}


export function triggerEvent(eventName,data,target){
	if(target) target.dispatchEvent(new CustomEvent(eventName,{detail:data}));
	else window.dispatchEvent(new CustomEvent(eventName,{detail:data}));
}

export function addCustomeEventListener(eventName,listener,target){
	if(target) target.addEventListener(eventName,listener);
	else window.addEventListener(eventName,listener);
}

//=========Global Search Functions=========//
export async function AJheaderSearch(){
	let fieldid = document.getElementById('s');
	if(fieldid && fieldid.value === ""){
		fieldid.focus();
		return false;
	}
	else if(!fieldid){
		return false;
	}
	
	const jsonData = {'keyword_search':fieldid.value};
    const url = '/Search/submitsearch/';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){ 
		if(data.returnStr !==''){
			window.location=data.returnStr;
		}
		else{
			let pTag;
			let message = cTag('div');
				pTag = cTag('p', {'style': "text-align: left;"});
				pTag.innerHTML = Translate('Sorry, nothing was returned for your search term.');
			message.appendChild(pTag);
				pTag = cTag('p', {'style': "text-align: left;"});
				pTag.innerHTML = Translate('Enter an IMEI #, Customer Name or search Tickets with t#, Sales Invoices with s#, PO with p# or Orders with o#');
			message.appendChild(pTag);
			alert_dialog(Translate('Searching Result'), message, Translate('Ok'));
		}
	}
	return false;		
}

export function actionBtnClick(classOrIdName, buttonLabel, DisableYN){
	if(classOrIdName.includes('#')){
		let btnObj = document.querySelector(classOrIdName);		
		if(DisableYN===1){
			if(buttonLabel.includes('ing')){buttonLabel += '...';}
			
			if(btnObj.tagName==='INPUT'){btnObj.value = buttonLabel;}
			else if(btnObj.tagName==='BUTTON'){btnObj.innerHTML = buttonLabel;}
			if(!btnObj.classList.contains('is-disabled')){btnObj.classList.add('is-disabled');}
			btnObj.disabled = true;
		}
		else{
			if(btnObj.tagName==='INPUT'){btnObj.value = buttonLabel;}
			else if(btnObj.tagName==='BUTTON'){btnObj.innerHTML = buttonLabel;}			
			if(btnObj.classList.contains('is-disabled')){btnObj.classList.remove('is-disabled');}
			btnObj.disabled = false;
		}
	}
	else{
		document.querySelectorAll(classOrIdName).forEach(btnObj=>{
			if(DisableYN===1){
				if(buttonLabel.includes('ing')){buttonLabel += '...';}
			
				if(btnObj.tagName==='INPUT'){btnObj.value = buttonLabel;}
				else if(btnObj.tagName==='BUTTON'){btnObj.innerHTML = buttonLabel;}
				if(!btnObj.classList.contains('is-disabled')){btnObj.classList.add('is-disabled');}
				btnObj.disabled = true;
			}
			else{
				if(btnObj.tagName==='INPUT'){btnObj.value = buttonLabel;}
				else if(btnObj.tagName==='BUTTON'){btnObj.innerHTML = buttonLabel;}			
				if(btnObj.classList.contains('is-disabled')){btnObj.classList.remove('is-disabled');}
				btnObj.disabled = false;
			}
		});
	}
}

export function callPlaceholder(){
	document.querySelectorAll(".placeholder").forEach(oneRowObj=>{
		oneRowObj.addEventListener('focus', e=> {
			if(e.target.value ===''){
				e.target.placeholder = '';
			}
		});

		oneRowObj.addEventListener('blur', e=> {
			if(e.target.value ==='' && e.target.hasAttribute('alt')){
				e.target.placeholder = e.target.getAttribute('alt');
			}
		});
	});
}

export function callShowInputOrSelect(){
	if(document.querySelectorAll(".showNewInputOrSelect").length>0){
		document.querySelectorAll(".showNewInputOrSelect").forEach(oneObj=>{
			oneObj.addEventListener('click', showNewInputOrSelect);
		});
	}
}

export function showNewInputOrSelect(event = false){
	if(event){
		let labelStr, iTagClass;
		let parentDivObj = event.target.closest('.input-group');
		let dropdown = parentDivObj.querySelector('select');
		let input = parentDivObj.querySelector('input');
		let iTagObj = parentDivObj.querySelector('i');
		let spanObj = parentDivObj.querySelector('span');
		let dropdownVal = dropdown.value;
		if(iTagObj.className.includes('fa-plus')){
			labelStr = Translate('List');
			iTagClass = 'fa fa-list';
			if(dropdown.style.display !== 'none'){
				dropdown.style.display = 'none';
			}
			if(dropdown.querySelectorAll("option").length>0){
				dropdown.value = dropdown.querySelectorAll("option")[0].value;
			}
			if(input.style.display === 'none'){
				input.style.display = '';
			}
			input.focus();
		}
		else{
			labelStr = Translate('New');
			iTagClass = 'fa fa-plus';
			if(input.style.display !== 'none'){
				input.style.display = 'none';
			}
			input.value = '';
			if(dropdown.style.display === 'none'){
				dropdown.style.display = '';
			}
			dropdown.value = dropdownVal;
			dropdown.focus();
		}
		spanObj.innerHTML = '';
		spanObj.append(cTag('i', {class:iTagClass}), ' ', labelStr);
	}
}

export function bytesToSize(bytes) {
   const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
   if (bytes === 0) return '0 Bytes';
   let i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

export function showImage(imagePath, showing_order){
	let targetval = 'UploadImageID'+showing_order;
	let targetvalue = document.querySelector("#"+targetval);
	targetvalue.innerHTML = "";
		let divPicture = cTag('div', {class: "currentPicture"});
			let img = cTag('img', {class: "img-responsive", 'style': "max-height:600px;", src: imagePath, alt : ""});
		divPicture.appendChild(img);
	targetvalue.appendChild(divPicture);

	if(document.querySelector("#"+targetval).querySelector(".currentPicture")){
		if(document.querySelector("#"+targetval).querySelectorAll(".currentPicture")){
			document.querySelectorAll(".currentPicture").forEach(oneClassObj=>{
				oneClassObj.addEventListener('mouseenter',function(){
					let picturepath = this.querySelector("img").getAttribute("src");
					let deletedicon = cTag('div', {class: "deletedicon"});
					deletedicon.addEventListener('click', function(){
						AJremove_Picture(picturepath, 'fieldImages');
					});
					this.append(deletedicon);
				});
	
				oneClassObj.addEventListener('mouseleave',function(){
					if(document.querySelector("#"+targetval)){
						this.querySelector(".deletedicon").remove();
					}
				});
			});
		}
	}
	document.getElementById("ff"+showing_order).value = imagePath;
}

export function AJremove_Picture(picturepath, frompage, filetype){
	if(picturepath !==''){
		let uploadBtn, button, showing_order;
        let dialogConfirm = cTag('p',{"style": "text-align: left;"});
        dialogConfirm.innerHTML = Translate('Do you sure want to remove this picture permanently?');

		popup_dialog(
			dialogConfirm,
			{
				title:Translate('Remove Picture'),
				width:400,
				buttons: {
					_Close: {
						text: Translate('Close'), 
						class: 'btn defaultButton', 'style': "margin-left: 10px;",
						click: function(hide) {
							hide();
							if(frompage==='invoice_setup'){
								if(document.querySelector("#logo_size").value !== document.querySelector("#oldlogo_size").value){
									document.querySelector("#logo_size").value = document.querySelector("#oldlogo_size").value;
								}
							}
						},
					},
					_Confirm:{
						text: Translate('Confirm'), 
						class: 'btn saveButton archive', 'style': "margin-left: 10px;",
						click: async function(hide) {
							hide();
							
							let repairs_id = 0;
							if(frompage==='repairs'){
								repairs_id = document.querySelector("#repairs_id").value;
							}
							const jsonData = {"repairs_id":repairs_id, "picturepath":picturepath};
							const url =  "/Common/AJremove_Picture";
							fetchData(afterFetch,url,jsonData);

							function afterFetch(data){
								if(data.returnStr==='Ok'){
									showTopMessage('success_msg',Translate('Picture removed successfully.'));
									
									if(frompage !==''){										
										if(frompage==='invoice_setup'){
											uploadBtn = document.createDocumentFragment();
											uploadBtn.append(Translate('Upload Logo'));
											uploadBtn.appendChild(cTag('br'));
												button = cTag('button',{"type":"button", "class":"uploadButton", "name":"open", "click":()=>upload_dialog(Translate('Upload Invoice Logo'),'invoice_setup','app_logo_')});
												button.innerHTML = Translate('Upload')+'...';
											uploadBtn.appendChild(button);
											document.querySelector('#'+frompage+'_picture').innerHTML = '';
											document.querySelector('#'+frompage+'_picture').appendChild(uploadBtn);
										}
										else if(frompage==='homepage'){
											uploadBtn = document.createDocumentFragment();
											uploadBtn.append(Translate('Upload Invoice Logo'));
											uploadBtn.appendChild(cTag('br'));
												button = cTag('button',{"type":"button", "class":"uploadButton", "name":"open", "click":()=>upload_dialog(Translate('Upload Invoice Logo'),'homepage','web_logo_')});
												button.innerHTML = Translate('Upload')+'...';
											uploadBtn.appendChild(button);
											document.querySelector('#'+frompage+'_picture').innerHTML = '';
											document.querySelector('#'+frompage+'_picture').appendChild(uploadBtn);
										}
										else if(frompage==='products'){
											let defaultImageSRC = document.querySelector("#defaultImageSRC").value;
											if(defaultImageSRC !=='' && document.querySelector('#products_picture')){
												let defaultPicture = cTag('div',{"class":"currentPicture"});
												defaultPicture.appendChild(cTag('img',{"alt":"", "class":"img-responsive", 'style': "max-height: 250px;", "src":`${defaultImageSRC}`}));
												
												document.querySelector('#products_picture').innerHTML = '';
												document.querySelector('#products_picture').appendChild(defaultPicture);
											}
										}
										else if(frompage==='fieldImages'){
											let fieldImagesID = document.getElementsByClassName("fieldImages");
											for(let l=0; l<fieldImagesID.length; l++){
												
												let imagePath = fieldImagesID[l].value;
												showing_order = fieldImagesID[l].getAttribute('name').replace('ff', '');
												if(imagePath===picturepath){
													uploadBtn = cTag('button',{"type":"button", "class":"uploadButton", "name":"open", "click":()=>upload_dialog(showing_order)});
													uploadBtn.innerHTML = Translate('Upload');
													document.querySelector('#UploadImageID'+showing_order).innerHTML = '';
													document.querySelector('#UploadImageID'+showing_order).appendChild(uploadBtn);
													document.querySelector("#ff"+showing_order).value = '';
												}
											}
											runImageScript();
										}
										else if(frompage==='all_pages_header'){
											uploadBtn = document.createDocumentFragment();
											uploadBtn.append(Translate('Upload Header Logo'));
											uploadBtn.appendChild(cTag('br'));
												button = cTag('button',{"type":"button", "class":"uploadButton", "name":"open", "click":()=>upload_dialog(Translate('Upload Header Logo'),'all_pages_header','web_logo_')});
												button.innerHTML = Translate('Upload')+'...';
											uploadBtn.appendChild(button);
											document.querySelector('#'+frompage+'_picture').innerHTML = '';
											document.querySelector('#'+frompage+'_picture').appendChild(uploadBtn);
											document.querySelector("#web_logo").value = '';
											triggerEvent('preview');
										}
										else if(frompage==='home_page_body'){
											document.querySelector("#onePicture").value = '/assets/images/pagebodyseg11.png';
											triggerEvent('preview');
										}
										else if(frompage==='fieldImages'){
											let pathArray = picturepath.split('/');
											if(pathArray.length===5){
												let pictureName = pathArray[4];
												let pictureInfo = pictureName.split('_');
												if(pictureInfo.length===9){
													showing_order = pictureInfo[7];
													let imageID = 'UploadImageID'+showing_order;
													if(document.getElementById(imageID)){
														document.querySelector("#"+imageID).innerHTML = '';
														document.querySelector("#ff"+showing_order).value = '';
													}
												}
											}
										}
										else if(frompage==='customFile'){
											let name = document.querySelector(`.popup_container [href="${picturepath}"]`).getAttribute('name');											
											let uploadBtn = document.querySelector(`input[name="${name}"]`);

											if(document.querySelector(`label[for='${name}'] span.required`)) uploadBtn.classList.add('required');

											if(filetype==='image'){
												document.querySelectorAll('.customPicture').forEach(item=>{
													item.remove();
												})
											}
											else if(filetype==='pdf'){
												document.querySelectorAll('.customPDF').forEach(item=>{
													item.remove();
												})
											}
											uploadBtn.style.display = '';
										}
									}
								}
								else{
									showTopMessage('alert_msg',Translate('Could not remove picture.'));
								}
							}
						},
					}
				}
			}
		);
		                               
	}
}


export function serialize(formID){
	let form = document.querySelector(formID)
	let formData = new FormData(form);
	let serializedData = {};
    for(let entry of formData.entries()) {
        let key = entry[0];
        let value = entry[1];
        if(/\[\]$/.test(key)){ //when key has [] at the last position, then asume that it should be an array
            if(serializedData[key]) serializedData[key].push(value); //if the array is already present, then push new value
            else serializedData[key] = [value]; //otherwise initialize an array
        }
        else serializedData[key] = value;
    }
	return serializedData;
}

//=========Time Clock==============//
export async function showTimeClockForm(segment3name){
    const jsonData = {};
	jsonData['segment3name'] = segment3name;

	let formGroup, inputField;
	let formDialog = cTag('div');
        const form = cTag('form', {'action': "#", name: "frmTimeClock", id: "frmTimeClock", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			let divErrorMsg = cTag('div', {id: "error_TimeClock",class: "errormsg"});
        form.appendChild(divErrorMsg);
            let timeClock = cTag('div', {id: "timeClockForm"});
                formGroup = cTag('div', {class: "flex validrow", "align":"left"});
                    let dateTimeTitle = cTag('div', {class: "columnSM4"});
						let dateTimeLabel = cTag('label');
						dateTimeLabel.innerHTML = Translate('Date Time');
					dateTimeTitle.appendChild(dateTimeLabel);
                formGroup.appendChild(dateTimeTitle);
					let dateTimeField = cTag('div', {class: "columnSM8"});
                        let fieldLabel = cTag('label', {id: "showDateTime"});
					dateTimeField.appendChild(fieldLabel);
                formGroup.appendChild(dateTimeField);
            timeClock.appendChild(formGroup);

                formGroup = cTag('div', {class: "flex", "align":"left", 'style': "align-items: center;"});
                    let employeeNoColumn = cTag('div', {class: "columnSM4"});
						let employeeNoLabel = cTag('label', {'for': "userEmpNo"});
						employeeNoLabel.innerHTML = Translate('Employee Number');
					employeeNoColumn.appendChild(employeeNoLabel);
                formGroup.appendChild(employeeNoColumn);
					let employeeNoField = cTag('div', {class: "columnSM8"});
                        let dinInGroup = cTag('div', {class: "input-group"});
                            inputField = cTag('input', {name: "userEmpNo", id: "userEmpNo", class: "form-control", 'value': "", 'type': "text", 'size': 20, 'maxlength': 20});
							inputField.addEventListener('keydown',event=>{if(event.which===13) checkValidEmpNumber()});
                        dinInGroup.appendChild(inputField);
                            let employeeSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Check Valid Employee Number')});
                            employeeSpan.addEventListener('click', checkValidEmpNumber);
                                let searchIcon = cTag('i', {class: "fa fa-search"});
							employeeSpan.appendChild(searchIcon);
                        dinInGroup.appendChild(employeeSpan);
					employeeNoField.appendChild(dinInGroup);
                formGroup.appendChild(employeeNoField);
            timeClock.appendChild(formGroup);

                formGroup = cTag('div', {class: "flex validrow", "align":"left"});
                    let clockInColumn = cTag('div', {class: "columnSM4"});
						let clockInLabel = cTag('label', {id: "clockinorout", 'style': "font-size:22px; font-weight:bold; text-align: center;", class: "borderbottom"});
					clockInColumn.appendChild(clockInLabel);
                formGroup.appendChild(clockInColumn);
                    let employeeName = cTag('div', {class: "columnSM8"});
						let employeeNameLabel = cTag('label', {id: "empName"});
					employeeName.appendChild(employeeNameLabel);
                formGroup.appendChild(employeeName);
            timeClock.appendChild(formGroup);

                formGroup = cTag('div', {class: "flex validrow", "align":"left", 'style': "align-items: center;"});
                    let pinTitle = cTag('div', {class: "columnSM4 "});
						let pinLabel = cTag('label', {'for': "pin"});
						pinLabel.innerHTML = Translate('PIN');
					pinTitle.appendChild(pinLabel);
                formGroup.appendChild(pinTitle);
                    let pinValue = cTag('div', {class: "columnSM8"});
                        let divInGroup = cTag('div', {class: "input-group"});
                            inputField = cTag('input', {name: "pin", id: "pin", class: "form-control", 'value': "", 'type': "text", 'size': 10, 'maxlength': 10});
                        divInGroup.appendChild(inputField);
                            let timeSpan = cTag('span', {class: "input-group-addon cursor", 'data-toggle': "tooltip", 'data-placement': "bottom", title: "", 'data-original-title': Translate('Check Valid Employee Number')});
                            timeSpan.addEventListener('click', ()=>{
								formDialog.closest('#popup').querySelector('footer .saveButton').click();
							});
								let searchIcon2 = cTag('i', {class: "fa fa-search"});
							timeSpan.appendChild(searchIcon2);
                        divInGroup.appendChild(timeSpan);
					pinValue.appendChild(divInGroup);
                formGroup.appendChild(pinValue);
            timeClock.appendChild(formGroup);
        form.appendChild(timeClock);

            //=====Hidden Fields for Pagination======//
        [
            { name: 'segment3name', value: segment3name},
            { name: 'validEmpNumber', value: 0 },
            { name: 'tuser_id', value: 0 },
            { name: 'time_clock_id', value: 0 },
        ].forEach(field=>{
            let input = cTag('input', {'type': "hidden", name: field.name, id: field.name, 'value': field.value});
            form.appendChild(input);
        });
	formDialog.appendChild(form);

    popup_dialog1000(Translate('Set Clock In / Out'),formDialog,AJsave_timeClock);

	setTimeout(function() {
		actionBtnClick('.btnmodel', Translate('Save'), 1);

		document.getElementById("userEmpNo").focus();	
	}, 500);
	document.querySelectorAll(".validrow").forEach(item=>{
		if(item.style.display !== 'none'){
			item.style.display = 'none';
        }
    });
	document.querySelector("#userEmpNo").addEventListener('keypress',function (e) {
		if(e.which === 13) {
			checkValidEmpNumber();
		}
	});
	document.querySelector( "#pin" ).addEventListener('keyup',function() {
		if(this.value !==''){
			actionBtnClick('.btnmodel', Translate('Save'), 0);
		}
		else{
			actionBtnClick('.btnmodel', Translate('Save'), 1);
		}
	});
	return false;
}

export async function checkValidEmpNumber(){
	let userEmpNo = document.getElementById("userEmpNo");
	if(userEmpNo.value !==''){            
		let jsonData = {};
		jsonData['userEmpNo'] = userEmpNo.value;
		const url = '/Common/checkValidEmpNumber';

		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			document.getElementById("validEmpNumber").value = data.validEmpNumber;
			document.getElementById("time_clock_id").value = data.time_clock_id;
			if(data.validEmpNumber===1){
				let timeClockForm = document.getElementById("timeClockForm");
				if(data.clockinorout==='1'){
					timeClockForm.style.background = "#90EE90";
					document.getElementById("clockinorout").innerHTML =  Translate('Clock In');
				}
				else{
					timeClockForm.style.background = "#FFCCCB";
					document.getElementById("clockinorout").innerHTML =  Translate('Clock Out');
				}
				document.getElementById("showDateTime").innerHTML = DBDateToViewDate(data.showDateTime);
				document.getElementById("tuser_id").value = data.user_id;
				document.getElementById("empName").innerHTML = data.empName;
				document.querySelectorAll(".validrow").forEach(oneRowObj=>{
					if(oneRowObj.style.display === 'none'){
						oneRowObj.style.display = '';
					}
				});
				document.getElementById("pin").focus();
			}
			else{
				document.getElementById("clockinorout").innerHTML = '';
				document.querySelectorAll(".validrow").forEach(oneRowObj=>{
					if(oneRowObj.style.display !== 'none'){
						oneRowObj.style.display = 'none';
					}
				});
				document.getElementById("pin").value = '';
				userEmpNo.focus();
				let errorTimeClock = document.getElementById("error_TimeClock");
				errorTimeClock.innerHTML = Translate('Invalid Employee Number');
				if(errorTimeClock.style.display === 'none'){
					errorTimeClock.style.display = '';
				}
				setTimeout(function() {
					if(errorTimeClock.style.display !== 'none'){
						errorTimeClock.style.display = 'none';
					}
				}, 5000);
			}
		
        }
	}
	else{
		userEmpNo.focus();
	}
}

export async function AJsave_timeClock(hidePopup){
    if(!document.getElementById("validEmpNumber")) return;
    
	let userEmpNo = document.getElementById("userEmpNo");
	let pin = document.getElementById("pin");
	let validEmpNumber = document.getElementById("validEmpNumber").value;
	let tuser_id = document.getElementById("tuser_id").value;

	if(userEmpNo.value !=='' && pin.value !=='' && validEmpNumber>0 && tuser_id>0){
        const jsonData = serialize('#frmTimeClock');
        const url = '/Common/AJsave_timeClock';
        
		fetchData(afterFetch,url,jsonData);

		function afterFetch(data){
			if(data.returnStr !=='Error'){
				let frompage = segment1;
				let segment3name = document.getElementById("segment3name").value;
				const popupContent = document.querySelector( ".popup_container #popup div" );
				popupContent.innerHTML = ''
					let p = cTag('p',{style:'color:teal;font-weight:bolder'}); 
					if(data.returnStr==='Clock_in') p.innerHTML = Translate('Successfully Clocked In');
					else p.innerHTML = Translate('Successfully Clocked Out');
				popupContent.appendChild(p);
				setTimeout(() => {
					hidePopup();
				}, 1500);
				if(frompage==='Time_Clock' && segment3name==='view'){
                    triggerEvent('filter');
				}
			}
			else{				
				pin.focus();
                let error_TimeClock = document.getElementById("error_TimeClock");
                error_TimeClock.innerHTML = Translate('Invalid PIN');
				if(error_TimeClock.style.display === 'none'){
					error_TimeClock.style.display = '';
				}
				setTimeout(function() {
					if(error_TimeClock.style.display !== 'none'){
						error_TimeClock.style.display = 'none';
					}
				}, 5000);
			}
        }		
	}
	else{
		userEmpNo.focus();
	}
}

//=========Contact US==============//
export function checkHelpForm(){
	let returnval = true;
	let helpemail = document.getElementById("helpemail").value;
	if(helpemail===''){
		returnval = false;
	}
	else if(emailcheck(helpemail)===false){
		returnval = false;
	}
	
	let helpsubject = document.getElementById("helpsubject").value;
	if(helpsubject===''){
		returnval = false;
	}
	let helpdescription = document.getElementById("helpdescription").value;
	if(helpdescription===''){
		returnval = false;
	}
	
	if(returnval === false){		
		document.querySelectorAll('.btnmodel').forEach(oneRowObj=>{
			oneRowObj.classList.add('is-disabled');
			oneRowObj.disabled = true;
		});
		return false;
	}
	else{
		document.querySelectorAll('.btnmodel').forEach(oneRowObj=>{
			oneRowObj.classList.remove('is-disabled');
			oneRowObj.disabled = false;
		});
		return true;
	}
}

export async function sendHelpMail(hidePopup){
	if(checkHelpForm()===false){return false;}
	else{
		actionBtnClick('.btnmodel', Translate('Sending'), 1);		

		// const jsonData = serialize("#frmhelpform");
		const url = '/Home/sendHelpMail';

		fetchData(afterFetch,url,document.getElementById("frmhelpform"),'formData');

		function afterFetch(data){
			if(data.returnStr==='sent'){
				showTopMessage('success_msg', Translate('Your request has been successfully sent.'));
				hidePopup();
			}
			else{
				document.getElementById("error_msg").innerHTML = Translate('Could not sent your mail. Try again.');
				actionBtnClick('.btnmodel', Translate('Send'), 0);
			}
		}
		return false;
	}
}

export async function showHelpPopup(){
    const jsonData = {};
    const url = '/Home/helpForm';

	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		let formGroup, inputField, requiredField;
		const formDialog = cTag('div');
			const form = cTag('form', {'action': "#", name: "frmhelpform", id: "frmhelpform", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
				let errormsg = cTag('div', {id: "error_msg", class: "errormsg"});
			form.appendChild(errormsg);

				formGroup = cTag('div', {class: "columnXS12", "align":"left"});
					let nameLabel = cTag('label', {'for': "helpname"});
					nameLabel.innerHTML = Translate('Your name');
				formGroup.appendChild(nameLabel);
					inputField = cTag('input', {name: "helpname", id: "helpname", class:"form-control helpForm", 'value': data.helpname, 'type': "text", 'size': 50, 'maxlength': 50});
				formGroup.appendChild(inputField);
			form.appendChild(formGroup);

				formGroup = cTag('div', {class: "columnXS12", "align":"left"});
					let emailLabel = cTag('label', {'for': "helpemail"});
					emailLabel.innerHTML = Translate('Email');
						requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML ='*';
					emailLabel.appendChild(requiredField);
				formGroup.appendChild(emailLabel);
					inputField = cTag('input', {'required': "required", name: "helpemail", id: "helpemail", class:"form-control helpForm", 'value': data.helpemail, 'type': "email", 'size': 50, 'maxlength': 50});
				formGroup.appendChild(inputField);
			form.appendChild(formGroup);

				formGroup = cTag('div', {class: "columnXS12", "align":"left"});
					let subjectLabel = cTag('label', {'for': "helpsubject"});
					subjectLabel.innerHTML = Translate('Subject');
						requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML ='*';
					subjectLabel.appendChild(requiredField);
				formGroup.appendChild(subjectLabel);
					inputField = cTag('input', {'required': "required", name: "helpsubject", id: "helpsubject", class:"form-control helpForm", 'value': "", 'type': "text", 'size': 150, 'maxlength': 150});
				formGroup.appendChild(inputField);
			form.appendChild(formGroup);

				formGroup = cTag('div', {class: "columnXS12", "align":"left"});
					let helpLabel = cTag('label', {'for': "helpdescription"});
					helpLabel.innerHTML = Translate('How can we help you?');
						requiredField = cTag('span', {class: "required"});
						requiredField.innerHTML ='*';
					helpLabel.appendChild(requiredField);
				formGroup.appendChild(helpLabel);
					let textarea = cTag('textarea', {'required': "required", name: "helpdescription", id: "helpdescription", 'rows': 4, class: "form-control helpForm"});
				formGroup.appendChild(textarea);
			form.appendChild(formGroup);
            
				formGroup = cTag('div', {class: "columnXS12", "align":"left"});
					let attachmentLabel = cTag('label', {'for': "attachment", 'style': "cursor: pointer;"});
					attachmentLabel.innerHTML = Translate('Attachment');
				formGroup.append(cTag('i',{class:'fa fa-paperclip',style:'margin-right:10px'}),attachmentLabel);
					let attachment = cTag('input', {'type': "file",accept:"image/*,.pdf,.doc,.docx,.txt", name: "attachment", id: "attachment", class: "helpForm cursor",style:'width:100%'});
				formGroup.appendChild(attachment);
			form.appendChild(formGroup);

				inputField = cTag('input', {'type': "hidden", name: "helpbrowser", id: "helpbrowser", 'value': navigator.userAgent});
			form.appendChild(inputField);
				inputField = cTag('input', {'type': "hidden", name: "helpurl", id: "helpurl", 'value': location.href});
			form.appendChild(inputField);
		formDialog.appendChild(form);
		
		popup_dialog600(Translate('Contact Us'), formDialog, Translate('Send'), sendHelpMail);
		
		setTimeout(function() {
			document.getElementById("helpname").focus();
			checkHelpForm();
			document.querySelectorAll(".helpForm").forEach(oneObj=>{
				oneObj.addEventListener('keyup', checkHelpForm);
			});
		}, 500);
	}
}

export function showthisurivalue(){
	let namevalue = document.getElementById("name").value;
	let errorid = document.getElementById('error_company_subdomain');
	errorid.innerHTML = '';							
	
	if(namevalue !==''){
		let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-";
		let IsValid = true;
		let Char;
		let subdomain = '';
		let i;
		for (i = 0; i < namevalue.length; i++){ 
			Char = namevalue.charAt(i); 
			Char = Char.replace(" ", '-');
			Char = Char.replace("'", '');
			Char = Char.replace('"', '');
			Char = Char.replace('.', '');
			Char = Char.replace(',', '');
			Char = Char.replace('!', '');
			Char = Char.replace('/', '-or-');
			Char = Char.replace('?', '');
			Char = Char.replace('&', 'and');								
			Char = Char.replace('$', 'dollar');
			Char = Char.replace('+', '-plus-');
			Char = Char.replace('&amp;', 'and');
			Char = Char.replace('(', '');
			Char = Char.replace(')', '');
			subdomain = subdomain+Char;
		}
		
		subdomain = subdomain.replace('--', '-');
		let subdomainvalue = '';
		for (i = 0; i < subdomain.length && IsValid === true; i++){ 
			Char = subdomain.charAt(i); 
			if (ValidChars.indexOf(Char) === -1){
				IsValid = false;
				subdomainvalue = subdomainvalue.substring(0,30);
				document.getElementById("name").value = subdomainvalue.toLowerCase();
				errorid.innerHTML = Translate('Invalid sub-domain');
				document.getElementById("name").focus();
				return false;
			}
			else{
				subdomainvalue = subdomainvalue+Char;
			}
		}
		
		subdomainvalue = subdomainvalue.substring(0,30);
		
		if(IsValid === false){
			document.getElementById("name").value = subdomainvalue.toLowerCase();
			return false;
		}
		else{
			document.getElementById("name").value = subdomainvalue.toLowerCase();
			return true;
		}
	}
}

export function multiSelectAction(idName11){
	let parent = document.querySelector('#'+idName11);
	if(parent){
		window.addEventListener('click', function(e){
			if (document.querySelector('#'+idName11) && !document.querySelector('#'+idName11).contains(e.target) && document.querySelector('#'+idName11).classList.contains('open')){
				document.querySelector('#'+idName11).classList.remove('open');
				document.querySelector('#'+idName11).querySelector('.dropdown-toggle').ariaExpanded = false;
			}
		});
		parent.addEventListener('click',function(){
			parent.classList.toggle("open");
			if(document.querySelector('#'+idName11))
				document.querySelector('#'+idName11).querySelector('.dropdown-toggle').ariaExpanded = true;
		})
	}
}		

//=======Pagination=========//
export function onClickPagination(){
	//everytime any filter function called, this function called as well. instead of calling displayTrancatedText in every filter we're calling it here.
	displayTrancatedText();
	
	let page, targetUrl;
	let setCurrentPage = function(url) {
		if(url) page = url.replace('/'+document.querySelector('#pageURI').value+'/', '');			
		else page = 1;
		document.querySelector("#page").value = page;
		triggerEvent('loadTable');
	};
	let pageNo = parseInt(document.querySelector("#page").value);
	if(isNaN(pageNo)){pageNo = 1;}
	if(pageNo===1){
		let pathname = window.location.pathname;
		let targetUrl1 = '/'+document.querySelector('#pageURI').value;
		targetUrl = '/'+document.querySelector('#pageURI').value+'/'+pageNo;
		if(pathname !==targetUrl && pathname !==targetUrl1){
			window.history.pushState({url: "" + targetUrl + ""}, '', targetUrl);
		}
	}	
	
	let pagination = document.querySelector("#Pagination");
	pagination.innerHTML = '';
	pagination.appendChild(createLinks());
			
	document.querySelectorAll('#Pagination ul li a').forEach(item=>{
		item.addEventListener('click',function(e){
			let disClassN = this.getAttribute('class');
			if(disClassN==='disabled'){return false;}
			e.preventDefault();
			targetUrl = this.getAttribute('href');
			const targetTitle = this.getAttribute('title');				
			window.history.pushState({url: "" + targetUrl + ""}, targetTitle, targetUrl);
			setCurrentPage(targetUrl);
		})
	});

	window.onpopstate = function(event) {
		setCurrentPage(event.state ? event.state.url : null);
	};
}

export function createLinks(){
	let page, li, a, activeCls, span, disabledCls, i;
	let total = document.querySelector("#totalTableRows").value;
	page = document.querySelector("#page").value;
	let limit = checkAndSetLimit();
	let uri = document.querySelector('#pageURI').value;
	
	if(parseInt(total)===0 || parseInt(limit)===0 || limit==='' || isNaN(parseInt(limit))){
		document.querySelector('#fromtodata').innerHTML = '0-0/0';
		return document.createDocumentFragment();
	}
	
	let num_edge = 2;	
	let numberOfPages = Math.ceil(total/limit);
	page = Math.floor(page);
	if(page>numberOfPages){page = numberOfPages;}
	let start1 = 1;
	let end1 = numberOfPages;
	let start2 = 0;
	let end2 = 0;
	let start3 = 0;
	let end3 = 0
	if(numberOfPages>num_edge){
		end1 = end2 = Math.floor(num_edge);
		if(numberOfPages>Math.floor(end1+num_edge) && page>end1 && page<=parseInt(numberOfPages-num_edge)){
			start2 = page;
			end2 = Math.floor(page+num_edge);
			if(Math.floor(page-1)>end1){
				start2 = Math.floor(page-1);
				end2 = Math.floor(page+1);
			}		
		}
		if(numberOfPages>end2){
			start3 = end3 = numberOfPages;
			if(Math.floor(numberOfPages-num_edge)>=end2){
				start3 = Math.floor(numberOfPages-num_edge+1);
			}
		}
	}
	
	let fromPag = Math.floor(Math.floor(Math.floor(page-1)*limit)+1);
	let toPag = Math.floor(page*limit);
	if(toPag>total){toPag = total;}
	if(fromPag>total){fromPag = 1;}
	
	document.querySelector('#fromtodata').innerHTML = fromPag+'-'+toPag+'/'+total;
	
	let html = document.createElement('ul');
	if(page > 1){
		li = cTag('li',{ 'class':`prev` });
			a = cTag('a',{ 'href':`/${uri}/${(page - 1)}` });
			a.innerHTML = '&laquo;';
		li.appendChild(a);
		html.appendChild(li);
	}
	else{
		li = cTag('li',{ 'class':`disabled prev` });
			a = cTag('a',{ 'class':`disabled`,'href':`javascript:void(0);` });
			a.innerHTML = '&laquo;';
		li.appendChild(a);
		html.appendChild(li);
	}
	 
	for ( i = start1; i <= end1; i++ ) {
		activeCls = '';
		if(page===i){activeCls = "active";}
		li = cTag('li',{ 'class':activeCls });
			a = cTag('a',{ 'href':`/${uri}/${i}` });
			a.innerHTML = i;
		li.appendChild(a);
		html.appendChild(li);
	}
	if ( end1 < start2 ) {
		if ( Math.floor(end1+1) < start2 ) {
			li = cTag('li',{ 'class':'disabled' });
				span = cTag('span');
				span.innerHTML = '..';
			li.appendChild(span);
			html.appendChild(li);
		}
		for ( i = start2; i <= end2; i++ ) {
			activeCls = '';
			disabledCls = '';
			if(page===i){
				activeCls = "active";
				disabledCls = "disabled";
			}
			li = cTag('li',{ 'class':activeCls });
				a = cTag('a',{ 'class':disabledCls,'href':`/${uri}/${i}` });
				a.innerHTML = i;
			li.appendChild(a);
			html.appendChild(li);
		}
	}
	if ( end2 < start3 ) {
		if ( Math.floor(end2+1) < start3 ) {
			li = cTag('li',{ 'class':'disabled' });
				span = cTag('span');
				span.innerHTML = '..';
			li.appendChild(span);
			html.appendChild(li);
		}
		for ( i = start3; i <= end3; i++ ) {
			activeCls = '';
			disabledCls = '';
			if(page===i){
				activeCls = "active";
				disabledCls = "disabled";
			}
			li = cTag('li',{ 'class':activeCls });
				a = cTag('a',{ 'class':disabledCls,'href':`/${uri}/${i}` });
				a.innerHTML = i;
			li.appendChild(a);
			html.appendChild(li);
		}
	}
	if(numberOfPages>page){
		li = cTag('li',{ 'class':`next` });
			a = cTag('a',{ 'href':`/${uri}/${(page + 1)}` });
			a.innerHTML = '&raquo;';
		li.appendChild(a);
		html.appendChild(li);
	}
	else{
		li = cTag('li',{ 'class':`disabled next` });
			a = cTag('a',{ 'class':`disabled`,'href':`javascript:void(0);` });
			a.innerHTML = '&raquo;';
		li.appendChild(a);
		html.appendChild(li);
	}	
	return html;
}

export function wysiwyrEditor(fieldIdName='description'){
    let activeHtmlArea = false;
    let styles = {
        radiousBorder:{
            'border':'1px solid #ccc',
            'border-radius':'4px',
            'display':'flex'       
        },
        item:{
            'padding':'6px 12px',
            'cursor':'pointer',
            'display':'flex',
			'align-items':'center'

        },
        nonSuppotredItem:{
            'padding':'6px 12px',
            'cursor':'not-allowed',
            'display':'flex',
			'align-items':'center',
            'opacity':'0.4'
        },
        hoveredItem:{
            'background':'#e6e6e6',
            'box-shadow':'inset 0 2px 4px rgb(0 0 0 / 15%), 0 1px 2px rgb(0 0 0 / 5%)'
        },
        unhoveredItem:{
            'background':'transparent',
            'box-shadow':'none'
        },
        separator:{
            'border-right':'1px solid #ccc',
        },
        toolbar:{
            'position':'relative',
            'list-style': 'none',
            'padding': 0,
            'display': 'flex',
            'flex-wrap':'wrap',
            'gap': '10px',
            'margin-bottom':'10px'
        },
        editingArea:{
            'padding':'10px 15px',
            'height':'250px',
            'max-height':'40vh',
            'width':'95%',
            'box-shadow':'0 0 1px gray'
        },
        actionButtonWrapper:{
            'position': 'absolute',
            'height': '100%',
            'background': 'rgba(199, 199, 199, 0)',
            'width': 0,
            'z-index': '100'
        },
        activeButton:{
            'opacity':1,
            'background':'transparent',
            'cursor':'pointer',
        },
        deactiveButton:{
            'opacity':'.4',
            'background':'#fbfbfb',
            'cursor':'not-allowed',
        }
    }
    let mouseHoverFunctions = {
        enter:function(){setStyles(this,styles.hoveredItem)},
        leave:function(){setStyles(this,styles.unhoveredItem)}
    }
   
    let editor = cTag('div',{'id':'wysiwyrEditor'});
    editor.appendChild(toolbar());
        let htmlArea = cTag('textarea',{ 'id':fieldIdName,'name':fieldIdName });
		htmlArea.addEventListener('blur',sanitizer);
        setStyles(htmlArea,styles.radiousBorder);
        setStyles(htmlArea,styles.editingArea);
        setStyles(htmlArea,{'display':'none'}); // initially hidden
        htmlArea.addEventListener('keyup',()=>{
            updateEditBox();
        })
    editor.appendChild(htmlArea);
        let editingArea = cTag('iframe',{'id':'editingArea'});
        setTimeout(() => {
            editingArea.contentDocument.designMode = "on";
            editingArea.contentDocument.addEventListener('keyup',()=>{
                updateDescription();
            })
            editingArea.contentDocument.addEventListener('keydown',(event)=>{
                if(event.ctrlKey && event.keyCode!=17){
					let keyCode = event.keyCode;
					if([66,73,83,85].includes(keyCode)){
						event.preventDefault();
						if(keyCode===66) editor.querySelector('[title="CTRL+B"]').click();
						else if(keyCode===73) editor.querySelector('[title="CTRL+I"]').click();
						else if(keyCode===83) editor.querySelector('[title="CTRL+S"]').click();
						else if(keyCode===85) editor.querySelector('[title="CTRL+U"]').click();
					};
                }
            })
            editingArea.contentDocument.addEventListener('selectionchange',highlightBtn)
        }, 500);
        setStyles(editingArea,styles.radiousBorder);
        setStyles(editingArea,styles.editingArea);
    editor.appendChild(editingArea);

    function highlightBtn(){
		let selection = editingArea.contentWindow.getSelection().anchorNode;
        if(!selection) return;
        let selectedNode = selection.parentNode;
        let commands = ['bold','italic','underline','small','insertUnorderedList','insertOrderedList'];
        let activButtons = [];
        let inactivButtons = [];

        commands.forEach(cmd=>{
            if(document.getElementById('editingArea').contentDocument.queryCommandState(cmd)) activButtons.push(cmd);
            else inactivButtons.push(cmd);
        });
        //identifying block
        if('BLOCKQUOTE'===selectedNode.tagName||selectedNode.closest('BLOCKQUOTE')) activButtons.push('BLOCKQUOTE');
        else inactivButtons.push('BLOCKQUOTE');
		//identifying text-type
		let textTypeFound = false;
		['P','H1','H2','H3','H4','H5','H6'].forEach((item,indx)=>{
			if(!textTypeFound){
				if(item===selectedNode.tagName||selectedNode.closest(item)){
					textTypeFound = true;
					if(indx===0) document.getElementById('text-type').innerHTML = 'Normal text';
					else document.getElementById('text-type').innerHTML = 'Heading '+indx;
				}
				else document.getElementById('text-type').innerHTML = 'Normal text';
			}
		});

        inactivButtons.forEach(cmd=>{
            let node = editor.querySelector('#'+cmd);
            setStyles(node,styles.hoveredItem);
            setHoverStyle(node);
        })
        
        activButtons.forEach(cmd=>{
            let node = editor.querySelector('#'+cmd);
            setStyles(node,styles.hoveredItem);
            removeHoverStyle(node);
        })
                
    }

    function toolbar(){
		let li, a, div, span;
        let ul = cTag('ul',{ 'class':`toolbar` });
        setStyles(ul,styles.toolbar);
            li = cTag('li',{ 'class':`dropdown`,'id':'wysiwyrEditorDropdown','style':"width: 138px;" });
                a = cTag('a',{ 'class':`btn defaultButton dropdown-toggle`,'data-toggle':`dropdown` });
                setStyles(a,styles.radiousBorder);
                setStyles(a,styles.item);
                setHoverStyle(a);
					span = cTag('span',{ 'class':`fa fa-font` });
					span.innerHTML = '&nbsp;'
                a.appendChild(span);
                    let currentFont = cTag('span',{ 'class':`current-font`,'id':'text-type' });
                    currentFont.innerHTML = ' Normal text';
                a.appendChild(currentFont);
                a.appendChild(cTag('b',{ 'class':`fa fa-caret-down`,'style':'margin-left:5px;font-size:12px' }));
            li.appendChild(a);
                let dropdownMenu = cTag('ul',{ 'class':`dropdown-menu`, 'style':'left: 0 !important;width: 20%;' });
                ['Normal text','Heading 1','Heading 2','Heading 3','Heading 4','Heading 5','Heading 6'].forEach(item=>{
                    let li1 = cTag('li');
                        a = cTag('a');
                        a.innerHTML = item;
                        a.addEventListener('click',()=>{
                            currentFont.innerHTML = ' '+item;
                            if(/Heading/.test(item)) actionButtonHandler(null,`H${item.match(/Heading (\d)/)[1]}`);
                            else actionButtonHandler(null,'P');
                        })
                    li1.appendChild(a);
                    dropdownMenu.appendChild(li1)
                })
            li.appendChild(dropdownMenu);
        ul.appendChild(li);
            li = cTag('li');
                div = cTag('div');
                setStyles(div,styles.radiousBorder);
                [
                    {type:'bold',style:'font-weight:bold'},
                    {type:'italic',style:'font-style:italic'},
                    {type:'underline',style:'text-decoration:underline'},
                    {type:'small',style:''},
                ].forEach((item,indx)=>{
                        let cmd = item.type;
                        if(cmd === 'small') cmd = 'decreaseFontSize';
                        let firstLetter = item.type[0];
                        firstLetter = firstLetter.toUpperCase();
                        span = cTag('span',{ 'id':item.type,'title':`CTRL+${firstLetter}`,'style':item.style });
                        checkForSupport(cmd,span,function(){actionButtonHandler(cmd)});
                        if(indx!==3) setStyles(span,styles.separator);
                        span.innerHTML = firstLetter+item.type.slice(1);
                    div.appendChild(span);
                })
            li.appendChild(div);
        ul.appendChild(li);
            li = cTag('li');
                span = cTag('span',{ 'id':'BLOCKQUOTE','class':`fa fa-quote-left` });
                setStyles(span,styles.radiousBorder);
                setStyles(span,{'height':'100%'});
                checkForSupport("formatBlock",span,function(){actionButtonHandler(null,"BLOCKQUOTE")});
            li.appendChild(span);
        ul.appendChild(li);
            li = cTag('li');
                div = cTag('div');
                setStyles(div,styles.radiousBorder);
                setStyles(div,{'height':'100%'});
                [
                    {cmd:'insertUnorderedList',title:'Unordered list',class:'fa-list-ul'},
                    {cmd:'insertOrderedList',title:'Ordered list',class:'fa-list-ol'},
                    {cmd:'Outdent',title:'Outdent',class:'fa-outdent'},
                    {cmd:'Indent',title:'Indent',class:'fa-indent'},
                ].forEach((item,indx)=>{
                        span = cTag('span',{ 'id':item.cmd, 'class':`fa ${item.class}`,'title':item.title, });
                        if(indx!==3) setStyles(span,styles.separator);
                        checkForSupport(item.cmd,span,function(){actionButtonHandler(item.cmd)});                       
                    div.appendChild(span);
                })
            li.appendChild(div);
        ul.appendChild(li);
            li = cTag('li');
                div = cTag('div');
                    span = cTag('span',{ 'title':`Edit HTML` });
                    span.appendChild(cTag('span',{'class':`fa fa-pencil`,'style':'font-size:12px;margin-right:5px'}))
                    span.append('HTML');
                    span.addEventListener('click',htmlEditorHandler)
                    setStyles(span,styles.radiousBorder);
                    setStyles(span,styles.item);
                    setHoverStyle(span);
                div.appendChild(span);
            li.appendChild(div);
        ul.appendChild(li);
        return ul;
    }
    function htmlEditorHandler(){
        this.classList.toggle('active');
        if(this.classList.contains('active')){
            setStyles(this,styles.hoveredItem)
            removeHoverStyle(this);
        } 
        else{
            setStyles(this,styles.unhoveredItem);
            setHoverStyle(this);
        } 

        activeHtmlArea = !activeHtmlArea;
        if(activeHtmlArea){
            editingArea.style.display = 'none';
            htmlArea.style.display = '';
            editor.querySelectorAll('.action-button').forEach(buttonItem=>{
                setStyles(buttonItem,styles.deactiveButton);
                removeHoverStyle(buttonItem);
            });
        }else{
            editingArea.style.display = '';
            htmlArea.style.display = 'none';
            editor.querySelectorAll('.action-button').forEach(buttonItem=>{
                setStyles(buttonItem,styles.activeButton);
                setHoverStyle(buttonItem);
            });
            highlightBtn()
        }
    }
    function actionButtonHandler(cmd,tag){        
        let doc = editingArea.contentDocument;
        if(cmd) doc.execCommand(cmd)
        else doc.execCommand('formatBlock',false,`<${tag}>`);     
        updateDescription();
        highlightBtn();
    }
    function updateDescription(){
        let fieldId = editor.querySelector('#'+fieldIdName);
        fieldId.value = editingArea.contentDocument.body.innerHTML;
    }
    function updateEditBox(){
        editingArea.contentDocument.body.innerHTML = editor.querySelector('#'+fieldIdName).value;
    }
    function checkForSupport(cmd,node,cbf){
        if(document.queryCommandSupported(cmd)){
            setStyles(node,styles.item);
            setHoverStyle(node);
            node.addEventListener('click',cbf);
            node.classList.add('action-button');
        }
        else{
            setStyles(node,styles.nonSuppotredItem);
        }
    }

    function setStyles(node,stylesObj){
		for (const property in stylesObj) {
			node.style[property] = stylesObj[property];
		}
	}
    function setHoverStyle(node){
        setStyles(node,styles.unhoveredItem);
        node.addEventListener('mouseenter',mouseHoverFunctions.enter)
        node.addEventListener('mouseleave',mouseHoverFunctions.leave)
    }
    function removeHoverStyle(node){
        node.removeEventListener('mouseenter',mouseHoverFunctions.enter)
        node.removeEventListener('mouseleave',mouseHoverFunctions.leave)
    }
    return editor;
}

//=========Auto Complete=======//

export function customAutoComplete(node,options){
	const delay = options.delay||500;
	let timeoutID;
	let loader;
    let searchingState = false;
	let enterKeyPressed = false;
	let autoCompleteResults;
    let sourceData=[];
	node.style.paddingRight = '30px';
    node.addEventListener('keyup',async function(event){
        hide();
		enterKeyPressed = false;
		if (event.which === 13){
			clearTimeout(timeoutID);
			node.blur();
			enterKeyPressed = true;
			hide();
			return;
		}
		clearTimeout(timeoutID);
		if(loader) loader.remove();
		timeoutID = setTimeout(async function(){
			if(node.value.length>=options.minLength){
				if(!autoCompleteResults) autoCompleteResults = cTag('ul',{ 'class':`autoCompleteResults`});
				else autoCompleteResults.innerHTML = '';
				startLoading();
                searchingState = !searchingState;//toggle state
				if(options.search) options.search();
				await options.source(node.value,addToSource);
				loader.remove();
                searchingState = !searchingState;
				if(enterKeyPressed || searchingState) return;
				if(sourceData.length===0){
						let li = cTag('li',{ 'class':`ui-menu-item errormsg` });
						li.innerHTML = Translate('Nothing found');
					autoCompleteResults.appendChild(li);
				}
				else{
					sourceData.forEach(item=>{
						let li;
						if(options.renderItem) li =  options.renderItem(item);
						else{
							if(segment1==='Customers'&&segment2==='view'&&item.id===Number(segment3)) return;
							else if(segment1==='Manage_Data' && !['archive_Data','eu_gdpr'].includes(segment2)){ 
								if(segment2==='sview'){
                                    if(item.id===Number(segment3)) return;
                                } 
								else if(item.id===Number(document.getElementById(segment2+'_id').value)) return;
							}
							li = cTag('li',{ 'class':`ui-menu-item` });
								const div = cTag('div');					
								div.append(item.label);
							li.appendChild(div)
						}
						li.addEventListener('click',function(event){
							clearTimeout(timeoutID);
							hide();
							options.select(event,item);
						});
						autoCompleteResults.appendChild(li);
					});
				}
				const nodeInfo = node.getBoundingClientRect();
				if(node.closest('.popup_container')) autoCompleteResults.style.top = nodeInfo.top+nodeInfo.height+'px';
				else autoCompleteResults.style.top = window.scrollY+nodeInfo.top+nodeInfo.height+'px';
				autoCompleteResults.style.left = nodeInfo.left+'px';
				autoCompleteResults.style.width = nodeInfo.width+'px';
				if(node.closest('.popup_container')) autoCompleteResults.style.position = 'fixed';
				document.body.appendChild(autoCompleteResults);
				document.addEventListener('mousedown',hideOnMouseDown);
			}
		}, delay);
    });
	function startLoading(){
		loader = cTag('span',{'id':'autoSearchLoader'});
		let {right:nodeRight,top:nodeTop,height:nodeHeight} = node.getBoundingClientRect();
        loader.style.left = (nodeRight-25)+'px';
        loader.style.top = (nodeTop+((nodeHeight-15)/2))+'px';
		document.body.appendChild(loader);
	}
    function addToSource(data){
        sourceData = data;
    }
    function hide(){
		if(autoCompleteResults) autoCompleteResults.remove();
		document.removeEventListener('mousedown',hideOnMouseDown);
    }
	node.hide = hide;

	function hideOnMouseDown(event){
		if(!(autoCompleteResults.contains(event.target)||event.target === node)) hide();
	}
}

export function AJautoComplete(fieldIdName,selectCBF){
	let frompage = segment1;
	let minLen = 2;
	if(fieldIdName==='invoice_no'){minLen = 1;}
	const node = document.querySelector("#"+fieldIdName);
	if(node){
		customAutoComplete(node,{
			minLength: minLen,
			source: async function (request, response) {
				let fnName = fieldIdName;
				if(fieldIdName==='posupplier'){fnName = 'supplier';}
				else if(fieldIdName==='assign_to'){fnName = 'employee';}
				else if(fieldIdName==='customer'){fnName = 'customer_name';}
	
				const jsonData = {"keyword_search":request, 'fieldIdName':fieldIdName, 'frompage':frompage};
				const url = "/Common/AJautoComplete_"+fnName;

				await fetchData(afterFetch,url,jsonData,'JSON',0);

				function afterFetch(data){
					response(data.returnStr);
				}
			},
			select: function( event, info ) {
				// console.log(fieldIdName);
				node.value = info.label;
				if(frompage==='Purchase_orders'){
					selectCBF(info);
					return;
				}
				if(document.querySelector("#lbphone")){document.querySelector("#lbphone").innerHTML = info.contact_no;}
				if(document.querySelector("#customers_id")){document.querySelector("#customers_id").value = info.id;}
				if(document.querySelector("#changed_customer_id" )){document.querySelector( "#changed_customer_id" ).value =  info.id;}
				else if(document.querySelector("#customer_id" )){document.querySelector( "#customer_id" ).value =  info.id;}
				if(document.querySelector("#suppliers_id" )){document.querySelector( "#suppliers_id" ).value =  info.id;}
				if(document.querySelector("#email_address" )){document.querySelector( "#email_address" ).value =  info.email;}
				if(fieldIdName==='product' && document.querySelector("#sku")){document.querySelector( "#sku" ).value = info.sku;	}
				if(fieldIdName==='product' && document.querySelector('#product_archive')){archiveProduct();return false;}
				if(fieldIdName==='product' && document.querySelector('#btnAI_form')){searchSKU();return false;}
				// if(fieldIdName==='customer_name' && document.querySelector('#customers_archive')){selectCBF();return false;}
				// if(fieldIdName==='supplier' && document.querySelector('#suppliers_archive')){selectCBF();return false;}
				if(fieldIdName==='invoice_no' && document.querySelector('#Invoices_archive')){selectCBF();return false;}
				
				if(fieldIdName==='customer_name' && info.am !==''){alert_dialog(Translate('Alert message'), info.am, Translate('Ok'));}
				
				if(document.querySelector("#errmsg_customer_id")){document.getElementById('errmsg_customer_id').innerHTML = '';}
							
				if(frompage==='POS'){
					selectCBF('customer_id',info);
					document.getElementById('editCustomerHide').style.display = '';
				}
				else if(frompage==='Livestocks'){
					// debugger					
					if(fieldIdName==='supplier'){
						
						document.getElementById('supplier').value = info.label;
						node.parentElement.querySelector("#supplier_id").value = info.id;
					} else if(fieldIdName==='product'){
						
						document.getElementById('product').value = info.label;
						node.parentElement.querySelector("#supplier_id").value = info.id;
					} else if(fieldIdName==='lsproduct'){
						
						document.getElementById('lsproduct').value = info.label;
						// console.log(info.label);
						node.parentElement.querySelector("#lsproduct_id").value = info.id;
						return true;
					} else if(fieldIdName==='plsproduct'){
						
						document.getElementById('plsproduct').value = info.label;
						node.parentElement.querySelector("#plsproduct_id").value = info.id;
						return true;
					}					
				}
				else if(frompage==='Repairs'){
					
					if(segment2==='edit') document.getElementById('customer_name').setAttribute('readonly','');
					else if(document.getElementsByClassName("notify_how").length>0 && document.frmrepairs.notify_how.checked){
						const notify_how = document.frmrepairs.notify_how.value;
						const notify_email = document.getElementById('notify_email');
						const notify_sms = document.getElementById('notify_sms');
						if(notify_how===1){
							notify_email.setAttribute('required', true);
							notify_email.style.display = '';
							notify_email.value = info.email;
							notify_sms.setAttribute('required', false);
							notify_sms.value = '';
							notify_sms.style.display = 'none';
						}
						else{
							notify_email.setAttribute('required', false);
							notify_email.value = '';
							notify_email.style.display = 'none';
							notify_sms.setAttribute('required', true);
							notify_sms.style.display = '';
							notify_sms.value = info.contact_no;
						}
					}
					if(document.querySelector("#customer_devices")){selectCBF();}
				}
				else if(frompage==='Orders' && segment2==='edit'){
					
					document.getElementById('customer_name').setAttribute('readonly','');
				}
				
				if((fieldIdName==='customer_name' && document.querySelector( "#toCustomerInfo" ))||(fieldIdName==='supplier' && document.querySelector( "#toSupplierInfo" )) ||(fieldIdName==='product' ) ||(fieldIdName==='lsproduct' ) ||(fieldIdName==='plsproduct' )){
                    document.querySelectorAll('.popup_footer_button')[1].style.display = '';//show Merge
                    document.getElementById(fieldIdName).value = '';
					const nameSplit = info.label.split('(');
					const htmlStr = document.createDocumentFragment();
						let nameDiv = cTag('div', {'style': "margin-bottom: 10px; font-weight: bold;"});
						nameDiv.append(Translate('Name')+': ');
							let nameSpan = cTag('span', {'style': 'color: #969595;'});
							nameSpan.innerHTML = nameSplit[0];
						nameDiv.appendChild(nameSpan);
					htmlStr.appendChild(nameDiv);
						let phoneDiv = cTag('div', {'style': "margin-bottom: 10px; font-weight: bold;"});
						phoneDiv.append(Translate('Phone No.')+': ');
							let phoneSpan = cTag('span', {'style': 'color: #969595;'});
							phoneSpan.innerHTML = info.contact_no;
						phoneDiv.appendChild(phoneSpan);
					htmlStr.appendChild(phoneDiv);
						let emailDiv = cTag('div', {'style': "margin-bottom: 10px; font-weight: bold;"});
						emailDiv.append(Translate('Email')+': ');
							let emailSpan = cTag('span', {'style': 'color: #969595;'});
							emailSpan.innerHTML = info.email;
						emailDiv.appendChild(emailSpan);
					htmlStr.appendChild(emailDiv);
                    if(fieldIdName==='customer_name'){
                        document.querySelector( "#toCustomerInfo" ).innerHTML = '';
                        document.querySelector( "#toCustomerInfo" ).append(htmlStr);
                        document.querySelector("#tocustomers_id").value = info.id;
                    } else if(fieldIdName==='lsproduct'){
                        // document.querySelector( "#toPedigreeInfo" ).innerHTML = '';
                        // document.querySelector( "#toPedigreeInfo" ).append(htmlStr);
                        // document.querySelector("#tocustomers_id").value = info.id;
                    }
                    else{
                        document.querySelector( "#toSupplierInfo" ).innerHTML = '';
                        document.querySelector( "#toSupplierInfo" ).append(htmlStr);
                        document.querySelector("#tosuppliers_id").value = info.id;
                    }
				}
				
				if(document.querySelector( "#customerNameField" )){
					document.getElementById('customer_name').value = info.label;
					document.getElementById('customer_name').setAttribute('readonly', '');
					if(segment1 === 'POS') {
						document.getElementById('changeCustomerId').style.display = '';
						document.getElementById('editCustomerHide').style.display = '';
						document.getElementById('newCustomerId').style.display = 'none';
					}
				}
				return false;
			}
		});
	}
}

export function AJautoComplete_headerSearch(){
	const headerSearch = document.getElementById("s");
	if(headerSearch){
		headerSearch.addEventListener('keydown',function(event){
			if(event.which===13){
				getSearchedItem(this.value,(searchedItem)=>{
					if(searchedItem.length>0) afterSelect(false,searchedItem[0]);
					else{
						showTopMessage('alert_msg',Translate('Nothing found'));
						this.value = '';
					}
				});
			}
		})
		customAutoComplete(headerSearch,{
			minLength:2,
			source: getSearchedItem,
			select: afterSelect
		});
	}
	async function getSearchedItem(request, response) {
		const jsonData = {"keyword_search":request};
		const url = "/Search/AJ_globalsearch";

		await fetchData(afterFetch,url,jsonData,'JSON',0);

		function afterFetch(data){
			response(data.returnStr);
		}
	}
	function afterSelect( event, info ) {				
		headerSearch.value = info.lv;
		
		if(info.t==='m'){
			const url = "/IMEI/view/"+info.lv;
			window.location=url;
		}
		else if(info.t==='r'){
			const url = "/Repairs/edit/"+info.i;
			window.location=url;
		}
		else if(info.t==='o'){
			const url = "/Orders/edit/"+info.i;
			window.location=url;
		}
		else if(info.t==='s'){
			const url = '/Invoices/view/'+info.i;
			window.location=url;
		}
		else if(info.t==='p'){
			const url = '/Purchase_orders/edit/'+info.i;
			window.location=url;
		}
		else if(info.t==='t'){
			const url = '/Repairs/edit/'+info.i;
			window.location=url;
		}
		else if(info.t==='c'){
			const url = "/Customers/view/"+info.i;
			window.location=url;
		}
		
		return false;
	}
}


export function autocompleteIMEI(){
	const qty_or_imei = document.getElementById('qty_or_imei');
	if(qty_or_imei){
		customAutoComplete(qty_or_imei,{								
			minLength:2,
			source: async function (request, response) {
				const jsonData = {
				"supplier_id":document.getElementById('supplier_id').value,
				 "product_id":document.getElementById('product_id').value, 
				 "item_number":request
				};
				const url = "/Purchase_orders/AJ_showitem_numberDropdown";
	
				await fetchData(afterFetch,url,jsonData,'JSON',0);

				function afterFetch(data){
					response(data.returnStr);
				}		
			},
			select: function( event, info ) {
				qty_or_imei.value = info.label;	
				document.getElementById("item_id").value = info.item_id;
				return false;
			}
		});
		qty_or_imei.addEventListener('keydown',function (e) {
			if(e.which === 13) {
				qty_or_imei.hide();
			}            
		});
	}
}

export function uploadImage(showing_order){
	const form_for = 'repairs';
	const table_id = document.frmFormsData.table_id.value;
	const forms_id = document.frmFormsData.forms_id.value;
	const forms_data_id = document.frmFormsData.forms_data_id.value;
	const fileprename = form_for+'_'+table_id+'_forms_'+forms_id+'_data_'+forms_data_id+'_ID_'+showing_order+'_';
	
	let input;
	const dialogConfirm = cTag('div');
		const form = cTag('form', {name: "frmupload", id: "frmupload", 'action': "/Common/uploadpicture", 'enctype': "multipart/form-data", 'method': "post", 'accept-charset': "utf-8"});
			input= cTag('input', {name: "filename", id: "filename", 'type': "file"});
		form.appendChild(input);
			input= cTag('input', {name: "frompage", id: "frompage", 'type': "hidden", 'value': "fieldImages"});
		form.appendChild(input);
			input= cTag('input', {'type': "hidden", name: "MAX_FILE_SIZE", 'value': 30000});
		form.appendChild(input);
			input= cTag('input', {name: "fileprename", id: "fileprename", 'type': "hidden", 'value': fileprename});
		form.appendChild(input);
			input= cTag('input', {name: "oldfilename", id: "oldfilename", 'type': "hidden", 'value': ""});
		form.appendChild(input);
			let span = cTag('span', {id: "errmsg_filename", class: "errormsg"});
		form.appendChild(span);
	dialogConfirm.appendChild(form);	

	popup_dialog(
		dialogConfirm,
		{
			title:Translate('Upload Image'),
			width:400,
			buttons:{
				'Cancel':{
					text: Translate('Cancel'), 
					class: 'btn defaultButton', 
					click: function(hide) {
						hide();
					}
				},
				'Upload':{
					text: Translate('Upload'), 
					class: 'btn saveButton btnupload', 
					click: formSubmit
				},
			},
		}
	);
	
	const targetval = '#UploadImageID'+showing_order;
	
	async function formSubmit(hidePopup){
		if(!(beforeSubmit()===false)){
			activeLoader();
			const url = form.getAttribute('action');			
			fetchData(afterFetch,url,form,'formData',0);
			function afterFetch(data){
				hideLoader();
				let target = document.querySelector(targetval);			
				target.innerHTML = '';
					const currentPicture = cTag('div',{ 'class':'currentPicture' });
					currentPicture.appendChild(cTag('img',{ 'class':'img-responsive','src': data.returnStr }));
					currentPicture.addEventListener('mouseenter',function(){
						this.appendChild(cTag('div',{'class':"deletedicon",'click':()=>AJremove_Picture(data.returnStr,'fieldImages')}));
					});
					currentPicture.addEventListener('mouseleave',function(){
						currentPicture.querySelector('.deletedicon').remove();
					});	
				target.appendChild(currentPicture);
				const picturepath = document.querySelector(targetval).querySelector("img").getAttribute("src");
				document.querySelector("#ff"+showing_order).value = picturepath;
				hidePopup();
			}

		} 
	}

	function beforeSubmit(){
		const uploadBtn = document.querySelector(".btnupload");
		document.querySelector("#errmsg_filename").innerHTML = '';
		
		//check whether browser fully supports all File API
		if (window.File && window.FileReader && window.FileList && window.Blob)
		 {
		
			if( !document.querySelector('#filename').value){	
				document.querySelector("#errmsg_filename").innerHTML = Translate('Missing picture');
				return false
			}
			
			const fsize = document.querySelector('#filename').files[0].size; //get file size
			const ftype = document.querySelector('#filename').files[0].type; // get file type
			
			//allow only valid image file types 
			switch(ftype)
			{
				case 'image/png': case 'image/gif': case 'image/jpeg': case 'image/pjpeg':
					break;
				default:
					document.querySelector("#errmsg_filename").innerHTML = ftype+' is '+Translate('Unsupported file type');
					return false
			}

			//Allowed file size is less than 1 MB (1048576)
			if(fsize>4194304){
				const bsize = cTag('b');
				bsize.innerHTML = bytesToSize(fsize);
				document.querySelector("#errmsg_filename").append(bsize,Translate('Too big Image file! <br />Please reduce the size of your photo using an image editor.'));
				return false
			}
			
			btnEnableDisable(uploadBtn,Translate('Uploading'),true);

			document.querySelector("#errmsg_filename").innerHTML = "";  
		 }
		 else{
			btnEnableDisable(uploadBtn,Translate('Uploading'),false);
			document.querySelector("#errmsg_filename").innerHTML = Translate('Please upgrade your browser, because your current browser lacks some new features we need!');
			 return false;
		 }
	}
}

export function historyTable(title,hiddenProperties,haveSignatureBtn=false){
	let page = 1;
	let pathArray = window.location.pathname.split('/');
	if(pathArray.length>4) page = parseInt(pathArray[4]);

	hiddenProperties = {
		'pageURI':segment1+'/'+segment2+'/'+segment3,
		'page':page,
		'rowHeight':'34',
		'totalTableRows':0,
		'publicsShow':0,
		...hiddenProperties
	}
	const widget = cTag('div', {class: "cardContainer", 'style': "margin-bottom: 10px;"});
	//=====Hidden Fields for Pagination======//
	for (const key in hiddenProperties){		
		widget.appendChild(cTag('input', {'type': "hidden",name: key, id: key, 'value': hiddenProperties[key]}));
	}

		const widgetHeader = cTag('div', {class: "cardHeader"});
			const widgetHeaderRow = cTag('div', {class: "flexSpaBetRow"});
				const widgetHeaderName = cTag('div', {class: "columnXS12 columnSM4", 'style': "margin: 0;"});
					const widgetHeaderTitle = cTag('h3');
					widgetHeaderTitle.innerHTML = title;
				widgetHeaderName.appendChild(widgetHeaderTitle);
			widgetHeaderRow.appendChild(widgetHeaderName);

				const sortDropDown = cTag('div', {class: "columnXS6 columnSM4", 'style': "margin: 0;"});
					const selectHistory = cTag('select', {class: "form-control", 'style': "margin-top: 2px;", name: "shistory_type", id: "shistory_type"});
					selectHistory.addEventListener('change', ()=>triggerEvent('filter'));
						const historyOption = cTag('option', {'value': ""});
						historyOption.innerHTML = Translate('All Activities');
					selectHistory.appendChild(historyOption);
				sortDropDown.appendChild(selectHistory);
			widgetHeaderRow.appendChild(sortDropDown);

				const buttonTitle = cTag('div', {class: "columnXS6 columnSM4 flexEndRow", 'style': "margin:0; gap:10px; align-items: center;"});
				if(haveSignatureBtn){
					let signatureButton = cTag('button',{ 'id':'digital_signature_btn','href':`javascript:void(0);`,'class':`btn defaultButton` });
						signatureButton.innerHTML = Translate('Add Digital Signature');
						if(getDeviceOperatingSystem() !='unknown'){
							signatureButton.innerHTML = '';
							signatureButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', `${Translate('Digital Signature')}`);
						}
					buttonTitle.appendChild(signatureButton);
				}
					const noteButton = cTag('button', {class: "btn defaultButton"});
					noteButton.innerHTML = Translate('Add New Note');
					if(getDeviceOperatingSystem() !=='unknown'){
						noteButton.innerHTML = '';
						noteButton.append(cTag('i',{ 'class':`fa fa-plus` }), ' ', `${Translate('Note')}`);
					}
					noteButton.addEventListener('click', function(){AJget_notesPopup(0);});
				buttonTitle.appendChild(noteButton);
			widgetHeaderRow.appendChild(buttonTitle);
		widgetHeader.appendChild(widgetHeaderRow);
	widget.appendChild(widgetHeader);

		const activityDiv = cTag('div', {class: "cardContent", 'style': "padding: 0;"});
			const divTable = cTag('div', {class: "flexSpaBetRow"});
				const divTableColumn = cTag('div', {class: "columnXS12", 'style': "margin: 0; padding: 0;"});
					const noMoreTables = cTag('div', {id: "no-more-tables"});
						const activityTable = cTag('table', {class: "table-bordered table-striped table-condensed cf listing", 'style': "margin-top: 2px;"});
							const activityHead = cTag('thead', {class: "cf"});
								const columnNames = activityFieldAttributes.map(colObj=>(colObj.datatitle));
								const activityHeadRow = cTag('tr');
									const thCol0 = cTag('th', {'style': "width: 80px;"});
									thCol0.innerHTML = columnNames[0];

									const thCol1 = cTag('th', {'style': "width: 80px;"});
									thCol1.innerHTML = columnNames[1];

									const thCol2 = cTag('th', {'width': "20%"});
									thCol2.innerHTML = columnNames[2];

									const thCol3 = cTag('th', {'width': "20%"});
									thCol3.innerHTML = columnNames[3];

									const thCol4 = cTag('th');
									thCol4.innerHTML = columnNames[4];
								activityHeadRow.append(thCol0, thCol1, thCol2, thCol3, thCol4);
							activityHead.appendChild(activityHeadRow);
						activityTable.appendChild(activityHead);
							const activityBody = cTag('tbody', {id: "tableRows"});
						activityTable.appendChild(activityBody);
					noMoreTables.appendChild(activityTable);
				divTableColumn.appendChild(noMoreTables);
			divTable.appendChild(divTableColumn);
		activityDiv.appendChild(divTable);
		addPaginationRowFlex(activityDiv,true);
	widget.appendChild(activityDiv);
	return widget;
}

export function displayTrancatedText(){
	document.querySelectorAll('.anchorfulllink').forEach(item=>{
		if(item.scrollWidth>item.clientWidth){
			let TruncatedElement = item.parentNode;
			let title = TruncatedElement.getAttribute('data-title');
			TruncatedElement.style.position = 'relative';
				let span = cTag('span',{class:'ellipsis'});
				if(getDeviceOperatingSystem()==='unknown'){
					span.setAttribute('data-toggle',"tooltip");
					span.setAttribute('data-original-title',item.innerText);
					tooltip(span);
				}else{
					span.addEventListener('click',()=>{
						let dialogConfirm = cTag('div');
						dialogConfirm.innerHTML = item.innerText;
						popup_dialog(dialogConfirm,{'title':title});
					});
				}
				span.innerText = '...';
			TruncatedElement.appendChild(span);
		} 
	})
}

export function lefsidemenubar(){
	let width, nav, sideBar, sidebarWrapper;
	width = window.innerWidth;
	nav = document.getElementById('fanav');
	sideBar = document.getElementById('sideBar');
	sidebarWrapper = document.getElementById('settingsleftsidemenubar');

	sidebarWrapper.addEventListener('click', function(){
		if(sideBar.style.display === 'none'){
			sideBar.style.display = '';
		}
		else{
			sideBar.style.display = 'none';
		}
	})

	if(width>767  ){
		nav.style.display = 'none';
		sideBar.style.display = '';
	}
	else{
		nav.style.display = '';
		sideBar.style.display = 'none';
	}
}

export function leftsideHide(menuID, menuClass){
	if(document.querySelector('#'+menuID)){
		document.querySelector('#'+menuID).addEventListener('click', event=>{
			document.querySelectorAll('.'+menuClass).forEach(oneTag=>{
				oneTag.classList.toggle('settingslefthide');
			});
		});
	}
}


function createHeader(){	
	let a,span,li;
	const header = cTag('header',{ 'class':`flexSpaBetRow` });
		let headerDiv = cTag('div',{ 'class':`columnLG7 columnMD5 columnSM4 columnXS12` });
			const h1 = cTag('h1',{ 'style':`padding-left: 20px` });
				a = cTag('a',{ 'href':`javascript:void(0);`,'title':companyName,'id':`settingsleftsidemenubar` });
				a.appendChild(cTag('i',{ 'class':`fa fa-navicon`,'style':`display: none`,'id':`fanav` }));
			h1.appendChild(a);
			if(accountsInfo[1]>0){
					a = cTag('a',{ 'style':`margin-left: 10px; font-weight: bold`,'href':`/Home`,'title':companyName });
						const i = cTag('i',{ 'class':`fa` });
						i.innerHTML = '&#xf015;';
					a.append(i,' ',companyName);
				h1.appendChild(a);
			}
		headerDiv.appendChild(h1);
	header.appendChild(headerDiv);
		headerDiv = cTag('div',{ 'class':`columnLG2 columnMD3 columnSM4 columnXS5`,'style':`position: relative; padding-top: 10px` });
		if(accountsInfo[1]>0){
			let searchedText = (new URLSearchParams(window.location.search)).get('s')||'';
			headerDiv.appendChild(cTag('input',{ 'maxlength':`50`,'autocomplete':`off`,'name':`s`,'id':`s`,'class':`dashboard_search`,'type':`text`,'value':searchedText,'placeholder':Translate('Search here'),'style':`padding-right: 30px` }));
				span = cTag('span',{ 'class':`headerSearchLabel`,'click':AJheaderSearch,'keydown':listenToEnterKey(AJheaderSearch),'for':`s`,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Enter an IMEI #, Customer Name or search Tickets with t#, Sales Invoices with s#, PO with p# or Orders with o#'),'data-tooltip-active':`true` });
				span.appendChild(cTag('i',{ 'class':`fa fa-search` }));
			headerDiv.appendChild(span);
		}
	header.appendChild(headerDiv);
		headerDiv = cTag('div',{ 'class':`columnLG3 columnMD4 columnSM4 columnXS7`,'style':`position: relative; padding-top: 5px` });
		if(accountsInfo[1]>0){
				const ul = cTag('ul',{ 'class':`flexEndRow nav navbar-nav` });
					li = cTag('li');
						let supportButton = cTag('a',{ 'class': "btn supportButton", 'href':`/Home/help`,'title':Translate('Support') });
						supportButton.innerHTML = Translate('Support')+'?';
					li.appendChild(supportButton);
				ul.appendChild(li);
				if(accountsInfo[4]){
						li = cTag('li');
							a = cTag('a',{ 'click':()=>showTimeClockForm(segment2),'id':`showTimeClockForm`,'href':`javascript:void(0);`,'title':Translate('Time Clock Manager') });
							a.appendChild(cTag('i',{ 'class':`fa fa-clock-o`,'style':`font-size: 24px` }));
						li.appendChild(a);
					ul.appendChild(li);
				}
					li = cTag('li',{ 'class':`dropdown minwidth120`,'id':`mydropdown` });
						a = cTag('a',{ 'href':`javascript:void(0);`,'class':`dropdown-toggle`,'data-toggle':`dropdown`,'role':`button`,'aria-haspopup':`true`,'aria-expanded':`true` });
						a.append(userFirstName,' ');
						a.appendChild(cTag('span',{ 'class':`caret` }));
					li.appendChild(a);
						let dropdownMenu = cTag('ul',{ 'class':`dropdown-menu` });
							let dropdownItem = cTag('li');
								let dropdownLink = cTag('a',{ 'href':`/Settings/myInfo`,'title':Translate('My Information') });
								dropdownLink.innerHTML = Translate('My Information');
							dropdownItem.appendChild(dropdownLink);
						dropdownMenu.appendChild(dropdownItem);
						let OUR_DOMAINNAME = window.location.hostname.split('.').slice(-2).join('.');
						if(['machouse.com.bd', 'machousel.com.bd'].includes(OUR_DOMAINNAME)){
							if((accountsInfo[2]>0 && accountsInfo[2]<10) || (accountsInfo[0]<10 && accountsInfo[3]==1 && accountsInfo[0]==accountsInfo[1]) || (accountsInfo[3]===1)){
									dropdownItem = cTag('li');
										dropdownLink = cTag('a',{ 'href':`/Account/payment_details`,'title':`Payment Details` });
										dropdownLink.innerHTML = Translate('Billing');
									dropdownItem.appendChild(dropdownLink);
								dropdownMenu.appendChild(dropdownItem);
							}
						}
							dropdownItem = cTag('li');
								dropdownLink = cTag('a',{ 'href':`/Account/login/Logout`,'id':`lnkLogOut`,'title':Translate('Logout') });
								dropdownLink.innerHTML = Translate('Logout');
							dropdownItem.appendChild(dropdownLink);
						dropdownMenu.appendChild(dropdownItem);                                                                             
					li.appendChild(dropdownMenu);
				ul.appendChild(li);
			headerDiv.appendChild(ul);
		}
	header.appendChild(headerDiv);
	document.getElementById('topheaderbar').appendChild(header);
	
	AJautoComplete_headerSearch();
	
	const ContactUsButtonOfTrialAccount = document.querySelector('.showHelpPopupBtn');
	if(ContactUsButtonOfTrialAccount) ContactUsButtonOfTrialAccount.addEventListener('click',showHelpPopup);
}

function createSideBar(){
	let modulesInfo = {
		'1': {label:Translate('Cash Register'),fileName:'POS',icon:'shopping-cart'},
		'2': {label:Translate('Repairs'),fileName:'Repairs',icon:'wrench'},
		'3': {label:Translate('Invoices'),fileName:'Invoices',icon:'folder-open'},
		'4': {label:Translate('Customers'),fileName:'Customers',icon:'address-book'},
		'5': {label:Translate('Products'),fileName:'Products',icon:'barcode'},
		'6': {label:Translate('Purchase Orders'),fileName:'Purchase_orders',icon:'plus-square'},
		'7': {label:Translate('Orders'),fileName:'Orders',icon:'pencil-square-o'},
		'8': {label:Translate('Livestocks Inventory'),fileName:'IMEI',icon:'tablet'},
		'9': {label:Translate('Stock Take'),fileName:'Stock_Take',icon:'folder-open'},
		'10': {label:Translate('Expenses'),fileName:'Expenses',icon:'money'},
		'11': {label:Translate('Suppliers'),fileName:'Suppliers',icon:'address-book'},
		'13': {label:Translate('Dashboard'),fileName:'Dashboard',icon:'line-chart'},
		'14': {label:Translate('End of Day'),fileName:'End_of_Day',icon:'money'},
		'15': {label:Translate('Appointment Calendar'),fileName:'Appointment_Calendar',icon:'calendar-plus-o'},
		'16': {label:Translate('Accounts Receivables'),fileName:'Accounts_Receivables',icon:'credit-card'},
		'17': {label:Translate('Time Clock Manager'),fileName:'Time_Clock',icon:'clock-o'},
		'18': {label:Translate('Website'),fileName:'Website',icon:'globe'},
		'19': {label:Translate('Commissions'),fileName:'Commissions',icon:'bullhorn'},
		'20': {label:Translate('Sales Reports'),fileName:'Sales_reports',icon:'pie-chart'},
		'21': {label:Translate('Repairs Reports'),fileName:'Repairs_reports',icon:'pie-chart'},
		'22': {label:Translate('Inventory Reports'),fileName:'Inventory_reports',icon:'pie-chart'},
		'23': {label:Translate('Activity Feed'),fileName:'Activity_Feed',icon:'exchange'},
		'24': {label:Translate('Getting Started'),fileName:'Getting_Started',icon:'cog'},
		'25': {label:Translate('Manage Data'),fileName:'Manage_Data',icon:'cog'},
		'26': {label:Translate('Setup'),fileName:'Settings',icon:'cog'},
		'27': {label:Translate('Integrations'),fileName:'Integrations',icon:'compress'},
		'28': {label:Translate('Accounts'),fileName:'Accounts',icon:'money'},
		'29': {label:Translate('Livestocks'),fileName:'Livestocks',icon:'barcode'},
	}
	if(multipleLocations>0) modulesInfo['12'] = {label:Translate('Inventory Transfer'),fileName:'Inventory_Transfer',icon:'truck'};
	

	const NumberOfPossibleNavItems = Math.floor((window.innerHeight-document.querySelector('header').getBoundingClientRect().height)/100)
	let counter = 0;
	let allowedModules = modulesInfo;
	const sidebarWrapper = document.getElementById('sidebarWrapper');
	sidebarWrapper.innerHTML = '';
	const sideNavBar = cTag('ul',{ 'class':`sidebar-nav settingslefthide`,'id':`sideNav` });

	if(!Array.isArray(allowed)) allowedModules = allowed;

	for (const key in allowedModules) {
		if(modulesInfo[key] && counter<NumberOfPossibleNavItems){
				const navItem = cTag('li');
				if(modulesInfo[key].fileName===segment1) navItem.setAttribute('class','active');
					const link = cTag('a',{ 'class':`firstclild sidebarlink`,'href':`/${modulesInfo[key].fileName}` });
					link.append(cTag('i',{ 'class':`fa fa-${modulesInfo[key].icon}` }),` ${modulesInfo[key].label}`);
				navItem.appendChild(link);
			sideNavBar.appendChild(navItem);
		    counter++;
		} 
	}
	sidebarWrapper.appendChild(sideNavBar);
}

function autoLogOut(){
	let intervalID;
	let dialogConfirm = cTag('div');  

	window.localStorage.setItem('idlePeriod_counter',0);
	window.localStorage.setItem('counter_last_updated_timestamp',Date.now());

	window.addEventListener('mousemove',()=>{		
			if(!dialogConfirm.classList.contains('timeoutdialog')){
				window.localStorage.setItem('idlePeriod_counter',0);
				window.localStorage.setItem('counter_last_updated_timestamp',Date.now());
			}
	});

	intervalID = setInterval(function() {
		let now = Date.now();
		let counter_last_updated_timestamp = window.localStorage.getItem('counter_last_updated_timestamp');
		let idlePeriod_counter = parseInt(window.localStorage.getItem('idlePeriod_counter'))+1;
				
		if(idlePeriod_counter>=(timelimit_autoLogOut-300)){
			if(dialogConfirm.classList.contains('timeoutdialog')){
				if(idlePeriod_counter >= timelimit_autoLogOut){
					clearInterval(intervalID);
					window.location = '/Account/login/Logout';
				}
				else if(document.getElementById("logOutTimeCount")){
					document.getElementById("logOutTimeCount").innerHTML = formatTime(parseInt(timelimit_autoLogOut-idlePeriod_counter))||'0 Seconds';
				}
			}
			else{
				dialogConfirm.classList.add('timeoutdialog');
				dialogConfirm.innerHTML = '';
					let pTag;
					let message = cTag('div');
						let divTop30 = cTag('div', {'style': "padding: 30px 0;"});
							pTag = cTag('p', {class: "font15normal", 'style': "padding-left: 20px; padding-right: 20px;", 'align': "center"});
							pTag.innerHTML = Translate('Your session is about to expire. You will be logged out in');
						divTop30.appendChild(pTag);
							pTag = cTag('p', {'align': "center", class: "font15normal", 'style': "font-weight: bold; color: #000;", id: "logOutTimeCount"});
							pTag.innerHTML = formatTime(parseInt(timelimit_autoLogOut-idlePeriod_counter));
						divTop30.appendChild(pTag);
					message.appendChild(divTop30);
				dialogConfirm.appendChild(message);

				popup_dialog(
					dialogConfirm,
					{
						title:Translate('Session Timeout Warning'),
						width:500,
						buttons: {
							_Continue_Working:{
								text:Translate('Continue Working'),
								class: 'btn saveButton btnmodel', 'style': "margin-left: 10px;", click: function(hide) {
									hide();
									dialogConfirm.classList.remove('timeoutdialog');
									dialogConfirm.innerHTML = '';
									window.localStorage.setItem('idlePeriod_counter',0);
									window.localStorage.setItem('counter_last_updated_timestamp',Date.now());
								},
							}
						}
					},
					false
				);
			}
		}
		else{
			if(dialogConfirm.classList.contains('timeoutdialog')){
				dialogConfirm.close();
				dialogConfirm.classList.remove('timeoutdialog');
			}				
		}
		if(now-counter_last_updated_timestamp>=1000){
			window.localStorage.setItem('idlePeriod_counter',idlePeriod_counter);
			window.localStorage.setItem('counter_last_updated_timestamp',now);	
		}

	}, 100);
}

function initiateHeartBeating(){
	if(window.accountsInfo && window.accountsInfo[1]>0){
		const HeartBeatInterval = 1000*60*5;//5minutes
		heartBeatingTimerID = setTimeout(() => {
			fetch('/Account/heartbeat').catch(()=>console.log('failed to beat heart'));
			initiateHeartBeating();
		}, HeartBeatInterval);
	}
}

function freezeVariable(variable){
	for (const key in variable) {
		if(typeof variable[key] === 'object') freezeVariable(variable[key])
	}
	Object.freeze(variable);
}

if(!(['prints','cprints', 'formsprints'].includes(segment2)||segment3==='label')){
	freezeVariable(allowed);
	if(!(segment1==='Account' && ['login', 'forgotpassword', 'setnewpassword', 'signup'].includes(segment2))){
		createHeader();
		createSideBar();
		initiateHeartBeating();
	}


	leftsideHide("settingsleftsidemenubar",'sidebar-nav');
	leftsideHide('settingsleftsidemenubar','sidebar-nav1');

	if(segment1 === 'Home')
	{
		document.getElementById('sideNav').classList.remove('settingslefthide');
	}

	if(document.getElementById('fanav') && segment1 !== 'Home'){
		lefsidemenubar();
	}
	if(segment1 !== 'Home'){
		window.addEventListener('resize', function(){
			if(document.getElementById('fanav')){
				let width,nav, sideBar, sidenav;
				width = window.innerWidth;
				nav = document.getElementById('fanav');
				sideBar = document.getElementById('sideBar');
				sidenav = document.getElementById('sideNav');

				if(width >767  ){
					nav.style.display = 'none';
					sideBar.style.display = '';
					if(!(sidenav.classList.contains('settingslefthide'))){
						sidenav.classList.add('settingslefthide');
					}
				}
				else{
					nav.style.display = '';
					sideBar.style.display = 'none';
					if((sideBar.style.display === 'none') && !(sidenav.classList.contains('settingslefthide'))){
						sideBar.style.display = '';
					}
					else if((sideBar.style.display !== 'none') && !(sidenav.classList.contains('settingslefthide'))){
						sideBar.style.display = 'none';
					}
				}
			}
		});
	}
	
	callShowInputOrSelect();
	multiSelectAction('mydropdown');	
	autoLogOut();
}

//=========Barcode-Labels==============
export async function barcodeLabel(data,labelFor,target,preview){
	if(!data.fontFamily){ //delete this after test	
		alert('font-family is missing');
		console.error('font-family is missing');
	} 
	let labelwidth = data.labelwidth;
	let labelheight = data.labelheight;
	let top_margin = data.top_margin;
	let right_margin = data.right_margin;
	let bottom_margin = data.bottom_margin;
	let left_margin = data.left_margin;
	let printableWidth = labelwidth-(left_margin+right_margin);
	let printableHeight = labelheight-(top_margin+bottom_margin);

	let fontsize = ({'Small':'11', 'Regular':'12', 'Large':'13'})[data.fontSize]||'12';

	let printCSS = `
		*{ box-sizing:border-box;margin:0; padding:0;line-height: 1 }
		body{ font-family:Arial, sans-serif, Helvetica;background:#fff;color:#000; }
		@media print{@page {size:${data.orientation};margin: 0px;}}
		#label{
			overflow:hidden;
			width:${labelwidth}px;height:${labelheight}px;
			font-size:${fontsize}px;text-align:center;
			padding:${top_margin}px ${right_margin}px ${bottom_margin}px ${left_margin}px;
			page-break-before: always;
		}		
		#label:first-child {
			page-break-before: avoid;
		}
		.contentArea{
			display:flex;flex-direction:column;justify-content:space-between;
			width:${printableWidth}px;
			height:${printableHeight}px;			
		}
		.textContent{font-family:${data.fontFamily}; min-height:${fontsize}px;overflow:hidden;white-space: pre-wrap;overflow-wrap: break-word; }
		.barcode{
			font-family: 'Libre Barcode';font-size: 35px;
			white-space:nowrap;overflow-wrap: normal;display:inline-block;
		}	
	`;

	if(data.customFieldsData){
		data.customFieldsData.forEach(item=>{
			let fieldName = item.field_name.replace(/\s+/g,'_');
			data[fieldName] = item.value;
		})
	}
	
    const font = new FontFace("Libre Barcode", "url(/assets/fonts/LibreBarcodeText.woff2)");
    target.document.fonts.add(font);
    await font.load();
		
	let LabelTemplate = data[labelFor].trim();
	let topLabelSection = '';
	let barcodeSection = '';
	let bottomLabelSection = '';

	if(/\{\{Barcode\}\}/.test(LabelTemplate)){
		let splitedTemplate = LabelTemplate.split('{{Barcode}}');
		topLabelSection = LabelTextContent(splitedTemplate[0]);
		barcodeSection = LabelBarcode(data.Barcode);
		bottomLabelSection = LabelTextContent(splitedTemplate[1]);
	}else{
		topLabelSection = LabelTextContent(LabelTemplate);
	}

	const head = target.document.head;
		const title = cTag('title');
		title.innerHTML = data.title;
	head.appendChild(title);
		const style = cTag('style');
		style.append(printCSS);
	head.appendChild(style);  

		let labelContainer = cTag('div',{id:`label`});
			let labelContent = cTag('div',{class:`contentArea`});
			labelContent.append(topLabelSection,barcodeSection,bottomLabelSection);
		labelContainer.appendChild(labelContent);
	target.document.body.appendChild(labelContainer);
	
	if(barcodeSection) resizeBarCode(labelContent.clientWidth,barcodeSection);

	//fiting all the content inside label having barcode intact
	let topLabelSectionHeight = topLabelSection?topLabelSection.clientHeight:0;
	let bottomLabelSectionHeight = bottomLabelSection?bottomLabelSection.clientHeight:0;

	if(topLabelSection) trimLabelText(topLabelSection,topLabelSectionHeight+1);
	if(bottomLabelSection) trimLabelText(bottomLabelSection,bottomLabelSectionHeight+1);

	if(preview){
		target.print();
		target.close();
	}

	function LabelTextContent(template){
		if(template){
			const TextContent = cTag('pre',{class:'textContent'});
				let content = template;
				const LabelTags = (template.match(/\{\{[^\}\}]+\}\}/g)||[]).map(tag=>tag.replace(/(\{\{)|(\}\})/g,''));	
				LabelTags.forEach(tag=>{ 
					const value = tag==='TicketNo'?`t${data[tag]}`:data[tag];
					content = content.replace(`{{${tag}}}`,value||(preview?tag:''));
				});
			TextContent.innerHTML = content.trim();
			return TextContent;
		}
		else return '';
	}
	function LabelBarcode(barcodeValue){
		if(barcodeValue){
			let barcode = cTag('span',{class:'barcode'});
			barcode.innerHTML = encodeToCode128(barcodeValue); 
			return barcode;
		}
		else return '';
	}
	
}

export function encodeToCode128(textToEncode){
    let textChars = textToEncode.split('');
    let startChar = String.fromCharCode(204);
    let endChar = String.fromCharCode(206);
    let checkSum = 104;
    textChars.forEach((char,indx)=>{
      let charValue = char.charCodeAt()-32; //according to code128 character-set-table code128-value is 32 lesser then ASCII-value
      checkSum += (charValue*(indx+1));
    });
    checkSum = (checkSum%103)+32;
    if(checkSum>126) checkSum += 68;
    let checkChar = String.fromCharCode(checkSum);
    return startChar+textToEncode+checkChar+endChar;
}
export function resizeBarCode(labelSize,barcode){
    let barcodeSize = barcode.scrollWidth;
    let barCodeFontSize = parseInt(getComputedStyle(barcode).fontSize);
    if(barCodeFontSize>0 && barcodeSize>labelSize){
		barCodeFontSize = barCodeFontSize-1;
        barcode.style.fontSize = barCodeFontSize+'px';
        return resizeBarCode(labelSize,barcode);
    }
	else return barCodeFontSize;
}
export function trimLabelText(node,nodeHeight){   
	if(nodeHeight<0) return;
	     
	let trimmed = trim();

	if(trimmed){
		let trimmedText = node.innerText;
		//add elipsis
		if(node.innerText.match(/\n$/)) node.innerText = node.innerText.replace(/\n$/,'...');
		else node.innerText = node.innerText + '...';
		//trim 3 more character if there's not enough room to fit elipsis
		if(contentOverflowed()) node.innerText = trimmedText.slice(0,-5)+ '...';
	}

	function trim(){
		let trimmed = false;      
		if(contentOverflowed()){
			trimmed = true;
			node.innerText = node.innerText.slice(0,-1);
			trim();
		}
		return trimmed;
	}
	function contentOverflowed(){
		let contentHeight = node.scrollHeight;
		if(contentHeight>nodeHeight) return true;
		else return false;
	}
}

//==========Round-issue and Long-precision fix================
export function calculate(operation,Number1,Number2,roundScale){
	//throwing error passing invalid params
	if(!['add','sub','mul','div'].includes(operation)) throw new Error(`Invalid operator keyword: ${operation}`)
 	else if(Number1===undefined || Number2===undefined) throw new Error(`Missing operand (First-Operand:${Number1} Second-Operand:${Number2})`)
 	else if(roundScale===undefined) throw new Error(`Invalid Round Scale: ${roundScale}`)


    Number1 = RNumber(Number1);
    Number2 = RNumber(Number2);
    let largestDenominator = Math.max(Number1.Denominator,Number2.Denominator);
    let results;
    if(operation==='mul'){
        results = (Number1.Numerator * Number2.Numerator)/(Number1.Denominator * Number2.Denominator);
    }
    else if(operation==='div'){
        results = (Number1.Numerator * (largestDenominator/Number1.Denominator)) / (Number2.Numerator * (largestDenominator/Number2.Denominator));
    }
    else{
        Number1 = Number1.Numerator*(largestDenominator/Number1.Denominator);
        Number2 = Number2.Numerator*(largestDenominator/Number2.Denominator);
        if(operation==='add') results = (Number1 + Number2)/largestDenominator;
        if(operation==='sub') results = (Number1 - Number2)/largestDenominator;
    }

	if(roundScale===false) return results;
    return round(results,roundScale);
   
    function RNumber(number){      
        let [integer, fraction=''] = number.toString().split('.');
        return {
            Numerator:Number(integer+fraction),
            Denominator: Math.pow(10,fraction.length)
        }    
    }
}
export function round(number,scale){
	if(scale===undefined) throw new Error(`Invalid Round Scale: ${scale}`)

	let [integer,fraction] = number.toString().split('.');
	const numberType = integer.search('-')===0?'-':'+';
	integer = Math.abs(integer);
	if(fraction && fraction.length>scale){
	  fraction = fraction.slice(0,scale+1);
	  let fraction_controllDigit = Number(fraction[scale]);
	  fraction = fraction.slice(0,scale);
	  if(fraction_controllDigit>=5){
		fraction = roundUp(fraction);
		if(fraction>=Math.pow(10,scale)){
		  integer++;
		  fraction = 0;
		}
	  }
	}
	integer = numberType==='+'?`+${integer}`:`-${integer}`;
	fraction = fraction?`.${fraction}`:``;
	return Number(`${integer}${fraction}`);

	function roundUp(fraction){
	  let digitsInfraction = fraction.split('').map(digit=>Number(digit));
	  const LastDigitIndex = digitsInfraction.length-1;
	  digitsInfraction[LastDigitIndex] += 1;//rounding up
	  if(digitsInfraction[LastDigitIndex]>9){
		const slicedDecimalPart = digitsInfraction.slice(0,LastDigitIndex).join('');
		if(LastDigitIndex===0) return '10';
		else return roundUp(slicedDecimalPart)+'0';
	  }
	  else return digitsInfraction.join('');
	}
} 
