
class MotionManager{
    "use strict";

    init(engine){
        this.engine = engine;

        this.position = {
            start: {
                x: 0,
                y: 0
            },
            center:{
                x: 0,
                y: 0
            },
            end: {
                x: 0,
                y: 0
            }
        };
        this.motionContainer = new zim.Container();
        this.curveLine = new zim.Shape();
        this.circles = new zim.Container();
        this.actualElement = null;

        let c1 = new zim.Circle(4,"#ebcb35de");
        let c2 = new zim.Circle(10,"#f65634de");
        let c3 = new zim.Circle(4,"#ebcb35de");
        let c4 = new zim.Circle(4,"#ebcb35de");
        c1.name = "c1";c1.expand(5);c1.set({x:0,y:0});c1.noDrag();
        c2.name = "c2";c2.expand(5);c2.set({x:0,y:0});c2.drag();
        c3.name = "c3";c3.expand(5);c3.set({x:0,y:0});c3.noDrag();
        c4.name = "c4";c4.expand(5);c4.set({x:0,y:0});c4.noDrag();
        this.circles.addChild(c1);
        this.circles.addChild(c2);
        this.circles.addChild(c3);
        this.circles.addChild(c4);

        this.circles.getChildByName("c2").off("pressmove");
        this.circles.getChildByName("c2").on("pressmove",()=>{
            this.position.center = {
                x: this.circles.getChildByName("c2").x,
                y: this.circles.getChildByName("c2").y,
            };
            this.actualElement.centerPkt = $.extend(true,{},this.position.center);
            this.draw();
        });

        this.circles.getChildByName("c2").off("pressup");
        this.circles.getChildByName("c2").on("pressup",()=>{
            this.position.center = {
                x: this.circles.getChildByName("c2").x,
                y: this.circles.getChildByName("c2").y,
            };
            this.actualElement.centerPkt = $.extend(true,{},this.position.center);
            this.actualElement.changed();
            this.engine.objectManager.eventManager.saveToConfig(this.actualElement,this.engine.objectManager,true);
            this.draw();
        });

        this.motionContainer.set({x:0,y:0});
        this.circles.set({x:0,y:0});
        this.curveLine.set({x:0,y:0});
        this.motionContainer.addChild(this.curveLine);
        this.motionContainer.addChild(this.circles);

        this.motionContainer.visible = false;
        this.engine.stage.addChild(this.motionContainer);
    }

    show(start, end, element, ctrPkt){
        this.positionBefore = null;
        this.motionContainer.visible = true;
        this.actualElement = element;
        let center = null;
        if(this.actualElement.centerPkt){
            center = this.actualElement.centerPkt;
        }else{
            center = {
                x: start.x + (end.x - start.x)/2,
                y: start.y + (end.y - start.y)/2,
            };
        }
        this.position = {
            start: end,
            center: center,
            end: start
        };
        this.circles.getChildByName("c1").pos(start.x,start.y);
        this.circles.getChildByName("c2").pos(this.position.center.x,this.position.center.y);
        this.circles.getChildByName("c3").pos(end.x,end.y);

        let beforeConf = this.engine.objectManager.getConfigForObjectInFrame(this.engine.objectManager.currentFrame-2,element);

        if(beforeConf){
            this.circles.getChildByName("c4").visible = true;

            let center = null;

            if(ctrPkt){
                center = ctrPkt;
            }else{
                center = {
                    x: beforeConf.position[0] + (end.x - beforeConf.position[0])/2,
                    y: beforeConf.position[1] + (end.y - beforeConf.position[1])/2,
                };
            }
            this.positionBefore = {
                start: {
                    x: beforeConf.position[0],
                    y: beforeConf.position[1],
                },
                center: center,
                end: end
            };
            this.circles.getChildByName("c4").pos(this.positionBefore.start.x,this.positionBefore.start.y);

        }else{
            this.positionBefore = null;
            this.circles.getChildByName("c4").visible = false;
        }

        this.draw();
    }

    hide(){
        this.motionContainer.visible = false;
        this.engine.stage.update();
        this.positionBefore = null;
    }

    draw(){
        this.engine.stage.setChildIndex( this.motionContainer, this.engine.stage.numChildren-1);
        this.curveLine.graphics.clear().sd([10, 10]).ss(1,"round").s("rgba(255,255,255,0.8)")
            .mt(this.position.start.x,this.position.start.y).qt(this.position.center.x,this.position.center.y,this.position.end.x,this.position.end.y)

        if(this.positionBefore){
            this.curveLine.graphics.sd([10, 10])
                .mt(this.position.start.x,this.position.start.y)
                .qt(this.positionBefore.center.x,this.positionBefore.center.y,this.positionBefore.start.x,this.positionBefore.start.y)
        }

        this.curveLine.graphics.sd([10, 10]).s("rgba(255,255,255,0.3)").mt(this.position.start.x,this.position.start.y).lt(this.position.center.x,this.position.center.y)
            .lt(this.position.end.x,this.position.end.y);
        this.engine.stage.update();
    }

    getQBezierValue(t, p1, p2, p3) {
        let iT = 1 - t;
        return iT * iT * p1 + 2 * iT * t * p2 + t * t * p3;
    }

    positionOnCurve(object, position) {
        let startX = object.intervalData.start.x,
            startY = object.intervalData.start.y,
            cpX = 0,
            cpY = 0,
            endX = object.intervalData.position.x,
            endY = object.intervalData.position.y;


        if(!object.intervalData.before){
            cpX = startX + (endX - startX)/2;
            cpY = startY + (endY - startY)/2;
        }else{
            cpX = object.intervalData.before.x;
            cpY = object.intervalData.before.y;
        }

        return {
            x:  this.getQBezierValue(position, startX, cpX, endX),
            y:  this.getQBezierValue(position, startY, cpY, endY)
        };
    }


}


export default new MotionManager();