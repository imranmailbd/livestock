let pathArray = window.location.pathname.split('/');
let segment1 = 'index';
let segment2 = '';
let segment3 = 1;

if(pathArray.length>1 && pathArray[1] !==''){
	segment1 = pathArray[1].replace('-', '_');
    if(pathArray.length>2){
        segment2 = pathArray[2];
        if(pathArray.length>3){segment3 = pathArray[3];}
    }
}

let Limits;
Limits = ['9','15','30','60','102','501'];

document.cookie = "jsessionid=oIZEL75SLnw; HttpOnly; Secure; SameSite=Strict";

let fullDomain = window.location.hostname;
let domainName = window.location.hostname.split('.').slice(-2).join('.');
let prevSegment1, prevSegment2, prevSegment3;
prevSegment1 = prevSegment2 = prevSegment3 ='';

if (sessionStorage.getItem("prevSegments") !== null) {
	let prevSegments = JSON.parse(sessionStorage.getItem("prevSegments"));
    prevSegment1 = prevSegments.segment1;
    prevSegment2 = prevSegments.segment2;
    prevSegment3 = prevSegments.segment3;
	if(prevSegment1 !== segment1){
		sessionStorage.setItem('list_filters', JSON.stringify({}));
	}
}

sessionStorage.setItem('prevSegments', JSON.stringify({segment1, segment2, segment3}));

//===================error-handler============================
window.addEventListener('error',function(errorEvent){
	if(['cellstorelocal.co', 'machousel.com.bd'].includes(domainName)){
		alert(errorEvent.error.message);
	}
	const {message,stack} = errorEvent.error;
	let errMessage = stack;
	if(stack.search(message)<0) errMessage = `${message} at ${stack}`;
	const jsonData = {name: 'Script Error', message: '1: '+errMessage+` ---UserAgent: ${navigator.userAgent}`, url: document.location.href};
	let options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
    fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));	
})
window.onunhandledrejection = function(errorEvent) {
	if(['cellstorelocal.co', 'machousel.com.bd'].includes(domainName)){
		alert(errorEvent.reason.message);
	}	
	const {message,stack} = errorEvent.reason;
	let errMessage = stack;
	if(stack.search(message)<0) errMessage = `${message} at ${stack}`;
    const jsonData = {name: 'Script Error', message: '2: '+errMessage+` ---UserAgent: ${navigator.userAgent}`, url: document.location.href};
    let options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
    fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));	
};


window.addEventListener('online',()=>sendCachedError('cachedError'));
function sendCachedError(storageID){
    if(window.localStorage.getItem(storageID)){
    	let cachedError = JSON.parse(window.localStorage.getItem(storageID));
    	if(cachedError.length){
        	cachedError.forEach(error=>{
        		let options = {method: "POST", body:JSON.stringify(error), headers:{'Content-Type':'application/json'}};
        		if(window.navigator.onLine) fetch('/Home/handleErr/', options);
        	})
        	window.localStorage.removeItem(storageID);
    	}
    }
}

function cacheError(error,storageID){
	let cachedError = JSON.parse(window.localStorage.getItem(storageID));
	if(cachedError) window.localStorage.setItem(storageID,JSON.stringify([...cachedError,error]));
	else window.localStorage.setItem(storageID,JSON.stringify([error]));
}

function checkForSuccessfulRequest(response){
	let serverIsOffline = (400<=response.status && response.status<600)?true:false;
	//if server is online and there is any cachedServerOfflineError, then send it first
	if(!serverIsOffline) sendCachedError('cachedServerOfflineError');

	return new Promise((resolve,reject)=>{
        if(response.ok) resolve(response);
        else{
			let err = new Error(response.status+' '+response.statusText);
			if(serverIsOffline) err.serverOffline = true; // setting a flag that server is offline
            else err.serverIssue = true;
            reject(err);
        } 
    })
}

