<?php defined('InShopNC') or exit('Access Invalid!');?>
<style type="text/css">
.card{background:#FCF8E3;}
.card form{width:480px; margin:0 auto; padding-top:20px;}
.card ul li{padding:10px 20px;}
.card ul li input{height:20px; line-height:20px;}
.card ul li.sub{padding-left:100px;}
.card ul li.sub input{height:35px;}
</style>

<div class="wrap">
  <div class="tabmenu">
    <?php include template('layout/submenu');?>
  </div>
  <div class="card">
	  <form method="post" action="index.php?act=predeposit&op=gift_card">
		<input type="hidden" name="form_submit" value="ok" />
		<ul>
		  <li><span class="red">* </span>充值卡卡号：<input type="text" id="number" name="number" size="25" onkeyup="value=value.replace(/[^\d]/g,''),checkNumber();" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))" /><lable id="number_tit" class="red"></lable></li>
		  <li><span class="red">* </span>充值卡密钥：<input type="text" id="key" name="key" size="25" onkeyup="checkKey();" /><lable id="key_tit" class="red"></lable></li>
		  <li class="sub"><input type="submit" value="确认充值" /></li>
		</ul>
	  </form>
  </div>
</div>

<script type="text/javascript">
function checkNumber(){
	var number = document.getElementById('number').value;
	if(number.length!=10){
		document.getElementById('number_tit').innerText=" × 充值卡卡号必须为10位数字";
	}else{
		document.getElementById('number_tit').innerText="";
	}
}

function checkKey(){
	var number = document.getElementById('key').value;
	if(number.length!=8){
		document.getElementById('key_tit').innerText=" × 充值卡密钥必须为8个字符";
	}else{
		document.getElementById('key_tit').innerText="";
	}
}
</script>
