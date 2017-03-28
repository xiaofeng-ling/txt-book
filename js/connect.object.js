(function() {
	/*
	var ajax = function() {
		return this;
	};

	ajax.prototype = {
	constructor: ajax,
	send: function(url, callback, data) {
		data = data || "";
		
		this.http = new XMLHttpRequest();		
		this.http.open("POST", url, true);
		this.http.onreadystatechange = callback;
		this.http.send(data);
		}
	}

	// 全局变量
	$.ajax = new ajax();
	*/
	$.ajax = function(url, callback, data) {		
		data = data || "";
		
		var http = new XMLHttpRequest();
		http.open("POST", url, true);
		
		/*
		不建议使用这样的方式，这段代码用于在执行的时候将回调函数的
		this指向一个XMLHttpRequest对象，从而获得其属性
		*/
		http.callback = callback;
		
		http.onload = function() {
			if (http.status = 200)
				http.callback(http.responseText);
			else
				console.log("请求失败");
		}
		http.send(data);
	}
	
})();
