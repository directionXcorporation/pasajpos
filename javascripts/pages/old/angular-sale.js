app.controller('parentobj.controllers.sale',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, $mdToast, $log, i18nService, uiGridConstants, $templateCache, offlineItems, offlineSales, localDb, globals, priceRounding, guid, localStorageService, errorHandling, externalDevice, settings, offlineTillOperation, receiptTemplatesService, tableMapsService) {
	///////////////////////////////
	/**
	TO DO: Re arrange the currency array when default is changed to prevent on-change event trigger
	*/
	//////////////////////////////
	externalDevice.sendData("%clearScreen%","LINE_DISPLAY").then(function(response){
		externalDevice.sendData("Till Open","LINE_DISPLAY");
	});
	$scope.privatedata = {};
	
	if(!$scope.parentobj.privatedata.salesScreen){
		$scope.parentobj.privatedata.salesScreen = {};
	}
	
	var tillId;
	var tillNumber;
	
	$scope.parentobj.OpenSalesScreen = function(salesScreenData, items=[], salePayments=[], itemsDb=undefined, tableMapsDb=undefined){
		$mdDialog.show({
			scope: $scope,
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: 'salesScreen.html',
			parent: angular.element(document.body),
			targetEvent: salesScreenData,
			clickOutsideToClose: false,
			locals: {
				salesScreen: salesScreenData.salesScreen,
				setlog: $scope.parentobj.setlog,
				items: items,
				itemsDb: itemsDb,
				tableMapsDb: tableMapsDb,
				salePayments: salePayments
			},
			multiple: true,
			fullscreen: true
		})
		.then(function(salesScreenData) {
		
		}, function() {
	
		});
	};
	function DialogController(salesScreen, setlog, salePayments, items, itemsDb, tableMapsDb, $scope, $q, $log) {
		if(!$scope.parentobj){
			$scope.parentobj = {};
			$scope.parentobj.privatedata = {};
		}
		
		/////////////////////////////////////
		/* Should read below from database */
		////////////////////////////////////
		$scope.retryTimes = 2;
		$scope.currencies = [
			{currencyId: "EUR", isDefault: '0', cashExp: '0.25', roundingExp: '2'},
			{currencyId: "USD", isDefault: '0', cashExp: '0.25', roundingExp: '2'},
			{currencyId: "CAD", isDefault: '1', cashExp: '0.25', roundingExp: '2'}
		];
		$scope.roundingType = 'round';
		$scope.cashRoundingType = 'down';
		$scope.discountMarkdowns = [
			{markdown_id: '1', markdown_description: 'test1'}, 
			{markdown_id: '2', markdown_description: 'test2'}
		];
		$scope.taxes = [
			{taxDetails_id: '1', taxDetails_name: 'GST', taxDetails_percent: '5.00'},
			{taxDetails_id: '2', taxDetails_name: 'QST', taxDetails_percent: '9.50'}
		];
		
		//calculate the two line below from currencies
		$scope.rounding = {exp: '2'}; 
		$scope.cashRounding = {
			'CAD': {exp: '0.25'},
			'USD': {exp: '0.25'}
		};
		$scope.allowedPaymentMethods = [
			{
				'paymentMethodDetails_methodId' : '1',
				'paymentMethodDetails_methodCode': 'CASHCAD',
				'paymentMethodDetails_currencyId': 'CAD',
				'paymentMethodDetails_methodName': 'Cash CAD',
				'paymentMethodDetails_methodDescription': 'Pay by cash',
				'paymentMethodDetails_sendToExtId' : '',
				'paymentMethodDetails_recieveFromExtId': '',
				'paymentMethodDetails_priceRounding': 1,
				'paymentExtDetails_sendParameters': {}, //aditional parameters that needs to be sent to Ext
				'paymentExtDetails_baseUrl': '', //In extDetails table- send to this url - no leading slashed aT THE END. //127.0.0.1 or https://127.0.0.1
				'paymentExtDetails_method': '', //In extDetails table- send using this methd(POST,GET)
				'paymentExtDetails_silentSend': 0, //In extDetails table- Do not show user input or sent parameters on screen
				'paymentExtDetails_silentRecieve': 0, //In extDetails table- Do not show user input or recieve parameters on screen
				'paymentExtDetails_recieveParameters': [] //In extDetails table- recieved parameters transformation table
			},
			{
				'paymentMethodDetails_methodId' : '2',
				'paymentMethodDetails_methodCode': 'CASHUSD',
				'paymentMethodDetails_currencyId': 'USD',
				'paymentMethodDetails_methodName': 'Cash USD',
				'paymentMethodDetails_methodDescription': 'Pay by cash',
				'paymentMethodDetails_sendToExtId' : '',
				'paymentMethodDetails_recieveFromExtId': '',
				'paymentMethodDetails_priceRounding': 1,
				'paymentExtDetails_sendParameters': [], //aditional parameters that needs to be sent to Ext
				'paymentExtDetails_baseUrl': '', //In extDetails table- send to this url
				'paymentExtDetails_method': '', //In extDetails table- send using this methd(POST,GET)
				'paymentExtDetails_silentSend': 0, //In extDetails table- Do not show user input or sent parameters on screen
				'paymentExtDetails_silentRecieve': 0, //In extDetails table- Do not show user input or recieve parameters on screen
				'paymentExtDetails_recieveParameters': [] //In extDetails table- recieved parameters transformation table
			},
			{
				'paymentMethodDetails_methodId' : '3',
				'paymentMethodDetails_methodCode': 'MASTERN',
				'paymentMethodDetails_currencyId': 'CAD',
				'paymentMethodDetails_methodName': 'Mastercard',
				'paymentMethodDetails_methodDescription': 'Pay by wireless card reader-Mastercard',
				'paymentMethodDetails_sendToExtId' : '1',
				'paymentMethodDetails_recieveFromExtId': '2',
				'paymentMethodDetails_priceRounding': 0,
				'paymentExtDetails_sendParameters': [], //aditional  parameters that needs to be sent to Ext [{parameterName,parameterModel}]
				'paymentExtDetails_baseUrl': '', //In extDetails table- send to this url
				'paymentExtDetails_method': '', //In extDetails table- send using this methd(POST,GET)
				'paymentExtDetails_silentSend': 0, //In extDetails table- Do not show user input or sent parameters on screen
				'paymentExtDetails_silentRecieve': 0, //In extDetails table- Do not show user input or recieve parameters on screen
				'paymentExtDetails_recieveParameters': [
					{
						parameterName: 'Transaction Number',
						parameterModel: 'salesPayment_transactionNumber',
						parameterMask: '999?9?9?9?9?9?9?9?',
						isSuccessFactor: 1,
						successValue: ''
					}
				] //In extDetails table- recieved parameters transformation table [{parameterName,parameterModel,parameterMask,isSuccessFactor,successValue}]
			},
			{
				'paymentMethodDetails_methodId' : '4',
				'paymentMethodDetails_methodCode': 'VISAN',
				'paymentMethodDetails_currencyId': 'CAD',
				'paymentMethodDetails_methodName': 'Visa',
				'paymentMethodDetails_methodDescription': 'Pay by wireless card reader-Visa',
				'paymentMethodDetails_sendToExtId' : '',
				'paymentMethodDetails_recieveFromExtId': '',
				'paymentMethodDetails_priceRounding': 0,
				'paymentExtDetails_sendParameters': [], //aditional parameters that needs to be sent to Ext
				'paymentExtDetails_baseUrl': '', //In extDetails table- send to this url
				'paymentExtDetails_method': '', //In extDetails table- send using this methd(POST,GET)
				'paymentExtDetails_silentSend': 0, //In extDetails table- Do not show user input or sent parameters on screen
				'paymentExtDetails_silentRecieve': 0, //In extDetails table- Do not show user input or recieve parameters on screen
				'paymentExtDetails_recieveParameters': [] //In extDetails table- recieved parameters transformation table
			},
			{
				'paymentMethodDetails_methodId' : '5',
				'paymentMethodDetails_methodCode': 'CARD',
				'paymentMethodDetails_currencyId': 'CAD',
				'paymentMethodDetails_methodName': 'Card',
				'paymentMethodDetails_methodDescription': 'Pay by connected card reader',
				'paymentMethodDetails_sendToExtId' : '',
				'paymentMethodDetails_recieveFromExtId': '',
				'paymentMethodDetails_priceRounding': 0,
				'paymentExtDetails_sendParameters': [], //aditional parameters that needs to be sent to Ext
				'paymentExtDetails_baseUrl': '', //In extDetails table- send to this url
				'paymentExtDetails_method': '', //In extDetails table- send using this methd(POST,GET)
				'paymentExtDetails_silentSend': 0, //In extDetails table- Do not show user input or sent parameters on screen
				'paymentExtDetails_silentRecieve': 0, //In extDetails table- Do not show user input or recieve parameters on screen
				'paymentExtDetails_recieveParameters': [] //In extDetails table- recieved parameters transformation table
			}
		];
		/////////////////////////////////////
		/* Should read above from database */
		////////////////////////////////////
		
		
		$scope.defaultCurrency = $filter('filter')($scope.currencies, {isDefault: '1'})[0];
		$scope.items = items;
		$scope.salePayments = salePayments;
		var handledPaymentsNumber = 0;
		var successfulPaymentsNumber = 0;
		$scope.newLine = function(previousCurrencyId){
			let defaultCurrencyId;
			if(previousCurrencyId){
				defaultCurrencyId = previousCurrencyId;
			}else{
				defaultCurrencyId = $scope.defaultCurrency.currencyId;
			}
			let salesItem_offlineSalesItemId = guid.newuuid('salesItemId');
			var item = {
				salesItem_salesHeaderId: $scope.reciept.salesHeader_offlineSalesId,
				salesItem_offlineSalesItemId: salesItem_offlineSalesItemId,
				itemsDetail_itemName: '',
				itemsDetail_itemBarcode: '',
				salesItem_quantity: '',
				itemsPrice_currencyId: defaultCurrencyId,
				itemsPrice_price: '',
				salesItem_discount: []
			}
			/*var headerDiscount = {
				salesHeader_offlineSalesId: '',
				salesHeaderDiscount_percent: 0,
				salesHeaderDiscount_amount: 0,
				salesHeaderDiscount_markdownId: ''
			}*/
			$scope.items.push(item);
			setTimeout(function(){
				document.querySelector('#barcode_'+$scope.items.length).focus();
			},0);
		}
		
		$scope.InitializeSaleScreen = function(){
			$scope.form = {};
			$scope.total = {};
			$scope.total.totalAfterTax = 0;
			$scope.totalPayment = {};

			$scope.reciept = {
				salesHeader_tillId: tillId,
				salesHeader_offlineSalesId: guid.newuuid('salesId'),
				salesHeader_createTime: moment.utc().format("YYYY-MM-DD hh:mm:ss"),
				salesHeader_offlineReceiptNumber: guid.createNewNumeric('sales', salesScreen.tillDetails.storesDetail_storeCode + salesScreen.tillDetails.tillsDetail_tillNumber),
				salesHeader_discount: [],
				salesItem: [],
				salesPayment: []
			};
			$scope.parentobj.selectedItemsArray = {};
			$scope.salesScreen = salesScreen;
			$scope.selectedScreen = $filter('filter')(salesScreen.screens, {screenName: 'main'})[0];
			$scope.items = [];
			$scope.salePayments = [];
			$scope.formDisabled = 0;
			$scope.fields = [];
			$scope.paymentRecievedParameters = {};
			$scope.paidVsTotal = 0;
			$scope.newLine();
			handledPaymentsNumber = 0;
		}
		$scope.InitializeSaleScreen();
		
		$scope.addDiscount  = function(item={}){
			if (item && item.constructor === Object && Object.keys(item).length > 0){
				let itemDiscount = {
					salesItem_offlineSalesItemId: item.salesItem_offlineSalesItemId,
					salesItemDiscount_precent: '',
					salesItemDiscount_amount: '',
					salesItemDiscount_markdownId: ''
				}
				item['salesItem_discount'].push(itemDiscount);
			}else{
				if($scope.reciept && $scope.reciept.salesHeader_discount){
					let headerDiscount = {
						salesHeaderDiscount_salesHeaderId: $scope.reciept.salesHeader_offlineSalesId,
						salesHeaderDiscount_percent: '',
						salesHeaderDiscount_amount: '',
						salesHeaderDiscount_markdownId: ''
					}
					$scope.reciept.salesHeader_discount.push(headerDiscount);
				}
			}
		}
		
		$scope.changeScreen = function(screenId){
			$scope.selectedScreen = $filter('filter')($scope.salesScreen.screens, {screenId: screenId})[0];
		}
		
		$scope.removePaymentMethod = function(index){
			$scope.salePayments.splice(index,1);
			$scope.makePaymentTotals(0);
		}
		$scope.makePaymentTotals = function(zeroRoundingDiscount=0){
			let totalPaidAmount = 0;
			let paymentRoundingDiscount = 0;
			if(!$scope.salePayments){
				$scope.salePayments = [];
			}
			for (var i = 0; i < $scope.salePayments.length; i++){
				totalPaidAmount = parseFloat(totalPaidAmount) + parseFloat($scope.salePayments[i].salesPayment_paidAmount);
				if(zeroRoundingDiscount == 1){
					if($scope.salePayments[i].salesPayment_paymentMethodRounding){
						$scope.salePayments[i].salesPayment_paymentMethodRounding = 0;
					}
				}
				paymentRoundingDiscount = parseFloat(paymentRoundingDiscount) + parseFloat($scope.salePayments[i].salesPayment_paymentMethodRounding);
			}
			$scope.totalPayment.totalAmount = totalPaidAmount;
			$scope.totalPayment.totalPaymentMethodRoundingDiscount = paymentRoundingDiscount;
			if($scope.rounding.exp && $scope.roundingType){
				$scope.totalPayment.totalAmount = priceRounding.round($scope.totalPayment.totalAmount, $scope.rounding.exp, $scope.roundingType);
				$scope.totalPayment.totalPaymentMethodRoundingDiscount = priceRounding.round($scope.totalPayment.totalPaymentMethodRoundingDiscount, $scope.rounding.exp, $scope.roundingType);
			}
		}
		$scope.selectPaymentMethod = function(methodId){
			let zeroRoundingDiscount = 0;
			let totalPaidAmount = 0;
			if(!$scope.salePayments){
				$scope.salePayments = [];
			}
			$scope.makePaymentTotals(0);
			
			let methodDetails = $filter('filter')($scope.allowedPaymentMethods, {paymentMethodDetails_methodId: methodId})[0];
			let paymentMethodRounding = 0;
			let suggestedAmount = parseFloat($scope.total.totalAfterTax) - parseFloat($scope.totalPayment.totalAmount);

			if(methodDetails.paymentMethodDetails_priceRounding){
				if($scope.cashRounding[methodDetails.paymentMethodDetails_currencyId].exp && $scope.cashRoundingType){
					suggestedAmount = priceRounding.priceRound(suggestedAmount, $scope.cashRounding[methodDetails.paymentMethodDetails_currencyId].exp,  $scope.cashRoundingType);
					paymentMethodRounding = parseFloat($scope.total.totalAfterTax) - parseFloat(suggestedAmount);
					if($scope.rounding.exp && $scope.roundingType){
						paymentMethodRounding = priceRounding.round(paymentMethodRounding, $scope.rounding.exp, $scope.roundingType);
					}
				}
			}else{
				if($scope.totalPayment.totalPaymentMethodRoundingDiscount){
					zeroRoundingDiscount = 1;
				}
			}

			if($scope.rounding.exp && $scope.roundingType){
				suggestedAmount = priceRounding.round(suggestedAmount, $scope.rounding.exp, $scope.roundingType);
			}
			
			let newPayment = {
				salesPayment_salesHeaderId: $scope.reciept.salesHeader_offlineSalesId,
				salesPayment_offlinePaymentId: guid.newuuid('salesPaymentId'),
				salesPayment_methodId: methodDetails.paymentMethodDetails_methodId,
				salesPayment_methodCode: methodDetails.paymentMethodDetails_methodCode,
				salesPayment_methodName: methodDetails.paymentMethodDetails_methodName,
				salesPayment_paidAmount: suggestedAmount,
				salesPayment_currencyId: methodDetails.paymentMethodDetails_currencyId,
				salesPayment_paymentMethodRounding: paymentMethodRounding,
				salesPayment_paymentStatus: 0,
				salesPayment_createTime: moment.utc().format("YYYY-MM-DD hh:mm:ss")
			};

			if(!$scope.salePayments){
				$scope.salePayments = [];
			}
			$scope.salePayments.push(newPayment);
			$scope.makePaymentTotals(zeroRoundingDiscount);
			zeroRoundingDiscount = 0;
		}
		$scope.equateAllValues = function(itemArray, itemProperty, index, callback=undefined, callbackVariable=undefined){
			for (var i = 0, len = itemArray.length; i < len; i++) {
				itemArray[i][itemProperty] = itemArray[index][itemProperty];
				if(callback && callbackVariable=='all'){
					 if(itemArray[i]['salesItem_quantity'] || itemArray[i]['itemsDetail_itemBarcode']){
						callback(i,i);
					}
				}
			}
			$scope.previousValue = itemArray[index][itemProperty];
			
		}
		$scope.removeItem = function(index){
			let defaultCurrencyId = $scope.items[index]['itemsPrice_currencyId'];
			$scope.items.splice(index,1);
			if(index-1 >= 0){
				$scope.gotoNextLine(index-1,2);
			}else if(index+1 == $scope.items.length){
				$scope.gotoNextLine(index+1,3);
			}else if($scope.items.length==0){
				$scope.newLine(defaultCurrencyId);
			}
		}
		
		$scope.lineValueChanged = function(index){
			$scope.makeLineValues(index);
			$scope.makeTotals();
		}

		$scope.parseFloat = parseFloat;
		
		$scope.makeLineValues = function(index){
			var lineAfterDiscount = $scope.items[index]['itemsPrice_price'] * $scope.items[index]['salesItem_quantity'];
			for(var t=0;t<$scope.items[index].salesItem_discount.length;t++){
				if($scope.items[index].salesItem_discount[t]['salesItemDiscount_precent']){
					lineAfterDiscount = lineAfterDiscount - (lineAfterDiscount * $scope.items[index].salesItem_discount[t]['salesItemDiscount_precent']/100)
				}else if($scope.items[index].salesItem_discount[t]['salesItemDiscount_amount']){
					lineAfterDiscount = lineAfterDiscount - $scope.items[index].salesItem_discount[t]['salesItemDiscount_amount'];
				}
			}
			$scope.items[index]['salesItem_afterDiscount'] = priceRounding.round(lineAfterDiscount, $scope.rounding.exp, $scope.roundingType);

			if(!$scope.items[index]['salesItem_scanTime']){
				$scope.items[index]['salesItem_scanTime'] = moment.utc().format("YYYY-MM-DD hh:mm:ss");
			}
		}

		$scope.makeTotals = function(){
			var total = {};
			total.totalQty = 0;
			total.totalDiscount = 0;
			total.totalPrice = 0;
			total.totalAfterDiscount = 0;
			total.totalAfterTax = 0;
			total.totalTax = 0;
			total.recieptDiscount = 0;
			total.afterRecieptDiscount = 0;
			total.tax = {};
			$scope.reciept['salesHeader_finishTime'] = moment.utc().format("YYYY-MM-DD hh:mm:ss");
			
			for (var i = 0, len = $scope.items.length; i < len; i++) {
				if(parseInt($scope.items[i]['salesItem_quantity'])){
					total.totalQty = parseInt(total.totalQty) + parseInt($scope.items[i]['salesItem_quantity']);
				}
				
				if($scope.items[i]['itemsPrice_price'] * parseInt($scope.items[i]['salesItem_quantity'])){
					total.totalPrice = parseFloat(total.totalPrice) + (parseFloat($scope.items[i]['itemsPrice_price']) * parseInt($scope.items[i]['salesItem_quantity']));
				}
				if(parseFloat($scope.items[i]['salesItem_afterDiscount'])){
					total.totalAfterDiscount = parseFloat(total.totalAfterDiscount) + parseFloat($scope.items[i]['salesItem_afterDiscount']);
				}
				
				if(parseFloat(total.totalAfterDiscount) && parseFloat(total.totalPrice)){
					total.totalDiscount = parseFloat(total.totalPrice) - parseFloat(total.totalAfterDiscount);
				}
			}
			
			let afterReceiptDiscount = parseFloat(total.totalAfterDiscount);
			for(var t=0;t<$scope.reciept['salesHeader_discount'].length;t++){
				if($scope.reciept['salesHeader_discount'][t]['salesHeaderDiscount_percent']){
					afterReceiptDiscount = afterReceiptDiscount - (afterReceiptDiscount * parseFloat($scope.reciept['salesHeader_discount'][t]['salesHeaderDiscount_percent'])/100);
				}else if($scope.reciept['salesHeader_discount'][t]['salesHeaderDiscount_amount']){
					afterReceiptDiscount = afterReceiptDiscount - parseFloat($scope.reciept['salesHeader_discount'][t]['salesHeaderDiscount_amount']);
				}
			}
			total.afterRecieptDiscount = priceRounding.round(afterReceiptDiscount, $scope.rounding.exp, $scope.roundingType);

			total.recieptDiscount = total.totalAfterDiscount - total.afterRecieptDiscount;
			
			for (var i = 0, len = $scope.taxes.length; i < len; i++) {
				total.totalTax = parseFloat(total.totalTax) + (parseFloat($scope.taxes[i].taxDetails_percent) * parseFloat(total.afterRecieptDiscount))/100;
				total.tax[$scope.taxes[i]['taxDetails_id']] = priceRounding.round(parseFloat($scope.taxes[i]['taxDetails_percent'])/100 * parseFloat(total.afterRecieptDiscount), $scope.rounding.exp, $scope.roundingType);
			}
			total.totalAfterTax = total.totalTax + total.afterRecieptDiscount;
			
			total.totalDiscount = priceRounding.round(total.totalDiscount, $scope.rounding.exp, $scope.roundingType);
			total.totalPrice = priceRounding.round(total.totalPrice, $scope.rounding.exp, $scope.roundingType);
			total.totalAfterDiscount = priceRounding.round(total.totalAfterDiscount, $scope.rounding.exp, $scope.roundingType);
			total.afterRecieptDiscount = priceRounding.round(total.afterRecieptDiscount, $scope.rounding.exp, $scope.roundingType);
			total.recieptDiscount = priceRounding.round(total.recieptDiscount, $scope.rounding.exp, $scope.roundingType);
			total.totalAfterTax = priceRounding.round(total.totalAfterTax, $scope.rounding.exp, $scope.roundingType);
			$scope.total = total;
		}
		$scope.gotoNextLine = function(index, debug=0){
			if(itemsDb){
			$log.info("should see this only once per enter"+debug);
				var itemDetails = localDb.selectItem('items',{'itemsDetail_itemBarcode_itemsPrice_currencyId': $scope.items[index]['itemsDetail_itemBarcode']+$scope.items[index]['itemsPrice_currencyId']}, 1, itemsDb);
			}
			if(itemDetails){
				if(itemDetails['itemsPrice_price']){
					$scope.items[index]['itemsPrice_price'] = itemDetails['itemsPrice_price'];
				}
				if(itemDetails['itemsDetail_itemName']){
					$scope.items[index]['itemsDetail_itemName'] = itemDetails['itemsDetail_itemName'];
				}
				$scope.items[index]['itemsDetail_itemId'] = itemDetails['itemsDetail_itemId'];
				if((index+1)==$scope.items.length){
					if(!$scope.items[index]['salesItem_quantity'] || $scope.items[index]['salesItem_quantity']==0){
						$scope.items[index]['salesItem_quantity'] = 1;
					}
					if($scope.form.saleDetails.$valid){
						$scope.newLine($scope.items[index]['itemsPrice_currencyId']);
					}
					$scope.makeTotals();
					
				}else{
					if($scope.items[$scope.items.length-1]['itemsDetail_itemBarcode']){
						if($scope.form.saleDetails.$valid){
							$scope.newLine($scope.items[index]['itemsPrice_currencyId']);
						}else{
							$scope.form.saleDetails.$error.required[0].$$element.focus();
						}
						$scope.makeTotals();
					}else{
						setTimeout(function(){
							document.querySelector('#barcode_'+$scope.items.length).focus();
						},0);
					}
				}
				$scope.makeLineValues(index);
			}else if(($scope.items[index]['salesItem_quantity'] || $scope.items[index]['itemsDetail_itemBarcode']) && (!$scope.items[index]['itemsDetail_itemName'])){
				$mdToast.show({
					hideDelay   : 5000,
					position    : 'top right',
					templateUrl : 'itemDetailsNotFound.html'
				});
				$scope.openItemSelect(index, $scope.items);
			}
			
		}
		$scope.selectItem = function(itemBarcode=''){
		
		}
		$scope.clickToClose = function(salesScreen){
		externalDevice.sendData("%clearScreen%","LINE_DISPLAY").then(function(response){
				externalDevice.sendData("Till Closed","LINE_DISPLAY");
				});
			setlog('',0);
			$mdDialog.cancel();
		}
		$scope.openItemSelect = function(index=undefined, items=[]){
			$mdDialog.show({
				scope: $scope,
		   		preserveScope: true,
				controller: itemSelectDialogController,
				templateUrl: 'ItemSelect.html',
				parent: angular.element(document.body),
				clickOutsideToClose: true,
				locals: {
					index: index,
					items: items
				},
				multiple: true,
				fullscreen: true
			})
			.then(function() {
			
			}, function() {
				
			});
		}
		
		$scope.calclulatePaidVsTotal = function(){
			var deffer = $q.defer();
			try{
					let totalPaidAmount = 0;
					let paymentRoundingDiscount = 0;
					for (var i = 0; i < $scope.salePayments.length; i++){
						totalPaidAmount = parseFloat(totalPaidAmount) + parseFloat($scope.salePayments[i].salesPayment_paidAmount);
						paymentRoundingDiscount = parseFloat(paymentRoundingDiscount) + parseFloat($scope.salePayments[i].salesPayment_paymentMethodRounding);
					}
		
					let paidAndDiscount = totalPaidAmount + paymentRoundingDiscount;
					let paidVsTotal = parseFloat($scope.total.totalAfterTax) - parseFloat(paidAndDiscount);
					if($scope.rounding.exp && $scope.roundingType){
						paidVsTotal = priceRounding.round(paidVsTotal, $scope.rounding.exp, $scope.roundingType);
					}
					if(paidVsTotal == 0){
						deffer.resolve({paidVsTotal: 0});
					}else{
						$mdDialog.show({
					   		preserveScope: false,
							controller: checkoutErrorDialogController,
							templateUrl: 'checkoutError.html',
							parent: angular.element(document.body),
							clickOutsideToClose: false,
							locals: {
								paidVsTotal: paidVsTotal
							},
							multiple: true,
							fullscreen: true
						})
						.then(function() {
							deffer.resolve({paidVsTotal: paidVsTotal});
						}, function() {
							deffer.resolve({status: -1});
						});
					}
					return deffer.promise;
			}
			catch(err){
				deffer.reject({status: -1, err: err});
				return deffer.promise;
			}
		}
		$scope.processPayment = function(discrepancy=0){
			/**
			********************
			*** Status Table ***
			********************
			1: success
			2: failed on attempt
			3: payment unsuccessful
			4: process attempted, but the result still unknown
			*/
			var deffer = $q.defer();
				try{
					$scope.formDisabled = 1;
					var totalPaymentNumber = $scope.salePayments.length;
					let processedPayments = [];
					
					for (var i = 0; i < totalPaymentNumber; i++){
						$scope.salePayments[i].processAttempTime = moment.utc().format("YYYY-MM-DD hh:mm:ss");
						let methodId = $scope.salePayments[i].salesPayment_methodId;
						let methodDetails = $filter('filter')($scope.allowedPaymentMethods, {paymentMethodDetails_methodId: methodId})[0];
						if(methodDetails['paymentMethodDetails_sendToExtId'] && methodDetails['paymentMethodDetails_sendToExtId']!==''){
							let baseUrl = methodDetails['paymentExtDetails_baseUrl'];
							if(baseUrl){
								let params = {};
								let data = {};
								if(methodDetails.paymentExtDetails_method == "GET"){
									params = methodDetails.paymentExtDetails_sendParameters;
								}else{
									data = methodDetails.paymentExtDetails_sendParameters;
								}
								$http({
									method : methodDetails.paymentExtDetails_method,
									data: data,
									params: params,
									url : baseUrl,
									headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
								}).then(function submitSuccess(response) {
									//check response adn success or fail
									let status = 4;
									
									processedPayments.push({paymentMethodId: methodId, status: status});
									if(processedPayments.length == totalPaymentNumber){
										deffer.resolve({processedPayments: processedPayments});
									}
								}, function submitError(response) {
									$scope.formDisabled = 0;
									let status = 2;
									processedPayments.push({paymentMethodId: methodId, status: status});
									if(processedPayments.length == totalPaymentNumber){
										deffer.resolve({processedPayments: processedPayments});
									}
								});
							}else{
								if(!methodDetails.paymentExtDetails_silentRecieve && methodDetails.paymentExtDetails_recieveParameters.length){
									$mdDialog.show({
								   		preserveScope: false,
										controller: paymentInputDialogController,
										templateUrl: 'paymentInput.html',
										parent: angular.element(document.body),
										clickOutsideToClose: false,
										locals: {
											fields: methodDetails.paymentExtDetails_recieveParameters,
											index: i
										},
										multiple: true,
										fullscreen: false
									})
									.then(function(response) {
										let status = 4;
										$scope.salePayments[response['index']].processFinishTime = moment.utc().format("YYYY-MM-DD hh:mm:ss");
										let successValues = $filter('filter')(methodDetails.paymentExtDetails_recieveParameters, {isSuccessFactor: 1});
										for (var t = 0; t < successValues.length; t++){
											if(successValues[t]['successValue'] == '' || response['parameters'][paymentExtDetails_recieveParameters[t]['parameterModel']] == successValues[t]['successValue']){
												status = 1;
											}else{
												status = 3;
											}
										}
										$scope.salePayments[response['index']].salesPayment_receivedResponse = response['parameters'];
										$scope.setPaymentStatus(status, $scope.salePayments[response['index']], totalPaymentNumber);
										$scope.formDisabled = 0;
										
										processedPayments.push({paymentMethodId: methodId, status: status});
										if(processedPayments.length == totalPaymentNumber){
											deffer.resolve({processedPayments: processedPayments});
										}
										//success
									}, function(response) {
										let status = 2;
										$scope.formDisabled = 0;
										$scope.salePayments[response['index']].processFinishTime = moment.utc().format("YYYY-MM-DD hh:mm:ss");
										$scope.setPaymentStatus(status, $scope.salePayments[response['index']], totalPaymentNumber);
										
										processedPayments.push({paymentMethodId: methodId, status: status});
										if(processedPayments.length == totalPaymentNumber){
											deffer.resolve({processedPayments: processedPayments});
										}
										//fail
									});
								}else if(!methodDetails.paymentExtDetails_recieveParameters.length){
									let status = 1;
									$scope.salePayments[i].processFinishTime = moment.utc().format("YYYY-MM-DD hh:mm:ss");
									$scope.setPaymentStatus(status, $scope.salePayments[i], totalPaymentNumber);
									
									processedPayments.push({paymentMethodId: methodId, status: status});
									if(processedPayments.length == totalPaymentNumber){
										deffer.resolve({processedPayments: processedPayments});
									}
								}
							}
						}else{
							//TO DO ADD methodDetails.paymentExtDetails_silentRecieve here too
							$scope.salePayments[i].processFinishTime = moment.utc().format("YYYY-MM-DD hh:mm:ss");
							$scope.setPaymentStatus(1, $scope.salePayments[i], totalPaymentNumber);
							
							processedPayments.push({paymentMethodId: methodId, status: status});
							if(processedPayments.length == totalPaymentNumber){
								deffer.resolve({processedPayments: processedPayments});
							}
						}
					}
					return deffer.promise;
				}
				catch(err){
					deffer.reject({processedPayments: [], err: err});
					return deffer.promise;
				}
		}
		$scope.createReceiptTemplate = function(receiptTemplateId='', receiptType='', tillId = '', settingsDb={}, db={}){
			//Get the template and print
			//salesId: unique id for receipt
			//receiptTemplate: template id
			var storeId = '';
			var receiptDetails = {};
			
			if(!tillId){
				let tillDetailsSetting = settings.getSettings('tillDetails', settingsDb, $scope.parentobj.setlog);
				if(tillDetailsSetting && tillDetailsSetting.value){
					tillId = tillDetailsSetting.value.tillId;
				}
			}

			if(tillId){
				let tillDetails = localStorageService.get("tillDetails_"+tillId);
				if(tillDetails && tillDetails.storesDetail_storeId){
					storeId = tillDetails.storesDetail_storeId;
				}
			}

			if(!receiptTemplateId && storeId && receiptType){
				receiptTemplateDetails = localDb.selectItem('receiptTemplates',{'storesIdreceiptsId_storeId': storeId, 'storesIdreceiptsId_receiptType': receiptType}, 1, undefined, undefined, db,1);
			}else if(receiptTemplateId && storeId){
				receiptTemplateDetails = localDb.selectItem('receiptTemplates',{'storesIdreceiptsId_storeId_receiptId': storeId+receiptTemplateId}, 1, undefined, undefined, db);
			}
			return receiptTemplateDetails;
		}
		
		$scope.createReceipt = function(receiptTemplateDetails = {}, salesId = '', settingsDb={}, db={}){
			var receiptDetails = {};
			var salesHeaderDetails = {};
			var salesItemDetails = {};
			var salesPaymentDetails = {};

			if(!receiptTemplateDetails || (Object.keys(receiptTemplateDetails).length === 0 && receiptTemplateDetails.constructor === Object)){
				receiptTemplateDetails = $scope.createReceiptTemplate('', 'sales', '', settingsDb, db);
			}
			
			if(receiptTemplateDetails && receiptTemplateDetails.receiptsDetail_receiptData){
				if(!salesId){
					salesHeaderDetails = localDb.selectItem('salesHeader',{}, 1, undefined, {sortBy: {column: 'offlineInsertTime', isdesc: 1}}, db);
					salesId = salesHeaderDetails.salesHeader_salesId;
				}else{
					salesHeaderDetails = localDb.selectItem('salesHeader',{'salesHeader_salesId': salesId}, 1, undefined, undefined, db);
				}
				if(salesId){
					//TODO: get other sales details
				}
			}
			if(receiptTemplateDetails && receiptTemplateDetails.receiptsDetail_receiptData){
				receiptDetails =  receiptTemplateDetails.receiptsDetail_receiptData;
			}
			return receiptDetails;
		}
		
		$scope.showReceipt = function(receiptDetails= {}, print= 1){
			if(!receiptDetails || (Object.keys(receiptDetails).length === 0 && receiptDetails.constructor === Object)){
				settings.getSettingsTable().then(function(dbDetails){
					let settingsDb = dbDetails.settingsDb;
					let db = dbDetails.db;
					receiptDetails = $scope.createReceipt({},'',settingsDb,db);
					showReceiptDialog(JSON.parse(receiptDetails));
				});
			}else{
				showReceiptDialog(receiptDetails);
			}
		}
		function showReceiptDialog(receiptDetails={}) {
			$mdDialog.show({
		   		preserveScope: false,
				controller: receiptPrintDialogController,
				templateUrl: 'printReceipt.html',
				parent: angular.element(document.body),
				clickOutsideToClose: true,
				locals: {
					receiptDetails: receiptDetails
				},
				multiple: true
			})
			.then(function() {
				
			}, function() {
			
			});
		}
		function receiptPrintDialogController(receiptDetails, $scope, $mdDialog) {
			if(!receiptDetails){
				receiptDetails = {};
			}
			if(!receiptDetails.options){
				receiptDetails.options = {};
				receiptDetails.options.customCss = "";
				receiptDetails.options.paperWidth = "";
			}
			
			receiptDetails.options.draggable = {enabled: false};
			receiptDetails.options.resizable = {enabled: false};		
			receiptDetails.options.disablePushOnDrag = true;
			receiptDetails.options.disablePushOnResize = true;
			receiptDetails.options.pushItems = false;
			receiptDetails.options.swap = false;
			if(!receiptDetails.screens || !receiptDetails.screens.length){
				receiptDetails.screens = [
					{
						items: [],
						screenId: "main",
						screenName: "main"
					}
				];
			}
			$scope.data = receiptDetails;
			$scope.selectedScreen = $filter('filter')($scope.data.screens, {screenName: 'main'})[0];

			$scope.clickToClose = function(){
				$mdDialog.hide();
			}
		}
		$scope.checkout = function(print=1){
			var deffer = $q.defer();
			try{
				return $scope.calclulatePaidVsTotal().then(function(response){
					return $scope.processPayment(response.paidVsTotal).then(function(response2){
						if(print==1){
							$scope.showReceipt();
						}
						return deffer.resolve(response2);
					}, function(error) {
						return deffer.reject(error);
					});
				}, function(error) {
					return deffer.reject(error);
				});
			}
			catch(err){
				deffer.reject({processedPayments: [], err: err});
				return deffer.promise;
			}
			return deffer.promise;
		}
		$scope.setPaymentStatus = function(status, salePayment, totalPaymentNumber){
			salePayment.salesPayment_paymentStatus = status; //4: unexpected response, 3: rejected, 2: cancelled, 1:success, 0:not tried
			$scope.saveSalesPayment(salePayment, 0, status, totalPaymentNumber);
		}
		/*$scope.saveSalesItem = function(retryTime=0, items=[]){
			if(retryTime==0){
				items = $scope.items;
				for (var i = 0; i < items.length; i++) {
					items[i].syncedOnline = 0;
				}
			}
			return offlineSales.initSales().then(function(dbParams){
				offlineSales.insertsalesItem(items, dbParams.salesItemDb, dbParams.db).then(function(response){
					if(response.status){
						return {status: 1};
					}else{
						if($scope.retryTimes && retryTime < $scope.retryTimes){
							errorHandling.saveError('salesItem', 'newInsert', retryTime, items, response.response);
							retryTime = retryTime + 1;
							$scope.saveSalesItem(retryTime, items);
						}else{
							return {status: 0};
						}
					}
				});
			});
		}*/
		$scope.saveSalesHeader = function(retryTime=0, salesHeader={}, saleItems=[], salePayments=[]){
			if(retryTime==0){
				salesHeader = $scope.reciept;
				salesHeader.total = $scope.total;
				salesHeader.totalPayment = $scope.totalPayment;
				salesHeader.syncedOnline = 0;
				salesHeader.salesItem = $scope.items;
				salesHeader.salesPayment = $scope.salePayments;
			}
			//TO DO: CHECK WHY IT IS NOT SAVING HERE
			return offlineSales.initSales().then(function(dbParams){
				return offlineSales.insertsalesHeader(salesHeader, dbParams.salesHeaderDb, dbParams.db).then(function(response){
					if(response.status){
						//SAVE FOR EXPORT
						let saleFormatted = tableMapsService.formatExportData(salesHeader, "salesHeader", tableMapsDb, $scope.parentobj.setlog);
							if(saleFormatted && saleFormatted.exportedData){
								tableMapsService.insertDataToExport(saleFormatted.exportedData,  $scope.parentobj.setlog).then(function(toExportResponse){
									if(toExportResponse.status){
										salesHeader.syncedOnline = 1;
										localDb.updateItem("salesHeader", salesHeader, dbParams.salesHeaderDb, dbParams.db).then(function(updateSyncHeader){
											if(updateSyncHeader.status){
												tableMapsService.exportToServer(toExportResponse.db, 3, 1000, 0, 0, 0, $scope.parentobj.setlog);												}
										});
									}
								});
							}
						return {status: 1};
					}else{
						if($scope.retryTimes && retryTime < $scope.retryTimes){
							errorHandling.saveError('salesHeader', 'newInsert', retryTime, salesHeader, response.response);
							retryTime = retryTime + 1;
							$scope.saveSalesHeader(retryTime, salesHeader);
						}else{
							return {status: 0};
						}
					}
				});
			});
		}
		$scope.saveSalesPayment = function(salePayment, retryTime=0, status, totalPaymentNumber=1){
			//TO DO: 
			/*if(retryTime==0){
				$scope.saveSalesHeader(0);
				$scope.saveSalesItem(0);
				salePayment.syncedOnline = 0;
				handledPaymentsNumber = handledPaymentsNumber + 1;
			}*/
			handledPaymentsNumber = handledPaymentsNumber + 1;
			if(status == 1){
				successfulPaymentsNumber = successfulPaymentsNumber + 1;
			}
			
			if(status == 1 && successfulPaymentsNumber == totalPaymentNumber){
				$scope.saveSalesHeader(0).then(function(response){
					if(response.status){
						$scope.InitializeSaleScreen();
						$mdToast.show({
							hideDelay   : 3000,
							position    : 'top right',
							controller  : 'ToastCtrl',
							templateUrl : 'saleSaved.html'
						});
					}else{
						
					}
				});
			}else if(handledPaymentsNumber==totalPaymentNumber){
				//some payments did not go through
			}
			$scope.parentobj.setlog('',0);
			$scope.formDisabled = false;
					/*offlineSales.insertPayment(salePayment, dbParams.salesPaymentDb, dbParams.db).then(function(response){
						if(response.status){
							
						}else{
							if($scope.retryTimes && retryTime < $scope.retryTimes){
								errorHandling.saveError('salesPayment', 'newInsert', retryTime, salePayment, response.response);
								retryTime = retryTime + 1;
								$scope.saveSalesPayment(salePayment, retryTime, status, totalPaymentNumber);
							}
						}
						if(status == 1 && handledPaymentsNumber==totalPaymentNumber){
							$scope.InitializeSaleScreen();
							$mdToast.show({
								hideDelay   : 3000,
								position    : 'top right',
								controller  : 'ToastCtrl',
								templateUrl : 'saleSaved.html'
							});
						}
						$scope.parentobj.setlog('',0);
						$scope.formDisabled = false;
					});*/
		}
	}
	function paymentInputDialogController(fields, index, $scope, $mdDialog){
		$scope.fields = fields;

		let paymentRecievedParameters = {};
		for (var j = 0; j < fields.length; j++){
			paymentRecievedParameters[fields[j]['parameterModel']] = '';
		}
		$scope.paymentRecievedParameters = paymentRecievedParameters;
		$scope.clickToClose = function(){
			$mdDialog.cancel({index: index});
		}
		$scope.clickToSave = function(){
			$mdDialog.hide({parameters: $scope.paymentRecievedParameters, index: index});
		}
	}
	function checkoutErrorDialogController(paidVsTotal, $scope, $mdDialog){
		$scope.paidVsTotal = paidVsTotal;
		$scope.clickToClose = function(){
			$mdDialog.cancel();
		}
		$scope.clickToSave = function(){
			$mdDialog.hide();
		}
	}
	function itemSelectDialogController($scope, index, items){
		$scope.parentobj.afterItemSingleSelectCallbackFunction = function(selectedItem){
			$mdDialog.hide();
		}
		$scope.clickToClose = function(){
			if(index && items.length >0 ){
				items[index]['itemsDetail_itemBarcode'] = '';
			}
			$mdDialog.cancel();
		}
	}
	
	$scope.parentobj.getSalesScreen = function(tillDetails, itemsDb = undefined, tableMapsDb=undefined){
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Sales Screen Details",1);
		if(tillId){
			var saleParameters = {
				action: "getSalesScreenDetails",
				tillId: tillDetails.tillsDetail_tillId
			};

			$http({
				method : "POST",
				url : "/sale.php",
				data: saleParameters,
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				$scope.parentobj.pageIsLoading = 0;
				if(response.data.status){
					$scope.privatedata.data = {};
					
					response.data.salesScreen.rows[0].salesScreenDetail_salesScreenData.options.draggable = {enabled: false};
					response.data.salesScreen.rows[0].salesScreenDetail_salesScreenData.options.resizable = {enabled: false};
					
					response.data.salesScreen.rows[0].salesScreenDetail_salesScreenData.options.disablePushOnDrag = true;
					response.data.salesScreen.rows[0].salesScreenDetail_salesScreenData.options.disablePushOnResize = true;
					response.data.salesScreen.rows[0].salesScreenDetail_salesScreenData.options.pushItems = false;
					response.data.salesScreen.rows[0].salesScreenDetail_salesScreenData.options.swap = false;
					
					$scope.privatedata.data.salesScreen = response.data.salesScreen.rows[0].salesScreenDetail_salesScreenData;
					$scope.privatedata.data.salesScreen.tillDetails = tillDetails;
					if(response.data.tillOptions){
						if(response.data.tillOptions.roundingSettings){
							
						}else{
						
						}
					}
					$scope.parentobj.setlog('',0);
					$scope.parentobj.OpenSalesScreen($scope.privatedata.data, [], [], itemsDb, tableMapsDb);		
				}else{
					$scope.parentobj.setlog(response.data.msg,0);
				}
			});
		}else{
			//show till select if allowed
			$scope.parentobj.setlog("Error in finding requested till details",0);
			$scope.parentobj.pageIsLoading = 0;
		}
	};
	
	settings.getSettingsTable().then(function(dbDetails){
		let settingsDb = dbDetails.settingsDb;
		let db = dbDetails.db;
		let tillDetailsSetting = settings.getSettings('tillDetails', settingsDb, $scope.parentobj.setlog);
		if(!tillDetailsSetting || !tillDetailsSetting.value){
			gotoTillOperation();
		}else{
			let tillDetails = tillDetailsSetting.value;
			tillId = tillDetails.tillId;
			tillNumber = tillDetails.tillNumber;
			offlineTillOperation.getTillOperationsDb().then(function(tillOperationDbDetails){
				let tillStatus = 0;
				//sync with online data
				offlineTillOperation.syncTillOperation(tillId, tillOperationDbDetails.tillOperationDb, tillOperationDbDetails.db, $scope.parentobj.setlog).then(function(tillOperationDetails){
					//get latest till operation whenre close or open happened
					tillStatus = offlineTillOperation.getTillStatus(tillId, tillOperationDbDetails.tillOperationDb);
					if(tillStatus[0] && tillStatus[0]['tillOperation_operationId']){
						if(!tillStatus.length || tillStatus[0].tillOperation_operationType=="close"){
							gotoTillOperation();
						}else{
							openSalesScreen(tillId);
						}
					}
				});
			});
		}
	});
	function gotoTillOperation(){
		$state.go('page', {pageCode: 'tillOperation'});
	}
	
	//TO DO: MAKE IT ABLE TO GET SEVERAL REQUESTS IN ONE CALL TO SERVER INSTEAD OF MULTIPLE
	//TO DO: EXPORT DATA AFTER SAVING TO DATABASE (FOR ITEMS aND PAYMETNS REMOVE EMPTY ROWS BEFORE EXPORTING)
	$scope.refreshData = function(tillId){
		return offlineItems.getItems(tillId, $scope.parentobj.setlog).then(function(response){
			if(response.status){
				let tillDetails = localStorageService.get('tillDetails_'+tillId);
				return receiptTemplatesService.getTillReceiptTemplates('', tillId, $scope.parentobj.setlog).then(function(receiptTemplateResponse){
					if(receiptTemplateResponse.status){
						return tableMapsService.getTableMaps(["salesHeader"], $scope.parentobj.setlog).then(function(tableMapsResponse){
							if(tableMapsResponse.status){
								offlineSales.initSales().then(function(dbParams){
									let notSyncedSales = dbParams.salesHeaderDb.find({'syncedOnline': 0});
									let notSyncedSalesFormatted = tableMapsService.formatExportData(notSyncedSales, "salesHeader", tableMapsResponse.tableMapsDb, $scope.parentobj.setlog);
									if(notSyncedSalesFormatted && notSyncedSalesFormatted.exportedData){
										tableMapsService.insertDataToExport(notSyncedSalesFormatted.exportedData,  $scope.parentobj.setlog).then(function(toExportResponse){
											if(toExportResponse.status){
												for(var j=0; j<notSyncedSales.length; j++){
													notSyncedSales[j].syncedOnline = 1;
												}
												localDb.updateItem("salesHeader", notSyncedSales, dbParams.salesHeaderDb, dbParams.db).then(function(updateSyncHeader){
													if(updateSyncHeader.status){
														tableMapsService.exportToServer(toExportResponse.db, 3, 1000, 0, 0, 0, $scope.parentobj.setlog);
													}
												});
											}
										});
									}
								});
								return {status: 1, tillDetails: tillDetails, itemsDb: response.itemsDb, tableMapsDb: tableMapsResponse.tableMapsDb};
							}
						});
					}
				});
			}
		});
	}

	function openSalesScreen(tillId){
		if(tillId){
			$scope.refreshData(tillId).then(function(response){
				if(response.status){
					$scope.parentobj.getSalesScreen(response.tillDetails, response.itemsDb, response.tableMapsDb);
				}
			});
		}else{
			gotoTillOperation();
		}
	}
	
});

