var flag = 1;
var buffer = "";
var name = "";
var clickFlag = 0;

function clickScroll() {
	// 点击指定位置进行翻页，目前测试中
	if (clickFlag) {
		document.body.scrollTop = document.body.scrollTop + window.innerHeight;
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
			readMain.innerHTML = buffer + xmlhttp.responseText.replace("\n", "</br>");
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
		
		if (true == confirm("确认是段标黄的文本吗？")) {
			var tempstr = buffer.substring(pos, buffer.length);
			
			offset = function() {
				var charnum = 0;
				for (i=pos; i<tempstr.length; i++) {
					charnum += (buffer.charCodeAt(i)>255 ? 3 : 1);
				}
				
				return charnum;
			}
			
			var xmlhttp = new XMLHttpRequest();
			
			xmlhttp.open("GET", "read.php?operator=...这里需要写保存的操作符&book=1111.txt&offset="+offset, true);
			xmlhttp.send();
			
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					alert("进度已保存，可以安全退出！");
				}
			}
		}
	}	
	}
}

window.onscroll = function() {
	// 滚动无限加载，目前测试中
	var xmlhttp;
	xmlhttp = new XMLHttpRequest();

	if ((document.body.scrollHeight - document.body.scrollTop) <= 2000) {
		flag = 1;
		xmlhttp.open("GET", "read.php?operator=2&book=1111.txt", true);
		xmlhttp.send();
	}
	
	if (document.body.scrollTop <= 2000) {
		flag = 2;
		xmlhttp.open("GET", "read.php?operator=3&book=1111.txt", true);
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
		}
	}
}

function displayBlock() {
	setting.style = "display:block";
}

function displayNone() {
	setting.style = "display:none";
}

function setClickFlag(e) {
	clickFlag = e;	
}

window.onload = function() {
	readMain.onmouseup = function() {
	if (!saveBox.checked)
		return -1;
		
	var selectText = window.getSelection().toString();
	var pos;
	
	if ((pos = buffer.lastIndexOf(selectText)) > 0)
	{
		var tempStr1 = readMain.innerHTML.substring(0, pos);
		var newText = "<span style='background:yellow'>" + selectText + "</span>";
		var tempStr2 = readMain.innerHTML.substring(pos+selectText.length, buffer.replace("\n", "</br>").length);
		
		readMain.innerHTML = tempStr1 + newText + tempStr2;
	}
	else
		alert("未搜索到指定文本，请重试！");
	
	console.log(selectText);
}
}