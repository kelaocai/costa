var jiathis_config = {
    url:"",
    summary:"",
    pic:"",
    title:"",
    hideMore:false,
    appkey:{
        "tsina":"-111",
        "tqq":"-111",
        "t163":"-111",
        "tsouhu":"-111"
    },
    jid:1
};

var marketCaptchaPreCheckSuccessFlag = false;

$(function () {
    var formattedData = [];
    $.getJSON($('#recommend').attr('data-resource-uri'), function (data) {
        formattedData = shareDataHandler.formatData(data);
    });


    tool.loading("showIncome");
    $.getJSON($('#showIncome').attr('data-resource-uri'),
        function (data) {
            tool.loadingEnd("showIncome");
            var formattedData = incomeHandle.formattedData(data);
            incomeHandle.showDetail(formattedData);
        });

    $('#recommend li').bind('mouseover', function () {
        changeContent($(this));
    });
    function changeContent(obj) {
        for (var j = 0; j < formattedData.length; j++) {
            if (formattedData[j].shareChannel == obj.attr('data-resource-uri')) {
                jiathis_config.title = formattedData[j].title == '' ? ' ' : formattedData[j].title;
                jiathis_config.url = formattedData[j].mktUrl;
                jiathis_config.summary = formattedData[j].summary;
                jiathis_config.pic = formattedData[j].imageURL;
            }
        }
    }

    var pTitle = "";
    var pSummary = "";
    var mktUrl = "";

    function popUpContent(obj) {
        for (var k = 0; k < formattedData.length; k++) {
            if (formattedData[k].shareChannel == obj.attr('data-resource-uri')) {
                pTitle = formattedData[k].title;
                pSummary = formattedData[k].summary;
                mktUrl = formattedData[k].mktUrl;
            }
        }
    }

    $(".byQQ,.byMSN").on("click", function () {
        popUpContent($(this));
        IMPopUp(pSummary + mktUrl);
    });
    $(".byURL").on("click", function () {
        popUpContent($(this));
        directCopyPopUp(mktUrl);

    });
    $(".byEmail").on('click', function () {
        popUpContent($(this));
        emailPopUp(pTitle, pSummary);
        changeCaptcha();

    });
    $("body").on('click', '.changeValidNum, #validateImg', function () {
        changeCaptcha();
        removeErrors('inputValid');
        $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
    });

    $("body").on('blur', '#inputValid', function (event) {
        checkVerifyCode();
    });

    $("body").on('focus', '#inputValid', function (event) {
        marketCaptchaPreCheckSuccessFlag = false
        removeErrors('inputValid');
        $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
    });

    $(".bySMS").on('click', function () {
        popUpContent($(this));
        $.getJSON("service/recommend/count-sms-recommend-number",
            function (data) {
                var smsNumberData = smsHandle.smsNumber(data);
                var maxSendNumber = smsNumberData.maxSendNumber;
                var sendNumber = smsNumberData.sendNumber;
                if (maxSendNumber <= sendNumber) {
                    smsFailurePopUp(maxSendNumber, sendNumber);
                } else {
                    smsPopUp(pSummary, maxSendNumber, sendNumber);
                    changeCaptcha();
                }
            });


    });


    $("body").on('click', '.sendEmail', function () {
        var status = {
            isAllEmpty : true,
            noErrorTip : true
        }
        var emails = '';
        $('.mail-input').each(function () {
            var strTmp = $.trim($(this).val());
            if (strTmp != '') {
                emails += strTmp + ';';
            }
            if (strTmp.length > 0) {
                status.isAllEmpty = false;
            }
            if ($(this).parents(".control_group").hasClass("error")) {
                status.noErrorTip = false;
            }
        });
        if (status.isAllEmpty) {
            $('#mailTo1').next(".help_line").html('<i class="icon minusCircleIcon"></i>请填写收件人邮件地址。').parents(".control_group").addClass("error");
            return;
        }
        //marketCaptchaPreCheckSuccessFlag = true;
        if (!marketCaptchaPreCheckSuccessFlag) {
            if ($("#inputValid").val() == '') {
                showErrors("请填写验证码。", "inputValid");
                return;
            }
//            else {
//                showErrors("验证码错误。", "inputValid");
//                $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").addClass("failureCircleIcon");
//            }
//            return;
        }

        //send url
        var url = 'recommend/post-email-recommend';
        var paras = {};
        paras['emails'] = emails;
        paras['vcode'] = $("#inputValid").val();

        $.post(url, paras, function(data) {
//               data.result = '00';
                if (data.result == '00') {
                    emailSuccessPopUp();
                } else if (data.result == '03') {//不是邮箱
                    var emails = data.errorMsg.split(';');
                    $('.mail-input').each(function () {
                        var strTmp = $.trim($(this).val());
                        for (var i = 0; i < emails.length; i++) {
                            if (strTmp == emails[i]) {
                                $(this).next(".help_line").html('<i class="icon minusCircleIcon"></i>邮件地址格式错误。').parents(".control_group").addClass("error");
                            }
                        }
                    });
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
                    changeCaptcha();
                } else if (data.result == '04') {//重复
                    var emails = data.errorMsg.split(';');
                    $('.mail-input').each(function () {
                        var strTmp = $.trim($(this).val());
                        for (var i = 0; i < emails.length; i++) {
                            if (strTmp == emails[i]) {
                                $(this).next(".help_line").html('<i class="icon minusCircleIcon"></i>邮箱地址重复。').parents(".control_group").addClass("error");
                            }
                        }
                    });
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
                    changeCaptcha();
                } else if (data.result == '02') {//已经被推荐
                    var emails = data.errorMsg.split(';');
                    $('.mail-input').each(function () {
                        var strTmp = $.trim($(this).val());
                        for (var i = 0; i < emails.length; i++) {
                            if (strTmp == emails[i]) {
                                $(this).next(".help_line").html('<i class="icon minusCircleIcon"></i>该用户已被推荐。').parents(".control_group").addClass("error");
                            }
                        }
                    });
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
                    changeCaptcha();
                } else if (data.result == '06') {//验证码失效
                    marketCaptchaPreCheckSuccessFlag = false;
                    showErrors("验证码错误。", "inputValid");
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").addClass("failureCircleIcon");
                    changeCaptcha();
                }
            }
        );
    });

    $("body").on('click', '.sendSMS', function () {
        var status = {
            isAllEmpty : true,
            noErrorTip : true
        }
        //遍历.sms-input,设置输入框状态
        var phoneNumbers = '';
        $('.sms-input').each(function () {
            var strTmp = $.trim($(this).val());
            if (strTmp != '') {
                phoneNumbers += strTmp + ';';
            }
            if (strTmp.length > 0) {
                status.isAllEmpty = false;
            }
            if ($(this).parents(".control_group").hasClass("error")) {
                status.noErrorTip = false;
            }
        });
        //都为空提示
        if (status.isAllEmpty) {
            $('#smsTo1').next(".help_line").html('<i class="icon minusCircleIcon"></i>请填写手机号码。').parents(".control_group").addClass("error");
            return;
        }
        //marketCaptchaPreCheckSuccessFlag = true;
        if (!marketCaptchaPreCheckSuccessFlag) {
            if ($("#inputValid").val() == '') {
                showErrors("请填写验证码。", "inputValid");
                return;
            }
//            else {
//                showErrors("验证码错误。", "inputValid");
//                $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").addClass("failureCircleIcon");
//            }
//            return;
        }

        //send url
        var url = 'recommend/post-sms-recommend';
        var paras = {};
        paras['phoneNumbers'] = phoneNumbers;
        paras['vcode'] = $("#inputValid").val();

        $.post(url, paras, function(data) {
//                data.result = '06';
                if (data.result == '00') {
                    smsSuccessPopUp();
                } else if (data.result == '05') {//超出条数
                    smsFailurePopUp();
                } else if (data.result == '03') {//不是11位
                    var phones = data.errorMsg.split(';');
                    $('.sms-input').each(function () {
                        var strTmp = $.trim($(this).val());
                        for (var i = 0; i < phones.length; i++) {
                            if (strTmp == phones[i]) {
                                $(this).next(".help_line").html('<i class="icon minusCircleIcon"></i>手机号码是11位数字。').parents(".control_group").addClass("error");
                            }
                        }
                    });
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
                    changeCaptcha();
                } else if (data.result == '04') {//重复
                    var phones = data.errorMsg.split(';');
                    $('.sms-input').each(function () {
                        var strTmp = $.trim($(this).val());
                        for (var i = 0; i < phones.length; i++) {
                            if (strTmp == phones[i]) {
                                $(this).next(".help_line").html('<i class="icon minusCircleIcon"></i>手机号码重复。').parents(".control_group").addClass("error");
                            }
                        }
                    });
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
                    changeCaptcha();
                } else if (data.result == '02') {//已经被推荐
                    var phones = data.errorMsg.split(';');
                    $('.sms-input').each(function () {
                        var strTmp = $.trim($(this).val());
                        for (var i = 0; i < phones.length; i++) {
                            if (strTmp == phones[i]) {
                                $(this).next(".help_line").html('<i class="icon minusCircleIcon"></i>该用户已被推荐。').parents(".control_group").addClass("error");
                            }
                        }
                    });
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
                    changeCaptcha();
                } else if (data.result == '06') {//验证码失效
                    marketCaptchaPreCheckSuccessFlag = false;
                    showErrors("验证码错误。", "inputValid");
                    $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").removeClass("failureCircleIcon");
                    changeCaptcha();
                }
            }
        );
    });

    $("body").on('focusin focusout', '.form_horizontal .input', function() {
        $(".form_horizontal .input").each(function() {
            if ($.trim($(this).val()).length <= 0 || !$(this).hasClass("error")) {
                $(this).next(".help_line").empty().parents(".control_group").removeClass("error");
            }
        });
    });


    $('body').on('blur', '.mail-input', function() {
        var isAllEmpty = true;
        $('.mail-input').each(function () {
            var strTmp = $.trim($(this).val());
            if (strTmp.length > 0) {
                isAllEmpty = false;
            }
        });
        if (isAllEmpty) {
            $('#mailTo1').next(".help_line").html('<i class="icon minusCircleIcon"></i>请填写收件人邮件地址。').parents(".control_group").addClass("error");
        }
    });

    $('body').on('blur', '.sms-input', function() {
        var isAllEmpty = true;
        $('.sms-input').each(function () {
            var strTmp = $.trim($(this).val());
            if (strTmp.length > 0) {
                isAllEmpty = false;
            }
        });
        if (isAllEmpty) {
            $('#smsTo1').next(".help_line").html('<i class="icon minusCircleIcon"></i>请填写手机号码。').parents(".control_group").addClass("error");
        }
    });


});

