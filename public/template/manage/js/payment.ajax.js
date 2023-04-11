// JavaScript Document ajax请求
$(function () {

	//ajax提交	
	$("#pay-query,#pay-post,#pay-refuse").click(function () {

		var me = $(this).attr("id");
		$("#pay-post").attr('disabled', true);
		var submit_url = $('#' + me).attr('data-url');

		if (me == 'pay-query') {
			used(submit_url);
			return;
		}

		switch (me) {
			case 'pay-post':
				me = '同意付款';
				break;
			case 'pay-refuse':
				me = '拒绝付款';
				break;
		}


		layer.confirm('确认要' + me + '吗？', {
				icon: 3,
				title: me,
				btn: ['确认', '取消'] //按钮
			},
			function () {
				used(submit_url);
			},
			function () {
				layer.msg('取消操作', {
					icon: 2
				});
				return;
			});

		function used(submit_url) {
			$.ajax({
				url: submit_url,
				type: 'POST',
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function (data) {
					if (data.status === 1) {
						layer.msg(data.msg, {
							icon: 1
						});
						setTimeout(function () {
								window.parent.location.reload(); //刷新父窗口
								var index = parent.layer.getFrameIndex(window.name);
								parent.layer.close(index); //关闭窗口
							},
							1000);

					} else {
						layer.msg(data.msg, {
							icon: 5
						});
						setTimeout(function () {
								$("#pay-post").removeAttr("disabled");
							},
							1000);
					}
				},
				error: function () {
					//console.log(submit_url);
					layer.msg('请求异常', {
						icon: 5
					});
					setTimeout(function () {
							$("#pay-post").removeAttr("disabled");
						},
						1000);

				}
			});
		}
	});

});
