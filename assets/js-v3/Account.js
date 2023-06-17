import {
    cTag, Translate, checkAndSetLimit, storeSessionData, emailcheck, popup_dialog, popup_dialog600, checkNumericInputOnKeydown,fetchData,
    DBDateToViewDate, printbyurl, confirm_dialog, alert_dialog, showTopMessage, setOptions, addPaginationRowFlex, getDeviceOperatingSystem, listenToEnterKey,
    addCustomeEventListener, serialize, showthisurivalue, onClickPagination, controllNumericField, validateRequiredField, getCookie
} from './common.js';

if(segment2 ===''){segment2 = 'login';}

//==========================signup=========================//
function signup(){
    let Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    document.getElementById('sideBar').innerHTML = '';
	let pTag;
    let OUR_DOMAINNAME = extractRootDomain(window.location.hostname,0);
        let signUpSection = cTag('section', {'style': "background: #F5F5F5;"});
            const signUpRow = cTag('div',{ 'class':`flexCenterRow` });
				let signUpColumn = cTag('div',{ 'class':`columnXS12` });
                    let signUpBox = cTag('div', {'class':`signUpForm`});
                        const signUpForm = cTag('form',{ 'id':`frmsignup`,'class':`signUpField`,'action':`#`,'name':`frmsignup`,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
                        signUpForm.addEventListener('submit',checksignup1);
							let signUpName = cTag('div',{ 'align':`center` });
								let signUpHeaderTitle = cTag('h3', {id: 'COMPANYNAME'});
								signUpHeaderTitle.innerHTML = 'Software Trial Signup Page';
							signUpName.appendChild(signUpHeaderTitle);
								pTag = cTag('p',{ 'style':`font-weight:bold;`});
								pTag.innerHTML = 'No credit card required';
							signUpName.appendChild(pTag);
						signUpForm.appendChild(signUpName);
                        signUpForm.appendChild(cTag('div',{ 'class':`flexStartRow`,'id':`err_message` }));

                            let signUpFieldRow = cTag('div',{ 'id':`signup1row` });
                                let emailField = cTag('div',{ 'class':`flexCenterRow` });
                                emailField.appendChild(cTag('input',{ required: true, 'class':`signup`,'type':`email`,'name':`user_email`,'id':`user_email`,'placeholder':`Business email address`,'title':`Business email address`,'autocomplete':`off`,'value':``,'maxlength':`50` }));
                                emailField.appendChild(cTag('div',{ 'class':`errorFieldMessage` }));
							signUpFieldRow.appendChild(emailField);
                            signUpFieldRow.appendChild(cTag('div',{ 'class':`flexStartRow errorField`, 'style': "padding-top: 10px; padding-left: 30px;", 'id':`error_user_email` }));
                                let passwordField = cTag('div',{ 'class':`flexCenterRow` });
                                passwordField.appendChild(cTag('input',{ required: true, 'class':`signup`,'type':`password`,'name':`user_password`,'id':`user_password`,'placeholder':`Password (minimum 4 characters)`,'title':`Password (minimum 4 characters)`,'autocomplete':`off`,'value':``,'maxlength':`32` }));
                                passwordField.appendChild(cTag('div',{ 'class':`errorFieldMessage` }));
							signUpFieldRow.appendChild(passwordField);
                            signUpFieldRow.appendChild(cTag('div',{ 'class':`flexStartRow errorField`, 'style': "padding-top: 10px; padding-left: 30px", 'id':`error_user_password` }));
                                const getStartedButton = cTag('div',{ 'class':`flexCenterRow` });
                                    let inputField = cTag('input',{ 'type':`button`,'id':`btnsignup1`,'class':`signUpButton`,'value':`Get Started!`});
                                    inputField.addEventListener('click',checksignup1);
								getStartedButton.appendChild(inputField);
                            signUpFieldRow.appendChild(getStartedButton);
						signUpForm.appendChild(signUpFieldRow);

                            let signUpHiddenRow = cTag('div',{ 'id':`signup2row`, style:'display:none'});
                                let companyNameRow = cTag('div',{ 'class':`flexStartRow` });
                                companyNameRow.appendChild(cTag('input',{ required: true, 'class':`signup`, 'style': "margin-top: 10px;", 'type':`text`,'placeholder': 'Company Name','title':`Enter your Company Name`,'name':`company_name`,'id':`company_name`,'autocomplete':`off`,'value':``,'keyup':constructACname,'blur':checkDuplicateAC,'maxlength':`40` }));
                                companyNameRow.appendChild(cTag('div',{ 'class':`errorFieldMessage` }));
							signUpHiddenRow.appendChild(companyNameRow);
                            signUpHiddenRow.appendChild(cTag('div',{ 'class':`flexStartRow errorField`, 'id':`error_company_name` }));
                                let companySubDomainColumn = cTag('div',{ 'class':`columnXS12`, 'style': "padding-left: 0;" });
									let companySubDomainRow = cTag('div',{ 'class':`flex`, 'style': "align-items: center; text-align: left;" });
                                        let signUpCompany = cTag('div',{ 'class':`columnXS7`, 'style': "padding-left: 0;" });
                                        signUpCompany.appendChild(cTag('input',{ required: true, 'class':`signup`,'type':`text`,'name':`company_subdomain`,'id':`company_subdomain`,'placeholder':`Accounts Name`,'title':`Accounts Name`,'autocomplete':`off`,'value':``,'keyup':checkValidSubdomain,'size':`30`,'maxlength':`30`, 'style': "padding-left: 27px;" }));
									companySubDomainRow.appendChild(signUpCompany);
                                        let domainName = cTag('div',{ 'class':`columnXS5`, 'style': "position: relative;" });
                                            let bTag = cTag('b',{'id':`OUR_DOMAINNAME`, 'style': "font-size: 16px;" });
                                            bTag.innerHTML = '.'+OUR_DOMAINNAME;
										domainName.appendChild(bTag);
                                        domainName.appendChild(cTag('div',{ 'class':`errorFieldMessage errorfieldcompany_subdomain`, 'style': "top: -6px;" }));
									companySubDomainRow.appendChild(domainName);
								companySubDomainColumn.appendChild(companySubDomainRow);
							signUpHiddenRow.appendChild(companySubDomainColumn);
                            signUpHiddenRow.appendChild(cTag('div',{ 'class':`flexStartRow errorField`, 'id':`error_company_subdomain` }));
                                let userFirstNameRow = cTag('div',{ 'class':`flexStartRow` });
                                userFirstNameRow.appendChild(cTag('input',{ required: true, 'class':`signup`,'type':`text`,'name':`user_first_name`,'id':`user_first_name`,'placeholder':'First Name','title':'First Name','value':``,'size':`12`,'maxlength':`12` }));
							signUpHiddenRow.appendChild(userFirstNameRow);
								let userFirstNameError = cTag('div',{ 'class':`flexStartRow errorField`, 'style': "padding-top: 5px;", 'id':`error_user_first_name` });
                                userFirstNameError.appendChild(cTag('div',{ 'class':`errorFieldMessage errorfielduser_first_name` }));
							signUpHiddenRow.appendChild(userFirstNameError);
                                let phoneNoColumn = cTag('div',{ 'class':`columnSM7`, 'style': "padding-left: 0; text-align: left;" });
                                phoneNoColumn.appendChild(cTag('input',{ required: true, 'class':`signup input_box`,'type':`tel`,'name':`company_phone_no`,'id':`company_phone_no`,'placeholder':`Phone Number`,'title':`Phone Number`,'value':``,'size':`20`,'maxlength':`20`, 'style': "padding-left: 27px;" }));
							signUpHiddenRow.appendChild(phoneNoColumn);
								let phoneNoError = cTag('div',{ 'class':`flexStartRow errorField`, 'style': "padding-top: 5px;", 'id':`error_company_phone_no` });
                                phoneNoError.appendChild(cTag('div',{ 'class':`errorFieldMessage errorfielduser_company_phone_no` }));
							signUpHiddenRow.appendChild(phoneNoError);
                                let countryRow = cTag('div',{ 'class':`flexStartRow` });
                                    let selectCountry = cTag('select',{ 'class':`signup`,'name':"company_country_name",'id':"company_country_name", 'required': '' });
                                    selectCountry.addEventListener('change', function (){
                                        if (['Canada', 'United States'].includes(this.value)) {
                                            document.getElementById('taxInclusiveRow').style.display = 'none';
                                        }
                                        else {
                                            document.getElementById('taxInclusiveRow').style.display = '';
                                        }
                                    })
								countryRow.appendChild(selectCountry);
                                countryRow.appendChild(cTag('div',{ 'class':`errorFieldMessage` }));
							signUpHiddenRow.appendChild(countryRow);
                            signUpHiddenRow.appendChild(cTag('div',{ 'class':`flexStartRow errorField`, 'style': "padding-top: 10px;", 'id':`error_company_country_name` }));
                                let taxRow = cTag('div',{'style': "padding-top: 10px; text-align: left;", 'id':`error_company_country_name` });
									pTag = cTag('p',{ 'style':`font-size:18px; font-weight:bold` });
                                    pTag.innerHTML = 'Do you charge sales tax?';
								taxRow.appendChild(pTag);
							signUpHiddenRow.appendChild(taxRow);
								let percentTaxRow = cTag('div',{ 'class':`flex`, 'style': "align-items: center; text-align: left;" });
									let percentTaxTitle = cTag('div',{ 'class':`columnSM5` });
										let percentTaxLabel = cTag('label',{ 'for':`taxes_percentage`,'style':`padding-left:15px; cursor:pointer;` });
										percentTaxLabel.innerHTML = 'Percent Sales Tax';
									percentTaxTitle.appendChild(percentTaxLabel);
								percentTaxRow.appendChild(percentTaxTitle);
									let percentTaxField = cTag('div',{ 'class':`columnSM7` });
										let taxInputField = cTag('input',{ 'class':`signup input_box`,'type': "text",'data-min':'0','data-max':'99.999','data-format':'d.ddd','name':`taxes_percentage`,'id':`taxes_percentage`,'value':`0.000` });
										controllNumericField(taxInputField, '#error_taxes_percentage');
									percentTaxField.appendChild(taxInputField);
								percentTaxRow.appendChild(percentTaxField);
							signUpHiddenRow.appendChild(percentTaxRow);
                            signUpHiddenRow.appendChild(cTag('div',{ 'class':`flexCenterRow errorField`, 'id':`error_taxes_percentage` }));

								let taxNameRow = cTag('div',{ 'class':`flex`, 'style': "align-items: center; text-align: left;" });
									let taxNameTitle = cTag('div',{ 'class':`columnSM5` });
										let taxNameLabel = cTag('label',{ 'for':`taxes_name`,'style':`padding-left:15px; cursor:pointer;` });
										taxNameLabel.innerHTML = 'Tax Name';
									taxNameTitle.appendChild(taxNameLabel);
								taxNameRow.appendChild(taxNameTitle);
									let taxNameField = cTag('div',{ 'class':`columnSM7` });
									taxNameField.appendChild(cTag('input',{ 'class':`signup input_box`,'type':`text`,'name':`taxes_name`,'id':`taxes_name`,'value': 'Sales Tax','size':`20`,'maxlength':`20` }));
								taxNameRow.appendChild(taxNameField);
							signUpHiddenRow.appendChild(taxNameRow);
                            signUpHiddenRow.appendChild(cTag('div',{ 'class':`flexCenterRow errorField`, 'id':`error_taxes_name` }));
								let taxInclusiveRow = cTag('div',{ 'class':`flex`, 'id': 'taxInclusiveRow', 'style': "text-align: left;" });
									let taxInclusiveTitle = cTag('div',{ 'class':`columnSM5` });
										let taxInclusiveLabel = cTag('label',{ 'for':`tax_inclusive`,'style':`padding-left:15px;cursor:pointer;` });
										taxInclusiveLabel.innerHTML = 'Inclusive';
									taxInclusiveTitle.appendChild(taxInclusiveLabel);
								taxInclusiveRow.appendChild(taxInclusiveTitle);
									let taxInclusiveValue = cTag('div',{ 'class':`columnSM7` });
										let inclusiveLabel = cTag('label',{ 'style':`cursor:pointer` });
										inclusiveLabel.appendChild(cTag('input',{ 'type':`checkbox`,'name':`tax_inclusive`,'id':`tax_inclusive`,'value':`1` }));
										inclusiveLabel.append(' (Tax is included in price)');
									taxInclusiveValue.appendChild(inclusiveLabel);
								taxInclusiveRow.appendChild(taxInclusiveValue);
							signUpHiddenRow.appendChild(taxInclusiveRow);
                            signUpHiddenRow.appendChild(cTag('div',{ 'class':`flexStartRow errorField`, 'style': "padding-top: 10px;", 'id':`error_tax_inclusive` }));
                                let buttonName = cTag('div',{ 'class':`flexCenterRow` });

                                let coupon_code = segment3;
                                let source = getCookie('source');
                                if (source != "") {coupon_code = source;}
                                if(coupon_code=== undefined){coupon_code = '';}
                                buttonName.appendChild(cTag('input',{ 'type':`hidden`,'name':`coupon_code`,'id':'coupon_code','value': coupon_code }));
                                
                                buttonName.appendChild(cTag('input',{ 'type':`submit`,'id':`btnsignup2`,'class':`signUpButton`,'value':`Create FREE Account` }));
							signUpHiddenRow.appendChild(buttonName);
						signUpForm.appendChild(signUpHiddenRow);

                            let loginRow = cTag('div',{ 'class':`flexStartRow` });
                                let loginLink = cTag('a',{ 'href':`/Account/login`,'title':`Have a login? Click Here`, 'style': "margin: 10px;" });
                                loginLink.innerHTML = 'Have a login? Click Here';
							loginRow.appendChild(loginLink);
						signUpForm.appendChild(loginRow);
					signUpBox.appendChild(signUpForm);
				signUpColumn.appendChild(signUpBox);
			signUpRow.appendChild(signUpColumn);
		signUpSection.appendChild(signUpRow);		
    Dashboard.appendChild(signUpSection);
    AJ_signup_MoreInfo()
}

async function AJ_signup_MoreInfo(){
    const url = '/'+segment1+'/AJ_signup_MoreInfo';

    fetchData(afterFetch,url,{});

    function afterFetch(data){
        let select = document.querySelector("#company_country_name");
        select.innerHTML = '';
        let option = cTag('option', {value:''});
        option.innerHTML = 'Country';
        select.appendChild(option);
        setOptions(select,data.comCouNamOpt, 0, 0);
        let COMPANYNAME = data.COMPANYNAME;
        let pageTitle = document.getElementById("COMPANYNAME");
        pageTitle.innerHTML = COMPANYNAME+' '+pageTitle.innerHTML;
    }
}

function constructACname(){
    let str = document.getElementById("company_name").value;
	let namevalue = str.trim();
	let errorid = document.getElementById('error_company_subdomain');
	errorid.innerHTML = '';							
	
	if(namevalue !==''){
		let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-&";
		let IsValid=true;
		let Char;
		let subdomain = '';
		
		for (let i = 0; i < namevalue.length; i++){ 
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
		for (let i = 0; i < subdomain.length && IsValid === true; i++){ 
			Char = subdomain.charAt(i); 
			if (ValidChars.indexOf(Char) === -1){}
			else{
				subdomainvalue = subdomainvalue+Char;
			}
		}
		subdomainvalue = subdomainvalue.substring(0,30);
		let lastChar = subdomainvalue.charAt(subdomainvalue.length-1);
		if(lastChar==='-'){
			subdomainvalue = subdomainvalue.slice(0, -1);
		}
		
		document.getElementById("company_subdomain").value = subdomainvalue.toLowerCase();							
	}
}	

function checkDuplicateAC(){
    let str = document.getElementById("company_subdomain").value;
	let namevalue = str.trim();
	let errorid = document.getElementById('error_company_subdomain');
	if(namevalue.length<5){								
		let error = document.querySelector('.errorfieldcompany_subdomain')
		error.classList.remove('successIcon');
		error.classList.add('errorIcon');
		errorid.innerHTML = 'Sub-domain should be minimum 5 characters';
		error.focus();
		return false;
	}
	else if(namevalue === 'www'){								
		document.querySelector('.errorfieldcompany_subdomain').classList.remove('successIcon');
		document.querySelector('.errorfieldcompany_subdomain').classList.add('errorIcon');
		errorid.innerHTML = 'Sub-domain should not be www';
		this.focus();
		return false;
	}
	else{
		checkSubDomainExists(namevalue, errorid);
	}						
}

function checkValidSubdomain(){
	let namevalue = document.getElementById("company_subdomain").value;
	let errorid = document.getElementById('error_company_subdomain');
	errorid.innerHTML = '';							
	
	let i;
	if(namevalue !==''){
		let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-&";
		let IsValid = true;
		let Char;
		let subdomain = '';
		for ( i = 0; i < namevalue.length; i++){ 
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
		subdomain = subdomain.replace('__', '_');
		let subdomainvalue = '';
		for ( i = 0; i < subdomain.length && IsValid === true; i++){ 
			Char = subdomain.charAt(i); 
			if (ValidChars.indexOf(Char) === -1){
				IsValid = false;
				subdomainvalue = subdomainvalue.substring(0,30);
				document.getElementById("company_subdomain").value = subdomainvalue.toLowerCase();
				
				document.querySelector('.errorfieldcompany_subdomain').classList.remove('successIcon');
				document.querySelector('.errorfieldcompany_subdomain').classList.add('errorIcon');
				errorid.innerHTML = 'Invalid sub-domain';
				document.getElementById("company_subdomain").focus();
				return false;
			}
			else{
				subdomainvalue = subdomainvalue+Char;
			}
		}
		
		subdomainvalue = subdomainvalue.substring(0,30);
		
		if(IsValid === false){                                    
			document.getElementById("company_subdomain").value = subdomainvalue.toLowerCase();
			return false;
		}
		else{
			document.getElementById("company_subdomain").value = subdomainvalue.toLowerCase();
			return true;
		}
	}
}	

async function checkSubDomainExists(namevalue, errorid){
	const jsonData = {};
	jsonData['company_subdomain'] = namevalue;
    const url = '/Account/signupCheck';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr !=='OK'){
			document.querySelector('.errorfieldcompany_subdomain').classList.remove('successIcon');
			document.querySelector('.errorfieldcompany_subdomain').classList.add('errorIcon');
			errorid.innerHTML = data.returnStr;
			return false;
		}
		else{
			document.querySelector('.errorfieldcompany_subdomain').classList.remove('errorIcon');	
			document.querySelector('.errorfieldcompany_subdomain').classList.add('successIcon');
			return true;
		}
	}
	return false;
}

function validSubDomainCharacter(text){
	let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-";
	let IsNumber = true;
	let Char;
	for (let i = 0; i < text.length && IsNumber === true; i++){ 
		Char = text.charAt(i); 
		if (ValidChars.indexOf(Char) === -1){
			IsNumber = false;
		}
	}
	return IsNumber;
}

function validCompanyNameCharacter(text){
	let ValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,-_ &";
	let IsNumber = true;
	let Char;
	for (let i = 0; i < text.length && IsNumber === true; i++){ 
		Char = text.charAt(i); 
		if (ValidChars.indexOf(Char) === -1){
			IsNumber = false;
		}
	}
	return IsNumber;
}

function checksignup1(){
	let namevalue, errorid;
	namevalue = document.getElementById("user_email").value;
    
	errorid = document.getElementById('error_user_email');
	errorid.innerHTML = '';	

	if(namevalue.length<1){
		errorid.innerHTML = 'Email is required';
		document.getElementById("user_email").focus();
		return false;
	}
	else if(emailcheck(namevalue) ===false){
		errorid.innerHTML = 'Invalid email';
		document.getElementById("user_email").focus();
		return false;
	}
	
	namevalue = document.getElementById("user_password").value;
	errorid = document.getElementById('error_user_password');
	errorid.innerHTML = '';	
		
	if(namevalue.length<4){		
		errorid.innerHTML = 'Password should be minimum 4 characters';
		document.getElementById("user_password").focus();
		return false;
	}							
	
	let elem = document.getElementById("signup1row");
	if(elem.style.display !== 'none'){
		elem.style.display = 'none';
	}
	
	let element = document.getElementById("signup2row");
	if(element.style.display === 'none'){
		element.style.display = '';
	}

    let form = document.getElementById("frmsignup");
    form.addEventListener('submit', checksignup2);
	
	return false;
}

async function checksignup2(event){
	if(event){ event.preventDefault();}

	let namevalue, errorid;
	namevalue = document.getElementById("company_name").value;
	errorid = document.getElementById('error_company_name');
	errorid.innerHTML = '';
	if(validCompanyNameCharacter(namevalue) ===false){									
		errorid.innerHTML = 'Invalid company name';
		document.getElementById("company_name").focus();
		return false;
	}
	else if(namevalue.length<1){								
		errorid.innerHTML = 'Company is required';
		document.getElementById("company_name").focus();
		return false;
	}
	
	namevalue = document.getElementById("company_subdomain").value;
	let lastChar = namevalue.charAt(namevalue.length-1);
	if(lastChar==='-'){
		namevalue = namevalue.slice(0, -1);
	}
	
	errorid = document.getElementById('error_company_subdomain');
	errorid.innerHTML = '';
	
	if(namevalue.length<5){
		document.querySelector('.errorfieldcompany_subdomain').classList.remove('successIcon');
		document.querySelector('.errorfieldcompany_subdomain').classList.add('errorIcon');
		errorid.innerHTML = 'Sub-domain should be minimum 5 characters';
		document.getElementById("company_subdomain").focus();
		return false;
	}
	else if(namevalue === 'www'){
		document.querySelector('.errorfieldcompany_subdomain').classList.remove('successIcon');
		document.querySelector('.errorfieldcompany_subdomain').classList.add('errorIcon');
		errorid.innerHTML = 'Sub-domain should not be www';
		this.focus();
		return false;
	}
	else{
		document.getElementById("company_subdomain").value = namevalue;
		if(checkSubDomainExists(namevalue, errorid)===false){
			return false;
		}
	}
	
	let company_subdomain = namevalue;
	namevalue = document.getElementById("user_first_name").value;
	errorid = document.getElementById('error_user_first_name');
	errorid.innerHTML = '';
	if(namevalue.length<2){		
		errorid.innerHTML = 'First Name is required';
		document.getElementById("user_first_name").focus();
		return false;
	}
						   
	namevalue = document.getElementById("company_country_name").value;
	errorid = document.getElementById('error_company_country_name');
		
	if(namevalue===0){
		errorid.innerHTML = 'Missing Country';
		document.getElementById("company_country_name").focus();
		return false;
	}

    let taxes_percentage = document.getElementById("taxes_percentage");
    if(!validateRequiredField(taxes_percentage,'#error_taxes_percentage') || !taxes_percentage.valid()) return;

    errorid = document.getElementById('error_taxes_name');
    errorid.innerHTML = '';	
    namevalue = document.getElementById("taxes_name").value;
    if(namevalue===''){
        errorid.innerHTML = 'Taxes name missing.';
        document.getElementById("taxes_name").focus();
        return false;
    }
	
	/* if(document.getElementById("taxes_percentage").value !=='0.00'){
		errorid = document.getElementById('error_taxes_percentage');
		errorid.innerHTML = '';	
		namevalue = parseFloat(document.getElementById("taxes_percentage").value);
		if(isNaN(namevalue)){
			errorid.innerHTML = 'Invalid taxes percentage.';	
			document.getElementById("taxes_percentage").value = '';
			document.getElementById("taxes_percentage").focus();
			return false;
		}
		else if(namevalue>=100){
			errorid.innerHTML = 'Maximum taxes percentage is 99.999.';	
			document.getElementById("taxes_percentage").value = '';
			document.getElementById("taxes_percentage").focus();
			return false;
		}
		
		
	} */
									
	let btn = document.getElementById("btnsignup2");
    btn.value = 'Creating accounts...';
    btn.disabled =  true;

    const jsonData = serialize('#frmsignup');
    const url = '/Account/savesignup';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){		
		if(data.returnStr === 'Success'){
			let OUR_DOMAINNAME = extractRootDomain(window.location.hostname,0);
			window.location = 'http://'+company_subdomain+'.'+OUR_DOMAINNAME+'/Account/login/signup-success';
		}
		else{
			let btnsignup2 = document.getElementById("btnsignup2")
            btnsignup2.value = 'Create accounts';
            btnsignup2.disabled =  false;
		}
    }
}

//===========================login=========================//

function login(){
    let label, spanTag, inputGroup, inputField, successDiv, errorDiv, aTag;
    const queryString = location.search;
    const params = new URLSearchParams(queryString);
    const msg = params.get("msg");

    const subdomain = SUBDOMAIN;

    document.getElementById('sideBar').innerHTML = '';

    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const logInContainer = cTag('div',{class:"flexCenterRow"});
            let emptySide = cTag('div',{id: 'sideclass'});
            emptySide.innerHTML = '&nbsp';
        logInContainer.appendChild(emptySide);
            const middleColumn = cTag('div',{class:'columnMD6', id: 'middleclass'});
                const calloutDiv = cTag('div',{class:"innerContainer"});
                    inputField =  cTag('input',{type:"hidden", id:"OUR_DOMAINNAME", value: OUR_DOMAINNAME});
                calloutDiv.appendChild(inputField);
                    //========= Here $successMsg =========//
                    if(segment3 === 'signup-success'){
                        let signUpColumn = cTag('div',{ 'class':`columnXS12` });
                            successDiv = cTag('div',{ 'class':`innerContainer success_msg` });
                            successDiv.append('Congratulations! You are successfully registered', cTag('br'), 'Please login below to begin.');
                        signUpColumn.appendChild(successDiv);
                        calloutDiv.appendChild(signUpColumn);
                    }
                    else if(segment3 === 'sent-success'){
                        let sentColumn = cTag('div',{ 'class':`columnXS12` });
                            successDiv = cTag('div',{ 'class':`innerContainer success_msg` });
                            successDiv.append('You have been sent a email to confirm this request.', cTag('br'), 'Check your email and click the enclosed link to change your password.');
                        sentColumn.appendChild(successDiv);
                        calloutDiv.appendChild(sentColumn);
                    }
                    else if(segment3 === 'password-saved'){
                        let passwordColumn = cTag('div',{ 'class':`columnXS12` });
                            successDiv = cTag('div',{ 'class':`innerContainer success_msg` });
                            successDiv.append('Your new password saved successfully.');
                            successDiv.appendChild(cTag('br'));
                            successDiv.append('Check your new password.');
                        passwordColumn.appendChild(successDiv);
                        calloutDiv.appendChild(passwordColumn);
                    }
                    else if(segment3 === 'duplicated_user'){
                        const duplicateUserRow = cTag('div',{ 'class':`columnXS12` });
                            errorDiv = cTag('div',{ 'class':`innerContainer error_msg` });
                            errorDiv.innerHTML = 'Another user has logged in with your email address and has logged you out.';
                        duplicateUserRow.appendChild(errorDiv);
                        calloutDiv.appendChild(duplicateUserRow);
                    }
                    else if(msg !== null){
                        let messageRow;
                        if(msg==='Please check your email for a message from us'){
                            messageRow = cTag('div',{ 'class':`columnXS12` });
                                successDiv = cTag('div',{ 'class':`innerContainer success_msg` });
                                successDiv.innerHTML = msg;
                            messageRow.appendChild(successDiv);
                        }
                        else{
                            messageRow = cTag('div',{ 'class':`columnXS12` });
                                errorDiv = cTag('div',{ 'class':`innerContainer error_msg` });
                                errorDiv.innerHTML = msg;
                            messageRow.appendChild(errorDiv);
                        }
                        calloutDiv.appendChild(messageRow);
                    }
                    errorDiv = cTag('div',{class:"columnXS12", id:"err_message"});
                calloutDiv.appendChild(errorDiv);

                    const logInForm = cTag('form',{action:"/Account/check", name:"frmlogin", id:"frmlogin", class:"formfield", method:"post", enctype:"multipart/form-data"});
                        const logInRow = cTag('div',{class:'flex'});
                            const logInColumn = cTag('div',{class:'columnXS12', id: 'class1', 'style': "padding: 6px 10px;"})
                                let logHeader = cTag('h3',{class:'borderbottom', id: 'class2'});
                                logHeader.innerHTML = 'Login into POS ERP';
                            logInColumn.appendChild(logHeader)
                                let ipHead = cTag('h4',{id: 'IP_Address', 'style': "margin-top: 8px;"});
                                ipHead.innerHTML = 'IP Address: 0.0.0.0';
                            logInColumn.appendChild(ipHead);
                        logInRow.appendChild(logInColumn);
                                let subDomainRow = cTag('div');
                                if(['www', ''].includes(subdomain) === false){
                                        let subDomainColumn = cTag('div',{'style': "padding-top: 8px;"});
                                            const SubdomainLabel = cTag('label',{for:"company_subdomain"});
                                            SubdomainLabel.innerHTML = subdomain +'.'+ OUR_DOMAINNAME;
                                            const WorStationLabel = cTag('label',{id:"workstationsName"});
                                            WorStationLabel.innerHTML = 'Work-station: Unknown';
                                        subDomainColumn.append(SubdomainLabel,cTag('br'),WorStationLabel);
                                    subDomainRow.appendChild(subDomainColumn);

                                        subDomainColumn = cTag('div',{'style': "padding-top: 8px;"});
                                            let select = cTag('select', {class: "form-control", required:'required', name: "company_subdomain", id: "company_subdomain"});
                                                let option = cTag('option',{ 'value':''});
                                                option.innerHTML = 'Select Branch Name';
                                            select.appendChild(option);
                                        subDomainColumn.appendChild(select);
                                        subDomainColumn.appendChild(cTag('span',{ 'id':`errmsg_company_subdomain`,'class':`errormsg` }));
                                    subDomainRow.appendChild(subDomainColumn);

                                }
                                else{
                                        let companySubdomain = cTag('div',{class:"columnXS8  columnSM6"});
                                        if(getDeviceOperatingSystem()==='unknown'){
                                            inputField = cTag('input',{type:"text", name:"company_subdomain", id:"company_subdomain", placeholder:"Sub-Domain Name", class:"form-control", autocomplete:"off", value:"", required:true, maxlength:"30"});
                                            inputField.addEventListener('keydown', (event)=>{if(event.which === 13){redirectToSubDomain('company_subdomain');}});
                                            companySubdomain.append(inputField);
                                        }
                                        else{
                                            inputGroup = cTag('div',{ 'class': 'input-group' });
                                                inputField = cTag('input',{type:"text", name:"company_subdomain", id:"company_subdomain", placeholder:"Sub-Domain Name", class:"form-control", autocomplete:"off", value:"", required:true, maxlength:"30"});
                                                inputField.addEventListener('keydown', (event)=>{if(event.which === 13){redirectToSubDomain('company_subdomain');}});
                                            inputGroup.appendChild(inputField);
                                                spanTag = cTag('span', {'data-toggle':'tooltip', 'class':'input-group-addon cursor', 'title': 'Click for Enter'});
                                                    let turnDownIcon = cTag('i', {'class':'fa fa-turn-down-left'});
                                                spanTag.appendChild(turnDownIcon);
                                                spanTag.addEventListener('click', function(){redirectToSubDomain('company_subdomain');});
                                            inputGroup.appendChild(spanTag);
                                            companySubdomain.append(inputGroup);
                                        }
                                    subDomainRow.appendChild(companySubdomain);
                                        const subDomainColumn = cTag('div',{class:"columnXS4 columnSM6"});
                                            let subDomainLabel = cTag('label',{for:"company_subdomain"});
                                            subDomainLabel.innerHTML = OUR_DOMAINNAME;
                                        subDomainColumn.appendChild(subDomainLabel);
                                    subDomainRow.appendChild(subDomainColumn);
                                } 
                                    errorDiv = cTag('span',{ 'id':`errmsg_company_subdomain`,'class':`errormsg` });
                                subDomainRow.appendChild(errorDiv);
                            logInColumn.appendChild(subDomainRow);

                                let userEmailRow = cTag('div');
                                    if(['www', ''].includes(subdomain)){userEmailRow.setAttribute('style', 'display:none')}
                                    let userEmailColumn = cTag('div',{  'style': "padding-top: 15px;" });
                                        inputField = cTag('input',{ 'maxlength':`100`,'type':`text`,'name':`user_email`,'id':`user_email`,'class':`form-control`,'placeholder':Translate('Email Address'),'autocomplete':`off`,'value':`` });
                                        inputField.addEventListener('keydown',event=>{
                                            if(event.which===13) checkloginId();
                                        })
                                        if(['www', ''].includes(subdomain)===false){inputField.setAttribute('required', 'required')}
                                    userEmailColumn.appendChild(inputField);
                                    userEmailColumn.appendChild(cTag('span',{ 'id':`errmsg_user_email`,'class':`errormsg` }));
                                userEmailRow.appendChild(userEmailColumn);
                            logInColumn.appendChild(userEmailRow);
                            
                                let passwordRow = cTag('div');
                                    if(['www', ''].includes(subdomain)){passwordRow.setAttribute('style', 'display:none')}
                                    let passwordColumn = cTag('div',{ 'style': "padding-top: 15px;" });
                                        inputField = cTag('input',{ 'minlength':`4`,'maxlength':`32`,'type':`password`,'name':`user_password`,'id':`user_password`,'class':`form-control`,'placeholder':Translate('Password'),'autocomplete':`off`,'value':`` });
                                        inputField.addEventListener('keydown',event=>{
                                            if(event.which===13) checkloginId();
                                        })
                                        if(['www', ''].includes(subdomain)===false){inputField.setAttribute('required', 'required');}
                                    passwordColumn.appendChild(inputField);
                                passwordRow.appendChild(passwordColumn);
                                passwordRow.appendChild(cTag('span',{ 'id':`errmsg_user_password`,'class':`errormsg` }));
                            logInColumn.appendChild(passwordRow);

                                let buttonRow = cTag('div');
                                    let buttonColumn = cTag('div',{ 'style': "padding-top: 15px;" });
                                        inputField = cTag('input',{ 'type':`button`,'id':`btnsubmit`,'class':`submitButton`,'value': ''});
                                        if(['www', ''].includes(subdomain) === false){
                                            inputField.addEventListener('click',checkloginId);
                                            inputField.value = 'Login';
                                        }
                                        else{
                                            inputField.addEventListener('click', function (){redirectToSubDomain('company_subdomain');});
                                            inputField.value = 'Go to Company Sub-Domain';
                                        }
                                    buttonColumn.appendChild(inputField);
                                buttonRow.appendChild(buttonColumn);
                            logInColumn.appendChild(buttonRow);

                                let noAccountRow = cTag('div');
                                    let noAccountColumn = cTag('div',{ 'class':`flexSpaBetRow`, 'style': "padding-top: 15px;" });
                                        aTag = cTag('a',{ 'href':`/Account/forgotpassword`,'title':`Forgot password` });
                                        aTag.innerHTML = 'Forgot Password?';
                                    noAccountColumn.appendChild(aTag);
                                        aTag = cTag('a',{ 'href':`/Account/signup`,'title':`No accounts Yet? Click Here` });
                                        aTag.innerHTML = 'No accounts Yet? Click Here';
                                    noAccountColumn.appendChild(aTag);
                                noAccountRow.appendChild(noAccountColumn);
                            logInColumn.appendChild(noAccountRow);                            
                        logInRow.appendChild(logInColumn);                            
                    logInForm.appendChild(logInRow);
                calloutDiv.appendChild(logInForm)
            middleColumn.appendChild(calloutDiv);
        logInContainer.appendChild(middleColumn);
    Dashboard.appendChild(logInContainer);
    AJ_login_MoreInfo()
}

async function AJ_login_MoreInfo(){
    const url = '/'+segment1+'/AJ_login_MoreInfo';
    fetchData(afterFetch,url,{});

    function afterFetch(data){
        const title = data.title;
        const REMOTE_ADDR = data.REMOTE_ADDR;
        const message =  document.getElementById('message');

        if(data.login_message.length>10){
            let logMessage = cTag('div',{ 'class':`columnSM6`, id: 'login_Messages', 'style': "padding: 6px 10px;"});
                let logMessageColumn = cTag('div');
                    const logMessageHeader = cTag('h3');
                    logMessageHeader.innerHTML = 'Login Messages';
                logMessageColumn.appendChild(logMessageHeader);
            logMessage.appendChild(logMessageColumn);
                let loginMessage = cTag('div',{ 'class':`innerContainer columnSM12`, id: 'login_Messages1' });
                    const pTag = cTag('p',{ 'align':`left`, id: 'message' });
                    pTag.innerHTML = data.login_message;
                loginMessage.appendChild(pTag);
            logMessage.appendChild(loginMessage);
            document.querySelector('#frmlogin div').appendChild(logMessage);

            document.getElementById('sideclass').setAttribute('class','columnSM12 columnMD2');
            document.getElementById('middleclass').setAttribute('class','columnSM12 columnMD8');
            document.getElementById('class1').setAttribute('class','columnXS12 columnSM6');
            document.getElementById('class1').setAttribute('style', "border-right: 1px solid #CCC; padding-left: 0;");
            document.getElementById('class2').setAttribute('class','borderbottom' );
            document.getElementById('class2').setAttribute('style', "margin-top: 10px;" );
            document.getElementById('class2').innerHTML = title;
        }
        const workstationsName = document.getElementById('workstationsName');
        if(workstationsName) workstationsName.innerHTML = 'Work-station: '+data.workstationsName;

        const subDomain = document.getElementById('company_subdomain');
        setOptions(subDomain,data.subDomainOpts, 0, 0);
        if(subDomain) subDomain.value = data.subDomain;
        
        document.getElementById('IP_Address').innerHTML = 'IP Address: '+ REMOTE_ADDR
    }
}

function extractRootDomain(url, value) {
    const domain = url;
    const val = value;
    const splitArr = domain.split('.');
    const arrLen = splitArr.length;

    if (val === 1) {
        if(arrLen > 2)
            return splitArr[0];
        else
            return '';
    }
    else if (val === 0) {
        return splitArr[arrLen - 2]+ '.' + splitArr[arrLen - 1];
    }
    return domain;
}

function loginAnyway(){
	const OUR_DOMAINNAME = document.getElementById("OUR_DOMAINNAME").value;
	const company_subdomain = document.getElementById("company_subdomain").value;
	document.querySelector( "#frmlogin" ).submit();
	return true;
}

async function checkloginId(){
    let errorID;
    const company_subdomain = document.getElementById("company_subdomain");
	errorID = document.getElementById("errmsg_company_subdomain");
    errorID.innerHTML = '';
    if(company_subdomain.value===''){
        errorID.innerHTML = 'Missing Branch Name.';
        company_subdomain.focus();
        return false;
    }

    const user_email = document.getElementById("user_email");
	errorID = document.getElementById("errmsg_user_email");
    errorID.innerHTML = '';
    if(user_email.value===''){
        errorID.innerHTML = 'Missing Email.';
        user_email.focus();
        return false;
    }
    else if(emailcheck(user_email.value)===false){
        errorID.innerHTML = 'Invalid Email.';
        user_email.focus();
        return false;
    }
    
	const user_password = document.getElementById("user_password");
	errorID = document.getElementById("errmsg_user_password");
	errorID.innerHTML = '';
    if(user_password.value===''){
        errorID.innerHTML = 'Missing Password.';
        user_password.focus();
        return false;
    }

	const OUR_DOMAINNAME = document.getElementById("OUR_DOMAINNAME").value;

    const jsonData = serialize("#frmlogin");
    const url = '/Account/checkloginId';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let workstationsName = data.workstationsName;
        let someoneworkstations_id = data.workstations_id;
        let workstations_id = getCookie('workstations_id');
        let userName = data.userName;
		if(data.returnStr===''){
			document.querySelector( "#frmlogin" ).submit();
			return true;
		}
		else if(data.returnStr==='Home'){
			loginAnyway();
		}
		else if(data.returnStr==='Someone'){
            if(workstations_id != someoneworkstations_id){
			    showSomeoneloginMsg(workstationsName, userName);
            }
            else{
                loginAnyway();
            }
		}
		else{
			document.querySelector("#err_message").innerHTML = data.returnStr;
		}		
    }
	return false;
}

