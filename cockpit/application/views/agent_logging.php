<?php $this->load->view('header'); ?>

<form action="<?php echo site_url('/agent/login');?>" method="post" id="form-logging">
	<p>USERNAME: <input type="text" name="name" /></p>
	<p>PASSWORD: <input type="password" name="password" /></p>
	<p><button type="submit" class="btn btn-primary">LOGIN</button></p>
	<p id="p-err"></p>
</form>

<script type="text/javascript">
$('#form-logging').submit(function(){
	$.get($(this).attr('action'), $(this).serialize(), function(data) {
        if (data.success) {
        	window.location.reload();
        } else {
        	$('#p-err').html('Incorrect name or password.');
        }
    }, "json");
	return false;
});
$('#tab-logging').addClass('active');
</script>
<?php $this->load->view('footer'); ?>
