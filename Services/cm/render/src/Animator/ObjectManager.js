import EventManager from './EventManager';
import MenuBuilder from './MenuBuilder';
import NotifManager from "./NotifManager";
import RectangleArea from "./elements/area/RectangleArea";
import MultiArrow from "./elements/arrows/MultiArrow";
import CircleArea from "./elements/area/CircleArea";
import SingleArrow from "./elements/arrows/SingleArrow";
import OtherElement from "./elements/other/OtherElement";
import NumberedPlayers from "./elements/players/NumberedPlayers";
import TextElement from "./elements/text/TextElement";

class ObjectManager{
    "use strict";

    build(){
        // aktualnie edytowane ustawienia obiektu w menu(prawy klik)
        this.actualEditetObjects = [];

        // lista obiektow dostepnych we wszystkich klatkach
        this.objectList = [];

        // lista elementow np klatka [0] - klatka zero
        this.elementPerFrame = [[]];

        this.eventManager = EventManager;

        // aktualnie wyswietlana klatka
        this.currentFrame = 0;

        // maksymalna klatka
        this.maxFrame = 1;

        //field for custom item
        this.arrowOrderCount = 0;

        this.engine = {};

        this.keyPressed = [];

        this.initKeyActions();

        $(document).bind('keydown keypress', function(e){
            if(
                (e.which === 67 && e.ctrlKey) ||
                (e.which === 86 && e.ctrlKey) ||
                (e.which === 65 && e.ctrlKey) ||
                (e.which === 83 && e.ctrlKey) ||
                (e.which === 90 && e.ctrlKey)
            ){
                event.preventDefault();
                return false;
            }
            return true;
        });
    }

    initKeyActions(){

        $(document).off("keyup keydown");
        $(document).on("keyup keydown", (e) => {
            switch(e.type) {
                case "keydown" :
                    let idx = this.keyPressed.indexOf(e.keyCode);
                    if(idx < 0 ){
                        this.keyPressed.push(e.keyCode);
                    }
                    break;
                case "keyup" :
                    for(let i=this.keyPressed.length-1;i>=0;i--){
                        if(this.keyPressed[i] === e.keyCode){
                            this.keyPressed.splice(i,1);
                        }
                    }
                    break;
            }
            this.actualEditetObjects = this.getAllSelected();
            if(this.isKeyPressed(17) && this.isKeyPressed(67)){
                this.copySelected();
            }else if(this.isKeyPressed(17) && this.isKeyPressed(86)){
                this.deselectAll();
                MenuBuilder.pasteSelected();
            }else if(this.isKeyPressed(46)){
                this.deleteInConfiguratorMenu(this.engine);
            }else if(this.isKeyPressed(17) && this.isKeyPressed(65)){
                this.selectAll();
            }else if(this.isKeyPressed(17) && this.isKeyPressed(90)){
                this.engine.historyManager.goBack();
            }else if(this.isKeyPressed(27)){
                this.deselectAll(true);
            }
            return true;
        });

    }

    changeObjectsLayerPos(direction,objects){
        for(let i=0;i<objects.length;i++){
            for(let z=0;z<this.elementPerFrame[this.currentFrame].length;z++){
                if(this.elementPerFrame[this.currentFrame][z].guid === objects[i].guid){
                    if(direction==="down"){
                        if(z > 0){
                            let tmp = $.extend({},this.elementPerFrame[this.currentFrame][z-1]);
                            let tmp2 = $.extend({},this.elementPerFrame[this.currentFrame][z]);
                            delete this.elementPerFrame[this.currentFrame][z-1];
                            delete this.elementPerFrame[this.currentFrame][z];
                            this.elementPerFrame[this.currentFrame][z] = tmp;
                            this.elementPerFrame[this.currentFrame][z-1] = tmp2;
                        }
                    }else{
                        if(z < this.elementPerFrame[this.currentFrame].length-1){
                            let tmp = $.extend({},this.elementPerFrame[this.currentFrame][z+1]);
                            let tmp2 = $.extend({},this.elementPerFrame[this.currentFrame][z]);
                            delete this.elementPerFrame[this.currentFrame][z+1];
                            delete this.elementPerFrame[this.currentFrame][z];
                            this.elementPerFrame[this.currentFrame][z] = tmp;
                            this.elementPerFrame[this.currentFrame][z+1] = tmp2;
                        }
                    }
                    break;
                }
            }
        }

        for(let i=this.elementPerFrame[this.currentFrame].length-1;i>=0;i--){
            for(let z = 0;z<this.objectList.length;z++){
                if(this.objectList[z].guid === this.elementPerFrame[this.currentFrame][i].guid) {
                    this.engine.stage.removeChild(this.objectList[z].container);
                    break;
                }
            }
        }

        this.draw(this.engine);

    }