function showSomeoneloginMsg(workstationsName, userName){
    confirm_dialog(Translate('Already logged in by same credential.'), userName+' '+Translate('is already logged in with the same credentials at workstation')+': '+workstationsName+'<br><br>'+Translate('If you continue you will log them out.'), loginAnyway);
}

function AJsave_forgotpassword(){
	const company_subdomain = document.getElementById("company_subdomain").value;
	document.querySelector("#frmforgotPassword").setAttribute('action', '//'+company_subdomain+'.'+OUR_DOMAINNAME+'/Account/AJsave_forgotpassword');
	return true;
}

function AJsave_newpassword(event){
    const OUR_DOMAINNAME = document.getElementById("OUR_DOMAINNAME").value;
    const company_subdomain = document.getElementById("company_subdomain").value;

    const oField = document.getElementById("user_password");  
    const oField2 = document.getElementById("reuser_password");

    const oElement = document.getElementById("errmsg_user_password");
    oElement.innerHTML = "";			
    if(oField.value !== oField2.value){
        oElement.innerHTML = 'Retype Password is not same Password';
        oField.focus();
        if(event){ event.preventDefault();}
        return(false);
    }
    document.querySelector("#frmsetnewPassword").setAttribute('action', '//'+company_subdomain+'.'+OUR_DOMAINNAME+'/Account/AJsave_newpassword');
    return true;
}

