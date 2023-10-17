class App
{
    constructor(){
        this.numFloors = 0;
        this.numElevators = 0;
        this.elevatorCapacity = 0;

        const xhr = new XMLHttpRequest();
        xhr.responseType = "json";
        xhr.open("POST", "http://lift.local/server/index.php");
        xhr.setRequestHeader("x-requested-with", "XMLHttpRequest");
        xhr.setRequestHeader("Content-Type", "application/json");
        this.xhr = xhr;
    };

    init = () => {
        this.formStart = document.getElementById("formStart");
        this.updateFormStartInputs()
        this.initFormStart();
    };

    ajax = data => {
        data = {
            ...data,
            numFloors: this.numFloors,
            numElevators: this.numElevators,
            elevatorCapacity: this.elevatorCapacity
        };
        const xhr = this.xhr;
        return new Promise(function(resolve, reject){
            xhr.onload = () => {
                if(xhr.status !== 200){
                    reject(xhr.response);
                }
                resolve(xhr.response);
            };
            xhr.onerror = () => {
                reject(xhr.status);
            };
            xhr.send(JSON.stringify(data));
        });
    };

    toggleElement = (element, state = null) => {
        state === null ? element.classList.toggle("hidden") : element.classList.toggle("hidden", !state);
    };

    updateFormStartInputs = () => {
        this.formStart.querySelectorAll("input").forEach(input => {
            input.parentElement.querySelector(".input-value").innerText = input.value;
            input.oninput = this.updateFormStartInputs;
            this[input.id] = Number(input.value);
        });
    };

    initFormStart = () => {
        const thisClass = this;
        thisClass.formStart.onsubmit = function(e){
            e.preventDefault();
            thisClass.toggleElement(this, false);
            new Building(thisClass.numFloors, thisClass.numElevators, thisClass.elevatorCapacity);
        };
    };
}

class Building
{
    constructor(numFloors, numElevators, elevatorCapacity){
        this.buildingBlock = document.querySelector(".building");
        this.floorsBlock = this.buildingBlock.querySelector(".floors");
        this.elevatorsBlock = this.buildingBlock.querySelector(".elevators");
        this.floors = [];
        this.elevators = [];

        const floorBlock = this.floorsBlock.querySelector(".floor");
        this.floorsBlock.innerHTML = "";
        for(let i = 0; i < numFloors; i++){
            const floor = new Floor(floorBlock.cloneNode(true));
            this.floors.push(floor);
            this.floorsBlock.append(floor.element);
        }

        const elevatorBlock = this.elevatorsBlock.querySelector(".elevator");
        this.elevatorsBlock.innerHTML = "";
        for(let i = 0; i < numElevators; i++){
            const elevator = new Elevator(elevatorBlock.cloneNode(true), this.floors);
            this.elevators.push(elevator);
            this.elevatorsBlock.append(elevator.element);
        }

        app.toggleElement(this.buildingBlock, true);
    };
}

class Floor
{
    constructor(element){
        this.element = element;
        this.buttonCallElevator = element.querySelector(".floor__button-call-elevator");
        this.buttonCallElevator.onclick = () => {
            alert("Вызов лифта");
        };
    };
}

class Elevator
{
    constructor(element, floors){
        element.classList.add("opened");
        this.element = element;
        this.floors = floors;
        this.doorsBlock = element.querySelector(".elevator__doors");
        this.panelBlock = element.querySelector(".elevator__panel");
        this.currentFloor = 0;

        const buttonSelectFloor = this.panelBlock.querySelector(".elevator__button-select-floor");
        this.panelBlock.innerHTML = "";
        for(let i = 0; i < this.floors.length; i++){
            const buttonSelectFloorNew = buttonSelectFloor.cloneNode(true);
            buttonSelectFloorNew.innerText = i + 1;
            buttonSelectFloorNew.onclick = () => {
                this.moveToFloor(i);
            };
            this.panelBlock.append(buttonSelectFloorNew);
        }
    }

    closeDoors = () => {
        return new Promise((resolve) => {
            this.element.classList.remove("opened");
            this.doorsBlock.ontransitionend = () => {
                resolve();
            };
        });
    };

    openDoors = () => {
        return new Promise((resolve) => {
            this.element.classList.add("opened");
            this.doorsBlock.ontransitionend = () => {
                resolve();
            };
        });
    };

    moveToFloor = (destinationFloor) => {
        this.closeDoors().then(() => {
            this.element.ontransitionend = (e) => {
                if(e.propertyName !== "bottom") return;
                this.openDoors();
            };
            this.element.style.bottom = `${destinationFloor * 220}px`;
        });
    };

    animate = ({timing, draw, duration}) => {
        return new Promise((resolve) => {
            let start = performance.now();
            requestAnimationFrame(function animate(time){
                let timeFraction = (time - start) / duration;
                if(timeFraction > 1) timeFraction = 1;
                let progress = timing(timeFraction);
                draw(progress);
                if(timeFraction < 1){
                    requestAnimationFrame(animate);
                }else{
                    resolve();
                }
            });
        });
    };
}

const app = new App();

onload = () => {
    app.init();
    app.ajax({});
}