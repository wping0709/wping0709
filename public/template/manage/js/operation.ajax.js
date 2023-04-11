// JavaScript Document ajax请求
$(function () {

	//设置标题
	if ($('.navbar-page-title').length > 0) {
		var t = $(document).attr('title').split("-");
		$('.navbar-page-title').text(t[0]);
	}

	//回车键保存
	$(document).keyup(function (event) {
		if (event.keyCode == 13) {
			$("#save-data").click();
		}
	});

	//ajax提交	
	if ($('#save-data').length > 0) {
		$("#save-data").click(function () {
			$("#save-data").attr('disabled', true);

			var submit_url = window.location.href;
			var formData = new FormData($('form')[0]);
			$.ajax({
				url: submit_url,
				type: 'POST',
				dataType: 'json',
				data: formData,
				processData: false,
				contentType: false,
				success: function (data) {
					if (data.status === 1) {
						layer.msg(data.info, {
							icon: 1
						});
						setTimeout(function () {
								window.parent.location.reload(); //刷新父窗口
								var index = parent.layer.getFrameIndex(window.name);
								parent.layer.close(index); //关闭窗口
							},
							500);

					} else {
						layer.msg(data.info, {
							icon: 5
						});
						setTimeout(function () {
								$("#save-data").removeAttr("disabled");
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
							$("#save-data").removeAttr("disabled");
						},
						1000);

				}
			});
		});
	}




	$(".Set_state").click(function () {
		var state = 0;
		var i = $(".Set_state").index(this);

		if ($(".Set_state").get(i).checked) {
			state = 1;
		}
		var dataid = $(".Set_state").eq(i).attr('data-id');
		operation('state', dataid, state);
	});


	//数据操作
	$(".operation").click(function () {
		var i = $(".operation").index(this);
		var url = $(".operation").eq(i).attr('url');
		var imp = $(".operation").eq(i).attr('imp');
		var size = $(".operation").eq(i).attr('size');

		var title = $(".operation").eq(i).attr('title');
		if (!title) {
			title = $(".operation").eq(i).attr('data-original-title');
		}
		var w=$(window).width();
		var x,y;
		if(size=='' && w > 900){
			x=$(window).width()-($(window).width()*40/100);
			y=$(window).height()-($(window).height()*10/100);
			size=x+','+y;
		}else if(w < 900){
			x=$(window).width()-($(window).width()*5/100);
			y=$(window).height()-($(window).height()*20/100);
			size=x+','+y;
		}
		
		
		
		if (size) {
			size = size.split(",");
		}

		switch (imp) {
			case 'newpage':
				layer.open({
					type: 2,
					title: title,
					area: [size[0] + 'px', size[1] + 'px'],
					fixed: false,
					//不固定
					maxmin: false,
					content: url
				});
				break;
			case 'delete':
				var dataid = $(".operation").eq(i).attr('data-id');
				if (!dataid) {
					layer.msg('请选择数据', {
						icon: 5
					});
					return;
				}
				operation('delete', dataid, 0);
				break;

			case 'moredelete':
				var dataid = "";
				$.each($("input[name='ids[]']:checked"), function () {
					dataid += ',' + $(this).val();
				});

				if (!dataid) {
					layer.msg('您当前未选择任何数据', {
						icon: 5
					});
					return;
				}

				operation('delete', dataid, 0);
				break;

		}

	});


	//删除功能
	function operation(used, dataid, state) {
		var url = window.location.href;

		switch (used) {
			//删除	
			case 'delete':

				layer.confirm('确认要删除吗？', {
						btn: ['确认', '取消'] //按钮
					},
					function () {

						$.get(url, {
								used: used,
								dataid: dataid,
							},
							function (data) {
								if (data.status === 1) {
									layer.msg(data.info, {
										icon: 1
									});
									setTimeout(function () {
											window.parent.location.reload(); //刷新父窗口
											var index = parent.layer.getFrameIndex(window.name);
											parent.layer.close(index); //关闭窗口
										},
										500);

								} else {
									layer.msg(data.info, {
										icon: 5
									});
								}
							});

					},
					function () {
						layer.msg('取消操作', {
							icon: 2
						});
					});

				break;

			case 'state':


				$.get(url, {
						used: used,
						dataid: dataid,
						state: state,
					},
					function (data) {
						if (data.status === 1) {
							layer.msg(data.info, {
								icon: 1
							});
							setTimeout(function () {
									window.parent.location.reload(); //刷新父窗口
									var index = parent.layer.getFrameIndex(window.name);
									parent.layer.close(index); //关闭窗口
								},
								500);

						} else if(data.status === 2) {
									layer.msg(data.info, {
										icon: 1
									});
						} else {
							layer.msg(data.info, {
								icon: 5
							});
						}
					});
				break;
		}

	}

});

//获取当前URL，不包含参数	
function GetUrlPara() {　　　　
	var url = document.location.toString();　　　　
	var arrUrl = url.split("?");　　　　
	var para = arrUrl[0];
	self.location.href = para;
}


//清空系统缓存
function Getcache(url) {
	$.get(url, {},
		function (data) {
			if (data.status === 1) {

				layer.msg(data.info, {
					icon: 1
				});

				setTimeout(function () {
						window.parent.location.reload(); //刷新父窗口
						var index = parent.layer.getFrameIndex(window.name);
						parent.layer.close(index); //关闭窗口
					},
					1000);

			} else {
				layer.msg(data.info, {
					icon: 5
				});
			}
		});

}