function redirectToSubDomain(subdomainId){
    const subdomain = document.getElementById(subdomainId).value;
    if(subdomain.length>0 && subdomain !==''){
        window.location = '//'+ subdomain +'.'+OUR_DOMAINNAME+'/Account/login';
    }
    else{
        const errmdg = document.getElementById("errmsg_company_subdomain")
        errmdg.innerHTML = 'Missing sub-domain';
    }
}

function forgotpassword(){
    let inputField;
    const subdomain = SUBDOMAIN;

    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
    document.getElementById('sideBar').innerHTML = '';
        const forgotPasswordRow = cTag('div',{class:"flexCenterRow"});
            let emptyColumn = cTag('div',{id: 'sideclass'});
            emptyColumn.innerHTML = '&nbsp';
        forgotPasswordRow.appendChild(emptyColumn);
            const forgotPasswordColumn = cTag('div',{class:'columnMD6', id: 'middleclass'});
                const callOutDiv = cTag('div',{class:"innerContainer"});
                    inputField =  cTag('input',{type:"hidden", id:"OUR_DOMAINNAME", value: OUR_DOMAINNAME});
                        //========= Here $successMsg =========//
                    let errorMessage = cTag('div',{class:"columnXS12", id:"err_message"});
                callOutDiv.append(inputField, errorMessage);
                    const forgotPasswordHeader = cTag('h3',{'style': "padding-bottom: 15px;"});
                    forgotPasswordHeader.innerHTML = 'Forgot Password';
                callOutDiv.appendChild(forgotPasswordHeader);
                    const forgotPasswordForm = cTag('form',{action:"/Account/AJsave_forgotpassword", name:"frmforgotPassword", id:"frmforgotPassword", class:"formfield", method:"post", enctype:"multipart/form-data"});
                    forgotPasswordForm.addEventListener('submit',AJsave_forgotpassword);
                    if(['www', ''].includes(subdomain) === false){
                        let subDomainColumn = cTag('div');
                            inputField = cTag('input',{type:"hidden", name:"company_subdomain", id:"company_subdomain", maxlength:"30", placeholder:"Sub-Domain Name", autocomplete:"off", value:subdomain});
                            let subDomainLabel = cTag('label',{for:"company_subdomain"});
                            subDomainLabel.innerHTML = subdomain +'.'+ OUR_DOMAINNAME;
                        subDomainColumn.append(inputField,subDomainLabel);
                        forgotPasswordForm.appendChild(subDomainColumn);
                    }
                    else{
                        let subDomainDiv = cTag('div',{class:"columnXS8  columnSM6"});
                            inputField = cTag('input',{type:"text", name:"company_subdomain", id:"company_subdomain", placeholder:"Sub-Domain Name", class:"form-control", autocomplete:"off", value:"", required:true, maxlength:"30"});
                        subDomainDiv.appendChild(inputField);
                    forgotPasswordForm.appendChild(subDomainDiv);
                        const domainName = cTag('div',{class:"columnXS4 columnSM6"});
                            let domainLabel = cTag('label',{for:"company_subdomain"});
                            domainLabel.innerHTML = OUR_DOMAINNAME;
                        domainName.appendChild(domainLabel);
                    forgotPasswordForm.appendChild(domainName);
                    }
                    forgotPasswordForm.appendChild(cTag('span',{ 'id':`errmsg_company_subdomain`,'class':`errormsg` }));

                        let emailDiv = cTag('div',{ 'style': "padding-top: 15px;" });
                        emailDiv.appendChild(cTag('input',{ 'type':`email`,'name':`user_email`,'id':`user_email`, required: true, 'placeholder':Translate('Email Address'),'class':`form-control`,'maxlength':`50`,'autocomplete':`off`,'value':``,'size':`50` }));
                        emailDiv.appendChild(cTag('span',{ 'id':`errmsg_user_email`,'class':`erroryellow` }));
                    forgotPasswordForm.appendChild(emailDiv);

                        let sendLinkButton = cTag('div',{ 'style': "padding-top: 15px;" });
                        sendLinkButton.appendChild(cTag('input',{ 'type':`submit`,'id':`btnsubmit`,'class':`submitButton`,'value':`Send Link` }));
                    forgotPasswordForm.appendChild(sendLinkButton);

                        const gotPasswordRow = cTag('div',{class: 'flexSpaBetRow', 'style': "padding-top: 15px;"})
                            let gotPasswordLink = cTag('a',{ 'href':`/Account/login`,'title':`Forgot password` });
                            gotPasswordLink.innerHTML = 'Got Password?';
                        gotPasswordRow.appendChild(gotPasswordLink);
                            let noAccountLink = cTag('a',{ 'href':`/Account/signup`,'title':`No accounts Yet? Click Here` });
                            noAccountLink.innerHTML = 'No accounts Yet? Click Here';
                        gotPasswordRow.appendChild(noAccountLink);
                    forgotPasswordForm.appendChild(gotPasswordRow);
                callOutDiv.appendChild(forgotPasswordForm);
            forgotPasswordColumn.appendChild(callOutDiv);
        forgotPasswordRow.appendChild(forgotPasswordColumn);
    Dashboard.appendChild(forgotPasswordRow);
}

