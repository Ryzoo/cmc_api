import NotifManager from './NotifManager'

import SingleArrow from './elements/arrows/SingleArrow'
import MultiArrow from './elements/arrows/MultiArrow'
import CircleArea from './elements/area/CircleArea'
import RectangleArea from './elements/area/RectangleArea'
import OtherElement from "./elements/other/OtherElement";
import NumberedPlayers from "./elements/players/NumberedPlayers";
import TextElement from "./elements/text/TextElement";

class MenuBuilder{
    "use strict";

    constructor(){
        this.objects = {};
        this.config = {};
        this.htmlString = "";
        this.defaultSelect = false;
        this.engine = {};
        this.objectManager = {};
        this.copyTmp = [];
        this.groupElements = [];
        this.groupIndex = 0;
        let self = this;

        $(document).on("click",".animatorInMenuPasteButton",function(){
            self.objectManager.deselectAll();
            self.pasteSelected();
        });

        $(document).on("click",".animatorMenuColorItem",function(){
            $(".animatorMenuColorItem").each(function(){
                $(this).removeClass("animatorMenuColorItemSelected");
            });
            $(this).addClass("animatorMenuColorItemSelected");
            let toSaveType = $(this).data("type");

            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config[toSaveType].current = $(this).data("val");
                self.objects[i].select(null,true);

                for(let j=0;j<self.objectManager.elementPerFrame.length;j++){
                    for(let k=0;k<self.objectManager.elementPerFrame[j].length;k++){
                        if(self.objects[i].guid === self.objectManager.elementPerFrame[j][k].guid){
                            let cfg = (JSON).parse(self.objectManager.elementPerFrame[j][k].config);
                            cfg[toSaveType].current = $(this).data("val");
                            self.objectManager.elementPerFrame[j][k].config = JSON.prune(cfg, {inheritedProperties:true,prunedString: undefined})
                            break;
                        }
                    }
                }
            }

            self.engine.stage.update();
        });

