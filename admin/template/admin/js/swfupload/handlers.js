function fileQueueError(file, errorCode, message) {
	try {
		var msg='';
		switch (errorCode) {
			case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
				message='文件大小不能为0！';
				break;
			case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
				message='文件超过限制大小！';
				break;
			case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			    message='非法的文件类型！';
				break;
			case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
			    message='上传文件过多！';
				break;
			default:
				break;
		}
		showInfo(message);
	} catch (ex) {
		this.debug(ex);
	}
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadProgress(file, bytesLoaded) {
	try {
		var percent = Math.ceil((bytesLoaded / file.size) * 100);
		showInfo(file.name+'上传中。。。（'+percent+'%）');
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {   
		if(file.filestatus==-3){
			showInfo(file.name+'上传失败！');
		}else{
			var response = eval('(' + serverData + ')');
			if(response.type=='error'){
				showInfo(response.msg);
			}else{
				showInfo(file.name+'上传成功！');
			    document.getElementById('pic').value=response.msg;
			}
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadComplete(file) {
	try {
		/*  I want the next upload to continue automatically so I'll call startUpload here */
		if (this.getStats().files_queued > 0) {
			this.startUpload();
		} else {//all completed
			if(swfupload_refresh){
			    window.location.reload();	
			}
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		switch (errorCode) {
			case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			    message='上传取消！';
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			    message='上传停止！';
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
				message='上传文件过多！';
				break;
			default:
				break;
		}
		showInfo(message);
	} catch (ex) {
		this.debug(ex);
	}
}

function showInfo(msg){
	document.getElementById('divFileProgressContainer').innerHTML=msg;	
}