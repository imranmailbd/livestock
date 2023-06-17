/* function roundnumNew(num){
    var bkNum = num; // taken backup original num
    if(num <0){num = 0-num;}
    num = num.toFixed(3);
    num = +(Math.round(Number(num) + "e+2")  + "e-2");
    // If original num is negative then will back negative
    if(bkNum <0){num = 0-num;}
    
    return num;
} */

let roundnumNew = commonroundnum;

function calculateTaxNew(taxable_total, taxes_rate, tax_inclusive){
    console.log('taxable_total:',taxable_total, ' taxes_rate:',taxes_rate,' tax_inclusive:', tax_inclusive)
    // var taxes_percentage = taxes_rate*0.01;
    var taxes_percentage = Calculate('mul',false,taxes_rate,0.01);
    var returntax = 0.00;
    if(tax_inclusive>0){
        // returntax = taxable_total-(taxable_total/(taxes_percentage+1));
        returntax = Calculate('sub',false,taxable_total,Calculate('div',false,taxable_total,Calculate('add',false,taxes_percentage,1)));
    }
    else{
        // returntax = taxable_total*taxes_percentage;
        returntax = Calculate('mul',false,taxable_total,taxes_percentage);
    }
    
    return [returntax, round(returntax,2)];
}	

function calculateTax(taxable_total, taxes_rate, tax_inclusive){
    let taxes_percentage = taxes_rate*0.01;
    let returntax = 0.00;
    if(tax_inclusive>0){
        returntax = taxable_total-(taxable_total/(taxes_percentage+1));
    }
    else{
        returntax = taxable_total*taxes_percentage;
    }
    return [returntax, commonroundnum(returntax)];
}

//=================From Common.js===================//
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

function commonroundnum(number, scale = 2){
	let backup = number; // taken backup original number
	if(number <0){number = 0-number;}
	if(!("" + number).includes("e")) {
		number = +(Math.round(number + "e+" + scale)  + "e-" + scale);
	}
    else{
		let arr = ("" + number).split("e");
		let sig = ""
		if(+arr[1] + scale > 0) {
		  sig = "+";
		}
		number = +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
	}
	if(backup <0){number = 0-number;}
	return number;
}

//===============new process=================
function round(number,scale){
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

function RNumber(number){      
    let [integer, fraction=''] = number.toString().split('.');
    return {
        Numerator:Number(integer+fraction),
        Denominator: Math.pow(10,fraction.length)
}

}

function Calculate(operation,roundScale,Number1,Number2){
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
    if(roundScale !== false) return round(results,roundScale);
    else return results;
}

