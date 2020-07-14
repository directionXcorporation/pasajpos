function(logService){
	this.round = function round(value, exp='2', type='round'){
		exp = parseInt(exp);
		let x = new Decimal(value);
		let output;
		if(type == 'round'){
			output = x.toDecimalPlaces(exp);
		}else if(type=='up'){
			output = x.toDecimalPlaces(exp, 0);
		}else if(type=='down'){
			output = x.toDecimalPlaces(exp, 1);
		}
		return parseFloat(output.toNumber());
	}
	this.cashRound = function priceRound(value, exp=0.05, type='round'){
		//Returns a new Decimal whose value is the nearest multiple of exp to the value of this Decimal.
		exp = parseFloat(exp);
		let x = new Decimal(value);
		let output;
		if(type == 'round'){
			output = x.toNearest(exp);
		}else if(type=='up'){
			output = x.toNearest(exp,0);
		}else if(type=='down'){
			output = x.toNearest(exp,1);
		}else{
			output = x.toNearest(exp);
		}
		return parseFloat(output.toNumber());
	}
}