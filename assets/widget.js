/* PLEASE DO NOT COPY AND PASTE THIS CODE. */

let WidgetForm = document.createElement('div');
WidgetForm.setAttribute('id', 'loadWidgetForm');

const Widgets = {
	AJload_contact_us_WidgetForm, AJload_customer_WidgetForm,
	AJload_appointment_WidgetForm, AJload_repair_status_WidgetForm, 
	AJload_quote_WidgetForm
};
const swVersion = 'v3';
let language = "English";
let CSAPI = document.getElementById("CSAPI");
let jsFile = CSAPI.getAttribute('src');
let module = CSAPI.getAttribute('class');
let fullURLSplit = jsFile.split("/assets");
let fullURL = fullURLSplit[0];
let subdomWEM = fullURLSplit[1].replace("/widget.js?","");
fullURLSplit = fullURL.split('.');
let subdomain = fullURLSplit[0].replace("http://","").replace("http://","");
const api_endpoint = fullURL+"/widget?sd="+subdomWEM+'&module='+module;

if(['', 'undefined', 'www'].indexOf(subdomain)===-1){	
	CSAPI.parentNode.insertBefore(WidgetForm, CSAPI);	
	renderWidget();
}

function renderWidget(){
	let widgetConstructor = Widgets['AJload_'+module+'_WidgetForm'];
	let filterEnabledWidget = ['services','product','cellPhones'].includes(module);
	
	if(!filterEnabledWidget){
		fetch(api_endpoint)
		.then(res=>res.json()).then(data=>{
			language = data.language;
			if(data.error !== ''){
				WidgetForm.innerHTML = data.error;
			}
			else if(!loadLangFile(data)) widgetConstructor(data);
		})
		.catch((err)=>console.log('error happened:-',err));
	}
	else AJload_filtered_WidgetForm();

	function loadLangFile(data){
		if(!document.getElementById('viewPageInfo')){
			let script = document.createElement('script');
			script.setAttribute('type',"text/javascript")
			script.setAttribute('src',fullURL+'/assets/js-'+swVersion+'/languages/'+data.language+'.js');
			document.body.appendChild(script);
			script.addEventListener('load',()=>widgetConstructor(data));
			return true;
		}
	}
}

//=========Contact Us=========//
async function AJload_contact_us_WidgetForm(data){
	let bg = data.bg_color;
	let color = data.color;
	let font = data.font_family;
	let btn_bg = data.but_bg_color;
	let btn_color = data.but_color;
	let btn_font = data.but_font_family;
	let language = data.language;	

	let style = document.createElement('style');
	style.innerHTML = `
		#loadWidgetForm .wid100Per{width:100%;float:left}
		#loadWidgetForm .widgetInput{width:95%; height:45px; padding-left:4%;background: rgba(255, 255, 255, 0.7);border:1px solid #CCC;color:#414141; font-family:${font};font-size:15px;line-height:45px;}
		#loadWidgetForm .widgetSubmit{background: ${btn_bg};border: none;color:${btn_color};font-family:${btn_font};font-size: 14px !important;text-transform: uppercase !important; padding:0px 15px !important; line-height:45px;}
		#loadWidgetForm .mBot20{margin:0 0 20px;}
		#loadWidgetForm .cursor{cursor:pointer}
	`;
	WidgetForm.appendChild(style);
		let section = document.createElement('section');
		section.setAttribute('class',"wid100Per");
		section.setAttribute('style',`font-family: ${font}; padding: 20px 0; text-align: left; `);
			let form = document.createElement('form');
			form.setAttribute('name',"frmCSWidget");
			form.setAttribute('id',"frmCSWidget");
			form.setAttribute('class',"formfield");
			form.setAttribute('method',"post");
			form.setAttribute('enctype',"multipart/form-data");
				let div13 = document.createElement('div');
				div13.setAttribute('style',`background: ${bg}; color: ${color};  width: 100%; max-width: 480px; overflow: hidden; margin: 0 auto; padding: 20px; border: 1px solid #ccc; text-align: left; `);
					let div = document.createElement('div');
					div.setAttribute('class',"wid100Per");
						let h2 = document.createElement('h2');
						h2.setAttribute('class',"mBot20");
						h2.setAttribute('style',"border-bottom: 1px solid #363947");
						h2.append(Translate('Get in touch with us'));
					div.appendChild(h2);
				div13.appendChild(div);
					let div1 = document.createElement('div');
					div1.setAttribute('class',"wid100Per mBot20");
						let input = document.createElement('input');
						input.setAttribute('required',"");
						input.setAttribute('minlength',"2");
						input.setAttribute('maxlength',"50");
						input.setAttribute('type',"text");
						input.setAttribute('placeholder', Translate('Name'));
						input.setAttribute('name',"name");
						input.setAttribute('id',"name");
						input.setAttribute('class',"widgetInput");
					div1.appendChild(input);
				div13.appendChild(div1);
					let div2 = document.createElement('div');
					div2.setAttribute('class',"wid100Per mBot20");
						let input1 = document.createElement('input');
						input1.setAttribute('required',"");
						input1.setAttribute('minlength',"6");
						input1.setAttribute('maxlength',"50");
						input1.setAttribute('type',"email");
						input1.setAttribute('placeholder', Translate('Email'));
						input1.setAttribute('name',"email");
						input1.setAttribute('id',"email");
						input1.setAttribute('class',"widgetInput");
					div2.appendChild(input1);
				div13.appendChild(div2);
					let div3 = document.createElement('div');
					div3.setAttribute('class',"wid100Per mBot20");
						let textarea = document.createElement('textarea');
						textarea.setAttribute('required',"");
						textarea.setAttribute('minlength',"5");
						textarea.setAttribute('class',"widgetInput");
						textarea.setAttribute('placeholder', Translate('Message'));
						textarea.setAttribute('name',"message");
						textarea.setAttribute('id',"message");
						textarea.setAttribute('style',"min-height: 100px");
					div3.appendChild(textarea);
				div13.appendChild(div3);
					let div11 = document.createElement('div');
					div11.setAttribute('class',"wid100Per");
						let div10 = document.createElement('div');
						div10.setAttribute('id',"mathCaptcha");	
					div11.appendChild(div10);								
						let span = document.createElement('span');
						span.setAttribute('id',"errRecaptcha");
						span.setAttribute('style',"color: red");
					div11.appendChild(span);
				div13.appendChild(div11);
					let div12 = document.createElement('div');
					div12.setAttribute('class',"wid100Per mBot20");
						let input3 = document.createElement('input');
						input3.setAttribute('name',"btnCkRS");
						input3.setAttribute('id',"btnCkRS");
						input3.setAttribute('value', Translate('Send Message'));
						input3.setAttribute('type',"submit");
						input3.setAttribute('class',"widgetSubmit");
					div12.appendChild(input3);
						let input4 = document.createElement('input');
						input4.setAttribute('name',"language");
						input4.setAttribute('id',"language");
						input4.setAttribute('value',language);
						input4.setAttribute('type',"hidden");
					div12.appendChild(input4);
				div13.appendChild(div12);
					let span1 = document.createElement('span');
					span1.setAttribute('class',"wid100Per");
					span1.setAttribute('id',"showRetVal");
				div13.appendChild(span1);
			form.appendChild(div13);
			form.addEventListener('submit', sendContactUs)
		section.appendChild(form);
	WidgetForm.appendChild(section);
	mathCaptcha();
	document.querySelectorAll('input').forEach(node=>node.addEventListener('blur',sanitizer));
	document.querySelectorAll('textarea').forEach(node=>node.addEventListener('blur',sanitizer));			
}

async function sendContactUs(e){
	e.preventDefault();
	const jsonData = frmSerialize("#frmCSWidget");
	let i = document.getElementById("errRecaptcha");
	let language = document.getElementById("language").value;

	if(i.innerHTML="","Checked"!==checkMathCaptcha())return i.innerHTML=Translate('Please verify you are human!'),!1;
	
	fetch(fullURL+"/widget?sd="+subdomWEM+'&module='+module,{method: "POST",body:JSON.stringify(jsonData), headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}})
	.then(res=>res.json()).then(data=>{
		if(data.error !== ''){
			WidgetForm.innerHTML = data.error;
			return;
		}else{
			document.getElementById('frmCSWidget').reset();
			document.getElementById("showRetVal").innerHTML = data.responseStr;
			return;
		}
	}).catch(()=>{
		alert(Translate('There is an error on connection.'));
	})
}