var shareDataHandler = (function () {
    function formatData(data) {
        var i = 0;
        var rows = [];
        var length = data.list.length;
        for (i; i < length; i++) {
            var row = {
                "mktUrl":data.list[i].mktUrl,
                "title":data.list[i].title,
                "summary":data.list[i].summary,
                "imageURL":data.list[i].imageURL,
                "shareChannel":data.list[i].shareChannel
            };
            rows.push(row);
        }
        return rows;
    }

    return {formatData:formatData}
})();

var incomeHandle = {
    showDetail:function (formattedData) {
        LufaxTemplate.textareaTemplateMergeAndShow("showIncomeTemp", formattedData, function (mergedTemplate) {
            $("#showIncome").html(mergedTemplate);
        });
    },
    formattedData:function (data) {
        var data = data.profit;
        return {
            "currentProfit":LufaxUtility.numberFormatWithoutCurrency(data.currentProfit),
            "currentProfitStatus":data.currentProfitStatus,
            "lastProfit":LufaxUtility.numberFormatWithoutCurrency(data.lastProfit),
            "lastPorfitStatus":data.lastPorfitStatus,
            "totalSettlementProfit":LufaxUtility.numberFormatWithoutCurrency(data.totalSettlementProfit),
            "totalSettlementProfitStatus":data.totalSettlementProfitStatus

        }
    }
}

