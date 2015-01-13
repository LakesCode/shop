//jd_side js
$(function(){
            /*监听滚动条和左侧菜单点击事件 start*/
            var _arr = [];
            window.onscroll = function(){
                if(1300 < $(document).scrollTop()){
                    $('.nav_Sidebar').fadeIn('slow');
                }else{
                    $('.nav_Sidebar').fadeOut('slow');
                }
                if(($('#footer').offset().top - $('.nav_Sidebar').height() - 50) <= $(document).scrollTop()){
                    $('.nav_Sidebar').css({'bottom':$('#footer').outerHeight() + 'px'});
                }else{
                    $('.nav_Sidebar').css({'bottom':'100px'});
                }
                $('.home-standard-layout').each(function(index){
                    var that = $(this);
                    that.index = index;
                    if($(document).scrollTop() + $(window).height()/2 > that.offset().top){
                        _arr.push(index);
                    }
                });
                if(_arr.length){
                    $('.nav_Sidebar a').eq(_arr[_arr.length-1]).css({'backgroundImage':'url(/shop/templates/default/images/jd_side-active.png)'}).addClass('current').siblings().css({'backgroundImage':'url(/shop/templates/default/images/jd_side.png)'}).removeClass('current');
                    _arr = [];
                }
            }
            $('.nav_Sidebar a').each(function(index){
                $(this).click(function(){
                    $('html,body').animate({scrollTop: $('.home-standard-layout').eq(index).offset().top - 20 + 'px'}, 200);
                }).mouseover(function(){
                    if($(this).hasClass('current')){
                        return;
                    }else{
                        $(this).css({'backgroundImage':'url(/shop/templates/default/images/jd_side-hover.png)'});
                    }
                }).mouseout(function(){
                    if($(this).hasClass('current')){
                        return;
                    }else{
                        $(this).css({'backgroundImage':'url(/shop/templates/default/images/jd_side.png)'});
                    }
                });
            });
            /*end*/
            window.onload = window.onresize = function(){
                if($(window).width() < 1300 || 500 > $(document).scrollTop()){
                    $('.nav_Sidebar').fadeOut('slow');
                }else{
                    $('.nav_Sidebar').fadeIn('slow');
                }
                
            }		   
});
