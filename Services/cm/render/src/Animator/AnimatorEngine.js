import ObjectManager from './ObjectManager';
import NotifManager from './NotifManager';
import Grid from './Grid';
import Player from './Player';
import RectangleArea from "./elements/area/RectangleArea";
import MultiArrow from "./elements/arrows/MultiArrow";
import CircleArea from "./elements/area/CircleArea";
import SingleArrow from "./elements/arrows/SingleArrow";
import OtherElement from "./elements/other/OtherElement";
import NumberedPlayers from "./elements/players/NumberedPlayers";
import TextElement from "./elements/text/TextElement";
import MotionManager from "./MotionManager";
import HistoryManager from "./HistoryManager";
import 'whatwg-fetch';

class AnimatorEngine {
    "use strict";

    build(renderIs=false,data=null) {
        NotifManager.init();


        this.mainFrame = new zim.Frame("animatorCanvas",$('#animatorCanvas').width(),$('#animatorCanvas').height(),'#CCC');
        // dostepne opcje
        this.options = {
            fullscreen: false,
            grid: false,
        };

        this.watermark = null;

        this.animatorItemConfig = {};
        fetch('componentResources/animator/animatorItemConfig.json')
            .then(r=>r.json())
            .then((config)=>{
                this.animatorItemConfig = config;
            });

        this.animatorItemList = {};
        fetch('componentResources/animator/animatorItemList.json')
            .then(r=>r.json())
            .then((config)=>{
                this.animatorItemList = config;
            });

        this.loadData = data;
        if(this.loadData == null && this.dataToLoadLocal){
            this.loadData = this.dataToLoadLocal;
        }

        if(this.loadData && this.loadData.watermark){
            this.watermark = $.extend({},this.loadData.watermark) ;
        }

        this.resizeTimeout = null;

        this.grid = Grid;
        this.objectManager = ObjectManager;
        this.field = null;
        this.watermarkBitmap = null;

        // aktualna akcja myszki
        this.actualAction = 'field';

        // aktualnie wybrany element z danych obiektow
        this.actualActionItemName = 'none';

        this.fieldUrl = this.loadData ? this.loadData.pathField : "componentResources/animator/football/pitch/light_grass_bottom_half.jpg";

        this.motionManager = MotionManager;
        // podstawowa konfiguracja menadzera obiektow
        this.objectManager.build();

        //akcja po załadowaniu widoku 1 raz
        this.mainFrame.off('ready');
        this.mainFrame.on('ready',()=>{
            this.resize(renderIs);
        });

        this.waiter = null;

        this.selectRect = new zim.Rectangle(1, 1, "#007bff4d");
        this.historyManager = HistoryManager;

        this.selectRect.visible = false;

        this.player = Player;
        this.player.init(this);
        this.historyManager.init(this);
    }
    init () {
        this.stage = this.mainFrame.stage;
        zim.Ticker.alwaysOff(this.stage);

        this.mainFrame.tickChildren = false;
        this.mainFrame.tickEnabled = false;
        this.mainFrame.tickOnUpdate  = false;

        this.stage.tickChildren = false;
        this.stage.tickEnabled = false;
        this.stage.tickOnUpdate  = false;

        this.stage.enableMouseOver(10);
        this.loadFieldAndObjects();
    }
    loadFieldAndObjects(){
        this.loadImgAndGet(this.fieldUrl,(frame,tmpImg)=>{
            let ff = this.mainFrame.stage.getChildByName('field');
            if(ff){
                this.mainFrame.stage.removeChild(ff);
                delete ff.image;
                ff.image = null;
            }

            this.field = new zim.Bitmap(this.fieldUrl);
            this.field.name = 'field';
            this.field.x = 0;
            this.field.y = 0;

            let wat = this.mainFrame.stage.getChildByName('watermark');
            if(wat){
                this.mainFrame.stage.removeChild(wat);
                delete wat.image;
                wat.image = null;
            }

            ObjectManager.init(this.field,this);

            if(this.loadData){

                for (let i=0;i<this.loadData.objectInAnimation.length;i++){
                    if(this.loadData.objectInAnimation[i]){
                        let configOnStart = null;

                        for (let j=0;j<this.loadData.frameData[0].length;j++){
                            if(this.loadData.frameData[0][i] && this.loadData.frameData[0][i].guid === this.loadData.objectInAnimation[i].savedConfig.guid){
                                configOnStart = $.extend(true,[],this.loadData.frameData[0][i]);
                                break;
                            }
                        }

                        if(configOnStart){
                            this.loadData.objectInAnimation[i].savedConfig = configOnStart;
                        }
                    }
                }

                ObjectManager.maxFrame = this.loadData.frameData.length;
                ObjectManager.elementPerFrame = [];

                for (let i=0;i<this.loadData.frameData.length;i++){
                    if(this.loadData.frameData[i]){
                        ObjectManager.elementPerFrame.push($.extend(true,[],this.loadData.frameData[i]));
                    }
                }

                for(let i=ObjectManager.maxFrame-1;i>=0;i--){
                    if(!ObjectManager.elementPerFrame[i]){
                        ObjectManager.maxFrame--;
                        ObjectManager.elementPerFrame.splice(i,1);
                    }
                }

                ObjectManager.currentFrame = 0;

                ObjectManager.load(this.loadData.objectInAnimation);

                for(let i=0;i<ObjectManager.maxFrame;i++){

                    if(!$('.animatorFrameItem').eq(i).length){
                        $('#animatorFrameSliderContainer').append(`<div class="animatorFrameItem"><span>${1+i}</span></div>`);
                    }

                    $('.animatorFrameItem').eq(i).addClass('animatorFrameVisibled');
                }
            }


            ObjectManager.draw(this);
            this.motionManager.init(this);

            if(this.watermark && this.watermark.image){

                this.loadFromData64(this.watermark.image)
                    .then((result)=>{

                        this.watermarkBitmap = new zim.Bitmap(result);
                        this.watermarkBitmap.name = 'watermark';
                        this.watermarkBitmap.scale = this.watermark.scale;
                        this.watermarkBitmap.alpha = this.watermark.opacity;

                        let actualWidth = this.stage.width / this.stage.scaleX;
                        let actualHeight = this.stage.height / this.stage.scaleY;

                        switch (this.watermark.position) {
                            case "top-left":
                                this.watermarkBitmap.x = 20;
                                this.watermarkBitmap.y = 0;
                                break;
                            case "top":
                                this.watermarkBitmap.x = (actualWidth / 2.0) - this.watermarkBitmap.width / 2.0;
                                this.watermarkBitmap.y = 0;
                                break;
                            case "center":
                                this.watermarkBitmap.x = (actualWidth / 2.0) - this.watermarkBitmap.width / 2.0;
                                this.watermarkBitmap.y = (actualHeight / 2.0) - this.watermarkBitmap.height / 2.0;
                                break;
                            case "top-right":
                                this.watermarkBitmap.x = actualWidth - this.watermarkBitmap.width - 20;
                                this.watermarkBitmap.y = 0;
                                break;
                            case "bottom-left":
                                this.watermarkBitmap.x = 20;
                                this.watermarkBitmap.y = actualHeight - this.watermarkBitmap.height;
                                break;
                            case "bottom":
                                this.watermarkBitmap.x = (actualWidth / 2.0) - this.watermarkBitmap.width / 2.0;
                                this.watermarkBitmap.y = actualHeight - this.watermarkBitmap.height;
                                break;
                            case "bottom-right":
                                this.watermarkBitmap.x = actualWidth - this.watermarkBitmap.width - 20;
                                this.watermarkBitmap.y = actualHeight - this.watermarkBitmap.height;
                                break;
                        }

                        this.stage.addChildAt(this.field,0);
                        this.stage.addChild(this.selectRect);
                        if(this.watermarkBitmap) {
                            this.stage.addChild(this.watermarkBitmap);

                        }
                        this.stage.update();
                        if(this.loadData){
                            this.setFrame(0);
                            this.loadData = null;
                        }
                    });
            }else{
                this.stage.addChildAt(this.field,0);
                this.stage.addChild(this.selectRect);
                if(this.watermarkBitmap) {
                    this.stage.addChild(this.watermarkBitmap);

                }
                this.stage.update();
                if(this.loadData){
                    this.setFrame(0);
                    this.loadData = null;
                }
            }

        });
    }

