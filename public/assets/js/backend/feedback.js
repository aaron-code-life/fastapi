define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'feedback/index' + location.search,
                    add_url: 'feedback/add',
                    edit_url: 'feedback/edit',
                    del_url: 'feedback/del',
                    multi_url: 'feedback/multi',
                    import_url: 'feedback/import',
                    table: 'feedback',
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
                        {field: 'type', title: __('Type'),searchList: {1: __('下载/加载问题'), 2: __('图片问题'),3: __('BUG反馈')},custom:{1: 'info', 2:'warning',3:'danger'},formatter: Table.api.formatter.label},
                        {field: 'content', title: __('Content'), operate: 'LIKE'},
                        {field: 'contact', title: __('Contact'), operate: 'LIKE'},
                        {field: 'createtime', title: __('提交时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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