function setnewpassword(){
    let inputField;
    const subdomain = SUBDOMAIN;

    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const newPasswordRow = cTag('div',{class:"flexCenterRow"});
            let emptyColumn = cTag('div',{ id: 'sideclass'});
            emptyColumn.innerHTML = '&nbsp';
        newPasswordRow.appendChild(emptyColumn);
            const newPasswordColumn = cTag('div',{class:'columnMD6', id: 'middleclass'});
                const callOutDiv = cTag('div',{class:"innerContainer"});
                    inputField =  cTag('input',{type:"hidden", id:"OUR_DOMAINNAME", value: OUR_DOMAINNAME});
                        //========= Here $successMsg =========//
                    let errorDiv = cTag('div',{class:"columnXS12", id:"err_message"});
                callOutDiv.append(inputField, errorDiv);
                    const newPasswordHeader = cTag('h3',{'style': "padding-bottom: 15px;"});
                    newPasswordHeader.innerHTML = 'Set New Password';
                callOutDiv.appendChild(newPasswordHeader);
                    const newPasswordForm = cTag('form',{action:"/Account/AJsave_newpassword", name:"frmsetnewPassword", id:"frmsetnewPassword", class:"formfield", method:"post", enctype:"multipart/form-data"});
                    newPasswordForm.addEventListener('submit',AJsave_newpassword);
                    if(['www', ''].includes(subdomain) === false){
                        let subDomainDiv = cTag('div');
                            inputField = cTag('input',{type:"hidden", name:"company_subdomain", id:"company_subdomain", maxlength:"30", placeholder:"Sub-Domain Name", autocomplete:"off", value:subdomain});/////, 
                            let subDomainLabel = cTag('label',{for:"company_subdomain"});
                            subDomainLabel.innerHTML = subdomain +'.'+ OUR_DOMAINNAME;
                        subDomainDiv.append(inputField,subDomainLabel);
                        newPasswordForm.appendChild(subDomainDiv);
                    }
                    else{
                        const subDomainColumn = cTag('div',{class:"columnXS8  columnSM6"});
                            inputField = cTag('input',{type:"text", name:"company_subdomain", id:"company_subdomain", placeholder:"Sub-Domain Name", class:"form-control", autocomplete:"off", value:"", required:true, maxlength:"30"});
                        subDomainColumn.appendChild(inputField);
                    newPasswordForm.appendChild(subDomainColumn);
                        const domainNames = cTag('div',{class:"columnXS4 columnSM6"});
                            let domainNamesLabel = cTag('label',{ for:"company_subdomain"});
                            domainNamesLabel.innerHTML = OUR_DOMAINNAME;
                        domainNames.appendChild(domainNamesLabel);
                    newPasswordForm.appendChild(domainNames);
                    }
                    newPasswordForm.appendChild(cTag('span',{ 'id':`errmsg_company_subdomain`,'class':`errormsg` }));

                        let userPasswordColumn = cTag('div',{ 'style': "padding-top: 15px;" });
                        userPasswordColumn.appendChild(cTag('input',{ 'type':`password`,'name':`user_password`,'id':`user_password`, required: true, 'minlength':`4`,'maxlength':`32`,'class':`form-control`,'placeholder':Translate('Password'),'autocomplete':`off`,'value':`` }));
                    newPasswordForm.appendChild(userPasswordColumn);

                        let retypePassword = cTag('div',{ 'style': "padding-top: 15px;" });
                        retypePassword.appendChild(cTag('input',{ 'type':`password`,'name':`reuser_password`,'id':`reuser_password`, required: true, 'minlength':`4`,'maxlength':`32`,'class':`form-control`,'placeholder':`Retype Password`,'autocomplete':`off`,'value':`` }));
                    newPasswordForm.appendChild(retypePassword);

                        let errorColumn = cTag('div',{ 'class':`columnXS12` });
                        errorColumn.appendChild(cTag('span',{ 'id':`errmsg_user_password`,'class':`errormsg` }));
                        errorColumn.appendChild(cTag('span',{ 'id':`errmsg_user_email`,'class':`errormsg` }));
                    newPasswordForm.appendChild(errorColumn);

                        const changePasswordInput = cTag('input',{ 'maxlength':`100`,'type':`hidden`,'name':`changepass_link`,'id':`changepass_link`,'autocomplete':`off`,'value':`` })
                    newPasswordForm.appendChild(changePasswordInput);
                        const userInput = cTag('input',{ 'maxlength':`100`,'type':`hidden`,'name':`user_email`,'id':`user_email`,'autocomplete':`off`,'value':`` });
                    newPasswordForm.appendChild(userInput);

                        let saveButtonRow = cTag('div',{ 'style': "padding-top: 15px;" });
                        saveButtonRow.appendChild(cTag('input',{ 'type':`submit`,'id':`btnsubmit`,'class':`submitButton`,'value':`Save & Login` }));
                    newPasswordForm.appendChild(saveButtonRow);

                        const forgotPasswordRow = cTag('div',{class: 'flexSpaBetRow', 'style': "padding-top: 15px;"})
                            let passwordLink = cTag('a',{ 'href':`/Account/login`,'title':`Forgot password` });
                            passwordLink.innerHTML = 'Got Password?';
                        forgotPasswordRow.appendChild(passwordLink);
                            let noAccountsLink = cTag('a',{ 'href':`/Account/signup`,'title':`No accounts Yet? Click Here` });
                            noAccountsLink.innerHTML = 'No accounts Yet? Click Here';
                        forgotPasswordRow.appendChild(noAccountsLink);
                    newPasswordForm.appendChild(forgotPasswordRow);
                callOutDiv.appendChild(newPasswordForm)
            newPasswordColumn.appendChild(callOutDiv);
        newPasswordRow.appendChild(newPasswordColumn);
    Dashboard.appendChild(newPasswordRow);
    AJ_setnewpassword_MoreInfo();
}

