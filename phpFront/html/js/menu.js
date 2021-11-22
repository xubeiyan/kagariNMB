/**
* 控制两侧的菜单
*/

const areaListButton = document.querySelector('#area-list-switcher');
const areaList = document.querySelectorAll('.area-list-menu')[0];
const funcListButton = document.querySelector('#add-func-switcher');
const funcList = document.querySelectorAll('.func-list-menu')[0];
	
// 板块列表显示
areaListButton.addEventListener('click', function () {
	areaList.classList.toggle('hide');
});

// 功能列表显示
funcListButton.addEventListener('click', function () {
	funcList.classList.toggle('hide');
});

