import NotifManager from './NotifManager';

class HistoryManager{
    "use strict";

    init(engine){
        this.engine = engine;
        this.history = {
            last: []
        };
        this.noAccept = "";
    }

    addEvent(type, value){
        return;
        if(this.noAccept.length > 0 && this.noAccept === type ) {this.noAccept=""; return;}

        let last = this.history.last[this.history.last.length -1];
        if(last && (last.type !== type ||  JSON.pruned(last.value)  !== JSON.pruned(value) )){
            if(type==="config" && last.value.guid ){
                if(last.value.guid !== value.guid){
                    this.history.last.push({type:type,value:value});
                }
            }else {
                this.history.last.push({type:type,value:value});
            }
        }else if(!last){
            this.history.last.push({type:type,value:value});
        }
        if(this.history.last.length > 10 ) {
            this.history.last.splice(10,1);
        }

    }

    recreateChange(){
        let event = this.history.last[ this.history.last.length -1 ];
        this.history.last.splice(this.history.last.length -1,1);

        switch (event.type) {
            case "field":
                this.noAccept = event.type;
                this.engine.changeField(event.value);
                break;
            case "add":
                this.engine.objectManager.deleteObject(event.value);
                break;
        }
    }

    goBack(){
        if(this.history.last.length <= 0) {
            NotifManager.localNotify("Brak zmian do cofnięcia.","error",2000);
            return false;
        }else{
            NotifManager.localNotify("Zmiany zostały cofnięte.","info",2000);
            this.recreateChange();
        }
    }

}

export default new HistoryManager();