//===========Customer=========//
async function AJload_customer_WidgetForm(data){
	let bg = data.bg_color;
	let color = data.color;
	let font = data.font_family;
	let btn_bg = data.but_bg_color;
	let btn_color = data.but_color;
	let btn_font = data.but_font_family;			
	let language = data.language;
	
	let style = document.createElement('style');
	style.innerHTML = `
		#loadWidgetForm .wid100Per{width:100%;}
		#loadWidgetForm .wid40Per{width:38%;float:left;padding:0 1%;}
		#loadWidgetForm .wid60Per{width:58%;float:right;padding:0 1%;}
		#loadWidgetForm .widgetInput{width:95%; height:45px; padding-left:4%;background: rgba(255, 255, 255, 0.7);border:1px solid #CCC;color:#414141; font-family:${font};font-size:15px;line-height:45px;}
		#loadWidgetForm .widgetSubmit{background: ${btn_bg};border: none;color:${btn_color};font-family:${btn_font};font-size: 14px !important;text-transform: uppercase !important; padding:0px 15px !important; line-height:45px;}
		#loadWidgetForm .mBot20{margin:0 0 20px;}
		#loadWidgetForm .cursor{cursor:pointer}
		@media screen and (max-width: 768px) {
			#loadWidgetForm .wid40Per{width:98%;float:left;padding:0 1%;}
			#loadWidgetForm .wid60Per{width:98%;float:left;padding:0 1%;}
		}
		`;
	WidgetForm.appendChild(style);

	let div, fieldContainer_1st, input, option, textarea, span;
		const section = document.createElement('section');
		section.setAttribute('class',"wid100Per");
		section.setAttribute('style',`padding: 20px 0; text-align: left; `);
			let form = document.createElement('form');
			form.setAttribute('name',"frmCSWidget");
			form.setAttribute('id',"frmCSWidget");
			form.setAttribute('class',"formfield");
			form.setAttribute('method',"post");
			form.setAttribute('enctype',"multipart/form-data");
			form.addEventListener('submit',saveCustomer);

				let div13 = document.createElement('div');
				div13.setAttribute('style', `background: ${bg}; color: ${color}; font-family: ${font}; width: 100%; max-width: 960px; overflow: hidden; margin: 0 auto; padding: 20px; border: 1px solid #ccc; text-align: left;`);
					div = document.createElement('div');
					div.setAttribute('class',"wid100Per");
						let h2 = document.createElement('h2');
						h2.setAttribute('class',"mBot20");
						h2.setAttribute('style',`border-bottom: 1px solid ${color}`);
						h2.innerHTML = Translate('Add Customer Information');
					div.appendChild(h2);
				div13.appendChild(div);
					div = document.createElement('div');
					div.setAttribute('class',"wid100Per");
					if(data.CFNames.length>0){
						let u = Math.ceil(data.CFNames.length / 2);
						fieldContainer_1st = document.createElement('div');
						fieldContainer_1st.setAttribute('class',"wid40Per");
					div.appendChild(fieldContainer_1st);
						let fieldContainer_2nd = document.createElement('div');
						fieldContainer_2nd.setAttribute('class',"wid60Per");
					div.appendChild(fieldContainer_2nd);
						data.CFNames.forEach((cfn,indx)=>{                  
							if(indx<u){                    
								fieldCreator(fieldContainer_1st);
							}else{
								fieldCreator(fieldContainer_2nd);
							}                 
						
							function fieldCreator(fieldContainer){
								let type = data.CFDetails[cfn][1];
								if(type === 'Checkbox'){
									if(cfn === 'offers_email'){
										let label = document.createElement('label');
										label.setAttribute('class','cursor mBot20');
										label.setAttribute('for',cfn);
										input = document.createElement('input');
										input.checked = true;
										input.setAttribute('type','checkbox');
										input.setAttribute('class','cursor');
										input.setAttribute('name',cfn);
										input.setAttribute('id',cfn);
										input.setAttribute('value','1');
										label.appendChild(input);
										label.append(' ', data.CFDetails[cfn][0]);
										fieldContainer.appendChild(label);
									}
									else{
										let label = document.createElement('label');
										label.style.display = 'block';
										label.setAttribute('class','cursor mBot20');
										label.setAttribute('for',cfn);
										input = document.createElement('input');
										if(data.CFDetails[cfn][3] === '*') input.required = true;
										input.setAttribute('type','checkbox');
										input.setAttribute('class','cursor');
										input.setAttribute('name',cfn);
										input.setAttribute('id',cfn);
										input.setAttribute('value','1');
										label.appendChild(input);
										label.append(' ', data.CFDetails[cfn][0]+' '+data.CFDetails[cfn][3]);
										fieldContainer.appendChild(label);
									}                  
								}
								else if(type === 'DropDown'){
									let select = document.createElement('select');
									if(data.CFDetails[cfn][3] === '*') select.required = true;
									select.setAttribute('class','widgetInput mBot20');
									select.setAttribute('name',cfn);
									select.setAttribute('id',cfn);
										option = document.createElement('option');
										option.setAttribute('value','');
										option.innerHTML = 'Select ' + data.CFDetails[cfn][0]+data.CFDetails[cfn][3];
									select.appendChild(option);
									let options =  data.CFDetails[cfn][2];
									if(options !== ''){
										options.split('||').forEach(item=>{
											option = document.createElement('option');
											option.setAttribute('value',item);
											option.innerHTML = item;
											select.appendChild(option);
										})
									}
									fieldContainer.appendChild(select); 
								}
								else if(type === 'TextAreaBox'){
									textarea = document.createElement('textarea');
									textarea.setAttribute('rows','1');
									textarea.setAttribute('cols','50');
									textarea.setAttribute('placeholder',data.CFDetails[cfn][0]+' '+data.CFDetails[cfn][3]);
									if(data.CFDetails[cfn][3] === '*') textarea.required = true;
									textarea.setAttribute('name',cfn);
									textarea.setAttribute('id',cfn);
									textarea.setAttribute('class','widgetInput mBot20');
									fieldContainer.appendChild(textarea); 
								}
								else if(type === 'Date'){
									const input = document.createElement('input');
									input.setAttribute('type','text');
									input.addEventListener('keydown',event=>event.preventDefault());
									input.setAttribute('placeholder',data.CFDetails[cfn][0]+' '+data.CFDetails[cfn][3]);
									if(data.CFDetails[cfn][3] === '*') input.required = true;
									input.setAttribute('name',cfn);
									input.setAttribute('id',cfn);
									input.setAttribute('class','widgetInput mBot20');
									fieldContainer.appendChild(input); 
									date_picker_dialog(input,({date, month, year}, close)=>{
										close();
										let dateVal;
										if(data.dateformat.toLowerCase()=='d-m-y'){
											dateVal = date+'-'+month+'-'+year;    
										}    
										else{    
											dateVal = month+'/'+date+'/'+year;    
										}    
										input.value = dateVal;
									},data.dateformat);
								}
                                else if(type==='TextBox')
                                {
									input = document.createElement('input');
									input.setAttribute('type','text');
									input.setAttribute('placeholder',data.CFDetails[cfn][0]);
									input.setAttribute('name',cfn);
									input.setAttribute('id',cfn);
									input.setAttribute('class','widgetInput mBot20');
									if(cfn === 'email'){
										input.addEventListener('blur',checkEmail);
										span = document.createElement('span');
										span.setAttribute('id','erremail');
										span.setAttribute('style','color:red;font-size:12px;font-style:italic');
										fieldContainer.appendChild(span);
									}
									if(data.CFDetails[cfn][3] === '*'){
										input.required = true;
										input.setAttribute('minlength','2');
										input.setAttribute('maxlength','50');
										input.setAttribute('placeholder',data.CFDetails[cfn][0]+' *');
									}
									fieldContainer.appendChild(input); 
								}
							}
						});

						textarea = document.createElement('textarea');
						textarea.setAttribute('cols','100');
						textarea.setAttribute('rows','1');
						textarea.setAttribute('style','display:none');
						textarea.setAttribute('name','CFNamesJson');
						textarea.setAttribute('id','CFNamesJson');
						textarea.innerHTML = JSON.stringify(data.CFNames);
					div.appendChild(textarea); 
						textarea = document.createElement('textarea');
						textarea.setAttribute('cols','100');
						textarea.setAttribute('rows','1');
						textarea.setAttribute('style','display:none');
						textarea.setAttribute('name','CFDetailsJson');
						textarea.setAttribute('id','CFDetailsJson');
						textarea.innerHTML = JSON.stringify(data.CFDetails);
					div.appendChild(textarea); 
						input = document.createElement('input');
						input.setAttribute('type','hidden');
						input.setAttribute('name','CFCount');
						input.setAttribute('id','CFCount');
						input.value = data.CFCount;
					div.appendChild(input);
					}	
				div13.appendChild(div);
					div = document.createElement('div');
					div.setAttribute('class',"wid100Per");
						fieldContainer_1st = document.createElement('div');
						fieldContainer_1st.setAttribute('class',"wid40Per");
							let divCaptcha = document.createElement('div');
							divCaptcha.setAttribute('id','mathCaptcha');
						fieldContainer_1st.appendChild(divCaptcha);
							span = document.createElement('span');
							span.setAttribute('id','errRecaptcha');
							span.setAttribute('style',"color:red");
						fieldContainer_1st.appendChild(span);
					div.appendChild(fieldContainer_1st);
						
				div13.appendChild(div);
					div = document.createElement('div');
					div.setAttribute('class',"wid100Per mBot20");
						fieldContainer_1st = document.createElement('div');
						fieldContainer_1st.setAttribute('class',"wid40Per");
							input = document.createElement('input');
							input.setAttribute('name','btnCkRS');
							input.setAttribute('id','btnCkRS');                           
							input.setAttribute('value', Translate('Save'));                
							input.setAttribute('type','submit');                
							input.setAttribute('class','widgetSubmit');
						fieldContainer_1st.appendChild(input);
							let input4 = document.createElement('input');
							input4.setAttribute('name',"language");
							input4.setAttribute('id',"language");
							input4.setAttribute('value',language);
							input4.setAttribute('type',"hidden");
						fieldContainer_1st.appendChild(input4); 
					div.appendChild(fieldContainer_1st);
				div13.appendChild(div);
					div = document.createElement('div');
					div.setAttribute('class',"wid100Per");               
					div.setAttribute('id',"showRetVal"); 
					div.setAttribute("style","font-size:16px"); 
					div.innerHTML = '&nbsp;'
				div13.appendChild(div);
			form.appendChild(div13)
		section.appendChild(form);
	WidgetForm.appendChild(section);
	mathCaptcha();
	document.querySelectorAll('input').forEach(node=>node.addEventListener('blur',sanitizer));
	document.querySelectorAll('textarea').forEach(node=>node.addEventListener('blur',sanitizer));			
}

function checkEmail() {
  	let e = document.getElementById("email");
	if(e.value==='') return;
	let language = document.getElementById("language").value;
  	let t =
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(
        String(e.value).toLowerCase()
      ),
    o = document.getElementById("erremail");
  	return (
    	(o.innerHTML = ""), !!t || ((o.innerHTML = Translate('Invalid Email')), e.focus(), !1)
  	);
}

async function saveCustomer(e) {
	e.preventDefault();
	let language = document.getElementById("language").value;
	const jsonData = frmSerialize("#frmCSWidget");
	let i = document.getElementById("errRecaptcha");
	if(i.innerHTML="","Checked"!==checkMathCaptcha())return i.innerHTML=Translate('Please verify you are human'),!1;
	
	let r = document.getElementById("errRecaptcha");
	if (((r.innerHTML = ""), "Checked" !== checkMathCaptcha()))
		return (r.innerHTML = Translate('Please verify you are human')), !1;
	
	fetch(fullURL+"/widget?sd="+subdomWEM+'&module='+module,{method: "POST",body:JSON.stringify(jsonData), headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}})
	.then(res=>res.json()).then(data=>{
		if(data.error !== ''){
			WidgetForm.innerHTML = data.error;
			return;
		}else{
			document.getElementById('frmCSWidget').reset();
			document.getElementById("showRetVal").innerHTML = data.responseStr;
			return;
		}
		alert(Translate('There is an error while saving information.'));
	}).catch(()=>{
		alert(Translate('There is an error on connection.'));
	})
}


//=========Appointment=========//
const weekDays = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
const Months = ["January","February","March","April","May","June","July","August","September","October","November","December"]

