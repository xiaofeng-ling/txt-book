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
	
	var textArray = new Array();

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
		
		/* 添加属性 */
		if (typeof property == "object") {
			for (x in property)
				nodes[x] = property[x];
		}
		
		$(id).appendChild(nodes);
	}
	
	$.getText = function(element, depth=256) {
		/* 类似于element.textContent */
	    if (element == "" || typeof(element) == "undefined" || depth < 0)
		    return -1;	
		
		depth--;
	
		if (element.nodeType == 3 && element.nodeValue != "\n")
			textArray.push(element.nodeValue);	
	
		for (var nodes = element.childNodes, i=0; i<nodes.length; i++)
			$.getText(nodes[i], depth);

        return textArray;
    }
	
	$.getText2 = function(element) {
		/* 获取节点中的文本节点内容，返回获取到的第一个文本 */
		if (element == "" || typeof(element) == "undefined")
		    return -1;
		
		for (var nodes = element.childNodes, i=0; i<nodes.length; i++)
			if (nodes[i].nodeType == 3)
				return nodes[i].nodeValue;
			
		return -1;
	}	
	
	$.phpEvent = "event.php";
	
})();
