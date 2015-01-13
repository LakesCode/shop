<?php defined('InShopNC') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/home_special.css" rel="stylesheet" type="text/css">

<div class="warp-all">
  <div class="mainbox">
    <?php if(!empty($output['special_list']) && is_array($output['special_list'])) {?>
    <ul class="special-list">
      <?php foreach($output['special_list'] as $value) {?>
      <li>
        <h3 class="special-title"> <a href="<?php echo $value['special_link'];?>" target="_blank"> <?php echo $value['special_title'];?> </a> </h3>
        <div class="special-cover"> <a href="<?php echo $value['special_link'];?>" target="_blank"> <img src="<?php echo getCMSSpecialImageUrl($value['special_image']);?>" alt="" /></a> </div>
      </li>
      <?php } ?>
    </ul>
    <div class="pagination mt10 mb10"> <?php echo $output['show_page'];?> </div>
    <?php } else { ?>
    <div class="no-content">暂无专题内容</div>
    <?php } ?>
  </div>
</div>
