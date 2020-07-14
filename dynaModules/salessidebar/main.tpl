<md-content>
    <div data-ng-repeat="(key, msg) in $ctrl.receipt[0].msg" class="md-info" data-ng-if="msg">
        {{msg}}
    </div>
    <md-list flex class="md-dense">
        <md-subheader class="md-no-sticky">Receipt Details</md-subheader>
        <md-list-item class="md-3-line" ng-click="null">
            <div class="md-list-item-text" layout="column">
                <h4>Receipt Id: {{$ctrl.receipt[0].salesHeader_offlineSalesId}}</h4>
                <h4>Creation Time: {{$ctrl.receipt[0].salesHeader_createTime}}</h4>
                <h4>Store Code: {{$ctrl.receipt[0].salesHeader_storeCode}}</h4>
                <h4>Till Code: {{$ctrl.receipt[0].salesHeader_tillCode}}</h4>
                <h4>Customer Name: {{$ctrl.receipt[0].usersIdsalesHeaderId[0].usersIdsalesHeaderId_firstName}} {{$ctrl.receipt[0].usersIdsalesHeaderId[0].usersIdsalesHeaderId_lastName}}</h4>
                <h4>Customer Phone: {{$ctrl.receipt[0].usersIdsalesHeaderId[0].usersIdsalesHeaderId_cellphone}}</h4>
                <h4>Customer Email: {{$ctrl.receipt[0].usersIdsalesHeaderId[0].usersIdsalesHeaderId_email}}</h4>
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
                    <b>Total Tax:</b> {{$ctrl.receipt[0].total.totalTax}}
                </div>
                <div flex style='text-align: right;'>
                    <b>Base Total:</b> {{$ctrl.receipt[0].total.totalAfterDiscount}}
                </div>
                <div flex style='text-align: right;'>
                    <b>Total:</b> {{$ctrl.receipt[0].total.totalAfterTax}}
                </div>
            </div>
        </div>
    </div>
</md-content>