    dragAllTo(object,offset,objectManager,arrowManager,engine){
        let movedObj = this.getAllSelected();
        for(let i=0;i<movedObj.length;i++){
            if(movedObj[i].guid !== object.guid)
                movedObj[i].dragTo(offset,engine,arrowManager,objectManager);
        }
        this.engine.stage.update();
    }

    isKeyPressed(code) {
        return this.keyPressed.indexOf(code) >= 0;
    }

    init(field,engine){
        this.engine = engine;
        EventManager.init(field,engine,this);
    }

    draw(engine){
        // rysowanie na ekranie wszystkich obiektow z danej klatki
        this.elementPerFrame[this.currentFrame].forEach((elementConfig)=>{
            this.initObjectByConfig(elementConfig,engine);
        });
    }

    load(objectToLoad){
        this.objectList = [];
        for(let i=0;i<objectToLoad.length;i++){
            switch (objectToLoad[i].type){
                case 'SingleArrow':
                    this.objectList.push( new SingleArrow(this.engine,objectToLoad[i].savedConfig,true));
                    break;
                case 'MultiArrow':
                    this.objectList.push( new MultiArrow(this.engine,objectToLoad[i].savedConfig,true));
                    break;
                case 'CircleArea':
                    this.objectList.push( new CircleArea(this.engine,objectToLoad[i].savedConfig,true) );
                    break;
                case 'RectangleArea':
                    this.objectList.push( new RectangleArea(this.engine,objectToLoad[i].savedConfig,true) );
                    break;
                case 'OtherElement':
                    this.objectList.push( new OtherElement(this.engine,objectToLoad[i].savedConfig,true) );
                    break;
                case 'NumberedPlayers':
                    this.objectList.push( new NumberedPlayers(this.engine,objectToLoad[i].savedConfig,true) );
                    break;
                case 'TextElement':
                    this.objectList.push( new TextElement(this.engine,objectToLoad[i].savedConfig,true) );
                    break;
            }
        }

    }

    selectInRect(start,end){
        let rectPos = {
            x: start.x < end.x ? start.x : end.x,
            y: start.y < end.y ? start.y : end.y,
            x1: start.x > end.x ? start.x : end.x,
            y1: start.y > end.y ? start.y : end.y,
        };

        for(let i=0;i<this.elementPerFrame[this.currentFrame].length;i++){
            let objID = this.getObjectWithGuid(this.elementPerFrame[this.currentFrame][i].guid);
            if(objID >= 0 ){
                let objPos = this.objectList[objID].getPosition();
                if(this.checkRectIsInRect(rectPos,objPos)){
                    this.select(this.objectList[objID],true);
                }
            }
        }
    }

    checkRectIsInRect(r1,r2){
        return (r2.x >= r1.x && r2.x1 <= r1.x1 && r2.y >= r1.y && r2.y1 <= r1.y1);
    }

    isRightClick(event){

        let rightclick;
        let e = event.nativeEvent || window.event;
        if (e.which) rightclick = (e.which === 3);
        else if (e.button) rightclick = (e.button === 2);

        if(e.touches && e.touches.length > 1 ){
            rightclick = true;
        }

        return rightclick;
    }

    setFrame(frame){
        this.engine.motionManager.hide();

        this.initObjectInFrame(frame);
        this.currentFrame = frame;

        if(frame+1 > this.maxFrame)
            this.maxFrame = frame+1;
        if(!this.elementPerFrame[this.currentFrame]){
            this.elementPerFrame[this.currentFrame] = [];

            for(let i=0;i<this.elementPerFrame[this.currentFrame-1].length;i++){
                for(let z = 0;z<this.objectList.length;z++){
                    if(this.objectList[z].guid === this.elementPerFrame[this.currentFrame-1][i].guid) {
                        this.eventManager.saveToConfig(this.objectList[z],this);
                        break;
                    }
                }
            }
        }

        for(let z = 0;z<this.objectList.length;z++){
            this.engine.stage.removeChild(this.objectList[z].container);
        }

        for(let i=0;i<this.elementPerFrame[this.currentFrame].length;i++){
            for(let z = 0;z<this.objectList.length;z++){
                if(this.objectList[z].guid === this.elementPerFrame[this.currentFrame][i].guid) {
                    this.engine.stage.addChild(this.objectList[z].container);
                    this.objectList[z].prepareFromConfig(this.elementPerFrame[this.currentFrame][i]);
                    break;
                }
            }
        }

        this.deselectAll();
    }

