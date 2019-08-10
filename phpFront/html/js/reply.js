/*
* 控制回复
*/

var replyContent = document.getElementById('reply_content');

function reply(id) {
	replyContent.innerHTML += '>>No.' + id + '\n';
}