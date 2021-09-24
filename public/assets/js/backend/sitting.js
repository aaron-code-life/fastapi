define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'sitting/index' + location.search,
                    add_url: 'sitting/add',
                    edit_url: 'sitting/edit',
                    del_url: 'sitting/del',
                    multi_url: 'sitting/multi',
                    import_url: 'sitting/import',
                    table: 'sitting',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                showExport: false,
                showColumns:false,
                showToggle:false,
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        //{checkbox: true},
                        {field: 'user.username', title: __('User.username'), operate: 'LIKE'},
                        {field: 'version', title: __('Version'),searchList:{1: __('豪华'), 2: __('舒适'),3: __('旗舰')}, operate: 'LIKE',formatter:Table.api.formatter.flag},
                        {field: 'seat', title: __('Seat'),searchList: {1: __('左前'), 2: __('右前'),3: __('左后'),4: __('右后')}, operate: 'LIKE',formatter: Table.api.formatter.flag},
                        {field: 'func', title: __('Func'),searchList: {1: __('通风'), 2: __('加热')}, operate: 'LIKE',formatter:Table.api.formatter.flag},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate',visible:false, title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});