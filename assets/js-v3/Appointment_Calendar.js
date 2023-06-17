import {
    cTag, Translate, tooltip, confirm_dialog, showTopMessage, setOptions, activeLoader,hideLoader, btnEnableDisable, 
    popup_dialog600, date_picker, sanitizer, fetchData, serialize
} from './common.js';

let appointmentDate = new Date();
let daysOpt = {"1":"1 Days","2":"2 Days","3":"3 Days","4":"4 Days","5":"5 Days","6":"6 Days","7":"7 Days"};

if(segment2 === '') segment2 = 'lists';

function get_Date_Day(pre_next){
    let time = new Date();
    let todaysTime = parseInt(appointmentDate.getTime());
    if(isNaN(todaysTime)){todaysTime = 0;}
    time.setTime(todaysTime+(pre_next*1000*86400))
    let days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    function getDate(dateForPopup=false,fullYear=true){
        let date = time.getDate();
        let month = time.getMonth()+1;
        if(month<10) month = '0'+month;
        if(date<10) date = '0'+date;
        let year = time.getFullYear();
        if(!fullYear) year = (year+'').slice(2);
        if(!dateForPopup){
            if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
                return `${date}-${month}-${year}`;
            }
            else{
                return `${month}/${date}/${year}`;
            }
        }
        else return `${year}-${month}-${date}`;
    }
    function getDay(){
        return days[time.getDay()];
    }
    return {'getDate':getDate,'getDay':getDay}
}

function getTime_am_pm(time){
    if(timeformat==='24 hour') return time;
    else{
        if(time<12) return time+'am';
        if(time===12) return time+'pm';
        if(time<24) return (time-12)+'pm';
        if(time===24) return (time-12)+'am';
    }
}

function changeTimeFormate(hour){
    if(!(timeformat==='24 hour')){
        let [apphour,period] = hour.match(/([0-9]{1,2})(am|pm)/).slice(1);
        apphour = parseInt(apphour);
        if(period==='am') apphour===12?apphour+=12:apphour;
        else if(period==='pm') apphour===12?apphour:apphour+=12;
        return apphour;
    }
    return hour;
}

