$(function() {	
	
//刷新验证码
$("#codeimg").click(function () {
    $("#codeimg").attr("src","/captcha?="+Math.random());
})	
	
	
$(document).keyup(function(event){
  if(event.keyCode ==13){
	  $("#save-data").click();
  }
})	
	$("#save-data").click(function () {
		 $("#save-data").attr('disabled',true).prop('class','submit btn-lock');
			var submit_url=window.location.href;
			var formData = new FormData($('form')[0]);
			$.ajax({
            url: submit_url,
            type: 'POST',
            dataType: 'json',  
			data: formData,
			processData: false, 
    		contentType: false,
            success: function(data) {
                if (data.status === 1) {
					layer.msg(data.info, {icon: 1});
					setTimeout(function(){
					window.location.href = "index.shtml";
					}, 1000);
				}else{
					$("#codeimg").click();
					layer.msg(data.info, {icon: 5});
					setTimeout(function(){
					$("#save-data").removeAttr("disabled").prop("class","submit btn-submit");
					}, 1000);
                }
			},
		error: function(){
			console(submit_url);
			layer.msg('请求异常', {icon: 5});
        }  
		});	
	});
})