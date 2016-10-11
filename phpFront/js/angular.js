/**
* kagari是某个库
*/

var kagari = function () {
	var that = {
		switchListPanel: function () {
			var area = document.getElementById("areas");
			console.log('!');
			if (area.style.display == '' || area.style.display == 'none') {
				area.style.display = 'block';
			} else {
				area.style.display = 'none';
			}
		}
	};
	return that;
	},
	k = kagari();