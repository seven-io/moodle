M.block_seven = {
    init: function (Y, viewPage) {
        var loadUsers = Y.one('#load_user_list');
        var selectRole = Y.one('#id_r_id');
        var selectMsgTpl = Y.one('#id_m_id');
        var selectCourse = Y.one('#id_c_id');
        var userList = Y.one('#table-change');
        var img = Y.one('#load');
        var msgSend = Y.one(2 === viewPage ? '#submit_send_sms' : '#submit_send_voice');

        sevenLoadMessage();

        img.hide();
        msgSend.hide();

        loadUsers.on('click', function () {
            Y.io('user_list.php?msg='
                + sevenGetMessageBody().get('value')
                + '&c_id='
                + selectCourse.get('value')
                + '&r_id='
                + selectRole.get('value'), {
                on: {
                    complete: function (id, e) {
                        sevenSetUserList(e.responseText);

                        msgSend.show();
                    },
                    start: function () {
                        sevenSetUserList(
                            '<img alt=\'\' src=\'loading.gif\' style=\'margin-left: 6cm;\' />');
                    }
                }
            });
        });

        if (-1 !== [1, 2].indexOf(Number.parseInt(viewPage))) // send SMS or Voice
            selectMsgTpl.on('change', sevenLoadMessage);

        function sevenLoadMessage() {
            var el = sevenGetMessageBody();

            Y.io('load_message.php?m_id=' + sevenGetMessageTemplateId(), {
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

        function sevenSetUserList(innerHTML) {
            userList.set('innerHTML', innerHTML);
        }

        function sevenGetMessageTemplateId() {
            return selectMsgTpl.get('value');
        }

        function sevenGetMessageBody() {
            return Y.one('#id_msg_body');
        }
    }
};
