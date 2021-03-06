window.addEvent('domready',function(){
   updateCartInfo();
   var cartViewUrl='/ecstore/index.php/cart-view.html';
   var MiniCarWidgets={
        init:function(){
            var minicart=this.cart= $('minicart_1150');
            this.detailbox=$('minicart_cont_1150');
            this.handle=minicart.getElement('.minicart-text');
            this.attach();
        },
        attach:function(){
            this.handle.addEvents({
                'mouseenter':function(e){
                    if(e&&this.state) return;
                    this.show();
                    this.request({
                        url:cartViewUrl,
                        method:'post',
                        onRequest:function(){
                            this.detailbox.innerHTML='<div class="loading">加载中...</div>';
                        }.bind(this)
                    });
                }.bind(this)
            });
            this.cart.addEvent('_show',function(e){
                this.handle.fireEvent('mouseenter');
            }.bind(this));
        },
        show:function(e){
            this.cart.addClass('mini-cart-on');
            this.state=true;
            var self = this;
            $('minicart_1150').addEvent('mouseleave',function(){
                self.hide();
                this.removeEvent('click',arguments.callee);
            });
            return this;
        },
        hide:function(){
            this.cart.removeClass('mini-cart-on');
            this.state=false;
        },
        setHeight: function(el) {
            var h = window.getSize().y - this.detailbox.getPosition(document.body).y + window.getScroll().y - this.detailbox.getPatch().y;
            var dh = this.detailbox.getSize().y - this.detailbox.getPatch('padding', 'border').y;
            if(dh >= h){
                this.detailbox.setStyle('height',h);
            }
            else this.detailbox.setStyle('height', 'auto');
            return this;
        },
        removeCart:function(el){
            this.request({
                url:el.href,
                data:el.getParent('.goods-item')
            });
        },
        addEvent:function(){
            var _this=this;
            this.detailbox.getElements('.action-delete').addEvent('click',function(e){
                e.stop();
                _this.removeCart(this);
            });
        },
        request:function(options){
            options = options || {};
            options.data = (options.data?options.data.toQueryString():'')+'&mini_cart_list=true';
            options = Object.merge({
                method:'post',
                update: this.detailbox,
                onSuccess:function(re){
                    try{
                        updateCartInfo();
                        fixImageSize($$('img[data-img-zoom]'));
                    }catch(e){}
                    this.setHeight().addEvent();
                }.bind(this)
            },options);
            return new Request.HTML(options).send();
        }
    };
    MiniCarWidgets.init();
});