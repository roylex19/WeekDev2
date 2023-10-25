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
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "/server/index.php");
        xhr.responseType = "json";
        xhr.setRequestHeader("x-requested-with", "XMLHttpRequest");
        xhr.setRequestHeader("Content-Type", "application/json");
        return new Promise((resolve, reject) => {
            xhr.onload = () => {
                const response = xhr.response
                if(xhr.status !== 200){
                    reject(response);
                }
                resolve(response);
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
        this.numFloors = numFloors;
        this.numElevators = numElevators;
        this.elevatorCapacity = elevatorCapacity;
        this.floors = [];
        this.elevators = [];
        this.elevatorQueue = [];
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
            const elevator = new Elevator(elevatorBlock.cloneNode(true), i, this.elevatorCapacity, this.floors);
            this.elevators.push(elevator);
            this.elevatorsBlock.append(elevator.element);
        }

        app.toggleElement(this.buildingBlock, true);

        setInterval(this.sendSelectedFloorsToServer, 1000);
    };

    sendSelectedFloorsToServer = () => {
        if(this.elevatorQueue.length === 0){
            return false;
        }

        app.ajax({
            action: "callElevator",
            ...this.getData()
        }).then(res => {
            if(res === null){
                return false;
            }
            this.setData(res.data);
            const elevatorData = res.data.elevator;
            if(elevatorData === undefined){
                return false;
            }
            const elevator = this.elevators[res.data.elevator.id];
            elevator.setData(elevatorData);
            const action = res.data.action;
            if(action === "moveToFloor"){
                elevator.moveToPosition(res.data.position, res.data.duration);
            }else if(action === "openDoors"){
                elevator.onArrived();
            }
        });
    };

    addToElevatorQueue = (floor) => {
        app.building.elevatorQueue.push(floor);
    };

    getData = () => {
        return {
            elevators: this.getElevatorsData(),
            elevatorQueue: this.elevatorQueue
        };
    };

    getElevatorsData = () => {
        return this.elevators.map(elevator => (elevator.getData()));
    };

    setData = (data) => {
        this.elevatorQueue = data.elevatorQueue;
    };
}

class Floor
{
    constructor(element, number){
        this.element = element;
        this.number = number;

        const buttonCallElevator = element.querySelector(".floor__button-call-elevator");
        buttonCallElevator.addEventListener("click", () => {
            this.setActiveButtonCallElevator(true);
            app.building.addToElevatorQueue(this.number);
        });

        this.buttonCallElevator = buttonCallElevator;
    };

    setActiveButtonCallElevator = (state) => {
        this.buttonCallElevator.classList.toggle("active", state);
    };
}

class Elevator
{
    constructor(element, id, capacity, floors){
        this.element = element;
        this.id = id;
        this.doorsBlock = element.querySelector(".elevator__doors");
        this.panelBlock = element.querySelector(".elevator__panel");
        this.buttonsFloorsBlock = element.querySelector(".elevator__buttons-floors");
        this.buttonsOptionBlock = element.querySelector(".elevator__buttons-option");
        this.floors = floors.slice(0).reverse();
        this.floor = this.floors[0];
        this.capacity = capacity;
        this.isAvailable = true;
        this.isMoving = false;
        this.height = 220;
        this.speed = 2;
        this.doorsCloseDelayTime = 5;
        this.isDoorsOpened = false;
        this.floorQueue = [];

        const buttonSelectFloor = this.buttonsFloorsBlock.querySelector(".elevator__button-select-floor");
        this.buttonsFloorsBlock.innerHTML = "";
        for(let i = 0; i < this.floors.length; i++){
            const buttonSelectFloorNew = buttonSelectFloor.cloneNode(true);
            buttonSelectFloorNew.innerText = i + 1;
            buttonSelectFloorNew.onclick = () => {
                this.addToFloorQueue(i + 1);
                this.sendSelectedFloorsToServer();
            };
            this.buttonsFloorsBlock.append(buttonSelectFloorNew);
        }

        const buttonOpenDoors = this.buttonsOptionBlock.querySelector(".elevator__button-open-doors");
        const buttonCloseDoors = this.buttonsOptionBlock.querySelector(".elevator__button-close-doors");
        buttonOpenDoors.addEventListener("click", this.openDoors);
        buttonCloseDoors.addEventListener("click", this.closeDoors);
        this.panelBlock.append(buttonOpenDoors, buttonCloseDoors);

        this.element.addEventListener("transitionend", (e) => {
            if(e.propertyName !== "bottom"){
                return false;
            }
            this.onArrived();
        });
    };

    onArrived = () => {
        this.floor.setActiveButtonCallElevator(false);
        this.openDoors();
    };

    closeDoors = () => {
        this.doorsBlock.classList.remove("opened");
        this.doorsBlock.ontransitionend = () => {
            app.ajax({
                action: "closeElevatorDoors",
                elevators: app.building.getElevatorsData(),
                id: this.id
            }).then(res => {
                this.setData(res.data.elevator);
            });
        };
    };

    openDoors = () => {
        this.doorsBlock.classList.add("opened");
        this.doorsBlock.ontransitionend = () => {
            app.ajax({
                action: "openElevatorDoors",
                elevators: app.building.getElevatorsData(),
                id: this.id
            }).then(res => {
                this.setData(res.data.elevator);
                setTimeout(this.closeDoors, this.doorsCloseDelayTime * 1000);
            });
        };
    };

    moveToPosition = (position, duration) => {
        this.element.style.transitionDuration = duration + "s";
        this.element.style.bottom = position + "px";
    };

    setFloor(number){
        this.floor = this.floors[number - 1];
    };

    sendSelectedFloorsToServer = () => {
        app.ajax({
            action: "sendElevator",
            elevators: app.building.getElevatorsData(),
            id: this.id
        }).then(res => {
            if(res === null){
                return false;
            }
            this.setData(res.data.elevator);
            const action = res.data.action;
            if(action === "moveToFloor"){
                this.moveToPosition(res.data.position, res.data.duration);
            }
        });
    };

    addToFloorQueue = (floor) => {
        this.floorQueue.push(floor);
    }

    getData = () => {
        return {
            id: this.id,
            currentFloor: this.floor.number,
            capacity: this.capacity,
            isAvailable: this.isAvailable,
            isMoving: this.isMoving,
            isDoorsOpened: this.isDoorsOpened,
            height: this.height,
            speed: this.speed,
            floorQueue: this.floorQueue
        };
    };

    setData = (data) => {
        this.setFloor(data.currentFloor);
        this.isAvailable = data.isAvailable;
        this.isMoving = data.isMoving;
        this.isDoorsOpened = data.isDoorsOpened;
    };
}

const app = new App();

onload = () => {
    app.init();
};