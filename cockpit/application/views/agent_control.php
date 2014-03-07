<?php $this->load->view('header'); ?>

<p><?php echo $user['name'];?>様、お帰りなさい。</p>

<p>お前のアカウントには <?php echo $user['credit_balance'];?>ポイント がありますよ〜</p>

<div id="stage">

</div>

<script type="text/template" id="tpl-password">
    <form action="<?php echo site_url('/agent/changepwd');?>" method="post" id="form-pwd">
        <p>CURRENT PWD: <input type="password" name="current_password" /></p>
        <p>NEW PASSWOD: <input type="password" name="new_password" /></p>
        <p><button type="submit" class="btn btn-danger">DO IT</button></p>
        <p id="p-err"></p>
    </form>
</script>

<p><button href="<?php echo site_url('/agent/status');?>" class="btn btn-primary btn-status">STATUS</button></p>

<p><button href="<?php echo site_url('/make');?>" class="btn btn-danger btn-lnk">MAKE TICKET</button></p>

<p><button class="btn btn-primary btn-password">CHANGE PASSWORD</button></p>

<p><button href="<?php echo site_url('/agent/log');?>" class="btn btn-info btn-log">CREDIT LOG</button></p>

<p><button href="<?php echo site_url('/agent/logout');?>" class="btn btn-danger btn-lnk">LOGOUT</button></p>

<script type="text/javascript">
function make_request(url, callback) {
    $.get(url, function(data) {
        if (!data.success) {
            $('#stage').html('FAILED TO MAKE REQUEST!');
        } else {
            callback(data);
        }
    }, "json");
}
$('.btn-lnk').click(function(){
    window.location.href = $(this).attr('href');
    return false;
});
$('.btn-status').click(function(){
    make_request($(this).attr('href'), function(data) {
        $('#stage').html('');
        for (var k in data) {
            $('<p>' + k + ': ' + data[k] + '</p>').appendTo('#stage');
        }
    });
});
$('.btn-log').click(function(){
    make_request($(this).attr('href'), function(data) {
        $('#stage').html('これはログです：');
        for (var i in data.logs) {
            var row = data.logs[i];
            $('<p>TICKET ' + row.ticket + ': DIFF ' + row.diff + ' BALANCE ' + row.balance + '</p>').appendTo('#stage');
        }
        $('<p>ーーー終わるーーー</p>').appendTo('#stage');
    });
});
$('.btn-password').click(function(){
    $('#stage').html($('#tpl-password').html());
    $('#form-pwd').submit(function(){
        $.get($(this).attr('action'), $(this).serialize(), function(data) {
            if (data.success) {
                $('#stage').html('NEW PASSWORD SET!');
            } else {
                $('#stage').html('FAILED TO CHANGE PASSWORD!');
            }
        }, "json");
        return false;
    });
});
$('#tab-control').addClass('active');
</script>
<?php $this->load->view('footer'); ?>
