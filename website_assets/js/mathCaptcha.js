function mathCaptcha(){
    let numField;
    const Container = document.getElementById("mathCaptcha");
    Container.innerHTML = '';
        let style = document.createElement('style');
        style.innerHTML = `.bGreen {border: 2px solid green;outline: none;}
                            .bRed {border: 2px solid red;outline: none;}`;
    Container.appendChild(style);
        let mainDiv = document.createElement('div');
        mainDiv.setAttribute('style'," width: 300px; float: left; margin: 0 15px 15px 0; font-size: 24px; font-weight: bold; text-align: center; border: 1px solid #ccc; background: linear-gradient(to right top, #eee, #ddd, #F6F6F6); padding: 15px; font-family: 'Brush Script MT', 'Brush Script Std', cursive; color: #000; ");
            numField = document.createElement('div');
            numField.setAttribute('id',"fNumber");
            numField.setAttribute('style',"width: 30px; float: left; margin: 0 5px");
        mainDiv.appendChild(numField);
            numField = document.createElement('div');
            numField.setAttribute('style',"width: 10px; float: left; margin: 0 5px");
            numField.innerHTML = '+';
        mainDiv.appendChild(numField);
            numField = document.createElement('div');
            numField.setAttribute('id',"lNumber");
            numField.setAttribute('style',"width: 30px; float: left; margin: 0 5px");
        mainDiv.appendChild(numField);
            numField = document.createElement('div');
            numField.setAttribute('style',"width: 20px; float: left; margin: 0 15px 0 5px");
            numField.innerHTML = '=';
        mainDiv.appendChild(numField);
            let input = document.createElement('input');
            input.setAttribute('required',"");
            input.setAttribute('type',"text");
            input.setAttribute('name',"mathCaptcha");
            input.setAttribute('id',"resultN");
            input.setAttribute('value',"");
            input.setAttribute('style'," width: 80px; float: left; margin: 0 5px; padding: 5px; line-height: 20px; ");
        mainDiv.appendChild(input);
            let reset = document.createElement('div');
            reset.setAttribute('onclick',"mathCaptcha();");
            reset.setAttribute('style'," width: 20px; float: left; margin: 0 10px 0 0px; cursor: pointer; ");
                let iTag = document.createElement('i');
                iTag.setAttribute('class',"fa fa-refresh");
                iTag.setAttribute('style',"margin-left:10px;");
            reset.appendChild(iTag);
        mainDiv.appendChild(reset);
    Container.appendChild(mainDiv);

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