async function AJload_appointment_WidgetForm(data){  
	let e = new Date(),
	t = e.getFullYear(),
	i = e.getMonth()

	let bg = data.bg_color;
	let color = data.color;
	let font = data.font_family;
	let btn_bg = data.but_bg_color;
	let btn_color = data.but_color;
	let btn_font = data.but_font_family;
	let language = data.language;
	let y = data.timeformat === '12 hour'? '90': '70';
	
	let style = document.createElement('style');
	style.innerHTML = `
		#loadWidgetForm .wid100Per{width:100%;float:left}
		#loadWidgetForm .wid40Per{width:38%;float:left;padding:0 1%;}
		#loadWidgetForm .wid50Per{width:48%;float:left;padding:0 1%;}
		#loadWidgetForm .wid60Per{width:58%;float:right;padding:0 1%;}
		#loadWidgetForm .widgetInput{width:95%; height:45px; padding-left:4%;background: rgba(255, 255, 255, 0.7);border:1px solid #CCC;color:#414141; font-family:${font};font-size:15px;line-height:45px;}
		#loadWidgetForm .widgetSubmit{background: ${btn_bg};border: none;color:${btn_color};font-family:${btn_font};font-size: 14px !important;text-transform: uppercase !important; padding:0px 15px !important; line-height:45px;}
		#loadWidgetForm .mBot20{margin:0 0 20px;}
		#loadWidgetForm .calDates .mmyyName {background-color: #24aeff; color: white; text-align: center; font-size: 14pt; padding: 5px 10px; transition: 0.5s background-color;}
		#loadWidgetForm .calDates .day.selected, .HMdiv.selected{background-color: #1caff6;color: white;cursor:pointer;opacity: .7;transition: .5s opacity;}
		#loadWidgetForm .calDates .day.selected:hover, .HMdiv.selected:hover{opacity: 1;}
		#loadWidgetForm .calDates .day{float:left;width:46px;margin:3px;padding:6px 0;font-size:13pt;text-align:center;border-radius: 10px;border: solid 1px #ddd;}
		#loadWidgetForm .calDates .day.label, .HMdiv.label{background-color: white;color: black;border:none;font-weight:bold;padding:1px;}
		#loadWidgetForm .calDates .day.disab{color:#CCC !important;}
		#loadWidgetForm .calDates .day.blank{background-color: white;border:none;padding:1px;}
		#loadWidgetForm .HMdiv {float: left; width: ${y}px; padding: 5px 10px; margin: 0 5px 10px; border: solid 1px #ddd; font-size: 13pt; text-align: center;}
		#loadWidgetForm .cursor{cursor:pointer}
		@media screen and (max-width: 768px) {
			#loadWidgetForm .wid40Per{width:98%;float:left;padding:0 1%;}
			#loadWidgetForm .wid50Per{width:98%;float:left;padding:10px 1%;}
			#loadWidgetForm .wid60Per{width:98%;float:left;padding:0 1%;}
		}
	`;

	WidgetForm.appendChild(style);

	let div, input, textarea, divClndr, span;
	const section = document.createElement('section');
	section.setAttribute('class',"wid100Per");
	section.setAttribute('style',`padding: 20px 0; text-align: left; `);
		let form = document.createElement('form');
		form.setAttribute('name',"frmCSWidget");
		form.setAttribute('id',"frmCSWidget");
		form.setAttribute('class',"formfield");
		form.setAttribute('method',"post");
		form.setAttribute('enctype',"multipart/form-data");
		form.addEventListener('submit',sendAppointment);
			let div13 = document.createElement('div');
			div13.setAttribute('style', `background: ${bg}; color: ${color}; font-family: ${font}; width: 100%; max-width: 960px; overflow: hidden; margin: 0 auto; padding: 20px; border: 1px solid #ccc; text-align: left;`);
				div = document.createElement('div');
				div.setAttribute('class',"wid100Per");
				let h2 = document.createElement('h2');
				h2.setAttribute('class',"mBot20");
				h2.setAttribute('style',`border-bottom: 1px solid ${color}`);
				h2.innerHTML = Translate('Repair Appointment');
				div.appendChild(h2);
			div13.appendChild(div);
			div = document.createElement('div');
			div.setAttribute('class',"wid100Per");
			if(data.fieldNames.length>0){
					let f = parseInt(data.fieldNames.length / 2);
					let fieldContainer_1st = document.createElement('div');
					fieldContainer_1st.setAttribute('class',"wid40Per");
						div.appendChild(fieldContainer_1st);
					let fieldContainer_2nd = document.createElement('div');
					fieldContainer_2nd.setAttribute('class',"wid60Per");
					div.appendChild(fieldContainer_2nd);
					data.fieldNames.forEach((item,indx)=>{
						if(indx<=f){                    
							fieldCreator(fieldContainer_1st);
						}else{
							fieldCreator(fieldContainer_2nd);
						}
						function fieldCreator(fieldContainer){
							input = document.createElement('input');
							input.setAttribute('placeholder',item);
							input.setAttribute('name',`fieldName${indx}`);
							input.setAttribute('id',`fieldName${indx}`);
							input.setAttribute('class','widgetInput mBot20');
							if(indx === 0){
							input.setAttribute('type','text');
							input.setAttribute('minlength','2');
							input.setAttribute('maxlength','50');
							input.required = true;
							}else if(indx === 1){
							input.setAttribute('type','tel');
							input.setAttribute('minlength','6');
							input.setAttribute('maxlength','50');
							input.required = true;
							}else if(indx === 2){
							input.setAttribute('type','email');
							input.setAttribute('minlength','6');
							input.setAttribute('maxlength','50');
							input.required = true;
							}else{
							input.setAttribute('type','text');
							}
							fieldContainer.appendChild(input);
						}
					})
				}
			div13.appendChild(div);
				div = document.createElement('div');
				div.setAttribute('class',"wid100Per mBot20");
				textarea = document.createElement('textarea');
				textarea.setAttribute('style','display:none');
				textarea.setAttribute('name','scheduleJson');
				textarea.setAttribute('id','scheduleJson');
				textarea.innerHTML = JSON.stringify(data.schedules);
				div.appendChild(textarea);
				textarea = document.createElement('textarea');
				textarea.setAttribute('style','display:none');
				textarea.setAttribute('name','appointments');
				textarea.setAttribute('id','appointments');
				textarea.innerHTML = JSON.stringify(data.appointments);
				div.appendChild(textarea);
				textarea = document.createElement('textarea');
				textarea.setAttribute('style','display:none');
				textarea.setAttribute('name','fieldNamesJson');
				textarea.setAttribute('id','fieldNamesJson');
				textarea.innerHTML = JSON.stringify(data.fieldNames);
				div.appendChild(textarea);
				input = document.createElement('input');
				input.setAttribute('type','hidden');
				input.setAttribute('name','fieldCount');
				input.setAttribute('id','fieldCount');
				input.value = data.fieldNames.length;
				div.appendChild(input);
				divClndr = document.createElement('div');
				divClndr.setAttribute("class","wid50Per")
				divClndr.appendChild(loadCalendarDays(t, i, "curMonth", data.schedules,data.blockoutDates));
				div.appendChild(divClndr);
			div13.appendChild(div);
				11 === i ? ((t += 1), (i = 0)) : (i += 1);
				e.setFullYear(t);
				e.setMonth(i);
				t = e.getFullYear();
				i = e.getMonth();
				divClndr = document.createElement('div');
				divClndr.setAttribute("class","wid50Per")
				divClndr.appendChild(loadCalendarDays(t, i, "nextMonth", data.schedules,data.blockoutDates));
				div.appendChild(divClndr);
			div13.appendChild(div)
			div = document.createElement('div');
			div.setAttribute('class','wid100Per');
				input = document.createElement('input');
				input.setAttribute('type','hidden');
				input.setAttribute('name','appdate');
				input.setAttribute('id','appdate');
				input.value = '';
			div.appendChild(input);
				input = document.createElement('input');
				input.setAttribute('type','hidden');
				input.setAttribute('name','hourMinute');
				input.setAttribute('id','hourMinute');
				input.value = '';
			div.appendChild(input);
				input = document.createElement('input');
				input.setAttribute('type','hidden');
				input.setAttribute('name','timeformat');
				input.setAttribute('id','timeformat');
				input.value = data.timeformat;
			div.appendChild(input);
				span = document.createElement('span');
				span.setAttribute('id','errAppdate');
				span.setAttribute('style','color:red;margin-bottom:15px;display:block;');
			div.appendChild(span);
				let divTM = document.createElement('div');
				divTM.setAttribute('class','wid100Per');
				divTM.setAttribute('id','timeToMeet');
			div.appendChild(divTM);
				span = document.createElement('span');
				span.setAttribute('id','errApptime');
				span.setAttribute('style','color:red;margin-bottom:15px;display:block;');
			div.appendChild(span);
		div13.appendChild(div);
			div = document.createElement('div');
			div.setAttribute('class',"wid100Per");
				let divCaptcha = document.createElement('div');
				divCaptcha.setAttribute('id','mathCaptcha');
			div.appendChild(divCaptcha);
				span = document.createElement('span');
				span.setAttribute('id','errRecaptcha');
				span.setAttribute('style',"color:red");
			div.appendChild(span);
		div13.appendChild(div);
			div = document.createElement('div');
			div.setAttribute('class',"wid100Per mBot20");
				input = document.createElement('input');
				input.setAttribute('name','btnCkRS');
				input.setAttribute('id','btnCkRS');          
				input.setAttribute('value', Translate('SEND APPOINTMENT'));
				input.setAttribute('type','submit');                
				input.setAttribute('class','widgetSubmit');
			div.appendChild(input);
				let input4 = document.createElement('input');
				input4.setAttribute('name',"language");
				input4.setAttribute('id',"language");
				input4.setAttribute('value',language);
				input4.setAttribute('type',"hidden");
			div.appendChild(input4);              
		div13.appendChild(div);
			div = document.createElement('div');
			div.setAttribute('class',"wid100Per");               
			div.setAttribute('id',"showRetVal"); 
			div.setAttribute("style","font-size:16px"); 
			div.innerHTML = '&nbsp;'
		div13.appendChild(div);
		form.appendChild(div13);
	section.appendChild(form);
	WidgetForm.appendChild(section);
	mathCaptcha();
	document.querySelectorAll('input').forEach(node=>node.addEventListener('blur',sanitizer));
	document.querySelectorAll('textarea').forEach(node=>node.addEventListener('blur',sanitizer));
}

