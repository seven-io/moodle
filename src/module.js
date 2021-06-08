console.log('sms.PREinit!!!!!');

M.block_sms77 = {
    init: function sms77Init(Y, viewPage) {
        console.log('sms.init');

        var load_users = Y.one('#load_user_list');
        var select_role = Y.one('#id_r_id');
        var select_msg_tpl = Y.one('#id_m_id');
        var select_course = Y.one('#id_c_id');
        var user_list = Y.one('#table-change');
        var img = Y.one('#load');
        var msg_body = sms77GetMessageBody();
        var sms_send = Y.one('#submit_send_sms');
        var msg_send = sms_send ? sms_send : Y.one('#submit_send_voice');

        Y.io('load_message.php?m_id=' + sms77GetMessageTemplateId(), {
            on: {
                complete: function sms77LoadMessageOnComplete(id, e) {
                    img.hide();

                    msg_body.show();
                    msg_body.set('value', e.responseText);
                },
                start: function sms77LoadMessageOnStart() {
                    msg_body.hide();

                    img.show();
                }
            }
        });

        img.hide();

        msg_send.hide();

        load_users.on('click', function onClickShowUsers() {
            var url = 'user_list.php?msg='
                + sms77GetMessageBody().get('value')
                + '&c_id='
                + select_course.get('value')
                + '&r_id='
                + select_role.get('value');

            Y.io(url, {
                on: {
                    complete: function sms77LoadUsersOnClickOnComplete(id, e) {
                        sms77SetUserList(e.responseText);

                        msg_send.show();
                    },
                    start: function sms77LoadUsersOnClickOnStart() {
                        sms77SetUserList(
                            '<img src="loading.gif" style="margin-left: 6cm;" />');
                    }
                }
            });
        });

        if (-1 !== [1, 2].indexOf(Number.parseInt(viewPage))) { // send SMS or Voice
            select_msg_tpl.on('change', function sms77OnSelectMessageTemplate() {
                var content = sms77GetMessageBody();

                Y.io('load_message.php?m_id=' + sms77GetMessageTemplateId(), {
                    on: {
                        complete:
                            function sms77OnSelectMessageTemplateLoadMessageOnComplete
                                (id, e) {
                                img.hide();

                                content.show();
                                content.set('value', e.responseText);
                            },
                        start: function sms77OnSelectMessageTemplateLoadMessageOnStart() {
                            content.hide();

                            img.show();
                        }
                    }
                });
            });
        }

        function sms77SetUserList(innerHTML) {
            user_list.set('innerHTML', innerHTML);
        }

        function sms77GetMessageTemplateId() {
            return select_msg_tpl.get('value');
        }

        function sms77GetMessageBody() {
            return Y.one('#id_sms_body');
        }
    }
};