app.service('offlineSales', function($http, $q, localDb, localStorageService, globals){
	this.initSales = function(){
		return $q(function (resolve, reject) {
			try {
				if(!globals.db){
					localDb.initilizeDb().then(function(db){
						let salesHeaderDb = localDb.getTable('salesHeader', db);
						let salesItemDb = localDb.getTable('salesItem', db);
						let salesPaymentDb = localDb.getTable('salesPayment', db);
						resolve({salesHeaderDb: salesHeaderDb, salesItemDb: salesItemDb, salesPaymentDb: salesPaymentDb, db: db});
					});
				}else{
					let salesHeaderDb = localDb.getTable('salesHeader', globals.db);
					let salesItemDb = localDb.getTable('salesItem', globals.db);
					let salesPaymentDb = localDb.getTable('salesPayment', globals.db);
					resolve({salesHeaderDb: salesHeaderDb, salesItemDb: salesItemDb, salesPaymentDb: salesPaymentDb, db: globals.db});
				}
			}
			catch(err) {
				reject(err.message);
			}
		});
	};
	this.insertPayment = function(paymentsToInsert,salesPaymentDb, db){
		let paymentToInsertArray = [];
		if(!paymentsToInsert.isArray){
			paymentToInsertArray.push(paymentsToInsert);
		}else{
			paymentToInsertArray = paymentsToInsert;
		}
		return localDb.insertItem('salesPayment', paymentToInsertArray, salesPaymentDb).then(function(response){
			db.throttledSaveDrain(function () {
				db.saveDatabase();
			});
			return ({status: 1, response: response});
		}, function (error) {
			return ({status: 0, response: error});
		}).catch(function (error) {
			return errorService.handleError({status: 0, response: error.message});
		});
	};
	this.insertsalesHeader = function(headersToInsert,salesHeaderDb, db){
		let headersToInsertArray = [];
		if(!headersToInsert.isArray){
			headersToInsertArray.push(headersToInsert);
		}else{
			headersToInsertArray = headersToInsert;
		}
		return localDb.insertItem('salesHeader', headersToInsertArray, salesHeaderDb).then(function(response){
			db.throttledSaveDrain(function () {
				db.saveDatabase();
			});
			return ({status: 1, response: response});
		}, function (error) {
			return ({status: 0, response: error});
		}).catch(function (error) {
			return errorService.handleError({status: 0, response: error.message});
		});
	};
	this.insertsalesItem = function(itemsToInsert,salesItemDb, db){
		let itemsToInsertArray = [];
		if(!itemsToInsert.isArray){
			itemsToInsertArray.push(itemsToInsert);
		}else{
			itemsToInsertArray = itemsToInsert;
		}
		return localDb.insertItem('salesItem', itemsToInsertArray, salesItemDb).then(function(response){
			db.throttledSaveDrain(function () {
				db.saveDatabase();
			});
			return ({status: 1, response: response});
		}, function (error) {
			return ({status: 0, response: error});
		}).catch(function (error) {
			return errorService.handleError({status: 0, response: error.message});
		});
	};
});