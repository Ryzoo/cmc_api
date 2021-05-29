import SingleArrow from "./elements/arrows/SingleArrow"
import MultiArrow from "./elements/arrows/MultiArrow"
import RectangleArea from "./elements/area/RectangleArea"
import CircleArea from "./elements/area/CircleArea"
import OtherElement from "./elements/other/OtherElement"
import NumberedPlayers from "./elements/players/NumberedPlayers";
import TextElement from "./elements/text/TextElement";

class EventManager {
    "use strict";

    init(field, engine, objectManager) {
        this.objectManager = objectManager;
        this.engine = engine;
        this.field = field;

        if (engine.stage && field) {
            this.field.off("mousedown", this.handleClick.bind(this));
            this.field.on("mousedown", this.handleClick.bind(this));
        }
    }

    handleClick(e) {
        if (!this.objectManager.isRightClick(e)) {
            this.startSelectPosition = this.engine.stage.globalToLocal(this.engine.stage.mouseX, this.engine.stage.mouseY);
            this.endSelectPosition = this.startSelectPosition;

            if (!this.objectManager.isKeyPressed(16)) this.objectManager.deselectAll();
            this.objectManager.hideSettingsMenu();

            if (this.engine.actualAction === 'arrows' && this.engine.actualActionItemName !== "none") {
                switch (this.engine.actualActionItemName) {
                    case "bzpilka":
                        this.objectManager.objectList.push(new SingleArrow(this.engine, {arrowType: "curved", arrowHead: "one"}));
                        break;
                    case "bbezpilki":
                        this.objectManager.objectList.push(new SingleArrow(this.engine, {arrowType: "gap", arrowHead: "one"}));
                        break;
                    case "podanie":
                        this.objectManager.objectList.push(new SingleArrow(this.engine, {
                            arrowType: "straight",
                            arrowHead: "one"
                        }));
                        break;
                    case "strzal":
                        this.objectManager.objectList.push(new SingleArrow(this.engine, {arrowType: "double", arrowHead: "one"}));
                        break;
                    case "podanie-m":
                        this.objectManager.objectList.push(new MultiArrow(this.engine, {
                            arrowType: "straight",
                            arrowHead: "one"
                        }));
                        break;
                    case "bbezpilki-m":
                        this.objectManager.objectList.push(new MultiArrow(this.engine, {arrowType: "gap", arrowHead: "one"}));
                        break;
                    case "bzpilka-m":
                        this.objectManager.objectList.push(new MultiArrow(this.engine, {arrowType: "curved", arrowHead: "one"}));
                        break;
                    case "strzal-m":
                        this.objectManager.objectList.push(new MultiArrow(this.engine, {arrowType: "double", arrowHead: "one"}));
                        break;
                    case "odleglosc":
                        this.objectManager.objectList.push(new SingleArrow(this.engine, {
                            arrowType: "straight",
                            arrowHead: "two"
                        }));
                        break;
                    case "pomocnicza":
                        this.objectManager.objectList.push(new SingleArrow(this.engine, {arrowType: "gap", arrowHead: "none"}));
                        break;
                    case "odleglosc-m":
                        this.objectManager.objectList.push(new MultiArrow(this.engine, {
                            arrowType: "straight",
                            arrowHead: "two"
                        }));
                        break;
                    case "pomocnicza-m":
                        this.objectManager.objectList.push(new MultiArrow(this.engine, {arrowType: "gap", arrowHead: "none"}));
                        break;
                    default:
                        return;
                }
            }
            else if (this.engine.actualAction === 'area' && this.engine.actualActionItemName !== "none") {
                switch (this.engine.actualActionItemName) {
                    case "prosty-r":
                        this.objectManager.objectList.push(new RectangleArea(this.engine, {type: "straight"}));
                        break;
                    case "przerywany-r":
                        this.objectManager.objectList.push(new RectangleArea(this.engine, {type: "gap"}));
                        break;
                    case "prosty-c":
                        this.objectManager.objectList.push(new CircleArea(this.engine, {type: "straight"}));
                        break;
                    case "przerywany-c":
                        this.objectManager.objectList.push(new CircleArea(this.engine, {type: "gap"}));
                        break;
                    default:
                        return;
                }
            }
            else if (this.engine.actualAction === 'other' && this.engine.actualActionItemName !== "none") {
                this.objectManager.objectList.push(new OtherElement(this.engine, {pathToImg: this.engine.actualActionItemName}));
            }
            else if (this.engine.actualAction === 'players' && this.engine.actualActionItemName !== "none") {
                switch (this.engine.actualActionItemName) {
                    case "circle-red":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#e74c3c", gk: false}));
                        break;
                    case "circle-blue":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#3598db", gk: false}));
                        break;
                    case "circle-yellow":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#f1c410", gk: false}));
                        break;
                    case "circle-green":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#3ccd70", gk: false}));
                        break;
                    case "circle-red-gk":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#e74c3c", gk: true}));
                        break;
                    case "circle-blue-gk":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#3598db", gk: true}));
                        break;
                    case "circle-yellow-gk":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#f1c410", gk: true}));
                        break;
                    case "circle-green-gk":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#3ccd70", gk: true}));
                        break;
                    case "circle-coach":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#coach", gk: false}));
                        break;
                    case "circle-empty":
                        this.objectManager.objectList.push(new NumberedPlayers(this.engine, {color: "#ffffff", gk: false}));
                        break;
                    default:
                        this.objectManager.objectList.push(new OtherElement(this.engine, {
                            pathToImg: this.engine.actualActionItemName,
                            group: "players"
                        }));
                }
            } else if (this.engine.actualAction === 'text' && this.engine.actualActionItemName !== "none") {
                switch (this.engine.actualActionItemName) {
                    case "courier":
                        this.objectManager.objectList.push(new TextElement(this.engine, {
                            fontType: "courier new",
                            isBackground: false
                        }));
                        break;
                    case "lato":
                        this.objectManager.objectList.push(new TextElement(this.engine, {fontType: "lato", isBackground: false}));
                        break;
                    case "courier-b":
                        this.objectManager.objectList.push(new TextElement(this.engine, {
                            fontType: "courier new",
                            isBackground: true
                        }));
                        break;
                    case "lato-b":
                        this.objectManager.objectList.push(new TextElement(this.engine, {fontType: "lato", isBackground: true}));
                        break;
                    default:
                        return;
                }
            } else {
                this.objectManager.deselectAll();
                this.eventSelectedMove = this.field.on("pressmove", (e) => {
                    let current = this.engine.stage.globalToLocal(this.engine.stage.mouseX, this.engine.stage.mouseY);
                    this.endSelectPosition = current;
                    this.engine.selectRect.x = this.startSelectPosition.x;
                    this.engine.selectRect.y = this.startSelectPosition.y;
                    this.engine.selectRect.widthOnly = current.x - this.startSelectPosition.x;
                    this.engine.selectRect.heightOnly = current.y - this.startSelectPosition.y;
                    this.engine.selectRect.visible = true;
                    this.engine.stage.update();
                });
                this.eventSelectedUp = this.field.on("pressup", () => {
                    this.field.off("pressmove", this.eventSelectedMove);
                    this.field.off("pressup", this.eventSelectedUp);
                    this.eventSelectedMove = null;
                    this.eventSelectedUp = null;
                    this.engine.selectRect.visible = false;
                    this.engine.stage.update();
                    this.objectManager.selectInRect(this.startSelectPosition, this.endSelectPosition);
                    this.startSelectPosition = this.endSelectPosition = {x: 0, y: 0};
                });
            }
        } else {
            this.objectManager.showSettingsMenu(true, e);
        }
        e.stopPropagation();
        return false;
    }

    saveToConfig(object, objectManager, withPos = false) {

        if (objectManager.elementPerFrame[objectManager.currentFrame]) {

            let objectIndeks = objectManager.existObjectInFrame(object.guid);

            let objConfig = $.extend(true, {}, object.getConfig(false, withPos));

            if (objectIndeks >= 0)
                objectManager.elementPerFrame[objectManager.currentFrame][objectIndeks] = objConfig;
            else
                objectManager.elementPerFrame[objectManager.currentFrame].push(objConfig);
            object.prepareFromConfig(objConfig);
        }
    }

    destroy() {
    }
}

export default new EventManager();