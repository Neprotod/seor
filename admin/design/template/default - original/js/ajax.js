$(document).ready(function(){
            $("#main a").click(function(){
                var $wrap = $("body");
                var href = $(this).attr('href');
                var $iframe = $("<iframe class=\"iframe animated\" src=\""+href+"?ajax\" />");
                
                $wrap.css({'overflow':'hidden'});
                
                var $parent = $(this).parent(".to_frame");
                $iframe.addClass('fadeIn');
                
                $parent.css('visibility','hidden');
                $parent.removeClass('loading animated openIn');
                var $clone = $parent.clone();
                $clone.css('visibility','');

                /*$clone.addClass("open");*/
                $clone.css({
                    'position':'fixed',
                    'width':$parent.width()+"px",
                    'height':$parent.height()+"px",
                    'left':$parent.offset().left+"px",
                    'top':($parent.offset().top-$(window).scrollTop())+"px"
                    });
                $clone.html('');
                $parent.after($clone);
                //÷ентрируем
                setTimeout(function(){
                    $clone.addClass('animated rotate');
                    $clone.css({
                        'transition':"all 0.25s ease",
                        'left':"50%",
                        'margin-left':"-"+($clone.width()/2)+"px",
                        'top':"50%",
                        'margin-top':"-"+($clone.height()/2)+"px",
                        'display':"block",
                        'z-index':"1000000000000"
                    });
                    
                },20);
                setTimeout(function(){
                    $clone.css({
                        'transition':"",
                        'margin-left':"0",
                        'margin-top':"0",
                        'width':"100%",
                        'height':"100%",
                        'left':"0px",
                        'top':"0px",
                        'padding':"0px",
                        'display':"block"
                    });
                    
                },260);
                
                setTimeout(function(){
                    $clone.html($iframe);
                },850);
                
                setTimeout(function(){
                    $parent.css('visibility','');
                },1000);

                $iframe.load(function(){
                    var drop = $('#drop',$(this).contents());
                    drop.click(function(){
                        $clone.addClass("drop");
                        setTimeout(function(){
                            $clone.remove();
                        },300);
                        $wrap.css({'overflow':''});
                    });
                });
                return false;
            });

        });