function handleErr(err,api_endpoint) {
	if(err.serverIssue){
		const jsonData = {name: 'Server Issue', message: err.stack+' for API: '+api_endpoint, url: document.location.href};
		const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
		fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));
	}
	else if(err.serverOffline){					 
		const jsonData = {name: 'Server is Unavailable/Offline', message: ' at '+Date()+' when retrieving data from API: '+api_endpoint, url: document.location.href}; 			
		cacheError(jsonData,'cachedServerOfflineError');// if server is offline then just cache the error  
	}
	else{
		if(err.stack.search('TypeError: Failed to fetch')===-1){
			const jsonData = {name: 'JSON Issue', message: err.stack+' for API: '+api_endpoint, url: document.location.href};
			const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
			fetch('/Home/handleErr/', options).catch(()=>cacheError(jsonData,'cachedError'));
		}
	}
	return {code: 400};

}

function connection_dialog(callbackfunction){
	let connection_retries, d, expires;
    let args = Array.prototype.slice.call(arguments, 1);
	if(document.cookie.indexOf("connection_retries=") >= 0) {
		connection_retries = getCookie('connection_retries');
   		if (connection_retries >= 3 ) {
			let dialogConfirm = cTag('div');
				let pTag = cTag('p',{'style':'text-align: left;'});
                if(!window.navigator.onLine) pTag.innerHTML = Translate('Internet connection problem. Retry again.');
                else pTag.innerHTML = 'There is some server issues. Retry again';
			dialogConfirm.appendChild(pTag);

			document.cookie = "failcall="+window.location.href+"; path=/";
			
			connection_retries = 0;
			d = new Date();
			d.setTime(d.getTime() + 60000);
			expires = "expires="+ d.toUTCString();
			document.cookie = "connection_retries="+connection_retries+";" + expires + ";";
			
		}
   		else{
          	setTimeout(function() {
				connection_retries++;
				d = new Date();
				d.setTime(d.getTime() + 60000);
				expires = "expires="+ d.toUTCString();
				document.cookie = "connection_retries="+connection_retries+";" + expires + ";";
				
				if(args.length>0){
					callbackfunction.apply(this, args);
				}
				else{
					callbackfunction();
				}
			}, 4000);
		}
   	}
	else{
		setTimeout(function() {
			connection_retries = 1;
			d = new Date();
			d.setTime(d.getTime() + 60000);
			expires = "expires="+ d.toUTCString();
			document.cookie = "connection_retries="+connection_retries+";" + expires + ";";
			
			if(args.length>0){
				callbackfunction.apply(this, args);
			}
			else{
				callbackfunction();
			}
		}, 4000);
	}
}
//===================error-handler============================

function checkAndSetLimit(){
	let rowHeight = 31;
    if(segment1==='Repairs' || segment2==='invoicesReport'){rowHeight = 51;}
	let limit = parseInt(document.getElementById("limit").value);
	if(isNaN(limit) || limit==0){
		limit = 15;
		const displayHeight = window.innerHeight;
		
		const topheaderbar = parseFloat(document.querySelector("#topheaderbar").offsetHeight);
		if(isNaN(topheaderbar) || topheaderbar==0){topheaderbar = 60;}

		let outerTableHeight = 0;
		const outerTableHeightObj = document.querySelectorAll(".outerTableHeight");
		if(outerTableHeightObj.length>0){
			outerTableHeightObj.forEach(oneHeightObj=>{
				let oneHeight = parseFloat(oneHeightObj.offsetHeight);
				if(isNaN(oneHeight) || oneHeight==0){oneHeight = 45;}
				outerTableHeight += oneHeight;
			})
		}

		const bodyHeight = displayHeight-topheaderbar-outerTableHeight-50;
		if(bodyHeight<=0 || bodyHeight<=rowHeight){limit = 1;}
		else{
			limit = Math.floor(bodyHeight/rowHeight);
		}
	}
	return limit;
}

