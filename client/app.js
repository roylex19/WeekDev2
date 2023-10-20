class App
{
    constructor(){
        this.numFloors = 0;
        this.numElevators = 0;
        this.elevatorCapacity = 0;
        this.building = new Building(this.numFloors, this.numElevators, this.elevatorCapacity);
    };

    init = () => {
        this.formStart = document.getElementById("formStart");
        this.updateFormStartInputs()
        this.initFormStart();
    };

    ajax = (data = {}) => {
        data = {
            ...data,
            ...this.building.getData()
        };
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "/server/index.php");
        xhr.responseType = "json";
        xhr.setRequestHeader("x-requested-with", "XMLHttpRequest");
        xhr.setRequestHeader("Content-Type", "application/json");
        return new Promise((resolve, reject) => {
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
            thisClass.building.numFloors = thisClass.numFloors;
            thisClass.building.numElevators = thisClass.numElevators;
            thisClass.building.elevatorCapacity = thisClass.elevatorCapacity;
            thisClass.building.init();
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
        this.numFloors = numFloors;
        this.numElevators = numElevators;
        this.elevatorCapacity = elevatorCapacity;
    };

    init = () => {
        const floorBlock = this.floorsBlock.querySelector(".floor");
        this.floorsBlock.innerHTML = "";
        for(let i = 0; i < this.numFloors; i++){
            const floor = new Floor(floorBlock.cloneNode(true), this.numFloors - i);
            this.floors.push(floor);
            this.floorsBlock.append(floor.element);
        }

        const elevatorBlock = this.elevatorsBlock.querySelector(".elevator");
        this.elevatorsBlock.innerHTML = "";
        for(let i = 0; i < this.numElevators; i++){
            const elevator = new Elevator(elevatorBlock.cloneNode(true), this.floors, this.elevatorCapacity);
            this.elevators.push(elevator);
            this.elevatorsBlock.append(elevator.element);
        }

        app.toggleElement(this.buildingBlock, true);
    };

    getData = () => {
        const elevators = [];
        this.elevators.forEach(elevator => {
            elevators.push({
                currentFloor: elevator.currentFloor,
                capacity: elevator.capacity,
            });
        });

        return {
            elevatorHeight: 220,
            elevators,
        };
    };
}

class Floor
{
    constructor(element, floorNumber){
        this.element = element;
        this.floorNumber = floorNumber;
        this.buttonCallElevator = element.querySelector(".floor__button-call-elevator");

        const thisClass = this;
        this.buttonCallElevator.onclick = function(){
            if(this.classList.contains("active")){
                return false;
            }
            this.classList.add("active");
            thisClass.callElevator();
        };
    };

    callElevator = () => {
        app.ajax({
            action: "callElevator",
            floor: this.floorNumber
        }).then(res => {
            this.onCallElevator(res.data.position);
        });
    };

    onCallElevator = (position) => {

    };
}

class Elevator
{
    constructor(element, floors, capacity){
        this.element = element;
        this.floors = floors;
        this.doorsBlock = element.querySelector(".elevator__doors");
        this.panelBlock = element.querySelector(".elevator__panel");
        this.currentFloor = 0;
        this.capacity = capacity;
        this.isDoorsOpened = false;

        const buttonSelectFloor = this.panelBlock.querySelector(".elevator__button-select-floor");
        this.panelBlock.innerHTML = "";
        for(let i = 0; i < this.floors.length; i++){
            const buttonSelectFloorNew = buttonSelectFloor.cloneNode(true);
            buttonSelectFloorNew.innerText = i + 1;
            buttonSelectFloorNew.onclick = () => {
                //this.moveToFloor(i);
            };
            this.panelBlock.append(buttonSelectFloorNew);
        }

        this.floors.forEach(floor => {
            floor.onCallElevator = (position) => {
                this.moveToPosition(position);
            };
        });
    }

    closeDoors = () => {
        return new Promise((resolve) => {
            this.element.classList.remove("opened");
            this.doorsBlock.ontransitionend = () => {
                this.isDoorsOpened = false;
                resolve();
            };
        });
    };

    openDoors = () => {
        return new Promise((resolve) => {
            this.element.classList.add("opened");
            this.doorsBlock.ontransitionend = () => {
                this.isDoorsOpened = true;
                resolve();
            };
        });
    };

    moveToPosition = (position) => {
        if(!this.isDoorsOpened){
            this.element.style.bottom = position + "px";
            this.element.ontransitionend = (e) => {
                if(e.propertyName !== "bottom"){
                    return false;
                }
                this.openDoors();
            };
        }else{
            this.closeDoors().then(() => {
                this.element.style.bottom = position + "px";
                this.element.ontransitionend = (e) => {
                    if(e.propertyName !== "bottom"){
                        return false;
                    }
                    this.openDoors();
                };
            });
        }
    };
}

const app = new App();

onload = () => {
    app.init();
}