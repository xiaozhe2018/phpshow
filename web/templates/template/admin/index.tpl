<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title> <{$title}> </title>
    <link href="../../static/newui/css/base.css" rel="stylesheet" type="text/css" />
    <link href="../../static/newui/css/common.css" rel="stylesheet" type="text/css" />
    <link href="../../static/newui/css/menu.css" rel="stylesheet" type="text/css" />
</head>
<body class="frame-from">
  
<div id="app" class="ui-oa app-load">
        <div class="ui-header clearfix">
            <div class="ui-h-info fr">
                <div class="table-cell">
                    欢迎您，<label class="c-orange"><{$user.user_name}></label>
                    <span> | </span>
                    <a href="?ac=loginout">退出</a>
                </div>
            </div>
        </div><!-- end ui-header -->
        <div class="ui-main" :class="{'mini':hideSub}">
            <div class="ui-left">
                <div class="ui-l-wrap">

                    <div class="ui-l-n">
                        <div class="ui-l-n-l">
                            <ul>
                                <li v-for="nav in navs" :class="{on:activeNav === nav.id}" @click="changeNav(nav.id)">
                                    <i class="m-icon m-icon-{{nav.cls}}" ></i>
                                    {{nav.text}}
                                </li>
                            </ul>
                        </div>
                        <div class="ui-l-n-r">
                            <div class="ui-l-a clearfix">
                                <a class="ui-l-a-u" @click="showAll">全展开</a>
                                <a class="ui-l-a-d" @click="hideAll">全收起</a>
                            </div>
                            <div class="ui-l-sub">
                                <ul>
                                    <li v-for="sub in subs" v-show="activeNav === sub.pid">
                                        <a :class="{on:sub.status}" @click="sub.status = !sub.status">{{sub.text}}</a>
                                        <ul v-show="sub.status">
                                            <li  v-for="slist in subs" v-if="slist.pid === sub.id">
                                                <a @click="changeUrl(slist,true)">{{slist.text}}</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end ui-left -->
            <div class="ui-right">
                <div class="toggle-btn" :class="{'close':hideSub}" @click.self="toggleSub($event)"></div>
                <div class="ui-r-nav">
                    <div class="ui-r-wrap">
                        <div class="ui-wrap">
                            <ul class="ui-tabs clearfix" v-el:tabs rel="0" v-bind:style="{width: tabsW +'px',transform:'translateX('+tabsX+'px)'}">
                                <li :class="{on:tab.status}" v-for="tab in tabs" @click="changeTab(tab)">{{tab.text}} <i v-if="$index !== 0" @click.stop="closeTab($index)"></i></li>
                            </ul>
                        </div>
                        <div class="ui-tab-nav clearfix">
                            <a class="ui-tn-l" @click="moveTabs(0)"></a>
                            <a class="ui-tn-r" @click="moveTabs(1)"></a>
                            <a title="关闭全部" class="ui-tn-close" @click="closeAll"></a>
                        </div>
                    </div>
                </div>
                <div class="ui-r-iframe">
                    <div class="ui-nav">
                        <div class="ui-nav-wrap">
                            <div class="ui-prev" @click.stop="prev">
                                <i></i>
                                <span>后退</span>
                            </div>
                            <div class="ui-next" @click.stop="next">
                                <i></i>
                                <span>前进</span>
                            </div>
                            <div class="ui-reload" @click.stop="reload">
                                <i></i>
                                <span>刷新</span>
                            </div>
                            <div class="ui-full" @click.stop="full">
                                <i></i>
                                <span>全屏</span>
                            </div>
                        </div>
                    </div>
                    <iframe :src="debugBase+tab.url" v-show="tab.status" :rel="tab.id" :class="{'active':tab.status}"frameborder="0" v-for="tab in tabs"></iframe>
                </div>
            </div><!-- end ui-right -->
        </div><!-- end ui-main -->

    </div><!-- end ui-oa -->
</body>