    loadFromData64(d64){
        return new Promise((resolve,reject)=>{
            let image = new Image();
            image.onload = function () {
                resolve(image);
            };
            image.src = d64;
        });
    }

    setFrame(frame){
        ObjectManager.setFrame(frame);
        ObjectManager.deselectAll();
        ObjectManager.engine = this;
        $('.animatorFrameActived').each(function(){
            $(this).removeClass('animatorFrameActived');
        });
        $('.animatorFrameItem').eq(frame).addClass('animatorFrameActived');
    }

    deleteFrame(frame,callback = null){
        ObjectManager.deleteFrame(frame,callback);
    }

    setHelp(helpText){
        $("#animatorHelpText").text(helpText);
    }

    resize(renderIs = false){

        if(this.mainFrame.stage && this.mainFrame){
            this.resizeLayout(renderIs);
            this.checkOptionsStatus(renderIs);
            ObjectManager.hideSettingsMenu();
        }

        if(this.checkInterval) clearInterval(this.checkInterval);
        this.checkInterval = setInterval(()=>{
            if($('#animator').height() > 0 && $('#animatorCanvas').height() <= 50 ){
                this.resize();
            }
        },2000);

    }
    saveInConfiguratorMenu(){
        ObjectManager.saveInConfiguratorMenu(this);
    }
    deleteInConfiguratorMenu(){
        ObjectManager.deleteInConfiguratorMenu(this);
    }
    changeCategory(categoryName){
        this.actualAction = categoryName;

        switch(categoryName){
            case "field": this.setHelp("Wybierz rodzaj boiska."); break;
            default:this.setHelp("Wybierz element, aby umieścić go na boisku.");break;
        }
    }
    cancelCategoryItem(){
        $('.animatorItemInGroup').each(function(element){
            $( this ).removeClass('animatorItemInGroupActived');
        });
        this.changeCategoryItem("none");
    }
    changeCategoryItem(itemName){
        this.actualActionItemName = itemName;

        switch(itemName){
            case "none": this.setHelp("Wybierz element, aby umieścić go na boisku."); break;
            case "bzpilka":
            case "bbezpilki":
            case "podanie":
            case "strzal":
            case "odleglosc":
            case "pomocnicza":
            case "podanie-m":
            case "bbezpilki-m":
            case "bzpilka-m":
            case "strzal-m":
            case "odleglosc-m":
            case "pomocnicza-m":
            default:
                this.setHelp("Kliknij i przytrzymaj wciśnięty lewy przycisk myszki, aby narysować linię."); break;
        }

        if(itemName === "none") return;
        if(this.actualAction==="field"){
            this.changeField(itemName);
        }
    }
    changeField(path){
        this.cancelCategoryItem();
        this.historyManager.addEvent("field",this.fieldUrl );
        this.fieldUrl = path;
        this.loadFieldAndObjects();
    }
    changeWatermark(watermark){
        this.cancelCategoryItem();
        this.watermark = watermark;
        this.loadFieldAndObjects();
    }
    resizeLayout(renderIs){
        if(this.resizeTimeout) clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(()=>{
            $('#animatorCanvasCanvas').remove();
            let animCanvas = $('#animatorCanvas');
            let animCanvasCont = $('#animatorCanvasContainer');
            let leftSidebar = $('#animatorLeftSidebar');
            let containerWidth = animCanvasCont.width();
            let calculatedHeight = containerWidth*0.599889625;

            animCanvas.height(calculatedHeight);
            leftSidebar.height(calculatedHeight);

            delete this.mainFrame.canvas;
            this.mainFrame.canvas = null;

            setTimeout(()=>{
                this.mainFrame.remakeCanvas(parseInt(Math.round(animCanvas.width())+(renderIs?0:1)),parseInt(Math.round(animCanvas.height())+(renderIs?0:1)));
                this.init();
                let scale = containerWidth / 1812;
                this.stage.setTransform(0,0,scale,scale);

                if(this.options.grid == 1 || this.options.grid == 2){
                    this.grid.init(this,this.options.grid);
                }

                this.stage.update();
                clearTimeout(this.resizeTimeout);
                this.resizeTimeout = null;
            },500)
        },500);
    }
    save( animationData ){
        let promise = new Promise((resolve, reject)=>{
            if( !animationData.name || animationData.name.length < 3 ){
                NotifManager.localNotify("Wpisz temat Twojego projektu.","error",6000);
                reject(false);
            }else if(!animationData.folder){
                NotifManager.localNotify("Musisz wybrać folder do zapisu","error",6000);
                reject(false);
            }else{
                let objectInProject = [];

                this.objectManager.deselectAll();

                for(let i=0;i<this.objectManager.objectList.length;i++){
                    let objectType = (this.objectManager.objectList[i] instanceof SingleArrow) ? 'SingleArrow' : (this.objectManager.objectList[i] instanceof MultiArrow) ? 'MultiArrow' :
                        (this.objectManager.objectList[i] instanceof CircleArea) ? 'CircleArea' : (this.objectManager.objectList[i] instanceof OtherElement) ? 'OtherElement' :
                            (this.objectManager.objectList[i] instanceof NumberedPlayers) ? 'NumberedPlayers' :
                                (this.objectManager.objectList[i] instanceof TextElement) ? 'TextElement' : 'RectangleArea';

                    objectInProject.push({
                        type: objectType,
                        savedConfig: this.objectManager.objectList[i].getConfig()
                    });
                }

                if(this.options.fullscreen) {
                    this.optionToggle("fullscreen");
                }

                if(this.options.grid) {
                    this.optionToggle("grid");
                }

                this.objectManager.setFrame(0);

                for(let i=0;i<animationData.equipment.length;i++){
                    animationData.equipment[i] = animationData.equipment[i].toLowerCase()
                }

                let animationDatas = {
                    name: animationData.name,
                    folder: animationData.folder,
                    login_token: AuthMiddleware.user.login_token,
                    first_img: this.stage.toDataURL("#ffffff","image/jpeg"),
                    age_category: animationData.age_category,
                    equipment: animationData.equipment,
                    description: animationData.description,
                    pathField: this.fieldUrl,
                    frameData: $.extend(true,[],this.objectManager.elementPerFrame),
                    objectInAnimation: objectInProject,
                    game_field: animationData.field,
                    watermark: this.watermark,
                    tips: animationData.tips
                };


                axios.post("animations",JSON.prune(animationDatas, {arrayMaxLength:500,inheritedProperties:true,prunedString: undefined}))
                    .then((response)=>{
                        NotifManager.localNotify("Twój projekt został zapisany.","success",6000);
                        window.localStorage.clear();
                        resolve(response.data);
                    })
                    .catch(()=>{
                        reject(false);
                    });

                animationDatas = null;
            }
        });
        return promise;
    }
    loadLocal(data){
        this.dataToLoadLocal = data;
    }
    saveLocal(animationData){
        let objectInProject = [];

        if(this.objectManager && this.objectManager.objectList){
            for(let i=0;i<this.objectManager.objectList.length;i++){
                let objectType = (this.objectManager.objectList[i] instanceof SingleArrow) ? 'SingleArrow' : (this.objectManager.objectList[i] instanceof MultiArrow) ? 'MultiArrow' :
                    (this.objectManager.objectList[i] instanceof CircleArea) ? 'CircleArea' : (this.objectManager.objectList[i] instanceof OtherElement) ? 'OtherElement' :
                        (this.objectManager.objectList[i] instanceof NumberedPlayers) ? 'NumberedPlayers' :
                            (this.objectManager.objectList[i] instanceof TextElement) ? 'TextElement' : 'RectangleArea';
                objectInProject.push({
                    type: objectType,
                    savedConfig: this.objectManager.objectList[i].getConfig()
                });
            }
        }

        let animationDatas = {
            animData: animationData,
            pathField: this.fieldUrl,
            watermark: this.watermark,
            frameData: this.objectManager && this.objectManager.elementPerFrame ? $.extend(true,[],this.objectManager.elementPerFrame) : [],
            objectInAnimation: objectInProject,
        };

        localStorage.setItem('animation', JSON.prune(animationDatas, {inheritedProperties:true,prunedString: undefined}));

        animationDatas = null;
    }
    update(animationData, onlyData=false){
        let promise = new Promise((resolve, reject)=>{

            if( !animationData.name || animationData.name.length < 3 ){
                NotifManager.localNotify("Wpisz temat Twojego projektu.","error",6000);
                reject(false);
            }else if(!animationData.folder){
                NotifManager.localNotify("Musisz wybrać folder do zapisu","error",6000);
                reject(false);
            }else{

                let objectInProject = [];
                for(let i=0;i<this.objectManager.objectList.length;i++){
                    let objectType = (this.objectManager.objectList[i] instanceof SingleArrow) ? 'SingleArrow' : (this.objectManager.objectList[i] instanceof MultiArrow) ? 'MultiArrow' :
                        (this.objectManager.objectList[i] instanceof CircleArea) ? 'CircleArea' : (this.objectManager.objectList[i] instanceof OtherElement) ? 'OtherElement' :
                            (this.objectManager.objectList[i] instanceof NumberedPlayers) ? 'NumberedPlayers' :
                                (this.objectManager.objectList[i] instanceof TextElement) ? 'TextElement' : 'RectangleArea';
                    objectInProject.push({
                        type: objectType,
                        savedConfig: this.objectManager.objectList[i].getConfig()
                    });
                }

                if(this.options.fullscreen) {
                    this.optionToggle("fullscreen");
                }

                if(this.options.grid) {
                    this.optionToggle("grid");
                }

                this.objectManager.setFrame(0);

                for(let i=0;i<animationData.equipment.length;i++){
                    animationData.equipment[i] = animationData.equipment[i].toLowerCase()
                }

                let animationDatas = {
                    name: animationData.name,
                    folder: animationData.folder,
                    login_token: AuthMiddleware.user.login_token,
                    first_img: this.stage.toDataURL("#ffffff","image/jpeg"),
                    description: animationData.description,
                    age_category: animationData.age_category,
                    equipment: animationData.equipment,
                    pathField: this.fieldUrl,
                    frameData: this.objectManager.elementPerFrame,
                    objectInAnimation: objectInProject,
                    game_field: animationData.game_field,
                    tips: animationData.tips,
                    onlyData: onlyData,
                    watermark: this.watermark,
                };


                axios.put("animations/"+animationData.id,JSON.prune(animationDatas, {arrayMaxLength:500,inheritedProperties:true,prunedString: undefined}))
                    .then((response)=>{
                        NotifManager.localNotify("Twój projekt został zapisany.","success",6000);
                        resolve(response);
                    })
                    .catch((error, er)=>{
                        reject(false);
                    })

            }
        });
        return promise;
    }
    optionToggle(optionName){
        if( optionName === "grid" ){
            this.options[optionName] ++;
            if(this.options[optionName] >= 3)this.options[optionName] = 0;
            this.checkOptionsStatus();
            return this.options[optionName] === 0 || this.options[optionName] === 1;
        }
        this.options[optionName] = !this.options[optionName];
        this.checkOptionsStatus();
        return true;
    }
    checkOptionsStatus(renderIs){
        if(!this.options) return;
        this.fullscreenOptionCheck();
        this.resizeLayout(renderIs);
    }
    loadImgAndGet(path,callback){
        if(this.waiter){
            this.waiter.hide();
            this.waiter = null;
        }
        this.waiter = new zim.Waiter({
            container: this.stage,
            circleColor: 'white',
            corner: 3,
            color: '#944DC3',
            fadeTime: 500
        });
        this.waiter.show();
        this.waiter.x = 906;
        this.waiter.y = 510;
        let thisFrame = this.mainFrame;

        let cnv = document.createElement("canvas"),
            ctx = cnv.getContext("2d");

        let tmpImg = new Image();
        tmpImg.crossOrigin = "Anonymous";

        tmpImg.onload = () => {
            cnv.width = tmpImg.width;
            cnv.height = tmpImg.height;
            this.waiter.hide();
            ctx.drawImage(tmpImg, 0, 0);
            tmpImg.onload = null;
            callback(thisFrame,cnv.toDataURL());
            thisFrame.stage.update();
            thisFrame.removeAllEventListeners("complete");
        };
        tmpImg.src = path;
    }
    copySelected(){
        ObjectManager.copySelected();
    }
    fullscreenOptionCheck(){
        let animator = document.querySelector("#animator");
        if(this.options.fullscreen){
            if(animator.webkitRequestFullscreen)animator.webkitRequestFullscreen();
            else if(animator.mozRequestFullScreen)animator.mozRequestFullScreen();
            else if(animator.msRequestFullscreen)animator.msRequestFullscreen();
            else if(animator.requestFullscreen)animator.requestFullscreen();
        }else{
            if(document.webkitExitFullscreen)document.webkitExitFullscreen();
            else if(document.mozCancelFullScreen)document.mozCancelFullScreen();
            else if(document.msExitFullscreen)document.msExitFullscreen();
            else if(document.exitFullscreen)document.exitFullscreen();
            else if(document.cancelFullScreen)document.cancelFullScreen();
        }

        if (document.fullscreenElement ||
            document.mozFullScreenElement ||
            document.webkitFullscreenElement ||
            document.msFullscreenElement ) {
            $('#animator').addClass('inFullScreen');
        }else{
            $('#animator').removeClass('inFullScreen');
        }
    }
    destroy(){
        if(this.mainFrame){
            this.mainFrame.off('ready');
            this.mainFrame.off('complete');
            this.mainFrame.removeAllEventListeners();
            if(this.mainFrame.stage){
                this.mainFrame.stage.removeAllEventListeners();
                this.mainFrame.stage.removeAllChildren();
            }
        }
        if(this.field) this.field.removeAllEventListeners();
        if(this.checkInterval) clearInterval(this.checkInterval);

        delete this.dataToLoadLocal;
        this.dataToLoadLocal = null;

        ObjectManager.destroy();
        NotifManager.destroy();
        this.grid.destroy();
    }
    getFramePng(){
        this.objectManager.deselectAll();
        if(this.stage && ObjectManager){
            this.downloadURI(this.stage.toDataURL(),'klatka_'+ObjectManager.currentFrame);
            NotifManager.localNotify("Obraz danej klatki został utworzony i jest właśnie pobierany.","info",6000);
        }else NotifManager.localNotify("Nie możesz teraz wygenerować obrazu klatki, spróbuj jeszcze raz za kilka sekund.","warning",6000);
    }
    downloadURI(uri, name) {
        let link = document.createElement("a");
        link.download = name;
        link.href = uri;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

}

export default new AnimatorEngine()