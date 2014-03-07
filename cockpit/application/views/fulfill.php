<?php $this->load->view('header'); ?>

<div class="inner cover">
    <h1 class="cover-heading">即时提货</h1>
    <p class="lead">请粘贴或输入提货编码，我们会协助您即时完成提货。</p>
    <p>
        <input type="text" id="number" name="number" placeholder="提货编码" maxlength="19"/>
    </p>
    <div id="detail" style="display:none">
        <p id="info">发货信息</p>
        <div id="fields"></div>
        <p>
            <a class="btn btn-lg btn-default field-input" href="#" id="btn-fulfill">立即提货</a>
        </p>
    </div>
    <div id="splash" style="display:none">
        <img src="<?php echo base_url('/static/ajax-loader.gif');?>" />
    </div>
    <div id="error" style="display:none">
        <p id="error-info"></p>
    </div>
</div>

<script type="text/javascript">
var locking = false;
function show_error(msg) {
    $('#error-info').html(msg);
    $('#error').fadeIn();
}
function hide_error() {
    $('#error').fadeOut(10);
}
function check_number() {
	var number = $('#number').val();
    if (number.length != 19) {
        return true;
    }
    if (locking) return;
    locking = true;
    hide_error();
    $('#splash').fadeIn();
    $.get("<?php echo site_url('/ticket/valid');?>/" + number, function(data) {
        locking = false;
        $('#splash').fadeOut(10);
        if (!data.valid) {
            show_error('您提供的提货编码无效。');
            return;
        }
        $('#number').attr('readonly', 'readonly');
        var fields = '';
        for (var id in data.fields) {
            var f = data.fields[id];
            switch (f.type) {
                case 'text':
                    fields += '<p><input class="field-input" type="text" name="' + id + '" placeholder="' + f.text + '"/></p>';
                    break;
                case 'select':
                    fields += '<p><select class="field-input" name="' + id + '">';
                    for (var i in f.items) {
                        var row = f.items[i];
                        fields += '<option value="' + row['id'] + '">' + row['name'] + '</option>';
                    }
                    fields += '</select></p>';
            }
        }
        $('#fields').html(fields);
        $('#info').html(data.name + ' ' + data.count + data.scale);
        $('#detail').fadeIn();
        $('#fields input:eq(0)').focus();
    }, "json");
	return true;
}
function resetpage() {
    $('#detail').fadeOut(10);
    $('#number').val('').removeAttr('readonly').focus();
}
$('#btn-fulfill').click(function() {
    $('#splash').fadeIn();
    hide_error();
    $('.field-input').attr('readonly', 'readonly');
    $.post("<?php echo site_url('/ticket/fulfill');?>", $('input, select').serialize(), function(data) {
        $('#splash').fadeOut(10);
        if (!data.success) {
            show_error('提货失败，请重新确认您提供的信息。');
            return;
        }
        resetpage();
        show_error('提货成功，如果长时间未到账请与客户服务取得联系。');
    }, "json");
});
$('#number').keyup(check_number);
$('#tab-ticket').addClass('active');
</script>

<?php $this->load->view('footer'); ?>