async function AJ_setnewpassword_MoreInfo(){
    const jsonData = {};
	jsonData['changepass_link'] = segment3;
    const url = '/'+segment1+'/AJ_setnewpassword_MoreInfo';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        document.getElementById('changepass_link').value = data.changepass_link;
        document.getElementById('user_email').value = data.user_email;
    }
}


//================Our-billing=============
function leftSideMenu(pageMiddle, paypal_id){
    let div1;
    const ourBillingModules = {
        'payment_details':Translate('Payment Details'),
        'locations':Translate('Locations'),
        'closeAccounts':Translate('Close Accounts')
    }
    const dashboard = document.getElementById('viewPageInfo');
        let headerTitleRow = cTag('div');
            const headerTitle = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
            headerTitle.append(ourBillingModules[segment2]+' ');
            headerTitle.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':`This page captures the accounts billing information` }));
        headerTitleRow.appendChild(headerTitle);
    dashboard.appendChild(headerTitleRow);
        const billingRow = cTag('div',{ 'class':`flexStartRow` });
            div1 = cTag('div',{ 'class':`columnMD2 columnSM3`});
                let calloutDiv = cTag('div',{'class':`innerContainer` });
                    const aTag = cTag('a',{ 'href':`javascript:void(0);`,'id':`secondarySideMenu` });
                    aTag.appendChild(cTag('i',{ 'class':`fa fa-align-justify`, 'style': "font-size: 2em;" }));
                calloutDiv.appendChild(aTag);
                    const menuUl = cTag('ul',{ 'class':`secondaryNavMenu settingslefthide` });                    
                    for (const module in ourBillingModules) {
                        const moduletitle = ourBillingModules[module];
                        let linkstr;
                        let span;
                        if(module==='closeAccounts'){
                            if(paypal_id===''){
                                linkstr = cTag('a',{ 'href':`javascript:void(0);`,'click':()=>closeAccounts(''),'title':moduletitle });
                                    span = cTag('span');
                                    span.innerHTML = moduletitle;
                                linkstr.appendChild(span);
                            }
                            else{
                                linkstr = cTag('a',{ 'href':`javascript:void(0);`,'click':()=>alert_dialog(Translate('Alert message'), 'You can not close this account. Please unsubscribe from bKash first, then automatically the account will be closed.', Translate('Ok')),'title':moduletitle });
                                    span = cTag('span');
                                    span.innerHTML = moduletitle;
                                linkstr.appendChild(span);
                            }
                        }
                        else{
                            linkstr = cTag('a',{ 'href':`/Account/${module}`,'title':moduletitle });
                                span = cTag('span');
                                span.innerHTML = moduletitle;
                            linkstr.appendChild(span);
                        }
                        let activeclass = '';
                        if(module===segment2 || (segment2==='' && module==='payment_details')){
                            linkstr = cTag('h4', {'style': "font-size: 18px;"});
                            linkstr.innerHTML = moduletitle;
                            activeclass = 'activeclass';
                        }
                            const li = cTag('li',{'class':activeclass});
                            li.appendChild(linkstr);
                        menuUl.appendChild(li);
                    }
                calloutDiv.appendChild(menuUl);
            div1.appendChild(calloutDiv);
        billingRow.appendChild(div1);
            let innerContainerDiv = cTag('div',{ 'class':`columnMD10 columnSM9` });
                let calloutInfo = cTag('div',{ 'class':`innerContainer`, 'style': `background:#FFF;` });
                calloutInfo.appendChild(pageMiddle);
            innerContainerDiv.appendChild(calloutInfo);
        billingRow.appendChild(innerContainerDiv);
    dashboard.appendChild(billingRow);

    //=======sessionStorage =========//
	let list_filters;
	if (sessionStorage.getItem("list_filters") !== null) {
		list_filters = JSON.parse(sessionStorage.getItem("list_filters"));
	}
	else{
		list_filters = {};
	}	
	let keyword_search = '';
    if(list_filters.hasOwnProperty("keyword_search")){
        keyword_search = list_filters.keyword_search;
        if(document.querySelector('#keyword_search')){
            document.querySelector('#keyword_search').value = keyword_search;
        }
    }
}

