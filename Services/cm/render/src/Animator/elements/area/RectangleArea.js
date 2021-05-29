import BaseElement from "../BaseElement";

class AreaElement extends BaseElement{
    "use strict";

    constructor(engine,objectBaseConf,loaded = false){
        super(engine,objectBaseConf,"area",loaded);
    }

    buildNew(callback){
        this.container.set(this.mousePos);

        let moveListener = this.engine.stage.on("pressmove", (e) => {
            let coordMouse = this.engine.stage.globalToLocal(this.engine.stage.mouseX, this.engine.stage.mouseY);

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:coordMouse.x,y:coordMouse.y},0,0);

            if(snapToGrid.status){
                coordMouse.x += snapToGrid.ox;
                coordMouse.y += snapToGrid.oy;
            }

            let width = coordMouse.x - this.container.x;
            let height = coordMouse.y - this.container.y;

            this.circles.getChildByName("c2").pos(width,0);
            this.circles.getChildByName("c3").pos(width,height);
            this.circles.getChildByName("c4").pos(0,height);

            this.changed();
            this.engine.stage.update();
        });

        let upListener = this.engine.stage.on("pressup", () => {
            this.engine.stage.off("pressmove", moveListener);
            this.engine.stage.off("pressup", upListener);
            this.changed(true);
        });

        this.eventBuild();
        if(callback)callback();
    }

    getConfig( copy = false,withCtrl=true ){
        let circ = [];
        if(this.circles){
            circ = [
                [this.circles.getChildByName("c1").x,this.circles.getChildByName("c1").y],
                [this.circles.getChildByName("c2").x,this.circles.getChildByName("c2").y],
                [this.circles.getChildByName("c3").x,this.circles.getChildByName("c3").y],
                [this.circles.getChildByName("c4").x,this.circles.getChildByName("c4").y],
            ]
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

    eventBuild(){
        this.config.type.current = this.baseConf.type;

        this.pos={
            x:this.mousePos.x,
            y:this.mousePos.y,
            x1:this.mousePos.x,
            y1:this.mousePos.y,
        };

        this.rectangle = new zim.Shape();
        this.circles = new zim.Container();

        let c1 = new zim.Circle(12,"#f65634de");
        let c2 = new zim.Circle(12,"#f65634de");
        let c3 = new zim.Circle(12,"#f65634de");
        let c4 = new zim.Circle(12,"#f65634de");
        c1.name = "c1";c1.expand(5);
        c2.name = "c2";c2.expand(5);
        c3.name = "c3";c3.expand(5);
        c4.name = "c4";c4.expand(5);

        this.circles.addChild(c1);
        this.circles.addChild(c2);
        this.circles.addChild(c3);
        this.circles.addChild(c4);

        this.circles.visible = false;

        this.circles.getChildByName("c1").pos(0,0);

        this.container.addChild(this.rectangle);
        this.container.addChild(this.circles);

        this.rectangle.drag();
        this.circles.drag();

        this.circles.getChildAt(0).off("pressmove");
        this.circles.getChildAt(0).on("pressmove",()=>{
            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+this.circles.getChildByName("c1").x,y:this.container.y+this.circles.getChildByName("c1").y},0,0);
            if(snapToGrid.status){
                this.circles.getChildByName("c1").x += snapToGrid.ox;
                this.circles.getChildByName("c1").y += snapToGrid.oy;
            }

            this.container.x += this.circles.getChildByName("c1").x;
            this.container.y += this.circles.getChildByName("c1").y;
            this.circles.getChildByName("c2").x -= this.circles.getChildByName("c1").x;
            this.circles.getChildByName("c2").y -= this.circles.getChildByName("c1").y;
            this.circles.getChildByName("c3").x -= this.circles.getChildByName("c1").x;
            this.circles.getChildByName("c3").y -= this.circles.getChildByName("c1").y;
            this.circles.getChildByName("c4").x -= this.circles.getChildByName("c1").x;
            this.circles.getChildByName("c4").y -= this.circles.getChildByName("c1").y;
            this.circles.getChildByName("c1").pos(0,0);
            this.changed();
            this.engine.stage.update();
        });

        let self = this;
        for(let i=1;i<4;i++){
            this.circles.getChildAt(i).off("pressmove");
            this.circles.getChildAt(i).on("pressmove",function(){
                let snapToGrid = self.engine.grid.snapToGrid(self.container,{x:self.container.x+this.x,y:self.container.y+this.y},0,0);
                if(snapToGrid.status){
                    this.x += snapToGrid.ox;
                    this.y += snapToGrid.oy;
                }
                self.changed();
                self.engine.stage.update();
            });
        }

        this.rectangle.off('pressmove');
        this.rectangle.on('pressmove',(e)=>{
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            this.container.setChildIndex(this.rectangle,2);
            this.container.setChildIndex(this.circles,1);
            let ox=this.rectangle.x,
                oy=this.rectangle.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);

            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.rectangle.pos(0,0);
            this.changed();
            this.engine.stage.update();
        });

        this.rectangle.off("pressup");
        this.rectangle.on("pressup",(e)=>{
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            this.container.setChildIndex(this.rectangle,2);
            this.container.setChildIndex(this.circles,1);
            let ox=this.rectangle.x,
                oy=this.rectangle.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);

            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.rectangle.pos(0,0);
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
        this.circles.getChildByName("c3").x = this.baseConf.circles[2][0];
        this.circles.getChildByName("c3").y = this.baseConf.circles[2][1];
        this.circles.getChildByName("c4").x = this.baseConf.circles[3][0];
        this.circles.getChildByName("c4").y = this.baseConf.circles[3][1];

        this.changed();
        this.engine.stage.update();
    }

    findPosition(){
        if(this.circles){
            let min ={
                x:this.circles.getChildAt(0).x,
                y:this.circles.getChildAt(0).y
            };
            let max ={
                x:this.circles.getChildAt(0).x,
                y:this.circles.getChildAt(0).y
            };
            for(let i = 1; i < 4; i++){
                if(this.circles.getChildAt(i).x > max.x) max.x = this.circles.getChildAt(i).x;
                else if(this.circles.getChildAt(i).x < min.x) min.x = this.circles.getChildAt(i).x;

                if(this.circles.getChildAt(i).y > max.y) max.y = this.circles.getChildAt(i).y;
                else if(this.circles.getChildAt(i).y < min.y) min.y = this.circles.getChildAt(i).y;
            }
            this.pos={
                x:min.x + this.container.x,
                y:min.y + this.container.y,
                x1:max.x + this.container.x,
                y1:max.y + this.container.y,
            };

            let bounds = [
                (min.x < 0 ? min.x : -min.x) - 20,
                (min.y < 0 ? min.y : -min.y) - 20,
                Math.abs(min.x-max.x) + 40,
                Math.abs(min.y-max.y) + 40,
            ];

            this.container.setBounds(bounds[0],bounds[1],bounds[2],bounds[3]);
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

    updateShape() {
        let pkt = [
            this.circles.getChildByName("c1").pos(),
            this.circles.getChildByName("c2").pos(),
            this.circles.getChildByName("c3").pos(),
            this.circles.getChildByName("c4").pos(),
        ];

        let rgb = this.hexToRgb(this.config.color.current);

        this.rectangle.graphics.clear().mt(0,0);

        if(this.config.opacity.current <= 1){

            if(this.config.type.current === "gap"){
                this.rectangle.graphics.sd([10, 10]);
            }

            this.rectangle.graphics.ss(2,"round").s(`rgb(${rgb.r},${rgb.g},${rgb.b})`)
                .mt(pkt[0].x,pkt[0].y)
                .lineTo(pkt[1].x,pkt[1].y)
                .lineTo(pkt[2].x,pkt[2].y)
                .lineTo(pkt[3].x,pkt[3].y)
                .lineTo(pkt[0].x,pkt[0].y)

        }else{

            if(this.config.type.current === "gap"){
                this.rectangle.graphics.sd([10, 10]);
            }

            this.rectangle.graphics.ss(2,"round").s(`rgb(${rgb.r},${rgb.g},${rgb.b})`)
                .f(`rgba(${rgb.r},${rgb.g},${rgb.b},${(this.config.opacity.current/100)})`)
                .mt(pkt[0].x,pkt[0].y)
                .lineTo(pkt[1].x,pkt[1].y)
                .lineTo(pkt[2].x,pkt[2].y)
                .lineTo(pkt[3].x,pkt[3].y)
                .lineTo(pkt[0].x,pkt[0].y)
                .ef();
        }

    }

}

export default AreaElement;