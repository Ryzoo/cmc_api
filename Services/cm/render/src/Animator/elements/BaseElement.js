class BaseElement{
    "use strict";

    // glowny kontener elementu, w nim znajduja sie wszystkie inne lementy obiektu
    container = null;
    isSelected = false;
    guid = null;
    pos={
        x:0,
        y:0,
        x1:0,
        y1:0
    };
    baseConf = null;

    constructor(engine, baseConf, objectName, loaded = false){

        this.engine = engine;
        this.eventManager = this.engine.objectManager.eventManager;
        this.objectManager = this.engine.objectManager;

        this.guid = !baseConf.guid ? this.objectManager.newGuid() : baseConf.guid;
        this.name = objectName;
        this.baseConf = baseConf;

        this.container = new zim.Container();
        this.isSelected = false;
        this.mousePos = this.getMousePosition();

        this.basicColor = {
            selected:{
                in: "rgba(246, 86, 52, 0.90)",
                out: "rgba(246, 86, 52, 0.50)"
            },
            notSelected:{
                in: "rgba(255, 255, 255, 0.2)",
                out: "rgba(255, 255, 255, 0.01)"
            }
        };


        this.engine.historyManager.addEvent("add",  this.guid );

        let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.mousePos.x,y:this.mousePos.y},0,0);

        if(snapToGrid.status){
            this.mousePos.x += snapToGrid.ox;
            this.mousePos.y += snapToGrid.oy;
        }

        this.pos={
            x:0,
            y:0,
            x1:0,
            y1:0,
        };

        this.loadOptionsFromConfig();

        this.container.tickChildren = false;
        this.container.tickEnabled = false;
        this.container.tickOnUpdate  = false;

        this.container.off('mousedown');
        this.container.on('mousedown',(e)=>{
            if(this.engine.actualActionItemName !== "none"){
                this.eventManager.handleClick(e);
            }else{
                this.containerIsClicked = true;
                this.isRight = this.objectManager.isRightClick(e);
            }
        });

        this.container.off('pressmove');
        this.container.on('pressmove',()=>{
            if(this.engine.actualActionItemName === "none"){
                this.containerIsClicked = false;
            }
        });

        this.container.off('pressup');
        this.container.on('pressup',(e)=>{
            if(this.engine.actualActionItemName === "none"){
                if(this.isRight){
                    if(!this.isSelected && this.containerIsClicked){
                        this.objectManager.select(this);
                    }
                    this.containerIsClicked = false;
                    this.objectManager.showSettingsMenu(false,e);
                }else{
                    if(this.containerIsClicked){
                        this.objectManager.select(this);
                        this.containerIsClicked = false;
                    }
                }
            }
        });

        if(this.engine.stage.getChildIndex(this.container) < 0) this.engine.stage.addChild(this.container);
        if(!loaded) this.eventManager.saveToConfig(this,this.objectManager);
    }

    move(position){
        this.container.x += position.x;
        this.container.y += position.y;
    }

    getMousePosition(){
        return this.engine.stage.globalToLocal(this.engine.stage.mouseX,this.engine.stage.mouseY);
    }

    // kalkulacja pozycji obiektu w przestrzeni, ustawienie podstawowych danych
    findPosition(){
        throw new Error('Implement it!');
    }

    update(){

    }

    hexToRgb(hex) {
        let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    // funkcja do odpalenia po kazdej zmianie parametrow, ma za zadanie zapisac wszystkie zmiany i odswierzyc obiekt
    changed(endIs = false){
        if(!this.container){
            return;
        }

        //this.engine.historyManager.addEvent("config",  $.extend(true, [], {conf:this.engine.objectManager.elementPerFrame.slice(), guid:this.guid}) );

        this.redraw();
        this.findPosition();
        this.updateCache();
        this.checkMotionManager();
        if(this.objectManager.existObjectInFrame(this.guid) >= 0 ) {
            this.eventManager.saveToConfig(this,this.objectManager,true);
        }
    }

    checkMotionManager(){
        if(this.isSelected){
            let beforeConf = this.engine.objectManager.getConfigForObjectInFrame(this.engine.objectManager.currentFrame-1,this);
            if(beforeConf){
                this.engine.motionManager.show(
                    {
                        x: this.container.x,
                        y: this.container.y
                    },
                    {
                        x: beforeConf.position[0],
                        y: beforeConf.position[1]
                    },
                    this,
                    beforeConf.centerPkt
                );
            }else{
                this.engine.motionManager.hide();
            }
        }
    }

    // budoawanie nowego elementu
    buildNew(callback){
        throw new Error('Implement it!');
    }

    // build all event used with object
    eventBuild(){
        throw new Error('Implement it!');
    }

    // budoawnie z konfiga po kopiowaniu
    buildFromLast(){
        throw new Error('Implement it!');
    }

    //budowanie indywidualne obiektu
    build(){
        if(this.baseConf.config){
            let configInThisFrame = this.objectManager.getConfigForObjectInFrame(this.objectManager.currentFrame, this);
            if(configInThisFrame){
                this.prepareFromConfig(configInThisFrame);
            }else{
                this.prepareFromConfig(this.baseConf);
            }
            this.buildFromLast();
            return;
        }
        this.buildNew(()=>{
            this.updateCache();
            let cnf = this.getConfig();
            for(let z=this.objectManager.currentFrame+1;z<this.objectManager.maxFrame;z++){
                this.objectManager.elementPerFrame[z].push(cnf);
            }
        });

    }

    // wczytanie konfiguracji obiektu
    loadOptionsFromConfig(){
        for(let i=0;i<this.engine.animatorItemConfig.items.length;i++){
            if(this.engine.animatorItemConfig.items[i].name === this.name){
                this.config = this.engine.animatorItemConfig.items[i];
                break;
            }
        }
        this.build();
    }

    // pobranie pozycji i rozmiarow
    getPosition(){
        return this.pos;
    }

    // aktualizacja wygladu
    updateCache(){
        this.container.cache();
    }

    // przesuniecie obiektu
    dragTo(offset){
        this.container.x -= offset[0];
        this.container.y -= offset[1];
        this.changed();
    }

    // zaznaczenie tego elementu
    select(stage,forceUpdate = false){
        throw new Error('Implement it!');
    }

    // odznaczenie tego elementu
    deselect(stage){
        throw new Error('Implement it!');
    }

    // przerysowanie elementu
    redraw(){
        throw new Error('Implement it!');
    }

    // pobranie kontenera ktory reprezentuje caly obiekt
    getObject(){
        return this.container;
    }

    // aktualizacja opcji obiektu z konfiguracji
    prepareFromConfig(config){
        this.container.x = config.position[0];
        this.container.y = config.position[1];
        this.container.rotation = config.rotation;
        this.container.scale = config.scale;
        this.findPosition();
    }

    // pobranie aktualnej konfiguracji danego obiektu
    getConfig( copy = false, withCtrl = true ){
        return {
            guid: !copy ? this.guid : null,
            position: [this.container.x,this.container.y],
            rotation: this.container.rotation,
            scale: this.container.scaleX
        };
    }

    getCurvePoints(ptsa, tension) {

        tension = typeof tension === 'number' ? tension : 0.5;

        let _pts, res = [],
            x, y,
            t1x, t2x, t1y, t2y,
            c1, c2, c3, c4, st,
            pow3, pow2,
            pow32, pow23,
            p0, p1, p2, p3,
            pl = ptsa.length;

        _pts = ptsa.concat();

        _pts.unshift(ptsa[1]);
        _pts.unshift(ptsa[0]);
        _pts.push(ptsa[pl - 2], ptsa[pl - 1]);

        for (let i = 2; i < pl; i += 2) {

            p0 = _pts[i];
            p1 = _pts[i + 1];
            p2 = _pts[i + 2];
            p3 = _pts[i + 3];

            t1x = (p2 - _pts[i - 2]) * tension;
            t2x = (_pts[i + 4] - p0) * tension;

            t1y = (p3 - _pts[i - 1]) * tension;
            t2y = (_pts[i + 5] - p1) * tension;

            let distance = Math.sqrt(Math.pow(p2-p0,2)+Math.pow(p3-p1,2));
            let numOfSegments = parseInt(distance / 10);

            for(let t = 0; t <= numOfSegments; t++) {
                st = t / numOfSegments;

                pow2 = Math.pow(st, 2);
                pow3 = pow2 * st;
                pow23 = pow2 * 3;
                pow32 = pow3 * 2;

                c1 = pow32 - pow23 + 1;
                c2 = pow23 - pow32;
                c3 = pow3 - 2 * pow2 + st;
                c4 = pow3 - pow2;

                x = c1 * p0 + c2 * p2 + c3 * t1x + c4 * t2x;
                y = c1 * p1 + c2 * p3 + c3 * t1y + c4 * t2y;

                if(res.length < 2 || t!==0)
                    res.push(x, y);
            }
        }
        return res;
    }
}
export default BaseElement;