async function payment_details(){
    const url = '/'+segment1+'/AJ_payment_details_MoreInfo';
    fetchData(afterFetch,url,{});

    function afterFetch(data){
        let div1,div, button, thCol, tableHead, tableHeadRow;
        const pageMiddle = document.createDocumentFragment();
        if(data.paymentAccountID !=='' || data.paypal_id !==''){
                const paypalInfoTitle = cTag('h3',{ 'style': "color: #090; font-weight: bold;" });
                paypalInfoTitle.innerHTML = Translate('bKash Information');
            pageMiddle.appendChild(paypalInfoTitle);
        }
        if(['0000-00-00','1000-01-01'].includes(data.next_payment_due)){
                let nextPaymentRow = cTag('div',{ 'class':`customInfoGrid` });
                    let dueLabel = cTag('label', {'style': "padding-left: 10px;"});
                    dueLabel.innerHTML = Translate('Next Payment Due')+':';
                    let nextPaymentDiv = cTag('span');
                    nextPaymentDiv.innerHTML = DBDateToViewDate(data.next_payment_due,0,1);
                nextPaymentRow.append(dueLabel, nextPaymentDiv);
            pageMiddle.appendChild(nextPaymentRow);
        }
            let statusRow = cTag('div',{ 'class':`customInfoGrid` });
                let statusLabel = cTag('label', {'style': "padding-left: 10px;"});
                statusLabel.innerHTML = Translate('Status')+':';
                let statusField = cTag('span');
                statusField.innerHTML = data.status;
            statusRow.append(statusLabel, statusField);
        pageMiddle.appendChild(statusRow);
            let pricePerRow = cTag('div',{ 'class':`customInfoGrid` });
                let pricePerLabel = cTag('label', {'style': "padding-left: 10px;"});
                pricePerLabel.innerHTML = Translate('Price per location')+':';
                let pricePerField = cTag('span');
                pricePerField.innerHTML = data.price_per_location+' USD';
            pricePerRow.append(pricePerLabel, pricePerField);
        pageMiddle.appendChild(pricePerRow);
            let payFrequencyRow = cTag('div',{ 'class':`customInfoGrid` });
                let payFrequencyLabel = cTag('label', {'style': "padding-left: 10px;"});
                payFrequencyLabel.innerHTML = Translate('Pay Frequency')+':';
                let payFrequencyField = cTag('span');
                payFrequencyField.innerHTML = data.pay_frequency;
            payFrequencyRow.append(payFrequencyLabel, payFrequencyField);
        pageMiddle.appendChild(payFrequencyRow);

        if(window.location.host.split('.').slice(-2).join('.') !== 'machousel.com.bd'){
            if(data.paymentAccountID !==''){
                if(data.pstatus !==''){
                        let paidByDiv = cTag('div',{ 'class':`customInfoGrid` });
                            let paidLabel = cTag('label', {'style': "padding-left: 10px;"});
                            paidLabel.innerHTML = Translate('Paid By')+':';
                            let paypalSpan = cTag('span');
                            paypalSpan.innerHTML = 'bKash';
                        paidByDiv.append(paidLabel, paypalSpan);
                    pageMiddle.appendChild(paidByDiv);
                        let paypalStatusDiv = cTag('div',{ 'class':`customInfoGrid` });
                            let paypalStatusLabel = cTag('label', {'style': "padding-left: 10px;"});
                            paypalStatusLabel.innerHTML = `bKash ${Translate('Status')}:`;
                            let paypalStatusType = cTag('span');
                            paypalStatusType.innerHTML = data.pstatus;
                        paypalStatusDiv.append(paypalStatusLabel, paypalStatusType);
                    pageMiddle.appendChild(paypalStatusDiv);
                        let paypalQtyDiv = cTag('div',{ 'class':`customInfoGrid` });
                            let paypalQtyLabel = cTag('label', {'style': "padding-left: 10px;"});
                            paypalQtyLabel.innerHTML = 'bKash QTY:';
                            let paypalQtySpan = cTag('span');
                            paypalQtySpan.append(data.quantity);
                        paypalQtyDiv.append(paypalQtyLabel, paypalQtySpan);
                    pageMiddle.appendChild(paypalQtyDiv);
                }
                if(data.pstatus ==='ACTIVE' && data.pid !==''){
                        let nextPaymentDiv = cTag('div',{ 'class':`customInfoGrid` });
                            let nextLabel = cTag('label', {'style': "padding-left: 10px;"});
                            nextLabel.innerHTML = 'bKash Next Payment:';
                            let nextPaymentDate = cTag('span');
                            nextPaymentDate.innerHTML = DBDateToViewDate(data.next_billing_time, 0, 1);
                        nextPaymentDiv.append(nextLabel, nextPaymentDate);
                    pageMiddle.appendChild(nextPaymentDiv);
                        div1 = cTag('div',{ 'class':`flexStartRow` });
                            div = cTag('div',{ 'class':`columnSM5 columnMD3` });
                            div.innerHTML = ' ';
                        div1.appendChild(div);
                            div = cTag('div',{ 'class':`columnSM7 columnMD9` });
                                button = cTag('button',{ 'class':`btn subscribeButton`,'type':`button`,'id':`btnSubscr_cancel`,'click':()=>subscribeAction(data.pid, 0, 'cancel')});
                                button.innerHTML = 'Cancel Subscription';
                            div.appendChild(button);
                            if(data.outstanBalVal>0){
                                    button = cTag('button',{ 'class':`btn saveButton`,'type':`button`,'id':`btnSubscr_capture`});
                                    button.innerHTML = 'Capture Payment';
                                div.appendChild(button);
                            }
                        div1.appendChild(div);
                    pageMiddle.appendChild(div1);
                }
            }
            if(data.status ==='SUSPENDED'){
                    let billingRow = cTag('div',{ 'class':`flexStartRow` });
                        let billingColumn = cTag('div',{ 'class':`columnSM12` });
                        billingColumn.innerHTML = Translate('Please contact us for billing information.');
                    billingRow.appendChild(billingColumn);
                pageMiddle.appendChild(billingRow);
            }
            else if(data.paymentAccountID ==='' && data.oldCustomer !=='' && data.oldCustomer==='RESOURCE_NOT_FOUND'){
                    let supportRow = cTag('div',{ 'class':`flexStartRow` });
                        let supportColumn = cTag('div',{ 'class':`columnSM12` });
                        supportColumn.innerHTML = 'Please contact with SUPPORT.';
                    supportRow.appendChild(supportColumn);
                pageMiddle.appendChild(supportRow);
            }
            else if((data.paymentAccountID ==='' && data.paypal_id ==='') || data.pstatus !=='ACTIVE' || (data.pstatus ==='APPROVAL_PENDING' && data.status ==='Active')){
                    let paymentAccountRow = cTag('div',{ 'class':`flexStartRow` });
                        div = cTag('div',{ 'class':`columnSM5 columnMD3` });
                        div.innerHTML = ' ';
                    paymentAccountRow.appendChild(div);
                        div1 = cTag('div',{ 'class':`columnSM7 columnMD9` });
                        if(data.status === 'Trial' && ['0000-00-00', '1000-01-01'].includes(data.next_payment_due)){
                            div1.append(Translate('To subscribe please click the')+' ');
                        }
                        button = cTag('button',{ 'class':`btn subscribeButton`,'type':`button`});
                        button.addEventListener('click', function(){location.reload();});
                        button.innerHTML = 'Please reload page again.';
                        div1.appendChild(button);
                    paymentAccountRow.appendChild(div1);
                pageMiddle.appendChild(paymentAccountRow);
            }                
            pageMiddle.appendChild(cTag('input',{ 'type':`hidden`,'id':`access_token`,'value':data.access_token }));
            pageMiddle.appendChild(cTag('input',{ 'type':`hidden`,'id':`planId`,'value':data.planId }));
        }
        else{
                let subscriptionRow = cTag('div',{ 'class':`flex` });
                    let subscriptionDiv = cTag('div',{ 'class':`columnSM5 columnMD3` });
                        let emptyLabel = cTag('label');
                        emptyLabel.innerHTML = ' ';
                    subscriptionDiv.appendChild(emptyLabel);
                subscriptionRow.appendChild(subscriptionDiv);
                    let subscriptionsColumn = cTag('div',{ 'class':`columnSM7 columnMD9`, 'style': "padding-top: 5px;" });
                    subscriptionsColumn.append('For payment we use bKash subscriptions ');
                        let subscribeButton = cTag('button',{ 'class':`btn saveButton`,'type':`button`,'id':`btnCreatebKashSubs` });
                        subscribeButton.innerHTML = Translate('Subscribe bKash');
                    subscriptionsColumn.appendChild(subscribeButton);
                    subscriptionsColumn.appendChild(cTag('input',{ 'type':`hidden`,'id':`subdomain`,'value':data.subdomain }));
                subscriptionRow.appendChild(subscriptionsColumn);
            pageMiddle.appendChild(subscriptionRow);
        }

            let hrLine = cTag('hr');
        pageMiddle.appendChild(hrLine);

            const invoiceDetailDiv = cTag('div',{ 'class':`columnXS12` });
            invoiceDetailDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`pageURI`,'id':`pageURI`,'value':`${segment1}/${segment2}` }));
            invoiceDetailDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`page`,'id':`page`,'value':`1` }));
            invoiceDetailDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`rowHeight`,'id':`rowHeight`,'value':`34` }));
            invoiceDetailDiv.appendChild(cTag('input',{ 'type':`hidden`,'name':`totalTableRows`,'id':`totalTableRows`,'value':`1` }));
                let invoiceDetailRow = cTag('div',{ 'class':`flexSpaBetRow` });
                    let invoiceDetailColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
                        const invoiceHeader = cTag('h3');
                        invoiceHeader.append(Translate('Invoice Details')+' ');
                        invoiceHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Invoice Details') }));
                    invoiceDetailColumn.appendChild(invoiceHeader);
                invoiceDetailRow.appendChild(invoiceDetailColumn);
                    let searchColumn = cTag('div',{ 'class':`columnXS12 columnSM6` });
                        let searchInGroup = cTag('div',{ 'class':`input-group` });
                        searchInGroup.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Account_payment_details),'type':`text`,'placeholder':Translate('Search Invoice Number'),'value':'','id':`keyword_search`,'name':`keyword_search`,'class':`form-control`,'maxlength':`50` }));
                            const searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'click':filter_Account_payment_details,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search Invoice Number') });
                            searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                        searchInGroup.appendChild(searchSpan);
                    searchColumn.appendChild(searchInGroup);
                invoiceDetailRow.appendChild(searchColumn);
            invoiceDetailDiv.appendChild(invoiceDetailRow);
                let paymentTableRow = cTag('div',{ 'class':`columnSM12`,'style':`position: relative` });
                    let paymentNoMore = cTag('div',{ 'id':`no-more-tables` });
                        let paymentTable = cTag('table',{ 'class':`table-bordered table-striped table-condensed cf listing` });
                            tableHead = cTag('thead',{ 'class':`cf` });
                                tableHeadRow = cTag('tr');
                                    thCol = cTag('th',{ 'style': "width: 80px;" });
                                    thCol.innerHTML = Translate('Date');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`left`,'width':`10%`,'nowrap':`` });
                                    thCol.innerHTML = Translate('Invoice Number');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`left` });
                                    thCol.innerHTML = Translate('Description');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`left`,'width':`15%` });
                                    thCol.innerHTML = Translate('Paid By');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`left`,'width':`10%` });
                                    thCol.innerHTML = Translate('Next Payment Due');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`left`,'width':`7%` });
                                    thCol.innerHTML = Translate('Total');
                                tableHeadRow.appendChild(thCol);
                                    thCol = cTag('th',{ 'align':`left`,'width':`7%` });
                                    thCol.innerHTML = Translate('Print');
                                tableHeadRow.appendChild(thCol);
                            tableHead.appendChild(tableHeadRow);
                        paymentTable.appendChild(tableHead);
                            let paymentTableBody = cTag('tbody',{ 'id':`tableRows` });
                        paymentTable.appendChild(paymentTableBody);
                    paymentNoMore.appendChild(paymentTable);
                paymentTableRow.appendChild(paymentNoMore);
            invoiceDetailDiv.appendChild(paymentTableRow);
            addPaginationRowFlex(invoiceDetailDiv);

                let hr2Line = cTag('hr');
            invoiceDetailDiv.appendChild(hr2Line);

                let noteTableRow = cTag('div',{ 'class':`columnSM12`,'style':`position: relative` });
                    let noteNoMore = cTag('div',{ 'id':`no-more-tables` });
                        let noteTable = cTag('table',{ 'class':`table-bordered table-striped table-condensed cf listing` });
                            tableHead = cTag('thead',{ 'class':`cf` });
                                tableHeadRow = cTag('tr');
                                    const tdCol = cTag('th',{ 'style': "width: 80px;",'nowrap':"" });
                                    tdCol.innerHTML = Translate('Date');
                                tableHeadRow.appendChild(tdCol);
                                    thCol = cTag('th',{ 'align':`left` });
                                    thCol.innerHTML = Translate('Note Description');
                                tableHeadRow.appendChild(thCol);
                            tableHead.appendChild(tableHeadRow);
                        noteTable.appendChild(tableHead);
                            let noteTableBody = cTag('tbody',{ 'id':`Searchresult1` });
                            showNoteDescription(noteTableBody, data.noteData);
                        noteTable.appendChild(noteTableBody);
                    noteNoMore.appendChild(noteTable);
                noteTableRow.appendChild(noteNoMore);
            invoiceDetailDiv.appendChild(noteTableRow);
        pageMiddle.appendChild(invoiceDetailDiv);

        addCustomeEventListener('filter',filter_Account_payment_details);
        addCustomeEventListener('loadTable',loadTableRows_Account_payment_details);
        leftSideMenu(pageMiddle,data.paypal_id);
        filter_Account_payment_details(true);
    }
}

