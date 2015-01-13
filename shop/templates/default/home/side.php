<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/side_right.css" rel="stylesheet" type="text/css">

<div id="ncToolbar" class="nc-appbar" style="right: 0px;">
  <div class="nc-appbar-tabs" id="appBarTabs">
    <div class="user" nctype="a-barLoginBox">
    <?php if($_SESSION['is_login'] == '1'){?>
      <div class="avatar"><img src="<?php echo getMemberAvatarForID($v['geval_frommemberid']);?>"></div>
      <p><a href="<?php echo urlShop('member_snsindex');?>"><?php echo $_SESSION['member_name'];?></a></p>
    <?php }else{?> 
      <div class="avatar"><img src="<?php echo SHOP_TEMPLATES_URL;?>/images/default_user_portrait.gif"></div>
      <p><a href="index.php?act=login&op=index" target="_blank">未登录</a></p>
	<?php }?>
    </div> 
      
    <ul class="tools">
      <li><a href="javascript:void(0);" id="toolbar_cart_r" class="cart">购物车<i><?php echo $output['cart_goods_num'];?></i></a></li>
      <li><a href="javascript:void(0);" id="gotop" class="gotop" style="opacity: 0.5;">顶部</a></li>
    </ul>
    <div class="content-box" id="content-compare">
      <div class="top">
        <h3>商品对比</h3>
        <a href="javascript:void(0);" class="close" title="隐藏"></a></div>
      <div id="comparelist"></div>
    </div>
    <div class="content-box" id="content-cart">
      <div class="top">
        <h3>我的购物车</h3>
        <a href="javascript:void(0);" class="close" title="隐藏"></a></div>
      <div id="toolbar_cartlist_r">

        <ul class="cart-list">
          <li>
            <dl>
              <dd style="text-align: center; ">
                <div class="head-user-menuR">
                  <dl class="my-cartR">
                    <dd>
                      <div class="incart-goods-box">
                        <div class="incart-goods"> <img class="loading" src="<?php echo SHOP_TEMPLATES_URL;?>/images/loading.gif" /> </div>
                      </div>
                      <div class="checkout"> <span class="total-price">共<i><?php echo $output['cart_goods_num'];?></i><?php echo $lang['nc_kindof_goods'];?></span><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=cart" class="btn-cart">结算购物车中的商品</a> </div>
                    </dd>
                  </dl>
                </div>
              </dd>
            </dl>
          </li>
          <div class="header-wrap"> </div>
        </ul>          
      
      </div>
    </div>
    <a id="activator" href="javascript:void(0);" class="nc-appbar-hide"></a> 
  </div>
    
  <div class="nc-hidebar" id="ncHideBar" style="right: -79px;">
    <div class="nc-hidebar-bg">
      <div class="user-avatar"><img src="<?php echo SHOP_TEMPLATES_URL;?>/images/default_user_portrait.gif"></div>
      <div class="frame"></div>
      <div class="show"></div>
    </div>
  </div>
  
</div>


<script type="text/javascript">
//动画显示边条内容区域
$(function() {
	$(function() {
		$('#activator').click(function() {
			$('#content-cart').animate({'right': '-250px'});
			$('#content-compare').animate({'right': '-150px'});
			$('#ncToolbar').animate({'right': '-60px'}, 300,
			function() {
				$('#ncHideBar').animate({'right': '59px'},	300);
			});
	        $('div[nctype^="bar"]').hide();
		});
		$('#ncHideBar').click(function() {
			$('#ncHideBar').animate({
				'right': '-79px'
			},
			300,
			function() {
				$('#content-cart').animate({'right': '-250px'});
				$('#content-compare').animate({'right': '-250px'});
				$('#ncToolbar').animate({'right': '0'},300);
			});
		});
	});
    $("#compare").click(function(){
    	if ($("#content-compare").css('right') == '-210px') {
 		   loadCompare(false);
 		   $('#content-cart').animate({'right': '-210px'});
  		   $("#content-compare").animate({right:'50px'});
    	} else {
    		$(".close").click();
    		$(".chat-list").css("display",'none');
        }
	});
    $("#toolbar_cart_r").click(function(){
        if ($("#content-cart").css('right') == '-210px') {
         	$('#content-compare').animate({'right': '-210px'});
    		$("#content-cart").animate({right:'50px'});
    		if (!$("#toolbar_cartlist_r").html()) {
    			$("#toolbar_cartlist_r").load('index.php?act=cart&op=ajax_load&type=html');
    		}
        } else {
        	$(".close").click();
        	$(".chat-list").css("display",'none');
        }
	});
	$(".close").click(function(){
		$("#content-cart").animate({right:'-210px'});
      });

	$(".quick-menu dl").hover(function() {
		$(this).addClass("hover");
	},
	function() {
		$(this).removeClass("hover");
	});

    // 右侧bar用户信息
    $('div[nctype="a-barUserInfo"]').click(function(){
        $('div[nctype="barUserInfo"]').toggle();
    });
    // 右侧bar登录
    $('div[nctype="a-barLoginBox"]').click(function(){
        $('div[nctype="barLoginBox"]').toggle();
        document.getElementById('codeimage').src='index.php?act=seccode&op=makecode&nchash=0fb732d7&t=' + Math.random();
    });
    $('a[nctype="close-barLoginBox"]').click(function(){
        $('div[nctype="barLoginBox"]').toggle();
    });
    });
</script>

<script>
//返回顶部
backTop=function (btnId){
	var btn=document.getElementById(btnId);
	var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
	window.onscroll=set;
	btn.onclick=function (){
		btn.style.opacity="0.5";
		window.onscroll=null;
		this.timer=setInterval(function(){
		    scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			scrollTop-=Math.ceil(scrollTop*0.1);
			if(scrollTop==0) clearInterval(btn.timer,window.onscroll=set);
			if (document.documentElement.scrollTop > 0) document.documentElement.scrollTop=scrollTop;
			if (document.body.scrollTop > 0) document.body.scrollTop=scrollTop;
		},10);
	};
	function set(){
	    scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
	    btn.style.opacity=scrollTop?'1':"0.5";
	}
};
backTop('gotop');
</script>
