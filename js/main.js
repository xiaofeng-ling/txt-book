/* 全局变量 */
var current_book = "";	// 指向当前书籍
var tail_end_book = "";	// 指向尾部已结束的书籍
var head_end_book = "";	// 指向头部已结束的书籍
var ajax_lock = 0;		// ajax锁
var operator_flag = 0;	// 操作标志

window.onload = function() {
    /* 初始化 */
    getBooks(true);
	
	window.addEventListener("scroll", wheel);
	window.addEventListener("click", event_handle)
	
	$("feature").addEventListener("mousemove", displayBlock);
	$("feature").addEventListener("mouseout", displayNone);
	$("setting").addEventListener("mousemove", displayBlock);
	$("setting").addEventListener("mouseout", displayNone);
}

window.onbeforeunload = function() {
	save_offset();
	return "您的进度将被保存";
}

function getBooks(flag) {
        Ajax("GET", "operator.php?operator=6&book=book").callback(function() {
			if (this.responseText == "用户未登陆!") {
			/*	重定向至登录页面 */
				location.href = "login.php";
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
					node_li.id = "book";
					node_li.className = "bookshelf";
                    $("books").appendChild(node_li);
                }
            } // end if
            /* 异步加载第一本书 */
            if (current_book !== "" && flag) {
		/* 我不知道为什么不加这一句在首次页面载入完成后使用保存函数会提示找不到length属性 */
		$("readMain").innerHTML = "";

                /* 重置prev_offset */
				Ajax("GET", "operator.php?operator=7&book="+current_book).callback(function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "重置成功！")
							;
						else {
							alert("初始化失败！\n");
							return -1;
						}
					}
				});
				
				/* 加载下一页 */
                load_next();

                /* 加载上一页 */
                load_prev();
            } // end if
        });
}

function wheel(event) {
	var delta = 0;
	
	if (!event)
		event = window.event;
	
	if (event.wheelDelta) {
		/* wheelDelta属性提供120的倍数，表明滚动的力度，正值代表上，负值代表下 */
		delta = event.wheelDelta/120;
		
		if (window.opera)
			delta = -delta;
	}
	else if (event.detail) {
		/* 这里是为了火狐 */
		delta = -event.detail/3;
	}
	
	/* 滚动无限加载，目前测试中 */
	/* 获取下一页 */
	if ((document.body.scrollHeight - document.body.scrollTop) <= 2000 && current_book != "" && current_book != tail_end_book && !ajax_lock) {
		load_next();
		}
	
	/* 获取上一页 */
	else if (document.body.scrollTop <= 400 && current_book != "" && current_book != head_end_book && !ajax_lock) {
		load_prev();
		
		}// end if
}

function displayBlock() {
	$("setting").style.display = "block";
}

function displayNone() {
	$("setting").style.display = "none";
}

function display_all_books() {
	/* 此函数用于显示所有的书籍 */
	Ajax("GET", "operator.php?operator=8&book=book").callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
                all_books = JSON.parse(this.responseText);
				$("all_books_right").innerHTML = "";
				
				for (i=0; i<all_books.length; i++) {
					var node = document.createElement("li");
					node.style.cursor = "pointer;";
					node.innerHTML = decodeURI(all_books[i]);
					node.id = "newBook";
					node.title = "点击增加书籍";
					node.className = "bookshelf";
					$("all_books_right").appendChild(node);
				}
				
				$("all_books_right").style.display = "block";
				$("all_books_left").style.display = "block";
			}
		}
	);
	
}

function load_next() {
	ajax_lock = 1;
	
	Ajax("GET", "operator.php?operator=1&book="+current_book).callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				if (this.responseText.length == 0)
					tail_end_book = current_book;
				else {
					/* 将字符串分割为更小的长度以增加保存记录精度 */
					for (i=0; i<this.responseText.length/1024; i++) {
						var node = document.createElement("span");
						node.innerHTML = this.responseText.substr(i*1024, 1024).replace(/</g, "&#60").replace(/\r*\n+/g, "<br>");
						$("readMain").appendChild(node);
					}
				}
			}

			ajax_lock = 0;
		});
}

