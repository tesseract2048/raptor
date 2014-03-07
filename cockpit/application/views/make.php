<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css" />
    <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</head>


<div class="inner cover">
    <h1 class="cover-heading">生成提货编码</h1>
    <table>
<?php
function cate_bg($idx) {
    if ($idx % 2 == 0) {
        return '#CCCCCC';
    }
    return '#AAAAAA';
}
function subcate_bg($idx) {
    if ($idx % 2 == 0) {
        return '#CCCCFF';
    }
    return '#AAAAFF';
}
function product_bg($idx) {
    if ($idx % 2 == 0) {
        return '#CCFFCC';
    }
    return '#AAFFAA';
}
$cate_idx = 0;
$subcate_idx = 1;
$product_idx = 0;
foreach ($tree as $cate) {
    $cate_printed = FALSE;
    $rowspan = 0;
    foreach ($cate['subcates'] as $subcate) {
        foreach ($subcate['products'] as $product) {
            $rowspan += 1;
        }
    }
    foreach ($cate['subcates'] as $subcate) {
        $subcate_printed = FALSE;
        foreach ($subcate['products'] as $product) {
?>
    <tr>
        <?php if (!$cate_printed) {
            $cate_printed = TRUE;?>
            <td rowspan="<?php echo $rowspan;?>" style="background-color: <?php echo cate_bg($cate_idx++);?>"><?php echo $cate['name'];?></td>
        <?php }?>
        <?php if (!$subcate_printed) {
            $subcate_printed = TRUE;?>
            <td rowspan="<?php echo count($subcate['products']);?>" style="background-color: <?php echo subcate_bg($subcate_idx++);?>"><?php echo $subcate['name'];?></td>
        <?php }?>
        <td style="background-color: <?php echo product_bg($product_idx);?>"><?php echo $product['name'];?><?php echo $product['norm_value'];?><?php echo $product['scale'];?></td>
        <td style="background-color: <?php echo product_bg($product_idx);?>"><?php echo $product['stock_price'];?>￥</td>
        <td style="background-color: <?php echo product_bg($product_idx++);?>" class="product-row" data-id="<?php echo $product['id'];?>" data-name="<?php echo $product['name'];?>">
            <input type="text" class="product-count" value="1" size="2"/>倍面值 
            <input type="text" class="ticket-count" value="0" size="2"/>张
        </td>
    </tr>
<?php
        }
    }
}
?>
    </table>
</div>

<button id="btn-make">生成提货编码!</button>

<div id="make-result"></div>

<script type="text/javascript">
$('#btn-make').click(function() {
    $('.product-row').each(function() {
        var productId = $(this).data('id');
        var productName = $(this).data('name');
        var productCount = $(this).find('.product-count').val();
        var ticketCount = $(this).find('.ticket-count').val();
        if (ticketCount == 0) return;
        $.get("<?php echo site_url('/make/create');?>/" + productId + "/" + productCount + "/" + ticketCount, function(data) {
            if (!data.success) {
                $('<p>' + productName + '创建失败: 面额倍数范围为 ' + data.min + ' - ' + data.max + '</p>').appendTo('#make-result');
            } else {
                for (var i in data.tickets) {
                    $('<p>' + productName + '*' + productCount + ' 的提货编码为: ' + data.tickets[i] + '</p>').appendTo('#make-result');
                }
            }
        }, "json");
    });
});
</script>

</body>
</html>