async function filter_Account_payment_details(firstLoad = false){
    let page = 1;
    if(firstLoad){
        page = parseInt(document.getElementById("page").value);
        if(isNaN(page) || page===0){
            page = 1;
        }
    }
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['keyword_search'] = document.getElementById("keyword_search").value;			
	jsonData['totalRows'] = document.getElementById("totalTableRows").value;
	jsonData['rowHeight'] = document.getElementById("rowHeight").value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
    const url = "/Account/AJgetPage_payment_details/filter";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createPaymentDetailsLists(document.getElementById("tableRows"),data.tableRows);
        document.getElementById("totalTableRows").value = data.totalRows;
        
        onClickPagination();
    }
}

async function loadTableRows_Account_payment_details(){
	const jsonData = {};
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = "/Account/AJgetPage_payment_details";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        createPaymentDetailsLists(document.getElementById("tableRows"),data.tableRows);
        onClickPagination();
    }
}

function createPaymentDetailsLists(parentNode,tableData){
    parentNode.innerHTML = '';
    if(tableData.length){
        tableData.forEach(item=>{
            let td;
            const tr = cTag('tr');
                td = cTag('td',{ 'data-title':'Date','align':`center` });
                td.innerHTML = DBDateToViewDate(item[1],1,1)[0];
            tr.appendChild(td);
                td = cTag('td',{ 'data-title':'Invoice Number','align':`center` });
                td.innerHTML = item[2];
            tr.appendChild(td);
                td = cTag('td',{ 'data-title':'Description','align':`center` });
                td.innerHTML = item[3];
            tr.appendChild(td);
                td = cTag('td',{ 'data-title':'Paid By','align':`center` });
                td.innerHTML = item[4];
            tr.appendChild(td);
                td = cTag('td',{ 'data-title':'Next Payment Due','align':`center` });
                td.innerHTML = DBDateToViewDate(item[5],1,1)[0];
            tr.appendChild(td);
                td = cTag('td',{ 'data-title':'Total','align':`right` });
                td.innerHTML = item[6];
            tr.appendChild(td);
                td = cTag('td',{ 'data-title':'Print Invoice','align':`center` });
                    const aTag = cTag('a',{ 'click':()=>printbyurl(`/Account/prints/${item[0]}`),'title':Translate('Print Invoice') });
                    aTag.appendChild(cTag('i',{ 'class':`fa fa-print`, 'style': "font-size: 18px; font-weight: bold;" }));
                td.appendChild(aTag);
            tr.appendChild(td);
            parentNode.appendChild(tr);
        })
    }
    else{
        const tr = cTag('tr');
            const td = cTag('td',{ 'colspan':`7`});
            td.innerHTML = '';
        tr.appendChild(td);
        parentNode.appendChild(tr);
    }
}

async function showNoteDescription(parentNode,noteData){
    let tr, td;
    if(noteData.length){
        noteData.forEach(item=>{
                tr = cTag('tr');
                    td = cTag('td',{ 'data-title':Translate('Date'),'align':`center`,'nowrap':"" });
                    td.innerHTML = DBDateToViewDate(item[1],0,1);
                tr.appendChild(td);
                    td = cTag('td',{ 'data-title':Translate('Description'),'align':`left` });
                    td.innerHTML = item[2];
				if(item[3]=='Admin'){
                    const iTag = cTag('i',{ 'AJget_OurNotes':`AJget_OurNotes(${item[0]});`, class:'fa fa-edit cursor', 'data-original-title':'Edit Note' });
                    td.append(' ', iTag);
				}
                tr.appendChild(td);
            parentNode.appendChild(tr);
        })
    }
    else{
            tr = cTag('tr');
                td = cTag('td',{ 'colspan':`2`});
                td.innerHTML = '';
            tr.appendChild(td);
        parentNode.appendChild(tr);
    }
}

function closeAccounts(paymentAccountID){
	if(paymentAccountID !==''){
		alert_dialog(Translate('CLOSE ACCOUNT'), Translate('You must remove bKash information first then try to CLOSE account.'), Translate('Close'))
	}
	else{
		let dialogConfirm = cTag('div');
			let pTag = cTag('p',{ 'style': "text-align: left;"});
				let strong = cTag('strong');
				strong.innerHTML = Translate('This will CLOSE your account. You will no longer be charged for this service.<br /><br /><strong>Are you sure you want to CLOSE your account?');
            pTag.appendChild(strong);
		dialogConfirm.appendChild(pTag);

		popup_dialog(
			dialogConfirm,
			{
				title:Translate('CLOSE ACCOUNT'),
				width:400,
				buttons: {
					"Cancel": {
						text: "Cancel", 
						class: 'btn defaultButton', 'style': "margin-left: 10px;",
						click: function(hidePopup) {
							hidePopup();
						},
					},
					"Agree":{
						text: "Agree", 
						class: 'btn saveButton archive', 'style': "margin-left: 10px;",
						click: function(hidePopup) {
							confirmCloseAccounts();
							hidePopup();
						},
					}
				}
			}
		);
	}
}

async function confirmCloseAccounts(){
    let archive;
	archive = document.querySelector('.archive');
	archive.value = 'Updating';
	archive.disabled = false;

	const jsonData = {};
	const url = "/Account/closeAccounts";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.returnStr !=='error'){
			window.location = '/Account/payment_details';
		}
		else{
			archive = document.querySelector('.archive');
			archive.value = 'Agree';
			archive.disabled = false;

			showTopMessage('alert_msg', "Sorry! Could not cancel your account.");
		}
	}
	return false;
}

