import BaseElement from "../BaseElement";

class TextElement extends BaseElement{
    "use strict";

    constructor(engine,objectBaseConf,loaded = false){
        super(engine,objectBaseConf,"text",loaded);
    }

    buildNew(callback){
        this.container.set(this.mousePos);
        this.config.type.current = this.baseConf.fontType;
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

        this.recShape = new zim.Rectangle(this.config.sizeWidth.current,60, !this.baseConf.isBackground ? this.basicColor.notSelected.out : this.config.fontColor.current);

        this.textIn = new zim.Label({
            text: this.config.textIn.current,
            size: this.config.size.current,
            font: this.config.type.current,
            align: "center",
            valign: "middle",
            lineWidth: this.config.sizeWidth.current,
            backing: this.recShape,
            lineHeight: 30,
            color: "#ffffff",
        });

        this.textIn.off("mouseover");
        this.textIn.on("mouseover",()=>{
            this.recShape.color = this.basicColor.notSelected.in;
            this.changed();
            this.engine.stage.update();
        });

        this.textIn.off("mouseout");
        this.textIn.on("mouseout",()=>{
            this.recShape.color = !this.baseConf.isBackground ? this.basicColor.notSelected.out : this.config.fontColor.current;
            this.changed(true);
            this.engine.stage.update();
        });

        this.recShape.off("mouseover");
        this.recShape.on("mouseover",()=>{
            this.recShape.color = this.basicColor.notSelected.in;
            this.changed();
            this.engine.stage.update();
        });

        this.recShape.off("mouseout");
        this.recShape.on("mouseout",()=>{
            this.recShape.color = !this.baseConf.isBackground ? this.basicColor.notSelected.out : this.config.fontColor.current;
            this.changed(true);
            this.engine.stage.update();
        });

        this.textIn.off('mousedown');
        this.textIn.on('mousedown',(e)=>{
            this.lastScreenPos = [e.stageX,e.stageY];
        });
        this.recShape.off('mousedown');
        this.recShape.on('mousedown',(e)=>{
            this.lastScreenPos = [e.stageX,e.stageY];
        });

        this.textIn.off('pressmove');
        this.textIn.on('pressmove',(e)=>{
            this.container.setChildIndex(this.recShape,0);
            this.container.setChildIndex(this.textIn,1);
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
            this.container.setChildIndex(this.recShape,0);
            this.container.setChildIndex(this.textIn,1);
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
            this.changed(true);
            this.engine.stage.update();
        });

        this.recShape.off('pressmove');
        this.recShape.on('pressmove',(e)=>{
            if(!this || !this.container) return;
            this.container.setChildIndex(this.recShape,0);
            this.container.setChildIndex(this.textIn,1);
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

        this.recShape.off('pressup');
        this.recShape.on('pressup',(e)=>{
            if(!this || !this.container) return;
            this.container.setChildIndex(this.recShape,0);
            this.container.setChildIndex(this.textIn,1);
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
            this.recShape.pos(0,0);
            this.changed(true);
            this.engine.stage.update();
        });

        this.recShape.drag();
        this.textIn.drag();

        this.recShape.centerReg(this.container);
        this.textIn.centerReg(this.container);
        this.changed();
    }

    getConfig( copy = false, withCtrl = true ){
        return {
            guid: !copy ? this.guid : null,
            position: [this.container.x,this.container.y],
            rotation: this.container.rotation,
            scale: this.container.scaleX,
            isBackground: this.baseConf.isBackground,
            config: JSON.prune(this.config, {inheritedProperties:true,prunedString: undefined}),
            centerPkt: withCtrl ? this.centerPkt : null,
        };
    }

    prepareFromConfig(config){
        this.container.x = config.position[0];
        this.container.y = config.position[1];
        this.container.rotation = config.rotation;
        this.container.scale = config.scale;
        this.baseConf.isBackground = config.isBackground;
        this.config = (JSON).parse(config.config);
        this.centerPkt = config.centerPkt;
        this.findPosition();
    }

    findPosition(){
        this.pos={
            x:this.container.x-(this.config.sizeWidth ? this.config.sizeWidth.current/2+20 : 0),
            y:this.container.y-(this.textIn ? ((this.textIn.label.getMeasuredHeight())/2)+20 : 0),
            x1:this.container.x + (this.config.sizeWidth ? this.config.sizeWidth.current+40 : 0) -(this.config.sizeWidth ? this.config.sizeWidth.current/2+20 : 0),
            y1:this.container.y + (this.textIn ? (this.textIn.label.getMeasuredHeight())+40 : 0)-(this.textIn ? ((this.textIn.label.getMeasuredHeight())/2)+20 : 0),
        };

        let txtB = {
            x:  -(this.config.sizeWidth ? this.config.sizeWidth.current/2+20 : 0),
            y: -(this.textIn ? ((this.textIn.label.getMeasuredHeight())/2)+20 : 0),
            width: this.config.sizeWidth ? this.config.sizeWidth.current+40 : 0,
            height: this.textIn ? (this.textIn.label.getMeasuredHeight())+40 : 0
        };
        this.container.setBounds(txtB.x,txtB.y,txtB.width,txtB.height);
    }

    updateCache(){
        this.container.rotation = this.config.rotation.current;
        this.container.cache();
    }

    select(stage,forceUpdate = false){
        if(!this.isSelected || forceUpdate){
            this.isSelected = true;
            this.recShape.color = !this.baseConf.isBackground ? this.basicColor.notSelected.out : this.config.fontColor.current;
            this.changed();
            if(stage) stage.update();
        }
    }

    deselect(stage){
        if(this.isSelected){
            this.isSelected = false;
            this.recShape.color = !this.baseConf.isBackground ? this.basicColor.notSelected.out : this.config.fontColor.current;
            this.changed();
            if(stage) stage.update();
        }
    }

    redraw(){
        this.updateShape();
    }

    updateShape() {
        this.recShape.widthOnly = this.config.sizeWidth.current+40;
        this.textIn.label.lineWidth = this.config.sizeWidth.current-40;
        this.textIn.label.font = this.config.size.current + "px " + this.config.type.current;
        this.textIn.label.lineHeight = this.config.size.current;
        this.textIn.text = this.config.textIn.current;
        this.textIn.color = !this.baseConf.isBackground ? this.config.fontColor.current : (this.config.fontColor.current === "#ffffff" ? "#000000" : "#ffffff");
        this.recShape.heightOnly = this.textIn.label.getMeasuredHeight()+40;
        this.textIn.setBounds(0,0,this.config.sizeWidth.current,this.textIn.label.getMeasuredHeight());
        this.textIn.label.setBounds(0,0,this.config.sizeWidth.current,this.textIn.label.getMeasuredHeight());
        this.textIn.label.y = this.config.size.current-5;
        this.textIn.label.x = this.config.sizeWidth.current/2;
        this.recShape.centerReg(this.container);
        this.textIn.centerReg(this.container);
    }
}
export default TextElement;