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
		http.onload = function() {
			if (http.status = 200)
				callback();
			else
				console.log("请求失败");
		}
		http.send(data);
	}
	
})();
