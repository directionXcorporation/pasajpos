<section layout="column" flex="grow" data-ng-controller="loginRegisterForm">
	<h2>{{Login using password or Fingerprint}} / {{Register new account}}</h2>
	<div layout="row" flex="grow">
		<div>
			<md-button class="md-raised md-primary" data-ng-click="openLogin();" id="openLogin">{{Login}}</button>
		</div>
		<div>
			<md-button class="md-raised md-primary" data-ng-click="openRegister();" id="openRegister">{{Register}}</button>
		</div>
	</div>
	<!--LOGIN FORM HOLDER-->
	<script type="text/ng-template" id="login.html">
		<md-dialog aria-label="Login">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2><md-icon aria-label="Login" md-font-icon="security" style="font-size:2vw;">security</md-icon><span flex>{{Login}}</span></h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close dialog" md-font-icon="close" style="font-size:2vw;">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content style="max-width:800px;max-height:810px;">
				<md-progress-linear md-mode="query" data-ng-disabled="!FormDisabled"></md-progress-linear>
				<md-content class="md-padding">
						<div id="loginformholder">
								<div layout="column">
									<div class="msgholder">{{responseErrText}}</div>
									<form method="POST" data-ng-model="loginform" name="loginform">
										<div layout="column">
											<md-input-container class="md-icon-float md-block">
												<label>{{Username}}</label>
												<md-icon aria-label="Username" md-font-icon="perm_identity" style="display: inline; font-size:2vw;">perm_identity</md-icon>
												<input type="text" name="username" data-ng-model="privatedata.username" required data-ng-disabled="FormDisabled" flex="grow" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block">
												<label>{{Password}}</label>
												<md-icon aria-label="Password" md-font-icon="vpn_key" style="display: inline; font-size:2vw;">vpn_key</md-icon>
												<input type="password" name="password" required data-ng-model="privatedata.password" data-ng-disabled="FormDisabled" flex="grow">
											</md-input-container>
										</div>
									</form>
									<div>
										<a href="#">{{Forgot password}}??</a>
									</div>
								</div>
						</div>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;">
					<md-button data-ng-click="clickToLogin()" data-ng-disabled="FormDisabled || loginform.$invalid" class="md-raised md-primary">
						{{Login}}
					</md-button>
					<span flux></span>
					<md-button data-ng-click="clickToClose()" class="md-raised">
						{{Cancel}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<!-- REGISTER FORM HOLDER -->
	<script type="text/ng-template" id="register.html">
		<md-dialog aria-label="Register">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2><md-icon aria-label="Login" md-font-icon="account_circle" style="font-size:2vw;">account_circle</md-icon>{{Register}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close dialog" md-font-icon="close" style="font-size:2vw;">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content style="max-width:800px;max-height:810px;">
				<md-progress-linear md-mode="query" data-ng-disabled="!FormDisabled"></md-progress-linear>
				<md-content class="md-padding">
						<div id="registerformholder">
								<div layout="column">
									<div class="msgholder">{{responseErrText}}</div>
									<form method="POST" data-ng-model="registerform" name="registerform">
										<div layout="column">
											<md-input-container class="md-icon-float md-block">
												<label>{{Email}}({{Username}})</label>
												<md-icon aria-label="Username" md-font-icon="perm_identity" style="display: inline; font-size:2vw;">perm_identity</md-icon>
												<input type="text" name="username" data-ng-model="privatedata.username" required data-ng-disabled="FormDisabled" flex="grow" />
											</md-input-container>
											
											<md-input-container class="md-icon-float md-block">
												<label>{{Password}}</label>
												<md-icon aria-label="Password" md-font-icon="vpn_key" style="display: inline; font-size:2vw;">vpn_key</md-icon>
												<input type="password" name="password" required data-ng-model="privatedata.password" data-ng-disabled="FormDisabled" flex="grow">
											</md-input-container>
											
											<!--name-->
											<div layout="row">
												<md-radio-group ng-model="privatedata.gender">
													<md-radio-button value="1">{{Mr}}.</md-radio-button>
													<md-radio-button value="2">{{Ms}}.</md-radio-button>
													<md-radio-button value="0">-</md-radio-button>
												</md-radio-group>
												
												<md-input-container class="md-icon-float md-block">
													<label>{{First Name}}</label>
													<md-icon aria-label="First Name" md-font-icon="face" style="display: inline; font-size:2vw;">face</md-icon>
													<input type="text" name="firstname" data-ng-model="privatedata.firstname" required data-ng-disabled="FormDisabled" flex="grow" />
												</md-input-container>
												<md-input-container class="md-icon-float md-block">
													<label>{{Last Name}}</label>
													<input type="text" name="lastname" data-ng-model="privatedata.lastname" required data-ng-disabled="FormDisabled" flex="grow" />
												</md-input-container>
											</div>					
										</div>
									</form>
								</div>
						</div>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;">
					<md-button data-ng-click="clickToRegister()" data-ng-disabled="FormDisabled || registerform.$invalid" class="md-raised md-primary">
						{{Register}}
					</md-button>
					<span flux></span>
					<md-button data-ng-click="clickToClose()" class="md-raised">
						{{Cancel}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="registerComplete.html" >
		<md-toast>
			<div layout="column">
				<div layout="row">
					<div>{{Success}}!</div>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="closeToast()">
						<md-icon aria-label="Close dialog" md-font-icon="close" style="font-size:2vw;color:white;">close</md-icon>
					</md-button>
				</div>
				<div class="column">
					{{Your registration is successful. However, your account needs to be activatd by administrator before you will be able to login}}.
				</div>
			</div>
		</md-toast>
	</script>
	<script type="text/ng-template" id="loginComplete.html" >
		<md-toast>
			<div layout="column">
				<div layout="row">
					<div>{{Success}}!</div>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="closeToast()">
						<md-icon aria-label="Close dialog" md-font-icon="close" style="font-size:2vw;color:white;">close</md-icon>
					</md-button>
				</div>
				<div class="column">
					{{Your login is successful}}.
				</div>
			</div>
		</md-toast>
	</script>
</section>