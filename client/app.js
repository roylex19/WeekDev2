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
        this.indicatorsBlock = this.buildingBlock.querySelector(".indicators");
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
        const indicatorBlock = this.indicatorsBlock.querySelector(".indicator");
        this.elevatorsBlock.innerHTML = "";
        this.indicatorsBlock.innerHTML = "";
        for(let i = 0; i < this.numElevators; i++){
            const elevator = new Elevator(elevatorBlock.cloneNode(true), i, this.elevatorCapacity, this.floors, indicatorBlock.cloneNode(true));
            this.elevators.push(elevator);
            this.elevatorsBlock.append(elevator.element);
            this.indicatorsBlock.append(elevator.indicatorBlock);
        }

        app.toggleElement(this.buildingBlock, true);

        window.scrollTo(0, document.body.scrollHeight);

        setInterval(this.sendSelectedFloorsToServer, 1000);
    };

    sendSelectedFloorsToServer = () => {
        if(this.elevatorQueue.length === 0 && this.isElevatorsQueueEmpty()){
            return false;
        }

        app.ajax({
            action: "processElevators",
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
            const action = elevatorData.action;
            const elevator = this.elevators[elevatorData.id];
            if(elevator === undefined){
                return false;
            }
            elevator.setData(elevatorData);
            if(action === "moveToFloor"){
                elevator.moveToPosition(elevatorData.position, elevatorData.duration);
            }else if(action === "openDoors"){
                elevator.onArrived();
            }
        });
    };

    addToElevatorQueue = (floor) => {
        if(!app.building.elevatorQueue.includes(floor)){
            app.building.elevatorQueue.push(floor);
        }
    };

    getData = () => {
        return {
            elevators: this.getElevatorsData(),
            elevatorQueue: this.elevatorQueue
        };
    };

    isElevatorsQueueEmpty = () => {
        const floorQueue = [];
        this.elevators.forEach(elevator => floorQueue.push(...elevator.floorQueue));
        return floorQueue.length === 0;
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

        const titleBlock = element.querySelector(".floor__title");
        titleBlock.innerText = number + " этаж";
    };

    setActiveButtonCallElevator = (state) => {
        this.buttonCallElevator.classList.toggle("active", state);
    };
}

class Elevator
{
    constructor(element, id, capacity, floors, indicatorBlock){
        this.element = element;
        this.id = id;
        this.doorsBlock = element.querySelector(".elevator__doors");
        this.buttonsFloorsBlock = element.querySelector(".elevator__buttons-floors");
        this.buttonsOptionBlock = element.querySelector(".elevator__buttons-option");
        this.capacityIndicatorBlock = element.querySelector(".elevator__capacity-indicator");
        this.numberPassengersBlock = element.querySelector(".elevator__number-passengers");
        this.indicatorBlock = indicatorBlock;
        this.indicatorFloorNumberBlock = indicatorBlock.querySelector(".indicator__floor-number");
        this.indicatorFloorNumberInternalBlock = element.querySelector(".elevator__floor-indicator");
        this.floors = floors.slice(0).reverse();
        this.floor = this.floors[0];
        this.capacity = capacity;
        this.isAvailable = true;
        this.isMoving = false;
        this.height = 220;
        this.speed = 2;
        this.doorsCloseDelayTime = 10;
        this.isDoorsOpened = false;
        this.floorQueue = [];
        this.buttonsSelectFloor = [];
        this.direction = 0;
        this.numberPassengers = 0;
        this.closeDoorsTimeoutId = null;
        this.checkFloorIntervalId = null;

        this.initFloorButtons();
        this.initLoadPassengers();
        this.initButtonCancel();

        this.element.addEventListener("transitionend", (e) => {
            if(e.propertyName !== "bottom"){
                return false;
            }
            this.onArrived();
            if(this.checkFloorIntervalId !== null){
                clearInterval(this.checkFloorIntervalId);
            }
        });
    };

