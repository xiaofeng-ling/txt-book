// 全局变量
var current_book = "";	// 指向当前书籍
var tail_end_book = "";	// 指向尾部已结束的书籍
var head_end_book = "";	// 指向头部已结束的书籍
var ajax_lock = 0;

/*--------这段代码是借鉴jQuery的，真是太精妙了，虽然还有另一种写法，但这种思想才是最棒的！--------------*/
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
		this.http.send();
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

/*----------------自己的$()函数，有了这个兼容性会更好--------------------------*/

var $ = function(id) { return document.getElementById(id);}

/*--------------------$()函数结束----------------------------------------------*/
window.onload = function() {
    // 初始化
    getBooks(true);

	window.addEventListener("scroll", wheel);

    // 用于切换小说
    $("books").onclick = function(e) {
        if (true == confirm("确认切换至小说：" + e.target.innerHTML)) {
            current_book = e.target.innerHTML;
            $("readMain").innerHTML = "";
            tail_end_book = head_end_book = ""; 
			
			(function() {
                if (current_book !== "") {
					// 重置prev_offset
					Ajax("GET", "init.php?book="+current_book).callback(function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "重置成功！")
							;
						else {
							alert("初始化失败！\n");
							return -1;
						}
					}
					});
					
                    // 加载下一页
                    load_next();

                    // 加载上一页
                    load_prev();
                };
            } ()); // end function()
			
        } // end onclick()
    }
	
	$("all_books").onclick = function(e) {
		if (true == confirm("确认增加小说《"+e.target.innerHTML + "》？")) {
			Ajax("GET", "addbook.php?book=" + e.target.innerHTML).callback(
				function() {
					if (this.readyState == 4 && this.status == 200) {
						alert(this.responseText);
						getBooks(false);
					}
				}
			)
		}
	}	
}

function getBooks(flag) {
        Ajax("GET", "getbooks.php").callback(function() {
			if (this.responseText == "用户未登陆") {
				$("readMain").innerHTML = this.responseText;
				return -1;
			}
			
            if (this.readyState == 4 && this.status == 200) {
                books_buffer = JSON.parse(this.responseText);
				
				for (i=0; i<books_buffer.length; i++) {
					books_buffer[i] = decodeURI(books_buffer[i]);
				}
				
                current_book = books_buffer[0];
				$("books").innerHTML = "";

                for (book in books_buffer) {
                    node_li = document.createElement("li");
                    node_li.innerHTML = books_buffer[book];
                    node_li.style = "list-style-type:none";
                    $("books").appendChild(node_li);
                }
            } // end if
            // 异步加载第一本书
            if (current_book !== "" && flag) {
                // 重置prev_offset
				Ajax("GET", "init.php?book="+current_book).callback(function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "重置成功！")
							;
						else {
							alert("初始化失败！\n");
							return -1;
						}
					}
				});
				
				// 加载下一页
                load_next();

                // 加载上一页
                load_prev();
            } // end if
        });
}

function wheel(event) {
	var delta = 0;
	
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
	// 获取下一页
	if ((document.body.scrollHeight - document.body.scrollTop) <= 2000 && current_book != "" && current_book != tail_end_book && !ajax_lock) {
		load_next();
		}
	
	// 获取上一页
	else if (document.body.scrollTop <= 400 && current_book != "" && current_book != head_end_book && !ajax_lock) {
		load_prev();
		
		}// end if
}

function displayBlock() {
	$("setting").style = "display:block";
}

function displayNone() {
	$("setting").style = "display:none";
}

function display_all_books() {
	// 此函数用于显示所有的书籍
	Ajax("GET", "tools.php").callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
                all_books = JSON.parse(this.responseText);
				$("all_books").innerHTML = "";
				
				for (i=0; i<all_books.length; i++) {
					var node = document.createElement("li");
					node.style = "list-style-type:none";
					node.innerHTML = decodeURI(all_books[i]);
					$("all_books").appendChild(node);
				}
				
				$("all_books").style = "display:block";
			}
		}
	)
}

function load_next() {
	ajax_lock = 1;
	
	Ajax("GET", "next.php?book="+current_book).callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				if (this.responseText.length == 0)
					tail_end_book = current_book;
				else {
					var node = document.createElement("span");
					node.innerHTML = this.responseText.replace("\n", "<br>");
					$("readMain").appendChild(node);
				}
			}
			
			ajax_lock = 0;
		});
}

function load_prev() {
	ajax_lock = 1;
	
	Ajax("GET", "prev.php?book="+current_book).callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				if (this.responseText.length == 0)
					head_end_book = current_book;
				else {
					scroll_pos = document.body.scrollHeight - document.body.scrollTop;
					
					var node = document.createElement("span");
					node.innerHTML = this.responseText.replace("\n", "<br>");
					$("readMain").insertBefore(node, readMain.firstChild);
				}
				
				// 在我的chrome浏览器上，加载向上页面后滚动条不会自动调整位置，于是只能通过这样很是模糊的代码来解决
				document.body.scrollTop = document.body.scrollHeight - scroll_pos;
			}
			
			ajax_lock = 0;
		});
}

function save_offset() {
	// 函数用于保存当前书籍的阅读进度
	var child_element = $("readMain").childNodes;
	var utf8_bytes = 0;
	
	for (var i=0; i<child_element.length; i++) {
		// 获取当前滚动条位置的元素
		if (child_element[i].offsetTop < document.body.scrollTop && child_element[i].offsetTop + child_element[i].offsetHeight > document.body.scrollTop) {
			console.log(child_element[i].innerHTML);
			for (; i<child_element.length; i++) {
				utf8_bytes += calc_utf8_bytes(child_element[i].innerHTML);
			}
		}
	}
	
	Ajax("GET", "save.php?book="+current_book+"&offset="+utf8_bytes).callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				if (this.responseText == "保存成功！") {
					alert("保存成功，现在可以安全关闭本页面");
					return 1;
				}
				else if (true == confirm("保存失败，需要再试一次吗？"))
					save_offset();
			}
		}
	)
}

function calc_utf8_bytes(string) {
	// 本函数用于计算utf8码所占字节数	
	var bytes = 0;
	
	for (var i=0; i<string.length; i++) {
		var value = string.charCodeAt(i);
		
		if (value < 0x080)
			bytes += 1;
		else if (value < 0x0800)
			bytes += 2;
		else
			bytes += 3;
	}
	
	return bytes;
}