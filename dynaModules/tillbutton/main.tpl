<md-button class='till-button md-raised md-primary {{$ctrl.class}}' data-ng-if="$ctrl.onclickaction" data-ng-click='$ctrl.onclickaction({parameters: parameters});' data-ng-disabled='$ctrl.disable' id='{{$ctrl.id}}_content'>{{$ctrl.text}}</md-button