    initFloorButtons = () => {
        const buttonSelectFloor = this.buttonsFloorsBlock.querySelector(".elevator__button-select-floor");
        this.buttonsFloorsBlock.innerHTML = "";
        for(let i = 0; i < this.floors.length; i++){
            const buttonSelectFloorNew = buttonSelectFloor.cloneNode(true);
            buttonSelectFloorNew.innerText = i + 1;
            buttonSelectFloorNew.onclick = () => {
                this.addToFloorQueue(i + 1);
                buttonSelectFloorNew.classList.add("active");
            };
            this.buttonsSelectFloor.push(buttonSelectFloorNew);
            this.buttonsFloorsBlock.append(buttonSelectFloorNew);
        }

        const buttonOpenDoors = this.buttonsOptionBlock.querySelector(".elevator__button-open-doors");
        const buttonCloseDoors = this.buttonsOptionBlock.querySelector(".elevator__button-close-doors");
        buttonOpenDoors.addEventListener("click", this.openDoors);
        buttonCloseDoors.addEventListener("click", this.closeDoors);
        this.buttonsOptionBlock.append(buttonOpenDoors, buttonCloseDoors);
    };

    initLoadPassengers = () => {
        this.element.querySelector(".elevator__button-load-passenger").onclick = () => {
            app.ajax({
                action: "loadElevatorPassenger",
                elevators: app.building.getElevatorsData(),
                id: this.id
            }).then(res => {
                this.setData(res.data.elevator);
            });
        };
        this.element.querySelector(".elevator__button-unload-passenger").onclick = () => {
            app.ajax({
                action: "unloadElevatorPassenger",
                elevators: app.building.getElevatorsData(),
                id: this.id
            }).then(res => {
                this.setData(res.data.elevator);
            });
        };
    };

    initButtonCancel = () => {
        const buttonCancel = this.buttonsOptionBlock.querySelector(".elevator__button-cancel");
        buttonCancel.onclick = () => this.cancel();
    };

    onArrived = () => {
        this.floor.setActiveButtonCallElevator(false);
        this.buttonsSelectFloor[this.floor.number - 1].classList.remove("active");
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
        if(this.closeDoorsTimeoutId !== null){
            clearTimeout(this.closeDoorsTimeoutId);
        }
        this.doorsBlock.classList.add("opened");
        this.doorsBlock.ontransitionend = () => {
            app.ajax({
                action: "openElevatorDoors",
                elevators: app.building.getElevatorsData(),
                id: this.id
            }).then(res => {
                this.setData(res.data.elevator);
            });
        };
        this.closeDoorsTimeoutId = setTimeout(this.closeDoors, this.doorsCloseDelayTime * 1000);
    };

    moveToPosition = (position, duration) => {
        this.element.style.transitionDuration = duration + "s";
        this.element.style.bottom = position + "px";
        this.checkFloorIntervalId = setInterval(this.checkFloor, 100);
    };

    checkFloor = () => {
        this.setFloor(this.floors.length - Math.round((this.element.getBoundingClientRect().bottom + window.scrollY) / this.height) + 1);
        this.indicatorFloorNumberBlock.innerText = this.floor.number;
        this.indicatorFloorNumberInternalBlock.innerText = this.floor.number;
    };

    setFloor(number){
        this.floor = this.floors[number - 1];
    };

    addToFloorQueue = (floor) => {
        if(!this.floorQueue.includes(floor)){
            this.floorQueue.push(floor);
        }
    };

    updateNumberPassengers = (number) => {
        this.numberPassengers = number;
        this.numberPassengersBlock.innerText = number;
        this.capacityIndicatorBlock.classList.toggle("active", !this.isAvailable);
    };

    cancel = () => {
        this.floorQueue = [];
        this.buttonsSelectFloor.forEach(button => button.classList.remove("active"));
    };

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
            floorQueue: this.floorQueue,
            direction: this.direction,
            numberPassengers: this.numberPassengers
        };
    };

    setData = (data) => {
        this.setFloor(data.currentFloor);
        this.isAvailable = data.isAvailable;
        this.isMoving = data.isMoving;
        this.isDoorsOpened = data.isDoorsOpened;
        this.floorQueue = data.floorQueue;
        this.direction = data.direction;
        this.updateNumberPassengers(data.numberPassengers);
    };
}

const app = new App();

onload = () => {
    app.init();
};