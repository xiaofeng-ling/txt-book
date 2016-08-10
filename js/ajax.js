// 全局变量
var flag = 1;
var string_buffer = "";
var ajax_lock = 0;
var current_book = "";
var tail_end_book = "";
var head_end_book = "";

/*--------这段代码是借鉴jQuery的，真的是，太精妙了，虽然还有另一种写法，但这种思想才是最棒的！--------------*/
// 自己封装的并不彻底，无法取得成功状态，再参考jquery
var Ajax = function(method, url, async) {
	return new Ajax.prototype.init(method, url, async);
}

Ajax.prototype = {
	init: function(method, url, async) {
		
		if (typeof(async) === "undefined")
			async = true;
		
		this.http = new XMLHttpRequest();
		this.http.open(method, url, async);
		return this;
	},
	
	callback: function(func) {
		this.http.onreadystatechange = func;
		return this;
	},
	
	send: function() {
		this.http.send();
		return this;
	}
}

Ajax.prototype.init.prototype = Ajax.prototype;

/*-------------借鉴至jQuery----------------------------------------------------------------------------------*/

/*-------------上面的代码还有一种写法，不知道实用不----------------------------------------------------------

var Ajax = function(method, url, async) {
	return new Ajax.init(method, url, async);
}

Ajax.init = function(method, url, async) {
		
		if (typeof(async) === "undefined")
			async = true;
		
		this.http = new XMLHttpRequest();
		this.http.open(method, url, async);
		return this;
	};
	
Ajax.callback = function(func) {
		this.http.onreadystatechange = func;
		return this;
	};
	
Ajax.send = function() {
		this.http.send();
		return this;
	};
	
Ajax.responseText = function() {
		return this.http.responseText;
	};

Ajax.init.prototype = Ajax;

-------------到此为止--------------------------------------------------------------------------------------*/

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
	
	// 采用新的方式无法正常工作？明日修复
	(function getBooks() {
		Ajax("GET", "read.php?operator=128&book=NULL&offset=NULL").callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				books_buffer = this.responseText.split("|");
				current_book = books_buffer[0];
				
				for (book in books_buffer) {
					node_li = document.createElement("li");
					node_li.innerHTML = books_buffer[book];
					node_li.style = "list-style-type:none";
					books.appendChild(node_li);
				}
			}
			
			// 异步加载第一本书
			if (current_book !== "") {
				Ajax("GET", "read.php?operator=2&book="+current_book).callback(
				function() {
					if (this.readyState == 4 && this.status == 200) {
						string_buffer = this.responseText;
						readMain.innerHTML = string_buffer + this.responseText.replace("\n", "<br>");
					}
				}).send();
			}
		}).send();
	}());	
	
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
		
		Ajax("GET", "read.php?operator=2&book="+current_book).callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				if (this.reponseText ==== "")
					tail_end_book = current_book;
				
				if (readMain.innerHTML === "") {
					this.open("")
				}
			}
		}
		
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