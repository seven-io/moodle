M.block_sms77 = {
    init: function (Y, viewPage) {
        var loadUsers = Y.one('#load_user_list');
        var selectRole = Y.one('#id_r_id');
        var selectMsgTpl = Y.one('#id_m_id');
        var selectCourse = Y.one('#id_c_id');
        var userList = Y.one('#table-change');
        var img = Y.one('#load');
        var msgSend = Y.one(2 === viewPage ? '#submit_send_sms' : '#submit_send_voice');

        sms77LoadMessage();

        img.hide();
        msgSend.hide();

        loadUsers.on('click', function () {
            Y.io('user_list.php?msg='
                + sms77GetMessageBody().get('value')
                + '&c_id='
                + selectCourse.get('value')
                + '&r_id='
                + selectRole.get('value'), {
                on: {
                    complete: function (id, e) {
                        sms77SetUserList(e.responseText);

                        msgSend.show();
                    },
                    start: function () {
                        sms77SetUserList(
                            '<img src="loading.gif" style="margin-left: 6cm;" />');
                    }
                }
            });
        });

        if (-1 !== [1, 2].indexOf(Number.parseInt(viewPage))) // send SMS or Voice
            selectMsgTpl.on('change', sms77LoadMessage);

        function sms77LoadMessage() {
            var el = sms77GetMessageBody();

            Y.io('load_message.php?m_id=' + sms77GetMessageTemplateId(), {
                on: {
                    complete: function (id, e) {
                        img.hide();

                        el.show();
                        el.set('value', e.responseText);
                    },
                    start: function () {
                        el.hide();

                        img.show();
                    }
                }
            });
        }

        function sms77SetUserList(innerHTML) {
            userList.set('innerHTML', innerHTML);
        }

        function sms77GetMessageTemplateId() {
            return selectMsgTpl.get('value');
        }

        function sms77GetMessageBody() {
            return Y.one('#id_msg_body');
        }
    }
};