function Translate(index){
	if(loadLangFile != 'English'){
		if(langModifiedData !==undefined && langModifiedData[index] !==undefined){
			return langModifiedData[index];
		}
		else if(languageData[index] !==undefined){
			return languageData[index];
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
			
			const jsonData = {name: 'JS Translate issue: '+index, message: message+', Language: '+loadLangFile, url: document.location.href};
			const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
			fetch('/Home/handleErr/', options);
		}
	}
	return index;
}

function tooltip(node){
    if(node.getAttribute('data-tooltip-active')) return;

    let styles = {
        tooltip:{
            'position':'absolute',
            'background':'black',
            'color':'white',
            'padding':'3px 8px',
            'font-size':'12px',
            'text-align':'center',
            'max-width':'200px',
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
        tooltip = cTag('div');
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

function cTag(tagName, attributes){
    let node = document.createElement(tagName);
    if(attributes){
        for(const [key, value] of Object.entries(attributes)) {
            if(typeof value === 'function') node.addEventListener(key,value);
			else node.setAttribute(key, value);
        }
    }
    return node;
}

function storeSessionData(currentData){
	let previousData = {};	
	if(prevSegment1 === segment1){
		previousData = JSON.parse(sessionStorage.getItem("list_filters"));
	}	
	const newData = {...previousData, ...currentData};
	sessionStorage.setItem('list_filters', JSON.stringify(newData));
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

//=========Home===========//
function index(){
	let service_box,sm_container;
	const viewPageInfo = document.querySelector('#viewPageInfo');
	viewPageInfo.innerHTML = '';	
		let bannerSection = cTag('section',{ 'class':'banner','style':"width:100%;height:auto;padding-bottom: 131px;padding-top: 143px;text-transform: uppercase;" });
			let containerDiv = cTag('div',{ 'class':'container' });
				let bannerRow = cTag('div',{ 'class':'flexSpaBetRow' });
					const bannerColumn = cTag('div',{ 'class':'columnSM7' });
						let bannerContainer = cTag('div',{ 'class':'sm_container' });
						bannerContainer.appendChild(cTag('h2',{ 'class':'mst_one' }));
						bannerContainer.appendChild(cTag('h3',{ 'class':'mst_two_three' }));
						bannerContainer.appendChild(cTag('h2',{ 'class':'mst_four' }));
					bannerColumn.appendChild(bannerContainer)
				bannerRow.appendChild(bannerColumn);
					let pictureColumn = cTag('div',{ 'id':"home_page_body_picture", 'class':"columnSM5", 'style': "text-align: center;" });
					pictureColumn.appendChild(cTag('img',{ 'style':'max-width:100%;','id':'onePicture' }));
				bannerRow.appendChild(pictureColumn);
			containerDiv.appendChild(bannerRow);
		bannerSection.appendChild(containerDiv);
	viewPageInfo.appendChild(bannerSection)

		let serviceSection = cTag('section',{ 'style':"width:100%; height:auto; padding: 103px 0;",'class':'services' });
			let serviceContainer = cTag('div',{ 'class':'container' });
				let serviceRow = cTag('div',{ 'class':'flexSpaBetRow' });
					let serviceColumn = cTag('div',{ 'class':'columnSM4' });
						service_box = cTag('div',{ 'class':'service_box' });
							sm_container = cTag('div',{ 'class':'sm_container' });
								let serviceHeader = cTag('h2');
								serviceHeader.innerHTML = Translate('CELLULAR<br>Services');
							sm_container.appendChild(serviceHeader)
								let ulList = cTag('ul',{ 'class':'list' });								
							sm_container.appendChild(ulList)
						service_box.appendChild(sm_container)
					serviceColumn.appendChild(service_box)
				serviceRow.appendChild(serviceColumn);
					let serviceBoxCol = cTag('div',{ 'class':'columnSM4' });
						service_box = cTag('div',{ 'class':'service_box' });
							sm_container = cTag('div',{ 'class':'sm_container' });
								let hourHeader = cTag('h2');
								hourHeader.innerHTML = Translate('Business<br>HOURS');
							sm_container.appendChild(hourHeader);
								let ulHour = cTag('ul',{ 'id':'business_hours_list','class':'list' });
								for(let i=0; i<7; i++){
									ulHour.appendChild(cTag('li'));
								}
							sm_container.appendChild(ulHour);
						service_box.appendChild(sm_container);
					serviceBoxCol.appendChild(service_box);
				serviceRow.appendChild(serviceBoxCol);
					let addressColumn = cTag('div',{ 'class':'columnSM4' });
						service_box = cTag('div',{ 'class':'service_box' });
							sm_container = cTag('div',{ 'class':'sm_container' });
								let addressHeader = cTag('h2');
								addressHeader.innerHTML = Translate('BUSINESS<br>ADDRESS');
							sm_container.appendChild(addressHeader)
								let ulAddress = cTag('ul',{ 'class':'list' });
								ulAddress.appendChild(cTag('li',{ 'class':'business_address' }));
							sm_container.appendChild(ulAddress)
							sm_container.appendChild(cTag('iframe',{id:'mapAddress', 'width':'100%','height':'250','frameborder':0,'style':'border: 0','scrolling':'no','marginheight':0,'marginwidth':0,'src':"http://maps.google.it/maps?q=20-Whiteleas-Ave-Toronto-ON-Canada-m1b1w7&output=embed" }))
						service_box.appendChild(sm_container)
					addressColumn.appendChild(service_box)
				serviceRow.appendChild(addressColumn);
			serviceContainer.appendChild(serviceRow);
		serviceSection.appendChild(serviceContainer);
	viewPageInfo.appendChild(serviceSection)

		let service2Section = cTag('section',{ 'class':'services2','style':"width:100%; height:auto;padding-bottom:95px;" });
			let service2Container = cTag('div',{ 'class':'container' });
				let service2Row = cTag('div',{ 'class':'flexSpaBetRow' });
				[
					'bd_one','bd_two','bd_three'
				].forEach(id=>{
						let service2Column = cTag('div',{ 'class':'columnSM4' });
							service_box = cTag('div',{ 'class':'service_box' });
								sm_container = cTag('div',{ 'class':'sm_container' });
								sm_container.appendChild(cTag('div',{ 'id':id+'_icon' }))
								sm_container.appendChild(cTag('h3',{ 'id':id+'_headline' }));
								sm_container.appendChild(cTag('p',{ 'id':id+'_details' }));
							service_box.appendChild(sm_container);
						service2Column.appendChild(service_box);
					service2Row.appendChild(service2Column);
				})
			service2Container.appendChild(service2Row);
		service2Section.appendChild(service2Container);
	viewPageInfo.appendChild(service2Section)

	AJ_index_MoreInfo();
}

async function AJ_index_MoreInfo(){
	const jsonData = {};
    const url = '/Instancehome/AJ_index_MoreInfo';  
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		// section 1
		const section1 = document.querySelector('.banner');

		section1.style.background = data.bg_color1;
		section1.style.color = data.color1;
		section1.style.fontFamily = data.font_family1;

		document.querySelector(".mst_one").innerHTML = data.mst_one;
		document.querySelector(".mst_two_three").innerHTML = (data.mst_two)+'<br>'+(data.mst_three);
		document.querySelector(".mst_four").innerHTML = data.mst_four;

		const img = document.getElementById('onePicture');
		img.src = data.onePicture;
		img.alt = data.alt;

		// section 2
		const section2 = document.querySelector('.services');

		section2.style.background = data.bg_color2;
		section2.style.color = data.color2;
		section2.style.fontFamily = data.font_family2;

		const cellular_services_list = document.querySelector('ul.list');
		for (let index = 1; index < 8; index++) {
			const cellular_service_name = `cellular_services${index}`;
			const cellular_service_value = data[cellular_service_name];
			if(cellular_service_value) {
				const list = cTag('li',{ 'class':cellular_service_name});
				list.innerText = cellular_service_value;
				cellular_services_list.appendChild(list);
			}
		}

		const list = document.getElementById('business_hours_list').childNodes;
		list[0].innerHTML = data.mon_from === 'Closed'? `» ${Translate('Monday Closed')}`: `» ${Translate('Monday')} ${data.mon_from} to ${data.mon_to}`;
		list[1].innerHTML = data.tue_from === 'Closed'? `» ${Translate('Tuesday Closed')}`: `» ${Translate('Tuesday')} ${data.tue_from} to ${data.tue_to}`;
		list[2].innerHTML = data.wed_from === 'Closed'? `» ${Translate('Wednesday Closed')}`: `» ${Translate('Wednesday')} ${data.wed_from} to ${data.wed_to}`;
		list[3].innerHTML = data.thu_from === 'Closed'? `» ${Translate('Thursday Closed')}`: `» ${Translate('Thursday')} ${data.thu_from} to ${data.thu_to}`;
		list[4].innerHTML = data.fri_from === 'Closed'? `» ${Translate('Friday Closed')}`: `» ${Translate('Friday')} ${data.fri_from} to ${data.fri_to}`;
		list[5].innerHTML = data.sat_from === 'Closed'? `» ${Translate('Saturday Closed')}`: `» ${Translate('Saturday')} ${data.sat_from} to ${data.sat_to}`;
		list[6].innerHTML = data.sun_from === 'Closed'? `» ${Translate('Sunday Closed')}`: `» ${Translate('Sunday')} ${data.sun_from} to ${data.sun_to}`;

		let mapAddress;
		document.querySelector('.business_address').innerHTML = data.business_address;
		mapAddress = data.business_address.replace(' ', '-');
		mapAddress = mapAddress.replace(',', '-');
		document.querySelector('#mapAddress').src = 'http://maps.google.it/maps?q='+mapAddress+'&output=embed';

		// section 3
		const section3 = document.querySelector('.services2');			

		section3.style.background = data.bg_color3;
		section3.style.color = data.color3;
		section3.style.fontFamily = data.font_family3;

		document.getElementById('bd_one_details').innerHTML = data.bd_one_details;
		document.getElementById('bd_one_headline').innerHTML = `${data.bd_one_headline} <br> ${data.bd_one_subheadline}`;
		document.getElementById('bd_one_icon').setAttribute('class',`fa fa-${data.bd_one_icon||'refresh'}`);

		document.getElementById('bd_two_details').innerHTML = data.bd_two_details;
		document.getElementById('bd_two_headline').innerHTML = `${data.bd_two_headline} <br> ${data.bd_two_subheadline}`;
		document.getElementById('bd_two_icon').setAttribute('class',`fa fa-${data.bd_two_icon||'cogs'}`);

		document.getElementById('bd_three_details').innerHTML = data.bd_three_details;
		document.getElementById('bd_three_headline').innerHTML = `${data.bd_three_headline} <br> ${data.bd_three_subheadline}`;
		document.getElementById('bd_three_icon').setAttribute('class',`fa fa-${data.bd_three_icon||'phone'}`);
	}
}

function extractRootDomain(url,value) {
	let domain = url;
	const val = value;
	const splitArr = domain.split('.');
	const arrLen = splitArr.length;
	if (arrLen > 2 && val === 1) {
		domain =  splitArr[0];
	}
	else if (arrLen > 2 && val === 0) {
		domain = splitArr[arrLen - 2]+ '.' + splitArr[arrLen - 1];
	}
	return domain;
}

//=========Contact_us===========//
function Contact_Us(){
	const viewPageInfo = document.querySelector('#viewPageInfo');
	viewPageInfo.innerHTML = '';
		const contactUsSection = cTag('section',{ 'style':"width: 100%; padding: 20px 0;" });
			const contactUsContainer = cTag('div',{ 'class':"container" });
				const contactUsRow = cTag('div',{ 'class':"flexSpaBetRow" });
					const contactUsDiv = cTag('div',{ 'class':"columnSM8"});
					contactUsDiv.appendChild(cTag('div',{ 'id':"CSAPIDiv",'class':"flexCenterRow"}));
				contactUsRow.appendChild(contactUsDiv);
					const contactUsColumn = cTag('div',{ 'class':"columnSM4", 'style': "padding-top: 20px;" });
						const contactUsBox = cTag('div',{ 'class':"service_box" });
							const smContainer = cTag('div',{ 'class':"sm_container", 'style': "padding-top: 15px;" });
								const businessHourTitle = cTag('h2');
								businessHourTitle.innerHTML = Translate('Business<br>HOURS');
							smContainer.appendChild(businessHourTitle);
								const businessUl = cTag('ul',{ 'class':"list", 'style': "margin-bottom: 20px;", 'id':'business_hours_list' });
								for(let i=0; i<7; i++){
									businessUl.appendChild(cTag('li'));
								}
							smContainer.appendChild(businessUl);
						contactUsBox.appendChild(smContainer);
					contactUsColumn.appendChild(contactUsBox);
						const serviceBox = cTag('div',{ 'class':"service_box" });
							const serviceBoxDiv = cTag('div',{ 'class':"sm_container" });
								const businessAddressTitle = cTag('h2');
								businessAddressTitle.innerHTML = Translate('BUSINESS<br>ADDRESS');
							serviceBoxDiv.appendChild(businessAddressTitle);
								const statusDiv = cTag('div',{ 'class':"columnSM12", 'style': "padding-top: 10px;" });
									const pTag = cTag('p',{ 'style':"line-height: 25px;" });
									pTag.appendChild(cTag('i',{ 'class':"fa fa-map-marker", 'style': "float: left; padding: 2px 10px; font-size: 18px; line-height: 25px;", 'aria-hidden':"true" }));
									pTag.appendChild(cTag('span',{ 'id':"business_address" }));
								statusDiv.appendChild(pTag);
								statusDiv.appendChild(cTag('div',{ 'style': 'clear: both;' }));
									const pTag1 = cTag('p',{ 'style':"line-height: 25px;" });
									pTag1.appendChild(cTag('i',{ 'class':"fa fa-mobile", 'style': "float: left; padding: 2px 10px; font-size: 18px; line-height: 25px;", 'aria-hidden':"true" }));
									pTag1.appendChild(cTag('a',{ 'id':"company_phone_no" }));
								statusDiv.appendChild(pTag1);
								statusDiv.appendChild(cTag('div',{ 'style': 'clear: both;' }));
									const pTag2 = cTag('p',{ 'style':"line-height: 25px;" });
									pTag2.appendChild(cTag('i',{ 'class':"fa fa-envelope-o", 'style': "float: left; padding: 2px 10px; font-size: 18px; line-height: 25px;", 'aria-hidden':"true" }));
									pTag2.appendChild(cTag('a',{ 'id':"customer_service_email" }));
								statusDiv.appendChild(pTag2);
							serviceBoxDiv.appendChild(statusDiv);
						serviceBox.appendChild(serviceBoxDiv);
					contactUsColumn.appendChild(serviceBox);
				contactUsRow.appendChild(contactUsColumn);
			contactUsContainer.appendChild(contactUsRow);
		contactUsSection.appendChild(contactUsContainer);
	viewPageInfo.appendChild(contactUsSection);
	viewPageInfo.appendChild(cTag('iframe',{ 'width':'100%','height':'250','frameborder':0,'style':'border: 0','scrolling':'no','marginheight':0,'marginwidth':0,'src':"http://maps.google.it/maps?q=4/21-Solimullah-Road,-Mohammadpur,-Dhaka---1207,-United-States&output=embed" }));

	AJ_Contact_Us_MoreInfo();
}

async function AJ_Contact_Us_MoreInfo(){
	const jsonData = {};
    const url = '/Instancehome/AJ_Contact_Us_MoreInfo';  
	fetchData(afterFetch,url,jsonData);

	function afterFetch(data){
		const list = document.getElementById('business_hours_list').childNodes;
		list[0].innerHTML = data.mon_from === 'Closed'? `» ${Translate('Monday Closed')}`: `» ${Translate('Monday')} ${data.mon_from} to ${data.mon_to}`;
		list[1].innerHTML = data.tue_from === 'Closed'? `» ${Translate('Tuesday Closed')}`: `» ${Translate('Tuesday')} ${data.tue_from} to ${data.tue_to}`;
		list[2].innerHTML = data.wed_from === 'Closed'? `» ${Translate('Wednesday Closed')}`: `» ${Translate('Wednesday')} ${data.wed_from} to ${data.wed_to}`;
		list[3].innerHTML = data.thu_from === 'Closed'? `» ${Translate('Thursday Closed')}`: `» ${Translate('Thursday')} ${data.thu_from} to ${data.thu_to}`;
		list[4].innerHTML = data.fri_from === 'Closed'? `» ${Translate('Friday Closed')}`: `» ${Translate('Friday')} ${data.fri_from} to ${data.fri_to}`;
		list[5].innerHTML = data.sat_from === 'Closed'? `» ${Translate('Saturday Closed')}`: `» ${Translate('Saturday')} ${data.sat_from} to ${data.sat_to}`;
		list[6].innerHTML = data.sun_from === 'Closed'? `» ${Translate('Sunday Closed')}`: `» ${Translate('Sunday')} ${data.sun_from} to ${data.sun_to}`;

		document.querySelector('#business_address').innerHTML = data.business_address;	
		
		const company_phone_no = document.querySelector('#company_phone_no')
		company_phone_no.setAttribute('href',`tel:${data.company_phone_no}`);
		company_phone_no.setAttribute('title',data.company_phone_no);
		company_phone_no.innerHTML = data.company_phone_no;	

		const customer_service_email = document.querySelector('#customer_service_email')
		customer_service_email.setAttribute('href',`mailto:${data.customer_service_email}`);
		customer_service_email.setAttribute('title',data.customer_service_email);
		customer_service_email.innerHTML = data.customer_service_email;	

		if(document.getElementById('CSAPIDiv')){
			const URL = window.location.hostname;
			const OUR_DOMAINNAME = extractRootDomain(URL,0);
			const subdomain = extractRootDomain(URL,1);
			const encodeSubdomain = document.getElementById("encodeSubdomain").value;
			const scriptTag = cTag('script', {'id':"CSAPI", 'class':"contact_us"});
			scriptTag.defer = true;
			scriptTag.src = "http://"+subdomain+"."+OUR_DOMAINNAME+"/assets/widget.js?"+encodeSubdomain;
			scriptTag.async = false;
			document.getElementById('CSAPIDiv').appendChild(scriptTag);
		}
	}
}


//=======Common Function=======//

async function fetchData(afterFetch,url,payload,contentType='JSON',numberOfFetchRequest=1){
	if(numberOfFetchRequest) activeLoader();

	let options;
    if(contentType==='formData') options = {method: "POST", body:new FormData(payload)};
	else options = {method: "POST", body:JSON.stringify(payload), headers:{'Content-Type':'application/json'}};

	let data = await fetch(url,options).then(checkForSuccessfulRequest).then(response=>response.json()).catch((err)=>handleErr(err,url));
	
    if (data.offline){
        if(numberOfFetchRequest===0){
            showTopMessage('error_msg',Translate('Internet connection problem. Retry again.'));
        }
        if(numberOfFetchRequest<5){
            setTimeout(()=>{fetchData(afterFetch,url,payload,contentType,++numberOfFetchRequest)}, 2000);
        }
        else{
            let message = cTag('p',{'class':'txtleft'});
            message.innerHTML = Translate('Internet connection problem. Retry again.');
			popup_dialog(
				message,
				{
					title:Translate('Could not connect to internet'),
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
			afterFetch(data);
            hideLoader();
		}
	}
}

function activeLoader(){
    if(document.getElementById('loaderOverlay')) return;
	let disScreen = cTag('div',{ 'class': 'disScreen',id:'loaderOverlay','style':'z-index:99999' });
	disScreen.appendChild(cTag('img',{ 'src': '/assets/images/ajax-loader.gif' }));
    document.body.appendChild(disScreen);
}

function hideLoader(){
    if(document.getElementById('loaderOverlay')) document.getElementById('loaderOverlay').remove();
}


document.addEventListener('scroll', function(e) {
	const Powered = document.querySelector(".footer_scroll");
	const add_scroll = document.querySelector(".add_scroll");
	const scroll_val = window.scrollY;

	window.requestAnimationFrame(function() {
		if (scroll_val >= 150 && !add_scroll){
			Powered.classList.add('add_scroll');
		}
		else if (scroll_val < 150 && add_scroll){
			Powered.classList.remove('add_scroll');
		}
	});
});

function activeNavbarToggle(){
	let toggleBtn = document.querySelector('.navbar-toggle');
	let navMenu = document.querySelector('.navbar-nav');
	toggleBtn.addEventListener('click',function(){
		if(toggleBtn.getAttribute('aria-expanded')==='true'){
			navMenu.style.height = navMenu.clientHeight+'px';
			navMenu.style.overflow = 'hidden';
			setTimeout(()=>{
				navMenu.style.height = '0px';
			},50)
			toggleBtn.setAttribute('aria-expanded',false);
		}
		else{
			navMenu.style.height = '280px';
			setTimeout(()=>{
				navMenu.style.height = 'auto';
				navMenu.style.overflow = 'visible';
			},500)
			toggleBtn.setAttribute('aria-expanded',true);			
		}
	})
}

function activeRepairsDropdownMenu(){
	let menu = document.querySelector('[data-toggle="dropdown"][class="dropdown-toggle"] ~ [class~="dropdown-menu"]');
	if(menu){
		menu.style.right = '10px';
		menu.style.left = menu.style.top = 'auto';
	}

	let menuBtn = document.querySelector('[class="dropdown-toggle"][data-toggle="dropdown"]');
	if(menuBtn){
		menuBtn.addEventListener('click',function(){
			if(this.getAttribute('aria-expanded')==='true'){
				if(menu.style.display !== 'none'){
					menu.style.display = 'none';
				}
				this.setAttribute('aria-expanded',false);
			}
			else{
				if(menu.style.display === 'none'){
					menu.style.display = '';
				}
				this.setAttribute('aria-expanded',true);
			}
		});
		document.addEventListener('click',function(event){
			if((event.target.parentNode !== menuBtn) && (event.target !== menuBtn) && menuBtn.getAttribute('aria-expanded')==='true'){
				if(menu.style.display !== 'none'){
					menu.style.display = 'none';
				}
				menuBtn.setAttribute('aria-expanded',false);
			}
		})	
	}
}

document.addEventListener('DOMContentLoaded', async()=>{
	let layoutFunctions = {index, Contact_Us};
	let functionName = segment1;
	if(segment2) functionName = segment1+'_'+ segment2;
	if(!['Appointment','Customer', 'Services', 'Product', 'CellPhones', 'Check_Repair_Status','Quote'].includes(segment1)) layoutFunctions[functionName]();
	
	document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
	
	const footer_scroll = Math.floor(document.querySelector(".footer_scroll").clientHeight);
	const displayHeight = Math.floor(window.innerHeight);
	let minHeight = Math.floor(displayHeight-footer_scroll);
	document.querySelector("#wrapper").style.minHeight =  minHeight+'px';
	
	activeNavbarToggle();
	activeRepairsDropdownMenu();
});