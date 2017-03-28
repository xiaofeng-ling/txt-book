(function() {
	window.addEventListener("load", init);
	
	function init() {
		/*
		$("index_button").addEventListener("click", function() {$.changeTo("index")});
		$("read_button").addEventListener("click", function() {$.changeTo("read")});
		*/
		$("main").addEventListener("click", read);
		
		loadAllBook();
	}
	
	function loadAllBook() {
		/* 加载所有书籍 */
		var operator = new Object();
		
		/* 与php交互的操作指令 */
		operator.operator_code = 9;
		
		$.ajax.send($.phpEvent, function() {
			if (this.readyState == 4 && this.status == 200) {
				
				var result = $.decode(this.responseText);
				
				if ("error_code" in result)
					if (result.error_message == "noLogin")
						window.location = "login.php";
					else {
						alert(result.error_message);
						return -1;
					}
				
				/* 将书名解码，然后添加 */
				for (x in result) {	
					$.addNode("main", "div", "<p>" + result[x] + "</p>");
				}
			}
		}, $.encode(operator));
	}
	
	function read(e) {
		/* 点击书籍后跳入阅读界面阅读 */
		e = e || window.event;
		
		window.location = "read.html?" + e.target.innerHTML;
	}
	
})();