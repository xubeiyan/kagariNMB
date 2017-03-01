/**
* angular是某个库，反正和Angular.js没太大关系
*/

var kagari = function () {
	var that = {
		switchListPanel: function () {
			var area = document.getElementById("areas"),
				areap = document.getElementById("areas-p");
			if (area.style.display == '' || area.style.display == 'none') {
				console.log('areas list show~');
				area.style.display = 'block';
				areap.style.display = 'none';
			} else {
				console.log('areas list hide~');
				area.style.display = 'none';
				areap.style.display = 'block';
			}
		},
		switchFunctionPanel: function () {
			var area = document.getElementById("functions");
			if (area.style.display == '' || area.style.display == 'none') {
				console.log('functions list show~');
				area.style.display = 'block';
			} else {
				console.log('functions list hide~');
				area.style.display = 'none';
			}
		}
	};
	return that;
	},
	k = kagari();