function lists(){
    const Dashboard = document.querySelector('#viewPageInfo');
    Dashboard.innerHTML = '';
        const titleRow = cTag('div');
            const titleHeader = cTag('h2',{ 'style': "padding: 5px; text-align: start;" });
            titleHeader.append(Translate('Appointment Calendar')+' ');
            titleHeader.appendChild(cTag('i',{ "class":"fa fa-info-circle", 'style': "font-size: 16px;", "data-toggle":"tooltip","data-placement":"bottom","title":"","data-original-title":Translate('This page displays the list of your appointment calendar') }));
        titleRow.appendChild(titleHeader);
    Dashboard.appendChild(titleRow);
        let callOutDivStyle = ''
        if(OS!=='unknown') callOutDivStyle += 'padding-left: 0; padding-right: 0;';
        const callOutDiv = cTag('div',{ "class":"innerContainer", "style": callOutDivStyle });
            const filterRow = cTag('div',{ "class":"flexSpaBetRow" });
                const dateRangeColumn = cTag('div',{ "class":"flex columnLG4 columnMD12" });
                    const leftArrow = cTag('div');
                        let leftArrowLink = cTag('a',{ 'style': "border-radius: 4px; box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075); font-weight: bold; padding: 4px 12px; font-size: 18px; background: #FAFAD2;", "href":"javascript:void(0);","id":"previous" });
                        leftArrowLink.innerHTML = '«';
                    leftArrow.appendChild(leftArrowLink);
                dateRangeColumn.appendChild(leftArrow);
                dateRangeColumn.appendChild(cTag('div',{ 'style': "padding-left: 10px; margin-top: -4px;", "id":"appointmentDateContainer" }));
                    const rightArrow = cTag('div',{ 'style': "padding-left: 10px;" });
                        let rightArrowLink = cTag('a',{ 'style': "border-radius: 4px; box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075); font-weight: bold; padding: 4px 12px; font-size: 18px; background: #FAFAD2;", "href":"javascript:void(0);","id":"next" });
                        rightArrowLink.innerHTML = '»';
                    rightArrow.appendChild(rightArrowLink);
                dateRangeColumn.appendChild(rightArrow);
            filterRow.appendChild(dateRangeColumn);
            
                const dropDownColumn = cTag('form',{  "class":"flexEndRow columnLG8 columnMD12",'style':'gap:5px',"action":"#","name":"frmvarapp","id":"frmvarapp","method":"post","enctype":"multipart/form-data" });
                    const DaysInView = cTag('div',{'class':'input-group'});
                        const dayViewLabel = cTag('label',{ "class":"input-group-addon",'style':'min-width:110px; font-weight: bold;' });
                        dayViewLabel.innerHTML = Translate('Days in view');
                    DaysInView.appendChild(dayViewLabel);
                    DaysInView.appendChild(cTag('input',{ "type":"hidden","name":"variables_id","id":"variables_id" }));
                        const selectDayView = cTag('select',{ "name":"days_in_view","id":"days_in_view","class":"form-control",'style':'min-width:95px',"change": ()=> saveVariablesAppointments('days_in_view')});
                        setOptions(selectDayView,daysOpt,1,0);
                    DaysInView.appendChild(selectDayView);                        
                dropDownColumn.appendChild(DaysInView);
                    const StartTime = cTag('div',{'class':'input-group'});
                        const startTimeLabel = cTag('label',{ "class":"input-group-addon",'style':'min-width:110px; font-weight: bold;' });
                        startTimeLabel.innerHTML = Translate('Start Time');
                    StartTime.appendChild(startTimeLabel);
                        const selectStartTime = cTag('select',{ "name":"starttime","id":"starttime","class":"form-control",'style':'min-width:95px',"change": ()=> {setEndTimeOptions(); saveVariablesAppointments('starttime');} });
                        for(let i=1;i<=24;i++){
                            let option = cTag('option',{"value":i});
                            option.innerHTML = getTime_am_pm(i);
                            selectStartTime.appendChild(option);
                        }                      
                    StartTime.appendChild(selectStartTime);                       
                dropDownColumn.appendChild(StartTime);
                    const EndTime = cTag('div',{'class':'input-group'});
                        const endTimeLabel = cTag('label',{ "class":"input-group-addon",'style':'min-width:110px; font-weight: bold;' });
                        endTimeLabel.innerHTML = Translate('End Time');
                    EndTime.appendChild(endTimeLabel);
                        const selectEndTime = cTag('select',{ "name":"endtime","id":"endtime","class":"form-control",'style':'min-width:95px',"change": ()=> saveVariablesAppointments('endtime') });
                    EndTime.appendChild(selectEndTime);                        
                dropDownColumn.appendChild(EndTime); 
            filterRow.appendChild(dropDownColumn);
        callOutDiv.appendChild(filterRow);
            const listTableRow = cTag('div',{ "class":"flexStartRow", "id":'daysView'});
        callOutDiv.appendChild(listTableRow);
    Dashboard.appendChild(callOutDiv);

    AJ_lists_MoreInfo();
}

