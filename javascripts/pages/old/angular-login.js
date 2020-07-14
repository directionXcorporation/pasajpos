app.service('functionAccess', function(httpService, $filter, $q, $log, localDb, localStorageService, lodash, Auth, loginService, logService){
    return {
        checkAccess: checkAccess
    };
    function checkAccess(functionId, validActions=undefined, isAllowed=true, db){ //team in a POS is store or userid for self check
        //logService.setlog(["checking access to "+functionId, {db: db, operationId:operationId, teamArray:teamArray}],"debug");
        let functionGroup = getFunctionGroup(functionId, db);
        if(!functionGroup || !functionGroup.length){
            return true;
        }
        
        let userId;
        let userDetails;
        let functionGroupDetail;
        try{
            userId = Auth.getUser();
            logService.setlog(["checking access to "+functionId + " for "+userId],"debug");
        }
        catch(e){
            logService.setlog(["Current userId could not be fetched on "+functionId, {error: e}], "error");
        }
        try{
            loginService.getLoggedInUserDetails(1).then(function(user){
                userDetails = user;
            });
            logService.setlog(["fetched userDetails for access to "+functionId + " for "+userId, {userDetails: userDetails}],"debug");
        }
        catch(e){
            logService.setlog(["Current userDetails could not be fetched for "+userId+ " on function "+functionId, {db: db, error: e}], "error");
        }
        try{
            functionGroupDetail = checkFunctionGroupAccess(functionId, userDetails.usersGroup, operationId, db);
            
            logService.setlog(["checking access to "+functionId+" for current user: "+userId, {operationId: operationId, teamArray: teamArray, functionId: functionId, 'userDetails of logged in user': userDetails, functionGroupDetail: functionGroupDetail}], "debug");
        }
        catch(e){
            logService.setlog(["Error while checking access to "+functionId+" for current user ", {operationId: operationId, teamArray: teamArray, functionId: functionId, 'userDetails of logged in user': userDetails, functionGroupDetail: functionGroupDetail, error: e}], "error");
        }
        
        if(functionGroupDetail && functionGroupDetail.length){
            if(!teamArray || !teamArray.length){
                return true;
            }
            for(let i=0; i<teamArray.length; i++){
                let functionGroupDetail_withRestriction = lodash.find(functionGroupDetail, { dynaPageFunctionId_usersGroupId: [ { dynaPageFunctionId_usersGroupId_restrictionId: '' } ]});
                if(lodash.size(functionGroupDetail_withRestriction)){
                    return true;
                }
                let teamObj = teamArray[i];
                let functionGroupDetail_restriction = lodash.find(functionGroupDetail, { dynaPageFunctionId_usersGroupId: [ { dynaFunctionRestrictions_localColumn: Object.keys(teamObj)[0] } ]});
                if(functionGroupDetail_restriction){
                    if(userDetails[functionGroupDetail_restriction.dynaFunctionRestrictions_localColumn] == teamObj[Object.keys(teamObj)[0]]){
                        return true;
                    }
                }
            }
            return false;
        }else{
            return false;
        }
    }
    function getFunctionGroup(functionId, db){
        let groupFunctions = [];
        let searchCondition = {
            'dynaPageFunctionId_usersGroupId.dynaPageFunctionId_usersGroupId_functionId' : functionId,
            'dynaPageFunctionId_usersGroupId.dynaPageFunctionId_usersGroupId_status' : 1
        };
        groupFunctions = localDb.advancedSelectItem('groupsDetail', searchCondition, db);
        return groupFunctions;
    }
});