    deleteFrame(frame,callback = null){
        if(this.elementPerFrame[frame]) {
            for (let z = 0; z < this.elementPerFrame[frame].length; z++) {
                this.deleteObject(this.elementPerFrame[frame][z].guid);
            }
        }
        this.elementPerFrame[frame] = null;
        this.maxFrame = this.maxFrame-1;
        if(callback) callback();
    }

    deselectAll(isKeyed = false){
        this.engine.motionManager.hide();
        if(isKeyed){
            if(this.actualEditetObjects.length > 0 ){
                let msq = '', lnt = this.actualEditetObjects.length;
                if(lnt === 1) msq = "Odznaczono 1 element.";
                else if(lnt >= 2 &&lnt <= 4) msq = `Odznaczono ${lnt} elementy.`;
                else msq = `Odznaczono ${lnt} elementów.`;
                NotifManager.localNotify(msq,'warning',1500);
            }else{
                NotifManager.localNotify("Nie ma nic do odzaczenia.",'warning',1500);
            }
        }

        for(let i=this.elementPerFrame[this.currentFrame].length-1;i>=0;i--){
            for(let z = 0;z<this.objectList.length;z++){
                if(this.objectList[z].guid === this.elementPerFrame[this.currentFrame][i].guid) {
                    this.objectList[z].deselect();
                    break;
                }
            }
        }

        this.actualEditetObjects = [];
        this.hideSettingsMenu();
        this.engine.stage.update();
    }

    showSettingsMenu(isCopyMenu = false,e){
        this.actualEditetObjects = this.getAllSelected();

        if(this.actualEditetObjects.length > 0 || isCopyMenu){
            $('#configuratorMenu #configuratorMenuContent').html(this.buildSettingsHtml(isCopyMenu));
            let positionForMenu = this.positionForMenu(e);
            $('#configuratorMenu').css("top",positionForMenu.y+"px");
            $('#configuratorMenu').css("left",positionForMenu.x+"px");
            setTimeout(()=>{
                $('#configuratorMenu').stop().fadeIn(50);
            },50);
        }
    }

    buildSettingsHtml(isCopyMenu = false){
        let htmlString = '';
        $("#configuratorMenuActionsDynamicElements").html('');

        if(!isCopyMenu){
            let selectedType = this.selectedObjectType();

            if(selectedType === "none") htmlString += `<label class="animatorInMenuInfo">Konfiguracja niemożliwa, zaznaczyłeś elementy z różnych kategorii. Zaznacz elementy z tej samej kategorii lub pojedynczy element.</label>`;
            else htmlString += MenuBuilder.build(this.engine,selectedType,this.actualEditetObjects,this.actualEditetObjects.length === 1);

            $("#configuratorMenuActions").stop().show();
            $("#configuratorMenuActionsDynamicElements").html(MenuBuilder.buildLayerPosition(this.engine,selectedType,this.actualEditetObjects,this));
        }else {
            $("#configuratorMenuActions").stop().hide();
            htmlString = MenuBuilder.buildOnlyCopyPaste(this.engine,this.actualEditetObjects,this);
        }

        return htmlString;
    }

    selectedObjectType(){
        let type = this.actualEditetObjects[0].config.name;
        for(let z = 1;z<this.actualEditetObjects.length;z++){
            if(this.actualEditetObjects[z].config.name !== type){
                type = "none";
                break;
            }
        }
        return type;
    }

    hideSettingsMenu(){
        this.actualEditetObjects = [];
        setTimeout(()=>{
            $('#configuratorMenu').stop().fadeOut(50);
        },50);
    }

    saveInConfiguratorMenu(engine){
        this.hideSettingsMenu();
        engine.resize();
    }

