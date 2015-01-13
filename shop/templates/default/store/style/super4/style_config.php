<?php
defined('InShopNC') or exit('Access Invalid!');

$style_template = <<<EOT
<div style="background:url(templates/default/store/style/super4/images/cen.jpg) no-repeat center top;height:350px;">
</div>
<div style="position:relative;margin:10px auto 0px;width:1200px;height:530px;">
	<div style="z-index:1;position:absolute;width:600px;height:263px;visibility:visible;top:0px;left:0px;">
		<a href="http://www.b0635.com" target="_blank"><img style="border-bottom:0px;border-left:0px;border-top:0px;border-right:0px;" alt="image" src="templates/default/store/style/super4/images/lwkj_r1_c1.jpg" width="600" height="263" /></a> 
	</div>
	<div style="z-index:2;position:absolute;width:300px;height:263px;visibility:visible;top:0px;left:600px;">
		<a href="http://www.b0635.com" target="_blank"><img style="border-bottom:0px;border-left:0px;border-top:0px;border-right:0px;" alt="image" src="templates/default/store/style/super4/images/lwkj_r1_c4.jpg" width="300" height="263" /></a> 
	</div>
	<div style="z-index:3;position:absolute;width:300px;height:263px;visibility:visible;top:0px;left:900px;">
		<a href="http://www.b0635.com" target="_blank"><img style="border-bottom:0px;border-left:0px;border-top:0px;border-right:0px;" alt="image" src="templates/default/store/style/super4/images/lwkj_r1_c5.jpg" width="300" height="263" /></a> 
	</div>
	<div style="z-index:4;position:absolute;width:199px;height:265px;visibility:visible;top:263px;left:0px;">
		<a href="http://www.b0635.com" target="_blank"><img style="border-bottom:0px;border-left:0px;border-top:0px;border-right:0px;" alt="image" src="templates/default/store/style/super4/images/lwkj_r2_c1.jpg" width="199" height="265" /></a> 
	</div>
	<div style="z-index:5;position:absolute;width:201px;height:265px;visibility:visible;top:263px;left:199px;">
		<a href="http://www.b0635.com" target="_blank"><img style="border-bottom:0px;border-left:0px;border-top:0px;border-right:0px;" alt="image" src="templates/default/store/style/super4/images/lwkj_r2_c2.jpg" width="201" height="265" /></a> 
	</div>
	<div style="z-index:6;position:absolute;width:200px;height:265px;visibility:visible;top:263px;left:400px;">
		<a href="http://www.b0635.com" target="_blank"><img style="border-bottom:0px;border-left:0px;border-top:0px;border-right:0px;" alt="image" src="templates/default/store/style/super4/images/lwkj_r2_c3.jpg" width="200" height="265" /></a> 
	</div>
	<div style="z-index:7;position:absolute;width:600px;height:265px;visibility:visible;top:263px;left:600px;">
		<a href="http://www.b0635.com" target="_blank"><img style="border-bottom:0px;border-left:0px;border-top:0px;border-right:0px;" alt="image" src="templates/default/store/style/super4/images/lwkj_r2_c4.jpg" width="600" height="265" /></a> 
	</div>
</div>


EOT;


$style_info = <<<EOT
	<img alt="" style="width:300px;float:left;margin:0px;" src="templates/{$tpl_name}/store/style/super4/images/style.jpg" border="0" />
  <div style=" float: right;margin: 0 auto;width:300px;  line-height:24px;">
    <p style="color:red"> <strong>可编辑区域的宽度为通栏100% <strong></p>
    <p> <strong>1</strong>号宽100%的div，图片为背景居中，宽1500px，达到通栏的效果  </p>
    <p> <strong>2</strong>号图片区域，宽1200px，也可以尝试其他宽度。为了设置居中效果，请给与样式margin:0 auto;</p>
    <input type="button" name="" value="恢复默认" onclick="insert_template();">
    <p> <span style="color:#999999;">编辑器使用技巧：</span> </p>
    <p style="margin-left:20px;"> <span style="color:#999999;">1可以用“HTML代码”按钮来查看编辑代码。</span> </p>
    <p style="margin-left:20px;"> <span style="color:#999999;">2如果一行要插入多个图片，可先用“表格”按钮分隔。</span> </p>
    <p style="margin-left:20px;"> <span style="color:#999999;">3默认模板中的链接地址为当前网站地址。</span> </p>
    <p style="margin-left:20px;"> <span style="color:#999999;">4“插入相册图片”的链接地址为当前店铺的地址。</span> </p>
    <p style="margin-left:20px;"> <span style="color:#999999;">5编辑器中图片的链接地址可以在鼠标右键的弹出菜单中选择修改。</span> </p>
    <p style="margin-left:20px;"> <span style="color:#999999;">6编辑器中所有内容均可替换修改。</span> <br />
    </p>
  </div>
EOT;

?>