function loadCalendarDays(year, month, currentOrNext, schedules,blockoutDates) {
	let div, span, dayDiv;
	let calendar = cTag('div',{ 'style':'background-color:white;max-width:380px;box-shadow: 0px 5px 10px rgba(0,0,0,0.4)','class':'wid100Per calDates','id':currentOrNext });
		div = cTag('div',{'class':'mmyyName','style':'float: left'});
		span = cTag('span');
		span.innerHTML = Months[month];
		div.appendChild(span);
	calendar.appendChild(div);
		div = cTag('div',{'class':'mmyyName','style':'float: right'});
		span = cTag('span');
		span.innerHTML = year;
		div.appendChild(span);
	calendar.appendChild(div);
		div = cTag('div',{'style':'clear:both'});
	calendar.appendChild(div);
		div = cTag('div',{'class':'wid100Per'});
		["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"].forEach((day,indx)=>{
		if(weekDays[indx] in schedules){
			dayDiv = cTag('div',{'class':'day label'});
			dayDiv.innerHTML = day;
		div.appendChild(dayDiv);
		}else{
			dayDiv = cTag('div',{'class':'day label disab'});
			dayDiv.innerHTML = day;
		div.appendChild(dayDiv);
		}
		})
	calendar.appendChild(div);
    div = cTag('div',{'class':'wid100Per'});


	let dayOfMonth = new Date(year, month, 1).getDay();
    for (let l = 0; l<dayOfMonth; l++){
		let blankDiv = cTag('div',{'class':'day blank'});
    	div.appendChild(blankDiv);
    }

	let lastDateOfMonth = new Date(year, month+1, 0).getDate();
	let today = new Date();
	let dateToBeCompared = new Date(today.getFullYear(),today.getMonth()+1,today.getDate()).getTime();
    for (let date = 1; date<=lastDateOfMonth; date++) {
		if(dayOfMonth===7) dayOfMonth = 0;
		let formatedDate = year + "-" + (month+1) + "-" + date;
		let currentDate = new Date(year,month+1,date).getTime();

		if(weekDays[dayOfMonth] in schedules && dateToBeCompared<=currentDate && !blockoutDates.includes(`${year}-${month+1>9?month+1:'0'+(month+1)}-${date>9?date:'0'+date}`)){
			let activeDiv = cTag('div',{'id':formatedDate,'class':'day cursor'});
			activeDiv.addEventListener('click',()=>dateClick(formatedDate));
			if(dateToBeCompared===currentDate){
				activeDiv.setAttribute('style','border-color:#2aaeff');
			}
			activeDiv.innerHTML = date;
			div.appendChild(activeDiv);
		}else{
			let inactiveDiv = cTag('div',{'id':formatedDate,'class':'day disab'});
			inactiveDiv.innerHTML = date;
			div.appendChild(inactiveDiv);
		}
		dayOfMonth++;
    }
	calendar.appendChild(div);
	return calendar;
}

function dateClick(date) {
  document.querySelectorAll(".day.selected").forEach(item=>item.classList.remove("selected"));
  document.getElementById(date).classList.add("selected");
  let d = document.getElementById("timeformat").value,
    r = date.split("-"),
    o = r[0],
    l = r[1],
    s = r[2],
    c = new Date(o, l - 1, s).getDay(),
    m = new Date(),
    u = m.getMonth() + 1,
    p = new Date(m.getFullYear(), u, m.getDate()).getTime(),
    g = ("0" + m.getHours()).slice(-2) + ":" + ("0" + m.getMinutes()).slice(-2),
    y = "";
  p === new Date(o, l, s).getTime() && (y = " disab");
  let v = JSON.parse(document.getElementById("scheduleJson").value),
    f = weekDays[c];
	
	document.getElementById("timeToMeet").innerHTML = '';
 	if (f in v) {
		let h = v[f];
		if (2 === h.length) {
		let w = h[1],
			b = [],
			x = [];
		for (let M in w)
			if (w.hasOwnProperty(M)) {
			let T = w[M].split("_"),
				B = T[1],
				E = T[2],
				D = B,
				C = E;

				if(d==='12 hour'){
					D = parseInt(D);
					if(D>=12){
						C += "pm";
						if(D>12){
							D -= 12;
						}
					}
					else{
						C += "am";
					}
				}

			let I = B + ":" + E,
				k = D + ":" + C;
			b.includes(I) || b.push(I), x.includes(k) || x.push(k);
			}
		b.sort();
		const N = cTag('div',{'class':'wid100Per'});
		let div;
		if (b.length > 0)
			for (let i = 0; i < b.length; i++)
			if( "" !== y && g > b[i]){
				div = cTag('div',{ 'style':'color:#CCC !important','class':'HMdiv cursor','id':b[i] });
				div.innerHTML = x[i];
				N.appendChild(div);
			}else{
				div = cTag('div',{ 'class':'HMdiv cursor','id':b[i] });
				div.addEventListener('click',()=>setHourMinute(b[i]))
				div.innerHTML = x[i];
				N.appendChild(div);
			}
			document.getElementById("timeToMeet").appendChild(N);         
		}
	}
	(l = ("0" + l).slice(-2)),
    (s = ("0" + s).slice(-2)),
    (document.getElementById("appdate").value = o + "-" + l + "-" + s),
    (document.getElementById("hourMinute").value = "");
}





function setHourMinute(e) {
	let language = document.getElementById("language").value;
	let t = document.getElementById("appdate").value + " " + e;
	let i, a, n;
	if (JSON.parse(document.getElementById("appointments").value).includes(t)) {
		for (
		i = document.getElementsByClassName("HMdiv"), a = 0;
		a < i.length;
		a++
		) {
			n = i.item(a).getAttribute("id");
		if (i.item(a).getAttribute("class").includes("selected"))
			document.getElementById(n).classList.remove("selected");
		}
		(document.getElementById("hourMinute").value = ""),
		(document.getElementById("errApptime").innerHTML =Translate('This date and time already booked. Try again with different date and time.'));
	} else {
		document.getElementById("errApptime").innerHTML = "";
		for (
		i = document.getElementsByClassName("HMdiv"), a = 0;
		a < i.length;
		a++
		) {
		n = i.item(a).getAttribute("id");
		if (i.item(a).getAttribute("class").includes("selected"))
			document.getElementById(n).classList.remove("selected");
		}
		document.getElementById(e).classList.add("selected"),
		(document.getElementById("hourMinute").value = e + ":00");
	}
}

function sendAppointment(evnt) {
	evnt.preventDefault();
	let language = document.getElementById("language").value;
	const jsonData = {};
	jsonData["module"] = module;
  	let  t = parseInt(document.frmCSWidget.querySelector("#fieldCount").value);
	if ((("" === t || isNaN(t)) && (t = 0), (jsonData['fieldCount']=t), t > 0))
		for (let i = 0; i < t; i++) {
		jsonData[`field${i}Val`] = document.getElementById("fieldName" + i).value;
		}
	JSON.parse(document.frmCSWidget.querySelector("#fieldNamesJson").value);
	let a = document.frmCSWidget.querySelector("#appdate");
	let n,d = document.frmCSWidget.querySelector("#hourMinute");
	if (
		(((n = document.frmCSWidget.querySelector("#errAppdate")).innerHTML = ""),
		"" === a.value)
	)
    return (n.innerHTML = Translate('Missing Date to meet')), a.focus(), !1;
	jsonData['appdate'] = a.value;
	if (
		(((n = document.frmCSWidget.querySelector("#errApptime")).innerHTML = ""),
		"" === d.value)
	)
		return (n.innerHTML = Translate('Missing hour minutes to meet')), a.focus(), !1;
	if (
		((jsonData['hourMinute'] = d.value),
		((n = document.frmCSWidget.querySelector("#errRecaptcha")).innerHTML = ""),
		"Checked" !== checkMathCaptcha())
	)
    return (n.innerHTML = Translate('Please verify you are human')), !1;

  	fetch(fullURL+"/widget?sd="+subdomWEM+'&module='+module,{method: "POST",body:JSON.stringify(jsonData), headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}})
	.then(res=>res.json()).then(data=>{
		let showRetVal = document.frmCSWidget.querySelector("#showRetVal");
		if(data.error !== ''){
			showRetVal.innerHTML = data.error;
		}else{
      showRetVal.innerHTML = data.responseStr;
    }
  }).catch((err)=>{
    console.log(err);
		alert(Translate('There is an error on connection.'));
	});
}

//============Repair Status=========//
function AJload_repair_status_WidgetForm(data) {
	let bg = data.bg_color;
	let color = data.color;
	let font = data.font_family;
	let btn_bg = data.but_bg_color;
	let btn_color = data.but_color;
	let btn_font = data.but_font_family;
	let language = data.language;
	
	let style = document.createElement('style');
	style.innerHTML = `
	#loadWidgetForm .wid100Per{width:100%;}
	#loadWidgetForm .widgetInput{width:95%; height:45px; padding-left:4%;background: rgba(255, 255, 255, 0.7);border:1px solid #CCC;color:#414141; font-family:${font};font-size:15px;line-height:45px;}
	#loadWidgetForm .widgetSubmit{background: ${btn_bg};border: none;color:${btn_color};font-family:${btn_font};font-size: 14px !important;text-transform: uppercase !important; padding:0px 15px !important; line-height:45px;}
	#loadWidgetForm .mBot20{margin:0 0 20px;}
	#loadWidgetForm .cursor{cursor:pointer}
	`;
	WidgetForm.appendChild(style);
		const section = document.createElement('section');
		section.setAttribute('class',"wid100Per");
		section.setAttribute('style',`font-family: 'Arial'; padding: 20px 0; text-align: left; `);
			let form = document.createElement('form');
			form.setAttribute('name',"frmCSWidget");
			form.setAttribute('id',"frmCSWidget");
			form.setAttribute('class',"formfield");
			form.setAttribute('method',"post");
			form.setAttribute('enctype',"multipart/form-data");
				let div13 = document.createElement('div');
				div13.setAttribute('style',`background: ${bg}; color: ${color}; width: 100%; max-width: 480px; overflow: hidden; margin: 0 auto; padding: 20px; border: 1px solid #ccc; text-align: left;`);
					const div = document.createElement('div');
					div.setAttribute('class',"wid100Per");
						let h2 = document.createElement('h2');
						h2.setAttribute('class',"mBot20");
						h2.setAttribute('style',"border-bottom: 1px solid #363947");
						h2.innerHTML = Translate('Repair Status Online');
					div.appendChild(h2);
				div13.appendChild(div);
					let div1 = document.createElement('div');
					div1.setAttribute('class',"wid100Per mBot20");
						let input = document.createElement('input');
						input.setAttribute('required',"");
						input.setAttribute('minlength',"2");
						input.setAttribute('maxlength',"50");
						input.setAttribute('type',"text");
						input.setAttribute('placeholder', Translate('First Name'));
						input.setAttribute('name',"firstName");
						input.setAttribute('id',"firstName");
						input.setAttribute('class',"widgetInput");
					div1.appendChild(input);
				div13.appendChild(div1);
					let div2 = document.createElement('div');
					div2.setAttribute('class',"wid100Per mBot20");
						let input1 = document.createElement('input');
						input1.setAttribute('required',"");
						input1.setAttribute('size',"22");
						input1.setAttribute('minlength',"1");
						input1.setAttribute('maxlength',"11");
						input1.setAttribute('type',"text");
						input1.setAttribute('placeholder', Translate('Ticket Number'));
						input1.setAttribute('name',"ticketNo");
						input1.setAttribute('id',"ticketNo");
						input1.setAttribute('class',"widgetInput");
					div2.appendChild(input1);
				div13.appendChild(div2);
					let div11 = document.createElement('div');
					div11.setAttribute('class',"wid100Per");
						let div10 = document.createElement('div');
						div10.setAttribute('id',"mathCaptcha");	
					div11.appendChild(div10);								
						let span = document.createElement('span');
						span.setAttribute('id',"errRecaptcha");
						span.setAttribute('style',"color: red");
					div11.appendChild(span);
				div13.appendChild(div11);
					let div12 = document.createElement('div');
					div12.setAttribute('class',"wid100Per mBot20");
						let input3 = document.createElement('input');
						input3.setAttribute('id',"btnCkRS");
						input3.setAttribute('value', Translate('Check Repair Status'));
						input3.setAttribute('type',"submit");
						input3.setAttribute('class',"widgetSubmit");
					div12.appendChild(input3);
						let input4 = document.createElement('input');
						input4.setAttribute('name',"language");
						input4.setAttribute('id',"language");
						input4.setAttribute('value',language);
						input4.setAttribute('type',"hidden");
					div12.appendChild(input4);
				div13.appendChild(div12);
					let span1 = document.createElement('span');
					span1.setAttribute('class',"wid100Per");
					span1.setAttribute('id',"showRetVal");
				div13.appendChild(span1);
			form.appendChild(div13);
			form.addEventListener('submit',getRepairStatus)
		section.appendChild(form);
	WidgetForm.appendChild(section);
	mathCaptcha();
	document.querySelectorAll('input').forEach(node=>node.addEventListener('blur',sanitizer));
	document.querySelectorAll('textarea').forEach(node=>node.addEventListener('blur',sanitizer));
}

