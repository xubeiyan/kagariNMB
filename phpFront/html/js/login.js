/*
* 控制登入界面的显示/隐藏
*/

var adminLoginButton = document.getElementById('admin-login-button'),
	adminLoginList = document.getElementById('admin-login-list');

adminLoginButton.addEventListener('click', function () {
	if (adminLoginList.style.display != 'block') {
		adminLoginList.style.display = 'block';
	} else {
		adminLoginList.style.display = 'none';
	}
	
});