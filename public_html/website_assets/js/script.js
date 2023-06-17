const toggleButton = document.getElementsByClassName('toggle-button')[0];
const navbarLinks = document.getElementsByClassName('navbar-links')[0];

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
if(urlParams.get('source')){
    let source = urlParams.get('source');
    let sourceCookies = getCookie('source');
    if(source !='' && sourceCookies=='')
        setCookie('source', source, 180);
}

toggleButton.addEventListener('click', () => {
    navbarLinks.classList.toggle('active');
})

function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    let expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(name){
	let [cookie] = document.cookie.split(';').
	map(item=>{
		let [name,value] = item.trim().split('=');
		return {name,value};
	}).
	filter(item=>item.name===name);
	
	if(cookie) return cookie.value;
    else return '';
}