    select(object,fullShift = false){
        let guid = object.guid;

        if(this.existObjectInFrame(guid) >= 0){
            this.hideSettingsMenu();
            this.engine.cancelCategoryItem();
            //czy shift wcisniety
            if(this.isKeyPressed(16) || fullShift){
                let isIn = false;
                for(let z = 0;z<this.actualEditetObjects.length;z++){
                    if(this.actualEditetObjects[z].guid === guid){
                        isIn = true;
                        break;
                    }
                }
                if(!isIn){
                    object.select();
                    this.actualEditetObjects.push(object);
                }
            }else{
                this.deselectAll();
                object.select();
                this.actualEditetObjects = [];
                this.actualEditetObjects.push(object);
            }

        }
    }

    deleteInConfiguratorMenu(engine){
        this.actualEditetObjects = this.getAllSelected();
        let lnt = this.actualEditetObjects.length;

        for(let z = 0;z<this.actualEditetObjects.length;z++){
            this.deleteObject(this.actualEditetObjects[z].guid);
        }

        this.hideSettingsMenu();

        let msq = '';
        if(lnt === 0){
            NotifManager.localNotify("Nie zaznaczyłeś żadnych elementów do usunięcia.",'error',1500);
        }else{
            if(lnt === 1) msq = "Usunąłeś 1 element.";
            else if(lnt >= 2 &&lnt <= 4) msq = `Usunąłeś ${lnt} elementy.`;
            else msq = `Usunąłeś ${lnt} elementów.`;
            NotifManager.localNotify(msq,'warning',1500);
        }
    }

    positionForMenu(e){
        let eventDoc, doc, body, mousePos;
        let event = e.nativeEvent || window.event;
        event = jQuery.event.fix(event);

        let target = event.target || event.srcElement,
            style = target.currentStyle || window.getComputedStyle(target, null),
            borderLeftWidth = parseInt(style['borderLeftWidth'], 10),
            borderTopWidth = parseInt(style['borderTopWidth'], 10),
            rect = target.getBoundingClientRect(),
            offsetX = event.clientX - borderLeftWidth - rect.left,
            offsetY = event.clientY - borderTopWidth - rect.top;

        if(e.nativeEvent && e.nativeEvent.changedTouches && e.nativeEvent.changedTouches.length > 1 ){
            offsetX = e.nativeEvent.changedTouches[0].clientX - borderLeftWidth - rect.left,
            offsetY = e.nativeEvent.changedTouches[0].clientY - borderTopWidth - rect.top;
        }

        mousePos = {
            x:  offsetX,
            y:  offsetY
        };

        if((mousePos.x + $('#configuratorMenu').width()) > $('#animatorCanvasContainer').width() ){
            mousePos.x -= $('#configuratorMenu').width();
        }

        if((mousePos.y + $('#configuratorMenu').height()) > $('#animatorCanvasContainer').height() ){
            if(mousePos.y - $('#configuratorMenu').height() >= 0){
                mousePos.y -= $('#configuratorMenu').height();
            }else mousePos.y = 0;
        }

        if(mousePos.x === 0 || mousePos.y === 0){
            mousePos.x = 20;
            mousePos.y = 20;
        }

        return mousePos;
    }

    getAllSelected(){
        let returnedList = [];

        for(let i=this.elementPerFrame[this.currentFrame].length-1;i>=0;i--){
            for(let z = 0;z<this.objectList.length;z++){
                if(this.objectList[z].guid === this.elementPerFrame[this.currentFrame][i].guid && this.objectList[z].isSelected) {
                    returnedList.push(this.objectList[z]);
                }
            }
        }

        return returnedList;
    }

    selectAll(){
        for(let i=this.elementPerFrame[this.currentFrame].length-1;i>=0;i--){
            for(let z = 0;z<this.objectList.length;z++){
                if(this.objectList[z].guid === this.elementPerFrame[this.currentFrame][i].guid) {
                    this.select(this.objectList[z],true);
                    break;
                }
            }
        }

        let msq ='';
        if(this.objectList.length === 0){
            NotifManager.localNotify("Brak elementów do zaznaczenia.",'error',1500);
        }else{
            if(this.objectList.length === 1) msq = "Zaznaczono 1 element.";
            else if(this.objectList.length >= 2 &&this.objectList.length <= 4) msq = `Zaznaczono ${this.objectList.length} elementy.`;
            else msq = `Zaznaczono ${this.objectList.length} elementów.`;
            NotifManager.localNotify(msq,'warning',1500);
        }
    }

    copySelected(){
        MenuBuilder.copySelected(this.engine,this.actualEditetObjects,this,EventManager);
        $('#configuratorMenu').stop().fadeOut(100);
    }

