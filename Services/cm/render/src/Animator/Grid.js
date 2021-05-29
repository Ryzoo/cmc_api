
class Grid{
    "use strict";

    init(engine, lvl = 1){
        this.engine = engine;
        this.gridLine = [];

        this.minSnapPx = 5;
        this.grWidth = 140 / lvl;
        this.grHeight = 140 / lvl;

        if(this.gridContainer) this.destroy();

        this.gridContainer = new zim.Container();
        this.gridContainer.mouseEnabled = false;
        this.gridContainer.tickEnabled = false;
        this.gridContainer.setBounds(0,0,this.engine.stage.width / this.engine.stage.scaleX,this.engine.stage.height / this.engine.stage.scaleX);

        this.createGrid();

        this.engine.stage.addChild(this.gridContainer);
    }

    createGrid(){
        for(let i=this.grWidth; i<this.engine.stage.width/ this.engine.stage.scaleX; i+=this.grWidth){
            this.createLine(i,0,i,this.engine.stage.height/ this.engine.stage.scaleX);
        }

        for(let i=this.grHeight; i<this.engine.stage.height/ this.engine.stage.scaleX; i+=this.grHeight){
            this.createLine(0,i,this.engine.stage.width/ this.engine.stage.scaleX,i);
        }
    }

    snapToGrid(container,conPos,ox,oy){
        let status = false;
        let nOx = ox;
        let nOy = oy;

        this.deselectLine();

        if(this.gridLine && this.engine.options.grid){

            for(let i=this.gridLine.length-1; i>=0; i--) {

                if (this.gridLine[i].pkt.start[0] !== 0) {
                    if (conPos.x >= this.gridLine[i].pkt.start[0]  - this.minSnapPx && conPos.x <= this.gridLine[i].pkt.start[0]  + this.minSnapPx) {
                        nOx = this.gridLine[i].pkt.start[0] - (conPos.x-ox);
                        status = true;
                        this.selectLine(this.gridLine[i]);
                    }
                }

                if (this.gridLine[i].pkt.start[0] === 0){
                    if (conPos.y >= this.gridLine[i].pkt.start[1]  - this.minSnapPx && conPos.y <= this.gridLine[i].pkt.start[1]  + this.minSnapPx) {
                        nOy =  this.gridLine[i].pkt.start[1] - (conPos.y-oy);
                        status = true;
                        this.selectLine(this.gridLine[i]);
                    }
                }

            }

        }

        return {
            status: status,
            ox: nOx,
            oy: nOy
        }
    }

    createLine(a,b,c,d){
        this.gridLine.push(new zim.Shape());
        let actual = this.gridLine[this.gridLine.length-1];
        this.gridContainer.addChild(actual);
        actual.pkt = {
            start: [a,b],
            end: [c,d]
        };
        actual.graphics.clear().s("#ffffff69").ss(1,"round")
            .mt(actual.pkt.start[0],actual.pkt.start[1])
            .lt(actual.pkt.end[0],actual.pkt.end[1]);
    }

    deselectLine(){
        if(this.gridLine){
            for(let i=this.gridLine.length-1; i>=0; i--){
                this.gridLine[i].graphics.clear().s("#ffffff69").ss(1,"round")
                    .mt(this.gridLine[i].pkt.start[0],this.gridLine[i].pkt.start[1])
                    .lt(this.gridLine[i].pkt.end[0],this.gridLine[i].pkt.end[1]);
            }
        }
    }

    selectLine(line){
        line.graphics.clear().s("#ffffff69").ss(3,"round")
            .mt(line.pkt.start[0],line.pkt.start[1])
            .lt(line.pkt.end[0],line.pkt.end[1]);
    }

    destroy(){

        if(this.gridLine){
            for(let i=this.gridLine.length-1; i>=0; i--){
                this.gridLine[i].removeAllEventListeners();
                delete this.gridLine[i];
                this.gridLine[i] = null;
                this.gridLine.splice(i,1);
            }
        }

        if(this.gridContainer){
            this.gridContainer.removeAllEventListeners();
            delete this.gridContainer;
            this.gridContainer = null;
        }
    }

}


export default new Grid();