function getRepairStatus(e){
    e.preventDefault();
	let language = document.getElementById("language").value;
	const jsonData = frmSerialize("#frmCSWidget");
	let i = document.getElementById("errRecaptcha");
	if(i.innerHTML="","Checked"!==checkMathCaptcha())return i.innerHTML=Translate('Please verify you are human'),!1;
	
    fetch(fullURL+"/widget?sd="+subdomWEM+'&module='+module,{method: "POST",body:JSON.stringify(jsonData), headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}})
    .then(res=>res.json()).then(data=>{
        if(data.error !== ''){
            WidgetForm.innerHTML = data.error;
            return;
        }else{
			document.getElementById('frmCSWidget').reset();
            document.getElementById("showRetVal").innerHTML = data.responseStr;
            return;
        }
    }).catch(()=>{
        alert(Translate('There is an error on connection.'));
    })
}

//=========Quote_part=======//
function AJload_quote_WidgetForm(data){
	let style = document.createElement('style');
	style.innerHTML = `
	#loadWidgetForm *{box-sizing:border-box}
	#loadWidgetForm .columnXS12,#loadWidgetForm .columnSM6,#loadWidgetForm .columnSM12{	padding: 0px 5px;margin: 5px 0;width: 100%;	}
	@media screen and (min-width:300px) { #loadWidgetForm .columnXS12 { width: 100%; } }
	@media (min-width:768px) { #loadWidgetForm .columnSM12 { width: 100%; } #loadWidgetForm .columnSM6 { width: 50%; }	}
	#loadWidgetForm .flexCenterRow { display: flex;flex-flow: row wrap;justify-content: center; }
	#loadWidgetForm .container { max-width: 1170px;margin: 0 auto;padding: 0px 15px;	}
	#loadWidgetForm .sendQuoteButton { width: 132px;height: 51px;background: #ef7f1b;border: none;color: #FFF;text-transform: uppercase;border-radius: 4px;	}
	#loadWidgetForm .sendQuoteButton:hover { background: #071afd;transition: all 1s ease-in-out; }
	`;
	WidgetForm.appendChild(style);

	const inputItem = [
		{min:'2', max:'50', name_id:'name', type:'text', placeholder:Translate('Name')},
		{min:'2', max:'20', name_id:'phone', type:'text', placeholder:Translate('Contact No')},
		{min:'6', max:'50', name_id:'email', type:'email', placeholder:Translate('Email'), size:'50'},
		{min:'2', max:'100', name_id:'bamod', type:'text', placeholder:Translate('Brand and model of device')},
	]

		const quoteSection = cTag('section',{ 'style':`width:100%; padding:20px 0; background:${data.bg_color}; color:${data.color}; font-family:${data.font_family}` });
			const quoteContainer = cTag('div',{ 'class':"container" });
				const divMsg = cTag('div',{'class':'columnXS12','id':'messageContainer'});
			quoteContainer.appendChild(divMsg);
				const quoteRow = cTag('div',{ 'class':"flexCenterRow" });
					const quoteTitle = cTag('div',{ 'class':"columnSM12" });
						const quoteHeader = cTag('h2',{ 'style':"border-bottom:1px solid #000000; margin:0 0 20px;" });
						quoteHeader.innerHTML = Translate('Request a Quote');
					quoteTitle.appendChild(quoteHeader);
				quoteRow.appendChild(quoteTitle);
			quoteContainer.appendChild(quoteRow);
				const quoteForm = cTag('form',{ 'method':"post",'enctype':"multipart/form-data",'name':"frmQuote",'id':"frmQuote" });
				quoteForm.addEventListener('submit', sendQuote);
					const quoteCenter = cTag('div',{ 'class':"flexCenterRow" });

					inputItem.forEach(item=>{
						const inputDiv2 = cTag('div',{ 'class':"columnSM6 columnXS12", 'style': "margin-bottom: 20px;" });
							const inputField = cTag('input',{ 'required':"true",'minlength':item.min,'maxlength':item.max,'type':item.type,'placeholder':item.placeholder,'name':item.name_id,'id':item.name_id,'value':"",'style':"width:100%; height:45px; padding-left:10px;background: rgba(255, 255, 255, 0.7); border:1px solid #CCC;font-size: 15px;line-height:45px;" });
							inputField.addEventListener('blur',sanitizer);
							if(item.size) inputField.setAttribute('size',item.size);
						inputDiv2.appendChild(inputField);
						quoteCenter.appendChild(inputDiv2);
					})
						const textareaDiv = cTag('div',{ 'class':"columnXS12", 'style': "margin-bottom: 20px;" });
							const textarea = cTag('textarea',{ 'required':"true",'minlength':"5",'placeholder':Translate('Problem'),'name':"message",'id':"message",'style':"width:100%; height:150px; padding-left:10px;background: rgba(255, 255, 255, 0.7); border:1px solid #CCC;font-size: 15px;line-height:45px;" });
							textarea.addEventListener('blur',sanitizer);
						textareaDiv.appendChild(textarea);
					quoteCenter.appendChild(textareaDiv);
						let errorColumn = cTag('div',{ 'class':"columnSM6 columnXS12", 'style': "margin-bottom: 20px;" });
						errorColumn.appendChild(cTag('div',{ 'class':"g-recaptcha",'data-sitekey':"6LfigCAUAAAAAJwo4ZdYUPElOfGPNxn8qW2Y6pUz" }));
						errorColumn.appendChild(cTag('span',{ 'style': "color: #ff0000;",'id':"reCaptcha" }));
					quoteCenter.appendChild(errorColumn);
						let captchaColumn = cTag('div',{'class':"columnXS12", 'style': "margin-bottom: 20px;"});
							let captchaField = cTag('div',{'id':"mathCaptcha"});
						captchaColumn.appendChild(captchaField);
							const errorSpan = cTag('span',{'id':"errRecaptcha",'style':"color: red"});
						captchaColumn.appendChild(errorSpan);
					quoteCenter.appendChild(captchaColumn);
						let submitColumn = cTag('div',{ 'class':"columnXS12" });
						submitColumn.appendChild(cTag('input',{ 'type':"submit",'name':"submit",'class':"sendQuoteButton",'value':Translate('SEND QUOTE'),'style':"font-family:'Comic Sans MS';" }));
					quoteCenter.appendChild(submitColumn);
				quoteForm.appendChild(quoteCenter);
			quoteContainer.appendChild(quoteForm);
		quoteSection.appendChild(quoteContainer);
	WidgetForm.appendChild(quoteSection);
	mathCaptcha();
}

async function sendQuote(event){
    event.preventDefault();
	const i = document.getElementById("errRecaptcha");
	if(i.innerHTML="","Checked"!==checkMathCaptcha())return i.innerHTML=Translate('Please verify you are human!'),!1;

	const divMsg = document.querySelector('#messageContainer');
	divMsg.innerHTML = '';
    const jsonData = {
        name: document.querySelector('#name').value,
        phone: document.querySelector('#phone').value,
        email: document.querySelector('#email').value,
        bamod: document.querySelector('#bamod').value,
        message: document.querySelector('#message').value,
    };
    const url = '/Instancehome/sendQuote';
	fetch(api_endpoint,{method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}})
	.then(response=>response.json()).then(data=>{
			const successDiv = cTag('div',{'class':'quoteSuccessMessage','style':'color:#03ae02'});
			successDiv.innerHTML = data.responseStr;
		divMsg.appendChild(successDiv);
		document.querySelector('#frmQuote').reset();
	})

}

