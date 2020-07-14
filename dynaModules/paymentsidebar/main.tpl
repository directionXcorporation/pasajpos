<md-content id="paymentSideBar">
    <md-list flex class="md-dense">
        <div data-ng-repeat="(key, msg) in $ctrl.receipt[0].msg" class="md-info" data-ng-if="msg">
            {{msg}}
        </div>
        <div data-ng-if="$ctrl.receipt[0].total.totalAfterTax != $ctrl.receipt[0].total.totalPaidAmount" class="md-info" id="remainingAmountToPay_content">
            <span>Payment is not complete</span>
        </div>
        <md-subheader class="md-no-sticky" translate>Receipt Details</md-subheader>
        <md-list-item class="md-3-line" ng-click="null">
            <div class="md-list-item-text" layout="column">
                <h4><span translate>Receipt Id:</span> {{$ctrl.receipt[0].salesHeader_offlineSalesId}}</h4>
                <h4><span translate>Receipt Total:</span> {{$ctrl.receipt[0].total.totalPrice}}</h4>
                <h4><span translate>Total Discount:</span> {{$ctrl.receipt[0].total.totalDiscount}}</h4>
                <h4><span translate>Total After Discount:</span> {{$ctrl.receipt[0].total.totalAfterDiscount}}</h4>
                <h4><span translate>Total Tax:</span> {{$ctrl.receipt[0].total.totalTax}}</h4>
                <h4><span translate>Total Payable:</span> {{$ctrl.receipt[0].total.totalAfterTax}}</h4>
            </div>
        </md-list-item>
    </md-list>
    <md-divider ></md-divider>
    <div layout="row" flex>
        <div layout='column' flex>
            <div flex>
                <div layout='column' data-ng-repeat='tax in $ctrl.receipt[0].salesTax'>
                    <div flex><b>{{::tax.salesHeaderTax_taxName}} ({{::tax.salesHeaderTax_taxPercent}}%):</b> {{::tax.salesHeaderTax_taxAmount}}
                    </div>
                </div>
                <div flex style='text-align: right;'>
                    <b translate>Total Tax:</b> {{$ctrl.receipt[0].total.totalTax}}
                </div>
                <div flex style='text-align: right;'>
                    <b translate>Base Total:</b> {{$ctrl.receipt[0].total.totalAfterDiscount}}
                </div>
                <div flex style='text-align: right;'>
                    <b translate>Total Payable:</b> {{$ctrl.receipt[0].total.totalAfterTax}}
                </div>
            </div>
        </div>
    </div>
</md-content>