        $(document).on("click",".animatorMenuColorItemImg",function(){
            $(".animatorMenuColorItemImg").each(function(){
                $(this).removeClass("animatorMenuColorItemSelected");
            });
            $(this).addClass("animatorMenuColorItemSelected");
            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config.color_img.current = $(this).data("val");
                self.objects[i].updateBitmap();
                self.objects[i].select(null,true);
            }

            self.engine.stage.update();
        });

        $(document).on("change",".animatorMenuInputText",function(){
            let type = $(this).data("val");
            let max = $(this).data("max");
            let thisVal = $(this).val();
            if(thisVal.length > max){
                thisVal = thisVal.substr(0,max);
                $(this).val(thisVal);
            }
            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config[type].current = $(this).val();
                self.objects[i].select(null,true);

                for(let j=0;j<self.objectManager.elementPerFrame.length;j++){
                    for(let k=0;k<self.objectManager.elementPerFrame[j].length;k++){
                        if(self.objects[i].guid === self.objectManager.elementPerFrame[j][k].guid){
                            let cfg = (JSON).parse(self.objectManager.elementPerFrame[j][k].config);
                            cfg[type].current = $(this).val();
                            self.objectManager.elementPerFrame[j][k].config = JSON.prune(cfg, {inheritedProperties:true,prunedString: undefined})
                            break;
                        }
                    }
                }
            }
            self.engine.stage.update();
        });

        $(document).on("click",".animatorMenuGroupLower, .animatorMenuGroupHigher",function(){
            let value = parseInt( $(this).data("val") );
            self.groupIndex += value;

            if(self.groupIndex < 0) self.groupIndex = self.groupElements.length-1;
            if(self.groupIndex > self.groupElements.length-1) self.groupIndex = 0;

            for(let i=0;i<self.objects.length;i++){
                self.objects[i].changeImg(self.groupElements[self.groupIndex]);
            }
            $(".animatorMenuGroupSelected img").first().attr("src",self.groupElements[self.groupIndex]);
            self.engine.stage.update();
        });

        $(document).on("click",".animatorMenuScaleLower, .animatorMenuScaleHigher",function(){
            let value = $(this).data("val");
            let max = $(this).data("max");
            let min = $(this).data("min");
            let current = parseFloat($('.animatorMenuScaleCurrent').first().val());
            current -= value;
            current = current.toFixed(2);
            if( current < min) current = min;
            else if( current > max) current = max;
            $('.animatorMenuScaleCurrent').first().val(current);

            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config.scale.current = parseFloat(current);
                self.objects[i].select(null,true);
            }

            self.engine.stage.update();
        });

        $(document).on("change",".animatorMenuScaleCurrent",function(){
            let max = $(this).data("max");
            let min = $(this).data("min");
            let current = parseFloat($(this).val());
            if( current < min) current = min;
            else if( current > max) current = max;
            $('.animatorMenuScaleCurrent').first().val(parseFloat(current));

            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config.scale.current = parseFloat(current);
                self.objects[i].select(null,true);
            }

            self.engine.stage.update();
        });

        $(document).on("click",".animatorMenuRotateLower, .animatorMenuRotateHigher",function(){
            let value = $(this).data("val");
            let max = $(this).data("max");
            let min = $(this).data("min");
            let current = parseInt($('.animatorMenuRotateCurrent').first().val());
            if(current === 0 && value > 0 ) current = 360;
            if(current === 360 && value < 0 ) current = 0;
            current -= value;
            if( current < min) current = min;
            else if( current > max) current = max;
            $('.animatorMenuRotateCurrent').first().val(current);

            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config.rotation.current = parseInt(current);
                self.objects[i].select(null,true);
            }

            self.engine.stage.update();
        });

        $(document).on("change",".animatorMenuRotateCurrent",function(){
            let max = $(this).data("max");
            let min = $(this).data("min");
            let current = parseInt($(this).val());
            if( current < min) current = min;
            else if( current > max) current = max;
            $('.animatorMenuRotateCurrent').first().val(current);

            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config.rotation.current = parseInt(current);
                self.objects[i].select(null,true);
            }

            self.engine.stage.update();
        });

        $(document).on("change",".animatorMenuSwitch",function(){
            let type = $(this).data("val");
            for(let i=0;i<self.objects.length;i++){
                self.objects[i].config[type].current = $(this).val();
                self.objects[i].select(null,true);

                for(let j=0;j<self.objectManager.elementPerFrame.length;j++){
                    for(let k=0;k<self.objectManager.elementPerFrame[j].length;k++){
                        if(self.objects[i].guid === self.objectManager.elementPerFrame[j][k].guid){
                            let cfg = (JSON).parse(self.objectManager.elementPerFrame[j][k].config);
                            cfg[type].current = $(this).val();
                            self.objectManager.elementPerFrame[j][k].config = JSON.prune(cfg, {inheritedProperties:true,prunedString: undefined})
                            break;
                        }
                    }
                }
            }
            self.engine.stage.update();
        });

        $(document).on("change",".animatorMenuNumberSwitch",function() {
            let type = $(this).data("val");
            let max = parseInt($(this).data("max"));
            let min = parseInt($(this).data("min"));
            let curr = parseInt($(this).val());

            if (min <= curr && curr <= max){
                    for (let i = 0; i < self.objects.length; i++) {
                        self.objects[i].config[type].current = parseInt($(this).val());
                        self.objects[i].select(null,true);

                        for(let j=0;j<self.objectManager.elementPerFrame.length;j++){
                            for(let k=0;k<self.objectManager.elementPerFrame[j].length;k++){
                                if(self.objects[i].guid === self.objectManager.elementPerFrame[j][k].guid){
                                    let cfg = (JSON).parse(self.objectManager.elementPerFrame[j][k].config);
                                    cfg[type].current = parseInt($(this).val());
                                    self.objectManager.elementPerFrame[j][k].config = JSON.prune(cfg, {inheritedProperties:true,prunedString: undefined})
                                    break;
                                }
                            }
                        }
                    }
                self.engine.stage.update();
            }
            else{
                if(curr < min) $(this).val(min);
                else $(this).val(max);
            }
        });

        $(document).on("click","#animatorLayerChangeButtonUp",function(){
            self.objectManager.changeObjectsLayerPos("up",self.objects);
        });

        $(document).on("click","#animatorLayerChangeButtonDown",function(){
            self.objectManager.changeObjectsLayerPos("down",self.objects);
        });
    }

    pasteSelected(){
        let msq = '';
        if(this.copyTmp.length === 0){
            NotifManager.localNotify("Schowek jest pusty, skopiuj elementy przed wklejeniem.",'error',1500);
        }else{
            if(this.copyTmp.length === 1) msq = "Wklejono 1 element.";
            else if(this.copyTmp.length >= 2 && this.copyTmp.length <= 4) msq = `Wklejono ${this.copyTmp.length} elementy.`;
            else msq = `Wklejono ${this.copyTmp.length} elementów.`;
            NotifManager.localNotify(msq,'warning',1500);

            for(let i=0;i<this.copyTmp.length;i++){
                let objectType = (this.copyTmp[i] instanceof SingleArrow) ? 'SingleArrow' : (this.copyTmp[i] instanceof MultiArrow) ? 'MultiArrow' :
                    (this.copyTmp[i] instanceof CircleArea) ? 'CircleArea' : (this.copyTmp[i] instanceof OtherElement) ? 'OtherElement' :
                        (this.copyTmp[i] instanceof NumberedPlayers) ? 'NumberedPlayers' :
                            (this.copyTmp[i] instanceof TextElement) ? 'TextElement' : 'RectangleArea';

                switch (objectType){
                    case 'SingleArrow':
                        this.objectManager.objectList.push( new SingleArrow(this.engine,$.extend(true,{},this.copyTmp[i].getConfig(true))));
                        break;
                    case 'MultiArrow':
                        this.objectManager.objectList.push( new MultiArrow(this.engine,$.extend(true,{},this.copyTmp[i].getConfig(true))));
                        break;
                    case 'CircleArea':
                        this.objectManager.objectList.push( new CircleArea(this.engine,$.extend(true,{},this.copyTmp[i].getConfig(true))) );
                        break;
                    case 'RectangleArea':
                        this.objectManager.objectList.push( new RectangleArea(this.engine,$.extend(true,{},this.copyTmp[i].getConfig(true))) );
                        break;
                    case 'OtherElement':
                        this.objectManager.objectList.push( new OtherElement(this.engine,$.extend(true,{},this.copyTmp[i].getConfig(true))) );
                        break;
                    case 'NumberedPlayers':
                        this.objectManager.objectList.push( new NumberedPlayers(this.engine,$.extend(true, {},this.copyTmp[i].getConfig(true))) );
                        break;
                    case 'TextElement':
                        this.objectManager.objectList.push( new TextElement(this.engine,$.extend(true, {},this.copyTmp[i].getConfig(true))) );
                        break;
                    default:
                        return;
                }
                let nowIn = this.objectManager.objectList.length-1;
                setTimeout(()=>{
                    this.objectManager.select(this.objectManager.objectList[nowIn],true);
                },100);
            }
        }
    }

    buildLayerSelect(){
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuLayerPosContainer">`;

        this.addLabel("Pozycja w warstwie:");

        this.htmlString += `<button class="btn btn-outline-light btn-sm " id="animatorLayerChangeButtonUp">W górę</i></button>`;
        this.htmlString += `<button class="btn btn-outline-light btn-sm " id="animatorLayerChangeButtonDown">W dół</button>`;

        this.htmlString += `</div>`;
    }

    buildOnlyCopyPaste(engine, objects, objectManager){
        this.htmlString = '';
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuCopyPasteContainer">`;

        if(this.copyTmp.length>0){
            if(this.copyTmp.length === 1)this.addLabel(`Aktualnie przechowujesz w schowku ${this.copyTmp.length} element`);
            else if(this.copyTmp.length >= 2 &&this.copyTmp.length <= 4)this.addLabel(`Aktualnie przechowujesz w schowku ${this.copyTmp.length} elementy`);
            else this.addLabel(`Aktualnie przechowujesz w schowku ${this.copyTmp.length} elementów`);
            this.htmlString += `<button class="btn btn-outline-light btn-sm animatorInMenuPasteButton" >Wklej</button>`
        }else{
            this.addLabel(`Nie ma nic w schowku. Kliknij na wybrany obiekt lub grupę obiektów prawym przyciskiem myszy, a następnie kliknij przycisk kopiuj, aby dodać je do schowka.`);
        }

        this.htmlString += `</div>`;
        return this.htmlString;
    }

    buildLayerPosition(engine,selectedType, objects, objectManager){
        this.engine = engine;
        this.objects = objects;
        this.objectManager = objectManager;
        this.config = objects[0].config;
        this.htmlString = "";
        this.buildLayerSelect();
        return this.htmlString;
    }

    build(engine,selectedType, objects, defaultSelect = false){
        this.engine = engine;
        this.objects = objects;
        this.config = objects[0].config;
        this.baseConfig = objects[0].getConfig();
        this.htmlString = "";
        this.defaultSelect = defaultSelect;

        switch(selectedType){
            case "arrow":
                this.buildColor();
                this.buildSwitch("type","Zmień typ strzałki:",this.config.type.options,this.config.type.current);
                this.buildSwitch("arrowhead","Zmień ilość grotów:",this.config.arrowhead.options,this.config.arrowhead.current);
                this.buildNumberSwitch("size",`Zmień grubość (${this.config.size.options[0]}-${this.config.size.options[1]}):`,this.config.size.current,this.config.size.options[0],this.config.size.options[1]);
                this.buildNumberSwitch("orderSize",`Zmień wielkość numeracji (${this.config.orderSize.options[0]}-${this.config.orderSize.options[1]}):`,this.config.orderSize.current,this.config.orderSize.options[0],this.config.orderSize.options[1]);
                this.buildNumberSwitch("orderText",`Numer porządkowy (${this.config.orderText.options[0]}-${this.config.orderText.options[1]}):`,this.config.orderText.current,this.config.orderText.options[0],this.config.orderText.options[1]);
                break;
            case "area":
                this.buildColor();
                this.buildSwitch("type","Zmień typ obramowania:",this.config.type.options,this.config.type.current);
                this.buildNumberSwitch("opacity",`Zmień przezroczystość (${this.config.opacity.options[0]}-${this.config.opacity.options[1]}):`,this.config.opacity.current,this.config.opacity.options[0],this.config.opacity.options[1]);
                break;
            case "numberedPlayer":
                this.buildColor();
                this.buildTextInput("textIn",`Tekst w środku (maks. ${this.config.textIn.options} znaki)`,this.config.textIn.current,this.config.textIn.options);
                this.buildScale(this.config);
                break;
            case "text":
                this.buildColor("Kolor:", this.config.fontColor, "fontColor");
                this.buildSwitch("type","Zmień czcionkę:",this.config.type.options,this.config.type.current);
                this.buildTextInput("textIn",`Tekst (maks. ${this.config.textIn.options} znaków)`,this.config.textIn.current,this.config.textIn.options);
                this.buildNumberSwitch("size",`Zmień rozmiar czcionki (${this.config.size.options[0]}-${this.config.size.options[1]}):`,this.config.size.current,this.config.size.options[0],this.config.size.options[1]);
                this.buildNumberSwitch("sizeWidth",`Zmień szerokość linii (${this.config.sizeWidth.options[0]}-${this.config.sizeWidth.options[1]}):`,this.config.sizeWidth.current,this.config.sizeWidth.options[0],this.config.sizeWidth.options[1]);
                this.buildRotation(this.config);
                break;
            case "other":
                this.buildColorImg();
                this.buildTextInput("textIn",`Podpis elementu (maks. ${this.config.textIn.options} znaków)`,this.config.textIn.current,this.config.textIn.options);
                this.inGroupSelect("Zmień grafikę:",this.config);
                this.buildScale(this.config);
                this.buildRotation(this.config);
                break;
            default:
                this.htmlString += `<label> Brak dostępnych opcji konfiguracyjnych.</label>`;
        }

        return this.htmlString;
    }

    inGroupSelect(label,config){
        let pathToImg = this.baseConfig.pathToImg;
        let mainGroup = this.baseConfig.group;
        let elements = [];

        if(!mainGroup) return;

        for(let i=0;i<this.engine.animatorItemList.category.length;i++){
            if(this.engine.animatorItemList.category[i].name === mainGroup){
                for(let z=0;z<this.engine.animatorItemList.category[i].items[1].items.length;z++){
                    elements.push(this.engine.animatorItemList.category[i].items[1].items[z].img);
                }
                for(let z=0;z<this.engine.animatorItemList.category[i].items[2].items.length;z++){
                    elements.push(this.engine.animatorItemList.category[i].items[2].items[z].img);
                }
                break;
            }
        }

        if (elements.length <= 0 ) return;

        this.groupElements = elements;
        this.groupIndex = 0;

        for(let z=0;z<this.groupElements.length;z++){
            if(this.groupElements[z] === pathToImg){
                this.groupIndex = z;
                break;
            }
        }

        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuInGroupSelectContainer">`;
        this.addLabel(label);
        this.htmlString += `<div class="animatorMenuGroupLower" data-val="${-1}"><i class="fas fa-caret-left"></i></div>`;
        this.htmlString += `<div class="animatorMenuGroupSelected" ><img alt="${mainGroup}" src="${this.groupElements[this.groupIndex]}"/></div>`;
        this.htmlString += `<div class="animatorMenuGroupHigher" data-val="${1}"><i class="fas fa-caret-right"></i></div>`;
        this.htmlString += `</div>`;
    }

    buildScale(config){
        if(!config.scalable) return;
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuScaleContainer">`;
        this.addLabel("Przeskaluj obiekt");
        this.htmlString += `<div class="animatorMenuScaleLower" data-val="${config.scale.options[2]}" data-min="${config.scale.options[0]}" data-max="${config.scale.options[1]}"><i class="fas fa-caret-left"></i></div>`;
        this.htmlString += `<input class="animatorMenuScaleCurrent" value="${config.scale.current}" data-min="${config.scale.options[0]}" data-max="${config.scale.options[1]}"/>`;
        this.htmlString += `<div class="animatorMenuScaleHigher" data-val="-${config.scale.options[2]}" data-min="${config.scale.options[0]}" data-max="${config.scale.options[1]}"><i class="fas fa-caret-right"></i></div>`;
        this.htmlString += `</div>`;
    }

    buildRotation(config,current){
        if(!config.rotatable) return;
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuRotationContainer">`;
        this.addLabel("Obróć obiekt");
        this.htmlString += `<div class="animatorMenuRotateLower" data-val="${config.rotation.options[2]}" data-min="${config.rotation.options[0]}" data-max="${config.rotation.options[1]}" ><i class="fas fa-caret-left"></i></div>`;
        this.htmlString += `<input class="animatorMenuRotateCurrent" value="${config.rotation.current}" data-min="${config.rotation.options[0]}" data-max="${config.rotation.options[1]}"/>`;
        this.htmlString += `<div class="animatorMenuRotateHigher" data-val="-${config.rotation.options[2]}" data-min="${config.rotation.options[0]}" data-max="${config.rotation.options[1]}"><i class="fas fa-caret-right"></i></div>`;
        this.htmlString += `</div>`;
    }

    buildTextInput(data,label,current,charMax){
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuTextInputContainer">`;
        this.addLabel(label);
        this.htmlString += `<input data-val="${data}" data-max="${charMax}" class="animatorMenuInputText form-control" value="${!this.defaultSelect ? '' :current}" />`;
        this.htmlString += `</div>`;
    }

    buildColorImg(){
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuColorContainer">`;

        this.addLabel("Wybór koloru:");

        for(let i=0;i<this.config.color_img.options.length;i++){
            if(this.defaultSelect && this.config.color_img.options[i].data === this.config.color_img.current)
                this.htmlString += `<div class="animatorMenuColorItemImg animatorMenuColorItemSelected" data-val="${this.config.color_img.options[i].data}" style="background-color: ${this.config.color_img.options[i].color}"></div>`;
            else
                this.htmlString += `<div class="animatorMenuColorItemImg" data-val="${this.config.color_img.options[i].data}" style="background-color: ${this.config.color_img.options[i].color}"></div>`;
        }

        this.htmlString += `</div>`;
    }

    buildColor(label = "Wybór koloru:", config = this.config.color, objSave = "color"){
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuColorContainer">`;
        this.addLabel(label);
        for(let i=0;i<config.options.length;i++){
            if(this.defaultSelect && config.options[i] === config.current)
                this.htmlString += `<div class="animatorMenuColorItem animatorMenuColorItemSelected" data-val="${config.options[i]}" data-type="${objSave}" style="background-color: ${config.options[i]}"></div>`;
            else
                this.htmlString += `<div class="animatorMenuColorItem" data-val="${config.options[i]}" data-type="${objSave}" style="background-color: ${config.options[i]}"></div>`;
        }
        this.htmlString += `</div>`;
    }

    copySelected(engine, selected, objectManager, eventManager){
        this.objectManager = objectManager;
        this.eventManager = eventManager;
        this.engine = engine;


        this.copyTmp = selected || [];
        let msq = '';
        if(this.copyTmp.length === 0){
            NotifManager.localNotify("Nie zaznaczyłeś żadnych elementów do skopiowania.",'error',1500);
        }else{
            if(this.copyTmp.length === 1) msq = "Skopiowałeś 1 element.";
            else if(this.copyTmp.length >= 2 &&this.copyTmp.length <= 4) msq = `Skopiowałeś ${this.copyTmp.length} elementy.`;
            else msq = `Skopiowałeś ${this.copyTmp.length} elementów.`;
            NotifManager.localNotify(msq,'warning',1500);
        }
    }

    buildSwitch(data,label,options,current){
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuSwitchContainer">`;
        this.addLabel(label);
        this.htmlString += `<select data-val="${data}" class="animatorMenuSwitch">`;

        if(!this.defaultSelect) this.htmlString += `<option selected disabled>${label}</option>`;

        for(let i=0;i<options.length;i++){
            if(this.defaultSelect && options[i].val === current)
                this.htmlString += `<option value="${options[i].val}" selected>${options[i].name}</option>`;
            else
                this.htmlString += `<option value="${options[i].val}">${options[i].name}</option>`;
        }

        this.htmlString += `</select>`;
        this.htmlString += `</div>`;
    }

    buildNumberSwitch(data,label,current, min, max){
        this.htmlString += `<div class="animatorMenuElementContainer animatorMenuNumberSwitchContainer">`;
        this.addLabel(label);
        this.htmlString += `<input data-val="${data}" data-min="${min}" data-max="${max}" class="animatorMenuNumberSwitch" type="number" value="${current}" min="${min}" max="${max}">`;
        this.htmlString += `</div>`;
    }

    addLabel(text){
        this.htmlString += `<label class="animatorMenuLabel">${text}</label>`;
    }

    destroy(){

    }
}


export default new MenuBuilder();