<script src="../../static/newui/js/vue.min.js"></script>
<script src="../../static/newui/js/jquery.min.js"></script>
<script src="../../static/newui/js/jquery.nicescroll.min.js"></script>
<script>
//数据
var MenuData = {
  <{$menu|str_replace('&amp;','&',@me)}>
};

    var app = new Vue({
        el : '#app',
        data: {
            activeNav:'',
            activeUrl:'',
            hideSub: false,//是否隐藏二级菜单
            debugBase:'',
            navs : [],
            subs:[],
            tabs:[],
            defaultTabs:{},
            tabsOffset:141,//tabs中li的长度，为固定值
            tabsW:0,
            tabsX:0
        },
        created:function(){
            for(var item in MenuData){
                if(!this.activeNav) this.activeNav = item;
                if(MenuData[item].Default){
                    this.activeUrl = MenuData[item].url;
                    this.defaultTabs = {id:item,text:MenuData[item].Text,url:MenuData[item].url,status:true};
                    this.tabs.push(this.defaultTabs);
                }
                if(isNaN(MenuData[item].Parent)){
                    this.navs.push({
                        id: item,
                        text : MenuData[item].Text,
                        cls: MenuData[item].Cls
                    });
                }
                else {
                    this.subs.push({
                        pid: MenuData[item].Parent,
                        id : item,
                        text : MenuData[item].Text,
                        url : MenuData[item].url,
                        status: true 
                    })
                }
            }
        },
        compiled:function(){
            //初始化tabs宽度
            this.$el.style.cssText = 'transform: translateY(0);';
            this.changeTabsLength();
            var that = this;
            /*
            setInterval(function(){
                $.get('?ct=index&ac=index&setMsg=1',function(data){
                    if(data){
                        that.message = data;
                    }
                })
            },30000);
            */
            $(".ui-l-n-l").niceScroll({
                cursorcolor: "rgba(255,255,255,.5)",
                cursorborder:"0px",
                cursorborderradius:"3px",
                autohidemode:"scroll",
                background:'rgba(255,255,255,0)'
            });
        },
        methods:{
            /*
            msgClick:function(){
                this.changeUrl({"pid":"19","id":"21","text":"收取信件","url":"?ct=message&ac=message_inbox","status":true});
            },
            */
            toggleSub:function(event){
                this.hideSub = !this.hideSub;
                if(this.hideSub){
                    $(event.target).addClass('close');
                }else{
                    $(event.target).removeClass('close');
                }
            },
            changeNav:function(key){
                this.hideSub = false;
                this.activeNav = key;
            },
            changeUrl:function(item,flag){
                this.activeUrl = item.url;
                var index = this.hideAllTab(item);
                if(index||index===0){
                    this.tabs[index].status = true;
                    this.scrollToptab(index);

                    if(flag) {
                        $('iframe[rel="'+this.tabs[index].id+'"').attr('src',this.tabs[index].url);
                    }
                }else{
                    this.tabsW += this.$els.tabs.children[app.$els.tabs.childElementCount-1].offsetWidth;
                    this.tabs.push({id:item.id,text:item.text,url:item.url,status:true});
                    _this = this;
                    setTimeout(function(){//防止dom没有渲染时获取不到元素
                       _this.scrollToptab(_this.tabs.length-1);
                    },600);

                }
            },
            logout:function(){
                alert('这里写退出逻辑');
            },
            showAll:function(){
                for(var i in this.subs){
                    if(this.subs[i].pid === this.activeNav) this.subs[i].status = true;
                }
            },
            hideAll:function(){
                for(var i in this.subs){
                    if(this.subs[i].pid === this.activeNav) this.subs[i].status = false;
                }
            },
            changeTab:function(item){
                var index = this.hideAllTab(item);
                item.status = true;
                this.scrollToptab(index);
            },
            checkTab:function(item){

            },
            closeTab:function(index){
                //if(index === 0 || this.tabs.length === 1) return;

                var flag = this.tabs[index].status;
                this.tabs.splice(index,1);
                
                this.tabsW -= this.tabsOffset;
                this.scrollToptab(index-1);

                //数组空是把第一个打开
                if(this.tabs.length <=0 ){
                    this.changeUrl(this.defaultTabs);
                }else{
                    if(flag) this.tabs[this.tabs.length-1].status = true;
                }

            },
            closeAll:function(){
                this.tabs = [];
                this.tabsW = 0;
                this.tabsX = 0;
                this.changeUrl(this.defaultTabs);
            },
            hideAllTab:function(item){
                var flag = false
                for(var i in this.tabs){
                    if(item && item.id === this.tabs[i].id) flag = i;
                    this.tabs[i].status = false;
                }

                return flag;
            },
            moveTabs:function(flag){
            var pw = this.$els.tabs.parentElement.offsetWidth,
                pl = this.$els.tabs.parentElement.getBoundingClientRect().left;
                if(flag){//next
                    if( pw + Math.abs(this.tabsX) >= this.tabsW || this.tabW<=pw) return;
                    this.tabsX -= this.tabsOffset;
                }else{//prev
                    if(this.tabsX ===0) return ;
                    if(this.tabsW<=pw){
                    this.tabsX = 0;
               }else{
                  var cl = this.$els.tabs.getBoundingClientRect().left;
                  if(cl<pl) this.tabsX += this.tabsOffset
                  
               }
                }
            },
            changeTabsLength:function(){
                var w = 0;
                var list = this.$els.tabs.children,
                    len = list.length;
                for(var i = 0;i<len;i++){
                    w += list[i].offsetWidth;
                }
                this.tabsW = w;
            },
            scrollToptab:function(index){
                var pw = app.$els.tabs.parentElement.offsetWidth,
                    pl = app.$els.tabs.parentElement.getBoundingClientRect().left;
                if(this.tabsW > pw){
               if(index === this.tabs.length - 1){
                     this.tabsX -= this.tabsW - pw - Math.abs(this.tabsX);
               }else{
                  var cl = app.$els.tabs.children[index].getBoundingClientRect().left;
                  if(cl < pl){//左边遮住
                     this.tabsX += pl - cl;
                  }else if(cl - Math.abs(this.tabsX) >= pw){//右边遮住
                     this.tabsX -= cl - Math.abs(this.tabsX) - pw + this.tabsOffset;
                  }
               }
        
                }else{
               if(this.tabsX !== 0 )
                  this.tabsX = 0;
            }
          },
          prev:function(){
            var iframe = document.querySelector('iframe.active');
            iframe&&iframe.contentWindow.history.back();
          },
          next:function(){
            var iframe = document.querySelector('iframe.active');
            iframe&&iframe.contentWindow.history.forward();
          },
          reload:function(){
            var iframe = document.querySelector('iframe.active');
            iframe&&iframe.contentWindow.location.reload();
          },
          full:function(){
            if(!this.$el.getAttribute('full')){
                this.$el.setAttribute('full',1);
            }else{
                this.$el.removeAttribute('full');
            }
          }
        }
    });
</script>
</html>
