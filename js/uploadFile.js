var uploadFile = function(file, slice, php) {
	/* file表示一个文件指针	
	** slice表示分片长度，默认1024*1024(1M)
	** php表示后台处理文件，默认为uploadFile.php
	*/
	
	//var md5 = hex_md5;					// 此处可以采用md5加密函数
	
	if (!(file instanceof File))
		return '不是一个有效的文件指针';
	
	slice = slice || 1024*1024;
	php = php || "uploadFile.php";
	
	/* 初始化操作,阻塞模式 */
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open("GET", php + "?filename=" + encodeURI(file.name) + "&slices=" + Math.ceil(file.size/slice), false);
	xmlHttp.send();
	
	if (xmlHttp.responseText !== 'success')
		return '初始化失败!错误信息:'+xmlHttp.responseText;
	
	/* 循环读取数据并逐步上传 */
	for (var i=0; i<Math.ceil(file.size/slice); i++) {
		var reader = new FileReader();
		var blob = file.slice(i*slice, (i+1)*slice);
		var sendHttp = new XMLHttpRequest();
		
		reader.readAsBinaryString(blob);
		
		sendHttp.index = i;
		sendHttp.open("post", php);
		sendHttp.onreadystatechange = function() {
			if (sendHttp.responseText === 'success' || sendHttp.responseText === "合并完成")
				return '分片'+sendHttp.index+'上传成功';
			
			return '第'+sendHttp.index+'分片上传失败, 错误信息:'+sendHttp.responseText;
		}
		
		/* 设置http头 */
		sendHttp.setRequestHeader('filename', encodeURI(file.name));
		sendHttp.setRequestHeader('sliceindex', i);
		sendHttp.setRequestHeader('slicesize', slice);
		/* 
		此处可以采用md5算法验证数据准确性
		sendHttp.setRequestHeader('checksum', md5(blob)); 
		*/
		
		/* 上传数据 */
		sendHttp.send(blob);
	}
}