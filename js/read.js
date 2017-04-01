(function() {
	/* 事件处理 */
	/*---------------- 初始化 ---------------------*/
	var pageY = window.pageYOffset;		// 判断页面滚动
	
	/* 退出页面的时候保存 */
	window.addEventListener("beforeunload", unload);
	
	/* 滚动事件 */
	window.addEventListener("scroll", scroll);
	
	/* 加载完成时的事件 */
	window.addEventListener("load", init);
	
	/* 当前书籍 */
	/* 因为传入的是url,所以中文会被编码成url的格式,需要再解码 */
	var currentBook = decodeURI(window.location.toString().split("?")[1]);
	
	/* 异步请求锁，防止一下子请求次数太多 */
	var ajaxLock = 1;
	
	/*---------------    函数    ------------------*/
	function init() {
		if (currentBook == "") {
			alert("参数错误");
			return -1;
		}
		
		loadNext();
		loadPrev();
	}
	
	function unload() {
		saveOffset();
		resetPrevOffset();
	}
	
	function scroll() {
		if (currentBook == "") {
			alert("参数错误");
			return -1;
		}
		
		/* 判断滚动方向 */
		if (ajaxLock && document.body.scrollHeight - document.body.scrollTop <= 2000)
			loadNext();
		else if (ajaxLock && document.body.scrollTop <= 400)
			loadPrev();
		
		pageY = window.pageYOffset;
	}
	
	function mousedown(e) {
		e = e || window.event;
		
		/* 判断鼠标点击的左右键， 0为左键，2为右键*/
		if (0 == e.button)
			window.scrollBy(window.innerHeight);	// 向下滚动
		else if (2 == e.button)
			window.scrollBy(-1 * window.innerHeight); // 向上滚动		
	}
	
	function loadNext() {
		var operator = new Object();
		
		ajaxLock = 0;
		
		/* 与php交互的操作指令 */
		operator.operator_code = 1;
		operator.book = currentBook;
		
		$.ajax($.phpEvent, function(result) {
			var result = $.decode(result);
			
			if (result.error_code == -1) {
				
				/* 以数据的1024分割，创建span节点，替换其中的特殊字符，将\n替换为HTML中的<br>标签 */
				for(var i=0; i<result.data.length; i += 1024)
					$.addNode("readMain", "span", result.data.substr(i, 1024).replace(/</g, "&#60").replace(/\r*\n+/g, "<br>"));
			}
			
			ajaxLock = 1;
		}, $.encode(operator));
		
	}
	
	function loadPrev() {
		var operator = new Object();
		
		ajaxLock = 0;
		
		/* 与php交互的操作指令 */
		operator.operator_code = 2;
		operator.book = currentBook;
		
		$.ajax($.phpEvent, function(result) {
			var result = $.decode(result);
				
			if (result.error_code == -1) {
				
				/* 以数据的1024分割，创建span节点，替换其中的特殊字符，将\n替换为HTML中的<br>标签
				   与get_next()函数不同，这个函数是从下往上类似于搭积木一样增加节点				*/
				for(var i=0; i<result.data.length; i += 1024)
					$.addNode("readMain", "span", result.data.substr(i * -1, 1024).replace(/</g, "&#60").replace(/\r*\n+/g, "<br>"));
			}
			
			ajaxLock = 1;
		}, $.encode(operator));
	}
	
	function saveOffset() {
		var child = document.body.childNodes;
		var txtData = new Array();
		var operator = new Object();
		var blob;
		
		/* 获取在当前页面的第一个元素，用于计算到末尾总共多少字节数 */
		for (i = 0; i<child.length; i++) {
			if (child[i].offsetTop > document.body.scrollTop) {
				// 替换页面中的换行标签，以提高准确度
				txtData.push(child[i].innerHTML.replace(/<br>/g, "\n"));
			}
		}
		
		/* 将数据二进制化，用于计算字节数 */
		blob = new Blob(txtData);
		
		/* 与php交互的操作指令 */
		operator.operator_code = 3;
		operator.message = "save_offset";
		operator.offset = blob.size;
		operator.book = currentBook;
		
		/* 发送请求处理数据 */
		$.ajax($.phpEvent, function(result) {
			if ($.decode(result).error_code == -1) {
				alert("保存成功！\n");
				return 1;
			}
			else
				return 0;
		}, $.encode(operator));
		
	}
	
	function resetPrevOffset() {
		/*
 		重置上一页的偏移量，用于在切换书籍、退出时用到
 		*/
		var operator = new Object();
		
		/* 与php交互的操作指令 */
		operator.operator_code = 7;
		operator.book = currentBook;
		
		$.ajax($.phpEvent, function(result) {
			if ($.decode(result).error_code == -1) {
				// 做一些操作
				return 1;
			}
			else
				return 0;
		}, $.encode(operator));
	}
	
})();
