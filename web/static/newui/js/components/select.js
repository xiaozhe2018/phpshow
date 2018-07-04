var uiSelect = Vue.extend({
	props:{
		name:{
			type:String,
			default:'ui-select'
		},
		data:{
			type:Array,
			required: true
		},
		value: {
	      twoWay: true
	    },
	},
	template : '<div class="select">\
				<select :name="name" v-model="value">\
					<option v-for="item in data" :value="item.value" >{{item.name}}</option>\
				</select>\
				<div class="select-show" @click="status = !status">{{showName}}</div>\
				<ul class="select-options" v-show="status">\
					<li v-for="item in data" :value="item.value" @click="change(item)">{{item.name}}</li>\
				</ul>\
			</div>',
	data:function(){
		return {
			status : false,
			showName:''
		}
	},
	created:function(){
		this.showName = this.value2name(this.value);

	},
	compiled:function(){
		var _this = this;
		document.addEventListener('mouseup',function(){
			if(_this.status) _this.status = false;
		},false);

	},
	methods:{
	 	change:function(item){
			this.value = item.value
			this.status = false;
			this.showName = item.name;
		},
		value2name:function(value){
			var len = this.data.length;
			for(var i=0;i<len;i++){
				if(value === this.data[i].value) return this.data[i].name
			}
			this.value = this.data[0].value;
			return this.data[0].name;
		}
	 },
});

Vue.component('ui-select', uiSelect)