//=======filtered_Widget===========//
function AJload_filtered_WidgetForm(){	
	let style = document.createElement('style');
	style.innerHTML = `
	#loadWidgetForm *,.popup_container *{ box-sizing:border-box }
	#loadWidgetForm .columnXS6, #loadWidgetForm .columnXS12, #loadWidgetForm .columnSM3, #loadWidgetForm .columnSM4, #loadWidgetForm .columnSM6, #loadWidgetForm .columnSM12, #loadWidgetForm .columnMD5, #loadWidgetForm .columnMD7{
		padding: 0px 5px;
		margin: 5px 0;
		width: 100%;
	}
	@media screen and (min-width:300px) {
		#loadWidgetForm .columnXS12 { width: 100%; }
		#loadWidgetForm .columnXS6 { width: 50%; }
	}
	@media (min-width:768px) {
		#loadWidgetForm .columnSM12 { width: 100%; }
		#loadWidgetForm .columnSM6 { width: 50%; }
		#loadWidgetForm .columnSM4 { width: 33.33333333%; }
		#loadWidgetForm .columnSM3 { width: 25%; }
	}
	@media (min-width:992px) {
		#loadWidgetForm .columnMD7 { width: 58.33333333%; }
		#loadWidgetForm .columnMD5 { width: 41.66666667%; }
	}
	#loadWidgetForm .flexStartRow, #loadWidgetForm .flexCenterRow, #loadWidgetForm .flexSpaBetRow { display: flex;flex-flow: row wrap; }
	#loadWidgetForm .flexStartRow { justify-content: flex-start; }
	#loadWidgetForm .flexCenterRow { justify-content: center; }
	#loadWidgetForm .flexSpaBetRow { justify-content: space-between; }
	#loadWidgetForm .container { max-width: 1170px;margin: 0 auto;padding: 0px 15px; }
	#loadWidgetForm .form-control { display: block;width: 100%;padding: 6px 12px;color: #555;border: 1px solid #ccc;border-radius: 4px;box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%); }
	#loadWidgetForm .form-control:focus { border-color: #66afe9;outline: 0;box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, 0.6); }
	#loadWidgetForm input.form-control, #loadWidgetForm select.form-control { height: 34px; }
	#loadWidgetForm .input-group { position: relative;display: table;border-collapse: separate;border: 1px solid #ccc;border-radius: 4px; }
	#loadWidgetForm .input-group .form-control { position: relative;width: 100%;border-right: 1px solid #ccc;border-radius: 4px; }
	#loadWidgetForm .input-group .form-control:first-child, #loadWidgetForm .input-group-addon:first-child { border-top-right-radius: 0;border-bottom-right-radius: 0; }
	#loadWidgetForm .input-group .form-control, #loadWidgetForm .input-group-addon { display: table-cell; }
	#loadWidgetForm .input-group-addon:last-child { border-left: 0; }
	#loadWidgetForm .input-group-addon { position: relative;padding: 0 12px;background-color: #eee;border-radius: 4px;width: 1%;white-space: nowrap;vertical-align: middle; }
	#loadWidgetForm .single_product { border: 1px solid #CCC;padding-left: 20px;background: #f6f5f5;height: 100%;position: relative; }
	#loadWidgetForm .cell_content { width: 60%;padding-right: 20px;position: relative;z-index: 1; }
	#loadWidgetForm .cell_content::before { content: '';background: url(${fullURL}/assets/images/image_sprites.png) #f6f5f5 no-repeat 0px -124px;position: absolute;left: -24.5px;width: 37px;height: 45px;transform: scale(0.8);top: 42px;z-index: -1; }
	#loadWidgetForm .single_product h4 { margin-top: 15px;font-weight: 600; }
	#loadWidgetForm .single_product h4 span { font-size: 13px;margin-top: 15px; }
	#loadWidgetForm .single_product h3 { color: #335DBB;font-size: 18px;text-align: left; }
	#loadWidgetForm .single_product h2 { border-bottom: 2px solid #335dbb;color: #545353;font-size: 20px;margin-top: 10px;padding-bottom: 10px;font-weight: bold; }
	#loadWidgetForm .single_product .inStockIcon { left: 20px;bottom: 20px;width: 90px;height: 30px; }
	#loadWidgetForm .cell_content_img { padding: 40px 0;background: #f6f5f5;position: relative }
	#loadWidgetForm .cell_content_img img { height: 138px;max-width: 150px;margin: 0 auto; }
	#loadWidgetForm .img-responsive { display: block;max-width: 100%;height: auto; }
	#loadWidgetForm label#fromtodata { padding-top: 10px;margin-left: 15px; }
	#loadWidgetForm #Pagination ul>li { display: inline; }
	#loadWidgetForm #Pagination ul>li:first-child>a { border-top-left-radius: 4px;border-bottom-left-radius: 4px; }
	#loadWidgetForm #Pagination ul>.disabled>a, #loadWidgetForm #Pagination ul>.disabled>span { color: #777;cursor: not-allowed;background-color: #fff;border-color: #ddd; }
	#loadWidgetForm #Pagination ul li.disabled.prev a, #loadWidgetForm #Pagination ul li.disabled.next a { background: #d0d0d0; }
	#loadWidgetForm #Pagination ul>.active>a { color: #fff;cursor: default;background-color: #337ab7;border-color: #337ab7; }
	#loadWidgetForm #Pagination ul>li>a, #loadWidgetForm #Pagination ul>li>span { position: relative;padding: 6px 12px;margin-left: -1px;color: #337ab7;text-decoration: none;background-color: #fff;border: 1px solid #ddd; }
	#loadWidgetForm #Pagination ul>li:last-child>a { border-top-right-radius: 4px;border-bottom-right-radius: 4px; }
	`;
	WidgetForm.appendChild(style);

	const widgetInfo = {
		services:{title:"Services",type:"Labor/Services"},
		product:{title:'Products',type:'Standard'},
		cellPhones:{title:'Live Stocks',type:'Live Stocks'}
	}
	const sproduct_type = {services:"Labor/Services",product:'Standard',cellPhones:'Live Stocks'};
	const header = {services:"Services",product:'Products',cellPhones:'Live Stocks'};

	const serviceSection = cTag('section');
		const serviceDiv = cTag('div',{ 'class':"container","style":"overflow: hidden" });
		[
			{ name_id: 'pageURI', value: 'Services' },
			{ name_id: 'page', value: 1 },
			{ name_id: 'totalTableRows', value: 1 },
		].forEach(item=>{
			serviceDiv.appendChild(cTag('input',{ 'type':"hidden",'name':item.name_id,'id':item.name_id,'value':item.value }));
		})
			const serviceRow = cTag('div',{ 'class':"flexSpaBetRow",'style':"border-bottom: 1px solid #ddd; margin-bottom: 20px; align-items: center;" });
				const serviceTitle = cTag('div',{ 'class':"columnXS12 columnSM3" });
					const serviceHeader = cTag('h2',{ 'id':'title','style':'margin:0px' });
					serviceHeader.innerHTML = Translate(widgetInfo[module].title);
				serviceTitle.appendChild(serviceHeader);
				serviceTitle.appendChild(cTag('input',{ 'type':"hidden",'name':"sproduct_type",'id':"sproduct_type",'value':widgetInfo[module].type }));
			serviceRow.appendChild(serviceTitle);
				const manufacturerColumn = cTag('div',{ 'class':"columnXS6 columnSM3"});					
				manufacturerColumn.appendChild(cTag('select',{ 'class':"form-control",'name':"smanufacturer_id",'id':"smanufacturer_id",'change':()=>filter(widgetInfo[module].type) }));
			serviceRow.appendChild(manufacturerColumn);
				const categoryColumn = cTag('div',{ 'class':"columnXS6 columnSM3"});
				categoryColumn.appendChild(cTag('select',{ 'class':"form-control",'name':"scategory_id",'id':"scategory_id",'change':()=>filter(widgetInfo[module].type) }));
			serviceRow.appendChild(categoryColumn);
				const searchColumn = cTag('div',{ 'class':"columnXS12 columnSM3"});
					const searchInput = cTag('div',{ 'class':"input-group" });
					searchInput.appendChild(cTag('input',{'keydown':listenToEnterKey(()=>filter(widgetInfo[module].type)),'type':"text",'placeholder':Translate('Search Products'),'value':"",'id':"keyword_search",'name':"keyword_search",'class':"form-control",'maxlength':"50" }));
						const searchSpan = cTag('span',{ 'class':"input-group-addon", 'style': "cursor: pointer;", 'click':()=>filter(widgetInfo[module].type),'data-toggle':"tooltip",'data-placement':"bottom",'title':"Search Products" });
						searchSpan.innerHTML = ` <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
							</svg>`;
					searchInput.appendChild(searchSpan);
				searchColumn.appendChild(searchInput);
			serviceRow.appendChild(searchColumn);
		serviceDiv.appendChild(serviceRow);
			const tableDiv = cTag('div',{ 'class':"columnSM12 flexCenterRow",'id':"tableRows", 'style': "padding-bottom: 20px;" });
		serviceDiv.appendChild(tableDiv);
			const paginationRow2 = cTag('div', {class: "flexSpaBetRow"});
				const pagination2Column = cTag('div', {class: "columnXS12 flexSpaBetRow"});
					const paginationDiv = cTag('div', {'style': "display: flex;"});
						const selectPagination = cTag('select', { class: "form-control", 'style': "width: 100px;", name: "limit", id: "limit", 'change':()=> loadTableRows(widgetInfo[module].type) });
						setOptions(selectPagination, [15, 20, 25, 50, 100, 500], 0, 0);
					paginationDiv.appendChild(selectPagination);
					paginationDiv.appendChild(cTag('label', {id: "fromtodata"}));
				pagination2Column.appendChild(paginationDiv);					
				pagination2Column.appendChild(cTag('div', { id: "Pagination"}));        
			paginationRow2.appendChild(pagination2Column);
		serviceDiv.appendChild(paginationRow2);		
	serviceSection.appendChild(serviceDiv);
	WidgetForm.appendChild(serviceSection);
	filter(widgetInfo[module].type);
}

async function view_popup(data){
	const section = cTag('section');
		const serviceDiv = cTag('div',{ 'class':"container" });
			const serviceField = cTag('div',{ 'class':"flexCenterRow" });					
				const imageColumn = cTag('div',{ 'class':"columnMD5",'style':'display:flex;align-items:center;justify-content:center;' });
				imageColumn.appendChild(cTag('img',{ 'class':"img-responsive",'src':fullURL+data.productSrc,'alt':data.prodImg }));
			serviceField.appendChild(imageColumn);
				const productName = cTag('div',{ 'style':"text-align: left; padding: 20px 0px 20px 40px; ",'class':"columnMD7 cell_content_img" });
					const productTitle = cTag('h2',{ 'style':"margin-bottom: 10px;" });
					productTitle.innerHTML = data.product_name;
				productName.appendChild(productTitle);
					if(data.display_prices === 1 && data.regular_price !== 0){
						const priceDiv = cTag('div',{ 'class':"columnSM12", 'style': "padding: 0px;" });
							const priceField = cTag('div',{ 'style': "font-size: 42px; font-weight: bold; text-align: left; color: #719b37;", 'align':"center" });
							priceField.innerHTML = data.currency+parseFloat(data.regular_price).toFixed(2);
						priceDiv.appendChild(priceField);
					productName.appendChild(priceDiv);
						const descriptionField = cTag('div',{ 'class':"columnSM12", 'style': "padding: 25px 0;" });
						descriptionField.innerHTML = data.description;
					productName.appendChild(descriptionField);
					if(data.enable_paypal === 1){
							const form = cTag('form',{ 'target':"paypal",'action':"http://www.paypal.com/cgi-bin/webscr",'method':"post" });
							form.appendChild(cTag('input',{ 'type':"hidden",'name':"business",'value':data.paypal_email }));
							form.appendChild(cTag('input',{ 'type':"hidden",'name':"cmd",'value':"_cart" }));
							form.appendChild(cTag('input',{ 'type':"hidden",'name':"add",'value':"1" }));
							form.appendChild(cTag('input',{ 'type':"hidden",'name':"item_name",'value':`${data.sku} - ${data.product_name}` }));
							form.appendChild(cTag('input',{ 'type':"hidden",'name':"amount",'value':data.regular_price }));
							form.appendChild(cTag('input',{ 'type':"hidden",'name':"currency_code",'value':data.currency_code }));
							form.appendChild(cTag('input',{ 'type':"image",'name':"submit",'src':"http://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif",'alt':"Add to Cart" }));
							form.appendChild(cTag('img',{ 'alt':"",'width':"1",'height':"1",'src':"http://www.paypalobjects.com/en_US/i/scr/pixel.gif" }));
						productName.appendChild(form);
					}
				}
			serviceField.appendChild(productName);
		serviceDiv.appendChild(serviceField);
	section.appendChild(serviceDiv);
	popup_dialog(data.product_name,section);
}

