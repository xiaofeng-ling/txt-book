// 全局变量
var flag = 1;
var buffer = "";
var ajax_lock = 0;

function clickScrollNext() {
	// 点击指定位置进行翻页，目前测试中
	if (click.checked) {
			document.body.scrollTop += window.innerHeight;
	}
}

function clickScrollPrev() {
	// 点击指定位置进行翻页，目前测试中
	if (click.checked) {
			document.body.scrollTop -= window.innerHeight;
	}
}

function displayClick() {
	if (click.checked) {
		clickPrev.style = "top:0%;  display:block;";
		clickNext.style = "top:50%; display:block;";
	}
	else {
		clickPrev.style = "display:none;";
		clickNext.style = "display:none;";
	}
}

function getBooks() {
	var xmlhttp = new XMLHttpRequest();
	var books;
	
	xmlhttp.open("GET", "read.php?operator=128&book=", false);
	xmlhttp.send();
	
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			books = xmlhttp.responseText.split("|");
			
			for (book in books) {
				var newNode = document.createElement("a");
				newNode.innerHTML = book;
				setting.addendChild(newNode);
			}
		}
	}
}

window.onload = function() {
	var xmlhttp = new XMLHttpRequest();
	
	xmlhttp.open("GET", "read.php?operator=2&book=1111.txt", true);
	xmlhttp.send();
	// 页面加载的时候ajax
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			buffer = xmlhttp.responseText
			//readMain.innerHTML = buffer + xmlhttp.responseText.replace("\n", "</br>");
		}
	}
	
	// 保存的时候页面获取文件偏移量
	readMain.onmouseup = function() {
		var selectText = window.getSelection().toString();
		var pos = 0;
		
		// 获取文本并将其显示出来
		if ((pos = buffer.lastIndexOf(selectText)) > 0) {
			var tempStr1 = readMain.innerHTML.substring(0, pos);
			var newText = "<span style='background:yellow'>" + selectText + "</span>";
			var tempStr2 = readMain.innerHTML.substring(pos+selectText.length, buffer.replace("\n", "</br>").length);
			
			readMain.innerHTML = tempStr1 + newText + tempStr2;
			
			if (true == confirm("确认是这段标黄的文本吗？")) {
				var tempstr = buffer.substring(pos, buffer.length);
				
				offset = function() {
					var charnum = 0;
					for (i=pos; i<tempstr.length; i++) {
						charnum += (buffer.charCodeAt(i)>255 ? 3 : 1);
					}
					
					return charnum;
				}
				
				var xmlhttp = new XMLHttpRequest();
				
				xmlhttp.open("GET", "read.php?operator=64&book=1111.txt&offset="+offset, true);
				xmlhttp.send();
				
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						alert("进度已保存，可以安全退出！");
					}
				}
			}
		}	
	}
	
		// 绑定点击翻页事件
	form.addEventListener("click", displayClick);
	clickNext.addEventListener("click", clickScrollNext);
	clickPrev.addEventListener("click", clickScrollPrev);
}

window.onscroll = function() {
	// 滚动无限加载，目前测试中
	var xmlhttp;
	xmlhttp = new XMLHttpRequest();
	
	if ((document.body.scrollHeight - document.body.scrollTop) <= 2000 && ajax_lock==0) {
		flag = 1;
		ajax_lock = 1;
		xmlhttp.open("GET", "read.php?operator=2&book=1111.txt", true);
		xmlhttp.send();
	}
	
	if (document.body.scrollTop <= 200 && ajax_lock==0) {
		flag = 2;
		ajax_lock = 1;
		xmlhttp.open("GET", "read.php?operator=4&book=1111.txt", true);
		xmlhttp.send();
	}
	
	// ajax 回调函数
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			buffer = xmlhttp.responsetText;
			
			if (1 == flag)
				readMain.innerHTML = readMain.innerHTML + xmlhttp.responseText.replace("\n", "</br>");
			else
				readMain.innerHTML = xmlhttp.responseText.replace("\n", "</br>") + readMain.innerHTML;
			
			flag = 0;
			ajax_lock = 0;
		}
	}
}

function displayBlock() {
	setting.style = "display:block";
}

function displayNone() {
	setting.style = "display:none";
}