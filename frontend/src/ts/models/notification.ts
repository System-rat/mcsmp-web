export enum NotificationType {
    Error,
    Info
}

export class Notification {
    private _hash: number;

    public constructor(
        public title: string,
        public text: string,
        public type: NotificationType,
        public onClick?: () => void,
        public timeReceived: Date = new Date()
    ) {
        this._hash = Math.random() * 1000000;
    }

    get hash() {
        return  this._hash;
    }
}