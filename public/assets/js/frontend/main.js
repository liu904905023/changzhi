
var uploaders = [];

var options = {
    'bucket': 'match-file',
    'save-key': '/{year}/{mon}/{day}/{filemd5}{.suffix}',
    'expiration': Math.floor(new Date().getTime() / 1000) + 86400
};
// 查看更多参数：http://docs.upyun.com/api/form_api/#表单API接口简介
var policy = window.btoa(JSON.stringify(options));
// 从 UPYUN 用户管理后台获取表单 API
var form_api_secret = 'mxN/2nNFlLDaC3K/M8RrSENsDAw=';
// 计算签名
var signature = md5(policy + '&' + form_api_secret);
var initUploaders = function (uploaders) {
    $(".browse_button").each(function () {
        self = $(this);
        var browse_button_id = self.attr('id'),
            base = 'http://matchfile.wlzjedu.com',
            upload_url = 'https://v0.api.upyun.com/' + options.bucket,
            max_size = self.attr('data-max-size'),
            file_extensions = self.attr('data-extensions'),

            input = self.siblings('input'),
            process = self.siblings('.process');

        var flash_swf_url = './plupload/Moxie.swf',
            silverlight_xap_url = './plupload/Moxie.xap';

        var index = null; // 遮罩实例

        var uploader = new plupload.Uploader({
            runtimes: 'html5,flash,silverlight,html4',
            browse_button: browse_button_id,
            url: upload_url,
            max_retries: 3,     //允许重试次数
            flash_swf_url: flash_swf_url,
            silverlight_xap_url: silverlight_xap_url,
            multipart_params: {
                'Filename': '${filename}', // adding this to keep consistency across the runtimes
                'Content-Type': '',
                'policy': policy,
                'signature': signature,
            },
            filters: {
                max_file_size: max_size,
                mime_types: [
                    {title: "Image files", extensions: file_extensions}
                ]
            },

            init: {
                PostInit: function () {
                },

                FilesAdded: function (up, files) {
                    plupload.each(files, function (file) {
                        // process.attr('id', file.id).removeClass('none');
                        process.find('.filename').html(file.name);
                        // browse_button_id.siblings('.filename').html(file.name)
//                            process.find('.filesize').html(plupload.formatSize(file.size) + ', ');
                    });
                    $('.mask').show();
                    up.start();
                },

                UploadProgress: function (up, file) {
                    $('.percent').html(file.percent + '%');
                },

                FileUploaded: function (up, file, result) {
                    var responseJson = JSON.parse(result.response);
                    var filepath = base+responseJson.url;
                    input.val(filepath);
                    $('.mask').hide();
                    $("#resultTip").find(".project-modal-del").text("上传成功");
                    $("#resultTip").modal("show");
                },
                Error: function (up, err) {
                    $("#resultTip").find(".project-modal-del").text(err.message);
                    $("#resultTip").modal("show");
                }
            }
        });

        uploader.init();
        uploaders.push(uploader);
    });
};
initUploaders(uploaders);
