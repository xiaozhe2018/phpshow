UE.registerUI('dialog',function(editor,uiName){
    //创建dialog
    var url = window.location.href;
    url = url.replace(/ac=(.*)/,"ac=index&_t="+new Date().getTime());
    var dialog = new UE.ui.Dialog({
        //指定弹出层中页面的路径，这里只能支持页面,因为跟addCustomizeDialog.js相同目录，所以无需加路径
        iframeUrl:url,
        //需要指定当前的编辑器实例
        editor:editor,
        //指定dialog的名字
        name:uiName,
        //dialog的标题
        title:"insert_link",
        //指定dialog的外围样式
        cssRules:"width:880px;height:440px;",
        //如果给出了buttons就代表dialog有确定和取消
        buttons:[
            {
                className:'edui-okbutton',
                label:'confirm',
                onclick:function () {
                    var iframeWindow =  document.getElementById(dialog.id+'_iframe');
                    var iframeDoc = iframeWindow.contentDocument || iframeWindow.contentWindow.document;
                    var checkBox = iframeDoc.getElementsByName('id[]');
                    var text = null;
                    var jiaochengID = '';
                    var linkObj = {
                        'href' : '',
                        'target' :'_blank',
                        'title' : '',
                        '_href': ''
                    };
                    var count = 0;
                    for(var i = 0; i < checkBox.length; i++) {
                        if (checkBox[i].checked) {
                            jiaochengID = checkBox[i].value;
                            if (!isNaN(jiaochengID)) {
                                text = iframeDoc.getElementById(jiaochengID).innerHTML;
                                if (text != null) {
                                    if (count > 0) {
                                        editor.execCommand('insertHtml', '&nbsp;');
                                    }
                                    linkObj.href = text;
                                    linkObj.title = text;
                                    linkObj._href = 'http://www.xiazaiba.com/gonglue/'+jiaochengID+'.html';
                                    editor.execCommand('link', linkObj);
                                    count++;
                                }
                            }
                        }
                   }
                   dialog.close(true);
                }
            },
            {
                className:'edui-cancelbutton',
                label:'cancel',
                onclick:function () {
                    dialog.close(false);
                }
            }
        ]});

    //参考addCustomizeButton.js
    var btn = new UE.ui.Button({
        name:'dialogbutton' + uiName,
        title:'插入文章链接',
        //需要添加的额外样式，指定icon图标，这里默认使用一个重复的icon
        cssRules :'background-position: -500px 0;',
        onclick:function () {
            //渲染dialog
            dialog.render();
            dialog.open();
        }
    });

    return btn;
}/*index 指定添加到工具栏上的那个位置，默认时追加到最后,editorId 指定这个UI是那个编辑器实例上的，默认是页面上所有的编辑器都会添加这个按钮*/);