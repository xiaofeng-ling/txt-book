(function () {
	/* 全局变量 */
	window.$ = function(id) {
		return document.getElementById(id);
	}
	
	$.decode = function(data) {
		return JSON.parse(data);
	}
	
	$.encode = function(data) {
		return JSON.stringify(data);
	}

	/*
 	该函数暂时被废弃
	*/	
	$.changeTo = function(page) {		
		if ("books" == page)
			document.write();
		else if("read" == page)
			document.write();
		else if("index" == page)
			document.write();
		else
			return -1;		
	}
	
	$.addNode = function(id, nodeType, content, property) {
		var nodes = document.createElement(nodeType);
		nodes.innerHTML = content;
		
		if (typeof property == "object") {
			for (x in property)
				nodes[x] = property[x];
		}
		
		$(id).appendChild(nodes);
	}
	
	$.phpEvent = "event.php";
	
})();
