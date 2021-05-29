import BaseElement from "../BaseElement";

class SingleArrow extends BaseElement{
    "use strict";
    constructor(engine,objectBaseConf,loaded = false){
        super(engine,objectBaseConf,"arrow",loaded);
    }
    buildNew(callback){

        this.container.set(this.mousePos);

        this.config.orderText.current = 0;
        if(!$("#animatorSwitchArrowOrder button").first().hasClass('button-free-p')){
            this.objectManager.arrowOrderCount += 1;
            this.config.orderText.current = this.objectManager.arrowOrderCount;
        }

        this.config.type.current = this.baseConf.arrowType;
        if (this.baseConf.arrowHead) this.config.arrowhead.current = this.baseConf.arrowHead;

        this.eventBuild();

        let moveListener = this.engine.stage.on("pressmove", (e) => {
            let coordMouse = this.engine.stage.globalToLocal(this.engine.stage.mouseX,this.engine.stage.mouseY);

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:coordMouse.x,y:coordMouse.y},0,0);

            if(snapToGrid.status){
                coordMouse.x += snapToGrid.ox;
                coordMouse.y += snapToGrid.oy;
            }

            this.circles.getChildByName("c2").pos(coordMouse.x-this.container.x,coordMouse.y-this.container.y);
            this.changed();
            this.engine.stage.update();
        });

        let upListener = this.engine.stage.on("pressup", () => {
            this.engine.stage.off("pressmove", moveListener);
            this.engine.stage.off("pressup", upListener);
            this.dragLine.off("mouseover");
            this.dragLine.on("mouseover",()=>{
                this.dragLineColor = this.basicColor.notSelected.in;
                this.changed();
                this.engine.stage.update();
            });

            this.dragLine.off("mouseout");
            this.dragLine.on("mouseout",()=>{
                this.dragLineColor = this.basicColor.notSelected.out;
                this.changed(true);
                this.engine.stage.update();
            });
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

        this.arrow = new zim.Shape().pos(0,0);
        this.dragLine = new zim.Shape().pos(0,0);

        this.circles = new zim.Container();
        this.orderContainer = new zim.Container();
        this.orderCircle = new zim.Circle(this.config.orderSize.current,"#ffffff");
        this.orderLabel = new zim.Label({
            text:"10",
            size: this.config.orderSize.current,
            font:"courier",
            color:"black"
        });

        this.orderLabel.center(this.orderCircle);
        this.orderCircle.center(this.orderContainer);
        this.orderContainer.visible = false;

        this.circles.visible = false;
        this.dragLineColor = this.basicColor.notSelected.out;
        this.isSelected = false;

        let c1 = new zim.Circle(12,"#f65634de");
        let c2 = new zim.Circle(12,"#f65634de");
        c1.name = "c1";
        c2.name = "c2";
        c1.expand(5);
        c2.expand(5);

        this.circles.addChild(c1);
        this.circles.addChild(c2);

        this.container.addChild(this.arrow);
        this.container.addChild(this.circles);
        this.container.addChild(this.dragLine);
        this.container.addChild(this.orderContainer);

        this.orderContainer.noDrag();
        this.dragLine.drag();
        this.circles.drag();
        this.arrow.drag();

        this.circles.off("pressmove");
        this.circles.on("pressmove",(e)=>{
            if( this.circles.getChildByName("c1").x !== 0 || this.circles.getChildByName("c1").y !== 0){

                let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x + this.circles.getChildByName("c1").x,y:this.container.y + this.circles.getChildByName("c1").y},0,0);
                if(snapToGrid.status){
                    this.circles.getChildByName("c1").x += snapToGrid.ox;
                    this.circles.getChildByName("c1").y += snapToGrid.oy;
                }

                this.container.x += this.circles.getChildByName("c1").x;
                this.container.y += this.circles.getChildByName("c1").y;
                this.circles.getChildByName("c2").x -= this.circles.getChildByName("c1").x;
                this.circles.getChildByName("c2").y -= this.circles.getChildByName("c1").y;
                this.circles.getChildByName("c1").pos(0,0);
            }else{
                let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x + this.circles.getChildByName("c2").x,y:this.container.y + this.circles.getChildByName("c2").y},0,0);
                if(snapToGrid.status){
                    this.circles.getChildByName("c2").x += snapToGrid.ox;
                    this.circles.getChildByName("c2").y += snapToGrid.oy;
                }
            }
            this.changed();
            this.engine.stage.update();
        });

        this.circles.off("pressup");
        this.circles.on("pressup",()=>{
            if( this.circles.getChildByName("c1").x !== 0 || this.circles.getChildByName("c1").y !== 0){

                let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x + this.circles.getChildByName("c1").x,y:this.container.y + this.circles.getChildByName("c1").y},0,0);
                if(snapToGrid.status){
                    this.circles.getChildByName("c1").x += snapToGrid.ox;
                    this.circles.getChildByName("c1").y += snapToGrid.oy;
                }

                this.container.x += this.circles.getChildByName("c1").x;
                this.container.y += this.circles.getChildByName("c1").y;
                this.circles.getChildByName("c2").x -= this.circles.getChildByName("c1").x;
                this.circles.getChildByName("c2").y -= this.circles.getChildByName("c1").y;
                this.circles.getChildByName("c1").pos(0,0);
            }else{
                let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x + this.circles.getChildByName("c2").x,y:this.container.y + this.circles.getChildByName("c2").y},0,0);
                if(snapToGrid.status){
                    this.circles.getChildByName("c2").x += snapToGrid.ox;
                    this.circles.getChildByName("c2").y += snapToGrid.oy;
                }
            }
            this.changed(true);
            this.engine.stage.update();
        });

        this.arrow.off('pressmove');
        this.arrow.on('pressmove',(e)=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }

            let ox = this.arrow.x;
            let oy = this.arrow.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.arrow.pos(0,0);
            this.changed();
        });

        this.arrow.off("pressup");
        this.arrow.on("pressup",()=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }

            let ox = this.arrow.x;
            let oy = this.arrow.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.arrow.pos(0,0);
            this.changed(true);
        });

        this.dragLine.off('pressmove');
        this.dragLine.on('pressmove',(e)=>{
            if(!this || !this.container) return;
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }

            let ox = this.dragLine.x;
            let oy = this.dragLine.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.dragLine.pos(0,0);
            this.changed();
        });

        this.dragLine.off("pressup");
        this.dragLine.on("pressup",(e)=>{
            if(!this || !this.container) return;
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }

            let ox = this.dragLine.x;
            let oy = this.dragLine.y;

            let snapToGrid = this.engine.grid.snapToGrid(this.container,{x:this.container.x+ox,y:this.container.y+oy},ox,oy);
            if(snapToGrid.status){
                ox = snapToGrid.ox;
                oy = snapToGrid.oy;
            }

            this.container.x += ox;
            this.container.y += oy;

            this.objectManager.dragAllTo(this,[-ox,-oy]);
            this.dragLine.pos(0,0);
            this.changed(true);
        });
    }
    buildFromLast(){

        this.container.set({x:this.baseConf.position[0],y:this.baseConf.position[1]});

        this.eventBuild();

        this.circles.getChildByName("c1").x = this.baseConf.circles[0][0];
        this.circles.getChildByName("c1").y = this.baseConf.circles[0][1];
        this.circles.getChildByName("c2").x = this.baseConf.circles[1][0];
        this.circles.getChildByName("c2").y = this.baseConf.circles[1][1];

        this.dragLine.off("mouseover");
        this.dragLine.on("mouseover",()=>{
            this.dragLineColor = this.basicColor.notSelected.in;
            this.changed();
        });

        this.dragLine.off("mouseout");
        this.dragLine.on("mouseout",()=>{
            this.dragLineColor = this.basicColor.notSelected.out;
            this.changed(true);
        });

        this.changed();
        this.engine.stage.update();
    }
    update(arrowCount,engine){
        if( arrowCount !== "" && this.config.orderText.current > arrowCount){
            this.config.orderText.current -= 1;
            this.changed(true);
            engine.stage.update();
        }
    }

    getConfig( copy = false,withCtrl=true ){
        let circ = [];
        if(this.circles){
            circ = [
                [this.circles.getChildByName("c1").x,this.circles.getChildByName("c1").y],
                [this.circles.getChildByName("c2").x,this.circles.getChildByName("c2").y],
            ]
        }
        return {
            guid: !copy ? this.guid : null,
            position: [this.container.x,this.container.y],
            rotation: this.container.rotation,
            scale: this.container.scaleX,
            arrowType: this.baseConf.arrowType,
            arrowHead: this.baseConf.arrowHead,
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
        this.baseConf.arrowType = config.arrowType;
        this.baseConf.arrowHead = config.arrowHead;
        this.config = (JSON).parse(config.config);
        this.centerPkt = config.centerPkt;
        this.findPosition();
    }

    findPosition(){
        if(this.circles){
            let min = {
                x: this.circles.getChildByName("c2").x < 0 ? this.circles.getChildByName("c2").x : 0,
                y: this.circles.getChildByName("c2").y < 0 ? this.circles.getChildByName("c2").y : 0
            };

            let max = {
                x: this.circles.getChildByName("c2").x > 0 ? this.circles.getChildByName("c2").x : 0,
                y: this.circles.getChildByName("c2").y > 0 ? this.circles.getChildByName("c2").y : 0
            };

            this.pos={
                x: min.x +this.container.x,
                y: min.y +this.container.y,
                x1: max.x +this.container.x,
                y1: max.y +this.container.y,
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

        this.orderCircle.radius = this.config.orderSize.current;
        this.orderLabel.size = parseInt(this.config.orderSize.current);

        let pts = [];

        let dblPoint={
            x: (this.circles.getChildByName("c2").x),
            y: (this.circles.getChildByName("c2").y)
        };

        pts.push(0);pts.push(0);
        pts.push(dblPoint.x);pts.push(dblPoint.y);
        pts = this.getCurvePoints(pts, 1);

        let color = this.config.color.current;
        this.arrow.graphics.clear().ss(this.config.size.current).s(color);
        this.arrow.graphics.moveTo(pts[0], pts[1]);
        let poz = 1.570796;
        let historyP= {};

        // pierwszy grot strzalki
        if(this.config.arrowhead.current === "two"){
            let currentPkt = {
                x: pts[0],
                y: pts[1]
            };
            let nextPkt = {
                x: pts[2],
                y: pts[3]
            };

            let angle = Math.atan2((currentPkt.y - nextPkt.y) , (currentPkt.x - nextPkt.x));
            angle = (angle / (Math.PI / 180));
            this.arrow.graphics.f(this.config.color.current);
            this.arrow.graphics.ss(this.config.size.current).dp(currentPkt.x, currentPkt.y, (this.config.size.current+2), 3, 0.5,angle);
            this.arrow.graphics.f(null);
        }

        this.dragLine.graphics.clear().ss(20,"round").s(this.dragLineColor).mt(0,0);

        for(let i = 0, j=0; i < pts.length; j++,i += 2){

            this.dragLine.graphics.lt(pts[i],pts[i+1]);

            let currentPkt = {
                x: pts[i],
                y: pts[i+1]
            };
            let nextPkt = {
                x: pts[i+2],
                y: pts[i+3]
            };
            let symetricPkt = {
                x: (currentPkt.x + nextPkt.x)/2,
                y: (currentPkt.y + nextPkt.y)/2
            };
            let angle = Math.atan2((currentPkt.y - nextPkt.y) , (currentPkt.x - nextPkt.x));
            let angleEnd = angle+poz;
            let newPkt = {};

            switch(this.config.type.current){
                case "gap":

                    if (j%2) this.arrow.graphics.lineTo(pts[i], pts[i+1]);
                    else this.arrow.graphics.moveTo(pts[i], pts[i+1]);

                    break;
                case "double":

                    angleEnd = angle+1.570796;

                    newPkt = {
                        x: nextPkt.x + parseFloat(3 * (Math.cos(angleEnd))),
                        y: nextPkt.y + parseFloat(3 * (Math.sin(angleEnd))),
                    };

                    angleEnd = angle+(1.570796*-1);

                    let newPkt2 = {
                        x: nextPkt.x + parseFloat(3 * (Math.cos(angleEnd))),
                        y: nextPkt.y + parseFloat(3 * (Math.sin(angleEnd))),
                    };

                    if(j===0)
                        this.arrow.graphics.lt(newPkt.x,newPkt.y).mt(currentPkt.x,currentPkt.y).lt(newPkt2.x,newPkt2.y).mt(newPkt.x,newPkt.y);
                    else if((i+2) < (pts.length - 3))
                        this.arrow.graphics.lt(newPkt.x,newPkt.y).mt(historyP.x,historyP.y).lt(newPkt2.x,newPkt2.y).mt(newPkt.x,newPkt.y);
                    else
                        this.arrow.graphics.lt(nextPkt.x,nextPkt.y).mt(historyP.x,historyP.y).lt(nextPkt.x,nextPkt.y).mt(nextPkt.x,nextPkt.y);

                    historyP = {
                        x:newPkt2.x,
                        y:newPkt2.y
                    };

                    break;
                case "straight":

                    this.arrow.graphics.lineTo(pts[i], pts[i+1]);

                    break;
                case "curved":

                    newPkt = {
                        x: symetricPkt.x + parseFloat(5 * (Math.cos(angleEnd))),
                        y: symetricPkt.y + parseFloat(5 * (Math.sin(angleEnd))),
                    };

                    this.arrow.graphics.quadraticCurveTo(newPkt.x,newPkt.y,nextPkt.x,nextPkt.y);

                    break;
            }
            poz *= -1;

        }

        // drugi grot
        if(this.config.arrowhead.current === "one" || this.config.arrowhead.current === "two"){
            let currentPkt = {
                x: pts[pts.length-4],
                y: pts[pts.length-3]
            };
            let nextPkt = {
                x: pts[pts.length-2],
                y: pts[pts.length-1]
            };

            let angle = Math.atan2((currentPkt.y - nextPkt.y) , (currentPkt.x - nextPkt.x));
            angle = (angle / (Math.PI / 180)) - 180;
            this.arrow.graphics.f(this.config.color.current);
            this.arrow.graphics.ss(this.config.size.current,"round").dp(nextPkt.x, nextPkt.y, (this.config.size.current+2), 3, 0.5,angle);
        }

        let centerPkt = (Math.round((pts.length / 2)/2)*2)-2;
        let newPkt = {
            x:pts[centerPkt],
            y:pts[centerPkt+1]
        };

        if(!newPkt.x || !newPkt.y){
            centerPkt+=2;
            newPkt = {
                x:pts[centerPkt],
                y:pts[centerPkt+1]
            };
        }

        if(newPkt.x || newPkt.y){
            this.orderContainer.x = newPkt.x;
            this.orderContainer.y = newPkt.y;
            this.orderLabel.text = this.config.orderText.current;
            this.orderLabel.center(this.orderCircle);
            this.orderContainer.visible = !(this.config.orderText.current === 0 || this.config.orderText.current === "");
        }
     }
}

export default SingleArrow;