async function filter(type){	
	const jsonData = getFilterAttributes(true);	
	const smanufacturer_id = jsonData.smanufacturer_id;
	const scategory_id = jsonData.scategory_id;
	activeLoader();
	
	fetch(api_endpoint,{method:'POST',body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}})
	.then(response=>response.json()).then(afterFetch);

	function afterFetch(data){
		let select,option;
		select = document.getElementById("smanufacturer_id");
		select.innerHTML = '';
			option = cTag('option',{ 'value': '' });
			option.innerHTML= Translate('All Manufacturers');
		select.appendChild(option);
		setOptions(select, data.manOpt, 1, 1);
		if(smanufacturer_id !==''){
			select.value = smanufacturer_id;
		}
		
		select = document.getElementById("scategory_id");
		select.innerHTML = '';
			option = cTag('option',{ 'value': '' });
			option.innerHTML= Translate('All Categories');
		select.appendChild(option);
		setOptions(select, data.catOpt, 1, 1);
		if(scategory_id !==''){
			select.value = scategory_id;
		}
		
		document.getElementById("totalTableRows").value = data.totalRows;
		
		loadImages(data,type);

		onClickPagination();
		hideLoader();
	}
}

async function loadTableRows(type){
	const jsonData = getFilterAttributes(false);	
	activeLoader();

	fetch(api_endpoint,{method:'POST',body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}})
	.then(response=>response.json()).then(afterFetch);

	function afterFetch(data){
		loadImages(data,type);
		onClickPagination();
		hideLoader();
	}
}

function getFilterAttributes(forFilter){
	return {
		filter:forFilter?1:0,
		sproduct_type: document.getElementById('sproduct_type').value,
		smanufacturer_id: document.getElementById('smanufacturer_id').value,
		scategory_id: document.getElementById('scategory_id').value,
		keyword_search: document.getElementById('keyword_search').value,
		limit: parseInt(document.getElementById("limit").value),
		totalTableRows: parseInt(document.getElementById("totalTableRows").value),
		page: parseInt(document.getElementById("page").value)
	}
}

function loadImages(data,type){
	let divCell;
	const tableRowsContainer = document.querySelector('#tableRows');
	tableRowsContainer.innerHTML = '';
	if(type==="Standard" || type==="Labor/Services"){
		data.tabledata.forEach(imageItem=>{
			let viewData = {
				currency : data.currency,
				currency_code : data.currency_code,
				display_prices : data.display_prices,
				enable_paypal : data.enable_paypal,
				paypal_email : data.paypal_email,

				product_name : imageItem[3],
				prodImg : imageItem[5],
				productSrc : imageItem[6],
				sku : imageItem[10],
				description : imageItem[11],
				regular_price : imageItem[12],
			}
			let columnType = cTag('div',{ 'class':"columnSM4 columnXS6 super-xs" });
				let singleProductDiv = cTag('div',{ 'class':"single_product",'align':"center",'style':"min-height:250px;cursor:pointer" });
				singleProductDiv.addEventListener('click',()=>view_popup(viewData));
				if(imageItem[5] !== ''){
					singleProductDiv.appendChild(cTag('img',{ 'style':"max-width:100%; max-height:200px;",'src':fullURL+imageItem[6],'alt':imageItem[3] }));
				}
					const headerTitle = cTag('h3',{ 'style':"text-align:center; color:#333;" });
					headerTitle.innerHTML = imageItem[3];
				singleProductDiv.appendChild(headerTitle);
			columnType.appendChild(singleProductDiv);
		tableRowsContainer.appendChild(columnType);
		})
	}else{
		data.tabledata.forEach(item=>{
			let viewData = {
				currency : data.currency,
				currency_code : data.currency_code,
				display_prices : data.display_prices,
				enable_paypal : data.enable_paypal,
				paypal_email : data.paypal_email,

				product_name : item[3],
				prodImg : item[5],
				productSrc : item[6],
				sku : item[10],
				description : item[11],
				regular_price : item[12],
			}
			let contentDiv7 = cTag('div',{ 'class':"columnXS12 columnSM6 super-xs" });
				let singleProduct = cTag('div',{ 'class':"single_product",'style':'cursor:pointer' });
				singleProduct.addEventListener('click',()=>view_popup(viewData));
					divCell = cTag('div',{ 'class':'cell_content' });
						const itemHeader = cTag('h2');
						itemHeader.innerHTML = item[3];
					divCell.appendChild(itemHeader);
				singleProduct.appendChild(divCell);

					let titleDiv = cTag('div',{ 'class':"columnSM6 columnXS12" });;
					let titleDivRow = cTag('div',{ 'class':'flexStartRow columnSM12' });
						if(item[7] !== ''){							
								let capacityHeader = cTag('h4');
									let capacitySpan = cTag('span');
									capacitySpan.innerHTML = Translate('Capacity')+': ';
								capacityHeader.appendChild(capacitySpan);
								capacityHeader.append(item[7]);
							titleDiv.appendChild(capacityHeader);
						}
						if(item[8].length>0){
							let colorHeader = cTag('h4');
									let colorSpan = cTag('span');
									colorSpan.innerHTML = Translate('Color')+': ';
								colorHeader.appendChild(colorSpan)
								item[8].forEach(color=>{
									colorHeader.append(color);
								})
							titleDiv.appendChild(colorHeader);
						}
						if(Object.keys(item[9]).length>0){
								let gradeHeader = cTag('h4');
									let gradeSpan = cTag('span');
									gradeSpan.innerHTML = Translate('Grade')+': ';
								gradeHeader.appendChild(gradeSpan);
								let grades = [];
								Object.keys(item[9]).forEach(grade=>{
									grades.push(grade);
								})
								gradeHeader.append(grades.join(', '));
							titleDiv.appendChild(gradeHeader);
							titleDivRow.appendChild(titleDiv);
						}
				singleProduct.appendChild(titleDivRow);

					divCell = cTag('div',{ 'class':'columnXS12 columnSM6 cell_content_img' });
					if(item[5] !== ''){
						divCell.appendChild(cTag('img',{ 'src':fullURL+item[6],'alt':item[3] }))
					}
					titleDivRow.appendChild(divCell);
				singleProduct.appendChild(titleDivRow);

					const divIcon = cTag('div',{ 'class':'inStockIcon', 'style': "margin-bottom: 10px;" });
					divIcon.appendChild(cTag('img',{ 'class':'img-responsive','src':fullURL+'/assets/images/available_button.png','alt':'In Stock' }));
				singleProduct.appendChild(divIcon);
			contentDiv7.appendChild(singleProduct);
		tableRowsContainer.appendChild(contentDiv7);
		})
	}
}

//=========common-functions=========//
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

function stripslashes(str){
	str = str.replace(/\\'/g, '\'');
	str = str.replace(/\\"/g, '"');
	str = str.replace(/\\0/g, '\0');
	str = str.replace(/\\\\/g, '\\');
	return str;
}

function Translate(index){
	if(language != 'English'){
		if(langModifiedData !==undefined && langModifiedData[index] !==undefined){
			return langModifiedData[index];
		}
		else if(languageData !==undefined && languageData[index] !==undefined){
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
			
			const jsonData = {name: 'JS Translate issue: '+index, message: message+': '+language, url: document.location.href};
			const options = {method: "POST", body:JSON.stringify(jsonData), headers:{'Content-Type':'application/json'}};
			fetch('/Home/handleErr/', options);
		}
	}
	return index;
}

function frmSerialize(formID) {
	let form = document.querySelector(formID)
	if (!form || form.nodeName !== "FORM") {
		return;
	}
	let i, j, q = [], fields = [];
	for (i = 0; i < form.elements.length; i++) {
		if (form.elements[i].name === "") {
			continue;
		}
		switch (form.elements[i].nodeName) {
		case 'INPUT':
			switch (form.elements[i].type) {
			case 'text':
			case 'color':
			case 'email':
			case 'hidden':
			case 'password':
			case 'button':
			case 'reset':
			case 'submit':
				q.push(form.elements[i].name + "=" + form.elements[i].value);
				break;
			case 'checkbox':
			case 'radio':
				if (form.elements[i].checked) {
					q.push(form.elements[i].name + "=" + form.elements[i].value);
				}						
				break;
			case 'file':
				break;
			}
			break;			 
		case 'TEXTAREA':
			q.push(form.elements[i].name + "=" + form.elements[i].value);
			break;
		case 'SELECT':
			switch (form.elements[i].type) {
			case 'select-one':
				q.push(form.elements[i].name + "=" + form.elements[i].value);
				break;
			case 'select-multiple':
				for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
					if (form.elements[i].options[j].selected) {
						q.push(form.elements[i].name + "=" + form.elements[i].options[j].value);
					}
				}
				break;
			}
			break;
		case 'BUTTON':
			switch (form.elements[i].type) {
			case 'reset':
			case 'submit':
			case 'button':
				q.push(form.elements[i].name + "=" + form.elements[i].value);
				break;
			}
			break;
		}
	}

	let oneFieldSplit;
	q.forEach(oneFieldInfo=>{
		oneFieldSplit = oneFieldInfo.split('=');
		if(oneFieldSplit[0] !==''){
			fields.push(oneFieldSplit[0]);
		}
	});

	const fieldsGroup = fields.reduce(function (obj, item){
		if(!obj[item]){obj[item] = 0;}
		obj[item]++;
		return obj
	},{});

	let returnSerData = {};

	for (let key in fieldsGroup) {
		if (fieldsGroup.hasOwnProperty(key)) {
			let value = fieldsGroup[key];
			if(value===1){
				let fieldVal = '';
				q.forEach(oneFieldInfo=>{
					oneFieldSplit = oneFieldInfo.split('=');
					if(oneFieldSplit[0] ===key){
						fieldVal = oneFieldSplit[1];
					}
				});
				returnSerData[key] = fieldVal;
			}
			else{
				let fieldValues = [];
				q.forEach(oneFieldInfo=>{
					oneFieldSplit = oneFieldInfo.split('=');
					if(oneFieldSplit[0] ===key){
						fieldValues.push(oneFieldSplit[1]);
					}
				});
				returnSerData[key.replace('[]', '')] = fieldValues;
			}
		}
	}
	return returnSerData;
}

