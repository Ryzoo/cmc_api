import BaseElement from "../BaseElement";

class CircleArea extends BaseElement{
    "use strict";

    constructor(engine,objectBaseConf,loaded = false){
        super(engine,objectBaseConf,"area",loaded);
    }

    buildNew(callback){
        this.container.set(this.mousePos);
        this.eventBuild();
        let moveListener = this.engine.stage.on("pressmove", (e) => {
            let coordMouse = this.engine.stage.globalToLocal(this.engine.stage.mouseX,this.engine.stage.mouseY);

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:coordMouse.x,y:coordMouse.y},0,0);

            if(snapToGrid.status){
                coordMouse.x += snapToGrid.ox;
                coordMouse.y += snapToGrid.oy;
            }

            this.circles.getChildByName("c2").x = coordMouse.x - this.container.x;
            this.circles.getChildByName("c2").y = coordMouse.y - this.container.y;

            this.changed();
            this.engine.stage.update();
        });

        let upListener = this.engine.stage.on("pressup", () => {
            this.engine.stage.off("pressmove", moveListener);
            this.engine.stage.off("pressup", upListener);
            this.eventManager.saveToConfig(this,this.objectManager);
            this.changed(true);
        });
        if(callback)callback();
    }

    eventBuild(){
        this.pos={
            x:this.mousePos.x,
            y:this.mousePos.y,
            x1:this.mousePos.x,
            y1:this.mousePos.y,
        };

        this.config.type.current = this.baseConf.type;

        this.circle = new zim.Shape().set({x:0,y:0});
        this.circles = new zim.Container();

        let c1 = new zim.Circle(4,"#ebcb35de");
        let c2 = new zim.Circle(10,"#f65634de");
        c1.name = "c1";c1.expand(5);c1.set({x:0,y:0});c1.noDrag();
        c2.name = "c2";c2.expand(5);c2.set(this.mousePos);c2.drag();
        this.circles.addChild(c1);
        this.circles.addChild(c2);

        this.circles.visible = false;

        this.container.addChild(this.circle);
        this.container.addChild(this.circles);

        this.circle.drag();

        this.circles.getChildByName("c2").off("pressmove");
        this.circles.getChildByName("c2").on("pressmove",()=>{
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+this.circles.getChildByName("c2").x,y:this.container.y+this.circles.getChildByName("c2").y},0,0);

            if(snapToGrid.status){
                this.circles.getChildByName("c2").x += snapToGrid.ox;
                this.circles.getChildByName("c2").y += snapToGrid.oy;
            }

            this.changed();
            this.engine.stage.update();
        });

        this.circles.getChildByName("c2").off("pressup");
        this.circles.getChildByName("c2").on("pressup",()=>{
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+this.circles.getChildByName("c2").x,y:this.container.y+this.circles.getChildByName("c2").y},0,0);

            if(snapToGrid.status){
                this.circles.getChildByName("c2").x += snapToGrid.ox;
                this.circles.getChildByName("c2").y += snapToGrid.oy;
            }

            this.changed(true);
            this.engine.stage.update();
        });

        this.circle.off('pressmove');
        this.circle.on('pressmove',(e)=>{
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            this.container.setChildIndex(this.circle,2);
            this.container.setChildIndex(this.circles,1);

            let ox=this.circle.x,
                oy=this.circle.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);

            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-this.circle.x,-this.circle.y]);
            this.circle.pos(0,0);

            this.changed();
            this.engine.stage.update();
        });

        this.circle.off("pressup");
        this.circle.on("pressup",(e)=>{
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            this.container.setChildIndex(this.circle,2);
            this.container.setChildIndex(this.circles,1);

            let ox=this.circle.x,
                oy=this.circle.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);

            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-this.circle.x,-this.circle.y]);
            this.circle.pos(0,0);

            this.changed(true);
            this.engine.stage.update();
        });
    }

    buildFromLast(){
        this.container.set({x:this.baseConf.position[0],y:this.baseConf.position[1]});

        this.eventBuild();

        this.circles.getChildByName("c1").x = this.baseConf.circles[0][0];
        this.circles.getChildByName("c1").y = this.baseConf.circles[0][1];
        this.circles.getChildByName("c2").x = this.baseConf.circles[1][0];
        this.circles.getChildByName("c2").y = this.baseConf.circles[1][1];

        this.changed();
        this.engine.stage.update();
    }

    findPosition(){
        if(this.circles){
            let pos1 = this.circles.getChildByName("c1").pos();
            let pos2 = this.circles.getChildByName("c2").pos();
            let radius = Math.sqrt(Math.pow(pos2.x-pos1.x,2)+Math.pow(pos2.y-pos1.y,2));
            this.pos={
                x:this.container.x-radius,
                y:this.container.y-radius,
                x1:this.container.x+radius,
                y1:this.container.y+radius,
            };
            this.container.setBounds(-radius-20,-radius-20,40+radius*2,40+radius*2);
        }
    }

    select(stage,forceUpdate = false){
        if(!this.isSelected || forceUpdate){
            this.isSelected = true;
            this.circles.visible = true;
            this.changed();
            if(stage) stage.update();
        }
    }

    deselect(stage){
        if(this.isSelected){
            this.isSelected = false;
            this.circles.visible = false;
            this.changed();
            if(stage) stage.update();
        }
    }

    redraw(){
        this.updateShape();
    }

    getConfig( copy = false,withCtrl=true ){
        let circ = [];
        if(this.circles){
            circ = [
                [this.circles.getChildByName("c1").x,this.circles.getChildByName("c1").y],
                [this.circles.getChildByName("c2").x,this.circles.getChildByName("c2").y],
            ];
        }
        return {
            guid: !copy ? this.guid : null,
            position: [this.container.x,this.container.y],
            rotation: this.container.rotation,
            scale: this.container.scaleX,
            type: this.baseConf.type,
            circles: circ,
            config: JSON.prune(this.config, {inheritedProperties:true,prunedString: undefined}),
            centerPkt: withCtrl ? this.centerPkt : null,
        };
    }

    prepareFromConfig(config){
        this.container.x = config.position[0];
        this.container.y = config.position[1];
        this.container.rotation = config.rotation;
        this.container.scale = config.scale;
        this.baseConf.type = config.type;
        this.config = (JSON).parse(config.config);
        this.centerPkt = config.centerPkt;
        this.findPosition();
    }

    updateShape() {
        let pos1 = this.circles.getChildByName("c1").pos();
        let pos2 = this.circles.getChildByName("c2").pos();
        let radius = Math.sqrt(Math.pow(pos2.x-pos1.x,2)+Math.pow(pos2.y-pos1.y,2));

        this.circle.graphics.clear().mt(0,0);

        if(this.config.type.current === "gap"){
            this.circle.graphics.sd([10, 10]);
        }

        let rgb = this.hexToRgb(this.config.color.current);

        this.circle.graphics.ss(2,"round").s(`rgb(${rgb.r},${rgb.g},${rgb.b})`);
        if(this.config.opacity.current > 1) this.circle.graphics.f(`rgba(${rgb.r},${rgb.g},${rgb.b},${(this.config.opacity.current/100)})`);
        this.circle.graphics.arc(pos1.x, pos1.y, radius, 0, Math.PI*2).ef();
    }

}

export default CircleArea;