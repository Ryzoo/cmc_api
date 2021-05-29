<?php
use Core\System\Api;

$api = new Api();
$api->group("chat",function(Api $api){
	$api->get("/","ChatController@get");
    $api->get("/message/{id}","ChatController@getMessageList");
    $api->put("/message/{id}/read","ChatController@setMessageRead");
});

$api->group("logs",function(Api $api){
    $api->get("/","LogsController@getAll");
    $api->delete("/","LogsController@removeAll");
});

$api->group("user",function(Api $api){
    $api->get("/","UserController@getAll");
    $api->get("/{id}","UserController@getOne");
    $api->get("/{id}/license","UserController@getUserLicense");

    $api->put("/","UserController@update");
    $api->put("/{id}","UserController@saveUser");

    $api->post("/{id}/activate","UserController@activate");
    $api->post("/","UserController@addUser");
    $api->post("/password","UserController@changePassword");
    $api->post("/{id}/license/{id}/extend","UserController@extendUserLicense");

    $api->delete("/{id}","UserController@delete");
});

$api->group("wallet",function(Api $api){
    $api->get("/","WalletController@getWallet");
    $api->post("/add","WalletController@add");
});

$api->group("friends",function(Api $api){
    $api->get("/","FriendController@getAll");
    $api->delete("/{id}","FriendController@delete");
    $api->post("/emailInvite","FriendController@emailInvite");
    $api->get("/find","FriendController@find");
    $api->post("/invite","FriendController@invite");
    $api->post("/inviteReply","FriendController@inviteReply");
});

$api->group("file",function(Api $api){
    $api->get("/download/{imgId}","FileController@download");
});

$api->group("folders",function(Api $api){
    $api->get("/{type}","FolderController@getAllFromType");
    $api->get("/{id}/content","FolderController@get");
    $api->post("/","FolderController@addFolder");
    $api->delete("/{id}","FolderController@removeFolder");
    $api->put("/{id}","FolderController@editFolder");
});

$api->group("watermark",function(Api $api){
    $api->get("/","WatermarkController@getAll");
    $api->post("/","WatermarkController@add");
    $api->delete("/{id}","WatermarkController@remove");
});

$api->group("animations",function(Api $api){
    $api->get("/","AnimationController@getAll");
    $api->get("/shared","AnimationController@getAllShared");
    $api->get("/{id}","AnimationController@getOne");
    $api->get("/{id}/imgId","AnimationController@getImgId");
    $api->get("/{id}/sharedList","AnimationController@getSharedUnSharedList");
    $api->put("/{id}","AnimationController@update");
    $api->post("/","AnimationController@add");
    $api->post("/{id}/saved","AnimationController@saveRenderImage");
    $api->post("/{id}/copy","AnimationController@copy");
    $api->post("/{id}/share/{friendId}","AnimationController@share");
    $api->delete("/{id}","AnimationController@delete");
    $api->delete("/{id}/share/{friendId}","AnimationController@removeShare");
});

$api->group("conspect",function(Api $api){
    $api->get("/","ConspectController@getAll");
    $api->get("/shared","ConspectController@getAllShared");
    $api->get("/{id}","ConspectController@getOne");
    $api->get("/{id}/download/{theme}","ConspectController@download");
    $api->get("/{id}/sharedList","ConspectController@getSharedUnSharedList");
    $api->put("/{id}","ConspectController@update");
    $api->post("/","ConspectController@add");
    $api->post("/{id}/share/{friendId}","ConspectController@share");
    $api->delete("/{id}","ConspectController@delete");
    $api->delete("/{id}/share/{friendId}","ConspectController@removeShare");
});

$api->group("events",function(Api $api){
    $api->post("/","EventController@add");
	$api->delete("/{id}","EventController@delete");
	$api->put("/{id}","EventController@update");
    $api->get("/","EventController@getAll");
});

$api->group("notification",function(Api $api){
    $api->get('/',"NotificationController@getAll");
    $api->post('/',"NotificationController@add");
    $api->put('/{id}/read',"NotificationController@setAsRead");
    $api->delete('/{id}',"NotificationController@delete");
});

$api->group("auth",function (Api $api){
    $api->post("/login","AuthController@login");
    $api->post("/register","AuthController@register");
    $api->post("/checkAuth","AuthController@checkAuth");
    $api->post("/password-reset","AuthController@resetUserPassword");
    $api->post("/change-password-reset","AuthController@changeResetUserPassword");
});

$api->group("stats",function(Api $api){
    $api->get("/database","StatsController@getDatabaseStat");
});

$api->group("database",function(Api $api){
    $api->delete("/table","DatabaseController@deleteTable");
    $api->delete("/row","DatabaseController@deleteRow");
    $api->delete("/row/{tableName}","DatabaseController@deleteTableRow");
    $api->post("/migration","DatabaseController@runMigration");
    $api->get("/copy","DatabaseController@runCopy");
});

$api->group("role",function(Api $api){
    $api->get("/","RoleController@get");
    $api->get("/{id}","RoleController@getOne");
    $api->delete("/{id}","RoleController@delete");
    $api->put("/{id}","RoleController@save");
    $api->post("/","RoleController@add");
});

$api->group("permission",function(Api $api){
    $api->get("/","PermissionController@get");
    $api->get("/{id}","PermissionController@getOne");
    $api->delete("/{id}","PermissionController@delete");
    $api->put("/{id}","PermissionController@save");
    $api->post("/","PermissionController@add");
});

$api->group("license",function(Api $api){
    $api->get("/","LicenseController@get");
    $api->get("/{id}","LicenseController@getOne");
    $api->get("/role/{id}","LicenseController@getLicenseByRole");
    $api->delete("/{id}","LicenseController@delete");
    $api->put("/{id}","LicenseController@save");
    $api->post("/","LicenseController@add");
});

$api->group("userLicense",function(Api $api){
    $api->get("/","UserLicenseController@get");
    $api->get("/{id}","UserLicenseController@getOne");
    $api->delete("/{id}","UserLicenseController@delete");
    $api->put("/{id}","UserLicenseController@save");
    $api->post("/","UserLicenseController@add");
});

$api->group("gift",function(Api $api){
    $api->post("/","GiftController@getGift");
    $api->post("/use","GiftController@useGift");
});

$api->group("contact",function(Api $api){
		$api->post("/","ContactController@sendMessageToUs");
	});

$api->run();