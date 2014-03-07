<?php
$this->load->helper('user');
$current_user = get_user();
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="<?php echo base_url('/static/ticket.css');?>" />
    <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</head>

<body>
<div class="site-wrapper">
  <div class="site-wrapper-inner">
    <div class="cover-container">
      <header>
        <div class="masthead clearfix">
          <div class="inner">
            <h3 class="masthead-brand">RAPTOR</h3>
            <ul class="nav masthead-nav">
<?php
if ($current_user) {?>
              <li><?php echo $current_user['name'];?></li>
              <li id="tab-control"><a href="#">用户中心</a></li>
<?php
} else {?>
              <li id="tab-logging"><a href="<?php echo site_url('/agent');?>">登录</a></li>
<?php
}?>
              <li id="tab-ticket"><a href="<?php echo site_url('/ticket');?>">即时提货</a></li>
              <li><a href="#">客户服务</a></li>
            </ul>
          </div>
        </div>
      </header>