async function AJ_lists_MoreInfo(){
    let appointment_date = get_Date_Day(0).getDate();
    if(segment3) appointment_date = segment3.replace(/_/g,'/');
    const url = '/'+segment1+'/AJ_lists_MoreInfo';
    
    fetchData(afterFetch,url,{appointment_date});

    function afterFetch(data){
        if(calenderDate.toLowerCase()==='dd-mm-yyyy'){
            let [dd,mm,yy] = data.appDate.split('-');
            appointmentDate = new Date(`${mm}/${dd}/${yy}`)
        }
        else{
            appointmentDate = new Date(data.appDate);
        }
        document.querySelector('#previous').addEventListener("click", ()=> reloadAppCalWithDate(get_Date_Day(-data.days_in_view).getDate()))
        document.querySelector('#next').addEventListener("click", ()=> reloadAppCalWithDate(get_Date_Day(data.days_in_view).getDate()))
        let appointmentDateContainer = document.querySelector('#appointmentDateContainer')
        if(data.days_in_view===1){
            appointmentDateContainer.appendChild(cTag('input',{ "class":"form-control", size:'23', 'style': "background: #f05523; color: #FFF; border-color: #f05523; text-align: center;", "readonly":"","type":"text","name":"appointment_date","id":"appointment_date","value":data.appDate }));
        }
        else{
            appointmentDateContainer.appendChild(cTag('input',{ "type":"hidden","name":"appointment_date", "id":"appointment_date","value":data.appDate }));
            appointmentDateContainer.appendChild(cTag('input',{ "class":"form-control", 'style': "background: #f05523; color: #FFF; border-color: #f05523; text-align: center;", "name":"AppCal_daterange","id":"AppCal_daterange","readonly":"","type":"text","value":`${data.appDate} - ${data.endDate}` }))
        }
        document.querySelector('#variables_id').value = data.variables_id;
        document.querySelector('#starttime').value = data.starttime;
        document.querySelector('#days_in_view').value = data.days_in_view;

        for(let i=parseInt(data.starttime)+1;i<=24;i++){
                let option = cTag('option',{"value":i});
                option.innerHTML = getTime_am_pm(i);
            document.querySelector('#endtime').appendChild(option);
        }
        document.querySelector('#endtime').value = data.endtime;

        let oneViewWidth = (100/data.days_in_view).toFixed(3)+"%";
        let timewidth = 10+(data.days_in_view*2)+"%";
        
        let fieldName, appointmentHeadRow, tdCol1;            
        let colLG = 12, colMD = 12;
        if(data.days_in_view>1){
            if(data.days_in_view<=4){
                colMD = colLG = parseInt(12/data.days_in_view);
                if(data.days_in_view>=3){
                    colMD = 6;
                }
            }
            else{
                colLG = 3;
                colMD = 6;
            }
        }

        for(let i=0;i<data.days_in_view;i++){
            let time = get_Date_Day(i);
            let day_date_title = `${time.getDay()} ${time.getDate(false,false)}`;
                let tdCol = cTag('div',{'class':'columnMD'+colMD+' columnLG'+colLG});
                    const dayViewTable = cTag('table',{ "class":"table-bordered table-striped table-condensed cf listing " });
                        const dayViewHead = cTag('thead',{ "class":"cf" });
                            appointmentHeadRow = cTag('tr');
                                const thCol0 = cTag('th',{ "nowrap":"","width":timewidth });
                                thCol0.innerHTML = Translate('Hour');
                            appointmentHeadRow.appendChild(thCol0);
                                const thCol1 = cTag('th',{ "align":"left" });
                                thCol1.innerHTML = day_date_title;
                            appointmentHeadRow.appendChild(thCol1);
                        dayViewHead.appendChild(appointmentHeadRow);
                    dayViewTable.appendChild(dayViewHead);
                        const appointmentBody = cTag('tbody');
                        let starttime = parseInt(data.starttime);
                        let endtime = parseInt(data.endtime);
                        if(data.curDateInfo[day_date_title]){
                            data.curDateInfo[day_date_title].forEach((item)=>{
                                if(item.length===2){
                                    appointmentHeadRow = cTag('tr');
                                        tdCol1 = cTag('td',{ "align":"right", 'style': "font-weight: bold;" });
                                        tdCol1.innerHTML = item[0];
                                    appointmentHeadRow.appendChild(tdCol1);
                                        tdCol1 = cTag('td');
                                            let newAppointmentLink = cTag('a',{ "class":"fulllink", 'style': "font-weight: bold;", "click": ()=> AJgetPopup_Appointment_Calendar(0, time.getDate(true), changeTimeFormate(item[0]), '00') });
                                            newAppointmentLink.appendChild(cTag('i',{ "class":"fa fa-plus" }));
                                        tdCol1.appendChild(newAppointmentLink);
                                    appointmentHeadRow.appendChild(tdCol1);
                                    appointmentBody.appendChild(appointmentHeadRow);
                                }
                                else{                                            
                                    let hour = parseInt(item[0]);
                                    if(timeformat === '12 hour') item[0].indexOf('am')>-1? hour+='am':hour+='pm';
                                    else hour===0?hour=24:hour; 

                                    appointmentHeadRow = cTag('tr');
                                        tdCol1 = cTag('td',{ "align":"right" });
                                            let aTag = cTag('a',{ "class":"fulllink", "click": ()=> AJgetPopup_Appointment_Calendar(item[2], time.getDate(true), changeTimeFormate(hour), item[3]) });
                                            aTag.innerHTML = item[0];
                                        tdCol1.appendChild(aTag);
                                    appointmentHeadRow.appendChild(tdCol1);
                                        tdCol1 = cTag('td');
                                            let tdSpan = cTag('span');
                                            tdSpan.innerHTML = item[1];
                                        tdCol1.appendChild(tdSpan);
                                            let tdSpan2 = cTag('span', {'style': "min-width: 95%; display: inline-block; text-align: end;"});
                                                let removeSpan = cTag('i',{ "class":"fa fa-remove cursor", 'style': "font-weight: bold; margin-left: 10px;", "click": ()=> AJremoveAppointment_Calendar(item[2]) });
                                                let editSpan = cTag('i',{ "class":"fa fa-edit cursor", 'style': "font-weight: bold;", "click": ()=> AJgetPopup_Appointment_Calendar(item[2], time.getDate(true), changeTimeFormate(hour), item[3]) });
                                            tdSpan2.append(editSpan, removeSpan);
                                        tdCol1.appendChild(tdSpan2);
                                    appointmentHeadRow.appendChild(tdCol1);
                                    appointmentBody.appendChild(appointmentHeadRow);
                                }
                            })
                        }
                        else{
                            for(let j=starttime;j<=endtime;j++){
                                    appointmentHeadRow = cTag('tr');
                                        tdCol1 = cTag('td',{ "align":"right", 'style': "font-weight: bold;" });
                                        tdCol1.innerHTML = getTime_am_pm(j);
                                    appointmentHeadRow.appendChild(tdCol1);
                                        tdCol1 = cTag('td');
                                            let newAppointment = cTag('a',{ "class":"fulllink", 'style': "font-weight: bold;", "click": ()=> AJgetPopup_Appointment_Calendar(0, time.getDate(true), j, '00') });
                                            newAppointment.appendChild(cTag('i',{ "class":"fa fa-plus" }));
                                        tdCol1.appendChild(newAppointment);
                                    appointmentHeadRow.appendChild(tdCol1);
                                appointmentBody.appendChild(appointmentHeadRow);                                        
                            }
                        }
                    dayViewTable.appendChild(appointmentBody);
                tdCol.appendChild(dayViewTable);
            document.querySelector('#daysView').appendChild(tdCol);
        }
        if (document.querySelector('#appointment_date')){
            fieldName = 'appointment_date';    
            if (document.querySelector('#AppCal_daterange')){fieldName = 'AppCal_daterange';}  
            date_picker('#'+fieldName,(date,month,year)=>{reloadAppCalWithDate(month+'/'+date+'/'+year);});
        }
    }
}

