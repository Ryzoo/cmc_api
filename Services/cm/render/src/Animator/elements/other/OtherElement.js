import BaseElement from "../BaseElement";

class OtherElement extends BaseElement{
    "use strict";

    constructor(engine,objectBaseConf,loaded = false){
        super(engine,objectBaseConf,"other",loaded);
    }
    buildNew(callback){
        this.currentLoad = false;
        this.container.set(this.mousePos);
        this.eventBuild(()=>{
            if(callback)callback();
        });
    }
    buildFromLast(){
        this.currentLoad = false;
        this.container.set({x:this.baseConf.position[0],y:this.baseConf.position[1]});
        this.eventBuild();
    }
    eventBuild(callback){
        this.basicPath = this.baseConf.pathToImg;
        let path = this.basicPath.replace(".svg","") + this.config.color_img.current + ".svg";
        let loadedImg = document.createElement("img");
        let newGuid = this.objectManager.newGuid();
        this.guidLoad = newGuid;
        loadedImg.addEventListener("load", (event)=> {

            if(newGuid !== this.guidLoad) return;

            this.image = new zim.Bitmap(loadedImg);

            this.image.regX = this.image.width/2;
            this.image.regY = this.image.height/2;
            this.image.x = 0;
            this.image.y = 0;

            this.pos={
                x:this.mousePos.x,
                y:this.mousePos.y,
                x1:this.mousePos.x+this.image.width,
                y1:this.mousePos.y+this.image.height,
            };

            if(!this.textIn){
                this.textIn = new zim.Label({
                    text: this.config.textIn.current,
                    size: 18,
                    font: "courier",
                    align: "center",
                    valign: "middle",
                    color: "white",
                    lineHeight: 18,
                    fontOptions:"bold"
                });
            }

            if(this.dragRect){
                this.container.removeChild(this.dragRect);
                delete this.dragRect;
                this.dragRect = null;
            }

            this.dragRect = new zim.Rectangle(this.image.width,this.image.height,this.basicColor.notSelected.out);
            this.dragRect.regX = this.image.width/2;
            this.dragRect.regY = this.image.height/2;
            this.dragRect.x = 0;
            this.dragRect.y = 0;

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

            this.image.off("mouseover");
            this.image.on("mouseover",()=>{
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
                this.changed(true);
                this.engine.stage.update();
            });

            this.image.off("mouseout");
            this.image.on("mouseout",()=>{
                if(this.isSelected){
                    this.dragRect.color = this.basicColor.selected.out;
                }else{
                    this.dragRect.color = this.basicColor.notSelected.out;
                }
                this.changed();
                this.engine.stage.update();
            });

            this.dragRect.off('mousedown');
            this.dragRect.on('mousedown',(e)=>{
                this.lastScreenPos = [e.stageX,e.stageY];
            });

            this.dragRect.off('pressmove');
            this.dragRect.on('pressmove',(e)=>{
                if(!this || !this.container) return;
                this.container.setChildIndex(this.dragRect,0);
                this.container.setChildIndex(this.image,1);
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
                this.objectManager.dragAllTo(this,[-ox,-oy],this.objectManager,this.eventManager,this.engine);

                this.dragRect.pos(0,0);
                this.changed();
                this.engine.stage.update();
            });

            this.dragRect.off('pressup');
            this.dragRect.on('pressup',(e)=>{
                if(!this || !this.container) return;
                this.container.setChildIndex(this.dragRect,0);
                this.container.setChildIndex(this.image,1);
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
                this.objectManager.dragAllTo(this,[-ox,-oy],this.objectManager,this.eventManager,this.engine);

                this.dragRect.pos(0,0);
                this.changed(true);
                this.engine.stage.update();
            });

            this.image.off('mousedown');
            this.image.on('mousedown',(e)=>{
                this.lastScreenPos = [e.stageX,e.stageY];
            });

            this.image.off('pressmove');
            this.image.on('pressmove',(e)=>{
                this.container.setChildIndex(this.dragRect,0);
                this.container.setChildIndex(this.image,1);
                if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this);}
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
                this.objectManager.dragAllTo(this,[-ox,-oy],this.objectManager,this.eventManager,this.engine);

                this.image.pos(0,0);
                this.changed();
                this.engine.stage.update();
            });

            this.image.off('pressup');
            this.image.on('pressup',(e)=>{
                this.container.setChildIndex(this.dragRect,0);
                this.container.setChildIndex(this.image,1);
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
                this.objectManager.dragAllTo(this,[-ox,-oy],this.objectManager,this.eventManager,this.engine);

                this.image.pos(0,0);
                this.changed(true);
                this.engine.stage.update();
            });

            if(this.container.getChildIndex(this.dragRect) < 0) this.container.addChild(this.dragRect);
            if(this.container.getChildIndex(this.image) < 0) this.image.centerReg(this.container);
            if(this.container.getChildIndex(this.textIn) < 0) {
                this.container.addChild(this.textIn);
            }

            this.dragRect.drag();
            this.image.drag();
            this.image.pos(0,0);

            this.updateText();

            this.changed();
            this.engine.stage.update();
            this.currentLoad = false;
            this.updateText();

            if(callback)callback();
        });
        loadedImg.src = path;
    }

    getConfig( copy = false, withCtrl = true ){
        return {
            guid: !copy ? this.guid : null,
            position: [this.container.x,this.container.y],
            rotation: this.container.rotation,
            scale: this.container.scaleX,
            pathToImg: this.baseConf.pathToImg,
            config: JSON.prune(this.config, {inheritedProperties:true,prunedString: undefined}),
            group: this.baseConf.group ? this.baseConf.group : null,
            centerPkt: withCtrl ? this.centerPkt : null,
        };
    }

    prepareFromConfig(config){
        this.container.x = config.position[0];
        this.container.y = config.position[1];
        this.container.rotation = config.rotation;
        this.container.scale = config.scale;
        this.baseConf.group = config.group;
        this.config = (JSON).parse(config.config);
        this.centerPkt = config.centerPkt;
        if(this.baseConf.pathToImg !== config.pathToImg){
            this.baseConf.pathToImg = config.pathToImg;
            this.updateBitmap();
        }else{
            this.findPosition();
        }
        this.updateText();
    }

    findPosition(){
        this.updateText();
        if(this.image){
            let width = this.image.width > this.textIn.width ? this.image.width : this.textIn.width;
            this.pos={
                x:this.container.x-(width/2)*this.config.scale.current,
                y:this.container.y-(this.image.height/2)*this.config.scale.current,
                x1:this.container.x+(width/2)*this.config.scale.current,
                y1:this.container.y+(this.image.height/2)*this.config.scale.current,
            };
            this.container.setBounds(-width/2 - 30,-this.image.height/2 - 20,width + 60,this.image.height + 90);
        }
    }

    updateText(){
        if(this.textIn && this.image){
            this.textIn.regY = this.textIn.skewY = 0;
            this.textIn.label.x = this.textIn.label.y = 0;
            this.textIn.y = (this.image.height/2)+40;
        }
    }

    changeImg(path){
        if(this.currentLoad) return;
        this.currentLoad = true;
        this.baseConf.pathToImg = path;
        this.updateBitmap();
        this.eventManager.saveToConfig(this,this.objectManager,true);
    }

    updateCache(){
        this.container.scaleX = this.container.scaleY = this.config.scale.current;
        this.container.rotation = this.config.rotation.current;
        this.textIn.text = this.config.textIn.current;
        this.textIn.scaleX =  this.textIn.scaleY = 1/this.config.scale.current;
        this.updateText();
        this.container.cache();
    }
    updateBitmap(){
        this.container.removeChild(this.image);
        delete this.image;
        this.image = null;
        this.eventBuild();
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
    redraw(){
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
}

export default OtherElement;