    newGuid() {
        let S4 = () => (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
    }

    initObjectByConfig( config, engine ){
        let indeks = this.existObject(config.guid);
        if(indeks >= 0) this.objectList[indeks].prepareFromConfig(config);
        else return;
        engine.stage.addChild(this.objectList[indeks].getObject());
    }

    existObjectInFrame(guid){
        if(this.elementPerFrame[this.currentFrame])
            for(let i=0;i<this.elementPerFrame[this.currentFrame].length;i++){
                if(this.elementPerFrame[this.currentFrame][i].guid === guid){
                    return i;
                }
            }
        return -1;
    }

    getObjectWithGuid(guid){
        for(let i=0;i<this.objectList.length;i++){
            if(this.objectList[i].guid === guid) return i;
        }
        return -1;
    }

    existObjectInAllFrame(guid){
        for(let z = 0;z<this.maxFrame;z++){
            for(let i=0;i<this.elementPerFrame[z].length;i++){
                if(this.elementPerFrame[z][i].guid === guid)return i;
            }
        }
        return -1;
    }

    existObjectInFrame(guid){
        if(this.elementPerFrame[this.currentFrame])
        for(let i=0;i<this.elementPerFrame[this.currentFrame].length;i++){
            if(this.elementPerFrame[this.currentFrame][i].guid === guid)return i;
        }
        return -1;
    }

    existObject(guid){
        for(let i=0;i<this.objectList.length;i++){
            if(this.objectList[i].guid === guid) return i;
        }
        return -1;
    }

    initObjectInFrame(frame){
        for(let z = 0;z<this.objectList.length;z++){
            let conf = this.getConfigForObjectInFrame(frame,this.objectList[z]);
            if(conf) this.objectList[z].prepareFromConfig(conf);
        }
    }

    getConfigForObjectInFrame(frame, object){
        if(!this.elementPerFrame || !this.elementPerFrame[frame]) return null;

        for(let i=this.elementPerFrame[frame].length-1;i>=0;i--){
            if(!this.elementPerFrame[frame][i]){
                this.elementPerFrame[frame].splice(i,1);
            }else if(this.elementPerFrame[frame][i].guid === object.guid) {
                return this.elementPerFrame[frame][i];
            }
        }

        return null;
    }

    deleteObject(guid){
        for(let z=this.currentFrame;z<this.maxFrame;z++){
            if(this.elementPerFrame[z])
            for(let i=this.elementPerFrame[z].length-1;i>=0;i--){
                if(this.elementPerFrame[z][i].guid === guid) {
                    this.elementPerFrame[z].splice(i,1);
                    for(let z = 0;z<this.objectList.length;z++){
                        if(this.objectList[z].guid === guid) {
                            if(this.objectList[z].dragLine){
                                this.objectList[z].dragLine.off("mouseout");
                                this.objectList[z].dragLine.off("mouseover");
                            }
                            this.engine.stage.removeChild(this.objectList[z].container);
                            break;
                        }
                    }
                    break;
                }
            }
        }

        this.engine.motionManager.hide();

        let arrowCount = null;

        if(this.existObjectInAllFrame(guid) === -1){
            for(let z = 0;z<this.objectList.length;z++){
                if(this.objectList[z].guid === guid) {

                    if(this.objectList[z].config && this.objectList[z].config.orderText && this.objectList[z].config.orderText.current){
                        arrowCount = this.objectList[z].config.orderText.current;
                    }else arrowCount = null;

                    this.objectList[z].container.removeAllEventListeners();
                    this.objectList[z].container.removeAllChildren();
                    this.objectList[z].container = null;
                    this.objectList[z] = null;
                    this.objectList.splice(z,1);
                    break;
                }
            }
        }

        if(arrowCount){
            this.arrowOrderCount -= 1;
            for(let i=0;i<this.objectList.length;i++){
                this.objectList[i].update(arrowCount, this.engine);
            }
        }

    }

    destroy(){
        $(document).off("keyup keydown");
        $(document).unbind('keydown keypress');
        for(let i=0;i<this.objectList.length;i++){
            if( this.objectList[i].container ){
                this.objectList[i].container.removeAllEventListeners();
                delete this.objectList[i].container;
                this.objectList[i].container = null;
            }
        }
        EventManager.destroy();
        MenuBuilder.destroy();
    }
}


export default new ObjectManager();