function reloadAppCalWithDate(appointment_date){
	appointment_date = appointment_date.replace('/', '_').replace('/', '_').replace('/', '_').replace('/', '_');
	window.location = '/Appointment_Calendar/lists/' + appointment_date;
}

async function saveVariablesAppointments(fromField){
	const controller = document.querySelector("#"+fromField)
    controller.setAttribute('style', "background: #FFFF99;");
    const startTime = Number(document.getElementById('starttime').value);
    const endTime = Number(document.getElementById('endtime').value||0);
    if(startTime>=endTime) document.getElementById('endtime').value = startTime+1;
    
    activeLoader();
   
	const jsonData = serialize("#frmvarapp");
    const url = '/'+segment1+'/saveVariablesAppointments';

    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg !=='error'){
			let appointment_date = document.getElementById("appointment_date").value;
			controller.classList.remove('lightYellow');
            controller.disabled = false;
			reloadAppCalWithDate(appointment_date);
		}
		else{
            showTopMessage('alert_msg',Translate('Form fields data is missing.'));
		}
    }
	return false;
}

function setEndTimeOptions(){
	let starttime = parseInt(document.getElementById("starttime").value);
	if(starttime ==='' || isNaN(starttime)){starttime = 9;}
	let endtime = document.getElementById("endtime").value;
	for(let i = starttime+1; i <= 24; i++){
            let option = cTag('option',{"value":i});
            option.innerHTML = getTime_am_pm(i);
        document.querySelector('#endtime').appendChild(option);
	}
	document.getElementById("endtime").value = endtime;
}

