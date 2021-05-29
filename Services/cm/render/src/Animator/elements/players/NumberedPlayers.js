import BaseElement from "../BaseElement";

class NumberedPlayers extends BaseElement{
    "use strict";

    constructor(engine,objectBaseConf,loaded = false){
        super(engine,objectBaseConf,"numberedPlayer",loaded);
    }

    buildNew(callback){
        this.container.set(this.mousePos);
        this.eventBuild();
        if(callback)callback();
    }

    buildFromLast(){
        this.container.set({x:this.baseConf.position[0],y:this.baseConf.position[1]});
        this.eventBuild();
    }

    eventBuild(){
        this.pos={
            x:this.mousePos.x,
            y:this.mousePos.y,
            x1:this.mousePos.x,
            y1:this.mousePos.y,
        };

        if(this.baseConf.color !== 'coach'){
            this.config.color.current = this.baseConf.color;
        }else{
            this.config.color.current = '#000000';
            this.config.textIn.current = "T";
        }

        this.textIn = new zim.Label({
            text: this.config.textIn.current,
            size:35,
            font:"courier",
            color: this.baseConf.gk ? "black" : "white",
            fontOptions:"bold"
        });
        this.basicCircle = new zim.Shape(60,60);
        this.dragRect = new zim.Rectangle(60,60,this.basicColor.notSelected.out);

        this.dragRect.centerReg(this.container);
        this.basicCircle.centerReg(this.container);
        this.textIn.centerReg(this.container);

        this.basicCircle.drag();
        this.dragRect.drag();
        this.textIn.drag();

        this.textIn.off("mouseover");
        this.textIn.on("mouseover",()=>{
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.in;
            }else{
                this.dragRect.color = this.basicColor.notSelected.in;
            }
            this.changed();
            this.engine.stage.update();
        });

