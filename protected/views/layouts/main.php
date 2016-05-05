<?php 
use app\models\ApiInterface; 
$BaseUrl=yii::$aliases["@web"]; 
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
	<meta name="description" http-equiv="description" content="pizza diy" />
	<meta name="keywords" http-equiv="keywords" content="pizza diy" />
	<meta name="fragment" content="!" />
	<meta content="telephone=no" name="format-detection" />
	<title>weChat Js Api Test</title>
	<script src="/js/jquery-2.1.3.js"></script>
	<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
	
	<script>
		CONFIGOBJECT = <?php $interface= new ApiInterface();echo $interface->getConfig();?>;
		wx.config({
		    appId: CONFIGOBJECT.appId,
		    timestamp: CONFIGOBJECT.timestamp,
		    nonceStr: CONFIGOBJECT.nonceStr,
		    signature: CONFIGOBJECT.signature,
		    jsApiList: [
		      // 所有要调用的 API 都要加到这个列表中
		      'checkJsApi',
		      'onMenuShareTimeline',
		      'onMenuShareAppMessage',
		      'startRecord',
		      'stopRecord',
		      'onVoiceRecordEnd',
		      'playVoice',
		      'pauseVoice',
		      'stopVoice',
		      'onVoicePlayEnd',
		      'uploadVoice',
		      'downloadVoice',
		      
		    ]
		  });

		var localId = null;
		var serverId = null;

		wx.onVoiceRecordEnd({
		    // 录音时间超过一分钟没有停止的时候会执行 complete 回调
		    complete: function (res) {
		        localId = res.localId; 
		    }
		});

		wx.onVoicePlayEnd({
		    success: function (res) {
		        var localId = res.localId; // 返回音频的本地ID
		    }
		});

		

		function start(){
			wx.startRecord();
			setTimeout(stop, 3000);
		}

		function stop(){
			wx.stopRecord({
			    success: function (res) {
			        localId = res.localId;
			        upload();
			    }
			});
		}

		function play(){
			wx.playVoice({
			    localId: localId // 需要播放的音频的本地ID，由stopRecord接口获得
			});
		}

		function pauseVoice(){
			wx.pauseVoice({
			    localId: localId // 需要暂停的音频的本地ID，由stopRecord接口获得
			});
		}

		function stopVoice(){
			wx.stopVoice({
			    localId: localId // 需要停止的音频的本地ID，由stopRecord接口获得
			});
		}

		function upload(){
			wx.uploadVoice({
			    localId: localId, // 需要上传的音频的本地ID，由stopRecord接口获得
			    isShowProgressTips: 1, // 默认为1，显示进度提示
			        success: function (res) {
			        	serverId = res.serverId; // 返回音频的服务器端ID
			        	$.ajax({
							type:"POST",
							global:false,
							url:"/tom",
							data:{serverId:serverId},
							dataType:"JSON",
							success:function(data){
								// $("#img").attr("src","http://wow.kfc.trioisobardev.com/tom/getimg/?file="+data.msg)
								// $("#imgshow").show()
								alert(data.msg)
							}
						});
			        	//alert(serverId);
			    }
			});
		}

		function download(){
			wx.downloadVoice({
			    serverId: serverId, // 需要下载的音频的服务器端ID，由uploadVoice接口获得
			    isShowProgressTips: 1, // 默认为1，显示进度提示
			    success: function (res) {
			        localId = res.localId; // 返回音频的本地ID
			        alert(localId);
			    }
			});
		}

		function addCard()
		{
			wx.addCard({
			    cardList: [<?php $wechat = new \app\models\Wechat; echo $wechat->createWxCardJsConfig();?>], // 需要添加的卡券列表
			    success: function (res) {
			        var cardList = res.cardList; // 添加的卡券列表信息
			        alert('已添加卡券：' + JSON.stringify(res.cardList));
			        //alert(cardList);
			    }
			});
		}

		function openCard()
		{
			wx.openCard({
			    cardList: [{
			        cardId: 'cardId',
			        code: 'cardIcode'
			    }]// 需要打开的卡券列表
			});
		}



	</script>
</head>
<body>
	<input type="button" value="开始录音" onclick="start();">
	<br />
	<input type="button" value="停止录音" onclick="stop();">
	<br />
	<input type="button" value="播放录音" onclick="play();">
	<br />
	<input type="button" value="暂停播放" onclick="pauseVoice();">
	<br />
	<input type="button" value="停止播放" onclick="stopVoice();">
	<br />
	<input type="button" value="上传录音" onclick="upload();">
	<br />
	<input type="button" value="下载录音" onclick="download();">
	<br />
	<input type="button" value="add card" onclick="addCard();">
	<br />
	<input type="button" value="open card" onclick="openCard();">
	<div id="imgshow" style="display:none">
		<img style="width:900px" src="" id="img">
	</div>
</body>
</html>