async function AJgetPopup_Appointment_Calendar(appointments_id, appointment_date, hour, minutes){
    activeLoader();
    const jsonData = {"appointments_id":appointments_id};
    const url = '/'+segment1+'/AJgetPopup';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        let option, requiredField;
        const appointmentsForm = cTag('form',{ "action":"#","name":"frmappointments","id":"frmappointments", "enctype":"multipart/form-data","method":"post","accept-charset":"utf-8" });
            const appointmentDateRow = cTag('div',{ "class":"flex" });
                const appointmentDateColumn = cTag('div',{ "class":"columnXS4 columnSM4","align":"left" });
                    const appointmentDateLabel = cTag('label');
                    appointmentDateLabel.innerHTML = Translate('Appointment Date')+':';
                appointmentDateColumn.appendChild(appointmentDateLabel);
            appointmentDateRow.appendChild(appointmentDateColumn);
                const appointmentDateValue = cTag('div',{ "class":"columnXS8 columnSM8","align":"left" });
                appointmentDateValue.innerHTML = appointment_date+' '+ getTime_am_pm(hour);
            appointmentDateRow.appendChild(appointmentDateValue);
        appointmentsForm.appendChild(appointmentDateRow);
            const minutesRow = cTag('div',{ "class":"flex" });
                const minutesColumn = cTag('div',{ "class":"columnXS4 columnSM4","align":"left" });
                    const minuteLabel = cTag('label',{ "for":"appminutes" });
                    minuteLabel.append(Translate('Minutes'));
                        requiredField = cTag('span',{ "class":"required" });
                        requiredField.innerHTML = '*';
                    minuteLabel.appendChild(requiredField);
                minutesColumn.appendChild(minuteLabel);
            minutesRow.appendChild(minutesColumn);
                const minuteValue = cTag('div',{ "class":"columnXS8 columnSM8","align":"left" });
                    let selectMinute = cTag('select',{ "required":"","class":"form-control","id":"appminutes","name":"appminutes" });
                    for(let i = 0; i < 60; i+=5){
                        if(i < 10){
                            option = cTag('option',{ "selected":"","value":'0'+i });
                            option.innerHTML = '0'+i
                        }
                        else{
                            option = cTag('option',{ "selected":"","value":i });
                            option.innerHTML = i;
                        }
                        selectMinute.appendChild(option);
                    }
                    selectMinute.value = minutes;
                minuteValue.appendChild(selectMinute);
            minutesRow.appendChild(minuteValue);
        appointmentsForm.appendChild(minutesRow);
            const descriptionRow = cTag('div',{ "class":"flex" });
                const descriptionColumn = cTag('div',{ "class":"columnXS4 columnSM4","align":"left" });
                    const descriptionLabel = cTag('label',{ "for":"appdescription" });
                    descriptionLabel.append(Translate('Description'));
                        requiredField = cTag('span',{ "class":"required" });
                        requiredField.innerHTML = '*';
                    descriptionLabel.appendChild(requiredField);
                descriptionColumn.appendChild(descriptionLabel);
            descriptionRow.appendChild(descriptionColumn);
                const descriptionField = cTag('div',{ "class":"columnXS8 columnSM8","align":"left" });
                    const textarea = cTag('textarea',{ "required":"","class":"form-control","name":"appdescription","id":"appdescription","rows":"3" });
                    textarea.innerHTML = data.returnStr;
                    textarea.addEventListener('blur',sanitizer);
                descriptionField.appendChild(textarea);
                descriptionField.appendChild(cTag('span',{ "id":"error_appointments","class":"errormsg" }));
            descriptionRow.appendChild(descriptionField);
        appointmentsForm.appendChild(descriptionRow);
        appointmentsForm.appendChild(cTag('input',{ "type":"hidden","name":"appdate","id":"appdate","value":appointment_date }));
        appointmentsForm.appendChild(cTag('input',{ "type":"hidden","name":"apphour","id":"apphour","value":hour }));
        appointmentsForm.appendChild(cTag('input',{ "type":"hidden","name":"appointments_id","id":"appointments_id","value":appointments_id }));			

        popup_dialog600(Translate('New Appointment Information'), appointmentsForm, Translate('Save'), saveAppointment_CalendarForm);			

        setTimeout(function() {
            document.getElementById("appminutes").focus();
        }, 500);

        hideLoader();
    }
}	