function mathCaptcha(){
	let numField, oprtrField;
	const Container = document.getElementById("mathCaptcha");
	Container.innerHTML = '';
		let style = document.createElement('style');
		style.innerHTML = `
			.bGreen {border: 2px solid green;outline: none;}
			.bRed {border: 2px solid red;outline: none;}
		`;
	Container.appendChild(style);
		let div = document.createElement('div');
		div.setAttribute('style'," width: 300px; float: left; margin: 0 15px 15px 0; font-size: 24px; font-weight: bold; text-align: center; border: 1px solid #ccc; background: linear-gradient(to right top, #eee, #ddd, #f6f6f6); padding: 15px; font-family: 'Brush Script MT', 'Brush Script Std', cursive; color: #000; ");
			numField = document.createElement('div');
			numField.setAttribute('id',"fNumber");
			numField.setAttribute('style',"width: 30px; float: left; margin: 0 5px");
		div.appendChild(numField);
			oprtrField = document.createElement('div');
			oprtrField.setAttribute('style',"width: 10px; float: left; margin: 0 5px");
			oprtrField.innerHTML = '+';
		div.appendChild(oprtrField);
			numField = document.createElement('div');
			numField.setAttribute('id',"lNumber");
			numField.setAttribute('style',"width: 30px; float: left; margin: 0 5px");
		div.appendChild(numField);
			oprtrField = document.createElement('div');
			oprtrField.setAttribute('style',"width: 20px; float: left; margin: 0 15px 0 5px");
			oprtrField.innerHTML = '=';
		div.appendChild(oprtrField);
			let input = document.createElement('input');
			input.addEventListener('keydown',event=>{
				if(event.key.length===1 && !/\d/.test(event.key)) event.preventDefault();
			})
			input.setAttribute('required',"");
			input.setAttribute('type',"text");
			input.setAttribute('name',"mathCaptcha");
			input.setAttribute('id',"resultN");
			input.setAttribute('value',"");
			input.setAttribute('style'," width: 80px; float: left; margin: 0 5px; padding: 5px; line-height: 20px;-webkit-appearance: none; ");
		div.appendChild(input);
			let reset = document.createElement('i');
			reset.addEventListener('click',mathCaptcha);
			reset.setAttribute('class'," fa fa-refresh ");
		div.appendChild(reset);
	Container.appendChild(div);
	
	
	let integer = Math.random() * 123456789;
	let fNumber = parseInt(integer.toString().substr(0, 1));
	if(isNaN(fNumber)){fNumber = 3;}
	let lNumber = parseInt(integer.toString().substr(3, 1));
	if(isNaN(lNumber)){lNumber = 7;}
	
	document.getElementById("fNumber").innerHTML = fNumber;
	document.getElementById("lNumber").innerHTML = lNumber;
}

function checkMathCaptcha(){
	let fNumber = parseInt(document.getElementById("fNumber").innerHTML);
	if(isNaN(fNumber)){fNumber = 0;}
	let lNumber = parseInt(document.getElementById("lNumber").innerHTML);
	if(isNaN(lNumber)){lNumber = 0;}
	let resultN = parseInt(document.getElementById("resultN").value);
	if(isNaN(resultN)){resultN = 0;}
	let expectedResult = parseInt(fNumber+lNumber);
	if(isNaN(expectedResult)){expectedResult = 0;}
	
	if(expectedResult===resultN){
		document.getElementById("resultN").classList.remove("bRed");
		document.getElementById("resultN").classList.add("bGreen");
		return 'Checked';
	}
	else{
		document.getElementById("resultN").classList.remove("bGreen");
		document.getElementById("resultN").classList.add("bRed");
		document.getElementById("resultN").focus();
		return false;
	}
}

function sanitizer(){
	const ContainsOnEventListener = /<[^>]*on\w+\s*=[^>]*>/gi.test(this.value);
    const ConstinsScriptTag = /<\/?\s*script/gi.test(this.value);    
    if(ContainsOnEventListener || ConstinsScriptTag){
        this.value = 'Potential malcious code found here...';
		this.focus();
    }
}


function onClickPagination(){	
	function goto(event){
		event.preventDefault();
		document.getElementById('page').value = this.getAttribute('data-page');
		filter();            
	}

    document.querySelector("#Pagination").innerHTML = '';

	let page, li, aTag, span;
	const total = document.querySelector("#totalTableRows").value;
	page = document.querySelector("#page").value;
	let limit = document.querySelector("#limit").value;
	if(limit===''){
		limit = 15;
	}
	const uri = document.querySelector('#pageURI').value;
	
	if(parseInt(total)===0 || limit===0 || limit==='' || isNaN(parseInt(limit))){
		document.querySelector('#fromtodata').innerHTML = '0-0/0';
		return document.createDocumentFragment();
	}
	const num_edge = 2;	
	const np = Math.ceil(total/limit);
	page = Math.floor(page);
	if(page>np){page = np;}
	const start1 = 1;
	let end1 = np;
	let start2 = 0;
	let end2 = 0;
	let start3 = 0;
	let end3 = 0
	if(np>num_edge){
		end1 = end2 = Math.floor(num_edge);
		if(np>Math.floor(end1+num_edge) && page>end1 && page<=parseInt(np-num_edge)){
			start2 = page;
			end2 = Math.floor(page+num_edge);
			if(Math.floor(page-1)>end1){
				start2 = Math.floor(page-1);
				end2 = Math.floor(page+1);
			}		
		}
		if(np>end2){
			start3 = end3 = np;
			if(Math.floor(np-num_edge)>=end2){
				start3 = Math.floor(np-num_edge+1);
			}
		}
	}
	
	let fromPag = Math.floor(Math.floor(Math.floor(page-1)*limit)+1);
	let toPag = Math.floor(page*limit);
	if(toPag>total){toPag = total;}
	if(fromPag>total){fromPag = 1;}
	
	document.querySelector('#fromtodata').innerHTML = (fromPag+'-'+toPag+'/'+total);
	
	const html = cTag('ul',{ 'class':'pagination' });
	if(page > 1){
            li = cTag('li',{ 'class':'prev' });
				aTag = cTag('a',{ href:'#','data-page':page-1 });
                aTag.innerHTML = '&laquo;';
				aTag.addEventListener('click',goto);
            li.appendChild(aTag)
        html.appendChild(li)
	}
	else{
            li = cTag('li',{ 'class':'disabled prev' });
				aTag = cTag('a',{ 'class':'disabled','href':'javascript:void(0)' });
                aTag.innerHTML = '&laquo;';
            li.appendChild(aTag)
        html.appendChild(li)
	}
	 
	for ( let i = start1; i <= end1; i++ ) {
            li = cTag('li');
            if(page===i) li.setAttribute('class','active');
				aTag = cTag('a',{ href:'#','data-page':i });
                aTag.innerHTML = i;
				aTag.addEventListener('click',goto);
            li.appendChild(aTag);
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
		for ( let i = start2; i <= end2; i++ ) {
                li = cTag('li');                
					aTag = cTag('a',{ href:'#','data-page':i });
					aTag.addEventListener('click',goto);
                    if(page===i){
						aTag.setAttribute('class','disabled');
                        li.setAttribute('class','active');
                    }
                    aTag.innerHTML = i;
                li.appendChild(aTag);
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
		for ( let i = start3; i <= end3; i++ ) {
                li = cTag('li');                
					aTag = cTag('a',{ href:'#','data-page':i });
					aTag.addEventListener('click',goto);
                    if(page===i){
						aTag.setAttribute('class','disabled');
                        li.setAttribute('class','active');
                    }
                    aTag.innerHTML = i;
                li.appendChild(aTag);
            html.appendChild(li);
		}
	}
	if(np>page){
			li = cTag('li',{ 'class':'next' });
				aTag = cTag('a',{ href:'#','data-page':page+1 });
                aTag.innerHTML = '&raquo;';
				aTag.addEventListener('click',goto);
            li.appendChild(aTag);
        html.appendChild(li);
	}
	else{
			li = cTag('li',{ 'class':'disabled next' });
				aTag = cTag('a',{ 'class':'disabled','href':'javascript:void(0)' });
                aTag.innerHTML = '&raquo;';
            li.appendChild(aTag);
        html.appendChild(li);
	}	

	document.querySelector("#Pagination").appendChild(html);
			
		
}

function listenToEnterKey(listener){
	return function(event){
		if(event.which===13) listener();
	}
}

function setOptions(Node, optionsData, object1_0, sort1_0){
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

function popup_dialog(title, popupContent){
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
			'width':'800px',
			'max-width':'90vw',
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
			'background': `url(${fullURL}/assets/images/image_sprites.png) no-repeat 0px -211px`
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
			'font-size': '15px',
			'padding': '7px 12px',
			'border': '1px solid transparent',
			'border-radius': '3px',
			'background-color': 'tomato',
			'color': 'white'
		}
   	}

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
			let header = cTag('header',{id:'popup_header'});
			setStyles(header,styles.popup_header);
				let headerTitle = cTag('h3');
				headerTitle.innerText = title;
				setStyles(headerTitle,{'margin':0,'font-size':'1.1em','font-weight':'bold'});
			header.appendChild(headerTitle);
				let closeBtn = cTag('button',{id:'popup_close_button'});
				closeBtn.addEventListener('click',()=>popup_container.remove());
				setStyles(closeBtn,styles.popup_close_button);
			header.appendChild(closeBtn)
		popup.appendChild(header);        
		popup.appendChild(popupContent);     
    popup_container.appendChild(popup);

		//popup-overlay
		let overlay = cTag('div',{'id':'popup_overlay'});
		setStyles(overlay,styles.overlay);
	popup_container.appendChild(overlay)
	WidgetForm.appendChild(popup_container);
	

	//add necessery functionality and styling on popupContent.....
	setStyles(popupContent,{'padding':'10px 15px','overflow':'auto'});
	if(popupContent.style.display === 'none'){
		popupContent.style.display = '';
	}
	makeDraggable(header);

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

	
	function setStyles(node,stylesObj){
		for (const property in stylesObj) {
			node.style[property] = stylesObj[property];
		}
	}

}

function date_picker_dialog(node,callback,calenderDate){ 
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
        if(node.value){
            if(calenderDate.toLowerCase()==='m/d/y' && new Date(node.value)!='Invalid Date'){
                let [mm,dd,yy] = node.value.split('/');
                selectedDate = {date:dd,month:mm,year:yy};
                mm = mm-1;
                [Month,Year] = [mm,yy*1];
            }
            else{
                let [dd,mm,yy] = node.value.split('-');
                if(new Date(`${mm}/${dd}/${yy}`)!='Invalid Date'){
                    selectedDate = {date:dd,month:mm,year:yy};
                    mm = mm-1;
                    [Month,Year] = [mm,yy*1];
                }
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

function activeLoader(){	
    if(document.getElementById('loaderOverlay')) return;
	let disScreen = cTag('div',{ 'style': 'display: flex;align-items: center;justify-content: center;opacity: .5;position: fixed;top: 0;left: 0;width: 100%;height: 100%;background: #fff;z-index:99999',id:'loaderOverlay' });
	disScreen.appendChild(cTag('img',{ 'src': fullURL+'/assets/images/ajax-loader.gif' }));
    document.body.appendChild(disScreen);
}
function hideLoader(){
    if(document.getElementById('loaderOverlay')) document.getElementById('loaderOverlay').remove();
}
