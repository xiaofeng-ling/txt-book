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
		this.data = null;
		return this;
	},
	
	setData: function(data) {
		this.data = data;
		return this;
	},
	
	send: function() {
		this.http.send();
	},
	
	callback: function(func) {		
		this.http.onreadystatechange = func;
		this.http.send(this.data);
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