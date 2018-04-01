/**
* angular是某个库，反正和Angular.js没太大关系
*/

var areaListButton = document.getElementsByClassName('area-list')[0],
	areaList = document.getElementsByClassName('area-list-menu')[0],
	funcListButton = document.getElementsByClassName('add-func')[0],
	funcList = document.getElementsByClassName('func-list-menu')[0];
	
// 板块列表显示
areaListButton.addEventListener('click', function () {
	if (areaList.style.display != 'none') {
		areaList.style.display = 'none';
		
	} else {
		areaList.style.display = 'inline-block';
	}
});

// 功能列表显示
funcListButton.addEventListener('click', function () {
	if (funcList.style.display != 'none') {
		funcList.style.display = 'none';
	} else {
		funcList.style.display = 'inline-block';
	}
})