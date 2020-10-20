M.block_sms77 = {};

M.block_sms77.init = function (Y, viewPage) {
    var load_users = Y.one('#load_user_list');
    var select_role = Y.one('#id_r_id');
    var select_msg_tpl = Y.one('#id_m_id');
    var select_course = Y.one('#id_c_id');
    var user_list = Y.one('#table-change');
    var img = Y.one('#load');
    var msg_body = getMessageBody();
    var msg_tpl_id = getMessageTemplateId('value');
    var sms_send = Y.one('#submit_send_sms');
    var voice_send = Y.one('#submit_send_voice');
    var msg_send = sms_send ? sms_send : voice_send;

    Y.io('load_message.php?m_id=' + msg_tpl_id, {
        on: {
            complete: function (id, e) {
                img.hide();

                msg_body.show();
                msg_body.set('value', e.responseText);
            },
            start: function () {
                msg_body.hide();

                img.show();
            }
        }
    });

    img.hide();

    msg_send.hide();

    load_users.on('click', function () { // on click "show users"
        var url = 'user_list.php?msg='
            + getMessageBody().get('value')
            + '&c_id='
            + select_course.get('value')
            + '&r_id='
            + select_role.get('value');

        Y.io(url, {
            on: {
                complete: function (id, e) {
                    setUserList(e.responseText);

                    msg_send.show();
                },
                start: function () {
                    setUserList('<img src="loading.gif" style="margin-left: 6cm;" />');
                }
            }
        });
    });

    if (-1 !== [1, 2].indexOf(parseInt(viewPage))) { // send SMS or Voice
        select_msg_tpl.on('change', function () { // select message template
            var content = getMessageBody();

            Y.io('load_message.php?m_id=' + getMessageTemplateId('value'), {
                on: {
                    start: function () {
                        content.hide();

                        img.show();
                    },
                    complete: function (id, e) {
                        img.hide();

                        content.show();
                        content.set('value', e.responseText);
                    }
                }
            });
        });
    }

    function setUserList(innerHTML) {
        user_list.set('innerHTML', innerHTML);
    }

    function getMessageTemplateId() {
        return select_msg_tpl.get('value');
    }

    function getMessageBody() {
        return Y.one('#id_sms_body');
    }
};