(function() {
	window.addEventListener("load", loadBook);
	
	/* --------------------index.js 中的全局变量 ----------------------*/
	var category = "";		// 当前类别
	var pageNum = 1;				// 当前页
	
	function loadBook() {		
		$("main").addEventListener("click", addBook);
		$("pageNum").addEventListener("click", loadPage);
		$("category_ul").addEventListener("click", changeClass);

		getClass();
		
		return 1;
	}
	
	function getBooks(category, pageNum) {
		/* 获取书籍 */
		
		var operator = new Object();
		
		operator.operator_code = 6;
		operator.category = category;
		operator.pages = pageNum;
		$.ajax($.phpEvent, function(result) {
			var result = $.decode(result);
			
			if ("error_code" in result)
				if (result.error_message == "noLogin")
					window.location = "login.php";
				else {
					alert(result.error_message);
					return -1;
				}
			
			for (x in result) {
				var node = document.createElement("div");
				node.innerHTML = "<li>" + result[x].name + "<a id=\"add\" href=\"#\">添加</a></li> <p>" + result[x].introduction + "</p>";
				$("main_ul").appendChild(node);
			}
		}, $.encode(operator));
		
		return 1;
	}
	
	function getClass() {
		/* 获取所有的分类 */
		
		var operator = new Object();
		
		operator.operator_code = 8;
		
		$.ajax($.phpEvent, function(result) {		
			var result = $.decode(result);
			
			if ("error_code" in result)
				if (result.error_message == "noLogin")
					window.location = "login.php";
				else {
					alert(result.error_message);
					return -1;
				}
			
			for (x in result) {
				var node = document.createElement("li");
				node.innerHTML = "<a href=\"#\">" + result[x].class + "</a>";
				$("category_ul").appendChild(node);
			}
			
			category = result[0].class;
			getBooks(result[0].class, 1);
			getClassNum(result[0].class);
		}, $.encode(operator));
	}
	
	function addBook(e) {
		e = e || window.event;
		
		if (e.target.id != "add") {
			return -1;
		}
		
		operator = new Object();
		
		operator.operator_code = 4;
		operator.book = $.getText2(e.target.parentNode);
		
		$.ajax($.phpEvent, function(result) {
				var msg = $.decode(result);
				
				if (msg.error_code != -1 && msg.error_message == "noLogin")
					window.location = "login.php";
				
				alert("添加成功");
		}, $.encode(operator));
		
	}
	
	function getClassNum(category) {
		var operator = new Object();
		operator.operator_code = 10;
		operator.class = category;
		
		$.ajax($.phpEvent, function(result) {
			var result = $.decode(result);
			
			pageNum = result.count;
			
			if (pageNum/10 <= 4) {
				for (var i=1; i<pageNum/10+1; i++) {
					$.addNode("pageNum", "a", i, {href: "#"});
				}
			}
			else {
				for (var i=1; i<5; i++) {
					var nodes = document.createElement("a");
					nodes.href = "#";
					nodes.innerHTML = i;
					$("pageNum").appendChild(nodes);
				}
				
				$.addNode("pageNum", "span", "...");
				
				$.addNode("pageNum", "a", pageNum/10, {href: "#"});
				$.addNode("pageNum", "a", pageNum/10+1, {href: "#"});
			}
		}, $.encode(operator));
	}
	
	function loadPage(e) {
		e = e || window.event;

		if (e.target.nodeName == "A") {
			pageNum = e.target.innerHTML;
			$("main_ul").innerHTML = "";
			e.target.className = "selected";
			
			getBooks(category, pageNum);
		}
	}
	
	function changeClass(e) {
		e = e || window.event;
		
		category = e.target.innerHTML;
		pageNum = 1;
		$("main_ul").innerHTML = "";
		
		getBooks(category, pageNum);
	}
	
})();
