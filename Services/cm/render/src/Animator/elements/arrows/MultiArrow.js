import BaseElement from "../BaseElement";

class MultiArrow extends BaseElement{
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

        this.circles.addChild(new zim.Circle(4,"#ffffff"));

        (this.circles.getChildAt(0)).x = this.mousePos.x - this.container.x;
        (this.circles.getChildAt(0)).y = this.mousePos.y - this.container.y;

        let moveListener = this.engine.stage.on("pressmove", (e) => {
            let coordMouse = this.engine.stage.globalToLocal(this.engine.stage.mouseX, this.engine.stage.mouseY);

            coordMouse.x -= this.container.x;
            coordMouse.y -= this.container.y;

            let w = coordMouse.x - this.circles.getChildAt(this.circlesCount-1).x;
            let h = coordMouse.y - this.circles.getChildAt(this.circlesCount-1).y;
            let l = Math.sqrt(w*w+h*h);
            if( l >= 20){
                this.circlesCount++;
                this.circles.addChild(new zim.Circle(5,"#ffffff"));
                this.circles.getChildAt(this.circlesCount-1).x = coordMouse.x;
                this.circles.getChildAt(this.circlesCount-1).y = coordMouse.y;
                this.engine.stage.update();
            }
        });
        let upListener = this.engine.stage.on("pressup", () => {
            this.engine.stage.off("pressmove", moveListener);
            this.engine.stage.off("pressup", upListener);
            this.circles.visible = false;
            if( this.circlesCount > 2 )
                for(let i = this.circlesCount -2; i > 0; i-=2){
                    this.circles.removeChildAt(i);
                    this.circlesCount--;
                }
            for(let i = 0; i < this.circlesCount; i++){
                this.circles.getChildAt(i).radius = 10;
                this.circles.getChildAt(i).color = "#f65634de";
                this.circlesList.push(this.circles.getChildAt(i));
            }
            this.dragLine.off("mouseover");
            this.dragLine.on("mouseover",()=>{
                if(!this || !this.container) return;
                this.container.setChildIndex(this.dragLine,1);
                this.container.setChildIndex(this.orderContainer,2);
                this.container.setChildIndex(this.circles,3);
                this.container.setChildIndex(this.arrow,0);
                this.dragLineColor = this.basicColor.notSelected.in;
                this.changed();
                this.engine.stage.update();
            });
            this.dragLine.off("mouseout");
            this.dragLine.on("mouseout",()=>{
                if(!this || !this.container) return;
                this.container.setChildIndex(this.dragLine,1);
                this.container.setChildIndex(this.orderContainer,2);
                this.container.setChildIndex(this.circles,3);
                this.container.setChildIndex(this.arrow,0);
                this.dragLineColor = this.basicColor.notSelected.out;
                this.changed(true);
                this.engine.stage.update();
            });
            this.changed(true);
            this.engine.stage.update();
            if(callback)callback();
        });