var smsHandle = {
    smsNumber:function (data) {
        return {
            "maxSendNumber":data.maxSendNumber,
            "sendNumber":data.sendNumber
        }
    }
}

function changeCaptcha() {
    marketCaptchaPreCheckSuccessFlag = false;
    $("#validateImg").attr("src", '');
    $("#validateImg").attr("src", $("#validateImg").attr("data-resource-uri") + "&_=" + new Date().getTime());
    $(".inputValid").val(""); //清空用户输入值
    return false;
}

var checkVerifyCode = function() {

    if ($("#inputValid").val() == '') {
        marketCaptchaPreCheckSuccessFlag = false;
        showErrors("请填写验证码。", "inputValid");
        return;
    }

    var url = 'https://user.lufax.com/user/captcha/pre-check?jsoncallback=?';
    var paras = {};
    paras["inputValue"] = $("#inputValid").val();
    paras["source"] = 'a';

    $.getJSON(url, paras, function(data) {
        if (data.result == 'SUCCESS') {
            marketCaptchaPreCheckSuccessFlag = true;
            $("#inputValid").siblings(".icon").removeClass("failureCircleIcon").addClass("correctCircleIcon");
            removeErrors('inputValid');
        } else {
            showErrors("验证码错误。", "inputValid");
            $("#inputValid").siblings(".icon").removeClass("correctCircleIcon").addClass("failureCircleIcon");
            changeCaptcha();
        }
    });
};

function showErrors(message, id) {
    $("#" + id).parents('.controls').find('.help_line').empty().html('<i class="icon minusCircleIcon"></i>' + message).parents(".control_group").addClass("error");
}

function removeErrors(id) {
    $("#" + id).parents('.controls').find('.help_line').empty().parents(".control_group").removeClass("error");
}