async function locations(){
    const pageMiddle = document.createDocumentFragment();
    pageMiddle.appendChild(cTag('input',{ 'type':`hidden`,'name':`pageURI`,'id':`pageURI`,'value':`${segment1}/${segment2}` }));
    pageMiddle.appendChild(cTag('input',{ 'type':`hidden`,'name':`page`,'id':`page`,'value':1 }));
    pageMiddle.appendChild(cTag('input',{ 'type':`hidden`,'name':`rowHeight`,'id':`rowHeight`,'value':'34' }));
    pageMiddle.appendChild(cTag('input',{ 'type':`hidden`,'name':`totalTableRows`,'id':`totalTableRows`,'value':1 }));
        const locationRow = cTag('div',{ 'class':`flexStartRow` });
            const locationColumn = cTag('div',{ 'class':`columnXS12 columnMD7` });
                let locationTitle = cTag('div',{ 'class':`flexStartRow` });
                    let locationTitleCol = cTag('div',{ 'class':`columnXS12 columnSM6` });
                        const locationHeader = cTag('h3');
                        locationHeader.append(Translate('Locations List')+' ');
                        locationHeader.appendChild(cTag('i',{ 'class':`fa fa-info-circle`, 'style': "font-size: 16px;", 'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Locations List') }));
                    locationTitleCol.appendChild(locationHeader);
                locationTitle.appendChild(locationTitleCol);
                    let searchLocation = cTag('div',{ 'class':`columnXS12 columnSM6` });
                        let searchInput = cTag('div',{ 'class':`input-group` });
                        searchInput.appendChild(cTag('input',{ 'keydown':listenToEnterKey(filter_Account_locations),'type':`text`,'placeholder':Translate('Search Locations'),'id':`keyword_search`,'name':`keyword_search`,'class':`form-control`,'maxlength':`50` }));
                            let searchSpan = cTag('span',{ 'class':`input-group-addon cursor`,'click':filter_Account_locations,'data-toggle':`tooltip`,'data-placement':`bottom`,'data-original-title':Translate('Search Locations') });
                            searchSpan.appendChild(cTag('i',{ 'class':`fa fa-search` }));
                        searchInput.appendChild(searchSpan);
                    searchLocation.appendChild(searchInput);
                locationTitle.appendChild(searchLocation);
            locationColumn.appendChild(locationTitle);
                let locationTableRow = cTag('div',{ 'class':`flexStartRow` });
                    let locationTableColumn = cTag('div',{ 'class':`columnSM12`,'style':`position: relative` });
                        let noMoreTables = cTag('div',{ 'id':`no-more-tables` });
                            const locationTable = cTag('table',{ 'class':`table-bordered table-striped table-condensed cf listing` });
                                const tableHead = cTag('thead',{ 'class':`cf` });
                                    const tableRow = cTag('tr');
                                        const thCol = cTag('th',{ 'style': "text-align: center;" });
                                        thCol.innerHTML = Translate('Location Name');
                                    tableRow.appendChild(thCol);
                                tableHead.appendChild(tableRow);
                            locationTable.appendChild(tableHead);
                                const tbody = cTag('tbody',{ 'id':`tableRows` });
                            locationTable.appendChild(tbody);
                        noMoreTables.appendChild(locationTable);
                    locationTableColumn.appendChild(noMoreTables);
                locationTableRow.appendChild(locationTableColumn);
            locationColumn.appendChild(locationTableRow);
            addPaginationRowFlex(locationColumn);
        locationRow.appendChild(locationColumn);
            let newLocationColumn = cTag('div',{ 'class':`columnXS12 columnMD5` });
                const newLocationHeader = cTag('h4',{ 'class':`borderbottom`, 'style': "font-size: 18px;", 'id':`formtitle` });
                newLocationHeader.innerHTML = Translate('Add New Location');
            newLocationColumn.appendChild(newLocationHeader);
                const locationForm = cTag('form',{ 'action':`#`,'name':`frmlocations`,'id':`frmlocations`,'submit':AJsave_locations,'enctype':`multipart/form-data`,'method':`post`,'accept-charset':`utf-8` });
                    let locationDiv = cTag('div',{ 'class':`flexSpaBetRow`,'style':'margin: 10px 0px;' });
                        const titleLabel = cTag('label',{ 'for':`name` });
                        titleLabel.append(Translate('Location Name'));
                            let requiredField = cTag('span',{ 'class':`required` });
                            requiredField.innerHTML = '*';
                        titleLabel.appendChild(requiredField);
                    locationDiv.appendChild(titleLabel);
                    locationDiv.appendChild(cTag('input',{ 'type':`text`,'required':``,'class':`form-control`,'name':`name`,'id':`name`,'value':``,'size':`30`,'maxlength':`30`,'keyup':showthisurivalue }));
                    locationDiv.appendChild(cTag('span',{ 'id':`error_company_subdomain`,'class':`errormsg` }));
                locationForm.appendChild(locationDiv);
                    let locationGroup = cTag('div',{ 'class':`flexStartRow` });
                    locationGroup.appendChild(cTag('input',{ 'type':`hidden`,'name':`locations_id`,'id':`locations_id`,'value':`0` }));
                    locationGroup.appendChild(cTag('input',{ 'type':`submit`,'id':`submit`,'class':`btn saveButton`, 'style': "margin-right: 10px;", 'value':` ${Translate('Save')} ` }));
                    locationGroup.appendChild(cTag('input',{ 'type':`button`,'name':`reset`,'id':`reset`,'click':resetForm_locations,'value':Translate('Cancel'), 'class':`btn defaultButton`, 'style': "margin-right: 10px; display: none;"}));
                    locationGroup.appendChild(cTag('input',{ 'type':`button`,'name':`archive`,'id':`archive`,'value':Translate('Archive'),'class':`btn archiveButton`, 'style': "display: none;" }));
                locationForm.appendChild(locationGroup);
            newLocationColumn.appendChild(locationForm);
        locationRow.appendChild(newLocationColumn);
    pageMiddle.appendChild(locationRow);
    let paypal_id = '';
    if(document.getElementById('paypal_id')){
        paypal_id = document.getElementById('paypal_id').value;
    }

    addCustomeEventListener('filter',filter_Account_locations);
    addCustomeEventListener('loadTable',loadTableRows_Account_locations);
    leftSideMenu(pageMiddle, paypal_id);
    filter_Account_locations(true);
}

async function filter_Account_locations(firstLoad = false){
    let page = 1;
    if(firstLoad){
        page = parseInt(document.getElementById("page").value);
        if(isNaN(page) || page===0){
            page = 1;
        }
    }
	document.getElementById("page").value = page;
	
	const jsonData = {};
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = page;
	
    const url = "/Account/AJgetPage_locations/filter";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        storeSessionData(jsonData);
        createLocationLists(document.getElementById("tableRows"),data.tableRows);
        document.getElementById("totalTableRows").value = data.totalRows;
        
        onClickPagination();
    }
}

async function loadTableRows_Account_locations(){
	const jsonData = {};
	jsonData['keyword_search'] = document.getElementById('keyword_search').value;			
	jsonData['totalRows'] = document.getElementById('totalTableRows').value;
	jsonData['rowHeight'] = document.getElementById('rowHeight').value;	
	jsonData['limit'] = checkAndSetLimit();
	jsonData['page'] = document.getElementById('page').value;
	
    const url = "/Account/AJgetPage_locations";

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        createLocationLists(document.getElementById("tableRows"),data.tableRows);
        onClickPagination();
    }
}

function createLocationLists(parentNode,tableData){
    parentNode.innerHTML = '';
    if(tableData.length){
        tableData.forEach(item=>{
                const tr = cTag('tr');
                    const td = cTag('td',{ 'data-title':Translate('Location Name'),'align':`left` });
                    if (item.prod_cat_man !== item.laccounts_id){
                            let a = cTag('a',{ 'class':`anchorfulllink`,'href':`javascript:void(0);`,'title':Translate('Location Name') });
                            a.innerHTML = item.company_subdomain;
                        td.appendChild(a);
                    }
                    else td.innerHTML = item.company_subdomain;
                tr.appendChild(td);
            parentNode.appendChild(tr);
        })
    }
    else{
            const tr = cTag('tr');
                const td = cTag('td');
                td.innerHTML = '';
            tr.appendChild(td);
        parentNode.appendChild(tr);
    }
}

function AJsave_locations(event){
    event.preventDefault();
	let userStatus = document.getElementById("userStatus").value;
	let namevalue = document.getElementById('name').value.trim();
	let lastChar = namevalue.charAt(namevalue.length-1);

	if(lastChar==='-'){
		namevalue = namevalue.slice(0, -1);
	}
	let errorid = document.getElementById('error_company_subdomain');
	if(namevalue.length<5){								
		errorid.innerHTML = Translate('Sub-domain should be minimum 5 characters');
		document.getElementById("name").focus();
		return false;
	}
	else if(namevalue === 'www'){
		errorid.innerHTML = Translate('Sub-domain should not be www');
		document.getElementById("name").focus();
		return false;
	}
	else if(userStatus === 'Trial'){								
		alert_dialog(Translate('Locations Setup'), Translate('The feature to have multiple locations and to transfer inventory from one store to another is only available after you subscribe. If you would like to know more about how this feature works send us a message and we will explain it to you'), Translate('Ok'));
		return false;
	}
	else{
		document.getElementById("name").value = namevalue;
		let locationAllow = parseInt(document.getElementById("locationAllow").value);
        if(isNaN(locationAllow)){locationAllow = 0;}
		if(locationAllow===0){
			alert_dialog(Translate('Add New Location'), Translate('Each additional location is <br><b><center>$29.99USD per month per location</b></center><p>To create additional locations please contact us using the HELP contact form'), Translate('Ok'));			
		}
		else{
			confirmLocation();
		}
		return false;
	}
}

async function confirmLocation(){
	const jsonData = {};
	jsonData['name'] = document.getElementById("name").value;

    let submit;
	submit =  document.querySelector("#submit");
    submit.value = Translate('Saving')+'...';
    submit.disabled = true;

	document.querySelectorAll('.archive').forEach(oneRowObj=>{
		oneRowObj.innerHTML = Translate('Saving')+'...';
		oneRowObj.disabled = true;
	});

    const url = '/Account/AJsave_locations/';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
		if(data.savemsg =='Add'){
			resetForm_locations();
			filter_Account_locations();

            let alertMsg;
            const ContainerFragment = document.createDocumentFragment();
                alertMsg = cTag('p');
                alertMsg.innerText = Translate('Your new location has been created. To log into your new location you will go to URL');
            ContainerFragment.append(alertMsg);
            ContainerFragment.append(cTag('br'));
                const strong = cTag('strong');
                strong.innerText = data.returnStr;
            ContainerFragment.append(strong);
            ContainerFragment.append(cTag('br'));
            ContainerFragment.append(cTag('br'));
                alertMsg = cTag('p');
                alertMsg.innerText = Translate('The admins email and password from this accounts has been copied to this new location and you can use those credentials to log into your new location.');
            ContainerFragment.append(alertMsg);
			alert_dialog(Translate('Alert message'), ContainerFragment, Translate('Ok'));
		}
        else if(data.returnStr=='errorOnAdding'){
			alert_dialog(Translate('Alert message'), Translate('Error occured while adding new location! Please try again.'), Translate('Ok'));
		}
		else if(data.returnStr=='Name_Already_Exist'){
			alert_dialog(Translate('Alert message'), Translate('The new location name you have chosen is already used or is invalid. Please try a different location name'), Translate('Ok'));
		}  
		else{
			alert_dialog(Translate('Alert message'), Translate('Error ocured while saving this location'), Translate('Ok'));
		}
			
        submit =  document.querySelector("#submit");
        submit.value = Translate('Save');
        submit.disabled = false;
        document.querySelectorAll('.archive').forEach(oneRowObj=>{
            oneRowObj.innerHTML = Translate('Confirm');
            oneRowObj.disabled = false;
        });
	}
	return false;
}

function resetForm_locations(){
	document.getElementById("name").value = '';
    document.querySelector("#reset").style.display = 'none';
    document.querySelector("#archive").style.display = 'none';
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {login,signup,forgotpassword,setnewpassword,payment_details,locations};
    layoutFunctions[segment2]();
});