        this.engine.stage.update();
    }

    getConfig( copy = false, withCtrl=true ){
        return {
            guid: !copy ? this.guid : null,
            position: [this.container.x,this.container.y],
            rotation: this.container.rotation,
            scale: this.container.scaleX,
            circlesCount: this.circlesCount,
            arrowType: this.baseConf.arrowType,
            arrowHead: this.baseConf.arrowHead,
            config: JSON.prune(this.config, {inheritedProperties:true,prunedString: undefined}),
            circlesList: this.circlesList,
            centerPkt: withCtrl ? this.centerPkt : null,
            circlesPos: this.prepareCirclesPos()
        };
    }

    prepareCirclesPos(){
        let returnedArray = [];
        for(let i = this.circlesList.length-1; i >=0 ; i--){
            returnedArray.push({
                x: this.circlesList[i].x,
                y: this.circlesList[i].y
            });
        }
        return returnedArray;
    }

    prepareFromConfig(config){
        this.guid = config.guid;
        this.container.x = config.position[0];
        this.container.y = config.position[1];
        this.container.rotation = config.rotation;
        this.container.scale = config.scale;
        this.circlesCount = config.circlesCount;
        this.baseConf.arrowType = config.arrowType;
        this.baseConf.arrowHead = config.arrowHead;
        this.config = (JSON).parse(config.config);

        this.centerPkt = config.centerPkt;
        this.circlesPos = config.circlesPos;

        this.findPosition();
    }

    eventBuild(){
        this.pos={
            x:this.mousePos.x,
            y:this.mousePos.y,
            x1:this.mousePos.x,
            y1:this.mousePos.y,
        };

        this.arrow = new zim.Shape();
        this.dragLine = new zim.Shape();

        this.dragLineColor = this.basicColor.notSelected.out;
        this.circles = new zim.Container();

        this.orderContainer = new zim.Container();
        this.orderCircle = new zim.Circle(this.config.orderSize.current,"#ffffff");

        this.orderLabel = new zim.Label({
            text:"10",
            size: parseInt(this.config.orderSize.current),
            font:"courier",
            color:"black"
        });

        this.orderContainer.visible=false;

        this.orderLabel.center(this.orderCircle);
        this.orderCircle.center(this.orderContainer);

        this.container.addChild(this.arrow);
        this.container.addChild(this.circles);
        this.container.addChild(this.dragLine);
        this.container.addChild(this.orderContainer);

        this.orderContainer.noDrag();
        this.dragLine.drag();
        this.circles.drag();
        this.arrow.drag();

        this.circles.visible = true;
        this.isSelected = false;
        this.circlesCount = 1;
        this.circlesList = [];

        this.circles.off("pressmove");
        this.circles.on("pressmove",(e)=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if( this.circlesList[0].x !== 0 || this.circlesList[0].y !== 0){
                this.container.x += this.circlesList[0].x;
                this.container.y += this.circlesList[0].y;
                for(let i = 1; i < this.circlesList.length; i++){
                    this.circlesList[i].x -= this.circlesList[0].x;
                    this.circlesList[i].y -= this.circlesList[0].y;
                }
                this.circlesList[0].x = this.circlesList[0].y = 0;
            }
            this.changed();
            this.engine.stage.update();
        });

        this.circles.off("pressup");
        this.circles.on("pressup",(e)=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if( this.circlesList[0].x !== 0 || this.circlesList[0].y !== 0){
                this.container.x += this.circlesList[0].x;
                this.container.y += this.circlesList[0].y;
                for(let i = 1; i < this.circlesList.length; i++){
                    this.circlesList[i].x -= this.circlesList[0].x;
                    this.circlesList[i].y -= this.circlesList[0].y;
                }
                this.circlesList[0].x = this.circlesList[0].y = 0;
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
            this.container.x += this.arrow.x;
            this.container.y += this.arrow.y;
            this.objectManager.dragAllTo(this,[-this.arrow.x,-this.arrow.y]);
            this.arrow.pos(0,0);
            this.changed();
            this.engine.stage.update();
        });

        this.arrow.off("pressup");
        this.arrow.on("pressup",(e)=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            this.container.x += this.arrow.x;
            this.container.y += this.arrow.y;
            this.objectManager.dragAllTo(this,[-this.arrow.x,-this.arrow.y]);
            this.arrow.pos(0,0);
            this.changed(true);
            this.engine.stage.update();
        });

        this.dragLine.off('pressmove');
        this.dragLine.on('pressmove',(e)=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            this.container.x += this.dragLine.x;
            this.container.y += this.dragLine.y;
            this.objectManager.dragAllTo(this,[-this.dragLine.x,-this.dragLine.y]);
            this.dragLine.pos(0,0);
            this.changed();
            this.engine.stage.update();
        });

        this.dragLine.off("pressup");
        this.dragLine.on("pressup",(e)=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            if(!this.objectManager.isRightClick(e) && !this.isSelected){ this.objectManager.select(this); }
            this.container.x += this.dragLine.x;
            this.container.y += this.dragLine.y;
            this.objectManager.dragAllTo(this,[-this.dragLine.x,-this.dragLine.y]);
            this.dragLine.pos(0,0);
            this.changed(true);
            this.engine.stage.update();
        });
     }

    buildFromLast(){

        this.container.set({x:this.baseConf.position[0],y:this.baseConf.position[1]});

        this.eventBuild();

        this.circlesCount = this.baseConf.circlesCount;

        for(let i = this.circlesCount-1; i >= 0; i--){
            let newCircle = new zim.Circle(10,"#f65634de");
            newCircle.x = this.baseConf.circlesPos[i].x;
            newCircle.y = this.baseConf.circlesPos[i].y;
            this.circlesList.push(newCircle);
            this.circles.addChild(newCircle);
        }

        this.circles.visible = false;

        this.dragLine.off("mouseover");
        this.dragLine.on("mouseover",()=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            this.dragLineColor = this.basicColor.notSelected.in;
            this.changed();
            this.engine.stage.update();
        });

        this.dragLine.off("mouseout");
        this.dragLine.on("mouseout",()=>{
            this.container.setChildIndex(this.dragLine,1);
            this.container.setChildIndex(this.orderContainer,2);
            this.container.setChildIndex(this.circles,3);
            this.container.setChildIndex(this.arrow,0);
            this.dragLineColor = this.basicColor.notSelected.out;
            this.changed(true);
            this.engine.stage.update();
        });

        if( this.circlesList[0].x !== 0 || this.circlesList[0].y !== 0){
            this.container.x += this.circlesList[0].x;
            this.container.y += this.circlesList[0].y;
            for(let i = 1; i < this.circlesList.length; i++){
                this.circlesList[i].x -= this.circlesList[0].x;
                this.circlesList[i].y -= this.circlesList[0].y;
            }
            this.circlesList[0].x = this.circlesList[0].y = 0;
        }

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
        this.updateArrow();
    }
    findPosition(){
        if(this.circles && this.circlesList[0]) {
            let min = {
                x: this.circlesList[0].x,
                y: this.circlesList[0].y
            };
            let max = {
                x: this.circlesList[0].x,
                y: this.circlesList[0].y
            };
            for (let i = 0; i < this.circlesList.length; i++) {
                if (this.circlesList[i].x > max.x) max.x = this.circlesList[i].x;
                else if (this.circlesList[i].x < min.x) min.x = this.circlesList[i].x;

                if (this.circlesList[i].y > max.y) max.y = this.circlesList[i].y;
                else if (this.circlesList[i].y < min.y) min.y = this.circlesList[i].y;
            }

            this.pos = {
                x: min.x + this.container.x,
                y: min.y + this.container.y,
                x1: max.x + this.container.x,
                y1: max.y + this.container.y,
            };

            let bounds = [
                (min.x < 0 ? min.x : -min.x) - 20,
                (min.y < 0 ? min.y : -min.y) - 20,
                Math.abs(min.x - max.x) + 40,
                Math.abs(min.y - max.y) + 40,
            ];

            this.container.setBounds(bounds[0], bounds[1], bounds[2], bounds[3]);
        }
    }
    updateArrow() {

        this.orderCircle.radius = this.config.orderSize.current;
        this.orderLabel.size = parseInt(this.config.orderSize.current);

        let pts = [];
        let firstElement = this.circlesList[0];
        for(let i = 0; i < this.circlesList.length; i++){
            pts.push(this.circlesList[i].x-firstElement.x);
            pts.push(this.circlesList[i].y-firstElement.y);
        }
        pts = this.getCurvePoints(pts, 0.4);
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

        let centerPkt = Math.round((pts.length / 2)/2)*2;
        let newPkt = {
            x:firstElement.x+pts[centerPkt],
            y:firstElement.y+pts[centerPkt+1]
        };

        this.orderContainer.x = newPkt.x;
        this.orderContainer.y = newPkt.y;
        this.orderLabel.text = this.config.orderText.current;
        this.orderLabel.center(this.orderCircle);
        this.orderContainer.visible = !(this.config.orderText.current === 0 || this.config.orderText.current === "");

    }
}

export default MultiArrow;