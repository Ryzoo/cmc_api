
class NotifManager{
    "use strict";

    init(){
        $(document).off('click touch','.animatorNotif');
        $(document).on('click touch','.animatorNotif',function(){
            let el = $(this);
            el.stop().fadeOut(50,function(){
                el.remove();
            });
        });
    }

    localNotify(message, type='info', time = 3000, callback){
        let addedClass = '';
        switch (type){
            case "warning":
                addedClass = 'animatorNotifWarning';
                break;
            case "error":
                addedClass = 'animatorNotifError';
                break;
            case "success":
                addedClass = 'animatorNotifSuccess';
                break;
            case "info":
            default:
                addedClass = 'animatorNotifInfo';
        }

        $('#animatorNotifierContainer').append(`
            <div class="animatorNotif ${addedClass}" style="display: none">
                <small>
                    ${message}
                </small>
                <div class="animatorNotifEnd">
                    <i class="fas fa-times"></i>
                </div>
            </div>
        `);

        let currentEl = $('.animatorNotif').last().stop().fadeIn(50);

        if(time){
            setTimeout(function(){
                currentEl.stop().fadeOut(50,function(){
                    currentEl.remove();
                    if(callback) callback();
                });
            },time);
        }
    }

    destroy(){
        $(document).off('click touch','.animatorNotif');
    }
}


export default new NotifManager();