async function saveAppointment_CalendarForm(hidePopoup){
    let pTag;
	const errorId = document.getElementById('error_appointments');
	errorId.innerHTML = '';
	if(document.querySelector("#appdate").value===''){
            pTag = cTag('p');
            pTag.innerHTML = Translate('Date field is required.');
		errorId.appendChild(pTag);
		document.querySelector("#appdate").focus();
		return false;
	}	

	errorId.innerHTML = '';
	if(document.querySelector("#apphour").value===''){
            pTag = cTag('p');
            pTag.innerHTML = Translate('Hour field is required.');
		errorId.appendChild(pTag);
		document.querySelector("#apphour").focus();
		return false;
	}	

	errorId.innerHTML = '';
	if(document.querySelector("#appminutes").value===''){
            pTag = cTag('p');
            pTag.innerHTML = Translate('Minutes field is required.');
		errorId.appendChild(pTag);
		document.querySelector("#appminutes").focus();
		return false;
	}	

	errorId.innerHTML = '';
    let newDescription = document.querySelector("#appdescription");
	if(newDescription.value===''){
            pTag = cTag('p', {'style': "margin: 0;"});
            pTag.innerHTML = Translate('Description field is required.');
		errorId.appendChild(pTag);
		newDescription.focus();
        newDescription.classList.add('errorFieldBorder');
		return false;
	}else{
		newDescription.classList.remove('errorFieldBorder');
	}

    let submitBtn = document.querySelector(".btnmodel");
    btnEnableDisable(submitBtn,Translate('Saving'),true);
    
    activeLoader();
    
    const jsonData = serialize("#frmappointments");
    const url = '/'+segment1+'/AJsaveAppointment_Calendar';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.savemsg ===''){
			hidePopoup();
			let appointment_date = document.getElementById("appointment_date").value;
			reloadAppCalWithDate(appointment_date);
		}
		else{
			if(data.savemsg = 'errorAdding') errorId.innerHTML = Translate('Error occured while adding new data! Please try again.');
			if(data.savemsg = 'errorNoChange') errorId.innerHTML = Translate('There is no changes made. Please try again.');
		}
        btnEnableDisable(submitBtn,Translate('Save'),false);
        hideLoader();
    }
	return false;
}

function AJremoveAppointment_Calendar(appointments_id){
    let removeWarning = cTag('p');
    removeWarning.append(Translate('Are you sure you want to remove this Appointment Calendar permanently?'));
        let input = cTag('input',{type:"hidden", id:"appointments_id", value:appointments_id});
    removeWarning.appendChild(input);
	confirm_dialog(Translate('Remove Appointment Calendar'),removeWarning,confirmAJremoveAppointment_Calendar);
}

async function confirmAJremoveAppointment_Calendar(hidePopoup){
	let archiveBtn = document.querySelector(".archive");
    archiveBtn.innerHTML = Translate('Removing')+'...';
    archiveBtn.disabled = true;
	let appointments_id = document.querySelector("#appointments_id").value;
    
    activeLoader();
   
    const jsonData = {"appointments_id":appointments_id};
    const url = '/'+segment1+'/AJremoveAppointment_Calendar';
    fetchData(afterFetch,url,jsonData);

    function afterFetch(data){
        if(data.removeCount>0){
			let appointment_date = document.getElementById("appointment_date").value;
			reloadAppCalWithDate(appointment_date);
            hidePopoup();
		}
		else{	
            showTopMessage('alert_msg',Translate('Could not remove information'));
            archiveBtn.innerHTML = Translate('Confirm');
            archiveBtn.disabled = false;
		}
    }
}

document.addEventListener('DOMContentLoaded', async()=>{
    let layoutFunctions = {lists};
    layoutFunctions[segment2]();

    /* let fn;
    fn = window[segment2];
    if(typeof fn === "function"){fn();}
    
    let loadData = 'AJ_'+segment2+'_MoreInfo';
    fn = window[loadData];
    if(typeof fn === "function"){fn();} */

    document.querySelectorAll('[data-toggle="tooltip"]').forEach(item=>tooltip(item));
});