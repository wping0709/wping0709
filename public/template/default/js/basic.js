function ClearSubmit(e) {
	if (e.keyCode == 13) {
		var key = $('#search-input').val();
		search();
	}
}

function search() {
	var key = $('#search-input').val();
	if (key) {
		window.location.href = "/search/tag/" + key + ".shtml";
	} else {
		layer.msg('啥都没输入', {
			icon: 0
		});

	}
}

function reply(rid, rname) {
	$('.comment-form h4').text('回复：' + rname + '(' + rid + ')');
	$('#up-id').val(rid);
}

$(function () {
	$("#send-data").click(function () {
		var aid = $('input[name=aid]').val();
		var up = $('input[name=up]').val();
		var type = $('input[name=type]').val();
		var name = $('input[name=name]').val();
		var email = $('input[name=email]').val();
		var url = $('input[name=url]').val();
		var content = $('textarea[name=content]').val();
		var formData = new FormData($('form')[0]);
		//获取表单FILE名称
		formData.append('aid', aid);
		formData.append('up', up);
		formData.append('name', name);
		formData.append('email', email);
		formData.append('url', url);
		formData.append('content', content);
		if (!name) {
			layer.msg('请填写昵称', {
				icon: 0
			});
			return;
		}

		if (name.length < 2) {
			layer.msg('昵称太短，需要大于2个字', {
				icon: 0
			});
			return;
		}

		if (!email) {
			layer.msg('请填写邮箱', {
				icon: 0
			});
			return;
		}

		var myreg = /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
		if (!myreg.test(email)) {
			layer.msg('请填写正确的邮箱', {
				icon: 0
			});
			return;
		}


		if (!content) {
			layer.msg('请填写内容', {
				icon: 0
			});
			return;
		}

		if (content.length < 5) {
			layer.msg('内容太短，需要大于5个字', {
				icon: 0
			});
			return;
		}


		var re = new RegExp("^[a-zA-Z0-9]+$");
		if (re.test(content)) {
			layer.msg('说点有意义的吧？', {
				icon: 0
			});
			return;
		}


		var msg = layer.msg('发送中...', {
			icon: 16,
			shade: 0.01
		});
		$.ajax({
			url: '/data/channel/comment',
			type: 'POST',
			dataType: 'json',
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (ReturnData) {
				if (ReturnData.status === 1) {
					setTimeout(function () {
							var msg = layer.msg(ReturnData.msg, {
								icon: 1
							});
						},
						500)
				} else {
					var msg = layer.msg(ReturnData.msg, {
						icon: 0
					});
				}
				setTimeout(function () {
						layer.close(msg);
						window.location.reload();
					},
					2000)

			},
			error: function () {
				alert("数据加载失败！");
			}
		});

	});

	$(".single-gallery-image").click(function () {
		var i = $(".single-gallery-image").index(this);
		i = parseInt(i);


		var img = $(".single-gallery-image").eq(i).attr('data-img');
		var explain = $(".single-gallery-image").eq(i).attr('data-explain');
		var time = $(".single-gallery-image").eq(i).attr('data-time');

		$("#time").text(time);
		$("#title").text(explain);
		$("#img").attr('src', img);

		$("#up").attr('data', i - 1);

		$("#down").attr('data', i + 1);

		$('.photo-view').show();
	});



	$("#up,#down,#close").click(function () {
		var id = $(this).attr("id");
		if (id === 'close') {
			$('.photo-view').hide();
			return;
		}
		var i = parseInt($(this).attr('data'));
		var img = $(".single-gallery-image").eq(i).attr('data-img');
		var explain = $(".single-gallery-image").eq(i).attr('data-explain');
		var time = $(".single-gallery-image").eq(i).attr('data-time');
		var lenth = $(".single-gallery-image").length - 2;
		switch (id) {
			case 'up':
				if (i === -1) {
					i = lenth;
				} else {

					i = i - 1;
				}
				$("#up").attr('data', i);
				break;
			case 'down':
				if (i > lenth) {
					i = 0;
				} else {
					i = i + 1;
				}
				$("#down").attr('data', i);
				break;
		}

		$("#time").text(time);
		$("#title").text(explain);
		$("#img").attr('src', img);
		$('.photo-view').show();

	});


});
