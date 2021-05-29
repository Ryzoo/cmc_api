
class Player {
    "use strict";

    init( engine ){
        this.engine = engine;
        this.objectManager = this.engine.objectManager;

        this.framerate = 60;
        this.frameIncrease = 3; // jedna klatka w aniamtorze to 3 sekundy
        this.frameInterval = (this.framerate*this.frameIncrease);
        zim.Ticker.update = null;
    }

    reset(){
        if(this.actualTimeout) clearTimeout(this.actualTimeout);
        this.engine.setFrame(0);
        this.actualFrame = 0;
        this.playFrame = 0;
    }

    play(automatic = true){
        this.reset();

        if($("#allFrame") && $("#allFrame").val() == -1){
            let frame = (this.objectManager.maxFrame-1) * this.frameInterval;
            if(frame === 0 ) frame = 2;
            else frame--;
            $("#allFrame").val(frame);
        }

        this.getObjectNextConfigData();
        this.nextFrame(automatic);
    }

    nextFrame(automatic){
        this.actualFrame++;
        $("#allFrame").val($("#allFrame").val()-1);
        if(this.actualFrame > this.frameInterval){
            this.actualFrame = 0;
            this.playFrame++;
            if(this.playFrame >= this.objectManager.maxFrame-1){
                this.reset();
                return;
            }
            this.engine.setFrame(this.playFrame);
            this.getObjectNextConfigData();
        }

        this.objectManager.objectList.forEach((element)=>{
            if(element.intervalData){
                let position = this.engine.motionManager.positionOnCurve(element,this.actualFrame/((this.frameIncrease * this.framerate)+1));
                element.container.pos(position.x,position.y);

                let bsRotation = element.intervalData.rotationNext - element.intervalData.rotation;
                let bs2ndRotation = element.intervalData.rotationNext - (element.intervalData.rotation+360);
                let bs3ndRotation = element.intervalData.rotationNext+360 - (element.intervalData.rotation);
                let bs4ndRotation = element.intervalData.rotationNext+360 - (element.intervalData.rotation+360);
                let bs1 = Math.abs(bsRotation) < Math.abs(bs2ndRotation) ? bsRotation : bs2ndRotation;
                let bs2 = Math.abs(bs3ndRotation) < Math.abs(bs4ndRotation) ? bs3ndRotation : bs4ndRotation;
                let endBs = Math.abs(bs1) < Math.abs(bs2) ? bs1 : bs2;

                element.container.rotation = element.intervalData.rotation + endBs*(this.actualFrame/((this.frameIncrease * this.framerate)+1));

                element.container.scaleX = element.container.scaleY = element.intervalData.scale + ((element.intervalData.scaleNext-element.intervalData.scale)*(this.actualFrame/((this.frameIncrease * this.framerate)+1)));
            }
        });

        if(automatic) this.actualTimeout = setTimeout(()=>{
            this.nextFrame(automatic);
        },1000/this.framerate)
    }

    pause(){
        this.reset();
    }

    getObjectNextConfigData(){
        this.objectManager.objectList.forEach((element)=>{
            let nextConfig = this.objectManager.getConfigForObjectInFrame(this.playFrame+1,element);

            if(nextConfig){
                element.intervalData = {
                    position:{
                        x: nextConfig.position[0],
                        y: nextConfig.position[1]
                    },
                    start:{
                        x: element.container.x,
                        y: element.container.y
                    },
                    scale: element.container.scaleX,
                    scaleNext: nextConfig.scale,
                    rotation: element.container.rotation,
                    rotationNext: nextConfig.rotation,
                    before: nextConfig.centerPkt
                }
            }else element.intervalData = null;
        });
    }
}

export default new Player()