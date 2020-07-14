<div class="w3-container" data-ng-controller="parentobj.controllers.users">
	<md-card data-ng-repeat="user in parentobj.privatedata.users">
		<md-card-title>
			<md-card-title-media>
				<div class="md-media-sm card-media" layout>
					<md-icon aria-label="User Icon" md-font-icon="person" style="color:grey" data-fittext>person</md-icon>
				</div>
			</md-card-title-media>
			<md-card-title-text data-ng-hide="user.editing">
				<span class="md-headline">{{user.gender==='1' ? "Mr." : user.gender === '2' ? "Ms." : ""}} {{user.firstName}}&nbsp;{{user.lastName}}</span>
				<span class="md-subhead description">{{user.username}}</span>
			</md-card-title-text>
			<md-card-actions layout="row" layout-align="end center">
				<md-button class="md-raised" id="view_{{user.userId}}" data-ng-click="parentobj.getUserDetails(user,false);">
					<md-icon aria-label="User View Icon" md-font-icon="remove_red_eye" style="color:grey" data-fittext>remove_red_eye</md-icon>
				</md-button>
				<md-button class="md-raised" id="edit_{{user.userId}}" data-ng-click="parentobj.getUserDetails(user,true);">
					<md-icon aria-label="User Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
				</md-button>
			</md-card-actions>
		</md-card-title>
	</md-card>
	
	<script type="text/ng-template" id="userChangeOK.html">
		<md-toast>
			{{_User updated successfully}}
		</md-toast>
	</script>
	<script type="text/ng-template" id="editUser.html">
		<md-dialog aria-label="{{ editAllowed ? 'Editting' : 'Viewing'}} {{record.firstName}} {{record.lastName}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{ editAllowed ? '{{_EDITING}}' : '{{_VIEWING}}' }} {{record.firstName}} {{record.lastName}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(record)">
						<md-icon aria-label="Clse User Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<!--<md-whiteframe class="msgholder md-whiteframe-3dp error" flex="">{{responseErrText}}</md-whiteframe>-->
				<md-tabs md-dynamic-height md-border-bottom>
					<md-tab label="{{_BASIC_DETAILS}}">
						<md-content class="md-padding">
							<fieldset data-ng-disabled="!editAllowed">
								<form name="userDetails">
									<div layout-gt-xs="row">
										<md-radio-group ng-model="record.gender">
											<md-radio-button value="1">Mr.</md-radio-button>
											<md-radio-button value="2">Ms.</md-radio-button>
											<md-radio-button value="0">-</md-radio-button>
										</md-radio-group>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_FIRST_NAME}}</label>
											<input data-ng-model="record.firstName" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_LAST_NAME}}</label>
											<input data-ng-model="record.lastName" />
										</md-input-container>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab>
						<md-tab-label>{{_LOGIN_DETAILS}}<md-icon md-font-icon="error_outline" flex="" style="color:red;font-size:20px;" data-ng-if="userLogin.$invalid">error_outline</md-icon></md-tab-label>
						<md-tab-body>
							<md-content class="md-padding">
								<fieldset data-ng-disabled="!editAllowed">
									<form name="userLogin">
										<div layout-gt-xs="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_USERNAME}}</label>
												<input data-ng-model="record.username" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_PASSWORD}}</label>
												<input type="{{changeUserPasswordType}}" id="changeUserPassword" name="password" data-ng-model="record.password" />
												<div data-ng-messages="userLogin.password.$error" data-ng-show="true">
												      <div data-ng-message="passwordRequiredError">{{_Password is required to change the username}}</div>
												</div>
											</md-input-container>
										</div>
									</form>
									<form name="userPin">
										<div layout-gt-xs="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs data-ng-if="record.pinLoginStatus">
												<label>{{_PINCODE}}</label>
												<input type="number" id="changeUserPinCode" name="pinCode" data-ng-model="record.pinCode" mask="9999" reject="true" clean="false" validate="true" use-keyboard="Numbers" />
											</md-input-container>
										</div>
									</form>
								</fieldset>
							</md-content>
						</md-tab-body>
					</md-tab>
					<md-tab>
						<md-tab-label>{{_STATUS_DETAILS}}<md-icon md-font-icon="error_outline" flex="" style="color:red;font-size:20px;" data-ng-if="userStatus.$invalid">error_outline</md-icon></md-tab-label>
						<md-tab-body>
							<md-content class="md-padding">
								<fieldset data-ng-disabled="!editAllowed">
									<form name="userStatus">
										<div layout-gt-xs="column">
											<div layout="row">
												<md-input-container class="md-icon-float md-block" flex-gt-xs>
													<md-switch ng-model="record.userStatus" aria-label="{{_USER_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
														{{_USER_STATUS}}
													</md-switch>
												</md-input-container>
												<md-datepicker data-ng-if="record.userStatus" data-ng-model="record.statusStartDate" md-placeholder="{{_Access Start Date}}" md-open-on-focus acceptallzeroasdate="true"></md-datepicker>
												<md-datepicker data-ng-if="record.userStatus" data-ng-model="record.statusEndDate" md-placeholder="{{_Access End Date}}" md-open-on-focus acceptallzeroasdate="true"></md-datepicker>
											</div>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="record.passwordLoginStatus" aria-label="{{_PASSWORD_LOGIN_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_PASSWORD_LOGIN_STATUS}}
												</md-switch>
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="record.fingerprintLoginStatus" aria-label="{{_FINGERPRINT_LOGIN_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_FINGERPRINT_LOGIN_STATUS}}
												</md-switch>
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="record.pinLoginStatus" aria-label="{{_PIN_LOGIN_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_PIN_LOGIN_STATUS}}
												</md-switch>
											</md-input-container>
										</div>
									</form>
								</fieldset>
							</md-content>
						</md-tab-body>
					</md-tab>
					<md-tab label="{{_GROUP_DETAILS}}">
						<md-content class="md-padding">
							<fieldset data-ng-disabled="!editAllowed">
								<form name="userGroups">
									<div layout-gt-xs="row">
										<treeselect data-ng-repeat="parent in privatedata.treeView" family="parent" record="record" itemclick="toggleselectedgroups(scopeGroupId,scopeRecord)"></treeselect>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_FUNCTION_DETAILS}}">
						<md-content class="md-padding">
							<fieldset data-ng-disabled="!editAllowed">
								<form name="userFunctions">
									<div layout-gt-xs="column">
										<md-virtual-repeat-container id="functions-vertical-container" style="height:200px;width:100%;">
											
											<div md-virtual-repeat="functionDetails in parentobj.privatedata.allfunctions" class="repeated-item"  flex>
												<md-checkbox value="{{functionDetails.functionCode}}" data-ng-checked="(record.functions|filter:{functionCode:functionDetails.functionCode}:true).length > 0" data-ng-disabled="(record.functions|filter:{functionCode:functionDetails.functionCode}:true).length > 0 && !(record.functions|filter:{functionCode:functionDetails.functionCode}:true)[0]['userId'] || 1" layout="row">
													{{functionDetails.functionName}}
													<md-tooltip md-direction="right" data-ng-if="(record.functions|filter:{functionCode:functionDetails.functionCode}:true).length > 0">{{_Obtained from group}}: '{{(record.functions|filter:{functionCode:functionDetails.functionCode}:true)[0]['groupName']}}'</md-tooltip>
												</md-checkbox>
											</div>
										</md-virtual-repeat-container>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{property.name}}" data-ng-repeat="property in record.properties">
						<md-content class="md-padding">
							<fieldset data-ng-disabled="!editAllowed">
								<form name="{{property.name}}">
									<div layout-gt-xs="row">
										<md-input-container class="md-icon-float md-block" flex-gt-xs data-ng-repeat="item in property.nodes">
											<label>item.name</label>
											<input data-ng-model="record.properties[property.name][item.name]" />
										</md-input-container>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
				 </md-tabs>
				 <md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button data-ng-if="editAllowed" class="md-raised md-primary" data-ng-click="clickToSave(record)" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose(record)">
						{{editAllowed ? '{{_CANCEL}}' : '{{_CLOSE}}'}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
</div>
