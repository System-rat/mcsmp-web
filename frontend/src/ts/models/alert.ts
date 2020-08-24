export enum AlertType {
    Debug,
    Info,
    Warning,
    Critical,
}

export class AlertMessage {
    constructor(
        public message: string,
        public title: string = "Alert",
        public alertType: AlertType = AlertType.Info,
        public requireConfirm: boolean = false,
        public onConfirm?: (result: boolean) => Promise<void>
    ) {

    }
}