function load_prev() {
	ajax_lock = 1;
	
	Ajax("GET", "operator.php?operator=2&book="+current_book).callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				if (this.responseText.length == 0)
					head_end_book = current_book;
				else {
					scroll_pos = document.body.scrollHeight - document.body.scrollTop;

					/* 将字符串分割为更小的长度以增加保存记录精度 */
					for (i=1; i<this.responseText.length/1024; i++) {
						var node = document.createElement("span");
						node.innerHTML = this.responseText.substr(i*-1024, 1024).replace(/</g, "&#60").replace(/\r*\n+/g, "<br>");
						$("readMain").insertBefore(node, readMain.firstChild);
					}
					
					/* 最后再填入不足1024的部分 */
					var cutOut = this.responseText.length%1024 == 0 ? 1024 : this.responseText.length%1024;
					var node = document.createElement("span");
					node.innerHTML = this.responseText.substr((i+1)*-1024, cutOut).replace(/</g, "&#60").replace(/\r*\n+/g, "<br>");
					$("readMain").insertBefore(node, readMain.firstChild);
				
					/* 在我的chrome浏览器上，加载向上页面后滚动条不会自动调整位置，于是只能通过这样很是模糊的代码来解决 */
					document.body.scrollTop = document.body.scrollHeight - scroll_pos;

				}
			}
			
			ajax_lock = 0;
		});
}

function save_offset() {
	/* 函数用于保存当前书籍的阅读进度
	** 直接使用blob计算字节数 */

	var child_element = $("readMain").childNodes;
	var data = new Array();
	
	if (document.body.scrollTop == 0) {
		for (var i=0; i<child_element.length; i++) {
				if (child_element[i].innerHTML != "")
					data.push(child_element[i].innerHTML.replace(/<br>/g, "\n"));
			}
	}		
	else {
		for (var i=0; i<child_element.length; i++) {
			/* 获取当前滚动条位置的元素 */
			if (child_element[i].offsetTop < document.body.scrollTop && child_element[i].offsetTop + child_element[i].offsetHeight > document.body.scrollTop) {
				for (; i<child_element.length; i++) {
					if (child_element[i].innerHTML != "")
						data.push(child_element[i].innerHTML.replace(/<br>/g, "\n"));
				}
			}
		}
	}
	
	/* 采用blob计算字节数 */
	var blob = new Blob(data);

	Ajax("GET", "operator.php?operator=3&book="+current_book+"&offset="+blob.size).callback(
		function() {
			if (this.readyState == 4 && this.status == 200) {
				if (this.responseText == "保存成功！") {
					alert("保存成功，现在可以安全关闭本页面");
					return 1;
				}
				else if (true == confirm("保存失败，需要再试一次吗？"))
					save_offset();
				
				return 0;
			}
		}
	)
}

function operator_set() {
	if (operator_flag)
		$("operator").innerHTML = "切换模式";
	else
		$("operator").innerHTML = "删除模式";
	
	operator_flag = !operator_flag;
}

function switch_book(book) {
	/* 选择对书籍的操作 */
	if (operator_flag)
		del_book(book);
	else
		change_book(book);
}

function change_book(book) {
	if (true == confirm("确认切换至小说：" + book)) {
		current_book = book;
		$("readMain").innerHTML = "";
		tail_end_book = head_end_book = ""; 
		
		(function() {
			if (current_book !== "") {
				/* 重置prev_offset */
				Ajax("GET", "operator.php?operator=7&book="+current_book).callback(function() {
				if (this.readyState == 4 && this.status == 200) {
					if (this.responseText == "重置成功！")
						;
					else {
						alert("初始化失败！\n");
						return -1;
					}
				}
				});
				
				/* 加载下一页 */
				load_next();

				/* 加载上一页 */
				load_prev();
			};
		} ()); // end function()
		
	}
}

function del_book(book) {
	if (true == confirm("确认删除小说：" + book)) {
		(function() {
			Ajax("GET", "operator.php?operator=5&book="+book).callback(function() {
				if (this.readyState == 4 && this.status == 200) {
					alert(this.responseText);
					getBooks(current_book == book);
					
					return 1;
				}
			});
		}());
	}
}

function add_book(book) {	
	if (true == confirm("确认增加小说《" + book + "》？")) {
		Ajax("GET", "operator.php?operator=4&book=" + book).callback(
			function() {
				if (this.readyState == 4 && this.status == 200) {
					alert(this.responseText);
					getBooks(false);
				}
			}
		)
	}
}

function close_all_books(e) {
	e = e || window.event;
	
	if (e.target.id !== "all_books_left" && e.target.id !== "all_books_right") {
		if ($("all_books_left").style.display === "block" || $("all_books_right").style.display === "block") {
					$("all_books_left").style.display = $("all_books_right").style.display = "none";
		}
	}
}

function event_handle(e) {
	/* 通用的事件处理函数 */
	
	e = e || window.event;
	
	switch(e.target.id) {
		case "books":
			switch_book();
			break;
			
		case "add":
			display_all_books();
			break;
			
		case "operator":
			operator_set();
			break;
			
		case "saveButton":
			save_offset();
			getBooks(true);
			break;
			
		case "newBook":
			add_book(e.target.innerHTML);
			break;
			
		case "book":
			switch_book(e.target.innerHTML);
			break;	
			
		default:
			close_all_books(e);
			break;
	}
}
