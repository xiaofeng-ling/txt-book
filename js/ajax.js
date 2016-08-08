// 全局变量
var flag = 1;
var string_buffer = "";
var ajax_lock = 0;
var current_book = "";
var tail_end_book = "";
var head_end_book = "";

function clickScrollNext() {
	// 点击指定位置进行翻页，目前测试中
	if (click.checked) {
			document.body.scrollTop += window.innerHeight;
	}
}

function clickScrollPrev() {
	// 点击指定位置进行翻页，目前测试中
	if (click.checked) {
			document.body.scrollTop -= window.innerHeight;
	}
}

function displayClick() {
	if (click.checked) {
		clickPrev.style = "top:0%;  display:block;";
		clickNext.style = "top:50%; display:block;";
	}
	else {
		clickPrev.style = "display:none;";
		clickNext.style = "display:none;";
	}
}

window.onload = function() {
	// 我不知道为什么采用(function getBooks() {})(); 这样的方式不行....
	// 加载所有书籍
	(function getBooks() {
		var xmlhttp = new XMLHttpRequest();
		var books_buffer = "";
		
		xmlhttp.open("GET", "read.php?operator=128&book=NULL&offset=NULL", true);
		xmlhttp.send();
		
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				books_buffer = xmlhttp.responseText.split("|");
				current_book = books_buffer[0];
				
				for (book in books_buffer) {
					node_li = document.createElement("li");
					node_li.innerHTML = books_buffer[book];
					node_li.style = "list-style-type:none";
					books.appendChild(node_li);
				}
			}
			
			// 异步加载第一本书
			var xmlhttp_first = new XMLHttpRequest();
			
			if (current_book !== "") {
			
				xmlhttp_first.open("GET", "read.php?operator=2&book="+current_book, true);
				xmlhttp_first.send();
				// 页面加载的时候ajax
				xmlhttp_first.onreadystatechange=function() {
					if (xmlhttp_first.readyState == 4 && xmlhttp_first.status == 200) {
						string_buffer = xmlhttp_first.responseText
						readMain.innerHTML = string_buffer + xmlhttp_first.responseText.replace("\n", "<br>");
					}
				}
			}
		}
	}());	
	
	// 保存的时候页面获取文件偏移量
	readMain.onmouseup = function() {
		var selectText = window.getSelection().toString();
		var pos = 0;
		
		// 获取文本并将其显示出来
		if ((pos = string_buffer.lastIndexOf(selectText)) > 0) {
			var tempStr1 = readMain.innerHTML.substring(0, pos);
			var newText = "<span style='background:yellow'>" + selectText + "</span>";
			var tempStr2 = readMain.innerHTML.substring(pos+selectText.length, string_buffer.replace("\n", "<br>").length);
			
			readMain.innerHTML = tempStr1 + newText + tempStr2;
			
			if (true == confirm("确认是这段标黄的文本吗？")) {
				var tempstr = string_buffer.substring(pos, string_buffer.length);
				
				offset = function() {
					var charnum = 0;
					for (i=pos; i<tempstr.length; i++) {
						charnum += (string_buffer.charCodeAt(i)>255 ? 3 : 1);
					}
					
					return charnum;
				}
				
				var xmlhttp = new XMLHttpRequest();
				
				xmlhttp.open("GET", "read.php?operator=64&book="+current_book+"&offset="+offset, true);
				xmlhttp.send();
				
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						alert("进度已保存，可以安全退出！");
					}
				}
			}
		}	
	}
	
	// 绑定点击翻页事件
	form.addEventListener("click", displayClick);
	clickNext.addEventListener("click", clickScrollNext);
	clickPrev.addEventListener("click", clickScrollPrev);
	
	window.addEventListener("DOMMouseScroll", wheel);
	window.onmousewheel = document.onmousewheel = wheel;
	
	// 用于切换小说
	books.onclick = function(e) {
		if (true == confirm("确认切换至小说："+e.target.innerHTML)) {
			current_book = e.target.innerHTML;
			readMain.innerHTML = "";
			tail_end_book = head_end_book = "";
			window.wheel();
		}
	}
}

function wheel(event) {
	var delta = 0;
	var xmlhttp;
	xmlhttp = new XMLHttpRequest();
	var count = 5;
	
	if (!event)
		event = window.event;
	
	if (event.wheelDelta) {
		// wheelDelta属性提供120的倍数，表明滚动的力度，正值代表上，负值代表下
		delta = event.wheelDelta/120;
		
		if (window.opera)
			delta = -delta;
	}
	else if (event.detail) {
		// 这里是为了火狐
		delta = -event.detail/3;
	}
	
	
	// 滚动无限加载，目前测试中
	if ((document.body.scrollHeight - document.body.scrollTop) <= 2000 && delta<0 && !ajax_lock && current_book != tail_end_book) {
		flag = 1;
		xmlhttp.open("GET", "read.php?operator=2&book="+current_book, true);
		xmlhttp.send();
		ajax_lock = 1;
	}
	
	if (document.body.scrollTop <= 2000 && delta>0 && !ajax_lock && current_book != head_end_book) {
		flag = 2;
		xmlhttp.open("GET", "read.php?operator=4&book="+current_book, true);
		xmlhttp.send();
		ajax_lock = 1;
	}
	
	// ajax 回调函数
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			if (xmlhttp.responseText === "") {
				if (flag == 1)
					tail_end_book = current_book;
				else if (flag == 2)
					head_end_book = current_book;
				
				if (count && readMain.innerHTML === "") {
				// 如果到达结尾，服务端将不会返回数据，此时需要获取上一页的数据，最多尝试5次
				xmlhttp.open("GET", "read.php?operator=4&book="+current_book, true);
				xmlhttp.send();
				count--;
				end_book = current_book;
			}
			}
			
			
			
			string_buffer = xmlhttp.responsetText;
			var node = document.createElement("div");
			node.innerHTML = xmlhttp.responseText.replace("\n", "<br>");
			
			if (1 == flag)
				readMain.appendChild(node);
			else
				readMain.insertBefore(node, readMain.firstChild);
			
			flag = 0;
			ajax_lock = 0;
		}
	}
}

function displayBlock() {
	setting.style = "display:block";
}

function displayNone() {
	setting.style = "display:none";
}