        this.textIn.off("mouseout");
        this.textIn.on("mouseout",()=>{
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.out;
            }else{
                this.dragRect.color = this.basicColor.notSelected.out;
            }
            this.changed();
            this.engine.stage.update();
        });

        this.dragRect.off("mouseover");
        this.dragRect.on("mouseover",()=>{
            if(!this || !this.container) return;
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.in;
            }else{
                this.dragRect.color = this.basicColor.notSelected.in;
            }
            this.changed();
            this.engine.stage.update();
        });

        this.basicCircle.off("mouseover");
        this.basicCircle.on("mouseover",()=>{
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.in;
            }else{
                this.dragRect.color = this.basicColor.notSelected.in;
            }
            this.changed();
            this.engine.stage.update();
        });

        this.dragRect.off("mouseout");
        this.dragRect.on("mouseout",()=>{
            if(!this || !this.container) return;
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.out;
            }else{
                this.dragRect.color = this.basicColor.notSelected.out;
            }
            this.changed();
            this.engine.stage.update();
        });

        this.basicCircle.off("mouseout");
        this.basicCircle.on("mouseout",()=>{
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.out;
            }else{
                this.dragRect.color = this.basicColor.notSelected.out;
            }
            this.changed();
            this.engine.stage.update();
        });

        this.textIn.off('mousedown');
        this.textIn.on('mousedown',(e)=>{
            this.lastScreenPos = [e.stageX,e.stageY];
        });

        this.dragRect.off('mousedown');
        this.dragRect.on('mousedown',(e)=>{
            if(!this || !this.container) return;
            this.lastScreenPos = [e.stageX,e.stageY];
        });

        this.basicCircle.off('mousedown');
        this.basicCircle.on('mousedown',(e)=>{
            this.lastScreenPos = [e.stageX,e.stageY];
        });

        this.textIn.off('pressmove');
        this.textIn.on('pressmove',(e)=>{
            this.container.setChildIndex(this.dragRect,0);
            this.container.setChildIndex(this.basicCircle,1);
            this.container.setChildIndex(this.textIn,2);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            let ox = (e.stageX-this.lastScreenPos[0])/ this.engine.stage.scaleX;
            let oy = (e.stageY-this.lastScreenPos[1])/ this.engine.stage.scaleX;

            this.lastScreenPos = [e.stageX,e.stageY];
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }
            this.container.x += ox;
            this.container.y += oy;
            this.lastScreenPos = [e.stageX,e.stageY];
                this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.textIn.pos(0,0);
            this.changed();
            this.engine.stage.update();
        });

        this.textIn.off('pressup');
        this.textIn.on('pressup',(e)=>{
            this.container.setChildIndex(this.dragRect,0);
            this.container.setChildIndex(this.basicCircle,1);
            this.container.setChildIndex(this.textIn,2);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            let ox = (e.stageX-this.lastScreenPos[0])/ this.engine.stage.scaleX;
            let oy = (e.stageY-this.lastScreenPos[1])/ this.engine.stage.scaleX;

            this.lastScreenPos = [e.stageX,e.stageY];
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }
            this.container.x += ox;
            this.container.y += oy;
            this.lastScreenPos = [e.stageX,e.stageY];
            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.dragRect.pos(0,0);
            this.changed(true);
            this.engine.stage.update();
        });

        this.dragRect.off('pressmove');
        this.dragRect.on('pressmove',(e)=>{
            this.container.setChildIndex(this.dragRect,0);
            this.container.setChildIndex(this.basicCircle,1);
            this.container.setChildIndex(this.textIn,2);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            let ox = (e.stageX-this.lastScreenPos[0])/ this.engine.stage.scaleX;
            let oy = (e.stageY-this.lastScreenPos[1])/ this.engine.stage.scaleX;

            this.lastScreenPos = [e.stageX,e.stageY];
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }
            this.container.x += ox;
            this.container.y += oy;
            this.lastScreenPos = [e.stageX,e.stageY];
            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.dragRect.pos(0,0);
            this.changed();
            this.engine.stage.update();
        });

        this.dragRect.off('pressup');
        this.dragRect.on('pressup',(e)=>{
            this.container.setChildIndex(this.dragRect,0);
            this.container.setChildIndex(this.basicCircle,1);
            this.container.setChildIndex(this.textIn,2);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            let ox = (e.stageX-this.lastScreenPos[0])/ this.engine.stage.scaleX;
            let oy = (e.stageY-this.lastScreenPos[1])/ this.engine.stage.scaleX;

            this.lastScreenPos = [e.stageX,e.stageY];
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }
            this.container.x += ox;
            this.container.y += oy;
            this.lastScreenPos = [e.stageX,e.stageY];
            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.dragRect.pos(0,0);
            this.changed(true);
            this.engine.stage.update();
        });

        this.basicCircle.off('pressmove');
        this.basicCircle.on('pressmove',(e)=>{
            this.container.setChildIndex(this.dragRect,0);
            this.container.setChildIndex(this.basicCircle,1);
            this.container.setChildIndex(this.textIn,2);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }

            let ox = (e.stageX-this.lastScreenPos[0])/ this.engine.stage.scaleX;
            let oy = (e.stageY-this.lastScreenPos[1])/ this.engine.stage.scaleX;

            this.lastScreenPos = [e.stageX,e.stageY];
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }
            this.container.x += ox;
            this.container.y += oy;
            this.lastScreenPos = [e.stageX,e.stageY];
            this.objectManager.dragAllTo(this,[-ox,-oy]);

            this.basicCircle.pos(0,0);
            this.changed();
            this.engine.stage.update();
        });

        this.basicCircle.off('pressup');
        this.basicCircle.on('pressup',(e)=>{
            this.container.setChildIndex(this.dragRect,0);
            this.container.setChildIndex(this.basicCircle,1);
            this.container.setChildIndex(this.textIn,2);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }

            let ox = (e.stageX-this.lastScreenPos[0])/ this.engine.stage.scaleX;
            let oy = (e.stageY-this.lastScreenPos[1])/ this.engine.stage.scaleX;

            this.lastScreenPos = [e.stageX,e.stageY];
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }
            this.container.x += ox;
            this.container.y += oy;
            this.lastScreenPos = [e.stageX,e.stageY];
            this.objectManager.dragAllTo(this,[-ox,-oy]);

            this.basicCircle.pos(0,0);
            this.changed(true);
            this.engine.stage.update();
        });

        this.changed();
    }

    getConfig( copy = false, withCtrl = true ){
        return {
            guid: !copy ? this.guid : null,
            position: [this.container.x,this.container.y],
            rotation: this.container.rotation,
            scale: this.container.scaleX,
            gk: this.baseConf.gk,
            color: this.baseConf.color,
            config: JSON.prune(this.config, {inheritedProperties:true,prunedString: undefined}),
            centerPkt: withCtrl ? this.centerPkt : null,
        };
    }

    prepareFromConfig(config){
        this.container.x = config.position[0];
        this.container.y = config.position[1];
        this.container.rotation = config.rotation;
        this.container.scale = config.scale;
        this.baseConf.gk = config.gk;
        this.baseConf.color = config.color;
        this.config = (JSON).parse(config.config);
        this.centerPkt = config.centerPkt;
        this.findPosition();
    }

    findPosition(){
        this.pos={
            x:this.container.x,
            y:this.container.y,
            x1:this.container.x+(25*this.config.scale.current),
                y1:this.container.y+(25*this.config.scale.current),
        };

        this.container.setBounds(-50,-50,100,100);
    }

    updateCache(){
        this.container.scaleX = this.config.scale.current;
        this.container.scaleY = this.config.scale.current;
        this.container.cache();
    }

    select(stage,forceUpdate = false){
        if(!this.isSelected || forceUpdate){
            this.isSelected = true;
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.out;
            }else{
                this.dragRect.color = this.basicColor.notSelected.out;
            }
            this.changed();
            if(stage) stage.update();
        }
    }

    deselect(stage){
        if(this.isSelected){
            this.isSelected = false;
            if(this.isSelected){
                this.dragRect.color = this.basicColor.selected.out;
            }else{
                this.dragRect.color = this.basicColor.notSelected.out;
            }
            this.changed();
            if(stage) stage.update();
        }
    }

    redraw(){
        this.updateShape();
    }

    updateShape() {
        this.textIn.text = this.config.textIn.current;
        this.textIn.centerReg(this.container);
        this.basicCircle.graphics.ss(7,"round").s(this.baseConf.gk ? this.config.color.current : "white")
            .f( this.baseConf.gk ? "white" : this.config.color.current)
            .arc(30, 30, 30, 0, Math.PI*2)
            .ef();
    }
}
export default NumberedPlayers;